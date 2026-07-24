<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_monlaututoria\event;

/**
 * Shared base for local_tut_referral events. get_url() points at
 * referrals/view.php, not student/view.php — unlike agreements/follow-ups,
 * referrals are never shown as a ficha tab (see referral_service's class
 * docblock for why).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class referral_event_base extends \core\event\base {

    /**
     * @return string one of the base::CRUD_* single-char codes
     */
    abstract protected function get_crud_value(): string;

    protected function init() {
        $this->data['objecttable'] = 'local_tut_referral';
        $this->data['crud'] = $this->get_crud_value();
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_objectid_mapping() {
        return ['db' => 'local_tut_referral', 'restore' => \core\event\base::NOT_MAPPED];
    }

    public function get_url() {
        return new \moodle_url('/local/monlaututoria/referrals/view.php', ['id' => $this->objectid]);
    }

    /**
     * @param int $objectid
     * @param int $userid
     * @param int $studentid
     * @param array $other
     * @return static
     */
    protected static function build(int $objectid, int $userid, int $studentid, array $other = []): self {
        return static::create([
            'objectid'      => $objectid,
            'context'       => \context_system::instance(),
            'userid'        => $userid,
            'relateduserid' => $studentid,
            'other'         => $other,
        ]);
    }
}
