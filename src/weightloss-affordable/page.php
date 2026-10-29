<?php
/**
 * Default page template.
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

	if ( comments_open() || get_comments_number() ) {
		echo '<div class="site-container section-pad">';
		comments_template();
		echo '</div>';
	}
endwhile;

get_footer();
