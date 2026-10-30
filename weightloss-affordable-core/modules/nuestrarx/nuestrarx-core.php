<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Constants are declared by WeightLossAffordable Core before this module loads.

require_once NRX_CORE_DIR . 'includes/helpers.php';
require_once NRX_CORE_DIR . 'includes/settings.php';
require_once NRX_CORE_DIR . 'includes/capabilities.php';
require_once NRX_CORE_DIR . 'includes/post-types.php';
require_once NRX_CORE_DIR . 'includes/fields.php';
require_once NRX_CORE_DIR . 'includes/people.php';
require_once NRX_CORE_DIR . 'includes/treatments.php';
require_once NRX_CORE_DIR . 'includes/sections.php';
require_once NRX_CORE_DIR . 'includes/section-renderer.php';
require_once NRX_CORE_DIR . 'includes/quiz/store.php';
require_once NRX_CORE_DIR . 'includes/quiz/render.php';
require_once NRX_CORE_DIR . 'includes/quiz/submit.php';
require_once NRX_CORE_DIR . 'includes/intakes.php';
require_once NRX_CORE_DIR . 'includes/forms.php';
require_once NRX_CORE_DIR . 'includes/integrations.php';
require_once NRX_CORE_DIR . 'includes/seo.php';
require_once NRX_CORE_DIR . 'includes/compliance.php';
require_once NRX_CORE_DIR . 'includes/installer.php';
require_once NRX_CORE_DIR . 'includes/admin.php';

if ( is_admin() ) {
	require_once NRX_CORE_DIR . 'includes/quiz/admin.php';
}

/**
 * Load translations.
 */
function nrx_core_load_textdomain() {
	load_plugin_textdomain( 'nuestrarx-core', false, dirname( NRX_CORE_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'nrx_core_load_textdomain' );

/**
 * Activation: register content types, capabilities and flush rewrite rules.
 *
 * Content is never imported automatically. Seeding is an explicit,
 * idempotent administrator action (NuestraRx -> Setup).
 */
function nrx_core_activate() {
	nrx_register_post_types();
	nrx_grant_capabilities();
	add_option( 'nrx_core_activated_at', current_time( 'mysql' ), '', 'no' );
	flush_rewrite_rules();
}


/**
 * Deactivation: keep data. Only rewrite rules are flushed.
 */
function nrx_core_deactivate() {
	flush_rewrite_rules();
}

