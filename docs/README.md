# CN Starter — Agency WordPress Theme Boilerplate

A no-build-step WordPress theme for Chernoff Newman, built around ACF Pro blocks with live editor preview, Figma design-token integration, and a guided setup wizard.

## Quick Start

1. **Activate the theme** — the Setup Wizard launches automatically.
2. **Install required plugins** — ACF Pro is required; ACF Extended, Yoast, and Gravity Forms are recommended.
3. **Sync ACF field groups** — Custom Fields → Tools → Sync (after installing ACF Pro).
4. **Import design tokens** — CN Starter → Figma Tokens (optional but recommended).
5. **Build pages** — use CN Blocks in the block editor.

## What's Included

- **12 starter blocks**: Hero, CTA, Content Section, Cards Grid, Testimonials, Team Grid, FAQ Accordion, Image Gallery, Video Embed, Tabs, Gravity Form, Spacer
- **Block generator**: CN Starter → Create Block (or `wp cn block create`)
- **Figma token importer**: CN Starter → Figma Tokens
- **Setup wizard**: CN Starter → Setup Wizard
- **In-admin documentation**: CN Starter → Theme Docs

## Key Principles

- **No build steps** — vanilla CSS + JS, PHP templates
- **BEM naming** — `.block__element--modifier`
- **Mobile-first** — base styles target mobile, `min-width` media queries scale up
- **CSS variables** — all design tokens in `assets/css/variables.css`
- **ACF JSON sync** — field groups version-controlled in `acf-json/`

## Documentation

- [Getting Started](getting-started.md)
- [Creating Blocks](creating-blocks.md)
- [Design Tokens](design-tokens.md)
- [Figma Workflow](figma-workflow.md)
- [Plugin Integrations](plugin-integrations.md)
- [Troubleshooting](troubleshooting.md)
- [AI Agent Onboarding](ai-agent-onboarding.md)
