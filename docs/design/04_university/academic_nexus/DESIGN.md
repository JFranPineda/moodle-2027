---
name: Academic Nexus
colors:
  surface: '#f8f9ff'
  surface-dim: '#ccdbf3'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e6eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d5e3fc'
  on-surface: '#0d1c2e'
  on-surface-variant: '#45464e'
  inverse-surface: '#233144'
  inverse-on-surface: '#eaf1ff'
  outline: '#75777f'
  outline-variant: '#c5c6cf'
  surface-tint: '#4f5e83'
  primary: '#000922'
  on-primary: '#ffffff'
  primary-container: '#0f2042'
  on-primary-container: '#7988b0'
  inverse-primary: '#b6c6f1'
  secondary: '#1d4ed8'
  on-secondary: '#ffffff'
  secondary-container: '#4069f2'
  on-secondary-container: '#fffbff'
  tertiary: '#000d06'
  on-tertiary: '#ffffff'
  tertiary-container: '#002718'
  on-tertiary-container: '#159b6e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d9e2ff'
  primary-fixed-dim: '#b6c6f1'
  on-primary-fixed: '#081a3c'
  on-primary-fixed-variant: '#37466a'
  secondary-fixed: '#dce1ff'
  secondary-fixed-dim: '#b7c4ff'
  on-secondary-fixed: '#001551'
  on-secondary-fixed-variant: '#0039b5'
  tertiary-fixed: '#85f8c4'
  tertiary-fixed-dim: '#68dba9'
  on-tertiary-fixed: '#002114'
  on-tertiary-fixed-variant: '#005137'
  background: '#f8f9ff'
  on-background: '#0d1c2e'
  surface-variant: '#d5e3fc'
typography:
  display-lg:
    fontFamily: inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.025em
  display-md:
    fontFamily: inter
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.005em
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
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: inter
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.04em
  code-sm:
    fontFamily: jetbrainsMono
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  sidebar-w: 260px
  layer-panel-w: 780px
  layer-subpanel-w: 540px
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 2.5rem
  space-3xl: 3rem
  col-gap: 1.5rem
---

## Brand & Style

This design system establishes a high-performance academic environment tailored for higher education scholars, faculty, and administrative staff. Synthesizing the layered, progressive-disclosure architecture of modern institutional platforms with the modular curricular granularity of traditional open-source academic tools, it delivers distraction-free clarity and cognitive poise.

The visual style merges **Corporate / Modern** precision with architectural **Tonal Layering**. The atmosphere is authoritative, composed, and institutional:
- High contrast, pristine typography ensures long-session readability for dense syllabi, multi-step rubrics, and asynchronous discourse.
- A foundational dark slate anchor sidebar establishes institutional gravity, while primary academic workspaces stay airy, crisp, and focused.
- Fluid, multi-tiered contextual panels allow students to inspect assignments (*Tareas*), review grade rubrics (*Calificaciones*), and join synchronous sessions (*Sala Virtual*) without losing their position within the course hierarchy.

## Colors

The palette is engineered around collegiate rigor, accessibility compliance (WCAG 2.1 AAA for body copy), and instant cognitive orientation across complex course modularities.

### Primary & Secondary Hierarchy
- **Institutional Navy (`#0F2042`):** Grounding color used for primary navigation, critical headers, and primary structural commands. Reflects institutional authority and intellectual focus.
- **Royal Blue (`#1D4ED8`):** Dynamic interactive state. Applied to active tabs, current module selections, primary links, focus indicators, and action triggers.
- **Academic Emerald (`#059669`):** Represents completion, submitted deliverables, on-track grades, and open live rooms (*Sala Virtual activa*).
- **Slate Neutral (`#475569`):** Calibrated neutral scale for metadata, secondary rubrics, inactive module counters, and borders.

### Semantic Status Colors
- **Success / Submissions (`#059669`):** Tarea enviada, calificado, sala disponible.
- **Attention / Pending (`#D97706`):** Próxima fecha límite, cuestionario en progreso, borrador guardado.
- **Critical / Overdue (`#DC2626`):** Tarea atrasada, intento fallido, plazo vencido.
- **Informative / Discussion (`#0284C7`):** Foros con respuestas no leídas, anuncio institucional.

### Surface System
- **Sidebar Shell:** `#0F172A` (Deep Slate Base), `#1E293B` (Elevated Nav Item), `#334155` (Divider Line).
- **Canvas Base:** `#F8FAFC` (Anti-fatigue Academic Canvas).
- **Surface Elevation 01 (Card/Container):** `#FFFFFF`.
- **Surface Elevation 02 (Layered Slide-Out Panel):** `#FFFFFF` with structural border `#E2E8F0`.
- **Surface Elevation 03 (Sub-inspector/Rubrics):** `#F1F5F9`.

## Typography

Typography uses **Inter** across all structural levels to maintain neutral, objective, and mathematically balanced text rendering across high-density tables and deep prose modules.

- **Numbers & Metrics:** Use tabular numbers (`font-variant-numeric: tabular-nums;`) across *Calificaciones*, grade weights, rubrics, and assignment countdown timers to ensure clean vertical alignment.
- **Section Headers:** Set with tightened letter spacing (`-0.02em` to `-0.01em`) to create dense, commanding anchors for course titles and syllabus units.
- **Metadata & Subtext:** Set at `body-sm` (`12px`) or `label-md` (`12px`) with deliberate tracking to ensure legibility when displaying timestamps, file payloads, or submission confirmations.

## Layout & Spacing

The canvas is engineered natively for high-resolution desktop learning workstations (optimized at **1920x1080**), utilizing an asymmetric fixed-and-fluid orchestration.

### 1920x1080 Spatial Blueprint
- **Primary Institutional Rail (Left, Persistent):** Fixed width of `260px`, spans full 1080px height. Houses global identity, institution portal switchers, user identity, and master route anchors (Cursos, Calendario, Mensajes, Calificaciones Generales).
- **Core Workspace (Center/Right):** Fluid remaining viewport width (`1660px` when panels are retracted). Features a max-width content container of `1440px` centered within the remaining space with `40px` horizontal interior padding.
- **Slide-Out Content Panels (The Layer System):**
  - **Level 1 Slide-Out Panel (`780px` wide):** Triggered when an activity item (e.g., *Tarea: Ensayo Crítico #2*) is opened. Slides smoothly from the right edge with a backdrop sheet over the canvas.
  - **Level 2 Inspector / Rubric Overlay (`540px` wide):** Secondary nested sheet that slides over Level 1 (e.g., specific grading rubric criterion, feedback annotation, or session technical specs), preserving contextual return paths without full-page reloads.

### Grid Rhythm
- **Course Activity Grid:** 12-column dynamic layout inside the main viewport with `24px` (`col-gap`) gutters.
- Course cards utilize a 3-column span (`col-span-4`), allowing exactly 3 robust course containers per row, or 4 compact containers (`col-span-3`).
- **Module Linear List:** 1-column unified card list with consistent `12px` vertical separation for optimal scanning.

## Elevation & Depth

Visual hierarchy uses **Tactile Layering** and **Surface Step-offs** rather than deep, unfocused drop shadows. Contrast is maintained via deliberate border definition and calibrated z-index layering.

### Depth Stratification
- **Ground 0 (Canvas):** `#F8FAFC` — Base background for the primary course syllabus and dashboard metrics.
- **Level 1 (Cards, Modules, Institutional Widgets):** `#FFFFFF` surface with a crisp border: `1px solid #E2E8F0`. Low-profile elevation: `0 1px 3px rgba(15, 23, 42, 0.05)`.
- **Level 2 (Hovered Cards, Dropdowns, Flyouts):** `#FFFFFF` surface with border `1px solid #CBD5E1` and ambient shadow: `0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.04)`.
- **Level 3 (Slide-out Slide Sheet):** Slides over the main viewport. Solid `#FFFFFF` interior with a left-edge keyline (`1px solid #E2E8F0`) reinforced with directional ambient shadow: `-12px 0 32px -4px rgba(15, 23, 42, 0.12)`.
- **Backdrop Scrim:** `rgba(15, 23, 42, 0.35)` with a backdrop-filter blur of `2px` applied over underlying content to shift focus entirely to the active module panel.

## Shapes

The design system adopts a **Soft (Level 1)** geometric standard. This balance avoids distracting roundness while eliminating harsh, utilitarian corners, creating a modern, professional, academic feel.

- **Base Radius (`4px` / `0.25rem`):** Badges, status tags, form inputs, checkboxes, table cell selectors, micro indicators.
- **Container Radius (`8px` / `0.5rem`):** Activity module rows (*Tareas, Cuestionarios*), dialog modals, dropdown menus, button components.
- **Structural Radius (`12px` / `0.75rem`):** Primary course cards, slide-out panels (top-left and bottom-left bounds when floating), global notification toasts.

## Components

### 1. Primary Navigation Sidebar
- **Background:** `#0F172A` with item hover `#1E293B` and active accent line in Royal Blue (`#1D4ED8`) along the left edge (`3px solid`).
- **Typography:** `14px` weight `500` in `#94A3B8`, switching to `#FFFFFF` with weight `600` for the active item.
- **Course Selector:** Sticky top profile with university badge, user portrait, and term switcher dropdown.

### 2. Activity Module Cards (Moodle Hybrid Paradigm)
- **Container:** White card, `rounded-md`, border `1px solid #E2E8F0`, padding `16px 20px`.
- **Module Indicator Icons:** Left-aligned `40x40px` rounded icon badge color-coded by pedagogical type:
  - *Tareas (Assignments):* Royal Blue tint (`#EFF6FF`), Icon `#1D4ED8`.
  - *Cuestionarios (Quizzes):* Amber tint (`#FEF3C7`), Icon `#D97706`.
  - *Foros (Discussions):* Cyan tint (`#E0F2FE`), Icon `#0284C7`.
  - *Recursos (Resources/PDF):* Slate tint (`#F1F5F9`), Icon `#475569`.
  - *Sala Virtual (Collaborate):* Emerald tint (`#ECFDF5`), Icon `#059669`.
- **Action Zone:** Right-aligned status chip, due-date stamp, and chevron indicating slide-out access.

### 3. Layered Slide-Out Course Content Panel (Blackboard Ultra Paradigm)
- **Width:** `780px` on `1920x1080` screen; slides from right.
- **Header:** Sticky `72px` tall header with breadcrumb trail (`Curso > Unidad 3 > Tarea`), activity title (`headline-md`), and high-contrast dismissal control (`esc` badge or `✕` icon).
- **Body Layout:** Two-column split: Left content column (`65%`) for prompt, reading materials, submission file-drop zone; Right meta column (`35%`) for due date, attempts allowed, maximum points, and grading criteria rubric link.
- **Layer 2 Sub-panel:** Rubric detail overlays from the panel's right side, allowing students to verify grading criteria alongside their active editor.

### 4. Status Chips & Badges
- **Shape:** Height `24px`, padding `0 8px`, `rounded-sm`, text `label-sm` uppercase.
- **Variants:**
  - *Enviado (Submitted):* Background `#ECFDF5`, text `#065F46`, border `1px solid #A7F3D0`.
  - *Pendiente (Pending):* Background `#FEF3C7`, text `#92400E`, border `1px solid #FDE68A`.
  - *Vencido (Overdue):* Background `#FEF2F2`, text `#991B1B`, border `1px solid #FECACA`.
  - *En Vivo (Live Session):* Background `#ECFDF5`, text `#047857`, left pulsating dot (`#10B981`).

### 5. Buttons & Controls
- **Primary:** Background `#0F2042`, text `#FFFFFF`, hover `#1E3A8A`. Height `40px`, padding `0 18px`, `rounded-md`, `label-lg`.
- **Secondary / Action:** Background `#1D4ED8`, text `#FFFFFF`, hover `#1E40AF`.
- **Ghost / Institutional:** Transparent background, text `#475569`, hover `#F1F5F9`.
- **Form Inputs:** Height `40px`, border `1px solid #CBD5E1`, focus outline `2px solid #1D4ED8` with zero ring offset.

### 6. Gradebook / Calificaciones Table Matrix
- **Header:** Background `#F8FAFC`, text `#475569`, border-bottom `2px solid #CBD5E1`.
- **Row:** Height `52px`, alternating zebra tint optional, hover `#F8FAFC`.
- **Score Cell:** Numeric display formatted with tabular figures. Displays obtained grade, total grade, and status chip in an aligned inline flex layout.