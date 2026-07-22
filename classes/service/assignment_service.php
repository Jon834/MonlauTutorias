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
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\assignment_source;
use local_monlaututoria\event\assignment_created;
use local_monlaututoria\event\assignment_closed;
use local_monlaututoria\event\student_reassigned;
use local_monlaututoria\event\co_tutor_added;
use local_monlaututoria\event\co_tutor_removed;

/**
 * Application service enforcing the business rules for tutor-student
 * assignments: self-assignment, duplicates, dates, deleted/suspended users,
 * locked academic years, single active primary tutor, atomic reassignment.
 * Pages/forms must go through this class, never call assignment_repository
 * directly for writes.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_service {

    /** @var assignment_repository */
    private $repository;

    /** @var academic_year_repository */
    private $academicyearrepository;

    public function __construct(
        ?assignment_repository $repository = null,
        ?academic_year_repository $academicyearrepository = null
    ) {
        $this->repository = $repository ?? new assignment_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
    }

    /**
     * Creates a new assignment.
     *
     * @param \stdClass $data must contain studentid, tutorid, academicyearid; may contain
     *                        cohortid, assignmenttype, isprimary, timestart, timeend, source, externalid
     * @param int $userid
     * @param bool $allowsuspended whether to allow a suspended student/tutor (caller must have
     *                             shown an explicit warning; there is no settings page yet)
     * @param bool $canoverridelock whether the caller holds local/monlaututoria:overridelock
     * @return int the new id
     */
    public function create(\stdClass $data, int $userid, bool $allowsuspended = false, bool $canoverridelock = false): int {
        $studentid = (int) $data->studentid;
        $tutorid = (int) $data->tutorid;
        $academicyearid = (int) $data->academicyearid;
        $assignmenttype = $data->assignmenttype ?? assignment_type::PRIMARY;
        $isprimary = !empty($data->isprimary);

        $this->validate_not_self($studentid, $tutorid);
        $this->validate_type($assignmenttype);
        $this->validate_isprimary_type_match($isprimary, $assignmenttype);

        $student = $this->validate_user($studentid, 'error_assignment_invalid_student');
        $tutor = $this->validate_user($tutorid, 'error_assignment_invalid_tutor');
        $this->validate_not_suspended($student, $allowsuspended, 'error_assignment_student_suspended');
        $this->validate_not_suspended($tutor, $allowsuspended, 'error_assignment_tutor_suspended');

        $this->validate_academic_year($academicyearid, $canoverridelock);

        if (!empty($data->cohortid)) {
            $this->validate_cohort((int) $data->cohortid);
        }

        $timestart = isset($data->timestart) ? (int) $data->timestart : time();
        $timeend = isset($data->timeend) ? (int) $data->timeend : null;
        $this->validate_dates($timestart, $timeend);

        if ($this->repository->has_active_duplicate($studentid, $tutorid, $academicyearid, $assignmenttype)) {
            throw new \moodle_exception('error_assignment_duplicate', 'local_monlaututoria');
        }

        if ($isprimary && $this->repository->count_active_primary($studentid, $academicyearid) > 0) {
            throw new \moodle_exception('error_assignment_primary_duplicate', 'local_monlaututoria');
        }

        $data->studentid = $studentid;
        $data->tutorid = $tutorid;
        $data->academicyearid = $academicyearid;
        $data->assignmenttype = $assignmenttype;
        $data->isprimary = $isprimary;
        $data->timestart = $timestart;
        $data->timeend = $timeend;
        $data->source = $data->source ?? assignment_source::MANUAL;
        $data->createdby = $userid;

        $id = $this->repository->create($data);

        if ($assignmenttype === assignment_type::CO_TUTOR) {
            co_tutor_added::create_from_id($id, $userid, $studentid, $tutorid, $academicyearid)->trigger();
        } else {
            assignment_created::create_from_id($id, $userid, $studentid, $tutorid, $academicyearid, $data->source)->trigger();
        }

        return $id;
    }

    /**
     * Closes an assignment.
     *
     * @param int $id
     * @param int $userid
     * @param int|null $timeend defaults to now
     * @return bool
     */
    public function close(int $id, int $userid, ?int $timeend = null): bool {
        $existing = $this->repository->get($id);

        if ($existing->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_already_closed', 'local_monlaututoria');
        }

        $timeend = $timeend ?? time();
        $result = $this->repository->close($id, $userid, $timeend);

        if ($existing->assignmenttype === assignment_type::CO_TUTOR) {
            co_tutor_removed::create_from_id($id, $userid, (int) $existing->studentid)->trigger();
        } else {
            assignment_closed::create_from_id($id, $userid, (int) $existing->studentid)->trigger();
        }

        return $result;
    }

    /**
     * Closes an active co-tutor assignment specifically, guarding that the
     * target row really is one (close() alone would also accept a primary row).
     *
     * @param int $id
     * @param int $userid
     * @param int|null $timeend
     * @return bool
     */
    public function remove_cotutor(int $id, int $userid, ?int $timeend = null): bool {
        $existing = $this->repository->get($id);

        if ($existing->assignmenttype !== assignment_type::CO_TUTOR || $existing->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_not_active_cotutor', 'local_monlaututoria');
        }

        $timeend = $timeend ?? time();
        $result = $this->repository->close($id, $userid, $timeend);

        co_tutor_removed::create_from_id($id, $userid, (int) $existing->studentid)->trigger();

        return $result;
    }

    /**
     * Adds a co-tutor for a student. Thin wrapper around create() fixing
     * assignmenttype=co_tutor, isprimary=false.
     *
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @param int $userid
     * @param int|null $timestart
     * @param int|null $timeend
     * @param bool $allowsuspended
     * @param bool $canoverridelock
     * @return int
     */
    public function add_cotutor(
        int $studentid,
        int $tutorid,
        int $academicyearid,
        int $userid,
        ?int $timestart = null,
        ?int $timeend = null,
        bool $allowsuspended = false,
        bool $canoverridelock = false
    ): int {
        $data = (object) [
            'studentid'      => $studentid,
            'tutorid'        => $tutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => assignment_type::CO_TUTOR,
            'isprimary'      => false,
            'timestart'      => $timestart,
            'timeend'        => $timeend,
        ];

        return $this->create($data, $userid, $allowsuspended, $canoverridelock);
    }

    /**
     * Reassigns a student to a new primary tutor: closes the current active
     * primary assignment and creates a new one, atomically. Fires a single
     * student_reassigned event for the whole operation (never separate
     * assignment_closed/assignment_created events for this action).
     *
     * The previous tutor's operational access is revoked implicitly: scope_service
     * only grants access over "vigente" assignments, and access was never
     * granted through a Moodle role tied to the assignment, so there is
     * nothing else to unassign here (see docs/seguridad-permisos.md).
     *
     * @param int $studentid
     * @param int $newtutorid
     * @param int $academicyearid
     * @param int $userid
     * @param int|null $effectivedate defaults to now
     * @param bool $keepcotutors if false, also closes existing co-tutor rows (each fires co_tutor_removed)
     * @param bool $allowsuspended
     * @param bool $canoverridelock
     * @return int the new assignment's id
     */
    public function reassign(
        int $studentid,
        int $newtutorid,
        int $academicyearid,
        int $userid,
        ?int $effectivedate = null,
        bool $keepcotutors = true,
        bool $allowsuspended = false,
        bool $canoverridelock = false
    ): int {
        global $DB;

        $this->validate_not_self($studentid, $newtutorid);

        $current = $this->repository->find_active_primary($studentid, $academicyearid);
        if ($current === null) {
            throw new \moodle_exception('error_assignment_no_active_primary', 'local_monlaututoria');
        }

        if ((int) $current->tutorid === $newtutorid) {
            throw new \moodle_exception('error_assignment_reassign_same_tutor', 'local_monlaututoria');
        }

        $student = $this->validate_user($studentid, 'error_assignment_invalid_student');
        $tutor = $this->validate_user($newtutorid, 'error_assignment_invalid_tutor');
        $this->validate_not_suspended($student, $allowsuspended, 'error_assignment_student_suspended');
        $this->validate_not_suspended($tutor, $allowsuspended, 'error_assignment_tutor_suspended');
        $this->validate_academic_year($academicyearid, $canoverridelock);

        $effectivedate = $effectivedate ?? time();

        $transaction = $DB->start_delegated_transaction();

        $this->repository->close((int) $current->id, $userid, $effectivedate);

        $newid = $this->repository->create((object) [
            'studentid'      => $studentid,
            'tutorid'        => $newtutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => assignment_type::PRIMARY,
            'isprimary'      => true,
            'timestart'      => $effectivedate,
            'source'         => assignment_source::MANUAL,
            'createdby'      => $userid,
        ]);

        $closedcotutorids = [];
        if (!$keepcotutors) {
            foreach ($this->repository->find_active_cotutors($studentid, $academicyearid) as $cotutor) {
                $this->repository->close((int) $cotutor->id, $userid, $effectivedate);
                $closedcotutorids[] = (int) $cotutor->id;
            }
        }

        $transaction->allow_commit();

        student_reassigned::create_from_id(
            $newid,
            $userid,
            $studentid,
            (int) $current->tutorid,
            (int) $current->id,
            $academicyearid
        )->trigger();

        foreach ($closedcotutorids as $cotutorid) {
            co_tutor_removed::create_from_id($cotutorid, $userid, $studentid)->trigger();
        }

        return $newid;
    }

    /**
     * @param int $studentid
     * @param int $tutorid
     */
    private function validate_not_self(int $studentid, int $tutorid): void {
        if ($studentid === $tutorid) {
            throw new \moodle_exception('error_assignment_self', 'local_monlaututoria');
        }
    }

    /**
     * @param string $assignmenttype
     */
    private function validate_type(string $assignmenttype): void {
        if (!in_array($assignmenttype, assignment_type::values(), true)) {
            throw new \moodle_exception('error_assignment_invalid_type', 'local_monlaututoria');
        }
    }

    /**
     * @param bool $isprimary
     * @param string $assignmenttype
     */
    private function validate_isprimary_type_match(bool $isprimary, string $assignmenttype): void {
        if ($isprimary && $assignmenttype !== assignment_type::PRIMARY) {
            throw new \moodle_exception('error_assignment_isprimary_type_mismatch', 'local_monlaututoria');
        }
    }

    /**
     * Validates the user exists and is not deleted. Deleted is always
     * blocking, unlike suspended: there is no override parameter for it.
     *
     * @param int $userid
     * @param string $errorkey
     * @return \stdClass
     */
    private function validate_user(int $userid, string $errorkey): \stdClass {
        $user = \core_user::get_user($userid);
        if (!$user || !empty($user->deleted)) {
            throw new \moodle_exception($errorkey, 'local_monlaututoria');
        }

        return $user;
    }

    /**
     * @param \stdClass $user
     * @param bool $allowsuspended
     * @param string $errorkey
     */
    private function validate_not_suspended(\stdClass $user, bool $allowsuspended, string $errorkey): void {
        if (!empty($user->suspended) && !$allowsuspended) {
            throw new \moodle_exception($errorkey, 'local_monlaututoria');
        }
    }

    /**
     * @param int $academicyearid
     * @param bool $canoverridelock
     * @return \stdClass
     */
    private function validate_academic_year(int $academicyearid, bool $canoverridelock): \stdClass {
        try {
            $year = $this->academicyearrepository->get($academicyearid);
        } catch (\dml_missing_record_exception $e) {
            throw new \moodle_exception('error_assignment_academicyear_invalid', 'local_monlaututoria');
        }

        if (!empty($year->locked) && !$canoverridelock) {
            throw new \moodle_exception('error_assignment_academicyear_locked', 'local_monlaututoria');
        }

        return $year;
    }

    /**
     * @param int $cohortid
     */
    private function validate_cohort(int $cohortid): void {
        global $DB;

        if (!$DB->record_exists('cohort', ['id' => $cohortid])) {
            throw new \moodle_exception('error_assignment_invalid_cohort', 'local_monlaututoria');
        }
    }

    /**
     * @param int $timestart
     * @param int|null $timeend
     */
    private function validate_dates(int $timestart, ?int $timeend): void {
        if ($timeend !== null && $timeend < $timestart) {
            throw new \moodle_exception('error_assignment_dates_invalid', 'local_monlaututoria');
        }
    }
}
