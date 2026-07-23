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
 * Edits the editable fields of an existing assignment (cohort, dates, note).
 * Student, tutor, type and status are never editable here.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$id = required_param('id', PARAM_INT);

$repository = new \local_monlaututoria\repository\assignment_repository();
$existing = $repository->get($id);

require_capability('local/monlaututoria:manageassignments', $context);

$canmanageclosed = has_capability('local/monlaututoria:manageclosedassignments', $context);
$editingclosed = $existing->status !== \local_monlaututoria\domain\assignment_status::ACTIVE;
if ($editingclosed && !$canmanageclosed) {
    throw new \moodle_exception('error_assignment_closed_no_permission', 'local_monlaututoria');
}

// Defense in depth: editing a specific student's assignment also goes through
// scope_service, on top of the manageassignments capability above.
$scope = new \local_monlaututoria\service\scope_service($repository);
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/edit.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_edit_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_edit_title', 'local_monlaututoria'));

$student = core_user::get_user((int) $existing->studentid);
$tutor = core_user::get_user((int) $existing->tutorid);

$cohortoptions = [];
foreach ($DB->get_records('cohort', null, 'name ASC', 'id, name') as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}

$form = new \local_monlaututoria\form\assignment_edit_form(null, [
    'cohorts'       => $cohortoptions,
    'studentname'   => $student ? fullname($student) : ('#' . $existing->studentid),
    'tutorname'     => $tutor ? fullname($tutor) : ('#' . $existing->tutorid),
    'requirereason' => $editingclosed,
]);
$form->set_data((object) [
    'id'        => $id,
    'cohortid'  => $existing->cohortid,
    'timestart' => $existing->timestart,
    'timeend'   => $existing->timeend,
    'note'      => $existing->note ?? '',
]);

$returnurl = new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $canoverridelock = has_capability('local/monlaututoria:overridelock', $context);

    $service = new \local_monlaututoria\service\assignment_service($repository);
    $service->update($id, (object) [
        'cohortid'  => !empty($data->cohortid) ? (int) $data->cohortid : null,
        'timestart' => $data->timestart,
        'timeend'   => !empty($data->timeend) ? $data->timeend : null,
        'note'      => $data->note !== '' ? $data->note : null,
    ], (int) $USER->id, $canmanageclosed, $canoverridelock, $data->reason ?? null);

    redirect(
        $returnurl,
        get_string('assignment_update_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignment_edit_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
