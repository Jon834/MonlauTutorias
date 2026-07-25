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

use local_monlaututoria\domain\entry_attachment_category;

/**
 * Quick tutoring entry registration form (phase 5.2 — "menos de un minuto").
 * Student, tutor and academic year are never fields here: the student comes
 * preselected from the page that opens this form (entries/create.php), the
 * tutor is always the logged-in user, and the academic year is carried as a
 * hidden field — none of that matches "menos de un minuto" if the tutor has
 * to pick them. Only the fields docs/fases/phase-5.md lists for 5.2: fecha,
 * modalidad, motivo, comentario compartido, nota interna, seguimiento.
 *
 * Field-level validation only; entry_service is the sole authority for the
 * business rules (existence/active checks, locked academic year).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_quick_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'studentid');
        $mform->setType('studentid', PARAM_INT);

        $mform->addElement('hidden', 'academicyearid');
        $mform->setType('academicyearid', PARAM_INT);

        // Phase 6.2: carries the follow-up this entry is meant to close, if
        // any — 0 when reached normally (not closing anything).
        $mform->addElement('hidden', 'followupid');
        $mform->setType('followupid', PARAM_INT);

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

        $mform->addElement('textarea', 'contentvisible', get_string('entry_field_contentvisible', 'local_monlaututoria'));
        $mform->setType('contentvisible', PARAM_TEXT);
        $mform->addRule('contentvisible', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'noteinternal', get_string('entry_field_noteinternal', 'local_monlaututoria'));
        $mform->setType('noteinternal', PARAM_TEXT);
        // "Debe quedar claro que solo lo verá el tutor y coordinadores, no el
        // alumno" — a real point of confusion in manual testing, since
        // nothing next to the field said so before. The hard floor itself
        // was already correct (entry_service::mask_content() never shows
        // noteinternal to the student, see its docblock); this only makes
        // that rule visible to whoever is filling in the form.
        $mform->addHelpButton('noteinternal', 'entry_field_noteinternal', 'local_monlaututoria');

        $mform->addElement(
            'date_selector',
            'nextfollowupdate',
            get_string('entry_field_nextfollowupdate', 'local_monlaututoria'),
            ['optional' => true]
        );

        // Optional, only when the page decided the current user is entitled
        // to attach files at all (same editanyentry/editownentry rule
        // entries/attachments.php already enforces — see entries/create.php).
        // Uploading here at creation time saves a separate trip to that page
        // right afterwards, without duplicating its access rule.
        if (!empty($customdata['canupload'])) {
            $mform->addElement(
                'select',
                'attachmentcategory',
                get_string('entry_attachment_category', 'local_monlaututoria'),
                entry_attachment_category::get_options()
            );
            $mform->setType('attachmentcategory', PARAM_ALPHA);

            $mform->addElement(
                'filemanager',
                'attachments',
                get_string('entry_attachment_files', 'local_monlaututoria'),
                null,
                ['subdirs' => 0, 'maxfiles' => 10, 'accepted_types' => '*']
            );
        }

        $this->add_action_buttons(true, get_string('entry_register', 'local_monlaututoria'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['nextfollowupdate']) && $data['nextfollowupdate'] < $data['entrydate']) {
            $errors['nextfollowupdate'] = get_string('error_entry_followup_before_entrydate', 'local_monlaututoria');
        }

        return $errors;
    }
}
