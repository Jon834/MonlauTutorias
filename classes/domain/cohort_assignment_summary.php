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
 * Immutable aggregate counts for a cohort_assignment_preview. This is the
 * only part of a preview that gets persisted (as summaryjson on
 * local_tut_bulkoperation) — see cohort_assignment_preview_service for why.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_summary {

    public function __construct(
        public readonly int $totalmembers,
        public readonly int $suspendedcount,
        public readonly int $deletedcount,
        public readonly int $tocreatecount,
        public readonly int $toreassigncount,
        public readonly int $tocreatecotutorcount,
        public readonly int $toclosecount,
        public readonly int $nochangecount,
        public readonly int $skippedcount,
        public readonly int $conflictcount
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function to_array(): array {
        return [
            'totalmembers'         => $this->totalmembers,
            'suspendedcount'       => $this->suspendedcount,
            'deletedcount'         => $this->deletedcount,
            'tocreatecount'        => $this->tocreatecount,
            'toreassigncount'      => $this->toreassigncount,
            'tocreatecotutorcount' => $this->tocreatecotutorcount,
            'toclosecount'         => $this->toclosecount,
            'nochangecount'        => $this->nochangecount,
            'skippedcount'         => $this->skippedcount,
            'conflictcount'        => $this->conflictcount,
        ];
    }

    /**
     * @param array<string, int> $data
     * @return self
     */
    public static function from_array(array $data): self {
        return new self(
            (int) ($data['totalmembers'] ?? 0),
            (int) ($data['suspendedcount'] ?? 0),
            (int) ($data['deletedcount'] ?? 0),
            (int) ($data['tocreatecount'] ?? 0),
            (int) ($data['toreassigncount'] ?? 0),
            (int) ($data['tocreatecotutorcount'] ?? 0),
            (int) ($data['toclosecount'] ?? 0),
            (int) ($data['nochangecount'] ?? 0),
            (int) ($data['skippedcount'] ?? 0),
            (int) ($data['conflictcount'] ?? 0)
        );
    }

    /**
     * Whether two summaries differ in any count — used to detect drift
     * between a stored preview and a freshly recomputed one.
     *
     * @param cohort_assignment_summary $other
     * @return bool
     */
    public function differs_from(self $other): bool {
        return $this->to_array() !== $other->to_array();
    }
}
