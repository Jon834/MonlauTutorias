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
 * Immutable result of parsing one data row of a CSV assignment import.
 * Purely syntactic: values are the raw (trimmed) cell strings, never resolved
 * against the database — that is phase 3D.2's job, reusing existing services
 * rather than duplicating validation here.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_row {

    /**
     * @param int $rownumber 1-based line number in the file (the header is line 1)
     * @param array<string, string> $values recognised column name => raw trimmed cell value
     * @param string[] $errors csv_import_error_code values detected for this row
     */
    public function __construct(
        public readonly int $rownumber,
        public readonly array $values,
        public readonly array $errors
    ) {
    }

    /**
     * @return bool
     */
    public function is_valid(): bool {
        return empty($this->errors);
    }
}
