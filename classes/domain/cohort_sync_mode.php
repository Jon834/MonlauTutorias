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
 * Synchronisation modes for cohort-based bulk assignment (phase 3C). Only
 * classification (preview) is implemented for any of these in phase 3C.1 —
 * actually applying add_only/add_and_close_missing/replace_primary is phases
 * 3C.3-3C.5.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_sync_mode {

    /** @var string computes the preview only, never writes */
    public const PREVIEW_ONLY = 'preview_only';

    /** @var string creates missing assignments; never closes or reassigns existing ones */
    public const ADD_ONLY = 'add_only';

    /** @var string add_only, plus closes cohort-sourced assignments for students no longer in the cohort */
    public const ADD_AND_CLOSE_MISSING = 'add_and_close_missing';

    /** @var string replaces the current primary tutor via reassign_primary_tutor(); high-risk, never default */
    public const REPLACE_PRIMARY = 'replace_primary';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PREVIEW_ONLY, self::ADD_ONLY, self::ADD_AND_CLOSE_MISSING, self::REPLACE_PRIMARY];
    }
}
