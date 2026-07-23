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

use local_monlaututoria\domain\csv_import_error_code;

/**
 * Tests for csv_import_parser_service. The parser itself never touches the
 * database, but this still extends advanced_testcase with resetAfterTest()
 * for consistency with every other test in this project.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_parser_service_test extends \advanced_testcase {

    public function test_valid_file_parses_all_columns(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,cohort,assignmenttype,isprimary,timestart,timeend,source\n"
            . "student1,tutor1,2026-2027,cohortA,primary,1,2026-09-01,,manual\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->is_usable());
        $this->assertCount(1, $result->rows);
        $row = $result->rows[0];
        $this->assertTrue($row->is_valid());
        $this->assertSame(2, $row->rownumber);
        $this->assertSame('student1', $row->values['student']);
        $this->assertSame('primary', $row->values['assignmenttype']);
    }

    public function test_missing_required_header_aborts(): void {
        $this->resetAfterTest();

        $content = "student,academicyear\nstudent1,2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertFalse($result->is_usable());
        $this->assertContains(csv_import_error_code::MISSING_REQUIRED_HEADER, $result->fileerrors);
        $this->assertSame([], $result->rows);
    }

    public function test_unknown_column_aborts(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,notacolumn\nstudent1,tutor1,2026-2027,x\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertFalse($result->is_usable());
        $this->assertContains(csv_import_error_code::UNKNOWN_COLUMN, $result->fileerrors);
    }

    public function test_empty_file_returns_empty_file_error(): void {
        $this->resetAfterTest();

        $service = new csv_import_parser_service();
        $result = $service->parse('');

        $this->assertFalse($result->is_usable());
        $this->assertSame([csv_import_error_code::EMPTY_FILE], $result->fileerrors);
    }

    public function test_blank_lines_are_skipped(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\nstudent1,tutor1,2026-2027\n\nstudent2,tutor2,2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertCount(2, $result->rows);
    }

    public function test_missing_required_fields_flagged_per_row(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\n,tutor1,2026-2027\nstudent1,,2026-2027\nstudent1,tutor1,\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertContains(csv_import_error_code::MISSING_STUDENT, $result->rows[0]->errors);
        $this->assertContains(csv_import_error_code::MISSING_TUTOR, $result->rows[1]->errors);
        $this->assertContains(csv_import_error_code::MISSING_ACADEMICYEAR, $result->rows[2]->errors);
        $this->assertCount(3, $result->invalid_rows());
        $this->assertCount(0, $result->valid_rows());
    }

    public function test_isprimary_validation(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,isprimary\ns1,t1,2026-2027,0\ns2,t2,2026-2027,1\ns3,t3,2026-2027,yes\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->rows[0]->is_valid());
        $this->assertTrue($result->rows[1]->is_valid());
        $this->assertContains(csv_import_error_code::INVALID_ISPRIMARY, $result->rows[2]->errors);
    }

    public function test_date_validation(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,timestart,timeend\n"
            . "s1,t1,2026-2027,2026-09-01,2027-06-30\n"
            . "s2,t2,2026-2027,01/09/2026,\n"
            . "s3,t3,2026-2027,2026-13-40,\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->rows[0]->is_valid());
        $this->assertContains(csv_import_error_code::INVALID_TIMESTART, $result->rows[1]->errors);
        $this->assertContains(csv_import_error_code::INVALID_TIMESTART, $result->rows[2]->errors);
    }

    public function test_assignmenttype_and_source_validation(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,assignmenttype,source\n"
            . "s1,t1,2026-2027,primary,manual\n"
            . "s2,t2,2026-2027,not_a_type,not_a_source\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->rows[0]->is_valid());
        $this->assertContains(csv_import_error_code::INVALID_ASSIGNMENTTYPE, $result->rows[1]->errors);
        $this->assertContains(csv_import_error_code::INVALID_SOURCE, $result->rows[1]->errors);
    }

    public function test_duplicate_row_flagged(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear,assignmenttype\n"
            . "s1,t1,2026-2027,primary\n"
            . "s1,t1,2026-2027,primary\n"
            . "s1,t1,2026-2027,co_tutor\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->rows[0]->is_valid());
        $this->assertContains(csv_import_error_code::DUPLICATE_ROW, $result->rows[1]->errors);
        // Different assignmenttype: not a duplicate of row 0.
        $this->assertTrue($result->rows[2]->is_valid());
    }

    public function test_column_count_mismatch_flagged(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\ns1,t1\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertContains(csv_import_error_code::COLUMN_COUNT_MISMATCH, $result->rows[0]->errors);
    }

    public function test_semicolon_delimiter(): void {
        $this->resetAfterTest();

        $content = "student;tutor;academicyear\ns1;t1;2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content, ';');

        $this->assertTrue($result->is_usable());
        $this->assertSame('s1', $result->rows[0]->values['student']);
    }

    public function test_quoted_field_with_embedded_delimiter_and_newline(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\n\"s1, with comma\",\"t1\nwith newline\",2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertCount(1, $result->rows);
        $this->assertSame('s1, with comma', $result->rows[0]->values['student']);
        $this->assertSame("t1\nwith newline", $result->rows[0]->values['tutor']);
    }

    public function test_bom_is_stripped_from_first_header(): void {
        $this->resetAfterTest();

        $content = "\xEF\xBB\xBFstudent,tutor,academicyear\ns1,t1,2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertTrue($result->is_usable());
        $this->assertContains('student', $result->headers);
    }

    public function test_encoding_conversion(): void {
        $this->resetAfterTest();

        $utf8content = "student,tutor,academicyear\nJosé,tutor1,2026-2027\n";
        $latin1content = \core_text::convert($utf8content, 'UTF-8', 'ISO-8859-1');

        $service = new csv_import_parser_service();
        $result = $service->parse($latin1content, ',', 'ISO-8859-1');

        $this->assertTrue($result->is_usable());
        $this->assertSame('José', $result->rows[0]->values['student']);
    }

    public function test_row_numbers_match_file_lines(): void {
        $this->resetAfterTest();

        $content = "student,tutor,academicyear\ns1,t1,2026-2027\ns2,t2,2026-2027\n";

        $service = new csv_import_parser_service();
        $result = $service->parse($content);

        $this->assertSame(2, $result->rows[0]->rownumber);
        $this->assertSame(3, $result->rows[1]->rownumber);
    }
}
