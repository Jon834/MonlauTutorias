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
 * Cadenes d'idioma en català per a local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Monlau Tutoria';
$string['monlaututoria:view'] = 'Veure Monlau Tutoria';
$string['monlaututoria:viewconfiguration'] = 'Veure la configuració de Monlau Tutoria';
$string['monlaututoria:manageacademicyears'] = 'Gestionar cursos acadèmics';
$string['monlaututoria:managecatalogues'] = 'Gestionar catàlegs de tutoria';
$string['monlaututoria:overridelock'] = 'Anul·lar el bloqueig de cursos acadèmics';

$string['academicyears'] = 'Cursos acadèmics';
$string['reasons'] = 'Motius de tutoria';
$string['modalities'] = 'Modalitats de contacte';

$string['academicyear_name'] = 'Nom';
$string['academicyear_shortname'] = 'Nom curt';
$string['academicyear_startdate'] = 'Data d\'inici';
$string['academicyear_enddate'] = 'Data de fi';
$string['academicyear_active'] = 'Actiu';
$string['academicyear_locked'] = 'Bloquejat';
$string['academicyear_create'] = 'Nou curs acadèmic';
$string['academicyear_edit'] = 'Editar';
$string['academicyear_activate'] = 'Activar';
$string['academicyear_lock'] = 'Bloquejar';
$string['academicyear_unlock'] = 'Desbloquejar';
$string['academicyear_delete'] = 'Eliminar';
$string['academicyear_list_empty'] = 'Encara no s\'ha creat cap curs acadèmic.';
$string['academicyear_activate_confirm'] = 'El curs acadèmic "{$a}" està actualment actiu. Si actives aquest es desactivarà l\'anterior. Continuar?';
$string['academicyear_activate_confirm_noactive'] = 'Activar aquest curs acadèmic?';
$string['academicyear_activate_success'] = 'Curs acadèmic activat.';
$string['academicyear_locked_success'] = 'Curs acadèmic bloquejat.';
$string['academicyear_unlocked_success'] = 'Curs acadèmic desbloquejat.';
$string['academicyear_delete_confirm'] = 'Eliminar el curs acadèmic "{$a}"? Aquesta acció no es pot desfer.';
$string['academicyear_delete_success'] = 'Curs acadèmic eliminat.';
$string['academicyear_delete_blocked_active'] = 'No es pot eliminar el curs acadèmic actiu.';
$string['academicyear_delete_blocked_used'] = 'Aquest curs acadèmic no es pot eliminar perquè hi ha altres dades que el referencien.';
$string['noactiveacademicyear_warning'] = 'No hi ha cap curs acadèmic actiu. Crea\'n i activa\'n un per continuar.';

$string['error_enddate_before_startdate'] = 'La data de fi ha de ser posterior a la data d\'inici.';
$string['error_shortname_duplicate'] = 'Aquest nom curt ja està en ús.';
$string['error_academicyear_locked'] = 'Aquest curs acadèmic està bloquejat i no es pot modificar.';
$string['error_noaccess_overridelock'] = 'No tens permís per desbloquejar aquest curs acadèmic.';

$string['reason_name'] = 'Nom';
$string['reason_shortname'] = 'Nom curt';
$string['reason_description'] = 'Descripció';
$string['reason_active'] = 'Actiu';
$string['reason_requiresfollowup'] = 'Requereix seguiment';
$string['reason_defaultvisibility'] = 'Visibilitat per defecte';
$string['reason_create'] = 'Nou motiu';
$string['reason_edit'] = 'Editar';
$string['reason_activate'] = 'Activar';
$string['reason_deactivate'] = 'Desactivar';
$string['reason_delete'] = 'Eliminar';
$string['reason_delete_confirm'] = 'Eliminar el motiu "{$a}"? Aquesta acció no es pot desfer.';
$string['reason_moveup'] = 'Pujar';
$string['reason_movedown'] = 'Baixar';
$string['reason_list_empty'] = 'Encara no s\'ha creat cap motiu.';
$string['reason_delete_blocked_used'] = 'Aquest motiu no es pot eliminar perquè hi ha altres dades que el referencien.';

$string['modality_name'] = 'Nom';
$string['modality_shortname'] = 'Nom curt';
$string['modality_description'] = 'Descripció';
$string['modality_active'] = 'Actiu';
$string['modality_create'] = 'Nova modalitat';
$string['modality_edit'] = 'Editar';
$string['modality_activate'] = 'Activar';
$string['modality_deactivate'] = 'Desactivar';
$string['modality_delete'] = 'Eliminar';
$string['modality_delete_confirm'] = 'Eliminar la modalitat "{$a}"? Aquesta acció no es pot desfer.';
$string['modality_moveup'] = 'Pujar';
$string['modality_movedown'] = 'Baixar';
$string['modality_list_empty'] = 'Encara no s\'ha creat cap modalitat.';
$string['modality_delete_blocked_used'] = 'Aquesta modalitat no es pot eliminar perquè hi ha altres dades que la referencien.';

$string['visibility_shared'] = 'Compartit amb l\'alumne';
$string['visibility_internal'] = 'Intern tutorial';
$string['visibility_restricted'] = 'Restringit';

$string['eventacademicyearcreated'] = 'Curs acadèmic creat';
$string['eventacademicyearupdated'] = 'Curs acadèmic actualitzat';
$string['eventacademicyearactivated'] = 'Curs acadèmic activat';
$string['eventacademicyearlocked'] = 'Curs acadèmic bloquejat o desbloquejat';
$string['eventreasoncreated'] = 'Motiu de tutoria creat';
$string['eventreasonupdated'] = 'Motiu de tutoria actualitzat';
$string['eventreasonactivated'] = 'Motiu de tutoria activat o desactivat';
$string['eventmodalitycreated'] = 'Modalitat de contacte creada';
$string['eventmodalityupdated'] = 'Modalitat de contacte actualitzada';
$string['eventmodalityactivated'] = 'Modalitat de contacte activada o desactivada';

$string['reason_seed_acogida_inicial'] = 'Acollida inicial';
$string['reason_seed_seguimiento_ordinario'] = 'Seguiment ordinari';
$string['reason_seed_rendimiento_academico'] = 'Rendiment acadèmic';
$string['reason_seed_asistencia'] = 'Assistència';
$string['reason_seed_puntualidad'] = 'Puntualitat';
$string['reason_seed_convivencia'] = 'Convivència';
$string['reason_seed_motivacion'] = 'Motivació';
$string['reason_seed_habitos_estudio'] = 'Hàbits d\'estudi';
$string['reason_seed_organizacion'] = 'Organització';
$string['reason_seed_orientacion_academica'] = 'Orientació acadèmica';
$string['reason_seed_orientacion_profesional'] = 'Orientació professional';
$string['reason_seed_practicas_empresa'] = 'Pràctiques a l\'empresa';
$string['reason_seed_situacion_personal'] = 'Situació personal';
$string['reason_seed_seguimiento_acuerdos'] = 'Seguiment d\'acords';
$string['reason_seed_contacto_familia'] = 'Contacte amb la família';
$string['reason_seed_solicitud_alumno'] = 'Sol·licitud de l\'alumne';
$string['reason_seed_solicitud_familia'] = 'Sol·licitud de la família';
$string['reason_seed_reconocimiento_positivo'] = 'Reconeixement positiu';
$string['reason_seed_derivacion'] = 'Derivació';
$string['reason_seed_otro'] = 'Altre';

$string['modality_seed_presencial'] = 'Presencial';
$string['modality_seed_telefono'] = 'Telèfon';
$string['modality_seed_videoconferencia'] = 'Videoconferència';
$string['modality_seed_correo_electronico'] = 'Correu electrònic';
$string['modality_seed_mensajeria'] = 'Missatgeria';
$string['modality_seed_reunion_coordinacion'] = 'Reunió de coordinació';
$string['modality_seed_otra'] = 'Altra';

$string['privacy:metadata:createdby'] = 'L\'usuari que va crear aquest registre.';
$string['privacy:metadata:modifiedby'] = 'L\'usuari que va modificar aquest registre per última vegada.';
$string['privacy:metadata:timecreated'] = 'La data en què es va crear el registre.';
$string['privacy:metadata:timemodified'] = 'La data de l\'última modificació del registre.';
$string['privacy:metadata:academicyear'] = 'Informació sobre els cursos acadèmics, incloent-hi qui va crear o modificar per última vegada cadascun.';
$string['privacy:metadata:academicyear:name'] = 'El nom visible del curs acadèmic.';
$string['privacy:metadata:academicyear:shortname'] = 'El nom curt estable del curs acadèmic.';
$string['privacy:metadata:reason'] = 'Informació sobre els motius de tutoria, incloent-hi qui va crear o modificar per última vegada cadascun.';
$string['privacy:metadata:reason:name'] = 'El nom visible del motiu.';
$string['privacy:metadata:reason:shortname'] = 'El nom curt estable del motiu.';
$string['privacy:metadata:modality'] = 'Informació sobre les modalitats de contacte, incloent-hi qui va crear o modificar per última vegada cadascuna.';
$string['privacy:metadata:modality:name'] = 'El nom visible de la modalitat.';
$string['privacy:metadata:modality:shortname'] = 'El nom curt estable de la modalitat.';

$string['monlaututoria:viewownstudents'] = 'Veure els alumnes propis assignats';
$string['monlaututoria:viewstudent'] = 'Veure la fitxa de tutoria d\'un alumne concret';
$string['monlaututoria:viewhistoricalassignments'] = 'Veure les assignacions històriques (tancades) pròpies';
$string['monlaututoria:assignstudents'] = 'Crear assignacions d\'alumnes';
$string['monlaututoria:manageassignments'] = 'Gestionar assignacions existents';
$string['monlaututoria:managecohortassignments'] = 'Gestionar assignacions des de cohorts';
$string['monlaututoria:importassignments'] = 'Importar assignacions des de CSV';
$string['monlaututoria:reassignstudents'] = 'Reassignar alumnes a un nou tutor';
$string['monlaututoria:viewallassignments'] = 'Veure totes les assignacions sense restricció d\'àmbit';
$string['monlaututoria:manageclosedassignments'] = 'Reobrir o modificar assignacions tancades';

$string['error_assignment_self'] = 'Un alumne no pot ser el seu propi tutor.';
$string['error_assignment_invalid_student'] = 'L\'alumne seleccionat no existeix o ha estat eliminat.';
$string['error_assignment_invalid_tutor'] = 'El tutor seleccionat no existeix o ha estat eliminat.';
$string['error_assignment_student_suspended'] = 'El compte de l\'alumne seleccionat està suspès.';
$string['error_assignment_tutor_suspended'] = 'El compte del tutor seleccionat està suspès.';
$string['error_assignment_academicyear_invalid'] = 'El curs acadèmic seleccionat no existeix.';
$string['error_assignment_academicyear_locked'] = 'El curs acadèmic seleccionat està bloquejat per a noves assignacions.';
$string['error_assignment_invalid_cohort'] = 'La cohort seleccionada no existeix.';
$string['error_assignment_dates_invalid'] = 'La data de fi no pot ser anterior a la data d\'inici.';
$string['error_assignment_duplicate'] = 'Ja existeix una assignació activa idèntica.';
$string['error_assignment_isprimary_type_mismatch'] = 'Només una assignació de tipus principal es pot marcar com a tutor principal.';
$string['error_assignment_primary_duplicate'] = 'Aquest alumne ja té un tutor principal actiu per a aquest curs acadèmic.';
$string['error_assignment_invalid_type'] = 'Tipus d\'assignació no vàlid.';
$string['error_assignment_already_closed'] = 'Aquesta assignació ja està tancada o cancel·lada.';
$string['error_assignment_no_active_primary'] = 'Aquest alumne no té cap tutor principal actiu per reassignar.';
$string['error_assignment_reassign_same_tutor'] = 'El nou tutor ja és el tutor principal.';
$string['error_assignment_not_active_cotutor'] = 'Aquesta assignació no és una assignació de cotutor activa.';
$string['error_scope_access_denied'] = 'No tens accés a les dades de tutoria d\'aquest alumne.';

$string['eventassignmentcreated'] = 'Assignació creada';
$string['eventassignmentclosed'] = 'Assignació tancada';
$string['eventstudentreassigned'] = 'Alumne reassignat a un nou tutor';
$string['eventcotutoradded'] = 'Cotutor afegit';
$string['eventcotutorremoved'] = 'Cotutor eliminat';

$string['assignmenttype_primary'] = 'Tutor principal';
$string['assignmenttype_co_tutor'] = 'Cotutor';
$string['assignmenttype_support'] = 'Suport';
$string['assignmenttype_orientation'] = 'Orientació';
$string['assignmenttype_other'] = 'Altre';
$string['assignmentstatus_active'] = 'Activa';
$string['assignmentstatus_closed'] = 'Tancada';
$string['assignmentstatus_cancelled'] = 'Cancel·lada';
$string['assignmentstatus_pending'] = 'Pendent';
$string['assignmentsource_manual'] = 'Manual';
$string['assignmentsource_cohort'] = 'Cohort';
$string['assignmentsource_csv'] = 'Importació CSV';
$string['assignmentsource_external'] = 'Externa';
$string['assignmentsource_migration'] = 'Migració';

$string['privacy:metadata:assignment'] = 'Informació sobre les assignacions tutor-alumne.';
$string['privacy:metadata:assignment:studentid'] = 'L\'alumne de l\'assignació.';
$string['privacy:metadata:assignment:tutorid'] = 'El tutor de l\'assignació.';
$string['privacy:metadata:assignment:cohortid'] = 'La cohort d\'origen de l\'assignació, si escau.';
$string['privacy:metadata:assignment:academicyearid'] = 'El curs acadèmic al qual pertany l\'assignació.';
$string['privacy:metadata:assignment:assignmenttype'] = 'El tipus d\'assignació (principal, cotutor, etc.).';
$string['privacy:metadata:assignment:isprimary'] = 'Si aquesta és l\'assignació de tutor principal.';
$string['privacy:metadata:assignment:status'] = 'L\'estat de l\'assignació (activa, tancada, etc.).';
$string['privacy:metadata:assignment:timestart'] = 'Quan va començar l\'assignació.';
$string['privacy:metadata:assignment:timeend'] = 'Quan va finalitzar l\'assignació, si està tancada.';
$string['privacy:metadata:assignment:source'] = 'Com es va crear l\'assignació (manual, cohort, CSV, etc.).';

$string['assignments'] = 'Assignacions';
$string['assignment_detail_title'] = 'Detall de l\'assignació';
$string['assignment_history_title'] = 'Historial d\'assignacions';

$string['filter_academicyear'] = 'Curs acadèmic';
$string['filter_tutor'] = 'Tutor';
$string['filter_student'] = 'Alumne';
$string['filter_cohort'] = 'Cohort';
$string['filter_assignmenttype'] = 'Tipus d\'assignació';
$string['filter_status'] = 'Estat';
$string['filter_source'] = 'Origen';
$string['filter_timestartfrom'] = 'Data d\'inici des de';
$string['filter_timestartto'] = 'Data d\'inici fins a';
$string['filter_timeendfrom'] = 'Data de fi des de';
$string['filter_timeendto'] = 'Data de fi fins a';
$string['filter_apply'] = 'Aplicar filtres';
$string['filter_all'] = 'Tots';

$string['assignment_col_student'] = 'Alumne';
$string['assignment_col_tutor'] = 'Tutor';
$string['assignment_col_cotutors'] = 'Cotutors';
$string['assignment_col_cohort'] = 'Cohort';
$string['assignment_col_academicyear'] = 'Curs acadèmic';
$string['assignment_col_type'] = 'Tipus';
$string['assignment_col_timestart'] = 'Data d\'inici';
$string['assignment_col_timeend'] = 'Data de finalització';
$string['assignment_col_status'] = 'Estat';
$string['assignment_col_source'] = 'Origen';
$string['assignment_col_actions'] = 'Accions';
$string['assignment_viewdetail'] = 'Veure detall';

$string['assignment_createdby'] = 'Creat per';
$string['assignment_modifiedby'] = 'Última modificació per';

$string['assignment_upcoming'] = 'Futura';

$string['assignments_list_empty'] = 'No hi ha assignacions que coincideixin amb els filtres seleccionats.';
$string['assignment_history_empty'] = 'Aquest alumne encara no té historial d\'assignacions.';

$string['eventassignmentviewed'] = 'Assignació vista';
$string['eventassignmentupdated'] = 'Assignació actualitzada';

$string['assignment_create_title'] = 'Nova assignació';
$string['assignment_edit_title'] = 'Editar assignació';
$string['assignment_create'] = 'Nova assignació';
$string['assignment_edit'] = 'Editar';
$string['assignment_create_success'] = 'Assignació creada.';
$string['assignment_update_success'] = 'Assignació actualitzada.';
$string['assignment_field_isprimary'] = 'Marcar com a tutor principal';
$string['assignment_field_note'] = 'Observació administrativa';
$string['assignment_field_editreason'] = 'Motiu de la modificació';
$string['assignment_field_closereason'] = 'Motiu de tancament';
$string['assignment_field_closedate'] = 'Data efectiva de tancament';

$string['assignment_close'] = 'Tancar';
$string['assignment_close_title'] = 'Tancar assignació';
$string['assignment_close_confirm'] = 'Confirmar tancament';
$string['assignment_close_confirm_checkbox'] = 'Confirmo que vull tancar aquesta assignació.';
$string['assignment_close_success'] = 'Assignació tancada.';
$string['assignment_close_success_no_primary'] = 'Assignació tancada. L\'alumne ha quedat sense tutor principal actiu.';
$string['warning_assignment_close_no_primary'] = 'En tancar aquesta assignació, l\'alumne quedarà sense tutor principal actiu.';

$string['closereason_tutorchange'] = 'Canvi de tutor';
$string['closereason_groupchange'] = 'Canvi de grup';
$string['closereason_levelchange'] = 'Canvi de nivell';
$string['closereason_endofyear'] = 'Fi de curs acadèmic';
$string['closereason_studentleft'] = 'Baixa de l\'alumne';
$string['closereason_tutorleft'] = 'Baixa del tutor';
$string['closereason_adminerror'] = 'Error administratiu';
$string['closereason_supportended'] = 'Fi de suport o cotutoria';
$string['closereason_other'] = 'Altre';

$string['error_assignment_closed_no_permission'] = 'No tens permís per editar una assignació tancada o cancel·lada.';
$string['error_assignment_edit_reason_required'] = 'Cal indicar un motiu per modificar una assignació tancada o cancel·lada.';
$string['error_assignment_close_reason_invalid'] = 'Motiu de tancament no vàlid.';
$string['error_assignment_close_before_start'] = 'La data de tancament no pot ser anterior a la data d\'inici.';
$string['error_assignment_close_not_confirmed'] = 'Cal confirmar el tancament.';
$string['error_assignment_close_use_remove_cotutor'] = 'Una assignació de cotutor es retira des de la gestió de cotutors, no des d\'aquesta pàgina.';
$string['error_assignment_reassign_reason_invalid'] = 'Motiu de reassignació no vàlid.';
$string['error_assignment_reassign_conflict'] = 'Aquesta assignació s\'ha modificat mitjançant una altra acció mentre es processava aquesta reassignació. No s\'ha aplicat cap canvi; comprova l\'estat actual i torna-ho a provar.';

$string['reassignreason_groupchange'] = 'Canvi de grup';
$string['reassignreason_levelchange'] = 'Canvi de nivell';
$string['reassignreason_orgchange'] = 'Canvi organitzatiu';
$string['reassignreason_tempsubstitution'] = 'Substitució temporal';
$string['reassignreason_tutorleft'] = 'Baixa del tutor';
$string['reassignreason_reorganization'] = 'Reorganització de tutories';
$string['reassignreason_adminerror'] = 'Error administratiu';
$string['reassignreason_coordinationrequest'] = 'Sol·licitud de coordinació';
$string['reassignreason_other'] = 'Altre';

$string['privacy:metadata:assignment:note'] = 'Una observació administrativa opcional sobre l\'assignació.';
$string['privacy:metadata:assignment:closereason'] = 'El motiu codificat pel qual es va tancar l\'assignació.';
