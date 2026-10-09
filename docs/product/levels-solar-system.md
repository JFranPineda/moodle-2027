# Sistema solar de niveles y menú «Niveles» del sitio público

La portada pública (`richiacademy.com`, sin sesión) ya no lleva formulario de
acceso. En su lugar, la academia en el centro y **cinco planetas orbitando**,
uno por nivel. En la barra superior, el menú **Niveles** muestra lo mismo en
forma de lista.

Referencia visual: el *Enjoy Learning* de rdtlearning.com/digital_learning,
llevado a la paleta obsidiana + oro del sitio.

## Qué ve el visitante

| Dónde | Qué hace |
|---|---|
| **Planetas** | Giran solos; se **detienen** al pasar el ratón por el sistema. Al tocar uno se abre una ficha con los cursos del nivel. Se cierra con la ✕, con Escape o tocando fuera |
| **Menú Niveles** | Al pasar el ratón (o tocar, en pantallas táctiles) se abre el panel. A la izquierda los cinco niveles con su número de cursos; a la derecha, los cursos del nivel bajo el puntero, agrupados |
| **Cursos** | Son **solo informativos**: texto, no enlaces. Para entrar a un curso hay que ingresar al aula virtual |

**Para iniciar sesión**: la llave dorada **Aula virtual** de la barra, que abre
la página de acceso de Moodle (con su token, sus errores y el «olvidé mi
contraseña»).

En el **móvil** el menú de la barra no aparece (la barra del sitio público
nunca tuvo menú en pantallas pequeñas): los planetas cumplen esa función, con
etiquetas cortas («Pre-U», «Universidad»).

## De dónde salen los datos

Todo es **dinámico**: se lee de las categorías y cursos reales en cada carga
(`theme_richimath\levels`). Un curso nuevo, renombrado u oculto se refleja en
la siguiente visita. **Nada se guarda aparte.**

Los niveles no coinciden con el árbol de categorías —Primaria y Secundaria
están dentro de ESCOLAR; Pre universitario y Universitario están arriba—, así
que cada nivel **busca** su categoría:

1. **Por número ID** (prioridad): la categoría cuyo *Número ID* es la clave del
   nivel: `primaria`, `secundaria`, `preuniversitario`, `universitario`, `ib`.
2. **Por nombre**, si ninguna tiene ese número ID: la categoría menos profunda
   cuyo nombre contenga PRIMARIA / SECUNDARIA / PRE…UNIVERSI / empiece por
   UNIVERSI / BACHILLERATO o IB (sin importar mayúsculas ni tildes).

Hoy los cuatro primeros se encuentran por nombre. **IB todavía no tiene
categoría**: su planeta y su pestaña dicen «Próximamente». Cuando la academia
cree la categoría (al mismo nivel que las demás, según lo acordado), aparecerá
sola. Si el nombre elegido no contiene «Bachillerato» ni «IB», basta con poner
`ib` en su *Número ID*.

Cada curso se agrupa por la ruta de subcategorías por debajo del nivel
(«UNIVERSIDAD DEL PACÍFICO · CICLO REGULAR»), porque casi todos se llaman
«MATEMÁTICA» y es la subcategoría lo que los distingue. El orden es el de la
gestión de cursos de Moodle.

Lo que ve un visitante es exactamente lo que Moodle le deja ver: **un curso o
una categoría ocultos no salen**.

## Textos editables

Nombres de los niveles, sus lemas y las etiquetas de los planetas son cadenas
del tema (`level*`, `sitelevels*`, `sitenavlevels`): *Administración del sitio →
Idioma → Personalización del idioma → theme_richimath*.

## Colores

Cada nivel lleva un neón: Primaria verde `#00ff66`, Secundaria violeta
`#b46bff`, Pre universitario cian `#00e5ff`, Universitario carmín `#ff2a4d`,
IB naranja `#ff9f1c`. Los tres primeros que coinciden con una división
conservan el suyo. Mapa `$rm-site-levels` en la sección G del SCSS.

## Ficheros

| Fichero | Qué es |
|---|---|
| `theme/richimath/classes/levels.php` | Resuelve los cinco niveles y sus cursos |
| `theme/richimath/templates/local/site.mustache` | Barra con el menú, sistema solar, fichas y el JS |
| `theme/richimath/templates/local/site_level_courses.mustache` | Lista de cursos agrupados, compartida por menú y fichas |
| `theme/richimath/scss/post.scss` §G5 y §G5b | Órbitas, planetas, fichas, menú |

## Verificado (2026-10-03, local)

- Sin sesión, a 1440 y 390 px: sin desborde horizontal.
- Datos reales: Primaria 2, Secundaria 5, Pre universitario 8, Universitario
  14, IB «Próximamente».
- Menú: pasar el ratón abre; pasar por cada nivel cambia el panel derecho.
- Planeta: abre su ficha, el foco va al botón de cerrar; Escape la cierra y
  devuelve el foco al planeta.

Sin verificar en navegador: con `prefers-reduced-motion` los planetas deberían
quedarse quietos, cada uno en su ángulo (la regla está en §G5).

## Trampas pagadas

1. **«¿Tiene ratón?» se pregunta al puntero, no al dispositivo.** La primera
   versión usaba `matchMedia('(hover: hover)')`; un portátil con pantalla
   táctil (o un navegador sin cabeza) responde que no y el menú dejaba de
   abrirse al pasar el ratón. Ahora se usa `pointerenter` con
   `pointerType === 'mouse'`, y un toque sigue funcionando como alternador.
2. **Con `:hover` puro el menú salta al primer nivel** al cruzar el hueco entre
   la lista y los cursos. Por eso el nivel activo lo fija el JS y se queda
   hasta pasar por otro.
3. **El planeta gira al revés a la misma velocidad que su brazo** para que la
   etiqueta siga derecha; las dos animaciones comparten duración y retardo
   negativo, o la etiqueta se tuerce poco a poco.
