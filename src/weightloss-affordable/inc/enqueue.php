<?php
/**
 * Asset loading.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end styles and scripts.
 */
function wla_enqueue_assets() {
	$ver = WLA_THEME_VERSION;

	wp_enqueue_style(
		'wla-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..600&family=Manrope:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'wla-theme', WLA_THEME_URI . '/assets/css/theme.css', array(), $ver );

	wp_enqueue_script( 'wla-theme', WLA_THEME_URI . '/assets/js/theme.js', array(), $ver, true );
	wp_localize_script(
		'wla-theme',
		'WLA_CONFIG',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'sendingLabel' => __( 'Sending…', 'weightloss-affordable' ),
			'restUrl'      => esc_url_raw( rest_url() ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Font families are also exposed as CSS variables for child themes.
	$css = ':root{--serif:"Fraunces",Georgia,serif;--sans:"Manrope",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}';
	wp_add_inline_style( 'wla-theme', $css );
}
add_action( 'wp_enqueue_scripts', 'wla_enqueue_assets' );

/**
 * Preconnect to the font host.
 *
 * @param array  $urls          URLs to print.
 * @param string $relation_type Relation.
 * @return array
 */
function wla_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'wla_resource_hints', 10, 2 );

/**
 * Editor assets.
 */
function wla_enqueue_editor_assets() {
	wp_enqueue_style( 'wla-editor', WLA_THEME_URI . '/assets/css/editor.css', array(), WLA_THEME_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'wla_enqueue_editor_assets' );
