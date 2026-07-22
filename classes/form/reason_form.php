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

use local_monlaututoria\domain\visibility_level;
use local_monlaututoria\repository\reason_repository;

/**
 * Create/edit form for tutoring reasons.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reason_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('reason_name', 'local_monlaututoria'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('reason_shortname', 'local_monlaututoria'));
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('reason_description', 'local_monlaututoria'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'requiresfollowup', get_string('reason_requiresfollowup', 'local_monlaututoria'));

        $mform->addElement(
            'select',
            'defaultvisibility',
            get_string('reason_defaultvisibility', 'local_monlaututoria'),
            visibility_level::get_options()
        );
        $mform->setDefault('defaultvisibility', visibility_level::INTERNAL);

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

        $repository = new reason_repository();
        $excludeid = !empty($data['id']) ? (int) $data['id'] : null;
        if ($repository->shortname_exists($data['shortname'], $excludeid)) {
            $errors['shortname'] = get_string('error_shortname_duplicate', 'local_monlaututoria');
        }

        return $errors;
    }
}
