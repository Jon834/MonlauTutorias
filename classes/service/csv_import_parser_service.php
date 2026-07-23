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
use local_monlaututoria\domain\csv_import_row;
use local_monlaututoria\domain\csv_parse_result;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\assignment_source;

/**
 * Parses a CSV assignment import into structured, validated rows. Purely
 * syntactic: never queries the database (no user/cohort/academic year
 * existence checks) — that is phase 3D.2's job, reusing assignment_service's
 * validators rather than duplicating them here, the same way
 * cohort_assignment_preview_service already does for phase 3C.
 *
 * Takes raw file content (a string), not a Moodle stored_file: the File API
 * upload flow belongs to phase 3D.2, which will hand this service the
 * decoded content of whatever the administrator uploaded.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_parser_service {

    /** @var string[] */
    private const REQUIRED_HEADERS = ['student', 'tutor', 'academicyear'];

    /** @var string[] */
    private const OPTIONAL_HEADERS = ['cohort', 'assignmenttype', 'isprimary', 'timestart', 'timeend', 'source'];

    /**
     * @param string $content raw file content, in $encoding
     * @param string $delimiter single character, e.g. ',' or ';'
     * @param string $encoding source encoding understood by core_text::convert(), e.g. 'UTF-8', 'ISO-8859-1'
     * @return csv_parse_result
     */
    public function parse(string $content, string $delimiter = ',', string $encoding = 'UTF-8'): csv_parse_result {
        if ($encoding !== 'UTF-8') {
            $content = \core_text::convert($content, $encoding, 'UTF-8');
        }
        // Strip a UTF-8 byte-order mark, if present, so it never ends up
        // prepended to the first header name.
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            return new csv_parse_result([], [], [csv_import_error_code::EMPTY_FILE]);
        }

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $rawheaders = fgetcsv($stream, 0, $delimiter);
        if ($rawheaders === false) {
            fclose($stream);

            return new csv_parse_result([], [], [csv_import_error_code::EMPTY_FILE]);
        }

        $headermap = []; // column index => recognised lowercase header name.
        $fileerrors = [];
        $allowedheaders = array_merge(self::REQUIRED_HEADERS, self::OPTIONAL_HEADERS);
        foreach ($rawheaders as $index => $rawheader) {
            $header = strtolower(trim((string) $rawheader));
            if (!in_array($header, $allowedheaders, true)) {
                $fileerrors[] = csv_import_error_code::UNKNOWN_COLUMN;
                continue;
            }
            $headermap[$index] = $header;
        }

        $foundheaders = array_values($headermap);
        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $foundheaders, true)) {
                $fileerrors[] = csv_import_error_code::MISSING_REQUIRED_HEADER;
            }
        }

        if (!empty($fileerrors)) {
            fclose($stream);

            return new csv_parse_result(array_values(array_unique($foundheaders)), [], array_values(array_unique($fileerrors)));
        }

        $rows = [];
        $seenkeys = [];
        $rownumber = 1;
        $expectedcolumns = count($rawheaders);
        while (($fields = fgetcsv($stream, 0, $delimiter)) !== false) {
            $rownumber++;

            if (count($fields) === 1 && trim((string) ($fields[0] ?? '')) === '') {
                continue;
            }

            $rows[] = $this->build_row($rownumber, $fields, $headermap, $expectedcolumns, $seenkeys);
        }
        fclose($stream);

        return new csv_parse_result(array_values($headermap), $rows, []);
    }

    /**
     * @param int $rownumber
     * @param array $fields
     * @param array<int, string> $headermap column index => recognised header name
     * @param int $expectedcolumns width of the original header row (including any unrecognised columns)
     * @param array<string, bool> $seenkeys duplicate-detection accumulator, updated by reference
     * @return csv_import_row
     */
    private function build_row(
        int $rownumber,
        array $fields,
        array $headermap,
        int $expectedcolumns,
        array &$seenkeys
    ): csv_import_row {
        $errors = [];

        if (count($fields) !== $expectedcolumns) {
            $errors[] = csv_import_error_code::COLUMN_COUNT_MISMATCH;
        }

        $values = [];
        foreach ($headermap as $index => $header) {
            $values[$header] = trim((string) ($fields[$index] ?? ''));
        }

        if (($values['student'] ?? '') === '') {
            $errors[] = csv_import_error_code::MISSING_STUDENT;
        }
        if (($values['tutor'] ?? '') === '') {
            $errors[] = csv_import_error_code::MISSING_TUTOR;
        }
        if (($values['academicyear'] ?? '') === '') {
            $errors[] = csv_import_error_code::MISSING_ACADEMICYEAR;
        }

        if (isset($values['isprimary']) && $values['isprimary'] !== '' && !in_array($values['isprimary'], ['0', '1'], true)) {
            $errors[] = csv_import_error_code::INVALID_ISPRIMARY;
        }
        if (isset($values['timestart']) && $values['timestart'] !== '' && !$this->is_valid_date($values['timestart'])) {
            $errors[] = csv_import_error_code::INVALID_TIMESTART;
        }
        if (isset($values['timeend']) && $values['timeend'] !== '' && !$this->is_valid_date($values['timeend'])) {
            $errors[] = csv_import_error_code::INVALID_TIMEEND;
        }
        if (
            isset($values['assignmenttype']) && $values['assignmenttype'] !== ''
            && !in_array($values['assignmenttype'], assignment_type::values(), true)
        ) {
            $errors[] = csv_import_error_code::INVALID_ASSIGNMENTTYPE;
        }
        if (
            isset($values['source']) && $values['source'] !== ''
            && !in_array($values['source'], assignment_source::values(), true)
        ) {
            $errors[] = csv_import_error_code::INVALID_SOURCE;
        }

        $duplicatekey = strtolower($values['student'] ?? '') . '|' . strtolower($values['tutor'] ?? '') . '|'
            . strtolower($values['academicyear'] ?? '') . '|' . strtolower($values['assignmenttype'] ?? '');
        if (isset($seenkeys[$duplicatekey])) {
            $errors[] = csv_import_error_code::DUPLICATE_ROW;
        } else {
            $seenkeys[$duplicatekey] = true;
        }

        return new csv_import_row($rownumber, $values, $errors);
    }

    /**
     * Strict ISO 8601 calendar date (YYYY-MM-DD), locale-independent.
     *
     * @param string $value
     * @return bool
     */
    private function is_valid_date(string $value): bool {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return false;
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]);
    }
}
