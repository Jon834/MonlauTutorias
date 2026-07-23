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
 * Immutable input for assignment_service::reassign_primary_tutor(). Carries
 * only what the caller may decide: never the previous assignment's id or
 * tutor — the service looks those up itself from studentid/academicyearid,
 * so a stale or manipulated value cannot be smuggled in.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reassign_assignment_command {

    public function __construct(
        public readonly int $studentid,
        public readonly int $newtutorid,
        public readonly int $academicyearid,
        public readonly string $reassignreason,
        public readonly ?int $effectivedate = null,
        public readonly bool $keepcotutors = true,
        public readonly bool $allowsuspended = false,
        public readonly bool $canoverridelock = false
    ) {
    }
}
