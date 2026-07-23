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
 * Stable action codes classifying what a cohort bulk assignment operation
 * would do (phase 3C.1) or did do (phases 3C.3+) for a single student.
 * Never store only the translated label — the code is the stable contract.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_action {

    public const CREATE_PRIMARY = 'create_primary';
    public const CREATE_COTUTOR = 'create_cotutor';
    public const SKIP_EXISTING = 'skip_existing';
    public const SKIP_SUSPENDED = 'skip_suspended';
    public const SKIP_INVALID = 'skip_invalid';
    public const CONFLICT_PRIMARY = 'conflict_primary';
    public const REASSIGN_PRIMARY = 'reassign_primary';
    public const CLOSE_MISSING = 'close_missing';
    public const NO_CHANGE = 'no_change';
    public const ERROR = 'error';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::CREATE_PRIMARY, self::CREATE_COTUTOR, self::SKIP_EXISTING, self::SKIP_SUSPENDED,
            self::SKIP_INVALID, self::CONFLICT_PRIMARY, self::REASSIGN_PRIMARY, self::CLOSE_MISSING,
            self::NO_CHANGE, self::ERROR,
        ];
    }
}
