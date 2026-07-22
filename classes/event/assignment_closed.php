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
 * Event triggered when a tutor-student assignment is closed (not as part of
 * a reassignment — that triggers student_reassigned instead — and not for a
 * co-tutor row, which triggers co_tutor_removed instead).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_closed extends assignment_event_base {

    protected function get_crud_value(): string {
        return 'u';
    }

    public static function get_name() {
        return get_string('eventassignmentclosed', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} closed the assignment with id {$this->objectid} "
            . "for the student with id {$this->relateduserid}.";
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int $studentid
     * @return self
     */
    public static function create_from_id(int $objectid, int $userid, int $studentid): self {
        return self::build($objectid, $userid, $studentid);
    }
}
