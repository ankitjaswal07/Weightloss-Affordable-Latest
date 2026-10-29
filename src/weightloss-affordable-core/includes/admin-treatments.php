<?php
/**
 * Treatment record editor.
 *
 * Adds structured, grouped custom fields to the Treatments post type so a
 * non-technical administrator can manage clinical and commercial details
 * without touching code or one large text box.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the treatment details meta box.
 */
function wla_add_treatment_meta_box() {
	add_meta_box(
		'wla_treatment_details',
		__( 'Treatment details', 'weightloss-affordable-core' ),
		'wla_render_treatment_meta_box',
		'nrx_treatment',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wla_add_treatment_meta_box' );

/**
 * Render the treatment fields.
 *
 * @param WP_Post $post Post.
 */
function wla_render_treatment_meta_box( $post ) {
	wp_nonce_field( 'wla_save_treatment', 'wla_treatment_nonce' );

	$groups    = wla_treatment_field_schema();
	$repeaters = wla_treatment_repeater_schema();

	echo '<p class="description">' . esc_html__( 'Only publish information you can verify. Fields left blank are hidden on the front end rather than shown empty.', 'weightloss-affordable-core' ) . '</p>';

	foreach ( $groups as $group_key => $group ) {
		echo '<div class="wla-treat-group">';
		echo '<h3>' . esc_html( $group['label'] ) . '</h3>';
		echo '<div class="wla-treat-group__grid">';
		foreach ( $group['fields'] as $key => $field ) {
			$value = get_post_meta( $post->ID, '_wla_' . $key, true );
			if ( '' === $value || null === $value ) {
				$value = $field['default'] ?? '';
			}
			wla_admin_field( 'wla_treat[' . $key . ']', $field, $value );
		}
		echo '</div></div>';
	}

	foreach ( $repeaters as $key => $repeater ) {
		$rows = get_post_meta( $post->ID, '_wla_' . $key, true );
		echo '<div class="wla-treat-group">';
		wla_admin_repeater( 'wla_treat_rows[' . $key . ']', $repeater, is_array( $rows ) ? $rows : array() );
		echo '</div>';
	}
}

/**
 * Save treatment fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_save_treatment_meta( $post_id ) {
	if ( ! isset( $_POST['wla_treatment_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wla_treatment_nonce'] ) ), 'wla_save_treatment' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'nrx_treatment' !== get_post_type( $post_id ) ) {
		return;
	}

	$schema = wla_treatment_field_schema();
	$raw    = isset( $_POST['wla_treat'] ) && is_array( $_POST['wla_treat'] ) ? wp_unslash( $_POST['wla_treat'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
	foreach ( $schema as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			$value = isset( $raw[ $key ] ) ? wla_sanitize_meta_value( $field['type'], $raw[ $key ] ) : '';
			if ( '' === $value ) {
				delete_post_meta( $post_id, '_wla_' . $key );
				continue;
			}
			update_post_meta( $post_id, '_wla_' . $key, $value );
		}
	}

	$repeaters = wla_treatment_repeater_schema();
	$rows_raw  = isset( $_POST['wla_treat_rows'] ) && is_array( $_POST['wla_treat_rows'] ) ? wp_unslash( $_POST['wla_treat_rows'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
	foreach ( $repeaters as $group_key => $group ) {
		$clean = array();
		$rows  = isset( $rows_raw[ $group_key ] ) && is_array( $rows_raw[ $group_key ] ) ? $rows_raw[ $group_key ] : array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$item = array();
			$keep = false;
			foreach ( $group['fields'] as $sub_key => $sub ) {
				$value         = isset( $row[ $sub_key ] ) ? wla_sanitize_meta_value( $sub['type'], $row[ $sub_key ] ) : '';
				$item[ $sub_key ] = $value;
				if ( '' !== $value ) {
					$keep = true;
				}
			}
			if ( $keep ) {
				$clean[] = $item;
			}
		}
		if ( empty( $clean ) ) {
			delete_post_meta( $post_id, '_wla_' . $group_key );
		} else {
			update_post_meta( $post_id, '_wla_' . $group_key, $clean );
		}
	}
}
add_action( 'save_post', 'wla_save_treatment_meta' );

/**
 * Admin list columns for treatments.
 *
 * @param array $columns Columns.
 * @return array
 */
function wla_treatment_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['wla_medication'] = __( 'Medication', 'weightloss-affordable-core' );
			$new['wla_price']      = __( 'Price', 'weightloss-affordable-core' );
			$new['wla_order']      = __( 'Order', 'weightloss-affordable-core' );
		}
	}
	return $new;
}
add_filter( 'manage_nrx_treatment_posts_columns', 'wla_treatment_columns' );

/**
 * Render treatment columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function wla_treatment_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'wla_medication':
			echo esc_html( (string) get_post_meta( $post_id, '_wla_medication_name', true ) );
			break;
		case 'wla_price':
			$price  = (string) get_post_meta( $post_id, '_wla_price', true );
			$period = (string) get_post_meta( $post_id, '_wla_billing_period', true );
			echo $price ? esc_html( trim( $price . ' ' . $period ) ) : '—';
			break;
		case 'wla_order':
			echo (int) get_post_meta( $post_id, '_wla_display_order', true );
			break;
	}
}
add_action( 'manage_nrx_treatment_posts_custom_column', 'wla_treatment_column_content', 10, 2 );

/**
 * Order treatments by the display-order field in the admin list.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function wla_treatment_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'nrx_treatment' !== $query->get( 'post_type' ) ) {
		return;
	}
	$query->set( 'orderby', 'menu_order title' );
	$query->set( 'order', 'ASC' );
}
add_action( 'pre_get_posts', 'wla_treatment_admin_order' );
