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
 * Stable codes for problems detected while parsing a CSV assignment import
 * (phase 3D.1). File/header-level codes abort the whole parse; row-level
 * codes are attached to the specific csv_import_row.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_error_code {

    // File/header-level.
    public const EMPTY_FILE = 'empty_file';
    public const MISSING_REQUIRED_HEADER = 'missing_required_header';
    public const UNKNOWN_COLUMN = 'unknown_column';

    // Row-level.
    public const COLUMN_COUNT_MISMATCH = 'column_count_mismatch';
    public const MISSING_STUDENT = 'missing_student';
    public const MISSING_TUTOR = 'missing_tutor';
    public const MISSING_ACADEMICYEAR = 'missing_academicyear';
    public const INVALID_ISPRIMARY = 'invalid_isprimary';
    public const INVALID_TIMESTART = 'invalid_timestart';
    public const INVALID_TIMEEND = 'invalid_timeend';
    public const INVALID_ASSIGNMENTTYPE = 'invalid_assignmenttype';
    public const INVALID_SOURCE = 'invalid_source';
    public const DUPLICATE_ROW = 'duplicate_row';
}
