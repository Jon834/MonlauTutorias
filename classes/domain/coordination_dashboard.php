<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\domain;

/**
 * Full coordination dashboard payload.
 */
final class coordination_dashboard {
    /**
     * @param int[] $cohortids
     * @param array<int, string> $cohortlabels
     * @param array<int, string> $tutoroptions
     * @param coordination_breakdown_row[] $cohortbreakdown
     * @param coordination_breakdown_row[] $tutorbreakdown
     */
    public function __construct(
        public readonly int $viewerid,
        public readonly int $academicyearid,
        public readonly array $cohortids,
        public readonly ?int $selectedtutorid,
        public readonly int $generatedat,
        public readonly coordination_dashboard_summary $summary,
        public readonly coordination_quality_summary $quality,
        public readonly array $cohortlabels,
        public readonly array $tutoroptions,
        public readonly array $cohortbreakdown,
        public readonly array $tutorbreakdown
    ) {
    }
}
