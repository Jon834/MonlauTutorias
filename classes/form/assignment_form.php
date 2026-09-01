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

use local_monlaututoria\domain\assignment_type;

/**
 * Manual assignment creation form. Contains only field-level validation;
 * assignment_service is the sole authority for the business rules (duplicate
 * checks, suspended/deleted users, locked academic years, single primary
 * tutor).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Confirmed working against a real Moodle 5.1 instance (phase 3B.1).
        $userselectoroptions = [
            'ajax'     => 'core_user/form_user_selector',
            'multiple' => false,
            'valuehtmlcallback' => function ($value) {
                $user = \core_user::get_user((int) $value);

                return $user ? fullname($user) : '';
            },
        ];

        $mform->addElement('autocomplete', 'studentid', get_string('assignment_col_student', 'local_monlaututoria'), [], $userselectoroptions);
        $mform->setType('studentid', PARAM_INT);
        $mform->addRule('studentid', get_string('required'), 'required', null, 'client');

        $mform->addElement('autocomplete', 'tutorid', get_string('assignment_col_tutor', 'local_monlaututoria'), [], $userselectoroptions);
        $mform->setType('tutorid', PARAM_INT);
        $mform->addRule('tutorid', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'select',
            'academicyearid',
            get_string('assignment_col_academicyear', 'local_monlaututoria'),
            $customdata['academicyears']
        );
        $mform->setType('academicyearid', PARAM_INT);
        $mform->addRule('academicyearid', get_string('required'), 'required', null, 'client');

        // Fase 13 — in simple mode there is only the primary tutor, and
        // cohorts (an import concept) are not surfaced. The service still
        // accepts cohortid=0 and assignmenttype=primary exactly as before.
        $simplemode = \local_monlaututoria\feature::simple_mode();

        if ($simplemode) {
            $mform->addElement('hidden', 'cohortid', 0);
            $mform->setType('cohortid', PARAM_INT);
        } else {
            $cohortoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + $customdata['cohorts'];
            $mform->addElement('select', 'cohortid', get_string('assignment_col_cohort', 'local_monlaututoria'), $cohortoptions);
            $mform->setType('cohortid', PARAM_INT);
        }

        $typeoptions = assignment_type::get_options();
        if ($simplemode) {
            $typeoptions = [assignment_type::PRIMARY => $typeoptions[assignment_type::PRIMARY]];
        }
        $mform->addElement(
            'select',
            'assignmenttype',
            get_string('assignment_col_type', 'local_monlaututoria'),
            $typeoptions
        );
        $mform->setDefault('assignmenttype', assignment_type::PRIMARY);

        // No separate "isprimary" field here on purpose: assignments/create.php
        // derives it directly from "Tipo" (isprimary = assignmenttype===primary)
        // before calling the service. A standalone checkbox next to the type
        // dropdown let a user pick "Tutor principal" without ticking it (or
        // vice versa) and silently create a row invisible to
        // dashboard_service/block_monlaututoria/reassign_primary_tutor(), all
        // of which key off isprimary=1, not assignmenttype alone — a real bug
        // found in manual testing, fixed by removing the redundant input
        // rather than trying to keep 2 independent fields in sync. CSV import
        // keeps its own explicit "isprimary" column unaffected — a bulk
        // import has legitimate reasons (historical rows, tests) to set them
        // independently that a single manual creation does not.
        $mform->addElement('date_selector', 'timestart', get_string('assignment_col_timestart', 'local_monlaututoria'));

        $mform->addElement(
            'date_selector',
            'timeend',
            get_string('assignment_col_timeend', 'local_monlaututoria'),
            ['optional' => true]
        );

        $mform->addElement('textarea', 'note', get_string('assignment_field_note', 'local_monlaututoria'));
        $mform->setType('note', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('assignment_create', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['studentid']) && !empty($data['tutorid']) && $data['studentid'] == $data['tutorid']) {
            $errors['tutorid'] = get_string('error_assignment_self', 'local_monlaututoria');
        }

        if (!empty($data['timeend']) && $data['timeend'] < $data['timestart']) {
            $errors['timeend'] = get_string('error_assignment_dates_invalid', 'local_monlaututoria');
        }

        return $errors;
    }
}
