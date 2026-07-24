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
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\service\csv_import_apply_service;
use local_monlaututoria\event\csv_import_failed;

/**
 * Ad hoc task that applies a large CSV import (phase 3D.4) in the background,
 * queued by csv_import_dispatch_service instead of running the whole
 * apply_partial()/apply_atomic() loop inline in the HTTP request.
 *
 * Reuses csv_import_apply_service::apply() unchanged — this task is only an
 * asynchronous wrapper around the exact same call the synchronous path makes,
 * so every safety property already built into that service (never trust the
 * stored preview, idempotent status guard, strategies, events) applies here
 * without any duplication.
 *
 * The file this task needs was copied, at dispatch time, from the user's
 * draft area (which would not reliably survive until this task runs) into
 * this plugin's own file area (component=local_monlaututoria,
 * filearea=csvimport, itemid=operation id, system context) — the only place
 * in this plugin that stores an uploaded file server-side, and only for the
 * short window between dispatch and this task running. It is deleted here
 * as soon as processing finishes (success or failure); if the task never
 * runs, cleanup_bulk_operations_task deletes it as an orphan.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class process_csv_import_task extends \core\task\adhoc_task {

    public function get_name() {
        return get_string('task_process_csv_import', 'local_monlaututoria');
    }

    public function execute() {
        $data = $this->get_custom_data();
        $operationid = (int) $data->operationid;
        $userid = (int) $data->userid;

        $bulkoperationrepository = new bulk_operation_repository();
        $operation = $bulkoperationrepository->get($operationid);

        // Already applied/cancelled by the time this task runs (e.g. the
        // administrator re-uploaded and re-applied manually first): nothing
        // to do, apply() itself would reject it anyway.
        if ($operation->status !== bulk_operation_status::PREVIEWED) {
            return;
        }

        $fs = get_file_storage();
        $syscontext = \context_system::instance();
        $files = $fs->get_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', $operationid, 'id', false);
        $file = reset($files);

        if (!$file) {
            $bulkoperationrepository->update_status($operationid, bulk_operation_status::FAILED);
            // Same event every other FAILED transition fires (phase 3E.5) —
            // this path used to leave the operation FAILED with no audit
            // trail explaining why, unlike apply()'s own atomic_all rollback.
            csv_import_failed::create_from_operation($operationid, $userid, null)->trigger();

            return;
        }

        $parameters = json_decode($operation->parametersjson ?? '{}', true) ?: [];

        try {
            $content = $file->get_content();
            $applyservice = new csv_import_apply_service();
            $applyservice->apply(
                $operation->operationuuid,
                $content,
                $parameters['delimiter'] ?? ',',
                $parameters['encoding'] ?? 'UTF-8',
                $parameters['strategy'] ?? csv_import_apply_strategy::PARTIAL_VALID,
                $userid,
                !empty($parameters['allowreassignconflicts'])
            );
        } finally {
            $fs->delete_area_files($syscontext->id, 'local_monlaututoria', 'csvimport', $operationid);
        }
    }
}
