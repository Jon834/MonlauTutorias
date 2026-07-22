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
 * Installation smoke tests for local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {

    public function test_plugin_is_registered(): void {
        $this->resetAfterTest();

        $plugin = \core_plugin_manager::instance()->get_plugin_info('local_monlaututoria');

        $this->assertNotNull($plugin);
        $this->assertSame('local_monlaututoria', $plugin->component);
    }

    public function test_pluginname_string_exists(): void {
        $this->assertSame('Monlau Tutoria', get_string('pluginname', 'local_monlaututoria'));
    }
}
