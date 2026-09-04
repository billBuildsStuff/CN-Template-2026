<?php
/**
 * ACF configuration: JSON sync, options page, sync notices.
 *
 * Field groups live in /acf-json/ (version-controlled source of truth).
 * ACF auto-detects that folder; we also add an admin notice when the
 * database copy is out of sync with the JSON on disk.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tell ACF to save field group JSON exports into the theme's acf-json folder.
 */
function cn_acf_json_save_point( $path ) {
	return CN_THEME_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'cn_acf_json_save_point' );

/**
 * Tell ACF to load field group JSON from the theme's acf-json folder.
 */
function cn_acf_json_load_point( $paths ) {
	$paths[] = CN_THEME_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'cn_acf_json_load_point' );

/**
 * Get the current build mode.
 *
 * 'blocks'   — ACF blocks in the WordPress editor (default).
 * 'flexible' — ACF Flexible Content page builder via template-flexible.php.
 *
 * @return string 'blocks' or 'flexible'.
 */
function cn_get_build_mode() {
	$mode = get_option( 'cn_build_mode', 'blocks' );
	return in_array( $mode, array( 'blocks', 'flexible' ), true ) ? $mode : 'blocks';
}

/**
 * Theme Settings options page (site-wide fields: build mode, header CTA, footer content, socials).
 */
function cn_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_sub_page( array(
		'page_title'  => __( 'Theme Settings', 'cn-starter' ),
		'menu_title'  => __( 'Theme Settings', 'cn-starter' ),
		'menu_slug'   => 'cn-theme-settings',
		'parent_slug' => 'cn-starter',
		'capability'  => 'manage_options',
	) );
}
add_action( 'acf/init', 'cn_acf_options_page' );

/**
 * Register Build Mode field on the Theme Settings options page.
 */
function cn_register_build_mode_field() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_cn_build_mode',
		'title'    => __( 'Build Mode', 'cn-starter' ),
		'fields'   => array(
			array(
				'key'           => 'field_cn_build_mode',
				'label'         => __( 'Page Building Method', 'cn-starter' ),
				'name'          => 'cn_build_mode',
				'type'          => 'select',
				'instructions'  => __( "Lock the site to one editing approach. 'Blocks' uses the WordPress block editor with CN custom blocks. 'Flexible Layouts' uses a page-builder-style ACF Flexible Content field on every page.", 'cn-starter' ),
				'required'      => true,
				'choices'       => array(
					'blocks'   => __( 'Blocks (WordPress Editor)', 'cn-starter' ),
					'flexible' => __( 'Flexible Layouts (ACF Page Builder)', 'cn-starter' ),
				),
				'default_value' => cn_get_build_mode(),
				'return_format' => 'value',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'cn-theme-settings',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	) );
}
add_action( 'acf/init', 'cn_register_build_mode_field' );

/**
 * Sync the ACF options field back to the wp_options table so
 * cn_get_build_mode() can read it without ACF being loaded.
 */
function cn_sync_build_mode_option( $value, $post_id, $field ) {
	if ( 'field_cn_build_mode' === $field['key'] ) {
		update_option( 'cn_build_mode', sanitize_key( $value ) );
	}
	return $value;
}
add_filter( 'acf/update_value', 'cn_sync_build_mode_option', 10, 3 );

/**
 * Remove the Flexible Layout page template when in blocks mode.
 * When in flexible mode, force all pages to use the flexible template.
 *
 * @param array    $templates     Page templates.
 * @return array Filtered templates.
 */
function cn_filter_page_templates( $templates ) {
	$mode = cn_get_build_mode();

	if ( 'blocks' === $mode ) {
		unset( $templates['template-flexible.php'] );
	}

	return $templates;
}
add_filter( 'theme_page_templates', 'cn_filter_page_templates' );

/**
 * Force the flexible template on all pages when in flexible mode.
 *
 * @param string  $template  Path to the template file.
 * @return string Filtered template path.
 */
function cn_force_flexible_template( $template ) {
	if ( cn_get_build_mode() !== 'flexible' ) {
		return $template;
	}

	if ( is_page() && file_exists( CN_THEME_DIR . '/template-flexible.php' ) ) {
		return CN_THEME_DIR . '/template-flexible.php';
	}

	return $template;
}
add_filter( 'template_include', 'cn_force_flexible_template' );

/**
 * Warn admins when ACF field groups on disk differ from the database.
 * Keeps acf-json/ as the source of truth across environments.
 */
function cn_acf_sync_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && 'acf-field-group' === $screen->post_type ) {
		return; // ACF shows its own sync UI here.
	}

	// Scan the JSON directory directly — ACF's cached file list may be stale
	// after a reset or sync. Query the DB directly for the same reason.
	$json_dir = CN_THEME_DIR . '/acf-json';
	if ( ! is_dir( $json_dir ) ) {
		return;
	}

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

	$needs_sync = 0;
	$files      = scandir( $json_dir );

	foreach ( $files as $filename ) {
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
		// Check DB directly — ACF's cache may show local JSON groups with ID=0.
		if ( ! isset( $db_groups[ $json['key'] ] ) ) {
			$needs_sync++;
			continue;
		}
		$db_post = $db_groups[ $json['key'] ];
		if ( isset( $json['modified'] ) && (int) $json['modified'] > (int) strtotime( $db_post->post_modified ) ) {
			$needs_sync++;
		}
	}

	if ( $needs_sync > 0 ) {
		printf(
			'<div class="notice notice-warning"><p><strong>CN Starter:</strong> %s <a href="%s">%s</a></p></div>',
			esc_html( sprintf(
				/* translators: %d: number of field groups */
				_n( '%d ACF field group needs to be synced from the theme.', '%d ACF field groups need to be synced from the theme.', $needs_sync, 'cn-starter' ),
				$needs_sync
			) ),
			esc_url( admin_url( 'edit.php?post_type=acf-field-group&post_status=sync' ) ),
			esc_html__( 'Sync now', 'cn-starter' )
		);
	}
}
add_action( 'admin_notices', 'cn_acf_sync_notice' );

/**
 * Google Maps API key passthrough for ACF map fields.
 * Define CN_GOOGLE_MAPS_KEY in wp-config.php to enable.
 */
function cn_acf_google_maps_key( $api ) {
	if ( defined( 'CN_GOOGLE_MAPS_KEY' ) ) {
		$api['key'] = CN_GOOGLE_MAPS_KEY;
	}
	return $api;
}
add_filter( 'acf/fields/google_map/api', 'cn_acf_google_maps_key' );

/**
 * Auto-sync ACF JSON field groups into the database.
 *
 * Scans the /acf-json/ directory and imports any field groups that are
 * missing from the database or whose JSON modified timestamp is newer
 * than the stored post. This runs on theme activation and can be
 * triggered manually from the dashboard.
 *
 * @return int Number of field groups synced.
 */
function cn_acf_auto_sync() {
	if ( ! function_exists( 'acf_import_field_group' ) ) {
		return 0;
	}

	// Scan the acf-json directory directly — ACF's cached file list may be stale
	// after a reset (files were deleted by ACF hooks and restored by our code).
	$json_dir  = CN_THEME_DIR . '/acf-json';
	$synced    = 0;
	$json_data = array();

	if ( is_dir( $json_dir ) ) {
		$files = scandir( $json_dir );
		foreach ( $files as $filename ) {
			if ( $filename[0] === '.' ) {
				continue;
			}
			$file = $json_dir . '/' . $filename;
			if ( is_dir( $file ) ) {
				continue;
			}
			if ( pathinfo( $filename, PATHINFO_EXTENSION ) !== 'json' ) {
				continue;
			}
			$json = json_decode( file_get_contents( $file ), true );
			if ( ! is_array( $json ) || ! isset( $json['key'] ) ) {
				continue;
			}
			$json_data[ $json['key'] ] = $file;
		}
	}

	if ( empty( $json_data ) ) {
		return 0;
	}

	// Save file contents so we can restore them after sync (ACF deletes files during import).
	$json_backups = array();
	foreach ( $json_data as $key => $file ) {
		$json_backups[ $key ] = file_get_contents( $file );
	}

	// Build a map of existing DB field groups by key for reliable checking.
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

	foreach ( $json_data as $key => $file ) {
		$json = json_decode( file_get_contents( $file ), true );
		if ( ! $json ) {
			continue;
		}

		// Check DB directly — ACF's cache may show local JSON groups with ID=0.
		if ( ! isset( $db_groups[ $key ] ) ) {
			// Not in DB — import it.
			acf_import_field_group( $json );
			$synced++;
			continue;
		}

		// Check if JSON is newer than the DB post.
		$db_post = $db_groups[ $key ];
		if ( isset( $json['modified'] ) && (int) $json['modified'] > (int) strtotime( $db_post->post_modified ) ) {
			$json['ID'] = $db_post->ID;
			acf_import_field_group( $json );
			$synced++;
		}
	}

	// Restore JSON files that were deleted during sync.
	foreach ( $json_backups as $key => $content ) {
		$file = CN_THEME_DIR . '/acf-json/' . $key . '.json';
		if ( ! file_exists( $file ) && $content ) {
			file_put_contents( $file, $content );
		}
	}

	return $synced;
}

/**
 * Run auto-sync on theme activation (after_switch_theme).
 * Note: This only runs when the theme is first activated, not on every load.
 * For the wizard flow, sync is triggered in cn_wizard_create_starter_content().
 */
function cn_acf_sync_on_activation() {
	if ( function_exists( 'acf_get_local_json_files' ) ) {
		cn_acf_auto_sync();
	}
}
add_action( 'after_switch_theme', 'cn_acf_sync_on_activation' );

/**
 * Handle manual ACF sync trigger from the dashboard.
 */
function cn_handle_manual_acf_sync() {
	if ( ! isset( $_GET['cn_action'] ) || 'sync_acf' !== $_GET['cn_action'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_sync_acf' );

	$synced = cn_acf_auto_sync();
	set_transient( 'cn_acf_sync_result', $synced, 60 );

	wp_safe_redirect( remove_query_arg( array( 'cn_action', '_wpnonce' ), wp_get_referer() ) );
	exit;
}
add_action( 'admin_init', 'cn_handle_manual_acf_sync' );

/**
 * Show sync result notice.
 */
function cn_acf_sync_result_notice() {
	$synced = get_transient( 'cn_acf_sync_result' );
	if ( false === $synced ) {
		return;
	}
	delete_transient( 'cn_acf_sync_result' );

	if ( $synced > 0 ) {
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>CN Starter:</strong> %s</p></div>',
			esc_html( sprintf(
				_n( '%d ACF field group synced successfully.', '%d ACF field groups synced successfully.', $synced, 'cn-starter' ),
				$synced
			) )
		);
	} else {
		echo '<div class="notice notice-info is-dismissible"><p><strong>CN Starter:</strong> ' . esc_html__( 'All ACF field groups are already up to date.', 'cn-starter' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'cn_acf_sync_result_notice' );

/**
 * Admin notice on pages when build mode is flexible but the page
 * is not using the flexible template (edge case after switching modes).
 */
function cn_build_mode_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	$mode = cn_get_build_mode();
	if ( 'flexible' === $mode ) {
		echo '<div class="notice notice-info"><p><strong>CN Starter:</strong> ' . esc_html__( 'Build mode is set to Flexible Layouts. All pages automatically use the Flexible Layout template.', 'cn-starter' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'cn_build_mode_admin_notice' );

/**
 * Handle "Reset All Settings" action from Theme Settings.
 * Performs a full wipe: deletes all CN-created pages, flexible layout
 * meta, nav menus, and all theme options. Returns to a fresh install state.
 * WP core pages (Privacy Policy, Sample Page) are preserved.
 */
function cn_handle_reset_setup() {
	if ( ! isset( $_GET['cn_action'] ) || 'reset_setup' !== $_GET['cn_action'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_reset_setup' );

	// Delete all CN-created pages (preserve WP core pages: Privacy Policy, Sample Page).
	$cn_pages = get_posts( array(
		'post_type'   => 'page',
		'numberposts' => -1,
		'post_status' => 'any',
		'exclude'     => array( 2, 3 ), // WP core Sample Page & Privacy Policy.
	) );
	foreach ( $cn_pages as $page ) {
		// Delete all flexible layout meta for this page.
		delete_post_meta( $page->ID, 'cn_layouts' );
		delete_post_meta( $page->ID, '_cn_layouts' );
		$all_meta = get_post_meta( $page->ID );
		foreach ( $all_meta as $key => $vals ) {
			if ( strpos( $key, 'cn_layouts_' ) === 0 ) {
				delete_post_meta( $page->ID, $key );
			}
		}
		// Trash the page (not force-delete, so it can be recovered if needed).
		wp_trash_post( $page->ID );
	}

	// Delete the Primary nav menu if it exists.
	$menu = wp_get_nav_menu_object( 'Primary' );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}

	// Reset homepage display to default (posts).
	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
	update_option( 'page_for_posts', 0 );

	// Delete all theme options.
	delete_option( 'cn_setup_complete' );
	delete_option( 'cn_wizard_step' );
	delete_option( 'cn_build_mode' );
	delete_option( 'cn_site_type' );
	delete_option( 'cn_design_tokens_css' );
	delete_option( 'cn_design_tokens_json' );
	delete_option( 'cn_design_tokens_quick' );

	// Delete ACF field group posts so the wizard shows the sync prompt.
	// ACF deletes JSON files from disk when field group posts are deleted,
	// so back up the JSON content and restore it after deletion.
	if ( function_exists( 'acf_get_local_json_files' ) ) {
		$json_dir     = CN_THEME_DIR . '/acf-json';
		$json_backups = array();
		if ( is_dir( $json_dir ) ) {
			$files = scandir( $json_dir );
			foreach ( $files as $filename ) {
				if ( $filename[0] === '.' ) {
					continue;
				}
				$file = $json_dir . '/' . $filename;
				if ( is_dir( $file ) || pathinfo( $filename, PATHINFO_EXTENSION ) !== 'json' ) {
					continue;
				}
				$content = file_get_contents( $file );
				$json    = json_decode( $content, true );
				if ( isset( $json['key'] ) ) {
					$json_backups[ $json['key'] ] = $content;
				}
			}
		}

		// Delete all ACF field group posts directly from DB.
		$db_groups = get_posts( array(
			'post_type'      => 'acf-field-group',
			'numberposts'    => -1,
			'post_status'    => 'any',
			'fields'         => array( 'ID' ),
		) );
		foreach ( $db_groups as $post ) {
			wp_delete_post( $post->ID, true );
		}

		// Restore JSON files that were deleted.
		foreach ( $json_backups as $key => $content ) {
			$file = CN_THEME_DIR . '/acf-json/' . $key . '.json';
			if ( ! file_exists( $file ) && $content ) {
				file_put_contents( $file, $content );
			}
		}
	}

	// Redirect to the setup wizard.
	set_transient( 'cn_setup_wizard_redirect', 1, 30 );

	wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard' ) );
	exit;
}
add_action( 'admin_init', 'cn_handle_reset_setup' );

/**
 * Show a settings summary panel on the Theme Settings page
 * with build mode info, wizard link, and a confirmed full-wipe reset.
 */
function cn_build_mode_settings_notice() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'admin_page_cn-theme-settings', 'cn-starter_page_cn-theme-settings' ), true ) ) {
		return;
	}

	$mode       = cn_get_build_mode();
	$mode_lbl   = 'flexible' === $mode ? __( 'Flexible Layouts', 'cn-starter' ) : __( 'Blocks', 'cn-starter' );
	$site_type  = get_option( 'cn_site_type', 'full' );
	$site_lbl   = 'landing' === $site_type ? __( 'Landing Page', 'cn-starter' ) : __( 'Full Site', 'cn-starter' );
	$wizard     = admin_url( 'admin.php?page=cn-setup-wizard' );
	$reset      = wp_nonce_url( admin_url( '?cn_action=reset_setup' ), 'cn_reset_setup' );

	// Branded page header.
	if ( function_exists( 'cn_admin_page_header' ) ) {
		cn_admin_page_header( __( 'Theme Settings', 'cn-starter' ), __( 'Configure build mode, content conversion, and reset options.', 'cn-starter' ) );
	}
	// Add CN wrapper class to the ACF options page .wrap element.
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var wrap = document.querySelector('.wrap.acf-settings-wrap');
		if (wrap) { wrap.classList.add('cn-settings-wrap'); }
	});
	</script>

	<div class="cn-settings-panel">
		<div class="cn-settings-panel__header">
			<h2><?php esc_html_e( 'CN Starter Theme', 'cn-starter' ); ?></h2>
			<span class="cn-settings-panel__badge cn-settings-panel__badge--<?php echo esc_attr( $mode ); ?>"><?php echo esc_html( $mode_lbl ); ?></span>
			<span class="cn-settings-panel__badge cn-settings-panel__badge--<?php echo esc_attr( $site_type ); ?>"><?php echo esc_html( $site_lbl ); ?></span>
		</div>
		<p class="cn-settings-panel__desc">
			<?php esc_html_e( 'Current build mode:', 'cn-starter' ); ?>
			<code><?php echo esc_html( $mode_lbl ); ?></code>
			&middot;
			<?php esc_html_e( 'Site type:', 'cn-starter' ); ?>
			<code><?php echo esc_html( $site_lbl ); ?></code>
		</p>
		<div class="cn-settings-panel__actions">
			<a href="<?php echo esc_url( $wizard ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-admin-tools"></span>
				<?php esc_html_e( 'Re-run Setup Wizard', 'cn-starter' ); ?>
			</a>
			<button type="button" class="button button-link-delete" id="cn-reset-all">
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e( 'Reset All Settings', 'cn-starter' ); ?>
			</button>
		</div>
	</div>

	<div id="cn-reset-dialog" class="cn-reset-dialog" style="display:none">
		<div class="cn-reset-dialog__content">
			<h3><?php esc_html_e( 'Reset All Settings', 'cn-starter' ); ?></h3>
			<p class="cn-reset-dialog__warning">
				<span class="dashicons dashicons-warning" style="color:#c62828;font-size:24px;width:24px;height:24px;vertical-align:middle"></span>
				<?php esc_html_e( 'This will permanently delete:', 'cn-starter' ); ?>
			</p>
			<ul class="cn-reset-dialog__list">
				<li><?php esc_html_e( 'All CN-created pages (Home, About, Contact, Blog, etc.)', 'cn-starter' ); ?></li>
				<li><?php esc_html_e( 'All flexible layout data (cn_layouts meta)', 'cn-starter' ); ?></li>
				<li><?php esc_html_e( 'The Primary navigation menu', 'cn-starter' ); ?></li>
				<li><?php esc_html_e( 'All design tokens and Figma token imports', 'cn-starter' ); ?></li>
				<li><?php esc_html_e( 'Build mode setting, site type, and setup completion flag', 'cn-starter' ); ?></li>
			</ul>
			<p class="cn-reset-dialog__note">
				<?php esc_html_e( 'WP core pages (Privacy Policy, Sample Page) are preserved. Pages are trashed (not permanently deleted) so they can be recovered if needed.', 'cn-starter' ); ?>
			</p>
			<p class="cn-reset-dialog__note">
				<?php esc_html_e( 'Custom block files in /blocks/ are preserved — these are theme code, not content. Delete manually if no longer needed.', 'cn-starter' ); ?>
			</p>
			<p class="cn-reset-dialog__confirm">
				<label>
					<input type="checkbox" id="cn-reset-confirm">
					<?php esc_html_e( 'I understand this action cannot be undone from the wizard.', 'cn-starter' ); ?>
				</label>
			</p>
			<p class="cn-reset-dialog__buttons">
				<a href="<?php echo esc_url( $reset ); ?>" class="button button-link-delete" id="cn-reset-proceed" style="display:none">
					<?php esc_html_e( 'Yes, Reset Everything', 'cn-starter' ); ?>
				</a>
				<button type="button" class="button" id="cn-reset-cancel"><?php esc_html_e( 'Cancel', 'cn-starter' ); ?></button>
			</p>
		</div>
	</div>

	<script>
	jQuery( function ( $ ) {
		$( '#cn-reset-all' ).on( 'click', function () {
			$( '#cn-reset-dialog' ).show();
		} );
		$( '#cn-reset-cancel' ).on( 'click', function () {
			$( '#cn-reset-dialog' ).hide();
			$( '#cn-reset-confirm' ).prop( 'checked', false );
			$( '#cn-reset-proceed' ).hide();
		} );
		$( '#cn-reset-confirm' ).on( 'change', function () {
			$( '#cn-reset-proceed' ).toggle( this.checked );
		} );
	} );
	</script>

	<style>
	.cn-reset-dialog { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center; }
	.cn-reset-dialog__content { background:#161b22;border:1px solid #30363d;border-radius:10px;padding:32px;max-width:520px;box-shadow:0 8px 32px rgba(0,0,0,.4);color:#e6edf3; }
	.cn-reset-dialog__content h3 { margin:0 0 16px;color:#f0f6fc; }
	.cn-reset-dialog__warning { font-weight:600;margin-bottom:8px;color:#f85149; }
	.cn-reset-dialog__list { margin:0 0 16px 24px;color:#c9d1d9; }
	.cn-reset-dialog__list li { margin-bottom:4px; }
	.cn-reset-dialog__note { color:#8b949e;font-size:13px;margin-bottom:16px; }
	.cn-reset-dialog__confirm { margin-bottom:16px; }
	.cn-reset-dialog__confirm label { cursor:pointer;color:#c9d1d9; }
	.cn-reset-dialog__buttons { display:flex;gap:12px; }
	</style>
	<?php
}
add_action( 'admin_notices', 'cn_build_mode_settings_notice' );
