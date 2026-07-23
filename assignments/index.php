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
 * Paginated, filterable listing of tutor-student assignments.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$viewcaps = ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents'];
if (!has_any_capability($viewcaps, $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewownstudents', 'nopermissions', '');
}

$canviewall = has_capability('local/monlaututoria:viewallassignments', $context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;

$filters = [];

$academicyearid = optional_param('academicyearid', 0, PARAM_INT);
if ($academicyearid > 0) {
    $filters['academicyearid'] = $academicyearid;
}

$assignmenttype = optional_param('assignmenttype', '', PARAM_ALPHAEXT);
if (in_array($assignmenttype, \local_monlaututoria\domain\assignment_type::values(), true)) {
    $filters['assignmenttype'] = $assignmenttype;
}

$status = optional_param('status', '', PARAM_ALPHA);
if (in_array($status, \local_monlaututoria\domain\assignment_status::values(), true)) {
    $filters['status'] = $status;
}

$source = optional_param('source', '', PARAM_ALPHA);
if (in_array($source, \local_monlaututoria\domain\assignment_source::values(), true)) {
    $filters['source'] = $source;
}

$cohortid = optional_param('cohortid', 0, PARAM_INT);
if ($cohortid > 0) {
    $filters['cohortid'] = $cohortid;
}

$studentid = optional_param('studentid', 0, PARAM_INT);
if ($studentid > 0) {
    $filters['studentid'] = $studentid;
}

$tutorid = optional_param('tutorid', 0, PARAM_INT);
if ($tutorid > 0) {
    $filters['tutorid'] = $tutorid;
}

foreach (['timestartfrom', 'timestartto', 'timeendfrom', 'timeendto'] as $datekey) {
    $value = optional_param($datekey, 0, PARAM_INT);
    if ($value > 0) {
        $filters[$datekey] = $value;
    }
}

// Scope: without viewallassignments, a tutor only ever sees their own
// students, regardless of any tutorid supplied in the URL (prevents IDOR).
if (!$canviewall) {
    $filters['tutorid'] = (int) $USER->id;
}

$PAGE->set_url('/local/monlaututoria/assignments/index.php', $filters + ['page' => $page]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignments', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignments', 'local_monlaututoria'));

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

$cohortoptions = [];
foreach ($DB->get_records('cohort', null, 'name ASC', 'id, name') as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}

$filterform = new \local_monlaututoria\form\assignment_filter_form(
    $PAGE->url,
    ['academicyears' => $academicyearoptions, 'cohorts' => $cohortoptions],
    'get'
);
// The GET filter values already come from optional_param() above; set_data()
// here only pre-fills the form fields for redisplay, it does not gate the query.
$filterform->set_data($filters);

$repository = new \local_monlaututoria\repository\assignment_repository();
$totalcount = $repository->count_search($filters);
$records = $repository->search($filters, $page * $perpage, $perpage);

// Batch-fetch display data for this page only, to avoid N+1 and avoid
// loading full user profiles for the whole system.
$studentids = [];
$tutorids = [];
$cohortids = [];
$academicyearids = [];
foreach ($records as $record) {
    $studentids[(int) $record->studentid] = true;
    $tutorids[(int) $record->tutorid] = true;
    if (!empty($record->cohortid)) {
        $cohortids[(int) $record->cohortid] = true;
    }
    $academicyearids[(int) $record->academicyearid] = true;
}

$userids = array_unique(array_merge(array_keys($studentids), array_keys($tutorids)));
$users = !empty($userids) ? $DB->get_records_list('user', 'id', $userids, '', 'id, firstname, lastname, email') : [];
$cohorts = !empty($cohortids) ? $DB->get_records_list('cohort', 'id', array_keys($cohortids), '', 'id, name') : [];

$academicyears = [];
foreach (array_keys($academicyearids) as $ayid) {
    try {
        $academicyears[$ayid] = $academicyearrepository->get($ayid);
    } catch (\dml_missing_record_exception $e) {
        // A dangling reference should not break the whole listing.
        continue;
    }
}

$cotutorrecords = $repository->get_cotutors_for_students(array_keys($studentids));
$cotutorsbystudent = [];
foreach ($cotutorrecords as $cotutor) {
    $tutor = $users[$cotutor->tutorid] ?? null;
    $cotutorsbystudent[(int) $cotutor->studentid][] = $tutor ? fullname($tutor) : ('#' . $cotutor->tutorid);
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$typeoptions = \local_monlaututoria\domain\assignment_type::get_options();
$sourceoptions = \local_monlaututoria\domain\assignment_source::get_options();
$dateformat = get_string('strftimedatefullshort', 'langconfig');

$rows = [];
foreach ($records as $record) {
    $student = $users[$record->studentid] ?? null;
    $tutor = $users[$record->tutorid] ?? null;
    $cohort = !empty($record->cohortid) ? ($cohorts[$record->cohortid] ?? null) : null;
    $academicyear = $academicyears[$record->academicyearid] ?? null;
    $cotutornames = $cotutorsbystudent[(int) $record->studentid] ?? [];

    $badge = $renderer->status_badge_data($record->status, (int) $record->timestart);

    $rows[] = $badge + [
        'studentname'        => $student ? fullname($student) : ('#' . $record->studentid),
        'tutorname'          => $tutor ? fullname($tutor) : ('#' . $record->tutorid),
        'cotutornames'       => !empty($cotutornames) ? implode(', ', $cotutornames) : '—',
        'cohortname'         => $cohort ? format_string($cohort->name) : '—',
        'academicyearname'   => $academicyear ? format_string($academicyear->name) : '—',
        'typelabel'          => $typeoptions[$record->assignmenttype] ?? $record->assignmenttype,
        'timestartformatted' => userdate($record->timestart, $dateformat),
        'timeendformatted'   => !empty($record->timeend) ? userdate($record->timeend, $dateformat) : '—',
        'sourcelabel'        => $sourceoptions[$record->source] ?? $record->source,
        'detailurl'          => (new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $record->id]))->out(false),
        'viewdetaillabel'    => get_string('assignment_viewdetail', 'local_monlaututoria'),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignments', 'local_monlaututoria'));

$filterform->display();

echo $renderer->assignments_list($rows);

echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);

echo $OUTPUT->footer();
