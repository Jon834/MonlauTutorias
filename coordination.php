<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
if (!has_any_capability(['local/monlaututoria:viewcoordinationdashboard', 'local/monlaututoria:viewallassignments'], $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewcoordinationdashboard', 'nopermissions', '');
}

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$coordscopeservice = new \local_monlaututoria\service\coordination_scope_service();
$dashboardservice = new \local_monlaututoria\service\coordination_dashboard_service();

$availablecohortids = $coordscopeservice->get_effective_cohort_ids((int) $USER->id);
$selectedcohortid = optional_param('cohortid', 0, PARAM_INT);
$selectedtutorid = optional_param('tutorid', 0, PARAM_INT);
$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);

$academicyear = $requestedacademicyearid > 0 ? $academicyearrepository->get($requestedacademicyearid) : $academicyearrepository->get_active();
if ($academicyear === null) {
    throw new \moodle_exception('error_academicyear_required', 'local_monlaututoria');
}

$selectedcohortids = $selectedcohortid > 0 ? [$selectedcohortid] : $availablecohortids;
$dashboardall = $dashboardservice->get_dashboard((int) $USER->id, (int) $academicyear->id, $selectedcohortids, null);
$dashboard = $selectedtutorid > 0
    ? $dashboardservice->get_dashboard((int) $USER->id, (int) $academicyear->id, $selectedcohortids, $selectedtutorid)
    : $dashboardall;

admin_externalpage_setup('local_monlaututoria_coordination');
$PAGE->set_url('/local/monlaututoria/coordination.php', ['academicyearid' => $academicyear->id, 'cohortid' => $selectedcohortid, 'tutorid' => $selectedtutorid]);
$PAGE->set_title(get_string('coordination_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('coordination_title', 'local_monlaututoria'));
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coordination_title', 'local_monlaututoria'));

if (empty($availablecohortids)) {
    echo $OUTPUT->notification(get_string('coordination_dashboard_noscope', 'local_monlaututoria'), \core\output\notification::NOTIFY_WARNING);
    echo $OUTPUT->footer();
    return;
}

$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}
echo $renderer->single_select(new moodle_url('/local/monlaututoria/coordination.php', ['cohortid' => $selectedcohortid, 'tutorid' => $selectedtutorid]), 'academicyearid', $academicyearoptions, (int) $academicyear->id, null, 'coordinationacademicyearselector');

$cohortoptions = [0 => get_string('coordination_cohort_all', 'local_monlaututoria')];
foreach ($dashboardall->cohortlabels as $cohortid => $label) {
    $cohortoptions[$cohortid] = $label;
}
echo $renderer->single_select(new moodle_url('/local/monlaututoria/coordination.php', ['academicyearid' => $academicyear->id, 'tutorid' => $selectedtutorid]), 'cohortid', $cohortoptions, $selectedcohortid, null, 'coordinationcohortselector');

$tutoroptions = [0 => get_string('coordination_tutor_all', 'local_monlaututoria')] + $dashboardall->tutoroptions;
echo $renderer->single_select(new moodle_url('/local/monlaututoria/coordination.php', ['academicyearid' => $academicyear->id, 'cohortid' => $selectedcohortid]), 'tutorid', $tutoroptions, $selectedtutorid, null, 'coordinationtutorselector');

echo html_writer::div(
    html_writer::link(new moodle_url('/local/monlaututoria/coordination_export.php', ['academicyearid' => $academicyear->id, 'cohortid' => $selectedcohortid, 'tutorid' => $selectedtutorid, 'format' => 'csv', 'sesskey' => sesskey()]), get_string('coordination_export_csv', 'local_monlaututoria'))
    . ' | '
    . html_writer::link(new moodle_url('/local/monlaututoria/coordination_export.php', ['academicyearid' => $academicyear->id, 'cohortid' => $selectedcohortid, 'tutorid' => $selectedtutorid, 'format' => 'xlsx', 'sesskey' => sesskey()]), get_string('coordination_export_xlsx', 'local_monlaututoria')),
    'mb-3'
);

echo html_writer::tag('p', get_string('coordination_generatedat', 'local_monlaututoria', userdate($dashboard->generatedat, get_string('strftimedatetime', 'langconfig'))));
echo $renderer->coordination_summary_cards($dashboard->summary);
echo $renderer->heading(get_string('coordination_quality_title', 'local_monlaututoria'), 3);
echo $renderer->coordination_quality_cards($dashboard->quality);
echo $renderer->heading(get_string('coordination_breakdown_cohorts', 'local_monlaututoria'), 3);
echo $renderer->coordination_breakdown_table($dashboard->cohortbreakdown);
echo $renderer->heading(get_string('coordination_breakdown_tutors', 'local_monlaututoria'), 3);
echo $renderer->coordination_breakdown_table($dashboard->tutorbreakdown);

echo $OUTPUT->footer();
