<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\service;

use local_monlaututoria\domain\notification_frequency;
use local_monlaututoria\domain\notification_type;

final class notification_preference_service {
    public const ASSIGNMENT_CHANGES = 'assignmentchanges';
    public const REFERRAL_CHANGES = 'referralchanges';
    public const FOLLOWUP_REMINDERS = 'followupreminders';
    public const AGREEMENT_REMINDERS = 'agreementreminders';
    public const DIGEST_FREQUENCY = 'digestfrequency';
    private const PREFIX = 'local_monlaututoria_notifications_';

    public function get_settings(int $userid): array {
        return [
            self::ASSIGNMENT_CHANGES => $this->get_bool($userid, self::ASSIGNMENT_CHANGES, true),
            self::REFERRAL_CHANGES => $this->get_bool($userid, self::REFERRAL_CHANGES, true),
            self::FOLLOWUP_REMINDERS => $this->get_bool($userid, self::FOLLOWUP_REMINDERS, true),
            self::AGREEMENT_REMINDERS => $this->get_bool($userid, self::AGREEMENT_REMINDERS, true),
            self::DIGEST_FREQUENCY => $this->get_digest_frequency($userid),
        ];
    }

    public function save_settings(int $userid, array $settings): void {
        set_user_preference($this->key(self::ASSIGNMENT_CHANGES), !empty($settings[self::ASSIGNMENT_CHANGES]) ? 1 : 0, $userid);
        set_user_preference($this->key(self::REFERRAL_CHANGES), !empty($settings[self::REFERRAL_CHANGES]) ? 1 : 0, $userid);
        set_user_preference($this->key(self::FOLLOWUP_REMINDERS), !empty($settings[self::FOLLOWUP_REMINDERS]) ? 1 : 0, $userid);
        set_user_preference($this->key(self::AGREEMENT_REMINDERS), !empty($settings[self::AGREEMENT_REMINDERS]) ? 1 : 0, $userid);
        $frequency = $settings[self::DIGEST_FREQUENCY] ?? notification_frequency::DAILY;
        if (!in_array($frequency, notification_frequency::values(), true)) {
            $frequency = notification_frequency::DAILY;
        }
        set_user_preference($this->key(self::DIGEST_FREQUENCY), $frequency, $userid);
    }

    public function is_enabled_for_notification_type(int $userid, string $notificationtype): bool {
        if (in_array($notificationtype, [notification_type::ASSIGNMENT_ASSIGNED, notification_type::ASSIGNMENT_REASSIGNED], true)) {
            return $this->get_bool($userid, self::ASSIGNMENT_CHANGES, true);
        }
        if (in_array($notificationtype, [notification_type::REFERRAL_ASSIGNED, notification_type::REFERRAL_RETURNED], true)) {
            return $this->get_bool($userid, self::REFERRAL_CHANGES, true);
        }
        if (in_array($notificationtype, [notification_type::FOLLOWUP_DUE, notification_type::FOLLOWUP_OVERDUE], true)) {
            return $this->get_bool($userid, self::FOLLOWUP_REMINDERS, true);
        }
        if (in_array($notificationtype, [notification_type::AGREEMENT_DUE, notification_type::AGREEMENT_OVERDUE], true)) {
            return $this->get_bool($userid, self::AGREEMENT_REMINDERS, true);
        }
        return $this->get_digest_frequency($userid) !== notification_frequency::NONE;
    }

    public function get_digest_frequency(int $userid): string {
        $value = get_user_preferences($this->key(self::DIGEST_FREQUENCY), notification_frequency::DAILY, $userid);
        return in_array($value, notification_frequency::values(), true) ? $value : notification_frequency::DAILY;
    }

    private function get_bool(int $userid, string $suffix, bool $default): bool {
        return (bool) get_user_preferences($this->key($suffix), $default ? 1 : 0, $userid);
    }

    private function key(string $suffix): string {
        return self::PREFIX . $suffix;
    }
}
