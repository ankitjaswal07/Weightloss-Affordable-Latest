<?php
/**
 * Theme bootstrap.
 *
 * The theme owns presentation only. Treatments, forms, SEO, sections storage
 * and the demo importer live in the WeightLossAffordable Core plugin, so the
 * site keeps its data if the theme is switched.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLA_THEME_VERSION', '3.0.0' );
define( 'WLA_THEME_DIR', get_template_directory() );
define( 'WLA_THEME_URI', get_template_directory_uri() );

require_once WLA_THEME_DIR . '/inc/setup.php';
require_once WLA_THEME_DIR . '/inc/enqueue.php';
require_once WLA_THEME_DIR . '/inc/customizer.php';
require_once WLA_THEME_DIR . '/inc/template-tags.php';
require_once WLA_THEME_DIR . '/template-parts/sections/helpers.php';
require_once WLA_THEME_DIR . '/inc/sections-template.php';

/**
 * Notice when the companion plugin is missing.
 */
function wla_missing_core_notice() {
	if ( function_exists( 'wla_section_schema' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p></div>',
		esc_html__( 'WeightLossAffordable:', 'weightloss-affordable' ),
		esc_html__( 'install and activate the WeightLossAffordable Core plugin to manage treatments, forms and demo content.', 'weightloss-affordable' )
	);
}
add_action( 'admin_notices', 'wla_missing_core_notice' );
