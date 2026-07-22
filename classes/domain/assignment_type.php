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
 * Valid values for local_tut_assignment.assignmenttype.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_type {

    public const PRIMARY = 'primary';
    public const CO_TUTOR = 'co_tutor';
    public const SUPPORT = 'support';
    public const ORIENTATION = 'orientation';
    public const OTHER = 'other';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PRIMARY, self::CO_TUTOR, self::SUPPORT, self::ORIENTATION, self::OTHER];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::PRIMARY     => get_string('assignmenttype_primary', 'local_monlaututoria'),
            self::CO_TUTOR    => get_string('assignmenttype_co_tutor', 'local_monlaututoria'),
            self::SUPPORT     => get_string('assignmenttype_support', 'local_monlaututoria'),
            self::ORIENTATION => get_string('assignmenttype_orientation', 'local_monlaututoria'),
            self::OTHER       => get_string('assignmenttype_other', 'local_monlaututoria'),
        ];
    }
}
