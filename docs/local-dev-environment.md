# Entorno de desarrollo local — Moodle 5.3

`docker compose up -d` en la raíz de este repo → **http://localhost:8083**.
Corre al lado del espejo 4.3 (`github/moodle`, puerto 8080), así se comparan
las dos versiones lado a lado.

| Servicio | Imagen | Qué hace |
|---|---|---|
| `db` | `mysql:8.4` | La base que exige 5.3 y la que tendrá producción |
| `web` | `moodlehq/moodle-php-apache:8.3` | Apache + PHP 8.3, `DocumentRoot` en `public/` |
| `cron` | la misma imagen | `admin/cli/cron.php` cada 60 s, como en producción |

## Lo que el contenedor `web` configura al arrancar (y por qué)

1. **`DocumentRoot` = `public/`** (variable `APACHE_DOCUMENT_ROOT`). Desde 5.1 el
   código web vive ahí; `config.php` y `admin/cli/` siguen en la raíz.
2. **El router de Moodle.** 5.3 marca un router sin configurar como
   **comprobación crítica**. Todo lo que no es fichero ni directorio va a
   `/r.php` con una regla de `mod_rewrite`.
   - **No sirve `FallbackResource /r.php`**: PHP contesta su propio 404 a un
     `*.php` inexistente antes de que actúe el *fallback*, y uno de los cinco
     tests de core pide justo eso (`/lib/exampleshimroute2.php` → 302).
   - Con el router configurado, `config.php` lleva `$CFG->routerconfigured = true`.
3. **Apache escucha también en 8083 dentro del contenedor.** El test del router
   lo hace el propio Moodle desde el servidor contra `$CFG->wwwroot`
   (`localhost:8083`), y dentro del contenedor solo existía el puerto 80: los
   tests fallaban con «cURL error 7» aunque desde el navegador todo iba bien.
4. **`mod_rewrite` y `AllowOverride FileInfo Indexes`** en `public/`, para el
   `.htaccess` de las rutas limpias (MIG-14).

> **Ojo para MIG-14**: cuando exista `public/.htaccess` con `RewriteEngine On`,
> sus reglas **sustituyen** a las del bloque `<Directory>` (mod_rewrite no las
> mezcla salvo `RewriteOptions Inherit`). La regla del router tendrá que ir
> **al final de ese `.htaccess`**, después de las rutas limpias, y en
> producción igual.

## `config.php` local

No está en git. Lo esencial: `dbtype=mysqli`, `dbhost=db`, `wwwroot=http://localhost:8083`,
`dataroot=/var/moodledata`, `routerconfigured=true`, depuración `E_ALL` y
**`noemailever = true`** (en local no sale ningún correo).

## Instalación limpia (ya hecha el 2026-10-09)

```bash
docker compose up -d
docker cp ../moodle-lang/es_v5.3/es moodle-2027-web-1:/var/moodledata/lang/es
docker compose exec -T web chown -R www-data:www-data /var/moodledata
docker compose exec -T -u www-data web php admin/cli/install_database.php \
  --lang=es --adminuser=qa.admin --adminpass='…' --adminemail=qa.admin@localhost.local \
  --fullname="Richi Academy (5.3 local)" --shortname="Richi Academy" --agree-license
docker compose exec -T -u www-data web php admin/cli/checks.php   # todo OK
```

La contraseña del admin local no va en el repo.

---

# Referencia: el entorno de 4.3

Lo que sigue es la guía del espejo 4.3 (`github/moodle`). Sigue valiendo para
**sacar la copia de producción** (Fase 2 del plan): BD y `moodledata/filedir`
viajan juntos.

## Entorno de desarrollo local (4.3)

Objetivo: un Moodle en el portátil que sea **espejo de Contabo**, para probar
antes de desplegar sobre un sitio con alumnos.

Producción usa Moodle 4.3.12 sobre MySQL/MariaDB y Apache. El entorno local debe
usar el mismo motor de base de datos; ver la sección de portabilidad en
[plugin-development.md](plugin-development.md) para saber por qué.

## Con Docker

El `docker-compose.yml` está versionado en la raíz del repositorio (no contiene
secretos). Usa la imagen oficial de desarrollo de Moodle HQ
(`moodlehq/moodle-php-apache:8.1`), que trae todas las extensiones PHP que
Moodle exige ya compiladas, y MariaDB 10.11 — espejo del motor de Contabo.

```bash
cd /home/jfranpineda2025/Documentos/github/moodle
docker compose up -d          # espera al healthcheck de la BD solo
```

Primera vez (instala la BD con admin / Admin.1234):

```bash
docker compose exec web bash -c 'mkdir -p /var/moodledata && chown www-data:www-data /var/moodledata'
docker compose exec -u www-data web php admin/cli/install_database.php \
  --agree-license --adminuser=admin --adminpass='Admin.1234' \
  --adminemail=admin@localhost.local --fullname="Richimath Local" --shortname="richimath-dev"
docker compose exec -u www-data web php admin/cli/cfg.php --name=theme --set=richimath
docker compose exec -u www-data web php admin/cli/purge_caches.php
```

Sitio en <http://localhost:8080>. Los comandos CLI van siempre con
`-u www-data`: si corren como root dejan ficheros en moodledata que Apache
luego no puede escribir.

Parar / arrancar / resetear:

```bash
docker compose stop           # parar sin perder datos
docker compose up -d          # volver a arrancar
docker compose down -v        # ⚠ borra BD y moodledata locales (reset total)
```

## `config.php` local

No está versionado, así que cada entorno mantiene el suyo. Crea el tuyo:

```php
<?php
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'moodle';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = ['dbpersist' => 0, 'dbsocket' => 0, 'dbport' => ''];

$CFG->wwwroot   = 'http://localhost:8080';
$CFG->dataroot  = '/var/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 0777;

// Solo en local: muestra los errores en vez de tragárselos
$CFG->debug        = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
$CFG->cachejs      = false;
$CFG->themedesignermode = true;

require_once(__DIR__ . '/lib/setup.php');
```

`debugdisplay` y `themedesignermode` **solo en local**. En producción muestran
rutas internas y trazas a cualquier visitante, y degradan el rendimiento.

## Instalación por CLI

```bash
docker compose exec web php admin/cli/install_database.php \
  --agree-license \
  --adminuser=admin \
  --adminpass=Admin.1234 \
  --adminemail=admin@localhost \
  --fullname="Richimath Local" \
  --shortname="richimath-dev"
```

## Datos de prueba

Para probar contra algo parecido a la realidad, restaura una copia de producción
en local. **Nunca en sentido contrario.**

```bash
# En Contabo
mysqldump -u root -p moodle | gzip > /root/moodle-$(date +%F).sql.gz

# En el portátil
scp root@169.58.171.171:/root/moodle-*.sql.gz .
gunzip -c moodle-*.sql.gz | docker compose exec -T db mysql -u root -proot moodle
docker compose exec web php admin/cli/purge_caches.php
```

Tras restaurar, corrige el `wwwroot` para que el sitio local no redirija a la IP
de producción:

```bash
docker compose exec web php admin/cli/cfg.php --name=wwwroot --set=http://localhost:8080
```

La BD y `moodledata/filedir` van JUNTOS: el dump referencia ficheros físicos
(imágenes de curso, adjuntos) por hash. Sin el filedir, las páginas de
categorías y los formularios de curso escupen warnings de ficheros ausentes.
Tras importar el dump, sincroniza también:

```bash
rsync -az root@169.58.171.171:/var/www/moodledata/filedir/ /tmp/filedir/
tar -C /tmp/filedir -cf - . | docker compose exec -T web tar -xf - -C /var/moodledata/filedir
docker compose exec web chown -R www-data:www-data /var/moodledata/filedir
docker compose exec -u www-data web php admin/cli/purge_caches.php
rm -rf /tmp/filedir
```

Ese dump contiene datos personales de alumnos reales: mantenlo fuera del
repositorio (ya cubierto por `.gitignore`) y bórralo cuando termines.

## Comprobación antes de cada push

```bash
docker compose exec web php admin/cli/upgrade.php --non-interactive
docker compose exec web php admin/cli/purge_caches.php
curl -sI http://localhost:8080/ | head -1
```

Si la migración corre limpia en local, correrá en Contabo.
