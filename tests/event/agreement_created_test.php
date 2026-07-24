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

namespace local_monlaututoria\event;

use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\service\agreement_service;
use local_monlaututoria\domain\agreement_create_command;
use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\domain\agreement_status;

/**
 * Tests for the local_tut_agreement events (phase 6.1/6.3).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_created_test extends \advanced_testcase {

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

    public function test_create_triggers_agreement_created(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new agreement_service();

        $sink = $this->redirectEvents();
        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time()),
            get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof agreement_created));
        $this->assertCount(1, $matching);
        $this->assertSame($id, $matching[0]->objectid);
        $this->assertSame($student->id, $matching[0]->relateduserid);
        $this->assertSame($entryid, $matching[0]->other['entryid']);
    }

    public function test_complete_triggers_agreement_updated_with_old_and_new_status(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new agreement_service();
        $id = $service->create(
            new agreement_create_command($entryid, 'A', agreement_responsible_type::TUTOR, $tutor->id, null, time()),
            get_admin()->id
        );

        $sink = $this->redirectEvents();
        $service->complete($id, get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof agreement_updated));
        $this->assertCount(1, $matching);
        $this->assertSame(agreement_status::PENDING, $matching[0]->other['oldstatus']);
        $this->assertSame(agreement_status::COMPLETED, $matching[0]->other['newstatus']);
    }
}
