<?php
/**
 * Template Name: Flexible Layout
 *
 * A page builder template using ACF Flexible Content.
 * Editors add sections from a single "Page Sections" field —
 * no block editor needed. Each section mirrors a CN block and
 * reuses the same CSS classes for a consistent frontend look.
 *
 * Requires ACF Pro. ACFE (ACF Extended) enhances the UI with
 * a modal picker and better previews but is not required.
 *
 * @package cn-starter
 */

get_header();

while ( have_posts() ) :
	the_post();
	cn_render_flexible_layouts();
endwhile;

get_footer();
