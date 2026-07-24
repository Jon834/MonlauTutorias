<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\service;

use local_monlaututoria\domain\assignment_type;
use local_monlaututoria\domain\notification_frequency;
use local_monlaututoria\domain\notification_status;
use local_monlaututoria\domain\notification_type;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\notification_repository;
use local_monlaututoria\task\dispatch_notification_task;

final class notification_dispatch_service {
    private const MESSAGE_PROVIDER_CHANGES = 'changes';
    private const MESSAGE_PROVIDER_REMINDERS = 'reminders';
    private const MESSAGE_PROVIDER_DIGESTS = 'digests';

    public function queue_assignment_created(int $recipientid, int $actorid, int $assignmentid): ?int {
        return $this->queue_entity_notification(notification_type::ASSIGNMENT_ASSIGNED, $recipientid, $actorid, 'assignment', $assignmentid);
    }

    public function queue_assignment_reassigned(int $recipientid, int $actorid, int $assignmentid): ?int {
        return $this->queue_entity_notification(notification_type::ASSIGNMENT_REASSIGNED, $recipientid, $actorid, 'assignment', $assignmentid);
    }

    public function queue_referral_assigned(int $recipientid, int $actorid, int $referralid): ?int {
        return $this->queue_entity_notification(notification_type::REFERRAL_ASSIGNED, $recipientid, $actorid, 'referral', $referralid);
    }

    public function queue_referral_returned(int $recipientid, int $actorid, int $referralid): ?int {
        return $this->queue_entity_notification(notification_type::REFERRAL_RETURNED, $recipientid, $actorid, 'referral', $referralid);
    }

    public function queue_followup_due(int $recipientid, int $followupid, string $bucketkey): ?int {
        return $this->queue_entity_notification(notification_type::FOLLOWUP_DUE, $recipientid, null, 'followup', $followupid, $bucketkey);
    }

    public function queue_followup_overdue(int $recipientid, int $followupid, string $bucketkey): ?int {
        return $this->queue_entity_notification(notification_type::FOLLOWUP_OVERDUE, $recipientid, null, 'followup', $followupid, $bucketkey);
    }

    public function queue_agreement_due(int $recipientid, int $agreementid, string $bucketkey): ?int {
        return $this->queue_entity_notification(notification_type::AGREEMENT_DUE, $recipientid, null, 'agreement', $agreementid, $bucketkey);
    }

    public function queue_agreement_overdue(int $recipientid, int $agreementid, string $bucketkey): ?int {
        return $this->queue_entity_notification(notification_type::AGREEMENT_OVERDUE, $recipientid, null, 'agreement', $agreementid, $bucketkey);
    }

    public function queue_digest(int $recipientid, string $frequency, string $bucketkey): ?int {
        $preference = new notification_preference_service();
        if ($frequency !== $preference->get_digest_frequency($recipientid)) {
            return null;
        }

        $type = $frequency === notification_frequency::WEEKLY ? notification_type::WEEKLY_DIGEST : notification_type::DAILY_DIGEST;
        $repository = new notification_repository();
        $id = $repository->create_if_missing((object) [
            'notificationtype' => $type,
            'recipientid' => $recipientid,
            'actorid' => null,
            'entitytype' => 'digest',
            'entityid' => $recipientid,
            'digestkey' => $bucketkey,
        ]);
        $this->enqueue_dispatch_task($id);

        return $id;
    }

    public function dispatch(int $notificationid): void {
        $repository = new notification_repository();
        $log = $repository->get($notificationid);
        if ($log->status === notification_status::SENT) {
            return;
        }
        if (!$repository->claim_for_send($notificationid)) {
            return;
        }

        try {
            $message = $this->build_message($repository->get($notificationid));
            if ($message === null) {
                $repository->mark_failed($notificationid, 'Notification recipient or entity no longer exists.');
                return;
            }

            message_send($message);
            $repository->mark_sent($notificationid);
        } catch (\Throwable $e) {
            $repository->mark_failed($notificationid, $e->getMessage());
        }
    }

    private function queue_entity_notification(
        string $notificationtype,
        int $recipientid,
        ?int $actorid,
        string $entitytype,
        int $entityid,
        string $digestkey = ''
    ): ?int {
        if ($actorid !== null && $recipientid === $actorid) {
            return null;
        }

        $preference = new notification_preference_service();
        if (!$preference->is_enabled_for_notification_type($recipientid, $notificationtype)) {
            return null;
        }

        $repository = new notification_repository();
        $id = $repository->create_if_missing((object) [
            'notificationtype' => $notificationtype,
            'recipientid' => $recipientid,
            'actorid' => $actorid,
            'entitytype' => $entitytype,
            'entityid' => $entityid,
            'digestkey' => $digestkey,
        ]);
        $this->enqueue_dispatch_task($id);

        return $id;
    }

    private function enqueue_dispatch_task(int $notificationid): void {
        $task = new dispatch_notification_task();
        $task->set_custom_data(['notificationid' => $notificationid]);
        \core\task\manager::queue_adhoc_task($task);
    }

    private function build_message(\stdClass $log): ?\core\message\message {
        $recipient = \core_user::get_user((int) $log->recipientid);
        if (!$recipient || !empty($recipient->deleted) || !empty($recipient->suspended)) {
            return null;
        }

        [$subject, $body, $url, $providername] = $this->build_payload($log);
        if ($subject === null || $body === null || $url === null) {
            return null;
        }

        $message = new \core\message\message();
        $message->component = 'local_monlaututoria';
        $message->name = $providername;
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $recipient;
        $message->subject = $subject;
        $message->fullmessage = $body;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '<p>' . s($body) . '</p>';
        $message->smallmessage = $subject;
        $message->notification = 1;
        $message->contexturl = $url->out(false);
        $message->contexturlname = get_string('pluginname', 'local_monlaututoria');

        return $message;
    }

    private function build_payload(\stdClass $log): array {
        switch ($log->notificationtype) {
            case notification_type::ASSIGNMENT_ASSIGNED:
                return $this->build_assignment_payload((int) $log->entityid, 'notification_subject_assignment_assigned', 'notification_body_assignment_assigned');
            case notification_type::ASSIGNMENT_REASSIGNED:
                return $this->build_assignment_payload((int) $log->entityid, 'notification_subject_assignment_reassigned', 'notification_body_assignment_reassigned');
            case notification_type::REFERRAL_ASSIGNED:
                return $this->build_referral_payload((int) $log->entityid, 'notification_subject_referral_assigned', 'notification_body_referral_assigned');
            case notification_type::REFERRAL_RETURNED:
                return $this->build_referral_payload((int) $log->entityid, 'notification_subject_referral_returned', 'notification_body_referral_returned');
            case notification_type::FOLLOWUP_DUE:
                return $this->build_simple_dashboard_payload((int) $log->recipientid, 'notification_subject_followup_due', 'notification_body_followup_due');
            case notification_type::FOLLOWUP_OVERDUE:
                return $this->build_simple_dashboard_payload((int) $log->recipientid, 'notification_subject_followup_overdue', 'notification_body_followup_overdue');
            case notification_type::AGREEMENT_DUE:
                return $this->build_simple_dashboard_payload((int) $log->recipientid, 'notification_subject_agreement_due', 'notification_body_agreement_due');
            case notification_type::AGREEMENT_OVERDUE:
                return $this->build_simple_dashboard_payload((int) $log->recipientid, 'notification_subject_agreement_overdue', 'notification_body_agreement_overdue');
            case notification_type::DAILY_DIGEST:
                return $this->build_digest_payload((int) $log->recipientid, notification_frequency::DAILY);
            case notification_type::WEEKLY_DIGEST:
                return $this->build_digest_payload((int) $log->recipientid, notification_frequency::WEEKLY);
        }

        return [null, null, null, null];
    }

    private function build_assignment_payload(int $assignmentid, string $subjectkey, string $bodykey): array {
        $assignment = (new assignment_repository())->get($assignmentid);
        if ((int) $assignment->isprimary !== 1 || $assignment->assignmenttype !== assignment_type::PRIMARY) {
            return [null, null, null, null];
        }

        $url = new \moodle_url('/local/monlaututoria/student/view.php', [
            'id' => (int) $assignment->studentid,
            'academicyearid' => (int) $assignment->academicyearid,
        ]);

        return [get_string($subjectkey, 'local_monlaututoria'), get_string($bodykey, 'local_monlaututoria'), $url, self::MESSAGE_PROVIDER_CHANGES];
    }

    private function build_referral_payload(int $referralid, string $subjectkey, string $bodykey): array {
        $url = new \moodle_url('/local/monlaututoria/referrals/view.php', ['id' => $referralid]);
        return [get_string($subjectkey, 'local_monlaututoria'), get_string($bodykey, 'local_monlaututoria'), $url, self::MESSAGE_PROVIDER_CHANGES];
    }

    private function build_simple_dashboard_payload(int $recipientid, string $subjectkey, string $bodykey): array {
        return [get_string($subjectkey, 'local_monlaututoria'), get_string($bodykey, 'local_monlaututoria'), $this->get_best_dashboard_url($recipientid), self::MESSAGE_PROVIDER_REMINDERS];
    }

    private function build_digest_payload(int $recipientid, string $frequency): array {
        $year = (new academic_year_repository())->get_active();
        if ($year === null) {
            return [null, null, null, null];
        }

        $dashboard = (new dashboard_service())->get_tutor_dashboard($recipientid, (int) $year->id);
        if ($dashboard->summary->assignedcount === 0) {
            return [null, null, null, null];
        }

        $subjectkey = $frequency === notification_frequency::WEEKLY ? 'notification_subject_weekly_digest' : 'notification_subject_daily_digest';
        $bodykey = $frequency === notification_frequency::WEEKLY ? 'notification_body_weekly_digest' : 'notification_body_daily_digest';
        $body = get_string($bodykey, 'local_monlaututoria', (object) [
            'assignedcount' => $dashboard->summary->assignedcount,
            'upcomingfollowupcount' => $dashboard->summary->upcomingfollowupcount,
            'overduefollowupcount' => $dashboard->summary->overduefollowupcount,
            'pendingagreementcount' => $dashboard->summary->pendingagreementcount,
            'overdueagreementcount' => $dashboard->summary->overdueagreementcount,
            'openreferralcount' => $dashboard->summary->openreferralcount,
        ]);

        return [get_string($subjectkey, 'local_monlaututoria'), $body, $this->get_best_dashboard_url($recipientid), self::MESSAGE_PROVIDER_DIGESTS];
    }

    private function get_best_dashboard_url(int $recipientid): \moodle_url {
        $context = \context_system::instance();
        if (has_capability('local/monlaututoria:viewcoordinationdashboard', $context, $recipientid)
            || has_capability('local/monlaututoria:viewallassignments', $context, $recipientid)) {
            return new \moodle_url('/local/monlaututoria/coordination.php');
        }
        return new \moodle_url('/local/monlaututoria/dashboard.php');
    }
}
