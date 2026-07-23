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
 * Data access over Moodle core's cohort_members/user tables, used by
 * unassigned_students_service to build the population to analyse. Pure
 * membership lookups only — no reference to local_tut_assignment here (that
 * lives in assignment_repository), same one-table-per-repository convention
 * used across this plugin.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_membership_repository {

    /**
     * All distinct users belonging to any of the given cohorts. No pagination
     * here: unassigned_students_service classifies the whole population in
     * PHP to correctly paginate over the filtered (unassigned-only) subset —
     * see its class docblock for why.
     *
     * @param int[] $cohortids
     * @return \stdClass[] keyed by user id: id, firstname, lastname, email, suspended, deleted
     */
    public function get_members(array $cohortids): array {
        global $DB;

        if (empty($cohortids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED, 'cohort');
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.suspended, u.deleted
                  FROM {cohort_members} cm
                  JOIN {user} u ON u.id = cm.userid
                 WHERE cm.cohortid $insql
              ORDER BY u.lastname ASC, u.firstname ASC, u.id ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Which of the given cohorts each of the given users belongs to, so
     * callers can attribute a student to the specific selected cohort(s)
     * without re-querying per student.
     *
     * @param int[] $cohortids
     * @param int[] $userids
     * @return array<int, int[]> userid => list of cohortids
     */
    public function get_memberships(array $cohortids, array $userids): array {
        global $DB;

        if (empty($cohortids) || empty($userids)) {
            return [];
        }

        [$cohortinsql, $cohortparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED, 'cohort');
        [$userinsql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'user');
        $sql = "SELECT cm.userid, cm.cohortid
                  FROM {cohort_members} cm
                 WHERE cm.cohortid $cohortinsql AND cm.userid $userinsql";

        // get_recordset_sql(), not get_records_sql(): (userid, cohortid) pairs are
        // not unique by userid alone when a user belongs to more than one of the
        // requested cohorts, and get_records_sql() would key-collide on that.
        $memberships = [];
        $rows = $DB->get_recordset_sql($sql, $cohortparams + $userparams);
        foreach ($rows as $record) {
            $memberships[(int) $record->userid][] = (int) $record->cohortid;
        }
        $rows->close();

        return $memberships;
    }
}
