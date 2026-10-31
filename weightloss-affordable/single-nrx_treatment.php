<?php
/**
 * Single treatment: Elementor canvas when assigned, otherwise the treatment component.
 *
 * @package WeightLossAffordable
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="main-content" class="wla-elementor-canvas-reset">
	<?php
	while ( have_posts() ) :
		the_post();
		$is_elementor = ( 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) && did_action( 'elementor/loaded' ) );
		if ( $is_elementor ) {
			the_content();
		} elseif ( shortcode_exists( 'wla_treatment_detail' ) ) {
			echo do_shortcode( '[wla_treatment_detail]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
