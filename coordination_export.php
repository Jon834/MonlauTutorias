<?php
require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::COORDINATION);
require_capability('local/monlaututoria:exportcoordinationreports', $context);
if (!has_any_capability(['local/monlaututoria:viewcoordinationdashboard', 'local/monlaututoria:viewallassignments'], $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewcoordinationdashboard', 'nopermissions', '');
}

$format = required_param('format', PARAM_ALPHANUMEXT);
$selectedcohortid = optional_param('cohortid', 0, PARAM_INT);
$selectedtutorid = optional_param('tutorid', 0, PARAM_INT);
$requestedacademicyearid = required_param('academicyearid', PARAM_INT);

$dashboardservice = new \local_monlaututoria\service\coordination_dashboard_service();
$coordscopeservice = new \local_monlaututoria\service\coordination_scope_service();
$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyear = $academicyearrepository->get($requestedacademicyearid);
$cohortids = $selectedcohortid > 0 ? [$selectedcohortid] : $coordscopeservice->get_effective_cohort_ids((int) $USER->id);
$dashboard = $dashboardservice->get_dashboard((int) $USER->id, (int) $academicyear->id, $cohortids, $selectedtutorid > 0 ? $selectedtutorid : null);
$rows = $dashboardservice->build_export_rows($dashboard, $format);

\local_monlaututoria\event\coordination_dashboard_exported::create_from_export((int) $USER->id, (int) $academicyear->id, $cohortids, $format, count($rows), $selectedtutorid > 0 ? $selectedtutorid : null)->trigger();

$columns = [
    'section' => get_string('coordination_export_column_section', 'local_monlaututoria'),
    'label' => get_string('coordination_export_column_label', 'local_monlaututoria'),
    'population' => get_string('coordination_breakdown_population', 'local_monlaututoria'),
    'withinitial' => get_string('coordination_breakdown_withinitial', 'local_monlaututoria'),
    'withoutentry' => get_string('coordination_breakdown_withoutentry', 'local_monlaututoria'),
    'overduefollowups' => get_string('coordination_breakdown_overduefollowups', 'local_monlaututoria'),
    'opencases' => get_string('coordination_breakdown_opencases', 'local_monlaututoria'),
    'generatedat' => get_string('coordination_export_column_generatedat', 'local_monlaututoria'),
    'format' => get_string('coordination_export_column_format', 'local_monlaututoria'),
];

\core\dataformat::download_data('monlau_coordination_' . $academicyear->shortname, $format, array_values($columns), $rows, array_keys($columns));
