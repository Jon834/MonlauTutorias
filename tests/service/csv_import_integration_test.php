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
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\domain\csv_import_apply_strategy;
use local_monlaututoria\domain\csv_import_row_outcome;
use local_monlaututoria\domain\assignment_source;

/**
 * Integral test for the whole CSV import pipeline (phase 3D.4's "pruebas
 * integrales" requirement): parse -> preview -> dispatch -> apply -> error
 * report, exercised together through the real services rather than in
 * isolation, the way an administrator's browser session actually drives it
 * end to end via assignments/import.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_integration_test extends \advanced_testcase {

    public function test_full_round_trip_small_file(): void {
        $this->resetAfterTest();

        $validstudent = $this->getDataGenerator()->create_user();
        $validtutor = $this->getDataGenerator()->create_user();

        $conflictstudent = $this->getDataGenerator()->create_user();
        $existingtutor = $this->getDataGenerator()->create_user();
        $conflicttutor = $this->getDataGenerator()->create_user();

        $repo = new academic_year_repository();
        $yearid = $repo->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        $year = $repo->get($yearid);

        $assignmentrepository = new assignment_repository();
        $assignmentrepository->create((object) [
            'studentid' => $conflictstudent->id, 'tutorid' => $existingtutor->id,
            'academicyearid' => $year->id, 'assignmenttype' => 'primary', 'isprimary' => 1,
            'createdby' => get_admin()->id,
        ]);

        $content = "student,tutor,academicyear\n"
            . "{$validstudent->email},{$validtutor->email},{$year->shortname}\n"
            . "{$conflictstudent->email},{$conflicttutor->email},{$year->shortname}\n"
            . "nobody@example.com,{$validtutor->email},{$year->shortname}\n";

        // Step 1: preview (parses + resolves against the database).
        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);
        $this->assertSame(3, $preview->summary->totalrows);
        $this->assertSame(1, $preview->summary->validcount);
        $this->assertSame(1, $preview->summary->conflictcount);
        $this->assertSame(1, $preview->summary->errorcount);

        // Step 2: dispatch (small file, applies inline through
        // csv_import_apply_service without the caller needing to know that).
        $dispatchservice = new csv_import_dispatch_service();
        $result = $dispatchservice->dispatch(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id, false, 0
        );

        $this->assertNotNull($result);
        $this->assertSame(1, $result->count(csv_import_row_outcome::CREATED));
        $this->assertSame(1, $result->count(csv_import_row_outcome::SKIPPED_CONFLICT));
        $this->assertSame(1, $result->count(csv_import_row_outcome::SKIPPED_ERROR));

        $this->assertTrue($assignmentrepository->has_active_duplicate(
            $validstudent->id, $validtutor->id, $year->id, 'primary'
        ));
        // The conflict row must not have touched the existing assignment.
        $this->assertTrue($assignmentrepository->is_current_tutor_of_student(
            $existingtutor->id, $conflictstudent->id, $year->id
        ));

        $created = array_values(array_filter(
            $result->rows,
            static fn ($row) => $row->outcome === csv_import_row_outcome::CREATED
        ))[0];
        $this->assertSame(assignment_source::CSV, $assignmentrepository->get($created->assignmentid)->source);

        // Step 3: error report — only the 2 rows that were not created.
        $exportservice = new csv_import_error_export_service();
        $reportrows = $exportservice->rows($result);
        $this->assertCount(2, $reportrows);
    }
}
