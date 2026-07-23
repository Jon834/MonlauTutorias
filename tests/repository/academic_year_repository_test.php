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
 * Tests for academic_year_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_repository_test extends \advanced_testcase {

    public function test_create_and_get(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => $userid,
        ]);

        $record = $repository->get($id);

        $this->assertSame('2026-2027', $record->name);
        $this->assertSame(0, (int) $record->active);
        $this->assertSame(0, (int) $record->locked);
    }

    public function test_get_missing_throws_exception(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get(999999);
    }

    public function test_find_by_shortname(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $id = $repository->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        $record = $repository->get($id);

        $this->assertSame($id, (int) $repository->find_by_shortname($record->shortname)->id);
        $this->assertNull($repository->find_by_shortname('does-not-exist'));
    }

    public function test_shortname_exists(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => $userid,
        ]);

        $this->assertTrue($repository->shortname_exists('2026-2027'));
        $this->assertFalse($repository->shortname_exists('2026-2027', $id));
        $this->assertFalse($repository->shortname_exists('2099-2100'));
    }

    public function test_get_active_and_clear_active(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => $userid,
        ]);

        $this->assertNull($repository->get_active());

        $repository->set_active_flag($id, true, $userid);
        $active = $repository->get_active();
        $this->assertNotNull($active);
        $this->assertSame($id, (int) $active->id);

        $cleared = $repository->clear_active($userid);
        $this->assertSame([$id], $cleared);
        $this->assertNull($repository->get_active());
    }

    public function test_update(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => $userid,
        ]);

        $repository->update((object) ['id' => $id, 'name' => '2026-2027 (revisado)'], $userid);

        $record = $repository->get($id);
        $this->assertSame('2026-2027 (revisado)', $record->name);
    }

    public function test_delete(): void {
        $this->resetAfterTest();

        $repository = new academic_year_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => $userid,
        ]);

        $repository->delete($id);

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get($id);
    }
}
