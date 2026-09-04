<?php
/**
 * Plugin dependency checker.
 *
 * WP's native `Requires Plugins` header only works for plugins (not themes),
 * and most of our stack is premium (not on wordpress.org), so we surface a
 * clear admin notice with the status of each dependency instead.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * The plugin stack this theme is built around.
 *
 * @return array[]
 */
function cn_required_plugins() {
	return array(
		array(
			'name'     => 'ACF Pro',
			'slug'     => 'advanced-custom-fields-pro',
			'wporg'    => false,
			'url'      => 'https://www.advancedcustomfields.com/pro/',
			'check'    => static function () { return class_exists( 'ACF' ) && function_exists( 'acf_register_block_type' ); },
			'level'    => 'required',
			'note'     => __( 'Powers all CN blocks and the Theme Settings page.', 'cn-starter' ),
		),
		array(
			'name'     => 'ACF Extended',
			'slug'     => 'acf-extended',
			'wporg'    => true,
			'check'    => static function () { return class_exists( 'ACFE' ); },
			'level'    => 'recommended',
			'note'     => __( 'Enhanced field types and admin UX (free version on wp.org).', 'cn-starter' ),
		),
		array(
			'name'     => 'Yoast SEO',
			'slug'     => 'wordpress-seo',
			'wporg'    => true,
			'check'    => static function () { return defined( 'WPSEO_VERSION' ); },
			'level'    => 'recommended',
			'note'     => __( 'SEO management.', 'cn-starter' ),
		),
		array(
			'name'     => 'Gravity Forms',
			'slug'     => 'gravityforms',
			'wporg'    => false,
			'url'      => 'https://www.gravityforms.com/',
			'check'    => static function () { return class_exists( 'GFAPI' ); },
			'level'    => 'optional',
			'note'     => __( 'Needed by the Gravity Form block.', 'cn-starter' ),
		),
		array(
			'name'     => 'Admin Columns Pro',
			'slug'     => 'admin-columns-pro',
			'wporg'    => false,
			'url'      => 'https://www.admincolumns.com/',
			'check'    => static function () { return defined( 'ACP_FILE' ); },
			'level'    => 'optional',
			'note'     => __( 'Admin list table customization.', 'cn-starter' ),
		),
	);
}

/**
 * Get plugin statuses for display.
 *
 * @return array[]
 */
function cn_plugin_statuses() {
	return array_map(
		static function ( $plugin ) {
			$plugin['active'] = (bool) call_user_func( $plugin['check'] );
			unset( $plugin['check'] );
			return $plugin;
		},
		cn_required_plugins()
	);
}

/**
 * Install a plugin from wordpress.org by slug.
 *
 * @param string $slug Plugin slug on wordpress.org.
 * @return true|WP_Error
 */
function cn_install_plugin_wporg( $slug ) {
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	@set_time_limit( 300 );
	@ini_set( 'memory_limit', '512M' );

	WP_Filesystem();

	// Check if plugin is already installed — just activate it.
	$installed = get_plugins();
	foreach ( $installed as $path => $data ) {
		if ( strtok( $path, '/' ) === $slug ) {
			if ( is_plugin_active( $path ) ) {
				return true;
			}
			$result = activate_plugin( $path, '', false, true );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			return true;
		}
	}

	$api = plugins_api( 'plugin_information', array(
		'slug'   => $slug,
		'fields' => array( 'sections' => false ),
	) );

	if ( is_wp_error( $api ) ) {
		return new WP_Error( 'api_failed', sprintf( 'Could not fetch plugin info for "%s" from wordpress.org: %s', $slug, $api->get_error_message() ) );
	}

	$skin     = new Plugin_Installer_Skin( array( 'type' => 'web', 'title' => $api->name ) );
	$upgrader = new Plugin_Upgrader( $skin );

	ob_start();
	$result = $upgrader->install( $api->download_link );
	ob_end_clean();

	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( ! $result ) {
		return new WP_Error( 'install_failed', sprintf( 'Installation failed for "%s".', $slug ) );
	}

	return cn_activate_plugin_by_slug( $slug );
}

/**
 * Install a plugin from a ZIP URL (premium plugins).
 *
 * @param string $zip_url URL to a plugin ZIP file.
 * @return true|WP_Error
 */
function cn_install_plugin_from_zip( $zip_url ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php';

	WP_Filesystem();

	$skin     = new Plugin_Installer_Skin( array( 'type' => 'upload', 'title' => basename( $zip_url ) ) );
	$upgrader = new Plugin_Upgrader( $skin );

	ob_start();
	$result = $upgrader->install( $zip_url );
	ob_end_clean();

	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( ! $result ) {
		return new WP_Error( 'install_failed', sprintf( 'Installation failed from URL: %s', $zip_url ) );
	}

	return true;
}

/**
 * Activate a plugin by its wordpress.org slug.
 *
 * @param string $slug Plugin slug.
 * @return true|WP_Error
 */
function cn_activate_plugin_by_slug( $slug ) {
	$installed = get_plugins();
	$plugin_path = '';

	foreach ( $installed as $path => $data ) {
		if ( strtok( $path, '/' ) === $slug ) {
			$plugin_path = $path;
			break;
		}
	}

	if ( ! $plugin_path ) {
		return new WP_Error( 'not_found', sprintf( 'Plugin "%s" installed but could not be located for activation.', $slug ) );
	}

	$result = activate_plugin( $plugin_path, '', false, true );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return true;
}

/**
 * AJAX handler: install a plugin (free from wp.org or premium from ZIP URL).
 */
function cn_ajax_install_plugin() {
	check_ajax_referer( 'cn_install_plugin', 'nonce' );

	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}

	$slug    = sanitize_key( $_POST['slug'] ?? '' );
	$zip_url = isset( $_POST['zip_url'] ) ? esc_url_raw( wp_unslash( $_POST['zip_url'] ) ) : '';

	if ( ! $slug ) {
		wp_send_json_error( array( 'message' => 'Missing plugin slug.' ) );
	}

	$plugins = cn_required_plugins();
	$plugin  = null;
	foreach ( $plugins as $p ) {
		if ( $p['slug'] === $slug ) {
			$plugin = $p;
			break;
		}
	}

	if ( ! $plugin ) {
		wp_send_json_error( array( 'message' => 'Unknown plugin slug.' ) );
	}

	if ( $plugin['wporg'] ) {
		$result = cn_install_plugin_wporg( $slug );
	} elseif ( $zip_url ) {
		$result = cn_install_plugin_from_zip( $zip_url );
		if ( ! is_wp_error( $result ) ) {
			$result = cn_activate_plugin_by_slug( $slug );
		}
	} else {
		wp_send_json_error( array( 'message' => sprintf( '%s is a premium plugin — provide a ZIP download URL.', $plugin['name'] ) ) );
		return;
	}

	if ( is_wp_error( $result ) ) {
		error_log( '[CN Starter] Plugin install failed for ' . $slug . ': ' . $result->get_error_message() );
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => sprintf( '%s installed and activated.', $plugin['name'] ) ) );
}
add_action( 'wp_ajax_cn_install_plugin', 'cn_ajax_install_plugin' );

/**
 * Admin notice when a REQUIRED plugin is missing.
 */
function cn_plugin_checker_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$missing = array_filter(
		cn_plugin_statuses(),
		static function ( $plugin ) {
			return 'required' === $plugin['level'] && ! $plugin['active'];
		}
	);

	if ( ! $missing ) {
		return;
	}

	$names = wp_list_pluck( $missing, 'name' );
	printf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s <strong>%s</strong>. <a href="%s">%s</a></p></div>',
		esc_html__( 'CN Starter:', 'cn-starter' ),
		esc_html__( 'this theme requires', 'cn-starter' ),
		esc_html( implode( ', ', $names ) ),
		esc_url( admin_url( 'plugins.php' ) ),
		esc_html__( 'Manage plugins', 'cn-starter' )
	);
}
add_action( 'admin_notices', 'cn_plugin_checker_notice' );
