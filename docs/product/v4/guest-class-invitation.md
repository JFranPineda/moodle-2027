# Manual: invitar a una clase de BigBlueButton sin matricular a nadie

Para invitar a una persona **a una sola clase en vivo**, sin crearle cuenta en
Moodle ni inscribirla en ningún curso. El invitado deja nombres, apellidos,
universidad y correo antes de entrar; esos datos quedan en la lista
**Asistentes a sesiones abiertas** y no en los usuarios de Moodle.

Detalle técnico de la función: [../open-session-leads.md](../open-session-leads.md).
Manual general de clases en vivo: [../bbb-user-manual.md](../bbb-user-manual.md).

---

## 1. Preparación (una sola vez, administrador)

Estos dos ajustes viven en la base de datos de la plataforma: **no llegan con
un despliegue**, hay que hacerlos a mano en `richiacademy.com`.

1. **Administración del sitio → Extensiones → Módulos de actividad →
   Gestionar actividades**: BigBlueButton debe estar **sin el ojo tachado**.
   Si está apagado, cualquier enlace a una clase da el error
   `invalidcoursemoduleid`, incluso para el administrador.
2. **Administración del sitio → Extensiones → Módulos de actividad →
   BigBlueButton**: activar **External guest access** (acceso de invitados
   externos) y guardar.

---

## 2. Crear la clase (cada vez)

1. Entra al curso donde vivirá la clase y activa el **Modo de edición**.
2. **Añadir una actividad o un recurso → BigBlueButton**.
3. Ponle **nombre** (es lo que verá el invitado) y la **fecha y hora** de la
   clase.
4. En la sección **Acceso de invitados** (*Guest access*), marca **Permitir
   acceso de invitados** (*Allow guest access*).
   - Opcional: **Los invitados deben ser admitidos por un moderador**, si
     quieres aprobar a cada uno al entrar.
5. **Guardar y mostrar**.

> La actividad tiene que estar dentro de un curso, así que **los alumnos
> inscritos en ese curso también la verán**. Si no quieres que la vean, crea un
> curso aparte solo para clases abiertas.
>
> **No la ocultes a los alumnos**: no está comprobado que el enlace de
> invitación funcione con la actividad oculta.

---

## 3. Enviar la invitación

1. Abre la actividad BigBlueButton que acabas de crear.
2. En su menú de ajustes, entra a **Invitar visitantes a esta sesión**.
3. Elige una de las dos opciones:
   - **Copiar el enlace** que aparece en el recuadro y mandarlo por WhatsApp,
     redes o como quieras.
   - **Pegar los correos**, separados por comas, y pulsar **Enviar la
     invitación**. A cada uno le llega un correo con el enlace.

### ⚠️ El enlace correcto

En los ajustes de la actividad, Moodle también muestra un **enlace de invitado
propio** con una contraseña. **No mandes ese.**

| | Enlace de **Invitar visitantes a esta sesión** | Enlace de los ajustes de la actividad |
|---|---|---|
| Pide contraseña al invitado | No | Sí |
| Pide nombres, apellidos, universidad, correo | Sí | Solo un nombre |
| Guarda quién entró | **Sí** | No |

---

## 4. Lo que ve el invitado

1. Abre el enlace y ve un formulario: **nombres, apellidos, universidad y
   correo**, más una casilla opcional para aceptar que la academia le escriba.
2. Pulsa **Continuar** y luego **Entrar a la clase**.
3. Entra a la sala. No necesita contraseña ni cuenta.

Si vuelve a entrar con el mismo correo, **no se duplica**: se suma una visita a
su registro.

---

## 5. El día de la clase

- **Entra tú primero e inicia la sesión.** Si el invitado llega antes, ve
  *«La reunión aún no ha comenzado. Por favor, vuelva más tarde»* y tiene que
  volver a abrir el enlace cuando ya estés dentro.
- Si marcaste «deben ser admitidos por un moderador», te aparecerá cada
  invitado en la sala para que lo aceptes.

---

## 6. Ver quién se registró

**Administración del sitio → Usuarios → Cuentas → Asistentes a sesiones
abiertas**.

- La lista completa: nombre, universidad, correo, cuántas veces vino y si
  aceptó que se le escriba.
- **Descargar la lista de correos (CSV)**: solo incluye a quienes **aceptaron**
  recibir comunicaciones (Ley 29733). La lista en pantalla es el registro de
  asistencia; el CSV es la lista de correo, y no son lo mismo.

---

## Problemas frecuentes

| Lo que pasa | Por qué | Qué hacer |
|---|---|---|
| El invitado ve «La reunión aún no ha comenzado» | Nadie ha iniciado la sesión todavía | Entra tú primero; el invitado reabre el enlace |
| Error `invalidcoursemoduleid` | BigBlueButton está apagado en el sitio | Paso 1.1 |
| Al crear la clase no aparece la sección **Acceso de invitados** | *External guest access* apagado en el sitio | Paso 1.2 |
| La página de invitación dice «Esta sesión todavía no admite invitados» | No marcaste *Permitir acceso de invitados* en la actividad | Paso 2.4 |
| No aparece **Invitar visitantes a esta sesión** | Tu cuenta no puede editar actividades en ese curso | Entra con una cuenta de profesor con permiso de edición |
| No llega el correo de invitación | Correo saliente del sitio | Manda el enlace copiado mientras tanto; ver [../outgoing-mail-gmail.md](../outgoing-mail-gmail.md) |
