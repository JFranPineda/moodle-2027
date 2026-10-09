# Botón de WhatsApp del profesor del curso

Un botón flotante en cada curso que abre WhatsApp con el número del profesor.
El profesor pone su número y decide si aparece; cada curso puede cambiarlo.

Pedido en `features_v2.docx` (R3). Plan completo:
[../plans/features-v2-plan.md](../plans/features-v2-plan.md).

## Nada de esto se guarda en una tabla nuestra

Los tres ajustes son **campos nativos de Moodle**, creados por el paso de
actualización del plugin con nombres cortos fijos. Así el profesor los edita en
las pantallas que ya conoce y no hay un formulario nuestro que mantener.

| Dónde lo edita | Campo | Qué decide |
|---|---|---|
| Su perfil → WhatsApp | `rmwhatsapp` (texto) | Su número |
| Su perfil → WhatsApp | `rmwhatsappon` (casilla) | Lo enciende en **todos** sus cursos |
| Ajustes del curso → WhatsApp | `rmwhatsapp` (select) | La excepción de ese curso |

El select tiene **tres** valores y no es una casilla:

- *Lo que decida el profesor* — por defecto
- *Mostrar siempre* — enciende el botón aunque el profesor lo tenga apagado
- *No mostrar* — lo apaga aunque el profesor lo tenga encendido

«Heredar» tiene que existir: sin él, un profesor que lo tiene apagado no podría
encenderlo en un curso suelto, ni silenciar uno quien lo tiene encendido.

## Cómo usarlo

**Profesor, una vez:** su perfil → *Editar perfil* → sección **WhatsApp** →
escribe el número **con código de país y sin espacios** (`51987654321`) y marca
*Mostrar mi WhatsApp en mis cursos*.

**Por curso, cuando haga falta:** *Editar ajustes del curso* → sección
**WhatsApp** → elegir *Mostrar siempre* o *No mostrar*.

## Qué profesor responde

El **primero de los profesores con permiso de edición** del curso que tenga
número configurado. Un profesor sin rol de edición o un gestor no contestan por
un curso: no son a quien el alumno debe escribir.

## Verificado

Probado en local el 2026-10-03, como alumno (`estudiante.demo`), curso
MATEPREUL:

```
profesor apagado   + curso heredar          -> sin boton
profesor apagado   + curso mostrar siempre  -> BOTON
profesor apagado   + curso no mostrar       -> sin boton
profesor encendido + curso heredar          -> BOTON
```

A 1440 y a 390 px: el botón queda **encima del FAB de Consultas**, que a su vez
queda encima del «?» de Boost. Medido en móvil: WhatsApp a 168 px del fondo,
Consultas a 112 px, **sin solaparse**.

## Trampas pagadas

1. **`configdata` tiene que llegar como ARRAY** a
   `api::save_field_configuration()`. Las claves aplanadas de formulario
   (`'configdata[options]'`) **se ignoran en silencio**: el campo se crea, se ve
   bien en la interfaz, y cada valor se lee como `NULL` porque no tiene
   opciones. Costó descubrirlo porque nada falla — el botón simplemente no
   obedece al curso.
2. **El select guarda el índice, no la etiqueta.** Comparar contra un literal
   se rompería al traducir; los dos lados leen la misma cadena de idioma.
3. **`get_instance_data($id, false)` no devuelve los campos sin valor.** Para
   leer un campo que el curso nunca tocó hay que pasar `true`.
4. **`fullname()` necesita TODOS los campos de nombre** —`firstnamephonetic` y
   compañía— o cada página de curso se llena de avisos de depuración. Se piden
   con `\core_user\fields::for_name()->get_sql('u', false, '', '', true)`, y ese
   quinto argumento en `true` es el que pone la coma inicial: en `false` se
   concatena `u.idfirstname` y el SQL revienta.
5. **El número hay que limpiarlo.** `+51 987 654 321` llega a WhatsApp como
   `51987654321`; con el `+` y los espacios, el enlace abre un chat vacío.

## Lo que se arregló de camino

`local_richimath_extend_settings_navigation()` estaba declarado recibiendo un
`navigation_node` como segundo argumento. Moodle lo llama con el **contexto**
(`load_local_plugin_settings()` en `navigationlib.php`), así que lanzaba un
`TypeError` en **todas las páginas de curso**. Llevaba roto desde que se añadió
el enlace de invitados de BigBlueButton y no se vio porque nadie abrió un curso
después. Corregido: el nodo se saca ahora de `$settings->get('modulesettings')`.
