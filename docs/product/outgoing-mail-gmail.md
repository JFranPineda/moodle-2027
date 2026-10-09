# Correo saliente con Gmail (SMTP): arregla «cannotmailconfirm» y activa el envío automático

Pregunta de origen (2026-08-29): «Olvidé mi contraseña» falla con
`Error al enviar el correo electrónico de confirmación de cambio de contraseña`
(`Error code: cannotmailconfirm`, `login/lib.php` línea 173). ¿Se arregla
configurando correo saliente? ¿Es gratis? ¿Cómo con una cuenta @gmail.com?

## Por qué pasa

Moodle no tiene ningún servidor de correo configurado (*Administración del
sitio → Servidor → Correo saliente* está vacío), así que `email_to_user()`
cae al `mail()` de PHP, y en Contabo no hay Postfix/sendmail detrás. Todo
correo falla en silencio; «Olvidé mi contraseña» es el único sitio del core
que convierte ese fallo en un error visible. Es la misma causa por la que las
[invitaciones](invitations.md) avisan «no pudo enviar el correo».

Solo hace falta **correo saliente**. El «correo entrante» (IMAP, para que
Moodle procese respuestas a foros) no interviene ni se necesita.

## Mientras tanto: restablecer la contraseña a mano

Admin → *Usuarios → Cuentas → Examinar lista de usuarios* → ⚙ del alumno →
**Nueva contraseña** (+ marcar *Forzar cambio de contraseña*) → guardar, y
se la pasas por WhatsApp. No depende del correo.

## ¿Es gratis?

Sí. Una cuenta Gmail normal incluye SMTP sin coste, con un tope de ~500
destinatarios al día (Google Workspace: 2.000). Para la academia sobra.
Lo único que Google exige hoy es entrar con una **contraseña de aplicación**
(la opción «aplicaciones menos seguras» ya no existe y la contraseña normal
de la cuenta no sirve). Requiere tener activada la **verificación en 2 pasos**.

Recomendación: usar una cuenta dedicada del aula (p. ej.
`aula.richimath@gmail.com`) en vez de la personal — la contraseña de
aplicación da permiso para enviar en nombre de esa cuenta, y así el remitente
que ven padres y alumnos es el del aula. Hoy la dirección de no-responder de
Moodle ya es una @gmail.com (la del profe); vale también.

## Paso 1 — En Google (5 minutos)

1. <https://myaccount.google.com> → **Seguridad** → **Verificación en 2 pasos**
   → activarla (pide el móvil).
2. <https://myaccount.google.com/apppasswords> (o buscar «Contraseñas de
   aplicaciones» en la caja de búsqueda de la cuenta) → nombre `Moodle Richi
   Math` → **Crear**.
3. Copiar los 16 caracteres que muestra, **sin los espacios**. Solo se ven una
   vez; si se pierden, se crea otra.

## Paso 2 — En Moodle (Contabo)

*Administración del sitio → Servidor → Correo saliente*:

| Ajuste | Valor |
|---|---|
| Servidores SMTP (`smtphosts`) | `smtp.gmail.com:587` |
| Seguridad SMTP (`smtpsecure`) | `TLS` |
| Tipo de autenticación SMTP (`smtpauthtype`) | `LOGIN` |
| Nombre de usuario SMTP (`smtpuser`) | `tucuenta@gmail.com` |
| Contraseña SMTP (`smtppass`) | la contraseña de aplicación (16 caracteres) |
| Dirección de no-responder (`noreplyaddress`) | **la misma** `tucuenta@gmail.com` |

Guardar. El resto de campos se dejan como están. Alternativa equivalente:
`smtp.gmail.com:465` + `SSL`.

`noreplyaddress` tiene que ser la misma cuenta: Gmail reescribe el remitente
a la cuenta autenticada, y si Moodle pone otra, el correo sale «vía» o rebota.

Todo esto vive en la BD (no viaja por git). Por CLI, si se prefiere:

```bash
cd /var/www/html
sudo -u www-data php admin/cli/cfg.php --name=smtphosts --set=smtp.gmail.com:587
sudo -u www-data php admin/cli/cfg.php --name=smtpsecure --set=tls
sudo -u www-data php admin/cli/cfg.php --name=smtpauthtype --set=LOGIN
sudo -u www-data php admin/cli/cfg.php --name=smtpuser --set=tucuenta@gmail.com
sudo -u www-data php admin/cli/cfg.php --name=noreplyaddress --set=tucuenta@gmail.com
sudo -u www-data php admin/cli/cfg.php --name=smtppass --set=XXXXXXXXXXXXXXXX   # queda en el historial del shell: mejor por UI
sudo -u www-data php admin/cli/purge_caches.php
```

## Paso 3 — Probar

1. *Administración del sitio → Servidor → Correo saliente → Probar la
   configuración de correo saliente* (`/admin/testoutgoingmailconf.php`):
   poner un correo tuyo → **Enviar un mensaje de prueba**. Debe decir que se
   envió y llegar (mirar spam la primera vez).
2. Cerrar sesión → «Olvidé mi contraseña» → correo del alumno → llega el
   enlace.
3. Crear una invitación desde un curso: ahora dirá «creada y enviada».

Si la prueba falla:

- `SMTP Error: Could not authenticate` → contraseña de aplicación mal copiada
  (con espacios) o la verificación en 2 pasos no está activa.
- `Could not connect to SMTP host` → el VPS no llega al puerto: en Contabo,
  `nc -zv smtp.gmail.com 587` debe decir *succeeded*; si no, probar 465/SSL.
- Llega pero a spam: normal los primeros envíos desde una cuenta nueva; con el
  uso mejora. Un dominio propio con SPF/DKIM lo resuelve del todo (pendiente
  con el TLS, ver security-checklist.md).

## Cosas a saber

- Si se cambia la contraseña de la cuenta Google, Google **revoca** las
  contraseñas de aplicación: hay que crear otra y actualizar `smtppass`.
- La contraseña de aplicación equivale a poder enviar como esa cuenta: solo
  en Moodle, nunca en chats ni en el repo.
- 500 correos/día es por destinatarios; Moodle agrupa notificaciones, así que
  con decenas de alumnos no se acerca al tope.
- En el Moodle local (docker) se puede configurar igual para probar, pero no
  hace falta: las invitaciones locales se siguen compartiendo por enlace.

## Verificación completa (2026-08-30, tras configurar SMTP)

### A. Correo saliente

1. **Ajustes guardados** (Contabo, como www-data):
   ```bash
   cd /var/www/html
   for n in smtphosts smtpsecure smtpauthtype smtpuser noreplyaddress; do echo -n "$n = "; sudo -u www-data php admin/cli/cfg.php --name=$n; done
   ```
   Esperado: `smtp.gmail.com:587`, `tls`, `LOGIN`, la cuenta Gmail, y la misma cuenta en `noreplyaddress`.
2. **Prueba oficial**: *Administración del sitio → Servidor → Correo saliente →
   Probar la configuración de correo saliente* (`/admin/testoutgoingmailconf.php`)
   → tu correo → *Enviar un mensaje de prueba*. Verde + el correo en la bandeja
   (o spam la primera vez). Si sale rojo, la misma pantalla muestra el diálogo SMTP
   con el error exacto.
3. **Prueba desde CLI** (si la web no responde o para ver el detalle):
   ```bash
   sudo -u www-data php -r 'define("CLI_SCRIPT", true); require "/var/www/html/config.php"; $CFG->debugsmtp = true;
     $to = clone core_user::get_support_user(); $to->id = -99; $to->email = "TU@CORREO"; $to->emailstop = 0;
     var_dump(email_to_user($to, core_user::get_noreply_user(), "Prueba SMTP Moodle", "Hola desde Contabo"));'
   ```
   `bool(true)` + el correo recibido = OK. Con `debugsmtp` imprime la conversación SMTP.
4. **Flujos reales**: (a) cerrar sesión → «Olvidé mi contraseña» → llega el enlace
   (antes daba `cannotmailconfirm`); (b) curso → Más → Invitar alumnos → «Invitación
   creada y enviada por correo» y el correo llega al invitado; (c) *Usuarios →
   Agregar un usuario* con «Generar contraseña y notificar al usuario».
5. **Gmail → Enviados** de la cuenta configurada: todo lo que Moodle manda queda ahí.
   Es la confirmación definitiva de que salió por Gmail y no por `mail()`.

### B. Cron (imprescindible para avisos de foros, resúmenes y correo entrante)

Sin cron, el correo saliente inmediato funciona (recuperar contraseña,
invitaciones), pero las notificaciones de foro/mensajería y todo el correo
entrante se quedan en cola.

```bash
sudo crontab -u www-data -l                          # debe existir una línea con admin/cli/cron.php
sudo -u www-data php /var/www/html/admin/cli/cron.php | tail -5   # ejecución manual, sin errores
```
Si no hay línea: `sudo crontab -u www-data -e` y añadir
`* * * * * /usr/bin/php /var/www/html/admin/cli/cron.php >/dev/null 2>&1`.
*Administración del sitio → Notificaciones* deja de avisar «El cron no se ha ejecutado».

### C. Correo entrante (solo si se activó; no hace falta para nada de lo anterior)

Sirve para dos cosas: responder a un foro contestando el correo de aviso, y
«Correo a archivos privados». Ajustes en *Administración del sitio → Servidor →
Correo entrante* (`tool_messageinbound`):

| Ajuste | Valor con Gmail |
|---|---|
| Habilitar correo entrante (`messageinbound_enabled`) | Sí |
| Nombre del buzón (`messageinbound_mailbox`) | parte local de la cuenta (`aula.richimath`) |
| Dominio del correo (`messageinbound_domain`) | `gmail.com` |
| Servidor de correo entrante (`messageinbound_host`) | `imap.gmail.com:993` |
| Usar SSL (`messageinbound_hostssl`) | SSL |
| Usuario / contraseña | la cuenta y **la misma contraseña de aplicación** |

Gmail acepta las direcciones con `+` que usa Moodle (`aula.richimath+xxxx@gmail.com`),
así que no hay que crear nada más en Google.

Probar:

1. *Correo entrante → Manejadores de mensajes* (`/admin/tool/messageinbound/index.php`):
   habilitar **Correo a archivos privados** y **Respuestas por correo a los mensajes del foro**.
2. Forzar la recogida (cada minuto por cron; a mano):
   ```bash
   sudo -u www-data php /var/www/html/admin/cli/scheduled_task.php --execute='\tool_messageinbound\task\pickup_task'
   ```
   o *Servidor → Tareas → Tareas programadas → Procesar correo entrante → Ejecutar ahora*.
   Sin errores de conexión IMAP = credenciales y puerto bien.
3. Prueba funcional: *Archivos privados* (menú de usuario) → copiar la dirección
   «Enviar por correo a archivos privados» → desde el correo del propio usuario mandar un
   mensaje con un adjunto → tras la recogida aparece el archivo (la primera vez Moodle
   responde pidiendo confirmar el remitente: contestar ese correo y repetir). Segunda
   prueba: contestar a un aviso de foro desde el correo → la respuesta aparece en el foro.

## Probar el SMTP en LOCAL antes que en producción

El Moodle local es un espejo de Contabo **con los correos reales de los
alumnos**. Antes de meter credenciales SMTP en local hay que desviar TODO el
correo a una sola dirección tuya, o cualquier cron/foro/aviso local acabaría
en la bandeja de un alumno de verdad.

1. **Desvío obligatorio** — en `config.php` de la raíz del repo (el local, no
   versionado), antes del `require_once(__DIR__ . '/lib/setup.php')`:
   ```php
   // Solo en local: TODO el correo saliente va a esta dirección, sea quien sea el destinatario.
   $CFG->divertallemailsto = 'tu@correo.com';
   ```
   Moodle marca el asunto con `[DIVERTED destinatario@original]`, así se ve a
   quién habría ido. **Es lo esperado en local**: la prueba oficial y «Olvidé mi
   contraseña» dicen «enviado» y el correo aparece en la bandeja de desvío, no
   en la del destinatario. Para que TUS direcciones de prueba sí lo reciban
   directamente (y solo ellas), lista de expresiones regulares separadas por
   comas:
   ```php
   $CFG->divertallemailsexcept = 'jpinedas@uni\.pe, otro@correo\.com';
   ```
   Comprobar que no está `$CFG->noemailever` (lo bloquearía todo).
2. **Mismos ajustes que en Contabo** (desde `~/Documentos/github/moodle`):
   ```bash
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=smtphosts --set=smtp.gmail.com:587
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=smtpsecure --set=tls
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=smtpauthtype --set=LOGIN
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=smtpuser --set=tucuenta@gmail.com
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=noreplyaddress --set=tucuenta@gmail.com
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/cfg.php --name=smtppass --set=XXXXXXXXXXXXXXXX
   docker compose exec -T -u www-data web php /var/www/html/admin/cli/purge_caches.php
   ```
   (o por UI en <http://localhost:8080/admin/settings.php?section=outgoingmailconfig>
   como `qa.admin`; la contraseña por UI no queda en el historial del shell).
3. **El contenedor llega a Gmail**:
   ```bash
   docker compose exec -T web bash -c 'timeout 5 bash -c "</dev/tcp/smtp.gmail.com/587" && echo "587 abierto"'
   ```
4. **Prueba oficial**: <http://localhost:8080/admin/testoutgoingmailconf.php>
   → un correo cualquiera (llegará igualmente a `divertallemailsto`) → Enviar.
   En rojo muestra el diálogo SMTP; en verde, revisar la bandeja (y spam).
   El remitente que ve el destinatario es siempre la cuenta Gmail autenticada
   («Nombre (vía AulaVirtual) <learning.richiacademy@gmail.com>»), aunque el
   formulario de prueba diga otro «De:»: Gmail reescribe el remitente. Igual en
   producción.
5. **Prueba CLI con diálogo SMTP completo**:
   ```bash
   docker compose exec -T -u www-data web php -r 'define("CLI_SCRIPT", true); require "/var/www/html/config.php"; $CFG->debugsmtp = true;
     $to = clone core_user::get_support_user(); $to->id = -99; $to->email = "alumno.cualquiera@example.com"; $to->emailstop = 0;
     var_dump(email_to_user($to, core_user::get_noreply_user(), "Prueba SMTP local", "Hola desde docker"));'
   ```
   `bool(true)` y el correo en TU bandeja (desviado) = SMTP correcto.
6. **Flujos reales en local**: «Olvidé mi contraseña» con `estudiante.demo`
   (llega a ti, desviado) y una invitación desde el curso 35 → «creada y
   enviada». En *Enviados* del Gmail aparecen ambos.
7. **Al terminar**: quitar `smtppass` de local si no se va a seguir usando
   (`cfg.php --name=smtppass --set=`) — el `divertallemailsto` se queda para
   siempre en el config local. Luego repetir el bloque «Verificación completa»
   en Contabo.
