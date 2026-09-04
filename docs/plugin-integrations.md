# Plugin Integrations

## ACF Pro (Required)

The entire block system depends on ACF Pro. Without it, no CN Blocks will register.

**What it powers:**
- All 12 starter blocks (field groups in `acf-json/`)
- Theme Settings options page (CN Starter → Theme Settings)
- Block field groups (auto-synced from `acf-json/`)

**Setup after activating:**
1. Go to Custom Fields → Tools → Sync.
2. Sync all field groups from the theme's `acf-json/` folder.

**ACF JSON sync:**
All field groups are stored in `acf-json/` and version-controlled. When you create or edit a field group in the WP admin, ACF saves it to this folder automatically. An admin notice appears if the database is out of sync with the files.

## ACF Extended (Recommended)

Enhances ACF with additional field types and admin features.

**Benefits:**
- More field type options (e.g., block preview enhancements)
- Better admin UI for managing field groups
- Additional dynamic field types

No special configuration needed — it enhances ACF automatically.

## Yoast SEO (Recommended)

**What the theme does:**
- Moves the Yoast metabox below ACF fields for a cleaner edit screen
- All theme templates are SEO-ready (semantic HTML, proper heading hierarchy)

**Configuration:**
- Configure in SEO → General Settings after activation
- No theme-specific setup needed

## Gravity Forms (Optional)

**What the theme does:**
- Provides a Gravity Form block (CN Starter → Create Block if you need a custom form layout)
- Auto-populates the form selector in the block's ACF fields
- Disables Gravity Forms' built-in CSS theme so the form inherits the theme's design system styles
- Form elements (inputs, buttons) styled in `base.css` and `blocks/gravity-form/style.css`

**Setup:**
1. Install and activate Gravity Forms.
2. Create at least one form.
3. Add the "Gravity Form" block to a page and select your form.

## Admin Columns Pro (Optional)

No theme integration needed — it works independently to customize admin list table columns.

## Google Maps (Optional)

If you use ACF's Google Map field in a custom block, define the API key in `wp-config.php`:

```php
define( 'CN_GOOGLE_MAPS_KEY', 'your-api-key-here' );
```

The theme passes this to ACF automatically via the `acf/fields/google_map/api` filter.
