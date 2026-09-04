<?php
/**
 * Gravity Form block.
 *
 * Fields: heading, intro, form_id (select, populated in inc/integrations/gravity-forms.php).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'gform-block' );

$heading = get_field( 'heading' );
$intro   = get_field( 'intro' );
$form_id = get_field( 'form_id' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	echo '<div class="gform-block"><h2>' . esc_html__( 'Get in touch', 'cn-starter' ) . '</h2><p>' . esc_html__( 'A Gravity Form renders here.', 'cn-starter' ) . '</p></div>';
	return;
}

if ( ! class_exists( 'GFAPI' ) ) {
	if ( $is_preview ) {
		cn_block_placeholder( __( 'Gravity Form', 'cn-starter' ), __( 'Gravity Forms is not active.', 'cn-starter' ) );
	}
	return;
}

if ( ! $form_id && $is_preview ) {
	cn_block_placeholder( __( 'Gravity Form', 'cn-starter' ), __( 'Choose a form in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="gform-block__container container container--narrow">
		<?php if ( $heading ) : ?>
			<h2 class="gform-block__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( $intro ) : ?>
			<p class="gform-block__intro u-text-muted"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
		<div class="gform-block__form">
			<?php
			if ( $form_id ) {
				gravity_form( (int) $form_id, false, false, false, null, true );
			}
			?>
		</div>
	</div>
</section>
