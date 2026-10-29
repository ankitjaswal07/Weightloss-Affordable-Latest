<?php
/**
 * Reusable admin field renderers (meta boxes).
 *
 * Used by the page-section editor and the treatment editor so both look and
 * behave the same, and so new fields only need a schema entry.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render one field control.
 *
 * @param string $name  Input name (may contain brackets).
 * @param array  $field Field definition.
 * @param mixed  $value Current value.
 * @return void
 */
function wla_admin_field( $name, $field, $value ) {
	$id   = 'wla-f-' . substr( md5( $name ), 0, 10 );
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	if ( 'repeater' === $type ) {
		wla_admin_repeater( $name, $field, $value );
		return;
	}

	echo '<p class="wla-field wla-field--' . esc_attr( $type ) . '">';
	printf( '<label for="%1$s"><strong>%2$s</strong></label>', esc_attr( $id ), esc_html( $field['label'] ?? '' ) );

	switch ( $type ) {
		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( (string) $value )
			);
			break;

		case 'wysiwyg':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="10" class="large-text code">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( (string) $value )
			);
			echo '<span class="description">' . esc_html__( 'Basic HTML is allowed (headings, lists, links, emphasis).', 'weightloss-affordable-core' ) . '</span>';
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( (array) ( $field['options'] ?? array() ) as $opt_value => $opt_label ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $opt_value ),
					selected( (string) $value, (string) $opt_value, false ),
					esc_html( $opt_label )
				);
			}
			echo '</select>';
			break;

		case 'media':
			printf(
				'<span class="wla-media-field"><input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text" /> <button type="button" class="button wla-media-pick">%4$s</button> <button type="button" class="button-link wla-media-clear">%5$s</button></span>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_html__( 'Choose image', 'weightloss-affordable-core' ),
				esc_html__( 'Clear', 'weightloss-affordable-core' )
			);
			echo '<span class="description">' . esc_html__( 'Choose from the media library, or enter a theme image file name (for example hero-care.jpg).', 'weightloss-affordable-core' ) . '</span>';
			break;

		case 'number':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" class="small-text" step="1" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
			break;

		default:
			printf(
				'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="large-text" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<span class="description">' . esc_html( $field['help'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Render a repeater control.
 *
 * @param string $name  Base input name.
 * @param array  $field Field definition (fields => sub fields).
 * @param mixed  $rows  Current rows.
 * @return void
 */
function wla_admin_repeater( $name, $field, $rows ) {
	$rows  = is_array( $rows ) ? array_values( $rows ) : array();
	$subs  = (array) ( $field['fields'] ?? array() );
	$index = 0;

	echo '<div class="wla-repeater" data-wla-repeater>';
	echo '<p class="wla-repeater__label"><strong>' . esc_html( $field['label'] ?? '' ) . '</strong></p>';
	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
	}

	echo '<div class="wla-repeater__rows" data-wla-repeater-rows>';

	// Existing rows.
	foreach ( $rows as $row ) {
		wla_admin_repeater_row( $name, $subs, is_array( $row ) ? $row : array(), $index );
		++$index;
	}

	// Blueprint row used by JS to append new rows (also the no-JS fallback).
	wla_admin_repeater_row( $name, $subs, array(), '__i__', true );

	echo '</div>';
	printf(
		'<button type="button" class="button wla-repeater__add">%s</button>',
		esc_html__( '+ Add row', 'weightloss-affordable-core' )
	);
	echo '</div>';
}

/**
 * Render one repeater row.
 *
 * @param string $name     Base input name.
 * @param array  $subs     Sub-field definitions.
 * @param array  $row      Row values.
 * @param int|string $index Row index.
 * @param bool   $blueprint Whether this is the hidden blueprint row.
 * @return void
 */
function wla_admin_repeater_row( $name, $subs, $row, $index, $blueprint = false ) {
	$style = $blueprint ? ' style="display:none"' : '';
	printf( '<div class="wla-repeater__row"%s>', $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="wla-repeater__fields">';
	foreach ( $subs as $key => $sub ) {
		$field_name = $name . '[' . $index . '][' . $key . ']';
		$sub_type   = isset( $sub['type'] ) ? $sub['type'] : 'text';
		$value      = isset( $row[ $key ] ) ? $row[ $key ] : '';
		echo '<p class="wla-field">';
		printf(
			'<label><span>%1$s</span></label>',
			esc_html( $sub['label'] ?? $key )
		);
		if ( 'textarea' === $sub_type ) {
			printf(
				'<textarea name="%1$s" rows="2" class="large-text">%2$s</textarea>',
				esc_attr( $field_name ),
				esc_textarea( (string) $value )
			);
		} elseif ( 'select' === $sub_type ) {
			printf( '<select name="%1$s">', esc_attr( $field_name ) );
			foreach ( (array) ( $sub['options'] ?? array() ) as $opt_value => $opt_label ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $opt_value ),
					selected( (string) $value, (string) $opt_value, false ),
					esc_html( $opt_label )
				);
			}
			echo '</select>';
		} else {
			printf(
				'<input type="text" name="%1$s" value="%2$s" class="large-text" />',
				esc_attr( $field_name ),
				esc_attr( (string) $value )
			);
		}
		echo '</p>';
	}
	echo '</div>';
	echo '<p class="wla-repeater__actions"><button type="button" class="button-link wla-row-up">' . esc_html__( 'Move up', 'weightloss-affordable-core' ) . '</button> <button type="button" class="button-link wla-row-down">' . esc_html__( 'Move down', 'weightloss-affordable-core' ) . '</button> <button type="button" class="button-link-delete wla-row-remove">' . esc_html__( 'Remove', 'weightloss-affordable-core' ) . '</button></p>';
	echo '</div>';
}
