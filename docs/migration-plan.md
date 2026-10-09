# Plan de migración: Richi Math de Moodle 4.3.12 a 5.3 LTS

Cómo llevar la plataforma de `richiacademy.com` de **Moodle 4.3.12** (sin
parches de seguridad desde el 21 abr 2025) a **Moodle 5.3 LTS** (publicada el
5 oct 2026), con todo lo propio funcionando y sin perder datos de alumnos.

Lo que se migra, ticket por ticket: [migration-tickets.md](migration-tickets.md)
— 25 tickets funcionales (`FUN`, lo que ve el usuario, con sus commits) y 23
técnicos (`MIG`, cómo se porta el código).
Este documento dice **en qué orden, dónde y cómo se vuelve atrás**.

Redactado el 2026-10-08.

---

## 1. Tres restricciones que dan forma al plan

**1. No se puede saltar directo de 4.3 a 5.3.** El entorno de 5.3 exige venir
de 4.4 o más (`<MOODLE version="5.3" requires="4.4">` en
`public/admin/environment.xml`). Por eso hay un **paso intermedio por 4.5
LTS**, que acepta venir desde 4.1.2.

**2. Cada versión pide un servidor distinto.**

| | Producción hoy | 4.5 LTS | 5.3 LTS |
|---|---|---|---|
| PHP | 8.2 | 8.1+ ✅ | **8.3** ❌ |
| Base de datos | MySQL 8 | MySQL 8.0 ✅ | **MySQL 8.4** ❌ |

La 4.5 corre con el servidor de hoy. Así que el orden es: **4.5 con el
servidor actual → subir PHP y MySQL → 5.3**. Nunca las dos cosas a la vez: si
algo falla, hay que saber cuál de las dos fue.

**3. La 4.5 es solo un trampolín.** Nadie usa la plataforma en 4.5: es un
`upgrade.php` que transforma la base de datos y nada más. Por eso **no se
porta el tema a 4.5**. El código propio se porta una sola vez, a 5.3.

---

## 2. Visión general

```
Fase 0  Preparar         repo moodle-2027 + entorno local 5.3            (MIG-01..03)
Fase 1  Portar código    local_richimath + temas sobre 5.3 limpia         (MIG-10..32)
Fase 2  Ensayo general   copia de producción: 4.3 → 4.5 → 5.3 en local     (todos)
Fase 3  Preparar server  inventario, snapshot, PHP 8.3, MySQL 8.4 (ensayo) (servidor)
Fase 4  Corte            ventana de mantenimiento en producción
Fase 5  Después          vigilancia, ajustes en BD, backlog
```

**Regla de oro (ya escrita en `MEMORY.md` del repo viejo):** espejo local →
arreglar → snapshot → producción. **Nunca producción primero.**

---

## 3. Fase 0 — Preparar (1–2 días)

Tickets **MIG-01, MIG-02, MIG-03**.

1. **Repositorio**: `.gitignore`, `CLAUDE.md`, `MEMORY.md`, `SUMMARY.md` y
   `docs/` traídos del repo viejo. El primer push lo decides tú (`origin/main`
   todavía no existe en GitHub).
2. **Entorno local 5.3**: Docker con PHP 8.3 + MariaDB 11.4 (o MySQL 8.4, que
   es lo que tendrá producción: preferible **MySQL 8.4** para ensayar lo mismo).
   `DocumentRoot` en `public/`.
3. **Instalación limpia de 5.3** en local, sin nuestros plugins, para tener la
   referencia de «cómo se ve core». Guardar capturas de login, dashboard,
   curso, examen y lista de usuarios: son la base para comparar en la Fase 1.

**Sale de la fase**: `/admin/environment.php` todo verde y una 5.3 limpia
funcionando en `localhost`.

---

## 4. Fase 1 — Portar el código a 5.3 (2–3 semanas)

Sobre la 5.3 **limpia** de la Fase 0, ticket a ticket, en este orden (cada
paso depende del anterior):

| Paso | Tickets técnicos | Funciones que quedan listas (`FUN`) | Por qué en este orden |
|---|---|---|---|
| 1.1 | **MIG-10** base de `local_richimath` | — (base) | Es la dependencia de todo lo demás (rutas, WhatsApp, planes) |
| 1.2 | **MIG-20** base del tema + SCSS | FUN-01 marca y títulos | Sin un tema que compile no se puede ver nada |
| 1.3 | **MIG-21** las 5 plantillas de core | FUN-03 login · FUN-06 barra lateral y cajón móvil · FUN-07 calendario · FUN-08 chips del dashboard · FUN-09 paginación de «Mis cursos» · FUN-10 «Consultas» | Login y `drawers` son el armazón de todas las páginas |
| 1.4 | **MIG-22** renderers | FUN-11 niveles y subniveles (tarjetas de categoría) | El login y el catálogo dependen de ellos |
| 1.5 | **MIG-24** los 4 temas de nivel | FUN-02 logos (los 4 de nivel + favicon) · FUN-17 tema por plan (aspecto) | Heredan del padre ya estable |
| 1.6 | **MIG-14** rutas limpias + `.htaccess` | FUN-04 sitio, `/students` y URL limpias (rutas) | Toca servidor y login: aislado y con sus pruebas |
| 1.7 | **MIG-23** sitio institucional | FUN-04 (portada) · FUN-05 sistema solar y menú Niveles · FUN-02 (marca web) | Usa rutas, renderer y `levels` |
| 1.8 | **MIG-11, 12, 13, 15, 16, 17** | FUN-18 invitaciones · FUN-17 planes (asignación) · FUN-19 **enlace de invitado BBB** · FUN-20 WhatsApp · FUN-21 privacidad | Independientes entre sí; cada una con su «Hecho cuando» |
| 1.9 | **MIG-25** revisión sección a sección del SCSS | FUN-12 página del curso y ⋮ sin parpadeo · FUN-14 **UX de evaluaciones** · FUN-15 calificador · FUN-25 banner | Al final, cuando todo el marcado ya es el de 5.3 |
| 1.10 | **MIG-26, MIG-27** decisiones con Richi | FUN-22 lista de usuarios · FUN-13 iconos | Enseñar opciones, decidir, aplicar |
| 1.11 | **MIG-30, 31, 32** scripts, recursos y manuales | FUN-16 Kepler · FUN-23 despliegue · FUN-24 servidor BBB | Cerrar rutas a `public/` y actualizar manuales |

Los 25 `FUN` aparecen en la tabla: ninguno queda sin paso asignado.

**Cómo se trabaja cada ticket**:
- Un commit por ticket (`feat(...)` / `refactor(...)` en inglés, sin
  atribuciones), con los IDs `MIG-xx` y los `FUN-xx` que cierra en el cuerpo.
- Un `FUN` se da por listo cuando cumple su «Hecho cuando» **en el navegador**,
  no cuando su `MIG` compila.
- Depuración en **DEVELOPER** durante toda la fase: un aviso de obsoleto hoy es
  un error en la próxima versión.
- Capturas a **1440 y 390 px**, como **admin y como alumno**, comparadas con
  las de 4.3 (`github/moodle` sigue funcionando en local para comparar lado a
  lado).
- Plantillas de core: **siempre** partiendo de la de 5.3 (MIG-21).

**Sale de la fase**: los 23 tickets técnicos (`MIG`) cerrados y los 25
funcionales (`FUN`) con su «Hecho cuando» cumplido sobre una base **vacía**.
Todavía sin datos reales.

---

## 5. Fase 2 — Ensayo general con datos reales (3–5 días)

Aquí se ensaya **exactamente** lo que se hará en producción, con una copia de
producción.

### 5.1 Copia de producción a local
1. En Contabo, fuera de horario: `mysqldump --single-transaction` de la base y
   `tar` de `moodledata` (sin `cache/`, `localcache/`, `sessions/`, `temp/`).
2. Bajar ambos al portátil. **Son datos reales de alumnos**: no se suben a
   GitHub ni a ningún servicio, y se borran al terminar el ensayo.

### 5.2 Los dos saltos, en local
```
4.3.12 (copia de prod)
   │  código: Moodle 4.5.x estándar + nuestros 6 plugins de 4.3 tal cual
   │  PHP 8.2 · MySQL 8.0      ← mismo servidor que prod hoy
   ▼  php admin/cli/upgrade.php --non-interactive
4.5.x
   │  cambiar a PHP 8.3 · MySQL 8.4 (upgrade in-place de la base)
   │  código: moodle-2027 (5.3 + plugins portados)
   ▼  php admin/cli/upgrade.php --non-interactive
5.3
```

- **En el salto a 4.5 los plugins van tal cual** (piden 2023100900 y la 4.5 lo
  cumple). No hace falta que el tema se vea bien en 4.5: nadie navega ahí. Si
  el tema estorbara al `upgrade.php`, se cambia el tema del sitio a Boost solo
  para ese paso.
- **Cronometrar cada paso.** El tiempo real del ensayo decide la duración de la
  ventana de la Fase 4.
- Anotar **cada aviso** del `upgrade.php`. En particular: la conversión de los
  bancos de preguntas a `mod_qbank` (5.0) y los módulos retirados
  (MIG-40).

### 5.3 Pruebas de aceptación sobre la copia
La lista completa de «Hecho cuando» de los **25 tickets funcionales**
(`FUN-01…25`), **más** estos casos que
solo existen con datos reales:

- [ ] Un alumno real de cada nivel entra y ve **su** tema (planes, MIG-12).
- [ ] Sus cursos, sus notas, sus intentos de examen anteriores y sus entregas
      siguen ahí.
- [ ] Las 95 carpetas y los 47 foros abren; los ficheros se descargan.
- [ ] El H5P de Kepler y el cuestionario dinámico funcionan (MIG-32).
- [ ] Una clase BBB se crea contra el VPS 6 y un profesor entra; un invitado
      entra por el enlace de registro (MIG-16).
- [ ] La privacidad entre alumnos sigue igual (MIG-17).
- [ ] Los ajustes de la sección E de los tickets tienen los valores esperados.
- [ ] El correo sale (SMTP Gmail) y el cron de `www-data` corre.

**Sale de la fase**: todo verde **dos veces seguidas**, partiendo cada vez de
una copia limpia de producción. Si algo se arregla en medio, el ensayo se
repite entero.

---

## 6. Fase 3 — Preparar el servidor (en paralelo a la Fase 2)

### 6.1 Inventario de producción (solo lectura)
Antes de tocar nada, en Contabo:
```bash
lsb_release -a                       # versión de Ubuntu
php -v                               # 8.2 esperado
mysql --version                      # 8.0.x esperado
mysql -e "SELECT user, plugin FROM mysql.user WHERE user = '<usuario de moodle>';"
df -h /var /var/www                  # espacio para backups (BD + moodledata ×2)
```
Más la consulta de **MIG-40** (actividades de `chat` y `survey`).

### 6.2 Trampas del servidor ya conocidas
- **MySQL 8.4 desactiva `mysql_native_password` por defecto.** Si el usuario
  de Moodle se autentica con ese plugin, después de la subida **no podrá
  conectar**. Antes del corte, pasarlo a `caching_sha2_password` (y probar que
  PHP conecta) o, como último recurso, reactivar el plugin en `my.cnf`.
- **Ubuntu 22.04 no trae MySQL 8.4**: hay que añadir el repositorio APT
  oficial de MySQL. La subida 8.0 → 8.4 en el sitio está soportada, pero se
  ensaya primero (Fase 2) y con snapshot.
- **PHP 8.3 al lado de 8.2** (PPA ondrej, el mismo de hoy): instalar
  `php8.3` con las mismas extensiones (`php8.2 -m` como lista) y cambiar el
  módulo de Apache solo en el corte. Así volver atrás es cambiar el módulo de
  vuelta.
- **`max_input_vars ≥ 5000`** en el `php.ini` de 8.3 (ya fue una trampa en
  4.3).
- **`DocumentRoot` → `/var/www/html/public`** y `AllowOverride FileInfo
  Indexes` en el nuevo directorio, por el `.htaccess` de rutas.

### 6.3 Red de seguridad
- **Snapshot del VPS 4** en el panel de Contabo justo antes del corte (no
  días antes).
- Copia de la BD y de `moodledata` **fuera del VPS** (el portátil), por si el
  snapshot tampoco sirviera.

---

## 7. Fase 4 — El corte en producción (una noche)

**Cuándo**: con la Fase 2 en verde dos veces, y no antes de **2–3 semanas
después del 5 oct** (decisión ya tomada: un `.0` recién salido trae fallos).
Fecha concreta: la eliges tú con Richi, fuera de semana de exámenes. La
duración sale del cronómetro del ensayo, con un margen de ×2.

Plantilla de la ventana (horas de Lima):

| Hora | Paso | Vuelta atrás si falla |
|---|---|---|
| T−7 días | Aviso a alumnos y profesores de la ventana | — |
| T−0:30 | Último ensayo verde confirmado; repasar este plan | No empezar |
| T+0:00 | **Modo mantenimiento** (`admin/cli/maintenance.php --enable`); parar cron | — |
| T+0:05 | Backup: `mysqldump` + `tar` de `moodledata` → copia fuera del VPS | — |
| T+0:20 | **Snapshot del VPS 4** en Contabo | — |
| T+0:30 | Código **4.5.x** + plugins actuales → `upgrade.php` | Restaurar snapshot |
| T+? | **PHP 8.3** (cambiar módulo de Apache) + **MySQL 8.0 → 8.4** | Volver a 8.2 / restaurar snapshot |
| T+? | Código **moodle-2027** (5.3 + plugins portados); `DocumentRoot` → `public/`; `config.php` | Restaurar snapshot |
| T+? | `upgrade.php` → 5.3; purgar cachés | Restaurar snapshot |
| T+? | **Pruebas de humo** (abajo), con mantenimiento todavía puesto (como admin) | Restaurar snapshot |
| T+? | Quitar mantenimiento; volver a encender cron | — |

**Pruebas de humo** (20 minutos, como admin y con una cuenta de alumno de
prueba). Una muestra de los `FUN`, no todos: el resto ya se probó en el ensayo.

| # | Prueba | Cubre |
|---|---|---|
| 1 | `https://richiacademy.com` sin sesión: marca web, sistema solar, menú «Niveles» | FUN-02, 04, 05 |
| 2 | Login correcto y login **fallido** (se queda en `/login` con el error) | FUN-03, 04 |
| 3 | Dashboard con chips; barra lateral con el logo del nivel; un curso de cada nivel con su tema | FUN-02, 06, 08, 17 |
| 4 | `/course/index.php` como admin: tarjetas de categoría con sus subcategorías | FUN-11 |
| 5 | Un curso en modo edición: el ⋮ de una actividad se abre y se puede clicar | FUN-12 |
| 6 | Un examen: abrir sin enviar (marca de agua, sin líneas entre opciones) | FUN-14 |
| 7 | Una actividad BBB: abrir la página y la de «Invitar visitantes a esta sesión» | FUN-19, 24 |
| 8 | Como alumno: `/user/index.php?id=<curso>` dice «sin permisos» | FUN-21 |
| 9 | `admin/cli/cron.php` una vez a mano, sin errores | operación |

**Regla de decisión**: si una prueba de humo falla y el arreglo no es evidente
en **30 minutos**, se **restaura el snapshot** y se sale de la ventana en 4.3.
Mejor otra noche que una mañana de alumnos sin plataforma.

---

## 8. Fase 5 — Después (2 semanas)

- **Día 1**: revisar logs de Apache y de PHP, la cola de tareas
  (`admin/tool/task`), correo saliente, avisos de las primeras clases BBB.
- **Semana 1**: canal directo con Richi y los profesores para reportes;
  arreglos como hotfix sobre `moodle-2027`.
- **Verificar ajustes en BD** (sección E de los tickets) uno por uno.
- **Retirar PHP 8.2**: desinstalarlo cuando lleve 2 semanas estable;
  borrar las copias de la BD y de `moodledata` del portátil.
- **Actualizar documentación**: `MEMORY.md` del repo nuevo con lo aprendido;
  marcar `github/moodle` como **archivado** (solo lectura) en su README.
- **Actualizaciones menores de 5.3**: aplicar cada 5.3.x de seguridad. El
  procedimiento es el de la Fase 4 sin los saltos de versión.
- **Retomar el backlog** (sección F de los tickets): Lote A, IB, IA.

---

## 9. Riesgos y cómo se cubren

| Riesgo | Probabilidad | Cobertura |
|---|---|---|
| Plantilla copiada de 4.3 que «funciona» pero oculta lo nuevo de core | Alta | MIG-21: siempre partir de la de 5.3; comparar con las capturas de la 5.3 limpia |
| El usuario de BD no conecta tras MySQL 8.4 (`mysql_native_password`) | Media | §6.2: migrar el plugin de autenticación antes del corte, ensayado |
| `upgrade.php` tarda más de lo previsto en producción | Media | Cronometrado en el ensayo ×2; ventana con margen; snapshot |
| Traspaso de invitados BBB falla en silencio | Media | MIG-16: prueba anónima completa en el ensayo |
| Rutas limpias rompen el login (POST redirigido) | Baja (ya pasó una vez) | MIG-14: guardas obligatorias + prueba de login fallido en humo |
| Actividades de `chat`/`survey` en prod | Baja (0 en el espejo) | MIG-40 antes de fijar fecha |
| La paginación de arriba de «Mis cursos» desaparece sin error (el JS depende de los `data-region` de `block_myoverview`) | Media | FUN-09: probarla con más de una página de cursos |
| Los ajustes de logo propios de 5.x (*Apariencia → Logos*) compiten con nuestros logos por nivel | Baja | FUN-02: dejar esos ajustes vacíos y comprobar cada nivel |
| Un cambio de marcado de 5.3 deja sin estilo una pantalla que nadie abrió en el ensayo | Media | MIG-25 recorre las secciones A–J página por página; cada `FUN` dice qué página mirar |
| Fallos propios de un `.0` recién salido | Media | Esperar 2–3 semanas; preferir 5.3.1 si sale antes de la fecha |
| Pérdida de datos | Muy baja | Backup fuera del VPS + snapshot + ensayo dos veces |

---

## 10. Quién decide qué

| Decisión | Quién |
|---|---|
| Fecha de la ventana | Tú, con Richi |
| Iconos de core o propios (MIG-27) | Richi, viendo las dos opciones |
| Lista de usuarios: informe nuevo tal cual o adaptado (MIG-26) | Richi |
| Primer push de `moodle-2027` | Tú |
| Restaurar snapshot durante la ventana | Quien ejecute el corte, por la regla de los 30 minutos |
