<?php
/**
 * Intake records.
 *
 * Privacy model:
 * - Answers are stored encrypted with sodium, including when a webhook is
 *   configured, to avoid loss if delivery fails. Encryption is required.
 * - Answers are also handed to the configured clinical webhook when present.
 * - Free-text answers are always encrypted at rest when stored.
 * - Records are private, excluded from REST, search, sitemaps and feeds, and
 *   are deleted after the configured retention period.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether clinical answers should be stored locally.
 *
 * @param array $config Quiz config.
 * @return string Empty string when not storing, otherwise the reason.
 */
function nrx_intake_storage_reason( $config ) {
	$webhook = trim( (string) ( $config['settings']['webhook_url'] ?? '' ) );
	$webhook = $webhook ? $webhook : (string) nrx_setting( 'quiz_webhook_url', '' );
	if ( ! empty( $config['settings']['store_clinical_answers'] ) || ! empty( nrx_setting( 'store_clinical_answers', 0 ) ) ) {
		return 'admin_enabled';
	}
	if ( '' === $webhook ) {
		return 'no_webhook_configured';
	}
	return '';
}

/**
 * Extract lead fields without exposing clinical free text.
 *
 * @param array $answers Sanitized answers.
 * @return array
 */
function nrx_intake_lead_fields( $answers ) {
	$phone = '';
	$email = '';
	foreach ( array( 'phone', 'telefono', 'tel' ) as $key ) {
		if ( ! empty( $answers[ $key ] ) && is_scalar( $answers[ $key ] ) ) {
			$phone = preg_replace( '/\D/', '', (string) $answers[ $key ] );
			break;
		}
	}
	if ( strlen( $phone ) === 11 && '1' === $phone[0] ) {
		$phone = substr( $phone, 1 );
	}
	if ( ! empty( $answers['email'] ) ) {
		$email = sanitize_email( $answers['email'] );
	}
	return array(
		'phone' => $phone,
		'email' => $email,
		'state' => ! empty( $answers['state'] ) ? strtoupper( sanitize_text_field( $answers['state'] ) ) : '',
	);
}

/**
 * Store a completed evaluation.
 *
 * @param array  $config   Quiz config.
 * @param array  $answers  Sanitized answers.
 * @param string $outcome  Outcome key.
 * @param array  $flags    Clinical flags.
 * @return int Post ID, or 0 on failure.
 */
function nrx_store_intake( $config, $answers, $outcome, $flags ) {
	// Keep a recoverable encrypted copy even when a webhook is configured.
	// Never silently fall back to plaintext or claim success without storage.
	$payload = wp_json_encode( $answers );
	$encrypted = $payload ? nrx_encrypt( $payload ) : '';
	if ( 0 !== strpos( $encrypted, 'nrxenc:' ) && 0 !== strpos( $encrypted, 'nrxb64:' ) ) {
		nrx_log( 'Intake encryption unavailable', array() );
		return 0;
	}
	$lead   = nrx_intake_lead_fields( $answers );
	$reason = nrx_intake_storage_reason( $config );
	$reason = $reason ? $reason : 'webhook_backup';
	$now    = current_time( 'mysql' );
	$title  = sprintf( 'Enquiry %s · %s', $now, nrx_quiz_outcome_labels()[ $outcome ] ?? $outcome );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'nrx_intake',
			'post_status' => 'private',
			'post_title'  => $title,
			'post_author' => 0,
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		nrx_log( 'No se pudo guardar la evaluación', array( 'error' => is_wp_error( $post_id ) ? $post_id->get_error_message() : 'unknown' ) );
		return 0;
	}

	update_post_meta( $post_id, '_nrx_kind', 'quiz' );
	update_post_meta( $post_id, '_nrx_outcome', sanitize_key( $outcome ) );
	update_post_meta( $post_id, '_nrx_flags', $reason ? array_map( 'sanitize_key', (array) $flags ) : array() );
	update_post_meta( $post_id, '_nrx_phone', $lead['phone'] );
	update_post_meta( $post_id, '_nrx_email', $lead['email'] );
	update_post_meta( $post_id, '_nrx_state', $lead['state'] );
	update_post_meta( $post_id, '_nrx_bmi', ! empty( $answers['bmi'] ) ? (float) $answers['bmi'] : '' );
	update_post_meta( $post_id, '_nrx_age_range', sanitize_text_field( (string) ( $answers['age'] ?? '' ) ) );
	update_post_meta( $post_id, '_nrx_sex', sanitize_key( (string) ( $answers['sex'] ?? '' ) ) );
	update_post_meta( $post_id, '_nrx_goal', sanitize_text_field( (string) ( $answers['goal'] ?? '' ) ) );
	if ( ! empty( $answers['selected_treatment'] ) ) {
		update_post_meta( $post_id, '_nrx_treatment', sanitize_title( (string) $answers['selected_treatment'] ) );
	}
	if ( ! empty( $answers['selected_plan'] ) ) {
		update_post_meta( $post_id, '_nrx_plan', sanitize_title( (string) $answers['selected_plan'] ) );
	}
	update_post_meta( $post_id, '_nrx_config_version', (int) ( $config['version'] ?? 0 ) );
	update_post_meta( $post_id, '_nrx_consent_version', sanitize_text_field( (string) ( $config['settings']['consent_version'] ?? '' ) ) );
	update_post_meta( $post_id, '_nrx_consent_at', $now );
	update_post_meta( $post_id, '_nrx_consent_granted', ! empty( $answers['consent'] ) ? 1 : 0 );
	update_post_meta( $post_id, '_nrx_source_url', esc_url_raw( wp_get_referer() ? wp_get_referer() : '' ) );
	update_post_meta( $post_id, '_nrx_ip_hash', nrx_hashed_ip() );
	update_post_meta( $post_id, '_nrx_storage_reason', $reason );
	update_post_meta( $post_id, '_nrx_retention_until', gmdate( 'Y-m-d H:i:s', time() + ( max( 1, (int) nrx_setting( 'intake_retention_days', 90 ) ) * DAY_IN_SECONDS ) ) );
	update_post_meta( $post_id, '_nrx_webhook_status', 'pending' );
	update_post_meta( $post_id, '_nrx_read', 0 );

	// Campaign attribution without personal data.
	$utm = array();
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'nrx_lp' ) as $param ) {
		if ( isset( $_POST[ $param ] ) ) {
			$utm[ $param ] = sanitize_text_field( wp_unslash( $_POST[ $param ] ) );
		}
	}
	if ( $utm ) {
		update_post_meta( $post_id, '_nrx_utm', $utm );
	}

	if ( $reason ) {
		$stored = update_post_meta( $post_id, '_nrx_answers_enc', $encrypted );
		if ( ! $stored || get_post_meta( $post_id, '_nrx_answers_enc', true ) !== $encrypted ) {
			wp_delete_post( $post_id, true );
			nrx_log( 'Intake encrypted storage failed', array() );
			return 0;
		}
		update_post_meta( $post_id, '_nrx_answers_stored', 1 );
	}

	/**
	 * Allow integrations to extend a stored intake.
	 *
	 * @param int   $post_id Intake post ID.
	 * @param array $answers Sanitized answers.
	 * @param array $config  Quiz config.
	 */
	do_action( 'nrx_intake_stored', $post_id, $answers, $config );

	// Hand the case to the clinical destination.
	$webhook_url = trim( (string) ( $config['settings']['webhook_url'] ?? '' ) );
	$webhook_url = $webhook_url ? $webhook_url : (string) nrx_setting( 'quiz_webhook_url', '' );
	$secret      = (string) nrx_setting( 'quiz_webhook_secret', '' );
	$status      = 'skipped';
	if ( $webhook_url ) {
		$result = nrx_send_intake_webhook( $webhook_url, $secret, $post_id, $config, $answers, $outcome, $flags );
		$status = is_wp_error( $result ) ? 'failed' : 'sent';
		if ( is_wp_error( $result ) ) {
			nrx_log( 'Webhook de evaluación falló', array( 'intake' => $post_id, 'error' => $result->get_error_message() ) );
		}
	}
	update_post_meta( $post_id, '_nrx_webhook_status', $status );

	update_post_meta( $post_id, '_nrx_notification_status', nrx_notify_intake( $post_id, $config, $answers, $outcome ) ? 'accepted_by_mailer' : 'failed' );
	nrx_notify_patient_receipt( $post_id, $config, $answers, $outcome );

	return (int) $post_id;
}

/**
 * Send the clinical webhook with HMAC signature.
 *
 * @param string $url      HTTPS endpoint.
 * @param string $secret   HMAC secret.
 * @param int    $post_id  Intake ID.
 * @param array  $config   Config.
 * @param array  $answers  Answers.
 * @param string $outcome  Outcome.
 * @param array  $flags    Flags.
 * @return true|WP_Error
 */
function nrx_send_intake_webhook( $url, $secret, $post_id, $config, $answers, $outcome, $flags ) {
	$url = esc_url_raw( $url );
	if ( 0 !== strpos( $url, 'https://' ) ) {
		return new WP_Error( 'nrx_webhook_insecure', __( 'El webhook debe usar HTTPS.', 'nuestrarx-core' ) );
	}
	if ( '' === $secret ) {
		return new WP_Error( 'nrx_webhook_no_secret', __( 'Falta la clave secreta del webhook.', 'nuestrarx-core' ) );
	}

	$payload = array(
		'schema'     => 'nrx.intake.v1',
		'event'      => 'intake.created',
		'id'         => (int) $post_id,
		'ref'        => 'NRX-' . (int) $post_id,
		'created_at' => current_time( 'c' ),
		'outcome'    => $outcome,
		'flags'      => array_values( (array) $flags ),
		'consent'    => array(
			'version'  => (string) ( $config['settings']['consent_version'] ?? '' ),
			'granted'  => 1,
			'granted_at' => current_time( 'mysql' ),
		),
		'answers'    => $answers,
		'site'       => home_url( '/' ),
	);
	$body = wp_json_encode( $payload );
	$sig  = hash_hmac( 'sha256', $body, $secret );

	$response = wp_remote_post(
		$url,
		array(
			'timeout'     => 8,
			'redirection' => 0,
			'headers'     => array(
				'Content-Type'       => 'application/json; charset=utf-8',
				'X-NuestraRx-Event'  => 'intake.created',
				'X-NuestraRx-Signature' => 'sha256=' . $sig,
				'User-Agent'         => 'NuestraRx-Core/' . NRX_CORE_VERSION,
			),
			'body'        => $body,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'nrx_webhook_status', sprintf( 'HTTP %d', $code ) );
	}
	return true;
}

/**
 * Admin notification with routing data only (no clinical free text).
 *
 * @param int    $post_id Intake.
 * @param array  $config  Config.
 * @param array  $answers Answers.
 * @param string $outcome Outcome.
 */
function nrx_notify_intake( $post_id, $config, $answers, $outcome ) {
	$to = sanitize_email( (string) ( $config['settings']['notify_email'] ?? '' ) );
	if ( ! $to ) {
		$to = sanitize_email( (string) nrx_setting( 'quiz_notify_email', '' ) );
	}
	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}
	$labels = nrx_quiz_outcome_labels();
	$lead   = nrx_intake_lead_fields( $answers );
	$lines  = array(
		sprintf( 'Nueva evaluación %s', 'NRX-' . $post_id ),
		'',
		sprintf( 'Resultado inicial: %s', $labels[ $outcome ] ?? $outcome ),
		sprintf( 'Estado: %s', $lead['state'] ? $lead['state'] : '—' ),
		sprintf( 'Teléfono: %s', $lead['phone'] ? $lead['phone'] : '—' ),
		sprintf( 'Email: %s', $lead['email'] ? $lead['email'] : '—' ),
		sprintf( 'Consentimiento: %s', ! empty( $answers['consent'] ) ? 'sí' : 'no' ),
		'',
		'Abre el registro en el panel (acceso restringido):',
		admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
		'',
		'Este correo no incluye respuestas clínicas.',
	);
	return wp_mail(
		$to,
		sprintf( '[NuestraRx] Evaluación %s — %s', 'NRX-' . $post_id, $labels[ $outcome ] ?? $outcome ),
		implode( "\n", $lines ),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);
}

/**
 * Optional receipt to the patient when an email was collected.
 *
 * @param int    $post_id Intake.
 * @param array  $config  Config.
 * @param array  $answers Answers.
 * @param string $outcome Outcome.
 */
function nrx_notify_patient_receipt( $post_id, $config, $answers, $outcome ) {
	$email = ! empty( $answers['email'] ) ? sanitize_email( $answers['email'] ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		return;
	}
	$body = sprintf(
		"Hello,\n\nWe received your enquiry (reference NRX-%d). Submission does not guarantee treatment or approval.\n\nIf you have questions, contact %s.",
		$post_id,
		nrx_setting( 'contact_email', get_option( 'admin_email' ) )
	);
	wp_mail( $email, __( 'We received your enquiry', 'nuestrarx-core' ), $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
}

/* -------------------------------------------------------------------------
 * Admin UI
 * ---------------------------------------------------------------------- */

/**
 * Intake list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function nrx_intake_columns( $columns ) {
	return array(
		'cb'            => $columns['cb'] ?? '',
		'title'         => __( 'Evaluación', 'nuestrarx-core' ),
		'nrx_outcome'   => __( 'Resultado', 'nuestrarx-core' ),
		'nrx_state'     => __( 'Estado', 'nuestrarx-core' ),
		'nrx_contact'   => __( 'Contacto', 'nuestrarx-core' ),
		'nrx_consent'   => __( 'Consentimiento', 'nuestrarx-core' ),
		'nrx_webhook'   => __( 'Integración', 'nuestrarx-core' ),
		'date'          => __( 'Fecha', 'nuestrarx-core' ),
	);
}
add_filter( 'manage_nrx_intake_posts_columns', 'nrx_intake_columns' );

/**
 * Render intake columns.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function nrx_intake_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'nrx_outcome':
			$outcome = get_post_meta( $post_id, '_nrx_outcome', true );
			$labels  = nrx_quiz_outcome_labels();
			echo '<span class="nrx-pill nrx-outcome-' . esc_attr( $outcome ) . '">' . esc_html( $labels[ $outcome ] ?? $outcome ) . '</span>';
			break;
		case 'nrx_state':
			echo esc_html( get_post_meta( $post_id, '_nrx_state', true ) );
			break;
		case 'nrx_contact':
			$phone = get_post_meta( $post_id, '_nrx_phone', true );
			$email = get_post_meta( $post_id, '_nrx_email', true );
			echo $phone ? '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>' : '';
			echo $email ? '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '';
			break;
		case 'nrx_consent':
			echo get_post_meta( $post_id, '_nrx_consent_granted', true ) ? esc_html__( 'Sí', 'nuestrarx-core' ) : esc_html__( 'No', 'nuestrarx-core' );
			break;
		case 'nrx_webhook':
			$status = get_post_meta( $post_id, '_nrx_webhook_status', true );
			$map    = array( 'sent' => 'Enviado', 'failed' => 'Falló', 'pending' => 'Pendiente', 'skipped' => 'Sin webhook' );
			echo esc_html( $map[ $status ] ?? ( $status ? $status : '—' ) );
			break;
	}
}
add_action( 'manage_nrx_intake_posts_custom_column', 'nrx_intake_column_content', 10, 2 );

/**
 * Intake detail meta box.
 */
function nrx_register_intake_meta_boxes() {
	add_meta_box( 'nrx_intake_detail', __( 'Detalle de la evaluación', 'nuestrarx-core' ), 'nrx_intake_detail_box', 'nrx_intake', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nrx_register_intake_meta_boxes' );

/**
 * Render intake details.
 *
 * @param WP_Post $post Intake.
 */
function nrx_intake_detail_box( $post ) {
	if ( ! current_user_can( 'nrx_manage_intakes' ) && ! current_user_can( 'manage_options' ) ) {
		echo '<p>' . esc_html__( 'No tienes permiso para ver esta evaluación.', 'nuestrarx-core' ) . '</p>';
		return;
	}
	$labels  = nrx_quiz_outcome_labels();
	$outcome = get_post_meta( $post->ID, '_nrx_outcome', true );
	$stored  = get_post_meta( $post->ID, '_nrx_answers_stored', true );
	$reason  = get_post_meta( $post->ID, '_nrx_storage_reason', true );
	$webhook = get_post_meta( $post->ID, '_nrx_webhook_status', true );
	echo '<div class="nrx-admin">';
	echo '<table class="widefat striped nrx-intake-table"><tbody>';
	$rows = array(
		__( 'Referencia', 'nuestrarx-core' )    => 'NRX-' . $post->ID,
		__( 'Resultado', 'nuestrarx-core' )     => $labels[ $outcome ] ?? $outcome,
		__( 'Estado', 'nuestrarx-core' )        => get_post_meta( $post->ID, '_nrx_state', true ),
		__( 'Teléfono', 'nuestrarx-core' )      => get_post_meta( $post->ID, '_nrx_phone', true ),
		__( 'Email', 'nuestrarx-core' )         => get_post_meta( $post->ID, '_nrx_email', true ),
		__( 'IMC', 'nuestrarx-core' )           => get_post_meta( $post->ID, '_nrx_bmi', true ),
		__( 'Edad', 'nuestrarx-core' )          => get_post_meta( $post->ID, '_nrx_age_range', true ),
		__( 'Meta', 'nuestrarx-core' )          => get_post_meta( $post->ID, '_nrx_goal', true ),
		__( 'Consentimiento', 'nuestrarx-core' ) => ( get_post_meta( $post->ID, '_nrx_consent_granted', true ) ? __( 'Otorgado', 'nuestrarx-core' ) : __( 'No otorgado', 'nuestrarx-core' ) ) . ' · v' . get_post_meta( $post->ID, '_nrx_consent_version', true ),
		__( 'Webhook', 'nuestrarx-core' )       => $webhook,
		__( 'Retención', 'nuestrarx-core' )     => get_post_meta( $post->ID, '_nrx_retention_until', true ),
	);
	foreach ( $rows as $label => $value ) {
		printf( '<tr><th style="width:180px">%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( (string) $value ) );
	}
	echo '</tbody></table>';

	echo '<h3>' . esc_html__( 'Respuestas clínicas', 'nuestrarx-core' ) . '</h3>';
	if ( $stored ) {
		$enc     = get_post_meta( $post->ID, '_nrx_answers_enc', true );
		$decoded = json_decode( nrx_decrypt( $enc ), true );
		echo '<p class="nrx-field-help">' . esc_html__( 'Almacenadas cifradas. Motivo:', 'nuestrarx-core' ) . ' ' . esc_html( $reason ) . '.</p>';
		if ( is_array( $decoded ) ) {
			echo '<table class="widefat striped nrx-intake-table"><tbody>';
			foreach ( $decoded as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( ', ', array_map( 'sanitize_text_field', $value ) );
				}
				printf( '<tr><th style="width:220px">%s</th><td>%s</td></tr>', esc_html( $key ), esc_html( (string) $value ) );
			}
			echo '</tbody></table>';
		}
	} else {
		echo '<p>' . esc_html__( 'Las respuestas clínicas no se almacenan en WordPress. Se enviaron al destino clínico configurado. Esto reduce la exposición de datos de salud.', 'nuestrarx-core' ) . '</p>';
	}
	echo '</div>';
}

/**
 * Mark as read when opened.
 *
 * @param WP_Post $post Post.
 */
function nrx_mark_intake_read( $post ) {
	if ( ! $post || 'nrx_intake' !== $post->post_type || ! current_user_can( 'nrx_manage_intakes' ) ) {
		return;
	}
	update_post_meta( $post->ID, '_nrx_read', 1 );
}
add_action( 'edit_form_top', 'nrx_mark_intake_read' );

/**
 * Filter intakes by outcome.
 *
 * @param string $post_type Post type.
 */
function nrx_intake_filters( $post_type ) {
	if ( 'nrx_intake' !== $post_type || ! current_user_can( 'nrx_manage_intakes' ) ) {
		return;
	}
	$current = isset( $_GET['nrx_outcome'] ) ? sanitize_key( wp_unslash( $_GET['nrx_outcome'] ) ) : '';
	echo '<select name="nrx_outcome"><option value="">' . esc_html__( 'Todos los resultados', 'nuestrarx-core' ) . '</option>';
	foreach ( nrx_quiz_outcome_labels() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'nrx_intake_filters' );

/**
 * Apply intake filters.
 *
 * @param WP_Query $query Query.
 */
function nrx_intake_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'nrx_intake' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( ! empty( $_GET['nrx_outcome'] ) ) {
		$query->set( 'meta_key', '_nrx_outcome' );
		$query->set( 'meta_value', sanitize_key( wp_unslash( $_GET['nrx_outcome'] ) ) );
	}
}
add_action( 'pre_get_posts', 'nrx_intake_filter_query' );

/* -------------------------------------------------------------------------
 * Retention and export
 * ---------------------------------------------------------------------- */

/**
 * Schedule the retention cron on init.
 */
function nrx_schedule_retention() {
	if ( ! wp_next_scheduled( 'nrx_intake_retention' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'nrx_intake_retention' );
	}
}
add_action( 'init', 'nrx_schedule_retention' );

/**
 * Delete expired intakes.
 */
function nrx_run_retention_cleanup() {
	$days = max( 1, (int) nrx_setting( 'intake_retention_days', 90 ) );
	$ids  = get_posts(
		array(
			'post_type'      => 'nrx_intake',
			'post_status'    => 'any',
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'date_query'     => array(
				array(
					'before' => gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) ),
					'column' => 'post_date_gmt',
				),
			),
		)
	);
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	if ( $ids ) {
		nrx_log( 'Limpieza de retención', array( 'deleted' => count( $ids ) ) );
	}
}
add_action( 'nrx_intake_retention', 'nrx_run_retention_cleanup' );

/**
 * Export intake routing data as CSV (no clinical answers).
 */
function nrx_export_intakes() {
	nrx_verify_admin_request( 'nrx_export_intakes', 'nrx_export_intakes' );
	$intakes = get_posts(
		array(
			'post_type'      => 'nrx_intake',
			'post_status'    => 'any',
			'posts_per_page' => 5000,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=nuestrarx-evaluaciones-' . gmdate( 'Y-m-d' ) . '.csv' );
	header( 'X-Content-Type-Options: nosniff' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'ref', 'fecha', 'resultado', 'estado', 'telefono', 'email', 'bmi', 'consentimiento', 'webhook' ) );
	$labels = nrx_quiz_outcome_labels();
	foreach ( $intakes as $intake ) {
		fputcsv(
			$out,
			array(
				'NRX-' . $intake->ID,
				$intake->post_date,
				$labels[ get_post_meta( $intake->ID, '_nrx_outcome', true ) ] ?? '',
				get_post_meta( $intake->ID, '_nrx_state', true ),
				get_post_meta( $intake->ID, '_nrx_phone', true ),
				get_post_meta( $intake->ID, '_nrx_email', true ),
				get_post_meta( $intake->ID, '_nrx_bmi', true ),
				get_post_meta( $intake->ID, '_nrx_consent_granted', true ) ? 'si' : 'no',
				get_post_meta( $intake->ID, '_nrx_webhook_status', true ),
			)
		);
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_nrx_export_intakes', 'nrx_export_intakes' );

/**
 * Add an export button to the intake list.
 */
function nrx_intake_export_button() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-nrx_intake' !== $screen->id || ! current_user_can( 'nrx_export_intakes' ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=nrx_export_intakes' ), 'nrx_export_intakes' );
	echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Exportar CSV (sin datos clínicos)', 'nuestrarx-core' ) . '</a>';
}
add_action( 'manage_posts_extra_tablenav', 'nrx_intake_export_button' );
