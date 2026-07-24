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
use local_monlaututoria\service\academic_year_service;

/**
 * Tests for the local_tut_academicyear events.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_events_test extends \advanced_testcase {

    public function test_create_triggers_created_event(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $sink = $this->redirectEvents();

        $id = $service->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
        ], $userid);

        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(academic_year_created::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertSame('local_tut_academicyear', $events[0]->objecttable);
    }

    public function test_activate_triggers_activated_event_with_previous(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $first = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);
        $second = $service->create((object) [
            'name' => 'Segundo', 'shortname' => 'segundo',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
        ], $userid);

        $service->activate($first, $userid);

        $sink = $this->redirectEvents();
        $service->activate($second, $userid);
        $events = $sink->get_events();
        $sink->close();

        $activated = array_values(array_filter($events, static fn ($e) => $e instanceof academic_year_activated));
        $this->assertCount(1, $activated);
        $this->assertEquals($second, $activated[0]->objectid);
        $this->assertEquals($first, $activated[0]->other['previousactiveid']);
    }

    public function test_lock_triggers_locked_event(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        $sink = $this->redirectEvents();
        $service->set_locked($id, true, $userid, false);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(academic_year_locked::class, $events[0]);
        $this->assertTrue($events[0]->other['locked']);
    }

    public function test_delete_triggers_deleted_event_with_shortname(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        $sink = $this->redirectEvents();
        $service->delete($id, $userid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(academic_year_deleted::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertSame('primero', $events[0]->other['shortname']);
    }
}
