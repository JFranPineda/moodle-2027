# Levantar BigBlueButton en el VPS 6 (Ubuntu 22.04)

Guía de ejecución. **Las decisiones ya están tomadas** y se explican al final,
en la Parte 2; si solo quieres levantarlo, sigue la Parte 1 de arriba abajo.

| | |
|---|---|
| Servidor | Contabo **Cloud VPS 6** — 6 vCPU, 12 GB RAM, **400 GB SSD**, puerto 300 Mbit/s |
| Sistema | **Ubuntu 22.04** (ni 24.04 ni 26.04 — Parte 2 §A) |
| BBB | **3.0** (`-v jammy-300`) |
| Hostname | `clases.richiacademy.com` |
| Para qué | 5 clases a la vez de 1 profesor + 10 alumnos, cámaras de alumno apagadas, **con grabación** |
| Moodle que lo usa | `https://richiacademy.com` (otro servidor, el VPS 4) |

Tiempo total: ~1 hora, de la cual 25 min es el instalador trabajando solo.

---

# PARTE 1 — LOS PASOS

## Paso 1 · Contratar el VPS (panel de Contabo)

1. Producto: **Cloud VPS 6**.
2. **Región: América (EE. UU. Este o Central).** No Europa: el configurador
   marca ~183 ms desde Perú y una conversación con esa latencia se pisa. Es lo
   único que no se arregla después sin reinstalar.
3. **Storage: 400 GB SSD** (+3,60 $/mes). Con grabación obligatoria, 200 GB se
   llenan en 1–3 meses.
4. **Imagen: Ubuntu 22.04.** Si el formulario no la ofrece, contrata igual y
   reinstala 22.04 desde el panel antes del paso 3.
5. Anota: **IP pública**, **IPv6** y la contraseña de root.

## Paso 2 · DNS (panel de Porkbun)

*Domain Management → richiacademy.com → DNS Records → + Add record*:

| Type | Host | Answer | TTL |
|---|---|---|---|
| A | `clases` | la IP del paso 1 | 600 |
| AAAA | `clases` | la IPv6 del paso 1 | 600 |

Comprueba desde tu portátil **antes de seguir** (Let's Encrypt lo exige):

```bash
dig +short clases.richiacademy.com          # → tu IP
dig +short AAAA clases.richiacademy.com     # → tu IPv6
```

Si no responde, espera y repite. No avances sin esto.

## Paso 3 · Preparar el sistema

```bash
ssh root@LA_IP

apt update && apt upgrade -y
timedatectl set-timezone America/Lima
hostnamectl set-hostname clases.richiacademy.com
echo "127.0.1.1 clases.richiacademy.com" >> /etc/hosts

# Swap de 8 GB. Con 12 GB de RAM no es una red de seguridad: es parte del plan.
fallocate -l 8G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

Comprobaciones antes de instalar (el instalador las repite y aborta si fallan):

```bash
lsb_release -a                      # Ubuntu 22.04
nproc                               # 6
free -h                             # ~12 GB de RAM y 8 GB de swap
df -h /                             # ~400 GB
ss -tlnp | grep -E ':80 |:443 '     # NADA debe responder aquí
hostname -I; dig +short clases.richiacademy.com   # la misma IP
```

Si `apt upgrade` cambió el kernel: `reboot`, espera un minuto y vuelve a entrar.

## Paso 4 · Instalar BBB

Una sola orden. Tarda 20–30 minutos.

```bash
wget -qO- https://raw.githubusercontent.com/bigbluebutton/bbb-install/v3.0.x-release/bbb-install.sh \
  | bash -s -- -v jammy-300 -s clases.richiacademy.com -e learning.richiacademy@gmail.com -w
```

Qué hace cada opción: `-v jammy-300` = BBB 3.0 sobre 22.04 · `-s` = hostname ·
`-e` = correo para el certificado (Let's Encrypt, se renueva solo) · `-w` =
firewall `ufw` con los puertos de BBB. **Sin `-g`** (nada de Greenlight: se
entra solo desde Moodle) y **sin `-a`** (nada de demos de la API).

Si se corta el SSH a medias: vuelve a entrar y **repite la misma orden**. Es
idempotente.

## Paso 5 · Ajustes del servidor

```bash
nano /etc/bigbluebutton/bbb-web.properties
```

Pega esto (está también en
[`scripts/bbb/config/bbb-web.properties`](../scripts/bbb/config/bbb-web.properties)
del repositorio, que es la copia buena):

```properties
defaultMeetingDuration=0
autoStartRecording=false
allowStartStopRecording=true
defaultMaxUsers=20
muteOnStart=true
lockSettingsDisableCam=true
meetingCameraCap=2
```

`lockSettingsDisableCam=true` es **la línea que sostiene todo el
dimensionamiento**: bloquea la cámara de los alumnos. Los bloqueos de BBB se
aplican solo a espectadores, así que el profesor conserva la suya.

```bash
bbb-conf --restart
```

## Paso 6 · Procesar las grabaciones de noche

Grabar durante la clase es barato; **procesar** la grabación al terminar es lo
que exprime la CPU. Con 5 clases seguidas, el procesado se manda a la madrugada.

BBB 3.0 no trae ningún timer para esto. El procesado son dos servicios:
`bbb-rap-starter` **encola** en redis/resque las reuniones terminadas, y
`bbb-rap-resque-worker` **consume** la cola en cuanto acaba la clase. La ventana
se consigue parando el worker de día: el starter sigue encolando, los trabajos
se acumulan y el worker los drena al levantar a las 23:00.

```bash
systemctl disable --now bbb-rap-resque-worker.service
```

Los cuatro units están en
[`scripts/bbb/config/rap-night-window/`](../scripts/bbb/config/rap-night-window/),
que es la copia buena:

```bash
cp bbb-rap-worker-*.timer bbb-rap-worker-*.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now bbb-rap-worker-start.timer bbb-rap-worker-stop.timer
systemctl list-timers 'bbb-rap-worker-*'
```

La ventana es **23:00 – 06:00** (decidida el 2026-09-14: las clases pueden
acabar tarde). `OnCalendar` usa la hora local y el servidor va en
`America/Lima` (paso 3), así que no hay que convertir nada a GMT-5 a mano: se
escribe `23:00:00` y son las 11 de la noche en Perú. Para mover la ventana se
edita esa línea en cada `.timer` — systemd no admite variables ahí, el valor va
literal:

```bash
sed -i 's/^OnCalendar=.*/OnCalendar=*-*-* 23:00:00/' /etc/systemd/system/bbb-rap-worker-start.timer
sed -i 's/^OnCalendar=.*/OnCalendar=*-*-* 06:00:00/' /etc/systemd/system/bbb-rap-worker-stop.timer
systemctl daemon-reload && systemctl restart bbb-rap-worker-start.timer bbb-rap-worker-stop.timer
```

**Trampa**: `bbb-conf --restart` puede volver a levantar el worker, porque su
unit lleva `PartOf=bigbluebutton.target`. Tras cualquier `bbb-conf --restart`
hecho de día, vuelve a pararlo con `systemctl stop bbb-rap-resque-worker`.

El worker ya viene con `Nice=19`, así que incluso corriendo cede CPU a las
clases en vivo: la ventana es la segunda red, no la única.

**Díselo al profesor**: la grabación aparece **a la mañana siguiente**, no al
terminar la clase. Si algún día hay clase después de las 23:00, corre la
ventana.

Señal de que la ventana se quedó corta: los trabajos en
`/var/bigbluebutton/recording/status/processing/` crecen día a día en vez de
vaciarse. El cierre de las 06:00 pilla un trabajo a medias y lo deja para la
noche siguiente — no se pierde, pero el alumno lo ve un día tarde. Se ensancha
la ventana, o se sube a `COUNT=2` en el unit del worker.

## Paso 7 · Comprobar que quedó bien

```bash
bbb-conf --check      # sin "Potential problems" graves
bbb-conf --status     # todos los servicios [ active ]
ufw status            # ver más abajo cómo se lee
certbot renew --dry-run
bbb-conf --secret     # ← copia la URL y el secreto: son para el paso 8
```

**`ufw status` no lista los puertos por número**, y eso despista: agrupa por
perfil de aplicación. `OpenSSH` *es* el 22/tcp y `Nginx Full` *es* el 80 más el
443/tcp. Lo que debe salir:

```
OpenSSH                    ALLOW       Anywhere     ← 22/tcp
Nginx Full                 ALLOW       Anywhere     ← 80/tcp y 443/tcp
16384:32768/udp            ALLOW       Anywhere     ← el medio
3478                       ALLOW       Anywhere     ← el TURN local
```

Para ver qué puertos hay detrás de cada nombre: `ufw app info OpenSSH` y
`ufw app info 'Nginx Full'`.

Comprueba también que el worker de grabaciones sigue parado, porque
`bbb-conf --restart` lo resucita (paso 6):

```bash
systemctl is-enabled bbb-rap-resque-worker   # disabled
systemctl is-active bbb-rap-resque-worker    # inactive
```

En el navegador, `https://clases.richiacademy.com/` debe abrir **con candado**.
Mostrará una página mínima: es lo correcto, no hay Greenlight.

## Paso 8 · Conectarlo a Moodle

En el **otro** servidor (el de `richiacademy.com`), por interfaz web:

1. Entra como admin a
   `https://richiacademy.com/admin/settings.php?section=modsettingbigbluebuttonbn`
2. **URL del servidor**: `https://clases.richiacademy.com/bigbluebutton/`
   ← con la barra final.
3. **Secreto compartido**: el que copiaste en el paso 7. Hay que pulsar el
   lápiz para poder editarlo.
4. **NO marcar** «Comprendo y acepto el acuerdo sobre tratamiento de datos»: ese
   acuerdo es de Blindside y solo aplica a SU servidor. La condición está escrita
   en `mod/bigbluebuttonbn/lib.php` — `server_url === DEFAULT_SERVER_URL &&
   !default_dpa_accepted` —, así que apuntando al servidor propio la casilla
   deja de pintar nada.
5. Guardar.
6. *Extensiones → Módulos de actividad → Gestionar actividades*: comprueba que
   **BigBlueButton** tiene el ojo abierto.
7. En cada actividad que crees: marca **«La sesión puede ser grabada»**, o el
   profesor no verá el botón de grabar.

**La lista de restricciones de esa página NO desaparece al guardar**, y engaña:
«duración máxima 60 minutos», «25 usuarios», etc. siguen ahí. Es la descripción
fija del campo (`config_server_url_description`, que
`mod/bigbluebuttonbn/classes/settings.php` pasa como tercer argumento del
`admin_setting_configtext`): habla de las credenciales por defecto y se imprime
apuntes donde apuntes.

El aviso que sí desaparece es otro: `show_default_server_warning()` en
`mod/bigbluebuttonbn/classes/output/view_page.php`, que se muestra al admin
**dentro de una actividad BBB** mientras la URL siga siendo la de Blindside. Así
que la comprobación no se hace en la página de ajustes, sino creando una
actividad BigBlueButton en un curso y abriéndola como admin: sin aviso =
apuntando al servidor propio.

**Algoritmo de checksum**: dejar SHA1, que es el valor por defecto del plugin.
Si al entrar a una sala sale «Failed to join» o un error de checksum, cambiarlo
a SHA256 y repetir.

## Paso 9 · Snapshot

Al **VPS 6, el de BBB** — no al VPS 4 de Moodle. El plan incluye 2. Haz uno
**ahora**, con todo instalado y conectado: volver aquí es más rápido que
repetir la guía.

Panel de Contabo → **Cloud VPS 6 (`clases.richiacademy.com`)** → *Snapshots* →
crear, con nombre y fecha.

Al VPS 4 no le sirve de gran cosa: su valor son la BD y `moodledata`, que
cambian cada día, así que la foto del disco envejece mal. Ahí el respaldo real
es el dump de BD + filedir (ver `docs/local-dev-environment.md`). Un snapshot
del VPS 4 no sobra, pero **no es un respaldo de Moodle**.

## Paso 10 · Prueba de aceptación

No des el servidor por bueno sin medirlo. Con `htop` abierto en otra terminal:

1. **Una clase real** (profesor + 2 o 3 alumnos): audio, pizarra, compartir
   pantalla. Comprueba que un alumno **no puede** encender su cámara.
2. **Deja la sala abierta más de 65 minutos**: no debe cerrarse sola.
3. **El ensayo que importa**: abre **las 5 salas a la vez** con toda la gente
   que puedas reunir, y que **las 5 compartan pantalla al mismo tiempo**. Ese es
   el pico real. Durante el ensayo, en el VPS:

```bash
htop                # CPU total por debajo del 85 %, sin swap en uso
free -h             # la fila Swap, columna used, en 0
vnstat -tr 20       # salida esperada: 80–100 Mbit/s
bbb-conf --check    # ningún servicio caído
```

4. **Graba 2 minutos**, termina la clase y comprueba **a la mañana siguiente**
   que la grabación aparece en la actividad de Moodle.

**Criterio de subida, decidido de antemano.** Si en ese pico ves cualquiera de
estas cuatro cosas, el VPS 6 se queda corto y hay que subir a **Cloud VPS 8**
desde el panel (se redimensiona sin reinstalar, sin repetir nada de esta guía):

- CPU sostenida por encima del **85 %**
- **swap en uso** durante la clase
- salida por encima de **250 Mbit/s**
- disco por encima del **70 %** pese a la poda de grabaciones

---

# PARTE 2 — POR QUÉ, Y QUÉ HACER DESPUÉS

## A. Por qué Ubuntu 22.04 y BBB 3.0

Cada versión de BBB se publica para **una sola** distribución:

| | BBB 3.0 | BBB 4.0 |
|---|---|---|
| Ubuntu | **22.04** | **24.04** |
| Estado | Estable | **En desarrollo.** Su propia documentación dice *«unreleased… (in development)»* |
| Qué sirve el repositorio | 3.0.x | `4.0.0~rc.3+20260911…` — candidata **recompilada a diario** |
| Instalador | `-v jammy-300` | `-v noble-400` (rama `v4.0.x-release`) |

Sí **se puede** instalar en 24.04 con la 4.0. No se recomienda aquí por dos
razones de este proyecto: las **grabaciones son requisito** y la cadena de
grabación es lo más frágil al cambiar de versión mayor (y falla *después* de la
clase, con los datos ya capturados); y un `apt upgrade` rutinario sobre un
repositorio de compilaciones diarias te cambia el servidor sin avisar. Además,
el módulo BigBlueButton de Moodle 4.3 se escribió contra la API 2.x/3.x.

Comprobar si esto ya cambió, en 30 segundos:

```bash
curl -s -o /dev/null -w "3.0 jammy: %{http_code}\n" \
  https://ubuntu.bigbluebutton.org/jammy-300/dists/bigbluebutton-jammy/Release.gpg
curl -s -o /dev/null -w "4.0 noble: %{http_code}\n" \
  https://ubuntu.bigbluebutton.org/noble-400/dists/bigbluebutton-noble/Release.gpg
curl -s https://ubuntu.bigbluebutton.org/noble-400/dists/bigbluebutton-noble/main/binary-amd64/Packages \
  | grep -A1 '^Package: bbb-html5$'
```

22.04 tiene soporte estándar hasta **abril de 2027** (ESM hasta 2032). El plan:
empezar aquí y migrar a 24.04 + 4.0 cuando 4.0 sea estable, con §D (1 hora).

## B. Por qué el VPS 6 aguanta 5 × 11, y cuándo deja de aguantar

| Recurso | Tu caso (cámaras de alumno apagadas) | ¿Cabe? |
|---|---|---|
| Audio | 55 personas × ~40 kbit/s ≈ 5–10 Mbit/s | Sobra |
| Pantalla compartida | 5 emisores + 50 receptores ≈ **80–90 Mbit/s** | Sí (puerto de 300) |
| CPU | BBB **no recodifica**: reenvía. 55 de audio + 5 pantallas en 6 núcleos | Ajustado |
| RAM | **El riesgo.** BBB 3.0 carga Java, Scala, FreeSWITCH, mediasoup, Redis, Postgres, Hasura | 12 GB → swap obligatorio |
| Disco | Las grabaciones (ver §C) | El límite real |

El mínimo que BBB documenta es 8 núcleos y 16 GB: este plan está por debajo, y
por eso el paso 10 mide en vez de suponer, y por eso las cámaras de alumno están
bloqueadas. 50 cámaras encendidas no caben ni en esta CPU ni en este puerto.

## C. Las grabaciones y el disco

La pregunta que surge sola es si se puede colgar un Object Storage de Contabo
(~1 TB) al VPS 6 y guardar ahí las grabaciones. **Se puede, pero no resuelve lo
que parece.** Merece la pena entender por qué antes de pagarlo.

### Cómo sirve BBB una grabación

Una grabación publicada **no es un fichero**: es un directorio con cientos de
piezas pequeñas —las diapositivas en PNG, los eventos en JSON, miniaturas, el
audio, el vídeo de la pantalla compartida— que **nginx sirve desde el disco
local** en `/var/bigbluebutton/recording/published/`. Al abrirla, el navegador
pide decenas de esos ficheros y va saltando por el vídeo con peticiones de
rango.

Eso decide el resto:

| Opción | Qué es | Para qué sirve de verdad |
|---|---|---|
| **Más SSD en el mismo VPS 6** | 400 GB por +3,60 $/mes al contratar; después, *Extend SSD Storage* en el panel | **Lo que tus alumnos necesitan**: la grabación sigue viva, reproducible y enlazada en Moodle, sin que BBB se entere del cambio |
| **Object Storage** (S3, ~10 €/TB al mes, sin coste de salida) | Un **servicio aparte** al que se llega por red con la API de S3 — no vive «dentro» del VPS | **Archivo frío**: guardar copia de lo viejo barato y para siempre |
| **Subir a VPS 8** | 300 GB y el mínimo de BBB cumplido | Cuando además falte CPU o RAM |

### Por qué el Object Storage no debe ser el directorio de reproducción

Se puede montar con `s3fs` o `rclone mount` y *parece* que funciona. En la
práctica se paga caro:

- **Cientos de ficheros diminutos por grabación.** Cada apertura es una petición
  HTTP: lo que en disco son microsegundos, ahí son decenas de milisegundos. Una
  clase tarda en abrir y el salto por la barra de tiempo se vuelve torpe.
- **Un montaje FUSE es un punto de fallo nuevo.** Si se cae la red o el montaje,
  nginx devuelve errores y **todas** las grabaciones desaparecen a la vez.
- **El procesado escribe ahí.** La tubería de BBB hace miles de escrituras por
  grabación; sobre S3 es lenta y frágil.

### Pros y contras, en corto

**Object Storage — a favor**: ~10 €/TB al mes es mucho más barato que ampliar
SSD; **sin coste de salida** en Contabo; capacidad prácticamente infinita; la
copia sobrevive aunque el VPS se pierda (es un respaldo real, no solo espacio).

**Object Storage — en contra**: no sirve como directorio de reproducción (lo de
arriba); una grabación archivada **deja de aparecer en Moodle** salvo que la
devuelvas al disco; y montarla añade credenciales y un servicio más que
mantener.

### El horizonte, en meses y en gigas

«Ciclo en curso + anterior» hay que traducirlo a un número, porque el disco solo
entiende meses. Con 5 clases de 1,5 h, 20 días al mes ≈ **150 horas al mes**:

| Cuánto pesa una hora grabada | Al mes | En 200 GB | **En los 400 GB del paso 1** |
|---|---|---|---|
| 300 MB (poca pantalla compartida) | 45 GB | ~4 meses | **~8 meses** |
| 600 MB (uso normal) | 90 GB | ~2 meses | **~4 meses** |
| 1 GB (pantalla casi todo el rato) | 150 GB | ~1 mes | **~2,5 meses** |

*(Descontando ~20 GB de sistema y BBB. La horquilla es amplia a propósito:
depende de cuánto se comparta pantalla, y por eso conviene medir la
primera semana en vez de creerse esta tabla.)*

Léelo así: **con 400 GB, «ciclo en curso + anterior» cabe si un ciclo dura un
trimestre**; si vuestros ciclos son de cinco o seis meses, o se comparte
pantalla todo el tiempo, habrá que archivar antes o ampliar otra vez.

### La combinación que sí cumple el requisito

Tu requisito es que **el alumno pueda volver a ver su clase**. Eso obliga a que
la grabación esté viva en el disco del VPS. Entonces:

1. **Define el horizonte que el alumno tiene derecho a ver** — por ejemplo, el
   ciclo en curso y el anterior. Eso, y no «todo», es lo que debe caber en el
   disco local; dimensiona el SSD para ese horizonte con lo que midas en la
   primera semana.
2. **Todo lo anterior se archiva en Object Storage** con `rclone`, y se borra del
   servidor. Deja de verse en Moodle: es una copia institucional, no un servicio
   al alumno. Que eso esté claro con Richi antes de borrar nada.
3. **Escribe el horizonte en la web de la academia.** «Las clases quedan
   disponibles durante el ciclo en curso y el siguiente» es una promesa que se
   puede cumplir con este presupuesto; «para siempre» no lo es sin pagar más.

### Ampliar el disco más tarde: se puede, pero cuesta más que hacerlo hoy

Contabo permite **Extend SSD Storage** desde el panel de cliente sobre un VPS ya
contratado, y el espacio se activa casi sin corte
([Contabo](https://help.contabo.com/en/support/solutions/articles/103000404620-can-i-add-more-ssd-storage-to-my-vps-or-vds-dedicated-server-)).
Lo que no hace el panel es el trabajo de dentro:

```bash
# Tras ampliar en el panel: el disco crece, la partición no.
lsblk                       # ves el disco mayor y la partición igual
growpart /dev/sda 1         # extiende la partición
resize2fs /dev/sda1         # y el sistema de ficheros
df -h /                     # ahora sí
```

Contabo avisa —y con razón— de **hacer copia antes de tocar particiones: un
error ahí se lleva los datos**. Por eso, con la grabación como requisito, la
recomendación es contratar los **400 GB desde el primer día por 3,60 $/mes**:
son 43 $ al año por no tener que redimensionar particiones en un servidor con
clases encima y con las grabaciones de un ciclo dentro.

Y un aviso para el futuro: cambiar a un plan **NVMe** no es un redimensionado,
es una **reinstalación**. Si algún día se migra ahí, se aplica el runbook de
§D.

Esqueleto del archivado, para cuando llegue el momento (no hace falta el primer
día):

```bash
apt install -y rclone
rclone config       # tipo s3, proveedor Other, endpoint y claves del panel de Contabo

# Subir lo publicado hace más de N meses y, solo si la subida verifica, borrarlo
rclone copy /var/bigbluebutton/recording/published rm:grabaciones/2026-I --checksum
rclone check /var/bigbluebutton/recording/published rm:grabaciones/2026-I
# y recién entonces: bbb-record --delete <id>   (saca también la ficha de Moodle)
```

## D. Mover el BBB a otro VPS (y por qué no dockerizarlo)

### Antes de nada: subir de plan no es migrar

Contabo redimensiona CPU y RAM sobre la misma máquina. Pasar de VPS 6 a VPS 8 no
mueve nada: el servidor sigue siendo el mismo, con su disco, su hostname, su
certificado y sus grabaciones. **La migración solo hace falta si cambias de
región, de proveedor o a un plan NVMe** (ese sí reinstala).

### Por qué no dockerizar BBB

Es una idea razonable que en este caso concreto sale cara:

- **BBB no está soportado en contenedores para producción.** Lo único oficial
  con Docker es el entorno de *desarrollo* (`docker-dev`), pensado para compilar
  componentes, no para dar clases.
- **Lo que BBB necesita es justo lo que un contenedor esconde**: systemd, el
  rango UDP 16384–32768 entero, FreeSWITCH hablando con la red del host, nginx
  en 80/443. Acaba ejecutándose con `network_mode: host` y privilegios, o sea,
  con la complejidad de Docker y ninguna de sus ventajas.
- **Existe un proyecto comunitario** (`bbb-docker`), y funciona para quien lo
  mantiene. Pero no es oficial, va por detrás de las versiones, y cuando algo
  falle estarás fuera del camino que documenta BBB y del que conoce cualquiera a
  quien preguntes.
- **No resuelve el problema que quieres resolver.** Mover un BBB ya es barato
  sin Docker, porque el servidor **no guarda estado propio**: guarda una
  instalación reproducible con un script, cuatro ficheros de configuración y las
  grabaciones.

### El runbook de migración (~1 hora, sin reinstalar Moodle)

Lo que hay que llevarse cabe en una lista:

| Qué | Dónde | ¿Está en git? |
|---|---|---|
| La instalación | no se copia: se vuelve a ejecutar el paso 5 | el paso 5 sí |
| Ajustes del servidor | `/etc/bigbluebutton/bbb-web.properties` | **sí**, `scripts/bbb/config/` |
| Ventana de procesado | `/etc/systemd/system/bbb-rap-worker-*.{timer,service}` | **sí**, `scripts/bbb/config/rap-night-window/` |
| Ajustes del cliente | `/etc/bigbluebutton/bbb-html5.yml` | solo si se llega a tocar |
| El secreto | `bbb-conf --secret` | **no, y nunca**: se regenera en el servidor nuevo |
| **Las grabaciones** | `/var/bigbluebutton/recording/published/` (y `status/`) | no: son datos, no configuración |

```bash
# 1. En el servidor NUEVO: pasos 2 a 6 de esta guía, con el MISMO hostname
#    todavía apuntando al viejo (usa la IP para entrar por SSH).

# 2. Copiar configuración y grabaciones (desde el viejo, con el nuevo ya instalado)
rsync -avz /etc/bigbluebutton/bbb-web.properties root@IP_NUEVA:/etc/bigbluebutton/
rsync -avz /etc/bigbluebutton/bbb-html5.yml      root@IP_NUEVA:/etc/bigbluebutton/
rsync -avz /etc/systemd/system/bbb-rap-worker-*  root@IP_NUEVA:/etc/systemd/system/
# En el servidor nuevo, repetir el paso 6: disable del worker + enable de los timers.
rsync -avz --progress /var/bigbluebutton/recording/published/ \
      root@IP_NUEVA:/var/bigbluebutton/recording/published/
rsync -avz /var/bigbluebutton/recording/status/ root@IP_NUEVA:/var/bigbluebutton/recording/status/

# 3. En el nuevo: dueños correctos y reinicio
chown -R bigbluebutton:bigbluebutton /var/bigbluebutton/recording
systemctl daemon-reload && bbb-conf --restart && bbb-conf --check

# 4. Cambiar el registro A/AAAA de clases.richiacademy.com a la IP nueva y
#    esperar a que propague (TTL 600 = 10 min).

# 5. Renovar el certificado en el nuevo host (ya con el DNS apuntando):
certbot --nginx -d clases.richiacademy.com

# 6. En Moodle: pegar el NUEVO secreto (bbb-conf --secret) en la configuración
#    del módulo. La URL no cambia si mantienes el hostname.

# 7. Prueba del paso 9 y, solo entonces, dar de baja el servidor viejo.
```

Hazlo **fuera de horario de clases** y deja el servidor viejo encendido un par
de días: si algo falla, revertir es volver a apuntar el DNS.

## E. Mantenimiento

| Cuándo | Qué |
|---|---|
| Mensual | `apt update && apt upgrade -y && bbb-conf --check`; revisar `df -h` y `free -h` |
| Tras cada clase grande las primeras semanas | `free -h`: si el swap usado crece clase a clase, el VPS 6 está al límite |
| Versión menor de BBB | repetir la orden del paso 5 con las mismas opciones: actualiza en sitio |
| Cada 90 días | el certificado se renueva solo; `certbot renew --dry-run` para confirmarlo |
| Si algo se cuelga | `bbb-conf --restart`; si persiste, `reboot` |
| Grabaciones (semanal el primer mes, luego mensual) | ver «Seguir la cola de grabaciones» aquí abajo. Con los 200 GB contratados son 1–3 meses sin podar, no los 8 que daban 400 GB: la poda no es opcional, ver §C |
| Copias | el sistema no hace falta respaldarlo (se reinstala en 30 min); las grabaciones sí, si importan: `rsync` de `/var/bigbluebutton/published/` a otro sitio |

### ¿Hay alguna clase corriendo ahora?

La actividad BigBlueButton que se ve en un curso de Moodle es **un enlace**, no
una sala encendida: la sala se crea cuando alguien entra y se destruye cuando
sale el último (BBB cierra una sala vacía al minuto; *Finalizar reunión* la
cierra al instante). Así que ver la actividad en el curso no significa que el
servidor esté gastando nada.

Para saber qué hay vivo de verdad, se le pregunta al servidor:

```bash
SECRET=$(bbb-conf --secret | awk -F': *' '/Secret/{print $2; exit}')
CHK=$(printf 'getMeetings%s' "$SECRET" | sha1sum | cut -d' ' -f1)
curl -s "https://clases.richiacademy.com/bigbluebutton/api/getMeetings?checksum=$CHK"
```

`<messageKey>noMeetings</messageKey>` = nada corriendo. Si hay reuniones, las
lista con su nombre y cuánta gente tienen dentro.

**En reposo el servidor nunca está a cero**: los 18 servicios (Java,
PostgreSQL, Redis, FreeSWITCH, nginx) siguen levantados para que una sala abra
en dos segundos. Esa memoria ocupada es normal y no hay que apagar nada. Lo que
importa es que no crezca **durante** las clases hasta tocar el swap — esa sí es
la señal de que el plan se quedó corto (§B).

### Seguir la cola de grabaciones

Con la ventana nocturna del paso 6, una grabación **no se procesa al acabar la
clase**: espera en cola hasta las 23:00. Esta es la foto de por dónde va cada
una — se puede correr cuando se quiera, y no toca nada:

```bash
for d in /var/bigbluebutton/recording/status/*/; do
  printf '%-14s %s\n' "$(basename "$d")" "$(ls "$d" 2>/dev/null | wc -l)"
done
```

Recorre las carpetas de estado que existan en el servidor, así que no se queda
obsoleto si BBB cambia los nombres entre versiones. `processing` es la que
importa: son los trabajos a medias.

Lo mismo en formato legible, con el nombre y la fecha de cada clase:

```bash
bbb-record --list
```

**Cómo leerlo**: lo normal es que por la mañana esté todo en `published` y el
resto a cero. Un número que **crece día a día** en `processing` significa que el
cierre de las 06:00 pilla trabajos a medias: la ventana se quedó corta (ver el
paso 6). Un número que crece en `recorded` o `sanity` sin llegar nunca a
`published` es otra cosa — disco lleno o worker caído, ver §F.

Y el espacio, que es el límite real de este servidor:

```bash
du -sh /var/bigbluebutton/recording/published/ ; df -h /
```

## F. Problemas típicos

| Síntoma | Causa | Arreglo |
|---|---|---|
| El instalador aborta con «hostname does not resolve» | DNS sin propagar | esperar y repetir el paso 3 |
| Aborta con «port 80 in use» | había Apache/nginx | el VPS no estaba limpio: reinstalar Ubuntu 22.04 desde el panel |
| Alumnos de algunos colegios no oyen ni ven la pantalla | su red bloquea UDP | el instalador ya dejó **TURN** local (coturn + haproxy en el 3478), que hace pasar a esos clientes por TCP/443: comprobar con `systemctl status coturn` antes de buscar otra causa |
| «Failed to join» desde Moodle | secreto o URL mal copiados | repetir `bbb-conf --secret`; la URL termina en `/bigbluebutton/` |
| La grabación no aparece al terminar la clase | es lo esperado: el procesado va de noche (paso 6) | `bbb-record --list` para verla en cola; aparece a la mañana siguiente |
| La grabación no aparece **tampoco al día siguiente** | disco lleno, cola parada o los timers mal | `df -h`; `systemctl list-timers 'bbb-rap-worker-*'`; arrancar el worker a mano con `systemctl start bbb-rap-resque-worker` y mirar `journalctl -u bbb-rap-resque-worker` |
| Con 5 clases a la vez se entrecorta la pantalla compartida | CPU o puerto al tope | mirar `htop` y `vnstat`; si es CPU o swap, subir a Cloud VPS 8; si es red, escalonar los horarios |
| El servidor va bien 40 min y luego se arrastra | swap en uso: 12 GB se agotaron | `free -h` durante la clase; si se repite, es la señal de subir de plan |
| Un alumno pide encender su cámara | está bloqueado a propósito (paso 5) | el profesor puede desbloquearlo en esa sala desde *Gestionar usuarios* |

## G. Seguridad mínima

- El secreto solo lo conoce Moodle: nadie puede crear salas desde fuera.
- SSH con clave pública y `PasswordAuthentication no` en
  `/etc/ssh/sshd_config`; `ufw` cierra el resto.
- Sin Greenlight ni demos de la API, la única superficie web pública es la sala.
- Actualizaciones mensuales (§E).
