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
 * Second step of the cohort-based bulk assignment flow: confirms and applies
 * a previewed operation. Carries only the operationuuid — unlike CSV
 * import's equivalent form, there is no file/delimiter/encoding to re-read,
 * since cohort_assignment_apply_service recomputes everything it needs
 * straight from the stored local_tut_bulkoperation row (see
 * cohort_assignment_preview_service::command_from_operation()).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'applyoperationuuid');
        $mform->setType('applyoperationuuid', PARAM_ALPHANUMEXT);

        $mform->addElement(
            'advcheckbox', 'confirmapply', '', get_string('cohort_assignment_apply_confirm_checkbox', 'local_monlaututoria')
        );

        $this->add_action_buttons(true, get_string('cohort_assignment_apply_button', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['confirmapply'])) {
            $errors['confirmapply'] = get_string('error_cohort_apply_not_confirmed', 'local_monlaututoria');
        }

        return $errors;
    }
}
