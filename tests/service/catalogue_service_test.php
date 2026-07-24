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

use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;

/**
 * Tests for catalogue_service, shared by the reason and modality catalogues.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class catalogue_service_test extends \advanced_testcase {

    /**
     * @return array
     */
    public static function catalogue_type_provider(): array {
        return [
            'reason'   => [catalogue_service::TYPE_REASON],
            'modality' => [catalogue_service::TYPE_MODALITY],
        ];
    }

    /**
     * @param string $type
     * @return reason_repository|modality_repository
     */
    private function repository_for(string $type) {
        return $type === catalogue_service::TYPE_REASON
            ? new reason_repository()
            : new modality_repository();
    }

    /**
     * @dataProvider catalogue_type_provider
     */
    public function test_create_and_shortname_uniqueness(string $type): void {
        $this->resetAfterTest();

        $service = new catalogue_service($type, $this->repository_for($type));
        $userid = get_admin()->id;

        $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);

        $this->expectException(\moodle_exception::class);
        $service->create((object) ['name' => 'Otro', 'shortname' => 'uno'], $userid);
    }

    /**
     * @dataProvider catalogue_type_provider
     */
    public function test_set_active(string $type): void {
        $this->resetAfterTest();

        $repository = $this->repository_for($type);
        $service = new catalogue_service($type, $repository);
        $userid = get_admin()->id;

        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);
        $service->set_active($id, false, $userid);

        $this->assertSame(0, (int) $repository->get($id)->active);
    }

    /**
     * @dataProvider catalogue_type_provider
     */
    public function test_move_swaps_order(string $type): void {
        $this->resetAfterTest();

        $repository = $this->repository_for($type);
        $service = new catalogue_service($type, $repository);
        $userid = get_admin()->id;

        $service->create((object) ['name' => 'Primero', 'shortname' => 'primero', 'sortorder' => 1], $userid);
        $second = $service->create((object) ['name' => 'Segundo', 'shortname' => 'segundo', 'sortorder' => 2], $userid);

        $service->move($second, -1);

        $all = array_values($repository->get_all());
        $this->assertSame('Segundo', $all[0]->name);
        $this->assertSame('Primero', $all[1]->name);
    }

    /**
     * @dataProvider catalogue_type_provider
     */
    public function test_delete(string $type): void {
        $this->resetAfterTest();

        $repository = $this->repository_for($type);
        $service = new catalogue_service($type, $repository);
        $userid = get_admin()->id;

        $id = $service->create((object) ['name' => 'Uno', 'shortname' => 'uno'], $userid);
        $service->delete($id, $userid);

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get($id);
    }
}
