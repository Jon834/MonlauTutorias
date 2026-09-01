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
     * 1. $userid IS $studentid, holding local/monlaututoria:viewownfile ->
     *    true (phase 4.3: a student's access to their own longitudinal file
     *    is unconditional on tutoring relationships — they are not "their
     *    own tutor" — and deliberately checked before every other branch,
     *    including viewallassignments, since it does not depend on it).
     * 2. local/monlaututoria:viewallassignments -> true (global/administrative
     *    access; also the minimal "extended coordination scope" for this
     *    phase, since there is no scope-configuration page yet).
     * 3. No local/monlaututoria:viewownstudents and no viewallassignments -> false.
     * 4. A current ("vigente") primary or co-tutor assignment -> true.
     *    support/orientation/other assignment types do NOT grant access.
     * 5. Otherwise, with local/monlaututoria:viewhistoricalassignments, a past
     *    relationship of $userid with THIS student (any status) -> true. This
     *    is narrow by design: it grants access to one's own tutoring history,
     *    not a global audit capability over any student.
     * 6. Otherwise -> false.
     *
     * @param int $userid
     * @param int $studentid
     * @param int|null $academicyearid
     * @return bool
     */
    public function can_user_access_student(int $userid, int $studentid, ?int $academicyearid = null): bool {
        $context = \context_system::instance();

        if ($userid === $studentid && has_capability('local/monlaututoria:viewownfile', $context, $userid)) {
            return true;
        }

        if (has_capability('local/monlaututoria:viewallassignments', $context, $userid)) {
            return true;
        }

        // Fase 13 — in simple mode an assignment is the whole authorisation
        // (no viewownstudents role needed); in full mode the capability is
        // still required.
        $simplemode = \local_monlaututoria\feature::simple_mode();
        if (!$simplemode && !has_capability('local/monlaututoria:viewownstudents', $context, $userid)) {
            return false;
        }

        // The current primary tutor or co-tutor of this student can see the
        // student's WHOLE longitudinal file — earlier academic years and
        // tutorías recorded by previous tutors included. The file belongs to
        // the student, not to a course or a year (docs/requisitos-
        // funcionales.md). The null (not $academicyearid) is the point: "is
        // $userid currently this student's tutor at all", not "…in the year
        // being viewed".
        if ($this->repository->is_current_tutor_of_student($userid, $studentid, null)) {
            return true;
        }

        // A PAST primary tutor or co-tutor of this student — the relationship
        // has ended. They keep a narrow access: only the tutorías they
        // recorded themselves (enforced by entry_service, which restricts the
        // listing to their own entries for a historical-only viewer). In full
        // mode this still needs viewhistoricalassignments; in simple mode a
        // past assignment is enough.
        if (($simplemode || has_capability('local/monlaututoria:viewhistoricalassignments', $context, $userid))
            && $this->repository->has_historical_relationship($userid, $studentid, $academicyearid)) {
            return true;
        }

        return false;
    }

    /**
     * Whether $userid can see $studentid ONLY because of a past (ended)
     * tutoring relationship — not as an admin, not as the current tutor. Such
     * a viewer gets a narrowed view: only the tutorías they recorded
     * themselves (entry_service forces a tutorid filter), and only the
     * "Tutorías" tab of the ficha.
     *
     * @param int $userid
     * @param int $studentid
     * @return bool
     */
    public function access_is_historical_only(int $userid, int $studentid): bool {
        $context = \context_system::instance();

        if (has_capability('local/monlaututoria:viewallassignments', $context, $userid)) {
            return false;
        }
        if ($this->repository->is_current_tutor_of_student($userid, $studentid, null)) {
            return false;
        }

        return $this->repository->has_historical_relationship($userid, $studentid);
    }

    /**
     * Whether $userid should get the tutor UI at all (the panel, the block's
     * tutor section, the "Asignaciones"-free tutor navigation). True when they
     * hold local/monlaututoria:viewownstudents or viewallassignments, OR —
     * in simple mode only — when they are currently the tutor of at least one
     * student (fase 13: an assignment makes you a tutor, no role needed).
     * Per-student access is still decided by can_user_access_student().
     *
     * @param int $userid
     * @return bool
     */
    public function user_is_tutor(int $userid): bool {
        $context = \context_system::instance();

        if (has_any_capability(
            ['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments'],
            $context,
            $userid
        )) {
            return true;
        }

        // In simple mode, currently OR formerly having students assigned is
        // enough: a former tutor still needs to reach the tutorías they
        // recorded (their "alumnos que tutoricé antes"). can_user_access_
        // student() keeps the per-student access as narrow as ever.
        return \local_monlaututoria\feature::simple_mode()
            && $this->repository->has_any_tutoring_ever($userid);
    }

    /**
     * Whether $userid may CREATE tutorías — a stricter check than
     * user_is_tutor(): a former tutor (no current assignments) can read what
     * they recorded but must not write new entries. True for the read
     * capabilities' holders, or — in simple mode — a current tutor of at
     * least one student.
     *
     * @param int $userid
     * @return bool
     */
    public function user_is_current_tutor(int $userid): bool {
        $context = \context_system::instance();

        if (has_any_capability(
            ['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments'],
            $context,
            $userid
        )) {
            return true;
        }

        return \local_monlaututoria\feature::simple_mode()
            && $this->repository->has_any_current_tutoring($userid);
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
