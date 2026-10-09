# Plan: cuatro diseños en una sola plataforma (multi-tema)

Fecha: 2026-09-03. Estado: **propuesta, pendiente de tu OK**. No se ha
implementado nada de este documento todavía.

Punto de partida: `docs/design/` trae cuatro sistemas completos —
`01_elementary_school` (Primary Moodle Odyssey, ya aplicado),
`02_high_school` (EduMoodle Campus Secundaria), `03_pre_university`
(Aula Boost Academic LMS) y `04_university` (Academic Nexus). Cada uno con su
paleta, su tipografía y su logo.

## Respuesta corta a tu pregunta

**No hace falta inventar un sistema de suscripciones.** Moodle ya resuelve el
tema de cada página en este orden (`moodle_page::resolve_theme()`):

```
curso  →  categoría  →  sesión  →  usuario  →  cohorte  →  sitio
```

Con dos interruptores nativos (`allowcategorythemes` y `allowcohortthemes`)
tienes exactamente lo que pides, sin tablas nuevas:

| Dónde está el usuario | Qué manda | Por qué |
|---|---|---|
| **Dentro de un curso** | el **tema de la categoría** | el diseño pertenece al contenido: un curso de primaria se ve de primaria aunque lo abra un profesor o un alumno de la U. |
| **Fuera de un curso** (Área personal, calendario, mensajes, perfil) | el **tema de la cohorte** del usuario | ahí no hay categoría que consultar — Moodle salta ese paso — así que el nivel del alumno decide |
| Visitante sin sesión / admin | tema del sitio | la portada pública es una sola |

La **cohorte es tu «plan»**: creas cuatro (Primaria, Secundaria, Preuni,
Universidad), metes a cada alumno en la suya y le asignas su tema. Es un
concepto de primera clase en Moodle (Administración → Usuarios → Cohortes),
sirve además para matricular en lote, y el día que haya pagos, la pasarela
solo tiene que mover al alumno de cohorte. Nada que mantener por nuestra
cuenta.

**Por qué NO un campo de perfil «plan» propio**: haría lo mismo que la
cohorte pero tendríamos que escribir el código que lo lee, un formulario para
editarlo y la lógica de tema — y perderíamos las matrículas en lote que la
cohorte ya da gratis.

**Por qué NO «un tema por usuario» a secas** (`allowuserthemes`): un alumno de
secundaria que lleva un curso de preuniversitario vería el aula de preu con
piel de secundaria. El contenido debe mandar dentro del curso. Lo dejamos
disponible solo como excepción manual (accesibilidad, pruebas).

## Arquitectura de los temas

Un **tema base** con toda la ingeniería y **cuatro hijos** que solo cambian
los tokens. Hoy `theme_richimath` mezcla las dos cosas.

```
theme/richimath/          base: plantillas, renderers, secciones A-E del SCSS
  └── scss/pre.scss       tokens NEUTROS (sin paleta concreta)
theme/rm_primaria/        hijo: paleta + fuentes + logo de 01_elementary_school
theme/rm_secundaria/      hijo: 02_high_school
theme/rm_preu/            hijo: 03_pre_university
theme/rm_universidad/     hijo: 04_university
```

Cada hijo son ~5 ficheros (`config.php` con `$THEME->parents = ['richimath','boost']`,
`version.php`, `lang/`, `scss/pre.scss` con su paleta, `style/fonts.css`,
`pix/` con su logo). Las plantillas, los renderers, el sidebar, las
invitaciones y todo lo construido hasta hoy se heredan: **se escribe una vez y
sirve para los cuatro**.

Alternativa descartada: un solo tema con las cuatro paletas en variables CSS
y una clase en `<body>`. Es menos código, pero no permite fuentes ni logo
distintos por nivel (que los diseños sí piden) y multiplica el CSS que carga
cada alumno.

## Fases

**F1 — Preparar el base (½ día).** Sacar de `theme_richimath` los valores
concretos de color y fuente a variables con `!default`, para que un hijo pueda
pisarlos. Verificar que el sitio se ve igual que hoy.

**F2 — Primer hijo, `rm_primaria` (½ día).** Mover ahí la paleta Odyssey
actual. Al terminar, el sitio con `rm_primaria` activo debe ser idéntico a lo
que hay hoy: es la prueba de que el mecanismo base/hijo funciona.

**F3 — Los otros tres (1 día).** `rm_secundaria`, `rm_preu`,
`rm_universidad` a partir de sus `DESIGN.md`. Cada uno: paleta, tipografías,
logo. Revisión con capturas a 1440 y 390 de las mismas ocho vistas de la
auditoría del 2026-09-03.

**F4 — Cableado (½ día).** Activar `allowcategorythemes` y
`allowcohortthemes`; asignar tema a NIVEL PRIMARIA, NIVEL SECUNDARIA,
PRE UNIVERSITARIO y UNIVERSIDAD; crear las cuatro cohortes con su tema.
Todo por UI, vive en la BD (no viaja por git): queda documentado como paso de
despliegue, igual que el banner.

**F5 — Automatizar la cohorte (½ día, opcional).** Un observador de eventos en
`local_richimath`: cuando un alumno se matricula en un curso, si no tiene
cohorte, se le asigna la del nivel de ese curso. Mientras no exista, Richi las
asigna a mano desde Cohortes (o el CSV de alta ya admite una columna
`cohort1`).

Total: ~3 días de trabajo, sin tocar core y sin tablas nuevas.

## Decisiones que necesito de ti

1. **ESCOLAR se parte en dos** (NIVEL PRIMARIA → diseño 01, NIVEL SECUNDARIA →
   diseño 02). ¿Correcto, o Escolar entero va con uno solo?
2. **Qué ve el admin y el profe** fuera de un curso: ¿el tema del sitio
   (propongo el de secundaria como neutro) o el de su cohorte?
3. **Portada pública y login**: hoy son la marca Richi Math (logo RM, cubos
   dorados). ¿Se quedan así para todos, o quieres una portada por nivel?
4. ~~¿Un logo distinto por nivel (los `logo_*` de `docs/design/`) o el RM en los
   cuatro?~~ **Resuelto el 2026-09-04**: uno por nivel, desde los tableros de
   `docs/design/logos/`. Cómo se construye y cómo pisa al del padre:
   [un logo por nivel](../product/plans-and-appearances.md#un-logo-por-nivel).

## Riesgos y notas

- El tema de categoría **solo aplica dentro de un curso**: fuera manda la
  cohorte. Un alumno sin cohorte verá el tema del sitio — por eso F5.
- Cuatro temas = cuatro compilaciones de SCSS. La primera carga tras un purge
  es más lenta; irrelevante con el tráfico de la academia.
- Cambiar el base afecta a los cuatro hijos: es la ventaja (una corrección
  llega a todos) y el riesgo (una regresión también). La auditoría de
  contraste documentada en `theme-richimath.md` se corre sobre cada hijo.
- Moodle cachea el tema resuelto por página; tras asignar temas hay que purgar.
