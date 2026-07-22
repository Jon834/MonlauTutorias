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

use local_monlaututoria\domain\assignment;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\assignment_status;

/**
 * Data access for local_tut_assignment. No business rules here, only DML.
 *
 * Distinguishes "active" (status=active, regardless of dates) from "current"
 * a.k.a. "vigente" (status=active AND now falls inside [timestart, timeend)):
 * duplicate checks must use the former, access checks (scope_service) the
 * latter. See docs/modelo-datos.md.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignment_repository {

    /** @var string */
    private const TABLE = 'local_tut_assignment';

    /**
     * Inserts a new assignment and returns its id.
     *
     * @param \stdClass $data must contain studentid, tutorid, academicyearid, createdby;
     *                        may contain cohortid, assignmenttype, isprimary, status, timestart,
     *                        timeend, source, externalid
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->studentid = (int) $data->studentid;
        $record->tutorid = (int) $data->tutorid;
        $record->cohortid = isset($data->cohortid) ? (int) $data->cohortid : null;
        $record->academicyearid = (int) $data->academicyearid;
        $record->assignmenttype = $data->assignmenttype ?? assignment_type::PRIMARY;
        $record->isprimary = !empty($data->isprimary) ? 1 : 0;
        $record->status = $data->status ?? assignment_status::ACTIVE;
        $record->timestart = isset($data->timestart) ? (int) $data->timestart : time();
        $record->timeend = isset($data->timeend) ? (int) $data->timeend : null;
        $record->source = $data->source ?? \local_monlaututoria\domain\assignment_source::MANUAL;
        $record->externalid = $data->externalid ?? null;
        $record->createdby = (int) $data->createdby;
        $record->modifiedby = (int) $data->createdby;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Returns the raw record for an assignment, or throws if missing.
     *
     * @param int $id
     * @return \stdClass
     */
    public function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * Returns a typed DTO for an assignment.
     *
     * @param int $id
     * @return assignment
     */
    public function get_dto(int $id): assignment {
        return assignment::from_record($this->get($id));
    }

    /**
     * Closes an assignment (status=closed, timeend set). Callers are
     * responsible for enforcing business guards before calling this.
     *
     * @param int $id
     * @param int $modifiedby
     * @param int $timeend
     * @return bool
     */
    public function close(int $id, int $modifiedby, int $timeend): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = assignment_status::CLOSED;
        $record->timeend = $timeend;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * @param int $id
     * @param bool $isprimary
     * @param int $modifiedby
     */
    public function set_primary_flag(int $id, bool $isprimary, int $modifiedby): void {
        global $DB;

        $DB->set_field(self::TABLE, 'isprimary', $isprimary ? 1 : 0, ['id' => $id]);
        $DB->set_field(self::TABLE, 'modifiedby', $modifiedby, ['id' => $id]);
        $DB->set_field(self::TABLE, 'timemodified', time(), ['id' => $id]);
    }

    /**
     * @param int $studentid
     * @param int|null $academicyearid
     * @return \stdClass[]
     */
    public function find_by_student(int $studentid, ?int $academicyearid = null): array {
        global $DB;

        $conditions = ['studentid' => $studentid];
        if ($academicyearid !== null) {
            $conditions['academicyearid'] = $academicyearid;
        }

        return $DB->get_records(self::TABLE, $conditions, 'timestart ASC');
    }

    /**
     * @param int $tutorid
     * @param int|null $academicyearid
     * @return \stdClass[]
     */
    public function find_by_tutor(int $tutorid, ?int $academicyearid = null): array {
        global $DB;

        $conditions = ['tutorid' => $tutorid];
        if ($academicyearid !== null) {
            $conditions['academicyearid'] = $academicyearid;
        }

        return $DB->get_records(self::TABLE, $conditions, 'timestart ASC');
    }

    /**
     * @param int $cohortid
     * @return \stdClass[]
     */
    public function find_by_cohort(int $cohortid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['cohortid' => $cohortid], 'timestart ASC');
    }

    /**
     * Assignments with status=active, regardless of the timestart/timeend window.
     *
     * @param int $studentid
     * @param int|null $academicyearid
     * @return \stdClass[]
     */
    public function find_active(int $studentid, ?int $academicyearid = null): array {
        global $DB;

        $conditions = ['studentid' => $studentid, 'status' => assignment_status::ACTIVE];
        if ($academicyearid !== null) {
            $conditions['academicyearid'] = $academicyearid;
        }

        return $DB->get_records(self::TABLE, $conditions, 'timestart ASC');
    }

    /**
     * Assignments that are "vigente": status=active AND now falls inside
     * [timestart, timeend). This is what grants access, not find_active().
     *
     * @param int $studentid
     * @param int|null $academicyearid
     * @param int|null $now defaults to time(), injectable for tests
     * @return \stdClass[]
     */
    public function find_current(int $studentid, ?int $academicyearid = null, ?int $now = null): array {
        global $DB;

        $now = $now ?? time();
        $params = ['studentid' => $studentid, 'status' => assignment_status::ACTIVE, 'now1' => $now, 'now2' => $now];
        $sql = 'studentid = :studentid AND status = :status AND timestart <= :now1 '
            . 'AND (timeend IS NULL OR timeend > :now2)';

        if ($academicyearid !== null) {
            $sql .= ' AND academicyearid = :academicyearid';
            $params['academicyearid'] = $academicyearid;
        }

        return $DB->get_records_select(self::TABLE, $sql, $params, 'timestart ASC');
    }

    /**
     * Assignments that are not currently active: closed, cancelled, pending,
     * or active but outside their time window.
     *
     * @param int $studentid
     * @param int|null $academicyearid
     * @return \stdClass[]
     */
    public function find_historical(int $studentid, ?int $academicyearid = null): array {
        global $DB;

        $conditions = ['studentid' => $studentid];
        if ($academicyearid !== null) {
            $conditions['academicyearid'] = $academicyearid;
        }

        return $DB->get_records(self::TABLE, $conditions, 'timestart ASC');
    }

    /**
     * Whether $tutorid is currently ("vigente") the primary tutor or co-tutor
     * of $studentid. Other assignment types (support/orientation/other) do
     * not count, by design (see docs/seguridad-permisos.md).
     *
     * @param int $tutorid
     * @param int $studentid
     * @param int|null $academicyearid
     * @param int|null $now
     * @return bool
     */
    public function is_current_tutor_of_student(
        int $tutorid,
        int $studentid,
        ?int $academicyearid = null,
        ?int $now = null
    ): bool {
        global $DB;

        $now = $now ?? time();
        [$typesql, $typeparams] = $DB->get_in_or_equal(
            [assignment_type::PRIMARY, assignment_type::CO_TUTOR],
            SQL_PARAMS_NAMED,
            'type'
        );

        $params = array_merge(
            ['tutorid' => $tutorid, 'studentid' => $studentid, 'status' => assignment_status::ACTIVE, 'now1' => $now, 'now2' => $now],
            $typeparams
        );
        $sql = 'tutorid = :tutorid AND studentid = :studentid AND status = :status '
            . "AND assignmenttype $typesql AND timestart <= :now1 AND (timeend IS NULL OR timeend > :now2)";

        if ($academicyearid !== null) {
            $sql .= ' AND academicyearid = :academicyearid';
            $params['academicyearid'] = $academicyearid;
        }

        return $DB->record_exists_select(self::TABLE, $sql, $params);
    }

    /**
     * Whether $tutorid has EVER been the primary tutor or co-tutor of
     * $studentid (any status, any time window) — the check behind
     * local/monlaututoria:viewhistoricalassignments.
     *
     * @param int $tutorid
     * @param int $studentid
     * @param int|null $academicyearid
     * @return bool
     */
    public function has_historical_relationship(int $tutorid, int $studentid, ?int $academicyearid = null): bool {
        global $DB;

        [$typesql, $typeparams] = $DB->get_in_or_equal(
            [assignment_type::PRIMARY, assignment_type::CO_TUTOR],
            SQL_PARAMS_NAMED,
            'type'
        );

        $params = array_merge(['tutorid' => $tutorid, 'studentid' => $studentid], $typeparams);
        $sql = "tutorid = :tutorid AND studentid = :studentid AND assignmenttype $typesql";

        if ($academicyearid !== null) {
            $sql .= ' AND academicyearid = :academicyearid';
            $params['academicyearid'] = $academicyearid;
        }

        return $DB->record_exists_select(self::TABLE, $sql, $params);
    }

    /**
     * Whether an active row with the exact same (studentid, tutorid,
     * academicyearid, assignmenttype) already exists.
     *
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @param string $assignmenttype
     * @return bool
     */
    public function has_active_duplicate(int $studentid, int $tutorid, int $academicyearid, string $assignmenttype): bool {
        global $DB;

        return $DB->record_exists(self::TABLE, [
            'studentid'      => $studentid,
            'tutorid'        => $tutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => $assignmenttype,
            'status'         => assignment_status::ACTIVE,
        ]);
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @param int|null $excludeid
     * @return int
     */
    public function count_active_primary(int $studentid, int $academicyearid, ?int $excludeid = null): int {
        global $DB;

        $params = [
            'studentid'      => $studentid,
            'academicyearid' => $academicyearid,
            'status'         => assignment_status::ACTIVE,
            'isprimary'      => 1,
        ];
        $sql = 'studentid = :studentid AND academicyearid = :academicyearid '
            . 'AND status = :status AND isprimary = :isprimary';

        if ($excludeid !== null) {
            $sql .= ' AND id <> :excludeid';
            $params['excludeid'] = $excludeid;
        }

        return $DB->count_records_select(self::TABLE, $sql, $params);
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @return \stdClass|null
     */
    public function find_active_primary(int $studentid, int $academicyearid): ?\stdClass {
        global $DB;

        $record = $DB->get_record(self::TABLE, [
            'studentid'      => $studentid,
            'academicyearid' => $academicyearid,
            'status'         => assignment_status::ACTIVE,
            'isprimary'      => 1,
        ]);

        return $record !== false ? $record : null;
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @return \stdClass[]
     */
    public function find_active_cotutors(int $studentid, int $academicyearid): array {
        global $DB;

        return $DB->get_records(self::TABLE, [
            'studentid'      => $studentid,
            'academicyearid' => $academicyearid,
            'status'         => assignment_status::ACTIVE,
            'assignmenttype' => assignment_type::CO_TUTOR,
        ]);
    }
}
