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
 * Stable codes explaining why a CSV import row got the status it did, once
 * resolved against the database (phase 3D.2). Distinct from
 * csv_import_error_code, which covers the earlier, purely syntactic parsing
 * stage (phase 3D.1) — a row can carry codes from both stages.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_message_code {

    public const STUDENT_NOT_FOUND = 'student_not_found';
    public const STUDENT_SUSPENDED = 'student_suspended';
    public const STUDENT_SELF_TUTOR = 'student_self_tutor';
    public const TUTOR_NOT_FOUND = 'tutor_not_found';
    public const TUTOR_SUSPENDED = 'tutor_suspended';
    public const ACADEMICYEAR_NOT_FOUND = 'academicyear_not_found';
    public const ACADEMICYEAR_LOCKED = 'academicyear_locked';
    public const COHORT_NOT_FOUND = 'cohort_not_found';
    public const DUPLICATE_ACTIVE = 'duplicate_active';
    public const PRIMARY_CONFLICT = 'primary_conflict';
    public const ROW_EXCLUDED = 'row_excluded';
}
