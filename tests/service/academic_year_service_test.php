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

namespace local_monlaututoria\service;

use local_monlaututoria\repository\academic_year_repository;

/**
 * Tests for academic_year_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_service_test extends \advanced_testcase {

    private function repository(): academic_year_repository {
        return new academic_year_repository();
    }

    public function test_create_validates_dates(): void {
        $this->resetAfterTest();

        $service = new academic_year_service($this->repository());
        $userid = get_admin()->id;

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027',
            'startdate' => strtotime('2027-06-30'), 'enddate' => strtotime('2026-09-01'),
        ], $userid);
    }

    public function test_create_validates_shortname_uniqueness(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $service->create((object) [
            'name' => '2026-2027', 'shortname' => 'dup',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
        ], $userid);

        $this->expectException(\moodle_exception::class);
        $service->create((object) [
            'name' => 'Otro', 'shortname' => 'dup',
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
        ], $userid);
    }

    public function test_activate_deactivates_previous(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
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
        $this->assertSame(1, (int) $repository->get($first)->active);

        $service->activate($second, $userid);
        $this->assertSame(0, (int) $repository->get($first)->active);
        $this->assertSame(1, (int) $repository->get($second)->active);
    }

    public function test_locked_year_cannot_be_updated_without_override(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        $service->set_locked($id, true, $userid, false);

        $this->expectException(\moodle_exception::class);
        $service->update((object) ['id' => $id, 'name' => 'Cambiado'], $userid, false);
    }

    public function test_locked_year_can_be_updated_with_override(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        $service->set_locked($id, true, $userid, false);
        $service->update((object) ['id' => $id, 'name' => 'Cambiado'], $userid, true);

        $this->assertSame('Cambiado', $repository->get($id)->name);
    }

    public function test_unlocking_requires_override_only_when_locked(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        // Locking never requires the override capability.
        $service->set_locked($id, true, $userid, false);
        $this->assertSame(1, (int) $repository->get($id)->locked);

        $this->expectException(\moodle_exception::class);
        $service->set_locked($id, false, $userid, false);
    }

    public function test_delete_blocks_active_year(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);
        $service->activate($id, $userid);

        $this->expectException(\moodle_exception::class);
        $service->delete($id, $userid);
    }

    public function test_delete_allows_inactive_unlocked_year(): void {
        $this->resetAfterTest();

        $repository = $this->repository();
        $service = new academic_year_service($repository);
        $userid = get_admin()->id;

        $id = $service->create((object) [
            'name' => 'Primero', 'shortname' => 'primero',
            'startdate' => strtotime('2025-09-01'), 'enddate' => strtotime('2026-06-30'),
        ], $userid);

        $service->delete($id, $userid);

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get($id);
    }
}
