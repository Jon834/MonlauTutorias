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
 * Event triggered when a CSV import ends in the FAILED state — either an
 * atomic_all batch rolled back because one row failed (failedrownumber set),
 * or a deferred import that never got to attempt a single row at all, e.g.
 * process_csv_import_task finding its persisted file missing (failedrownumber
 * null; phase 3E.5 — found while reviewing that every FAILED transition
 * fires this event, and that one path did not).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_failed extends csv_import_operation_event_base {

    public static function get_name() {
        return get_string('eventcsvimportfailed', 'local_monlaututoria');
    }

    public function get_description() {
        if ($this->other['failedrownumber'] === null) {
            return "The CSV import (operation id {$this->objectid}) failed before any row could be attempted.";
        }

        return "The CSV import (operation id {$this->objectid}) failed and was rolled back "
            . "(atomic_all strategy), at row {$this->other['failedrownumber']}.";
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int|null $failedrownumber the row that caused an atomic_all rollback,
     *                                   or null when the operation failed before
     *                                   attempting any row (e.g. the file was missing)
     * @return self
     */
    public static function create_from_operation(int $operationid, int $userid, ?int $failedrownumber): self {
        return static::build($operationid, $userid, ['failedrownumber' => $failedrownumber]);
    }
}
