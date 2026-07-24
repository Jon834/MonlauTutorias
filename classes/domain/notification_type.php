<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

final class notification_type {
    public const ASSIGNMENT_ASSIGNED = 'assignment_assigned';
    public const ASSIGNMENT_REASSIGNED = 'assignment_reassigned';
    public const REFERRAL_ASSIGNED = 'referral_assigned';
    public const REFERRAL_RETURNED = 'referral_returned';
    public const FOLLOWUP_DUE = 'followup_due';
    public const FOLLOWUP_OVERDUE = 'followup_overdue';
    public const AGREEMENT_DUE = 'agreement_due';
    public const AGREEMENT_OVERDUE = 'agreement_overdue';
    public const DAILY_DIGEST = 'daily_digest';
    public const WEEKLY_DIGEST = 'weekly_digest';

    public static function values(): array {
        return [
            self::ASSIGNMENT_ASSIGNED,
            self::ASSIGNMENT_REASSIGNED,
            self::REFERRAL_ASSIGNED,
            self::REFERRAL_RETURNED,
            self::FOLLOWUP_DUE,
            self::FOLLOWUP_OVERDUE,
            self::AGREEMENT_DUE,
            self::AGREEMENT_OVERDUE,
            self::DAILY_DIGEST,
            self::WEEKLY_DIGEST,
        ];
    }
}
