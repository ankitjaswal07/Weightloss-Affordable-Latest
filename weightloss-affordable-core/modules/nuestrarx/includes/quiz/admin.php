<?php
/**
 * Quiz builder.
 *
 * Draft/publish workflow: editors change a draft, validate it, preview it as
 * a logged-in reviewer, and only a user with `nrx_publish_quiz` can publish.
 * Every publish stores a revision snapshot that can be restored.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the builder screen.
 */
function nrx_quiz_admin_menu() {
	add_submenu_page(
		'nrx-dashboard',
		__( 'Evaluación', 'nuestrarx-core' ),
		__( 'Evaluación', 'nuestrarx-core' ),
		'nrx_manage_quiz',
		'nrx-quiz',
		'nrx_quiz_admin_page'
	);
}
add_action( 'admin_menu', 'nrx_quiz_admin_menu' );

/**
 * Parse a condition value (comma-separated for list operators).
 *
 * @param string $operator Operator.
 * @param string $value    Raw value.
 * @return string|array
 */
function nrx_quiz_condition_value( $operator, $value ) {
	$value = sanitize_text_field( is_array( $value ) ? implode( ', ', $value ) : (string) $value );
	if ( in_array( $operator, array( 'in', 'not_in' ), true ) ) {
		$list = array_values( array_filter( array_map( 'trim', explode( ',', $value ) ), 'strlen' ) );
		return implode( ',', $list );
	}
	return $value;
}

/**
 * Operators allowed in clinical safety rules (includes derived-field checks).
 *
 * @return array
 */
function nrx_quiz_check_operators() {
	return array_merge(
		array(
			'age_under'         => __( 'edad menor que', 'nuestrarx-core' ),
			'age_over'          => __( 'edad mayor que', 'nuestrarx-core' ),
			'state_unavailable' => __( 'estado no disponible', 'nuestrarx-core' ),
		),
		nrx_quiz_operators()
	);
}

/**
 * Sanitize an option value without destroying meaningful characters.
 *
 * sanitize_title() was stripping "+" (turning "60+" into "60") and lowercasing
 * state codes, which silently changed stored answers and broke rule matching.
 *
 * @param string $value Value.
 * @param string $label Fallback label.
 * @param string $type  Step type.
 * @return string
 */
function nrx_quiz_option_value( $value, $label = '', $type = 'radio' ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $value ) {
		$value = (string) $label;
	}
	$value = preg_replace( '/[^A-Za-z0-9_.+\-]+/', '-', $value );
	$value = trim( (string) $value, '-' );
	if ( 'state' === $type ) {
		$value = strtoupper( substr( $value, 0, 2 ) );
	}
	if ( '' === $value ) {
		$value = sanitize_title( (string) $label );
	}
	return $value;
}

/**
 * Build a configuration array from the submitted builder form.
 *
 * @param array $base Base config (current draft).
 * @param array $post Submitted data (unslashed).
 * @return array
 */
function nrx_quiz_config_from_post( $base, $post ) {
	$input = isset( $post['nrx_quiz'] ) && is_array( $post['nrx_quiz'] ) ? $post['nrx_quiz'] : array();

	$config           = $base;
	$config['meta']   = array();
	foreach ( array( 'title', 'intro_eyebrow', 'intro_title', 'intro_body' ) as $key ) {
		$config['meta'][ $key ]          = sanitize_text_field( $input['meta'][ $key ] ?? '' );
		$config['meta'][ $key . '_en' ]  = sanitize_text_field( $input['meta'][ $key . '_en' ] ?? '' );
	}
	$config['meta']['estimated_minutes'] = max( 1, absint( $input['meta']['estimated_minutes'] ?? 5 ) );
	$trust = array();
	$trust_en = array();
	foreach ( (array) ( $input['meta']['trust'] ?? array() ) as $index => $value ) {
		$value = sanitize_text_field( $value );
		if ( '' !== $value ) {
			$trust[]    = $value;
			$trust_en[] = sanitize_text_field( $input['meta']['trust_en'][ $index ] ?? $value );
		}
	}
	$config['meta']['trust']    = $trust;
	$config['meta']['trust_en'] = $trust_en;

	$settings = $config['settings'];
	foreach ( array( 'notify_email', 'privacy_url', 'hipaa_url', 'safety_url', 'consent_version' ) as $key ) {
		$value = isset( $input['settings'][ $key ] ) ? wp_unslash( $input['settings'][ $key ] ) : '';
		$settings[ $key ] = 'notify_email' === $key ? sanitize_email( $value ) : sanitize_text_field( $value );
	}
	$settings['webhook_url'] = isset( $input['settings']['webhook_url'] ) ? esc_url_raw( wp_unslash( $input['settings']['webhook_url'] ) ) : '';
	$settings['min_bmi']     = isset( $input['settings']['min_bmi'] ) && is_numeric( $input['settings']['min_bmi'] ) ? (float) $input['settings']['min_bmi'] : 25;
	$settings['require_state'] = ! empty( $input['settings']['require_state'] ) ? 1 : 0;
	$settings['collect_phone'] = ! empty( $input['settings']['collect_phone'] ) ? 1 : 0;
	$settings['collect_email'] = ! empty( $input['settings']['collect_email'] ) ? 1 : 0;
	$settings['store_clinical_answers'] = ! empty( $input['settings']['store_clinical_answers'] ) ? 1 : 0;

	$outcomes = array();
	foreach ( array_keys( nrx_quiz_outcome_labels() ) as $key ) {
		$row = isset( $input['outcomes'][ $key ] ) && is_array( $input['outcomes'][ $key ] ) ? $input['outcomes'][ $key ] : array();
		$default = $config['settings']['outcomes'][ $key ] ?? array();
		$outcomes[ $key ] = array(
			'eyebrow'      => sanitize_text_field( $row['eyebrow'] ?? ( $default['eyebrow'] ?? '' ) ),
			'eyebrow_en'   => sanitize_text_field( $row['eyebrow_en'] ?? ( $default['eyebrow_en'] ?? '' ) ),
			'title'        => sanitize_text_field( $row['title'] ?? ( $default['title'] ?? '' ) ),
			'title_en'     => sanitize_text_field( $row['title_en'] ?? ( $default['title_en'] ?? '' ) ),
			'body'         => sanitize_textarea_field( $row['body'] ?? ( $default['body'] ?? '' ) ),
			'body_en'      => sanitize_textarea_field( $row['body_en'] ?? ( $default['body_en'] ?? '' ) ),
			'cta_label'    => sanitize_text_field( $row['cta_label'] ?? ( $default['cta_label'] ?? '' ) ),
			'cta_label_en' => sanitize_text_field( $row['cta_label_en'] ?? ( $default['cta_label_en'] ?? '' ) ),
			'cta_url'      => esc_url_raw( $row['cta_url'] ?? ( $default['cta_url'] ?? '' ) ),
			'tone'         => in_array( $row['tone'] ?? '', array( 'success', 'review', 'neutral' ), true ) ? $row['tone'] : ( $default['tone'] ?? 'neutral' ),
		);
	}
	$settings['outcomes'] = $outcomes;

	$config['settings'] = $settings;

	// Evaluation rules.
	$checks = array();
	foreach ( (array) ( $input['checks'] ?? array() ) as $index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$operator = sanitize_key( $row['operator'] ?? 'equals' );
		if ( ! array_key_exists( $operator, nrx_quiz_check_operators() ) ) {
			$operator = 'equals';
		}
		$checks[] = array(
			'id'       => sanitize_key( $row['id'] ?? 'rule_' . $index ),
			'enabled'  => ! empty( $row['enabled'] ) ? 1 : 0,
			'field'    => sanitize_key( $row['field'] ?? '' ),
			'operator' => $operator,
			'value'    => nrx_quiz_condition_value( $operator, $row['value'] ?? '' ),
			'outcome'  => sanitize_key( $row['outcome'] ?? '' ),
			'flag'     => sanitize_key( $row['flag'] ?? '' ),
			'label'    => sanitize_text_field( $row['label'] ?? '' ),
		);
	}
	$config['evaluation']['checks'] = $checks;
	$default_outcome = sanitize_key( $input['default_outcome'] ?? 'qualified' );
	$config['evaluation']['default_outcome'] = array_key_exists( $default_outcome, nrx_quiz_outcome_labels() ) ? $default_outcome : 'qualified';

	// Steps.
	$steps = array();
	foreach ( (array) ( $input['steps'] ?? array() ) as $index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$type = sanitize_key( $row['type'] ?? 'radio' );
		if ( ! array_key_exists( $type, nrx_quiz_step_types() ) ) {
			$type = 'radio';
		}
		$step = array(
			'id'              => sanitize_key( $row['id'] ?? '' ),
			'type'            => $type,
			'phase'           => sanitize_text_field( $row['phase'] ?? '' ),
			'phase_en'        => sanitize_text_field( $row['phase_en'] ?? '' ),
			'question_number' => isset( $row['question_number'] ) ? absint( $row['question_number'] ) : 0,
			'title'           => sanitize_text_field( $row['title'] ?? '' ),
			'title_en'        => sanitize_text_field( $row['title_en'] ?? '' ),
			'help'            => sanitize_textarea_field( $row['help'] ?? '' ),
			'help_en'         => sanitize_textarea_field( $row['help_en'] ?? '' ),
			'field'           => sanitize_key( $row['field'] ?? '' ),
			'required'        => ! empty( $row['required'] ) ? 1 : 0,
			'validation'      => array(),
			'conditions'      => array(),
		);

		if ( in_array( $type, array( 'number', 'measurements' ), true ) ) {
			foreach ( array( 'min', 'max', 'min_bmi', 'max_weight', 'min_weight' ) as $key ) {
				if ( isset( $row['validation'][ $key ] ) && is_numeric( $row['validation'][ $key ] ) ) {
					$step['validation'][ $key ] = (float) $row['validation'][ $key ];
				}
			}
		}
		foreach ( (array) ( $row['options'] ?? array() ) as $option ) {
			if ( ! is_array( $option ) || ( '' === ( $option['value'] ?? '' ) && '' === ( $option['label'] ?? '' ) ) ) {
				continue;
			}
			$clean = array(
				'value'    => nrx_quiz_option_value( $option['value'] ?? $option['label'], $option['label'] ?? '', $type ),
				'label'    => sanitize_text_field( $option['label'] ?? '' ),
				'label_en' => sanitize_text_field( $option['label_en'] ?? '' ),
				'emoji'    => sanitize_text_field( $option['emoji'] ?? '' ),
				'note'     => sanitize_text_field( $option['note'] ?? '' ),
				'note_en'  => sanitize_text_field( $option['note_en'] ?? '' ),
				'flag'     => sanitize_key( $option['flag'] ?? '' ),
				'exclusive' => ! empty( $option['exclusive'] ) ? 1 : 0,
			);
			$step['options'][] = $clean;
		}

		foreach ( (array) ( $row['fields'] ?? array() ) as $sub ) {
			if ( ! is_array( $sub ) || empty( $sub['key'] ) ) {
				continue;
			}
			$step['fields'][] = array(
				'key'          => sanitize_key( $sub['key'] ),
				'type'         => sanitize_key( $sub['type'] ?? 'text' ),
				'label'        => sanitize_text_field( $sub['label'] ?? '' ),
				'label_en'     => sanitize_text_field( $sub['label_en'] ?? '' ),
				'placeholder'  => sanitize_text_field( $sub['placeholder'] ?? '' ),
				'required'     => ! empty( $sub['required'] ) ? 1 : 0,
				'autocomplete' => sanitize_text_field( $sub['autocomplete'] ?? '' ),
				'pattern'      => sanitize_text_field( $sub['pattern'] ?? '' ),
				'maxlength'    => isset( $sub['maxlength'] ) ? absint( $sub['maxlength'] ) : 0,
				'sensitive'    => ! empty( $sub['sensitive'] ) ? 1 : 0,
			);
		}

		$items = array();
		foreach ( (array) ( $row['items'] ?? array() ) as $item ) {
			$item = sanitize_textarea_field( is_array( $item ) ? ( $item['text'] ?? '' ) : $item );
			if ( '' !== $item ) {
				$items[] = $item;
			}
		}
		$items_en = array();
		foreach ( (array) ( $row['items_en'] ?? array() ) as $item ) {
			$items_en[] = sanitize_textarea_field( is_array( $item ) ? ( $item['text'] ?? '' ) : $item );
		}
		if ( 'consent' === $type ) {
			$step['items']    = $items;
			$step['items_en'] = $items_en;
		}

		$boxes = array();
		foreach ( (array) ( $row['checkboxes'] ?? array() ) as $box ) {
			if ( ! is_array( $box ) || empty( $box['key'] ) ) {
				continue;
			}
			$boxes[] = array(
				'key'      => sanitize_key( $box['key'] ),
				'label'    => sanitize_text_field( $box['label'] ?? '' ),
				'label_en' => sanitize_text_field( $box['label_en'] ?? '' ),
				'required' => ! empty( $box['required'] ) ? 1 : 0,
			);
		}
		if ( $boxes ) {
			$step['checkboxes'] = $boxes;
		}
		if ( 'consent' === $type ) {
			$step['decline_label']    = sanitize_text_field( $row['decline_label'] ?? '' );
			$step['decline_label_en'] = sanitize_text_field( $row['decline_label_en'] ?? '' );
			$step['secondary_label']  = sanitize_text_field( $row['secondary_label'] ?? '' );
			$step['secondary_label_en'] = sanitize_text_field( $row['secondary_label_en'] ?? '' );
			$step['secondary_url']    = esc_url_raw( $row['secondary_url'] ?? '' );
		}
		$step['validation_message'] = sanitize_text_field( $row['validation_message'] ?? '' );
		$step['validation_message_en'] = sanitize_text_field( $row['validation_message_en'] ?? '' );
		$step['info_body']          = sanitize_textarea_field( $row['info_body'] ?? '' );
		$step['info_body_en']       = sanitize_textarea_field( $row['info_body_en'] ?? '' );

		// Visibility conditions.
		foreach ( (array) ( $row['conditions'] ?? array() ) as $condition ) {
			if ( ! is_array( $condition ) || empty( $condition['field'] ) ) {
				continue;
			}
			$operator = sanitize_key( $condition['operator'] ?? 'equals' );
			if ( ! array_key_exists( $operator, nrx_quiz_operators() ) ) {
				$operator = 'equals';
			}
			$step['conditions'][] = array(
				'field'    => sanitize_key( $condition['field'] ),
				'operator' => $operator,
				'value'    => nrx_quiz_condition_value( $operator, $condition['value'] ?? '' ),
			);
		}

		// Remove keys the type does not use.
		if ( 'consent' !== $type ) {
			unset( $step['items'], $step['items_en'], $step['checkboxes'], $step['decline_label'], $step['decline_label_en'], $step['secondary_label'], $step['secondary_label_en'], $step['secondary_url'] );
		}
		if ( 'fields' !== $type && 'consent' !== $type ) {
			unset( $step['fields'] );
		}
		if ( ! in_array( $type, array( 'radio', 'checkboxes', 'select', 'state' ), true ) ) {
			unset( $step['options'] );
		}

		$steps[] = $step;
	}
	$config['steps'] = $steps;

	return nrx_quiz_normalize( $config );
}

/**
 * Handle builder actions.
 */
function nrx_quiz_handle_admin() {
	if ( ! nrx_can_edit_quiz() ) {
		wp_die( esc_html__( 'No tienes permiso para editar la evaluación.', 'nuestrarx-core' ) );
	}
	check_admin_referer( 'nrx_quiz_admin', '_nrx_nonce' );

	$action = isset( $_POST['nrx_quiz_action'] ) ? sanitize_text_field( wp_unslash( $_POST['nrx_quiz_action'] ) ) : 'save';
	$parts  = explode( ':', $action );
	$verb   = $parts[0];
	$index  = isset( $parts[1] ) ? (int) $parts[1] : -1;

	$base = nrx_quiz_get( 'draft' );
	$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized field by field.

	// The builder posts a single JSON field so long quizzes never hit
	// max_input_vars (default 1000), which would silently drop the last steps.
	if ( isset( $post['nrx_quiz_payload'] ) && is_string( $post['nrx_quiz_payload'] ) && '' !== $post['nrx_quiz_payload'] ) {
		$decoded = json_decode( (string) $post['nrx_quiz_payload'], true );
		if ( is_array( $decoded ) ) {
			$post['nrx_quiz'] = $decoded;
		} else {
			set_transient(
				'nrx_quiz_notice_' . get_current_user_id(),
				array(
					'message' => __( 'El formulario llegó incompleto y no se guardó nada. Recarga la página e inténtalo de nuevo.', 'nuestrarx-core' ),
					'type'    => 'error',
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=nrx-quiz' ) );
			exit;
		}
	}

	$config = nrx_quiz_config_from_post( $base, $post );

	$message = __( 'Borrador guardado.', 'nuestrarx-core' );
	$type    = 'success';

	switch ( $verb ) {
		case 'add_step':
			$new_type = isset( $_POST['nrx_new_step_type'] ) ? sanitize_key( wp_unslash( $_POST['nrx_new_step_type'] ) ) : 'radio';
			$new_type = array_key_exists( $new_type, nrx_quiz_step_types() ) ? $new_type : 'radio';
			$config['steps'][] = array(
				'id'       => 'paso_' . wp_rand( 1000, 9999 ),
				'type'     => $new_type,
				'phase'    => 'Nueva sección',
				'title'    => __( 'Nueva pregunta', 'nuestrarx-core' ),
				'field'    => 'campo_' . wp_rand( 1000, 9999 ),
				'required' => 1,
				'options'  => in_array( $new_type, array( 'radio', 'checkboxes', 'select', 'state' ), true )
					? array(
						array( 'value' => 'opcion_1', 'label' => 'Opción 1' ),
						array( 'value' => 'opcion_2', 'label' => 'Opción 2' ),
					)
					: array(),
				'checkboxes' => 'consent' === $new_type ? array( array( 'key' => 'consent', 'label' => 'Acepto continuar.', 'required' => 1 ) ) : array(),
			);
			$message = __( 'Paso añadido al final.', 'nuestrarx-core' );
			break;

		case 'duplicate_step':
			if ( isset( $config['steps'][ $index ] ) ) {
				$copy = $config['steps'][ $index ];
				$copy['id']    = $copy['id'] . '_copia_' . wp_rand( 100, 999 );
				$copy['title'] = $copy['title'] . ' (copia)';
				if ( ! empty( $copy['field'] ) ) {
					$copy['field'] = $copy['field'] . '_copia_' . wp_rand( 100, 999 );
				}
				array_splice( $config['steps'], $index + 1, 0, array( $copy ) );
				$message = __( 'Paso duplicado. Revisa los identificadores antes de publicar.', 'nuestrarx-core' );
				$type    = 'warning';
			}
			break;

		case 'delete_step':
			if ( isset( $config['steps'][ $index ] ) ) {
				array_splice( $config['steps'], $index, 1 );
				$message = __( 'Paso eliminado del borrador.', 'nuestrarx-core' );
				$type    = 'warning';
			}
			break;

		case 'move_up':
			if ( $index > 0 && isset( $config['steps'][ $index ] ) ) {
				$swap = $config['steps'][ $index - 1 ];
				$config['steps'][ $index - 1 ] = $config['steps'][ $index ];
				$config['steps'][ $index ]     = $swap;
				$message = __( 'Paso movido hacia arriba.', 'nuestrarx-core' );
			}
			break;

		case 'move_down':
			if ( $index >= 0 && isset( $config['steps'][ $index + 1 ] ) ) {
				$swap = $config['steps'][ $index + 1 ];
				$config['steps'][ $index + 1 ] = $config['steps'][ $index ];
				$config['steps'][ $index ]     = $swap;
				$message = __( 'Paso movido hacia abajo.', 'nuestrarx-core' );
			}
			break;

		case 'publish':
			if ( ! nrx_can_publish_quiz() ) {
				$message = __( 'No tienes permiso para publicar cambios de la evaluación.', 'nuestrarx-core' );
				$type    = 'error';
				break;
			}
			nrx_quiz_save_draft( $config );
			$note   = isset( $_POST['nrx_publish_note'] ) ? sanitize_text_field( wp_unslash( $_POST['nrx_publish_note'] ) ) : '';
			$result = nrx_quiz_publish( $config, $note );
			if ( is_wp_error( $result ) ) {
				$message = $result->get_error_message();
				$type    = 'error';
			} else {
				$message = __( 'Evaluación publicada. Se guardó una revisión recuperable.', 'nuestrarx-core' );
			}
			break;

		case 'restore':
			$revisions = get_option( 'nrx_quiz_revisions', array() );
			$revision  = is_array( $revisions ) ? ( $revisions[ $index ] ?? null ) : null;
			if ( $revision && isset( $revision['config'] ) ) {
				nrx_quiz_save_draft( $revision['config'] );
				$message = sprintf( __( 'Revisión v%s cargada en el borrador. Publícala para aplicarla.', 'nuestrarx-core' ), $revision['version'] );
				$type    = 'warning';
			}
			break;

		case 'validate':
			nrx_quiz_save_draft( $config );
			$result = nrx_quiz_validate( $config );
			$message = is_wp_error( $result )
				? $result->get_error_message()
				: __( 'Validación correcta: puedes publicar y previsualizar.', 'nuestrarx-core' );
			$type = is_wp_error( $result ) ? 'error' : 'success';
			break;

		case 'save':
		default:
			nrx_quiz_save_draft( $config );
			break;
	}

	// Structural actions (add, duplicate, delete, move) edit the in-memory
	// config only; without this they would report success and change nothing.
	if ( in_array( $verb, array( 'add_step', 'duplicate_step', 'delete_step', 'move_up', 'move_down' ), true ) ) {
		nrx_quiz_save_draft( $config );
	}

	set_transient(
		'nrx_quiz_notice_' . get_current_user_id(),
		array(
			'message' => $message,
			'type'    => $type,
		),
		60
	);
	wp_safe_redirect( admin_url( 'admin.php?page=nrx-quiz' ) );
	exit;
}
add_action( 'admin_post_nrx_quiz_admin', 'nrx_quiz_handle_admin' );

/**
 * Repeater row renderer for options / fields / checkboxes.
 *
 * @param string $name  Input name prefix.
 * @param array  $row   Row values.
 * @param array  $cols  Column definitions: key => [label, type].
 */
function nrx_quiz_admin_repeater_row( $name, $index, $row, $cols ) {
	echo '<tr class="nrx-repeater-row">';
	foreach ( $cols as $key => $col ) {
		$value  = $row[ $key ] ?? '';
		$field  = $name . '[' . $index . '][' . $key . ']';
		echo '<td>';
		if ( 'checkbox' === ( $col['type'] ?? 'text' ) ) {
			echo '<input type="checkbox" name="' . esc_attr( $field ) . '" value="1" ' . checked( ! empty( $value ), true, false ) . '>';
		} elseif ( 'textarea' === ( $col['type'] ?? 'text' ) ) {
			echo '<textarea name="' . esc_attr( $field ) . '" rows="2">' . esc_textarea( (string) $value ) . '</textarea>';
		} else {
			$input_type = in_array( $col['type'] ?? '', array( 'number', 'url', 'email' ), true ) ? $col['type'] : 'text';
			echo '<input type="' . esc_attr( $input_type ) . '" name="' . esc_attr( $field ) . '" value="' . esc_attr( (string) $value ) . '"' . ( ! empty( $col['placeholder'] ) ? ' placeholder="' . esc_attr( $col['placeholder'] ) . '"' : '' ) . '>';
		}
		echo '</td>';
	}
	echo '<td><button type="button" class="button-link nrx-remove-row" aria-label="' . esc_attr__( 'Eliminar fila', 'nuestrarx-core' ) . '">×</button></td></tr>';
}

/**
 * Render one step editor.
 *
 * @param int   $index Step index.
 * @param array $step  Step.
 */
function nrx_quiz_admin_step( $index, $step ) {
	$types    = nrx_quiz_step_types();
	$base     = 'nrx_quiz[steps][' . $index . ']';
	$type     = $step['type'] ?? 'radio';
	$phase    = $step['phase'] ?? '';
	$title    = $step['title'] ?? '';
	$fields_exist = in_array( $type, array( 'radio', 'checkboxes', 'select', 'state' ), true );
	?>
	<details class="nrx-step" <?php echo 0 === $index ? 'open' : ''; ?>>
		<summary>
			<span class="nrx-step-index"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
			<span class="nrx-step-title"><?php echo esc_html( $title ?: __( '(sin título)', 'nuestrarx-core' ) ); ?></span>
			<span class="nrx-pill"><?php echo esc_html( $types[ $type ] ?? $type ); ?></span>
			<span class="nrx-step-id"><code><?php echo esc_html( $step['id'] ?? '' ); ?></code></span>
		</summary>
		<div class="nrx-step-body">
			<div class="nrx-field-grid">
				<p><label><?php esc_html_e( 'ID (interno, sin espacios)', 'nuestrarx-core' ); ?><br>
					<input type="text" name="<?php echo esc_attr( $base ); ?>[id]" value="<?php echo esc_attr( $step['id'] ?? '' ); ?>" required></label></p>
				<p><label><?php esc_html_e( 'Tipo', 'nuestrarx-core' ); ?><br>
					<select name="<?php echo esc_attr( $base ); ?>[type]" class="nrx-step-type">
						<?php foreach ( $types as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></label></p>
				<p><label><?php esc_html_e( 'Sección / fase', 'nuestrarx-core' ); ?><br>
					<input type="text" name="<?php echo esc_attr( $base ); ?>[phase]" value="<?php echo esc_attr( $phase ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Sección / fase (EN)', 'nuestrarx-core' ); ?><br>
					<input type="text" name="<?php echo esc_attr( $base ); ?>[phase_en]" value="<?php echo esc_attr( $step['phase_en'] ?? '' ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Número visible', 'nuestrarx-core' ); ?><br>
					<input type="number" min="0" name="<?php echo esc_attr( $base ); ?>[question_number]" value="<?php echo esc_attr( (string) ( $step['question_number'] ?? 0 ) ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Campo que guarda la respuesta', 'nuestrarx-core' ); ?><br>
					<input type="text" name="<?php echo esc_attr( $base ); ?>[field]" value="<?php echo esc_attr( $step['field'] ?? '' ); ?>" <?php echo in_array( $type, array( 'fields', 'measurements', 'consent', 'info' ), true ) ? 'readonly' : ''; ?>></label></p>
			</div>

			<div class="nrx-field-grid nrx-field-grid-2">
				<p><label><?php esc_html_e( 'Pregunta (ES)', 'nuestrarx-core' ); ?><br>
					<input type="text" class="large-text" name="<?php echo esc_attr( $base ); ?>[title]" value="<?php echo esc_attr( $title ); ?>" required></label></p>
				<p><label><?php esc_html_e( 'Pregunta (EN)', 'nuestrarx-core' ); ?><br>
					<input type="text" class="large-text" name="<?php echo esc_attr( $base ); ?>[title_en]" value="<?php echo esc_attr( $step['title_en'] ?? '' ); ?>"></label></p>
			</div>
			<p><label><?php esc_html_e( 'Texto de ayuda (ES)', 'nuestrarx-core' ); ?><br>
				<textarea name="<?php echo esc_attr( $base ); ?>[help]" rows="2" class="large-text"><?php echo esc_textarea( $step['help'] ?? '' ); ?></textarea></label></p>
			<p><label><?php esc_html_e( 'Texto de ayuda (EN)', 'nuestrarx-core' ); ?><br>
				<textarea name="<?php echo esc_attr( $base ); ?>[help_en]" rows="2" class="large-text"><?php echo esc_textarea( $step['help_en'] ?? '' ); ?></textarea></label></p>

			<div class="nrx-field-grid">
				<p><label><input type="checkbox" name="<?php echo esc_attr( $base ); ?>[required]" value="1" <?php checked( ! empty( $step['required'] ) ); ?>> <?php esc_html_e( 'Respuesta obligatoria', 'nuestrarx-core' ); ?></label></p>
				<p><label><?php esc_html_e( 'Mensaje de validación (ES)', 'nuestrarx-core' ); ?><br>
					<input type="text" class="large-text" name="<?php echo esc_attr( $base ); ?>[validation_message]" value="<?php echo esc_attr( $step['validation_message'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Ej. Selecciona una opción para continuar.', 'nuestrarx-core' ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Mensaje de validación (EN)', 'nuestrarx-core' ); ?><br>
					<input type="text" class="large-text" name="<?php echo esc_attr( $base ); ?>[validation_message_en]" value="<?php echo esc_attr( $step['validation_message_en'] ?? '' ); ?>"></label></p>
			</div>

			<?php if ( 'measurements' === $type ) : ?>
				<div class="nrx-field-grid">
					<p><label><?php esc_html_e( 'IMC mínimo', 'nuestrarx-core' ); ?><br>
						<input type="number" step="0.1" min="0" name="<?php echo esc_attr( $base ); ?>[validation][min_bmi]" value="<?php echo esc_attr( (string) ( $step['validation']['min_bmi'] ?? '' ) ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Peso mínimo (lb)', 'nuestrarx-core' ); ?><br>
						<input type="number" min="0" name="<?php echo esc_attr( $base ); ?>[validation][min_weight]" value="<?php echo esc_attr( (string) ( $step['validation']['min_weight'] ?? '' ) ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Peso máximo (lb)', 'nuestrarx-core' ); ?><br>
						<input type="number" min="0" name="<?php echo esc_attr( $base ); ?>[validation][max_weight]" value="<?php echo esc_attr( (string) ( $step['validation']['max_weight'] ?? '' ) ); ?>"></label></p>
				</div>
			<?php endif; ?>

			<?php if ( 'info' === $type ) : ?>
				<p><label><?php esc_html_e( 'Contenido informativo (ES)', 'nuestrarx-core' ); ?><br>
					<textarea name="<?php echo esc_attr( $base ); ?>[info_body]" rows="3" class="large-text"><?php echo esc_textarea( $step['info_body'] ?? '' ); ?></textarea></label></p>
				<p><label><?php esc_html_e( 'Contenido informativo (EN)', 'nuestrarx-core' ); ?><br>
					<textarea name="<?php echo esc_attr( $base ); ?>[info_body_en]" rows="3" class="large-text"><?php echo esc_textarea( $step['info_body_en'] ?? '' ); ?></textarea></label></p>
			<?php endif; ?>

			<?php if ( $fields_exist ) : ?>
				<h4><?php esc_html_e( 'Opciones de respuesta', 'nuestrarx-core' ); ?></h4>
				<table class="widefat nrx-repeater">
					<thead><tr>
						<th><?php esc_html_e( 'Valor', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Etiqueta ES', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Etiqueta EN', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Emoji', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Nota ES', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Nota EN', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Bandera clínica', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Exclusiva', 'nuestrarx-core' ); ?></th>
						<th></th>
					</tr></thead>
					<tbody data-nrx-repeater="options" data-name="<?php echo esc_attr( $base . '[options]' ); ?>">
					<?php foreach ( array_values( (array) ( $step['options'] ?? array() ) ) as $ri => $row ) : ?>
						<?php
						nrx_quiz_admin_repeater_row(
							$base . '[options]',
							$ri,
							$row,
							array(
								'value'    => array( 'label' => 'Valor' ),
								'label'    => array( 'label' => 'ES' ),
								'label_en' => array( 'label' => 'EN' ),
								'emoji'    => array( 'label' => 'Emoji' ),
								'note'     => array( 'label' => 'Nota' ),
								'note_en'  => array( 'label' => 'Nota EN' ),
								'flag'     => array( 'label' => 'Flag' ),
								'exclusive' => array( 'label' => 'Exclusiva', 'type' => 'checkbox' ),
							)
						);
						?>
					<?php endforeach; ?>
					</tbody>
				</table>
				<template data-nrx-template="options">
					<?php
					nrx_quiz_admin_repeater_row(
						$base . '[options]',
						'__i__',
						array(),
						array(
							'value'    => array( 'label' => 'Valor' ),
							'label'    => array( 'label' => 'ES' ),
							'label_en' => array( 'label' => 'EN' ),
							'emoji'    => array( 'label' => 'Emoji' ),
							'note'     => array( 'label' => 'Nota' ),
							'note_en'  => array( 'label' => 'Nota EN' ),
							'flag'     => array( 'label' => 'Flag' ),
							'exclusive' => array( 'label' => 'Exclusiva', 'type' => 'checkbox' ),
						)
					);
					?>
				</template>
				<p><button type="button" class="button nrx-add-row" data-target="options"><?php esc_html_e( 'Añadir opción', 'nuestrarx-core' ); ?></button></p>
				<p class="nrx-field-help"><?php esc_html_e( 'Las opciones con "exclusiva" desmarcan las demás (por ejemplo "Ninguna de las anteriores").', 'nuestrarx-core' ); ?></p>
			<?php endif; ?>

			<?php if ( 'fields' === $type ) : ?>
				<h4><?php esc_html_e( 'Campos del formulario', 'nuestrarx-core' ); ?></h4>
				<table class="widefat nrx-repeater">
					<thead><tr>
						<th><?php esc_html_e( 'Clave', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Tipo', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Etiqueta ES', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Etiqueta EN', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Placeholder', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Obligatorio', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Autocomplete', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Patrón', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Máx.', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Sensible', 'nuestrarx-core' ); ?></th>
						<th></th>
					</tr></thead>
					<tbody data-nrx-repeater="fields" data-name="<?php echo esc_attr( $base . '[fields]' ); ?>">
					<?php foreach ( array_values( (array) ( $step['fields'] ?? array() ) ) as $ri => $row ) : ?>
						<?php nrx_quiz_admin_repeater_row( $base . '[fields]', $ri, $row, array( 'key' => array( 'label' => 'key' ), 'type' => array( 'label' => 'type' ), 'label' => array( 'label' => 'ES' ), 'label_en' => array( 'label' => 'EN' ), 'placeholder' => array( 'label' => 'ph' ), 'required' => array( 'type' => 'checkbox' ), 'autocomplete' => array( 'label' => 'ac' ), 'pattern' => array( 'label' => 'pattern' ), 'maxlength' => array( 'type' => 'number' ), 'sensitive' => array( 'type' => 'checkbox' ) ) ); ?>
					<?php endforeach; ?>
					</tbody>
				</table>
				<template data-nrx-template="fields">
					<?php
					nrx_quiz_admin_repeater_row(
						$base . '[fields]',
						'__i__',
						array(),
						array(
							'key' => array( 'label' => 'key' ),
							'type' => array( 'label' => 'type' ),
							'label' => array( 'label' => 'ES' ),
							'label_en' => array( 'label' => 'EN' ),
							'placeholder' => array( 'label' => 'ph' ),
							'required' => array( 'type' => 'checkbox' ),
							'autocomplete' => array( 'label' => 'ac' ),
							'pattern' => array( 'label' => 'pattern' ),
							'maxlength' => array( 'type' => 'number' ),
							'sensitive' => array( 'type' => 'checkbox' ),
						)
					);
					?>
				</template>
				<p><button type="button" class="button nrx-add-row" data-target="fields"><?php esc_html_e( 'Añadir campo', 'nuestrarx-core' ); ?></button></p>
			<?php endif; ?>

			<?php if ( 'consent' === $type ) : ?>
				<h4><?php esc_html_e( 'Puntos del consentimiento', 'nuestrarx-core' ); ?></h4>
				<table class="widefat nrx-repeater">
					<thead><tr><th><?php esc_html_e( 'Texto ES', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Texto EN', 'nuestrarx-core' ); ?></th><th></th></tr></thead>
					<tbody data-nrx-repeater="items">
					<?php
					$items    = array_values( (array) ( $step['items'] ?? array() ) );
					$items_en = array_values( (array) ( $step['items_en'] ?? array() ) );
					foreach ( $items as $i => $item ) :
						?>
						<tr>
							<td><textarea name="<?php echo esc_attr( $base ); ?>[items][<?php echo esc_attr( (string) $i ); ?>]" rows="2"><?php echo esc_textarea( $item ); ?></textarea></td>
							<td><textarea name="<?php echo esc_attr( $base ); ?>[items_en][<?php echo esc_attr( (string) $i ); ?>]" rows="2"><?php echo esc_textarea( $items_en[ $i ] ?? '' ); ?></textarea></td>
							<td><button type="button" class="button-link nrx-remove-row">×</button></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<template data-nrx-template="items">
					<tr>
						<td><textarea name="<?php echo esc_attr( $base ); ?>[items][__i__]" rows="2"></textarea></td>
						<td><textarea name="<?php echo esc_attr( $base ); ?>[items_en][__i__]" rows="2"></textarea></td>
						<td><button type="button" class="button-link nrx-remove-row">×</button></td>
					</tr>
				</template>
				<p><button type="button" class="button nrx-add-row" data-target="items"><?php esc_html_e( 'Añadir punto', 'nuestrarx-core' ); ?></button></p>
				<p class="nrx-field-help"><?php esc_html_e( 'Cada punto se muestra como texto informativo. Las casillas obligatorias se configuran abajo.', 'nuestrarx-core' ); ?></p>

				<h4><?php esc_html_e( 'Casillas que el paciente debe marcar', 'nuestrarx-core' ); ?></h4>
				<table class="widefat nrx-repeater">
					<thead><tr><th><?php esc_html_e( 'Clave', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Texto ES', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Texto EN', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Obligatoria', 'nuestrarx-core' ); ?></th><th></th></tr></thead>
					<tbody data-nrx-repeater="checkboxes" data-name="<?php echo esc_attr( $base . '[checkboxes]' ); ?>">
					<?php foreach ( array_values( (array) ( $step['checkboxes'] ?? array() ) ) as $ri => $row ) : ?>
						<?php nrx_quiz_admin_repeater_row( $base . '[checkboxes]', $ri, $row, array( 'key' => array( 'label' => 'key' ), 'label' => array( 'label' => 'ES' ), 'label_en' => array( 'label' => 'EN' ), 'required' => array( 'type' => 'checkbox' ) ) ); ?>
					<?php endforeach; ?>
					</tbody>
				</table>
				<template data-nrx-template="checkboxes">
					<?php
					nrx_quiz_admin_repeater_row(
						$base . '[checkboxes]',
						'__i__',
						array(),
						array(
							'key' => array( 'label' => 'key' ),
							'label' => array( 'label' => 'ES' ),
							'label_en' => array( 'label' => 'EN' ),
							'required' => array( 'type' => 'checkbox' ),
						)
					);
					?>
				</template>
				<p><button type="button" class="button nrx-add-row" data-target="checkboxes"><?php esc_html_e( 'Añadir casilla', 'nuestrarx-core' ); ?></button></p>

				<div class="nrx-field-grid">
					<p><label><?php esc_html_e( 'Botón "no deseo continuar" (ES)', 'nuestrarx-core' ); ?><br>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[decline_label]" value="<?php echo esc_attr( $step['decline_label'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Botón "no deseo continuar" (EN)', 'nuestrarx-core' ); ?><br>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[decline_label_en]" value="<?php echo esc_attr( $step['decline_label_en'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Enlace secundario (ES)', 'nuestrarx-core' ); ?><br>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[secondary_label]" value="<?php echo esc_attr( $step['secondary_label'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'URL del enlace secundario', 'nuestrarx-core' ); ?><br>
						<input type="url" name="<?php echo esc_attr( $base ); ?>[secondary_url]" value="<?php echo esc_attr( $step['secondary_url'] ?? '' ); ?>" placeholder="https://wa.me/…"></label></p>
				</div>
			<?php endif; ?>

			<h4><?php esc_html_e( 'Mostrar este paso solo si…', 'nuestrarx-core' ); ?></h4>
			<table class="widefat nrx-repeater">
				<thead><tr><th><?php esc_html_e( 'Campo', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Operador', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Valor (coma para listas)', 'nuestrarx-core' ); ?></th><th></th></tr></thead>
				<tbody data-nrx-repeater="conditions" data-name="<?php echo esc_attr( $base . '[conditions]' ); ?>">
				<?php foreach ( array_values( (array) ( $step['conditions'] ?? array() ) ) as $ri => $row ) : ?>
					<tr>
						<td><input type="text" name="<?php echo esc_attr( $base ); ?>[conditions][<?php echo esc_attr( (string) $ri ); ?>][field]" value="<?php echo esc_attr( $row['field'] ?? '' ); ?>"></td>
						<td>
							<select name="<?php echo esc_attr( $base ); ?>[conditions][<?php echo esc_attr( (string) $ri ); ?>][operator]">
								<?php foreach ( nrx_quiz_operators() as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row['operator'] ?? '', $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="text" name="<?php echo esc_attr( $base ); ?>[conditions][<?php echo esc_attr( (string) $ri ); ?>][value]" value="<?php echo esc_attr( is_array( $row['value'] ?? '' ) ? implode( ', ', $row['value'] ) : ( $row['value'] ?? '' ) ); ?>"></td>
						<td><button type="button" class="button-link nrx-remove-row">×</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<template data-nrx-template="conditions">
					<tr>
						<td><input type="text" name="<?php echo esc_attr( $base ); ?>[conditions][__i__][field]" value=""></td>
						<td>
							<select name="<?php echo esc_attr( $base ); ?>[conditions][__i__][operator]">
								<?php foreach ( nrx_quiz_operators() as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="text" name="<?php echo esc_attr( $base ); ?>[conditions][__i__][value]" value=""></td>
						<td><button type="button" class="button-link nrx-remove-row">×</button></td>
					</tr>
				</template>
			</table>
			<p><button type="button" class="button nrx-add-row" data-target="conditions"><?php esc_html_e( 'Añadir condición', 'nuestrarx-core' ); ?></button></p>
			<p class="nrx-field-help"><?php esc_html_e( 'Todas las condiciones deben cumplirse. Los pasos ocultos no se validan ni se envían.', 'nuestrarx-core' ); ?></p>

			<div class="nrx-step-actions">
				<button type="submit" class="button" name="nrx_quiz_action" value="move_up:<?php echo esc_attr( (string) $index ); ?>">↑ <?php esc_html_e( 'Subir', 'nuestrarx-core' ); ?></button>
				<button type="submit" class="button" name="nrx_quiz_action" value="move_down:<?php echo esc_attr( (string) $index ); ?>">↓ <?php esc_html_e( 'Bajar', 'nuestrarx-core' ); ?></button>
				<button type="submit" class="button" name="nrx_quiz_action" value="duplicate_step:<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Duplicar', 'nuestrarx-core' ); ?></button>
				<button type="submit" class="button nrx-danger" name="nrx_quiz_action" value="delete_step:<?php echo esc_attr( (string) $index ); ?>" onclick="return confirm('¿Eliminar este paso del borrador?');"><?php esc_html_e( 'Eliminar', 'nuestrarx-core' ); ?></button>
			</div>
		</div>
	</details>
	<?php
}

/**
 * Builder screen.
 */
function nrx_quiz_admin_page() {
	if ( ! nrx_can_edit_quiz() ) {
		wp_die( esc_html__( 'No tienes permiso para editar la evaluación.', 'nuestrarx-core' ) );
	}
	$config    = nrx_quiz_get( 'draft' );
	$published = nrx_quiz_get( 'published' );
	$notice    = get_transient( 'nrx_quiz_notice_' . get_current_user_id() );
	if ( $notice ) {
		delete_transient( 'nrx_quiz_notice_' . get_current_user_id() );
	}
	$dirty      = nrx_quiz_has_draft_changes();
	$revisions  = get_option( 'nrx_quiz_revisions', array() );
	$preview    = add_query_arg(
		array(
			'nrx_quiz_preview' => 1,
			'_wpnonce'         => wp_create_nonce( 'nrx_quiz_preview' ),
		),
		nrx_intake_url()
	);
	$field_keys = array();
	foreach ( (array) $config['steps'] as $step ) {
		if ( ! empty( $step['field'] ) ) {
			$field_keys[] = $step['field'];
		}
		foreach ( (array) ( $step['fields'] ?? array() ) as $sub ) {
			$field_keys[] = $sub['key'];
		}
		foreach ( (array) ( $step['checkboxes'] ?? array() ) as $box ) {
			$field_keys[] = $box['key'];
		}
	}
	$field_keys = array_values( array_unique( array_filter( $field_keys ) ) );
	?>
	<div class="wrap nrx-admin-wrap nrx-quiz-admin">
		<h1><?php esc_html_e( 'Evaluación de elegibilidad', 'nuestrarx-core' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( in_array( $notice['type'], array( 'success', 'warning', 'error' ), true ) ? $notice['type'] : 'info' ); ?> is-dismissible"><p><?php echo wp_kses_post( $notice['message'] ); ?></p></div>
		<?php endif; ?>

		<div class="nrx-quiz-header">
			<div>
				<p>
					<?php
					printf(
						esc_html__( 'Publicada: v%1$s · %2$s · %3$d pasos', 'nuestrarx-core' ),
						esc_html( (string) ( $published['version'] ?? 1 ) ),
						esc_html( (string) ( $published['updated'] ?? '—' ) ),
						count( (array) $published['steps'] )
					);
					?>
					<?php if ( $dirty ) : ?><span class="nrx-pill nrx-pill-warn"><?php esc_html_e( 'Borrador sin publicar', 'nuestrarx-core' ); ?></span><?php endif; ?>
				</p>
			</div>
			<div class="nrx-quiz-header-actions">
				<a class="button" href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Previsualizar borrador', 'nuestrarx-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( nrx_intake_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ver versión pública', 'nuestrarx-core' ); ?></a>
			</div>
		</div>

		<p class="nrx-field-help"><?php esc_html_e( 'Los cambios se guardan en un borrador. La versión pública no cambia hasta que un usuario con permiso de publicación pulse "Publicar". Cada publicación guarda una revisión recuperable.', 'nuestrarx-core' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nrx-quiz-form">
			<input type="hidden" name="action" value="nrx_quiz_admin">
			<input type="hidden" name="nrx_quiz_payload" value="">
			<?php wp_nonce_field( 'nrx_quiz_admin', '_nrx_nonce' ); ?>

			<details class="nrx-card nrx-quiz-settings">
				<summary><h2><?php esc_html_e( 'Textos de la evaluación', 'nuestrarx-core' ); ?></h2></summary>
				<div class="nrx-field-grid nrx-field-grid-2">
					<p><label><?php esc_html_e( 'Título (ES)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][title]" value="<?php echo esc_attr( $config['meta']['title'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Título (EN)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][title_en]" value="<?php echo esc_attr( $config['meta']['title_en'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Antetítulo (ES)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][intro_eyebrow]" value="<?php echo esc_attr( $config['meta']['intro_eyebrow'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Antetítulo (EN)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][intro_eyebrow_en]" value="<?php echo esc_attr( $config['meta']['intro_eyebrow_en'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Título de portada (ES)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][intro_title]" value="<?php echo esc_attr( $config['meta']['intro_title'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Título de portada (EN)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[meta][intro_title_en]" value="<?php echo esc_attr( $config['meta']['intro_title_en'] ?? '' ); ?>"></label></p>
				</div>
				<p><label><?php esc_html_e( 'Introducción (ES)', 'nuestrarx-core' ); ?><br><textarea class="large-text" rows="3" name="nrx_quiz[meta][intro_body]"><?php echo esc_textarea( $config['meta']['intro_body'] ?? '' ); ?></textarea></label></p>
				<p><label><?php esc_html_e( 'Introducción (EN)', 'nuestrarx-core' ); ?><br><textarea class="large-text" rows="3" name="nrx_quiz[meta][intro_body_en]"><?php echo esc_textarea( $config['meta']['intro_body_en'] ?? '' ); ?></textarea></label></p>
				<p><label><?php esc_html_e( 'Minutos estimados', 'nuestrarx-core' ); ?><br><input type="number" min="1" max="60" name="nrx_quiz[meta][estimated_minutes]" value="<?php echo esc_attr( (string) ( $config['meta']['estimated_minutes'] ?? 5 ) ); ?>"></label></p>

				<h3><?php esc_html_e( 'Indicadores de confianza', 'nuestrarx-core' ); ?></h3>
				<p class="nrx-field-help"><?php esc_html_e( 'Solo afirmaciones verificables. Aparecen bajo el título de la evaluación.', 'nuestrarx-core' ); ?></p>
				<div class="nrx-trust-grid">
					<?php for ( $i = 0; $i < 4; $i++ ) : ?>
						<div>
							<input type="text" name="nrx_quiz[meta][trust][<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $config['meta']['trust'][ $i ] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Texto ES', 'nuestrarx-core' ); ?>">
							<input type="text" name="nrx_quiz[meta][trust_en][<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $config['meta']['trust_en'][ $i ] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Texto EN', 'nuestrarx-core' ); ?>">
						</div>
					<?php endfor; ?>
				</div>
			</details>

			<details class="nrx-card">
				<summary><h2><?php esc_html_e( 'Configuración y reglas clínicas', 'nuestrarx-core' ); ?></h2></summary>
				<div class="nrx-field-grid">
					<p><label><?php esc_html_e( 'IMC mínimo', 'nuestrarx-core' ); ?><br><input type="number" step="0.1" min="0" name="nrx_quiz[settings][min_bmi]" value="<?php echo esc_attr( (string) ( $config['settings']['min_bmi'] ?? 25 ) ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Email de notificación', 'nuestrarx-core' ); ?><br><input type="email" name="nrx_quiz[settings][notify_email]" value="<?php echo esc_attr( $config['settings']['notify_email'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Webhook clínico (HTTPS)', 'nuestrarx-core' ); ?><br><input type="url" class="large-text" name="nrx_quiz[settings][webhook_url]" value="<?php echo esc_attr( $config['settings']['webhook_url'] ?? '' ); ?>"></label></p>
					<p><label><?php esc_html_e( 'Versión del consentimiento', 'nuestrarx-core' ); ?><br><input type="text" name="nrx_quiz[settings][consent_version]" value="<?php echo esc_attr( $config['settings']['consent_version'] ?? '' ); ?>"></label></p>
				</div>
				<div class="nrx-field-grid">
					<p><label><input type="checkbox" name="nrx_quiz[settings][require_state]" value="1" <?php checked( ! empty( $config['settings']['require_state'] ) ); ?>> <?php esc_html_e( 'Exigir estado', 'nuestrarx-core' ); ?></label></p>
					<p><label><input type="checkbox" name="nrx_quiz[settings][collect_phone]" value="1" <?php checked( ! empty( $config['settings']['collect_phone'] ) ); ?>> <?php esc_html_e( 'Recoger teléfono', 'nuestrarx-core' ); ?></label></p>
					<p><label><input type="checkbox" name="nrx_quiz[settings][collect_email]" value="1" <?php checked( ! empty( $config['settings']['collect_email'] ) ); ?>> <?php esc_html_e( 'Recoger email', 'nuestrarx-core' ); ?></label></p>
					<p><label><input type="checkbox" name="nrx_quiz[settings][store_clinical_answers]" value="1" <?php checked( ! empty( $config['settings']['store_clinical_answers'] ) ); ?>> <?php esc_html_e( 'Guardar respuestas clínicas cifradas', 'nuestrarx-core' ); ?></label></p>
				</div>
				<p class="nrx-field-help"><?php esc_html_e( 'Si hay webhook configurado, las respuestas se envían firmadas y no se almacenan en WordPress por defecto.', 'nuestrarx-core' ); ?></p>

				<h3><?php esc_html_e( 'Reglas de seguridad (se evalúan en orden)', 'nuestrarx-core' ); ?></h3>
				<table class="widefat nrx-repeater">
					<thead><tr>
						<th><?php esc_html_e( 'Activa', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Etiqueta', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Campo', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Operador', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Valor', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Resultado', 'nuestrarx-core' ); ?></th>
						<th><?php esc_html_e( 'Bandera', 'nuestrarx-core' ); ?></th>
						<th></th>
					</tr></thead>
					<tbody data-nrx-repeater="checks" data-name="nrx_quiz[checks]">
					<?php foreach ( array_values( (array) ( $config['evaluation']['checks'] ?? array() ) ) as $check_index => $check ) : ?>
						<?php $check_name = 'nrx_quiz[checks][' . $check_index . ']'; ?>
						<tr>
							<td><input type="checkbox" name="<?php echo esc_attr( $check_name ); ?>[enabled]" value="1" <?php checked( ! empty( $check['enabled'] ) ); ?>></td>
							<td><input type="text" name="<?php echo esc_attr( $check_name ); ?>[label]" value="<?php echo esc_attr( $check['label'] ?? '' ); ?>"></td>
							<td><input type="text" name="<?php echo esc_attr( $check_name ); ?>[field]" value="<?php echo esc_attr( $check['field'] ?? '' ); ?>" list="nrx-field-keys"></td>
							<td>
								<select name="<?php echo esc_attr( $check_name ); ?>[operator]">
									<?php foreach ( nrx_quiz_check_operators() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $check['operator'] ?? '', $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<input type="hidden" name="<?php echo esc_attr( $check_name ); ?>[id]" value="<?php echo esc_attr( $check['id'] ?? '' ); ?>">
							</td>
							<td><input type="text" name="<?php echo esc_attr( $check_name ); ?>[value]" value="<?php echo esc_attr( is_array( $check['value'] ?? '' ) ? implode( ', ', $check['value'] ) : ( $check['value'] ?? '' ) ); ?>"></td>
							<td>
								<select name="<?php echo esc_attr( $check_name ); ?>[outcome]">
									<option value=""><?php esc_html_e( '— ninguna —', 'nuestrarx-core' ); ?></option>
									<?php foreach ( nrx_quiz_outcome_labels() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $check['outcome'] ?? '', $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td><input type="text" name="<?php echo esc_attr( $check_name ); ?>[flag]" value="<?php echo esc_attr( $check['flag'] ?? '' ); ?>"></td>
							<td><button type="button" class="button-link nrx-remove-row">×</button></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<template data-nrx-template="checks">
						<tr>
							<td><input type="checkbox" name="nrx_quiz[checks][__i__][enabled]" value="1"></td>
							<td><input type="text" name="nrx_quiz[checks][__i__][label]" value=""></td>
							<td><input type="text" name="nrx_quiz[checks][__i__][field]" value="" list="nrx-field-keys"></td>
							<td>
								<select name="nrx_quiz[checks][__i__][operator]">
									<?php foreach ( nrx_quiz_check_operators() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<input type="hidden" name="nrx_quiz[checks][__i__][id]" value="">
							</td>
							<td><input type="text" name="nrx_quiz[checks][__i__][value]" value=""></td>
							<td>
								<select name="nrx_quiz[checks][__i__][outcome]">
									<option value=""><?php esc_html_e( '— ninguna —', 'nuestrarx-core' ); ?></option>
									<?php foreach ( nrx_quiz_outcome_labels() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td><input type="text" name="nrx_quiz[checks][__i__][flag]" value=""></td>
							<td><button type="button" class="button-link nrx-remove-row">×</button></td>
						</tr>
					</template>
				</table>
				<p><button type="button" class="button nrx-add-row" data-target="checks"><?php esc_html_e( 'Añadir regla', 'nuestrarx-core' ); ?></button></p>
				<p><label><?php esc_html_e( 'Resultado por defecto', 'nuestrarx-core' ); ?> <select name="nrx_quiz[default_outcome]">
					<?php foreach ( nrx_quiz_outcome_labels() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $config['evaluation']['default_outcome'] ?? 'qualified', $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select></label></p>
				<datalist id="nrx-field-keys">
					<?php foreach ( $field_keys as $key ) : ?><option value="<?php echo esc_attr( $key ); ?>"></option><?php endforeach; ?>
				</datalist>
			</details>

			<div class="nrx-quiz-steps">
				<h2><?php esc_html_e( 'Pasos', 'nuestrarx-core' ); ?></h2>
				<?php foreach ( (array) $config['steps'] as $index => $step ) : ?>
					<?php nrx_quiz_admin_step( $index, $step ); ?>
				<?php endforeach; ?>
			</div>

			<div class="nrx-card">
				<h2><?php esc_html_e( 'Añadir paso', 'nuestrarx-core' ); ?></h2>
				<p>
					<select name="nrx_new_step_type">
						<?php foreach ( nrx_quiz_step_types() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="button" name="nrx_quiz_action" value="add_step"><?php esc_html_e( 'Añadir al final', 'nuestrarx-core' ); ?></button>
				</p>
				<p class="nrx-field-help"><?php esc_html_e( 'El nuevo paso se crea con valores mínimos; complétalo y usa "Validar" antes de publicar.', 'nuestrarx-core' ); ?></p>
			</div>

			<details class="nrx-card">
				<summary><h2><?php esc_html_e( 'Pantallas de resultado', 'nuestrarx-core' ); ?></h2></summary>
				<?php foreach ( nrx_quiz_outcome_labels() as $key => $label ) : $outcome = $config['settings']['outcomes'][ $key ] ?? array(); ?>
					<div class="nrx-outcome">
						<h3><?php echo esc_html( $label ); ?> <code><?php echo esc_html( $key ); ?></code></h3>
						<div class="nrx-field-grid nrx-field-grid-2">
							<p><label><?php esc_html_e( 'Antetítulo (ES)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][eyebrow]" value="<?php echo esc_attr( $outcome['eyebrow'] ?? '' ); ?>"></label></p>
							<p><label><?php esc_html_e( 'Antetítulo (EN)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][eyebrow_en]" value="<?php echo esc_attr( $outcome['eyebrow_en'] ?? '' ); ?>"></label></p>
							<p><label><?php esc_html_e( 'Título (ES)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][title]" value="<?php echo esc_attr( $outcome['title'] ?? '' ); ?>"></label></p>
							<p><label><?php esc_html_e( 'Título (EN)', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][title_en]" value="<?php echo esc_attr( $outcome['title_en'] ?? '' ); ?>"></label></p>
						</div>
						<p><label><?php esc_html_e( 'Texto (ES)', 'nuestrarx-core' ); ?><br><textarea class="large-text" rows="3" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][body]"><?php echo esc_textarea( $outcome['body'] ?? '' ); ?></textarea></label></p>
						<p><label><?php esc_html_e( 'Texto (EN)', 'nuestrarx-core' ); ?><br><textarea class="large-text" rows="3" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][body_en]"><?php echo esc_textarea( $outcome['body_en'] ?? '' ); ?></textarea></label></p>
						<div class="nrx-field-grid">
							<p><label><?php esc_html_e( 'Botón (ES)', 'nuestrarx-core' ); ?><br><input type="text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][cta_label]" value="<?php echo esc_attr( $outcome['cta_label'] ?? '' ); ?>"></label></p>
							<p><label><?php esc_html_e( 'Botón (EN)', 'nuestrarx-core' ); ?><br><input type="text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][cta_label_en]" value="<?php echo esc_attr( $outcome['cta_label_en'] ?? '' ); ?>"></label></p>
							<p><label><?php esc_html_e( 'URL del botón', 'nuestrarx-core' ); ?><br><input type="text" class="large-text" name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][cta_url]" value="<?php echo esc_attr( $outcome['cta_url'] ?? '' ); ?>" placeholder="/pago-semanal/ o vacío = WhatsApp"></label></p>
							<p><label><?php esc_html_e( 'Tono', 'nuestrarx-core' ); ?><br>
								<select name="nrx_quiz[outcomes][<?php echo esc_attr( $key ); ?>][tone]">
									<?php foreach ( array( 'success' => __( 'Éxito', 'nuestrarx-core' ), 'review' => __( 'Revisión', 'nuestrarx-core' ), 'neutral' => __( 'Neutral', 'nuestrarx-core' ) ) as $tone => $tone_label ) : ?>
										<option value="<?php echo esc_attr( $tone ); ?>" <?php selected( $outcome['tone'] ?? '', $tone ); ?>><?php echo esc_html( $tone_label ); ?></option>
									<?php endforeach; ?>
								</select></label></p>
						</div>
					</div>
				<?php endforeach; ?>
			</details>

			<div class="nrx-quiz-footer">
				<div class="nrx-quiz-footer-actions">
					<button type="submit" class="button button-primary button-large" name="nrx_quiz_action" value="save"><?php esc_html_e( 'Guardar borrador', 'nuestrarx-core' ); ?></button>
					<button type="submit" class="button button-large" name="nrx_quiz_action" value="validate"><?php esc_html_e( 'Validar', 'nuestrarx-core' ); ?></button>
					<?php if ( nrx_can_publish_quiz() ) : ?>
						<button type="submit" class="button button-primary button-large" name="nrx_quiz_action" value="publish" onclick="return confirm('¿Publicar la evaluación? La versión pública cambiará inmediatamente.');"><?php esc_html_e( 'Publicar', 'nuestrarx-core' ); ?></button>
					<?php endif; ?>
				</div>
				<p><label><?php esc_html_e( 'Nota de cambio (se guarda con la revisión)', 'nuestrarx-core' ); ?><br>
					<input type="text" class="large-text" name="nrx_publish_note" value="" placeholder="<?php esc_attr_e( 'Ej. Ajuste del texto de consentimiento aprobado por cumplimiento', 'nuestrarx-core' ); ?>"></label></p>
			</div>
		</form>

		<div class="nrx-card">
			<h2><?php esc_html_e( 'Revisiones publicadas', 'nuestrarx-core' ); ?></h2>
			<?php if ( empty( $revisions ) ) : ?>
				<p><?php esc_html_e( 'Aún no hay revisiones.', 'nuestrarx-core' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Versión', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Fecha', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Usuario', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Nota', 'nuestrarx-core' ); ?></th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $revisions as $index => $revision ) : ?>
						<tr>
							<td>v<?php echo esc_html( (string) ( $revision['version'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $revision['time'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( get_userdata( (int) ( $revision['user'] ?? 0 ) )->user_login ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $revision['note'] ?? '' ) ); ?></td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="nrx_quiz_admin">
									<?php wp_nonce_field( 'nrx_quiz_admin', '_nrx_nonce' ); ?>
									<button type="submit" class="button" name="nrx_quiz_action" value="restore:<?php echo esc_attr( (string) $index ); ?>" onclick="return confirm('¿Cargar esta revisión en el borrador?');"><?php esc_html_e( 'Restaurar en borrador', 'nuestrarx-core' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
