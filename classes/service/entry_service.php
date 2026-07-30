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

use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\entry_participant_repository;
use local_monlaututoria\repository\entry_reason_repository;
use local_monlaututoria\repository\entry_version_repository;
use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;
use local_monlaututoria\domain\entry;
use local_monlaututoria\domain\entry_create_command;
use local_monlaututoria\domain\entry_participant_type;
use local_monlaututoria\domain\entry_status;
use local_monlaututoria\event\entry_created;
use local_monlaututoria\event\entry_updated;
use local_monlaututoria\event\entry_annulled;

/**
 * Application service for tutoring entries (phase 5.1 — "dominio y datos").
 * No page/form calls create() yet (that arrives in phase 5.2/5.3); the
 * vertical slice from domain to service is built now because
 * docs/fases/phase-5.md's own 5.1 scope asks for "repositorios y servicios"
 * alongside the schema, same as assignment_service existed a full increment
 * before assignments/create.php did (phase 3A vs 3B.2).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_service {

    /** @var entry_repository */
    private $repository;

    /** @var entry_participant_repository */
    private $participantrepository;

    /** @var entry_reason_repository */
    private $reasonlinkrepository;

    /** @var entry_version_repository */
    private $versionrepository;

    /** @var reason_repository */
    private $reasonrepository;

    /** @var modality_repository */
    private $modalityrepository;

    /** @var assignment_service */
    private $assignmentservice;

    /** @var scope_service */
    private $scopeservice;

    public function __construct(
        ?entry_repository $repository = null,
        ?entry_participant_repository $participantrepository = null,
        ?entry_reason_repository $reasonlinkrepository = null,
        ?entry_version_repository $versionrepository = null,
        ?reason_repository $reasonrepository = null,
        ?modality_repository $modalityrepository = null,
        ?assignment_service $assignmentservice = null,
        ?scope_service $scopeservice = null
    ) {
        $this->repository = $repository ?? new entry_repository();
        $this->participantrepository = $participantrepository ?? new entry_participant_repository();
        $this->reasonlinkrepository = $reasonlinkrepository ?? new entry_reason_repository();
        $this->versionrepository = $versionrepository ?? new entry_version_repository();
        $this->reasonrepository = $reasonrepository ?? new reason_repository();
        $this->modalityrepository = $modalityrepository ?? new modality_repository();
        $this->assignmentservice = $assignmentservice ?? new assignment_service();
        $this->scopeservice = $scopeservice ?? new scope_service();
    }

    /**
     * Creates a new tutoring entry with its participants and related reasons,
     * atomically. Reuses assignment_service's user/academic-year validation
     * rather than duplicating it (same student/tutor/locked-year rules that
     * already govern assignments).
     *
     * @param entry_create_command $command
     * @param int $userid the author (persisted as createdby)
     * @return int the new entry id
     */
    public function create(entry_create_command $command, int $userid): int {
        global $DB;

        if ($command->studentid === $command->tutorid) {
            throw new \moodle_exception('error_assignment_self', 'local_monlaututoria');
        }

        $this->assignmentservice->validate_user($command->studentid, 'error_assignment_invalid_student');
        $this->assignmentservice->validate_user($command->tutorid, 'error_assignment_invalid_tutor');
        $this->assignmentservice->validate_academic_year($command->academicyearid, $command->canoverridelock);

        if ($command->modalityid !== null) {
            $this->validate_modality($command->modalityid);
        }

        foreach ($command->reasonids as $reasonid) {
            $this->validate_reason((int) $reasonid);
        }

        if ($command->nextfollowupdate !== null && $command->nextfollowupdate < $command->entrydate) {
            throw new \moodle_exception('error_entry_followup_before_entrydate', 'local_monlaututoria');
        }

        foreach ($command->participants as $participant) {
            $this->validate_participant($participant);
        }

        $transaction = $DB->start_delegated_transaction();

        $entryid = $this->repository->create((object) [
            'studentid'        => $command->studentid,
            'tutorid'          => $command->tutorid,
            'academicyearid'   => $command->academicyearid,
            'entrydate'        => $command->entrydate,
            'modalityid'       => $command->modalityid,
            'contentvisible'   => $command->contentvisible,
            'noteinternal'     => $command->noteinternal,
            'noterestricted'   => $command->noterestricted,
            'nextfollowupdate' => $command->nextfollowupdate,
            'createdby'        => $userid,
        ]);

        foreach ($command->participants as $participant) {
            $this->participantrepository->create((object) [
                'entryid'         => $entryid,
                'participanttype' => $participant->participanttype,
                'userid'          => $participant->userid ?? null,
                'externalname'    => $participant->externalname ?? null,
                'createdby'       => $userid,
            ]);
        }

        if (!empty($command->reasonids)) {
            $this->reasonlinkrepository->attach($entryid, $command->reasonids);
        }

        $transaction->allow_commit();

        entry_created::create_from_id(
            $entryid,
            $userid,
            $command->studentid,
            $command->tutorid,
            $command->academicyearid
        )->trigger();

        return $entryid;
    }

    /**
     * Edits the editable fields of an active tutoring entry (phase 5.5).
     * Never touches studentid/tutorid/academicyearid/entrydate/status —
     * changing status is annul()'s job, a separate flow.
     *
     * "Ventana de edición configurable" (docs/fases/phase-5.md): edits made
     * within local_monlaututoria/entryeditwindow seconds of creation
     * (default 3 days, see settings.php) are a quick correction and need no
     * reason; anything later is a "cambio sensible" and $reason becomes
     * mandatory — same shape as assignment_service::update()'s
     * $editingclosed/$reason requirement for a closed assignment.
     *
     * Every edit — inside or outside the window — is snapshotted into
     * local_tut_entryversion first (the table phase 5.1 created empty),
     * capturing the fields as they were immediately before this write.
     *
     * Concurrency: same best-effort guard as assignment_service::close()
     * (phase 3E.3) — re-read the status immediately before writing, inside
     * the transaction, aborting if another request already annulled this
     * entry in between.
     *
     * @param int $id
     * @param \stdClass $data may contain modalityid, contentvisible, noteinternal,
     *                        noterestricted, nextfollowupdate
     * @param int $userid
     * @param bool $caneditrestricted whether the caller holds
     *                                local/monlaututoria:viewrestrictednotes —
     *                                without it, a noterestricted value in
     *                                $data is silently dropped, never written
     * @param string|null $reason required once outside the edit window
     * @param int[]|null $reasonids when given, replaces the entry's complete
     *                              set of related "motivos" (entry_reason_
     *                              repository::sync()); null leaves the
     *                              existing set untouched — distinct from an
     *                              empty array, which clears it
     * @return bool
     */
    public function update(
        int $id,
        \stdClass $data,
        int $userid,
        bool $caneditrestricted = false,
        ?string $reason = null,
        ?array $reasonids = null
    ): bool {
        global $DB;

        $existing = $this->repository->get($id);

        if ($existing->status !== entry_status::ACTIVE) {
            throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
        }

        // Falls back to the same 3-day default declared in settings.php
        // rather than trusting that Moodle has already seeded it into
        // mdl_config_plugins — admin_setting defaults are only applied once
        // the settings page has actually been visited or upgrade has run,
        // which a fresh install/test sandbox cannot always guarantee.
        // ">=", not ">": a 0-second window must mean "outside" from the very
        // first moment, including an edit made in the same wall-clock second
        // as the entry's own creation — time() can never be earlier than
        // timecreated, so ">" alone could stay false for a same-second edit.
        $editwindow = (int) (get_config('local_monlaututoria', 'entryeditwindow') ?: (3 * DAYSECS));
        $outsidewindow = time() >= ((int) $existing->timecreated + $editwindow);
        if ($outsidewindow && trim((string) $reason) === '') {
            throw new \moodle_exception('error_entry_edit_reason_required', 'local_monlaututoria');
        }

        if (!empty($data->modalityid)) {
            $this->validate_modality((int) $data->modalityid);
        }

        if (property_exists($data, 'nextfollowupdate') && !empty($data->nextfollowupdate)) {
            $entrydate = (int) $existing->entrydate;
            if ((int) $data->nextfollowupdate < $entrydate) {
                throw new \moodle_exception('error_entry_followup_before_entrydate', 'local_monlaututoria');
            }
        }

        if (property_exists($data, 'noterestricted') && !$caneditrestricted) {
            // Defense in depth: the edit form never renders this field
            // without the capability, but the service does not trust that
            // alone — same reasoning as create()'s equivalent page-side check.
            unset($data->noterestricted);
        }

        if ($reasonids !== null) {
            foreach ($reasonids as $reasonid) {
                $this->validate_reason((int) $reasonid);
            }
        }

        $transaction = $DB->start_delegated_transaction();

        $recheck = $this->repository->get($id);
        if ($recheck->status !== entry_status::ACTIVE) {
            throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
        }

        $this->snapshot_current_state($id, $existing, $userid, $reason);

        $result = $this->repository->update_editable_fields($id, $data, $userid);

        if ($reasonids !== null) {
            $this->reasonlinkrepository->sync($id, array_map('intval', $reasonids));
        }

        $transaction->allow_commit();

        entry_updated::create_from_id($id, $userid, (int) $existing->studentid, (int) $existing->academicyearid)->trigger();

        return $result;
    }

    /**
     * Annuls a tutoring entry — status=annulled, never a physical delete
     * ("anulación lógica, nunca borrado ordinario", docs/fases/phase-5.md).
     * A reason is always required, unlike update(): annulment is inherently
     * a sensitive change, there is no "quick" version of it.
     *
     * Concurrency: same pattern as update()/assignment_service::close().
     *
     * @param int $id
     * @param int $userid
     * @param string $reason
     * @return bool
     */
    public function annul(int $id, int $userid, string $reason): bool {
        global $DB;

        if (trim($reason) === '') {
            throw new \moodle_exception('error_entry_annul_reason_required', 'local_monlaututoria');
        }

        $existing = $this->repository->get($id);
        if ($existing->status !== entry_status::ACTIVE) {
            throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
        }

        $transaction = $DB->start_delegated_transaction();

        $recheck = $this->repository->get($id);
        if ($recheck->status !== entry_status::ACTIVE) {
            throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
        }

        $this->snapshot_current_state($id, $existing, $userid, $reason);

        $result = $this->repository->annul($id, $userid);

        $transaction->allow_commit();

        entry_annulled::create_from_id($id, $userid, (int) $existing->studentid, $reason)->trigger();

        return $result;
    }

    /**
     * Writes one local_tut_entryversion row capturing $existing's editable
     * fields as they were immediately before the write update()/annul() is
     * about to make. Shared by both so the snapshot shape is defined once.
     *
     * @param int $entryid
     * @param \stdClass $existing raw row, read before any change
     * @param int $userid
     * @param string|null $reason
     */
    private function snapshot_current_state(int $entryid, \stdClass $existing, int $userid, ?string $reason): void {
        $snapshot = [
            'status'           => $existing->status,
            'modalityid'       => $existing->modalityid !== null ? (int) $existing->modalityid : null,
            'contentvisible'   => $existing->contentvisible,
            'noteinternal'     => $existing->noteinternal,
            'noterestricted'   => $existing->noterestricted,
            'nextfollowupdate' => $existing->nextfollowupdate !== null ? (int) $existing->nextfollowupdate : null,
        ];

        $this->versionrepository->create((object) [
            'entryid'       => $entryid,
            'versionnumber' => $this->versionrepository->get_next_version_number($entryid),
            'snapshotjson'  => json_encode($snapshot),
            'changereason'  => $reason !== null && trim($reason) !== '' ? $reason : null,
            'createdby'     => $userid,
        ]);
    }

    /**
     * Returns a tutoring entry with every field the viewer is not authorised
     * to see already set to null — never a partial object a template later
     * decides whether to render, per docs/fases/phase-5.md's "regla crítica"
     * ("la información no autorizada debe excluirse en servidor").
     *
     * Two independent checks, both required:
     * 1. scope_service::require_user_can_access_student() — can this user see
     *    ANYTHING about this student at all (already covers the student
     *    viewing their own record via viewownfile, unchanged from phase 4.3).
     * 2. Per-field capability, checked here directly (same reasoning as
     *    scope_service calling has_capability() itself: this must always be
     *    enforced together, never left to a caller to remember):
     *    - contentvisible: shown to the student themselves, or to anyone with
     *      local/monlaututoria:viewstudentvisiblecontent.
     *    - noteinternal: shown only to a non-student viewer with
     *      local/monlaututoria:viewinternalnotes — never to the student, no
     *      matter what capability they might otherwise hold.
     *    - noterestricted: same hard floor, gated by
     *      local/monlaututoria:viewrestrictednotes.
     *
     * @param int $entryid
     * @param int $viewerid
     * @return entry
     */
    public function get_for_viewer(int $entryid, int $viewerid): entry {
        $record = $this->repository->get($entryid);

        $this->scopeservice->require_user_can_access_student(
            $viewerid,
            (int) $record->studentid,
            (int) $record->academicyearid
        );

        return entry::from_record($this->mask_content($record, $viewerid));
    }

    /**
     * Paginated, filtered history of one student's tutoring entries (phase
     * 5.4 — "línea de tiempo" of the ficha's "Tutorías" tab), with the same
     * per-field masking as get_for_viewer() applied to every row.
     *
     * A single scope_service check covers the whole page: unlike
     * get_for_viewer() (one entry, one academic year taken from that row),
     * here every row already shares the same $studentid/$academicyearid the
     * caller asked for, so checking once before the query is enough — not a
     * per-row scope re-check, which would be redundant (identical result for
     * every row) and the exact kind of query-count-that-scales-with-rows this
     * project has rejected before (phase 3E.4).
     *
     * @param int $studentid
     * @param int $academicyearid
     * @param array $filters see entry_repository::search() — studentid/academicyearid
     *                        are always forced, even if present
     * @param int $viewerid
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort see entry_repository::sortable_columns()
     * @param string $direction 'ASC' or 'DESC'
     * @return entry[]
     */
    public function get_history_for_student(
        int $studentid,
        int $academicyearid,
        array $filters,
        int $viewerid,
        int $limitfrom = 0,
        int $limitnum = 0,
        string $sort = 'entrydate',
        string $direction = 'DESC'
    ): array {
        $this->scopeservice->require_user_can_access_student($viewerid, $studentid, $academicyearid);

        $filters['studentid'] = $studentid;
        $filters['academicyearid'] = $academicyearid;
        $records = $this->repository->search($filters, $limitfrom, $limitnum, $sort, $direction);

        return array_map(
            fn (\stdClass $record) => entry::from_record($this->mask_content($record, $viewerid)),
            array_values($records)
        );
    }

    /**
     * @param int $studentid
     * @param int $academicyearid
     * @param array $filters see get_history_for_student()
     * @param int $viewerid
     * @return int
     */
    public function count_history_for_student(int $studentid, int $academicyearid, array $filters, int $viewerid): int {
        $this->scopeservice->require_user_can_access_student($viewerid, $studentid, $academicyearid);

        $filters['studentid'] = $studentid;
        $filters['academicyearid'] = $academicyearid;

        return $this->repository->count_search($filters);
    }

    /**
     * Sets whichever of contentvisible/noteinternal/noterestricted the
     * viewer is not authorised to see to null, in place. Shared by
     * get_for_viewer() (one row) and get_history_for_student() (a page of
     * rows) so the masking rule is defined exactly once.
     *
     * @param \stdClass $record raw local_tut_entry row
     * @param int $viewerid
     * @return \stdClass the same object, mutated
     */
    private function mask_content(\stdClass $record, int $viewerid): \stdClass {
        $context = \context_system::instance();
        $isstudent = $viewerid === (int) $record->studentid;

        if (!$isstudent && !has_capability('local/monlaututoria:viewstudentvisiblecontent', $context, $viewerid)) {
            $record->contentvisible = null;
        }
        if ($isstudent || !has_capability('local/monlaututoria:viewinternalnotes', $context, $viewerid)) {
            $record->noteinternal = null;
        }
        if ($isstudent || !has_capability('local/monlaututoria:viewrestrictednotes', $context, $viewerid)) {
            $record->noterestricted = null;
        }

        return $record;
    }

    /**
     * @param int $modalityid
     */
    private function validate_modality(int $modalityid): void {
        try {
            $modality = $this->modalityrepository->get($modalityid);
        } catch (\dml_missing_record_exception $e) {
            throw new \moodle_exception('error_entry_modality_invalid', 'local_monlaututoria');
        }

        if (empty($modality->active)) {
            throw new \moodle_exception('error_entry_modality_invalid', 'local_monlaututoria');
        }
    }

    /**
     * @param int $reasonid
     */
    private function validate_reason(int $reasonid): void {
        try {
            $reason = $this->reasonrepository->get($reasonid);
        } catch (\dml_missing_record_exception $e) {
            throw new \moodle_exception('error_entry_reason_invalid', 'local_monlaututoria');
        }

        if (empty($reason->active)) {
            throw new \moodle_exception('error_entry_reason_invalid', 'local_monlaututoria');
        }
    }

    /**
     * @param \stdClass $participant must contain participanttype; exactly one
     *                                of userid/externalname
     */
    private function validate_participant(\stdClass $participant): void {
        if (!in_array($participant->participanttype ?? null, entry_participant_type::values(), true)) {
            throw new \moodle_exception('error_entry_participant_type_invalid', 'local_monlaututoria');
        }

        $hasuserid = !empty($participant->userid);
        $hasexternalname = !empty($participant->externalname);

        if ($hasuserid === $hasexternalname) {
            // Both set or neither set — exactly one is required.
            throw new \moodle_exception('error_entry_participant_identity_invalid', 'local_monlaututoria');
        }

        if ($hasuserid) {
            $this->assignmentservice->validate_user((int) $participant->userid, 'error_entry_participant_user_invalid');
        }
    }
}
