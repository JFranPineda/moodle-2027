# Escape room 360° — «Estación Kepler» (piloto de secundaria)

Un juego listo para subir: una sala 360°, cuatro puntos y un puzle de teorema de
Pitágoras. Es el piloto que decide si el formato engancha antes de invertir en
más escenarios.

Plan completo por niveles: [immersive-games-plan.md](immersive-games-plan.md).

## Qué es: DOS piezas, y el motivo

| Pieza | Fichero | Qué aporta |
|---|---|---|
| **La sala 360°** | `assets/h5p/estacion-kepler.h5p` (3,1 MB) | La historia y **el método**. Sin un solo número |
| **El cuestionario** | `assets/h5p/kepler-preguntas.xml` | **Los números**, distintos para cada alumno |

Nivel secundaria, 15-20 minutos entre las dos, calificación automática.

### Por qué están separadas

**H5P no puede aleatorizar.** Un `.h5p` es un paquete estático: el
`content.json` se congela al empaquetar y todos los alumnos ven exactamente los
mismos números. El primero que termina le dicta las respuestas al resto.

**Moodle sí, con las preguntas Calculadas.** El enunciado, la respuesta correcta
y **cada distractor** son fórmulas sobre comodines (`{a}`, `{b}`), y cada
intento saca una fila distinta del conjunto de datos. Es del núcleo de Moodle,
sin plugins.

De ahí el reparto: **la sala enseña a resolverlo, el cuestionario lo evalúa**.
Es mejor pedagogía además — el método se aprende una vez y se aplica a números
que cambian.

**El paquete de la sala trae sus propias librerías**, así que instala *Virtual
Tour (360)* al subirlo aunque el sitio no lo tuviera.

### La historia

Un micrometeorito abre una grieta en el casco. Los sensores no la alcanzan: solo
miden sus dos sombras sobre ejes perpendiculares. El alumno deduce que la grieta
es la hipotenusa y repara la nave en cinco pasos.

La gracia es que **el dato que falta no se puede medir, solo deducir**. Eso es
Pitágoras haciendo algo útil, no un ejercicio.

### Las cinco preguntas

| # | Paso | Qué se calcula |
|---|---|---|
| 1 | La grieta | Hipotenusa desde los dos catetos |
| 2 | El sellador | División con redondeo hacia arriba |
| 3 | La segunda grieta | Cateto desde hipotenusa y el otro cateto |
| 4 | El parche | Área del triángulo |
| 5 | El borde | Perímetro |

Cada paso continúa la historia del anterior. **Los distractores no son números
al azar**: son los errores reales (sumar los catetos, olvidar el ÷2, redondear
hacia abajo), también como fórmula, así que cambian con cada alumno.

Diez ternos pitagóricos en el conjunto de datos, elegidos para que **toda
hipotenusa salga entera**. Con rangos aleatorios saldría 7,2809… y el ejercicio
de razonamiento se convertiría en uno de calculadora.

## Para el profesor: subirlo y usarlo

**No hace falta un administrador.** El rol Profesor ya tiene las capacidades
`moodle/contentbank:access`, `:upload`, `:useeditor` y `moodle/h5p:deploy`
(comprobado en la BD el 2026-09-23). Un profesor monta esto solo, de principio
a fin.

### Subirlo al banco del curso

1. Curso → **Más** → **Banco de contenido**
2. **Subir** → elige `estacion-kepler.h5p` → **Guardar**
3. Queda en el banco de **ese curso**

Si lo quieres disponible para todos los cursos a la vez, el mismo paso pero en
*Administración del sitio → Banco de contenido* — eso sí pide administrador.

### En cada curso donde lo quieras

1. Curso → **Modo de edición**
2. En la semana que toque → **Añadir una actividad o un recurso** → **H5P**
3. *Nombre*: «Estación Kepler — Sella la grieta»
4. En **Archivo del paquete**, botón *Elegir un archivo* → pestaña **Banco de
   contenido** → selecciona el juego
5. **Calificación** → *Puntuación máxima*: 10
6. **Finalización de actividad** → *Mostrar la actividad como completada cuando
   se cumplan las condiciones* → marcar **El estudiante debe recibir una
   calificación**
7. **Guardar y mostrar**

El paso 6 es el que permite después encadenar la semana siguiente y la insignia
— ver el final de [math-games-primary.md](math-games-primary.md).

### Cambiar las preguntas sin tocar nada raro

Banco de contenido → el juego → **Editar**. Se abre el editor visual:

- **Preguntas y textos**: en el panel de la izquierda, la escena «Sala de
  Máquinas» lista los cuatro puntos. Pincha uno y edita su contenido.
- **Mover un punto**: arrástralo sobre la imagen. El editor recalcula la
  posición solo — no hay que escribir coordenadas.
- **Cambiar la sala**: sustituye la imagen de la escena por otra panorámica.
  Tiene que ser **equirectangular con proporción 2:1** (4096×2048 va bien). En
  [Poly Haven](https://polyhaven.com/hdris) hay cientos libres: descarga el
  *Tonemapped JPG*.

⚠️ **Edita siempre la copia del curso o duplica antes.** Si editas el original
del banco, cambias el juego en todos los cursos que lo usen.

## Crear un escape room nuevo desde cero, como profesor

Sin tocar ficheros ni scripts, todo por interfaz.

### 1. Conseguir la panorámica

Tiene que ser **equirectangular, proporción 2:1** (4096×2048 va perfecto).
Gratis y sin registro en [Poly Haven](https://polyhaven.com/hdris): abre una,
pestaña de descargas, y baja el **Tonemapped JPG**. Son CC0, se pueden usar sin
atribución (aunque ponerla es de buena educación).

También vale una foto tomada con cámara 360, y engancha más: los alumnos
reconocen el aula.

### 2. Crear el contenido

1. Curso → **Más** → **Banco de contenido**
2. **Añadir** → **Virtual Tour (360)**
3. *Título*: el nombre del juego
4. **Añadir escena** → sube la panorámica → tipo **360**
5. Ponle nombre a la escena y guarda

### 3. Colocar los puntos

Dentro de la escena, la barra de arriba tiene los tipos de punto:

| Botón | Para qué |
|---|---|
| **Texto** | La pista, el enunciado, los datos |
| **Imagen** | Un plano, una gráfica, una foto del problema |
| **Single Choice Set** | El puzle: preguntas con una respuesta correcta |
| **Ir a la escena** | El «candado»: lleva a la sala siguiente |

Para cada uno: pincha el botón, **arrastra el punto sobre la imagen** donde lo
quieras, y rellena su contenido. El editor calcula la posición solo.

**Ponle rótulo a cada punto** (campo *Label*). Sin rótulo el alumno gira a ciegas
buscando puntos invisibles, y eso es frustración, no puzle. Numéralos
(`1 · …`, `2 · …`) para marcar el orden.

### 4. Encadenar salas

Con dos o más escenas, un punto **Ir a la escena** en la primera abre la
segunda. Para que sea un candado de verdad, mete ese punto en un rincón poco
visible y di dónde está **solo en el mensaje de respuesta correcta** del puzle.
Así lo que abre la puerta es saber, no buscar.

### 5. Llevarlo al curso

Guarda, y sigue los pasos de «En cada curso donde lo quieras» de arriba.

### Diseñar el puzle: lo que funciona

- **Un concepto por escena.** Cuatro escenas, cuatro ideas.
- **El dato que hace falta no debe poder medirse, solo deducirse.** Es lo que
  convierte un ejercicio en un puzle — en este juego, la grieta.
- **Código final con los resultados anteriores**: obliga a resolverlo todo de
  verdad en vez de adivinar la última.
- **Distractores con sentido**: en «6 y 8», el 14 (los suma) y el 48 (los
  multiplica) son los errores reales. Un distractor absurdo no enseña nada.

### Importar el cuestionario

1. Curso → **Más** → **Banco de preguntas** → **Importar**
2. Formato **XML de Moodle** → sube `assets/h5p/kepler-preguntas.xml`
3. Importar. Aparecen las 5 en la categoría «Estación Kepler»
4. Curso → **Añadir actividad** → **Cuestionario**, nombre
   «Consola de reparación»
5. Dentro: **Preguntas** → *Agregar* → *del banco de preguntas* → las cinco
6. *Comportamiento de las preguntas*: **Interactivo con varios intentos**, para
   que la retroalimentación de cada error se lea al momento

Colócalo **justo debajo** de la sala 360 en el curso: el punto 4 de la sala
manda ahí.

### Regenerar los dos paquetes

```bash
python3 scripts/build-h5p-escape-room.py     # la sala
python3 scripts/build-kepler-questions.py    # las preguntas
```

El primero descarga las librerías del hub de H5P y la panorámica de Poly Haven,
reescala y empaqueta (necesita `ffmpeg` y red). El segundo no necesita nada.

El generador de preguntas **verifica cada tanda antes de escribir el XML**:
evalúa las cuatro alternativas sobre los diez ternos y aborta si dos coinciden o
si alguna sale decimal. Si cambias una fórmula y rompe algo, no llegas a
importarlo — te lo dice en la consola.

## Para el alumno: cómo se juega

Esto se puede pegar tal cual en la descripción de la actividad:

> **Estás en la sala de máquinas de la Estación Kepler y hay una fuga.**
>
> 1. **Arrastra con el dedo o el ratón** para girar y mirar alrededor.
> 2. Toca los rótulos numerados. Empieza por el **1 · Informe de avería**.
> 3. Los puntos 2 y 3 te dan los datos. El 4 es la consola: ahí respondes.
> 4. **En móvil o tablet, pulsa el icono de pantalla completa** (arriba a la
>    derecha) antes de empezar. Sin eso la sala se ve como una franja estrecha.
> 5. Si te pierdes, el botón de la esquina inferior izquierda te devuelve al
>    punto de partida.

## Verificado

Probado en el Moodle local (4.3.12) el 2026-09-23, como administrador:

- Sube al banco de contenido e instala `H5P.ThreeImage 0.5`, `H5P.ThreeSixty
  0.3`, `H5P.SingleChoiceSet 1.11` y `H5P.AdvancedText 1.1`.
- Los cuatro puntos se pintan con su rótulo y responden al clic.
- El informe abre con su formato; la consola abre el cuestionario, baraja las
  respuestas y marca la correcta en verde.
- Al enfocar un punto con el teclado, la cámara gira sola hasta él (accesible
  sin ratón).

**Pendiente de probar con alumnos reales**, que es lo único que decide si el
formato merece más escenarios.

## Trampas pagadas al construirlo

1. **`interactionpos` va en RADIANES en las escenas 360, no en porcentajes.**
   Todos los ejemplos oficiales usan `"52%,17%"` porque sus escenas son
   `static`. En una escena `360` esos valores llegan a
   `ThreeSixty.setElementPosition()`, que hace `Math.sin("50%")` → `NaN`: los
   puntos existen, no dan ningún error y **se pintan en ninguna parte**. El
   formato correcto es `"yaw,pitch"`, yaw de 0 a 2π y pitch de −π/2 a π/2.
2. **Moodle no guarda el título del `h5p.json`**: la tabla `h5p` no tiene
   columna de título. El nombre que se ve sale del Banco de contenido o de la
   actividad, así que da igual lo que diga el paquete.
3. **El validador de H5P necesita un `stored_file`**, no una ruta. Validar por
   CLI con `H5PValidator` a secas revienta con «Using get_file() before file is
   set»; la vía buena es `\core_h5p\helper::save_h5p()` con el fichero ya en el
   almacén.
4. **En móvil el visor 360 colapsa a una franja de ~150 px** y los rótulos se
   pisan. No hay ajuste que lo arregle: la respuesta es pantalla completa, y por
   eso el aviso va dentro del propio informe de avería.
5. Un clic sobre un punto que está **detrás de la cámara** no abre nada aunque
   el botón exista en el árbol de accesibilidad. Para automatizar la prueba hay
   que enfocarlo con el teclado, que sí gira la cámara.
6. **Dos alternativas pueden dar el mismo número sin que se note al escribirlas.**
   Con el terno (6,8,10) el área del triángulo y su perímetro valen ambos 24: la
   correcta y un distractor salían idénticos, y el alumno que elegía «la buena»
   podía quedar suspenso. Por eso el generador evalúa todas las combinaciones
   antes de escribir el XML.
7. **Un decimal entre enteros delata la respuesta.** Un distractor que daba
   5,8309… se descartaba de un vistazo, sin calcular. El verificador también
   rechaza eso.
8. **Si el divisor divide exacto, `ceil()` y `floor()` coinciden.** En la
   pregunta del sellador eso dejaba dos opciones iguales; las longitudes de tira
   están elegidas para que nunca dividan exactamente a la hipotenusa.
