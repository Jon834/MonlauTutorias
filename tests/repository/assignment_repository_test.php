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

namespace local_monlaututoria\repository;

/**
 * Tests for assignment_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_repository_test extends \advanced_testcase {

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

    public function test_create_and_get(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid'      => $student->id,
            'tutorid'        => $tutor->id,
            'academicyearid' => $academicyearid,
            'createdby'      => get_admin()->id,
        ]);

        $record = $repository->get($id);
        $this->assertSame((int) $student->id, (int) $record->studentid);
        $this->assertSame('primary', $record->assignmenttype);
        $this->assertSame('active', $record->status);
    }

    public function test_find_by_student_and_tutor(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, $repository->find_by_student($student->id));
        $this->assertCount(1, $repository->find_by_tutor($tutor->id));
    }

    public function test_find_active_vs_current_window(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'timestart' => time() + DAYSECS,
            'createdby' => get_admin()->id,
        ]);

        // Active regardless of the time window.
        $this->assertCount(1, $repository->find_active($student->id));
        // Not "current" (vigente) yet: timestart is in the future.
        $this->assertCount(0, $repository->find_current($student->id));
    }

    public function test_find_historical(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);
        $repository->close($id, get_admin()->id, time());

        $this->assertCount(1, $repository->find_historical($student->id));
    }

    public function test_find_by_cohort(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $cohort = $this->getDataGenerator()->create_cohort();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'cohortid' => $cohort->id,
            'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, $repository->find_by_cohort($cohort->id));
    }

    public function test_has_active_duplicate(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);

        $this->assertTrue($repository->has_active_duplicate($student->id, $tutor->id, $academicyearid, 'primary'));
        $this->assertFalse($repository->has_active_duplicate($student->id, $tutor->id, $academicyearid, 'co_tutor'));
    }

    public function test_count_and_find_active_primary(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
            'createdby' => get_admin()->id,
        ]);

        $this->assertSame(1, $repository->count_active_primary($student->id, $academicyearid));
        $this->assertSame(0, $repository->count_active_primary($student->id, $academicyearid, $id));

        $primary = $repository->find_active_primary($student->id, $academicyearid);
        $this->assertNotNull($primary);
        $this->assertSame($id, (int) $primary->id);
    }

    public function test_is_current_tutor_ignores_non_tutor_types(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'assignmenttype' => 'support',
            'createdby' => get_admin()->id,
        ]);

        $this->assertFalse($repository->is_current_tutor_of_student($tutor->id, $student->id, $academicyearid));
    }

    public function test_has_historical_relationship(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);
        $repository->close($id, get_admin()->id, time());

        $this->assertTrue($repository->has_historical_relationship($tutor->id, $student->id));
        $this->assertFalse($repository->is_current_tutor_of_student($tutor->id, $student->id));
    }
}
