# Changelog — local_monlaututoria

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
