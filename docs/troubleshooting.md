# Troubleshooting

## Blocks don't appear in the inserter

**Cause:** ACF Pro is not active or field groups aren't synced.

**Fix:**
1. Install and activate ACF Pro.
2. Go to Custom Fields → Tools → Sync.
3. Sync all field groups.
4. Clear caches (site + browser).

## Block shows "Preview not available" or gray box

**Cause:** The block's `block.json` is missing the `example` attribute, or `mode` isn't set to `preview`.

**Fix:** Ensure `block.json` includes:
```json
"acf": { "mode": "preview", "renderTemplate": "render.php" },
"example": { "attributes": { "mode": "preview", "data": { "_is_example": true } } }
```

And that `render.php` handles the example case:
```php
if ( ! empty( $block['data']['_is_example'] ) ) {
  echo '<div class="your-block"><p>Preview content</p></div>';
  return;
}
```

## "Field groups need to be synced" notice won't go away

**Cause:** The `acf-json/` files are newer than the database copies.

**Fix:**
1. Go to Custom Fields → Tools → Sync.
2. Click "Sync" on each group (or "Sync all").
3. If it persists, check file permissions on `acf-json/`.

## Figma tokens not applying

**Cause:** Invalid JSON or no recognized token groups.

**Fix:**
1. Validate your JSON at jsonlint.com.
2. Ensure top-level keys are `colors`, `typography`, and/or `spacing`.
3. Check that token names don't contain spaces (use kebab-case).

## Styles not updating after editing a block's CSS

**Cause:** Browser or plugin cache.

**Fix:**
1. Hard refresh (Cmd+Shift+R / Ctrl+Shift+R).
2. If using a caching plugin, purge the cache.
3. The theme uses `CN_THEME_VERSION` for cache-busting — bump the version in `style.css` header to force all assets to refresh.

## Setup wizard didn't launch

**Fix:** Go to CN Starter → Setup Wizard manually. The wizard can be re-run at any time.

## "Block already exists" when creating via Block Generator

**Cause:** A folder with that slug already exists in `blocks/`.

**Fix:** Choose a different slug, or delete the existing block folder if it's unused.

## InnerBlocks not rendering

**Cause:** `jsx` support not enabled in `block.json`.

**Fix:** Set `"supports": { "jsx": true }` in `block.json`. Note: InnerBlocks requires Gutenberg/ACF Pro 6.0+.

## Gravity Form block shows "Gravity Forms is not active"

**Fix:** Install and activate the Gravity Forms plugin. The block only renders when `GFAPI` class exists.

## WP-CLI block command not found

**Cause:** WP-CLI isn't loaded or the theme isn't active.

**Fix:**
1. Ensure the theme is activated.
2. Run `wp cli info` to confirm WP-CLI is available.
3. The command is: `wp cn block create <slug> "<Title>"`.
