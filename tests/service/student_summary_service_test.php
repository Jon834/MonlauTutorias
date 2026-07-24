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
 * Tests for student_summary_service (phase 4.1 — "cabecera y resumen").
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class student_summary_service_test extends \advanced_testcase {

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

    public function test_summary_includes_primary_tutor_and_cotutors(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $primarytutor = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $primarytutor->id,
            'academicyearid' => $year, 'isprimary' => 1, 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $cotutor->id,
            'academicyearid' => $year, 'assignmenttype' => 'co_tutor', 'createdby' => get_admin()->id,
        ]);

        $summary = (new student_summary_service())->get_summary($student->id, $year);

        $this->assertNotNull($summary->primaryassignment);
        $this->assertSame($primarytutor->id, (int) $summary->primaryassignment->tutorid);
        $this->assertCount(1, $summary->cotutorassignments);
        $this->assertSame($cotutor->id, (int) $summary->cotutorassignments[0]->tutorid);
    }

    public function test_summary_has_no_primary_when_student_is_unassigned(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $summary = (new student_summary_service())->get_summary($student->id, $year);

        $this->assertNull($summary->primaryassignment);
        $this->assertEmpty($summary->cotutorassignments);
        $this->assertNull($summary->lastassignment);
    }

    public function test_last_assignment_is_the_one_with_the_latest_timestart(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $earliertutor = $this->getDataGenerator()->create_user();
        $latertutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $earliertutor->id,
            'academicyearid' => $year, 'assignmenttype' => 'support',
            'timestart' => strtotime('2026-09-01'), 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $latertutor->id,
            'academicyearid' => $year, 'assignmenttype' => 'orientation',
            'timestart' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);

        $summary = (new student_summary_service())->get_summary($student->id, $year);

        $this->assertNotNull($summary->lastassignment);
        $this->assertSame($latertutor->id, (int) $summary->lastassignment->tutorid);
    }

    public function test_upcoming_assignment_is_detected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $futuretutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $futuretutor->id,
            'academicyearid' => $year, 'assignmenttype' => 'support',
            'timestart' => time() + DAYSECS, 'createdby' => get_admin()->id,
        ]);

        $summary = (new student_summary_service())->get_summary($student->id, $year);

        $this->assertCount(1, $summary->upcomingassignments);
        $this->assertSame($futuretutor->id, (int) $summary->upcomingassignments[0]->tutorid);
    }

    public function test_summary_is_scoped_to_the_requested_academic_year_only(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $thisyear = $this->create_academic_year();
        $otheryear = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $otheryear, 'isprimary' => 1, 'createdby' => get_admin()->id,
        ]);

        $summary = (new student_summary_service())->get_summary($student->id, $thisyear);

        $this->assertNull($summary->primaryassignment);
        $this->assertNull($summary->lastassignment);
    }
}
