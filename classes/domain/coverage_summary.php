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
 * Immutable aggregate coverage indicators for a cohort population, returned
 * by unassigned_students_service::get_coverage_summary().
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class coverage_summary {

    /**
     * @param int $analyzedcount total students in the requested population
     * @param int $withprimarycount students with a vigente primary tutor
     * @param int $withoutprimarycount students without one
     * @param float $coveragepercent 0-100, 0.0 when analyzedcount is 0
     * @param int $suspendedcount suspended accounts within the population
     * @param int $futurependingcount students without a current tutor but with one scheduled
     * @param int $conflictcount students with at least one detected conflict
     */
    public function __construct(
        public readonly int $analyzedcount,
        public readonly int $withprimarycount,
        public readonly int $withoutprimarycount,
        public readonly float $coveragepercent,
        public readonly int $suspendedcount,
        public readonly int $futurependingcount,
        public readonly int $conflictcount
    ) {
    }
}
