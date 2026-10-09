# Desarrollo de plugins: campos de BD e interfaces propias

Cómo crear funcionalidad nueva en local y que llegue a Contabo por git.

Todo el código propio vive en un plugin `local_richimath`. Nunca en el core.

## Estructura del plugin

```
local/richimath/
├── version.php                    ← el número que dispara las migraciones
├── index.php                      ← página de entrada
├── db/
│   ├── install.xml                ← tablas para instalaciones nuevas
│   ├── upgrade.php                ← migraciones para las ya existentes
│   └── access.php                 ← capacidades / permisos
├── classes/
│   ├── form/alumno_form.php       ← formularios (moodleform)
│   └── output/renderer.php        ← renderers
├── templates/panel.mustache       ← plantillas
├── lang/en/local_richimath.php    ← strings (en inglés siempre; es el idioma base)
└── styles.css
```

## `version.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_richimath';
$plugin->version   = 2026081900;   // AAAAMMDDXX — súbelo en CADA cambio de esquema
$plugin->requires  = 2023100900;   // Moodle 4.3, la versión de Contabo
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1.0';
```

El `requires` sale del `version.php` del propio Moodle: en Contabo es
`2023100912.00` (Moodle 4.3.12), así que `2023100900` es el mínimo correcto.

## Campos de base de datos: XMLDB

El esquema viaja como **código**, no como datos. Moodle compara el
`$plugin->version` del código con lo que tiene registrado en su tabla
`config_plugins` y ejecuta solo los bloques pendientes. Es el mismo mecanismo
que usa Moodle para sus propias actualizaciones.

### `db/upgrade.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_richimath_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026081900) {
        $table = new xmldb_table('local_richimath_alumno');
        $field = new xmldb_field('nivel', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026081900, 'local', 'richimath');
    }

    return true;
}
```

Cada cambio futuro se añade como un bloque `if ($oldversion < N)` nuevo, con su
`upgrade_plugin_savepoint`. **No se editan los bloques antiguos**: en producción
ya se ejecutaron, y reescribirlos deja los entornos desincronizados.

### No escribas `install.xml` a mano

Moodle trae un editor gráfico: *Administración del sitio → Desarrollo → Editor
XMLDB*. Genera el `install.xml` y te da el código del `upgrade.php` ya escrito.
Úsalo en el entorno local, y sube el resultado por git.

### Ciclo completo de un cambio de esquema

| Dónde | Qué |
|---|---|
| Local | Editas `upgrade.php`, subes `$plugin->version`, pruebas |
| Local | `git push` |
| Contabo | `git pull` |
| Contabo | `php admin/cli/upgrade.php --non-interactive` ← aplica el cambio a MySQL |

## Interfaces nuevas

Son código puro, así que viajan por git sin fricción. Página mínima:

```php
<?php
// local/richimath/index.php
require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/richimath:view', $context);

$PAGE->set_url(new moodle_url('/local/richimath/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_richimath'));
$PAGE->set_heading(get_string('pluginname', 'local_richimath'));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_richimath/panel', ['alumnos' => $datos]);
echo $OUTPUT->footer();
```

Con `db/access.php` para el permiso:

```php
$capabilities = [
    'local/richimath:view' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => ['manager' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW],
    ],
];
```

Si solo tocas plantillas o CSS, no hace falta `upgrade.php`: basta `git pull` y
`purge_caches.php`.

## Portabilidad entre motores de base de datos

Contabo corre **MySQL/MariaDB**. Si tu entorno local usa PostgreSQL, el código
tiene que ceñirse a la API de Moodle o fallará en producción aunque funcione en
local. Confirma el motor con:

```bash
php /var/www/html/admin/cli/cfg.php --name=dbtype
```

**Portable** — funciona en los dos motores:

```php
$DB->get_records('local_richimath_alumno', ['nivel' => 3]);
$DB->get_record_sql('SELECT * FROM {local_richimath_alumno} WHERE id = ?', [$id]);
$DB->sql_concat('nombre', "' '", 'apellido');
$DB->sql_like('nombre', ':pat', false);
$DB->insert_record('local_richimath_alumno', $registro);
```

**Rompe al cambiar de motor:**

- SQL crudo con comillas invertidas (`` `tabla` ``) — sintaxis solo de MySQL
- `ILIKE` — solo PostgreSQL
- `LIMIT / OFFSET` escrito a mano en vez de los parámetros de `get_records_sql()`
- Tipos `SERIAL`, `AUTO_INCREMENT` declarados a mano en vez de vía XMLDB
- Diferencias de sensibilidad a mayúsculas en las comparaciones de texto

Fíjate en las llaves: `{local_richimath_alumno}`. Moodle sustituye ahí el prefijo
de tablas configurado en `config.php`. Escribir el nombre real de la tabla
rompe en cuanto el prefijo cambie.

La recomendación práctica es que el entorno local use MariaDB, espejo de
producción — ver [local-dev-environment.md](local-dev-environment.md).
Desarrollar en Postgres y desplegar en MySQL es pedir que algo pase en local y
falle con alumnos dentro.

## Convenciones

- Nombres de tabla con prefijo del plugin: `local_richimath_*`.
- Strings siempre vía `get_string()`, nunca texto suelto en las plantillas.
- El idioma base de los strings es el inglés (`lang/en/`); las traducciones se
  añaden en `lang/es/`.
- Un plugin, un propósito. Si crece demasiado, sepáralo.
