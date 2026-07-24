<?php
// This file is part of Moodle - http://moodle.org/

namespace local_monlaututoria\event;

final class coordination_dashboard_exported extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'cohort';
    }

    public static function get_name() {
        return get_string('eventcoordinationdashboardexported', 'local_monlaututoria');
    }

    public function get_description() {
        return "The user with id {$this->userid} exported the coordination dashboard in {$this->other['format']} format for academic year {$this->other['academicyearid']} ({$this->other['rowcount']} row(s)).";
    }

    public static function create_from_export(int $userid, int $academicyearid, array $cohortids, string $format, int $rowcount, ?int $tutorid = null): self {
        return self::create([
            'context' => \context_system::instance(),
            'relateduserid' => $userid,
            'userid' => $userid,
            'objectid' => !empty($cohortids) ? (int) reset($cohortids) : 0,
            'other' => [
                'academicyearid' => $academicyearid,
                'cohortcount' => count($cohortids),
                'format' => $format,
                'rowcount' => $rowcount,
                'tutorid' => $tutorid,
            ],
        ]);
    }
}
