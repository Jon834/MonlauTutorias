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

use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\domain\agreement;
use local_monlaututoria\domain\agreement_create_command;
use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\domain\agreement_status;
use local_monlaututoria\event\agreement_created;
use local_monlaututoria\event\agreement_updated;

/**
 * Application service for agreements (phase 6.1 "Acuerdos" + 6.3 "Acciones
 * rápidas" — the quick actions ship together with the entity itself, a
 * deliberate deviation from the entry_service precedent of splitting create
 * (5.2/5.3) from edit/annul (5.5) into separate releases, made to avoid
 * shipping an agreement that can never be marked complete for a whole
 * release. See docs/plan-desarrollo.md for the full rationale.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_service {

    /** @var agreement_repository */
    private $repository;

    /** @var entry_repository */
    private $entryrepository;

    /** @var assignment_service */
    private $assignmentservice;

    /** @var scope_service */
    private $scopeservice;

    public function __construct(
        ?agreement_repository $repository = null,
        ?entry_repository $entryrepository = null,
        ?assignment_service $assignmentservice = null,
        ?scope_service $scopeservice = null
    ) {
        $this->repository = $repository ?? new agreement_repository();
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->assignmentservice = $assignmentservice ?? new assignment_service();
        $this->scopeservice = $scopeservice ?? new scope_service();
    }

    /**
     * Creates a new agreement tied to an existing tutoring entry.
     * studentid/academicyearid are resolved from the entry, never accepted
     * from the caller — the agreement always concerns the same student as
     * its origin entry.
     *
     * @param agreement_create_command $command
     * @param int $userid
     * @return int the new agreement id
     */
    public function create(agreement_create_command $command, int $userid): int {
        $entry = $this->entryrepository->get($command->entryid);

        $this->scopeservice->require_user_can_access_student(
            $userid, (int) $entry->studentid, (int) $entry->academicyearid
        );

        $this->validate_responsible($command->responsibletype, $command->responsibleuserid, $command->responsibleexternalname);

        $agreementid = $this->repository->create((object) [
            'entryid'                  => $command->entryid,
            'studentid'                => $entry->studentid,
            'description'              => $command->description,
            'responsibletype'          => $command->responsibletype,
            'responsibleuserid'        => $command->responsibleuserid,
            'responsibleexternalname'  => $command->responsibleexternalname,
            'duedate'                  => $command->duedate,
            'visibletostudent'         => $command->visibletostudent,
            'createdby'                => $userid,
        ]);

        agreement_created::create_from_id($agreementid, $userid, (int) $entry->studentid, $command->entryid)->trigger();

        return $agreementid;
    }

    /**
     * @param int $agreementid
     * @param int $viewerid
     * @return agreement
     */
    public function get_for_viewer(int $agreementid, int $viewerid): agreement {
        $record = $this->repository->get($agreementid);
        $entry = $this->entryrepository->get((int) $record->entryid);

        $this->scopeservice->require_user_can_access_student(
            $viewerid, (int) $record->studentid, (int) $entry->academicyearid
        );

        if ($viewerid === (int) $record->studentid && empty($record->visibletostudent)) {
            // Whole-row visibility, not per-field masking like entry's 3
            // note tiers: an agreement is either shown to the student or it
            // is not, there is nothing partial to hide within one row.
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }

        return agreement::from_record($record);
    }

    /**
     * Lists agreements for a student, applying the same visibleto­student
     * rule as get_for_viewer() to every row when $viewerid is the student.
     *
     * @param int $studentid
     * @param int $academicyearid
     * @param array $filters see agreement_repository::search()
     * @param int $viewerid
     * @param int $limitfrom
     * @param int $limitnum
     * @return agreement[]
     */
    public function list_for_student(
        int $studentid,
        int $academicyearid,
        array $filters,
        int $viewerid,
        int $limitfrom = 0,
        int $limitnum = 0
    ): array {
        $this->scopeservice->require_user_can_access_student($viewerid, $studentid, $academicyearid);

        $filters['studentid'] = $studentid;
        $isstudentviewer = $viewerid === $studentid;

        $records = $this->repository->search($filters, $limitfrom, $limitnum);
        if ($isstudentviewer) {
            $records = array_filter($records, static fn ($record) => !empty($record->visibletostudent));
        }

        return array_map(fn (\stdClass $record) => agreement::from_record($record), array_values($records));
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @param array $filters see list_for_student()
     * @param int $viewerid
     * @return int
     */
    public function count_for_student(int $studentid, int $academicyearid, array $filters, int $viewerid): int {
        $this->scopeservice->require_user_can_access_student($viewerid, $studentid, $academicyearid);

        $filters['studentid'] = $studentid;
        if ($viewerid === $studentid) {
            // No SQL-level column for visibletostudent filtering beyond the
            // flag itself — cheap enough at this scale (one student's own
            // agreements, never a site-wide count) to reuse list_for_student()
            // and count the already-filtered rows, same rule applied once.
            return count($this->list_for_student($studentid, $academicyearid, $filters, $viewerid, 0, 0));
        }

        return $this->repository->count_search($filters);
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function complete(int $id, int $userid): bool {
        return $this->transition($id, $userid, agreement_status::COMPLETED, [agreement_status::PENDING, agreement_status::IN_PROGRESS]);
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function reopen(int $id, int $userid): bool {
        return $this->transition($id, $userid, agreement_status::PENDING, [agreement_status::COMPLETED, agreement_status::CANCELLED]);
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function cancel(int $id, int $userid): bool {
        return $this->transition($id, $userid, agreement_status::CANCELLED, agreement_status::open_values());
    }

    /**
     * @param int $id
     * @param int $newduedate
     * @param int $userid
     * @return bool
     */
    public function postpone(int $id, int $newduedate, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, agreement_status::open_values(), true)) {
            throw new \moodle_exception('error_agreement_cannot_postpone_closed', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, $existing->status, $userid, $newduedate);

        agreement_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, $existing->status, $newduedate
        )->trigger();

        return $result;
    }

    /**
     * @param int $id
     * @param int $userid
     * @param string $newstatus
     * @param string[] $allowedfrom statuses $id must currently be in
     * @return bool
     */
    private function transition(int $id, int $userid, string $newstatus, array $allowedfrom): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, $allowedfrom, true)) {
            throw new \moodle_exception('error_agreement_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, $newstatus, $userid);

        agreement_updated::create_from_id($id, $userid, (int) $existing->studentid, $existing->status, $newstatus)->trigger();

        return $result;
    }

    /**
     * @param string $responsibletype
     * @param int|null $responsibleuserid
     * @param string|null $responsibleexternalname
     */
    private function validate_responsible(string $responsibletype, ?int $responsibleuserid, ?string $responsibleexternalname): void {
        if (!in_array($responsibletype, agreement_responsible_type::values(), true)) {
            throw new \moodle_exception('error_agreement_responsible_type_invalid', 'local_monlaututoria');
        }

        $hasuserid = !empty($responsibleuserid);
        $hasexternalname = !empty($responsibleexternalname);

        if ($hasuserid === $hasexternalname) {
            throw new \moodle_exception('error_agreement_responsible_identity_invalid', 'local_monlaututoria');
        }

        if ($hasuserid) {
            $this->assignmentservice->validate_user((int) $responsibleuserid, 'error_agreement_responsible_user_invalid');
        }
    }
}
