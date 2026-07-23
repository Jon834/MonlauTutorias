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
 * Valid values for local_tut_bulkoperation.status.
 *
 * Only DRAFT and PREVIEWED are produced by phase 3C.1 (preview only, no
 * execution yet). The rest are part of the schema but have no producer until
 * phases 3C.2-3C.6 — same "reserved for a later phase" pattern already used
 * for assignment_status::PENDING/CANCELLED since phase 3A.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class bulk_operation_status {

    public const DRAFT = 'draft';
    public const PREVIEWED = 'previewed';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const COMPLETED_WITH_ERRORS = 'completed_with_errors';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::DRAFT, self::PREVIEWED, self::CONFIRMED, self::PROCESSING,
            self::COMPLETED, self::COMPLETED_WITH_ERRORS, self::FAILED, self::CANCELLED,
        ];
    }
}
