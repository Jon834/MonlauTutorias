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

/**
 * Adds the local_monlaututoria pages to Site administration.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig
    || has_capability('local/monlaututoria:viewconfiguration', context_system::instance())
    || has_any_capability(
        ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents'],
        context_system::instance()
    )) {
    $ADMIN->add('localplugins', new admin_category(
        'local_monlaututoria',
        get_string('pluginname', 'local_monlaututoria')
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_academicyears',
        get_string('academicyears', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/academicyears.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:manageacademicyears']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_reasons',
        get_string('reasons', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/reasons.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:managecatalogues']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_modalities',
        get_string('modalities', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/modalities.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:managecatalogues']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_assignments',
        get_string('assignments', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/assignments/index.php'),
        ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_import',
        get_string('csv_import_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/assignments/import.php'),
        ['local/monlaututoria:importassignments']
    ));
}

// This plugin has no admin_settingpage of its own, only external pages above.
$settings = null;
