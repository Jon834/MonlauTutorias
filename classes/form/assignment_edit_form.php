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
 * Assignment edit form. Only exposes the fields that assignment_service
 * actually allows to change (cohort, dates, note). Student and tutor are
 * shown as read-only context (static elements, not inputs) — they are never
 * submitted, so tampering with the request cannot change them: the service
 * layer does not read studentid/tutorid from update() at all.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_edit_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('static', 'studentname', get_string('assignment_col_student', 'local_monlaututoria'), $customdata['studentname']);
        $mform->addElement('static', 'tutorname', get_string('assignment_col_tutor', 'local_monlaututoria'), $customdata['tutorname']);

        $cohortoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + $customdata['cohorts'];
        $mform->addElement('select', 'cohortid', get_string('assignment_col_cohort', 'local_monlaututoria'), $cohortoptions);
        $mform->setType('cohortid', PARAM_INT);

        $mform->addElement('date_selector', 'timestart', get_string('assignment_col_timestart', 'local_monlaututoria'));

        $mform->addElement(
            'date_selector',
            'timeend',
            get_string('assignment_col_timeend', 'local_monlaututoria'),
            ['optional' => true]
        );

        $mform->addElement('textarea', 'note', get_string('assignment_field_note', 'local_monlaututoria'));
        $mform->setType('note', PARAM_TEXT);

        if (!empty($customdata['requirereason'])) {
            $mform->addElement('textarea', 'reason', get_string('assignment_field_editreason', 'local_monlaututoria'));
            $mform->setType('reason', PARAM_TEXT);
            $mform->addRule('reason', get_string('required'), 'required', null, 'client');
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['timeend']) && $data['timeend'] < $data['timestart']) {
            $errors['timeend'] = get_string('error_assignment_dates_invalid', 'local_monlaututoria');
        }

        if (!empty($this->_customdata['requirereason']) && trim((string) ($data['reason'] ?? '')) === '') {
            $errors['reason'] = get_string('error_assignment_edit_reason_required', 'local_monlaututoria');
        }

        return $errors;
    }
}
