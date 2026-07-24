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
use local_monlaututoria\domain\csv_import_row_outcome;

/**
 * Builds the downloadable "errors" CSV report for an applied import (phase
 * 3D.4): only the rows that were NOT applied as-is (conflicts left untouched,
 * error/excluded rows, and rows that failed at apply time) — successful
 * creates/reassignments/no-changes are already covered by the inline summary
 * and are not repeated here.
 *
 * Every raw value comes straight from the administrator-supplied file, so it
 * is neutralised against spreadsheet formula injection before being written
 * out: a cell starting with =, +, - or @ gets a leading single quote, the
 * standard mitigation that forces spreadsheet applications to treat it as
 * literal text instead of a formula.
 *
 * Never persisted: called once, synchronously, from the same request that
 * already holds the csv_import_apply_result in memory (see
 * assignments/import.php and assignments/import_report.php).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_error_export_service {

    /** @var string[] outcomes worth reporting as "not applied" */
    private const REPORTABLE_OUTCOMES = [
        csv_import_row_outcome::SKIPPED_CONFLICT,
        csv_import_row_outcome::SKIPPED_ERROR,
        csv_import_row_outcome::SKIPPED_EXCLUDED,
        csv_import_row_outcome::FAILED,
    ];

    /**
     * @return string[] column headers, in the same order as rows()
     */
    public function columns(): array {
        return [
            get_string('csv_col_row', 'local_monlaututoria'),
            get_string('csv_col_outcome', 'local_monlaututoria'),
            get_string('assignment_col_student', 'local_monlaututoria'),
            get_string('assignment_col_tutor', 'local_monlaututoria'),
            get_string('assignment_col_academicyear', 'local_monlaututoria'),
            get_string('assignment_col_cohort', 'local_monlaututoria'),
            get_string('csv_col_messages', 'local_monlaututoria'),
        ];
    }

    /**
     * @param csv_import_apply_result $result
     * @return array<int, array<int, string|int>> one indexed array per reportable row,
     *                                              values aligned with columns()
     */
    public function rows(csv_import_apply_result $result): array {
        $rows = [];
        foreach ($result->rows as $row) {
            if (!in_array($row->outcome, self::REPORTABLE_OUTCOMES, true)) {
                continue;
            }

            $rows[] = [
                $row->rownumber,
                get_string('csv_apply_outcome_' . $row->outcome, 'local_monlaututoria'),
                self::neutralize($row->values['student'] ?? ''),
                self::neutralize($row->values['tutor'] ?? ''),
                self::neutralize($row->values['academicyear'] ?? ''),
                self::neutralize($row->values['cohort'] ?? ''),
                $row->errormessagecode !== null ? get_string($row->errormessagecode, 'local_monlaututoria') : '',
            ];
        }

        return $rows;
    }

    /**
     * Sends the CSV as a file download and terminates the request, using
     * Moodle's own dataformat export API rather than hand-rolled headers.
     *
     * @param csv_import_apply_result $result
     * @return void
     */
    public function download(csv_import_apply_result $result): void {
        \core\dataformat::download_data(
            'csv_import_errors_' . $result->operationuuid,
            'csv',
            $this->columns(),
            $this->rows($result)
        );
    }

    /**
     * @param string $value
     * @return string
     */
    public static function neutralize(string $value): string {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
