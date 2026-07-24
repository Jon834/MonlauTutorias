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
 * Valid values for local_tut_referral.destination (docs/fases/phase-6.md's
 * 6.4 bullet list: "Coordinación, orientación o dirección").
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_destination {

    public const COORDINATION = 'coordination';
    public const ORIENTATION = 'orientation';
    public const MANAGEMENT = 'management';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::COORDINATION, self::ORIENTATION, self::MANAGEMENT];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::COORDINATION => get_string('referraldestination_coordination', 'local_monlaututoria'),
            self::ORIENTATION  => get_string('referraldestination_orientation', 'local_monlaututoria'),
            self::MANAGEMENT   => get_string('referraldestination_management', 'local_monlaututoria'),
        ];
    }
}
