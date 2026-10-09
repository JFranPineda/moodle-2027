# Juegos y gamificación por temas

Pregunta de origen: ¿se pueden meter juegos lúdicos por tema? Sí — lo principal
ya viene instalado.

## Nivel 1 — H5P (instalado, recomendado)

Actividad `H5P` en cualquier sección/semana. Tipos de juego: memoria,
crucigrama, sopa de letras, arrastrar y soltar, emparejar imágenes, flashcards,
vídeo interactivo, hotspots… Todos puntúan al libro de calificaciones.

**Estado (2026-09-18): las librerías YA están instaladas** — Memory Game,
Interactive Video, Mark the Words, Multiple Choice, Question Set y más. Se ven
en *Administración del sitio → H5P → Gestionar librerías* (`/h5p/libraries.php`).
Para crear un juego: curso → *Añadir actividad → H5P*, o **Banco de contenido →
Añadir** si se quiere reutilizar en varios cursos.

**El ojo de esa página NO descarga**: es `\core_h5p\api::set_library_enabled()`,
activa o desactiva el tipo de contenido. Si el enlace dice `action=disable`, esa
librería está ENCENDIDA y el clic la apagaría. No tocar.

**Moodle NO descarga del hub de H5P.** Su menú H5P tiene solo tres páginas
(`admin/settings/h5p.php`): resumen, gestionar librerías y ajustes — no hay
botón de descarga. Esa función es del plugin H5P de WordPress/Drupal, no del
core de Moodle; confundirlo cuesta media hora buscando un botón que no existe.
Un tipo de contenido nuevo se instala **subiendo un fichero `.h5p`**: bajarlo de
h5p.org (en el ejemplo del tipo, *Reuse → Download as .h5p*) y subirlo en
**Banco de contenido → Subir**. Las librerías quedan instaladas para TODO el
sitio, se suba en el contexto que se suba.

### Qué tipo de H5P para qué idea

El error habitual es pedir un juego que no encaja con lo que se quiere
practicar. **Una sopa de letras esconde PALABRAS**: no admite «5 + 3», por mucho
que sea el juego que primero se le ocurre a todo el mundo para primaria.

| Lo que se quiere practicar | Tipo H5P | Cómo queda |
|---|---|---|
| Operación ↔ resultado | **Memory Game** | Carta `−7 + 3` ↔ carta `−4`. El mejor para primaria: visual, sin teclear |
| Colocar resultados | **Drag and Drop** | Fichas con números sobre una lámina de operaciones |
| Cálculo contrarreloj | **Arithmetic Quiz** | Genera él solo las operaciones |
| Pistas que son operaciones | **Crossword** | «El doble de 15» → `30` en las casillas |
| Vocabulario del tema | **Find the Words** | SUMA, RESTA, ENTERO, POSITIVO… aquí SÍ encaja el pupiletras |
| Parar un vídeo y preguntar | **Interactive Video** | Sobre un vídeo corto de YouTube |

**Enteros negativos**: cada casilla de `Crossword` es un carácter y el signo `−`
es dudoso. Con resultados positivos va seguro; para negativos, probar UNA pista
antes de escribir veinte, o tirar de `Memory Game`, donde la respuesta es una
carta entera.

**Al montar un Memory Game, que no se repitan resultados** (`8 − 12` y `−7 + 3`
dan ambos `−4`): el emparejamiento se vuelve ambiguo y el juego se rompe.

## Nivel 2 — nativo complementario

- **Insignias** (habilitadas): medallas automáticas al completar semanas/cursos.
  *Administración del curso → Insignias*.
- **Cuestionario** en comportamiento "interactivo con varios intentos" + pistas.
- **Lección**: recorridos ramificados según respuestas.
- **Combinación con el goteo semanal** ([self-paced-weeks.md](self-paced-weeks.md)):
  el juego H5P de la semana N como candado de la semana N+1, más insignia al
  completar. Jugar → avanzar → medalla.

## Nivel 3 — plugins de terceros (CONGELADO hasta después de la migración)

- `mod_game`: ahorcado, millonario, serpientes y escaleras, sudoku, cryptex —
  se alimentan del banco de preguntas o glosarios propios.
- `block_xp` (Level Up XP): puntos, niveles y ranking por curso. Es la única
  forma de tener una **tabla de clasificación**; Moodle no la trae.

Ninguno instalado, y **de momento no se instala ninguno** (decisión 2026-09-14):
el sitio migra a **5.3 LTS** a partir del 5 oct 2026, y hoy el árbol está limpio
— core + `theme/richimath` + `local_richimath`, cero terceros. Cada plugin
añadido antes de la migración es una pieza más que puede no tener versión
compatible el día que haga falta. H5P e insignias son **core**: viajan con
Moodle y no añaden riesgo.

Regla del repo cuando llegue el momento: los plugins se copian a su directorio
estándar y se commitean con la versión anotada en MEMORY.md; auditar
mantenimiento y compatibilidad antes.

## Lo que NO se puede (y conviene decirlo antes de prometerlo)

Las plataformas de moda para esta edad —**Blooket, Kahoot, Gimkit, Prodigy,
99math**— son servicios externos. Desde Moodle solo se pone un enlace:

- **La nota NO vuelve a Moodle.** El alumno juega fuera y el resultado se queda
  fuera. No entra al libro de calificaciones; el profesor lo copia a mano o no
  lo califica. La integración real exigiría LTI, que sus planes gratuitos no
  ofrecen.
- **Son menores.** Primaria = niños de 10-11 años abriendo cuentas en servicios
  estadounidenses que recogen sus datos. Es una decisión de la academia y de las
  familias, no técnica. **Prodigy** además vende suscripción a los padres
  *dentro del juego*: en un aula de pago eso es un problema de forma.
- Excepción limpia: **PhET** (Univ. de Colorado). Simuladores libres,
  incrustables, sin cuentas ni registro. No califican, pero para explicar
  fracciones, áreas o recta numérica valen mucho.

**Tampoco existe un leaderboard nativo.** El libro de calificaciones enseña a
cada alumno SOLO sus propias notas — no sirve como tabla de posiciones, por
mucho que lo sugieran las guías que circulan. Eso es `block_xp`, y está
congelado hasta después de la migración.

**Ojo con las listas de tipos de H5P que dan las IA**: `Crossword` e
`Interactive Video` existen; otros nombres que circulan (p. ej. «The Chase») no
constan en el catálogo oficial. El catálogo real se ve al entrar al hub — mirar
ahí antes de prometer una actividad concreta.

## La estética ya está resuelta

Las guías insisten en «evitar portales infantiles, preferir interfaces
modernas». Eso ya está hecho: cada nivel tiene su tema
(`rmprimaria`, `rmsecundaria`, `rmpreu`, `rmuniversidad`), asignado por el plan
del alumno. Ver [plans-and-appearances.md](plans-and-appearances.md).

## Juegos inmersivos (escape rooms 360°, y el 3D real)

Qué significa de verdad «juegos en 3D» en Moodle, con un escenario por nivel
—primaria, secundaria, pre universitario, universitario y Bachillerato
Internacional— y por qué el 360° llega antes que Unity:
[immersive-games-plan.md](immersive-games-plan.md).

## Recetas concretas

Seis juegos montados y listos para copiar, sobre sumas y restas con enteros
(primaria), más cómo encadenarlos con restricciones e insignias:
[math-games-primary.md](math-games-primary.md).

## Sugerencia de arranque

Piloto en un curso: descargar Memory Game + Crossword, crear un juego para la
Semana 1, encadenarlo como candado de la Semana 2 y crear la insignia "Semana 1
superada". Validar con un alumno real antes de replicar.
