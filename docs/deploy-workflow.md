# Flujo de despliegue

Cómo llevar un cambio del portátil a producción sin romper el sitio.

## Qué viaja por git y qué no

Es la distinción que decide todo lo demás.

| Sí viaja (lo editas en local) | No viaja (vive solo en Contabo) |
|---|---|
| Plugins: `local/`, `mod/`, `blocks/`, `theme/` | Base de datos: cursos, usuarios, matrículas, notas |
| PHP, plantillas Mustache, CSS, JS | Ajustes hechos desde el panel de administración |
| Strings de idioma (`lang/`) | Campos de perfil creados clicando en la UI |
| Esquema de BD **declarado en código** (`db/install.xml`, `db/upgrade.php`) | `/var/www/moodledata`: ficheros subidos, caché, sesiones |
| | `config.php` (uno distinto por entorno) |

Corolario importante: si creas un campo de perfil desde
*Administración → Campos de perfil del usuario*, se guarda en la tabla
`user_info_field` de MySQL y **no aparece en el repositorio**. Para que un campo
viaje por git tiene que nacer en código — ver
[plugin-development.md](plugin-development.md).

## Ciclo normal

**En local:**

```bash
cd /home/jfranpineda2025/Documentos/github/moodle
# editar código en local/richimath/
git add -A
git commit -m "feat(richimath): descripción del cambio"
git push
```

**En Contabo:**

```bash
cd /var/www/html
php admin/cli/maintenance.php --enable
git pull
php admin/cli/upgrade.php --non-interactive
php admin/cli/purge_caches.php
chown -R www-data:www-data /var/www/html
php admin/cli/maintenance.php --disable
```

Los tres pasos que la gente olvida:

- **`upgrade.php`** es quien lee el `version.php` de tus plugins y aplica los
  cambios de esquema a MySQL. Sin él, el código nuevo está pero las tablas no.
- **`purge_caches.php`**: Moodle cachea plantillas, CSS y definiciones de
  strings de forma agresiva. Sin purgar, tus cambios "no se ven".
- **`chown`**: si haces `git pull` como `root`, los ficheros nuevos quedan de
  `root` y Apache (que corre como `www-data`) no puede leerlos.

Si el cambio es solo CSS o plantillas, basta con `git pull` + `purge_caches.php`.

## Antes de cualquier migración de esquema

```bash
mysqldump -u root -p moodle | gzip > /root/moodle-$(date +%F).sql.gz
tar czf /root/moodledata-$(date +%F).tar.gz /var/www/moodledata
```

## Por qué `config.php` no está versionado

Hasta el commit `d747781d` sí lo estaba, y contenía **las dos** configuraciones
—la del XAMPP local y la de Contabo— con la contraseña de MySQL dentro. Eso
causaba dos problemas:

1. **Rompía producción al desplegar.** Ajustar `config.php` en local y hacer push
   significaba que el `git pull` en Contabo sobreescribía el config de
   producción. Moodle apuntaba a una BD inexistente y el sitio caía.
2. **Credenciales en el repositorio.** Aunque sea privado, la contraseña queda en
   el historial de git de forma permanente.

Desde `d747781d` el fichero está en `.gitignore` y cada entorno mantiene el suyo.

### Si alguna vez hay que repetir la operación

`git rm --cached` es **atómico**: si le pasas varios ficheros y uno no existe,
no destrackea ninguno. Hay que pasarle solo los que realmente están en el índice.

```bash
# LOCAL — primero
cd /home/jfranpineda2025/Documentos/github/moodle
git rm --cached config.php
printf 'config.php\nconfig-viejo.php\nmoodledata/\n' >> .gitignore
git add .gitignore
git commit -m "chore: untrack config.php (contiene credenciales de BD)"

# verificar antes de subir
git ls-files --error-unmatch config.php && echo "MAL: sigue versionado" || echo "OK: destrackeado"
git status --short config.php    # vacío = ignorado correctamente

git push
```

```bash
# CONTABO — después. El orden importa: al dejar de estar versionado,
# el git pull BORRA el config.php del servidor. La copia va antes.
cp /var/www/html/config.php /root/config.php.bak
cd /var/www/html && git pull
cp /root/config.php.bak /var/www/html/config.php
chown root:www-data /var/www/html/config.php && chmod 640 /var/www/html/config.php
```

Añadir un fichero al `.gitignore` **no** lo destrackea si ya estaba en el índice;
`.gitignore` solo afecta a ficheros no versionados. Hacen falta las dos cosas.

## Verificación post-despliegue

```bash
php /var/www/html/admin/cli/cfg.php --name=dataroot   # → /var/www/moodledata
curl -sI http://169.58.171.171/ | head -1             # → HTTP/1.1 200 OK
cd /var/www/html && git log -1 --format='%h %s'       # el commit que esperabas
```

Si `cfg.php` da error de conexión a la base de datos, el `config.php` no está en
su sitio: restaura desde `/root/config.php.bak`.

## Reglas duras

- **Nunca hagas dump de la BD local y lo restaures en Contabo.** Borrarías los
  cursos, usuarios y matrículas reales. El esquema viaja como código; los datos
  se quedan donde están.
- **Nunca edites el core de Moodle** (`lib/`, `admin/`, `course/`, `mod/*` de
  serie). La próxima actualización de seguridad te pisa los cambios o entra en
  conflicto. Todo lo propio va en `local/richimath/` o en un tema hijo.
- **Nunca ejecutes un agente con `--dangerously-skip-permissions` en el
  servidor de producción.**
- Prueba en local antes de subir. Es un sitio con alumnos.
