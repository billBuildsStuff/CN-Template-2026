# Getting Started

## Prerequisites

- WordPress 6.0+
- PHP 8.0+
- ACF Pro (required)
- ACF Extended (recommended)
- Yoast SEO (recommended)
- Gravity Forms (optional — needed for the Gravity Form block)

## Installation

1. Copy the `cn-starter` folder to `wp-content/themes/`.
2. Activate in Appearance → Themes.
3. The Setup Wizard redirects you automatically. If it doesn't, go to CN Starter → Setup Wizard.

## Setup Wizard Steps

1. **Welcome** — checks plugin status (required/recommended/optional). **Auto-install** free plugins (Yoast SEO, ACF Extended) directly from wordpress.org with one click. For premium plugins (ACF Pro, Gravity Forms), paste a ZIP download URL and click "Install from URL".
2. **Site Identity** — site title, tagline, logo upload.
3. **Design Tokens** — set core brand colors (pre-filled with CN Green/Black/Vert), or import from Figma later.
4. **Starter Content** — creates Home, About, Contact, Blog pages + primary menu.
5. **Done** — links to edit homepage, import Figma tokens, read docs.

## Installing Plugins via WP-CLI

```bash
# Install all free plugins from wordpress.org (Yoast SEO, ACF Extended)
wp cn plugins install

# Install premium plugins too — pass ZIP URLs
wp cn plugins install --acf-pro=https://example.com/acf-pro.zip --gravity-forms=https://example.com/gf.zip

# Skip free plugins, only install premium ones
wp cn plugins install --skip-free --acf-pro=https://example.com/acf-pro.zip
```

## After Setup

1. Go to **Custom Fields → Tools → Sync** to import the block field groups from `acf-json/`.
2. Edit the homepage (Pages → Home) and start adding CN Blocks.
3. Customize colors/typography via **CN Starter → Figma Tokens** or by editing `assets/css/variables.css`.

## Project Structure

```
cn-starter/
├── acf-json/          # ACF field groups (version-controlled)
├── assets/
│   ├── css/           # variables, base, utilities, editor styles
│   ├── js/            # main.js, blocks.js (accordion/tabs logic)
│   └── images/        # icons, theme images
├── blocks/            # ACF blocks (each in its own folder)
│   └── _template/     # copy this to create a new block
├── docs/              # markdown documentation (shown in admin)
├── inc/               # PHP modules (setup, enqueue, blocks, admin, etc.)
│   ├── admin/         # setup wizard, plugin checker, figma tokens, docs, block generator
│   └── integrations/  # gravity forms, yoast
├── patterns/          # block patterns
├── functions.php      # module loader
├── theme.json         # block editor configuration
├── header.php
├── footer.php
├── page.php
├── single.php
├── index.php
└── 404.php
```

## CSS Architecture

1. `variables.css` — design tokens (colors, typography, spacing, layout). **This is what Figma tokens override.**
2. `base.css` — reset, typography, buttons, forms, header/footer.
3. `utilities.css` — small helper classes (spacing, visibility, alignment).
4. `editor.css` — editor-only styles to match the frontend.
5. Each block has its own `style.css` — auto-enqueued only when the block is on the page.

## JavaScript

- `main.js` — mobile nav, smooth scroll. Loaded on every page.
- `blocks.js` — accordion and tab interactivity. Only loaded when a block that needs it is rendered.
- No jQuery dependency. No build step.
