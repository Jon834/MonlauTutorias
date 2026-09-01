<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::COORDINATION);
if (!has_any_capability(['local/monlaututoria:viewcoordinationdashboard', 'local/monlaututoria:viewallassignments'], $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewcoordinationdashboard', 'nopermissions', '');
}

$PAGE->set_context($context);

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$coordscopeservice = new \local_monlaututoria\service\coordination_scope_service();
$dashboardservice = new \local_monlaututoria\service\coordination_dashboard_service();

$availablecohortids = $coordscopeservice->get_effective_cohort_ids((int) $USER->id);
$selectedcohortid = optional_param('cohortid', 0, PARAM_INT);
$selectedtutorid = optional_param('tutorid', 0, PARAM_INT);
$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);
$selectedstudentdepartment = optional_param('studentdepartment', '', PARAM_ALPHA);
$selectedtutordepartment = optional_param('tutordepartment', '', PARAM_ALPHA);

$breakdownsortable = ['label', 'studentcount', 'withinitialcount', 'withoutentrycount', 'overduefollowupcount', 'opencasecount'];
$cohortbreaksort = optional_param('cohortbreaksort', '', PARAM_ALPHA);
if (!in_array($cohortbreaksort, $breakdownsortable, true)) {
    $cohortbreaksort = '';
}
$cohortbreakdir = strtoupper(optional_param('cohortbreakdir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';
$tutorbreaksort = optional_param('tutorbreaksort', '', PARAM_ALPHA);
if (!in_array($tutorbreaksort, $breakdownsortable, true)) {
    $tutorbreaksort = '';
}
$tutorbreakdir = strtoupper(optional_param('tutorbreakdir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';

$academicyear = $requestedacademicyearid > 0 ? $academicyearrepository->get($requestedacademicyearid) : $academicyearrepository->get_active();
if ($academicyear === null) {
    throw new \moodle_exception('error_academicyear_required', 'local_monlaututoria');
}

$selectedcohortids = $selectedcohortid > 0 ? [$selectedcohortid] : $availablecohortids;
$dashboardall = $dashboardservice->get_dashboard(
    (int) $USER->id, (int) $academicyear->id, $selectedcohortids, null, null,
    $selectedstudentdepartment, $selectedtutordepartment
);
$dashboard = $selectedtutorid > 0
    ? $dashboardservice->get_dashboard(
        (int) $USER->id, (int) $academicyear->id, $selectedcohortids, $selectedtutorid, null,
        $selectedstudentdepartment, $selectedtutordepartment
    )
    : $dashboardall;

// coordination_dashboard::$cohortbreakdown/$tutorbreakdown are readonly —
// usort() can't write back into them, so sort local copies instead.
$breakdownsorter = static function (string $sort, string $dir): \Closure {
    return function ($a, $b) use ($sort, $dir): int {
        $result = $sort === 'label' ? strcasecmp($a->label, $b->label) : ($a->{$sort} <=> $b->{$sort});
        return $dir === 'DESC' ? -$result : $result;
    };
};
$cohortbreakdown = $dashboard->cohortbreakdown;
$tutorbreakdown = $dashboard->tutorbreakdown;
if ($cohortbreaksort !== '') {
    usort($cohortbreakdown, $breakdownsorter($cohortbreaksort, $cohortbreakdir));
}
if ($tutorbreaksort !== '') {
    usort($tutorbreakdown, $breakdownsorter($tutorbreaksort, $tutorbreakdir));
}

$breakdownbaseurl = new moodle_url('/local/monlaututoria/coordination.php', array_filter([
    'academicyearid' => $academicyear->id,
    'cohortid' => $selectedcohortid ?: null,
    'tutorid' => $selectedtutorid ?: null,
    'studentdepartment' => $selectedstudentdepartment ?: null,
    'tutordepartment' => $selectedtutordepartment ?: null,
    'cohortbreaksort' => $cohortbreaksort ?: null,
    'cohortbreakdir' => $cohortbreaksort !== '' ? $cohortbreakdir : null,
    'tutorbreaksort' => $tutorbreaksort ?: null,
    'tutorbreakdir' => $tutorbreaksort !== '' ? $tutorbreakdir : null,
], static fn ($value) => $value !== null));

$PAGE->set_url('/local/monlaututoria/coordination.php', [
    'academicyearid' => $academicyear->id,
    'cohortid' => $selectedcohortid,
    'tutorid' => $selectedtutorid,
    'studentdepartment' => $selectedstudentdepartment,
    'tutordepartment' => $selectedtutordepartment,
    'cohortbreaksort' => $cohortbreaksort,
    'cohortbreakdir' => $cohortbreakdir,
    'tutorbreaksort' => $tutorbreaksort,
    'tutorbreakdir' => $tutorbreakdir,
]);
$PAGE->set_title(get_string('coordination_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('coordination_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('coordination');

$headeractions = [];
if (has_capability('local/monlaututoria:managecoordinationscopes', $context)) {
    $headeractions[] = [
        'url' => new moodle_url('/local/monlaututoria/coordination_scopes.php'),
        'label' => get_string('coordination_scope_manage', 'local_monlaututoria'),
        'title' => get_string('coordination_scope_manage_help', 'local_monlaututoria'),
    ];
}

echo $renderer->page_header_card(
    get_string('coordination_title', 'local_monlaututoria'),
    get_string('coordination_dashboard_intro', 'local_monlaututoria'),
    null,
    null,
    $headeractions,
    get_string('pluginname', 'local_monlaututoria')
);

if (empty($availablecohortids)) {
    echo $OUTPUT->notification(get_string('coordination_dashboard_noscope', 'local_monlaututoria'), \core\output\notification::NOTIFY_WARNING);
    echo $OUTPUT->footer();
    return;
}

// Every filter's own URL carries the OTHER current filters, so changing one
// never resets the rest — "mantener filtros al volver a una página".
$commonparams = [
    'academicyearid' => $academicyear->id,
    'cohortid' => $selectedcohortid,
    'tutorid' => $selectedtutorid,
    'studentdepartment' => $selectedstudentdepartment,
    'tutordepartment' => $selectedtutordepartment,
];

$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}
echo $renderer->single_select(
    new moodle_url('/local/monlaututoria/coordination.php', array_diff_key($commonparams, ['academicyearid' => null])),
    'academicyearid', $academicyearoptions, (int) $academicyear->id, null, 'coordinationacademicyearselector'
);

$cohortoptions = [0 => get_string('coordination_cohort_all', 'local_monlaututoria')];
foreach ($dashboardall->cohortlabels as $cohortid => $label) {
    $cohortoptions[$cohortid] = $label;
}
echo html_writer::tag('p', get_string('coordination_filter_help', 'local_monlaututoria'), ['class' => 'text-muted']);
echo $renderer->single_select(
    new moodle_url('/local/monlaututoria/coordination.php', array_diff_key($commonparams, ['cohortid' => null])),
    'cohortid', $cohortoptions, $selectedcohortid, null, 'coordinationcohortselector'
);

$tutoroptions = [0 => get_string('coordination_tutor_all', 'local_monlaututoria')] + $dashboardall->tutoroptions;
echo $renderer->single_select(
    new moodle_url('/local/monlaututoria/coordination.php', array_diff_key($commonparams, ['tutorid' => null])),
    'tutorid', $tutoroptions, $selectedtutorid, null, 'coordinationtutorselector'
);

$departmentoptions = $dashboardservice->get_department_options();
if (!empty($departmentoptions)) {
    $departmentoptionlist = array_combine($departmentoptions, $departmentoptions);

    // Both selects share the same option values (FP/ESO/CORP/MM) — without a
    // visible label, picking a department on either one would look identical
    // on screen with no way to tell which is which (same "Elegir..." problem
    // already fixed elsewhere for single_select — see student/view.php).
    $studentdepartmentselect = new single_select(
        new moodle_url('/local/monlaututoria/coordination.php', array_diff_key($commonparams, ['studentdepartment' => null])),
        'studentdepartment',
        ['' => get_string('coordination_department_all', 'local_monlaututoria')] + $departmentoptionlist,
        $selectedstudentdepartment, null, 'coordinationstudentdepartmentselector'
    );
    $studentdepartmentselect->set_label(get_string('coordination_studentdepartment_label', 'local_monlaututoria'));
    echo $OUTPUT->render($studentdepartmentselect);

    $tutordepartmentselect = new single_select(
        new moodle_url('/local/monlaututoria/coordination.php', array_diff_key($commonparams, ['tutordepartment' => null])),
        'tutordepartment',
        ['' => get_string('coordination_department_all', 'local_monlaututoria')] + $departmentoptionlist,
        $selectedtutordepartment, null, 'coordinationtutordepartmentselector'
    );
    $tutordepartmentselect->set_label(get_string('coordination_tutordepartment_label', 'local_monlaututoria'));
    echo $OUTPUT->render($tutordepartmentselect);
}

echo html_writer::div(
    html_writer::link(new moodle_url('/local/monlaututoria/coordination_export.php', $commonparams + ['format' => 'csv', 'sesskey' => sesskey()]), get_string('coordination_export_csv', 'local_monlaututoria'))
    . ' | '
    . html_writer::link(new moodle_url('/local/monlaututoria/coordination_export.php', $commonparams + ['format' => 'xlsx', 'sesskey' => sesskey()]), get_string('coordination_export_xlsx', 'local_monlaututoria')),
    'mb-3'
);

echo html_writer::tag('p', get_string('coordination_generatedat', 'local_monlaututoria', userdate($dashboard->generatedat, get_string('strftimedatetime', 'langconfig'))));
echo $renderer->coordination_summary_cards($dashboard->summary);
echo $renderer->heading(get_string('coordination_quality_title', 'local_monlaututoria'), 3);
echo $renderer->coordination_quality_cards($dashboard->quality);
echo $renderer->heading(get_string('coordination_breakdown_cohorts', 'local_monlaututoria'), 3);
echo $renderer->coordination_breakdown_table(
    $cohortbreakdown, $cohortbreaksort, $cohortbreakdir, $breakdownbaseurl, 'cohortbreaksort', 'cohortbreakdir'
);
echo $renderer->heading(get_string('coordination_breakdown_tutors', 'local_monlaututoria'), 3);
echo $renderer->coordination_breakdown_table(
    $tutorbreakdown, $tutorbreaksort, $tutorbreakdir, $breakdownbaseurl, 'tutorbreaksort', 'tutorbreakdir'
);

echo $OUTPUT->footer();

