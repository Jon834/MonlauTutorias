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

use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\repository\bulk_operation_repository;

/**
 * Scheduled task (phase 3D.4, retention policy added in 3E.6) that purges
 * three kinds of state this plugin's bulk-operation flows (cohort
 * assignment, CSV import) can leave behind:
 *
 * 1. Abandoned operations: rows still in DRAFT/PREVIEWED after
 *    ABANDONED_TTL_SECONDS — a preview the administrator generated and then
 *    never applied. These envelope rows were only ever scaffolding for a
 *    preview/apply round trip (see bulk_operation_repository's class
 *    docblock); once that window has passed, keeping them serves no purpose.
 * 2. Finished operations: rows in a terminal status (completed/
 *    completed_with_errors/failed/cancelled) after TERMINAL_TTL_SECONDS (90
 *    days) — the retention policy decided for phase 3E.6 (see the privacy
 *    provider's class docblock). Only aggregate counts are ever lost here,
 *    never per-student data (see cohort_assignment_preview_service's class
 *    docblock) — the real personal data lives in local_tut_assignment, kept
 *    indefinitely and handled by anonymisation on erasure instead of a TTL.
 * 3. Orphaned csvimport files: files copied into this plugin's own file area
 *    by csv_import_dispatch_service for a deferred large import, left behind
 *    because the ad hoc task already finished (it deletes its own file in a
 *    finally block, so this only catches the task never having run, or the
 *    process crashing before that block) or because the owning operation row
 *    no longer exists.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cleanup_bulk_operations_task extends \core\task\scheduled_task {

    /** @var int draft/previewed operations older than this are purged */
    public const ABANDONED_TTL_SECONDS = 86400;

    /** @var int completed/completed_with_errors/failed/cancelled operations older than this are purged */
    public const TERMINAL_TTL_SECONDS = 90 * 86400;

    public function get_name() {
        return get_string('task_cleanup_bulk_operations', 'local_monlaututoria');
    }

    public function execute() {
        $bulkoperationrepository = new bulk_operation_repository();
        $fs = get_file_storage();
        $syscontext = \context_system::instance();

        $abandoned = $bulkoperationrepository->get_older_than(
            self::ABANDONED_TTL_SECONDS,
            [bulk_operation_status::DRAFT, bulk_operation_status::PREVIEWED]
        );
        foreach ($abandoned as $operation) {
            $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', (int) $operation->id);
            $bulkoperationrepository->delete((int) $operation->id);
        }

        $finished = $bulkoperationrepository->get_older_than(
            self::TERMINAL_TTL_SECONDS,
            [
                bulk_operation_status::COMPLETED,
                bulk_operation_status::COMPLETED_WITH_ERRORS,
                bulk_operation_status::FAILED,
                bulk_operation_status::CANCELLED,
            ]
        );
        foreach ($finished as $operation) {
            $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', (int) $operation->id);
            $bulkoperationrepository->delete((int) $operation->id);
        }

        $this->delete_orphaned_files($bulkoperationrepository, $fs, $syscontext);
    }

    /**
     * @param bulk_operation_repository $bulkoperationrepository
     * @param \file_storage $fs
     * @param \context_system $syscontext
     * @return void
     */
    private function delete_orphaned_files(
        bulk_operation_repository $bulkoperationrepository,
        \file_storage $fs,
        \context $syscontext
    ): void {
        $areafiles = $fs->get_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', false, 'itemid', false);

        $itemids = [];
        foreach ($areafiles as $file) {
            $itemids[$file->get_itemid()] = true;
        }

        foreach (array_keys($itemids) as $operationid) {
            $operationid = (int) $operationid;
            try {
                $operation = $bulkoperationrepository->get($operationid);
            } catch (\dml_missing_record_exception $e) {
                $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', $operationid);
                continue;
            }

            // Any status other than PREVIEWED means the operation was
            // already processed (or purged above) — the file has no further
            // purpose left, whether the task's own cleanup ran or not.
            if ($operation->status !== bulk_operation_status::PREVIEWED) {
                $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', $operationid);
            }
        }
    }
}
