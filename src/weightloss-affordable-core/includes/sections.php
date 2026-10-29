<?php
/**
 * Page sections storage (the Elementor-free editing layer).
 *
 * Every page stores an ordered list of sections at `_wla_sections`:
 * [ [ 'type' => 'hero', 'fields' => [ ... ] ], ... ]
 *
 * Pages created by the demo importer also store the same structure inside
 * Elementor data (one widget per section), so both editors stay in sync:
 *  - saving Elementor widgets writes through to `_wla_sections`
 *    (see includes/elementor.php),
 *  - the meta box writes `_wla_sections` directly.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch raw sections for a post.
 *
 * @param int  $post_id  Post ID.
 * @param bool $fallback Whether to fall back to the slug blueprint.
 * @return array
 */
function wla_get_page_sections( $post_id = 0, $fallback = true ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$raw = get_post_meta( $post_id, '_wla_sections', true );
	if ( is_string( $raw ) ) {
		$raw = json_decode( $raw, true );
	}
	if ( is_array( $raw ) && ! empty( $raw ) ) {
		return wla_normalize_sections( $raw );
	}

	if ( $fallback && 'page' === get_post_type( $post_id ) ) {
		$slug = get_post_field( 'post_name', $post_id );
		return wla_blueprint_sections( $slug );
	}

	return array();
}

/**
 * Normalise a list of sections against the schema.
 *
 * @param array $sections Raw sections.
 * @return array
 */
function wla_normalize_sections( $sections ) {
	$out = array();
	foreach ( (array) $sections as $section ) {
		if ( ! is_array( $section ) || empty( $section['type'] ) ) {
			continue;
		}
		$type = sanitize_key( $section['type'] );
		if ( ! wla_section_exists( $type ) ) {
			continue;
		}
		$fields = isset( $section['fields'] ) && is_array( $section['fields'] ) ? $section['fields'] : array();
		$out[]  = array(
			'type'   => $type,
			'fields' => wla_normalize_section( $type, $fields ),
		);
	}
	return $out;
}

/**
 * Does a section type exist?
 *
 * @param string $type Section type.
 * @return bool
 */
function wla_section_exists( $type ) {
	$schema = wla_section_schema();
	return isset( $schema[ $type ] );
}

/**
 * Build a section list from a page blueprint slug.
 *
 * @param string $slug Page slug.
 * @return array
 */
function wla_blueprint_sections( $slug ) {
	$blueprints = wla_default_page_blueprints();
	$sections   = array();
	// Unknown pages get no invented sections; they simply render their content.
	if ( ! isset( $blueprints[ $slug ] ) ) {
		return array();
	}
	$types = $blueprints[ $slug ];
	$index      = 1;
	foreach ( $types as $type ) {
		$fields = wla_section_defaults( $type );
		if ( isset( $fields['index'] ) ) {
			$fields['index'] = sprintf( '%02d', $index );
			++$index;
		}
		$sections[] = array(
			'type'   => $type,
			'fields' => $fields,
		);
	}
	return $sections;
}

/**
 * Persist sections for a post.
 *
 * @param int   $post_id  Post ID.
 * @param array $sections Sections.
 * @return void
 */
function wla_save_page_sections( $post_id, $sections ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return;
	}
	$clean = wla_normalize_sections( $sections );
	update_post_meta( $post_id, '_wla_sections', wp_slash( wp_json_encode( $clean ) ) );
	update_post_meta( $post_id, '_wla_sections_updated', current_time( 'mysql' ) );
}

/**
 * Append one section (used by the demo importer and duplicate actions).
 *
 * @param int    $post_id Post ID.
 * @param string $type    Section type.
 * @param array  $fields  Field overrides.
 * @return void
 */
function wla_add_page_section( $post_id, $type, $fields = array() ) {
	$sections   = wla_get_page_sections( $post_id, false );
	$sections[] = array(
		'type'   => $type,
		'fields' => wp_parse_args( $fields, wla_section_defaults( $type ) ),
	);
	wla_save_page_sections( $post_id, $sections );
}

/**
 * Fetch a treatment meta value with a default.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without prefix.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function wla_treatment_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, '_wla_' . $key, true );
	if ( '' === $value || null === $value || false === $value ) {
		return $default;
	}
	return $value;
}

/**
 * Fetch a treatment repeater value.
 *
 * @param int    $post_id Post ID.
 * @param string $group   Repeater group key.
 * @return array
 */
function wla_treatment_rows( $post_id, $group ) {
	$rows = get_post_meta( $post_id, '_wla_' . $group, true );
	return is_array( $rows ) ? $rows : array();
}
