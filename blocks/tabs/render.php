<?php
/**
 * Tabs block.
 *
 * Fields: tabs (repeater: title, content [wysiwyg]).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'tabs' );

$tabs = get_field( 'tabs' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	$tabs = array(
		array( 'title' => __( 'Tab One', 'cn-starter' ), 'content' => __( 'First panel content.', 'cn-starter' ) ),
		array( 'title' => __( 'Tab Two', 'cn-starter' ), 'content' => __( 'Second panel content.', 'cn-starter' ) ),
	);
}

if ( ! $tabs && $is_preview ) {
	cn_block_placeholder( __( 'Tabs', 'cn-starter' ), __( 'Add tabs in the block sidebar.', 'cn-starter' ) );
	return;
}

cn_block_needs_js();
?>

<div id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="tabs__container container">
		<div class="tabs__nav" role="tablist">
			<?php foreach ( (array) $tabs as $i => $tab ) : ?>
				<button
					class="tabs__button<?php echo 0 === $i ? ' is-active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $attrs['id'] . '-panel-' . $i ); ?>"
				>
					<?php echo esc_html( $tab['title'] ?? '' ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( (array) $tabs as $i => $tab ) : ?>
			<div
				class="tabs__panel"
				id="<?php echo esc_attr( $attrs['id'] . '-panel-' . $i ); ?>"
				role="tabpanel"
				<?php echo 0 !== $i ? 'hidden' : ''; ?>
			>
				<?php echo wp_kses_post( wpautop( $tab['content'] ?? '' ) ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
