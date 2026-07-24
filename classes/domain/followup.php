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

namespace local_monlaututoria\domain;

/**
 * Immutable data transfer object for a row of local_tut_followup.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup {

    public function __construct(
        public readonly ?int $id,
        public readonly int $entryid,
        public readonly ?int $closingentryid,
        public readonly int $studentid,
        public readonly int $duedate,
        public readonly string $priority,
        public readonly string $status,
        public readonly int $createdby,
        public readonly int $modifiedby,
        public readonly int $timecreated,
        public readonly int $timemodified
    ) {
    }

    /**
     * @return bool
     */
    public function is_overdue(): bool {
        return in_array($this->status, followup_status::open_values(), true) && $this->duedate < time();
    }

    /**
     * @param \stdClass $record
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        return new self(
            isset($record->id) ? (int) $record->id : null,
            (int) $record->entryid,
            isset($record->closingentryid) ? (int) $record->closingentryid : null,
            (int) $record->studentid,
            (int) $record->duedate,
            $record->priority,
            $record->status,
            (int) ($record->createdby ?? 0),
            (int) ($record->modifiedby ?? 0),
            (int) ($record->timecreated ?? 0),
            (int) ($record->timemodified ?? 0)
        );
    }
}
