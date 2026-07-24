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

namespace local_monlaututoria\repository;

/**
 * Read-only access to Moodle core's cohort table.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_repository {

    /**
     * @return \stdClass[] keyed by id
     */
    public function get_all(): array {
        global $DB;

        return $DB->get_records('cohort', null, 'name ASC, id ASC');
    }

    /**
     * @param int[] $ids
     * @return \stdClass[] keyed by id
     */
    public function get_many(array $ids): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        return $DB->get_records_list('cohort', 'id', array_unique(array_map('intval', $ids)));
    }

    /**
     * @return int[]
     */
    public function get_all_ids(): array {
        return array_map('intval', array_keys($this->get_all()));
    }
}
