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
 * English language strings for local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Monlau Tutoria';
$string['monlaututoria:view'] = 'View Monlau Tutoria';
$string['monlaututoria:viewconfiguration'] = 'View Monlau Tutoria configuration';
$string['monlaututoria:manageacademicyears'] = 'Manage academic years';
$string['monlaututoria:managecatalogues'] = 'Manage tutoring catalogues';
$string['monlaututoria:overridelock'] = 'Override locked academic years';

$string['academicyears'] = 'Academic years';
$string['reasons'] = 'Tutoring reasons';
$string['modalities'] = 'Contact modalities';

$string['academicyear_name'] = 'Name';
$string['academicyear_shortname'] = 'Short name';
$string['academicyear_startdate'] = 'Start date';
$string['academicyear_enddate'] = 'End date';
$string['academicyear_active'] = 'Active';
$string['academicyear_locked'] = 'Locked';
$string['academicyear_create'] = 'New academic year';
$string['academicyear_edit'] = 'Edit';
$string['academicyear_activate'] = 'Activate';
$string['academicyear_lock'] = 'Lock';
$string['academicyear_unlock'] = 'Unlock';
$string['academicyear_delete'] = 'Delete';
$string['academicyear_list_empty'] = 'No academic years have been created yet.';
$string['academicyear_activate_confirm'] = 'Academic year "{$a}" is currently active. Activating this one will deactivate it. Continue?';
$string['academicyear_activate_confirm_noactive'] = 'Activate this academic year?';
$string['academicyear_activate_success'] = 'Academic year activated.';
$string['academicyear_locked_success'] = 'Academic year locked.';
$string['academicyear_unlocked_success'] = 'Academic year unlocked.';
$string['academicyear_delete_confirm'] = 'Delete academic year "{$a}"? This cannot be undone.';
$string['academicyear_delete_success'] = 'Academic year deleted.';
$string['academicyear_delete_blocked_active'] = 'The active academic year cannot be deleted.';
$string['academicyear_delete_blocked_used'] = 'This academic year cannot be deleted because it is referenced by other data.';
$string['noactiveacademicyear_warning'] = 'There is no active academic year. Create and activate one to continue.';

$string['error_enddate_before_startdate'] = 'The end date must be after the start date.';
$string['error_shortname_duplicate'] = 'This short name is already in use.';
$string['error_academicyear_locked'] = 'This academic year is locked and cannot be modified.';
$string['error_noaccess_overridelock'] = 'You do not have permission to unlock this academic year.';

$string['reason_name'] = 'Name';
$string['reason_shortname'] = 'Short name';
$string['reason_description'] = 'Description';
$string['reason_active'] = 'Active';
$string['reason_requiresfollowup'] = 'Requires follow-up';
$string['reason_defaultvisibility'] = 'Default visibility';
$string['reason_create'] = 'New reason';
$string['reason_edit'] = 'Edit';
$string['reason_activate'] = 'Activate';
$string['reason_deactivate'] = 'Deactivate';
$string['reason_delete'] = 'Delete';
$string['reason_delete_confirm'] = 'Delete reason "{$a}"? This cannot be undone.';
$string['reason_moveup'] = 'Move up';
$string['reason_movedown'] = 'Move down';
$string['reason_list_empty'] = 'No reasons have been created yet.';
$string['reason_delete_blocked_used'] = 'This reason cannot be deleted because it is referenced by other data.';

$string['modality_name'] = 'Name';
$string['modality_shortname'] = 'Short name';
$string['modality_description'] = 'Description';
$string['modality_active'] = 'Active';
$string['modality_create'] = 'New modality';
$string['modality_edit'] = 'Edit';
$string['modality_activate'] = 'Activate';
$string['modality_deactivate'] = 'Deactivate';
$string['modality_delete'] = 'Delete';
$string['modality_delete_confirm'] = 'Delete modality "{$a}"? This cannot be undone.';
$string['modality_moveup'] = 'Move up';
$string['modality_movedown'] = 'Move down';
$string['modality_list_empty'] = 'No modalities have been created yet.';
$string['modality_delete_blocked_used'] = 'This modality cannot be deleted because it is referenced by other data.';

$string['visibility_shared'] = 'Shared with the student';
$string['visibility_internal'] = 'Internal tutoring';
$string['visibility_restricted'] = 'Restricted';

$string['eventacademicyearcreated'] = 'Academic year created';
$string['eventacademicyearupdated'] = 'Academic year updated';
$string['eventacademicyearactivated'] = 'Academic year activated';
$string['eventacademicyearlocked'] = 'Academic year locked or unlocked';
$string['eventreasoncreated'] = 'Tutoring reason created';
$string['eventreasonupdated'] = 'Tutoring reason updated';
$string['eventreasonactivated'] = 'Tutoring reason activated or deactivated';
$string['eventmodalitycreated'] = 'Contact modality created';
$string['eventmodalityupdated'] = 'Contact modality updated';
$string['eventmodalityactivated'] = 'Contact modality activated or deactivated';
$string['eventacademicyeardeleted'] = 'Academic year deleted';
$string['eventreasondeleted'] = 'Tutoring reason deleted';
$string['eventmodalitydeleted'] = 'Contact modality deleted';

$string['reason_seed_acogida_inicial'] = 'Initial welcome';
$string['reason_seed_seguimiento_ordinario'] = 'Routine follow-up';
$string['reason_seed_rendimiento_academico'] = 'Academic performance';
$string['reason_seed_asistencia'] = 'Attendance';
$string['reason_seed_puntualidad'] = 'Punctuality';
$string['reason_seed_convivencia'] = 'Coexistence';
$string['reason_seed_motivacion'] = 'Motivation';
$string['reason_seed_habitos_estudio'] = 'Study habits';
$string['reason_seed_organizacion'] = 'Organisation';
$string['reason_seed_orientacion_academica'] = 'Academic guidance';
$string['reason_seed_orientacion_profesional'] = 'Career guidance';
$string['reason_seed_practicas_empresa'] = 'Work placement';
$string['reason_seed_situacion_personal'] = 'Personal situation';
$string['reason_seed_seguimiento_acuerdos'] = 'Agreement follow-up';
$string['reason_seed_contacto_familia'] = 'Contact with family';
$string['reason_seed_solicitud_alumno'] = 'Requested by the student';
$string['reason_seed_solicitud_familia'] = 'Requested by the family';
$string['reason_seed_reconocimiento_positivo'] = 'Positive recognition';
$string['reason_seed_derivacion'] = 'Referral';
$string['reason_seed_otro'] = 'Other';

$string['modality_seed_presencial'] = 'In person';
$string['modality_seed_telefono'] = 'Phone';
$string['modality_seed_videoconferencia'] = 'Videoconference';
$string['modality_seed_correo_electronico'] = 'Email';
$string['modality_seed_mensajeria'] = 'Messaging';
$string['modality_seed_reunion_coordinacion'] = 'Coordination meeting';
$string['modality_seed_otra'] = 'Other';

$string['privacy:metadata:createdby'] = 'The user who created this record.';
$string['privacy:metadata:modifiedby'] = 'The user who last modified this record.';
$string['privacy:metadata:timecreated'] = 'The time the record was created.';
$string['privacy:metadata:timemodified'] = 'The time the record was last modified.';
$string['privacy:metadata:academicyear'] = 'Information about academic years, including who created or last modified each one.';
$string['privacy:metadata:academicyear:name'] = 'The visible name of the academic year.';
$string['privacy:metadata:academicyear:shortname'] = 'The stable short name of the academic year.';
$string['privacy:metadata:reason'] = 'Information about tutoring reasons, including who created or last modified each one.';
$string['privacy:metadata:reason:name'] = 'The visible name of the reason.';
$string['privacy:metadata:reason:shortname'] = 'The stable short name of the reason.';
$string['privacy:metadata:modality'] = 'Information about contact modalities, including who created or last modified each one.';
$string['privacy:metadata:modality:name'] = 'The visible name of the modality.';
$string['privacy:metadata:modality:shortname'] = 'The stable short name of the modality.';
$string['privacy:metadata:enabledcohort'] = 'Which Moodle cohorts an administrator has enabled as relevant to this plugin, including who configured it.';
$string['privacy:metadata:enabledcohort:cohortid'] = 'The enabled Moodle cohort (not personal data itself — a reference to the cohort).';

$string['monlaututoria:viewownstudents'] = 'View own assigned students';
$string['monlaututoria:viewstudent'] = 'View an individual student\'s tutoring record';
$string['monlaututoria:viewhistoricalassignments'] = 'View own historical (closed) assignments';
$string['monlaututoria:assignstudents'] = 'Create student assignments';
$string['monlaututoria:manageassignments'] = 'Manage existing assignments';
$string['monlaututoria:managecohortassignments'] = 'Manage cohort-based assignments';
$string['monlaututoria:importassignments'] = 'Import assignments from CSV';
$string['monlaututoria:reassignstudents'] = 'Reassign students to a new tutor';
$string['monlaututoria:viewallassignments'] = 'View all assignments regardless of scope';
$string['monlaututoria:manageclosedassignments'] = 'Reopen or modify closed assignments';
$string['monlaututoria:viewownfile'] = 'View my own longitudinal file';

$string['error_assignment_self'] = 'A student cannot be their own tutor.';
$string['error_assignment_invalid_student'] = 'The selected student does not exist or has been deleted.';
$string['error_assignment_invalid_tutor'] = 'The selected tutor does not exist or has been deleted.';
$string['error_assignment_student_suspended'] = 'The selected student account is suspended.';
$string['error_assignment_tutor_suspended'] = 'The selected tutor account is suspended.';
$string['error_assignment_academicyear_invalid'] = 'The selected academic year does not exist.';
$string['error_assignment_academicyear_locked'] = 'The selected academic year is locked for new assignments.';
$string['error_assignment_invalid_cohort'] = 'The selected cohort does not exist.';
$string['error_assignment_dates_invalid'] = 'The end date cannot be before the start date.';
$string['error_assignment_duplicate'] = 'An identical active assignment already exists.';
$string['error_assignment_isprimary_type_mismatch'] = 'Only a primary assignment can be marked as the primary tutor.';$string['error_assignment_primary_duplicate'] = 'This student already has an active primary tutor for this academic year.';
$string['error_assignment_invalid_type'] = 'Invalid assignment type.';
$string['error_assignment_already_closed'] = 'This assignment is already closed or cancelled.';
$string['error_assignment_no_active_primary'] = 'This student has no active primary tutor to reassign.';
$string['error_assignment_reassign_same_tutor'] = 'The new tutor is already the primary tutor.';
$string['error_assignment_not_active_cotutor'] = 'This assignment is not an active co-tutor assignment.';
$string['error_scope_access_denied'] = 'You do not have access to this student\'s tutoring data.';

$string['eventassignmentcreated'] = 'Assignment created';
$string['eventassignmentclosed'] = 'Assignment closed';
$string['eventstudentreassigned'] = 'Student reassigned to a new tutor';
$string['eventcotutoradded'] = 'Co-tutor added';
$string['eventcotutorremoved'] = 'Co-tutor removed';

$string['assignmenttype_primary'] = 'Primary tutor';
$string['assignmenttype_co_tutor'] = 'Co-tutor';
$string['assignmenttype_support'] = 'Support';
$string['assignmenttype_orientation'] = 'Orientation';
$string['assignmenttype_other'] = 'Other';
$string['assignmentstatus_active'] = 'Active';
$string['assignmentstatus_closed'] = 'Closed';
$string['assignmentstatus_cancelled'] = 'Cancelled';
$string['assignmentstatus_pending'] = 'Pending';
$string['assignmentsource_manual'] = 'Manual';
$string['assignmentsource_cohort'] = 'Cohort';
$string['assignmentsource_csv'] = 'CSV import';
$string['assignmentsource_external'] = 'External';
$string['assignmentsource_migration'] = 'Migration';

$string['privacy:metadata:assignment'] = 'Information about tutor-student assignments.';
$string['privacy:metadata:assignment:studentid'] = 'The student in the assignment.';
$string['privacy:metadata:assignment:tutorid'] = 'The tutor in the assignment.';
$string['privacy:metadata:assignment:cohortid'] = 'The cohort the assignment originated from, if any.';
$string['privacy:metadata:assignment:academicyearid'] = 'The academic year the assignment belongs to.';
$string['privacy:metadata:assignment:assignmenttype'] = 'The type of assignment (primary, co-tutor, etc.).';
$string['privacy:metadata:assignment:isprimary'] = 'Whether this is the primary tutor assignment.';
$string['privacy:metadata:assignment:status'] = 'The assignment status (active, closed, etc.).';
$string['privacy:metadata:assignment:timestart'] = 'When the assignment started.';
$string['privacy:metadata:assignment:timeend'] = 'When the assignment ended, if closed.';
$string['privacy:metadata:assignment:source'] = 'How the assignment was created (manual, cohort, CSV, etc.).';

$string['assignments'] = 'Assignments';
$string['assignment_detail_title'] = 'Assignment detail';
$string['assignment_history_title'] = 'Assignment history';

$string['filter_academicyear'] = 'Academic year';
$string['filter_tutor'] = 'Tutor';
$string['filter_student'] = 'Student';
$string['filter_cohort'] = 'Cohort';
$string['filter_assignmenttype'] = 'Assignment type';
$string['filter_status'] = 'Status';
$string['filter_source'] = 'Source';
$string['filter_timestartfrom'] = 'Start date from';
$string['filter_timestartto'] = 'Start date to';
$string['filter_timeendfrom'] = 'End date from';
$string['filter_timeendto'] = 'End date to';
$string['filter_apply'] = 'Apply filters';
$string['filter_all'] = 'All';

$string['assignment_col_student'] = 'Student';
$string['assignment_col_tutor'] = 'Tutor';
$string['assignment_col_cotutors'] = 'Co-tutors';
$string['assignment_col_cohort'] = 'Cohort';
$string['assignment_col_academicyear'] = 'Academic year';
$string['assignment_col_type'] = 'Type';
$string['assignment_col_timestart'] = 'Start date';
$string['assignment_col_timeend'] = 'End date';
$string['assignment_col_status'] = 'Status';
$string['assignment_col_source'] = 'Source';
$string['assignment_col_actions'] = 'Actions';
$string['assignment_viewdetail'] = 'View detail';

$string['assignment_createdby'] = 'Created by';
$string['assignment_modifiedby'] = 'Last modified by';

$string['assignment_upcoming'] = 'Upcoming';

$string['assignments_list_empty'] = 'No assignments match the selected filters.';
$string['assignment_history_empty'] = 'This student has no assignment history yet.';

$string['eventassignmentviewed'] = 'Assignment viewed';
$string['eventassignmentupdated'] = 'Assignment updated';

$string['assignment_create_title'] = 'New assignment';
$string['assignment_edit_title'] = 'Edit assignment';
$string['assignment_create'] = 'New assignment';
$string['assignment_edit'] = 'Edit';
$string['assignment_create_success'] = 'Assignment created.';
$string['assignment_update_success'] = 'Assignment updated.';
$string['assignment_field_note'] = 'Administrative note';
$string['assignment_field_editreason'] = 'Reason for the change';
$string['assignment_field_closereason'] = 'Reason for closing';
$string['assignment_field_closedate'] = 'Effective closing date';

$string['assignment_close'] = 'Close';
$string['assignment_close_title'] = 'Close assignment';
$string['assignment_close_confirm'] = 'Confirm closure';
$string['assignment_close_confirm_checkbox'] = 'I confirm I want to close this assignment.';
$string['assignment_close_success'] = 'Assignment closed.';
$string['assignment_close_success_no_primary'] = 'Assignment closed. The student is now left without an active primary tutor.';
$string['warning_assignment_close_no_primary'] = 'Closing this assignment will leave the student without an active primary tutor.';

$string['closereason_tutorchange'] = 'Change of tutor';
$string['closereason_groupchange'] = 'Change of group';
$string['closereason_levelchange'] = 'Change of level';
$string['closereason_endofyear'] = 'End of academic year';
$string['closereason_studentleft'] = 'Student left';
$string['closereason_tutorleft'] = 'Tutor left';
$string['closereason_adminerror'] = 'Administrative error';
$string['closereason_supportended'] = 'End of support or co-tutoring';
$string['closereason_other'] = 'Other';

$string['error_assignment_closed_no_permission'] = 'You do not have permission to edit a closed or cancelled assignment.';
$string['error_invalidacademicyearid'] = 'The requested academic year does not exist.';
$string['error_assignment_edit_reason_required'] = 'You must provide a reason to edit a closed or cancelled assignment.';
$string['error_assignment_close_reason_invalid'] = 'Invalid closing reason.';
$string['error_assignment_close_before_start'] = 'The closing date cannot be earlier than the start date.';
$string['error_assignment_close_not_confirmed'] = 'You must confirm the closure.';
$string['error_assignment_close_use_remove_cotutor'] = 'A co-tutor assignment is removed from co-tutor management, not from this page.';
$string['error_assignment_reassign_reason_invalid'] = 'Invalid reassignment reason.';
$string['error_assignment_reassign_conflict'] = 'This assignment was changed by another action while this reassignment was being processed. No changes were made; please check the current state and try again.';

$string['reassignreason_groupchange'] = 'Change of group';
$string['reassignreason_levelchange'] = 'Change of level';
$string['reassignreason_orgchange'] = 'Organisational change';
$string['reassignreason_tempsubstitution'] = 'Temporary substitution';
$string['reassignreason_tutorleft'] = 'Tutor left';
$string['reassignreason_reorganization'] = 'Reorganisation of tutoring';
$string['reassignreason_adminerror'] = 'Administrative error';
$string['reassignreason_coordinationrequest'] = 'Coordination request';
$string['reassignreason_other'] = 'Other';

$string['privacy:metadata:assignment:note'] = 'An optional administrative note about the assignment.';
$string['privacy:metadata:assignment:closereason'] = 'The coded reason why the assignment was closed.';

$string['eventcohortassignmentpreviewed'] = 'Cohort assignment preview generated';
$string['eventcohortassignmentapplied'] = 'Cohort assignment applied';
$string['eventcohortassignmentapplyfailed'] = 'Cohort assignment apply failed';

$string['error_cohort_mode_invalid'] = 'Invalid cohort assignment synchronisation mode.';
$string['error_cohort_same_tutor_cotutor'] = 'The primary tutor and the co-tutor cannot be the same person.';
$string['error_cohort_operation_not_usable'] = 'This operation is not a cohort assignment.';
$string['error_cohort_already_applied'] = 'This operation has already been applied or is in progress.';
$string['error_cohort_mode_preview_only_cannot_apply'] = '"Preview only" mode cannot be applied; generate a new preview with a different mode.';
$string['error_cohort_preview_changed'] = 'The underlying data has changed since this preview was generated. Generate a new one.';
$string['error_cohort_apply_row_failed'] = 'The operation could not be applied; nothing was written.';
$string['error_cohort_apply_not_confirmed'] = 'You must confirm that you want to apply this cohort assignment.';

$string['cohort_assignment_create'] = 'Assign by cohort';
$string['cohort_assignment_create_tip'] = 'Assign a tutor to every student in a cohort at once, with a preview before confirming.';
$string['cohort_assignment_manual_hint'] = 'This form creates a single manual assignment. The "Cohort" field is only a descriptive tag on that one assignment, not a bulk trigger. To assign a tutor to a whole cohort, use "Assign by cohort".';

$string['cohort_assignment_title'] = 'Cohort assignment';
$string['cohort_assignment_intro'] = 'Pick a cohort, an academic year and a tutor, preview the result and confirm to write the assignments.';

$string['cohort_assignment_field_cohort'] = 'Cohort';
$string['cohort_assignment_field_primarytutor'] = 'Primary tutor';
$string['cohort_assignment_field_cotutor'] = 'Co-tutor (optional)';
$string['cohort_assignment_field_mode'] = 'Synchronisation mode';
$string['cohort_assignment_field_mode_help'] = 'Add assignments: creates tutoring assignments for students who do not have one yet, without touching existing ones. Add and close missing: also closes the assignments of students no longer in the cohort. Replace primary tutor: reassigns students who already have one to a new tutor (the highest-impact action). Preview only: shows the result without being able to confirm it.';
$string['cohort_assignment_field_includesuspended'] = 'Include students with a suspended account';
$string['cohort_assignment_field_allowsuspendedtutor'] = 'Allow a tutor or co-tutor with a suspended account';
$string['cohort_assignment_preview_button'] = 'Preview';

$string['cohort_assignment_mode_add_only'] = 'Add assignments';
$string['cohort_assignment_mode_add_and_close_missing'] = 'Add and close missing';
$string['cohort_assignment_mode_replace_primary'] = 'Replace primary tutor';
$string['cohort_assignment_mode_preview_only'] = 'Preview only';

$string['cohort_assignment_preview_summary_title'] = 'Preview summary';
$string['cohort_assignment_summary_total'] = 'Students analysed: {$a}';
$string['cohort_assignment_summary_tocreate'] = 'New assignments: {$a}';
$string['cohort_assignment_summary_toreassign'] = 'Reassignments: {$a}';
$string['cohort_assignment_summary_tocreatecotutor'] = 'New co-tutors: {$a}';
$string['cohort_assignment_summary_toclose'] = 'Assignments to close (departed students): {$a}';
$string['cohort_assignment_summary_nochange'] = 'No change: {$a}';
$string['cohort_assignment_summary_skipped'] = 'Skipped: {$a}';
$string['cohort_assignment_summary_suspended'] = 'Suspended accounts: {$a}';
$string['cohort_assignment_summary_conflicts'] = 'Conflicts detected: {$a}';
$string['cohort_assignment_conflicts_warning'] = 'Data conflicts were detected for some students; review them before confirming.';
$string['cohort_assignment_preview_empty'] = 'The selected cohort has no members.';

$string['cohort_assignment_col_action'] = 'Action';
$string['cohort_assignment_col_currenttutor'] = 'Current tutor';
$string['cohort_assignment_col_cotutoraction'] = 'Co-tutor action';
$string['cohort_assignment_col_conflicts'] = 'Conflicts';

$string['cohort_assignment_apply_title'] = 'Confirm apply';
$string['cohort_assignment_apply_intro'] = 'Confirming will actually write the assignments described above. This action cannot be undone from here.';
$string['cohort_assignment_apply_confirm_checkbox'] = 'I confirm that I want to apply this cohort assignment.';
$string['cohort_assignment_apply_button'] = 'Apply';
$string['cohort_assignment_apply_result_title'] = 'Apply result';
$string['cohort_assignment_apply_success'] = 'Cohort assignment applied successfully.';
$string['cohort_assignment_apply_created'] = 'Assignments created: {$a}';
$string['cohort_assignment_apply_reassigned'] = 'Reassignments made: {$a}';
$string['cohort_assignment_apply_closed'] = 'Assignments closed: {$a}';
$string['cohort_assignment_apply_nochange'] = 'No change: {$a}';
$string['cohort_assignment_apply_skipped'] = 'Skipped: {$a}';
$string['cohort_assignment_apply_result_empty'] = 'There are no results to show.';

$string['cohort_action_create_primary'] = 'Create primary tutor';
$string['cohort_action_create_cotutor'] = 'Create co-tutor';
$string['cohort_action_reassign_primary'] = 'Reassign primary tutor';
$string['cohort_action_close_missing'] = 'Close (departed)';
$string['cohort_action_no_change'] = 'No change';
$string['cohort_action_skip_existing'] = 'Skipped (already has a tutor)';
$string['cohort_action_skip_suspended'] = 'Skipped (suspended)';
$string['cohort_action_skip_invalid'] = 'Skipped (invalid)';
$string['cohort_action_conflict_primary'] = 'Conflict';
$string['cohort_action_error'] = 'Error';

$string['conflictcode_multipleactiveprimary'] = 'More than one active primary tutor at once';
$string['conflictcode_overlappingfuture'] = 'More than one overlapping future assignment';
$string['conflictcode_duplicatehistorical'] = 'Overlapping historical assignments';
$string['conflictcode_deletedtutoractive'] = 'The active assignment\'s tutor account is deleted';

$string['privacy:metadata:bulkoperation'] = 'Cohort-based bulk assignment operations';
$string['privacy:metadata:bulkoperation:cohortid'] = 'The cohort used as the student population source.';
$string['privacy:metadata:bulkoperation:academicyearid'] = 'The academic year the operation applies to.';
$string['privacy:metadata:bulkoperation:primarytutorid'] = 'The tutor selected as primary tutor for the operation.';
$string['privacy:metadata:bulkoperation:cotutorid'] = 'The tutor selected as co-tutor for the operation, if any.';
$string['privacy:metadata:bulkoperation:mode'] = 'The synchronisation mode used for the operation.';

$string['eventcsvimportpreviewed'] = 'CSV import preview generated';

$string['csv_import_title'] = 'Import assignments from CSV';
$string['csv_import_intro'] = 'Upload a CSV file to preview which tutor-student assignments it would create. Nothing is applied yet — this only shows a preview.';
$string['csv_field_file'] = 'CSV file';
$string['csv_field_delimiter'] = 'Delimiter';
$string['csv_delimiter_comma'] = 'Comma (,)';
$string['csv_delimiter_semicolon'] = 'Semicolon (;)';
$string['csv_delimiter_tab'] = 'Tab';
$string['csv_field_encoding'] = 'File encoding';
$string['csv_upload_preview'] = 'Preview';
$string['csv_preview_summary_title'] = 'Preview summary';
$string['csv_summary_total'] = 'Rows analysed: {$a}';
$string['csv_summary_valid'] = 'Valid: {$a}';
$string['csv_summary_warning'] = 'With warnings: {$a}';
$string['csv_summary_conflict'] = 'Conflicts: {$a}';
$string['csv_summary_error'] = 'Errors: {$a}';
$string['csv_summary_excluded'] = 'Excluded: {$a}';
$string['csv_col_row'] = 'Row';
$string['csv_col_status'] = 'Status';
$string['csv_col_messages'] = 'Messages';
$string['csv_preview_empty'] = 'The file has no data rows to preview.';
$string['csv_exclude_title'] = 'Exclude rows';
$string['csv_exclude_intro'] = 'Tick any rows you want to exclude, then recalculate the preview.';
$string['csv_row_label'] = 'Exclude row {$a}';
$string['csv_recalculate_preview'] = 'Recalculate preview';
$string['csv_apply_not_available_yet'] = 'Applying this import is not available yet — this phase only previews the file.';

$string['csv_status_valid'] = 'Valid';
$string['csv_status_warning'] = 'Warning';
$string['csv_status_conflict'] = 'Conflict';
$string['csv_status_error'] = 'Error';
$string['csv_status_excluded'] = 'Excluded';

$string['csv_message_empty_file'] = 'The file is empty.';
$string['csv_message_missing_required_header'] = 'A required column header is missing.';
$string['csv_message_unknown_column'] = 'The file contains a column that is not recognised.';
$string['csv_message_column_count_mismatch'] = 'This row does not have the expected number of columns.';
$string['csv_message_missing_student'] = 'The student column is empty.';
$string['csv_message_missing_tutor'] = 'The tutor column is empty.';
$string['csv_message_missing_academicyear'] = 'The academic year column is empty.';
$string['csv_message_invalid_isprimary'] = 'The "primary tutor" column must be 0 or 1.';
$string['csv_message_invalid_timestart'] = 'The start date is not a valid date (YYYY-MM-DD).';
$string['csv_message_invalid_timeend'] = 'The end date is not a valid date (YYYY-MM-DD).';
$string['csv_message_invalid_assignmenttype'] = 'The assignment type is not recognised.';
$string['csv_message_invalid_source'] = 'The source is not recognised.';
$string['csv_message_duplicate_row'] = 'This row repeats an earlier row in the same file.';
$string['csv_message_student_not_found'] = 'No matching student account was found (by email, username or ID number).';
$string['csv_message_student_suspended'] = 'The student account is suspended.';
$string['csv_message_student_self_tutor'] = 'The student and the tutor cannot be the same person.';
$string['csv_message_tutor_not_found'] = 'No matching tutor account was found (by email, username or ID number).';
$string['csv_message_tutor_suspended'] = 'The tutor account is suspended.';
$string['csv_message_academicyear_not_found'] = 'No academic year matches this short name.';
$string['csv_message_academicyear_locked'] = 'This academic year is locked for new assignments.';
$string['csv_message_cohort_not_found'] = 'No cohort matches this identifier; the assignment would be created without a cohort.';
$string['csv_message_duplicate_active'] = 'An identical active assignment already exists.';
$string['csv_message_primary_conflict'] = 'This student already has an active primary tutor.';
$string['csv_message_row_excluded'] = 'Manually excluded.';

$string['error_csv_file_not_usable'] = 'The file could not be read, or has no usable rows. Check the headers and try again.';
$string['error_csv_invalid_parameters'] = 'Invalid or missing import parameters.';

$string['eventcsvimportqueued'] = 'CSV import queued for background processing';
$string['eventcsvimportstarted'] = 'CSV import started';
$string['eventcsvimportcompleted'] = 'CSV import completed';
$string['eventcsvimportcompletedwitherrors'] = 'CSV import completed with errors';
$string['eventcsvimportfailed'] = 'CSV import failed';

$string['csv_field_strategy'] = 'Application strategy';
$string['csv_strategy_partial_valid'] = 'Apply valid rows, record errors per row (recommended)';
$string['csv_strategy_atomic_all'] = 'All or nothing: one failing row cancels the whole batch';
$string['csv_field_allow_reassign'] = 'Reassign conflicting primary tutors';
$string['csv_field_allow_reassign_help'] = 'When a row conflicts with an existing, different active primary tutor, this option reassigns the student to the tutor in the file instead of skipping the row. Duplicate rows (the exact same assignment already exists) are never affected by this option.';
$string['csv_apply_confirm_checkbox'] = 'I confirm I want to apply this import.';
$string['csv_apply_button'] = 'Apply import';
$string['csv_apply_title'] = 'Apply this import';
$string['csv_apply_intro'] = 'This creates or reassigns real assignments based on the preview above. This cannot be undone from this page.';
$string['csv_apply_result_title'] = 'Import result';
$string['csv_apply_created'] = 'Created: {$a}';
$string['csv_apply_reassigned'] = 'Reassigned: {$a}';
$string['csv_apply_nochange'] = 'Already up to date: {$a}';
$string['csv_apply_skipped'] = 'Skipped: {$a}';
$string['csv_apply_failed'] = 'Failed: {$a}';
$string['csv_apply_status_completed'] = 'Import completed successfully.';
$string['csv_apply_status_completed_with_errors'] = 'Import completed, but some rows failed. See the counts above.';
$string['csv_apply_status_failed'] = 'Import failed and was rolled back — no changes were made.';

$string['error_csv_apply_strategy_invalid'] = 'Invalid application strategy.';
$string['error_csv_already_applied'] = 'This import has already been applied.';
$string['error_csv_preview_changed'] = 'The file or the underlying data changed since the preview was generated. Generate a new preview and try again.';
$string['error_csv_apply_row_failed'] = 'This row could not be applied.';
$string['error_csv_apply_not_confirmed'] = 'You must confirm before applying the import.';

$string['csv_col_outcome'] = 'Outcome';
$string['csv_apply_result_empty'] = 'This import did not produce any processed rows.';
$string['csv_apply_outcome_created'] = 'Created';
$string['csv_apply_outcome_reassigned'] = 'Reassigned';
$string['csv_apply_outcome_no_change'] = 'No change';
$string['csv_apply_outcome_skipped_conflict'] = 'Skipped (conflict)';
$string['csv_apply_outcome_skipped_error'] = 'Skipped (error)';
$string['csv_apply_outcome_skipped_excluded'] = 'Skipped (excluded)';
$string['csv_apply_outcome_failed'] = 'Failed';

$string['csv_apply_deferred'] = 'This file has many rows and is being applied in the background by a scheduled task. Nothing is applied yet on this page; check the event log later for the result.';
$string['csv_report_download'] = 'Download report of rows not applied (CSV)';
$string['error_csv_report_not_available'] = 'The report is no longer available. It can only be downloaded once, immediately after applying the import.';

$string['eventcsverrorreportdownloaded'] = 'CSV import error report downloaded';
$string['task_process_csv_import'] = 'Apply a large CSV import in the background';
$string['task_cleanup_bulk_operations'] = 'Clean up abandoned bulk operations and temporary files';

$string['privacy:metadata:csvimportfiles'] = 'The CSV file of a large import, copied temporarily so the background task can read it; removed as soon as it is processed or, at the latest, on the next scheduled cleanup.';

$string['student_summary_title'] = 'Student file';
$string['student_viewficha'] = 'View file';
$string['student_field_primarytutor'] = 'Primary tutor';
$string['student_field_cotutors'] = 'Co-tutors';
$string['student_field_lastassignment'] = 'Last assignment';
$string['student_field_upcoming'] = 'Upcoming changes';
$string['student_summary_no_primary'] = 'No active primary tutor for this academic year.';
$string['student_summary_no_cotutors'] = 'No active co-tutors.';
$string['student_summary_no_assignments'] = 'No assignments in this academic year.';
$string['student_summary_no_upcoming'] = 'No upcoming changes scheduled.';
$string['studenttab_summary'] = 'Summary';
$string['studenttab_history'] = 'History';
$string['studenttab_tutoring'] = 'Tutoring';
$string['studenttab_agreements'] = 'Agreements';
$string['studenttab_tutoring_empty'] = 'Tutoring history is not available yet — coming in a later phase.';
$string['studenttab_agreements_empty'] = 'Agreements are not available yet — coming in a later phase.';
$string['student_history_col_reason'] = 'Reason';
$string['privacy:metadata:assignment:reassignreason'] = 'The coded reason recorded when this assignment was created by reassigning the student\'s primary tutor.';

// Phase 5.1 — tutoring entries: domain and data.
$string['monlaututoria:viewstudentvisiblecontent'] = 'View tutoring content shared with the student';
$string['monlaututoria:viewinternalnotes'] = 'View internal tutoring notes';
$string['monlaututoria:viewrestrictednotes'] = 'View restricted tutoring notes';

$string['entrystatus_active'] = 'Active';
$string['entrystatus_annulled'] = 'Annulled';

$string['entryparticipanttype_family'] = 'Family';
$string['entryparticipanttype_orientation'] = 'Orientation';
$string['entryparticipanttype_company'] = 'Company';
$string['entryparticipanttype_teacher'] = 'Teaching staff';
$string['entryparticipanttype_other'] = 'Other';

$string['error_entry_followup_before_entrydate'] = 'The next follow-up date cannot be before the entry date.';
$string['error_entry_modality_invalid'] = 'The selected modality does not exist or is not active.';
$string['error_entry_reason_invalid'] = 'One of the selected reasons does not exist or is not active.';
$string['error_entry_participant_type_invalid'] = 'Invalid participant type.';
$string['error_entry_participant_identity_invalid'] = 'Each participant must specify exactly one internal user or external name, never both nor neither.';
$string['error_entry_participant_user_invalid'] = 'The selected participant user does not exist or has been deleted.';

$string['evententrycreated'] = 'Tutoring entry created';

$string['privacy:metadata:entry'] = 'Information about tutoring entries: student, responsible tutor, academic year, date, modality, shared content, internal and restricted notes.';
$string['privacy:metadata:entry:studentid'] = 'The id of the student this entry is about.';
$string['privacy:metadata:entry:tutorid'] = 'The id of the tutor responsible for this entry.';
$string['privacy:metadata:entry:academicyearid'] = 'The academic year of this entry.';
$string['privacy:metadata:entry:entrydate'] = 'The real date the tutoring actuation took place.';
$string['privacy:metadata:entry:modalityid'] = 'The contact modality of this entry.';
$string['privacy:metadata:entry:contentvisible'] = 'The entry content shared with the student.';
$string['privacy:metadata:entry:noteinternal'] = 'The internal note of this entry, not shown to the student.';
$string['privacy:metadata:entry:noterestricted'] = 'The restricted note of this entry, the most sensitive tier.';
$string['privacy:metadata:entry:status'] = 'The status of this entry (active or annulled).';
$string['privacy:metadata:entry:nextfollowupdate'] = 'The next follow-up date, if one was set.';
$string['privacy:metadata:entryparticipant'] = 'Information about a tutoring entry\'s participants, internal (Moodle users) or external.';
$string['privacy:metadata:entryparticipant:participanttype'] = 'The participant type (family, orientation, company, teaching staff, other).';
$string['privacy:metadata:entryparticipant:userid'] = 'The id of the participant, when they are a Moodle user.';
$string['privacy:metadata:entryparticipant:externalname'] = 'The name of the participant, when they are not a Moodle user.';
$string['privacy:metadata:entryversion'] = 'Snapshots of a tutoring entry\'s content taken before each edit or annulment.';
$string['privacy:metadata:entryversion:versionnumber'] = 'The sequence number of this snapshot within its entry.';
$string['privacy:metadata:entryversion:snapshotjson'] = 'The entry\'s editable fields as they were immediately before this edit.';
$string['privacy:metadata:entryversion:changereason'] = 'The reason given for this edit or annulment, if any.';
$string['privacy:metadata:entryattachment'] = 'Metadata about files attached to a tutoring entry: document category and description.';
$string['privacy:metadata:entryattachment:category'] = 'The document category of the attachment (report, consent, evidence, other).';
$string['privacy:metadata:entryattachment:description'] = 'The description given for the attachment, if any.';
$string['privacy:metadata:entryattachmentfiles'] = 'The tutoring entry attachment files themselves.';

// Phase 5.2 — quick tutoring entry registration.
$string['monlaututoria:createentry'] = 'Register a tutoring entry';
$string['entry_field_entrydate'] = 'Entry date';
$string['entry_field_modality'] = 'Modality';
$string['entry_field_reason'] = 'Reason';
$string['entry_field_contentvisible'] = 'Comment shared with the student';
$string['entry_field_noteinternal'] = 'Internal note';
$string['entry_field_noteinternal_help'] = 'Only visible to tutors and coordination. The student never sees this note, however they view the entry.';
$string['entry_field_nextfollowupdate'] = 'Next follow-up';
$string['entry_register'] = 'Register entry';
$string['entry_register_title'] = 'Register tutoring entry — {$a}';
$string['entry_register_success'] = 'Tutoring entry registered successfully.';
$string['entry_pick_student_title'] = 'Choose a student';
$string['entry_pick_student_intro'] = 'Choose one of your students to register a new tutoring entry.';
$string['entry_pick_student_label'] = 'Student';
$string['entry_pick_student_empty'] = 'You have no students with a current primary tutoring assignment in the active academic year.';

// Phase 5.3 — full tutoring entry registration.
$string['entry_full_register'] = 'Full registration';
$string['entry_full_register_title'] = 'Full tutoring entry registration — {$a}';
$string['entry_field_reasons'] = 'Reasons';
$string['entry_field_noterestricted'] = 'Restricted note';
$string['entry_field_visibilitytier'] = 'Visibility';
$string['entry_field_participanttype'] = 'Participant type';
$string['entry_field_participantuser'] = 'Internal participant (user)';
$string['entry_field_participantexternalname'] = 'External participant (name)';
$string['entry_participants_header'] = 'Participants';
$string['entry_participant_addmore'] = 'Add another participant';

// Phase 5.4 — tutoring entry history and detail.
$string['entry_history_empty'] = 'No tutoring entries recorded for this academic year with these filters.';
$string['entry_viewdetail'] = 'View detail';
$string['entry_detail_title'] = 'Tutoring entry detail';

// Phase 5.5 — editing, versioning and annulment.
$string['monlaututoria:editownentry'] = 'Edit own tutoring entries';
$string['monlaututoria:editanyentry'] = 'Edit any tutoring entry';
$string['monlaututoria:annulentry'] = 'Annul a tutoring entry';
$string['setting_entryeditwindow'] = 'Entry edit window';
$string['setting_entryeditwindow_desc'] = 'How long after a tutoring entry is recorded it can be edited without giving a reason. Past this window, any edit requires a change reason.';
$string['setting_dashboard_showreferrals'] = 'Show referrals on the tutor dashboard';
$string['setting_dashboard_showreferrals_desc'] = 'Shows the referrals card and section on the tutor dashboard. Turning this off does not delete or hide referrals themselves — it only stops showing them there; the referral management screen keeps working exactly the same.';
$string['setting_dashboard_showpriority'] = 'Show priority students on the tutor dashboard';
$string['setting_dashboard_showpriority_desc'] = 'Shows the priority-student card, column and section (an automatic calculation, not something a tutor sets) on the tutor dashboard and the block. Turning this off does not change the calculation, only whether it is shown.';
$string['entry_edit_title'] = 'Edit tutoring entry';
$string['entry_edit_success'] = 'Tutoring entry updated successfully.';
$string['entry_field_editreason'] = 'Reason for change';
$string['error_entry_edit_reason_required'] = 'The edit window has passed without a reason — state the reason for this change.';
$string['error_entry_already_annulled'] = 'This tutoring entry is already annulled.';
$string['entry_annul_title'] = 'Annul tutoring entry';
$string['entry_annul_success'] = 'Tutoring entry annulled successfully.';
$string['entry_field_annulreason'] = 'Reason for annulment';
$string['entry_annul_confirm_checkbox'] = 'I confirm I want to annul this tutoring entry.';
$string['entry_annul_confirm'] = 'Confirm annulment';
$string['error_entry_annul_reason_required'] = 'State the reason for the annulment.';
$string['error_entry_annul_not_confirmed'] = 'You must confirm you want to annul this tutoring entry.';
$string['evententryupdated'] = 'Tutoring entry updated';
$string['evententryannulled'] = 'Tutoring entry annulled';

// Phase 5.6 — tutoring entry attachments.
$string['entryattachmentcategory_report'] = 'Report';
$string['entryattachmentcategory_consent'] = 'Consent';
$string['entryattachmentcategory_evidence'] = 'Evidence';
$string['entryattachmentcategory_other'] = 'Other';
$string['entry_attachment_category'] = 'Document category';
$string['entry_attachment_files'] = 'Files';
$string['entry_attachment_upload'] = 'Upload files';
$string['entry_attachments_title'] = 'Tutoring entry attachments';
$string['entry_attachment_upload_success'] = '{$a} file(s) uploaded successfully.';
$string['entry_attachments_empty'] = 'This tutoring entry has no attachments yet.';
$string['error_entry_attachment_category_invalid'] = 'Invalid document category.';

// Phase 6.1/6.3 — agreements.
$string['monlaututoria:createagreement'] = 'Create an agreement';
$string['monlaututoria:manageagreements'] = 'Complete, reopen, postpone or cancel agreements';
$string['priority_low'] = 'Low';
$string['priority_medium'] = 'Medium';
$string['priority_high'] = 'High';
$string['agreementstatus_pending'] = 'Pending';
$string['agreementstatus_in_progress'] = 'In progress';
$string['agreementstatus_completed'] = 'Completed';
$string['agreementstatus_cancelled'] = 'Cancelled';
$string['agreementstatus_overdue'] = 'Overdue';
$string['agreementresponsibletype_student'] = 'Student';
$string['agreementresponsibletype_tutor'] = 'Tutor';
$string['agreementresponsibletype_family'] = 'Family';
$string['agreementresponsibletype_teacher'] = 'Teaching staff';
$string['agreementresponsibletype_coordination'] = 'Coordination';
$string['agreementresponsibletype_orientation'] = 'Orientation';
$string['agreementresponsibletype_company'] = 'Company';
$string['agreementresponsibletype_other'] = 'Other';
$string['agreement_field_description'] = 'Description';
$string['agreement_field_responsibletype'] = 'Responsible type';
$string['agreement_field_responsibleuser'] = 'Responsible (Moodle user)';
$string['agreement_field_responsibleexternalname'] = 'Responsible (external name)';
$string['agreement_field_duedate'] = 'Due date';
$string['agreement_field_visibletostudent'] = 'Visible to the student';
$string['agreement_field_status'] = 'Status';
$string['agreement_create'] = 'Create agreement';
$string['agreement_create_title'] = 'Create agreement — {$a}';
$string['agreement_create_success'] = 'Agreement created successfully.';
$string['agreement_complete'] = 'Mark completed';
$string['agreement_reopen'] = 'Reopen';
$string['agreement_cancel'] = 'Cancel agreement';
$string['agreement_postpone'] = 'Postpone';
$string['agreement_postpone_title'] = 'Postpone agreement';
$string['agreement_action_success'] = 'Agreement updated successfully.';
$string['agreement_confirm_cancel'] = 'Cancel this agreement? This cannot be undone from here.';
$string['agreements_empty'] = 'No agreements have been created yet.';
$string['agreements_filter_overdue'] = 'Overdue only';
$string['error_agreement_responsible_type_invalid'] = 'Invalid responsible type.';
$string['error_agreement_responsible_identity_invalid'] = 'Select exactly one responsible: a Moodle user or an external name.';
$string['error_agreement_responsible_user_invalid'] = 'The selected responsible user does not exist or has been deleted.';
$string['error_agreement_invalid_transition'] = 'This action cannot be applied to the agreement in its current status.';
$string['error_agreement_cannot_postpone_closed'] = 'A completed or cancelled agreement cannot be postponed.';
$string['eventagreementcreated'] = 'Agreement created';
$string['eventagreementupdated'] = 'Agreement updated';

// Phase 6.2/6.3 — follow-ups.
$string['monlaututoria:createfollowup'] = 'Create a follow-up';
$string['monlaututoria:managefollowups'] = 'Complete, reopen, postpone or cancel follow-ups';
$string['followupstatus_pending'] = 'Pending';
$string['followupstatus_completed'] = 'Completed';
$string['followupstatus_cancelled'] = 'Cancelled';
$string['followupstatus_overdue'] = 'Overdue';
$string['followup_field_duedate'] = 'Expected date';
$string['followup_field_priority'] = 'Priority';
$string['followup_field_status'] = 'Status';
$string['followup_create'] = 'Create follow-up';
$string['followup_create_title'] = 'Create follow-up — {$a}';
$string['followup_create_success'] = 'Follow-up created successfully.';
$string['followup_complete'] = 'Mark completed';
$string['followup_reopen'] = 'Reopen';
$string['followup_cancel'] = 'Cancel follow-up';
$string['followup_action_success'] = 'Follow-up updated successfully.';
$string['followup_confirm_cancel'] = 'Cancel this follow-up? This cannot be undone from here.';
$string['followups_empty'] = 'No follow-ups have been created yet.';
$string['followups_filter_overdue'] = 'Overdue only';
$string['studenttab_followups'] = 'Follow-ups';
$string['error_followup_priority_invalid'] = 'Invalid priority.';
$string['error_followup_invalid_transition'] = 'This action cannot be applied to the follow-up in its current status.';
$string['error_followup_cannot_postpone_closed'] = 'A completed or cancelled follow-up cannot be postponed.';
$string['eventfollowupcreated'] = 'Follow-up created';
$string['eventfollowupupdated'] = 'Follow-up updated';
$string['entry_field_followup'] = 'Closes follow-up';

// Phase 6.4 — referrals.
$string['monlaututoria:createreferral'] = 'Create a referral';
$string['monlaututoria:managereferrals'] = 'View, assign, resolve or cancel any referral';
$string['referraldestination_coordination'] = 'Coordination';
$string['referraldestination_orientation'] = 'Orientation';
$string['referraldestination_management'] = 'Management';
$string['referralstatus_pending'] = 'Pending';
$string['referralstatus_in_progress'] = 'In progress';
$string['referralstatus_resolved'] = 'Resolved';
$string['referralstatus_cancelled'] = 'Cancelled';
$string['referral_field_destination'] = 'Destination';
$string['referral_field_reason'] = 'Reason';
$string['referral_field_status'] = 'Status';
$string['referral_field_assignedto'] = 'Assigned to';
$string['referral_field_resolution'] = 'Resolution';
$string['referral_field_originentry'] = 'Origin tutoring entry';
$string['referral_create'] = 'Refer';
$string['referral_create_title'] = 'Create referral — {$a}';
$string['referral_create_success'] = 'Referral created successfully.';
$string['referral_assign'] = 'Assign';
$string['referral_resolve'] = 'Resolve';
$string['referral_cancel'] = 'Cancel referral';
$string['referral_confirm_cancel'] = 'Cancel this referral? This cannot be undone from here.';
$string['referral_action_success'] = 'Referral updated successfully.';
$string['referral_viewdetail'] = 'View detail';
$string['referral_detail_title'] = 'Referral detail';
$string['referrals_title'] = 'Referrals';
$string['referrals_empty'] = 'No referrals have been created yet.';
$string['error_referral_destination_invalid'] = 'Invalid destination.';
$string['error_referral_priority_invalid'] = 'Invalid priority.';
$string['error_referral_reason_required'] = 'State the reason for the referral.';
$string['error_referral_resolution_required'] = 'State the resolution.';
$string['error_referral_invalid_transition'] = 'This action cannot be applied to the referral in its current status.';
$string['eventreferralcreated'] = 'Referral created';
$string['eventreferralupdated'] = 'Referral updated';

// Phase 6.5 — Privacy API for agreements/follow-ups/referrals.
$string['privacy:metadata:agreement'] = 'Information about agreements: origin entry, description, responsible party, due date, status, visibility to the student.';
$string['privacy:metadata:agreement:studentid'] = 'The id of the student this agreement concerns.';
$string['privacy:metadata:agreement:description'] = 'The description of the agreement.';
$string['privacy:metadata:agreement:responsibletype'] = 'The type of the responsible party.';
$string['privacy:metadata:agreement:responsibleuserid'] = 'The id of the responsible party, when they are a Moodle user.';
$string['privacy:metadata:agreement:responsibleexternalname'] = 'The name of the responsible party, when they are not a Moodle user.';
$string['privacy:metadata:agreement:duedate'] = 'The due date of the agreement.';
$string['privacy:metadata:agreement:status'] = 'The status of the agreement.';
$string['privacy:metadata:agreement:visibletostudent'] = 'Whether this agreement is visible to the student.';
$string['privacy:metadata:followup'] = 'Information about follow-ups: origin entry, due date, priority, status, closing entry.';
$string['privacy:metadata:followup:studentid'] = 'The id of the student this follow-up concerns.';
$string['privacy:metadata:followup:duedate'] = 'The expected date of the follow-up.';
$string['privacy:metadata:followup:priority'] = 'The priority of the follow-up.';
$string['privacy:metadata:followup:status'] = 'The status of the follow-up.';
$string['privacy:metadata:followup:closingentryid'] = 'The id of the tutoring entry that closed this follow-up, if any.';
$string['privacy:metadata:referral'] = 'Information about referrals: origin entry, destination, reason, priority, assignee, status, resolution.';
$string['privacy:metadata:referral:studentid'] = 'The id of the student this referral concerns.';
$string['privacy:metadata:referral:destination'] = 'The destination of the referral.';
$string['privacy:metadata:referral:reason'] = 'The reason given for the referral.';
$string['privacy:metadata:referral:priority'] = 'The priority of the referral.';
$string['privacy:metadata:referral:assignedto'] = 'The id of the staff member handling the referral, if assigned.';
$string['privacy:metadata:referral:status'] = 'The status of the referral.';
$string['privacy:metadata:referral:resolution'] = 'The resolution given for the referral, if resolved.';
$string['evententryattachmentadded'] = 'Attachment added to a tutoring entry';

$string['dashboard_title'] = 'Tutor dashboard';
$string['dashboard_summary_assigned'] = 'Assigned students';
$string['dashboard_summary_attended'] = 'With at least one tutoring entry';
$string['dashboard_summary_pendinginitial'] = 'Pending first tutoring entry';
$string['dashboard_summary_coverage'] = 'Coverage';
$string['dashboard_students_empty'] = 'You have no current primary students assigned in this academic year.';
$string['dashboard_col_lastentry'] = 'Latest tutoring entry';
$string['dashboard_col_entrycount'] = 'Entries this year';
$string['dashboard_col_missinginitial'] = 'Missing initial tutoring entry';
$string['dashboard_col_coverage'] = 'Coverage status';
$string['dashboard_coveragestatus_covered'] = 'Covered';
$string['dashboard_coveragestatus_pending_initial'] = 'Pending first tutoring entry';

$string['dashboard_summary_followupsoverdue'] = 'Overdue follow-ups';
$string['dashboard_summary_agreementspending'] = 'Pending agreements';
$string['dashboard_summary_referrals'] = 'Referral cases';
$string['dashboard_summary_priority'] = 'Priority students';
$string['dashboard_summary_familycontacts'] = 'Family contacts';
$string['dashboard_studentfilter_all'] = 'All students';
$string['dashboard_studentfilter_pendinginitial'] = 'Only missing initial tutoring';
$string['dashboard_studentfilter_withpending'] = 'Only with pending work';
$string['dashboard_studentfilter_priority'] = 'Only priority students';
$string['dashboard_pendingfilter_all'] = 'All pending items';
$string['dashboard_pendingfilter_open'] = 'Only open items';
$string['dashboard_pendingfilter_overdue'] = 'Only overdue items';
$string['dashboard_section_students'] = 'My students';
$string['dashboard_view_roster'] = 'My students';
$string['dashboard_view_pending'] = 'Pending work';
$string['dashboard_roster_entrycount'] = '{$a} tutoring entries';
$string['dashboard_roster_lastentry'] = 'Latest: {$a}';
$string['dashboard_roster_noentry'] = 'No tutoring entry yet';
$string['dashboard_section_followups'] = 'Follow-ups';
$string['dashboard_section_agreements'] = 'Agreements';
$string['dashboard_section_referrals'] = 'Referral cases';
$string['dashboard_section_priority'] = 'Priority students';
$string['dashboard_col_pendingbundle'] = 'Pending items';
$string['dashboard_col_priority'] = 'Priority';
$string['dashboard_action_viewstudent'] = 'View file';
$string['dashboard_action_createentry'] = 'Register tutoring';
$string['dashboard_action_createfollowup'] = 'Create follow-up';
$string['dashboard_followups_empty'] = 'There are no follow-ups to show for the current filter.';
$string['dashboard_agreements_empty'] = 'There are no agreements to show for the current filter.';
$string['dashboard_priority_empty'] = 'There are no priority students for the current filter.';

$string['monlaututoria:viewcoordinationdashboard'] = 'View coordination dashboard';
$string['monlaututoria:exportcoordinationreports'] = 'Export coordination reports';
$string['monlaututoria:managecoordinationscopes'] = 'Manage coordination scopes';
$string['coordination_title'] = 'Coordination dashboard';
$string['coordination_intro'] = 'Analyse coverage, quality and the breakdown of tutoring entries by academic year, cohort or tutor.';
$string['coordination_scopes_title'] = 'Coordination scopes';
$string['cohort_visibility_title'] = 'Enabled cohorts';
$string['cohort_visibility_intro'] = 'Choose which Moodle cohorts are relevant to Monlau Tutoría. Only the ones ticked here will appear in assignment creation, cohort-based bulk assignment and the coordination dashboard. Untick the ones you want to hide (e.g. staff cohorts). Note: if none end up ticked when you save, that is treated as "unrestricted" and every cohort shows again — keep at least one ticked to actually hide the rest.';
$string['cohort_visibility_empty'] = 'There are no cohorts created on this site yet.';
$string['cohort_visibility_save'] = 'Save enabled cohorts';
$string['cohort_visibility_saved'] = 'Enabled cohorts updated successfully.';
$string['coordination_dashboard_noscope'] = 'You do not have any coordination scope assigned.';
$string['coordination_dashboard_empty'] = 'There is no data for the current filters.';
$string['coordination_generatedat'] = 'Generated at: {$a}';
$string['coordination_cohort_all'] = 'All my cohorts';
$string['coordination_tutor_all'] = 'All tutors';
$string['coordination_export_csv'] = 'Export CSV';
$string['coordination_export_xlsx'] = 'Export spreadsheet';
$string['coordination_export_summary'] = 'Summary';
$string['coordination_export_column_section'] = 'Section';
$string['coordination_export_column_label'] = 'Label';
$string['coordination_export_column_generatedat'] = 'Generated at';
$string['coordination_export_column_format'] = 'Format';
$string['coordination_summary_population'] = 'Analysed population';
$string['coordination_summary_withinitial'] = 'With initial tutoring';
$string['coordination_summary_withoutentry'] = 'Without any tutoring entry';
$string['coordination_summary_overduefollowups'] = 'Overdue follow-ups';
$string['coordination_summary_opencases'] = 'Open cases';
$string['coordination_quality_title'] = 'Quality indicators';
$string['coordination_quality_timetofirst'] = 'Time to first tutoring';
$string['coordination_quality_agreements'] = 'Completed agreements';
$string['coordination_quality_followups'] = 'On-time follow-ups';
$string['coordination_quality_familycontacts'] = 'Family contacts';
$string['coordination_quality_continuity'] = 'Continuity after tutor change';
$string['coordination_breakdown_cohorts'] = 'Group view';
$string['coordination_breakdown_tutors'] = 'Tutor view';
$string['coordination_breakdown_label'] = 'Scope';
$string['coordination_breakdown_population'] = 'Students';
$string['coordination_breakdown_withinitial'] = 'With initial';
$string['coordination_breakdown_withoutentry'] = 'Without entries';
$string['coordination_breakdown_overduefollowups'] = 'Overdue follow-ups';
$string['coordination_breakdown_opencases'] = 'Open cases';
$string['coordination_breakdown_unassigned'] = 'Without current tutor';
$string['coordination_scope_user'] = 'User';
$string['coordination_scope_availablecohorts'] = 'Cohorts';
$string['coordination_scope_assignments'] = 'Current scope assignments';
$string['coordination_scope_current'] = 'Edit scope for: {$a}';
$string['coordination_scope_save'] = 'Save scope';
$string['coordination_scope_saved'] = 'Scope updated.';
$string['coordination_scope_empty'] = 'There are no coordination scopes assigned yet.';
$string['eventcoordinationdashboardexported'] = 'Coordination report exported';

$string['notification_preferences_title'] = 'Notification preferences';
$string['notification_preferences_intro'] = 'Choose which alerts you want to receive and how often summaries are sent.';
$string['notification_preferences_save'] = 'Save preferences';
$string['notification_preferences_saved'] = 'Notification preferences saved.';
$string['notification_pref_assignmentchanges'] = 'Notify me when a student is assigned or reassigned to me';
$string['notification_pref_referralchanges'] = 'Notify me about referrals received or returned';
$string['notification_pref_followupreminders'] = 'Notify me about upcoming or overdue follow-ups';
$string['notification_pref_agreementreminders'] = 'Notify me about upcoming or overdue agreements';
$string['notification_pref_digestfrequency'] = 'Digest frequency';
$string['notification_frequency_none'] = 'No digest';
$string['notification_frequency_daily'] = 'Daily';
$string['notification_frequency_weekly'] = 'Weekly';
$string['notification_subject_assignment_assigned'] = 'New tutoring assignment';
$string['notification_body_assignment_assigned'] = 'You have a new tutoring update in Moodle. Open Moodle to review the assigned student.';
$string['notification_subject_assignment_reassigned'] = 'Tutoring reassignment';
$string['notification_body_assignment_reassigned'] = 'A tutoring assignment changed in Moodle. Open Moodle to review the update.';
$string['notification_subject_referral_assigned'] = 'New assigned referral';
$string['notification_body_referral_assigned'] = 'You have a pending referral in Moodle. Open Moodle to review it.';
$string['notification_subject_referral_returned'] = 'Referral update';
$string['notification_body_referral_returned'] = 'A referral you created changed in Moodle. Open Moodle to review the update.';
$string['notification_subject_followup_due'] = 'Upcoming follow-up';
$string['notification_body_followup_due'] = 'You have an upcoming follow-up in Moodle. Open Moodle to review it.';
$string['notification_subject_followup_overdue'] = 'Overdue follow-up';
$string['notification_body_followup_overdue'] = 'You have an overdue follow-up in Moodle. Open Moodle to review it.';
$string['notification_subject_agreement_due'] = 'Upcoming agreement';
$string['notification_body_agreement_due'] = 'You have an upcoming agreement in Moodle. Open Moodle to review it.';
$string['notification_subject_agreement_overdue'] = 'Overdue agreement';
$string['notification_body_agreement_overdue'] = 'You have an overdue agreement in Moodle. Open Moodle to review it.';
$string['notification_subject_daily_digest'] = 'Daily tutoring digest';
$string['notification_body_daily_digest'] = 'Daily digest: {$a->assignedcount} students, {$a->upcomingfollowupcount} upcoming follow-ups, {$a->overduefollowupcount} overdue follow-ups, {$a->pendingagreementcount} pending agreements, {$a->overdueagreementcount} overdue agreements and {$a->openreferralcount} open referrals.';
$string['notification_subject_weekly_digest'] = 'Weekly tutoring digest';
$string['notification_body_weekly_digest'] = 'Weekly digest: {$a->assignedcount} students, {$a->upcomingfollowupcount} upcoming follow-ups, {$a->overduefollowupcount} overdue follow-ups, {$a->pendingagreementcount} pending agreements, {$a->overdueagreementcount} overdue agreements and {$a->openreferralcount} open referrals.';
$string['task_dispatch_notification'] = 'Send one tutoring notification';
$string['task_send_notification_reminders'] = 'Queue tutoring reminders';
$string['task_retry_failed_notifications'] = 'Retry failed tutoring notifications';
$string['task_cleanup_notification_logs'] = 'Clean tutoring notification logs';
$string['privacy:metadata:notification'] = 'Operational notification metadata stored by the plugin.';
$string['privacy:metadata:notification:notificationtype'] = 'The notification type.';
$string['privacy:metadata:notification:recipientid'] = 'The user receiving the notification.';
$string['privacy:metadata:notification:actorid'] = 'The user who triggered the notification when applicable.';
$string['privacy:metadata:notification:entitytype'] = 'The related entity type.';
$string['privacy:metadata:notification:entityid'] = 'The related entity id.';
$string['privacy:metadata:notification:digestkey'] = 'Daily or weekly deduplication bucket.';
$string['privacy:metadata:notification:status'] = 'Delivery status.';
$string['privacy:metadata:notification:attempts'] = 'Number of delivery attempts.';
$string['privacy:metadata:notification:lasterror'] = 'Last recorded delivery error.';
$string['privacy:metadata:notification:timesent'] = 'Time when the notification was sent.';


$string['nav_dashboard'] = 'Dashboard';
$string['nav_myfile'] = 'My tutoring';
$string['nav_dashboard_tip'] = 'Quick overview of tutor students, pending items and priorities.';
$string['nav_assignments'] = 'Assignments';
$string['nav_assignments_tip'] = 'Review student allocation and open each assignment detail.';
$string['nav_referrals'] = 'Referrals';
$string['nav_referrals_tip'] = 'Manage open referrals and review their status.';
$string['nav_coordination'] = 'Coordination';
$string['nav_coordination_tip'] = 'Global view by academic year, cohort and tutor.';
$string['nav_coordinators'] = 'Coordinators';
$string['nav_coordinators_tip'] = 'Choose who can access coordination and which cohorts each one supervises.';
$string['nav_notifications'] = 'Notifications';
$string['nav_notifications_tip'] = 'Adjust reminders, changes and digest settings.';
$string['nav_configuration'] = 'Configuration';
$string['nav_configuration_tip'] = 'Access academic years, reasons and contact modalities.';
$string['nav_help'] = 'Help';
$string['nav_help_tip'] = 'Learn what a tutoring entry, agreement, follow-up and referral are.';

$string['help_page_title'] = 'Monlau Tutoria quick guide';
$string['help_page_intro'] = 'These are the 4 concepts you will use most often day to day. Each one normally starts from a tutoring entry, and each has its own lifecycle.';
$string['help_concept_entry_title'] = 'Tutoring entry';
$string['help_concept_entry_short'] = 'The record of one specific tutoring actuation: when it happened, which contact modality, why, and its content in 3 tiers — what is shared with the student, an internal note (tutors and coordination only) and a restricted note (even more limited).';
$string['help_concept_entry_full'] = 'This is where everything else starts: an agreement, follow-up or referral always originates from a specific entry. You can register a quick entry (the minimum, in under a minute) or a full one (multiple reasons, internal or external participants, and the restricted note if you hold that permission). An entry can be edited shortly after creating it without giving a reason; past a configurable window, editing it requires explaining why. It is never deleted: if it needs to stop applying, it is annulled, and the fact that it existed is kept.';
$string['help_concept_agreement_title'] = 'Agreement';
$string['help_concept_agreement_short'] = 'A concrete commitment that comes out of a tutoring entry: who is responsible for it (the student themselves, the family, the tutor, orientation...) and by when.';
$string['help_concept_agreement_full'] = 'An agreement can be marked visible to the student or kept internal. Over its life it can be marked completed, reopened if it needs picking up again, postponed to a new due date, or cancelled if it no longer applies.';
$string['help_concept_followup_title'] = 'Follow-up';
$string['help_concept_followup_short'] = 'Marks a future date to check how something discussed in a tutoring entry is progressing, with a priority (low, medium or high).';
$string['help_concept_followup_full'] = 'It can be closed in 2 ways: manually (nothing more to it), or by registering a new, linked tutoring entry that documents how it was resolved — this second way is the more common one, since it records what actually happened. A follow-up is never seen by the student.';
$string['help_concept_referral_title'] = 'Referral';
$string['help_concept_referral_short'] = 'Routes a case to coordination, orientation or management for someone else to handle, with a reason freshly written for the occasion — never a copy of the entry\'s internal notes.';
$string['help_concept_referral_full'] = 'Whoever receives a referral may have no prior tutoring relationship with the student at all — that is exactly why they can see and handle it even though they are not listed as the student\'s tutor. It can be assigned to a specific person and, once resolved, the resolution is recorded. Like the rest, the student never sees it either.';
$string['help_visibility_title'] = 'What does the student see?';
$string['help_visibility_body'] = 'The student only ever sees the tutoring content marked "shared with the student", and any agreement explicitly marked visible to them. They never see an entry\'s internal or restricted notes, nor follow-ups, nor referrals — this is always enforced on the server, never hidden with styling alone.';
$string['help_dashboard_title'] = 'How do I read this panel?';
$string['help_dashboard_body'] = 'The cards above summarise your situation at a glance: assigned students, tutoring coverage, and how much is pending (overdue follow-ups, pending agreements, open referrals, priority students). Below that is the detail for each, with quick actions so you do not have to open every student\'s file one by one.';
$string['help_studentview_title'] = 'What is in each tab of the student file?';
$string['help_studentview_body'] = 'Summary: tutor, cohort and academic year. History: this student\'s tutor assignment history. Tutoring: the full record of tutoring actuations. Agreements and Follow-ups: the commitments and review dates that came out of those entries.';
$string['nav_student'] = 'Student record';
$string['nav_student_tip'] = 'Return to the current student tutoring record.';

$string['dashboard_intro'] = 'Review assigned students, pending items and tutoring alerts at a glance.';
$string['assignments_intro'] = 'Filter assignments, open each detail and jump quickly to the student record.';
$string['assignment_detail_intro'] = 'Review the full assignment context here and jump directly to the student record.';
$string['student_detail_intro'] = 'Use the tabs to move between summary, history, tutoring entries, agreements and follow-ups.';
$string['entry_detail_intro'] = 'This screen centralises the tutoring entry content and related actions.';
$string['referrals_intro'] = 'Review the referral queue and filter by status to prioritise work.';
$string['referral_detail_intro'] = 'Review the referral, its owner and the available actions without leaving context.';
$string['notifications_intro'] = 'Choose which alerts you want to receive and how often.';
$string['academicyears_intro'] = 'Manage academic years and control which one is active or locked.';
$string['reasons_intro'] = 'Maintain the tutoring reasons catalogue and its default visibility.';
$string['modalities_intro'] = 'Define the available contact channels used when recording tutoring entries.';

$string['page_back_dashboard'] = 'Back to dashboard';
$string['page_back_assignments'] = 'Back to assignments';
$string['page_back_referrals'] = 'Back to referrals';
$string['page_back_student_entries'] = 'Back to student entries';
$string['page_back_configuration'] = 'Back to configuration';

$string['assignments_create_tip'] = 'Create a new manual assignment and then open its detail.';

$string['studenttab_summary_tip'] = 'Overview of the student in the selected academic year.';
$string['studenttab_history_tip'] = 'History of assignments and tutor changes over time.';
$string['studenttab_tutoring_tip'] = 'List of tutoring entries recorded for this student.';
$string['studenttab_agreements_tip'] = 'Follow-up of agreements linked to the student\'s tutoring entries.';
$string['studenttab_followups_tip'] = 'Open follow-ups pending for this student.';

$string['configuration_intro'] = 'Entry point for administering the plugin and its base catalogues.';

$string['settings_entryeditwindow_title'] = 'Tutoring entry edit window';

$string['assignment_reassign'] = 'Reassign tutor';
$string['assignment_reassign_title'] = 'Reassign tutor';
$string['assignment_reassign_confirm'] = 'Confirm reassignment';
$string['assignment_reassign_confirm_checkbox'] = 'I confirm that I want to reassign this tutoring assignment to another tutor.';
$string['assignment_reassign_success'] = 'Tutor reassigned successfully.';
$string['assignment_reassign_intro'] = 'This closes the current primary assignment and creates a new one with the selected tutor, preserving history.';
$string['assignment_field_newtutor'] = 'New tutor';
$string['assignment_field_reassignreason'] = 'Reason for reassignment';
$string['assignment_field_reassigndate'] = 'Effective reassignment date';
$string['assignment_field_keepcotutors'] = 'Keep the student\'s active co-tutors';
$string['warning_assignment_reassign'] = 'This action will close the current primary assignment and create a new one with the selected tutor.';
$string['warning_assignment_reassign_cotutors'] = 'The student has {} active co-tutor(s). You can keep or close them as part of the reassignment.';
$string['error_assignment_reassign_only_primary'] = 'Only an active primary assignment can be reassigned.';
$string['error_assignment_reassign_not_confirmed'] = 'You must confirm the reassignment.';

$string['coordination_scope_manage'] = 'Manage coordinators';
$string['coordination_scope_manage_help'] = 'Choose which coordinators can supervise each cohort.';
$string['coordination_dashboard_intro'] = 'Aggregated coordination view. Scope is defined by the cohorts assigned to the coordinator and, within that scope, the dashboard can be filtered by tutor.';
$string['coordination_filter_help'] = 'First narrow by cohort and then, if needed, filter by tutor within those cohorts.';
$string['error_coordination_scope_invalid_user'] = 'You can only assign scopes to users with access to the coordination dashboard.';
$string['coordination_scope_intro'] = 'Select a coordinator and assign the cohorts they can supervise. Inside the dashboard they can then filter by tutor.';
$string['coordination_scope_help_steps'] = 'To enable a coordinator: 1) assign the user a role with the capability local/monlaututoria:viewcoordinationdashboard at system level, 2) select that user on this screen, and 3) assign the cohorts they can supervise.';

// Phase 13 — simple mode.
$string['setting_simplemode'] = 'Simple mode';
$string['setting_simplemode_desc'] = 'Reduces the plugin to the day-to-day essentials: the tutor records tutoring entries and the student consults their own entries and who their tutor is. Hides agreements, follow-ups, referrals, the coordination dashboard, notifications, imports (CSV and from cohorts), co-tutors and attachments — from the navigation and from their pages. Nothing is deleted: data, services and tests stay intact, and clearing this checkbox restores everything. Off by default.';
$string['error_featuredisabled'] = 'This feature is not available: the site is running Monlau Tutoria in simple mode.';
