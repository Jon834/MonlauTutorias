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

/**
 * Tests for entry_version_repository — the first writer local_tut_entryversion
 * has had since phase 5.1 created it empty.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_version_repository_test extends \advanced_testcase {

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

    public function test_get_next_version_number_starts_at_one(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();

        $this->assertSame(1, (new entry_version_repository())->get_next_version_number($entryid));
    }

    public function test_create_and_get_for_entry(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $repository = new entry_version_repository();

        $repository->create((object) [
            'entryid' => $entryid, 'versionnumber' => 1,
            'snapshotjson' => json_encode(['contentvisible' => 'Old content']),
            'changereason' => 'Typo fix', 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'entryid' => $entryid, 'versionnumber' => 2,
            'snapshotjson' => json_encode(['contentvisible' => 'Newer content']),
            'createdby' => get_admin()->id,
        ]);

        $versions = array_values($repository->get_for_entry($entryid));

        $this->assertCount(2, $versions);
        // Most recent version first.
        $this->assertSame(2, (int) $versions[0]->versionnumber);
        $this->assertNull($versions[0]->changereason);
        $this->assertSame(1, (int) $versions[1]->versionnumber);
        $this->assertSame('Typo fix', $versions[1]->changereason);

        $this->assertSame(3, $repository->get_next_version_number($entryid));
    }

    public function test_get_next_version_number_is_independent_per_entry(): void {
        $this->resetAfterTest();

        $entry1 = $this->create_entry();
        $entry2 = $this->create_entry();
        $repository = new entry_version_repository();

        $repository->create((object) [
            'entryid' => $entry1, 'versionnumber' => 1,
            'snapshotjson' => '{}', 'createdby' => get_admin()->id,
        ]);

        $this->assertSame(2, $repository->get_next_version_number($entry1));
        $this->assertSame(1, $repository->get_next_version_number($entry2));
    }
}
