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
 * Stable codes for what actually happened to a CSV import row when applied
 * (phase 3D.3) — distinct from csv_import_row_status, which is the
 * pre-application classification computed at preview time (phase 3D.2).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_row_outcome {

    /** @var string a new assignment was created */
    public const CREATED = 'created';

    /** @var string the student's primary tutor was reassigned (conflict row, explicitly allowed) */
    public const REASSIGNED = 'reassigned';

    /** @var string the exact assignment already existed; nothing was written (idempotency) */
    public const NO_CHANGE = 'no_change';

    /** @var string a conflict row was left untouched because reassignment was not allowed */
    public const SKIPPED_CONFLICT = 'skipped_conflict';

    /** @var string an error row from the preview was never attempted */
    public const SKIPPED_ERROR = 'skipped_error';

    /** @var string a manually excluded row was never attempted */
    public const SKIPPED_EXCLUDED = 'skipped_excluded';

    /** @var string applying this row raised an unexpected error (partial_valid only) */
    public const FAILED = 'failed';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::CREATED, self::REASSIGNED, self::NO_CHANGE, self::SKIPPED_CONFLICT,
            self::SKIPPED_ERROR, self::SKIPPED_EXCLUDED, self::FAILED,
        ];
    }
}
