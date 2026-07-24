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
 * Tests for entry_reason_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_reason_repository_test extends \advanced_testcase {

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

    public function test_attach_and_get_for_entry(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $reasonrepo = new reason_repository();
        $reason1 = $reasonrepo->create((object) ['name' => 'R1', 'shortname' => 'r1-' . uniqid(), 'createdby' => get_admin()->id]);
        $reason2 = $reasonrepo->create((object) ['name' => 'R2', 'shortname' => 'r2-' . uniqid(), 'createdby' => get_admin()->id]);

        $repository = new entry_reason_repository();
        $repository->attach($entryid, [$reason1, $reason2]);

        $ids = $repository->get_for_entry($entryid);

        $this->assertEqualsCanonicalizing([$reason1, $reason2], $ids);
    }

    public function test_get_for_entry_returns_empty_array_when_none_attached(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();

        $this->assertSame([], (new entry_reason_repository())->get_for_entry($entryid));
    }

    public function test_attach_ignores_duplicate_ids_in_the_same_call(): void {
        $this->resetAfterTest();

        $entryid = $this->create_entry();
        $reasonrepo = new reason_repository();
        $reasonid = $reasonrepo->create((object) ['name' => 'R1', 'shortname' => 'r1-' . uniqid(), 'createdby' => get_admin()->id]);

        $repository = new entry_reason_repository();
        $repository->attach($entryid, [$reasonid, $reasonid]);

        $this->assertSame([$reasonid], $repository->get_for_entry($entryid));
    }

    public function test_get_for_entries_batches_in_one_call(): void {
        $this->resetAfterTest();

        $entry1 = $this->create_entry();
        $entry2 = $this->create_entry();
        $reasonrepo = new reason_repository();
        $reason1 = $reasonrepo->create((object) ['name' => 'R1', 'shortname' => 'r1-' . uniqid(), 'createdby' => get_admin()->id]);
        $reason2 = $reasonrepo->create((object) ['name' => 'R2', 'shortname' => 'r2-' . uniqid(), 'createdby' => get_admin()->id]);

        $repository = new entry_reason_repository();
        $repository->attach($entry1, [$reason1, $reason2]);
        $repository->attach($entry2, [$reason2]);

        $result = $repository->get_for_entries([$entry1, $entry2, 999999]);

        $this->assertEqualsCanonicalizing([$reason1, $reason2], $result[$entry1]);
        $this->assertSame([$reason2], $result[$entry2]);
        $this->assertArrayNotHasKey(999999, $result);
    }

    public function test_get_for_entries_with_empty_array_returns_empty_array(): void {
        $this->resetAfterTest();

        $this->assertSame([], (new entry_reason_repository())->get_for_entries([]));
    }
}
