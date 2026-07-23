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
 * Immutable per-row result of resolving one csv_import_row against the
 * database (phase 3D.2): identifies the student/tutor/academic year/cohort
 * referenced by the row's raw values, and classifies the row's status.
 * Computed on demand, never persisted — same reasoning as
 * cohort_assignment_item (phase 3C.1): a persisted per-student list can only
 * go stale, so previews are always recomputed from the source (here, the
 * uploaded file's content, re-read from the draft area).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_preview_row {

    /**
     * @param int $rownumber
     * @param array<string, string> $values raw values from csv_import_row
     * @param string $status one of csv_import_row_status::values()
     * @param string[] $messagecodes csv_import_error_code and/or csv_import_message_code values
     * @param int|null $studentid resolved, when found
     * @param int|null $tutorid resolved, when found
     * @param int|null $academicyearid resolved, when found
     * @param int|null $cohortid resolved, when the optional column was given and matched
     * @param string $assignmenttype resolved (defaults applied)
     * @param bool $isprimary resolved (defaults applied)
     */
    public function __construct(
        public readonly int $rownumber,
        public readonly array $values,
        public readonly string $status,
        public readonly array $messagecodes,
        public readonly ?int $studentid,
        public readonly ?int $tutorid,
        public readonly ?int $academicyearid,
        public readonly ?int $cohortid,
        public readonly string $assignmenttype,
        public readonly bool $isprimary
    ) {
    }
}
