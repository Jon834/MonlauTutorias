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
 * Immutable DTO for a row of local_tut_reason.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reason extends catalogue_item {

    public function __construct(
        ?int $id,
        string $name,
        string $shortname,
        ?string $description,
        bool $active,
        int $sortorder,
        int $createdby,
        int $modifiedby,
        int $timecreated,
        int $timemodified,
        public readonly bool $requiresfollowup,
        public readonly int $defaultvisibility
    ) {
        parent::__construct(
            $id,
            $name,
            $shortname,
            $description,
            $active,
            $sortorder,
            $createdby,
            $modifiedby,
            $timecreated,
            $timemodified
        );
    }

    public static function from_record(\stdClass $record): static {
        return new self(
            isset($record->id) ? (int) $record->id : null,
            $record->name,
            $record->shortname,
            $record->description ?? null,
            !empty($record->active),
            (int) ($record->sortorder ?? 0),
            (int) ($record->createdby ?? 0),
            (int) ($record->modifiedby ?? 0),
            (int) ($record->timecreated ?? 0),
            (int) ($record->timemodified ?? 0),
            !empty($record->requiresfollowup),
            (int) ($record->defaultvisibility ?? visibility_level::INTERNAL)
        );
    }

    public function to_record(): \stdClass {
        $record = parent::to_record();
        $record->requiresfollowup = $this->requiresfollowup ? 1 : 0;
        $record->defaultvisibility = $this->defaultvisibility;

        return $record;
    }
}
