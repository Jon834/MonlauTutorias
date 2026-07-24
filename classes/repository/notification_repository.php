<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\repository;

use local_monlaututoria\domain\notification_status;

final class notification_repository {
    private const TABLE = 'local_tut_notification';

    public function create_if_missing(\stdClass $data): int {
        global $DB;

        $record = (object) [
            'notificationtype' => $data->notificationtype,
            'recipientid' => (int) $data->recipientid,
            'actorid' => isset($data->actorid) ? (int) $data->actorid : null,
            'entitytype' => $data->entitytype,
            'entityid' => (int) $data->entityid,
            'digestkey' => (string) ($data->digestkey ?? ''),
            'status' => notification_status::PENDING,
            'attempts' => 0,
            'lasterror' => null,
            'timesent' => null,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        try {
            return $DB->insert_record(self::TABLE, $record);
        } catch (\dml_write_exception $e) {
            return $this->get_existing_id(
                $record->notificationtype,
                $record->recipientid,
                $record->entitytype,
                $record->entityid,
                $record->digestkey
            );
        }
    }

    public function get(int $id): \stdClass {
        global $DB;
        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    public function get_existing_id(string $notificationtype, int $recipientid, string $entitytype, int $entityid, string $digestkey): int {
        global $DB;
        return (int) $DB->get_field(self::TABLE, 'id', [
            'notificationtype' => $notificationtype,
            'recipientid' => $recipientid,
            'entitytype' => $entitytype,
            'entityid' => $entityid,
            'digestkey' => $digestkey,
        ], MUST_EXIST);
    }

    public function claim_for_send(int $id): bool {
        global $DB;

        $record = $this->get($id);
        if (!in_array($record->status, notification_status::retryable_values(), true)) {
            return false;
        }

        $record->status = notification_status::PROCESSING;
        $record->attempts = (int) $record->attempts + 1;
        $record->lasterror = null;
        $record->timemodified = time();
        return $DB->update_record(self::TABLE, $record);
    }

    public function mark_sent(int $id): void {
        global $DB;
        $record = $this->get($id);
        $record->status = notification_status::SENT;
        $record->timesent = time();
        $record->timemodified = $record->timesent;
        $record->lasterror = null;
        $DB->update_record(self::TABLE, $record);
    }

    public function mark_failed(int $id, string $message): void {
        global $DB;
        $record = $this->get($id);
        $record->status = notification_status::FAILED;
        $record->lasterror = \core_text::substr($message, 0, 1333);
        $record->timemodified = time();
        $DB->update_record(self::TABLE, $record);
    }

    public function find_retryable_failed(int $olderthanseconds, int $maxattempts = 5, int $limit = 100): array {
        global $DB;
        $cutoff = time() - $olderthanseconds;
        return $DB->get_records_select(
            self::TABLE,
            'status = :status AND attempts < :attempts AND timemodified <= :cutoff',
            ['status' => notification_status::FAILED, 'attempts' => $maxattempts, 'cutoff' => $cutoff],
            'timemodified ASC, id ASC',
            '*',
            0,
            $limit
        );
    }

    public function find_older_than(int $ttlseconds, array $statuses): array {
        global $DB;
        if (empty($statuses)) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');
        $params['cutoff'] = time() - $ttlseconds;
        return $DB->get_records_select(self::TABLE, 'status ' . $insql . ' AND timemodified <= :cutoff', $params, 'timemodified ASC, id ASC');
    }

    public function delete(int $id): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['id' => $id]);
    }

    public function find_by_recipient(int $recipientid): array {
        global $DB;
        return $DB->get_records(self::TABLE, ['recipientid' => $recipientid], 'id ASC');
    }
}
