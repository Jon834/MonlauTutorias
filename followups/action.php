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
 * Confirms and performs one of the follow-up quick actions (phase 6.3):
 * complete (manually — see followups/... entries/create.php for the "closed
 * via a linked entry" path), reopen, cancel. Same shape as
 * agreements/action.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::FOLLOWUPS);
require_capability('local/monlaututoria:managefollowups', $context);

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
// followup_service names its manual-completion method complete_manually(),
// not complete() — entries/create.php's linked-entry path calls
// close_with_entry() instead, never this page.
$actiontomethod = ['complete' => 'complete_manually', 'reopen' => 'reopen', 'cancel' => 'cancel'];
if (!array_key_exists($action, $actiontomethod)) {
    throw new \moodle_exception('invalidaction');
}
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$repository = new \local_monlaututoria\repository\followup_repository();
$followup = $repository->get($id);

$entryrepository = new \local_monlaututoria\repository\entry_repository();
$entry = $entryrepository->get((int) $followup->entryid);

$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, (int) $followup->studentid, (int) $entry->academicyearid);

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $followup->entryid]);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/followups/action.php', ['id' => $id, 'action' => $action]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('followup_' . $action, 'local_monlaututoria'));
$PAGE->set_heading(get_string('followup_' . $action, 'local_monlaututoria'));

if ($confirm && confirm_sesskey()) {
    $service = new \local_monlaututoria\service\followup_service();
    $method = $actiontomethod[$action];
    $service->$method($id, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('followup_action_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$confirmstringkey = $action === 'cancel' ? 'followup_confirm_cancel' : 'followup_' . $action;

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string($confirmstringkey, 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/followups/action.php', ['id' => $id, 'action' => $action, 'confirm' => 1, 'sesskey' => sesskey()]),
    $returnurl
);
echo $OUTPUT->footer();
