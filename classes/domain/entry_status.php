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
 * Valid values for local_tut_entry.status.
 *
 * `annulled` is part of the schema but has no producer yet in phase 5.1:
 * every entry created by entry_service starts as `active`. Reserved for
 * phase 5.5 ("Anulación lógica, nunca borrado ordinario"), same pattern as
 * assignment_status::CLOSED/CANCELLED before phase 3B wired them.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_status {

    public const ACTIVE = 'active';
    public const ANNULLED = 'annulled';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::ACTIVE, self::ANNULLED];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::ACTIVE   => get_string('entrystatus_active', 'local_monlaututoria'),
            self::ANNULLED => get_string('entrystatus_annulled', 'local_monlaututoria'),
        ];
    }
}
