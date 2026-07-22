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

/**
 * Single point of truth for "can this user access this student's tutoring
 * data". Every page and service that exposes student-level data must call
 * require_user_can_access_student() before returning anything — a general
 * capability alone never authorises access to a specific student.
 *
 * Unlike academic_year_service (which receives already-resolved capability
 * booleans from the calling page), this service deliberately calls
 * has_capability() itself: docs/seguridad-permisos.md requires capability +
 * context + scope + current assignment to be checked together as a single
 * unit, which is exactly what this class exists to encapsulate.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class scope_service {

    /** @var assignment_repository */
    private $repository;

    public function __construct(?assignment_repository $repository = null) {
        $this->repository = $repository ?? new assignment_repository();
    }

    /**
     * Whether $userid may access $studentid's tutoring data.
     *
     * Authentication itself is the calling page's responsibility
     * (require_login()); this service assumes $userid is already a valid,
     * logged-in user id.
     *
     * Order of checks:
     * 1. local/monlaututoria:viewallassignments -> true (global/administrative
     *    access; also the minimal "extended coordination scope" for this
     *    phase, since there is no scope-configuration page yet).
     * 2. No local/monlaututoria:viewownstudents and no viewallassignments -> false.
     * 3. A current ("vigente") primary or co-tutor assignment -> true.
     *    support/orientation/other assignment types do NOT grant access.
     * 4. Otherwise, with local/monlaututoria:viewhistoricalassignments, a past
     *    relationship of $userid with THIS student (any status) -> true. This
     *    is narrow by design: it grants access to one's own tutoring history,
     *    not a global audit capability over any student.
     * 5. Otherwise -> false.
     *
     * @param int $userid
     * @param int $studentid
     * @param int|null $academicyearid
     * @return bool
     */
    public function can_user_access_student(int $userid, int $studentid, ?int $academicyearid = null): bool {
        $context = \context_system::instance();

        if (has_capability('local/monlaututoria:viewallassignments', $context, $userid)) {
            return true;
        }

        if (!has_capability('local/monlaututoria:viewownstudents', $context, $userid)) {
            return false;
        }

        if ($this->repository->is_current_tutor_of_student($userid, $studentid, $academicyearid)) {
            return true;
        }

        if (has_capability('local/monlaututoria:viewhistoricalassignments', $context, $userid)
            && $this->repository->has_historical_relationship($userid, $studentid, $academicyearid)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $userid
     * @param int $studentid
     * @param int|null $academicyearid
     */
    public function require_user_can_access_student(int $userid, int $studentid, ?int $academicyearid = null): void {
        if (!$this->can_user_access_student($userid, $studentid, $academicyearid)) {
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }
    }
}
