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
use local_monlaututoria\repository\bulk_operation_repository;
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\assignment_source;
use local_monlaututoria\domain\assignment_conflict_code;
use local_monlaututoria\domain\bulk_operation_status;
use local_monlaututoria\domain\cohort_sync_mode;
use local_monlaututoria\domain\cohort_assignment_action;
use local_monlaututoria\domain\cohort_assignment_command;
use local_monlaututoria\domain\cohort_assignment_item;
use local_monlaututoria\domain\cohort_assignment_summary;
use local_monlaututoria\domain\cohort_assignment_preview;
use local_monlaututoria\event\cohort_assignment_previewed;

/**
 * Classifies the members of a Moodle cohort against a proposed primary tutor
 * (and optional co-tutor) for an academic year, for phase 3C's cohort-based
 * bulk assignment flow. Read-only: never writes to local_tut_assignment.
 *
 * Deliberately persists only an operation envelope (local_tut_bulkoperation:
 * identity, parameters, aggregate summary) and NEVER the per-student
 * classification itself. Two reasons:
 *  - it is exactly the kind of data phase 3C's own privacy requirements say
 *    should not be kept indefinitely once a preview is abandoned;
 *  - a persisted item list can only go stale between preview and
 *    confirmation, which is precisely the "caducidad" problem phase 3C asks
 *    for. Recomputing on demand and diffing the resulting summary against
 *    the one stored at preview time (see has_changed_since_preview())
 *    sidesteps that: there is nothing to go stale because nothing but
 *    aggregate counts is kept.
 *
 * Reuses assignment_repository/cohort_membership_repository from phase 3B.5
 * unchanged, and assignment_service's validators (made public for this) to
 * avoid duplicating the tutor/cohort/academic-year business rules.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_preview_service {

    /** @var assignment_repository */
    private $assignmentrepository;

    /** @var cohort_membership_repository */
    private $cohortrepository;

    /** @var academic_year_repository */
    private $academicyearrepository;

    /** @var bulk_operation_repository */
    private $bulkoperationrepository;

    /** @var assignment_service */
    private $assignmentservice;

    public function __construct(
        ?assignment_repository $assignmentrepository = null,
        ?cohort_membership_repository $cohortrepository = null,
        ?academic_year_repository $academicyearrepository = null,
        ?bulk_operation_repository $bulkoperationrepository = null,
        ?assignment_service $assignmentservice = null
    ) {
        $this->assignmentrepository = $assignmentrepository ?? new assignment_repository();
        $this->cohortrepository = $cohortrepository ?? new cohort_membership_repository();
        $this->academicyearrepository = $academicyearrepository ?? new academic_year_repository();
        $this->bulkoperationrepository = $bulkoperationrepository ?? new bulk_operation_repository();
        $this->assignmentservice = $assignmentservice ?? new assignment_service($this->assignmentrepository, $this->academicyearrepository);
    }

    /**
     * Validates the command, classifies the cohort's current membership, and
     * persists only the operation envelope (never the per-student items).
     *
     * @param cohort_assignment_command $command
     * @param int $userid
     * @return cohort_assignment_preview
     */
    public function preview(cohort_assignment_command $command, int $userid): cohort_assignment_preview {
        $this->validate_command($command);

        [$summary, $items] = $this->classify($command);

        $operationuuid = bulk_operation_repository::generate_uuid();
        $operationid = $this->bulkoperationrepository->create((object) [
            'operationuuid'  => $operationuuid,
            'cohortid'       => $command->cohortid,
            'academicyearid' => $command->academicyearid,
            'primarytutorid' => $command->primarytutorid,
            'cotutorid'      => $command->cotutorid,
            'mode'           => $command->mode,
            'status'         => bulk_operation_status::PREVIEWED,
            'parametersjson' => json_encode([
                'timestart'           => $command->timestart,
                'timeend'             => $command->timeend,
                'includesuspended'    => $command->includesuspended,
                'allowsuspendedtutor' => $command->allowsuspendedtutor,
                'canoverridelock'     => $command->canoverridelock,
            ]),
            'summaryjson'    => json_encode($summary->to_array()),
            'createdby'      => $userid,
        ]);

        cohort_assignment_previewed::create_from_operation(
            $operationid,
            $userid,
            $command->cohortid,
            $command->academicyearid,
            $command->mode,
            $summary->totalmembers
        )->trigger();

        return new cohort_assignment_preview($operationuuid, $operationid, $summary, $items);
    }

    /**
     * @param string $operationuuid
     * @param int $ttlseconds defaults to 30 minutes, per the phase 3C requirements
     * @return bool
     */
    public function is_expired(string $operationuuid, int $ttlseconds = 1800): bool {
        return $this->bulkoperationrepository->is_expired($operationuuid, $ttlseconds);
    }

    /**
     * Recomputes the classification from the operation's stored parameters
     * and compares the resulting summary against the one stored at preview
     * time. True means the cohort membership or the students' assignments
     * changed since — a new preview should be generated instead of trusting
     * this one.
     *
     * @param string $operationuuid
     * @return bool
     */
    public function has_changed_since_preview(string $operationuuid): bool {
        $operation = $this->bulkoperationrepository->get_by_uuid($operationuuid);
        $parameters = json_decode($operation->parametersjson ?? '{}', true) ?: [];

        $command = new cohort_assignment_command(
            (int) $operation->cohortid,
            (int) $operation->academicyearid,
            (int) $operation->primarytutorid,
            $operation->mode,
            $operation->cotutorid !== null ? (int) $operation->cotutorid : null,
            $parameters['timestart'] ?? null,
            $parameters['timeend'] ?? null,
            !empty($parameters['includesuspended']),
            !empty($parameters['allowsuspendedtutor']),
            !empty($parameters['canoverridelock'])
        );

        [$freshsummary] = $this->classify($command);
        $storedsummary = cohort_assignment_summary::from_array(json_decode($operation->summaryjson ?? '{}', true) ?: []);

        return $freshsummary->differs_from($storedsummary);
    }

    /**
     * @param cohort_assignment_command $command
     */
    private function validate_command(cohort_assignment_command $command): void {
        if (!in_array($command->mode, cohort_sync_mode::values(), true)) {
            throw new \moodle_exception('error_cohort_mode_invalid', 'local_monlaututoria');
        }

        $this->assignmentservice->validate_cohort($command->cohortid);
        $this->assignmentservice->validate_academic_year($command->academicyearid, $command->canoverridelock);

        $tutor = $this->assignmentservice->validate_user($command->primarytutorid, 'error_assignment_invalid_tutor');
        $this->assignmentservice->validate_not_suspended($tutor, $command->allowsuspendedtutor, 'error_assignment_tutor_suspended');

        if ($command->cotutorid !== null) {
            if ($command->cotutorid === $command->primarytutorid) {
                throw new \moodle_exception('error_cohort_same_tutor_cotutor', 'local_monlaututoria');
            }
            $cotutor = $this->assignmentservice->validate_user($command->cotutorid, 'error_assignment_invalid_tutor');
            $this->assignmentservice->validate_not_suspended($cotutor, $command->allowsuspendedtutor, 'error_assignment_tutor_suspended');
        }
    }

    /**
     * @param cohort_assignment_command $command
     * @return array{0: cohort_assignment_summary, 1: cohort_assignment_item[]}
     */
    private function classify(cohort_assignment_command $command): array {
        global $DB;

        $now = time();
        $members = $this->cohortrepository->get_members([$command->cohortid]);
        $studentids = array_map('intval', array_keys($members));

        $items = [];

        if (!empty($members)) {
            $primaryrows = $this->assignmentrepository->find_primary_rows_for_students($studentids, $command->academicyearid);

            $rowsbystudent = [];
            foreach ($primaryrows as $row) {
                $rowsbystudent[(int) $row->studentid][] = $row;
            }

            $cotutorrowsbystudent = [];
            if ($command->cotutorid !== null) {
                $cotutorrows = $this->assignmentrepository->get_cotutors_for_students($studentids, $command->academicyearid);
                foreach ($cotutorrows as $row) {
                    $cotutorrowsbystudent[(int) $row->studentid][] = $row;
                }
            }

            foreach ($members as $member) {
                $items[] = $this->classify_student(
                    (int) $member->id,
                    !empty($member->suspended),
                    !empty($member->deleted),
                    $rowsbystudent[(int) $member->id] ?? [],
                    $cotutorrowsbystudent[(int) $member->id] ?? [],
                    $command,
                    $now
                );
            }
        }

        // Runs regardless of current membership: an emptied-out cohort is
        // exactly when detecting departed students matters most.
        if ($command->mode === cohort_sync_mode::ADD_AND_CLOSE_MISSING) {
            $memberids = array_flip($studentids);
            $cohortsourcedrows = $this->assignmentrepository->find_by_cohort($command->cohortid);

            $missingtutorids = [];
            $missingrows = [];
            foreach ($cohortsourcedrows as $row) {
                if ((int) $row->academicyearid !== $command->academicyearid) {
                    continue;
                }
                if ($row->assignmenttype !== 'primary' || $row->status !== assignment_status::ACTIVE) {
                    continue;
                }
                if ($row->source !== assignment_source::COHORT) {
                    continue;
                }
                if (isset($memberids[(int) $row->studentid])) {
                    continue;
                }
                $missingrows[] = $row;
                $missingtutorids[(int) $row->tutorid] = true;
            }

            foreach ($missingrows as $row) {
                $items[] = new cohort_assignment_item(
                    (int) $row->studentid,
                    cohort_assignment_action::CLOSE_MISSING,
                    null,
                    false,
                    false,
                    (int) $row->tutorid,
                    (int) $row->id,
                    []
                );
            }
        }

        return [$this->summarise($items), $items];
    }

    /**
     * @param int $studentid
     * @param bool $suspended
     * @param bool $deleted
     * @param \stdClass[] $rows this student's primary-type rows, any status
     * @param \stdClass[] $cotutorrows this student's active co_tutor rows
     * @param cohort_assignment_command $command
     * @param int $now
     * @return cohort_assignment_item
     */
    private function classify_student(
        int $studentid,
        bool $suspended,
        bool $deleted,
        array $rows,
        array $cotutorrows,
        cohort_assignment_command $command,
        int $now
    ): cohort_assignment_item {
        $active = [];
        foreach ($rows as $row) {
            if ($this->is_vigente($row, $now)) {
                $active[] = $row;
            }
        }

        $conflictcodes = [];
        if (count($active) > 1) {
            $conflictcodes[] = assignment_conflict_code::MULTIPLE_ACTIVE_PRIMARY;
        }
        $current = $active[0] ?? null;

        if ($deleted || $studentid === $command->primarytutorid) {
            $action = cohort_assignment_action::SKIP_INVALID;
        } else if ($suspended && !$command->includesuspended) {
            $action = cohort_assignment_action::SKIP_SUSPENDED;
        } else if (count($active) > 1) {
            $action = cohort_assignment_action::CONFLICT_PRIMARY;
        } else if ($current === null) {
            $action = cohort_assignment_action::CREATE_PRIMARY;
        } else if ((int) $current->tutorid === $command->primarytutorid) {
            $action = cohort_assignment_action::NO_CHANGE;
        } else if ($command->mode === cohort_sync_mode::REPLACE_PRIMARY) {
            $action = cohort_assignment_action::REASSIGN_PRIMARY;
        } else {
            $action = cohort_assignment_action::SKIP_EXISTING;
        }

        $cotutoraction = null;
        if ($command->cotutorid !== null) {
            if ($deleted || $studentid === $command->cotutorid) {
                $cotutoraction = cohort_assignment_action::SKIP_INVALID;
            } else if ($suspended && !$command->includesuspended) {
                $cotutoraction = cohort_assignment_action::SKIP_SUSPENDED;
            } else {
                $hascotutor = false;
                foreach ($cotutorrows as $cotutorrow) {
                    if ((int) $cotutorrow->tutorid === $command->cotutorid) {
                        $hascotutor = true;
                        break;
                    }
                }
                $cotutoraction = $hascotutor ? cohort_assignment_action::NO_CHANGE : cohort_assignment_action::CREATE_COTUTOR;
            }
        }

        return new cohort_assignment_item(
            $studentid,
            $action,
            $cotutoraction,
            $suspended,
            $deleted,
            $current !== null ? (int) $current->tutorid : null,
            $current !== null ? (int) $current->id : null,
            $conflictcodes
        );
    }

    /**
     * @param \stdClass $row
     * @param int $now
     * @return bool
     */
    private function is_vigente(\stdClass $row, int $now): bool {
        if ($row->status !== assignment_status::ACTIVE) {
            return false;
        }
        $timeend = $row->timeend !== null ? (int) $row->timeend : null;

        return (int) $row->timestart <= $now && ($timeend === null || $timeend > $now);
    }

    /**
     * @param cohort_assignment_item[] $items
     * @return cohort_assignment_summary
     */
    private function summarise(array $items): cohort_assignment_summary {
        $totalmembers = 0;
        $suspendedcount = 0;
        $deletedcount = 0;
        $tocreatecount = 0;
        $toreassigncount = 0;
        $tocreatecotutorcount = 0;
        $toclosecount = 0;
        $nochangecount = 0;
        $skippedcount = 0;
        $conflictcount = 0;

        foreach ($items as $item) {
            if ($item->action === cohort_assignment_action::CLOSE_MISSING) {
                $toclosecount++;
                continue;
            }

            $totalmembers++;
            if ($item->suspended) {
                $suspendedcount++;
            }
            if ($item->deleted) {
                $deletedcount++;
            }
            if ($item->cotutoraction === cohort_assignment_action::CREATE_COTUTOR) {
                $tocreatecotutorcount++;
            }

            switch ($item->action) {
                case cohort_assignment_action::CREATE_PRIMARY:
                    $tocreatecount++;
                    break;
                case cohort_assignment_action::REASSIGN_PRIMARY:
                    $toreassigncount++;
                    break;
                case cohort_assignment_action::NO_CHANGE:
                    $nochangecount++;
                    break;
                case cohort_assignment_action::CONFLICT_PRIMARY:
                    $conflictcount++;
                    break;
                case cohort_assignment_action::SKIP_EXISTING:
                case cohort_assignment_action::SKIP_SUSPENDED:
                case cohort_assignment_action::SKIP_INVALID:
                    $skippedcount++;
                    break;
            }
        }

        return new cohort_assignment_summary(
            $totalmembers,
            $suspendedcount,
            $deletedcount,
            $tocreatecount,
            $toreassigncount,
            $tocreatecotutorcount,
            $toclosecount,
            $nochangecount,
            $skippedcount,
            $conflictcount
        );
    }

}
