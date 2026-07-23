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
 * Detail view of a single tutor-student assignment, plus a basic history of
 * the student's assignments.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:viewstudent', $context);

$id = required_param('id', PARAM_INT);

$repository = new \local_monlaututoria\repository\assignment_repository();
$assignment = $repository->get($id);

$scope = new \local_monlaututoria\service\scope_service($repository);
$scope->require_user_can_access_student((int) $USER->id, (int) $assignment->studentid, (int) $assignment->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/view.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_detail_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_detail_title', 'local_monlaututoria'));

\local_monlaututoria\event\assignment_viewed::create_from_id(
    $id,
    (int) $USER->id,
    (int) $assignment->studentid,
    (int) $assignment->academicyearid
)->trigger();

$student = core_user::get_user((int) $assignment->studentid);
$tutor = core_user::get_user((int) $assignment->tutorid);
$createdby = core_user::get_user((int) $assignment->createdby);
$modifiedby = core_user::get_user((int) $assignment->modifiedby);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
try {
    $academicyear = $academicyearrepository->get((int) $assignment->academicyearid);
} catch (\dml_missing_record_exception $e) {
    $academicyear = null;
}

$cohort = null;
if (!empty($assignment->cohortid)) {
    $cohort = $DB->get_record('cohort', ['id' => $assignment->cohortid]);
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$dateformat = get_string('strftimedatefullshort', 'langconfig');
$datetimeformat = get_string('strftimedatetimeshort', 'langconfig');

$typeoptions = \local_monlaututoria\domain\assignment_type::get_options();
$sourceoptions = \local_monlaututoria\domain\assignment_source::get_options();

$badge = $renderer->status_badge_data($assignment->status, (int) $assignment->timestart);

$canmanageclosed = has_capability('local/monlaututoria:manageclosedassignments', $context);
$canmanageassignments = has_capability('local/monlaututoria:manageassignments', $context);
$isactive = $assignment->status === \local_monlaututoria\domain\assignment_status::ACTIVE;
$canedit = $canmanageassignments && ($isactive || $canmanageclosed);
$canclose = $canmanageassignments && $isactive
    && $assignment->assignmenttype !== \local_monlaututoria\domain\assignment_type::CO_TUTOR;

$closereasonoptions = \local_monlaututoria\domain\assignment_close_reason::get_options();

$detaildata = (object) ($badge + [
    'studentname'         => $student ? fullname($student) : ('#' . $assignment->studentid),
    'tutorname'           => $tutor ? fullname($tutor) : ('#' . $assignment->tutorid),
    'typelabel'           => $typeoptions[$assignment->assignmenttype] ?? $assignment->assignmenttype,
    'academicyearname'    => $academicyear ? format_string($academicyear->name) : '—',
    'cohortname'          => $cohort ? format_string($cohort->name) : '—',
    'timestartformatted'  => userdate($assignment->timestart, $dateformat),
    'timeendformatted'    => !empty($assignment->timeend) ? userdate($assignment->timeend, $dateformat) : '—',
    'sourcelabel'         => $sourceoptions[$assignment->source] ?? $assignment->source,
    'noteformatted'       => !empty($assignment->note) ? format_text($assignment->note, FORMAT_PLAIN) : '—',
    'closereasonlabel'    => !empty($assignment->closereason)
        ? ($closereasonoptions[$assignment->closereason] ?? $assignment->closereason)
        : '—',
    'createdbyname'       => $createdby ? fullname($createdby) : ('#' . $assignment->createdby),
    'createdonformatted'  => userdate($assignment->timecreated, $datetimeformat),
    'modifiedbyname'      => $modifiedby ? fullname($modifiedby) : ('#' . $assignment->modifiedby),
    'modifiedonformatted' => userdate($assignment->timemodified, $datetimeformat),
    'canedit'             => $canedit,
    'editurl'             => $canedit
        ? (new moodle_url('/local/monlaututoria/assignments/edit.php', ['id' => $id]))->out(false)
        : '',
    'editlabel'           => get_string('assignment_edit', 'local_monlaututoria'),
    'canclose'            => $canclose,
    'closeurl'            => $canclose
        ? (new moodle_url('/local/monlaututoria/assignments/close.php', ['id' => $id]))->out(false)
        : '',
    'closelabel'          => get_string('assignment_close', 'local_monlaututoria'),
]);

// Basic history: every assignment for this student, most recent first.
// find_by_student() orders ASC by timestart, so reverse it here.
$historyrecords = array_reverse($repository->find_by_student((int) $assignment->studentid));

$historytutorids = array_unique(array_map(static fn ($record) => (int) $record->tutorid, $historyrecords));
$historytutors = !empty($historytutorids)
    ? $DB->get_records_list('user', 'id', $historytutorids, '', 'id, firstname, lastname, email')
    : [];

$historyentries = [];
foreach ($historyrecords as $entry) {
    $entrytutor = $historytutors[$entry->tutorid] ?? null;
    $entrybadge = $renderer->status_badge_data($entry->status, (int) $entry->timestart);

    $historyentries[] = $entrybadge + [
        'tutorname'          => $entrytutor ? fullname($entrytutor) : ('#' . $entry->tutorid),
        'typelabel'          => $typeoptions[$entry->assignmenttype] ?? $entry->assignmenttype,
        'timestartformatted' => userdate($entry->timestart, $dateformat),
        'timeendformatted'   => !empty($entry->timeend) ? userdate($entry->timeend, $dateformat) : '—',
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignment_detail_title', 'local_monlaututoria'));

echo $renderer->assignment_detail($detaildata);

echo $renderer->assignment_history($historyentries);

echo $OUTPUT->footer();
