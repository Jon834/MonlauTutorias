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
 * Data access for local_tut_enabledcohort — the global, admin-curated
 * allowlist of Moodle cohorts this plugin treats as relevant. An empty table
 * is a valid, meaningful state ("unrestricted"), not "nothing configured
 * yet" — see cohort_visibility_service, the only caller that interprets
 * emptiness.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class enabled_cohort_repository {

    /** @var string */
    private const TABLE = 'local_tut_enabledcohort';

    /**
     * @return int[]
     */
    public function get_all_ids(): array {
        global $DB;

        return array_map('intval', $DB->get_fieldset_select(self::TABLE, 'cohortid', '1=1'));
    }

    /**
     * Replaces the complete enabled set in one go — this is a global
     * allowlist, not a per-user one, so there is no "for this actor" scoping
     * to preserve across the call, unlike coordination_scope_repository.
     *
     * @param int[] $cohortids
     * @param int $actorid
     */
    public function replace_all(array $cohortids, int $actorid): void {
        global $DB;

        $cohortids = array_values(array_unique(array_map('intval', $cohortids)));
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(self::TABLE);

        $now = time();
        foreach ($cohortids as $cohortid) {
            $DB->insert_record(self::TABLE, (object) [
                'cohortid' => $cohortid,
                'createdby' => $actorid,
                'timecreated' => $now,
            ]);
        }

        $transaction->allow_commit();
    }
}
