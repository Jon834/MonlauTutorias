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
 * Tests for scope_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class scope_service_test extends \advanced_testcase {

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
     * @param string $capability
     * @param int $userid
     */
    private function grant_capability_to_user(string $capability, int $userid): void {
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $userid, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
    }

    public function test_tutor_accesses_assigned_student(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);

        $assignmentrepo = new assignment_repository();
        $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);

        $scope = new scope_service($assignmentrepo);
        $this->assertTrue($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_tutor_does_not_access_unassigned_student(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);

        $scope = new scope_service();
        $this->assertFalse($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_cotutor_accesses_student(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $cotutor->id);

        $assignmentrepo = new assignment_repository();
        $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $cotutor->id,
            'academicyearid' => $academicyearid, 'assignmenttype' => 'co_tutor',
            'createdby' => get_admin()->id,
        ]);

        $scope = new scope_service($assignmentrepo);
        $this->assertTrue($scope->can_user_access_student($cotutor->id, $student->id));
    }

    public function test_previous_tutor_loses_access_after_close(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);

        $assignmentrepo = new assignment_repository();
        $id = $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);
        $assignmentrepo->close($id, get_admin()->id, time());

        $scope = new scope_service($assignmentrepo);
        $this->assertFalse($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_previous_tutor_with_historical_capability_accesses_own_history(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);
        $this->grant_capability_to_user('local/monlaututoria:viewhistoricalassignments', $tutor->id);

        $assignmentrepo = new assignment_repository();
        $id = $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);
        $assignmentrepo->close($id, get_admin()->id, time());

        $scope = new scope_service($assignmentrepo);
        $this->assertTrue($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_admin_with_viewallassignments_accesses_any_student(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $admin = $this->getDataGenerator()->create_user();

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $admin->id);

        $scope = new scope_service();
        $this->assertTrue($scope->can_user_access_student($admin->id, $student->id));
    }

    public function test_user_without_capability_denied(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_user();

        $scope = new scope_service();
        $this->assertFalse($scope->can_user_access_student($user->id, $student->id));
    }

    public function test_future_assignment_does_not_grant_access_yet(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);

        $assignmentrepo = new assignment_repository();
        $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'timestart' => time() + DAYSECS,
            'createdby' => get_admin()->id,
        ]);

        $scope = new scope_service($assignmentrepo);
        $this->assertFalse($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_expired_assignment_does_not_grant_access(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewownstudents', $tutor->id);

        $assignmentrepo = new assignment_repository();
        $assignmentrepo->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
            'timestart' => time() - (2 * DAYSECS), 'timeend' => time() - DAYSECS,
            'createdby' => get_admin()->id,
        ]);

        $scope = new scope_service($assignmentrepo);
        $this->assertFalse($scope->can_user_access_student($tutor->id, $student->id));
    }

    public function test_require_access_throws_when_denied(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_user();

        $scope = new scope_service();

        $this->expectException(\moodle_exception::class);
        $scope->require_user_can_access_student($user->id, $student->id);
    }

    public function test_student_with_viewownfile_accesses_own_record(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $this->grant_capability_to_user('local/monlaututoria:viewownfile', $student->id);

        // No tutoring relationship at all — access is granted purely because
        // the viewer IS the student, phase 4.3's whole point.
        $scope = new scope_service();
        $this->assertTrue($scope->can_user_access_student($student->id, $student->id));
    }

    public function test_student_with_viewownfile_cannot_access_another_students_record(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $otherstudent = $this->getDataGenerator()->create_user();
        $this->grant_capability_to_user('local/monlaututoria:viewownfile', $student->id);

        $scope = new scope_service();
        $this->assertFalse($scope->can_user_access_student($student->id, $otherstudent->id));
    }

    public function test_student_without_viewownfile_cannot_access_own_record(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();

        $scope = new scope_service();
        $this->assertFalse($scope->can_user_access_student($student->id, $student->id));
    }
}
