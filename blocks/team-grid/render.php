<?php
/**
 * Team Grid block.
 *
 * Fields: heading, members (repeater: photo, name, role, bio).
 *
 * @param array $block      Block settings and attributes.
 * @param bool  $is_preview True during backend preview render.
 */

$attrs = cn_block_attrs( $block, 'team' );

$heading = get_field( 'heading' );
$members = get_field( 'members' );

if ( ! empty( $block['data']['_is_example'] ) ) {
	$members = array_fill( 0, 3, array(
		'photo' => null,
		'name'  => __( 'Team Member', 'cn-starter' ),
		'role'  => __( 'Job Title', 'cn-starter' ),
		'bio'   => '',
	) );
}

if ( ! $members && $is_preview ) {
	cn_block_placeholder( __( 'Team Grid', 'cn-starter' ), __( 'Add team members in the block sidebar.', 'cn-starter' ) );
	return;
}
?>

<section id="<?php echo $attrs['id']; ?>" class="<?php echo $attrs['class']; ?>">
	<div class="team__container container">
		<?php if ( $heading ) : ?>
			<h2 class="team__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="team__grid">
			<?php foreach ( (array) $members as $member ) : ?>
				<div class="team-member">
					<?php if ( ! empty( $member['photo'] ) ) : ?>
						<?php echo wp_get_attachment_image( is_array( $member['photo'] ) ? $member['photo']['ID'] : $member['photo'], 'cn-square', false, array( 'class' => 'team-member__photo', 'loading' => 'lazy' ) ); ?>
					<?php else : ?>
						<div class="team-member__photo team-member__photo--placeholder"></div>
					<?php endif; ?>
					<h3 class="team-member__name"><?php echo esc_html( $member['name'] ?? '' ); ?></h3>
					<?php if ( ! empty( $member['role'] ) ) : ?>
						<p class="team-member__role"><?php echo esc_html( $member['role'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $member['bio'] ) ) : ?>
						<p class="team-member__bio"><?php echo esc_html( $member['bio'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
