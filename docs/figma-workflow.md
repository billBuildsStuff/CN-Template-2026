# Figma Workflow

## Designer → Developer Handoff

This theme integrates with Figma to streamline design-to-code work. The primary integration points are **design token export** and **Figma MCP** (automated design context extraction).

## Step 1: Set Up Tokens in Figma

Before exporting, the designer needs to define styles and variables in the Figma file. Here is exactly how:

### Color Styles

1. Open the Figma file.
2. Click the **Local styles** panel (right sidebar → Design tab).
3. Click the **+** next to **Color** → name it semantically (e.g. `primary`, `secondary`, `accent`, `background`, `surface`, `text`, `text-muted`).
4. Set the hex value for each.
5. **Required names** that map to theme CSS variables: `primary`, `secondary`, `accent`, `background`, `surface`, `text`, `text-muted`, `border`, `text-inverse`.
6. **Optional**: `primary-dark`, `primary-light`, `surface-dark`, `success`, `error`.

### Typography Styles

1. In the same **Local styles** panel, click **+** next to **Text**.
2. Create text styles named to match theme variables: `font-primary` (sans-serif), `font-secondary` (serif).
3. The font family value should be the full CSS stack, e.g. `'Gotham', system-ui, sans-serif`.
4. Also create size styles: `font-size-xs` through `font-size-4xl` with rem values.
5. Font weight styles: `font-weight-light` (300), `font-weight-normal` (400), `font-weight-medium` (500), `font-weight-bold` (700).

### Spacing Variables

1. Go to **Local variables** (right sidebar → Design tab → Variables icon at bottom).
2. Click **+** → create a collection called `spacing`.
3. Add variables: `space-xs` (0.25rem), `space-sm` (0.5rem), `space-md` (1rem), `space-lg` (2rem), `space-xl` (4rem), `space-2xl` (8rem).
4. Set each as a **Number** variable with the rem value.

### Layout Variables (optional)

Add to the same variables collection: `container-max` (1200), `container-wide` (1400), `container-narrow` (800), `gutter` (1).

## Step 2: Export Tokens

### Method A: Figma MCP (automated, recommended)

If you have Cascade, Windsurf, or another MCP-capable AI tool connected to Figma:

1. Open your AI tool and ensure the Figma MCP server is connected.
2. Paste the Figma file URL (or a specific frame/node URL).
3. Ask the AI: "Extract design tokens from this Figma file and generate a token JSON for the CN Starter theme."
4. The AI uses `get_design_context` and `get_variable_defs` to pull colors, fonts, and spacing.
5. Copy the generated JSON output.

### Method B: Figma Tokens Plugin (manual)

1. Install the **Tokens Studio for Figma** plugin (free, from Figma Community).
2. Open the plugin: Plugins → Tokens Studio.
3. Go to **Export** → copy the JSON output.
4. Restructure into the theme's expected format (see below) if needed.

### Method C: Hand-write the JSON

If the token set is small, just write it manually. Use this template:

```json
{
  "colors": {
    "primary": "#059973",
    "primary-dark": "#047d5e",
    "primary-light": "#2aa072",
    "secondary": "#000000",
    "accent": "#2aa072",
    "background": "#ffffff",
    "surface": "#f5f5f5",
    "surface-dark": "#383838",
    "border": "#b4b4b4",
    "text": "#000000",
    "text-muted": "#707070",
    "text-inverse": "#ffffff"
  },
  "typography": {
    "font-primary": "'Gotham', system-ui, -apple-system, sans-serif",
    "font-secondary": "'Calluna', Georgia, serif",
    "font-size-xs": "0.875rem",
    "font-size-sm": "1rem",
    "font-size-base": "1.125rem",
    "font-size-lg": "1.25rem",
    "font-size-xl": "1.875rem",
    "font-size-2xl": "2.25rem",
    "font-size-3xl": "3rem",
    "font-size-4xl": "3.75rem",
    "font-weight-normal": "400",
    "font-weight-medium": "500",
    "font-weight-bold": "700"
  },
  "spacing": {
    "space-xs": "0.25rem",
    "space-sm": "0.5rem",
    "space-md": "1rem",
    "space-lg": "2rem",
    "space-xl": "4rem",
    "space-2xl": "8rem"
  }
}
```

## Step 3: Import in WordPress

1. Go to **CN Starter → Figma Tokens**.
2. Paste the JSON into the textarea.
3. Click **Apply Tokens**.
4. The theme generates CSS `:root` overrides and saves them as a database option.
5. Overrides load after `variables.css` on both frontend and editor — no file edits needed.
6. The **Fonts in Use** panel on the right shows all fonts detected from the import, with sourcing guidance (Adobe Fonts, Google Fonts, self-hosted).

## Step 4: Source Fonts

After importing tokens, the **Fonts in Use** sidebar on the Figma Tokens page shows each font with its likely source:

- **Adobe Fonts** (Gotham, Calluna, Proxima Nova): Create a Web Project at fonts.adobe.com, add the embed code to `header.php` before `</head>`.
- **Google Fonts** (Inter, Roboto, etc.): Self-host via `@font-face` or use Google Fonts CDN link in `header.php`.
- **System fonts**: No action needed — they're in the fallback stack.

## What Gets Updated

- All CSS variables (`--color-*`, `--font-*`, `--space-*`)
- Block editor color palette (via `theme.json` referencing the same variables)
- Typography presets in the editor
- Spacing scale in the editor

## Resetting

Go to CN Starter → Figma Tokens → **Reset to Theme Defaults** to remove all overrides and restore the original `variables.css` values.

## Full Token Reference

See `assets/css/variables.css` for the complete list of token names the theme recognizes. Token names in the JSON must match (without the `--` prefix). Unknown names are ignored silently.

## Tips for Designers

- **Name tokens semantically**, not by value (`primary` not `blue-500`).
- **Keep the token set small** — 7-12 colors, 2 fonts, 6 spacing values.
- **Use Figma Variables** (not just styles) for spacing — the MCP can extract them.
- **Test in Figma first** — the token names must match what the theme expects.
- **Font stacks must be valid CSS** — wrap font names with spaces in quotes: `'Gotham', sans-serif`.
