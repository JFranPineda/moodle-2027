---
name: EduMoodle Campus Secundaria
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#434655'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#747686'
  outline-variant: '#c4c5d7'
  surface-tint: '#2151da'
  primary: '#0037b0'
  on-primary: '#ffffff'
  primary-container: '#1d4ed8'
  on-primary-container: '#cad3ff'
  inverse-primary: '#b7c4ff'
  secondary: '#00687a'
  on-secondary: '#ffffff'
  secondary-container: '#57dffe'
  on-secondary-container: '#006172'
  tertiary: '#2c2abc'
  on-tertiary: '#ffffff'
  tertiary-container: '#4648d4'
  on-tertiary-container: '#d1d1ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dce1ff'
  primary-fixed-dim: '#b7c4ff'
  on-primary-fixed: '#001551'
  on-primary-fixed-variant: '#0039b5'
  secondary-fixed: '#acedff'
  secondary-fixed-dim: '#4cd7f6'
  on-secondary-fixed: '#001f26'
  on-secondary-fixed-variant: '#004e5c'
  tertiary-fixed: '#e1e0ff'
  tertiary-fixed-dim: '#c0c1ff'
  on-tertiary-fixed: '#07006c'
  on-tertiary-fixed-variant: '#2f2ebe'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.025em
  display-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
    letterSpacing: -0.005em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0.005em
  label-md:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  code-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  space-xxs: 0.125rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-base: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  gutter-mobile: 1rem
  gutter-desktop: 1.5rem
  max-content-width: 1380px
---

## Brand & Style

The design system is engineered specifically for secondary and upper-secondary school learners (Ages 12–18; Grades 7–12 / ESO / Bachillerato), bridging the gap between playful primary school interfaces and utilitarian higher-education platforms. It balances collegiate credibility with vibrant digital fluency, avoiding childish gamification in favor of purposeful, focused, and rewarding academic momentum.

The design movement is **Modern Academic Neo-SaaS**:
- Crisp, content-first cards organized to tame dense Moodle core markup (`course-content`, `activity-item`, `block-region`).
- High-contrast structure paired with energetic micro-accents to reduce cognitive fatigue during multi-hour homework and study sessions.
- Purposeful visual hierarchy that elevates deadlines, submission states, and feedback loops into high-visibility status indicators.
- Respectful of high schoolers' digital agency: responsive, fast, clutter-free, and styled like premier modern productivity tools rather than legacy bureaucratic institutional software.

## Colors

The color architecture is built around an academic deep-spectrum navy foundation, balanced with vivid cyan and indigo signals to drive interaction and task clarity.

- **Primary (`#1d4ed8` — Academic Blue):** Anchors global navigation, primary course actions, confirmed submission states, and active breadcrumbs.
- **Secondary (`#06b6d4` — Cyan Spark):** Used for forward-looking indicators, active live sessions (`mod_bigbluebuttonbn`), interactive H5P elements, and progress bars.
- **Tertiary (`#6366f1` — Electric Indigo):** Reserved for student self-directed milestones, forum threads (`mod_forum`), peer collaboration tags, and badges.
- **Neutral (`#0f172a` — Slate Midnight):** Deep slate provides crisp typographic clarity and strict baseline contrast against light neutral backgrounds (`#f8fafc` Canvas, `#ffffff` Surfaces, `#e2e8f0` Borders).

### Functional & Academic Semantic Tokens
- **Success / Passed (`#059669`):** Graded submissions meeting mastery thresholds, completed activity checkboxes (`core_completion`).
- **Warning / Approaching (`#d97706`):** Assignments due within 24 hours in `block_timeline`, quiz grace periods.
- **Critical / Overdue (`#dc2626`):** Past-due submissions, failed attempts, urgent teacher alerts.
- **Surface Elevation Mappings:**
  - `surface-canvas`: `#f8fafc`
  - `surface-card`: `#ffffff`
  - `surface-subtle`: `#f1f5f9`
  - `border-soft`: `#e2e8f0`
  - `border-strong`: `#cbd5e1`

## Typography

The type system pairs **Plus Jakarta Sans** for expressive, geometric, modern structural headings with **Inter** for ultra-legible instructional and body text. 

- **Headings (Plus Jakarta Sans):** Provide a contemporary, confident tone that feels relevant to tech-savvy students while maintaining rigorous clarity for subject and module titles.
- **Body & Controls (Inter):** Ensures zero ambiguity in formula notations, assignment rubrics, multiple-choice quiz questions (`mod_quiz`), and system metadata across varied displays and zoom states.
- **Hierarchy Enforcement:** Section titles (`core_course` topics) map directly to `headline-lg`, module activity names map to `headline-sm`, and activity metadata (due dates, completion status) strictly consume `label-md` and `label-sm` to maintain strict scannability.

## Layout & Spacing

The layout is a responsive 12-column asymmetric fluid grid structured to support Moodle's dual-tier layout: main learning stream alongside right-side contextual widgets (`block-region`).

### Grid & Breakpoints
- **Desktop (1200px+):** 12-column grid with a fixed max content boundary of `1380px`. Primary learning stream occupies 8 or 9 columns; sidebar blocks (`block_timeline`, `block_calendar_month`, `block_completionstatus`) occupy 3 or 4 columns.
- **Tablet (768px - 1199px):** Sidebar collapses into a collapsible secondary drawer or flows beneath central activities. The main column adopts full width with 24px inline gutters.
- **Mobile (< 768px):** Single-column layout. Gutters compress to 16px. Sticky course navigation header with horizontal-scrolling topic breadcrumbs.

### Spacing Cadence
- Vertical margins between distinct course modules (`.activity` items) conform to a strict 12px (`space-md`) gap to avoid excessive scrolling while preventing visual collapse.
- Topic cards and course sections employ 24px (`space-lg`) internal padding with 32px (`space-xl`) separating thematic didactic units.

## Elevation & Depth

The design system abandons heavy drop shadows in favor of a crisp **Low-Contrast Outlines + Tonal Elevation** philosophy. This keeps student interfaces uncluttered, rendering fast on school-issued Chromebooks, tablets, and lower-tier mobile hardware.

- **Level 0 (Base Canvas):** `#f8fafc` — Background surface across course categories and global dashboards.
- **Level 1 (Card & Module Resting):** `#ffffff` surface, bounded by a 1px border of `#e2e8f0`, accompanied by a subtle ambient shadow: `0 1px 2px 0 rgba(15, 23, 42, 0.04)`.
- **Level 2 (Interactive Hover / Priority Blocks):** `#ffffff` surface, border transitions to `#cbd5e1`, with shadow: `0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05)`.
- **Level 3 (Modals, Overlays & Sticky Drawers):** `#ffffff` surface, elevated with `0 10px 15px -3px rgba(15, 23, 42, 0.1), 0 4px 6px -4px rgba(15, 23, 42, 0.05)` and a subtle `backdrop-filter: blur(8px)` on supporting veil overlays (`rgba(15, 23, 42, 0.45)`).

## Shapes

The design system implements a **Soft (Level 1)** structural curvature. This provides an organized, polished, and contemporary aesthetic that feels disciplined and collegiate, steering clear of toy-like rounded bubbles.

- **Base Radius (`0.25rem` / 4px):** Applied to badges, table row highlights, form checkboxes, and micro tags.
- **Medium Radius (`0.5rem` / 8px):** The standard token for inputs, standard buttons, activity list items (`mod_assign`, `mod_quiz`), and dropdown menus.
- **Large Radius (`0.75rem` / 12px):** Applied to container surfaces, course section boxes, contextual blocks (`block_timeline`), and modal dialogs.
- **Pill Exception:** Full roundedness (`9999px`) is reserved exclusively for interactive avatar chips and numerical status notification badges.

## Components

### Buttons
- **Primary:** Background `#1d4ed8`, text `#ffffff`, font weight 600. On hover: `#1e40af`. Active: `#1e3a8a`. Used for "Add Submission", "Attempt Quiz Now", and "Join Session".
- **Secondary:** Background `#ffffff`, border `1px solid #cbd5e1`, text `#0f172a`. On hover: `#f1f5f9`.
- **Accent Action:** Background `#06b6d4`, text `#ffffff`. Reserved for interactive launches (`mod_h5p`, `mod_bigbluebuttonbn`).
- **Dimensions:** Default height 40px, padding 0 16px, border-radius 8px. Compact height 32px, padding 0 12px, border-radius 6px.

### Inputs & Forms
- **Fields:** Surface `#ffffff`, border `1px solid #cbd5e1`, height 42px, radius 8px, font Inter 14px.
- **States:** Focus produces `border-color: #1d4ed8` paired with `box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15)`. Error state utilizes `#dc2626` outline and text feedback.

### Activity Items (`core_course` Activity Feed)
- **Container:** White surface, border `1px solid #e2e8f0`, radius 8px, padding 12px 16px. Displays left-aligned module-specific iconography (Blue for Assign, Cyan for BBB, Violet for Forum, Amber for Quiz).
- **Completion Trigger:** Right-aligned interactive pill:
  - *Not done:* Bordered dashed `#cbd5e1` with gray check icon.
  - *Completed:* Solid `#059669` background with white check icon.

### Cards & Sidebar Blocks
- **Course & Sidebar Blocks (`block_timeline`, `block_calendar_month`):** Header features `headline-sm` with a top 3px accent stroke matching module context. Internal list items use subtle divider lines (`#f1f5f9`).
- **Assignment Summary Card (`mod_assign`):** Emphasizes submission state via full-width top progress tint: Green for Submitted, Amber for Draft/Pending, Red for Missing.

### Chips & Badges
- **Academic Tags:** Height 24px, radius 4px, font size 11px uppercase with 0.04em letter-spacing.
  - *Overdue:* Background `#fef2f2`, text `#991b1b`.
  - *Graded:* Background `#ecfdf5`, text `#065f46`.
  - *Live Now:* Background `#ecfeff`, text `#0e7490` with pulsing cyan dot.