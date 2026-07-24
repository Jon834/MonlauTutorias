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

    if ($oldversion < 2026072500) {
        // Optional administrative note on assignments, introduced in phase 3B.2.
        $table = new xmldb_table('local_tut_assignment');
        $field = new xmldb_field('note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'externalid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026072500, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026072600) {
        // Coded closing reason on assignments, introduced in phase 3B.3.
        $table = new xmldb_table('local_tut_assignment');
        $field = new xmldb_field('closereason', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'note');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026072600, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026072900) {
        // Cohort-based bulk assignment operations, introduced in phase 3C.1.
        // Preview only: no per-student rows are persisted (see docs/modelo-datos.md).
        $table = new xmldb_table('local_tut_bulkoperation');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('operationuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $table->add_field('operationtype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'cohort_assignment');
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('academicyearid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('primarytutorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cotutorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('mode', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'draft');
        $table->add_field('parametersjson', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('summaryjson', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_operationuuid', XMLDB_KEY_UNIQUE, ['operationuuid']);
        $table->add_index('ix_cohortid', XMLDB_INDEX_NOTUNIQUE, ['cohortid']);
        $table->add_index('ix_academicyearid', XMLDB_INDEX_NOTUNIQUE, ['academicyearid']);
        $table->add_index('ix_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('ix_createdby', XMLDB_INDEX_NOTUNIQUE, ['createdby']);
        $table->add_index('ix_timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026072900, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026073100) {
        // CSV import operations (phase 3D.2) reuse local_tut_bulkoperation
        // alongside cohort-based operations (phase 3C.1), but a CSV import has
        // no single cohort/academic year/tutor — each row can specify its own.
        $table = new xmldb_table('local_tut_bulkoperation');

        // On some DBs (observed on PostgreSQL) changing a column's NOT NULL
        // constraint fails with a dependency error while an index still
        // references that column; drop the two affected indexes first and
        // recreate them once the column changes are done.
        $cohortindex = new xmldb_index('ix_cohortid', XMLDB_INDEX_NOTUNIQUE, ['cohortid']);
        if ($dbman->index_exists($table, $cohortindex)) {
            $dbman->drop_index($table, $cohortindex);
        }
        $academicyearindex = new xmldb_index('ix_academicyearid', XMLDB_INDEX_NOTUNIQUE, ['academicyearid']);
        if ($dbman->index_exists($table, $academicyearindex)) {
            $dbman->drop_index($table, $academicyearindex);
        }

        $field = new xmldb_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'operationtype');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        $field = new xmldb_field('academicyearid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'cohortid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        $field = new xmldb_field('primarytutorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'academicyearid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        $field = new xmldb_field('mode', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'status');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        if (!$dbman->index_exists($table, $cohortindex)) {
            $dbman->add_index($table, $cohortindex);
        }
        if (!$dbman->index_exists($table, $academicyearindex)) {
            $dbman->add_index($table, $academicyearindex);
        }

        upgrade_plugin_savepoint(true, 2026073100, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026080800) {
        // Coded reassignment reason on the new row created by
        // reassign_primary_tutor(), introduced in phase 4.2 so the student
        // file's history tab can show it without querying the event log —
        // previously it only lived in the student_reassigned event's "other"
        // data (see assignment_reassign_reason's class docblock before this
        // phase). Null on every row not created by a reassignment.
        $table = new xmldb_table('local_tut_assignment');
        $field = new xmldb_field('reassignreason', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'closereason');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026080800, 'local', 'monlaututoria');
    }

    return true;
}
