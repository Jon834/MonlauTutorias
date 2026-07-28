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
 * Data access for local_tut_entryreason, the many-to-many link between a
 * tutoring entry and local_tut_reason ("motivos relacionados"). Returns bare
 * reasonids — callers resolve full local_tut_reason records themselves via
 * reason_repository::get_many(), same batching rationale as
 * academic_year_repository::get_many() (phase 3E.4).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_reason_repository {

    /** @var string */
    private const TABLE = 'local_tut_entryreason';

    /**
     * Bulk-inserts the (entryid, reasonid) links for a newly created entry.
     * Callers are responsible for validating each reasonid beforehand.
     *
     * @param int $entryid
     * @param int[] $reasonids
     */
    public function attach(int $entryid, array $reasonids): void {
        global $DB;

        foreach (array_unique(array_map('intval', $reasonids)) as $reasonid) {
            $DB->insert_record(self::TABLE, (object) [
                'entryid'  => $entryid,
                'reasonid' => $reasonid,
            ]);
        }
    }

    /**
     * Replaces every existing (entryid, reasonid) link with exactly the set
     * given — used by entry_service::update() (phase 5.5 edit) to let a
     * tutor change the "motivos" of an already-created entry, something
     * create() never needed to do since it always starts from zero links.
     * Callers are responsible for validating each reasonid beforehand, same
     * as attach().
     *
     * @param int $entryid
     * @param int[] $reasonids the complete new set; an empty array removes
     *                         every existing link and attaches none
     */
    public function sync(int $entryid, array $reasonids): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['entryid' => $entryid]);
        $this->attach($entryid, $reasonids);
    }

    /**
     * @param int $entryid
     * @return int[]
     */
    public function get_for_entry(int $entryid): array {
        global $DB;

        return array_values(array_map(
            'intval',
            $DB->get_fieldset_select(self::TABLE, 'reasonid', 'entryid = :entryid', ['entryid' => $entryid])
        ));
    }

    /**
     * Batch fetch for a page of entries, one query regardless of row count —
     * used by the history table (phase 5.4) instead of calling
     * get_for_entry() once per row.
     *
     * @param int[] $entryids
     * @return array<int, int[]> reasonids keyed by entryid; entries with no
     *                            reason attached are simply absent
     */
    public function get_for_entries(array $entryids): array {
        global $DB;

        if (empty($entryids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_unique(array_map('intval', $entryids)), SQL_PARAMS_NAMED);
        $links = $DB->get_records_select(self::TABLE, "entryid $insql", $params, 'entryid ASC, id ASC');

        $byentry = [];
        foreach ($links as $link) {
            $byentry[(int) $link->entryid][] = (int) $link->reasonid;
        }

        return $byentry;
    }
}
