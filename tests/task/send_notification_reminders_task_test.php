<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

use local_monlaututoria\domain\agreement_responsible_type;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\agreement_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\followup_repository;
use local_monlaututoria\repository\notification_repository;

final class send_notification_reminders_task_test extends \advanced_testcase {
    private function create_year(bool $active = true): int {
        $repository = new academic_year_repository();
        $id = $repository->create((object) [
            'name' => '2026-2027',
            'shortname' => 'notif-task-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        if ($active) {
            $repository->set_active_flag($id, true, get_admin()->id);
        }
        return $id;
    }

    public function test_task_queues_due_and_overdue_notifications_without_duplicates(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $yearid = $this->create_year();
        $now = strtotime('2026-10-15 08:00:00');

        (new assignment_repository())->create((object) [
            'studentid' => $student->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $yearid,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => $now - DAYSECS,
            'createdby' => get_admin()->id,
        ]);
        $entryid = (new entry_repository())->create((object) [
            'studentid' => $student->id,
            'tutorid' => $tutor->id,
            'academicyearid' => $yearid,
            'entrydate' => $now - DAYSECS,
            'createdby' => get_admin()->id,
        ]);
        (new followup_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $student->id,
            'duedate' => $now - DAYSECS,
            'createdby' => get_admin()->id,
        ]);
        (new agreement_repository())->create((object) [
            'entryid' => $entryid,
            'studentid' => $student->id,
            'description' => 'Agreement',
            'responsibletype' => agreement_responsible_type::TUTOR,
            'responsibleuserid' => $tutor->id,
            'duedate' => $now + DAYSECS,
            'createdby' => get_admin()->id,
        ]);

        (new \local_monlaututoria\service\notification_reminder_service())->queue_reminders($now);
        (new \local_monlaututoria\service\notification_reminder_service())->queue_reminders($now);

        $rows = (new notification_repository())->find_by_recipient($tutor->id);
        $this->assertCount(3, $rows);
    }
}
