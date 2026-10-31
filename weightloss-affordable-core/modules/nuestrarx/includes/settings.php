<?php
/**
 * Default settings and sanitization.
 *
 * Content defaults mirror the reference site (nuestrarx.com). Every value
 * is editable in wp-admin and values that make a compliance claim are
 * tracked by the compliance register.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default option values.
 *
 * @return array
 */
function nrx_default_settings() {
	return array(
		// Brand and legal identity.
		'brand_name'          => 'WeightLossAffordable',
		'brand_tagline_es'    => 'Información clara. Un siguiente paso más seguro.',
		'brand_tagline_en'    => 'Clear information. A more confident next step.',
		'legal_name'          => 'WeightLossAffordable',
		'address'             => '',
		'phone_display'       => '',
		'phone_e164'          => '',
		'whatsapp'            => '',
		'contact_email'       => '',
		'privacy_email'       => '',
		'safety_email'        => '',
		'support_email'       => '',
		'instagram_url'       => 'https://instagram.com/nuestra.rx',
		'facebook_url'        => 'https://facebook.com/NuestraRx',
		'legitscript_url'     => 'https://www.legitscript.com/websites/?checker_keywords=nuestrarx.com',

		// Clinical operating model (published facts from the reference site).
		'clinical_partner'    => 'Beluga Health',
		'clinical_partner_url' => 'https://www.belugahealth.com/',
		'clinical_partner_about' => 'https://www.belugahealth.com/about',
		'processing_partner'  => 'NMI',
		'processing_partner_url' => 'https://www.nmi.com/',
		'pharmacy_model'      => 'Farmacias 503A / 503B con licencia en EE. UU.',
		'service_states_note' => '50 estados, Washington D.C. y Puerto Rico',

		// Conversion links.
		'intake_slug'         => 'consultation-eligibility',
		'patient_portal_url'  => '',
		'checkout_url'        => '',
		'payment_page_slug'   => 'weekly-payment',
		'blog_url'            => '/resources/',
		'reschedule_url'      => '',
		'header_cta_label_es' => '¿Califico?',
		'header_cta_label_en' => 'Do I qualify?',
		'header_cta_url'      => '/consultation-eligibility/',

		// Global disclosures.
		'footer_disclaimer'   => 'WeightLossAffordable publishes educational information about medical weight management. This website does not practice medicine, does not issue prescriptions, and does not guarantee eligibility, approval, or results. Compounded GLP-1 medications are not FDA-approved and are not bioequivalent to brand-name products. Clinical decisions belong to a licensed clinician. You must be 18 or older. This content does not replace professional medical advice.',
		'results_disclaimer'  => 'Individual results vary and are not guaranteed. Clinical eligibility and any prescription are determined by a licensed clinician.',
		'testimonials_disclaimer' => 'Do not publish testimonials without documented authorization. Individual experiences vary and are not guaranteed.',
		'compounding_disclaimer' => 'Compounded GLP-1 medications are not FDA-approved and are not bioequivalent to brand-name products. They are prepared by licensed 503A/503B pharmacies only when a licensed clinician determines that compounding is appropriate.',

		// Quiz configuration (editable through the builder as well).
		// Public intake submissions stay disabled until compliance, privacy and handoff are verified.
		'quiz_submissions_enabled' => 1,
		'quiz_min_bmi'        => 25,
		'quiz_require_state'  => 1,
		'states_unavailable'  => array(),
		'quiz_notify_email'   => '',
		'quiz_webhook_url'    => '',
		'quiz_webhook_secret' => '',
		'store_clinical_answers' => 0,
		'intake_retention_days'  => 90,
		'quiz_analytics_events'  => 0,

		// Inquiry form.
		'inquiry_notify_email' => '',
		'inquiry_webhook_url'  => '',

		// Privacy / analytics. All tags are only loaded after consent.
		'analytics_enabled'   => 0,
		'ga_measurement_id'   => '',
		'meta_pixel_id'       => '',
		'tiktok_pixel_id'     => '',
		'privacy_page_url'    => '/privacy-policy/',
		'hipaa_page_url'      => '/privacy-policy/',
		'terms_page_url'      => '/terms-and-conditions/',
		'refund_page_url'     => '/refund-cancellation-policy/',
		'safety_page_url'     => '/treatment-safety-information/',
		'compliance_page_url' => '/medical-disclaimer/',
		'do_not_sell_url'     => '/privacy-policy/',

		// Presentation.
		'default_lang'        => 'en',
		'google_fonts'        => 1,
		'load_parent_motion'  => 0,
		'brand_accent'        => '#d19a67',
		'compliance_approved' => 0,
		'claims_reviewed_at'  => '',
	);
}

/**
 * Sanitize a settings patch coming from the settings screen.
 *
 * @param array $input Raw input.
 * @return array
 */
function nrx_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = nrx_default_settings();
	$out      = array();

	$text_keys = array(
		'brand_name', 'brand_tagline_es', 'brand_tagline_en', 'legal_name', 'address',
		'phone_display', 'phone_e164', 'whatsapp', 'contact_email', 'privacy_email',
		'safety_email', 'support_email', 'clinical_partner', 'processing_partner',
		'pharmacy_model', 'service_states_note', 'intake_slug', 'payment_page_slug',
		'header_cta_label_es', 'header_cta_label_en', 'quiz_notify_email',
		'inquiry_notify_email', 'ga_measurement_id', 'meta_pixel_id', 'tiktok_pixel_id',
		'default_lang', 'brand_accent', 'claims_reviewed_at',
	);
	foreach ( $text_keys as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$value        = 'default_lang' === $key ? sanitize_key( $input[ $key ] ) : sanitize_text_field( $input[ $key ] );
			$out[ $key ]  = $value;
		}
	}

	$email_keys = array( 'contact_email', 'privacy_email', 'safety_email', 'support_email', 'quiz_notify_email', 'inquiry_notify_email' );
	foreach ( $email_keys as $key ) {
		if ( isset( $out[ $key ] ) ) {
			$out[ $key ] = sanitize_email( $out[ $key ] );
		}
	}

	$url_keys = array(
		'instagram_url', 'facebook_url', 'legitscript_url', 'clinical_partner_url',
		'clinical_partner_about', 'processing_partner_url', 'patient_portal_url',
		'checkout_url', 'reschedule_url', 'quiz_webhook_url', 'inquiry_webhook_url',
		'privacy_page_url', 'hipaa_page_url', 'terms_page_url', 'refund_page_url',
		'safety_page_url', 'compliance_page_url', 'do_not_sell_url', 'blog_url',
	);
	foreach ( $url_keys as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$value       = trim( (string) $input[ $key ] );
			$out[ $key ] = ( 0 === strpos( $value, '/' ) ) ? sanitize_text_field( $value ) : esc_url_raw( $value );
		}
	}

	$html_keys = array( 'footer_disclaimer', 'results_disclaimer', 'testimonials_disclaimer', 'compounding_disclaimer' );
	foreach ( $html_keys as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$out[ $key ] = wp_kses_post( $input[ $key ] );
		}
	}

	$bool_keys = array( 'quiz_require_state', 'store_clinical_answers', 'quiz_analytics_events', 'analytics_enabled', 'google_fonts', 'load_parent_motion', 'compliance_approved' );
	foreach ( $bool_keys as $key ) {
		$out[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
	}

	$int_keys = array( 'quiz_min_bmi', 'intake_retention_days' );
	foreach ( $int_keys as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$out[ $key ] = max( 0, (int) $input[ $key ] );
		}
	}

	if ( isset( $input['states_unavailable'] ) ) {
		$states = is_array( $input['states_unavailable'] ) ? $input['states_unavailable'] : array();
		$valid  = array_keys( nrx_states() );
		$out['states_unavailable'] = array_values( array_intersect( array_map( 'strtoupper', array_map( 'sanitize_text_field', $states ) ), $valid ) );
	}

	// Preserve secret without re-echoing it in the form.
	if ( isset( $input['quiz_webhook_secret'] ) && '' !== trim( (string) $input['quiz_webhook_secret'] ) ) {
		$out['quiz_webhook_secret'] = sanitize_text_field( $input['quiz_webhook_secret'] );
	}

	foreach ( $defaults as $key => $default ) {
		if ( ! array_key_exists( $key, $out ) ) {
			$existing     = nrx_settings();
			$out[ $key ]  = array_key_exists( $key, $existing ) ? $existing[ $key ] : $default;
		}
	}

	return $out;
}
