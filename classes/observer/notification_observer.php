<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\observer;

use local_monlaututoria\domain\referral_status;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\referral_repository;
use local_monlaututoria\service\notification_dispatch_service;

final class notification_observer {
    public static function assignment_created(\local_monlaututoria\event\assignment_created $event): void {
        $assignment = (new assignment_repository())->get((int) $event->objectid);
        if ((int) $assignment->isprimary !== 1) {
            return;
        }
        (new notification_dispatch_service())->queue_assignment_created((int) $assignment->tutorid, (int) $event->userid, (int) $assignment->id);
    }

    public static function student_reassigned(\local_monlaututoria\event\student_reassigned $event): void {
        $assignment = (new assignment_repository())->get((int) $event->objectid);
        if ((int) $assignment->isprimary !== 1) {
            return;
        }
        (new notification_dispatch_service())->queue_assignment_reassigned((int) $assignment->tutorid, (int) $event->userid, (int) $assignment->id);
    }

    public static function referral_updated(\local_monlaututoria\event\referral_updated $event): void {
        $referral = (new referral_repository())->get((int) $event->objectid);
        $dispatch = new notification_dispatch_service();
        $assignedto = $event->other['assignedto'] ?? null;
        if (!empty($assignedto)) {
            $dispatch->queue_referral_assigned((int) $assignedto, (int) $event->userid, (int) $referral->id);
        }

        $newstatus = $event->other['newstatus'] ?? null;
        if (in_array($newstatus, [referral_status::RESOLVED, referral_status::CANCELLED], true)) {
            $dispatch->queue_referral_returned((int) $referral->createdby, (int) $event->userid, (int) $referral->id);
        }
    }
}
