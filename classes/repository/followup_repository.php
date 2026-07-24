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

use local_monlaututoria\domain\followup_status;
use local_monlaututoria\domain\priority_level;

/**
 * Data access for local_tut_followup. No business rules, no security — same
 * layering as agreement_repository/entry_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup_repository {

    /** @var string */
    private const TABLE = 'local_tut_followup';

    /** @var string[] */
    private const SORTABLE_COLUMNS = ['duedate', 'status', 'priority', 'timecreated'];

    /**
     * @param \stdClass $data must contain entryid, studentid, duedate, createdby;
     *                        may contain priority (defaults to priority_level::MEDIUM)
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->closingentryid = null;
        $record->studentid = (int) $data->studentid;
        $record->duedate = (int) $data->duedate;
        $record->priority = $data->priority ?? priority_level::MEDIUM;
        $record->status = followup_status::PENDING;
        $record->createdby = (int) $data->createdby;
        $record->modifiedby = (int) $data->createdby;
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
     * @param int $entryid
     * @return \stdClass[]
     */
    public function find_by_entry(int $entryid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['entryid' => $entryid], 'duedate ASC, id ASC');
    }

    /**
     * @param array $filters optional keys: studentid, entryid, status, priority, overdue (bool)
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort must be one of self::SORTABLE_COLUMNS, falls back to 'duedate'
     * @param string $direction 'ASC' or 'DESC'
     * @return \stdClass[]
     */
    public function search(
        array $filters,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'duedate',
        string $direction = 'ASC'
    ): array {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        if (!in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'duedate';
        }
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $DB->get_records_select(self::TABLE, $sql, $params, "$sort $direction, id ASC", '*', $limitfrom, $limitnum);
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
     * @param int $id
     * @param string $status one of followup_status::values()
     * @param int $modifiedby
     * @param int|null $duedate when set, replaces the current due date (postpone)
     * @return bool
     */
    public function update_status(int $id, string $status, int $modifiedby, ?int $duedate = null): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = $status;
        if ($duedate !== null) {
            $record->duedate = $duedate;
        }
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Closes a follow-up via a newly created, linked tutoring entry — sets
     * status=completed and records which entry closed it, distinct from a
     * manual completion (update_status() alone, no closingentryid).
     *
     * @param int $id
     * @param int $closingentryid
     * @param int $modifiedby
     * @return bool
     */
    public function close_with_entry(int $id, int $closingentryid, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = followup_status::COMPLETED;
        $record->closingentryid = $closingentryid;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * @param array $filters see search()
     * @return array{0: string, 1: array}
     */
    private function build_search_where(array $filters): array {
        global $DB;

        $conditions = ['1 = 1'];
        $params = [];

        $equalityfilters = ['studentid', 'entryid', 'status', 'priority'];
        foreach ($equalityfilters as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $conditions[] = "$key = :$key";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['overdue'])) {
            [$insql, $inparams] = $DB->get_in_or_equal(followup_status::open_values(), SQL_PARAMS_NAMED, 'ov');
            $conditions[] = "status $insql AND duedate < :overduenow";
            $params = array_merge($params, $inparams, ['overduenow' => time()]);
        }

        return [implode(' AND ', $conditions), $params];
    }
}
