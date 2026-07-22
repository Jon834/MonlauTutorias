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
 * Create/edit an academic year.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:manageacademicyears', $context);

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/monlaututoria/academicyear_edit.php', ['id' => $id]));
$PAGE->set_pagelayout('admin');

$repository = new \local_monlaututoria\repository\academic_year_repository();
$service = new \local_monlaututoria\service\academic_year_service($repository);
$canoverridelock = has_capability('local/monlaututoria:overridelock', $context);

$record = null;
if ($id) {
    $record = $repository->get($id);
    if (!empty($record->locked) && !$canoverridelock) {
        throw new \moodle_exception('error_academicyear_locked', 'local_monlaututoria');
    }
}

$title = $id
    ? get_string('academicyear_edit', 'local_monlaututoria')
    : get_string('academicyear_create', 'local_monlaututoria');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$form = new \local_monlaututoria\form\academic_year_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/monlaututoria/academicyears.php'));
} else if ($data = $form->get_data()) {
    if (!empty($data->id)) {
        $service->update($data, (int) $USER->id, $canoverridelock);
    } else {
        $service->create($data, (int) $USER->id);
    }
    redirect(new moodle_url('/local/monlaututoria/academicyears.php'));
}

if ($record) {
    $form->set_data($record);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$form->display();
echo $OUTPUT->footer();
