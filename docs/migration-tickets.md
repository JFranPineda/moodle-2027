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
10. **Router de Moodle** (`r.php`). En 5.3 un router sin configurar es una
    **comprobación crítica**: hay que configurarlo en el servidor web y poner
    `$CFG->routerconfigured = true` (verificado en la Fase 0).

---

## Cómo leer los tickets

Hay **dos capas**, y cada función aparece en las dos:

- **`FUN-xx` — tickets funcionales**: lo que ve el usuario (alumno, profesor,
  admin o visitante), con **los commits de `github/moodle` que lo
  construyeron**. Son la lista de comprobación de «¿sigue todo funcionando?».
- **`MIG-xx` — tickets técnicos**: el trabajo de portar el código a 5.3
  (plantillas, renderers, SCSS, hooks, rutas). Una función depende de uno o
  varios `MIG`; un `MIG` sirve a varias funciones.

Una función está migrada cuando **su `FUN` cumple su «Hecho cuando»** en 5.3.
Los `MIG` son el camino, no la meta.

Leyenda de riesgo en 5.3: 🟢 copiar y verificar · 🟡 adaptar · 🔴 rehacer sobre
la base nueva.

Todas las comprobaciones se hacen a **1440 y 390 px**, como **admin** y como
**alumno** (la regla del repo).

---

## 1. Tickets funcionales (lo que ve el usuario)

### Marca e identidad

#### FUN-01 · Marca «Richi Math» y título de cada pestaña 🟢
- **Qué ve el usuario**: la marca escrita como «Richi Math», nunca «Richie»
  (T-01), y cada pestaña del navegador con el formato «Página | Richi Academy».
- **Commits**: `27e251f6` (título en la portada), `32e49d2a` (título de marca en
  todas las páginas).
- **Piezas**: `core_renderer::page_title()`, cadenas en+es del tema.
- **En 5.3**: `page_title()` sigue existiendo con la misma firma → MIG-22.
- **Hecho cuando**: dashboard, curso, examen y login muestran el título de
  marca; ningún texto dice «Richie».

#### FUN-02 · Logos de Richi Math: barra lateral, login, sitio web y uno por nivel 🟡
- **Qué ve el usuario**:
  - **barra lateral** del aula: logo RM blanco, de **color distinto por nivel**
    (Primaria, Secundaria, Pre Uni, Universidad), con su **favicon**;
  - **login** (`/login`): el logo sobre la tarjeta, junto con el nombre del sitio;
  - **sitio web** (`/`): la marca web en la esquina de la barra superior y en el
    pie de página.
- **Commits**: `05b83936` (logo blanco en la barra lateral, login por capas),
  `21a4b013` (un logo RM por nivel que pisa el del padre), `19879a3e` (marcas
  v3; vuelve el nombre del sitio a la tarjeta de login), `c4a93643` (marca web
  en la esquina del sitio institucional).
- **Piezas**: `theme/richimath/pix/{whitelogo,loginlogo,weblogo,favicon}`,
  `theme/rm*/pix_plugins/theme/richimath/whitelogo.png` y `pix/favicon.ico`,
  `scripts/build-theme-logos.py`, fuentes en `assets/logo-*.svg` y
  `docs/design/logos/` (v3 y web_logo).
- **En 5.3**: los PNG se copian a `public/theme/…`; el script tiene que
  escribir ahí (MIG-30). Ojo: 5.x tiene sus propios ajustes de logo
  (*Apariencia → Logos*): dejarlos vacíos para que no compitan con los nuestros.
- **Hecho cuando**: cada uno de los 4 temas de nivel muestra su logo y su
  favicon en la barra lateral; `/login` muestra logo + nombre; `/` muestra la
  marca web arriba a la izquierda y en el pie.

### Entrada y sitio público

#### FUN-03 · Página de login «Crystalline Academic Glass» 🔴
- **Qué ve el usuario**: el login partido en dos (arte + tarjeta de vidrio),
  con halos, glifos matemáticos animados al fondo, la etiqueta corta
  «Usuario», el botón para mostrar la contraseña y el formulario que postea a
  `/login`.
- **Commits**: `ce7a83d9` (fondo propio), `e3143f87` (arte responsivo),
  `05b83936` (login por capas), `c7d85631` (etiqueta corta de usuario),
  `8c45ed45` (animación de glifos), `d40dad07` (parcial `mathfield` único),
  `9991288e` (rehecho como Crystalline Academic Glass), `19879a3e` (nombre del
  sitio en la tarjeta).
- **Piezas**: `templates/core/loginform.mustache`, `templates/local/mathfield.mustache`,
  SCSS §A y §A2c, `render_login()`, `pix/loginground.jpg`, `loginsquare.png`.
  Doc: `docs/product/ux-pro-login-and-exam-flow.md`.
- **En 5.3**: **la plantilla de login de core cambió 304 líneas** → rehacer
  sobre la de 5.3 (MIG-21); `render_login()` del padre ya no añade
  `errorformatted`/`logourl`/`sitename` (MIG-22).
- **Hecho cuando**: login correcto entra; login **fallido** muestra el error y
  se queda en `/login`; «¿Olvidó su contraseña?» funciona; los glifos se ven y
  se paran con *reducir movimiento*; a 390 px la tarjeta ocupa la pantalla sin
  desbordar.

#### FUN-04 · Sitio institucional en `/`, catálogo en `/students` y URL limpias 🔴
- **Qué ve el visitante sin sesión**: la portada institucional (obsidiana + oro,
  titular, cifras reales de la academia, divisiones, método, llamada a la
  acción, pie), el catálogo de cursos en `/students` y direcciones sin `.php`
  (`/login`, `/students`, `/recover`…).
- **Commits**: `d094d82a` y `22383d7a` (oferta pre-login, T-02), `482934ea`
  (landing dinámica con el catálogo real), `6bbf5c54` (sitio institucional,
  `/students`, URL limpias), `785ff69c`, `ff345f50`, `4d6d1c93`, `01a5b985`
  (arreglos de rutas y del interruptor `prettyurls`), `69759873` (cifras reales,
  sin banner duplicado).
- **Piezas**: `layout/site.php`, `templates/local/{site,students}.mustache`,
  SCSS §A2, §A2b y §G, `local/richimath/{students.php, routes.php, classes/routes.php}`,
  `.htaccess`, `scripts/build-routes.php`. Doc: `docs/product/public-site-and-routes.md`.
- **En 5.3**: MIG-13, MIG-14 (el `.htaccess` pasa a `public/` y convive con el
  router de Moodle), MIG-23.
- **Hecho cuando**: lo de MIG-14 (cada ruta en sus dos direcciones, login
  fallido se queda en `/login`) y la portada se ve igual que en 4.3.

#### FUN-05 · Sistema solar de niveles y menú «Niveles» 🟡
- **Qué ve el visitante**: en vez del formulario de login, 5 planetas
  (Primaria, Secundaria, Pre universitario, Universitario, IB) orbitando la
  academia; cada planeta abre la ficha de sus cursos reales (solo lectura); en
  la barra superior, «Niveles ▾» con los cursos del nivel bajo el puntero.
- **Commits**: `e91c7f93`.
- **Piezas**: `classes/levels.php`, `templates/local/{site,site_level_courses}.mustache`,
  SCSS §G4, §G5, §G5b. Doc: `docs/product/levels-solar-system.md`.
- **En 5.3**: MIG-23 (comprobar que `{{#js}}` se sigue inyectando en un layout
  propio).
- **Hecho cuando**: los planetas giran y se paran al pasar el ratón; tocar uno
  abre su ficha y Escape la cierra; el menú cambia de nivel al pasar el ratón;
  IB dice «Próximamente» mientras no tenga categoría; sin desborde a 390 px.

### Navegación del aula

#### FUN-06 · Barra lateral (modos Blackboard y Canvas) y navegación móvil 🔴
- **Qué ve el usuario**: barra lateral fija a la izquierda en pantallas grandes,
  con dos modos (Blackboard, ancha; **Canvas, compacta con icono y texto
  debajo**, por defecto — T-04); el ítem activo sigue a la URL; enlace al
  catálogo **solo para staff**; por debajo de `lg`, un cajón móvil con el mismo
  estilo.
- **Commits**: `9a26590f` (barra lateral), `dfb213a9` (activo por URL),
  `445c6941` (cajón móvil), `6c8b98a2` (enlace al catálogo), `340bf352`
  (catálogo solo para staff), `3c88d834` (ajuste Canvas), `c0a3a79e` (nombres
  Blackboard/Canvas, Canvas por defecto).
- **Piezas**: `templates/local/sidebar.mustache`,
  `templates/theme_boost/{drawers,primary-drawer-mobile}.mustache`,
  `settings.php` (`theme_richimath/sidebarstyle`), SCSS §B2b, §B4, §D4.
- **En 5.3**: `drawers` y `primary-drawer-mobile` cambiaron en core (50 y 31
  líneas) → MIG-21.
- **Hecho cuando**: los dos modos se alternan desde *Apariencia → Temas →
  Richimath*; un alumno no ve el enlace al catálogo; el cajón móvil abre y
  cierra a 390 px.

#### FUN-07 · Calendario estilo Blackboard 🟢
- **Qué ve el usuario**: calendario del mes con rejilla limpia, vista de día por
  horas con **línea de la hora actual** y franja de semana centrada, calendario
  del dashboard a ancho completo.
- **Commits**: `ce7a83d9` (calendario Blackboard), `9682fe23` (vista de día por
  horas), `9a26590f` (calendario a ancho completo).
- **Piezas**: `templates/core_calendar/{day_detailed,calendar_month}.mustache`,
  SCSS §B2, §B2a, §B2c, §B2d.
- **En 5.3**: las dos plantillas de core **no cambiaron** (0 líneas) → copiar.
- **Hecho cuando**: mes, día y próximos eventos se ven como en 4.3; la línea de
  la hora actual aparece en la vista de día.

#### FUN-08 · Dashboard: chips de acceso rápido y animación 🟡
- **Qué ve el usuario**: arriba del dashboard, chips con sus cursos
  (y, para staff, las categorías) para entrar sin bajar (T-03); los glifos
  matemáticos de fondo.
- **Commits**: `e736f304` (chips y animación), `d40dad07` (parcial único).
- **Piezas**: `core_renderer::dashboard_chips()`, `drawers.mustache`, SCSS §B3,
  §D5.
- **En 5.3**: depende de MIG-21 (`drawers`).
- **Hecho cuando**: un alumno con 3 cursos ve 3 chips; más de 12 muestran «ver
  todos».

#### FUN-09 · «Mis cursos»: tarjetas y paginación también arriba 🟡
- **Qué ve el usuario**: `/my/courses.php` con tarjetas estilo Blackboard y la
  barra de paginación **repetida arriba**, para cambiar de página sin bajar
  hasta el final (T-08).
- **Commits**: `d750035d` (piel Blackboard Ultra), `3c88d834` (paginación
  espejo).
- **Piezas**: JS en `drawers.mustache` (clona la barra de `block_myoverview` y
  la vuelve a sincronizar con un `MutationObserver`), SCSS §C4 y §D2.
- **En 5.3**: el JS depende de `[data-region="paging-bar"]` y
  `[data-region="paging-control-container"]` de `block_myoverview`: **comprobar
  que existen** en 5.3. Si no, la barra de arriba desaparece sin error.
- **Hecho cuando**: con más de una página de cursos, la paginación de arriba
  existe y cambia de página igual que la de abajo.

#### FUN-10 · Botón flotante «Consultas» 🟢
- **Qué ve el usuario**: un botón fijo abajo a la derecha que abre el chat con
  el administrador desde cualquier página (T-10), apilado sin tapar el «?» de
  Boost ni el de WhatsApp.
- **Commits**: `3c88d834`.
- **Piezas**: `core_renderer::admin_message_url()`, `drawers.mustache`, SCSS §D3.
- **En 5.3**: depende de MIG-21.
- **Hecho cuando**: abre la conversación con el admin; a 390 px no tapa los
  otros botones.

### Cursos, categorías y actividades

#### FUN-11 · Niveles y subniveles (categorías y subcategorías) bonitos 🟡
- **Qué ve el usuario**: el catálogo de categorías (`/course/index.php`) y la
  portada con sesión como **tarjetas de categoría**, cada una con **un color de
  la paleta de su nivel**; al abrir una categoría, sus subcategorías (grados,
  universidades, ciclos) se despliegan dentro de la tarjeta, sin un panel
  extra detrás; las categorías ocultas no se muestran, tampoco a staff si así
  se configura.
- **Commits**: `445c6941` (tarjetas de catálogo de categorías), `75657932`
  (tarjetas en la portada con sesión), `27e251f6` (tarjetas renovadas),
  `b0ead170` (un color por nivel), `90ac6f5b` (sin panel detrás de los hijos),
  `961d48f2` (ocultar categorías ocultas también a staff, como ajuste del sitio),
  `340bf352` (los enlaces al catálogo, solo staff).
- **Piezas**: `classes/output/core/course_renderer.php` (`coursecat_category()`),
  `local/richimath/classes/category_visibility.php` y su ajuste, SCSS §C9 y
  §D6, paletas en `theme/rm*/scss/palette.scss`.
- **En 5.3**: `coursecat_category()` sigue en `core_course_renderer` (comprobar
  la firma, MIG-22); el marcado de `course/index.php` hay que revisarlo con
  MIG-25.
- **Hecho cuando**: ESCOLAR → NIVEL PRIMARIA → QUINTO GRADO se despliega en
  tarjetas con el color de cada nivel; «Categoría 1» (oculta) no aparece;
  Universidad → Universidad del Pacífico → Ciclo regular igual.

#### FUN-12 · Página del curso: cabecera, secciones y filas de actividad 🔴
- **Qué ve el usuario**: cabecera del curso, cada sección como tarjeta, cada
  actividad como fila tarjeta con su icono; el menú ⋮ de edición **sin
  parpadeo**.
- **Commits**: `9a09cb3c` (página del curso Blackboard Ultra), `32e49d2a` y
  `168e7d3c` (sistema de diseño Odyssey), `acc5ff59` (⋮ que parpadeaba detrás
  de la fila siguiente).
- **Piezas**: SCSS §C1, §E1, §E6 (**sin `transform` en el hover**: es lo que
  causaba el parpadeo), §E7.
- **En 5.3**: el marcado del curso (formato reactivo) puede haber cambiado →
  MIG-25.
- **Hecho cuando**: en modo edición, el ⋮ de cualquier actividad abre su menú
  **encima** de la fila siguiente y se puede clicar cada opción, incluido el
  submenú «Disponibilidad».

#### FUN-13 · Iconos de actividad en color, sin baldosa 🔴
- **Qué ve el usuario**: los iconos de actividades y recursos en colores vivos
  y sin fondo, en el curso, en el selector y en los bloques.
- **Commits**: `27e251f6` (paleta de iconos de marca), `2ab510bb` (iconos en
  degradado sin baldosa).
- **En 5.3**: core ya los trae en color y sin baldosa → **decidir** (MIG-27).
- **Hecho cuando**: decisión tomada con Richi; ningún icono negro ni con
  baldosa.

### Evaluaciones

#### FUN-14 · UX de evaluaciones («Imperial Academic Precision») 🔴
- **Qué ve el alumno**: las cuatro pantallas del cuestionario rediseñadas:
  - **ficha** (`view.php`) como lista de metadatos;
  - **intento**: cinta de identidad, temporizador en monoespaciada, tarjetas
    de pregunta con el **arte de Richi Math detrás** (marca de agua RM) y
    opciones **sin líneas entre ellas**;
  - **navegación** como matriz de preguntas;
  - **resumen** y **revisión** como hoja de calificación.

  Todo con **el color de su nivel** (el oro del diseño original es un *rol*, no
  un color) y sin la caja vacía de intento.
- **Commits**: `64635041` (arte detrás de las preguntas), `51852e9b` (flujo de
  examen por nivel), `b3209f54` (JetBrains Mono en todos los temas),
  `ea90e68d` (oculta la caja vacía de intento), `19879a3e` (arte v3 del
  examen), `3c6c785e` (sin líneas entre opciones).
- **Piezas**: SCSS §D8 y §F1–F7, `pix/examground.png`, `style/fonts.css`, el
  token `$od-exam-accent` de cada paleta. Docs:
  `docs/product/ux-pro-login-and-exam-flow.md`, tablero en `docs/design/`.
- **En 5.3**: el cuestionario cambió entre 4.4 y 5.3 → revisar §D8 y §F1–F7
  pantalla por pantalla (MIG-25). El banco de preguntas ahora vive en
  `mod_qbank` (no afecta al alumno).
- **Hecho cuando**: con un alumno de cada nivel: ficha, intento, temporizador,
  navegación, resumen y revisión se ven con su color; la marca de agua se ve
  detrás del enunciado sin tapar fórmulas; ninguna línea entre opciones; a
  390 px el temporizador y la navegación siguen visibles.

#### FUN-15 · Calificador: menús ⋮ visibles y tabla a ancho completo 🟡
- **Qué ve el profesor**: el informe del calificador con cabeceras legibles,
  los menús ⋮ de cada columna sin tapar el contenido (T-05) y la tabla usando
  todo el ancho.
- **Commits**: `3c88d834` (⋮ del calificador), `716cc750` (ancho completo,
  pie discreto).
- **Piezas**: SCSS §D1.
- **En 5.3**: el calificador cambió en 4.4–4.5 → MIG-25.
- **Hecho cuando**: en un curso con 10 alumnos, cada ⋮ de columna abre su menú
  sin tapar la columna congelada de nombres.

#### FUN-16 · Juego «Estación Kepler» (escape room 360) con valores por alumno 🟡
- **Qué ve el alumno**: una sala 360 en H5P con cuatro pistas y, después, 5
  preguntas con **números distintos para cada alumno**.
- **Commits**: `f69f0634` (escape room 360), `02f7c3db` (números distintos por
  alumno).
- **Piezas**: `assets/h5p/{estacion-kepler.h5p, kepler-preguntas.xml}`,
  `scripts/build-{h5p-escape-room,kepler-questions}.py`.
  Doc: `docs/product/escape-room-360.md`.
- **En 5.3**: MIG-32.
- **Hecho cuando**: dos alumnos distintos ven números distintos en la misma
  pregunta; el H5P abre y sus cuatro pistas funcionan.

### Cuentas, acceso y comunicación

#### FUN-17 · Un tema por nivel, asignado por plan 🟡
- **Qué ve el usuario**: cada alumno entra a un aula con **la paleta, la
  tipografía y el logo de su nivel** (Primaria, Secundaria, Pre Uni en rojo y
  negro, Universidad en STEM con su barra estilo Blackboard), según el plan que
  le asigna el admin.
- **Commits**: `d0fbad72` (un tema por nivel, planes y apariencias),
  `bcbf542d` (capas del módulo de planes), `25b5bcc9` (cada plan con la
  apariencia de su nivel), `ad6ca6a5` (barra Blackboard de Universidad),
  `52f2d428` (Pre Uni en Red & Black, Universidad en Dynamic STEM), `21a4b013`
  (logo por nivel).
- **Piezas**: `local/richimath/{plans.php, userplans.php, classes/plans/*, classes/observer.php}`,
  `theme/rm*/`. Doc: `docs/product/plans-and-appearances.md`.
- **En 5.3**: MIG-12, MIG-24.
- **Hecho cuando**: un usuario de cada plan entra y ve su tema; cambiar el plan
  desde el admin se nota en el siguiente inicio de sesión.

#### FUN-18 · Invitaciones por correo y enlace compartido del curso 🟢
- **Qué ve el profesor**: «Invitar alumnos» en el curso; escribe correos y cada
  uno recibe un enlace personal de un solo uso; o comparte **un único enlace
  del curso** que solo deja entrar a los correos de la lista. El invitado
  escribe su propio nombre al crear la cuenta.
- **Commits**: `2acf7aab` (invitaciones de un solo uso), `24168779` (solo
  correo; el invitado pone su nombre), `840315ff` (copiar enlace en HTTP;
  página rediseñada), `47a1a455` (un enlace compartido por curso).
- **Piezas**: `local/richimath/{invite.php, accept.php, classes/invitation.php, classes/courselink.php, classes/form/*, templates/invite.mustache}`,
  SCSS §D7. Docs: `docs/product/{invitations,matricular-por-correo}.md`.
- **En 5.3**: MIG-11.
- **Hecho cuando**: invitación por correo → el invitado crea su cuenta y queda
  matriculado; el enlace compartido rechaza un correo que no está en la lista.

#### FUN-19 · Enlace de invitado a una clase de BigBlueButton (sin matricular) 🟡
- **Qué ve el profesor**: en una actividad BBB, **«Invitar visitantes a esta
  sesión»**: copia un enlace o lo manda por correo. **Qué ve el invitado**: un
  formulario (nombres, apellidos, universidad, correo, permiso de marketing
  opcional) y luego **entra a la sala sin cuenta y sin contraseña**. **Qué ve el
  admin**: *Usuarios → Cuentas → Asistentes a sesiones abiertas*, con el CSV de
  quienes aceptaron que se les escriba.
- **Commits**: `e568dac0` (registro de invitados y lead), `27a21d53`
  (identificador del formulario de core en el traspaso: sin él, el invitado
  veía un formulario de contraseña vacío), `db7a308a` (manual del profesor).
- **Piezas**: `local/richimath/{session.php, sessionlink.php, leads.php, classes/lead.php, classes/form/guest_register_form.php}`,
  tabla `local_richimath_lead`, enlace en el menú de la actividad
  (`extend_settings_navigation`). Docs: `docs/product/open-session-leads.md`,
  `docs/product/v4/guest-class-invitation.md`.
- **En 5.3**: MIG-16 (el traspaso depende del nombre de clase del formulario de
  invitado de core: si cambió, falla en silencio).
- **Hecho cuando**: como anónimo: registro → lead guardado → «Entrar a la
  clase» → core acepta (o dice «La reunión aún no ha comenzado»); el mismo
  correo dos veces suma visitas sin duplicar; el CSV solo trae a quienes
  aceptaron.

#### FUN-20 · Botón de WhatsApp del profesor 🟢
- **Qué ve el alumno**: un botón verde en el curso que abre el WhatsApp de su
  profesor, si el profesor lo activó en su perfil o el curso lo fuerza.
- **Commits**: `325035b2`.
- **En 5.3**: MIG-15.
- **Hecho cuando**: la matriz de 8 casos de `docs/product/whatsapp-teacher-button.md`.

#### FUN-21 · Privacidad entre alumnos 🟢
- **Qué ve el alumno**: solo lo suyo; ni participantes, ni perfiles, ni
  correos, ni cursos de sus compañeros.
- **Commits**: `6201ab5e`.
- **En 5.3**: MIG-17.
- **Hecho cuando**: la tabla «Verificado» de `docs/product/student-privacy.md`.

#### FUN-22 · Lista de usuarios del admin 🔴
- **Qué ve el admin**: `/admin/user.php` titulada «Lista de usuarios», con el
  filtro por **correo** a mano y la página legible.
- **Commits**: `8abd0b7b` (estilos), `b1c85d8e` y `6d42867a` (los dos ajustes
  en BD y la cadena personalizada).
- **En 5.3**: la página es otra (*report builder*) → MIG-26.
- **Hecho cuando**: lo de MIG-26.

### Operación

#### FUN-23 · Despliegue de un comando en Contabo 🟡
- **Qué hace**: `scripts/deploy-contabo.sh` actualiza el código, corre el
  upgrade, purga cachés y comprueba que el sitio y una URL limpia responden.
- **Commits**: `ea499ced`, `d74d1da5`, `15ee495e`.
- **En 5.3**: MIG-30 (el `DocumentRoot` cambia; `config.php` y `admin/cli/`
  siguen en la raíz).
- **Hecho cuando**: el script corre de punta a punta contra 5.3 en el ensayo.

#### FUN-24 · Configuración del servidor BigBlueButton versionada 🟢
- **Qué es**: la ventana nocturna de procesado de grabaciones (23:00–06:00 Lima)
  y la configuración del VPS 6, guardadas en el repo.
- **Commits**: `d1e4feeb`, `9609daa3`, `895450f0`.
- **Piezas**: `scripts/bbb/`, `docs/bbb-server-setup.md`.
- **En 5.3**: no depende de Moodle → copiar la carpeta (MIG-30). Lo que sí hay
  que comprobar es que el módulo BBB de 5.3 sigue conectando con el servidor
  BBB 3.0 del VPS 6.
- **Hecho cuando**: un profesor crea e inicia una clase contra el VPS 6 desde
  5.3.

#### FUN-25 · Portada con sesión: banner de bienvenida 🟢
- **Qué ve el usuario con sesión**: el banner de bienvenida de Richi Math en la
  portada del aula, encima de las tarjetas de categoría.
- **Commits**: `75657932`.
- **Piezas**: `scripts/set-frontpage-banner.php` (lo crea como *label* en la BD,
  con `idnumber` `richimath-frontpage-banner`), `assets/frontpage-banner.jpg`,
  SCSS §D6. Doc: `docs/product/frontpage-content.md`.
- **En 5.3**: el *label* viaja con la BD; el script tiene que encontrar
  `config.php` en la raíz (MIG-30).
- **Hecho cuando**: la portada con sesión muestra el banner una sola vez.

---

## 2. Trazabilidad: cada commit de `github/moodle`, en su ticket

Los 142 commits, agrupados. Los de solo documentación viajan con MIG-31.

| Commits | Ticket |
|---|---|
| `bfc05864` `74f42200` `0e25818d` `5d6fb837` `afee9a8e` `b6e0adb7` | Importación de 4.3 desde Contabo — **no se migran** (el núcleo es el de 5.3) |
| `d747781d` | MIG-01 (`config.php` fuera de git) · MIG-03 |
| `ce7a83d9` `e3143f87` `05b83936` `c7d85631` `8c45ed45` `d40dad07` `9991288e` | FUN-03 |
| `9682fe23` | FUN-07 |
| `9a26590f` | FUN-06 · FUN-07 · MIG-02 (`dev-up.sh`) |
| `d750035d` `1f70093f` `9db0452c` `1f757c57` `d1eb1dee` `a98b77eb` `70cb077a` `5891bd31` | Piel general del aula (Blackboard Ultra, fondo y vidrio) → sustituida por Odyssey en `32e49d2a`/`168e7d3c` → MIG-20, MIG-25 (§C) |
| `dfb213a9` `445c6941` `6c8b98a2` `340bf352` `c0a3a79e` | FUN-06 (y `445c6941`, `340bf352` también FUN-11) |
| `9a09cb3c` `acc5ff59` | FUN-12 |
| `3c88d834` | FUN-06 · FUN-09 · FUN-10 · FUN-15 |
| `716cc750` | FUN-15 |
| `d094d82a` `22383d7a` `482934ea` `6bbf5c54` `785ff69c` `ff345f50` `4d6d1c93` `01a5b985` `69759873` | FUN-04 |
| `c4a93643` `21a4b013` `19879a3e` | FUN-02 (y FUN-03, FUN-14, FUN-17) |
| `e736f304` | FUN-08 |
| `75657932` | FUN-11 · FUN-25 |
| `64635041` `51852e9b` `b3209f54` `ea90e68d` `3c6c785e` | FUN-14 |
| `27e251f6` | FUN-01 · FUN-11 · FUN-13 |
| `32e49d2a` `168e7d3c` | FUN-01 · FUN-12 · MIG-20 (sistema de diseño Odyssey) |
| `b0ead170` `90ac6f5b` `961d48f2` | FUN-11 |
| `d0fbad72` `bcbf542d` `25b5bcc9` `ad6ca6a5` `52f2d428` | FUN-17 |
| `2acf7aab` `24168779` `840315ff` `47a1a455` | FUN-18 |
| `e568dac0` `27a21d53` `db7a308a` | FUN-19 |
| `325035b2` | FUN-20 |
| `6201ab5e` | FUN-21 |
| `8abd0b7b` `b1c85d8e` `6d42867a` | FUN-22 |
| `2ab510bb` | FUN-13 |
| `f69f0634` `02f7c3db` | FUN-16 |
| `ea499ced` `d74d1da5` `15ee495e` | FUN-23 |
| `d1e4feeb` `9609daa3` `895450f0` | FUN-24 |
| `83781248` | Borrado de ficheros de diseño — nada que migrar |
| Resto (`3122cbd8`, `f7692128`, `9d4cec44`, `85537cd5`, `6a7d45a4`, `f8e73ec5`, `35dc39eb`, `678dc713`, `120c949b`, `7ebf9408`, `79e0120c`, `89a9a7c7`, `b18b514f`, `6041989c`, `62f78361`, `cddeebe1`, `69e5eaa3`, `3329be73`, `51cb55fc`, `d8e326f0`, `b221a1b5`, `321939bf`, `f58f7769`, todos los `docs(bbb)`, `docs(games)`, `docs(gamification)`, `docs(h5p)`, `docs(memory)`, `e557b3b2`, `eda6dc5f`, `87dc033f`, `0020655b`, `1fd46b0e`, `3c044eae`, `5e4217d5`) | Solo documentación → MIG-31 |

**Tickets del cliente del 2026-08-24** (`docs/plans/tickets-20260824-plan.md`),
para que ninguno se pierda: T-01 → FUN-01 · T-02 → FUN-04 · T-03 → FUN-08 ·
T-04 → FUN-06 · T-05 → FUN-15 · T-06 → FUN-24 (BBB) · T-07 y T-09 → guías
(MIG-31) · T-08 → FUN-09 · T-10 → FUN-10 · T-11 descartado.

---

## 3. Tickets técnicos (cómo se porta el código)

### A. Repositorio y entorno

#### MIG-01 · Preparar el repositorio `moodle-2027` 🟢
- **Qué**: `.gitignore` (como mínimo `config.php`, `node_modules/`,
  `moodledata/`, `.env`); llevar `CLAUDE.md`, `MEMORY.md` y `SUMMARY.md` del repo
  viejo, actualizados a 5.3; registrar en `MEMORY.md` las reglas de siempre
  (nunca tocar core, nunca contraseñas en el repo, commits en inglés
  Conventional Commits, verificación con capturas).
- **Ojo**: `git status` dice `main...origin/main [desaparecido]`: la rama
  remota no existe todavía. El primer push es decisión tuya.
- **Hecho cuando**: `git status` limpio con `config.php` ignorado, y los tres
  ficheros de contexto presentes.

#### MIG-02 · Entorno local para 5.3 🟡
- **Origen**: `docker-compose.yml`, `scripts/dev-up.sh`, `docs/local-dev-environment.md`.
- **Qué cambia**: imagen `moodlehq/moodle-php-apache:8.3` (hoy 8.1), base
  `mariadb:11.4` o `mysql:8.4` (hoy 10.11), `DocumentRoot` apuntando a
  `/var/www/html/public`, `mod_rewrite` y `AllowOverride` como ahora.
- **Hecho cuando**: `admin/cli/install_database.php` o la restauración del
  espejo arrancan, y `/admin/environment.php` sale todo en verde.

#### MIG-03 · `config.php` de 5.3 🟢
- **Qué**: partir de `config-dist.php` de la raíz; trasladar `wwwroot`,
  `dataroot`, `dbtype` (`mysqli` en prod), `sslproxy`/`reverseproxy` si los hay, y
  el desvío de correo local. **`$CFG->routerconfigured = true`**, con el router
  configurado en Apache (ver MIG-14), y un `$CFG->sessioncookie` propio en
  local para no chocar con el espejo 4.3 en el mismo `localhost`.
- **Hecho cuando**: el sitio carga en local; `admin/cli/checks.php` sin
  críticos; ninguna credencial queda en git. **Hecho el 2026-10-09.**

---

### B. Plugin `local_richimath`

Todo el plugin se copia a `public/local/richimath/`. Sus 5 tablas
(`invitation`, `courselink`, `plan`, `userplan`, `lead`) y sus datos **viajan
solos con la base de datos**. No hay que recrearlas.

#### MIG-10 · Base del plugin 🟡
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

#### MIG-11 · Invitaciones de un solo uso y enlace del curso 🟢
- **Origen**: `invite.php`, `accept.php`, `classes/{invitation,courselink}.php`,
  `classes/form/{invite_form,identify_form,signup_form}.php`, `templates/invite.mustache`.
  Docs: `docs/product/invitations.md`, `matricular-por-correo.md`.
- **Qué cambia**: el formulario usa `moodleform` (sin cambios de API). Revisar
  la plantilla por clases de Bootstrap 4 (2 usos).
- **Hecho cuando**: invitar por correo a un curso → el invitado crea cuenta por
  el enlace → queda matriculado; el enlace compartido del curso funciona.

#### MIG-12 · Planes y apariencias (tema por usuario) 🟡
- **Origen**: `plans.php`, `userplans.php`, `classes/plans/*`, `classes/observer.php`,
  `db/events.php` (`\core\event\user_loggedin`), `classes/form/plan_form.php`.
  Doc: `docs/product/plans-and-appearances.md`.
- **Qué cambia**: comprobar que el tema del usuario (`allowuserthemes`) sigue
  siendo el mecanismo en 5.3 y que el observador asigna el tema correcto al
  iniciar sesión.
- **Hecho cuando**: un alumno con plan Primaria entra y ve `rmprimaria`; el
  cambio de plan desde el admin se nota en el siguiente inicio de sesión.

#### MIG-13 · Catálogo `/students` y visibilidad de categorías 🟡
- **Origen**: `students.php`, `classes/category_visibility.php`,
  `theme/richimath/templates/local/students.mustache`, SCSS §A2.
- **Hecho cuando**: `/students` lista las categorías visibles con sus cursos;
  «Categoría 1» sigue oculta.

#### MIG-14 · Rutas limpias 🔴
- **Origen**: `local/richimath/routes.php` (mapa), `classes/routes.php`,
  `scripts/build-routes.php` (escribe el bloque del `.htaccess`), `.htaccess`,
  ajuste `local_richimath/prettyurls`. Doc: `docs/product/public-site-and-routes.md`.
- **Qué cambia**:
  - El `.htaccess` va a `public/.htaccess`; `build-routes.php` tiene que
    escribir ahí.
  - Las reglas reescriben a scripts reales (`/login/index.php`…): mismas rutas
    relativas dentro de `public/`.
  - **Convivencia con el router de Moodle** (obligatorio en 5.3): todo lo que
    no es fichero ni directorio va a `/r.php` con un `RewriteRule`. **No sirve
    `FallbackResource`**: PHP contesta su propio 404 a un `*.php` inexistente y
    uno de los tests de core pide justo eso. Y como las reglas de un
    `.htaccess` **sustituyen** a las del `<Directory>`, la regla del router
    tiene que ir **al final de `public/.htaccess`**, después de las rutas
    limpias. `build-routes.php` la escribe ahí.
  - Comprobación: `admin/cli/checks.php` → «Configuración de router» OK (sus 5
    tests de URL).
  - Las tres guardas siguen siendo obligatorias: `REDIRECT_STATUS`, no
    redirigir POST, `DirectorySlash Off`.
- **Hecho cuando**: cada ruta del mapa responde en sus dos direcciones (limpia →
  200, `.php` → redirige a la limpia); un **login fallido** se queda en `/login`
  con el error; con `prettyurls` apagado, todo funciona con las URL normales.

#### MIG-15 · Botón de WhatsApp del profesor 🟢
- **Origen**: `classes/{whatsapp,whatsapp_fields}.php`, upgrade 2026100300/01,
  renderer `teacher_whatsapp()`, FAB en `drawers.mustache`, SCSS §I.
  Doc: `docs/product/whatsapp-teacher-button.md`.
- **Qué cambia**: nada en datos (campos de perfil y de curso viajan con la BD).
  El FAB depende de MIG-21 (`drawers.mustache`).
- **Hecho cuando**: la matriz de 8 casos (profesor encendido/apagado × curso
  sin tocar/heredar/mostrar/no mostrar) da lo mismo que en 4.3; a 390 px los
  tres botones no se solapan.

#### MIG-16 · Invitados a una clase BBB y registro de leads 🟡
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

#### MIG-17 · Privacidad entre alumnos 🟢
- **Origen**: `classes/privacy_lockdown.php`, upgrade 2026100400.
  Doc: `docs/product/student-privacy.md`.
- **Qué cambia**: nada en datos (permisos y ajustes viajan con la BD).
  **Verificar** que las capacidades siguen existiendo con el mismo nombre y que
  el *report builder* de participantes respeta `viewparticipants`.
- **Hecho cuando**: como alumno, se repite la tabla «Verificado» del doc:
  participantes sin permiso, perfil del compañero «no disponible», compañero
  fuera del buscador de mensajes, profesor sí.

---

### C. Tema `theme_richimath` y temas de nivel

#### MIG-20 · Base del tema y compilación SCSS 🔴
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

#### MIG-21 · Rehacer las 5 plantillas de core copiadas 🔴
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

#### MIG-22 · Renderers que pisan a core 🟡
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

#### MIG-23 · Sitio institucional, sistema solar de niveles 🟡
- **Origen**: `layout/site.php`, `templates/local/{site,site_level_courses,mathfield}.mustache`,
  `classes/levels.php`, SCSS §G. Docs: `docs/product/levels-solar-system.md`,
  `public-site-and-routes.md`.
- **Qué cambia**: el layout tiene que seguir emitiendo `output.main_content` y
  usar `output.doctype`. El JS va en `{{#js}}`: comprobar que 5.3 lo sigue
  inyectando en un layout propio.
- **Hecho cuando**: sin sesión, los 5 planetas giran y abren su ficha, el menú
  «Niveles» muestra los cursos reales, no hay desborde a 390 px; con sesión,
  la portada es la de Boost.

#### MIG-24 · Los 4 temas de nivel 🟡
- **Origen**: `theme/rm{primaria,secundaria,preu,universidad}/` — `config.php`,
  `lib.php` (concatena el SCSS del padre + `palette.scss` [+ `post.scss`]),
  `pix_plugins/theme/richimath/whitelogo.png`, `style/fonts.css`, `lang/`.
- **Qué cambia**: `requires`/`dependencies` a 5.3; recompilar cada paleta
  contra Bootstrap 5.
- **Hecho cuando**: un alumno de cada nivel ve su paleta, su logo y su
  tipografía; la nota de contraste de cada tema (AA) se mantiene.

#### MIG-25 · Revisión de las secciones del SCSS contra el marcado de 5.3 🔴
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

#### MIG-26 · Lista de usuarios del admin ✅ decidido: la de 5.3 + título
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

#### MIG-27 · Iconos de actividad ✅ decidido: los de Moodle 5.3
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

### D. Scripts, recursos y documentación

#### MIG-30 · Scripts 🟡
| Script | Qué cambia |
|---|---|
| `build-routes.php` | Escribir en `public/.htaccess` |
| `build-activity-icons.py` | **Retirado** (MIG-27: iconos de Moodle) |
| `build-theme-logos.py` | Salida a `public/theme/*/pix` |
| `set-frontpage-banner.php` | Ruta del `config.php` (raíz) |
| `build-h5p-escape-room.py`, `build-kepler-questions.py` | Sin cambios de código; ver MIG-32 |
| `deploy-contabo.sh` | `config.php` y los comandos CLI (`admin/cli/`) **siguen en la raíz**, fuera de `public/` (comprobado en el árbol de 5.3); lo que cambia es el `DocumentRoot` del vhost |
| `dev-up.sh` | Según MIG-02 |
| `scripts/bbb/` | Sin cambios (es del servidor BBB, no de Moodle) |

#### MIG-31 · Recursos y documentación 🟢
- Copiar `assets/` y `docs/` (diseño, planes, producto, servidor BBB) tal cual.
- Actualizar en los manuales las rutas y pantallas que cambian en 5.3:
  banco de preguntas (MIG-32), lista de usuarios (MIG-26), iconos (MIG-27).

#### MIG-32 · Juego «Estación Kepler» y preguntas dinámicas 🟡
- **Origen**: `assets/h5p/estacion-kepler.h5p`, `assets/h5p/kepler-preguntas.xml`
  (5 `calculatedmulti`). Doc: `docs/product/escape-room-360.md`.
- **Qué cambia**: el H5P y las preguntas **ya importadas** viajan con la BD (el
  banco del curso se convierte en una instancia `mod_qbank` en la
  actualización). Lo que cambia es el **manual del profesor**: dónde se importa
  el XML en 5.3.
- **Hecho cuando**: el cuestionario de Kepler genera valores distintos por
  alumno tras la migración; el manual describe la importación en 5.3.

---

### E. Datos y ajustes que viven en la base de datos

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
| `frontpageloggedin` | **2** (lista de categorías: las tarjetas por nivel). Una instalación nueva de 5.3 trae 6 (lista de cursos) |
| `forcelogin` | **0** — si no, el sitio institucional y `/students` mandan al login. Una instalación nueva de 5.3 lo trae en 1; el upgrade conserva el valor de producción |
| `enablemyhome`, `enablemycourses` (nuevos en 5.x) | **1** — «Página principal» y «Mis cursos» en la barra lateral. Core los enciende al actualizar un sitio existente y los deja apagados en una instalación nueva (comprobado en la Fase 1) |

#### MIG-40 · Comprobar módulos retirados en producción 🟢
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

## 4. Lo que NO es migración (backlog que sigue después)

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

## 5. Resumen

| Capa | Tickets | Riesgo alto 🔴 |
|---|---|---|
| **Funcionales** (lo que ve el usuario) | FUN-01…25 → **25** | FUN-03 login, FUN-04 sitio y rutas, FUN-06 barra lateral, FUN-12 página del curso, FUN-13 iconos, FUN-14 evaluaciones, FUN-22 lista de usuarios |
| **Técnicos** · A. Repositorio y entorno | MIG-01…03 | — |
| **Técnicos** · B. `local_richimath` | MIG-10…17 | MIG-14 |
| **Técnicos** · C. Temas | MIG-20…27 | MIG-20, 21, 25, 26, 27 |
| **Técnicos** · D. Scripts, recursos, docs | MIG-30…32 | — |
| **Técnicos** · E. Datos | MIG-40 + verificación | — |
| **Total** | **25 funcionales + 23 técnicos** | |

La migración está terminada cuando **los 25 `FUN` cumplen su «Hecho cuando»**
sobre la copia de producción (Fase 2 del plan) y otra vez en producción
(pruebas de humo de la Fase 4).
