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

namespace local_monlaututoria\repository;

use local_monlaututoria\domain\entry_status;

/**
 * Tests for entry_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_repository_test extends \advanced_testcase {

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

    public function test_create_and_get(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();

        $repository = new entry_repository();
        $id = $repository->create((object) [
            'studentid'      => $student->id,
            'tutorid'        => $tutor->id,
            'academicyearid' => $academicyearid,
            'entrydate'      => strtotime('2026-10-01'),
            'contentvisible' => 'Shared content',
            'noteinternal'   => 'Internal note',
            'noterestricted' => 'Restricted note',
            'createdby'      => get_admin()->id,
        ]);

        $record = $repository->get($id);

        $this->assertSame($student->id, (int) $record->studentid);
        $this->assertSame(entry_status::ACTIVE, $record->status);
        $this->assertSame('Shared content', $record->contentvisible);
        $this->assertSame('Internal note', $record->noteinternal);
        $this->assertSame('Restricted note', $record->noterestricted);
    }

    public function test_get_missing_throws_exception(): void {
        $this->resetAfterTest();

        $repository = new entry_repository();

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get(999999);
    }

    public function test_find_by_student_orders_most_recent_first(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new entry_repository();

        $older = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-09-15'), 'createdby' => get_admin()->id,
        ]);
        $newer = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-10-15'), 'createdby' => get_admin()->id,
        ]);

        $records = array_values($repository->find_by_student($student->id));

        $this->assertCount(2, $records);
        $this->assertSame($newer, (int) $records[0]->id);
        $this->assertSame($older, (int) $records[1]->id);
    }

    public function test_search_and_count_search_filter_by_status(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new entry_repository();

        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'createdby' => get_admin()->id,
        ]);

        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'status' => entry_status::ACTIVE]));
        $this->assertSame(0, $repository->count_search(['studentid' => $student->id, 'status' => entry_status::ANNULLED]));

        $records = $repository->search(['studentid' => $student->id]);
        $this->assertCount(1, $records);
    }

    public function test_search_filters_by_modalityid(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $modalityrepository = new modality_repository();
        $modality1 = $modalityrepository->create((object) ['name' => 'M1', 'shortname' => 'm1-' . uniqid(), 'createdby' => get_admin()->id]);
        $modality2 = $modalityrepository->create((object) ['name' => 'M2', 'shortname' => 'm2-' . uniqid(), 'createdby' => get_admin()->id]);
        $repository = new entry_repository();

        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'modalityid' => $modality1, 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'modalityid' => $modality2, 'createdby' => get_admin()->id,
        ]);

        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'modalityid' => $modality1]));
        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'modalityid' => $modality2]));
        $this->assertSame(2, $repository->count_search(['studentid' => $student->id]));
    }

    public function test_search_filters_by_reasonid(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $reasonrepository = new reason_repository();
        $reason1 = $reasonrepository->create((object) ['name' => 'R1', 'shortname' => 'r1-' . uniqid(), 'createdby' => get_admin()->id]);
        $reason2 = $reasonrepository->create((object) ['name' => 'R2', 'shortname' => 'r2-' . uniqid(), 'createdby' => get_admin()->id]);
        $repository = new entry_repository();
        $reasonlinkrepository = new entry_reason_repository();

        $entrywithreason1 = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'createdby' => get_admin()->id,
        ]);
        $reasonlinkrepository->attach($entrywithreason1, [$reason1]);

        $entrywithreason2 = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'createdby' => get_admin()->id,
        ]);
        $reasonlinkrepository->attach($entrywithreason2, [$reason2]);

        $matching = $repository->search(['studentid' => $student->id, 'reasonid' => $reason1]);
        $this->assertCount(1, $matching);
        $this->assertSame($entrywithreason1, (int) array_values($matching)[0]->id);
    }

    public function test_search_filters_by_visibilitytier(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new entry_repository();

        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'noterestricted' => 'Sensitive', 'createdby' => get_admin()->id,
        ]);
        $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'createdby' => get_admin()->id,
        ]);

        $this->assertSame(1, $repository->count_search(['studentid' => $student->id, 'visibilitytier' => 'noterestricted']));
        $this->assertSame(0, $repository->count_search(['studentid' => $student->id, 'visibilitytier' => 'noteinternal']));
        $this->assertSame(2, $repository->count_search(['studentid' => $student->id]));
    }

    public function test_update_editable_fields_ignores_absent_keys_and_touches_only_given_ones(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new entry_repository();

        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-10-01'), 'contentvisible' => 'Original', 'noteinternal' => 'Original internal',
            'createdby' => get_admin()->id,
        ]);

        $editor = $this->getDataGenerator()->create_user();
        $repository->update_editable_fields($id, (object) ['contentvisible' => 'Updated'], $editor->id);

        $record = $repository->get($id);
        $this->assertSame('Updated', $record->contentvisible);
        // noteinternal was never passed — untouched.
        $this->assertSame('Original internal', $record->noteinternal);
        $this->assertSame($editor->id, (int) $record->modifiedby);
        // studentid/tutorid/academicyearid/entrydate/status are never
        // readable from $data at all — same guarantee as
        // assignment_repository::update_editable_fields().
        $this->assertSame($student->id, (int) $record->studentid);
        $this->assertSame(strtotime('2026-10-01'), (int) $record->entrydate);
    }

    public function test_annul_sets_status_and_never_deletes_the_row(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyearid = $this->create_academic_year();
        $repository = new entry_repository();

        $id = $repository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $academicyearid,
            'entrydate' => time(), 'contentvisible' => 'Content', 'createdby' => get_admin()->id,
        ]);

        $repository->annul($id, get_admin()->id);

        $record = $repository->get($id);
        $this->assertSame(entry_status::ANNULLED, $record->status);
        // The row and its content survive — logical annulment, not deletion.
        $this->assertSame('Content', $record->contentvisible);
    }
}
