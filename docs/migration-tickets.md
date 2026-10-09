# Tickets de migración: `github/moodle` (4.3.12) → `github/moodle-2027` (5.3 LTS)

Inventario de **todo lo propio** de Richi Math que hay que llevar del fork 4.3
al código nuevo de 5.3, ticket por ticket. El plan que los ordena en el tiempo
está en [migration-plan.md](migration-plan.md).

Redactado el 2026-10-08 comparando los dos árboles en disco.

---

## 0. Punto de partida (medido, no supuesto)

**Origen** — `github/moodle`, rama `main`, Moodle **4.3.12 (Build 20250414)**,
2023100912. Desde la importación del código de Contabo (`0e25818d`) hay 248
ficheros cambiados, **ninguno de core**: la regla «nunca tocar core» se cumplió.
Lo propio cabe en esta lista:

| Qué | Dónde | Ficheros |
|---|---|---|
| Plugin de herramientas `local_richimath` (v2026100400) | `local/richimath/` | 41 |
| Tema padre `theme_richimath` (v2026100301) | `theme/richimath/` | 54 + 23 iconos |
| 4 temas de nivel `rmprimaria`, `rmsecundaria`, `rmpreu`, `rmuniversidad` | `theme/rm*/` | 38 |
| Reglas de URL limpias | `.htaccess` | 1 |
| Scripts de construcción y despliegue | `scripts/` | 15 |
| Recursos fuente (logos, fondos, H5P, preguntas) | `assets/` | 17 |
| Documentación | `docs/`, `MEMORY.md`, `SUMMARY.md`, `CLAUDE.md` | 79 |
| Entorno local | `docker-compose.yml` | 1 |

`core_plugin_manager` confirma que **no hay ningún otro plugin no estándar**
instalado: solo esos seis.

**Destino** — `github/moodle-2027`, Moodle **5.3.0 (Build 20261005)**,
2026100500.00, rama 503, STABLE. Un commit («Created v 5.3 LTS»), sin tags,
`githash.php` = 4262229. Sin `.gitignore` y sin `docs/` hasta este documento.

### Lo que cambia en 5.3 y afecta a casi todos los tickets

1. **El código web vive en `public/`.** Los plugins van en
   `public/local/richimath`, `public/theme/richimath`…; `config.php` queda en la
   raíz del repo. El `DocumentRoot` de Apache pasa a `…/public`.
2. **No se sube directo desde 4.3.** `public/admin/environment.xml` declara
   `<MOODLE version="5.3" requires="4.4">`. Hace falta un paso intermedio por
   **4.5 LTS**. Ver el plan.
3. **Servidor más nuevo**: PHP **8.3** (prod tiene 8.2) y **MySQL 8.4 /
   MariaDB 11.4** (prod tiene MySQL 8; local MariaDB 10.11).
4. **Bootstrap 5** (desde 5.0). Las clases y atributos de Bootstrap 4 siguen
   funcionando gracias a `bs4-compat`, pero **ese puente está marcado como
   obsoleto** y desaparecerá.
5. **Hooks**: el callback `*_after_config` está reemplazado por el hook
   `\core\hook\after_config`. 5.3 todavía llama al antiguo, con aviso de
   obsoleto.
6. **Iconos de actividad nuevos**: core ya los pinta **en color y sin
   baldosa** (filtro SVG por propósito), con modo oscuro.
7. **`mod_chat` y `mod_survey` salieron de core** (5.0).
8. **Banco de preguntas**: desde 5.0 vive en instancias del módulo `mod_qbank`;
   los bancos de curso se migran solos en la actualización.
9. **Lista de usuarios del admin** (`/admin/user.php`): ahora es un informe del
   *report builder*, no el formulario de filtros de 4.3.
10. **Router de Moodle** (`r.php`, `$CFG->routerconfigured = false` por defecto).

---

## Cómo leer cada ticket

- **Origen** → **Destino**: rutas en 4.3 y en 5.3.
- **Qué cambia**: el trabajo de adaptación concreto. Si dice «copiar», es copiar
  y verificar.
- **Hecho cuando**: criterios de aceptación verificables en el navegador o por
  CLI, a 1440 y 390 px, como admin y como alumno (la regla del repo).
- **Riesgo**: 🟢 copiar y verificar · 🟡 adaptar · 🔴 rehacer sobre la base nueva.

---

## A. Repositorio y entorno

### MIG-01 · Preparar el repositorio `moodle-2027` 🟢
- **Qué**: `.gitignore` (como mínimo `config.php`, `node_modules/`,
  `moodledata/`, `.env`); llevar `CLAUDE.md`, `MEMORY.md` y `SUMMARY.md` del repo
  viejo, actualizados a 5.3; registrar en `MEMORY.md` las reglas de siempre
  (nunca tocar core, nunca contraseñas en el repo, commits en inglés
  Conventional Commits, verificación con capturas).
- **Ojo**: `git status` dice `main...origin/main [desaparecido]`: la rama
  remota no existe todavía. El primer push es decisión tuya.
- **Hecho cuando**: `git status` limpio con `config.php` ignorado, y los tres
  ficheros de contexto presentes.

### MIG-02 · Entorno local para 5.3 🟡
- **Origen**: `docker-compose.yml`, `scripts/dev-up.sh`, `docs/local-dev-environment.md`.
- **Qué cambia**: imagen `moodlehq/moodle-php-apache:8.3` (hoy 8.1), base
  `mariadb:11.4` o `mysql:8.4` (hoy 10.11), `DocumentRoot` apuntando a
  `/var/www/html/public`, `mod_rewrite` y `AllowOverride` como ahora.
- **Hecho cuando**: `admin/cli/install_database.php` o la restauración del
  espejo arrancan, y `/admin/environment.php` sale todo en verde.

### MIG-03 · `config.php` de 5.3 🟢
- **Qué**: partir de `config-dist.php` de la raíz; trasladar `wwwroot`,
  `dataroot`, `dbtype` (`mysqli` en prod), `sslproxy`/`reverseproxy` si los hay, y
  el desvío de correo local. Dejar **`$CFG->routerconfigured = false`** (ver
  MIG-23).
- **Hecho cuando**: el sitio carga en local; ninguna credencial queda en git.

---

## B. Plugin `local_richimath`

Todo el plugin se copia a `public/local/richimath/`. Sus 5 tablas
(`invitation`, `courselink`, `plan`, `userplan`, `lead`) y sus datos **viajan
solos con la base de datos**. No hay que recrearlas.

### MIG-10 · Base del plugin 🟡
- **Origen**: `local/richimath/{version.php, lib.php, settings.php, db/*, lang/*, classes/privacy/}`.
- **Qué cambia**:
  - `version.php`: `requires = 2026100500`, nueva `version` (por ejemplo
    `2026110100`) **sin** pasos de upgrade nuevos que no hagan falta.
  - `local_richimath_after_config()` (limpia el `wantsurl` de `/login`) → hook
    `\core\hook\after_config` en `db/hooks.php` + clase estática. Borrar el
    callback antiguo, que en 5.3 solo sirve para generar avisos.
  - `extend_navigation_course` y `extend_settings_navigation`: 5.3 los sigue
    llamando con la misma firma (`$this, $this->context` en
    `settings_navigation::load_local_plugin_settings()`). Copiar y verificar.
    **La firma de `extend_settings_navigation` fue la que tumbó producción el
    2026-10-03**: el segundo argumento es un **contexto**.
  - `db/upgrade.php`: se queda entero (los pasos viejos no se vuelven a
    ejecutar: la versión guardada ya los supera).
- **Hecho cuando**: `admin/cli/upgrade.php` pasa sin avisos de obsoleto del
  plugin, y `/my/`, un curso y una actividad abren sin error con depuración
  DEVELOPER.

### MIG-11 · Invitaciones de un solo uso y enlace del curso 🟢
- **Origen**: `invite.php`, `accept.php`, `classes/{invitation,courselink}.php`,
  `classes/form/{invite_form,identify_form,signup_form}.php`, `templates/invite.mustache`.
  Docs: `docs/product/invitations.md`, `matricular-por-correo.md`.
- **Qué cambia**: el formulario usa `moodleform` (sin cambios de API). Revisar
  la plantilla por clases de Bootstrap 4 (2 usos).
- **Hecho cuando**: invitar por correo a un curso → el invitado crea cuenta por
  el enlace → queda matriculado; el enlace compartido del curso funciona.

### MIG-12 · Planes y apariencias (tema por usuario) 🟡
- **Origen**: `plans.php`, `userplans.php`, `classes/plans/*`, `classes/observer.php`,
  `db/events.php` (`\core\event\user_loggedin`), `classes/form/plan_form.php`.
  Doc: `docs/product/plans-and-appearances.md`.
- **Qué cambia**: comprobar que el tema del usuario (`allowuserthemes`) sigue
  siendo el mecanismo en 5.3 y que el observador asigna el tema correcto al
  iniciar sesión.
- **Hecho cuando**: un alumno con plan Primaria entra y ve `rmprimaria`; el
  cambio de plan desde el admin se nota en el siguiente inicio de sesión.

### MIG-13 · Catálogo `/students` y visibilidad de categorías 🟡
- **Origen**: `students.php`, `classes/category_visibility.php`,
  `theme/richimath/templates/local/students.mustache`, SCSS §A2.
- **Hecho cuando**: `/students` lista las categorías visibles con sus cursos;
  «Categoría 1» sigue oculta.

### MIG-14 · Rutas limpias 🔴
- **Origen**: `local/richimath/routes.php` (mapa), `classes/routes.php`,
  `scripts/build-routes.php` (escribe el bloque del `.htaccess`), `.htaccess`,
  ajuste `local_richimath/prettyurls`. Doc: `docs/product/public-site-and-routes.md`.
- **Qué cambia**:
  - El `.htaccess` va a `public/.htaccess`; `build-routes.php` tiene que
    escribir ahí.
  - Las reglas reescriben a scripts reales (`/login/index.php`…): mismas rutas
    relativas dentro de `public/`.
  - **Convivencia con el router de Moodle**: con `routerconfigured = false` no
    hay conflicto. Si algún día se configura el router (todo lo que no existe
    va a `r.php`), nuestras reglas deben ir **antes**.
  - Las tres guardas siguen siendo obligatorias: `REDIRECT_STATUS`, no
    redirigir POST, `DirectorySlash Off`.
- **Hecho cuando**: cada ruta del mapa responde en sus dos direcciones (limpia →
  200, `.php` → redirige a la limpia); un **login fallido** se queda en `/login`
  con el error; con `prettyurls` apagado, todo funciona con las URL normales.

### MIG-15 · Botón de WhatsApp del profesor 🟢
- **Origen**: `classes/{whatsapp,whatsapp_fields}.php`, upgrade 2026100300/01,
  renderer `teacher_whatsapp()`, FAB en `drawers.mustache`, SCSS §I.
  Doc: `docs/product/whatsapp-teacher-button.md`.
- **Qué cambia**: nada en datos (campos de perfil y de curso viajan con la BD).
  El FAB depende de MIG-21 (`drawers.mustache`).
- **Hecho cuando**: la matriz de 8 casos (profesor encendido/apagado × curso
  sin tocar/heredar/mostrar/no mostrar) da lo mismo que en 4.3; a 390 px los
  tres botones no se solapan.

### MIG-16 · Invitados a una clase BBB y registro de leads 🟡
- **Origen**: `session.php`, `sessionlink.php`, `leads.php`, `classes/lead.php`,
  `classes/form/guest_register_form.php`, tabla `local_richimath_lead`.
  Docs: `docs/product/open-session-leads.md`, `docs/product/v4/guest-class-invitation.md`.
- **Qué cambia**: el traspaso postea a `mod/bigbluebuttonbn/guest.php` con el
  identificador **que core calcula desde el nombre de clase**
  (`mod_bigbluebuttonbn\form\guest_login`). Si en 5.3 la clase cambió de nombre
  o el formulario ganó campos, el traspaso falla **en silencio**. Hay que
  comprobarlo.
- **Hecho cuando**: como anónimo: registro → lead guardado → «Entrar a la
  clase» → core acepta y responde «La reunión aún no ha comenzado» (o entra si
  está abierta). El CSV solo incluye a quienes aceptaron.

### MIG-17 · Privacidad entre alumnos 🟢
- **Origen**: `classes/privacy_lockdown.php`, upgrade 2026100400.
  Doc: `docs/product/student-privacy.md`.
- **Qué cambia**: nada en datos (permisos y ajustes viajan con la BD).
  **Verificar** que las capacidades siguen existiendo con el mismo nombre y que
  el *report builder* de participantes respeta `viewparticipants`.
- **Hecho cuando**: como alumno, se repite la tabla «Verificado» del doc:
  participantes sin permiso, perfil del compañero «no disponible», compañero
  fuera del buscador de mensajes, profesor sí.

---

## C. Tema `theme_richimath` y temas de nivel

### MIG-20 · Base del tema y compilación SCSS 🔴
- **Origen**: `theme/richimath/{config.php, lib.php, settings.php, version.php, scss/pre.scss, scss/post.scss (≈5 850 líneas), style/fonts.css, pix/*}`.
- **Qué cambia**:
  - `version.php`: `requires = 2026100500`, `dependencies = ['theme_boost' => 2026100500]`.
  - `pre.scss` redefine variables de Bootstrap 4; en Bootstrap 5 algunas se
    llaman distinto o ya no existen. Compilar y leer **cada** aviso.
  - Funciones de color: BS5 trae `tint-color()`/`shade-color()`; `darken()` y
    `lighten()` siguen en scssphp, pero conviene alinearse.
  - Comprobar que `$activity-icon-*-bg` y `$activity-icon-colors` siguen
    existiendo (sí: `public/theme/boost/scss/moodle/variables.scss`).
  - **Modo oscuro**: 5.3 lo trae apagado por defecto (Boost →
    *Colour modes*). Dejarlo **apagado**: los temas no están diseñados para él.
- **Hecho cuando**: purga de cachés sin error de SCSS; la página de login, el
  dashboard y un curso se ven como en 4.3 a 1440 y 390 px.

### MIG-21 · Rehacer las 5 plantillas de core copiadas 🔴
Es el riesgo más traicionero: una plantilla copiada de 4.3 **no falla**. El tema
sigue sirviendo la copia vieja y lo nuevo de core simplemente no aparece.
**Método obligatorio**: partir de la plantilla de 5.3 y volver a aplicar
encima el cambio mínimo nuestro, nunca al revés.

| Plantilla (en el tema) | Plantilla de core en 5.3 | Líneas cambiadas en core 4.3→5.3 |
|---|---|---|
| `templates/core/loginform.mustache` | `public/lib/templates/loginform.mustache` | **304** |
| `templates/theme_boost/drawers.mustache` | `public/theme/boost/templates/drawers.mustache` | 50 |
| `templates/theme_boost/primary-drawer-mobile.mustache` | `public/theme/boost/templates/primary-drawer-mobile.mustache` | 31 |
| `templates/core_calendar/day_detailed.mustache` | `public/calendar/templates/day_detailed.mustache` | 0 |
| `templates/core_calendar/calendar_month.mustache` | `public/calendar/templates/calendar_month.mustache` | 0 |

- Nuestros añadidos a rescatar: login «Crystalline Academic Glass» y la acción
  a `/login`; en `drawers`, la barra lateral, los FAB de Consultas y WhatsApp y
  el campo de glifos; en el cajón móvil, la navegación estilo Blackboard.
- Las dos del calendario no cambiaron en core: copiar.
- En las 5, migrar las clases de Bootstrap 4 (`sr-only`→`visually-hidden`,
  `data-toggle`→`data-bs-toggle`, `ml-/mr-`→`ms-/me-`, `font-weight-bold`→`fw-bold`).
- **Hecho cuando**: cada plantilla es la de 5.3 más nuestro diff, y ese diff
  está documentado en la cabecera de la plantilla.

### MIG-22 · Renderers que pisan a core 🟡
- **Origen**: `classes/output/core_renderer.php` (extiende
  `theme_boost\output\core_renderer`) y `classes/output/core/course_renderer.php`.
- **Qué cambia**:
  - `render_login()`: en 4.3 el padre añadía `errorformatted`, `logourl` y
    `sitename` al contexto y por eso los repetíamos. **En 5.3 el padre solo
    llama a `export_for_template()` y pinta.** Comprobar qué trae ya el
    contexto y quitar lo duplicado.
  - `page_title()` y `body_attributes()` siguen existiendo con la misma firma.
  - `course_renderer::coursecat_category()` sigue en `core_course_renderer`:
    verificar la firma.
  - El resto (`offer_*`, `site_*`, `dashboard_chips`, `teacher_whatsapp`,
    `admin_message_url`, `show_catalog`, `routes`) son métodos propios: copiar.
- **Hecho cuando**: un login fallido muestra el error y el nombre del sitio;
  las categorías del catálogo se ven con sus tarjetas.

### MIG-23 · Sitio institucional, sistema solar de niveles 🟡
- **Origen**: `layout/site.php`, `templates/local/{site,site_level_courses,mathfield}.mustache`,
  `classes/levels.php`, SCSS §G. Docs: `docs/product/levels-solar-system.md`,
  `public-site-and-routes.md`.
- **Qué cambia**: el layout tiene que seguir emitiendo `output.main_content` y
  usar `output.doctype`. El JS va en `{{#js}}`: comprobar que 5.3 lo sigue
  inyectando en un layout propio.
- **Hecho cuando**: sin sesión, los 5 planetas giran y abren su ficha, el menú
  «Niveles» muestra los cursos reales, no hay desborde a 390 px; con sesión,
  la portada es la de Boost.

### MIG-24 · Los 4 temas de nivel 🟡
- **Origen**: `theme/rm{primaria,secundaria,preu,universidad}/` — `config.php`,
  `lib.php` (concatena el SCSS del padre + `palette.scss` [+ `post.scss`]),
  `pix_plugins/theme/richimath/whitelogo.png`, `style/fonts.css`, `lang/`.
- **Qué cambia**: `requires`/`dependencies` a 5.3; recompilar cada paleta
  contra Bootstrap 5.
- **Hecho cuando**: un alumno de cada nivel ve su paleta, su logo y su
  tipografía; la nota de contraste de cada tema (AA) se mantiene.

### MIG-25 · Revisión de las secciones del SCSS contra el marcado de 5.3 🔴
El SCSS se engancha a clases de core que pueden haber cambiado. Cada sección se
revisa en su página:

| Sección | Página donde se comprueba |
|---|---|
| A, A2, A2b, A2c | Login, `/students`, portada anónima |
| B1–B4 | Barra superior, calendario (día/mes/próximos), barra lateral, dashboard, navegación móvil |
| C1–C9 | Cromo general, navegación secundaria, formularios/tablas de admin, `/my/courses.php`, perfiles, modales, catálogo de categorías |
| D1–D8 | Calificador, paginación espejo, FAB «Consultas», barra lateral compacta, chips del dashboard, portada con sesión, página de invitaciones, preguntas |
| E1–E13 | Sistema «Odyssey»: tarjetas, filas de actividad (**sin `transform` en hover**), bloques, migas, inputs |
| F1–F7 | Flujo de examen: ficha, intento, temporizador, navegación, resumen, revisión, **sin líneas entre opciones** |
| G1–G9 | Sitio institucional |
| H | Lista de usuarios del admin → **ver MIG-26** |
| I | FAB de WhatsApp |
| J | Iconos de actividad → **ver MIG-27** |

- **Hecho cuando**: cada fila verificada con captura a 1440 y 390 px; lo que ya
  no aplica se **borra**, no se deja muerto.

### MIG-26 · Lista de usuarios del admin (rehacer) 🔴
- **Origen**: SCSS §H (`#id_newfiltercontainer…`), ajuste `userfiltersdefault=email`,
  cadena `userlist` personalizada («Lista de usuarios»).
- **Qué cambia**: en 5.3 `/admin/user.php` es un informe del *report builder*
  (`core_admin\reportbuilder\local\systemreports\users`). La sección H no
  aplica, y `userfiltersdefault` no gobierna sus filtros.
- **Decisión pendiente**: aceptar el informe nuevo tal cual (ya trae filtros
  por campo, uno de ellos el correo) o rehacer los tres pedidos de entonces
  (título, filtro por defecto = correo, UX) sobre el informe.
- **Hecho cuando**: el título dice «Lista de usuarios» y se puede filtrar por
  correo sin pasos extra.

### MIG-27 · Iconos de actividad (decidir) 🔴
- **Origen**: `scripts/build-activity-icons.py`, `theme/richimath/pix_plugins/mod/*/monologo.svg` (23), SCSS §J.
  Doc: `docs/product/activity-icons.md`.
- **Qué cambia**: en 5.3 core ya pinta los iconos **en color y sin baldosa**,
  y los dibujos son nuevos (24×24, `fill="#212529"` explícito). El script
  **no funcionará tal cual**:
  1. `chat` y `survey` ya no existen → el script se detiene;
  2. los SVG traen `fill` explícito en cada trazo → el degradado en la raíz no
     llega;
  3. core los recolorea con un filtro SVG que pisaría nuestro degradado.
- **Decisión pendiente**: (a) **quedarse con los de core** —ya cumplen «en
  color, sin fondo» y soportan modo oscuro— y borrar §J y `pix_plugins/mod`; o
  (b) adaptar el script a los SVG nuevos y desactivar el filtro de core.
  Recomendación: **(a)**, y enseñárselo a Richi antes de decidir.
- **Hecho cuando**: decisión tomada y aplicada; ningún icono negro.

---

## D. Scripts, recursos y documentación

### MIG-30 · Scripts 🟡
| Script | Qué cambia |
|---|---|
| `build-routes.php` | Escribir en `public/.htaccess` |
| `build-activity-icons.py` | Según MIG-27 (borrar o adaptar) |
| `build-theme-logos.py` | Salida a `public/theme/*/pix` |
| `set-frontpage-banner.php` | Ruta del `config.php` (raíz) |
| `build-h5p-escape-room.py`, `build-kepler-questions.py` | Sin cambios de código; ver MIG-32 |
| `deploy-contabo.sh` | `config.php` y los comandos CLI (`admin/cli/`) **siguen en la raíz**, fuera de `public/` (comprobado en el árbol de 5.3); lo que cambia es el `DocumentRoot` del vhost |
| `dev-up.sh` | Según MIG-02 |
| `scripts/bbb/` | Sin cambios (es del servidor BBB, no de Moodle) |

### MIG-31 · Recursos y documentación 🟢
- Copiar `assets/` y `docs/` (diseño, planes, producto, servidor BBB) tal cual.
- Actualizar en los manuales las rutas y pantallas que cambian en 5.3:
  banco de preguntas (MIG-32), lista de usuarios (MIG-26), iconos (MIG-27).

### MIG-32 · Juego «Estación Kepler» y preguntas dinámicas 🟡
- **Origen**: `assets/h5p/estacion-kepler.h5p`, `assets/h5p/kepler-preguntas.xml`
  (5 `calculatedmulti`). Doc: `docs/product/escape-room-360.md`.
- **Qué cambia**: el H5P y las preguntas **ya importadas** viajan con la BD (el
  banco del curso se convierte en una instancia `mod_qbank` en la
  actualización). Lo que cambia es el **manual del profesor**: dónde se importa
  el XML en 5.3.
- **Hecho cuando**: el cuestionario de Kepler genera valores distintos por
  alumno tras la migración; el manual describe la importación en 5.3.

---

## E. Datos y ajustes que viven en la base de datos

No se portan: **viajan con la BD**. Pero hay que **verificarlos** después de la
migración, porque una actualización puede reiniciarlos.

| Ajuste | Valor esperado |
|---|---|
| `theme` del sitio | `richimath`; temas por usuario según plan (`allowuserthemes`) |
| `frontpage` | vacío |
| `local_richimath/prettyurls` | como esté en prod (encendido solo si el `.htaccess` está activo) |
| `theme_richimath/sidebarstyle` | `wide` o `compact`, como esté |
| BBB: `bigbluebuttonbn_server_url` / secreto | el VPS 6 |
| BBB: módulo activado y `bigbluebuttonbn_guestaccess_enabled` = 1 | |
| `hiddenuserfields`, `defaultpreference_maildisplay` = 0, permisos del rol estudiante | los de MIG-17 |
| Campos `rmwhatsapp`, `rmwhatsappon` (perfil) y `rmwhatsapp` (curso) | presentes |
| Personalización de idioma (es) | `userlist` = «Lista de usuarios» (ver MIG-26) |
| Correo saliente (SMTP Gmail) y cron de `www-data` | funcionando |
| Registro en moodle.org | se mantiene |

### MIG-40 · Comprobar módulos retirados en producción 🟢
- **Qué**: en el espejo local no hay ninguna actividad de `chat` ni `survey`
  (hay `folder` 95, `forum` 47, `quiz`, `bigbluebuttonbn`, `h5pactivity`,
  `label`). **Confirmarlo en producción** antes de migrar:
  ```sql
  SELECT m.name, COUNT(cm.id) FROM mdl_modules m
  LEFT JOIN mdl_course_modules cm ON cm.module = m.id
  WHERE m.name IN ('chat','survey') GROUP BY m.name;
  ```
- **Hecho cuando**: 0 y 0, o (si hay) decidido si se instala el plugin externo
  o se exporta su contenido antes.

---

## F. Lo que NO es migración (backlog que sigue después)

De `docs/plans/features-v2-plan.md`. No bloquea la migración. Se retoma sobre
5.3:

- **Lote A** (diseño): carpetas como carpetas, portada de curso. Las líneas del
  examen y los iconos ya están hechos en 4.3 y entran por MIG-25/27.
- **Lote C**: categoría + plan de **IB**; el tema `rmib` sigue bloqueado sin
  tablero de diseño.
- **Lote D** (IA): **5.3 no genera preguntas con IA**. Trae proveedores
  (OpenAI con *endpoint* configurable, Anthropic, DeepSeek, Gemini, Ollama,
  Bedrock, Azure) y acciones de explicar, generar texto, generar imagen y
  resumir. Kimi entra por el proveedor OpenAI cambiando el *endpoint*. La
  generación de preguntas habría que construirla encima de `generate_text`.

---

## Resumen

| Bloque | Tickets | 🔴 |
|---|---|---|
| A. Repositorio y entorno | MIG-01…03 | — |
| B. `local_richimath` | MIG-10…17 | MIG-14 |
| C. Temas | MIG-20…27 | MIG-20, 21, 25, 26, 27 |
| D. Scripts, recursos, docs | MIG-30…32 | — |
| E. Datos | MIG-40 + verificación | — |
| **Total** | **23 tickets** | **6** |
