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
 * Detail view of a single referral (phase 6.4). Access is resolved entirely
 * by referral_service::get_for_viewer() — creator, assignee, or
 * managereferrals — never scope_service, see that service's class docblock.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::REFERRALS);

$id = required_param('id', PARAM_INT);

$service = new \local_monlaututoria\service\referral_service();
$referral = $service->get_for_viewer($id, (int) $USER->id);

$student = core_user::get_user($referral->studentid);
$entry = null;
try {
    $entry = (new \local_monlaututoria\repository\entry_repository())->get($referral->entryid);
} catch (\dml_missing_record_exception $e) {
    $entry = null;
}
$assignee = $referral->assignedto !== null ? core_user::get_user($referral->assignedto) : null;

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/referrals/view.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('referral_detail_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('referral_detail_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$canmanage = has_capability('local/monlaututoria:managereferrals', $context);
$isopen = in_array(
    $referral->status,
    [\local_monlaututoria\domain\referral_status::PENDING, \local_monlaututoria\domain\referral_status::IN_PROGRESS],
    true
);

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('referrals', [
    'studentid' => (int) $referral->studentid,
    'studentlabel' => $student ? fullname($student) : get_string('nav_student', 'local_monlaututoria'),
]);
echo $renderer->page_header_card(
    get_string('referral_detail_title', 'local_monlaututoria'),
    get_string('referral_detail_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/referrals/index.php'),
    get_string('page_back_referrals', 'local_monlaututoria'),
    [],
    $student ? fullname($student) : null
);
echo $renderer->referral_detail($referral, $student, $entry, $assignee);

if ($canmanage && $isopen) {
    echo html_writer::div(
        $OUTPUT->single_button(
            new moodle_url('/local/monlaututoria/referrals/assign.php', ['id' => $id]),
            get_string('referral_assign', 'local_monlaututoria')
        ) .
        $OUTPUT->single_button(
            new moodle_url('/local/monlaututoria/referrals/resolve.php', ['id' => $id]),
            get_string('referral_resolve', 'local_monlaututoria')
        ) .
        $OUTPUT->single_button(
            new moodle_url('/local/monlaututoria/referrals/action.php', ['id' => $id, 'action' => 'cancel']),
            get_string('referral_cancel', 'local_monlaututoria')
        ),
        'd-flex gap-2'
    );
}

echo $OUTPUT->footer();


