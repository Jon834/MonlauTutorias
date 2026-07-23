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
use local_monlaututoria\service\assignment_service;
use local_monlaututoria\domain\assignment_close_reason;
use local_monlaututoria\domain\assignment_reassign_reason;
use local_monlaututoria\domain\reassign_assignment_command;

/**
 * Tests for the local_tut_assignment events.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_events_test extends \advanced_testcase {

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

    public function test_create_triggers_assignment_created(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $sink = $this->redirectEvents();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(assignment_created::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertEquals($student->id, $events[0]->relateduserid);
        $this->assertSame('local_tut_assignment', $events[0]->objecttable);
        $this->assertEquals(CONTEXT_SYSTEM, $events[0]->contextlevel);
    }

    public function test_cotutor_create_triggers_co_tutor_added(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();

        $sink = $this->redirectEvents();
        $service->add_cotutor($student->id, $tutor->id, $academicyearid, get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(co_tutor_added::class, $events[0]);
    }

    public function test_close_triggers_assignment_closed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $sink = $this->redirectEvents();
        $service->close($id, get_admin()->id, assignment_close_reason::END_OF_YEAR);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(assignment_closed::class, $events[0]);
        $this->assertSame(assignment_close_reason::END_OF_YEAR, $events[0]->other['closereason']);
        $this->assertFalse($events[0]->other['leftwithoutprimary']);
    }

    public function test_close_primary_flags_left_without_primary_in_event(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $sink = $this->redirectEvents();
        $service->close($id, get_admin()->id, assignment_close_reason::TUTOR_LEFT);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertTrue($events[0]->other['leftwithoutprimary']);
    }

    public function test_reassign_triggers_single_student_reassigned_event(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor1 = $this->getDataGenerator()->create_user();
        $tutor2 = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $oldid = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor1->id,
            'academicyearid' => $academicyearid, 'isprimary' => true,
        ], get_admin()->id);

        $sink = $this->redirectEvents();
        $result = $service->reassign_primary_tutor(
            new reassign_assignment_command(
                $student->id,
                $tutor2->id,
                $academicyearid,
                assignment_reassign_reason::REORGANIZATION
            ),
            get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(student_reassigned::class, $events[0]);
        $this->assertEquals($result->newassignmentid, $events[0]->objectid);
        $this->assertEquals($student->id, $events[0]->relateduserid);
        $this->assertEquals($tutor1->id, $events[0]->other['previoustutorid']);
        $this->assertEquals($oldid, $events[0]->other['previousassignmentid']);
        $this->assertSame(assignment_reassign_reason::REORGANIZATION, $events[0]->other['reassignreason']);
        $this->assertSame([], $events[0]->other['closedcotutorids']);
    }

    public function test_remove_cotutor_triggers_co_tutor_removed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $cotutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->add_cotutor($student->id, $cotutor->id, $academicyearid, get_admin()->id);

        $sink = $this->redirectEvents();
        $service->remove_cotutor($id, get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(co_tutor_removed::class, $events[0]);
    }

    public function test_assignment_viewed_event(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $sink = $this->redirectEvents();
        assignment_viewed::create_from_id($id, get_admin()->id, $student->id, $academicyearid)->trigger();
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(assignment_viewed::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertEquals($student->id, $events[0]->relateduserid);
        $this->assertSame('local_tut_assignment', $events[0]->objecttable);
        $this->assertEquals($academicyearid, $events[0]->other['academicyearid']);
    }

    public function test_update_triggers_assignment_updated(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);

        $sink = $this->redirectEvents();
        $service->update($id, (object) ['note' => 'Updated'], get_admin()->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(assignment_updated::class, $events[0]);
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertEquals($student->id, $events[0]->relateduserid);
        $this->assertSame(['note'], $events[0]->other['fieldschanged']);
        $this->assertArrayNotHasKey('reason', $events[0]->other);
    }

    public function test_update_event_records_reason_and_omits_note_content_when_editing_closed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new assignment_service();
        $id = $service->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $academicyearid,
        ], get_admin()->id);
        $service->close($id, get_admin()->id, assignment_close_reason::OTHER);

        $sink = $this->redirectEvents();
        $service->update(
            $id,
            (object) ['note' => 'Nota interna sensible'],
            get_admin()->id,
            true,
            false,
            'Corrección de fecha detectada en auditoría'
        );
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(assignment_updated::class, $events[0]);
        $this->assertSame(['note'], $events[0]->other['fieldschanged']);
        $this->assertSame('Corrección de fecha detectada en auditoría', $events[0]->other['reason']);
        $this->assertStringNotContainsString('Nota interna sensible', json_encode($events[0]->other));
    }
}
