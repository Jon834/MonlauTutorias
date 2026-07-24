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

namespace local_monlaututoria\event;

/**
 * Event triggered by any of agreement_service's quick actions (phase 6.3):
 * complete, reopen, postpone, cancel. One consolidated event rather than one
 * class per action — oldstatus/newstatus (and newduedate for postpone) in
 * `other` are enough for logstore_standard to reconstruct what happened,
 * same economy already applied to entry_updated.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_updated extends agreement_event_base {

    protected function get_crud_value(): string {
        return 'u';
    }

    public static function get_name() {
        return get_string('eventagreementupdated', 'local_monlaututoria');
    }

    public function get_description() {
        $oldstatus = $this->other['oldstatus'] ?? '?';
        $newstatus = $this->other['newstatus'] ?? '?';

        return "The user with id {$this->userid} changed agreement (id {$this->objectid}) "
            . "status from {$oldstatus} to {$newstatus}, for the student with id {$this->relateduserid}.";
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int $studentid
     * @param string $oldstatus
     * @param string $newstatus
     * @param int|null $newduedate set only when this update postponed the due date
     * @return self
     */
    public static function create_from_id(
        int $objectid,
        int $userid,
        int $studentid,
        string $oldstatus,
        string $newstatus,
        ?int $newduedate = null
    ): self {
        return self::build($objectid, $userid, $studentid, array_filter([
            'oldstatus'  => $oldstatus,
            'newstatus'  => $newstatus,
            'newduedate' => $newduedate,
        ], static fn ($value) => $value !== null));
    }
}
