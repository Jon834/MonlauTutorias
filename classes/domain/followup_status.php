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
 * Valid persisted values for local_tut_followup.status. "vencido"/overdue is
 * computed at read time, same reasoning as agreement_status — see that
 * class's docblock.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup_status {

    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PENDING, self::COMPLETED, self::CANCELLED];
    }

    /**
     * @return string[]
     */
    public static function open_values(): array {
        return [self::PENDING];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::PENDING   => get_string('followupstatus_pending', 'local_monlaututoria'),
            self::COMPLETED => get_string('followupstatus_completed', 'local_monlaututoria'),
            self::CANCELLED => get_string('followupstatus_cancelled', 'local_monlaututoria'),
        ];
    }
}
