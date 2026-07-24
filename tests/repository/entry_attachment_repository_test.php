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

use local_monlaututoria\domain\entry_attachment_category;

/**
 * Tests for entry_attachment_repository — category/description metadata
 * only, never the file bytes (Moodle's File API's job).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_repository_test extends \advanced_testcase {

    /**
     * @return int
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

    public function test_create_and_exists_for_pathnamehash(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $repository = new entry_attachment_repository();

        $this->assertFalse($repository->exists_for_pathnamehash('abc123'));

        $repository->create((object) [
            'entryid' => $entryid, 'pathnamehash' => 'abc123',
            'category' => entry_attachment_category::REPORT, 'createdby' => get_admin()->id,
        ]);

        $this->assertTrue($repository->exists_for_pathnamehash('abc123'));
    }

    public function test_get_for_entry_keys_by_pathnamehash(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $repository = new entry_attachment_repository();

        $repository->create((object) [
            'entryid' => $entryid, 'pathnamehash' => 'hash-one',
            'category' => entry_attachment_category::CONSENT, 'createdby' => get_admin()->id,
        ]);

        $byhash = $repository->get_for_entry($entryid);

        $this->assertArrayHasKey('hash-one', $byhash);
        $this->assertSame(entry_attachment_category::CONSENT, $byhash['hash-one']->category);
    }

    public function test_get_for_entry_returns_empty_array_when_none_attached(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();

        $this->assertSame([], (new entry_attachment_repository())->get_for_entry($entryid));
    }
}
