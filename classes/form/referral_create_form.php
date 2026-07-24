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

use local_monlaututoria\domain\referral_destination;
use local_monlaututoria\domain\priority_level;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Referral creation form (phase 6.4). The "reason" textarea deliberately has
 * no default value bound to it anywhere in this form or the page that uses
 * it — "no duplicar contenido sensible" (docs/fases/phase-6.md) means the
 * motive must always be freshly authored, never pre-filled from the origin
 * entry's noteinternal/noterestricted.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class referral_create_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'entryid');
        $mform->setType('entryid', PARAM_INT);

        $mform->addElement(
            'select',
            'destination',
            get_string('referral_field_destination', 'local_monlaututoria'),
            referral_destination::get_options()
        );

        $mform->addElement('textarea', 'reason', get_string('referral_field_reason', 'local_monlaututoria'));
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'select',
            'priority',
            get_string('followup_field_priority', 'local_monlaututoria'),
            priority_level::get_options()
        );
        $mform->setDefault('priority', priority_level::MEDIUM);

        $this->add_action_buttons(true, get_string('referral_create', 'local_monlaututoria'));
    }
}
