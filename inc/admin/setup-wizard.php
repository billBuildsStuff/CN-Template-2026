<?php
/**
 * Setup Wizard — guided onboarding for new projects.
 *
 * CN Starter → Setup Wizard, or auto-redirect on first activation.
 * Steps: Welcome/plugins → Site identity → Design tokens → Starter content → Done.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the wizard page (hidden from menu once complete).
 */
function cn_wizard_menu() {
	add_submenu_page(
		'cn-starter',
		__( 'Setup Wizard', 'cn-starter' ),
		__( 'Setup Wizard', 'cn-starter' ),
		'manage_options',
		'cn-setup-wizard',
		'cn_wizard_render'
	);
}
add_action( 'admin_menu', 'cn_wizard_menu' );

/**
 * Redirect to the wizard on first activation.
 */
function cn_wizard_activation_redirect() {
	if ( get_transient( 'cn_setup_wizard_redirect' ) && current_user_can( 'manage_options' ) ) {
		delete_transient( 'cn_setup_wizard_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard' ) );
		exit;
	}
}
add_action( 'admin_init', 'cn_wizard_activation_redirect' );

/**
 * Handle wizard step submissions.
 */
function cn_wizard_handle_submit() {
	if ( ! isset( $_POST['cn_wizard_step'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'cn_wizard' );

	$step = sanitize_key( $_POST['cn_wizard_step'] );
	$next = 2;

	switch ( $step ) {
		case 'welcome':
			$next = 2;
			update_option( 'cn_wizard_step', 1 );
			break;
		case 'build_mode':
			if ( isset( $_POST['cn_build_mode'] ) ) {
				update_option( 'cn_build_mode', sanitize_key( wp_unslash( $_POST['cn_build_mode'] ) ) );
			}
			$site_type = isset( $_POST['cn_site_type'] ) ? sanitize_key( wp_unslash( $_POST['cn_site_type'] ) ) : 'full';
			update_option( 'cn_site_type', in_array( $site_type, array( 'full', 'landing' ), true ) ? $site_type : 'full' );
			$next = 3;
			update_option( 'cn_wizard_step', 2 );
			break;

		case 'identity':
			if ( isset( $_POST['blogname'] ) ) {
				update_option( 'blogname', sanitize_text_field( wp_unslash( $_POST['blogname'] ) ) );
			}
			if ( isset( $_POST['blogdescription'] ) ) {
				update_option( 'blogdescription', sanitize_text_field( wp_unslash( $_POST['blogdescription'] ) ) );
			}
			if ( ! empty( $_POST['custom_logo'] ) ) {
				set_theme_mod( 'custom_logo', absint( $_POST['custom_logo'] ) );
			}
			$next = 4;
			update_option( 'cn_wizard_step', 3 );
			break;

		case 'tokens':
			// Manual quick colors — full import lives in CN Starter → Figma Tokens.
			$css = '';
			foreach ( array( 'primary', 'secondary', 'accent' ) as $color ) {
				if ( ! empty( $_POST[ 'color_' . $color ] ) ) {
					$value = sanitize_hex_color( wp_unslash( $_POST[ 'color_' . $color ] ) );
					if ( $value ) {
						$css .= "--color-{$color}: {$value};";
					}
				}
			}
			if ( $css ) {
				update_option( 'cn_design_tokens_css', ':root{' . $css . '}' );
			}
			$next = 5;
			update_option( 'cn_wizard_step', 4 );
			break;

		case 'content':
			// Sync ACF field groups from JSON before creating starter content.
			if ( function_exists( 'cn_acf_auto_sync' ) ) {
				cn_acf_auto_sync();
			}
			if ( ! empty( $_POST['create_pages'] ) ) {
				cn_wizard_create_starter_content();
			}
			$next = 6;
			update_option( 'cn_setup_complete', 1 );
			update_option( 'cn_wizard_step', 6 );
			break;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard&step=' . $next ) );
	exit;
}
add_action( 'admin_init', 'cn_wizard_handle_submit' );

/**
 * Build an ACF block comment with field data for use in post_content.
 *
 * Generates proper <!-- wp:acf/{slug} --> markup with a data attribute
 * containing field values and field key references. For blocks that
 * support InnerBlocks (jsx), wraps inner block content inside the
 * block comment instead of self-closing.
 *
 * @param string $slug    Block slug (e.g. 'hero', 'content-section').
 * @param array  $data    Field data as [field_name => value].
 * @param string $inner   Inner block HTML content (for JSX blocks).
 * @return string Block markup for post_content.
 */
function cn_acf_block_markup( $slug, $data = array(), $inner = '' ) {
	$block_name = 'acf/' . $slug;
	$attrs      = array(
		'name' => $block_name,
		'mode' => 'preview',
	);

	// Build the data attribute with field key references.
	// ACF blocks store data as { field_name => value, _field_name => field_key }.
	// Without the _-prefixed key references, get_field() can't resolve values.
	if ( $data ) {
		$full_data = array();

		// Look up the field group for this block to get field keys.
		$field_keys = cn_get_block_field_keys( 'acf/' . $slug );

		foreach ( $data as $name => $value ) {
			$full_data[ $name ] = $value;
			// Add the field key reference so ACF can resolve the field.
			if ( isset( $field_keys[ $name ] ) ) {
				$full_data[ '_' . $name ] = $field_keys[ $name ];
			}
		}
		$attrs['data'] = $full_data;
	}

	$json = wp_json_encode( $attrs );

	if ( $inner ) {
		// Non-self-closing block with inner content.
		return "<!-- wp:{$block_name} {$json} -->\n{$inner}\n<!-- /wp:{$block_name} -->";
	}

	return "<!-- wp:{$block_name} {$json} /-->";
}

/**
 * Get a map of field_name => field_key for a given block.
 *
 * Uses cn_get_block_field_groups() (from flexible-layouts.php) to find
 * the ACF field group targeting this block, then acf_get_fields() to
 * extract all top-level field keys. For repeater fields, also maps
 * sub-field keys.
 *
 * @param string $block_name Full block name (e.g. 'acf/hero').
 * @return array [field_name => field_key]
 */
function cn_get_block_field_keys( $block_name ) {
	if ( ! function_exists( 'cn_get_block_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
		return array();
	}

	$groups = cn_get_block_field_groups();
	if ( ! isset( $groups[ $block_name ] ) ) {
		return array();
	}

	$group  = $groups[ $block_name ];
	$fields = acf_get_fields( $group['key'] );
	if ( ! $fields ) {
		return array();
	}

	$keys = array();
	foreach ( $fields as $field ) {
		$keys[ $field['name'] ] = $field['key'];
	}

	return $keys;
}

/**
 * Create starter pages and the primary menu.
 */
function cn_wizard_create_starter_content() {
	$mode      = cn_get_build_mode();
	$site_type = get_option( 'cn_site_type', 'full' );
	$site_name = get_bloginfo( 'name' );
	$site_desc = get_bloginfo( 'description' );

	if ( 'flexible' === $mode ) {
		// Flexible mode: create pages with flexible content rows.
		if ( 'landing' === $site_type ) {
			$pages = array(
				'Home' => array(
					'layouts' => array(
						array( 'acf_fc_layout' => 'hero', 'heading' => $site_name, 'subheading' => $site_desc, 'style' => 'default' ),
						array( 'acf_fc_layout' => 'content_section', 'image_position' => 'left', 'content' => '<p>Welcome to ' . $site_name . '. Edit this section to tell your story.</p>' ),
						array( 'acf_fc_layout' => 'cta', 'heading' => 'Ready to get started?', 'content' => '<p>Let\'s create something great together.</p>', 'cta_text' => 'Get Started', 'cta_link' => array( 'url' => '#', 'title' => 'Get Started', 'target' => '' ), 'style' => 'default' ),
					),
				),
			);
		} else {
			$pages = array(
				'Home'    => array(
					'layouts' => array(
						array( 'acf_fc_layout' => 'hero', 'heading' => $site_name, 'subheading' => $site_desc, 'style' => 'default' ),
						array( 'acf_fc_layout' => 'cta', 'heading' => 'Ready to get started?', 'content' => '<p>Let\'s create something great together.</p>', 'cta_text' => 'Contact Us', 'cta_link' => array( 'url' => home_url( '/contact' ), 'title' => 'Contact Us', 'target' => '' ), 'style' => 'default' ),
					),
				),
				'About'   => array(
					'layouts' => array(
						array( 'acf_fc_layout' => 'content_section', 'image_position' => 'left', 'content' => '<p>Welcome to ' . $site_name . '. Edit this section to tell your story.</p>' ),
					),
				),
				'Contact' => array(
					'layouts' => array(
						array( 'acf_fc_layout' => 'gravity_form', 'heading' => 'Get in touch' ),
					),
				),
				'Blog'    => array( 'layouts' => array() ),
			);
		}
	} else {
		// Blocks mode: create pages with block markup including field data.
		$hero_data = array(
			'heading'    => $site_name,
			'subheading' => $site_desc,
		);

		$cta_data = array(
			'heading'  => 'Ready to get started?',
			'cta_text' => 'Contact Us',
			'cta_link' => array(
				'url'   => home_url( '/contact' ),
				'title' => 'Contact Us',
				'target' => '',
			),
		);

		$cta_inner = "<!-- wp:paragraph -->\n<p>Let's create something great together.</p>\n<!-- /wp:paragraph -->";

		$content_section_data = array(
			'image_position' => 'left',
		);
		$content_section_inner = "<!-- wp:heading -->\n<h2>About " . $site_name . "</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Welcome to " . $site_name . ". Edit this section to tell your story.</p>\n<!-- /wp:paragraph -->";

		$gform_data = array(
			'heading' => 'Get in touch',
		);

		if ( 'landing' === $site_type ) {
			$landing_cta_data = array(
				'heading'  => 'Ready to get started?',
				'cta_text' => 'Get Started',
				'cta_link' => array(
					'url'   => '#',
					'title' => 'Get Started',
					'target' => '',
				),
			);

			$pages = array(
				'Home' => cn_acf_block_markup( 'hero', $hero_data )
					. "\n" . cn_acf_block_markup( 'content-section', $content_section_data, $content_section_inner )
					. "\n" . cn_acf_block_markup( 'cta', $landing_cta_data, $cta_inner ),
			);
		} else {
			$pages = array(
				'Home'    => cn_acf_block_markup( 'hero', $hero_data )
					. "\n" . cn_acf_block_markup( 'cta', $cta_data, $cta_inner ),
				'About'   => cn_acf_block_markup( 'content-section', $content_section_data, $content_section_inner ),
				'Contact' => cn_acf_block_markup( 'gravity-form', $gform_data ),
				'Blog'    => '',
			);
		}
	}

	// Only create a nav menu for full sites — landing pages are single-page sites.
	$menu_id = 0;
	if ( 'landing' !== $site_type ) {
		$menu_id = wp_create_nav_menu( __( 'Primary', 'cn-starter' ) );
		if ( is_wp_error( $menu_id ) ) {
			$menu    = wp_get_nav_menu_object( 'Primary' );
			$menu_id = $menu ? $menu->term_id : 0;
		}
	}

	foreach ( $pages as $title => $data ) {
		$existing = get_page_by_path( sanitize_title( $title ) );
		if ( $existing ) {
			$page_id = $existing->ID;
		} else {
			$post_data = array(
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_type'   => 'page',
			);

			if ( 'flexible' === $mode && is_array( $data ) ) {
				$post_data['post_content'] = '';
			} else {
				$post_data['post_content'] = is_string( $data ) ? $data : '';
			}

			$page_id = wp_insert_post( $post_data );
		}

		// Save flexible content layouts as post meta.
		if ( 'flexible' === $mode && $page_id && ! is_wp_error( $page_id ) && is_array( $data ) && ! empty( $data['layouts'] ) ) {
			update_field( 'cn_layouts', $data['layouts'], $page_id );
		}

		if ( $page_id && ! is_wp_error( $page_id ) && $menu_id ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}

		if ( 'Home' === $title && $page_id && ! is_wp_error( $page_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
		}
		if ( 'Blog' === $title && $page_id && ! is_wp_error( $page_id ) ) {
			update_option( 'page_for_posts', $page_id );
		}
	}

	// In landing mode, skip the nav menu — single-page site.
	if ( 'landing' !== $site_type && $menu_id ) {
		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * Resume wizard from last saved step when visiting without a step param.
 * Must run on admin_init (before headers are sent) for wp_safe_redirect to work.
 */
function cn_wizard_resume_redirect() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// Only act on the wizard page.
	if ( ! isset( $_GET['page'] ) || 'cn-setup-wizard' !== $_GET['page'] ) {
		return;
	}

	$completed = (int) get_option( 'cn_wizard_step', 0 );
	$has_step  = isset( $_GET['step'] );
	$step      = $has_step ? absint( $_GET['step'] ) : 0;

	// Resume: no step in URL, go to next uncompleted step.
	if ( ! $has_step ) {
		if ( $completed > 0 && $completed < 6 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard&step=' . ( $completed + 1 ) ) );
			exit;
		} elseif ( $completed >= 6 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard&step=6' ) );
			exit;
		}
		return;
	}

	// Guard: don't allow jumping ahead of completed steps via URL.
	if ( $step > 1 && $step > $completed + 1 ) {
		wp_safe_redirect( admin_url( 'admin.php?page=cn-setup-wizard&step=' . max( 1, $completed + 1 ) ) );
		exit;
	}
}
add_action( 'admin_init', 'cn_wizard_resume_redirect' );

/**
 * Suppress admin notices from third-party plugins (Yoast, ACF, etc.) on the
 * wizard page so they don't break the guided flow with distracting warnings.
 */
function cn_wizard_suppress_notices() {
	if ( ! isset( $_GET['page'] ) || 'cn-setup-wizard' !== $_GET['page'] ) {
		return;
	}
	remove_all_actions( 'admin_notices' );
	remove_all_actions( 'all_admin_notices' );
}
add_action( 'admin_head', 'cn_wizard_suppress_notices', 999 );

/**
 * Render the wizard UI.
 */
function cn_wizard_render() {
	$completed = (int) get_option( 'cn_wizard_step', 0 );

	$step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;

	// Guard: don't allow jumping ahead of completed steps via URL.
	if ( $step > 1 && $step > $completed + 1 ) {
		$step = max( 1, $completed + 1 );
	}

	$steps = array(
		1 => __( 'Welcome', 'cn-starter' ),
		2 => __( 'Build Mode', 'cn-starter' ),
		3 => __( 'Site Identity', 'cn-starter' ),
		4 => __( 'Design Tokens', 'cn-starter' ),
		5 => __( 'Starter Content', 'cn-starter' ),
		6 => __( 'Done', 'cn-starter' ),
	);
	?>
	<div class="wrap cn-wizard">
		<div class="cn-wizard__card">
			<div class="cn-wizard__header">
				<div class="cn-wizard__header-gradient"></div>
				<div class="cn-wizard__brand">
					<img src="<?php echo esc_url( CN_THEME_URI . '/assets/images/cn-mark-green.svg' ); ?>" alt="Chernoff Newman" class="cn-wizard__brand-mark">
					<div class="cn-wizard__brand-text">
						<span class="cn-wizard__brand-name">Chernoff Newman</span>
						<span class="cn-wizard__brand-sub">CN Starter Theme</span>
					</div>
				</div>
			</div>

			<ol class="cn-wizard__steps">
				<?php foreach ( $steps as $num => $label ) : ?>
					<?php
					$classes = array();
					if ( $num === $step ) {
						$classes[] = 'is-current';
					} elseif ( $num < $step || ( $completed > 0 && $num <= $completed ) ) {
						$classes[] = 'is-done';
					}
					$can_navigate = $num <= $completed || $num === $step;
						?>
					<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php if ( $can_navigate && $num !== $step ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-setup-wizard&step=' . $num ) ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $label ); ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php
			switch ( $step ) {
				case 1:
					cn_wizard_step_welcome();
					break;
				case 2:
					cn_wizard_step_build_mode();
					break;
				case 3:
					cn_wizard_step_identity();
					break;
				case 4:
					cn_wizard_step_tokens();
					break;
				case 5:
					cn_wizard_step_content();
					break;
				default:
					cn_wizard_step_done();
			}
			?>
		</div>
	</div>
	<?php
}

/** Step 1: Welcome + plugin status with auto-install. */
function cn_wizard_step_welcome() {
	$plugins    = cn_plugin_statuses();
	$free_count = count( array_filter( $plugins, static fn( $p ) => ! empty( $p['wporg'] ) && ! $p['active'] ) );
	?>
	<h1><?php esc_html_e( 'Welcome to CN Starter', 'cn-starter' ); ?></h1>
	<p class="cn-wizard__lead"><?php esc_html_e( "Let's get this project set up. First, check your plugin stack:", 'cn-starter' ); ?></p>

	<?php if ( $free_count > 0 ) : ?>
		<p class="cn-wizard__install-bar">
			<button type="button" class="button button-secondary" id="cn-install-free">
				<?php echo esc_html( sprintf( _n( 'Install %d free plugin from wordpress.org', 'Install %d free plugins from wordpress.org', $free_count, 'cn-starter' ), $free_count ) ); ?>
			</button>
		</p>
	<?php endif; ?>

	<table class="widefat striped cn-wizard__plugins">
		<tbody>
			<?php foreach ( $plugins as $plugin ) : ?>
				<?php
				$search_url = admin_url( 'plugin-install.php?tab=search&s=' . urlencode( $plugin['name'] ) );
				$upload_url = admin_url( 'plugin-install.php?tab=upload' );
				?>
				<tr data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>" data-wporg="<?php echo esc_attr( $plugin['wporg'] ? '1' : '0' ); ?>">
					<td><strong><?php echo esc_html( $plugin['name'] ); ?></strong></td>
					<td><?php echo esc_html( ucfirst( $plugin['level'] ) ); ?></td>
					<td><?php echo esc_html( $plugin['note'] ); ?></td>
					<td class="cn-wizard__plugin-action">
						<?php if ( $plugin['active'] ) : ?>
							<span class="cn-wizard__status cn-wizard__status--active">&#10003; <?php esc_html_e( 'Active', 'cn-starter' ); ?></span>
						<?php else : ?>
							<span class="cn-wizard__status cn-wizard__status--missing">&#10007; <?php esc_html_e( 'Missing', 'cn-starter' ); ?></span>
							<?php if ( $plugin['wporg'] ) : ?>
								<button type="button" class="button button-small cn-wizard__install-btn" data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>">
									<?php esc_html_e( 'Install', 'cn-starter' ); ?>
								</button>
								<a href="<?php echo esc_url( $search_url ); ?>" class="cn-wizard__dashboard-link"><?php esc_html_e( 'Search in dashboard', 'cn-starter' ); ?></a>
							<?php else : ?>
								<a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-small cn-wizard__get-btn">
									<?php esc_html_e( 'Get Plugin', 'cn-starter' ); ?> &nearr;
								</a>
								<a href="<?php echo esc_url( $upload_url ); ?>" class="cn-wizard__dashboard-link"><?php esc_html_e( 'Upload in dashboard', 'cn-starter' ); ?></a>
							<?php endif; ?>
							<span class="cn-wizard__install-status" data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>"></span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="cn-wizard__cli-hint">
		<code>wp cn plugins install</code> <?php esc_html_e( '— install free plugins via WP-CLI. Use --acf-pro=<url> for premium ZIPs.', 'cn-starter' ); ?>
	</p>

	<form method="post">
		<?php wp_nonce_field( 'cn_wizard' ); ?>
		<input type="hidden" name="cn_wizard_step" value="welcome">
		<p class="cn-wizard__actions">
			<button class="button button-primary button-hero"><?php esc_html_e( "Let's Get Started", 'cn-starter' ); ?></button>
		</p>
	</form>

	<script>
	jQuery( function ( $ ) {
		var nonce = '<?php echo wp_create_nonce( 'cn_install_plugin' ); ?>';

		function installPlugin( slug, zipUrl, $statusEl, autoReload ) {
			autoReload = autoReload !== false;
			$statusEl.html( '<span class="spinner is-active" style="float:none"></span> Installing…' );

			var searchUrl = '<?php echo esc_js( admin_url( "plugin-install.php?tab=search&s=" ) ); ?>' + encodeURIComponent( slug.replace( /-/g, ' ' ) );

			return $.ajax( {
				url: ajaxurl,
				type: 'POST',
				timeout: 120000,
				data: {
					action: 'cn_install_plugin',
					nonce: nonce,
					slug: slug,
					zip_url: zipUrl || ''
				}
			} ).done( function ( res ) {
				if ( res.success ) {
					$statusEl.html( '<span style="color:#059973;font-weight:700">&#10003; ' + res.data.message + '</span>' );
					if ( autoReload ) {
						setTimeout( function() { window.location.reload(); }, 1500 );
					}
				} else {
					$statusEl.html( '<span style="color:#c62828;font-weight:700">&#10007; ' + res.data.message + '</span>' );
				}
			} ).fail( function ( jqXHR, textStatus ) {
				var msg;
				if ( textStatus === 'timeout' ) {
					msg = 'Timed out — this plugin may be too large for AJAX install. <a href="' + searchUrl + '" target="_blank">Install from dashboard &nearr;</a>';
				} else if ( jqXHR.responseText ) {
					var text = jqXHR.responseText.replace( /<[^>]*>/g, '' ).trim().substring( 0, 200 );
					msg = text || ( 'HTTP ' + jqXHR.status + ' ' + jqXHR.statusText );
				} else if ( jqXHR.status ) {
					msg = 'HTTP ' + jqXHR.status + ' ' + jqXHR.statusText;
				} else {
					msg = 'Request failed — <a href="' + searchUrl + '" target="_blank">Install from dashboard &nearr;</a>';
				}
				$statusEl.html( '<span style="color:#c62828;font-weight:700">&#10007; ' + msg + '</span>' );
			} );
		}

		$( '.cn-wizard__install-btn' ).on( 'click', function () {
			var slug = $( this ).data( 'slug' );
			var $status = $( '.cn-wizard__install-status[data-slug="' + slug + '"]' );
			installPlugin( slug, '', $status, true );
		} );

		$( '#cn-install-free' ).on( 'click', function () {
			var $btn = $( this );
			$btn.prop( 'disabled', true ).text( 'Installing…' );

			var queue = [];
			$( '.cn-wizard__plugins tr[data-wporg="1"]' ).each( function () {
				queue.push( {
					slug: $( this ).data( 'slug' ),
					$status: $( this ).find( '.cn-wizard__install-status' )
				} );
			} );

			function processNext() {
				if ( queue.length === 0 ) {
					$btn.text( 'Done! Reloading…' );
					setTimeout( function() { window.location.reload(); }, 1000 );
					return;
				}
				var item = queue.shift();
				$btn.text( 'Installing ' + ( item.slug ) + '…' );
				installPlugin( item.slug, '', item.$status, false ).always( processNext );
			}

			processNext();
		} );
	} );
	</script>
	<?php
}

/** Step 2: Build Mode — choose blocks or flexible layouts, and site type. */
function cn_wizard_step_build_mode() {
	$current   = cn_get_build_mode();
	$site_type = get_option( 'cn_site_type', 'full' );
	?>
	<h1><?php esc_html_e( 'Choose Your Build Mode', 'cn-starter' ); ?></h1>
	<p class="cn-wizard__lead"><?php esc_html_e( 'Lock the site to one editing approach. This cannot be changed after setup.', 'cn-starter' ); ?></p>

	<form method="post">
		<?php wp_nonce_field( 'cn_wizard' ); ?>
		<input type="hidden" name="cn_wizard_step" value="build_mode">
		<input type="hidden" name="cn_site_type" id="cn_site_type_input" value="<?php echo esc_attr( $site_type ); ?>">

		<p class="cn-eyebrow cn-wizard__group-label"><?php esc_html_e( 'Editing Approach', 'cn-starter' ); ?></p>
		<div class="cn-wizard__build-mode">
			<label class="cn-wizard__mode-card<?php echo 'blocks' === $current ? ' is-selected' : ''; ?>">
				<input type="radio" name="cn_build_mode" value="blocks" <?php checked( $current, 'blocks' ); ?>>
				<span class="cn-wizard__mode-icon dashicons dashicons-edit-page"></span>
				<span class="cn-wizard__mode-title"><?php esc_html_e( 'Blocks', 'cn-starter' ); ?></span>
				<span class="cn-wizard__mode-desc"><?php esc_html_e( 'WordPress block editor with CN custom blocks. Best for content editors who use Gutenberg.', 'cn-starter' ); ?></span>
			</label>
			<label class="cn-wizard__mode-card<?php echo 'flexible' === $current ? ' is-selected' : ''; ?>">
				<input type="radio" name="cn_build_mode" value="flexible" <?php checked( $current, 'flexible' ); ?>>
				<span class="cn-wizard__mode-icon dashicons dashicons-layout"></span>
				<span class="cn-wizard__mode-title"><?php esc_html_e( 'Flexible Layouts', 'cn-starter' ); ?></span>
				<span class="cn-wizard__mode-desc"><?php esc_html_e( 'ACF Flexible Content page builder. Add sections from a unified picker — no block editor needed.', 'cn-starter' ); ?></span>
			</label>
		</div>

		<p class="cn-eyebrow cn-wizard__group-label"><?php esc_html_e( 'Site Scope', 'cn-starter' ); ?></p>
		<div class="cn-wizard__site-type">
			<label class="cn-wizard__site-type-card<?php echo 'full' === $site_type ? ' is-selected' : ''; ?>" data-site-type="full">
				<span class="cn-wizard__site-type-icon dashicons dashicons-admin-multisite"></span>
				<span class="cn-wizard__site-type-title"><?php esc_html_e( 'Full Site', 'cn-starter' ); ?></span>
				<span class="cn-wizard__site-type-desc"><?php esc_html_e( 'Creates Home, About, Contact, and Blog pages with a primary navigation menu.', 'cn-starter' ); ?></span>
			</label>
			<label class="cn-wizard__site-type-card<?php echo 'landing' === $site_type ? ' is-selected' : ''; ?>" data-site-type="landing">
				<span class="cn-wizard__site-type-icon dashicons dashicons-welcome-view-site"></span>
				<span class="cn-wizard__site-type-title"><?php esc_html_e( 'Landing Page', 'cn-starter' ); ?></span>
				<span class="cn-wizard__site-type-desc"><?php esc_html_e( 'Creates a single homepage with hero, content, and CTA sections. No nav menu — perfect for one-page sites.', 'cn-starter' ); ?></span>
			</label>
		</div>

		<p class="cn-wizard__actions">
			<button class="button button-primary button-hero"><?php esc_html_e( 'Save & Continue', 'cn-starter' ); ?></button>
			<button type="submit" name="cn_wizard_skip" value="1" class="cn-wizard__skip"><?php esc_html_e( 'Skip', 'cn-starter' ); ?></button>
		</p>
	</form>
	<script>
	jQuery( function ( $ ) {
		$( '.cn-wizard__mode-card' ).on( 'click', function () {
			var $card = $( this );
			$card.find( 'input[type="radio"]' ).prop( 'checked', true );
			$( '.cn-wizard__mode-card' ).removeClass( 'is-selected' );
			$card.addClass( 'is-selected' );
		} );
		$( '.cn-wizard__site-type-card' ).on( 'click', function () {
			var $card = $( this );
			$( '.cn-wizard__site-type-card' ).removeClass( 'is-selected' );
			$card.addClass( 'is-selected' );
			$( '#cn_site_type_input' ).val( $card.data( 'site-type' ) );
		} );
	} );
	</script>
	<?php
}

/** Step 3: Site identity. */
function cn_wizard_step_identity() {
	wp_enqueue_media();
	?>
	<h1><?php esc_html_e( 'Site Identity', 'cn-starter' ); ?></h1>
	<form method="post">
		<?php wp_nonce_field( 'cn_wizard' ); ?>
		<input type="hidden" name="cn_wizard_step" value="identity">

		<table class="form-table">
			<tr>
				<th><label for="blogname"><?php esc_html_e( 'Site Title', 'cn-starter' ); ?></label></th>
				<td><input name="blogname" id="blogname" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'blogname' ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="blogdescription"><?php esc_html_e( 'Tagline', 'cn-starter' ); ?></label></th>
				<td><input name="blogdescription" id="blogdescription" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'blogdescription' ) ); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Logo', 'cn-starter' ); ?></th>
				<td>
					<input type="hidden" name="custom_logo" id="cn-logo-id" value="">
					<button type="button" class="button" id="cn-logo-select"><?php esc_html_e( 'Choose Logo', 'cn-starter' ); ?></button>
					<span id="cn-logo-preview"></span>
				</td>
			</tr>
		</table>

		<p class="cn-wizard__actions">
			<button class="button button-primary button-hero"><?php esc_html_e( 'Save & Continue', 'cn-starter' ); ?></button>
			<button type="submit" name="cn_wizard_skip" value="1" class="cn-wizard__skip"><?php esc_html_e( 'Skip', 'cn-starter' ); ?></button>
		</p>
	</form>
	<script>
	jQuery( function ( $ ) {
		$( '#cn-logo-select' ).on( 'click', function ( e ) {
			e.preventDefault();
			var frame = wp.media( { title: '<?php echo esc_js( __( 'Choose Logo', 'cn-starter' ) ); ?>', multiple: false } );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$( '#cn-logo-id' ).val( att.id );
				$( '#cn-logo-preview' ).html( '<img src="' + att.url + '" style="max-height:40px;vertical-align:middle;margin-left:8px">' );
			} );
			frame.open();
		} );
	} );
	</script>
	<?php
}

/** Step 3: Design tokens. */
function cn_wizard_step_tokens() {
	?>
	<h1><?php esc_html_e( 'Design Tokens', 'cn-starter' ); ?></h1>
	<p>
		<?php esc_html_e( 'Set your core brand colors now. You can come back to these anytime via', 'cn-starter' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-design-tokens' ) ); ?>"><?php esc_html_e( 'CN Starter → Design Tokens', 'cn-starter' ); ?></a>.
		<?php esc_html_e( 'For full Figma token import (typography, spacing), use', 'cn-starter' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>"><?php esc_html_e( 'Figma Tokens', 'cn-starter' ); ?></a>.
	</p>
	<form method="post">
		<?php wp_nonce_field( 'cn_wizard' ); ?>
		<input type="hidden" name="cn_wizard_step" value="tokens">

		<table class="form-table">
			<tr>
				<th><label for="color_primary"><?php esc_html_e( 'Primary Color (CN Green)', 'cn-starter' ); ?></label></th>
				<td><input name="color_primary" id="color_primary" type="color" value="#059973"></td>
			</tr>
			<tr>
				<th><label for="color_secondary"><?php esc_html_e( 'Secondary Color (CN Black)', 'cn-starter' ); ?></label></th>
				<td><input name="color_secondary" id="color_secondary" type="color" value="#000000"></td>
			</tr>
			<tr>
				<th><label for="color_accent"><?php esc_html_e( 'Accent Color (CN Vert)', 'cn-starter' ); ?></label></th>
				<td><input name="color_accent" id="color_accent" type="color" value="#2aa072"></td>
			</tr>
		</table>

		<p class="cn-wizard__actions">
			<button class="button button-primary button-hero"><?php esc_html_e( 'Save & Continue', 'cn-starter' ); ?></button>
			<button type="submit" name="cn_wizard_skip" value="1" class="cn-wizard__skip"><?php esc_html_e( 'Skip', 'cn-starter' ); ?></button>
		</p>
	</form>
	<?php
}

/** Step 5: Starter content. */
function cn_wizard_step_content() {
	$site_type = get_option( 'cn_site_type', 'full' );
	$is_landing = 'landing' === $site_type;
	?>
	<h1><?php esc_html_e( 'Starter Content', 'cn-starter' ); ?></h1>
	<?php if ( $is_landing ) : ?>
		<p><?php esc_html_e( 'Create a single homepage with hero, content, and CTA sections, and set it as the front page.', 'cn-starter' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Create Home, About, Contact, and Blog pages, wire up the primary menu, and set the homepage.', 'cn-starter' ); ?></p>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'cn_wizard' ); ?>
		<input type="hidden" name="cn_wizard_step" value="content">
		<label>
			<input type="checkbox" name="create_pages" value="1" checked>
			<?php echo $is_landing ? esc_html__( 'Create starter homepage', 'cn-starter' ) : esc_html__( 'Create starter pages & menu', 'cn-starter' ); ?>
		</label>

		<p class="cn-wizard__actions">
			<button class="button button-primary button-hero"><?php esc_html_e( 'Finish Setup', 'cn-starter' ); ?></button>
		</p>
	</form>
	<?php
}

/** Step 5: Done. */
function cn_wizard_step_done() {
	// Check if ACF field groups need syncing from JSON.
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
		$files = scandir( $json_dir );
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
			// Check DB directly — ACF's cache may be stale after import.
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
	<h1><?php esc_html_e( "You're all set", 'cn-starter' ); ?></h1>
	<p><?php esc_html_e( 'The theme is configured. Here are some useful next steps:', 'cn-starter' ); ?></p>

	<?php if ( $acf_needs_sync > 0 ) : ?>
		<div class="notice notice-warning inline" style="margin: 16px 36px; border-left: 4px solid #dba617;">
			<p>
				<strong><?php esc_html_e( 'Action required:', 'cn-starter' ); ?></strong>
				<?php
				echo esc_html( sprintf(
					_n( '%d ACF field group needs to be synced to make block fields editable.', '%d ACF field groups need to be synced to make block fields editable.', $acf_needs_sync, 'cn-starter' ),
					$acf_needs_sync
				) );
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cn-starter&cn_action=sync_acf' ), 'cn_sync_acf' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Sync ACF Field Groups', 'cn-starter' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>
		<div class="notice notice-success inline" style="margin: 16px 36px; border-left: 4px solid #00a32a;">
			<p>
				<strong><?php esc_html_e( 'ACF field groups synced.', 'cn-starter' ); ?></strong>
				<?php esc_html_e( 'All block fields are imported and ready to edit.', 'cn-starter' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<ul class="cn-wizard__links">
		<li><a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_option( 'page_on_front' ) . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit the homepage', 'cn-starter' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-design-tokens' ) ); ?>"><?php esc_html_e( 'Adjust brand colors (Design Tokens)', 'cn-starter' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-figma-tokens' ) ); ?>"><?php esc_html_e( 'Import full Figma design tokens', 'cn-starter' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-docs' ) ); ?>"><?php esc_html_e( 'Read the theme docs', 'cn-starter' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cn-block-generator' ) ); ?>"><?php esc_html_e( 'Create a new block', 'cn-starter' ); ?></a></li>
	</ul>
	<p class="cn-wizard__actions">
		<a class="button button-primary button-hero" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Go to Dashboard', 'cn-starter' ); ?></a>
	</p>
	<?php
}

/**
 * Wizard styles — handled by cn_admin_branding_styles in branding.php.
 */
