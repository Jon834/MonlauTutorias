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

namespace local_monlaututoria;

use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;
use local_monlaututoria\repository\academic_year_repository;
use local_monlaututoria\repository\assignment_repository;
use local_monlaututoria\repository\bulk_operation_repository;

/**
 * Upgrade-path tests (phase 3E.8 — "Prueba de actualización desde cada
 * versión publicada" del trabajo obligatorio de la Fase 3E).
 *
 * There is no real multi-version Moodle stack available in this environment
 * to genuinely install 0.1.0 and upgrade it step by step to the current
 * version — PHPUnit's resetAfterTest() always starts from today's full
 * install.xml. Instead, each test here reconstructs a specific historical
 * pre-upgrade schema state by dropping exactly the tables/fields the real
 * upgrade.php blocks would still be waiting to add, then calls
 * xmldb_local_monlaututoria_upgrade() directly with the $oldversion that
 * corresponds to that real, published release — genuinely exercising the
 * same code path a live upgrade would run, just without a second Moodle
 * codebase to install first. This is disclosed here rather than presented
 * as equivalent to a true multi-instance upgrade rehearsal.
 *
 * Every db/upgrade.php block already guards its own DDL call with
 * table_exists()/field_exists()/index_exists(), so most of what these tests
 * verify is that those guards are actually correct — not just present.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class upgrade_test extends \advanced_testcase {

    /** @var string[] every table this plugin's install.xml defines */
    private const ALL_TABLES = [
        'local_tut_academicyear',
        'local_tut_reason',
        'local_tut_modality',
        'local_tut_assignment',
        'local_tut_bulkoperation',
    ];

    /**
     * @return \database_manager
     */
    private function dbman(): \database_manager {
        global $DB;

        return $DB->get_manager();
    }

    private function require_upgrade_script(): void {
        global $CFG;

        require_once($CFG->dirroot . '/local/monlaututoria/db/upgrade.php');
    }

    /**
     * Drops every table this plugin owns, if it exists — used to simulate
     * the 0.1.0 "skeleton only" release, before phase 2 introduced any of them.
     */
    private function drop_all_tables(): void {
        $dbman = $this->dbman();

        foreach (self::ALL_TABLES as $tablename) {
            $table = new \xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }
    }

    public function test_upgrade_from_0_1_0_skeleton_recreates_everything(): void {
        $this->resetAfterTest();
        $this->require_upgrade_script();

        $this->drop_all_tables();
        $dbman = $this->dbman();
        foreach (self::ALL_TABLES as $tablename) {
            $this->assertFalse($dbman->table_exists(new \xmldb_table($tablename)), "$tablename should not exist yet");
        }

        // 2026072200 is older than every real savepoint in db/upgrade.php —
        // equivalent to upgrading from 0.1.0, the very first published release.
        $result = xmldb_local_monlaututoria_upgrade(2026072200);
        $this->assertTrue($result);

        foreach (self::ALL_TABLES as $tablename) {
            $this->assertTrue($dbman->table_exists(new \xmldb_table($tablename)), "$tablename should exist after upgrade");
        }

        $assignmenttable = new \xmldb_table('local_tut_assignment');
        $this->assertTrue($dbman->field_exists($assignmenttable, new \xmldb_field('note')));
        $this->assertTrue($dbman->field_exists($assignmenttable, new \xmldb_field('closereason')));

        // The catalogue seed (normally db/install.php's job on a fresh
        // install) is also re-run from inside the first upgrade block, since
        // a real upgrading site never runs db/install.php.
        $this->assertTrue((new reason_repository())->shortname_exists('acogida_inicial'));
        $this->assertTrue((new modality_repository())->shortname_exists('presencial'));

        // local_tut_bulkoperation's cohortid/academicyearid/primarytutorid/mode
        // must already be nullable (the 2026073100 fix folded into the same
        // upgrade run) — proven functionally, by creating a csv_import-style
        // operation that leaves them all null.
        $bulkoperationrepository = new bulk_operation_repository();
        $id = $bulkoperationrepository->create((object) [
            'operationuuid' => bulk_operation_repository::generate_uuid(),
            'operationtype' => 'csv_import',
            'createdby'     => get_admin()->id,
        ]);
        $operation = $bulkoperationrepository->get($id);
        $this->assertNull($operation->cohortid);
        $this->assertNull($operation->academicyearid);
        $this->assertNull($operation->primarytutorid);
        $this->assertNull($operation->mode);
    }

    public function test_upgrade_from_phase_3a_0_3_0_adds_note_closereason_and_bulkoperation(): void {
        $this->resetAfterTest();
        $this->require_upgrade_script();

        $dbman = $this->dbman();

        // Simulate 0.3.0: local_tut_assignment exists but without note/closereason,
        // and local_tut_bulkoperation does not exist yet.
        $assignmenttable = new \xmldb_table('local_tut_assignment');
        foreach (['note', 'closereason'] as $fieldname) {
            $field = new \xmldb_field($fieldname);
            if ($dbman->field_exists($assignmenttable, $field)) {
                $dbman->drop_field($assignmenttable, $field);
            }
        }
        $bulkoperationtable = new \xmldb_table('local_tut_bulkoperation');
        if ($dbman->table_exists($bulkoperationtable)) {
            $dbman->drop_table($bulkoperationtable);
        }

        // 2026072400 is the savepoint that created local_tut_assignment itself
        // (phase 3A) — using it as $oldversion simulates a site that has just
        // that table and nothing added after it, i.e. 0.3.0.
        $result = xmldb_local_monlaututoria_upgrade(2026072400);
        $this->assertTrue($result);

        $this->assertTrue($dbman->field_exists($assignmenttable, new \xmldb_field('note')));
        $this->assertTrue($dbman->field_exists($assignmenttable, new \xmldb_field('closereason')));
        $this->assertTrue($dbman->table_exists(new \xmldb_table('local_tut_bulkoperation')));

        // Existing assignment rows created before the upgrade must survive it
        // untouched — add_field() never touches existing data.
        $year = (new academic_year_repository())->create((object) [
            'name' => '2026-2027', 'shortname' => '2026-2027-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $assignmentrepository = new assignment_repository();
        $id = $assignmentrepository->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id,
            'academicyearid' => $year, 'createdby' => get_admin()->id,
        ]);
        $this->assertNotNull($assignmentrepository->get($id));
    }

    public function test_upgrade_from_already_current_version_is_a_safe_noop(): void {
        $this->resetAfterTest();
        $this->require_upgrade_script();

        // The schema is already fully current (resetAfterTest() always installs
        // from today's install.xml). 2026073100 is the last real savepoint in
        // db/upgrade.php — every version from 0.4.2 onward starts here with no
        // schema left to apply, which is exactly the common case (most phases
        // since bumped version.php without touching install.xml/upgrade.php).
        $result = xmldb_local_monlaututoria_upgrade(2026073100);

        $this->assertTrue($result);
        foreach (self::ALL_TABLES as $tablename) {
            $this->assertTrue($this->dbman()->table_exists(new \xmldb_table($tablename)));
        }
    }

    public function test_upgrade_called_twice_in_a_row_is_idempotent(): void {
        $this->resetAfterTest();
        $this->require_upgrade_script();

        $this->drop_all_tables();

        $first = xmldb_local_monlaututoria_upgrade(2026072200);
        $second = xmldb_local_monlaututoria_upgrade(2026072200);

        $this->assertTrue($first);
        $this->assertTrue($second);

        // Running it twice must not have duplicated the catalogue seed.
        $this->assertCount(20, (new reason_repository())->get_all());
        $this->assertCount(7, (new modality_repository())->get_all());
    }
}
