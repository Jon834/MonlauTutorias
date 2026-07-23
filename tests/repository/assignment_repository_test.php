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

    public function test_close_persists_closereason_and_note(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);

        $timeend = time();
        $repository->close($id, get_admin()->id, $timeend, 'end_of_year', 'Fin de curso');

        $record = $repository->get($id);
        $this->assertSame('closed', $record->status);
        $this->assertSame($timeend, (int) $record->timeend);
        $this->assertSame('end_of_year', $record->closereason);
        $this->assertSame('Fin de curso', $record->note);
    }

    public function test_close_without_reason_or_note_leaves_them_untouched(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
            'note' => 'Nota original',
        ]);

        $repository->close($id, get_admin()->id, time());

        $record = $repository->get($id);
        $this->assertSame('Nota original', $record->note);
        $this->assertNull($record->closereason);
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

    public function test_search_filters_by_academicyear_and_status(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $yearone = $this->create_academic_year();
        $yeartwo = $this->create_academic_year();

        $repository = new assignment_repository();
        $activeid = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yearone, 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yeartwo, 'createdby' => get_admin()->id,
        ]);
        $repository->close($activeid, get_admin()->id, time());

        $results = $repository->search(['academicyearid' => $yearone]);
        $this->assertCount(1, $results);
        $this->assertSame($activeid, (int) array_values($results)[0]->id);

        $results = $repository->search(['academicyearid' => $yearone, 'status' => 'active']);
        $this->assertCount(0, $results);

        $this->assertSame(2, $repository->count_search(['studentid' => $student->id]));
        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'status' => 'closed']));
    }

    public function test_search_pagination(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new assignment_repository();

        for ($i = 0; $i < 5; $i++) {
            $student = $this->getDataGenerator()->create_user();
            $repository->create((object) [
                'studentid' => $student->id, 'tutorid' => $tutor->id,
                'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
            ]);
        }

        $this->assertSame(5, $repository->count_search(['tutorid' => $tutor->id]));
        $this->assertCount(2, $repository->search(['tutorid' => $tutor->id], 0, 2));
        $this->assertCount(2, $repository->search(['tutorid' => $tutor->id], 2, 2));
        $this->assertCount(1, $repository->search(['tutorid' => $tutor->id], 4, 2));
    }

    public function test_search_rejects_unknown_sort_column(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'createdby' => get_admin()->id,
        ]);

        // An unrecognised sort column must fall back to 'timestart', never be
        // interpolated into the SQL as-is.
        $results = $repository->search(['studentid' => $student->id], 0, 0, 'id; DROP TABLE users; --');
        $this->assertCount(1, $results);
    }

    public function test_update_editable_fields_persists_and_ignores_protected_fields(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $otherstudent = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'timestart' => strtotime('2026-09-01'),
            'createdby' => get_admin()->id,
        ]);

        $newstart = strtotime('2026-09-15');
        $newend = strtotime('2027-06-01');
        $repository->update_editable_fields($id, (object) [
            'timestart' => $newstart,
            'timeend'   => $newend,
            'note'      => 'Updated note',
            // These must be silently ignored even though present.
            'studentid' => $otherstudent->id,
            'tutorid'   => $otherstudent->id,
            'status'    => 'closed',
        ], get_admin()->id);

        $record = $repository->get($id);
        $this->assertSame($newstart, (int) $record->timestart);
        $this->assertSame($newend, (int) $record->timeend);
        $this->assertSame('Updated note', $record->note);
        // Protected fields must be untouched.
        $this->assertSame((int) $student->id, (int) $record->studentid);
        $this->assertSame((int) $tutor->id, (int) $record->tutorid);
        $this->assertSame('active', $record->status);
    }

    public function test_get_cotutors_for_students_groups_by_student(): void {
        $this->resetAfterTest();

        $studentone = $this->getDataGenerator()->create_user();
        $studenttwo = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $studentone->id, 'tutorid' => $cotutor->id,
            'academicyearid' => $academicyearid, 'assignmenttype' => 'co_tutor',
            'createdby' => get_admin()->id,
        ]);

        $results = $repository->get_cotutors_for_students([$studentone->id, $studenttwo->id]);

        $this->assertCount(1, $results);
        $this->assertSame((int) $studentone->id, (int) array_values($results)[0]->studentid);
    }

    public function test_find_primary_rows_for_students_scopes_by_type_and_year(): void {
        $this->resetAfterTest();

        $studentone = $this->getDataGenerator()->create_user();
        $studenttwo = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $yearone = $this->create_academic_year();
        $yeartwo = $this->create_academic_year();

        $repository = new assignment_repository();
        $repository->create((object) [
            'studentid' => $studentone->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yearone, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);
        // Different type: must be excluded even for the same student/year.
        $repository->create((object) [
            'studentid' => $studentone->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yearone, 'assignmenttype' => 'co_tutor',
            'createdby' => get_admin()->id,
        ]);
        // Different academic year: must be excluded.
        $repository->create((object) [
            'studentid' => $studentone->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yeartwo, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);
        // Not in the requested student batch: must be excluded.
        $repository->create((object) [
            'studentid' => $studenttwo->id, 'tutorid' => $tutor->id,
            'academicyearid' => $yearone, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $results = $repository->find_primary_rows_for_students([$studentone->id], $yearone);

        $this->assertCount(1, $results);
        $this->assertSame((int) $studentone->id, (int) array_values($results)[0]->studentid);
        $this->assertSame($yearone, (int) array_values($results)[0]->academicyearid);
    }

    public function test_find_primary_rows_for_students_empty_input_returns_empty(): void {
        $this->resetAfterTest();

        $repository = new assignment_repository();

        $this->assertSame([], $repository->find_primary_rows_for_students([], $this->create_academic_year()));
    }
}
