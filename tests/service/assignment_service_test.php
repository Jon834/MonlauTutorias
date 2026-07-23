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
use local_monlaututoria\domain\assignment_close_reason;
use local_monlaututoria\domain\assignment_reassign_reason;
use local_monlaututoria\domain\reassign_assignment_command;

/**
 * Test double whose create() always throws, used to verify that reassign()
 * rolls back the close() of the old assignment when creating the new one
 * fails inside the same transaction.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class failing_create_assignment_repository extends assignment_repository {
    public function create(\stdClass $data): int {
        throw new \moodle_exception('error_assignment_invalid_tutor', 'local_monlaututoria');
    }
}

/**
 * Test double that always returns a fixed, stale snapshot from
 * find_active_primary(), regardless of what has since happened to that row in
 * the database. get() is inherited unmodified, so the concurrency recheck
 * inside reassign_primary_tutor() (which calls get(), not find_active_primary())
 * still sees the real, current state — letting tests simulate a row that
 * changed between the initial validation read and the write.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stale_primary_assignment_repository extends assignment_repository {
    public function __construct(private \stdClass $stalesnapshot) {
    }

    public function find_active_primary(int $studentid, int $academicyearid): ?\stdClass {
        return $this->stalesnapshot;
    }
}

/**
 * Tests for assignment_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_service_test extends \advanced_testcase {

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

    public function test_create_valid_assignment(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid'      => $student->id,
            'tutorid'        => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $this->assertIsInt($id);
    }

    public function test_student_cannot_be_own_tutor(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $student->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
    }

    public function test_nonexistent_student_rejected(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => 999999, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
    }

    public function test_deleted_student_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        delete_user($student);

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
    }

    public function test_suspended_user_blocked_by_default_and_allowed_with_override(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user(['suspended' => 1]);
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        try {
            $service->create((object) [
                'studentid' => $student->id, 'tutorid' => $tutor->id,
                'academicyearid' => $academicyearid,
            ], get_admin()->id);
            $this->fail('Expected moodle_exception for suspended student without override');
        } catch (\moodle_exception $e) {
            // Expected.
        }

        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id, true);

        $this->assertIsInt($id);
    }

    public function test_locked_academic_year_blocked_by_default(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year(true);

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
    }

    public function test_locked_academic_year_allowed_with_override(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year(true);

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id, false, true);

        $this->assertIsInt($id);
    }

    public function test_duplicate_active_primary_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor2->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);
    }

    public function test_dates_invalid_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
            'timestart' => time(), 'timeend' => time() - DAYSECS,
        ], get_admin()->id);
    }

    public function test_close_and_reject_double_close(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);

        $this->expectException(\moodle_exception::class);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);
    }

    public function test_close_rejects_invalid_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->close($id, get_admin()->id, 'not_a_real_reason');
    }

    public function test_close_rejects_date_before_start(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'timestart' => strtotime('2026-09-01'),
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER, strtotime('2026-08-01'));
    }

    public function test_close_persists_reason_and_note(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $timeend = time();
        $service->close($id, get_admin()->id, assignment_close_reason::STUDENT_LEFT, $timeend, 'Se ha dado de baja');

        $repository = new assignment_repository();
        $record = $repository->get($id);
        $this->assertSame('closed', $record->status);
        $this->assertSame($timeend, (int) $record->timeend);
        $this->assertSame(assignment_close_reason::STUDENT_LEFT, $record->closereason);
        $this->assertSame('Se ha dado de baja', $record->note);
    }

    public function test_reassign_success(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $oldid = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $result = $service->reassign_primary_tutor(
            new reassign_assignment_command(
                $student->id,
                $tutor2->id,
                $academicyearid,
                assignment_reassign_reason::TUTOR_LEFT
            ),
            get_admin()->id
        );

        $this->assertNotEquals($oldid, $result->newassignmentid);
        $this->assertSame($oldid, $result->previousassignmentid);
        $this->assertSame((int) $tutor1->id, $result->previoustutorid);
        $this->assertSame((int) $tutor2->id, $result->newtutorid);
        $this->assertSame([], $result->closedcotutorids);

        $repository = new assignment_repository();
        $this->assertSame('closed', $repository->get($oldid)->status);
        $this->assertSame((int) $tutor2->id, (int) $repository->get($result->newassignmentid)->tutorid);
    }

    public function test_reassign_rejects_invalid_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->reassign_primary_tutor(
            new reassign_assignment_command($student->id, $tutor2->id, $academicyearid, 'not_a_real_reason'),
            get_admin()->id
        );
    }

    public function test_reassign_without_active_primary_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->reassign_primary_tutor(
            new reassign_assignment_command($student->id, $tutor->id, $academicyearid, assignment_reassign_reason::OTHER),
            get_admin()->id
        );
    }

    public function test_reassign_same_tutor_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->reassign_primary_tutor(
            new reassign_assignment_command($student->id, $tutor->id, $academicyearid, assignment_reassign_reason::OTHER),
            get_admin()->id
        );
    }

    public function test_reassign_validates_new_tutor_before_touching_old_assignment(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $oldid = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        try {
            $service->reassign_primary_tutor(
                new reassign_assignment_command($student->id, 999999, $academicyearid, assignment_reassign_reason::OTHER),
                get_admin()->id
            );
            $this->fail('Expected moodle_exception for invalid new tutor');
        } catch (\moodle_exception $e) {
            // Expected.
        }

        $repository = new assignment_repository();
        $this->assertSame('active', $repository->get($oldid)->status);
    }

    public function test_reassign_rolls_back_close_when_create_fails_inside_transaction(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $realrepository = new assignment_repository();
        $oldid = $realrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $service = new assignment_service(new failing_create_assignment_repository());

        try {
            $service->reassign_primary_tutor(
                new reassign_assignment_command($student->id, $tutor2->id, $academicyearid, assignment_reassign_reason::OTHER),
                get_admin()->id
            );
            $this->fail('Expected an exception from the failing repository');
        } catch (\Throwable $e) {
            // Expected: the injected repository's create() always throws.
        }

        // The close() that ran before create() failed must have been rolled back.
        $this->assertSame('active', $realrepository->get($oldid)->status);
    }

    public function test_reassign_detects_concurrent_change_and_rolls_back(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $realrepository = new assignment_repository();
        $oldid = $realrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);
        // Snapshot taken while the row was still active, as find_active_primary()
        // would have returned it, simulating a race where the row changes
        // between that read and the write further down the operation.
        $stalesnapshot = $realrepository->get($oldid);

        // Simulate a concurrent admin already closing this same primary assignment.
        $realrepository->close($oldid, get_admin()->id, time());

        $service = new assignment_service(new stale_primary_assignment_repository($stalesnapshot));

        try {
            $service->reassign_primary_tutor(
                new reassign_assignment_command($student->id, $tutor2->id, $academicyearid, assignment_reassign_reason::OTHER),
                get_admin()->id
            );
            $this->fail('Expected a conflict exception when the row changed concurrently');
        } catch (\moodle_exception $e) {
            $this->assertStringContainsString('reassign_conflict', $e->errorcode);
        }

        $this->assertSame('closed', $realrepository->get($oldid)->status);
        $this->assertNull($realrepository->find_active_primary($student->id, $academicyearid));
    }

    public function test_reassign_keeps_cotutors_by_default(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);
        $cotutorid = $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);

        $result = $service->reassign_primary_tutor(
            new reassign_assignment_command($student->id, $tutor2->id, $academicyearid, assignment_reassign_reason::OTHER),
            get_admin()->id
        );

        $this->assertSame([$cotutorid], $result->keptcotutorids);
        $this->assertSame([], $result->closedcotutorids);

        $repository = new assignment_repository();
        $this->assertSame('active', $repository->get($cotutorid)->status);
    }

    public function test_reassign_can_close_cotutors(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);
        $cotutorid = $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);

        $result = $service->reassign_primary_tutor(
            new reassign_assignment_command(
                $student->id,
                $tutor2->id,
                $academicyearid,
                assignment_reassign_reason::OTHER,
                null,
                false
            ),
            get_admin()->id
        );

        $this->assertSame([], $result->keptcotutorids);
        $this->assertSame([$cotutorid], $result->closedcotutorids);

        $repository = new assignment_repository();
        $this->assertSame('closed', $repository->get($cotutorid)->status);
    }

    public function test_add_and_remove_cotutor(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $primarytutor = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $primarytutor->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $cotutorid = $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);
        $this->assertIsInt($cotutorid);

        $service->remove_cotutor($cotutorid, get_admin()->id);

        $repository = new assignment_repository();
        $this->assertSame('closed', $repository->get($cotutorid)->status);
    }

    public function test_duplicate_cotutor_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);
    }

    public function test_update_success(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $newend = strtotime('2027-06-30');
        $service->update($id, (object) [
            'note'    => 'Seguimiento trimestral',
            'timeend' => $newend,
        ], get_admin()->id);

        $repository = new assignment_repository();
        $record = $repository->get($id);
        $this->assertSame('Seguimiento trimestral', $record->note);
        $this->assertSame($newend, (int) $record->timeend);
    }

    public function test_update_closed_assignment_rejected_without_permission(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);

        $this->expectException(\moodle_exception::class);
        $service->update($id, (object) ['note' => 'x'], get_admin()->id, false);
    }

    public function test_update_closed_assignment_allowed_with_permission(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);

        $service->update($id, (object) ['note' => 'Corrección histórica'], get_admin()->id, true);

        $repository = new assignment_repository();
        $this->assertSame('Corrección histórica', $repository->get($id)->note);
    }

    public function test_update_invalid_dates_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'timestart' => strtotime('2026-09-01'),
        ], get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->update($id, (object) ['timeend' => strtotime('2026-08-01')], get_admin()->id);
    }

    public function test_update_locked_academic_year_blocked_and_overridable(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $academicyearrepository = new academic_year_repository();
        $academicyearrepository->set_locked_flag($academicyearid, true, get_admin()->id);

        try {
            $service->update($id, (object) ['note' => 'x'], get_admin()->id, false, false);
            $this->fail('Expected moodle_exception for locked academic year without override');
        } catch (\moodle_exception $e) {
            // Expected.
        }

        $service->update($id, (object) ['note' => 'y'], get_admin()->id, false, true);

        $repository = new assignment_repository();
        $this->assertSame('y', $repository->get($id)->note);
    }

    public function test_update_closed_assignment_requires_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);

        try {
            $service->update($id, (object) ['note' => 'x'], get_admin()->id, true, false, '');
            $this->fail('Expected moodle_exception for missing reason on closed assignment edit');
        } catch (\moodle_exception $e) {
            // Expected: manageclosedassignments alone is not enough, a reason is required too.
        }

        $service->update($id, (object) ['note' => 'x'], get_admin()->id, true, false, 'Corrección de fecha detectada en auditoría');

        $repository = new assignment_repository();
        $this->assertSame('x', $repository->get($id)->note);
    }
}
