<?php
/**
 * Enquiry and consultation forms.
 *
 * Works with and without JavaScript:
 *  - no JS: a normal POST to admin-post.php that redirects back with a status,
 *  - with JS: the same endpoint via admin-ajax.php, submitted in place.
 *
 * Submissions are stored as private `wla_submission` records, optionally
 * emailed, rate limited, and purged after the configured retention period.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nonce action used by both endpoints.
 */
const WLA_FORM_NONCE = 'wla_form_submit';

/**
 * Validate and store a submission.
 *
 * @return array{success:bool,message:string,reference:string}
 */
function wla_handle_form_submission() {
	$settings = wla_get_settings();

	$data = array(
		'form_type'      => isset( $_POST['wla_form_type'] ) ? sanitize_key( wp_unslash( $_POST['wla_form_type'] ) ) : 'general',
		'name'           => isset( $_POST['wla_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wla_name'] ) ) : '',
		'email'          => isset( $_POST['wla_email'] ) ? sanitize_email( wp_unslash( $_POST['wla_email'] ) ) : '',
		'phone'          => isset( $_POST['wla_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wla_phone'] ) ) : '',
		'state'          => isset( $_POST['wla_state'] ) ? sanitize_text_field( wp_unslash( $_POST['wla_state'] ) ) : '',
		'contact_method' => isset( $_POST['wla_contact_method'] ) ? sanitize_key( wp_unslash( $_POST['wla_contact_method'] ) ) : 'email',
		'topic'          => isset( $_POST['wla_topic'] ) ? sanitize_key( wp_unslash( $_POST['wla_topic'] ) ) : 'general',
		'message'        => isset( $_POST['wla_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wla_message'] ) ) : '',
		'source_url'     => isset( $_POST['wla_source_url'] ) ? esc_url_raw( wp_unslash( $_POST['wla_source_url'] ) ) : '',
		'consent'        => ( isset( $_POST['wla_consent'] ) && '' !== (string) wp_unslash( $_POST['wla_consent'] ) ) ? '1' : '',
	);

	if ( ! in_array( $data['form_type'], array( 'general', 'consultation' ), true ) ) {
		$data['form_type'] = 'general';
	}

	$error = new WP_Error();

	// Spam: honeypot must be empty.
	if ( ! empty( $_POST['wla_company'] ) ) {
		$error->add( 'spam', __( 'Your message could not be sent.', 'weightloss-affordable-core' ) );
	}

	// Spam: submissions faster than the configured minimum are rejected.
	$started = isset( $_POST['wla_started_at'] ) ? absint( $_POST['wla_started_at'] ) : 0;
	$min     = max( 1, (int) $settings['spam_min_seconds'] );
	if ( $started && ( time() - $started ) < $min ) {
		$error->add( 'too_fast', __( 'Please take a moment to review your message before sending.', 'weightloss-affordable-core' ) );
	}

	// Spam: simple per-IP rate limit.
	$ip_hash = wla_client_ip_hash();
	$key     = 'wla_rate_' . $ip_hash;
	$count   = (int) get_transient( $key );
	if ( $count >= 5 ) {
		$error->add( 'rate_limited', __( 'Too many messages have been sent from this connection. Please try again later.', 'weightloss-affordable-core' ) );
	}

	if ( '' === $data['name'] ) {
		$error->add( 'name', __( 'Please enter your name.', 'weightloss-affordable-core' ) );
	}
	if ( ! is_email( $data['email'] ) ) {
		$error->add( 'email', __( 'Please enter a valid email address.', 'weightloss-affordable-core' ) );
	}
	if ( '' === $data['message'] || strlen( $data['message'] ) < 10 ) {
		$error->add( 'message', __( 'Please add a short message (at least 10 characters).', 'weightloss-affordable-core' ) );
	}
	if ( 'consultation' === $data['form_type'] && '' === $data['state'] ) {
		$error->add( 'state', __( 'Please tell us your state so we can confirm where care is available.', 'weightloss-affordable-core' ) );
	}
	if ( '1' !== $data['consent'] ) {
		$error->add( 'consent', __( 'Please confirm you agree to the privacy notice.', 'weightloss-affordable-core' ) );
	}

	/**
	 * Allow additional validation.
	 *
	 * @param WP_Error $error Validation errors.
	 * @param array    $data  Sanitised submission.
	 */
	$error = apply_filters( 'wla_form_validation', $error, $data );

	if ( $error->has_errors() ) {
		return array(
			'success'   => false,
			'message'   => implode( ' ', array_unique( $error->get_error_messages() ) ),
			'reference' => '',
		);
	}

	$reference = 'WLA-' . strtoupper( substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 6 ) );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'wla_submission',
			'post_status' => 'private',
			'post_title'  => sprintf(
				/* translators: 1: name, 2: reference, 3: date */
				__( '%1$s — %2$s — %3$s', 'weightloss-affordable-core' ),
				$data['name'],
				$reference,
				current_time( 'Y-m-d H:i' )
			),
			'meta_input'  => array(
				'_wla_form_type'   => $data['form_type'],
				'_wla_name'        => $data['name'],
				'_wla_email'       => $data['email'],
				'_wla_phone'       => $data['phone'],
				'_wla_state'       => $data['state'],
				'_wla_method'      => $data['contact_method'],
				'_wla_topic'       => $data['topic'],
				'_wla_message'     => $data['message'],
				'_wla_source_url'  => $data['source_url'],
				'_wla_consent'     => '1',
				'_wla_reference'   => $reference,
				'_wla_ip_hash'     => $ip_hash,
				'_wla_user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 190 ) : '',
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return array(
			'success'   => false,
			'message'   => __( 'We could not save your message. Please try again, or email us directly.', 'weightloss-affordable-core' ),
			'reference' => '',
		);
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	wla_notify_form_submission( $post_id, $data, $reference );

	/**
	 * Fires after a successful form submission.
	 *
	 * @param int   $post_id   Submission post ID.
	 * @param array $data      Submission data.
	 * @param string $reference Reference code.
	 */
	do_action( 'wla_form_submitted', $post_id, $data, $reference );

	return array(
		'success'   => true,
		'message'   => __( 'Thank you — your message has been received. A member of the team will reply within 1–2 business days.', 'weightloss-affordable-core' ),
		'reference' => $reference,
	);
}

/**
 * Pseudonymised client IP (never the raw address).
 *
 * @return string
 */
function wla_client_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 32 );
}

/**
 * Email the team about a new submission.
 *
 * @param int    $post_id   Submission ID.
 * @param array  $data      Data.
 * @param string $reference Reference.
 * @return void
 */
function wla_notify_form_submission( $post_id, $data, $reference ) {
	$settings = wla_get_settings();
	if ( '1' !== (string) $settings['notify_enabled'] ) {
		return;
	}

	$recipients = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', (string) $settings['notification_emails'] ) ) ) );
	if ( empty( $recipients ) ) {
		return;
	}

	$subject = sprintf(
		/* translators: 1: form type, 2: reference */
		__( 'New %1$s enquiry (%2$s)', 'weightloss-affordable-core' ),
		$data['form_type'],
		$reference
	);

	$lines = array(
		sprintf( __( 'Reference: %s', 'weightloss-affordable-core' ), $reference ),
		sprintf( __( 'Type: %s', 'weightloss-affordable-core' ), $data['form_type'] ),
		sprintf( __( 'Name: %s', 'weightloss-affordable-core' ), $data['name'] ),
		sprintf( __( 'Email: %s', 'weightloss-affordable-core' ), $data['email'] ),
	);
	if ( $data['phone'] ) {
		$lines[] = sprintf( __( 'Phone: %s', 'weightloss-affordable-core' ), $data['phone'] );
	}
	if ( $data['state'] ) {
		$lines[] = sprintf( __( 'State: %s', 'weightloss-affordable-core' ), $data['state'] );
	}
	$lines[] = sprintf( __( 'Topic: %s', 'weightloss-affordable-core' ), $data['topic'] );
	$lines[] = '';
	$lines[] = __( 'Message:', 'weightloss-affordable-core' );
	$lines[] = $data['message'];
	$lines[] = '';
	$lines[] = sprintf( __( 'View in WordPress: %s', 'weightloss-affordable-core' ), admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );

	wp_mail( $recipients, $subject, implode( "\n", $lines ) );
}

/**
 * admin-post handler: submit and redirect back.
 *
 * @return void
 */
function wla_handle_form_post() {
	$nonce = isset( $_POST['wla_form_nonce'] ) ? sanitize_key( wp_unslash( $_POST['wla_form_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, WLA_FORM_NONCE ) ) {
		wp_safe_redirect( add_query_arg( 'wla_form_status', 'invalid', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
		exit;
	}

	$result  = wla_handle_form_submission();
	$referer = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$referer = remove_query_arg( array( 'wla_form_status', 'wla_ref' ), $referer );

	if ( $result['success'] ) {
		$referer = add_query_arg(
			array(
				'wla_form_status' => 'sent',
				'wla_ref'         => rawurlencode( $result['reference'] ),
			),
			$referer
		);
		$referer = $referer . '#contact';
	} else {
		$referer = add_query_arg( 'wla_form_status', 'invalid', $referer ) . '#contact';
	}

	wp_safe_redirect( $referer );
	exit;
}
add_action( 'admin_post_nopriv_wla_general_form', 'wla_handle_form_post' );
add_action( 'admin_post_wla_general_form', 'wla_handle_form_post' );
add_action( 'admin_post_nopriv_wla_consultation_form', 'wla_handle_form_post' );
add_action( 'admin_post_wla_consultation_form', 'wla_handle_form_post' );

/**
 * admin-ajax handler: JSON response for the enhanced form.
 *
 * @return void
 */
function wla_handle_form_ajax() {
	$nonce = isset( $_POST['wla_form_nonce'] ) ? sanitize_key( wp_unslash( $_POST['wla_form_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, WLA_FORM_NONCE ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Please reload the page and try again.', 'weightloss-affordable-core' ) ), 403 );
	}

	$result = wla_handle_form_submission();
	if ( $result['success'] ) {
		wp_send_json_success( array( 'message' => $result['message'], 'reference' => $result['reference'] ) );
	}
	wp_send_json_error( array( 'message' => $result['message'] ), 422 );
}
add_action( 'wp_ajax_wla_ajax_form', 'wla_handle_form_ajax' );
add_action( 'wp_ajax_nopriv_wla_ajax_form', 'wla_handle_form_ajax' );

/**
 * Show the submission details on the enquiry edit screen.
 */
function wla_add_submission_meta_box() {
	add_meta_box(
		'wla_submission_details',
		__( 'Enquiry details', 'weightloss-affordable-core' ),
		'wla_render_submission_meta_box',
		'wla_submission',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wla_add_submission_meta_box' );

/**
 * Render submission details (read-only).
 *
 * @param WP_Post $post Post.
 * @return void
 */
function wla_render_submission_meta_box( $post ) {
	$fields = array(
		'_wla_reference'  => __( 'Reference', 'weightloss-affordable-core' ),
		'_wla_form_type'  => __( 'Form type', 'weightloss-affordable-core' ),
		'_wla_name'       => __( 'Name', 'weightloss-affordable-core' ),
		'_wla_email'      => __( 'Email', 'weightloss-affordable-core' ),
		'_wla_phone'      => __( 'Phone', 'weightloss-affordable-core' ),
		'_wla_state'      => __( 'State', 'weightloss-affordable-core' ),
		'_wla_method'     => __( 'Preferred contact method', 'weightloss-affordable-core' ),
		'_wla_topic'      => __( 'Topic', 'weightloss-affordable-core' ),
		'_wla_message'    => __( 'Message', 'weightloss-affordable-core' ),
		'_wla_source_url' => __( 'Submitted from', 'weightloss-affordable-core' ),
	);
	echo '<table class="widefat striped"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf(
			'<tr><th style="width:200px">%1$s</th><td>%2$s</td></tr>',
			esc_html( $label ),
			nl2br( esc_html( (string) $value ) )
		);
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'Enquiries are stored privately and deleted automatically after the retention period set in Settings.', 'weightloss-affordable-core' ) . '</p>';
}

/**
 * Add useful admin columns for submissions.
 *
 * @param array $columns Columns.
 * @return array
 */
function wla_submission_columns( $columns ) {
	return array(
		'cb'        => $columns['cb'] ?? '',
		'title'     => __( 'Enquiry', 'weightloss-affordable-core' ),
		'wla_email' => __( 'Email', 'weightloss-affordable-core' ),
		'wla_type'  => __( 'Type', 'weightloss-affordable-core' ),
		'date'      => __( 'Received', 'weightloss-affordable-core' ),
	);
}
add_filter( 'manage_wla_submission_posts_columns', 'wla_submission_columns' );

/**
 * Render submission columns.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 * @return void
 */
function wla_submission_column_content( $column, $post_id ) {
	if ( 'wla_email' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_wla_email', true ) );
	}
	if ( 'wla_type' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_wla_form_type', true ) );
	}
}
add_action( 'manage_wla_submission_posts_custom_column', 'wla_submission_column_content', 10, 2 );

/**
 * Daily retention cleanup.
 */
function wla_purge_old_submissions() {
	$days = (int) wla_get_settings( 'retention_days' );
	if ( $days < 1 ) {
		return;
	}
	$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
	$old    = get_posts(
		array(
			'post_type'      => 'wla_submission',
			'post_status'    => 'any',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'date_query'     => array(
				array(
					'column' => 'post_date_gmt',
					'before' => $cutoff,
				),
			),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'wla_daily_cleanup', 'wla_purge_old_submissions' );

/**
 * Schedule the cleanup job.
 */
function wla_schedule_cleanup() {
	if ( ! wp_next_scheduled( 'wla_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'wla_daily_cleanup' );
	}
}
add_action( 'init', 'wla_schedule_cleanup' );

/**
 * Clear the scheduled job on deactivation.
 */
function wla_unschedule_cleanup() {
	wp_clear_scheduled_hook( 'wla_daily_cleanup' );
}
