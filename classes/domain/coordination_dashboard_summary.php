<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

/**
 * Aggregate headline counts for the coordination dashboard.
 */
final class coordination_dashboard_summary {
    public function __construct(
        public readonly int $populationcount,
        public readonly int $withinitialcount,
        public readonly int $withoutentrycount,
        public readonly int $overduefollowupcount,
        public readonly int $opencasecount
    ) {
    }
}
