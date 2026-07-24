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
use local_monlaututoria\service\referral_service;
use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\domain\priority_level;

/**
 * Tests for the local_tut_referral events (phase 6.4). Confirms
 * referral_updated never carries the resolution text in `other` — see that
 * event class's docblock.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_created_test extends \advanced_testcase {

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

    public function test_create_triggers_referral_created(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new referral_service();

        $sink = $this->redirectEvents();
        $id = $service->create($entryid, referral_destination::COORDINATION, 'A', priority_level::MEDIUM, get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof referral_created));
        $this->assertCount(1, $matching);
        $this->assertSame($id, $matching[0]->objectid);
        $this->assertSame($student->id, $matching[0]->relateduserid);
        $this->assertSame(referral_destination::COORDINATION, $matching[0]->other['destination']);
    }

    public function test_resolve_triggers_referral_updated_without_leaking_resolution_text(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new referral_service();
        $id = $service->create($entryid, referral_destination::COORDINATION, 'A', priority_level::MEDIUM, get_admin()->id);

        $sink = $this->redirectEvents();
        $service->resolve($id, 'Contains the student\'s private situation', get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof referral_updated));
        $this->assertCount(1, $matching);
        $this->assertArrayNotHasKey('resolution', $matching[0]->other);
        $this->assertStringNotContainsString('private situation', json_encode($matching[0]->other));
    }
}
