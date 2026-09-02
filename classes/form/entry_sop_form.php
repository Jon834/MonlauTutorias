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
 * SOP tutoring entry registration form (fase 14). Same base fields as the
 * quick form MINUS "comentario compartido con el alumno" (a SOP entry is
 * never visible to the student), PLUS "Recomendaciones SOP" and an
 * attachments block with the two SOP document categories.
 *
 * Student, tutor and academic year are never fields here — same reasoning as
 * entry_quick_form: the student is preselected by entries/create_sop.php, the
 * tutor is the logged-in SOP orientation tutor, the academic year is hidden.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_sop_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'studentid');
        $mform->setType('studentid', PARAM_INT);

        $mform->addElement('hidden', 'academicyearid');
        $mform->setType('academicyearid', PARAM_INT);

        $mform->addElement('date_selector', 'entrydate', get_string('entry_field_entrydate', 'local_monlaututoria'));
        $mform->setDefault('entrydate', time());

        $mform->addElement(
            'select',
            'modalityid',
            get_string('entry_field_modality', 'local_monlaututoria'),
            [0 => get_string('choosedots')] + $customdata['modalities']
        );
        $mform->setType('modalityid', PARAM_INT);

        $mform->addElement(
            'select',
            'reasonid',
            get_string('entry_field_reason', 'local_monlaututoria'),
            [0 => get_string('choosedots')] + $customdata['reasons']
        );
        $mform->setType('reasonid', PARAM_INT);
        $mform->addRule('reasonid', get_string('required'), 'required', null, 'client');

        // Maps to local_tut_entry.noteinternal — a SOP entry has no "shared"
        // tier at all, so this is simply "the notes".
        $mform->addElement('textarea', 'noteinternal', get_string('entry_field_sopobservations', 'local_monlaututoria'));
        $mform->setType('noteinternal', PARAM_TEXT);

        $mform->addElement('textarea', 'recommendationsop', get_string('entry_field_recommendationsop', 'local_monlaututoria'));
        $mform->setType('recommendationsop', PARAM_TEXT);

        // Two independent upload boxes: what the student/family provided vs.
        // what the orientator produced after studying the case.
        $mform->addElement(
            'filemanager',
            'reportfiles',
            get_string('entryattachmentcategory_sop_report', 'local_monlaututoria'),
            null,
            ['subdirs' => 0, 'maxfiles' => 10, 'accepted_types' => '*']
        );

        $mform->addElement(
            'filemanager',
            'recommendationfiles',
            get_string('entryattachmentcategory_sop_recommendation', 'local_monlaututoria'),
            null,
            ['subdirs' => 0, 'maxfiles' => 10, 'accepted_types' => '*']
        );

        $this->add_action_buttons(true, get_string('entry_sop_register', 'local_monlaututoria'));
    }
}
