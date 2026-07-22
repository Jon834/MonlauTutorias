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
 * Immutable data transfer object for a row of local_tut_assignment.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment {

    public function __construct(
        public readonly ?int $id,
        public readonly int $studentid,
        public readonly int $tutorid,
        public readonly ?int $cohortid,
        public readonly int $academicyearid,
        public readonly string $assignmenttype,
        public readonly bool $isprimary,
        public readonly string $status,
        public readonly int $timestart,
        public readonly ?int $timeend,
        public readonly string $source,
        public readonly ?string $externalid,
        public readonly int $createdby,
        public readonly int $modifiedby,
        public readonly int $timecreated,
        public readonly int $timemodified
    ) {
    }

    /**
     * Builds an instance from a Moodle DML record.
     *
     * @param \stdClass $record
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        return new self(
            isset($record->id) ? (int) $record->id : null,
            (int) $record->studentid,
            (int) $record->tutorid,
            isset($record->cohortid) ? (int) $record->cohortid : null,
            (int) $record->academicyearid,
            $record->assignmenttype,
            !empty($record->isprimary),
            $record->status,
            (int) $record->timestart,
            isset($record->timeend) ? (int) $record->timeend : null,
            $record->source,
            $record->externalid ?? null,
            (int) ($record->createdby ?? 0),
            (int) ($record->modifiedby ?? 0),
            (int) ($record->timecreated ?? 0),
            (int) ($record->timemodified ?? 0)
        );
    }

    /**
     * Converts this DTO back into a Moodle DML record.
     *
     * @return \stdClass
     */
    public function to_record(): \stdClass {
        $record = new \stdClass();
        if ($this->id !== null) {
            $record->id = $this->id;
        }
        $record->studentid = $this->studentid;
        $record->tutorid = $this->tutorid;
        $record->cohortid = $this->cohortid;
        $record->academicyearid = $this->academicyearid;
        $record->assignmenttype = $this->assignmenttype;
        $record->isprimary = $this->isprimary ? 1 : 0;
        $record->status = $this->status;
        $record->timestart = $this->timestart;
        $record->timeend = $this->timeend;
        $record->source = $this->source;
        $record->externalid = $this->externalid;
        $record->createdby = $this->createdby;
        $record->modifiedby = $this->modifiedby;
        $record->timecreated = $this->timecreated;
        $record->timemodified = $this->timemodified;

        return $record;
    }
}
