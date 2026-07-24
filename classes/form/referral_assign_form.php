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

namespace local_monlaututoria\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Referral assignment form (phase 6.4) — picks the staff member handling it.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_assign_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'autocomplete',
            'assignedto',
            get_string('referral_field_assignedto', 'local_monlaututoria'),
            [],
            [
                'ajax'              => 'core_user/form_user_selector',
                'multiple'          => false,
                'valuehtmlcallback' => static function ($value) {
                    $user = \core_user::get_user((int) $value);

                    return $user ? fullname($user) : '';
                },
            ]
        );
        $mform->addRule('assignedto', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('referral_assign', 'local_monlaututoria'));
    }
}
