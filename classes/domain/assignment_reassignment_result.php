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
 * Immutable outcome of assignment_service::reassign_primary_tutor(), returned
 * only after the transaction has committed successfully.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_reassignment_result {

    /**
     * @param int $previousassignmentid
     * @param int $previoustutorid
     * @param int $newassignmentid
     * @param int $newtutorid
     * @param int $effectivedate
     * @param int[] $keptcotutorids
     * @param int[] $closedcotutorids
     */
    public function __construct(
        public readonly int $previousassignmentid,
        public readonly int $previoustutorid,
        public readonly int $newassignmentid,
        public readonly int $newtutorid,
        public readonly int $effectivedate,
        public readonly array $keptcotutorids,
        public readonly array $closedcotutorids
    ) {
    }
}
