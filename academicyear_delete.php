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
 * Confirms and performs deletion of an academic year.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:manageacademicyears', $context);

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/monlaututoria/academicyear_delete.php', ['id' => $id]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('academicyear_delete', 'local_monlaututoria'));
$PAGE->set_heading(get_string('academicyear_delete', 'local_monlaututoria'));

$repository = new \local_monlaututoria\repository\academic_year_repository();
$service = new \local_monlaututoria\service\academic_year_service($repository);
$year = $repository->get($id);

$returnurl = new moodle_url('/local/monlaututoria/academicyears.php');

if ($confirm && confirm_sesskey()) {
    $service->delete($id, (int) $USER->id);
    redirect($returnurl, get_string('academicyear_delete_success', 'local_monlaututoria'));
}

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('academicyear_delete_confirm', 'local_monlaututoria', format_string($year->name)),
    new moodle_url('/local/monlaututoria/academicyear_delete.php', ['id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]),
    $returnurl
);
echo $OUTPUT->footer();
