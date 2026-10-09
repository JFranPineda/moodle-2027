# Pizarra virtual por curso

Ticket: T-07 del plan [tickets-20260824-plan.md](../plans/tickets-20260824-plan.md).

**Decisión tomada (MVP, sin instalar nada)**: Excalidraw embebido como recurso
URL dentro de la sección "Clases en Vivo" de cada curso. Dibujo colaborativo
real, gratis, cero mantenimiento — la alternativa de plugin (`mod_board`) es
solo post-its, no pizarra de dibujo; y una pizarra dentro de BigBlueButton/Jitsi
ya llega gratis el día que se resuelva el ticket T-06 de videollamadas.

## Cómo añadirla a un curso (por UI, lo hace el profe)

1. Entra al curso → activa el *Modo de edición*.
2. En la sección **Clases en Vivo** → *Añadir una actividad o un recurso* → **URL**.
3. Nombre: `Pizarra`. URL: `https://excalidraw.com/#room=<ID_UNICO>,<CLAVE>`
   — abre excalidraw.com, clic en *Live collaboration start*, copia el enlace
   que genera (ya trae sala y clave únicas) y pégalo ahí.
4. En *Apariencia* elige "Insertar" para que abra dentro de la página del curso.
5. Guardar. Todos los alumnos matriculados ven el mismo enlace = misma pizarra.

## Notas

- La sala de Excalidraw vive en el navegador de cada participante mientras
  la pestaña esté abierta con gente conectada — no persiste sola; si quieren
  guardar lo dibujado, usar *Exportar* dentro de Excalidraw (PNG/SVG) al cerrar
  la clase, y subirlo como archivo al curso si se quiere dejar constancia.
- Una sala por curso (no reutilizar el mismo enlace entre cursos): si dos
  clases comparten sala, los alumnos de una ven el pizarrón de la otra.
