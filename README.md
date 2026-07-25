# local_monlaututoria

**Version:** 0.10.0 ? **Moodle:** 5.1.x (instalacion verificada) ? **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignaciÃ³n de alumnos a tutores, registro de tutorÃ­as, historial entre cursos acadÃ©micos, y separaciÃ³n entre contenido visible para el alumno y notas internas.

Toda la lÃ³gica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resÃºmenes y accesos rÃ¡pidos.

## Estado del proyecto

**Fase 10 - Revision integral.** Fase 10 completa a nivel de codigo y documentacion. Se endurece la exportacion de coordinacion con `sesskey`, se declara explicitamente `local_tut_notification` en Privacy API y se anaden los entregables transversales de produccion (`docs/informe-seguridad.md`, `docs/informe-privacidad.md`, `docs/matriz-compatibilidad.md`, `docs/riesgos-residuales.md`, `docs/backup-rollback-operativo.md`). Proximo bloque de fase: Fase 11 - Piloto.

> **La migraciÃ³n de esquema de 3D.2 fallÃ³ una primera vez en un Moodle 5.1 real sobre PostgreSQL** (`ddl_dependency_exception` por Ã­ndices dependientes); corregida en `db/upgrade.php` y **confirmada por el usuario: la actualizaciÃ³n completa se instala sin errores**. A partir de la Fase 3D.1, el seguimiento detallado usa `docs/roadmap.md`/`docs/project-status.md` (decisiÃ³n explÃ­cita: se sigue ese roadmap, que no incluye todavÃ­a las interfaces pendientes de cotutores, reasignaciÃ³n, alumnos sin tutor ni cohortes).

Esta version (0.10.0) anade:

| Ãrea | Contenido |
|---|---|
| Privacy API | `local_tut_agreement`/`local_tut_followup`/`local_tut_referral` ampliadas en metadata, contextos, exportaciÃ³n sin enmascarar y anonimizaciÃ³n de identidad (`studentid`, `responsibleuserid`/`assignedto`, `createdby`/`modifiedby`), conservando `description`/`reason`/`resolution` â€” misma polÃ­tica que `local_tut_entry` |
| RevisiÃ³n | Seguridad (IDOR/XSS/CSRF), rendimiento y accesibilidad sobre las 15 pÃ¡ginas nuevas de la Fase 6 â€” sin mÃ¡s hallazgos que el IDOR de `followupid` ya corregido en 6.2 |
| Pruebas | PHPUnit: 5 casos nuevos en `tests/privacy/provider_test.php` + 1 caso nuevo en `tests/upgrade_test.php` (actualizaciÃ³n 0.6.6 â†’ actual) |
| Cierre | **Cierra la Fase 6 completa (6.1-6.5)** |

VersiÃ³n previa 0.7.2 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_referral` (creada ya en 6.1, sin escritor hasta ahora): destino, motivo siempre de nueva redacciÃ³n, prioridad, asignado, estado, resoluciÃ³n |
| Servicio | `referral_service::get_for_viewer()` â€” el Ãºnico mecanismo de lectura de todo el plugin sin `scope_service`: visibilidad por `managereferrals` o por ser creador/asignado |
| Seguridad | `referral_updated` nunca lleva el texto de `resolution` en los datos del evento, mismo criterio que `note` en `local_tut_assignment` |
| Interfaz | `referrals/index.php`/`view.php`/`create.php`/`assign.php`/`resolve.php`/`action.php` (nuevos), con entrada propia en *AdministraciÃ³n del sitio*, sin pestaÃ±a en la ficha del alumno |
| Pruebas | PHPUnit: 4+6+2 casos nuevos. Behat: `referral_management.feature` (3 escenarios) |

VersiÃ³n previa 0.7.1 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_followup` (creada ya en 6.1, sin escritor hasta ahora): fecha prevista, prioridad, estado, tutorÃ­a de cierre opcional |
| Servicio | `followup_service::close_with_entry()`: cierra un seguimiento mediante una nueva tutorÃ­a vinculada, reutilizando `entries/create.php`/`create_full.php` con un parÃ¡metro `followupid` |
| CorrecciÃ³n propia | El parÃ¡metro `followupid` no comprobaba pertenecer al mismo alumno de la nueva tutorÃ­a â€” IDOR real, corregido antes de cerrar el incremento |
| Interfaz | `followups/create.php`/`action.php`/`postpone.php` (nuevos); nueva pestaÃ±a "Seguimientos" en la ficha del alumno |
| Pruebas | PHPUnit: 4+6+2 casos nuevos. Behat: `followup_management.feature` (2 escenarios) |

VersiÃ³n previa 0.7.0 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_agreement` (nueva), `local_tut_followup`/`local_tut_referral` (creadas de una vez, sin escritor hasta 6.2/6.4) |
| Servicio | `agreement_service`: crear + completar/reabrir/posponer/cancelar en el mismo incremento (decisiÃ³n deliberada, ver el docblock de la clase) |
| Interfaz | `agreements/create.php`/`action.php`/`postpone.php` (nuevos); pestaÃ±a "Acuerdos" de la ficha del alumno con listado real y filtro "vencidos" |
| Pruebas | PHPUnit: 5+9+2 casos nuevos. Behat: `agreement_management.feature` (3 escenarios) |

VersiÃ³n previa 0.6.6 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Privacy API | Hueco real corregido: `local_tut_entryversion`/`local_tut_entryattachment` (5.5/5.6) nunca se habÃ­an declarado en `classes/privacy/provider.php` â€” ampliado en metadata, contextos, exportaciÃ³n (el archivo adjunto se exporta vÃ­a `writer::export_file()`) y anonimizaciÃ³n (solo `createdby`, ninguna de las dos tablas tiene `studentid`/`tutorid` propios; contenido conservado, `description` del adjunto sÃ­ se limpia) |
| RevisiÃ³n | Seguridad (IDOR/XSS/CSRF), rendimiento y accesibilidad sobre las 7 pÃ¡ginas nuevas de la Fase 5 â€” sin hallazgos de cÃ³digo nuevos, ver `docs/seguridad-permisos.md` |
| Pruebas | PHPUnit: 5 casos nuevos en `tests/privacy/provider_test.php` + 1 caso nuevo en `tests/upgrade_test.php` (actualizaciÃ³n 0.5.3 â†’ actual, cubriendo las 5 tablas de la Fase 5) |
| Cierre | **Cierra la Fase 5 completa (5.1-5.7)** |

VersiÃ³n previa 0.6.5 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_entryattachment` (nueva, solo metadatos â€” categorÃ­a/descripciÃ³n; los archivos viven en el almacenamiento de Moodle) |
| Servicio | `entry_attachment_service` (nuevo): sube vÃ­a `file_save_draft_area_files()` (antivirus del sitio ya aplicado automÃ¡ticamente); listar exige Ã¡mbito + `viewinternalnotes`, alumno siempre denegado |
| Seguridad | `local_monlaututoria_pluginfile()` (nuevo `lib.php`): control de acceso a archivos reimplementado a mano, porque el callback no puede reutilizar un servicio que lanza excepciones |
| Interfaz | `entries/attachments.php` (nuevo): 4 categorÃ­as documentales (informe/autorizaciÃ³n/evidencia/otro), una por lote de subida |
| Pruebas | PHPUnit: 3+5+1 casos nuevos. Behat: `entry_attachments.feature` (nuevo, 4 escenarios, incluida una prueba directa contra `pluginfile.php`) |

VersiÃ³n previa 0.6.4 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| ConfiguraciÃ³n | Primer `admin_settingpage` del plugin: "Ventana de ediciÃ³n de tutorÃ­as" (`entryeditwindow`, 3 dÃ­as por defecto) |
| Servicio | `entry_service::update()`/`annul()` (nuevos): fotografÃ­an el estado anterior en `local_tut_entryversion` antes de escribir; `snapshot_current_state()` compartido entre ambos |
| Permisos | 3 capacidades nuevas: `editownentry` (solo `createdby` propio), `editanyentry`, `annulentry` |
| Interfaz | `entries/edit.php`/`entries/annul.php` (nuevos), con enlaces desde `entries/view.php` |
| Pruebas | PHPUnit: 3+2+8+1+1 casos nuevos. Behat: `entry_edit_annul.feature` (nuevo, 4 escenarios) |

VersiÃ³n previa 0.6.3 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Interfaz | `entries/view.php` (nuevo, detalle); pestaÃ±a "TutorÃ­as" de `student/view.php` ahora con listado real, filtros y paginaciÃ³n |
| Servicio | `entry_repository`: filtros por modalidad/motivo/visibilidad; `entry_reason_repository::get_for_entries()` (batch); `entry_service::mask_content()` extraÃ­do y reutilizado por `get_history_for_student()`/`count_history_for_student()` (nuevos) |
| Permisos | Sin capacidades nuevas â€” reutiliza `viewstudent`/`viewownfile` (pÃ¡gina) y las 3 de contenido (servicio) |
| Pruebas | PHPUnit: 3+2+4 casos nuevos. Behat: `entry_history.feature` (nuevo, 3 escenarios) |

VersiÃ³n previa 0.6.2 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Interfaz | `entries/create_full.php` (nuevo) + `entry_full_form.php` (nuevo): motivos mÃºltiples, participantes internos/externos por filas repetibles, nota restringida condicional |
| Permisos | Sin capacidades nuevas â€” reutiliza `createentry` (5.2) y `viewrestrictednotes` (5.1) |
| Acceso | BotÃ³n "Registro completo" junto al de registro rÃ¡pido, en la pestaÃ±a "TutorÃ­as" |
| Pruebas | Behat: `entry_full_registration.feature` (nuevo, 3 escenarios). Sin PHPUnit nuevo |

VersiÃ³n previa 0.6.1 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Interfaz | `entries/create.php` (nuevo) + `entry_quick_form.php` (nuevo): fecha, modalidad, motivo, comentario compartido (obligatorio), nota interna, prÃ³ximo seguimiento |
| Permisos | Nueva capacidad `local/monlaututoria:createentry` + `scope_service::require_user_can_access_student()` |
| Acceso | Enlace "Registrar tutorÃ­a" en la pestaÃ±a "TutorÃ­as" de `student/view.php`; su aviso se corrige (ya no dice que "el registro" no estÃ¡ disponible, solo "el historial") |
| Pruebas | Behat: `entry_quick_registration.feature` (nuevo, 4 escenarios). Sin PHPUnit nuevo â€” sin lÃ³gica de negocio propia, mismo criterio que `assignments/create.php` |

VersiÃ³n previa 0.6.0 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_entry` (con 3 columnas de contenido de nivel fijo: `contentvisible`/`noteinternal`/`noterestricted`), `local_tut_entryreason` (N:M con los motivos de la Fase 2), `local_tut_entryparticipant` (internos y externos), `local_tut_entryversion` (schema-only, sin escritor hasta 5.5) |
| Servicio | `entry_service::get_for_viewer()`: reutiliza `scope_service` sin modificarlo + un segundo filtro propio por capacidad de contenido, con suelo duro para el propio alumno |
| Permisos | 3 capacidades nuevas de lectura: `viewstudentvisiblecontent`, `viewinternalnotes`, `viewrestrictednotes` |
| Privacy API | Misma polÃ­tica que `local_tut_assignment` (decisiÃ³n del usuario): conservaciÃ³n indefinida, anonimizaciÃ³n de identidad, contenido de las notas conservado |
| Pruebas | PHPUnit: 4+2+3 casos en los repositorios nuevos + 18 en `entry_service_test.php` + 1 de evento + 4 en `provider_test.php`. Sin Behat (sin interfaz todavÃ­a) |

VersiÃ³n previa 0.5.3 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Responsive | `table-responsive` aÃ±adido a `academic_years_list()`, `catalogue_list()`, `assignments_list`, `student_history_table()`, `csv_import_preview_table()` y `csv_import_apply_result_table()` |
| Accesibilidad | `student_tabs()`: `aria-current="page"` en la pestaÃ±a activa (enlaces reales, ya accesibles por teclado de forma nativa) |
| Errores claros | `academic_year_repository::find()` (nuevo, devuelve `null` en vez de lanzar); usado en `student/view.php` para un `academicyearid` invÃ¡lido, mismo criterio ya aplicado al `studentid` |
| Rendimiento | N+1 corregido en `renderer::student_summary()`: `core_user::get_user()` no tiene cachÃ© para ids normales, confirmado leyendo el core de Moodle; sustituido por un Ãºnico `$DB->get_records_list()` por lote |
| Pruebas | PHPUnit: 1 caso nuevo en `academic_year_repository_test.php` + 2 en `renderer_test.php`. Behat: 1 escenario nuevo en `student_summary.feature` |

VersiÃ³n previa 0.5.2 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Permisos | Nueva capacidad `local/monlaututoria:viewownfile`, arquetipo "Usuario autenticado" (concedida por defecto a nivel de sistema, a diferencia del arquetipo "Estudiante" que solo se asigna a nivel de curso) |
| Servicio | `scope_service`: nueva rama de acceso propio â€” si `$userid === $studentid` y tiene `viewownfile`, acceso concedido sin depender de ninguna relaciÃ³n de tutorÃ­a |
| Interfaz | `student/view.php` calcula `$islimitedview` para el propio alumno; renderer oculta enlaces a `assignments/view.php` y las columnas Origen/Motivo del historial en esa vista |
| Deliberadamente no implementado | "CoordinaciÃ³n segÃºn Ã¡mbito" â€” no existe en el modelo de datos del proyecto el concepto de coordinador responsable de un subconjunto de alumnos/cohortes, mismo vacÃ­o ya documentado desde 3B.5A/3C.1/3E.1 |
| Pruebas | PHPUnit: 3 casos nuevos en `scope_service_test.php` + 4 en `renderer_test.php`. Behat: 2 escenarios nuevos en `student_summary.feature` |

VersiÃ³n previa 0.5.1 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | Nuevo campo `local_tut_assignment.reassignreason` (nullable) â€” ya anticipado desde la Fase 3B.4A, con su propio paso en `db/upgrade.php` |
| Servicio | `assignment_repository::search_history_for_student()` (nuevo): historial ordenado por curso acadÃ©mico y fecha de inicio, con filtros y paginaciÃ³n |
| Interfaz | Nueva pestaÃ±a "Historial" en `student/view.php` (curso acadÃ©mico, tutor, tipo, estado, fechas, origen, motivo de cierre/reasignaciÃ³n) |
| CorrecciÃ³n propia | `student_history_table()` no escapaba el nombre del tutor (`html_writer::table()` no escapa como Mustache) â€” encontrado y corregido antes de cerrar el incremento |
| Pruebas | PHPUnit: 6 casos nuevos en `assignment_repository_test.php` + 1 en `assignment_service_test.php` + 1 en `renderer_test.php`. Behat: `student_history.feature` (nuevo, 3 escenarios) |

VersiÃ³n previa 0.5.0 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Servicio | `student_summary_service::get_summary(studentid, academicyearid)` (nuevo, sin tabla): tutor principal y cotutores vigentes, cohorte, Ãºltima asignaciÃ³n y prÃ³ximos cambios â€” recalculado en cada peticiÃ³n, nunca persistido |
| Interfaz | Nueva pÃ¡gina `student/view.php`: capacidad `viewstudent` + `scope_service` desde el primer commit, selector de curso acadÃ©mico, foto del alumno |
| Acceso | Enlace "Ver ficha" aÃ±adido al listado de asignaciones y al detalle de una asignaciÃ³n |
| Pruebas | PHPUnit: `student_summary_service_test.php` (nuevo, 5 casos) + 1 caso nuevo en `renderer_test.php`. Behat: `student_summary.feature` (nuevo, 3 escenarios) |

VersiÃ³n previa 0.4.8 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| DecisiÃ³n funcional | PolÃ­tica de retenciÃ³n decidida por el usuario antes de implementar: `local_tut_assignment` se conserva indefinidamente; una solicitud de acceso/borrado se resuelve con exportaciÃ³n completa y **anonimizaciÃ³n, nunca borrado fÃ­sico** de la fila |
| Privacy API | `classes/privacy/provider.php` ampliado: `get_contexts_for_userid()`/`get_users_in_context()`/`export_user_data()` cubren ahora `local_tut_assignment` y `local_tut_bulkoperation`; `delete_data_for_user(s)`/`delete_data_for_all_users_in_context()` anonimizan (reasignan al usuario "sin respuesta" de Moodle y vacÃ­an `note`) en vez de borrar |
| RetenciÃ³n | `cleanup_bulk_operations_task`: nuevo `TERMINAL_TTL_SECONDS` (90 dÃ­as) purga operaciones ya finalizadas, sumado a la purga de abandonadas ya existente desde 3D.4 |
| Pruebas | PHPUnit: `tests/privacy/provider_test.php` (nuevo, 7 casos) + 4 casos nuevos en `cleanup_bulk_operations_task_test.php` |

VersiÃ³n previa 0.4.7 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| CorrecciÃ³n | `academic_year_service::delete()`/`catalogue_service::delete()` no disparaban ningÃºn evento, a diferencia del resto de sus mÃ©todos â€” la acciÃ³n mÃ¡s irreversible de todas, sin auditorÃ­a. Nuevos eventos `academic_year_deleted`/`reason_deleted`/`modality_deleted` (con el `shortname` de la fila borrada). Cambio de firma: `delete()` exige ahora `$userid` en ambos servicios |
| CorrecciÃ³n | `csv_import_dispatch_service::dispatch()` no disparaba ningÃºn evento al diferir una importaciÃ³n grande a tarea ad hoc. Nuevo evento `csv_import_queued` |
| CorrecciÃ³n | `process_csv_import_task::execute()` marcaba `failed` sin evento cuando el archivo persistido faltaba. `csv_import_failed` acepta ahora `failedrownumber` nulo para este caso |
| Documentado, sin cÃ³digo | `catalogue_service::move()` (reordenar) y la limpieza automÃ¡tica de `cleanup_bulk_operations_task` siguen sin evento â€” severidad cosmÃ©tica / limpieza de sistema, no acciones de usuario |
| Pruebas | PHPUnit: 1 caso nuevo (`academic_year_deleted`) + 2 casos nuevos (`reason_deleted`/`modality_deleted`) + 2 pruebas existentes ampliadas (`csv_import_queued`, `csv_import_failed` con fila nula) |

VersiÃ³n previa 0.4.6 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Revisado, ya bien | `unassigned_students_service`/`cohort_assignment_preview_service` ya resuelven todo por lote, sin N+1 â€” sin cambios necesarios |
| CorrecciÃ³n | `assignments/index.php` resolvÃ­a los cursos acadÃ©micos de la pÃ¡gina con un `get()` por id distinto dentro de un bucle (N+1 real, severidad baja). Nuevo `academic_year_repository::get_many()` (una sola consulta) lo sustituye |
| Documentado, sin cÃ³digo | `csv_import_preview_service::resolve_row()` ejecuta varias consultas por fila del CSV â€” sÃ­ escala linealmente con archivos grandes, pero ya mitigado en la experiencia de usuario desde 3D.4 (umbral de tarea ad hoc); no reescrito por el riesgo de tocar la resoluciÃ³n de identificadores sin cobertura de integraciÃ³n real |
| Pruebas | PHPUnit: `tests/performance/assignment_listing_performance_test.php` (nuevo, crea 2.000 asignaciones reales, compara recuento de consultas a 50 y a 2.000 filas) + 2 casos de `get_many()` |

VersiÃ³n previa 0.4.5 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| CorrecciÃ³n | `csv_import_apply_service::apply()`: la transiciÃ³n `previewed â†’ processing` era dos pasos separados con la recomputaciÃ³n completa de la previsualizaciÃ³n en medio â€” una ventana de carrera real (dos clics en "Aplicar" podÃ­an escribir asignaciones duplicadas). Ahora usa `bulk_operation_repository::claim()`, una comparaciÃ³n-y-escritura atÃ³mica |
| CorrecciÃ³n | `assignment_service::close()`/`remove_cotutor()`: no releÃ­an la fila justo antes de escribir (a diferencia de `reassign_primary_tutor()` desde 3B.4A) â€” un cierre doble concurrente podÃ­a sobrescribir el motivo/nota/fecha del primer cierre en vez de rechazarse |
| Nuevo | `bulk_operation_repository::claim(int $id, string $fromstatus, string $tostatus): bool` â€” primitiva de comparar-y-intercambiar reutilizable |
| Documentado, sin cÃ³digo | `assignment_service::create()` tiene una carrera de la misma familia, pero sin soluciÃ³n portable disponible (Ã­ndice Ãºnico condicional o bloqueo de fila, ninguno de los dos expresable/disponible aquÃ­) â€” limitaciÃ³n conocida, no una protecciÃ³n cosmÃ©tica |
| Pruebas | PHPUnit: 2 casos nuevos de `claim()` + 1 de aplicaciÃ³n CSV concurrente + 2 de cierre/cotutor concurrente, con dos nuevos dobles de prueba |

VersiÃ³n previa 0.4.4 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| CorrecciÃ³n | Bug real de 3D.3: `csv_import_apply_service` llamaba a un mÃ©todo (`csv_import_preview_summary::from_array()`) que no existÃ­a â€” invisible a `php -l`, solo detectable ejecutando pruebas o el flujo real |
| Informe | Tabla de resultado por fila tras aplicar, no solo recuentos agregados |
| ExportaciÃ³n | `csv_import_error_export_service`: descarga CSV de filas no aplicadas (conflicto/error/excluida/fallida), con neutralizaciÃ³n de inyecciÃ³n de fÃ³rmulas (`=`, `+`, `-`, `@`) y sin persistir nunca el informe (vive en `$SESSION`, descarga Ãºnica) |
| Tarea ad hoc | `csv_import_dispatch_service` + `process_csv_import_task`: por encima de 50 filas, la importaciÃ³n se difiere a una tarea en segundo plano en vez de aplicarse en la misma peticiÃ³n |
| Limpieza | `cleanup_bulk_operations_task` (diaria): purga operaciones abandonadas y archivos temporales huÃ©rfanos |
| Privacy API | Ãrea de archivos `csvimport` declarada, sin exportaciÃ³n/borrado (misma razÃ³n documentada que `local_tut_assignment`) |
| Eventos | `csv_error_report_downloaded` |
| Pruebas | PHPUnit: 1 prueba de dominio + 5 del servicio de exportaciÃ³n + 3 del servicio de despacho + 3 de la tarea ad hoc + 5 de la tarea de limpieza + 1 prueba integral (parseoâ†’previsualizaciÃ³nâ†’despachoâ†’aplicaciÃ³nâ†’informe) |

VersiÃ³n previa 0.4.3 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Servicio | `csv_import_apply_service::apply()`: crea o reasigna asignaciones reales a partir de una previsualizaciÃ³n, reutilizando `assignment_service::create()`/`reassign_primary_tutor()` |
| ReasignaciÃ³n de conflictos | Solo si se activa explÃ­citamente `allowreassignconflicts` (nunca por defecto); un duplicado exacto nunca se reasigna |
| Idempotencia | RecomprobaciÃ³n de duplicados antes de escribir cada fila; una operaciÃ³n no puede aplicarse dos veces |
| RevalidaciÃ³n | Nunca confÃ­a en la previsualizaciÃ³n guardada â€” recalcula y compara antes de aplicar |
| Estrategias | `partial_valid` (por defecto) y `atomic_all` (todo o nada, con rollback real) |
| Eventos | `csv_import_started`/`completed`/`completed_with_errors`/`failed` |
| Interfaz | Tercer paso en `assignments/import.php`: aplicar con confirmaciÃ³n explÃ­cita y resumen del resultado |
| Pruebas | PHPUnit: 9 casos del servicio de aplicaciÃ³n + 3 de eventos |

VersiÃ³n previa 0.4.2 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Esquema | `local_tut_bulkoperation` (de 3C.1) ampliada con `operationtype=csv_import`; `cohortid`/`academicyearid`/`primarytutorid`/`mode` ahora admiten `null` â€” quinta migraciÃ³n de esquema real del proyecto |
| Servicio | `csv_import_preview_service::preview()`: resuelve cada fila contra la BD (alumno/tutor por correo/usuario/idnumber, curso por shortname, cohorte opcional por id/idnumber) reutilizando validaciones ya pÃºblicas de `assignment_service` |
| Estados | `valid`, `warning`, `conflict`, `error`, `excluded` â€” cohorte no encontrada es advertencia, no error |
| Interfaz | `assignments/import.php`: subida + tabla de previsualizaciÃ³n + exclusiÃ³n manual (siempre recalculada desde cero) |
| Pruebas | PHPUnit: 16 casos nuevos + repositorio ampliado; Behat: primeros escenarios de la Fase 3D |

VersiÃ³n previa 0.4.1 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Servicio | `csv_import_parser_service::parse()`: convierte contenido CSV en filas validadas sintÃ¡cticamente (cabeceras, campos obligatorios, formatos, duplicados internos) â€” sin consultar todavÃ­a la base de datos |
| Formato | `fgetcsv()` sobre stream en memoria (soporta comillas con delimitadores/saltos de lÃ­nea), conversiÃ³n de codificaciÃ³n, retirada de BOM |
| Pruebas | PHPUnit: 17 casos de parseo y validaciÃ³n |

VersiÃ³n previa 0.4.0 aÃ±adÃ­a:

| Ãrea | Contenido |
|---|---|
| Tabla nueva | `local_tut_bulkoperation` (identidad + parÃ¡metros + resumen agregado de una operaciÃ³n) â€” **sin** tabla de elementos por alumno, decisiÃ³n explÃ­cita para no retener previsualizaciones que pueden quedar obsoletas o nunca ejecutarse |
| Servicio | `cohort_assignment_preview_service::preview()`: clasifica cada miembro de una cohorte frente a un tutor principal/cotutor propuestos, con la misma semÃ¡ntica de vigencia del resto del proyecto |
| Acciones | `cohort_assignment_action` (10 cÃ³digos: crear, reasignar, cerrar ausente, sin cambios, conflicto, omitidos...) â€” principal y cotutor de forma independiente por alumno |
| Modos | `cohort_sync_mode`: `preview_only`, `add_only`, `add_and_close_missing`, `replace_primary` â€” solo clasificaciÃ³n en esta fase, sin ejecuciÃ³n |
| Caducidad | Sin tabla de detalle: se recalcula y compara el resumen agregado contra el guardado (`has_changed_since_preview()`), mÃ¡s comprobaciÃ³n por antigÃ¼edad (`is_expired()`) |
| ReutilizaciÃ³n | 4 validaciones de `assignment_service` (tutor, cohorte, curso bloqueado) pasan de `private` a `public` para evitar duplicarlas |
| Pruebas | PHPUnit: los 13 escenarios de previsualizaciÃ³n del prompt (incluidos los 4 modos), mÃ¡s caducidad, cambio detectado y validaciones |

Versiones previas: 0.6.6 (Fase 5.7 â€” cierre de la Fase 5 completa), 0.6.5 (Fase 5.6 â€” adjuntos de tutorÃ­as), 0.6.4 (Fase 5.5 â€” ediciÃ³n, versionado y anulaciÃ³n de tutorÃ­as), 0.6.3 (Fase 5.4 â€” historial y detalle de tutorÃ­as), 0.6.2 (Fase 5.3 â€” registro completo de tutorÃ­as), 0.6.1 (Fase 5.2 â€” registro rÃ¡pido de tutorÃ­as), 0.6.0 (Fase 5.1 â€” registro de tutorÃ­as: dominio y datos), 0.5.3 (Fase 4.4 â€” UX, rendimiento y cierre; cierra la Fase 4 completa), 0.5.2 (Fase 4.3 â€” permisos y vistas), 0.5.1 (Fase 4.2 â€” historial de asignaciones), 0.5.0 (Fase 4.1 â€” ficha del alumno: cabecera y resumen), 0.4.8 (Fase 3E.6 â€” Privacy API completa y retenciÃ³n; cierra la Fase 3E con 3E.7/3E.8), 0.4.7 (Fase 3E.5 â€” revisiÃ³n de eventos y auditorÃ­a), 0.4.6 (Fase 3E.4 â€” rendimiento y revisiÃ³n N+1), 0.4.5 (Fase 3E.3 â€” concurrencia e idempotencia), 0.4.4 (Fase 3D.4 â€” informe y cierre de la importaciÃ³n CSV), 0.4.3 (Fase 3D.3 â€” aplicaciÃ³n real de la importaciÃ³n CSV), 0.3.5 (Fase 3B.5A â€” servicio de detecciÃ³n de alumnos sin tutor), 0.3.4 (Fase 3B.4A â€” servicio de reasignaciÃ³n), 0.3.3 (Fase 3B.3A â€” cierre de asignaciones), 0.3.2 (Fase 3B.2 â€” creaciÃ³n y ediciÃ³n manual), 0.3.1 (Fase 3B.1 â€” listado y detalle, confirmada en Moodle real), 0.3.0 (Fase 3A â€” modelo y servicios de asignaciÃ³n), 0.2.0 (Fase 2 â€” cursos acadÃ©micos y catÃ¡logos).

**TodavÃ­a sin implementar:** interfaz de asignaciÃ³n masiva desde cohortes (formulario, previsualizaciÃ³n en pantalla, confirmaciÃ³n, ejecuciÃ³n, cierre de ausentes, sustituciÃ³n), interfaz del informe de alumnos sin tutor, formulario e interfaz de reasignaciÃ³n, gestiÃ³n de cotutores como funcionalidad propia, vistas diferenciadas por rol en la ficha del alumno (Fase 4.3, la cabecera/resumen e historial de 4.1/4.2 ya existen), registro de tutorÃ­as, acuerdos, seguimientos, dashboards, notificaciones, derivaciones, pantalla de "operaciones" para consultar el estado de una importaciÃ³n CSV diferida a tarea ad hoc.

Ver [`docs/roadmap.md`](../../docs/roadmap.md) y [`docs/project-status.md`](../../docs/project-status.md) en la raÃ­z del repositorio para el roadmap y estado actuales; [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) recoge la narrativa detallada de las fases 1-6.5 (**las Fases 4, 5 y 6 quedan completas**).

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) ya se comprobÃ³ compatible al instalar correctamente en un Moodle 5.1 de pruebas real; sigue pendiente ajustarlo al nÃºmero exacto del core (no bloqueante).
- PHP segÃºn los requisitos de Moodle 5.1.

## InstalaciÃ³n

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *AdministraciÃ³n del sitio â†’ Notificaciones* para completar la instalaciÃ³n, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** verificado en un Moodle 5.1 de pruebas real hasta la Fase 3B.1 inclusive a nivel de interfaz (incluido el selector AJAX de usuario). **La actualizaciÃ³n de esquema completa (3B.2 â†’ 3D.2, incluida la correcciÃ³n del fallo de Ã­ndice en PostgreSQL) se ha instalado sin errores en esa misma instancia** â€” confirmado por el usuario. 3D.3/3D.4/4.1 no aÃ±aden esquema; 4.2 sÃ­ aÃ±adiÃ³ una migraciÃ³n real (`local_tut_assignment.reassignreason`, nullable); 4.3 y 4.4 no aÃ±adieron esquema; 5.1 sÃ­ aÃ±ade una migraciÃ³n real (4 tablas nuevas de tutorÃ­as); 5.2-5.5 no aÃ±aden esquema (interfaces nuevas y, en 5.5, la primera opciÃ³n de configuraciÃ³n real del plugin); 5.6 sÃ­ aÃ±ade una migraciÃ³n real (tabla de metadatos de adjuntos); 5.7 no aÃ±ade esquema; 6.1 sÃ­ aÃ±ade una migraciÃ³n real (3 tablas nuevas de acuerdos/seguimientos/derivaciones, creadas de una vez); **6.2-6.5 no aÃ±aden esquema propio** (las 2 tablas restantes ya existÃ­an desde 6.1). Ninguna de las migraciones desde 4.2 en adelante se ha probado todavÃ­a contra esa instancia. Lo que falta todavÃ­a por probar manualmente en el navegador: la interfaz de las Fases 3B.2/3B.3A/3C.1/3D.2/3D.3/3D.4/4.1/4.2/4.3/4.4/5.2/5.3/5.4/5.5/5.6/6.1/6.2/6.4, y ejecutar PHPUnit/Behat (los servicios de 3B.4A/3B.5A no tienen interfaz que probar).

## Versiones compatibles

| VersiÃ³n del plugin | Moodle |
|---|---|
| 0.7.3 | 5.1.x (sin esquema nuevo sobre 0.7.2; cierre de la Fase 6 completa â€” Privacy API ampliada para acuerdos/seguimientos/derivaciones, PHPUnit pendiente de probar) |
| 0.7.2 | 5.1.x (migraciÃ³n de esquema ya aplicada en 6.1 â€” sin esquema nuevo propio; derivaciones de la Fase 6.4, PHPUnit/Behat pendientes de probar) |
| 0.7.1 | 5.1.x (sin esquema nuevo propio; seguimientos de la Fase 6.2, PHPUnit/Behat pendientes de probar) |
| 0.7.0 | 5.1.x (nueva migraciÃ³n de esquema â€” 3 tablas de acuerdos/seguimientos/derivaciones â€” todavÃ­a sin probar contra la instancia real; acuerdos de la Fase 6.1, PHPUnit/Behat pendientes de probar) |
| 0.6.6 | 5.1.x (sin esquema nuevo sobre 0.6.5; cierre de la Fase 5 completa â€” Privacy API ampliada para `entryversion`/`entryattachment`, PHPUnit pendiente de probar) |
| 0.6.5 | 5.1.x (nueva migraciÃ³n de esquema â€” tabla de metadatos de adjuntos â€” todavÃ­a sin probar contra la instancia real; primer uso de la File API y `pluginfile.php`, PHPUnit/Behat pendientes de probar) |
| 0.6.4 | 5.1.x (sin esquema nuevo sobre 0.6.3; primera opciÃ³n de configuraciÃ³n real del plugin â€” ventana de ediciÃ³n â€” PHPUnit/Behat pendientes de probar) |
| 0.6.3 | 5.1.x (sin esquema nuevo sobre 0.6.2; historial y detalle de la Fase 5 â€” PHPUnit/Behat pendientes de probar) |
| 0.6.2 | 5.1.x (sin esquema nuevo sobre 0.6.1; segunda interfaz de la Fase 5 â€” registro completo â€” Behat pendiente de probar) |
| 0.6.1 | 5.1.x (sin esquema nuevo sobre 0.6.0; primera interfaz de la Fase 5 â€” registro rÃ¡pido â€” Behat pendiente de probar) |
| 0.6.0 | 5.1.x (nueva migraciÃ³n de esquema â€” 4 tablas de tutorÃ­as â€” todavÃ­a sin probar contra la instancia real; dominio y datos de 5.1, sin interfaz, PHPUnit pendiente de probar) |
| 0.5.3 | 5.1.x (sin esquema nuevo sobre 0.5.2; cierre de la Fase 4 â€” responsive, teclado, errores claros, N+1 â€” PHPUnit/Behat pendientes de probar) |
| 0.5.2 | 5.1.x (sin esquema nuevo sobre 0.5.1; nueva capacidad `viewownfile` y vista limitada de 4.3, PHPUnit/Behat pendientes de probar) |
| 0.5.1 | 5.1.x (nueva migraciÃ³n de esquema â€” `reassignreason` â€” todavÃ­a sin probar contra la instancia real; historial de 4.2, PHPUnit/Behat pendientes de probar) |
| 0.5.0 | 5.1.x (sin esquema nuevo sobre 0.4.8, que ya estÃ¡ **verificado** âœ…; ficha del alumno de 4.1, PHPUnit/Behat todavÃ­a pendientes de probar) |
| 0.4.8 | 5.1.x (sin esquema nuevo sobre 0.4.7, que ya estÃ¡ **verificado** âœ…; Privacy API completa y retenciÃ³n de 3E.6, PHPUnit todavÃ­a pendiente de probar) |
| 0.4.7 | 5.1.x (sin esquema nuevo sobre 0.4.6, que ya estÃ¡ **verificado** âœ…; correcciones de eventos/auditorÃ­a de 3E.5, PHPUnit todavÃ­a pendiente de probar) |
| 0.4.6 | 5.1.x (sin esquema nuevo sobre 0.4.5, que ya estÃ¡ **verificado** âœ…; correcciÃ³n N+1 de 3E.4, PHPUnit todavÃ­a pendiente de probar) |
| 0.4.5 | 5.1.x (sin esquema nuevo sobre 0.4.4, que ya estÃ¡ **verificado** âœ…; correcciones de concurrencia de 3E.3, PHPUnit todavÃ­a pendiente de probar) |
| 0.4.4 | 5.1.x (sin esquema nuevo sobre 0.4.3, que ya estÃ¡ **verificado** âœ…; interfaz de 3D.4 y PHPUnit/Behat todavÃ­a pendientes de probar) |
| 0.4.3 | 5.1.x (sin esquema nuevo sobre 0.4.2, que ya estÃ¡ **verificado** âœ…; interfaz de 3D.3 y PHPUnit/Behat todavÃ­a pendientes de probar) |
| 0.4.2 | 5.1.x (instalaciÃ³n y **actualizaciÃ³n de esquema verificadas** âœ… hasta esta versiÃ³n inclusive, tras corregir un fallo de Ã­ndice en PostgreSQL; interfaz de 3B.2/3B.3A/3C.1/3D.2 y PHPUnit/Behat todavÃ­a pendientes de probar) |
| 0.4.1 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A/3C.1/3D.1 pendientes de probar â€” 3D.1 no toca esquema; 3B.2/3B.3A/3C.1 sÃ­, pendiente `db/upgrade.php`) |
| 0.4.0 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A/3C.1 pendientes de probar en el navegador y, para 3B.2/3B.3A/3C.1, `db/upgrade.php`) |
| 0.3.5 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A pendientes de probar en el navegador y, para 3B.2/3B.3A, `db/upgrade.php`) |
| 0.3.4 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A pendientes de probar en el navegador y, para 3B.2/3B.3A, `db/upgrade.php`) |
| 0.3.3 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2/3B.3A pendientes de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.2 | 5.1.x (instalaciÃ³n verificada hasta 3B.1 inclusive; 3B.2 pendiente de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.1 | 5.1.x (instalaciÃ³n verificada âœ…, incluida la interfaz de listado/detalle y el selector AJAX) |
| 0.3.0 | 5.1.x (instalaciÃ³n verificada âœ…) |
| 0.2.0 | 5.1.x (pendiente de verificaciÃ³n) |
| 0.1.0 | 5.1.x (pendiente de verificaciÃ³n) |

## Licencia

GNU GPL v3 o posterior, la misma licencia que Moodle.
