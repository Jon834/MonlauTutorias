# local_monlaututoria

**Versión:** 0.4.2 · **Moodle:** 5.1.x (instalación verificada ✅) · **Licencia:** GPL v3+

## Objetivo

Plugin de Moodle que da soporte al sistema longitudinal de seguimiento tutorial de Monlau: asignación de alumnos a tutores, registro de tutorías, historial entre cursos académicos, y separación entre contenido visible para el alumno y notas internas.

Toda la lógica de negocio reside en este plugin. El bloque complementario `block_monlaututoria` solo muestra resúmenes y accesos rápidos.

## Estado del proyecto

**Fase 3D.2 — Subida y previsualización de importación CSV.** ✅ Completada a nivel de código, sobre la Fase 3D.1 (parser). Sin aplicación real todavía — solo subida (área de borrador de Moodle) + previsualización contra la base de datos, probadas por PHPUnit/Behat.

> A partir de la Fase 3D.1, el seguimiento detallado usa `docs/roadmap.md`/`docs/project-status.md` en la raíz del repositorio (decisión explícita: se sigue ese roadmap, que no incluye todavía las interfaces pendientes de cotutores, reasignación, alumnos sin tutor ni cohortes).

Esta versión (0.4.2) añade:

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

Versiones previas: 0.3.5 (Fase 3B.5A — servicio de detección de alumnos sin tutor), 0.3.4 (Fase 3B.4A — servicio de reasignación), 0.3.3 (Fase 3B.3A — cierre de asignaciones), 0.3.2 (Fase 3B.2 — creación y edición manual), 0.3.1 (Fase 3B.1 — listado y detalle, confirmada en Moodle real), 0.3.0 (Fase 3A — modelo y servicios de asignación), 0.2.0 (Fase 2 — cursos académicos y catálogos).

**Todavía sin implementar:** aplicación/informe/exportación de la importación CSV (solo subida+previsualización existen), interfaz de asignación masiva desde cohortes (formulario, previsualización en pantalla, confirmación, ejecución, cierre de ausentes, sustitución), interfaz del informe de alumnos sin tutor, formulario e interfaz de reasignación, gestión de cotutores como funcionalidad propia, ficha del alumno, registro de tutorías, acuerdos, seguimientos, dashboards, notificaciones, derivaciones.

Ver [`docs/roadmap.md`](../../docs/roadmap.md) y [`docs/project-status.md`](../../docs/project-status.md) en la raíz del repositorio para el roadmap y estado actuales; [`docs/plan-desarrollo.md`](../../docs/plan-desarrollo.md) recoge la narrativa detallada de las fases 1-3D.1.

## Requisitos

- **Moodle 5.1.x.** El valor de `$plugin->requires` en [`version.php`](version.php) ya se comprobó compatible al instalar correctamente en un Moodle 5.1 de pruebas real; sigue pendiente ajustarlo al número exacto del core (no bloqueante).
- PHP según los requisitos de Moodle 5.1.

## Instalación

1. Copiar/enlazar este directorio en `<moodle>/local/monlaututoria`.
2. Visitar *Administración del sitio → Notificaciones* para completar la instalación, o ejecutar `php admin/cli/upgrade.php`.

> **Nota:** verificado en un Moodle 5.1 de pruebas real hasta la Fase 3B.1 inclusive (incluido el selector AJAX de usuario). Las actualizaciones de esquema y la interfaz de las Fases 3B.2, 3B.3A, 3C.1 y 3D.2, y los servicios sin interfaz de 3B.4A/3B.5A (esta versión), todavía no se han probado manualmente en esa instancia — solo se ha validado la sintaxis PHP.

## Versiones compatibles

| Versión del plugin | Moodle |
|---|---|
| 0.4.2 | 5.1.x (instalación verificada hasta 3B.1 inclusive; resto pendiente — 3B.2/3B.3A/3C.1/3D.2 tienen migración de esquema pendiente de `db/upgrade.php`) |
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
