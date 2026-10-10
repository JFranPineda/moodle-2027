# Fase 2 — Copia de producción y ensayo del upgrade

Cómo se saca una copia de `richiacademy.com` (VPS 4 de Contabo) y cómo se
ensaya sobre ella, en local, el upgrade **4.3 → 4.5 → 5.3** tal como se hará en
producción. Plan general: [../migration-plan.md](../migration-plan.md) §5.

La fase sale cuando el ensayo queda **en verde dos veces seguidas**, cada una
desde una copia limpia. Por eso este procedimiento se repite entero cada vez.

**Cerrada el 2026-10-10**: los ensayos 3 y 4 salieron en verde seguidos (§4).

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

### Aceptación automática

```bash
scripts/rehearsal/acceptance.sh ~/richimath-prod-copy          # ~5 min
scripts/rehearsal/acceptance.sh ~/richimath-prod-copy --bbb    # + sala real en el VPS 6
```

Escribe una línea PASS/FAIL por comprobación y termina con error si alguna
falla.

**Qué comprueba sola:**
- **Rutas limpias** en las dos direcciones.
- **Login:** error sin salir de `/login`, *reducir movimiento*, 390.
- **Portada y banner.**
- **Admin:** lista de usuarios, línea de la hora del calendario, página de
  ajustes del tema y columna de nombres del calificador.
- **Datos reales:** todas las carpetas y foros, y cada fichero en `moodledata`.
- **Invitaciones** y enlace compartido.
- **Privacidad** con un alumno real de un curso, vía «Entrar como».
- **Alumno de prueba `qa.alumno`** con inicio de sesión real: tema por plan,
  chips, sin catálogo, botones flotantes en móvil.
- **Examen completo:** temporizador, navegación, marca de agua, sin botones
  flotantes en el intento ni en el resumen, y caja de solución en la revisión.

Con `--bbb` abre una sala **nueva** y sin grabación en el servidor real, entra
como profesor y como invitado, y la cierra.

**Lo que sigue siendo a ojo:** las capturas que deja en `work/acceptance/`
(fuera del repo: llevan alumnos reales), los colores y espaciados, y el
movimiento del sistema solar.

## 4. Registro de ensayos

| Paso | Ensayo 1 · copia 1 | Ensayo 2 · copia 2 | Ensayo 3 · copia 2 | Ensayo 4 · copia 3 |
|---|---|---|---|---|
| Importar base + `moodledata` | 24 s | 30 s | 32 s | 29 s |
| Unificar collation | 4 s | 4 s (nada que convertir) | 4 s | 4 s (nada que convertir) |
| Upgrade 4.3 → 4.5 | 139 s | 173 s | 198 s | 159 s |
| Cambio a PHP 8.3 + MySQL 8.4 | 20 s | 24 s | 25 s | 20 s |
| Upgrade 4.5 → 5.3 | 148 s | 199 s | 225 s | 164 s |
| Tareas adhoc (≈ todo CSS; `mod_qbank` ≈ 1 s) | 197 s | 205 s | 240 s | 229 s |
| Personalización de idioma | 7 s | 9 s | 11 s | 12 s |
| **Total, de la collation al último paso** | **515 s ≈ 8,6 min** | **614 s ≈ 10,2 min** | **703 s ≈ 11,7 min** | **588 s ≈ 9,8 min** |
| Resultado técnico | Esquema OK; sección E: acceso de invitados BBB apagado (venía así) | Esquema OK; sección E: **tema del sitio = `rmuniversidad`** (venía así de producción, ver §5) | Esquema OK; sección E igual que el ensayo 2 (tema del sitio = `rmuniversidad`, de producción) | Esquema OK; **sección E toda en OK**, con el tema del sitio = `richimath` |
| Aceptación | A mano: 22/25 ✅, 4 arreglos | Automática: 51/51 ✅ (un fallo del propio script, corregido); a ojo: «Attempt submitted.» en inglés → 37 cadenas traducidas | Automática: **52/52 ✅** con BBB real; sin arreglos en medio | Automática: **52/52 ✅** con BBB real; sin arreglos. **Segundo verde seguido: la fase queda cerrada** |

- **Copia 1**: tomada el 2026-10-09 a las 16:43 (hora del servidor).
- **Copia 2**: tomada a las 23:54, ya con la collation unificada, el acceso de
  invitados encendido y el banner instalado.
- **Copia 3**: tomada el 2026-10-10 a las 06:37 (hora del servidor), con el
  tema del sitio ya devuelto a Richimath.

Los tiempos varían con la carga del portátil (119–198 s el salto a 4.5 y
126–225 s el salto a 5.3, entre siete ejecuciones); la ventana real sale de
medirlos en el servidor (Fase 3).

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
- **Aceptación completa (2026-10-09)**, con datos reales en `:8084`. Como
  alumno, con «Entrar como» y con `qa.alumno`, un alumno de prueba creado en la
  copia para iniciar sesión de verdad.

| FUN | Resultado | Nota |
|---|---|---|
| 01 Marca y títulos | ✅ | «… \| Richi Academy» en login, dashboard, curso, examen y categorías; ningún «Richie» |
| 02 Logos | ✅ | Cada tema sirve su logo (5 distintos). ⚠ Favicon: manda el del sitio (Apariencia → Logos), igual que en 4.3 → decisión |
| 03 Login | ✅ | Correcto, fallido (se queda en `/login`), «¿Olvidó…?», glifos parados con *reducir movimiento*, 390 sin desborde |
| 04 Rutas limpias | ✅ | 20 rutas en las dos direcciones. `/join` sin token y `/signup` (autorregistro apagado) responden con su error, como en 4.3 |
| 05 Sistema solar | ✅ | Pausa al pasar el ratón, ficha con Escape, menú «Niveles» por hover; IB ya muestra sus 2 cursos reales |
| 06 Barra lateral | ✅ tras arreglo | **La página de ajustes del tema no existía en 5.3** (ver abajo). Alumno sin enlace al catálogo; cajón móvil abre y cierra |
| 07 Calendario | ✅ | Mes, día (con la línea de la hora) y próximos eventos |
| 08 Chips | ✅ | 1 curso → 1 chip; 15 cursos → 12 + «Ver todos mis cursos» |
| 09 Paginación arriba | ✅ | La de arriba cambia de página y queda sincronizada con la de abajo |
| 10 Consultas | ✅ tras arreglo | Abre la conversación con el admin. A 390 **tapaba el botón del cajón de bloques** (ver abajo) |
| 11 Niveles y subniveles | ✅ | Tarjetas por nivel; ESCOLAR → NIVEL PRIMARIA → QUINTO/SEXTO GRADO. Producción no tiene categorías ocultas |
| 12 Página del curso | ✅ | El ⋮ de actividad y el de disponibilidad, por encima y clicables |
| 13 Iconos | ✅ | Los de Moodle en color, sin baldosa ni negros |
| 14 Evaluaciones | ✅ | Flujo completo en Universitaria (ficha, intento con temporizador, navegación, resumen, revisión) y revisiones en Primaria y Pre-U; 390: temporizador fijo y navegación por su botón |
| 15 Calificador | ✅ tras arreglo | Menús por encima. **La columna de nombres quedaba bajo la barra lateral** al desplazar (ver abajo) |
| 16 Kepler | — | No está en producción (su único H5P es «Test Actividad» y no hay preguntas `calculated`). Los recursos están en el repo |
| 17 Tema por plan | ✅ | Inicio de sesión real: Secundaria → `rmsecundaria`; cambio a Universitaria en `/members` → `rmuniversidad` al volver a entrar |
| 18 Invitaciones | ✅ tras arreglo | Invitación → cuenta → matriculado; el enlace compartido rechaza un correo fuera de la lista. **`user_create_user()` está obsoleta en 5.3** (ver abajo) |
| 19 Invitado BBB | ✅ | Registro → lead → «Entrar a la clase» → sala real del VPS 6; mismo correo = 1 fila con 2 visitas; el CSV solo trae a quien aceptó |
| 20 WhatsApp | ✅ | Profesor activado + curso «Lo que decida el profesor» → botón; «No mostrar» → sin botón. En producción nadie lo tiene configurado |
| 21 Privacidad | ✅ | Las 8 pruebas con una alumna real; la búsqueda de mensajes encuentra al profesor y no al compañero |
| 22 Lista de usuarios | ✅ | «Lista de usuarios» |
| 23 Despliegue | ⏳ | Es del servidor: Fase 3 |
| 24 Servidor BBB | ✅ | Desde 5.3, el admin crea y entra a una sala en el VPS 6 (actividad nueva, sin grabación, cerrada después: las de la copia comparten sala con producción) |
| 25 Banner | — | Producción no tiene banner (solo existió en el espejo 4.3) → decisión |

**Casos con datos reales (§5.3 del plan):**
- **Temas por nivel:** cada nivel muestra su tema.
- **Cursos, notas e intentos:** siguen ahí. Producción no tiene tareas.
- **Carpetas, foros y ficheros:** las 152 carpetas y los 27 foros abren. Los 281 ficheros están en `moodledata` con su tamaño, y una muestra se descarga con 200 y tamaño exacto.
- **BBB:** ✅ (FUN-19 y FUN-24).
- **Privacidad:** ✅ (FUN-21).
- **Sección E:** ✅.
- **Correo y cron:** son del servidor (Fase 3/4).

**Arreglado durante la aceptación** (por la regla del plan, el ensayo se repite entero):
1. **Página de ajustes del tema.** 5.3 crea la página de cada tema oculta y solo la lista si el tema la desoculta. Apariencia → Temas → Richimath daba «Error de sección».
2. **Calificador.** 5.3 desplaza la página entera en horizontal y fija la columna de nombres en el borde de la ventana, debajo de nuestra barra. Ahora se fija a su derecha. El índice del curso no necesita hueco: `drawers.js` lo aparta.
3. **Botones flotantes en móvil.** 5.3 sube los botones de cajón a `calc(99vh - navbar × 2.5)`, justo donde estaba «Consultas». Con ese botón en la página, «Consultas» y WhatsApp van encima.
4. **Cuentas de invitados.** `accept.php` usa `\core\user::create_user()` en vez de la obsoleta `user_create_user()` (MDL-82650). Nada más de nuestro código usa API obsoleta de 5.3, comprobado contra `deprecatedlib.php` y los atributos `#[deprecated]`.

**Decisiones de Richi (2026-10-09), ya aplicadas:**
1. **Favicon:** el que gestiona Moodle, en todos los niveles: el icono RM de
   Apariencia → Logos (como hoy en 4.3). Sin favicon por nivel.
2. **Banner (FUN-25):** se instala. En la copia ya está, y en 5.3 se veía con una
   franja vacía encima (la cabecera de sección lleva `.d-flex`, con
   `!important`): corregido. En producción se instala ya con el script (abajo),
   y viaja con la base.
3. **Botones flotantes:** fuera durante el intento y su resumen («Consultas» y
   WhatsApp); vuelven en la revisión. Lo decide el *renderer*, no el CSS.

**Instalar el banner en producción (4.3), en el VPS:**

```bash
cd /var/www/html
sudo -u www-data php scripts/set-frontpage-banner.php /var/www/html/assets/frontpage-banner.jpg
```

La ruta va absoluta: el CLI de Moodle se mueve a la carpeta del script al
arrancar (`lib/setup.php`), y el script de 4.3 todavía no lo compensa (el de
5.3 sí).

## 5. Hallazgos que pasan a las fases 3 y 4

1. **Collation mixta → unificada en producción el 2026-10-09.** Las 5
   tablas de `local_richimath` y el `config.php` estaban en
   `utf8mb4_general_ci`, y las 483 de core en `utf8mb4_unicode_ci`. Se
   unificó en el VPS (~1 min de mantenimiento): 488/488 en `unicode_ci` y
   esquema OK (plan §6.2). El paso «Unificar collation» del ensayo sigue
   haciendo falta en las copias anteriores a esa hora; en las posteriores no
   convierte nada.
2. **MySQL 8.4 sin `mysql_native_password` → no aplica.** `moodleuser` usa
   `caching_sha2_password` y `root`, `auth_socket`.
3. **Salida a internet** desde el VPS durante el corte, para los paquetes de
   idioma (sección 3).
4. **El CSS de los temas** (unos 3 min en el portátil) va dentro de la ventana,
   como tarea adhoc que deja el upgrade.
5. **Enlace de invitado BBB (FUN-19)**: `bigbluebuttonbn_guestaccess_enabled`
   estaba en 0 ya en 4.3. Encendido en producción el 2026-10-09; las copias
   anteriores lo traen apagado.
6. **MIG-40 cerrado**: ni `chat` ni `survey` en producción.
7. **Tema del sitio en producción = `rmuniversidad`** (copia 2): lo cambió el
   usuario probando la interfaz y lo **devolvió a Richimath** el mismo día. No
   quedó en `config_log`, porque el selector de temas no deja rastro. Con sesión
   iniciada no se nota: cada usuario tiene el tema de su plan, y el plan Admin
   usa el aspecto Universidad. Solo lo ven los visitantes sin sesión. La
   copia 3 ya lo trae en `richimath`, y el ensayo 4 dejó la sección E toda en
   OK.
8. **El paquete español de 5.3 aún no traduce todo**: la versión acaba de salir.
   37 cadenas que ven alumnos o profesores van traducidas en
   `assets/customlang/es/` y se importan en el corte. Antes del corte conviene
   repetir el recuento, por si el paquete trae más o menos huecos:

   ```bash
   docker compose -p rmrehearsal exec -T web php -r 'define("MOODLE_INTERNAL", 1);
     $string = []; include "/var/www/html/public/mod/quiz/lang/en/quiz.php"; $en = $string;
     $string = []; include "/var/moodledata/lang/es/quiz.php";
     print_r(array_keys(array_diff_key($en, $string)));'
   ```
   Se cambia `quiz` por el componente que se quiera revisar. Con el ensayo ya
   borrado (§6), el mismo comando sirve en `:8083` sin `-p rmrehearsal`, desde
   la raíz del repo: monta `moodledata` en la misma ruta.

## 6. Al cerrar la Fase 2

```bash
docker compose -p rmrehearsal down -v     # contenedores y volúmenes del ensayo
rm -rf ~/richimath-prod-copy              # la copia, con sus datos de alumnos
```
