<?php
/**
 * Testimonials block.
 *
 * Fields: heading, testimonials (repeater: quote, author, role, photo).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'testimonials' );

$heading = get_field( 'heading' );
$items   = get_field( 'testimonials' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	$items = array_fill( 0, 2, array(
		'quote'  => __( 'Working with this team was a fantastic experience from start to finish.', 'cn-starter' ),
		'author' => __( 'Jane Smith', 'cn-starter' ),
		'role'   => __( 'CEO, Example Co.', 'cn-starter' ),
		'photo'  => null,
	) );
}

if ( ! $items && $is_preview ) {
	cn_block_placeholder( __( 'Testimonials', 'cn-starter' ), __( 'Add testimonials in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="testimonials__container container">
		<?php if ( $heading ) : ?>
			<h2 class="testimonials__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="testimonials__grid">
			<?php foreach ( (array) $items as $item ) : ?>
				<figure class="testimonial">
					<blockquote class="testimonial__quote">
						<?php echo esc_html( $item['quote'] ?? '' ); ?>
					</blockquote>
					<figcaption class="testimonial__meta">
						<?php if ( ! empty( $item['photo'] ) ) : ?>
							<?php echo wp_get_attachment_image( is_array( $item['photo'] ) ? $item['photo']['ID'] : $item['photo'], 'thumbnail', false, array( 'class' => 'testimonial__photo' ) ); ?>
						<?php endif; ?>
						<span class="testimonial__author"><?php echo esc_html( $item['author'] ?? '' ); ?></span>
						<?php if ( ! empty( $item['role'] ) ) : ?>
							<span class="testimonial__role"><?php echo esc_html( $item['role'] ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
