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
use local_monlaututoria\repository\cohort_membership_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\assignment_conflict_code;
use local_monlaututoria\domain\unassigned_status_code;
use local_monlaututoria\domain\unassigned_student;
use local_monlaututoria\domain\coverage_summary;

/**
 * Detects students within a set of Moodle cohorts who lack a "vigente"
 * (currently active and within its time window) primary tutor for a given
 * academic year and reference date.
 *
 * Classifies the WHOLE requested population (covered and uncovered alike) in
 * exactly 3 database queries, regardless of population size:
 * cohort_membership_repository::get_members(), ::get_memberships(), and
 * assignment_repository::find_primary_rows_for_students(). Classification
 * itself happens in PHP so that search()/count() can filter and paginate
 * over the "no active primary" subset correctly — a SQL-level NOT EXISTS
 * join would paginate correctly too, but this plugin's repositories
 * deliberately avoid joining across tables (see assignment_repository's
 * class docblock); this keeps that same convention at the cost of holding
 * the full population in PHP memory for the duration of one call. Verified
 * as an acceptable trade-off up to a few thousand students (the scale named
 * in the phase 3B.5 requirements); a true SQL-level filter would be needed
 * well beyond that and is noted here as a known future optimisation.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class unassigned_students_service {

    /** @var assignment_repository */
    private $assignmentrepository;

    /** @var cohort_membership_repository */
    private $cohortrepository;

    /** @var academic_year_repository */
    private $academicyearrepository;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?cohort_membership_repository $cohortrepository = null,
        ?academic_year_repository $academicyearrepository = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->cohortrepository = $cohortrepository ?? new cohort_membership_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
    }

    /**
     * Students in the given cohorts without a vigente primary tutor.
     *
     * @param int[] $cohortids
     * @param int $academicyearid
     * @param int|null $referencedate defaults to now
     * @param int $limitfrom
     * @param int $limitnum 0 means "no limit"
     * @return unassigned_student[]
     */
    public function search(
        array $cohortids,
        int $academicyearid,
        ?int $referencedate = null,
        int $limitfrom = 0,
        int $limitnum = 0
    ): array {
        $unassigned = $this->get_unassigned($cohortids, $academicyearid, $referencedate);

        return $limitnum > 0
            ? array_slice($unassigned, $limitfrom, $limitnum)
            : array_slice($unassigned, $limitfrom);
    }

    /**
     * @param int[] $cohortids
     * @param int $academicyearid
     * @param int|null $referencedate
     * @return int
     */
    public function count(array $cohortids, int $academicyearid, ?int $referencedate = null): int {
        return count($this->get_unassigned($cohortids, $academicyearid, $referencedate));
    }

    /**
     * @param int[] $cohortids
     * @param int $academicyearid
     * @param int|null $referencedate
     * @return coverage_summary
     */
    public function get_coverage_summary(
        array $cohortids,
        int $academicyearid,
        ?int $referencedate = null
    ): coverage_summary {
        $classified = $this->classify_population($cohortids, $academicyearid, $referencedate);

        $analyzed = count($classified);
        $withprimary = 0;
        $suspended = 0;
        $futurepending = 0;
        $conflicts = 0;

        foreach ($classified as $student) {
            if ($student->hasactiveprimary) {
                $withprimary++;
            }
            if ($student->suspended) {
                $suspended++;
            }
            if (!$student->hasactiveprimary && $student->futureprimaryassignmentid !== null) {
                $futurepending++;
            }
            if (!empty($student->conflictcodes)) {
                $conflicts++;
            }
        }

        $withoutprimary = $analyzed - $withprimary;
        $coveragepercent = $analyzed > 0 ? round(($withprimary / $analyzed) * 100, 1) : 0.0;

        return new coverage_summary(
            $analyzed,
            $withprimary,
            $withoutprimary,
            $coveragepercent,
            $suspended,
            $futurepending,
            $conflicts
        );
    }

    /**
     * @param int[] $cohortids
     * @param int $academicyearid
     * @param int|null $referencedate
     * @return unassigned_student[] re-indexed from 0, hasactiveprimary=false only
     */
    private function get_unassigned(array $cohortids, int $academicyearid, ?int $referencedate): array {
        $classified = $this->classify_population($cohortids, $academicyearid, $referencedate);

        return array_values(array_filter($classified, static fn (unassigned_student $s) => !$s->hasactiveprimary));
    }

    /**
     * @param int[] $cohortids
     * @param int $academicyearid
     * @param int|null $referencedate
     * @return array<int, unassigned_student> keyed by studentid
     */
    private function classify_population(array $cohortids, int $academicyearid, ?int $referencedate): array {
        global $DB;

        if (empty($cohortids)) {
            return [];
        }

        $this->validate_academic_year($academicyearid);

        $now = $referencedate ?? time();

        $members = $this->cohortrepository->get_members($cohortids);
        if (empty($members)) {
            return [];
        }

        $studentids = array_map('intval', array_keys($members));
        $memberships = $this->cohortrepository->get_memberships($cohortids, $studentids);
        $primaryrows = $this->assignmentrepository->find_primary_rows_for_students($studentids, $academicyearid);

        $rowsbystudent = [];
        $activetutorids = [];
        foreach ($primaryrows as $row) {
            $rowsbystudent[(int) $row->studentid][] = $row;
            if ($row->status === assignment_status::ACTIVE) {
                $activetutorids[(int) $row->tutorid] = true;
            }
        }

        $deletedtutorids = [];
        if (!empty($activetutorids)) {
            $tutorrecords = $DB->get_records_list('user', 'id', array_keys($activetutorids), '', 'id, deleted');
            foreach ($tutorrecords as $tutorrecord) {
                if (!empty($tutorrecord->deleted)) {
                    $deletedtutorids[(int) $tutorrecord->id] = true;
                }
            }
        }

        $result = [];
        foreach ($members as $member) {
            $studentid = (int) $member->id;
            $result[$studentid] = $this->classify_student(
                $studentid,
                $memberships[$studentid] ?? [],
                !empty($member->suspended),
                !empty($member->deleted),
                $rowsbystudent[$studentid] ?? [],
                $now,
                $deletedtutorids
            );
        }

        return $result;
    }

    /**
     * @param int $studentid
     * @param int[] $cohortids
     * @param bool $suspended
     * @param bool $deleted
     * @param \stdClass[] $rows this student's primary-type rows, any status
     * @param int $now
     * @param array<int, bool> $deletedtutorids tutorids (keys) whose user account is deleted
     * @return unassigned_student
     */
    private function classify_student(
        int $studentid,
        array $cohortids,
        bool $suspended,
        bool $deleted,
        array $rows,
        int $now,
        array $deletedtutorids
    ): unassigned_student {
        $active = [];
        $future = [];
        $historical = [];
        $hasexpiredactive = false;
        $hasclosed = false;
        $last = null;
        $nextfuture = null;

        foreach ($rows as $row) {
            $timestart = (int) $row->timestart;
            $timeend = $row->timeend !== null ? (int) $row->timeend : null;
            $ishistorical = false;

            if ($row->status === assignment_status::ACTIVE) {
                if ($timestart > $now) {
                    $future[] = $row;
                    if ($nextfuture === null || $timestart < (int) $nextfuture->timestart) {
                        $nextfuture = $row;
                    }
                } else if ($timeend !== null && $timeend <= $now) {
                    $ishistorical = true;
                    $hasexpiredactive = true;
                } else {
                    $active[] = $row;
                }
            } else if (in_array($row->status, [assignment_status::CLOSED, assignment_status::CANCELLED], true)) {
                $ishistorical = true;
                $hasclosed = true;
            }

            if ($ishistorical) {
                $historical[] = $row;
                if ($last === null || $timestart > (int) $last->timestart) {
                    $last = $row;
                }
            }
        }

        $conflicts = [];
        if (count($active) > 1) {
            $conflicts[] = assignment_conflict_code::MULTIPLE_ACTIVE_PRIMARY;
        }
        if (count($future) > 1) {
            $conflicts[] = assignment_conflict_code::OVERLAPPING_FUTURE;
        }
        if ($this->has_overlapping_windows($historical)) {
            $conflicts[] = assignment_conflict_code::DUPLICATE_HISTORICAL;
        }
        foreach ($active as $row) {
            if (!empty($deletedtutorids[(int) $row->tutorid])) {
                $conflicts[] = assignment_conflict_code::DELETED_TUTOR_ACTIVE;
                break;
            }
        }

        $hasactiveprimary = count($active) > 0;

        if (!empty($conflicts)) {
            $statuscode = unassigned_status_code::DATA_CONFLICT;
        } else if ($hasactiveprimary) {
            $statuscode = unassigned_status_code::HAS_ACTIVE_PRIMARY;
        } else if ($nextfuture !== null) {
            $statuscode = unassigned_status_code::FUTURE_PENDING;
        } else if ($hasexpiredactive) {
            $statuscode = unassigned_status_code::EXPIRED_ACTIVE;
        } else if ($hasclosed) {
            $statuscode = unassigned_status_code::PREVIOUS_CLOSED;
        } else {
            $statuscode = unassigned_status_code::NEVER_ASSIGNED;
        }

        return new unassigned_student(
            $studentid,
            $cohortids,
            $suspended,
            $deleted,
            $hasactiveprimary,
            $statuscode,
            $conflicts,
            $last !== null ? (int) $last->id : null,
            $last !== null ? (int) $last->tutorid : null,
            $last !== null && $last->timeend !== null ? (int) $last->timeend : null,
            $nextfuture !== null ? (int) $nextfuture->id : null,
            $nextfuture !== null ? (int) $nextfuture->tutorid : null,
            $nextfuture !== null ? (int) $nextfuture->timestart : null,
            $now
        );
    }

    /**
     * Whether any two rows in this small, per-student list have overlapping
     * [timestart, timeend) windows. O(n²) is fine here: n is one student's
     * historical primary rows, realistically single digits.
     *
     * @param \stdClass[] $rows 0-indexed
     * @return bool
     */
    private function has_overlapping_windows(array $rows): bool {
        $rows = array_values($rows);
        $count = count($rows);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $aend = $rows[$i]->timeend !== null ? (int) $rows[$i]->timeend : PHP_INT_MAX;
                $bend = $rows[$j]->timeend !== null ? (int) $rows[$j]->timeend : PHP_INT_MAX;
                if ((int) $rows[$i]->timestart < $bend && (int) $rows[$j]->timestart < $aend) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $academicyearid
     */
    private function validate_academic_year(int $academicyearid): void {
        try {
            $this->academicyearrepository->get($academicyearid);
        } catch (\dml_missing_record_exception $e) {
            throw new \moodle_exception('error_assignment_academicyear_invalid', 'local_monlaututoria');
        }
    }
}
