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

namespace local_monlaututoria\observer;

use local_monlaututoria\domain\entry_kind;
use local_monlaututoria\feature;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\entry_repository;

/**
 * Fase 14 — when the SOP orientation tutor records a SOP entry, notify the
 * student's current primary tutor with an in-Moodle notification (the bell).
 *
 * Deliberately independent of the fase 9 notification module (which is hidden
 * in simple mode, where SOP lives) and of its queue/retry machinery: this is
 * a single message_send() with NO SOP content — only the student's name and a
 * link, per the project rule that notifications never carry sensitive notes.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class sop_notification_observer {

    public static function entry_created(\local_monlaututoria\event\entry_created $event): void {
        global $CFG;

        if (!feature::simple_mode()) {
            return;
        }

        require_once($CFG->dirroot . '/lib/messagelib.php');

        $entry = (new entry_repository())->get((int) $event->objectid);
        if (($entry->entrykind ?? entry_kind::REGULAR) !== entry_kind::SOP) {
            return;
        }

        $studentid = (int) $entry->studentid;
        $primary = (new assignment_repository())->find_active_primary($studentid, (int) $entry->academicyearid);
        if ($primary === null) {
            return;
        }

        $tutorid = (int) $primary->tutorid;
        // The primary tutor recorded it themselves (also the SOP tutor, or
        // coordination acting as both): no self-notification.
        if ($tutorid === (int) $event->userid) {
            return;
        }

        $tutor = \core_user::get_user($tutorid);
        $student = \core_user::get_user($studentid);
        if (!$tutor || !$student || !empty($tutor->deleted) || !empty($tutor->suspended)) {
            return;
        }

        $url = new \moodle_url('/local/monlaututoria/entries/view.php', ['id' => (int) $entry->id]);
        $studentname = fullname($student);

        $message = new \core\message\message();
        $message->component         = 'local_monlaututoria';
        $message->name              = 'sopentryrecorded';
        $message->userfrom          = \core_user::get_noreply_user();
        $message->userto            = $tutor;
        $message->subject           = get_string('message_sopentry_subject', 'local_monlaututoria', $studentname);
        $message->fullmessage       = get_string('message_sopentry_body', 'local_monlaututoria', (object) [
            'student' => $studentname,
            'url'     => $url->out(false),
        ]);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml   = get_string('message_sopentry_body_html', 'local_monlaututoria', (object) [
            'student' => s($studentname),
            'url'     => $url->out(false),
        ]);
        $message->smallmessage      = get_string('message_sopentry_subject', 'local_monlaututoria', $studentname);
        $message->notification      = 1;
        $message->contexturl        = $url->out(false);
        $message->contexturlname    = get_string('entry_detail_title', 'local_monlaututoria');
        $message->courseid          = SITEID;

        message_send($message);
    }
}
