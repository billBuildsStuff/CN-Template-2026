<?php
/**
 * ACF block registration.
 *
 * Every folder in /blocks/ (except _template) containing a block.json is
 * auto-registered. To create a block: copy /blocks/_template/, rename, done.
 * Or run: wp cn block create <slug> "<Title>"
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all theme blocks.
 */
function cn_register_blocks() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return; // ACF Pro not active — plugin checker will notify.
	}

	if ( cn_get_build_mode() !== 'blocks' ) {
		return; // Flexible Layouts mode — don't register blocks.
	}

	$dirs = glob( CN_THEME_DIR . '/blocks/*', GLOB_ONLYDIR );
	if ( ! $dirs ) {
		return;
	}

	foreach ( $dirs as $dir ) {
		if ( '_template' === basename( $dir ) ) {
			continue;
		}
		if ( file_exists( $dir . '/block.json' ) ) {
			register_block_type( $dir );
		}
	}
}
add_action( 'init', 'cn_register_blocks', 5 );

/**
 * "CN Blocks" category, pinned to the top of the inserter.
 *
 * @param array $categories Existing categories.
 * @return array
 */
function cn_block_category( $categories ) {
	$cn_cats = array(
		array(
			'slug'  => 'cn-hero',
			'title' => __( 'CN Hero', 'cn-starter' ),
			'icon'  => 'cover-image',
		),
		array(
			'slug'  => 'cn-content',
			'title' => __( 'CN Content', 'cn-starter' ),
			'icon'  => 'edit-page',
		),
		array(
			'slug'  => 'cn-cards',
			'title' => __( 'CN Cards & Grids', 'cn-starter' ),
			'icon'  => 'grid-view',
		),
		array(
			'slug'  => 'cn-media',
			'title' => __( 'CN Media', 'cn-starter' ),
			'icon'  => 'format-gallery',
		),
		array(
			'slug'  => 'cn-forms',
			'title' => __( 'CN Forms', 'cn-starter' ),
			'icon'  => 'forms',
		),
		array(
			'slug'  => 'cn-layout',
			'title' => __( 'CN Layout', 'cn-starter' ),
			'icon'  => 'align-full-width',
		),
	);

	// Keep old cn-blocks slug for backward compat.
	$cn_cats[] = array(
		'slug'  => 'cn-blocks',
		'title' => __( 'CN Blocks (Legacy)', 'cn-starter' ),
		'icon'  => 'star-filled',
	);

	return array_merge( $cn_cats, $categories );
}
add_filter( 'block_categories_all', 'cn_block_category' );

/**
 * Editor placeholder styling for empty blocks.
 */
function cn_block_placeholder_styles() {
	$css = '.cn-block-placeholder{display:flex;flex-direction:column;gap:4px;padding:24px;border:2px dashed #ccc;border-radius:4px;background:#f9f9f9;color:#555;font-family:sans-serif;text-align:center}.cn-block-placeholder strong{font-size:14px}.cn-block-placeholder span{font-size:12px}';
	wp_register_style( 'cn-block-placeholder', false, array(), CN_THEME_VERSION );
	wp_enqueue_style( 'cn-block-placeholder' );
	wp_add_inline_style( 'cn-block-placeholder', $css );
}
add_action( 'enqueue_block_editor_assets', 'cn_block_placeholder_styles' );
