<?php
/**
 * FAQ Accordion block.
 *
 * Fields: heading, faqs (repeater: question, answer [wysiwyg]).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'faq' );

$heading = get_field( 'heading' );
$faqs    = get_field( 'faqs' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	$faqs = array(
		array( 'question' => __( 'How does this work?', 'cn-starter' ), 'answer' => __( 'Click a question to reveal its answer.', 'cn-starter' ) ),
		array( 'question' => __( 'Can I add more questions?', 'cn-starter' ), 'answer' => __( 'Yes — add rows in the block sidebar.', 'cn-starter' ) ),
	);
}

if ( ! $faqs && $is_preview ) {
	cn_block_placeholder( __( 'FAQ Accordion', 'cn-starter' ), __( 'Add questions in the block sidebar.', 'cn-starter' ) );
	return;
}

cn_block_needs_js();
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="faq__container container container--narrow">
		<?php if ( $heading ) : ?>
			<h2 class="faq__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="faq__list">
			<?php foreach ( (array) $faqs as $i => $item ) : ?>
				<div class="faq__item">
					<h3 class="faq__question-wrap">
						<button class="faq__question" aria-expanded="false" aria-controls="<?php echo esc_attr( $attrs['id'] . '-panel-' . $i ); ?>">
							<?php echo esc_html( $item['question'] ?? '' ); ?>
							<span class="faq__icon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="faq__answer" id="<?php echo esc_attr( $attrs['id'] . '-panel-' . $i ); ?>">
						<?php echo wp_kses_post( wpautop( $item['answer'] ?? '' ) ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
