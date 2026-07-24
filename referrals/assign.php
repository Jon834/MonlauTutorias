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
 * Assigns a referral to a staff member (phase 6.4).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managereferrals', $context);

$id = required_param('id', PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/referrals/assign.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('referral_assign', 'local_monlaututoria'));
$PAGE->set_heading(get_string('referral_assign', 'local_monlaututoria'));

$form = new \local_monlaututoria\form\referral_assign_form();
$form->set_data((object) ['id' => $id]);

$returnurl = new moodle_url('/local/monlaututoria/referrals/view.php', ['id' => $id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\referral_service();
    $service->assign((int) $data->id, (int) $data->assignedto, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('referral_action_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('referral_assign', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
