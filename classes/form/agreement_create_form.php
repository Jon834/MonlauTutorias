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

use local_monlaututoria\domain\agreement_responsible_type;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Agreement creation form (phase 6.1). entryid is a hidden field, carried
 * from the page — the agreement always concerns the entry it is opened from,
 * never a free-standing selector.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_create_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'entryid');
        $mform->setType('entryid', PARAM_INT);

        $mform->addElement('textarea', 'description', get_string('agreement_field_description', 'local_monlaututoria'));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'select',
            'responsibletype',
            get_string('agreement_field_responsibletype', 'local_monlaututoria'),
            agreement_responsible_type::get_options()
        );

        $mform->addElement(
            'autocomplete',
            'responsibleuserid',
            get_string('agreement_field_responsibleuser', 'local_monlaututoria'),
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

        $mform->addElement(
            'text',
            'responsibleexternalname',
            get_string('agreement_field_responsibleexternalname', 'local_monlaututoria')
        );
        $mform->setType('responsibleexternalname', PARAM_TEXT);

        $mform->addElement('date_selector', 'duedate', get_string('agreement_field_duedate', 'local_monlaututoria'));
        $mform->setDefault('duedate', time() + WEEKSECS);

        $mform->addElement(
            'advcheckbox',
            'visibletostudent',
            get_string('agreement_field_visibletostudent', 'local_monlaututoria')
        );

        $this->add_action_buttons(true, get_string('agreement_create', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $hasuserid = !empty($data['responsibleuserid']);
        $hasexternalname = trim((string) ($data['responsibleexternalname'] ?? '')) !== '';

        if ($hasuserid === $hasexternalname) {
            $errors['responsibletype'] = get_string('error_agreement_responsible_identity_invalid', 'local_monlaututoria');
        }

        return $errors;
    }
}
