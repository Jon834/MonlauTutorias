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
 * Tests for modality_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class modality_repository_test extends \advanced_testcase {

    public function test_create_defaults_active_and_get_dto(): void {
        $this->resetAfterTest();

        $repository = new modality_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) ['name' => 'Presencial', 'shortname' => 'presencial', 'createdby' => $userid]);

        $record = $repository->get($id);
        $this->assertSame(1, (int) $record->active);

        $dto = $repository->get_dto($id);
        $this->assertInstanceOf(\local_monlaututoria\domain\modality::class, $dto);
    }

    public function test_shortname_uniqueness(): void {
        $this->resetAfterTest();

        $repository = new modality_repository();
        $userid = get_admin()->id;

        $repository->create((object) ['name' => 'A', 'shortname' => 'dup', 'createdby' => $userid]);

        $this->assertTrue($repository->shortname_exists('dup'));
    }

    public function test_delete(): void {
        $this->resetAfterTest();

        $repository = new modality_repository();
        $userid = get_admin()->id;

        $id = $repository->create((object) ['name' => 'A', 'shortname' => 'a', 'createdby' => $userid]);
        $repository->delete($id);

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get($id);
    }
}
