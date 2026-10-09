---
name: Imperial Academic Precision
colors:
  surface: '#0f131c'
  surface-dim: '#0f131c'
  surface-bright: '#353943'
  surface-container-lowest: '#0a0e17'
  surface-container-low: '#181b25'
  surface-container: '#1c1f29'
  surface-container-high: '#262a34'
  surface-container-highest: '#31353f'
  on-surface: '#dfe2ef'
  on-surface-variant: '#d8c3ad'
  inverse-surface: '#dfe2ef'
  inverse-on-surface: '#2c303a'
  outline: '#a08e7a'
  outline-variant: '#534434'
  surface-tint: '#ffb95f'
  primary: '#ffc174'
  on-primary: '#472a00'
  primary-container: '#f59e0b'
  on-primary-container: '#613b00'
  inverse-primary: '#855300'
  secondary: '#c1c7cf'
  on-secondary: '#2b3137'
  secondary-container: '#41474e'
  on-secondary-container: '#afb6bd'
  tertiary: '#8fd5ff'
  on-tertiary: '#00344a'
  tertiary-container: '#1abdff'
  on-tertiary-container: '#004966'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffddb8'
  primary-fixed-dim: '#ffb95f'
  on-primary-fixed: '#2a1700'
  on-primary-fixed-variant: '#653e00'
  secondary-fixed: '#dde3eb'
  secondary-fixed-dim: '#c1c7cf'
  on-secondary-fixed: '#161c22'
  on-secondary-fixed-variant: '#41474e'
  tertiary-fixed: '#c5e7ff'
  tertiary-fixed-dim: '#7fd0ff'
  on-tertiary-fixed: '#001e2d'
  on-tertiary-fixed-variant: '#004c6a'
  background: '#0f131c'
  on-background: '#dfe2ef'
  surface-variant: '#31353f'
typography:
  display-lg:
    fontFamily: Newsreader
    fontSize: 4rem
    fontWeight: '400'
    lineHeight: 4.5rem
    letterSpacing: -0.025em
  display-lg-mobile:
    fontFamily: Newsreader
    fontSize: 2.25rem
    fontWeight: '400'
    lineHeight: 2.75rem
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Newsreader
    fontSize: 2.5rem
    fontWeight: '400'
    lineHeight: 3rem
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Newsreader
    fontSize: 1.75rem
    fontWeight: '400'
    lineHeight: 2.25rem
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Newsreader
    fontSize: 1.75rem
    fontWeight: '400'
    lineHeight: 2.25rem
  headline-sm:
    fontFamily: Newsreader
    fontSize: 1.25rem
    fontWeight: '500'
    lineHeight: 1.75rem
  body-lg:
    fontFamily: Geist
    fontSize: 1.125rem
    fontWeight: '400'
    lineHeight: 1.75rem
  body-md:
    fontFamily: Geist
    fontSize: 0.9375rem
    fontWeight: '400'
    lineHeight: 1.5rem
  body-sm:
    fontFamily: Geist
    fontSize: 0.8125rem
    fontWeight: '400'
    lineHeight: 1.25rem
  label-lg:
    fontFamily: JetBrains Mono
    fontSize: 0.875rem
    fontWeight: '500'
    lineHeight: 1.25rem
    letterSpacing: 0.05em
  label-md:
    fontFamily: JetBrains Mono
    fontSize: 0.75rem
    fontWeight: '500'
    lineHeight: 1rem
    letterSpacing: 0.08em
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 0.6875rem
    fontWeight: '400'
    lineHeight: 0.875rem
    letterSpacing: 0.1em
spacing:
  gutter-xs: 0.25rem
  gutter-sm: 0.5rem
  gutter-md: 1rem
  gutter-lg: 1.5rem
  gutter-xl: 2rem
  margin-mobile: 1rem
  margin-tablet: 2rem
  margin-desktop: 3.5rem
  col-gap: 1.5rem
  row-gap: 2rem
---

## Brand & Style

This design system establishes an ultra-refined, prestigious academic atmosphere engineered specifically for high-level mathematical study and mastery. Drawing from classical luxury publishing and brutalist technical precision, the aesthetic merges the gravitas of elite institutional academia with modern computational sharpness. 

The emotional impact must evoke discipline, uncompromising rigor, and elevated prestige. Users should feel they are stepping into an exclusive digital institute where mathematics is treated as a high craft. Surfaces are dark and dense, punctuated by fine-gauge metallic boundaries and polished gilded accents. 

Visual attributes:
- **Style Archetype**: Monochromatic Luxury Minimalism married with Precision Technical Architecture.
- **Atmosphere**: Deep obsidian expanses layered with platinum hairline dividers and concentrated flashes of burnished gold.
- **Density**: High-efficiency, mathematical clarity, disciplined spacing rhythm without frivolous visual clutter.

## Colors

The palette is strictly restricted to an uncompromising triad of Deep Obsidian, Cool Metallic Platinum/Silver, and Burnished Gold. No semantic reds, greens, or chromatic blues are permitted; status, hierarchies, and states are communicated purely through value steps, luminous contrast, and metallic brilliance.

### Palette Roles
- **Primary (Burnished Gold - `#f59e0b`, `#eab308`, `#d97706`, `#b45309`)**: Reserved strictly for high-order focal points: primary action targets, active theorem states, mastery metrics, verified insignia, and focus indicator rings.
- **Secondary (Platinum & Silver - `#ffffff`, `#f1f5f9`, `#e2e8f0`, `#cbd5e1`, `#94a3b8`)**: Provides razor-sharp technical articulation. Used for structural grid borders, data formulas, secondary indicators, interactive hover lines, and high-readability typographic layers.
- **Neutral / Obsidian (`#000000`, `#090d16`, `#0f172a`)**: The void canvas. Grounding layers that absorb light to elevate mathematical typography and gold interfaces.

### States & Validation
- **Success / Validated**: Pure Platinum White (`#ffffff`) fill with high-luminous Gold border (`#f59e0b`).
- **Warning / Alert**: Deep Gold Core (`#d97706`) with Amber-Gold outline (`#b45309`).
- **Error / Invalidation**: High-contrast Silver-Slate glyph (`#94a3b8`) framed in dense stark black with sharp double-stroke platinum lines; never rely on red.
- **Disabled**: Obsidian surface `#0f172a` at 40% opacity with low-tier silver border `#94a3b8` at 20% opacity.

## Typography

The typographic hierarchy pairs classical academic authority with modern computational rigor.

1. **Academic Authority (Headlines - `Newsreader`)**: Evoking classical mathematics texts, treatises, and formal academic documentation. Used in titles, lecture names, chapter headers, and modal thesis statements.
2. **Computational Precision (Body - `Geist`)**: Neutral, crystal-clear grotesk typography ensuring that long technical passages, proof walkthroughs, and problem statements remain fatigue-free.
3. **Mathematical Notation & Labels (Code & Metadata - `JetBrains Mono`)**: Strict tabular and formulaic alignment. Used for timestamps, formula tokens, grade matrices, step identifiers, and technical metadata.

## Layout & Spacing

The layout is built upon a 12-column architectural grid inspired by scientific manuscript layout and precision financial terminals.

- **Desktop (1200px+)**: 12 columns, 56px page margins (`margin-desktop`), 24px gutters (`col-gap`). Content is anchored by a persistent split-pane architecture: left navigation index (2 columns), central proof and problem canvas (7 columns), and right inspection/telemetry drawer (3 columns).
- **Tablet (768px - 1199px)**: 8 columns, 32px margins (`margin-tablet`), 16px gutters. Telemetry shifts into a collapsable bottom drawer or overlay.
- **Mobile (Below 768px)**: 4 columns, 16px margins (`margin-mobile`), 12px gutters. Proofs and formula inputs consume 100% horizontal safe area to maintain equation readability.

Every layout partition must align flush to crisp bounding rules; avoid floating detached content cards without structural platinum dividers.

## Elevation & Depth

To maintain high-performance sharpness and academic seriousness, the system rejects standard blurry drop-shadows. Depth is achieved via **tonal stratification and low-contrast metallic hairline borders**.

- **Level 0 (Base Canvas)**: `#000000` — Pure void background for maximum optical contrast.
- **Level 1 (Sub-Surfaces & Panes)**: `#090d16` bordered with a 1px hairline stroke of `#cbd5e1` at 15% opacity (`rgba(203, 213, 225, 0.15)`).
- **Level 2 (Active Cards & Toolbars)**: `#0f172a` bordered with 1px hairline stroke of `#e2e8f0` at 30% opacity.
- **Level 3 (Modals, Overlays, Dropdowns)**: `#090d16` with a precision 1px border of `#f59e0b` (Gold) or `#ffffff` (White) at 40% opacity, accompanied by an ambient, sharp obsidian backlight: `0 0 0 1px rgba(245, 158, 11, 0.2), 0 20px 40px -15px rgba(0, 0, 0, 0.95)`.

## Shapes

The shape grammar is strictly **Sharp (`0`)**. Every element — buttons, inputs, modal containers, data chips, and tabs — features crisp, 0px radius angles.

Sharp corners reflect mathematical exactitude, precision drafting tools, and institutional legacy. Rounded or bubbly shapes are strictly prohibited as they dilute the intellectual severity and formal luxury of the platform.

## Components

### Buttons
- **Primary (Imperial Gold)**: Solid `#f59e0b` fill, text in obsidian `#000000` (`JetBrains Mono`, bold uppercase), 0px radius. Hover shifts to luminous gold `#eab308` with an interior inset line of pure white `#ffffff` at 30% opacity. Active state drops to `#d97706`.
- **Secondary (Platinum Monolith)**: `#090d16` background with a continuous 1px `#e2e8f0` stroke and `#f1f5f9` typography. Hover: `#0f172a` fill, border brightens to pure white `#ffffff`.
- **Ghost (Terminal Link)**: Transparent background, `#cbd5e1` text with an underline offset of 4px. Hover transforms text to `#f59e0b` and underline to solid gold.

### Chips & Badges
- Constructed with `JetBrains Mono` label-sm text. 
- Background: `#0f172a`. Border: 1px `#94a3b8` at 30% opacity. 
- Mastery/Distinction Badge: Solid `#000000` backdrop with 1px metallic gold border (`#f59e0b`) and shimmering gold typography (`#f59e0b`).

### Lists & Problem Tables
- Tabular data renders flush with alternating obsidian steps (`#000000` and `#090d16`).
- Dividers are 1px solid hairline rules (`#cbd5e1` at 12% opacity).
- Hovering over a problem row activates a 2px left border accent in gold (`#f59e0b`) and raises the row surface to `#0f172a`.

### Checkboxes & Radios
- Sharp 0px square inputs.
- Unchecked: `#090d16` background with 1px `#94a3b8` border.
- Checked: `#f59e0b` solid gold background with a `#000000` geometric checkmark or nested square.

### Input Fields & LaTeX Formula Prompts
- Background: `#090d16`. Border: 1px `#94a3b8` at 30% opacity. Typography: `Geist` or `JetBrains Mono` in `#ffffff`.
- Focus state: Instant transition to a 1px pure gold (`#f59e0b`) perimeter outline with zero blur ring.
- Prefix/Suffix tags (e.g., coordinate units, $\int$, $\sum$) rendered in `#94a3b8` with a right hairline divider.

### Mathematical Proof Cards
- Surface: `#090d16`.
- Outline: 1px `#cbd5e1` at 20% opacity.
- Header contains an institutional status bar: Serif title (`Newsreader`) on the left, step coordinate badge (`[STEP 04/12]`) in `JetBrains Mono` on the right, framed in thin metallic rules.