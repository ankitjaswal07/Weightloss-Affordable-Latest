<?php
/**
 * Plugin Name: WeightLossAffordable Core
 * Plugin URI: https://weightlossaffordable.com/
 * Description: Treatments, structured page sections, Elementor section widgets, enquiry and consultation forms, technical SEO, settings and a one-click demo importer for the WeightLossAffordable theme.
 * Version: 3.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: WeightLossAffordable
 * Text Domain: weightloss-affordable-core
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLA_CORE_VERSION', '3.0.0' );
define( 'WLA_CORE_FILE', __FILE__ );
define( 'WLA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once WLA_CORE_DIR . 'includes/schema.php';
require_once WLA_CORE_DIR . 'includes/post-types.php';
require_once WLA_CORE_DIR . 'includes/sections.php';
require_once WLA_CORE_DIR . 'includes/admin-fields.php';
require_once WLA_CORE_DIR . 'includes/admin-sections.php';
require_once WLA_CORE_DIR . 'includes/admin-treatments.php';
require_once WLA_CORE_DIR . 'includes/demo-content.php';
require_once WLA_CORE_DIR . 'includes/importer.php';
require_once WLA_CORE_DIR . 'includes/renderer.php';
require_once WLA_CORE_DIR . 'includes/forms.php';
require_once WLA_CORE_DIR . 'includes/seo.php';
require_once WLA_CORE_DIR . 'includes/admin-seo.php';
require_once WLA_CORE_DIR . 'includes/settings.php';
require_once WLA_CORE_DIR . 'includes/admin.php';
require_once WLA_CORE_DIR . 'includes/compliance.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once WLA_CORE_DIR . 'includes/cli.php';
}

// Elementor integration only loads once Elementor is ready.
add_action(
	'elementor/init',
	function () {
		require_once WLA_CORE_DIR . 'includes/elementor.php';
	}
);

/**
 * Load translations.
 */
function wla_core_load_textdomain() {
	load_plugin_textdomain( 'weightloss-affordable-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wla_core_load_textdomain' );

/**
 * Activation.
 */
function wla_core_activate() {
	wla_register_content_types();
	flush_rewrite_rules();
	if ( ! wp_next_scheduled( 'wla_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'wla_daily_cleanup' );
	}
}
register_activation_hook( __FILE__, 'wla_core_activate' );

/**
 * Deactivation. Data is preserved.
 */
function wla_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wla_core_deactivate' );
