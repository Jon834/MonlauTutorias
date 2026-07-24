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
 * Shared priority scale for local_tut_followup and local_tut_referral
 * (phase 6.2/6.4) — one enum, not two copies, since both entities use the
 * exact same 3 values.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class priority_level {

    public const LOW = 'low';
    public const MEDIUM = 'medium';
    public const HIGH = 'high';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::LOW, self::MEDIUM, self::HIGH];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::LOW    => get_string('priority_low', 'local_monlaututoria'),
            self::MEDIUM => get_string('priority_medium', 'local_monlaututoria'),
            self::HIGH   => get_string('priority_high', 'local_monlaututoria'),
        ];
    }
}
