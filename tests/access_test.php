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

namespace local_monlaututoria;

/**
 * Tests that every capability is denied by default (empty archetypes).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access_test extends \advanced_testcase {

    /**
     * @return array
     */
    public static function capability_provider(): array {
        return [
            'view'                      => ['local/monlaututoria:view'],
            'viewconfiguration'         => ['local/monlaututoria:viewconfiguration'],
            'manageacademicyears'       => ['local/monlaututoria:manageacademicyears'],
            'managecatalogues'          => ['local/monlaututoria:managecatalogues'],
            'overridelock'              => ['local/monlaututoria:overridelock'],
            'viewownstudents'           => ['local/monlaututoria:viewownstudents'],
            'viewstudent'               => ['local/monlaututoria:viewstudent'],
            'viewhistoricalassignments' => ['local/monlaututoria:viewhistoricalassignments'],
            'assignstudents'            => ['local/monlaututoria:assignstudents'],
            'manageassignments'         => ['local/monlaututoria:manageassignments'],
            'managecohortassignments'   => ['local/monlaututoria:managecohortassignments'],
            'importassignments'         => ['local/monlaututoria:importassignments'],
            'reassignstudents'          => ['local/monlaututoria:reassignstudents'],
            'viewallassignments'        => ['local/monlaututoria:viewallassignments'],
            'manageclosedassignments'   => ['local/monlaututoria:manageclosedassignments'],
        ];
    }

    /**
     * @dataProvider capability_provider
     */
    public function test_capability_denied_by_default(string $capability): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse(has_capability($capability, \context_system::instance()));
    }
}
