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

use local_monlaututoria\repository\academic_year_repository;

/**
 * Create/edit form for academic years. Contains no business logic beyond
 * client/server field validation; academic_year_service enforces the
 * authoritative rules.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class academic_year_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('academicyear_name', 'local_monlaututoria'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('academicyear_shortname', 'local_monlaututoria'));
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('academicyear_startdate', 'local_monlaututoria'));
        $mform->addElement('date_selector', 'enddate', get_string('academicyear_enddate', 'local_monlaututoria'));

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

        if ($data['enddate'] <= $data['startdate']) {
            $errors['enddate'] = get_string('error_enddate_before_startdate', 'local_monlaututoria');
        }

        $repository = new academic_year_repository();
        $excludeid = !empty($data['id']) ? (int) $data['id'] : null;
        if ($repository->shortname_exists($data['shortname'], $excludeid)) {
            $errors['shortname'] = get_string('error_shortname_duplicate', 'local_monlaututoria');
        }

        return $errors;
    }
}
