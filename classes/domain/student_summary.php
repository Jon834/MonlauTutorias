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
 * Immutable header/summary for a student's longitudinal file (phase 4.1) —
 * "cabecera y resumen": current primary tutor and co-tutors, cohort, last
 * assignment and any not-yet-started ("upcoming") one, all scoped to a
 * single academic year. Computed on demand from local_tut_assignment; no new
 * table, same "recompute, never persist a snapshot" principle already used
 * throughout this plugin's preview services.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class student_summary {

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @param \stdClass|null $primaryassignment raw local_tut_assignment record, or null if unassigned
     * @param \stdClass[] $cotutorassignments raw local_tut_assignment records, active co-tutor rows
     * @param \stdClass|null $lastassignment raw local_tut_assignment record with the latest timestart
     *                                       in this academic year, of any type/status, or null if none
     * @param \stdClass[] $upcomingassignments active rows not yet "vigente" (timestart in the future)
     */
    public function __construct(
        public readonly int $studentid,
        public readonly int $academicyearid,
        public readonly ?\stdClass $primaryassignment,
        public readonly array $cotutorassignments,
        public readonly ?\stdClass $lastassignment,
        public readonly array $upcomingassignments
    ) {
    }
}
