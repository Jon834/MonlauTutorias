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

use local_monlaututoria\domain\entry_participant_type;

/**
 * Tests for entry_participant_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_participant_repository_test extends \advanced_testcase {

    /**
     * @return int a plain local_tut_entry row id, no need for the full service
     */
    private function create_entry(): int {
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearrepo = new academic_year_repository();
        $academicyearid = $academicyearrepo->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);

        return (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'createdby' => get_admin()->id,
        ]);
    }

    public function test_create_internal_and_external_participant(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $internaluser = $this->getDataGenerator()->create_user();
        $repository = new entry_participant_repository();

        $repository->create((object) [
            'entryid' => $entryid, 'participanttype' => entry_participant_type::TEACHER,
            'userid' => $internaluser->id, 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'entryid' => $entryid, 'participanttype' => entry_participant_type::FAMILY,
            'externalname' => 'Jane Doe (mother)', 'createdby' => get_admin()->id,
        ]);

        $participants = array_values($repository->get_for_entry($entryid));

        $this->assertCount(2, $participants);
        $this->assertSame($internaluser->id, (int) $participants[0]->userid);
        $this->assertNull($participants[0]->externalname);
        $this->assertNull($participants[1]->userid);
        $this->assertSame('Jane Doe (mother)', $participants[1]->externalname);
    }

    public function test_get_for_entry_returns_empty_for_entry_without_participants(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();

        $this->assertSame([], (new entry_participant_repository())->get_for_entry($entryid));
    }
}
