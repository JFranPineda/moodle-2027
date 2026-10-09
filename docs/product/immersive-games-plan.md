# Juegos inmersivos por nivel — qué se puede hacer y qué cuesta

Para Richi y quien monte los cursos. Responde a la petición de «juegos en 3D,
tipo escape room de vida o muerte, donde las claves sean teoremas y
operaciones», segmentada por los cinco niveles de la academia.

Recetas 2D ya listas: [math-games-primary.md](math-games-primary.md).
Contexto y límites generales: [gamification.md](gamification.md).

## 1. «3D» son tres cosas, y solo una es viable ahora

| Vía | Qué es | Esfuerzo por juego | Nota al libro | Veredicto |
|---|---|---|---|---|
| **`Virtual Tour (360)`** de H5P | Fotos 360°: el alumno está *dentro* de la sala, gira, pincha objetos, responde para avanzar | **una tarde** | **sí, automática** | **empezar aquí** |
| Genially / escape rooms externos | Enlace fuera de Moodle | horas | **no** | solo si no importa calificar |
| Unity WebGL / three.js en **SCORM** | 3D real: personaje, física, polígonos | **semanas o meses**, más artista | sí, vía SCORM | más adelante, si el 360° funciona |

**El escape room que describes no necesita 3D real.** Entrar a una sala, mirar
alrededor, encontrar una pista, meter un código: eso es exactamente lo que hace
`Virtual Tour (360)`. El 3D real aporta *caminar*, y caminar no enseña
matemática.

### Por qué el 3D real no toca ahora

No es que no se pueda: es secuencia. Hoy hay encima el BigBlueButton recién
montado, H5P sin estrenar y una migración a **5.3 LTS** a partir del 5 oct 2026.
Abrir un proyecto Unity de meses antes de que el 360° demuestre que los alumnos
juegan sería empezar la casa por el tejado.

**Y el aparato del alumno manda.** Una compilación Unity WebGL pesa entre 20 y
100 MB y quiere GPU decente. Los alumnos entran desde tablets y móviles, con
datos móviles. Un `Virtual Tour` son fotos: pesa una fracción y se mueve en
cualquier cosa. Antes de invertir en 3D real hay que medir con qué entran de
verdad (*Informes → Registros* del sitio lo dice).

## 2. Sobre lo de «vida o muerte»

Para secundaria y arriba, perfecto: la tensión es el motor del juego.

**Para primaria (10-11 años), no.** Mismo mecanismo, otro envoltorio: rescatar a
alguien, reparar algo antes de que se estropee, salvar a un animal. Se consigue
la misma urgencia sin que un padre pregunte por qué su hijo de diez años juega a
morirse. La cuenta atrás y los candados funcionan igual de bien.

## 3. Cómo se monta un escape room 360° (vale para todos los niveles)

**Tipo H5P**: `Virtual Tour (360)`. Comprueba en
*Administración del sitio → H5P → Gestionar librerías* si lo tienes (va por la
V, al final de la lista). Si no, se instala subiendo su `.h5p` al Banco de
contenido — ver [gamification.md](gamification.md).

### Las imágenes 360°

Tres formas, de menos a más esfuerzo:

1. **Panorámicas libres**: Poly Haven y similares dan HDRI/panorámicas
   equirectangulares gratis y de uso libre. Es lo más rápido.
2. **Cámara 360** prestada: fotografiar aulas, laboratorio o pasillos reales de
   la academia. Es lo que más engancha — reconocen el sitio.
3. Generadas por IA, pidiendo formato **equirectangular 2:1**. Salen bien para
   escenarios de fantasía; cuidado con las costuras en los bordes.

Formato: equirectangular, proporción **2:1** (p. ej. 4096×2048).

### La estructura que funciona

1. **Escena de entrada** con el planteamiento y el reloj: «Quedan 20 minutos».
2. **Tres o cuatro escenas**, una por concepto. Cada una con:
   - Un *hotspot* de **texto o imagen** con la pista (el enunciado, el dato, el
     teorema a aplicar).
   - Un *hotspot* de **pregunta** (`Single Choice Set` o `Multiple Choice`)
     cuyo acierto revela el siguiente paso.
   - Un *hotspot* de **ir a la escena siguiente**, que es el «candado».
3. **Escena final** con el desenlace.

**La clave está en el orden**: coloca el hotspot de avanzar en un rincón poco
visible y menciona su ubicación solo en la respuesta correcta. Así el candado
real es el conocimiento, no encontrar el botón.

### Que cuente para la nota

Igual que cualquier H5P: puntuación máxima 10, *Finalización de actividad* con
«debe recibir una calificación», y después se encadena con restricciones e
insignias. Ver el final de [math-games-primary.md](math-games-primary.md).

## 4. Un escenario por nivel

Mismo motor (`Virtual Tour 360`), distinto envoltorio y distinto contenido.

### Primaria — «El Refugio de los Animales»

**Tono**: rescate, sin muerte. **Duración**: 15-20 min.

Una tormenta ha dejado el refugio sin luz y los animales sin comida. Hay que
reactivar tres sistemas antes de que amanezca.

| Escena | Puzle | Contenido |
|---|---|---|
| Almacén | Repartir sacos en partes iguales entre los corrales | División, fracciones sencillas |
| Termostato | La temperatura bajó de 5° a −3°: ¿cuántos grados hay que subir? | **Sumas y restas con enteros** |
| Depósito | Completar litros hasta la marca | Suma con decimales |
| Salida | Código de 3 cifras = los tres resultados en orden | Repaso |

El código final formado por los resultados anteriores es el mejor cierre: obliga
a haber resuelto todo de verdad, no a adivinar.

### Secundaria — «Estación Orbital: 40 minutos de oxígeno»

**Tono**: supervivencia, ya sí. **Duración**: 30-40 min.

Un impacto dejó la estación averiada. Reparar antes de quedarse sin aire.

| Escena | Puzle | Contenido |
|---|---|---|
| Sala de máquinas | Calcular la presión que falta | Ecuaciones de primer grado |
| Casco | Medir la grieta con dos distancias conocidas | **Teorema de Pitágoras** |
| Panel solar | Orientar al ángulo correcto | Razones trigonométricas |
| Comunicaciones | Frecuencia = raíz de una ecuación | Ecuación de segundo grado |

### Pre universitario — «Sala de Control: el simulacro»

**Tono**: presión de examen de admisión. **Duración**: 45-60 min, **con reloj**.
Encaja con la paleta carmín y azabache de `rmpreu`.

Cada escena es un bloque del examen de admisión, cronometrado como el real. No
se trata de ambientar: se trata de **ensayar la presión**.

| Escena | Contenido |
|---|---|
| Módulo I | Factorización y productos notables |
| Módulo II | Trigonometría: identidades y ecuaciones |
| Módulo III | Límites y continuidad |
| Módulo IV | Geometría analítica: recta y cónicas |

Al final, un informe de en qué módulo se perdió más tiempo. Eso vale más que el
juego.

### Universitario — «Planta Industrial: el reactor»

**Tono**: decisión técnica con consecuencias. **Duración**: 60 min.

No hay candados escondidos: hay **decisiones fundamentadas**. Cada elección
cambia el estado de la planta y el alumno ve el efecto.

| Escena | Puzle | Contenido |
|---|---|---|
| Sala de control | Sistema de ecuaciones para equilibrar tres flujos | Matrices, Gauss |
| Torre de enfriamiento | Ritmo de enfriamiento | Ecuaciones diferenciales, derivadas |
| Laboratorio | ¿La muestra está fuera de control? | Probabilidad, distribución normal |
| Informe | Justificar la decisión tomada | Redacción técnica |

### Bachillerato Internacional — «La Investigación»

**Aquí el escape room es mala idea, y conviene decirlo.**

El IB no evalúa si sabes resolver: evalúa **cómo investigas, modelas, justificas
y comunicas**. La *Internal Assessment* es una exploración matemática escrita.
Un juego de candados entrena exactamente lo contrario — respuesta rápida y
única.

Lo que sí sirve, con el mismo `Virtual Tour`:

- **Recogida de datos en escenario**: cada escena tiene datos reales (una
  gráfica, una tabla, una medición). El alumno los recoge y **luego** modela
  fuera del juego. El 360° es el trabajo de campo, no el examen.
- **Dilemas de TOK** (*Theory of Knowledge*) como hotspots: «¿este modelo
  predice o solo describe?», «¿qué supuestos estás aceptando?». Sin respuesta
  correcta única — se responden en el foro del curso y se debaten.
- **Matemáticas AA vs AI**: para AI (aplicaciones), datos reales y modelización;
  para AA (análisis), demostración y estructura.

Para IB, el juego es la **fuente de datos**, y el trabajo de verdad pasa después.

## 5. El piloto ya está construido

El escenario de secundaria descrito arriba existe y funciona:
[escape-room-360.md](escape-room-360.md) — `assets/h5p/estacion-kepler.h5p`,
listo para subir al Banco de contenido. Trae sus propias librerías, así que
instala *Virtual Tour (360)* al subirlo aunque el sitio no lo tuviera.

Lo que sigue explica el criterio con el que se eligió empezar por ahí.

## 5 bis. Por dónde empezar

Un piloto, no cinco. **Secundaria** es el mejor banco de pruebas: edad que más
engancha con el formato, contenido con puzles naturales (Pitágoras, ecuaciones),
y sin la sensibilidad de primaria ni la exigencia de IB.

1. Comprobar si `Virtual Tour (360)` está instalado; si no, subir su `.h5p`.
2. Bajar cuatro panorámicas libres que parezcan una estación espacial.
3. Montar **una sola escena** con un puzle. No las cuatro.
4. Probarlo con `estudiante.demo` — **nunca como administrador**, que lo ve todo
   y no detecta lo que está mal.
5. Dárselo a tres alumnos reales y mirarlos jugar sin ayudarles.

Si a los tres les engancha, se monta el resto y se replica por niveles. Si no,
se ha perdido una tarde en vez de un proyecto de meses.

## 6. Cuándo replantear el 3D real

Tiene sentido abrir ese melón solo si se cumplen **las tres**:

- Los escape rooms 360° se usan y se terminan (los registros lo dicen).
- La migración a 5.3 LTS está cerrada y estable.
- Hay presupuesto para desarrollo y arte, o alguien del equipo que lo haga.

Entonces la vía es **Unity WebGL empaquetado como SCORM**, que es el único
formato que devuelve la nota a Moodle sin depender de un servicio externo. Y se
empieza por *un* juego de *un* nivel, no por una plataforma.
