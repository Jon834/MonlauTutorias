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
use local_monlaututoria\service\entry_service;
use local_monlaututoria\domain\entry_create_command;

/**
 * Tests for the entry_annulled event.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_annulled_test extends \advanced_testcase {

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

    public function test_annul_triggers_entry_annulled_with_the_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create(
            new entry_create_command($student->id, $tutor->id, $academicyearid, strtotime('2026-10-01')),
            get_admin()->id
        );

        $sink = $this->redirectEvents();
        $service->annul($id, get_admin()->id, 'Family moved away');
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof entry_annulled));
        $this->assertCount(1, $matching);
        $this->assertSame($id, $matching[0]->objectid);
        $this->assertSame($student->id, $matching[0]->relateduserid);
        $this->assertSame('Family moved away', $matching[0]->other['reason']);
    }
}
