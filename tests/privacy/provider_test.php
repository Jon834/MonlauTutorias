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

namespace local_monlaututoria\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\bulk_operation_repository;

/**
 * Tests for the retention policy decided in phase 3E.6: local_tut_assignment
 * and local_tut_bulkoperation are now exported and anonymised (never
 * deleted) on erasure, and local_tut_bulkoperation additionally has a 90-day
 * TTL for finished operations (see cleanup_bulk_operations_task_test.php).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \advanced_testcase {

    /**
     * @return int academic year id
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

    public function test_get_contexts_for_userid_finds_assignment_involvement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($student->id)->get_contexts());
        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_get_contexts_for_userid_finds_bulk_operation_involvement(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();

        (new bulk_operation_repository())->create((object) [
            'operationuuid'  => bulk_operation_repository::generate_uuid(),
            'primarytutorid' => $tutor->id,
            'createdby'      => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_export_user_data_includes_assignments_with_role_and_counterpart(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Seguimiento inicial',
            'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::export_user_data($approved);

        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([get_string('pluginname', 'local_monlaututoria')]);
        $this->assertNotEmpty($data->assignments);
        $this->assertContains('student', $data->assignments[0]->yourrole);
        $this->assertSame(fullname($tutor), $data->assignments[0]->counterpart);
        $this->assertSame('Seguimiento inicial', $data->assignments[0]->note);
    }

    public function test_delete_data_for_user_anonymizes_without_deleting_the_row(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Contiene el nombre del alumno',
            'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $repository->get($id);

        // The row still exists — this is anonymisation, not deletion — and
        // the tutor's own side of the relationship is untouched.
        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($tutor->id, (int) $record->tutorid);
        $this->assertNull($record->note);
        // Historically relevant facts survive anonymisation.
        $this->assertSame('primary', $record->assignmenttype);
        $this->assertSame(1, (int) $record->isprimary);
    }

    public function test_delete_data_for_users_anonymizes_each_listed_user(): void {
        $this->resetAfterTest();

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id1 = $repository->create((object) [
            'studentid' => $student1->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);
        $id2 = $repository->create((object) [
            'studentid' => $student2->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_userlist($context, 'local_monlaututoria', [$student1->id, $student2->id]);
        provider::delete_data_for_users($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $this->assertSame($noreply, (int) $repository->get($id1)->studentid);
        $this->assertSame($noreply, (int) $repository->get($id2)->studentid);
        // The shared tutor is a bystander to these two erasure requests.
        $this->assertSame($tutor->id, (int) $repository->get($id1)->tutorid);
        $this->assertSame($tutor->id, (int) $repository->get($id2)->tutorid);
    }

    public function test_delete_data_for_all_users_in_context_anonymizes_everyone(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Nota', 'createdby' => get_admin()->id,
        ]);

        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $noreply = \core_user::get_noreply_user()->id;
        $record = $repository->get($id);
        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($noreply, (int) $record->tutorid);
        $this->assertNull($record->note);
    }

    public function test_get_users_in_context_lists_students_tutors_and_bulk_operation_roles(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $userlist = new userlist(\context_system::instance(), 'local_monlaututoria');
        provider::get_users_in_context($userlist);

        $userids = $userlist->get_userids();
        $this->assertContains((int) $student->id, $userids);
        $this->assertContains((int) $tutor->id, $userids);
    }
}
