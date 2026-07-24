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
 * Immutable dashboard row for one tutor-owned student (phase 7).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tutor_dashboard_student {

    /**
     * @param int $studentid
     * @param int $activeentrycount active tutoring entries in the requested academic year
     * @param \stdClass|null $latestactiveentry latest active local_tut_entry row in that academic year
     * @param bool $missinginitial true when the student still has no active tutoring entry this year
     * @param bool $covered true when the student already has at least one active tutoring entry this year
     * @param int $openfollowupcount open follow-ups tied to this student
     * @param int $overduefollowupcount open follow-ups already overdue
     * @param int $openagreementcount open agreements tied to this student
     * @param int $overdueagreementcount open agreements already overdue
     * @param int $openreferralcount visible open referrals tied to this student
     * @param bool $ispriority true when the student should surface in the dashboard's priority bucket
     */
    public function __construct(
        public readonly int $studentid,
        public readonly int $activeentrycount,
        public readonly ?\stdClass $latestactiveentry,
        public readonly bool $missinginitial,
        public readonly bool $covered,
        public readonly int $openfollowupcount,
        public readonly int $overduefollowupcount,
        public readonly int $openagreementcount,
        public readonly int $overdueagreementcount,
        public readonly int $openreferralcount,
        public readonly bool $ispriority
    ) {
    }
}
