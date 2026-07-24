<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

final class notification_frequency {
    public const NONE = 'none';
    public const DAILY = 'daily';
    public const WEEKLY = 'weekly';

    public static function values(): array {
        return [self::NONE, self::DAILY, self::WEEKLY];
    }
}
