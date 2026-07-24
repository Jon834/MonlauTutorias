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

$isowner = ((int) $existing->createdby === (int) $USER->id);
$caneditany = has_capability('local/monlaututoria:editanyentry', $context);
if (!$caneditany && !($isowner && has_capability('local/monlaututoria:editownentry', $context))) {
    throw new \moodle_exception('nopermissions', 'error', '', get_string('entry_edit_title', 'local_monlaututoria'));
}

if ($existing->status !== \local_monlaututoria\domain\entry_status::ACTIVE) {
    throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
}

// Defense in depth: editing a specific student's entry also goes through
// scope_service, on top of the edit capability above.
$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$editwindow = (int) get_config('local_monlaututoria', 'entryeditwindow');
$requirereason = time() > ((int) $existing->timecreated + $editwindow);
$showrestricted = has_capability('local/monlaututoria:viewrestrictednotes', $context);

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

$form = new \local_monlaututoria\form\entry_edit_form(null, [
    'modalities'         => $modalityoptions,
    'studentname'        => $student ? fullname($student) : ('#' . $existing->studentid),
    'entrydateformatted' => userdate((int) $existing->entrydate, get_string('strftimedatefullshort', 'langconfig')),
    'showrestricted'     => $showrestricted,
    'requirereason'      => $requirereason,
]);
$form->set_data((object) [
    'id'               => $id,
    'modalityid'       => $existing->modalityid,
    'contentvisible'   => $existing->contentvisible ?? '',
    'noteinternal'     => $existing->noteinternal ?? '',
    'noterestricted'   => $showrestricted ? ($existing->noterestricted ?? '') : '',
    'nextfollowupdate' => $existing->nextfollowupdate,
]);

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $updatedata = (object) [
        'modalityid'       => !empty($data->modalityid) ? (int) $data->modalityid : null,
        'contentvisible'   => $data->contentvisible,
        'noteinternal'     => $data->noteinternal,
        'nextfollowupdate' => !empty($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null,
    ];
    if ($showrestricted) {
        $updatedata->noterestricted = $data->noterestricted ?? '';
    }

    $service = new \local_monlaututoria\service\entry_service($repository);
    $service->update($id, $updatedata, (int) $USER->id, $showrestricted, $data->reason ?? null);

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
