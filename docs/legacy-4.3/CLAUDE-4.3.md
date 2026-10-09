# CLAUDE.md — Moodle Richi Math

> **Lee [MEMORY.md](MEMORY.md) primero**: estado vivo de los entornos, trampas
> conocidas y pendientes. Es la fuente de verdad entre sesiones — actualízalo
> al terminar cualquier tarea.

## Qué es este repo

Fork de **Moodle 4.3.12** para el aula virtual de Richi Math (academia de
matemáticas, Perú). Producción en un VPS Contabo (`169.58.171.171`, Apache +
MySQL 8 + PHP 8.2). Todo lo propio vive en el tema **`theme/richimath/`**
(hijo de Boost) — diseño tipo Blackboard Ultra con marca RM (dorado/navy).

**Regla número uno: NUNCA editar core de Moodle** (`lib/`, `course/`, `admin/`,
`mod/*` de serie…). Solo `theme/richimath/`, `scripts/`, `docs/`, y —si algún
día hace falta— plugins en sus directorios estándar. Si una tarea parece exigir
core: parar y escalar al usuario.

## Arquitectura del tema (theme/richimath/)

- `scss/post.scss` — TODO el estilo, en secciones comentadas (A login,
  B1-B4 navbar/calendario/sidebar/móvil, C1-C9 skin global). Tokens y mixin
  arriba del archivo (`$richimath-*`, veils, `richimath-frosted`). Reutilizar
  siempre; cero colores nuevos sueltos.
- `templates/` — overrides mínimos-diff: `core/loginform` (login + parcial de
  animación), `theme_boost/drawers` (sidebar, landing dinámica, chips,
  paginación clonada, FAB Consultas), `theme_boost/primary-drawer-mobile`
  (drawer móvil navy), `local/sidebar`, `local/mathfield` (animación de
  glifos compartida — constantes nombradas, tamaño responsive por --size),
  `core_calendar/day_detailed` (rejilla horaria; override de ESTE parcial y no
  calendar_day para sobrevivir la navegación AJAX), `core_calendar/calendar_month`
  (toggle, gated por iscalendarblock).
- `classes/output/core_renderer.php` — la lógica del tema: `show_catalog()`
  (catálogo staff-only), `show_offer()` + `offer_categories()` (landing
  anónima), `dashboard_chips()` (accesos rápidos; gate pagelayout +
  CONTEXT_USER), `admin_message_url()` (FAB de consultas), `body_attributes()`
  (clases richimath-has-landing / richimath-sidebar-compact).
- `settings.php` — ajuste `sidebarstyle`: «Blackboard» (ancha) / «Canvas»
  (compacta, default). Los settings de tema viven en la sección
  `themesettingrichimath` (nomenclatura core; una página propia rompe el enlace
  del selector de temas).
- `pix/` — assets procesados desde `assets/` (ver pipeline en
  [docs/theme-richimath.md](docs/theme-richimath.md)).
- `lang/en` (base) + `lang/es` — nunca texto duro en plantillas.

## Comandos

```bash
./scripts/dev-up.sh        # levanta el entorno local (docker) + upgrade + purge
docker compose exec -u www-data web php admin/cli/purge_caches.php   # tras CADA cambio
docker compose exec -u www-data web php admin/cli/upgrade.php --non-interactive  # tras bump de version.php
bash scripts/deploy-contabo.sh   # despliegue en el VPS (se ejecuta EN el servidor)
```

- CLI de Moodle **siempre como www-data** (root secuestra las cachés de moodledata),
  y desde `/var/www/html` (desde /root escupe un warning chdir inofensivo).
- Cambio de plantilla o renderer → bump `version.php` + upgrade + purge.
- Verificación SIEMPRE en navegador con captura: 1440px y 390px, como admin
  (`richi85`) y como alumno (`estudiante.demo` en local). Sin captura no hay done.
- Ajustes que NO viajan por git (BD): `frontpage` (vacío para la landing),
  `theme_richimath/sidebarstyle`, idioma, visibilidad de categorías, bloques
  del dashboard — cada uno documentado en docs/theme-richimath.md; al
  desplegar, revisar la sección de ajustes manuales del plan vigente.
- **No usar Artifacts jamás** (preferencia expresa del usuario): entregar con
  capturas en el chat y ficheros locales.

## Entorno local

Espejo de producción: `docker compose up -d` (MariaDB + moodle-php-apache 8.1),
sitio en `http://localhost:8080`. La BD es un dump real de Contabo — cursos y
alumnos reales: **solo lectura de datos; jamás dumps local→producción**.
BD y `moodledata/filedir` viajan JUNTOS (ver docs/local-dev-environment.md).

## Estilo y convenciones

- Commits: Conventional Commits en inglés; **sin co-autoría ni atribución**;
  **nunca `git push`** — el push es del usuario.
- Nombres de archivo en inglés; contenido de `docs/**` en español.
- Comentarios de código: en inglés, cortos, explican el PORQUÉ.
- MVP y camino feliz; sin programación defensiva innecesaria; borrar código muerto.
- Documentar toda feature nueva en `docs/` (`.md`).

## Documentación clave

| Doc | Qué |
|---|---|
| [docs/theme-richimath.md](docs/theme-richimath.md) | El tema completo: decisiones, límites, despliegue |
| [docs/deploy/…](docs/) → `deploy-workflow.md` | Qué viaja por git y qué no; ciclo de despliegue |
| [docs/local-dev-environment.md](docs/local-dev-environment.md) | Entorno local + espejo de BD/filedir |
| [docs/security-checklist.md](docs/security-checklist.md) | Riesgos abiertos (TLS, contraseña BD, 4.3 EOL) |
| [docs/plans/](docs/plans/) | Planes de implementación por lote de tickets |
| [docs/tickets/](docs/tickets/) | Pedidos del cliente (fuente) |
