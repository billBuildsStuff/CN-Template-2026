<?php
/**
 * Spacer block.
 *
 * Fields: size (sm|md|lg|xl|2xl), size_mobile (optional override).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$size        = get_field( 'size' ) ?: 'lg';
$size_mobile = get_field( 'size_mobile' );

$classes = 'spacer spacer--' . esc_attr( $size );
if ( $size_mobile ) {
	$classes .= ' spacer--mobile-' . esc_attr( $size_mobile );
}
if ( $is_preview ) {
	$classes .= ' spacer--preview';
}
?>

<div class="<?php echo esc_attr( $classes ); ?>" aria-hidden="true">
	<?php if ( $is_preview ) : ?>
		<span class="spacer__label"><?php echo esc_html( strtoupper( $size ) ); ?></span>
	<?php endif; ?>
</div>
