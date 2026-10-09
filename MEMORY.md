# MEMORY.md — estado vivo de la migración a 5.3 (actualizar al terminar cada tarea)

Última actualización: 2026-10-09 (Fase 1 en curso: 1.1–1.8 hechos; siguiente 1.9 = MIG-25).

## Árboles en disco

| Carpeta | Qué es |
|---|---|
| `github/moodle` | Producción actual, **4.3.12**. Origen de lo que se porta. Docker local en `:8080` |
| `github/moodle-2026` | **4.5.14+ (Build 20261002)**, sin cambios. Solo para el salto intermedio de la Fase 2 (sin `public/`) |
| `github/moodle-2027` | **Este repo, 5.3.0 (Build 20261005)**. Docker local en `:8083` |
| `github/moodle-lang` | Paquetes de idioma ya descomprimidos: `es_4.5/` y `es_v5.3/es/` |

Imágenes Docker descargadas: `moodlehq/moodle-php-apache:8.2` y `:8.3`,
`mysql:8.0` y `:8.4`.

## Fases (docs/migration-plan.md)

| Fase | Estado |
|---|---|
| 0 Preparar | ✅ 2026-10-09 (MIG-01, 02, 03 + línea base en docs/migration/baseline-5.3) |
| 1 Portar código | en curso: 1.1–1.8 ✅ (MIG-10, 11, 12, 13, 14, 15, 16, 17, 20, 21, 22, 23, 24); faltan 1.9 MIG-25, 1.10 MIG-26/27 (decisión de Richi), 1.11 MIG-30/31/32 |
| 2 Ensayo con copia de producción | — |
| 3 Preparar servidor | — |
| 4 Corte | — |
| 5 Después | — |

## Hechos verificados de 5.3

- Sube solo desde **4.4+** (`public/admin/environment.xml`): ruta 4.3 → 4.5 → 5.3.
- Exige **PHP 8.3** y **MySQL 8.4 / MariaDB 11.4**.
- Librerías en `public/lib/` (no hace falta `composer`); `config.php` y
  `admin/cli/` en la raíz.

## Entorno local 5.3 (Fase 0)

- `docker compose up -d` → `db` (mysql:8.4), `web` (php-apache 8.3, docroot
  `public/`), `cron` (cada 60 s). `http://localhost:8083`.
- 5.3 instalada limpia en español; `admin/cli/checks.php` → todo OK.
- Cuentas locales: `qa.admin` (admin) y `estudiante.demo` (alumno), misma
  contraseña que en el espejo 4.3 (está en la memoria del agente, nunca aquí).
- Contenido de prueba: curso `BASE53` (id 2) y «Cuestionario base 5.3» (cmid 6).

## Trampas pagadas en la Fase 0

1. **Router crítico en 5.3**: `routerconfigured = false` ya no es aceptable
   (comprobación ERROR). `FallbackResource /r.php` NO basta: PHP responde su 404
   a un `*.php` inexistente antes del fallback, y core lo prueba
   (`/lib/exampleshimroute2.php` → 302). Va un `RewriteRule` a `/r.php` si no
   es fichero ni directorio. Con `.htaccess` propio (MIG-14) esa regla tiene que
   ir al FINAL del `.htaccess`: sus reglas sustituyen a las del `<Directory>`.
2. **El test del router lo hace el servidor contra `wwwroot`**: en Docker,
   `localhost:8083` no existe dentro del contenedor → Apache escucha también
   en 8083 dentro. En producción no pasa (el dominio resuelve).
3. **`sed` con `\*` en un `command:` de compose**: la barra se pierde entre
   YAML y bash; usar patrones sin metacaracteres.
4. **Cookies de dos Moodle en el mismo `localhost`**: se pisan aunque cambie el
   puerto → `$CFG->sessioncookie = '53'` en el `config.php` de 5.3.
5. **El generador de preguntas de core necesita PHPUnit** (no instalado): para
   preguntas de prueba, importar GIFT con `qformat_gift`, y luego
   `quiz_settings::create($id)->get_grade_calculator()->recompute_quiz_sumgrades()`
   o el intento dice que ninguna pregunta tiene calificación.

## Trampas pagadas en la Fase 1

1. **Login partido en 5.x** (`theme_boost/templates/core/login_layout.mustache`):
   panel promocional de core a la izquierda («500.000.000 usuarios»). Nuestro
   override `core/login_layout` es de una columna y NO lleva la clase
   `login-layout-right-content` (core la limita a 576px).
2. **`.icon` tope 24px en 5.3** (max-width/max-height): todo logo pintado con
   `{{#pix}}` encoge. Cada regla de logo lleva `max-height/max-width: none`.
3. **Interruptor «Modo de edición» = componente React** (`.mds-switch`,
   `mds-switch--label-start` invierte el orden con `row-reverse`).
4. **`enablemyhome` / `enablemycourses`** (nuevos): apagados en instalación
   nueva, encendidos por el upgrade de un sitio existente. En local se
   encendieron a mano para imitar producción.
5. **Propósito de actividad `interface` → `interactivecontent`** (variable
   `$activity-icon-interactivecontent-bg`).
6. **Bootstrap 5**: `.bg-*` llevan `!important` (nuestros colores de badge
   también); `.custom-select`→`.form-select`; `.form-group` ya no existe
   (filas `mb-3 row fitem`).
7. **CSS en caché del navegador tras purgar**: si una medida no cuadra después
   de un `purge_caches`, recargar la página antes de concluir que la regla no
   aplica (pasó dos veces).
8. **Plantillas copiadas**: se rehacen con `git merge-file -p nuestra base_4.3
   core_5.3` (fusión a tres bandas) y se resuelven los choques a mano.
9. `pix_plugins/mod` (iconos de 4.3) y la sección §J **fuera** hasta que Richi
   decida MIG-27: tapaban los iconos nuevos de 5.3 o los ponían negros.

## Paso 1.8 verificado sin cambios de código (2026-10-09)

- **Privacidad (MIG-17)**: participantes sin permiso, perfil de curso y de
  sitio de la compañera bloqueados, compañera fuera del buscador de mensajes,
  profesor visible. La instalación nueva aplica el bloqueo desde `install.php`.
- **WhatsApp (MIG-15)**: FAB con `wa.me/51987654321` en el curso como alumno.
- **Planes (MIG-12)**: el alumno recibe `rmprimaria` al entrar.
- **Invitaciones (MIG-11)**: enlace → cuenta con el correo invitado →
  matriculado → invitación usada.
- **Invitados BBB (MIG-16)**: registro → lead → traspaso con
  `_qf__mod_bigbluebuttonbn_form_guest_login` → core acepta y consulta la sala.
  El error final («URL using bad/illegal format») es que en local no hay
  servidor BBB: 5.3 ya no trae el servidor de pruebas por defecto.
- **Datos de prueba creados**: árbol de categorías tipo producción, 14 cursos
  `PAG*` (paginación), `estudiante.dos`, actividad BBB en `BASE53`.
- **Ajustes nuevos de 5.3 que difieren en instalación limpia**: `forcelogin=1`
  (rompía el sitio público), `enablemyhome/enablemycourses=0`. En local se
  pusieron como producción; están en la sección E de los tickets.
