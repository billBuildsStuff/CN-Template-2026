# CN Starter — Agency WordPress Theme Boilerplate

A no-build-step WordPress theme for Chernoff Newman, built around ACF Pro blocks with live editor preview, Figma design-token integration, and a guided setup wizard.

## Requirements

- WordPress 6.0+
- PHP 8.0+
- ACF Pro (required)
- ACF Extended (recommended)
- Yoast SEO (recommended)
- Gravity Forms (optional)

## Quick Start

1. Copy `cn-starter/` to `wp-content/themes/`.
2. Activate in Appearance → Themes.
3. Follow the Setup Wizard (launches automatically).
4. Install ACF Pro, then sync field groups: Custom Fields → Tools → Sync.
5. Start building pages with CN Blocks.

## Features

### Blocks
- 12 starter blocks: Hero, CTA, Content Section, Cards Grid, Testimonials, Team Grid, FAQ Accordion, Image Gallery, Video Embed, Tabs, Gravity Form, Spacer
- Live preview in the editor (not gray boxes)
- Block inserter thumbnails via example data
- InnerBlocks support for nested editable content
- Auto-enqueued block styles (only when block is used)
- BEM CSS naming, mobile-first responsive

### Developer Tools
- **Block Generator** — CN Starter → Create Block (or `wp cn block create`)
- **WP-CLI** — `wp cn block create <slug> "<Title>" --description="..." --icon=...`
- **VS Code Snippets** — `.vscode/snippets.code-snippets` with templates for `render.php`, `block.json`, BEM CSS, InnerBlocks, and repeater loops
- **Block Patterns** — pre-built page section combos in the inserter under "CN Starter"

### Design System
- CSS variables for all design tokens (`assets/css/variables.css`)
- `theme.json` maps editor settings to the same tokens
- Figma token importer (CN Starter → Figma Tokens)
- No build step — vanilla CSS + JS

### Onboarding
- Multi-step Setup Wizard (plugins check → site identity → design tokens → starter content → done)
- Plugin dependency checker with activation links
- In-admin documentation (CN Starter → Theme Docs)

### Integrations
- **ACF Pro** — block field groups, options page, JSON sync
- **ACF Extended** — enhanced field types
- **Yoast SEO** — metabox repositioned below ACF fields
- **Gravity Forms** — form selector block, theme CSS disabled for design-system styling

## Project Structure

```
cn-starter/
├── acf-json/              ACF field groups (version-controlled)
├── assets/
│   ├── css/               variables, base, utilities, editor
│   ├── js/                main.js, blocks.js
│   └── images/            icons, theme images
├── blocks/                ACF blocks (each in its own folder)
│   └── _template/         scaffold template for new blocks
├── docs/                  markdown docs (rendered in admin)
├── inc/
│   ├── admin/             setup wizard, plugin checker, figma tokens, docs, block generator
│   ├── integrations/      gravity forms, yoast
│   ├── acf.php            ACF config, options page, JSON sync
│   ├── blocks.php         auto-registration, block category
│   ├── cli.php            WP-CLI block scaffolder
│   ├── enqueue.php        script/style loading
│   ├── helpers.php        utility functions
│   └── setup.php          theme supports, menus, image sizes
├── patterns/              block patterns
├── .vscode/               snippets, settings, extension recommendations
├── functions.php          module loader
├── theme.json             block editor configuration
├── header.php / footer.php
├── page.php / single.php / index.php / 404.php
└── style.css              theme header
```

## Creating a New Block

### Via Admin UI
CN Starter → Create Block → fill in title, slug, description, icon.

### Via WP-CLI
```bash
wp cn block create logo-wall "Logo Wall" --description="Grid of client logos" --icon=grid-view
```

### Via VS Code Snippets
Type `cnblock` in a new `render.php`, `cnblockjson` in a new `block.json`, `cnblockcss` in a new `style.css`.

After scaffolding, sync the ACF field group (Custom Fields → Tools → Sync) and customize the files.

## Figma Workflow

1. Designer defines color styles, text styles, and spacing variables in Figma.
2. Export tokens as JSON (or use Figma MCP to extract automatically).
3. Paste into CN Starter → Figma Tokens → Apply.
4. All CSS variables update across frontend and editor.

See `docs/figma-workflow.md` for details.

## Documentation

Full docs available in admin (CN Starter → Theme Docs) or in `docs/`:

- [Getting Started](docs/getting-started.md)
- [Creating Blocks](docs/creating-blocks.md)
- [Design Tokens](docs/design-tokens.md)
- [Figma Workflow](docs/figma-workflow.md)
- [Plugin Integrations](docs/plugin-integrations.md)
- [Troubleshooting](docs/troubleshooting.md)

## License

Proprietary — Chernoff Newman.
