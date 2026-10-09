# Privacidad entre alumnos

**Regla:** un alumno ve lo suyo y nada de sus compañeros: ni su correo, ni sus
notas, ni en qué cursos está, ni cuándo entró por última vez.

Origen: un alumno de tercero le contó a su profesor que podía ver «toda la
información de los demás». Era cierto, y era el comportamiento de serie de
Moodle.

## Qué veía un alumno antes (medido el 2026-10-04, local)

| Dónde | Qué veía de un compañero |
|---|---|
| Curso → **Participantes** | Nombre, rol, grupos y último acceso de todos los inscritos |
| **Perfil** del compañero | **Correo**, zona horaria, **lista de cursos**, primer y último acceso |
| Buscador de **mensajes** | A cualquier compañero de sus cursos, para escribirle |
| **Notas** del compañero | Nada — ya estaban protegidas |

## Qué cambió

Todo con los interruptores del propio Moodle, aplicado por el paso de
actualización `2026100400` de `local_richimath`
(`local_richimath\privacy_lockdown`). **Llega a producción al desplegar**; no
hay que tocar nada a mano.

1. **Rol Estudiante** (y cualquier rol de tipo estudiante): pierde
   - `moodle/course:viewparticipants` — la pestaña Participantes desaparece;
   - `moodle/user:viewdetails` — el perfil de un compañero dice «Los detalles de
     este usuario no están disponibles». Esto también saca a los compañeros del
     buscador de mensajes;
   - `moodle/user:readuserposts`, `moodle/user:readuserblogs` — la página que
     junta los mensajes de foro o el blog de un compañero;
   - `block/online_users:viewlist` — quién está conectado.
2. **Rol Usuario autenticado**: pierde `block/online_users:viewlist` y
   `moodle/badges:viewotherbadges`.
3. **Ocultar campos de usuario** (*Administración del sitio → Usuarios →
   Permisos → Políticas del usuario*): correo, descripción, ciudad, país, zona
   horaria, primer y último acceso, IP, **cursos**, grupos, suspendido. Solo los
   ve quien tiene `moodle/user:viewhiddendetails` — profesores y gestores.
4. **Correo oculto en todas las cuentas** (`maildisplay = 0`) y por defecto en
   las nuevas. Así el perfil de cada alumno dice «Oculto a todo el mundo…» en
   lugar de «Visible para otros participantes del curso», que ya no era verdad.

## Qué NO cambió

- **El alumno ve todo lo suyo**: perfil, correo, notas, cursos.
- **El profesor ve a sus alumnos como antes**: participantes, correos, perfiles,
  calificaciones.
- **El alumno puede ver el perfil de su profesor y escribirle**: los profesores
  son contactos del curso. Del profesor solo ve lo que el profesor publica (su
  WhatsApp, si lo activó); su correo y sus cursos también quedan ocultos.

## Lo que sigue a la vista, a propósito

Son cosas que se comparten **por ser actividades en grupo**, no fichas
personales. Se controlan en cada actividad:

- **Foros**: cada mensaje lleva el nombre de quien lo escribió.
- **Consulta (choice)**: si el profesor elige «Publicar resultados con nombres».
  Usar «Publicar resultados de forma anónima».
- **BigBlueButton**: en la sala se ven los nombres de quienes están conectados.
- **Bloque «Actividad reciente»**, si un curso lo tiene: nombra a quien publicó.

## Verificado (2026-10-04, local, como `estudiante.demo` en el curso 31)

| Prueba | Antes | Después |
|---|---|---|
| Participantes del curso | lista completa | **sin permiso**, y sin pestaña |
| Perfil del compañero (curso y sitio) | correo, cursos, accesos | **«no disponibles para usted»** |
| Mensajes de foro del compañero | página accesible | «no hay aportaciones que pueda ver» |
| Buscar al compañero en mensajes | aparecía | **no aparece** |
| Buscar al profesor en mensajes | aparecía | aparece |
| Perfil del profesor | (no medido) | solo su WhatsApp |
| Notas del compañero | sin permiso | sin permiso |
| Su propio perfil y sus notas | visibles | visibles |

Y el profesor (`richi85`) conserva en el curso: ver participantes, ver perfiles,
ver campos ocultos, ver identidad (correo) y ver todas las calificaciones.

## Verificado en Moodle 5.3 (2026-10-09, `github/moodle-2027`, local)

Mismas pruebas como `estudiante.demo` en el curso `BASE53`, con la compañera
`estudiante.dos` y el profesor `qa.admin`. En 5.3 el bloqueo llega por dos
caminos: en una instalación nueva lo aplica `db/install.php`; en la migración
viaja con la base de datos (paso de actualización 2026100400 de 4.3).

| # | Prueba | Resultado en 5.3 |
|---|---|---|
| 1 | Participantes del curso | **sin permiso**, y sin pestaña «Participantes» |
| 2 | Perfil de curso de la compañera | «No puede ver el perfil de este usuario» |
| 3 | Perfil de sitio de la compañera | «Los detalles de este usuario no están disponibles para usted» |
| 4 | Mensajes de foro de la compañera | «No hay aportaciones realizadas por este usuario que usted pueda ver» |
| 5 | Notas de la compañera | «No se pueden ver las calificaciones» |
| 6 | Buscar a la compañera en mensajes | **no aparece** |
| 6b | Buscar al profesor en mensajes | aparece |
| 7 | Perfil del profesor | solo su WhatsApp; sin correo ni cursos |
| 8 | Su propio perfil | su correo, marcado «Oculto a todo el mundo excepto…» |
| 9 | Sus propias notas | visibles |
| 10 | **Nuevo en 5.3: pestaña «Actividades»** (`/course/overview.php`) | solo **sus** datos: su calificación, el estado de **su** entrega y totales de mensajes del foro; ningún nombre de compañero |

Rol estudiante en 5.3: `viewparticipants`, `viewdetails`, `readuserposts`,
`readuserblogs` y `online_users:viewlist` **quitadas**; campos ocultos y
`defaultpreference_maildisplay = 0` aplicados. El profesor conserva en el
curso: ver participantes, ver perfiles, ver campos ocultos, ver identidad
(correo) y ver todas las calificaciones.

## Para deshacerlo

- Permisos: *Administración del sitio → Usuarios → Permisos → Definir roles →
  Estudiante → Editar*, y volver a marcar las capacidades de arriba.
- Campos: *Políticas del usuario → Ocultar campos de usuario*.
- El `maildisplay` anterior de cada cuenta no se guarda: casi todas estaban en
  el valor de serie (2), que se puede volver a poner por lotes si hiciera falta.
