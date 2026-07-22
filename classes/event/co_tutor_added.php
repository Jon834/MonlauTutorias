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
 * Event triggered when a co-tutor is added for a student. Triggered instead
 * of assignment_created when the new row's assignmenttype is co_tutor.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class co_tutor_added extends assignment_event_base {

    protected function get_crud_value(): string {
        return 'c';
    }

    public static function get_name() {
        return get_string('eventcotutoradded', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} added a co-tutor assignment (id {$this->objectid}) "
            . "for the student with id {$this->relateduserid}.";
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @return self
     */
    public static function create_from_id(int $objectid, int $userid, int $studentid, int $tutorid, int $academicyearid): self {
        return self::build($objectid, $userid, $studentid, [
            'tutorid'        => $tutorid,
            'academicyearid' => $academicyearid,
        ]);
    }
}
