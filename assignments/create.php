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
 * Manual creation of a tutor-student assignment.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:assignstudents', $context);
$canoverridelock = has_capability('local/monlaututoria:overridelock', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/create.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_create_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_create_title', 'local_monlaututoria'));

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

$cohortoptions = [];
foreach ($DB->get_records('cohort', null, 'name ASC', 'id, name') as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}

$form = new \local_monlaututoria\form\assignment_form(null, [
    'academicyears' => $academicyearoptions,
    'cohorts'       => $cohortoptions,
]);

$returnurl = new moodle_url('/local/monlaututoria/assignments/index.php');

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\assignment_service();
    $id = $service->create((object) [
        'studentid'      => (int) $data->studentid,
        'tutorid'        => (int) $data->tutorid,
        'academicyearid' => (int) $data->academicyearid,
        'cohortid'       => !empty($data->cohortid) ? (int) $data->cohortid : null,
        'assignmenttype' => $data->assignmenttype,
        'isprimary'      => !empty($data->isprimary),
        'timestart'      => $data->timestart,
        'timeend'        => !empty($data->timeend) ? $data->timeend : null,
        'note'           => $data->note !== '' ? $data->note : null,
    ], (int) $USER->id, false, $canoverridelock);

    redirect(
        new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $id]),
        get_string('assignment_create_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignment_create_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
