# Changelog — local_monlaututoria

## 0.5.3 — 2026-07-24

**Ficha longitudinal del alumno — UX, rendimiento y cierre** — Fase 4.4 (sobre la Fase 4.3). **Cierra la Fase 4 completa** (4.1-4.4). Sin migración de esquema — todos los hallazgos de esta revisión de cierre eran de código de presentación/consulta, no de modelo de datos.

- **Diseño responsive**: las tablas generadas con `html_writer::table()` (`academic_years_list()`, `catalogue_list()`, `student_history_table()`, `csv_import_preview_table()`, `csv_import_apply_result_table()`) y la tabla Mustache `assignments_list` no estaban envueltas en un contenedor `table-responsive` — en una pantalla estrecha, una tabla ancha desbordaba la página entera en vez de desplazarse solo dentro de su propio contenedor. Corregido en las 6 tablas del módulo, no solo en las de la ficha del alumno, porque es el mismo defecto del mismo método de renderizado en cada caso.
- **Navegación por teclado**: las pestañas de la ficha del alumno (`student_tabs()`) ya eran accesibles por teclado de forma nativa (son enlaces `<a href>` reales, no un widget JS) — añadido `aria-current="page"` en la pestaña activa, la señal de accesibilidad correcta para un conjunto de enlaces de navegación real (mismo patrón que un breadcrumb), no `aria-selected` (que sería para un tablist controlado por JS que no existe aquí).
- **Errores claros**: un `academicyearid` manipulado en `student/view.php` dependía de `academic_year_repository::get()` (`MUST_EXIST`), que deja burbujear una `dml_missing_record_exception` genérica. Nuevo método `academic_year_repository::find()` (devuelve `null` en vez de lanzar) usado para producir el mismo tipo de mensaje claro que ya existía para un `studentid` inválido en el mismo archivo, en vez de una página de excepción de base de datos.
- **Revisión N+1**: `renderer::student_summary()` llamaba a `core_user::get_user()` una vez por tutor (principal, cada cotutor, última asignación, cada próximo cambio) — confirmado que este método **no tiene ninguna caché** para ids normales (siempre golpea la base de datos), así que era el mismo patrón N+1 ya corregido en `assignments/index.php` en la Fase 3E.4. Corregido con un único `$DB->get_records_list()` por lote, igual que en el resto del proyecto.
- Sin cambios de esquema. Como sí cambia código de producción, bump de versión de **parche** (0.5.2 → 0.5.3) — último incremento del bloque de la Fase 4.

**Pruebas**
- PHPUnit: 1 caso nuevo en `academic_year_repository_test.php` (`find()` devuelve `null` en vez de lanzar), 2 casos nuevos en `renderer_test.php` (recuento de lecturas de BD constante con 1 vs. 5 cotutores; `aria-current="page"` solo en la pestaña activa).
- Behat: `student_summary.feature` ampliado con 1 escenario nuevo (`academicyearid` inválido muestra el mensaje claro del plugin, no una página de excepción genérica).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Cierra la Fase 4.** Próxima fase según `docs/roadmap.md`: **Fase 5 — registro de tutorías**.

---

## 0.5.2 — 2026-07-24

**Ficha longitudinal del alumno — permisos y vistas** — Fase 4.3 (sobre la Fase 4.2). Sin migración de esquema (solo una capacidad nueva, sincronizada automáticamente por Moodle).

- **Nueva capacidad `local/monlaututoria:viewownfile`**: un alumno puede ver su propia ficha longitudinal, sin necesidad de `viewstudent`/`viewownstudents`/`viewallassignments`. Concedida por defecto al arquetipo **"Usuario autenticado"** (`user`), no al de "Estudiante" (`student`) — el rol Student de Moodle se asigna normalmente a nivel de curso, y esta capacidad es de contexto de sistema, así que un valor por defecto atado al arquetipo `student` nunca se aplicaría de verdad en una instalación típica. Es seguro concederla ampliamente: `scope_service` solo la usa para que un usuario vea **su propio** registro, nunca el de otra persona, sea quien sea.
- **`scope_service::can_user_access_student()`**: nueva rama, comprobada antes que ninguna otra — si el usuario que consulta ES el propio alumno y tiene `viewownfile`, acceso concedido de forma incondicional (no depende de ninguna relación de tutoría).
- **Vista limitada del alumno** en `student/view.php`: al ver su propia ficha, se ocultan los enlaces a `assignments/view.php` (página a la que no tiene capacidad de acceder, y que muestra la observación administrativa/motivo/quién creó o modificó la fila) y, en la pestaña Historial, las columnas "Origen" y "Motivo" (categorización administrativa interna).
- **"Coordinación según ámbito" — explícitamente no abordado**: el modelo de ámbitos de este proyecto sigue siendo binario (`viewallassignments` o nada); no existe el concepto de "coordinador responsable de un subconjunto de alumnos/cohortes", mismo vacío ya documentado desde las Fases 3B.5A/3C.1/3E.1. Construir un ámbito más granular falso habría sido peor que dejarlo pendiente.

**Pruebas**
- PHPUnit: 3 casos nuevos en `scope_service_test.php` (acceso propio concedido/denegado según la capacidad, y que no se extiende al registro de otro alumno) + 4 casos nuevos en `renderer_test.php` (vista limitada sin enlaces, vista completa con enlaces, historial sin motivo/origen).
- Behat: `student_summary.feature` ampliado (2 escenarios nuevos: acceso propio sin capacidad concedida manualmente, y ocultación del motivo/enlace en la vista propia).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de la Fase 4): diseño responsive, navegación por teclado, estados vacíos y de error, revisión N+1, PHPUnit/Behat/revisión manual de cierre (4.4).

---

## 0.5.1 — 2026-07-24

**Ficha longitudinal del alumno — historial de asignaciones** — Fase 4.2 (sobre la Fase 4.1, ficha del alumno). **Migración de esquema real**: nuevo campo `local_tut_assignment.reassignreason` (char(30), nullable) — ya anticipado en el docblock de `assignment_reassign_reason` desde la Fase 3B.4A ("se revisará si hace falta persistirlo cuando la interfaz necesite mostrarlo en el historial"), mismo criterio que `closereason` (Fase 3B.3A).

- `assignment_repository::search_history_for_student()` (nuevo): reutiliza `build_search_where()`, orden fijo por curso académico y fecha de inicio (cronología, no tabla ordenable).
- `reassign_primary_tutor()` ahora persiste el motivo de reasignación en la fila nueva (nunca en la que cierra); el evento `student_reassigned` sigue siendo la auditoría, la columna es el dato consultable sin leer el registro de eventos.
- Nueva pestaña "Historial" en `student/view.php` (que ahora tiene 4 pestañas: Resumen, Historial, Tutorías y Acuerdos — las 2 últimas vacías hasta las fases 5/6): tabla con curso académico, tutor, tipo, estado, fechas, origen y motivo (cierre o reasignación), filtro por estado, paginación.
- **Bug propio encontrado y corregido antes de cerrar el incremento**: `student_history_table()` usa `html_writer::table()` en vez de Mustache — a diferencia de esta última, no escapa nada automáticamente. El nombre del tutor se pasaba sin `s()`; corregido con su prueba de regresión.

**Pruebas**
- PHPUnit: 6 casos nuevos en `assignment_repository_test.php`, 1 en `assignment_service_test.php`, 1 en `renderer_test.php`. Behat: `student_history.feature` (nuevo, 3 escenarios).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores) y validación XML de `install.xml`.

**Fuera de alcance de esta versión** (resto de la Fase 4): vistas diferenciadas por rol (4.3); diseño responsive, navegación por teclado, revisión N+1, cierre (4.4).

---

## 0.5.0 — 2026-07-24

**Ficha longitudinal del alumno — cabecera y resumen** — Fase 4.1 (primer incremento de la Fase 4, sobre la Fase 3E ya cerrada). Sin migración de esquema: todo se calcula sobre `local_tut_assignment` ya existente, nunca se persiste.

- Nueva página `student/view.php?id=<studentid>&academicyearid=<opcional>`: capacidad `viewstudent` + `scope_service` desde el primer momento (misma comprobación que `assignments/view.php`, no algo que se añada más adelante en la Fase 4.3).
- Nuevo `student_summary_service::get_summary()`: tutor principal y cotutores vigentes (reutiliza `find_active_primary()`/`find_active_cotutors()`, ya existentes desde 3B.2/3B.4A), cohorte (resuelta de la asignación principal), última asignación del curso académico y cualquier cambio programado a futuro (asignación activa con `timestart` todavía no llegado).
- Selector de curso académico en la propia ficha (por defecto, el curso activo).
- Enlace **Ver ficha** añadido al listado de asignaciones y al detalle de una asignación.
- Foto del alumno vía `$OUTPUT->user_picture()` (API pública de Moodle).

**Pruebas**
- PHPUnit: `tests/service/student_summary_service_test.php` (nuevo, 5 casos) + 1 caso nuevo en `tests/output/renderer_test.php` (escapado de un nombre de tutor hostil, mismo patrón de defensa en profundidad de la Fase 3E.2).
- Behat: `tests/behat/student_summary.feature` (nuevo, 3 escenarios).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de la Fase 4): historial de asignaciones (4.2), vistas diferenciadas por rol más allá de la comprobación binaria de `scope_service` — tutor/coordinación/alumno (4.3), rendimiento/revisión N+1/cierre (4.4).

---

## 0.4.8 — 2026-07-24

**Privacy API completa y retención** — Fase 3E.6 (sobre 3E.1-3E.5, cierre integral del módulo de asignaciones). Sin migración de esquema. Cierra el hueco de cumplimiento que este proyecto dejaba explícitamente abierto desde la Fase 3A.

**Decisión funcional previa (el usuario decidió antes de que se tocara ningún código):**
- `local_tut_assignment`: conservación indefinida (es el historial longitudinal que el proyecto existe para mantener); una solicitud de acceso/borrado se resuelve con exportación completa y **anonimización, nunca borrado físico de la fila** (borrar destruiría también el historial de la otra persona implicada).
- `local_tut_bulkoperation`: mismo tratamiento de anonimización, más un límite de conservación real de **90 días** para operaciones ya finalizadas.

**Cambios:**
- `classes/privacy/provider.php`: `get_contexts_for_userid()`/`get_users_in_context()` ahora cubren también `local_tut_assignment` (studentid/tutorid/createdby/modifiedby) y `local_tut_bulkoperation` (createdby/primarytutorid/cotutorid). `export_user_data()` añade `assignments`/`bulkoperations` al export, con la contraparte de cada relación resuelta a un nombre legible. Nuevos métodos privados de anonimización (`anonymize_assignments()`, `anonymize_all_assignments()`, `anonymize_bulk_operations()`, `anonymize_all_bulk_operations()`): reasignan studentid/tutorid/createdby/modifiedby/primarytutorid/cotutorid al usuario "sin respuesta" de Moodle (mismo mecanismo que ya reasignaba atribución en los catálogos desde la Fase 2) y vacían el campo `note` de cualquier fila afectada — nunca borran la fila.
- `classes/task/cleanup_bulk_operations_task.php`: nuevo `TERMINAL_TTL_SECONDS` (90 días); purga operaciones `completed`/`completed_with_errors`/`failed`/`cancelled` más antiguas, sumado a la purga de operaciones abandonadas ya existente desde 3D.4.

**Pruebas**
- PHPUnit: `tests/privacy/provider_test.php` (nuevo, 7 casos) + 4 casos nuevos en `tests/task/cleanup_bulk_operations_task_test.php` (uno por estado terminal, con `@dataProvider`).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de 3E): manual administrativo (3E.7), prueba de actualización desde cada versión publicada (3E.8).

---

## 0.4.7 — 2026-07-23

**Revisión de eventos y auditoría** — Fase 3E.5 (sobre 3E.1-3E.4, cierre integral del módulo de asignaciones). Sin migración de esquema.

- **Corregido — eliminaciones sin evento:** `academic_year_service::delete()` y `catalogue_service::delete()` (motivos y modalidades) eran las únicas acciones de escritura de sus respectivas clases que no disparaban ningún evento — precisamente la más irreversible de todas. Nuevos eventos `academic_year_deleted`, `reason_deleted`, `modality_deleted` (con el `shortname` de la fila eliminada en `other`, ya que `objectid` deja de poder resolverse a nada tras el borrado). Cambio de firma: `delete()` en ambos servicios ahora exige `$userid` para poder atribuir el evento.
- **Corregido — importación CSV diferida sin evento propio:** `csv_import_dispatch_service::dispatch()` no disparaba ningún evento en el momento de encolar una importación grande — el único rastro era `csv_import_started`, disparado cuando la tarea en segundo plano se ejecutaba de verdad (que puede ser mucho después, o nunca si la tarea falla antes de llegar tan lejos). Nuevo evento `csv_import_queued`.
- **Corregido — fallo de tarea ad hoc sin evento:** `process_csv_import_task::execute()` marcaba la operación como `failed` cuando el archivo persistido no aparecía, sin disparar ningún evento — a diferencia del rollback `atomic_all`, que sí dispara `csv_import_failed`. `csv_import_failed::create_from_operation()` acepta ahora `failedrownumber` nulo para cubrir también este caso ("falló antes de intentar ninguna fila").
- **Revisado, documentado sin cambio:** `catalogue_service::move()` (reordenar motivos/modalidades) no dispara evento — de severidad baja/cosmética, se deja documentado en vez de añadir un evento nuevo. La limpieza automática de operaciones abandonadas (`cleanup_bulk_operations_task`) tampoco dispara evento — es limpieza de sistema sobre datos ya efímeros y agregados, no una acción de usuario.
- Actualizaciones de llamada: `academicyear_delete.php` y `catalogue_action.php` pasan ahora el `userid` del usuario actual a `delete()`.

**Pruebas**
- PHPUnit: 1 caso nuevo para `academic_year_deleted`, 2 para `reason_deleted`/`modality_deleted`, 1 para `csv_import_queued` (añadido a una prueba existente), 1 actualizado para el nuevo evento en el fallo de archivo ausente de la tarea ad hoc.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de 3E): Privacy API completa (3E.6), manual administrativo (3E.7), prueba de actualización desde cada versión publicada (3E.8).

---

## 0.4.6 — 2026-07-23

**Rendimiento con 2.000 alumnos y revisión de consultas N+1** — Fase 3E.4 (sobre 3E.1-3E.3, cierre integral del módulo de asignaciones). Sin migración de esquema.

- **Revisado, ya bien:** `unassigned_students_service` y `cohort_assignment_preview_service` ya resuelven todos sus datos por lote (una consulta para todos los miembros/asignaciones/cotutores implicados, nunca una consulta por alumno dentro de un bucle) — sin cambios necesarios.
- **Corregido:** `assignments/index.php` resolvía los cursos académicos de la página actual con una llamada a `academic_year_repository::get()` por cada id distinto dentro de un bucle. Acotado por el tamaño de página (máximo 20), así que de severidad baja, pero un patrón N+1 real y fácil de corregir: nuevo `academic_year_repository::get_many(array $ids): array` (una sola consulta), reemplaza el bucle.
- **Encontrado y documentado, sin cambio de código:** `csv_import_preview_service::resolve_row()` ejecuta varias consultas por fila del CSV (alumno, tutor, curso académico, cohorte, duplicados) — con un archivo de miles de filas, esto sí escala linealmente. No se reescribe en este incremento: el impacto ya está mitigado a nivel de arquitectura desde la Fase 3D.4 (los archivos de más de 50 filas se difieren a una tarea en segundo plano, así que el coste ya no bloquea la petición HTTP del usuario), y una reescritura para resolver todo por lote tocaría la lógica de resolución de identificadores (correo → usuario → número de identificación) sin cobertura de integración real en este entorno para validar que no se rompe nada. Documentado como oportunidad de optimización futura, no como error.
- Nuevo `tests/performance/assignment_listing_performance_test.php`: crea 2.000 asignaciones reales y comprueba que el número de consultas de `search()`/`count_search()` no escala con el tamaño de la tabla (idéntico a 50 filas que a 2.000) — la propiedad real que importa, no un tiempo de reloj poco fiable entre máquinas.

**Pruebas**
- PHPUnit: 1 prueba de rendimiento nueva (2.000 filas reales, lenta de ejecutar a propósito) + 2 casos nuevos para `academic_year_repository::get_many()`.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de 3E): revisión de eventos y auditoría (3E.5), Privacy API completa (3E.6), manual administrativo (3E.7), prueba de actualización desde cada versión publicada (3E.8).

---

## 0.4.5 — 2026-07-23

**Concurrencia e idempotencia** — Fase 3E.3 (sobre 3E.1/3E.2, cierre integral del módulo de asignaciones). Sin migración de esquema. A diferencia de 3E.1/3E.2 (revisión y pruebas, sin código de producción), este incremento sí corrige comportamiento real.

- **`csv_import_apply_service::apply()`**: la transición `previewed → processing` ahora usa `bulk_operation_repository::claim()`, una comprobación-y-escritura atómica (dentro de una transacción, releyendo el estado real justo antes de escribir) en vez de una comprobación separada seguida de una escritura incondicional. Corrige una ventana de carrera real: dos clics en "Aplicar importación" (o una petición reintentada) podían pasar ambos la comprobación inicial mientras `preview()` se recalculaba (un trabajo no trivial: reparsea todo el archivo y consulta la base de datos fila por fila), y ambos acabar escribiendo asignaciones duplicadas.
- **`assignment_service::close()` y `remove_cotutor()`**: mismo patrón ya usado en `reassign_primary_tutor()` desde la Fase 3B.4A — se relee la fila justo antes de escribir, dentro de una transacción, y se aborta si su estado ya no es `active`. Corrige que un cierre doble concurrente pudiera sobrescribir silenciosamente el motivo/nota/fecha del primer cierre.
- **`bulk_operation_repository::claim(int $id, string $fromstatus, string $tostatus): bool`** (nuevo): primitiva de comparar-y-intercambiar reutilizable para futuras transiciones de estado de operaciones masivas.
- **Decisión documentada, sin cambio de código:** `assignment_service::create()` tiene una ventana de carrera similar (comprobación de duplicado/conflicto de tutor principal antes de insertar), pero envolverla en una transacción no la cerraría de verdad sin un índice único condicional (no expresable de forma portable en XMLDB) o bloqueo de fila (no disponible portablemente en Moodle DML) — se documenta como limitación conocida en vez de añadir una protección cosmética que no resolvería nada. Ver `docs/seguridad-permisos.md`.

**Pruebas**
- PHPUnit: 2 casos nuevos en `bulk_operation_repository_test.php` (`claim()` transiciona cuando coincide, falla y no toca el estado cuando ya cambió), 1 caso nuevo en `csv_import_apply_service_test.php` (aplicación concurrente rechazada por el claim atómico, simulada con un doble de prueba que devuelve una instantánea obsoleta), 2 casos nuevos en `assignment_service_test.php` (`close()`/`remove_cotutor()` rechazan un cierre concurrente detectado en la relectura, mismo patrón de doble de prueba que `reassign_primary_tutor()` desde 3B.4A).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de 3E): rendimiento con 2.000 alumnos y revisión N+1 (3E.4), revisión de eventos y auditoría (3E.5), Privacy API completa (3E.6), manual administrativo (3E.7), prueba de actualización desde cada versión publicada (3E.8).

---

## 0.4.4 — 2026-07-23

**Informe y cierre de la importación CSV** — Fase 3D.4 (sobre la Fase 3D.3), última de la Fase 3D. Sin migración de esquema.
- **Corrección de un bug real de 3D.3**: `csv_import_apply_service::apply()` llamaba a `csv_import_preview_summary::from_array()`, un método que no existía (`php -l` no detecta llamadas a métodos estáticos inexistentes). Corregido, con su propio PHPUnit de round-trip.
- Informe por fila tras aplicar: `csv_import_apply_result_row` gana un campo `values` opcional (valores en bruto), y `assignments/import.php` muestra ahora una tabla de resultado por fila, no solo recuentos.
- `csv_import_error_export_service`: descarga CSV de las filas no aplicadas tal cual (conflicto, error, excluida, fallida), vía `\core\dataformat::download_data()`. Neutraliza inyección de fórmulas (valores que empiezan por `=`, `+`, `-`, `@` reciben un prefijo de comilla simple). El informe nunca se persiste: vive en `$SESSION` hasta su única descarga (`assignments/import_report.php`, nuevo).
- `csv_import_dispatch_service` + `process_csv_import_task`: por encima de 50 filas, la importación se difiere a una tarea ad hoc en vez de aplicarse en la misma petición — el archivo se copia temporalmente a un área propia del plugin y la tarea llama al mismo `csv_import_apply_service::apply()` de 3D.3 sin duplicar reglas.
- `cleanup_bulk_operations_task` (nueva, diaria): purga operaciones `draft`/`previewed` abandonadas y archivos huérfanos del área `csvimport`; no toca operaciones en estado terminal (sin política de conservación institucional todavía).
- Privacy API: área de archivos `csvimport` declarada vía `add_subsystem_link('core_files', ...)`, sin exportación/borrado — mismo criterio que `local_tut_assignment`.
- Evento nuevo: `csv_error_report_downloaded`.

**Pruebas**
- PHPUnit: `csv_import_preview_summary_test.php` (nuevo), `csv_import_error_export_service_test.php` (nuevo, 5 casos), `csv_import_dispatch_service_test.php` (nuevo, 3 casos), `process_csv_import_task_test.php` (nuevo, 3 casos), `cleanup_bulk_operations_task_test.php` (nuevo, 5 casos), `csv_import_integration_test.php` (nuevo, prueba integral parseo→previsualización→despacho→aplicación→informe).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).
- Sin Behat nuevo (el flujo diferido requiere un archivo grande, poco practicable en un escenario Behat).

**Cierra la Fase 3D.** Fuera de alcance (sin fecha): pantalla de "operaciones" para consultar el estado de una importación diferida tras abandonar la página.

---

## 0.4.3 — 2026-07-23

**Aplicación real de la importación CSV** — Fase 3D.3 (sobre la Fase 3D.2). Sin informe detallado, exportación de errores, tarea ad hoc ni limpieza de operaciones antiguas en este incremento — eso es la Fase 3D.4. Sin migración de esquema.
- `csv_import_apply_service::apply()`: crea (`assignment_service::create()`, forzando `source=csv`) o reasigna (`reassign_primary_tutor()`) asignaciones reales a partir de una previsualización, reutilizando servicios existentes sin escribir nunca directamente en `local_tut_assignment`.
- Reasignar un conflicto de tutor principal duplicado solo ocurre si se activa explícitamente `allowreassignconflicts` (casilla desmarcada por defecto) — nunca de forma automática; un duplicado exacto de la misma asignación nunca se reasigna, se trata siempre como "sin cambios".
- Idempotencia real: recomprobación de duplicados justo antes de escribir cada fila, y una operación no puede aplicarse dos veces (su estado pasa de `previewed` a un estado terminal).
- Nunca confía en la previsualización guardada: recalcula la clasificación en el momento de aplicar y rechaza si algo ha cambiado desde que se generó la previsualización.
- Dos estrategias: `partial_valid` (por defecto, continúa tras un fallo real de una fila) y `atomic_all` (revierte el lote completo si falla una fila, mediante una única transacción).
- Cuatro eventos nuevos: `csv_import_started`, `csv_import_completed`, `csv_import_completed_with_errors`, `csv_import_failed` — recuentos agregados únicamente.
- `assignments/import.php` amplía su flujo con un tercer paso ("Aplicar importación": estrategia, permitir reasignar conflictos, confirmación explícita) y un resumen básico del resultado.

**Pruebas**
- PHPUnit: `csv_import_apply_service_test.php` (nuevo, 9 casos) y `csv_import_apply_events_test.php` (nuevo, 3 casos).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).
- Sin Behat nuevo en esta entrega.

**Fuera de alcance de esta versión** (resto de 3D): informe detallado descargable, exportación de errores, tarea ad hoc para archivos grandes, limpieza de operaciones antiguas.

---

## 0.4.2 — 2026-07-23

**Subida y previsualización de importación CSV** — Fase 3D.2 (sobre la Fase 3D.1). Sin aplicación, informe, exportación ni tarea ad hoc en este incremento — solo subida + previsualización.
- `local_tut_bulkoperation` (de 3C.1) se amplía en vez de crear una tabla nueva: `operationtype=csv_import`; `cohortid`/`academicyearid`/`primarytutorid`/`mode` pasan a admitir `null`. **Quinta migración de esquema real del proyecto.**
- `csv_import_preview_service::preview()`: resuelve cada fila contra la base de datos (alumno/tutor por correo/usuario/`idnumber`, curso académico por `shortname`, cohorte opcional por id/`idnumber`), reutilizando las validaciones ya públicas de `assignment_service` (desde 3C.1) y las consultas de duplicados de `assignment_repository` (desde 3A) — sin duplicar ninguna regla.
- Estados por fila: `valid`, `warning`, `conflict`, `error`, `excluded`. Cohorte no encontrada = advertencia (se crearía sin cohorte), no error.
- `assignments/import.php`: formulario de subida (área de borrador de Moodle, nunca almacenamiento permanente del plugin) + tabla de previsualización + exclusión manual de filas, que siempre recalcula desde cero.
- Refactor: `is_expired()`/`generate_uuid()` se mueven de `cohort_assignment_preview_service` a `bulk_operation_repository`, compartidos ahora por los dos servicios de operación masiva.
- Evento nuevo `csv_import_previewed`, con recuentos agregados únicamente.

**Pruebas**
- PHPUnit: ampliación de `academic_year_repository_test.php` y `bulk_operation_repository_test.php`, `csv_import_preview_service_test.php` (nuevo, 16 casos), `csv_import_previewed_test.php` (nuevo).
- Behat: `csv_import_preview.feature` (nuevo, primeros escenarios de la Fase 3D) — con aviso explícito de que el paso de subida de archivo es lo menos verificado de esta entrega.
  - ⚠️ Nada ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin) y validación XML de `install.xml`.

**Fuera de alcance de esta versión** (resto de 3D): aplicación real, informe de resultados, exportación de errores, tarea ad hoc, limpieza de temporales.

---

## 0.4.1 — 2026-07-23

**Parser y formato de importación CSV** — Fase 3D.1 (sobre la Fase 3C.1). Sin subida de archivo, previsualización ni aplicación en este incremento — solo el parser puro.
- `csv_import_parser_service::parse()`: convierte contenido CSV en filas estructuradas y validadas sintácticamente. Cabeceras obligatorias `student`/`tutor`/`academicyear`, opcionales `cohort`/`assignmenttype`/`isprimary`/`timestart`/`timeend`/`source`; cabecera desconocida o alguna obligatoria ausente aborta el parseo completo (error a nivel de archivo, sin procesar filas).
- Validación por fila (solo sintáctica, sin consultar la base de datos): campos obligatorios no vacíos, `isprimary` `0`/`1`, fechas `YYYY-MM-DD` estrictas (ISO 8601), `assignmenttype`/`source` reutilizando `assignment_type`/`assignment_source` ya definidos, número de columnas coincidente con la cabecera, duplicados internos del propio archivo.
- Uso de `fgetcsv()` sobre un stream en memoria (soporta campos entre comillas con delimitadores o saltos de línea incrustados), conversión de codificación con `core_text::convert()`, retirada de la marca BOM UTF-8.
- A partir de este incremento, el seguimiento fase a fase pasa a `docs/roadmap.md`/`docs/project-status.md` (decisión explícita: seguir ese roadmap tal cual, sin construir todavía las interfaces pendientes de cotutores, reasignación, alumnos sin tutor ni cohortes).

**Pruebas**
- PHPUnit: `csv_import_parser_service_test.php` (nuevo, 17 casos).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).
- Sin Behat en esta entrega (no hay interfaz que probar todavía).

**Fuera de alcance de esta versión** (resto de 3D): subida de archivo, previsualización en pantalla, resolución de identificadores contra usuarios reales, aplicación, informe, exportación, tarea ad hoc.

---

## 0.4.0 — 2026-07-23

**Modelo de operación y previsualización de asignación masiva desde cohortes** — Fase 3C.1 (sobre la Fase 3B.5A). Sin formulario, confirmación, ejecución real, cierre de ausentes, sustitución, tarea ad hoc ni exportación en este incremento — solo clasificación de lectura.
- Tabla nueva `local_tut_bulkoperation` (identidad + parámetros + resumen agregado). **Decisión explícita de no crear una tabla de elementos por alumno**: el detalle de una previsualización se recalcula siempre en el momento en vez de persistirse, evitando tanto el problema de "caducidad" (una lista guardada solo puede quedar desincronizada) como la retención indefinida de datos por alumno que nunca llegaron a ejecutarse.
- `cohort_assignment_preview_service::preview()`: clasifica cada miembro de una cohorte Moodle frente a un tutor principal (y cotutor opcional) propuestos para un curso académico, con la misma semántica de vigencia usada en todo el proyecto desde la Fase 3A. Reutiliza sin cambios `cohort_membership_repository`/`assignment_repository::find_primary_rows_for_students()`/`get_cotutors_for_students()` (de las Fases 3B.1/3B.5), y cuatro validaciones de `assignment_service` que pasan de `private` a `public` para evitar duplicarlas.
- Acciones por alumno (`cohort_assignment_action`, 10 códigos): acción principal (tutor) y acción de cotutor independientes para el mismo alumno.
- `add_and_close_missing` identifica, en una consulta aparte, las asignaciones `source=cohort` de esa cohorte/curso cuyo alumno ya no es miembro — nunca asignaciones manuales, de otra cohorte o de otro curso, y funciona incluso si la cohorte se ha quedado sin miembros.
- Caducidad sin tabla de detalle: `is_expired()` por antigüedad, `has_changed_since_preview()` recalcula y compara el resumen agregado contra el guardado.
- Evento nuevo `cohort_assignment_previewed`, con recuentos agregados únicamente — nunca la lista de alumnos.
- Privacy API: nueva entrada de metadatos para `local_tut_bulkoperation` — footprint mucho más ligero que `local_tut_assignment`, ya que esta tabla nunca almacena datos por alumno.

**Pruebas**
- PHPUnit: `bulk_operation_repository_test.php` (nuevo), `cohort_assignment_preview_service_test.php` (nuevo, 20 casos: los 13 escenarios de previsualización del prompt incluidos los 4 modos, más caducidad, cambio detectado y validaciones), `cohort_assignment_previewed_test.php` (nuevo).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin) y validación XML de `install.xml`.
- Sin Behat en esta entrega (no hay interfaz que probar todavía).

**Fuera de alcance de esta versión** (resto de 3C): formulario web, tabla de previsualización en pantalla, exclusión manual, confirmación, ejecución real, cierre de ausentes efectivo, sustitución efectiva, informe de resultados, exportación, tarea ad hoc, locking/concurrencia de ejecución, reintento, cancelación.

---

## 0.3.5 — 2026-07-23

**Servicio de detección de alumnos sin tutor principal** — Fase 3B.5A (sobre la Fase 3B.4A). Sin interfaz, filtros, exportación ni migración de esquema en este incremento.
- `unassigned_students_service::search()`/`count()`/`get_coverage_summary()`: dado un conjunto de cohortes Moodle, un curso académico y una fecha de referencia (por defecto ahora), clasifica cada alumno del universo como con/sin tutor principal vigente — misma semántica temporal que `scope_service`/la reasignación de 3B.4.
- Nuevo repositorio `cohort_membership_repository` (solo `cohort_members`/`user`, nunca referencia `local_tut_assignment`) y nuevo `assignment_repository::find_primary_rows_for_students()`.
- Clasificación completa en PHP tras exactamente 3 consultas fijas (no crece con el tamaño de la población) — decisión deliberada para mantener el criterio de "sin joins entre tablas" ya establecido en el repositorio; documentado como válido hasta unos miles de alumnos, no diseñado para poblaciones mucho mayores sin revisar el enfoque.
- Estados (`unassigned_status_code`): sin asignación, anterior cerrada, futura pendiente, activa pero fuera de vigencia, y conflicto de datos (prioritario sobre los demás).
- Conflictos (`assignment_conflict_code`): dos principales vigentes a la vez, dos futuras solapadas, solapamiento entre cerradas históricas, tutor con cuenta eliminada en una fila activa. Un tutor de otro curso académico no necesita detección propia: al filtrar por curso, esas filas no aparecen y el alumno se clasifica correctamente como "nunca asignado" para ese curso.
- Límite de ámbito documentado explícitamente: el modelo de ámbitos actual no permite restringir cohortes por coordinador (no existe ese concepto en el sistema) — se deja como limitación conocida en vez de simular un aislamiento inexistente.

**Pruebas**
- PHPUnit: `cohort_membership_repository_test.php` (nuevo), ampliación de `assignment_repository_test.php`, y `unassigned_students_service_test.php` (nuevo, 18 casos: los 10 escenarios de detección y los 4 de conflicto pedidos, más cobertura, paginación, población vacía y curso académico inválido).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).
- Sin Behat en esta entrega (no hay interfaz que probar todavía).

**Fuera de alcance de esta versión** (resto de 3B.5): interfaz, resumen visual, filtros interactivos, exportación CSV, acción "Asignar tutor" desde el informe.

---

## 0.3.4 — 2026-07-23

**Servicio transaccional de reasignación** — Fase 3B.4A (sobre la Fase 3B.3A). Sin interfaz ni migración de esquema en este incremento.
- `assignment_service::reassign_primary_tutor(reassign_assignment_command $command, int $actorid): assignment_reassignment_result` reemplaza al antiguo `reassign()` (sin otros consumidores, no había página que lo usara todavía). Sigue cerrando la principal vigente y creando la nueva dentro de una única transacción; añade motivo de reasignación codificado (`assignment_reassign_reason`, 9 valores), rechazo de fecha efectiva anterior al inicio de la asignación anterior, y una comprobación de concurrencia: la fila se relee desde la base de datos justo antes de escribir, dentro de la transacción, y si su estado o tutor ya no coinciden con lo validado, la operación aborta sin cambiar nada.
- Nuevos DTOs inmutables `reassign_assignment_command`/`assignment_reassignment_result`: el comando nunca acepta el id ni el tutor de la asignación anterior (el servicio los busca él mismo), y el resultado expone ambos ids, ambos tutores, la fecha efectiva y los cotutores mantenidos/cerrados.
- Evento `student_reassigned` ampliado con `reassignreason` y `closedcotutorids`. El motivo se registra solo en el evento, no en la tabla — mismo criterio que el motivo de edición de 3B.2; se revisará si 3B.4B necesita mostrarlo en el historial.
- Sin tabla de auditoría paralela, sin capacidades nuevas (`reassignstudents` ya existía desde 3A; la comprobación de capacidad en una página queda para 3B.4B).

**Pruebas**
- PHPUnit: los casos de reasignación de 3A se adaptaron a la nueva firma; nuevos: motivo inválido, mantener/cerrar cotutores (verificando el DTO de resultado), y un caso de concurrencia con un repositorio de prueba que simula una lectura obsoleta para comprobar que la relectura dentro de la transacción detecta el conflicto y no crea una segunda asignación principal.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).
- Sin Behat en esta entrega (no hay interfaz que probar todavía).

**Fuera de alcance de esta versión** (resto de 3B.4, y toda la Fase 3B.3 salvo el cierre): formulario de reasignación, previsualización/confirmación en pantalla, gestión de cotutores como funcionalidad propia, informe de alumnos sin tutor.

---

## 0.3.3 — 2026-07-23

**Cierre de asignaciones** — Fase 3B.3A (sobre la Fase 3B.2)
- Campo nuevo `closereason` (motivo codificado de cierre) en `local_tut_assignment`, uno de 9 valores fijos (`assignment_close_reason`): cambio de tutor, cambio de grupo, cambio de nivel, fin de curso académico, baja del alumno, baja del tutor, error administrativo, fin de apoyo/cotutoría, otro. **Cuarta migración de esquema real de este proyecto** (`db/upgrade.php` con `$dbman->add_field()`).
- `assignment_service::close()` ampliado: exige un motivo válido, rechaza cerrar una asignación ya cerrada/cancelada, rechaza una fecha de cierre anterior a `timestart`, permite sobrescribir la observación administrativa, y calcula `leftwithoutprimary` (si al cerrar el tutor principal vigente el alumno queda sin ninguno activo) — sin crear ni reasignar automáticamente un reemplazo, eso queda para una fase posterior.
- `assignments/close.php` + `classes/form/assignment_close_form.php`: resumen de la asignación (alumno, tutor, tipo, curso, inicio), advertencia explícita si se va a cerrar el tutor principal, motivo, fecha efectiva, observación opcional y checkbox de confirmación obligatorio. Requiere `local/monlaututoria:manageassignments` + `scope_service` (mismo patrón de defensa en profundidad que `edit.php`). Rechaza cerrar filas `co_tutor` — esas se retiran desde la gestión de cotutores, todavía sin implementar.
- Acción "Cerrar" añadida al listado y al detalle (solo para asignaciones activas que no sean de tipo cotutor); el detalle ahora muestra el motivo de cierre cuando la asignación está cerrada.
- Evento `assignment_closed` ampliado con `closereason` y `leftwithoutprimary`. Sin tabla de auditoría paralela — Events API + `logstore_standard`, mismo criterio de siempre.
- Privacy API: `closereason` añadido a los metadatos ya declarados de `local_tut_assignment` (sigue sin export/borrado, mismo alcance reducido documentado).

**Pruebas**
- PHPUnit: nuevos casos en `assignment_repository_test.php` (persistencia de `closereason`/`note` al cerrar), `assignment_service_test.php` (motivo inválido, fecha anterior al inicio, persistencia de motivo/nota, doble cierre) y `assignment_events_test.php` (contenido de `assignment_closed`, incluyendo `leftwithoutprimary` en ambos casos).
- Behat: `assignment_close.feature` (cierre de secundaria, cierre de principal con advertencia y mensaje de "sin tutor principal", doble cierre rechazado, sin permiso) y `assignment_close_locale_es.feature`/`assignment_close_locale_ca.feature`.
  - ⚠️ Ni PHPUnit ni Behat se han ejecutado todavía en este entorno; solo se ha validado la sintaxis PHP (`php -l`, 0 errores en todo el plugin).

**Fuera de alcance de esta versión** (resto de 3B.3, y 3B.4-3B.5): gestión de cotutores (añadir/retirar/consultar), reasignación de tutor principal, informe de alumnos sin tutor.

> Pendiente de que el usuario ejecute la actualización real (`db/upgrade.php`) y las pruebas PHPUnit/Behat en su Moodle 5.1 de pruebas.

---

## 0.3.2 — 2026-07-23

**Confirmación de Fase 3B.1** — el usuario probó manualmente la interfaz de listado/detalle contra su Moodle 5.1 real: el selector de alumno/tutor con autocompletar (`core_user/form_user_selector`) funciona correctamente, el listado vacío muestra el mensaje esperado y el menú "Asignaciones" aparece en *Plugins*. Ya no es un punto abierto.

**Creación y edición manual de asignaciones** — Fase 3B.2 (sobre la Fase 3B.1)
- Campo nuevo `note` (observación administrativa opcional) en `local_tut_assignment`. **Primera migración de esquema de este proyecto ejecutada contra una instalación ya viva**: `db/upgrade.php` añade un bloque `if ($oldversion < 2026072500)` con `$dbman->add_field()` guardado por `field_exists()`, además de `install.xml`.
- `assignment_service::update()` (nuevo): edita únicamente `cohortid`, `timestart`, `timeend`, `note`. Nunca `studentid`/`tutorid` (romperían el historial — para eso está la reasignación, fuera de esta fase) ni `assignmenttype`/`isprimary`/`status` (cambiar de tipo o cerrar son flujos propios). Reutiliza los validadores privados existentes (`validate_dates()`, `validate_cohort()`, `validate_academic_year()`) sin duplicar lógica.
- `assignment_repository::update_editable_fields()` (nuevo): garantía estructural de que los campos protegidos nunca se leen del payload de entrada, se envíen o no.
- `assignments/create.php` + `classes/form/assignment_form.php`: formulario de creación completo (alumno/tutor vía autocomplete AJAX, curso académico, cohorte, tipo, tutor principal, fechas, observación). Requiere `local/monlaututoria:assignstudents`.
- `assignments/edit.php` + `classes/form/assignment_edit_form.php`: formulario de edición — alumno/tutor se muestran como texto estático, nunca como campos editables. Requiere `local/monlaututoria:manageassignments`, más `manageclosedassignments` si la asignación no está activa, más `scope_service` (defensa en profundidad deliberada: un rol necesita `manageassignments` + ámbito sobre el alumno para poder editar).
- Botón "Nueva asignación" en `assignments/index.php`; acción "Editar" añadida al listado y al detalle (`assignment_summary.mustache`, `assignment_detail.mustache`).
- Evento nuevo: `assignment_updated`. La auditoría pedida se resuelve con Events API + `logstore_standard`, sin tabla de auditoría paralela (mismo criterio que fases anteriores).
- Privacy API: `note` añadido a los metadatos ya declarados de `local_tut_assignment` (sigue sin export/borrado, mismo alcance reducido documentado).

**Pruebas**
- PHPUnit: ampliados `assignment_repository_test.php`, `assignment_service_test.php`, `assignment_events_test.php` con los casos de `update()`.
- Behat: **primeros escenarios de este proyecto** — `assignment_create.feature`, `assignment_edit.feature`, `assignment_locale_es.feature`, `assignment_locale_ca.feature`.
  - ⚠️ Ni PHPUnit ni Behat se han ejecutado todavía en este entorno; solo se ha validado la sintaxis PHP (`php -l`, 0 errores).

**Fuera de alcance de esta versión** (sub-fases 3B.3-3B.5): cierre, cotutores, reasignación, informe de alumnos sin tutor, cohortes, CSV.

**Cierre de huecos (gap analysis) tras contrastar contra el prompt detallado de la fase:**
- `assignments/create.php` ahora propaga la capacidad `overridelock` al servicio (antes no se podía crear una asignación en un curso académico bloqueado ni con esa capacidad).
- Editar una asignación cerrada o cancelada exige ahora también un **motivo** (`reason`) no vacío, además de `manageclosedassignments`. Se registra en el evento `assignment_updated`, no en la tabla.
- `assignment_updated` incluye ahora `fieldschanged` (qué campos cambiaron) en su contenido; el texto de `note` nunca se incluye en el evento, solo si cambió o no.

> Pendiente de que el usuario ejecute la actualización real (`db/upgrade.php`) y las pruebas PHPUnit/Behat en su Moodle 5.1 de pruebas.

---

## 0.3.1 — 2026-07-23

**Interfaz de asignaciones** — Fase 3B.1: listado y detalle (sobre la Fase 3A, ya instalada y validada en un Moodle 5.1 de pruebas real)
- `assignments/index.php`: listado paginado con filtros (curso académico, tutor, alumno, cohorte, tipo, estado, origen, rango de fechas de inicio/fin). Ámbito por capacidad: `viewallassignments` ve toda la tabla; `viewownstudents` solo ve sus propios alumnos, forzado en el servidor (no manipulable por URL).
- `assignments/view.php`: detalle de una asignación + historial básico del alumno (más reciente primero, distingue vigente/futura/cerrada/cancelada). Protegido por `local/monlaututoria:viewstudent` (primer consumidor real de esta capacidad, definida en 3A) + `scope_service`.
- Nueva sección "Asignaciones" en *Administración del sitio → Plugins → Monlau Tutoria*.

**Repositorio y evento**
- `assignment_repository::search()`, `count_search()`, `get_cotutors_for_students()` — nuevos, con ordenación por lista blanca de columnas (nunca interpola el valor recibido) y patrón "batch fetch tras paginar" para evitar N+1 sobre `user`/`cohort`/cursos académicos/cotutores.
- Evento nuevo: `assignment_viewed`.

**Renderizado**
- Primer uso de Mustache en este plugin: 6 plantillas nuevas (`assignment_status`, `assignment_summary`, `assignments_list`, `assignment_detail`, `assignment_history`, `empty_state`). La Fase 2 sigue usando `html_writer` puro, sin modificar.
- `assignment_filter_form` (Forms API, método GET para que los filtros persistan en la URL).

**Pruebas**
- PHPUnit: `search()`/`count_search()`/`get_cotutors_for_students()` (extendiendo `assignment_repository_test.php`), evento `assignment_viewed` (extendiendo `assignment_events_test.php`).
  - ⚠️ Todavía sin ejecutar de verdad en este entorno de desarrollo; ya hay Moodle de pruebas disponible para hacerlo.
- Sin Behat en esta entrega (no pedido para 3B.1).

**Punto abierto, no verificado con certeza:** el selector de alumno/tutor en `assignment_filter_form` usa `'ajax' => 'core_user/form_user_selector'`. Pendiente de confirmar manualmente contra el Moodle 5.1 real; si no funciona, alternativa de respaldo documentada en `docs/plan-desarrollo.md`.

**Fuera de alcance de esta versión** (sub-fases 3B.2-3B.5): creación, edición, cierre y reasignación de asignaciones, gestión de cotutores, informe de alumnos sin tutor.

---

## 0.3.0 — 2026-07-22

**Asignaciones tutor-alumno** (`local_tut_assignment`) — Fase 3A: modelo y servicios básicos (sin interfaz todavía)
- `assignment_service`: crear, cerrar, reasignar (atómico, un único evento `student_reassigned`), añadir/quitar cotutor.
- Reglas: alumno ≠ tutor; sin duplicados activos; tutor principal único por alumno y curso (incondicional, sin panel de configuración todavía); usuario eliminado siempre bloqueante; usuario suspendido bloqueado salvo override explícito; curso académico bloqueado bloqueante salvo `overridelock`.
- `scope_service`: punto único de control de acceso alumno a alumno (capacidad + ámbito + asignación **vigente**, distinta de solo "activa"). Ver `docs/seguridad-permisos.md`.

**Seguridad y trazabilidad**
- 10 capacidades nuevas (deny-by-default): `viewownstudents`, `viewstudent`, `viewhistoricalassignments`, `assignstudents`, `manageassignments`, `managecohortassignments`, `importassignments`, `reassignstudents`, `viewallassignments`, `manageclosedassignments`.
- 5 eventos: `assignment_created`, `assignment_closed`, `student_reassigned`, `co_tutor_added`, `co_tutor_removed`.
- Privacy API: metadatos de la nueva tabla añadidos a `classes/privacy/provider.php`; export/borrado deliberadamente NO implementados todavía (a diferencia de los catálogos, aquí la fila entera es dato personal — pendiente de política institucional de conservación, ver `docs/modelo-datos.md`).

**Pruebas**
- PHPUnit: repositorio, `assignment_service`, `scope_service`, eventos, capacidades (extendiendo `access_test.php` existente).
  - ⚠️ Ninguna se ha podido ejecutar en este entorno de desarrollo (no hay instancia Moodle disponible).
- Sin Behat en esta entrega (no hay interfaz que probar todavía).

**Fuera de alcance de esta versión** (Fases 3B-3E del prompt original): páginas de administración de asignaciones, asignación desde cohortes, importación CSV, tareas programadas, servicios externos/AJAX.

---

## 0.2.0 — 2026-07-22

**Cursos académicos** (`local_tut_academicyear`)
- CRUD completo.
- Activación exclusiva transaccional (desactiva el curso previamente activo).
- Bloqueo con capacidad de anulación (`local/monlaututoria:overridelock`).
- Guard de borrado: bloquea si está activo, bloqueado, o referenciado por datos de fases futuras.

**Catálogos configurables**
- Motivos de tutoría (`local_tut_reason`) — 20 valores semilla.
- Modalidades de contacto (`local_tut_modality`) — 7 valores semilla.
- Siembra idempotente vía `db/install.php` / `db/upgrade.php` a partir de cadenas de idioma (nunca texto hardcodeado).

**Seguridad y trazabilidad**
- Capacidades nuevas (todas deny-by-default): `local/monlaututoria:manageacademicyears`, `local/monlaututoria:managecatalogues`, `local/monlaututoria:viewconfiguration`, `local/monlaututoria:overridelock`.
- 10 clases de evento: creación/actualización/activación/bloqueo de curso académico; creación/actualización/activación de motivos y modalidades.
- Privacy API mínima (`classes/privacy/provider.php`) para los campos `createdby`/`modifiedby` de las 3 tablas nuevas.

**Interfaz y pruebas**
- Páginas de administración CRUD para las 3 entidades, registradas en *Site administration*.
- Cadenas de idioma en/es/ca completas para toda la funcionalidad de esta fase.
- Pruebas PHPUnit (repositorios, servicios, eventos, capacidades, siembra) y escenarios Behat.
  - ⚠️ Ninguna se ha podido ejecutar en este entorno de desarrollo (no hay instancia Moodle disponible).

**Fuera de alcance de esta versión:** asignaciones tutor-alumno, sincronización de cohortes, ficha del alumno, registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones.

---

## 0.1.0 — 2026-07-22

- Esqueleto inicial instalable del plugin: `version.php`, `lib.php`, `db/access.php` con la capacidad *placeholder* `local/monlaututoria:view`, `db/install.xml` sin tablas, cadenas de idioma en/es/ca, prueba PHPUnit de humo (`tests/plugin_test.php`).
- Sin lógica de negocio: no incluye asignaciones, tutorías, acuerdos, seguimientos ni dashboards.
