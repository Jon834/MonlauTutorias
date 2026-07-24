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
$string['eventacademicyeardeleted'] = 'Curs acadèmic eliminat';
$string['eventreasondeleted'] = 'Motiu de tutoria eliminat';
$string['eventmodalitydeleted'] = 'Modalitat de contacte eliminada';

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
$string['monlaututoria:viewownfile'] = 'Veure la meva pròpia fitxa longitudinal';

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
$string['error_invalidacademicyearid'] = 'El curs acadèmic sol·licitat no existeix.';
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

$string['eventcohortassignmentpreviewed'] = 'Previsualització d\'assignació des de cohort generada';

$string['error_cohort_mode_invalid'] = 'Mode de sincronització no vàlid.';
$string['error_cohort_same_tutor_cotutor'] = 'El tutor principal i el cotutor no poden ser la mateixa persona.';

$string['privacy:metadata:bulkoperation'] = 'Operacions massives d\'assignació des de cohorts';
$string['privacy:metadata:bulkoperation:cohortid'] = 'La cohort utilitzada com a font de població d\'alumnes.';
$string['privacy:metadata:bulkoperation:academicyearid'] = 'El curs acadèmic al qual s\'aplica l\'operació.';
$string['privacy:metadata:bulkoperation:primarytutorid'] = 'El tutor seleccionat com a tutor principal per a l\'operació.';
$string['privacy:metadata:bulkoperation:cotutorid'] = 'El tutor seleccionat com a cotutor per a l\'operació, si n\'hi ha.';
$string['privacy:metadata:bulkoperation:mode'] = 'El mode de sincronització utilitzat en l\'operació.';

$string['eventcsvimportpreviewed'] = 'Previsualització d\'importació CSV generada';

$string['csv_import_title'] = 'Importar assignacions des de CSV';
$string['csv_import_intro'] = 'Puja un fitxer CSV per previsualitzar quines assignacions tutor-alumne crearia. Encara no s\'aplica res — això només mostra una previsualització.';
$string['csv_field_file'] = 'Fitxer CSV';
$string['csv_field_delimiter'] = 'Delimitador';
$string['csv_delimiter_comma'] = 'Coma (,)';
$string['csv_delimiter_semicolon'] = 'Punt i coma (;)';
$string['csv_delimiter_tab'] = 'Tabulador';
$string['csv_field_encoding'] = 'Codificació del fitxer';
$string['csv_upload_preview'] = 'Previsualitzar';
$string['csv_preview_summary_title'] = 'Resum de la previsualització';
$string['csv_summary_total'] = 'Files analitzades: {$a}';
$string['csv_summary_valid'] = 'Vàlides: {$a}';
$string['csv_summary_warning'] = 'Amb advertiment: {$a}';
$string['csv_summary_conflict'] = 'Conflictes: {$a}';
$string['csv_summary_error'] = 'Errors: {$a}';
$string['csv_summary_excluded'] = 'Excloses: {$a}';
$string['csv_col_row'] = 'Fila';
$string['csv_col_status'] = 'Estat';
$string['csv_col_messages'] = 'Missatges';
$string['csv_preview_empty'] = 'El fitxer no té files de dades per previsualitzar.';
$string['csv_exclude_title'] = 'Excloure files';
$string['csv_exclude_intro'] = 'Marca les files que vulguis excloure i recalcula la previsualització.';
$string['csv_row_label'] = 'Excloure fila {$a}';
$string['csv_recalculate_preview'] = 'Recalcular previsualització';
$string['csv_apply_not_available_yet'] = 'Encara no està disponible aplicar aquesta importació — aquesta fase només previsualitza el fitxer.';

$string['csv_status_valid'] = 'Vàlida';
$string['csv_status_warning'] = 'Advertiment';
$string['csv_status_conflict'] = 'Conflicte';
$string['csv_status_error'] = 'Error';
$string['csv_status_excluded'] = 'Exclosa';

$string['csv_message_empty_file'] = 'El fitxer és buit.';
$string['csv_message_missing_required_header'] = 'Falta una capçalera obligatòria.';
$string['csv_message_unknown_column'] = 'El fitxer conté una columna no reconeguda.';
$string['csv_message_column_count_mismatch'] = 'Aquesta fila no té el nombre de columnes esperat.';
$string['csv_message_missing_student'] = 'La columna d\'alumne és buida.';
$string['csv_message_missing_tutor'] = 'La columna de tutor és buida.';
$string['csv_message_missing_academicyear'] = 'La columna de curs acadèmic és buida.';
$string['csv_message_invalid_isprimary'] = 'La columna "tutor principal" ha de ser 0 o 1.';
$string['csv_message_invalid_timestart'] = 'La data d\'inici no és una data vàlida (AAAA-MM-DD).';
$string['csv_message_invalid_timeend'] = 'La data de fi no és una data vàlida (AAAA-MM-DD).';
$string['csv_message_invalid_assignmenttype'] = 'El tipus d\'assignació no es reconeix.';
$string['csv_message_invalid_source'] = 'L\'origen no es reconeix.';
$string['csv_message_duplicate_row'] = 'Aquesta fila repeteix una altra d\'anterior del mateix fitxer.';
$string['csv_message_student_not_found'] = 'No s\'ha trobat cap compte d\'alumne coincident (per correu, usuari o número d\'identificació).';
$string['csv_message_student_suspended'] = 'El compte de l\'alumne està suspès.';
$string['csv_message_student_self_tutor'] = 'L\'alumne i el tutor no poden ser la mateixa persona.';
$string['csv_message_tutor_not_found'] = 'No s\'ha trobat cap compte de tutor coincident (per correu, usuari o número d\'identificació).';
$string['csv_message_tutor_suspended'] = 'El compte del tutor està suspès.';
$string['csv_message_academicyear_not_found'] = 'Cap curs acadèmic coincideix amb aquest identificador curt.';
$string['csv_message_academicyear_locked'] = 'Aquest curs acadèmic està bloquejat per a noves assignacions.';
$string['csv_message_cohort_not_found'] = 'Cap cohort coincideix amb aquest identificador; l\'assignació es crearia sense cohort.';
$string['csv_message_duplicate_active'] = 'Ja existeix una assignació activa idèntica.';
$string['csv_message_primary_conflict'] = 'Aquest alumne ja té un tutor principal actiu.';
$string['csv_message_row_excluded'] = 'Exclosa manualment.';

$string['error_csv_file_not_usable'] = 'No s\'ha pogut llegir el fitxer, o no té files utilitzables. Comprova les capçaleres i torna-ho a provar.';
$string['error_csv_invalid_parameters'] = 'Paràmetres d\'importació no vàlids o incomplets.';

$string['eventcsvimportqueued'] = 'Importació CSV encuada per a processament en segon pla';
$string['eventcsvimportstarted'] = 'Importació CSV iniciada';
$string['eventcsvimportcompleted'] = 'Importació CSV completada';
$string['eventcsvimportcompletedwitherrors'] = 'Importació CSV completada amb errors';
$string['eventcsvimportfailed'] = 'Importació CSV fallida';

$string['csv_field_strategy'] = 'Estratègia d\'aplicació';
$string['csv_strategy_partial_valid'] = 'Aplicar les files vàlides i registrar els errors per fila (recomanat)';
$string['csv_strategy_atomic_all'] = 'Tot o res: una fila fallida cancel·la tot el lot';
$string['csv_field_allow_reassign'] = 'Reassignar tutors principals en conflicte';
$string['csv_field_allow_reassign_help'] = 'Quan una fila entra en conflicte amb un tutor principal actiu diferent ja existent, aquesta opció reassigna l\'alumne amb el tutor del fitxer en lloc d\'ometre la fila. Les files duplicades (la mateixa assignació ja existeix exactament igual) mai es veuen afectades per aquesta opció.';
$string['csv_apply_confirm_checkbox'] = 'Confirmo que vull aplicar aquesta importació.';
$string['csv_apply_button'] = 'Aplicar importació';
$string['csv_apply_title'] = 'Aplicar aquesta importació';
$string['csv_apply_intro'] = 'Això crea o reassigna assignacions reals a partir de la previsualització anterior. No es pot desfer des d\'aquesta pàgina.';
$string['csv_apply_result_title'] = 'Resultat de la importació';
$string['csv_apply_created'] = 'Creades: {$a}';
$string['csv_apply_reassigned'] = 'Reassignades: {$a}';
$string['csv_apply_nochange'] = 'Ja estaven al dia: {$a}';
$string['csv_apply_skipped'] = 'Omeses: {$a}';
$string['csv_apply_failed'] = 'Fallides: {$a}';
$string['csv_apply_status_completed'] = 'La importació s\'ha completat correctament.';
$string['csv_apply_status_completed_with_errors'] = 'La importació s\'ha completat, però algunes files han fallat. Consulta els recomptes anteriors.';
$string['csv_apply_status_failed'] = 'La importació ha fallat i s\'ha revertit — no s\'ha aplicat cap canvi.';

$string['error_csv_apply_strategy_invalid'] = 'Estratègia d\'aplicació no vàlida.';
$string['error_csv_already_applied'] = 'Aquesta importació ja s\'ha aplicat.';
$string['error_csv_preview_changed'] = 'El fitxer o les dades subjacents han canviat des que es va generar la previsualització. Genera una nova previsualització i torna-ho a provar.';
$string['error_csv_apply_row_failed'] = 'Aquesta fila no s\'ha pogut aplicar.';
$string['error_csv_apply_not_confirmed'] = 'Cal confirmar abans d\'aplicar la importació.';

$string['csv_col_outcome'] = 'Resultat';
$string['csv_apply_result_empty'] = 'Aquesta importació no ha generat cap fila processada.';
$string['csv_apply_outcome_created'] = 'Creada';
$string['csv_apply_outcome_reassigned'] = 'Reassignada';
$string['csv_apply_outcome_no_change'] = 'Sense canvis';
$string['csv_apply_outcome_skipped_conflict'] = 'Omesa (conflicte)';
$string['csv_apply_outcome_skipped_error'] = 'Omesa (error)';
$string['csv_apply_outcome_skipped_excluded'] = 'Omesa (exclosa)';
$string['csv_apply_outcome_failed'] = 'Fallida';

$string['csv_apply_deferred'] = 'Aquest fitxer té moltes files i s\'està aplicant en segon pla mitjançant una tasca programada. Encara no s\'aplica res en aquesta pàgina; consulta més tard el resultat al registre d\'esdeveniments.';
$string['csv_report_download'] = 'Descarregar informe de files no aplicades (CSV)';
$string['error_csv_report_not_available'] = 'L\'informe ja no està disponible. Només es pot descarregar una vegada, immediatament després d\'aplicar la importació.';

$string['eventcsverrorreportdownloaded'] = 'Informe d\'errors d\'importació CSV descarregat';
$string['task_process_csv_import'] = 'Aplicar una importació CSV gran en segon pla';
$string['task_cleanup_bulk_operations'] = 'Netejar operacions massives i fitxers temporals abandonats';

$string['privacy:metadata:csvimportfiles'] = 'El fitxer CSV d\'una importació gran, copiat temporalment perquè la tasca en segon pla el pugui llegir; s\'elimina tan bon punt es processa o, com a màxim, en la següent neteja programada.';

$string['student_summary_title'] = 'Fitxa de l\'alumne';
$string['student_viewficha'] = 'Veure fitxa';
$string['student_field_primarytutor'] = 'Tutor principal';
$string['student_field_cotutors'] = 'Cotutors';
$string['student_field_lastassignment'] = 'Última assignació';
$string['student_field_upcoming'] = 'Propers canvis';
$string['student_summary_no_primary'] = 'Sense tutor principal actiu per a aquest curs acadèmic.';
$string['student_summary_no_cotutors'] = 'Sense cotutors actius.';
$string['student_summary_no_assignments'] = 'Sense assignacions en aquest curs acadèmic.';
$string['student_summary_no_upcoming'] = 'No hi ha cap canvi programat.';
$string['studenttab_summary'] = 'Resum';
$string['studenttab_history'] = 'Historial';
$string['studenttab_tutoring'] = 'Tutories';
$string['studenttab_agreements'] = 'Acords';
$string['studenttab_tutoring_empty'] = 'El registre de tutories encara no està disponible — arribarà en una fase posterior.';
$string['studenttab_agreements_empty'] = 'Els acords encara no estan disponibles — arribaran en una fase posterior.';
$string['student_history_col_reason'] = 'Motiu';
$string['privacy:metadata:assignment:reassignreason'] = 'El motiu codificat registrat quan aquesta assignació es va crear en reassignar el tutor principal de l\'alumne.';
