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
 * Immutable per-student result of applying a cohort assignment operation
 * (the missing "confirm" step cohort_assignment_preview_service's own
 * docblock names as phases 3C.3-3C.5). Primary and co-tutor outcomes are
 * reported independently, mirroring cohort_assignment_item's own split —
 * a student can get a new primary tutor while their co-tutor status stays
 * unchanged, or vice versa.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_result_row {

    /**
     * @param int $studentid
     * @param string $outcome one of cohort_assignment_action::values(), for the primary tutor role
     * @param int|null $primaryassignmentid the created/reassigned/closed assignment id, when applicable
     * @param string|null $cotutoroutcome one of cohort_assignment_action::values(), only when a co-tutor was requested
     * @param int|null $cotutorassignmentid the created co-tutor assignment id, when applicable
     * @param string|null $errormessagecode a lang string key, never a raw exception message
     */
    public function __construct(
        public readonly int $studentid,
        public readonly string $outcome,
        public readonly ?int $primaryassignmentid,
        public readonly ?string $cotutoroutcome,
        public readonly ?int $cotutorassignmentid,
        public readonly ?string $errormessagecode = null
    ) {
    }
}
