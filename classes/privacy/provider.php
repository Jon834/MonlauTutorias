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
 *   incidental attribution Ã¢â‚¬â€ tutoring relationship history is kept
 *   indefinitely (no expiry), but a subject access/erasure request is now
 *   fully honoured. Export returns every row the requesting user appears in,
 *   as student or tutor, with the other party resolved to a readable name.
 *   Erasure never deletes a row (that would destroy the other party's own
 *   history) Ã¢â‚¬â€ it anonymises it instead: studentid/tutorid/createdby/
 *   modifiedby referencing the erased user are reassigned to the Moodle
 *   "no-reply" user (same mechanism reassign_attribution() already uses for
 *   the 3 catalogues), and the free-text `note` on any row the erased user
 *   appears in (as student or tutor) is cleared, since prose notes can name
 *   a person even after their id reference is gone. assignmenttype/
 *   isprimary/status/dates/source/closereason are left untouched Ã¢â‚¬â€ the fact
 *   that some tutoring relationship existed, when, and why it ended, remains
 *   available for institutional history once anonymised.
 * - `local_tut_bulkoperation`: same anonymisation treatment for
 *   primarytutorid/cotutorid/createdby. On top of that, this table now has an
 *   actual retention limit: `cleanup_bulk_operations_task` purges operations
 *   in a terminal status (completed/completed_with_errors/failed/cancelled)
 *   after 90 days Ã¢â‚¬â€ see TERMINAL_TTL_SECONDS there. Abandoned draft/previewed
 *   operations were already purged after 1 day (phase 3D.4); this adds the
 *   missing other half of the policy.
 *
 * **`local_tut_entry` (phase 5.1), same retention policy as
 * `local_tut_assignment`, decided by the user together with this table's own
 * introduction:** conserved indefinitely; erasure anonymises
 * studentid/tutorid/createdby/modifiedby to the "no-reply" user but leaves
 * `contentvisible`/`noteinternal`/`noterestricted` untouched Ã¢â‚¬â€ this is the
 * tutoring record itself, with institutional history value, same reasoning
 * already applied to `note` on `local_tut_assignment`. Export includes all 3
 * note fields unmasked, regardless of the requesting user's normal
 * viewstudentvisiblecontent/viewinternalnotes/viewrestrictednotes
 * capabilities: a subject access request is a distinct concept from ordinary
 * in-app viewing permission, same as `note`/`closereason` already export
 * unmasked today. `local_tut_entryparticipant.userid` is anonymised the same
 * way when it matches the erased user; `externalname` is out of scope (not
 * tied to any Moodle userid). `local_tut_entryreason` (a pure join, no
 * personal data of its own) is not declared separately Ã¢â‚¬â€ reassigning
 * studentid/tutorid on its parent `local_tut_entry` row already covers it,
 * there is nothing else in it to anonymise.
 *
 * **`local_tut_entryversion` (phase 5.5, closed by this class in phase
 * 5.7's "pruebas de filtraciÃƒÂ³n de datos" review Ã¢â‚¬â€ it was left undeclared
 * when 5.5 first wired a writer for it, a real gap this review caught and
 * fixed):** the table never stores studentid/tutorid at all (a version
 * snapshot only captures the entry's editable content fields, not who the
 * relationship is between), so its only personal-data footprint is
 * `createdby` (who made the edit) Ã¢â‚¬â€ scoped and anonymised by that field
 * alone, same as `local_tut_entryparticipant` is scoped by `userid` alone
 * rather than by joining back to the parent entry. `snapshotjson` and
 * `changereason` are conserved untouched on erasure, same institutional-
 * history reasoning as `local_tut_entry`'s own content fields Ã¢â‚¬â€ a snapshot
 * can still incidentally name someone in prose even after the editor's own
 * id is anonymised, the same accepted limitation `note` already has.
 *
 * **`local_tut_entryattachment` (phase 5.6, same review):** likewise scoped
 * by `createdby` alone (no studentid/tutorid column). `description` is
 * cleared on erasure Ã¢â‚¬â€ free text closer in kind to `note` than to
 * institutional content. `category`, the files themselves and their
 * filenames are left untouched, same reasoning as `contentvisible`. The
 * files (component=local_monlaututoria, filearea=entryattachment) ARE
 * exported via `writer::export_area_files()` for entries the requesting
 * user is student/tutor/creator/modifier/participant of Ã¢â‚¬â€ unlike the
 * transient csvimport file area below, these files are meant to persist
 * and are squarely the kind of record a subject access request should
 * surface.
 *
 * **`local_tut_agreement`/`local_tut_followup`/`local_tut_referral` (phase
 * 6.1/6.2/6.4), same retention policy as `local_tut_entry`:** all 3 are
 * conserved indefinitely; erasure anonymises identity fields (studentid,
 * plus `responsibleuserid` on agreements, `assignedto` on referrals,
 * createdby/modifiedby throughout) to the "no-reply" user, but leaves the
 * institutional content untouched Ã¢â‚¬â€ `description` (agreement), `reason`/
 * `resolution` (referral). This is the same content-vs-identity split
 * already applied to `local_tut_entry`'s own note fields; the "no duplicar
 * contenido sensible" rule for referrals (docs/fases/phase-6.md) is a
 * content-origin constraint on referral_create_form.php, not a privacy
 * classification Ã¢â‚¬â€ it does not change how `reason`/`resolution` are treated
 * here. `local_tut_followup` has no free-text field of its own (see
 * followup_service's class docblock for why) Ã¢â‚¬â€ only identity to anonymise.
 * Export includes all 3 exactly as far as each entity is visible per
 * get_for_viewer() would show it to its own creator (unmasked, same
 * "subject access is not gated by ordinary viewing capability" reasoning as
 * `local_tut_entry`).
 *
 * **`local_tut_notification` (phase 9.1-9.5):** operational delivery logs,
 * not tutoring content. They still hold personal data (`recipientid`, `actorid`,
 * related entity ids, delivery errors), so they must be declared in metadata
 * and export/erasure. The retention policy differs from the institutional-
 * history tables above: rows where the user is recipient are deleted on
 * erasure, because they are an operational inbox copy rather than the
 * canonical tutoring record; rows where they only appear as `actorid` keep
 * the audit trail but lose that attribution (reassigned to the Moodle
 * no-reply user). Scheduled cleanup already purges old rows operationally
 * via `cleanup_notification_logs_task`.
 *
 * The local_monlaututoria/csvimport file area (phase 3D.4) is unaffected by
 * this: it holds the same kind of personal data as local_tut_assignment's
 * studentid/tutorid (whoever a large CSV import's rows name), but only
 * transiently Ã¢â‚¬â€ a file only exists there between csv_import_dispatch_service
 * deferring an import and process_csv_import_task processing it (normally
 * seconds to minutes), and cleanup_bulk_operations_task removes anything left
 * behind. Declared via core_files for completeness but still not wired into
 * export/delete Ã¢â‚¬â€ the file is gone again well before any subject access
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

        // Global admin-curated cohort allowlist — cohortid is not personal
        // data (an FK to Moodle's own cohort table, never a userid); only
        // createdby is an attribution reference. No modifiedby: the whole
        // set is replaced at once (cohort_visibility_service::
        // replace_enabled_cohorts()), never edited row by row.
        $collection->add_database_table('local_tut_enabledcohort', [
            'cohortid'    => 'privacy:metadata:enabledcohort:cohortid',
            'createdby'   => 'privacy:metadata:createdby',
            'timecreated' => 'privacy:metadata:timecreated',
        ], 'privacy:metadata:enabledcohort');

        // Lighter footprint than local_tut_assignment: this table never
        // stores per-student data (see cohort_assignment_preview_service's
        // class docblock) Ã¢â‚¬â€ only attribution (createdby) and the selected
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

        // Exported and anonymised on erasure (phase 3E.6) Ã¢â‚¬â€ see the class
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

        // Exported and anonymised on erasure (phase 5.1) Ã¢â‚¬â€ see the class
        // docblock for the retention policy this implements.
        $collection->add_database_table('local_tut_entry', [
            'studentid'        => 'privacy:metadata:entry:studentid',
            'tutorid'          => 'privacy:metadata:entry:tutorid',
            'academicyearid'   => 'privacy:metadata:entry:academicyearid',
            'entrydate'        => 'privacy:metadata:entry:entrydate',
            'modalityid'       => 'privacy:metadata:entry:modalityid',
            'contentvisible'   => 'privacy:metadata:entry:contentvisible',
            'noteinternal'     => 'privacy:metadata:entry:noteinternal',
            'noterestricted'   => 'privacy:metadata:entry:noterestricted',
            'status'           => 'privacy:metadata:entry:status',
            'nextfollowupdate' => 'privacy:metadata:entry:nextfollowupdate',
            'createdby'        => 'privacy:metadata:createdby',
            'modifiedby'       => 'privacy:metadata:modifiedby',
            'timecreated'      => 'privacy:metadata:timecreated',
            'timemodified'     => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:entry');

        $collection->add_database_table('local_tut_entryparticipant', [
            'participanttype' => 'privacy:metadata:entryparticipant:participanttype',
            'userid'          => 'privacy:metadata:entryparticipant:userid',
            'externalname'    => 'privacy:metadata:entryparticipant:externalname',
            'createdby'       => 'privacy:metadata:createdby',
            'timecreated'     => 'privacy:metadata:timecreated',
        ], 'privacy:metadata:entryparticipant');

        // Exported and anonymised on erasure (phase 5.7, closing a gap left
        // by phase 5.5) Ã¢â‚¬â€ see the class docblock. No studentid/tutorid: a
        // version snapshot only captures the entry's own editable fields.
        $collection->add_database_table('local_tut_entryversion', [
            'versionnumber' => 'privacy:metadata:entryversion:versionnumber',
            'snapshotjson'  => 'privacy:metadata:entryversion:snapshotjson',
            'changereason'  => 'privacy:metadata:entryversion:changereason',
            'createdby'     => 'privacy:metadata:createdby',
            'timecreated'   => 'privacy:metadata:timecreated',
        ], 'privacy:metadata:entryversion');

        // Exported and anonymised on erasure (phase 5.7, closing a gap left
        // by phase 5.6) Ã¢â‚¬â€ see the class docblock. The attachment files
        // themselves are exported via core_files below.
        $collection->add_database_table('local_tut_entryattachment', [
            'category'    => 'privacy:metadata:entryattachment:category',
            'description' => 'privacy:metadata:entryattachment:description',
            'createdby'   => 'privacy:metadata:createdby',
            'timecreated' => 'privacy:metadata:timecreated',
        ], 'privacy:metadata:entryattachment');

        // Persistent, unlike the transient csvimport area below Ã¢â‚¬â€ exported
        // for entries the requesting user is entitled to (see the class
        // docblock and export_entry_attachments()).
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:entryattachmentfiles');

        // Exported and anonymised on erasure (phase 6.1) Ã¢â‚¬â€ see the class docblock.
        $collection->add_database_table('local_tut_agreement', [
            'studentid'                => 'privacy:metadata:agreement:studentid',
            'description'              => 'privacy:metadata:agreement:description',
            'responsibletype'          => 'privacy:metadata:agreement:responsibletype',
            'responsibleuserid'        => 'privacy:metadata:agreement:responsibleuserid',
            'responsibleexternalname'  => 'privacy:metadata:agreement:responsibleexternalname',
            'duedate'                  => 'privacy:metadata:agreement:duedate',
            'status'                   => 'privacy:metadata:agreement:status',
            'visibletostudent'         => 'privacy:metadata:agreement:visibletostudent',
            'createdby'                => 'privacy:metadata:createdby',
            'modifiedby'               => 'privacy:metadata:modifiedby',
            'timecreated'              => 'privacy:metadata:timecreated',
            'timemodified'             => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:agreement');

        // Exported and anonymised on erasure (phase 6.2) Ã¢â‚¬â€ see the class docblock.
        $collection->add_database_table('local_tut_followup', [
            'studentid'      => 'privacy:metadata:followup:studentid',
            'duedate'        => 'privacy:metadata:followup:duedate',
            'priority'       => 'privacy:metadata:followup:priority',
            'status'         => 'privacy:metadata:followup:status',
            'closingentryid' => 'privacy:metadata:followup:closingentryid',
            'createdby'      => 'privacy:metadata:createdby',
            'modifiedby'     => 'privacy:metadata:modifiedby',
            'timecreated'    => 'privacy:metadata:timecreated',
            'timemodified'   => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:followup');

        // Exported and anonymised on erasure (phase 6.4) Ã¢â‚¬â€ see the class docblock.
        $collection->add_database_table('local_tut_referral', [
            'studentid'    => 'privacy:metadata:referral:studentid',
            'destination'  => 'privacy:metadata:referral:destination',
            'reason'       => 'privacy:metadata:referral:reason',
            'priority'     => 'privacy:metadata:referral:priority',
            'assignedto'   => 'privacy:metadata:referral:assignedto',
            'status'       => 'privacy:metadata:referral:status',
            'resolution'   => 'privacy:metadata:referral:resolution',
            'createdby'    => 'privacy:metadata:createdby',
            'modifiedby'   => 'privacy:metadata:modifiedby',
            'timecreated'  => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:referral');

        $collection->add_database_table('local_tut_notification', [
            'notificationtype' => 'privacy:metadata:notification:notificationtype',
            'recipientid'      => 'privacy:metadata:notification:recipientid',
            'actorid'          => 'privacy:metadata:notification:actorid',
            'entitytype'       => 'privacy:metadata:notification:entitytype',
            'entityid'         => 'privacy:metadata:notification:entityid',
            'digestkey'        => 'privacy:metadata:notification:digestkey',
            'status'           => 'privacy:metadata:notification:status',
            'attempts'         => 'privacy:metadata:notification:attempts',
            'lasterror'        => 'privacy:metadata:notification:lasterror',
            'timesent'         => 'privacy:metadata:notification:timesent',
            'timecreated'      => 'privacy:metadata:timecreated',
            'timemodified'     => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:notification');

        // Transient only Ã¢â‚¬â€ see the class docblock. Not wired into
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
                SELECT 1 FROM {local_tut_enabledcohort} WHERE createdby = :ec1
                UNION
                SELECT 1 FROM {local_tut_assignment}
                    WHERE studentid = :as1 OR tutorid = :as2 OR createdby = :as3 OR modifiedby = :as4
                UNION
                SELECT 1 FROM {local_tut_bulkoperation}
                    WHERE createdby = :bo1 OR primarytutorid = :bo2 OR cotutorid = :bo3
                UNION
                SELECT 1 FROM {local_tut_entry}
                    WHERE studentid = :en1 OR tutorid = :en2 OR createdby = :en3 OR modifiedby = :en4
                UNION
                SELECT 1 FROM {local_tut_entryparticipant} WHERE userid = :ep1
                UNION
                SELECT 1 FROM {local_tut_entryversion} WHERE createdby = :ev1
                UNION
                SELECT 1 FROM {local_tut_entryattachment} WHERE createdby = :ea1
                UNION
                SELECT 1 FROM {local_tut_agreement}
                    WHERE studentid = :ag1 OR responsibleuserid = :ag2 OR createdby = :ag3 OR modifiedby = :ag4
                UNION
                SELECT 1 FROM {local_tut_followup}
                    WHERE studentid = :fu1 OR createdby = :fu2 OR modifiedby = :fu3
                UNION
                SELECT 1 FROM {local_tut_referral}
                    WHERE studentid = :rf1 OR assignedto = :rf2 OR createdby = :rf3 OR modifiedby = :rf4
                UNION
                SELECT 1 FROM {local_tut_notification}
                    WHERE recipientid = :nt1 OR actorid = :nt2';
        $params = [
            'ay1' => $userid, 'ay2' => $userid,
            'r1'  => $userid, 'r2'  => $userid,
            'm1'  => $userid, 'm2'  => $userid,
            'ec1' => $userid,
            'as1' => $userid, 'as2' => $userid, 'as3' => $userid, 'as4' => $userid,
            'bo1' => $userid, 'bo2' => $userid, 'bo3' => $userid,
            'en1' => $userid, 'en2' => $userid, 'en3' => $userid, 'en4' => $userid,
            'ep1' => $userid,
            'ev1' => $userid,
            'ea1' => $userid,
            'ag1' => $userid, 'ag2' => $userid, 'ag3' => $userid, 'ag4' => $userid,
            'fu1' => $userid, 'fu2' => $userid, 'fu3' => $userid,
            'rf1' => $userid, 'rf2' => $userid, 'rf3' => $userid, 'rf4' => $userid,
            'nt1' => $userid, 'nt2' => $userid,
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

        // No modifiedby on this table (see get_metadata()) — not part of the
        // generic TABLES loop above.
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_enabledcohort}', []);

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('tutorid', 'SELECT tutorid FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_assignment}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_assignment}', []);

        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_bulkoperation}', []);
        $userlist->add_from_sql('primarytutorid', 'SELECT primarytutorid FROM {local_tut_bulkoperation}', []);
        $userlist->add_from_sql('cotutorid', 'SELECT cotutorid FROM {local_tut_bulkoperation}', []);

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_entry}', []);
        $userlist->add_from_sql('tutorid', 'SELECT tutorid FROM {local_tut_entry}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_entry}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_entry}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_tut_entryparticipant}', []);

        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_entryversion}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_entryattachment}', []);

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_agreement}', []);
        $userlist->add_from_sql('responsibleuserid', 'SELECT responsibleuserid FROM {local_tut_agreement}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_agreement}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_agreement}', []);

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_followup}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_followup}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_followup}', []);

        $userlist->add_from_sql('studentid', 'SELECT studentid FROM {local_tut_referral}', []);
        $userlist->add_from_sql('assignedto', 'SELECT assignedto FROM {local_tut_referral}', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_tut_referral}', []);
        $userlist->add_from_sql('modifiedby', 'SELECT modifiedby FROM {local_tut_referral}', []);

        $userlist->add_from_sql('recipientid', 'SELECT recipientid FROM {local_tut_notification}', []);
        $userlist->add_from_sql('actorid', 'SELECT actorid FROM {local_tut_notification}', []);
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

        $enabledcohorts = $DB->get_records('local_tut_enabledcohort', ['createdby' => $userid]);
        $data['enabledcohorts'] = array_values(array_map(
            static fn (\stdClass $record): \stdClass => (object) ['cohortid' => (int) $record->cohortid],
            $enabledcohorts
        ));

        $data['assignments'] = self::export_assignments($userid);
        $data['bulkoperations'] = self::export_bulk_operations($userid);
        $data['entries'] = self::export_entries($userid);
        $data['entryversions'] = self::export_entry_versions($userid);
        $data['entryattachments'] = self::export_entry_attachments($userid);
        $data['agreements'] = self::export_agreements($userid);
        $data['followups'] = self::export_followups($userid);
        $data['referrals'] = self::export_referrals($userid);
        $data['notifications'] = self::export_notifications($userid);

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
            // name Ã¢â‚¬â€ a raw id would not be intelligible in an export meant
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

    /**
     * Exports every entry where the requesting user is student, tutor,
     * creator, modifier, or an internal participant. Notes are included
     * unmasked (see the class docblock) Ã¢â‚¬â€ a subject access request is not
     * gated by the normal viewstudentvisiblecontent/viewinternalnotes/
     * viewrestrictednotes capabilities.
     *
     * @param int $userid
     * @return array
     */
    private static function export_entries(int $userid): array {
        global $DB;

        $sql = 'SELECT DISTINCT e.* FROM {local_tut_entry} e
                LEFT JOIN {local_tut_entryparticipant} p ON p.entryid = e.id
                WHERE e.studentid = :s OR e.tutorid = :t OR e.createdby = :c OR e.modifiedby = :m OR p.userid = :pid';
        $records = $DB->get_records_sql($sql, ['s' => $userid, 't' => $userid, 'c' => $userid, 'm' => $userid, 'pid' => $userid]);

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
            if ($DB->record_exists('local_tut_entryparticipant', ['entryid' => $record->id, 'userid' => $userid])) {
                $roles[] = 'participant';
            }

            $counterpartid = (int) $record->studentid === $userid ? (int) $record->tutorid : (int) $record->studentid;
            $counterpart = \core_user::get_user($counterpartid);

            $export[] = (object) [
                'yourrole'         => $roles,
                'counterpart'      => $counterpart ? fullname($counterpart) : null,
                'entrydate'        => userdate($record->entrydate),
                'status'           => $record->status,
                'contentvisible'   => $record->contentvisible,
                'noteinternal'     => $record->noteinternal,
                'noterestricted'   => $record->noterestricted,
                'nextfollowupdate' => $record->nextfollowupdate ? userdate($record->nextfollowupdate) : null,
                'timecreated'      => userdate($record->timecreated),
                'timemodified'     => userdate($record->timemodified),
            ];
        }

        return $export;
    }

    /**
     * Exports every version snapshot the requesting user created (see the
     * class docblock: local_tut_entryversion has no studentid/tutorid of its
     * own, so createdby is the only scoping field available). snapshotjson
     * is included unmasked, same "subject access overrides normal viewing
     * capabilities" reasoning as export_entries().
     *
     * @param int $userid
     * @return array
     */
    private static function export_entry_versions(int $userid): array {
        global $DB;

        $records = $DB->get_records('local_tut_entryversion', ['createdby' => $userid]);

        $export = [];
        foreach ($records as $record) {
            $export[] = (object) [
                'entryid'       => (int) $record->entryid,
                'versionnumber' => (int) $record->versionnumber,
                'snapshot'      => json_decode($record->snapshotjson, true) ?? [],
                'changereason'  => $record->changereason,
                'timecreated'   => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    /**
     * Exports every attachment the requesting user uploaded (createdby is
     * the only scoping field, same reasoning as export_entry_versions()),
     * including the file itself via export_area_files() Ã¢â‚¬â€ see the class
     * docblock for why these files (unlike the transient csvimport area)
     * are squarely in scope for a subject access request.
     *
     * @param int $userid
     * @return array
     */
    private static function export_entry_attachments(int $userid): array {
        global $DB;

        $records = $DB->get_records('local_tut_entryattachment', ['createdby' => $userid]);

        $context = \context_system::instance();
        $fs = get_file_storage();
        $export = [];
        foreach ($records as $record) {
            $file = $fs->get_file_by_hash($record->pathnamehash);
            $filename = $file ? $file->get_filename() : null;

            if ($file) {
                writer::with_context($context)->export_file(
                    [get_string('pluginname', 'local_monlaututoria'), 'entryattachments'],
                    $file
                );
            }

            $export[] = (object) [
                'entryid'     => (int) $record->entryid,
                'filename'    => $filename,
                'category'    => $record->category,
                'description' => $record->description,
                'timecreated' => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    /**
     * Exports every agreement where the requesting user is student,
     * responsible party, creator or modifier. Unmasked, same reasoning as
     * export_entries().
     *
     * @param int $userid
     * @return array
     */
    private static function export_agreements(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_agreement',
            'studentid = :s OR responsibleuserid = :r OR createdby = :c OR modifiedby = :m',
            ['s' => $userid, 'r' => $userid, 'c' => $userid, 'm' => $userid]
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->studentid === $userid) {
                $roles[] = 'student';
            }
            if ((int) $record->responsibleuserid === $userid) {
                $roles[] = 'responsible';
            }
            if ((int) $record->createdby === $userid) {
                $roles[] = 'creator';
            }
            if ((int) $record->modifiedby === $userid) {
                $roles[] = 'modifier';
            }

            $export[] = (object) [
                'yourrole'         => $roles,
                'entryid'          => (int) $record->entryid,
                'description'      => $record->description,
                'responsibletype'  => $record->responsibletype,
                'duedate'          => userdate($record->duedate),
                'status'           => $record->status,
                'visibletostudent' => (bool) $record->visibletostudent,
                'timecreated'      => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    /**
     * Exports every follow-up where the requesting user is student, creator
     * or modifier. Unmasked, same reasoning as export_entries().
     *
     * @param int $userid
     * @return array
     */
    private static function export_followups(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_followup',
            'studentid = :s OR createdby = :c OR modifiedby = :m',
            ['s' => $userid, 'c' => $userid, 'm' => $userid]
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->studentid === $userid) {
                $roles[] = 'student';
            }
            if ((int) $record->createdby === $userid) {
                $roles[] = 'creator';
            }
            if ((int) $record->modifiedby === $userid) {
                $roles[] = 'modifier';
            }

            $export[] = (object) [
                'yourrole'    => $roles,
                'entryid'     => (int) $record->entryid,
                'duedate'     => userdate($record->duedate),
                'priority'    => $record->priority,
                'status'      => $record->status,
                'timecreated' => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    /**
     * Exports every referral where the requesting user is student, assignee,
     * creator or modifier. Unmasked, same reasoning as export_entries() Ã¢â‚¬â€
     * this is the one export in this method where "unmasked" specifically
     * means it does NOT go through referral_service::get_for_viewer()'s
     * capability check, since a subject access request is about what the
     * platform holds about the requester, not about what they are entitled
     * to browse day-to-day.
     *
     * @param int $userid
     * @return array
     */
    private static function export_referrals(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_referral',
            'studentid = :s OR assignedto = :a OR createdby = :c OR modifiedby = :m',
            ['s' => $userid, 'a' => $userid, 'c' => $userid, 'm' => $userid]
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->studentid === $userid) {
                $roles[] = 'student';
            }
            if ((int) $record->assignedto === $userid) {
                $roles[] = 'assignee';
            }
            if ((int) $record->createdby === $userid) {
                $roles[] = 'creator';
            }
            if ((int) $record->modifiedby === $userid) {
                $roles[] = 'modifier';
            }

            $export[] = (object) [
                'yourrole'    => $roles,
                'entryid'     => (int) $record->entryid,
                'destination' => $record->destination,
                'reason'      => $record->reason,
                'priority'    => $record->priority,
                'status'      => $record->status,
                'resolution'  => $record->resolution,
                'timecreated' => userdate($record->timecreated),
            ];
        }

        return $export;
    }

    /**
     * @param int $userid
     * @return array
     */
    private static function export_notifications(int $userid): array {
        global $DB;

        $records = $DB->get_records_select(
            'local_tut_notification',
            'recipientid = :r OR actorid = :a',
            ['r' => $userid, 'a' => $userid],
            'id ASC'
        );

        $export = [];
        foreach ($records as $record) {
            $roles = [];
            if ((int) $record->recipientid === $userid) {
                $roles[] = 'recipient';
            }
            if ((int) $record->actorid === $userid) {
                $roles[] = 'actor';
            }

            $export[] = (object) [
                'yourrole' => $roles,
                'notificationtype' => $record->notificationtype,
                'entitytype' => $record->entitytype,
                'entityid' => (int) $record->entityid,
                'digestkey' => $record->digestkey,
                'status' => $record->status,
                'attempts' => (int) $record->attempts,
                'lasterror' => $record->lasterror,
                'timesent' => $record->timesent ? userdate($record->timesent) : null,
                'timecreated' => userdate($record->timecreated),
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
        self::anonymize_all_entries();
        self::anonymize_all_entry_versions();
        self::anonymize_all_entry_attachments();
        self::anonymize_all_agreements();
        self::anonymize_all_followups();
        self::anonymize_all_referrals();
        self::delete_all_notifications();
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_SYSTEM) {
                $userid = $contextlist->get_user()->id;
                self::reassign_attribution($userid);
                self::anonymize_assignments($userid);
                self::anonymize_bulk_operations($userid);
                self::anonymize_entries($userid);
                self::anonymize_entry_versions($userid);
                self::anonymize_entry_attachments($userid);
                self::anonymize_agreements($userid);
                self::anonymize_followups($userid);
                self::anonymize_referrals($userid);
                self::anonymize_notifications($userid);
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
            self::anonymize_entries((int) $userid);
            self::anonymize_entry_versions((int) $userid);
            self::anonymize_entry_attachments((int) $userid);
            self::anonymize_agreements((int) $userid);
            self::anonymize_followups((int) $userid);
            self::anonymize_referrals((int) $userid);
            self::anonymize_notifications((int) $userid);
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

        // No modifiedby on this table (see get_metadata()).
        $DB->set_field('local_tut_enabledcohort', 'createdby', $noreply, ['createdby' => $userid]);
    }

    private static function reassign_all_attribution(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        foreach (self::TABLES as $table) {
            $DB->set_field($table, 'createdby', $noreply, []);
            $DB->set_field($table, 'modifiedby', $noreply, []);
        }

        $DB->set_field('local_tut_enabledcohort', 'createdby', $noreply, []);
    }

    /**
     * Anonymises every local_tut_assignment row where $userid appears as
     * student, tutor, creator or modifier Ã¢â‚¬â€ never deletes a row, since that
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
     * Anonymises every local_tut_assignment row in the system Ã¢â‚¬â€ used only by
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

    /**
     * Anonymises local_tut_entry rows where $userid is student, tutor,
     * creator or modifier, and local_tut_entryparticipant.userid where it
     * matches Ã¢â‚¬â€ never deletes a row, and never touches contentvisible/
     * noteinternal/noterestricted (decided by the user together with this
     * table's own introduction, phase 5.1 Ã¢â‚¬â€ see the class docblock).
     *
     * @param int $userid
     */
    private static function anonymize_entries(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entry', 'studentid', $noreply, ['studentid' => $userid]);
        $DB->set_field('local_tut_entry', 'tutorid', $noreply, ['tutorid' => $userid]);
        $DB->set_field('local_tut_entry', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_entry', 'modifiedby', $noreply, ['modifiedby' => $userid]);

        $DB->set_field('local_tut_entryparticipant', 'userid', $noreply, ['userid' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_entries(), for every row in the
     * system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_entries(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entry', 'studentid', $noreply, []);
        $DB->set_field('local_tut_entry', 'tutorid', $noreply, []);
        $DB->set_field('local_tut_entry', 'createdby', $noreply, []);
        $DB->set_field('local_tut_entry', 'modifiedby', $noreply, []);

        $DB->set_field('local_tut_entryparticipant', 'userid', $noreply, []);
    }

    /**
     * Anonymises local_tut_entryversion rows created by $userid Ã¢â‚¬â€ createdby
     * only, no studentid/tutorid on this table (see the class docblock).
     * snapshotjson/changereason are left untouched, same institutional-
     * history reasoning as local_tut_entry's own content fields.
     *
     * @param int $userid
     */
    private static function anonymize_entry_versions(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entryversion', 'createdby', $noreply, ['createdby' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_entry_versions(), for every row in the
     * system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_entry_versions(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entryversion', 'createdby', $noreply, []);
    }

    /**
     * Anonymises local_tut_entryattachment rows created by $userid Ã¢â‚¬â€
     * createdby only, same reasoning as anonymize_entry_versions(). Clears
     * `description` (free text closer in kind to `note` than to
     * institutional content, see the class docblock); `category` and the
     * attachment files themselves are left untouched.
     *
     * @param int $userid
     */
    private static function anonymize_entry_attachments(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entryattachment', 'description', null, ['createdby' => $userid]);
        $DB->set_field('local_tut_entryattachment', 'createdby', $noreply, ['createdby' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_entry_attachments(), for every row in
     * the system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_entry_attachments(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_entryattachment', 'description', null, []);
        $DB->set_field('local_tut_entryattachment', 'createdby', $noreply, []);
    }

    /**
     * Anonymises local_tut_agreement rows where $userid is student,
     * responsible user, creator or modifier Ã¢â‚¬â€ never touches `description`
     * (see the class docblock).
     *
     * @param int $userid
     */
    private static function anonymize_agreements(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_agreement', 'studentid', $noreply, ['studentid' => $userid]);
        $DB->set_field('local_tut_agreement', 'responsibleuserid', $noreply, ['responsibleuserid' => $userid]);
        $DB->set_field('local_tut_agreement', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_agreement', 'modifiedby', $noreply, ['modifiedby' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_agreements(), for every row in the
     * system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_agreements(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_agreement', 'studentid', $noreply, []);
        $DB->set_field('local_tut_agreement', 'responsibleuserid', $noreply, []);
        $DB->set_field('local_tut_agreement', 'createdby', $noreply, []);
        $DB->set_field('local_tut_agreement', 'modifiedby', $noreply, []);
    }

    /**
     * Anonymises local_tut_followup rows where $userid is student, creator
     * or modifier. No free-text field on this table (see the class docblock).
     *
     * @param int $userid
     */
    private static function anonymize_followups(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_followup', 'studentid', $noreply, ['studentid' => $userid]);
        $DB->set_field('local_tut_followup', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_followup', 'modifiedby', $noreply, ['modifiedby' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_followups(), for every row in the
     * system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_followups(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_followup', 'studentid', $noreply, []);
        $DB->set_field('local_tut_followup', 'createdby', $noreply, []);
        $DB->set_field('local_tut_followup', 'modifiedby', $noreply, []);
    }

    /**
     * Anonymises local_tut_referral rows where $userid is student, assignee,
     * creator or modifier Ã¢â‚¬â€ never touches `reason`/`resolution` (see the
     * class docblock).
     *
     * @param int $userid
     */
    private static function anonymize_referrals(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_referral', 'studentid', $noreply, ['studentid' => $userid]);
        $DB->set_field('local_tut_referral', 'assignedto', $noreply, ['assignedto' => $userid]);
        $DB->set_field('local_tut_referral', 'createdby', $noreply, ['createdby' => $userid]);
        $DB->set_field('local_tut_referral', 'modifiedby', $noreply, ['modifiedby' => $userid]);
    }

    /**
     * Same anonymisation as anonymize_referrals(), for every row in the
     * system Ã¢â‚¬â€ used only by delete_data_for_all_users_in_context().
     */
    private static function anonymize_all_referrals(): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;

        $DB->set_field('local_tut_referral', 'studentid', $noreply, []);
        $DB->set_field('local_tut_referral', 'assignedto', $noreply, []);
        $DB->set_field('local_tut_referral', 'createdby', $noreply, []);
        $DB->set_field('local_tut_referral', 'modifiedby', $noreply, []);
    }

    /**
     * Notification logs are operational metadata only: rows where the user was
     * recipient are deleted entirely, while rows they only triggered keep the
     * delivery audit but lose that actor attribution.
     *
     * @param int $userid
     */
    private static function anonymize_notifications(int $userid): void {
        global $DB;

        $noreply = \core_user::get_noreply_user()->id;
        $DB->delete_records('local_tut_notification', ['recipientid' => $userid]);
        $DB->set_field('local_tut_notification', 'actorid', $noreply, ['actorid' => $userid]);
    }

    private static function delete_all_notifications(): void {
        global $DB;

        $DB->delete_records('local_tut_notification');
    }
}

