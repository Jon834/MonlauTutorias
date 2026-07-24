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
 * Immutable aggregate counts for a csv_import_preview. The only part of a
 * CSV preview that gets persisted (as summaryjson on local_tut_bulkoperation).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_preview_summary {

    public function __construct(
        public readonly int $totalrows,
        public readonly int $validcount,
        public readonly int $warningcount,
        public readonly int $conflictcount,
        public readonly int $errorcount,
        public readonly int $excludedcount
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function to_array(): array {
        return [
            'totalrows'     => $this->totalrows,
            'validcount'    => $this->validcount,
            'warningcount'  => $this->warningcount,
            'conflictcount' => $this->conflictcount,
            'errorcount'    => $this->errorcount,
            'excludedcount' => $this->excludedcount,
        ];
    }

    /**
     * @param array<string, int> $data
     * @return self
     */
    public static function from_array(array $data): self {
        return new self(
            (int) ($data['totalrows'] ?? 0),
            (int) ($data['validcount'] ?? 0),
            (int) ($data['warningcount'] ?? 0),
            (int) ($data['conflictcount'] ?? 0),
            (int) ($data['errorcount'] ?? 0),
            (int) ($data['excludedcount'] ?? 0)
        );
    }
}
