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
 * Immutable result of cohort_assignment_preview_service::preview(). Only
 * operationuuid and summary are persisted (on local_tut_bulkoperation);
 * items always come from a fresh, in-memory classification.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_preview {

    /**
     * @param string $operationuuid
     * @param int $operationid
     * @param cohort_assignment_summary $summary
     * @param cohort_assignment_item[] $items
     */
    public function __construct(
        public readonly string $operationuuid,
        public readonly int $operationid,
        public readonly cohort_assignment_summary $summary,
        public readonly array $items
    ) {
    }
}
