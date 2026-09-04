<?php
/**
 * Video Embed block.
 *
 * Fields: video_url (oEmbed), caption.
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'video-embed' );

$video_url = get_field( 'video_url' );
$caption   = get_field( 'caption' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	echo '<div class="video-embed"><div class="video-embed__frame" style="background:var(--color-secondary);display:grid;place-items:center;color:#fff">&#9658;</div></div>';
	return;
}

if ( ! $video_url && $is_preview ) {
	cn_block_placeholder( __( 'Video Embed', 'cn-starter' ), __( 'Paste a YouTube or Vimeo URL in the sidebar.', 'cn-starter' ) );
	return;
}

$embed = $video_url ? wp_oembed_get( $video_url ) : '';
?>

<figure id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="video-embed__frame">
		<?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput -- oEmbed HTML from WP core. ?>
	</div>
	<?php if ( $caption ) : ?>
		<figcaption class="video-embed__caption u-text-muted"><?php echo esc_html( $caption ); ?></figcaption>
	<?php endif; ?>
</figure>
