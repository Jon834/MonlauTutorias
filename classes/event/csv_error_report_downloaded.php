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
 * Event triggered when an administrator downloads the CSV error/skipped-rows
 * report for an applied import (phase 3D.4).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_error_report_downloaded extends csv_import_operation_event_base {

    public static function get_name() {
        return get_string('eventcsverrorreportdownloaded', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} downloaded the CSV error report for import operation "
            . "id {$this->objectid} ({$this->other['rowcount']} row(s)).";
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $rowcount number of rows included in the report — never the row content itself
     * @return self
     */
    public static function create_from_operation(int $operationid, int $userid, int $rowcount): self {
        return static::build($operationid, $userid, ['rowcount' => $rowcount]);
    }
}
