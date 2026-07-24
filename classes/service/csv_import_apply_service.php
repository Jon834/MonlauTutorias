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

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\csv_import_row_status;
use local_monlaututoria\domain\csv_import_message_code;
use local_monlaututoria\domain\csv_import_row_outcome;
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\domain\csv_import_apply_result_row;
use local_monlaututoria\domain\csv_import_apply_result;
use local_monlaututoria\domain\csv_import_preview_row;
use local_monlaututoria\domain\csv_import_preview_summary;
use local_monlaututoria\domain\assignment_source;
use local_monlaututoria\domain\reassign_assignment_command;
use local_monlaututoria\domain\assignment_reassign_reason;
use local_monlaututoria\event\csv_import_started;
use local_monlaututoria\event\csv_import_completed;
use local_monlaututoria\event\csv_import_completed_with_errors;
use local_monlaututoria\event\csv_import_failed;

/**
 * Applies a previously previewed CSV import (phase 3D.3): creates or
 * reassigns real local_tut_assignment rows for the rows a preview classified
 * as valid/warning/conflict, reusing assignment_service::create() and
 * ::reassign_primary_tutor() rather than writing to the repository directly.
 *
 * Never trusts the stored preview blindly: given the caller's file content
 * again, it always recomputes the classification and refuses to apply if it
 * has drifted from what was stored at preview time (same "recompute, don't
 * trust a snapshot" principle as cohort_assignment_preview_service). An
 * operation can only be applied once — its status must still be PREVIEWED.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_service {

    /** @var assignment_repository */
    private $assignmentrepository;

    /** @var bulk_operation_repository */
    private $bulkoperationrepository;

    /** @var assignment_service */
    private $assignmentservice;

    /** @var csv_import_preview_service */
    private $previewservice;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?bulk_operation_repository $bulkoperationrepository = null,
        ?assignment_service $assignmentservice = null,
        ?csv_import_preview_service $previewservice = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->bulkoperationrepository = $bulkoperationrepository ?? new bulk_operation_repository();
        $this->assignmentservice = $assignmentservice ?? new assignment_service($this->assignmentrepository);
        $this->previewservice = $previewservice ?? new csv_import_preview_service($this->assignmentrepository);
    }

    /**
     * @param string $operationuuid a previewed (not yet applied) operation
     * @param string $content the same file content the preview was generated from
     * @param string $delimiter
     * @param string $encoding
     * @param string $strategy one of csv_import_apply_strategy::values()
     * @param int $userid
     * @param bool $allowreassignconflicts if true, rows conflicting on an existing
     *                                     different primary tutor are reassigned via
     *                                     reassign_primary_tutor() instead of skipped
     * @return csv_import_apply_result
     */
    public function apply(
        string $operationuuid,
        string $content,
        string $delimiter,
        string $encoding,
        string $strategy,
        int $userid,
        bool $allowreassignconflicts = false
    ): csv_import_apply_result {
        global $DB;

        if (!in_array($strategy, csv_import_apply_strategy::values(), true)) {
            throw new \moodle_exception('error_csv_apply_strategy_invalid', 'local_monlaututoria');
        }

        $operation = $this->bulkoperationrepository->get_by_uuid($operationuuid);
        if ($operation->operationtype !== 'csv_import') {
            throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
        }
        if ($operation->status !== bulk_operation_status::PREVIEWED) {
            throw new \moodle_exception('error_csv_already_applied', 'local_monlaututoria');
        }

        $parameters = json_decode($operation->parametersjson ?? '{}', true) ?: [];
        $excludedrownumbers = $parameters['excludedrownumbers'] ?? [];
        $storedsummary = csv_import_preview_summary::from_array(json_decode($operation->summaryjson ?? '{}', true) ?: []);

        // Never trust the stored preview: recompute from the same content and
        // refuse to proceed if anything has changed since it was generated.
        // preview() persists its own tracking row as a side effect (harmless,
        // same as any other preview call) — the operation actually being
        // applied and whose status/events matter is $operation (the original
        // $operationuuid), never that fresh one; otherwise the "already
        // applied" guard above would silently stop protecting anything, since
        // it checks $operationuuid's status, not a different row's.
        $freshpreview = $this->previewservice->preview($content, $delimiter, $encoding, $userid, $excludedrownumbers);
        if ($freshpreview->summary->to_array() !== $storedsummary->to_array()) {
            throw new \moodle_exception('error_csv_preview_changed', 'local_monlaututoria');
        }

        // Atomic compare-and-swap, not a plain write: the preview() call just
        // above can take long enough (parses the whole file again, queries
        // the database per row) for two concurrent "Apply" requests on the
        // same operation to both pass the PREVIEWED check above and both
        // reach this point. Only one of them may actually claim the
        // transition to PROCESSING; the other is rejected here, before
        // either has written a single real assignment (phase 3E.3).
        if (!$this->bulkoperationrepository->claim($operation->id, bulk_operation_status::PREVIEWED, bulk_operation_status::PROCESSING)) {
            throw new \moodle_exception('error_csv_already_applied', 'local_monlaututoria');
        }
        csv_import_started::create_from_operation($operation->id, $userid, $strategy)->trigger();

        try {
            if ($strategy === csv_import_apply_strategy::ATOMIC_ALL) {
                $rows = $this->apply_atomic($freshpreview->rows, $userid, $allowreassignconflicts);
            } else {
                $rows = $this->apply_partial($freshpreview->rows, $userid, $allowreassignconflicts);
            }
        } catch (csv_import_atomic_failure $e) {
            $this->bulkoperationrepository->update_status($operation->id, bulk_operation_status::FAILED);
            csv_import_failed::create_from_operation($operation->id, $userid, $e->rownumber)->trigger();

            return new csv_import_apply_result($operationuuid, $strategy, bulk_operation_status::FAILED, []);
        }

        $createdcount = $this->count_outcome($rows, csv_import_row_outcome::CREATED);
        $reassignedcount = $this->count_outcome($rows, csv_import_row_outcome::REASSIGNED);
        $nochangecount = $this->count_outcome($rows, csv_import_row_outcome::NO_CHANGE);
        $errorcount = $this->count_outcome($rows, csv_import_row_outcome::FAILED);

        if ($errorcount > 0) {
            $finalstatus = bulk_operation_status::COMPLETED_WITH_ERRORS;
            $this->bulkoperationrepository->update_status($operation->id, $finalstatus);
            csv_import_completed_with_errors::create_from_operation(
                $operation->id,
                $userid,
                $createdcount,
                $reassignedcount,
                $errorcount
            )->trigger();
        } else {
            $finalstatus = bulk_operation_status::COMPLETED;
            $this->bulkoperationrepository->update_status($operation->id, $finalstatus);
            csv_import_completed::create_from_operation(
                $operation->id,
                $userid,
                $createdcount,
                $reassignedcount,
                $nochangecount
            )->trigger();
        }

        return new csv_import_apply_result($operationuuid, $strategy, $finalstatus, $rows);
    }

    /**
     * @param csv_import_preview_row[] $rows
     * @param int $userid
     * @param bool $allowreassignconflicts
     * @return csv_import_apply_result_row[]
     */
    private function apply_partial(array $rows, int $userid, bool $allowreassignconflicts): array {
        $results = [];
        foreach ($rows as $row) {
            try {
                $results[] = $this->apply_row($row, $userid, $allowreassignconflicts);
            } catch (\Throwable $e) {
                $results[] = new csv_import_apply_result_row(
                    $row->rownumber,
                    csv_import_row_outcome::FAILED,
                    null,
                    'error_csv_apply_row_failed',
                    $row->values
                );
            }
        }

        return $results;
    }

    /**
     * @param csv_import_preview_row[] $rows
     * @param int $userid
     * @param bool $allowreassignconflicts
     * @return csv_import_apply_result_row[]
     */
    private function apply_atomic(array $rows, int $userid, bool $allowreassignconflicts): array {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $results = [];

        foreach ($rows as $row) {
            try {
                $results[] = $this->apply_row($row, $userid, $allowreassignconflicts);
            } catch (\Throwable $e) {
                throw new csv_import_atomic_failure($row->rownumber);
            }
        }

        $transaction->allow_commit();

        return $results;
    }

    /**
     * @param csv_import_preview_row $row
     * @param int $userid
     * @param bool $allowreassignconflicts
     * @return csv_import_apply_result_row
     */
    private function apply_row(csv_import_preview_row $row, int $userid, bool $allowreassignconflicts): csv_import_apply_result_row {
        if ($row->status === csv_import_row_status::EXCLUDED) {
            return new csv_import_apply_result_row(
                $row->rownumber, csv_import_row_outcome::SKIPPED_EXCLUDED, null, null, $row->values
            );
        }
        if ($row->status === csv_import_row_status::ERROR) {
            return new csv_import_apply_result_row(
                $row->rownumber, csv_import_row_outcome::SKIPPED_ERROR, null, null, $row->values
            );
        }

        if ($row->status === csv_import_row_status::CONFLICT) {
            $isprimaryconflict = in_array(csv_import_message_code::PRIMARY_CONFLICT, $row->messagecodes, true);
            if (!$allowreassignconflicts || !$isprimaryconflict) {
                return new csv_import_apply_result_row(
                    $row->rownumber, csv_import_row_outcome::SKIPPED_CONFLICT, null, null, $row->values
                );
            }

            if ($this->assignmentrepository->has_active_duplicate(
                $row->studentid,
                $row->tutorid,
                $row->academicyearid,
                $row->assignmenttype
            )) {
                return new csv_import_apply_result_row(
                    $row->rownumber, csv_import_row_outcome::NO_CHANGE, null, null, $row->values
                );
            }

            $result = $this->assignmentservice->reassign_primary_tutor(
                new reassign_assignment_command(
                    $row->studentid,
                    $row->tutorid,
                    $row->academicyearid,
                    assignment_reassign_reason::OTHER
                ),
                $userid
            );

            return new csv_import_apply_result_row(
                $row->rownumber, csv_import_row_outcome::REASSIGNED, $result->newassignmentid, null, $row->values
            );
        }

        // VALID or WARNING.
        if ($this->assignmentrepository->has_active_duplicate(
            $row->studentid,
            $row->tutorid,
            $row->academicyearid,
            $row->assignmenttype
        )) {
            return new csv_import_apply_result_row(
                $row->rownumber, csv_import_row_outcome::NO_CHANGE, null, null, $row->values
            );
        }

        $newid = $this->assignmentservice->create((object) [
            'studentid'      => $row->studentid,
            'tutorid'        => $row->tutorid,
            'academicyearid' => $row->academicyearid,
            'cohortid'       => $row->cohortid,
            'assignmenttype' => $row->assignmenttype,
            'isprimary'      => $row->isprimary,
            'source'         => assignment_source::CSV,
        ], $userid);

        return new csv_import_apply_result_row(
            $row->rownumber, csv_import_row_outcome::CREATED, $newid, null, $row->values
        );
    }

    /**
     * @param csv_import_apply_result_row[] $rows
     * @param string $outcome
     * @return int
     */
    private function count_outcome(array $rows, string $outcome): int {
        return count(array_filter($rows, static fn (csv_import_apply_result_row $row) => $row->outcome === $outcome));
    }
}
