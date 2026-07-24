<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

final class dispatch_notification_task extends \core\task\adhoc_task {
    public function get_name() {
        return get_string('task_dispatch_notification', 'local_monlaututoria');
    }

    public function execute() {
        $data = (array) $this->get_custom_data();
        if (empty($data['notificationid'])) {
            return;
        }
        (new \local_monlaututoria\service\notification_dispatch_service())->dispatch((int) $data['notificationid']);
    }
}
