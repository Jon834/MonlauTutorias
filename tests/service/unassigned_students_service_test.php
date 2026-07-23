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

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\domain\unassigned_status_code;
use local_monlaututoria\domain\assignment_conflict_code;

/**
 * Tests for unassigned_students_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class unassigned_students_service_test extends \advanced_testcase {

    /**
     * @return int
     */
    private function create_academic_year(): int {
        $repo = new academic_year_repository();

        return $repo->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
    }

    /**
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @param array $overrides
     * @return int
     */
    private function create_row(int $studentid, int $tutorid, int $academicyearid, array $overrides = []): int {
        $repository = new assignment_repository();

        return $repository->create((object) array_merge([
            'studentid'      => $studentid,
            'tutorid'        => $tutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => 'primary',
            'isprimary'      => 1,
            'status'         => 'active',
            'createdby'      => get_admin()->id,
        ], $overrides));
    }

    public function test_student_with_no_assignment_is_never_assigned(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame($student->id, $results[0]->studentid);
        $this->assertSame(unassigned_status_code::NEVER_ASSIGNED, $results[0]->statuscode);
        $this->assertSame([], $results[0]->conflictcodes);
    }

    public function test_student_with_active_primary_is_excluded(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $this->create_row($student->id, $tutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new unassigned_students_service();

        $this->assertSame([], $service->search([$cohort->id], $academicyearid));
        $this->assertSame(0, $service->count([$cohort->id], $academicyearid));
    }

    public function test_only_cotutor_does_not_count_as_primary(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $this->create_row($student->id, $cotutor->id, $academicyearid, [
            'assignmenttype' => 'co_tutor', 'isprimary' => 0,
        ]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::NEVER_ASSIGNED, $results[0]->statuscode);
    }

    public function test_closed_assignment_is_previous_closed(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $rowid = $this->create_row($student->id, $tutor->id, $academicyearid, [
            'timestart' => time() - (2 * DAYSECS),
            'status'    => 'closed',
            'timeend'   => time() - DAYSECS,
        ]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::PREVIOUS_CLOSED, $results[0]->statuscode);
        $this->assertSame($rowid, $results[0]->lastprimaryassignmentid);
        $this->assertSame((int) $tutor->id, $results[0]->lastprimarytutorid);
    }

    public function test_future_assignment_is_future_pending(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $rowid = $this->create_row($student->id, $tutor->id, $academicyearid, [
            'timestart' => time() + (7 * DAYSECS),
        ]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::FUTURE_PENDING, $results[0]->statuscode);
        $this->assertSame($rowid, $results[0]->futureprimaryassignmentid);
        $this->assertSame((int) $tutor->id, $results[0]->futureprimarytutorid);
    }

    public function test_expired_active_assignment_is_flagged(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        // Still status=active, but its window already elapsed: should have been closed.
        $this->create_row($student->id, $tutor->id, $academicyearid, [
            'timestart' => time() - (10 * DAYSECS),
            'timeend'   => time() - DAYSECS,
        ]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::EXPIRED_ACTIVE, $results[0]->statuscode);
    }

    public function test_tutor_from_another_academic_year_does_not_count(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $yearone = $this->create_academic_year();
        $yeartwo = $this->create_academic_year();

        $this->create_row($student->id, $tutor->id, $yeartwo, ['timestart' => time() - DAYSECS]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $yearone);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::NEVER_ASSIGNED, $results[0]->statuscode);
    }

    public function test_suspended_student_flag_is_reported(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user(['suspended' => 1]);
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->suspended);
    }

    public function test_student_in_multiple_selected_cohorts_appears_once_with_both(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort1->id, $student->id);
        cohort_add_member($cohort2->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new unassigned_students_service();
        $results = $service->search([$cohort1->id, $cohort2->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertEqualsCanonicalizing([$cohort1->id, $cohort2->id], $results[0]->cohortids);
    }

    public function test_historical_reference_date_reclassifies_correctly(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        // Still status=active, window [60 days ago, 30 days ago) — vigente back
        // then, expired by today's reference date.
        $windowstart = time() - (60 * DAYSECS);
        $windowend = time() - (30 * DAYSECS);
        $this->create_row($student->id, $tutor->id, $academicyearid, [
            'timestart' => $windowstart, 'timeend' => $windowend,
        ]);

        $service = new unassigned_students_service();

        // Today: window already elapsed, no longer covered.
        $results = $service->search([$cohort->id], $academicyearid);
        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::EXPIRED_ACTIVE, $results[0]->statuscode);

        // Back when it was still vigente: covered, so not "unassigned" at that reference date.
        $this->assertSame([], $service->search([$cohort->id], $academicyearid, $windowstart + DAYSECS));
    }

    public function test_two_active_primaries_flagged_as_conflict(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        // Bypasses assignment_service's single-active-primary rule on purpose,
        // to simulate a pre-existing data-quality issue the report must surface.
        $this->create_row($student->id, $tutor1->id, $academicyearid, ['timestart' => time() - DAYSECS]);
        $this->create_row($student->id, $tutor2->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new unassigned_students_service();
        $summary = $service->get_coverage_summary([$cohort->id], $academicyearid);

        $this->assertSame(1, $summary->conflictcount);
        $this->assertSame(1, $summary->withprimarycount);
    }

    public function test_overlapping_future_assignments_flagged_as_conflict(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $this->create_row($student->id, $tutor1->id, $academicyearid, ['timestart' => time() + (5 * DAYSECS)]);
        $this->create_row($student->id, $tutor2->id, $academicyearid, ['timestart' => time() + (10 * DAYSECS)]);

        $service = new unassigned_students_service();
        $results = $service->search([$cohort->id], $academicyearid);

        $this->assertCount(1, $results);
        $this->assertSame(unassigned_status_code::DATA_CONFLICT, $results[0]->statuscode);
        $this->assertContains(assignment_conflict_code::OVERLAPPING_FUTURE, $results[0]->conflictcodes);
    }

    public function test_deleted_tutor_on_active_row_flagged_as_conflict(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $this->create_row($student->id, $tutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);
        delete_user($tutor);

        $service = new unassigned_students_service();
        $summary = $service->get_coverage_summary([$cohort->id], $academicyearid);

        $this->assertSame(1, $summary->conflictcount);
    }

    public function test_coverage_summary_counts(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $covered = $this->getDataGenerator()->create_user();
        $uncovered = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $covered->id);
        cohort_add_member($cohort->id, $uncovered->id);
        $academicyearid = $this->create_academic_year();

        $this->create_row($covered->id, $tutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new unassigned_students_service();
        $summary = $service->get_coverage_summary([$cohort->id], $academicyearid);

        $this->assertSame(2, $summary->analyzedcount);
        $this->assertSame(1, $summary->withprimarycount);
        $this->assertSame(1, $summary->withoutprimarycount);
        $this->assertSame(50.0, $summary->coveragepercent);
    }

    public function test_search_supports_pagination(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        for ($i = 0; $i < 5; $i++) {
            $student = $this->getDataGenerator()->create_user();
            cohort_add_member($cohort->id, $student->id);
        }
        $academicyearid = $this->create_academic_year();

        $service = new unassigned_students_service();

        $this->assertCount(5, $service->search([$cohort->id], $academicyearid));
        $this->assertCount(2, $service->search([$cohort->id], $academicyearid, null, 0, 2));
        $this->assertCount(3, $service->search([$cohort->id], $academicyearid, null, 2, 10));
        $this->assertSame(5, $service->count([$cohort->id], $academicyearid));
    }

    public function test_empty_cohorts_returns_empty(): void {
        $this->resetAfterTest();

        $service = new unassigned_students_service();

        $this->assertSame([], $service->search([], $this->create_academic_year()));
    }

    public function test_invalid_academic_year_rejected(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $service = new unassigned_students_service();

        $this->expectException(\moodle_exception::class);
        $service->search([$cohort->id], 999999);
    }
}
