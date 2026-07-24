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
use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;
use local_monlaututoria\domain\entry_create_command;
use local_monlaututoria\domain\entry_participant_type;

/**
 * Tests for entry_service: create() validation and get_for_viewer()'s
 * capability-based content masking (phase 5.1).
 *
 * The masking matrix is the security-critical part of this phase — it is the
 * concrete implementation of docs/fases/phase-5.md's "regla crítica" and
 * CLAUDE.md's mandatory "alumno intentando consultar notas internas" test.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_service_test extends \advanced_testcase {

    /**
     * @return int
     */
    private function create_academic_year(): int {
        $repo = new academic_year_repository();

        return $repo->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
    }

    /**
     * @param string $capability
     * @param int $userid
     */
    private function grant_capability_to_user(string $capability, int $userid): void {
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $userid, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
    }

    /**
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @return entry_create_command
     */
    private function valid_command(int $studentid, int $tutorid, int $academicyearid): entry_create_command {
        return new entry_create_command(
            $studentid,
            $tutorid,
            $academicyearid,
            strtotime('2026-10-01'),
            null,
            'Shared content',
            'Internal note',
            'Restricted note'
        );
    }

    public function test_create_valid_entry(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->assertIsInt($id);
    }

    public function test_create_persists_participants_and_reasons(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $participantuser = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $reasonid = (new reason_repository())->create((object) [
            'name' => 'Motivo', 'shortname' => 'motivo-' . uniqid(), 'createdby' => get_admin()->id,
        ]);

        $command = new entry_create_command(
            $student->id,
            $tutor->id,
            $academicyearid,
            strtotime('2026-10-01'),
            null,
            'Shared content',
            null,
            null,
            null,
            [$reasonid],
            [
                (object) ['participanttype' => entry_participant_type::TEACHER, 'userid' => $participantuser->id],
                (object) ['participanttype' => entry_participant_type::FAMILY, 'externalname' => 'Jane Doe'],
            ]
        );

        $service = new entry_service();
        $id = $service->create($command, get_admin()->id);

        $participants = (new \local_monlaututoria\repository\entry_participant_repository())->get_for_entry($id);
        $this->assertCount(2, $participants);

        $reasonids = (new \local_monlaututoria\repository\entry_reason_repository())->get_for_entry($id);
        $this->assertSame([$reasonid], $reasonids);
    }

    public function test_student_cannot_be_own_tutor(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create(
            $this->valid_command($student->id, $student->id, $academicyearid),
            get_admin()->id
        );
    }

    public function test_nonexistent_student_rejected(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create(
            $this->valid_command(999999, $tutor->id, $academicyearid),
            get_admin()->id
        );
    }

    public function test_locked_academic_year_rejected_without_override(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        (new academic_year_repository())->set_locked_flag($academicyearid, true, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create(
            $this->valid_command($student->id, $tutor->id, $academicyearid),
            get_admin()->id
        );
    }

    public function test_invalid_reason_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, null, null, null, null, [999999]
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_inactive_reason_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $reasonrepo = new reason_repository();
        $reasonid = $reasonrepo->create((object) [
            'name' => 'R', 'shortname' => 'r-' . uniqid(), 'createdby' => get_admin()->id, 'active' => 0,
        ]);

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, null, null, null, null, [$reasonid]
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_invalid_modality_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'), 999999
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_followup_before_entrydate_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-10'),
            null, null, null, null, strtotime('2026-10-01')
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_participant_with_neither_userid_nor_externalname_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, null, null, null, null, [],
            [(object) ['participanttype' => entry_participant_type::OTHER]]
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_participant_with_both_userid_and_externalname_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $participantuser = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, null, null, null, null, [],
            [(object) [
                'participanttype' => entry_participant_type::OTHER,
                'userid' => $participantuser->id, 'externalname' => 'Someone',
            ]]
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    public function test_participant_invalid_user_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $command = new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, null, null, null, null, [],
            [(object) ['participanttype' => entry_participant_type::TEACHER, 'userid' => 999999]]
        );

        $this->expectException(\moodle_exception::class);
        (new entry_service())->create($command, get_admin()->id);
    }

    // --- get_for_viewer() masking matrix ---

    public function test_get_for_viewer_denies_access_without_scope(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $stranger = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->get_for_viewer($id, $stranger->id);
    }

    public function test_get_for_viewer_student_self_view_never_sees_internal_or_restricted_notes(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        // Deliberately grant the student every content capability there is —
        // the hard floor in get_for_viewer() must still hide the notes from
        // their own self-view regardless (CLAUDE.md's mandatory test case).
        $this->grant_capability_to_user('local/monlaututoria:viewownfile', $student->id);
        $this->grant_capability_to_user('local/monlaututoria:viewstudentvisiblecontent', $student->id);
        $this->grant_capability_to_user('local/monlaututoria:viewinternalnotes', $student->id);
        $this->grant_capability_to_user('local/monlaututoria:viewrestrictednotes', $student->id);

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $entry = $service->get_for_viewer($id, $student->id);

        $this->assertSame('Shared content', $entry->contentvisible);
        $this->assertNull($entry->noteinternal);
        $this->assertNull($entry->noterestricted);
    }

    public function test_get_for_viewer_staff_without_any_content_capability_sees_nothing(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        // Grants scope access but none of the 3 content capabilities.
        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $staff->id);

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $entry = $service->get_for_viewer($id, $staff->id);

        $this->assertNull($entry->contentvisible);
        $this->assertNull($entry->noteinternal);
        $this->assertNull($entry->noterestricted);
    }

    public function test_get_for_viewer_staff_with_viewstudentvisiblecontent_sees_only_shared_content(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $staff->id);
        $this->grant_capability_to_user('local/monlaututoria:viewstudentvisiblecontent', $staff->id);

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $entry = $service->get_for_viewer($id, $staff->id);

        $this->assertSame('Shared content', $entry->contentvisible);
        $this->assertNull($entry->noteinternal);
        $this->assertNull($entry->noterestricted);
    }

    public function test_get_for_viewer_staff_with_viewinternalnotes_sees_internal_note(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $staff->id);
        $this->grant_capability_to_user('local/monlaututoria:viewinternalnotes', $staff->id);

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $entry = $service->get_for_viewer($id, $staff->id);

        $this->assertNull($entry->contentvisible);
        $this->assertSame('Internal note', $entry->noteinternal);
        $this->assertNull($entry->noterestricted);
    }

    public function test_get_for_viewer_staff_with_viewrestrictednotes_sees_restricted_note(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $staff->id);
        $this->grant_capability_to_user('local/monlaututoria:viewrestrictednotes', $staff->id);

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $entry = $service->get_for_viewer($id, $staff->id);

        $this->assertNull($entry->contentvisible);
        $this->assertNull($entry->noteinternal);
        $this->assertSame('Restricted note', $entry->noterestricted);
    }

    // --- get_history_for_student()/count_history_for_student() (phase 5.4) ---

    public function test_get_history_for_student_masks_every_row_and_orders_most_recent_first(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $older = $service->create(new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-01'),
            null, 'Older content', 'Internal', 'Restricted'
        ), get_admin()->id);
        $newer = $service->create(new entry_create_command(
            $student->id, $tutor->id, $academicyearid, strtotime('2026-10-15'),
            null, 'Newer content', 'Internal', 'Restricted'
        ), get_admin()->id);

        // The student's own history view: never sees internal/restricted,
        // regardless of row, same hard floor as get_for_viewer().
        $this->grant_capability_to_user('local/monlaututoria:viewownfile', $student->id);

        $history = $service->get_history_for_student($student->id, $academicyearid, [], $student->id);

        $this->assertCount(2, $history);
        $this->assertSame($newer, $history[0]->id);
        $this->assertSame($older, $history[1]->id);
        $this->assertNull($history[0]->noteinternal);
        $this->assertNull($history[0]->noterestricted);
        // contentvisible is shown to the student themselves, same rule as get_for_viewer().
        $this->assertSame('Newer content', $history[0]->contentvisible);
    }

    public function test_get_history_for_student_applies_filters(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $tutor->id);

        $matching = $service->get_history_for_student(
            $student->id, $academicyearid, ['status' => \local_monlaututoria\domain\entry_status::ACTIVE], $tutor->id
        );
        $this->assertCount(1, $matching);

        $nomatch = $service->get_history_for_student(
            $student->id, $academicyearid, ['status' => \local_monlaututoria\domain\entry_status::ANNULLED], $tutor->id
        );
        $this->assertCount(0, $nomatch);
    }

    public function test_count_history_for_student_denies_access_without_scope(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $stranger = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->count_history_for_student($student->id, $academicyearid, [], $stranger->id);
    }

    public function test_get_history_for_student_does_not_scale_query_count_with_row_count(): void {
        $this->resetAfterTest();

        global $DB;

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        for ($i = 0; $i < 5; $i++) {
            $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);
        }

        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $tutor->id);

        // One scope check + one search() query, never one per row — masking
        // happens in PHP over the already-fetched rows (phase 3E.4 discipline).
        $readsbefore = $DB->perf_get_reads();
        $history = $service->get_history_for_student($student->id, $academicyearid, [], $tutor->id);
        $reads = $DB->perf_get_reads() - $readsbefore;

        $this->assertCount(5, $history);
        $this->assertLessThanOrEqual(3, $reads);
    }

    // --- update()/annul() (phase 5.5) ---

    public function test_update_inside_window_does_not_require_a_reason(): void {
        $this->resetAfterTest();
        set_config('entryeditwindow', DAYSECS, 'local_monlaututoria');

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $result = $service->update($id, (object) ['contentvisible' => 'Corrected'], get_admin()->id);

        $this->assertTrue($result);
    }

    public function test_update_outside_window_requires_a_reason(): void {
        $this->resetAfterTest();
        // A 0-second window means literally any edit is "outside" it.
        set_config('entryeditwindow', 0, 'local_monlaututoria');

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->update($id, (object) ['contentvisible' => 'Corrected'], get_admin()->id);
    }

    public function test_update_outside_window_succeeds_with_a_reason_and_creates_a_snapshot(): void {
        $this->resetAfterTest();
        set_config('entryeditwindow', 0, 'local_monlaututoria');

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $editor = $this->getDataGenerator()->create_user();
        $service->update(
            $id, (object) ['contentvisible' => 'Corrected'], $editor->id, false, 'Fixing a typo'
        );

        $versions = array_values((new \local_monlaututoria\repository\entry_version_repository())->get_for_entry($id));
        $this->assertCount(1, $versions);
        $this->assertSame('Fixing a typo', $versions[0]->changereason);
        $this->assertSame($editor->id, (int) $versions[0]->createdby);
        // The snapshot captures the PRE-edit value, not the new one.
        $snapshot = json_decode($versions[0]->snapshotjson, true);
        $this->assertSame('Shared content', $snapshot['contentvisible']);

        $updated = (new \local_monlaututoria\repository\entry_repository())->get($id);
        $this->assertSame('Corrected', $updated->contentvisible);
    }

    public function test_update_drops_noterestricted_when_caller_cannot_edit_it(): void {
        $this->resetAfterTest();
        set_config('entryeditwindow', DAYSECS, 'local_monlaututoria');

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        // caneditrestricted=false — the attempted change to noterestricted
        // must be silently dropped, same defence-in-depth as create().
        $service->update(
            $id, (object) ['noterestricted' => 'Should not be written'], get_admin()->id, false
        );

        $updated = (new \local_monlaututoria\repository\entry_repository())->get($id);
        $this->assertSame('Restricted note', $updated->noterestricted);
    }

    public function test_update_rejects_editing_an_annulled_entry(): void {
        $this->resetAfterTest();
        set_config('entryeditwindow', DAYSECS, 'local_monlaututoria');

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);
        $service->annul($id, get_admin()->id, 'No longer relevant');

        $this->expectException(\moodle_exception::class);
        $service->update($id, (object) ['contentvisible' => 'Too late'], get_admin()->id);
    }

    public function test_annul_requires_a_non_empty_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->annul($id, get_admin()->id, '   ');
    }

    public function test_annul_sets_status_and_creates_a_snapshot(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);

        $service->annul($id, get_admin()->id, 'Family moved away');

        $entry = (new \local_monlaututoria\repository\entry_repository())->get($id);
        $this->assertSame(\local_monlaututoria\domain\entry_status::ANNULLED, $entry->status);
        // Logical annulment — the content survives.
        $this->assertSame('Shared content', $entry->contentvisible);

        $versions = array_values((new \local_monlaututoria\repository\entry_version_repository())->get_for_entry($id));
        $this->assertCount(1, $versions);
        $this->assertSame('Family moved away', $versions[0]->changereason);
    }

    public function test_annul_rejects_an_already_annulled_entry(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $service = new entry_service();
        $id = $service->create($this->valid_command($student->id, $tutor->id, $academicyearid), get_admin()->id);
        $service->annul($id, get_admin()->id, 'First reason');

        $this->expectException(\moodle_exception::class);
        $service->annul($id, get_admin()->id, 'Second reason');
    }
}
