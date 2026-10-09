# Tema Richimath: login personalizado y calendario estilo Blackboard

Tema hijo de Boost en `theme/richimath/`. Cubre dos personalizaciones:

1. **Página de login**. Desde 2026-09-05 la rige *Crystalline Academic Glass*
   (`docs/ux_pro/LOGIN DE USUARIO/DESIGN.md`): barra de identidad, tarjeta
   partida y pie de créditos — ver
   [login y flujo de examen](product/ux-pro-login-and-exam-flow.md). Lo que
   sigue describe la capa de arte anterior (`docs/fondo_login.png`), que ya no
   se pinta.
2. **Calendario y dashboard** re-estilados con la estética Blackboard Ultra:
   blanco limpio, barra de navegación azul marino, acento morado en "hoy".

## Decisiones de diseño

- **Login por capas** (desde los assets de `assets/`): textura gris de fondo a
  `cover`, logo RM fijo arriba-izquierda y cubos dorados fijos abajo-derecha,
  ambos escalando con `clamp()` según el viewport. El titular y el lema son
  HTML real (strings `welcomeheading` / `welcometagline`, en inglés base y
  español), así que nada se recorta ni colisiona en ninguna pantalla — esto
  sustituye al antiguo PNG único con los textos horneados y sus tres
  workarounds (contain, banner móvil, margen 18vh).
- Los SVG de `assets/` llevan el raster como **máscara interna**: extraer el
  base64 da la máscara, no el arte. El pipeline correcto (y el usado) es
  renderizar el SVG compuesto en un navegador sobre blanco, perforar el blanco
  a alfa y recortar al contenido (`loginlogo.png`, `loginsquare.png`,
  `whitelogo.png` en `pix/`). `background_gray.svg` sí es un raster directo →
  `loginground.jpg`.
- El sidebar corona con `pix/whitelogo.png` (logo blanco perforado) como marca
  estática. Cada tema hijo lo reemplaza por el suyo sin tocar la plantilla:
  ver [un logo por nivel](product/plans-and-appearances.md#un-logo-por-nivel).
- Las etiquetas USUARIO / PASSWORD no van hardcodeadas: son los strings de core
  (`username` / `password`) con `text-transform: uppercase`. En un sitio en
  español se ven como USUARIO / CONTRASEÑA y siguen siendo traducibles.
- **No se reconstruye el calendario**: Moodle ya trae las tres vistas que piden
  las maquetas. Solo se re-estilan con SCSS, acotado a `body.path-calendar` y
  `#page-my-index` para no romper nada más.
- **Sidebar vertical tipo Blackboard** en escritorio (≥992px): fija a la
  izquierda, 240px, azul marino. Reutiliza la navegación primaria real de
  Moodle (por eso "Site administration" solo aparece a admins) y añade
  Calendario, Mensajes, Notificaciones y Calificaciones; abajo, el interruptor
  de edición y Cerrar sesión. En pantallas menores vuelve la barra superior
  normal de Boost (en azul marino) con su menú hamburguesa — esa es la
  estrategia responsive.
- El dashboard va a ancho completo y el bloque Calendario lleva un toggle
  Día | Mes | Próximos eventos que enlaza a las vistas del calendario.

## Mapa de vistas (maquetas → URLs reales)

| Maqueta | Vista Moodle |
|---|---|
| Vista por día (segunda imagen) | `/calendar/view.php?view=day` |
| Vista por mes (tercera imagen) | `/calendar/view.php?view=month` |
| Fechas de exámenes / cierres (cuarta imagen) | `/calendar/view.php?view=upcoming` |

Las fechas de entrega de tareas, cuestionarios y cierres de curso aparecen en
esas vistas automáticamente: Moodle las genera de las actividades con fecha.

## Vista de día (rejilla horaria tipo Blackboard)

`/calendar/view.php?view=day` deja de ser una lista: tira semanal (L–D, día
visto en círculo morado, clicable), fila de chips para eventos de día completo,
rejilla horaria 06:00–22:00 y barra negra de la hora actual con pastilla HH:MM
(solo cuando el día visto es hoy, se reposiciona cada minuto). Los eventos se
colocan por su hora de inicio, se reparten en columnas si se solapan y abren el
modal nativo de Moodle al pulsarlos.

Cómo funciona: override de `core_calendar/day_detailed` (se eligió ese parcial
y no `calendar_day` porque la navegación con flechas re-renderiza
`day_detailed` por AJAX — así la rejilla sobrevive al cambio de día). La
plantilla solo emite stubs de datos ocultos; un JS inline construye todo. Si el
JS falla, se muestra la lista estándar de Moodle (mejora progresiva).

Nota de zona horaria: las posiciones usan la zona del navegador. Si el perfil
Moodle del usuario tiene otra zona, los bloques se desplazan (el campo
`timeusermidnight` del exporter llega a 0 en esta ruta de renderizado, así que
no se puede corregir sin lógica adicional; el código lo usa cuando llega real).

## Piel Blackboard global (secciones C1–C8 del SCSS)

Todas las rutas logueadas comparten el lenguaje Blackboard Ultra: fondo
`#f8f8f8`, contenido en tarjetas blancas (borde `#e3e5e8`, radio 8px), títulos
de página en serifa (Georgia), pestañas segmentadas (activa = navy), botones y
formularios redondeados con foco morado, tablas limpias. Cobertura específica:
lista de cursos con barra de acento de 6 colores rotando, página de curso con
secciones-tarjeta, perfil, calificaciones, mensajes, modales y alertas. Todo
acotado con `$richimath-skin` (excluye login y layouts popup/embedded/secure…),
y responsive verificado a 390px sin scroll horizontal.

En el sidebar, la marca es un título estático (antes enlazaba a `/my/`
duplicando Dashboard) y un JS defensivo elimina cualquier enlace con URL
repetida (ignorando anclas y cabeceras sin URL de menús personalizados).

Desde la v2026082205, **todas las rutas logueadas** comparten el mismo fondo
por capas del login (textura gris a `cover` + cubos dorados fijos
abajo-derecha con `clamp()`), vía pseudo-elemento fijo en el scope
`$richimath-skin` — detrás de todo, nunca intercepta clics. Las superficies
grandes (tarjeta de contenido y bloques del dashboard) son **cristal
esmerilado**: blanco al 66% + `backdrop-filter: blur(6px)`, y los paneles interiores
(mensajes, celdas del calendario, secciones de curso, tablas) son
semitransparentes en vez de blanco opaco, para no tapar el efecto. La capa
C1b remata lo que Boost/Bootstrap pintan blanco por su cuenta encima de la
tarjeta (`#region-main`, el footer `.bg-white`, paneles de mensajería,
tarjetas de perfil y de cursos) — sin ella el esmerilado solo se veía en el
dashboard, que no usa `#region-main`. El fondo se
aprecia a través de ellas sin sacrificar contraste del texto; transparencia
total pondría el texto directo sobre la textura y los cubos (ilegible), y
blanco opaco esconderia el fondo. En navegadores sin `backdrop-filter` queda
el blanco al 82% sin blur — igualmente legible.

## Navegación móvil (<992px) y catálogo de categorías

- **Móvil**: la barra superior queda mínima (hamburguesa + marca + iconos) y la
  hamburguesa abre el drawer de Boost re-estilado como panel azul marino con
  la navegación completa: ítems nativos + Calendario/Mensajes/Notificaciones/
  Calificaciones + Salir (override de `theme_boost/primary-drawer-mobile`),
  con resaltado del ítem activo por URL, igual que el sidebar de escritorio.
- **Catálogo** (`/course/index.php`): las categorías se muestran como tarjetas
  (rejilla 1/2/3 columnas, serifa, acento morado, hover con elevación) con el
  drill-down nativo de Moodle: categoría → subcategorías → cursos. Es la vía
  recomendada para que el alumno navegue la oferta; `/my/courses.php` sigue
  mostrando SUS cursos matriculados (cambiar esa página exigiría tocar core).

## Landing pública (portada sin sesión)

La portada que ve un visitante **sin sesión** (`http://.../`) no es la portada
de Moodle: es una landing de captación. La de un usuario con sesión **no
cambia** en absoluto.

Qué se ve, de arriba abajo:

1. **Héroe**: nombre completo del sitio, el lema del tema (`welcometagline`) y
   la única llamada a la acción (`offercta`, "Consulta tu matrícula con el
   profesor…").
2. **El catálogo real, en pestañas**: una por categoría de primer nivel
   (Escolar / Pre universitario / Universidad), con los cursos de la categoría
   **y de todas sus descendientes**. Cada curso es una tarjeta con su nombre y,
   debajo, la subcategoría (`QUINTO GRADO`, `PRE - U.LIMA`…): media academia
   repite el nombre "MATEMÁTICA" y la subcategoría es lo único que las
   distingue.

Decisiones que conviene no revertir sin pensarlo:

- **La sección no tiene ningún enlace, y eso es deliberado.** Un clic anónimo
  sobre un curso solo llega al callejón sin salida de la matriculación ("no se
  puede auto matricular"), así que los cursos son texto plano. La CTA tampoco
  es un enlace: es la frase "consulta tu matrícula con el profesor", y por eso
  ya **no** se pinta como un botón — prometía un clic que no existía. Si algún
  día hay a dónde llevar al visitante (WhatsApp, formulario), la CTA pasa a ser
  un `<a>` de verdad y recupera el aspecto de botón.
- **Datos reales, no una lista estática.** Los produce
  `core_renderer::offer_categories()` a través de `core_course_category`, la
  misma API que usa `/course/index.php`: las categorías ocultas, los cursos
  ocultos y los cursos que el visitante no puede ver **no llegan** a la
  plantilla, sin filtros propios. Una categoría sin cursos visibles no se
  pinta (por eso "Categoría 1" no aparece).
- **Tope de 8 cursos por pestaña** (`OFFER_COURSE_LIMIT`), con una línea
  "+N cursos más en este nivel" cuando sobran. Universidad tiene 14: listarlos
  todos convierte la landing en el índice de cursos. El tope **se reparte**
  (`offer_sample_courses`): se toma un curso por subcategoría inmediata en cada
  pasada, no los 8 primeros por `sortorder`. Con el corte plano, los 8 de
  Universidad caían todos bajo Universidad del Pacífico y U. de Lima y U. de
  Piura no aparecían en ninguna parte de la página pública.
- **Las pestañas son mejora progresiva.** Sin JavaScript se ven todos los
  paneles abiertos, cada uno con su título; el JS de `drawers.mustache` marca
  la sección como lista, muestra la barra de pestañas y deja un panel a la
  vista. Navegación con teclado: flechas ←/→, Inicio y Fin. Los roles ARIA
  (`tablist`/`tab`/`tabpanel`) los pone **ese mismo JS**, no la plantilla: la
  barra está oculta sin JS, así que en el HTML dejaban tres paneles de pestaña
  sin ninguna pestaña que los gobernase. El panel lleva `tabindex="0"` porque
  dentro no hay nada enfocable.
- **La limpieza del núcleo se cuelga de `richimath-has-landing`.** La clase la
  añade el renderer desde `show_offer()`. No vale `notloggedin` de Moodle: un
  invitado **sí** tiene sesión, así que veía la landing *y* la cabecera del
  núcleo debajo — dos `<h1>` con el nombre del sitio en la misma página.

### Paso de despliegue obligatorio (vive en BD, NO viaja por git)

La landing sustituye a la portada de Moodle, pero la lista "Cursos
disponibles" la pinta el core según un ajuste del sitio. Hay que vaciarlo **en
cada entorno**, después de desplegar el tema:

```bash
# Portada para visitantes SIN sesión: vacía (sin lista de cursos)
php admin/cli/cfg.php --name=frontpage --set=
php admin/cli/purge_caches.php

# Comprobación: debe imprimir una línea vacía
php admin/cli/cfg.php --name=frontpage
```

⚠️ **No tocar `frontpageloggedin`** (vale `2`, nombres de categorías): ese es
el que gobierna la portada de quien ya ha entrado, y debe seguir igual.

El resto de lo que el core seguía pintando para el visitante (el titular
"AULA VIRTUAL Richi Math" y la tarjeta de resumen de la sección 0) se oculta
por CSS acotado a `body.notloggedin.pagelayout-frontpage` (sección A2b del
SCSS), no por configuración.

## Página de curso (C5 revisada)

Vista tipo Blackboard Ultra: título despejado (los botones de los drawers son
pastillas ancladas a las esquinas de la tarjeta — la causa del solape original
era que el `backdrop-filter` convierte la tarjeta en contenedor de los
elementos `position: fixed`), secciones-tarjeta con acento morado, chevron a la
derecha y divisor, filas de actividad con el icono en contenedor tintado por
propósito (colores del propio Moodle), y CTAs de edición discontinuos morados.
Verificado en vista normal, modo edición (arrastrar secciones incluido) y
móvil; los indicadores de arrastre y el modo "una sección por página" tienen
overrides específicos — no tocar sin releer los comentarios del SCSS.

## Accesos rápidos del Área personal (chips) y animación de portada

- **Chips**: franja superior del Área personal con accesos de un clic — los
  cursos matriculados del usuario (máx. 12 + «Ver todos», orden por último
  acceso) y, solo para staff, una segunda fila con las categorías. Renderer
  `dashboard_chips()` (1 consulta para cursos), plantilla en drawers, SCSS D5.
  Los nombres truncados llevan `title` con el nombre completo.
- **Animación de portada**: símbolos matemáticos flotando por TODO el fondo de
  la landing pública (capa fija a viewport, detrás del contenido, sección A2c).
  ~1 de cada 3 «explota» al llegar arriba; `prefers-reduced-motion` la
  desactiva; sin JS no pasa nada.

## Portada con sesión (D6)

La «Página Principal» de un usuario con sesión muestra el banner de la
sección 1 (un recurso Área de texto y medios; se instala y reemplaza con
`scripts/set-frontpage-banner.php`, o Richi lo edita por UI) como imagen
limpia y redondeada — la tarjeta de sección de C5 solo aparece en modo
edición — y la lista de categorías con las mismas tarjetas C9 del catálogo
(el selector de C9 incluye `body#page-site-index`; la clase
`.frontpage-category-names` va en el propio nodo del árbol, no en un
wrapper). Guía de uso y despliegue en
[product/frontpage-content.md](product/frontpage-content.md).

## Flujo de examen (sección F)

Las cuatro pantallas de `mod_quiz` siguen *Imperial Academic Precision*
(`docs/ux_pro/FLUJO DE EXAMENES/…/DESIGN.md`) sin tocar el módulo: cinta
obsidiana, esquina 0px, metadatos monoespaciados y el acento del nivel.
Detalle, límites y trampas en
[login y flujo de examen](product/ux-pro-login-and-exam-flow.md).

## Fondo de las preguntas de examen (D8)

Boost pinta cada enunciado de cuestionario (`.que .formulation`, en intento,
revisión y vista previa) con la alerta «info» celeste. Desde el 2026-08-31 se
sustituye por el arte de examen de Richi: `assets/exam-background.png`
(entregado como «FONDO PARA EXAMENES.png», 800×400) → `pix/examground.jpg`,
textura gris clara con el logo RM abajo a la derecha, bajo un velo blanco
(`$richimath-veil-light`) para que enunciados largos y campos de respuesta
mantengan contraste; `background-position: right bottom` deja el logo en la
esquina sea cual sea la altura de la pregunta. Las cajas de retroalimentación
(`.outcome`, `.comment`) conservan el amarillo de Boost. Verificado en la
vista previa de un cuestionario con opción múltiple, V/F, respuesta corta y
numérica, 1440 y 390.

## Limitaciones conocidas (deliberadas)

- El "Cerrar sesión" del sidebar enlaza sin `sesskey`: Moodle muestra su página
  de confirmación. Es el comportamiento estándar y seguro.
- La campana del sidebar lleva a la página completa de notificaciones (no abre
  el popover): duplicar el popover de la barra superior rompería sus IDs.
- El selector de idioma en escritorio queda dentro de las preferencias de
  usuario (la barra superior que lo llevaba está oculta en ≥992px).

## Estructura del tema

```
theme/richimath/
├── config.php                              ← hijo de boost
├── lib.php                                 ← carga SCSS de boost + post.scss propio
├── version.php                             ← theme_richimath, requires 2023100900
├── scss/post.scss                          ← TODO el estilo propio vive aquí
├── templates/core/loginform.mustache       ← override del formulario de login
├── templates/theme_boost/drawers.mustache  ← layout: copia de Boost + 1 línea (sidebar)
├── templates/local/sidebar.mustache        ← el sidebar vertical
├── templates/core_calendar/calendar_month.mustache ← toggle Día/Mes/Vencimientos (solo en el bloque)
├── pix/loginbg.png                         ← copia de docs/fondo_login.png
├── lang/en/theme_richimath.php
└── classes/privacy/provider.php
```

## Despliegue y activación

```bash
# Local: probar, luego push. En Contabo:
cd /var/www/html
php admin/cli/maintenance.php --enable
git pull
php admin/cli/upgrade.php --non-interactive     # instala el tema (nuevo plugin)
php admin/cli/purge_caches.php
chown -R www-data:www-data /var/www/html
php admin/cli/maintenance.php --disable

# Activar el tema
php admin/cli/cfg.php --name=theme --set=richimath
php admin/cli/purge_caches.php

# Landing pública: vaciar la portada de visitante (ver "Landing pública")
php admin/cli/cfg.php --name=frontpage --set=
php admin/cli/purge_caches.php
```

Verificación: abrir `http://169.58.171.171/login/index.php` en ventana privada.
Si el fondo no aparece, purga cachés otra vez y recarga con Ctrl+Shift+R (el CSS
compilado se cachea agresivamente).

Volver atrás en cualquier momento:

```bash
php /var/www/html/admin/cli/cfg.php --name=theme --set=boost
php /var/www/html/admin/cli/purge_caches.php
```

## Dashboard (/my): dejar el calendario como pantalla principal

El re-estilado es del tema, pero **qué bloques** muestra el dashboard se decide
en la administración (vive en BD, no en git):

1. *Administración del sitio → Apariencia → Página de inicio predeterminada*
   (`/my/indexsys.php`).
2. Activar edición, quitar los bloques que sobren y añadir **Calendario**
   en la columna central.
3. "Restablecer Dashboard para todos los usuarios" para propagarlo.

Con eso, `/my/` muestra el calendario mensual re-estilado, y los botones de
vista llevan a día / mes / próximos eventos.

## Decisiones deliberadas (no son bugs)

- El **logo subido en la administración** (Apariencia → Logos) no se muestra en
  la página de login: el branding ya viene dentro de la imagen de fondo. El
  resto del sitio sí lo usa.
- El nombre del sitio en el login queda solo para lectores de pantalla
  (`sr-only`): el titular visible es el de la imagen.
- El favicon del tema base es el de Boost (`pix/favicon.ico`); sustituir por uno
  de Richi Math cuando exista. Los cuatro hijos ya llevan el suyo: el favicon
  **no hereda del padre** (`resolve_image_location()` devuelve
  `$this->dir/pix/favicon.ico` a secas), así que un hijo sin ese fichero servía
  un 404.

## Idioma español (no viaja por git)

El paquete de idioma vive en `moodledata/lang` y el ajuste en la BD, así que
hay que repetirlo en cada entorno. En Contabo:

```bash
# Instalar el paquete es (Moodle 4.3 no trae CLI de langimport; se usa el controlador)
php -r 'define("CLI_SCRIPT", true); require "/var/www/html/config.php"; (new \tool_langimport\controller())->install_languagepacks("es"); purge_all_caches();'

# Idioma por defecto del sitio (lo que ve el login)
php /var/www/html/admin/cli/cfg.php --name=lang --set=es
php /var/www/html/admin/cli/purge_caches.php
```

Ojo: el idioma del **perfil** de cada usuario pisa el del sitio una vez
logueado. Para pasar a español los usuarios ya existentes:

```bash
php -r 'define("CLI_SCRIPT", true); require "/var/www/html/config.php"; global $DB; $DB->set_field("user", "lang", "es");'
```

Los usuarios nuevos heredan el del sitio. Alternativa por UI:
*Administración → Idioma → Paquetes de idioma* y *Ajustes de idioma*.

## Levantar cambios en local

```bash
./scripts/dev-up.sh    # arranca contenedores, aplica upgrades y purga cachés
```

Tras editar SCSS o plantillas basta recargar (el entorno local lleva
`themedesignermode`); si no se refleja, vuelve a ejecutar el script.

## Cambiar la imagen de fondo en el futuro

Sustituir `theme/richimath/pix/loginbg.png` (mismo nombre), commit, push, y en
Contabo: `git pull` + `purge_caches.php`. Si la nueva imagen no es cuadrada,
revisar el `background-size` en `scss/post.scss`.

## Marca en la pestaña, iconos de actividad y tarjetas de categoría (2026-08-31)

Tres peticiones de Richi sobre lo que se ve, resueltas sin tocar core:

- **Pestaña del navegador**: la portada decía «Página Principal | AulaVirtual»
  porque core arma el título como `get_string('home')` + el **nombre corto**
  del sitio. `core_renderer::page_title()` ahora devuelve solo el nombre corto
  cuando el pagetype es `site-index`; el resto de páginas conservan el título
  de core. Para que la pestaña lea «Richi Math» hay que poner ese nombre corto
  (*Administración del sitio → General → Ajustes de la página principal →
  Nombre corto del sitio*, hoy `AulaVirtual`): vive en la BD y no viaja por
  git, así que se cambia en cada entorno. El mismo valor alimenta la marca de
  la barra superior en móvil.
- **Iconos de actividad** (cuadrado de color en la cabecera de cada
  actividad, en las filas del curso y en los bloques): el glifo sale de
  `mod/<módulo>/pix/monologo.svg` y el color del cuadrado de la paleta por
  *propósito* de Boost (`$activity-icon-*-bg`, declaradas `!default`). El rosa
  chicle de los cuestionarios era el valor de fábrica; `scss/pre.scss` ahora
  repinta las seis en el rango del tema (evaluación = morado RM, contenido =
  navy…). Para cambiar **el dibujo** y no solo el color, el tema puede
  sustituir cualquier icono sin tocar el módulo:
  `theme/richimath/pix_plugins/mod/quiz/monologo.svg` (SVG monocromo; Moodle
  lo tiñe de blanco sobre el cuadrado). Pedir el arte a Richi antes.
- **Tarjetas de categoría** (`/course/index.php` y la portada con sesión,
  sección C9): franja superior en degradado navy→morado en vez de la barra
  fina lateral, esquinas de 10px, altura mínima para que una categoría vacía
  no quede raquítica, elevación y borde morado al pasar el ratón, y el
  contador de cursos como pastilla morada bajo el nombre.

## Sistema de diseño «Primary Moodle Odyssey» (2026-09-03)

El aula pasa del lenguaje Blackboard (navy + dorado + serifa) al sistema que
Richi entregó en [docs/design/](design/): `01_elementary_school/primary_moodle_odyssey/DESIGN.md`
(tokens y componentes) y el mockup
`01_elementary_school/aula_virtual_moodle_4._primaria_ciencias_naturales/`
(pantalla de referencia). Los otros tres niveles tienen su propio sistema en
esa misma carpeta y esperan al [plan multi-tema](plans/multi-theme-plan.md).

**Dónde vive cada cosa**

- `scss/pre.scss` — las variables de Bootstrap (paleta, tipografías, radios,
  alturas de control). Al fijarlas antes del preset de Boost, **todos** los
  componentes que Moodle construye (botones, tarjetas, formularios, tablas,
  alertas) salen ya con el sistema puesto, sin una regla nuestra.
- `style/fonts.css` — el `@import` de Google Fonts (Quicksand + Nunito Sans).
  Va en una hoja plana declarada en `config.php` (`$THEME->sheets`) porque el
  compilador SCSS resuelve `@import` como ruta de fichero y rechaza una URL.
- `scss/post.scss` — arriba, los tokens históricos (`$richimath-navy`,
  `$richimath-purple`, veils, `$richimath-serif`…) **conservan el nombre pero
  apuntan a su rol en el sistema nuevo**: así las secciones A–D heredan la
  paleta sin reescribirlas. Al final, la **sección E** cubre lo que las
  variables no alcanzan: barra superior blanca, rail izquierdo blanco con
  pastilla azul, tarjetas de 16px, botones con sombra sólida inferior
  («teclas»), filas de actividad con glifo de 52px, insignias en pastilla,
  migas, inputs de 48px y anillos de foco.

**Qué se conserva de Richi Math**: el login entero (logo RM, cubos dorados,
animación de glifos; solo el botón adopta el azul del sistema), el banner de
la portada y el fondo de exámenes (D8), con el velo blanco subido para
mantener el contraste sobre el lienzo claro.

**Paleta** (roles de DESIGN.md): primario `#004ccd` / contenedor `#0f62fe`,
secundario verde `#006d40`, terciario coral `#9a3600`, lienzo `#f9f9ff`,
superficies blancas, hairline `#c3c6d8`. Los cuadrados de icono de actividad
siguen el mapa por propósito del sistema (cuestionario azul, tarea coral,
foro violeta, recurso esmeralda…).

**Marca en la pestaña**: `core_renderer::page_title()` sustituye el nombre
corto del sitio que core añade por el string `brandname` del tema
(«Richi Academy»), en todas las páginas; la portada lo muestra solo.

Verificado en local a 1440 y 390 px: portada con sesión, Área personal, curso,
catálogo, login y navegación móvil, sin scroll horizontal.

### Auditoría de cobertura (2026-09-03)

El rediseño se validó vista por vista, no solo en las pantallas de portada:

1. **Restos del lenguaje viejo**: búsqueda de literales (`#262d3d`, `#7b2d8b`,
   `#f2f2f4`, Georgia, veils translúcidos) en el SCSS. Los que quedaban eran
   *texto blanco* pensado para fondos navy que ahora son blancos, así que las
   secciones B1 (barra superior), B2b (rail izquierdo) y B4 (drawer móvil) se
   **reescribieron claras en origen** en vez de taparlas con overrides; los
   bloques E3/E4 se eliminaron por redundantes.
2. **Sonda de contraste WCAG** en el navegador (color calculado vs. fondo
   heredado, umbral 4.5:1 / 3:1 para texto grande) sobre Área personal, curso,
   curso en modo edición, calendario, mensajes, participantes, catálogo,
   portada, invitaciones y ajustes de administración. Defectos reales
   encontrados y corregidos: el bloque de usuario del rail y el «Modo de
   edición» seguían en blanco sobre blanco, el índice del curso marcaba la
   página actual en navy, y el rail compacto (Canvas) partía las etiquetas con
   guiones porque el peso 700 del sistema es más ancho que el anterior.
3. **Falsos positivos conocidos**: una sonda que herede fondos se detiene en
   `rgba(0,0,0,0.03)` (la banda de las tablas) y lo trata como negro opaco;
   ahí el contraste real es correcto. Comprobado a ojo en participantes y
   mensajería.

Para repetirla: abrir cada vista y evaluar el color calculado de los nodos de
texto sin hijos contra el primer fondo opaco de su cadena.

## Tarjetas de categoría en color (2026-09-15)

Las tres tarjetas de `/course/index.php` (y de la portada con sesión) eran
blancas con la misma franja navy→morado arriba: nada distinguía **ESCOLAR** de
**PRE UNIVERSITARIO** ni de **UNIVERSIDAD**, ni abiertas ni cerradas. Ahora cada
tarjeta se pinta en un color propio, **abierta y cerrada**.

**De dónde sale el color.** De `$richimath-course-accents`, la rotación de seis
acentos que **cada paleta hija redefine** (`theme/rm*/scss/palette.scss`). No hay
ni un color nuevo, y ninguno está atado a una categoría concreta: la tarjeta se
pinta en la apariencia que el plan del alumno le dio, igual que las barras de
las tarjetas de curso del Área personal. Cambiar de plan cambia los tres colores.

**El reparto es por posición** (`:nth-of-type(6n + i)`), no por id de categoría.
Una palabra: un `idnumber` no existe en todas las instalaciones y las categorías
se crean y se borran; la posición siempre está.

**Cómo se implementa.** El mixin `richimath-category-accent($accent)` (arriba del
`post.scss`, junto a los tokens) emite siete **propiedades personalizadas**
—`--rm-cat-accent`, `--rm-cat-ink`, los tres velos, el borde y el halo—. Así un
único juego de reglas sirve para los seis colores, y **los hijos de una tarjeta
abierta heredan el color de su padre** sin saber cuál es: una subcategoría de
Universidad se lee en el color de Universidad.

Qué toma el color: la franja superior (acento → tinta), un velo diagonal sobre
la cara blanca, el borde, el nombre, la pastilla del contador, el halo al pasar
el ratón y, con la tarjeta abierta, los enlaces de los hijos.

**Dentro de la tarjeta abierta no hay caja.** El primer intento puso a los hijos
sobre un panel blanco con su propio borde: se leía como **una tarjeta dentro de
otra** y rompía justo el color que se acababa de ganar. Los hijos van sobre el
velo de la tarjeta, separados del título por una línea de pelo en el color de la
tarjeta; las filas de curso (`.coursebox`), que en el resto del sitio son cajas
blancas, se quedan transparentes ahí dentro por la misma razón.

**Dos decisiones que parecen arbitrarias y no lo son:**

1. **La tinta no es el acento, es `mix($accent, $richimath-navy, 55%)`.** El
   acento crudo sería lo obvio y falla 4.5:1 en cuanto la rotación tiene un
   ámbar o un lima. Con la mezcla, el peor caso medido es **4,78:1** (el ámbar
   de Universidad); el resto va de 5 a 13.
2. **El ratón oscurece el velo, no aclara el texto.** Mover el título al acento
   al pasar por encima es el gesto natural y rompe justo el contraste que el
   punto anterior protege.

**Se reordenaron dos rotaciones** (solo el orden; los seis colores son los
mismos). Las tres primeras posiciones son las que la rejilla pone una al lado de
otra, y en `rmuniversidad` eran zafiro-eléctrico-cian —tres azules— y en `rmpreu`
carmín-azabache-granate —dos rojos—. Ahora son zafiro-cian-ámbar y
carmín-azabache-oro. El mismo reorden mejora las barras de `/my/courses.php`,
que leen la misma lista.

**Código muerto retirado**: la sección E repetía el pintado de estas tarjetas en
roles Odyssey. No se veía nunca — C9 cuelga de `body#page-course-index`, y un
**id gana a cualquier número de clases**, así que `#{$richimath-skin}` (que son
`:not()` encadenados, es decir clases) perdía siempre. C9 es la única casa de
estas tarjetas.

Verificado a 1440 y 390 px, cerradas y abiertas, como `qa.admin`
(*rmuniversidad*) y como `estudiante.demo` (*rmprimaria*), y las cinco paletas
comparadas una sobre otra.

## Lista de usuarios del administrador (2026-09-27)

`/admin/user.php` es core y **no se toca**. Las tres mejoras que pidió Richi se
resolvieron sin editar una línea de core: dos son ajustes que viven en la BD y
la tercera es SCSS del tema.

### 1. El título — «Examinar lista de usuarios» → «Lista de usuarios»

Es la cadena `userlist` del componente `core_admin`. Se cambia por
**Administración del sitio → Idioma → Personalización del idioma**:

1. Elegir **es** → *Abrir paquete de idioma para edición*
2. En la lista de componentes, seleccionar **`admin.php`**
3. *Identificador de la cadena*: `userlist` → **Mostrar cadenas**
4. Escribir «Lista de usuarios» en *Traducción local personalizada*
5. **Guardar los cambios del paquete de idioma**

⚠️ **En esa pantalla los componentes del núcleo NO se llaman por su nombre
interno**: `core_admin` aparece como **`admin.php`**. Lo decide
`admin/tool/customlang/filter_form.php`, que etiqueta cada opción con
`$component.'.php'` aunque el valor que envía sea `core_admin`. Buscar
«core_admin» en esa lista no da nada.

⚠️ **Hay más de un `userlist` en el sitio.** El de `moodle.php` (componente
`core`) ya dice «Lista de usuarios» y NO es el de esta página. El bueno es el
que en *Texto estándar* dice **«Examinar lista de usuarios»**.

⚠️ El botón que publica es **«Guardar los cambios del paquete de idioma»**.
«Aplicar los cambios y continuar editando» guarda pero no publica.

⚠️ **Vive en la BD y en moodledata: NO viaja por git.** Hay que repetirlo en
cada entorno. Aplicado en local el 2026-09-27; **pendiente en Contabo**.

### 2. Filtrar por correo sin pulsar «Mostrar más»

No hacía falta un select nuevo: el ajuste de sitio **`userfiltersdefault`** ya
decide qué filtros salen sin desplegar. Por defecto trae `realname`, y para
esta academia va en **`email`**.

**Administración del sitio → Usuarios → Gestión de usuarios** →
*Filtros de usuario por defecto* → seleccionar **Dirección de correo**.

O por CLI:

```bash
php admin/cli/cfg.php --name=userfiltersdefault --set=email
```

Admite varios separados por coma (`email,realname`). El resto sigue detrás de
«Mostrar más», que no desaparece. **También vive en la BD**: repetir en Contabo.

El mecanismo está en `user/filters/lib.php`: todos los campos nacen marcados
como avanzados y ese ajuste los desmarca uno a uno.

### 3. El aspecto — sección H del SCSS

Solo pintura sobre el marcado que core ya emite:

- Los dos `fieldset` (Nuevo filtro / Filtros activos) pasan a ser **tarjetas**,
  para que se lean como bloques y no como texto suelto sobre la página.
- La fila del filtro va **en línea** (etiqueta · operador · valor) en vez de
  estirarse por tres columnas de rejilla con un hueco muerto en medio.
- **«Mostrar más» baja al final** del bloque con `order: 99`. Core lo emite
  ARRIBA del campo, que es donde nadie lo busca.
- El recuento («7 Usuarios») deja de ser un `h2` de titular y pasa a dato, con
  una barra de acento.

**TRAMPA, y costó una pasada**: core esconde los campos avanzados con

```scss
.jsenabled .mform .containsadvancedelements .advanced { display: none; }
```

Un selector con **id** le gana en especificidad. Poner `display: flex` a secas
sobre `#id_newfiltercontainer .fitem` **desplegaba los 98 filtros de golpe** sin
dar ningún error. Por eso el `display` solo se declara en
`:not(.advanced)` y en `.advanced.show`.
