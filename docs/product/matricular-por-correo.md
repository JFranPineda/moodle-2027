# Matricular alumnos por correo (cualquier dominio)

Ticket: T-09 del plan [tickets-20260824-plan.md](../plans/tickets-20260824-plan.md).

**Verificado**: el sitio no restringe dominios de correo (`allowemailaddresses`
vacío) — puedes matricular con Gmail, Hotmail, correos del colegio, cualquiera.

## Vía recomendada: subir usuarios por CSV (crea cuenta + matricula en un paso)

1. *Administración del sitio → Usuarios → Cuentas → Subir usuarios*.
2. Sube un CSV con esta cabecera exacta:

```csv
username,email,firstname,lastname,password,course1
juan.perez,juan.perez@gmail.com,Juan,Pérez,Cambiar123*,PRE-U.LIMA
```

- `course1` = el **idnumber** o shortname del curso (no el nombre visible).
  Verlo en *Administración del curso → Configuración → Número ID del curso*.
- Puedes matricular en varios cursos a la vez con `course2`, `course3`…
- `password`: si la dejas vacía, Moodle genera una y envía el correo de
  bienvenida (requiere el correo saliente configurado — ver
  [security-checklist.md](../security-checklist.md)).

3. En el asistente de subida: revisa la vista previa, confirma, listo. El
  alumno recibe acceso inmediato sin que el admin entre curso por curso.

## Alternativa: matricular un alumno ya existente en un curso puntual

*Curso → Participantes → Matricular usuarios* → buscar por nombre o correo →
elegir rol "Estudiante" → Matricular. Sirve para altas sueltas, no para lotes.

## Fuera de alcance de este ticket (evaluar aparte si hace falta)

Plugin `enrol_invitation` (el alumno recibe un enlace de invitación y se
autorregistra) — añade una capa de autoservicio, pero es un plugin de terceros
que hay que mantener. No instalado; valorarlo si el volumen de altas lo justifica.

## Dónde se asignan estudiantes a un curso (pregunta 2026-08-29)

Siempre **dentro del curso**, no desde la portada: la pestaña «Participantes»
de la portada (AULA VIRTUAL Richi Math) lista usuarios del sitio y no
matricula en ningún curso.

1. *Mis cursos* (o *Cursos*) → entrar al curso.
2. Pestaña **Participantes** → botón **Matricular usuarios** (arriba a la derecha).
3. Buscar por nombre o correo, elegir rol **Estudiante**, *Matricular usuarios*.

Para muchos alumnos a la vez: el CSV de arriba con la columna `course1` (crea
la cuenta si no existe y matricula en el mismo paso). Para quitar a alguien:
Participantes → icono de papelera en su fila.
