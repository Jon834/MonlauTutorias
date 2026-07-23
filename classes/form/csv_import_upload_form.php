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
 * First step of the CSV assignment import flow: upload the file (via a
 * standard Moodle draft file area, never persisted permanently by this
 * plugin) and choose delimiter/encoding. assignments/import.php reads the
 * uploaded content and hands it to csv_import_preview_service — this form
 * has no business logic of its own.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_upload_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'filepicker',
            'csvfile',
            get_string('csv_field_file', 'local_monlaututoria'),
            null,
            ['accepted_types' => ['.csv', '.txt'], 'maxbytes' => 2 * 1024 * 1024]
        );
        $mform->addRule('csvfile', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'delimiter', get_string('csv_field_delimiter', 'local_monlaututoria'), [
            ','  => get_string('csv_delimiter_comma', 'local_monlaututoria'),
            ';'  => get_string('csv_delimiter_semicolon', 'local_monlaututoria'),
            "\t" => get_string('csv_delimiter_tab', 'local_monlaututoria'),
        ]);

        $mform->addElement('select', 'encoding', get_string('csv_field_encoding', 'local_monlaututoria'), [
            'UTF-8'         => 'UTF-8',
            'ISO-8859-1'    => 'ISO-8859-1',
            'Windows-1252'  => 'Windows-1252',
        ]);

        $this->add_action_buttons(true, get_string('csv_upload_preview', 'local_monlaututoria'));
    }
}
