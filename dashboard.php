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

// Both default enabled (Site administration > Local plugins > Monlau
// Tutoria > Settings) — real-use feedback found the referrals/priority
// sections of this dashboard confusing for tutors, who can already mention
// a derivación in the tutoring entry's own text; a school can turn either
// off without losing the underlying data (referral_service/dashboard_service
// keep computing them exactly as before, only the rendering is gated).
// !== '0', not (bool) cast: get_config() returns false (not '0') when the
// setting has never been written yet (e.g. right after upgrade, before
// anyone has opened the settings page) — casting that to bool would default
// a brand-new install to "hidden", the opposite of the intended default.
$showreferrals = get_config('local_monlaututoria', 'dashboard_showreferrals') !== '0';
$showpriority = get_config('local_monlaututoria', 'dashboard_showpriority') !== '0';

$validstudentfilters = ['all', 'pendinginitial', 'withpending'];
if ($showpriority) {
    $validstudentfilters[] = 'priority';
}
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
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

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
];
if ($showpriority) {
    $studentfilteroptions['priority'] = get_string('dashboard_studentfilter_priority', 'local_monlaututoria');
}
$pendingfilteroptions = [
    'all' => get_string('dashboard_pendingfilter_all', 'local_monlaututoria'),
    'open' => get_string('dashboard_pendingfilter_open', 'local_monlaututoria'),
    'overdue' => get_string('dashboard_pendingfilter_overdue', 'local_monlaututoria'),
];

// One independent sort per table on this page (studentsort/followupsort/
// agreementsort/referralsort), each validated against its own small
// whitelist — never trust the URL value directly into a usort() comparator.
$studentsort = optional_param('studentsort', '', PARAM_ALPHA);
if (!in_array($studentsort, ['studentname', 'lastentry', 'entrycount'], true)) {
    $studentsort = '';
}
$studentdir = strtoupper(optional_param('studentdir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';

$followupsort = optional_param('followupsort', '', PARAM_ALPHA);
if (!in_array($followupsort, ['studentname', 'duedate', 'priority'], true)) {
    $followupsort = '';
}
$followupdir = strtoupper(optional_param('followupdir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';

$agreementsort = optional_param('agreementsort', '', PARAM_ALPHA);
if (!in_array($agreementsort, ['studentname', 'duedate'], true)) {
    $agreementsort = '';
}
$agreementdir = strtoupper(optional_param('agreementdir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';

$referralsort = optional_param('referralsort', '', PARAM_ALPHA);
if (!in_array($referralsort, ['studentname', 'destination', 'priority', 'status'], true)) {
    $referralsort = '';
}
$referraldir = strtoupper(optional_param('referraldir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC';

$cancreateentry = has_capability('local/monlaututoria:createentry', $context);
$cancreatefollowup = has_capability('local/monlaututoria:createfollowup', $context);
$canmanageagreements = has_capability('local/monlaututoria:manageagreements', $context);
$canmanagefollowups = has_capability('local/monlaututoria:managefollowups', $context);
$canmanagereferrals = has_capability('local/monlaututoria:managereferrals', $context);

echo $OUTPUT->header();
echo $renderer->plugin_navigation('dashboard');
echo $renderer->page_header_card(
    get_string('dashboard_title', 'local_monlaututoria'),
    get_string('dashboard_intro', 'local_monlaututoria'),
    null,
    null,
    [],
    get_string('pluginname', 'local_monlaututoria')
);
echo $renderer->contextual_help(
    get_string('help_dashboard_title', 'local_monlaututoria'),
    get_string('help_dashboard_body', 'local_monlaututoria')
);

// Wrapped together (previously echoed bare, one after another, running
// straight into the summary cards below with no separation) — same
// .local-monlaututoria-toolbar treatment already applied to the ficha del
// alumno's "Tutorías" tab filters.
$dashboardfilters = '';
if (!empty($academicyearoptions)) {
    $dashboardfilters .= $OUTPUT->single_select(
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
$dashboardfilters .= $OUTPUT->single_select(
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
$dashboardfilters .= $OUTPUT->single_select(
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
echo html_writer::div($dashboardfilters, 'local-monlaututoria-toolbar');

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

// Every table here is already a small, fully-loaded-in-memory array (never
// a paginated SQL query) — a plain usort() is enough, no repository/service
// change needed. studentname sorts always fall back to '' for a student
// whose user record could not be resolved, so they sort first/last
// predictably instead of raising a notice.
$studentname = static fn (int $studentid) => isset($studentusers[$studentid]) ? fullname($studentusers[$studentid]) : '';
$priorityrank = array_flip(\local_monlaututoria\domain\priority_level::values());

if ($studentsort !== '') {
    usort($filteredstudents, function ($a, $b) use ($studentsort, $studentdir, $studentname): int {
        $result = match ($studentsort) {
            'studentname' => strcasecmp($studentname($a->studentid), $studentname($b->studentid)),
            'lastentry'   => ($a->latestactiveentry->entrydate ?? 0) <=> ($b->latestactiveentry->entrydate ?? 0),
            'entrycount'  => $a->activeentrycount <=> $b->activeentrycount,
        };

        return $studentdir === 'DESC' ? -$result : $result;
    });
}

if ($followupsort !== '') {
    $followupsorter = function ($a, $b) use ($followupsort, $followupdir, $studentname, $priorityrank): int {
        $result = match ($followupsort) {
            'studentname' => strcasecmp($studentname($a->studentid), $studentname($b->studentid)),
            'duedate'     => $a->duedate <=> $b->duedate,
            'priority'    => ($priorityrank[$a->priority] ?? 0) <=> ($priorityrank[$b->priority] ?? 0),
        };

        return $followupdir === 'DESC' ? -$result : $result;
    };
    usort($overduefollowups, $followupsorter);
    usort($upcomingfollowups, $followupsorter);
}

if ($agreementsort !== '') {
    $agreementsorter = function ($a, $b) use ($agreementsort, $agreementdir, $studentname): int {
        $result = match ($agreementsort) {
            'studentname' => strcasecmp($studentname($a->studentid), $studentname($b->studentid)),
            'duedate'     => $a->duedate <=> $b->duedate,
        };

        return $agreementdir === 'DESC' ? -$result : $result;
    };
    usort($overdueagreements, $agreementsorter);
    usort($pendingagreements, $agreementsorter);
}

if ($showreferrals && $referralsort !== '') {
    usort($referrals, function ($a, $b) use ($referralsort, $referraldir, $studentname, $priorityrank): int {
        $result = match ($referralsort) {
            'studentname' => strcasecmp($studentname($a->studentid), $studentname($b->studentid)),
            'destination' => strcasecmp($a->destination, $b->destination),
            'priority'    => ($priorityrank[$a->priority] ?? 0) <=> ($priorityrank[$b->priority] ?? 0),
            'status'      => strcasecmp($a->status, $b->status),
        };

        return $referraldir === 'DESC' ? -$result : $result;
    });
}

// Shared by every table's sort links, so choosing a new sort column for one
// table never resets any of the others — every table's current sort state
// travels along on every link, not just the one being clicked.
$sortbaseurl = new moodle_url('/local/monlaututoria/dashboard.php', array_filter([
    'academicyearid'  => $requestedacademicyearid ?: null,
    'studentfilter'   => $studentfilter,
    'pendingfilter'   => $pendingfilter,
    'studentsort'     => $studentsort ?: null,
    'studentdir'      => $studentsort !== '' ? $studentdir : null,
    'followupsort'    => $followupsort ?: null,
    'followupdir'     => $followupsort !== '' ? $followupdir : null,
    'agreementsort'   => $agreementsort ?: null,
    'agreementdir'    => $agreementsort !== '' ? $agreementdir : null,
    'referralsort'    => $referralsort ?: null,
    'referraldir'     => $referralsort !== '' ? $referraldir : null,
], static fn ($value) => $value !== null));

echo $renderer->dashboard_summary_cards($dashboard->summary, $showreferrals, $showpriority);
echo $renderer->heading(get_string('dashboard_section_students', 'local_monlaututoria'), 3);
echo $renderer->dashboard_students_table(
    $filteredstudents,
    $studentusers,
    (int) $academicyear->id,
    $cancreateentry,
    $cancreatefollowup,
    $showpriority,
    $studentsort,
    $studentdir,
    $sortbaseurl
);

echo $renderer->heading(get_string('dashboard_section_followups', 'local_monlaututoria'), 3);
if (empty($overduefollowups) && empty($upcomingfollowups)) {
    // One combined empty note, not two — showing "no overdue follow-ups"
    // directly above a table that DOES have upcoming ones (or vice versa)
    // reads as contradictory, not informative.
    echo $renderer->dashboard_followups_table([], $studentusers, $canmanagefollowups, $followupsort, $followupdir, $sortbaseurl);
} else {
    if (!empty($overduefollowups)) {
        echo $renderer->dashboard_followups_table(
            $overduefollowups, $studentusers, $canmanagefollowups, $followupsort, $followupdir, $sortbaseurl
        );
    }
    if (!empty($upcomingfollowups)) {
        echo $renderer->dashboard_followups_table(
            $upcomingfollowups, $studentusers, $canmanagefollowups, $followupsort, $followupdir, $sortbaseurl
        );
    }
}

echo $renderer->heading(get_string('dashboard_section_agreements', 'local_monlaututoria'), 3);
if (empty($overdueagreements) && empty($pendingagreements)) {
    echo $renderer->dashboard_agreements_table(
        [], $studentusers, $responsibleusers, $canmanageagreements, $agreementsort, $agreementdir, $sortbaseurl
    );
} else {
    if (!empty($overdueagreements)) {
        echo $renderer->dashboard_agreements_table(
            $overdueagreements, $studentusers, $responsibleusers, $canmanageagreements,
            $agreementsort, $agreementdir, $sortbaseurl
        );
    }
    if (!empty($pendingagreements)) {
        echo $renderer->dashboard_agreements_table(
            $pendingagreements, $studentusers, $responsibleusers, $canmanageagreements,
            $agreementsort, $agreementdir, $sortbaseurl
        );
    }
}

if ($showreferrals) {
    echo $renderer->heading(get_string('dashboard_section_referrals', 'local_monlaututoria'), 3);
    echo $renderer->referrals_table($referrals, $studentusers, $referralsort, $referraldir, $sortbaseurl);
}

if ($showpriority) {
    echo $renderer->heading(get_string('dashboard_section_priority', 'local_monlaututoria'), 3);
    echo $renderer->dashboard_priority_students_list($dashboard->prioritystudents, $studentusers, (int) $academicyear->id);
}

echo $OUTPUT->footer();


