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
 * Shared base for reason and modality catalogue events. A single class cannot
 * be reused across both tables directly because get_objectid_mapping() (used
 * by backup/restore) must resolve to one fixed table per class; this base
 * only factors out the logic that IS identical, leaving objecttable/url to
 * the concrete leaf classes.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class catalogue_item_event_base extends \core\event\base {

    /**
     * @return string one of the base::CRUD_* single-char codes
     */
    abstract protected function get_crud_value(): string;

    /**
     * @return string the Moodle table name for this catalogue
     */
    abstract protected function get_catalogue_table(): string;

    protected function init() {
        $this->data['objecttable'] = $this->get_catalogue_table();
        $this->data['crud'] = $this->get_crud_value();
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Convenience factory shared by every leaf event class in this hierarchy.
     *
     * @param int $objectid
     * @param int $userid
     * @param array $other
     * @return static
     */
    protected static function build(int $objectid, int $userid, array $other = []): self {
        return static::create([
            'objectid' => $objectid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => $other,
        ]);
    }
}
