<?php
/**
 * CN Starter — theme bootstrap.
 *
 * Loads every module from /inc/. Keep this file lean; add functionality
 * in a new /inc/ file instead. See /docs/getting-started.md.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

define( 'CN_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'CN_THEME_DIR', get_template_directory() );
define( 'CN_THEME_URI', get_template_directory_uri() );

$cn_modules = array(
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/helpers.php',
	'inc/blocks.php',
	'inc/acf.php',
	'inc/flexible-layouts.php',
	'inc/admin/admin-menu.php',
	'inc/admin/plugin-checker.php',
	'inc/admin/setup-wizard.php',
	'inc/admin/documentation.php',
	'inc/admin/figma-tokens.php',
	'inc/admin/design-tokens.php',
	'inc/admin/block-generator.php',
	'inc/admin/branding.php',
	'inc/integrations/gravity-forms.php',
	'inc/integrations/yoast.php',
	'patterns/patterns.php',
);

foreach ( $cn_modules as $cn_module ) {
	$cn_path = CN_THEME_DIR . '/' . $cn_module;
	if ( file_exists( $cn_path ) ) {
		require_once $cn_path;
	}
}

// WP-CLI block scaffolder: wp cn block create <slug> "<Title>".
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once CN_THEME_DIR . '/inc/cli.php';
}
