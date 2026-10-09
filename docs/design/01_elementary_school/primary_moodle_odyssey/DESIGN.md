---
name: Primary Moodle Odyssey
colors:
  surface: '#f9f9ff'
  surface-dim: '#cfdaf1'
  surface-bright: '#f9f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f3ff'
  surface-container: '#e7eeff'
  surface-container-high: '#dee8ff'
  surface-container-highest: '#d8e3fa'
  on-surface: '#111c2c'
  on-surface-variant: '#424656'
  inverse-surface: '#263142'
  inverse-on-surface: '#ebf1ff'
  outline: '#737687'
  outline-variant: '#c3c6d8'
  surface-tint: '#0052dd'
  primary: '#004ccd'
  on-primary: '#ffffff'
  primary-container: '#0f62fe'
  on-primary-container: '#f3f3ff'
  inverse-primary: '#b4c5ff'
  secondary: '#006d40'
  on-secondary: '#ffffff'
  secondary-container: '#95f3b8'
  on-secondary-container: '#007243'
  tertiary: '#9a3600'
  on-tertiary: '#ffffff'
  tertiary-container: '#c34600'
  on-tertiary-container: '#fff1ed'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174c'
  on-primary-fixed-variant: '#003da9'
  secondary-fixed: '#97f6bb'
  secondary-fixed-dim: '#7cd9a0'
  on-secondary-fixed: '#002110'
  on-secondary-fixed-variant: '#00522f'
  tertiary-fixed: '#ffdbce'
  tertiary-fixed-dim: '#ffb599'
  on-tertiary-fixed: '#370e00'
  on-tertiary-fixed-variant: '#7f2b00'
  background: '#f9f9ff'
  on-background: '#111c2c'
  surface-variant: '#d8e3fa'
typography:
  headline-xl:
    fontFamily: Quicksand
    fontSize: 38px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.5px
  headline-lg:
    fontFamily: Quicksand
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
  headline-md:
    fontFamily: Quicksand
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-sm:
    fontFamily: Quicksand
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-xl:
    fontFamily: Nunito Sans
    fontSize: 19px
    fontWeight: '400'
    lineHeight: 30px
  body-lg:
    fontFamily: Nunito Sans
    fontSize: 17px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Nunito Sans
    fontSize: 15px
    fontWeight: '600'
    lineHeight: 22px
  body-sm:
    fontFamily: Nunito Sans
    fontSize: 13px
    fontWeight: '600'
    lineHeight: 18px
  label-lg:
    fontFamily: Nunito Sans
    fontSize: 16px
    fontWeight: '800'
    lineHeight: 22px
    letterSpacing: 0.3px
  label-md:
    fontFamily: Nunito Sans
    fontSize: 14px
    fontWeight: '700'
    lineHeight: 18px
  label-sm:
    fontFamily: Nunito Sans
    fontSize: 12px
    fontWeight: '800'
    lineHeight: 16px
    letterSpacing: 0.5px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  spacing-2xs: 0.25rem
  spacing-xs: 0.5rem
  spacing-sm: 0.75rem
  spacing-md: 1rem
  spacing-lg: 1.5rem
  spacing-xl: 2rem
  spacing-2xl: 2.5rem
  spacing-3xl: 3rem
  gutter-canvas: 1.5rem
  sidebar-left-width: 280px
  sidebar-right-width: 340px
  content-max-width: 1220px
---

## Brand & Style

The design system establishes a welcoming, cheerful, and secure digital classroom tailored for primary school learners aged 6 to 11, while offering clarity for educators and parents. It harmonizes childhood curiosity with structured digital ergonomics, transforming Moodle's utilitarian architecture into an intuitive learning expedition.

### Design Movement & Aesthetic
- **Tactile Soft-Materialism & Contemporary Playful**: The interface blends gentle, pillowy dimensional qualities with crisp structural cards. Instead of chaotic toy-like graphics, it maintains calm, rhythmic chunking through chunky tap targets, pill badges, and illustrated status glyphs.
- **Cognitive Ease**: High visual clarity, low clutter, and direct affordances eliminate ambiguity for nascent readers and developing motor skills.
- **Accessibility Foundation**: Conceived from the ground up for WCAG 2.1 Level AAA compliance, maintaining robust contrast ratios (minimum 7:1 for normal text, 4.5:1 for large text and interactive components) without sacrificing an energetic, optimistic atmosphere.
- **Emotional Resonance**: Reassurance, accomplishment, curiosity, and warmth. The UI feels like an organized wooden desk paired with dynamic, friendly stickers.

## Colors

The palette balances energy with strict contrast safeguards:

- **Primary (`#0F62FE` - Educational Deep Sky Blue)**: Represents discovery, safety, and digital navigation. Used for primary focus outlines, main navigation states, and verified progress. Passes WCAG AAA (8.4:1 contrast against pure white).
- **Secondary (`#0E7A4A` - Pine Emerald Green)**: Anchors growth, completed achievements, submitted tasks, and positive feedback. Deepened intentionally to achieve 7.2:1 against light tints.
- **Tertiary (`#D94F00` - Vivid Coral Amber)**: Drives action verbs, upcoming due dates, interactive launch buttons, and badge celebrations. Replaces washed-out orange to secure 7.1:1 on white and off-white cards.
- **Neutral (`#4A5568` - Slate Charcoal)**: Mid-tone neutral foundation for high-readability body copy (6.3:1) and secondary captions, stepping down to `#1A202C` (14.5:1) for primary headers.
- **Accent Sunny Yellow (`#B86B00` on light, `#FFF3D6` background)**: Reserved for alert banners, highlight backgrounds, and star award badges, always anchored with high-contrast borders and text.
- **Surface Canvas (`#F4F7FB` / `#FFFFFF`)**: A calm, slightly blue-tinted paper background that diminishes screen glare during long reading sessions.

## Typography

Typography prioritizes terminal clarity for emerging readers:

- **Quicksand** governs all headlines and section titles. Its geometric, open counters, rounded apexes, and organic curves create an approachable feel while avoiding childish infantilization.
- **Nunito Sans** serves body copy and UI labels. It preserves clear letter disambiguation (distinguishing uppercase `I`, lowercase `l`, and digit `1`), essential for early childhood literacy.
- **Type Scale Rules**:
  - Minimum font size anywhere in course units: `15px` (`body-md`), ensuring legibility at arm's-length desktop viewing.
  - Generous line-height ratios (minimum 1.5x on paragraph text) prevent cognitive crowding.
  - Bold weightings (`600` and `700`) are actively applied to labels and callouts to support rapid scanning for children navigating without fully developed phonological processing.

## Layout & Spacing

The layout is constructed around an enhanced 3-column architecture designed for 1920x1080 display environments, fully compliant with Moodle Boost standards:

### Global Grid Architecture (1920x1080)
- **Top Navigation Bar**: Fixed `72px` height across the top edge. Houses institutional identity, course breadcrumb chips, universal search, role switcher, notification bell, and user avatar with level pill.
- **Left Rail (Course Navigation & Outline)**: Fixed `280px` width. Collapsible into an icon drawer to grant more focus room during deep exercises. Displays module topics, progress checkmarks, and home navigation.
- **Central Learning Hub (Course Content)**: Fluid width (`min: 780px`, `max: 1220px`). Structured vertically by week/topic units containing interactive activity cards.
- **Right Block Drawer (Moodle Auxiliary Blocks)**: Fixed `340px` width. Contains Calendar, Upcoming Deadlines, Badges/Gamification Trophy Case, and Quick Teacher Help.
- **Vertical Rhythm**: A strict 8-point base spacing grid. Interactive target touch areas enforce a minimum bounding box of `48px x 48px` to facilitate effortless pointer accuracy for young students.

## Elevation & Depth

Visual hierarchy uses physical, tactile cues rather than fuzzy, disorienting drops:

- **Surface Layer 0 (Canvas)**: `#F4F7FB` soft blue-tinted background.
- **Surface Layer 1 (Cards & Sidebars)**: `#FFFFFF` crisp white panels framed with a solid 1.5px structural border in `#E2E8F0` and an ambient tint shadow: `0 4px 12px -2px rgba(15, 98, 254, 0.06)`.
- **Surface Layer 2 (Interactive Modules & Hover Targets)**: Elevated with a solid bottom highlight simulating 3D keys: `box-shadow: 0 4px 0 0 #CBD5E1`.
- **Interactive Button Depth (Pressable Physics)**:
  - Default: `box-shadow: 0 4px 0 0 [Darker Tint], 0 6px 12px rgba(0,0,0,0.08)`.
  - Active/Pressed: `transform: translateY(3px); box-shadow: 0 1px 0 0 [Darker Tint]`.
- **Focus Rings**: Mandatory 3px solid `#0F62FE` offset by a 2px white gap (`box-shadow: 0 0 0 2px #FFFFFF, 0 0 0 5px #0F62FE`) to support full keyboard and switch navigation.

## Shapes

The geometric personality features soft, organic contours:

- **Standard Containers & Cards**: `16px` border-radius (`rounded-lg`), producing friendly and welcoming panels that reduce visual harshness.
- **Interactive Buttons & Input Fields**: `12px` border-radius, creating an inviting, tactile press surface.
- **Badges, Status Tags, and Role Switchers**: Pill radius (`9999px`) for quick scanning.
- **Activity Icon Glyphs**: Placed within squircle holders (`14px` continuous curvature) featuring pastel tint fills paired with deep saturated iconography.

## Components

### 1. Activity & Resource Cards (Moodle Content Hub)
- **Container**: White surface with a left-edge 6px colored indicator bar designating activity types:
  - **Tarea (Assignment)**: Amber Coral (`#D94F00`)
  - **Cuestionario (Quiz)**: Sky Blue (`#0F62FE`)
  - **Foro (Discussion Forum)**: Violet (`#6929C4`)
  - **Lección Interactiva (Lesson)**: Emerald Green (`#0E7A4A`)
  - **Videoclase (Live Class)**: Red Ruby (`#DA1E28`)
  - **Recursos (Files/Books)**: Deep Teal (`#005D5D`)
- **Metadata**: Left squircle icon (`52px`), title in `headline-sm`, estimated time chip, and a prominent right-side completion button (checkbox with celebratory pop animation).

### 2. Buttons
- **Primary Action (Tertiary Tint / Coral)**: `#D94F00` background, white bold label, `48px` minimum height, tactile bottom border (`#9F3A00`).
- **Secondary Action (Sky Tint)**: `#EBF2FF` background, `#0F62FE` text, 2px solid border (`#B3D1FF`).
- **States**: Clear hover lightness shift, distinct active downward press translation, high-contrast focus outline.

### 3. Checkboxes & Progress Radio Buttons
- Oversized `28px x 28px` base dimension.
- Unchecked: `#FFFFFF` fill with 2.5px solid `#A0AEC0` border.
- Completed (Checked): `#0E7A4A` fill with a bold white checkmark icon, triggering a subtle starburst micro-interaction.

### 4. Moodle Top Bar
- Height: `72px`, background: `#FFFFFF` with `#E2E8F0` bottom boundary.
- **Role Switcher**: Pill dropdown displaying student's current persona (e.g., "Explorador/a - Alumno/a") with clear switch options for teachers/guardians.
- **Streak & Points Counter**: Pill container displaying consecutive days active and earned star points.

### 5. Sidebar Blocks (Boost Architecture)
- **Calendario Escolar**: Monthly visual matrix with color-coded day circles matching activity types.
- **Próximos Eventos (Deadlines)**: Countdown cards indicating remaining days with clear text tags (e.g., "Mañana", "En 3 días").
- **Insignias y Trofeos (Achievements)**: Visual shelf showing locked (silhouette) and unlocked (saturated vector) badges with progress bars.

### 6. Inputs & Search Fields
- Large `48px` height with `16px` padded internal icons, `#FFFFFF` field surface, and 2px `#CBD5E1` boundary. Active typing state shifts border to 2.5px `#0F62FE`.