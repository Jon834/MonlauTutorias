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

use local_monlaututoria\domain\assignment_reassign_reason;

/**
 * Confirmation form for reassigning an active primary tutor assignment.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_reassign_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('static', 'summary', '', $customdata['summaryhtml']);

        if (!empty($customdata['warninghtml'])) {
            $mform->addElement('static', 'warning', '', $customdata['warninghtml']);
        }

        $userselectoroptions = [
            'ajax' => 'core_user/form_user_selector',
            'multiple' => false,
            'valuehtmlcallback' => function ($value) {
                $user = \core_user::get_user((int) $value);
                return $user ? fullname($user) : '';
            },
        ];

        $mform->addElement(
            'autocomplete',
            'newtutorid',
            get_string('assignment_field_newtutor', 'local_monlaututoria'),
            [],
            $userselectoroptions
        );
        $mform->setType('newtutorid', PARAM_INT);
        $mform->addRule('newtutorid', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'select',
            'reassignreason',
            get_string('assignment_field_reassignreason', 'local_monlaututoria'),
            assignment_reassign_reason::get_options()
        );
        $mform->setType('reassignreason', PARAM_ALPHANUMEXT);
        $mform->addRule('reassignreason', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'date_selector',
            'effectivedate',
            get_string('assignment_field_reassigndate', 'local_monlaututoria')
        );

        $mform->addElement(
            'advcheckbox',
            'keepcotutors',
            '',
            get_string('assignment_field_keepcotutors', 'local_monlaututoria')
        );
        $mform->setType('keepcotutors', PARAM_BOOL);

        $mform->addElement(
            'advcheckbox',
            'confirm',
            '',
            get_string('assignment_reassign_confirm_checkbox', 'local_monlaututoria')
        );
        $mform->setType('confirm', PARAM_BOOL);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'studentid');
        $mform->setType('studentid', PARAM_INT);

        $mform->addElement('hidden', 'currenttutorid');
        $mform->setType('currenttutorid', PARAM_INT);

        $this->add_action_buttons(true, get_string('assignment_reassign_confirm', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['studentid']) && !empty($data['newtutorid']) && (int) $data['studentid'] === (int) $data['newtutorid']) {
            $errors['newtutorid'] = get_string('error_assignment_self', 'local_monlaututoria');
        }

        if (!empty($data['currenttutorid']) && !empty($data['newtutorid']) && (int) $data['currenttutorid'] === (int) $data['newtutorid']) {
            $errors['newtutorid'] = get_string('error_assignment_reassign_same_tutor', 'local_monlaututoria');
        }

        if (empty($data['confirm'])) {
            $errors['confirm'] = get_string('error_assignment_reassign_not_confirmed', 'local_monlaututoria');
        }

        return $errors;
    }
}
