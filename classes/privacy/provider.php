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
 * local_tut_assignment (added in phase 3A) is different: studentid/tutorid
 * ARE the personal data, not incidental attribution, and there is no
 * institutional retention policy defined yet for tutoring relationship
 * history (see docs/modelo-datos.md and docs/pruebas.md — "Privacidad:
 * pendiente"). Declaring a context/export/delete path without one would
 * either destroy historically relevant data or falsely claim compliance, so
 * this class deliberately ONLY registers local_tut_assignment's metadata
 * below; it is NOT included in self::TABLES and is untouched by
 * get_contexts_for_userid()/get_users_in_context()/export_user_data()/
 * delete_data_for_user(s)/delete_data_for_all_users_in_context(). This is a
 * known, documented compliance gap to close once that policy exists.
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

        // Metadata only. Much lighter footprint than local_tut_assignment:
        // this table never stores per-student data (see
        // cohort_assignment_preview_service's class docblock) — only
        // attribution (createdby) and the selected tutor(s) as references.
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

        // Metadata only — see the class docblock for why export/delete do not
        // yet cover this table.
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
            'createdby'      => 'privacy:metadata:createdby',
            'modifiedby'     => 'privacy:metadata:modifiedby',
            'timecreated'    => 'privacy:metadata:timecreated',
            'timemodified'   => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:assignment');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();

        $sql = 'SELECT 1 FROM {local_tut_academicyear} WHERE createdby = :ay1 OR modifiedby = :ay2
                UNION
                SELECT 1 FROM {local_tut_reason} WHERE createdby = :r1 OR modifiedby = :r2
                UNION
                SELECT 1 FROM {local_tut_modality} WHERE createdby = :m1 OR modifiedby = :m2';
        $params = [
            'ay1' => $userid, 'ay2' => $userid,
            'r1'  => $userid, 'r2'  => $userid,
            'm1'  => $userid, 'm2'  => $userid,
        ];

        if ($DB->record_exists_sql($sql, $params)) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        if ($userlist->get_context()->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        foreach (self::TABLES as $table) {
            $userlist->add_from_sql('createdby', "SELECT createdby FROM {{$table}}", []);
            $userlist->add_from_sql('modifiedby', "SELECT modifiedby FROM {{$table}}", []);
        }
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

        writer::with_context(\context_system::instance())->export_data(
            [get_string('pluginname', 'local_monlaututoria')],
            (object) $data
        );
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        self::reassign_all_attribution();
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_SYSTEM) {
                self::reassign_attribution($contextlist->get_user()->id);
            }
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        if ($userlist->get_context()->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            self::reassign_attribution((int) $userid);
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
}
