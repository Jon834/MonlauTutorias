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

use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\event\academic_year_created;
use local_monlaututoria\event\academic_year_updated;
use local_monlaututoria\event\academic_year_activated;
use local_monlaututoria\event\academic_year_locked;
use local_monlaututoria\event\academic_year_deleted;

/**
 * Application service enforcing the business rules for academic years:
 * date validation, shortname uniqueness, exclusive activation, lock overrides
 * and the delete guard. Pages/forms must go through this class, never call
 * academic_year_repository directly for writes.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_service {

    /** @var academic_year_repository */
    private $repository;

    public function __construct(?academic_year_repository $repository = null) {
        $this->repository = $repository ?? new academic_year_repository();
    }

    /**
     * Creates a new academic year. Never active on creation; activate() separately.
     *
     * @param \stdClass $data must contain name, shortname, startdate, enddate
     * @param int $userid
     * @return int the new id
     */
    public function create(\stdClass $data, int $userid): int {
        $this->validate_dates((int) $data->startdate, (int) $data->enddate);
        $this->validate_shortname_unique($data->shortname);

        $data->createdby = $userid;
        $id = $this->repository->create($data);

        academic_year_created::create_from_id($id, $userid)->trigger();

        return $id;
    }

    /**
     * Updates an academic year's name/shortname/dates.
     *
     * @param \stdClass $data must contain id
     * @param int $userid
     * @param bool $canoverridelock whether the caller holds local/monlaututoria:overridelock
     * @return bool
     */
    public function update(\stdClass $data, int $userid, bool $canoverridelock): bool {
        $id = (int) $data->id;
        $existing = $this->repository->get($id);

        if (!empty($existing->locked) && !$canoverridelock) {
            throw new \moodle_exception('error_academicyear_locked', 'local_monlaututoria');
        }

        $startdate = isset($data->startdate) ? (int) $data->startdate : (int) $existing->startdate;
        $enddate = isset($data->enddate) ? (int) $data->enddate : (int) $existing->enddate;
        $this->validate_dates($startdate, $enddate);

        if (!empty($data->shortname)) {
            $this->validate_shortname_unique($data->shortname, $id);
        }

        $result = $this->repository->update($data, $userid);

        academic_year_updated::create_from_id($id, $userid)->trigger();

        return $result;
    }

    /**
     * Activates an academic year, deactivating any previously active one inside
     * a single delegated transaction so there is never more than one active row.
     *
     * @param int $id
     * @param int $userid
     */
    public function activate(int $id, int $userid): void {
        global $DB;

        $this->repository->get($id);

        $transaction = $DB->start_delegated_transaction();
        $previouslyactive = $this->repository->clear_active($userid);
        $this->repository->set_active_flag($id, true, $userid);
        $transaction->allow_commit();

        $previousid = $previouslyactive[0] ?? null;

        academic_year_activated::create_from_id($id, $userid, $previousid)->trigger();
    }

    /**
     * Locks or unlocks an academic year. Unlocking an already-locked year
     * requires local/monlaututoria:overridelock; locking never does.
     *
     * @param int $id
     * @param bool $locked
     * @param int $userid
     * @param bool $canoverridelock
     */
    public function set_locked(int $id, bool $locked, int $userid, bool $canoverridelock): void {
        $existing = $this->repository->get($id);

        if (!empty($existing->locked) && $locked === false && !$canoverridelock) {
            throw new \moodle_exception('error_noaccess_overridelock', 'local_monlaututoria');
        }

        $this->repository->set_locked_flag($id, $locked, $userid);

        academic_year_locked::create_from_id($id, $userid, $locked)->trigger();
    }

    /**
     * Deletes an academic year, blocking if it is active, locked, or referenced
     * by data from a future phase.
     *
     * @param int $id
     * @param int $userid
     */
    public function delete(int $id, int $userid): void {
        $existing = $this->repository->get($id);

        if (!empty($existing->active)) {
            throw new \moodle_exception('academicyear_delete_blocked_active', 'local_monlaututoria');
        }

        if (!empty($existing->locked)) {
            throw new \moodle_exception('error_academicyear_locked', 'local_monlaututoria');
        }

        if ($this->repository->has_dependent_data($id)) {
            throw new \moodle_exception('academicyear_delete_blocked_used', 'local_monlaututoria');
        }

        $this->repository->delete($id);

        academic_year_deleted::create_from_id($id, $userid, $existing->shortname)->trigger();
    }

    /**
     * @param int $startdate
     * @param int $enddate
     */
    private function validate_dates(int $startdate, int $enddate): void {
        if ($enddate <= $startdate) {
            throw new \moodle_exception('error_enddate_before_startdate', 'local_monlaututoria');
        }
    }

    /**
     * @param string $shortname
     * @param int|null $excludeid
     */
    private function validate_shortname_unique(string $shortname, ?int $excludeid = null): void {
        if ($this->repository->shortname_exists($shortname, $excludeid)) {
            throw new \moodle_exception('error_shortname_duplicate', 'local_monlaututoria');
        }
    }
}
