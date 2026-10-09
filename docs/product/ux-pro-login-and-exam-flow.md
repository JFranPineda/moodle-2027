# Login y flujo de examen según UX_PRO

Los tableros de `docs/ux_pro/` traen **dos sistemas de diseño distintos**, cada
uno con su `DESIGN.md`, y este documento cuenta cómo se llevaron al tema:

| Carpeta | Sistema | Qué gobierna |
|---|---|---|
| `LOGIN DE USUARIO/` | **Crystalline Academic Glass** | La puerta de entrada: vidrio, halos, esquinas suaves (4–12px), Space Grotesk + Inter + JetBrains Mono |
| `FLUJO DE EXAMENES/` | **Imperial Academic Precision** | Las cuatro pantallas de `mod_quiz`: obsidiana, filos de platino, **esquina 0px**, Newsreader + Geist + JetBrains Mono |

## La regla que resuelve el choque

Los dos sistemas piden paletas y tipografías propias (cian diamante uno, oro
imperial el otro) y el encargo pide **mantener el color, el logo y la tipografía
de cada plan** ([planes y apariencias](plans-and-appearances.md)). No se puede
tener todo, así que la regla es explícita:

1. **La estructura, la densidad, la forma y la jerarquía son de UX_PRO.** Es lo
   que hace que una pantalla se reconozca: la tarjeta partida del login, la
   cinta de identidad del examen, la matriz de preguntas, los metadatos
   monoespaciados, la esquina recta del examen frente a la suave del login.
2. **El color es del nivel.** El oro `#f59e0b` del tablero de examen se lee como
   un **rol**, no como un color: el token `$od-exam-accent` lo ocupa con la
   clave del nivel. Un examen se ve de Primaria en Primaria y de Universidad en
   Universidad, que es justo lo que el logo por nivel empezó a hacer.
3. **La tipografía es del nivel, con una excepción**: **JetBrains Mono**, la
   única familia que ambos `DESIGN.md` comparten, entra como *capa técnica* —
   temporizador, identificadores, estados, etiquetas y metadatos. Nunca prosa.

Un detalle que conviene saber: **el login siempre se pinta con el tema del
sitio**, porque el tema del usuario no existe hasta que hay sesión. La puerta de
entrada es, por tanto, la marca común (azul Odyssey + RM dorado); los cuatro
niveles se separan a partir del primer clic.

## Login — Crystalline Academic Glass

Plantilla `theme/richimath/templates/core/loginform.mustache`, estilos en la
**sección A** de `post.scss`.

- **Barra de identidad**: isotipo RM, nombre de marca, subtítulo del campus,
  chip de conexión segura y el selector de idioma de Moodle.
- **Tarjeta partida**: a la izquierda el héroe (chip de portal, titular, bajada,
  la tarjeta del logo y el pie con el estado); a la derecha las credenciales.
- **Campos** con icono a la izquierda, halo de foco de 3px, botón de mostrar /
  ocultar contraseña, enlace de «¿Olvidó su contraseña?» junto a la etiqueta.
- **CTA** con degradado del primario al contenedor, bisel interior y resplandor
  en hover, esquina de 6px como pide el sistema.
- Debajo: proveedores de identidad (solo si el sitio tiene alguno configurado),
  instrucciones, acceso de invitado, alta de cuenta y el aviso de cookies.

**Lo que no se implementó, y por qué**: los botones de **Google y Microsoft 365**
del tablero son SSO que este Moodle no tiene configurado — se pintan solos
cuando exista un `auth_oauth2`, y no antes. La casilla **«recordar nombre de
usuario»** tampoco existe: Moodle guarda ese dato en una cookie que decide el
ajuste `rememberusername` del sitio, así que la casilla no controlaría nada.
Los enlaces legales del pie (privacidad, reglamento, mesa de ayuda) apuntaban a
páginas inventadas; el pie quedó con el nombre del sitio y el crédito de Moodle.

## Flujo de examen — Imperial Academic Precision

Estilos en la **sección F** de `post.scss`, sin tocar una línea de `mod/quiz`
(regla número uno del repo). Las cuatro pantallas del tablero se corresponden
una a una con las de Moodle:

| Tablero | Página real | Qué se rediseñó |
|---|---|---|
| 1. Vista previa e instrucciones | `mod/quiz/view.php` | Cinta obsidiana con el nombre del examen; la ficha técnica (intentos, límite, método, nota de aprobación) como lista de datos monoespaciada; CTA de acento |
| 2. Cuestionario en curso | `attempt.php` | Temporizador obsidiano **pegajoso**; tarjeta de pregunta con franja de estado (número, estado, bandera); respuestas como filas tabulares; matriz de navegación |
| 3. Resumen del intento | `summary.php` | Tabla de verificación con estratos alternos y estados por valor; bloque de envío irreversible con filo de acento |
| 4. Revisión y calificación | `review.php` | Hoja de calificación monoespaciada; tarjetas por pregunta con su estado; retroalimentación con barra de acento |

**Superficie clara, cromo oscuro.** El tablero 1 es negro entero, pero los
tableros 2 y 3 —donde el alumno de verdad *lee y resuelve*— son claros. Se siguió
esa lectura: la obsidiana se queda en la cinta de identidad y en el
temporizador, que es donde comunica «esto es un examen», y el área de trabajo
sigue clara para no pelear con cada fórmula.

**El fondo de examen de Richi** (`pix/examground.jpg`, sección D8) se mantiene
—es marca, no adorno— pero con el velo blanco al 90 % y la esquina recta, para
que la marca de agua nunca compita con un enunciado.

**Lo que no se implementó, y por qué**: el tablero inventa datos que Moodle no
tiene — proctoring y «foco de ventana», *scratchpad* de derivadas, solucionario
paso a paso, exportación a PDF, foto y firma del docente, latencia de red,
hash de integridad, cadencia de resolución. Pintarlos sería inventar
información delante de un alumno en examen. Se implementó lo que el LMS sí
sabe; si alguna de esas piezas se quiere de verdad, cada una es una funcionalidad
propia (un plugin o un servicio), no un estilo.

## Trampas pagadas

1. **Boost fija el ancho del login con `!important`**
   (`.login-container { width: 500px !important }`), así que la tarjeta partida
   solo se ensancha respondiendo en el mismo tono.
2. **El layout del login envuelve la región en una `.row` de Bootstrap**: sus
   gutters negativos sacan 15px fuera del viewport y levantan una barra de
   desplazamiento horizontal. Se neutralizan en la página de acceso.
3. **`.que .info` va flotado a la izquierda con 7em** en Boost. Para convertirlo
   en la franja superior del diseño hay que soltar el `float` primero.
4. **`.trafficlight` de la matriz de preguntas es una capa absoluta a pantalla
   completa** (`top:0;bottom:0`) pintada de blanco: tapa el número. Liberándola
   del `top` se convierte en la regla de estado de 3px que dibuja el tablero.
5. **Core estiliza la matriz como `.path-mod-quiz #mod_quiz_navblock …`**, con id
   *y* clase: cualquier selector más corto pierde la cascada aunque venga
   después. Por eso la sección F entra con la clase del `body`.
6. **El acento del nivel se apaga sobre la obsidiana**: se elige contra un lienzo
   claro y cae por debajo de 4.5:1 sobre negro. `$od-exam-accent-bright` es el
   mismo acento aclarado, y es el que va sobre el cromo oscuro.
7. **El entorno local corre con `themedesignermode`** en `config.php`: sirve el
   CSS de cada plugin por separado y **en otro orden**, así que un estilo puede
   verse bien en producción y perder en local. Verificar siempre con el modo
   como esté en local: si gana ahí, gana en el bundle.

## Cómo sembrar un examen para probar

El generador de preguntas de PHPUnit no sirve fuera de PHPUnit. La receta que sí
funciona en CLI, y con la que se verificó esto:

1. `add_moduleinfo()` con el módulo `quiz` (hace falta
   `\core\session\manager::set_user(get_admin())` antes: las áreas de ficheros
   borrador exigen un usuario real).
2. Importar preguntas **GIFT** con `qformat_gift` sobre la categoría del
   contexto del cuestionario, y añadirlas con `quiz_add_quiz_question()`.
3. `add_moduleinfo()` **no guarda las opciones de revisión** (llegan del
   formulario como casillas): si el alumno ve «No está autorizado para revisar
   este intento», hay que fijar los siete campos `review*` a mano.

## Verificado en local (2026-09-05)

Con `estudiante.demo`, recorriendo el flujo completo — vista previa → intento →
resumen → revisión — en el tema de su plan, más el mismo recorrido con el plan
Universitaria para comprobar que el acento sigue al nivel. Login a 1440 y 390 px
sin desbordes; examen a 1440 y 390 px. Capturas del recorrido en el traspaso de
la sesión.
