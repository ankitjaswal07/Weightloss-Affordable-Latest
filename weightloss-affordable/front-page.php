<?php
/**
 * Front Page: Renders Elementor widget canvas when Elementor builder is active on this page,
 * OR renders the exact 100% preview homepage out of the box automatically!
 */
get_header();
?>
<main id="main-content" class="wla-elementor-canvas-reset">
<?php
if ( have_posts() && 'page' === get_option( 'show_on_front' ) ) :
	while ( have_posts() ) :
		the_post();
		$is_elementor = ( 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) && did_action( 'elementor/loaded' ) );
		if ( $is_elementor ) {
			the_content();
		} else {
			echo wla_render_full_homepage(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	endwhile;
else :
	echo wla_render_full_homepage(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;
?>
</main>
<?php
get_footer();
