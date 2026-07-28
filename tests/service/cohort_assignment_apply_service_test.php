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

use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\assignment_source;
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\cohort_assignment_action;
use local_monlaututoria\domain\cohort_assignment_command;
use local_monlaututoria\domain\cohort_sync_mode;
use local_monlaututoria\event\cohort_assignment_applied;
use local_monlaututoria\event\cohort_assignment_apply_failed;

/**
 * Tests for cohort_assignment_apply_service — the "confirm" step
 * cohort_assignment_preview_service's own docblock names as phases 3C.3-3C.5.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_service_test extends \advanced_testcase {

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

    private function create_row(int $studentid, int $tutorid, int $academicyearid, array $overrides = []): int {
        $repository = new assignment_repository();

        return $repository->create((object) array_merge([
            'studentid'      => $studentid,
            'tutorid'        => $tutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => assignment_type::PRIMARY,
            'isprimary'      => 1,
            'status'         => assignment_status::ACTIVE,
            'timestart'      => time() - DAYSECS,
            'createdby'      => get_admin()->id,
        ], $overrides));
    }

    public function test_apply_creates_primary_assignments_for_unassigned_students(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student1->id);
        cohort_add_member($cohort->id, $student2->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $result = (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);

        $this->assertSame(bulk_operation_status::COMPLETED, $result->finalstatus);
        $this->assertSame(2, $result->count(cohort_assignment_action::CREATE_PRIMARY));

        $assignmentrepository = new assignment_repository();
        $rows = $assignmentrepository->find_by_student($student1->id);
        $this->assertCount(1, $rows);
        $row = reset($rows);
        $this->assertSame($tutor->id, (int) $row->tutorid);
        $this->assertSame(assignment_source::COHORT, $row->source);
        $this->assertSame(1, (int) $row->isprimary);

        $operation = (new bulk_operation_repository())->get($preview->operationid);
        $this->assertSame(bulk_operation_status::COMPLETED, $operation->status);
    }

    public function test_apply_creates_cotutor_when_requested(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command(
                $cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY, $cotutor->id
            ),
            get_admin()->id
        );

        $result = (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);

        $this->assertSame(1, $result->count(cohort_assignment_action::CREATE_PRIMARY));
        $row = reset($result->rows);
        $this->assertSame(cohort_assignment_action::CREATE_COTUTOR, $row->cotutoroutcome);
        $this->assertNotNull($row->cotutorassignmentid);

        $cotutorrow = (new assignment_repository())->get($row->cotutorassignmentid);
        $this->assertSame($cotutor->id, (int) $cotutorrow->tutorid);
        $this->assertSame(assignment_type::CO_TUTOR, $cotutorrow->assignmenttype);
    }

    public function test_apply_reassigns_when_mode_is_replace_primary(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $oldtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();
        $oldrowid = $this->create_row($student->id, $oldtutor->id, $academicyearid);

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $newtutor->id, cohort_sync_mode::REPLACE_PRIMARY),
            get_admin()->id
        );

        $result = (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);

        $this->assertSame(1, $result->count(cohort_assignment_action::REASSIGN_PRIMARY));

        $assignmentrepository = new assignment_repository();
        $oldrow = $assignmentrepository->get($oldrowid);
        $this->assertSame(assignment_status::CLOSED, $oldrow->status);

        $current = $assignmentrepository->find_active_primary($student->id, $academicyearid);
        $this->assertSame($newtutor->id, (int) $current->tutorid);
    }

    public function test_apply_closes_missing_assignments_when_mode_is_add_and_close_missing(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $departedstudent = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        // Cohort-sourced row for a student who is NOT (any longer) a member.
        $rowid = $this->create_row($departedstudent->id, $tutor->id, $academicyearid, [
            'source' => assignment_source::COHORT,
            'cohortid' => $cohort->id,
        ]);

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command(
                $cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_AND_CLOSE_MISSING
            ),
            get_admin()->id
        );

        $result = (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);

        $this->assertSame(1, $result->count(cohort_assignment_action::CLOSE_MISSING));

        $row = (new assignment_repository())->get($rowid);
        $this->assertSame(assignment_status::CLOSED, $row->status);
    }

    public function test_apply_rejects_an_already_applied_operation(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $applyservice = new cohort_assignment_apply_service();
        $applyservice->apply($preview->operationuuid, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $applyservice->apply($preview->operationuuid, get_admin()->id);
    }

    public function test_apply_rejects_preview_only_mode(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::PREVIEW_ONLY),
            get_admin()->id
        );

        $this->expectException(\moodle_exception::class);
        (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);
    }

    public function test_apply_rejects_when_data_changed_since_preview(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $othertutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        // Something else assigns the student a primary tutor in between
        // preview and apply — the fresh classification would no longer
        // match the summary stored at preview time.
        $this->create_row($student->id, $othertutor->id, $academicyearid);

        $this->expectException(\moodle_exception::class);
        (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);
    }

    public function test_apply_triggers_applied_event(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        $sink = $this->redirectEvents();
        (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);
        $events = array_filter($sink->get_events(), static fn ($e) => $e instanceof cohort_assignment_applied);
        $sink->close();

        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertEquals($preview->operationid, $event->objectid);
        $this->assertSame(1, $event->other['createdcount']);
    }

    public function test_apply_triggers_failed_event_and_rolls_back_on_error(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $student->id);
        $academicyearid = $this->create_academic_year();

        $previewservice = new cohort_assignment_preview_service();
        $preview = $previewservice->preview(
            new cohort_assignment_command($cohort->id, $academicyearid, $tutor->id, cohort_sync_mode::ADD_ONLY),
            get_admin()->id
        );

        // Force the write to fail: create a conflicting active primary for
        // the student directly through the repository (bypassing the
        // service, same trick used elsewhere in this suite to simulate a
        // race between preview and apply) *and* make has_changed_since_preview()
        // itself unable to catch it by also deleting the operation's summary
        // reference — simplest reliable way here is to close the academic
        // year lock right before applying, which assignment_service::create()
        // re-validates on every write but classify() does not re-check inside
        // apply(), since command_from_operation() reads canoverridelock from
        // the ORIGINAL preview-time flag, not a live capability check.
        (new academic_year_repository())->set_locked_flag($academicyearid, true, get_admin()->id);

        $sink = $this->redirectEvents();
        try {
            (new cohort_assignment_apply_service())->apply($preview->operationuuid, get_admin()->id);
            $this->fail('Expected a moodle_exception to be thrown.');
        } catch (\moodle_exception $e) {
            // Expected.
        }
        $events = array_filter($sink->get_events(), static fn ($e) => $e instanceof cohort_assignment_apply_failed);
        $sink->close();

        $this->assertCount(1, $events);

        $operation = (new bulk_operation_repository())->get($preview->operationid);
        $this->assertSame(bulk_operation_status::FAILED, $operation->status);

        $assignmentrepository = new assignment_repository();
        $this->assertSame([], $assignmentrepository->find_by_student($student->id));
    }
}
