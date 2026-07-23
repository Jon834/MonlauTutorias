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
 * Immutable per-student result of unassigned_students_service's primary-tutor
 * coverage classification. Computed for every student in the requested cohort
 * population, not only those lacking a tutor — search()/count() then filter
 * on hasactiveprimary, and get_coverage_summary() aggregates over all of them.
 *
 * Deliberately holds no display data (name, email): callers batch-fetch that
 * themselves for the page of studentids actually shown, same pattern as
 * assignment_repository::search() in assignments/index.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class unassigned_student {

    /**
     * @param int $studentid
     * @param int[] $cohortids selected cohorts this student belongs to
     * @param bool $suspended
     * @param bool $deleted
     * @param bool $hasactiveprimary
     * @param string $statuscode one of unassigned_status_code::values()
     * @param string[] $conflictcodes assignment_conflict_code::values() detected for this student
     * @param int|null $lastprimaryassignmentid most recent non-active, non-future primary row
     * @param int|null $lastprimarytutorid
     * @param int|null $lastprimarytimeend
     * @param int|null $futureprimaryassignmentid soonest not-yet-started primary row
     * @param int|null $futureprimarytutorid
     * @param int|null $futureprimarytimestart
     * @param int $suggestedstartdate
     */
    public function __construct(
        public readonly int $studentid,
        public readonly array $cohortids,
        public readonly bool $suspended,
        public readonly bool $deleted,
        public readonly bool $hasactiveprimary,
        public readonly string $statuscode,
        public readonly array $conflictcodes,
        public readonly ?int $lastprimaryassignmentid,
        public readonly ?int $lastprimarytutorid,
        public readonly ?int $lastprimarytimeend,
        public readonly ?int $futureprimaryassignmentid,
        public readonly ?int $futureprimarytutorid,
        public readonly ?int $futureprimarytimestart,
        public readonly int $suggestedstartdate
    ) {
    }
}
