# Changelog — local_monlaututoria

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
