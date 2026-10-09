# MEMORY.md — estado vivo de la migración a 5.3 (actualizar al terminar cada tarea)

Última actualización: 2026-10-09 (Fase 2: ensayo 1 + aceptación completa sobre la copia; 4 arreglos durante la aceptación → toca el ensayo 2 desde una copia nueva).

## Árboles en disco

| Carpeta | Qué es |
|---|---|
| `github/moodle` | Producción actual, **4.3.12**. Origen de lo que se porta. Docker local en `:8080` |
| `github/moodle-2026` | **4.5.14+ (Build 20261002)**, sin cambios. Solo para el salto intermedio de la Fase 2 (sin `public/`) |
| `github/moodle-2027` | **Este repo, 5.3.0 (Build 20261005)**. Docker local en `:8083` |
| `github/moodle-lang` | Paquetes de idioma ya descomprimidos: `es_4.5/` y `es_v5.3/es/` |

Imágenes Docker descargadas: `moodlehq/moodle-php-apache:8.2` y `:8.3`,
`mysql:8.0` y `:8.4`.

## Fases (docs/migration-plan.md)

| Fase | Estado |
|---|---|
| 0 Preparar | ✅ 2026-10-09 (MIG-01, 02, 03 + línea base en docs/migration/baseline-5.3) |
| 1 Portar código | ✅ 2026-10-09 — 23/23 MIG. MIG-27 = iconos de Moodle; MIG-26 = lista de usuarios de 5.3 + `core_admin/userlist` = «Lista de usuarios» (customlang) |
| 2 Ensayo con copia de producción | 🟡 ensayo 1 + aceptación FUN-01…25 hechos (22 ✅, FUN-16/25 no aplican, FUN-23 es de Fase 3); 4 arreglos en medio → falta el ensayo 2 con copia nueva ([docs/migration/phase-2-production-copy.md](docs/migration/phase-2-production-copy.md)) |
| 3 Preparar servidor | — |
| 4 Corte | — |
| 5 Después | — |

## Hechos verificados de 5.3

- Sube solo desde **4.4+** (`public/admin/environment.xml`): ruta 4.3 → 4.5 → 5.3.
- Exige **PHP 8.3** y **MySQL 8.4 / MariaDB 11.4**.
- Librerías en `public/lib/` (no hace falta `composer`); `config.php` y
  `admin/cli/` en la raíz.

## Entorno local 5.3 (Fase 0)

- `docker compose up -d` → `db` (mysql:8.4), `web` (php-apache 8.3, docroot
  `public/`), `cron` (cada 60 s). `http://localhost:8083`.
- 5.3 instalada limpia en español; `admin/cli/checks.php` → todo OK.
- Cuentas locales: `qa.admin` (admin) y `estudiante.demo` (alumno), misma
  contraseña que en el espejo 4.3 (está en la memoria del agente, nunca aquí).
- Contenido de prueba: curso `BASE53` (id 2) y «Cuestionario base 5.3» (cmid 6).

## Trampas pagadas en la Fase 0

1. **Router crítico en 5.3**: `routerconfigured = false` ya no es aceptable
   (comprobación ERROR). `FallbackResource /r.php` NO basta: PHP responde su 404
   a un `*.php` inexistente antes del fallback, y core lo prueba
   (`/lib/exampleshimroute2.php` → 302). Va un `RewriteRule` a `/r.php` si no
   es fichero ni directorio. Con `.htaccess` propio (MIG-14) esa regla tiene que
   ir al FINAL del `.htaccess`: sus reglas sustituyen a las del `<Directory>`.
2. **El test del router lo hace el servidor contra `wwwroot`**: en Docker,
   `localhost:8083` no existe dentro del contenedor → Apache escucha también
   en 8083 dentro. En producción no pasa (el dominio resuelve).
3. **`sed` con `\*` en un `command:` de compose**: la barra se pierde entre
   YAML y bash; usar patrones sin metacaracteres.
4. **Cookies de dos Moodle en el mismo `localhost`**: se pisan aunque cambie el
   puerto → `$CFG->sessioncookie = '53'` en el `config.php` de 5.3.
5. **El generador de preguntas de core necesita PHPUnit** (no instalado): para
   preguntas de prueba, importar GIFT con `qformat_gift`, y luego
   `quiz_settings::create($id)->get_grade_calculator()->recompute_quiz_sumgrades()`
   o el intento dice que ninguna pregunta tiene calificación.

## Trampas pagadas en la Fase 1

1. **Login partido en 5.x** (`theme_boost/templates/core/login_layout.mustache`):
   panel promocional de core a la izquierda («500.000.000 usuarios»). Nuestro
   override `core/login_layout` es de una columna y NO lleva la clase
   `login-layout-right-content` (core la limita a 576px).
2. **`.icon` tope 24px en 5.3** (max-width/max-height): todo logo pintado con
   `{{#pix}}` encoge. Cada regla de logo lleva `max-height/max-width: none`.
3. **Interruptor «Modo de edición» = componente React** (`.mds-switch`,
   `mds-switch--label-start` invierte el orden con `row-reverse`).
4. **`enablemyhome` / `enablemycourses`** (nuevos): apagados en instalación
   nueva, encendidos por el upgrade de un sitio existente. En local se
   encendieron a mano para imitar producción.
5. **Propósito de actividad `interface` → `interactivecontent`** (variable
   `$activity-icon-interactivecontent-bg`).
6. **Bootstrap 5**: `.bg-*` llevan `!important` (nuestros colores de badge
   también); `.custom-select`→`.form-select`; `.form-group` ya no existe
   (filas `mb-3 row fitem`).
7. **CSS en caché del navegador tras purgar**: si una medida no cuadra después
   de un `purge_caches`, recargar la página antes de concluir que la regla no
   aplica (pasó dos veces).
8. **Plantillas copiadas**: se rehacen con `git merge-file -p nuestra base_4.3
   core_5.3` (fusión a tres bandas) y se resuelven los choques a mano.
9. `pix_plugins/mod` (iconos de 4.3) y la sección §J **fuera** hasta que Richi
   decida MIG-27: tapaban los iconos nuevos de 5.3 o los ponían negros.

## Paso 1.8 verificado sin cambios de código (2026-10-09)

- **Privacidad (MIG-17)**: participantes sin permiso, perfil de curso y de
  sitio de la compañera bloqueados, compañera fuera del buscador de mensajes,
  profesor visible. La instalación nueva aplica el bloqueo desde `install.php`.
- **WhatsApp (MIG-15)**: FAB con `wa.me/51987654321` en el curso como alumno.
- **Planes (MIG-12)**: el alumno recibe `rmprimaria` al entrar.
- **Invitaciones (MIG-11)**: enlace → cuenta con el correo invitado →
  matriculado → invitación usada.
- **Invitados BBB (MIG-16)**: registro → lead → traspaso con
  `_qf__mod_bigbluebuttonbn_form_guest_login` → core acepta y consulta la sala.
  El error final («URL using bad/illegal format») es que en local no hay
  servidor BBB: 5.3 ya no trae el servidor de pruebas por defecto.
- **Datos de prueba creados**: árbol de categorías tipo producción, 14 cursos
  `PAG*` (paginación), `estudiante.dos`, actividad BBB en `BASE53`.
- **Ajustes nuevos de 5.3 que difieren en instalación limpia**: `forcelogin=1`
  (rompía el sitio público), `enablemyhome/enablemycourses=0`. En local se
  pusieron como producción; están en la sección E de los tickets.

## Paso 1.9 (MIG-25) — lo que 5.3 cambió en pantallas (2026-10-09)

1. **Navegación secundaria = componente React `core/nav/Nav`** (`a.mds-nav-pill`,
   `--selected`, punto indicador). Mueve pestañas a «Más» si la lista es más
   alta que la barra (`Nav.tsx: menu.offsetHeight > container.offsetHeight`);
   core fija la barra en `$moremenu-height` → crecerla lo que añade el
   control segmentado o TODO acaba en «Más».
2. **Cajones «anclados» en páginas de curso** (tarjeta gris redondeada, tope
   de altura): se anulan en nuestro layout con barra lateral.
3. **`.que` es flex en FILA en 5.3**: nuestra barra de estado a todo el ancho
   expulsaba la pregunta de la tarjeta → preguntas EN BLANCO. Ahora columna.
4. **Iconos de actividad**: quitar el tamaño/padding de baldosa de 4.3 (5.3 los
   pinta sin baldosa y el contenedor mide 32px).
5. **§H (lista de usuarios) borrada**: el formulario de 4.3 no existe en 5.3.
6. **El calificador desborda la página en horizontal**: es core 5.3 (columnas
   de 200px), igual en Boost puro. No se toca.
7. **`frontpageloggedin`**: 5.3 nuevo trae 6 (lista de cursos); producción usa
   2 (categorías, con nuestras tarjetas). Puesto a 2 en local.
8. **Pruebas con agent-browser**: el tour de bienvenida de Moodle se come los
   clics (`button[data-role=end]` lo cierra); el desplegable de categorías se
   abre con el `h3`, no con el enlace; tras `purge_caches` hay que
   `build_theme_css.php` ANTES de abrir el navegador (si no, guarda el CSS
   viejo con la URL nueva — pasó 3 veces).

## Paso 1.11 (MIG-30/31/32)

- `scripts/` de 5.3 YA EXISTÍA en core (lib, packages, swizzle.mjs…): lo nuestro
  se añadió sin sobrescribir (`rsync --ignore-existing`).
- `build-theme-logos.py` regenera idéntico en `public/theme`.
- `build-activity-icons.py` NO correr hasta MIG-27.
- `dev-up.sh` construye el CSS de los 5 temas al final.
- Manual de Kepler: importar en «Bancos de preguntas» (mod_qbank).

## Decisiones de Richi aplicadas (2026-10-09)

- **MIG-27**: iconos de Moodle 5.3. `scripts/build-activity-icons.py` retirado.
  El color de cada tipo de actividad sale de `$activity-icon-*-bg` de la
  paleta de cada nivel.
- **MIG-26**: lista de usuarios de 5.3 tal cual + título por personalización de
  idioma `core_admin/userlist` = «Lista de usuarios»
  (`public/admin/tool/customlang/cli/import.php --lang=es --source=<dir con admin.php> --checkin`).
  Vive en BD/`moodledata/lang/es_local`: verificar tras el corte.
- **Privacidad (FUN-21) verificada en 5.3** con las 10 pruebas de
  `docs/product/student-privacy.md`, incluida la pestaña nueva
  «Actividades» (`/course/overview.php`): el alumno solo ve lo suyo.
- `user_can_view_profile()` está obsoleta en 5.3 (usar
  `\core\user::can_view_profile()`); nuestro código no la usa.

## Fase 2 — copia de producción y ensayo (2026-10-09)

- **Copia** en `~/richimath-prod-copy` (fuera de repos, `chmod 700`; datos
  reales: borrar al cerrar la fase). Origen: MySQL **8.0.46**, código
  `3c044eae`, BD 43 MB (3,2 MB gz), `moodledata` 220 MB (102 MB gz).
  Procedimiento: [docs/migration/phase-2-production-copy.md](docs/migration/phase-2-production-copy.md).
- **Ensayo**: `scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy` →
  proyecto Docker `rmrehearsal` en **:8084** (no toca :8083 ni :8080). Parte
  siempre de cero. Admin `qa.admin` con contraseña aleatoria en
  `~/richimath-prod-copy/work/qa-admin.txt`; alumnos con «Entrar como».
- **Sección E**: `scripts/check-db-settings.php` (OK/FAIL/INFO). En la copia:
  todo OK salvo `bigbluebuttonbn_guestaccess_enabled = 0`, que venía así de
  producción (FUN-19 no funcionaba en 4.3). **Encendido en producción el
  2026-10-09** (global + un curso); las copias anteriores lo traen apagado.
- **MIG-40 ✅**: 0 chat, 0 survey. Producción: folder 152, forum 27, quiz 20,
  BBB 4, url 1, h5pactivity 1. Producción **no está registrada** en moodle.org y
  **no tiene customlang** (sin `es_local`).
- **Humo en 5.3 con datos reales** ✅: portada 1440/390, dashboard admin,
  alumna de primaria (dashboard 1440/390, curso 390, revisión de un intento del
  3-oct con nota y fórmulas).

### Trampas pagadas en la Fase 2

1. **`upgrade.php` instala solo el paquete `es` de su versión** (la copia trae
   el de 4.3). El VPS necesita salida a `download.moodle.org` en el corte.
2. **El upgrade encola `build_installed_themes_task`**: ~3 min de CSS dentro de
   la ventana. NO purgar después o se compila dos veces.
3. **El CLI de Moodle se mueve a la carpeta del script** (`lib/setup.php`, en 4.3
   y en 5.3): una ruta relativa se busca ahí. Por eso `customlang/cli/import.php
   --source=assets/...` decía «Falta archivo». Rutas absolutas, o guardar
   `getcwd()` antes del `require config.php` (lo hace ya
   `scripts/set-frontpage-banner.php`).
4. **Collation — unificada en producción el 2026-10-09 19:31**: el `config.php`
   (líneas 18 y 30, repetida) y las 5 tablas `local_richimath_*` estaban en
   `utf8mb4_general_ci`; las 483 de core, en `unicode_ci`. Hecho con `sed` +
   `admin/cli/mysql_collation.php` (0 errores, 488/488, esquema OK). Respaldo:
   `/root/moodle-backups/pre-collation-2026-10-09-1931.sql.gz` (borrar en unos
   días). Las copias anteriores a esa hora aún necesitan el paso del ensayo.
10. **Producción, BD**: usuario `moodleuser` con `caching_sha2_password`
    (MySQL 8.4 sin `mysql_native_password` no le afecta); `root` por
    `auth_socket`. VPS en **Ubuntu 24.04**, que trae MySQL 8.0: el 8.4 viene
    del repo APT oficial de MySQL.
5. **Copia = identidad de producción**: solo podría salir correo (SMTP Gmail);
   `noemailever` en el config del ensayo. `airnotifier` activo pero sin clave,
   sin OAuth2 de sistema. Sin cron en el ensayo (el aviso de `checks.php` es
   esperado).
6. **Editar un script mientras corre**: bash siguió con la versión vieja (el
   editor reemplaza el fichero). Relanzar para probar los cambios.
7. **`pgrep -f patrón` dentro de un `bash -c` que contiene el patrón** se
   encuentra a sí mismo: el bucle de espera no acaba nunca.
8. **`version.php` hace `die()` fuera de Moodle**: la versión, con
   `admin/cli/cfg.php --name=release`.
9. Con «Entrar como», el aviso «Usted se ha identificado como…» se recorta en
   la cabecera de la barra lateral (solo lo ve el admin).

## Ajustes de UI pedidos sobre 5.3 (2026-10-09)

- **Cajón derecho de bloques**: 5.3 «ancla» los cajones junto al contenido en
  `pagelayout-standard` y `body.limitedwidth` (cuestionario, actividades,
  admin). Core pone `left` desde reglas `:has(#page.drawers…)` (dos IDs): solo
  `!important` las vence. Vuelve al borde, con el botón de cerrar arriba a la
  derecha, como en 4.3 (`docs/theme-richimath.md`).
- **Caja de solución del examen (`.que .outcome`)**: azul hielo `#f4f8ff` con
  borde fino y marca de agua RM (el crema se rechazó por «amarillo»), y un
  color por nivel (veredicto, título, pasos, fórmulas, respuesta).
  **Colores fijos, no de la paleta del nivel**: `rmpreu` tiene `$od-primary`
  ROJO y `rmsecundaria`/`rmuniversidad` `$od-secondary` turquesa.
- **Fórmulas MathJax anchas**: `.que` tiene `overflow: hidden` y las cortaba en
  el móvil → `mjx-container[display="true"]` con scroll horizontal.
- **Fechas de actividad** (`.activity-dates`): rejilla `1fr 1fr` sin salto que
  core solo apila en viewport estrecho → `auto-fit` al ancho real.

### Trampas de verificación

1. **`html { scroll-behavior: smooth }`**: el clic por coordenadas de
   agent-browser llega antes de que acabe el desplazamiento (el login a 390 «no
   enviaba»). Usar `find role button click --name …` o Enter. No es un fallo
   para usuarios reales.
2. **MathJax tarda segundos** en componer una revisión larga: esperar antes de
   capturar o se ve TeX crudo.
3. **En Boost la página se desplaza dentro de `#page`**, no en `window`:
   `document.querySelector('#page').scrollTop`.
4. Compilar el CSS (≈2,5 min por entorno) en `:8084` y `:8083` a la vez: los dos
   montan el mismo árbol y cada uno tiene su caché.

## Aceptación sobre la copia (2026-10-09) — trampas

1. **5.3 crea la página de ajustes de cada tema OCULTA**
   (`admin/settings/appearance.php`) y solo la lista si el tema la desoculta:
   `$settings->hidden = false;` en `theme/richimath/settings.php`. Sin eso,
   Apariencia → Temas → Richimath daba «Error de sección».
2. **`drawers.js` aparta los cajones al desplazar en horizontal**
   (`displaceDrawers`, calificador): el ancla de la columna fija solo necesita
   el ancho de nuestra barra lateral.
3. **Botones de cajón en móvil a `calc(99vh - navbar × 2.5)`** (5.3): el carril
   de «Consultas» (7rem) los tapaba → con `.drawer-right-toggle` en la página,
   los FAB suben encima.
4. **`user_create_user()` obsoleta en 5.3** (MDL-82650) → `\core\user::create_user()`,
   misma firma y misma política de contraseñas. Ningún otro uso obsoleto en
   nuestro código (comprobado contra `deprecatedlib.php` y `#[deprecated]`).
5. **«Entrar como» no dispara `user_loggedin`**: los temas por plan se prueban
   con un inicio de sesión real → `qa.alumno` en la copia (contraseña en
   `~/richimath-prod-copy/work/qa-alumno.txt`).
6. **Las actividades BBB de la copia comparten sala con producción**: para
   probar contra el VPS 6, crear una actividad NUEVA (meetingid aleatorio), sin
   grabación, y cerrar la reunión al acabar (`mod_bigbluebuttonbn_end_meeting`).
7. **En producción el usuario 5 es una alumna real**, no `estudiante.demo` como
   en el espejo 4.3: no fiarse de los ids del espejo.
8. agent-browser: el enlace «Terminar intento…» lo intercepta el JS del intento
   (ir a su `href`); el modal de «Enviar todo y terminar» no abre bajo
   automatización (`#frm-finishattempt.submit()`); la búsqueda de mensajes se
   prueba con `core_message_message_search_users`; un elemento `position: fixed`
   tiene `offsetParent` nulo (medir con `getBoundingClientRect`).

## Decisiones de Richi aplicadas (2026-10-09, tras la aceptación)

- **Favicon**: el del sitio (Apariencia → Logos, icono RM) en todos los niveles.
- **Banner de portada (FUN-25)**: instalado en la copia; en producción, con
  `sudo -u www-data php scripts/set-frontpage-banner.php /var/www/html/assets/frontpage-banner.jpg`.
  En 5.3 la cabecera de sección lleva `.d-flex` (`!important`): la sección D6
  necesita `display: none !important` para quitar la franja vacía.
- **«Consultas» y WhatsApp fuera del intento de examen** (attempt y summary):
  `core_renderer::in_quiz_attempt()`; vuelven en la revisión.
- La barra secundaria de 5.3 (React) pinta un instante las pestañas en dos
  líneas antes de mandarlas a «Más»: esperar antes de capturar, no es un fallo.
