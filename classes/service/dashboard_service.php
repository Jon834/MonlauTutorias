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

namespace local_monlaututoria\service;

use local_monlaututoria\domain\agreement;
use local_monlaututoria\domain\followup;
use local_monlaututoria\domain\priority_level;
use local_monlaututoria\domain\referral;
use local_monlaututoria\domain\tutor_dashboard;
use local_monlaututoria\domain\tutor_dashboard_student;
use local_monlaututoria\domain\tutor_dashboard_summary;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;

/**
 * Builds the tutor dashboard (phase 7): current primary students, tutoring
 * coverage, pending operational work, quick-action context and the compact
 * block summary.
 *
 * Coverage means "already has at least one active tutoring entry in the
 * selected academic year". Priority students are derived, never persisted:
 * any student with an overdue agreement/follow-up, any visible open referral,
 * or any high-priority follow-up/referral is surfaced as priority.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class dashboard_service {

    /** @var assignment_repository */
    private $assignmentrepository;

    /** @var entry_repository */
    private $entryrepository;

    /** @var academic_year_repository */
    private $academicyearrepository;

    /** @var followup_repository */
    private $followuprepository;

    /** @var agreement_repository */
    private $agreementrepository;

    /** @var referral_service */
    private $referralservice;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?entry_repository $entryrepository = null,
        ?academic_year_repository $academicyearrepository = null,
        ?followup_repository $followuprepository = null,
        ?agreement_repository $agreementrepository = null,
        ?referral_service $referralservice = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
        $this->followuprepository = $followuprepository ?? new followup_repository();
        $this->agreementrepository = $agreementrepository ?? new agreement_repository();
        $this->referralservice = $referralservice ?? new referral_service();
    }

    /**
     * Returns the dashboard for the active academic year, or null when there
     * is no active year yet.
     *
     * @param int $tutorid
     * @param int|null $now injectable for tests
     * @return tutor_dashboard|null
     */
    public function get_active_tutor_dashboard(int $tutorid, ?int $now = null): ?tutor_dashboard {
        $academicyear = $this->academicyearrepository->get_active();
        if ($academicyear === null) {
            return null;
        }

        return $this->get_tutor_dashboard($tutorid, (int) $academicyear->id, $now);
    }

    /**
     * @param int $tutorid
     * @param int $academicyearid
     * @param int|null $now injectable for tests
     * @return tutor_dashboard
     */
    public function get_tutor_dashboard(int $tutorid, int $academicyearid, ?int $now = null): tutor_dashboard {
        $now = $now ?? time();
        $this->academicyearrepository->get($academicyearid);

        $rows = array_values($this->assignmentrepository->find_current_primary_by_tutor($tutorid, $academicyearid, $now));
        $studentids = array_map(static fn (\stdClass $row): int => (int) $row->studentid, $rows);

        $counts = $this->entryrepository->count_active_by_students($studentids, $academicyearid);
        $latestentries = $this->entryrepository->get_latest_active_by_students($studentids, $academicyearid);
        $familycontactcount = $this->entryrepository->count_family_contacts_by_students($studentids, $academicyearid);

        $followups = array_map(
            static fn (\stdClass $row): followup => followup::from_record($row),
            array_values($this->followuprepository->find_open_by_students($studentids))
        );
        $agreements = array_map(
            static fn (\stdClass $row): agreement => agreement::from_record($row),
            array_values($this->agreementrepository->find_open_by_students($studentids))
        );
        $referrals = $this->referralservice->list_open_for_students($studentids, $tutorid);

        $followupsbystudent = [];
        $agreementsbystudent = [];
        $referralsbystudent = [];
        $upcomingfollowups = [];
        $overduefollowups = [];
        foreach ($followups as $followup) {
            $followupsbystudent[$followup->studentid][] = $followup;
            if ($followup->is_overdue()) {
                $overduefollowups[] = $followup;
            } else {
                $upcomingfollowups[] = $followup;
            }
        }

        $pendingagreements = [];
        $overdueagreements = [];
        foreach ($agreements as $agreement) {
            $agreementsbystudent[$agreement->studentid][] = $agreement;
            if ($agreement->is_overdue()) {
                $overdueagreements[] = $agreement;
            } else {
                $pendingagreements[] = $agreement;
            }
        }

        foreach ($referrals as $referral) {
            $referralsbystudent[$referral->studentid][] = $referral;
        }

        $students = [];
        $prioritystudents = [];
        $attendedcount = 0;
        foreach ($rows as $row) {
            $studentid = (int) $row->studentid;
            $entrycount = (int) ($counts[$studentid] ?? 0);
            $covered = $entrycount > 0;
            if ($covered) {
                $attendedcount++;
            }

            $studentfollowups = $followupsbystudent[$studentid] ?? [];
            $studentagreements = $agreementsbystudent[$studentid] ?? [];
            $studentreferrals = $referralsbystudent[$studentid] ?? [];

            $overduefollowupcount = count(array_filter($studentfollowups, static fn (followup $followup): bool => $followup->is_overdue()));
            $overdueagreementcount = count(array_filter($studentagreements, static fn (agreement $agreement): bool => $agreement->is_overdue()));
            $hashighpriorityfollowup = !empty(array_filter(
                $studentfollowups,
                static fn (followup $followup): bool => $followup->priority === priority_level::HIGH
            ));
            $hashighpriorityreferral = !empty(array_filter(
                $studentreferrals,
                static fn (referral $referral): bool => $referral->priority === priority_level::HIGH
            ));
            $ispriority = $overduefollowupcount > 0
                || $overdueagreementcount > 0
                || !empty($studentreferrals)
                || $hashighpriorityfollowup
                || $hashighpriorityreferral;

            $student = new tutor_dashboard_student(
                $studentid,
                $entrycount,
                $latestentries[$studentid] ?? null,
                !$covered,
                $covered,
                count($studentfollowups),
                $overduefollowupcount,
                count($studentagreements),
                $overdueagreementcount,
                count($studentreferrals),
                $ispriority
            );
            $students[] = $student;
            if ($ispriority) {
                $prioritystudents[] = $student;
            }
        }

        $assignedcount = count($students);
        $pendinginitialcount = $assignedcount - $attendedcount;
        $coveragepercent = $assignedcount > 0 ? round(($attendedcount / $assignedcount) * 100, 2) : 0.0;

        return new tutor_dashboard(
            $tutorid,
            $academicyearid,
            $students,
            new tutor_dashboard_summary(
                $assignedcount,
                $attendedcount,
                $pendinginitialcount,
                $coveragepercent,
                count($upcomingfollowups),
                count($overduefollowups),
                count($pendingagreements),
                count($overdueagreements),
                count($referrals),
                count($prioritystudents),
                $familycontactcount
            ),
            $upcomingfollowups,
            $overduefollowups,
            $pendingagreements,
            $overdueagreements,
            $referrals,
            $prioritystudents
        );
    }
}
