<?php
/** Polylang-aware utilities and safe integration defaults. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_register_translation_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) { return; }
	$strings = array(
		'Clear information. A more confident next step.', 'Ask a question', 'Explore', 'Read more',
		'General information', 'Services and availability', 'Language support', 'Website help',
		'Please do not include health information in this form.', 'Draft for legal review before publication.',
	);
	foreach ( $strings as $string ) { pll_register_string( 'weightloss-affordable-core', $string, 'Theme interface' ); }
}
add_action( 'init', 'wla_register_translation_strings', 30 );

/** Add translated equivalents for core archive/page requests where Polylang is active. */
function wla_language_hreflang_hints() {
	if ( is_admin() || ! function_exists( 'pll_current_language' ) ) { return; }
	$language = pll_current_language( 'locale' );
	if ( $language ) { echo '<meta name="content-language" content="' . esc_attr( $language ) . '">' . "\n"; }
}
add_action( 'wp_head', 'wla_language_hreflang_hints', 2 );

/** Use the correct navigation menu for each active Polylang language. */
function wla_localized_menu_args( $args ) {
	if ( ! function_exists( 'pll_current_language' ) || empty( $args['theme_location'] ) || ! in_array( $args['theme_location'], array( 'primary', 'footer' ), true ) ) { return $args; }
	$language = pll_current_language( 'slug' );
	$location = sanitize_key( $args['theme_location'] );
	$menu_id = absint( get_option( 'wla_demo_menu_' . $language . '_' . $location ) );
	if ( $menu_id ) { $args['menu'] = $menu_id; }
	return $args;
}
add_filter( 'wp_nav_menu_args', 'wla_localized_menu_args', 20 );

/** No advertising pixels, medical intake, or third-party tracking are added by this plugin. */
