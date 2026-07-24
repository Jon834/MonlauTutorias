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

namespace local_monlaututoria\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_monlaututoria.
 *
 * The 3 catalogue tables (academic years, reasons, modalities) hold shared
 * institutional data, not personal data about the requesting user: only the
 * createdby/modifiedby attribution fields reference a user. See
 * reassign_attribution() for why erasure reassigns those fields instead of
 * deleting the catalogue rows.
 *
 * **Retention policy (decided 2026-07-24, phase 3E.6), closing the compliance
 * gap this class previously left open:**
 *
 * - `local_tut_assignment`: studentid/tutorid ARE the personal data, not
 *   incidental attribution — tutoring relationship history is kept
 *   indefinitely (no expiry), but a subject access/erasure request is now
 *   fully honoured. Export returns every row the requesting user appears in,
 *   as student or tutor, with the other party resolved to a readable name.
 *   Erasure never deletes a row (that would destroy the other party's own
 *   history) — it anonymises it instead: studentid/tutorid/createdby/
 *   modifiedby referencing the erased user are reassigned to the Moodle
 *   "no-reply" user (same mechanism reassign_attribution() already uses for
 *   the 3 catalogues), and the free-text `note` on any row the erased user
 *   appears in (as student or tutor) is cleared, since prose notes can name
 *   a person even after their id reference is gone. assignmenttype/
 *   isprimary/status/dates/source/closereason are left untouched — the fact
 *   that some tutoring relationship existed, when, and why it ended, remains
 *   available for institutional history once anonymised.
 * - `local_tut_bulkoperation`: same anonymisation treatment for
 *   primarytutorid/cotutorid/createdby. On top of that, this table now has an
 *   actual retention limit: `cleanup_bulk_operations_task` purges operations
 *   in a terminal status (completed/completed_with_errors/failed/cancelled)
 *   after 90 days — see TERMINAL_TTL_SECONDS there. Abandoned draft/previewed
 *   operations were already purged after 1 day (phase 3D.4); this adds the
 *   missing other half of the policy.
 *
 * The local_monlaututoria/csvimport file area (phase 3D.4) is unaffected by
 * this: it holds the same kind of personal data as local_tut_assignment's
 * studentid/tutorid (whoever a large CSV import's rows name), but only
 * transiently — a file only exists there between csv_import_dispatch_service
 * deferring an import and process_csv_import_task processing it (normally
 * seconds to minutes), and cleanup_bulk_operations_task removes anything left
 * behind. Declared via core_files for completeness but still not wired into
 * export/delete — the file is gone again well before any subject access
 * request could reasonably reach it, and there is nothing meaningful to
 * anonymise in a file that is about to be deleted anyway.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /** @var string[] */
    private const TABLES = ['local_tut_academicyear', 'local_tut_reason', 'local_tut_modality'];

    public static function get_metadata(collection $collection): collection {
        $attribution = [
            'createdby'    => 'privacy:metadata:createdby',
            'modifiedby'   => 'privacy:metadata:modifiedby',
            'timecreated'  => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ];

        $collection->add_database_table('local_tut_academicyear', $attribution + [
            'name'      => 'privacy:metadata:academicyear:name',
            'shortname' => 'privacy:metadata:academicyear:shortname',
        ], 'privacy:metadata:academicyear');

        $collection->add_database_table('local_tut_reason', $attribution + [
            'name'      => 'privacy:metadata:reason:name',
            'shortname' => 'privacy:metadata:reason:shortname',
        ], 'privacy:metadata:reason');

        $collection->add_database_table('local_tut_modality', $attribution + [
            'name'      => 'privacy:metadata:modality:name',
            'shortname' => 'privacy:metadata:modality:shortname',
        ], 'privacy:metadata:modality');

        // Lighter footprint than local_tut_assignment: this table never
        // stores per-student data (see cohort_assignment_preview_service's
        // class docblock) — only attribution (createdby) and the selected
        // tutor(s) as references. Exported and anonymised on erasure (phase
        // 3E.6) like every other table below, plus a 90-day retention limit
        // for finished operations (see the class docblock).
        $collection->add_database_table('local_tut_bulkoperation', [
            'cohortid'       => 'privacy:metadata:bulkoperation:cohortid',
            'academicyearid' => 'privacy:metadata:bulkoperation:academicyearid',
            'primarytutorid' => 'privacy:metadata:bulkoperation:primarytutorid',
            'cotutorid'      => 'privacy:metadata:bulkoperation:cotutorid',
            'mode'           => 'privacy:metadata:bulkoperation:mode',
            'createdby'      => 'privacy:metadata:createdby',
            'timecreated'    => 'privacy:metadata:timecreated',
            'timemodified'   => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:bulkoperation');

        // Exported and anonymised on erasure (phase 3E.6) — see the class
        // docblock for the retention policy this implements.
        $collection->add_database_table('local_tut_assignment', [
            'studentid'      => 'privacy:metadata:assignment:studentid',
            'tutorid'        => 'privacy:metadata:assignment:tutorid',
            'cohortid'       => 'privacy:metadata:assignment:cohortid',
            'academicyearid' => 'privacy:metadata:assignment:academicyearid',
            'assignmenttype' => 'privacy:metadata:assignment:assignmenttype',
            'isprimary'      => 'privacy:metadata:assignment:isprimary',
            'status'         => 'privacy:metadata:assignment:status',
            'timestart'      => 'privacy:metadata:assignment:timestart',
            'timeend'        => 'privacy:metadata:assignment:timeend',
            'source'         => 'privacy:metadata:assignment:source',
            'note'           => 'privacy:metadata:assignment:note',
            'closereason'    => 'privacy:metadata:assignment:closereason',
            'reassignreason' => 'privacy:metadata:assignment:reassignreason',
            'createdby'      => 'privacy:metadata:createdby',
            'modifiedby'     => 'privacy:metadata:modifiedby',
            'timecreated'    => 'privacy:metadata:timecreated',
            'timemodified'   => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:assignment');

        // Transient only — see the class docblock. Not wired into
        // export/delete, same documented reason as local_tut_assignment.
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:csvimportfiles');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();

        $sql = 'SELECT 1 FROM {local_tut_academicyear} WHERE createdby = :ay1 OR modifiedby = :ay2
                UNION
                SELECT 1 FROM {local_tut_reason} WHERE createdby = :r1 OR modifiedby = :r2
                UNION
                SELECT 1 FROM {local_tut_modality} WHERE createdby = :m1 OR modifiedby = :m2
                UNION
                SELECT 1 FROM {local_tut_assignment}
                    WHERE studentid = :as1 OR tutorid = :as2 OR createdby = :as3 OR modifiedby = :as4
                UNION
                SELECT 1 FROM {local_tut_bulkoperation}
                    WHERE createdby = :bo1 OR primarytutorid = :bo2 OR cotutorid = :bo3';
        $params = [
            'ay1' => $userid, 'ay2' => $userid,
            'r1'  => $userid, 'r2'  => $userid,
            'm1'  => $userid, 'm2'  => $userid,
            'as1' => $userid, 'as2' => $userid, 'as3' => $userid, 'as4' => $userid,
            'bo1' => $userid, 'bo2' => $userid, 'bo3' => $userid,
        ];

        if ($DB->record_exists_sql($sql, $params)) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        if ($userlist->get_context()->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        foreach (self::TABLES as $table) {
            $userlist->add_from_sql('createdby', "SELECT createdby FROM {{$table}}", []);
            $userlist->add_from_sql('modifiedby', "SELECT modifiedby FROM {{$table}}", []);
        }

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('tutorid', 'SELECT tutorid FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_assignment}', []);

        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_bulkoperation}', []);
        $userlist->add_from_sql('primarytutorid', 'SELECT primarytutorid FROM {local_tut_bulkoperation}', []);
        $userlist->add_from_sql('cotutorid', 'SELECT cotutorid FROM {local_tut_bulkoperation}', []);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $hassystem = false;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_SYSTEM) {
                $hassystem = true;
            }
        }
        if (!$hassystem) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $data = ['academicyears' => [], 'reasons' => [], 'modalities' => []];
        $tabletokey = [
            'local_tut_academicyear' => 'academicyears',
            'local_tut_reason'       => 'reasons',
            'local_tut_modality'     => 'modalities',
        ];

        foreach ($tabletokey as $table => $key) {
            $records = $DB->get_records_select(
                $table,
                'createdby = :u1 OR modifiedby = :u2',
                ['u1' => $userid, 'u2' => $userid]
            );
            foreach ($records as $record) {
                $data[$key][] = (object) [
                    'name'      => $record->name,
                    'shortname' => $record->shortname,
                    'role'      => ((int) $record->createdby === $userid) ? 'created' : 'modified',
                ];
            }
        }

        $data['assignments'] = self::export_assignments($userid);
        $data['bulkoperations'] = self::export_bulk_operations($userid);

        writer::with_context(\context_system::instance())->export_data(
            [get_string('pluginname', 'local_monlaututoria')],
            (object) $data
        );
    }

    /**
     * @param int $userid
     * @return array
     */
    private static function export_assignments(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_assignment',
            'studentid = :s OR tutorid = :t OR createdby = :c OR modifiedby = :m',
            ['s' => $userid, 't' => $userid, 'c' => $userid, 'm' => $userid]
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->studentid === $userid) {
                $roles[] = 'student';
            }
            if ((int) $record->tutorid === $userid) {
                $roles[] = 'tutor';
            }
            if ((int) $record->createdby === $userid) {
                $roles[] = 'creator';
            }
            if ((int) $record->modifiedby === $userid) {
                $roles[] = 'modifier';
            }

            // The other party in the relationship, resolved to a readable
            // name — a raw id would not be intelligible in an export meant
            // for the data subject to actually read.
            $counterpartid = (int) $record->studentid === $userid ? (int) $record->tutorid : (int) $record->studentid;
            $counterpart = \core_user::get_user($counterpartid);

            $export[] = (object) [
                'yourrole'       => $roles,
                'counterpart'    => $counterpart ? fullname($counterpart) : null,
                'assignmenttype' => $record->assignmenttype,
                'isprimary'      => (bool) $record->isprimary,
                'status'         => $record->status,
                'timestart'      => $record->timestart ? userdate($record->timestart) : null,
                'timeend'        => $record->timeend ? userdate($record->timeend) : null,
                'source'         => $record->source,
                'note'           => $record->note,
                'closereason'    => $record->closereason,
                'timecreated'    => userdate($record->timecreated),
                'timemodified'   => userdate($record->timemodified),
            ];
        }

        return $export;
    }

    /**
     * @param int $userid
     * @return array
     */
    private static function export_bulk_operations(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_bulkoperation',
            'createdby = :c OR primarytutorid = :p OR cotutorid = :co',
            ['c' => $userid, 'p' => $userid, 'co' => $userid]
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->createdby === $userid) {
                $roles[] = 'creator';
            }
            if ((int) $record->primarytutorid === $userid) {
                $roles[] = 'primarytutor';
            }
            if ((int) $record->cotutorid === $userid) {
                $roles[] = 'cotutor';
            }

            $export[] = (object) [
                'yourrole'      => $roles,
                'operationtype' => $record->operationtype,
                'mode'          => $record->mode,
                'status'        => $record->status,
                'timecreated'   => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        self::reassign_all_attribution();
        self::anonymize_all_assignments();
        self::anonymize_all_bulk_operations();
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_SYSTEM) {
                $userid = $contextlist->get_user()->id;
                self::reassign_attribution($userid);
                self::anonymize_assignments($userid);
                self::anonymize_bulk_operations($userid);
            }
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        if ($userlist->get_context()->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            self::reassign_attribution((int) $userid);
            self::anonymize_assignments((int) $userid);
            self::anonymize_bulk_operations((int) $userid);
        }
    }

    /**
     * @param int $userid
     */
    private static function reassign_attribution(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        foreach (self::TABLES as $table) {
            $DB->set_field($table, 'createdby', $noreply, ['createdby' => $userid]);
            $DB->set_field($table, 'modifiedby', $noreply, ['modifiedby' => $userid]);
        }
    }

    private static function reassign_all_attribution(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        foreach (self::TABLES as $table) {
            $DB->set_field($table, 'createdby', $noreply, []);
            $DB->set_field($table, 'modifiedby', $noreply, []);
        }
    }

    /**
     * Anonymises every local_tut_assignment row where $userid appears as
     * student, tutor, creator or modifier — never deletes a row, since that
     * would also destroy the other party's own history. The row ids are
     * collected before reassigning studentid/tutorid, so the WHERE clause
     * used to clear `note` still matches after those fields have changed.
     *
     * @param int $userid
     */
    private static function anonymize_assignments(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $affectedids = $DB->get_fieldset_select(
            'local_tut_assignment',
            'id',
            'studentid = :s OR tutorid = :t',
            ['s' => $userid, 't' => $userid]
        );

        $DB->set_field('local_tut_assignment', 'studentid', $noreply, ['studentid' => $userid]);
        $DB->set_field('local_tut_assignment', 'tutorid', $noreply, ['tutorid' => $userid]);

        if (!empty($affectedids)) {
            [$insql, $params] = $DB->get_in_or_equal($affectedids, SQL_PARAMS_NAMED);
            $DB->set_field_select('local_tut_assignment', 'note', null, "id $insql", $params);
        }

        $DB->set_field('local_tut_assignment', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_assignment', 'modifiedby', $noreply, ['modifiedby' => $userid]);
    }

    /**
     * Anonymises every local_tut_assignment row in the system — used only by
     * delete_data_for_all_users_in_context() (the whole system context is
     * being purged, e.g. plugin uninstall), never by a single-user erasure
     * request. Clears `note` unconditionally: with no single user left to
     * scope the WHERE clause to, there is no remaining reason to keep any of it.
     */
    private static function anonymize_all_assignments(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_assignment', 'studentid', $noreply, []);
        $DB->set_field('local_tut_assignment', 'tutorid', $noreply, []);
        $DB->set_field('local_tut_assignment', 'note', null, []);
        $DB->set_field('local_tut_assignment', 'createdby', $noreply, []);
        $DB->set_field('local_tut_assignment', 'modifiedby', $noreply, []);
    }

    /**
     * Same anonymisation as anonymize_assignments(), for
     * local_tut_bulkoperation's createdby/primarytutorid/cotutorid. No `note`
     * field on this table to worry about.
     *
     * @param int $userid
     */
    private static function anonymize_bulk_operations(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_bulkoperation', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_bulkoperation', 'primarytutorid', $noreply, ['primarytutorid' => $userid]);
        $DB->set_field('local_tut_bulkoperation', 'cotutorid', $noreply, ['cotutorid' => $userid]);
    }

    private static function anonymize_all_bulk_operations(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_bulkoperation', 'createdby', $noreply, []);
        $DB->set_field('local_tut_bulkoperation', 'primarytutorid', $noreply, []);
        $DB->set_field('local_tut_bulkoperation', 'cotutorid', $noreply, []);
    }
}
