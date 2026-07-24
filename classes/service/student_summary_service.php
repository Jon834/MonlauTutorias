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

namespace local_monlaututoria\service;

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\student_summary;

/**
 * Assembles the "cabecera y resumen" of a student's longitudinal file (phase
 * 4.1): current primary tutor, co-tutors, last assignment, and any
 * not-yet-started one, all scoped to a single academic year. Reuses
 * assignment_repository's existing queries — find_active_primary()/
 * find_active_cotutors() already power reassign_primary_tutor() (3B.4A) and
 * assignments/create.php's duplicate checks — rather than duplicating them.
 *
 * Does not check capabilities or scope itself, same convention as every
 * other service in this plugin: student/view.php is responsible for
 * require_capability() and scope_service::require_user_can_access_student()
 * before calling this.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class student_summary_service {

    /** @var assignment_repository */
    private $repository;

    public function __construct(?assignment_repository $repository = null) {
        $this->repository = $repository ?? new assignment_repository();
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @return student_summary
     */
    public function get_summary(int $studentid, int $academicyearid): student_summary {
        $primary = $this->repository->find_active_primary($studentid, $academicyearid);
        $cotutors = array_values($this->repository->find_active_cotutors($studentid, $academicyearid));

        $yearrows = $this->repository->find_by_student($studentid, $academicyearid);

        // find_by_student() is ordered ASC by timestart, so the last element
        // is the most recently started assignment of any type/status.
        $last = !empty($yearrows) ? end($yearrows) : null;

        $now = time();
        $upcoming = array_values(array_filter(
            $yearrows,
            static fn (\stdClass $row) => $row->status === assignment_status::ACTIVE && (int) $row->timestart > $now
        ));

        return new student_summary($studentid, $academicyearid, $primary, $cotutors, $last, $upcoming);
    }
}
