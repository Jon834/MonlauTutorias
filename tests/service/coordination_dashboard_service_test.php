<?php
namespace local_monlaututoria\service;

use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\coordination_scope_repository;
use local_monlaututoria\repository\entry_reason_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\reason_repository;

final class coordination_dashboard_service_test extends \advanced_testcase {

    private function grant_capability(int $userid, string $capability): void {
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $userid, \context_system::instance()->id);
    }

    private function create_year(): int {
        return (new academic_year_repository())->create((object) [
            'name' => '2026-2027',
            'shortname' => 'coord-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
    }

    public function test_dashboard_is_limited_to_assigned_scope(): void {
        $this->resetAfterTest();

        $viewer = $this->getDataGenerator()->create_user();
        $this->grant_capability($viewer->id, 'local/monlaututoria:viewcoordinationdashboard');

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        global $DB;
        $DB->insert_record('cohort_members', (object) ['cohortid' => $cohort1->id, 'userid' => $student1->id, 'timeadded' => time()]);
        $DB->insert_record('cohort_members', (object) ['cohortid' => $cohort2->id, 'userid' => $student2->id, 'timeadded' => time()]);

        (new coordination_scope_repository())->replace_user_scopes($viewer->id, [$cohort1->id], get_admin()->id);

        $year = $this->create_year();
        $tutor = $this->getDataGenerator()->create_user();
        (new assignment_repository())->create((object) [
            'studentid' => $student1->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $year,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => time() - DAYSECS,
            'createdby' => get_admin()->id,
        ]);
        (new assignment_repository())->create((object) [
            'studentid' => $student2->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $year,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => time() - DAYSECS,
            'createdby' => get_admin()->id,
        ]);

        $dashboard = (new coordination_dashboard_service())->get_dashboard($viewer->id, $year, [$cohort1->id]);

        $this->assertSame(1, $dashboard->summary->populationcount);
        $this->assertCount(1, $dashboard->cohortbreakdown);
        $this->assertSame(1, $dashboard->cohortbreakdown[0]->studentcount);
    }

    public function test_dashboard_counts_initial_entries_and_quality_indicators(): void {
        $this->resetAfterTest();

        $viewer = $this->getDataGenerator()->create_user();
        $this->grant_capability($viewer->id, 'local/monlaututoria:viewcoordinationdashboard');

        $cohort = $this->getDataGenerator()->create_cohort(['name' => 'ESO A']);
        $student = $this->getDataGenerator()->create_user();
        global $DB;
        $DB->insert_record('cohort_members', (object) ['cohortid' => $cohort->id, 'userid' => $student->id, 'timeadded' => time()]);
        (new coordination_scope_repository())->replace_user_scopes($viewer->id, [$cohort->id], get_admin()->id);

        $year = $this->create_year();
        $tutor = $this->getDataGenerator()->create_user();
        (new assignment_repository())->create((object) [
            'studentid' => $student->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $year,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => strtotime('2026-09-01'),
            'createdby' => get_admin()->id,
        ]);

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $year,
            'entrydate' => strtotime('2026-09-03'),
            'createdby' => get_admin()->id,
        ]);
        $initialreason = (new reason_repository())->find_by_shortname('acogida_inicial');
        (new entry_reason_repository())->attach($entryid, [(int) $initialreason->id]);
        (new followup_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $student->id,
            'duedate' => strtotime('2026-09-10'),
            'createdby' => get_admin()->id,
        ]);
        $followuprepo = new followup_repository();
        $followup = array_values($followuprepo->find_by_students([$student->id]))[0];
        $followuprepo->update_status((int) $followup->id, 'completed', get_admin()->id, strtotime('2026-09-08'));
        (new agreement_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $student->id,
            'description' => 'Call family',
            'responsibletype' => agreement_responsible_type::FAMILY,
            'responsibleexternalname' => 'Parent',
            'duedate' => strtotime('2026-09-15'),
            'createdby' => get_admin()->id,
        ]);
        $agreementrepo = new agreement_repository();
        $agreement = array_values($agreementrepo->find_by_students([$student->id]))[0];
        $agreementrepo->update_status((int) $agreement->id, 'completed', get_admin()->id);

        $dashboard = (new coordination_dashboard_service())->get_dashboard($viewer->id, $year, [$cohort->id], null, strtotime('2026-09-20'));

        $this->assertSame(1, $dashboard->summary->withinitialcount);
        $this->assertSame(0, $dashboard->summary->withoutentrycount);
        $this->assertSame(0, $dashboard->summary->overduefollowupcount);
        $this->assertSame(2.0, $dashboard->quality->averagedaystofirstentry);
        $this->assertSame(100.0, $dashboard->quality->agreementcompletionpercent);
        $this->assertSame(100.0, $dashboard->quality->followupontimepercent);
    }
}
