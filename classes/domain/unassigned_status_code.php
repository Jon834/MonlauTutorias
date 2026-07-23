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
 * Stable codes classifying why a student does or does not have an active
 * primary tutor, as computed by unassigned_students_service. No lang strings
 * yet — no UI consumes these labels until phase 3B.5B.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class unassigned_status_code {

    /** @var string the student has a vigente primary tutor; not actually "unassigned" */
    public const HAS_ACTIVE_PRIMARY = 'has_active_primary';

    /** @var string no primary-type row at all for this student/academic year */
    public const NEVER_ASSIGNED = 'never_assigned';

    /** @var string the most recent primary row is closed or cancelled */
    public const PREVIOUS_CLOSED = 'previous_closed';

    /** @var string only a not-yet-started (future) primary row exists */
    public const FUTURE_PENDING = 'future_pending';

    /** @var string a primary row is still status=active but its timeend has already passed */
    public const EXPIRED_ACTIVE = 'expired_active';

    /** @var string one or more data-quality conflicts were detected; takes priority over the others */
    public const DATA_CONFLICT = 'data_conflict';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::HAS_ACTIVE_PRIMARY, self::NEVER_ASSIGNED, self::PREVIOUS_CLOSED,
            self::FUTURE_PENDING, self::EXPIRED_ACTIVE, self::DATA_CONFLICT,
        ];
    }
}
