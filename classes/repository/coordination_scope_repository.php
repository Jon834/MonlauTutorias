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
 * Explicit coordination scopes by cohort (phase 8.1).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class coordination_scope_repository {

    private const TABLE = 'local_tut_coordscope';

    /**
     * @param int $userid
     * @return \stdClass[] keyed by id
     */
    public function find_by_user(int $userid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['userid' => $userid], 'cohortid ASC, id ASC');
    }

    /**
     * @param int[] $userids
     * @return array<int, int[]> userid => cohortids
     */
    public function get_cohort_ids_for_users(array $userids): array {
        global $DB;

        if (empty($userids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $userids)), SQL_PARAMS_NAMED, 'user');
        $rows = $DB->get_records_select(self::TABLE, 'userid ' . $insql, $params, 'userid ASC, cohortid ASC');

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->userid][] = (int) $row->cohortid;
        }

        return $result;
    }

    /**
     * @param int $userid
     * @return int[]
     */
    public function get_cohort_ids_for_user(int $userid): array {
        $rows = $this->find_by_user($userid);

        return array_values(array_map(static fn (\stdClass $row): int => (int) $row->cohortid, $rows));
    }

    /**
     * @return \stdClass[]
     */
    public function get_all(): array {
        global $DB;

        return $DB->get_records(self::TABLE, null, 'userid ASC, cohortid ASC');
    }

    /**
     * Replaces the full set of cohort assignments for one user.
     *
     * @param int $userid
     * @param int[] $cohortids
     * @param int $actorid
     */
    public function replace_user_scopes(int $userid, array $cohortids, int $actorid): void {
        global $DB;

        $cohortids = array_values(array_unique(array_map('intval', $cohortids)));
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(self::TABLE, ['userid' => $userid]);

        $now = time();
        foreach ($cohortids as $cohortid) {
            $DB->insert_record(self::TABLE, (object) [
                'userid' => $userid,
                'cohortid' => $cohortid,
                'createdby' => $actorid,
                'modifiedby' => $actorid,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }

        $transaction->allow_commit();
    }
}
