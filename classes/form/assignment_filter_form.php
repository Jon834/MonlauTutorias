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
use local_monlaututoria\domain\assignment_status;
use local_monlaututoria\domain\assignment_source;

/**
 * Filter form for the assignments listing (assignments/index.php). Submitted
 * via GET so filters stay in the URL (bookmarkable, persisted on pagination).
 * Contains no business logic: filter values are read directly from the URL
 * by the page, this class only renders and lightly validates the fields.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_filter_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $academicyearoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + $customdata['academicyears'];
        $mform->addElement('select', 'academicyearid', get_string('filter_academicyear', 'local_monlaututoria'), $academicyearoptions);
        $mform->setType('academicyearid', PARAM_INT);

        $typeoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + assignment_type::get_options();
        $mform->addElement('select', 'assignmenttype', get_string('filter_assignmenttype', 'local_monlaututoria'), $typeoptions);

        $statusoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + assignment_status::get_options();
        $mform->addElement('select', 'status', get_string('filter_status', 'local_monlaututoria'), $statusoptions);

        $sourceoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + assignment_source::get_options();
        $mform->addElement('select', 'source', get_string('filter_source', 'local_monlaututoria'), $sourceoptions);

        $cohortoptions = [0 => get_string('filter_all', 'local_monlaututoria')] + $customdata['cohorts'];
        $mform->addElement('select', 'cohortid', get_string('filter_cohort', 'local_monlaututoria'), $cohortoptions);
        $mform->setType('cohortid', PARAM_INT);

        // NOTE: 'core_user/form_user_selector' is the AJAX transport this form
        // relies on for a search-as-you-type user picker instead of a full
        // dropdown. Verify against the live Moodle 5.1 instance — if it does
        // not resolve, fall back to core's user_selector_base widget instead.
        $userselectoroptions = [
            'ajax'     => 'core_user/form_user_selector',
            'multiple' => false,
            'valuehtmlcallback' => function ($value) {
                $user = \core_user::get_user((int) $value);

                return $user ? fullname($user) : '';
            },
        ];
        $mform->addElement('autocomplete', 'studentid', get_string('filter_student', 'local_monlaututoria'), [], $userselectoroptions);
        $mform->setType('studentid', PARAM_INT);
        $mform->addElement('autocomplete', 'tutorid', get_string('filter_tutor', 'local_monlaututoria'), [], $userselectoroptions);
        $mform->setType('tutorid', PARAM_INT);

        $mform->addElement('date_selector', 'timestartfrom', get_string('filter_timestartfrom', 'local_monlaututoria'), ['optional' => true]);
        $mform->addElement('date_selector', 'timestartto', get_string('filter_timestartto', 'local_monlaututoria'), ['optional' => true]);
        $mform->addElement('date_selector', 'timeendfrom', get_string('filter_timeendfrom', 'local_monlaututoria'), ['optional' => true]);
        $mform->addElement('date_selector', 'timeendto', get_string('filter_timeendto', 'local_monlaututoria'), ['optional' => true]);

        $this->add_action_buttons(false, get_string('filter_apply', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['timestartfrom']) && !empty($data['timestartto']) && $data['timestartto'] < $data['timestartfrom']) {
            $errors['timestartto'] = get_string('error_assignment_dates_invalid', 'local_monlaututoria');
        }
        if (!empty($data['timeendfrom']) && !empty($data['timeendto']) && $data['timeendto'] < $data['timeendfrom']) {
            $errors['timeendto'] = get_string('error_assignment_dates_invalid', 'local_monlaututoria');
        }

        return $errors;
    }
}
