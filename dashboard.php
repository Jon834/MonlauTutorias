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
 * Tutor dashboard (phase 7 complete).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
if (!has_any_capability(['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments'], $context)) {
    require_capability('local/monlaututoria:viewownstudents', $context);
}

$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);
$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
if ($requestedacademicyearid > 0) {
    $academicyear = $academicyearrepository->find($requestedacademicyearid);
    if ($academicyear === null) {
        throw new \moodle_exception('error_invalidacademicyearid', 'local_monlaututoria');
    }
} else {
    $academicyear = $academicyearrepository->get_active();
}

$validstudentfilters = ['all', 'pendinginitial', 'withpending', 'priority'];
$validpendingfilters = ['all', 'open', 'overdue'];
$studentfilter = optional_param(
    'studentfilter',
    get_user_preferences('local_monlaututoria_dashboard_studentfilter', 'all'),
    PARAM_ALPHA
);
if (!in_array($studentfilter, $validstudentfilters, true)) {
    $studentfilter = 'all';
}
set_user_preference('local_monlaututoria_dashboard_studentfilter', $studentfilter);

$pendingfilter = optional_param(
    'pendingfilter',
    get_user_preferences('local_monlaututoria_dashboard_pendingfilter', 'all'),
    PARAM_ALPHA
);
if (!in_array($pendingfilter, $validpendingfilters, true)) {
    $pendingfilter = 'all';
}
set_user_preference('local_monlaututoria_dashboard_pendingfilter', $pendingfilter);

$urlparams = ['studentfilter' => $studentfilter, 'pendingfilter' => $pendingfilter];
if ($requestedacademicyearid > 0) {
    $urlparams['academicyearid'] = $requestedacademicyearid;
}
$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/dashboard.php', $urlparams);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('dashboard_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('dashboard_title', 'local_monlaututoria'));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}
$studentfilteroptions = [
    'all' => get_string('dashboard_studentfilter_all', 'local_monlaututoria'),
    'pendinginitial' => get_string('dashboard_studentfilter_pendinginitial', 'local_monlaututoria'),
    'withpending' => get_string('dashboard_studentfilter_withpending', 'local_monlaututoria'),
    'priority' => get_string('dashboard_studentfilter_priority', 'local_monlaututoria'),
];
$pendingfilteroptions = [
    'all' => get_string('dashboard_pendingfilter_all', 'local_monlaututoria'),
    'open' => get_string('dashboard_pendingfilter_open', 'local_monlaututoria'),
    'overdue' => get_string('dashboard_pendingfilter_overdue', 'local_monlaututoria'),
];

$cancreateentry = has_capability('local/monlaututoria:createentry', $context);
$cancreatefollowup = has_capability('local/monlaututoria:createfollowup', $context);
$canmanageagreements = has_capability('local/monlaututoria:manageagreements', $context);
$canmanagefollowups = has_capability('local/monlaututoria:managefollowups', $context);
$canmanagereferrals = has_capability('local/monlaututoria:managereferrals', $context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dashboard_title', 'local_monlaututoria'));

if (!empty($academicyearoptions)) {
    echo $OUTPUT->single_select(
        new moodle_url('/local/monlaututoria/dashboard.php', [
            'studentfilter' => $studentfilter,
            'pendingfilter' => $pendingfilter,
        ]),
        'academicyearid',
        $academicyearoptions,
        $academicyear !== null ? (int) $academicyear->id : '',
        ['' => get_string('choosedots')],
        'dashboardacademicyearselector'
    );
}

echo $OUTPUT->single_select(
    new moodle_url('/local/monlaututoria/dashboard.php', array_filter([
        'academicyearid' => $requestedacademicyearid ?: null,
        'pendingfilter' => $pendingfilter,
    ])),
    'studentfilter',
    $studentfilteroptions,
    $studentfilter,
    [],
    'dashboardstudentfilterselector'
);
echo $OUTPUT->single_select(
    new moodle_url('/local/monlaututoria/dashboard.php', array_filter([
        'academicyearid' => $requestedacademicyearid ?: null,
        'studentfilter' => $studentfilter,
    ])),
    'pendingfilter',
    $pendingfilteroptions,
    $pendingfilter,
    [],
    'dashboardpendingfilterselector'
);

if ($academicyear === null) {
    echo $renderer->noactiveacademicyear_warning();
    echo $OUTPUT->footer();
    exit;
}

$dashboard = (new \local_monlaututoria\service\dashboard_service())
    ->get_tutor_dashboard((int) $USER->id, (int) $academicyear->id);

$filteredstudents = $dashboard->students;
if ($studentfilter === 'pendinginitial') {
    $filteredstudents = array_values(array_filter($filteredstudents, static fn ($student): bool => $student->missinginitial));
} else if ($studentfilter === 'withpending') {
    $filteredstudents = array_values(array_filter(
        $filteredstudents,
        static fn ($student): bool => ($student->openfollowupcount + $student->openagreementcount + $student->openreferralcount) > 0
    ));
} else if ($studentfilter === 'priority') {
    $filteredstudents = $dashboard->prioritystudents;
}

$upcomingfollowups = $dashboard->upcomingfollowups;
$overduefollowups = $dashboard->overduefollowups;
$pendingagreements = $dashboard->pendingagreements;
$overdueagreements = $dashboard->overdueagreements;
$referrals = $dashboard->referrals;
if ($pendingfilter === 'open') {
    $overduefollowups = [];
    $overdueagreements = [];
} else if ($pendingfilter === 'overdue') {
    $upcomingfollowups = [];
    $pendingagreements = [];
}

$studentids = array_values(array_unique(array_map(static fn ($row): int => $row->studentid, $dashboard->students)));
$studentusers = !empty($studentids)
    ? $DB->get_records_list('user', 'id', $studentids, '', 'id, firstname, lastname, email')
    : [];
$responsibleuserids = [];
foreach (array_merge($dashboard->pendingagreements, $dashboard->overdueagreements) as $agreement) {
    if ($agreement->responsibleuserid !== null) {
        $responsibleuserids[] = (int) $agreement->responsibleuserid;
    }
}
$responsibleusers = !empty($responsibleuserids)
    ? $DB->get_records_list('user', 'id', array_unique($responsibleuserids), '', 'id, firstname, lastname, email')
    : [];

echo $renderer->dashboard_summary_cards($dashboard->summary);
echo $renderer->heading(get_string('dashboard_section_students', 'local_monlaututoria'), 3);
echo $renderer->dashboard_students_table(
    $filteredstudents,
    $studentusers,
    (int) $academicyear->id,
    $cancreateentry,
    $cancreatefollowup
);

echo $renderer->heading(get_string('dashboard_section_followups', 'local_monlaututoria'), 3);
echo $renderer->dashboard_followups_table($overduefollowups, $studentusers, $canmanagefollowups);
echo $renderer->dashboard_followups_table($upcomingfollowups, $studentusers, $canmanagefollowups);

echo $renderer->heading(get_string('dashboard_section_agreements', 'local_monlaututoria'), 3);
echo $renderer->dashboard_agreements_table($overdueagreements, $studentusers, $responsibleusers, $canmanageagreements);
echo $renderer->dashboard_agreements_table($pendingagreements, $studentusers, $responsibleusers, $canmanageagreements);

echo $renderer->heading(get_string('dashboard_section_referrals', 'local_monlaututoria'), 3);
echo $renderer->referrals_table($referrals, $studentusers);

echo $renderer->heading(get_string('dashboard_section_priority', 'local_monlaututoria'), 3);
echo $renderer->dashboard_priority_students_list($dashboard->prioritystudents, $studentusers, (int) $academicyear->id);

echo $OUTPUT->footer();
