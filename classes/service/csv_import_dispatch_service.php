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

use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\csv_import_apply_result;
use local_monlaututoria\domain\csv_import_preview_summary;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\task\process_csv_import_task;
use local_monlaututoria\event\csv_import_queued;

/**
 * Decides, for a previewed CSV import, whether to apply it synchronously
 * (small files) or defer it to an ad hoc task (large files, phase 3D.4) —
 * the only place in the plugin that makes this decision, so
 * assignments/import.php stays a thin page.
 *
 * The decision uses the row count already stored on the operation at preview
 * time (summaryjson.totalrows), not a fresh re-parse: cheap, and the actual
 * content revalidation ("never trust the stored preview") still happens
 * inside csv_import_apply_service::apply() either way — synchronously here,
 * or inside process_csv_import_task when deferred.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_dispatch_service {

    /** @var int rows at or below this count are applied inline; above, deferred to a task */
    public const LARGE_IMPORT_THRESHOLD = 50;

    /** @var bulk_operation_repository */
    private $bulkoperationrepository;

    /** @var csv_import_apply_service */
    private $applyservice;

    public function __construct(
        ?bulk_operation_repository $bulkoperationrepository = null,
        ?csv_import_apply_service $applyservice = null
    ) {
        $this->bulkoperationrepository = $bulkoperationrepository ?? new bulk_operation_repository();
        $this->applyservice = $applyservice ?? new csv_import_apply_service();
    }

    /**
     * @param string $operationuuid a previewed (not yet applied) operation
     * @param string $content the same file content the preview was generated from
     * @param string $delimiter
     * @param string $encoding
     * @param string $strategy one of csv_import_apply_strategy::values()
     * @param int $userid
     * @param bool $allowreassignconflicts
     * @param int $draftitemid the uploader's current draft file area item id,
     *                          only read from when deferring to a task
     * @return csv_import_apply_result|null null means the import was queued
     *                                       for background processing instead
     *                                       of applied immediately
     */
    public function dispatch(
        string $operationuuid,
        string $content,
        string $delimiter,
        string $encoding,
        string $strategy,
        int $userid,
        bool $allowreassignconflicts,
        int $draftitemid
    ): ?csv_import_apply_result {
        $operation = $this->bulkoperationrepository->get_by_uuid($operationuuid);
        $storedsummary = csv_import_preview_summary::from_array(json_decode($operation->summaryjson ?? '{}', true) ?: []);

        if ($storedsummary->totalrows <= self::LARGE_IMPORT_THRESHOLD) {
            return $this->applyservice->apply(
                $operationuuid, $content, $delimiter, $encoding, $strategy, $userid, $allowreassignconflicts
            );
        }

        if ($operation->operationtype !== 'csv_import') {
            throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
        }
        if ($operation->status !== bulk_operation_status::PREVIEWED) {
            throw new \moodle_exception('error_csv_already_applied', 'local_monlaututoria');
        }

        $this->persist_file_for_task($operation->id, $draftitemid, $userid);

        $parameters = json_decode($operation->parametersjson ?? '{}', true) ?: [];
        $parameters['delimiter'] = $delimiter;
        $parameters['encoding'] = $encoding;
        $parameters['strategy'] = $strategy;
        $parameters['allowreassignconflicts'] = $allowreassignconflicts;
        $this->bulkoperationrepository->update_parameters($operation->id, json_encode($parameters));

        $task = new process_csv_import_task();
        $task->set_custom_data(['operationid' => $operation->id, 'userid' => $userid]);
        \core\task\manager::queue_adhoc_task($task);

        // Deferring is itself an audit-worthy moment (phase 3E.5): without
        // this, the only record of "who triggered this large import and
        // when" was csv_import_started, fired only once the ad hoc task
        // actually runs — which can happen much later, or never at all if
        // the task fails before it gets that far (e.g. the persisted file
        // goes missing).
        csv_import_queued::create_from_operation($operation->id, $userid, $storedsummary->totalrows)->trigger();

        return null;
    }

    /**
     * Copies the uploaded file from the user's draft area (self-cleaning,
     * but not guaranteed to survive until an ad hoc task runs) into this
     * plugin's own file area, keyed by operation id. Deleted by
     * process_csv_import_task right after processing, or by
     * cleanup_bulk_operations_task if abandoned.
     *
     * @param int $operationid
     * @param int $draftitemid
     * @param int $userid
     * @return void
     */
    private function persist_file_for_task(int $operationid, int $draftitemid, int $userid): void {
        $fs = get_file_storage();
        $usercontext = \context_user::instance($userid);
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
        $draftfile = reset($draftfiles);
        if (!$draftfile) {
            throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
        }

        $syscontext = \context_system::instance();
        $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', $operationid);
        $fs->create_file_from_storedfile([
            'contextid' => $syscontext->id,
            'component' => 'local_monlaututoria',
            'filearea'  => 'csvimport',
            'itemid'    => $operationid,
            'filepath'  => '/',
            'filename'  => $draftfile->get_filename(),
        ], $draftfile);
    }
}
