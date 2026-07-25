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

use local_monlaututoria\domain\entry_participant_type;
use local_monlaututoria\domain\entry_attachment_category;

/**
 * Full tutoring entry registration form (phase 5.3): everything the quick
 * form (phase 5.2) deliberately left out — multiple related reasons,
 * internal/external participants, and the restricted note. Optionally also
 * lets the user attach files at creation time (same as entry_quick_form),
 * gated by customdata['canupload'] — see entries/create_full.php.
 *
 * The restricted note element is only added to the form at all when the
 * caller passes customdata['showrestricted'] = true (resolved by the page
 * from local/monlaututoria:viewrestrictednotes) — never rendered-then-hidden
 * by CSS/JS, per the project's security rule: unauthorised content must never
 * reach the browser in the first place.
 *
 * Each participant row lets the user fill in an internal user OR an external
 * name (never both, never neither, for a row to count) — entry_service is
 * still the authority validating that invariant; this form only skips rows
 * left completely empty.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_full_form extends \moodleform {

    /** @var int how many participant rows are shown by default */
    private const INITIAL_PARTICIPANT_ROWS = 2;

    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'studentid');
        $mform->setType('studentid', PARAM_INT);

        $mform->addElement('hidden', 'academicyearid');
        $mform->setType('academicyearid', PARAM_INT);

        // Phase 6.2: same as entry_quick_form — carries the follow-up this
        // entry is meant to close, if any.
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
            'reasonids',
            get_string('entry_field_reasons', 'local_monlaututoria'),
            $customdata['reasons'],
            ['multiple' => true]
        );
        $mform->addRule('reasonids', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'contentvisible', get_string('entry_field_contentvisible', 'local_monlaututoria'));
        $mform->setType('contentvisible', PARAM_TEXT);
        $mform->addRule('contentvisible', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'noteinternal', get_string('entry_field_noteinternal', 'local_monlaututoria'));
        $mform->setType('noteinternal', PARAM_TEXT);
        $mform->addHelpButton('noteinternal', 'entry_field_noteinternal', 'local_monlaututoria');

        if (!empty($customdata['showrestricted'])) {
            $mform->addElement('textarea', 'noterestricted', get_string('entry_field_noterestricted', 'local_monlaututoria'));
            $mform->setType('noterestricted', PARAM_TEXT);
        }

        $mform->addElement(
            'date_selector',
            'nextfollowupdate',
            get_string('entry_field_nextfollowupdate', 'local_monlaututoria'),
            ['optional' => true]
        );

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

        $mform->addElement('header', 'participantsheader', get_string('entry_participants_header', 'local_monlaututoria'));
        $mform->setExpanded('participantsheader');

        $userselectoroptions = [
            'ajax'     => 'core_user/form_user_selector',
            'multiple' => false,
            'valuehtmlcallback' => function ($value) {
                $user = \core_user::get_user((int) $value);

                return $user ? fullname($user) : '';
            },
        ];

        $repeatarray = [];
        $repeatarray[] = $mform->createElement(
            'select',
            'participanttype',
            get_string('entry_field_participanttype', 'local_monlaututoria'),
            entry_participant_type::get_options()
        );
        $repeatarray[] = $mform->createElement(
            'autocomplete',
            'participantuserid',
            get_string('entry_field_participantuser', 'local_monlaututoria'),
            [],
            $userselectoroptions
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'participantexternalname',
            get_string('entry_field_participantexternalname', 'local_monlaututoria')
        );

        $repeatoptions = [
            'participantuserid' => ['type' => PARAM_INT],
            'participantexternalname' => ['type' => PARAM_TEXT],
        ];

        $this->repeat_elements(
            $repeatarray,
            self::INITIAL_PARTICIPANT_ROWS,
            $repeatoptions,
            'participant_repeats',
            'participant_add_fields',
            1,
            get_string('entry_participant_addmore', 'local_monlaututoria'),
            true
        );

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

        $repeats = (int) ($data['participant_repeats'] ?? 0);
        for ($i = 0; $i < $repeats; $i++) {
            $hasuser = !empty($data['participantuserid'][$i]);
            $hasexternal = trim((string) ($data['participantexternalname'][$i] ?? '')) !== '';
            if ($hasuser && $hasexternal) {
                $errors["participantexternalname[$i]"] = get_string('error_entry_participant_identity_invalid', 'local_monlaututoria');
            }
        }

        return $errors;
    }
}
