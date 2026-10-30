<?php
/** Load WLA presentation assets for bundled shortcodes and Elementor components. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_experience_page_has_components() {
	if ( is_front_page() || is_singular( 'nrx_treatment' ) ) { return true; }
	$post = get_post();
	if ( ! $post ) { return false; }
	$content = (string) $post->post_content . ' ' . (string) get_post_meta( $post->ID, '_elementor_data', true );
	foreach ( array( '[wla_treatment_plans', '[wla_treatment_detail', '[wla_get_quote', '[wla_results', '[wla_video_testimonials', '[wla_calculators', 'wla-experience-component' ) as $marker ) {
		if ( false !== strpos( $content, $marker ) ) { return true; }
	}
	return false;
}
function wla_experience_assets() {
	if ( ! wla_experience_page_has_components() ) { return; }
	wp_enqueue_style( 'wla-experience', WLA_EXPERIENCE_URL . 'assets/experience.css', array(), WLA_EXPERIENCE_VERSION );
	wp_enqueue_script( 'wla-calculators', WLA_EXPERIENCE_URL . 'assets/calculators.js', array(), WLA_EXPERIENCE_VERSION, true );
	wp_enqueue_script( 'wla-videos', WLA_EXPERIENCE_URL . 'assets/videos.js', array(), WLA_EXPERIENCE_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'wla_experience_assets', 12 );

register_activation_hook( WLA_CORE_FILE, function() {
	if ( function_exists( 'wla_experience_register_video_type' ) ) { wla_experience_register_video_type(); }
	flush_rewrite_rules();
} );
