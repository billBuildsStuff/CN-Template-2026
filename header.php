<?php
/**
 * Site header.
 *
 * @package cn-starter
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'cn-starter' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<div class="site-header__logo">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a href="%s" rel="home"><img src="%s" alt="%s" class="site-header__logo-mark"></a>',
					esc_url( home_url( '/' ) ),
					esc_url( CN_THEME_URI . '/assets/images/cn-mark-green.svg' ),
					esc_attr( get_bloginfo( 'name' ) )
				);
			}
			?>
		</div>

		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'cn-starter' ); ?>">
			<button class="site-nav__toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'cn-starter' ); ?>">
				<span></span><span></span><span></span>
			</button>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_class'     => 'site-nav__list',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
