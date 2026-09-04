# AI Agent Onboarding

Copy the prompt below and paste it into your AI coding assistant (Cascade, Windsurf, Cursor, etc.) to fully onboard it to this theme's architecture. This is designed to be comprehensive enough that the agent can work productively without exploring the codebase first.

## Master Prompt

```
I'm working on a WordPress theme called CN Starter (Chernoff Newman agency boilerplate). Here is everything you need to know about the architecture.

## Location & Constants
The theme is at: wp-content/themes/cn-starter/
Key constants defined in functions.php:
- CN_THEME_DIR — absolute path to theme root (use for file_get_contents, file_exists, glob)
- CN_THEME_URI — URL to theme root (use for wp_enqueue_style/script, image URLs)
- CN_THEME_VERSION — current theme version (use in wp_enqueue_* version arg)

## Core Principles
- No build step — vanilla CSS + JS, PHP templates only. No npm, no webpack, no SCSS, no TypeScript.
- BEM CSS naming convention: .block__element--modifier
- Mobile-first responsive: base styles target mobile, min-width media queries scale up.
- All design tokens are CSS custom properties in assets/css/variables.css.
- ACF Pro blocks with live editor preview (mode: "preview" in block.json).
- ACF JSON sync: field groups are version-controlled in /acf-json/. Never hand-edit these — use the ACF admin UI.
- No jQuery dependency on frontend. Vanilla JS only.
- Tabs for indentation in PHP/CSS, spaces in JSON.

## PHP Constants & Security
- All files start with: defined( 'ABSPATH' ) || exit;
- Admin forms use: wp_nonce_field( 'cn_action_name' ) + check_admin_referer( 'cn_action_name' )
- Capability checks: current_user_can( 'manage_options' )
- Output escaping: esc_html() for text, esc_attr() for attributes, esc_url() for URLs, wp_kses_post() for trusted HTML
- DB writes: update_option() / delete_option() with sanitized values

## Directory Structure
- /blocks/ — each block in its own folder (block.json, render.php, style.css). Auto-registered by inc/blocks.php scanning the folder via glob(). Skip _template/ folder.
- /blocks/_template/ — starter template for manual block creation. Copy and rename.
- /acf-json/ — ACF field group JSON files. ACF auto-saves here via acf/settings/save_json filter. Sync via Custom Fields → Tools → Sync.
- /assets/css/ — variables.css (design tokens), base.css (reset/typography/buttons/header-footer), utilities.css (spacing/visibility helpers), editor.css (editor-only styles).
- /assets/js/ — main.js (global: mobile nav, smooth scroll), blocks.js (accordion/tabs/gallery interactivity, loaded on demand).
- /assets/images/ — theme images (cn-mark.svg white logo, cn-mark-green.svg green logo) and /icons/ folder for inline SVGs.
- /docs/ — markdown documentation auto-rendered in admin (CN Starter → Theme Docs). Each .md file appears in the sidebar.
- /inc/ — PHP modules loaded by functions.php via require_once.
- /inc/admin/ — admin pages: setup-wizard.php, figma-tokens.php, design-tokens.php, block-generator.php, documentation.php, branding.php, plugin-checker.php.
- /inc/integrations/ — third-party plugin integration: gravity-forms.php, yoast.php.
- /patterns/ — block patterns registered in patterns.php.
- /.vscode/snippets.code-snippets — VS Code snippets for rapid block scaffolding.
- /flexible/ — optional custom partials that override block render.php for flexible mode (e.g. /flexible/hero.php overrides /blocks/hero/render.php).

## Module Loading
functions.php requires these modules in order:
1. inc/setup.php — theme supports, nav menus, image sizes, first-activation flag
2. inc/enqueue.php — frontend + editor asset loading
3. inc/helpers.php — template helper functions (cn_block_attrs, cn_icon, cn_responsive_image, cn_block_placeholder, cn_block_needs_js, cn_asset)
4. inc/blocks.php — block auto-registration + block categories
5. inc/acf.php — ACF JSON sync, options page, build mode, page template filtering, reset handler, sync notices
6. inc/flexible-layouts.php — flexible content field group auto-generation + renderer + content converter
7. inc/admin/plugin-checker.php — plugin dependency notices + AJAX install
8. inc/admin/setup-wizard.php — setup wizard
9. inc/admin/documentation.php — in-admin markdown docs
10. inc/admin/figma-tokens.php — Figma token import
11. inc/admin/design-tokens.php — quick brand color editor
12. inc/admin/block-generator.php — block creation UI
13. inc/admin/branding.php — shared admin CSS (CN green buttons, logo accents)
14. inc/integrations/gravity-forms.php, yoast.php
15. patterns/patterns.php — block patterns
16. inc/cli.php — WP-CLI commands (only loaded if WP_CLI is defined)

## Block Auto-Registration (inc/blocks.php)
- cn_register_blocks() runs on init priority 5.
- Only runs if cn_get_build_mode() === 'blocks' (not in flexible mode).
- Scans /blocks/*/block.json via glob() and calls register_block_type( $dir ) on each.
- Skips /blocks/_template/.
- Block categories are filtered to add: cn-hero, cn-content, cn-cards, cn-media, cn-forms, cn-layout, plus legacy cn-blocks.

## Block Anatomy
Each block lives in /blocks/{slug}/ with:
- block.json — registration. Key fields:
  - "name": "acf/{slug}" (must start with acf/)
  - "title": "Human Title"
  - "category": "cn-content" (or cn-hero, cn-cards, cn-media, cn-forms, cn-layout)
  - "icon": "dashicon-name" (see https://developer.wordpress.org/resource/dashicons/)
  - "acf": { "mode": "preview", "renderTemplate": "render.php" }
  - "style": "file:./style.css" (auto-enqueued only when block is on page)
  - "supports": { "align": ["wide","full"], "anchor": true, "jsx": false }
  - "example": { "attributes": { "mode": "preview", "data": { "_is_example": true } } }
  - "jsx": true enables InnerBlocks for nested editable content
- render.php — template. Uses:
  - $block array (provided by ACF) — contains id, className, align, anchor
  - $is_preview boolean — true in editor
  - cn_block_attrs( $block, 'bem-base' ) — returns ['id' => string, 'class' => string] with BEM classes, align, style variations
  - get_field( 'field_name' ) — ACF field values
  - cn_block_placeholder( 'Title', 'Hint' ) — shows dashed placeholder in editor when empty
  - cn_block_needs_js() — enqueues blocks.js (call in render.php for accordion/tabs/gallery blocks)
  - cn_responsive_image( $image_id, 'size' ) — outputs wp_get_attachment_image with lazy loading
  - cn_icon( 'icon-name', 'css-class' ) — inline SVG from /assets/images/icons/
- style.css — BEM styles, mobile-first. Uses CSS variables from variables.css. Auto-enqueued only when block is rendered.

## Helper Functions (inc/helpers.php)
- cn_asset( 'path/to/file' ) — returns URL to /assets/ file
- cn_icon( $name, $class ) — outputs inline SVG from /assets/images/icons/{name}.svg
- cn_responsive_image( $image_id, $size, $attrs ) — responsive image with srcset, lazy loading
- cn_block_attrs( $block, $base_class ) — generates id + class attributes from block settings
- cn_block_placeholder( $title, $hint ) — editor placeholder for empty blocks
- cn_block_needs_js() — enqueues blocks.js for interactive blocks

## Build Modes (inc/acf.php)
cn_get_build_mode() returns 'blocks' or 'flexible' (stored in wp option 'cn_build_mode', default 'blocks').
- 'blocks' mode: registers all /blocks/ via register_block_type(). Pages use the WordPress block editor. Template: page.php.
- 'flexible' mode: does NOT register blocks. Instead registers an ACF Flexible Content field group (cn_layouts) on all pages. Template: template-flexible.php (forced via template_include filter).
- cn_filter_page_templates() removes the Flexible Layout template option in blocks mode.
- cn_force_flexible_template() forces template-flexible.php on all pages in flexible mode.

## Flexible Layouts (inc/flexible-layouts.php)
- cn_register_flexible_field_group() runs on acf/init. Only in flexible mode.
- Auto-generates layouts by scanning /blocks/ and matching each block to its ACF field group.
- Each block becomes a flexible content layout with the block's fields as sub-fields.
- cn_render_flexible_layouts() iterates rows, includes the block's render.php with acf_setup_meta() shim so get_field() works.
- Optional override: /flexible/{layout_name}.php takes precedence over /blocks/{slug}/render.php.
- Content converter: bidirectional conversion between block markup and cn_layouts meta (cn_convert_blocks_to_flexible, cn_convert_flexible_to_blocks).

## Design Tokens System
- Source of truth: assets/css/variables.css — all CSS custom properties (--color-*, --font-*, --space-*, --radius-*, --shadow-*, --transition-*).
- Figma import: CN Starter → Figma Tokens. Paste JSON with "colors", "typography", "spacing" groups. Generates :root CSS overrides stored in wp_option 'cn_design_tokens_css'. Loads after variables.css on both frontend and editor.
- Quick colors: CN Starter → Design Tokens. Sets primary/secondary/accent via color pickers. Stored in 'cn_design_tokens_quick'.
- theme.json references CSS variables for the block editor: color palette uses var(--color-*), font sizes use var(--font-size-*), spacing uses var(--space-*).
- Token JSON format: {"colors": {"primary": "#hex"}, "typography": {"font-primary": "'Font', sans-serif"}, "spacing": {"space-md": "1rem"}}
- cn_tokens_to_css( $tokens ) converts JSON to :root CSS.

## CSS Load Order
1. variables.css (design tokens — the base)
2. Figma token overrides (inline CSS from wp_options, injected via wp_add_inline_style)
3. base.css (reset, typography, buttons, forms, header/footer)
4. utilities.css (spacing, visibility, alignment helpers)
5. Block-specific style.css (only when block is on page, via register_block_type)
Editor also loads: editor.css via add_editor_style() in setup.php.

## JavaScript
- main.js — loaded on every page via wp_enqueue_script in enqueue.php. Mobile nav toggle, smooth scroll. No jQuery.
- blocks.js — registered but only enqueued when cn_block_needs_js() is called from a block's render.php. Handles accordion, tabs, gallery interactions. Vanilla JS.
- No build step, no modules, no bundler. Plain script files.

## Editor Styles
- add_editor_style() in setup.php loads variables.css, base.css, utilities.css, editor.css inside the Gutenberg iframe.
- cn_enqueue_editor_assets() in enqueue.php also injects Figma token overrides into the editor via wp_add_inline_style.
- cn_block_placeholder_styles() in blocks.php adds inline CSS for .cn-block-placeholder dashed boxes.

## Admin Pages
All admin pages are registered via add_submenu_page( 'cn-starter', ... ) (under the CN Starter top-level menu):
- Setup Wizard (cn-setup-wizard) — inc/admin/setup-wizard.php. 6 steps: Welcome/Plugins → Build Mode → Site Identity → Design Tokens → Starter Content → Done.
- Figma Tokens (cn-figma-tokens) — inc/admin/figma-tokens.php. JSON import + font discovery panel.
- Design Tokens (cn-design-tokens) — inc/admin/design-tokens.php. Quick color picker for primary/secondary/accent.
- Create Block (cn-block-generator) — inc/admin/block-generator.php. Form to scaffold new blocks.
- Theme Docs (cn-docs) — inc/admin/documentation.php. Renders /docs/*.md files with sidebar nav.
- Theme Settings (cn-theme-settings) — ACF options page registered in inc/acf.php via acf_add_options_page(). Contains Build Mode select field + reset button.
Shared admin branding: inc/admin/branding.php enqueues CN green buttons, logo accents, focus rings on all CN admin pages.

## Setup Wizard (inc/admin/setup-wizard.php)
- Steps: 1=Welcome(plugins), 2=Build Mode, 3=Site Identity, 4=Design Tokens, 5=Starter Content, 6=Done.
- cn_wizard_handle_submit() processes each step via POST.
- cn_wizard_create_starter_content() creates Home, About, Contact, Blog pages + Primary nav menu.
  - In blocks mode: pages contain block markup (<!-- wp:acf/hero ... /-->)
  - In flexible mode: pages get cn_layouts meta with layout rows
- Sets homepage to the Home page, blog page to Blog.
- cn_setup_complete option flag prevents re-redirect.
- First activation: cn_theme_activated() sets transient to redirect to wizard.

## Reset (inc/acf.php)
cn_handle_reset_setup() — triggered by ?cn_action=reset_setup with nonce.
- Deletes all CN-created pages (preserves WP core: Privacy Policy ID 2, Sample Page ID 3). Pages are trashed, not force-deleted.
- Deletes cn_layouts meta from all pages.
- Deletes Primary nav menu.
- Resets show_on_front, page_on_front, page_for_posts to defaults.
- Deletes options: cn_setup_complete, cn_build_mode, cn_design_tokens_css, cn_design_tokens_json, cn_design_tokens_quick.
- Block files in /blocks/ and ACF JSON in /acf-json/ are NOT deleted (they're theme code, not content).
- Redirects to setup wizard after reset.

## Plugin Stack (inc/admin/plugin-checker.php)
cn_required_plugins() returns:
- ACF Pro (required, premium) — class ACF + acf_register_block_type()
- ACF Extended (recommended, free on wp.org) — class ACFE
- Yoast SEO (recommended, free on wp.org) — WPSEO_VERSION
- Gravity Forms (optional, premium) — class GFAPI
- Admin Columns Pro (optional, premium) — ACP_FILE
AJAX install: cn_ajax_install_plugin() handles wp.org installs and premium ZIP URL installs.

## WP-CLI Commands (inc/cli.php)
- wp cn block create <slug> "<Title>" --description="..." --icon=dashicon
  Scaffolds: /blocks/{slug}/block.json, render.php, style.css + /acf-json/group_cn_{slug}.json
- wp cn plugins install [--acf-pro=<url>] [--gravity-forms=<url>] [--skip-free]
  Installs free plugins from wp.org, premium from ZIP URLs.

## Block Generator (inc/admin/block-generator.php)
cn_scaffold_block( $slug, $title, $desc, $icon ) — copies /blocks/_template/ to /blocks/{slug}/, replaces placeholders, creates ACF field group JSON in /acf-json/.
Slug must be kebab-case: ^[a-z0-9-]+$

## ACF Options Page (inc/acf.php)
- Registered via acf_add_options_page() at position 59 with dashicons-admin-generic icon.
- Contains: Build Mode select field (field_cn_build_mode).
- cn_sync_build_mode_option() syncs the ACF field value to wp_options 'cn_build_mode' on save.
- Additional fields can be added via ACF UI with location = Options Page = Theme Settings.

## Figma MCP Integration
For Cascade/Windsurf with Figma MCP connected:
- get_design_context( nodeId, fileKey ) — pulls design data, reference code, screenshot
- get_variable_defs( nodeId, fileKey ) — pulls Figma variable definitions
- search_design_system( fileKey, query ) — search components, variables, styles in libraries
- CN Figma file key: jupEWPXnVGrn3WBtZhfBFh (CN Website Redesign)
- Use these to extract design tokens, build blocks from Figma frames, or sync component libraries.

## Image Sizes (inc/setup.php)
- cn-hero: 1920x900 (cropped)
- cn-card: 600x400 (cropped)
- cn-square: 600x600 (cropped)
- Plus WordPress defaults: thumbnail, medium, large, full

## Nav Menus (inc/setup.php)
- 'primary' — Primary Navigation
- 'footer' — Footer Navigation

## Theme Supports (inc/setup.php)
title-tag, post-thumbnails, responsive-embeds, html5, custom-logo, align-wide, editor-styles

## Key Files Quick Reference
- functions.php — module loader (requires all /inc/ files)
- theme.json — block editor config (color palette, font sizes, spacing scale — all reference CSS variables)
- inc/setup.php — theme supports, menus, image sizes, activation flag
- inc/enqueue.php — frontend + editor asset loading
- inc/helpers.php — cn_block_attrs, cn_icon, cn_responsive_image, cn_block_placeholder, cn_block_needs_js, cn_asset
- inc/blocks.php — block auto-registration + categories
- inc/acf.php — ACF JSON sync, options page, build mode, template filtering, reset, sync notices, Google Maps key
- inc/flexible-layouts.php — flexible content field group, auto-generation, renderer, content converter
- inc/admin/setup-wizard.php — 6-step wizard
- inc/admin/figma-tokens.php — token import + font discovery
- inc/admin/design-tokens.php — quick color editor
- inc/admin/block-generator.php — block scaffolding UI
- inc/admin/documentation.php — markdown docs renderer
- inc/admin/branding.php — shared admin CSS
- inc/admin/plugin-checker.php — dependency notices + AJAX install
- inc/cli.php — WP-CLI commands
- header.php / footer.php — global template parts
- page.php — default page template (blocks mode)
- template-flexible.php — flexible content template (forced in flexible mode)
- index.php — blog listing
- single.php — single post
- 404.php — not found

## When Making Changes
- Follow existing code style: tabs for PHP/CSS, spaces in JSON.
- Don't add comments unless explicitly asked — the codebase is intentionally lean.
- Test in both build modes if the change affects content rendering.
- Use wp_kses_post() for trusted HTML, esc_html() for text, esc_attr() for attributes, esc_url() for URLs.
- Always use CN_THEME_DIR for file paths, CN_THEME_URI for URLs.
- Enqueue styles/scripts via wp_enqueue_style/wp_enqueue_script — never hardcode <link> or <script> tags.
- After PHP changes, verify no errors: check the site in browser or run wp CLI commands.
- When creating new admin pages, follow the pattern in existing files: add_submenu_page( 'cn-starter', ... ) + render function + nonce handling + capability check.
- When creating new blocks, use the block generator or _template folder. Always sync ACF field groups after.
- CSS: use existing variables from variables.css. Don't hardcode colors/spacing that already exist as tokens.
- JS: vanilla only, no jQuery. Use addEventListener, querySelector, etc.
```

## Usage

1. Copy the entire prompt above (everything between the triple backticks).
2. Paste it into a new chat with your AI assistant.
3. The assistant now has full architectural context and can work productively immediately.

## Tips

- **Keep the prompt updated** — if you add new patterns, helper functions, or architectural decisions, update this doc so the prompt stays accurate.
- **Project-specific details** — add the Figma file URL, client name, dev site URL, or any project-specific notes to the prompt before sharing with teammates.
- **For Cascade/Windsurf specifically** — the Figma MCP integration is already available. The assistant can pull design context directly from Figma files using the file URL and the MCP tools listed in the prompt.
- **For Cursor/Copilot** — the prompt gives enough context for code generation even without MCP access. The agent can still read the codebase files directly.
