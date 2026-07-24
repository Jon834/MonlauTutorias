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
 * Tutoring entry edit form (phase 5.5). Student, tutor, academic year,
 * entry date and status are never fields here — the same rationale as
 * assignment_edit_form: entry_service::update() does not read them at all,
 * so tampering with the request cannot change them.
 *
 * The restricted note is only added when customdata['showrestricted'] is
 * true (resolved by entries/edit.php from viewrestrictednotes), same
 * pattern as entry_full_form — never rendered-then-hidden.
 *
 * The change-reason field is only added, and only required, when
 * customdata['requirereason'] is true — the edit is happening outside the
 * configurable edit window (see entry_service::update()).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_edit_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('static', 'studentname', get_string('assignment_col_student', 'local_monlaututoria'), $customdata['studentname']);
        $mform->addElement('static', 'entrydateformatted', get_string('entry_field_entrydate', 'local_monlaututoria'), $customdata['entrydateformatted']);

        $mform->addElement(
            'select',
            'modalityid',
            get_string('entry_field_modality', 'local_monlaututoria'),
            [0 => get_string('choosedots')] + $customdata['modalities']
        );
        $mform->setType('modalityid', PARAM_INT);

        $mform->addElement('textarea', 'contentvisible', get_string('entry_field_contentvisible', 'local_monlaututoria'));
        $mform->setType('contentvisible', PARAM_TEXT);
        $mform->addRule('contentvisible', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'noteinternal', get_string('entry_field_noteinternal', 'local_monlaututoria'));
        $mform->setType('noteinternal', PARAM_TEXT);

        if (!empty($customdata['showrestricted'])) {
            $mform->addElement('textarea', 'noterestricted', get_string('entry_field_noterestricted', 'local_monlaututoria'));
            $mform->setType('noterestricted', PARAM_TEXT);
        }

        $mform->addElement(
            'date_selector',
            'nextfollowupdate',
            get_string('entry_field_nextfollowupdate', 'local_monlaututoria'),
            ['optional' => true]
        );

        if (!empty($customdata['requirereason'])) {
            $mform->addElement('textarea', 'reason', get_string('entry_field_editreason', 'local_monlaututoria'));
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

        if (!empty($this->_customdata['requirereason']) && trim((string) ($data['reason'] ?? '')) === '') {
            $errors['reason'] = get_string('error_entry_edit_reason_required', 'local_monlaututoria');
        }

        return $errors;
    }
}
