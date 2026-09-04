<?php
/**
 * Design Tokens — quick brand color editor.
 *
 * CN Starter → Design Tokens. Always accessible (not just during wizard).
 * Lets developers set core brand colors quickly. For full Figma token
 * import (typography, spacing, etc.), use CN Starter → Figma Tokens.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the admin page.
 */
function cn_design_tokens_menu() {
	add_submenu_page(
		'cn-starter',
		__( 'Design Tokens', 'cn-starter' ),
		__( 'Design Tokens', 'cn-starter' ),
		'manage_options',
		'cn-design-tokens',
		'cn_design_tokens_render'
	);
}
add_action( 'admin_menu', 'cn_design_tokens_menu' );

/**
 * Handle form submission.
 */
function cn_design_tokens_handle() {
	if ( ! isset( $_POST['cn_dt_action'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_design_tokens' );

	$css  = '';
	$keys = array(
		'primary'   => 'color-primary',
		'secondary' => 'color-secondary',
		'accent'    => 'color-accent',
	);

	$saved = array();

	foreach ( $keys as $field => $var ) {
		$post_key = 'color_' . $field;
		if ( isset( $_POST[ $post_key ] ) ) {
			$value = sanitize_hex_color( wp_unslash( $_POST[ $post_key ] ) );
			if ( $value ) {
				$css .= "--{$var}: {$value};\n";
				$saved[ $field ] = $value;
			}
		}
	}

	if ( $css ) {
		update_option( 'cn_design_tokens_css', ":root {\n{$css}}" );
		update_option( 'cn_design_tokens_quick', $saved );
		add_settings_error( 'cn_dt', 'saved', __( 'Brand colors saved. Changes are live on the frontend and editor.', 'cn-starter' ), 'success' );
	} else {
		add_settings_error( 'cn_dt', 'empty', __( 'No valid colors submitted.', 'cn-starter' ), 'error' );
	}
}
add_action( 'admin_init', 'cn_design_tokens_handle' );

/**
 * Get saved quick colors (falls back to CN brand defaults).
 */
function cn_design_tokens_quick_colors() {
	$saved = get_option( 'cn_design_tokens_quick', array() );

	return array(
		'primary'   => $saved['primary'] ?? '#059973',
		'secondary' => $saved['secondary'] ?? '#000000',
		'accent'    => $saved['accent'] ?? '#2aa072',
	);
}

/**
 * Render the page.
 */
function cn_design_tokens_render() {
	$colors = cn_design_tokens_quick_colors();
	$has_figma = get_option( 'cn_design_tokens_css' );
	?>
	<div class="wrap cn-dt">
		<?php cn_admin_page_header( __( 'Design Tokens', 'cn-starter' ), __( 'Quick-set core brand colors. Live immediately.', 'cn-starter' ) ); ?>
		<?php settings_errors( 'cn_dt' ); ?>

		<div class="cn-dt__intro">
			<p>
				<?php esc_html_e( 'Quick-set your core brand colors. These override the theme defaults and are live immediately.', 'cn-starter' ); ?>
				<?php esc_html_e( 'For full token import (typography, spacing, fonts from Figma), use', 'cn-starter' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>"><?php esc_html_e( 'Figma Tokens', 'cn-starter' ); ?></a>.
			</p>
		</div>

		<div class="cn-dt__grid">
			<div class="cn-dt__form-card">
				<form method="post" class="cn-dt-form">
					<?php wp_nonce_field( 'cn_design_tokens' ); ?>
					<input type="hidden" name="cn_dt_action" value="save">

					<table class="form-table">
						<tr>
							<th scope="row"><label for="color_primary"><?php esc_html_e( 'Primary Color', 'cn-starter' ); ?></label></th>
							<td>
								<input type="color" name="color_primary" id="color_primary" value="<?php echo esc_attr( $colors['primary'] ); ?>" class="cn-dt-color">
								<span class="cn-dt-hex"><?php echo esc_html( strtoupper( $colors['primary'] ) ); ?></span>
								<p class="description"><?php esc_html_e( 'Used for buttons, links, and active states. Default: CN Green.', 'cn-starter' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="color_secondary"><?php esc_html_e( 'Secondary Color', 'cn-starter' ); ?></label></th>
							<td>
								<input type="color" name="color_secondary" id="color_secondary" value="<?php echo esc_attr( $colors['secondary'] ); ?>" class="cn-dt-color">
								<span class="cn-dt-hex"><?php echo esc_html( strtoupper( $colors['secondary'] ) ); ?></span>
								<p class="description"><?php esc_html_e( 'Used for footer background and dark sections. Default: CN Black.', 'cn-starter' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="color_accent"><?php esc_html_e( 'Accent Color', 'cn-starter' ); ?></label></th>
							<td>
								<input type="color" name="color_accent" id="color_accent" value="<?php echo esc_attr( $colors['accent'] ); ?>" class="cn-dt-color">
								<span class="cn-dt-hex"><?php echo esc_html( strtoupper( $colors['accent'] ) ); ?></span>
								<p class="description"><?php esc_html_e( 'Used for highlights and decorative accents. Default: CN Vert.', 'cn-starter' ); ?></p>
							</td>
						</tr>
					</table>

					<p class="cn-dt-actions">
						<button class="button button-primary button-large" type="submit"><?php esc_html_e( 'Save Colors', 'cn-starter' ); ?></button>
						<?php if ( $has_figma ) : ?>
							<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>"><?php esc_html_e( 'Full Figma Token Import', 'cn-starter' ); ?></a>
						<?php endif; ?>
					</p>
				</form>
			</div>

			<div class="cn-dt__preview-card">
				<h2><?php esc_html_e( 'Live Preview', 'cn-starter' ); ?></h2>
				<div class="cn-dt-swatches">
					<div class="cn-dt-swatch" style="background:<?php echo esc_attr( $colors['primary'] ); ?>">
						<span class="cn-dt-swatch__label">Primary</span>
						<span class="cn-dt-swatch__hex"><?php echo esc_html( strtoupper( $colors['primary'] ) ); ?></span>
					</div>
					<div class="cn-dt-swatch" style="background:<?php echo esc_attr( $colors['secondary'] ); ?>">
						<span class="cn-dt-swatch__label">Secondary</span>
						<span class="cn-dt-swatch__hex"><?php echo esc_html( strtoupper( $colors['secondary'] ) ); ?></span>
					</div>
					<div class="cn-dt-swatch" style="background:<?php echo esc_attr( $colors['accent'] ); ?>">
						<span class="cn-dt-swatch__label">Accent</span>
						<span class="cn-dt-swatch__hex"><?php echo esc_html( strtoupper( $colors['accent'] ) ); ?></span>
					</div>
				</div>
				<div class="cn-dt-demo">
					<a href="#" class="button cn-dt-demo-btn" style="background:<?php echo esc_attr( $colors['primary'] ); ?>;border-color:<?php echo esc_attr( $colors['primary'] ); ?>;color:#fff">Sample Button</a>
					<a href="#" class="cn-dt-demo-link" style="color:<?php echo esc_attr( $colors['primary'] ); ?>">Sample Link</a>
				</div>
			</div>
		</div>

		<script>
		jQuery( function ( $ ) {
			$( '.cn-dt-color' ).on( 'input change', function () {
				var val = $( this ).val().toUpperCase();
				$( this ).next( '.cn-dt-hex' ).text( val );
				var field = $( this ).attr( 'id' );
				if ( field === 'color_primary' ) {
					$( '.cn-dt-swatch' ).eq(0).css( 'background', val );
					$( '.cn-dt-swatch' ).eq(0).find('.cn-dt-swatch__hex').text(val);
					$( '.cn-dt-demo-btn' ).css( { 'background': val, 'border-color': val } );
					$( '.cn-dt-demo-link' ).css( 'color', val );
				} else if ( field === 'color_secondary' ) {
					$( '.cn-dt-swatch' ).eq(1).css( 'background', val );
					$( '.cn-dt-swatch' ).eq(1).find('.cn-dt-swatch__hex').text(val);
				} else if ( field === 'color_accent' ) {
					$( '.cn-dt-swatch' ).eq(2).css( 'background', val );
					$( '.cn-dt-swatch' ).eq(2).find('.cn-dt-swatch__hex').text(val);
				}
			} );
		} );
		</script>

	<?php
}
