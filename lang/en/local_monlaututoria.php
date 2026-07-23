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
$string['error_assignment_isprimary_type_mismatch'] = 'Only a primary assignment can be marked as the primary tutor.';
$string['error_assignment_primary_duplicate'] = 'This student already has an active primary tutor for this academic year.';
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
