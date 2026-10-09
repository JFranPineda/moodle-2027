# Ventana nocturna de procesado de grabaciones (BBB 3.0)

Grabar durante la clase es barato; **procesar** la grabación es lo que exprime
la CPU. Estos cuatro units mandan el procesado a la madrugada.

BBB 3.0 **no tiene ningún timer** para esto (el `bbb-record-core.timer` que
documentaba la guía era de la rama 2.x y no existe). El procesado son dos
servicios:

- `bbb-rap-starter.service` — vigila las reuniones terminadas y **encola** en
  redis/resque.
- `bbb-rap-resque-worker.service` — **consume** la cola. Arranca en cuanto
  termina la clase.

La ventana se consigue **parando el worker de día**: el starter sigue
encolando, los trabajos se acumulan en redis y el worker los drena cuando
levanta a las 23:00.

El worker ya trae `Nice=19`, así que incluso corriendo cede CPU a las clases en
vivo. La ventana es la segunda red, no la única.

## Instalar

```bash
systemctl disable --now bbb-rap-resque-worker.service
cp bbb-rap-worker-*.{timer,service} /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now bbb-rap-worker-start.timer bbb-rap-worker-stop.timer
systemctl list-timers 'bbb-rap-worker-*'
```

La ventana es **23:00 – 06:00**, decidida por Richi el 2026-09-14 porque las
clases pueden acabar tarde. Para moverla se editan esas dos líneas y nada más:
systemd no admite variables en `OnCalendar`, así que el valor va literal en cada
`.timer`. `OnCalendar` usa la hora local y el servidor va en `America/Lima`.

Si algún día las grabaciones dejan de aparecer por la mañana, la ventana se
quedó corta: el cierre pilla un trabajo a medias y se va a la noche siguiente.
Se ensancha la ventana, o se pone `COUNT=2` en el unit del worker.

## Trampa

`bbb-conf --restart` puede volver a levantar el worker, porque su unit lleva
`PartOf=bigbluebutton.target`. Tras cualquier `bbb-conf --restart` hecho de
día: `systemctl stop bbb-rap-resque-worker`.

## Consecuencia para el profesor

La grabación aparece **a la mañana siguiente**, no al terminar la clase. Si se
programan clases más tarde de las 23:00, correr la ventana.
