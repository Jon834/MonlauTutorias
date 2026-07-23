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

use local_monlaututoria\domain\assignment_close_reason;

/**
 * Confirmation form for closing an active assignment: shows the assignment
 * summary and (when applicable) a warning that the student will be left
 * without an active primary tutor, then asks for a coded reason, the
 * effective closing date, an optional note and explicit confirmation.
 * assignment_service is the sole authority for the business rules (date
 * order, valid reason, cannot close twice).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_close_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('static', 'summary', '', $customdata['summaryhtml']);

        if (!empty($customdata['warningwithoutprimary'])) {
            $mform->addElement('static', 'warning', '', $customdata['warningwithoutprimary']);
        }

        $mform->addElement(
            'select',
            'closereason',
            get_string('assignment_field_closereason', 'local_monlaututoria'),
            assignment_close_reason::get_options()
        );
        $mform->setType('closereason', PARAM_ALPHANUMEXT);
        $mform->addRule('closereason', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_selector', 'timeend', get_string('assignment_field_closedate', 'local_monlaututoria'));

        $mform->addElement('textarea', 'note', get_string('assignment_field_note', 'local_monlaututoria'));
        $mform->setType('note', PARAM_TEXT);

        $mform->addElement(
            'advcheckbox',
            'confirm',
            '',
            get_string('assignment_close_confirm_checkbox', 'local_monlaututoria')
        );
        $mform->setType('confirm', PARAM_BOOL);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('assignment_close_confirm', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['confirm'])) {
            $errors['confirm'] = get_string('error_assignment_close_not_confirmed', 'local_monlaututoria');
        }

        return $errors;
    }
}
