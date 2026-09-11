<?php
/**
 * Call to Action block. Uses InnerBlocks for the body copy so editors
 * can add paragraphs, lists, etc. between the heading and button.
 *
 * Fields: heading, cta_text, cta_link.
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'cta' );

$heading  = get_field( 'heading' );
$cta_text = get_field( 'cta_text' );
$cta_link = get_field( 'cta_link' );

// ACF link field returns an array when return_format is 'array'.
$cta_url = is_array( $cta_link ) ? ( $cta_link['url'] ?? '' ) : $cta_link;

$inner_template = array(
	array( 'core/paragraph', array( 'placeholder' => __( 'Add supporting copy…', 'cn-starter' ) ) ),
);
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="cta__container container">
		<?php if ( $heading ) : ?>
			<h2 class="cta__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php elseif ( $is_preview ) : ?>
			<h2 class="cta__heading cta__heading--placeholder"><?php esc_html_e( 'Add a heading in the sidebar', 'cn-starter' ); ?></h2>
		<?php endif; ?>

		<div class="cta__body">
			<InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_template ) ); ?>" />
		</div>

		<?php if ( $cta_text && $cta_url ) : ?>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="cta__button btn btn--primary">
				<?php echo esc_html( $cta_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
