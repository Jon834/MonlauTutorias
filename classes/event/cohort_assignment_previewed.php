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
 * Event triggered when a cohort-based bulk assignment preview is generated.
 * Its objecttable is local_tut_bulkoperation, unlike the assignment_* events
 * in this same namespace (which all share assignment_event_base fixed to
 * local_tut_assignment) — this one needs its own init()/get_objectid_mapping()
 * rather than reusing that base class.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_previewed extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'local_tut_bulkoperation';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_bulkoperation', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public static function get_name() {
        return get_string('eventcohortassignmentpreviewed', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} generated a cohort assignment preview (operation id {$this->objectid}) "
            . "for cohort id {$this->other['cohortid']} in academic year id {$this->other['academicyearid']}, "
            . "mode {$this->other['mode']}.";
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/assignments.php');
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $cohortid
     * @param int $academicyearid
     * @param string $mode one of cohort_sync_mode::values()
     * @param int $membercount aggregate only — never a student id list
     * @return self
     */
    public static function create_from_operation(
        int $operationid,
        int $userid,
        int $cohortid,
        int $academicyearid,
        string $mode,
        int $membercount
    ): self {
        return static::create([
            'objectid' => $operationid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => [
                'cohortid'       => $cohortid,
                'academicyearid' => $academicyearid,
                'mode'           => $mode,
                'membercount'    => $membercount,
            ],
        ]);
    }
}
