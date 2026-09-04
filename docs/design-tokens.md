# Design Tokens

## What Are Design Tokens?

Design tokens are the single source of truth for your visual system: colors, typography, spacing, layout dimensions, and effects. They're defined as CSS custom properties (variables) in `assets/css/variables.css`.

## Token Categories

### Colors
```
--color-primary         Main brand color (buttons, links)
--color-primary-dark    Hover state for primary
--color-secondary       Dark surface (footer, inverse sections)
--color-accent          Highlight/CTA accent
--color-background      Page background
--color-surface         Light surface (cards, alternating sections)
--color-border          Border/divider color
--color-text            Body text
--color-text-muted      Secondary text
--color-text-inverse    Text on dark backgrounds
```

### Typography
```
--font-primary          Sans-serif font stack
--font-secondary        Serif font stack
--font-size-xs through --font-size-3xl
--font-weight-normal / medium / bold
--line-height-tight / base
```

### Spacing
```
--space-xs (0.25rem) through --space-2xl (8rem)
```

### Layout
```
--container-max (1200px)    Default content width
--container-wide (1400px)   Wide alignment width
--container-narrow (800px)  Text-focused content
--gutter (1rem)             Horizontal padding
```

### Effects
```
--radius-sm / md / lg
--shadow-sm / md / lg
--transition-fast (150ms) / base (300ms)
```

## How to Change Tokens

### Method 1: Edit variables.css directly
Best for project-specific defaults. Edit `assets/css/variables.css` and change the `:root` values.

### Method 2: Figma Token Import (recommended)
Use **CN Starter → Figma Tokens** to paste a JSON export from your designer. The importer generates CSS overrides that load after `variables.css`, so they take precedence without modifying the source file.

### Method 3: Setup Wizard
The wizard's Design Tokens step lets you set the three core brand colors quickly. For the full token set, use the Figma importer.

## Breakpoints

CSS variables can't be used inside `@media` queries, so breakpoints are documented in `variables.css` as comments:

```
sm: 640px | md: 768px | lg: 1024px | xl: 1280px
```

Always write mobile-first: base styles target mobile, then use `@media (min-width: 768px)` to scale up.

## theme.json Integration

The `theme.json` file maps block editor settings to CSS variables, so the Gutenberg color palette, font sizes, and spacing scale all reference the same tokens. When you change `--color-primary`, it updates everywhere — frontend, editor, and block settings panel.
