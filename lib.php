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
 * Library functions for local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves tutoring entry attachments (phase 5.6). This is the single most
 * important access-control choke point of this whole feature: pluginfile.php
 * URLs are visible to the browser and their itemid (the entry id) is
 * directly guessable/editable by anyone who has ever downloaded one
 * attachment — CLAUDE.md explicitly lists "acceso directo a archivos" as a
 * case that must be tested. Every check a normal page would run
 * (autenticación, contexto, capacidad, ámbito) has to be repeated here by
 * hand, because pluginfile.php never goes through entries/*.php at all.
 *
 * Mirrors entry_attachment_service::get_for_entry()'s own access rule
 * exactly (staff only, never the student themselves, viewinternalnotes +
 * scope_service) — deliberately re-implemented rather than reused, since a
 * false return here (not an exception) is how Moodle expects "access
 * denied" to be signalled from a pluginfile callback.
 *
 * @param stdClass $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool false on any failure/denial (never a redirect or an echo)
 */
function local_monlaututoria_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $USER;

    require_login();

    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }
    if ($filearea !== \local_monlaututoria\service\entry_attachment_service::FILEAREA) {
        return false;
    }
    if (count($args) < 2) {
        return false;
    }

    $entryid = (int) array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? ('/' . implode('/', $args) . '/') : '/';

    $entryrepository = new \local_monlaututoria\repository\entry_repository();
    try {
        $entry = $entryrepository->get($entryid);
    } catch (\dml_missing_record_exception $e) {
        return false;
    }

    $scope = new \local_monlaututoria\service\scope_service();
    if (!$scope->can_user_access_student((int) $USER->id, (int) $entry->studentid, (int) $entry->academicyearid)) {
        return false;
    }

    // Attachments are staff-only in this phase — same hard floor as
    // entry_attachment_service::get_for_entry(), reimplemented here because
    // pluginfile.php never calls that service.
    $isstudent = (int) $USER->id === (int) $entry->studentid;
    if ($isstudent || !has_capability('local/monlaututoria:viewinternalnotes', $context)) {
        return false;
    }

    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        'local_monlaututoria',
        \local_monlaututoria\service\entry_attachment_service::FILEAREA,
        $entryid,
        $filepath,
        $filename
    );
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
