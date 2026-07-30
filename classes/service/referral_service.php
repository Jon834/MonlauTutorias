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

use local_monlaututoria\repository\referral_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\domain\referral;
use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\domain\referral_status;
use local_monlaututoria\domain\priority_level;
use local_monlaututoria\event\referral_created;
use local_monlaututoria\event\referral_updated;

/**
 * Application service for referrals (phase 6.4 "Derivaciones básicas").
 *
 * Deliberately the ONE entity in this plugin whose read access is NOT
 * gated by scope_service — coordination/orientation/management routinely
 * receive referrals about students they have no tutoring relationship with,
 * that is the whole point of a referral. Creating one still requires the
 * creator to have scope over the student (the entry it originates from is
 * real data about that student), but viewing/handling one afterwards is
 * gated purely by local/monlaututoria:managereferrals (or being the creator
 * or the assignee) — "Derivaciones limitadas por capacidades", literally
 * what docs/fases/phase-6.md's acceptance criteria ask for, not by ámbito.
 *
 * "No duplicar contenido sensible" (same criteria): reason/resolution are
 * always freshly authored text, never auto-filled from the origin entry's
 * noteinternal/noterestricted — enforced by referral_create_form.php simply
 * never binding a default value to those fields, nothing to check here.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_service {

    /** @var referral_repository */
    private $repository;

    /** @var entry_repository */
    private $entryrepository;

    /** @var scope_service */
    private $scopeservice;

    public function __construct(
        ?referral_repository $repository = null,
        ?entry_repository $entryrepository = null,
        ?scope_service $scopeservice = null
    ) {
        $this->repository = $repository ?? new referral_repository();
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->scopeservice = $scopeservice ?? new scope_service();
    }

    /**
     * @param int $entryid
     * @param string $destination one of referral_destination::values()
     * @param string $reason
     * @param string $priority one of priority_level::values()
     * @param int $userid
     * @return int the new referral id
     */
    public function create(int $entryid, string $destination, string $reason, string $priority, int $userid): int {
        $entry = $this->entryrepository->get($entryid);

        $this->scopeservice->require_user_can_access_student($userid, (int) $entry->studentid, (int) $entry->academicyearid);

        if (!in_array($destination, referral_destination::values(), true)) {
            throw new \moodle_exception('error_referral_destination_invalid', 'local_monlaututoria');
        }
        if (!in_array($priority, priority_level::values(), true)) {
            throw new \moodle_exception('error_referral_priority_invalid', 'local_monlaututoria');
        }
        if (trim($reason) === '') {
            throw new \moodle_exception('error_referral_reason_required', 'local_monlaututoria');
        }

        $referralid = $this->repository->create((object) [
            'entryid'     => $entryid,
            'studentid'   => $entry->studentid,
            'destination' => $destination,
            'reason'      => $reason,
            'priority'    => $priority,
            'createdby'   => $userid,
        ]);

        referral_created::create_from_id($referralid, $userid, (int) $entry->studentid, $destination)->trigger();

        return $referralid;
    }

    /**
     * @param int $referralid
     * @param int $viewerid
     * @return referral
     */
    public function get_for_viewer(int $referralid, int $viewerid): referral {
        $record = $this->repository->get($referralid);

        if (!$this->can_view($record, $viewerid)) {
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }

        return referral::from_record($record);
    }

    /**
     * Broad listing for coordination/orientation/management — requires
     * managereferrals (not scope_service, see the class docblock).
     *
     * @param array $filters see referral_repository::search()
     * @param int $viewerid
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort see referral_repository::sortable_columns()
     * @param string $direction 'ASC' or 'DESC'
     * @return referral[]
     */
    public function list_for_coordination(
        array $filters,
        int $viewerid,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'timecreated',
        string $direction = 'DESC'
    ): array {
        require_capability('local/monlaututoria:managereferrals', \context_system::instance(), $viewerid);

        $records = $this->repository->search($filters, $limitfrom, $limitnum, $sort, $direction);

        return array_map(fn (\stdClass $record) => referral::from_record($record), array_values($records));
    }

    /**
     * @param array $filters see list_for_coordination()
     * @param int $viewerid
     * @return int
     */
    public function count_for_coordination(array $filters, int $viewerid): int {
        require_capability('local/monlaututoria:managereferrals', \context_system::instance(), $viewerid);

        return $this->repository->count_search($filters);
    }

    /**
     * @param int $id
     * @param int $assignedto
     * @param int $userid
     * @return bool
     */
    public function assign(int $id, int $assignedto, int $userid): bool {
        $existing = $this->repository->get($id);
        if ($existing->status === referral_status::RESOLVED || $existing->status === referral_status::CANCELLED) {
            throw new \moodle_exception('error_referral_invalid_transition', 'local_monlaututoria');
        }

        $newstatus = $existing->status === referral_status::PENDING ? referral_status::IN_PROGRESS : $existing->status;
        $result = $this->repository->assign($id, $assignedto, $userid);

        referral_updated::create_from_id($id, $userid, (int) $existing->studentid, $existing->status, $newstatus, $assignedto)->trigger();

        return $result;
    }

    /**
     * @param int $id
     * @param string $resolution
     * @param int $userid
     * @return bool
     */
    public function resolve(int $id, string $resolution, int $userid): bool {
        if (trim($resolution) === '') {
            throw new \moodle_exception('error_referral_resolution_required', 'local_monlaututoria');
        }

        $existing = $this->repository->get($id);
        if (!in_array($existing->status, [referral_status::PENDING, referral_status::IN_PROGRESS], true)) {
            throw new \moodle_exception('error_referral_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->resolve($id, $resolution, $userid);

        referral_updated::create_from_id($id, $userid, (int) $existing->studentid, $existing->status, referral_status::RESOLVED)->trigger();

        return $result;
    }

    /**
     * Visible open referrals for a batch of students. The same creator/assignee/
     * managereferrals rule as get_for_viewer(), but applied in bulk for dashboards.
     *
     * @param int[] $studentids
     * @param int $viewerid
     * @return referral[]
     */
    public function list_open_for_students(array $studentids, int $viewerid): array {
        $records = array_values($this->repository->find_open_by_students($studentids));
        $visible = array_filter($records, fn (\stdClass $record): bool => $this->can_view($record, $viewerid));

        return array_map(static fn (\stdClass $record): referral => referral::from_record($record), array_values($visible));
    }

    /**
     * @param int $id
     * @param int $userid
     * @return bool
     */
    public function cancel(int $id, int $userid): bool {
        $existing = $this->repository->get($id);
        if (!in_array($existing->status, [referral_status::PENDING, referral_status::IN_PROGRESS], true)) {
            throw new \moodle_exception('error_referral_invalid_transition', 'local_monlaututoria');
        }

        $result = $this->repository->cancel($id, $userid);

        referral_updated::create_from_id($id, $userid, (int) $existing->studentid, $existing->status, referral_status::CANCELLED)->trigger();

        return $result;
    }

    /**
     * @param \stdClass $record raw local_tut_referral row
     * @param int $viewerid
     * @return bool
     */
    private function can_view(\stdClass $record, int $viewerid): bool {
        if ((int) $record->createdby === $viewerid) {
            return true;
        }
        if ($record->assignedto !== null && (int) $record->assignedto === $viewerid) {
            return true;
        }

        return has_capability('local/monlaututoria:managereferrals', \context_system::instance(), $viewerid);
    }
}
