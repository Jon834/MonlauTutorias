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
 * Quick tutoring entry registration (phase 5.2). Usually reached with the
 * student already preselected via the "studentid" param (from the student's
 * own ficha, student/view.php) — but when it is missing (e.g. the "Nueva
 * tutoría" block shortcut, which has no specific student to link to), this
 * page shows a picker limited to the tutor's own current primary students
 * (assignment_repository::find_current_primary_by_tutor()) instead of
 * requiring the caller to already know a studentid.
 *
 * Security: local/monlaututoria:createentry (capability) +
 * scope_service::require_user_can_access_student() (ambito) — same 2-layer
 * pattern as every other page in this plugin exposing a specific student's
 * data (see docs/seguridad-permisos.md). The picker itself needs no separate
 * check: it is built from the tutor's own current assignments, so every
 * option it offers already passes scope_service by construction.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
// Fase 13 — in simple mode a tutor-by-assignment can register without the
// createentry capability; the per-student scope check below still applies.
$scopeservice = new \local_monlaututoria\service\scope_service();
$cancreate = has_capability('local/monlaututoria:createentry', $context)
    || (\local_monlaututoria\feature::simple_mode()
        && $scopeservice->user_is_current_tutor((int) $USER->id));
if (!$cancreate) {
    require_capability('local/monlaututoria:createentry', $context);
}

$studentid = optional_param('studentid', 0, PARAM_INT);
$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);
// Phase 6.2: reached with this param when the tutor is registering a new
// entry specifically to close an existing follow-up ("cierre... mediante
// nueva tutoría vinculada", docs/fases/phase-6.md) — no dedicated page for
// this, the existing quick/full registration flow is reused as-is.
$followupid = optional_param('followupid', 0, PARAM_INT);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyear = $requestedacademicyearid > 0
    ? $academicyearrepository->find($requestedacademicyearid)
    : $academicyearrepository->get_active();
if ($academicyear === null) {
    throw new \moodle_exception('error_invalidacademicyearid', 'local_monlaututoria');
}

if ($studentid <= 0) {
    $primaryrows = (new \local_monlaututoria\repository\assignment_repository())
        ->find_current_primary_by_tutor((int) $USER->id, (int) $academicyear->id);

    $studentoptions = [];
    if (!empty($primaryrows)) {
        $pickerstudentids = array_map(static fn ($row): int => (int) $row->studentid, $primaryrows);
        $pickerusers = $DB->get_records_list('user', 'id', $pickerstudentids, '', 'id, firstname, lastname');
        foreach ($pickerusers as $pickeruser) {
            $studentoptions[$pickeruser->id] = fullname($pickeruser);
        }
        asort($studentoptions);
    }

    $PAGE->set_context($context);
    $PAGE->set_url('/local/monlaututoria/entries/create.php', ['academicyearid' => $academicyear->id]);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title(get_string('entry_pick_student_title', 'local_monlaututoria'));
    $PAGE->set_heading(get_string('entry_pick_student_title', 'local_monlaututoria'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('entry_pick_student_title', 'local_monlaututoria'));

    if (empty($studentoptions)) {
        echo $OUTPUT->notification(
            get_string('entry_pick_student_empty', 'local_monlaututoria'),
            \core\output\notification::NOTIFY_INFO
        );
    } else {
        echo html_writer::tag('p', get_string('entry_pick_student_intro', 'local_monlaututoria'), ['class' => 'text-muted']);
        $picker = new single_select(
            new moodle_url('/local/monlaututoria/entries/create.php', ['academicyearid' => $academicyear->id]),
            'studentid',
            $studentoptions,
            '',
            ['' => get_string('choosedots')],
            'entrystudentpicker'
        );
        $picker->set_label(get_string('entry_pick_student_label', 'local_monlaututoria'));
        $picker->method = 'get';
        echo $OUTPUT->render($picker);
    }

    echo $OUTPUT->footer();
    exit;
}

$scopeservice->require_user_can_access_student((int) $USER->id, $studentid, (int) $academicyear->id);
// A former tutor can reach the ficha (read-only) but must not record new
// tutorías for a student they no longer have assigned.
if ($scopeservice->access_is_historical_only((int) $USER->id, $studentid)) {
    throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
}

$student = core_user::get_user($studentid);
if (!$student || !empty($student->deleted)) {
    throw new \moodle_exception('invaliduserid');
}

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/create.php', ['studentid' => $studentid, 'academicyearid' => $academicyear->id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_register_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_register_title', 'local_monlaututoria', fullname($student)));

$modalityoptions = [];
foreach ((new \local_monlaututoria\repository\modality_repository())->get_all(true) as $modality) {
    $modalityoptions[(int) $modality->id] = format_string($modality->name);
}

$reasonoptions = [];
foreach ((new \local_monlaututoria\repository\reason_repository())->get_all(true) as $reason) {
    $reasonoptions[(int) $reason->id] = format_string($reason->name);
}

// Same rule entries/attachments.php already enforces for uploading to an
// existing entry (editanyentry, or editownentry limited to entries this
// user authored) — "isowner" is trivially true here since the entry does
// not exist yet and this user is about to become its createdby.
$canupload = (has_capability('local/monlaututoria:editanyentry', $context)
    || has_capability('local/monlaututoria:editownentry', $context))
    // Fase 13 — no attachment field on the quick form in simple mode.
    && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::ATTACHMENTS);

$form = new \local_monlaututoria\form\entry_quick_form(null, [
    'modalities' => $modalityoptions,
    'reasons'    => $reasonoptions,
    'canupload'  => $canupload,
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
    $command = new \local_monlaututoria\domain\entry_create_command(
        (int) $data->studentid,
        (int) $USER->id,
        (int) $data->academicyearid,
        (int) $data->entrydate,
        !empty($data->modalityid) ? (int) $data->modalityid : null,
        $data->contentvisible !== '' ? $data->contentvisible : null,
        $data->noteinternal !== '' ? $data->noteinternal : null,
        null,
        !empty($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null,
        !empty($data->reasonid) ? [(int) $data->reasonid] : [],
        [],
        has_capability('local/monlaututoria:overridelock', $context)
    );

    $service = new \local_monlaututoria\service\entry_service();
    $newentryid = $service->create($command, (int) $USER->id);

    if (!empty($data->followupid)) {
        // followup_service's write methods (like entry_service::update()/
        // annul()) rely on the calling page for scope, they do not re-check
        // it themselves — so this page must verify the follow-up actually
        // belongs to the same student the scope check above already
        // covered, or a tutor with scope over $studentid could pass an
        // arbitrary followupid belonging to an unrelated student (IDOR).
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

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_register_title', 'local_monlaututoria', fullname($student)));
echo $renderer->contextual_help(
    get_string('help_concept_entry_title', 'local_monlaututoria'),
    html_writer::tag('p', get_string('help_concept_entry_short', 'local_monlaututoria'))
    . html_writer::tag('p', get_string('help_concept_entry_full', 'local_monlaututoria'))
);
$form->display();
echo $OUTPUT->footer();
