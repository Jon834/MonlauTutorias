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
 * Immutable input for entry_service::create(). A plain stdClass would work
 * but would leave this many named fields undocumented at the call site —
 * same rationale as reassign_assignment_command.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_create_command {

    /**
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @param int $entrydate
     * @param int|null $modalityid
     * @param string|null $contentvisible
     * @param string|null $noteinternal
     * @param string|null $noterestricted
     * @param int|null $nextfollowupdate
     * @param int[] $reasonids
     * @param \stdClass[] $participants each with participanttype and exactly
     *                                  one of userid/externalname
     * @param bool $canoverridelock whether the caller holds local/monlaututoria:overridelock
     */
    public function __construct(
        public readonly int $studentid,
        public readonly int $tutorid,
        public readonly int $academicyearid,
        public readonly int $entrydate,
        public readonly ?int $modalityid = null,
        public readonly ?string $contentvisible = null,
        public readonly ?string $noteinternal = null,
        public readonly ?string $noterestricted = null,
        public readonly ?int $nextfollowupdate = null,
        public readonly array $reasonids = [],
        public readonly array $participants = [],
        public readonly bool $canoverridelock = false
    ) {
    }
}
