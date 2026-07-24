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
 * Data access for local_tut_entryparticipant. No business rules here — the
 * "exactly one of userid/externalname" invariant is enforced by entry_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_participant_repository {

    /** @var string */
    private const TABLE = 'local_tut_entryparticipant';

    /**
     * @param \stdClass $data must contain entryid, participanttype, createdby;
     *                        may contain userid, externalname
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->entryid = (int) $data->entryid;
        $record->participanttype = $data->participanttype;
        $record->userid = isset($data->userid) ? (int) $data->userid : null;
        $record->externalname = $data->externalname ?? null;
        $record->createdby = (int) $data->createdby;
        $record->timecreated = time();

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * @param int $entryid
     * @return \stdClass[]
     */
    public function get_for_entry(int $entryid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['entryid' => $entryid], 'id ASC');
    }
}
