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
use local_monlaututoria\domain\cohort_assignment_command;
use local_monlaututoria\domain\cohort_assignment_action;
use local_monlaututoria\domain\cohort_sync_mode;
use local_monlaututoria\domain\assignment_conflict_code;

/**
 * Tests for cohort_assignment_preview_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_preview_service_test extends \advanced_testcase {

    /**
     * @param bool $locked
     * @return int
     */
    private function create_academic_year(bool $locked = false): int {
        $repo = new academic_year_repository();
        $id = $repo->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        if ($locked) {
            $repo->set_locked_flag($id, true, get_admin()->id);
        }

        return $id;
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

    /**
     * @param array $items unassigned_student-style not used here; cohort_assignment_item objects
     * @param int $studentid
     * @return \local_monlaututoria\domain\cohort_assignment_item
     */
    private function find_item(array $items, int $studentid): \local_monlaututoria\domain\cohort_assignment_item {
        foreach ($items as $item) {
            if ($item->studentid === $studentid) {
                return $item;
            }
        }
        $this->fail("No item found for student $studentid");
    }

    public function test_empty_cohort_returns_zeroed_summary(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $this->assertSame(0, $preview->summary->totalmembers);
        $this->assertSame([], $preview->items);
        $this->assertNotEmpty($preview->operationuuid);
    }

    public function test_student_without_assignment_creates_primary(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::CREATE_PRIMARY, $item->action);
        $this->assertSame(1, $preview->summary->tocreatecount);
    }

    public function test_student_already_assigned_to_selected_tutor_is_no_change(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();
        $this->create_row($student->id, $tutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::NO_CHANGE, $item->action);
        $this->assertSame(1, $preview->summary->nochangecount);
    }

    public function test_student_with_other_tutor_add_only_skips_existing(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();
        $this->create_row($student->id, $existingtutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $newtutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::SKIP_EXISTING, $item->action);
        $this->assertSame((int) $existingtutor->id, $item->currentprimarytutorid);
    }

    public function test_student_with_other_tutor_replace_primary_reassigns(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();
        $this->create_row($student->id, $existingtutor->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $newtutor->id, cohort_sync_mode::REPLACE_PRIMARY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::REASSIGN_PRIMARY, $item->action);
        $this->assertSame(1, $preview->summary->toreassigncount);
    }

    public function test_cotutor_creation_proposed_and_no_change_when_already_present(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();

        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY, $cotutor->id),
            get_admin()->id
        );
        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::CREATE_COTUTOR, $item->cotutoraction);
        $this->assertSame(1, $preview->summary->tocreatecotutorcount);

        // Now the co-tutor relationship already exists: must become no_change.
        $this->create_row($student->id, $cotutor->id, $academicyearid, [
            'assignmenttype' => 'co_tutor', 'isprimary' => 0, 'timestart' => time() - DAYSECS,
        ]);
        $preview2 = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY, $cotutor->id),
            get_admin()->id
        );
        $item2 = $this->find_item($preview2->items, $student->id);
        $this->assertSame(cohort_assignment_action::NO_CHANGE, $item2->cotutoraction);
    }

    public function test_suspended_student_skipped_by_default_and_included_with_flag(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user(['suspended' => 1]);
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();

        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );
        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::SKIP_SUSPENDED, $item->action);

        $preview2 = $service->preview(
            new cohort_assignment_command(
                $cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY,
                null, null, null, true
            ),
            get_admin()->id
        );
        $item2 = $this->find_item($preview2->items, $student->id);
        $this->assertSame(cohort_assignment_action::CREATE_PRIMARY, $item2->action);
    }

    public function test_deleted_student_skipped(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        delete_user($student);
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::SKIP_INVALID, $item->action);
    }

    public function test_two_active_primaries_flagged_as_conflict(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();
        $this->create_row($student->id, $tutor1->id, $academicyearid, ['timestart' => time() - DAYSECS]);
        $this->create_row($student->id, $tutor2->id, $academicyearid, ['timestart' => time() - DAYSECS]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $newtutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $student->id);
        $this->assertSame(cohort_assignment_action::CONFLICT_PRIMARY, $item->action);
        $this->assertContains(assignment_conflict_code::MULTIPLE_ACTIVE_PRIMARY, $item->conflictcodes);
        $this->assertSame(1, $preview->summary->conflictcount);
    }

    public function test_locked_academic_year_rejected_without_override(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year(true);

        $service = new cohort_assignment_preview_service();

        $this->expectException(\moodle_exception::class);
        $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );
    }

    public function test_locked_academic_year_allowed_with_override(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year(true);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command(
                $cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY,
                null, null, null, false, false, true
            ),
            get_admin()->id
        );

        $this->assertNotEmpty($preview->operationuuid);
    }

    public function test_tutor_present_in_own_cohort_is_skipped_as_invalid(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        // The selected tutor mistakenly also belongs to the target cohort.
        cohort_add_member($cohort->id, $tutor->id);
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $tutor->id);
        $this->assertSame(cohort_assignment_action::SKIP_INVALID, $item->action);
    }

    public function test_add_and_close_missing_detects_students_no_longer_in_cohort(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $remainingstudent = $this->getDataGenerator()->create_user();
        $departedstudent = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $remainingstudent->id);
        // departedstudent is NOT a cohort member, but has an active assignment
        // previously created from this exact cohort/academic year.
        $academicyearid = $this->create_academic_year();
        $rowid = $this->create_row($departedstudent->id, $tutor->id, $academicyearid, [
            'timestart' => time() - DAYSECS, 'source' => 'cohort', 'cohortid' => $cohort->id,
        ]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_AND_CLOSE_MISSING),
            get_admin()->id
        );

        $item = $this->find_item($preview->items, $departedstudent->id);
        $this->assertSame(cohort_assignment_action::CLOSE_MISSING, $item->action);
        $this->assertSame($rowid, $item->currentprimaryassignmentid);
        $this->assertSame(1, $preview->summary->toclosecount);
        // Only the remaining member counts towards totalmembers.
        $this->assertSame(1, $preview->summary->totalmembers);
    }

    public function test_add_and_close_missing_detects_departed_students_even_when_cohort_is_now_empty(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $departedstudent = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        // No one is a cohort member any more, but this row was sourced from it.
        $rowid = $this->create_row($departedstudent->id, $tutor->id, $academicyearid, [
            'timestart' => time() - DAYSECS, 'source' => 'cohort', 'cohortid' => $cohort->id,
        ]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_AND_CLOSE_MISSING),
            get_admin()->id
        );

        $this->assertSame(0, $preview->summary->totalmembers);
        $this->assertSame(1, $preview->summary->toclosecount);
        $item = $this->find_item($preview->items, $departedstudent->id);
        $this->assertSame(cohort_assignment_action::CLOSE_MISSING, $item->action);
        $this->assertSame($rowid, $item->currentprimaryassignmentid);
    }

    public function test_add_and_close_missing_ignores_manually_created_assignments(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $departedstudent = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        // Manual source, not cohort: must never be proposed for automatic closing.
        $this->create_row($departedstudent->id, $tutor->id, $academicyearid, [
            'timestart' => time() - DAYSECS, 'source' => 'manual', 'cohortid' => $cohort->id,
        ]);

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_AND_CLOSE_MISSING),
            get_admin()->id
        );

        foreach ($preview->items as $item) {
            $this->assertNotSame(cohort_assignment_action::CLOSE_MISSING, $item->action);
        }
        $this->assertSame(0, $preview->summary->toclosecount);
    }

    public function test_invalid_mode_rejected(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();

        $this->expectException(\moodle_exception::class);
        $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, 'not_a_real_mode'),
            get_admin()->id
        );
    }

    public function test_same_primary_and_cotutor_rejected(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();

        $this->expectException(\moodle_exception::class);
        $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY, $tutor->id),
            get_admin()->id
        );
    }

    public function test_is_expired(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $this->assertFalse($service->is_expired($preview->operationuuid, 1800));
        $this->assertTrue($service->is_expired($preview->operationuuid, -1));
    }

    public function test_has_changed_since_preview_detects_new_member(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new cohort_assignment_preview_service();
        $preview = $service->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $this->assertFalse($service->has_changed_since_preview($preview->operationuuid));

        $newstudent = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $newstudent->id);

        $this->assertTrue($service->has_changed_since_preview($preview->operationuuid));
    }
}
