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

    if ($oldversion < 2026081100) {
        // Phase 5.1: tutoring entries — the longitudinal record itself, its
        // motivos relacionados (many-to-many with local_tut_reason), its
        // participants (internal Moodle users and external people), and a
        // versions table with no writer yet (see its own comment below).
        $table = new xmldb_table('local_tut_entry');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('tutorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('academicyearid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('entrydate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('modalityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('contentvisible', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('noteinternal', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('noterestricted', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'active');
        $table->add_field('nextfollowupdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_student_academicyear', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'academicyearid']);
        $table->add_index('ix_tutorid', XMLDB_INDEX_NOTUNIQUE, ['tutorid']);
        $table->add_index('ix_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('ix_entrydate', XMLDB_INDEX_NOTUNIQUE, ['entrydate']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_entryreason');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reasonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_entry_reason', XMLDB_KEY_UNIQUE, ['entryid', 'reasonid']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_entryparticipant');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('participanttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('externalname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Created empty here — no repository/service wrote to it until phase
        // 5.5's editing/annulment service — because docs/fases/phase-5.md
        // groups "tablas de ... versiones" into 5.1's own scope alongside
        // the other 3 tables.
        $table = new xmldb_table('local_tut_entryversion');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('versionnumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('snapshotjson', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('changereason', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_entry_version', XMLDB_KEY_UNIQUE, ['entryid', 'versionnumber']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026081100, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026081600) {
        // Phase 5.6: document-category metadata for tutoring entry
        // attachments. The files themselves live in Moodle's File API
        // (component=local_monlaututoria, filearea=entryattachment,
        // itemid=entryid) — nothing to migrate there, File API tables are
        // core and already exist. This table only adds the category/
        // description File API has no native field for.
        $table = new xmldb_table('local_tut_entryattachment');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pathnamehash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('category', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_pathnamehash', XMLDB_KEY_UNIQUE, ['pathnamehash']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026081600, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026081800) {
        // Phase 6.1: agreements, follow-ups and referrals — all 3 tables
        // created here at once (same approach as phase 5.1's 4 tutoring
        // tables), even though only agreements gets a real feature this
        // increment. local_tut_followup/local_tut_referral stay empty until
        // 6.2/6.4 wire their own repository/service, same documented gap as
        // local_tut_entryversion was between 5.1 and 5.5.
        $table = new xmldb_table('local_tut_agreement');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('responsibletype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('responsibleuserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('responsibleexternalname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('duedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('visibletostudent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        $table->add_index('ix_student_status', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'status']);
        $table->add_index('ix_duedate', XMLDB_INDEX_NOTUNIQUE, ['duedate']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_followup');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('closingentryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('duedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('priority', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'medium');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        $table->add_index('ix_closingentryid', XMLDB_INDEX_NOTUNIQUE, ['closingentryid']);
        $table->add_index('ix_student_status', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'status']);
        $table->add_index('ix_duedate', XMLDB_INDEX_NOTUNIQUE, ['duedate']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_tut_referral');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('destination', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('priority', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'medium');
        $table->add_field('assignedto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('resolution', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('ix_entryid', XMLDB_INDEX_NOTUNIQUE, ['entryid']);
        $table->add_index('ix_studentid', XMLDB_INDEX_NOTUNIQUE, ['studentid']);
        $table->add_index('ix_status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('ix_assignedto', XMLDB_INDEX_NOTUNIQUE, ['assignedto']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026081800, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026082400) {
        // Phase 7.1 adds the tutor dashboard page/service only - no schema changes.
        upgrade_plugin_savepoint(true, 2026082400, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026082700) {
        // Phase 7.2-7.5 complete the tutor dashboard and add the companion block - no schema changes.
        upgrade_plugin_savepoint(true, 2026082700, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026090100) {
        // Phase 8.1: explicit cohort-based coordination scopes.
        $table = new xmldb_table('local_tut_coordscope');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_user_cohort', XMLDB_KEY_UNIQUE, ['userid', 'cohortid']);
        $table->add_index('ix_userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('ix_cohortid', XMLDB_INDEX_NOTUNIQUE, ['cohortid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026090100, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026090500) {
        // Phase 9.1-9.5: notification outbox and deduplication log.
        $table = new xmldb_table('local_tut_notification');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notificationtype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('recipientid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('actorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('entitytype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('entityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('digestkey', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, '');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('attempts', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lasterror', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_dispatch', XMLDB_KEY_UNIQUE, ['notificationtype', 'recipientid', 'entitytype', 'entityid', 'digestkey']);
        $table->add_index('ix_recipient_status', XMLDB_INDEX_NOTUNIQUE, ['recipientid', 'status']);
        $table->add_index('ix_actorid', XMLDB_INDEX_NOTUNIQUE, ['actorid']);
        $table->add_index('ix_timemodified', XMLDB_INDEX_NOTUNIQUE, ['timemodified']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026090500, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026090600) {
        // Data repair, no schema change: until this version,
        // assignment_service::create() only validated one direction of the
        // isprimary/assignmenttype invariant (isprimary=true requires
        // assignmenttype=primary), never the other way round. The manual
        // assignment creation form and CSV import both accept "Tipo" and
        // "Marcar como tutor principal" as independent inputs, so a
        // primary-type row with isprimary=0 could be created — invisible to
        // dashboard_service/block_monlaututoria/reassign_primary_tutor(),
        // all of which key off isprimary=1, not assignmenttype alone (a
        // tutor could see their own assignment listed as "Tutor principal /
        // Activa" while their dashboard and the block showed 0 assigned
        // students, with no "Reasignar" action available either). The
        // validation is now bidirectional going forward (see
        // assignment_service::validate_isprimary_type_match()); this backfills
        // any row already created before that fix.
        $DB->set_field('local_tut_assignment', 'isprimary', 1, ['assignmenttype' => 'primary', 'isprimary' => 0]);

        upgrade_plugin_savepoint(true, 2026090600, 'local', 'monlaututoria');
    }

    if ($oldversion < 2026091400) {
        // Real-use feedback: an admin has no way to curate which Moodle
        // cohorts are relevant to this plugin at all (e.g. hiding staff
        // cohorts) — every cohort dropdown across the plugin, and the
        // coordination scope a viewallassignments user gets by default,
        // currently offers literally every cohort on the site. An EMPTY
        // table means "unrestricted" (cohort_visibility_service falls back
        // to every cohort) so upgrading never silently hides anything until
        // an admin explicitly visits the new screen and saves a subset.
        $table = new xmldb_table('local_tut_enabledcohort');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ku_cohortid', XMLDB_KEY_UNIQUE, ['cohortid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026091400, 'local', 'monlaututoria');
    }

    return true;
}

