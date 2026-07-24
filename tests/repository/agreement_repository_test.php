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

use local_monlaututoria\domain\agreement_status;
use local_monlaututoria\domain\agreement_responsible_type;

/**
 * Tests for agreement_repository (phase 6.1/6.3).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_repository_test extends \advanced_testcase {

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

    public function test_create_and_get(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $repository = new agreement_repository();
        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'Attend weekly review',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => strtotime('2026-11-01'), 'createdby' => get_admin()->id,
        ]);

        $record = $repository->get($id);
        $this->assertSame($student->id, (int) $record->studentid);
        $this->assertSame(agreement_status::PENDING, $record->status);
        $this->assertSame('Attend weekly review', $record->description);
        $this->assertSame(0, (int) $record->visibletostudent);
    }

    public function test_get_missing_throws_exception(): void {
        $this->resetAfterTest();

        $this->expectException(\dml_missing_record_exception::class);
        (new agreement_repository())->get(999999);
    }

    public function test_find_by_entry(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new agreement_repository();

        $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'A',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => time(), 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, $repository->find_by_entry($entryid));
    }

    public function test_search_and_count_search_filter_by_status_and_student(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new agreement_repository();

        $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'A',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => time(), 'createdby' => get_admin()->id,
        ]);

        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'status' => agreement_status::PENDING]));
        $this->assertSame(0, $repository->count_search(['studentid' => $student->id, 'status' => agreement_status::COMPLETED]));
    }

    public function test_search_overdue_filter(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new agreement_repository();

        $overdueid = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'Overdue',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => strtotime('-1 day'), 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'Future',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => strtotime('+1 day'), 'createdby' => get_admin()->id,
        ]);

        $overdue = $repository->search(['studentid' => $student->id, 'overdue' => true]);
        $this->assertCount(1, $overdue);
        $this->assertSame($overdueid, (int) array_values($overdue)[0]->id);

        // A completed agreement past its due date is no longer "overdue" —
        // open_values() excludes it.
        $repository->update_status($overdueid, agreement_status::COMPLETED, get_admin()->id);
        $this->assertCount(0, $repository->search(['studentid' => $student->id, 'overdue' => true]));
    }

    public function test_update_status_changes_status_and_optionally_duedate(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new agreement_repository();

        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'A',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => strtotime('2026-11-01'), 'createdby' => get_admin()->id,
        ]);

        $repository->update_status($id, agreement_status::COMPLETED, get_admin()->id);
        $record = $repository->get($id);
        $this->assertSame(agreement_status::COMPLETED, $record->status);
        $this->assertSame(strtotime('2026-11-01'), (int) $record->duedate);

        $repository->update_status($id, agreement_status::PENDING, get_admin()->id, strtotime('2026-12-01'));
        $record = $repository->get($id);
        $this->assertSame(agreement_status::PENDING, $record->status);
        $this->assertSame(strtotime('2026-12-01'), (int) $record->duedate);
    }
}
