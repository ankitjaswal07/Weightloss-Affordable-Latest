<?php
/** Load the existing NuestraRx quiz renderer, CSS and JavaScript from Elementor/content placements. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_experience_nrx_quiz_is_configured() {
	$config = function_exists( 'nrx_quiz_get' ) ? nrx_quiz_get( 'published' ) : get_option( 'nrx_quiz_published', false );
	return is_array( $config ) && ! empty( $config['steps'] ) && is_array( $config['steps'] );
}
function wla_experience_elementor_has_embedded_quiz( $elements ) {
	foreach ( (array) $elements as $element ) {
		$settings = (array) ( $element['settings'] ?? array() );
		if ( 'get_quote' === ( $settings['module'] ?? '' ) && 'quiz' === ( $settings['quote_mode'] ?? '' ) ) { return true; }
		if ( ! empty( $element['elements'] ) && wla_experience_elementor_has_embedded_quiz( $element['elements'] ) ) { return true; }
	}
	return false;
}
function wla_experience_nrx_quiz_placement() {
	if ( ! function_exists( 'nrx_quiz_enqueue_assets' ) || ! shortcode_exists( 'nrx_quiz' ) || ! wla_experience_nrx_quiz_is_configured() ) { return false; }
	$post = get_post();
	if ( ! $post ) { return false; }
	$elementor = json_decode( (string) get_post_meta( $post->ID, '_elementor_data', true ), true );
	$template = get_page_template_slug( $post->ID );
	return has_shortcode( $post->post_content, 'nrx_quiz' )
		|| wla_experience_elementor_has_embedded_quiz( $elementor )
		|| ( is_array( $elementor ) && false !== strpos( wp_json_encode( $elementor ), 'nrx_quiz' ) )
		|| 'page-templates/intake.php' === $template;
}

/** At priority 19, call the plugin's original loader with its exact published config. */
function wla_experience_bridge_nrx_quiz_assets() {
	if ( ! wla_experience_nrx_quiz_placement() ) { return; }
	// The NRX stylesheet declares a dependency on a theme handle. Satisfy that
	// dependency without loading or activating the unrelated NuestraRx theme.
	if ( ! wp_style_is( 'nuestrarx-main', 'registered' ) ) {
		wp_register_style( 'nuestrarx-main', false, array(), defined( 'NRX_CORE_VERSION' ) ? NRX_CORE_VERSION : '1.0' );
	}
	$post = get_post();
	$original = $post->post_content;
	if ( ! has_shortcode( $original, 'nrx_quiz' ) ) { $post->post_content .= "\n[nrx_quiz]\n"; }
	// This uses NRX's own published quiz configuration and localized submission
	// nonce, validation messages, state rules and AJAX endpoint. No quiz logic is copied.
	nrx_quiz_enqueue_assets();
	$post->post_content = $original;
}
add_action( 'wp_enqueue_scripts', 'wla_experience_bridge_nrx_quiz_assets', 19 );

function wla_experience_enqueue_nrx_quiz_skin() {
	if ( wp_style_is( 'nrx-quiz', 'enqueued' ) ) {
		wp_enqueue_style( 'wla-experience-quiz-skin', WLA_EXPERIENCE_URL . 'assets/quiz-skin.css', array( 'nrx-quiz' ), WLA_EXPERIENCE_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'wla_experience_enqueue_nrx_quiz_skin', 25 );
