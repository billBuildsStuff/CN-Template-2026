<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and nav menus.
 */
function cn_theme_setup() {
	load_theme_textdomain( 'cn-starter', CN_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	// Editor styles (relative to theme root). Loads design tokens inside Gutenberg.
	add_editor_style( array(
		'assets/css/variables.css',
		'assets/css/base.css',
		'assets/css/utilities.css',
		'assets/css/editor.css',
	) );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'cn-starter' ),
		'footer'  => __( 'Footer Navigation', 'cn-starter' ),
	) );

	// Common agency image sizes.
	add_image_size( 'cn-hero', 1920, 900, true );
	add_image_size( 'cn-card', 600, 400, true );
	add_image_size( 'cn-square', 600, 600, true );
}
add_action( 'after_setup_theme', 'cn_theme_setup' );

/**
 * Flag first activation so the setup wizard can redirect.
 */
function cn_theme_activated() {
	if ( ! get_option( 'cn_setup_complete' ) ) {
		set_transient( 'cn_setup_wizard_redirect', 1, 60 );
	}
}
add_action( 'after_switch_theme', 'cn_theme_activated' );
