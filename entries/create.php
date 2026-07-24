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
 * Quick tutoring entry registration (phase 5.2). The student is always
 * preselected via the "studentid" param (reached from the student's own
 * ficha, student/view.php) — this page never offers a student picker.
 *
 * Security: local/monlaututoria:createentry (capability) +
 * scope_service::require_user_can_access_student() (ambito) — same 2-layer
 * pattern as every other page in this plugin exposing a specific student's
 * data (see docs/seguridad-permisos.md).
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

$form = new \local_monlaututoria\form\entry_quick_form(null, [
    'modalities' => $modalityoptions,
    'reasons'    => $reasonoptions,
]);
$form->set_data((object) ['studentid' => $studentid, 'academicyearid' => (int) $academicyear->id]);

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
    $service->create($command, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('entry_register_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_register_title', 'local_monlaututoria', fullname($student)));
$form->display();
echo $OUTPUT->footer();
