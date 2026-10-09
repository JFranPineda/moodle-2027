# Portada con sesión: banner cambiable y categorías en tarjetas

Pregunta de origen (2026-08-29): al entrar como admin, entre el título
«AULA VIRTUAL Richi Math» y la lista de categorías no había nada — ¿se puede
poner una imagen o algo? Decisión: **sí, banner en la sección principal de
la portada, cambiable por Richi cuando quiera**, y de paso la pantalla se
vistió con el tema.

Solo afecta a la portada **con sesión** (`/?redirect=0`, enlace «Página
Principal»). La landing pública es del tema y no cambia — ver
`theme-richimath.md` → «Landing pública».

## Qué hay ahora

1. **Banner** (`assets/frontpage-banner.jpg`, 1400×480: logo RM, «¡Bienvenidos
   a tu aula virtual!», lema y cubos dorados sobre navy). Vive como un
   recurso **Área de texto y medios** en la sección 1 de la portada — es
   contenido de la BD, no del tema — y el tema (sección D6 del SCSS) lo pinta
   como imagen limpia con esquinas redondeadas, sin la tarjeta de sección que
   dejaba una banda vacía encima. En modo edición la tarjeta vuelve para que
   los controles tengan dónde estar.
2. **Categorías en tarjetas**: la lista «Categorías» de la portada reutiliza
   las tarjetas del catálogo (`/course/index.php`, sección C9): una tarjeta
   por nivel (ESCOLAR / PRE UNIVERSITARIO / UNIVERSIDAD) con sus
   subcategorías dentro, 3 columnas en escritorio y 1 en móvil. El admin ve
   además «Categoría 1» atenuada porque está oculta y vacía: borrarla por UI
   cuando Richi confirme.

Verificado en local a 1440 y 390 px, como admin y como alumno.

## Estado 2026-08-31

- En producción Richi ya cambió el banner **por UI** (logo RM rojo + «Richi
  Math — Bienvenido a tu aula Virtual!!»): el mecanismo funciona tal cual.
- Nuevo arte entregado: `assets/frontpage-cover.png` (llegó como
  `tema_portada.png`, 800×400, rayos naranja sobre negro con «Richi Math»).
  Previsualizado en local como banner (`assets/frontpage-cover.jpg`, JPEG
  optimizado) a 1440 y 390: contrasta bien con la tarjeta clara; el texto
  «Richi Math» de la esquina queda tenue. A 800 px de ancho se ve algo suave
  en escritorio (la tarjeta lo muestra a ~750 px): si se adopta, pedir el
  original a ≥1400 px. Para ponerlo en prod:
  `sudo -u www-data php scripts/set-frontpage-banner.php /var/www/html/assets/frontpage-cover.jpg --alt="Richi Math — Bienvenido a tu aula virtual"`
  + purge (reemplaza el banner actual de Richi).

## Cambiar el banner más adelante (Richi, por UI)

Portada → *Modo de edición* → en el banner, menú ⋮ → **Editar ajustes** →
en el editor, clic sobre la imagen → icono de imagen → **Examinar
repositorios** → subir la nueva → *Guardar imagen* → *Guardar cambios y
volver*. Cualquier imagen apaisada sirve; la proporción recomendada es
~3:1 (1400×480 o 1600×550) para que en el móvil el texto siga leyéndose.
Si prefiere que el banner no tenga texto horneado, puede escribir el texto
debajo de la imagen en el mismo recurso.

Regenerar el banner de la marca: editar `assets/frontpage-banner.html`
(HTML + CSS; usa los assets del tema) y renderizarlo a 1400×480 con
cualquier navegador (`agent-browser` en la sesión de trabajo hizo
`set viewport 1400 480` + `screenshot`), guardar como JPEG calidad 88.

## Instalar / reemplazar el banner por línea de comandos

`scripts/set-frontpage-banner.php` crea el recurso si no existe (o adopta el
único que haya en la sección) y lo marca con el idnumber
`richimath-frontpage-banner`; las siguientes ejecuciones reemplazan la imagen
sin duplicar nada. Rutas absolutas:

```bash
# Local
docker compose exec -T -u www-data web php /var/www/html/scripts/set-frontpage-banner.php \
  /var/www/html/assets/frontpage-banner.jpg \
  --alt="¡Bienvenidos a tu aula virtual! Explora, aprende y domina las matemáticas con Richi Math"

# Contabo (como www-data, tras el deploy)
cd /var/www/html && sudo -u www-data php scripts/set-frontpage-banner.php \
  /var/www/html/assets/frontpage-banner.jpg \
  --alt="¡Bienvenidos a tu aula virtual! Explora, aprende y domina las matemáticas con Richi Math"
sudo -u www-data php admin/cli/purge_caches.php
```

El `--alt` es lo que leen los lectores de pantalla y el nombre del recurso.

## Otras opciones que siguen disponibles (sin código)

- *Administración → Página principal → Ajustes → Elementos de la página
  principal (usuarios registrados)*: añadir «Cursos matriculados» o
  «Buscador de cursos» a la portada. ⚠️ No tocar el ajuste de la **portada
  sin sesión** (debe seguir vacío): sostiene la landing pública.
- Más recursos en la misma sección (una *Página* con el calendario del ciclo,
  un *Foro* de anuncios): el tema solo «desnuda» las imágenes de los recursos
  Área de texto y medios; lo demás se ve con el estilo normal del curso.
