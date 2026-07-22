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
 * Valid values for local_tut_assignment.source.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_source {

    public const MANUAL = 'manual';
    public const COHORT = 'cohort';
    public const CSV = 'csv';
    public const EXTERNAL = 'external';
    public const MIGRATION = 'migration';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::MANUAL, self::COHORT, self::CSV, self::EXTERNAL, self::MIGRATION];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::MANUAL    => get_string('assignmentsource_manual', 'local_monlaututoria'),
            self::COHORT    => get_string('assignmentsource_cohort', 'local_monlaututoria'),
            self::CSV       => get_string('assignmentsource_csv', 'local_monlaututoria'),
            self::EXTERNAL  => get_string('assignmentsource_external', 'local_monlaututoria'),
            self::MIGRATION => get_string('assignmentsource_migration', 'local_monlaututoria'),
        ];
    }
}
