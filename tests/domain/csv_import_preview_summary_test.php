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

namespace local_monlaututoria\domain;

/**
 * Tests for csv_import_preview_summary::from_array(), added in phase 3D.4
 * after discovering csv_import_apply_service called a method that did not
 * exist (php -l cannot catch an undefined static method call — only running
 * this suite, or the real apply flow, would have surfaced it).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_preview_summary_test extends \advanced_testcase {

    public function test_from_array_round_trips_to_array(): void {
        $this->resetAfterTest();

        $summary = new csv_import_preview_summary(10, 5, 2, 1, 1, 1);

        $rebuilt = csv_import_preview_summary::from_array($summary->to_array());

        $this->assertEquals($summary->to_array(), $rebuilt->to_array());
        $this->assertSame(10, $rebuilt->totalrows);
        $this->assertSame(5, $rebuilt->validcount);
        $this->assertSame(2, $rebuilt->warningcount);
        $this->assertSame(1, $rebuilt->conflictcount);
        $this->assertSame(1, $rebuilt->errorcount);
        $this->assertSame(1, $rebuilt->excludedcount);
    }

    public function test_from_array_defaults_missing_keys_to_zero(): void {
        $this->resetAfterTest();

        $summary = csv_import_preview_summary::from_array([]);

        $this->assertSame(0, $summary->totalrows);
        $this->assertSame(0, $summary->validcount);
        $this->assertSame(0, $summary->warningcount);
        $this->assertSame(0, $summary->conflictcount);
        $this->assertSame(0, $summary->errorcount);
        $this->assertSame(0, $summary->excludedcount);
    }
}
