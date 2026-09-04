<?php
/**
 * Block Generator — CN Starter → Create Block.
 *
 * Scaffolds a new block from /blocks/_template/ without touching the CLI.
 * (CLI equivalent: wp cn block create <slug> "<Title>")
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the admin page.
 */
function cn_block_generator_menu() {
	add_submenu_page(
		'cn-starter',
		__( 'Create Block', 'cn-starter' ),
		__( 'Create Block', 'cn-starter' ),
		'manage_options',
		'cn-block-generator',
		'cn_block_generator_render'
	);
}
add_action( 'admin_menu', 'cn_block_generator_menu' );

/**
 * Scaffold a block from the _template folder.
 *
 * @param string $slug  Block slug (kebab-case).
 * @param string $title Block title.
 * @param string $desc  Block description.
 * @param string $icon  Dashicon name.
 * @return true|WP_Error
 */
function cn_scaffold_block( $slug, $title, $desc = '', $icon = 'smiley' ) {
	$slug = sanitize_title( $slug );
	if ( ! $slug ) {
		return new WP_Error( 'bad_slug', __( 'Invalid block slug.', 'cn-starter' ) );
	}

	$src  = CN_THEME_DIR . '/blocks/_template';
	$dest = CN_THEME_DIR . '/blocks/' . $slug;

	if ( file_exists( $dest ) ) {
		return new WP_Error( 'exists', __( 'A block with that slug already exists.', 'cn-starter' ) );
	}
	if ( ! wp_mkdir_p( $dest ) ) {
		return new WP_Error( 'mkdir', __( 'Could not create the block folder — check filesystem permissions.', 'cn-starter' ) );
	}

	$replacements = array(
		'cn-block-slug'        => $slug,
		'block-slug'           => $slug,
		'CN_BLOCK_TITLE'       => $title,
		'CN_BLOCK_DESCRIPTION' => $desc ?: $title,
		'CN_BLOCK_KEYWORD'     => explode( '-', $slug )[0],
		'"icon": "smiley"'     => '"icon": "' . sanitize_key( $icon ) . '"',
	);

	foreach ( array( 'block.json', 'render.php', 'style.css' ) as $file ) {
		$contents = file_get_contents( $src . '/' . $file );
		$contents = str_replace( array_keys( $replacements ), array_values( $replacements ), $contents );
		if ( false === file_put_contents( $dest . '/' . $file, $contents ) ) {
			return new WP_Error( 'write', __( 'Could not write block files.', 'cn-starter' ) );
		}
	}

	// Stub an ACF field group so the block has a starter "heading" field.
	$group_key = 'group_cn_' . str_replace( '-', '_', $slug );
	$json      = array(
		'key'      => $group_key,
		'title'    => 'Block: ' . $title,
		'fields'   => array(
			array(
				'key'   => 'field_cn_' . str_replace( '-', '_', $slug ) . '_heading',
				'label' => 'Heading',
				'name'  => 'heading',
				'type'  => 'text',
			),
		),
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/' . $slug ) ) ),
		'position' => 'normal',
		'style'    => 'default',
		'active'   => true,
		'modified' => time(),
	);
	file_put_contents(
		CN_THEME_DIR . '/acf-json/' . $group_key . '.json',
		wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	);

	return true;
}

/**
 * Handle form submission.
 */
function cn_block_generator_handle() {
	if ( ! isset( $_POST['cn_new_block_slug'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_block_generator' );

	$result = cn_scaffold_block(
		sanitize_text_field( wp_unslash( $_POST['cn_new_block_slug'] ) ),
		sanitize_text_field( wp_unslash( $_POST['cn_new_block_title'] ?? '' ) ),
		sanitize_text_field( wp_unslash( $_POST['cn_new_block_desc'] ?? '' ) ),
		sanitize_text_field( wp_unslash( $_POST['cn_new_block_icon'] ?? 'smiley' ) )
	);

	if ( is_wp_error( $result ) ) {
		add_settings_error( 'cn_block_gen', 'error', $result->get_error_message() );
	} else {
		add_settings_error(
			'cn_block_gen',
			'created',
			sprintf(
				/* translators: %s: block slug */
				__( 'Block created at /blocks/%s/. Sync the new field group under Custom Fields, then customize render.php and style.css.', 'cn-starter' ),
				sanitize_title( wp_unslash( $_POST['cn_new_block_slug'] ) )
			),
			'success'
		);
	}
}
add_action( 'admin_init', 'cn_block_generator_handle' );

/**
 * Render the page.
 */
function cn_block_generator_render() {
	?>
	<div class="wrap cn-block-gen">
		<?php cn_admin_page_header( __( 'Create a New Block', 'cn-starter' ), __( 'Scaffold a ready-to-edit ACF block with starter field group.', 'cn-starter' ) ); ?>
		<?php settings_errors( 'cn_block_gen' ); ?>

		<div class="cn-block-gen__grid">
			<div class="cn-block-gen__main">
				<div class="cn-block-gen__card">
					<p class="cn-block-gen__intro"><?php esc_html_e( 'Scaffolds a ready-to-edit ACF block from the theme template, including a starter field group with a heading field.', 'cn-starter' ); ?></p>

					<form method="post" class="cn-block-gen__form">
						<?php wp_nonce_field( 'cn_block_generator' ); ?>
						<table class="form-table">
							<tr>
								<th><label for="cn_new_block_title"><?php esc_html_e( 'Block Title', 'cn-starter' ); ?></label></th>
								<td>
									<input name="cn_new_block_title" id="cn_new_block_title" type="text" class="regular-text" placeholder="Logo Wall" required>
									<p class="description"><?php esc_html_e( 'Human-readable name shown in the block inserter.', 'cn-starter' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="cn_new_block_slug"><?php esc_html_e( 'Slug (kebab-case)', 'cn-starter' ); ?></label></th>
								<td>
									<input name="cn_new_block_slug" id="cn_new_block_slug" type="text" class="regular-text" placeholder="logo-wall" pattern="^[a-z0-9-]+$" required>
									<p class="description"><?php esc_html_e( 'Auto-generated from title. Used for the folder name and block ID.', 'cn-starter' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="cn_new_block_desc"><?php esc_html_e( 'Description', 'cn-starter' ); ?></label></th>
								<td>
									<input name="cn_new_block_desc" id="cn_new_block_desc" type="text" class="regular-text" placeholder="A grid of client logos">
									<p class="description"><?php esc_html_e( 'Short description shown in the block inserter.', 'cn-starter' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="cn_new_block_icon"><?php esc_html_e( 'Dashicon', 'cn-starter' ); ?></label></th>
								<td>
									<input name="cn_new_block_icon" id="cn_new_block_icon" type="text" class="regular-text" value="smiley">
									<p class="description"><a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener"><?php esc_html_e( 'Browse dashicons', 'cn-starter' ); ?></a> <?php esc_html_e( '(opens in new tab)', 'cn-starter' ); ?></p>
								</td>
							</tr>
						</table>
						<p class="cn-block-gen__submit"><button class="button button-primary button-large"><?php esc_html_e( 'Create Block', 'cn-starter' ); ?></button></p>
					</form>
				</div>
			</div>

			<div class="cn-block-gen__sidebar">
				<div class="cn-block-gen__info-card">
					<h3><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e( 'What Gets Created', 'cn-starter' ); ?></h3>
					<ul class="cn-block-gen__files">
						<li><code>blocks/<?php esc_html_e( 'your-slug', 'cn-starter' ); ?>/block.json</code></li>
						<li><code>blocks/<?php esc_html_e( 'your-slug', 'cn-starter' ); ?>/render.php</code></li>
						<li><code>blocks/<?php esc_html_e( 'your-slug', 'cn-starter' ); ?>/style.css</code></li>
						<li><code>acf-json/group_cn_<?php esc_html_e( 'your_slug', 'cn-starter' ); ?>.json</code></li>
					</ul>
				</div>
				<div class="cn-block-gen__info-card">
					<h3><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'After Creating', 'cn-starter' ); ?></h3>
					<ol class="cn-block-gen__steps">
						<li><?php esc_html_e( 'Sync the ACF field group: Custom Fields → Tools → Sync', 'cn-starter' ); ?></li>
						<li><?php esc_html_e( 'Edit the fields in the ACF field group', 'cn-starter' ); ?></li>
						<li><?php esc_html_e( 'Customize render.php with your markup', 'cn-starter' ); ?></li>
						<li><?php esc_html_e( 'Add styles in style.css (BEM naming)', 'cn-starter' ); ?></li>
					</ol>
				</div>
				<div class="cn-block-gen__info-card">
					<h3><span class="dashicons dashicons-terminal"></span> <?php esc_html_e( 'Or Use WP-CLI', 'cn-starter' ); ?></h3>
					<code class="cn-block-gen__cli">wp cn block create logo-wall "Logo Wall" --description="Grid of logos" --icon=grid-view</code>
				</div>
			</div>
		</div>

		<script>
		jQuery( function ( $ ) {
			$( '#cn_new_block_title' ).on( 'input', function () {
				var slug = $( this ).val().toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' );
				$( '#cn_new_block_slug' ).val( slug );
			} );
		} );
		</script>

	<?php
}
