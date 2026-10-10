# Fase 3 — Preparar el servidor

Cómo se deja listo el VPS 4 de Contabo (`richiacademy.com`) para que la noche
del corte (Fase 4) sea solo ejecutar, y cómo se miden en el propio servidor
los tiempos que fijan la ventana. Plan general:
[../migration-plan.md](../migration-plan.md) §6. Ensayos en local:
[phase-2-production-copy.md](phase-2-production-copy.md).

---

## 0. Qué se hace y qué no

| Bloque | Qué | ¿Corta el sitio? | Duración |
|---|---|---|---|
| **A. Inventario** | Solo lectura: versiones, Apache, PHP, MySQL, cron, salida a internet | No | 5 min |
| **B. Preparar sin corte** | PHP 8.3 instalado **apagado**; repositorio de MySQL 8.4 añadido **retenido** y comprobador oficial; árboles 4.5 y 5.3 preparados al lado; vhost de 5.3 escrito sin activar | No (Apache se recarga una vez) | 45–60 min |
| **C. Ensayo general en el servidor** | La secuencia exacta de la noche del corte, cronometrada, y **vuelta al snapshot** al final | Sí: unas 2 h de mantenimiento, de noche | 2–2,5 h |

**Lo que NO se hace en esta fase:** dejar producción en 5.3. Al terminar el
bloque C el sitio vuelve a 4.3, PHP 8.2 y MySQL 8.0, como si nada.

**Por qué el bloque C:** el ensayo en Docker no prueba cuatro cosas que solo
existen en el servidor:
1. el cambio de paquetes de MySQL (8.0 de Ubuntu → 8.4 de Oracle) sobre la
   configuración real de `/etc/mysql`;
2. la comprobación del router de 5.3 por HTTPS contra el dominio real;
3. los tiempos con la CPU del VPS;
4. **cuánto tarda revertir un snapshot de Contabo**, que es la vuelta atrás de
   la regla de los 30 minutos.

Si Richi no concede esa noche, se puede saltar el bloque C: la Fase 4 asume
entonces esos cuatro riesgos con el snapshot como red, y la ventana se calcula
con los tiempos del portátil × 2.

**Orden:** A → (pegar la salida para revisarla) → B → (pegar las salidas) → C.

---

## 1. Reglas de la fase

- **Snapshot antes de B y antes de C.** El panel de Contabo puede tener un
  número limitado de snapshots: borrar el viejo si no queda hueco.
- **Todo dentro de `tmux`.** Si el SSH se corta a mitad de un `upgrade.php`,
  el proceso muere con él. Con `tmux` se reengancha con `tmux attach`.
- **Nunca arrancar el código 4.5 o 5.3 contra la base de producción antes de
  la noche** (ni siquiera `cfg.php`). Escribiría cachés de otra versión
  (`moodledata/cache`, `muc/`) que lee el 4.3 en vivo. En B solo se copian
  ficheros y se comprueba sintaxis.
- **`php` tiene que seguir siendo 8.2** hasta el corte. Moodle 4.3 rechaza PHP
  8.3, y el cron y `deploy-contabo.sh` llaman a `php` a secas.
- **No pegar contraseñas en el chat.** Los comandos de esta guía no las
  imprimen; `debian.cnf` y las líneas `dbpass` quedan fuera a propósito.
- **Datos de alumnos:** las copias de la base y de `moodledata` del bloque C se
  borran al terminar, como en la Fase 2.

---

## 2. Bloque A — Inventario (solo lectura)

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
grep -rnE 'DocumentRoot|<Directory|AllowOverride|ServerName|ServerAlias' /etc/apache2/sites-enabled/ /etc/apache2/apache2.conf | grep -v ':\s*#'
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

Pegar la salida entera. Lo que se espera:

| Dato | Esperado | Si no |
|---|---|---|
| PHP | `php8.2-*` y `libapache2-mod-php8.2`; `php` → 8.2; sin `php8.3-*` | Si ya hay `php8.3-*` (Ubuntu 24.04 trae 8.3 de serie), se ajusta B2 |
| Apache | `php8.2` y `mpm_prefork` activos; `rewrite` y `ssl` activos | Si sale `php8.2-fpm`/`proxy_fcgi`, el cambio de PHP es otro: se ajusta B y C |
| `max_input_vars` | ≥ 5000 | B3 lo sube en 8.3 de todas formas |
| MySQL | 8.0.x de Ubuntu; `moodleuser` con `caching_sha2_password`; `root` con `auth_socket` | — |
| Config de MySQL | Sin opciones que 8.4 elimina (`default_authentication_plugin`, `expire_logs_days`…) | El comprobador de B4 lo confirma |
| Moodle | `3c044eae`, árbol limpio; tema `richimath`; `/var/www/html` en `safe.directory` | — |
| Cron | Una línea de `www-data` con `php …/admin/cli/cron.php` | Si está en `/etc/cron.d`, se ajusta C |
| Salida a internet | `200` en las cuatro | Sin `download.moodle.org`, los textos nuevos de 4.4+ saldrían en inglés |
| Disco | ≥ 3 GB libres en `/var` | — |

---

## 3. Bloque B — Preparar sin corte

De noche (B2 recarga Apache una vez). Cada paso termina con una comprobación:
si no da lo esperado, **parar** y pegar la salida.

### B1. Snapshot y sesión

1. Panel de Contabo → VPS 4 → **Snapshots** → crear `pre-fase3-<fecha>`.
   Esperar a que termine. **Anotar cuántos minutos tarda.**
2. En el servidor:
   ```bash
   ssh root@169.58.171.171
   tmux new -s fase3          # si se corta: ssh … y luego  tmux attach -t fase3
   cd /root/phase3
   ```

### B2. PHP 8.3 al lado del 8.2

Mismos paquetes que tiene el 8.2, con el nombre del 8.3:

```bash
apt update
PKGS=$(dpkg -l | awk '/^ii/ && $2 ~ /php8\.2/ {print $2}' | sed 's/8\.2/8.3/')
echo $PKGS
apt install -y $PKGS
```

**Justo después**, devolver `php` al 8.2 (instalar 8.3 lo cambia solo):

```bash
update-alternatives --set php /usr/bin/php8.2
php -v | head -1                          # PHP 8.2.x
a2query -m | grep php                     # solo php8.2
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/   # 200
sudo -u www-data php /var/www/html/admin/cli/cfg.php --name=release   # 4.3.12
```

Si `a2query` muestra `php8.3` activo:
`a2dismod php8.3 && a2enmod php8.2 && systemctl restart apache2`.

Extensiones que exige 5.3 (`public/admin/environment.xml`):

```bash
for e in iconv mbstring curl openssl ctype zip zlib gd simplexml spl pcre dom xml xmlreader \
         intl json hash fileinfo sodium filter tokenizer soap exif mysqli; do
  php8.3 -m | grep -qix "$e" || echo "FALTA $e"
done; echo "fin de la lista"
```

Si falta alguna: `apt install -y php8.3-<nombre>` y repetir.

### B3. `php.ini` del 8.3 con los valores del 8.2

```bash
ini82() { PHP_INI_SCAN_DIR=/etc/php/8.2/apache2/conf.d php8.2 -c /etc/php/8.2/apache2/php.ini -r "echo ini_get('$1');"; }
ini83() { PHP_INI_SCAN_DIR=/etc/php/8.3/apache2/conf.d php8.3 -c /etc/php/8.3/apache2/php.ini -r "echo ini_get('$1');"; }
INI=/etc/php/8.3/apache2/conf.d/99-moodle.ini
{
  echo '; Moodle 5.3: same values as PHP 8.2 (apache2), max_input_vars >= 5000.'
  for k in memory_limit upload_max_filesize post_max_size max_execution_time max_input_time date.timezone; do
    v=$(ini82 $k); [ -n "$v" ] && echo "$k = $v"
  done
  v=$(ini82 max_input_vars); echo "max_input_vars = $(( v > 5000 ? v : 5000 ))"
} > $INI
cat $INI
for k in memory_limit upload_max_filesize post_max_size max_execution_time max_input_vars; do
  echo "$k  8.2=$(ini82 $k)  8.3=$(ini83 $k)"
done
```

Las dos columnas tienen que coincidir, salvo `max_input_vars`, que en 8.3 es
≥ 5000. El módulo 8.3 está apagado: este fichero no afecta al sitio hasta el
corte.

### B4. MySQL 8.4: repositorio retenido y comprobador oficial

**Primero retener** los paquetes de MySQL instalados. Así, con el repositorio
de Oracle añadido, ningún `apt upgrade` sube MySQL antes de la noche:

```bash
apt-mark hold $(dpkg -l | awk '/^ii/ && $2 ~ /^mysql-/ {print $2}')
apt-mark showhold
```

Repositorio oficial, rama 8.4 LTS:

```bash
cd /root/phase3
F=$(curl -s https://dev.mysql.com/downloads/repo/apt/ | grep -o 'mysql-apt-config_[0-9.]*-[0-9]*_all\.deb' | head -1)
echo "$F"                          # mysql-apt-config_0.8.xx-1_all.deb (si sale vacío: copiar el nombre de esa página)
wget -q "https://dev.mysql.com/get/$F"
echo 'mysql-apt-config mysql-apt-config/select-server select mysql-8.4-lts' | debconf-set-selections
DEBIAN_FRONTEND=noninteractive dpkg -i "$F"
cat /etc/apt/sources.list.d/mysql.list        # tiene que decir mysql-8.4-lts
apt update                                     # sin errores de firma (EXPKEYSIG / NO_PUBKEY)
apt-cache policy mysql-server | head -4        # Instalado: 8.0.x (Ubuntu) · Candidato: 8.4.x (repo.mysql.com)
apt-get -s upgrade | grep -i mysql             # vacío o «retenidos»: nada de MySQL sube solo
apt-get -s install mysql-server 2>&1 | tail -25   # SOLO simulación: lo que la noche quitará e instalará
```

Si `mysql.list` no dice `mysql-8.4-lts`: `dpkg-reconfigure mysql-apt-config`
y elegir *MySQL Server & Cluster* → `mysql-8.4-lts`.

**Comprobador oficial de compatibilidad 8.0 → 8.4** (MySQL Shell, solo
lectura):

```bash
apt install -y mysql-shell
T=$(apt-cache policy mysql-community-server | awk '/Candid/{print $2}' | cut -d- -f1); echo "$T"
mysqlsh --socket=/var/run/mysqld/mysqld.sock --user=root --no-password -- \
  util check-for-server-upgrade --target-version="$T" \
  --config-path=/etc/mysql/mysql.conf.d/mysqld.cnf 2>&1 | tee /root/phase3/mysql-upgrade-check.txt
```

Tiene que terminar con *«No fatal errors were found that would prevent an
upgrade»*. Los avisos se revisan uno a uno. Si `apt install mysql-shell`
choca con los paquetes retenidos, pegar el error: se usa el tarball de MySQL
Shell, que no instala nada.

### B5. Árbol 4.5 para el salto intermedio

Es el mismo que el del ensayo: `github/moodle-2026` y los 6 plugins de 4.3,
pero estos salen del propio clon de producción, del commit desplegado.

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
grep -m1 'release' /var/www/moodle45/version.php      # 4.5.14+ (Build: 20261002)
ls /var/www/moodle45/local/richimath/version.php /var/www/moodle45/theme/rmpreu/version.php
php8.2 -l /var/www/moodle45/config.php
```

Nada de `cfg.php` ni otro CLI desde este árbol (regla de la §1).

### B6. Árbol 5.3 (clon de `moodle-2027`)

El clon tiene que traer exactamente lo ensayado: primero sube tus commits.
Después, `git log origin/main -1` en el portátil y en el servidor deben dar el
mismo commit. `.gitignore` solo excluye `config.php`, así que el clon es el
mismo árbol que montó el ensayo.

```bash
git clone https://github.com/JFranPineda/moodle-2027.git /var/www/moodle53
git -C /var/www/moodle53 log -1 --format='%h %s'      # el mismo commit que en el portátil
cp -p /var/www/html/config.php /var/www/moodle53/config.php
sed -i '/lib\/setup\.php/i $CFG->routerconfigured = true;' /var/www/moodle53/config.php
php8.3 -l /var/www/moodle53/config.php
diff /var/www/html/config.php /var/www/moodle53/config.php    # solo la línea routerconfigured
chown -R www-data:www-data /var/www/moodle53
```

- El `git log` va **antes** del `chown`: después, git se niega a operar como
  `root` en un árbol de otro dueño, salvo que esté en `safe.directory`. Tras el
  corte el árbol vive en `/var/www/html`, que ya está en la lista.
- `config.php` y `admin/cli/` quedan en la raíz, fuera de `public/`: dejan de
  ser accesibles por HTTP, a diferencia de 4.3.

### B7. Vhost de 5.3, escrito sin activar

En el corte, el código 4.3 pasa a `/var/www/moodle43` y el 5.3 ocupa
`/var/www/html`. El script de despliegue, el cron y la documentación siguen
apuntando a la misma ruta, y solo cambia el `DocumentRoot` a `public/`. Los
ficheros conservan su nombre, así que certbot no nota nada.

```bash
mkdir -p /root/phase3/apache43 /root/phase3/apache53
cd /etc/apache2/sites-enabled
for f in *.conf; do
  cp -L "$f" /root/phase3/apache43/
  sed -E 's#^([[:space:]]*DocumentRoot[[:space:]]+)"?/var/www/html/?"?[[:space:]]*$#\1/var/www/html/public#;
          s#<Directory[[:space:]]+"?/var/www/html/?"?[[:space:]]*>#<Directory /var/www/html/public>#' \
      "$f" > /root/phase3/apache53/"$f"
  echo "== $f"; diff "$f" /root/phase3/apache53/"$f"
done
```

Pegar el `diff`: solo deben cambiar las líneas `DocumentRoot` (y `<Directory>`
si el vhost la tiene). Para las rutas limpias y el router hacen falta
`mod_rewrite` y `AllowOverride FileInfo Indexes` sobre `public/`. Si
`AllowOverride` está en `apache2.conf` para `/var/www/` o `/var/www/html`, ya
alcanza a `public/`. La regla del router está al final de `public/.htaccess`,
en el repo.

### B8. Cierre del bloque B

```bash
php -v | head -1; a2query -m | grep php; apt-mark showhold
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/
crontab -u www-data -l
ls -ld /var/www/moodle45 /var/www/moodle53; df -h /var
```

Producción sigue igual: PHP 8.2, MySQL 8.0, 4.3, cron intacto. Pegar las
salidas de B2–B8.

---

## 4. Bloque C — Ensayo general en el servidor (y vuelta atrás)

Es el guion de la noche del corte. La única diferencia es el final: en vez de
abrir el sitio en 5.3, **se revierte el snapshot**. Necesita:
- una noche sin clases acordada con Richi, con unas 2 h de mantenimiento;
- un hueco libre de snapshot en Contabo;
- el portátil a mano para la copia de seguridad.

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
mkdir -p ~/richimath-prod-copy && chmod 700 ~/richimath-prod-copy
rsync -avP root@169.58.171.171:/root/moodle-copy/ ~/richimath-prod-copy/
cd ~/richimath-prod-copy && sha256sum -c SHA256SUMS
ssh root@169.58.171.171 'rm -rf /root/moodle-copy'      # solo después del OK
```

### C4. Snapshot

Panel de Contabo → Snapshots → crear `ensayo-53-<fecha>`. Esperar a que
termine. En el servidor:

```bash
echo "snapshot: <minutos> min" | tee -a /root/phase3/times.log
```

### C5. Salto 4.3 → 4.5 (PHP 8.2 + MySQL 8.0, como hoy)

```bash
s "upgrade 4.3 → 4.5" sudo -u www-data php8.2 /var/www/moodle45/admin/cli/upgrade.php --non-interactive
```

Tiene que terminar con *«…finalizada con éxito»* y con el paquete `es` de 4.5
instalado.

### C6. MySQL 8.0 → 8.4

```bash
mysql -e "SET GLOBAL innodb_fast_shutdown = 0"
apt-mark unhold $(apt-mark showhold | grep '^mysql-')
s "MySQL 8.0 → 8.4" apt-get install -y mysql-server
```

- **Si pregunta por un fichero de configuración**, conservar el actual (`N`):
  es el que validó el comprobador de B4. Anotar la pregunta.
- **Si pide contraseña de root**, dejarla vacía: `root` sigue por
  `auth_socket`.

```bash
systemctl is-active mysql; mysql -e "SELECT VERSION()"          # active · 8.4.x
mysql -e "SELECT user, plugin FROM mysql.user WHERE user IN ('root', 'moodleuser')"
tail -20 /var/log/mysql/error.log
```

### C7. PHP 8.3, código 5.3 y vhost

```bash
T=$SECONDS
a2dismod -q php8.2 && a2enmod -q php8.3
update-alternatives --set php /usr/bin/php8.3
mv /var/www/html /var/www/moodle43 && mv /var/www/moodle53 /var/www/html
cp /root/phase3/apache53/*.conf /etc/apache2/sites-enabled/
apache2ctl configtest && systemctl restart apache2
echo "switch PHP + code + vhost $((SECONDS - T))s" | tee -a /root/phase3/times.log
php -v | head -1                                              # PHP 8.3.x
# Server rehearsal only: this site is about to be reverted, so cron must not mail anyone.
sed -i '/lib\/setup\.php/i $CFG->noemailever = true;' /var/www/html/config.php
```

La línea de `noemailever` es **solo del ensayo**. La reversión del snapshot la
quita sola; la noche del corte no se añade.

### C8. Salto 4.5 → 5.3

```bash
cd /var/www/html
s "upgrade 4.5 → 5.3" sudo -u www-data php admin/cli/upgrade.php --non-interactive
```

`adhoc_task.php` **se niega a correr en mantenimiento por CLI** («CLI
maintenance mode active, cron execution suspended»). Hay que pasar antes al
mantenimiento «a medias»: solo entran los administradores, y eso es lo que
permite las pruebas de humo como admin. El orden importa: primero
`--enableold`, después borrar el fichero, para que el sitio no quede abierto
ni un instante.

```bash
sudo -u www-data php admin/cli/maintenance.php --enableold
rm /var/www/moodledata/climaintenance.html
s "adhoc tasks (CSS, qbank)" sudo -u www-data php admin/cli/adhoc_task.php --execute
s "Spanish customisations" sudo -u www-data php public/admin/tool/customlang/cli/import.php \
    --lang=es --source=/var/www/html/assets/customlang/es --checkin
```

No purgar cachés después: el CSS se compilaría dos veces.

### C9. Comprobaciones técnicas

```bash
sudo -u www-data php admin/cli/checks.php                    # sin críticos; el aviso del cron es esperado
sudo -u www-data php admin/cli/check_database_schema.php      # Database structure is ok.
sudo -u www-data php scripts/check-db-settings.php            # todo OK/INFO
```

`checks.php` hace aquí la prueba que Docker no podía: la del router, desde el
servidor contra `https://richiacademy.com`.

### C10. Pruebas de humo como admin (navegador)

`https://richiacademy.com/login` con la cuenta de administrador (`richi85`).
Sin sesión, el sitio muestra la página de mantenimiento. Las pruebas son las de
la tabla del plan ([../migration-plan.md](../migration-plan.md) §7), salvo la
1 y la 8: esas necesitan el sitio abierto y quedan para la noche del corte.

- **Prueba 2:** login correcto. Para el login fallido, ventana privada con un
  usuario inventado: no debe salir de `/login`.
- **Pruebas 3–7:** como admin.
- **Prueba 9:** `sudo -u www-data php admin/cli/cron.php` una vez. Con
  `noemailever` no sale ningún correo.

```bash
echo "smoke: <OK / qué falló>" | tee -a /root/phase3/times.log
```

### C11. Sacar los registros ANTES de revertir

La reversión borra todo lo escrito después del snapshot, también estos
registros:

```bash
exit                                  # cierra script (vuelca el registro); sigues en tmux
cat /root/phase3/times.log
```

En el **portátil**:

```bash
scp root@169.58.171.171:'/root/phase3/{times.log,server-rehearsal.log}' ~/richimath-prod-copy/
```

### C12. Revertir el snapshot y abrir 4.3

Panel de Contabo → Snapshots → `ensayo-53-<fecha>` → **Revert**. **Anotar
cuánto tarda** hasta que el SSH vuelve a responder. Después:

```bash
ssh root@169.58.171.171
php -v | head -1; mysql --version                    # 8.2 · 8.0
ls -d /var/www/*                                       # html (4.3), moodle45, moodle53
cd /var/www/html && sudo -u www-data php admin/cli/cfg.php --name=release    # 4.3.12
crontab -u www-data /root/phase3/www-data.crontab && crontab -u www-data -l
sudo -u www-data php admin/cli/maintenance.php --disable
curl -sS -o /dev/null -w '%{http_code}\n' https://richiacademy.com/        # 200
```

Abrir el sitio en una ventana privada: portada y login de 4.3. Avisar a Richi.
La copia del portátil (`~/richimath-prod-copy`) se borra cuando el registro
esté anotado.

---

## 5. Trampas conocidas antes de empezar

1. **`adhoc_task.php` no corre en mantenimiento por CLI**: hay que pasar antes
   a `--enableold` y borrar `climaintenance.html` (C8). El ensayo en Docker no
   lo veía porque no activaba el mantenimiento.
2. **El mantenimiento por CLI tampoco deja entrar al admin** por la web. Las
   pruebas de humo «con mantenimiento puesto» solo son posibles en el modo a
   medias.
3. **Instalar `php8.3-cli` cambia `/usr/bin/php` a 8.3**, y Moodle 4.3 lo
   rechaza: hay que devolverlo con `update-alternatives` en el mismo momento
   (B2).
4. **Con el repositorio de Oracle añadido, `apt upgrade` subiría MySQL a 8.4**
   con el 4.3 en vivo: por eso los paquetes quedan retenidos (B4) hasta C6.
5. **Arrancar el código nuevo contra la base de producción** escribe cachés de
   otra versión en `moodledata`: nada de CLI desde `moodle45`/`moodle53` antes
   de la noche.
6. **Un SSH cortado mata el `upgrade.php`** que esté corriendo: siempre dentro
   de `tmux`.
7. **Revertir el snapshot borra los registros de la noche**: sacarlos antes
   (C11).
8. **`git` como `root` en un árbol de `www-data`** pide `safe.directory`
   (B6).

---

## 6. Registro

| Paso | Portátil (ensayo 4) | Servidor (bloque C) |
|---|---|---|
| Snapshot (crear) | — | |
| Copia: volcado + `tar` | 21–27 s + 14 s (Fase 2) | |
| Upgrade 4.3 → 4.5 | 159 s | |
| MySQL 8.0 → 8.4 | 20 s | |
| Cambio de PHP, código y vhost | — | |
| Upgrade 4.5 → 5.3 | 164 s | |
| Tareas adhoc (CSS, `mod_qbank`) | 229 s | |
| Personalización de idioma | 12 s | |
| Pruebas de humo | — | |
| Snapshot (revertir) | — | |

Con la columna del servidor se fijan los `T+?` de la plantilla del corte
([../migration-plan.md](../migration-plan.md) §7), con margen × 2.

## 7. Qué pasa a la Fase 4

- La noche del corte es el bloque C hasta C10 sin la línea de `noemailever`,
  y después:
  1. prueba de humo 1 (portada sin sesión) y 8 (como alumno);
  2. `maintenance.php --disable`;
  3. restaurar el cron (`crontab -u www-data /root/phase3/www-data.crontab`;
     con `php` ya en 8.3, la misma línea sirve);
  4. una prueba de correo (Administración → Servidor → Correo → Probar).
- **FUN-23**: la primera vez que haga falta desplegar sobre 5.3,
  `bash /var/www/html/scripts/deploy-contabo.sh`. No se prueba en el bloque C,
  porque termina quitando el mantenimiento.
- **Fase 5:** retirar PHP 8.2 y `/var/www/moodle43` tras dos semanas estables;
  borrar `/root/moodle-backups/pre-collation-*`.
