# Plan para `features_v2.docx` — lo que pide Richi y en qué orden hacerlo

Fuente: [docs/product/v2/features_v2.docx](../product/v2/features_v2.docx) —
5 puntos escritos y 7 capturas. Las capturas llevan la mitad del encargo, así
que primero queda aquí qué dice cada una; si no, dentro de un mes nadie sabrá a
qué se refería «quitarle las líneas».

## 1. Lo que pide, descifrado

| # | Pide | Lo que muestra la captura |
|---|---|---|
| **1** | Evaluación constante del alumno sobre el tema visto, «si se puede generar con IA» | — |
| **2** | Asistente virtual para dudas: registro, cómo funciona la plataforma | — |
| **3** | WhatsApp integrado, para presentarse y pedir información | — |
| **4** | Mejorar el diseño interno | — |
| **5** | «Poner la lista tipo carpetas» | `mod/folder/view.php?id=247`: nueve PDF (TEMA 1…9) en lista plana dentro de la carpeta «Ejercicios adicionales» |
| **otro** | Referencia de a dónde quiere llegar | Artículo «Moodle 5.1: más intuitivo, seguro y con IA bajo control del docente» |
| **otro** | «Copiar en la web que muestre categorías y cursos» | Marketplace de plantillas Moodle (aulasmoodle.com) |
| **otro** | «Rediseño de los iconos» | El selector de actividades con los iconos de serie |
| **otro** | Referencia del interior de un curso | Moodle de *subitus*: rail de secciones, portada con foto, tópicos en tarjeta |
| **otro** | «Poner las categorías: Primaria, Secundaria, **IB**, Pre universitario» | Home de rdtlearning.com como referencia visual |
| **otro** | «Quitarle las líneas» | Intento de cuestionario: las reglas horizontales entre las alternativas A-E |

Curso piloto que él mismo señala para la evaluación: **MATEMÁTICAS PRE UP**.

## 2. El dato que ordena todo el plan

**Moodle trae un subsistema de IA desde la 4.5**: acciones `generate_text`,
`summarise_text`, `generate_image`, la placement **Explicar**, el **Asistente de
curso**, proveedores OpenAI / Azure / Ollama, y hasta informes de uso y de
aceptación de la política de IA.

**Este Moodle es 4.3.12. Nada de eso existe aquí.**

Y hay un segundo dato, igual de importante: **el núcleo NO genera preguntas de
examen con IA**. Lo verifiqué en las notas de la 5.0 — hay «Explicar» y
«Asistente de curso», no generación de preguntas. El punto 1 hay que
construirlo pase lo que pase.

Consecuencia para el orden:

- Los puntos **1 y 2 se apoyan en la migración**. Hacerlos sobre 4.3 es
  construir dos veces.
- El propio Richi adjuntó el artículo de «Moodle 5.1 con IA». **La migración no
  es un obstáculo para su pedido: es parte de su pedido**, aunque él no lo haya
  escrito así.
- Los puntos **4 y 5 y los de diseño no dependen de nada** y pueden salir ya.

## 3. Qué hay construido y qué falta

| Pieza | Estado |
|---|---|
| Cuatro temas hijos por nivel | ✅ `rmprimaria`, `rmsecundaria`, `rmpreu`, `rmuniversidad` |
| **Tema de IB** | ❌ **no existe** — y la categoría IB es nueva en este encargo |
| Sitio institucional público con divisiones | ✅ raíz + `/students` |
| Generar preguntas de Moodle por script | ✅ a medias: `scripts/build-kepler-questions.py` ya emite XML de preguntas calculadas |
| Chat de dudas | ⚠️ existe el botón «Consultas», que abre un chat de Moodle con el admin |
| WhatsApp | ❌ nada |
| Subsistema de IA | ❌ requiere 5.x |

## 4. Los lotes

### Lote A — Diseño, ya (no espera a nada)

Todo es SCSS del tema y ajustes; nada toca core. Es lo que Richi ve primero.

| Tarea | Qué es |
|---|---|
| **A1 · Quitar las líneas del cuestionario** | Captura 7. Las reglas entre alternativas vienen de `.que .answer > div`. SCSS, sección F (que ya existe para el flujo de examen) |
| **A2 · Carpeta tipo carpetas** | Captura 1. `mod_folder` tiene el ajuste **«Mostrar subcarpetas expandidas»** y el formato de visualización; si con eso no basta, SCSS sobre `.foldertree`. **Mirar el ajuste antes de escribir una línea de CSS** |
| **A3 · Rediseño de iconos** | Captura 4. Los iconos de actividad son del núcleo; se pisan desde el tema con `pix_plugins/mod/<modulo>/icon.svg` — el mismo mecanismo que ya usamos para el logo por nivel. **Sin tocar core** |
| **A4 · Interior del curso** | Captura 5 (*subitus*): portada con imagen, tópicos en tarjeta. Buena parte ya está hecha; falta la portada de curso |

Riesgo bajo, visible de inmediato. **Es lo que yo sacaría esta semana.**

### Lote B — Migración a Moodle 5.3 LTS

Sale el **5 de octubre de 2026**. Es la puerta de los puntos 1 y 2, y además
urge por sí sola: **4.3 dejó de recibir parches de seguridad el 21 de abril de
2025**.

Plan y trampas ya escritos en `MEMORY.md` — las cinco plantillas de core
copiadas de la 4.3 que **fallan en silencio**, los tres renderers, los cuatro
temas hijos, `local_richimath` y la subida de PHP.

Dejar pasar 2-3 semanas tras el lanzamiento antes de tocar producción.

### Lote C — Categorías y sitio público (puede ir en paralelo al Lote A)

| Tarea | Qué es |
|---|---|
| **C1 · Crear la categoría IB** | Hoy hay Primaria, Secundaria, Pre Uni y Universidad. IB es nueva |
| **C2 · Tema `rmib`** | Un hijo más de `richimath`: `scss/palette.scss` + `style/fonts.css`, como los otros cuatro |
| **C3 · Plan «Bachillerato Internacional»** | Alta en `local_richimath_plan` apuntando a la apariencia nueva |
| **C4 · Divisiones del sitio** | La raíz ya pinta las categorías de primer nivel: IB entra sola al crearla. Verificar, no reescribir |

**Resuelto (R1)**: IB va al mismo nivel que los otros cuatro, así que lleva
categoría + plan + tema. **Bloqueado en C2**: los otros temas salieron de un
tablero de diseño y para IB no existe ninguno. C1, C3 y C4 se pueden hacer ya.

### Lote D — Evaluación continua con IA (punto 1) · después del Lote B

La parte que no es IA **ya se puede hacer con el núcleo**: banco de preguntas
por tema, cuestionario con preguntas aleatorias, restricción de acceso para
abrir el siguiente tema, insignia al superar. Eso es configuración.

La parte de IA es **generar las preguntas**, y el camino más corto ya está medio
andado: `scripts/build-kepler-questions.py` genera **XML de Moodle** con
preguntas calculadas, distractores por fórmula y un verificador que aborta si
dos alternativas colisionan. Cambiar el contenido fijo por una llamada al
modelo, y mantener el verificador, es una evolución de ese script, no un
proyecto nuevo.

Orden propuesto:

1. **D1** · Piloto manual en MATEMÁTICAS PRE UP: un cuestionario por tema con el
   banco actual, encadenado con restricciones. **Sin IA.** Sirve para ver si el
   formato le vale a Richi antes de automatizar nada
2. **D2** · Generador: material del tema → modelo → XML de Moodle → import. El
   verificador de colisiones y decimales se queda, pase lo que pase
3. **D3** · **Revisión humana obligatoria antes de publicar.** Un modelo genera
   enunciados de matemática con errores sutiles; publicarlos sin que los mire un
   profesor es la forma más rápida de perder la confianza de los padres

⚠️ **Coste recurrente**: cada generación es una llamada de pago. Hay que decidir
quién paga y con qué tope antes de abrir la llave.

### Lote E — WhatsApp del profesor · ✅ HECHO (ver §6 bis)

Richi acotó el punto 3 a un botón flotante al WhatsApp del profesor del curso,
sin asistente. Está construido y verificado. El punto 2 (asistente de dudas)
queda para después de la migración, donde el Asistente de curso de Moodle 5.x
cubre parte.

### Lote E-bis — Asistente virtual (punto 2) · después del Lote B

Aquí hay una ventaja que no se puede desaprovechar: **esto es exactamente lo que
vende SimiAI**. Chatwoot + Constructor + WhatsApp + n8n ya están montados y en
producción para otros clientes.

Dos caminos, y no son excluyentes:

| | Asistente de curso de Moodle 5.x | Widget de SimiAI |
|---|---|---|
| Qué responde | sobre el contenido del curso | sobre **la plataforma, el registro, precios, matrícula** |
| WhatsApp | no | **sí** |
| Esfuerzo | configuración | integración de un widget ya existente |
| Dónde vive la conversación | en Moodle | en Chatwoot, con histórico y CRM |

Lo que pide el punto 2 —«dudas sobre registro, cómo funciona la plataforma»—
**no es el asistente de curso de Moodle**: es soporte, y eso es Chatwoot.

Propuesta: **el widget de SimiAI sustituye al botón «Consultas»** que hoy abre
un chat con el admin, y el mismo asistente atiende por WhatsApp. El Asistente de
curso de Moodle se evalúa aparte, cuando esté la 5.3.

Esto además cierra el círculo con lo construido la semana pasada: los leads de
las sesiones abiertas y las conversaciones del asistente son la misma base de
captación.

## 5. El orden, en una línea

```
Lote A (diseño, ya) ─┬─ Lote C (IB y categorías)
                     └─ Lote B (5.3 LTS, desde el 5 oct) ─┬─ Lote D (evaluación IA)
                                                          └─ Lote E (asistente + WhatsApp)
```

## 6. Respuestas de Richi (2026-10-03) y lo que cambian

**R1 · IB es una categoría «al mismo nivel» que Primaria, Secundaria, Pre
Universitario y Universidad.**
Las otras cuatro son nivel completo: categoría + plan + tema hijo. Se toma como
«el mismo trato». Pero hay un tope real: **los cuatro temas salieron de un
tablero de diseño de Richi** (`docs/design/0X_*`) y **para IB no hay tablero**.
Sin él no se puede inventar una paleta. Lo que se puede hacer ya es la categoría
y el plan; `rmib` espera diseño.

**R2 · Kimi-k2.6 con opción de OpenAI, y omitir si ya viene en la 5.3.**
Dos hallazgos:

- Moodle **no genera preguntas de examen con IA** en 5.0, y la página de la 5.3
  todavía no publica su lista de funciones (sale el 5 oct). **Hay que
  verificarlo ese día antes de construir nada.** Si no viene —lo más probable—,
  el Lote D sigue en pie.
- **No hará falta escribir un proveedor para Kimi.** El proveedor OpenAI de
  Moodle expone un **endpoint configurable**, y Kimi es compatible con la API de
  OpenAI: se configura, no se programa. La «opción de OpenAI» es cambiar ese
  endpoint.

**R3 · No hay asistente: un botón flotante de WhatsApp al número del profesor
del curso, configurable por el profesor y por curso.**
Mucho más barato que lo que yo proponía. **Ya está construido** — ver abajo.

**R4 · Ventana de mantenimiento: 5 de octubre de 2026, 22:00 → 01:00.**
Anotada. La migración la hace Richi; aquí no se toca nada de eso.

## 6 bis. Lo entregado de R3

El botón lee **campos nativos**, no una tabla nuestra: el profesor edita su
número en su perfil y la excepción en los ajustes del curso, pantallas que ya
conoce.

| Dónde | Campo | Qué decide |
|---|---|---|
| Perfil del profesor | `rmwhatsapp` | Su número |
| Perfil del profesor | `rmwhatsappon` | Lo enciende en **todos** sus cursos |
| Ajustes del curso | `rmwhatsapp` (select) | *Lo que decida el profesor* / *Mostrar siempre* / *No mostrar* |

Tres valores y no una casilla: «heredar» tiene que existir, o un profesor que lo
tiene apagado no podría encenderlo en un curso suelto, ni silenciar uno quien lo
tiene encendido.

Matriz verificada en local:

```
profesor apagado   + curso heredar          -> sin boton
profesor apagado   + curso mostrar siempre  -> BOTON
profesor apagado   + curso no mostrar       -> sin boton
profesor encendido + curso heredar          -> BOTON
```

`+51 987 654 321` llega a WhatsApp como `https://wa.me/51987654321`: sin el
limpiado, el enlace abre un chat vacío.

Detalle y trampas: [../product/whatsapp-teacher-button.md](../product/whatsapp-teacher-button.md).

## 7. Lo que queda por decidir

1. **El tablero de diseño de IB.** Sin él no hay tema `rmib`.
2. **Confirmar el 5 de octubre** si la 5.3 trae generación de preguntas con IA.
   Si la trae, el Lote D se reduce a configurarla.

## 7. Lo que NO recomiendo

- **Comprar una plantilla de pago** (la captura 3 es un marketplace de temas).
  `theme/richimath` ya tiene el sistema de diseño completo, cuatro hijos y el
  sitio institucional. Una plantilla comprada tira todo eso y además hay que
  migrarla a la 5.3 igual.
- **Abrir el Lote D antes del B.** Las preguntas generadas se importan igual en
  las dos versiones, pero el asistente y la política de IA de Moodle solo
  existen en 5.x, y acabaríamos con dos sistemas de IA sin relación.
- **Publicar preguntas generadas sin revisión humana.** Ver D3.
