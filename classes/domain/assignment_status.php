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
 * Valid values for local_tut_assignment.status.
 *
 * `pending` and `cancelled` are part of the schema but have no producer yet in
 * phase 3A: every assignment created by assignment_service starts as `active`.
 * Reserved for phases 3B/3D.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_status {

    public const ACTIVE = 'active';
    public const CLOSED = 'closed';
    public const CANCELLED = 'cancelled';
    public const PENDING = 'pending';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::ACTIVE, self::CLOSED, self::CANCELLED, self::PENDING];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::ACTIVE    => get_string('assignmentstatus_active', 'local_monlaututoria'),
            self::CLOSED    => get_string('assignmentstatus_closed', 'local_monlaututoria'),
            self::CANCELLED => get_string('assignmentstatus_cancelled', 'local_monlaututoria'),
            self::PENDING   => get_string('assignmentstatus_pending', 'local_monlaututoria'),
        ];
    }
}
