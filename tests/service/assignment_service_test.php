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

        $service->close($id, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->close($id, get_admin()->id);
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

        $newid = $service->reassign($student->id, $tutor2->id, $academicyearid, get_admin()->id);

        $this->assertNotEquals($oldid, $newid);

        $repository = new assignment_repository();
        $this->assertSame('closed', $repository->get($oldid)->status);
        $this->assertSame((int) $tutor2->id, (int) $repository->get($newid)->tutorid);
    }

    public function test_reassign_without_active_primary_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $this->expectException(\moodle_exception::class);
        $service->reassign($student->id, $tutor->id, $academicyearid, get_admin()->id);
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
        $service->reassign($student->id, $tutor->id, $academicyearid, get_admin()->id);
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
            $service->reassign($student->id, 999999, $academicyearid, get_admin()->id);
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
            $service->reassign($student->id, $tutor2->id, $academicyearid, get_admin()->id);
            $this->fail('Expected an exception from the failing repository');
        } catch (\Throwable $e) {
            // Expected: the injected repository's create() always throws.
        }

        // The close() that ran before create() failed must have been rolled back.
        $this->assertSame('active', $realrepository->get($oldid)->status);
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
}
