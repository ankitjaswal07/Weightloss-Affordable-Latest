<?php
/**
 * Single treatment template.
 *
 * Renders the treatment's own section list (page hero, dynamic detail block,
 * FAQs, call to action). Editable in Elementor or in the Page sections box.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	wla_render_page_body( get_the_ID() );
endwhile;

get_footer();
