# local_monlaututoria

**Versión:** 0.3.4 · **Moodle:** 5.1.x (instalación verificada ✅) · **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignación de alumnos a tutores, registro de tutorías, historial entre cursos académicos, y separación entre contenido visible para el alumno y notas internas.

Toda la lógica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resúmenes y accesos rápidos.

## Estado del proyecto

**Fase 3B.4A — Servicio transaccional de reasignación de tutor principal.** ✅ Completada a nivel de código, sobre la Fase 3B.3A (cierre de asignaciones). Sin interfaz todavía — solo el servicio de dominio, probado por PHPUnit.

Esta versión (0.3.4) añade:

| Área | Contenido |
|---|---|
| Servicio | `assignment_service::reassign_primary_tutor(reassign_assignment_command, actorid): assignment_reassignment_result` — reemplaza al `reassign()` de la Fase 3A (sin otros consumidores todavía) |
| Motivo codificado | `assignment_reassign_reason` (9 valores); se registra solo en el evento, no en la tabla |
| Concurrencia | Relectura del estado dentro de la transacción, justo antes de escribir; aborta sin cambios si detecta que otra operación ya actuó sobre la misma fila |
| Eventos | `student_reassigned` ampliado con `reassignreason` y `closedcotutorids` |
| Cotutores | Se pueden mantener (por defecto) o cerrar como parte de la misma operación atómica |
| Pruebas | PHPUnit (reasignación válida, motivo inválido, mantener/cerrar cotutores, conflicto de concurrencia simulado) |

Versiones previas: 0.3.3 (Fase 3B.3A — cierre de asignaciones), 0.3.2 (Fase 3B.2 — creación y edición manual), 0.3.1 (Fase 3B.1 — listado y detalle, confirmada en Moodle real), 0.3.0 (Fase 3A — modelo y servicios de asignación), 0.2.0 (Fase 2 — cursos académicos y catálogos).

**Todavía sin implementar:** formulario e interfaz de reasignación, gestión de cotutores como funcionalidad propia (añadir/retirar/consultar), informe de alumnos sin tutor, asignación desde cohortes, importación CSV, ficha del alumno, registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones.

Ver [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) en la raíz del repositorio para el roadmap completo.

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) ya se comprobó compatible al instalar correctamente en un Moodle 5.1 de pruebas real; sigue pendiente ajustarlo al número exacto del core (no bloqueante).
- PHP según los requisitos de Moodle 5.1.

## Instalación

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *Administración del sitio → Notificaciones* para completar la instalación, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** verificado en un Moodle 5.1 de pruebas real hasta la Fase 3B.1 inclusive (incluido el selector AJAX de usuario). Las actualizaciones de esquema y la interfaz de las Fases 3B.2 y 3B.3A, y el servicio de reasignación de 3B.4A (esta versión, sin interfaz propia), todavía no se han probado manualmente en esa instancia — solo se ha validado la sintaxis PHP.

## Versiones compatibles

| Versión del plugin | Moodle |
|---|---|
| 0.3.4 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A/3B.4A pendientes de probar en el navegador y, para 3B.2/3B.3A, `db/upgrade.php`) |
| 0.3.3 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2/3B.3A pendientes de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.2 | 5.1.x (instalación verificada hasta 3B.1 inclusive; 3B.2 pendiente de probar `db/upgrade.php` y la interfaz en el navegador) |
| 0.3.1 | 5.1.x (instalación verificada ✅, incluida la interfaz de listado/detalle y el selector AJAX) |
| 0.3.0 | 5.1.x (instalación verificada ✅) |
| 0.2.0 | 5.1.x (pendiente de verificación) |
| 0.1.0 | 5.1.x (pendiente de verificación) |

## Licencia

GNU GPL v3 o posterior, la misma licencia que Moodle.
