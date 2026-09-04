<?php
/**
 * Default page template — full-width block canvas.
 * Pages are built entirely with blocks; the container is managed
 * by each block (alignwide / alignfull supported).
 *
 * @package cn-starter
 */

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
