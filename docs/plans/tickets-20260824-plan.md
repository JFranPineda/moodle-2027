# Plan de implementación — Tickets de Richi (2026-08-24)

> **ESTADO 2026-08-24**: ejecutados T-01, T-02, T-04, T-05, T-07, T-08, T-09, T-10.
> Pendientes de decisión de Richi: **T-03** (destino de los chips), **T-06**
> (plataforma de videollamada), **T-11** (pasarela de pago; además requiere TLS).
> Ver "Preguntas abiertas" al final.

Fuente: [docs/tickets/tickets_20260824.docx](../tickets/tickets_20260824.docx) (texto + 14 capturas).
Ejecutor previsto: **Sonnet 5**, un ticket por sesión, siguiendo este plan al pie de la letra.
Antes de tocar nada: leer `CLAUDE.md` y `MEMORY.md` del repo — ahí viven las trampas
ya pagadas (cachés, www-data, .gitignore anclado, backdrop-filter…).

## Reglas de oro para el ejecutor

1. **Nunca editar core de Moodle.** Todo va en `theme/richimath/` (SCSS/plantillas/renderer)
   o como configuración/plugin. Si un ticket parece exigir core → parar y escalar.
2. Ciclo por ticket: editar → `docker compose exec -u www-data web php admin/cli/purge_caches.php`
   → verificar EN NAVEGADOR con captura (login como `richi85` para vista admin y
   `estudiante.demo` para vista alumno) → commit (`type(scope): asunto`, sin push).
3. Cambios de plantilla/versión → bump en `version.php` + `upgrade.php --non-interactive`.
4. SCSS: reutilizar tokens (`$richimath-*`, veils, mixin `richimath-frosted`);
   sección nueva comentada al final de `post.scss`; jamás colores nuevos sueltos.
5. Strings visibles → `lang/en` (base) + `lang/es`; nunca texto duro en plantillas.
6. Verificación responsive obligatoria: 1440px y 390px, sin scroll horizontal.

## FASE 1 — Quick wins (sin decisiones pendientes)

### T-01 · Marca: "Richi Math", no "Richie Math"
La captura del login del cliente muestra el lema con "Richie Math"; la marca es **Richi Math**.
- Archivos: `theme/richimath/lang/en/theme_richimath.php` y `lang/es/…`
  (strings `welcometagline`; revisar también `choosereadme` y cualquier otra aparición
  con `grep -rn "Richie" theme/richimath docs/`).
- Verificar: login logged-out muestra "…con Richi Math".
- Riesgo: ninguno. 15 minutos.

### T-05 · Menús "tres puntos" que tapan contenido (vista admin)
Dos casos en las capturas: (a) el solape del título con el botón del índice —
**ya resuelto** en `9a09cb3c`, verificar y cerrar; (b) kebabs del informe del calificador
pegados a los títulos de columna (`.gradereport-grader-table` cabeceras).
- Acción (b): SCSS en la sección del grader — separar el kebab del texto
  (`margin-left: auto` / padding en `th .moodle-actionmenu`), inspeccionar markup real
  logged-in en `/grade/report/grader/index.php?id=35`.
- Verificar: capturas antes/después del grader con ≥2 columnas.

### T-08 · Cambiar de curso sin bajar hasta el final ("Mis cursos")
La captura marca la paginación (Mostrar 12 + flechas ‹ ›) que solo existe ABAJO.
- Decisión técnica: JS pequeño del tema (plantilla `block_myoverview/view` override NO;
  preferir JS inyectado desde una plantilla ya nuestra o `{{#js}}` en el override
  existente de calendario NO aplica → usar un override mínimo de
  `blocks/myoverview/templates/view.mustache`… **ALTO**: myoverview re-renderiza por JS;
  la vía robusta es clonar la barra de paginación al montarse, observando
  `[data-region="courses-view"]` con un MutationObserver, desde un `{{#js}}` añadido
  en `templates/theme_boost/drawers.mustache` GUARDADO por `body#page-my-index` o
  `body#page-mycourses`. Clonar `[data-region="paging-bar-container"]` arriba del
  listado; los clics se DELEGAN al original (dispatch click al control equivalente)
  para no duplicar estado.
- Verificar: con >12 cursos (BD espejo los tiene), paginar desde la barra superior
  y la inferior; ordenación y filtros intactos; sin duplicados tras cambiar filtro.
- Riesgo: myoverview es reactivo; si el clon se desincroniza, descartar y re-clonar
  en cada render (el observer ya dispara por render).

### T-10 · Chat directo con el administrador
Nativo: la mensajería de Moodle ya conecta con el admin; falta hacerla de 1 clic.
- Acción: botón flotante "Consultas" (abajo-derecha, sobre el fondo dorado) en el
  tema — anchor fijo a `/message/index.php?id=2` (id del admin richi85; leerlo de
  BD no hardcodear: exponerlo vía renderer `theme_richimath\output\core_renderer`
  con `get_admin()->id`), icono `fa-comments`, tooltip por string. Solo logueados
  y NO en la página de mensajes; scope skin (no login).
- Verificar como estudiante.demo: clic → conversación abierta con Profe Richi.
- Nota: el "?" de ayuda de Moodle vive en esa esquina — apilar los botones
  (columna con gap) para no taparlo.

## FASE 2 — Diseño (requieren gusto, no permisos)

### T-04 · Sidebar estilo Canvas (iconos con texto debajo)
Referencia: captura de Canvas (barra angosta, icono arriba + etiqueta pequeña debajo).
- Acción: variante compacta del sidebar: ancho 88px, ítems en columna
  (icono 20px arriba, label 11px debajo, centrados), tooltips fuera. Los ítems del
  loop `mobileprimarynav` no traen icono → mapa de iconos por URL en la plantilla
  (`/my/`→fa-gauge, `/my/courses.php`→fa-graduation-cap, admin→fa-gear, resto ya
  tienen). `$richimath-sidebar-width: 88px` y TODOS los offsets derivados (B2b:
  margin-left de página, drawer 240→88, stickyfooter left) siguen la variable —
  verificar cada uso con grep antes.
- **Decisión de producto para Richi**: ¿sidebar ancho actual o compacto Canvas?
  Implementar detrás de un ajuste del tema (`theme_richimath/sidebarstyle`,
  settings.php nuevo con choice wide|compact, default wide) para poder alternar
  sin redeploy. Es el primer settings.php del tema: copiar patrón de theme_classic.
- Verificar: ambos modos, 1440/1024/390, drawers no solapan, edit switch visible.

### T-03 · Accesos directos con iconos a categorías/cursos (sin scroll)
- Acción MVP: bloque HTML pre-maquetado (grid de "chips" con icono + nombre,
  clases `richimath-quicklinks` estilizadas en el tema) que Richi pega en el
  Área personal por defecto (`/my/indexsys.php`) y en Página Principal.
  Entregar el HTML listo en `docs/product/quicklinks-snippet.html` con las 3
  categorías reales + "Mis cursos". El contenido lo mantiene Richi por UI
  (los ids de categoría cambian; no hardcodear en el tema).
- Verificar: dashboard de estudiante.demo muestra la grid; clic lleva a
  `/course/index.php?categoryid=N`… **ojo**: estudiantes sin catálogo (gate
  `340bf352`) → los chips de categoría solo tienen sentido si Richi decide
  reabrir el catálogo a alumnos, o los chips apuntan a cursos concretos.
  Preguntar a Richi ANTES de maquetar: ¿chips → categorías (reabrir catálogo)
  o chips → sus cursos matriculados?

### T-02 · Landing pre-login con oferta de cursos (estilo Canvas)
- Acción: sección "Nuestra oferta" bajo el formulario del login (el template
  override ya es nuestro): 3 tarjetas de categoría (ESCOLAR / PRE / UNIVERSIDAD)
  con blurb corto + CTA "Solicita tu matrícula" (WhatsApp de Richi — string
  configurable). Estático vía strings del tema (en+es), sin BD.
- Alternativa mayor (si Richi quiere el hero completo tipo Instructure):
  activar Página Principal pública de Moodle con bloques — proyecto aparte,
  no en este plan.
- Verificar: logged-out 1440/390; que el formulario siga siendo lo primero
  visible (el hero NO desplaza el login bajo el fold).

## FASE 3 — Funcional con decisión de negocio (bloqueados hasta OK de Richi)

### T-06 · Clases en vivo >1h con el mismo enlace (BBB)
Hecho duro: el BigBlueButton gratuito integrado (créditos Blindside) limita 60 min
y NO es extensible por código — es su modelo comercial. Opciones reales:
  A) **Autoalojar BBB** en un VPS dedicado (mín. 8GB RAM; no en el Contabo actual
     junto a Moodle) → sin límites, mismo plugin nativo. Coste servidor.
  B) **Jitsi** (plugin mod_jitsi): gratuito, sin límite duro, enlace estable por curso.
  C) Seguir con enlaces externos (Meet/Zoom) como recurso URL fijo por curso.
- Recomendación: B para empezar (0 coste, 1 tarde), A si la calidad/escala lo pide.
- Ejecución (cuando Richi elija): instalar plugin → `MEMORY.md` del repo documenta
  el procedimiento de plugins de terceros (git submodule NO; copiar a `mod/` y
  commitear, con versión anotada).

### T-07 · Pizarra virtual por curso
  A) La pizarra de BBB/Jitsi durante la clase (viene con T-06).
  B) Actividad dedicada: plugin `mod_board` (post-its) — NO es pizarra de dibujo.
  C) **Excalidraw embebido** (recurso URL en cada curso, sala por curso):
     0 instalación, dibujo colaborativo real. Recomendada como MVP.
- Ejecución C: crear recurso URL "Pizarra" en la sección "Clases en Vivo" de cada
  curso (Richi lo hace por UI; entregar mini-guía en docs/product/).

### T-09 · Matricular alumnos por correo (cualquier dominio)
Moodle de serie NO matricula "por email" — busca usuarios existentes. Camino:
  1) Verificar `allowemailaddresses` vacío (sin restricción de dominio):
     `php admin/cli/cfg.php --name=allowemailaddresses` → debe estar vacío.
  2) Flujo recomendado sin plugins: *Administración → Usuarios → Subir usuarios*
     (CSV: email,firstname,lastname,course1) — crea cuenta + matricula en un paso.
     Entregar plantilla CSV + guía en docs/product/.
  3) Opción plugin: `enrol_invitation` (invitación por email con un clic) —
     tercero, evaluar mantenimiento antes de adoptar.
- MVP: 1+2 (sin código). El ticket se cierra con la guía y una prueba real.

### T-11 · Pago del padre → acceso automático del alumno
Moodle core lo soporta: **Payment API + enrol_fee** (matrícula por pago):
curso con precio → padre paga → alumno queda matriculado al instante.
- Pasarelas: core trae PayPal; para tarjetas locales Perú evaluar plugin Stripe
  (oficial de Moodle HQ) o pasarela local (Culqi/Niubiz: plugins de terceros,
  auditar antes). DECISIÓN DE NEGOCIO: cuenta de cobro, moneda (PEN), precios.
- Ejecución (tras decisión): habilitar Payment account → gateway → activar
  `enrol_fee` → configurar precio por curso → probar con estudiante nuevo.
- Trampa conocida: el flujo de pago exige HTTPS — **prerequisito: dominio + TLS**
  (pendiente de security-checklist). Sin TLS no se lanza cobro real.

## Orden sugerido de ejecución

1. T-01 (15 min) → 2. T-05 (1h) → 3. T-10 (2h) → 4. T-08 (3h) →
5. Preguntas a Richi (T-03 destino de chips, T-04 estilo sidebar, T-06/07/09/11 negocio) →
6. T-02 + T-03 + T-04 según respuestas → 7. Fase 3 según decisiones.

Cada ticket termina con: captura antes/después en el commit message o docs/,
`MEMORY.md` actualizado, y despliegue solo cuando Richi valide en local.


---

## Estado de ejecución (2026-08-24)

| Ticket | Estado | Dónde |
|---|---|---|
| T-01 marca "Richi Math" | ✅ | strings en+es del tema |
| T-02 oferta pre-login | ✅ | `loginform.mustache` + SCSS A2 + strings |
| T-03 chips de acceso directo | 🔨 en curso | decisión tomada: chips de cursos matriculados para todos + chips de categorías solo staff |
| T-04 sidebar Canvas | ✅ | dos modos: «Blackboard» (ancha) y «Canvas» (compacta); **Canvas por defecto**. En Contabo el valor ya materializado exige: `cfg.php --component=theme_richimath --name=sidebarstyle --set=compact` |
| T-05 kebabs del calificador | ✅ | SCSS D1 (kebab absoluto a la derecha, columna congelada intacta) |
| T-06 clases en vivo >1h | 📋 decidido BBB autoalojado | pendiente OK de coste (~10 €/mes VPS aparte); ver respuesta detallada en este doc |
| T-07 pizarra virtual | ✅ guía | [docs/product/pizarra-virtual.md](../product/pizarra-virtual.md) |
| T-08 paginación arriba | ✅ | JS en `drawers.mustache` + SCSS D2 |
| T-09 matrícula por correo | ✅ guía | [docs/product/matricular-por-correo.md](../product/matricular-por-correo.md) |
| T-10 botón de consultas | ✅ cerrado | Richi decidió mantenerlo visible también para el admin |
| T-11 pago → acceso | ❌ descartado por ahora | decisión de Richi 2026-08-26; retomar cuando haya dominio + TLS |

## Preguntas abiertas para Richi

1. **T-03 chips**: los alumnos ya no ven el catálogo de categorías (decisión previa).
   ¿Los chips deben llevar a categorías (habría que reabrir el catálogo) o
   directamente a sus cursos matriculados?
2. **T-06 videollamadas**: ¿autoalojar BigBlueButton (VPS aparte, coste), usar
   Jitsi (gratis, sin límite) o seguir con enlaces externos?
3. **T-11 pagos**: pasarela (PayPal / Stripe / local peruana), moneda y precios.
   Prerequisito técnico: dominio + HTTPS (hoy el sitio va por IP sin TLS).
4. **T-10 detalle**: hoy el propio admin ve el botón "Consultas" (abre su
   autoconversación). ¿Ocultarlo para él? Es una línea de código.
5. **T-04 detalle**: el sidebar compacto queda como ajuste; el valor por defecto
   es `wide`. ¿Prefiere `compact` de fábrica?
