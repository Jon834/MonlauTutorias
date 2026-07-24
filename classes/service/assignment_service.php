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
use local_monlaututoria\domain\assignment_close_reason;
use local_monlaututoria\domain\assignment_reassign_reason;
use local_monlaututoria\domain\reassign_assignment_command;
use local_monlaututoria\domain\assignment_reassignment_result;
use local_monlaututoria\event\assignment_created;
use local_monlaututoria\event\assignment_updated;
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
     * Updates the editable fields of an assignment: cohortid, timestart,
     * timeend, note. Never touches studentid, tutorid, assignmenttype,
     * isprimary or status — changing tutor is a reassignment, changing
     * status is a close/cancel action, both separate flows.
     *
     * Editing a non-active (closed/cancelled) assignment additionally
     * requires a non-empty $reason, on top of manageclosedassignments: this
     * is a historical-record correction, so the "why" is recorded in the
     * assignment_updated event rather than silently overwriting history.
     *
     * @param int $id
     * @param \stdClass $data may contain cohortid, timestart, timeend, note
     * @param int $userid
     * @param bool $canmanageclosed whether the caller holds local/monlaututoria:manageclosedassignments
     *                              (required to edit a non-active assignment)
     * @param bool $canoverridelock whether the caller holds local/monlaututoria:overridelock
     * @param string|null $reason required when the assignment being edited is not active
     * @return bool
     */
    public function update(
        int $id,
        \stdClass $data,
        int $userid,
        bool $canmanageclosed = false,
        bool $canoverridelock = false,
        ?string $reason = null
    ): bool {
        $existing = $this->repository->get($id);
        $editingclosed = $existing->status !== assignment_status::ACTIVE;

        if ($editingclosed) {
            if (!$canmanageclosed) {
                throw new \moodle_exception('error_assignment_closed_no_permission', 'local_monlaututoria');
            }
            if (trim((string) $reason) === '') {
                throw new \moodle_exception('error_assignment_edit_reason_required', 'local_monlaututoria');
            }
        }

        $this->validate_academic_year((int) $existing->academicyearid, $canoverridelock);

        $timestart = property_exists($data, 'timestart') && $data->timestart !== null
            ? (int) $data->timestart
            : (int) $existing->timestart;
        $timeend = property_exists($data, 'timeend')
            ? (!empty($data->timeend) ? (int) $data->timeend : null)
            : (!empty($existing->timeend) ? (int) $existing->timeend : null);
        $this->validate_dates($timestart, $timeend);

        if (!empty($data->cohortid)) {
            $this->validate_cohort((int) $data->cohortid);
        }

        $newcohortid = $data->cohortid ?? null;
        $newnote = $data->note ?? null;

        $fieldschanged = [];
        if ((int) ($existing->cohortid ?? 0) !== (int) ($newcohortid ?? 0)) {
            $fieldschanged[] = 'cohortid';
        }
        if ((int) $existing->timestart !== $timestart) {
            $fieldschanged[] = 'timestart';
        }
        if ((int) ($existing->timeend ?? 0) !== (int) ($timeend ?? 0)) {
            $fieldschanged[] = 'timeend';
        }
        if ((string) ($existing->note ?? '') !== (string) ($newnote ?? '')) {
            // Only whether it changed is recorded — never the note content itself (see docs/seguridad-permisos.md).
            $fieldschanged[] = 'note';
        }

        $result = $this->repository->update_editable_fields($id, (object) [
            'cohortid'  => $newcohortid,
            'timestart' => $timestart,
            'timeend'   => $timeend,
            'note'      => $newnote,
        ], $userid);

        $eventextra = ['fieldschanged' => $fieldschanged];
        if ($editingclosed) {
            $eventextra['reason'] = shorten_text(trim((string) $reason), 255);
        }
        assignment_updated::create_from_id(
            $id,
            $userid,
            (int) $existing->studentid,
            (int) $existing->academicyearid,
            $eventextra
        )->trigger();

        return $result;
    }

    /**
     * Closes an assignment: sets status=closed and timeend, keeps every other
     * field (student, tutor, academic year, type, source) untouched — never a
     * physical delete. The student/tutor immediately lose scope_service
     * access to each other, since access is only ever granted over "vigente"
     * (active + within window) rows.
     *
     * Closing a primary tutor assignment does not automatically create or
     * reassign a replacement (that remains a manual follow-up, or the
     * dedicated reassignment flow in a later phase); this method only
     * reports, via the fired event, whether the student is now left without
     * an active primary tutor for the academic year.
     *
     * Concurrency: same best-effort guard as reassign_primary_tutor() (phase
     * 3E.3) — Moodle DML has no portable row-level locking, so $existing is
     * re-read from the database immediately before writing, inside a
     * transaction; if its status is no longer active (another request
     * already closed it in between), this aborts without touching anything
     * instead of silently overwriting the first closure's reason/note/date.
     *
     * @param int $id
     * @param int $userid
     * @param string $closereason one of assignment_close_reason::values()
     * @param int|null $timeend defaults to now
     * @param string|null $note replaces the administrative note when provided
     * @return bool
     */
    public function close(
        int $id,
        int $userid,
        string $closereason,
        ?int $timeend = null,
        ?string $note = null
    ): bool {
        global $DB;

        $existing = $this->repository->get($id);

        if ($existing->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_already_closed', 'local_monlaututoria');
        }

        if (!in_array($closereason, assignment_close_reason::values(), true)) {
            throw new \moodle_exception('error_assignment_close_reason_invalid', 'local_monlaututoria');
        }

        $timeend = $timeend ?? time();
        if ($timeend < (int) $existing->timestart) {
            throw new \moodle_exception('error_assignment_close_before_start', 'local_monlaututoria');
        }

        $transaction = $DB->start_delegated_transaction();

        $recheck = $this->repository->get($id);
        if ($recheck->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_already_closed', 'local_monlaututoria');
        }

        $result = $this->repository->close($id, $userid, $timeend, $closereason, $note);

        $transaction->allow_commit();

        if ($existing->assignmenttype === assignment_type::CO_TUTOR) {
            co_tutor_removed::create_from_id($id, $userid, (int) $existing->studentid)->trigger();

            return $result;
        }

        $leftwithoutprimary = $existing->assignmenttype === assignment_type::PRIMARY && !empty($existing->isprimary)
            && $this->repository->count_active_primary((int) $existing->studentid, (int) $existing->academicyearid) === 0;

        assignment_closed::create_from_id(
            $id,
            $userid,
            (int) $existing->studentid,
            $closereason,
            $leftwithoutprimary
        )->trigger();

        return $result;
    }

    /**
     * Closes an active co-tutor assignment specifically, guarding that the
     * target row really is one (close() alone would also accept a primary row).
     *
     * Same concurrency guard as close() (phase 3E.3): re-read immediately
     * before writing, inside a transaction, aborting if another request
     * already closed/changed this row in between.
     *
     * @param int $id
     * @param int $userid
     * @param int|null $timeend
     * @return bool
     */
    public function remove_cotutor(int $id, int $userid, ?int $timeend = null): bool {
        global $DB;

        $existing = $this->repository->get($id);

        if ($existing->assignmenttype !== assignment_type::CO_TUTOR || $existing->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_not_active_cotutor', 'local_monlaututoria');
        }

        $timeend = $timeend ?? time();

        $transaction = $DB->start_delegated_transaction();

        $recheck = $this->repository->get($id);
        if ($recheck->status !== assignment_status::ACTIVE) {
            throw new \moodle_exception('error_assignment_not_active_cotutor', 'local_monlaututoria');
        }

        $result = $this->repository->close($id, $userid, $timeend);

        $transaction->allow_commit();

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
     * Reassigns a student's primary tutor: closes the current active primary
     * assignment and creates a new one, atomically. Fires a single
     * student_reassigned event for the whole operation (never separate
     * assignment_closed/assignment_created events for this action).
     *
     * Date semantics: the previous assignment's timeend and the new
     * assignment's timestart are both set to the same effective date.
     * find_current() treats timeend as exclusive (timeend > now, not >=), so
     * at the exact effective date the old row is already not "vigente" and
     * the new one already is — no instant where both or neither grant access.
     *
     * Concurrency: Moodle DML has no portable row-level locking, so this is a
     * best-effort optimistic guard, not a hard guarantee. The current primary
     * assignment is read once for validation, then re-read from the database
     * (not from the command) immediately before the write, inside the
     * transaction; if its status or tutor no longer match what was just
     * validated, another operation has already acted on it and this one
     * aborts without changing anything, instead of silently overwriting it.
     *
     * The previous tutor's operational access is revoked implicitly: scope_service
     * only grants access over "vigente" assignments, and access was never
     * granted through a Moodle role tied to the assignment, so there is
     * nothing else to unassign here (see docs/seguridad-permisos.md).
     *
     * @param reassign_assignment_command $command
     * @param int $actorid
     * @return assignment_reassignment_result
     */
    public function reassign_primary_tutor(
        reassign_assignment_command $command,
        int $actorid
    ): assignment_reassignment_result {
        global $DB;

        $studentid = $command->studentid;
        $newtutorid = $command->newtutorid;
        $academicyearid = $command->academicyearid;

        $this->validate_not_self($studentid, $newtutorid);

        if (!in_array($command->reassignreason, assignment_reassign_reason::values(), true)) {
            throw new \moodle_exception('error_assignment_reassign_reason_invalid', 'local_monlaututoria');
        }

        $current = $this->repository->find_active_primary($studentid, $academicyearid);
        if ($current === null) {
            throw new \moodle_exception('error_assignment_no_active_primary', 'local_monlaututoria');
        }

        if ((int) $current->tutorid === $newtutorid) {
            throw new \moodle_exception('error_assignment_reassign_same_tutor', 'local_monlaututoria');
        }

        $student = $this->validate_user($studentid, 'error_assignment_invalid_student');
        $tutor = $this->validate_user($newtutorid, 'error_assignment_invalid_tutor');
        $this->validate_not_suspended($student, $command->allowsuspended, 'error_assignment_student_suspended');
        $this->validate_not_suspended($tutor, $command->allowsuspended, 'error_assignment_tutor_suspended');
        $this->validate_academic_year($academicyearid, $command->canoverridelock);

        $effectivedate = $command->effectivedate ?? time();
        if ($effectivedate < (int) $current->timestart) {
            throw new \moodle_exception('error_assignment_close_before_start', 'local_monlaututoria');
        }

        $transaction = $DB->start_delegated_transaction();

        $recheck = $this->repository->get((int) $current->id);
        if ($recheck->status !== assignment_status::ACTIVE || (int) $recheck->tutorid !== (int) $current->tutorid) {
            throw new \moodle_exception('error_assignment_reassign_conflict', 'local_monlaututoria');
        }

        $this->repository->close((int) $current->id, $actorid, $effectivedate);

        $newid = $this->repository->create((object) [
            'studentid'      => $studentid,
            'tutorid'        => $newtutorid,
            'academicyearid' => $academicyearid,
            'assignmenttype' => assignment_type::PRIMARY,
            'isprimary'      => true,
            'timestart'      => $effectivedate,
            'source'         => assignment_source::MANUAL,
            'reassignreason' => $command->reassignreason,
            'createdby'      => $actorid,
        ]);

        $keptcotutorids = [];
        $closedcotutorids = [];
        foreach ($this->repository->find_active_cotutors($studentid, $academicyearid) as $cotutor) {
            if ($command->keepcotutors) {
                $keptcotutorids[] = (int) $cotutor->id;
            } else {
                $this->repository->close((int) $cotutor->id, $actorid, $effectivedate);
                $closedcotutorids[] = (int) $cotutor->id;
            }
        }

        $transaction->allow_commit();

        student_reassigned::create_from_id(
            $newid,
            $actorid,
            $studentid,
            (int) $current->tutorid,
            (int) $current->id,
            $academicyearid,
            $command->reassignreason,
            $closedcotutorids
        )->trigger();

        foreach ($closedcotutorids as $cotutorid) {
            co_tutor_removed::create_from_id($cotutorid, $actorid, $studentid)->trigger();
        }

        return new assignment_reassignment_result(
            (int) $current->id,
            (int) $current->tutorid,
            $newid,
            $newtutorid,
            $effectivedate,
            $keptcotutorids,
            $closedcotutorids
        );
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
     * Public (not just used internally): cohort_assignment_preview_service
     * (phase 3C) reuses this instead of duplicating the same check.
     *
     * @param int $userid
     * @param string $errorkey
     * @return \stdClass
     */
    public function validate_user(int $userid, string $errorkey): \stdClass {
        $user = \core_user::get_user($userid);
        if (!$user || !empty($user->deleted)) {
            throw new \moodle_exception($errorkey, 'local_monlaututoria');
        }

        return $user;
    }

    /**
     * Public for the same reason as validate_user() above.
     *
     * @param \stdClass $user
     * @param bool $allowsuspended
     * @param string $errorkey
     */
    public function validate_not_suspended(\stdClass $user, bool $allowsuspended, string $errorkey): void {
        if (!empty($user->suspended) && !$allowsuspended) {
            throw new \moodle_exception($errorkey, 'local_monlaututoria');
        }
    }

    /**
     * Public for the same reason as validate_user() above.
     *
     * @param int $academicyearid
     * @param bool $canoverridelock
     * @return \stdClass
     */
    public function validate_academic_year(int $academicyearid, bool $canoverridelock): \stdClass {
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
     * Public for the same reason as validate_user() above.
     *
     * @param int $cohortid
     */
    public function validate_cohort(int $cohortid): void {
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
