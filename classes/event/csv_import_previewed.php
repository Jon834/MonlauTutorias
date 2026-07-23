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
 * Event triggered when a CSV assignment import preview is generated. Its
 * objecttable is local_tut_bulkoperation, same as cohort_assignment_previewed.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_previewed extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'local_tut_bulkoperation';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_bulkoperation', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public static function get_name() {
        return get_string('eventcsvimportpreviewed', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} generated a CSV import preview (operation id {$this->objectid}) "
            . "with {$this->other['totalrows']} rows.";
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/assignments/import.php');
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $totalrows aggregate only — never a student id list
     * @param int $validcount
     * @param int $errorcount
     * @return self
     */
    public static function create_from_operation(
        int $operationid,
        int $userid,
        int $totalrows,
        int $validcount,
        int $errorcount
    ): self {
        return static::create([
            'objectid' => $operationid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => [
                'totalrows'  => $totalrows,
                'validcount' => $validcount,
                'errorcount' => $errorcount,
            ],
        ]);
    }
}
