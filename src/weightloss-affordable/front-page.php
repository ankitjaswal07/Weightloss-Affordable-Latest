<?php
/**
 * Front page template.
 *
 * The homepage is a normal Page (editable in Elementor or in the Page sections
 * meta box) set as the static front page.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		wla_render_page_body( get_the_ID() );
	endwhile;
else :
	?>
	<section class="subpage-hero">
		<div class="site-container">
			<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<p class="hero-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
		</div>
	</section>
	<?php
endif;

get_footer();
