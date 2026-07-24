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
 * Immutable result of csv_import_apply_service::apply(). The detailed
 * per-row report/export (phase 3D.4) is not built yet; this is enough to
 * show a basic inline summary right after applying.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_result {

    /**
     * @param string $operationuuid
     * @param string $strategy one of csv_import_apply_strategy::values()
     * @param string $finalstatus one of bulk_operation_status::values()
     * @param csv_import_apply_result_row[] $rows
     */
    public function __construct(
        public readonly string $operationuuid,
        public readonly string $strategy,
        public readonly string $finalstatus,
        public readonly array $rows
    ) {
    }

    /**
     * @param string $outcome
     * @return int
     */
    public function count(string $outcome): int {
        return count(array_filter($this->rows, static fn ($row) => $row->outcome === $outcome));
    }
}
