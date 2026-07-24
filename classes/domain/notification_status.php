<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

final class notification_status {
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const SENT = 'sent';
    public const FAILED = 'failed';

    public static function retryable_values(): array {
        return [self::PENDING, self::FAILED];
    }
}
