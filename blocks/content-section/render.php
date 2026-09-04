<?php
/**
 * Content Section block. Two columns: image + InnerBlocks content.
 *
 * Fields: image, image_position (left|right).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'content-section' );

$image    = get_field( 'image' );
$position = get_field( 'image_position' ) ?: 'left';

$inner_template = array(
	array( 'core/heading', array( 'level' => 2, 'placeholder' => __( 'Section heading…', 'cn-starter' ) ) ),
	array( 'core/paragraph', array( 'placeholder' => __( 'Section copy…', 'cn-starter' ) ) ),
);
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?> content-section--image-<?php echo esc_attr( $position ); ?>">
	<div class="content-section__container container">
		<div class="content-section__media">
			<?php if ( $image ) : ?>
				<?php echo wp_get_attachment_image( is_array( $image ) ? $image['ID'] : $image, 'large', false, array( 'class' => 'content-section__image', 'loading' => 'lazy' ) ); ?>
			<?php elseif ( $is_preview ) : ?>
				<div class="content-section__image-placeholder"><?php esc_html_e( 'Choose an image in the sidebar', 'cn-starter' ); ?></div>
			<?php endif; ?>
		</div>
		<div class="content-section__content">
			<InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_template ) ); ?>" />
		</div>
	</div>
</section>
