<?php
/**
 * Shared admin branding — dark mode CN brand experience on all theme admin pages.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin page slugs that make up the CN branded experience.
 *
 * @return string[]
 */
function cn_admin_page_slugs() {
	return array(
		'cn-starter',
		'cn-setup-wizard',
		'cn-design-tokens',
		'cn-figma-tokens',
		'cn-block-generator',
		'cn-docs',
		'cn-theme-settings',
	);
}

/**
 * Whether the current request is a CN branded admin screen.
 *
 * Matches on the `page` query arg rather than the admin hook suffix or
 * screen id. Those differ between top-level pages, submenu pages and
 * ACF options pages (Theme Settings), which previously caused the
 * branding stylesheet to be skipped on some screens.
 *
 * @return bool
 */
function cn_is_admin_branded_screen() {
	if ( ! is_admin() ) {
		return false;
	}

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	return in_array( $page, cn_admin_page_slugs(), true );
}

/**
 * Enqueue shared admin branding styles on all CN admin pages.
 */
function cn_admin_branding_styles() {
	if ( ! cn_is_admin_branded_screen() ) {
		return;
	}

	wp_enqueue_style(
		'cn-admin-branding',
		CN_THEME_URI . '/assets/css/admin.css',
		array(),
		CN_THEME_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'cn_admin_branding_styles' );

/**
 * Add the cn-admin-dark body class on all CN admin pages.
 *
 * @param string $classes Existing admin body classes.
 * @return string
 */
function cn_admin_body_class( $classes ) {
	if ( cn_is_admin_branded_screen() ) {
		$classes .= ' cn-admin-dark cn-admin-page';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'cn_admin_body_class' );

/**
 * Render a shared branded page header for CN admin pages.
 * Call this at the top of each admin page render function,
 * right after the opening .wrap div.
 *
 * @param string $title    Page title (e.g. "Design Tokens").
 * @param string $subtitle Optional subtitle/description.
 */
function cn_admin_page_header( $title, $subtitle = '' ) {
	?>
	<div class="cn-page-header">
		<div class="cn-page-header__brand">
			<img src="<?php echo esc_url( CN_THEME_URI . '/assets/images/cn-mark.svg' ); ?>" alt="CN" class="cn-page-header__logo">
			<div>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="cn-page-header__version">v<?php echo esc_html( CN_THEME_VERSION ); ?></div>
	</div>
	<?php
}
