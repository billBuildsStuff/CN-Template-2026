<?php
/**
 * Figma Tokens importer.
 *
 * CN Starter → Figma Tokens. Paste a token JSON (exported by your designer
 * or generated via the Figma MCP — see /docs/figma-workflow.md), preview
 * the mapping, and apply. Applied tokens are stored as a CSS overrides
 * option and injected after variables.css on both frontend and editor.
 *
 * Expected JSON shape (flat or grouped):
 * {
 *   "colors":     { "primary": "#0af", "accent": "#f60" },
 *   "typography": { "font-primary": "'Inter', sans-serif", "font-size-base": "1rem" },
 *   "spacing":    { "space-md": "1rem", "space-lg": "2rem" }
 * }
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the admin page.
 */
function cn_figma_tokens_menu() {
	add_submenu_page(
		'cn-starter',
		__( 'Figma Tokens', 'cn-starter' ),
		__( 'Figma Tokens', 'cn-starter' ),
		'manage_options',
		'cn-figma-tokens',
		'cn_figma_tokens_render'
	);
}
add_action( 'admin_menu', 'cn_figma_tokens_menu' );

/**
 * Convert a token JSON array into :root CSS overrides.
 *
 * @param array $tokens Parsed token JSON.
 * @return string CSS.
 */
function cn_tokens_to_css( $tokens ) {
	$css    = '';
	$groups = array(
		'colors'     => 'color-',
		'typography' => '',
		'spacing'    => '',
	);

	foreach ( $groups as $group => $prefix ) {
		if ( empty( $tokens[ $group ] ) || ! is_array( $tokens[ $group ] ) ) {
			continue;
		}
		foreach ( $tokens[ $group ] as $name => $value ) {
			$name  = sanitize_key( str_replace( array( ' ', '/' ), '-', $name ) );
			$value = wp_strip_all_tags( (string) $value );
			// Don't double-prefix names already carrying the prefix.
			$var  = '--' . ( $prefix && 0 !== strpos( $name, rtrim( $prefix, '-' ) ) ? $prefix : '' ) . $name;
			$css .= "\t{$var}: {$value};\n";
		}
	}

	return $css ? ":root {\n{$css}}" : '';
}

/**
 * Handle apply/reset submissions.
 */
function cn_figma_tokens_handle() {
	if ( ! isset( $_POST['cn_tokens_action'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_figma_tokens' );

	$action = sanitize_key( $_POST['cn_tokens_action'] );

	if ( 'reset' === $action ) {
		delete_option( 'cn_design_tokens_css' );
		delete_option( 'cn_design_tokens_json' );
		add_settings_error( 'cn_tokens', 'reset', __( 'Token overrides removed — theme defaults restored.', 'cn-starter' ), 'success' );
		return;
	}

	$raw    = isset( $_POST['tokens_json'] ) ? wp_unslash( $_POST['tokens_json'] ) : '';
	$tokens = json_decode( $raw, true );

	if ( null === $tokens ) {
		add_settings_error( 'cn_tokens', 'invalid', __( 'Invalid JSON — check the format and try again.', 'cn-starter' ) );
		return;
	}

	$css = cn_tokens_to_css( $tokens );
	if ( ! $css ) {
		add_settings_error( 'cn_tokens', 'empty', __( 'No usable tokens found. Expected "colors", "typography", and/or "spacing" groups.', 'cn-starter' ) );
		return;
	}

	update_option( 'cn_design_tokens_css', $css );
	update_option( 'cn_design_tokens_json', wp_json_encode( $tokens ) );
	add_settings_error( 'cn_tokens', 'applied', __( 'Tokens applied. The frontend and editor now use your Figma values.', 'cn-starter' ), 'success' );
}
add_action( 'admin_init', 'cn_figma_tokens_handle' );

/**
 * Extract font families from token JSON.
 *
 * Parses the typography group for font-family declarations and
 * returns a unique list of font names with sourcing guidance.
 *
 * @param array $tokens Parsed token JSON.
 * @return array Array of font info: [ 'name' => string, 'stack' => string, 'source' => string ]
 */
function cn_extract_fonts_from_tokens( $tokens ) {
	if ( empty( $tokens['typography'] ) || ! is_array( $tokens['typography'] ) ) {
		return array();
	}

	$fonts = array();
	$seen  = array();

	foreach ( $tokens['typography'] as $key => $value ) {
		// Look for font-family related keys.
		if ( false === stripos( $key, 'font' ) || false === stripos( $key, 'family' ) && false === stripos( $key, 'font-primary' ) && false === stripos( $key, 'font-secondary' ) ) {
			// Also check keys that just say 'font-primary' or 'font-secondary'.
			if ( ! preg_match( '/^font[-_](primary|secondary)/i', $key ) ) {
				continue;
			}
		}

		$value = wp_strip_all_tags( (string) $value );

		// Extract the first font name from the stack (before the first comma).
		$parts = explode( ',', $value );
		$name  = trim( $parts[0], " '\"" );

		if ( ! $name || isset( $seen[ $name ] ) ) {
			continue;
		}
		$seen[ $name ] = true;

		// Determine likely sourcing.
		$source = 'Self-hosted or CDN';
		$name_lower = strtolower( $name );
		$adobe_fonts = array( 'gotham', 'calluna', 'proxima', 'adobe' );
		$google_fonts = array( 'inter', 'roboto', 'open sans', 'lato', 'montserrat', 'source sans', 'merriweather', 'nunito', 'poppins', 'raleway' );

		foreach ( $adobe_fonts as $af ) {
			if ( strpos( $name_lower, $af ) !== false ) {
				$source = 'Adobe Fonts (formerly Typekit) — purchased/hosted via Adobe Web Projects';
				break;
			}
		}
		foreach ( $google_fonts as $gf ) {
			if ( strpos( $name_lower, $gf ) !== false ) {
				$source = 'Google Fonts — free, self-host or CDN';
				break;
			}
		}

		$fonts[] = array(
			'name'   => $name,
			'stack'  => $value,
			'source' => $source,
		);
	}

	return $fonts;
}

/**
 * Render the page.
 */
function cn_figma_tokens_render() {
	$current_json = get_option( 'cn_design_tokens_json', '' );
	$current_css  = get_option( 'cn_design_tokens_css', '' );
	$tokens       = $current_json ? json_decode( $current_json, true ) : array();
	$fonts        = $tokens ? cn_extract_fonts_from_tokens( $tokens ) : array();
	?>
	<div class="wrap cn-figma-tokens">
		<?php cn_admin_page_header( __( 'Figma Design Tokens', 'cn-starter' ), __( 'Import full design tokens from Figma JSON export.', 'cn-starter' ) ); ?>
		<?php settings_errors( 'cn_tokens' ); ?>

		<div class="cn-figma-tokens__intro">
			<p>
				<?php esc_html_e( 'Paste a design-token JSON exported from Figma. See the workflow guide in', 'cn-starter' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-docs&doc=figma-workflow' ) ); ?>"><?php esc_html_e( 'Theme Docs → Figma Workflow', 'cn-starter' ); ?></a>.
			</p>
		</div>

		<div class="cn-figma-tokens__grid">
			<div class="cn-figma-tokens__main">
				<form method="post" class="cn-figma-tokens__form">
					<?php wp_nonce_field( 'cn_figma_tokens' ); ?>
					<textarea name="tokens_json" rows="16" class="large-text code" placeholder='{"colors": {"primary": "#0066cc"}, "typography": {"font-primary": "'Inter', sans-serif"}, "spacing": {"space-lg": "2rem"}}'><?php echo esc_textarea( $current_json ? wp_json_encode( json_decode( $current_json ), JSON_PRETTY_PRINT ) : '' ); ?></textarea>
					<p class="cn-figma-tokens__actions">
						<button class="button button-primary" name="cn_tokens_action" value="apply"><?php esc_html_e( 'Apply Tokens', 'cn-starter' ); ?></button>
						<?php if ( $current_css ) : ?>
							<button class="button" name="cn_tokens_action" value="reset" onclick="return confirm('<?php echo esc_js( __( 'Remove all token overrides?', 'cn-starter' ) ); ?>')"><?php esc_html_e( 'Reset to Theme Defaults', 'cn-starter' ); ?></button>
						<?php endif; ?>
					</p>
				</form>

				<?php if ( $current_css ) : ?>
					<h2><?php esc_html_e( 'Active CSS Overrides', 'cn-starter' ); ?></h2>
					<pre class="cn-figma-tokens__css"><?php echo esc_html( $current_css ); ?></pre>
				<?php endif; ?>
		</div>

			<div class="cn-figma-tokens__sidebar">
				<?php if ( ! empty( $fonts ) ) : ?>
					<div class="cn-font-panel">
						<div class="cn-font-panel__header">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<h2><?php esc_html_e( 'Fonts in Use', 'cn-starter' ); ?></h2>
						</div>
						<p class="cn-font-panel__desc"><?php esc_html_e( 'Fonts detected from your Figma token import. Source these before deploying.', 'cn-starter' ); ?></p>
						<ul class="cn-font-panel__list">
							<?php foreach ( $fonts as $font ) : ?>
								<li class="cn-font-panel__item">
									<div class="cn-font-panel__font-name" style="font-family:<?php echo esc_attr( $font['stack'] ); ?>"><?php echo esc_html( $font['name'] ); ?></div>
									<div class="cn-font-panel__font-source"><?php echo esc_html( $font['source'] ); ?></div>
									<code class="cn-font-panel__font-stack"><?php echo esc_html( $font['stack'] ); ?></code>
								</li>
							<?php endforeach; ?>
						</ul>
						<div class="cn-font-panel__hint">
							<span class="dashicons dashicons-info-outline"></span>
							<?php esc_html_e( 'Add @font-face or CDN links in theme header. For Adobe Fonts, create a Web Project and add the embed code to header.php.', 'cn-starter' ); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="cn-font-panel cn-font-panel--empty">
						<div class="cn-font-panel__header">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<h2><?php esc_html_e( 'Fonts in Use', 'cn-starter' ); ?></h2>
						</div>
						<p class="cn-font-panel__desc"><?php esc_html_e( 'Import tokens with typography data to see which fonts your Figma file uses.', 'cn-starter' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
}
