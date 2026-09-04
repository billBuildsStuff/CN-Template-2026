<?php
/**
 * Script & style registration.
 *
 * Block-specific CSS is auto-registered per block via block.json ("style")
 * and only loads when the block is present on the page.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend styles & scripts.
 */
function cn_enqueue_assets() {
	// Design tokens first — everything depends on them.
	wp_enqueue_style( 'cn-variables', CN_THEME_URI . '/assets/css/variables.css', array(), CN_THEME_VERSION );

	// Figma-imported token overrides (saved by the token importer).
	$tokens_css = get_option( 'cn_design_tokens_css' );
	if ( $tokens_css ) {
		wp_add_inline_style( 'cn-variables', $tokens_css );
	}

	wp_enqueue_style( 'cn-base', CN_THEME_URI . '/assets/css/base.css', array( 'cn-variables' ), CN_THEME_VERSION );
	wp_enqueue_style( 'cn-utilities', CN_THEME_URI . '/assets/css/utilities.css', array( 'cn-base' ), CN_THEME_VERSION );

	wp_enqueue_script( 'cn-main', CN_THEME_URI . '/assets/js/main.js', array(), CN_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'cn_enqueue_assets' );

/**
 * Editor-only assets (Gutenberg iframe + admin).
 */
function cn_enqueue_editor_assets() {
	// Token overrides also apply inside the editor.
	$tokens_css = get_option( 'cn_design_tokens_css' );
	if ( $tokens_css ) {
		wp_register_style( 'cn-editor-tokens', false, array(), CN_THEME_VERSION );
		wp_enqueue_style( 'cn-editor-tokens' );
		wp_add_inline_style( 'cn-editor-tokens', $tokens_css );
	}
}
add_action( 'enqueue_block_editor_assets', 'cn_enqueue_editor_assets' );

/**
 * Front-end block interactivity (accordions, tabs, galleries).
 * Only enqueued when a CN block that needs it is rendered — see cn_block_needs_js().
 */
function cn_register_block_js() {
	wp_register_script( 'cn-blocks', CN_THEME_URI . '/assets/js/blocks.js', array(), CN_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'cn_register_block_js', 5 );
