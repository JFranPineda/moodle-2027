# Sesiones abiertas: registro de visitantes y captación de leads

Un enlace por sesión de BigBlueButton que se manda por correo. Antes de entrar,
el visitante deja nombres, apellidos, universidad y correo, y queda guardado
para poder escribirle después.

## Qué trae Moodle y qué faltaba

| Pieza | Estado |
|---|---|
| Enlace de invitado por sesión | **nativo** (`guestlinkuid` + `guestpassword`) |
| Mandarlo por correo | **nativo** («Add guests» en la actividad) |
| Pedir nombres, apellidos, universidad | **construido aquí** — el nativo pide solo un nombre visible |
| Guardar quién entró | **construido aquí** — el nativo no guarda nada |

El nativo sirve para abrir la puerta; no sirve para saber quién entró. La pieza
propia es una **puerta delante de la puerta**: recoge los datos, los guarda y
entrega el visitante a la página nativa con la contraseña ya puesta, de modo que
al visitante nunca hay que darle una contraseña.

`mod/bigbluebuttonbn` **no se modifica**: el traspaso es un POST corriente a su
formulario.

## Lo que se añadió a `local_richimath` (v2026092700)

| Fichero | Qué hace |
|---|---|
| `db/install.xml` · `db/upgrade.php` | Tabla `local_richimath_lead` |
| `classes/lead.php` | Alta y actualización del lead, listados, contadores |
| `classes/form/guest_register_form.php` | El formulario del visitante |
| `session.php` | La puerta pública, `?uid=<guestlinkuid>` |
| `sessionlink.php` | El profesor copia el enlace y lo manda por correo |
| `leads.php` | Lista para el admin + CSV de los que dieron permiso |
| `lib.php` | «Invitar visitantes» dentro de la actividad BBB |
| `settings.php` | «Asistentes a sesiones abiertas» en Usuarios → Cuentas |

Decisiones tomadas por el camino:

- **Un lead por persona y sesión.** Quien asista a tres sesiones es una persona
  que vino tres veces (`joincount`), no tres leads. Índice único
  `(email, bbbinstanceid)`.
- **El consentimiento solo avanza.** Volver sin marcar la casilla no revoca en
  silencio lo que ya se concedió; para revocar hace falta pedirlo.
- **La casilla de marketing no condiciona la entrada** y viene desmarcada. La
  Ley 29733 pide consentimiento informado para usar datos con fines
  publicitarios, y cuesta una casilla.
- **El CSV exporta solo a quienes lo aceptaron.** La lista en pantalla es el
  registro de asistencia; el fichero es la lista de correo, y no son lo mismo.
- La tabla está declarada en el proveedor de privacidad aunque ningún `userid`
  apunte a ella: esa gente no tiene cuenta y esa tabla es el único sitio donde
  viven sus datos.

## El bloqueo de septiembre: dos causas, las dos cerradas

**Resuelto el 2026-10-03.** Lo que se documentó como «un invitado no llega a la
actividad» eran dos fallos distintos encadenados, y ninguno era de permisos.

### 1. El módulo estaba apagado en el sitio

`mdl_modules.visible = 0` para `bigbluebuttonbn`. Un módulo desactivado **no
entra en el `modinfo` de nadie**, ni siquiera del administrador, así que
`instance::get_cm()` moría con `invalidcoursemoduleid` para todo el mundo. Por
eso fallaba igual la página nativa: no era el rol invitado, era que esa
actividad no existía para ningún usuario.

Se comprobó abriendo `mod/bigbluebuttonbn/view.php?id=202` **como admin**: el
mismo error. Esa prueba es la que faltaba en septiembre — se dio por hecho que
el problema era del visitante sin medirlo contra un administrador.

Cómo verlo: **Administración del sitio → Extensiones → Módulos de actividad →
Gestionar actividades**; el ojo tachado es esto.

### 2. El formulario de traspaso usaba un identificador inventado

`moodleform` solo se da por enviado si llega su `_qf__<identificador>`, y ese
identificador lo calcula `get_form_identifier()` **a partir del nombre de la
clase**: `mod_bigbluebuttonbn_form_guest_login`, no `guest_login`. Nuestro
traspaso mandaba `_qf__guest_login`, así que la página nativa **nunca veía un
envío**: volvía a pintar el formulario vacío, sin un solo error, y el visitante
se topaba con una casilla de contraseña que nadie le había dado.

Es la peor clase de fallo: el POST sale con todo correcto, la contraseña es la
buena, y la pantalla no dice nada.

Corregido derivando el identificador de la clase de core en vez de escribirlo:

```php
$identifier = preg_replace('/[^a-z0-9_]/i', '_', \mod_bigbluebuttonbn\form\guest_login::class);
```

### Verificado de punta a punta

Como visitante anónimo, local, 2026-10-03:

```
session.php?uid=…        -> formulario de registro
POST con los 4 campos    -> lead guardado, pantalla de traspaso
boton «Entrar a la clase»-> guest.php acepta usuario y contraseña
                            y responde «La reunion aun no ha comenzado»
```

Ese último mensaje es de core y es el final correcto del camino: la sala no
estaba abierta. El traspaso funciona.

Lo demás, medido sobre la base de datos: 4 registros, el repetido sube a
`joincount` 3 sin duplicarse, el consentimiento sube de 0 a 1 y **no vuelve a
bajar** al reenviar sin marcar la casilla, y el CSV baja 2 filas de 4.

### Queda un borde áspero

Si la sala no está abierta, el visitante ve el aviso de core **más el
formulario de contraseña nativo**, que para él no tiene sentido: le dijimos que
no necesitaba contraseña. No rompe nada y es comportamiento de core. Si molesta,
la salida es consultar `get_meeting_info()` antes del traspaso y dar la noticia
en nuestra página.

## Ajustes de sitio que hacen falta (BD, no viajan por git)

1. **Extensiones → Módulos de actividad → Gestionar actividades**: que
   **BigBlueButton no esté con el ojo tachado**. Apagado, la actividad
   desaparece del `modinfo` de todos los usuarios y cualquier enlace a ella
   responde `invalidcoursemoduleid`, administrador incluido.
2. **Extensiones → Módulos → BigBlueButton** → *External guest access*
   encendido. Es `bigbluebuttonbn_guestaccess_enabled`, y vive en la config del
   **núcleo**, no en la del plugin:
   `set_config('bigbluebuttonbn_guestaccess_enabled', 1)`. Ponerlo como config
   de plugin (`set_config(..., 'bigbluebuttonbn')`) no hace nada y cuesta un
   rato descubrirlo.
3. En cada actividad: **Permitir acceso de invitados**.

## Para el profesor

1. Actividad BigBlueButton → *Permitir acceso de invitados*
2. Menú de la actividad → **Invitar visitantes a esta sesión**
3. Pegar los correos y enviar

**Manda el enlace de esa página, no el de los ajustes de la actividad.** Los dos
funcionan; solo uno registra quién entró.

## Para el administrador

**Administración del sitio → Usuarios → Cuentas → Asistentes a sesiones
abiertas**: la lista completa, con cuántas veces vino cada uno y si aceptó que
se le escriba, más el botón de descarga del CSV.
