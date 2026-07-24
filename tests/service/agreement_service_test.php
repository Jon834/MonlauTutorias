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
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\domain\agreement_create_command;
use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\domain\agreement_status;

/**
 * Tests for agreement_service: create() validation, get_for_viewer()/
 * list_for_student()'s visibletostudent rule, and the quick actions
 * (complete/reopen/postpone/cancel) from phase 6.3.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_service_test extends \advanced_testcase {

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
     * @param int $studentid
     * @param int $tutorid
     * @return int
     */
    private function create_entry(int $studentid, int $tutorid): int {
        return (new entry_repository())->create((object) [
            'studentid' => $studentid, 'tutorid' => $tutorid, 'academicyearid' => $this->create_academic_year(),
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
    }

    public function test_create_valid_agreement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new agreement_service();
        $id = $service->create(
            new agreement_create_command($entryid, 'Read every day', agreement_responsible_type::STUDENT, $student->id, null, strtotime('2026-11-01')),
            get_admin()->id
        );

        $this->assertIsInt($id);
    }

    public function test_create_rejects_responsible_with_both_or_neither_identity(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $this->expectException(\moodle_exception::class);
        $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::OTHER, null, null, time()),
            get_admin()->id
        );
    }

    public function test_create_rejects_invalid_responsible_user(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $this->expectException(\moodle_exception::class);
        $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, 999999, null, time()),
            get_admin()->id
        );
    }

    public function test_get_for_viewer_hides_agreement_from_student_when_not_visible(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time(), false),
            get_admin()->id
        );

        $this->expectException(\moodle_exception::class);
        $service->get_for_viewer($id, $student->id);
    }

    public function test_get_for_viewer_shows_agreement_to_student_when_visible(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time(), true),
            get_admin()->id
        );

        $agreement = $service->get_for_viewer($id, $student->id);
        $this->assertSame($id, $agreement->id);
    }

    public function test_list_for_student_filters_out_non_visible_rows_for_the_student_viewer(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $academicyear = (new entry_repository())->get($entryid)->academicyearid;
        $service = new agreement_service();

        $service->create(
            new agreement_create_command($entryid, 'Visible', agreement_responsible_type::TUTOR, $tutor->id, null, time(), true),
            get_admin()->id
        );
        $service->create(
            new agreement_create_command($entryid, 'Hidden', agreement_responsible_type::TUTOR, $tutor->id, null, time(), false),
            get_admin()->id
        );

        $staffview = $service->list_for_student($student->id, (int) $academicyear, [], get_admin()->id);
        $this->assertCount(2, $staffview);

        $studentview = $service->list_for_student($student->id, (int) $academicyear, [], $student->id);
        $this->assertCount(1, $studentview);
        $this->assertSame('Visible', $studentview[0]->description);
    }

    public function test_complete_reopen_postpone_cancel_transitions(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, strtotime('2026-11-01')),
            get_admin()->id
        );

        $service->complete($id, get_admin()->id);
        $this->assertSame(agreement_status::COMPLETED, $service->get_for_viewer($id, get_admin()->id)->status);

        $service->reopen($id, get_admin()->id);
        $this->assertSame(agreement_status::PENDING, $service->get_for_viewer($id, get_admin()->id)->status);

        $service->postpone($id, strtotime('2026-12-01'), get_admin()->id);
        $agreement = $service->get_for_viewer($id, get_admin()->id);
        $this->assertSame(agreement_status::PENDING, $agreement->status);
        $this->assertSame(strtotime('2026-12-01'), $agreement->duedate);

        $service->cancel($id, get_admin()->id);
        $this->assertSame(agreement_status::CANCELLED, $service->get_for_viewer($id, get_admin()->id)->status);
    }

    public function test_complete_rejects_already_completed_agreement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time()),
            get_admin()->id
        );
        $service->complete($id, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->complete($id, get_admin()->id);
    }

    public function test_postpone_rejects_cancelled_agreement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new agreement_service();

        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time()),
            get_admin()->id
        );
        $service->cancel($id, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->postpone($id, strtotime('+1 week'), get_admin()->id);
    }
}
