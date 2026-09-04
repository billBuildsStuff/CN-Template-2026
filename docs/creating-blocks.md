# Creating Blocks

## Option A: Admin UI (no CLI needed)

1. Go to **CN Starter → Create Block**.
2. Fill in: Title, Slug (kebab-case), Description, Dashicon.
3. Click **Create Block**.
4. The theme scaffolds:
   - `blocks/your-slug/block.json`
   - `blocks/your-slug/render.php`
   - `blocks/your-slug/style.css`
   - `acf-json/group_cn_your_slug.json` (starter field group with a "heading" field)
5. Go to **Custom Fields → Tools → Sync** to import the field group.
6. Edit `render.php` to reference your ACF fields and `style.css` for styling.

## Option B: WP-CLI

```bash
wp cn block create logo-wall "Logo Wall" --description="Grid of client logos" --icon=grid-view
```

Then sync the ACF field group and customize the files.

## Option C: Manual

1. Copy `blocks/_template/` to `blocks/your-slug/`.
2. In `block.json`, replace:
   - `cn-block-slug` → `your-slug`
   - `CN_BLOCK_TITLE` → `Your Block Title`
   - `CN_BLOCK_DESCRIPTION` → `Your description`
   - `CN_BLOCK_KEYWORD` → `keyword`
3. In `render.php`, replace `block-slug` with your BEM base class.
4. In `style.css`, replace `block-slug` with your BEM base class.
5. Create an ACF field group in Custom Fields, set Location to "Block is equal to Your Block Title".
6. Save — it syncs to `acf-json/` automatically.

## Block Anatomy

### block.json

```json
{
  "name": "acf/your-slug",
  "title": "Your Block Title",
  "description": "What this block does",
  "category": "cn-blocks",
  "icon": "smiley",
  "acf": { "mode": "preview", "renderTemplate": "render.php" },
  "style": "file:./style.css",
  "supports": { "align": ["wide","full"], "anchor": true, "jsx": false },
  "example": { "attributes": { "mode": "preview", "data": { "_is_example": true } } }
}
```

**Key settings:**
- `"mode": "preview"` — shows live PHP preview in the editor (not a gray box).
- `"example"` — provides sample data so the inserter shows a thumbnail.
- `"jsx": true` — enables InnerBlocks (nested editable content). Use in `render.php`: `<InnerBlocks />`.
- `"style": "file:./style.css"` — auto-enqueues the block's CSS only when used.

### render.php

```php
<?php
$attrs = cn_block_attrs( $block, 'your-slug' );
$heading = get_field( 'heading' );

if ( ! $heading && $is_preview ) {
  cn_block_placeholder( 'Your Block Title', 'Add a heading in the sidebar.' );
  return;
}
?>
<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
  <div class="your-slug__container container">
    <h2 class="your-slug__heading"><?php echo esc_html( $heading ); ?></h2>
  </div>
</section>
```

**Helper functions:**
- `cn_block_attrs( $block, 'bem-base' )` — generates `id` and `class` attributes from block settings (className, align, style variations).
- `cn_block_placeholder( 'Title', 'Hint' )` — shows a helpful placeholder in the editor when the block is empty.
- `cn_block_needs_js()` — enqueues `blocks.js` (call from blocks that use accordion/tab logic).
- `cn_asset( 'path/to/file' )` — get a theme asset URL.
- `cn_icon( 'icon-name', 'css-class' )` — output an inline SVG from `assets/images/icons/`.
- `cn_responsive_image( $image_id, 'size' )` — output a responsive image with srcset.

### style.css

BEM naming, mobile-first:

```css
.your-slug { padding-block: var(--space-xl); }
.your-slug__heading { font-size: var(--font-size-xl); }

@media (min-width: 768px) {
  .your-slug { padding-block: var(--space-2xl); }
}
```

## Using InnerBlocks

For blocks that need editable nested content (paragraphs, buttons, lists):

1. In `block.json`, set `"supports": { "jsx": true }`.
2. In `render.php`:

```php
$template = array(
  array( 'core/heading', array( 'level' => 2, 'placeholder' => 'Section heading…' ) ),
  array( 'core/paragraph', array( 'placeholder' => 'Section copy…' ) ),
);
?>
<div class="your-slug__content">
  <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>" />
</div>
```

## Style Variations

Add `"styles"` in `block.json`:

```json
"styles": [
  { "name": "default", "label": "Default", "isDefault": true },
  { "name": "centered", "label": "Centered" }
]
```

The editor adds `is-style-centered` to the block's className. `cn_block_attrs()` automatically converts this to `your-slug--centered` for BEM.

## Registering ACF Fields

Always create field groups in the WP admin (Custom Fields → Field Groups). ACF auto-saves them to `acf-json/` so they're version-controlled. Never hand-edit the JSON files directly — use the ACF UI.

If you see a "field groups need to be synced" notice, go to Custom Fields → Tools → Sync.
