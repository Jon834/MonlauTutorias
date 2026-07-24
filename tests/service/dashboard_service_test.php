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

use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\domain\entry_participant_type;
use local_monlaututoria\domain\entry_status;
use local_monlaututoria\domain\priority_level;
use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\entry_participant_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\referral_repository;

/**
 * Tests for dashboard_service (phase 7 - tutor dashboard complete).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class dashboard_service_test extends \advanced_testcase {

    /**
     * @param bool $active
     * @return int
     */
    private function create_academic_year(bool $active = false): int {
        $repository = new academic_year_repository();
        $id = $repository->create((object) [
            'name' => '2026-2027',
            'shortname' => 'dashboard-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        if ($active) {
            $repository->set_active_flag($id, true, get_admin()->id);
        }

        return $id;
    }

    private function create_assignment(int $studentid, int $tutorid, int $academicyearid, array $overrides = []): int {
        $data = (object) array_merge([
            'studentid' => $studentid,
            'tutorid' => $tutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => time() - DAYSECS,
            'timeend' => null,
            'createdby' => get_admin()->id,
        ], $overrides);

        return (new assignment_repository())->create($data);
    }

    private function create_entry(int $studentid, int $tutorid, int $academicyearid, array $overrides = []): int {
        return (new entry_repository())->create((object) array_merge([
            'studentid' => $studentid,
            'tutorid' => $tutorid,
            'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-10-01'),
            'createdby' => get_admin()->id,
        ], $overrides));
    }

    private function create_followup(int $entryid, int $studentid, int $duedate, string $priority = priority_level::MEDIUM): int {
        return (new followup_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $studentid,
            'duedate' => $duedate,
            'priority' => $priority,
            'createdby' => get_admin()->id,
        ]);
    }

    private function create_agreement(int $entryid, int $studentid, int $duedate): int {
        return (new agreement_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $studentid,
            'description' => 'Pending agreement',
            'responsibletype' => agreement_responsible_type::FAMILY,
            'responsibleexternalname' => 'Family',
            'duedate' => $duedate,
            'createdby' => get_admin()->id,
        ]);
    }

    private function create_referral(int $entryid, int $studentid, int $createdby, string $priority = priority_level::MEDIUM): int {
        return (new referral_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $studentid,
            'destination' => referral_destination::COORDINATION,
            'reason' => 'Escalated case',
            'priority' => $priority,
            'createdby' => $createdby,
        ]);
    }

    public function test_get_active_dashboard_returns_null_without_active_year(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();

        $this->assertNull((new dashboard_service())->get_active_tutor_dashboard($tutor->id));
    }

    public function test_lists_current_primary_students_with_latest_entry_and_count(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $now = strtotime('2026-10-15');

        $this->create_assignment($student1->id, $tutor->id, $year, ['timestart' => $now - (10 * DAYSECS)]);
        $this->create_assignment($student2->id, $tutor->id, $year, ['timestart' => $now - (10 * DAYSECS)]);

        $this->create_entry($student1->id, $tutor->id, $year, ['entrydate' => strtotime('2026-09-20')]);
        $latestid = $this->create_entry($student1->id, $tutor->id, $year, ['entrydate' => strtotime('2026-10-10')]);

        $dashboard = (new dashboard_service())->get_tutor_dashboard($tutor->id, $year, $now);

        $this->assertCount(2, $dashboard->students);
        $this->assertSame($student1->id, $dashboard->students[0]->studentid);
        $this->assertSame(2, $dashboard->students[0]->activeentrycount);
        $this->assertSame($latestid, (int) $dashboard->students[0]->latestactiveentry->id);
        $this->assertSame($student2->id, $dashboard->students[1]->studentid);
        $this->assertSame(0, $dashboard->students[1]->activeentrycount);
    }

    public function test_summary_tracks_followups_agreements_referrals_and_priority_students(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $now = strtotime('2026-10-15');

        $this->create_assignment($student1->id, $tutor->id, $year, ['timestart' => $now - DAYSECS]);
        $this->create_assignment($student2->id, $tutor->id, $year, ['timestart' => $now - DAYSECS]);

        $entry1 = $this->create_entry($student1->id, $tutor->id, $year, ['entrydate' => strtotime('2026-10-10')]);
        $entry2 = $this->create_entry($student2->id, $tutor->id, $year, ['entrydate' => strtotime('2026-10-10')]);

        $this->create_followup($entry1, $student1->id, $now - DAYSECS, priority_level::HIGH);
        $this->create_agreement($entry2, $student2->id, $now + DAYSECS);
        $this->create_referral($entry2, $student2->id, $tutor->id);

        $dashboard = (new dashboard_service())->get_tutor_dashboard($tutor->id, $year, $now);

        $this->assertSame(0, $dashboard->summary->upcomingfollowupcount);
        $this->assertSame(1, $dashboard->summary->overduefollowupcount);
        $this->assertSame(1, $dashboard->summary->pendingagreementcount);
        $this->assertSame(0, $dashboard->summary->overdueagreementcount);
        $this->assertSame(1, $dashboard->summary->openreferralcount);
        $this->assertSame(2, $dashboard->summary->prioritystudentcount);
        $this->assertCount(1, $dashboard->overduefollowups);
        $this->assertCount(1, $dashboard->pendingagreements);
        $this->assertCount(1, $dashboard->referrals);
        $this->assertTrue($dashboard->students[0]->ispriority);
        $this->assertTrue($dashboard->students[1]->ispriority);
    }

    public function test_family_contacts_are_counted_from_family_participants(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $this->create_assignment($student->id, $tutor->id, $year);
        $entryid = $this->create_entry($student->id, $tutor->id, $year);
        (new entry_participant_repository())->create((object) [
            'entryid' => $entryid,
            'participanttype' => entry_participant_type::FAMILY,
            'externalname' => 'Parent',
            'createdby' => get_admin()->id,
        ]);

        $dashboard = (new dashboard_service())->get_tutor_dashboard($tutor->id, $year);

        $this->assertSame(1, $dashboard->summary->familycontactcount);
    }

    public function test_referrals_not_visible_to_the_tutor_do_not_appear(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $otherstaff = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $now = strtotime('2026-10-15');

        $this->create_assignment($student->id, $tutor->id, $year, ['timestart' => $now - DAYSECS]);
        $entryid = $this->create_entry($student->id, $tutor->id, $year);
        $this->create_referral($entryid, $student->id, $otherstaff->id);

        $dashboard = (new dashboard_service())->get_tutor_dashboard($tutor->id, $year, $now);

        $this->assertSame(0, $dashboard->summary->openreferralcount);
        $this->assertEmpty($dashboard->referrals);
        $this->assertFalse($dashboard->students[0]->ispriority);
    }

    public function test_annulled_entries_do_not_count_towards_coverage(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $now = strtotime('2026-10-15');

        $this->create_assignment($student->id, $tutor->id, $year, ['timestart' => $now - DAYSECS]);
        $entryid = $this->create_entry($student->id, $tutor->id, $year);

        $entryrepository = new entry_repository();
        $record = $entryrepository->get($entryid);
        $record->status = entry_status::ANNULLED;
        $record->modifiedby = get_admin()->id;
        $record->timemodified = time();
        global $DB;
        $DB->update_record('local_tut_entry', $record);

        $dashboard = (new dashboard_service())->get_tutor_dashboard($tutor->id, $year, $now);

        $this->assertSame(0, $dashboard->summary->attendedcount);
        $this->assertSame(1, $dashboard->summary->pendinginitialcount);
        $this->assertFalse($dashboard->students[0]->covered);
    }
}
