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

namespace local_monlaututoria\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\entry_participant_repository;
use local_monlaututoria\repository\entry_version_repository;
use local_monlaututoria\repository\entry_attachment_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\referral_repository;
use local_monlaututoria\domain\entry_participant_type;
use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\service\entry_attachment_service;

/**
 * Tests for the retention policy decided in phase 3E.6: local_tut_assignment
 * and local_tut_bulkoperation are now exported and anonymised (never
 * deleted) on erasure, and local_tut_bulkoperation additionally has a 90-day
 * TTL for finished operations (see cleanup_bulk_operations_task_test.php).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \advanced_testcase {

    /**
     * @return int academic year id
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

    public function test_get_contexts_for_userid_finds_assignment_involvement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($student->id)->get_contexts());
        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_get_contexts_for_userid_finds_bulk_operation_involvement(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();

        (new bulk_operation_repository())->create((object) [
            'operationuuid'  => bulk_operation_repository::generate_uuid(),
            'primarytutorid' => $tutor->id,
            'createdby'      => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_get_contexts_for_userid_finds_entry_involvement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $participant = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        (new entry_participant_repository())->create((object) [
            'entryid' => $entryid, 'participanttype' => entry_participant_type::TEACHER,
            'userid' => $participant->id, 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($student->id)->get_contexts());
        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(1, provider::get_contexts_for_userid($participant->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_export_user_data_includes_assignments_with_role_and_counterpart(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Seguimiento inicial',
            'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::export_user_data($approved);

        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([get_string('pluginname', 'local_monlaututoria')]);
        $this->assertNotEmpty($data->assignments);
        $this->assertContains('student', $data->assignments[0]->yourrole);
        $this->assertSame(fullname($tutor), $data->assignments[0]->counterpart);
        $this->assertSame('Seguimiento inicial', $data->assignments[0]->note);
    }

    public function test_delete_data_for_user_anonymizes_without_deleting_the_row(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Contiene el nombre del alumno',
            'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $repository->get($id);

        // The row still exists — this is anonymisation, not deletion — and
        // the tutor's own side of the relationship is untouched.
        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($tutor->id, (int) $record->tutorid);
        $this->assertNull($record->note);
        // Historically relevant facts survive anonymisation.
        $this->assertSame('primary', $record->assignmenttype);
        $this->assertSame(1, (int) $record->isprimary);
    }

    public function test_delete_data_for_users_anonymizes_each_listed_user(): void {
        $this->resetAfterTest();

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id1 = $repository->create((object) [
            'studentid' => $student1->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);
        $id2 = $repository->create((object) [
            'studentid' => $student2->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_userlist($context, 'local_monlaututoria', [$student1->id, $student2->id]);
        provider::delete_data_for_users($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $this->assertSame($noreply, (int) $repository->get($id1)->studentid);
        $this->assertSame($noreply, (int) $repository->get($id2)->studentid);
        // The shared tutor is a bystander to these two erasure requests.
        $this->assertSame($tutor->id, (int) $repository->get($id1)->tutorid);
        $this->assertSame($tutor->id, (int) $repository->get($id2)->tutorid);
    }

    public function test_delete_data_for_all_users_in_context_anonymizes_everyone(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new assignment_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'note' => 'Nota', 'createdby' => get_admin()->id,
        ]);

        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $noreply = \core_user::get_noreply_user()->id;
        $record = $repository->get($id);
        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($noreply, (int) $record->tutorid);
        $this->assertNull($record->note);
    }

    public function test_get_users_in_context_lists_students_tutors_and_bulk_operation_roles(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);

        $userlist = new userlist(\context_system::instance(), 'local_monlaututoria');
        provider::get_users_in_context($userlist);

        $userids = $userlist->get_userids();
        $this->assertContains((int) $student->id, $userids);
        $this->assertContains((int) $tutor->id, $userids);
    }

    public function test_export_user_data_includes_entries_with_notes_unmasked(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'),
            'contentvisible' => 'Contenido compartido', 'noteinternal' => 'Nota interna',
            'noterestricted' => 'Nota restringida', 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::export_user_data($approved);

        $data = writer::with_context($context)->get_data([get_string('pluginname', 'local_monlaututoria')]);

        $this->assertNotEmpty($data->entries);
        $this->assertContains('student', $data->entries[0]->yourrole);
        // A subject access request is not gated by the normal
        // viewstudentvisiblecontent/viewinternalnotes/viewrestrictednotes
        // capabilities — every note is exported unmasked, same as note/
        // closereason on local_tut_assignment already are.
        $this->assertSame('Contenido compartido', $data->entries[0]->contentvisible);
        $this->assertSame('Nota interna', $data->entries[0]->noteinternal);
        $this->assertSame('Nota restringida', $data->entries[0]->noterestricted);
    }

    public function test_delete_data_for_user_anonymizes_entry_identity_but_keeps_notes(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $repository = new entry_repository();
        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'),
            'contentvisible' => 'Contenido', 'noteinternal' => 'Interna', 'noterestricted' => 'Restringida',
            'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $repository->get($id);

        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($tutor->id, (int) $record->tutorid);
        // Decision taken with the user when this table was introduced (phase
        // 5.1): same policy as local_tut_assignment's note — content is
        // conserved, only identity is anonymised.
        $this->assertSame('Contenido', $record->contentvisible);
        $this->assertSame('Interna', $record->noteinternal);
        $this->assertSame('Restringida', $record->noterestricted);
    }

    public function test_delete_data_for_user_anonymizes_entry_participant_userid(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $participant = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $participantrepository = new entry_participant_repository();
        $participantrepository->create((object) [
            'entryid' => $entryid, 'participanttype' => entry_participant_type::TEACHER,
            'userid' => $participant->id, 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($participant, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $records = array_values($participantrepository->get_for_entry($entryid));
        $this->assertSame($noreply, (int) $records[0]->userid);
    }

    /**
     * @param int $entryid
     * @param int $userid createdby of the file
     * @param string $filename
     * @param string $content
     * @return string the new pathnamehash
     */
    private function create_attachment_file(int $entryid, int $userid, string $filename, string $content): string {
        $file = get_file_storage()->create_file_from_string([
            'contextid' => \context_system::instance()->id,
            'component' => 'local_monlaututoria',
            'filearea'  => entry_attachment_service::FILEAREA,
            'itemid'    => $entryid,
            'filepath'  => '/',
            'filename'  => $filename,
        ], $content);

        return $file->get_pathnamehash();
    }

    public function test_get_contexts_for_userid_finds_entry_version_involvement(): void {
        $this->resetAfterTest();

        $editor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        (new entry_version_repository())->create((object) [
            'entryid' => $entryid, 'versionnumber' => 1,
            'snapshotjson' => json_encode(['status' => 'active']), 'createdby' => $editor->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($editor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_get_contexts_for_userid_finds_entry_attachment_involvement(): void {
        $this->resetAfterTest();

        $uploader = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $pathnamehash = $this->create_attachment_file($entryid, $uploader->id, 'informe.pdf', 'contenido');
        (new entry_attachment_repository())->create((object) [
            'entryid' => $entryid, 'pathnamehash' => $pathnamehash,
            'category' => 'report', 'createdby' => $uploader->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($uploader->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_export_user_data_includes_entry_versions_and_attachments(): void {
        $this->resetAfterTest();

        $editor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        (new entry_version_repository())->create((object) [
            'entryid' => $entryid, 'versionnumber' => 1,
            'snapshotjson' => json_encode(['contentvisible' => 'Antes de editar']),
            'changereason' => 'Corrección de fecha', 'createdby' => $editor->id,
        ]);
        $pathnamehash = $this->create_attachment_file($entryid, $editor->id, 'consentimiento.pdf', 'contenido');
        (new entry_attachment_repository())->create((object) [
            'entryid' => $entryid, 'pathnamehash' => $pathnamehash,
            'category' => 'consent', 'description' => 'Firmado por la familia',
            'createdby' => $editor->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($editor, 'local_monlaututoria', [$context->id]);
        provider::export_user_data($approved);

        $data = writer::with_context($context)->get_data([get_string('pluginname', 'local_monlaututoria')]);

        $this->assertNotEmpty($data->entryversions);
        $this->assertSame('Corrección de fecha', $data->entryversions[0]->changereason);
        $this->assertSame('Antes de editar', $data->entryversions[0]->snapshot['contentvisible']);

        $this->assertNotEmpty($data->entryattachments);
        $this->assertSame('consentimiento.pdf', $data->entryattachments[0]->filename);
        $this->assertSame('Firmado por la familia', $data->entryattachments[0]->description);
    }

    public function test_delete_data_for_user_anonymizes_entry_version_but_keeps_snapshot(): void {
        $this->resetAfterTest();

        $editor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $versionrepository = new entry_version_repository();
        $versionrepository->create((object) [
            'entryid' => $entryid, 'versionnumber' => 1,
            'snapshotjson' => json_encode(['contentvisible' => 'Menciona a Juan']),
            'changereason' => 'Motivo', 'createdby' => $editor->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($editor, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $records = array_values($versionrepository->get_for_entry($entryid));
        $this->assertSame($noreply, (int) $records[0]->createdby);
        // Institutional-history value conserved, same policy as local_tut_entry.
        $this->assertStringContainsString('Menciona a Juan', $records[0]->snapshotjson);
        $this->assertSame('Motivo', $records[0]->changereason);
    }

    public function test_delete_data_for_user_anonymizes_entry_attachment_and_clears_description(): void {
        $this->resetAfterTest();

        $uploader = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $pathnamehash = $this->create_attachment_file($entryid, $uploader->id, 'informe.pdf', 'contenido');
        $attachmentrepository = new entry_attachment_repository();
        $attachmentrepository->create((object) [
            'entryid' => $entryid, 'pathnamehash' => $pathnamehash,
            'category' => 'report', 'description' => 'Informe de Juan Pérez',
            'createdby' => $uploader->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($uploader, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $records = array_values($attachmentrepository->get_for_entry($entryid));
        $this->assertSame($noreply, (int) $records[0]->createdby);
        $this->assertNull($records[0]->description);
        // category and the file itself are left untouched.
        $this->assertSame('report', $records[0]->category);
    }

    public function test_get_contexts_for_userid_finds_agreement_followup_and_referral_involvement(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        (new agreement_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'A',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => time(), 'createdby' => get_admin()->id,
        ]);
        (new followup_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => time(), 'createdby' => get_admin()->id,
        ]);
        (new referral_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::COORDINATION,
            'reason' => 'A', 'createdby' => get_admin()->id,
        ]);

        $this->assertCount(1, provider::get_contexts_for_userid($student->id)->get_contexts());
        $this->assertCount(1, provider::get_contexts_for_userid($tutor->id)->get_contexts());
        $this->assertCount(0, provider::get_contexts_for_userid($bystander->id)->get_contexts());
    }

    public function test_export_user_data_includes_agreements_followups_and_referrals(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        (new agreement_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'Attend weekly review',
            'responsibletype' => agreement_responsible_type::STUDENT, 'responsibleuserid' => $student->id,
            'duedate' => time(), 'createdby' => get_admin()->id,
        ]);
        (new followup_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => time(), 'createdby' => get_admin()->id,
        ]);
        (new referral_repository())->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::ORIENTATION,
            'reason' => 'Repeated absences', 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::export_user_data($approved);

        $data = writer::with_context($context)->get_data([get_string('pluginname', 'local_monlaututoria')]);

        $this->assertNotEmpty($data->agreements);
        $this->assertSame('Attend weekly review', $data->agreements[0]->description);
        $this->assertNotEmpty($data->followups);
        $this->assertNotEmpty($data->referrals);
        $this->assertSame('Repeated absences', $data->referrals[0]->reason);
    }

    public function test_delete_data_for_user_anonymizes_agreement_but_keeps_description(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $agreementrepository = new agreement_repository();
        $agreementid = $agreementrepository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'description' => 'Mentions Juan',
            'responsibletype' => agreement_responsible_type::TUTOR, 'responsibleuserid' => $tutor->id,
            'duedate' => time(), 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $agreementrepository->get($agreementid);
        $this->assertSame($noreply, (int) $record->studentid);
        $this->assertSame($tutor->id, (int) $record->responsibleuserid);
        $this->assertSame('Mentions Juan', $record->description);
    }

    public function test_delete_data_for_user_anonymizes_followup(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $followuprepository = new followup_repository();
        $followupid = $followuprepository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'duedate' => time(), 'createdby' => get_admin()->id,
        ]);

        $context = \context_system::instance();
        $approved = new approved_contextlist($student, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $followuprepository->get($followupid);
        $this->assertSame($noreply, (int) $record->studentid);
    }

    public function test_delete_data_for_user_anonymizes_referral_but_keeps_reason_and_resolution(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $year,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
        $referralrepository = new referral_repository();
        $referralid = $referralrepository->create((object) [
            'entryid' => $entryid, 'studentid' => $student->id, 'destination' => referral_destination::COORDINATION,
            'reason' => 'Mentions Juan', 'createdby' => get_admin()->id,
        ]);
        $referralrepository->assign($referralid, $staff->id, get_admin()->id);
        $referralrepository->resolve($referralid, 'Met with the family', get_admin()->id);

        $context = \context_system::instance();
        $approved = new approved_contextlist($staff, 'local_monlaututoria', [$context->id]);
        provider::delete_data_for_user($approved);

        $noreply = \core_user::get_noreply_user()->id;
        $record = $referralrepository->get($referralid);
        $this->assertSame($noreply, (int) $record->assignedto);
        // Institutional-history content conserved for both fields.
        $this->assertSame('Mentions Juan', $record->reason);
        $this->assertSame('Met with the family', $record->resolution);
    }
}
