#!/usr/bin/env python3
"""Build the 360 escape-room pilot as a self-contained .h5p package.

The package carries its own H5P libraries, so it installs on any Moodle even if
`Virtual Tour (360)` was never added: uploading it to the content bank installs
the content type site-wide as a side effect.

Sources, both fetched at build time so nothing binary lives in the repo:

- Libraries: the official H5P hub (`api.h5p.org`), the same endpoint the H5P
  plugin itself downloads from.
- Panorama: Poly Haven, CC0, tonemapped equirectangular JPG. Downscaled to
  4096x2048 because the students come in from tablets on mobile data.

Usage:  python3 scripts/build-h5p-escape-room.py [-o OUTPUT.h5p]

Needs `ffmpeg` on the PATH (only to resize) and network access.
"""

import argparse
import json
import pathlib
import shutil
import subprocess
import tempfile
import urllib.request
import zipfile

HUB_URL = 'https://api.h5p.org/v1/content-types/H5P.ThreeImage'
PANO_URL = ('https://dl.polyhaven.org/file/ph-assets/HDRIs/extra/'
            'Tonemapped%20JPG/machine_shop_01.jpg')
PANO_NAME = 'images/escena-sala-maquinas.jpg'
PANO_W, PANO_H = 4096, 2048

TITLE = 'Estación Kepler — Sella la grieta'

# Poly Haven is CC0; H5P still wants the block filled in, and an empty one
# would be a lie about where the picture came from.
PANO_COPYRIGHT = {
    'license': 'CC0 1.0',
    'title': 'Machine Shop 01',
    'author': 'Poly Haven',
    'source': 'https://polyhaven.com/a/machine_shop_01',
    'version': '1.0',
}

# THE trap of this content type. In a `static` scene `interactionpos` is
# "x%,y%" over the flat image — that is what every official example shows,
# because the sample scenes are static. In a **360 scene it is "yaw,pitch" in
# RADIANS** (H5P.ThreeSixty's setElementPosition feeds them straight to
# Math.sin/Math.cos). Pass percentages here and you get Math.sin("50%") = NaN:
# the hotspots exist, raise no error, and are painted nowhere. Yaw runs 0..2π
# around the room; pitch −π/2..π/2, negative looking down.
POS_INFORME = '0,-0.05'
POS_PANEL = '-1.1,-0.15'
POS_CAJA = '1.1,-0.2'
POS_CONSOLA = '2.4,-0.1'


def text(label, html, pos):
    """A hotspot that just shows a panel of text."""
    return {
        'labelText': label,
        'label': {'labelPosition': 'inherit', 'showLabel': 'inherit'},
        'interactionpos': pos,
        'action': {
            'library': 'H5P.AdvancedText 1.1',
            'params': {'text': html},
            'subContentId': f'rm-text-{abs(hash(label)) % 10**8:08d}',
            'metadata': {'contentType': 'Text', 'license': 'U', 'title': label},
        },
    }


def question_set(label, pos, questions, feedback):
    """The puzzle itself. In SingleChoiceSet the FIRST answer is the correct
    one; H5P shuffles them before showing them."""
    return {
        'labelText': label,
        'label': {'labelPosition': 'inherit', 'showLabel': 'inherit'},
        'interactionpos': pos,
        'action': {
            'library': 'H5P.SingleChoiceSet 1.11',
            'params': {
                'choices': [
                    {
                        'subContentId': f'rm-q{i}-0000-0000-0000-00000000',
                        'question': f'<p>{q}</p>\n',
                        'answers': [f'<p>{a}</p>\n' for a in answers],
                    }
                    for i, (q, answers) in enumerate(questions)
                ],
                'overallFeedback': feedback,
                'behaviour': {
                    'autoContinue': True,
                    'timeoutCorrect': 2000,
                    'timeoutWrong': 3000,
                    'soundEffectsEnabled': True,
                    'enableRetry': True,
                    'enableSolutionsButton': False,
                    'passPercentage': 100,
                },
                'l10n': {
                    'nextButtonLabel': 'Siguiente',
                    'showSolutionButtonLabel': 'Ver solución',
                    'retryButtonLabel': 'Reintentar',
                    'solutionViewTitle': 'Soluciones',
                    'correctText': '¡Correcto!',
                    'incorrectText': 'Incorrecto',
                    'muteButtonLabel': 'Silenciar sonidos',
                    'closeButtonLabel': 'Cerrar',
                    'slideOfTotal': 'Paso :num de :total',
                    'scoreBarLabel': 'Has acertado :num de :total',
                },
            },
            'subContentId': 'rm-consola-0000-0000-000000000000',
            'metadata': {'contentType': 'Single Choice Set', 'license': 'U',
                         'title': label},
        },
    }


BRIEFING = (
    '<p><strong>ESTACIÓN KEPLER — ALERTA DE PRESIÓN</strong></p>'
    '<p>Un micrometeorito ha abierto una grieta en el casco. El oxígeno se '
    'está escapando y quedan <strong>40 minutos</strong> de aire.</p>'
    '<p>El casco se sella solo si pides <em>exactamente</em> el sellador que '
    'hace falta.</p>'
    '<p>Gira y lee el <strong>Panel de medición</strong> y la '
    '<strong>Caja de sellador</strong>: ahí está el método. Luego ve a la '
    '<strong>Consola de reparación</strong>, que es donde se repara.</p>'
    # On a phone the viewer collapses to a ~150px strip and the labels overlap.
    # Fullscreen is the fix, and the student has to be told inside the game.
    '<p><em>En el móvil o la tablet, pulsa primero el icono de pantalla '
    'completa (arriba a la derecha). Se ve mucho mejor.</em></p>'
)

# No numbers in the room on purpose: the quiz owns them and hands every student
# a different set. A figure written here would contradict the quiz.
PANEL = (
    '<p><strong>PANEL DE MEDICIÓN</strong></p>'
    '<p>Los sensores <strong>no alcanzan la grieta</strong>. Solo miden su '
    'sombra sobre los dos ejes del casco, que se cortan en ángulo recto.</p>'
    '<p>La grieta es la línea recta que une los extremos de esas dos sombras: '
    'es la <strong>hipotenusa</strong> del triángulo que forman.</p>'
    '<p>La consola te dará las dos medidas. Aquí tienes el método.</p>'
)

CAJA = (
    '<p><strong>CAJA DE SELLADOR</strong></p>'
    '<p>Las tiras <strong>no se pueden partir</strong>: si sobra media tira, '
    'esa tira ya se gastó.</p>'
    '<p>Así que al dividir siempre se <strong>redondea hacia arriba</strong>. '
    'Pide de menos y la grieta sigue abierta.</p>'
)

CONSOLA = (
    '<p><strong>CONSOLA DE REPARACIÓN</strong></p>'
    '<p>La consola es el cuestionario <strong>«Consola de reparación»</strong>, '
    'en el curso, justo debajo de esta sala.</p>'
    '<p>Son <strong>5 pasos</strong> y cada uno se apoya en el anterior. Ojo: '
    '<em>la consola da a cada tripulante medidas distintas</em>, así que la '
    'respuesta de tu compañero no te sirve.</p>'
)

PREGUNTAS = [
    ('La sombra horizontal mide 6 m y la vertical 8 m, y se cortan en ángulo '
     'recto. ¿Cuánto mide la grieta?',
     ['10 metros', '14 metros', '48 metros', '7 metros']),
    ('Las tiras de sellador miden 2 m. ¿Cuántas necesitas para cubrir la '
     'grieta entera?',
     ['5 tiras', '10 tiras', '4 tiras', '20 tiras']),
    ('Aparece una segunda grieta: mide 15 m y su sombra vertical es de 9 m. '
     '¿Cuánto mide su sombra horizontal?',
     ['12 metros', '6 metros', '24 metros', '17 metros']),
]

FEEDBACK = [
    {'from': 0, 'to': 33,
     'feedback': 'Sigue escapando oxígeno. Vuelve al Panel de medición: las '
                 'dos sombras y la grieta forman un triángulo rectángulo.'},
    {'from': 34, 'to': 99,
     'feedback': 'La grieta queda a medio sellar. Repasa el paso que fallaste '
                 'y vuelve a intentarlo.'},
    {'from': 100, 'to': 100,
     'feedback': '¡Casco sellado! La Estación Kepler recupera la presión. '
                 'Has usado el teorema de Pitágoras para medir algo que '
                 'ningún sensor podía alcanzar.'},
]


def build_content():
    return {
        'threeImage': {
            'startSceneId': 0,
            'scenes': [{
                'sceneId': 0,
                'sceneType': '360',
                'showBackButton': False,
                'scenename': 'Sala de Máquinas',
                'scenedescription': 'Sala de máquinas de la Estación Kepler. '
                                    'Gira para explorar y toca los rótulos.',
                'cameraStartPosition': '0,0',
                'scenesrc': {
                    'path': PANO_NAME,
                    'mime': 'image/jpeg',
                    'copyright': PANO_COPYRIGHT,
                    'width': PANO_W,
                    'height': PANO_H,
                },
                'interactions': [
                    text('1 · Informe de avería', BRIEFING, POS_INFORME),
                    text('2 · Panel de medición', PANEL, POS_PANEL),
                    text('3 · Caja de sellador', CAJA, POS_CAJA),
                    text('4 · Consola de reparación', CONSOLA, POS_CONSOLA),
                ],
            }],
        },
        'behaviour': {
            'sceneRenderingQuality': 'high',
            # Labels always visible: without them the students spin in circles
            # hunting for invisible hotspots, which is frustration, not puzzle.
            'label': {'labelPosition': 'right', 'showLabel': True},
        },
        'l10n': {
            'title': 'Estación Kepler',
            'playAudioTrack': 'Reproducir audio',
            'pauseAudioTrack': 'Pausar audio',
            'sceneDescription': 'Descripción de la escena',
            'resetCamera': 'Volver al inicio',
            'submitDialog': 'Enviar',
            'closeDialog': 'Cerrar',
            'expandButtonAriaLabel': 'Ampliar el rótulo',
            'backgroundLoading': 'Cargando la sala…',
            'noContent': 'Sin contenido',
        },
    }


def fetch(url, dest):
    with urllib.request.urlopen(url, timeout=300) as r, open(dest, 'wb') as f:
        shutil.copyfileobj(r, f)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('-o', '--output', default='assets/h5p/estacion-kepler.h5p')
    args = ap.parse_args()

    out = pathlib.Path(args.output).resolve()
    out.parent.mkdir(parents=True, exist_ok=True)

    with tempfile.TemporaryDirectory() as tmp:
        tmp = pathlib.Path(tmp)
        pkg = tmp / 'pkg'
        pkg.mkdir()

        print('· Descargando librerías del hub de H5P…')
        hub = tmp / 'hub.h5p'
        fetch(HUB_URL, hub)
        with zipfile.ZipFile(hub) as z:
            z.extractall(pkg)

        print('· Descargando la panorámica de Poly Haven…')
        raw = tmp / 'pano.jpg'
        fetch(PANO_URL, raw)

        print(f'· Reescalando a {PANO_W}x{PANO_H}…')
        images = pkg / 'content' / 'images'
        shutil.rmtree(images, ignore_errors=True)
        images.mkdir(parents=True)
        subprocess.run(
            ['ffmpeg', '-v', 'error', '-y', '-i', str(raw),
             '-vf', f'scale={PANO_W}:{PANO_H}', '-q:v', '4',
             str(pkg / 'content' / PANO_NAME)],
            check=True)

        print('· Escribiendo el contenido…')
        (pkg / 'content' / 'content.json').write_text(
            json.dumps(build_content(), ensure_ascii=False), encoding='utf-8')

        meta = json.loads((pkg / 'h5p.json').read_text(encoding='utf-8'))
        meta.update({
            'title': TITLE,
            'language': 'es',
            'license': 'U',
            'authors': [{'name': 'Richi Academy', 'role': 'Author'}],
        })
        (pkg / 'h5p.json').write_text(
            json.dumps(meta, ensure_ascii=False), encoding='utf-8')

        print(f'· Empaquetando en {out}…')
        if out.exists():
            out.unlink()
        # h5p.json has to sit at the root of the zip, not inside a folder.
        with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
            for path in sorted(pkg.rglob('*')):
                if path.is_file():
                    z.write(path, path.relative_to(pkg))

    size = out.stat().st_size / 1048576
    print(f'\nListo: {out}  ({size:.1f} MB)')
    print('Súbelo en Moodle: Administración del sitio → Banco de contenido → Subir.')


if __name__ == '__main__':
    main()
