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
 * Postpones a follow-up's due date (phase 6.3) — same shape as
 * agreements/postpone.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managefollowups', $context);

$id = required_param('id', PARAM_INT);

$repository = new \local_monlaututoria\repository\followup_repository();
$followup = $repository->get($id);

$entryrepository = new \local_monlaututoria\repository\entry_repository();
$entry = $entryrepository->get((int) $followup->entryid);

$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, (int) $followup->studentid, (int) $entry->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/followups/postpone.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('agreement_postpone_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('agreement_postpone_title', 'local_monlaututoria'));

$form = new \local_monlaututoria\form\followup_postpone_form(null, ['currentduedate' => (int) $followup->duedate]);
$form->set_data((object) ['id' => $id]);

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $followup->entryid]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\followup_service();
    $service->postpone((int) $data->id, (int) $data->newduedate, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('followup_action_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('agreement_postpone_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
