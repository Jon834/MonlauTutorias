<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\service;

use local_monlaututoria\domain\agreement_status;
use local_monlaututoria\domain\followup_status;
use local_monlaututoria\domain\notification_frequency;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;

final class notification_reminder_service {
    public const UPCOMING_WINDOW_SECONDS = 2 * DAYSECS;

    public function queue_reminders(?int $now = null): void {
        global $DB;

        $now = $now ?? time();
        $midnight = usergetmidnight($now);
        $bucketkey = date('Ymd', $midnight);
        $dispatch = new notification_dispatch_service();
        $entryrepository = new entry_repository();
        $assignmentrepository = new assignment_repository();
        $agreementrepository = new agreement_repository();
        $followuprepository = new followup_repository();

        foreach ($agreementrepository->search([], 0, 0, 'duedate', 'ASC') as $agreement) {
            if (!in_array($agreement->status, agreement_status::open_values(), true)) {
                continue;
            }
            foreach ($this->resolve_agreement_recipients($agreement, $entryrepository, $assignmentrepository, $now) as $recipientid) {
                if ($agreement->duedate < $midnight) {
                    $dispatch->queue_agreement_overdue($recipientid, (int) $agreement->id, $bucketkey);
                } else if ($agreement->duedate <= $now + self::UPCOMING_WINDOW_SECONDS) {
                    $dispatch->queue_agreement_due($recipientid, (int) $agreement->id, $bucketkey);
                }
            }
        }

        foreach ($followuprepository->search([], 0, 0, 'duedate', 'ASC') as $followup) {
            if (!in_array($followup->status, followup_status::open_values(), true)) {
                continue;
            }
            $entry = $entryrepository->get((int) $followup->entryid);
            foreach ($this->resolve_current_primary_tutors((int) $entry->studentid, (int) $entry->academicyearid, $assignmentrepository, $now) as $recipientid) {
                if ($followup->duedate < $midnight) {
                    $dispatch->queue_followup_overdue($recipientid, (int) $followup->id, $bucketkey);
                } else if ($followup->duedate <= $now + self::UPCOMING_WINDOW_SECONDS) {
                    $dispatch->queue_followup_due($recipientid, (int) $followup->id, $bucketkey);
                }
            }
        }

        $year = (new academic_year_repository())->get_active();
        if ($year === null) {
            return;
        }

        $assignments = $DB->get_records_select(
            'local_tut_assignment',
            'academicyearid = :year AND status = :status AND isprimary = 1 AND timestart <= :now1 AND (timeend IS NULL OR timeend > :now2)',
            ['year' => (int) $year->id, 'status' => 'active', 'now1' => $now, 'now2' => $now],
            '',
            'id, tutorid'
        );
        $tutorids = array_values(array_unique(array_map(static fn($row): int => (int) $row->tutorid, $assignments)));
        $preferences = new notification_preference_service();
        foreach ($tutorids as $tutorid) {
            $frequency = $preferences->get_digest_frequency($tutorid);
            if ($frequency === notification_frequency::DAILY) {
                $dispatch->queue_digest($tutorid, notification_frequency::DAILY, $bucketkey);
            } else if ($frequency === notification_frequency::WEEKLY && date('N', $midnight) === '1') {
                $dispatch->queue_digest($tutorid, notification_frequency::WEEKLY, date('oW', $midnight));
            }
        }
    }

    private function resolve_agreement_recipients(\stdClass $agreement, entry_repository $entryrepository, assignment_repository $assignmentrepository, int $now): array {
        if (!empty($agreement->responsibleuserid)) {
            return [(int) $agreement->responsibleuserid];
        }
        $entry = $entryrepository->get((int) $agreement->entryid);
        return $this->resolve_current_primary_tutors((int) $entry->studentid, (int) $entry->academicyearid, $assignmentrepository, $now);
    }

    private function resolve_current_primary_tutors(int $studentid, int $academicyearid, assignment_repository $assignmentrepository, int $now): array {
        $tutorids = [];
        foreach ($assignmentrepository->find_current($studentid, $academicyearid, $now) as $assignment) {
            if ((int) $assignment->isprimary === 1) {
                $tutorids[(int) $assignment->tutorid] = (int) $assignment->tutorid;
            }
        }
        return array_values($tutorids);
    }
}
