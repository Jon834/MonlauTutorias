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
 * Data access for local_tut_entryversion — the first writer this table has
 * had since it was created empty in phase 5.1 (see its comment in
 * db/install.xml). No business rules here: entry_service::update()/annul()
 * decide when a snapshot is taken and what it contains.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_version_repository {

    /** @var string */
    private const TABLE = 'local_tut_entryversion';

    /**
     * @param int $entryid
     * @return int the next versionnumber to use for this entry (1 for the first)
     */
    public function get_next_version_number(int $entryid): int {
        global $DB;

        $max = $DB->get_field_select(
            self::TABLE,
            'MAX(versionnumber)',
            'entryid = :entryid',
            ['entryid' => $entryid]
        );

        return ((int) $max) + 1;
    }

    /**
     * @param \stdClass $data must contain entryid, versionnumber, snapshotjson, createdby;
     *                        may contain changereason
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->versionnumber = (int) $data->versionnumber;
        $record->snapshotjson = $data->snapshotjson;
        $record->changereason = $data->changereason ?? null;
        $record->createdby = (int) $data->createdby;
        $record->timecreated = time();

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * @param int $entryid
     * @return \stdClass[] most recent version first
     */
    public function get_for_entry(int $entryid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['entryid' => $entryid], 'versionnumber DESC');
    }
}
