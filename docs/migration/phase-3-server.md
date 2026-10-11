# Fase 3 — Preparar el servidor: Ubuntu 26.04, PHP 8.4 y MySQL 8.4

Cómo se deja listo el VPS 4 de Contabo (`richiacademy.com`) para que la noche
del corte (Fase 4) sea solo ejecutar, y cómo se ensaya esa noche en el propio
servidor. Plan general: [../migration-plan.md](../migration-plan.md) §6.
Ensayos en local: [phase-2-production-copy.md](phase-2-production-copy.md).

---

## 0. Destino, decisión y orden

### Destino: lo más nuevo que Moodle 5.3 admite

| Pieza | Hoy | Después del corte | Por qué esa y no otra |
|---|---|---|---|
| Ubuntu | 24.04 LTS | **26.04 LTS** | La LTS más reciente (26.04.1 salió en agosto de 2026) |
| PHP | 8.2 (sury) | **8.4**, de `packages.sury.org` | Moodle 5.3 admite 8.3 y 8.4. Ubuntu 26.04 trae 8.5, que 5.3 todavía no admite («en implementación») |
| MySQL | 8.0.46 | **8.4 LTS**, el del propio Ubuntu 26.04 | Moodle 5.3 pide 8.4 como mínimo y no menciona 9.x. Ubuntu 26.04 lo trae de serie: no hace falta el repositorio de Oracle |
| Apache | 2.4.58, `mpm_prefork` + `mod_php` | **2.4 de Ubuntu 26.04, `mpm_event` + PHP-FPM + HTTP/2** | Lo actual: más concurrencia con menos memoria, y HTTP/2 (que `prefork` no admite) |
| Moodle | 4.3.12 | **5.3** | — |

Fuentes: [Moodle 5.3](https://moodledev.io/general/releases/5.3),
[Moodle: PHP](https://docs.moodle.org/503/en/PHP),
[Ubuntu 26.04 LTS](https://documentation.ubuntu.com/release-notes/26.04/summary-for-lts-users/).
El repositorio `packages.sury.org/php` publica 8.2–8.5 para 26.04
(`resolute`), firmado con la clave `15058500 A0235D97 F5D10063 B188E2B6 95BD4743`
(comprobado el 2026-10-10). El PPA de Launchpad no tiene `resolute` (404).

### Decisión (2026-10-10)

Se actualiza **el VPS actual en caliente**. Se descartó montar un VPS nuevo.
Consecuencia: la vuelta atrás es **revertir el snapshot**, y por eso el bloque
C ensaya la noche entera en el servidor, revertir incluido.

### Por qué todo en una noche, y en este orden

- **Moodle 4.3 no puede vivir en Ubuntu 26.04**: rechaza PHP > 8.2, y 26.04
  solo trae MySQL 8.4.
- Así que el salto a **4.5 se hace primero, en 24.04** (PHP 8.2 + MySQL 8.0):
  exactamente como en los cuatro ensayos en verde.
- Después se **actualiza Ubuntu**, que sube MySQL a 8.4 sobre los mismos datos
  (lo mismo que hicieron los ensayos). Moodle 4.5 espera sin tráfico web.
- Por último, **PHP 8.4 con FPM** y el salto a **5.3**.
- Mientras Ubuntu se actualiza, Apache sirve una **página estática de
  mantenimiento**. Si Apache se quedara sin PHP con `/var/www/html` como raíz,
  serviría `config.php` como texto, contraseña incluida.

```
24.04 · PHP 8.2 · MySQL 8.0      4.3 ──upgrade──▶ 4.5
página estática, PHP 8.2 fuera   do-release-upgrade ──▶ 26.04 · MySQL 8.4
26.04 · PHP 8.4-FPM · MySQL 8.4  4.5 ──upgrade──▶ 5.3 ──▶ Apache event + HTTP/2
```

### Bloques

| Bloque | Qué | ¿Corta el sitio? | Duración |
|---|---|---|---|
| **A. Inventario** ✅ | Solo lectura | No | Hecho el 2026-10-10 |
| **B. Preparar** | 24.04 al día, PHP sin uso fuera, árboles 4.5 y 5.3, página de mantenimiento, vhosts escritos sin activar | Solo un reinicio (1–2 min) | 45–60 min |
| **Dos verdes con PHP 8.4** (§4) | Copia 4 y ensayos 5 y 6 en el portátil, con la imagen 8.4 | No | ~1 h |
| **C. Ensayo general** | La noche entera, cronometrada, y **vuelta al snapshot** | Sí, de noche | 3–4 h |
| **Fase 4** (§6) | Lo mismo que C, sin revertir | Sí, de noche | Lo que mida C |

---

## 1. Reglas de la fase

- **Snapshot antes de B y antes de C.** El panel de Contabo puede tener pocos
  huecos: borrar el viejo si hace falta.
- **Todo dentro de `tmux`.** Un SSH cortado mata el `upgrade.php` o el
  `do-release-upgrade` que esté corriendo. El reinicio del final de la
  actualización de Ubuntu cierra `tmux`: se abre otra sesión.
- **Nunca un `.php` bajo la raíz web sin PHP detrás.** Apache lo serviría como
  texto. Antes de apagar PHP 8.2, la página estática (C6).
- **`php` sigue siendo 8.2 hasta la noche.** Moodle 4.3 rechaza 8.3 o más, y el
  cron y `deploy-contabo.sh` llaman a `php` a secas.
- **Nunca arrancar el código 4.5 o 5.3 contra la base de producción antes de
  la noche** (ni `cfg.php`): escribiría cachés de otra versión en
  `moodledata`, que lee el 4.3 en vivo.
- **No pegar contraseñas en el chat.** Los comandos de esta guía no las imprimen.
- **Datos de alumnos:** las copias se borran al terminar, como en la Fase 2.

---

## 2. Bloque A — Inventario (solo lectura) ✅

```bash
ssh root@169.58.171.171
mkdir -p /root/phase3 && chmod 700 /root/phase3
ini82() { PHP_INI_SCAN_DIR=/etc/php/8.2/apache2/conf.d php8.2 -c /etc/php/8.2/apache2/php.ini -r "echo ini_get('$1');"; }
{
echo "== sistema"; lsb_release -ds; nproc; free -h | head -2; df -h / /var /root
echo "== php"; ls /etc/php; update-alternatives --display php | head -3
dpkg -l | awk '/^ii/ && $2 ~ /php8\./ {print $2, $3}'
echo "-- 8.2 apache:"; for k in memory_limit upload_max_filesize post_max_size max_execution_time max_input_time max_input_vars date.timezone opcache.enable; do echo "$k = $(ini82 $k)"; done
echo "-- ini propios:"; find /etc/php/8.2/*/conf.d -type f
echo "== apache"; apache2ctl -v | head -1; a2query -m | grep -E 'php|mpm|rewrite|ssl'
ls -l /etc/apache2/sites-enabled/
grep -RnE 'DocumentRoot|<Directory|AllowOverride|ServerName|ServerAlias' /etc/apache2/sites-enabled/ /etc/apache2/apache2.conf | grep -v ':\s*#'
echo "== mysql"; mysql --version; dpkg -l | awk '/^ii/ && $2 ~ /mysql/ {print $2, $3}'; apt-mark showhold
grep -rnvE '^\s*([#;!]|$)' /etc/mysql/ --exclude=debian.cnf
mysql -e "SELECT user, host, plugin FROM mysql.user"
echo "== moodle"; cd /var/www/html; git log -1 --format='%h %ci %s'; git status --short | head
git config --global --get-all safe.directory
sudo -u www-data php admin/cli/cfg.php --name=release
sudo -u www-data php admin/cli/cfg.php --name=theme
grep -nE 'wwwroot|dataroot|dbcollation|dbtype|dbhost|prefix|setup\.php|routerconfigured|noemailever' config.php
du -sh /var/www/moodledata; ls -ld /var/www/*
echo "== cron"; crontab -u www-data -l 2>&1; grep -rl 'cron.php' /etc/cron.d /etc/crontab 2>/dev/null
echo "== salida a internet"
for v in 4.5 5.3; do curl -sSL -o /dev/null -w "langpack $v: %{http_code}\n" https://download.moodle.org/download.php/direct/langpack/$v/es.zip; done
curl -sS -o /dev/null -w 'repo.mysql.com: %{http_code}\n' https://repo.mysql.com/
curl -sS -o /dev/null -w 'github moodle-2027: %{http_code}\n' https://github.com/JFranPineda/moodle-2027
echo "== herramientas"; which tmux script
echo "== respaldos"; ls -lh /root/moodle-backups/ 2>&1
} 2>&1 | tee /root/phase3/inventory.txt
```

`grep -R` y no `-r`: los vhosts de `sites-enabled` son enlaces simbólicos, y
`grep -r` se los salta sin avisar.

### Resultado (2026-10-10)

| Dato | Encontrado |
|---|---|
| Máquina | Ubuntu 24.04.4, 4 vCPU, 7,8 GB de RAM, 88 GB libres |
| PHP | 8.2 de sury (en uso). **8.3 ya instalado**, del archivo de Ubuntu (`8.3.6-0ubuntu0.24.04.11`), con las mismas extensiones que el 8.2 más `ldap`, y `libapache2-mod-php8.3` apagado. También hay un 8.4 de sury, sin usar. `php` → 8.2 en modo **manual**: instalar paquetes ya no lo cambia |
| `php.ini` | Los valores propios están en `/etc/php/8.2/apache2/conf.d/99-richimath.ini`: `memory_limit` 512M, `upload_max_filesize` 200M, `post_max_size` 210M, `max_execution_time` 300, `max_input_time` 300, `max_input_vars` 5000. (El inventario leyó 0 y -1 en los dos tiempos: el binario CLI de PHP los fuerza, sea cual sea el `.ini`) |
| Apache | 2.4.58, `mpm_prefork` + `php8.2`, `ssl`, `rewrite`. Vhosts `000-default.conf` y `000-default-le-ssl.conf`. `apache2.conf` deja `/var/www/` con `AllowOverride None`. Los vhosts solo traen `DocumentRoot /var/www/html` y la redirección de certbot. El permiso del `.htaccess` está en `conf-enabled/richimath-routes.conf` (`<Directory /var/www/html>` + `AllowOverride FileInfo Indexes`), que también alcanza a `public/` tras el corte |
| MySQL | 8.0.46 de Ubuntu (6 paquetes `mysql-*`). La configuración no usa ninguna opción que 8.4 elimine. Ningún usuario con `mysql_native_password` |
| Moodle | `3c044eae`, árbol limpio, tema `richimath`, `/var/www/html` en `safe.directory`. El `config.php` conserva dos bloques (XAMPP y servidor) que elige un `if`; el `require` de `lib/setup.php` va fuera, en la línea 40 |
| Cron | `* * * * * /usr/bin/php /var/www/html/admin/cli/cron.php` en el crontab de `www-data` |
| Salida a internet | `200` en las cuatro |
| Respaldos | `/root/moodle-backups/` vacío: los de la collation ya están borrados |

---

## 3. Bloque B — Preparar (en 24.04)

De noche: B2 puede reiniciar el servidor. Cada paso termina con una
comprobación; si no da lo esperado, **parar** y pegar la salida.

### B1. Snapshot y sesión

1. Contabo → VPS 4 → **Snapshots** → crear `pre-fase3-<fecha>`. Esperar a que
   termine y **anotar cuánto tarda**.
2. ```bash
   ssh root@169.58.171.171
   tmux new -s fase3          # si se corta:  ssh …  y luego  tmux attach -t fase3
   cd /root/phase3
   ```

### B2. Ubuntu 24.04 al día

`do-release-upgrade` exige el sistema actualizado. Esto sube PHP 8.2, MySQL 8.0
y el kernel a su último parche:

```bash
apt update
apt full-upgrade -y          # si pregunta por un fichero de configuración: conservar el actual
[ -f /var/run/reboot-required ] && echo "HAY QUE REINICIAR"
```

Si hay que reiniciar: `reboot` (1–2 min sin servicio). Luego `ssh`,
`tmux new -s fase3`, y:

```bash
php -v | head -1; mysql --version
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/       # 200
sudo -u www-data php /var/www/html/admin/cli/cfg.php --name=release       # 4.3.12
```

### B3. Quitar el PHP que no se usa

El 8.3 de Ubuntu y el 8.4 de sury para 24.04 no los usa nadie. Además
estorbarían en la actualización: el 8.4 se instala la noche del corte, ya
compilado para 26.04.

```bash
UNUSED=$(dpkg -l | awk '/^ii/ && ($2 ~ /^(php8\.[345]|libapache2-mod-php8\.[345])/ || ($2 ~ /^php(-[a-z0-9.]+)?$/ && $2 != "php-common")) {print $2}')
echo $UNUSED
apt-get -s purge $UNUSED | awk '/^(Purg|Remv) /{print $2}' | tr '\n' ' '; echo     # simulación
```

La simulación **no** puede incluir `php8.2-*`, `libapache2-mod-php8.2` ni
`php-common`. Si los incluye, parar. Si no:

```bash
apt-get purge -y $UNUSED
dpkg -l | awk '/^rc/ && $2 ~ /php/ {print $2}' | xargs -r dpkg --purge     # restos de configuración
ls /etc/php
php -v | head -1; a2query -m | grep php                                   # 8.2 · solo php8.2
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/       # 200
```

### B4. Comprobar que el salto a 26.04 está disponible (sin hacerlo)

```bash
grep '^Prompt' /etc/update-manager/release-upgrades      # Prompt=lts
do-release-upgrade -c                                     # «New release '26.04.x LTS' available»: solo mira
ls /etc/apt/sources.list.d/                               # el PPA de sury (ondrej-…)
ufw status | head -1
```

Si `do-release-upgrade -c` no ofrece 26.04, parar y pegar la salida.

### B5. Guardar lo que la noche necesita

PHP 8.2 se desinstala esa noche, y con él su `99-richimath.ini`:

```bash
mkdir -p /root/phase3/php
cp -p /etc/php/8.2/apache2/conf.d/99-richimath.ini /root/phase3/php/
cat /root/phase3/php/99-richimath.ini
```

### B6. Árbol 4.5 para el salto intermedio

Es el de los ensayos: `github/moodle-2026` y los 6 plugins de 4.3, sacados del
propio clon de producción, del commit desplegado.

En el **portátil**:

```bash
rsync -az --delete --exclude=.git ~/Documentos/github/moodle-2026/ root@169.58.171.171:/var/www/moodle45/
```

En el **servidor**:

```bash
git -C /var/www/html archive HEAD local/richimath theme/richimath theme/rmprimaria \
    theme/rmsecundaria theme/rmpreu theme/rmuniversidad | tar -x -C /var/www/moodle45
cp -p /var/www/html/config.php /var/www/moodle45/config.php
chown -R www-data:www-data /var/www/moodle45
grep '^\$release' /var/www/moodle45/version.php      # 4.5.14+ (Build: 20261002)
ls /var/www/moodle45/local/richimath/version.php /var/www/moodle45/theme/rmpreu/version.php
php8.2 -l /var/www/moodle45/config.php
```

### B7. Árbol 5.3 (clon de `moodle-2027`)

Sube antes tus commits: el clon tiene que traer lo ensayado.
`.gitignore` solo excluye `config.php`, así que el clon es el mismo árbol que
montan los ensayos.

```bash
git clone https://github.com/JFranPineda/moodle-2027.git /var/www/moodle53
git -C /var/www/moodle53 log -1 --format='%h %s'      # el mismo commit que origin/main en el portátil
cp -p /var/www/html/config.php /var/www/moodle53/config.php
sed -i '/lib\/setup\.php/i $CFG->routerconfigured = true;' /var/www/moodle53/config.php
php8.2 -l /var/www/moodle53/config.php
diff /var/www/html/config.php /var/www/moodle53/config.php    # solo la línea routerconfigured
chown -R www-data:www-data /var/www/moodle53
```

El `git log` va antes del `chown`: después, git se niega a operar como `root`
en un árbol de otro dueño. Tras el corte el árbol vive en `/var/www/html`, que
ya está en `safe.directory`.

### B8. Página de mantenimiento y vhosts, escritos sin activar

Primero, cómo están hoy (el inventario no los mostró):

```bash
grep -RnE 'DocumentRoot|<Directory|AllowOverride|Rewrite|ServerName|ServerAlias' /etc/apache2/sites-enabled/
```

Después, la página estática (está en el repo, `assets/maintenance/`) y tres
juegos de vhosts:
- `apache43`: copia de los de hoy;
- `apachemaint`: la página estática, con 503 para todo;
- `apache53`: la raíz en `public/`.

Los ficheros conservan su nombre, así que certbot no nota nada.

```bash
mkdir -p /var/www/maintenance
cp /var/www/moodle53/assets/maintenance/index.html /var/www/maintenance/
mkdir -p /root/phase3/apache43 /root/phase3/apachemaint /root/phase3/apache53
cd /etc/apache2/sites-enabled
for f in *.conf; do
  cp -L "$f" /root/phase3/apache43/
  awk '/^[[:space:]]*DocumentRoot[[:space:]]/ {
         print "    DocumentRoot /var/www/maintenance"
         print "    # Static maintenance page while the OS is upgraded: no PHP behind this root."
         print "    <Directory /var/www/maintenance>"
         print "        Require all granted"
         print "    </Directory>"
         print "    RewriteEngine On"
         print "    RewriteRule ^/index\\.html$ - [END]"
         print "    RewriteRule ^ - [R=503,L]"
         print "    ErrorDocument 503 /index.html"
         next }
       { print }' "$f" > /root/phase3/apachemaint/"$f"
  sed -E 's#^([[:space:]]*DocumentRoot[[:space:]]+)"?/var/www/html/?"?[[:space:]]*$#\1/var/www/html/public#;
          s#<Directory[[:space:]]+"?/var/www/html/?"?[[:space:]]*>#<Directory /var/www/html/public>#' \
      "$f" > /root/phase3/apache53/"$f"
  echo "== $f (5.3)"; diff "$f" /root/phase3/apache53/"$f"
done
printf 'Protocols h2 http/1.1\n' > /root/phase3/richimath-http2.conf
```

Pegar el `grep` y los `diff`.
- **Vhosts de 5.3:** solo deben cambiar las líneas `DocumentRoot` (y
  `<Directory>` si el vhost la tiene).
- **Rutas limpias y router:** necesitan `mod_rewrite` y
  `AllowOverride FileInfo Indexes` sobre `public/`. En este servidor los da
  `conf-enabled/richimath-routes.conf` sobre `/var/www/html`, que alcanza a
  `public/` (comprobado el 2026-10-10: `/students` → 200).

**Probado en un Apache de Docker (2026-10-10):**
- **Vhost de mantenimiento:** `https://…/`, `/login` y `/config.php`, por
  dominio y por IP, dan 503 con la página, y `/index.html` da 200.
  `config.php` no se sirve nunca.
- **Vhost de 5.3:** sirve `public/`, y `/config.php` da 404.
- **La regla `[END]`:** sin ella, la redirección a HTTPS de certbot en el vhost
  `:80` se comía la página de error y devolvía un 301.

### B9. Cierre del bloque B

```bash
php -v | head -1; a2query -m | grep -E 'php|mpm'
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/
crontab -u www-data -l
ls -ld /var/www/*; ls /root/phase3 /root/phase3/*/
```

Producción sigue igual: 4.3, PHP 8.2, MySQL 8.0, cron intacto. Pegar las
salidas de B2–B9.

---

## 4. Antes del bloque C: dos verdes con PHP 8.4 en el portátil

Los cuatro ensayos de la Fase 2 subieron a 5.3 con PHP 8.3. Desde el
2026-10-10, `rehearse-upgrade.sh` y el entorno `:8083` usan **PHP 8.4**. Lo
comprobado hasta ahora:
- nuestros 75 ficheros PHP pasan `php -l` de 8.4 con todos los avisos
  activados;
- `:8083` en 8.4 responde sin un solo aviso, como admin a 1440 y como alumno a
  390.

Falta la prueba con datos reales: **dos verdes seguidos**.

1. **Copia 4** de producción, con el procedimiento de siempre
   ([phase-2-production-copy.md](phase-2-production-copy.md) §2).
2. Ensayo 5:
   ```bash
   cd ~/Documentos/github/moodle-2027
   scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy 2>&1 | tee ~/richimath-prod-copy/run5.out
   scripts/rehearsal/acceptance.sh ~/richimath-prod-copy --bbb 2>&1 | tee ~/richimath-prod-copy/acceptance5.out
   ```
   Se espera la línea `switch to PHP 8.4 + MySQL 8.4`, la sección E toda en
   OK y `52 passed, 0 failed`.
3. Ensayo 6: los mismos dos comandos (con `run6.out` y `acceptance6.out`). El
   script empieza de cero cada vez.

La copia 4 sirve además de copia de seguridad reciente fuera del VPS. Se borra
al cerrar el bloque C.

---

## 5. Bloque C — Ensayo general en el servidor (y vuelta atrás)

Es el guion de la noche del corte, entero. La única diferencia es el final: en
vez de abrir el sitio en 5.3, **se revierte el snapshot**. Necesita:
- una noche sin clases acordada con Richi, con unas 3–4 h de servicio cortado;
- un hueco libre de snapshot en Contabo;
- el portátil a mano.

Los alumnos no pierden nada: el snapshot se toma con el sitio ya en
mantenimiento.

### C1. Sesión, registro y cronómetro

```bash
ssh root@169.58.171.171
tmux new -s ensayo                       # si se corta:  tmux attach -t ensayo
script -a /root/phase3/server-rehearsal.log
s() { local n=$1 t=$SECONDS; shift; "$@"; local rc=$?; printf '%-30s %5ds rc=%s\n' "$n" $((SECONDS - t)) $rc | tee -a /root/phase3/times.log; return $rc; }
date | tee -a /root/phase3/times.log
```

### C2. Mantenimiento y cron parado

```bash
cd /var/www/html
s "maintenance on" sudo -u www-data php admin/cli/maintenance.php --enable
crontab -u www-data -l > /root/phase3/www-data.crontab && crontab -u www-data -r
while pgrep -u www-data -f admin/cli/cron.php >/dev/null; do sleep 5; done; echo "cron parado"
```

### C3. Copia fuera del VPS

```bash
mkdir -p /root/moodle-copy && chmod 700 /root/moodle-copy && cd /root/moodle-copy
STAMP=$(date +%F-%H%M)
s "dump database" bash -c "mysqldump -u root --single-transaction --quick --default-character-set=utf8mb4 \
    --no-tablespaces moodle | gzip > moodle-db-$STAMP.sql.gz"
s "tar moodledata" tar -C /var/www -czf moodledata-$STAMP.tar.gz \
    --exclude=moodledata/cache --exclude=moodledata/localcache --exclude=moodledata/sessions \
    --exclude=moodledata/temp --exclude=moodledata/trashdir --exclude=moodledata/lock moodledata
sha256sum *.gz > SHA256SUMS
```

En el **portátil**, en otra terminal:

```bash
mkdir -p ~/richimath-prod-copy/night && chmod 700 ~/richimath-prod-copy ~/richimath-prod-copy/night
rsync -avP root@169.58.171.171:/root/moodle-copy/ ~/richimath-prod-copy/night/
cd ~/richimath-prod-copy/night && sha256sum -c SHA256SUMS
ssh root@169.58.171.171 'rm -rf /root/moodle-copy'      # solo después del OK
```

### C4. Snapshot

Contabo → Snapshots → crear `ensayo-53-<fecha>`. Esperar a que termine.

```bash
echo "snapshot: <minutos> min" | tee -a /root/phase3/times.log
```

### C5. Salto 4.3 → 4.5 (24.04 · PHP 8.2 · MySQL 8.0, como en los ensayos)

```bash
s "upgrade 4.3 → 4.5" sudo -u www-data php8.2 /var/www/moodle45/admin/cli/upgrade.php --non-interactive
```

Tiene que terminar con *«…finalizada con éxito»*, con el paquete `es` de 4.5
instalado.

### C6. Página estática, y PHP 8.2 fuera

```bash
T=$SECONDS
cp /root/phase3/apachemaint/*.conf /etc/apache2/sites-enabled/
a2dismod -q php8.2 && a2dismod -q mpm_prefork && a2enmod -q mpm_event
apache2ctl configtest && systemctl restart apache2
curl -s -o /dev/null -w '%{http_code}\n' https://richiacademy.com/              # 503
curl -s -o /dev/null -w '%{http_code}\n' http://169.58.171.171/config.php       # 503: nunca el fichero
apt-get purge -y $(dpkg -l | awk '/^ii/ && $2 ~ /^(php8\.2|libapache2-mod-php8\.2)/ {print $2}')
rm -f /etc/apt/sources.list.d/ondrej-*          # el PPA de 24.04; el de 26.04 se añade en C9
apt update
echo "static page, PHP 8.2 out $((SECONDS - T))s" | tee -a /root/phase3/times.log
```

Si `config.php` diera otra cosa que 503, **parar**: el vhost no es el de
mantenimiento.

### C7. Ubuntu 24.04 → 26.04

```bash
date +%T | tee -a /root/phase3/times.log      # inicio
do-release-upgrade
```

| Pregunta | Respuesta |
|---|---|
| Fuentes de terceros desactivadas / ¿continuar? | `y` |
| ¿Arrancar un `sshd` adicional en el puerto 1022? | `y` |
| Resumen de paquetes: ¿empezar la actualización? | `y` |
| ¿Reiniciar servicios sin preguntar? | Sí |
| Fichero de configuración modificado (`mysqld.cnf`, `apache2.conf`, `sshd_config`…) | **Conservar el instalado** (`N`). Anotar cuál fue |
| ¿Quitar paquetes obsoletos? | `N`: se limpian a mano tras el corte |
| ¿Reiniciar ahora? | `y` |

El reinicio cierra `script` y `tmux`; lo escrito en los registros se conserva.

### C8. Tras el reinicio: Ubuntu 26.04 y MySQL 8.4

```bash
ssh root@169.58.171.171
tmux new -s ensayo2
script -a /root/phase3/server-rehearsal.log
s() { local n=$1 t=$SECONDS; shift; "$@"; local rc=$?; printf '%-30s %5ds rc=%s\n' "$n" $((SECONDS - t)) $rc | tee -a /root/phase3/times.log; return $rc; }
date +%T | tee -a /root/phase3/times.log      # fin del salto de Ubuntu
lsb_release -ds                                # Ubuntu 26.04.x LTS
systemctl is-active mysql apache2              # active · active
mysql -e "SELECT VERSION()"                    # 8.4.x
mysql -e "SELECT user, plugin FROM mysql.user WHERE user IN ('root', 'moodleuser')"
tail -20 /var/log/mysql/error.log
curl -s -o /dev/null -w '%{http_code}\n' https://richiacademy.com/       # 503 (página estática)
```

Si MySQL no arranca: pegar `error.log` y aplicar la regla de los 30 minutos.

### C9. PHP 8.4 con FPM

```bash
curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
dpkg -i /tmp/debsuryorg-archive-keyring.deb
echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ resolute main" \
    > /etc/apt/sources.list.d/php.list
apt update                                          # sin errores de firma
apt-cache policy php8.4-fpm | head -3               # candidato 8.4.x…ubuntu26.04, de packages.sury.org
s "install PHP 8.4" apt-get install -y php8.4-fpm php8.4-cli php8.4-curl php8.4-gd php8.4-intl \
    php8.4-mbstring php8.4-mysql php8.4-opcache php8.4-readline php8.4-soap php8.4-xml php8.4-zip
for e in iconv mbstring curl openssl ctype zip zlib gd simplexml spl pcre dom xml xmlreader \
         intl json hash fileinfo sodium filter tokenizer soap exif mysqli; do
  php8.4 -m | grep -qix "$e" || echo "FALTA $e"
done; echo "fin de la lista"
```

Valores de Richi Math y tamaño del pool. 30 procesos de ~100 MB son ~3 GB de
los 7,8 GB, y dejan sitio a MySQL:

```bash
cp /root/phase3/php/99-richimath.ini /etc/php/8.4/fpm/conf.d/
P=/etc/php/8.4/fpm/pool.d/www.conf
sed -i -E 's/^pm\.max_children = .*/pm.max_children = 30/; s/^pm\.start_servers = .*/pm.start_servers = 6/;
           s/^pm\.min_spare_servers = .*/pm.min_spare_servers = 4/; s/^pm\.max_spare_servers = .*/pm.max_spare_servers = 10/;
           s/^;?pm\.max_requests = .*/pm.max_requests = 500/' $P
grep -E '^pm' $P
php-fpm8.4 -t && systemctl restart php8.4-fpm
php-fpm8.4 -i | grep -E '^(memory_limit|upload_max_filesize|post_max_size|max_input_vars|max_execution_time) '
update-alternatives --set php /usr/bin/php8.4; php -v | head -1
```

Los valores tienen que ser los de `99-richimath.ini`: 512M, 200M, 210M, 5000 y 300.

### C10. Salto 4.5 → 5.3

```bash
mv /var/www/html /var/www/moodle43 && mv /var/www/moodle53 /var/www/html
# Server rehearsal only: this site is about to be reverted, so cron must not mail anyone.
sed -i '/lib\/setup\.php/i $CFG->noemailever = true;' /var/www/html/config.php
cd /var/www/html
s "upgrade 4.5 → 5.3" sudo -u www-data php admin/cli/upgrade.php --non-interactive
```

`adhoc_task.php` **se niega a correr en mantenimiento por CLI**: antes, el
mantenimiento «a medias» (solo admins). Primero `--enableold` y después borrar
el fichero, para que no haya un instante abierto:

```bash
sudo -u www-data php admin/cli/maintenance.php --enableold
rm /var/www/moodledata/climaintenance.html
s "adhoc tasks (CSS, qbank)" sudo -u www-data php admin/cli/adhoc_task.php --execute
s "Spanish customisations" sudo -u www-data php public/admin/tool/customlang/cli/import.php \
    --lang=es --source=/var/www/html/assets/customlang/es --checkin
```

No purgar cachés después: el CSS se compilaría dos veces. Apache sigue
sirviendo la página estática todo este paso.

### C11. Apache: FPM, HTTP/2 y el vhost de 5.3

```bash
T=$SECONDS
a2enmod -q proxy_fcgi setenvif http2 && a2enconf -q php8.4-fpm
cp /root/phase3/richimath-http2.conf /etc/apache2/conf-available/ && a2enconf -q richimath-http2
cp /root/phase3/apache53/*.conf /etc/apache2/sites-enabled/
apache2ctl configtest && systemctl reload apache2
echo "Apache → 5.3 $((SECONDS - T))s" | tee -a /root/phase3/times.log
curl -s -o /dev/null -w '%{http_code} HTTP/%{http_version}\n' https://richiacademy.com/login   # 200 HTTP/2
curl -s -o /dev/null -w '%{http_code}\n' https://richiacademy.com/config.php                    # 404
```

### C12. Comprobaciones técnicas

```bash
sudo -u www-data php admin/cli/checks.php                    # sin críticos; el aviso del cron es esperado
sudo -u www-data php admin/cli/check_database_schema.php      # Database structure is ok.
sudo -u www-data php scripts/check-db-settings.php            # todo OK/INFO
```

`checks.php` hace aquí la prueba que Docker no podía: la del router, desde el
servidor contra `https://richiacademy.com`.

### C13. Pruebas de humo como admin (navegador)

Abrir `https://richiacademy.com/login` y entrar con la cuenta de administrador
(`richi85`). Sin sesión, el sitio muestra el aviso de mantenimiento. Las
pruebas son las de la tabla del plan
([../migration-plan.md](../migration-plan.md) §7), salvo la 1 y la 8: esas
necesitan el sitio abierto y quedan para la noche del corte.

- **Prueba 2:** login correcto. Para el login fallido, una ventana privada con
  un usuario inventado: no debe salir de `/login`.
- **Pruebas 3–7:** como admin.
- **Prueba 9:** `sudo -u www-data php admin/cli/cron.php` una vez. Con
  `noemailever` no sale ningún correo.

```bash
echo "smoke: <OK / qué falló>" | tee -a /root/phase3/times.log
```

### C14. Sacar los registros ANTES de revertir

```bash
exit                                  # cierra script; sigues en tmux
cat /root/phase3/times.log
```

En el **portátil**:

```bash
scp root@169.58.171.171:'/root/phase3/{times.log,server-rehearsal.log}' ~/richimath-prod-copy/night/
```

### C15. Revertir el snapshot y abrir 4.3

Contabo → Snapshots → `ensayo-53-<fecha>` → **Revert**. **Anotar cuánto
tarda** hasta que el SSH vuelve a responder. Después:

```bash
ssh root@169.58.171.171
lsb_release -ds; php -v | head -1; mysql --version      # 24.04 · 8.2 · 8.0
ls -d /var/www/*                                         # html (4.3), maintenance, moodle45, moodle53
cd /var/www/html && sudo -u www-data php admin/cli/cfg.php --name=release    # 4.3.12
crontab -u www-data /root/phase3/www-data.crontab && crontab -u www-data -l
sudo -u www-data php admin/cli/maintenance.php --disable
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/            # 200
```

Abrir el sitio en una ventana privada (portada y login de 4.3) y avisar a
Richi. Con los tiempos de `times.log` se fija la ventana del corte.

---

## 6. La noche del corte (Fase 4)

Lo mismo que C1–C13, **sin** la línea de `noemailever` de C10. Después:

```bash
sudo -u www-data php /var/www/html/admin/cli/maintenance.php --disable
crontab -u www-data /root/phase3/www-data.crontab      # /usr/bin/php ya es 8.4
certbot renew --dry-run
```

- Pruebas de humo 1 (portada sin sesión) y 8 (como alumno).
- Una prueba de correo: Administración → Servidor → Correo → Probar.
- **FUN-23:** el primer despliegue sobre 5.3, con
  `bash /var/www/html/scripts/deploy-contabo.sh`.
- **Días después** (Fase 5):
  - `apt autoremove --purge`;
  - borrar `/var/www/moodle43`, `/var/www/moodle45` y `/var/www/maintenance`;
  - borrar la copia del portátil.

---

## 7. Trampas sabidas antes de empezar

1. **`adhoc_task.php` no corre en mantenimiento por CLI**, y ese mantenimiento
   tampoco deja entrar al admin por la web: hay que pasar al modo a medias
   (C10). El ensayo en Docker no lo veía porque no activaba el mantenimiento.
2. **Apache sin PHP con un `.php` bajo la raíz web sirve el código como
   texto**, `config.php` incluido. Por eso, página estática antes de apagar
   PHP (C6).
3. **La redirección a HTTPS de certbot se come la página de error** en el vhost
   `:80`. La regla `[END]` para `/index.html` lo evita (B8, probado).
4. **4.3 no puede vivir en 26.04** (PHP ≤ 8.2, y 26.04 solo trae MySQL 8.4):
   el salto a 4.5 va antes que Ubuntu.
5. **Ubuntu 26.04 trae PHP 8.5, y Moodle 5.3 no lo admite**: 8.4 de
   `packages.sury.org`. El PPA de Launchpad no tiene `resolute`.
6. **Arrancar el código nuevo contra la base de producción** escribe cachés de
   otra versión en `moodledata`: nada de CLI desde `moodle45`/`moodle53` antes
   de la noche.
7. **Un SSH cortado mata lo que esté corriendo**: siempre dentro de `tmux`. El
   reinicio de C7 cierra la sesión: se abre otra.
8. **Revertir el snapshot borra los registros de la noche**: sacarlos antes
   (C14).
9. **`git` como `root` en un árbol de `www-data`** pide `safe.directory` (B7).
10. **`grep -r` se salta los enlaces simbólicos** que encuentra (los vhosts de
    `sites-enabled`): usar `grep -R`.
11. **El binario CLI de PHP fuerza `max_execution_time = 0` y `max_input_time = -1`**,
    aunque se le pase el `php.ini` de Apache: para leer esos dos valores, el
    fichero (`99-richimath.ini`) o `php-fpm8.4 -i`.

---

## 8. Registro

| Paso | Portátil (ensayo 4) | Servidor (bloque C) |
|---|---|---|
| Snapshot (crear) | — | |
| Copia: volcado + `tar` | 21–27 s + 14 s (Fase 2) | |
| Upgrade 4.3 → 4.5 | 159 s | |
| Página estática y PHP 8.2 fuera | — | |
| Ubuntu 24.04 → 26.04 (incluye MySQL 8.4 y reinicio) | 20 s (solo MySQL) | |
| PHP 8.4 (instalar y configurar) | — | |
| Upgrade 4.5 → 5.3 | 164 s | |
| Tareas adhoc (CSS, `mod_qbank`) | 229 s | |
| Personalización de idioma | 12 s | |
| Apache → 5.3 | — | |
| Pruebas de humo | — | |
| Snapshot (revertir) | — | |

Con la columna del servidor se fijan los `T+?` de la plantilla del corte
([../migration-plan.md](../migration-plan.md) §7), con margen × 1,5.
