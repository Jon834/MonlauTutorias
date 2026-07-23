# local_monlaututoria

**Versión:** 0.3.1 · **Moodle:** 5.1.x (instalación verificada ✅) · **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignación de alumnos a tutores, registro de tutorías, historial entre cursos académicos, y separación entre contenido visible para el alumno y notas internas.

Toda la lógica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resúmenes y accesos rápidos.

## Estado del proyecto

**Fase 3B.1 — Listado y detalle de asignaciones.** ✅ Completada a nivel de código, sobre la Fase 3A (modelo y servicios) ya instalada y validada en un Moodle 5.1 de pruebas real.

Esta versión (0.3.1) añade:

| Área | Contenido |
|---|---|
| Interfaz de asignaciones | `assignments/index.php` (listado paginado + filtros, con ámbito por capacidad) y `assignments/view.php` (detalle + historial básico), en *Administración del sitio → Plugins → Monlau Tutoria → Asignaciones* |
| Repositorio | `assignment_repository::search()`/`count_search()`/`get_cotutors_for_students()`, con patrón "batch fetch tras paginar" para evitar N+1 |
| Eventos | `assignment_viewed` (nuevo) |
| Renderizado | Primer uso de Mustache en este plugin (6 plantillas nuevas); Fase 2 usa `html_writer` puro, sin tocar |
| Capacidades | `local/monlaututoria:viewstudent` obtiene su primer consumidor real |

Versiones previas: 0.3.0 (Fase 3A — modelo y servicios de asignación), 0.2.0 (Fase 2 — cursos académicos y catálogos).

**Todavía sin implementar:** creación, edición, cierre y reasignación de asignaciones, gestión de cotutores, informe de alumnos sin tutor, asignación desde cohortes, importación CSV, ficha del alumno, registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones.

**Punto abierto:** el selector de alumno/tutor con autocompletar (`assignment_filter_form`) usa `core_user/form_user_selector` — no verificado con certeza al 100% contra Moodle 5.1; confirmar manualmente en la instancia real.

Ver [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) en la raíz del repositorio para el roadmap completo.

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) ya se comprobó compatible al instalar correctamente en un Moodle 5.1 de pruebas real; sigue pendiente ajustarlo al número exacto del core (no bloqueante).
- PHP según los requisitos de Moodle 5.1.

## Instalación

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *Administración del sitio → Notificaciones* para completar la instalación, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** verificado en un Moodle 5.1 de pruebas real hasta la Fase 3A inclusive. La interfaz de la Fase 3B.1 (esta versión) todavía no se ha probado manualmente en esa instancia — solo se ha validado la sintaxis PHP.

## Versiones compatibles

| Versión del plugin | Moodle |
|---|---|
| 0.3.1 | 5.1.x (instalación verificada hasta 3A; 3B.1 pendiente de probar en el navegador) |
| 0.3.0 | 5.1.x (instalación verificada ✅) |
| 0.2.0 | 5.1.x (pendiente de verificación) |
| 0.1.0 | 5.1.x (pendiente de verificación) |

## Licencia

GNU GPL v3 o posterior, la misma licencia que Moodle.
