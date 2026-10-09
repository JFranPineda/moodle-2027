# MEMORY.md — estado vivo del proyecto (actualizar al terminar cada tarea)

> Traspaso narrado de la sesión 2026-09-04 → 2026-09-12 (qué se construyó, por
> qué, las trampas pagadas y dónde me equivoqué): [SUMMARY.md](SUMMARY.md).

Última actualización: 2026-10-04 (privacidad entre alumnos por paso de upgrade 2026100400 — docs/product/student-privacy.md; menú ⋮ de actividades ya no parpadea; examen sin líneas entre opciones; antes: sistema solar de niveles e iconos en color; PROD CAÍDO hasta desplegar 325035b2 o posterior).

## Entornos

| | Local | Producción (Contabo) |
|---|---|---|
| URL | http://localhost:8080 | https://richiacademy.com (la IP 169.58.171.171 redirige) |
| Stack | docker: moodlehq/moodle-php-apache:8.1 + MariaDB 10.11 | Apache + PHP **8.2** (PPA ondrej; 8.3 bloquea el instalador de 4.3) + MySQL 8 (`dbtype=mysqli`) |
| Código | working tree del repo (montado) | `/var/www/html` = clon git, rama `main` |
| Datos | espejo del dump de Contabo + filedir sincronizado | reales — moodledata en `/var/www/moodledata` |
| Admin | `richi85` (el admin local `admin` ya no existe tras el espejo) | `richi85` |
| Cuentas de prueba | `estudiante.demo` (alumno, cursos 31 y 35; con foto de prueba desde 2026-08-29) y `qa.admin` (admin local para automatización de navegador). Contraseñas locales rotadas el 2026-08-29: viven en la memoria del agente, NO en el repo | crear bajo demanda (bloque php en historial/docs) |

Tema activo: `richimath` v2026082600 en AMBOS entornos (deploy 2026-08-26 verificado externamente: landing+animación+chips en producción; frontpage vacío, sidebar compact y Categoría 1 oculta aplicados en la BD de Contabo). Idioma: es (paquete instalado; el
idioma de PERFIL pisa al del sitio).

**Ajuste de sitio de la landing pública** (vive en BD, NO viaja por git —
aplicar en CADA entorno tras desplegar): `frontpage` = *vacío* (quita "Cursos
disponibles" de la portada anónima). `frontpageloggedin` sigue en `2` y no se
toca. `php admin/cli/cfg.php --name=frontpage --set=` + purga. Ya aplicado en
local; **pendiente en Contabo**.

**Ajustes de la lista de usuarios del admin** (2026-09-27, BD — NO viajan por
git, aplicar en CADA entorno): (1) `userfiltersdefault` = **`email`** decide qué
filtro sale sin pulsar «Mostrar más» en `/admin/user.php`
(`php admin/cli/cfg.php --name=userfiltersdefault --set=email`, o
Usuarios → Gestión de usuarios); por defecto es `realname`. (2) La cadena
`userlist` de **`core_admin`** pasa a «Lista de usuarios» por Personalización
del idioma (es). Ambos aplicados en LOCAL, **pendientes en Contabo**. Lo visual
es la sección H del SCSS del tema, que sí viaja. Detalle en
docs/theme-richimath.md.

**Ajuste del tema** (primer `settings.php`, T-04): `theme_richimath/sidebarstyle`
= `wide` (por defecto) | `compact`. Se alterna por UI (Apariencia → Temas →
Richimath) o por CLI:
`docker compose exec -u www-data web php admin/cli/cfg.php --component=theme_richimath --name=sidebarstyle --set=compact`
(purgar después). El ancho vive en la custom property `--richimath-sidebar-width`
(240px / 88px): TODOS los offsets derivados (margen de página, drawer izquierdo)
la leen, no dupliques la sección B2b.

## Plugin `local_richimath` (invitaciones) — desde 2026-08-29

Enlace personal de un solo uso atado a un correo, con o sin curso: crea la
cuenta (o pide login si el correo ya existe), matricula por «manual» del
curso y entra. Desde v2026083000 (2026-08-30) el que invita SOLO da el
correo (columnas firstname/lastname eliminadas en db/upgrade.php); nombre y
apellido los escribe el invitado en accept.php; la lista muestra «Aceptada
por» con enlace al perfil. v2026083100 (2026-08-31): **enlace único por
  curso** (tabla `local_richimath_courselink`, caja azul marino arriba de la
  vista; el visitante escribe su correo y solo pasa si está en la lista; los
  correos agregados reciben ese mismo enlace con `email=` precargado).
  v2026083001: vista rediseñada con plantilla
`templates/invite.mustache` + SCSS D7 (token `$richimath-gold` nuevo), y
**Copiar enlace con respaldo `execCommand('copy')`** — `navigator.clipboard`
no existe en `http://169.58.171.171` (solo HTTPS/localhost): trampa que solo
aparece en prod. En Mustache, un ejemplo JSON dentro de `{{! }}` con `}}`
seguidos cierra el comentario y se imprime: JSON multilínea siempre. Páginas: `local/richimath/invite.php?courseid=N` (curso →
Más → Invitar alumnos), `invite.php` sin curso (Admin → Usuarios → Cuentas →
Invitaciones), `accept.php?token=` (público). Tabla
`local_richimath_invitation`, capacidad `local/richimath:invite`
(editingteacher + manager), ajuste `expirydays` (7). Botones Copiar enlace y
WhatsApp para cuando no hay correo saliente. **Correo saliente**: Alex
configuró Gmail SMTP (`learning.richiacademy@gmail.com`, app password) en
LOCAL el 2026-08-30 y las invitaciones salen (`sent=1`); en Contabo dice
haberlo configurado también, sin verificar aún por el agente. El config.php
LOCAL lleva `$CFG->divertallemailsto = 'learning.richiacademy@gmail.com'`
(añadido 2026-08-30): el espejo tiene correos reales de alumnos y sin desvío
cualquier cron/foro local les escribiría. Guía y checklist:
docs/product/outgoing-mail-gmail.md. Detalle y
verificación en docs/product/invitations.md. Se instala solo con el
`upgrade.php` del deploy.

## Cómo se despliega

Local: commit → usuario hace `git push` → en el VPS: `bash /var/www/html/scripts/deploy-contabo.sh`
(pull como root —las credenciales GitHub son de root—, chown, upgrade, purge,
verificación contra wwwroot). Detalle en docs/theme-richimath.md y docs/deploy-workflow.md.

**Deploy 2026-08-30 hecho por Alex** (commits hasta `6041989c`): plugin
`local_richimath` v2026083000 instalado; banner creado como `cm 229`
(los labels 46/47 antiguos están en la papelera, `deletioninprogress=1`).
Hallazgos en ese deploy: `smtphosts` VACÍO en Contabo (el SMTP solo estaba en
local) y **sin crontab para www-data** — pasos de arreglo en
docs/product/outgoing-mail-gmail.md (bloques A y B).

**Paso manual tras un deploy que cambie el banner (vive en BD)**:
`cd /var/www/html && sudo -u www-data php scripts/set-frontpage-banner.php /var/www/html/assets/frontpage-banner.jpg --alt="¡Bienvenidos a tu aula virtual! Explora, aprende y domina las matemáticas con Richi Math"`
+ purge. Es idempotente: adopta el label que Richi ya tenga en la sección 1
(en el espejo había uno con `XXX.png`) y lo marca con idnumber
`richimath-frontpage-banner`. Guía: docs/product/frontpage-content.md.

## Trampas ya pagadas (no reincidir)

1. **Cachés**: purgar tras CADA cambio; CLI siempre `sudo -u www-data` — como root
   deja `moodledata/cache` propiedad de root y la web se congela en config vieja.
2. **`.gitignore` anclado**: `/config.php` (raíz). Un patrón sin anclar se tragó
   `theme/richimath/config.php` durante días (tema instalado pero cayendo a boost).
3. **`backdrop-filter` crea containing block**: los `position:fixed` dentro de
   `.main-inner` (drawer toggles, sticky footer) se posicionan contra la tarjeta,
   no contra la ventana. Ya compensado en C5; cuidado al añadir capas frost.
4. **Moodle redirige hosts ≠ wwwroot con 303** — toda verificación curl va contra
   el wwwroot real y con `-L`.
5. **BD y filedir viajan juntos** en los espejos (si no: warnings getimagesize).
6. **PHP 8.3 + Moodle 4.3 = RESTRICT duro** del instalador (no hay flag). Contabo
   quedó en 8.2. La salida real es subir a **Moodle 5.3 LTS** (ver el punto de
   migración abajo); subir PHP es parte de esa migración, no un extra.
7. Overrides de plantilla: copia mínima-diff del original + verificar el resolver
   (`templates/<componente>/…`); el de tema padre va en `templates/theme_boost/`.
8. El pipeline de assets: los SVG del cliente llevan el raster como MÁSCARA —
   render compuesto en navegador + knockout de blanco, no extraer el base64.
9. `block_myoverview` se repinta por JS (`replaceNodeContents` sobre
   `[data-region="courses-view"]`): cualquier markup propio DENTRO de esa región
   se borra en cada render/filtro. El clon de la barra de paginación (T-08) vive
   FUERA de ella y se re-sincroniza con un MutationObserver.
10. Esquina inferior derecha ocupada por Boost: `.btn-footer-popover` ("?") en
   md+ (bottom 2rem) y, por debajo de md, los toggles flotantes de drawer a ~89px
   del fondo. El botón "Consultas" se apila por encima (5rem md+, 7rem móvil).

## Pendientes

- **5.3 LTS descargada en `github/moodle-2027` (verificado 2026-10-08)**:
  `public/version.php` = `2026100500.00`, release **`5.3 (Build: 20261005)`**,
  branch 503, STABLE (5.3.0). Un solo commit propio («Created v 5.3 LTS»),
  `githash.php` = 4262229, sin tags. **NO SE PUEDE SUBIR DIRECTO DESDE 4.3**:
  `public/admin/environment.xml` declara `<MOODLE version="5.3" requires="4.4">`
  y la comprobación de entorno lo bloquea. Ruta: **4.3.12 → 4.5 LTS (último
  4.5.x) → 5.3**. Además 5.3 exige **PHP 8.3** (prod: 8.2) y **MySQL 8.4 /
  MariaDB 11.4** (prod: MySQL 8; local: MariaDB 10.11). 4.5 aguanta PHP 8.2 y
  MySQL 8.0, así que el orden es: 4.5 con el stack actual → subir PHP y BD →
  5.3. Desde 5.1 el código vive en `public/`: el DocumentRoot del vhost y las
  rutas del `.htaccess` (rutas limpias) tienen que apuntar ahí.

- **Privacidad entre alumnos (2026-10-04)**: `local_richimath\privacy_lockdown`
  (upgrade 2026100400) quita al rol estudiante viewparticipants, viewdetails,
  readuserposts/blogs y online_users; oculta campos personales (hiddenuserfields)
  y pone maildisplay=0 a TODAS las cuentas (el valor anterior no se guarda).
  Quitar `moodle/user:viewdetails` también saca a los compañeros del buscador de
  mensajes (filtra por perfil de curso visible); el profesor sigue visible por
  ser contacto del curso. Quedan a la vista por diseño: autores en foros,
  consulta con nombres, nombres en la sala BBB.
- **Hover con `transform` en filas con menú = menú ilegible (2026-10-04)**: el
  transform crea contexto de apilamiento y la fila siguiente tapa el ⋮; el
  hover se pierde y vuelve en bucle (parpadeo). No usar transform en hover de
  nada que contenga un dropdown.

- **Sistema solar de niveles (2026-10-03)**: `theme_richimath\levels` resuelve
  los 5 niveles por NÚMERO ID de categoría (`primaria`, `secundaria`,
  `preuniversitario`, `universitario`, `ib`) y si no, por nombre (el menos
  profundo; Pre se resuelve antes que Universitario porque «PRE UNIVERSITARIO»
  contiene «UNIVERSITARIO»). IB sin categoría → «Próximamente». Menú «Niveles»
  solo en lg+ (la barra pública nunca tuvo menú en móvil); en móvil mandan los
  planetas. TRAMPA: `matchMedia('(hover: hover)')` da false en headless y en
  portátiles táctiles → usar `pointerenter` con `pointerType === 'mouse'`.
  `prefers-reduced-motion` sin verificar en navegador.
- **Iconos en color (2026-10-03)**: `scripts/build-activity-icons.py` →
  `theme/richimath/pix_plugins/mod/*/monologo.svg`. RE-EJECUTAR tras la
  migración a 5.3 (los dibujos salen de `mod/`); avisa de módulos sin color.

- **DESPLEGAR YA (2026-10-03)**: producción corre `e568dac0`, cuyo
  `local_richimath_extend_settings_navigation()` declara `navigation_node` en el
  segundo argumento. Core lo llama con el CONTEXTO
  (`load_local_plugin_settings()`, `$function($this, $this->context)`), así que
  lanza TypeError en TODA página que construye la navegación de ajustes: el
  dashboard tras el login incluido, no solo los cursos. Arreglado en `325035b2`.
  Lección: cualquier callback de navegación se prueba abriendo /my/, un curso y
  una actividad ANTES de commitear.
- **Invitados BBB: resuelto (2026-10-03)**. El «bloqueo» de septiembre eran dos
  fallos: (1) `mdl_modules.visible=0` para bigbluebuttonbn en LOCAL — un módulo
  apagado no entra en el modinfo de NADIE, admin incluido, de ahí
  `invalidcoursemoduleid`; se encendió en local. (2) El traspaso mandaba
  `_qf__guest_login`; moodleform exige `_qf__mod_bigbluebuttonbn_form_guest_login`
  (derivado del nombre de clase) y sin él re-pinta el formulario vacío SIN error.
  Lección: ante un error «de permisos», reproducirlo primero como admin.
  Verificado anónimo hasta «La reunión aún no ha comenzado» (respuesta de core).
- **Select de campo personalizado de curso**: `get_options()` antepone `''`, así
  que el `intvalue` guardado es 1-based (0 = sin tocar). Un script de prueba que
  escriba 0,1,2 da una matriz falsa — me pasó.

- **BBB: versión de Ubuntu = versión de BBB (2026-09-12, corregido)**: cada
  release de BBB se publica para UNA distro. 3.0 → Ubuntu 22.04 (`-v jammy-300`,
  rama `v3.0.x-release`), estable. **4.0 → Ubuntu 24.04** (`-v noble-400`, rama
  `v4.0.x-release`), EN DESARROLLO: su propia doc se titula «unreleased
  documentation (in development)» y el repo sirve
  `bbb-html5 4.0.0~rc.3+20260911…-git.local-build-…`, o sea una RC reconstruida
  a diario. O sea que SÍ se puede instalar en 24.04 (me equivoqué al decir que
  no existía). Recomendación para esta academia: 22.04 + 3.0, porque las
  grabaciones son requisito (la cadena de grabación es lo más frágil al cambiar
  de mayor, y falla DESPUÉS de la clase) y porque `apt upgrade` sobre un repo de
  builds diarias mueve el suelo. Tercera vía: 22.04 ahora y migrar a 4.0 cuando
  sea estable con el runbook §12. Verificar el estado en 30 s con los dos `curl`
  al `Release.gpg` de cada repo (están en la guía, paso 2). Si se va a 4.0, hay
  que probar ANTES que el módulo BBB de Moodle 4.3 (escrito contra 2.x/3.x) se
  entiende con su API.
- **BBB: servidor contratado y aprovisionado (2026-09-14)**: Cloud VPS 6 activo,
  `clases.richiacademy.com` → **13.140.38.18** (IPv6 `2605:a144:2357:4889::1`;
  los registros A y AAAA ya resuelven y coinciden con `hostname -I`). Ubuntu
  22.04.5 LTS jammy, 6 vCPU, 11 Gi de RAM, **swap de 8 GB ya creado**: paso 3 de
  la guía cumplido salvo `ss -tlnp | grep -E ':80 |:443 '`, que falta correr
  antes del paso 4 (BBB toma ambos puertos en exclusiva y aborta si algo los
  ocupa). **REGIÓN AMÉRICA CONFIRMADA POR MEDICIÓN**, que era el punto
  irreversible: 106 ms desde Lima contra los **193 ms del VPS 4** (mismo
  proveedor, Europa) usado como control, y el rango IPv6 `2605:` es ARIN.
  DISCO: **200 GB, no los 400 recomendados** — decisión consciente de Richi
  mientras experimenta. Ampliar ANTES de que haya grabaciones reales: Contabo
  amplía en caliente pero `growpart` + `resize2fs` van a mano sobre el servidor,
  y con grabación activa 200 GB son 1–3 meses sin podar, por debajo del
  horizonte «ciclo en curso + anterior». Trámite previo (2026-09-12/13): el
  pedido 15399531 se hizo como cliente EMPRESARIAL con el mismo correo de la
  cuenta personal del VPS 4 y Contabo lo retuvo pidiendo elegir entre fusionar o
  usar otro correo; se resolvió fusionando en la cuenta personal. Dato lateral
  que salió al medir: **el Moodle de producción está a 193 ms de Lima** (VPS 4 en
  Europa) — los alumnos cargan el aula desde otro continente.
- **BBB instalado y verificado (2026-09-14, paso 4 cerrado)**: **BigBlueButton
  3.0.37** sobre el VPS 6, con `bbb-install.sh -v jammy-300 -w`, sin Greenlight
  ni demos de API. `bbb-conf --check` sale **con la sección «Potential problems»
  VACÍA** y los 18 servicios en `active`. Certificado Let's Encrypt emitido para
  `clases.richiacademy.com` (vence 2026-12-13, renovación automática); desde
  fuera: HTTP 200, TLS verificado, y `/bigbluebutton/api` devuelve
  `<returncode>SUCCESS</returncode>` — que es el endpoint que consumirá Moodle.
  El instalador montó además **TURN local** (coturn + haproxy en el 3478), que
  no estaba en la guía y ayuda a los alumnos tras redes restrictivas.
  DOS SUSTOS QUE NO ERAN NADA, para no repetir el diagnóstico: (1)
  `freeswitch.service could not be registered or started` a mitad del `apt` —
  arranca antes de que el instalador escriba la IP en `vars.xml`, y se recupera
  solo en el `bbb-conf --restart` final; tras el reinicio queda
  `active (running)`. (2) `grep: /etc/bigbluebutton/bbb-web.properties: No such
  file or directory` durante `bbb-html5` — orden de paquetes, `bbb-config` crea
  el fichero después. AVISO REAL: el `apt upgrade` del paso 3 trae kernel nuevo
  (`-190` → `-191`) y `needrestart` deja `dbus`/`systemd-logind` diferidos; el
  reinicio del paso 3 no es opcional y además es la vía limpia para que
  FreeSWITCH arranque con la config ya correcta.
- **BBB dimensionado (2026-09-12)**: objetivo fijado por Richi = **5 clases en
  paralelo de 1 profesor + 10 alumnos con cámaras de alumno APAGADAS** (55
  personas). VPS: **Contabo Cloud VPS 8** (8 vCPU / 24 GB / 300 GB, ~14 €/mes) —
  es el plan más barato que llega al mínimo DOCUMENTADO de BBB 3.0 (8 núcleos,
  16 GB con swap, Ubuntu 22.04). Los VPS 4 y 6 que recomendaba el doc de agosto
  quedan por debajo (RAM), y esa recomendación ya está corregida. El pico real
  no es el audio (<10 Mbit/s los 55) sino la PANTALLA COMPARTIDA: 5 emisores +
  50 receptores ≈ 80–90 Mbit/s de salida, dentro del puerto de Contabo
  (200 Mbit/s–1 Gbit/s). La clave del dimensionamiento es
  `lockSettingsDisableCam=true` en `/etc/bigbluebutton/bbb-web.properties` (los
  bloqueos de BBB aplican SOLO a espectadores: el profesor conserva cámara) +
  `meetingCameraCap=2`; encender 50 cámaras tira el plan abajo. Grabación
  apagada por defecto porque procesarla es lo único que compite en CPU con las
  clases en vivo. **Richi eligió el Cloud VPS 6** (6 vCPU / 12 GB / 200 GB,
  puerto 300 Mbit/s, ~9 $/mes) por precio: está POR DEBAJO del mínimo (la RAM es
  el riesgo), y la guía lo acepta con cuatro condiciones no negociables
  (cámaras bloqueadas, swap de 8 GB, grabación apagada, ensayo de pico medido) y
  un criterio de subida decidido de antemano: CPU >85 % sostenida, swap en uso
  durante clase, o salida >250 Mbit/s → subir a VPS 8 desde el panel (sin
  reinstalar). PREGUNTA RECURRENTE resuelta en §0 bis: instalar «desde el repo
  de GitHub» NO es una alternativa a `bbb-install.sh` — el repo es el código
  fuente y la guía de desarrollo de BBB exige un servidor ya instalado para
  reemplazar componentes. Para personalizar: (1) configuración
  `bbb-web.properties` / `bbb-html5.yml`, (2) **SDK oficial de plugins**
  (`bigbluebutton-html-plugin-sdk`, un JS que carga el cliente, sin bifurcar),
  (3) bifurcar y compilar un componente solo como último recurso y NUNCA en el
  servidor de producción. BBB es LGPL: autoalojarlo no cuesta licencia y NO tiene
  tope de alumnos/clases/minutos — los 60 min y 25 usuarios son del servidor de
  DEMO de Blindside al que Moodle apunta de fábrica. **GRABACIONES = REQUISITO**
  (2026-09-12): capturar es barato, PROCESAR es lo caro y se manda de noche con
  parando de día
  `bbb-rap-resque-worker` con dos timers propios (**corregido 2026-09-14**: el
  `bbb-record-core.timer` que decía la guía es de BBB 2.x y NO existe en 3.0) — consecuencia: la grabación aparece a la
  mañana siguiente. Con grabación obligatoria el cuello de botella del VPS 6 deja
  de ser la CPU y pasa a ser el DISCO: 200 GB para ~150 h/mes = 45–150 GB/mes
  según cuánta pantalla se comparta, o sea 1–3 meses sin poda. Los datos crudos
  se borran solos a los 14 días (`/etc/cron.daily/bigbluebutton`). Los dos VPS
  son SEPARADOS por requisito (BBB toma 80/443 y UDP 16384-32768): el VPS 4 de
  Moodle solo abre la sala y enlaza; el vídeo Y la reproducción de grabaciones
  van por el VPS 6. ALMACENAMIENTO (§6 ter): una grabación publicada NO es un
  fichero sino un directorio de cientos de piezas que nginx sirve del disco
  local, así que **Object Storage (S3, ~10 €/TB, sin coste de salida) NO sirve
  como directorio de reproducción** (montaje FUSE = decenas de ms por fichero,
  punto de fallo único, y la tubería de procesado escribe miles de veces);
  sirve como ARCHIVO FRÍO. Lo que el alumno debe poder ver tiene que vivir en
  SSD local → ampliar disco del VPS 6 (el configurador lo ofrece) o subir de
  plan. Regla de producto que se propuso a Richi: definir un HORIZONTE («ciclo
  en curso y el anterior»), dimensionar el SSD para eso y archivar lo anterior
  con rclone + `bbb-record --delete` (deja de verse en Moodle: es copia
  institucional, no servicio al alumno). Horizonte elegido por Richi: **ciclo en
  curso + anterior**; con 400 GB eso cabe si un ciclo es trimestral (150 h/mes →
  ~8 meses a 300 MB/h, ~2,5 meses a 1 GB/h). DISCO: contratar **400 GB desde el
  día uno** (+3,60 $/mes); ampliar después se puede (*Extend SSD Storage* en el
  panel, casi sin corte) pero exige `growpart` + `resize2fs` a mano sobre un
  servidor en producción, y Contabo avisa del riesgo de tocar particiones.
  Cambiar a un plan NVMe NO es redimensionar: reinstala. ⚠️ REGIÓN: el
  configurador marcaba Unión Europea (183 ms desde Perú); hay que elegir
  **América (EE. UU. Este/Central)**, es lo único que no se arregla después sin
  reinstalar. DOCKER: descartado — BBB solo soporta contenedores para
  DESARROLLO (`docker-dev`); necesita systemd, el rango UDP entero y red del
  host. La portabilidad se resuelve con el runbook de migración (§12): reinstalar
  con el script + copiar 4 ficheros de config y `recording/published|status` +
  mover el DNS. Guía paso a paso: docs/bbb-server-setup.md.
- **Plan de tickets 2026-08-24**: 9 de 11 tickets ejecutados (T-01/02/03/04/05/07/08/09/10).
  Bloqueados esperando a Richi: T-06 (plataforma de videollamada — costeado el
  2026-08-30 en docs/product/live-classes-bbb.md: BBB propio en Contabo Cloud
  Cloud VPS 4/6 (gama 2026) ≈ 5,50–7,50 €/mes, hostname `clases.richiacademy.com`; 2026-08-31
  manuales listos: docs/bbb-server-setup.md (instalación) y
  docs/product/bbb-user-manual.md (uso en clase)), T-11
  (pasarela de pago + TLS, en curso con richiacademy.com). Preguntas abiertas al final de
  [docs/plans/tickets-20260824-plan.md](docs/plans/tickets-20260824-plan.md).
  Nuevo: el tema ya tiene `settings.php` (ajuste `sidebarstyle` wide|compact).
  T-03 (chips de acceso rápido, 2026-08-26): tira de pills arriba del dashboard
  (`/my/` SOLO — OJO: `/my/` y `/my/courses.php` comparten `body#page-my-index`,
  el gate es `pagelayout === 'mydashboard'`). Cursos matriculados por último
  acceso (`enrol_get_my_courses`, 1 query; tope 12 + pill "Ver todos mis
  cursos"); fila de categorías top-level solo staff (gate `show_catalog()`,
  se saltan las vacías → sin "Categoría 1"). Renderer `dashboard_chips()`,
  markup en drawers.mustache, estilos en sección D5 de post.scss. Falta
  captura de navegador (verificado por curl 1440-equivalente, no visual) y
  párrafo en docs/theme-richimath.md. Review 2026-08-26: los anchors de chip
  (cursos y categorías) llevan `title="{{{name}}}"` — el recorte CSS a 22ch
  dejaba "NIVELACIÓN DE MATEMÁTICA" sin fallback de hover; triple-stache
  porque format_string ya escapa (con `{{name}}` se doble-escaparía).
- **Seguridad** (docs/security-checklist.md): **TLS HECHO 2026-08-30/31** —
  dominio `richiacademy.com` (Porkbun) + certbot; verificado desde fuera:
  `https://richiacademy.com/` → 200 y `http://169.58.171.171/` → 303 a https.
  wwwroot de prod = `https://richiacademy.com` (config.php). Guía:
  [docs/domain-and-tls.md](docs/domain-and-tls.md). T-11 pagos desbloqueado;
  pendiente rotar contraseña BD,
  rotar contraseña BD (sigue `debian-sys-maint` + expuesta en historial git),
  Moodle 4.3 fuera de soporte → migrar a **5.3 LTS**, ver abajo.
- **Migración de Moodle: objetivo 5.3 LTS, no 4.5 (fechas verificadas
  2026-09-14 en moodledev.io/general/releases)**: 4.3 dejó de recibir parches de
  seguridad el **21 abr 2025** — el sitio lleva 17 meses sin ninguno, con datos
  reales de alumnos. **5.3 sale el 5 oct 2026 y ES LTS** (la siguiente LTS tras
  4.5); 5.2 vence seguridad el 4 oct 2027, igual que 4.5 LTS, así que subir a
  5.2 ahora sería hacer la migración dos veces. DECISIÓN: esperar a 5.3, dejar
  pasar 2-3 semanas tras el lanzamiento (un `.0` recién salido trae fallos) y
  usar el tiempo para ensayar EN LOCAL contra 5.2, que es casi idéntica.
  LO QUE SE ROMPE, inventariado: **5 plantillas de core copiadas de la 4.3**
  (`core/loginform`, `theme_boost/drawers`, `theme_boost/primary-drawer-mobile`,
  `core_calendar/day_detailed`, `core_calendar/calendar_month`) — el peligro no
  es que revienten, es que **fallan en SILENCIO**: el tema sigue sirviendo la
  copia vieja, sin error, y lo nuevo de core no aparece. Por cada una: comparar
  con la plantilla de la versión nueva y rehacer el diff mínimo SOBRE LA BASE
  NUEVA, nunca al revés. Además 3 renderers que pisan core (`core_renderer`,
  `core/course_renderer`), los 4 temas hijos que dependen de los tokens de
  `richimath`, el layout del sitio institucional, las rutas limpias y
  `local_richimath` (`requires = 2023100900`). ORDEN: espejo local → arreglar →
  snapshot del VPS 4 → producción. Nunca producción primero.
- **Sitio registrado en moodle.org (2026-09-14)**: de ahí vienen los correos de
  «hay una nueva versión disponible». El registro envía solo CONTADORES (cursos,
  usuarios, matrículas, posts) más versión, URL y los datos de contacto que se
  escriben — ningún dato de alumno (`lib/classes/hub/registration.php:173-195`).
  El banner «Su sitio aún no está registrado» no tenía forma soportada de
  ocultarse: `admin/renderer.php` solo mira `!is_registered() && site_is_public()`.
- Minors documentados del tema: paridad drawer (dedupe), detalles modo edición
  (checkboxes bulk, botón insertar), contraste borde CTA 3.13:1.
- La cuenta `estudiante.demo` local existe con contraseña conocida del historial:
  eliminar o rotar al cerrar el QA. La contraseña de `richi85` apareció en chats:
  recomendada rotación.
- "Categoría 1" vacía en producción: borrar por UI cuando Richi confirme (ya no
  se cuela en la landing pública — se saltan las categorías sin cursos visibles).
- Landing pública: el sidebar y el drawer de bloques (bloque Navegación) siguen
  pintándose para el visitante anónimo, con enlaces que solo llevan al login.
  Decidir con Richi si se recortan.

- **Lote 2026-08-29 (5 tareas de Richi), todo verificado en local 1440/390,
  admin y alumno, pendiente de push + deploy**: (1) portada con sesión →
  banner `assets/frontpage-banner.jpg` (fuente `.html`) instalado con
  `scripts/set-frontpage-banner.php` + SCSS D6 (banner limpio, C9 extendido a
  `body#page-site-index` para las tarjetas de categorías; sin bump de
  version.php, solo SCSS); (2) foto de usuario = Opción C nativa, verificada
  (docs/product/default-avatar.md); (3-5) plugin `local_richimath` de
  invitaciones (arriba). Falta en Contabo: correr el script del banner,
  configurar correo saliente (guía Gmail + contraseña de aplicación en
  docs/product/outgoing-mail-gmail.md — también arregla el `cannotmailconfirm`
  de «Olvidé mi contraseña», reportado 2026-08-29), y borrar «Categoría 1»
  (aparece atenuada al admin como tarjeta vacía en la portada).
- Descartado por decisión de Richi 2026-08-29: avatar Richi Math por defecto
  (opción A) y Gravatar; chips del dashboard en la portada (opción 3) no se
  hicieron — las tarjetas de categorías cubren el hueco.

- **Rediseño 2026-09-03 (sistema «Primary Moodle Odyssey»)**: el tema deja el
  lenguaje Blackboard y adopta el sistema de `docs/design/`. Paleta azul
  (#004ccd/#0f62fe) + verde + coral sobre lienzo #f9f9ff, Quicksand+Nunito
  Sans, radios 12/16px, botones con sombra sólida inferior. Implementación en
  tres capas: `scss/pre.scss` (variables Bootstrap), `style/fonts.css`
  (webfonts — el `@import` NO puede ir en SCSS: scssphp lo resuelve como ruta
  de fichero y aborta la compilación entera, dejando la web sin CSS), y
  sección E de `post.scss`. Los tokens viejos (`$richimath-navy`, `-purple`,
  veils, `-serif`) se **remapearon** a los roles nuevos en vez de duplicar
  overrides. Ojo: las reglas de la sección B2b viven dentro de
  `body.uses-drawers` + media query lg, así que un override del sidebar
  necesita esa misma especificidad. Título de pestaña: `page_title()` cambia
  el nombre corto por el string `brandname` = «Richi Academy» (tema, viaja por
  git; el nombre corto del sitio sigue alimentando la marca del navbar).
  Detalle en docs/theme-richimath.md.
- **Cobertura 2026-09-03 (segunda pasada)**: el rediseño se extendió a TODAS
  las vistas. La barra superior, el rail izquierdo y el drawer móvil se
  reescribieron claros EN ORIGEN (B1/B2b/B4) y se borraron los overrides
  E3/E4. Trampa: al quitar un override hay que comprobar qué regla vuelve a
  ganar — el fondo del rail volvió a navy con texto oscuro encima (ilegible)
  porque solo se habían migrado los colores de los enlaces. Auditoría con
  sonda de contraste WCAG en 12 vistas; corregidos el bloque de usuario del
  rail, la etiqueta «Modo de edición», el índice de curso y el guionado de
  las etiquetas del rail compacto. Un fondo `rgba(0,0,0,0.03)` da falso
  positivo en ese tipo de sonda.

- **Categorías ocultas (2026-09-03)**: «Categoría 1» ya estaba `visible=0`;
  se veía porque el admin tiene `moodle/category:viewhiddencategories`. Ajuste
  nuevo `local_richimath/hidehiddencategories` (por defecto ON) + override
  `theme_richimath\output\core\course_renderer::coursecat_category()` que
  devuelve '' para las ocultas. TRAMPA de nombres: para `get_renderer('core',
  'course')` la clase que busca la factory es
  `theme_<name>\output\core\course_renderer` (carpeta `classes/output/core/`),
  NO el plano `core_course_renderer`. La visibilidad se sigue configurando con
  el ojo de Gestionar cursos y categorías; Gestión sigue mostrándolas.
- **Multi-tema (4 diseños)**: plan escrito en
  [docs/plans/multi-theme-plan.md](docs/plans/multi-theme-plan.md), SIN
  implementar. Recomendación: tema base + 4 hijos, `allowcategorythemes` para
  dentro del curso y `allowcohortthemes` (la cohorte = el «plan» del alumno)
  para fuera. Orden real de Moodle: curso → categoría → sesión → usuario →
  cohorte → sitio, y la rama de categoría solo dispara si hay curso.

- **Planes y apariencias (2026-09-04)**: cuatro temas hijos nuevos
  (`rmprimaria`, `rmsecundaria`, `rmpreu`, `rmuniversidad`), uno por diseño de
  `docs/design/`; `theme_richimath` es el base y **todos sus tokens llevan
  `!default`**, así que un hijo solo aporta `scss/palette.scss` +
  `style/fonts.css`. Tablas `local_richimath_plan` (5 sembrados: Primaria por
  defecto, Secundaria, Pre Uni, Universitaria, Admin) y
  `local_richimath_userplan`. UI en Administración → Planes.
  El tema se aplica escribiendo `user.theme` y el upgrade enciende
  `allowuserthemes`. Trampas: (1) los literales sin tokenizar del SCSS no
  siguen al hijo — el cuerpo seguía en Nunito Sans hasta crear `$od-body`;
  (2) las cadenas de idioma con apóstrofe (`level's`) rompen el fichero PHP.
  Detalle en docs/product/plans-and-appearances.md.
- **Rail Blackboard en Universidad (2026-09-04, T8)**: `rmuniversidad` es el
  único hijo con `scss/post.scss` propio — su `lib.php` lo concatena DESPUÉS
  de lo que devuelve el base, que no se toca. Rail `#0f2042`, tinta blanca
  85 %, acento `#4069f2` (izquierda en rail ancho, abajo en compacto), drawer
  móvil igual; el espacio de trabajo se queda claro. Los planes Universitaria
  y Admin apuntan a la apariencia `university`. Las reglas necesitan
  `body.uses-drawers` + media query `lg` para ganar al padre.
- **Cada plan con la apariencia de su nivel (2026-09-04, `local_richimath`
  v2026090403)**: los 5 planes se sembraron TODOS con `elementary`, así que
  Secundaria y Pre Uni se veían iguales que Primaria (misma paleta, mismas
  fuentes, mismo logo) y `rmsecundaria`/`rmpreu` no los usaba nadie. Era el
  síntoma que reportó el usuario al ver que solo cambiaba Universidad. El
  `seed()` de `plan_service` ya lleva la apariencia por nivel y el paso de
  upgrade reapunta los planes sembrados **solo si siguen en `elementary`** (no
  pisa lo que el administrador haya elegido). RECORDAR al probar: cambiar la
  apariencia de un plan NO repinta a nadie — llega en el siguiente login; y
  cambiar `user.theme` en BD tampoco afecta a la sesión abierta.
- **Marca v3 y una regresión del login (2026-09-15)**: `docs/design/logos/v3/`
  (sin trackear) trae `16.svg` = monograma DORADO sin texto → `pix/loginlogo.png`
  (la tarjeta de bienvenida del login), y `17.svg` = cromado CON «RICHI MATH» →
  `pix/examground.png`, la marca de agua del examen (se borró el `examground.jpg`
  viejo para que no haya dos ficheros resolviendo al mismo nombre). Los SVG son
  600×600 con el arte incrustado y un manifiesto C2PA de cientos de KB;
  rasterizarlos con `scripts/build-theme-logos.py` deja solo el dibujo. Como es
  un LOGOTIPO y no una textura, el `background-size: cover` de D8 lo ampliaba
  hasta llenar la caja: ahora va `auto 70%` pinchado abajo-derecha, y el velo de
  la sección F bajó de 0.9 a 0.88 porque la marca cromada es oscura y se borraba.
  TRAMPA GRANDE descubierta aquí: **`core_renderer::render_login()` EXISTE en el
  core** y añade `sitename`, `logourl` y `errorformatted` al contexto antes de
  pintar; mi override (2026-09-11) los tiró y la tarjeta del login se quedó sin
  el nombre del sitio durante cuatro días. Al sobrescribir cualquier `render_*`
  de Moodle: mirar primero si el padre hace algo más que llamar a la plantilla.
- **Portada: cifras reales y banner fuera (2026-09-13)**: las cifras del héroe
  las fijó Richi — +100 alumnos, +90 % de ingreso, 5+ catedráticos, 24/7 — en las
  cadenas `sitestat*value` (es + en). Y el contenido de portada de Moodle (el
  label con `frontpage-cover.jpg`) YA NO SE PINTA en el sitio institucional:
  repetía la marca del héroe. OJO: el marcador `{{{output.main_content}}}` tiene
  que seguir en la plantilla o Moodle se niega a renderizar el layout; lo que se
  oculta es la caja (`body.richimath-site .rm-site-content{display:none}`). Para
  quitarlo también de la portada CON sesión hay que borrar el label desde la
  portada en modo edición (vive en BD, no viaja por git). El logo de la esquina
  es `pix/weblogo.png`, generado por `scripts/build-theme-logos.py` desde
  `docs/design/logos/web_logo/` (trampa: el fuente se llama `code.html` y un
  `<img>` rechaza `text/html` — el script lo copia a `assets/logo-web.svg`
  primero).
- **Sitio público y URL limpias (2026-09-10)**: la RAÍZ pasa a ser el sitio
  institucional (obsidiana + oro + un neón por división, docs/design/website) y
  el catálogo que estaba ahí se mudó ENTERO a `/students`. El sitio es un
  **layout de tema** (`theme/richimath/layout/site.php` + `templates/local/
  site.mustache` + sección G del SCSS) registrado como layout `frontpage` en el
  config del tema; los layouts heredan CLAVE A CLAVE, así que el resto sigue con
  Boost. Con sesión abierta el layout delega en el `drawers.php` del padre: el
  aula no cambia. Divisiones = categorías de primer nivel reales; consola =
  formulario de login real (mismo `logintoken`); titulares y CIFRAS = cadenas de
  idioma (`site*`, `division*`) — las cifras son declaraciones de la academia,
  no datos medidos. TRAMPAS: (1) todo layout debe emitir `output.main_content`
  o Moodle lanza «does not contain the main content placeholder»; (2) usar
  `{{{ output.doctype }}}`, no `<!DOCTYPE>` literal, o sale aviso de debug;
  (3) `output` es una CLAVE del contexto, no magia: `render_from_template($t, [])`
  deja todos los `{{{output.*}}}` vacíos; (4) `$richimath-skin` ahora excluye
  `body.richimath-site` para que lo oscuro no se filtre al aula; (5) las reglas
  del catálogo colgaban de `pagelayout-frontpage` y se remapearon a
  `richimath-has-landing`. URL limpias: **`local/richimath/routes.php` es la
  fuente de verdad**, `local_richimath\routes` construye enlaces y
  `scripts/build-routes.php` escribe el bloque del `.htaccess`. CADA ruta va en
  LAS DOS DIRECCIONES: la dirección limpia se sirve en el sitio (rewrite) y el
  script real redirige 301 a ella, que es lo que saca el `.php` de la barra
  aunque el enlace lo haya hecho Moodle. Tres guardas imprescindibles:
  `RewriteCond %{ENV:REDIRECT_STATUS} ^$` (si no, bucle rewrite↔redirect),
  `RewriteCond %{REQUEST_METHOD} !=POST` (un POST redirigido pierde los datos:
  rompería cada login) y `DirectorySlash Off` (login/ es un directorio real y
  Apache mandaba `/login` a `/login/` antes de las reglas). Las páginas propias
  hacen `$PAGE->set_url(routes::url(...))` para que sus formularios posteen a la
  dirección corta; el formulario de acceso es la ÚNICA excepción escrita a mano
  (`{{{ config.wwwroot }}}/login` en core/loginform.mustache) porque su contexto
  lo arma el core. Lo que NO puede limpiarse: lo que se identifica por id
  (`/course/view.php?id=31`) — haría falta un slug por curso. El servidor
  necesita `mod_rewrite` + `AllowOverride FileInfo Indexes` (en local lo
  enciende el docker-compose). TRAMPA GRANDE: Moodle rellena
  `$SESSION->wantsurl` con el REFERER en el POST de acceso y solo excluye SUS
  formas de escribir el login (`/login/`, `/login/index.php`), no la limpia —
  entrar desde `/login` te devolvía a `/login` y salía «ya inició sesión como…»
  como si la contraseña fallara. Lo limpia `local_richimath_after_config()`
  (setup.php lo llama en cada petición, ya con sesión). INTERRUPTOR:
  `local_richimath/prettyurls` (por defecto APAGADO) decide si el código emite
  la dirección limpia o la URL normal — con él encendido y el `.htaccess`
  desactivado, el formulario de acceso apunta a `/login`, que sin reglas es un
  DIRECTORIO real: Apache redirige, el POST se vuelve GET y el acceso deja de
  funcionar en silencio (le pasó a Richi en producción el 2026-09-11). Orden
  correcto SIEMPRE: vhost → `.htaccess` → interruptor. Los redirectores son 302
  y no 301 porque un 301 se cachea para siempre en el navegador y apagar las
  rutas dejaría a los visitantes pidiendo URL muertas. Detalle en
  docs/product/public-site-and-routes.md.
- **Pre Uni y Universidad repintados (2026-09-05, docs/design/themes/)**:
  `3_PREUNIV` = *Red & Black* (carmín `#dc2626` + azabache `#0f172a`, Outfit +
  Inter, esquinas RECTAS 4/8px) y `4_UNIVERSIDAD` = *Dynamic STEM Academy UI*
  (zafiro `#1e40af`, eléctrico `#3b82f6`, chispa cian `#06b6d4`, Outfit + Plus
  Jakarta Sans, esquinas 12/16px). Antes eran dos azules casi idénticos. Cada
  hijo tiene ahora su `scss/post.scss` (rmpreu lo estrena: regla técnica roja
  de 3px sobre `.main-inner`, botón `.btn-secondary` azabache, cápsula roja;
  hay que concatenarlo en su `lib.php` como hace rmuniversidad). El rail de
  Universidad deja de ser pizarra Blackboard: degradado zafiro con la fila
  activa en pastilla BLANCA con tinta zafiro — blanco sobre eléctrico `#3b82f6`
  da 3.68:1 y no pasa AA; blanco de fondo llega a 8.7:1. Los radios se fijan en
  la palette del hijo (`$border-radius`, `$card-border-radius`, `$btn-*`), que
  el preset de Boost lee después. El acento del examen sigue al nivel vía
  `$od-exam-accent` (carmín / eléctrico). Comparativa token a token actualizada
  en docs/product/plans-and-appearances.md.
- **UX_PRO: login y flujo de examen (2026-09-05)**: `docs/ux_pro/` trae DOS
  sistemas con su propio `DESIGN.md` — *Crystalline Academic Glass* (login:
  vidrio, esquinas suaves) e *Imperial Academic Precision* (mod_quiz: obsidiana,
  esquina 0px, oro). Regla aplicada: **estructura y forma de UX_PRO, color y
  tipografía del nivel**; el oro imperial se lee como ROL y lo ocupa
  `$od-exam-accent` (= la clave del nivel), con `$od-exam-accent-bright` para el
  texto sobre la obsidiana (el acento del nivel se elige contra lienzo claro y
  cae bajo 4.5:1 en negro). Única familia añadida: **JetBrains Mono**, la que
  ambos DESIGN.md comparten, solo para metadatos/temporizador/etiquetas.
  Login = plantilla `core/loginform` reescrita (tarjeta partida) + sección A;
  examen = **solo SCSS, sección F** (no se toca `mod/quiz`). El login SIEMPRE
  usa el tema del SITIO: el tema del usuario no existe sin sesión. NO se
  implementó lo que el tablero inventa (proctoring, scratchpad, solucionario,
  PDF, SSO sin `auth_oauth2`, casilla «recordar usuario» — esa cookie la decide
  un ajuste del sitio). TRAMPAS: Boost fija `.login-container` con
  `width:500px !important`; la `.row` del layout de login desborda 15px;
  `.que .info` va flotado 7em; `.trafficlight` de la matriz es una capa absoluta
  blanca que tapa el número (soltarla del `top`); core estiliza la matriz como
  `.path-mod-quiz #mod_quiz_navblock` (id+clase: hay que igualar). El local corre
  con `themedesignermode` en config.php → sirve el CSS por plugin y EN OTRO
  ORDEN. Sembrar un examen en CLI: `add_moduleinfo()` (con
  `session\manager::set_user(get_admin())`) + GIFT por `qformat_gift` +
  `quiz_add_quiz_question()`; `add_moduleinfo()` NO guarda las opciones de
  revisión (fijar los siete `review*` a mano o el alumno no puede revisar).
  Detalle en docs/product/ux-pro-login-and-exam-flow.md.
- **Un logo por nivel (2026-09-04)**: los cuatro tableros de Richi en
  `docs/design/logos/LOGO_*/code.html` (860×400) traen el isotipo RM en cuatro
  ediciones — oro/lima/rojo/azul corporativo.
  `scripts/build-theme-logos.py` recorta el monograma a `assets/logo-*.svg` y lo
  rasteriza con **Chrome sin cabeza** (el arte usa `filter="blur(5px)"`, función
  de filtro CSS en atributo SVG: solo un navegador la resuelve; un rasterizador
  clásico deja manchas opacas). Salida por hijo: `pix_plugins/theme/richimath/
  whitelogo.png` (400px) y `pix/favicon.ico` (16/32/48). La clave es
  `pix_plugins/<tipo>/<plugin>/`: un tema puede **pisar la imagen de otro
  componente**, así que el `{{#pix}} whitelogo, theme_richimath {{/pix}}` del
  sidebar sirve el logo del hijo sin tocar plantilla, renderer ni SCSS. TRAMPA:
  el **favicon no hereda** — para `$image === 'favicon'`, `resolve_image_location()`
  devuelve `$this->dir/pix/favicon.ico` sin mirar a los padres, así que los
  cuatro hijos servían un 404 (comprobado antes/después). El login y la portada
  anónima siguen con el RM del base: el tema del usuario no existe sin sesión.
  Verificación: `?theme=` con `allowthemechangeonurl` encendido y **apagado
  después** (la sesión guarda copia de `user.theme`, cambiarlo en BD no basta).
  Detalle en docs/product/plans-and-appearances.md.
- **Cuándo se aplica el tema (2026-09-04, T5/T6)**: el tema es una FOTO en
  `user.theme`, nunca se resuelve al pintar. Se escribe (a) al cambiar el plan
  DE UN USUARIO — inmediato, y si es uno mismo también en la sesión — y (b) al
  iniciar sesión (observador de `\core\event\user_loggedin`). Cambiar la
  apariencia de un PLAN no toca a nadie: llega en el próximo login. Código en
  capas: `plans/appearance` (catálogo) · `plans/plan` (entidad) ·
  `plans/*_repository` (únicos que nombran tabla) · `plans/theme_assignment`
  (único que escribe `user.theme`) · `plans/plan_service` (casos de uso) ·
  páginas y observador (entrega). Trampa: `\core_user\fields::for_name()->get_sql()`
  NO pone la coma inicial salvo que se pida (5.º argumento), y sin los campos
  de nombre `fullname()` llena la página de avisos de debugging.

- **Tarjetas de categoría en color (2026-09-15)**: las tres tarjetas de
  `/course/index.php` (ESCOLAR / PRE UNIVERSITARIO / UNIVERSIDAD) eran blancas
  con la misma franja; ahora cada una toma un acento de
  `$richimath-course-accents` **por posición** (`:nth-of-type(6n+i)`), no por id
  de categoría, así que el color sale de la paleta del hijo y sigue al plan del
  alumno. El mixin `richimath-category-accent()` emite siete propiedades
  personalizadas (`--rm-cat-*`): un solo juego de reglas para los seis colores y
  **los hijos de una tarjeta abierta heredan el color del padre**. Dentro de la
  tarjeta abierta **no puede haber caja**: el panel blanco del primer intento se
  leía como una tarjeta dentro de otra (Richi lo devolvió), así que los hijos van
  sobre el velo con una línea de pelo, y los `.coursebox` de dentro pierden su
  fondo blanco. La tinta es
  `mix($accent, $richimath-navy, 55%)` porque el acento crudo cae bajo 4.5:1 con
  ámbar o lima (peor caso medido 4,78:1), y el hover **oscurece el velo en vez de
  aclarar el texto** por lo mismo. Se reordenaron (solo el orden) las rotaciones
  de `rmuniversidad` y `rmpreu`: las tres primeras posiciones son las que la
  rejilla pone en fila y eran tres azules y dos rojos. TRAMPA que salió aquí: la
  sección E repintaba estas tarjetas y **no se veía nunca** — C9 cuelga de
  `body#page-course-index` y un **id gana a cualquier número de clases**, así que
  `#{$richimath-skin}` (`:not()` encadenados = clases) siempre pierde; bloque
  retirado. Detalle en docs/theme-richimath.md.

## Historial de la sesión fundacional (2026-08-19 → 2026-08-26, 38 commits)

Todo el tema richimath nació en esta sesión. Cronología por bloques, con commits:

1. **Descubrimiento y saneamiento** (`3122cbd8` y previos): Moodle 4.3.12 en
   Contabo (`/var/www/html`, MySQL, Apache:80). Se destrackeó `config.php`
   (credenciales en el historial: commits `b6e0adb7`/`afee9a8e` — contraseña BD
   AÚN sin rotar), docs/ operativos creados.
2. **Tema base** (`ce7a83d9`, `e3143f87`): hijo de Boost; login con arte;
   calendario/mes estilo Blackboard; entorno local Docker (`dev-up.sh`).
3. **Sidebar + dashboard** (`9a26590f`, `dfb213a9`, `6c8b98a2`): sidebar navy
   fijo (drawers.mustache + local/sidebar.mustache), estado activo por URL
   (JS), enlace Cursos. Dedupe defensivo de enlaces.
4. **Vista de día** (`9682fe23`): override de `day_detailed` (¡no
   calendar_day! — sobrevive la navegación AJAX), rejilla 06-22h, barra "ahora".
5. **Skin Blackboard global** (`d750035d`, C1-C9) + **fondo por capas y
   cristal esmerilado** (`1f70093f`→`d1eb1dee`): $richimath-skin, frost 66% +
   veils; los opacos de Boost (`#region-main`, `bg-white`, `bg-light`)
   neutralizados uno a uno CON MEDICIÓN DE DOM (no a ciegas).
6. **Assets del cliente** (`05b83936`): los SVG llevan el raster como MÁSCARA →
   pipeline: render compuesto en navegador + knockout de blanco →
   pix/{loginlogo,loginsquare,whitelogo}.png + loginground.jpg.
7. **Refactor clean-code** (`70cb077a`): tokens de velo, mixin frosted,
   verificado con DIFF DEL CSS COMPILADO antes/después (61 líneas, 8 hunks).
8. **Idioma** (`f7692128`): paquete es instalado (sin CLI en 4.3: controlador
   tool_langimport por php -r), lang del sitio + perfiles.
9. **La saga del despliegue** (`5891bd31`, `ea499ced`, `d74d1da5`): DOS causas
   apiladas — entorno (PHP 8.3 bloquea 4.3 por RESTRICT → PPA php8.2;
   dbtype mariadb→mysqli; max_input_vars) Y el `.gitignore` sin anclar que se
   tragó `theme/richimath/config.php` (tema instalado pero cayendo a boost).
   Diagnóstico decisivo: sondas PHP temporales servidas por Apache.
   `scripts/deploy-contabo.sh` con trap anti-mantenimiento-colgado.
10. **Espejo local** = dump Contabo + filedir (`9d4cec44`): BD y filedir viajan
    JUNTOS. richi85 es el admin local (admin/Admin.1234 ya no existe).
11. **Página de curso** (`9a09cb3c`): causa raíz del solape del título:
    backdrop-filter convierte la tarjeta en containing block de los fixed.
    Secciones-tarjeta, chevron derecha, filas con icono tintado por propósito.
12. **Catálogo staff-only** (`340bf352`): renderer show_catalog() por
    capacidades; alumnos navegan por Mis cursos.
13. **Lote de tickets de Richi** (docx con imágenes; plan en
    docs/plans/tickets-20260824-plan.md): `6a7d45a4`→`d094d82a` — marca
    "Richi Math", kebabs del grader (rehecho en `716cc750`: tabla min-width
    100%, kebabs en flujo atenuados), paginación clonada arriba en Mis cursos
    (MutationObserver, clic delegado), botón Consultas (FAB → chat con
    get_admin()), sidebar conmutable, oferta pre-login.
14. **Landing dinámica** (`22383d7a`, `482934ea`): catálogo real por pestañas
    (core_course_category API, cap 8/categoría repartido por subcategoría,
    fichas SIN enlace a propósito), frontpage='' para anónimos.
15. **Chips del dashboard** (`e736f304`): dashboard_chips() — cursos del
    usuario (orden lastaccess, 12 + Ver todos) + categorías solo staff; gate
    pagelayout-mydashboard + CONTEXT_USER (indexsys comparte pagetype).
16. **Animación matemática** (`e736f304`, `8c45ed45`, `d40dad07`): capa fija a
    viewport en landing y login; refactor a parcial único
    `local/mathfield.mustache` con constantes nombradas; tamaño responsive por
    `--size` × clamp(9px,1.4vw,20px) → 22-67px desktop, 10-30px móvil.
17. **Decisiones de Richi 2026-08-26**: T-04 modos "Blackboard"/"Canvas",
    Canvas default (`c0a3a79e`); T-10 botón visible también para admin;
    T-11 pagos descartado por ahora; T-06 quiere BBB → autoalojado ~10€/mes
    pendiente de OK; compartir pantalla desde iPad-navegador NO existe (iOS).
18. **Guías de producto** (docs/product/): matricular-por-correo (CSV),
    pizarra-virtual (Excalidraw), self-paced-weeks (goteo por finalización),
    gamification (H5P: librerías YA instaladas — verificado 2026-09-18 en
    `/h5p/libraries.php`: Memory Game, Interactive Video, Mark the Words,
    Multiple Choice, Question Set…; badges ON. OJO: el ojo de esa página
    ENCIENDE/APAGA el tipo (`set_library_enabled`), no descarga; y **Moodle no
    descarga del hub de H5P** — eso es del plugin de WordPress. Tipo nuevo =
    subir un `.h5p` al Banco de contenido, y queda para todo el sitio).
20. **2026-08-31 (assets de Richi)**: `assets/exam-background.png`
    («FONDO PARA EXAMENES.png») → `pix/examground.jpg` + SCSS D8 sobre
    `.que .formulation`; `assets/frontpage-cover.png` («tema_portada.png»)
    previsualizado como banner en local (prod conserva el banner que Richi
    puso por UI). El generador de preguntas de phpunit NO sirve en CLI
    (exige PHPUnit): para poblar un cuestionario de prueba, importar GIFT
    con `qformat_gift` + `quiz_add_quiz_question`. agent-browser: rutas de
    captura ABSOLUTAS (el daemon resuelve relativas contra su propio cwd).
19. **Sesión 2026-08-29**: portada con sesión (banner por script +
    tarjetas C9 reutilizadas; la clase `.frontpage-category-names` va en el
    nodo del árbol, no en un wrapper — un selector descendiente no casaba),
    verificación de foto de usuario por el file picker (el botón «Agregar…»
    del filemanager necesita click por JS con agent-browser; el anchor
    `href="#"` navega si el YUI no ha enganchado), primer plugin local
    (`local_richimath`, invitaciones). Trampa nueva: `core_user::get_noreply_user()`
    clonado como destinatario trae `emailstop = 1` → poner 0 o
    `email_to_user` calla. `docker compose exec` desde el contenedor: rutas
    ABSOLUTAS a los scripts (`/var/www/html/...`).

### Preferencias del usuario (imprescindibles)

- **NUNCA usar Artifacts** (pidió explícitamente no usarlos; preferencia
  también en la memoria global del agente). Entregas: capturas + ficheros locales.
- Verificación SIEMPRE con capturas de navegador real, ambos roles, 1440+390.
- Español en el trato y en docs; commits en inglés; **nunca git push** (lo hace él).
- Las contraseñas de richi85/estudiante.demo circularon por el chat:
  NO escribirlas en el repo; recomendada rotación (pendiente).

### Trucos operativos del entorno local

- `docker compose` SIEMPRE desde ~/Documentos/github/moodle (el cwd del shell
  se resetea al repo chatwoot entre comandos — anclar con cd && ...).
- Logout en el navegador de pruebas: /login/logout.php → el botón de
  confirmación se llama **«Continuar»** (no "Cerrar sesión" del sidebar).
- agent-browser: refs @eN caducan en cada snapshot; eval con
  getElementsByClassName cuando querySelector('.a .b') falle por comillas.
- Sondas web: fichero PHP temporal en /var/www/html + curl + rm — el
  diagnóstico definitivo cuando CLI y web discrepan.
- cfg.php como www-data desde /root escupe warning chdir inofensivo:
  cd /var/www/html primero.

## Decisiones de producto vigentes

- **Portada anónima = landing** (héroe + catálogo real en pestañas, desde
  `core_renderer::offer_categories()`; los cursos son texto plano, no enlaces —
  un clic anónimo solo llega a "no se puede auto matricular"). La portada con
  sesión no cambia. Detalle en docs/theme-richimath.md → "Landing pública".
- Catálogo (`/course/index.php`) **solo staff** (renderer gate). Alumnos navegan
  por "Mis cursos". Si Richi quiere catálogo público para captación, revertir el
  gate y/o ocultar categorías selectivamente.
- El dashboard por defecto y sus bloques se gestionan por UI (BD, no git).
- `/my/courses.php` intacto (core); el catálogo bonito vive en `/course/index.php`.
