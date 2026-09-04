<?php
/**
 * Logo Wall block.
 *
 * Fields: heading, logos (gallery).
 *
 * @param array  $block      The block settings and attributes.
 * @param bool   $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'logo-wall' );

$heading = get_field( 'heading' );
$logos   = get_field( 'logos' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	echo '<div class="logo-wall"><div class="logo-wall__container container"><div class="logo-wall__grid">';
	for ( $i = 0; $i < 5; $i++ ) {
		echo '<div class="logo-wall__item" style="aspect-ratio:3/2;background:var(--color-surface);border-radius:4px"></div>';
	}
	echo '</div></div></div>';
	return;
}

if ( ! $logos && $is_preview ) {
	cn_block_placeholder( __( 'Logo Wall', 'cn-starter' ), __( 'Add logos in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="logo-wall__container container">
		<?php if ( $heading ) : ?>
			<h2 class="logo-wall__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="logo-wall__grid">
			<?php foreach ( (array) $logos as $logo ) : ?>
				<div class="logo-wall__item">
					<?php echo wp_get_attachment_image( is_array( $logo ) ? $logo['ID'] : $logo, 'medium', false, array( 'class' => 'logo-wall__logo', 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
