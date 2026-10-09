# Juegos de matemática para primaria — recetas paso a paso

Para quien monta los cursos en Moodle. Cada receta es un juego completo con el
contenido ya escrito: se copia, se pega y queda.

Tema de ejemplo en todas: **sumas y restas con enteros** (10-11 años). Cambiando
los números sirven para cualquier otro.

Contexto general y qué NO se puede hacer: [gamification.md](gamification.md).

## Antes de empezar (una vez)

Comprueba qué tipos de contenido tienes en *Administración del sitio → H5P →
Gestionar librerías* (`/h5p/libraries.php`). La lista es alfabética — baja y
sube del todo, que es larga.

Confirmados instalados a 2026-09-18: **Memory Game**, **Interactive Video**,
**Mark the Words**, **Multiple Choice**, **Question Set**, Multimedia Choice,
Interactive Book, Personality Quiz, KewAr Code, Page.

Si falta alguno de los que usan estas recetas (`Crossword`, `Find the Words`,
`Drag the Words`, `Arithmetic Quiz`), se instala subiendo un `.h5p`:
h5p.org → el tipo → su ejemplo → *Reuse* → *Download as .h5p* → en Moodle,
**Banco de contenido → Subir**. Queda para todo el sitio.

**El ojo de esa página enciende y apaga, no descarga.** Si el enlace dice
`action=disable`, esa librería ya está encendida.

## Camino común a todas las recetas

Se repite en las seis, así que aquí una vez:

1. Curso → **Modo de edición** (arriba a la derecha)
2. En la semana que toque → **Añadir una actividad o un recurso**
3. **H5P**
4. *Nombre*: el del juego, con la semana («Enteros — Memoria, Semana 1»)
5. En el editor, **Seleccionar tipo de contenido** → el que diga la receta
6. Rellenar (ver cada receta)
7. Abajo, **Calificación** → *Puntuación máxima* 10
8. **Finalización de actividad** → *Mostrar la actividad como completada cuando
   se cumplan las condiciones* → marcar **El estudiante debe recibir una
   calificación**
9. **Guardar y mostrar**

El paso 8 es el que después permite encadenar semanas e insignias (ver el final).

---

## 1. Memoria de enteros — `Memory Game`

El más agradecido para primaria: no hay que teclear nada y es puro emparejar.

**Tipo**: `Memory Game` · **Instalado**: sí

En el editor, cada par es una tarjeta con su *Matching image* o, más simple,
usando el campo de texto alternativo. Ocho pares:

| Carta A | Carta B |
|---|---|
| `−7 + 3` | `−4` |
| `−2 − 6` | `−8` |
| `4 − 9` | `−5` |
| `−5 + 11` | `6` |
| `8 − 15` | `−7` |
| `−3 − 3` | `−6` |
| `10 − 13` | `−3` |
| `−1 + 10` | `9` |

⚠️ **Los ocho resultados son distintos a propósito.** Si dos operaciones dan el
mismo número, el juego acepta un emparejamiento equivocado como bueno y el
alumno aprende mal. Al inventar los tuyos, compruébalo antes.

Ocho pares = 16 cartas, que a esta edad es el máximo razonable. Con más, se
frustran.

---

## 2. Pupiletras del vocabulario — `Find the Words`

**Aquí es donde encaja la sopa de letras.** No admite operaciones: esconde
palabras. Sirve para fijar el lenguaje del tema, no el cálculo.

**Tipo**: `Find the Words` · **Comprueba que lo tienes**

Palabras a esconder:

```
SUMA
RESTA
ENTERO
POSITIVO
NEGATIVO
SIGNO
RECTA
CERO
OPUESTO
VALOR
```

Cuadrícula de 12×12 y *Show vocabulary* activado, para que vean qué buscan.

---

## 3. Crucigrama de resultados — `Crossword`

**Tipo**: `Crossword` · **Comprueba que lo tienes**

Dos formas, y la segunda es mejor para primaria.

### Variante A — la respuesta es el número

| Pista | Respuesta |
|---|---|
| El doble de 15 | `30` |
| 25 menos 9 | `16` |
| 7 más 8 | `15` |
| La mitad de 48 | `24` |

⚠️ **Solo resultados positivos.** Cada casilla del crucigrama es un carácter y
el signo `−` no encaja bien. Antes de escribir veinte pistas, prueba UNA con
resultado negativo y mira qué pasa.

### Variante B — la respuesta es el número escrito (recomendada)

| Pista | Respuesta |
|---|---|
| 15 + 15 | `TREINTA` |
| 20 − 4 | `DIECISEIS` |
| 9 + 6 | `QUINCE` |
| 50 ÷ 2 | `VEINTICINCO` |

Se cruzan mucho mejor (son palabras largas), no hay problema de signos, y de
paso practican escribir los números. Sin tildes: el crucigrama no las maneja.

---

## 4. Completar el resultado — `Drag the Words`

**Tipo**: `Drag the Words` · **Comprueba que lo tienes**

Es texto puro: lo que va entre asteriscos se convierte en ficha arrastrable. No
hace falta ninguna imagen — por eso va este y no `Drag and Drop`, que exige
preparar un fondo en PNG.

Pega esto tal cual en el campo de texto:

```
Arrastra cada resultado a su operación.

−7 + 3 = *−4*
−2 − 6 = *−8*
4 − 9 = *−5*
−5 + 11 = *6*
8 − 15 = *−7*
−3 − 3 = *−6*
```

Aquí los resultados repetidos **sí** darían problema, igual que en el memoria:
mantenlos distintos.

---

## 5. Cazar los negativos — `Mark the Words`

**Tipo**: `Mark the Words` · **Instalado**: sí

El alumno hace clic sobre las operaciones cuyo resultado es negativo. Lo que va
entre asteriscos es lo correcto:

```
Marca las operaciones cuyo resultado es NEGATIVO.

*3−8* 10−4 *−5+1* 7+2 *−6−1* 12−3 *0−9* 5+5
```

Bueno para comprobar si entienden el signo sin tener que calcular del todo.

---

## 6. Vídeo con preguntas — `Interactive Video`

**Tipo**: `Interactive Video` · **Instalado**: sí

1. Busca en YouTube un vídeo corto de enteros (5-8 minutos, no más)
2. En el editor, pega la URL del vídeo
3. Pestaña **Add interactions**: avanza la línea de tiempo y para donde quieras
   preguntar
4. Añade una **Single Choice Set** o **Multiple Choice** en ese punto
5. En *Adaptivity*, marca que **no pueda seguir** hasta acertar

Tres o cuatro preguntas por vídeo. Más, y abandonan.

---

## Encadenar: jugar → avanzar → medalla

Lo que convierte seis juegos sueltos en una progresión.

### El juego abre la semana siguiente

1. Semana 2 → **Editar ajustes** de su primera actividad
2. **Restricción de acceso** → *Añadir restricción* → **Finalización de
   actividad**
3. Elegir «Enteros — Memoria, Semana 1» → *debe estar marcada como completada*
4. **Clic en el ojo de la restricción** para tacharlo: así los que no han jugado
   ni ven que existe, en vez de verlo en gris

Esto necesita que el juego tenga *Finalización de actividad* configurada — el
paso 8 del camino común.

### La medalla al terminar

1. Curso → **Más** → **Insignias** → **Añadir una nueva insignia**
2. Nombre: «Enteros dominados». Súbele una imagen
3. **Criterio** → *Finalización de actividad* → marca los juegos de la semana
4. **Habilitar acceso** (si no, la insignia existe pero no se entrega nunca)

El paso 4 se olvida siempre.

## Antes de enseñárselo a nadie

Pruébalo con `estudiante.demo`, no como administrador. El admin ve y puede todo,
así que una actividad mal configurada le funciona igual — y te enteras en clase.
Es la misma trampa que dejó a los alumnos fuera del BigBlueButton.
