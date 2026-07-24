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

use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\domain\followup;
use local_monlaututoria\domain\followup_status;
use local_monlaututoria\domain\priority_level;
use local_monlaututoria\event\followup_created;
use local_monlaututoria\event\followup_updated;

/**
 * Application service for follow-ups (phase 6.2 "Seguimientos" + 6.3
 * "Acciones rápidas" — same deliberate bundling as agreement_service, see
 * its class docblock). Formalizes what local_tut_entry.nextfollowupdate
 * (phase 5.1) used to be a bare date into a tracked entity with priority,
 * status and a closing mechanism — but does not remove or reuse that column,
 * out of scope for this increment.
 *
 * Staff-only, no student-visible tier: unlike agreements ("Visibilidad para
 * el alumno" is explicit in docs/fases/phase-6.md's 6.1 bullet list),
 * follow-ups have no such field in 6.2's own list — same reasoning
 * entry_attachment_service already applied to attachments (phase 5.6): no
 * tier was asked for, so none is invented.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class followup_service {

    /** @var followup_repository */
    private $repository;

    /** @var entry_repository */
    private $entryrepository;

    /** @var scope_service */
    private $scopeservice;

    public function __construct(
        ?followup_repository $repository = null,
        ?entry_repository $entryrepository = null,
        ?scope_service $scopeservice = null
    ) {
        $this->repository = $repository ?? new followup_repository();
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->scopeservice = $scopeservice ?? new scope_service();
    }

    /**
     * @param int $entryid
     * @param int $duedate
     * @param string $priority one of priority_level::values()
     * @param int $userid
     * @return int the new follow-up id
     */
    public function create(int $entryid, int $duedate, string $priority, int $userid): int {
        $entry = $this->entryrepository->get($entryid);

        $this->scopeservice->require_user_can_access_student($userid, (int) $entry->studentid, (int) $entry->academicyearid);

        if (!in_array($priority, priority_level::values(), true)) {
            throw new \moodle_exception('error_followup_priority_invalid', 'local_monlaututoria');
        }

        $followupid = $this->repository->create((object) [
            'entryid'   => $entryid,
            'studentid' => $entry->studentid,
            'duedate'   => $duedate,
            'priority'  => $priority,
            'createdby' => $userid,
        ]);

        followup_created::create_from_id($followupid, $userid, (int) $entry->studentid, $entryid)->trigger();

        return $followupid;
    }

    /**
     * Never called by the student themselves in practice (no capability
     * grants that here), but does not assume that — always re-checks scope,
     * same defence-in-depth as get_for_viewer() elsewhere in this plugin.
     *
     * @param int $followupid
     * @param int $viewerid
     * @return followup
     */
    public function get_for_viewer(int $followupid, int $viewerid): followup {
        $record = $this->repository->get($followupid);
        $entry = $this->entryrepository->get((int) $record->entryid);

        $this->scopeservice->require_user_can_access_student($viewerid, (int) $record->studentid, (int) $entry->academicyearid);

        return followup::from_record($record);
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @param array $filters see followup_repository::search()
     * @param int $viewerid
     * @param int $limitfrom
     * @param int $limitnum
     * @return followup[]
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
        $records = $this->repository->search($filters, $limitfrom, $limitnum);

        return array_map(fn (\stdClass $record) => followup::from_record($record), array_values($records));
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

        return $this->repository->count_search($filters);
    }

    /**
     * Manual completion — "Cierre manual" from docs/fases/phase-5.md's 6.2
     * bullet list. See close_with_entry() for the other half ("mediante
     * nueva tutoría vinculada").
     *
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function complete_manually(int $id, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, followup_status::open_values(), true)) {
            throw new \moodle_exception('error_followup_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, followup_status::COMPLETED, $userid);

        followup_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, followup_status::COMPLETED
        )->trigger();

        return $result;
    }

    /**
     * Closes a follow-up via a newly created, linked tutoring entry — called
     * by entries/create.php/create_full.php when reached with a "followupid"
     * param, right after the new entry itself is created.
     *
     * @param int $id
     * @param int $closingentryid
     * @param int $userid
     * @return bool
     */
    public function close_with_entry(int $id, int $closingentryid, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, followup_status::open_values(), true)) {
            throw new \moodle_exception('error_followup_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->close_with_entry($id, $closingentryid, $userid);

        followup_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, followup_status::COMPLETED, null, $closingentryid
        )->trigger();

        return $result;
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function reopen(int $id, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, [followup_status::COMPLETED, followup_status::CANCELLED], true)) {
            throw new \moodle_exception('error_followup_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, followup_status::PENDING, $userid);

        followup_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, followup_status::PENDING
        )->trigger();

        return $result;
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function cancel(int $id, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, followup_status::open_values(), true)) {
            throw new \moodle_exception('error_followup_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, followup_status::CANCELLED, $userid);

        followup_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, followup_status::CANCELLED
        )->trigger();

        return $result;
    }

    /**
     * @param int $id
     * @param int $newduedate
     * @param int $userid
     * @return bool
     */
    public function postpone(int $id, int $newduedate, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, followup_status::open_values(), true)) {
            throw new \moodle_exception('error_followup_cannot_postpone_closed', 'local_monlaututoria');
        }

        $result = $this->repository->update_status($id, $existing->status, $userid, $newduedate);

        followup_updated::create_from_id(
            $id, $userid, (int) $existing->studentid, $existing->status, $existing->status, $newduedate
        )->trigger();

        return $result;
    }
}
