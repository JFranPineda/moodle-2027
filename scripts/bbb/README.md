# Configuración del servidor de clases en vivo (BBB)

Los ficheros de este directorio son **la configuración exacta** del servidor
BigBlueButton de `clases.richiacademy.com`, versionada aquí para que migrar o
reconstruir ese servidor sea copiar, no recordar.

Cómo se instala, se dimensiona y se opera:
[docs/bbb-server-setup.md](../../docs/bbb-server-setup.md).

| Fichero de aquí | Va a parar a | Qué decide |
|---|---|---|
| `config/bbb-web.properties` | `/etc/bigbluebutton/bbb-web.properties` | Duración, grabación, tope por sala, cámaras de alumno |
| `config/rap-night-window/bbb-rap-worker-*.{timer,service}` | `/etc/systemd/system/` | La ventana nocturna de procesado de grabaciones (BBB 3.0 no trae timer: se para el worker de día) |

## Lo que NO vive aquí, y no debe

- **El secreto compartido de BBB** (`bbb-conf --secret`). Vive en el servidor y
  en la configuración de Moodle. Quien lo tenga puede crear salas en tu nombre.
- **Las claves de Object Storage** para archivar grabaciones (`rclone config`).
- **Las grabaciones**. Son datos de alumnos y pesan gigas: van al disco del
  servidor y, lo viejo, al archivo frío.

Si algo de eso acaba en un commit, no basta con borrarlo: queda en el historial
y hay que rotarlo.

## Por qué está aquí y no en un repositorio aparte

Porque el aula es una sola: el secreto se pega en Moodle, la actividad se crea
en Moodle, el enlace a la grabación lo publica Moodle. Separar dos ficheros de
configuración en otro repositorio obliga a clonar dos cosas y a mantener
referencias cruzadas que se pudren. Un plugin de BBB —si algún día se escribe—
sí es otro proyecto: tiene su propio `npm`, su ciclo de versiones y se despliega
en otro servidor.
