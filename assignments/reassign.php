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
 * Reassigns the active primary tutor of a student.
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

require_capability('local/monlaututoria:reassignstudents', $context);

if ($existing->status !== \local_monlaututoria\domain\assignment_status::ACTIVE
    || $existing->assignmenttype !== \local_monlaututoria\domain\assignment_type::PRIMARY
    || empty($existing->isprimary)) {
    throw new \moodle_exception('error_assignment_reassign_only_primary', 'local_monlaututoria');
}

$scope = new \local_monlaututoria\service\scope_service($repository);
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/reassign.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_reassign_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_reassign_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$student = core_user::get_user((int) $existing->studentid);
$currenttutor = core_user::get_user((int) $existing->tutorid);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
try {
    $academicyear = $academicyearrepository->get((int) $existing->academicyearid);
} catch (\dml_missing_record_exception $e) {
    $academicyear = null;
}

$dateformat = get_string('strftimedatefullshort', 'langconfig');
$summarylines = [
    get_string('assignment_col_student', 'local_monlaututoria') . ': ' . ($student ? fullname($student) : '#' . $existing->studentid),
    get_string('assignment_col_tutor', 'local_monlaututoria') . ': ' . ($currenttutor ? fullname($currenttutor) : '#' . $existing->tutorid),
    get_string('assignment_col_academicyear', 'local_monlaututoria') . ': ' . ($academicyear ? format_string($academicyear->name) : '—'),
    get_string('assignment_col_timestart', 'local_monlaututoria') . ': ' . userdate($existing->timestart, $dateformat),
];
$summaryhtml = html_writer::alist(array_map('s', $summarylines));

$cotutors = $repository->find_active_cotutors((int) $existing->studentid, (int) $existing->academicyearid);
$warninghtml = html_writer::div(get_string('warning_assignment_reassign', 'local_monlaututoria'), 'alert alert-warning');
if (!empty($cotutors)) {
    $warninghtml .= html_writer::div(
        get_string('warning_assignment_reassign_cotutors', 'local_monlaututoria', count($cotutors)),
        'alert alert-info'
    );
}

$form = new \local_monlaututoria\form\assignment_reassign_form(null, [
    'summaryhtml' => $summaryhtml,
    'warninghtml' => $warninghtml,
]);
$form->set_data((object) [
    'id' => $id,
    'studentid' => (int) $existing->studentid,
    'currenttutorid' => (int) $existing->tutorid,
    'effectivedate' => time(),
    'keepcotutors' => 1,
]);

$returnurl = new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\assignment_service($repository);
    $canoverridelock = has_capability('local/monlaututoria:overridelock', $context);

    $result = $service->reassign_primary_tutor(
        new \local_monlaututoria\domain\reassign_assignment_command(
            (int) $existing->studentid,
            (int) $data->newtutorid,
            (int) $existing->academicyearid,
            $data->reassignreason,
            (int) $data->effectivedate,
            !empty($data->keepcotutors),
            false,
            $canoverridelock
        ),
        (int) $USER->id
    );

    redirect(
        new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $result->newassignmentid]),
        get_string('assignment_reassign_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('assignments', [
    'studentid' => (int) $existing->studentid,
    'studentlabel' => $student ? fullname($student) : get_string('nav_student', 'local_monlaututoria'),
    'academicyearid' => (int) $existing->academicyearid,
]);
echo $renderer->page_header_card(
    get_string('assignment_reassign_title', 'local_monlaututoria'),
    get_string('assignment_reassign_intro', 'local_monlaututoria'),
    $returnurl,
    get_string('page_back_assignments', 'local_monlaututoria'),
    [],
    $student ? fullname($student) : null
);
$form->display();
echo $OUTPUT->footer();
