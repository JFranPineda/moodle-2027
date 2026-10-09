# Iconos de actividad en color

Los iconos de actividades y recursos (en el curso, en el selector «Añadir una
actividad o un recurso», en bloques y cabeceras) son **los dibujos de Moodle,
en colores vivos y sin la baldosa de fondo**.

## Cómo se hizo, sin tocar core

- **El dibujo es el de Moodle.** `scripts/build-activity-icons.py` lee el
  `mod/<módulo>/pix/monologo.svg` de cada módulo instalado y lo rellena con un
  degradado de dos colores. El resultado va a
  `theme/richimath/pix_plugins/mod/<módulo>/monologo.svg`, que el tema usa en
  lugar del de core. Los cuatro temas de nivel lo heredan.
- **Sin baldosa.** Core pinta el icono de blanco (`filter`) sobre un cuadrado
  del color de su propósito. La sección J del SCSS quita las dos cosas.
- **BigBlueButton** es el único icono de marca (disco con una «b» blanca): se
  recolorea el disco y la letra se queda blanca.

## Colores

Un degradado distinto por módulo (paleta en el script). Cada color mantiene al
menos **3:1 de contraste sobre blanco** (WCAG para elementos no textuales): los
tonos más claros de cada familia —cian `#06B6D4`, ámbar `#F59E0B`, verde
`#22C55E`— caen a ~2,3:1 y sus trazos finos desaparecen, así que se usan sus
hermanos más intensos.

## Mantenimiento

```bash
python3 scripts/build-activity-icons.py
```

Volver a correrlo **después de actualizar Moodle** (p. ej. la migración a 5.3):
los dibujos se toman de `mod/`, así que un icono rediseñado por Moodle entra
solo. El script avisa si hay un módulo instalado **sin color asignado** — sin
baldosa, ese icono se vería negro hasta añadirlo a la paleta.

Tras desplegar: purgar cachés (los iconos se sirven cacheados por tema).

## Verificado (2026-10-03, local)

- Los cinco temas (`richimath`, `rmprimaria`, `rmsecundaria`, `rmpreu`,
  `rmuniversidad`) sirven el icono en color.
- Página del curso y selector a 1440 y 390 px, como administrador.
- En el selector el icono se agranda (2,75 rem): sin baldosa, el tamaño de serie
  dejaba un trazo de 20 px en una tarjeta de 120.
