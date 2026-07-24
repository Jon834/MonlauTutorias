<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

/**
 * Quality indicators for the coordination dashboard.
 */
final class coordination_quality_summary {
    public function __construct(
        public readonly float $averagedaystofirstentry,
        public readonly int $studentswithfirstentrysample,
        public readonly float $agreementcompletionpercent,
        public readonly int $completedagreementcount,
        public readonly int $totalagreementcount,
        public readonly float $followupontimepercent,
        public readonly int $ontimefollowupcount,
        public readonly int $completedfollowupcount,
        public readonly int $familycontactcount,
        public readonly float $continuitypercent,
        public readonly int $continuitysuccesscount,
        public readonly int $continuitysamplecount
    ) {
    }
}
