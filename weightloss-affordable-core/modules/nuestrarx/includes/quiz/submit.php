<?php
/**
 * Quiz submission endpoint: validation, evaluation, privacy-safe storage,
 * notification and optional clinical webhook handoff.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize answers according to the published configuration.
 *
 * Only fields defined in the configuration are accepted; unknown keys are
 * discarded. Measurements and BMI are recomputed server-side.
 *
 * @param array $config Configuration.
 * @param array $raw    Raw answers.
 * @return array
 */
function nrx_quiz_sanitize_answers( $config, $raw ) {
	$raw   = is_array( $raw ) ? $raw : array();
	$clean = array();
	$state_codes = array_keys( nrx_states() );

	foreach ( $config['steps'] as $step ) {
		$type = $step['type'] ?? 'radio';
		switch ( $type ) {
			case 'radio':
			case 'select':
			case 'state':
				$field = sanitize_key( $step['field'] ?? '' );
				if ( ! $field ) {
					break;
				}
				$value  = isset( $raw[ $field ] ) && is_scalar( $raw[ $field ] ) ? sanitize_text_field( wp_unslash( $raw[ $field ] ) ) : '';
				$values = wp_list_pluck( (array) ( $step['options'] ?? array() ), 'value' );
				if ( 'state' === $type ) {
					$value = strtoupper( $value );
					$clean[ $field ] = in_array( $value, $state_codes, true ) ? $value : '';
				} elseif ( in_array( $value, $values, true ) ) {
					$clean[ $field ] = $value;
				}
				break;

			case 'checkboxes':
				$field = sanitize_key( $step['field'] ?? '' );
				if ( ! $field ) {
					break;
				}
				$submitted = isset( $raw[ $field ] ) ? (array) $raw[ $field ] : array();
				$submitted = array_filter( $submitted, 'is_scalar' );
				$allowed   = wp_list_pluck( (array) ( $step['options'] ?? array() ), 'value' );
				$clean[ $field ] = array_values( array_intersect( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $submitted ) ), $allowed ) );
				break;

			case 'measurements':
				$clean['height_ft'] = isset( $raw['height_ft'] ) && is_numeric( $raw['height_ft'] ) ? (float) $raw['height_ft'] : 0;
				$clean['height_in'] = isset( $raw['height_in'] ) && is_numeric( $raw['height_in'] ) ? (float) $raw['height_in'] : 0;
				$clean['weight_lb'] = isset( $raw['weight_lb'] ) && is_numeric( $raw['weight_lb'] ) ? (float) $raw['weight_lb'] : 0;
				$clean['bmi']       = nrx_bmi( $clean['height_ft'], $clean['height_in'], $clean['weight_lb'] );
				break;

			case 'fields':
				foreach ( (array) ( $step['fields'] ?? array() ) as $sub ) {
					$key = sanitize_key( $sub['key'] ?? '' );
					if ( ! $key ) {
						continue;
					}
					$sub_type = $sub['type'] ?? 'text';
					$value    = $raw[ $key ] ?? '';
					if ( is_array( $value ) ) {
						$value = '';
					}
					$value = wp_unslash( (string) $value );
					if ( 'state' === $sub_type ) {
						$value = strtoupper( sanitize_text_field( $value ) );
						$clean[ $key ] = in_array( $value, $state_codes, true ) ? $value : '';
					} elseif ( 'email' === $sub_type ) {
						$clean[ $key ] = sanitize_email( $value );
					} elseif ( 'textarea' === $sub_type ) {
						$clean[ $key ] = sanitize_textarea_field( mb_substr( $value, 0, 2000 ) );
					} elseif ( 'date' === $sub_type ) {
						$clean[ $key ] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
					} elseif ( 'tel' === $sub_type ) {
						$clean[ $key ] = sanitize_text_field( $value );
					} elseif ( 'number' === $sub_type ) {
						$clean[ $key ] = is_numeric( $value ) ? (float) $value : '';
					} else {
						$clean[ $key ] = sanitize_text_field( mb_substr( $value, 0, 200 ) );
					}
				}
				break;

			case 'consent':
				foreach ( (array) ( $step['checkboxes'] ?? array() ) as $box ) {
					$key = sanitize_key( $box['key'] ?? '' );
					if ( $key ) {
						$clean[ $key ] = ! empty( $raw[ $key ] ) ? 1 : 0;
					}
				}
				break;

			case 'text':
			case 'textarea':
			case 'number':
			case 'date':
				$field = sanitize_key( $step['field'] ?? '' );
				if ( ! $field ) {
					break;
				}
				$value = isset( $raw[ $field ] ) && is_scalar( $raw[ $field ] ) ? wp_unslash( (string) $raw[ $field ] ) : '';
				if ( 'textarea' === $type ) {
					$clean[ $field ] = sanitize_textarea_field( mb_substr( $value, 0, 2000 ) );
				} elseif ( 'number' === $type ) {
					$clean[ $field ] = is_numeric( $value ) ? (float) $value : '';
				} elseif ( 'date' === $type ) {
					$clean[ $field ] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
				} else {
					$clean[ $field ] = sanitize_text_field( mb_substr( $value, 0, 200 ) );
				}
				break;
		}
	}

	return $clean;
}

/**
 * Validate visible required fields and formats.
 *
 * @param array $config  Config.
 * @param array $answers Sanitized answers.
 * @return true|WP_Error Error data contains a field => message map.
 */
function nrx_quiz_validate_answers( $config, $answers ) {
	$errors = array();

	foreach ( $config['steps'] as $step ) {
		if ( ! nrx_quiz_step_visible( $step, $answers ) ) {
			continue;
		}
		$type  = $step['type'] ?? 'radio';
		$req   = ! empty( $step['required'] );
		$field = sanitize_key( $step['field'] ?? '' );

		switch ( $type ) {
			case 'radio':
			case 'select':
			case 'state':
				if ( $req && empty( $answers[ $field ] ) ) {
					$errors[ $field ] = __( 'Selecciona una opción para continuar.', 'nuestrarx-core' );
				}
				break;

			case 'checkboxes':
				if ( $req && empty( $answers[ $field ] ) ) {
					$errors[ $field ] = __( 'Selecciona al menos una opción para continuar.', 'nuestrarx-core' );
				}
				break;

			case 'measurements':
				$ft = (float) ( $answers['height_ft'] ?? 0 );
				$in = (float) ( $answers['height_in'] ?? 0 );
				$lb = (float) ( $answers['weight_lb'] ?? 0 );
				if ( $ft < 3 || $ft > 8 || $in < 0 || $in > 11 || $lb < 70 || $lb > 700 ) {
					$errors['height_ft'] = __( 'Revisa tu estatura y peso (adultos: 3–8 pies, 70–700 lb).', 'nuestrarx-core' );
				}
				break;

			case 'fields':
				foreach ( (array) ( $step['fields'] ?? array() ) as $sub ) {
					$key   = sanitize_key( $sub['key'] ?? '' );
					$value = $answers[ $key ] ?? '';
					$sub_req = ! empty( $sub['required'] );
					if ( $sub_req && ( '' === $value || null === $value ) ) {
						$errors[ $key ] = __( 'Complete this field to continue.', 'nuestrarx-core' );
						continue;
					}
					if ( '' === $value ) {
						continue;
					}
					if ( 'email' === ( $sub['type'] ?? '' ) && ! is_email( $value ) ) {
						$errors[ $key ] = __( 'Enter a valid email address.', 'nuestrarx-core' );
					}
					if ( 'tel' === ( $sub['type'] ?? '' ) ) {
						$digits = preg_replace( '/\D/', '', (string) $value );
						if ( strlen( $digits ) === 11 && '1' === $digits[0] ) {
							$digits = substr( $digits, 1 );
						}
						if ( strlen( $digits ) !== 10 ) {
							$errors[ $key ] = __( 'Enter a valid 10-digit phone number.', 'nuestrarx-core' );
						}
					}
					if ( 'date' === ( $sub['type'] ?? '' ) ) {
						$age = nrx_age_from_date( (string) $value );
						if ( null === $age ) {
							$errors[ $key ] = __( 'Enter a valid date.', 'nuestrarx-core' );
						}
					}
					if ( ! empty( $sub['pattern'] ) && 'text' === ( $sub['type'] ?? '' ) ) {
						if ( ! preg_match( '/' . str_replace( '/', '\/', (string) $sub['pattern'] ) . '/', (string) $value ) ) {
							$errors[ $key ] = __( 'That format is not valid.', 'nuestrarx-core' );
						}
					}
				}
				break;

			case 'consent':
				foreach ( (array) ( $step['checkboxes'] ?? array() ) as $box ) {
					$key = sanitize_key( $box['key'] ?? '' );
					if ( ! empty( $box['required'] ) && empty( $answers[ $key ] ) ) {
						$errors[ $key ] = __( 'Check this box to continue.', 'nuestrarx-core' );
					}
				}
				break;

			case 'text':
			case 'textarea':
			case 'date':
				if ( $req && empty( $answers[ $field ] ) ) {
					$errors[ $field ] = __( 'Complete this field to continue.', 'nuestrarx-core' );
				}
				break;
		}
	}

	if ( $errors ) {
		return new WP_Error( 'nrx_quiz_validation', __( 'Please review your enquiry.', 'nuestrarx-core' ), $errors );
	}
	return true;
}

/**
 * Result screen markup.
 *
 * @param array  $config  Config.
 * @param string $outcome Outcome key.
 * @param array  $flags   Clinical flags.
 * @param int    $intake_id Intake post ID (0 for declines).
 * @return string
 */
function nrx_quiz_result_html( $config, $outcome, $flags = array(), $intake_id = 0 ) {
	$outcomes = $config['settings']['outcomes'] ?? array();
	$screen   = $outcomes[ $outcome ] ?? $outcomes['review'];
	$min_bmi  = $config['settings']['min_bmi'] ?? 25;
	$tone     = $screen['tone'] ?? 'neutral';
	$body     = str_replace( '{min_bmi}', number_format_i18n( (float) $min_bmi, 1 ), (string) ( $screen['body'] ?? '' ) );
	$body_en  = str_replace( '{min_bmi}', number_format_i18n( (float) $min_bmi, 1 ), (string) ( $screen['body_en'] ?? '' ) );
	$cta_url  = $screen['cta_url'] ?? '';
	if ( empty( $cta_url ) ) {
		if ( 'qualified' === $outcome ) {
			$cta_url = nrx_checkout_url( array( 'nrx_outcome' => 'qualified' ) );
			if ( ! $cta_url ) {
				// The live site sends a qualified patient to plan selection.
				// That page doubles as the payment hand-off until a real
				// checkout URL is configured in Ajustes.
				$cta_url = nrx_page_url( (string) nrx_setting( 'payment_page_slug', 'pago-semanal' ) );
			}
			if ( ! $cta_url ) {
				$cta_url = '/#tratamientos';
			}
		} else {
			$cta_url = nrx_whatsapp_url( 'Hola, tengo una pregunta sobre mi evaluación' );
			if ( ! $cta_url ) {
				$cta_url = '/';
			}
		}
	}
	$is_qualified = 'qualified' === $outcome;
	$is_restart   = in_array( $outcome, array( 'ineligible_bmi', 'declined' ), true );

	ob_start();
	?>
	<div class="nrx-result nrx-result-<?php echo esc_attr( $tone ); ?>" data-outcome="<?php echo esc_attr( $outcome ); ?>">
		<div class="nrx-result-mark" aria-hidden="true"><?php echo $is_qualified ? nrx_icon( 'check', 34 ) : ( 'review' === $tone ? nrx_icon( 'stethoscope', 34 ) : nrx_icon( 'info', 34 ) ); ?></div>
		<p class="nrx-result-eyebrow"<?php echo nrx_bi_attr( $screen['eyebrow_en'] ?? '' ); ?>><?php echo esc_html( $screen['eyebrow'] ?? '' ); ?></p>
		<h2 class="nrx-result-title" tabindex="-1" data-nrx-result-title<?php echo nrx_bi_attr( $screen['title_en'] ?? '' ); ?>><?php echo esc_html( $screen['title'] ?? '' ); ?></h2>
		<p class="nrx-result-body"<?php echo nrx_bi_attr( $body_en ); ?>><?php echo esc_html( $body ); ?></p>

		<?php if ( $is_qualified ) : ?>
			<ul class="nrx-result-next">
				<li><?php echo nrx_icon( 'check', 16 ); ?><span><?php esc_html_e( 'Un médico con licencia revisa tu historial.', 'nuestrarx-core' ); ?></span></li>
				<li><?php echo nrx_icon( 'check', 16 ); ?><span><?php esc_html_e( 'Si el tratamiento es apropiado, recibes tu receta.', 'nuestrarx-core' ); ?></span></li>
				<li><?php echo nrx_icon( 'check', 16 ); ?><span><?php esc_html_e( 'Puedes cancelar cuando quieras.', 'nuestrarx-core' ); ?></span></li>
			</ul>
		<?php endif; ?>

		<div class="nrx-result-actions">
			<?php if ( $is_qualified ) : ?>
				<a class="button button-primary nrx-button" href="<?php echo esc_url( $cta_url ); ?>"<?php echo nrx_bi_attr( $screen['cta_label_en'] ?? '' ); ?>>
					<span><?php echo esc_html( $screen['cta_label'] ?? __( 'Ver mi plan y precio', 'nuestrarx-core' ) ); ?></span>
					<span class="button-arrow"><?php echo nrx_icon( 'arrow', 18 ); ?></span>
				</a>
			<?php else : ?>
				<a class="button button-primary nrx-button" href="<?php echo esc_url( $cta_url ); ?>"<?php echo nrx_bi_attr( $screen['cta_label_en'] ?? '' ); ?>>
					<span><?php echo esc_html( $screen['cta_label'] ?? __( 'Hablar con el equipo', 'nuestrarx-core' ) ); ?></span>
					<span class="button-arrow"><?php echo nrx_icon( 'arrow', 18 ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( $is_restart ) : ?>
				<button type="button" class="button button-quiet" data-nrx-restart><?php esc_html_e( 'Volver a empezar', 'nuestrarx-core' ); ?></button>
			<?php endif; ?>
		</div>

		<?php if ( $intake_id ) : ?>
			<p class="nrx-result-ref"><?php echo esc_html( sprintf( __( 'Referencia de tu evaluación: NRX-%s', 'nuestrarx-core' ), strtoupper( (string) $intake_id ) ) ); ?></p>
		<?php endif; ?>

		<p class="nrx-result-disclaimer"><?php echo wp_kses_post( nl2br( esc_html( nrx_setting( 'results_disclaimer', '' ) ) ) ); ?></p>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Handle quiz submission.
 */
function nrx_quiz_handle_submit() {
	if ( ! nrx_setting( 'quiz_submissions_enabled', 0 ) ) {
		wp_send_json_error( array( 'message' => __( 'The enquiry quiz is in demonstration mode. Answers are not accepted or stored.', 'nuestrarx-core' ) ), 423 );
	}
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'nrx_quiz_submit' ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Reload the page and try again.', 'nuestrarx-core' ) ), 403 );
	}

	// Honeypot and time trap.
	if ( ! empty( $_POST['nrx_hp'] ) ) {
		wp_send_json_error( array( 'message' => __( 'We could not process the submission.', 'nuestrarx-core' ) ), 400 );
	}
	$elapsed = isset( $_POST['elapsed'] ) ? absint( $_POST['elapsed'] ) : 0;
	if ( $elapsed > 0 && $elapsed < 8 ) {
		wp_send_json_error( array( 'message' => __( 'We could not process the submission.', 'nuestrarx-core' ) ), 400 );
	}

	if ( ! nrx_rate_limit( 'quiz_submit', 8, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => __( 'Too many attempts from your connection. Please wait a moment and try again.', 'nuestrarx-core' ) ), 429 );
	}

	$config = nrx_quiz_get( 'published' );
	if ( nrx_quiz_is_preview() ) {
		$config = nrx_quiz_get( 'draft' );
	}

	$raw     = isset( $_POST['answers'] ) ? json_decode( wp_unslash( $_POST['answers'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$answers = nrx_quiz_sanitize_answers( $config, is_array( $raw ) ? $raw : array() );

	// Explicit decline: do not store or transmit anything.
	$declined = ! empty( $_POST['declined'] );
	if ( $declined ) {
		wp_send_json_success(
			array(
				'outcome' => 'declined',
				'html'    => nrx_quiz_result_html( $config, 'declined' ),
			)
		);
	}

	$valid = nrx_quiz_validate_answers( $config, $answers );
	if ( is_wp_error( $valid ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please review your enquiry.', 'nuestrarx-core' ),
				'fields'  => $valid->get_error_data(),
			),
			422
		);
	}

	$evaluation = nrx_quiz_evaluate( $config, $answers );
	$outcome    = $evaluation['outcome'];
	$flags      = $evaluation['flags'];

	// Additional server-side safeguard: invalid BMI is never "qualified".
	if ( 'qualified' === $outcome && ( empty( $answers['bmi'] ) || (float) $answers['bmi'] < (float) ( $config['settings']['min_bmi'] ?? 25 ) ) ) {
		$outcome = 'ineligible_bmi';
	}

	$intake_id = nrx_store_intake( $config, $answers, $outcome, $flags );
	if ( ! $intake_id ) {
		wp_send_json_error( array( 'message' => __( 'We could not save your enquiry. Please try again later.', 'nuestrarx-core' ) ), 500 );
	}

	/**
	 * Fires after a completed evaluation is stored.
	 *
	 * @param int   $intake_id Intake post ID.
	 * @param array $answers   Sanitized answers.
	 * @param array $context   Outcome and config.
	 */
	do_action( 'nrx_intake_created', $intake_id, $answers, array( 'outcome' => $outcome, 'config' => $config ) );

	nrx_rate_limit_clear( 'quiz_submit' );

	wp_send_json_success(
		array(
			'outcome'  => $outcome,
			'intakeId' => $intake_id,
			'html'     => nrx_quiz_result_html( $config, $outcome, $flags, $intake_id ),
			'redirect' => apply_filters( 'nrx_quiz_success_redirect', '', $outcome, $intake_id ),
		)
	);
}
add_action( 'wp_ajax_nrx_quiz_submit', 'nrx_quiz_handle_submit' );
add_action( 'wp_ajax_nopriv_nrx_quiz_submit', 'nrx_quiz_handle_submit' );
