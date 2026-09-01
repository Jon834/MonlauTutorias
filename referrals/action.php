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
 * Cancels a referral (phase 6.4) — the only referral action simple enough
 * for a plain confirm, same "confirm + confirm_sesskey" pattern as
 * agreements/action.php. "action" param kept (rather than a fixed
 * cancel.php) for consistency with the other 2 quick-action pages in this
 * plugin, even though cancel is the only value accepted today.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::REFERRALS);
require_capability('local/monlaututoria:managereferrals', $context);

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
if ($action !== 'cancel') {
    throw new \moodle_exception('invalidaction');
}
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$returnurl = new moodle_url('/local/monlaututoria/referrals/view.php', ['id' => $id]);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/referrals/action.php', ['id' => $id, 'action' => $action]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('referral_cancel', 'local_monlaututoria'));
$PAGE->set_heading(get_string('referral_cancel', 'local_monlaututoria'));

if ($confirm && confirm_sesskey()) {
    $service = new \local_monlaututoria\service\referral_service();
    $service->cancel($id, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('referral_action_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('referral_confirm_cancel', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/referrals/action.php', ['id' => $id, 'action' => $action, 'confirm' => 1, 'sesskey' => sesskey()]),
    $returnurl
);
echo $OUTPUT->footer();
