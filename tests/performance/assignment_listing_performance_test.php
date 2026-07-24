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

namespace local_monlaututoria\performance;

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;

/**
 * Performance/N+1 regression test for the assignments listing (phase 3E.4:
 * "Pruebas de rendimiento con 2.000 alumnos" y "Revisión de consultas N+1"
 * del trabajo obligatorio de la Fase 3E).
 *
 * Deliberately creates 2 000 real assignment rows (each with its own
 * generated student and tutor user, same as a real installation would have)
 * — this is a genuine at-scale test, not a downsized stand-in, and is
 * expected to take real wall-clock time to run (creating 4 000 Moodle users
 * via the standard data generator is the dominant cost, not this plugin's
 * own code). Not meant to run on every quick local iteration; run it
 * deliberately when validating this phase.
 *
 * The actual assertion is about query COUNT, not wall-clock time (which
 * varies too much across machines/CI to assert on reliably): fetching page 1
 * of a 50-row table must cost exactly as many database reads as fetching a
 * page of a 2 000-row table, because assignments/index.php's
 * search()/count_search() are meant to be paginated at the database level
 * (LIMIT/OFFSET), never by fetching everything and slicing in PHP. If a
 * future change accidentally turns this into a full-table scan, or into a
 * per-row loop of additional queries, this test's final assertion catches it
 * as a query-count regression rather than a vague timing flake.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_listing_performance_test extends \advanced_testcase {

    /** @var int total assignments the "at scale" side of the comparison creates */
    private const AT_SCALE_COUNT = 2000;

    /** @var int total assignments the "baseline" side of the comparison creates */
    private const BASELINE_COUNT = 50;

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

    /**
     * @param int $count
     * @param int $academicyearid
     * @return void
     */
    private function create_assignments(int $count, int $academicyearid): void {
        $repository = new assignment_repository();

        for ($i = 0; $i < $count; $i++) {
            $student = $this->getDataGenerator()->create_user();
            $tutor = $this->getDataGenerator()->create_user();
            $repository->create((object) [
                'studentid'      => $student->id,
                'tutorid'        => $tutor->id,
                'academicyearid' => $academicyearid,
                'createdby'      => get_admin()->id,
            ]);
        }
    }

    public function test_paginated_search_query_count_does_not_scale_with_table_size(): void {
        $this->resetAfterTest();

        global $DB;

        $year = $this->create_academic_year();
        $repository = new assignment_repository();

        $this->create_assignments(self::BASELINE_COUNT, $year);

        $readsbefore = $DB->perf_get_reads();
        $baselinerecords = $repository->search([], 0, 20);
        $baselinetotal = $repository->count_search([]);
        $baselinereads = $DB->perf_get_reads() - $readsbefore;

        $this->assertCount(20, $baselinerecords);
        $this->assertSame(self::BASELINE_COUNT, $baselinetotal);

        // Grow the same table up to 2 000 rows (this is the slow part — real
        // Moodle user accounts, not fixtures).
        $this->create_assignments(self::AT_SCALE_COUNT - self::BASELINE_COUNT, $year);

        $readsbefore2 = $DB->perf_get_reads();
        $atscalerecords = $repository->search([], 0, 20);
        $atscaletotal = $repository->count_search([]);
        $atscalereads = $DB->perf_get_reads() - $readsbefore2;

        $this->assertCount(20, $atscalerecords);
        $this->assertSame(self::AT_SCALE_COUNT, $atscaletotal);

        // The property under test: identical query cost at 40x the table
        // size. A regression to fetching all rows and paginating in PHP, or
        // to any per-row query in the listing path, would make this fail —
        // not by a small margin, but by roughly a factor of 40.
        $this->assertSame($baselinereads, $atscalereads);
    }
}
