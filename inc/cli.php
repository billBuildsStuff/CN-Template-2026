<?php
/**
 * WP-CLI commands for CN Starter theme.
 *
 * Usage:
 *   wp cn block create <slug> "<Title>" --description="..." --icon=...
 *   wp cn plugins install [--acf-pro=<url>] [--gravity-forms=<url>] [--acf-extended-pro=<url>] [--admin-columns-pro=<url>]
 *
 * @package cn-starter
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

WP_CLI::add_command( 'cn block', 'CN_Block_CLI_Command' );
WP_CLI::add_command( 'cn plugins', 'CN_Plugins_CLI_Command' );

/**
 * CN Starter block commands.
 */
class CN_Block_CLI_Command {

	/**
	 * Create a new ACF block from the theme template.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : Block slug in kebab-case (e.g. logo-wall).
	 *
	 * <title>
	 * : Human-readable block title (e.g. "Logo Wall").
	 *
	 * [--description=<description>]
	 * : Block description shown in the inserter.
	 *
	 * [--icon=<icon>]
	 * : Dashicon name (default: smiley). See https://developer.wordpress.org/resource/dashicons/
	 *
	 * ## EXAMPLES
	 *
	 *     wp cn block create logo-wall "Logo Wall" --description="Grid of client logos" --icon=grid-view
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function create( $args, $assoc_args ) {
		list( $slug, $title ) = $args;

		$desc = $assoc_args['description'] ?? '';
		$icon = $assoc_args['icon'] ?? 'smiley';

		$result = cn_scaffold_block( $slug, $title, $desc, $icon );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( sprintf(
			"Block created:\n  /blocks/%s/block.json\n  /blocks/%s/render.php\n  /blocks/%s/style.css\n  /acf-json/group_cn_%s.json\n\nNext: sync the field group in WP admin → Custom Fields, then edit render.php and style.css.",
			$slug, $slug, $slug, str_replace( '-', '_', $slug )
		) );
	}
}

/**
 * CN Starter plugin management commands.
 */
class CN_Plugins_CLI_Command {

	/**
	 * Install required/recommended plugins.
	 *
	 * Free plugins (Yoast SEO, ACF Extended free) are installed from wordpress.org.
	 * Premium plugins require a ZIP URL passed as a flag.
	 *
	 * ## OPTIONS
	 *
	 * [--acf-pro=<url>]
	 * : ZIP download URL for ACF Pro.
	 *
	 * [--gravity-forms=<url>]
	 * : ZIP download URL for Gravity Forms.
	 *
	 * [--admin-columns-pro=<url>]
	 * : ZIP download URL for Admin Columns Pro.
	 *
	 * [--skip-free]
	 * : Skip installing free plugins from wordpress.org.
	 *
	 * ## EXAMPLES
	 *
	 *     # Install all free plugins
	 *     wp cn plugins install
	 *
	 *     # Install everything including premium via ZIP URLs
	 *     wp cn plugins install --acf-pro=https://example.com/acf-pro.zip --gravity-forms=https://example.com/gf.zip
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function install( $args, $assoc_args ) {
		$plugins    = cn_required_plugins();
		$skip_free  = isset( $assoc_args['skip-free'] );
		$premium_map = array(
			'advanced-custom-fields-pro' => $assoc_args['acf-pro'] ?? '',
			'gravityforms'               => $assoc_args['gravity-forms'] ?? '',
			'admin-columns-pro'          => $assoc_args['admin-columns-pro'] ?? '',
		);

		$installed_count = 0;
		$skipped_count   = 0;
		$failed_count    = 0;

		foreach ( $plugins as $plugin ) {
			$is_active = call_user_func( $plugin['check'] );

			if ( $is_active ) {
				WP_CLI::log( "  ✓ {$plugin['name']} — already active" );
				$skipped_count++;
				continue;
			}

			WP_CLI::log( "  → Installing {$plugin['name']}…" );

			if ( $plugin['wporg'] && ! $skip_free ) {
				$result = cn_install_plugin_wporg( $plugin['slug'] );
			} elseif ( ! $plugin['wporg'] && ! empty( $premium_map[ $plugin['slug'] ] ) ) {
				$result = cn_install_plugin_from_zip( $premium_map[ $plugin['slug'] ] );
				if ( ! is_wp_error( $result ) ) {
					$result = cn_activate_plugin_by_slug( $plugin['slug'] );
				}
			} elseif ( ! $plugin['wporg'] ) {
				WP_CLI::warning( "    ⚠ {$plugin['name']} is premium — pass --{$this->slug_to_flag( $plugin['slug'] )}=<zip-url>" );
				$skipped_count++;
				continue;
			} else {
				WP_CLI::log( "    Skipped (--skip-free)" );
				$skipped_count++;
				continue;
			}

			if ( is_wp_error( $result ) ) {
				WP_CLI::warning( "    ✗ {$plugin['name']}: " . $result->get_error_message() );
				$failed_count++;
			} else {
				WP_CLI::success( "    ✓ {$plugin['name']} installed and activated" );
				$installed_count++;
			}
		}

		WP_CLI::log( "" );
		WP_CLI::log( sprintf( "Done: %d installed, %d skipped, %d failed.", $installed_count, $skipped_count, $failed_count ) );

		if ( $failed_count > 0 ) {
			WP_CLI::error( 'Some plugins failed to install.' );
		}
	}

	/**
	 * Convert a plugin slug to a CLI flag name.
	 *
	 * @param string $slug Plugin slug.
	 * @return string
	 */
	private function slug_to_flag( $slug ) {
		$map = array(
			'advanced-custom-fields-pro' => 'acf-pro',
			'gravityforms'               => 'gravity-forms',
			'admin-columns-pro'          => 'admin-columns-pro',
		);
		return $map[ $slug ] ?? str_replace( '_', '-', $slug );
	}
}
