# Changelog — local_monlaututoria

## 0.14.1 — 2026-09-02

**Fase 14 (S2) — remates del Orientador SOP + indicador de adjuntos.**

- **Icono de adjunto** (clip) en el listado de tutorías de la ficha: cualquier tutoría con archivos adjuntos lo muestra junto a la fecha. Nuevo `entry_attachment_repository::entry_ids_with_attachments()`.
- **Badge "SOP"** también en el listado (antes solo en el detalle).
- **Editar una tutoría SOP**: `entry_edit_form` con `sop` — sin "comentario compartido", "Observaciones SOP" + "Recomendaciones SOP", categorías de adjunto SOP. `entry_repository::update_editable_fields()` persiste `recommendationsop`.
- Ficha del alumno (Resumen): en modo simple, **"Cotutores" → "Orientador SOP"**.
- `version.php` → `2026091710` / `0.14.1`.

---

## 0.14.0 — 2026-09-02

**Fase 14 (S1) — Orientador SOP.** Solo en modo simple. Ver `docs/fases/phase-14-sop.md`.

- El **cotutor** pasa a ser el **Orientador SOP**: el formulario de asignación en modo simple ofrece solo "Tutor principal" y "Orientador SOP" (`assignment_type::get_simple_options()`).
- **Esquema** (`local_tut_entry`, upgrade `2026091709`): `entrykind` (`regular`\|`sop`) y `recommendationsop` (texto).
- **Tutoría SOP**: página nueva `entries/create_sop.php` + `entry_sop_form` (fecha, modalidad, motivo, Observaciones SOP, **Recomendaciones SOP**, adjuntos). Sin comentario compartido con el alumno.
- **Visibilidad**: `entry_service` excluye por completo las tutorías SOP para el alumno (lista y detalle). Cualquier tutor asignado vigente (principal o SOP) + coordinación las ven completas.
- **Quién registra SOP**: solo el orientador SOP del alumno o coordinación (validado en `entry_service::create()` y en la página). El tutor principal ve el botón "Registrar tutoría" normal; el orientador SOP ve "Registrar tutoría SOP".
- **Adjuntos SOP**: dos categorías nuevas — "Informes facilitados" (`sop_report`) y "Recomendaciones SOP" (`sop_recommendation`). `entries/attachments.php`, `entry_attachment_service` y `pluginfile` permiten los adjuntos de una tutoría SOP en modo simple sin la capacidad `viewinternalnotes` (mismo criterio que el contenido SOP). El alumno nunca los ve.
- **Panel del tutor**: nueva sección "Mis alumnos SOP" para el orientador.
- `version.php` → `2026091709` / `0.14.0`. Pruebas nuevas en `entry_service_test.php`. `php -l` correcto; PHPUnit/Behat sin ejecutar.
- Pendiente (S2): editar una tutoría SOP (el formulario de edición no tiene aún el campo Recomendaciones); relabel "Cotutores" → "Orientador SOP" en el Resumen de la ficha; badge SOP en el listado de tutorías.

---

## 0.13.8 — 2026-09-01

**Un tutor anterior puede consultar las tutorías que él hizo.**

- `scope_service`: un **tutor anterior** (asignación cerrada) tiene acceso — en modo simple sin capacidad, en modo completo con `viewhistoricalassignments` — pero **limitado a las tutorías que registró él mismo**. Nuevo `access_is_historical_only()`.
- `entry_service`: para un tutor anterior, el listado y el detalle de tutorías se restringen a `tutorid = él`; cualquier otra tutoría del alumno devuelve "acceso denegado".
- Ficha del alumno para un tutor anterior: solo la pestaña **Tutorías** (sus propias), vista limitada, sin botón de registrar, sin selector de curso que le bloquee entre años.
- Panel del tutor: nueva sección **"Alumnos que tutoricé antes"** (tarjetas atenuadas, "Tutoría finalizada") para poder llegar a esas fichas. Un tutor **solo** anterior también entra al panel en modo simple.
- **No** puede crear ni editar tutorías de un alumno que ya no tiene asignado (`user_is_current_tutor()` para eso).
- `assignment_repository`: `has_any_tutoring_ever()`, `has_any_current_tutoring()`, `find_historical_student_ids_by_tutor()`. Pruebas nuevas en `scope_service_test.php`. `version.php` → `2026091708` / `0.13.8`.

---

## 0.13.7 — 2026-09-01

**El tutor vigente de un alumno ve su ficha completa entre cursos.**

- `scope_service::can_user_access_student()`: ser el **tutor principal o cotutor vigente** de un alumno (en cualquier curso académico) da acceso a **toda** su ficha longitudinal — cursos anteriores y tutorías hechas por tutores anteriores incluidas. "El historial pertenece al alumno, no al curso" (`docs/requisitos-funcionales.md`).
- Antes, un tutor con asignación para el curso X solo veía el curso X salvo que tuviera `viewhistoricalassignments`. Un **tutor anterior** (asignación cerrada) sigue **sin** acceso salvo con esa capacidad — no cambia.
- Prueba nueva en `scope_service_test.php`. `version.php` → `2026091707` / `0.13.7`.

---

## 0.13.6 — 2026-09-01

**En modo simple, asignar alumnos a un profesor lo convierte en tutor — sin configurar roles.**

Feedback de uso real: se asignaron alumnos de tutoría a un profesor pero no le aparecía nada, porque una asignación no concede ninguna capacidad (eso viene de un rol). En **modo simple** esto ya no hace falta:

- Nuevo `scope_service::user_is_tutor()` + `assignment_repository::has_any_current_tutoring()`: en modo simple, ser tutor vigente de al menos un alumno equivale a la capacidad `viewownstudents`.
- `scope_service::can_user_access_student()`: en modo simple, ser tutor vigente de **ese** alumno da acceso, sin `viewownstudents`. El control sigue siendo estrecho — ese alumno concreto, asignación activa.
- El panel del tutor, el bloque, la navegación, "Mis tutorías", registrar y editar tutorías, y la ficha del alumno reconocen al tutor-por-asignación en modo simple.
- `entry_service::mask_content()`: en modo simple, quien pasa el control de ámbito (tutor de ese alumno o coordinación) ve el comentario compartido y la nota interna sin capacidad de lectura aparte. El alumno sigue sin ver nunca la nota interna.
- El alumno (sin asignaciones de tutor) sigue viendo solo "Mis tutorías". En **modo completo** nada cambia: todo sigue dependiendo de las capacidades.
- Pruebas nuevas en `scope_service_test.php`. `version.php` → `2026091706` / `0.13.6`.

---

## 0.13.5 — 2026-09-01

- Se oculta la tarjeta **"Contactos con familias"** del panel del tutor en modo simple: el dato viene de los participantes de una tutoría, que solo se registran en el formulario completo (oculto en modo simple), así que siempre valdría 0.
- `version.php` → `2026091705` / `0.13.5`.

---

## 0.13.4 — 2026-09-01

**Página "Ayuda" adaptada al modo simple.**

- En modo simple solo muestra el concepto **Tutoría** (fuera Acuerdo / Seguimiento / Derivación) y **"¿Qué ve el alumno?"**, con textos que ya no mencionan las 3 partes de visibilidad ni los conceptos ocultos: la tutoría tiene 2 partes (comentario compartido + nota interna).
- `version.php` → `2026091704` / `0.13.4`.

---

## 0.13.3 — 2026-09-01

**Tabla "Pendientes" del panel del tutor en modo simple (feedback de uso).**

- Se **quita la columna "Pendientes"** (Seg./Acu./Der.) — siempre 0/0/0 sin esos módulos.
- Se quita también la columna **"Sin tutoría inicial"** — es la misma información que "Estado de cobertura" (Sí ↔ Pendiente de primera tutoría).
- Tarjeta **"Cobertura"**: en modo simple muestra **"3 / 62"** (con tutoría / asignados) en vez de "4,84 %" — responde más directo a "¿ya tienen todos una tutoría?".
- `version.php` → `2026091703` / `0.13.3`.

---

## 0.13.2 — 2026-09-01

**Ajustes del panel del tutor en modo simple (feedback de uso).**

- Filtro de alumnos: en modo simple pasa a **Todos / Con tutoría / Sin tutoría** (antes: "sin tutoría inicial" + "con pendientes", este último inútil sin seguimientos/acuerdos). Nueva opción `covered` que filtra por `activeentrycount > 0`.
- Se **oculta el filtro "pendientes" (abiertos/vencidos)** en modo simple: solo afectaba a seguimientos y acuerdos.
- Roster: el nº de tutorías y la fecha de la última ya no se pegan (cada dato en su línea).
- `version.php` → `2026091702` / `0.13.2`.

---

## 0.13.1 — 2026-09-01

**Ajustes de la Fase 13 tras la primera prueba en Moodle real.**

- **El alumno no podía ver sus tutorías.** Paso nuevo en `db/upgrade.php` (`2026091701`) que reasigna `local/monlaututoria:viewownfile` (CAP_ALLOW) al rol de usuario autenticado — en algunas instalaciones el valor por defecto del arquetipo no había llegado a aplicarse o se había perdido. `assign_capability()` con el valor ya puesto es no-op, así que no pisa una personalización deliberada.
- **El tutor ya no ve "Asignaciones"** en modo simple: la navegación, la página `assignments/index.php`, la `admin_externalpage` y el enlace del bloque exigen ahora `viewallassignments` (coordinación) en modo simple, no `viewownstudents`. El tutor gestiona a sus alumnos desde el panel y la ficha.
- **Asignación de una cohorte entera a un tutor disponible en modo simple**: `assignments/cohort_create.php` deja de estar bajo `feature::IMPORTS` (solo la importación CSV sigue oculta). Sigue protegida por la capacidad `managecohortassignments`. En modo simple el formulario oculta el cotutor. El filtro por cohorte del listado vuelve a mostrarse en modo simple.
- `version.php` → `2026091701` / `0.13.1` (por encima del `2026091601` preexistente).

---

## 0.13.0 — 2026-09-01

**Fase 13 (S1–S5) — "modo simple", roster del tutor y manuales.** Nuevo ajuste de sitio `local_monlaututoria/simplemode` (checkbox, **desactivado por defecto** — una instalación existente no cambia sola). Con el modo activado se ocultan los módulos avanzados sin borrar nada: sus datos, servicios, tablas y pruebas quedan intactos, y desactivar la casilla lo restaura todo. Ver `docs/fases/phase-13-modo-simple.md`.

### S2 — Roster del tutor + ficha del alumno a lo esencial

- **Nueva vista "Mis alumnos"** en el panel del tutor (`dashboard.php`): rejilla de tarjetas con **foto** (`user_picture`), nombre e indicador *"N tutorías" / "Sin tutoría aún"* (texto, no solo color; icono `i/warning` en el estado pendiente). Toda la tarjeta enlaza a la ficha del alumno, pestaña "Tutorías". Nuevo `renderer::dashboard_student_roster()` + estilos `.local-monlaututoria-roster*`. El fetch de usuarios del panel ahora trae los campos de `user_picture` (`\core_user\fields::for_userpic()`).
- Conmutador de vista **Mis alumnos / Pendientes** (enlaces tipo pestaña, preferencia de usuario `local_monlaututoria_dashboard_view`). En modo simple la vista por defecto es el roster.
- En modo simple, el panel "Pendientes" oculta las secciones de seguimientos, acuerdos, derivaciones y prioridad (todo lo que depende de módulos ocultos); quedan las tarjetas de resumen, la tabla de alumnos y "Registrar tutoría".
- `renderer::student_tabs()` y `student/view.php`: las pestañas "Acuerdos" y "Seguimientos" de la ficha del alumno solo aparecen si esos módulos están activos; `tab=acuerdos|seguimientos` manipulado en la URL cae a "Resumen".
- Botón "Registrar (completo)" de la ficha oculto en modo simple (solo el rápido); sin campo de adjuntos en el formulario rápido.
- Enlaces a páginas ocultas (crear acuerdo/seguimiento/derivación y adjuntos en el detalle de tutoría; reasignar tutor y asignación por cohortes en asignaciones) ocultos cuando su módulo lo está.

### S5 — Documentación, navegación del alumno y Behat

- **Manuales paso a paso** en `docs/manuales/`: tutor, alumno, coordinación, admin técnico, + índice.
- `lib.php` → `local_monlaututoria_extend_navigation()`: añade **"Mis tutorías"** a la navegación plana para el alumno (su ficha limitada ya era accesible por capacidad `viewownfile` pero no había ningún enlace). No se muestra a usuarios con capacidades de tutor/coordinación.
- Behat: `simple_mode.feature` y `dashboard_roster.feature` (nuevos). Los `.feature` de módulos ocultos (`agreement_management`, `followup_management`, `referral_management`, `cohort_assignment`, `csv_import_preview`, `entry_attachments`, `entry_full_registration`) llevan el tag `@local_monlaututoria_advanced` para excluirlos del run por defecto en modo simple.

### S3 — Formulario de registro corto

- `entry_quick_form`: sin campo "próximo seguimiento" ni adjuntos en modo simple. `entries/create.php` fuerza `canupload=false`.
- `reason_form`: la visibilidad `RESTRICTED` no se ofrece en modo simple.
- `student/view.php` / `entries/view.php`: filtro y bloque de "nota restringida" condicionados a `RESTRICTEDNOTES`.

### S4 — Asignaciones simplificadas + tareas

- `assignment_form`: tipo fijo "principal" y cohorte oculta en modo simple. `assignment_filter_form`: sin filtros de tipo/origen/cohorte en modo simple.
- Acciones "Reasignar tutor" y "Asignación por cohortes" del listado/creación de asignaciones ocultas según `COTUTORS` / `IMPORTS`.
- Tareas `send_notification_reminders_task` / `retry_failed_notifications_task` / `dispatch_notification_task`: `execute()` no-op si `NOTIFICATIONS` está oculto (siguen registradas; reactivar el módulo no requiere upgrade). `cleanup_notification_logs_task` se deja activa.
- `dashboard_summary_cards`: omite las tarjetas de seguimientos/acuerdos cuando esos módulos están ocultos.

### S1 — Infraestructura + ocultar módulos de gestión avanzada

- Nuevo `classes/feature.php` (`\local_monlaututoria\feature`): interruptor único. `simple_mode()`, `enabled($feature)`, `require_enabled($feature)`. Funciones ocultas: acuerdos, seguimientos, derivaciones, coordinación (panel + ámbitos), notificaciones, importaciones (CSV + cohortes), cotutores/reasignación, adjuntos, registro completo y nota restringida.
- **Defensa en profundidad**: cada consumidor comprueba `feature::enabled()` para ocultar la interfaz, y además cada página oculta llama a `feature::require_enabled()` justo tras `require_login()` — manipular la URL devuelve `error_featuredisabled`, no la página.
- Guard añadido en 23 páginas: `referrals/*` (6), `coordination.php`, `coordination_export.php`, `coordination_scopes.php`, `notifications.php`, `cohort_visibility.php`, `assignments/import.php`, `assignments/import_report.php`, `assignments/cohort_create.php`, `assignments/reassign.php`, `agreements/*` (3), `followups/*` (3), `entries/attachments.php`, `entries/create_full.php`.
- `settings.php`: las `admin_externalpage` de derivaciones, coordinación, ámbitos, notificaciones e importación solo se registran si su función está activa; nuevo checkbox maestro.
- `renderer::plugin_navigation()`: oculta los enlaces de derivaciones, coordinación, coordinadores y notificaciones.
- `index.php` (hub de configuración): oculta visibilidad por cohorte y coordinadores.
- `block_monlaututoria`: oculta la sección de coordinación y el enlace de derivaciones.
- `observer\notification_observer`: no encola ninguna notificación si `NOTIFICATIONS` está oculto (las tareas de cron siguen registradas pero no encuentran trabajo — desactivarlas se difiere a S4).
- Sin cambio de esquema. `version.php` → `2026091700` / `0.13.0` (por encima de un `2026091601` que ya estaba instalado en el Moodle de pruebas sin estar en git — probablemente solo un bump manual de `version.php` al probar; no se ha encontrado código posterior a la v0.12.5 en el repositorio).
- Prueba nueva: `tests/feature_test.php` (lógica del interruptor). ⚠️ **No ejecutada** — no hay entorno Moodle en esta máquina; solo `php -l` (0 errores en los 330 archivos PHP del plugin + bloque). Pendientes (S5): Behat de la navegación/redirección en modo simple, del roster (`dashboard_roster.feature`), la agrupación `@advanced` de los `.feature` de módulos ocultos, y los manuales paso a paso (tutor, alumno, coordinación, admin técnico).

---

## 0.12.5 — 2026-07-30

**Encabezados de columna ordenables en todas las tablas principales.** Petición de uso real ("en todas las tablas estaria bien poder filtrar por el encabezado"), aclarada a "ordenar al hacer clic" aplicado de una vez a las tablas principales — no filtros por columna. Solo comportamiento de listado, sin cambio de esquema.

- Nuevo `renderer::sortable_header()`: helper compartido que pinta un enlace de encabezado — clic alterna ASC/DESC, cambiar de columna siempre empieza en ASC, con un indicador ▲/▼. Acepta nombres de parámetro de URL configurables (`$sortparam`/`$dirparam`) para que varias tablas independientes convivan en la misma página sin pisarse el estado de orden entre sí.
- **7 tablas ahora ordenables**: listado de asignaciones (`assignments/index.php`, ordenado en servidor vía `assignment_repository::search()`, que ya soportaba `ORDER BY` desde antes), las 4 tablas del panel del tutor (`dashboard.php`: alumnos, seguimientos, acuerdos, derivaciones — ordenadas en memoria con `usort()`, porque llegan como arrays ya completos sin paginar), el listado de derivaciones de coordinación (`referrals/index.php`, en servidor vía `referral_repository::search()`) y el historial de tutorías de la ficha del alumno (`student/view.php`, en servidor vía `entry_repository::search()`, al que se le añadió el parámetro de orden que ya soportaba el repositorio pero no usaba nadie). Las 2 tablas de desglose del panel de coordinación (`coordination.php`, por cohorte y por tutor) también son ordenables, en memoria.
- Limitación conocida y documentada (comentario en `referrals/index.php`): esa página comparte `renderer::referrals_table()` con `dashboard.php`, pero solo puede ordenar por las columnas que soporta `referral_repository::sortable_columns()` (estado/prioridad/fecha) porque el listado está paginado en SQL — no por alumno/destino, que si funcionan en el panel del tutor por venir de un array completo en memoria. Clic en esas dos columnas ahí no tiene efecto, mismo criterio de "orden desconocido cae al valor por defecto" que ya usa el resto de repositorios del plugin.
- Sin tablas nuevas de esquema, sin capacidades nuevas.
- ⚠️ No verificado todavía en un Moodle real; solo `php -l` (0 errores) en los 321 archivos PHP del plugin. `tests/output/renderer_test.php::test_assignments_list_escapes_hostile_row_values` actualizado a la nueva firma de `assignments_list()` (no ejecutado).

---

## 0.12.4 — 2026-07-30

**Ajuste de iconos: "Ver ficha" y "Reasignar tutor" pasan a Font Awesome, elegidos por el usuario.** Solo visual.

- `templates/assignment_summary.mustache`: "Ver ficha" usa `fa-solid fa-address-card` y "Reasignar tutor" usa `fa-solid fa-arrows-turn-to-dots`, embebidos directamente (`<i class="...">`, `aria-hidden="true"` por ser decorativos junto a texto ya visible) en vez del helper `{{#pix}}`. El resto (Ver detalle, Editar, Cerrar) sigue con los iconos de Moodle core del cambio anterior — ambos mecanismos conviven sin problema en un tema Boost, que ya carga Font Awesome globalmente.
- ⚠️ No verificado todavía en un Moodle real.

---

## 0.12.3 — 2026-07-25

**Iconos junto a las acciones del listado de asignaciones.** Petición de uso real, solo visual — sin cambio de esquema ni de lógica.

- `templates/assignment_summary.mustache`: cada acción de la columna "Acciones" (Ver detalle, Ver ficha, Editar, Reasignar tutor, Cerrar) ahora lleva un icono estándar de Moodle core delante del texto, vía el helper `{{#pix}}...{{/pix}}` — sin archivos de icono nuevos. Iconos decorativos (alt vacío): el texto del enlace ya identifica la acción, así que el lector de pantalla no la anuncia dos veces.
- Iconos usados: `i/info` (Ver detalle), `i/report` (Ver ficha), `t/edit` (Editar), `t/switch_roles` (Reasignar tutor), `t/locked` (Cerrar).
- ⚠️ No verificado todavía en un Moodle real — los nombres de icono son los estándar de Moodle core, pero conviene confirmar visualmente que `t/switch_roles`/`t/locked` encajan bien antes de darlo por cerrado.

---

## 0.12.2 — 2026-07-25

**Cohortes habilitadas: el filtro del listado de asignaciones también respeta ahora la lista.** Encontrado en uso real tras el 0.12.0 — se aplicó a los formularios de creación pero no al desplegable de filtro de `assignments/index.php`, que seguía mostrando todas las cohortes de Moodle.

- El filtro de cohorte del listado de asignaciones ahora muestra las cohortes habilitadas (`cohort_visibility.php`) **más** cualquier cohorte que ya tenga alguna asignación registrada, aunque se haya deshabilitado después — así nunca se pierde la capacidad de filtrar por datos que ya existen, solo se dejan de ofrecer cohortes irrelevantes que nunca se han usado.
- Nuevo `assignment_repository::get_distinct_cohort_ids()` (una sola consulta `SELECT DISTINCT`, sin N+1).
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 321 archivos PHP del plugin). Prueba nueva escrita (no ejecutada): `test_get_distinct_cohort_ids_returns_only_cohorts_actually_referenced`.

---

## 0.12.1 — 2026-07-25

**Corrección: excepción fatal al entrar en "Cohortes habilitadas".** Reportado en uso real.

- `cohort_visibility.php` llamaba a `admin_externalpage_setup()` sin haber cargado antes `$CFG->libdir . '/adminlib.php'` (`Call to undefined function admin_externalpage_setup()`) — el mismo `require_once` que ya tiene `coordination_scopes.php`, olvidado en la pantalla nueva.
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 321 archivos PHP del plugin).

---

## 0.12.0 — 2026-07-25

**Nueva funcionalidad: el administrador puede elegir qué cohortes de Moodle se muestran en el plugin.** Petición de uso real: cada desplegable de cohortes (crear asignación manual, asignación por cohorte, y el ámbito por defecto de un usuario con `viewallassignments` en coordinación) ofrecía literalmente todas las cohortes del sitio, incluidas las irrelevantes (p. ej. cohortes de personal).

- **Nueva tabla `local_tut_enabledcohort`** (lista global, no por usuario — distinta de `local_tut_coordscope`, que restringe cuáles de estas cohortes ve un coordinador concreto). **Vacía significa "sin restricción"**: tras actualizar, no se oculta nada hasta que un administrador entra en la nueva pantalla y guarda un subconjunto — ninguna instalación existente cambia de comportamiento sola.
- **Nueva pantalla "Cohortes habilitadas"** (Configuración del plugin, o Administración del sitio > Plugins locales > Monlau Tutoría > Cohortes habilitadas): lista de casillas, una por cohorte de Moodle. Aviso incluido en la propia pantalla: si no queda ninguna marcada al guardar, se interpreta como "todas habilitadas" (no como "ninguna"), para evitar que un desmarcado accidental de todo deje el plugin sin cohortes utilizables.
- **Aplicado en 3 sitios**: el desplegable de cohorte en "Nueva asignación", el de "Asignar por cohorte", y el ámbito por defecto de coordinación (`coordination_scope_service::get_effective_cohort_ids()`) — además, el ámbito explícito de un coordinador concreto (`coordination_scopes.php`) ahora se cruza con esta lista global, así que una cohorte deshabilitada globalmente deja de verse aunque un coordinador ya estuviera apuntado a ella.
- Privacy API actualizada (`createdby` como único dato de atribución; `cohortid` no es un dato personal, es una referencia a la propia cohorte de Moodle).
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 321 archivos PHP del plugin) y validación XML de `install.xml`. Pruebas nuevas escritas (no ejecutadas): `enabled_cohort_repository_test.php`, `cohort_visibility_service_test.php`, `coordination_scope_service_test.php`, y `local_tut_enabledcohort` añadida a la cobertura de `upgrade_test.php`.

---

## 0.11.2 — 2026-07-25

**Corrección: excepción fatal al entrar en el panel de coordinación.** Reportado en uso real.

- `coordination_dashboard_service::get_dashboard()` llamaba a `\core_user::get_users_by_id()`, un método que no existe en Moodle core (`Call to undefined method core\user::get_users_by_id()`) — rompía la carga de la pantalla en cuanto había al menos un tutor con asignaciones vigentes en el ámbito consultado. Sustituido por `$DB->get_records_list('user', 'id', ...)`, el mismo patrón de consulta por lote ya usado en el resto del plugin (`dashboard.php`, `block_monlaututoria.php`, `renderer.php`).
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 315 archivos PHP del plugin).

---

## 0.11.1 — 2026-07-25

**Panel del tutor: derivaciones y alumnos prioritarios ahora opcionales; enlace directo a "Nueva tutoría" en el bloque.** Feedback de uso real: ambas secciones generaban confusión al tutor — una derivación ya se puede explicar dentro del texto de la propia tutoría. Sin cambio de esquema, sin borrar nada: solo se deja de mostrar.

- **2 ajustes nuevos** (Administración del sitio > Plugins locales > Monlau Tutoría > Configuración): "Mostrar derivaciones en el panel del tutor" y "Mostrar alumnos prioritarios en el panel del tutor", ambos activados por defecto para no cambiar el comportamiento de una instalación existente sin que el administrador lo decida explícitamente. Al desactivarlos: desaparecen del panel del tutor la tarjeta, la sección/tabla, la columna "Prioridad" y el filtro "Alumnos prioritarios"; en el bloque desaparece el enlace "Abrir alumnos prioritarios". `dashboard_service`/`referral_service` siguen calculando exactamente lo mismo por debajo — es un ajuste de visualización, no de datos. **Deliberadamente sin tocar** `referrals/index.php` ni `coordination.php`: son pantallas de gestión para quien tiene el permiso de coordinación, no la vista del tutor que generaba la confusión.
- **Nuevo enlace "Nueva tutoría" en el bloque**, sin necesidad de conocer de antemano un alumno: `entries/create.php` ahora acepta llegar sin `studentid` y, en ese caso, muestra un selector limitado a los alumnos con tutoría principal vigente del propio tutor (`assignment_repository::find_current_primary_by_tutor()`) antes de continuar con el registro habitual — ningún alumno fuera de su ámbito aparece en la lista, por construcción.
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 315 archivos PHP del plugin principal y los 7 del bloque).

---

## 0.11.0 — 2026-07-25

**Nueva funcionalidad: asignación por cohorte completa, con vista previa y confirmación.** Cierra el hueco de la fase 3C — `cohort_assignment_preview_service` ya calculaba la clasificación desde hacía tiempo, pero no existía ni pantalla ni el paso de "confirmar" que escribe de verdad las asignaciones (reportado en uso real: la creación manual de asignaciones no permitía elegir una cohorte entera, solo un alumno cada vez). Sin cambio de esquema — reutiliza `local_tut_bulkoperation`, ya creado para esto.

- **Nueva pantalla `assignments/cohort_create.php`** ("Asignar por cohorte"), enlazada desde el listado de asignaciones y desde el propio formulario de creación manual (que ahora aclara que su campo "Cohorte" es solo una etiqueta, no un disparador masivo). Flujo de 3 pasos en una sola URL, igual que la importación CSV: elegir cohorte + curso académico + tutor principal (+ cotutor opcional) + modo → previsualizar (resumen y tabla por alumno, sin escribir nada) → confirmar y aplicar.
- **Nuevo `cohort_assignment_apply_service`**, el paso de "confirmar" que faltaba: nunca se fía de la previsualización guardada — recalcula la clasificación desde los propios parámetros de la operación y rechaza aplicar si algo ha cambiado desde que se generó. Solo se puede aplicar una vez (`bulk_operation_repository::claim()`, el mismo compare-and-swap atómico que ya usa la importación CSV). Toda la operación se aplica dentro de una única transacción (a diferencia de la importación CSV, aquí no hay elección de estrategia parcial/atómica: todo o nada). El modo "Solo previsualizar" nunca se puede confirmar, por diseño.
- **4 modos de sincronización**: solo previsualizar (nunca escribe, siempre disponible); añadir asignaciones a quien no tenga tutor; además cerrar las de alumnos que ya no están en la cohorte; reemplazar el tutor principal de quien ya tenía uno (acción de mayor impacto). Capacidades, exactamente la matriz que `docs/seguridad-permisos.md` ya proponía desde la Fase 3C.1, sin capacidades nuevas: `local/monlaututoria:managecohortassignments` (ya existía sin consumidor) da acceso a la pantalla y a "solo previsualizar"; `assignstudents` habilita "añadir asignaciones" y es requisito de los otros dos modos reales también (pueden crear una asignación nueva para un alumno sin tutor previo, no solo cerrar/reasignar); `manageassignments` habilita además "cerrar ausentes"; `reassignstudents`/`manageassignments` habilita además "reemplazar tutor principal".
- **Corrección de ortografía y cadenas de idioma faltantes** encontradas durante esta revisión: 8 cadenas que solo existían en `es` (mostraban `[[assignments_create_tip]]` literal en catalán/inglés) añadidas también a `en`/`ca`; 3 cadenas que solo existían en `en`/`es` añadidas a `ca`; varios acentos y apóstrofes catalanes corregidos en las cadenas tocadas (`Tauler de coordinació`, `àmbits`, etc. — no se ha hecho una auditoría completa del archivo `ca`, hay más casos sin corregir fuera del alcance de este cambio).
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 315 archivos PHP del plugin). Pruebas nuevas escritas (no ejecutadas): `tests/service/cohort_assignment_apply_service_test.php` (9 casos: creación, cotutor, reasignación, cierre de ausentes, ya aplicado, modo solo-previsualizar, previsualización caducada, eventos de éxito/fallo con rollback), y `tests/behat/cohort_assignment.feature` (3 escenarios).

---

## 0.10.6 — 2026-07-25

**Corrección: `unassigned_students_service` ya no cuenta cuentas suspendidas/eliminadas como "sin tutor vigente".** Sin cambio de esquema.

- Las cohortes de Moodle no se vacían automáticamente cuando termina la matrícula de un alumno, así que un alumno que ya se fue (cuenta suspendida al terminar el curso) podía seguir apareciendo en `search()`/`count()` como pendiente de tutor, y restaba en el denominador de `get_coverage_summary()` — bajando la cobertura por gente que nadie necesita asignar. Ahora `is_active_student()` excluye del cálculo a cualquier alumno con la cuenta suspendida o eliminada; `suspendedcount` sigue reportando cuántas hay, como dato informativo para coordinación.
- Este servicio todavía no está conectado a ninguna pantalla (solo tiene pruebas) — la corrección se hace ahora, antes de construir esa pantalla, para no arrastrar el problema.
- ⚠️ No ejecutado todavía en este entorno; solo `php -l`. Prueba existente actualizada (`test_suspended_student_flag_is_reported` → `test_suspended_student_is_excluded_from_the_unassigned_list`, ahora comprueba exclusión en vez de solo el flag) y prueba nueva para `get_coverage_summary()`.

---

## 0.10.5 — 2026-07-25

**Corrección de uso real: edición de tutorías y caracteres corruptos en el detalle de asignación.** Sin cambio de esquema.

- **Editar tutoría (`entries/edit.php`)**: ahora se pueden cambiar los "motivos" (`entry_field_reasons`, antes solo elegibles al crear) y adjuntar archivos directamente en la misma pantalla, sin un viaje aparte a `entries/attachments.php` — misma regla de capacidad (`editanyentry`/`editownentry` sobre el propietario) ya usada allí. Nuevo `entry_reason_repository::sync()` sustituye por completo el conjunto de motivos de una tutoría; `entry_service::update()` acepta un `$reasonids` opcional (`null` deja los motivos existentes intactos, un array vacío los borra todos).
- **Caracteres ilegibles corregidos**: `assignments/view.php` construía 6 valores de la pantalla "Detall de l'assignació" (curs acadèmic, cohort, data de finalització, observació, motiu de tancament, creat/modificat per) con un carácter de codificación corrupta (`�`) como marcador de "vacío" — sustituido por un guion largo limpio (`—`). Mismo carácter corrupto corregido también en `templates/assignment_detail.mustache` y en comentarios de `entry_repository.php` (cosmético, sin efecto funcional).
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 304 archivos PHP del plugin). Pruebas nuevas escritas (no ejecutadas): `entry_service_test.php` (sync/mantener/rechazar motivo inválido al editar), `entry_reason_repository_test.php` (`sync()`), y 2 escenarios Behat nuevos en `entry_edit_annul.feature`.

---

## 0.10.4 — 2026-07-25

**Página de ayuda estática + ayuda contextual por pantalla.** Sin cambio de esquema.

- **Nueva página `help.php`** ("Ayuda"), enlazada desde un nuevo elemento de navegación en todas las pantallas: explica en lenguaje llano qué es una tutoría, un acuerdo, un seguimiento y una derivación, y quién ve qué (alumno / tutor / coordinación). Puramente informativa, sin datos de ningún alumno — solo exige sesión iniciada, igual que `notifications.php`.
- **Ayuda contextual (`<details>`/`<summary>`, sin JavaScript)**: nuevo método `renderer::contextual_help()`, añadido en las 7 pantallas donde tiene más sentido — registro rápido y completo de tutoría, creación de acuerdo/seguimiento/derivación, panel del tutor y ficha del alumno — como un desplegable que el usuario abre solo si lo necesita, con el mismo texto breve que la página de ayuda para el concepto correspondiente.
- ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en los 304 archivos PHP del plugin).

---

## 0.10.3 — 2026-07-25

**Más correcciones de uso real (ficha del alumno y panel del tutor).** Sin cambio de esquema.

- **Ficha del alumno**: la foto del alumno y el selector de curso académico se pegaban entre sí (echoados uno tras otro sin contenedor). Ahora van en una fila con separación y el selector lleva su propia etiqueta visible.
- **Panel del tutor**: los 3 filtros (curso, alumnos, pendientes) y las tarjetas de resumen de debajo se veían pegados — ahora van en `.local-monlaututoria-toolbar` con su propio margen.
- **Mensajes de "no hay nada que mostrar"**: en el panel del tutor, `dashboard_followups_table()`/`dashboard_agreements_table()` mostraban un aviso de "no hay seguimientos/acuerdos" justo encima de una tabla que sí tenía filas (una lista vacía —p. ej. vencidos— seguida de otra con datos —p. ej. próximos—, ambas bajo el mismo epígrafe). Ahora solo se muestra un aviso combinado cuando **ambas** mitades están vacías; si alguna tiene datos, la otra simplemente no muestra nada. Además, el aviso en sí (y el de derivaciones/alumnos prioritarios) pasa de una caja de notificación azul a una línea de texto discreta (`subtle_empty_hint()`).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

---

## 0.10.2 — 2026-07-25

**Correcciones de uso real reportadas en pruebas manuales.** Sin cambio de esquema.

- **Registro de tutorías (formularios rápido y completo)**: la pestaña "Nota interna" ahora lleva un botón de ayuda aclarando que solo la ven tutores y coordinación, nunca el alumno (el enmascarado del lado servidor ya era correcto — `entry_service::mask_content()` — esto solo lo hace visible a quien rellena el formulario). Ambos formularios permiten ahora adjuntar archivos (con categoría documental) directamente al registrar la tutoría, sin necesitar un paso aparte en `entries/attachments.php` — gateado por la misma regla de capacidad que ya exige esa página (`editanyentry`/`editownentry`).
- **Ficha del alumno, pestaña "Tutorías"**: los botones "Registrar tutoría"/"Registro completo" y la fila de filtros se veían pegados entre sí, y los desplegables de filtro no mostraban ninguna etiqueta visible ("Elegir..." sin contexto). Corregido con más separación y usando `.local-monlaututoria-toolbar` (ya definida en `styles.css` pero sin usar hasta ahora) más etiquetas visibles en cada filtro.
- **Hallazgo real corregido**: un campo de casilla independiente ("Marcar como tutor principal") en `assignments/create.php` permitía crear una asignación etiquetada "Tutor principal" con `isprimary=0` en la base de datos — invisible para el panel del tutor, el bloque y la opción de reasignar tutor, todos los cuales exigen `isprimary=1`. Corregido quitando la casilla (la creación manual ahora deriva `isprimary` directamente del tipo elegido) y con un paso de reparación de datos en `db/upgrade.php` para filas ya creadas con el fallo. También corregidos caracteres de codificación corrupta ("�") en `assignments/index.php`.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

---

## 0.8.4 — 2026-07-24 (entrada de Codex; conservada tal cual, con corrupción de codificación original sin corregir aquí — fuera del alcance de este cambio)

**Panel del tutor ??? cierre de la Fase 7.** Completa 7.1-7.5 sobre la Fase 6 ya cerrada. **Cierra la Fase 7 completa.**

- **Dashboard del tutor** (`dashboard.php` + `dashboard_service`): lista alumnos principales vigentes del tutor para el curso acad??mico seleccionado, cobertura de tutor??as activas, seguimientos pr??ximos/vencidos, acuerdos pendientes/vencidos, derivaciones visibles y alumnos prioritarios.
- **Acciones r??pidas reales** en el panel: registrar tutor??a, ver ficha, crear seguimiento y ejecutar acciones de seguimiento/acuerdo desde sus tablas; filtros persistentes (`studentfilter`/`pendingfilter`) guardados en preferencias de usuario.
- **Resumen ampliado**: cobertura, pendientes, derivaciones, prioridad y contactos con familias calculados sin esquema nuevo.
- **Bloque complementario `block_monlaututoria`**: deja de ser esqueleto y consume `dashboard_service` para mostrar solo resumen y enlaces, sin acceso directo a tablas.
- **Pruebas/documentaci??n**: `dashboard_service_test.php` ampliado a 6 casos; `blocks/monlaututoria/tests/plugin_test.php` presente para el bloque. Cierre documental y bump de versi??n a `0.8.4` / `2026082700`.
  - ?????? No ejecutado todav??a en este entorno; validado con `php -l`, queda pendiente la ejecuci??n real de PHPUnit/Behat/Moodle navegador.

---

## 0.7.3 — 2026-07-24

**Cierre de la Fase 6** — Fase 6.5, la última de la Fase 6 (6.1-6.4 ya completas). Auditoría, no funcionalidad nueva. **Cierra la Fase 6 completa.**

- Privacy API ampliada para `local_tut_agreement`/`local_tut_followup`/`local_tut_referral`: metadata, contextos, exportación sin enmascarar, y anonimización de identidad (`studentid`, `responsibleuserid`/`assignedto`, `createdby`/`modifiedby`) conservando el contenido institucional (`description`/`reason`/`resolution`) — misma política que `local_tut_entry`.
- Revisión de seguridad (IDOR/XSS/CSRF), rendimiento y accesibilidad sobre las 15 páginas nuevas de la Fase 6: sin más hallazgos que el IDOR de `followupid` ya corregido durante 6.2.
- `tests/upgrade_test.php` ampliado con un caso de actualización 0.6.6 → actual.
- Sin migración de esquema.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

---

## 0.7.2 — 2026-07-24

**Derivaciones básicas** — Fase 6.4 (sobre 6.1-6.3). La única entidad de todo el plugin cuyo acceso de lectura no pasa por `scope_service`.

- **`local_tut_referral`** (tabla nueva): `entryid`, `studentid`, `destination` (coordinación/orientación/dirección), `reason` (siempre de nueva redacción, nunca copiado de las notas de la tutoría), `priority`, `assignedto`, `status`, `resolution`.
- **`referral_service::get_for_viewer()`**: visibilidad decidida por `managereferrals` o por ser el creador/asignado — nunca por ámbito. Crear sí exige ámbito sobre la tutoría de origen.
- `referral_updated` nunca lleva el texto de `resolution` en los datos del evento.
- 2 capacidades nuevas: `createreferral`, `managereferrals`.
- `referrals/index.php`/`view.php`/`create.php`/`assign.php`/`resolve.php`/`action.php` (nuevos), con entrada propia en *Administración del sitio*, sin pestaña en la ficha del alumno.
- Migración de esquema real (1 tabla, creada ya en 6.1), pero salto de versión de **parche** — sigue dentro del bloque de la Fase 6.
- PHPUnit: 4+6+2 casos nuevos. Behat: `referral_management.feature` (3 escenarios).

---

## 0.7.1 — 2026-07-24

**Seguimientos** — Fase 6.2 (sobre 6.1). Formaliza `local_tut_entry.nextfollowupdate` en una entidad propia.

- **`local_tut_followup`** (tabla nueva, creada ya en 6.1): `entryid`, `closingentryid` (opcional), `studentid`, `duedate`, `priority`, `status`.
- Acciones rápidas (completar/reabrir/posponer/cancelar) incluidas ya en este incremento.
- `entries/create.php`/`create_full.php` ganan un parámetro opcional `followupid` para cerrar un seguimiento mediante una nueva tutoría vinculada.
- **Hallazgo real corregido**: el parámetro `followupid` permitía cerrar el seguimiento de un alumno distinto sin comprobación — IDOR corregido verificando que el seguimiento pertenece al mismo alumno que la nueva tutoría.
- 2 capacidades nuevas: `createfollowup`, `managefollowups`.
- Nueva pestaña "Seguimientos" en `student/view.php`.
- PHPUnit: 4+6+2 casos nuevos. Behat: `followup_management.feature` (2 escenarios).

---

## 0.7.0 — 2026-07-24

**Acuerdos** — Fase 6.1, abre la Fase 6 completa ("Acuerdos, seguimientos y derivaciones"). Crea de una vez el esquema de las 3 tablas de toda la fase.

- **`local_tut_agreement`** (tabla nueva): `entryid`, `studentid`, `description`, `responsibletype` + responsable interno/externo, `duedate`, `status` (4 valores — "vencido" se calcula en lectura, nunca se persiste), `visibletostudent` (visibilidad de fila completa, no por campo).
- **`local_tut_followup`/`local_tut_referral`** (tablas nuevas, sin escritor todavía — igual que `local_tut_entryversion` entre las Fases 5.1 y 5.5).
- Acciones rápidas (completar/reabrir/posponer/cancelar) incluidas ya en este incremento — decisión deliberada, ver `agreement_service`.
- 2 capacidades nuevas: `createagreement`, `manageagreements`.
- `agreements/create.php`/`action.php`/`postpone.php` (nuevos). Pestaña "Acuerdos" de `student/view.php` con listado real y filtro "vencidos".
- Salto de versión **menor** (0.6.6 → 0.7.0) — abre el bloque de la Fase 6.
- PHPUnit: 5+9+2 casos nuevos. Behat: `agreement_management.feature` (3 escenarios).

---

## 0.6.6 — 2026-07-24

**Cierre de la Fase 5** — Fase 5.7, la última de la Fase 5 (5.1-5.6 ya completas). Auditoría, no funcionalidad nueva — mismo tipo de incremento que la Fase 3E sobre el módulo de asignaciones. **Cierra la Fase 5 completa.**

- **Hallazgo real corregido — hueco en la Privacy API**: `classes/privacy/provider.php` nunca se actualizó al crear `local_tut_entryversion` (5.5) ni `local_tut_entryattachment` (5.6) — ninguna de las dos estaba en `get_metadata()`, `get_contexts_for_userid()`, `get_users_in_context()`, la exportación ni el borrado/anonimización. Corregido: ambas tablas se localizan/exportan/anonimizan por `createdby` (no tienen `studentid`/`tutorid` propios), `snapshotjson`/`changereason`/`category` se conservan en el borrado (mismo valor de historial institucional que el resto de contenido de tutorías), `description` de los adjuntos se limpia (más parecida a `note` que a contenido institucional), y los propios archivos se exportan vía `writer::export_file()` en una solicitud de acceso.
- **Revisión de seguridad (IDOR/XSS/CSRF)** sobre las 7 páginas nuevas de la Fase 5: sin hallazgos nuevos, la implementación ya era coherente — ver `docs/seguridad-permisos.md`.
- **Revisión de rendimiento**: el bucle de `core_user::get_user()` por participante en `entries/view.php` revisado y aceptado sin cambio (acotado a una sola tutoría, no un listado que escale).
- **Revisión de accesibilidad**: tablas anchas nuevas ya usan `table-responsive`, formularios ya usan Forms API — confirmado sin cambios de código.
- **Pruebas**: `tests/upgrade_test.php` ampliado con un caso que simula la actualización desde 0.5.3 (justo antes de la Fase 5) hasta la versión actual, verificando que las 5 tablas nuevas se crean y los datos previos sobreviven. Nuevos casos en `tests/privacy/provider_test.php` para `local_tut_entryversion`/`local_tut_entryattachment` (contextos, exportación con archivo incluido, anonimización).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

---

## 0.6.5 — 2026-07-24

**Adjuntos de tutorías** — Fase 5.6 (sobre la Fase 5.5). Primer uso de la File API y de `pluginfile.php` en todo el plugin.

- **`local_tut_entryattachment`** (tabla nueva): solo metadatos (categoría documental, descripción) — los archivos en sí viven en el almacenamiento de archivos de Moodle (`component=local_monlaututoria`, `filearea=entryattachment`, `itemid=entryid`), identificados por `pathnamehash` (más robusto que el nombre de archivo, sobrevive a archivos distintos con el mismo nombre).
- **`entry_attachment_service`**: `save_uploaded_files()` mueve los archivos de un área de borrador al área permanente (`file_save_draft_area_files()`, el mecanismo estándar de Moodle — el antivirus configurado ya se aplica automáticamente por este mismo mecanismo, sin código adicional) y registra una fila de metadatos por archivo nuevo. `get_for_entry()` combina archivos + metadatos, con el mismo candado de seguridad que `entry_service::get_for_viewer()`: ámbito (`scope_service`) + `viewinternalnotes`, y **nunca** al propio alumno, sea cual sea su combinación de capacidades — los adjuntos son solo para el personal de tutoría en este incremento, no existe un nivel "compartido con el alumno" para archivos.
- **`local_monlaututoria_pluginfile()`** (nuevo en `lib.php`, primera función de este archivo): el punto de control de acceso más importante de todo el incremento — una URL de `pluginfile.php` es visible y manipulable directamente por el navegador, sin pasar por ninguna página del plugin. Repite a mano la autenticación + contexto + capacidad + ámbito que cualquier otra página ya hace, exactamente el caso "acceso directo a archivos" que `CLAUDE.md` pide probar explícitamente.
- **4 categorías documentales**: informe, autorización, evidencia, otro — una categoría por lote de subida, no por archivo individual (evitar JavaScript adicional sin precedente en este proyecto).
- **`entries/attachments.php`** (nuevo): listado (`viewinternalnotes` + ámbito) y subida (`editanyentry`/`editownentry`, bloqueada en entradas anuladas) — enlace desde `entries/view.php`.

**Pruebas**
- PHPUnit: 3 casos nuevos en `entry_attachment_repository_test.php`, 5 en `entry_attachment_service_test.php` (incluida la denegación al propio alumno pese a tener las capacidades), 1 en `entry_attachment_added_test.php`. Las subidas se simulan escribiendo directamente en un área de borrador (File API), ya que PHPUnit no puede conducir una subida HTTP real.
- Behat: `entry_attachments.feature` (nuevo, 4 escenarios) — incluye una prueba de acceso directo a `pluginfile.php` por un tutor sin relación con el alumno, señalada explícitamente como la menos verificada de este incremento (el texto exacto que Moodle muestra ante un archivo denegado necesita confirmarse contra la instancia real).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin) y una comprobación de buena formación del XML de `install.xml`.

**Explícitamente fuera de 5.6**: edición o eliminación de un adjunto ya subido (solo se puede añadir); ningún nivel de adjunto visible para el alumno (hueco aceptado y documentado, no un olvido — el modelo mínimo de la Fase 5 no define un nivel "compartido" para archivos). Resto de la Fase 5: cierre — pruebas de filtración, rendimiento, accesibilidad (5.7).

---

## 0.6.4 — 2026-07-24

**Edición, versionado y anulación de tutorías** — Fase 5.5 (sobre la Fase 5.4). Finalmente escribe en `local_tut_entryversion`, la tabla que la Fase 5.1 creó vacía a propósito.

- **`settings.php`**: primera opción de configuración real de todo el plugin (hasta ahora solo páginas externas) — "ventana de edición" (`local_monlaututoria/entryeditwindow`, `admin_setting_configduration`, por defecto 3 días). `entry_service::update()` la lee vía `get_config()`, con una reserva de 3 días en código por si el valor por defecto todavía no se ha sembrado en `mdl_config_plugins`.
- **`entry_service::update()`** (nuevo): dentro de la ventana, edición sin motivo; fuera de ella, motivo obligatorio ("cambios sensibles" del prompt de la Fase 5.5). Antes de escribir, siempre toma una foto del estado anterior en `local_tut_entryversion` — la lógica de foto se comparte con `annul()` mediante `snapshot_current_state()`, una sola definición. `noterestricted` se descarta silenciosamente si el llamador no tiene `viewrestrictednotes`, igual que ya hacía `create()`.
- **`entry_service::annul()`** (nuevo): anulación lógica (`status=annulled`), nunca borrado físico. Motivo siempre obligatorio — a diferencia de editar, anular no tiene versión "rápida". Misma protección de concurrencia (relectura dentro de transacción) que `assignment_service::close()` desde la Fase 3E.3.
- **3 capacidades nuevas**: `editownentry` (limitada a `createdby` propio), `editanyentry`, `annulentry`.
- **`entries/edit.php`/`entries/annul.php`** (nuevos), con sus formularios (`entry_edit_form.php`/`entry_annul_form.php`) — mismo patrón que `assignments/edit.php`/`assignments/close.php`: motivo condicional (`requirereason`) en el de edición, confirmación explícita obligatoria en el de anulación.
- Enlaces "Editar tutoría"/"Anular tutoría" en `entries/view.php`, visibles solo con la capacidad correspondiente y solo mientras la entrada sigue activa.
- Sin migración de esquema (la tabla ya existía; ninguna columna nueva).

**Pruebas**
- PHPUnit: 3 casos nuevos en `entry_version_repository_test.php`, 2 en `entry_repository_test.php`, 8 en `entry_service_test.php` (ventana dentro/fuera, foto del estado anterior, `noterestricted` descartado sin capacidad, rechazo de editar/anular una entrada ya anulada, motivo obligatorio en la anulación), 1 en `entry_updated_test.php`, 1 en `entry_annulled_test.php`.
- Behat: `entry_edit_annul.feature` (nuevo, 4 escenarios). El caso de "editar fuera de la ventana exige motivo" no se cubre aquí — requiere manipular el reloj, impracticable en Behat — y ya está probado directamente en `entry_service_test.php`.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Explícitamente fuera de 5.5** (resto de la Fase 5): adjuntos (5.6), cierre — pruebas de filtración, rendimiento, accesibilidad (5.7). Tampoco se permite editar participantes ni motivos de una entrada existente (`update_editable_fields()` no los toca) — hueco aceptado y documentado, no un olvido.

---

## 0.6.3 — 2026-07-24

**Historial y detalle de tutorías** — Fase 5.4 (sobre la Fase 5.3). Sustituye el aviso de marcador de posición de la pestaña "Tutorías" por un listado real, y añade una página de detalle por entrada.

- **`entry_repository::build_search_where()`** ampliado: filtros por `modalityid`, `reasonid` (subconsulta contra `local_tut_entryreason`) y `visibilitytier` (filas donde `contentvisible`/`noteinternal`/`noterestricted` no es nulo — útil, por ejemplo, para que un coordinador audite qué entradas tienen nota restringida).
- **`entry_reason_repository::get_for_entries()`** (nuevo): resuelve los motivos de un lote de entradas en una sola consulta, evitando el N+1 que llamar a `get_for_entry()` por fila habría introducido.
- **`entry_service`**: lógica de enmascarado extraída a un método privado `mask_content()`, reutilizado por `get_for_viewer()` (ya existente) y los dos métodos nuevos `get_history_for_student()`/`count_history_for_student()` — una sola comprobación de `scope_service` por página, nunca una por fila (mismo criterio de rendimiento que la Fase 3E.4).
- **`renderer::entry_history_table()`/`entry_detail()`** (nuevos): tabla cronológica con `table-responsive`, y una vista de detalle en Mustache — mismo patrón que `student_history_table()`/`assignment_detail`.
- **`entries/view.php`** (nuevo): detalle de una entrada, mismo patrón de 2 capas que `assignments/view.php` (`viewstudent` + `scope_service`, aquí delegado en `entry_service::get_for_viewer()`).
- **Vista limitada del alumno**: igual que el historial de asignaciones (Fase 4.2/4.3), la propia vista del alumno oculta la columna/filtro de Motivos y el enlace "Ver detalle" — mismo criterio de "categorización administrativa, no nota interna, pero tampoco pensada para el alumno".
- Sin migración de esquema ni capacidades nuevas.

**Pruebas**
- PHPUnit: 3 casos nuevos en `entry_repository_test.php` (filtros modalidad/motivo/visibilidad), 2 en `entry_reason_repository_test.php` (`get_for_entries()`), 4 en `entry_service_test.php` (enmascarado por fila, filtros, acceso denegado sin ámbito, recuento de lecturas de BD constante con el número de filas).
- Behat: `entry_history.feature` (nuevo, 3 escenarios): un tutor ve y abre el detalle de una entrada; un alumno nunca ve la nota interna ni la columna de motivos en su propio historial; filtrar por un motivo no relacionado no devuelve filas.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Explícitamente fuera de 5.4** (resto de la Fase 5): edición/versionado/anulación (5.5), adjuntos (5.6), cierre (5.7).

---

## 0.6.2 — 2026-07-24

**Registro completo de tutorías** — Fase 5.3 (sobre la Fase 5.2). Segunda interfaz de la Fase 5: todo lo que el registro rápido dejó fuera a propósito. Sin cambios en `entry_service`/`entry_create_command` — el dominio de la Fase 5.1 ya soportaba varios motivos, participantes y nota restringida desde el primer día; este incremento es puramente una interfaz nueva que por fin ejercita esa parte del modelo.

- **`classes/form/entry_full_form.php`** (nuevo): motivos múltiples (`<select multiple>`), participantes internos/externos por filas repetibles (`repeat_elements()`, cada fila con tipo + usuario interno opcional + nombre externo opcional), y la nota restringida — este último elemento **solo se añade al formulario en absoluto** cuando el llamador tiene `viewrestrictednotes`; nunca se renderiza y se oculta con CSS/JS.
- **`entries/create_full.php`** (nuevo): mismo patrón de 2 capas que `entries/create.php` (`createentry` + `scope_service`). Adjuntos explícitamente fuera de alcance (Fase 5.6).
- **Botón "Registro completo"** junto al de registro rápido, en la pestaña "Tutorías" de `student/view.php`.
- Sin migración de esquema ni capacidades nuevas (reutiliza `createentry` de 5.2 y `viewrestrictednotes` de 5.1).

**Pruebas**
- Behat: `entry_full_registration.feature` (nuevo, 3 escenarios): registro completo con 2 motivos + participante externo + nota restringida por quien tiene la capacidad; quien no la tiene nunca ve el campo; al menos un motivo es obligatorio. Un cuarto caso (participante con usuario interno Y nombre externo a la vez) se documenta como no cubierto aquí — el selector de usuario interno es un componente AJAX que no se puede rellenar con un paso Behat simple; esa validación ya está cubierta a nivel de servicio en `entry_service_test.php` (Fase 5.1).
- Sin PHPUnit nuevo: mismo criterio que 5.2, sin lógica de negocio propia en el formulario/página.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Explícitamente fuera de 5.3** (resto de la Fase 5): historial y detalle (5.4), edición/versionado/anulación (5.5), adjuntos (5.6), cierre (5.7).

---

## 0.6.1 — 2026-07-24

**Registro rápido de tutorías** — Fase 5.2 (sobre la Fase 5.1). Primera interfaz de la Fase 5: "menos de un minuto", alumno preseleccionado, sin selector de tutor (siempre el usuario conectado).

- **`classes/form/entry_quick_form.php`** (nuevo): fecha, modalidad, motivo (uno solo — el modelo admite varios, pero el registro rápido pide uno), comentario compartido (obligatorio), nota interna (opcional), próximo seguimiento (opcional). Alumno y curso académico van como campos ocultos, nunca elegibles aquí.
- **`entries/create.php`** (nuevo): `local/monlaututoria:createentry` (nueva capacidad) + `scope_service::require_user_can_access_student()`, mismo patrón de 2 capas que el resto del plugin. El alumno llega siempre por parámetro (`studentid`), nunca por un selector.
- **Enlace "Registrar tutoría"** en la pestaña "Tutorías" de `student/view.php`, visible solo con `createentry` y solo para quien no esté viendo su propia ficha. El aviso de la pestaña se corrige de "el registro de tutorías no está disponible" a "el **historial** de tutorías no está disponible" — el registro ya lo está desde este incremento, solo la vista de historial sigue pendiente (Fase 5.4).
- Sin migración de esquema (una capacidad nueva se sincroniza sola).

**Pruebas**
- Behat: `tests/behat/entry_quick_registration.feature` (nuevo, 4 escenarios): registro exitoso desde la ficha del alumno; usuario sin `createentry` denegado; tutor con `createentry` pero sin relación con el alumno denegado (IDOR); comentario compartido obligatorio.
- Sin PHPUnit nuevo: no hay lógica de negocio nueva en el formulario/página (toda la validación real ya la cubre `entry_service_test.php` desde la Fase 5.1); mismo criterio que `assignment_form.php`/`assignments/create.php`, sin pruebas unitarias propias.
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin).

**Explícitamente fuera de 5.2** (resto de la Fase 5): participantes internos/externos y motivos múltiples por interfaz (5.3), historial y detalle (5.4), edición/versionado/anulación (5.5), adjuntos (5.6), cierre (5.7).

---

## 0.6.0 — 2026-07-24

**Registro de tutorías — dominio y datos** — Fase 5.1, primer incremento de la Fase 5 (sobre la Fase 4, ya completa). "El núcleo funcional del plugin" según `docs/fases/phase-5.md`. **Migración de esquema real**: 4 tablas nuevas. Sin interfaz todavía — la construyen las fases 5.2-5.6.

- **`local_tut_entry`**: el registro tutorial en sí. `studentid`, `tutorid` (tutor responsable), `academicyearid`, `entrydate` (fecha real, distinta de `timecreated`), `modalityid` (reutiliza el catálogo de la Fase 2), y 3 columnas de contenido de nivel fijo — `contentvisible`/`noteinternal`/`noterestricted` — en vez de una sola columna con un nivel elegible.
- **`local_tut_entryreason`**: motivos relacionados, N:M con `local_tut_reason`.
- **`local_tut_entryparticipant`**: participantes internos (usuario Moodle) y externos (nombre libre), con tipo (familia/orientación/empresa/profesorado/otro).
- **`local_tut_entryversion`**: creada ahora, **sin repositorio ni escritor todavía** — la edición que generaría versiones llega en la Fase 5.5, mismo criterio que `closereason` llegó con la 3B.3A y no antes.
- **`entry_service::get_for_viewer()`**: el mecanismo de seguridad central del incremento. Reutiliza `scope_service` sin modificarlo para el acceso al alumno, y aplica un segundo filtro propio sobre el contenido — `noteinternal`/`noterestricted` nunca se muestran al propio alumno, sea cual sea su combinación de capacidades (implementa el caso de prueba obligatorio de `CLAUDE.md`, "alumno intentando consultar notas internas").
- **3 capacidades nuevas de lectura**: `viewstudentvisiblecontent`, `viewinternalnotes`, `viewrestrictednotes`. Las 4 capacidades de escritura "orientativas" de la fase (`createentry`/`editownentry`/`editanyentry`/`annulentry`) se dejan sin definir hasta que exista la página que las exige.
- **Privacy API**: misma política que `local_tut_assignment` (decisión del usuario en esta sesión) — conservación indefinida, anonimización de identidad en el borrado, contenido de las notas conservado por su valor de historial institucional; exportación sin enmascarar por capacidad.
- Sin cambios en `docs/matriz-capacidades.md`/`docs/seguridad-permisos.md` de fases anteriores, ampliados con una sección nueva cada uno.

**Pruebas**
- PHPUnit: 4+2+3 casos en los 3 repositorios nuevos; 18 casos en `entry_service_test.php` (9 de validación de `create()`, 9 cubriendo la matriz completa de `get_for_viewer()`); 1 caso de evento; 4 casos nuevos en `provider_test.php`.
- Sin Behat: no existe página alguna todavía (mismo criterio que la Fase 3A).
  - ⚠️ No ejecutado todavía en este entorno; solo `php -l` (0 errores en todo el plugin) y una comprobación de buena formación del XML de `install.xml`.

**Explícitamente fuera de 5.1** (resto de la Fase 5): registro rápido (5.2), registro completo con participantes por interfaz (5.3), historial y detalle (5.4), edición/versionado/anulación (5.5), adjuntos (5.6), cierre (5.7).

---

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
