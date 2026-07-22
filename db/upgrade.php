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
 * Upgrade steps for local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/upgradelib.php');

/**
 * Applies incremental schema changes for local_monlaututoria.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_monlaututoria_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026072300) {
        // These 3 tables did not exist in 0.1.0 (the skeleton-only release).
        // On a fresh install they are already created from install.xml, so every
        // create_table() call below is guarded by table_exists().
        $table = new xmldb_table('local_tut_academicyear');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('locked', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_shortname', XMLDB_KEY_UNIQUE, ['shortname']);
        $table->add_index('ix_active', XMLDB_INDEX_NOTUNIQUE, ['active']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_reason');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('requiresfollowup', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('defaultvisibility', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_shortname', XMLDB_KEY_UNIQUE, ['shortname']);
        $table->add_index('ix_active_sortorder', XMLDB_INDEX_NOTUNIQUE, ['active', 'sortorder']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_modality');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_shortname', XMLDB_KEY_UNIQUE, ['shortname']);
        $table->add_index('ix_active_sortorder', XMLDB_INDEX_NOTUNIQUE, ['active', 'sortorder']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        local_monlaututoria_seed_catalogues();

        upgrade_plugin_savepoint(true, 2026072300, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026072400) {
        // Tutor-student assignments, introduced in phase 3A. On a fresh install
        // this table is already created from install.xml, so create_table() is
        // guarded by table_exists() as with the phase 2 tables above.
        $table = new xmldb_table('local_tut_assignment');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('tutorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('academicyearid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignmenttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'primary');
        $table->add_field('isprimary', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active');
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeend', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('source', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'manual');
        $table->add_field('externalid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_academicyearid', XMLDB_INDEX_NOTUNIQUE, ['academicyearid']);
        $table->add_index('ix_cohortid', XMLDB_INDEX_NOTUNIQUE, ['cohortid']);
        $table->add_index('ix_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('ix_student_academicyear', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'academicyearid']);
        $table->add_index('ix_tutor_academicyear_status', XMLDB_INDEX_NOTUNIQUE, ['tutorid', 'academicyearid', 'status']);
        $table->add_index('ix_student_tutor_academicyear', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'tutorid', 'academicyearid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026072400, 'local', 'monlaututoria');
    }

    return true;
}
