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

/**
 * Tests for enabled_cohort_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class enabled_cohort_repository_test extends \advanced_testcase {

    public function test_get_all_ids_returns_empty_array_when_none_enabled(): void {
        $this->resetAfterTest();

        $this->assertSame([], (new enabled_cohort_repository())->get_all_ids());
    }

    public function test_replace_all_stores_the_given_cohorts(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $repository = new enabled_cohort_repository();
        $repository->replace_all([$cohort1->id, $cohort2->id], get_admin()->id);

        $this->assertEqualsCanonicalizing([(int) $cohort1->id, (int) $cohort2->id], $repository->get_all_ids());
    }

    public function test_replace_all_replaces_rather_than_appends(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $repository = new enabled_cohort_repository();
        $repository->replace_all([$cohort1->id], get_admin()->id);
        $repository->replace_all([$cohort2->id], get_admin()->id);

        $this->assertSame([(int) $cohort2->id], $repository->get_all_ids());
    }

    public function test_replace_all_with_empty_array_clears_the_table(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();

        $repository = new enabled_cohort_repository();
        $repository->replace_all([$cohort->id], get_admin()->id);
        $repository->replace_all([], get_admin()->id);

        $this->assertSame([], $repository->get_all_ids());
    }

    public function test_replace_all_ignores_duplicate_ids(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();

        $repository = new enabled_cohort_repository();
        $repository->replace_all([$cohort->id, $cohort->id], get_admin()->id);

        $this->assertSame([(int) $cohort->id], $repository->get_all_ids());
    }
}
