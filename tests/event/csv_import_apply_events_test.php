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

namespace local_monlaututoria\event;

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\service\csv_import_preview_service;
use local_monlaututoria\service\csv_import_apply_service;
use local_monlaututoria\domain\csv_import_apply_strategy;

/**
 * Test double whose create() throws for a specific student. Deliberately not
 * shared with tests/service/csv_import_apply_service_test.php's identical
 * double: cross-file reuse of a class defined inside a *_test.php file is
 * not something this project's PHPUnit autoloading has been verified to
 * support reliably, so each test file defines its own copy instead of
 * risking an autoload-order-dependent failure.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_test_throwing_create_assignment_repository extends assignment_repository {
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
 * Tests for the csv_import_started/completed/completed_with_errors/failed events.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_events_test extends \advanced_testcase {

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

    public function test_successful_apply_triggers_started_and_completed(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $applyservice = new csv_import_apply_service();

        $sink = $this->redirectEvents();
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(3, $events); // started, assignment_created, completed.
        $this->assertInstanceOf(csv_import_started::class, $events[0]);
        $this->assertInstanceOf(csv_import_completed::class, $events[2]);
        $this->assertSame(1, $events[2]->other['createdcount']);
    }

    public function test_partial_failure_triggers_completed_with_errors(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $failingrepository = new event_test_throwing_create_assignment_repository($student->id);
        $applyservice = new csv_import_apply_service($failingrepository);

        $sink = $this->redirectEvents();
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::PARTIAL_VALID, get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $completedwitherrors = array_values(array_filter(
            $events,
            static fn ($event) => $event instanceof csv_import_completed_with_errors
        ));
        $this->assertCount(1, $completedwitherrors);
        $this->assertSame(1, $completedwitherrors[0]->other['errorcount']);
    }

    public function test_atomic_failure_triggers_failed_event(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $year = $this->create_academic_year();
        $content = "student,tutor,academicyear\n{$student->email},{$tutor->email},{$year->shortname}\n";

        $previewservice = new csv_import_preview_service();
        $preview = $previewservice->preview($content, ',', 'UTF-8', get_admin()->id);

        $failingrepository = new event_test_throwing_create_assignment_repository($student->id);
        $applyservice = new csv_import_apply_service($failingrepository);

        $sink = $this->redirectEvents();
        $applyservice->apply(
            $preview->operationuuid, $content, ',', 'UTF-8',
            csv_import_apply_strategy::ATOMIC_ALL, get_admin()->id
        );
        $events = $sink->get_events();
        $sink->close();

        $failed = array_values(array_filter($events, static fn ($event) => $event instanceof csv_import_failed));
        $this->assertCount(1, $failed);
        $this->assertSame(2, $failed[0]->other['failedrownumber']);
    }
}
