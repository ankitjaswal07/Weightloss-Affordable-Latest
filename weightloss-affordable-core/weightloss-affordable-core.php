<?php
/**
 * Plugin Name: WeightLossAffordable Core
 * Plugin URI: https://weightlossaffordable.com/
 * Description: 100% Preview-Identical Free Elementor Widgets, Live Single-Page EN/ES Translator Engine, Custom Post Types, AJAX General Inquiry Form, and One-Click Single-Site Importer for WeightLossAffordable.com.
 * Version: 2.1.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: WeightLossAffordable
 * Text Domain: weightloss-affordable-core
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLA_CORE_VERSION', '2.1.0' );
define( 'WLA_CORE_FILE', __FILE__ );
define( 'WLA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once WLA_CORE_DIR . 'includes/post-types.php';
require_once WLA_CORE_DIR . 'includes/elementor.php';
require_once WLA_CORE_DIR . 'includes/contact-form.php';
require_once WLA_CORE_DIR . 'includes/translator.php';
require_once WLA_CORE_DIR . 'includes/demo-import.php';
require_once WLA_CORE_DIR . 'includes/site-bootstrap.php';

function wla_core_load_textdomain() {
	load_plugin_textdomain( 'weightloss-affordable-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wla_core_load_textdomain' );

function wla_core_activate() {
	wla_register_content_types();
	if ( function_exists( 'nrx_register_post_types' ) ) {
		nrx_register_post_types();
	}
	if ( function_exists( 'nrx_grant_capabilities' ) ) {
		nrx_grant_capabilities();
	}
	if ( function_exists( 'wla_experience_register_video_type' ) ) {
		wla_experience_register_video_type();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wla_core_activate' );

function wla_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wla_core_deactivate' );
