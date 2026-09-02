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
 * SOP tutoring entry registration (fase 14). Only available in simple mode,
 * only for the student's current SOP orientation tutor (assigned as a
 * co_tutor) or for coordination. The resulting entry has entrykind = 'sop'
 * and is never visible to the student.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
// SOP only exists in simple mode.
if (!\local_monlaututoria\feature::simple_mode()) {
    throw new \moodle_exception('error_featuredisabled', 'local_monlaututoria',
        (new moodle_url('/local/monlaututoria/dashboard.php'))->out(false));
}

$assignmentrepo = new \local_monlaututoria\repository\assignment_repository();
$iscoordination = has_capability('local/monlaututoria:viewallassignments', $context);

$studentid = optional_param('studentid', 0, PARAM_INT);
$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyear = $requestedacademicyearid > 0
    ? $academicyearrepository->find($requestedacademicyearid)
    : $academicyearrepository->get_active();
if ($academicyear === null) {
    throw new \moodle_exception('error_invalidacademicyearid', 'local_monlaututoria');
}

if ($studentid <= 0) {
    $pickerids = $iscoordination
        ? []
        : $assignmentrepo->find_current_cotutor_student_ids((int) $USER->id, (int) $academicyear->id);

    if (empty($pickerids) && !$iscoordination) {
        require_capability('local/monlaututoria:viewallassignments', $context);
    }

    $studentoptions = [];
    if (!empty($pickerids)) {
        foreach ($DB->get_records_list('user', 'id', $pickerids, '', 'id, firstname, lastname') as $u) {
            $studentoptions[$u->id] = fullname($u);
        }
        asort($studentoptions);
    }

    $PAGE->set_context($context);
    $PAGE->set_url('/local/monlaututoria/entries/create_sop.php', ['academicyearid' => $academicyear->id]);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title(get_string('entry_sop_pick_student_title', 'local_monlaututoria'));
    $PAGE->set_heading(get_string('entry_sop_pick_student_title', 'local_monlaututoria'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('entry_sop_pick_student_title', 'local_monlaututoria'));
    if (empty($studentoptions)) {
        echo $OUTPUT->notification(
            get_string('entry_sop_pick_student_empty', 'local_monlaututoria'),
            \core\output\notification::NOTIFY_INFO
        );
    } else {
        $picker = new single_select(
            new moodle_url('/local/monlaututoria/entries/create_sop.php', ['academicyearid' => $academicyear->id]),
            'studentid',
            $studentoptions,
            '',
            ['' => get_string('choosedots')],
            'sopstudentpicker'
        );
        $picker->set_label(get_string('entry_pick_student_label', 'local_monlaututoria'));
        $picker->method = 'get';
        echo $OUTPUT->render($picker);
    }
    echo $OUTPUT->footer();
    exit;
}

// Who may record a SOP entry for THIS student.
$issoptutor = $assignmentrepo->is_current_cotutor_of_student((int) $USER->id, $studentid, (int) $academicyear->id);
if (!$iscoordination && !$issoptutor) {
    throw new \moodle_exception('error_sop_not_orientador', 'local_monlaututoria');
}

$student = core_user::get_user($studentid);
if (!$student || !empty($student->deleted)) {
    throw new \moodle_exception('invaliduserid');
}

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/create_sop.php', [
    'studentid' => $studentid, 'academicyearid' => $academicyear->id,
]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_sop_register_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_sop_register_title', 'local_monlaututoria', fullname($student)));

$modalityoptions = [];
foreach ((new \local_monlaututoria\repository\modality_repository())->get_all(true) as $modality) {
    $modalityoptions[(int) $modality->id] = format_string($modality->name);
}
$reasonoptions = [];
foreach ((new \local_monlaututoria\repository\reason_repository())->get_all(true) as $reason) {
    $reasonoptions[(int) $reason->id] = format_string($reason->name);
}

$form = new \local_monlaututoria\form\entry_sop_form(null, [
    'modalities' => $modalityoptions,
    'reasons'    => $reasonoptions,
]);

$attachmentdraftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($attachmentdraftitemid, null, 'user', 'draft', null);

$form->set_data((object) [
    'studentid'      => $studentid,
    'academicyearid' => (int) $academicyear->id,
    'attachments'    => $attachmentdraftitemid,
]);

$returnurl = new moodle_url('/local/monlaututoria/student/view.php', [
    'id' => $studentid, 'academicyearid' => $academicyear->id, 'tab' => 'tutorias',
]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $command = new \local_monlaututoria\domain\entry_create_command(
        (int) $data->studentid,
        (int) $USER->id,
        (int) $data->academicyearid,
        (int) $data->entrydate,
        !empty($data->modalityid) ? (int) $data->modalityid : null,
        null,
        $data->noteinternal !== '' ? $data->noteinternal : null,
        null,
        null,
        !empty($data->reasonid) ? [(int) $data->reasonid] : [],
        [],
        has_capability('local/monlaututoria:overridelock', $context),
        \local_monlaututoria\domain\entry_kind::SOP,
        $data->recommendationsop !== '' ? $data->recommendationsop : null
    );

    $newentryid = (new \local_monlaututoria\service\entry_service())->create($command, (int) $USER->id);

    if (!empty($data->attachments)) {
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
echo $OUTPUT->heading(get_string('entry_sop_register_title', 'local_monlaututoria', fullname($student)));
$form->display();
echo $OUTPUT->footer();
