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
 * Valid values for local_tut_referral.status (docs/fases/phase-6.md's 6.4
 * bullet list: "asignado, estado y resolución"). Unlike agreement/followup,
 * there is no "vencido" concept here — a referral has no due date.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_status {

    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const RESOLVED = 'resolved';
    public const CANCELLED = 'cancelled';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PENDING, self::IN_PROGRESS, self::RESOLVED, self::CANCELLED];
    }

    /**
     * @return string[]
     */
    public static function open_values(): array {
        return [self::PENDING, self::IN_PROGRESS];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::PENDING     => get_string('referralstatus_pending', 'local_monlaututoria'),
            self::IN_PROGRESS => get_string('referralstatus_in_progress', 'local_monlaututoria'),
            self::RESOLVED    => get_string('referralstatus_resolved', 'local_monlaututoria'),
            self::CANCELLED   => get_string('referralstatus_cancelled', 'local_monlaututoria'),
        ];
    }
}
