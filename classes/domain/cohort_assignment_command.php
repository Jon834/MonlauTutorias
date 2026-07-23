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
 * Immutable input for cohort_assignment_preview_service::preview(). Carries
 * only the configuration a caller may choose — never a studentid list: the
 * service always resolves cohort membership itself, so a stale or
 * manipulated student selection cannot be smuggled in (phase 3C.1 has no
 * manual inclusion/exclusion yet; that is phase 3C.2+).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_command {

    public function __construct(
        public readonly int $cohortid,
        public readonly int $academicyearid,
        public readonly int $primarytutorid,
        public readonly string $mode,
        public readonly ?int $cotutorid = null,
        public readonly ?int $timestart = null,
        public readonly ?int $timeend = null,
        public readonly bool $includesuspended = false,
        public readonly bool $allowsuspendedtutor = false,
        public readonly bool $canoverridelock = false
    ) {
    }
}
