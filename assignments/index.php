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

// Fase 13 — in simple mode the assignments listing is coordination-only
// (viewallassignments). A plain tutor (viewownstudents) works from the panel
// and the student ficha, and never touches assignment records.
$viewcaps = \local_monlaututoria\feature::simple_mode()
    ? ['local/monlaututoria:viewallassignments']
    : ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents'];
if (!has_any_capability($viewcaps, $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewallassignments', 'nopermissions', '');
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

$sort = optional_param('sort', '', PARAM_ALPHA);
if (!in_array($sort, \local_monlaututoria\repository\assignment_repository::sortable_columns(), true)) {
    $sort = 'timestart';
}
$dir = strtoupper(optional_param('dir', 'DESC', PARAM_ALPHA)) === 'ASC' ? 'ASC' : 'DESC';

$PAGE->set_url('/local/monlaututoria/assignments/index.php', $filters + ['page' => $page, 'sort' => $sort, 'dir' => $dir]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignments', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignments', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

$repository = new \local_monlaututoria\repository\assignment_repository();

// Filterable cohorts: globally enabled ones (cohort_visibility.php), plus
// any cohort with at least one existing assignment even if since disabled
// — hiding a cohort from creation flows should never make existing data
// harder to find in this filter.
$filterablecohortids = array_unique(array_merge(
    (new \local_monlaututoria\service\cohort_visibility_service())->get_visible_cohort_ids(),
    $repository->get_distinct_cohort_ids()
));
$cohortoptions = [];
foreach ((new \local_monlaututoria\repository\cohort_repository())->get_many($filterablecohortids) as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}
asort($cohortoptions);

$filterform = new \local_monlaututoria\form\assignment_filter_form(
    $PAGE->url,
    ['academicyears' => $academicyearoptions, 'cohorts' => $cohortoptions],
    'get'
);
$filterform->set_data($filters);

$totalcount = $repository->count_search($filters);
$records = $repository->search($filters, $page * $perpage, $perpage, $sort, $dir);

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
$academicyears = $academicyearrepository->get_many(array_keys($academicyearids));

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

$canmanageassignments = has_capability('local/monlaututoria:manageassignments', $context);
$canmanageclosed = has_capability('local/monlaututoria:manageclosedassignments', $context);
$canassignstudents = has_capability('local/monlaututoria:assignstudents', $context);
$canreassignstudents = has_any_capability(['local/monlaututoria:reassignstudents', 'local/monlaututoria:manageassignments'], $context)
    && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::COTUTORS);

$rows = [];
foreach ($records as $record) {
    $student = $users[$record->studentid] ?? null;
    $tutor = $users[$record->tutorid] ?? null;
    $cohort = !empty($record->cohortid) ? ($cohorts[$record->cohortid] ?? null) : null;
    $academicyear = $academicyears[$record->academicyearid] ?? null;
    $cotutornames = $cotutorsbystudent[(int) $record->studentid] ?? [];

    $badge = $renderer->status_badge_data($record->status, (int) $record->timestart);

    $isactive = $record->status === \local_monlaututoria\domain\assignment_status::ACTIVE;
    $canedit = $canmanageassignments && ($isactive || $canmanageclosed);
    $canclose = $canmanageassignments && $isactive
        && $record->assignmenttype !== \local_monlaututoria\domain\assignment_type::CO_TUTOR;
    $canreassign = $canreassignstudents && $isactive
        && $record->assignmenttype === \local_monlaututoria\domain\assignment_type::PRIMARY
        && !empty($record->isprimary);

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
        'studentfichaurl'    => (new moodle_url('/local/monlaututoria/student/view.php', ['id' => $record->studentid]))->out(false),
        'studentfichalabel'  => get_string('student_viewficha', 'local_monlaututoria'),
        // Fase 14 — coordination can jump to this tutor's own panel.
        'cantutorpanel'      => $canviewall,
        'tutorpanelurl'      => $canviewall
            ? (new moodle_url('/local/monlaututoria/dashboard.php', ['tutorid' => $record->tutorid]))->out(false)
            : '',
        'tutorpanellabel'    => get_string('dashboard_viewtutorpanel_action', 'local_monlaututoria'),
        'canedit'            => $canedit,
        'editurl'            => $canedit
            ? (new moodle_url('/local/monlaututoria/assignments/edit.php', ['id' => $record->id]))->out(false)
            : '',
        'editlabel'          => get_string('assignment_edit', 'local_monlaututoria'),
        'canclose'           => $canclose,
        'closeurl'           => $canclose
            ? (new moodle_url('/local/monlaututoria/assignments/close.php', ['id' => $record->id]))->out(false)
            : '',
        'closelabel'         => get_string('assignment_close', 'local_monlaututoria'),
        'canreassign'        => $canreassign,
        'reassignurl'        => $canreassign
            ? (new moodle_url('/local/monlaututoria/assignments/reassign.php', ['id' => $record->id]))->out(false)
            : '',
        'reassignlabel'      => get_string('assignment_reassign', 'local_monlaututoria'),
    ];
}

echo $OUTPUT->header();
echo $renderer->plugin_navigation('assignments');

$headeractions = [];
if ($canassignstudents) {
    $headeractions[] = [
        'url' => new moodle_url('/local/monlaututoria/assignments/create.php'),
        'label' => get_string('assignment_create', 'local_monlaututoria'),
        'title' => get_string('assignments_create_tip', 'local_monlaututoria'),
    ];
}
if (has_capability('local/monlaututoria:managecohortassignments', $context)) {
    $headeractions[] = [
        'url' => new moodle_url('/local/monlaututoria/assignments/cohort_create.php'),
        'label' => get_string('cohort_assignment_create', 'local_monlaututoria'),
        'title' => get_string('cohort_assignment_create_tip', 'local_monlaututoria'),
    ];
}

echo $renderer->page_header_card(
    get_string('assignments', 'local_monlaututoria'),
    get_string('assignments_intro', 'local_monlaututoria'),
    null,
    null,
    $headeractions,
    get_string('pluginname', 'local_monlaututoria')
);

$filterform->display();

$sortbaseurl = new moodle_url('/local/monlaututoria/assignments/index.php', $filters + ['page' => $page]);
echo $renderer->assignments_list($rows, $sort, $dir, $sortbaseurl);

echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);

echo $OUTPUT->footer();
