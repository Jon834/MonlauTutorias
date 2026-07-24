# local_monlaututoria

**Versión:** 0.5.3 · **Moodle:** 5.1.x (instalación verificada ✅) · **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignación de alumnos a tutores, registro de tutorías, historial entre cursos académicos, y separación entre contenido visible para el alumno y notas internas.

Toda la lógica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resúmenes y accesos rápidos.

## Estado del proyecto

**Fase 4.4 — Ficha longitudinal del alumno: UX, rendimiento y cierre.** ✅ Completada, sobre la Fase 4.3. **Cierra la Fase 4 completa (4.1-4.4).** Revisión de cierre sin migración de esquema: envoltorio `table-responsive` añadido a las 6 tablas del módulo (no solo las de la ficha del alumno, porque es el mismo defecto en el mismo método de renderizado); `aria-current="page"` en la pestaña activa de la ficha (la navegación por teclado ya funcionaba de forma nativa, son enlaces reales); nuevo `academic_year_repository::find()` para convertir un `academicyearid` inválido en un mensaje claro en vez de una excepción de base de datos genérica; y un N+1 real corregido en `renderer::student_summary()` — `core_user::get_user()` confirmado sin ninguna caché para ids normales (llamado antes una vez por cada tutor mostrado).

> **La migración de esquema de 3D.2 falló una primera vez en un Moodle 5.1 real sobre PostgreSQL** (`ddl_dependency_exception` por índices dependientes); corregida en `db/upgrade.php` y **confirmada por el usuario: la actualización completa se instala sin errores**. A partir de la Fase 3D.1, el seguimiento detallado usa `docs/roadmap.md`/`docs/project-status.md` (decisión explícita: se sigue ese roadmap, que no incluye todavía las interfaces pendientes de cotutores, reasignación, alumnos sin tutor ni cohortes).

Esta versión (0.5.3) añade:

| Área | Contenido |
|---|---|
| Responsive | `table-responsive` añadido a `academic_years_list()`, `catalogue_list()`, `assignments_list`, `student_history_table()`, `csv_import_preview_table()` y `csv_import_apply_result_table()` |
| Accesibilidad | `student_tabs()`: `aria-current="page"` en la pestaña activa (enlaces reales, ya accesibles por teclado de forma nativa) |
| Errores claros | `academic_year_repository::find()` (nuevo, devuelve `null` en vez de lanzar); usado en `student/view.php` para un `academicyearid` inválido, mismo criterio ya aplicado al `studentid` |
| Rendimiento | N+1 corregido en `renderer::student_summary()`: `core_user::get_user()` no tiene caché para ids normales, confirmado leyendo el core de Moodle; sustituido por un único `$DB->get_records_list()` por lote |
| Pruebas | PHPUnit: 1 caso nuevo en `academic_year_repository_test.php` + 2 en `renderer_test.php`. Behat: 1 escenario nuevo en `student_summary.feature` |

Versión previa 0.5.2 añadía:

| Área | Contenido |
|---|---|
| Permisos | Nueva capacidad `local/monlaututoria:viewownfile`, arquetipo "Usuario autenticado" (concedida por defecto a nivel de sistema, a diferencia del arquetipo "Estudiante" que solo se asigna a nivel de curso) |
| Servicio | `scope_service`: nueva rama de acceso propio — si `$userid === $studentid` y tiene `viewownfile`, acceso concedido sin depender de ninguna relación de tutoría |
| Interfaz | `student/view.php` calcula `$islimitedview` para el propio alumno; renderer oculta enlaces a `assignments/view.php` y las columnas Origen/Motivo del historial en esa vista |
| Deliberadamente no implementado | "Coordinación según ámbito" — no existe en el modelo de datos del proyecto el concepto de coordinador responsable de un subconjunto de alumnos/cohortes, mismo vacío ya documentado desde 3B.5A/3C.1/3E.1 |
| Pruebas | PHPUnit: 3 casos nuevos en `scope_service_test.php` + 4 en `renderer_test.php`. Behat: 2 escenarios nuevos en `student_summary.feature` |

Versión previa 0.5.1 añadía:

| Área | Contenido |
|---|---|
| Esquema | Nuevo campo `local_tut_assignment.reassignreason` (nullable) — ya anticipado desde la Fase 3B.4A, con su propio paso en `db/upgrade.php` |
| Servicio | `assignment_repository::search_history_for_student()` (nuevo): historial ordenado por curso académico y fecha de inicio, con filtros y paginación |
| Interfaz | Nueva pestaña "Historial" en `student/view.php` (curso académico, tutor, tipo, estado, fechas, origen, motivo de cierre/reasignación) |
| Corrección propia | `student_history_table()` no escapaba el nombre del tutor (`html_writer::table()` no escapa como Mustache) — encontrado y corregido antes de cerrar el incremento |
| Pruebas | PHPUnit: 6 casos nuevos en `assignment_repository_test.php` + 1 en `assignment_service_test.php` + 1 en `renderer_test.php`. Behat: `student_history.feature` (nuevo, 3 escenarios) |

Versión previa 0.5.0 añadía:

| Área | Contenido |
|---|---|
| Servicio | `student_summary_service::get_summary(studentid, academicyearid)` (nuevo, sin tabla): tutor principal y cotutores vigentes, cohorte, última asignación y próximos cambios — recalculado en cada petición, nunca persistido |
| Interfaz | Nueva página `student/view.php`: capacidad `viewstudent` + `scope_service` desde el primer commit, selector de curso académico, foto del alumno |
| Acceso | Enlace "Ver ficha" añadido al listado de asignaciones y al detalle de una asignación |
| Pruebas | PHPUnit: `student_summary_service_test.php` (nuevo, 5 casos) + 1 caso nuevo en `renderer_test.php`. Behat: `student_summary.feature` (nuevo, 3 escenarios) |

Versión previa 0.4.8 añadía:

| Área | Contenido |
|---|---|
| Decisión funcional | Política de retención decidida por el usuario antes de implementar: `local_tut_assignment` se conserva indefinidamente; una solicitud de acceso/borrado se resuelve con exportación completa y **anonimización, nunca borrado físico** de la fila |
| Privacy API | `classes/privacy/provider.php` ampliado: `get_contexts_for_userid()`/`get_users_in_context()`/`export_user_data()` cubren ahora `local_tut_assignment` y `local_tut_bulkoperation`; `delete_data_for_user(s)`/`delete_data_for_all_users_in_context()` anonimizan (reasignan al usuario "sin respuesta" de Moodle y vacían `note`) en vez de borrar |
| Retención | `cleanup_bulk_operations_task`: nuevo `TERMINAL_TTL_SECONDS` (90 días) purga operaciones ya finalizadas, sumado a la purga de abandonadas ya existente desde 3D.4 |
| Pruebas | PHPUnit: `tests/privacy/provider_test.php` (nuevo, 7 casos) + 4 casos nuevos en `cleanup_bulk_operations_task_test.php` |

Versión previa 0.4.7 añadía:

| Área | Contenido |
|---|---|
| Corrección | `academic_year_service::delete()`/`catalogue_service::delete()` no disparaban ningún evento, a diferencia del resto de sus métodos — la acción más irreversible de todas, sin auditoría. Nuevos eventos `academic_year_deleted`/`reason_deleted`/`modality_deleted` (con el `shortname` de la fila borrada). Cambio de firma: `delete()` exige ahora `$userid` en ambos servicios |
| Corrección | `csv_import_dispatch_service::dispatch()` no disparaba ningún evento al diferir una importación grande a tarea ad hoc. Nuevo evento `csv_import_queued` |
| Corrección | `process_csv_import_task::execute()` marcaba `failed` sin evento cuando el archivo persistido faltaba. `csv_import_failed` acepta ahora `failedrownumber` nulo para este caso |
| Documentado, sin código | `catalogue_service::move()` (reordenar) y la limpieza automática de `cleanup_bulk_operations_task` siguen sin evento — severidad cosmética / limpieza de sistema, no acciones de usuario |
| Pruebas | PHPUnit: 1 caso nuevo (`academic_year_deleted`) + 2 casos nuevos (`reason_deleted`/`modality_deleted`) + 2 pruebas existentes ampliadas (`csv_import_queued`, `csv_import_failed` con fila nula) |

Versión previa 0.4.6 añadía:

| Área | Contenido |
|---|---|
| Revisado, ya bien | `unassigned_students_service`/`cohort_assignment_preview_service` ya resuelven todo por lote, sin N+1 — sin cambios necesarios |
| Corrección | `assignments/index.php` resolvía los cursos académicos de la página con un `get()` por id distinto dentro de un bucle (N+1 real, severidad baja). Nuevo `academic_year_repository::get_many()` (una sola consulta) lo sustituye |
| Documentado, sin código | `csv_import_preview_service::resolve_row()` ejecuta varias consultas por fila del CSV — sí escala linealmente con archivos grandes, pero ya mitigado en la experiencia de usuario desde 3D.4 (umbral de tarea ad hoc); no reescrito por el riesgo de tocar la resolución de identificadores sin cobertura de integración real |
| Pruebas | PHPUnit: `tests/performance/assignment_listing_performance_test.php` (nuevo, crea 2.000 asignaciones reales, compara recuento de consultas a 50 y a 2.000 filas) + 2 casos de `get_many()` |

Versión previa 0.4.5 añadía:

| Área | Contenido |
|---|---|
| Corrección | `csv_import_apply_service::apply()`: la transición `previewed → processing` era dos pasos separados con la recomputación completa de la previsualización en medio — una ventana de carrera real (dos clics en "Aplicar" podían escribir asignaciones duplicadas). Ahora usa `bulk_operation_repository::claim()`, una comparación-y-escritura atómica |
| Corrección | `assignment_service::close()`/`remove_cotutor()`: no releían la fila justo antes de escribir (a diferencia de `reassign_primary_tutor()` desde 3B.4A) — un cierre doble concurrente podía sobrescribir el motivo/nota/fecha del primer cierre en vez de rechazarse |
| Nuevo | `bulk_operation_repository::claim(int $id, string $fromstatus, string $tostatus): bool` — primitiva de comparar-y-intercambiar reutilizable |
| Documentado, sin código | `assignment_service::create()` tiene una carrera de la misma familia, pero sin solución portable disponible (índice único condicional o bloqueo de fila, ninguno de los dos expresable/disponible aquí) — limitación conocida, no una protección cosmética |
| Pruebas | PHPUnit: 2 casos nuevos de `claim()` + 1 de aplicación CSV concurrente + 2 de cierre/cotutor concurrente, con dos nuevos dobles de prueba |

Versión previa 0.4.4 añadía:

| Área | Contenido |
|---|---|
| Corrección | Bug real de 3D.3: `csv_import_apply_service` llamaba a un método (`csv_import_preview_summary::from_array()`) que no existía — invisible a `php -l`, solo detectable ejecutando pruebas o el flujo real |
| Informe | Tabla de resultado por fila tras aplicar, no solo recuentos agregados |
| Exportación | `csv_import_error_export_service`: descarga CSV de filas no aplicadas (conflicto/error/excluida/fallida), con neutralización de inyección de fórmulas (`=`, `+`, `-`, `@`) y sin persistir nunca el informe (vive en `$SESSION`, descarga única) |
| Tarea ad hoc | `csv_import_dispatch_service` + `process_csv_import_task`: por encima de 50 filas, la importación se difiere a una tarea en segundo plano en vez de aplicarse en la misma petición |
| Limpieza | `cleanup_bulk_operations_task` (diaria): purga operaciones abandonadas y archivos temporales huérfanos |
| Privacy API | Área de archivos `csvimport` declarada, sin exportación/borrado (misma razón documentada que `local_tut_assignment`) |
| Eventos | `csv_error_report_downloaded` |
| Pruebas | PHPUnit: 1 prueba de dominio + 5 del servicio de exportación + 3 del servicio de despacho + 3 de la tarea ad hoc + 5 de la tarea de limpieza + 1 prueba integral (parseo→previsualización→despacho→aplicación→informe) |

Versión previa 0.4.3 añadía:

| Área | Contenido |
|---|---|
| Servicio | `csv_import_apply_service::apply()`: crea o reasigna asignaciones reales a partir de una previsualización, reutilizando `assignment_service::create()`/`reassign_primary_tutor()` |
| Reasignación de conflictos | Solo si se activa explícitamente `allowreassignconflicts` (nunca por defecto); un duplicado exacto nunca se reasigna |
| Idempotencia | Recomprobación de duplicados antes de escribir cada fila; una operación no puede aplicarse dos veces |
| Revalidación | Nunca confía en la previsualización guardada — recalcula y compara antes de aplicar |
| Estrategias | `partial_valid` (por defecto) y `atomic_all` (todo o nada, con rollback real) |
| Eventos | `csv_import_started`/`completed`/`completed_with_errors`/`failed` |
| Interfaz | Tercer paso en `assignments/import.php`: aplicar con confirmación explícita y resumen del resultado |
| Pruebas | PHPUnit: 9 casos del servicio de aplicación + 3 de eventos |

Versión previa 0.4.2 añadía:

| Área | Contenido |
|---|---|
| Esquema | `local_tut_bulkoperation` (de 3C.1) ampliada con `operationtype=csv_import`; `cohortid`/`academicyearid`/`primarytutorid`/`mode` ahora admiten `null` — quinta migración de esquema real del proyecto |
| Servicio | `csv_import_preview_service::preview()`: resuelve cada fila contra la BD (alumno/tutor por correo/usuario/idnumber, curso por shortname, cohorte opcional por id/idnumber) reutilizando validaciones ya públicas de `assignment_service` |
| Estados | `valid`, `warning`, `conflict`, `error`, `excluded` — cohorte no encontrada es advertencia, no error |
| Interfaz | `assignments/import.php`: subida + tabla de previsualización + exclusión manual (siempre recalculada desde cero) |
| Pruebas | PHPUnit: 16 casos nuevos + repositorio ampliado; Behat: primeros escenarios de la Fase 3D |

Versión previa 0.4.1 añadía:

| Área | Contenido |
|---|---|
| Servicio | `csv_import_parser_service::parse()`: convierte contenido CSV en filas validadas sintácticamente (cabeceras, campos obligatorios, formatos, duplicados internos) — sin consultar todavía la base de datos |
| Formato | `fgetcsv()` sobre stream en memoria (soporta comillas con delimitadores/saltos de línea), conversión de codificación, retirada de BOM |
| Pruebas | PHPUnit: 17 casos de parseo y validación |

Versión previa 0.4.0 añadía:

| Área | Contenido |
|---|---|
| Tabla nueva | `local_tut_bulkoperation` (identidad + parámetros + resumen agregado de una operación) — **sin** tabla de elementos por alumno, decisión explícita para no retener previsualizaciones que pueden quedar obsoletas o nunca ejecutarse |
| Servicio | `cohort_assignment_preview_service::preview()`: clasifica cada miembro de una cohorte frente a un tutor principal/cotutor propuestos, con la misma semántica de vigencia del resto del proyecto |
| Acciones | `cohort_assignment_action` (10 códigos: crear, reasignar, cerrar ausente, sin cambios, conflicto, omitidos...) — principal y cotutor de forma independiente por alumno |
| Modos | `cohort_sync_mode`: `preview_only`, `add_only`, `add_and_close_missing`, `replace_primary` — solo clasificación en esta fase, sin ejecución |
| Caducidad | Sin tabla de detalle: se recalcula y compara el resumen agregado contra el guardado (`has_changed_since_preview()`), más comprobación por antigüedad (`is_expired()`) |
| Reutilización | 4 validaciones de `assignment_service` (tutor, cohorte, curso bloqueado) pasan de `private` a `public` para evitar duplicarlas |
| Pruebas | PHPUnit: los 13 escenarios de previsualización del prompt (incluidos los 4 modos), más caducidad, cambio detectado y validaciones |

Versiones previas: 0.5.2 (Fase 4.3 — permisos y vistas), 0.5.1 (Fase 4.2 — historial de asignaciones), 0.5.0 (Fase 4.1 — ficha del alumno: cabecera y resumen), 0.4.8 (Fase 3E.6 — Privacy API completa y retención; cierra la Fase 3E con 3E.7/3E.8), 0.4.7 (Fase 3E.5 — revisión de eventos y auditoría), 0.4.6 (Fase 3E.4 — rendimiento y revisión N+1), 0.4.5 (Fase 3E.3 — concurrencia e idempotencia), 0.4.4 (Fase 3D.4 — informe y cierre de la importación CSV), 0.4.3 (Fase 3D.3 — aplicación real de la importación CSV), 0.3.5 (Fase 3B.5A — servicio de detección de alumnos sin tutor), 0.3.4 (Fase 3B.4A — servicio de reasignación), 0.3.3 (Fase 3B.3A — cierre de asignaciones), 0.3.2 (Fase 3B.2 — creación y edición manual), 0.3.1 (Fase 3B.1 — listado y detalle, confirmada en Moodle real), 0.3.0 (Fase 3A — modelo y servicios de asignación), 0.2.0 (Fase 2 — cursos académicos y catálogos).

**Todavía sin implementar:** interfaz de asignación masiva desde cohortes (formulario, previsualización en pantalla, confirmación, ejecución, cierre de ausentes, sustitución), interfaz del informe de alumnos sin tutor, formulario e interfaz de reasignación, gestión de cotutores como funcionalidad propia, vistas diferenciadas por rol en la ficha del alumno (Fase 4.3, la cabecera/resumen e historial de 4.1/4.2 ya existen), registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones, pantalla de "operaciones" para consultar el estado de una importación CSV diferida a tarea ad hoc.

Ver [`docs/roadmap.md`](../../docs/roadmap.md) y [`docs/project-status.md`](../../docs/project-status.md) en la raíz del repositorio para el roadmap y estado actuales; [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) recoge la narrativa detallada de las fases 1-4.4 (**la Fase 4 queda completa** con este incremento).

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) ya se comprobó compatible al instalar correctamente en un Moodle 5.1 de pruebas real; sigue pendiente ajustarlo al número exacto del core (no bloqueante).
- PHP según los requisitos de Moodle 5.1.

## Instalación

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *Administración del sitio → Notificaciones* para completar la instalación, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** verificado en un Moodle 5.1 de pruebas real hasta la Fase 3B.1 inclusive a nivel de interfaz (incluido el selector AJAX de usuario). **La actualización de esquema completa (3B.2 → 3D.2, incluida la corrección del fallo de índice en PostgreSQL) se ha instalado sin errores en esa misma instancia** — confirmado por el usuario. 3D.3/3D.4/4.1 no añaden esquema; 4.2 sí añadió una migración real (`local_tut_assignment.reassignreason`, nullable); **4.3 y 4.4 no añaden esquema** (una capacidad y una revisión de cierre, respectivamente). Ninguna de las dos últimas se ha probado todavía contra esa instancia. Lo que falta todavía por probar manualmente en el navegador: la interfaz de las Fases 3B.2/3B.3A/3C.1/3D.2/3D.3/3D.4/4.1/4.2/4.3/4.4 (esta última incluye comprobar visualmente el desplazamiento horizontal de las tablas en una ventana estrecha), y ejecutar PHPUnit/Behat (los servicios de 3B.4A/3B.5A no tienen interfaz que probar).

## Versiones compatibles

| Versión del plugin | Moodle |
|---|---|
| 0.5.3 | 5.1.x (sin esquema nuevo sobre 0.5.2; cierre de la Fase 4 — responsive, teclado, errores claros, N+1 — PHPUnit/Behat pendientes de probar) |
| 0.5.2 | 5.1.x (sin esquema nuevo sobre 0.5.1; nueva capacidad `viewownfile` y vista limitada de 4.3, PHPUnit/Behat pendientes de probar) |
| 0.5.1 | 5.1.x (nueva migración de esquema — `reassignreason` — todavía sin probar contra la instancia real; historial de 4.2, PHPUnit/Behat pendientes de probar) |
| 0.5.0 | 5.1.x (sin esquema nuevo sobre 0.4.8, que ya está **verificado** ✅; ficha del alumno de 4.1, PHPUnit/Behat todavía pendientes de probar) |
| 0.4.8 | 5.1.x (sin esquema nuevo sobre 0.4.7, que ya está **verificado** ✅; Privacy API completa y retención de 3E.6, PHPUnit todavía pendiente de probar) |
| 0.4.7 | 5.1.x (sin esquema nuevo sobre 0.4.6, que ya está **verificado** ✅; correcciones de eventos/auditoría de 3E.5, PHPUnit todavía pendiente de probar) |
| 0.4.6 | 5.1.x (sin esquema nuevo sobre 0.4.5, que ya está **verificado** ✅; corrección N+1 de 3E.4, PHPUnit todavía pendiente de probar) |
| 0.4.5 | 5.1.x (sin esquema nuevo sobre 0.4.4, que ya está **verificado** ✅; correcciones de concurrencia de 3E.3, PHPUnit todavía pendiente de probar) |
| 0.4.4 | 5.1.x (sin esquema nuevo sobre 0.4.3, que ya está **verificado** ✅; interfaz de 3D.4 y PHPUnit/Behat todavía pendientes de probar) |
| 0.4.3 | 5.1.x (sin esquema nuevo sobre 0.4.2, que ya está **verificado** ✅; interfaz de 3D.3 y PHPUnit/Behat todavía pendientes de probar) |
| 0.4.2 | 5.1.x (instalación y **actualización de esquema verificadas** ✅ hasta esta versión inclusive, tras corregir un fallo de índice en PostgreSQL; interfaz de 3B.2/3B.3A/3C.1/3D.2 y PHPUnit/Behat todavía pendientes de probar) |
| 0.4.1 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A/3C.1/3D.1 pendientes de probar — 3D.1 no toca esquema; 3B.2/3B.3A/3C.1 sí, pendiente `db/upgrade.php`) |
| 0.4.0 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A/3C.1 pendientes de probar en el navegador y, para 3B.2/3B.3A/3C.1, `db/upgrade.php`) |
| 0.3.5 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A/3B.5A pendientes de probar en el navegador y, para 3B.2/3B.3A, `db/upgrade.php`) |
| 0.3.4 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A pendientes de probar en el navegador y, para 3B.2/3B.3A, `db/upgrade.php`) |
| 0.3.3 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A pendientes de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.2 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2 pendiente de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.1 | 5.1.x (instalación verificada ✅, incluida la interfaz de listado/detalle y el selector AJAX) |
| 0.3.0 | 5.1.x (instalación verificada ✅) |
| 0.2.0 | 5.1.x (pendiente de verificación) |
| 0.1.0 | 5.1.x (pendiente de verificación) |

## Licencia

GNU GPL v3 o posterior, la misma licencia que Moodle.
