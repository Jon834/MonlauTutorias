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

namespace local_monlaututoria\event;

/**
 * Event triggered when one or more files are attached to a tutoring entry
 * (phase 5.6) — one event per upload batch, same criterion as entry_created
 * covering its own multi-row atomic operation.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_added extends entry_event_base {

    protected function get_crud_value(): string {
        return 'c';
    }

    public static function get_name() {
        return get_string('evententryattachmentadded', 'local_monlaututoria');
    }

    public function get_description() {
        $count = $this->other['count'];

        return "The user with id {$this->userid} attached {$count} file(s) to a tutoring entry (id {$this->objectid}) "
            . "for the student with id {$this->relateduserid}.";
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int $studentid
     * @param int $count
     * @param string $category
     * @return self
     */
    public static function create_from_id(int $objectid, int $userid, int $studentid, int $count, string $category): self {
        return self::build($objectid, $userid, $studentid, ['count' => $count, 'category' => $category]);
    }
}
