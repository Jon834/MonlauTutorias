<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\task;

use local_monlaututoria\domain\notification_status;

final class cleanup_notification_logs_task_test extends \advanced_testcase {
    public function test_old_sent_and_failed_logs_are_deleted(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $old = time() - cleanup_notification_logs_task::TTL_SECONDS - HOURSECS;

        $DB->insert_record('local_tut_notification', (object) [
            'notificationtype' => 'daily_digest',
            'recipientid' => $user->id,
            'actorid' => null,
            'entitytype' => 'digest',
            'entityid' => $user->id,
            'digestkey' => '20261015',
            'status' => notification_status::SENT,
            'attempts' => 1,
            'lasterror' => null,
            'timesent' => $old,
            'timecreated' => $old,
            'timemodified' => $old,
        ]);
        $recentid = $DB->insert_record('local_tut_notification', (object) [
            'notificationtype' => 'daily_digest',
            'recipientid' => $user->id,
            'actorid' => null,
            'entitytype' => 'digest',
            'entityid' => $user->id,
            'digestkey' => '20261016',
            'status' => notification_status::FAILED,
            'attempts' => 1,
            'lasterror' => 'x',
            'timesent' => null,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        (new cleanup_notification_logs_task())->execute();

        $this->assertFalse($DB->record_exists('local_tut_notification', ['digestkey' => '20261015']));
        $this->assertTrue($DB->record_exists('local_tut_notification', ['id' => $recentid]));
    }
}
