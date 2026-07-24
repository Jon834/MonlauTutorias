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

use local_monlaututoria\domain\csv_import_apply_result;
use local_monlaututoria\domain\csv_import_apply_result_row;
use local_monlaututoria\domain\csv_import_row_outcome;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\csv_import_apply_strategy;

/**
 * Tests for csv_import_error_export_service (phase 3D.4). download() itself
 * is not exercised here: it calls \core\dataformat::download_data(), which
 * sends headers and terminates the request — not something a PHPUnit
 * process can call. columns()/rows()/neutralize() are pure data and cover
 * the actual business rules (which rows are reportable, formula-injection
 * neutralisation) independently of that transport detail.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_error_export_service_test extends \advanced_testcase {

    public function test_neutralize_prefixes_formula_looking_values(): void {
        $this->resetAfterTest();

        $this->assertSame("'=cmd|'/c calc'!A0", csv_import_error_export_service::neutralize("=cmd|'/c calc'!A0"));
        $this->assertSame("'+1+1", csv_import_error_export_service::neutralize('+1+1'));
        $this->assertSame("'-1-1", csv_import_error_export_service::neutralize('-1-1'));
        $this->assertSame("'@SUM(A1:A2)", csv_import_error_export_service::neutralize('@SUM(A1:A2)'));
    }

    public function test_neutralize_leaves_ordinary_values_untouched(): void {
        $this->resetAfterTest();

        $this->assertSame('jane@example.com', csv_import_error_export_service::neutralize('jane@example.com'));
        $this->assertSame('', csv_import_error_export_service::neutralize(''));
        $this->assertSame('Jane Doe', csv_import_error_export_service::neutralize('Jane Doe'));
    }

    public function test_rows_only_includes_not_applied_outcomes(): void {
        $this->resetAfterTest();

        $result = new csv_import_apply_result('uuid-1', csv_import_apply_strategy::PARTIAL_VALID, bulk_operation_status::COMPLETED_WITH_ERRORS, [
            new csv_import_apply_result_row(1, csv_import_row_outcome::CREATED, 10, null, ['student' => 'a@example.com']),
            new csv_import_apply_result_row(2, csv_import_row_outcome::REASSIGNED, 11, null, ['student' => 'b@example.com']),
            new csv_import_apply_result_row(3, csv_import_row_outcome::NO_CHANGE, null, null, ['student' => 'c@example.com']),
            new csv_import_apply_result_row(4, csv_import_row_outcome::SKIPPED_CONFLICT, null, null, ['student' => 'd@example.com']),
            new csv_import_apply_result_row(5, csv_import_row_outcome::SKIPPED_ERROR, null, null, ['student' => 'e@example.com']),
            new csv_import_apply_result_row(6, csv_import_row_outcome::SKIPPED_EXCLUDED, null, null, ['student' => 'f@example.com']),
            new csv_import_apply_result_row(7, csv_import_row_outcome::FAILED, null, 'error_csv_apply_row_failed', ['student' => 'g@example.com']),
        ]);

        $service = new csv_import_error_export_service();
        $rows = $service->rows($result);

        $this->assertCount(4, $rows);
        $reportedrownumbers = array_column($rows, 0);
        $this->assertSame([4, 5, 6, 7], $reportedrownumbers);
    }

    public function test_rows_neutralizes_raw_values_in_place(): void {
        $this->resetAfterTest();

        $result = new csv_import_apply_result('uuid-1', csv_import_apply_strategy::PARTIAL_VALID, bulk_operation_status::COMPLETED_WITH_ERRORS, [
            new csv_import_apply_result_row(
                1,
                csv_import_row_outcome::SKIPPED_ERROR,
                null,
                null,
                ['student' => '=HYPERLINK("http://evil.example")', 'tutor' => 'tutor@example.com', 'academicyear' => '2026-2027', 'cohort' => '']
            ),
        ]);

        $service = new csv_import_error_export_service();
        $rows = $service->rows($result);

        $this->assertCount(1, $rows);
        // Columns: row, outcome, student, tutor, academicyear, cohort, message.
        $this->assertStringStartsWith("'=", $rows[0][2]);
        $this->assertSame('tutor@example.com', $rows[0][3]);
    }

    public function test_columns_match_row_shape(): void {
        $this->resetAfterTest();

        $service = new csv_import_error_export_service();

        $this->assertCount(7, $service->columns());
    }
}
