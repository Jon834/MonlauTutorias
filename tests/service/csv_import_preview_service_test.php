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
use local_monlaututoria\domain\csv_import_row_status;
use local_monlaututoria\domain\csv_import_message_code;

/**
 * Tests for csv_import_preview_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_preview_service_test extends \advanced_testcase {

    /**
     * @param bool $locked
     * @return \stdClass created academic year record
     */
    private function create_academic_year(bool $locked = false): \stdClass {
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

        return $repo->get($id);
    }

    /**
     * @param string $student
     * @param string $tutor
     * @param string $academicyear
     * @param array $extracolumns
     * @return string
     */
    private function build_csv(string $student, string $tutor, string $academicyear, array $extracolumns = []): string {
        $headers = array_merge(['student', 'tutor', 'academicyear'], array_keys($extracolumns));
        $values = array_merge([$student, $tutor, $academicyear], array_values($extracolumns));

        return implode(',', $headers) . "\n" . implode(',', $values) . "\n";
    }

    public function test_valid_row_by_email(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertCount(1, $preview->rows);
        $row = $preview->rows[0];
        $this->assertSame(csv_import_row_status::VALID, $row->status);
        $this->assertSame((int) $student->id, $row->studentid);
        $this->assertSame((int) $tutor->id, $row->tutorid);
        $this->assertSame((int) $year->id, $row->academicyearid);
        $this->assertSame(1, $preview->summary->validcount);
        $this->assertNotEmpty($preview->operationuuid);
    }

    public function test_valid_row_by_username_and_idnumber(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user(['idnumber' => 'STU-001']);
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv('STU-001', $tutor->username, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::VALID, $preview->rows[0]->status);
        $this->assertSame((int) $student->id, $preview->rows[0]->studentid);
        $this->assertSame((int) $tutor->id, $preview->rows[0]->tutorid);
    }

    public function test_student_not_found(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv('nobody@example.com', $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::ERROR, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::STUDENT_NOT_FOUND, $preview->rows[0]->messagecodes);
        $this->assertSame(1, $preview->summary->errorcount);
    }

    public function test_tutor_not_found(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv($student->email, 'nobody@example.com', $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertContains(csv_import_message_code::TUTOR_NOT_FOUND, $preview->rows[0]->messagecodes);
    }

    public function test_academicyear_not_found(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();

        $content = $this->build_csv($student->email, $tutor->email, 'no-such-year');

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertContains(csv_import_message_code::ACADEMICYEAR_NOT_FOUND, $preview->rows[0]->messagecodes);
    }

    public function test_academicyear_locked(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year(true);

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::ERROR, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::ACADEMICYEAR_LOCKED, $preview->rows[0]->messagecodes);
    }

    public function test_student_and_tutor_suspended(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user(['suspended' => 1]);
        $tutor = $this->getDataGenerator()->create_user(['suspended' => 1]);
        $year = $this->create_academic_year();

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::ERROR, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::STUDENT_SUSPENDED, $preview->rows[0]->messagecodes);
        $this->assertContains(csv_import_message_code::TUTOR_SUSPENDED, $preview->rows[0]->messagecodes);
    }

    public function test_student_cannot_be_own_tutor(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv($user->email, $user->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertContains(csv_import_message_code::STUDENT_SELF_TUTOR, $preview->rows[0]->messagecodes);
    }

    public function test_cohort_not_found_is_a_warning_not_an_error(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname, ['cohort' => '999999']);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::WARNING, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::COHORT_NOT_FOUND, $preview->rows[0]->messagecodes);
        $this->assertNull($preview->rows[0]->cohortid);
        $this->assertSame(1, $preview->summary->warningcount);
    }

    public function test_cohort_resolved_by_id(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $cohort = $this->getDataGenerator()->create_cohort();

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname, ['cohort' => (string) $cohort->id]);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::VALID, $preview->rows[0]->status);
        $this->assertSame((int) $cohort->id, $preview->rows[0]->cohortid);
    }

    public function test_duplicate_active_assignment_is_a_conflict(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $assignmentrepository = new assignment_repository();
        $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::CONFLICT, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::DUPLICATE_ACTIVE, $preview->rows[0]->messagecodes);
        $this->assertSame(1, $preview->summary->conflictcount);
    }

    public function test_existing_primary_tutor_is_a_conflict_for_a_new_primary_row(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $assignmentrepository = new assignment_repository();
        $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $existingtutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = $this->build_csv($student->email, $newtutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::CONFLICT, $preview->rows[0]->status);
        $this->assertContains(csv_import_message_code::PRIMARY_CONFLICT, $preview->rows[0]->messagecodes);
    }

    public function test_row_level_parser_error_becomes_error_status(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\n,tutor1,2026-2027\n";

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertSame(csv_import_row_status::ERROR, $preview->rows[0]->status);
        $this->assertNull($preview->rows[0]->studentid);
    }

    public function test_excluded_row(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id, [2]);

        $this->assertSame(csv_import_row_status::EXCLUDED, $preview->rows[0]->status);
        $this->assertNull($preview->rows[0]->studentid);
        $this->assertSame(1, $preview->summary->excludedcount);
    }

    public function test_unusable_file_throws(): void {
        $this->resetAfterTest();

        $service = new csv_import_preview_service();

        $this->expectException(\moodle_exception::class);
        $service->preview('', ',', 'UTF-8', get_admin()->id);
    }

    public function test_is_expired(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $service = new csv_import_preview_service();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);

        $this->assertFalse($service->is_expired($preview->operationuuid, 1800));
        $this->assertTrue($service->is_expired($preview->operationuuid, -1));
    }
}
