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
 * Immutable per-student classification result within a cohort_assignment_preview.
 * Computed on demand, never persisted (see cohort_assignment_preview_service
 * class docblock for why). Carries a primary-tutor action and, independently,
 * an optional co-tutor action: a student's outcome for each role is not
 * always the same (e.g. their primary tutor may already be correct while a
 * co-tutor still needs to be added).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_item {

    /**
     * @param int $studentid
     * @param string $action one of cohort_assignment_action::values(), for the primary tutor role
     * @param string|null $cotutoraction one of cohort_assignment_action::values(), only when a co-tutor was requested
     * @param bool $suspended
     * @param bool $deleted
     * @param int|null $currentprimarytutorid
     * @param int|null $currentprimaryassignmentid
     * @param string[] $conflictcodes assignment_conflict_code::values() detected for this student
     */
    public function __construct(
        public readonly int $studentid,
        public readonly string $action,
        public readonly ?string $cotutoraction,
        public readonly bool $suspended,
        public readonly bool $deleted,
        public readonly ?int $currentprimarytutorid,
        public readonly ?int $currentprimaryassignmentid,
        public readonly array $conflictcodes
    ) {
    }
}
