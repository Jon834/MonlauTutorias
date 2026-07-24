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
 * Immutable summary cards for the tutor dashboard (phase 7).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tutor_dashboard_summary {

    /**
     * @param int $assignedcount current primary students assigned to the tutor in this academic year
     * @param int $attendedcount assigned students with at least one active tutoring entry in this academic year
     * @param int $pendinginitialcount assigned students still lacking that first tutoring entry
     * @param float $coveragepercent 0-100, 0.0 when assignedcount is 0
     * @param int $upcomingfollowupcount open follow-ups not yet overdue
     * @param int $overduefollowupcount open follow-ups already overdue
     * @param int $pendingagreementcount open agreements not yet overdue
     * @param int $overdueagreementcount open agreements already overdue
     * @param int $openreferralcount visible open referrals
     * @param int $prioritystudentcount assigned students currently flagged as priority
     * @param int $familycontactcount tutoring entries involving family contact in this academic year
     */
    public function __construct(
        public readonly int $assignedcount,
        public readonly int $attendedcount,
        public readonly int $pendinginitialcount,
        public readonly float $coveragepercent,
        public readonly int $upcomingfollowupcount,
        public readonly int $overduefollowupcount,
        public readonly int $pendingagreementcount,
        public readonly int $overdueagreementcount,
        public readonly int $openreferralcount,
        public readonly int $prioritystudentcount,
        public readonly int $familycontactcount
    ) {
    }
}
