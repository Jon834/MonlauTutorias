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

use local_monlaututoria\domain\csv_import_apply_strategy;

/**
 * Third step of the CSV assignment import flow: confirms and applies a
 * previewed operation (phase 3D.3). Carries the same hidden
 * draftitemid/delimiter/encoding as the exclude form, plus the
 * operationuuid, so the page can re-read the file and re-validate before
 * writing anything — never trusting the preview blindly.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'applyoperationuuid');
        $mform->setType('applyoperationuuid', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'draftitemid');
        $mform->setType('draftitemid', PARAM_INT);

        $mform->addElement('hidden', 'delimiter');
        $mform->setType('delimiter', PARAM_RAW);

        $mform->addElement('hidden', 'encoding');
        $mform->setType('encoding', PARAM_ALPHANUMEXT);

        $mform->addElement('select', 'strategy', get_string('csv_field_strategy', 'local_monlaututoria'), [
            csv_import_apply_strategy::PARTIAL_VALID => get_string('csv_strategy_partial_valid', 'local_monlaututoria'),
            csv_import_apply_strategy::ATOMIC_ALL     => get_string('csv_strategy_atomic_all', 'local_monlaututoria'),
        ]);

        $mform->addElement(
            'advcheckbox',
            'allowreassignconflicts',
            get_string('csv_field_allow_reassign', 'local_monlaututoria')
        );
        $mform->addHelpButton('allowreassignconflicts', 'csv_field_allow_reassign', 'local_monlaututoria');

        $mform->addElement('advcheckbox', 'confirmapply', '', get_string('csv_apply_confirm_checkbox', 'local_monlaututoria'));

        $this->add_action_buttons(true, get_string('csv_apply_button', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['confirmapply'])) {
            $errors['confirmapply'] = get_string('error_csv_apply_not_confirmed', 'local_monlaututoria');
        }

        return $errors;
    }
}
