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
 * Shared base for the 4 CSV import execution events (phase 3D.3): started,
 * completed, completed_with_errors, failed. All map to
 * local_tut_bulkoperation, same table as csv_import_previewed.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class csv_import_operation_event_base extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'local_tut_bulkoperation';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_bulkoperation', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/assignments/import.php');
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param array $other aggregate counts only — never a per-student list
     * @return static
     */
    protected static function build(int $operationid, int $userid, array $other = []): self {
        return static::create([
            'objectid' => $operationid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => $other,
        ]);
    }
}
