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

use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\domain\referral_status;
use local_monlaututoria\domain\priority_level;

/**
 * Tests for referral_service — in particular get_for_viewer()'s
 * capability-only visibility rule (creator, assignee, or managereferrals —
 * never scope_service), the security-critical part of phase 6.4. See the
 * class docblock of referral_service for why this diverges from every other
 * "view" method in this plugin.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_service_test extends \advanced_testcase {

    /**
     * @param int $studentid
     * @param int $tutorid
     * @return int
     */
    private function create_entry(int $studentid, int $tutorid): int {
        $academicyearid = (new academic_year_repository())->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);

        return (new entry_repository())->create((object) [
            'studentid' => $studentid, 'tutorid' => $tutorid, 'academicyearid' => $academicyearid,
            'entrydate' => strtotime('2026-10-01'), 'createdby' => get_admin()->id,
        ]);
    }

    /**
     * @param string $capability
     * @param int $userid
     */
    private function grant_capability_to_user(string $capability, int $userid): void {
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $userid, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
    }

    public function test_create_valid_referral(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);

        $service = new referral_service();
        $id = $service->create($entryid, referral_destination::COORDINATION, 'Repeated absences', priority_level::HIGH, get_admin()->id);

        $this->assertIsInt($id);
    }

    public function test_create_rejects_empty_reason(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new referral_service();

        $this->expectException(\moodle_exception::class);
        $service->create($entryid, referral_destination::COORDINATION, '   ', priority_level::MEDIUM, get_admin()->id);
    }

    public function test_get_for_viewer_allows_creator_assignee_and_managereferrals_but_denies_a_bystander(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $assignee = $this->getDataGenerator()->create_user();
        $coordinator = $this->getDataGenerator()->create_user();
        $bystander = $this->getDataGenerator()->create_user();
        $this->grant_capability_to_user('local/monlaututoria:managereferrals', $coordinator->id);
        // Needed only so $tutor can pass create()'s own scope_service check
        // (creating still requires scope over the student) — get_for_viewer()
        // itself never consults this capability, that is exactly what this
        // test is verifying.
        $this->grant_capability_to_user('local/monlaututoria:viewallassignments', $tutor->id);

        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new referral_service();

        $id = $service->create($entryid, referral_destination::ORIENTATION, 'A', priority_level::MEDIUM, $tutor->id);
        $service->assign($id, $assignee->id, get_admin()->id);

        // Creator: allowed.
        $this->assertSame($id, $service->get_for_viewer($id, $tutor->id)->id);
        // Assignee: allowed.
        $this->assertSame($id, $service->get_for_viewer($id, $assignee->id)->id);
        // managereferrals: allowed, even with no relationship to the student.
        $this->assertSame($id, $service->get_for_viewer($id, $coordinator->id)->id);

        // A bystander with none of the above: denied. This is the
        // security-critical case — "Derivaciones limitadas por capacidades".
        $this->expectException(\moodle_exception::class);
        $service->get_for_viewer($id, $bystander->id);
    }

    public function test_assign_resolve_cancel_transitions(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $staff = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new referral_service();

        $id = $service->create($entryid, referral_destination::COORDINATION, 'A', priority_level::MEDIUM, get_admin()->id);

        $service->assign($id, $staff->id, get_admin()->id);
        $referral = $service->get_for_viewer($id, get_admin()->id);
        $this->assertSame(referral_status::IN_PROGRESS, $referral->status);
        $this->assertSame($staff->id, $referral->assignedto);

        $service->resolve($id, 'Resolved via family meeting', get_admin()->id);
        $referral = $service->get_for_viewer($id, get_admin()->id);
        $this->assertSame(referral_status::RESOLVED, $referral->status);
        $this->assertSame('Resolved via family meeting', $referral->resolution);
    }

    public function test_resolve_rejects_empty_resolution(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new referral_service();

        $id = $service->create($entryid, referral_destination::COORDINATION, 'A', priority_level::MEDIUM, get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->resolve($id, '', get_admin()->id);
    }

    public function test_cancel_rejects_already_resolved_referral(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $entryid = $this->create_entry($student->id, $tutor->id);
        $service = new referral_service();

        $id = $service->create($entryid, referral_destination::COORDINATION, 'A', priority_level::MEDIUM, get_admin()->id);
        $service->resolve($id, 'Done', get_admin()->id);

        $this->expectException(\moodle_exception::class);
        $service->cancel($id, get_admin()->id);
    }
}
