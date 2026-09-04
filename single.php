<?php
/**
 * Single post template.
 *
 * @package cn-starter
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class(); ?>>
		<header class="container container--narrow u-py-xl">
			<h1><?php the_title(); ?></h1>
			<p class="u-text-muted">
				<?php echo esc_html( get_the_date() ); ?> &middot; <?php the_author(); ?>
			</p>
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php endif; ?>
		</header>
		<div class="container container--narrow">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
