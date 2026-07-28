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

/**
 * Tests for cohort_visibility_service — the global, admin-curated allowlist
 * of Moodle cohorts this plugin treats as relevant. "Empty means
 * unrestricted" is the core contract under test throughout.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_visibility_service_test extends \advanced_testcase {

    public function test_get_visible_cohort_ids_returns_every_cohort_when_unrestricted(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $ids = (new cohort_visibility_service())->get_visible_cohort_ids();

        $this->assertEqualsCanonicalizing([(int) $cohort1->id, (int) $cohort2->id], $ids);
    }

    public function test_get_visible_cohort_ids_returns_only_enabled_ones_when_restricted(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $this->getDataGenerator()->create_cohort();

        $service = new cohort_visibility_service();
        $service->replace_enabled_cohorts([$cohort1->id], get_admin()->id);

        $this->assertSame([(int) $cohort1->id], $service->get_visible_cohort_ids());
    }

    public function test_get_visible_cohorts_falls_back_to_every_cohort_when_unrestricted(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();

        $cohorts = (new cohort_visibility_service())->get_visible_cohorts();

        $this->assertArrayHasKey((int) $cohort->id, $cohorts);
    }

    public function test_get_visible_cohorts_is_sorted_by_name_when_restricted(): void {
        $this->resetAfterTest();

        $cohortz = $this->getDataGenerator()->create_cohort(['name' => 'Zeta']);
        $cohorta = $this->getDataGenerator()->create_cohort(['name' => 'Alfa']);

        $service = new cohort_visibility_service();
        $service->replace_enabled_cohorts([$cohortz->id, $cohorta->id], get_admin()->id);

        $names = array_values(array_map(static fn (\stdClass $c): string => $c->name, $service->get_visible_cohorts()));
        $this->assertSame(['Alfa', 'Zeta'], $names);
    }

    public function test_get_explicitly_enabled_cohort_ids_is_empty_when_unrestricted(): void {
        $this->resetAfterTest();

        $this->getDataGenerator()->create_cohort();

        $this->assertSame([], (new cohort_visibility_service())->get_explicitly_enabled_cohort_ids());
    }

    public function test_replace_enabled_cohorts_ignores_ids_that_are_not_real_cohorts(): void {
        $this->resetAfterTest();

        $cohort = $this->getDataGenerator()->create_cohort();

        $service = new cohort_visibility_service();
        $service->replace_enabled_cohorts([$cohort->id, 999999], get_admin()->id);

        $this->assertSame([(int) $cohort->id], $service->get_explicitly_enabled_cohort_ids());
    }
}
