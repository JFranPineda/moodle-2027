# CLAUDE.md — Moodle Richi Math (5.3 LTS)

> **Lee [MEMORY.md](MEMORY.md) primero**: estado vivo de la migración y del
> entorno. Actualízalo al terminar cualquier tarea.

## Qué es este repo

**Moodle 5.3 LTS** para el aula virtual de Richi Math (academia de matemáticas,
Perú). Sustituye a `github/moodle` (Moodle 4.3.12), que queda como referencia
mientras dura la migración. El trabajo en curso es esa migración:

- **Qué se migra**: [docs/migration-tickets.md](docs/migration-tickets.md) —
  25 tickets funcionales (`FUN`) y 23 técnicos (`MIG`).
- **En qué orden**: [docs/migration-plan.md](docs/migration-plan.md) — fases 0 a 5.
- **Historia de 4.3** (trampas, decisiones, despliegues):
  [docs/legacy-4.3/](docs/legacy-4.3/). Léela antes de portar una función: casi
  cada una tiene una trampa ya pagada ahí.

**Regla número uno: NUNCA editar core de Moodle.** Lo propio vive solo en
`public/theme/richimath/`, `public/theme/rm*/`, `public/local/richimath/`,
`scripts/`, `assets/` y `docs/`. Si una tarea parece exigir core: parar y
escalar al usuario.

## Estructura de 5.3 (cambia respecto a 4.3)

- El código web está en **`public/`** (es el `DocumentRoot`). Los plugins van en
  `public/<tipo>/<nombre>`.
- `config.php` y la CLI (`admin/cli/`) siguen en la **raíz**, fuera de `public/`.
- Las librerías de terceros vienen dentro de `public/lib/`: no hace falta
  `composer install` para ejecutar.
- Bootstrap 5 (las clases de Bootstrap 4 funcionan solo por un puente
  obsoleto: no usarlas en código nuevo).

## Comandos (entorno local)

```bash
docker compose up -d                                   # http://localhost:8083
docker compose exec -u www-data web php admin/cli/purge_caches.php
docker compose exec -u www-data web php admin/cli/upgrade.php --non-interactive
```

- CLI de Moodle **siempre como `www-data`**.
- Cambio de plantilla o renderer → bump `version.php` + upgrade + purga.
- Verificación SIEMPRE en navegador con captura: 1440 px y 390 px, como admin y
  como alumno. Sin captura no hay «hecho».
- **No usar Artifacts**: entregar con capturas y ficheros locales.

## Estilo y convenciones

- Commits: Conventional Commits en inglés; **sin co-autoría ni atribución**;
  **nunca `git push`** — el push es del usuario. En el cuerpo, los IDs
  `MIG-xx`/`FUN-xx` que cierra.
- Nombres de archivo en inglés; contenido de `docs/**` en español.
- Comentarios de código en inglés, cortos, explican el PORQUÉ.
- **Nunca contraseñas ni datos de alumnos en el repo.** Los volcados de
  producción viven fuera del repo y se borran al terminar cada ensayo.
