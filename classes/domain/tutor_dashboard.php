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
 * Immutable aggregate for the tutor dashboard page (phase 7).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tutor_dashboard {

    /**
     * @param int $tutorid
     * @param int $academicyearid
     * @param tutor_dashboard_student[] $students
     * @param tutor_dashboard_summary $summary
     * @param followup[] $upcomingfollowups
     * @param followup[] $overduefollowups
     * @param agreement[] $pendingagreements
     * @param agreement[] $overdueagreements
     * @param referral[] $referrals
     * @param tutor_dashboard_student[] $prioritystudents
     */
    public function __construct(
        public readonly int $tutorid,
        public readonly int $academicyearid,
        public readonly array $students,
        public readonly tutor_dashboard_summary $summary,
        public readonly array $upcomingfollowups,
        public readonly array $overduefollowups,
        public readonly array $pendingagreements,
        public readonly array $overdueagreements,
        public readonly array $referrals,
        public readonly array $prioritystudents
    ) {
    }
}
