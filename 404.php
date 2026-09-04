<?php
/**
 * 404 template.
 *
 * @package cn-starter
 */

get_header();
?>

<div class="container container--narrow u-py-xl u-text-center">
	<h1><?php esc_html_e( 'Page not found', 'cn-starter' ); ?></h1>
	<p><?php esc_html_e( "The page you're looking for doesn't exist or has moved.", 'cn-starter' ); ?></p>
	<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php esc_html_e( 'Back to homepage', 'cn-starter' ); ?>
	</a>
</div>

<?php
get_footer();
