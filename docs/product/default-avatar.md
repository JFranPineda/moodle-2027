# Foto de usuario: cómo ponerla (y qué se ve si no hay)

Pregunta de origen (2026-08-29): al crear un usuario, ¿se le puede poner algún
icono por defecto como imagen? Decisión de Richi: **foto real** (Opción C).
No hace falta instalar ni programar nada: es funcionalidad de Moodle,
verificada en local con el tema activo el 2026-08-29.

Sin foto, todo usuario se muestra con la silueta gris de Moodle
(`pix/u/f1.png`), aunque el formulario diga «Imagen actual: Ninguno».

## A) Al crear o editar un usuario (admin)

*Administración del sitio → Usuarios → Cuentas → Agregar un usuario* (o
*Examinar lista de usuarios → Editar*):

1. Sección **Imagen del usuario → Imagen nueva**: arrastrar la foto al
   recuadro punteado, o clic en el icono de página (**Agregar…**) → **Subir un
   archivo** → *Seleccionar archivo* → *Subir este archivo*.
2. **Actualizar información personal**.

Moodle recorta la imagen a cuadrado y genera los tres tamaños solos. JPG o
PNG; una foto de móvil normal va bien.

Verificado: foto subida a `estudiante.demo` por este camino → se ve en su
perfil, en la lista de participantes del curso y en la barra superior.

## B) Muchas fotos de golpe (admin)

*Administración del sitio → Usuarios → Cuentas → Subir imágenes de usuario*:
un ZIP con un archivo por alumno, nombrado con su **nombre de usuario**
(`juan.perez.jpg`). Se puede elegir si sobrescribir las que ya existan.

## C) Cada alumno la suya

Menú de usuario (arriba a la derecha) → **Perfil → Editar perfil → Imagen
del usuario**, mismo recuadro que en A. Los alumnos tienen ese permiso por
defecto.

## Alternativas descartadas (por si se retoman)

- **Icono Richi Math para todos** (tema): tres PNG en
  `theme/richimath/pix/u/` (`f1` 100 px, `f2` 35 px, `f3` 512 px) sustituyen
  la silueta gris para quien no tenga foto; Moodle busca primero en el tema
  activo (`lib/outputcomponents.php`, `image_url('u/f1')`). Solo `purge`, sin
  bump de versión.
- **Gravatar** (`Usuarios → Permisos → Políticas de usuario`: *Habilitar
  Gravatar* + «URL de imagen por defecto» = `identicon`): icono único por
  correo sin subir nada, a cambio de una petición a gravatar.com desde cada
  navegador.
