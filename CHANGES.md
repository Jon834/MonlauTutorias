# Changelog — local_monlaututoria

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
