# Línea base: Moodle 5.3 limpio (sin nada de Richi Math)

Capturas del 2026-10-09 de una instalación **limpia** de 5.3 en local
(`http://localhost:8083`): tema Boost, sin nuestros plugins. Sirven para la
Fase 1: al portar cada plantilla o sección del SCSS, se compara contra esto
para ver **qué trae core de nuevo** y no taparlo con una copia vieja de 4.3.

Contenido de prueba: curso `BASE53` generado (`tool_generator`, tamaño XS) +
«Cuestionario base 5.3» con 3 preguntas de opción múltiple importadas en GIFT;
usuarios `qa.admin` (admin) y `estudiante.demo` (alumno), solo en local.

| Captura | Página | Como |
|---|---|---|
| `login-1440.png`, `login-390.png` | `/login/index.php` | anónimo |
| `admin-dashboard-1440.png` | `/my/` | admin |
| `admin-course-1440.png` | `/course/view.php?id=2` | admin |
| `admin-categories-1440.png` | `/course/index.php` | admin |
| `admin-userlist-1440.png` | `/admin/user.php` | admin |
| `student-dashboard-1440.png` | `/my/` | alumno |
| `student-course-1440.png`, `student-course-390.png` | curso | alumno |
| `student-quiz-view-1440.png` | ficha del cuestionario | alumno |
| `student-quiz-attempt-1440.png`, `student-quiz-attempt-390.png` | intento | alumno |

## Lo que ya se ve y afecta a tickets

- **MIG-26 / FUN-22**: la lista de usuarios de 5.3 es un informe con columna
  **«Dirección de correo»** y botón **«Filtros»** de serie; el título sigue
  siendo «Examinar lista de usuarios».
- **FUN-14**: el intento de 5.3 pinta cada pregunta en una caja celeste con la
  ficha de estado a la izquierda y la navegación como cuadrados numerados a la
  derecha — es el marcado sobre el que hay que volver a aplicar §D8 y §F.
- **FUN-13 / MIG-27**: los iconos de actividad ya salen en color y sin baldosa
  (ver el del cuestionario junto al título del intento).
