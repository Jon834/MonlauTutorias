<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\service;

use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\notification_repository;

final class notification_dispatch_service_test extends \advanced_testcase {
    private function create_year(): int {
        return (new academic_year_repository())->create((object) [
            'name' => '2026-2027',
            'shortname' => 'notif-' . uniqid(),
            'startdate' => strtotime('2026-09-01'),
            'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
    }

    private function create_assignment(int $studentid, int $tutorid, int $yearid): int {
        return (new assignment_repository())->create((object) [
            'studentid' => $studentid,
            'tutorid' => $tutorid,
            'academicyearid' => $yearid,
            'assignmenttype' => 'primary',
            'isprimary' => 1,
            'status' => 'active',
            'timestart' => time() - DAYSECS,
            'createdby' => get_admin()->id,
        ]);
    }

    public function test_queue_assignment_created_respects_preference(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $yearid = $this->create_year();
        $assignmentid = $this->create_assignment($student->id, $tutor->id, $yearid);

        (new notification_preference_service())->save_settings($tutor->id, [
            notification_preference_service::ASSIGNMENT_CHANGES => 0,
            notification_preference_service::REFERRAL_CHANGES => 1,
            notification_preference_service::FOLLOWUP_REMINDERS => 1,
            notification_preference_service::AGREEMENT_REMINDERS => 1,
            notification_preference_service::DIGEST_FREQUENCY => 'daily',
        ]);

        $id = (new notification_dispatch_service())->queue_assignment_created($tutor->id, get_admin()->id, $assignmentid);
        $this->assertNull($id);
        $this->assertCount(0, (new notification_repository())->find_by_recipient($tutor->id));
    }

    public function test_dispatch_sends_message_once_for_duplicate_queue_requests(): void {
        $this->resetAfterTest();

        $sink = $this->redirectMessages();
        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $yearid = $this->create_year();
        $assignmentid = $this->create_assignment($student->id, $tutor->id, $yearid);

        $service = new notification_dispatch_service();
        $first = $service->queue_assignment_created($tutor->id, get_admin()->id, $assignmentid);
        $second = $service->queue_assignment_created($tutor->id, get_admin()->id, $assignmentid);

        $this->assertSame($first, $second);
        $service->dispatch((int) $first);
        $service->dispatch((int) $second);

        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(1, $messages);
    }
}
