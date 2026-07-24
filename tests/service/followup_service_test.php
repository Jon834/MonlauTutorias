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

use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\domain\followup_status;
use local_monlaututoria\domain\priority_level;

/**
 * Tests for followup_service: create() validation, and the quick actions
 * (complete/reopen/postpone/cancel/close_with_entry) from phase 6.3.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup_service_test extends \advanced_testcase {

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

    public function test_create_valid_followup(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new followup_service();
        $id = $service->create($entryid, strtotime('2026-11-01'), priority_level::LOW, get_admin()->id);

        $this->assertIsInt($id);
        $this->assertSame(priority_level::LOW, $service->get_for_viewer($id, get_admin()->id)->priority);
    }

    public function test_create_rejects_invalid_priority(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new followup_service();

        $this->expectException(\moodle_exception::class);
        $service->create($entryid, time(), 'urgentissimo', get_admin()->id);
    }

    public function test_complete_manually_reopen_postpone_cancel(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new followup_service();

        $id = $service->create($entryid, strtotime('2026-11-01'), priority_level::MEDIUM, get_admin()->id);

        $service->complete_manually($id, get_admin()->id);
        $this->assertSame(followup_status::COMPLETED, $service->get_for_viewer($id, get_admin()->id)->status);

        $service->reopen($id, get_admin()->id);
        $this->assertSame(followup_status::PENDING, $service->get_for_viewer($id, get_admin()->id)->status);

        $service->postpone($id, strtotime('2026-12-01'), get_admin()->id);
        $followup = $service->get_for_viewer($id, get_admin()->id);
        $this->assertSame(followup_status::PENDING, $followup->status);
        $this->assertSame(strtotime('2026-12-01'), $followup->duedate);

        $service->cancel($id, get_admin()->id);
        $this->assertSame(followup_status::CANCELLED, $service->get_for_viewer($id, get_admin()->id)->status);
    }

    public function test_close_with_entry_completes_and_records_closing_entry(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $closingentryid = $this->create_entry($student->id, $tutor->id);
        $service = new followup_service();

        $id = $service->create($entryid, time(), priority_level::MEDIUM, get_admin()->id);
        $service->close_with_entry($id, $closingentryid, get_admin()->id);

        $followup = $service->get_for_viewer($id, get_admin()->id);
        $this->assertSame(followup_status::COMPLETED, $followup->status);
        $this->assertSame($closingentryid, $followup->closingentryid);
    }

    public function test_complete_manually_rejects_already_completed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new followup_service();

        $id = $service->create($entryid, time(), priority_level::MEDIUM, get_admin()->id);
        $service->complete_manually($id, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->complete_manually($id, get_admin()->id);
    }

    public function test_postpone_rejects_cancelled_followup(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new followup_service();

        $id = $service->create($entryid, time(), priority_level::MEDIUM, get_admin()->id);
        $service->cancel($id, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->postpone($id, strtotime('+1 week'), get_admin()->id);
    }
}
