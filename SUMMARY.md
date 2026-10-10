# SUMMARY.md — traspaso de la sesión del 2026-10-09 (Fase 2 y cambios en producción)

> **Actualizado el 2026-10-10:** la Fase 2 quedó cerrada con la copia 3 y el
> ensayo 4 (52/52, sección E toda en OK). Lo que sigue es la Fase 3 (§10).

Qué se hizo en esta sesión, por qué y dónde quedó cada cosa, para retomarla sin
volver a descubrirla.

- **Estado vivo:** [MEMORY.md](MEMORY.md). Léelo primero.
- **Este documento:** la historia.
- **Sesión anterior** (preparación y portado a 5.3, Fases 0 y 1):
  [docs/sessions/2026-09-14-port-to-5.3.md](docs/sessions/2026-09-14-port-to-5.3.md).
- **Historia de 4.3:** [docs/legacy-4.3/](docs/legacy-4.3/).

Al cerrar la próxima sesión: mover este fichero a `docs/sessions/` con su fecha
y escribir uno nuevo.

---

## 1. Contexto y reglas

- **Richi Math** (academia de matemáticas, Perú) usa **Moodle 4.3.12** en
  `richiacademy.com`, un VPS 4 de Contabo:
  - Ubuntu 24.04, PHP 8.2, MySQL 8.0.46;
  - código de producción en el commit `3c044eae` de `github/moodle`.
- Este repo es su sustituto en **Moodle 5.3 LTS**. La ruta de subida es
  **4.3.12 → 4.5 → 5.3**: 5.3 solo sube desde 4.4 o superior, y exige PHP 8.3 y
  MySQL 8.4.
- **Reglas:**
  - nunca tocar core;
  - nunca contraseñas ni datos de alumnos en el repo;
  - commits en inglés (Conventional Commits) y sin atribución;
  - **nunca `git push`**: lo hace el usuario;
  - verificar en el navegador a 1440 y 390, como admin y como alumno;
  - no usar Artifacts.

**Entornos locales:**

| URL | Qué es | Cómo se levanta |
|---|---|---|
| `:8080` | Espejo 4.3 (`github/moodle`) | `docker compose up -d` en ese repo |
| `:8083` | 5.3 de desarrollo, con datos de prueba | `bash scripts/dev-up.sh` |
| `:8084` | **Ensayo** con la copia de producción (proyecto Docker `rmrehearsal`) | `scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy` |

`:8083` y `:8084` montan el mismo árbol, pero cada uno tiene su caché. Un cambio
de SCSS se compila en los dos:

```bash
php admin/cli/purge_caches.php --theme
php admin/cli/build_theme_css.php --themes=richimath,rmprimaria,rmsecundaria,rmpreu,rmuniversidad
```

Son unos 2,5 min por entorno; los dos pueden ir en paralelo.

---

## 2. Dónde estamos

| Fase | Estado |
|---|---|
| 0 Preparar | ✅ |
| 1 Portar el código | ✅ (23/23 MIG) |
| 2 Ensayo con copia de producción | ✅ 2026-10-10: ensayos 3 y 4 en verde seguidos (52/52 con BBB real, sin arreglos). El 4, sobre la copia 3, deja la sección E toda en OK |
| 3 Preparar el servidor | Pendiente; puede ir en paralelo con la 2 |
| 4 Corte (una noche) | Fecha a fijar con Richi: no antes del **19–26 de octubre** y fuera de semana de exámenes |
| 5 Después | Dos semanas de vigilancia |

**Siguiente paso inmediato:** la Fase 3, preparar el servidor (§10). Antes,
borrar el ensayo y la copia de producción (`docs/migration/phase-2-production-copy.md` §6).

---

## 3. Cambios hechos en PRODUCCIÓN (4.3) durante esta sesión

| Qué | Cómo | Resultado |
|---|---|---|
| **Collation unificada** | En `config.php`, `dbcollation` pasó de `utf8mb4_general_ci` a `utf8mb4_unicode_ci` (está repetido en las líneas 18 y 30). Después, `admin/cli/mysql_collation.php --collation=utf8mb4_unicode_ci`, con ~1 min de mantenimiento | `Converted: 5, errors: 0`; 488/488 tablas en `unicode_ci`; esquema OK |
| Respaldo previo a la collation | `/root/moodle-backups/pre-collation-2026-10-09-1931.sql.gz` y `config.php.pre-collation-…` | Borrado (carpeta vacía en el inventario del 2026-10-10) |
| **Acceso de invitados BBB** | Ajuste global (Extensiones → BigBlueButton → Características experimentales) y en una actividad (`course/modedit.php?update=261`) | El enlace de invitado (FUN-19) ya funciona en 4.3 |
| **Banner de portada** (FUN-25) | `sudo -u www-data php scripts/set-frontpage-banner.php /var/www/html/assets/frontpage-banner.jpg` | Instalado. La ruta va absoluta porque el script de 4.3 no resuelve relativas (§9) |
| **Tema del sitio** | El usuario lo cambió a `rmuniversidad` probando la interfaz y lo **devolvió a Richimath** el mismo día | Ver la explicación debajo |
| **Copias para el ensayo** | Copia 1 (`2026-10-09-1643`, hora del servidor), copia 2 (`2026-10-09-2354`) y copia 3 (`2026-10-10-0637`) | La 1 y la 2, borradas del VPS y del portátil; la 3 queda en `~/richimath-prod-copy` hasta cerrar la fase |

**Por qué cambiar el tema del sitio «no hacía nada» con sesión iniciada:**
- Cada usuario tiene su tema según su **plan** (`/members`). Así se asigna en el
  inicio de sesión.
- El plan **Admin** usa el aspecto *university*, es decir, `rmuniversidad`.
- Los planes solo pueden elegir entre los 4 temas de nivel: no existe un aspecto
  «Richimath» base.
- Todos los usuarios reales tienen tema de nivel. El tema del sitio
  (`theme/index.php`) solo lo ven los **visitantes sin sesión**: la portada
  pública y el login.

---

## 4. Herramientas de la Fase 2 (todo en el repo)

| Pieza | Para qué |
|---|---|
| [docs/migration/phase-2-production-copy.md](docs/migration/phase-2-production-copy.md) | Cómo sacar la copia (comandos del VPS y del portátil), reglas de datos, cómo ensayar, registro de ensayos, hallazgos |
| `scripts/rehearsal/rehearse-upgrade.sh <copia>` | El ensayo completo, desde cero cada vez. Ver los pasos debajo |
| `scripts/rehearsal/docker-compose.yml` | El entorno del ensayo. Las imágenes y el código cambian en cada salto; el `config.php` del ensayo se monta encima |
| `scripts/rehearsal/acceptance.sh <copia> [--bbb]` | La aceptación automática. Ver debajo |
| `scripts/check-db-settings.php` | La sección E de los tickets, valor a valor (OK/FAIL/INFO): tema, portada, `forcelogin`, idioma, BBB, privacidad, WhatsApp, collation… **Sirve también la noche del corte, en el servidor** |
| `assets/customlang/es/` | `admin.php` («Lista de usuarios», MIG-26) más 6 ficheros con **37 cadenas** que el paquete español de 5.3 aún no trae. Se importan con `public/admin/tool/customlang/cli/import.php --lang=es --source=<ruta ABSOLUTA> --checkin` |

**`rehearse-upgrade.sh`, paso a paso:**
1. Árbol 4.5 (`github/moodle-2026`) más los 6 plugins de 4.3 en el commit de
   `version.txt`.
2. PHP 8.2 + MySQL 8.0.
3. Importa la base y `moodledata`.
4. Unifica la collation (en copias nuevas ya no convierte nada).
5. Upgrade a 4.5.
6. Cambio a PHP 8.3 + MySQL 8.4, sobre el mismo volumen.
7. Upgrade a 5.3.
8. Tareas adhoc: el CSS de todos los temas y `mod_qbank`.
9. Importa las personalizaciones de idioma.
10. Crea el admin local `qa.admin` (contraseña en `<copia>/work/qa-admin.txt`).
11. Ejecuta `checks.php`, `check_database_schema.php` y `check-db-settings.php`.

Tarda entre 10 y 12 min en el portátil. El config del ensayo lleva
`noemailever`: la copia comparte identidad con producción, pero no envía
correos.

**`acceptance.sh`:**
- Hace unas **52 comprobaciones PASS/FAIL**:
  - rutas limpias en las dos direcciones;
  - login (error en `/login`, *reducir movimiento*, 390);
  - portada y banner;
  - páginas de admin;
  - todas las carpetas y foros, y cada fichero en `moodledata`;
  - invitaciones y enlace compartido;
  - privacidad con un alumno real, vía «Entrar como»;
  - el alumno de prueba `qa.alumno` con inicio de sesión real: tema por plan,
    chips y botones flotantes;
  - el examen completo, más que la revisión salga en español.
- Con `--bbb`: abre una **sala NUEVA y sin grabación** en el servidor real (VPS
  6), entra como profesor y como invitado, y la cierra.
- Deja capturas en `<copia>/work/acceptance/` (fuera del repo: salen alumnos).
- **Lo que sigue siendo a ojo:** las capturas y los colores.

---

## 5. Ensayos y aceptación

| | Ensayo 1 | Ensayo 2 | Ensayo 3 | Ensayo 4 |
|---|---|---|---|---|
| Copia | 1 | 2 | 2 | 3 |
| Upgrade (de la collation al último paso) | 8,6 min | 10,2 min | 11,7 min | 9,8 min |
| Técnico | Esquema OK | Esquema OK | Esquema OK | Esquema OK |
| Sección E | Invitados BBB apagados (así venía) | Tema del sitio `rmuniversidad` (así venía) | Igual que el 2 | **Toda en OK** |
| Aceptación | A mano, 22/25 ✅, **4 arreglos** | Automática 51/51 (un fallo del script, corregido); a ojo, textos en inglés → **37 cadenas traducidas** | **52/52 ✅ sin arreglos** | **52/52 ✅ sin arreglos** |

Los tiempos varían con la carga del portátil. La ventana real del corte se mide
en el servidor (Fase 3).

**Lo que confirmó la copia real:**
- Ninguna actividad de `chat` ni `survey`: MIG-40 ✅ (las 5 filas de `survey`
  son plantillas de core).
- Las 152 carpetas y los 27 foros abren; los 281 ficheros están con su tamaño.
- La transferencia a `mod_qbank` va bien: unos 1 s y 7 categorías.
- Cada `upgrade.php` instala el paquete `es` de su versión. Necesita salida a
  internet.
- **FUN-16 (Kepler) no está en producción:** el único H5P es «Test Actividad» y
  no hay preguntas `calculated`. Sus recursos siguen en el repo.
- **FUN-23 (despliegue) es de la Fase 3.**

---

## 6. Arreglos de código de esta sesión

Todos en 5.3 y verificados en el navegador.

| Qué se veía | Causa | Arreglo | Commit |
|---|---|---|---|
| Cajón derecho (bloques) flotando con un hueco a la derecha | 5.3 «ancla» los cajones junto al contenido en `pagelayout-standard` y `limitedwidth`, con reglas `:has(#page…)` que suman 2 IDs | Pegado al borde con `left: auto !important`; botón de cerrar arriba a la derecha | `4e6f92eed` |
| «Abrió… Cerró…» saliéndose de la cinta negra | Rejilla `1fr 1fr` sin salto; core solo apila en pantallas estrechas | `auto-fit` al ancho real | `4e6f92eed` |
| Caja de solución del examen: texto marrón sobre lavanda, doble borde | Alerta amarilla de core más fondo del tema | Fondo **azul hielo `#f4f8ff`** con marca de agua RM. Colores por importancia: veredicto rojo/verde/naranja, título como etiqueta, pasos azules, fórmulas violetas, respuesta verde. **Colores fijos, no de la paleta del nivel** (Pre-U tiene el primario rojo). Fórmulas anchas con scroll en el móvil | `2bb60f8ad`, `2fc8b35a6` |
| Apariencia → Temas → Richimath daba «Error de sección» | 5.3 crea la página de ajustes de cada tema **oculta** | `$settings->hidden = false` | `f3912be58` |
| Calificador: nombres tapados por la barra lateral al desplazar | 5.3 desplaza la página entera y fija la columna en x = 0 | `th.header { left: var(--richimath-sidebar-width) }`; `drawers.js` aparta el índice del curso | `9016f5ab3` |
| Móvil: «Consultas» tapaba el botón del cajón de bloques (la navegación del examen) | 5.3 sube los botones de cajón a `calc(99vh - navbar × 2.5)` | Con ese botón en la página, los FAB suben encima | `9016f5ab3` |
| Aviso de obsoleto al crear cuentas de invitados | `user_create_user()` está obsoleta en 5.3 (MDL-82650) | `\core\user::create_user()`. Ningún otro uso obsoleto en nuestro código | `6724f6fe6` |
| (Decisión) «Consultas» y WhatsApp durante el examen | — | No se pintan en el intento ni en su resumen; vuelven en la revisión | `61be9605d` |
| Franja vacía sobre el banner | 5.3 pone `.d-flex` (`!important`) en la cabecera de sección | `display: none !important` | `b3487f3a2` |
| El script del banner no encontraba la imagen | El CLI de Moodle se mueve a la carpeta del script | Guarda `getcwd()` antes del `config.php` | `d5dc9374d` |
| «Attempt submitted.» y otros textos en inglés | El paquete español de 5.3 está incompleto | 37 cadenas en `assets/customlang/es/` | `fd59f3ce1` |

---

## 7. Decisiones tomadas (Richi / usuario)

- **Collation:** unificada ya en producción, en vez de esperar al corte.
- **Favicon:** el del sitio (icono RM de Apariencia → Logos) en todos los
  niveles; sin favicon por nivel.
- **Banner de portada:** instalado.
- **Botones flotantes:** fuera del intento de examen y de su resumen.
- **Fondo de la caja de solución:** azul hielo. El crema se rechazó por
  «amarillo», y entre blanco, gris perla y azul hielo se eligió el último.
- **Tema del sitio:** Richimath. El cambio a Universidad fue una prueba y ya
  está revertido.

---

## 8. Datos de producción que conviene tener presentes

- **Servidor:**
  - Ubuntu 24.04, que trae MySQL 8.0: el 8.4 vendrá del repositorio APT oficial
    de MySQL;
  - usuario de BD `moodleuser`, con `caching_sha2_password`: el 8.4 sin
    `mysql_native_password` no le afecta;
  - `root` de MySQL por `auth_socket`.
- **Tamaños:**
  - BD de 43 MB (3,6 MB comprimida);
  - `moodledata` de 220 MB (`filedir` 158 MB; 102 MB comprimido);
  - volcado en 21–27 s, `tar` en 14 s, descarga en ~25 s.
- **Contenido:**
  - folder 152, forum 27, quiz 20, BBB 4, url 1, h5pactivity 1;
  - sin tareas; 24 usuarios; plan por defecto Primaria;
  - categorías ESCOLAR, PRE UNIVERSITARIO, UNIVERSIDAD y BACHILLERATO
    INTERNACIONAL (IB ya tiene 2 cursos);
  - ninguna categoría oculta.
- **Configuración:**
  - no registrado en moodle.org;
  - SMTP de Gmail configurado;
  - `airnotifier` activo pero sin clave;
  - ningún profesor tiene configurado el WhatsApp;
  - antes de esta sesión no había personalizaciones de idioma.
- **Usuarios:**
  - **el usuario 5 de producción es una alumna real**, no `estudiante.demo` como
    en el espejo 4.3: los ids del espejo no sirven;
  - el admin `richi85` es el usuario 2.

---

## 9. Trampas pagadas en esta sesión

**Moodle 5.3:**
1. **Cajones anclados** en `pagelayout-standard` y `limitedwidth`: el `left`
   viene de `:has(#page…)` (2 IDs) y solo lo vence `!important`.
2. **La página de ajustes de cada tema nace oculta:** hay que poner
   `$settings->hidden = false`.
3. **`drawers.js` (`displaceDrawers`)** aparta los cajones al desplazar en
   horizontal.
4. **Botones de cajón en el móvil** a `calc(99vh - navbar × 2.5)`.
5. **Bootstrap 5:** `.d-flex` y `.bg-*` llevan `!important`. 5.3 puso `.d-flex`
   en la cabecera de sección.
6. **La barra secundaria es React:** el primer pintado reparte las pestañas en
   dos líneas antes de mandarlas a «Más». Esperar antes de capturar.
7. **`user_create_user()` obsoleta** → `\core\user::create_user()` (misma firma,
   misma política de contraseñas).
8. **El paquete de idioma de 5.3 está incompleto:** contar los huecos antes del
   corte (comando en el documento de la Fase 2, §5).
9. **`upgrade.php` instala solo el paquete de idioma** de su versión: el VPS
   necesita salida a internet.
10. **El upgrade encola el CSS de todos los temas** (~3 min). No purgar después,
    o se compila dos veces.

**Servidor, CLI y datos:**
11. **El CLI de Moodle hace `chdir` a la carpeta del script** (`lib/setup.php`,
    en 4.3 y en 5.3): las rutas relativas fallan. Usar rutas absolutas.
12. **El selector de temas no escribe en `config_log`:** un cambio de tema del
    sitio no deja rastro.
13. **«Entrar como» no dispara `user_loggedin`:** el tema por plan se prueba con
    un inicio de sesión real (`qa.alumno`).
14. **Las actividades BBB de la copia comparten sala con producción:** para
    probar, crear una actividad nueva y cerrar la reunión al acabar.
15. **`version.php` hace `die()` fuera de Moodle:** la versión, con `cfg.php
    --name=release`.
16. **`du` no cuenta dos veces un directorio:** usar `moodledata/*`.

**Pruebas con agent-browser:**
17. **`html { scroll-behavior: smooth }`** (y el desplazamiento dentro de
    `#page`): un clic por coordenadas fuera de pantalla llega antes de que
    termine el desplazamiento. Usar `find role … click`, Enter, o empezar a 1440.
18. **El enlace «Terminar intento…»** lo intercepta el JS: ir a su `href`. El
    modal «Enviar todo y terminar» no abre bajo automatización:
    `#frm-finishattempt.submit()`.
19. **La búsqueda de mensajes** se prueba con el servicio web
    `core_message_message_search_users`.
20. **Un elemento `position: fixed` tiene `offsetParent` nulo:** medir con
    `getBoundingClientRect`.
21. **Un `eval` largo corta por tiempo:** hacer tandas de 25 páginas.
22. **MathJax tarda segundos:** esperar antes de capturar.
23. **En Boost, la página se desplaza dentro de `#page`**, no en `window`.

**Proceso:**
24. **Editar un script mientras corre:** bash sigue con la versión vieja.
25. **`pgrep -f patrón` dentro de un comando que contiene el patrón** se
    encuentra a sí mismo.

---

## 10. Lo que queda

1. **Fase 2 ✅ (2026-10-10).** Solo queda borrar el ensayo y la copia:
   `docker compose -p rmrehearsal down -v` y `rm -rf ~/richimath-prod-copy`.
2. **Fase 3, preparar el servidor:** el usuario ejecuta en el VPS lo que se le
   prepare, siempre con un **snapshot** antes.
   - **PHP 8.3** junto al 8.2 (PPA ondrej), con las mismas extensiones
     (`php8.2 -m`) y `max_input_vars ≥ 5000`. Sin cambiar todavía el módulo de
     Apache.
   - **MySQL 8.4** desde el repositorio APT oficial (8.0 → 8.4 sobre los mismos
     datos). Ensayar con snapshot.
   - **Vhost de 5.3:** `DocumentRoot` en `public/`, `AllowOverride FileInfo
     Indexes`, `mod_rewrite`, y la regla del router a `/r.php` al **final** de
     `public/.htaccess`.
   - **`config.php` de 5.3:**
     - `routerconfigured = true`;
     - `dbcollation = utf8mb4_unicode_ci`;
     - `wwwroot = https://richiacademy.com`;
     - `config.php` y `admin/cli/` en la raíz, fuera de `public/`.
   - **Comprobar:**
     - la salida a internet a `download.moodle.org`;
     - el script de despliegue contra 5.3 (FUN-23);
     - los **tiempos reales** del upgrade en el servidor, que fijan la ventana
       del corte.
   - **Tras el corte:** cron de `www-data` y SMTP; `check-db-settings.php` en
     verde.
3. **Fase 4:**
   - fecha con Richi;
   - una noche, según la plantilla del plan
     ([docs/migration-plan.md](docs/migration-plan.md) §7);
   - **regla de los 30 minutos** para volver atrás con el snapshot.
4. **Backlog** (no es migración):
   - tema IB (`rmib`, necesita diseño);
   - generación de preguntas con IA (`generate_text`);
   - lote A (carpetas y portada de curso);
   - si el admin quiere ver el aspecto Richimath base, hace falta un 5.º aspecto
     de plan.

---

## 11. Commits de esta sesión (`github/moodle-2027`)

```
758895ba4 feat(rehearsal): rehearse the 4.3 → 4.5 → 5.3 upgrade on a production copy
66f8daa43 docs(migration): record the first rehearsal on a production copy
3b3d97c75 feat(rehearsal): unify the database collation before the first upgrade
4e6f92eed fix(theme_richimath): pin the block drawer to the window edge again on 5.3
2bb60f8ad feat(theme_richimath): give the exam solution box its own colours and watermark
e0512c158 docs(theme): record the solution box, the block drawer and their traps
2fc8b35a6 style(theme_richimath): put the exam solution on pale ice blue instead of cream
f268c9b27 docs(migration): record the collation unified and BBB guest access on in production
f3912be58 fix(theme_richimath): list the theme settings page again on 5.3
9016f5ab3 fix(theme_richimath): keep grader names and the drawer toggler clear on 5.3
6724f6fe6 fix(local_richimath): create invited accounts with core\user::create_user
823c2790f docs(migration): record the acceptance pass on the production copy
61be9605d feat(theme_richimath): keep the floating buttons out of a quiz attempt
b3487f3a2 fix(theme_richimath): drop the empty band above the home banner on 5.3
d5dc9374d fix(scripts): find the banner image from where the command runs
145a675c4 docs(migration): record Richi's three decisions after the acceptance pass
ac8834dd6 feat(rehearsal): check a rehearsal's acceptance with one command
fd59f3ce1 feat(customlang): translate the 5.3 strings the Spanish pack still lacks
ebb7eae85 docs(migration): record rehearsals 2 and 3 on the second production copy
```

El usuario subió hasta `145a675c4`; los siguientes estaban sin subir al cerrar
la sesión (comprobar con `git status`).

---

## 12. Cómo retomar

```bash
cd ~/Documentos/github/moodle-2027
less MEMORY.md                                     # estado vivo y trampas
bash scripts/dev-up.sh                             # 5.3 de desarrollo en :8083
# Con una copia nueva en ~/richimath-prod-copy (procedimiento: docs/migration/phase-2-production-copy.md §2):
scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy          # ensayo en :8084
scripts/rehearsal/acceptance.sh ~/richimath-prod-copy --bbb          # aceptación
```

**Cuentas:**
- **En el ensayo:** `qa.admin`, con su contraseña en
  `~/richimath-prod-copy/work/qa-admin.txt` (se regenera en cada ensayo), y
  `qa.alumno`, en `work/qa-alumno.txt`.
- **En `:8083`:** las cuentas QA de siempre; la contraseña está solo en la
  memoria del agente.
