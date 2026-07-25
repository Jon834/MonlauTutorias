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
$string['error_assignment_isprimary_type_mismatch'] = 'Només una assignació de tipus principal es pot marcar com a tutor principal.';$string['error_assignment_primary_duplicate'] = 'Aquest alumne ja té un tutor principal actiu per a aquest curs acadèmic.';
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
$string['studenttab_tutoring_empty'] = 'L\'historial de tutories encara no està disponible — arribarà en una fase posterior.';
$string['studenttab_agreements_empty'] = 'Els acords encara no estan disponibles — arribaran en una fase posterior.';
$string['student_history_col_reason'] = 'Motiu';
$string['privacy:metadata:assignment:reassignreason'] = 'El motiu codificat registrat quan aquesta assignació es va crear en reassignar el tutor principal de l\'alumne.';

// Fase 5.1 — registre de tutories: domini i dades.
$string['monlaututoria:viewstudentvisiblecontent'] = 'Veure el contingut de tutories compartit amb l\'alumne';
$string['monlaututoria:viewinternalnotes'] = 'Veure les notes internes de tutories';
$string['monlaututoria:viewrestrictednotes'] = 'Veure les notes restringides de tutories';

$string['entrystatus_active'] = 'Activa';
$string['entrystatus_annulled'] = 'Anul·lada';

$string['entryparticipanttype_family'] = 'Família';
$string['entryparticipanttype_orientation'] = 'Orientació';
$string['entryparticipanttype_company'] = 'Empresa';
$string['entryparticipanttype_teacher'] = 'Professorat';
$string['entryparticipanttype_other'] = 'Altre';

$string['error_entry_followup_before_entrydate'] = 'La data de proper seguiment no pot ser anterior a la data de la tutoria.';
$string['error_entry_modality_invalid'] = 'La modalitat seleccionada no existeix o no està activa.';
$string['error_entry_reason_invalid'] = 'Un dels motius seleccionats no existeix o no està actiu.';
$string['error_entry_participant_type_invalid'] = 'Tipus de participant no vàlid.';
$string['error_entry_participant_identity_invalid'] = 'Cada participant ha d\'indicar exactament un usuari intern o un nom extern, mai els dos ni cap.';
$string['error_entry_participant_user_invalid'] = 'L\'usuari participant seleccionat no existeix o ha estat eliminat.';

$string['evententrycreated'] = 'Tutoria registrada';

$string['privacy:metadata:entry'] = 'Informació sobre les tutories registrades: alumne, tutor responsable, curs acadèmic, data, modalitat, contingut compartit, notes internes i restringides.';
$string['privacy:metadata:entry:studentid'] = 'L\'identificador de l\'alumne d\'aquesta tutoria.';
$string['privacy:metadata:entry:tutorid'] = 'L\'identificador del tutor responsable d\'aquesta tutoria.';
$string['privacy:metadata:entry:academicyearid'] = 'El curs acadèmic d\'aquesta tutoria.';
$string['privacy:metadata:entry:entrydate'] = 'La data real en què va tenir lloc la tutoria.';
$string['privacy:metadata:entry:modalityid'] = 'La modalitat de contacte d\'aquesta tutoria.';
$string['privacy:metadata:entry:contentvisible'] = 'El contingut de la tutoria compartit amb l\'alumne.';
$string['privacy:metadata:entry:noteinternal'] = 'La nota interna d\'aquesta tutoria, no visible per a l\'alumne.';
$string['privacy:metadata:entry:noterestricted'] = 'La nota restringida d\'aquesta tutoria, el nivell més sensible.';
$string['privacy:metadata:entry:status'] = 'L\'estat d\'aquesta tutoria (activa o anul·lada).';
$string['privacy:metadata:entry:nextfollowupdate'] = 'La data de proper seguiment, si se\'n va indicar una.';
$string['privacy:metadata:entryparticipant'] = 'Informació sobre els participants d\'una tutoria, interns (usuaris de Moodle) o externs.';
$string['privacy:metadata:entryparticipant:participanttype'] = 'El tipus de participant (família, orientació, empresa, professorat, altre).';
$string['privacy:metadata:entryparticipant:userid'] = 'L\'identificador del participant, quan és un usuari de Moodle.';
$string['privacy:metadata:entryparticipant:externalname'] = 'El nom del participant, quan no és un usuari de Moodle.';
$string['privacy:metadata:entryversion'] = 'Instantànies del contingut d\'una tutoria, preses abans de cada edició o anul·lació.';
$string['privacy:metadata:entryversion:versionnumber'] = 'El número de seqüència d\'aquesta instantània dins de la seva tutoria.';
$string['privacy:metadata:entryversion:snapshotjson'] = 'Els camps editables de la tutoria tal com estaven just abans d\'aquesta edició.';
$string['privacy:metadata:entryversion:changereason'] = 'El motiu indicat per a aquesta edició o anul·lació, si n\'hi va haver.';
$string['privacy:metadata:entryattachment'] = 'Metadades dels fitxers adjunts a una tutoria: categoria de document i descripció.';
$string['privacy:metadata:entryattachment:category'] = 'La categoria documental de l\'adjunt (informe, consentiment, evidència, altre).';
$string['privacy:metadata:entryattachment:description'] = 'La descripció indicada per a l\'adjunt, si n\'hi va haver.';
$string['privacy:metadata:entryattachmentfiles'] = 'Els mateixos fitxers adjunts a les tutories.';

// Fase 5.2 — registre ràpid de tutories.
$string['monlaututoria:createentry'] = 'Registrar una tutoria';
$string['entry_field_entrydate'] = 'Data de la tutoria';
$string['entry_field_modality'] = 'Modalitat';
$string['entry_field_reason'] = 'Motiu';
$string['entry_field_contentvisible'] = 'Comentari compartit amb l\'alumne';
$string['entry_field_noteinternal'] = 'Nota interna';
$string['entry_field_noteinternal_help'] = 'Només la veuran tutors i coordinació. L\'alumne mai veurà aquesta nota, sigui quina sigui la manera en què consulti el registre.';
$string['entry_field_nextfollowupdate'] = 'Proper seguiment';
$string['entry_register'] = 'Registrar tutoria';
$string['entry_register_title'] = 'Registrar tutoria — {$a}';
$string['entry_register_success'] = 'Tutoria registrada correctament.';

// Fase 5.3 — registre complet de tutories.
$string['entry_full_register'] = 'Registre complet';
$string['entry_full_register_title'] = 'Registre complet de tutoria — {$a}';
$string['entry_field_reasons'] = 'Motius';
$string['entry_field_noterestricted'] = 'Nota restringida';
$string['entry_field_visibilitytier'] = 'Visibilitat';
$string['entry_field_participanttype'] = 'Tipus de participant';
$string['entry_field_participantuser'] = 'Participant intern (usuari)';
$string['entry_field_participantexternalname'] = 'Participant extern (nom)';
$string['entry_participants_header'] = 'Participants';
$string['entry_participant_addmore'] = 'Afegir un altre participant';

// Fase 5.4 — historial i detall de tutories.
$string['entry_history_empty'] = 'No hi ha tutories registrades per a aquest curs acadèmic amb aquests filtres.';
$string['entry_viewdetail'] = 'Veure detall';
$string['entry_detail_title'] = 'Detall de la tutoria';

// Fase 5.5 — edició, versionat i anul·lació.
$string['monlaututoria:editownentry'] = 'Editar les tutories pròpies';
$string['monlaututoria:editanyentry'] = 'Editar qualsevol tutoria';
$string['monlaututoria:annulentry'] = 'Anul·lar una tutoria';
$string['setting_entryeditwindow'] = 'Finestra d\'edició de tutories';
$string['setting_entryeditwindow_desc'] = 'Temps després del registre d\'una tutoria durant el qual es pot editar sense indicar un motiu. Passat aquest termini, qualsevol edició exigeix un motiu del canvi.';
$string['entry_edit_title'] = 'Editar tutoria';
$string['entry_edit_success'] = 'Tutoria actualitzada correctament.';
$string['entry_field_editreason'] = 'Motiu del canvi';
$string['error_entry_edit_reason_required'] = 'Ha passat la finestra d\'edició sense motiu — indica el motiu d\'aquest canvi.';
$string['error_entry_already_annulled'] = 'Aquesta tutoria ja està anul·lada.';
$string['entry_annul_title'] = 'Anul·lar tutoria';
$string['entry_annul_success'] = 'Tutoria anul·lada correctament.';
$string['entry_field_annulreason'] = 'Motiu de l\'anul·lació';
$string['entry_annul_confirm_checkbox'] = 'Confirmo que vull anul·lar aquesta tutoria.';
$string['entry_annul_confirm'] = 'Confirmar anul·lació';
$string['error_entry_annul_reason_required'] = 'Indica el motiu de l\'anul·lació.';
$string['error_entry_annul_not_confirmed'] = 'Has de confirmar que vols anul·lar aquesta tutoria.';
$string['evententryupdated'] = 'Tutoria editada';
$string['evententryannulled'] = 'Tutoria anul·lada';

// Fase 5.6 — adjunts de tutories.
$string['entryattachmentcategory_report'] = 'Informe';
$string['entryattachmentcategory_consent'] = 'Autorització';
$string['entryattachmentcategory_evidence'] = 'Evidència';
$string['entryattachmentcategory_other'] = 'Altre';
$string['entry_attachment_category'] = 'Categoria documental';
$string['entry_attachment_files'] = 'Arxius';
$string['entry_attachment_upload'] = 'Pujar arxius';
$string['entry_attachments_title'] = 'Adjunts de la tutoria';
$string['entry_attachment_upload_success'] = 'S\'han pujat {$a} arxiu(s) correctament.';
$string['entry_attachments_empty'] = 'Aquesta tutoria encara no té cap arxiu adjunt.';
$string['error_entry_attachment_category_invalid'] = 'Categoria documental no vàlida.';

// Fase 6.1/6.3 — acords.
$string['monlaututoria:createagreement'] = 'Crear un acord';
$string['monlaututoria:manageagreements'] = 'Completar, reobrir, ajornar o cancel·lar acords';
$string['priority_low'] = 'Baixa';
$string['priority_medium'] = 'Mitjana';
$string['priority_high'] = 'Alta';
$string['agreementstatus_pending'] = 'Pendent';
$string['agreementstatus_in_progress'] = 'En curs';
$string['agreementstatus_completed'] = 'Completat';
$string['agreementstatus_cancelled'] = 'Cancel·lat';
$string['agreementstatus_overdue'] = 'Vençut';
$string['agreementresponsibletype_student'] = 'Alumne';
$string['agreementresponsibletype_tutor'] = 'Tutor';
$string['agreementresponsibletype_family'] = 'Família';
$string['agreementresponsibletype_teacher'] = 'Professorat';
$string['agreementresponsibletype_coordination'] = 'Coordinació';
$string['agreementresponsibletype_orientation'] = 'Orientació';
$string['agreementresponsibletype_company'] = 'Empresa';
$string['agreementresponsibletype_other'] = 'Altre';
$string['agreement_field_description'] = 'Descripció';
$string['agreement_field_responsibletype'] = 'Tipus de responsable';
$string['agreement_field_responsibleuser'] = 'Responsable (usuari de Moodle)';
$string['agreement_field_responsibleexternalname'] = 'Responsable (nom extern)';
$string['agreement_field_duedate'] = 'Data límit';
$string['agreement_field_visibletostudent'] = 'Visible per a l\'alumne';
$string['agreement_field_status'] = 'Estat';
$string['agreement_create'] = 'Crear acord';
$string['agreement_create_title'] = 'Crear acord — {$a}';
$string['agreement_create_success'] = 'Acord creat correctament.';
$string['agreement_complete'] = 'Marcar completat';
$string['agreement_reopen'] = 'Reobrir';
$string['agreement_cancel'] = 'Cancel·lar acord';
$string['agreement_postpone'] = 'Ajornar';
$string['agreement_postpone_title'] = 'Ajornar acord';
$string['agreement_action_success'] = 'Acord actualitzat correctament.';
$string['agreement_confirm_cancel'] = 'Vols cancel·lar aquest acord? Aquesta acció no es pot desfer des d\'aquí.';
$string['agreements_empty'] = 'Encara no s\'ha creat cap acord.';
$string['agreements_filter_overdue'] = 'Només vençuts';
$string['error_agreement_responsible_type_invalid'] = 'Tipus de responsable no vàlid.';
$string['error_agreement_responsible_identity_invalid'] = 'Selecciona exactament un responsable: un usuari de Moodle o un nom extern.';
$string['error_agreement_responsible_user_invalid'] = 'L\'usuari responsable seleccionat no existeix o ha estat eliminat.';
$string['error_agreement_invalid_transition'] = 'Aquesta acció no es pot aplicar a l\'acord en el seu estat actual.';
$string['error_agreement_cannot_postpone_closed'] = 'Un acord completat o cancel·lat no es pot ajornar.';
$string['eventagreementcreated'] = 'Acord creat';
$string['eventagreementupdated'] = 'Acord actualitzat';

// Fase 6.2/6.3 — seguiments.
$string['monlaututoria:createfollowup'] = 'Crear un seguiment';
$string['monlaututoria:managefollowups'] = 'Completar, reobrir, ajornar o cancel·lar seguiments';
$string['followupstatus_pending'] = 'Pendent';
$string['followupstatus_completed'] = 'Completat';
$string['followupstatus_cancelled'] = 'Cancel·lat';
$string['followupstatus_overdue'] = 'Vençut';
$string['followup_field_duedate'] = 'Data prevista';
$string['followup_field_priority'] = 'Prioritat';
$string['followup_field_status'] = 'Estat';
$string['followup_create'] = 'Crear seguiment';
$string['followup_create_title'] = 'Crear seguiment — {$a}';
$string['followup_create_success'] = 'Seguiment creat correctament.';
$string['followup_complete'] = 'Marcar completat';
$string['followup_reopen'] = 'Reobrir';
$string['followup_cancel'] = 'Cancel·lar seguiment';
$string['followup_action_success'] = 'Seguiment actualitzat correctament.';
$string['followup_confirm_cancel'] = 'Vols cancel·lar aquest seguiment? Aquesta acció no es pot desfer des d\'aquí.';
$string['followups_empty'] = 'Encara no s\'ha creat cap seguiment.';
$string['followups_filter_overdue'] = 'Només vençuts';
$string['studenttab_followups'] = 'Seguiments';
$string['error_followup_priority_invalid'] = 'Prioritat no vàlida.';
$string['error_followup_invalid_transition'] = 'Aquesta acció no es pot aplicar al seguiment en el seu estat actual.';
$string['error_followup_cannot_postpone_closed'] = 'Un seguiment completat o cancel·lat no es pot ajornar.';
$string['eventfollowupcreated'] = 'Seguiment creat';
$string['eventfollowupupdated'] = 'Seguiment actualitzat';
$string['entry_field_followup'] = 'Tanca el seguiment';

// Fase 6.4 — derivacions.
$string['monlaututoria:createreferral'] = 'Crear una derivació';
$string['monlaututoria:managereferrals'] = 'Veure, assignar, resoldre o cancel·lar qualsevol derivació';
$string['referraldestination_coordination'] = 'Coordinació';
$string['referraldestination_orientation'] = 'Orientació';
$string['referraldestination_management'] = 'Direcció';
$string['referralstatus_pending'] = 'Pendent';
$string['referralstatus_in_progress'] = 'En curs';
$string['referralstatus_resolved'] = 'Resolta';
$string['referralstatus_cancelled'] = 'Cancel·lada';
$string['referral_field_destination'] = 'Destinació';
$string['referral_field_reason'] = 'Motiu';
$string['referral_field_status'] = 'Estat';
$string['referral_field_assignedto'] = 'Assignat a';
$string['referral_field_resolution'] = 'Resolució';
$string['referral_field_originentry'] = 'Tutoria d\'origen';
$string['referral_create'] = 'Derivar';
$string['referral_create_title'] = 'Crear derivació — {$a}';
$string['referral_create_success'] = 'Derivació creada correctament.';
$string['referral_assign'] = 'Assignar';
$string['referral_resolve'] = 'Resoldre';
$string['referral_cancel'] = 'Cancel·lar derivació';
$string['referral_confirm_cancel'] = 'Vols cancel·lar aquesta derivació? Aquesta acció no es pot desfer des d\'aquí.';
$string['referral_action_success'] = 'Derivació actualitzada correctament.';
$string['referral_viewdetail'] = 'Veure detall';
$string['referral_detail_title'] = 'Detall de la derivació';
$string['referrals_title'] = 'Derivacions';
$string['referrals_empty'] = 'Encara no s\'ha creat cap derivació.';
$string['error_referral_destination_invalid'] = 'Destinació no vàlida.';
$string['error_referral_priority_invalid'] = 'Prioritat no vàlida.';
$string['error_referral_reason_required'] = 'Indica el motiu de la derivació.';
$string['error_referral_resolution_required'] = 'Indica la resolució.';
$string['error_referral_invalid_transition'] = 'Aquesta acció no es pot aplicar a la derivació en el seu estat actual.';
$string['eventreferralcreated'] = 'Derivació creada';
$string['eventreferralupdated'] = 'Derivació actualitzada';

// Fase 6.5 — Privacy API per a acords/seguiments/derivacions.
$string['privacy:metadata:agreement'] = 'Informació sobre acords: tutoria d\'origen, descripció, responsable, data límit, estat, visibilitat per a l\'alumne.';
$string['privacy:metadata:agreement:studentid'] = 'L\'identificador de l\'alumne al qual concerneix aquest acord.';
$string['privacy:metadata:agreement:description'] = 'La descripció de l\'acord.';
$string['privacy:metadata:agreement:responsibletype'] = 'El tipus de responsable.';
$string['privacy:metadata:agreement:responsibleuserid'] = 'L\'identificador del responsable, quan és un usuari de Moodle.';
$string['privacy:metadata:agreement:responsibleexternalname'] = 'El nom del responsable, quan no és un usuari de Moodle.';
$string['privacy:metadata:agreement:duedate'] = 'La data límit de l\'acord.';
$string['privacy:metadata:agreement:status'] = 'L\'estat de l\'acord.';
$string['privacy:metadata:agreement:visibletostudent'] = 'Si aquest acord és visible per a l\'alumne.';
$string['privacy:metadata:followup'] = 'Informació sobre seguiments: tutoria d\'origen, data prevista, prioritat, estat, tutoria de tancament.';
$string['privacy:metadata:followup:studentid'] = 'L\'identificador de l\'alumne al qual concerneix aquest seguiment.';
$string['privacy:metadata:followup:duedate'] = 'La data prevista del seguiment.';
$string['privacy:metadata:followup:priority'] = 'La prioritat del seguiment.';
$string['privacy:metadata:followup:status'] = 'L\'estat del seguiment.';
$string['privacy:metadata:followup:closingentryid'] = 'L\'identificador de la tutoria que va tancar aquest seguiment, si n\'hi va haver.';
$string['privacy:metadata:referral'] = 'Informació sobre derivacions: tutoria d\'origen, destinació, motiu, prioritat, assignat, estat, resolució.';
$string['privacy:metadata:referral:studentid'] = 'L\'identificador de l\'alumne al qual concerneix aquesta derivació.';
$string['privacy:metadata:referral:destination'] = 'La destinació de la derivació.';
$string['privacy:metadata:referral:reason'] = 'El motiu indicat per a la derivació.';
$string['privacy:metadata:referral:priority'] = 'La prioritat de la derivació.';
$string['privacy:metadata:referral:assignedto'] = 'L\'identificador del membre del personal que gestiona la derivació, si està assignada.';
$string['privacy:metadata:referral:status'] = 'L\'estat de la derivació.';
$string['privacy:metadata:referral:resolution'] = 'La resolució indicada per a la derivació, si es va resoldre.';
$string['evententryattachmentadded'] = 'Adjunt afegit a una tutoria';

$string['dashboard_title'] = 'Tauler del tutor';
$string['dashboard_summary_assigned'] = 'Alumnes assignats';
$string['dashboard_summary_attended'] = 'Amb almenys una tutoria';
$string['dashboard_summary_pendinginitial'] = 'Pendents de primera tutoria';
$string['dashboard_summary_coverage'] = 'Cobertura';
$string['dashboard_students_empty'] = 'No tens alumnes principals assignats en aquest curs academic.';
$string['dashboard_col_lastentry'] = 'Ultima tutoria';
$string['dashboard_col_entrycount'] = 'Tutories del curs';
$string['dashboard_col_missinginitial'] = 'Sense tutoria inicial';
$string['dashboard_col_coverage'] = 'Estat de cobertura';
$string['dashboard_coveragestatus_covered'] = 'Cobert';
$string['dashboard_coveragestatus_pending_initial'] = 'Pendent de primera tutoria';

$string['dashboard_summary_followupsoverdue'] = 'Seguiments ven?uts';
$string['dashboard_summary_agreementspending'] = 'Acords pendents';
$string['dashboard_summary_referrals'] = 'Casos derivats';
$string['dashboard_summary_priority'] = 'Alumnes prioritaris';
$string['dashboard_summary_familycontacts'] = 'Contactes amb families';
$string['dashboard_studentfilter_all'] = 'Tots els alumnes';
$string['dashboard_studentfilter_pendinginitial'] = 'Nom?s sense tutoria inicial';
$string['dashboard_studentfilter_withpending'] = 'Nom?s amb pendents';
$string['dashboard_studentfilter_priority'] = 'Nom?s prioritaris';
$string['dashboard_pendingfilter_all'] = 'Tots els pendents';
$string['dashboard_pendingfilter_open'] = 'Nom?s oberts';
$string['dashboard_pendingfilter_overdue'] = 'Nom?s ven?uts';
$string['dashboard_section_students'] = 'Els meus alumnes';
$string['dashboard_section_followups'] = 'Seguiments';
$string['dashboard_section_agreements'] = 'Acords';
$string['dashboard_section_referrals'] = 'Casos derivats';
$string['dashboard_section_priority'] = 'Alumnes prioritaris';
$string['dashboard_col_pendingbundle'] = 'Pendents';
$string['dashboard_col_priority'] = 'Prioritari';
$string['dashboard_action_viewstudent'] = 'Veure fitxa';
$string['dashboard_action_createentry'] = 'Registrar tutoria';
$string['dashboard_action_createfollowup'] = 'Crear seguiment';
$string['dashboard_followups_empty'] = 'No hi ha seguiments per mostrar amb el filtre actual.';
$string['dashboard_agreements_empty'] = 'No hi ha acords per mostrar amb el filtre actual.';
$string['dashboard_priority_empty'] = 'No hi ha alumnes prioritaris amb el filtre actual.';

$string['monlaututoria:viewcoordinationdashboard'] = 'Veure el tauler de coordinacio';
$string['monlaututoria:exportcoordinationreports'] = 'Exportar informes de coordinacio';
$string['monlaututoria:managecoordinationscopes'] = 'Gestionar ambits de coordinacio';
$string['coordination_title'] = 'Tauler de coordinacio';
$string['coordination_scopes_title'] = 'Ambits de coordinacio';
$string['coordination_dashboard_noscope'] = 'No tens cap ambit de coordinacio assignat.';
$string['coordination_dashboard_empty'] = 'No hi ha dades per als filtres actuals.';
$string['coordination_generatedat'] = 'Generat el: {$a}';
$string['coordination_cohort_all'] = 'Totes les meves cohorts';
$string['coordination_tutor_all'] = 'Tots els tutors';
$string['coordination_export_csv'] = 'Exportar CSV';
$string['coordination_export_xlsx'] = 'Exportar full de calcul';
$string['coordination_export_summary'] = 'Resum';
$string['coordination_export_column_section'] = 'Seccio';
$string['coordination_export_column_label'] = 'Etiqueta';
$string['coordination_export_column_generatedat'] = 'Data de generacio';
$string['coordination_export_column_format'] = 'Format';
$string['coordination_summary_population'] = 'Poblacio analitzada';
$string['coordination_summary_withinitial'] = 'Amb tutoria inicial';
$string['coordination_summary_withoutentry'] = 'Sense cap tutoria';
$string['coordination_summary_overduefollowups'] = 'Seguiments vencuts';
$string['coordination_summary_opencases'] = 'Casos oberts';
$string['coordination_quality_title'] = 'Indicadors de qualitat';
$string['coordination_quality_timetofirst'] = 'Temps fins a la primera tutoria';
$string['coordination_quality_agreements'] = 'Acords completats';
$string['coordination_quality_followups'] = 'Seguiments dins de termini';
$string['coordination_quality_familycontacts'] = 'Contactes amb families';
$string['coordination_quality_continuity'] = 'Continuïtat després de canvi de tutor';
$string['coordination_breakdown_cohorts'] = 'Vista per grup';
$string['coordination_breakdown_tutors'] = 'Vista per tutor';
$string['coordination_breakdown_label'] = 'Ambit';
$string['coordination_breakdown_population'] = 'Alumnes';
$string['coordination_breakdown_withinitial'] = 'Amb inicial';
$string['coordination_breakdown_withoutentry'] = 'Sense tutories';
$string['coordination_breakdown_overduefollowups'] = 'Seg. vencuts';
$string['coordination_breakdown_opencases'] = 'Casos oberts';
$string['coordination_breakdown_unassigned'] = 'Sense tutor vigent';
$string['coordination_scope_user'] = 'Usuari';
$string['coordination_scope_availablecohorts'] = 'Cohorts';
$string['coordination_scope_assignments'] = 'Assignacions vigents d ambit';
$string['coordination_scope_current'] = 'Editar ambit de: {$a}';
$string['coordination_scope_save'] = 'Desar ambit';
$string['coordination_scope_saved'] = 'Ambit actualitzat.';
$string['coordination_scope_empty'] = 'Encara no hi ha ambits de coordinacio assignats.';
$string['eventcoordinationdashboardexported'] = 'Informe de coordinacio exportat';

$string['notification_preferences_title'] = 'Preferencies de notificacions';
$string['notification_preferences_intro'] = 'Configura quins avisos vols rebre i amb quina frequencia resumida.';
$string['notification_preferences_save'] = 'Desar preferencies';
$string['notification_preferences_saved'] = 'Preferencies de notificacions desades.';
$string['notification_pref_assignmentchanges'] = 'Avisar-me quan se m\'assigni o reassigni un alumne';
$string['notification_pref_referralchanges'] = 'Avisar-me sobre derivacions rebudes o retornades';
$string['notification_pref_followupreminders'] = 'Avisar-me sobre seguiments propers o ven?uts';
$string['notification_pref_agreementreminders'] = 'Avisar-me sobre acords propers o ven?uts';
$string['notification_pref_digestfrequency'] = 'Frequencia del resum';
$string['notification_frequency_none'] = 'Sense resum';
$string['notification_frequency_daily'] = 'Diari';
$string['notification_frequency_weekly'] = 'Setmanal';
$string['notification_subject_assignment_assigned'] = 'Nova assignacio tutorial';
$string['notification_body_assignment_assigned'] = 'Tens una nova actualitzacio tutorial a Moodle. Entra per revisar l\'alumne assignat.';
$string['notification_subject_assignment_reassigned'] = 'Reassignacio tutorial';
$string['notification_body_assignment_reassigned'] = 'S\'ha actualitzat una tutoria assignada a Moodle. Entra per revisar el canvi.';
$string['notification_subject_referral_assigned'] = 'Nova derivacio assignada';
$string['notification_body_referral_assigned'] = 'Tens una derivacio pendent a Moodle. Entra per revisar-la.';
$string['notification_subject_referral_returned'] = 'Actualitzacio de derivacio';
$string['notification_body_referral_returned'] = 'Una derivacio creada per tu ha canviat a Moodle. Entra per revisar l\'actualitzacio.';
$string['notification_subject_followup_due'] = 'Seguiment proper';
$string['notification_body_followup_due'] = 'Tens un seguiment proper a Moodle. Entra per revisar-lo.';
$string['notification_subject_followup_overdue'] = 'Seguiment ven?ut';
$string['notification_body_followup_overdue'] = 'Tens un seguiment ven?ut a Moodle. Entra per revisar-lo.';
$string['notification_subject_agreement_due'] = 'Acord proper';
$string['notification_body_agreement_due'] = 'Tens un acord proper a Moodle. Entra per revisar-lo.';
$string['notification_subject_agreement_overdue'] = 'Acord ven?ut';
$string['notification_body_agreement_overdue'] = 'Tens un acord ven?ut a Moodle. Entra per revisar-lo.';
$string['notification_subject_daily_digest'] = 'Resum diari de tutories';
$string['notification_body_daily_digest'] = 'Resum diari: {$a->assignedcount} alumnes, {$a->upcomingfollowupcount} seguiments propers, {$a->overduefollowupcount} seguiments ven?uts, {$a->pendingagreementcount} acords pendents, {$a->overdueagreementcount} acords ven?uts i {$a->openreferralcount} derivacions obertes.';
$string['notification_subject_weekly_digest'] = 'Resum setmanal de tutories';
$string['notification_body_weekly_digest'] = 'Resum setmanal: {$a->assignedcount} alumnes, {$a->upcomingfollowupcount} seguiments propers, {$a->overduefollowupcount} seguiments ven?uts, {$a->pendingagreementcount} acords pendents, {$a->overdueagreementcount} acords ven?uts i {$a->openreferralcount} derivacions obertes.';
$string['task_dispatch_notification'] = 'Enviar una notificacio de tutoria';
$string['task_send_notification_reminders'] = 'Generar recordatoris de tutories';
$string['task_retry_failed_notifications'] = 'Reintentar notificacions fallides';
$string['task_cleanup_notification_logs'] = 'Netejar els logs de notificacions';
$string['privacy:metadata:notification'] = 'Metadades operatives de notificacions del connector.';
$string['privacy:metadata:notification:notificationtype'] = 'Tipus de notificacio.';
$string['privacy:metadata:notification:recipientid'] = 'Usuari destinatari de la notificacio.';
$string['privacy:metadata:notification:actorid'] = 'Usuari que ha originat la notificacio quan escau.';
$string['privacy:metadata:notification:entitytype'] = 'Tipus d\'entitat relacionada.';
$string['privacy:metadata:notification:entityid'] = 'Identificador de l\'entitat relacionada.';
$string['privacy:metadata:notification:digestkey'] = 'Clau de deduplicacio diaria o setmanal.';
$string['privacy:metadata:notification:status'] = 'Estat de lliurament.';
$string['privacy:metadata:notification:attempts'] = 'Nombre d\'intents de lliurament.';
$string['privacy:metadata:notification:lasterror'] = 'Ultim error de lliurament registrat.';
$string['privacy:metadata:notification:timesent'] = 'Moment d\'enviament.';

$string['nav_dashboard'] = 'Tauler';
$string['nav_dashboard_tip'] = 'Resum ràpid d alumnes, pendents i prioritats del tutor.';
$string['nav_assignments'] = 'Assignacions';
$string['nav_assignments_tip'] = 'Consulta el repartiment d alumnes i obre el detall de cada assignació.';
$string['nav_referrals'] = 'Derivacions';
$string['nav_referrals_tip'] = 'Gestiona les derivacions obertes i revisa\'n l estat.';
$string['nav_coordination'] = 'Coordinació';
$string['nav_coordination_tip'] = 'Visió global per curs, cohort i tutor.';
$string['nav_notifications'] = 'Notificacions';
$string['nav_notifications_tip'] = 'Ajusta recordatoris, canvis i resums del connector.';
$string['nav_configuration'] = 'Configuració';
$string['nav_configuration_tip'] = 'Accedeix a cursos acadèmics, motius i modalitats.';
$string['nav_help'] = 'Ajuda';
$string['nav_help_tip'] = 'Aprèn què és una tutoria, un acord, un seguiment i una derivació.';

$string['help_page_title'] = 'Guia ràpida de Monlau Tutoria';
$string['help_page_intro'] = 'Aquests són els 4 conceptes que faràs servir més sovint en el dia a dia. Cadascun neix normalment d\'una tutoria, i cadascun té el seu propi cicle de vida.';
$string['help_concept_entry_title'] = 'Tutoria';
$string['help_concept_entry_short'] = 'És el registre d\'una actuació tutorial concreta: quan va passar, amb quina modalitat de contacte, per quin motiu, i el seu contingut en 3 nivells — el compartit amb l\'alumne, la nota interna (només tutors i coordinació) i la nota restringida (encara més limitada).';
$string['help_concept_entry_full'] = 'És el punt de partida de tota la resta: un acord, un seguiment o una derivació sempre neixen d\'una tutoria concreta. Pots registrar una tutoria ràpida (el mínim, en menys d\'un minut) o completa (amb diversos motius, participants interns o externs, i la nota restringida si tens permís). Una tutoria es pot editar poc després de crear-la sense donar motiu; passat un temps configurable, editar-la exigeix explicar per què. Mai s\'esborra: si cal deixar-la sense efecte, s\'anul·la, i queda constància que va existir.';
$string['help_concept_agreement_title'] = 'Acord';
$string['help_concept_agreement_short'] = 'És un compromís concret que surt d\'una tutoria: qui n\'és responsable de complir-lo (el mateix alumne, la família, el tutor, orientació...) i per a quan.';
$string['help_concept_agreement_full'] = 'Un acord es pot marcar com a visible per a l\'alumne o mantenir-lo intern. Al llarg de la seva vida es pot marcar com a completat, reobrir si cal represendre\'l, ajornar la seva data límit, o cancel·lar-lo si ja no aplica.';
$string['help_concept_followup_title'] = 'Seguiment';
$string['help_concept_followup_short'] = 'Marca una data futura per revisar com evoluciona alguna cosa tractada en una tutoria, amb una prioritat (baixa, mitjana o alta).';
$string['help_concept_followup_full'] = 'Es pot tancar de dues maneres: manualment (sense més), o registrant una nova tutoria vinculada que documenta com es va resoldre — aquesta segona manera és la més habitual, ja que deixa constància del que realment va passar. Un seguiment mai el veu l\'alumne.';
$string['help_concept_referral_title'] = 'Derivació';
$string['help_concept_referral_short'] = 'Trasllada un cas a coordinació, orientació o direcció perquè el gestioni una altra persona, amb un motiu redactat de nou per a l\'ocasió — mai una còpia de les notes internes de la tutoria.';
$string['help_concept_referral_full'] = 'Qui rep una derivació pot no tenir cap relació de tutoria prèvia amb l\'alumne — per això la pot veure i gestionar encara que no aparegui com el seu tutor. Es pot assignar a una persona concreta i, quan es resol, es registra la resolució. Com la resta, tampoc la veu l\'alumne.';
$string['help_visibility_title'] = 'Què veu l\'alumne?';
$string['help_visibility_body'] = 'L\'alumne només veu el contingut de tutoria marcat com a "compartit amb l\'alumne", i els acords que s\'hagin marcat explícitament com a visibles per a ell. Mai veu les notes internes o restringides d\'una tutoria, ni els seguiments, ni les derivacions — això s\'aplica sempre al servidor, mai s\'amaga només amb estils.';
$string['help_dashboard_title'] = 'Com llegeixo aquest tauler?';
$string['help_dashboard_body'] = 'Les targetes de dalt resumeixen la teva situació d\'un cop d\'ull: alumnes assignats, cobertura de tutories, i quant tens pendent (seguiments vençuts, acords pendents, derivacions obertes, alumnes prioritaris). A sota tens el detall de cada cosa, amb accions ràpides per no haver d\'obrir la fitxa de cada alumne una per una.';
$string['help_studentview_title'] = 'Què hi ha a cada pestanya de la fitxa?';
$string['help_studentview_body'] = 'Resum: tutor, cohort i curs acadèmic. Historial: l\'històric d\'assignacions de tutor d\'aquest alumne. Tutories: el registre complet d\'actuacions tutorials. Acords i Seguiments: els compromisos i dates de revisió que han sortit d\'aquestes tutories.';
$string['nav_student'] = 'Fitxa de l alumne';
$string['nav_student_tip'] = 'Torna a la fitxa longitudinal de l alumne actual.';

$string['dashboard_intro'] = 'Revisa d un cop d ull els alumnes assignats, els pendents i les alertes que requereixen acció tutorial.';
$string['assignments_intro'] = 'Filtra assignacions, entra a cada detall i localitza ràpidament la fitxa de l alumne.';
$string['assignment_detail_intro'] = 'Aquí pots revisar el context complet de l assignació i saltar a la fitxa de l alumne.';
$string['student_detail_intro'] = 'Fes servir les pestanyes per moure t entre resum, historial, tutories, acords i seguiments.';
$string['entry_detail_intro'] = 'Aquesta pantalla centralitza el contingut de la tutoria i les seves accions relacionades.';
$string['referrals_intro'] = 'Consulta la cua de derivacions i aplica filtres per estat per prioritzar la gestió.';
$string['referral_detail_intro'] = 'Revisa la derivació, el responsable i les accions disponibles sense sortir del context.';
$string['notifications_intro'] = 'Decideix quins avisos vols rebre i amb quina freqüència.';
$string['academicyears_intro'] = 'Gestiona els cursos acadèmics disponibles i controla quin és actiu o blocat.';
$string['reasons_intro'] = 'Mantingues el catàleg de motius de tutoria i la seva visibilitat per defecte.';
$string['modalities_intro'] = 'Defineix els canals de contacte disponibles per registrar tutories.';

$string['page_back_dashboard'] = 'Tornar al tauler';
$string['page_back_assignments'] = 'Tornar a assignacions';
$string['page_back_referrals'] = 'Tornar a derivacions';
$string['page_back_student_entries'] = 'Tornar a tutories de l alumne';
$string['page_back_configuration'] = 'Tornar a configuració';

$string['settings_entryeditwindow_title'] = 'Finestra d edició de tutories';

$string['assignment_reassign'] = 'Reassignar tutor';
$string['assignment_reassign_title'] = 'Reassignar tutor';
$string['assignment_reassign_confirm'] = 'Confirmar reassignació';
$string['assignment_reassign_confirm_checkbox'] = 'Confirmo que vull reassignar aquesta tutoria a un altre tutor.';
$string['assignment_reassign_success'] = 'Tutor reassignat correctament.';
$string['assignment_reassign_intro'] = 'Tanca l assignació principal actual i en crea una de nova amb el tutor seleccionat, conservant l historial.';
$string['assignment_field_newtutor'] = 'Nou tutor';
$string['assignment_field_reassignreason'] = 'Motiu de la reassignació';
$string['assignment_field_reassigndate'] = 'Data efectiva de la reassignació';
$string['assignment_field_keepcotutors'] = 'Mantenir actius els cotutors de l alumne';
$string['warning_assignment_reassign'] = 'Aquesta acció tancarà l assignació principal actual i en crearà una de nova amb el tutor seleccionat.';
$string['warning_assignment_reassign_cotutors'] = 'L alumne té {} cotutor(s) actiu(s). Pots mantenir-los o tancar-los dins de la mateixa reassignació.';
$string['error_assignment_reassign_only_primary'] = 'Només es pot reassignar una assignació principal activa.';
$string['error_assignment_reassign_not_confirmed'] = 'Cal confirmar la reassignació.';

$string['coordination_scope_manage'] = 'Gestionar coordinadors';
$string['coordination_scope_manage_help'] = 'Triar quins coordinadors poden supervisar cada cohort.';
$string['coordination_dashboard_intro'] = 'Vista agregada per a coordinació. L abast es defineix per les cohorts assignades al coordinador i, dins d aquest abast, es pot filtrar per tutor.';
$string['coordination_filter_help'] = 'Primer acota per cohort i després, si cal, filtra per tutor dins d aquestes cohorts.';
$string['error_coordination_scope_invalid_user'] = 'Només pots assignar àmbits a usuaris amb accés al tauler de coordinació.';
$string['coordination_scope_intro'] = 'Selecciona un coordinador i assigna-li les cohorts que podrà supervisar. Dins del panell després podrà filtrar per tutor.';
