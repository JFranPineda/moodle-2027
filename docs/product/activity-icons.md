# Iconos de actividad en color (Moodle 5.3)

Los iconos de actividades y recursos (en el curso, en el selector «Añadir una
actividad o un recurso», en bloques y cabeceras) son **los de Moodle 5.3**:
dibujos nuevos, en color y **sin baldosa de fondo**, que también funcionan en
modo oscuro.

## Por qué los de Moodle y no los nuestros

En 4.3 Richi pidió los iconos «con el diseño de Moodle, sin fondo extra y con
colores vivos», y los hicimos nosotros: un degradado por módulo generado por un
script desde los dibujos de core. **Moodle 5.3 ya los trae así de serie.**
Decisión de Richi (2026-10-09, MIG-27): quedarse con los de Moodle.

Comparación lado a lado: [../migration/decisions/README.md](../migration/decisions/README.md).

## De dónde sale el color

Moodle tiñe cada icono con el color de su **propósito** (evaluación,
colaboración, comunicación, contenido, contenido interactivo,
administración). Esos colores son variables del tema, y **cada nivel tiene los
suyos** en su paleta:

| Tema | Fichero |
|---|---|
| Richi Math (base) | `public/theme/richimath/scss/pre.scss` |
| Primaria, Secundaria, Pre Uni, Universidad | `public/theme/rm*/scss/palette.scss` |

Variables: `$activity-icon-administration-bg`, `-assessment-bg`,
`-collaboration-bg`, `-communication-bg`, `-content-bg` e
`-interactivecontent-bg` (en 4.3 esta última se llamaba `-interface-bg`).

Para cambiar el color de un tipo de actividad en un nivel: editar esa variable
en su paleta, subir la versión del tema y purgar cachés. No hay que dibujar ni
generar nada.

## Mantenimiento

Ninguno: cada actualización de Moodle trae sus propios iconos. El script
`build-activity-icons.py` de 4.3 y los SVG de `pix_plugins/mod` no se migraron.
