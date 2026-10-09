#!/usr/bin/env python3
"""Generate the Kepler quiz as a Moodle XML question bank.

H5P cannot randomise: a .h5p is a static package and every student sees the
same numbers, so the first one to finish can dictate the answers to the rest.
Moodle's **calculated multichoice** question type can: the stem, the right
answer and every distractor are formulas over wildcards, and each attempt draws
a different row from the dataset.

The 360 room keeps the story and teaches the method; this quiz holds the
numbers. Import the output in the course question bank and build a quiz from it.

Usage:  python3 scripts/build-kepler-questions.py [-o OUTPUT.xml]
"""

import argparse
import html
import pathlib

# Pythagorean triples, so every hypotenuse comes out a whole number. Random
# ranges would give 7.2809... and turn a reasoning exercise into arithmetic.
TRIPLES = [(3, 4, 5), (6, 8, 10), (5, 12, 13), (9, 12, 15), (8, 15, 17),
           (12, 16, 20), (7, 24, 25), (20, 21, 29), (15, 20, 25), (10, 24, 26)]
STRIPS = [2, 3, 4, 2, 3, 3, 4, 5, 3, 4]          # strip length for question 2
N = len(TRIPLES)

CATEGORY = 'Estación Kepler'

# The crack is the hypotenuse; its two shadows are the legs.
CRACK = 'sqrt({a}*{a}+{b}*{b})'


def dataset(name, values, minimum, maximum):
    items = ''.join(
        f'      <dataset_item><number>{i}</number><value>{v}</value></dataset_item>\n'
        for i, v in enumerate(values, 1))
    return f"""  <dataset_definition>
    <status><text>private</text></status>
    <name><text>{name}</text></name>
    <type>calculated</type>
    <distribution><text>uniform</text></distribution>
    <minimum><text>{minimum}</text></minimum>
    <maximum><text>{maximum}</text></maximum>
    <decimals><text>0</text></decimals>
    <itemcount>{len(values)}</itemcount>
    <dataset_items>
{items}    </dataset_items>
    <number_of_items>{len(values)}</number_of_items>
  </dataset_definition>
"""


def answer(formula, fraction, feedback):
    return f"""  <answer fraction="{fraction}">
    <text>{{={formula}}}</text>
    <tolerance>0.01</tolerance>
    <tolerancetype>1</tolerancetype>
    <correctanswerformat>1</correctanswerformat>
    <correctanswerlength>2</correctanswerlength>
    <feedback format="html"><text>{html.escape(feedback)}</text></feedback>
  </answer>
"""


def question(name, stem, answers, datasets, correctfb, wildcards):
    check(name, answers, wildcards)
    ans = ''.join(answer(f, fr, fb) for f, fr, fb in answers)
    return f"""<question type="calculatedmulti">
  <name><text>{html.escape(name)}</text></name>
  <questiontext format="html"><text><![CDATA[{stem}]]></text></questiontext>
  <generalfeedback format="html"><text></text></generalfeedback>
  <defaultgrade>1</defaultgrade>
  <penalty>0.3333333</penalty>
  <hidden>0</hidden>
  <synchronize>0</synchronize>
  <single>1</single>
  <shuffleanswers>1</shuffleanswers>
  <answernumbering>abc</answernumbering>
  <correctfeedback format="html"><text>{html.escape(correctfb)}</text></correctfeedback>
  <partiallycorrectfeedback format="html"><text>Casi.</text></partiallycorrectfeedback>
  <incorrectfeedback format="html"><text>Sigue escapando oxígeno. Vuelve a la sala y repasa el método.</text></incorrectfeedback>
{ans}  <unitgradingtype>0</unitgradingtype>
  <unitpenalty>0.1</unitpenalty>
  <showunits>3</showunits>
  <unitsleft>0</unitsleft>
<dataset_definitions>
{''.join(datasets)}</dataset_definitions>
</question>
"""


def check(name, answers, wildcards):
    """Evaluate every alternative over every dataset row and refuse to build if
    the set is unusable.

    Two failures found the hard way, both invisible until a student hits them:

    - **Collisions.** For the (6,8,10) triple the triangle's area and its
      perimeter are both 24, so the right answer and a distractor rendered as
      the same number: the student picks the correct value off the wrong option
      and is marked wrong.
    - **Stray decimals.** One option coming out 5.8309... among whole numbers is
      discarded on sight, without doing any maths.
    """
    import math
    env = {'sqrt': math.sqrt, 'ceil': math.ceil, 'floor': math.floor,
           'abs': abs}
    problems = []
    for row in range(N):
        values = {k: v[row] for k, v in wildcards.items()}
        shown = []
        for formula, _, _ in answers:
            expr = formula
            for k, v in values.items():
                expr = expr.replace('{' + k + '}', str(v))
            value = eval(expr, {'__builtins__': {}}, env)  # noqa: S307 - our own formulas
            shown.append(round(value, 6))
        if len(set(shown)) != len(shown):
            problems.append(f'    fila {row + 1} {values}: opciones repetidas {shown}')
        for value in shown:
            if abs(value - round(value)) > 1e-9:
                problems.append(f'    fila {row + 1} {values}: opción decimal {value}')
    if problems:
        raise SystemExit(f'[{name}] la tanda de alternativas no sirve:\n'
                         + '\n'.join(problems))


def build():
    a = [t[0] for t in TRIPLES]
    b = [t[1] for t in TRIPLES]
    c = [t[2] for t in TRIPLES]          # hypotenuse of the second crack
    d = [t[0] for t in TRIPLES]          # one of its legs

    ds_a = dataset('a', a, min(a), max(a))
    ds_b = dataset('b', b, min(b), max(b))
    ds_t = dataset('t', STRIPS, min(STRIPS), max(STRIPS))
    ds_c = dataset('c', c, min(c), max(c))
    ds_d = dataset('d', d, min(d), max(d))

    qs = []

    qs.append(question(
        'Kepler 1 · La grieta',
        '<p><strong>PASO 1 — MEDIR LA GRIETA</strong></p>'
        '<p>Los sensores no alcanzan la grieta. Solo miden sus dos sombras '
        'sobre ejes que se cortan en ángulo recto:</p>'
        '<ul><li>Sombra horizontal: <strong>{a} m</strong></li>'
        '<li>Sombra vertical: <strong>{b} m</strong></li></ul>'
        '<p>¿Cuánto mide la grieta?</p>',
        [(CRACK, 100, 'Correcto: la grieta es la hipotenusa.'),
         ('{a}+{b}', 0, 'Has sumado las sombras. La grieta es la hipotenusa, no la suma.'),
         ('{a}*{b}', 0, 'Eso es el doble del área del triángulo, no un lado.'),
         ('abs({b}-{a})', 0, 'Restar las sombras no da la hipotenusa.')],
        [ds_a, ds_b],
        'Casco medido. Pasa a pedir el sellador.',
        {'a': a, 'b': b}))

    qs.append(question(
        'Kepler 2 · El sellador',
        '<p><strong>PASO 2 — PEDIR EL SELLADOR</strong></p>'
        f'<p>La grieta que acabas de medir tiene <strong>{{={CRACK}}} m</strong>.</p>'
        '<p>Las tiras de sellador miden <strong>{t} m</strong> cada una y no se '
        'pueden partir. ¿Cuántas tiras necesitas como mínimo para cubrirla entera?</p>',
        [(f'ceil({CRACK}/{{t}})', 100, 'Correcto: al no poder partirlas, se redondea hacia arriba.'),
         (f'floor({CRACK}/{{t}})', 0, 'Redondeaste hacia abajo: la grieta quedaría sin cubrir.'),
         (f'ceil({CRACK}*{{t}})', 0, 'Has multiplicado en vez de dividir.'),
         ('ceil(({a}+{b})/{t})', 0, 'Has usado la suma de las sombras en vez de la grieta.')],
        [ds_a, ds_b, ds_t],
        'Sellador en camino. Aparece una segunda grieta.',
        {'a': a, 'b': b, 't': STRIPS}))

    qs.append(question(
        'Kepler 3 · La segunda grieta',
        '<p><strong>PASO 3 — LA SEGUNDA GRIETA</strong></p>'
        '<p>Una segunda grieta mide <strong>{c} m</strong> y su sombra vertical '
        'es de <strong>{d} m</strong>. Las dos sombras vuelven a cortarse en '
        'ángulo recto.</p>'
        '<p>¿Cuánto mide su sombra horizontal?</p>',
        [('sqrt({c}*{c}-{d}*{d})', 100, 'Correcto: aquí conoces la hipotenusa y buscas un cateto, así que se resta.'),
         ('{c}-{d}', 0, 'Has restado los lados directamente. Pitágoras resta los CUADRADOS.'),
         ('{c}+{d}', 0, 'Has sumado los lados. Ningún lado es la suma de los otros dos.'),
         ('{c}*{d}', 0, 'Eso no es una longitud del triángulo.')],
        [ds_c, ds_d],
        'Las dos grietas localizadas. Toca cortar el parche.',
        {'c': c, 'd': d}))

    qs.append(question(
        'Kepler 4 · El parche',
        '<p><strong>PASO 4 — CORTAR EL PARCHE</strong></p>'
        '<p>Sobre la primera grieta hay que soldar un parche triangular cuyos '
        'catetos son sus dos sombras: <strong>{a} m</strong> y '
        '<strong>{b} m</strong>.</p>'
        '<p>¿Cuántos metros cuadrados de lámina necesitas?</p>',
        [('{a}*{b}/2', 100, 'Correcto: base por altura dividido entre dos.'),
         ('{a}*{b}', 0, 'Ese es el rectángulo entero. El parche es la mitad.'),
         ('{a}+{b}', 0, 'Has sumado los catetos: eso es una longitud, no un área.'),
         ('2*{a}*{b}', 0, 'Has multiplicado de más: el triángulo es la MITAD del rectángulo.')],
        [ds_a, ds_b],
        'Parche cortado. Queda sellar el borde.',
        {'a': a, 'b': b}))

    qs.append(question(
        'Kepler 5 · El borde',
        '<p><strong>PASO 5 — SELLAR EL BORDE</strong></p>'
        '<p>Falta pasar cordón por todo el contorno del parche: las dos sombras '
        '(<strong>{a} m</strong> y <strong>{b} m</strong>) y la propia grieta.</p>'
        '<p>¿Cuántos metros de cordón necesitas?</p>',
        [(f'{{a}}+{{b}}+{CRACK}', 100, '¡Casco sellado! Has recorrido los tres lados del triángulo.'),
         ('{a}+{b}', 0, 'Te has dejado la grieta, que es el lado más largo.'),
         ('2*({a}+{b})', 0, 'Eso sería el perímetro de un rectángulo, no de un triángulo.'),
         (f'3*{CRACK}', 0, 'Has dado por hecho que los tres lados miden igual. No es equilátero.')],
        [ds_a, ds_b],
        '¡Casco sellado! La Estación Kepler recupera la presión.',
        {'a': a, 'b': b}))

    cat = f"""<question type="category">
  <category><text>$course$/top/{CATEGORY}</text></category>
  <info format="html"><text>Preguntas calculadas del escape room 360 «Estación Kepler». Cada alumno recibe números distintos.</text></info>
  <idnumber></idnumber>
</question>
"""
    return '<?xml version="1.0" encoding="UTF-8"?>\n<quiz>\n' + cat + ''.join(qs) + '</quiz>\n'


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('-o', '--output', default='assets/h5p/kepler-preguntas.xml')
    args = ap.parse_args()
    out = pathlib.Path(args.output).resolve()
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(build(), encoding='utf-8')
    print(f'Listo: {out}')
    print(f'{N} valores por comodín · 5 preguntas · importar en '
          'Banco de preguntas → Importar → Formato XML de Moodle')


if __name__ == '__main__':
    main()
