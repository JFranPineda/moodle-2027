# Clases en vivo sin límite de 1 hora: BigBlueButton propio (T-06)

Ticket T-06 del [plan 2026-08-24](../plans/tickets-20260824-plan.md). Hecho
duro: el BigBlueButton gratuito que trae Moodle (créditos de Blindside) corta
a los 60 minutos y no hay forma de alargarlo por código. La salida es un
**servidor BBB propio** (mismo plugin nativo de Moodle, sin límite) o
**Jitsi** (gratis, sin límite, menos integrado).

Pregunta de origen (2026-08-30): costo mínimo en Contabo para clases > 1 h.

## Requisitos oficiales de BBB (3.0)

- Ubuntu 22.04 **limpio y dedicado**: no puede convivir con Moodle en el VPS
  actual (BBB se apodera de los puertos 80/443 y de todo el servidor).
- RAM: **16 GB para producción**, con swap. No es orientativo: es el mínimo
  que BBB documenta, y BBB 3.0 carga además Postgres y Hasura.
- CPU: **8 núcleos** con buen rendimiento monohilo (mínimo documentado).
- Disco: 50 GB si NO se graban las clases; **500 GB si se graban** (las
  grabaciones crecen ~0,5–1 GB/hora). Empezar sin grabación o con retención
  corta.
- Dominio propio con TLS (Let's Encrypt lo hace `bbb-install.sh` solo):
  usaremos `clases.richiacademy.com` (registro A en Porkbun → IP del VPS).
- Puertos: TCP 80/443 y **UDP 16384–32768** abiertos.

## Costo mínimo en Contabo (precios de lista verificados el 2026-08-31 en la web de Contabo, IVA aparte; la gama se llama «Cloud VPS <n.º de vCPU>»)

| Plan Contabo | vCPU / RAM / disco | €/mes | Sirve para |
|---|---|---|---|
| Cloud VPS 4 | 4 vCPU / 8 GB / 100 GB SSD | ~5,50 € | **Por debajo del mínimo de BBB.** Pruebas, no producción |
| Cloud VPS 6 | 6 vCPU / 12 GB / 200 GB SSD | ~7,50 € | **Sigue por debajo** de los 16 GB |
| **[Cloud VPS 8](https://contabo.com/en/vps/)** | **8 vCPU / 24 GB / 300 GB SSD** | **~14 €** | **El primero que cumple el mínimo.** 5 clases de 11 con cámaras de alumno apagadas |
| Cloud VPS 12 | 12 vCPU / 48 GB / 400 GB SSD | ~25 € | Si algún día las cámaras de los alumnos se encienden |

- Contabo cobra una **cuota única de instalación** (~5 €) en el plan mensual;
  se ahorra contratando 12 meses.
- Elegir región **EE. UU. (Este)** o la más cercana a Perú disponible: la
  latencia manda en audio/vídeo (Europa añade ~150 ms).
- Total realista: **≈ 5–10 €/mes** + el dominio que ya está pagado.

Recomendación técnica (2026-09-12, objetivo **5 clases en paralelo de 1
profesor + 10 alumnos**): **Cloud VPS 8**, el primero que cumple el mínimo
documentado por BBB.

**Decisión tomada: Cloud VPS 6** (~9 $/mes, o 7,20 $/mes a 24 meses), por
precio. Es viable para este perfil concreto —cámaras de alumno apagadas, audio
y pantalla compartida— **con cuatro condiciones** y un criterio de subida
medible, todo en [bbb-server-setup.md](../bbb-server-setup.md) §0. El riesgo
está en la RAM (12 GB frente a los 16 del mínimo), y se mitiga con swap.
Contabo escala a VPS 8 desde el panel **sin reinstalar**, así que la decisión
es reversible en minutos.

## Capacidad: clases en paralelo

La duración no cuenta; cuentan usuarios simultáneos y cámaras. Cifras
conservadoras con audio para todos, profesor con cámara + pantalla y pocas
cámaras de alumnos:

| VPS | Usuarios simultáneos | Clases paralelas de 20–25 |
|---|---|---|
| Cloud VPS 4 (4 vCore / 8 GB) | ~60–80 | 2 (3 casi sin cámaras) |
| Cloud VPS 6 (6 vCore / 12 GB) | ~120–150 | 4–5 |
| **Cloud VPS 8 (8 vCore / 24 GB)** | **~200** | **8** — el caso de Richi (55 personas) entra con holgura |

Para el caso concreto de 5 × (1 + 10) con cámaras de alumno apagadas, el pico
real es la **pantalla compartida**: 5 emisores y 50 receptores ≈ 80–90 Mbit/s
de salida, dentro del puerto de Contabo (200 Mbit/s–1 Gbit/s, tráfico
ilimitado con uso razonable). El audio de 55 personas no llega a 10 Mbit/s.

- Cámaras de alumnos: cada una se reenvía a todos (SFU); 25 cámaras ≈ 3
  clases solo con audio. Todos con cámara → dividir la tabla entre 2–3.
- Grabación: el procesado posterior usa ~1 núcleo un 30–50 % del tiempo de
  la clase; se encola, no bloquea.
- vCores compartidos: si una clase se entrecorta en hora punta, subir de
  plan sin reinstalar.
- Ancho de banda (32 TB/mes) no es cuello de botella: ~1–2 Mbit/s por alumno.
- El VPS de Moodle no interviene: el vídeo va alumno ↔ BBB; Moodle solo abre
  la sala.

Con 5 clases a la vez, lo que hay que vigilar no es el número de personas sino
**cuántas cámaras y cuántas pantallas compartidas** hay encendidas al mismo
tiempo. Por eso la instalación deja las cámaras de alumno bloqueadas por
defecto — ver [bbb-server-setup.md](../bbb-server-setup.md), paso 6.

## Alternativa 0 €: Jitsi Meet

- `meet.jit.si` público: sin límite de tiempo, sin instalar nada. Contras:
  sin integración con Moodle (se pega el enlace como recurso URL por curso),
  calidad variable, sin grabación gratuita.
- Jitsi autoalojado en un Cloud VPS 4 (misma ~5,50 €/mes): entonces el costo es el
  mismo que BBB y BBB integra mejor (asistencia, grabaciones en el curso,
  pizarra, encuestas). Por eso, si se paga un VPS, que sea para BBB.

## Puesta en marcha (resumen; ~1 tarde)

Guía técnica completa, paso a paso: [../bbb-server-setup.md](../bbb-server-setup.md).
Manual de uso en clase: [bbb-user-manual.md](bbb-user-manual.md).

1. Contratar el VPS (Ubuntu 22.04, sin panel). Porkbun → DNS → `A clases → IP-del-VPS`.
2. En el VPS como root (script oficial):
   ```bash
   wget -qO- https://raw.githubusercontent.com/bigbluebutton/bbb-install/v3.0.x-release/bbb-install.sh \
     | bash -s -- -v jammy-300 -s clases.richiacademy.com -e learning.richiacademy@gmail.com -w
   ```
   (`-w` instala el firewall con los puertos correctos; sin `-g`, no hace
   falta Greenlight: la puerta de entrada es Moodle). Al terminar:
   `bbb-conf --check` y `bbb-conf --secret` (URL + secreto).
3. Moodle (Contabo actual): *Administración → Extensiones → Módulos de
   actividad → BigBlueButton* → URL del servidor `https://clases.richiacademy.com/bigbluebutton/`
   + secreto. Desmarcar el aviso de «servidor de prueba».
4. En cada curso: actividad **BigBlueButton** en la sección «Clases en Vivo»
   (misma sala siempre; el enlace no cambia). Opcional: grabar.
5. Prueba: clase de > 60 min con 3–4 personas y cámaras.

Mantenimiento: `bbb-install.sh` se puede re-ejecutar para actualizar;
`apt upgrade` mensual; vigilar disco si se graba.

## Decisión pendiente de Richi

Aprobar ≈ 5,50–7,50 €/mes (+IVA) de VPS aparte. Con eso T-06 se ejecuta en una
tarde. Sin VPS, la única opción sin costo es Jitsi público como enlace externo.
