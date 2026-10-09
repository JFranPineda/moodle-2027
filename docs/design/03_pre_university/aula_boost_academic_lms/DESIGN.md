---
name: Aula Boost Academic LMS
colors:
  surface: '#f6f9ff'
  surface-dim: '#d4dbe3'
  surface-bright: '#f6f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eef4fd'
  surface-container: '#e8eef7'
  surface-container-high: '#e2e9f1'
  surface-container-highest: '#dce3ec'
  on-surface: '#151c22'
  on-surface-variant: '#414752'
  inverse-surface: '#2a3138'
  inverse-on-surface: '#ebf1fa'
  outline: '#717783'
  outline-variant: '#c1c7d3'
  surface-tint: '#005fac'
  primary: '#005498'
  on-primary: '#ffffff'
  primary-container: '#0f6cbf'
  on-primary-container: '#e4edff'
  inverse-primary: '#a4c9ff'
  secondary: '#315ca9'
  on-secondary: '#ffffff'
  secondary-container: '#86adff'
  on-secondary-container: '#033e8a'
  tertiary: '#834000'
  on-tertiary: '#ffffff'
  tertiary-container: '#a85300'
  on-tertiary-container: '#ffe7da'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d4e3ff'
  primary-fixed-dim: '#a4c9ff'
  on-primary-fixed: '#001c39'
  on-primary-fixed-variant: '#004884'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#aec6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#0f4490'
  tertiary-fixed: '#ffdcc6'
  tertiary-fixed-dim: '#ffb786'
  on-tertiary-fixed: '#311300'
  on-tertiary-fixed-variant: '#723600'
  background: '#f6f9ff'
  on-background: '#151c22'
  surface-variant: '#dce3ec'
typography:
  display-lg:
    fontFamily: inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: inter
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 30px
    letterSpacing: 0em
  headline-md:
    fontFamily: inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  title-md:
    fontFamily: inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 22px
  body-lg:
    fontFamily: inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-sm:
    fontFamily: inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.02em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  navbar-height: 4rem
  drawer-width-left: 18rem
  drawer-width-right: 20rem
  content-max-width: 75rem
  gutter-desktop: 1.5rem
  gutter-mobile: 1rem
---

## Brand & Style

This design system targets pre-university and college-preparatory students preparing for competitive entrance examinations within a structured institutional environment. The interface balances rigorous academic credibility with welcoming, stress-reducing clarity. Students interact with heavy volumes of curriculum materials, practice tests, timed assignments, and progress monitoring dashboards daily; therefore, visual ergonomics and functional predictability take precedence over decorative flair.

The design movement is **Corporate / Modern** optimized for higher education learning management systems (inspired by Moodle 4.x/5.x Boost architecture). It features disciplined structural alignment, explicit content segmentation, high-contrast legibility, and standardized modular drawers. Visual weight reinforces academic progression through clear status signaling, low cognitive load, and unmistakable interactive affordances.

## Colors

The color architecture is built for extended study sessions with an accessible light-mode baseline.

- **Primary (`#0F6CBF`)**: Classic institutional Moodle Boost blue. Applied to dominant interactive triggers, active tab indicators, breadcrumb links, course progress fills, and primary navigation drawer highlights.
- **Secondary (`#023E8A`)**: Deep academic navy. Anchors persistent navigational framing including the primary top navbar, high-level headers, and formal institution badges.
- **Tertiary / Accent (`#F58220`)**: Energetic warm amber. Reserved strictly for calls to action, upcoming assignment deadlines, unread notification indicators, caution-level completion milestones, and pending exam alerts.
- **Neutral Core**: Built upon a pragmatic greyscale scale:
  - Surface Canvas (`#F8F9FA`): Neutral course background reducing eye fatigue.
  - Surface Card (`#FFFFFF`): Crisp white topic cards, drawer blocks, and test panels.
  - Surface Border & Structural Dividers (`#DEE2E6` to `#E9ECEF`): Definitive boundaries defining blocks and topics.
  - Body Copy (`#212529`) and Secondary Typography (`#495057`): WCAG AAA compliant text contrast.
- **Functional Semantics**:
  - Success (`#198754`): Graded passes, completed activities, submitted quizzes.
  - Danger / Urgent (`#DC3545`): Overdue tasks, missed deadlines, live exam warnings.
  - Info (`#0DCAF0`): Non-evaluative instructional announcements.

## Typography

The design system relies entirely on **Inter** (with native system sans-serif fallback) to deliver extreme clarity in complex academic dashboards, formula-dense lesson notes, and timed quiz interfaces.

- **Display & Headlines**: Tightly tracked (`-0.02em` to `-0.01em`) with solid line-height to ensure course titles, topic unit titles, and section headers do not collide across responsive breakpoints.
- **Body Text**: Tuned to a generous line-height (`1.6x` body-lg, `1.57x` body-md) ensuring syllabus narratives, exam instructions, and discussion forum threads remain strain-free during long study stretches.
- **Labels & Microcopy**: Uppercase or semi-bold proportional tracking (`0.02em` to `0.04em`) applied to activity state indicators (e.g., "SUBMITTED", "GRADED", "DUE TOMORROW"), breadcrumb anchors, and course module completion chips.

## Layout & Spacing

The layout model is an academic three-column architectural canvas inspired by the Moodle 4.x/5.x Boost standard:

1. **Top Boost Navbar (Fixed, 64px / 4rem)**: Spans the full viewport width (`#023E8A` secondary navy or crisp white with `#DEE2E6` border). Houses primary app switcher, institution branding, site navigation, search, and the student profile dropdown.
2. **Left Drawer (Course Index / Topic Outline)**:
   - Width: `18rem` (`288px`).
   - Sticky, collapsible drawer containing hierarchical section trees, quiz jumping anchors, and completion checkmarks.
3. **Center Main Stream (Course Workspace)**:
   - Fluid grid spanning available central real estate with a maximum container limit of `75rem` (`1200px`) for optimal reading line-length.
   - Hosts the breadcrumb trail, course header banner, activity progress bar, and vertical stack of section/topic cards.
4. **Right Drawer (Auxiliary Block Region)**:
   - Width: `20rem` (`320px`).
   - Off-canvas or collapsible panel holding standard Moodle utility blocks: "Completion Progress Bar", "Upcoming Events", "Latest Announcements", and "Online Users".

### Responsive Breakpoints
- **Desktop (≥ 1200px)**: Three-column layout active. Left course index and right block drawer can both remain open simultaneously without obscuring the central stream.
- **Tablet / Laptop (768px – 1199px)**: Left drawer becomes an off-canvas slide-over triggered via the top-left hamburger button. Right drawer defaults to a collapsed rail with slide-over behavior.
- **Mobile (< 768px)**: Both drawers are off-canvas overlays with backdrop blur. The central stream switches to single-column full width with `1rem` horizontal margins.

## Elevation & Depth

The design system adopts a functional **Low-Contrast Outlines & Micro-Tonal Elevation** philosophy. LMS environments require visual order, not deep theatrical shadows that create visual fatigue across dozens of stacked cards.

- **Level 0 (Base Surface)**: `#F8F9FA` canvas background. Flat, zero elevation.
- **Level 1 (Cards & Topic Sections)**: `#FFFFFF` surface enclosed by a crisp, 1px solid border (`#E9ECEF` or `#DEE2E6`). Shadow: `0 1px 3px rgba(0, 0, 0, 0.04)`.
- **Level 2 (Interactive Cards on Hover & Auxiliary Blocks)**: Used for active topic sections, hovered assignments, and sidebar blocks. Shadow: `0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.03)` with border shifting to `#CED4DA`.
- **Level 3 (Sticky Navbars & Drawers)**: Course index left drawer and right block drawer. Elevation relies on a right-hand vertical border divider (`1px solid #DEE2E6`) paired with an ambient boundary shadow: `2px 0 8px rgba(0, 0, 0, 0.03)` (left) and `-2px 0 8px rgba(0, 0, 0, 0.03)` (right).
- **Level 4 (Modals & Overlays)**: Assignment rubric modals, timed exam confirmation dialogues, and off-canvas mobile drawers. Shadow: `0 12px 24px -4px rgba(2, 62, 138, 0.12), 0 6px 12px -2px rgba(0, 0, 0, 0.05)`.

## Shapes

The design system implements a **Soft (`1`)** roundedness scheme. In a technical, high-density educational system, overly rounded or pill-shaped containers waste critical corner space and degrade the visual authority of academic documents and exam grids.

- **Micro Components (0.25rem / 4px)**: Checkboxes, table cells, form inputs, breadcrumb badges, and progress bar tracks.
- **Standard UI Elements (0.375rem to 0.5rem / 6px to 8px)**: Activity item rows, resource cards, buttons, dropdown menus, and block cards.
- **Large Panels (0.75rem / 12px)**: Primary course header hero card, modal containers, and multi-topic grouping cards.
- **Pills (`9999px`)**: Solely reserved for contextual status badges (e.g., "COMPLETED", "OPTIONAL", "PASSING").

## Components

### Buttons
- **Primary**: Solid `#0F6CBF` with white text, `height: 2.375rem` (38px), `padding: 0 1rem`, `font-size: 14px`, `font-weight: 500`. Hover state darkens to `#0B5699` with smooth `150ms` transition.
- **Secondary / Outline**: 1px solid border (`#0F6CBF`) with `#0F6CBF` text on transparent background. Hover transitions to `#F0F7FF`.
- **Tertiary / Action (Exam Submissions)**: Solid amber `#F58220` with white text for urgent student actions (e.g., "Comenzar Intento del Examen", "Subir Tarea").
- **Ghost / Icon Buttons**: Zero background, neutral text (`#495057`), hover state `#E9ECEF` with `border-radius: 4px`. Used for drawer triggers and block collapse toggles.

### Breadcrumbs
- Standard Moodle Boost navigation path: `Inicio > Mis Cursos > Ciclo Preuniversitario 2025 > Álgebra > Unidad 3: Polinomios`.
- Separators use subtle forward slashes (`/`) in `#ADB5BD`.
- Interactive items styled in `#0F6CBF`, hover with underline; current/active page terminal node styled in bold `#212529`.

### Course Topic & Activity Cards
- **Section Headers**: Accordion-style headers with a left chevron indicator, section title in `headline-sm`, and an inline completion fraction counter (e.g., `4 / 6 Completado`).
- **Activity Item Row**: Clean horizontal list item inside topic cards (`min-height: 52px`).
  - Left: Standardized Moodle 4.x activity type icon container (28×28px rounded box with pastel category background: Blue for PDF/Page, Green for Quiz, Orange for Assignment, Purple for Forum).
  - Center: Activity title with description preview and due date metadata in `body-sm` (`#6C757D`).
  - Right: Automatic completion checkbox / status pill (`Completado: Visto` or `Pendiente`).

### Drawers & Navigation Trees
- **Left Course Index Drawer**:
  - Vertical tree with indent levels.
  - Active section highlighted with a 3px left border in `#0F6CBF` and a light blue background (`#F0F7FF`).
  - Circular progress completion icons alongside each topic row: grey ring for pending, green solid checkmark for completed.
- **Right Block Drawer**:
  - Encapsulated modular cards with a distinct card header (`title-md` in `#023E8A`), border bottom (`1px solid #E9ECEF`), and collapsible caret.
  - *Block: Barra de Progreso (Completion Progress)*: Multi-segment segmented bar showing green (completed), yellow (submitted), and blue/grey (future).
  - *Block: Eventos Próximos (Upcoming)*: Date badge (calendar block with month/day) + event title with direct jump link.

### Checkboxes & Radio Buttons
- Strict 18×18px squares/circles with a 1.5px border (`#CED4DA`).
- Checked state uses primary blue `#0F6CBF` with crisp white checkmark/dot.
- Indeterminate state for multi-part assignment modules.

### Input Fields & Search Bars
- Background: `#FFFFFF` with 1px border (`#CED4DA`), padding `0.5rem 0.75rem`, font size `14px`.
- Focus state: Border transitions to `#0F6CBF` with an accessible focus ring: `box-shadow: 0 0 0 0.2rem rgba(15, 108, 191, 0.2)`.
- Global search in top navbar: integrated icon prefix with rounded pill styling (`height: 38px`, background `#F1F3F5`).

### Chips & Badges
- **Status Pills**: Height `22px`, font size `11px`, bold, uppercase, horizontal padding `8px`.
  - Finalizado: `#D1E7DD` background with `#0F5132` text.
  - Vence Pronto: `#FFE8CC` background with `#D9480F` text.
  - Obligatorio: `#E7F5FF` background with `#1864AB` text.