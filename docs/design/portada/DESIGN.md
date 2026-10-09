---
name: Modern Virtual Classroom
colors:
  surface: '#f8f9fb'
  surface-dim: '#d9dadc'
  surface-bright: '#f8f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f6'
  surface-container: '#edeef0'
  surface-container-high: '#e7e8ea'
  surface-container-highest: '#e1e2e4'
  on-surface: '#191c1e'
  on-surface-variant: '#434654'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f3'
  outline: '#737686'
  outline-variant: '#c3c5d7'
  surface-tint: '#1353d8'
  primary: '#003fb1'
  on-primary: '#ffffff'
  primary-container: '#1a56db'
  on-primary-container: '#d4dcff'
  inverse-primary: '#b5c4ff'
  secondary: '#006c4a'
  on-secondary: '#ffffff'
  secondary-container: '#73fbbf'
  on-secondary-container: '#00734f'
  tertiary: '#8a2600'
  on-tertiary: '#ffffff'
  tertiary-container: '#b33400'
  on-tertiary-container: '#ffd4c8'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b5c4ff'
  on-primary-fixed: '#00174d'
  on-primary-fixed-variant: '#003dab'
  secondary-fixed: '#73fbbf'
  secondary-fixed-dim: '#53dea5'
  on-secondary-fixed: '#002114'
  on-secondary-fixed-variant: '#005237'
  tertiary-fixed: '#ffdbd0'
  tertiary-fixed-dim: '#ffb59e'
  on-tertiary-fixed: '#3a0b00'
  on-tertiary-fixed-variant: '#852400'
  background: '#f8f9fb'
  on-background: '#191c1e'
  surface-variant: '#e1e2e4'
typography:
  display-lg:
    fontFamily: Atkinson Hyperlegible Next
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Atkinson Hyperlegible Next
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Atkinson Hyperlegible Next
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Atkinson Hyperlegible Next
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: 0.01em
  caption:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max-width: 1280px
  gutter: 24px
  margin-desktop: 40px
  margin-mobile: 16px
---

## Brand & Style
The design system is centered on **Modern Minimalism with Soft Tactile elements**, specifically engineered to reduce cognitive load while fostering an environment of academic confidence. The target audience spans from young learners to adult students, necessitating a balance between "friendly/approachable" and "reliable/professional."

The emotional response should be one of "calm focus." By utilizing significant whitespace (negative space) and a clear visual hierarchy, the interface guides students through their learning journey without distraction. The style avoids the austerity of pure corporate minimalism by using organic rounded corners and soft, ambient shadows to create a welcoming, safe digital space.

## Colors
This design system utilizes a palette designed for clarity and motivation:
- **Primary (Professional Blue):** Used for navigation, headers, and primary branding elements to establish authority and trust.
- **Secondary (Soft Green):** Specifically reserved for progress indicators, success states, and completed milestones to provide positive reinforcement.
- **Accent (Vibrant Orange):** High-contrast color dedicated exclusively to primary Calls to Action (CTAs) like "Join Class" or "Submit Assignment."
- **Neutrals:** A range of cool grays (from `#F9FAFB` to `#111827`) to maintain a clean canvas and ensure AA/AAA accessibility for text contrast.

## Typography
The system prioritizes legibility above all else. We use **Atkinson Hyperlegible Next** for headings to ensure students with visual impairments can easily distinguish between similar character shapes. **Inter** is used for body text and UI labels due to its neutral, systematic nature and excellent screen performance.

Line heights are intentionally generous (1.5x for body text) to prevent text crowding. Large display sizes scale down for mobile devices to maintain a clear reading path without excessive scrolling.

## Layout & Spacing
The layout follows a **Fixed-Fluid Hybrid Grid**. Content is housed in a centered container with a maximum width of 1280px to prevent line lengths from becoming too long for comfortable reading. 

- **Desktop (1024px+):** 12-column grid with 24px gutters.
- **Tablet (768px - 1023px):** 8-column grid with 20px gutters.
- **Mobile (Up to 767px):** 4-column grid with 16px gutters and 16px side margins.

Spacing follows a linear 8px base unit. Section margins are generous (using 64px or 80px) to separate different learning modules visually without needing heavy dividers.

## Elevation & Depth
To maintain a modern and clean look, the design system uses **Ambient Shadows** and **Tonal Layering** rather than harsh borders.

- **Level 0 (Background):** Pure white or ultra-light gray (`#F9FAFB`) for the main canvas.
- **Level 1 (Cards/Sidebar):** Slightly elevated using a very soft shadow: `0px 4px 20px rgba(0, 0, 0, 0.05)`.
- **Level 2 (Modals/Active States):** Higher elevation with a more pronounced shadow: `0px 12px 32px rgba(0, 0, 0, 0.1)`.

Surfaces use subtle 1px borders in a light neutral tint to define boundaries on low-contrast screens.

## Shapes
The shape language is consistently **Rounded**. This choice removes "visual sharpness," making the technology feel more approachable and less intimidating for students. 

- **Standard Buttons & Inputs:** 0.5rem (8px) corner radius.
- **Cards & Containers:** 1rem (16px) corner radius.
- **Avatars & Progress Pills:** Fully rounded (pill-shaped).

## Components
- **Buttons:** Primary buttons use the Accent Orange for high visibility. Secondary buttons use the Professional Blue outline. All buttons include a subtle lift on hover.
- **Inputs:** Clean fields with 1px neutral borders that thicken and turn Blue on focus. Error states use a soft red, but emphasize descriptive icons for accessibility.
- **Cards:** Used for courses and lessons. They feature a 16px radius, soft ambient shadow, and a "Secondary Green" progress bar at the bottom.
- **Chips/Badges:** Used for tags like "New," "Due Soon," or "Completed." These use low-saturation background tints of the primary/secondary colors with high-saturation text.
- **Iconography:** Use a consistent 2pt stroke weight with rounded terminals. Icons should be "Open" style (non-filled) unless active, maintaining a light visual weight.
- **Progress Indicators:** Linear and circular progress bars use the "Soft Green" to reinforce the feeling of growth and achievement.