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
use local_monlaututoria\repository\enabled_cohort_repository;

/**
 * Resolves which Moodle cohorts this plugin treats as relevant — real-use
 * feedback: an admin had no way to hide irrelevant cohorts (e.g. staff
 * groups) from every cohort dropdown in the plugin (manual assignment
 * creation, cohort-based bulk assignment, the coordination scope a
 * viewallassignments user gets by default). Distinct from
 * coordination_scope_service, which restricts which of THESE cohorts one
 * specific coordinator may access — this restricts the pool for everyone.
 *
 * An empty local_tut_enabledcohort table means "unrestricted" (every Moodle
 * cohort is relevant), not "nothing configured" — this is what keeps
 * upgrading to this feature from silently hiding every cohort until an
 * admin visits cohort_visibility.php and saves a subset.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_visibility_service {

    /** @var cohort_repository */
    private $cohortrepository;

    /** @var enabled_cohort_repository */
    private $enabledcohortrepository;

    public function __construct(
        ?cohort_repository $cohortrepository = null,
        ?enabled_cohort_repository $enabledcohortrepository = null
    ) {
        $this->cohortrepository = $cohortrepository ?? new cohort_repository();
        $this->enabledcohortrepository = $enabledcohortrepository ?? new enabled_cohort_repository();
    }

    /**
     * @return int[]
     */
    public function get_visible_cohort_ids(): array {
        $enabled = $this->enabledcohortrepository->get_all_ids();

        return !empty($enabled) ? $enabled : $this->cohortrepository->get_all_ids();
    }

    /**
     * @return \stdClass[] keyed by id, sorted by name like cohort_repository::get_all()
     *                     — get_many() itself does not sort, since callers that
     *                     already know their own id order (e.g. resolving a
     *                     fixed list) should not pay for an unwanted re-sort
     */
    public function get_visible_cohorts(): array {
        $enabled = $this->enabledcohortrepository->get_all_ids();
        if (empty($enabled)) {
            return $this->cohortrepository->get_all();
        }

        $cohorts = $this->cohortrepository->get_many($enabled);
        uasort($cohorts, static fn (\stdClass $a, \stdClass $b): int => strnatcasecmp($a->name, $b->name));

        return $cohorts;
    }

    /**
     * @return int[] currently enabled cohort ids, [] when unrestricted (every
     *               cohort implicitly enabled) — distinct from
     *               get_visible_cohort_ids(), which resolves that fallback;
     *               callers building the admin checkbox list need to know
     *               whether the table is genuinely empty, not what it falls
     *               back to
     */
    public function get_explicitly_enabled_cohort_ids(): array {
        return $this->enabledcohortrepository->get_all_ids();
    }

    /**
     * @param int[] $cohortids
     * @param int $actorid
     */
    public function replace_enabled_cohorts(array $cohortids, int $actorid): void {
        $validcohortids = array_keys($this->cohortrepository->get_many($cohortids));
        $this->enabledcohortrepository->replace_all(array_map('intval', $validcohortids), $actorid);
    }
}
