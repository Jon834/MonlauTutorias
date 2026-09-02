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
 * Edits the editable fields of an active tutoring entry (phase 5.5).
 * Student, tutor, academic year, entry date and status are never editable
 * here — status changes go through entries/annul.php instead.
 *
 * Capability: editanyentry, or editownentry limited to entries this user
 * authored (createdby) — distinct from local/monlaututoria:createentry,
 * which only gates creating a new one.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$id = required_param('id', PARAM_INT);

$repository = new \local_monlaututoria\repository\entry_repository();
$existing = $repository->get($id);

$scope = new \local_monlaututoria\service\scope_service();

$isowner = ((int) $existing->createdby === (int) $USER->id);
$caneditany = has_capability('local/monlaututoria:editanyentry', $context);
// Fase 13 — in simple mode a CURRENT tutor may edit their own entry without
// the editownentry capability. A former tutor (historical-only access) is
// read-only: they never reach here.
$caneditown = has_capability('local/monlaututoria:editownentry', $context)
    || (\local_monlaututoria\feature::simple_mode()
        && !$scope->access_is_historical_only((int) $USER->id, (int) $existing->studentid));
if (!$caneditany && !($isowner && $caneditown)) {
    throw new \moodle_exception('nopermissions', 'error', '', get_string('entry_edit_title', 'local_monlaututoria'));
}

if ($existing->status !== \local_monlaututoria\domain\entry_status::ACTIVE) {
    throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
}

// Defense in depth: editing a specific student's entry also goes through
// scope_service, on top of the edit capability above.
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$editwindow = (int) get_config('local_monlaututoria', 'entryeditwindow');
$requirereason = time() > ((int) $existing->timecreated + $editwindow);
$showrestricted = has_capability('local/monlaututoria:viewrestrictednotes', $context);
// Same rule already enforced above to reach this page at all (editanyentry,
// or isowner + editownentry) — restated here, same as entries/attachments.php,
// so this stays correct even if the page-level check above is ever relaxed.
// Mirrors the page-level edit gate above (incl. the fase 13 simple-mode path).
$canupload = $caneditany || ($isowner && $caneditown);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/edit.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_edit_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_edit_title', 'local_monlaututoria'));

$student = core_user::get_user((int) $existing->studentid);

$modalityoptions = [];
foreach ((new \local_monlaututoria\repository\modality_repository())->get_all(true) as $modality) {
    $modalityoptions[(int) $modality->id] = format_string($modality->name);
}

$reasonoptions = [];
foreach ((new \local_monlaututoria\repository\reason_repository())->get_all(true) as $reason) {
    $reasonoptions[(int) $reason->id] = format_string($reason->name);
}

$reasonlinkrepository = new \local_monlaututoria\repository\entry_reason_repository();
$currentreasonids = $reasonlinkrepository->get_for_entry($id);

$issop = ($existing->entrykind ?? 'regular') === \local_monlaututoria\domain\entry_kind::SOP;

$form = new \local_monlaututoria\form\entry_edit_form(null, [
    'modalities'         => $modalityoptions,
    'reasons'            => $reasonoptions,
    'studentname'        => $student ? fullname($student) : ('#' . $existing->studentid),
    'entrydateformatted' => userdate((int) $existing->entrydate, get_string('strftimedatefullshort', 'langconfig')),
    'showrestricted'     => $showrestricted && !$issop,
    'requirereason'      => $requirereason,
    'canupload'          => $canupload,
    'sop'                => $issop,
]);

$attachmentdraftitemid = null;
if ($canupload) {
    $attachmentdraftitemid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($attachmentdraftitemid, null, 'user', 'draft', null);
}

$form->set_data((object) array_filter([
    'id'                => $id,
    'modalityid'        => $existing->modalityid,
    'reasonids'         => $currentreasonids,
    'contentvisible'    => $existing->contentvisible ?? '',
    'noteinternal'      => $existing->noteinternal ?? '',
    'recommendationsop' => $issop ? ($existing->recommendationsop ?? '') : null,
    'noterestricted'    => ($showrestricted && !$issop) ? ($existing->noterestricted ?? '') : '',
    'nextfollowupdate'  => $existing->nextfollowupdate,
    'attachments'       => $attachmentdraftitemid,
], static fn ($value) => $value !== null));

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $updatedata = (object) [
        'modalityid'  => !empty($data->modalityid) ? (int) $data->modalityid : null,
        'noteinternal' => $data->noteinternal,
    ];
    if ($issop) {
        $updatedata->recommendationsop = $data->recommendationsop ?? '';
    } else {
        $updatedata->contentvisible = $data->contentvisible;
        $updatedata->nextfollowupdate = !empty($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null;
    }
    if ($showrestricted && !$issop) {
        $updatedata->noterestricted = $data->noterestricted ?? '';
    }

    $service = new \local_monlaututoria\service\entry_service($repository, null, $reasonlinkrepository);
    $service->update(
        $id,
        $updatedata,
        (int) $USER->id,
        $showrestricted,
        $data->reason ?? null,
        array_map('intval', $data->reasonids ?? [])
    );

    if ($canupload && !empty($data->attachments)) {
        // Fase 14 — "Recomendaciones SOP" files live in their own filearea.
        $targetarea = ($data->attachmentcategory ?? '') === \local_monlaututoria\domain\entry_attachment_category::SOP_RECOMMENDATION
            ? \local_monlaututoria\service\entry_attachment_service::FILEAREA_SOP_RECOMMENDATION
            : \local_monlaututoria\service\entry_attachment_service::FILEAREA;
        (new \local_monlaututoria\service\entry_attachment_service($repository))->save_uploaded_files(
            $id, (int) $data->attachments, $data->attachmentcategory, (int) $USER->id, $targetarea
        );
    }

    redirect(
        $returnurl,
        get_string('entry_edit_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_edit_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
