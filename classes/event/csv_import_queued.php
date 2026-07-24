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

namespace local_monlaututoria\event;

/**
 * Event triggered when a large CSV import is deferred to an ad hoc task
 * instead of being applied inline (phase 3E.5 — found while reviewing that
 * every stage of the CSV flow fires an event: preview, apply start, apply
 * completion all did, but the moment an administrator's request actually
 * gets deferred to background processing did not. Without it, the only
 * audit trail for "who queued this and when" was csv_import_started, fired
 * whenever the ad hoc task eventually runs — which could be long after the
 * request that triggered it, and never fires at all if the task fails
 * before running (e.g. the file went missing).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_queued extends csv_import_operation_event_base {

    public static function get_name() {
        return get_string('eventcsvimportqueued', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} queued a large CSV import (operation id {$this->objectid}, "
            . "{$this->other['totalrows']} row(s)) for background processing.";
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $totalrows
     * @return self
     */
    public static function create_from_operation(int $operationid, int $userid, int $totalrows): self {
        return static::build($operationid, $userid, ['totalrows' => $totalrows]);
    }
}
