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
 * Data access for local_tut_bulkoperation. No business rules here, only DML.
 * Deliberately holds no per-student data — see
 * cohort_assignment_preview_service's class docblock for why.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_operation_repository {

    /** @var string */
    private const TABLE = 'local_tut_bulkoperation';

    /**
     * cohortid/academicyearid/primarytutorid/mode are only meaningful for
     * operationtype=cohort_assignment (phase 3C.1); operationtype=csv_import
     * (phase 3D.2) leaves them null, since a CSV import has no single
     * cohort/year/tutor — each row may specify its own.
     *
     * @param \stdClass $data must contain operationuuid, createdby; may contain
     *                        cohortid, academicyearid, primarytutorid, cotutorid,
     *                        mode, operationtype, status, parametersjson, summaryjson
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->operationuuid = $data->operationuuid;
        $record->operationtype = $data->operationtype ?? 'cohort_assignment';
        $record->cohortid = isset($data->cohortid) ? (int) $data->cohortid : null;
        $record->academicyearid = isset($data->academicyearid) ? (int) $data->academicyearid : null;
        $record->primarytutorid = isset($data->primarytutorid) ? (int) $data->primarytutorid : null;
        $record->cotutorid = isset($data->cotutorid) ? (int) $data->cotutorid : null;
        $record->mode = $data->mode ?? null;
        $record->status = $data->status ?? \local_monlaututoria\domain\bulk_operation_status::DRAFT;
        $record->parametersjson = $data->parametersjson ?? null;
        $record->summaryjson = $data->summaryjson ?? null;
        $record->createdby = (int) $data->createdby;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * @param int $id
     * @return \stdClass
     */
    public function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * @param string $operationuuid
     * @return \stdClass
     */
    public function get_by_uuid(string $operationuuid): \stdClass {
        global $DB;

        return $DB->get_record(self::TABLE, ['operationuuid' => $operationuuid], '*', MUST_EXIST);
    }

    /**
     * @param int $id
     * @param string $status one of bulk_operation_status::values()
     * @return bool
     */
    public function update_status(int $id, string $status): bool {
        global $DB;

        return $DB->set_field(self::TABLE, 'status', $status, ['id' => $id])
            && $DB->set_field(self::TABLE, 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Atomic compare-and-swap for the one status transition that genuinely
     * needs it (phase 3E.3): moving a previewed CSV import to "processing".
     * Unlike update_status() (an unconditional write, safe everywhere else
     * because by the time it runs the caller already holds an exclusive
     * claim), this re-reads the row from the database immediately before
     * writing and only transitions it if its status still matches
     * $fromstatus — closing the window where two concurrent "Apply" clicks
     * (or a retried request) could both pass an earlier, separate status
     * check and both proceed to write duplicate assignments. Same
     * best-effort philosophy already documented for
     * assignment_service::reassign_primary_tutor(): Moodle DML has no
     * portable row-level locking, so this narrows the race window rather
     * than eliminating it outright.
     *
     * @param int $id
     * @param string $fromstatus required current status for the transition to happen
     * @param string $tostatus new status, only written if $fromstatus still matches
     * @return bool true if this call performed the transition, false if the
     *              row's status no longer matched $fromstatus (someone else
     *              already claimed or changed it)
     */
    public function claim(int $id, string $fromstatus, string $tostatus): bool {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        $record = $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
        if ($record->status !== $fromstatus) {
            $transaction->allow_commit();

            return false;
        }

        $record->status = $tostatus;
        $record->timemodified = time();
        $DB->update_record(self::TABLE, $record);

        $transaction->allow_commit();

        return true;
    }

    /**
     * Whether the operation identified by $operationuuid is older than
     * $ttlseconds. Shared by every bulk-operation preview service
     * (cohort-based and CSV import) so the "how stale is too stale" rule
     * lives in one place.
     *
     * @param string $operationuuid
     * @param int $ttlseconds
     * @return bool
     */
    public function is_expired(string $operationuuid, int $ttlseconds): bool {
        $operation = $this->get_by_uuid($operationuuid);

        return (time() - (int) $operation->timecreated) > $ttlseconds;
    }

    /**
     * @param int $id
     * @param string $summaryjson
     * @return bool
     */
    public function update_summary(int $id, string $summaryjson): bool {
        global $DB;

        $record = $this->get($id);
        $record->summaryjson = $summaryjson;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Used by csv_import_dispatch_service (phase 3D.4) to record the apply
     * parameters (strategy, allowreassignconflicts) on the operation itself
     * when execution is deferred to an ad hoc task, since the task runs later
     * with no HTTP request context to read them from.
     *
     * @param int $id
     * @param string $parametersjson
     * @return bool
     */
    public function update_parameters(int $id, string $parametersjson): bool {
        global $DB;

        $record = $this->get($id);
        $record->parametersjson = $parametersjson;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Operations older than $ttlseconds, used by cleanup_bulk_operations_task
     * (phase 3D.4) to purge abandoned draft/previewed operations and
     * old terminal ones. Ordered oldest first purely for predictable test
     * assertions; the task itself does not rely on ordering.
     *
     * @param int $ttlseconds
     * @param string[] $statuses
     * @return \stdClass[]
     */
    public function get_older_than(int $ttlseconds, array $statuses): array {
        global $DB;

        if (empty($statuses)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED);
        $params['cutoff'] = time() - $ttlseconds;

        return $DB->get_records_select(
            self::TABLE,
            "status {$insql} AND timecreated < :cutoff",
            $params,
            'timecreated ASC'
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        global $DB;

        return $DB->delete_records(self::TABLE, ['id' => $id]);
    }

    /**
     * RFC4122 v4 UUID, no external dependency. Shared by every bulk-operation
     * preview service so operationuuid generation lives in one place.
     *
     * @return string
     */
    public static function generate_uuid(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
