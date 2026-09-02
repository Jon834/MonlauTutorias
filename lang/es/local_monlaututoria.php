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
 * Cadenas de idioma en español (España) para local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Monlau Tutoría';
$string['monlaututoria:view'] = 'Ver Monlau Tutoría';
$string['monlaututoria:viewconfiguration'] = 'Ver la configuración de Monlau Tutoría';
$string['monlaututoria:manageacademicyears'] = 'Gestionar cursos académicos';
$string['monlaututoria:managecatalogues'] = 'Gestionar catálogos de tutoría';
$string['monlaututoria:overridelock'] = 'Anular el bloqueo de cursos académicos';

$string['academicyears'] = 'Cursos académicos';
$string['reasons'] = 'Motivos de tutoría';
$string['modalities'] = 'Modalidades de contacto';

$string['academicyear_name'] = 'Nombre';
$string['academicyear_shortname'] = 'Nombre corto';
$string['academicyear_startdate'] = 'Fecha de inicio';
$string['academicyear_enddate'] = 'Fecha de fin';
$string['academicyear_active'] = 'Activo';
$string['academicyear_locked'] = 'Bloqueado';
$string['academicyear_create'] = 'Nuevo curso académico';
$string['academicyear_edit'] = 'Editar';
$string['academicyear_activate'] = 'Activar';
$string['academicyear_lock'] = 'Bloquear';
$string['academicyear_unlock'] = 'Desbloquear';
$string['academicyear_delete'] = 'Eliminar';
$string['academicyear_list_empty'] = 'Todavía no se ha creado ningún curso académico.';
$string['academicyear_activate_confirm'] = 'El curso académico "{$a}" está actualmente activo. Al activar este se desactivará el anterior. ¿Continuar?';
$string['academicyear_activate_confirm_noactive'] = '¿Activar este curso académico?';
$string['academicyear_activate_success'] = 'Curso académico activado.';
$string['academicyear_locked_success'] = 'Curso académico bloqueado.';
$string['academicyear_unlocked_success'] = 'Curso académico desbloqueado.';
$string['academicyear_delete_confirm'] = '¿Eliminar el curso académico "{$a}"? Esta acción no se puede deshacer.';
$string['academicyear_delete_success'] = 'Curso académico eliminado.';
$string['academicyear_delete_blocked_active'] = 'No se puede eliminar el curso académico activo.';
$string['academicyear_delete_blocked_used'] = 'Este curso académico no se puede eliminar porque hay otros datos que lo referencian.';
$string['noactiveacademicyear_warning'] = 'No hay ningún curso académico activo. Crea y activa uno para continuar.';

$string['error_enddate_before_startdate'] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
$string['error_shortname_duplicate'] = 'Este nombre corto ya está en uso.';
$string['error_academicyear_locked'] = 'Este curso académico está bloqueado y no se puede modificar.';
$string['error_noaccess_overridelock'] = 'No tienes permiso para desbloquear este curso académico.';

$string['reason_name'] = 'Nombre';
$string['reason_shortname'] = 'Nombre corto';
$string['reason_description'] = 'Descripción';
$string['reason_active'] = 'Activo';
$string['reason_requiresfollowup'] = 'Requiere seguimiento';
$string['reason_defaultvisibility'] = 'Visibilidad por defecto';
$string['reason_create'] = 'Nuevo motivo';
$string['reason_edit'] = 'Editar';
$string['reason_activate'] = 'Activar';
$string['reason_deactivate'] = 'Desactivar';
$string['reason_delete'] = 'Eliminar';
$string['reason_delete_confirm'] = '¿Eliminar el motivo "{$a}"? Esta acción no se puede deshacer.';
$string['reason_moveup'] = 'Subir';
$string['reason_movedown'] = 'Bajar';
$string['reason_list_empty'] = 'Todavía no se ha creado ningún motivo.';
$string['reason_delete_blocked_used'] = 'Este motivo no se puede eliminar porque hay otros datos que lo referencian.';

$string['modality_name'] = 'Nombre';
$string['modality_shortname'] = 'Nombre corto';
$string['modality_description'] = 'Descripción';
$string['modality_active'] = 'Activo';
$string['modality_create'] = 'Nueva modalidad';
$string['modality_edit'] = 'Editar';
$string['modality_activate'] = 'Activar';
$string['modality_deactivate'] = 'Desactivar';
$string['modality_delete'] = 'Eliminar';
$string['modality_delete_confirm'] = '¿Eliminar la modalidad "{$a}"? Esta acción no se puede deshacer.';
$string['modality_moveup'] = 'Subir';
$string['modality_movedown'] = 'Bajar';
$string['modality_list_empty'] = 'Todavía no se ha creado ninguna modalidad.';
$string['modality_delete_blocked_used'] = 'Esta modalidad no se puede eliminar porque hay otros datos que la referencian.';

$string['visibility_shared'] = 'Compartido con el alumno';
$string['visibility_internal'] = 'Interno tutorial';
$string['visibility_restricted'] = 'Restringido';

$string['eventacademicyearcreated'] = 'Curso académico creado';
$string['eventacademicyearupdated'] = 'Curso académico actualizado';
$string['eventacademicyearactivated'] = 'Curso académico activado';
$string['eventacademicyearlocked'] = 'Curso académico bloqueado o desbloqueado';
$string['eventreasoncreated'] = 'Motivo de tutoría creado';
$string['eventreasonupdated'] = 'Motivo de tutoría actualizado';
$string['eventreasonactivated'] = 'Motivo de tutoría activado o desactivado';
$string['eventmodalitycreated'] = 'Modalidad de contacto creada';
$string['eventmodalityupdated'] = 'Modalidad de contacto actualizada';
$string['eventmodalityactivated'] = 'Modalidad de contacto activada o desactivada';
$string['eventacademicyeardeleted'] = 'Curso académico eliminado';
$string['eventreasondeleted'] = 'Motivo de tutoría eliminado';
$string['eventmodalitydeleted'] = 'Modalidad de contacto eliminada';

$string['reason_seed_acogida_inicial'] = 'Acogida inicial';
$string['reason_seed_seguimiento_ordinario'] = 'Seguimiento ordinario';
$string['reason_seed_rendimiento_academico'] = 'Rendimiento académico';
$string['reason_seed_asistencia'] = 'Asistencia';
$string['reason_seed_puntualidad'] = 'Puntualidad';
$string['reason_seed_convivencia'] = 'Convivencia';
$string['reason_seed_motivacion'] = 'Motivación';
$string['reason_seed_habitos_estudio'] = 'Hábitos de estudio';
$string['reason_seed_organizacion'] = 'Organización';
$string['reason_seed_orientacion_academica'] = 'Orientación académica';
$string['reason_seed_orientacion_profesional'] = 'Orientación profesional';
$string['reason_seed_practicas_empresa'] = 'Prácticas en empresa';
$string['reason_seed_situacion_personal'] = 'Situación personal';
$string['reason_seed_seguimiento_acuerdos'] = 'Seguimiento de acuerdos';
$string['reason_seed_contacto_familia'] = 'Contacto con la familia';
$string['reason_seed_solicitud_alumno'] = 'Solicitud del alumno';
$string['reason_seed_solicitud_familia'] = 'Solicitud de la familia';
$string['reason_seed_reconocimiento_positivo'] = 'Reconocimiento positivo';
$string['reason_seed_derivacion'] = 'Derivación';
$string['reason_seed_otro'] = 'Otro';

$string['modality_seed_presencial'] = 'Presencial';
$string['modality_seed_telefono'] = 'Teléfono';
$string['modality_seed_videoconferencia'] = 'Videoconferencia';
$string['modality_seed_correo_electronico'] = 'Correo electrónico';
$string['modality_seed_mensajeria'] = 'Mensajería';
$string['modality_seed_reunion_coordinacion'] = 'Reunión de coordinación';
$string['modality_seed_otra'] = 'Otra';

$string['privacy:metadata:createdby'] = 'El usuario que creó este registro.';
$string['privacy:metadata:modifiedby'] = 'El usuario que modificó este registro por última vez.';
$string['privacy:metadata:timecreated'] = 'La fecha en que se creó el registro.';
$string['privacy:metadata:timemodified'] = 'La fecha de la última modificación del registro.';
$string['privacy:metadata:academicyear'] = 'Información sobre los cursos académicos, incluyendo quién creó o modificó por última vez cada uno.';
$string['privacy:metadata:academicyear:name'] = 'El nombre visible del curso académico.';
$string['privacy:metadata:academicyear:shortname'] = 'El nombre corto estable del curso académico.';
$string['privacy:metadata:reason'] = 'Información sobre los motivos de tutoría, incluyendo quién creó o modificó por última vez cada uno.';
$string['privacy:metadata:reason:name'] = 'El nombre visible del motivo.';
$string['privacy:metadata:reason:shortname'] = 'El nombre corto estable del motivo.';
$string['privacy:metadata:modality'] = 'Información sobre las modalidades de contacto, incluyendo quién creó o modificó por última vez cada una.';
$string['privacy:metadata:modality:name'] = 'El nombre visible de la modalidad.';
$string['privacy:metadata:modality:shortname'] = 'El nombre corto estable de la modalidad.';
$string['privacy:metadata:enabledcohort'] = 'Qué cohortes de Moodle un administrador ha habilitado como relevantes para este plugin, incluyendo quién lo configuró.';
$string['privacy:metadata:enabledcohort:cohortid'] = 'La cohorte de Moodle habilitada (no es un dato personal en sí, es una referencia a la propia cohorte).';

$string['monlaututoria:viewownstudents'] = 'Ver los alumnos propios asignados';
$string['monlaututoria:viewstudent'] = 'Ver la ficha de tutoría de un alumno concreto';
$string['monlaututoria:viewhistoricalassignments'] = 'Ver las asignaciones históricas (cerradas) propias';
$string['monlaututoria:assignstudents'] = 'Crear asignaciones de alumnos';
$string['monlaututoria:manageassignments'] = 'Gestionar asignaciones existentes';
$string['monlaututoria:managecohortassignments'] = 'Gestionar asignaciones desde cohortes';
$string['monlaututoria:importassignments'] = 'Importar asignaciones desde CSV';
$string['monlaututoria:reassignstudents'] = 'Reasignar alumnos a un nuevo tutor';
$string['monlaututoria:viewallassignments'] = 'Ver todas las asignaciones sin restricción de ámbito';
$string['monlaututoria:manageclosedassignments'] = 'Reabrir o modificar asignaciones cerradas';
$string['monlaututoria:viewownfile'] = 'Ver mi propia ficha longitudinal';

$string['error_assignment_self'] = 'Un alumno no puede ser su propio tutor.';
$string['error_assignment_invalid_student'] = 'El alumno seleccionado no existe o ha sido eliminado.';
$string['error_assignment_invalid_tutor'] = 'El tutor seleccionado no existe o ha sido eliminado.';
$string['error_assignment_student_suspended'] = 'La cuenta del alumno seleccionado está suspendida.';
$string['error_assignment_tutor_suspended'] = 'La cuenta del tutor seleccionado está suspendida.';
$string['error_assignment_academicyear_invalid'] = 'El curso académico seleccionado no existe.';
$string['error_assignment_academicyear_locked'] = 'El curso académico seleccionado está bloqueado para nuevas asignaciones.';
$string['error_assignment_invalid_cohort'] = 'La cohorte seleccionada no existe.';
$string['error_assignment_dates_invalid'] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
$string['error_assignment_duplicate'] = 'Ya existe una asignación activa idéntica.';
$string['error_assignment_isprimary_type_mismatch'] = 'Solo una asignación de tipo principal puede marcarse como tutor principal.';$string['error_assignment_primary_duplicate'] = 'Este alumno ya tiene un tutor principal activo para este curso académico.';
$string['error_assignment_invalid_type'] = 'Tipo de asignación no válido.';
$string['error_assignment_already_closed'] = 'Esta asignación ya está cerrada o cancelada.';
$string['error_assignment_no_active_primary'] = 'Este alumno no tiene ningún tutor principal activo que reasignar.';
$string['error_assignment_reassign_same_tutor'] = 'El nuevo tutor ya es el tutor principal.';
$string['error_assignment_not_active_cotutor'] = 'Esta asignación no es una asignación de cotutor activa.';
$string['error_scope_access_denied'] = 'No tienes acceso a los datos de tutoría de este alumno.';

$string['eventassignmentcreated'] = 'Asignación creada';
$string['eventassignmentclosed'] = 'Asignación cerrada';
$string['eventstudentreassigned'] = 'Alumno reasignado a un nuevo tutor';
$string['eventcotutoradded'] = 'Cotutor añadido';
$string['eventcotutorremoved'] = 'Cotutor eliminado';

$string['assignmenttype_primary'] = 'Tutor principal';
$string['assignmenttype_co_tutor'] = 'Cotutor';
$string['assignmenttype_support'] = 'Apoyo';
$string['assignmenttype_orientation'] = 'Orientación';
$string['assignmenttype_other'] = 'Otro';
$string['assignmentstatus_active'] = 'Activa';
$string['assignmentstatus_closed'] = 'Cerrada';
$string['assignmentstatus_cancelled'] = 'Cancelada';
$string['assignmentstatus_pending'] = 'Pendiente';
$string['assignmentsource_manual'] = 'Manual';
$string['assignmentsource_cohort'] = 'Cohorte';
$string['assignmentsource_csv'] = 'Importación CSV';
$string['assignmentsource_external'] = 'Externa';
$string['assignmentsource_migration'] = 'Migración';

$string['privacy:metadata:assignment'] = 'Información sobre las asignaciones tutor-alumno.';
$string['privacy:metadata:assignment:studentid'] = 'El alumno de la asignación.';
$string['privacy:metadata:assignment:tutorid'] = 'El tutor de la asignación.';
$string['privacy:metadata:assignment:cohortid'] = 'La cohorte de origen de la asignación, si procede.';
$string['privacy:metadata:assignment:academicyearid'] = 'El curso académico al que pertenece la asignación.';
$string['privacy:metadata:assignment:assignmenttype'] = 'El tipo de asignación (principal, cotutor, etc.).';
$string['privacy:metadata:assignment:isprimary'] = 'Si esta es la asignación de tutor principal.';
$string['privacy:metadata:assignment:status'] = 'El estado de la asignación (activa, cerrada, etc.).';
$string['privacy:metadata:assignment:timestart'] = 'Cuándo empezó la asignación.';
$string['privacy:metadata:assignment:timeend'] = 'Cuándo finalizó la asignación, si está cerrada.';
$string['privacy:metadata:assignment:source'] = 'Cómo se creó la asignación (manual, cohorte, CSV, etc.).';

$string['assignments'] = 'Asignaciones';
$string['assignment_detail_title'] = 'Detalle de la asignación';
$string['assignment_history_title'] = 'Historial de asignaciones';

$string['filter_academicyear'] = 'Curso académico';
$string['filter_tutor'] = 'Tutor';
$string['filter_student'] = 'Alumno';
$string['filter_cohort'] = 'Cohorte';
$string['filter_assignmenttype'] = 'Tipo de asignación';
$string['filter_status'] = 'Estado';
$string['filter_source'] = 'Origen';
$string['filter_timestartfrom'] = 'Fecha de inicio desde';
$string['filter_timestartto'] = 'Fecha de inicio hasta';
$string['filter_timeendfrom'] = 'Fecha de fin desde';
$string['filter_timeendto'] = 'Fecha de fin hasta';
$string['filter_apply'] = 'Aplicar filtros';
$string['filter_all'] = 'Todos';

$string['assignment_col_student'] = 'Alumno';
$string['assignment_col_tutor'] = 'Tutor';
$string['assignment_col_cotutors'] = 'Cotutores';
$string['assignment_col_cohort'] = 'Cohorte';
$string['assignment_col_academicyear'] = 'Curso académico';
$string['assignment_col_type'] = 'Tipo';
$string['assignment_col_timestart'] = 'Fecha de inicio';
$string['assignment_col_timeend'] = 'Fecha de finalización';
$string['assignment_col_status'] = 'Estado';
$string['assignment_col_source'] = 'Origen';
$string['assignment_col_actions'] = 'Acciones';
$string['assignment_viewdetail'] = 'Ver detalle';

$string['assignment_createdby'] = 'Creado por';
$string['assignment_modifiedby'] = 'Última modificación por';

$string['assignment_upcoming'] = 'Futura';

$string['assignments_list_empty'] = 'No hay asignaciones que coincidan con los filtros seleccionados.';
$string['assignment_history_empty'] = 'Este alumno todavía no tiene historial de asignaciones.';

$string['eventassignmentviewed'] = 'Asignación vista';
$string['eventassignmentupdated'] = 'Asignación actualizada';

$string['assignment_create_title'] = 'Nueva asignación';
$string['assignment_edit_title'] = 'Editar asignación';
$string['assignment_create'] = 'Nueva asignación';
$string['assignment_edit'] = 'Editar';
$string['assignment_create_success'] = 'Asignación creada.';
$string['assignment_update_success'] = 'Asignación actualizada.';
$string['assignment_field_note'] = 'Observación administrativa';
$string['assignment_field_editreason'] = 'Motivo de la modificación';
$string['assignment_field_closereason'] = 'Motivo de cierre';
$string['assignment_field_closedate'] = 'Fecha efectiva de cierre';

$string['assignment_close'] = 'Cerrar';
$string['assignment_close_title'] = 'Cerrar asignación';
$string['assignment_close_confirm'] = 'Confirmar cierre';
$string['assignment_close_confirm_checkbox'] = 'Confirmo que deseo cerrar esta asignación.';
$string['assignment_close_success'] = 'Asignación cerrada.';
$string['assignment_close_success_no_primary'] = 'Asignación cerrada. El alumno ha quedado sin tutor principal activo.';
$string['warning_assignment_close_no_primary'] = 'Al cerrar esta asignación, el alumno quedará sin tutor principal activo.';

$string['closereason_tutorchange'] = 'Cambio de tutor';
$string['closereason_groupchange'] = 'Cambio de grupo';
$string['closereason_levelchange'] = 'Cambio de nivel';
$string['closereason_endofyear'] = 'Fin de curso académico';
$string['closereason_studentleft'] = 'Baja del alumno';
$string['closereason_tutorleft'] = 'Baja del tutor';
$string['closereason_adminerror'] = 'Error administrativo';
$string['closereason_supportended'] = 'Fin de apoyo o cotutoría';
$string['closereason_other'] = 'Otro';

$string['error_assignment_closed_no_permission'] = 'No tienes permiso para editar una asignación cerrada o cancelada.';
$string['error_invalidacademicyearid'] = 'El curso académico solicitado no existe.';
$string['error_assignment_edit_reason_required'] = 'Debes indicar un motivo para modificar una asignación cerrada o cancelada.';
$string['error_assignment_close_reason_invalid'] = 'Motivo de cierre no válido.';
$string['error_assignment_close_before_start'] = 'La fecha de cierre no puede ser anterior a la fecha de inicio.';
$string['error_assignment_close_not_confirmed'] = 'Debes confirmar el cierre.';
$string['error_assignment_close_use_remove_cotutor'] = 'Una asignación de cotutor se retira desde la gestión de cotutores, no desde esta página.';
$string['error_assignment_reassign_reason_invalid'] = 'Motivo de reasignación no válido.';
$string['error_assignment_reassign_conflict'] = 'Esta asignación se ha modificado mediante otra acción mientras se procesaba esta reasignación. No se ha aplicado ningún cambio; comprueba el estado actual e inténtalo de nuevo.';

$string['reassignreason_groupchange'] = 'Cambio de grupo';
$string['reassignreason_levelchange'] = 'Cambio de nivel';
$string['reassignreason_orgchange'] = 'Cambio organizativo';
$string['reassignreason_tempsubstitution'] = 'Sustitución temporal';
$string['reassignreason_tutorleft'] = 'Baja del tutor';
$string['reassignreason_reorganization'] = 'Reorganización de tutorías';
$string['reassignreason_adminerror'] = 'Error administrativo';
$string['reassignreason_coordinationrequest'] = 'Solicitud de coordinación';
$string['reassignreason_other'] = 'Otro';

$string['privacy:metadata:assignment:note'] = 'Una observación administrativa opcional sobre la asignación.';
$string['privacy:metadata:assignment:closereason'] = 'El motivo codificado por el que se cerró la asignación.';

$string['eventcohortassignmentpreviewed'] = 'Previsualización de asignación desde cohorte generada';
$string['eventcohortassignmentapplied'] = 'Asignación desde cohorte aplicada';
$string['eventcohortassignmentapplyfailed'] = 'Aplicación de asignación desde cohorte fallida';

$string['error_cohort_mode_invalid'] = 'Modo de sincronización no válido.';
$string['error_cohort_same_tutor_cotutor'] = 'El tutor principal y el cotutor no pueden ser la misma persona.';
$string['error_cohort_operation_not_usable'] = 'Esta operación no es una asignación por cohorte.';
$string['error_cohort_already_applied'] = 'Esta operación ya se ha aplicado o está en curso.';
$string['error_cohort_mode_preview_only_cannot_apply'] = 'El modo "Solo previsualizar" no se puede aplicar; genera una nueva previsualización con otro modo.';
$string['error_cohort_preview_changed'] = 'Los datos han cambiado desde que se generó esta previsualización. Genera una nueva.';
$string['error_cohort_apply_row_failed'] = 'No se ha podido aplicar la operación; no se ha escrito ningún cambio.';
$string['error_cohort_apply_not_confirmed'] = 'Debes confirmar que quieres aplicar esta asignación por cohorte.';

$string['cohort_assignment_create'] = 'Asignar por cohorte';
$string['cohort_assignment_create_tip'] = 'Asigna un tutor a todos los alumnos de una cohorte de una vez, con previsualización antes de confirmar.';
$string['cohort_assignment_manual_hint'] = 'Este formulario crea una única asignación manual. El campo "Cohorte" es solo una etiqueta descriptiva sobre esa asignación, no asigna a todo el grupo. Para asignar un tutor a una cohorte entera, usa "Asignar por cohorte".';

$string['cohort_assignment_title'] = 'Asignación por cohorte';
$string['cohort_assignment_intro'] = 'Elige una cohorte, un curso académico y un tutor, previsualiza el resultado y confirma para escribir las asignaciones.';

$string['cohort_assignment_field_cohort'] = 'Cohorte';
$string['cohort_assignment_field_primarytutor'] = 'Tutor principal';
$string['cohort_assignment_field_cotutor'] = 'Cotutor (opcional)';
$string['cohort_assignment_field_mode'] = 'Modo de sincronización';
$string['cohort_assignment_field_mode_help'] = 'Añadir asignaciones: crea tutorías para los alumnos que aún no tienen una, sin tocar las existentes. Añadir y cerrar ausentes: además cierra las asignaciones de alumnos que ya no están en la cohorte. Reemplazar tutor principal: reasigna a un nuevo tutor a los alumnos que ya tenían uno (acción de mayor impacto). Solo previsualizar: muestra el resultado sin poder confirmarlo.';
$string['cohort_assignment_field_includesuspended'] = 'Incluir alumnos con la cuenta suspendida';
$string['cohort_assignment_field_allowsuspendedtutor'] = 'Permitir tutor o cotutor con la cuenta suspendida';
$string['cohort_assignment_preview_button'] = 'Previsualizar';

$string['cohort_assignment_mode_add_only'] = 'Añadir asignaciones';
$string['cohort_assignment_mode_add_and_close_missing'] = 'Añadir y cerrar ausentes';
$string['cohort_assignment_mode_replace_primary'] = 'Reemplazar tutor principal';
$string['cohort_assignment_mode_preview_only'] = 'Solo previsualizar';

$string['cohort_assignment_preview_summary_title'] = 'Resumen de la previsualización';
$string['cohort_assignment_summary_total'] = 'Alumnos analizados: {$a}';
$string['cohort_assignment_summary_tocreate'] = 'Asignaciones nuevas: {$a}';
$string['cohort_assignment_summary_toreassign'] = 'Reasignaciones: {$a}';
$string['cohort_assignment_summary_tocreatecotutor'] = 'Cotutores nuevos: {$a}';
$string['cohort_assignment_summary_toclose'] = 'Asignaciones a cerrar (alumnos ausentes): {$a}';
$string['cohort_assignment_summary_nochange'] = 'Sin cambios: {$a}';
$string['cohort_assignment_summary_skipped'] = 'Omitidos: {$a}';
$string['cohort_assignment_summary_suspended'] = 'Cuentas suspendidas: {$a}';
$string['cohort_assignment_summary_conflicts'] = 'Conflictos detectados: {$a}';
$string['cohort_assignment_conflicts_warning'] = 'Se han detectado conflictos de datos en algunos alumnos; revísalos antes de confirmar.';
$string['cohort_assignment_preview_empty'] = 'La cohorte seleccionada no tiene alumnos.';

$string['cohort_assignment_col_action'] = 'Acción';
$string['cohort_assignment_col_currenttutor'] = 'Tutor actual';
$string['cohort_assignment_col_cotutoraction'] = 'Acción cotutor';
$string['cohort_assignment_col_conflicts'] = 'Conflictos';

$string['cohort_assignment_apply_title'] = 'Confirmar aplicación';
$string['cohort_assignment_apply_intro'] = 'Al confirmar se escribirán de verdad las asignaciones descritas arriba. Esta acción no se puede deshacer desde aquí.';
$string['cohort_assignment_apply_confirm_checkbox'] = 'Confirmo que quiero aplicar esta asignación por cohorte.';
$string['cohort_assignment_apply_button'] = 'Aplicar';
$string['cohort_assignment_apply_result_title'] = 'Resultado de la aplicación';
$string['cohort_assignment_apply_success'] = 'Asignación por cohorte aplicada correctamente.';
$string['cohort_assignment_apply_created'] = 'Asignaciones creadas: {$a}';
$string['cohort_assignment_apply_reassigned'] = 'Reasignaciones realizadas: {$a}';
$string['cohort_assignment_apply_closed'] = 'Asignaciones cerradas: {$a}';
$string['cohort_assignment_apply_nochange'] = 'Sin cambios: {$a}';
$string['cohort_assignment_apply_skipped'] = 'Omitidos: {$a}';
$string['cohort_assignment_apply_result_empty'] = 'No hay resultados que mostrar.';

$string['cohort_action_create_primary'] = 'Crear tutor principal';
$string['cohort_action_create_cotutor'] = 'Crear cotutor';
$string['cohort_action_reassign_primary'] = 'Reasignar tutor principal';
$string['cohort_action_close_missing'] = 'Cerrar (ausente)';
$string['cohort_action_no_change'] = 'Sin cambios';
$string['cohort_action_skip_existing'] = 'Omitido (ya tiene tutor)';
$string['cohort_action_skip_suspended'] = 'Omitido (suspendido)';
$string['cohort_action_skip_invalid'] = 'Omitido (no válido)';
$string['cohort_action_conflict_primary'] = 'Conflicto';
$string['cohort_action_error'] = 'Error';

$string['conflictcode_multipleactiveprimary'] = 'Más de un tutor principal activo a la vez';
$string['conflictcode_overlappingfuture'] = 'Más de una asignación futura solapada';
$string['conflictcode_duplicatehistorical'] = 'Asignaciones históricas solapadas';
$string['conflictcode_deletedtutoractive'] = 'El tutor de la asignación activa tiene la cuenta eliminada';

$string['privacy:metadata:bulkoperation'] = 'Operaciones masivas de asignación desde cohortes';
$string['privacy:metadata:bulkoperation:cohortid'] = 'La cohorte usada como fuente de población de alumnos.';
$string['privacy:metadata:bulkoperation:academicyearid'] = 'El curso académico al que se aplica la operación.';
$string['privacy:metadata:bulkoperation:primarytutorid'] = 'El tutor seleccionado como tutor principal para la operación.';
$string['privacy:metadata:bulkoperation:cotutorid'] = 'El tutor seleccionado como cotutor para la operación, si lo hay.';
$string['privacy:metadata:bulkoperation:mode'] = 'El modo de sincronización usado en la operación.';

$string['eventcsvimportpreviewed'] = 'Previsualización de importación CSV generada';

$string['csv_import_title'] = 'Importar asignaciones desde CSV';
$string['csv_import_intro'] = 'Sube un archivo CSV para previsualizar qué asignaciones tutor-alumno crearía. Todavía no se aplica nada — esto solo muestra una previsualización.';
$string['csv_field_file'] = 'Archivo CSV';
$string['csv_field_delimiter'] = 'Delimitador';
$string['csv_delimiter_comma'] = 'Coma (,)';
$string['csv_delimiter_semicolon'] = 'Punto y coma (;)';
$string['csv_delimiter_tab'] = 'Tabulador';
$string['csv_field_encoding'] = 'Codificación del archivo';
$string['csv_upload_preview'] = 'Previsualizar';
$string['csv_preview_summary_title'] = 'Resumen de la previsualización';
$string['csv_summary_total'] = 'Filas analizadas: {$a}';
$string['csv_summary_valid'] = 'Válidas: {$a}';
$string['csv_summary_warning'] = 'Con advertencia: {$a}';
$string['csv_summary_conflict'] = 'Conflictos: {$a}';
$string['csv_summary_error'] = 'Errores: {$a}';
$string['csv_summary_excluded'] = 'Excluidas: {$a}';
$string['csv_col_row'] = 'Fila';
$string['csv_col_status'] = 'Estado';
$string['csv_col_messages'] = 'Mensajes';
$string['csv_preview_empty'] = 'El archivo no tiene filas de datos que previsualizar.';
$string['csv_exclude_title'] = 'Excluir filas';
$string['csv_exclude_intro'] = 'Marca las filas que quieras excluir y recalcula la previsualización.';
$string['csv_row_label'] = 'Excluir fila {$a}';
$string['csv_recalculate_preview'] = 'Recalcular previsualización';
$string['csv_apply_not_available_yet'] = 'Todavía no está disponible aplicar esta importación — esta fase solo previsualiza el archivo.';

$string['csv_status_valid'] = 'Válida';
$string['csv_status_warning'] = 'Advertencia';
$string['csv_status_conflict'] = 'Conflicto';
$string['csv_status_error'] = 'Error';
$string['csv_status_excluded'] = 'Excluida';

$string['csv_message_empty_file'] = 'El archivo está vacío.';
$string['csv_message_missing_required_header'] = 'Falta una cabecera obligatoria.';
$string['csv_message_unknown_column'] = 'El archivo contiene una columna no reconocida.';
$string['csv_message_column_count_mismatch'] = 'Esta fila no tiene el número de columnas esperado.';
$string['csv_message_missing_student'] = 'La columna de alumno está vacía.';
$string['csv_message_missing_tutor'] = 'La columna de tutor está vacía.';
$string['csv_message_missing_academicyear'] = 'La columna de curso académico está vacía.';
$string['csv_message_invalid_isprimary'] = 'La columna "tutor principal" debe ser 0 o 1.';
$string['csv_message_invalid_timestart'] = 'La fecha de inicio no es una fecha válida (AAAA-MM-DD).';
$string['csv_message_invalid_timeend'] = 'La fecha de fin no es una fecha válida (AAAA-MM-DD).';
$string['csv_message_invalid_assignmenttype'] = 'El tipo de asignación no se reconoce.';
$string['csv_message_invalid_source'] = 'El origen no se reconoce.';
$string['csv_message_duplicate_row'] = 'Esta fila repite otra anterior del mismo archivo.';
$string['csv_message_student_not_found'] = 'No se ha encontrado ninguna cuenta de alumno coincidente (por correo, usuario o número de identificación).';
$string['csv_message_student_suspended'] = 'La cuenta del alumno está suspendida.';
$string['csv_message_student_self_tutor'] = 'El alumno y el tutor no pueden ser la misma persona.';
$string['csv_message_tutor_not_found'] = 'No se ha encontrado ninguna cuenta de tutor coincidente (por correo, usuario o número de identificación).';
$string['csv_message_tutor_suspended'] = 'La cuenta del tutor está suspendida.';
$string['csv_message_academicyear_not_found'] = 'Ningún curso académico coincide con este identificador corto.';
$string['csv_message_academicyear_locked'] = 'Este curso académico está bloqueado para nuevas asignaciones.';
$string['csv_message_cohort_not_found'] = 'Ninguna cohorte coincide con este identificador; la asignación se crearía sin cohorte.';
$string['csv_message_duplicate_active'] = 'Ya existe una asignación activa idéntica.';
$string['csv_message_primary_conflict'] = 'Este alumno ya tiene un tutor principal activo.';
$string['csv_message_row_excluded'] = 'Excluida manualmente.';

$string['error_csv_file_not_usable'] = 'No se ha podido leer el archivo, o no tiene filas utilizables. Comprueba las cabeceras e inténtalo de nuevo.';
$string['error_csv_invalid_parameters'] = 'Parámetros de importación no válidos o incompletos.';

$string['eventcsvimportqueued'] = 'Importación CSV encolada para procesamiento en segundo plano';
$string['eventcsvimportstarted'] = 'Importación CSV iniciada';
$string['eventcsvimportcompleted'] = 'Importación CSV completada';
$string['eventcsvimportcompletedwitherrors'] = 'Importación CSV completada con errores';
$string['eventcsvimportfailed'] = 'Importación CSV fallida';

$string['csv_field_strategy'] = 'Estrategia de aplicación';
$string['csv_strategy_partial_valid'] = 'Aplicar las filas válidas y registrar los errores por fila (recomendado)';
$string['csv_strategy_atomic_all'] = 'Todo o nada: una fila fallida cancela todo el lote';
$string['csv_field_allow_reassign'] = 'Reasignar tutores principales en conflicto';
$string['csv_field_allow_reassign_help'] = 'Cuando una fila entra en conflicto con un tutor principal activo distinto ya existente, esta opción reasigna al alumno con el tutor del archivo en vez de omitir la fila. Las filas duplicadas (la misma asignación ya existe exactamente igual) nunca se ven afectadas por esta opción.';
$string['csv_apply_confirm_checkbox'] = 'Confirmo que quiero aplicar esta importación.';
$string['csv_apply_button'] = 'Aplicar importación';
$string['csv_apply_title'] = 'Aplicar esta importación';
$string['csv_apply_intro'] = 'Esto crea o reasigna asignaciones reales a partir de la previsualización anterior. No se puede deshacer desde esta página.';
$string['csv_apply_result_title'] = 'Resultado de la importación';
$string['csv_apply_created'] = 'Creadas: {$a}';
$string['csv_apply_reassigned'] = 'Reasignadas: {$a}';
$string['csv_apply_nochange'] = 'Ya estaban al día: {$a}';
$string['csv_apply_skipped'] = 'Omitidas: {$a}';
$string['csv_apply_failed'] = 'Fallidas: {$a}';
$string['csv_apply_status_completed'] = 'La importación se ha completado correctamente.';
$string['csv_apply_status_completed_with_errors'] = 'La importación se ha completado, pero algunas filas han fallado. Consulta los recuentos anteriores.';
$string['csv_apply_status_failed'] = 'La importación ha fallado y se ha revertido — no se ha aplicado ningún cambio.';

$string['error_csv_apply_strategy_invalid'] = 'Estrategia de aplicación no válida.';
$string['error_csv_already_applied'] = 'Esta importación ya se ha aplicado.';
$string['error_csv_preview_changed'] = 'El archivo o los datos subyacentes han cambiado desde que se generó la previsualización. Genera una nueva previsualización e inténtalo de nuevo.';
$string['error_csv_apply_row_failed'] = 'Esta fila no se ha podido aplicar.';
$string['error_csv_apply_not_confirmed'] = 'Debes confirmar antes de aplicar la importación.';

$string['csv_col_outcome'] = 'Resultado';
$string['csv_apply_result_empty'] = 'Esta importación no ha generado ninguna fila procesada.';
$string['csv_apply_outcome_created'] = 'Creada';
$string['csv_apply_outcome_reassigned'] = 'Reasignada';
$string['csv_apply_outcome_no_change'] = 'Sin cambios';
$string['csv_apply_outcome_skipped_conflict'] = 'Omitida (conflicto)';
$string['csv_apply_outcome_skipped_error'] = 'Omitida (error)';
$string['csv_apply_outcome_skipped_excluded'] = 'Omitida (excluida)';
$string['csv_apply_outcome_failed'] = 'Fallida';

$string['csv_apply_deferred'] = 'Este archivo tiene muchas filas y se está aplicando en segundo plano mediante una tarea programada. No se aplica nada todavía en esta página; comprueba más tarde el resultado en el registro de eventos.';
$string['csv_report_download'] = 'Descargar informe de filas no aplicadas (CSV)';
$string['error_csv_report_not_available'] = 'El informe ya no está disponible. Solo se puede descargar una vez, inmediatamente después de aplicar la importación.';

$string['eventcsverrorreportdownloaded'] = 'Informe de errores de importación CSV descargado';
$string['task_process_csv_import'] = 'Aplicar una importación CSV grande en segundo plano';
$string['task_cleanup_bulk_operations'] = 'Limpiar operaciones masivas y archivos temporales abandonados';

$string['privacy:metadata:csvimportfiles'] = 'El archivo CSV de una importación grande, copiado temporalmente para que la tarea en segundo plano pueda leerlo; se elimina en cuanto se procesa o, como máximo, en la siguiente limpieza programada.';

$string['student_summary_title'] = 'Ficha del alumno';
$string['student_viewficha'] = 'Ver ficha';
$string['student_field_primarytutor'] = 'Tutor principal';
$string['student_field_cotutors'] = 'Cotutores';
$string['student_field_lastassignment'] = 'Última asignación';
$string['student_field_upcoming'] = 'Próximos cambios';
$string['student_summary_no_primary'] = 'Sin tutor principal activo para este curso académico.';
$string['student_summary_no_cotutors'] = 'Sin cotutores activos.';
$string['student_summary_no_assignments'] = 'Sin asignaciones en este curso académico.';
$string['student_summary_no_upcoming'] = 'No hay ningún cambio programado.';
$string['studenttab_summary'] = 'Resumen';
$string['studenttab_history'] = 'Historial';
$string['studenttab_tutoring'] = 'Tutorías';
$string['studenttab_agreements'] = 'Acuerdos';
$string['studenttab_tutoring_empty'] = 'El historial de tutorías todavía no está disponible — llegará en una fase posterior.';
$string['studenttab_agreements_empty'] = 'Los acuerdos todavía no están disponibles — llegarán en una fase posterior.';
$string['student_history_col_reason'] = 'Motivo';
$string['privacy:metadata:assignment:reassignreason'] = 'El motivo codificado registrado cuando esta asignación se creó al reasignar el tutor principal del alumno.';

// Fase 5.1 — registro de tutorías: dominio y datos.
$string['monlaututoria:viewstudentvisiblecontent'] = 'Ver el contenido de tutorías compartido con el alumno';
$string['monlaututoria:viewinternalnotes'] = 'Ver las notas internas de tutorías';
$string['monlaututoria:viewrestrictednotes'] = 'Ver las notas restringidas de tutorías';

$string['entrystatus_active'] = 'Activa';
$string['entrystatus_annulled'] = 'Anulada';

$string['entryparticipanttype_family'] = 'Familia';
$string['entryparticipanttype_orientation'] = 'Orientación';
$string['entryparticipanttype_company'] = 'Empresa';
$string['entryparticipanttype_teacher'] = 'Profesorado';
$string['entryparticipanttype_other'] = 'Otro';

$string['error_entry_followup_before_entrydate'] = 'La fecha de próximo seguimiento no puede ser anterior a la fecha de la tutoría.';
$string['error_entry_modality_invalid'] = 'La modalidad seleccionada no existe o no está activa.';
$string['error_entry_reason_invalid'] = 'Uno de los motivos seleccionados no existe o no está activo.';
$string['error_entry_participant_type_invalid'] = 'Tipo de participante no válido.';
$string['error_entry_participant_identity_invalid'] = 'Cada participante debe indicar exactamente un usuario interno o un nombre externo, nunca ambos ni ninguno.';
$string['error_entry_participant_user_invalid'] = 'El usuario participante seleccionado no existe o ha sido eliminado.';

$string['evententrycreated'] = 'Tutoría registrada';

$string['privacy:metadata:entry'] = 'Información sobre las tutorías registradas: alumno, tutor responsable, curso académico, fecha, modalidad, contenido compartido, notas internas y restringidas.';
$string['privacy:metadata:entry:studentid'] = 'El identificador del alumno de esta tutoría.';
$string['privacy:metadata:entry:tutorid'] = 'El identificador del tutor responsable de esta tutoría.';
$string['privacy:metadata:entry:academicyearid'] = 'El curso académico de esta tutoría.';
$string['privacy:metadata:entry:entrydate'] = 'La fecha real en la que tuvo lugar la tutoría.';
$string['privacy:metadata:entry:modalityid'] = 'La modalidad de contacto de esta tutoría.';
$string['privacy:metadata:entry:contentvisible'] = 'El contenido de la tutoría compartido con el alumno.';
$string['privacy:metadata:entry:noteinternal'] = 'La nota interna de esta tutoría, no visible para el alumno.';
$string['privacy:metadata:entry:noterestricted'] = 'La nota restringida de esta tutoría, el nivel más sensible.';
$string['privacy:metadata:entry:status'] = 'El estado de esta tutoría (activa o anulada).';
$string['privacy:metadata:entry:nextfollowupdate'] = 'La fecha de próximo seguimiento, si se indicó una.';
$string['privacy:metadata:entryparticipant'] = 'Información sobre los participantes de una tutoría, internos (usuarios de Moodle) o externos.';
$string['privacy:metadata:entryparticipant:participanttype'] = 'El tipo de participante (familia, orientación, empresa, profesorado, otro).';
$string['privacy:metadata:entryparticipant:userid'] = 'El identificador del participante, cuando es un usuario de Moodle.';
$string['privacy:metadata:entryparticipant:externalname'] = 'El nombre del participante, cuando no es un usuario de Moodle.';
$string['privacy:metadata:entryversion'] = 'Instantáneas del contenido de una tutoría, tomadas antes de cada edición o anulación.';
$string['privacy:metadata:entryversion:versionnumber'] = 'El número de secuencia de esta instantánea dentro de su tutoría.';
$string['privacy:metadata:entryversion:snapshotjson'] = 'Los campos editables de la tutoría tal y como estaban justo antes de esta edición.';
$string['privacy:metadata:entryversion:changereason'] = 'El motivo indicado para esta edición o anulación, si lo hubo.';
$string['privacy:metadata:entryattachment'] = 'Metadatos de los archivos adjuntos a una tutoría: categoría de documento y descripción.';
$string['privacy:metadata:entryattachment:category'] = 'La categoría documental del adjunto (informe, consentimiento, evidencia, otro).';
$string['privacy:metadata:entryattachment:description'] = 'La descripción indicada para el adjunto, si la hubo.';
$string['privacy:metadata:entryattachmentfiles'] = 'Los propios archivos adjuntos a las tutorías.';

// Fase 5.2 — registro rápido de tutorías.
$string['monlaututoria:createentry'] = 'Registrar una tutoría';
$string['entry_field_entrydate'] = 'Fecha de la tutoría';
$string['entry_field_modality'] = 'Modalidad';
$string['entry_field_reason'] = 'Motivo';
$string['entry_field_contentvisible'] = 'Comentario compartido con el alumno';
$string['entry_field_noteinternal'] = 'Nota interna';
$string['entry_field_noteinternal_help'] = 'Solo la verán tutores y coordinación. El alumno nunca verá esta nota, sea cual sea la forma en que consulte el registro.';
$string['entry_field_nextfollowupdate'] = 'Próximo seguimiento';
$string['entry_register'] = 'Registrar tutoría';
// Fase 14 — Orientador SOP.
$string['assignmenttype_sop'] = 'Orientador SOP';
$string['entry_field_sopobservations'] = 'Observaciones SOP';
$string['entry_field_recommendationsop'] = 'Recomendaciones SOP';
$string['entry_sop_register'] = 'Registrar tutoría SOP';
$string['entry_sop_register_title'] = 'Registrar tutoría SOP — {$a}';
$string['entry_sop_pick_student_title'] = 'Registrar tutoría SOP';
$string['entry_sop_pick_student_empty'] = 'No tienes alumnos asignados como orientador SOP.';
$string['entry_sop_badge'] = 'SOP';
$string['entryattachmentcategory_sop_report'] = 'Informes facilitados';
$string['entryattachmentcategory_sop_recommendation'] = 'Recomendaciones SOP';
$string['error_sop_not_orientador'] = 'Solo el orientador SOP del alumno (o coordinación) puede registrar una tutoría SOP.';
$string['entry_register_title'] = 'Registrar tutoría — {$a}';
$string['entry_register_success'] = 'Tutoría registrada correctamente.';
$string['entry_pick_student_title'] = 'Elegir alumno';
$string['entry_pick_student_intro'] = 'Elige uno de tus alumnos para registrar una nueva tutoría.';
$string['entry_pick_student_label'] = 'Alumno';
$string['entry_pick_student_empty'] = 'No tienes alumnos con tutoría principal vigente en el curso académico activo.';

// Fase 5.3 — registro completo de tutorías.
$string['entry_full_register'] = 'Registro completo';
$string['entry_full_register_title'] = 'Registro completo de tutoría — {$a}';
$string['entry_field_reasons'] = 'Motivos';
$string['entry_field_noterestricted'] = 'Nota restringida';
$string['entry_field_visibilitytier'] = 'Visibilidad';
$string['entry_field_participanttype'] = 'Tipo de participante';
$string['entry_field_participantuser'] = 'Participante interno (usuario)';
$string['entry_field_participantexternalname'] = 'Participante externo (nombre)';
$string['entry_participants_header'] = 'Participantes';
$string['entry_participant_addmore'] = 'Añadir otro participante';

// Fase 5.4 — historial y detalle de tutorías.
$string['entry_history_empty'] = 'No hay tutorías registradas para este curso académico con estos filtros.';
$string['entry_viewdetail'] = 'Ver detalle';
$string['entry_detail_title'] = 'Detalle de la tutoría';

// Fase 5.5 — edición, versionado y anulación.
$string['monlaututoria:editownentry'] = 'Editar las tutorías propias';
$string['monlaututoria:editanyentry'] = 'Editar cualquier tutoría';
$string['monlaututoria:annulentry'] = 'Anular una tutoría';
$string['setting_entryeditwindow'] = 'Ventana de edición de tutorías';
$string['setting_entryeditwindow_desc'] = 'Tiempo tras el registro de una tutoría durante el cual se puede editar sin indicar un motivo. Pasado este plazo, cualquier edición exige un motivo del cambio.';
$string['setting_dashboard_showreferrals'] = 'Mostrar derivaciones en el panel del tutor';
$string['setting_dashboard_showreferrals_desc'] = 'Muestra la tarjeta y la sección de derivaciones en el panel del tutor. Desactivarlo no borra ni oculta las derivaciones en sí — solo deja de mostrarlas ahí; la pantalla de gestión de derivaciones sigue funcionando igual.';
$string['setting_dashboard_showpriority'] = 'Mostrar alumnos prioritarios en el panel del tutor';
$string['setting_dashboard_showpriority_desc'] = 'Muestra la tarjeta, la columna y la sección de alumnos prioritarios (un cálculo automático, no algo que el tutor marque) en el panel del tutor y en el bloque. Desactivarlo no cambia el cálculo, solo deja de mostrarlo.';
$string['entry_edit_title'] = 'Editar tutoría';
$string['entry_edit_success'] = 'Tutoría actualizada correctamente.';
$string['entry_field_editreason'] = 'Motivo del cambio';
$string['error_entry_edit_reason_required'] = 'Ha pasado la ventana de edición sin motivo — indica el motivo de este cambio.';
$string['error_entry_already_annulled'] = 'Esta tutoría ya está anulada.';
$string['entry_annul_title'] = 'Anular tutoría';
$string['entry_annul_success'] = 'Tutoría anulada correctamente.';
$string['entry_field_annulreason'] = 'Motivo de la anulación';
$string['entry_annul_confirm_checkbox'] = 'Confirmo que quiero anular esta tutoría.';
$string['entry_annul_confirm'] = 'Confirmar anulación';
$string['error_entry_annul_reason_required'] = 'Indica el motivo de la anulación.';
$string['error_entry_annul_not_confirmed'] = 'Debes confirmar que quieres anular esta tutoría.';
$string['evententryupdated'] = 'Tutoría editada';
$string['evententryannulled'] = 'Tutoría anulada';

// Fase 5.6 — adjuntos de tutorías.
$string['entryattachmentcategory_report'] = 'Informe';
$string['entryattachmentcategory_consent'] = 'Autorización';
$string['entryattachmentcategory_evidence'] = 'Evidencia';
$string['entryattachmentcategory_other'] = 'Otro';
$string['entry_attachment_category'] = 'Categoría documental';
$string['entry_attachment_files'] = 'Archivos';
$string['entry_attachment_upload'] = 'Subir archivos';
$string['entry_attachments_title'] = 'Adjuntos de la tutoría';
$string['entry_attachment_upload_success'] = 'Se han subido {$a} archivo(s) correctamente.';
$string['entry_attachments_empty'] = 'Esta tutoría todavía no tiene ningún archivo adjunto.';
$string['error_entry_attachment_category_invalid'] = 'Categoría documental no válida.';

// Fase 6.1/6.3 — acuerdos.
$string['monlaututoria:createagreement'] = 'Crear un acuerdo';
$string['monlaututoria:manageagreements'] = 'Completar, reabrir, posponer o cancelar acuerdos';
$string['priority_low'] = 'Baja';
$string['priority_medium'] = 'Media';
$string['priority_high'] = 'Alta';
$string['agreementstatus_pending'] = 'Pendiente';
$string['agreementstatus_in_progress'] = 'En curso';
$string['agreementstatus_completed'] = 'Completado';
$string['agreementstatus_cancelled'] = 'Cancelado';
$string['agreementstatus_overdue'] = 'Vencido';
$string['agreementresponsibletype_student'] = 'Alumno';
$string['agreementresponsibletype_tutor'] = 'Tutor';
$string['agreementresponsibletype_family'] = 'Familia';
$string['agreementresponsibletype_teacher'] = 'Docente';
$string['agreementresponsibletype_coordination'] = 'Coordinación';
$string['agreementresponsibletype_orientation'] = 'Orientación';
$string['agreementresponsibletype_company'] = 'Empresa';
$string['agreementresponsibletype_other'] = 'Otro';
$string['agreement_field_description'] = 'Descripción';
$string['agreement_field_responsibletype'] = 'Tipo de responsable';
$string['agreement_field_responsibleuser'] = 'Responsable (usuario de Moodle)';
$string['agreement_field_responsibleexternalname'] = 'Responsable (nombre externo)';
$string['agreement_field_duedate'] = 'Fecha límite';
$string['agreement_field_visibletostudent'] = 'Visible para el alumno';
$string['agreement_field_status'] = 'Estado';
$string['agreement_create'] = 'Crear acuerdo';
$string['agreement_create_title'] = 'Crear acuerdo — {$a}';
$string['agreement_create_success'] = 'Acuerdo creado correctamente.';
$string['agreement_complete'] = 'Marcar completado';
$string['agreement_reopen'] = 'Reabrir';
$string['agreement_cancel'] = 'Cancelar acuerdo';
$string['agreement_postpone'] = 'Posponer';
$string['agreement_postpone_title'] = 'Posponer acuerdo';
$string['agreement_action_success'] = 'Acuerdo actualizado correctamente.';
$string['agreement_confirm_cancel'] = '¿Cancelar este acuerdo? Esta acción no se puede deshacer desde aquí.';
$string['agreements_empty'] = 'Todavía no se ha creado ningún acuerdo.';
$string['agreements_filter_overdue'] = 'Solo vencidos';
$string['error_agreement_responsible_type_invalid'] = 'Tipo de responsable no válido.';
$string['error_agreement_responsible_identity_invalid'] = 'Selecciona exactamente un responsable: un usuario de Moodle o un nombre externo.';
$string['error_agreement_responsible_user_invalid'] = 'El usuario responsable seleccionado no existe o ha sido eliminado.';
$string['error_agreement_invalid_transition'] = 'Esta acción no se puede aplicar al acuerdo en su estado actual.';
$string['error_agreement_cannot_postpone_closed'] = 'Un acuerdo completado o cancelado no se puede posponer.';
$string['eventagreementcreated'] = 'Acuerdo creado';
$string['eventagreementupdated'] = 'Acuerdo actualizado';

// Fase 6.2/6.3 — seguimientos.
$string['monlaututoria:createfollowup'] = 'Crear un seguimiento';
$string['monlaututoria:managefollowups'] = 'Completar, reabrir, posponer o cancelar seguimientos';
$string['followupstatus_pending'] = 'Pendiente';
$string['followupstatus_completed'] = 'Completado';
$string['followupstatus_cancelled'] = 'Cancelado';
$string['followupstatus_overdue'] = 'Vencido';
$string['followup_field_duedate'] = 'Fecha prevista';
$string['followup_field_priority'] = 'Prioridad';
$string['followup_field_status'] = 'Estado';
$string['followup_create'] = 'Crear seguimiento';
$string['followup_create_title'] = 'Crear seguimiento — {$a}';
$string['followup_create_success'] = 'Seguimiento creado correctamente.';
$string['followup_complete'] = 'Marcar completado';
$string['followup_reopen'] = 'Reabrir';
$string['followup_cancel'] = 'Cancelar seguimiento';
$string['followup_action_success'] = 'Seguimiento actualizado correctamente.';
$string['followup_confirm_cancel'] = '¿Cancelar este seguimiento? Esta acción no se puede deshacer desde aquí.';
$string['followups_empty'] = 'Todavía no se ha creado ningún seguimiento.';
$string['followups_filter_overdue'] = 'Solo vencidos';
$string['studenttab_followups'] = 'Seguimientos';
$string['error_followup_priority_invalid'] = 'Prioridad no válida.';
$string['error_followup_invalid_transition'] = 'Esta acción no se puede aplicar al seguimiento en su estado actual.';
$string['error_followup_cannot_postpone_closed'] = 'Un seguimiento completado o cancelado no se puede posponer.';
$string['eventfollowupcreated'] = 'Seguimiento creado';
$string['eventfollowupupdated'] = 'Seguimiento actualizado';
$string['entry_field_followup'] = 'Cierra el seguimiento';

// Fase 6.4 — derivaciones.
$string['monlaututoria:createreferral'] = 'Crear una derivación';
$string['monlaututoria:managereferrals'] = 'Ver, asignar, resolver o cancelar cualquier derivación';
$string['referraldestination_coordination'] = 'Coordinación';
$string['referraldestination_orientation'] = 'Orientación';
$string['referraldestination_management'] = 'Dirección';
$string['referralstatus_pending'] = 'Pendiente';
$string['referralstatus_in_progress'] = 'En curso';
$string['referralstatus_resolved'] = 'Resuelta';
$string['referralstatus_cancelled'] = 'Cancelada';
$string['referral_field_destination'] = 'Destino';
$string['referral_field_reason'] = 'Motivo';
$string['referral_field_status'] = 'Estado';
$string['referral_field_assignedto'] = 'Asignado a';
$string['referral_field_resolution'] = 'Resolución';
$string['referral_field_originentry'] = 'Tutoría de origen';
$string['referral_create'] = 'Derivar';
$string['referral_create_title'] = 'Crear derivación — {$a}';
$string['referral_create_success'] = 'Derivación creada correctamente.';
$string['referral_assign'] = 'Asignar';
$string['referral_resolve'] = 'Resolver';
$string['referral_cancel'] = 'Cancelar derivación';
$string['referral_confirm_cancel'] = '¿Cancelar esta derivación? Esta acción no se puede deshacer desde aquí.';
$string['referral_action_success'] = 'Derivación actualizada correctamente.';
$string['referral_viewdetail'] = 'Ver detalle';
$string['referral_detail_title'] = 'Detalle de la derivación';
$string['referrals_title'] = 'Derivaciones';
$string['referrals_empty'] = 'Todavía no se ha creado ninguna derivación.';
$string['error_referral_destination_invalid'] = 'Destino no válido.';
$string['error_referral_priority_invalid'] = 'Prioridad no válida.';
$string['error_referral_reason_required'] = 'Indica el motivo de la derivación.';
$string['error_referral_resolution_required'] = 'Indica la resolución.';
$string['error_referral_invalid_transition'] = 'Esta acción no se puede aplicar a la derivación en su estado actual.';
$string['eventreferralcreated'] = 'Derivación creada';
$string['eventreferralupdated'] = 'Derivación actualizada';

// Fase 6.5 — Privacy API para acuerdos/seguimientos/derivaciones.
$string['privacy:metadata:agreement'] = 'Información sobre acuerdos: tutoría de origen, descripción, responsable, fecha límite, estado, visibilidad para el alumno.';
$string['privacy:metadata:agreement:studentid'] = 'El identificador del alumno al que concierne este acuerdo.';
$string['privacy:metadata:agreement:description'] = 'La descripción del acuerdo.';
$string['privacy:metadata:agreement:responsibletype'] = 'El tipo de responsable.';
$string['privacy:metadata:agreement:responsibleuserid'] = 'El identificador del responsable, cuando es un usuario de Moodle.';
$string['privacy:metadata:agreement:responsibleexternalname'] = 'El nombre del responsable, cuando no es un usuario de Moodle.';
$string['privacy:metadata:agreement:duedate'] = 'La fecha límite del acuerdo.';
$string['privacy:metadata:agreement:status'] = 'El estado del acuerdo.';
$string['privacy:metadata:agreement:visibletostudent'] = 'Si este acuerdo es visible para el alumno.';
$string['privacy:metadata:followup'] = 'Información sobre seguimientos: tutoría de origen, fecha prevista, prioridad, estado, tutoría de cierre.';
$string['privacy:metadata:followup:studentid'] = 'El identificador del alumno al que concierne este seguimiento.';
$string['privacy:metadata:followup:duedate'] = 'La fecha prevista del seguimiento.';
$string['privacy:metadata:followup:priority'] = 'La prioridad del seguimiento.';
$string['privacy:metadata:followup:status'] = 'El estado del seguimiento.';
$string['privacy:metadata:followup:closingentryid'] = 'El identificador de la tutoría que cerró este seguimiento, si lo hubo.';
$string['privacy:metadata:referral'] = 'Información sobre derivaciones: tutoría de origen, destino, motivo, prioridad, asignado, estado, resolución.';
$string['privacy:metadata:referral:studentid'] = 'El identificador del alumno al que concierne esta derivación.';
$string['privacy:metadata:referral:destination'] = 'El destino de la derivación.';
$string['privacy:metadata:referral:reason'] = 'El motivo indicado para la derivación.';
$string['privacy:metadata:referral:priority'] = 'La prioridad de la derivación.';
$string['privacy:metadata:referral:assignedto'] = 'El identificador del miembro del personal que gestiona la derivación, si está asignada.';
$string['privacy:metadata:referral:status'] = 'El estado de la derivación.';
$string['privacy:metadata:referral:resolution'] = 'La resolución indicada para la derivación, si se resolvió.';
$string['evententryattachmentadded'] = 'Adjunto añadido a una tutoría';

$string['dashboard_title'] = 'Panel del tutor';
$string['dashboard_summary_assigned'] = 'Alumnos asignados';
$string['dashboard_summary_attended'] = 'Con al menos una tutoria';
$string['dashboard_summary_pendinginitial'] = 'Pendientes de primera tutoria';
$string['dashboard_summary_coverage'] = 'Cobertura';
$string['dashboard_students_empty'] = 'No tienes alumnos principales asignados en este curso academico.';
$string['dashboard_col_lastentry'] = 'Ultima tutoria';
$string['dashboard_col_entrycount'] = 'Tutorias del curso';
$string['dashboard_col_missinginitial'] = 'Sin tutoria inicial';
$string['dashboard_col_coverage'] = 'Estado de cobertura';
$string['dashboard_coveragestatus_covered'] = 'Cubierto';
$string['dashboard_coveragestatus_pending_initial'] = 'Pendiente de primera tutoria';

$string['dashboard_summary_followupsoverdue'] = 'Seguimientos vencidos';
$string['dashboard_summary_agreementspending'] = 'Acuerdos pendientes';
$string['dashboard_summary_referrals'] = 'Casos derivados';
$string['dashboard_summary_priority'] = 'Alumnos prioritarios';
$string['dashboard_summary_familycontacts'] = 'Contactos con familias';
$string['dashboard_studentfilter_all'] = 'Todos los alumnos';
$string['dashboard_studentfilter_covered'] = 'Con tutoria';
$string['dashboard_studentfilter_notutoring'] = 'Sin tutoria';
$string['dashboard_studentfilter_pendinginitial'] = 'Solo sin tutoria inicial';
$string['dashboard_studentfilter_withpending'] = 'Solo con pendientes';
$string['dashboard_studentfilter_priority'] = 'Solo prioritarios';
$string['dashboard_pendingfilter_all'] = 'Todos los pendientes';
$string['dashboard_pendingfilter_open'] = 'Solo abiertos';
$string['dashboard_pendingfilter_overdue'] = 'Solo vencidos';
$string['dashboard_section_students'] = 'Mis alumnos';
$string['dashboard_view_roster'] = 'Mis alumnos';
$string['dashboard_view_pending'] = 'Pendientes';
$string['dashboard_roster_entrycount'] = '{$a} tutorías';
$string['dashboard_roster_lastentry'] = 'Última: {$a}';
$string['dashboard_roster_noentry'] = 'Sin tutoría aún';
$string['dashboard_roster_formertutor'] = 'Tutoría finalizada';
$string['dashboard_section_formerstudents'] = 'Alumnos que tutoricé antes';
$string['dashboard_section_sopstudents'] = 'Mis alumnos SOP';
$string['dashboard_section_followups'] = 'Seguimientos';
$string['dashboard_section_agreements'] = 'Acuerdos';
$string['dashboard_section_referrals'] = 'Casos derivados';
$string['dashboard_section_priority'] = 'Alumnos prioritarios';
$string['dashboard_col_pendingbundle'] = 'Pendientes';
$string['dashboard_col_priority'] = 'Prioritario';
$string['dashboard_action_viewstudent'] = 'Ver ficha';
$string['dashboard_action_createentry'] = 'Registrar tutoria';
$string['dashboard_action_createfollowup'] = 'Crear seguimiento';
$string['dashboard_followups_empty'] = 'No hay seguimientos que mostrar con el filtro actual.';
$string['dashboard_agreements_empty'] = 'No hay acuerdos que mostrar con el filtro actual.';
$string['dashboard_priority_empty'] = 'No hay alumnos prioritarios con el filtro actual.';

$string['monlaututoria:viewcoordinationdashboard'] = 'Ver panel agregado de coordinacion';
$string['monlaututoria:exportcoordinationreports'] = 'Exportar informes de coordinacion';
$string['monlaututoria:managecoordinationscopes'] = 'Gestionar ambitos de coordinacion';
$string['coordination_title'] = 'Panel de coordinacion';
$string['coordination_scopes_title'] = 'Ambitos de coordinacion';
$string['cohort_visibility_title'] = 'Cohortes habilitadas';
$string['cohort_visibility_intro'] = 'Elige qué cohortes de Moodle son relevantes para Monlau Tutoría. Solo las marcadas aquí aparecerán en la creación de asignaciones, la asignación por cohorte y el panel de coordinación. Desmarca las que quieras ocultar (por ejemplo, cohortes de personal). Aviso: si al guardar no queda ninguna marcada, se interpretará como "sin restricción" y volverán a mostrarse todas — deja marcada al menos una para ocultar el resto de verdad.';
$string['cohort_visibility_empty'] = 'No hay ninguna cohorte creada en este sitio todavía.';
$string['cohort_visibility_save'] = 'Guardar cohortes habilitadas';
$string['cohort_visibility_saved'] = 'Cohortes habilitadas actualizadas correctamente.';
$string['coordination_dashboard_noscope'] = 'No tienes ningun ambito de coordinacion asignado.';
$string['coordination_dashboard_empty'] = 'No hay datos para los filtros actuales.';
$string['coordination_generatedat'] = 'Generado el: {$a}';
$string['coordination_cohort_all'] = 'Todas mis cohortes';
$string['coordination_tutor_all'] = 'Todos los tutores';
$string['coordination_export_csv'] = 'Exportar CSV';
$string['coordination_export_xlsx'] = 'Exportar hoja de calculo';
$string['coordination_export_summary'] = 'Resumen';
$string['coordination_export_column_section'] = 'Seccion';
$string['coordination_export_column_label'] = 'Etiqueta';
$string['coordination_export_column_generatedat'] = 'Fecha de generacion';
$string['coordination_export_column_format'] = 'Formato';
$string['coordination_summary_population'] = 'Poblacion analizada';
$string['coordination_summary_withinitial'] = 'Con tutoria inicial';
$string['coordination_summary_withoutentry'] = 'Sin ninguna tutoria';
$string['coordination_summary_overduefollowups'] = 'Seguimientos vencidos';
$string['coordination_summary_opencases'] = 'Casos abiertos';
$string['coordination_quality_title'] = 'Indicadores de calidad';
$string['coordination_quality_timetofirst'] = 'Tiempo hasta primera tutoria';
$string['coordination_quality_agreements'] = 'Acuerdos completados';
$string['coordination_quality_followups'] = 'Seguimientos en plazo';
$string['coordination_quality_familycontacts'] = 'Contactos con familias';
$string['coordination_quality_continuity'] = 'Continuidad tras cambio de tutor';
$string['coordination_breakdown_cohorts'] = 'Vista por grupo';
$string['coordination_breakdown_tutors'] = 'Vista por tutor';
$string['coordination_breakdown_label'] = 'Ambito';
$string['coordination_breakdown_population'] = 'Alumnos';
$string['coordination_breakdown_withinitial'] = 'Con inicial';
$string['coordination_breakdown_withoutentry'] = 'Sin tutorias';
$string['coordination_breakdown_overduefollowups'] = 'Seg. vencidos';
$string['coordination_breakdown_opencases'] = 'Casos abiertos';
$string['coordination_breakdown_unassigned'] = 'Sin tutor vigente';
$string['coordination_scope_user'] = 'Usuario';
$string['coordination_scope_availablecohorts'] = 'Cohortes';
$string['coordination_scope_assignments'] = 'Asignaciones vigentes de ambito';
$string['coordination_scope_current'] = 'Editar ambito de: {$a}';
$string['coordination_scope_save'] = 'Guardar ambito';
$string['coordination_scope_saved'] = 'Ambito actualizado.';
$string['coordination_scope_empty'] = 'No hay ambitos de coordinacion asignados todavia.';
$string['eventcoordinationdashboardexported'] = 'Informe de coordinacion exportado';

$string['notification_preferences_title'] = 'Preferencias de notificaciones';
$string['notification_preferences_intro'] = 'Configura que avisos deseas recibir y con que frecuencia resumida.';
$string['notification_preferences_save'] = 'Guardar preferencias';
$string['notification_preferences_saved'] = 'Preferencias de notificaciones guardadas.';
$string['notification_pref_assignmentchanges'] = 'Avisarme cuando se me asigne o reasigne un alumno';
$string['notification_pref_referralchanges'] = 'Avisarme sobre derivaciones recibidas o devueltas';
$string['notification_pref_followupreminders'] = 'Avisarme sobre seguimientos proximos o vencidos';
$string['notification_pref_agreementreminders'] = 'Avisarme sobre acuerdos proximos o vencidos';
$string['notification_pref_digestfrequency'] = 'Frecuencia del resumen';
$string['notification_frequency_none'] = 'Sin resumen';
$string['notification_frequency_daily'] = 'Diario';
$string['notification_frequency_weekly'] = 'Semanal';
$string['notification_subject_assignment_assigned'] = 'Nueva asignacion tutorial';
$string['notification_body_assignment_assigned'] = 'Tienes una nueva actualizacion tutorial en Moodle. Entra para revisar el alumno asignado.';
$string['notification_subject_assignment_reassigned'] = 'Reasignacion tutorial';
$string['notification_body_assignment_reassigned'] = 'Se ha actualizado una tutoria asignada en Moodle. Entra para revisar el cambio.';
$string['notification_subject_referral_assigned'] = 'Nueva derivacion asignada';
$string['notification_body_referral_assigned'] = 'Tienes una derivacion pendiente en Moodle. Entra para revisarla.';
$string['notification_subject_referral_returned'] = 'Actualizacion de derivacion';
$string['notification_body_referral_returned'] = 'Una derivacion creada por ti ha cambiado en Moodle. Entra para revisar la actualizacion.';
$string['notification_subject_followup_due'] = 'Seguimiento proximo';
$string['notification_body_followup_due'] = 'Tienes un seguimiento proximo en Moodle. Entra para revisarlo.';
$string['notification_subject_followup_overdue'] = 'Seguimiento vencido';
$string['notification_body_followup_overdue'] = 'Tienes un seguimiento vencido en Moodle. Entra para revisarlo.';
$string['notification_subject_agreement_due'] = 'Acuerdo proximo';
$string['notification_body_agreement_due'] = 'Tienes un acuerdo proximo en Moodle. Entra para revisarlo.';
$string['notification_subject_agreement_overdue'] = 'Acuerdo vencido';
$string['notification_body_agreement_overdue'] = 'Tienes un acuerdo vencido en Moodle. Entra para revisarlo.';
$string['notification_subject_daily_digest'] = 'Resumen diario de tutorias';
$string['notification_body_daily_digest'] = 'Resumen diario: {$a->assignedcount} alumnos, {$a->upcomingfollowupcount} seguimientos proximos, {$a->overduefollowupcount} seguimientos vencidos, {$a->pendingagreementcount} acuerdos pendientes, {$a->overdueagreementcount} acuerdos vencidos y {$a->openreferralcount} derivaciones abiertas.';
$string['notification_subject_weekly_digest'] = 'Resumen semanal de tutorias';
$string['notification_body_weekly_digest'] = 'Resumen semanal: {$a->assignedcount} alumnos, {$a->upcomingfollowupcount} seguimientos proximos, {$a->overduefollowupcount} seguimientos vencidos, {$a->pendingagreementcount} acuerdos pendientes, {$a->overdueagreementcount} acuerdos vencidos y {$a->openreferralcount} derivaciones abiertas.';
$string['task_dispatch_notification'] = 'Enviar una notificacion de tutoria';
$string['task_send_notification_reminders'] = 'Generar recordatorios de tutorias';
$string['task_retry_failed_notifications'] = 'Reintentar notificaciones fallidas';
$string['task_cleanup_notification_logs'] = 'Limpiar logs de notificaciones';
$string['privacy:metadata:notification'] = 'Metadatos operativos de notificaciones del plugin.';
$string['privacy:metadata:notification:notificationtype'] = 'Tipo de notificacion enviada o programada.';
$string['privacy:metadata:notification:recipientid'] = 'Usuario destinatario de la notificacion.';
$string['privacy:metadata:notification:actorid'] = 'Usuario que origino la notificacion cuando aplica.';
$string['privacy:metadata:notification:entitytype'] = 'Tipo de entidad relacionada.';
$string['privacy:metadata:notification:entityid'] = 'Identificador de la entidad relacionada.';
$string['privacy:metadata:notification:digestkey'] = 'Clave de deduplicacion diaria o semanal.';
$string['privacy:metadata:notification:status'] = 'Estado del envio.';
$string['privacy:metadata:notification:attempts'] = 'Numero de intentos de envio.';
$string['privacy:metadata:notification:lasterror'] = 'Ultimo error de entrega registrado.';
$string['privacy:metadata:notification:timesent'] = 'Momento de envio.';

$string['nav_dashboard'] = 'Panel';
$string['nav_myfile'] = 'Mis tutorías';
$string['nav_dashboard_tip'] = 'Resumen rápido de alumnos, pendientes y prioridades del tutor.';
$string['nav_assignments'] = 'Asignaciones';
$string['nav_assignments_tip'] = 'Consulta el reparto de alumnos y abre el detalle de cada asignación.';
$string['nav_referrals'] = 'Derivaciones';
$string['nav_referrals_tip'] = 'Gestiona las derivaciones abiertas y revisa su estado.';
$string['nav_coordination'] = 'Coordinación';
$string['nav_coordination_tip'] = 'Visión global por curso, cohorte y tutor.';
$string['nav_coordinators'] = 'Coordinadores';
$string['nav_coordinators_tip'] = 'Define quién puede acceder a coordinación y qué cohortes supervisa cada uno.';
$string['nav_notifications'] = 'Notificaciones';
$string['nav_notifications_tip'] = 'Ajusta recordatorios, cambios y digestos del plugin.';
$string['nav_configuration'] = 'Configuración';
$string['nav_configuration_tip'] = 'Accede a cursos académicos, motivos y modalidades.';
$string['nav_help'] = 'Ayuda';
$string['nav_help_tip'] = 'Aprende qué es una tutoría, un acuerdo, un seguimiento y una derivación.';

$string['help_page_title'] = 'Guía rápida de Monlau Tutoría';
$string['help_page_intro'] = 'Estos son los 4 conceptos que vas a usar más a menudo en el día a día. Cada uno nace normalmente de una tutoría, y cada uno tiene su propio ciclo de vida.';
$string['help_concept_entry_title'] = 'Tutoría';
$string['help_concept_entry_short'] = 'Es el registro de una actuación tutorial concreta: cuándo ocurrió, con qué modalidad de contacto, por qué motivo, y su contenido en 3 niveles — lo compartido con el alumno, la nota interna (solo tutores y coordinación) y la nota restringida (todavía más limitada).';
$string['help_concept_entry_full'] = 'Es el punto de partida de todo lo demás: un acuerdo, un seguimiento o una derivación siempre nacen de una tutoría concreta. Puedes registrar una tutoría rápida (lo mínimo, en menos de un minuto) o completa (con varios motivos, participantes internos o externos, y la nota restringida si tienes permiso). Una tutoría se puede editar poco después de crearla sin dar motivo; pasado un tiempo configurable, editarla exige explicar por qué. Nunca se borra: si hay que dejarla sin efecto, se anula, y queda constancia de que existió.';
$string['help_page_intro_simple'] = 'Guía rápida del registro de tutorías.';
$string['help_concept_entry_short_simple'] = 'Es el registro de una actuación tutorial concreta: cuándo ocurrió, con qué modalidad de contacto, por qué motivo, y su contenido en dos partes — el comentario compartido con el alumno y la nota interna (solo la ven tutores y coordinación).';
$string['help_concept_entry_full_simple'] = 'Registras una tutoría desde la ficha del alumno (pestaña Tutorías) o desde el panel. Puedes editarla poco después de crearla sin dar motivo; pasado un tiempo configurable, editarla exige explicar por qué. Nunca se borra: si hay que dejarla sin efecto, se anula, y queda constancia de que existió.';
$string['help_visibility_body_simple'] = 'El alumno solo ve el contenido de tutoría marcado como "compartido con el alumno": fecha, modalidad, motivo y el comentario compartido. Nunca ve la nota interna. Esto se aplica siempre en el servidor, nunca se oculta solo con estilos.';
$string['help_concept_agreement_title'] = 'Acuerdo';
$string['help_concept_agreement_short'] = 'Es un compromiso concreto que sale de una tutoría: quién es responsable de cumplirlo (el propio alumno, la familia, el tutor, orientación...) y para cuándo.';
$string['help_concept_agreement_full'] = 'Un acuerdo se puede marcar como visible para el alumno o mantenerlo interno. A lo largo de su vida se puede marcar como completado, reabrir si hace falta retomarlo, posponer su fecha límite, o cancelarlo si ya no aplica.';
$string['help_concept_followup_title'] = 'Seguimiento';
$string['help_concept_followup_short'] = 'Marca una fecha futura para revisar cómo evoluciona algo tratado en una tutoría, con una prioridad (baja, media o alta).';
$string['help_concept_followup_full'] = 'Se puede cerrar de dos formas: manualmente (sin más), o registrando una nueva tutoría vinculada que documenta cómo se resolvió — esta segunda forma es la más habitual, ya que deja constancia de qué pasó de verdad. Un seguimiento nunca lo ve el alumno.';
$string['help_concept_referral_title'] = 'Derivación';
$string['help_concept_referral_short'] = 'Traslada un caso a coordinación, orientación o dirección para que lo gestione otra persona, con un motivo redactado de nuevo para la ocasión — nunca una copia de las notas internas de la tutoría.';
$string['help_concept_referral_full'] = 'Quien recibe una derivación puede no tener ninguna relación de tutoría previa con el alumno — por eso puede verla y gestionarla aunque no aparezca como su tutor. Se puede asignar a una persona concreta y, cuando se resuelve, se registra la resolución. Como el resto, tampoco la ve el alumno.';
$string['help_visibility_title'] = '¿Qué ve el alumno?';
$string['help_visibility_body'] = 'El alumno solo ve el contenido de tutoría marcado como "compartido con el alumno", y los acuerdos que se hayan marcado explícitamente como visibles para él. Nunca ve las notas internas o restringidas de una tutoría, ni los seguimientos, ni las derivaciones — esto se aplica siempre en el servidor, nunca se oculta solo con estilos.';
$string['help_dashboard_title'] = '¿Cómo leo este panel?';
$string['help_dashboard_body'] = 'Las tarjetas de arriba resumen tu situación con un vistazo: alumnos asignados, cobertura de tutorías, y cuánto tienes pendiente (seguimientos vencidos, acuerdos pendientes, derivaciones abiertas, alumnos prioritarios). Debajo tienes el detalle de cada cosa, con acciones rápidas para no tener que abrir la ficha de cada alumno una por una.';
$string['help_studentview_title'] = '¿Qué hay en cada pestaña de la ficha?';
$string['help_studentview_body'] = 'Resumen: tutor, cohorte y curso académico. Historial: el histórico de asignaciones de tutor de este alumno. Tutorías: el registro completo de actuaciones tutoriales. Acuerdos y Seguimientos: los compromisos y fechas de revisión que han salido de esas tutorías.';
$string['nav_student'] = 'Ficha del alumno';
$string['nav_student_tip'] = 'Vuelve a la ficha longitudinal del alumno actual.';

$string['dashboard_intro'] = 'Revisa de un vistazo los alumnos asignados, los pendientes y las alertas que requieren acción tutorial.';
$string['assignments_intro'] = 'Filtra asignaciones, entra en cada detalle y localiza rápido la ficha del alumno.';
$string['assignment_detail_intro'] = 'Aquí puedes revisar el contexto completo de la asignación y saltar a la ficha del alumno.';
$string['student_detail_intro'] = 'Usa las pestañas para moverte entre el resumen, historial, tutorías, acuerdos y seguimientos del alumno.';
$string['entry_detail_intro'] = 'Esta pantalla centraliza el contenido de la tutoría y sus acciones relacionadas.';
$string['referrals_intro'] = 'Consulta la cola de derivaciones y aplica filtros por estado para priorizar la gestión.';
$string['referral_detail_intro'] = 'Revisa la derivación, su responsable y las acciones disponibles sin salir del contexto.';
$string['coordination_intro'] = 'Analiza cobertura, calidad y desglose de tutorías por curso, cohorte o tutor.';
$string['notifications_intro'] = 'Decide qué avisos quieres recibir y con qué frecuencia para no perder seguimiento.';
$string['academicyears_intro'] = 'Gestiona los cursos académicos disponibles y controla cuál está activo o bloqueado.';
$string['reasons_intro'] = 'Mantiene el catálogo de motivos de tutoría y su visibilidad por defecto.';
$string['modalities_intro'] = 'Define los canales de contacto disponibles para registrar tutorías.';

$string['page_back_dashboard'] = 'Volver al panel';
$string['page_back_assignments'] = 'Volver a asignaciones';
$string['page_back_referrals'] = 'Volver a derivaciones';
$string['page_back_student_entries'] = 'Volver a tutorías del alumno';
$string['page_back_configuration'] = 'Volver a configuración';

$string['assignments_create_tip'] = 'Crea una nueva asignación manual y después accede a su detalle.';

$string['studenttab_summary_tip'] = 'Vista general del alumno en el curso académico seleccionado.';
$string['studenttab_history_tip'] = 'Historial de asignaciones y cambios de tutor a lo largo del tiempo.';
$string['studenttab_tutoring_tip'] = 'Listado de tutorías registradas para este alumno.';
$string['studenttab_agreements_tip'] = 'Seguimiento de acuerdos asociados a tutorías del alumno.';
$string['studenttab_followups_tip'] = 'Pendientes de seguimiento abiertos para este alumno.';

$string['configuration_intro'] = 'Punto de entrada para la administración del plugin y sus catálogos base.';

$string['settings_entryeditwindow_title'] = 'Ventana de edición de tutorías';

$string['assignment_reassign'] = 'Reasignar tutor';
$string['assignment_reassign_title'] = 'Reasignar tutor';
$string['assignment_reassign_confirm'] = 'Confirmar reasignación';
$string['assignment_reassign_confirm_checkbox'] = 'Confirmo que deseo reasignar esta tutoría a otro tutor.';
$string['assignment_reassign_success'] = 'Tutor reasignado correctamente.';
$string['assignment_reassign_intro'] = 'Cierra la asignación principal actual y crea una nueva con el tutor seleccionado, conservando el historial.';
$string['assignment_field_newtutor'] = 'Nuevo tutor';
$string['assignment_field_reassignreason'] = 'Motivo de la reasignación';
$string['assignment_field_reassigndate'] = 'Fecha efectiva de la reasignación';
$string['assignment_field_keepcotutors'] = 'Mantener los cotutores activos del alumno';
$string['warning_assignment_reassign'] = 'Esta acción cerrará la asignación principal actual y creará una nueva con el tutor seleccionado.';
$string['warning_assignment_reassign_cotutors'] = 'El alumno tiene {} cotutor(es) activo(s). Puedes mantenerlos o cerrarlos en la misma reasignación.';
$string['error_assignment_reassign_only_primary'] = 'Solo se puede reasignar una asignación principal activa.';
$string['error_assignment_reassign_not_confirmed'] = 'Debes confirmar la reasignación.';

$string['coordination_scope_manage'] = 'Gestionar coordinadores';
$string['coordination_scope_manage_help'] = 'Elegir qué coordinadores pueden supervisar cada cohorte.';
$string['coordination_dashboard_intro'] = 'Vista agregada para coordinación. El alcance se define por cohortes asignadas al coordinador y, dentro de ese alcance, se puede filtrar por tutor.';
$string['coordination_filter_help'] = 'Primero acota por cohorte y después, si lo necesitas, filtra por tutor dentro de esas cohortes.';
$string['error_coordination_scope_invalid_user'] = 'Solo puedes asignar ámbitos a usuarios con acceso al panel de coordinación.';
$string['coordination_scope_intro'] = 'Selecciona un coordinador y asígnale las cohortes que podrá supervisar. Dentro del panel, después podrá filtrar por tutor.';
$string['coordination_scope_help_steps'] = 'Para habilitar un coordinador: 1) asigna al usuario un rol con la capacidad local/monlaututoria:viewcoordinationdashboard en contexto sistema, 2) selecciónalo en esta pantalla y 3) marca las cohortes que podrá supervisar.';

// Fase 13 — modo simple.
$string['setting_simplemode'] = 'Modo simple';
$string['setting_simplemode_desc'] = 'Reduce el plugin a lo esencial para el día a día: el tutor registra tutorías y el alumno consulta las suyas y quién es su tutor. Oculta acuerdos, seguimientos, derivaciones, panel de coordinación, notificaciones, importaciones (CSV y cohortes), cotutores y adjuntos — de la navegación y de sus páginas. No borra nada: los datos, los servicios y las pruebas siguen intactos, y desactivar esta casilla lo restaura todo. Desactivado por defecto.';
$string['error_featuredisabled'] = 'Esta función no está disponible: el sitio está en modo simple de Monlau Tutoría.';
