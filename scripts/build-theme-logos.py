#!/usr/bin/env python3
"""Build the per-level RM logos from the design boards in docs/design/logos.

Each board (860x400) is a presentation: the RM isotype on a pedestal at the
left, a typographic panel at the right. Only the isotype is a logo, so this
script lifts that group into its own SVG (assets/logo-<level>.svg) and
rasterises it with headless Chrome — the design uses CSS filter functions in
SVG presentation attributes, which only a browser renders correctly.

Output per child theme:
  public/theme/<theme>/pix_plugins/theme/richimath/whitelogo.png  -> sidebar brand
  public/theme/<theme>/pix/favicon.ico                     -> browser tab

The pix_plugins path is Moodle's image override: a theme may replace an image
belonging to another component, so the children re-point richimath's logo
without a single template or SCSS change.

Usage: python3 scripts/build-theme-logos.py
"""

import re
import subprocess
import sys
import tempfile
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
CHROME = '/usr/bin/google-chrome'

# The four boards share the same geometry, so one viewBox frames the isotype in
# all of them; the transparent margins are trimmed away after rendering.
MONOGRAM_GROUP = '<g transform="translate(40, 44) scale(1.30)">'
VIEWBOX = '80 105 280 230'
RENDER_WIDTH = 1120
LOGO_WIDTH = 400  # sidebar shows 56px tall; 400px keeps 3x headroom
# The institutional site's mark: its own SVG, its own viewBox. Shown at 42px
# in the top bar, so 320px wide is already 3x on a retina screen.
WEB_LOGO_VIEWBOX = '0 0 420 340'
WEB_LOGO_WIDTH = 320

# Marca v3 (docs/design/logos/v3), entregada el 2026-09-15. Son SVG de 600x600
# con el arte incrustado y un manifiesto C2PA de varios cientos de KB;
# rasterizarlos deja solo el dibujo.
V3_VIEWBOX = '0 0 600 600'
V3_ASSETS = [
    # (fuente, destino en pix/, ancho) — 16 es el monograma dorado sin texto,
    # 17 el cromado con la palabra, que se usa como marca de agua del examen.
    ('16.svg', 'loginlogo.png', 400),
    ('17.svg', 'examground.png', 500),
]
V3_RENDER_WIDTH = 1200
FAVICON_SIZES = [16, 32, 48]

LEVELS = [
    ('LOGO_ELEMENTARY', 'elementary', 'rmprimaria'),
    ('LOGO_HIGHSCHOOL', 'highschool', 'rmsecundaria'),
    ('LOGO_PREUNI', 'preuni', 'rmpreu'),
    ('LOGO_UNIVERSITY', 'university', 'rmuniversidad'),
]


def extract_isotype(board: str) -> str:
    """The <defs> plus the monogram group, framed on its own viewBox."""
    defs = re.search(r'<defs>.*?</defs>', board, re.S).group(0)

    start = board.index(MONOGRAM_GROUP)
    depth, pos = 0, start
    for tag in re.finditer(r'<g\b|</g>', board[start:]):
        depth += 1 if tag.group(0) == '<g' else -1
        if depth == 0:
            pos = start + tag.end()
            break
    monogram = board[start:pos]

    # translate(45,45) is the board's group; the inner transform stays inside
    # the extracted markup, so the coordinates keep matching the viewBox.
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{VIEWBOX}">\n'
        f'  {defs}\n'
        f'  <g transform="translate(45, 45)">\n    {monogram}\n  </g>\n'
        f'</svg>\n'
    )


def rasterise(svg_path: Path, out_path: Path, width: int, viewbox: str = VIEWBOX,
              final_width: int = LOGO_WIDTH) -> Image.Image:
    """Headless Chrome shot of the SVG on a transparent canvas, trimmed."""
    vb = [float(n) for n in viewbox.split()]
    height = round(width * vb[3] / vb[2])
    with tempfile.TemporaryDirectory() as tmp:
        page = Path(tmp) / 'page.html'
        page.write_text(
            '<style>html,body{margin:0;background:transparent}'
            f'img{{display:block;width:{width}px}}</style>'
            f'<img src="{svg_path.as_uri()}">'
        )
        shot = Path(tmp) / 'shot.png'
        subprocess.run([
            CHROME, '--headless=new', '--disable-gpu', '--hide-scrollbars',
            '--default-background-color=00000000',
            f'--screenshot={shot}', f'--window-size={width},{height}',
            f'--user-data-dir={tmp}/profile', page.as_uri(),
        ], check=True, capture_output=True)
        image = Image.open(shot).convert('RGBA')
        image.load()

    image = image.crop(image.getbbox())
    image.thumbnail((final_width, final_width * 4), Image.LANCZOS)
    image.save(out_path)
    return image


def write_favicon(logo: Image.Image, out_path: Path) -> None:
    """The monogram centred on a transparent square, as a multi-size .ico."""
    side = max(logo.size)
    square = Image.new('RGBA', (side, side), (0, 0, 0, 0))
    square.paste(logo, ((side - logo.width) // 2, (side - logo.height) // 2))
    square.save(out_path, sizes=[(s, s) for s in FAVICON_SIZES])


def main() -> int:
    if not Path(CHROME).exists():
        sys.exit(f'{CHROME} not found: needed to rasterise the SVG filters.')

    for board_dir, level, theme in LEVELS:
        board = (ROOT / 'docs/design/logos' / board_dir / 'code.html').read_text()

        svg_path = ROOT / 'assets' / f'logo-{level}.svg'
        svg_path.write_text(extract_isotype(board))

        pix_plugins = ROOT / 'public' / 'theme' / theme / 'pix_plugins/theme/richimath'
        pix_plugins.mkdir(parents=True, exist_ok=True)
        logo = rasterise(svg_path, pix_plugins / 'whitelogo.png', RENDER_WIDTH)

        pix = ROOT / 'public' / 'theme' / theme / 'pix'
        pix.mkdir(parents=True, exist_ok=True)
        write_favicon(logo, pix / 'favicon.ico')

        print(f'{theme}: {logo.width}x{logo.height} from {board_dir}')

    # The institutional site's own mark: a standalone SVG, already cropped, so
    # it only needs rasterising. It lives in the base theme because that page
    # is always served by the site theme.
    board = ROOT / 'docs/design/logos/web_logo/code.html'
    if board.exists():
        # Copied to a .svg first: an <img> refuses a file served as text/html,
        # which is what a .html extension gets, and Chrome renders the broken
        # icon instead of the logo.
        svg_path = ROOT / 'assets' / 'logo-web.svg'
        svg_path.write_text(board.read_text())

        pix = ROOT / 'public/theme/richimath/pix'
        logo = rasterise(svg_path, pix / 'weblogo.png', WEB_LOGO_WIDTH, WEB_LOGO_VIEWBOX)
        print(f'theme_richimath: weblogo {logo.width}x{logo.height}')

    pix = ROOT / 'public/theme/richimath/pix'
    for source, target, width in V3_ASSETS:
        svg_path = ROOT / 'docs/design/logos/v3' / source
        if not svg_path.exists():
            continue
        art = rasterise(svg_path, pix / target, V3_RENDER_WIDTH, V3_VIEWBOX, width)
        print(f'theme_richimath: {target} {art.width}x{art.height} from v3/{source}')

    return 0


if __name__ == '__main__':
    sys.exit(main())
