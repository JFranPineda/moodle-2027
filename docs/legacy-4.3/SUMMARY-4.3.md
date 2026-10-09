# SUMMARY.md — traspaso de la sesión 2026-09-04 → 2026-09-12

Qué pasó en este canal y por qué, para arrancar la siguiente sesión sin releer
la conversación. **No sustituye a [MEMORY.md](MEMORY.md)** (estado vivo del
proyecto, acumulativo) ni a [CLAUDE.md](CLAUDE.md) (reglas de trabajo).

Orden de lectura al empezar: `CLAUDE.md` → `MEMORY.md` → este fichero.

> El traspaso de la sesión anterior (2026-08-26 → 2026-09-04: tema base,
> invitaciones, dominio y TLS, planes y apariencias) se sustituyó por este. Lo
> que sigue vigente de aquella sesión está en `MEMORY.md`.

---

## 1. Estado en una pantalla

| | Valor |
|---|---|
| Producción | **https://richiacademy.com** (Contabo VPS 4, `169.58.171.171`) |
| Local | `docker compose up -d` → http://localhost:8080 (espejo de la BD de Contabo) |
| Rama | `main`. **4 commits sin subir** (`d1e4feeb`…`00e41263`) |
| Último commit en el remoto | `69759873` |
| Versiones | `theme_richimath` y `local_richimath` **2026091100**; `rmpreu` y `rmuniversidad` 2026090500; `rmprimaria` y `rmsecundaria` 2026090402 |
| Commits de esta sesión | **27** (`21a4b013` → `00e41263`) |
| BBB | **Nada instalado todavía**: solo la guía y la decisión de VPS |

---

## 2. Qué se construyó, por bloques

### 2.1 Un logo RM por nivel

Los cuatro tableros de `docs/design/logos/LOGO_*` traen el isotipo en oro, lima,
rojo y azul. `scripts/build-theme-logos.py` recorta el monograma a
`assets/logo-*.svg` y lo rasteriza con **Chrome sin cabeza** (el arte usa
`filter="blur(5px)"`, función CSS en atributo SVG: solo un navegador la
resuelve).

Cada hijo pisa el logo del padre con `pix_plugins/theme/richimath/whitelogo.png`
— Moodle permite que un tema sustituya la imagen **de otro componente**, así que
no se tocó ni plantilla ni SCSS. De paso se arregló un 404: el **favicon no
hereda** del tema padre.

### 2.2 Cada plan con la apariencia de su nivel

Richi reportó que solo cambiaba el logo de Universidad. No era el logo: los
cinco planes se sembraron **todos con `elementary`**, así que Secundaria y Pre
Uni se veían idénticos a Primaria. `seed()` de `plan_service` ya lleva la
apariencia por nivel y el paso de upgrade `2026090403` reapunta los planes
sembrados **solo si siguen en el valor de fábrica**.

### 2.3 Login — *Crystalline Academic Glass*

`docs/ux_pro/LOGIN DE USUARIO`. Barra de identidad, tarjeta partida (marca /
credenciales) y pie de créditos, sección **A** del SCSS. Color, tipografía y
logo siguen siendo los del tema; lo único añadido fue **JetBrains Mono** para la
capa técnica.

### 2.4 Flujo de examen — *Imperial Academic Precision*

`docs/ux_pro/FLUJO DE EXAMENES`, sección **F** del SCSS, **sin tocar
`mod/quiz`**. Cinta obsidiana, esquina 0px, temporizador pegajoso, matriz de
preguntas. El oro del tablero se leyó como **rol**: `$od-exam-accent` toma la
clave de cada nivel.

### 2.5 Pre Uni y Universidad repintados

`docs/design/themes/`: Pre Uni pasa a **Red & Black** (carmín + azabache, Outfit
+ Inter, esquinas rectas 4/8px) y Universidad a **Dynamic STEM** (zafiro,
eléctrico, chispa cian, 12/16px). Antes eran dos azules casi idénticos.

### 2.6 Sitio institucional, `/students` y URL limpias

- La **raíz** es ahora el sitio institucional de `docs/design/website`
  (obsidiana + oro + un neón por división). Es un **layout de tema**
  (`layout/site.php` + `templates/local/site.mustache` + sección **G**), y con
  sesión abierta delega en el `drawers.php` de Boost: el aula no cambió.
- El catálogo que estaba en la raíz se mudó **entero** a `/students`.
- **URL limpias** desde un único mapa, `local/richimath/routes.php`.

Esto costó tres arreglos en caliente, todos ya en el repositorio:

1. El redirect estaba **al revés** (`/login` → `/login/index.php`).
2. Moodle guardaba `/login` como «a dónde iba» y devolvía al usuario ahí →
   *«ya inició sesión»* con la contraseña correcta.
3. Con el `.htaccess` desactivado, el formulario apuntaba a `/login` (un
   directorio real) y **el acceso dejó de funcionar en producción**. De ahí el
   interruptor `local_richimath/prettyurls`, **apagado por defecto**.

### 2.7 Portada: logo, cifras y banner

Logo propio en la esquina (`pix/weblogo.png`, desde
`docs/design/logos/web_logo`), cifras ajustadas a la realidad (**+100**,
**+90 %**, **5+**, 24/7) y el banner de portada **oculto** en el sitio: repetía
la marca del héroe.

### 2.8 BBB: decidido y documentado, no instalado

[`docs/bbb-server-setup.md`](docs/bbb-server-setup.md), reescrito al final en
dos partes: **Parte 1, diez pasos** de ejecución; **Parte 2**, el porqué.
Configuración versionada en [`scripts/bbb/config/`](scripts/bbb/config/).

| Decisión | Valor |
|---|---|
| Objetivo | 5 clases a la vez × (1 profesor + 10 alumnos), cámaras de alumno **apagadas** |
| VPS | Contabo **Cloud VPS 6** + **400 GB SSD**, **región América** |
| Sistema | **Ubuntu 22.04** + **BBB 3.0** (`-v jammy-300`) |
| Grabaciones | **Requisito**. Procesado a la madrugada (21:00–04:00) |
| Horizonte | ciclo en curso + anterior; lo anterior, a archivo frío |

---

## 3. Ajustes que viven en la BD (no viajan por git)

Repetir en **cada** entorno tras desplegar:

| Ajuste | Dónde | Estado |
|---|---|---|
| `local_richimath/prettyurls` | Extensiones → Herramientas Richi Math | **apagado**; encender solo cuando `/students` responda 200 |
| `frontpage` vacío | `admin/cli/cfg.php --name=frontpage --set=` | hecho en local; **pendiente en Contabo** |
| Banner de portada | label de la portada | sigue existiendo; oculto en el sitio, visible con sesión |
| Apariencia de los planes | Administración → Planes | la reapunta sola el upgrade `2026090403` |

---

## 4. Trampas pagadas en esta sesión (no reincidir)

1. **`output` es una clave del contexto, no magia**: `render_from_template($t, [])`
   deja todos los `{{{output.*}}}` vacíos.
2. **Todo layout debe emitir `output.main_content`** o Moodle se niega a
   renderizar. Ocultar la caja por CSS sí vale; quitar el marcador no.
3. Usar **`{{{ output.doctype }}}`**, no `<!DOCTYPE>` literal, o sale aviso de
   debug.
4. **El favicon no hereda** del tema padre (`resolve_image_location` devuelve
   `$this->dir/pix/favicon.ico` sin mirar arriba).
5. **`pix_plugins/<tipo>/<plugin>/`**: un tema puede pisar la imagen de otro
   componente. Así se hizo el logo por nivel sin tocar plantillas.
6. **Boost fija `.login-container` con `width:500px !important`**.
7. **`.que .info` va flotado 7em** en Boost; para la franja superior hay que
   soltar el `float` primero.
8. **`.trafficlight` de la matriz de preguntas** es una capa absoluta blanca a
   pantalla completa que tapa el número: liberarla del `top`.
9. Core estiliza la matriz como **`.path-mod-quiz #mod_quiz_navblock`** (id +
   clase): cualquier selector más corto pierde.
10. **Los redirects 301 se cachean para siempre** en el navegador. Por eso las
    rutas usan **302**: apagarlas dejaría a los visitantes pidiendo URL muertas.
11. **Un POST redirigido pierde los datos** (el navegador lo convierte en GET):
    `RewriteCond %{REQUEST_METHOD} !=POST` es obligatorio.
12. **`DirectorySlash Off`** hace falta porque `login/` es un directorio real; y
    exige `AllowOverride FileInfo **Indexes**` — con solo `FileInfo`, Apache
    responde **500**.
13. **Moodle rellena `$SESSION->wantsurl` con el referer** y solo excluye *sus*
    formas de escribir el login.
14. **El entorno local corre con `themedesignermode`**: sirve el CSS por plugin
    y **en otro orden**, así que un estilo puede ganar en producción y perder en
    local.
15. **Un `<img>` rechaza un fichero servido como `text/html`**: el fuente del
    logo web se llama `code.html` y Chrome rasterizaba el icono de imagen rota.
16. Para sembrar un examen por CLI: `add_moduleinfo()` (con
    `session\manager::set_user(get_admin())`) + GIFT por `qformat_gift` +
    `quiz_add_quiz_question()`. **`add_moduleinfo()` no guarda las opciones de
    revisión**: hay que fijar los siete `review*` a mano.

---

## 5. Dónde me equivoqué, para que no se repita

- **El redirect de las rutas, al revés.** Se vio en producción, no en local.
- **Dije que ninguna versión de BBB soportaba Ubuntu 24.04.** Falso: **BBB 4.0
  lo pide**. Solo probé `noble-300`, que no existe, y no `noble-400`. Lo cierto
  y más útil: 4.0 está en desarrollo y sirve una *release candidate*
  recompilada a diario.
- **Dejé el código dependiendo del `.htaccess`.** El interruptor de emergencia
  que yo mismo di rompió el acceso en producción. De ahí `prettyurls`.
- **La guía de BBB se volvió ilegible** de tanto documentar decisiones antes de
  los pasos. Reescrita en dos partes.

---

## 6. Pendientes

### Inmediato
- [ ] **`git push`** (4 commits).
- [ ] En Contabo: `bash /var/www/html/scripts/deploy-contabo.sh`.
- [ ] **Apache**: `a2enmod rewrite` + un `conf-available` con
      `AllowOverride FileInfo Indexes` sobre `/var/www/html`. **Ojo: el vhost
      que sirve HTTPS es `000-default-le-ssl.conf`**, no el de :80.
- [ ] Cuando `/students` responda 200: encender `prettyurls`.

### BBB
- [ ] Contratar el VPS 6 (región **América**, 400 GB, Ubuntu 22.04) y seguir la
      Parte 1 de la guía.
- [ ] Decidir con Richi **cuántos meses dura un ciclo**: de eso depende si el
      horizonte de grabaciones cabe en 400 GB.

### Producto
- [ ] ¿Borrar el banner de portada de la BD? (hoy solo está oculto en el sitio).
- [ ] Fotos reales para el sitio: donde el tablero pone fotografía, hay color y
      logotipo.
- [ ] Direcciones por división (`/school`, `/preuniversity`, `/university`):
      hoy las tarjetas enlazan a la categoría por id.

### Seguridad (arrastrado desde agosto)
- [ ] **Rotar la contraseña de la BD**, expuesta en el historial de git.
- [ ] Rotar las contraseñas de `richi85` y `estudiante.demo`.
- [ ] Moodle 4.3 fuera de soporte → planificar 4.5 LTS.

---

## 7. Cómo verificar que todo sigue en pie

```bash
cd ~/Documentos/github/moodle && docker compose up -d
docker compose exec -T -u www-data web php /var/www/html/admin/cli/upgrade.php --non-interactive
docker compose exec -T -u www-data web php /var/www/html/admin/cli/purge_caches.php
```

En el navegador, **de incógnito**:

| URL | Qué debe pasar |
|---|---|
| `/` | Sitio institucional oscuro, tres divisiones con categorías reales |
| `/students` | El catálogo en pestañas |
| `/login` | Entrar deja en `/dashboard`; fallar se queda en `/login` con el error |
| `/mod/quiz/view.php?id=199` | Flujo de examen con la cinta obsidiana (local) |

Y con los cuatro planes, que cada nivel traiga su logo y su paleta.

**Regla de verificación del repo**: nada se da por hecho sin captura de
navegador real a **1440 y 390 px**, como admin y como alumno.
