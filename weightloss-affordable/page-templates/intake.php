<?php
/**
 * Template Name: Get Quote / Medical Enquiry
 *
 * Dedicated quiz page used by demo import and the consultation-eligibility route.
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
	$is_elementor = ( 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) && did_action( 'elementor/loaded' ) );
	if ( $is_elementor ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	} elseif ( shortcode_exists( 'wla_get_quote' ) ) {
		echo do_shortcode( '[wla_get_quote mode="quiz" title="Get a quote" intro="Answer each step. You can go back without losing your answers."]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( shortcode_exists( 'nrx_quiz' ) ) {
		echo do_shortcode( '[nrx_quiz]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	}
	?>
</main>
<?php
get_footer();
