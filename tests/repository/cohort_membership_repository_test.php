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
 * Tests for cohort_membership_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_membership_repository_test extends \advanced_testcase {

    public function test_get_members_returns_distinct_users_across_cohorts(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        // student1 is in both cohorts: must appear only once.
        cohort_add_member($cohort1->id, $student1->id);
        cohort_add_member($cohort2->id, $student1->id);
        cohort_add_member($cohort2->id, $student2->id);

        $repository = new cohort_membership_repository();
        $members = $repository->get_members([$cohort1->id, $cohort2->id]);

        $this->assertCount(2, $members);
        $this->assertArrayHasKey($student1->id, $members);
        $this->assertArrayHasKey($student2->id, $members);
    }

    public function test_get_members_empty_cohorts_returns_empty(): void {
        $this->resetAfterTest();

        $repository = new cohort_membership_repository();

        $this->assertSame([], $repository->get_members([]));
    }

    public function test_get_memberships_maps_user_to_selected_cohorts(): void {
        $this->resetAfterTest();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $cohort3 = $this->getDataGenerator()->create_cohort();
        $student = $this->getDataGenerator()->create_user();

        cohort_add_member($cohort1->id, $student->id);
        cohort_add_member($cohort2->id, $student->id);
        // Not part of the requested selection: must not appear in the result.
        cohort_add_member($cohort3->id, $student->id);

        $repository = new cohort_membership_repository();
        $memberships = $repository->get_memberships([$cohort1->id, $cohort2->id], [$student->id]);

        $this->assertArrayHasKey($student->id, $memberships);
        $this->assertEqualsCanonicalizing([$cohort1->id, $cohort2->id], $memberships[$student->id]);
    }
}
