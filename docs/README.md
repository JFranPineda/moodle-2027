# Documentación — Moodle Richimath

Guía operativa del Moodle desplegado en el servidor Contabo y del flujo de
trabajo para desarrollarlo desde un clon local.

| Documento | Para qué |
|---|---|
| [server-layout.md](server-layout.md) | Dónde está cada cosa en Contabo: rutas, servicios, base de datos, versiones |
| [deploy-workflow.md](deploy-workflow.md) | Cómo llevar cambios de tu portátil a producción sin romper el sitio |
| [plugin-development.md](plugin-development.md) | Crear campos de BD e interfaces nuevas que viajen por git |
| [local-dev-environment.md](local-dev-environment.md) | Montar un Moodle local espejo de producción con Docker |
| [security-checklist.md](security-checklist.md) | Riesgos abiertos y cómo cerrarlos |
| [theme-richimath.md](theme-richimath.md) | Tema propio: login con fondo RM y calendario estilo Blackboard |
| [domain-and-tls.md](domain-and-tls.md) | Dominio richiacademy.com y HTTPS con Let's Encrypt |
| [bbb-server-setup.md](bbb-server-setup.md) | Instalar BigBlueButton propio en un Contabo Cloud VPS 4 (clases sin límite de 1 h) |
| [product/](product/) | Guías de uso para Richi: portada y banner, foto de usuario, invitaciones, correo, BBB (manual de usuario y costos), matrícula, pizarra, goteo semanal, gamificación |

## Resumen en diez líneas

- Producción: `http://169.58.171.171`, servidor Contabo Ubuntu 24.04, host `vmi3506104`.
- Código Moodle: `/var/www/html` — es un clon git de este repositorio.
- Repositorio: `github.com/Richimath-Moodle/Moodle`, rama `main`.
- Clon local de trabajo: `/home/jfranpineda2025/Documentos/github/moodle`.
- Moodle 4.3.12 (branch 403), MySQL/MariaDB, Apache2 en el puerto 80.
- `config.php` **no** está versionado: cada entorno tiene el suyo.
- El código viaja por git. Los datos (cursos, usuarios, ficheros) **no**.
- Los cambios de esquema de BD viajan como código, vía XMLDB y `upgrade.php`.
- Nunca se edita el core de Moodle: todo lo propio va en `theme/richimath/` (aspecto) y `local/richimath/` (invitaciones).
- Tras cada `git pull` en el servidor: `upgrade.php` + `purge_caches.php`.

## Convenciones de este directorio

Nombres de fichero en inglés, contenido en español. Un documento por tema.
Si cambia el comportamiento del sistema, se actualiza el documento en el mismo
commit que el cambio.
