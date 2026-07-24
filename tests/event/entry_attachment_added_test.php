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
use local_monlaututoria\service\entry_attachment_service;
use local_monlaututoria\domain\entry_create_command;
use local_monlaututoria\domain\entry_attachment_category;

/**
 * Tests for the entry_attachment_added event.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_added_test extends \advanced_testcase {

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

    public function test_save_uploaded_files_triggers_entry_attachment_added(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $entryid = (new entry_service())->create(
            new entry_create_command($student->id, $tutor->id, $academicyearid, strtotime('2026-10-01')),
            get_admin()->id
        );

        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, null, 'user', 'draft', null);
        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance(get_admin()->id)->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftitemid,
            'filepath'  => '/',
            'filename'  => 'evidencia.txt',
        ], 'contenido');

        $sink = $this->redirectEvents();
        (new entry_attachment_service())->save_uploaded_files(
            $entryid, $draftitemid, entry_attachment_category::EVIDENCE, get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $matching = array_values(array_filter($events, static fn ($event) => $event instanceof entry_attachment_added));
        $this->assertCount(1, $matching);
        $this->assertSame($entryid, $matching[0]->objectid);
        $this->assertSame($student->id, $matching[0]->relateduserid);
        $this->assertSame(1, $matching[0]->other['count']);
        $this->assertSame(entry_attachment_category::EVIDENCE, $matching[0]->other['category']);
    }
}
