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
 * Confirmation form for annulling a tutoring entry (phase 5.5): shows a
 * summary, then asks for a free-text reason (always required — annulment
 * has no "quick" version, unlike editing) and explicit confirmation.
 * entry_service is the sole authority for the business rules (cannot annul
 * twice).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_annul_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('static', 'summary', '', $customdata['summaryhtml']);

        $mform->addElement('textarea', 'reason', get_string('entry_field_annulreason', 'local_monlaututoria'));
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'advcheckbox',
            'confirm',
            '',
            get_string('entry_annul_confirm_checkbox', 'local_monlaututoria')
        );
        $mform->setType('confirm', PARAM_BOOL);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('entry_annul_confirm', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['confirm'])) {
            $errors['confirm'] = get_string('error_entry_annul_not_confirmed', 'local_monlaututoria');
        }

        return $errors;
    }
}
