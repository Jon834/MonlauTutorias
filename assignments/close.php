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
 * Closes an active assignment: shows a summary and (when applicable) a
 * warning before asking for a coded reason and explicit confirmation.
 * Never available for co-tutor rows — those are removed through co-tutor
 * management instead (phase 3B.3B/C).
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

if ($existing->status !== \local_monlaututoria\domain\assignment_status::ACTIVE) {
    throw new \moodle_exception('error_assignment_already_closed', 'local_monlaututoria');
}
if ($existing->assignmenttype === \local_monlaututoria\domain\assignment_type::CO_TUTOR) {
    throw new \moodle_exception('error_assignment_close_use_remove_cotutor', 'local_monlaututoria');
}

// Defense in depth: closing a specific student's assignment also goes
// through scope_service, on top of the manageassignments capability above.
$scope = new \local_monlaututoria\service\scope_service($repository);
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/close.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_close_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_close_title', 'local_monlaututoria'));

$student = core_user::get_user((int) $existing->studentid);
$tutor = core_user::get_user((int) $existing->tutorid);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
try {
    $academicyear = $academicyearrepository->get((int) $existing->academicyearid);
} catch (\dml_missing_record_exception $e) {
    $academicyear = null;
}

$typeoptions = \local_monlaututoria\domain\assignment_type::get_options();
$dateformat = get_string('strftimedatefullshort', 'langconfig');

$summarylines = [
    get_string('assignment_col_student', 'local_monlaututoria') . ': '
        . ($student ? fullname($student) : '#' . $existing->studentid),
    get_string('assignment_col_tutor', 'local_monlaututoria') . ': '
        . ($tutor ? fullname($tutor) : '#' . $existing->tutorid),
    get_string('assignment_col_type', 'local_monlaututoria') . ': '
        . ($typeoptions[$existing->assignmenttype] ?? $existing->assignmenttype),
    get_string('assignment_col_academicyear', 'local_monlaututoria') . ': '
        . ($academicyear ? format_string($academicyear->name) : '—'),
    get_string('assignment_col_timestart', 'local_monlaututoria') . ': '
        . userdate($existing->timestart, $dateformat),
];
$summaryhtml = html_writer::alist(array_map('s', $summarylines));

$willleavewithoutprimary = $existing->assignmenttype === \local_monlaututoria\domain\assignment_type::PRIMARY
    && !empty($existing->isprimary);
$warninghtml = $willleavewithoutprimary
    ? html_writer::div(get_string('warning_assignment_close_no_primary', 'local_monlaututoria'), 'alert alert-warning')
    : '';

$form = new \local_monlaututoria\form\assignment_close_form(null, [
    'summaryhtml'            => $summaryhtml,
    'warningwithoutprimary'  => $warninghtml,
]);
$form->set_data((object) [
    'id'      => $id,
    'timeend' => time(),
    'note'    => $existing->note ?? '',
]);

$returnurl = new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\assignment_service($repository);
    $service->close(
        $id,
        (int) $USER->id,
        $data->closereason,
        (int) $data->timeend,
        $data->note !== '' ? $data->note : null
    );

    $successkey = $willleavewithoutprimary ? 'assignment_close_success_no_primary' : 'assignment_close_success';
    redirect(
        $returnurl,
        get_string($successkey, 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignment_close_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
