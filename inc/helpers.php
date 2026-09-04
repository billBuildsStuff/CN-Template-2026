<?php
/**
 * Template helper functions.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a theme asset URL.
 *
 * @param string $path Path relative to /assets/.
 * @return string
 */
function cn_asset( $path ) {
	return CN_THEME_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * Output an inline SVG icon from /assets/images/icons/.
 *
 * @param string $name  Icon filename without extension.
 * @param string $class Optional CSS class.
 */
function cn_icon( $name, $class = '' ) {
	$file = CN_THEME_DIR . '/assets/images/icons/' . sanitize_file_name( $name ) . '.svg';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$svg = file_get_contents( $file );
	if ( $class ) {
		$svg = preg_replace( '/<svg /', '<svg class="' . esc_attr( $class ) . '" ', $svg, 1 );
	}
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme SVG.
}

/**
 * Responsive image helper with sensible defaults.
 *
 * @param int    $image_id Attachment ID.
 * @param string $size     Registered image size.
 * @param array  $attrs    Extra attributes.
 */
function cn_responsive_image( $image_id, $size = 'large', $attrs = array() ) {
	if ( ! $image_id ) {
		return;
	}
	$defaults = array( 'loading' => 'lazy', 'decoding' => 'async' );
	echo wp_get_attachment_image( $image_id, $size, false, array_merge( $defaults, $attrs ) );
}

/**
 * Build the class + id attributes for an ACF block wrapper.
 *
 * Handles className, align, and block style variations consistently.
 *
 * @param array  $block      The ACF block array.
 * @param string $base_class BEM base class, e.g. 'hero'.
 * @return array { 'id' => string, 'class' => string }
 */
function cn_block_attrs( $block, $base_class ) {
	$classes = array( $base_class );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
		// Mirror style variations as BEM modifiers: is-style-centered -> hero--centered.
		if ( preg_match( '/is-style-([\w-]+)/', $block['className'], $m ) ) {
			$classes[] = $base_class . '--' . $m[1];
		}
	}
	if ( ! empty( $block['align'] ) ) {
		$classes[] = 'align' . $block['align'];
	}

	$id = $base_class . '-' . $block['id'];
	if ( ! empty( $block['anchor'] ) ) {
		$id = $block['anchor'];
	}

	return array(
		'id'    => esc_attr( $id ),
		'class' => esc_attr( implode( ' ', $classes ) ),
	);
}

/**
 * Render an editor-only placeholder when a block has no content yet.
 *
 * @param string $title Block title shown in the placeholder.
 * @param string $hint  Instruction for the editor.
 */
function cn_block_placeholder( $title, $hint = '' ) {
	if ( ! $hint ) {
		$hint = __( 'Fill in the block fields in the sidebar to see a preview.', 'cn-starter' );
	}
	printf(
		'<div class="cn-block-placeholder"><strong>%s</strong><span>%s</span></div>',
		esc_html( $title ),
		esc_html( $hint )
	);
}

/**
 * Enqueue the shared block interactivity script (registered in enqueue.php).
 * Call from render.php of any block that needs JS (accordion, tabs, gallery).
 */
function cn_block_needs_js() {
	wp_enqueue_script( 'cn-blocks' );
}
