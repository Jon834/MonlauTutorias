<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managecoordinationscopes', $context);

$selecteduserid = optional_param('userid', 0, PARAM_INT);
$save = optional_param('save', 0, PARAM_BOOL);

$coordscopeservice = new \local_monlaututoria\service\coordination_scope_service();
$scoperepository = new \local_monlaututoria\repository\coordination_scope_repository();

$allscopeassignments = $scoperepository->get_all();
$assigneduserids = array_values(array_unique(array_map(static fn (\stdClass $row): int => (int) $row->userid, $allscopeassignments)));

$users = get_users_by_capability(
    $context,
    'local/monlaututoria:viewcoordinationdashboard',
    'u.id, u.firstname, u.lastname, u.email',
    'lastname ASC, firstname ASC, id ASC',
    '',
    '',
    false
);
if (!empty($assigneduserids)) {
    $assignedusers = $DB->get_records_list('user', 'id', $assigneduserids, 'lastname ASC, firstname ASC, id ASC', 'id, firstname, lastname, email, deleted');
    foreach ($assignedusers as $userid => $user) {
        if (empty($user->deleted)) {
            $users[(int) $userid] = $user;
        }
    }
}

uasort($users, static function (\stdClass $a, \stdClass $b): int {
    return strcasecmp(fullname($a), fullname($b));
});

if (!isset($users[$selecteduserid])) {
    $selecteduserid = 0;
}

if ($save && confirm_sesskey() && $selecteduserid > 0) {
    $cohortids = optional_param_array('cohortids', [], PARAM_INT);
    $coordscopeservice->replace_user_scopes($selecteduserid, (int) $USER->id, $cohortids);
    redirect(
        new moodle_url('/local/monlaututoria/coordination_scopes.php', ['userid' => $selecteduserid]),
        get_string('coordination_scope_saved', 'local_monlaututoria')
    );
}

admin_externalpage_setup('local_monlaututoria_coordination_scopes');
$PAGE->set_url('/local/monlaututoria/coordination_scopes.php', ['userid' => $selecteduserid]);
$PAGE->set_title(get_string('coordination_scopes_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('coordination_scopes_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$useroptions = [0 => get_string('choose')];
foreach ($users as $user) {
    $useroptions[(int) $user->id] = fullname($user);
}
// Only cohorts an admin has enabled for this plugin (cohort_visibility.php)
// — a coordinator should never be scoped to a cohort nobody can see anyway.
$cohorts = (new \local_monlaututoria\service\cohort_visibility_service())->get_visible_cohorts();
$scopecohortids = $scoperepository->get_cohort_ids_for_users(array_keys($users));
$currentcohortids = $selecteduserid > 0 ? ($scopecohortids[$selecteduserid] ?? []) : [];

echo $OUTPUT->header();
echo $renderer->plugin_navigation('coordinators');
echo $renderer->page_header_card(
    get_string('coordination_scopes_title', 'local_monlaututoria'),
    get_string('coordination_scope_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/coordination.php'),
    get_string('page_back_dashboard', 'local_monlaututoria'),
    [],
    get_string('pluginname', 'local_monlaututoria')
);
echo $OUTPUT->notification(get_string('coordination_scope_help_steps', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);

echo $renderer->single_select(
    new moodle_url('/local/monlaututoria/coordination_scopes.php'),
    'userid',
    $useroptions,
    $selecteduserid,
    null,
    'coordinationscopeuserselector'
);
echo $renderer->heading(get_string('coordination_scope_assignments', 'local_monlaututoria'), 3);
echo $renderer->coordination_scope_assignments_table($users, $scopecohortids, $cohorts);

if ($selecteduserid > 0) {
    $selecteduser = $users[$selecteduserid] ?? null;
    $selectedlabel = $selecteduser ? fullname($selecteduser) : ('#' . $selecteduserid);
    echo $renderer->heading(get_string('coordination_scope_current', 'local_monlaututoria', $selectedlabel), 3);
    echo html_writer::start_tag('form', ['method' => 'post']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $selecteduserid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'save', 'value' => 1]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    foreach ($cohorts as $cohort) {
        echo html_writer::div(
            html_writer::checkbox(
                'cohortids[]',
                (int) $cohort->id,
                in_array((int) $cohort->id, $currentcohortids, true),
                format_string($cohort->name)
            ),
            'form-check'
        );
    }
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'class' => 'btn btn-primary mt-3',
        'value' => get_string('coordination_scope_save', 'local_monlaututoria')
    ]);
    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
