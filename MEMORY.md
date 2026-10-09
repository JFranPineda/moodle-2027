# MEMORY.md — estado vivo de la migración a 5.3 (actualizar al terminar cada tarea)

Última actualización: 2026-10-09 (Fase 0 en curso).

## Árboles en disco

| Carpeta | Qué es |
|---|---|
| `github/moodle` | Producción actual, **4.3.12**. Origen de lo que se porta. Docker local en `:8080` |
| `github/moodle-2026` | **4.5.14+ (Build 20261002)**, sin cambios. Solo para el salto intermedio de la Fase 2 (sin `public/`) |
| `github/moodle-2027` | **Este repo, 5.3.0 (Build 20261005)**. Docker local en `:8083` |
| `github/moodle-lang` | Paquetes de idioma ya descomprimidos: `es_4.5/` y `es_v5.3/es/` |

Imágenes Docker descargadas: `moodlehq/moodle-php-apache:8.2` y `:8.3`,
`mysql:8.0` y `:8.4`.

## Fases (docs/migration-plan.md)

| Fase | Estado |
|---|---|
| 0 Preparar | en curso |
| 1 Portar código | — |
| 2 Ensayo con copia de producción | — |
| 3 Preparar servidor | — |
| 4 Corte | — |
| 5 Después | — |

## Hechos verificados de 5.3

- Sube solo desde **4.4+** (`public/admin/environment.xml`): ruta 4.3 → 4.5 → 5.3.
- Exige **PHP 8.3** y **MySQL 8.4 / MariaDB 11.4**.
- Librerías en `public/lib/` (no hace falta `composer`); `config.php` y
  `admin/cli/` en la raíz.
