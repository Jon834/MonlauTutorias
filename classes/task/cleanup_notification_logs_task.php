<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

use local_monlaututoria\domain\notification_status;
use local_monlaututoria\repository\notification_repository;

final class cleanup_notification_logs_task extends \core\task\scheduled_task {
    public const TTL_SECONDS = 180 * DAYSECS;

    public function get_name() {
        return get_string('task_cleanup_notification_logs', 'local_monlaututoria');
    }

    public function execute() {
        $repository = new notification_repository();
        foreach ($repository->find_older_than(self::TTL_SECONDS, [notification_status::SENT, notification_status::FAILED]) as $record) {
            $repository->delete((int) $record->id);
        }
    }
}
