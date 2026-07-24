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

use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\service\csv_import_preview_service;
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\domain\bulk_operation_status;

/**
 * Tests for process_csv_import_task (phase 3D.4), the ad hoc task
 * csv_import_dispatch_service queues for large CSV imports. Runs execute()
 * directly rather than through the real task queue/cron, same as any other
 * Moodle plugin's ad hoc task tests — the queueing mechanism itself is core
 * behaviour, not this plugin's.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class process_csv_import_task_test extends \advanced_testcase {

    /**
     * @return \stdClass
     */
    private function create_academic_year(): \stdClass {
        $repo = new academic_year_repository();
        $id = $repo->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);

        return $repo->get($id);
    }

    /**
     * Mirrors what csv_import_dispatch_service::persist_file_for_task() does,
     * without going through dispatch() itself (which would apply small
     * imports inline instead of deferring).
     *
     * @param int $operationid
     * @param string $content
     * @return void
     */
    private function store_operation_file(int $operationid, string $content): void {
        $syscontext = \context_system::instance();
        get_file_storage()->create_file_from_string([
            'contextid' => $syscontext->id,
            'component' => 'local_monlaututoria',
            'filearea'  => 'csvimport',
            'itemid'    => $operationid,
            'filepath'  => '/',
            'filename'  => 'import.csv',
        ], $content);
    }

    public function test_execute_applies_the_import_and_deletes_the_file(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $bulkoperationrepository = new bulk_operation_repository();
        $bulkoperationrepository->update_parameters($preview->operationid, json_encode([
            'delimiter'              => ',',
            'encoding'               => 'UTF-8',
            'excludedrownumbers'     => [],
            'strategy'               => csv_import_apply_strategy::PARTIAL_VALID,
            'allowreassignconflicts' => false,
        ]));
        $this->store_operation_file($preview->operationid, $content);

        $task = new process_csv_import_task();
        $task->set_custom_data(['operationid' => $preview->operationid, 'userid' => get_admin()->id]);
        $task->execute();

        $operation = $bulkoperationrepository->get($preview->operationid);
        $this->assertSame(bulk_operation_status::COMPLETED, $operation->status);

        $assignmentrepository = new assignment_repository();
        $this->assertTrue($assignmentrepository->has_active_duplicate($student->id, $tutor->id, $year->id, 'primary'));

        $files = get_file_storage()->get_area_files(
            \context_system::instance()->id, 'local_monlaututoria', 'csvimport', $preview->operationid, 'id', false
        );
        $this->assertEmpty($files);
    }

    public function test_execute_without_a_stored_file_marks_operation_failed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        // No store_operation_file() call: the file is missing, e.g. it was
        // already cleaned up by another process.
        $task = new process_csv_import_task();
        $task->set_custom_data(['operationid' => $preview->operationid, 'userid' => get_admin()->id]);

        $sink = $this->redirectEvents();
        $task->execute();
        $events = $sink->get_events();
        $sink->close();

        $bulkoperationrepository = new bulk_operation_repository();
        $operation = $bulkoperationrepository->get($preview->operationid);
        $this->assertSame(bulk_operation_status::FAILED, $operation->status);

        $failedevents = array_values(array_filter(
            $events,
            static fn ($event) => $event instanceof \local_monlaututoria\event\csv_import_failed
        ));
        $this->assertCount(1, $failedevents);
        $this->assertNull($failedevents[0]->other['failedrownumber']);
    }

    public function test_execute_on_an_already_processed_operation_is_a_noop(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $bulkoperationrepository = new bulk_operation_repository();
        $bulkoperationrepository->update_status($preview->operationid, bulk_operation_status::CANCELLED);
        $this->store_operation_file($preview->operationid, $content);

        $task = new process_csv_import_task();
        $task->set_custom_data(['operationid' => $preview->operationid, 'userid' => get_admin()->id]);
        $task->execute();

        // Untouched: still CANCELLED, not FAILED/COMPLETED, and the file is
        // left for cleanup_bulk_operations_task rather than this task acting
        // on an operation it no longer owns.
        $operation = $bulkoperationrepository->get($preview->operationid);
        $this->assertSame(bulk_operation_status::CANCELLED, $operation->status);
    }
}
