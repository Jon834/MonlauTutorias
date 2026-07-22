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
$string['error_assignment_isprimary_type_mismatch'] = 'Solo una asignación de tipo principal puede marcarse como tutor principal.';
$string['error_assignment_primary_duplicate'] = 'Este alumno ya tiene un tutor principal activo para este curso académico.';
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
