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
 * Event triggered when an academic year is activated (deactivating any other).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_activated extends academic_year_event_base {

    protected function get_crud_value(): string {
        return 'u';
    }

    public static function get_name() {
        return get_string('eventacademicyearactivated', 'local_monlaututoria');
    }

    public function get_description() {
        $description = "The user with id {$this->userid} activated the academic year with id {$this->objectid}.";

        if (!empty($this->other['previousactiveid'])) {
            $description .= " Previously active academic year id: {$this->other['previousactiveid']}.";
        }

        return $description;
    }

    /**
     * previousactiveid references the same table as objectid, but is not yet
     * mapped for backup/restore: this plugin has not exercised backup/restore
     * so far, so this is left as an explicit gap rather than a guessed mapping.
     *
     * @return array
     */
    public static function get_other_mapping() {
        return ['previousactiveid' => \core\event\base::NOT_MAPPED];
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int|null $previousactiveid
     * @return self
     */
    public static function create_from_id(int $objectid, int $userid, ?int $previousactiveid): self {
        return self::build($objectid, $userid, ['previousactiveid' => $previousactiveid]);
    }
}
