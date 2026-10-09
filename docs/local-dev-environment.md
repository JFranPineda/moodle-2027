# Entorno de desarrollo local

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
