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

use local_monlaututoria\domain\agreement_status;
use local_monlaututoria\domain\coordination_breakdown_row;
use local_monlaututoria\domain\coordination_dashboard;
use local_monlaututoria\domain\coordination_dashboard_summary;
use local_monlaututoria\domain\coordination_quality_summary;
use local_monlaututoria\domain\followup_status;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\cohort_membership_repository;
use local_monlaututoria\repository\cohort_repository;
use local_monlaututoria\repository\entry_reason_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\reason_repository;

/**
 * Aggregated coordination dashboard and export source (phase 8).
 *
 * The dashboard is deliberately built only from aggregate and operational
 * counts. It never exposes noteinternal/noterestricted or any free-text field
 * from tutoring entries, agreements or referrals.
 */
final class coordination_dashboard_service {

    private assignment_repository $assignmentrepository;
    private entry_repository $entryrepository;
    private entry_reason_repository $entryreasonrepository;
    private reason_repository $reasonrepository;
    private followup_repository $followuprepository;
    private agreement_repository $agreementrepository;
    private referral_service $referralservice;
    private cohort_membership_repository $cohortmembershiprepository;
    private cohort_repository $cohortrepository;
    private academic_year_repository $academicyearrepository;
    private coordination_scope_service $coordscopeservice;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?entry_repository $entryrepository = null,
        ?entry_reason_repository $entryreasonrepository = null,
        ?reason_repository $reasonrepository = null,
        ?followup_repository $followuprepository = null,
        ?agreement_repository $agreementrepository = null,
        ?referral_service $referralservice = null,
        ?cohort_membership_repository $cohortmembershiprepository = null,
        ?cohort_repository $cohortrepository = null,
        ?academic_year_repository $academicyearrepository = null,
        ?coordination_scope_service $coordscopeservice = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->entryreasonrepository = $entryreasonrepository ?? new entry_reason_repository();
        $this->reasonrepository = $reasonrepository ?? new reason_repository();
        $this->followuprepository = $followuprepository ?? new followup_repository();
        $this->agreementrepository = $agreementrepository ?? new agreement_repository();
        $this->referralservice = $referralservice ?? new referral_service();
        $this->cohortmembershiprepository = $cohortmembershiprepository ?? new cohort_membership_repository();
        $this->cohortrepository = $cohortrepository ?? new cohort_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
        $this->coordscopeservice = $coordscopeservice ?? new coordination_scope_service();
    }

    /**
     * @param int $viewerid
     * @param int $academicyearid
     * @param int[] $cohortids
     * @param int|null $selectedtutorid
     * @param int|null $now injectable for tests
     * @return coordination_dashboard
     */
    public function get_dashboard(
        int $viewerid,
        int $academicyearid,
        array $cohortids,
        ?int $selectedtutorid = null,
        ?int $now = null
    ): coordination_dashboard {
        $now = $now ?? time();
        $this->academicyearrepository->get($academicyearid);
        $cohortids = array_values(array_unique(array_map('intval', $cohortids)));
        $this->coordscopeservice->require_user_can_access_cohorts($viewerid, $cohortids);

        $members = $this->cohortmembershiprepository->get_members($cohortids);
        $studentids = array_map('intval', array_keys($members));
        $cohortlabels = [];
        foreach ($this->cohortrepository->get_many($cohortids) as $cohort) {
            $cohortlabels[(int) $cohort->id] = format_string($cohort->name, true, ['context' => \context_system::instance()]);
        }

        if (empty($studentids)) {
            return new coordination_dashboard(
                $viewerid,
                $academicyearid,
                $cohortids,
                $selectedtutorid,
                $now,
                new coordination_dashboard_summary(0, 0, 0, 0, 0),
                new coordination_quality_summary(0.0, 0, 0.0, 0, 0, 0.0, 0, 0, 0, 0.0, 0, 0),
                $cohortlabels,
                [],
                [],
                []
            );
        }

        $memberships = $this->cohortmembershiprepository->get_memberships($cohortids, $studentids);
        $primaryrows = array_values($this->assignmentrepository->find_primary_rows_for_students($studentids, $academicyearid));
        $rowsbystudent = [];
        foreach ($primaryrows as $row) {
            $rowsbystudent[(int) $row->studentid][] = $row;
        }

        $currentprimarybystudent = [];
        foreach ($studentids as $studentid) {
            $current = $this->resolve_current_primary($rowsbystudent[$studentid] ?? [], $now);
            if ($current !== null) {
                $currentprimarybystudent[$studentid] = $current;
            }
        }

        if ($selectedtutorid !== null) {
            $studentids = array_values(array_filter(
                $studentids,
                fn (int $studentid): bool => isset($currentprimarybystudent[$studentid])
                    && (int) $currentprimarybystudent[$studentid]->tutorid === $selectedtutorid
            ));
        }

        $tutorids = [];
        foreach ($currentprimarybystudent as $studentid => $row) {
            if (!in_array((int) $studentid, $studentids, true)) {
                continue;
            }
            $tutorids[(int) $row->tutorid] = true;
        }
        $tutoroptions = [];
        if (!empty($tutorids)) {
            $users = \core_user::get_users_by_id(array_keys($tutorids));
            foreach ($users as $user) {
                $tutoroptions[(int) $user->id] = fullname($user);
            }
            asort($tutoroptions);
        }

        $entrycounts = $this->entryrepository->count_active_by_students($studentids, $academicyearid);
        $activeentries = array_values($this->entryrepository->find_active_by_students($studentids, $academicyearid));
        $firstentries = $this->entryrepository->get_first_active_by_students($studentids, $academicyearid);
        $familycontactcount = $this->entryrepository->count_family_contacts_by_students($studentids, $academicyearid);

        $entryids = array_map(static fn (\stdClass $row): int => (int) $row->id, $activeentries);
        $entryreasonmap = $this->entryreasonrepository->get_for_entries($entryids);
        $initialreason = $this->reasonrepository->find_by_shortname('acogida_inicial');
        $initialreasonid = $initialreason ? (int) $initialreason->id : null;
        $studentswithinitial = [];
        if ($initialreasonid !== null) {
            foreach ($activeentries as $entry) {
                $reasons = $entryreasonmap[(int) $entry->id] ?? [];
                if (in_array($initialreasonid, $reasons, true)) {
                    $studentswithinitial[(int) $entry->studentid] = true;
                }
            }
        }

        $followups = array_values($this->followuprepository->find_by_students($studentids));
        $agreements = array_values($this->agreementrepository->find_by_students($studentids));
        $referrals = $this->referralservice->list_open_for_students($studentids, $viewerid);

        $followupsbystudent = [];
        $agreementsbystudent = [];
        $referralsbystudent = [];
        $overduefollowupcount = 0;
        $completedfollowups = [];
        foreach ($followups as $row) {
            $studentid = (int) $row->studentid;
            $followupsbystudent[$studentid][] = $row;
            if (in_array($row->status, followup_status::open_values(), true) && (int) $row->duedate < $now) {
                $overduefollowupcount++;
            }
            if ($row->status === followup_status::COMPLETED) {
                $completedfollowups[] = $row;
            }
        }

        $completedagreements = 0;
        foreach ($agreements as $row) {
            $studentid = (int) $row->studentid;
            $agreementsbystudent[$studentid][] = $row;
            if ($row->status === agreement_status::COMPLETED) {
                $completedagreements++;
            }
        }

        foreach ($referrals as $row) {
            $referralsbystudent[$row->studentid][] = $row;
        }

        $studentswithoutentry = 0;
        $studentswithopencase = 0;
        foreach ($studentids as $studentid) {
            if ((int) ($entrycounts[$studentid] ?? 0) === 0) {
                $studentswithoutentry++;
            }
            $hasopencase = !empty($followupsbystudent[$studentid]) || !empty($agreementsbystudent[$studentid]) || !empty($referralsbystudent[$studentid]);
            if ($hasopencase) {
                $studentswithopencase++;
            }
        }

        $cohortbreakdown = [];
        foreach ($cohortids as $cohortid) {
            $cohortstudentids = array_values(array_filter(
                $studentids,
                fn (int $studentid): bool => in_array($cohortid, $memberships[$studentid] ?? [], true)
            ));
            $cohortbreakdown[] = $this->build_breakdown_row($cohortid, $cohortlabels[$cohortid] ?? ('#' . $cohortid), $cohortstudentids, $entrycounts, $studentswithinitial, $followupsbystudent, $agreementsbystudent, $referralsbystudent, $now);
        }

        $tutorbreakdown = [];
        $unassignedstudentids = array_values(array_filter(
            $studentids,
            fn (int $studentid): bool => !isset($currentprimarybystudent[$studentid])
        ));
        if (!empty($unassignedstudentids)) {
            $tutorbreakdown[] = $this->build_breakdown_row(null, get_string('coordination_breakdown_unassigned', 'local_monlaututoria'), $unassignedstudentids, $entrycounts, $studentswithinitial, $followupsbystudent, $agreementsbystudent, $referralsbystudent, $now);
        }
        foreach ($tutoroptions as $tutorid => $label) {
            $tutorstudentids = array_values(array_filter(
                $studentids,
                fn (int $studentid): bool => isset($currentprimarybystudent[$studentid])
                    && (int) $currentprimarybystudent[$studentid]->tutorid === $tutorid
            ));
            $tutorbreakdown[] = $this->build_breakdown_row($tutorid, $label, $tutorstudentids, $entrycounts, $studentswithinitial, $followupsbystudent, $agreementsbystudent, $referralsbystudent, $now);
        }

        $averagedays = 0.0;
        $firstentrysample = 0;
        foreach ($studentids as $studentid) {
            if (!isset($firstentries[$studentid])) {
                continue;
            }
            $assignmentstart = $this->resolve_assignment_start($rowsbystudent[$studentid] ?? []);
            if ($assignmentstart === null) {
                continue;
            }
            $days = max(0, ((int) $firstentries[$studentid]->entrydate - $assignmentstart) / DAYSECS);
            $averagedays += $days;
            $firstentrysample++;
        }
        $averagedays = $firstentrysample > 0 ? round($averagedays / $firstentrysample, 2) : 0.0;

        $completedfollowupcount = count($completedfollowups);
        $ontimefollowupcount = 0;
        $closingentryids = array_values(array_unique(array_filter(array_map(static fn (\stdClass $row): ?int => isset($row->closingentryid) ? (int) $row->closingentryid : null, $completedfollowups))));
        $closingentries = $this->entryrepository->get_many($closingentryids);
        foreach ($completedfollowups as $followup) {
            $completedat = isset($followup->closingentryid) && isset($closingentries[(int) $followup->closingentryid])
                ? (int) $closingentries[(int) $followup->closingentryid]->entrydate
                : (int) $followup->timemodified;
            if ($completedat <= (int) $followup->duedate) {
                $ontimefollowupcount++;
            }
        }

        $continuitysample = 0;
        $continuitysuccess = 0;
        foreach ($studentids as $studentid) {
            foreach ($rowsbystudent[$studentid] ?? [] as $row) {
                if (empty($row->reassignreason)) {
                    continue;
                }
                $continuitysample++;
                foreach ($activeentries as $entry) {
                    if ((int) $entry->studentid === $studentid && (int) $entry->tutorid === (int) $row->tutorid && (int) $entry->entrydate >= (int) $row->timestart) {
                        $continuitysuccess++;
                        break;
                    }
                }
            }
        }

        return new coordination_dashboard(
            $viewerid,
            $academicyearid,
            $cohortids,
            $selectedtutorid,
            $now,
            new coordination_dashboard_summary(
                count($studentids),
                count($studentswithinitial),
                $studentswithoutentry,
                $overduefollowupcount,
                $studentswithopencase
            ),
            new coordination_quality_summary(
                $averagedays,
                $firstentrysample,
                count($agreements) > 0 ? round(($completedagreements / count($agreements)) * 100, 2) : 0.0,
                $completedagreements,
                count($agreements),
                $completedfollowupcount > 0 ? round(($ontimefollowupcount / $completedfollowupcount) * 100, 2) : 0.0,
                $ontimefollowupcount,
                $completedfollowupcount,
                $familycontactcount,
                $continuitysample > 0 ? round(($continuitysuccess / $continuitysample) * 100, 2) : 0.0,
                $continuitysuccess,
                $continuitysample
            ),
            $cohortlabels,
            $tutoroptions,
            $cohortbreakdown,
            $tutorbreakdown
        );
    }

    /**
     * @param coordination_dashboard $dashboard
     * @param string $format
     * @return array<int, array<string, string|int|float>>
     */
    public function build_export_rows(coordination_dashboard $dashboard, string $format): array {
        $generatedat = userdate($dashboard->generatedat, get_string('strftimedatetime', 'langconfig'));
        $rows = [];

        $rows[] = [
            'section' => 'summary',
            'label' => get_string('coordination_export_summary', 'local_monlaututoria'),
            'population' => $dashboard->summary->populationcount,
            'withinitial' => $dashboard->summary->withinitialcount,
            'withoutentry' => $dashboard->summary->withoutentrycount,
            'overduefollowups' => $dashboard->summary->overduefollowupcount,
            'opencases' => $dashboard->summary->opencasecount,
            'generatedat' => $generatedat,
            'format' => $format,
        ];

        foreach ($dashboard->cohortbreakdown as $row) {
            $rows[] = [
                'section' => 'cohort',
                'label' => $row->label,
                'population' => $row->studentcount,
                'withinitial' => $row->withinitialcount,
                'withoutentry' => $row->withoutentrycount,
                'overduefollowups' => $row->overduefollowupcount,
                'opencases' => $row->opencasecount,
                'generatedat' => $generatedat,
                'format' => $format,
            ];
        }

        foreach ($dashboard->tutorbreakdown as $row) {
            $rows[] = [
                'section' => 'tutor',
                'label' => $row->label,
                'population' => $row->studentcount,
                'withinitial' => $row->withinitialcount,
                'withoutentry' => $row->withoutentrycount,
                'overduefollowups' => $row->overduefollowupcount,
                'opencases' => $row->opencasecount,
                'generatedat' => $generatedat,
                'format' => $format,
            ];
        }

        $rows[] = [
            'section' => 'quality',
            'label' => get_string('coordination_quality_timetofirst', 'local_monlaututoria'),
            'population' => $dashboard->quality->studentswithfirstentrysample,
            'withinitial' => $dashboard->quality->averagedaystofirstentry,
            'withoutentry' => '',
            'overduefollowups' => '',
            'opencases' => '',
            'generatedat' => $generatedat,
            'format' => $format,
        ];
        $rows[] = [
            'section' => 'quality',
            'label' => get_string('coordination_quality_agreements', 'local_monlaututoria'),
            'population' => $dashboard->quality->totalagreementcount,
            'withinitial' => $dashboard->quality->completedagreementcount,
            'withoutentry' => $dashboard->quality->agreementcompletionpercent,
            'overduefollowups' => '',
            'opencases' => '',
            'generatedat' => $generatedat,
            'format' => $format,
        ];
        $rows[] = [
            'section' => 'quality',
            'label' => get_string('coordination_quality_followups', 'local_monlaututoria'),
            'population' => $dashboard->quality->completedfollowupcount,
            'withinitial' => $dashboard->quality->ontimefollowupcount,
            'withoutentry' => $dashboard->quality->followupontimepercent,
            'overduefollowups' => '',
            'opencases' => '',
            'generatedat' => $generatedat,
            'format' => $format,
        ];
        $rows[] = [
            'section' => 'quality',
            'label' => get_string('coordination_quality_familycontacts', 'local_monlaututoria'),
            'population' => $dashboard->quality->familycontactcount,
            'withinitial' => '',
            'withoutentry' => '',
            'overduefollowups' => '',
            'opencases' => '',
            'generatedat' => $generatedat,
            'format' => $format,
        ];
        $rows[] = [
            'section' => 'quality',
            'label' => get_string('coordination_quality_continuity', 'local_monlaututoria'),
            'population' => $dashboard->quality->continuitysamplecount,
            'withinitial' => $dashboard->quality->continuitysuccesscount,
            'withoutentry' => $dashboard->quality->continuitypercent,
            'overduefollowups' => '',
            'opencases' => '',
            'generatedat' => $generatedat,
            'format' => $format,
        ];

        return $rows;
    }

    /**
     * @param \stdClass[] $rows
     * @param int $now
     * @return \stdClass|null
     */
    private function resolve_current_primary(array $rows, int $now): ?\stdClass {
        foreach ($rows as $row) {
            if ($row->status === 'active' && (int) $row->timestart <= $now && ($row->timeend === null || (int) $row->timeend > $now)) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param \stdClass[] $rows
     * @return int|null
     */
    private function resolve_assignment_start(array $rows): ?int {
        $starts = array_map(static fn (\stdClass $row): int => (int) $row->timestart, $rows);
        if (empty($starts)) {
            return null;
        }

        return min($starts);
    }

    /**
     * @param int|null $entityid
     * @param string $label
     * @param int[] $studentids
     * @param array<int, int> $entrycounts
     * @param array<int, bool> $studentswithinitial
     * @param array<int, array<int, \stdClass>> $followupsbystudent
     * @param array<int, array<int, \stdClass>> $agreementsbystudent
     * @param array<int, array<int, mixed>> $referralsbystudent
     * @param int $now
     * @return coordination_breakdown_row
     */
    private function build_breakdown_row(
        ?int $entityid,
        string $label,
        array $studentids,
        array $entrycounts,
        array $studentswithinitial,
        array $followupsbystudent,
        array $agreementsbystudent,
        array $referralsbystudent,
        int $now
    ): coordination_breakdown_row {
        $withinitial = 0;
        $withoutentry = 0;
        $overduefollowups = 0;
        $opencases = 0;

        foreach ($studentids as $studentid) {
            if (!empty($studentswithinitial[$studentid])) {
                $withinitial++;
            }
            if ((int) ($entrycounts[$studentid] ?? 0) === 0) {
                $withoutentry++;
            }

            $studentoverdue = 0;
            foreach ($followupsbystudent[$studentid] ?? [] as $followup) {
                if (in_array($followup->status, followup_status::open_values(), true) && (int) $followup->duedate < $now) {
                    $studentoverdue++;
                }
            }
            $overduefollowups += $studentoverdue;

            if (!empty($followupsbystudent[$studentid]) || !empty($agreementsbystudent[$studentid]) || !empty($referralsbystudent[$studentid])) {
                $opencases++;
            }
        }

        return new coordination_breakdown_row(
            $entityid,
            $label,
            count($studentids),
            $withinitial,
            $withoutentry,
            $overduefollowups,
            $opencases
        );
    }
}
