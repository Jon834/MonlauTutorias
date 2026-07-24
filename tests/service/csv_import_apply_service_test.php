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
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\domain\csv_import_row_outcome;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\assignment_source;

/**
 * Test double whose create() throws for a specific student, used to
 * simulate a genuine unexpected failure during apply_row() without tripping
 * the "preview changed since generation" guard (which a real DB state change
 * visible to the classifier would otherwise trigger).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class throwing_create_assignment_repository extends assignment_repository {
    public function __construct(private int $throwforstudentid) {
    }

    public function create(\stdClass $data): int {
        if ((int) $data->studentid === $this->throwforstudentid) {
            throw new \moodle_exception('error_assignment_invalid_tutor', 'local_monlaututoria');
        }

        return parent::create($data);
    }
}

/**
 * Test double whose get_by_uuid() always returns a fixed, stale snapshot,
 * used to simulate the race window between apply()'s early PREVIEWED check
 * and its atomic claim() call (phase 3E.3): claim() is inherited unmodified,
 * so it still reads the row's real, current status from the database,
 * letting a test simulate "another request already claimed this operation
 * while this one was busy recomputing the preview".
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stale_previewed_bulk_operation_repository extends bulk_operation_repository {
    public function __construct(private \stdClass $stalesnapshot) {
    }

    public function get_by_uuid(string $operationuuid): \stdClass {
        return $this->stalesnapshot;
    }
}

/**
 * Tests for csv_import_apply_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_service_test extends \advanced_testcase {

    /**
     * @return \stdClass
     */
    private function create_academic_year(): \stdClass {
        $repo = new academic_year_repository();
        $id = $repo->create((object) [
            'name'      => '2026-2027',
            'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate'   => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);

        return $repo->get($id);
    }

    /**
     * @param string $student
     * @param string $tutor
     * @param string $academicyear
     * @return string
     */
    private function build_csv(string $student, string $tutor, string $academicyear): string {
        return "student,tutor,academicyear\n{$student},{$tutor},{$academicyear}\n";
    }

    public function test_valid_row_is_created(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );

        $this->assertSame(bulk_operation_status::COMPLETED, $result->finalstatus);
        $this->assertSame(1, $result->count(csv_import_row_outcome::CREATED));

        $assignmentrepository = new assignment_repository();
        $this->assertTrue($assignmentrepository->has_active_duplicate(
            $student->id, $tutor->id, $year->id, 'primary'
        ));
        $created = $assignmentrepository->get($result->rows[0]->assignmentid);
        $this->assertSame(assignment_source::CSV, $created->source);
    }

    public function test_conflict_row_skipped_by_default(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $assignmentrepository = new assignment_repository();
        $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $existingtutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = $this->build_csv($student->email, $newtutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );

        $this->assertSame(1, $result->count(csv_import_row_outcome::SKIPPED_CONFLICT));
        $this->assertTrue($assignmentrepository->is_current_tutor_of_student($existingtutor->id, $student->id, $year->id));
    }

    public function test_conflict_row_reassigned_when_allowed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $newtutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $assignmentrepository = new assignment_repository();
        $oldid = $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $existingtutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = $this->build_csv($student->email, $newtutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id, true
        );

        $this->assertSame(1, $result->count(csv_import_row_outcome::REASSIGNED));
        $this->assertSame('closed', $assignmentrepository->get($oldid)->status);
        $this->assertTrue($assignmentrepository->is_current_tutor_of_student($newtutor->id, $student->id, $year->id));
    }

    public function test_duplicate_active_never_reassigned_even_when_allowed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $assignmentrepository = new assignment_repository();
        $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id, true
        );

        // This is NO_CHANGE at the preview stage already (exact duplicate),
        // never CONFLICT, so it was never a reassignment candidate to begin with.
        $this->assertSame(1, $result->count(csv_import_row_outcome::NO_CHANGE));
    }

    public function test_error_and_excluded_rows_are_skipped(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv('nobody@example.com', $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );

        $this->assertSame(1, $result->count(csv_import_row_outcome::SKIPPED_ERROR));
    }

    public function test_already_applied_operation_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );

        $this->expectException(\moodle_exception::class);
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );
    }

    public function test_preview_changed_since_generation_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        // Something changes after the preview was generated: the student
        // becomes suspended, which the classifier will now flag as an error.
        global $DB;
        $DB->set_field('user', 'suspended', 1, ['id' => $student->id]);

        $applyservice = new csv_import_apply_service();

        $this->expectException(\moodle_exception::class);
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );
    }

    public function test_partial_valid_continues_after_one_row_fails(): void {
        $this->resetAfterTest();

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        $content = "student,tutor,academicyear\n"
            . "{$student1->email},{$tutor->email},{$year->shortname}\n"
            . "{$student2->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $failingrepository = new throwing_create_assignment_repository($student1->id);
        $applyservice = new csv_import_apply_service($failingrepository);

        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );

        $this->assertSame(bulk_operation_status::COMPLETED_WITH_ERRORS, $result->finalstatus);
        $this->assertSame(1, $result->count(csv_import_row_outcome::FAILED));
        $this->assertSame(1, $result->count(csv_import_row_outcome::CREATED));

        // The row that failed must not have left a partial assignment behind.
        $assignmentrepository = new assignment_repository();
        $this->assertFalse($assignmentrepository->has_active_duplicate($student1->id, $tutor->id, $year->id, 'primary'));
        $this->assertTrue($assignmentrepository->has_active_duplicate($student2->id, $tutor->id, $year->id, 'primary'));
    }

    public function test_atomic_all_rolls_back_everything_on_one_failure(): void {
        $this->resetAfterTest();

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();

        // student2's row comes first alphabetically by rownumber (row 2),
        // student1 fails on row 3 — atomic_all must undo row 2 as well.
        $content = "student,tutor,academicyear\n"
            . "{$student2->email},{$tutor->email},{$year->shortname}\n"
            . "{$student1->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $failingrepository = new throwing_create_assignment_repository($student1->id);
        $applyservice = new csv_import_apply_service($failingrepository);

        $result = $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::ATOMIC_ALL, get_admin()->id
        );

        $this->assertSame(bulk_operation_status::FAILED, $result->finalstatus);

        $assignmentrepository = new assignment_repository();
        $this->assertFalse($assignmentrepository->has_active_duplicate($student1->id, $tutor->id, $year->id, 'primary'));
        $this->assertFalse($assignmentrepository->has_active_duplicate($student2->id, $tutor->id, $year->id, 'primary'));
    }

    public function test_concurrent_apply_is_rejected_by_atomic_claim(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $realbulkoperationrepository = new bulk_operation_repository();
        $stalesnapshot = clone $realbulkoperationrepository->get($preview->operationid);

        // Simulate another request that already won the race and moved this
        // same operation to PROCESSING (e.g. it reached claim() first, while
        // this call is still working from a stale PREVIEWED snapshot it read
        // earlier — exactly the window preview() recomputation opens up).
        $realbulkoperationrepository->update_status($preview->operationid, bulk_operation_status::PROCESSING);

        $staleoperationrepository = new stale_previewed_bulk_operation_repository($stalesnapshot);
        $applyservice = new csv_import_apply_service(null, $staleoperationrepository);

        $this->expectException(\moodle_exception::class);
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );
    }

    public function test_invalid_strategy_rejected(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = $this->build_csv($student->email, $tutor->email, $year->shortname);

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();

        $this->expectException(\moodle_exception::class);
        $applyservice->apply($preview->operationuuid, $content, ',', 'UTF-8', 'not_a_real_strategy', get_admin()->id);
    }
}
