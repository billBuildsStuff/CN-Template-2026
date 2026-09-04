<?php
/**
 * Main fallback template (blog index / generic archives).
 *
 * @package cn-starter
 */

get_header();
?>

<div class="container u-py-xl">
	<?php if ( have_posts() ) : ?>
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<h1><?php single_post_title(); ?></h1>
		<?php endif; ?>

		<div class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'post-list__item' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="post-list__thumb">
							<?php the_post_thumbnail( 'cn-card' ); ?>
						</a>
					<?php endif; ?>
					<h2 class="post-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="u-text-muted"><?php echo esc_html( get_the_date() ); ?></p>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No posts found.', 'cn-starter' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
