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

use local_monlaututoria\domain\entry_status;

/**
 * Data access for local_tut_entry. No business rules, no security here —
 * scope_service/entry_service resolve who may see what; this class returns
 * raw rows to whoever calls it, same layering as assignment_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_repository {

    /** @var string */
    private const TABLE = 'local_tut_entry';

    /** @var string[] columns callers may sort search() results by */
    private const SORTABLE_COLUMNS = ['entrydate', 'status', 'timecreated'];

    /**
     * @return string[]
     */
    public static function sortable_columns(): array {
        return self::SORTABLE_COLUMNS;
    }

    /**
     * Inserts a new tutoring entry and returns its id.
     *
     * @param \stdClass $data must contain studentid, tutorid, academicyearid, entrydate, createdby;
     *                        may contain modalityid, contentvisible, noteinternal, noterestricted,
     *                        nextfollowupdate
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->studentid = (int) $data->studentid;
        $record->tutorid = (int) $data->tutorid;
        $record->academicyearid = (int) $data->academicyearid;
        $record->entrydate = (int) $data->entrydate;
        $record->modalityid = isset($data->modalityid) ? (int) $data->modalityid : null;
        $record->contentvisible = $data->contentvisible ?? null;
        $record->noteinternal = $data->noteinternal ?? null;
        $record->noterestricted = $data->noterestricted ?? null;
        $record->entrykind = $data->entrykind ?? \local_monlaututoria\domain\entry_kind::REGULAR;
        $record->recommendationsop = $data->recommendationsop ?? null;
        $record->status = entry_status::ACTIVE;
        $record->nextfollowupdate = isset($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null;
        $record->createdby = (int) $data->createdby;
        $record->modifiedby = (int) $data->createdby;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Returns the raw record for a tutoring entry, or throws if missing.
     *
     * @param int $id
     * @return \stdClass
     */
    public function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * @param int[] $ids
     * @return \stdClass[] keyed by id
     */
    public function get_many(array $ids): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        return $DB->get_records_list(self::TABLE, 'id', array_unique(array_map('intval', $ids)));
    }

    /**
     * @param int $studentid
     * @param int|null $academicyearid
     * @return \stdClass[] ordered most recent entrydate first
     */
    public function find_by_student(int $studentid, ?int $academicyearid = null): array {
        global $DB;

        $conditions = ['studentid' => $studentid];
        if ($academicyearid !== null) {
            $conditions['academicyearid'] = $academicyearid;
        }

        return $DB->get_records(self::TABLE, $conditions, 'entrydate DESC, id DESC');
    }

    /**
     * Paginated, filterable listing — same shape as assignment_repository::search(),
     * used by the student ficha's "Tutorías" history tab (phase 5.4).
     *
     * @param array $filters optional keys: studentid, tutorid, academicyearid, status,
     *                        modalityid, reasonid, visibilitytier ('contentvisible',
     *                        'noteinternal' or 'noterestricted' — rows where that
     *                        column is not null), entrydatefrom, entrydateto
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort must be one of self::SORTABLE_COLUMNS, silently falls back to
     *                     'entrydate' otherwise
     * @param string $direction 'ASC' or 'DESC'
     * @return \stdClass[]
     */
    public function search(
        array $filters,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'entrydate',
        string $direction = 'DESC'
    ): array {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        if (!in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'entrydate';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $DB->get_records_select(self::TABLE, $sql, $params, "$sort $direction, id DESC", '*', $limitfrom, $limitnum);
    }

    /**
     * Updates only the editable fields of a tutoring entry. Deliberately
     * never reads or touches studentid, tutorid, academicyearid, entrydate or
     * status from $data, even if present — changing status is annul()'s job
     * (a separate flow), not a generic edit. Reasonids ("motivos") are edited
     * too, but not through here — entry_service::update() syncs them
     * separately via entry_reason_repository::sync(), since they live in
     * their own link table, not as a column on this one. Participants still
     * have no edit path (accepted gap — see docs/seguridad-permisos.md).
     *
     * @param int $id
     * @param \stdClass $data may contain modalityid, contentvisible, noteinternal,
     *                        noterestricted, nextfollowupdate
     * @param int $modifiedby
     * @return bool
     */
    public function update_editable_fields(int $id, \stdClass $data, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);

        if (property_exists($data, 'modalityid')) {
            $record->modalityid = !empty($data->modalityid) ? (int) $data->modalityid : null;
        }
        if (property_exists($data, 'contentvisible')) {
            $record->contentvisible = $data->contentvisible !== '' ? $data->contentvisible : null;
        }
        if (property_exists($data, 'noteinternal')) {
            $record->noteinternal = $data->noteinternal !== '' ? $data->noteinternal : null;
        }
        if (property_exists($data, 'noterestricted')) {
            $record->noterestricted = $data->noterestricted !== '' ? $data->noterestricted : null;
        }
        if (property_exists($data, 'recommendationsop')) {
            $record->recommendationsop = $data->recommendationsop !== '' ? $data->recommendationsop : null;
        }
        if (property_exists($data, 'nextfollowupdate')) {
            $record->nextfollowupdate = !empty($data->nextfollowupdate) ? (int) $data->nextfollowupdate : null;
        }
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Annuls a tutoring entry (status=annulled) — never a physical delete.
     * Callers are responsible for enforcing business guards (reason
     * required, not already annulled) before calling this.
     *
     * @param int $id
     * @param int $modifiedby
     * @return bool
     */
    public function annul(int $id, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = entry_status::ANNULLED;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Counts active tutoring entries by student, in one academic year.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return array<int, int> keyed by student id
     */
    /**
     * Fase 14 — of the given students, which have at least one active SOP
     * tutoring entry in the academic year (for the "SOP" badge on the panel).
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return int[] the subset with a SOP entry
     */
    public function student_ids_with_sop_entries(array $studentids, int $academicyearid): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED);
        $params['academicyearid'] = $academicyearid;
        $params['status'] = entry_status::ACTIVE;
        $params['entrykind'] = \local_monlaututoria\domain\entry_kind::SOP;

        return array_values(array_map('intval', $DB->get_fieldset_select(
            self::TABLE,
            'DISTINCT studentid',
            "studentid $insql AND academicyearid = :academicyearid AND status = :status AND entrykind = :entrykind",
            $params
        )));
    }

    public function count_active_by_students(array $studentids, int $academicyearid): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED);
        $params['academicyearid'] = $academicyearid;
        $params['status'] = entry_status::ACTIVE;

        $sql = 'SELECT studentid, COUNT(1) AS entrycount
                  FROM {' . self::TABLE . '}
                 WHERE studentid ' . $insql . '
                   AND academicyearid = :academicyearid
                   AND status = :status
              GROUP BY studentid';

        $rows = $DB->get_records_sql($sql, $params);
        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row->studentid] = (int) $row->entrycount;
        }

        return $counts;
    }

    /**
     * Returns the latest active tutoring entry for each student in one academic year.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return array<int, \stdClass> keyed by student id
     */
    public function get_latest_active_by_students(array $studentids, int $academicyearid): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED);
        $params['academicyearid'] = $academicyearid;
        $params['status'] = entry_status::ACTIVE;

        $sql = 'studentid ' . $insql . ' AND academicyearid = :academicyearid AND status = :status';
        $rows = $DB->get_records_select(self::TABLE, $sql, $params, 'studentid ASC, entrydate DESC, id DESC');

        $latest = [];
        foreach ($rows as $row) {
            $studentid = (int) $row->studentid;
            if (!isset($latest[$studentid])) {
                $latest[$studentid] = $row;
            }
        }

        return $latest;
    }

    /**
     * Returns all active tutoring entries for a batch of students in one academic year.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return \stdClass[]
     */
    public function find_active_by_students(array $studentids, int $academicyearid): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED);
        $params['academicyearid'] = $academicyearid;
        $params['status'] = entry_status::ACTIVE;

        $sql = 'studentid ' . $insql . ' AND academicyearid = :academicyearid AND status = :status';

        return $DB->get_records_select(self::TABLE, $sql, $params, 'studentid ASC, entrydate ASC, id ASC');
    }

    /**
     * Returns the earliest active tutoring entry for each student in one academic year.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return array<int, \stdClass> keyed by student id
     */
    public function get_first_active_by_students(array $studentids, int $academicyearid): array {
        if (empty($studentids)) {
            return [];
        }

        $rows = $this->find_active_by_students($studentids, $academicyearid);
        $first = [];
        foreach ($rows as $row) {
            $studentid = (int) $row->studentid;
            if (!isset($first[$studentid])) {
                $first[$studentid] = $row;
            }
        }

        return $first;
    }

    /**
     * Counts active tutoring entries in one academic year that include at
     * least one family participant, across a batch of students. Each entry
     * counts once even if it has several family participants.
     *
     * @param int[] $studentids
     * @param int $academicyearid
     * @return int
     */
    public function count_family_contacts_by_students(array $studentids, int $academicyearid): int {
        global $DB;

        if (empty($studentids)) {
            return 0;
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED);
        $params['academicyearid'] = $academicyearid;
        $params['status'] = entry_status::ACTIVE;
        $params['participanttype'] = \local_monlaututoria\domain\entry_participant_type::FAMILY;

        $sql = 'SELECT COUNT(DISTINCT e.id)
                  FROM {' . self::TABLE . '} e
                  JOIN {local_tut_entryparticipant} ep ON ep.entryid = e.id
                 WHERE e.studentid ' . $insql . '
                   AND e.academicyearid = :academicyearid
                   AND e.status = :status
                   AND ep.participanttype = :participanttype';

        return (int) $DB->count_records_sql($sql, $params);
    }
    /**
     * @param array $filters see search()
     * @return int
     */
    public function count_search(array $filters): int {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        return $DB->count_records_select(self::TABLE, $sql, $params);
    }

    /**
     * @param array $filters see search()
     * @return array{0: string, 1: array}
     */
    private function build_search_where(array $filters): array {
        $conditions = ['1 = 1'];
        $params = [];

        $equalityfilters = ['studentid', 'tutorid', 'academicyearid', 'status', 'modalityid', 'entrykind'];
        foreach ($equalityfilters as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $conditions[] = "$key = :$key";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['entrydatefrom'])) {
            $conditions[] = 'entrydate >= :entrydatefrom';
            $params['entrydatefrom'] = (int) $filters['entrydatefrom'];
        }
        if (!empty($filters['entrydateto'])) {
            $conditions[] = 'entrydate <= :entrydateto';
            $params['entrydateto'] = (int) $filters['entrydateto'];
        }

        if (!empty($filters['reasonid'])) {
            $conditions[] = 'id IN (SELECT entryid FROM {local_tut_entryreason} WHERE reasonid = :reasonid)';
            $params['reasonid'] = (int) $filters['reasonid'];
        }

        if (!empty($filters['visibilitytier'])
            && in_array($filters['visibilitytier'], ['contentvisible', 'noteinternal', 'noterestricted'], true)) {
            $conditions[] = $filters['visibilitytier'] . ' IS NOT NULL';
        }

        return [implode(' AND ', $conditions), $params];
    }
}
