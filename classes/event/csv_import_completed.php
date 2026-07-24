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
 * Event triggered when a CSV import batch finishes with no per-row failures.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_completed extends csv_import_operation_event_base {

    public static function get_name() {
        return get_string('eventcsvimportcompleted', 'local_monlaututoria');
    }

    public function get_description() {
        return "The CSV import (operation id {$this->objectid}) completed: "
            . "{$this->other['createdcount']} created, {$this->other['reassignedcount']} reassigned.";
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $createdcount
     * @param int $reassignedcount
     * @param int $nochangecount
     * @return self
     */
    public static function create_from_operation(
        int $operationid,
        int $userid,
        int $createdcount,
        int $reassignedcount,
        int $nochangecount
    ): self {
        return static::build($operationid, $userid, [
            'createdcount'    => $createdcount,
            'reassignedcount' => $reassignedcount,
            'nochangecount'   => $nochangecount,
        ]);
    }
}
