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

use local_monlaututoria\domain\followup_status;
use local_monlaututoria\domain\priority_level;

/**
 * Tests for followup_repository (phase 6.2/6.3).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup_repository_test extends \advanced_testcase {

    /**
     * @param int $studentid
     * @param int $tutorid
     * @return int
     */
    private function create_entry(int $studentid, int $tutorid): int {
        $academicyearid = (new academic_year_repository())->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);

        return (new entry_repository())->create((object) [
            'studentid' => $studentid, 'tutorid' => $tutorid, 'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
    }

    public function test_create_and_get(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $repository = new followup_repository();
        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => strtotime('2026-11-01'),
            'priority' => priority_level::HIGH, 'createdby' => get_admin()->id,
        ]);

        $record = $repository->get($id);
        $this->assertSame($student->id, (int) $record->studentid);
        $this->assertSame(followup_status::PENDING, $record->status);
        $this->assertSame(priority_level::HIGH, $record->priority);
        $this->assertNull($record->closingentryid);
    }

    public function test_get_missing_throws_exception(): void {
        $this->resetAfterTest();

        $this->expectException(\dml_missing_record_exception::class);
        (new followup_repository())->get(999999);
    }

    public function test_search_overdue_filter(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new followup_repository();

        $overdueid = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => strtotime('-1 day'),
            'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => strtotime('+1 day'),
            'createdby' => get_admin()->id,
        ]);

        $overdue = $repository->search(['studentid' => $student->id, 'overdue' => true]);
        $this->assertCount(1, $overdue);
        $this->assertSame($overdueid, (int) array_values($overdue)[0]->id);
    }

    public function test_close_with_entry_sets_status_and_closingentryid(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $closingentryid = $this->create_entry($student->id, $tutor->id);
        $repository = new followup_repository();

        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => time(), 'createdby' => get_admin()->id,
        ]);

        $repository->close_with_entry($id, $closingentryid, get_admin()->id);

        $record = $repository->get($id);
        $this->assertSame(followup_status::COMPLETED, $record->status);
        $this->assertSame($closingentryid, (int) $record->closingentryid);
    }
}
