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
 * Picks the cohort, academic year, tutor(s) and sync mode for a cohort-based
 * bulk assignment preview (assignments/cohort_create.php). Only field-level
 * validation here — cohort_assignment_preview_service is the sole authority
 * for the business rules (invalid/suspended users, locked academic year,
 * same tutor/cotutor).
 *
 * customdata['modeoptions'] is resolved by the page from the current user's
 * capabilities, not fixed here: "add_and_close_missing" needs
 * manageassignments and "replace_primary" needs reassignstudents/
 * manageassignments — same page-resolves-capability, service-trusts-the-flag
 * pattern already used for canoverridelock throughout this plugin. A user
 * without either extra capability only ever sees "add_only".
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_create_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $userselectoroptions = [
            'ajax'     => 'core_user/form_user_selector',
            'multiple' => false,
            'valuehtmlcallback' => function ($value) {
                $user = \core_user::get_user((int) $value);

                return $user ? fullname($user) : '';
            },
        ];

        $mform->addElement(
            'select', 'cohortid', get_string('cohort_assignment_field_cohort', 'local_monlaututoria'), $customdata['cohorts']
        );
        $mform->setType('cohortid', PARAM_INT);
        $mform->addRule('cohortid', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'select', 'academicyearid', get_string('assignment_col_academicyear', 'local_monlaututoria'), $customdata['academicyears']
        );
        $mform->setType('academicyearid', PARAM_INT);
        $mform->addRule('academicyearid', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'autocomplete', 'primarytutorid', get_string('cohort_assignment_field_primarytutor', 'local_monlaututoria'), [], $userselectoroptions
        );
        $mform->setType('primarytutorid', PARAM_INT);
        $mform->addRule('primarytutorid', get_string('required'), 'required', null, 'client');

        // Fase 13 — no co-tutor in simple mode; the service treats a missing
        // cotutorid exactly as "none".
        if (!\local_monlaututoria\feature::simple_mode()) {
            $mform->addElement(
                'autocomplete', 'cotutorid', get_string('cohort_assignment_field_cotutor', 'local_monlaututoria'), [], $userselectoroptions
            );
            $mform->setType('cotutorid', PARAM_INT);
        }

        $mform->addElement(
            'select', 'mode', get_string('cohort_assignment_field_mode', 'local_monlaututoria'), $customdata['modeoptions']
        );
        $mform->addHelpButton('mode', 'cohort_assignment_field_mode', 'local_monlaututoria');

        $mform->addElement(
            'date_selector', 'timestart', get_string('assignment_col_timestart', 'local_monlaututoria'), ['optional' => true]
        );
        $mform->addElement(
            'date_selector', 'timeend', get_string('assignment_col_timeend', 'local_monlaututoria'), ['optional' => true]
        );

        $mform->addElement(
            'advcheckbox', 'includesuspended', get_string('cohort_assignment_field_includesuspended', 'local_monlaututoria')
        );
        $mform->addElement(
            'advcheckbox', 'allowsuspendedtutor', get_string('cohort_assignment_field_allowsuspendedtutor', 'local_monlaututoria')
        );

        $this->add_action_buttons(true, get_string('cohort_assignment_preview_button', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['cotutorid']) && !empty($data['primarytutorid']) && $data['cotutorid'] == $data['primarytutorid']) {
            $errors['cotutorid'] = get_string('error_cohort_same_tutor_cotutor', 'local_monlaututoria');
        }

        if (!empty($data['timestart']) && !empty($data['timeend']) && $data['timeend'] < $data['timestart']) {
            $errors['timeend'] = get_string('error_assignment_dates_invalid', 'local_monlaututoria');
        }

        return $errors;
    }
}
