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
        $record->note = $data->note ?? null;
        $record->closereason = null;
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
     * $closereason and $note are only written when explicitly passed
     * (non-null): internal callers such as reassign() and remove_cotutor()
     * close a row without either, leaving those fields untouched.
     *
     * @param int $id
     * @param int $modifiedby
     * @param int $timeend
     * @param string|null $closereason one of assignment_close_reason::values()
     * @param string|null $note replaces the administrative note when provided
     * @return bool
     */
    public function close(
        int $id,
        int $modifiedby,
        int $timeend,
        ?string $closereason = null,
        ?string $note = null
    ): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = assignment_status::CLOSED;
        $record->timeend = $timeend;
        if ($closereason !== null) {
            $record->closereason = $closereason;
        }
        if ($note !== null) {
            $record->note = $note !== '' ? $note : null;
        }
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Updates only the editable fields of an assignment: cohortid, timestart,
     * timeend, note. Deliberately never reads or touches studentid, tutorid,
     * assignmenttype, isprimary or status from $data, even if present —
     * changing tutor is a reassignment (a separate flow) and changing status
     * is a close/cancel action (a separate flow), not a generic edit.
     *
     * @param int $id
     * @param \stdClass $data may contain cohortid, timestart, timeend, note
     * @param int $modifiedby
     * @return bool
     */
    public function update_editable_fields(int $id, \stdClass $data, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);

        if (property_exists($data, 'cohortid')) {
            $record->cohortid = !empty($data->cohortid) ? (int) $data->cohortid : null;
        }
        if (property_exists($data, 'timestart')) {
            $record->timestart = (int) $data->timestart;
        }
        if (property_exists($data, 'timeend')) {
            $record->timeend = !empty($data->timeend) ? (int) $data->timeend : null;
        }
        if (property_exists($data, 'note')) {
            $record->note = $data->note !== '' ? $data->note : null;
        }
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

    /**
     * All primary-type rows (any status) for a batch of students within one
     * academic year, in a single query — used by unassigned_students_service
     * to classify primary-tutor coverage without querying per student.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return \stdClass[] ordered by studentid, timestart DESC
     */
    public function find_primary_rows_for_students(array $studentids, int $academicyearid): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED, 'student');
        $params['academicyearid'] = $academicyearid;
        $params['assignmenttype'] = assignment_type::PRIMARY;
        $sql = "studentid $insql AND academicyearid = :academicyearid AND assignmenttype = :assignmenttype";

        return $DB->get_records_select(self::TABLE, $sql, $params, 'studentid ASC, timestart DESC');
    }

    /** @var string[] columns callers may sort search() results by */
    private const SORTABLE_COLUMNS = ['timestart', 'timeend', 'status', 'assignmenttype', 'source'];

    /**
     * Paginated, filterable listing of assignments for the administration
     * pages. Never joins against `user`/`cohort`: callers must batch-fetch
     * display data for the returned rows themselves (see
     * assignments/index.php) to avoid loading full user profiles here.
     *
     * @param array $filters optional keys: academicyearid, tutorid, studentid, cohortid,
     *                        assignmenttype, status, source, timestartfrom, timestartto,
     *                        timeendfrom, timeendto
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort must be one of self::SORTABLE_COLUMNS, silently falls back to
     *                     'timestart' otherwise (never interpolates an arbitrary value into SQL)
     * @param string $direction 'ASC' or 'DESC'
     * @return \stdClass[]
     */
    public function search(
        array $filters,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'timestart',
        string $direction = 'DESC'
    ): array {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        if (!in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'timestart';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $DB->get_records_select(self::TABLE, $sql, $params, "$sort $direction, id DESC", '*', $limitfrom, $limitnum);
    }

    /**
     * Total count of rows matching $filters, for pagination.
     *
     * @param array $filters see search()
     * @return int
     */
    public function count_search(array $filters): int {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        return $DB->count_records_select(self::TABLE, $sql, $params);
    }

    /**
     * Active co-tutor rows for a batch of students, in a single query. Callers
     * group the result by studentid themselves (see assignments/index.php) —
     * this avoids one query per row when rendering a page of results.
     *
     * @param int[] $studentids
     * @param int|null $academicyearid
     * @return \stdClass[]
     */
    public function get_cotutors_for_students(array $studentids, ?int $academicyearid = null): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $inparams] = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED, 'student');
        $params = array_merge(
            $inparams,
            ['status' => assignment_status::ACTIVE, 'assignmenttype' => assignment_type::CO_TUTOR]
        );
        $sql = "studentid $insql AND status = :status AND assignmenttype = :assignmenttype";

        if ($academicyearid !== null) {
            $sql .= ' AND academicyearid = :academicyearid';
            $params['academicyearid'] = $academicyearid;
        }

        return $DB->get_records_select(self::TABLE, $sql, $params);
    }

    /**
     * Builds the shared WHERE clause + params for search()/count_search(),
     * so both stay in sync without duplicating the filter logic.
     *
     * @param array $filters see search()
     * @return array{0: string, 1: array}
     */
    private function build_search_where(array $filters): array {
        $conditions = ['1 = 1'];
        $params = [];

        $equalityfilters = ['academicyearid', 'tutorid', 'studentid', 'cohortid', 'assignmenttype', 'status', 'source'];
        foreach ($equalityfilters as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $conditions[] = "$key = :$key";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['timestartfrom'])) {
            $conditions[] = 'timestart >= :timestartfrom';
            $params['timestartfrom'] = (int) $filters['timestartfrom'];
        }
        if (!empty($filters['timestartto'])) {
            $conditions[] = 'timestart <= :timestartto';
            $params['timestartto'] = (int) $filters['timestartto'];
        }
        if (!empty($filters['timeendfrom'])) {
            $conditions[] = 'timeend >= :timeendfrom';
            $params['timeendfrom'] = (int) $filters['timeendfrom'];
        }
        if (!empty($filters['timeendto'])) {
            $conditions[] = 'timeend <= :timeendto';
            $params['timeendto'] = (int) $filters['timeendto'];
        }

        return [implode(' AND ', $conditions), $params];
    }
}
