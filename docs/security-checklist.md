# Riesgos abiertos y cómo cerrarlos

Revisión del 2026-08-19. Ordenado por urgencia.

## 1. Contraseña de MySQL expuesta en el historial de git — PENDIENTE

`config.php` estuvo versionado hasta el commit `d747781d` con la contraseña de
la base de datos dentro. Destrackear el fichero evita futuros problemas, pero
**no borra el pasado**: la contraseña sigue siendo legible en los commits
`b6e0adb7` y `afee9a8e` (ambos titulados "Update config.php") para cualquiera
con acceso al repositorio.

Rotar la contraseña es lo único que cierra de verdad la exposición. Reescribir
el historial de git no basta: si alguien clonó el repo, ya la tiene.

```sql
-- En Contabo: mysql -u root -p
CREATE USER 'moodle'@'localhost' IDENTIFIED BY 'PASSWORD_NUEVA_LARGA';
GRANT ALL PRIVILEGES ON moodle.* TO 'moodle'@'localhost';
FLUSH PRIVILEGES;
```

Después actualiza `dbuser` y `dbpass` en `/var/www/html/config.php` (que ya no
viaja por git) y comprueba:

```bash
php /var/www/html/admin/cli/cfg.php --name=dataroot   # si responde, la conexión va bien
```

## 2. Moodle usa `debian-sys-maint` como usuario de base de datos — PENDIENTE

`config.php` en producción tiene `$CFG->dbuser = 'debian-sys-maint'`. Esa es la
cuenta de mantenimiento del sistema Debian/Ubuntu: tiene privilegios totales
sobre **todo** el servidor MySQL, no solo sobre la base `moodle`. Una inyección
SQL en cualquier plugin dejaría de ser un problema de Moodle para pasar a serlo
de la máquina entera.

Además, los scripts de mantenimiento del sistema rotan esa contraseña por su
cuenta: el día que lo hagan, Moodle deja de conectar sin previo aviso.

Se resuelve con el mismo `CREATE USER` del punto anterior. Los dos arreglos son
el mismo trabajo; hazlos a la vez.

## 3. Sitio sin HTTPS — PENDIENTE

El sitio solo escucha en el puerto 80. Las credenciales de administrador y de
los alumnos viajan en texto plano por la red. Cualquiera en la misma wifi puede
leerlas.

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d TU_DOMINIO
```

Certbot necesita un **dominio**, no funciona sobre una IP desnuda. Hace falta
apuntar un dominio a `169.58.171.171` primero. Después, actualiza el `wwwroot`:

```bash
php /var/www/html/admin/cli/cfg.php --name=wwwroot --set=https://TU_DOMINIO
php /var/www/html/admin/cli/purge_caches.php
```

## 4. Ficheros sobrantes en el DocumentRoot — RESUELTO PARCIALMENTE

En `/var/www/html` había dos ficheros que no pertenecen a Moodle:

- `config-viejo.php` — configuración antigua con credenciales de BD. PHP
  normalmente lo ejecuta en vez de mostrarlo, pero si Apache falla o el módulo
  PHP se desactiva en una actualización, se sirve en texto plano con la
  contraseña dentro.
- `prueba_contabo.txt` — fichero de prueba, legible por cualquiera en
  `http://169.58.171.171/prueba_contabo.txt`.

```bash
mkdir -p /root/moodle-backups
mv /var/www/html/config-viejo.php /root/moodle-backups/
mv /var/www/html/prueba_contabo.txt /root/moodle-backups/
```

Regla general: nada que no sea Moodle vive en `/var/www/html`. Ambos nombres
están ya en `.gitignore` para que no vuelvan por git.

## 5. Moodle 4.3 probablemente fuera de soporte de seguridad — VERIFICAR

La versión instalada es 4.3.12 (Build 20250414). La rama 4.3 no es LTS y su
ventana de soporte de seguridad terminó alrededor de abril de 2025 — esa fecha
de build coincide con la última publicación de seguridad de la rama. Si es así,
las vulnerabilidades descubiertas desde entonces no tienen parche para 4.3.

Confirma el estado actual en la tabla oficial de versiones:
<https://moodledev.io/general/releases>

Si está confirmado, planifica la subida a la LTS vigente. Es una migración con
su propio riesgo: requiere respaldo completo, entorno local de prueba y
verificación de que PHP en el servidor cumple los requisitos de la versión
destino. No la hagas directamente sobre producción.

## 6. `config.php` destrackeado — RESUELTO

Commit `d747781d`. El fichero está en `.gitignore` y cada entorno mantiene el
suyo. Permisos en producción: `root:www-data`, modo `640`.

Verificación:

```bash
cd /var/www/html && git status --short config.php   # debe salir vacío
ls -l /var/www/html/config.php                      # -rw-r----- root www-data
```

## Copias de seguridad

No hay copias automáticas configuradas. Mínimo viable, antes de cualquier cambio:

```bash
mysqldump -u root -p moodle | gzip > /root/moodle-$(date +%F).sql.gz
tar czf /root/moodledata-$(date +%F).tar.gz /var/www/moodledata
```

Un dump en el mismo disco que la base de datos no es una copia de seguridad
real: si falla el disco o se pierde el servidor, se pierden los dos. Conviene
sacarlos fuera de la máquina.

## Chequeo que trae Moodle

```bash
php /var/www/html/admin/cli/checks.php
```

Lista permisos de `dataroot`, ausencia de HTTPS, ficheros sobrantes y ajustes
inseguros. Pásalo después de cada despliegue grande.
