# Planes y apariencias: un diseño por nivel

Pedido de Richi (2026-09-04): una tabla de **apariencias** con los cuatro
diseños de `docs/design/`, visibles en el selector de temas pero no elegibles
ahí; una tabla de **planes** creables por UI; la relación plan → apariencia
(N:1); y la relación usuario → plan (N:1).

## El modelo en una frase

**Apariencia** (código) ← elige → **Plan** (datos, por UI) ← pertenece →
**Usuario**. Cada usuario tiene un plan, cada plan tiene una apariencia, cada
apariencia es un tema de Moodle.

### Apariencias — se definen en código, no se inventan

`local_richimath\appearance` lista las cuatro, una por sistema de diseño:

| Clave | Tema Moodle | Diseño |
|---|---|---|
| `elementary` | `rmprimaria` | `01_elementary_school` (Primary Moodle Odyssey) |
| `highschool` | `rmsecundaria` | `02_high_school` (EduMoodle Campus Secundaria) |
| `preuniversity` | `rmpreu` | `themes/3_PREUNIV` (Pre-Universitario Red & Black) |
| `university` | `rmuniversidad` | `themes/4_UNIVERSIDAD` (Dynamic STEM Academy UI) |

Están en código a propósito: una apariencia solo existe si existe su tema y su
`DESIGN.md` en el repositorio. Agregar una es escribir un tema hijo, no
rellenar un formulario. Los cuatro **se ven** en *Administración del sitio →
Apariencia → Temas → Selector de temas*, como cualquier tema instalado; ahí no
se elige nada para los alumnos — eso lo deciden los planes.

### Planes — son datos

*Administración del sitio → **Planes** → Planes y apariencias*
(`/local/richimath/plans.php`). La instalación crea cinco: **Primaria** (por
defecto), Secundaria, Pre Uni, Universitaria y Admin, **cada uno con la
apariencia de su nivel** — Primaria `elementary`, Secundaria `highschool`,
Pre Uni `preuniversity`, Universitaria y Admin `university`. Se pueden
crear, renombrar, reordenar y borrar. Un plan lleva:

- **Nombre** visible y **nombre corto** estable (clave para código e importaciones).
- **Apariencia**: una de las cuatro.
- **Orden** y la marca **por defecto**. Solo un plan es el de por defecto y no
  se puede borrar: es a donde caen los usuarios sin plan y los de un plan
  eliminado.

### Usuarios — un plan cada uno

*Administración del sitio → Planes → **Usuarios y planes***
(`/local/richimath/userplans.php`): la lista de cuentas reales con un
desplegable por fila que guarda al cambiarlo, filtros por plan y por
nombre/correo, y paginación de 50. Un usuario sin fila cuenta como **Primaria**.

## Cómo se aplica el tema

Al asignar un plan (o al cambiar la apariencia de un plan, que refresca a todos
sus usuarios) se escribe el tema en la columna `theme` del propio usuario, que
Moodle ya respeta. El plugin activa `allowuserthemes` en su instalación —
sin eso el ajuste es inerte.

Se eligió escribir el valor en vez de resolverlo en cada página: deja el
camino caliente sin código nuestro, y un cambio de plan se ve al recargar, no
al volver a iniciar sesión.

## Arquitectura de los temas

Un base y cuatro hijos. `theme_richimath` tiene toda la ingeniería —
plantillas, renderers, las secciones A–E del SCSS— y **todos sus tokens llevan
`!default`**. Cada hijo son unos pocos ficheros y ni una regla copiada:

```
theme/rmprimaria/
├── config.php          $THEME->parents = ['richimath', 'boost']
├── lib.php             delega en theme_richimath_get_main_scss_content($theme, $palette)
├── version.php         depende de theme_richimath
├── scss/palette.scss   LOS TOKENS DE ESTE NIVEL (lo único propio de verdad)
├── style/fonts.css     el @import de Google Fonts de este nivel
├── pix/favicon.ico     el icono de pestaña de este nivel
├── pix_plugins/theme/richimath/whitelogo.png   su logo, pisando el del padre
└── lang/{en,es}/
```

Corolario: una corrección en el base llega a los cuatro. Una regresión
también — por eso la auditoría de contraste de `theme-richimath.md` se corre
sobre cada hijo.

## Los cuatro temas, uno contra otro

Qué cambia de verdad al pasar de un nivel a otro. Todos los valores salen de
`theme/<hijo>/scss/palette.scss` y `style/fonts.css`.

### Color

| Rol (dónde se ve) | Primaria | Secundaria | Pre Uni | Universidad |
|---|---|---|---|---|
| **Primario** — enlaces, marca del navbar, iconos de comunicación | `#004ccd` azul real | `#0037b0` azul índigo | `#b70011` carmín profundo | `#00288e` zafiro profundo |
| **Contenedor** — botón primario, pastilla activa, icono de examen | `#0f62fe` azul brillante | `#1d4ed8` azul cobalto | `#dc2626` carmín | `#1e40af` zafiro |
| **Sombra de tecla** — el borde inferior sólido de los botones | `#003da9` | `#0037b0` | `#991b1b` | `#001453` |
| **Secundario** — barras de progreso, iconos de contenido | `#006d40` verde pino | `#00687a` teal | `#059669` esmeralda | `#00687a` teal |
| **Contenedor secundario** — acento del activo, bordes hover | `#95f3b8` menta | `#57dffe` cian | `#a7f3d0` menta | `#06b6d4` cian spark |
| **Terciario** — iconos de colaboración e interfaz | `#9a3600` coral quemado | `#2c2abc` violeta | `#0f172a` negro azabache | `#003272` azul marino |
| **Lienzo** — el fondo de la página | `#f9f9ff` | `#faf8ff` | `#f7f9fb` | `#faf8ff` |
| **Superficie baja** — hover de filas, cajas tenues | `#f0f3ff` | `#f2f3ff` | `#f2f4f6` | `#f2f3ff` |
| **Tinta** — titulares y texto | `#111c2c` | `#131b2e` | `#191c1e` | `#131b2e` |
| **Tinta secundaria** — etiquetas, texto de apoyo | `#424656` | `#434655` | `#334155` | `#444653` |
| **Hairline** — bordes de tarjeta y tabla | `#c3c6d8` | `#c4c5d7` | `#cbd5e1` | `#c4c5d5` |

Léelo así: **los cuatro lienzos y las cuatro tintas siguen siendo casi el
mismo valor** — el sistema comparte el papel. Lo que separa a un nivel de otro
son las tres primeras filas, el color de acción, y ahí ya no hay confusión
posible: Primaria y Secundaria son azules escolares, **Pre Uni es carmín sobre
azabache** y **Universidad es zafiro con chispa cian**. Los dos últimos se
repintaron el 2026-09-05 desde `docs/design/themes/`, que traen su propio
`DESIGN.md` cada uno; antes eran dos azules casi idénticos.

### Tipografía

| | Titulares | Cuerpo | Se descarga |
|---|---|---|---|
| **Primaria** | **Quicksand** 500/600/700 | **Nunito Sans** 400–800 | `Quicksand\|Nunito+Sans` |
| **Secundaria** | **Plus Jakarta Sans** 500–800 | **Inter** 400–700 | `Plus+Jakarta+Sans\|Inter` |
| **Pre Uni** | **Outfit** 500–800 | **Inter** 400–700 | `Outfit\|Inter` |
| **Universidad** | **Outfit** 500–800 | **Plus Jakarta Sans** 400–800 | `Outfit\|Plus+Jakarta+Sans` |

La diferencia más visible del conjunto. Quicksand es geométrica y de remates
redondeados — infantil sin ser ridícula; Nunito Sans distingue la `I`
mayúscula de la `l` minúscula y del `1`, que es literalmente un requisito de
alfabetización temprana en su `DESIGN.md`. Plus Jakarta Sans da a Secundaria un
aire de herramienta de productividad. **Outfit** corona los dos niveles altos —
geometría con autoridad, la letra de un titular de examen — y debajo cambia el
cuerpo: Inter en Pre Uni, densa y neutra para enunciados largos; Plus Jakarta
Sans en Universidad, de altura de x mayor, para métricas y marcadores. Los
cuatro cargan además **JetBrains Mono** para la capa técnica (temporizadores,
identificadores, metadatos).

### Iconos de actividad (el cuadrado de color de cada recurso)

| Propósito | Primaria | Secundaria | Pre Uni | Universidad |
|---|---|---|---|---|
| Evaluación (cuestionario, tarea) | `#0f62fe` | `#1d4ed8` | `#dc2626` | `#1e40af` |
| Contenido (carpeta, página, URL) | `#006d40` | `#00687a` | `#0f172a` | `#00687a` |
| Colaboración (foro, wiki) | `#9a3600` | `#2c2abc` | `#334155` | `#3b82f6` |
| Comunicación | `#004ccd` | `#0037b0` | `#991b1b` | `#00288e` |
| Administración | `#424656` | `#434655` | `#64748b` | `#444653` |

Y las **barras de acento de las tarjetas de curso**, que rotan por posición:

- Primaria — azul, coral, violeta, esmeralda, rubí, teal (`#0f62fe, #d94f00, #6929c4, #0e7a4a, #da1e28, #005d5d`)
- Secundaria — cobalto, ámbar, violeta, teal, rubí, verde azulado (`#1d4ed8, #b45309, #2c2abc, #00687a, #ba1a1a, #0f766e`)
- Pre Uni — carmín, azabache, carmín oscuro, pizarra, esmeralda, ámbar (`#dc2626, #0f172a, #991b1b, #334155, #059669, #d97706`)
- Universidad — zafiro, azul eléctrico, cian, esmeralda, oro XP, rojo alerta (`#1e40af, #3b82f6, #06b6d4, #10b981, #f59e0b, #ef4444`)

Primaria es la más saturada y variada; Universidad, la más contenida.

### Marca

Cada nivel tiene su edición del isotipo RM (mismo dibujo, distinto color) y su
propio favicon: **oro** Primaria, **lima** Secundaria, **rojo** Pre Uni, **azul**
Universidad. Detalle del pipeline más abajo, en «Un logo por nivel».

### Estructura

| | Rail lateral | Drawer móvil | Radios | Reglas propias |
|---|---|---|---|---|
| Primaria | blanco, pastilla azul con sombra de tecla | blanco | 12/16px | ninguna |
| Secundaria | blanco, ídem | blanco | 12/16px | ninguna |
| **Pre Uni** | blanco, **pastilla carmín** | blanco | **4/8px** (recto, disciplinado) | `scss/post.scss` |
| **Universidad** | **degradado zafiro `#1e40af → #001453`, pastilla blanca con chispa cian** | **zafiro** | **12/16px** (táctil) | `scss/post.scss` |

### Resumen honesto de las diferencias

| Par | Qué los separa |
|---|---|
| Primaria ↔ Secundaria | **Mucho.** Dos familias tipográficas distintas, acentos menta vs cian, terciario coral vs violeta. Se distinguen de un vistazo. |
| Secundaria ↔ Pre Uni | **Máximo.** Se cambia de familia de color entero: azul cobalto contra carmín y azabache, y de esquinas redondeadas a rectas. |
| Pre Uni ↔ Universidad | **Mucho, desde el 2026-09-05.** Carmín recto y disciplinado contra zafiro redondeado y táctil; antes eran dos azules que costaba distinguir. |
| Primaria ↔ Universidad | **Alto.** Extremos del sistema: redondo, cálido y claro contra el ancla zafiro con chispa cian. |

**El límite que conviene saber**: Primaria y Secundaria siguen compartiendo
*layout* y *componentes* — mismas tarjetas, mismos botones, mismos espaciados —
y sus `DESIGN.md` describen personalidades estructurales que **todavía no están
implementadas** (Secundaria pide tarjetas más densas e indicadores de entrega de
alta visibilidad). Pre Uni y Universidad ya no: cada uno tiene su `post.scss`
con lo que su sistema pide y que las variables no alcanzan. Llevar las otras
dos al mismo punto es el mismo trabajo, sin tocar el base.

## Dónde se aparta cada nivel

Primaria y Secundaria son **solo paleta y tipografía**. Los dos niveles altos
tienen además su propio `scss/post.scss`, porque sus sistemas piden cosas que
una variable no alcanza. Ambos se repintaron el **2026-09-05** desde
`docs/design/themes/`, que trae un `DESIGN.md` y una maqueta por nivel.

**Pre Uni** (`rmpreu`) — *Red & Black*, alto contraste para preparación de
admisión (UNI / UNMSM). Carmín `#dc2626` para todo lo que exige atención,
azabache `#0f172a` para la estructura, superficies estériles de laboratorio y
**esquinas rectas** (4px en controles, 8px en tarjetas): su `DESIGN.md` dice
que lo redondeado diluye el rigor. Su `post.scss` añade lo estructural del
sistema: la **regla técnica roja** de 3px sobre el borde superior de la
superficie de trabajo, el **botón técnico en azabache** que el sistema reserva
para envíos finales, el acento izquierdo en la tarjeta sobre la que trabajas y
la cápsula de marca con lavado rojo. El temporizador del examen es su «live
countdown bar»: cifras monoespaciadas sobre azabache.

**Universidad** (`rmuniversidad`) — *Dynamic STEM Academy UI*, EdTech corporativa
con gamificación. Zafiro `#1e40af` para la estructura, azul eléctrico `#3b82f6`
para la interacción y **cian `#06b6d4` como chispa** de progreso, insignias y
rachas; esquinas de 12 y 16px. El rail deja de ser pizarra Blackboard y pasa a
un **degradado zafiro** — lo que su sistema pide para «key navigation bars» —
con la fila activa en pastilla **blanca con tinta zafiro** y una chispa cian en
el filo. La pastilla no va en azul eléctrico por una razón medible: blanco sobre
`#3b82f6` da 3.68:1, bajo el mínimo AA; blanco es el elemento más brillante del
rail y llega a 8.7:1. Botón primario con cara en degradado y elevación al
pasar el cursor, barras de progreso de cian a eléctrico.

Los planes **Universitaria** y **Admin** llevan esa apariencia, así que ambos
ven ese rail.

Cómo se extiende un hijo, por si hace falta para otro nivel: su `lib.php`
concatena su `post.scss` **después** de lo que devuelve el base. El base no se
toca; el hijo solo añade. Las reglas del rail viven dentro de
`body.uses-drawers` y de la media query `lg`, la misma especificidad con la
que el padre las pinta.

## Un logo por nivel

Cada nivel tiene su propia edición del isotipo RM: **oro** en Primaria, **lima**
en Secundaria, **rojo** en Pre Uni y **azul corporativo** en Universidad. El
alumno ve la suya arriba del rail y en la pestaña del navegador.

Las artes las entregó Richi como cuatro tableros de presentación en
`docs/design/logos/LOGO_*/code.html` (860×400: el isotipo sobre un pedestal a la
izquierda, un panel tipográfico a la derecha). Solo el isotipo es un logo, así
que `scripts/build-theme-logos.py` lo recorta y lo rasteriza:

```bash
python3 scripts/build-theme-logos.py   # regenera los ocho ficheros
```

1. Levanta el `<defs>` y el grupo del monograma (`translate(40, 44) scale(1.30)`
   — idéntico en los cuatro tableros) a un SVG propio, `assets/logo-<nivel>.svg`.
2. Lo rasteriza con **Chrome sin cabeza** sobre lienzo transparente. No es
   capricho: el arte usa funciones de filtro CSS (`filter="blur(5px)"`) en
   atributos de presentación SVG, que solo un navegador resuelve — un
   rasterizador clásico devolvería manchas opacas donde van las sombras.
3. Recorta al contenido y escala a 400px de ancho (el rail lo muestra a 56px, o
   34px en el modo compacto: sobra resolución para pantallas retina).
4. Escribe el PNG del rail y, centrado en un cuadrado, el `favicon.ico`
   multitamaño (16/32/48).

**Cómo pisa un hijo el logo del padre, sin tocar nada.** El sidebar lo pide
siempre igual (`{{#pix}} whitelogo, theme_richimath {{/pix}}`), pero Moodle deja
que un tema sustituya la imagen *de otro componente* si la coloca en
`pix_plugins/<tipo>/<plugin>/`. Por eso cada hijo guarda su logo en
`theme/<hijo>/pix_plugins/theme/richimath/whitelogo.png` y
`resolve_image_location()` lo encuentra antes que el del padre. Cero cambios en
plantillas, renderers o SCSS; el base conserva su RM blanco.

El favicon es el caso contrario y hay que saberlo: **no hereda**. Para
`$image === 'favicon'` Moodle devuelve `$this->dir/pix/favicon.ico` sin mirar a
los padres, así que los hijos servían un 404 hasta que cada uno tuvo el suyo.

**Límite conocido**: el login y la portada anónima siempre son del tema del
sitio (`richimath`), porque el tema del usuario no existe hasta que hay sesión.
El RM dorado del login es, por tanto, la marca común de la casa.

## Cuándo se aplica el tema (y cuándo no)

El tema **no se resuelve al pintar la página**: se escribe en la columna
`theme` del usuario, y esa foto solo se toma en dos momentos. Esa única
decisión es lo que hace que las dos reglas pedidas se cumplan a la vez.

| Qué cambias | Qué pasa |
|---|---|
| **El plan de un usuario** | Se le escribe el tema de ese plan **en el acto**. Nadie tiene que ir después a ponerle un tema a mano. Si te cambias el plan a ti mismo, lo ves en la página siguiente, sin volver a entrar. |
| **La apariencia de un plan** | **No toca a nadie.** Quien ya está trabajando sigue con lo que lleva puesto; el cambio le llega **en su próximo inicio de sesión**, que es cuando se vuelve a consultar el plan. |

Lo segundo es deliberado: re-pintar la plataforma de un alumno a mitad de una
clase es peor que esperar a mañana.

El apunte de honestidad: si un administrador cambia el plan de **otro**
usuario que está con la sesión abierta, la columna se actualiza al instante
pero la sesión de esa persona guarda su propia copia — lo verá al volver a
entrar, igual que en el segundo caso.

Quién escribe qué: `plans\theme_assignment` es el único sitio del plugin que
toca `user.theme`; lo llaman `plan_service::assign_user()` (primer caso) y el
observador de `\core\event\user_loggedin` (segundo).

## Arquitectura del código

Cuatro capas, sin interfaces que nadie implementa:

| Fichero | Responsabilidad |
|---|---|
| `plans/appearance.php` | El catálogo en código: qué diseños existen y qué tema los implementa. |
| `plans/plan.php` | La entidad. Sabe qué es un plan y qué lo hace válido; no sabe de tablas, formularios ni temas. |
| `plans/plan_repository.php` · `plans/user_plan_repository.php` | Persistencia. Los únicos ficheros que nombran una tabla. |
| `plans/theme_assignment.php` | La regla de cuándo se escribe el tema. Un solo sitio. |
| `plans/plan_service.php` | Los casos de uso que usan las pantallas y el observador. |
| `plans.php` · `userplans.php` · `form/plan_form.php` · `observer.php` | Entrega. Sin lógica de negocio. |

## Verificado en local (2026-09-04)

Los cuatro temas instalados y visibles en el selector; los cinco planes
sembrados (entonces todos con apariencia `elementary`, hoy uno por nivel).
Cada tema compila con su paleta, su tipografía y su lienzo propios.

Recorrido completo de las dos reglas, con el alumno con la sesión abierta:

1. Plan Secundaria → apariencia `highschool`; `estudiante.demo` asignado a
   Secundaria → su `user.theme` pasó a `rmsecundaria` **en el acto** y al
   recargar vio Plus Jakarta Sans, Inter, azul `#1d4ed8`, lienzo `#faf8ff`.
2. Con él dentro, el plan Secundaria se repuntó a `preuniversity`: su
   `user.theme` **siguió** en `rmsecundaria` y la página no cambió.
3. Cerró sesión y volvió a entrar → `user.theme` = `rmpreu`, y vio Inter,
   lienzo `#f6f9ff`, botón `#0f6cbf`.
4. El administrador se asignó a sí mismo el plan Admin (apariencia
   `university`) → vio Inter, lienzo `#f8f9ff` y azul `#1d4ed8` **sin volver
   a entrar**.
5. Borrar el plan por defecto se rechaza en la UI (no ofrece el botón) y en
   el servicio (`delete_plan()` devuelve `false`).

Rail Blackboard (2026-09-04): con el plan Admin apuntando a la apariencia
`university`, el rail queda en `rgb(15, 32, 66)` con etiquetas blancas al
85 % y acento `rgb(64, 105, 242)` en el activo — barra izquierda de 3px en el
rail ancho, inferior de 3px en el compacto — mientras la tarjeta de contenido
sigue clara. El drawer del móvil comparte fondo y tinta.

Estado entregado: **cada plan con la apariencia de su nivel**. Los cinco se
sembraron con `elementary`, así que Secundaria y Pre Uni se veían idénticos a
Primaria — misma paleta, mismas fuentes y, desde que hay logo por nivel, el
mismo logo. El paso de upgrade `2026090403` reapunta cada plan sembrado a su
nivel, y **solo si sigue con ese `elementary` de fábrica**: una apariencia
elegida por el administrador no se pisa. Cambiar cualquiera sigue siendo un
desplegable por plan.

Recorrido con `estudiante.demo` (2026-09-04), plan a plan y entrando de verdad
cada vez, con `user.theme` borrado antes para que solo el observador de login
pueda vestirlo: Primaria → `rmprimaria` (oro), Secundaria → `rmsecundaria`
(lima), Pre Uni → `rmpreu` (rojo), Universitaria → `rmuniversidad` (azul sobre
el rail oscuro). Capturas del rail en los cuatro casos.

Logo por nivel (2026-09-04): los cinco temas pedidos por URL (`?theme=`) y
capturados a 1440 como admin y como `estudiante.demo`, más 390 en Universidad.
Cada página pide `theme/image.php?theme=<hijo>&component=theme_richimath&image=whitelogo`
y recibe el suyo — oro, lima, rojo y azul —; el base sigue con su RM blanco.
Los cuatro favicons responden 200 (antes, 404). En el rail oscuro de
Universidad el monograma azul lee bien; la M es oscura sobre pizarra y se
sostiene por sus filos claros — si algún día se quiere más contraste, la
salida es la placa blanca del tablero de diseño, no repintar el logo.

## Pendiente / ideas

- **Tema por categoría** para que un curso de primaria se vea de primaria
  aunque lo abra un alumno de la U (`allowcategorythemes`; ver
  [plan multi-tema](../plans/multi-theme-plan.md)). Hoy manda el plan del
  usuario en todas partes.
- Asignar el plan solo al matricularse (observador de eventos), para no tener
  que tocar la lista a mano.
- Columna `plan` en la subida de usuarios por CSV.
