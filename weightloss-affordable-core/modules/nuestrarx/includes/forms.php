<?php
/**
 * General inquiry form (patients, partners and clinicians).
 *
 * Messages are treated as potentially sensitive: stored encrypted, never
 * included in notification emails, and covered by the intake retention rule.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the inquiry form.
 *
 * @param array $args Variant and heading overrides.
 * @return string
 */
function nrx_inquiry_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'variant' => 'patient',
			'title'   => __( 'Escríbenos', 'nuestrarx-core' ),
			'intro'   => __( 'Te respondemos en español. No incluyas información médica sensible si no es necesario.', 'nuestrarx-core' ),
		)
	);
	$topics = 'provider' === $args['variant']
		? array(
			'professional' => __( 'Consulta profesional / referencia', 'nuestrarx-core' ),
			'pharmacy'     => __( 'Farmacia o laboratorio', 'nuestrarx-core' ),
			'partnership'  => __( 'Alianza o prensa', 'nuestrarx-core' ),
			'other'        => __( 'Otro', 'nuestrarx-core' ),
		)
		: array(
			'evaluation' => __( 'Pregunta sobre mi evaluación', 'nuestrarx-core' ),
			'plan'       => __( 'Pregunta sobre mi plan o pago', 'nuestrarx-core' ),
			'support'    => __( 'Soporte de mi tratamiento', 'nuestrarx-core' ),
			'other'      => __( 'Otro', 'nuestrarx-core' ),
		);

	if ( ! wp_script_is( 'nrx-forms', 'enqueued' ) ) {
		wp_enqueue_script( 'nrx-forms', NRX_CORE_URL . 'assets/js/forms.js', array(), NRX_CORE_VERSION, true );
		wp_localize_script(
			'nrx-forms',
			'NRX_FORMS',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nrx_inquiry' ),
				'i18n'    => array(
					'sending' => __( 'Enviando…', 'nuestrarx-core' ),
					'invalid' => __( 'Revisa los campos marcados: faltan datos obligatorios.', 'nuestrarx-core' ),
					'error'   => __( 'No pudimos enviar tu mensaje. Inténtalo de nuevo o escríbenos por WhatsApp.', 'nuestrarx-core' ),
					'thanks'  => __( 'Gracias. Recibimos tu mensaje.', 'nuestrarx-core' ),
				),
			)
		);
	}

	ob_start();
	?>
	<div class="nrx-inquiry" data-nrx-inquiry>
		<h3 class="nrx-inquiry-title"><?php echo esc_html( $args['title'] ); ?></h3>
		<p class="nrx-inquiry-intro"><?php echo esc_html( $args['intro'] ); ?></p>
		<form class="nrx-inquiry-form" data-nrx-inquiry-form novalidate>
			<input type="hidden" name="variant" value="<?php echo esc_attr( $args['variant'] ); ?>">
			<input type="text" name="nrx_hp" class="nrx-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<div class="nrx-form-row">
				<label class="nrx-form-field">
					<span><?php esc_html_e( 'Nombre', 'nuestrarx-core' ); ?> <span class="nrx-req">*</span></span>
					<input type="text" name="name" autocomplete="name" required maxlength="120">
				</label>
				<label class="nrx-form-field">
					<span><?php esc_html_e( 'Email', 'nuestrarx-core' ); ?> <span class="nrx-req">*</span></span>
					<input type="email" name="email" autocomplete="email" required maxlength="160">
				</label>
			</div>
			<div class="nrx-form-row">
				<label class="nrx-form-field">
					<span><?php esc_html_e( 'Teléfono (opcional)', 'nuestrarx-core' ); ?></span>
					<input type="tel" name="phone" autocomplete="tel" maxlength="30">
				</label>
				<label class="nrx-form-field">
					<span><?php esc_html_e( 'Motivo', 'nuestrarx-core' ); ?></span>
					<select name="topic">
						<?php foreach ( $topics as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<label class="nrx-form-field nrx-form-field-full">
				<span><?php esc_html_e( 'Mensaje', 'nuestrarx-core' ); ?> <span class="nrx-req">*</span></span>
				<textarea name="message" rows="4" required maxlength="2000"></textarea>
			</label>
			<label class="nrx-form-consent">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php esc_html_e( 'Acepto la política de privacidad y que NuestraRx me contacte sobre esta consulta.', 'nuestrarx-core' ); ?> <a href="<?php echo esc_url( nrx_url( '/politica-de-privacidad/' ) ); ?>"><?php esc_html_e( 'Ver política', 'nuestrarx-core' ); ?></a></span>
			</label>
			<div class="nrx-form-error" role="alert" data-nrx-form-error hidden></div>
			<div class="nrx-form-success" role="status" data-nrx-form-success hidden></div>
			<button type="submit" class="button button-primary nrx-button" data-nrx-submit>
				<span data-nrx-submit-label><?php esc_html_e( 'Enviar mensaje', 'nuestrarx-core' ); ?></span>
				<span class="button-arrow"><?php echo nrx_icon( 'arrow', 18 ); ?></span>
			</button>
			<p class="nrx-form-note"><?php echo nrx_icon( 'lock', 14 ); ?> <?php esc_html_e( 'No compartimos tus datos. Las respuestas se almacenan cifradas.', 'nuestrarx-core' ); ?></p>
		</form>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Shortcode: [nrx_inquiry_form variant="patient" title="..."]
 *
 * @param array $atts Attributes.
 * @return string
 */
function nrx_inquiry_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'variant' => 'patient', 'title' => '' ), $atts, 'nrx_inquiry_form' );
	return nrx_inquiry_form(
		array(
			'variant' => 'provider' === $atts['variant'] ? 'provider' : 'patient',
			'title'   => $atts['title'] ? sanitize_text_field( $atts['title'] ) : __( 'Escríbenos', 'nuestrarx-core' ),
		)
	);
}
add_shortcode( 'nrx_inquiry_form', 'nrx_inquiry_shortcode' );

/**
 * Handle inquiry submission.
 */
function nrx_handle_inquiry() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'nrx_inquiry' ) ) {
		wp_send_json_error( array( 'message' => __( 'Tu sesión expiró. Recarga la página.', 'nuestrarx-core' ) ), 403 );
	}
	if ( ! empty( $_POST['nrx_hp'] ) ) {
		wp_send_json_error( array( 'message' => __( 'No pudimos procesar el mensaje.', 'nuestrarx-core' ) ), 400 );
	}
	if ( ! nrx_rate_limit( 'inquiry_submit', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => __( 'Recibimos varios mensajes desde tu conexión. Espera un momento o escríbenos por WhatsApp.', 'nuestrarx-core' ) ), 429 );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? preg_replace( '/[^0-9+]/', '', sanitize_text_field( wp_unslash( $_POST['phone'] ) ) ) : '';
	$topic   = isset( $_POST['topic'] ) ? sanitize_key( wp_unslash( $_POST['topic'] ) ) : 'other';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$consent = ! empty( $_POST['consent'] );
	$variant = isset( $_POST['variant'] ) && 'provider' === $_POST['variant'] ? 'provider' : 'patient';

	if ( '' === $name || ! is_email( $email ) || '' === $message || ! $consent ) {
		wp_send_json_error( array( 'message' => __( 'Completa nombre, email, mensaje y acepta la política de privacidad.', 'nuestrarx-core' ) ), 422 );
	}

	$encrypted_message = nrx_encrypt( $message );
	if ( 0 !== strpos( $encrypted_message, 'nrxenc:' ) ) {
		wp_send_json_error( array( 'message' => __( 'We could not securely save your message. Please try again later.', 'nuestrarx-core' ) ), 500 );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'nrx_intake',
			'post_status' => 'private',
			'post_title'  => sprintf( 'Consulta %s · %s', current_time( 'mysql' ), 'provider' === $variant ? 'profesional' : 'paciente' ),
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		nrx_log( 'No se pudo guardar la consulta', array( 'error' => $post_id->get_error_message() ) );
		wp_send_json_error( array( 'message' => __( 'No pudimos guardar tu mensaje. Escríbenos por WhatsApp.', 'nuestrarx-core' ) ), 500 );
	}
	update_post_meta( $post_id, '_nrx_kind', 'inquiry' );
	update_post_meta( $post_id, '_nrx_variant', $variant );
	update_post_meta( $post_id, '_nrx_name', $name );
	update_post_meta( $post_id, '_nrx_email', $email );
	update_post_meta( $post_id, '_nrx_phone', $phone );
	update_post_meta( $post_id, '_nrx_topic', $topic );
	if ( ! update_post_meta( $post_id, '_nrx_message_enc', $encrypted_message ) ) {
		wp_delete_post( $post_id, true );
		wp_send_json_error( array( 'message' => __( 'We could not save your message. Please try again later.', 'nuestrarx-core' ) ), 500 );
	}
	update_post_meta( $post_id, '_nrx_consent_granted', 1 );
	update_post_meta( $post_id, '_nrx_consent_at', current_time( 'mysql' ) );
	update_post_meta( $post_id, '_nrx_retention_until', gmdate( 'Y-m-d H:i:s', time() + ( max( 1, (int) nrx_setting( 'intake_retention_days', 90 ) ) * DAY_IN_SECONDS ) ) );
	update_post_meta( $post_id, '_nrx_ip_hash', nrx_hashed_ip() );

	$to = sanitize_email( (string) nrx_setting( 'inquiry_notify_email', '' ) );
	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}
	wp_mail(
		$to,
		sprintf( '[NuestraRx] Consulta %s', 'NRX-' . $post_id ),
		sprintf(
			"Nueva consulta %s.\n\nNombre: %s\nEmail: %s\nTeléfono: %s\nMotivo: %s\n\nAbrir en el panel:\n%s\n\nEl mensaje está cifrado y no se incluye en este correo.",
			'NRX-' . $post_id,
			$name,
			$email,
			$phone,
			$topic,
			admin_url( 'post.php?post=' . $post_id . '&action=edit' )
		),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);

	$webhook = (string) nrx_setting( 'inquiry_webhook_url', '' );
	if ( $webhook ) {
		$result = nrx_send_intake_webhook(
			$webhook,
			(string) nrx_setting( 'quiz_webhook_secret', '' ),
			$post_id,
			array( 'settings' => array( 'consent_version' => 'inquiry' ) ),
			array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => $phone,
				'topic'   => $topic,
				'message' => $message,
				'variant' => $variant,
				'consent' => 1,
			),
			'inquiry',
			array()
		);
		update_post_meta( $post_id, '_nrx_webhook_status', is_wp_error( $result ) ? 'failed' : 'sent' );
	}

	nrx_rate_limit_clear( 'inquiry_submit' );
	wp_send_json_success(
		array(
			'message' => __( 'Gracias. Recibimos tu mensaje y te responderemos en español.', 'nuestrarx-core' ),
			'ref'     => 'NRX-' . $post_id,
		)
	);
}
add_action( 'wp_ajax_nrx_inquiry_submit', 'nrx_handle_inquiry' );
add_action( 'wp_ajax_nopriv_nrx_inquiry_submit', 'nrx_handle_inquiry' );

/**
 * Show the inquiry message in the intake detail screen.
 *
 * @param WP_Post $post Post.
 */
function nrx_inquiry_meta_box( $post ) {
	if ( 'inquiry' !== get_post_meta( $post->ID, '_nrx_kind', true ) ) {
		return;
	}
	if ( ! current_user_can( 'nrx_manage_intakes' ) && ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$message = nrx_decrypt( (string) get_post_meta( $post->ID, '_nrx_message_enc', true ) );
	echo '<div class="nrx-admin"><table class="widefat striped"><tbody>';
	printf( '<tr><th style="width:180px">%s</th><td>%s</td></tr>', esc_html__( 'Nombre', 'nuestrarx-core' ), esc_html( (string) get_post_meta( $post->ID, '_nrx_name', true ) ) );
	printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Email', 'nuestrarx-core' ), esc_html( (string) get_post_meta( $post->ID, '_nrx_email', true ) ) );
	printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Teléfono', 'nuestrarx-core' ), esc_html( (string) get_post_meta( $post->ID, '_nrx_phone', true ) ) );
	printf( '<tr><th>%s</th><td><pre style="white-space:pre-wrap">%s</pre></td></tr>', esc_html__( 'Mensaje', 'nuestrarx-core' ), esc_html( $message ) );
	echo '</tbody></table></div>';
}
add_action( 'edit_form_after_title', 'nrx_inquiry_meta_box' );
