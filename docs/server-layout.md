# Mapa del servidor Contabo

Estado verificado el 2026-08-19 sobre el host `vmi3506104`.

## Acceso

| Dato | Valor |
|---|---|
| IP | `169.58.171.171` |
| URL del sitio | `http://169.58.171.171` |
| Sistema | Ubuntu 24.04 LTS |
| Hostname | `vmi3506104` |
| Usuarios | `root`, `ubuntu` |

Actualmente el acceso SSH es por contraseña. Para trabajar sin escribirla cada
vez, añade tu clave pública desde el portátil:

```bash
ssh-copy-id -i ~/.ssh/id_ed25519.pub root@169.58.171.171
```

## Rutas

| Qué | Ruta | Versionado en git |
|---|---|---|
| Código Moodle (DocumentRoot) | `/var/www/html` | Sí — clon de este repo |
| `moodledata` | `/var/www/moodledata` | No |
| Configuración activa | `/var/www/html/config.php` | **No** (ignorado a propósito) |
| Copia de seguridad del config | `/root/config.php.bak` | No |
| Datos MySQL | `/var/lib/mysql/moodle` | No |
| Ficheros sacados del webroot | `/root/moodle-backups/` | No |

Moodle cuelga **directamente de `/var/www/html`**, no de un subdirectorio
`html/moodle/`. Es decir, `/var/www/html/config.php`, `/var/www/html/mod/`, etc.

`moodledata` está en `/var/www/moodledata`: dentro de `/var/www` pero **fuera**
del DocumentRoot. Es lo correcto — Apache no lo sirve por HTTP. Si alguna vez el
`DocumentRoot` pasara a ser `/var/www`, los ficheros privados de los alumnos
quedarían expuestos.

## Servicios

| Servicio | Estado |
|---|---|
| Apache2 | activo, escuchando en `:80` |
| `apache-htcacheclean` | activo |
| MySQL / MariaDB | activo |
| Docker | **no instalado** — Moodle corre nativo, no en contenedor |
| Nginx | no instalado |

No hay TLS: el sitio solo responde en el puerto 80.

## Versiones

```
Moodle   4.3.12 (Build: 20250414)
branch   403
version  2023100912.00
maturity MATURITY_STABLE
```

Ese número `2023100912` es el que necesitas para el `$plugin->requires` de
cualquier plugin propio.

## Plugins instalados

Ninguno de terceros. Instalación limpia:

- `local/` — vacío (solo `readme.txt` y `upgrade.txt` del core)
- `mod/` — únicamente los módulos que trae Moodle de serie

Lienzo limpio: todo lo que aparezca en `local/` de aquí en adelante es código
propio.

## Repositorio git

```
origin  https://github.com/Richimath-Moodle/Moodle.git
rama    main
```

El servidor usa HTTPS para el remoto; el clon local usa SSH
(`git@github.com:richimath-moodle/moodle.git`). Es indiferente, apuntan al mismo
sitio.

## Comandos de diagnóstico

```bash
# Versión de Moodle
php -r 'require "/var/www/html/version.php"; echo "$release | $version | $branch", PHP_EOL;'

# Cualquier ajuste de config.php desde CLI
php /var/www/html/admin/cli/cfg.php --name=dataroot
php /var/www/html/admin/cli/cfg.php --name=dbtype
php /var/www/html/admin/cli/cfg.php --name=wwwroot

# Chequeo de salud y seguridad que trae Moodle
php /var/www/html/admin/cli/checks.php

# Commit desplegado ahora mismo
cd /var/www/html && git log -1 --format='%h %ci %s'

# Qué escucha en los puertos web
ss -tlnp | grep -E ':80|:443'

# El sitio responde
curl -sI http://169.58.171.171/ | head -1
```

## Cómo se localizó todo esto (por si hay que repetirlo)

Buscar `config.php` filtrando por la palabra "moodle" en la ruta **no funciona**:
el fichero vive en `/var/www/html/config.php` y esa ruta no contiene "moodle".
Lo que sí delata la instalación es la estructura interna:

```bash
find / -name version.php -path '*moodle*' 2>/dev/null
find / -maxdepth 5 -iname 'moodle*' -type d 2>/dev/null
ss -tlnp | grep -E ':80|:443'
```

Las pistas fueron `/var/www/html/admin/tool/moodlenet/` y
`/var/www/html/backup/moodle2` colgando directamente de `html`.
