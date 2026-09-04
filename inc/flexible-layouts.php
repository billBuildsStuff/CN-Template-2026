<?php
/**
 * ACF Flexible Content layouts — auto-generated from block definitions.
 *
 * Single source of truth: block ACF field groups + block.json.
 * When a developer creates a new block, its flexible layout is auto-
 * generated. Block render.php files are reused for frontend rendering
 * via an acf_setup_meta() shim, so there's only one set of templates.
 *
 * Includes a bidirectional content converter:
 *   - Blocks → Flexible: parses block markup, saves as cn_layouts meta
 *   - Flexible → Blocks: reads cn_layouts meta, generates block markup
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────────────────────────────────────
// Auto-generation of flexible layouts from block field groups.
// ──────────────────────────────────────────────────────────────────────────

/**
 * Register the Flexible Content field group, auto-generating layouts
 * from the block definitions in /blocks/.
 */
function cn_register_flexible_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	if ( cn_get_build_mode() !== 'flexible' ) {
		return;
	}

	$layouts = cn_auto_generate_flexible_layouts();
	if ( empty( $layouts ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'                   => 'group_cn_flexible',
		'title'                 => __( 'Page Layouts', 'cn-starter' ),
		'fields'                => array(
			array(
				'key'           => 'field_cn_flexible_content',
				'label'         => __( 'Page Sections', 'cn-starter' ),
				'name'          => 'cn_layouts',
				'type'          => 'flexible_content',
				'instructions'  => __( 'Add sections to build your page. Drag to reorder.', 'cn-starter' ),
				'required'      => false,
				'button_label'  => __( 'Add Section', 'cn-starter' ),
				'layouts'       => $layouts,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'page',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => array( 'the_content' ),
		'active'                => true,
	) );
}
add_action( 'acf/init', 'cn_register_flexible_field_group' );

/**
 * Auto-generate flexible content layouts by scanning /blocks/ and
 * matching each block to its ACF field group.
 *
 * @return array Array of flexible_content layout arrays.
 */
function cn_auto_generate_flexible_layouts() {
	$block_dirs = glob( CN_THEME_DIR . '/blocks/*', GLOB_ONLYDIR );
	if ( ! $block_dirs ) {
		return array();
	}

	// Get all ACF field groups indexed by their block location rule.
	$block_field_groups = cn_get_block_field_groups();

	$layouts = array();
	foreach ( $block_dirs as $dir ) {
		if ( '_template' === basename( $dir ) ) {
			continue;
		}
		$json_file = $dir . '/block.json';
		if ( ! file_exists( $json_file ) ) {
			continue;
		}

		$block_json = json_decode( file_get_contents( $json_file ), true );
		$block_name = $block_json['name'] ?? '';
		if ( ! $block_name ) {
			continue;
		}

		// Find the matching field group.
		$field_group = $block_field_groups[ $block_name ] ?? null;
		$layout      = cn_build_flexible_layout_from_block( $dir, $block_json, $field_group );
		if ( $layout ) {
			$layouts[] = $layout;
		}
	}

	return $layouts;
}

/**
 * Get all ACF field groups that target blocks, indexed by block name.
 *
 * @return array [block_name => field_group_array]
 */
function cn_get_block_field_groups() {
	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		return array();
	}

	$groups = acf_get_field_groups();
	$indexed = array();
	foreach ( $groups as $group ) {
		if ( empty( $group['location'] ) ) {
			continue;
		}
		foreach ( $group['location'] as $or ) {
			foreach ( $or as $and ) {
				if ( 'block' === $and['param'] && '==' === $and['operator'] ) {
					$indexed[ $and['value'] ] = $group;
				}
			}
		}
	}
	return $indexed;
}

/**
 * Build a flexible content layout from a block's directory, block.json,
 * and its ACF field group.
 *
 * @param string       $dir         Block directory path.
 * @param array        $block_json  Parsed block.json.
 * @param array|null   $field_group ACF field group array (or null if none).
 * @return array|null  Flexible content layout array, or null on failure.
 */
function cn_build_flexible_layout_from_block( $dir, $block_json, $field_group ) {
	$block_name = $block_json['name']; // e.g. "acf/hero"
	$slug       = str_replace( 'acf/', '', $block_name ); // "hero"
	$layout_name = str_replace( '-', '_', $slug ); // "hero" or "cards_grid"
	$title       = $block_json['title'] ?? ucfirst( $slug );

	$sub_fields = array();

	// Add a style selector if block.json defines styles.
	$styles = $block_json['styles'] ?? array();
	if ( count( $styles ) > 1 ) {
		$choices = array();
		foreach ( $styles as $style ) {
			$choices[ $style['name'] ] = $style['label'] ?? ucfirst( $style['name'] );
		}
		$sub_fields[] = array(
			'key'           => 'fl_' . $layout_name . '_style',
			'label'         => __( 'Style', 'cn-starter' ),
			'name'          => 'style',
			'type'          => 'select',
			'choices'       => $choices,
			'default_value' => $styles[0]['name'] ?? 'default',
		);
	}

	// Convert the block's ACF fields to flexible sub_fields.
	if ( $field_group && function_exists( 'acf_get_fields' ) ) {
		$block_fields = acf_get_fields( $field_group['key'] );
		foreach ( $block_fields as $field ) {
			$sub_fields[] = cn_convert_field_for_flexible( $field, $layout_name );
		}
	}

	// If the block supports InnerBlocks (jsx), add a wysiwyg content field
	// unless one already exists.
	$has_jsx  = ! empty( $block_json['supports']['jsx'] );
	$has_wysiwyg = false;
	foreach ( $sub_fields as $sf ) {
		if ( 'wysiwyg' === $sf['type'] ) {
			$has_wysiwyg = true;
			break;
		}
	}
	if ( $has_jsx && ! $has_wysiwyg ) {
		$sub_fields[] = array(
			'key'   => 'fl_' . $layout_name . '_content',
			'label' => __( 'Content', 'cn-starter' ),
			'name'  => 'content',
			'type'  => 'wysiwyg',
		);
	}

	if ( empty( $sub_fields ) ) {
		return null;
	}

	return array(
		'key'        => 'layout_cn_' . $layout_name,
		'name'       => $layout_name,
		'label'      => $title,
		'display'    => 'block',
		'sub_fields' => $sub_fields,
	);
}

/**
 * Recursively convert an ACF field for use in a flexible content layout.
 * Generates a unique field key with the `fl_` prefix.
 *
 * @param array  $field       ACF field array.
 * @param string $layout_name Layout name for key generation.
 * @return array Converted field array.
 */
function cn_convert_field_for_flexible( $field, $layout_name ) {
	$new_key = 'fl_' . $layout_name . '_' . $field['name'];

	$converted = array(
		'key'   => $new_key,
		'label' => $field['label'],
		'name'  => $field['name'],
		'type'  => $field['type'],
	);

	// Preserve important field-specific settings.
	$copy_keys = array(
		'instructions', 'required', 'default_value', 'placeholder',
		'choices', 'allow_null', 'multiple', 'return_format', 'preview_size',
		'min', 'max', 'append', 'step', 'layout', 'sub_fields', 'button_label',
		'min_rows', 'max_rows', 'rows', 'media_upload', 'wrapper',
	);
	foreach ( $copy_keys as $ck ) {
		if ( isset( $field[ $ck ] ) ) {
			$converted[ $ck ] = $field[ $ck ];
		}
	}

	// Recursively convert repeater sub_fields with unique keys.
	if ( 'repeater' === $field['type'] && ! empty( $converted['sub_fields'] ) ) {
		$converted['sub_fields'] = array();
		foreach ( $field['sub_fields'] as $sub ) {
			$converted['sub_fields'][] = cn_convert_field_for_flexible( $sub, $layout_name );
		}
	}

	return $converted;
}

// ──────────────────────────────────────────────────────────────────────────
// Renderer — reuses block render.php via acf_setup_meta() shim.
// ──────────────────────────────────────────────────────────────────────────

/**
 * Render all flexible content layouts for the current post.
 * Call this from template-flexible.php.
 *
 * For each layout row, the block's render.php is included with an
 * acf_setup_meta() shim so get_field() calls work. If a custom
 * /flexible/{layout}.php partial exists, it takes precedence.
 */
function cn_render_flexible_layouts() {
	if ( ! have_rows( 'cn_layouts' ) ) {
		return;
	}

	// Define a no-op InnerBlocks for frontend rendering (in case it's called as a function).
	if ( ! function_exists( 'InnerBlocks' ) ) {
		function InnerBlocks( $attrs = array() ) {
			echo '<!-- cn-flex-innerblocks -->';
		}
	}

	$index = 0;
	while ( have_rows( 'cn_layouts' ) ) :
		the_row();

		$layout_name  = get_row_layout();
		$block_slug   = str_replace( '_', '-', $layout_name );
		$partial_file = CN_THEME_DIR . '/flexible/' . $layout_name . '.php';
		$render_file  = CN_THEME_DIR . '/blocks/' . $block_slug . '/render.php';

		$GLOBALS['cn_flex_index'] = $index;

		if ( file_exists( $partial_file ) ) {
			// Optional: custom flexible partial overrides block render.php.
			include $partial_file;
		} elseif ( file_exists( $render_file ) ) {
			cn_render_block_as_flexible( $render_file, $layout_name, $block_slug, $index );
		}

		$index++;
	endwhile;
}

/**
 * Render a block's render.php in the flexible content context.
 *
 * Sets up ACF meta so get_field() calls in the block template find
 * the current row's sub-field data. Constructs a fake $block array
 * for cn_block_attrs(). Replaces InnerBlocks placeholders with
 * wysiwyg content for blocks that use jsx.
 *
 * @param string $render_file Path to the block's render.php.
 * @param string $layout_name Layout name (underscores).
 * @param string $block_slug  Block slug (hyphens).
 * @param int    $index       Row index for unique IDs.
 */
function cn_render_block_as_flexible( $render_file, $layout_name, $block_slug, $index ) {
	$post_id = get_the_ID();
	$row_data = get_row();

	// Get wysiwyg content for InnerBlocks replacement BEFORE acf_setup_meta(),
	// since get_sub_field() relies on the have_rows loop context.
	$inner_content = get_sub_field( 'content' ) ?: get_sub_field( 'body' );

	// Convert field keys to field names in the row data, and include
	// field key references (_name => key) so get_field() can resolve names.
	$meta = array();
	foreach ( $row_data as $key => $value ) {
		if ( str_starts_with( $key, 'fl_' ) && function_exists( 'acf_get_field' ) ) {
			$field = acf_get_field( $key );
			if ( $field && isset( $field['name'] ) ) {
				$meta[ $field['name'] ] = $value;
				$meta[ '_' . $field['name'] ] = $field['key'];
				continue;
			}
		}
		$meta[ $key ] = $value;
	}

	// Use a unique block ID as the meta post_id (like ACF does for blocks).
	// This makes get_field() resolve to our meta, not the have_rows loop context.
	$block_id = 'flex_' . $post_id . '_' . $index;

	// Set up ACF meta context so get_field() finds the row's data.
	if ( $meta && function_exists( 'acf_setup_meta' ) ) {
		acf_setup_meta( $meta, $block_id, true );
	}

	// Build a fake $block array for cn_block_attrs().
	$block = cn_build_fake_block( $block_slug, $layout_name, $index );
	$is_preview = false;

	// Capture the block render output.
	ob_start();
	include $render_file;
	$html = ob_get_clean();

	// Reset the ACF meta context.
	if ( $meta && function_exists( 'acf_reset_meta' ) ) {
		acf_reset_meta( $block_id );
	}

	// Replace InnerBlocks placeholder with wysiwyg content (for jsx blocks).
	// ACF block templates use <InnerBlocks .../> as an HTML tag, which PHP
	// outputs verbatim. Replace both the tag and our comment placeholder.
	if ( $inner_content ) {
		$replacement = wp_kses_post( wpautop( $inner_content ) );
		$html = preg_replace( '/<InnerBlocks[^>]*\/>/', $replacement, $html );
		$html = str_replace( '<!-- cn-flex-innerblocks -->', $replacement, $html );
	}

	echo $html;
}

/**
 * Build a fake $block array compatible with cn_block_attrs().
 *
 * @param string $block_slug  Block slug (hyphens).
 * @param string $layout_name Layout name (underscores).
 * @param int    $index       Row index.
 * @return array Block array for cn_block_attrs().
 */
function cn_build_fake_block( $block_slug, $layout_name, $index ) {
	$json_file = CN_THEME_DIR . '/blocks/' . $block_slug . '/block.json';
	$block_json = file_exists( $json_file ) ? json_decode( file_get_contents( $json_file ), true ) : array();

	$style = get_sub_field( 'style' ) ?: 'default';
	$class_name = '';
	if ( $style && 'default' !== $style ) {
		$class_name = 'is-style-' . $style;
	}

	return array(
		'id'        => $block_slug . '-flex-' . get_the_ID() . '-' . $index,
		'name'      => 'acf/' . $block_slug,
		'title'     => $block_json['title'] ?? $layout_name,
		'className' => $class_name,
		'align'     => '',
		'anchor'    => '',
		'data'      => array(),
	);
}

/**
 * Get a unique ID for the current flexible layout row.
 *
 * @param string $base Base string for the ID.
 * @return string Escaped HTML ID.
 */
function cn_flex_id( $base = '' ) {
	$index   = isset( $GLOBALS['cn_flex_index'] ) ? $GLOBALS['cn_flex_index'] : 0;
	$post_id = get_the_ID();
	return esc_attr( $base . '-flex-' . $post_id . '-' . $index );
}

/**
 * Enqueue block CSS when rendering flexible layouts on the frontend.
 * The flexible partials reuse the same CSS classes as blocks, so we
 * need the block styles loaded even though no blocks are in the content.
 */
function cn_flexible_enqueue_styles() {
	if ( ! is_page() ) {
		return;
	}

	// In flexible mode, all pages use the flexible template (forced via template_include).
	// is_page_template() checks stored meta which may not reflect the forced template.
	if ( cn_get_build_mode() !== 'flexible' && ! is_page_template( 'template-flexible.php' ) ) {
		return;
	}

	$block_dirs = glob( CN_THEME_DIR . '/blocks/*', GLOB_ONLYDIR );
	if ( ! $block_dirs ) {
		return;
	}

	foreach ( $block_dirs as $dir ) {
		$json_file = $dir . '/block.json';
		if ( ! file_exists( $json_file ) ) {
			continue;
		}
		$json = json_decode( file_get_contents( $json_file ), true );
		if ( empty( $json['style'] ) ) {
			continue;
		}

		// $json['style'] is a path relative to the block dir, e.g. file:./style.css
		$style_rel = preg_replace( '/^file:\.\//', '', $json['style'] );
		$style_path = $dir . '/' . $style_rel;
		$style_uri  = CN_THEME_URI . '/blocks/' . basename( $dir ) . '/' . $style_rel;

		if ( file_exists( $style_path ) ) {
			$handle = 'cn-block-' . basename( $dir );
			wp_enqueue_style( $handle, $style_uri, array( 'cn-base' ), CN_THEME_VERSION );
		}
	}

	// Also enqueue the interactivity script.
	wp_enqueue_script( 'cn-blocks' );
}
add_action( 'wp_enqueue_scripts', 'cn_flexible_enqueue_styles', 20 );

// ──────────────────────────────────────────────────────────────────────────
// Bidirectional content converter.
// ──────────────────────────────────────────────────────────────────────────

/**
 * Convert a page's block markup (post_content) to flexible content meta.
 *
 * Parses <!-- wp:acf/{block-name} ...--> blocks, extracts field data,
 * and saves it as cn_layouts post meta.
 *
 * @param int $post_id Page ID to convert.
 * @return int Number of layouts created.
 */
function cn_convert_blocks_to_flexible( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( ! $content ) {
		return 0;
	}

	// Parse block comments: <!-- wp:acf/{slug} {json} /-->
	$pattern = '/<!--\s+wp:acf\/([\w-]+)\s+({.*?})\s+\/-->/s';
	if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
		return 0;
	}

	$layouts = array();
	$all_attrs = array();
	foreach ( $matches as $match ) {
		$block_slug  = $match[1]; // e.g. "hero", "cards-grid"
		$layout_name = str_replace( '-', '_', $block_slug ); // "hero", "cards_grid"
		$attrs_json  = $match[2] ?? '{}';
		$attrs       = json_decode( $attrs_json, true );
		if ( ! is_array( $attrs ) ) {
			$attrs = array();
		}

		// Extract field data from the block's data attribute.
		$data = $attrs['data'] ?? array();

		// Build the layout row. Remove the field key references (keys starting with _).
		$row = array( 'acf_fc_layout' => $layout_name );
		foreach ( $data as $key => $value ) {
			if ( str_starts_with( $key, '_' ) ) {
				continue;
			}
			$row[ $key ] = $value;
		}

		$layouts[]     = $row;
		$all_attrs[]   = $attrs;
	}

	if ( empty( $layouts ) ) {
		return 0;
	}

	// Save the ACF field key reference.
	update_post_meta( $post_id, '_cn_layouts', 'field_cn_flexible_content' );

	// Save each row's field data as individual meta entries.
	foreach ( $layouts as $i => $row ) {
		$layout_name = $row['acf_fc_layout'];
		unset( $row['acf_fc_layout' ] );

		// Add style from block className if present.
		$block_class = $all_attrs[ $i ]['className'] ?? '';
		if ( preg_match( '/is-style-([\w-]+)/', $block_class, $m ) ) {
			$row['style'] = $m[1];
		}

		foreach ( $row as $field_name => $value ) {
			$meta_key   = 'cn_layouts_' . $i . '_' . $field_name;
			$field_key  = 'fl_' . $layout_name . '_' . $field_name;
			update_post_meta( $post_id, $meta_key, $value );
			update_post_meta( $post_id, '_' . $meta_key, $field_key );
		}
	}

	// Store layout order.
	$layout_order = array();
	foreach ( $layouts as $row ) {
		$layout_order[] = $row['acf_fc_layout'];
	}
	update_post_meta( $post_id, 'cn_layouts', $layout_order );

	return count( $layouts );
}

/**
 * Convert a page's flexible content meta (cn_layouts) to block markup.
 *
 * Reads cn_layouts meta, generates <!-- wp:acf/{block-name} --> block
 * comments, and saves as post_content.
 *
 * @param int $post_id Page ID to convert.
 * @return int Number of blocks created.
 */
function cn_convert_flexible_to_blocks( $post_id ) {
	// Use raw meta instead of get_field() because the flexible field group
	// is only registered in flexible mode — get_field would return false in blocks mode.
	$layout_order = get_post_meta( $post_id, 'cn_layouts', true );
	if ( empty( $layout_order ) || ! is_array( $layout_order ) ) {
		return 0;
	}

	$content = '';
	$count   = 0;

	foreach ( $layout_order as $i => $layout_name ) {
		$block_slug = str_replace( '_', '-', $layout_name );

		// Read all field values for this row from post meta.
		$prefix    = 'cn_layouts_' . $i . '_';
		$all_meta  = get_post_meta( $post_id );
		$data      = array();
		$style_val = '';

		foreach ( $all_meta as $meta_key => $meta_vals ) {
			if ( strpos( $meta_key, $prefix ) !== 0 ) {
				continue;
			}
			$field_name = substr( $meta_key, strlen( $prefix ) );
			if ( 'style' === $field_name ) {
				$style_val = $meta_vals[0];
			} else {
				$data[ $field_name ] = maybe_unserialize( $meta_vals[0] );
			}
		}

		// Build className from style.
		$className = '';
		if ( $style_val && 'default' !== $style_val ) {
			$className = 'is-style-' . $style_val;
		}

		$attrs = array(
			'name'  => 'acf/' . $block_slug,
			'mode'  => 'preview',
			'data'  => $data,
		);
		if ( $className ) {
			$attrs['className'] = $className;
		}

		$content .= '<!-- wp:acf/' . $block_slug . ' ' . wp_json_encode( $attrs ) . ' /-->' . "\n";
		$count++;
	}

	if ( $count > 0 ) {
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $content,
		) );
	}

	return $count;
}

/**
 * Convert all pages from blocks to flexible content.
 *
 * @return array ['converted' => int, 'skipped' => int]
 */
function cn_convert_all_pages_to_flexible() {
	$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'post_status' => 'any' ) );
	$converted = 0;
	$skipped   = 0;

	foreach ( $pages as $page ) {
		// Skip pages that already have flexible content.
		$existing = get_post_meta( $page->ID, 'cn_layouts', true );
		if ( $existing ) {
			$skipped++;
			continue;
		}
		// Only convert pages with block markup.
		if ( strpos( $page->post_content, 'wp:acf/' ) === false ) {
			$skipped++;
			continue;
		}
		$result = cn_convert_blocks_to_flexible( $page->ID );
		if ( $result > 0 ) {
			$converted++;
		} else {
			$skipped++;
		}
	}

	return array( 'converted' => $converted, 'skipped' => $skipped );
}

/**
 * Convert all pages from flexible content to blocks.
 *
 * @return array ['converted' => int, 'skipped' => int]
 */
function cn_convert_all_pages_to_blocks() {
	$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'post_status' => 'any' ) );
	$converted = 0;
	$skipped   = 0;

	foreach ( $pages as $page ) {
		// Use raw meta since the field group isn't registered in blocks mode.
		$layout_order = get_post_meta( $page->ID, 'cn_layouts', true );
		if ( empty( $layout_order ) || ! is_array( $layout_order ) ) {
			$skipped++;
			continue;
		}
		$result = cn_convert_flexible_to_blocks( $page->ID );
		if ( $result > 0 ) {
			$converted++;
		} else {
			$skipped++;
		}
	}

	return array( 'converted' => $converted, 'skipped' => $skipped );
}

/**
 * Handle the "Convert Content" admin action.
 */
function cn_handle_convert_content() {
	if ( ! isset( $_GET['cn_action'] ) || 'convert_content' !== $_GET['cn_action'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_convert_content' );

	$direction = $_GET['direction'] ?? 'to_flexible';
	$target    = 'to_flexible' === $direction ? 'flexible' : 'blocks';

	if ( 'flexible' === $target ) {
		$result = cn_convert_all_pages_to_flexible();
		set_transient( 'cn_convert_result', sprintf(
			'Converted %d pages to flexible content. %d pages skipped.',
			$result['converted'],
			$result['skipped']
		), 30 );
	} else {
		$result = cn_convert_all_pages_to_blocks();
		set_transient( 'cn_convert_result', sprintf(
			'Converted %d pages to block content. %d pages skipped.',
			$result['converted'],
			$result['skipped']
		), 30 );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=cn-theme-settings' ) );
	exit;
}
add_action( 'admin_init', 'cn_handle_convert_content' );

/**
 * Show a "Convert Content" panel on the Theme Settings page
 * with a button to convert all pages between build modes.
 */
function cn_convert_content_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'admin_page_cn-theme-settings' !== $screen->id ) {
		return;
	}

	// Show conversion result if available.
	$result = get_transient( 'cn_convert_result' );
	if ( $result ) {
		delete_transient( 'cn_convert_result' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>CN Starter:</strong> ' . esc_html( $result ) . '</p></div>';
	}

	$mode = cn_get_build_mode();

	if ( 'flexible' === $mode ) {
		$convert_url = wp_nonce_url( admin_url( '?cn_action=convert_content&direction=to_flexible' ), 'cn_convert_content' );
		?>
		<div class="cn-settings-panel">
			<div class="cn-settings-panel__header">
				<h2><?php esc_html_e( 'Content Conversion', 'cn-starter' ); ?></h2>
			</div>
			<p class="cn-settings-panel__desc"><?php esc_html_e( 'Convert existing block-based pages to flexible content layouts. This preserves all field data and creates matching flexible layout rows.', 'cn-starter' ); ?></p>
			<div class="cn-settings-panel__actions">
				<a href="<?php echo esc_url( $convert_url ); ?>" class="button button-secondary">
					<span class="dashicons dashicons-migrate"></span>
					<?php esc_html_e( 'Convert Pages to Flexible', 'cn-starter' ); ?>
				</a>
			</div>
		</div>
		<?php
	} else {
		$convert_url = wp_nonce_url( admin_url( '?cn_action=convert_content&direction=to_blocks' ), 'cn_convert_content' );
		?>
		<div class="cn-settings-panel">
			<div class="cn-settings-panel__header">
				<h2><?php esc_html_e( 'Content Conversion', 'cn-starter' ); ?></h2>
			</div>
			<p class="cn-settings-panel__desc"><?php esc_html_e( 'Convert existing flexible content pages to block markup. This preserves all field data and generates block comments in post_content.', 'cn-starter' ); ?></p>
			<div class="cn-settings-panel__actions">
				<a href="<?php echo esc_url( $convert_url ); ?>" class="button button-secondary">
					<span class="dashicons dashicons-migrate"></span>
					<?php esc_html_e( 'Convert Pages to Blocks', 'cn-starter' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'cn_convert_content_notice' );

/**
 * Admin notice on pages using the flexible template when ACF Pro is missing.
 */
function cn_flexible_acf_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		echo '<div class="notice notice-error"><p><strong>CN Starter:</strong> The Flexible Layout template requires ACF Pro to be active. Please activate ACF Pro to use this template.</p></div>';
	}
}
add_action( 'admin_notices', 'cn_flexible_acf_notice' );
