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
 * Data access for local_tut_entryattachment — category/description metadata
 * only. The file bytes themselves are Moodle's File API's responsibility,
 * never duplicated here.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_repository {

    /** @var string */
    private const TABLE = 'local_tut_entryattachment';

    /**
     * @param \stdClass $data must contain entryid, pathnamehash, category, createdby;
     *                        may contain description
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->pathnamehash = $data->pathnamehash;
        $record->category = $data->category;
        $record->description = $data->description ?? null;
        $record->createdby = (int) $data->createdby;
        $record->timecreated = time();

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * @param string $pathnamehash
     * @return bool
     */
    public function exists_for_pathnamehash(string $pathnamehash): bool {
        global $DB;

        return $DB->record_exists(self::TABLE, ['pathnamehash' => $pathnamehash]);
    }

    /**
     * Entry ids (from the given set) that have at least one tracked
     * attachment — fase 14, for the paperclip indicator in the tutorías list.
     *
     * @param int[] $entryids
     * @return int[] the subset that has attachments
     */
    public function entry_ids_with_attachments(array $entryids): array {
        global $DB;

        if (empty($entryids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_map('intval', $entryids), SQL_PARAMS_NAMED);

        return array_values(array_map('intval', $DB->get_fieldset_select(
            self::TABLE, 'DISTINCT entryid', "entryid $insql", $params
        )));
    }

    /**
     * @param int $entryid
     * @return \stdClass[] keyed by pathnamehash, for joining against File API results
     */
    public function get_for_entry(int $entryid): array {
        global $DB;

        $records = $DB->get_records(self::TABLE, ['entryid' => $entryid], 'timecreated ASC');

        $bypathnamehash = [];
        foreach ($records as $record) {
            $bypathnamehash[$record->pathnamehash] = $record;
        }

        return $bypathnamehash;
    }
}
