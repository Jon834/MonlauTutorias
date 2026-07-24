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
 * Event triggered when a contact modality is deleted (phase 3E.5 — same gap,
 * and same fix, as reason_deleted/academic_year_deleted).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class modality_deleted extends catalogue_item_event_base {

    protected function get_crud_value(): string {
        return 'd';
    }

    protected function get_catalogue_table(): string {
        return 'local_tut_modality';
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_modality', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/modalities.php');
    }

    public static function get_name() {
        return get_string('eventmodalitydeleted', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} deleted the modality with id {$this->objectid} "
            . "(shortname: {$this->other['shortname']}).";
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param string $shortname the deleted row's shortname, kept because objectid
     *                           cannot be resolved back to anything after deletion
     * @return self
     */
    public static function create_from_id(int $objectid, int $userid, string $shortname): self {
        return self::build($objectid, $userid, ['shortname' => $shortname]);
    }
}
