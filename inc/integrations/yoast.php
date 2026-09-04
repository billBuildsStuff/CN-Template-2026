<?php
/**
 * Yoast SEO integration.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Move the Yoast metabox below ACF fields for a cleaner edit screen.
 */
function cn_yoast_metabox_priority() {
	return 'low';
}
add_filter( 'wpseo_metabox_prio', 'cn_yoast_metabox_priority' );
