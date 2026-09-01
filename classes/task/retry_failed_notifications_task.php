<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

use local_monlaututoria\repository\notification_repository;

final class retry_failed_notifications_task extends \core\task\scheduled_task {
    public const RETRY_DELAY_SECONDS = 900;
    public const MAX_ATTEMPTS = 5;

    public function get_name() {
        return get_string('task_retry_failed_notifications', 'local_monlaututoria');
    }

    public function execute() {
        // Fase 13 — no-op in simple mode (notifications hidden).
        if (!\local_monlaututoria\feature::enabled(\local_monlaututoria\feature::NOTIFICATIONS)) {
            return;
        }
        $repository = new notification_repository();
        foreach ($repository->find_retryable_failed(self::RETRY_DELAY_SECONDS, self::MAX_ATTEMPTS) as $record) {
            $task = new dispatch_notification_task();
            $task->set_custom_data(['notificationid' => (int) $record->id]);
            \core\task\manager::queue_adhoc_task($task);
        }
    }
}
