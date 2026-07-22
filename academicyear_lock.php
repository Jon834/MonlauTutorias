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
 * Toggles the locked flag of an academic year.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:manageacademicyears', $context);
require_sesskey();

$id = required_param('id', PARAM_INT);
$lock = required_param('lock', PARAM_BOOL);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/monlaututoria/academicyear_lock.php', ['id' => $id]));

$repository = new \local_monlaututoria\repository\academic_year_repository();
$canoverridelock = has_capability('local/monlaututoria:overridelock', $context);
$service = new \local_monlaututoria\service\academic_year_service($repository);

$service->set_locked($id, (bool) $lock, (int) $USER->id, $canoverridelock);

$successstring = $lock ? 'academicyear_locked_success' : 'academicyear_unlocked_success';
redirect(new moodle_url('/local/monlaututoria/academicyears.php'), get_string($successstring, 'local_monlaututoria'));
