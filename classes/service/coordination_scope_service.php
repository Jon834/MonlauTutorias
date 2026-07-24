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

use local_monlaututoria\repository\cohort_repository;
use local_monlaututoria\repository\coordination_scope_repository;

/**
 * Explicit cohort-based coordination scope resolution (phase 8.1).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class coordination_scope_service {

    private coordination_scope_repository $repository;
    private cohort_repository $cohortrepository;

    public function __construct(
        ?coordination_scope_repository $repository = null,
        ?cohort_repository $cohortrepository = null
    ) {
        $this->repository = $repository ?? new coordination_scope_repository();
        $this->cohortrepository = $cohortrepository ?? new cohort_repository();
    }

    /**
     * @param int $userid
     * @return int[]
     */
    public function get_effective_cohort_ids(int $userid): array {
        $context = \context_system::instance();

        if (has_capability('local/monlaututoria:viewallassignments', $context, $userid)) {
            return $this->cohortrepository->get_all_ids();
        }

        require_capability('local/monlaututoria:viewcoordinationdashboard', $context, $userid);

        return $this->repository->get_cohort_ids_for_user($userid);
    }

    /**
     * @param int $userid
     * @param int[] $requestedcohortids
     */
    public function require_user_can_access_cohorts(int $userid, array $requestedcohortids): void {
        $allowed = $this->get_effective_cohort_ids($userid);
        $missing = array_diff(array_unique(array_map('intval', $requestedcohortids)), $allowed);
        if (!empty($missing)) {
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }
    }

    /**
     * @param int $userid
     * @param int $actorid
     * @param int[] $cohortids
     */
    public function replace_user_scopes(int $userid, int $actorid, array $cohortids): void {
        require_capability('local/monlaututoria:managecoordinationscopes', \context_system::instance(), $actorid);
        $validcohortids = array_keys($this->cohortrepository->get_many($cohortids));
        $this->repository->replace_user_scopes($userid, array_map('intval', $validcohortids), $actorid);
    }
}
