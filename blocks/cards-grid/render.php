<?php
/**
 * Cards Grid block.
 *
 * Fields: heading, columns (2|3|4), cards (repeater: icon [image], title, text, link [link array]).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'cards-grid' );

$heading = get_field( 'heading' );
$columns = get_field( 'columns' ) ?: '3';
$cards   = get_field( 'cards' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	$cards = array_fill( 0, 3, array(
		'icon'  => null,
		'title' => __( 'Card title', 'cn-starter' ),
		'text'  => __( 'A short supporting description for this card.', 'cn-starter' ),
		'link'  => null,
	) );
}

if ( ! $cards && $is_preview ) {
	cn_block_placeholder( __( 'Cards Grid', 'cn-starter' ), __( 'Add cards in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="cards-grid__container container">
		<?php if ( $heading ) : ?>
			<h2 class="cards-grid__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="cards-grid__grid cards-grid__grid--cols-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( (array) $cards as $card ) : ?>
				<?php
				$link     = $card['link'] ?? null;
				$link_url = is_array( $link ) ? ( $link['url'] ?? '' ) : '';
				$tag      = $link_url ? 'a' : 'div';
				?>
				<<?php echo $tag; ?> class="card"<?php echo $link_url ? ' href="' . esc_url( $link_url ) . '"' : ''; ?>>
					<?php if ( ! empty( $card['icon'] ) ) : ?>
						<div class="card__icon">
							<?php echo wp_get_attachment_image( is_array( $card['icon'] ) ? $card['icon']['ID'] : $card['icon'], 'thumbnail' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $card['title'] ) ) : ?>
						<h3 class="card__title"><?php echo esc_html( $card['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $card['text'] ) ) : ?>
						<p class="card__text"><?php echo esc_html( $card['text'] ); ?></p>
					<?php endif; ?>
				</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
