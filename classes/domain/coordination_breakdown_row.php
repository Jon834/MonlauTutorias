<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

/**
 * One aggregated row in the coordination dashboard breakdown tables.
 */
final class coordination_breakdown_row {
    public function __construct(
        public readonly ?int $entityid,
        public readonly string $label,
        public readonly int $studentcount,
        public readonly int $withinitialcount,
        public readonly int $withoutentrycount,
        public readonly int $overduefollowupcount,
        public readonly int $opencasecount
    ) {
    }
}
