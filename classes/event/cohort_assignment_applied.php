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
 * Event triggered when a previewed cohort-based bulk assignment operation is
 * successfully applied (the "confirm" step cohort_assignment_preview_service's
 * own docblock names as phases 3C.3-3C.5). Same objecttable/base shape as
 * cohort_assignment_previewed — this is a distinct class, not a status change
 * on that one, since a preview and its eventual apply are separate audit
 * entries a coordinator may want to tell apart in the log.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_applied extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'local_tut_bulkoperation';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_bulkoperation', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public static function get_name() {
        return get_string('eventcohortassignmentapplied', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} applied cohort assignment operation id {$this->objectid}: "
            . "{$this->other['createdcount']} created, {$this->other['reassignedcount']} reassigned, "
            . "{$this->other['closedcount']} closed.";
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/assignments/index.php');
    }

    /**
     * @param int $operationid
     * @param int $userid
     * @param int $createdcount
     * @param int $reassignedcount
     * @param int $closedcount
     * @return self
     */
    public static function create_from_operation(
        int $operationid,
        int $userid,
        int $createdcount,
        int $reassignedcount,
        int $closedcount
    ): self {
        return static::create([
            'objectid' => $operationid,
            'context'  => \context_system::instance(),
            'userid'   => $userid,
            'other'    => [
                'createdcount'    => $createdcount,
                'reassignedcount' => $reassignedcount,
                'closedcount'     => $closedcount,
            ],
        ]);
    }
}
