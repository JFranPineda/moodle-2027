# Manual de usuario: clases en vivo con BigBlueButton (BBB) desde Moodle

Para profesores y alumnos de Richi Math. BigBlueButton es la sala de
videoconferencia integrada en Moodle: se entra desde el curso, sin cuentas
aparte ni enlaces externos, y las grabaciones quedan dentro del curso.
El servidor es **propio** (`clases.richiacademy.com`, BigBlueButton 3.0.37,
instalado el 2026-09-14 siguiendo [bbb-server-setup.md](../bbb-server-setup.md)),
así que **no hay límite de duración ni de minutos**.

### Cómo está configurado, y qué significa en clase

Cinco decisiones vienen ya tomadas **en el servidor**. No se cambian por
actividad, y explican cosas que si no parecen fallos:

| Ajuste | Qué verás en clase |
|---|---|
| Sin límite de duración | la sala no se corta a los 60 minutos |
| **Cámara de alumno bloqueada** | los alumnos **no tienen botón de cámara**. El profesor sí conserva la suya |
| Todos entran en silencio | nadie llega con el micro abierto |
| La grabación no arranca sola | el profesor pulsa **Iniciar grabación** cuando empieza |
| Tope de 20 personas por sala | se esperan 11 (profesor + 10); el resto es margen |

La cámara de los alumnos está bloqueada **a propósito**: es lo que permite que
cinco clases simultáneas quepan en el servidor contratado. Se puede desbloquear
a un alumno concreto dentro de una sala (A8), pero cada cámara encendida cuesta
capacidad — no lo conviertas en costumbre.

Roles en la sala:

| Rol | Quién | Qué puede hacer |
|---|---|---|
| **Moderador** | profesor (y quien Moodle designe) | todo: presentar, grabar, silenciar, encuestas, salas, expulsar, cerrar |
| **Presentador** | un moderador o alumno al que se le cede el rol | subir presentación, dibujar, compartir pantalla, encuestas, vídeo externo |
| **Espectador** | alumnos | audio, chat, levantar la mano, reacciones, notas compartidas. **Sin cámara**: está bloqueada en el servidor (el profesor puede levantarlo por alumno, A8) |

---

## Parte A — Profesor

### A1. Crear la sala en el curso

1. Curso → **Modo de edición** → sección «Clases en Vivo» → *Añadir una
   actividad o un recurso* → **BigBlueButton**.
2. Nombre: «Clase en vivo» (una sola sala por curso; el enlace no cambia).
3. Tipo de instancia: **Sala con grabaciones** (permite grabar y ver las
   grabaciones ahí mismo).
4. Ajustes útiles:
   - *Mensaje de bienvenida*: aparece en el chat al entrar.
   - *Esperar al moderador*: los alumnos no pueden entrar hasta que el profe
     esté dentro (recomendado).
   - *Silenciar a los usuarios al entrar*: **ya lo hace el servidor**, no hace
     falta tocarlo.
   - *Programación de sesiones*: fecha de apertura/cierre solo si la sala se
     usa en horas fijas; sin fechas, siempre disponible.
   - *Ajustes de grabación*: **«La sesión puede grabarse» ✔ es obligatorio** —
     sin esa casilla el profesor no ve el botón de grabar y la clase se pierde.
     «Grabar todo desde el inicio» déjalo apagado: el servidor está puesto para
     que el profesor decida cuándo empieza la grabación, y así no se graba el
     cuarto de hora previo.
   - *Participantes*: por defecto «Todos los usuarios matriculados» como
     espectadores y el rol Profesor como moderador. Aquí se puede hacer
     moderador a un alumno ayudante.
   - **Ajustes comunes del módulo → Modo de grupo: «No hay grupos»**. Es una
     sala por curso, no una por grupo. Si lo dejas en grupos y el curso no
     tiene ninguno definido, **los alumnos no pueden entrar** (ver abajo).
5. Guardar. El alumno verá la actividad y un botón **Unirse a la sesión**.

> **Comprueba siempre con una cuenta de alumno, nunca solo como administrador.**
> El administrador se salta la comprobación de grupos por capacidad
> (`moodle/category:manage` en `mod/bigbluebuttonbn/classes/instance.php`), así
> que una sala rota para toda la clase se le abre sin problema. Si no lo pruebas
> como alumno, te enteras en la primera clase.

### A2. Entrar y configurar el audio (todos)

1. Clic en **Unirse a la sesión** → se abre BBB en una pestaña nueva.
2. Pregunta *¿Cómo quieres unirte al audio?*: **Micrófono** (para hablar) o
   **Solo escuchar**. Con micrófono hace una **prueba de eco**: si te oyes,
   *Sí*; si no, *No* y elige otro micrófono en la lista.
3. El navegador pedirá permiso de micrófono/cámara: *Permitir*. Chrome,
   Edge, Firefox y Safari actuales funcionan; en móvil, el navegador (no hace
   falta app).

Barra inferior: **micrófono** (silenciar), **auriculares** (dejar el audio),
**cámara**, **compartir pantalla**, **+** (acciones del presentador).

### A3. Presentar

- **Subir una presentación** (+ → *Subir/Gestionar presentaciones*): PDF,
  PowerPoint, Word, imágenes. Se convierten a diapositivas. Consejo:
  **PDF siempre** (conversión más fiel; PowerPoint a veces pierde fuentes).
  Se pueden tener varias subidas y cambiar entre ellas; marcar *Descargable*
  si los alumnos deben poder bajarla.
- Navegar: flechas ◀ ▶ bajo la diapositiva; zoom con la lupa; **ajustar a
  ancho**; pantalla completa en el icono de la esquina.
- **Compartir pantalla**: toda la pantalla, una ventana o una pestaña (ideal
  para GeoGebra, Desmos, una calculadora, un PDF en otra app). Si compartes
  una pestaña de Chrome con vídeo, marca *Compartir audio de la pestaña*.
  **Solo desde PC o portátil.** Desde tablet o móvil sale el error
  **«Código 1137. El navegador no está soportado»**: ningún navegador móvil
  implementa la captura de pantalla (`getDisplayMedia`), ni Chrome en Android ni
  nada en iPad. No es un ajuste que se pueda activar. Desde el tablet se
  sustituye por la pizarra sobre el PDF (A4) o por *Compartir cámara como
  contenido*, aquí abajo.
- **Cámara como contenido** (+ → *Compartir cámara como contenido*): pone la
  cámara en el área grande. Útil para enfocar una pizarra física o un papel
  con la cámara del móvil/tablet.
- **Compartir vídeo externo** (+ → *Compartir un vídeo externo*): YouTube,
  Vimeo, etc.; se reproduce sincronizado para todos.
- **Ocultar la presentación** (icono en la esquina de la diapositiva) para
  que las cámaras ocupen todo cuando no hay pizarra.

### A4. Pizarra (el corazón de una clase de matemática)

> **Sobre una pantalla compartida NO se puede dibujar.** Las herramientas de
> pizarra existen solo sobre la **presentación subida**. Mientras compartes
> pantalla, el área de dibujo desaparece para todos. Si quieres que un alumno
> resuelva y la clase lo vea, deja de compartir pantalla y trabaja sobre un PDF
> (aunque sea de páginas en blanco).

Herramientas a la derecha de la diapositiva: **lápiz**, formas (línea,
rectángulo, elipse, triángulo, flecha), **texto**, seleccionar/mover,
borrar, deshacer, colores y grosor. Se dibuja **sobre la diapositiva**
actual (una diapositiva en blanco = pizarra limpia: sube un PDF con páginas
vacías o usa la presentación por defecto).

- **Pizarra multiusuario**: deja que los alumnos dibujen — para que uno
  resuelva delante de la clase. Dos alcances:
  - **Un alumno concreto**: clic en su nombre en la lista de participantes →
    *Dar acceso a la pizarra*.
  - **Toda la clase**: icono de **varios usuarios** en la barra de la pizarra.

  Cada trazo lleva el nombre de quien lo hizo, y queda en la grabación. Se
  retira por el mismo sitio. Esto **no** convierte al alumno en presentador: no
  puede subir material ni compartir pantalla, solo dibujar. Para eso hace falta
  *Hacer presentador* (A8), y presentador solo hay uno a la vez.
- **Cursor**: los alumnos ven el puntero del presentador en tiempo real.
- **Tablet gráfica** o iPad con lápiz: funciona como cualquier puntero;
  en iPad, el lápiz escribe directo sobre la pizarra desde Safari.
- **Descargar la presentación con anotaciones** (+ → *Descargar*): entrega a
  los alumnos el PDF con todo lo escrito en clase.
- Borrar todas las anotaciones: icono de papelera de la barra de pizarra.

### A5. Comunicación

- **Chat público**: todos. **Chat privado**: clic en un nombre → *Iniciar
  chat privado*. El moderador puede guardar/copiar/limpiar el chat público.
- **Notas compartidas**: documento de texto colaborativo (fórmulas, tareas,
  enlaces) que todos editan; se puede exportar.
- **Levantar la mano** y **reacciones** (👍 😀 ✋…): aparecen junto al nombre;
  el moderador ve la lista de manos levantadas y las baja.
- **Subtítulos / transcripción** (si el servidor lo activa): botón *CC*.

### A6. Encuestas (comprobar comprensión en 30 segundos)

+ → **Iniciar una encuesta**: Verdadero/Falso, A/B/C/D, Sí/No/Abstención,
personalizada (escribes las opciones) o **respuesta escrita** (los alumnos
teclean la respuesta: perfecto para «¿cuál es el resultado?»). Opciones:
anónima, permitir varias respuestas. Mientras corre, ves el recuento en vivo;
**Publicar** lo pega en la diapositiva para todos.
Truco: si la diapositiva ya contiene «A. … B. … C. …», BBB ofrece la
**encuesta rápida** con esas opciones detectadas.

### A7. Salas de grupo (breakout rooms)

Icono de engranaje de la lista de participantes → **Crear salas de grupo**:
número de salas, duración, reparto manual/aleatorio, y si los alumnos
pueden elegir sala. Cada sala tiene su pizarra y audio; el moderador puede
**entrar en cualquiera**, enviar un mensaje a todas y **ampliar el tiempo**.
Al terminar, las anotaciones de cada sala se pueden traer a la sala principal.

### A8. Gestión de la clase

Engranaje de la lista de participantes:
- **Silenciar a todos** / silenciar a todos menos al presentador.
- **Bloquear espectadores**: sin cámara, sin micrófono, sin chat privado,
  sin notas… La **cámara ya viene bloqueada** desde el servidor; aquí se
  bloquea lo demás (chat privado, notas) cuando hace falta.
  Para dejar que **un alumno concreto** encienda la cámara en esta sala —
  enseñar un cuaderno, una exposición —, clic en su nombre y levantar el
  bloqueo. Hazlo puntualmente y vuelve a bajarlo: cada cámara encendida come
  capacidad del servidor.
- **Guardar nombres de usuario** (lista de asistencia rápida).
- **Política de invitados / sala de espera**: aprobar a quien entra.
- **Temporizador / cronómetro** (icono de reloj): cuenta regresiva visible
  para ejercicios cronometrados.
- Clic en un nombre → *Hacer presentador*, *Promover a moderador*, *Quitar
  de la sesión*.
- **Fijar una cámara** para que se vea siempre grande (la del profe).
- **Diseño** (Ajustes → Diseño): enfoque en presentación, en vídeo, o
  personalizado; el moderador puede **imponer su diseño** a todos.

### A9. Grabar

Botón **Iniciar grabación** arriba (aparece si la actividad lo permite). Se
puede **pausar** y reanudar; el cronómetro rojo indica que graba.

**La grabación aparece a la mañana siguiente, no al terminar la clase.** No es
un fallo: convertir el vídeo consume mucho procesador, así que ese trabajo se
manda de madrugada (23:00–06:00) para no competir con las clases en vivo. Al
**cerrar la sesión** (menú ⋮ → *Finalizar reunión*) la grabación se pone en
cola y espera ahí.

Al día siguiente aparece **en la misma actividad de Moodle**, bajo
«Grabaciones»: con vista previa, botón de reproducir, y opción de **ocultar**,
**proteger**, **editar nombre y descripción** o **borrar**. Solo la ven los
matriculados en el curso.

Consejo: avisar a los alumnos de que se graba — aparece un aviso en la sala. Las
cámaras de los alumnos están bloqueadas, así que en la grabación solo saldrán el
profesor y el material.

### A10. Terminar

⋮ (arriba a la derecha) → **Finalizar reunión** expulsa a todos y dispara el
procesado de la grabación. **Salir** solo te saca a ti (la sala sigue si hay
otro moderador).

### A11. Asistencia e informes

- Durante la clase: *Guardar nombres de usuario* (A8).
- Después: en la actividad de Moodle, pestaña **Informes** del curso →
  *Registros* filtrando por la actividad muestra quién se unió y cuándo.
- **Panel de análisis** (icono de gráfico del moderador, durante la sesión):
  tiempo de cada alumno en la sala, respuestas a encuestas, manos
  levantadas; descargable en CSV al final.

---

## Parte B — Alumno (para pegar en Avisos)

1. Entra al curso → **Clase en vivo** → *Unirse a la sesión*.
2. Elige **Micrófono** (si vas a hablar) o **Solo escuchar**. Haz la prueba
   de eco y da permiso al navegador.
3. Mantén el micrófono **silenciado** salvo cuando hables. Para pedir la
   palabra: **levanta la mano** (icono ✋) o escribe en el chat.
4. **No verás botón de cámara, y es normal**: está desactivada para que la
   clase vaya fluida para todos. Si el profesor necesita que enseñes algo, te
   la habilita en ese momento.
5. Si no oyes: sal del audio (auriculares) y vuelve a entrar; prueba otro
   navegador (Chrome); en el móvil usa el navegador, no hace falta app.
6. Si el profe da acceso a la pizarra, dibuja con las herramientas de la
   derecha; en tablet, con el lápiz.
7. Las grabaciones quedan en la misma actividad para repasar. Aparecen **al
   día siguiente** de la clase, no al terminarla.

---

## Parte C — Buenas prácticas para clases de matemática

- Prepara un **PDF por clase**: enunciados en páginas propias y **páginas en
  blanco intercaladas** para resolver encima.
- **Tablet gráfica** (o iPad + lápiz desde Safari) para escribir fluido;
  el ratón no sirve para álgebra.
- **La combinación que mejor funciona**: PC para compartir pantalla (GeoGebra,
  Desmos) y tablet con lápiz para escribir en la pizarra. El profesor entra
  desde los dos a la misma sala. El tablet solo no comparte pantalla, y el PC
  solo no escribe bien.
- Cada 10–15 min una **encuesta de respuesta escrita** con el resultado de
  un ejercicio: ves al instante quién va perdido, sin exponer a nadie.
- **Salas de grupo** de 3–4 alumnos para resolver, luego un alumno
  presenta en la pizarra multiusuario.
- **Graba siempre**; la grabación con la presentación anotada descargable es
  el material de repaso. Recuerda que estará disponible al día siguiente:
  no la prometas «para esta tarde».
- Grupos ruidosos: entrar en silencio ya es el comportamiento del servidor;
  si hace falta, añade *Bloquear chat privado*.
- Recomendación de conexión: **cable o wifi 5 GHz para el profesor**, que es
  quien emite cámara y pantalla. Los alumnos solo reciben, así que aguantan
  conexiones bastante peores.

## Problemas frecuentes

| Síntoma | Causa típica | Solución |
|---|---|---|
| Nadie me oye / no oigo | permiso denegado, micro equivocado, red que bloquea UDP | Auriculares → volver a unirse al audio; revisar permisos del navegador; cambiar de red (datos móviles) para probar |
| **«Usted no tiene rol con permiso para unirse a esta sesión»** (alumno) + «La sala se configuró para usar grupos pero el curso no tiene grupos definidos» | la actividad está en modo grupos y el curso no tiene grupos | *Editar ajustes* de la actividad → **Ajustes comunes del módulo** → **Modo de grupo: No hay grupos**. Si el desplegable está gris, el curso lo fuerza: *Editar ajustes del curso* → **Grupos** → modo *No hay grupos* y *Forzar* en **No** |
| **«Código 1137. El navegador no está soportado»** al compartir pantalla | estás en tablet o móvil: ningún navegador móvil sabe capturar pantalla | no tiene arreglo. Comparte desde PC, o desde el tablet usa la pizarra sobre el PDF (A4) o *Compartir cámara como contenido* (A3) |
| **El alumno no ve el botón de cámara** | es a propósito: está bloqueada en el servidor | no es un fallo; si hace falta para algo puntual, el profesor se lo habilita (A8) |
| La cámara del **profesor** no enciende | otra app la usa, permiso denegado | cerrar Zoom/Meet; permitir en el candado de la barra de direcciones |
| La presentación no sube | formato raro, >200 páginas | exportar a PDF; dividir |
| Todo va a saltos | red del alumno | apagar cámaras; *Ajustes → Guardar datos* |
| La grabación no aparece al terminar la clase | es lo esperado: se procesa de madrugada | aparece a la mañana siguiente en la misma actividad |
| Tampoco aparece **al día siguiente** | ahí sí hay algo mal | avisar al administrador: se revisa en el servidor (§E de `bbb-server-setup.md`) |
| «La sala está llena» | el tope es de 20 por sala | contar quién hay dentro; si de verdad hacen falta más, lo sube el administrador |

Referencia oficial (inglés, con vídeos cortos): <https://bigbluebutton.org/teachers/tutorials/>.
