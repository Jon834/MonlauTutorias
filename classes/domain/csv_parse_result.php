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
 * Immutable result of csv_import_parser_service::parse(). When $fileerrors is
 * non-empty, $rows is always empty: a header/encoding-level problem makes the
 * whole file unusable, so parsing does not proceed to individual rows.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_parse_result {

    /**
     * @param string[] $headers recognised column names, in file order
     * @param csv_import_row[] $rows
     * @param string[] $fileerrors csv_import_error_code values that abort the whole file
     */
    public function __construct(
        public readonly array $headers,
        public readonly array $rows,
        public readonly array $fileerrors
    ) {
    }

    /**
     * @return bool
     */
    public function is_usable(): bool {
        return empty($this->fileerrors);
    }

    /**
     * @return csv_import_row[]
     */
    public function valid_rows(): array {
        return array_values(array_filter($this->rows, static fn (csv_import_row $row) => $row->is_valid()));
    }

    /**
     * @return csv_import_row[]
     */
    public function invalid_rows(): array {
        return array_values(array_filter($this->rows, static fn (csv_import_row $row) => !$row->is_valid()));
    }
}
