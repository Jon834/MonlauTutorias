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

use local_monlaututoria\domain\csv_import_row_status;

/**
 * Second step of the CSV assignment import flow: lets the administrator
 * manually exclude specific rows and re-run the preview. Carries the
 * original draftitemid/delimiter/encoding as hidden fields so the page can
 * re-read the same uploaded file and recompute a fresh preview — never
 * trusting a stale row list (see csv_import_preview_service's class
 * docblock). One checkbox per row, built dynamically from the just-computed
 * preview passed in via customdata.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_exclude_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'draftitemid');
        $mform->setType('draftitemid', PARAM_INT);

        $mform->addElement('hidden', 'delimiter');
        $mform->setType('delimiter', PARAM_RAW);

        $mform->addElement('hidden', 'encoding');
        $mform->setType('encoding', PARAM_ALPHANUMEXT);

        foreach ($customdata['rows'] as $row) {
            $name = 'exclude_' . $row->rownumber;
            $mform->addElement(
                'advcheckbox',
                $name,
                get_string('csv_row_label', 'local_monlaututoria', $row->rownumber)
            );
            if ($row->status === csv_import_row_status::EXCLUDED) {
                $mform->setDefault($name, 1);
            }
        }

        $this->add_action_buttons(true, get_string('csv_recalculate_preview', 'local_monlaututoria'));
    }
}
