<?php
/**
 * Load bundled clinical modules (quiz, treatments, experience widgets).
 *
 * These modules shipped inside the plugin but were never required from the
 * bootstrap file, so the Get Quote quiz, treatment CPT and Elementor
 * experience widgets never ran on a normal install.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'NRX_CORE_VERSION' ) ) {
	define( 'NRX_CORE_VERSION', WLA_CORE_VERSION );
}
if ( ! defined( 'NRX_CORE_FILE' ) ) {
	define( 'NRX_CORE_FILE', WLA_CORE_FILE );
}
if ( ! defined( 'NRX_CORE_DIR' ) ) {
	define( 'NRX_CORE_DIR', WLA_CORE_DIR . 'modules/nuestrarx/' );
}
if ( ! defined( 'NRX_CORE_URL' ) ) {
	define( 'NRX_CORE_URL', WLA_CORE_URL . 'modules/nuestrarx/' );
}
if ( ! defined( 'NRX_CORE_BASENAME' ) ) {
	define( 'NRX_CORE_BASENAME', plugin_basename( WLA_CORE_FILE ) );
}

if ( ! defined( 'WLA_EXPERIENCE_VERSION' ) ) {
	define( 'WLA_EXPERIENCE_VERSION', WLA_CORE_VERSION );
}
if ( ! defined( 'WLA_EXPERIENCE_DIR' ) ) {
	define( 'WLA_EXPERIENCE_DIR', WLA_CORE_DIR . 'modules/experience/' );
}
if ( ! defined( 'WLA_EXPERIENCE_URL' ) ) {
	define( 'WLA_EXPERIENCE_URL', WLA_CORE_URL . 'modules/experience/' );
}

$nrx_bootstrap = NRX_CORE_DIR . 'nuestrarx-core.php';
if ( is_readable( $nrx_bootstrap ) ) {
	require_once $nrx_bootstrap;
}

$experience_files = array(
	'includes/experience-loader.php',
	'modules/experience/includes/content.php',
	'modules/experience/includes/shortcodes.php',
	'modules/experience/includes/treatment-detail.php',
	'modules/experience/includes/nuestra-quiz-bridge.php',
	'modules/experience/includes/elementor.php',
	'includes/demo-seed.php',
	'includes/demo-clinical.php',
);
foreach ( $experience_files as $relative ) {
	$path = WLA_CORE_DIR . $relative;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

/**
 * English is the site default. Do not switch WordPress core language files.
 */
function wla_force_english_defaults() {
	if ( function_exists( 'nrx_setting' ) && 'en' !== nrx_setting( 'default_lang', 'en' ) && false === get_option( 'nrx_settings', false ) ) {
		nrx_update_settings( array( 'default_lang' => 'en' ) );
	}
}
add_action( 'init', 'wla_force_english_defaults', 1 );

/**
 * Allow Elementor to edit treatment records.
 */
function wla_elementor_nrx_support( $post_types ) {
	if ( ! in_array( 'nrx_treatment', $post_types, true ) ) {
		$post_types[] = 'nrx_treatment';
	}
	return $post_types;
}
add_filter( 'elementor_cpt_support', 'wla_elementor_nrx_support' );
