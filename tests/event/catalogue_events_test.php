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

use local_monlaututoria\service\catalogue_service;

/**
 * Tests for the local_tut_reason / local_tut_modality events.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class catalogue_events_test extends \advanced_testcase {

    public function test_reason_create_triggers_reason_created_event(): void {
        $this->resetAfterTest();

        $service = new catalogue_service(catalogue_service::TYPE_REASON);
        $userid = get_admin()->id;

        $sink = $this->redirectEvents();
        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(reason_created::class, $events[0]);
        $this->assertSame('local_tut_reason', $events[0]->objecttable);
        $this->assertEquals($id, $events[0]->objectid);
    }

    public function test_modality_deactivate_triggers_modality_activated_event(): void {
        $this->resetAfterTest();

        $service = new catalogue_service(catalogue_service::TYPE_MODALITY);
        $userid = get_admin()->id;

        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);

        $sink = $this->redirectEvents();
        $service->set_active($id, false, $userid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(modality_activated::class, $events[0]);
        $this->assertFalse($events[0]->other['active']);
    }

    public function test_reason_delete_triggers_reason_deleted_event_with_shortname(): void {
        $this->resetAfterTest();

        $service = new catalogue_service(catalogue_service::TYPE_REASON);
        $userid = get_admin()->id;

        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);

        $sink = $this->redirectEvents();
        $service->delete($id, $userid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(reason_deleted::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertSame('uno', $events[0]->other['shortname']);
    }

    public function test_modality_delete_triggers_modality_deleted_event_with_shortname(): void {
        $this->resetAfterTest();

        $service = new catalogue_service(catalogue_service::TYPE_MODALITY);
        $userid = get_admin()->id;

        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);

        $sink = $this->redirectEvents();
        $service->delete($id, $userid);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(modality_deleted::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertSame('uno', $events[0]->other['shortname']);
    }
}
