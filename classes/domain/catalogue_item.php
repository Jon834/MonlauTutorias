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
 * Immutable base DTO shared by the reason and modality catalogues.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalogue_item {

    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $shortname,
        public readonly ?string $description,
        public readonly bool $active,
        public readonly int $sortorder,
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
     * @return static
     */
    public static function from_record(\stdClass $record): static {
        return new static(
            isset($record->id) ? (int) $record->id : null,
            $record->name,
            $record->shortname,
            $record->description ?? null,
            !empty($record->active),
            (int) ($record->sortorder ?? 0),
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
        $record->name = $this->name;
        $record->shortname = $this->shortname;
        $record->description = $this->description;
        $record->active = $this->active ? 1 : 0;
        $record->sortorder = $this->sortorder;
        $record->createdby = $this->createdby;
        $record->modifiedby = $this->modifiedby;
        $record->timecreated = $this->timecreated;
        $record->timemodified = $this->timemodified;

        return $record;
    }
}
