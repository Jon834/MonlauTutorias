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

use local_monlaututoria\domain\agreement_status;

/**
 * Data access for local_tut_agreement. No business rules, no security — same
 * layering as entry_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_repository {

    /** @var string */
    private const TABLE = 'local_tut_agreement';

    /** @var string[] */
    private const SORTABLE_COLUMNS = ['duedate', 'status', 'timecreated'];

    /**
     * @param \stdClass $data must contain entryid, studentid, description,
     *                        responsibletype, duedate, createdby; may contain
     *                        responsibleuserid, responsibleexternalname, visibletostudent
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->studentid = (int) $data->studentid;
        $record->description = $data->description;
        $record->responsibletype = $data->responsibletype;
        $record->responsibleuserid = isset($data->responsibleuserid) ? (int) $data->responsibleuserid : null;
        $record->responsibleexternalname = $data->responsibleexternalname ?? null;
        $record->duedate = (int) $data->duedate;
        $record->status = agreement_status::PENDING;
        $record->visibletostudent = !empty($data->visibletostudent) ? 1 : 0;
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
     * @param array $filters optional keys: studentid, entryid, status, overdue (bool —
     *                        true means status IN pending/in_progress AND duedate < now)
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
     * Updates status (and, when provided, duedate — used by "postpone") of an
     * agreement. Never touches entryid/studentid/description/responsible*.
     *
     * @param int $id
     * @param string $status one of agreement_status::values()
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
     * @param array $filters see search()
     * @return array{0: string, 1: array}
     */
    private function build_search_where(array $filters): array {
        global $DB;

        $conditions = ['1 = 1'];
        $params = [];

        $equalityfilters = ['studentid', 'entryid', 'status'];
        foreach ($equalityfilters as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $conditions[] = "$key = :$key";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['overdue'])) {
            [$insql, $inparams] = $DB->get_in_or_equal(agreement_status::open_values(), SQL_PARAMS_NAMED, 'ov');
            $conditions[] = "status $insql AND duedate < :overduenow";
            $params = array_merge($params, $inparams, ['overduenow' => time()]);
        }

        return [implode(' AND ', $conditions), $params];
    }
}
