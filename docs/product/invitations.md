# Invitaciones: alta de alumnos por enlace personal (plugin `local_richimath`)

Preguntas de origen (2026-08-29): ¿se puede agregar alumnos **desde el mismo
salón** sin pasar por Administración? ¿Compartir un **enlace de matrícula al
curso** para un solo alumno? ¿Un **enlace privado al correo** para que el
alumno se registre y luego asignarle cursos?

Las tres son la misma mecánica con distinto destino, y Moodle de serie no la
trae (el «Matricular usuarios» solo busca cuentas ya creadas, y el
autorregistro por correo está abierto a cualquiera). Por eso existe el plugin
propio `local/richimath/`, dentro de la regla del repo (nada en el core).

## Qué hace

Una **invitación** = un enlace personal, de un solo uso, atado a UN correo:

- Quien lo abre y **no tiene cuenta** → formulario corto (nombre, apellido,
  contraseña; el correo viene fijo) → cuenta creada, sesión iniciada.
- Quien lo abre y **ya tiene cuenta** con ese correo → se le pide iniciar
  sesión y vuelve solo al enlace.
- Quien lo abre con **otra cuenta** abierta → aviso «esta invitación es para
  X» y botón de cerrar sesión. Nadie más puede usar el enlace.
- Si la invitación **apunta a un curso**, al entrar queda matriculado como
  estudiante (método «Matriculación manual» del curso, igual que si lo
  matriculara el profe a mano). Si **no apunta a curso**, solo entra a la
  plataforma y los cursos se le asignan después.
- El enlace **vence** (7 días por defecto; ajuste *Extensiones → Plugins
  locales → Herramientas Richi Math*). Un enlace usado o vencido muestra un
  aviso claro y el botón de iniciar sesión.

## Cómo se usa

**Desde el salón (tarea 3 y 4)**: curso → pestaña **Más → Invitar alumnos**.
**Solo el correo** → *Crear invitación* (decisión 2026-08-30: el admin no
rellena nada más; nombre y apellido los escribe el propio alumno al abrir el
enlace, y son obligatorios en ese formulario). Aparece el enlace con dos
botones: **Copiar enlace** y **Enviar por WhatsApp** (abre WhatsApp con el
mensaje ya escrito). Debajo, la lista de invitaciones del curso con estado
(Pendiente / Aceptada / Vencida), fecha de vencimiento, **quién la aceptó**
(enlace a su perfil, con el nombre que él mismo escribió) y borrar.

**A la plataforma, sin curso (tarea 5)**: *Administración del sitio →
Usuarios → Cuentas → Invitaciones* (también aparece buscando «Invitaciones»
en el buscador de administración). Misma pantalla; el alumno entra y luego
se le matricula donde toque.

Quién puede invitar: profesor con edición del curso, gestores y admin
(capacidad `local/richimath:invite`).

## Enlace del curso: UNO para todos (v2026083100, 2026-08-31)

Pedido de Richi: un solo enlace por curso para N invitados, en vez de un
enlace por alumno. Ya está:

- Arriba de la pantalla «Invitar alumnos» aparece **«Enlace del curso — uno
  para todos»** (azul marino, fijo, no caduca): se pega una sola vez en el
  grupo de WhatsApp de padres o se proyecta en clase. La página de admin sin
  curso tiene su equivalente «Enlace de la plataforma».
- **El filtro es la lista de correos**: quien abre el enlace escribe su
  correo; si está en la lista de invitados del curso, sigue el flujo normal
  (crear cuenta con ese correo fijo, o iniciar sesión) y queda matriculado;
  si no, «este correo no está en la lista de invitados». Cada correo
  conserva su vencimiento y su un-solo-uso.
- **Agregar un correo** (formulario de siempre, ahora «Invitar este correo»)
  lo mete en la lista **y** le envía por email ese mismo enlace, con su
  correo ya precargado (parámetro `email=` sobre el mismo token: sigue
  siendo un único enlace).
- Los botones Copiar/WhatsApp de cada fila también comparten el enlace del
  curso con el correo precargado. Los enlaces personales antiguos siguen
  funcionando.

Implementación: tabla `local_richimath_courselink` (courseid único, token),
clase `courselink`, formulario `identify_form` en `accept.php`. Verificado en
local: correo invitado → alta y matrícula; correo no invitado → aviso;
prefill por `email=`.

## La pantalla (v2026083001, 2026-08-30)

Plantilla propia `templates/invite.mustache` + sección D7 del SCSS del tema:
arriba la invitación recién creada en una caja destacada (enlace en un campo
de solo lectura, **Copiar enlace**, **Enviar por WhatsApp**, vencimiento),
luego el formulario y la lista «Invitaciones enviadas» (correo, estado con
fecha, aceptada por, acciones; en móvil cada fila es una tarjeta). Borrar pide
confirmación.

**Copiar enlace en `http://`**: `navigator.clipboard` solo existe en HTTPS o
localhost, por eso el botón no hacía nada en Contabo (IP sin TLS). Ahora usa
`document.execCommand('copy')` como respaldo, y si ni eso funcionara deja el
enlace seleccionado para Ctrl+C. Verificado en local con y sin la API.

## Correo saliente

La invitación se envía por correo al crearla (Gmail SMTP configurado en
local y en Contabo el 2026-08-30, ver [outgoing-mail-gmail.md](outgoing-mail-gmail.md)).
Si el envío fallara, la pantalla lo avisa y el enlace igual queda listo para
copiar o mandar por WhatsApp — que en la práctica es como Richi se comunica
con padres y alumnos.

## Opinión sobre la tarea 5 (enlace privado solo para registrarse)

Es exactamente la Opción «sin curso» de arriba, y conviene tenerla, pero
para el día a día la invitación **con curso** es mejor: un solo paso
(registro + matrícula) en vez de dos. La idea de Richi de «sin ese correo no
se puede matricular» se cumple en ambas: el formulario no deja cambiar el
correo, el enlace es de un uso y caduca.

No se adoptó el plugin de terceros `enrol_invitation`: exige tener abierto
el autorregistro por correo de Moodle para crear cuentas (justo lo que no
queremos) y añade un mantenimiento externo por algo que son ~400 líneas
propias.

## Despliegue en Contabo

El plugin viaja por git. En el servidor, `scripts/deploy-contabo.sh` ya corre
`upgrade.php`, que lo instala (tabla `mdl_local_richimath_invitation` +
capacidad). Nada que configurar; opcionalmente ajustar los días de
vencimiento y el correo saliente.

## Verificado en local (2026-08-29)

Creación desde el curso 35 como admin; enlace abierto sin sesión → cuenta
nueva creada y matriculada como estudiante en el curso; enlace para un correo
existente → login y matrícula; enlace con otra cuenta abierta → aviso; enlace
usado / inválido → aviso. Capturas en la sesión de trabajo (1440 y 390).

## Pendiente / ideas

- Reenviar por correo desde la lista (hoy: copiar enlace o WhatsApp).
- Invitar a varios correos de una vez (pegar una lista).
- Elegir rol distinto a estudiante (hoy usa el rol por defecto de la
  matriculación manual del curso).
