<?php
/**
 * WeightLossAffordable theme bootstrap.
 *
 * @package WeightLossAffordable
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLA_THEME_VERSION', '2.2.0' );
define( 'WLA_THEME_DIR', get_template_directory() );
define( 'WLA_THEME_URI', get_template_directory_uri() );

require_once WLA_THEME_DIR . '/inc/setup.php';
require_once WLA_THEME_DIR . '/inc/render-helpers.php';
require_once WLA_THEME_DIR . '/inc/home-clinical.php';
require_once WLA_THEME_DIR . '/inc/enqueue.php';
require_once WLA_THEME_DIR . '/inc/template-tags.php';
