# Sitio público y URL limpias

Tres piezas que llegaron juntas el 2026-09-10:

1. La **raíz** (`https://richiacademy.com`) es ahora el **sitio institucional**:
   obsidiana, oro y un neón por división, según
   [docs/design/website/DESIGN.md](../design/website/DESIGN.md).
2. El **catálogo que estaba en la raíz** se mudó entero a
   **`/students`**, con el mismo diseño que tenía.
3. Todas las direcciones propias son **una palabra en inglés**, sin `.php` ni
   `/index.php`, y salen de un **único fichero de rutas**.

## 1. El sitio institucional (la raíz)

Es un **layout de tema**, no una plantilla dentro del aula: `theme/richimath/`
`layout/site.php` + `templates/local/site.mustache`, y la sección **G** del
SCSS. Se registra en el `config.php` del tema como layout de `frontpage`; los
layouts de Moodle se heredan clave a clave, así que el resto del aula sigue con
los de Boost.

**Quién lo ve**: solo un visitante sin sesión. Con sesión abierta, el mismo
fichero delega en el layout de Boost (`require` del `drawers.php` del padre), de
modo que el aula no cambió ni un pixel.

**Qué es real y qué es texto editable**:

| Bloque | De dónde sale |
|---|---|
| Las tres divisiones | Las **categorías de primer nivel** del sitio, en orden, con sus cursos reales como etiquetas y el enlace a la categoría |
| ~~La consola de acceso~~ | **Retirada el 2026-10-03.** En su lugar, el sistema solar de niveles ([levels-solar-system.md](levels-solar-system.md)). Se entra con la llave **Aula virtual** de la barra, que abre la página de acceso de Moodle |
| El sistema solar y el menú **Niveles** | Los **cinco niveles** resueltos sobre las categorías reales, con sus cursos — ver [levels-solar-system.md](levels-solar-system.md) |
| Titular, bajada, cifras, método, pie | **Cadenas de idioma** del tema (`site*`, `division*`) — se editan en *Administración del sitio → Idioma → Personalización*, sin desplegar |
| ~~La franja de imagen bajo la portada~~ | El contenido de portada de Moodle **no se muestra** desde el 2026-09-13: repetía la marca del héroe. El marcador sigue en el HTML porque Moodle lo exige; la caja está oculta en la sección G del SCSS |

**Las cifras del héroe son declaraciones de la academia**, no datos medidos, y
viven en las cadenas `sitestat*`. Richi las ajustó a la realidad el 2026-09-13:
**+100** alumnos, **+90 %** de ingreso, **5+** catedráticos, aula **24/7**.
Cámbialas o vacíalas cuando cambie el dato; el sistema no las calcula y no debe
fingir que sí.

**El neón por división** es la única parte cromática que el sistema reserva:
verde fósforo para Escolar, cian eléctrico para Preuniversitario y carmesí para
Universitario. Nada más en la página puede usar esos tres tonos.

Lo oscuro **no se filtra al aula**: el token `$richimath-skin`, que prefija toda
la piel del aula, excluye `body.richimath-site`. Los cuatro temas por nivel
siguen claros.

## 2. `/students`

La portada anterior se movió tal cual: héroe con el nombre del sitio, el lema,
la llamada a la acción y el catálogo real en pestañas. Cambió de sitio, no de
diseño.

- Página: `local/richimath/students.php`.
- Plantilla: `theme/richimath/templates/local/students.mustache` (salió de
  `theme_boost/drawers.mustache`, con su JS de pestañas).
- Estilos: la sección **A2** del SCSS, que antes colgaba de
  `body.pagelayout-frontpage` y ahora de `body.richimath-has-landing`.

## 3. Las URL limpias

**El fichero de rutas es la fuente de verdad**:
[`local/richimath/routes.php`](../../local/richimath/routes.php). Una línea por
dirección. Nadie más conoce el mapa: `local_richimath\routes` construye enlaces
a partir de él y `scripts/build-routes.php` escribe las reglas de Apache.

```bash
php scripts/build-routes.php          # reescribe el bloque del .htaccess
php scripts/build-routes.php --print  # lo imprime, sin tocar nada
```

Cada ruta funciona **en las dos direcciones**, y esa es la parte que importa:

1. **La dirección limpia se sirve en el sitio** (rewrite interno de Apache), así
   que se queda en la barra durante toda la visita.
2. **El script real redirige (301) a la dirección limpia** en cuanto un
   navegador lo pide. Eso es lo que saca el `.php` de la barra aunque el enlace
   lo haya construido Moodle: `/login/index.php` te deja en `/login`, `/my/` en
   `/dashboard`, `/user/profile.php` en `/profile`.

Dos guardas hacen que eso no se rompa, y las dos están en el generador:

- `RewriteCond %{ENV:REDIRECT_STATUS} ^$` — solo redirige lo que viene de
  fuera. Sin esto, el rewrite del punto 1 volvería a entrar por el 301 del
  punto 2 y el navegador giraría en un bucle.
- `RewriteCond %{REQUEST_METHOD} !=POST` — **un POST nunca se redirige**. El
  navegador convierte un POST redirigido en GET y tira los datos del
  formulario: cada login y cada formulario del sitio se romperían.

Y una tercera, en el bloque `mod_dir`: **`DirectorySlash Off`**. `login` es un
directorio real, así que Apache redirigía `/login` a `/login/` antes de que
nuestras reglas llegaran a servirlo.

| Dirección | Página |
|---|---|
| `/students` | catálogo de cursos |
| `/join` | aceptar invitación |
| `/invitations` `/plans` `/members` | invitar, planes, usuarios y planes |
| `/login` `/logout` `/signup` `/recover` | acceso, salida, alta, recuperar |
| `/dashboard` `/mycourses` `/courses` | área personal, mis cursos, catálogo |
| `/calendar` `/grades` `/messages` `/notifications` | el aula |
| `/profile` `/preferences` `/search` `/admin` | cuenta y administración |

**Los formularios también apuntan a la dirección limpia**: las páginas propias
llaman a `$PAGE->set_url(routes::url(...))`, con lo que Moodle construye sus
formularios sobre la dirección corta. El formulario de acceso es la única
excepción escrita a mano (`{{{ config.wwwroot }}}/login` en
`core/loginform.mustache`): su contexto lo arma el core y no lleva renderer.
Sin eso, un acceso fallido se re-dibujaba en `/login/index.php` — que es
exactamente lo que se veía en la barra.

### La trampa del «¿a dónde iba?»

Moodle recuerda a dónde ibas en `$SESSION->wantsurl` y, en el POST del acceso,
lo rellena con el **referer**. Se niega a guardar ahí la propia página de
acceso… pero solo conoce **sus** formas de escribirla (`/login/` y
`/login/index.php`). La nuestra es una tercera, así que entrar desde `/login`
guardaba `/login` como destino: el acceso funcionaba, Moodle te devolvía a
`/login`, y ahí una sesión abierta se responde con «*ya inició sesión como…,
necesita salir antes de volver a entrar*». Parecía que la contraseña fallaba
y no fallaba nada.

Lo arregla `local_richimath_after_config()` (en `local/richimath/lib.php`), que
corre en cada petición justo después de abrirse la sesión: si `wantsurl` apunta
a la dirección limpia de acceso, la borra. Los destinos de verdad no se tocan —
entrar desde `/grades` sigue devolviéndote a `/grades`.

### El límite honesto

Lo que **sí** queda limpio, y es casi todo lo que se ve: cualquier página del
mapa acaba en su palabra, la escribas tú, la enlace Moodle o la envíe un
correo. Un enlace interno a `.php` sobrevive un salto y termina en la
dirección corta.

Lo que **no** puede: las páginas que se identifican por un **id** —
`/course/view.php?id=31`, `/mod/quiz/view.php?id=199`, una tarea, un foro—.
Una palabra no puede nombrar a ochenta cursos distintos; harían falta *slugs*
por curso, que es otra funcionalidad (y datos nuevos en la base), no una
regla de Apache. Si Richi la quiere, se hace: cada curso publicaría
`/courses/algebra-1`.

### El interruptor (y por qué existe)

Las URL limpias dependen del servidor web, así que **el código no da por hecho
que están activas**. El ajuste `local_richimath/prettyurls` —*Administración
del sitio → Extensiones → Herramientas Richi Math*— decide qué construye el
código:

| Interruptor | Qué emiten los enlaces | Cuándo |
|---|---|---|
| **Apagado** (por defecto) | la URL normal de Moodle (`/login/index.php`) | siempre que el vhost no esté listo |
| **Encendido** | la dirección limpia (`/login`) | cuando `/students` responde 200 |

Esto no es adorno: con el interruptor encendido y el `.htaccess` desactivado,
el formulario de acceso apunta a `/login`, que sin reglas es un **directorio
real** — Apache redirige, el navegador convierte el POST en GET y **el acceso
deja de funcionar sin decir por qué**. El orden correcto es siempre: primero el
vhost, luego el interruptor.

Los redirectores de URL fea a limpia son **302, no 301**, por la misma razón: un
301 se queda cacheado en el navegador para siempre, y apagar las rutas dejaría a
quien ya visitó el sitio pidiendo direcciones que el servidor ya no contesta.

### Qué hace falta en el servidor

Las reglas viven en el `.htaccess` de la raíz de Moodle, así que Apache tiene
que leerlo:

```apache
# /etc/apache2/sites-available/richiacademy.conf
<Directory /var/www/html>
    # FileInfo para las reglas de rewrite; Indexes para DirectorySlash Off,
    # sin el cual /login se va a /login/ antes de que las reglas actúen.
    AllowOverride FileInfo Indexes
</Directory>
```

```bash
sudo a2enmod rewrite && sudo systemctl reload apache2
```

En local ya está resuelto: el `docker-compose.yml` enciende `mod_rewrite` y
pone `AllowOverride FileInfo Indexes` al arrancar el contenedor.

## Verificado en local (2026-09-10)

- Raíz a 1440 y 390 px sin desbordes: héroe, consola, tres divisiones con sus
  categorías reales, método, llamada a la acción y pie.
- Con sesión abierta, la raíz sigue siendo el aula de Boost.
- `/students` a 1440: catálogo completo en pestañas, idéntico al que estaba en
  la raíz.
- Cada página del mapa comprobada en las dos direcciones: `/login/index.php`,
  `/my/`, `/my/courses.php`, `/course/index.php`, `/calendar/view.php`,
  `/message/index.php`, `/grade/report/overview/index.php`,
  `/user/profile.php`, `/user/preferences.php` y `/admin/search.php` terminan
  todas en su palabra.
- Acceso **fallido**: la barra se queda en `/login` con el error.
- Acceso **correcto** desde `/login` y desde la consola de la portada: entra a
  `/dashboard`, sin pasar por la pantalla de «ya inició sesión».
- Acceso **exigido por una página**: `/grades` sin sesión lleva a `/login`, y al
  entrar devuelve a `/grades`.
- Salir por `/logout` devuelve a la portada.

## Pendiente

- El **banner de portada** (el label de la sección 1) sigue en la base de
  datos y ahora aparece bajo el héroe. Si duplica el mensaje, se borra desde la
  portada con el modo edición: la página no depende de él.
- Las **fotos** del tablero de diseño (aulas, laboratorios) no existen en el
  repositorio; donde el tablero pone fotografía, la implementación usa el color
  y el logotipo. Si Richi entrega las imágenes, entran sin tocar la estructura.
- Direcciones bonitas **por división** (`/school`, `/preuniversity`,
  `/university`): hoy las tarjetas enlazan a la categoría real por id. Cuando
  las categorías tengan `idnumber` estable, son tres líneas en el fichero de
  rutas.
