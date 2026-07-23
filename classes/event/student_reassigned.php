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
 * Event triggered when a student's primary tutor changes. A single event
 * covers the whole operation (closing the old assignment and creating the
 * new one), rather than separate assignment_closed/assignment_created events,
 * to keep the audit log free of duplicated entries for one business action.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class student_reassigned extends assignment_event_base {

    protected function get_crud_value(): string {
        return 'u';
    }

    public static function get_name() {
        return get_string('eventstudentreassigned', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} reassigned the student with id {$this->relateduserid} "
            . "from tutor id {$this->other['previoustutorid']} to a new assignment with id {$this->objectid}.";
    }

    /**
     * previoustutorid/previousassignmentid reference user.id and this same
     * table respectively, but are not yet mapped for backup/restore: this
     * plugin has not exercised backup/restore so far, same gap already left
     * documented on academic_year_activated::get_other_mapping() in phase 2.
     *
     * @return array
     */
    public static function get_other_mapping() {
        return [
            'previoustutorid'      => \core\event\base::NOT_MAPPED,
            'previousassignmentid' => \core\event\base::NOT_MAPPED,
        ];
    }

    /**
     * @param int $objectid the new assignment's id
     * @param int $userid
     * @param int $studentid
     * @param int $previoustutorid
     * @param int $previousassignmentid
     * @param int $academicyearid
     * @param string|null $reassignreason one of assignment_reassign_reason::values()
     * @param int[] $closedcotutorids co-tutor assignment ids closed as part of this reassignment
     * @return self
     */
    public static function create_from_id(
        int $objectid,
        int $userid,
        int $studentid,
        int $previoustutorid,
        int $previousassignmentid,
        int $academicyearid,
        ?string $reassignreason = null,
        array $closedcotutorids = []
    ): self {
        $other = [
            'previoustutorid'      => $previoustutorid,
            'previousassignmentid' => $previousassignmentid,
            'academicyearid'       => $academicyearid,
            'closedcotutorids'     => $closedcotutorids,
        ];
        if ($reassignreason !== null) {
            $other['reassignreason'] = $reassignreason;
        }

        return self::build($objectid, $userid, $studentid, $other);
    }
}
