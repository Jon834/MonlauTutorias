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
 * Immutable data transfer object for a row of local_tut_entry.
 *
 * contentvisible/noteinternal/noterestricted are nullable not only because
 * the underlying columns are nullable, but because entry_service::get_for_viewer()
 * constructs instances of this class with the fields the viewer is not
 * authorised to see already set to null — the masking happens before this
 * object exists, never in a template.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry {

    public function __construct(
        public readonly ?int $id,
        public readonly int $studentid,
        public readonly int $tutorid,
        public readonly int $academicyearid,
        public readonly int $entrydate,
        public readonly ?int $modalityid,
        public readonly ?string $contentvisible,
        public readonly ?string $noteinternal,
        public readonly ?string $noterestricted,
        public readonly string $status,
        public readonly ?int $nextfollowupdate,
        public readonly int $createdby,
        public readonly int $modifiedby,
        public readonly int $timecreated,
        public readonly int $timemodified,
        public readonly string $entrykind = entry_kind::REGULAR,
        public readonly ?string $recommendationsop = null
    ) {
    }

    /**
     * Builds an instance from a Moodle DML record, with no masking applied —
     * use entry_service::get_for_viewer() for viewer-aware access.
     *
     * @param \stdClass $record
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        return new self(
            isset($record->id) ? (int) $record->id : null,
            (int) $record->studentid,
            (int) $record->tutorid,
            (int) $record->academicyearid,
            (int) $record->entrydate,
            isset($record->modalityid) ? (int) $record->modalityid : null,
            $record->contentvisible ?? null,
            $record->noteinternal ?? null,
            $record->noterestricted ?? null,
            $record->status,
            isset($record->nextfollowupdate) ? (int) $record->nextfollowupdate : null,
            (int) ($record->createdby ?? 0),
            (int) ($record->modifiedby ?? 0),
            (int) ($record->timecreated ?? 0),
            (int) ($record->timemodified ?? 0),
            $record->entrykind ?? entry_kind::REGULAR,
            $record->recommendationsop ?? null
        );
    }
}
