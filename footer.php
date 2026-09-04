<?php
/**
 * Site footer.
 *
 * @package cn-starter
 */
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="site-footer__nav">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'container'      => false,
				'fallback_cb'    => false,
				'depth'          => 1,
			) );
			?>
		</div>
		<p class="site-footer__copyright u-text-muted">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
