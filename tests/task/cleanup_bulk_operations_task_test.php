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

namespace local_monlaututoria\task;

use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\bulk_operation_status;

/**
 * Tests for cleanup_bulk_operations_task (phase 3D.4).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cleanup_bulk_operations_task_test extends \advanced_testcase {

    /**
     * @param string $status
     * @param int $age seconds old the operation should appear
     * @return int operation id
     */
    private function create_operation(string $status, int $age): int {
        global $DB;

        $repo = new bulk_operation_repository();
        $id = $repo->create((object) [
            'operationuuid' => bulk_operation_repository::generate_uuid(),
            'operationtype' => 'csv_import',
            'status'        => $status,
            'createdby'     => get_admin()->id,
        ]);

        if ($age > 0) {
            $DB->set_field('local_tut_bulkoperation', 'timecreated', time() - $age, ['id' => $id]);
        }

        return $id;
    }

    /**
     * @param int $operationid
     * @return void
     */
    private function store_file(int $operationid): void {
        $syscontext = \context_system::instance();
        get_file_storage()->create_file_from_string([
            'contextid' => $syscontext->id,
            'component' => 'local_monlaututoria',
            'filearea'  => 'csvimport',
            'itemid'    => $operationid,
            'filepath'  => '/',
            'filename'  => 'import.csv',
        ], "student,tutor,academicyear\n");
    }

    /**
     * @param int $operationid
     * @return int
     */
    private function count_files(int $operationid): int {
        return count(get_file_storage()->get_area_files(
            \context_system::instance()->id, 'local_monlaututoria', 'csvimport', $operationid, 'id', false
        ));
    }

    public function test_abandoned_previewed_operation_is_purged(): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation(
            bulk_operation_status::PREVIEWED,
            cleanup_bulk_operations_task::ABANDONED_TTL_SECONDS + 3600
        );
        $this->store_file($operationid);

        (new cleanup_bulk_operations_task())->execute();

        $this->expectException(\dml_missing_record_exception::class);
        (new bulk_operation_repository())->get($operationid);
    }

    public function test_abandoned_operation_purge_also_deletes_its_file(): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation(
            bulk_operation_status::DRAFT,
            cleanup_bulk_operations_task::ABANDONED_TTL_SECONDS + 3600
        );
        $this->store_file($operationid);

        (new cleanup_bulk_operations_task())->execute();

        $this->assertSame(0, $this->count_files($operationid));
    }

    public function test_recent_previewed_operation_is_kept(): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation(bulk_operation_status::PREVIEWED, 0);
        $this->store_file($operationid);

        (new cleanup_bulk_operations_task())->execute();

        $operation = (new bulk_operation_repository())->get($operationid);
        $this->assertSame(bulk_operation_status::PREVIEWED, $operation->status);
        // Still in flight: the file must survive, process_csv_import_task
        // may still need it.
        $this->assertSame(1, $this->count_files($operationid));
    }

    public function test_orphaned_file_for_a_completed_operation_is_deleted(): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation(bulk_operation_status::COMPLETED, 0);
        $this->store_file($operationid);

        (new cleanup_bulk_operations_task())->execute();

        // The operation row itself is untouched: it is recent (age 0), well
        // within the 90-day retention window (phase 3E.6) — only the
        // now-purposeless file is removed.
        $operation = (new bulk_operation_repository())->get($operationid);
        $this->assertSame(bulk_operation_status::COMPLETED, $operation->status);
        $this->assertSame(0, $this->count_files($operationid));
    }

    public function test_file_for_a_nonexistent_operation_is_deleted(): void {
        $this->resetAfterTest();

        $orphanitemid = 999999;
        $this->store_file($orphanitemid);

        (new cleanup_bulk_operations_task())->execute();

        $this->assertSame(0, $this->count_files($orphanitemid));
    }

    /**
     * @dataProvider terminal_status_provider
     */
    public function test_finished_operation_older_than_90_days_is_purged(string $status): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation(
            $status,
            cleanup_bulk_operations_task::TERMINAL_TTL_SECONDS + 3600
        );

        (new cleanup_bulk_operations_task())->execute();

        $this->expectException(\dml_missing_record_exception::class);
        (new bulk_operation_repository())->get($operationid);
    }

    /**
     * @dataProvider terminal_status_provider
     */
    public function test_recent_finished_operation_is_kept(string $status): void {
        $this->resetAfterTest();

        $operationid = $this->create_operation($status, 0);

        (new cleanup_bulk_operations_task())->execute();

        $operation = (new bulk_operation_repository())->get($operationid);
        $this->assertSame($status, $operation->status);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function terminal_status_provider(): array {
        return [
            'completed'              => [bulk_operation_status::COMPLETED],
            'completed_with_errors'  => [bulk_operation_status::COMPLETED_WITH_ERRORS],
            'failed'                 => [bulk_operation_status::FAILED],
            'cancelled'              => [bulk_operation_status::CANCELLED],
        ];
    }
}
