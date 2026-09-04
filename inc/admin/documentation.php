<?php
/**
 * In-admin documentation page.
 *
 * CN Starter → Theme Docs. Renders markdown files from /docs/.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the docs page.
 */
function cn_docs_menu() {
	add_submenu_page(
		'cn-starter',
		__( 'Theme Docs', 'cn-starter' ),
		__( 'Theme Docs', 'cn-starter' ),
		'manage_options',
		'cn-docs',
		'cn_docs_render'
	);
}
add_action( 'admin_menu', 'cn_docs_menu' );

/**
 * Lightweight markdown → HTML converter (subset: headings, lists, code, links, bold, italic).
 *
 * @param string $md Markdown text.
 * @return string HTML.
 */
function cn_docs_markdown( $md ) {
	// Code blocks (fenced).
	$md = preg_replace_callback( '/```(\w*)\n(.*?)```/s', static function ( $m ) {
		return '<pre><code>' . esc_html( $m[2] ) . '</code></pre>';
	}, $md );

	// Inline code.
	$md = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $md );

	// Headings.
	$md = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $md );
	$md = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $md );
	$md = preg_replace( '/^# (.+)$/m', '<h1>$1</h1>', $md );

	// Bold and italic.
	$md = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $md );
	$md = preg_replace( '/\*([^*]+)\*/', '<em>$1</em>', $md );

	// Links — rewrite relative .md links to in-admin doc URLs.
	$md = preg_replace_callback( '/\[([^\]]+)\]\(([^)]+)\)/', static function ( $m ) {
		$text = $m[1];
		$url  = $m[2];
		if ( preg_match( '/^([a-z0-9_-]+)\.md$/i', $url, $slug ) ) {
			$url = admin_url( 'admin.php?page=cn-docs&doc=' . sanitize_file_name( $slug[1] ) );
		}
		return '<a href="' . esc_url( $url ) . '">' . $text . '</a>';
	}, $md );

	// Unordered lists.
	$md = preg_replace( '/^[\-\*] (.+)$/m', '<li>$1</li>', $md );
	$md = preg_replace( '/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $md );

	// Paragraphs (lines not already wrapped).
	$lines = preg_split( '/\n{2,}/', $md );
	$html  = '';
	foreach ( $lines as $block ) {
		$block = trim( $block );
		if ( ! $block ) {
			continue;
		}
		if ( preg_match( '/^<(h[1-6]|ul|ol|pre)/', $block ) ) {
			$html .= $block . "\n";
		} else {
			$html .= '<p>' . nl2br( $block ) . "</p>\n";
		}
	}

	return $html;
}

/**
 * Get available doc files.
 *
 * @return array [slug => title]
 */
function cn_docs_list() {
	$dir   = CN_THEME_DIR . '/docs';
	$files = glob( $dir . '/*.md' );
	$docs  = array();
	foreach ( $files as $file ) {
		$slug  = basename( $file, '.md' );
		$title = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
		$docs[ $slug ] = $title;
	}
	return $docs;
}

/**
 * Render the docs page.
 */
function cn_docs_render() {
	$docs       = cn_docs_list();
	$current    = isset( $_GET['doc'] ) ? sanitize_file_name( wp_unslash( $_GET['doc'] ) ) : 'README';
	$file       = CN_THEME_DIR . '/docs/' . $current . '.md';
	$content    = file_exists( $file ) ? file_get_contents( $file ) : '';
	?>
	<div class="wrap cn-docs-wrap">
		<?php cn_admin_page_header( __( 'Theme Documentation', 'cn-starter' ), __( 'Guides and references for building with CN Starter.', 'cn-starter' ) ); ?>
		<nav class="cn-docs-nav">
			<h3><?php esc_html_e( 'Documentation', 'cn-starter' ); ?></h3>
			<ul>
				<?php foreach ( $docs as $slug => $title ) : ?>
					<li>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-docs&doc=' . $slug ) ); ?>" class="<?php echo $slug === $current ? 'is-active' : ''; ?>">
							<?php echo esc_html( $title ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="cn-docs-content">
			<?php echo wp_kses_post( cn_docs_markdown( $content ) ); // phpcs:ignore -- our own markdown. ?>
		</div>
	</div>
	<?php
}

/**
 * Docs page styles — handled by cn_admin_branding_styles in branding.php.
 */
