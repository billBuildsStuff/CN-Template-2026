<?php
/**
 * Top-level admin menu — CN Starter.
 *
 * Consolidates all theme admin pages under one parent menu item
 * with the CN logo icon. Subpages register themselves via
 * add_submenu_page( 'cn-starter', ... ) in their respective files.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the top-level CN Starter menu with a dashboard landing page.
 */
function cn_admin_top_menu() {
	$svg = file_get_contents( CN_THEME_DIR . '/assets/images/cn-mark.svg' );
	$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg );

	add_menu_page(
		__( 'CN Starter', 'cn-starter' ),
		__( 'CN Starter', 'cn-starter' ),
		'manage_options',
		'cn-starter',
		'cn_dashboard_render',
		$icon,
		59
	);

	// Rename the auto-generated first submenu to "Dashboard".
	add_submenu_page(
		'cn-starter',
		__( 'Dashboard', 'cn-starter' ),
		__( 'Dashboard', 'cn-starter' ),
		'manage_options',
		'cn-starter',
		'cn_dashboard_render'
	);
}
add_action( 'admin_menu', 'cn_admin_top_menu', 5 );

/**
 * Force redirect to the setup wizard when setup is not complete.
 * Any CN Starter admin page (except the wizard itself) redirects to the wizard.
 */
function cn_require_setup_wizard() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Setup is complete — no redirect needed.
	if ( get_option( 'cn_setup_complete' ) ) {
		return;
	}

	// Only act on CN Starter admin pages.
	$page = $_GET['page'] ?? '';
	if ( ! $page || ! str_starts_with( $page, 'cn-' ) ) {
		return;
	}

	// Don't redirect the wizard itself.
	if ( 'cn-setup-wizard' === $page ) {
		return;
	}

	// Don't redirect AJAX requests.
	if ( wp_doing_ajax() ) {
		return;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard' ) );
	exit;
}
add_action( 'admin_init', 'cn_require_setup_wizard', 1 );

/**
 * Render the CN Starter dashboard page.
 */
function cn_dashboard_render() {
	$setup_complete = get_option( 'cn_setup_complete' );

	// If setup is not complete, show a minimal "run the wizard" screen.
	if ( ! $setup_complete ) {
		?>
		<div class="wrap cn-dashboard">
			<div class="cn-dashboard__header">
				<div class="cn-dashboard__brand">
					<img src="<?php echo esc_url( CN_THEME_URI . '/assets/images/cn-mark.svg' ); ?>" alt="CN" class="cn-dashboard__logo">
					<div>
						<h1>Chernoff Newman <span>CN Starter</span></h1>
						<p>Agency WordPress Theme Boilerplate</p>
					</div>
				</div>
				<div class="cn-dashboard__version">v<?php echo esc_html( CN_THEME_VERSION ); ?></div>
			</div>

			<div class="cn-dashboard__not-setup">
				<div class="cn-dashboard__not-setup-card">
					<span class="dashicons dashicons-admin-tools"></span>
					<h2><?php esc_html_e( 'Welcome to CN Starter', 'cn-starter' ); ?></h2>
					<p><?php esc_html_e( 'Run the setup wizard to configure your theme, sync ACF field groups, and create starter pages.', 'cn-starter' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-setup-wizard' ) ); ?>" class="button button-primary button-hero">
						<?php esc_html_e( 'Start Setup Wizard', 'cn-starter' ); ?> &rarr;
					</a>
				</div>
			</div>
		</div>
		<?php
		return;
	}

	$build_mode     = cn_get_build_mode();
	$plugins        = cn_plugin_statuses();
	$active_count   = count( array_filter( $plugins, static fn( $p ) => $p['active'] ) );
	$total_count    = count( $plugins );
	$has_tokens     = get_option( 'cn_design_tokens_css' );
	$colors         = cn_design_tokens_quick_colors();
	$page_on_front  = get_option( 'page_on_front' );
	$blocks         = glob( CN_THEME_DIR . '/blocks/*', GLOB_ONLYDIR );
	$block_count    = count( array_filter( $blocks, static fn( $d ) => '_template' !== basename( $d ) ) );

	// Check ACF field group sync status.
	$acf_needs_sync = 0;
	$json_dir = CN_THEME_DIR . '/acf-json';
	// Get all DB field group keys for a reliable check.
	$db_groups = array();
	$db_posts  = get_posts( array(
		'post_type'      => 'acf-field-group',
		'numberposts'    => -1,
		'post_status'    => 'any',
		'fields'         => array( 'ID', 'post_name', 'post_modified' ),
	) );
	foreach ( $db_posts as $post ) {
		$db_groups[ $post->post_name ] = $post;
	}
	if ( is_dir( $json_dir ) ) {
		$json_files = scandir( $json_dir );
		foreach ( $json_files as $filename ) {
			if ( $filename[0] === '.' ) {
				continue;
			}
			$file = $json_dir . '/' . $filename;
			if ( is_dir( $file ) || pathinfo( $filename, PATHINFO_EXTENSION ) !== 'json' ) {
				continue;
			}
			$json = json_decode( file_get_contents( $file ), true );
			if ( ! is_array( $json ) || ! isset( $json['key'] ) ) {
				continue;
			}
			// Check DB directly — ACF's cache may be stale.
			if ( ! isset( $db_groups[ $json['key'] ] ) ) {
				$acf_needs_sync++;
				continue;
			}
			$db_post = $db_groups[ $json['key'] ];
			if ( isset( $json['modified'] ) && (int) $json['modified'] > (int) strtotime( $db_post->post_modified ) ) {
				$acf_needs_sync++;
			}
		}
	}
	?>
	<div class="wrap cn-dashboard">
		<?php cn_admin_page_header( __( 'CN Starter', 'cn-starter' ), __( 'Chernoff Newman — Agency WordPress Theme Boilerplate', 'cn-starter' ) ); ?>

		<div class="cn-dashboard__grid">
			<!-- Setup Status -->
			<div class="cn-card cn-card--status is-complete">
				<div class="cn-card__icon">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Setup Complete', 'cn-starter' ); ?></h3>
					<p><?php esc_html_e( 'Your theme is configured and ready.', 'cn-starter' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-setup-wizard' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Re-run Wizard', 'cn-starter' ); ?> &rarr;</a>
				</div>
			</div>

			<!-- Build Mode -->
			<div class="cn-card">
				<div class="cn-card__icon"><span class="dashicons dashicons-layout"></span></div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Build Mode', 'cn-starter' ); ?></h3>
					<p><?php echo 'blocks' === $build_mode ? esc_html__( 'Blocks (WordPress Editor)', 'cn-starter' ) : esc_html__( 'Flexible Layouts (ACF)', 'cn-starter' ); ?></p>
				</div>
			</div>

			<!-- Plugins -->
			<div class="cn-card">
				<div class="cn-card__icon"><span class="dashicons dashicons-admin-plugins"></span></div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Plugins', 'cn-starter' ); ?></h3>
					<p><?php echo esc_html( sprintf( __( '%d of %d active', 'cn-starter' ), $active_count, $total_count ) ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-setup-wizard&step=1' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Manage plugins', 'cn-starter' ); ?> &rarr;</a>
				</div>
			</div>

			<!-- Design Tokens -->
			<div class="cn-card">
				<div class="cn-card__icon"><span class="dashicons dashicons-art"></span></div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Design Tokens', 'cn-starter' ); ?></h3>
					<div class="cn-card__swatches">
						<span style="background:<?php echo esc_attr( $colors['primary'] ); ?>"></span>
						<span style="background:<?php echo esc_attr( $colors['secondary'] ); ?>"></span>
						<span style="background:<?php echo esc_attr( $colors['accent'] ); ?>"></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-design-tokens' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Edit colors', 'cn-starter' ); ?> &rarr;</a>
				</div>
			</div>

			<!-- Blocks -->
			<div class="cn-card">
				<div class="cn-card__icon"><span class="dashicons dashicons-block-default"></span></div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Blocks', 'cn-starter' ); ?></h3>
					<p><?php echo esc_html( sprintf( _n( '%d block installed', '%d blocks installed', $block_count, 'cn-starter' ), $block_count ) ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-block-generator' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Create new block', 'cn-starter' ); ?> &rarr;</a>
				</div>
			</div>

			<!-- ACF Field Sync -->
			<div class="cn-card <?php echo $acf_needs_sync > 0 ? 'is-pending' : 'is-complete'; ?>">
				<div class="cn-card__icon">
					<?php if ( $acf_needs_sync > 0 ) : ?>
						<span class="dashicons dashicons-warning"></span>
					<?php else : ?>
						<span class="dashicons dashicons-yes-alt"></span>
					<?php endif; ?>
				</div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'ACF Field Sync', 'cn-starter' ); ?></h3>
					<p><?php echo $acf_needs_sync > 0 ? esc_html( sprintf( _n( '%d field group needs sync', '%d field groups need sync', $acf_needs_sync, 'cn-starter' ), $acf_needs_sync ) ) : esc_html__( 'All field groups synced', 'cn-starter' ); ?></p>
					<?php if ( $acf_needs_sync > 0 ) : ?>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cn-starter&cn_action=sync_acf' ), 'cn_sync_acf' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Sync now', 'cn-starter' ); ?> &rarr;</a>
					<?php else : ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acf-field-group' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Manage field groups', 'cn-starter' ); ?> &rarr;</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Figma Import -->
			<div class="cn-card">
				<div class="cn-card__icon"><span class="dashicons dashicons-format-image"></span></div>
				<div class="cn-card__body">
					<h3><?php esc_html_e( 'Figma Tokens', 'cn-starter' ); ?></h3>
					<p><?php echo $has_tokens ? esc_html__( 'Token overrides active', 'cn-starter' ) : esc_html__( 'No overrides — using defaults', 'cn-starter' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>" class="cn-card__link"><?php esc_html_e( 'Import tokens', 'cn-starter' ); ?> &rarr;</a>
				</div>
			</div>
		</div>

		<!-- Quick Links -->
		<div class="cn-dashboard__quick-links">
			<h2><?php esc_html_e( 'Quick Links', 'cn-starter' ); ?></h2>
			<div class="cn-dashboard__link-grid">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-setup-wizard' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-admin-tools"></span>
					<span>Setup Wizard</span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-theme-settings' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-admin-generic"></span>
					<span>Theme Settings</span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-design-tokens' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-art"></span>
					<span>Design Tokens</span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-format-image"></span>
					<span>Figma Tokens</span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-block-generator' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-plus-alt"></span>
					<span>Create Block</span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-docs' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-book"></span>
					<span>Theme Docs</span>
				</a>
				<?php if ( $page_on_front ) : ?>
				<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $page_on_front . '&action=edit' ) ); ?>" class="cn-quick-link">
					<span class="dashicons dashicons-edit-page"></span>
					<span>Edit Homepage</span>
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
