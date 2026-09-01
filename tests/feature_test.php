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
 * Tests for the simple-mode feature switch (fase 13).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_monlaututoria\feature
 */
final class feature_test extends \advanced_testcase {

    public function test_everything_enabled_when_simple_mode_off(): void {
        $this->resetAfterTest();

        // Unset (fresh install) behaves the same as an explicit '0'.
        $this->assertFalse(feature::simple_mode());
        $this->assertTrue(feature::enabled(feature::REFERRALS));
        $this->assertTrue(feature::enabled(feature::AGREEMENTS));
        $this->assertTrue(feature::enabled(feature::NOTIFICATIONS));

        set_config('simplemode', '0', 'local_monlaututoria');
        $this->assertFalse(feature::simple_mode());
        $this->assertTrue(feature::enabled(feature::COORDINATION));
    }

    public function test_advanced_features_hidden_in_simple_mode(): void {
        $this->resetAfterTest();
        set_config('simplemode', '1', 'local_monlaututoria');

        $this->assertTrue(feature::simple_mode());

        foreach ([
            feature::AGREEMENTS,
            feature::FOLLOWUPS,
            feature::REFERRALS,
            feature::COORDINATION,
            feature::NOTIFICATIONS,
            feature::IMPORTS,
            feature::COTUTORS,
            feature::ATTACHMENTS,
            feature::FULLENTRY,
            feature::RESTRICTEDNOTES,
        ] as $hidden) {
            $this->assertFalse(feature::enabled($hidden), $hidden . ' should be hidden in simple mode');
        }

        // The core tutor/student flow is not a listed feature, so it stays on.
        $this->assertTrue(feature::enabled('quickentry'));
    }

    public function test_require_enabled_is_silent_when_enabled(): void {
        $this->resetAfterTest();
        set_config('simplemode', '1', 'local_monlaututoria');

        // Not a hidden feature: no exception.
        feature::require_enabled('quickentry');
        $this->expectNotToPerformAssertions();
    }

    public function test_require_enabled_throws_when_hidden(): void {
        $this->resetAfterTest();
        set_config('simplemode', '1', 'local_monlaututoria');

        $this->expectException(\moodle_exception::class);
        feature::require_enabled(feature::REFERRALS);
    }

    public function test_require_enabled_does_not_throw_in_full_mode(): void {
        $this->resetAfterTest();
        set_config('simplemode', '0', 'local_monlaututoria');

        feature::require_enabled(feature::REFERRALS);
        $this->expectNotToPerformAssertions();
    }
}
