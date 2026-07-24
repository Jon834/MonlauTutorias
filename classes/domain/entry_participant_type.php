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
 * Valid values for local_tut_entryparticipant.participanttype. Applies to
 * both internal (userid set) and external (externalname set) participants —
 * an internal Moodle user can still be e.g. "orientation" staff.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_participant_type {

    public const FAMILY = 'family';
    public const ORIENTATION = 'orientation';
    public const COMPANY = 'company';
    public const TEACHER = 'teacher';
    public const OTHER = 'other';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::FAMILY, self::ORIENTATION, self::COMPANY, self::TEACHER, self::OTHER];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::FAMILY      => get_string('entryparticipanttype_family', 'local_monlaututoria'),
            self::ORIENTATION => get_string('entryparticipanttype_orientation', 'local_monlaututoria'),
            self::COMPANY     => get_string('entryparticipanttype_company', 'local_monlaututoria'),
            self::TEACHER     => get_string('entryparticipanttype_teacher', 'local_monlaututoria'),
            self::OTHER       => get_string('entryparticipanttype_other', 'local_monlaututoria'),
        ];
    }
}
