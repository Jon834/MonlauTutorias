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
 * Stable, coded reasons for reassigning a student's primary tutor. Recorded
 * both in the student_reassigned event's "other" data (the audit trail) and,
 * since phase 4.2, persisted on the new local_tut_assignment row's
 * reassignreason column — the student file's history tab (phase 4.2) needs
 * to show it without querying the event log, the same reasoning that put
 * closereason on the row back in phase 3B.3A.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_reassign_reason {

    public const GROUP_CHANGE = 'group_change';
    public const LEVEL_CHANGE = 'level_change';
    public const ORG_CHANGE = 'org_change';
    public const TEMP_SUBSTITUTION = 'temp_substitution';
    public const TUTOR_LEFT = 'tutor_left';
    public const REORGANIZATION = 'reorganization';
    public const ADMIN_ERROR = 'admin_error';
    public const COORDINATION_REQUEST = 'coordination_request';
    public const OTHER = 'other';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::GROUP_CHANGE, self::LEVEL_CHANGE, self::ORG_CHANGE, self::TEMP_SUBSTITUTION,
            self::TUTOR_LEFT, self::REORGANIZATION, self::ADMIN_ERROR, self::COORDINATION_REQUEST, self::OTHER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::GROUP_CHANGE         => get_string('reassignreason_groupchange', 'local_monlaututoria'),
            self::LEVEL_CHANGE         => get_string('reassignreason_levelchange', 'local_monlaututoria'),
            self::ORG_CHANGE           => get_string('reassignreason_orgchange', 'local_monlaututoria'),
            self::TEMP_SUBSTITUTION    => get_string('reassignreason_tempsubstitution', 'local_monlaututoria'),
            self::TUTOR_LEFT           => get_string('reassignreason_tutorleft', 'local_monlaututoria'),
            self::REORGANIZATION       => get_string('reassignreason_reorganization', 'local_monlaututoria'),
            self::ADMIN_ERROR          => get_string('reassignreason_adminerror', 'local_monlaututoria'),
            self::COORDINATION_REQUEST => get_string('reassignreason_coordinationrequest', 'local_monlaututoria'),
            self::OTHER                => get_string('reassignreason_other', 'local_monlaututoria'),
        ];
    }
}
