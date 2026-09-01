<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

final class send_notification_reminders_task extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task_send_notification_reminders', 'local_monlaututoria');
    }

    public function execute() {
        // Fase 13 — no-op while notifications are hidden (simple mode). The
        // task stays registered so re-enabling the feature needs no upgrade.
        if (!\local_monlaututoria\feature::enabled(\local_monlaututoria\feature::NOTIFICATIONS)) {
            return;
        }
        (new \local_monlaututoria\service\notification_reminder_service())->queue_reminders();
    }
}
