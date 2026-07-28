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

use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\assignment_source;
use local_monlaututoria\domain\assignment_close_reason;
use local_monlaututoria\domain\assignment_reassign_reason;
use local_monlaututoria\domain\reassign_assignment_command;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\cohort_assignment_action;
use local_monlaututoria\domain\cohort_assignment_apply_result;
use local_monlaututoria\domain\cohort_assignment_apply_result_row;
use local_monlaututoria\domain\cohort_assignment_command;
use local_monlaututoria\domain\cohort_assignment_item;
use local_monlaututoria\domain\cohort_sync_mode;
use local_monlaututoria\event\cohort_assignment_applied;
use local_monlaututoria\event\cohort_assignment_apply_failed;

/**
 * Applies a previously previewed cohort-based bulk assignment operation —
 * the "confirm" step cohort_assignment_preview_service's own docblock
 * names as phases 3C.3-3C.5, built now as one increment rather than split
 * further, since preview without a way to confirm it is not a usable
 * feature on its own.
 *
 * Never trusts the stored preview blindly: recomputes the classification
 * from the operation's own stored parameters (cohort_assignment_preview_
 * service::command_from_operation() + ::classify(), the same pair
 * has_changed_since_preview() already uses) and refuses to apply if
 * anything has drifted since the preview was generated — same "recompute,
 * don't trust a snapshot" principle as csv_import_apply_service. An
 * operation can only be applied once — its status must still be PREVIEWED,
 * enforced via bulk_operation_repository::claim()'s atomic compare-and-swap
 * (phase 3E.3), not a plain read-then-write.
 *
 * Unlike csv_import_apply_service, there is no atomic_all/partial strategy
 * choice: the whole operation always applies inside one transaction. A
 * cohort assignment run is a single deliberate action a coordinator just
 * reviewed in full on the preview screen — a partially-applied cohort would
 * be confusing to reconcile, and the population here (one cohort) is small
 * enough that an all-or-nothing rollback is the safer default.
 *
 * Reuses assignment_service::create()/reassign_primary_tutor()/close()
 * rather than writing to the repository directly, same layering as every
 * other write path in this plugin.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_service {

    /** @var bulk_operation_repository */
    private $bulkoperationrepository;

    /** @var assignment_service */
    private $assignmentservice;

    /** @var cohort_assignment_preview_service */
    private $previewservice;

    public function __construct(
        ?bulk_operation_repository $bulkoperationrepository = null,
        ?assignment_service $assignmentservice = null,
        ?cohort_assignment_preview_service $previewservice = null
    ) {
        $this->bulkoperationrepository = $bulkoperationrepository ?? new bulk_operation_repository();
        $this->assignmentservice = $assignmentservice ?? new assignment_service();
        $this->previewservice = $previewservice ?? new cohort_assignment_preview_service();
    }

    /**
     * @param string $operationuuid a previewed (not yet applied) operation
     * @param int $userid
     * @return cohort_assignment_apply_result
     */
    public function apply(string $operationuuid, int $userid): cohort_assignment_apply_result {
        $operation = $this->bulkoperationrepository->get_by_uuid($operationuuid);
        if ($operation->operationtype !== 'cohort_assignment') {
            throw new \moodle_exception('error_cohort_operation_not_usable', 'local_monlaututoria');
        }
        if ($operation->status !== bulk_operation_status::PREVIEWED) {
            throw new \moodle_exception('error_cohort_already_applied', 'local_monlaututoria');
        }
        if ($operation->mode === cohort_sync_mode::PREVIEW_ONLY) {
            // cohort_sync_mode::PREVIEW_ONLY's own docblock: "never writes" —
            // an operation previewed in this mode is not a draft of ADD_ONLY,
            // it is a deliberate look-without-touching request, honoured here
            // rather than silently treated as equivalent to ADD_ONLY just
            // because classify() happens to branch the same way for both.
            throw new \moodle_exception('error_cohort_mode_preview_only_cannot_apply', 'local_monlaututoria');
        }

        if ($this->previewservice->has_changed_since_preview($operationuuid)) {
            throw new \moodle_exception('error_cohort_preview_changed', 'local_monlaututoria');
        }

        $command = $this->previewservice->command_from_operation($operation);
        [, $items] = $this->previewservice->classify($command);

        // Atomic compare-and-swap, not a plain write: has_changed_since_preview()
        // just above can take long enough (re-queries cohort membership and
        // every student's assignments) for two concurrent "Confirmar" clicks on
        // the same operation to both pass the PREVIEWED check above and both
        // reach this point. Only one may actually claim the transition to
        // PROCESSING; the other is rejected here, before either has written a
        // single real assignment — same guard as csv_import_apply_service.
        if (!$this->bulkoperationrepository->claim($operation->id, bulk_operation_status::PREVIEWED, bulk_operation_status::PROCESSING)) {
            throw new \moodle_exception('error_cohort_already_applied', 'local_monlaututoria');
        }

        try {
            $rows = $this->apply_all($items, $command, $userid);
        } catch (cohort_assignment_apply_failure $e) {
            // By the time we get here, apply_all()'s own delegated transaction
            // has already gone out of scope and rolled back (its local
            // $transaction variable was destroyed unwinding out of that
            // method) — this update_status() call is therefore a fresh write,
            // not one that would be undone by that same rollback. Doing this
            // inside apply_all() itself, before its transaction unwinds, would
            // silently discard the status change along with everything else.
            $this->bulkoperationrepository->update_status($operation->id, bulk_operation_status::FAILED);
            cohort_assignment_apply_failed::create_from_operation($operation->id, $userid, $e->studentid)->trigger();

            throw new \moodle_exception('error_cohort_apply_row_failed', 'local_monlaututoria');
        }

        $createdcount = $this->count_outcome($rows, cohort_assignment_action::CREATE_PRIMARY);
        $reassignedcount = $this->count_outcome($rows, cohort_assignment_action::REASSIGN_PRIMARY);
        $closedcount = $this->count_outcome($rows, cohort_assignment_action::CLOSE_MISSING);

        $this->bulkoperationrepository->update_status($operation->id, bulk_operation_status::COMPLETED);
        cohort_assignment_applied::create_from_operation($operation->id, $userid, $createdcount, $reassignedcount, $closedcount)->trigger();

        return new cohort_assignment_apply_result($operationuuid, bulk_operation_status::COMPLETED, $rows);
    }

    /**
     * @param cohort_assignment_item[] $items
     * @param cohort_assignment_command $command
     * @param int $userid
     * @return cohort_assignment_apply_result_row[]
     */
    private function apply_all(array $items, cohort_assignment_command $command, int $userid): array {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $rows = [];

        foreach ($items as $item) {
            try {
                $rows[] = $this->apply_item($item, $command, $userid);
            } catch (\Throwable $e) {
                throw new cohort_assignment_apply_failure($item->studentid);
            }
        }

        $transaction->allow_commit();

        return $rows;
    }

    /**
     * @param cohort_assignment_item $item
     * @param cohort_assignment_command $command
     * @param int $userid
     * @return cohort_assignment_apply_result_row
     */
    private function apply_item(cohort_assignment_item $item, cohort_assignment_command $command, int $userid): cohort_assignment_apply_result_row {
        $primaryassignmentid = null;

        switch ($item->action) {
            case cohort_assignment_action::CREATE_PRIMARY:
                $primaryassignmentid = $this->assignmentservice->create((object) [
                    'studentid'      => $item->studentid,
                    'tutorid'        => $command->primarytutorid,
                    'academicyearid' => $command->academicyearid,
                    'cohortid'       => $command->cohortid,
                    'assignmenttype' => assignment_type::PRIMARY,
                    'isprimary'      => true,
                    'timestart'      => $command->timestart ?? time(),
                    'timeend'        => $command->timeend,
                    'source'         => assignment_source::COHORT,
                    // Suspension was already vetted by classify() (this item
                    // would be SKIP_SUSPENDED otherwise) — true here just
                    // avoids create() re-litigating a decision preview already
                    // made under $command->includesuspended/allowsuspendedtutor.
                ], $userid, true, $command->canoverridelock);
                break;

            case cohort_assignment_action::REASSIGN_PRIMARY:
                $result = $this->assignmentservice->reassign_primary_tutor(
                    new reassign_assignment_command(
                        $item->studentid,
                        $command->primarytutorid,
                        $command->academicyearid,
                        assignment_reassign_reason::REORGANIZATION,
                        $command->timestart,
                        true,
                        true,
                        $command->canoverridelock
                    ),
                    $userid
                );
                $primaryassignmentid = $result->newassignmentid;
                break;

            case cohort_assignment_action::CLOSE_MISSING:
                $this->assignmentservice->close(
                    (int) $item->currentprimaryassignmentid,
                    $userid,
                    assignment_close_reason::STUDENT_LEFT
                );
                $primaryassignmentid = $item->currentprimaryassignmentid;
                break;

            // NO_CHANGE, SKIP_EXISTING, SKIP_SUSPENDED, SKIP_INVALID,
            // CONFLICT_PRIMARY: nothing to write, reported as-is.
        }

        $cotutorassignmentid = null;
        if ($item->cotutoraction === cohort_assignment_action::CREATE_COTUTOR) {
            $cotutorassignmentid = $this->assignmentservice->create((object) [
                'studentid'      => $item->studentid,
                'tutorid'        => $command->cotutorid,
                'academicyearid' => $command->academicyearid,
                'cohortid'       => $command->cohortid,
                'assignmenttype' => assignment_type::CO_TUTOR,
                'isprimary'      => false,
                'timestart'      => $command->timestart ?? time(),
                'timeend'        => $command->timeend,
                'source'         => assignment_source::COHORT,
            ], $userid, true, $command->canoverridelock);
        }

        return new cohort_assignment_apply_result_row(
            $item->studentid,
            $item->action,
            $primaryassignmentid,
            $item->cotutoraction,
            $cotutorassignmentid
        );
    }

    /**
     * @param cohort_assignment_apply_result_row[] $rows
     * @param string $outcome
     * @return int
     */
    private function count_outcome(array $rows, string $outcome): int {
        return count(array_filter($rows, static fn (cohort_assignment_apply_result_row $row) => $row->outcome === $outcome));
    }
}
