<?php
/**
 * Block patterns — pre-built page sections from CN Blocks.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register block patterns.
 */
function cn_register_patterns() {
	register_block_pattern(
		'cn-starter/hero-with-cards',
		array(
			'title'       => __( 'Hero + Feature Cards', 'cn-starter' ),
			'description' => __( 'A hero section followed by a 3-column feature card grid.', 'cn-starter' ),
			'categories'  => array( 'cn-starter' ),
			'content'     => '<!-- wp:acf/hero {"name":"acf/hero","align":"full","mode":"preview"} /--><!-- wp:acf/cards-grid {"name":"acf/cards-grid","mode":"preview"} /-->',
		)
	);

	register_block_pattern(
		'cn-starter/about-section',
		array(
			'title'       => __( 'About Section', 'cn-starter' ),
			'description' => __( 'Content section with image + text, followed by a CTA.', 'cn-starter' ),
			'categories'  => array( 'cn-starter' ),
			'content'     => '<!-- wp:acf/content-section {"name":"acf/content-section","mode":"preview"} /--><!-- wp:acf/cta {"name":"acf/cta","mode":"preview"} /-->',
		)
	);

	register_block_pattern(
		'cn-starter/contact-page',
		array(
			'title'       => __( 'Contact Page', 'cn-starter' ),
			'description' => __( 'Heading + Gravity Form block for a contact page.', 'cn-starter' ),
			'categories'  => array( 'cn-starter' ),
			'content'     => '<!-- wp:acf/cta {"name":"acf/cta","mode":"preview"} /--><!-- wp:acf/gravity-form {"name":"acf/gravity-form","mode":"preview"} /-->',
		)
	);

	register_block_pattern(
		'cn-starter/team-section',
		array(
			'title'       => __( 'Team + Testimonials', 'cn-starter' ),
			'description' => __( 'Team grid followed by a testimonials section.', 'cn-starter' ),
			'categories'  => array( 'cn-starter' ),
			'content'     => '<!-- wp:acf/team-grid {"name":"acf/team-grid","mode":"preview"} /--><!-- wp:acf/testimonials {"name":"acf/testimonials","mode":"preview"} /-->',
		)
	);
}
add_action( 'init', 'cn_register_patterns' );

/**
 * Register a pattern category so CN patterns are grouped in the inserter.
 */
function cn_register_pattern_category() {
	register_block_pattern_category(
		'cn-starter',
		array( 'label' => __( 'CN Starter', 'cn-starter' ) )
	);
}
add_action( 'init', 'cn_register_pattern_category' );
