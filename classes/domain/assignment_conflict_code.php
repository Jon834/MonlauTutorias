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
 * Stable codes for data-quality conflicts detected by
 * unassigned_students_service among a student's primary-type assignment rows.
 * Detection only — this service never corrects these automatically.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_conflict_code {

    /** @var string more than one primary row is simultaneously "vigente" */
    public const MULTIPLE_ACTIVE_PRIMARY = 'multiple_active_primary';

    /** @var string more than one not-yet-started primary row is scheduled */
    public const OVERLAPPING_FUTURE = 'overlapping_future';

    /** @var string two or more closed/cancelled primary rows overlap in time */
    public const DUPLICATE_HISTORICAL = 'duplicate_historical_primary';

    /** @var string a currently-active primary row's tutor account is deleted */
    public const DELETED_TUTOR_ACTIVE = 'deleted_tutor_active_primary';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::MULTIPLE_ACTIVE_PRIMARY, self::OVERLAPPING_FUTURE,
            self::DUPLICATE_HISTORICAL, self::DELETED_TUTOR_ACTIVE,
        ];
    }
}
