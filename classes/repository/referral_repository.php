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

use local_monlaututoria\domain\referral_status;
use local_monlaututoria\domain\priority_level;

/**
 * Data access for local_tut_referral. No business rules, no security — same
 * layering as agreement_repository/followup_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_repository {

    /** @var string */
    private const TABLE = 'local_tut_referral';

    /** @var string[] */
    private const SORTABLE_COLUMNS = ['status', 'priority', 'timecreated'];

    /**
     * @return string[]
     */
    public static function sortable_columns(): array {
        return self::SORTABLE_COLUMNS;
    }

    /**
     * @param \stdClass $data must contain entryid, studentid, destination, reason, createdby;
     *                        may contain priority (defaults to priority_level::MEDIUM)
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->studentid = (int) $data->studentid;
        $record->destination = $data->destination;
        $record->reason = $data->reason;
        $record->priority = $data->priority ?? priority_level::MEDIUM;
        $record->assignedto = null;
        $record->status = referral_status::PENDING;
        $record->resolution = null;
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

        return $DB->get_records(self::TABLE, ['entryid' => $entryid], 'timecreated DESC');
    }

    /**
     * @param array $filters optional keys: studentid, entryid, status, priority,
     *                        assignedto, createdby
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort must be one of self::SORTABLE_COLUMNS, falls back to 'timecreated'
     * @param string $direction 'ASC' or 'DESC'
     * @return \stdClass[]
     */
    public function search(
        array $filters,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'timecreated',
        string $direction = 'DESC'
    ): array {
        global $DB;

        [$sql, $params] = $this->build_search_where($filters);

        if (!in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'timecreated';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $DB->get_records_select(self::TABLE, $sql, $params, "$sort $direction, id DESC", '*', $limitfrom, $limitnum);
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
     * @param int $assignedto
     * @param int $modifiedby
     * @return bool
     */
    public function assign(int $id, int $assignedto, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);
        $record->assignedto = $assignedto;
        if ($record->status === referral_status::PENDING) {
            $record->status = referral_status::IN_PROGRESS;
        }
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * @param int $id
     * @param string $resolution
     * @param int $modifiedby
     * @return bool
     */
    public function resolve(int $id, string $resolution, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = referral_status::RESOLVED;
        $record->resolution = $resolution;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * @param int $id
     * @param int $modifiedby
     * @return bool
     */
    public function cancel(int $id, int $modifiedby): bool {
        global $DB;

        $record = $this->get($id);
        $record->status = referral_status::CANCELLED;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * @param int[] $studentids
     * @return \stdClass[]
     */
    public function find_by_students(array $studentids): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED, 'student');

        return $DB->get_records_select(self::TABLE, 'studentid ' . $insql, $params, 'timecreated DESC, id DESC');
    }

    /**
     * @param int[] $studentids
     * @return \stdClass[]
     */
    public function find_open_by_students(array $studentids): array {
        global $DB;

        if (empty($studentids)) {
            return [];
        }

        [$studentsql, $studentparams] = $DB->get_in_or_equal(array_unique(array_map('intval', $studentids)), SQL_PARAMS_NAMED, 'student');
        [$statussql, $statusparams] = $DB->get_in_or_equal(referral_status::open_values(), SQL_PARAMS_NAMED, 'status');
        $sql = 'studentid ' . $studentsql . ' AND status ' . $statussql;

        return $DB->get_records_select(self::TABLE, $sql, $studentparams + $statusparams, 'timecreated DESC, id DESC');
    }

    /**
     * @param array $filters see search()
     * @return array{0: string, 1: array}
     */
    private function build_search_where(array $filters): array {
        $conditions = ['1 = 1'];
        $params = [];

        $equalityfilters = ['studentid', 'entryid', 'status', 'priority', 'assignedto', 'createdby'];
        foreach ($equalityfilters as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $conditions[] = "$key = :$key";
                $params[$key] = $filters[$key];
            }
        }

        return [implode(' AND ', $conditions), $params];
    }
}
