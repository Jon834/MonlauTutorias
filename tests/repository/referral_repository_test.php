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

use local_monlaututoria\domain\referral_status;
use local_monlaututoria\domain\referral_destination;

/**
 * Tests for referral_repository (phase 6.4).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_repository_test extends \advanced_testcase {

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

        $repository = new referral_repository();
        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::COORDINATION,
            'reason' => 'Repeated absences', 'createdby' => get_admin()->id,
        ]);

        $record = $repository->get($id);
        $this->assertSame($student->id, (int) $record->studentid);
        $this->assertSame(referral_status::PENDING, $record->status);
        $this->assertNull($record->assignedto);
        $this->assertNull($record->resolution);
    }

    public function test_assign_sets_assignedto_and_moves_pending_to_in_progress(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new referral_repository();

        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::ORIENTATION,
            'reason' => 'A', 'createdby' => get_admin()->id,
        ]);

        $repository->assign($id, $staff->id, get_admin()->id);

        $record = $repository->get($id);
        $this->assertSame($staff->id, (int) $record->assignedto);
        $this->assertSame(referral_status::IN_PROGRESS, $record->status);
    }

    public function test_resolve_sets_status_and_resolution(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new referral_repository();

        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::MANAGEMENT,
            'reason' => 'A', 'createdby' => get_admin()->id,
        ]);

        $repository->resolve($id, 'Met with the family, agreed a plan', get_admin()->id);

        $record = $repository->get($id);
        $this->assertSame(referral_status::RESOLVED, $record->status);
        $this->assertSame('Met with the family, agreed a plan', $record->resolution);
    }

    public function test_search_filters_by_status_and_assignedto(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $repository = new referral_repository();

        $id = $repository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::COORDINATION,
            'reason' => 'A', 'createdby' => get_admin()->id,
        ]);
        $repository->assign($id, $staff->id, get_admin()->id);

        $this->assertSame(1, $repository->count_search(['assignedto' => $staff->id]));
        $this->assertSame(1, $repository->count_search(['status' => referral_status::IN_PROGRESS]));
        $this->assertSame(0, $repository->count_search(['status' => referral_status::RESOLVED]));
    }
}
