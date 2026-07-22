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
 * Visibility levels for reason catalogue entries, matching docs/seguridad-permisos.md.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class visibility_level {

    /** @var int Content shared with the student. */
    public const SHARED = 0;

    /** @var int Internal tutoring content, not shown to the student. */
    public const INTERNAL = 1;

    /** @var int Restricted content. */
    public const RESTRICTED = 2;

    /**
     * Returns the valid visibility values.
     *
     * @return int[]
     */
    public static function values(): array {
        return [self::SHARED, self::INTERNAL, self::RESTRICTED];
    }

    /**
     * Returns a value => localised label map, suitable for a select form element.
     *
     * @return array<int, string>
     */
    public static function get_options(): array {
        return [
            self::SHARED     => get_string('visibility_shared', 'local_monlaututoria'),
            self::INTERNAL   => get_string('visibility_internal', 'local_monlaututoria'),
            self::RESTRICTED => get_string('visibility_restricted', 'local_monlaututoria'),
        ];
    }
}
