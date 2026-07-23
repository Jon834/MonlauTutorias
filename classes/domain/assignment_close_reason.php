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

namespace local_monlaututoria\domain;

/**
 * Stable, coded reasons for closing a tutor-student assignment
 * (local_tut_assignment.closereason). Application logic must reference these
 * constants, never the translated label.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_close_reason {

    public const TUTOR_CHANGE = 'tutor_change';
    public const GROUP_CHANGE = 'group_change';
    public const LEVEL_CHANGE = 'level_change';
    public const END_OF_YEAR = 'end_of_year';
    public const STUDENT_LEFT = 'student_left';
    public const TUTOR_LEFT = 'tutor_left';
    public const ADMIN_ERROR = 'admin_error';
    public const SUPPORT_ENDED = 'support_ended';
    public const OTHER = 'other';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::TUTOR_CHANGE, self::GROUP_CHANGE, self::LEVEL_CHANGE, self::END_OF_YEAR,
            self::STUDENT_LEFT, self::TUTOR_LEFT, self::ADMIN_ERROR, self::SUPPORT_ENDED, self::OTHER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::TUTOR_CHANGE   => get_string('closereason_tutorchange', 'local_monlaututoria'),
            self::GROUP_CHANGE   => get_string('closereason_groupchange', 'local_monlaututoria'),
            self::LEVEL_CHANGE   => get_string('closereason_levelchange', 'local_monlaututoria'),
            self::END_OF_YEAR    => get_string('closereason_endofyear', 'local_monlaututoria'),
            self::STUDENT_LEFT   => get_string('closereason_studentleft', 'local_monlaututoria'),
            self::TUTOR_LEFT     => get_string('closereason_tutorleft', 'local_monlaututoria'),
            self::ADMIN_ERROR    => get_string('closereason_adminerror', 'local_monlaututoria'),
            self::SUPPORT_ENDED  => get_string('closereason_supportended', 'local_monlaututoria'),
            self::OTHER          => get_string('closereason_other', 'local_monlaututoria'),
        ];
    }
}
