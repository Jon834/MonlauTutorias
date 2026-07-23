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

use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\csv_import_row;
use local_monlaututoria\domain\csv_import_row_status;
use local_monlaututoria\domain\csv_import_message_code;
use local_monlaututoria\domain\csv_import_preview_row;
use local_monlaututoria\domain\csv_import_preview_summary;
use local_monlaututoria\domain\csv_import_preview;
use local_monlaututoria\event\csv_import_previewed;

/**
 * Resolves a parsed CSV assignment import (from csv_import_parser_service,
 * phase 3D.1) against the current database state: finds the student/tutor by
 * email, username or idnumber, the academic year by shortname, and the
 * optional cohort by id or idnumber, then classifies each row's status
 * (valid/warning/conflict/error/excluded).
 *
 * Reuses assignment_service's public validators (student/tutor
 * valid+not-suspended, academic year not locked) and
 * assignment_repository's existing duplicate-detection queries, rather than
 * duplicating those rules — same principle already applied by
 * cohort_assignment_preview_service (phase 3C.1).
 *
 * Never persists per-row data: only an operation envelope (identity,
 * parameters, aggregate summary) goes into local_tut_bulkoperation, shared
 * with the cohort-based flow. The uploaded file itself lives in the caller's
 * Moodle draft file area, not in this plugin's storage — see
 * assignments/import.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_preview_service {

    /** @var assignment_repository */
    private $assignmentrepository;

    /** @var academic_year_repository */
    private $academicyearrepository;

    /** @var bulk_operation_repository */
    private $bulkoperationrepository;

    /** @var assignment_service */
    private $assignmentservice;

    /** @var csv_import_parser_service */
    private $parserservice;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?academic_year_repository $academicyearrepository = null,
        ?bulk_operation_repository $bulkoperationrepository = null,
        ?assignment_service $assignmentservice = null,
        ?csv_import_parser_service $parserservice = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
        $this->bulkoperationrepository = $bulkoperationrepository ?? new bulk_operation_repository();
        $this->assignmentservice = $assignmentservice
            ?? new assignment_service($this->assignmentrepository, $this->academicyearrepository);
        $this->parserservice = $parserservice ?? new csv_import_parser_service();
    }

    /**
     * @param string $content raw CSV file content
     * @param string $delimiter
     * @param string $encoding
     * @param int $userid
     * @param int[] $excludedrownumbers row numbers the administrator has manually excluded
     * @return csv_import_preview
     */
    public function preview(
        string $content,
        string $delimiter,
        string $encoding,
        int $userid,
        array $excludedrownumbers = []
    ): csv_import_preview {
        $parsed = $this->parserservice->parse($content, $delimiter, $encoding);
        if (!$parsed->is_usable()) {
            throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
        }

        $excludedset = array_flip($excludedrownumbers);
        $rows = [];
        foreach ($parsed->rows as $row) {
            $rows[] = $this->resolve_row($row, isset($excludedset[$row->rownumber]));
        }

        $summary = $this->summarise($rows);

        $operationid = $this->bulkoperationrepository->create((object) [
            'operationuuid'  => bulk_operation_repository::generate_uuid(),
            'operationtype'  => 'csv_import',
            'status'         => bulk_operation_status::PREVIEWED,
            'parametersjson' => json_encode([
                'delimiter'          => $delimiter,
                'encoding'           => $encoding,
                'excludedrownumbers' => array_values($excludedrownumbers),
            ]),
            'summaryjson'    => json_encode($summary->to_array()),
            'createdby'      => $userid,
        ]);
        $operation = $this->bulkoperationrepository->get($operationid);

        csv_import_previewed::create_from_operation(
            $operationid,
            $userid,
            $summary->totalrows,
            $summary->validcount,
            $summary->errorcount
        )->trigger();

        return new csv_import_preview($operation->operationuuid, $operationid, $summary, $rows);
    }

    /**
     * @param string $operationuuid
     * @param int $ttlseconds defaults to 30 minutes, same as the cohort-based flow
     * @return bool
     */
    public function is_expired(string $operationuuid, int $ttlseconds = 1800): bool {
        return $this->bulkoperationrepository->is_expired($operationuuid, $ttlseconds);
    }

    /**
     * @param csv_import_row $row
     * @param bool $excluded
     * @return csv_import_preview_row
     */
    private function resolve_row(csv_import_row $row, bool $excluded): csv_import_preview_row {
        if ($excluded) {
            return new csv_import_preview_row(
                $row->rownumber,
                $row->values,
                csv_import_row_status::EXCLUDED,
                [csv_import_message_code::ROW_EXCLUDED],
                null,
                null,
                null,
                null,
                $row->values['assignmenttype'] ?? assignment_type::PRIMARY,
                false
            );
        }

        if (!$row->is_valid()) {
            return new csv_import_preview_row(
                $row->rownumber,
                $row->values,
                csv_import_row_status::ERROR,
                $row->errors,
                null,
                null,
                null,
                null,
                $row->values['assignmenttype'] ?? assignment_type::PRIMARY,
                false
            );
        }

        $assignmenttype = $row->values['assignmenttype'] !== '' ? $row->values['assignmenttype'] : assignment_type::PRIMARY;
        $isprimary = isset($row->values['isprimary']) && $row->values['isprimary'] !== ''
            ? $row->values['isprimary'] === '1'
            : $assignmenttype === assignment_type::PRIMARY;

        $student = $this->find_user($row->values['student']);
        if ($student === null) {
            return $this->error_row($row, [csv_import_message_code::STUDENT_NOT_FOUND], $assignmenttype, $isprimary);
        }

        $tutor = $this->find_user($row->values['tutor']);
        if ($tutor === null) {
            return $this->error_row($row, [csv_import_message_code::TUTOR_NOT_FOUND], $assignmenttype, $isprimary);
        }

        $academicyear = $this->academicyearrepository->find_by_shortname($row->values['academicyear']);
        if ($academicyear === null) {
            return $this->error_row($row, [csv_import_message_code::ACADEMICYEAR_NOT_FOUND], $assignmenttype, $isprimary);
        }

        $messages = [];
        if ((int) $student->id === (int) $tutor->id) {
            $messages[] = csv_import_message_code::STUDENT_SELF_TUTOR;
        }
        if (!empty($student->suspended)) {
            $messages[] = csv_import_message_code::STUDENT_SUSPENDED;
        }
        if (!empty($tutor->suspended)) {
            $messages[] = csv_import_message_code::TUTOR_SUSPENDED;
        }
        if (!empty($academicyear->locked)) {
            $messages[] = csv_import_message_code::ACADEMICYEAR_LOCKED;
        }

        if (!empty($messages)) {
            return new csv_import_preview_row(
                $row->rownumber,
                $row->values,
                csv_import_row_status::ERROR,
                $messages,
                (int) $student->id,
                (int) $tutor->id,
                (int) $academicyear->id,
                null,
                $assignmenttype,
                $isprimary
            );
        }

        $cohortid = null;
        if (!empty($row->values['cohort'])) {
            $cohort = $this->find_cohort($row->values['cohort']);
            if ($cohort === null) {
                $messages[] = csv_import_message_code::COHORT_NOT_FOUND;
            } else {
                $cohortid = (int) $cohort->id;
            }
        }

        $status = csv_import_row_status::VALID;
        if ($this->assignmentrepository->has_active_duplicate(
            (int) $student->id,
            (int) $tutor->id,
            (int) $academicyear->id,
            $assignmenttype
        )) {
            $messages[] = csv_import_message_code::DUPLICATE_ACTIVE;
            $status = csv_import_row_status::CONFLICT;
        } else if ($isprimary && $this->assignmentrepository->count_active_primary((int) $student->id, (int) $academicyear->id) > 0) {
            $messages[] = csv_import_message_code::PRIMARY_CONFLICT;
            $status = csv_import_row_status::CONFLICT;
        } else if (!empty($messages)) {
            $status = csv_import_row_status::WARNING;
        }

        return new csv_import_preview_row(
            $row->rownumber,
            $row->values,
            $status,
            $messages,
            (int) $student->id,
            (int) $tutor->id,
            (int) $academicyear->id,
            $cohortid,
            $assignmenttype,
            $isprimary
        );
    }

    /**
     * @param csv_import_row $row
     * @param string[] $messages
     * @param string $assignmenttype
     * @param bool $isprimary
     * @return csv_import_preview_row
     */
    private function error_row(csv_import_row $row, array $messages, string $assignmenttype, bool $isprimary): csv_import_preview_row {
        return new csv_import_preview_row(
            $row->rownumber,
            $row->values,
            csv_import_row_status::ERROR,
            $messages,
            null,
            null,
            null,
            null,
            $assignmenttype,
            $isprimary
        );
    }

    /**
     * Resolves a student/tutor identifier by email, then username, then
     * idnumber, in that order. Never returns a deleted account. idnumber has
     * no uniqueness guarantee at the Moodle core level, so the first match
     * wins — a known limitation for CSV imports relying on idnumber.
     *
     * @param string $identifier
     * @return \stdClass|null
     */
    private function find_user(string $identifier): ?\stdClass {
        global $CFG, $DB;

        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $user = $DB->get_record('user', ['email' => $identifier, 'deleted' => 0], '*', IGNORE_MULTIPLE);
        if ($user) {
            return $user;
        }

        $user = $DB->get_record(
            'user',
            ['username' => $identifier, 'deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id],
            '*',
            IGNORE_MULTIPLE
        );
        if ($user) {
            return $user;
        }

        $user = $DB->get_record('user', ['idnumber' => $identifier, 'deleted' => 0], '*', IGNORE_MULTIPLE);

        return $user ?: null;
    }

    /**
     * Resolves a cohort by numeric id, then by idnumber.
     *
     * @param string $identifier
     * @return \stdClass|null
     */
    private function find_cohort(string $identifier): ?\stdClass {
        global $DB;

        $identifier = trim($identifier);
        if (ctype_digit($identifier)) {
            $cohort = $DB->get_record('cohort', ['id' => (int) $identifier]);
            if ($cohort) {
                return $cohort;
            }
        }

        $cohort = $DB->get_record('cohort', ['idnumber' => $identifier], '*', IGNORE_MULTIPLE);

        return $cohort ?: null;
    }

    /**
     * @param csv_import_preview_row[] $rows
     * @return csv_import_preview_summary
     */
    private function summarise(array $rows): csv_import_preview_summary {
        $counts = [
            csv_import_row_status::VALID    => 0,
            csv_import_row_status::WARNING  => 0,
            csv_import_row_status::CONFLICT => 0,
            csv_import_row_status::ERROR    => 0,
            csv_import_row_status::EXCLUDED => 0,
        ];
        foreach ($rows as $row) {
            $counts[$row->status]++;
        }

        return new csv_import_preview_summary(
            count($rows),
            $counts[csv_import_row_status::VALID],
            $counts[csv_import_row_status::WARNING],
            $counts[csv_import_row_status::CONFLICT],
            $counts[csv_import_row_status::ERROR],
            $counts[csv_import_row_status::EXCLUDED]
        );
    }
}
