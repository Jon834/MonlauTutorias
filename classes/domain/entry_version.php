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
 * Immutable data transfer object for a row of local_tut_entryversion. The
 * table was created empty in phase 5.1; this class only starts existing now
 * that phase 5.5's editing/annulment flow actually writes to it.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_version {

    /**
     * @param int|null $id
     * @param int $entryid
     * @param int $versionnumber
     * @param array $snapshot decoded from snapshotjson: the entry's editable
     *                        fields as they were immediately before this edit
     * @param string|null $changereason
     * @param int $createdby
     * @param int $timecreated
     */
    public function __construct(
        public readonly ?int $id,
        public readonly int $entryid,
        public readonly int $versionnumber,
        public readonly array $snapshot,
        public readonly ?string $changereason,
        public readonly int $createdby,
        public readonly int $timecreated
    ) {
    }

    /**
     * @param \stdClass $record
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        return new self(
            isset($record->id) ? (int) $record->id : null,
            (int) $record->entryid,
            (int) $record->versionnumber,
            json_decode($record->snapshotjson, true) ?? [],
            $record->changereason ?? null,
            (int) ($record->createdby ?? 0),
            (int) ($record->timecreated ?? 0)
        );
    }
}
