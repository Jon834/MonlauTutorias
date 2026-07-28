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
 * Event triggered when applying a cohort-based bulk assignment operation
 * fails and is rolled back — the whole operation is applied inside one
 * transaction (unlike CSV import, which offers an atomic_all/partial choice),
 * so any per-student failure here always means nothing at all was written.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_failed extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'local_tut_bulkoperation';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_bulkoperation', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public static function get_name() {
        return get_string('eventcohortassignmentapplyfailed', 'local_monlaututoria');
    }

    public function get_description() {
        return "Applying cohort assignment operation id {$this->objectid} failed and was rolled back, "
            . "for student id {$this->other['failedstudentid']}.";
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/assignments/index.php');
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int|null $failedstudentid the student whose write raised the error
     * @return self
     */
    public static function create_from_operation(int $operationid, int $userid, ?int $failedstudentid): self {
        return static::create([
            'objectid' => $operationid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => [
                'failedstudentid' => $failedstudentid,
            ],
        ]);
    }
}
