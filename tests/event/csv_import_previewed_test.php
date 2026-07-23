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
use local_monlaututoria\service\csv_import_preview_service;

/**
 * Tests for the csv_import_previewed event.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_previewed_test extends \advanced_testcase {

    public function test_preview_triggers_event_with_aggregate_data_only(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();

        $academicyearrepository = new academic_year_repository();
        $yearid = $academicyearrepository->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        $year = $academicyearrepository->get($yearid);

        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $service = new csv_import_preview_service();

        $sink = $this->redirectEvents();
        $preview = $service->preview($content, ',', 'UTF-8', get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(csv_import_previewed::class, $events[0]);
        $this->assertEquals($preview->operationid, $events[0]->objectid);
        $this->assertSame('local_tut_bulkoperation', $events[0]->objecttable);
        $this->assertSame(1, $events[0]->other['totalrows']);
        $this->assertSame(1, $events[0]->other['validcount']);
        $this->assertArrayNotHasKey('rows', $events[0]->other);
    }
}
