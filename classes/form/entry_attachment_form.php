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
 * Upload form for tutoring entry attachments (phase 5.6). One document
 * category applies to the whole batch of files uploaded in a single
 * submission — choosing a category per individual file would need extra
 * JavaScript this project has no other precedent for, and the phase does
 * not ask for that level of granularity.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'select',
            'category',
            get_string('entry_attachment_category', 'local_monlaututoria'),
            entry_attachment_category::get_options()
        );
        $mform->setType('category', PARAM_ALPHA);

        $mform->addElement(
            'filemanager',
            'attachments',
            get_string('entry_attachment_files', 'local_monlaututoria'),
            null,
            [
                'subdirs'        => 0,
                'maxfiles'       => 10,
                'accepted_types' => '*',
            ]
        );

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('entry_attachment_upload', 'local_monlaututoria'));
    }
}
