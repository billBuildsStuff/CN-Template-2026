<?php
/**
 * Image Gallery block.
 *
 * Fields: images (gallery), columns (2|3|4).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'gallery' );

$images  = get_field( 'images' );
$columns = get_field( 'columns' ) ?: '3';

if ( ! empty( $block['data']['_is_example'] ) ) {
	echo '<div class="gallery"><div class="gallery__grid gallery__grid--cols-3">';
	for ( $i = 0; $i < 3; $i++ ) {
		echo '<div class="gallery__item" style="aspect-ratio:4/3;background:var(--color-surface)"></div>';
	}
	echo '</div></div>';
	return;
}

if ( ! $images && $is_preview ) {
	cn_block_placeholder( __( 'Image Gallery', 'cn-starter' ), __( 'Add images in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="gallery__container container">
		<div class="gallery__grid gallery__grid--cols-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( (array) $images as $image ) : ?>
				<?php
				$image_id  = is_array( $image ) ? $image['ID'] : $image;
				$full_url  = wp_get_attachment_image_url( $image_id, 'full' );
				?>
				<a class="gallery__item" href="<?php echo esc_url( $full_url ); ?>" target="_blank" rel="noopener">
					<?php echo wp_get_attachment_image( $image_id, 'cn-card', false, array( 'loading' => 'lazy' ) ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
