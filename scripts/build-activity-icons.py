#!/usr/bin/env python3
"""Build the theme's colour activity icons from Moodle's own glyphs.

Each core module ships a one-colour `monologo.svg`; Boost paints it white on a
coloured tile. Richi asked for the same Moodle drawings without the tile and in
vivid colour, so this script keeps every path exactly as core draws it and only
fills it with a two-stop gradient. The result goes to
`theme/richimath/pix_plugins/mod/<module>/monologo.svg`, where the theme
overrides the core icon without touching `mod/`. The four level themes inherit
it through their parent.

Run again after a Moodle upgrade: the glyphs come from the installed `mod/`, so
a redrawn core icon is picked up instead of frozen here.

5.3 (MIG-27, pending Richi's decision): core already draws these icons in
colour and without a tile, and its new glyphs carry an explicit fill on every
path, so this script stops on the first one ("root already sets a fill"). It is
kept for the option of keeping our own gradients; do not run it until MIG-27 is
decided. mod_chat and mod_survey left core in 5.0 and left the palette.

    python3 scripts/build-activity-icons.py
"""
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / 'public' / 'theme' / 'richimath' / 'pix_plugins' / 'mod'

# Start → end of each gradient. Every stop keeps at least 3:1 against white
# (WCAG non-text contrast): the light ends of the "vivid" families — #06B6D4,
# #F59E0B, #22C55E — fall to ~2.3:1 and the thin strokes vanish on the
# course page, so their deeper siblings stand in.
PALETTE = {
    'assign':          ('#F43F5E', '#EA580C'),
    'bigbluebuttonbn': ('#2563EB', '#0891B2'),
    'book':            ('#8B5CF6', '#EC4899'),
    'choice':          ('#D97706', '#DC2626'),
    'data':            ('#6366F1', '#0891B2'),
    'feedback':        ('#EC4899', '#EA580C'),
    'folder':          ('#D97706', '#EA580C'),
    'forum':           ('#16A34A', '#0D9488'),
    'glossary':        ('#A855F7', '#6366F1'),
    'h5pactivity':     ('#0284C7', '#2563EB'),
    'imscp':           ('#65A30D', '#059669'),
    'label':           ('#F43F5E', '#A855F7'),
    'lesson':          ('#0891B2', '#8B5CF6'),
    'lti':             ('#DC2626', '#EC4899'),
    'page':            ('#3B82F6', '#8B5CF6'),
    'quiz':            ('#D946EF', '#6366F1'),
    'resource':        ('#0284C7', '#16A34A'),
    'scorm':           ('#EA580C', '#DB2777'),
    'url':             ('#0891B2', '#3B82F6'),
    'wiki':            ('#D97706', '#16A34A'),
    'workshop':        ('#DC2626', '#D97706'),
}

GRADIENT_ID = 'rmg'


def gradient(start: str, end: str) -> str:
    # userSpaceOnUse would tie the stops to each file's viewBox (74.4 in most,
    # 24 in two); objectBoundingBox follows the glyph, whatever its size.
    return (f'<defs><linearGradient id="{GRADIENT_ID}" x1="0" y1="0" x2="1" y2="1">'
            f'<stop offset="0" stop-color="{start}"/>'
            f'<stop offset="1" stop-color="{end}"/>'
            f'</linearGradient></defs>')


def colour(svg: str, module: str, start: str, end: str) -> str:
    # Illustrator's preamble is noise in a served icon.
    svg = re.sub(r'<\?xml[^>]*\?>\s*', '', svg)
    svg = re.sub(r'<!--.*?-->\s*', '', svg, flags=re.S)

    opening = re.search(r'<svg\b[^>]*>', svg)
    if not opening:
        sys.exit(f'{module}: no <svg> element')

    if module == 'bigbluebuttonbn':
        # The one branded glyph: a solid disc with a white "b". Recolour the
        # disc and leave the letter white, or the "b" would disappear.
        svg = svg.replace('fill="#0F4D9D"', f'fill="url(#{GRADIENT_ID})"')
        tag = opening.group(0)
    else:
        # Every other glyph is unfilled paths, which inherit the root's fill.
        tag = opening.group(0)
        if 'fill=' in tag:
            sys.exit(f'{module}: root already sets a fill; check the new core icon by hand')
        tag = tag[:-1] + f' fill="url(#{GRADIENT_ID})">'

    return svg.replace(opening.group(0), tag + gradient(start, end), 1)


def main() -> None:
    written = 0
    for module, (start, end) in PALETTE.items():
        source = ROOT / 'public' / 'mod' / module / 'pix' / 'monologo.svg'
        if not source.exists():
            sys.exit(f'{module}: {source} is missing')
        target = OUT / module / 'monologo.svg'
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(colour(source.read_text(encoding='utf-8'), module, start, end),
                          encoding='utf-8')
        written += 1

    # A module installed later without an entry here keeps core's glyph — and
    # with the tile gone it would render black. Say so instead of shipping it.
    installed = sorted(p.parent.parent.name for p in (ROOT / 'public' / 'mod').glob('*/pix/monologo.svg'))
    missing = [m for m in installed if m not in PALETTE]
    print(f'{written} icons written to {OUT.relative_to(ROOT)}')
    if missing:
        print('WITHOUT A COLOUR (add them to PALETTE):', ', '.join(missing))


if __name__ == '__main__':
    main()
