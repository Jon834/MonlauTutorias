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
use local_monlaututoria\repository\entry_attachment_repository;
use local_monlaututoria\domain\entry_create_command;
use local_monlaututoria\domain\entry_attachment_category;

/**
 * Tests for entry_attachment_service. Uploads are simulated by writing
 * directly into a user draft file area (the same File API primitive a real
 * filemanager submission produces), since PHPUnit cannot drive an actual
 * HTTP multipart upload.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_service_test extends \advanced_testcase {

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
     * @param int $userid whose draft area to write into
     * @param string $filename
     * @param string $content
     * @return int the new draft item id
     */
    private function create_draft_file(int $userid, string $filename, string $content): int {
        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, null, 'user', 'draft', null);

        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($userid)->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftitemid,
            'filepath'  => '/',
            'filename'  => $filename,
        ], $content);

        return $draftitemid;
    }

    /**
     * @param int $studentid
     * @param int $tutorid
     * @param int $academicyearid
     * @return int the new entry id
     */
    private function create_entry_with(int $studentid, int $tutorid, int $academicyearid): int {
        return (new entry_service())->create(
            new entry_create_command($studentid, $tutorid, $academicyearid, strtotime('2026-10-01')),
            get_admin()->id
        );
    }

    public function test_save_uploaded_files_records_new_attachments_and_ignores_reruns(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $entryid = $this->create_entry_with($student->id, $tutor->id, $academicyearid);

        $draftitemid = $this->create_draft_file(get_admin()->id, 'informe.txt', 'contenido del informe');

        $service = new entry_attachment_service();
        $newcount = $service->save_uploaded_files($entryid, $draftitemid, entry_attachment_category::REPORT, get_admin()->id);

        $this->assertSame(1, $newcount);

        $metadata = (new entry_attachment_repository())->get_for_entry($entryid);
        $this->assertCount(1, $metadata);
        $this->assertSame(entry_attachment_category::REPORT, array_values($metadata)[0]->category);
    }

    public function test_save_uploaded_files_rejects_an_invalid_category(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $entryid = $this->create_entry_with($student->id, $tutor->id, $academicyearid);

        $draftitemid = $this->create_draft_file(get_admin()->id, 'archivo.txt', 'contenido');

        $this->expectException(\moodle_exception::class);
        (new entry_attachment_service())->save_uploaded_files($entryid, $draftitemid, 'not-a-real-category', get_admin()->id);
    }

    public function test_save_uploaded_files_rejects_an_annulled_entry(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $entryid = $this->create_entry_with($student->id, $tutor->id, $academicyearid);
        (new entry_service())->annul($entryid, get_admin()->id, 'Ya no aplica');

        $draftitemid = $this->create_draft_file(get_admin()->id, 'archivo.txt', 'contenido');

        $this->expectException(\moodle_exception::class);
        (new entry_attachment_service())->save_uploaded_files($entryid, $draftitemid, entry_attachment_category::REPORT, get_admin()->id);
    }

    public function test_get_for_entry_denies_the_student_even_with_capabilities(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $entryid = $this->create_entry_with($student->id, $tutor->id, $academicyearid);

        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('local/monlaututoria:viewownfile', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        assign_capability('local/monlaututoria:viewinternalnotes', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $student->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->expectException(\moodle_exception::class);
        (new entry_attachment_service())->get_for_entry($entryid, $student->id);
    }

    public function test_get_for_entry_returns_the_uploaded_file_paired_with_its_category(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $entryid = $this->create_entry_with($student->id, $tutor->id, $academicyearid);

        $draftitemid = $this->create_draft_file(get_admin()->id, 'consentimiento.pdf', 'contenido');
        $service = new entry_attachment_service();
        $service->save_uploaded_files($entryid, $draftitemid, entry_attachment_category::CONSENT, get_admin()->id);

        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('local/monlaututoria:viewallassignments', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        assign_capability('local/monlaututoria:viewinternalnotes', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $tutor->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        $pairs = $service->get_for_entry($entryid, $tutor->id);

        $this->assertCount(1, $pairs);
        [$file, $metadata] = $pairs[0];
        $this->assertSame('consentimiento.pdf', $file->get_filename());
        $this->assertSame(entry_attachment_category::CONSENT, $metadata->category);
    }
}
