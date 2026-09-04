<?php
/**
 * Hero block.
 *
 * Fields: heading, subheading, cta_text, cta_link, background_image, overlay_opacity.
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'hero' );

$heading          = get_field( 'heading' );
$subheading       = get_field( 'subheading' );
$cta_text         = get_field( 'cta_text' );
$cta_link         = get_field( 'cta_link' );
$background_image = get_field( 'background_image' );
$overlay_opacity  = get_field( 'overlay_opacity' );

if ( ! $heading && ! $subheading && $is_preview ) {
	cn_block_placeholder( __( 'Hero', 'cn-starter' ), __( 'Add a heading and CTA in the block sidebar.', 'cn-starter' ) );
	return;
}

$style = '';
if ( $background_image ) {
	$url   = is_array( $background_image ) ? $background_image['url'] : wp_get_attachment_image_url( $background_image, 'cn-hero' );
	$style = 'background-image:url(' . esc_url( $url ) . ');';
}
$overlay = is_numeric( $overlay_opacity ) ? max( 0, min( 100, (int) $overlay_opacity ) ) / 100 : 0.4;
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?><?php echo $background_image ? ' hero--has-image' : ''; ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php if ( $background_image ) : ?>
		<div class="hero__overlay" style="opacity:<?php echo esc_attr( $overlay ); ?>"></div>
	<?php endif; ?>
	<div class="hero__container container">
		<?php if ( $heading ) : ?>
			<h1 class="hero__heading"><?php echo esc_html( $heading ); ?></h1>
		<?php endif; ?>

		<?php if ( $subheading ) : ?>
			<p class="hero__subheading"><?php echo esc_html( $subheading ); ?></p>
		<?php endif; ?>

		<?php if ( $cta_text && $cta_link ) : ?>
			<a href="<?php echo esc_url( $cta_link ); ?>" class="hero__cta btn btn--primary">
				<?php echo esc_html( $cta_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
