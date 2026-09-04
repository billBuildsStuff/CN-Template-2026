<?php
/**
 * CN_BLOCK_TITLE block template.
 *
 * HOW TO CREATE A BLOCK FROM THIS TEMPLATE:
 * 1. Copy this folder to /blocks/your-block-slug/
 * 2. Find & replace: cn-block-slug -> your-block-slug, block-slug -> your BEM base class,
 *    CN_BLOCK_TITLE / CN_BLOCK_DESCRIPTION / CN_BLOCK_KEYWORD in block.json
 * 3. Create an ACF field group, set Location to "Block is equal to CN_BLOCK_TITLE",
 *    and save (it will sync to /acf-json/)
 * 4. Reference your fields below with get_field()
 * Or skip all of this and run: wp cn block create your-block-slug "Your Block Title"
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during backend preview render.
 * @param int    $post_id    The post ID the block is rendering against.
 */

$attrs = cn_block_attrs( $block, 'block-slug' );

// Example thumbnail in the block inserter.
if ( ! empty( $block['data']['_is_example'] ) ) {
	echo '<div class="block-slug"><p>CN_BLOCK_TITLE preview</p></div>';
	return;
}

$heading = get_field( 'heading' );

// Editor placeholder when the block is empty.
if ( ! $heading && $is_preview ) {
	cn_block_placeholder( 'CN_BLOCK_TITLE' );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="block-slug__container container">
		<?php if ( $heading ) : ?>
			<h2 class="block-slug__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
	</div>
</section>
