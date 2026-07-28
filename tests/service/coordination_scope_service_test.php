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

use local_monlaututoria\repository\coordination_scope_repository;

/**
 * Tests for coordination_scope_service, focused on its interaction with the
 * global cohort_visibility_service allowlist added alongside it — a
 * viewallassignments user's "every cohort" default, and an explicitly
 * scoped coordinator's own list, must both respect a globally-disabled
 * cohort.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class coordination_scope_service_test extends \advanced_testcase {

    private function grant_capability(int $userid, string $capability): void {
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $userid, \context_system::instance()->id);
    }

    public function test_viewallassignments_user_gets_every_cohort_when_unrestricted(): void {
        $this->resetAfterTest();

        $viewer = $this->getDataGenerator()->create_user();
        $this->grant_capability($viewer->id, 'local/monlaututoria:viewallassignments');
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $ids = (new coordination_scope_service())->get_effective_cohort_ids($viewer->id);

        $this->assertEqualsCanonicalizing([(int) $cohort1->id, (int) $cohort2->id], $ids);
    }

    public function test_viewallassignments_user_only_gets_globally_enabled_cohorts(): void {
        $this->resetAfterTest();

        $viewer = $this->getDataGenerator()->create_user();
        $this->grant_capability($viewer->id, 'local/monlaututoria:viewallassignments');
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $this->getDataGenerator()->create_cohort();

        (new cohort_visibility_service())->replace_enabled_cohorts([$cohort1->id], get_admin()->id);

        $ids = (new coordination_scope_service())->get_effective_cohort_ids($viewer->id);

        $this->assertSame([(int) $cohort1->id], $ids);
    }

    public function test_coordinator_scope_is_narrowed_by_a_later_global_disable(): void {
        $this->resetAfterTest();

        $coordinator = $this->getDataGenerator()->create_user();
        $this->grant_capability($coordinator->id, 'local/monlaututoria:viewcoordinationdashboard');
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        (new coordination_scope_repository())->replace_user_scopes(
            $coordinator->id, [$cohort1->id, $cohort2->id], get_admin()->id
        );

        // An admin later disables cohort2 globally — the coordinator was
        // explicitly scoped to it before, but the global allowlist wins.
        (new cohort_visibility_service())->replace_enabled_cohorts([$cohort1->id], get_admin()->id);

        $ids = (new coordination_scope_service())->get_effective_cohort_ids($coordinator->id);

        $this->assertSame([(int) $cohort1->id], $ids);
    }

    public function test_coordinator_without_any_scope_gets_no_cohorts(): void {
        $this->resetAfterTest();

        $coordinator = $this->getDataGenerator()->create_user();
        $this->grant_capability($coordinator->id, 'local/monlaututoria:viewcoordinationdashboard');
        $this->getDataGenerator()->create_cohort();

        $this->assertSame([], (new coordination_scope_service())->get_effective_cohort_ids($coordinator->id));
    }
}
