# Fase 2 — Copia de producción y ensayo del upgrade

Cómo se saca una copia de `richiacademy.com` (VPS 4 de Contabo) y cómo se
ensaya sobre ella, en local, el upgrade **4.3 → 4.5 → 5.3** tal como se hará en
producción. Plan general: [../migration-plan.md](../migration-plan.md) §5.

La fase sale cuando el ensayo queda **en verde dos veces seguidas**, cada una
desde una copia limpia. Por eso este procedimiento se repite entero cada vez.

---

## 1. Reglas: son datos reales de alumnos

- La copia vive en **`~/richimath-prod-copy`** (`chmod 700`), fuera de todo
  repo. Nunca se sube a GitHub, a la nube ni a un chat.
- Se borra **del VPS** en cuanto la descarga verifica, y **del portátil** al
  cerrar la Fase 2 (sección 6).
- La copia comparte identidad con producción. Comprobado el 2026-10-09, solo
  podría salir **correo**: el SMTP de Gmail está configurado. El `config.php`
  del ensayo lleva `$CFG->noemailever = true`. El resto no sale:
  - el push (`airnotifier`) está activado pero sin clave;
  - el sitio no está registrado en moodle.org;
  - no hay cuenta OAuth2 de sistema.
- El ensayo **no lleva cron**. `checks.php` avisa de ello: es lo esperado.

## 2. Sacar la copia (en el VPS, como `root`, fuera de horario)

Sin modo mantenimiento: `--single-transaction` toma una foto coherente de la
base sin bloquearla. De noche, porque comprimir carga el VPS.

```bash
ssh root@169.58.171.171

# 1. Medir
cd /var/www/html
sudo -u www-data php admin/cli/cfg.php --name=dbname     # moodle
sudo -u www-data php admin/cli/cfg.php --name=prefix     # mdl_
mysql --version
df -h /root
du -sh /var/www/moodledata/* | sort -h

# 2. Base de datos
mkdir -p /root/moodle-copy && chmod 700 /root/moodle-copy && cd /root/moodle-copy
STAMP=$(date +%F-%H%M)
time nice -n 19 mysqldump -u root -p --single-transaction --quick \
  --default-character-set=utf8mb4 --no-tablespaces moodle \
  | gzip > moodle-db-$STAMP.sql.gz

# 3. moodledata, sin lo que Moodle regenera
time nice -n 19 ionice -c3 tar -C /var/www -czf moodledata-$STAMP.tar.gz \
  --exclude=moodledata/cache --exclude=moodledata/localcache \
  --exclude=moodledata/sessions --exclude=moodledata/temp \
  --exclude=moodledata/trashdir --exclude=moodledata/lock \
  moodledata

# 4. Versión y commit desplegados, y sumas
sudo -u www-data php /var/www/html/admin/cli/cfg.php --name=release > version.txt
git -C /var/www/html log -1 --format='%h %ci %s' >> version.txt
sha256sum *.gz > SHA256SUMS
ls -lh
```

En el portátil:

```bash
mkdir -p ~/richimath-prod-copy && chmod 700 ~/richimath-prod-copy
rsync -avP root@169.58.171.171:/root/moodle-copy/ ~/richimath-prod-copy/
cd ~/richimath-prod-copy && sha256sum -c SHA256SUMS     # «La suma coincide» en las dos
ssh root@169.58.171.171 'rm -rf /root/moodle-copy'      # solo después del OK
```

**Trampas:**
- `du -sh moodledata moodledata/{filedir,…}` imprime solo la primera línea:
  `du` no cuenta dos veces un directorio que ya sumó. Usar `moodledata/*`.
- `php -r 'require "version.php"; …'` da vacío: `version.php` hace `die()`
  fuera de Moodle. La versión sale de `cfg.php --name=release`.

**Medido el 2026-10-09:**
- **Base de datos**: 43 MB, 3,2 MB comprimida, 27 s.
- **`moodledata`**: 220 MB, 102 MB comprimido, 14 s.
- **Descarga**: 15 s.
- **Origen**: MySQL 8.0.46; código `3c044eae` (= `github/moodle` sin los
  commits de documentación).

## 3. Ensayar el upgrade

```bash
cd ~/Documentos/github/moodle-2027
scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy
```

Parte siempre de cero: borra el ensayo anterior (proyecto Docker
`rmrehearsal` y `~/richimath-prod-copy/work`). No toca el 5.3 de desarrollo
(`:8083`) ni el espejo 4.3 (`:8080`).

| Paso | Qué hace | Igual que en producción |
|---|---|---|
| Árbol 4.5 | `github/moodle-2026` + los 6 plugins de 4.3 en el commit de `version.txt` | Sí |
| PHP 8.2 + MySQL 8.0 | Las versiones que tiene hoy el VPS | Sí |
| Importar | Volcado y `moodledata` | — |
| Unificar collation | `config.php` del ensayo en `utf8mb4_unicode_ci` + `mysql_collation.php`: convierte las 5 tablas de `local_richimath`, las únicas en `general_ci` | Sí (plan §6.2) |
| Upgrade 4.3 → 4.5 | `upgrade.php --non-interactive` | Sí |
| Cambio a PHP 8.3 + MySQL 8.4 | MySQL actualiza su diccionario al arrancar sobre los mismos datos | Sí (en el VPS es `apt`; se mide en la Fase 3) |
| Upgrade 4.5 → 5.3 | Código de este repo | Sí |
| Tareas adhoc | Las que deja el upgrade: CSS de todos los temas y el paso de bancos de preguntas a `mod_qbank` | Sí |
| Personalización de idioma | `assets/customlang/es/` («Lista de usuarios», MIG-26) | Sí |
| Admin local | `qa.admin` con contraseña aleatoria en `work/qa-admin.txt` | **No**: solo para probar |

El paquete de idioma no es un paso aparte. La copia trae el `es` **de 4.3**, y
cada `upgrade.php` instala el de su versión («El paquete de idioma 'es' se ha
instalado con éxito»). Para eso **el VPS necesita salida a
`download.moodle.org`** la noche del corte. Sin ella, los textos nuevos desde 4.4
salen en inglés.

Al final se ejecutan `checks.php`, `check_database_schema.php` y
`scripts/check-db-settings.php` (la sección E de los tickets, valor a valor).

Resultados en `~/richimath-prod-copy/work/`:
- `rehearsal.log`: tiempos y ajustes;
- `upgrade-4.5.log` y `upgrade-5.3.log`: salida completa;
- `warnings.txt`: avisos agrupados;
- `schema.txt`: diferencias de esquema.

El sitio queda en **http://localhost:8084**. Para ver a un alumno: como
`qa.admin`, su perfil → «Iniciar sesión como».

## 4. Registro de ensayos

| Paso | Ensayo 1 · 2026-10-09 |
|---|---|
| Importar base + `moodledata` | 24 s |
| Unificar collation | 4 s |
| Upgrade 4.3 → 4.5 | 139 s |
| Cambio a PHP 8.3 + MySQL 8.4 | 20 s |
| Upgrade 4.5 → 5.3 | 148 s |
| Tareas adhoc (≈ todo CSS; `mod_qbank` ≈ 1 s) | 197 s |
| Personalización de idioma | 7 s |
| **Total, de la collation al último paso** | **515 s ≈ 8,6 min** |
| Resultado | `checks.php`: solo el aviso de cron. Esquema OK. Las 504 tablas en `unicode_ci`. Sección E: todo OK salvo el acceso de invitados BBB, que viene apagado de producción |

El script se corrió cuatro veces sobre la misma copia mientras se afinaba, y
los tiempos variaron poco: 119–139 s el salto a 4.5 y 126–152 s el salto a 5.3.

Tiempos del portátil: el VPS 4 tiene menos CPU. La Fase 3 los repite en el
servidor antes de fijar la ventana del corte.

**Aceptación del ensayo 1:**
- **Prueba de humo ✅** (capturas fuera del repo, porque llevan datos reales):
  - portada pública a 1440 y 390;
  - dashboard del admin;
  - una alumna de primaria con su tema `rmprimaria`: dashboard a 1440 y 390,
    su curso a 390 y la revisión de un intento suyo del 3 de octubre, con
    nota y fórmulas intactas;
  - repetida sobre la ejecución con la collation unificada: su curso a 1440,
    con su tema y sus actividades.
- **Falta** la lista completa: FUN-01…25 y los casos de §5.3 del plan.

**Avisos de los upgrades:** solo el *callback* `after_config` de 4.3 en el
salto a 4.5, esperado porque ese plugin es el viejo. En 5.3 ya es un *hook*.

## 5. Hallazgos que pasan a las fases 3 y 4

1. **Collation mixta → se unifica en el corte.**
   - Las 5 tablas de `local_richimath` están en `utf8mb4_general_ci` y las
     otras 483 en `utf8mb4_unicode_ci`. El `config.php` de producción dice
     `general_ci`, en dos líneas: la 18 y la 30.
   - Con ese config, las tablas que crean 4.4–5.3 saldrían en `general_ci`
     junto a core en `unicode_ci`.
   - Paso del corte, antes del primer upgrade (plan §6.2): `config.php` a
     `utf8mb4_unicode_ci` y `mysql_collation.php`. Ensayado: 5 tablas, sin
     errores, esquema OK.
2. **MySQL 8.4 sin `mysql_native_password` → no aplica.** `moodleuser` usa
   `caching_sha2_password` y `root`, `auth_socket`.
3. **Salida a internet** desde el VPS durante el corte, para los paquetes de
   idioma (sección 3).
4. **El CSS de los temas** (unos 3 min en el portátil) va dentro de la ventana,
   como tarea adhoc que deja el upgrade.
5. **Enlace de invitado BBB (FUN-19) apagado en producción**:
   `bigbluebuttonbn_guestaccess_enabled = 0` ya en 4.3. No depende de la
   migración.
6. **MIG-40 cerrado**: ni `chat` ni `survey` en producción.

## 6. Al cerrar la Fase 2

```bash
docker compose -p rmrehearsal down -v     # contenedores y volúmenes del ensayo
rm -rf ~/richimath-prod-copy              # la copia, con sus datos de alumnos
```
