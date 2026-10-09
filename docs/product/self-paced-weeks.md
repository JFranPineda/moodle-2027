# Avance por semanas al ritmo de cada alumno (contenido goteado)

Pregunta de origen: dos alumnos se matriculan con un mes de diferencia; ambos
deben empezar por la Semana 1, y a cada uno se le desbloquea la Semana 2 cuando
completa la 1 — sin importar la fecha en que entró.

**Es funcionalidad nativa de Moodle**: *Restricciones de acceso* + *Finalización
de actividad*. Nada que instalar.

## Regla de oro

- Restricción por **fecha** = calendario igual para todos → NO sirve para este caso.
- Restricción por **finalización de actividad** = individual por alumno → la correcta.

## Receta (por curso, lo hace el profe por UI)

1. *Configuración del curso → Rastreo de finalización → Sí*. (Una sola vez.)
2. En **SEMANA 1** (la carpeta o, mejor, su tarea/cuestionario): pestaña
   *Finalización de actividad* → definir qué cuenta como completada.
3. En **SEMANA 2**: *Restricciones de acceso → Añadir restricción →
   Finalización de actividad* → «SEMANA 1 completada».
4. Encadenar: la 3 exige la 2, la 4 exige la 3, etc.

## Recomendaciones

- **Condicionar a una actividad evaluable** (cuestionario/tarea de la semana),
  no a «ver la carpeta»: si basta con abrirla, el alumno desbloquea todo el
  curso en cinco minutos.
- **Mostrar la semana bloqueada en gris** (dejar el ojo visible en la
  restricción): el alumno ve el camino completo y qué le falta para avanzar.
  Ocultarla del todo también es posible (clic en el candado/ojo), pero informa menos.
- El profe siempre ve todo; la restricción aplica a estudiantes.
- Funciona igual si las semanas son carpetas dentro de una sección o secciones
  completas (las secciones también aceptan restricciones, en su propio menú de edición).

## Verificar como alumno

*Participantes → clic en un alumno → «Iniciar sesión como»* — se ve el curso
exactamente como él: Semana 1 abierta, las demás en gris con su condición.
