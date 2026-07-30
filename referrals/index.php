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
 * Referrals listing for coordination/orientation/management (phase 6.4).
 * Site-wide, not scoped to a single student's ficha — see referral_service's
 * class docblock for why this is capability-gated (managereferrals), not
 * scope_service-gated, unlike every other listing in this plugin.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managereferrals', $context);

$statusfilter = optional_param('status', '', PARAM_ALPHA);
$filters = [];
if (in_array($statusfilter, \local_monlaututoria\domain\referral_status::values(), true)) {
    $filters['status'] = $statusfilter;
}

$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;

// referrals_table() offers 4 columns (shared with dashboard.php's in-memory
// version), but this page is SQL-paginated — only a real ORDER BY can sort
// correctly across the full result set, not just the current page. Only
// referral_repository::sortable_columns() (status/priority/timecreated) can
// do that; clicking "Alumno"/"Destino" here silently has no effect, same
// "unknown sort falls back to the default" behaviour already established
// for every other sortable listing in this plugin.
$referralsort = optional_param('referralsort', '', PARAM_ALPHA);
if (!in_array($referralsort, \local_monlaututoria\repository\referral_repository::sortable_columns(), true)) {
    $referralsort = 'timecreated';
}
$referraldir = strtoupper(optional_param('referraldir', 'DESC', PARAM_ALPHA)) === 'ASC' ? 'ASC' : 'DESC';

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/referrals/index.php', array_filter([
    'status' => $statusfilter ?: null,
    'referralsort' => $referralsort,
    'referraldir' => $referraldir,
]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('referrals_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('referrals_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$service = new \local_monlaututoria\service\referral_service();
$totalcount = $service->count_for_coordination($filters, (int) $USER->id);
$referrals = $service->list_for_coordination(
    $filters, (int) $USER->id, $page * $perpage, $perpage, $referralsort, $referraldir
);

$studentids = array_unique(array_map(static fn ($referral) => $referral->studentid, $referrals));
$students = !empty($studentids) ? $DB->get_records_list('user', 'id', $studentids, '', 'id, firstname, lastname, email') : [];

$statusoptions = ['' => get_string('choosedots')] + \local_monlaututoria\domain\referral_status::get_options();
/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');
echo $OUTPUT->header();
echo $renderer->plugin_navigation('referrals');
echo $renderer->page_header_card(
    get_string('referrals_title', 'local_monlaututoria'),
    get_string('referrals_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/dashboard.php'),
    get_string('page_back_dashboard', 'local_monlaututoria'),
    [],
    get_string('pluginname', 'local_monlaututoria')
);
echo $OUTPUT->single_select(
    new moodle_url('/local/monlaututoria/referrals/index.php', ['referralsort' => $referralsort, 'referraldir' => $referraldir]),
    'status',
    $statusoptions,
    $statusfilter,
    [],
    'referralstatusselector'
);

$sortbaseurl = new moodle_url('/local/monlaututoria/referrals/index.php', array_filter(['status' => $statusfilter ?: null]));
echo $renderer->referrals_table($referrals, $students, $referralsort, $referraldir, $sortbaseurl);

echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
echo $OUTPUT->footer();



