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
 * Immutable per-row result of applying a CSV import (phase 3D.3).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_result_row {

    /**
     * @param int $rownumber
     * @param string $outcome one of csv_import_row_outcome::values()
     * @param int|null $assignmentid the created/reassigned assignment id, when applicable
     * @param string|null $errormessagecode a lang string key, never a raw exception message
     * @param array<string, string> $values raw values from the source row, kept only so the
     *                                       phase 3D.4 error report can be built from the result
     *                                       alone, without re-reading the file a second time
     */
    public function __construct(
        public readonly int $rownumber,
        public readonly string $outcome,
        public readonly ?int $assignmentid,
        public readonly ?string $errormessagecode,
        public readonly array $values = []
    ) {
    }
}
