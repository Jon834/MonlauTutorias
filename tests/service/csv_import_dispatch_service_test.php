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
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\task\process_csv_import_task;

/**
 * Tests for csv_import_dispatch_service (phase 3D.4): the threshold decision
 * between applying inline and deferring to process_csv_import_task.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_dispatch_service_test extends \advanced_testcase {

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
     * @param int $rowcount
     * @param \stdClass $year
     * @return array{content: string, draftitemid: int}
     */
    private function build_large_csv(int $rowcount, \stdClass $year): array {
        $content = "student,tutor,academicyear\n";
        for ($i = 0; $i < $rowcount; $i++) {
            $student = $this->getDataGenerator()->create_user();
            $tutor = $this->getDataGenerator()->create_user();
            $content .= "{$student->email},{$tutor->email},{$year->shortname}\n";
        }

        $draftitemid = file_get_unused_draft_itemid();
        $usercontext = \context_user::instance(get_admin()->id);
        get_file_storage()->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftitemid,
            'filepath'  => '/',
            'filename'  => 'import.csv',
        ], $content);

        return ['content' => $content, 'draftitemid' => $draftitemid];
    }

    public function test_small_import_applies_inline(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $dispatchservice = new csv_import_dispatch_service();
        $result = $dispatchservice->dispatch(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id, false, 0
        );

        $this->assertNotNull($result);
        $this->assertSame(bulk_operation_status::COMPLETED, $result->finalstatus);
        $this->assertEmpty(\core\task\manager::get_adhoc_tasks(process_csv_import_task::class));
    }

    public function test_large_import_is_deferred_to_adhoc_task(): void {
        $this->resetAfterTest();

        $year = $this->create_academic_year();
        $rowcount = csv_import_dispatch_service::LARGE_IMPORT_THRESHOLD + 1;
        ['content' => $content, 'draftitemid' => $draftitemid] = $this->build_large_csv($rowcount, $year);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $dispatchservice = new csv_import_dispatch_service();

        $sink = $this->redirectEvents();
        $result = $dispatchservice->dispatch(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::ATOMIC_ALL, get_admin()->id, true, $draftitemid
        );
        $events = $sink->get_events();
        $sink->close();

        $this->assertNull($result);

        $queuedevents = array_values(array_filter(
            $events,
            static fn ($event) => $event instanceof \local_monlaututoria\event\csv_import_queued
        ));
        $this->assertCount(1, $queuedevents);
        $this->assertSame($preview->operationid, (int) $queuedevents[0]->objectid);
        $this->assertSame($rowcount, $queuedevents[0]->other['totalrows']);

        $tasks = \core\task\manager::get_adhoc_tasks(process_csv_import_task::class);
        $this->assertCount(1, $tasks);
        $customdata = $tasks[0]->get_custom_data();
        $this->assertSame($preview->operationid, (int) $customdata->operationid);
        $this->assertSame(get_admin()->id, (int) $customdata->userid);

        // The operation itself is still PREVIEWED — process_csv_import_task
        // is the one that will move it to PROCESSING/terminal, exactly like
        // the synchronous path does.
        $bulkoperationrepository = new bulk_operation_repository();
        $operation = $bulkoperationrepository->get($preview->operationid);
        $this->assertSame(bulk_operation_status::PREVIEWED, $operation->status);

        $parameters = json_decode($operation->parametersjson, true);
        $this->assertSame(csv_import_apply_strategy::ATOMIC_ALL, $parameters['strategy']);
        $this->assertTrue($parameters['allowreassignconflicts']);

        $files = get_file_storage()->get_area_files(
            \context_system::instance()->id, 'local_monlaututoria', 'csvimport', $preview->operationid, 'id', false
        );
        $this->assertCount(1, $files);
    }

    public function test_large_import_already_applied_is_rejected(): void {
        $this->resetAfterTest();

        $year = $this->create_academic_year();
        $rowcount = csv_import_dispatch_service::LARGE_IMPORT_THRESHOLD + 1;
        ['content' => $content, 'draftitemid' => $draftitemid] = $this->build_large_csv($rowcount, $year);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $bulkoperationrepository = new bulk_operation_repository();
        $bulkoperationrepository->update_status($preview->operationid, bulk_operation_status::COMPLETED);

        $dispatchservice = new csv_import_dispatch_service();

        $this->expectException(\moodle_exception::class);
        $dispatchservice->dispatch(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id, false, $draftitemid
        );
    }
}
