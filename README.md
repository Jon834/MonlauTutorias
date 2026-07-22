# local_monlaututoria

**Versión:** 0.3.0 · **Moodle:** 5.1.x (pendiente de verificar) · **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignación de alumnos a tutores, registro de tutorías, historial entre cursos académicos, y separación entre contenido visible para el alumno y notas internas.

Toda la lógica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resúmenes y accesos rápidos.

## Estado del proyecto

**Fase 3A — Modelo y servicios básicos de asignación tutor-alumno.** ✅ Completada a nivel de código (primer incremento de un prompt de Fase 3 más amplio; 3B-3E siguen pendientes).

Esta versión (0.3.0) añade:

| Área | Contenido |
|---|---|
| Asignaciones | `local_tut_assignment`: `assignment_service` (crear, cerrar, reasignar, cotutores) y `scope_service` (control de acceso alumno a alumno) |
| Capacidades | 10 nuevas: `viewownstudents`, `viewstudent`, `viewhistoricalassignments`, `assignstudents`, `manageassignments`, `managecohortassignments`, `importassignments`, `reassignstudents`, `viewallassignments`, `manageclosedassignments` |
| Eventos | `assignment_created`, `assignment_closed`, `student_reassigned`, `co_tutor_added`, `co_tutor_removed` |
| Privacy API | Metadatos de la nueva tabla añadidos; export/borrado pendientes de política institucional (ver `docs/modelo-datos.md`) |

Versión previa (0.2.0 — Fase 2): cursos académicos y catálogos de motivos/modalidades.

**Todavía sin implementar:** páginas de administración de asignaciones, asignación desde cohortes, importación CSV, ficha del alumno, registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones.

Ver [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) en la raíz del repositorio para el roadmap completo.

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) es un *placeholder* pendiente de verificar contra el build real del core (ver comentario en el propio archivo) — no se ha confirmado todavía porque el entorno Moodle local (fase "Entorno local" del plan de desarrollo) aún no está implementado.
- PHP según los requisitos de Moodle 5.1.

## Instalación

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *Administración del sitio → Notificaciones* para completar la instalación, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** no verificado en un Moodle real en este entorno de desarrollo (no hay instancia disponible); solo se ha validado la sintaxis PHP de cada archivo.

## Versiones compatibles

| Versión del plugin | Moodle |
|---|---|
| 0.3.0 | 5.1.x (pendiente de verificación) |
| 0.2.0 | 5.1.x (pendiente de verificación) |
| 0.1.0 | 5.1.x (pendiente de verificación) |

## Licencia

GNU GPL v3 o posterior, la misma licencia que Moodle.
