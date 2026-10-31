<?php
/**
 * Schema-driven admin fields.
 *
 * Section types and treatments declare their editable fields once; the same
 * schema renders the form, sanitizes input and exposes values to templates.
 * Every field can be bilingual (Spanish value + English value).
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a group of fields.
 *
 * @param array  $fields       Field schema.
 * @param array  $values       Current values keyed by field key.
 * @param string $name_prefix  Input name prefix, e.g. nrx_meta.
 * @param array  $args         Optional render args.
 */
function nrx_render_fields( $fields, $values, $name_prefix, $args = array() ) {
	$values = is_array( $values ) ? $values : array();
	foreach ( (array) $fields as $field ) {
		if ( empty( $field['key'] ) && empty( $field['type'] ) ) {
			continue;
		}
		if ( isset( $field['type'] ) && 'heading' === $field['type'] ) {
			echo '<h3 class="nrx-field-heading">' . esc_html( $field['label'] ?? '' ) . '</h3>';
			if ( ! empty( $field['help'] ) ) {
				echo '<p class="nrx-field-heading-help">' . esc_html( $field['help'] ) . '</p>';
			}
			continue;
		}
		$key   = (string) $field['key'];
		$value = array_key_exists( $key, $values ) ? $values[ $key ] : ( $field['default'] ?? '' );
		if ( ! empty( $field['bilingual'] ) && function_exists( 'nrx_is_spanish' ) && ! nrx_is_spanish() ) {
			$en_key   = $key . '_en';
			$en_value = array_key_exists( $en_key, $values ) ? $values[ $en_key ] : ( $field['default_en'] ?? '' );
			if ( '' === (string) $en_value ) {
				$en_value = $value;
			}
			$en_field = $field;
			$en_field['bilingual'] = false;
			unset( $en_field['default'] );
			nrx_render_field( $en_field, $en_value, $name_prefix . '[' . $en_key . ']' );
			continue;
		}
		nrx_render_field( $field, $value, $name_prefix . '[' . $key . ']' );
		if ( ! empty( $field['bilingual'] ) ) {
			$en_key   = $key . '_en';
			$en_value = array_key_exists( $en_key, $values ) ? $values[ $en_key ] : ( $field['default_en'] ?? '' );
			$en_field = $field;
			$en_field['label'] = ( $field['label'] ?? $key ) . ' — English';
			$en_field['help']  = __( 'Shown when the visitor chooses EN. If empty, the primary text is used.', 'nuestrarx-core' );
			$en_field['bilingual'] = false;
			unset( $en_field['default'] );
			nrx_render_field( $en_field, $en_value, $name_prefix . '[' . $en_key . ']' );
		}
	}
}

/**
 * Render one field.
 *
 * @param array  $field Field definition.
 * @param mixed  $value Current value.
 * @param string $name  HTML input name.
 */
function nrx_render_field( $field, $value, $name ) {
	$type        = $field['type'] ?? 'text';
	$id          = 'nrx_' . sanitize_key( str_replace( array( '[', ']' ), '_', $name ) ) . '_' . wp_rand( 1000, 9999 );
	$label       = $field['label'] ?? '';
	$help        = $field['help'] ?? '';
	$placeholder = $field['placeholder'] ?? '';
	$required    = ! empty( $field['required'] );
	$wrap_class  = 'nrx-field nrx-field-' . sanitize_html_class( $type );
	if ( 'checkbox' === $type ) {
		$wrap_class .= ' nrx-field-checkbox';
	}
	if ( 'richtext' === $type ) {
		$wrap_class .= ' nrx-field-wide';
	}

	echo '<div class="' . esc_attr( $wrap_class ) . '">';
	if ( 'checkbox' !== $type ) {
		echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $required ? ' <span class="nrx-req">*</span>' : '' ) . '</label>';
	}

	switch ( $type ) {
		case 'textarea':
		case 'richtext':
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( $field['rows'] ?? ( 'richtext' === $type ? 8 : 4 ) ) . '" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( is_scalar( $value ) ? (string) $value : '' ) . '</textarea>';
			if ( 'richtext' === $type ) {
				echo '<p class="nrx-field-help">' . esc_html__( 'HTML permitido: párrafos, listas, enlaces, negritas. No se permiten scripts.', 'nuestrarx-core' ) . '</p>';
			}
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . '>';
			if ( empty( $field['required'] ) ) {
				echo '<option value="">' . esc_html( $field['empty_label'] ?? __( '— Selecciona —', 'nuestrarx-core' ) ) . '</option>';
			}
			foreach ( (array) ( $field['options'] ?? array() ) as $opt_key => $opt_label ) {
				echo '<option value="' . esc_attr( $opt_key ) . '"' . selected( (string) $value, (string) $opt_key, false ) . '>' . esc_html( $opt_label ) . '</option>';
			}
			echo '</select>';
			break;

		case 'checkbox':
			echo '<label class="nrx-checkbox-row"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( (int) $value, 1, false ) . '> <span>' . esc_html( $label ) . '</span></label>';
			break;

		case 'image':
			$url = is_scalar( $value ) ? (string) $value : '';
			echo '<div class="nrx-image-field" data-nrx-image-field>';
			echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $url ) . '" placeholder="https://… o ID de la biblioteca" inputmode="url">';
			echo '<button type="button" class="button" data-nrx-media>' . esc_html__( 'Elegir imagen', 'nuestrarx-core' ) . '</button>';
			echo '<button type="button" class="button-link" data-nrx-media-clear>' . esc_html__( 'Quitar', 'nuestrarx-core' ) . '</button>';
			if ( $url ) {
				echo '<span class="nrx-image-preview">' . ( is_numeric( $url ) ? wp_get_attachment_image( (int) $url, 'medium' ) : '<img src="' . esc_url( $url ) . '" alt="" loading="lazy">' ) . '</span>';
			}
			echo '</div>';
			break;

		case 'number':
			echo '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" step="' . esc_attr( $field['step'] ?? '1' ) . '"' . ( isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '' ) . ( isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '' ) . '>';
			break;

		case 'color':
			echo '<input type="color" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ? $value : '#173d36' ) . '">';
			break;

		case 'repeater':
			nrx_render_repeater( $field, $value, $name );
			break;

		case 'text':
		case 'email':
		case 'url':
		case 'tel':
		default:
			$input_type = in_array( $type, array( 'email', 'url', 'tel' ), true ) ? $type : 'text';
			echo '<input type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $value ) ? (string) $value : '' ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $required ? ' required' : '' ) . '>';
			break;
	}

	if ( $help ) {
		echo '<p class="nrx-field-help">' . wp_kses_post( $help ) . '</p>';
	}
	echo '</div>';
}

/**
 * Render a repeater field with a JS row template.
 *
 * @param array  $field Field definition.
 * @param mixed  $value Rows.
 * @param string $name  Input name.
 */
function nrx_render_repeater( $field, $value, $name ) {
	$rows  = is_array( $value ) ? array_values( $value ) : array();
	$subs  = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array();
	$label = $field['row_label'] ?? __( 'Elemento', 'nuestrarx-core' );
	echo '<div class="nrx-repeater" data-nrx-repeater data-next-index="' . esc_attr( count( $rows ) ) . '">';
	echo '<div class="nrx-repeater-rows" data-nrx-rows>';

	if ( empty( $rows ) ) {
		$rows = array( array() );
	}
	foreach ( $rows as $index => $row ) {
		echo '<div class="nrx-repeater-row" data-nrx-row>';
		echo '<div class="nrx-repeater-bar"><strong>' . esc_html( $label ) . ' <span data-nrx-row-number>' . ( $index + 1 ) . '</span></strong>';
		echo '<span class="nrx-repeater-actions">';
		echo '<button type="button" class="button-link" data-nrx-move="up" aria-label="' . esc_attr__( 'Mover arriba', 'nuestrarx-core' ) . '">↑</button>';
		echo '<button type="button" class="button-link" data-nrx-move="down" aria-label="' . esc_attr__( 'Mover abajo', 'nuestrarx-core' ) . '">↓</button>';
		echo '<button type="button" class="button-link" data-nrx-clone>' . esc_html__( 'Duplicar', 'nuestrarx-core' ) . '</button>';
		echo '<button type="button" class="button-link nrx-danger" data-nrx-remove>' . esc_html__( 'Eliminar', 'nuestrarx-core' ) . '</button>';
		echo '</span></div>';
		echo '<div class="nrx-repeater-fields">';
		nrx_render_fields( $subs, is_array( $row ) ? $row : array(), $name . '[' . $index . ']' );
		echo '</div></div>';
	}
	echo '</div>';

	// Template used by JS for new rows. It is inert until cloned.
	echo '<template data-nrx-row-template>';
	echo '<div class="nrx-repeater-row" data-nrx-row>';
	echo '<div class="nrx-repeater-bar"><strong>' . esc_html( $label ) . ' <span data-nrx-row-number></span></strong>';
	echo '<span class="nrx-repeater-actions">';
	echo '<button type="button" class="button-link" data-nrx-move="up">↑</button>';
	echo '<button type="button" class="button-link" data-nrx-move="down">↓</button>';
	echo '<button type="button" class="button-link" data-nrx-clone>' . esc_html__( 'Duplicar', 'nuestrarx-core' ) . '</button>';
	echo '<button type="button" class="button-link nrx-danger" data-nrx-remove>' . esc_html__( 'Eliminar', 'nuestrarx-core' ) . '</button>';
	echo '</span></div><div class="nrx-repeater-fields">';
	nrx_render_fields( $subs, array(), $name . '[__INDEX__]' );
	echo '</div></div>';
	echo '</template>';

	echo '<button type="button" class="button nrx-add-row" data-nrx-add-row>+ ' . esc_html( sprintf( __( 'Añadir %s', 'nuestrarx-core' ), strtolower( $label ) ) ) . '</button>';
	echo '</div>';
}

/**
 * Sanitize a set of fields from request input.
 *
 * @param array $fields Field schema.
 * @param array $input  Raw input.
 * @return array
 */
function nrx_sanitize_fields( $fields, $input ) {
	$input = is_array( $input ) ? $input : array();
	$out   = array();
	foreach ( (array) $fields as $field ) {
		if ( empty( $field['key'] ) || ( isset( $field['type'] ) && 'heading' === $field['type'] ) ) {
			continue;
		}
		$key   = (string) $field['key'];
		$value = array_key_exists( $key, $input ) ? $input[ $key ] : null;
		$out[ $key ] = nrx_sanitize_field( $field, $value );
		if ( ! empty( $field['bilingual'] ) ) {
			$en_key = $key . '_en';
			$en_value = array_key_exists( $en_key, $input ) ? $input[ $en_key ] : null;
			$en_field = $field;
			$en_field['bilingual'] = false;
			$out[ $en_key ] = nrx_sanitize_field( $en_field, $en_value );
		}
	}
	return $out;
}

/**
 * Sanitize a single field value.
 *
 * @param array $field Field definition.
 * @param mixed $value Raw value.
 * @return mixed
 */
function nrx_sanitize_field( $field, $value ) {
	$type = $field['type'] ?? 'text';
	switch ( $type ) {
		case 'richtext':
			return is_scalar( $value ) ? wp_kses_post( wp_unslash( (string) $value ) ) : '';
		case 'textarea':
			return is_scalar( $value ) ? sanitize_textarea_field( wp_unslash( (string) $value ) ) : '';
		case 'email':
			return is_scalar( $value ) ? sanitize_email( wp_unslash( (string) $value ) ) : '';
		case 'url':
		case 'image':
			return is_scalar( $value ) ? esc_url_raw( trim( wp_unslash( (string) $value ) ) ) : '';
		case 'tel':
			return is_scalar( $value ) ? sanitize_text_field( wp_unslash( (string) $value ) ) : '';
		case 'number':
			if ( ! is_scalar( $value ) || '' === trim( (string) $value ) || ! is_numeric( $value ) ) {
				return '';
			}
			return (float) $value;
		case 'checkbox':
			return ! empty( $value ) ? 1 : 0;
		case 'color':
			return sanitize_hex_color( is_scalar( $value ) ? wp_unslash( (string) $value ) : '' );
		case 'select':
			$options = (array) ( $field['options'] ?? array() );
			$value   = is_scalar( $value ) ? sanitize_text_field( wp_unslash( (string) $value ) ) : '';
			return array_key_exists( $value, $options ) ? $value : '';
		case 'repeater':
			$rows = is_array( $value ) ? array_slice( $value, 0, 100 ) : array();
			$subs = (array) ( $field['fields'] ?? array() );
			$out  = array();
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$clean = nrx_sanitize_fields( $subs, $row );
				// Drop completely empty rows.
				$has_value = false;
				foreach ( $clean as $item ) {
					if ( is_scalar( $item ) ? '' !== trim( (string) $item ) : ! empty( $item ) ) {
						$has_value = true;
						break;
					}
				}
				if ( $has_value ) {
					$out[] = $clean;
				}
			}
			return $out;
		case 'text':
		default:
			return is_scalar( $value ) ? sanitize_text_field( wp_unslash( (string) $value ) ) : '';
	}
}

/**
 * Load post meta values for a schema.
 *
 * @param int   $post_id Post ID.
 * @param array $fields  Field schema.
 * @return array
 */
function nrx_get_meta_values( $post_id, $fields ) {
	$values = array();
	foreach ( (array) $fields as $field ) {
		if ( empty( $field['key'] ) || ( isset( $field['type'] ) && 'heading' === $field['type'] ) ) {
			continue;
		}
		$key = (string) $field['key'];
		$values[ $key ] = get_post_meta( $post_id, '_nrx_' . $key, true );
		if ( ! empty( $field['bilingual'] ) ) {
			$values[ $key . '_en' ] = get_post_meta( $post_id, '_nrx_' . $key . '_en', true );
		}
	}
	return $values;
}

/**
 * Persist post meta values from a schema.
 *
 * @param int   $post_id Post ID.
 * @param array $fields  Field schema.
 * @param array $values  Sanitized values.
 */
function nrx_save_meta_values( $post_id, $fields, $values ) {
	foreach ( (array) $fields as $field ) {
		if ( empty( $field['key'] ) || ( isset( $field['type'] ) && 'heading' === $field['type'] ) ) {
			continue;
		}
		$key = (string) $field['key'];
		if ( array_key_exists( $key, $values ) ) {
			update_post_meta( $post_id, '_nrx_' . $key, $values[ $key ] );
		}
		if ( ! empty( $field['bilingual'] ) ) {
			$en_key = $key . '_en';
			if ( array_key_exists( $en_key, $values ) ) {
				update_post_meta( $post_id, '_nrx_' . $en_key, $values[ $en_key ] );
			}
		}
	}
}

/**
 * Standard meta box save guard shared by content types.
 *
 * @param int    $post_id Post ID.
 * @param string $nonce_action Nonce action.
 * @param string $nonce_field Nonce field name.
 * @return bool Whether it is safe to continue.
 */
function nrx_can_save_meta( $post_id, $nonce_action, $nonce_field ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST[ $nonce_field ] ) ) {
		return false;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) );
	if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
}
