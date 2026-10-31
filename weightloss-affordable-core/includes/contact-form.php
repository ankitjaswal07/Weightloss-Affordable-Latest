<?php
/**
 * Non-clinical general inquiry handler supporting BOTH live AJAX JSON submission
 * (100% same UX as preview!) AND progressive admin-post.php fallback + [wla_contact_form].
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_validate_general_inquiry_request() {
	if ( ! isset( $_POST['wla_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wla_contact_nonce'] ) ), 'wla_general_contact' ) ) {
		return new WP_Error( 'invalid_nonce', 'Invalid security token.' );
	}
	if ( ! empty( $_POST['wla_company'] ) ) {
		return array( 'honeypot' => true );
	}
	$name         = isset( $_POST['wla_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wla_name'] ) ) : '';
	$email        = isset( $_POST['wla_email'] ) ? sanitize_email( wp_unslash( $_POST['wla_email'] ) ) : '';
	$topic        = isset( $_POST['wla_topic'] ) ? sanitize_key( wp_unslash( $_POST['wla_topic'] ) ) : '';
	$consent      = isset( $_POST['wla_consent'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['wla_consent'] ) );
	$valid_topics = array( 'general', 'services', 'language', 'website' );

	if ( '' === $name || ! is_email( $email ) || ! in_array( $topic, $valid_topics, true ) || ! $consent ) {
		return new WP_Error( 'invalid_fields', 'Please check all required fields.' );
	}

	$subject = sprintf( '[%s] General website inquiry (%s)', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $topic );
	$body    = "A general, non-clinical website inquiry was submitted.\n\nName: {$name}\nEmail: {$email}\nTopic: {$topic}\nSubmitted: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	wp_mail( sanitize_email( get_option( 'admin_email' ) ), $subject, $body, $headers );

	return array(
		'name'  => $name,
		'email' => $email,
		'topic' => $topic,
	);
}

function wla_ajax_general_contact_handler() {
	$result = wla_validate_general_inquiry_request();
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
	}
	wp_send_json_success( array( 'ok' => true ) );
}
add_action( 'wp_ajax_nopriv_wla_ajax_general_contact', 'wla_ajax_general_contact_handler' );
add_action( 'wp_ajax_wla_ajax_general_contact', 'wla_ajax_general_contact_handler' );

function wla_handle_general_contact() {
	$referer  = wp_get_referer();
	$redirect = wp_validate_redirect( $referer ? $referer : home_url( '/' ), home_url( '/' ) );
	$result   = wla_validate_general_inquiry_request();
	$status   = is_wp_error( $result ) ? 'invalid' : 'sent';
	wp_safe_redirect( add_query_arg( 'wla_form_status', $status, $redirect ) . '#contact' );
	exit;
}
add_action( 'admin_post_nopriv_wla_general_contact', 'wla_handle_general_contact' );
add_action( 'admin_post_wla_general_contact', 'wla_handle_general_contact' );

function wla_contact_form_shortcode() {
	if ( function_exists( 'wla_render_contact_section' ) ) {
		return wla_render_contact_section();
	}
	return '';
}
add_shortcode( 'wla_contact_form', 'wla_contact_form_shortcode' );
