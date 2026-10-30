<?php
/**
 * Enqueue exact Fraunces + Manrope typography, 100% shared CSS design system,
 * bidirectional live EN/ES translator data, and interactive motion engine.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_google_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Manrope:wght@400;500;600;700;800&display=swap';
}

function wla_enqueue_assets() {
	wp_enqueue_style(
		'weightloss-affordable-fonts',
		wla_google_fonts_url(),
		array(),
		null
	);
	wp_enqueue_style(
		'weightloss-affordable-style',
		get_stylesheet_uri(),
		array( 'weightloss-affordable-fonts' ),
		WLA_THEME_VERSION
	);
	wp_enqueue_style(
		'weightloss-affordable-main',
		WLA_THEME_URI . '/assets/css/theme.css',
		array( 'weightloss-affordable-style' ),
		WLA_THEME_VERSION
	);

	wp_enqueue_script(
		'weightloss-affordable-translations',
		WLA_THEME_URI . '/assets/js/translations-data.js',
		array(),
		WLA_THEME_VERSION,
		true
	);
	wp_enqueue_script(
		'weightloss-affordable-main',
		WLA_THEME_URI . '/assets/js/theme.js',
		array( 'weightloss-affordable-translations' ),
		WLA_THEME_VERSION,
		true
	);

	$custom_overrides = get_option( 'wla_translator_overrides', array() );
	$cached_pairs     = get_option( 'wla_translation_cache', array() );

	wp_localize_script(
		'weightloss-affordable-main',
		'WLA_CONFIG',
		array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'wla_site_nonce' ),
			'defaultLang'     => sanitize_key( get_option( 'wla_default_lang', 'en' ) ),
			'homeUrl'         => home_url( '/' ),
			'customOverrides' => is_array( $custom_overrides ) ? $custom_overrides : array(),
			'cachedPairs'     => is_array( $cached_pairs ) ? $cached_pairs : array(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wla_enqueue_assets' );

function wla_editor_assets() {
	wp_enqueue_style( 'weightloss-affordable-editor-fonts', wla_google_fonts_url(), array(), null );
	wp_enqueue_style( 'weightloss-affordable-editor', WLA_THEME_URI . '/assets/css/editor.css', array( 'weightloss-affordable-editor-fonts' ), WLA_THEME_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'wla_editor_assets' );
add_action( 'elementor/editor/after_enqueue_styles', 'wla_enqueue_assets' );
