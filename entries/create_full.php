<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Full tutoring entry registration (phase 5.3): multiple related reasons,
 * internal/external participants, and the restricted note (only when the
 * user holds local/monlaututoria:viewrestrictednotes). Optionally also lets
 * the user attach files at creation time — see entries/create.php's own
 * canupload logic, reused here identically. Same 2-layer security pattern
 * as entries/create.php (phase 5.2): capability + scope_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:createentry', $context);

$studentid = required_param('studentid', PARAM_INT);
$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);
$followupid = optional_param('followupid', 0, PARAM_INT);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyear = $requestedacademicyearid > 0
    ? $academicyearrepository->find($requestedacademicyearid)
    : $academicyearrepository->get_active();
if ($academicyear === null) {
    throw new \moodle_exception('error_invalidacademicyearid', 'local_monlaututoria');
}

$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, $studentid, (int) $academicyear->id);

$student = core_user::get_user($studentid);
if (!$student || !empty($student->deleted)) {
    throw new \moodle_exception('invaliduserid');
}

$PAGE->set_context($context);
$PAGE->set_url(
    '/local/monlaututoria/entries/create_full.php',
    ['studentid' => $studentid, 'academicyearid' => $academicyear->id]
);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_full_register_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_full_register_title', 'local_monlaututoria', fullname($student)));

$modalityoptions = [];
foreach ((new \local_monlaututoria\repository\modality_repository())->get_all(true) as $modality) {
    $modalityoptions[(int) $modality->id] = format_string($modality->name);
}

$reasonoptions = [];
foreach ((new \local_monlaututoria\repository\reason_repository())->get_all(true) as $reason) {
    $reasonoptions[(int) $reason->id] = format_string($reason->name);
}

$showrestricted = has_capability('local/monlaututoria:viewrestrictednotes', $context);
$canupload = has_capability('local/monlaututoria:editanyentry', $context)
    || has_capability('local/monlaututoria:editownentry', $context);

$form = new \local_monlaututoria\form\entry_full_form(null, [
    'modalities'     => $modalityoptions,
    'reasons'        => $reasonoptions,
    'showrestricted' => $showrestricted,
    'canupload'      => $canupload,
]);

$attachmentdraftitemid = null;
if ($canupload) {
    $attachmentdraftitemid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($attachmentdraftitemid, null, 'user', 'draft', null);
}

$form->set_data((object) array_filter([
    'studentid'      => $studentid,
    'academicyearid' => (int) $academicyear->id,
    'followupid'     => $followupid,
    'attachments'    => $attachmentdraftitemid,
], static fn ($value) => $value !== null));

$returnurl = new moodle_url('/local/monlaututoria/student/view.php', ['id' => $studentid, 'academicyearid' => $academicyear->id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $participants = [];
    $repeats = (int) ($data->participant_repeats ?? 0);
    for ($i = 0; $i < $repeats; $i++) {
        $userid = !empty($data->participantuserid[$i]) ? (int) $data->participantuserid[$i] : null;
        $externalname = trim((string) ($data->participantexternalname[$i] ?? ''));
        if ($userid === null && $externalname === '') {
            // Untouched row — not every repeat slot needs to be used.
            continue;
        }
        $participants[] = (object) [
            'participanttype' => $data->participanttype[$i],
            'userid'          => $userid,
            'externalname'    => $externalname !== '' ? $externalname : null,
        ];
    }

    $command = new \local_monlaututoria\domain\entry_create_command(
        (int) $data->studentid,
        (int) $USER->id,
        (int) $data->academicyearid,
        (int) $data->entrydate,
        !empty($data->modalityid) ? (int) $data->modalityid : null,
        $data->contentvisible !== '' ? $data->contentvisible : null,
        $data->noteinternal !== '' ? $data->noteinternal : null,
        // Never read from $data unless the form actually offered the field —
        // a tampered POST adding "noterestricted" cannot smuggle it in, since
        // the form never rendered it and get_data() only returns elements
        // the form itself defined.
        ($showrestricted && !empty($data->noterestricted)) ? $data->noterestricted : null,
        !empty($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null,
        array_map('intval', $data->reasonids ?? []),
        $participants,
        has_capability('local/monlaututoria:overridelock', $context)
    );

    $service = new \local_monlaututoria\service\entry_service();
    $newentryid = $service->create($command, (int) $USER->id);

    if (!empty($data->followupid)) {
        // Same IDOR guard as entries/create.php — see its comment.
        $followuprepository = new \local_monlaututoria\repository\followup_repository();
        $followup = $followuprepository->get((int) $data->followupid);
        if ((int) $followup->studentid !== $studentid) {
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }

        (new \local_monlaututoria\service\followup_service())->close_with_entry(
            (int) $data->followupid, $newentryid, (int) $USER->id
        );
    }

    if ($canupload && !empty($data->attachments)) {
        (new \local_monlaututoria\service\entry_attachment_service())->save_uploaded_files(
            $newentryid, (int) $data->attachments, $data->attachmentcategory, (int) $USER->id
        );
    }

    redirect(
        $returnurl,
        get_string('entry_register_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_full_register_title', 'local_monlaututoria', fullname($student)));
$form->display();
echo $OUTPUT->footer();
