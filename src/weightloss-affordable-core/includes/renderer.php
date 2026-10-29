<?php
/**
 * Section rendering.
 *
 * Converts stored section fields into the argument arrays expected by the
 * theme's presentation helpers (template-parts/sections/*.php), then renders.
 * The theme owns markup; this file owns the data contract, so the same
 * renderer serves the front end, Elementor widgets and the admin preview.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map section types to theme renderer functions.
 *
 * @return array
 */
function wla_section_render_map() {
	return array(
		'hero'              => 'wla_render_hero_section',
		'intro'             => 'wla_render_intro_section',
		'product'           => 'wla_render_product_section',
		'services'          => 'wla_render_services_section',
		'journey'           => 'wla_render_journey_section',
		'approach'          => 'wla_render_approach_section',
		'pricing'           => 'wla_render_pricing_section',
		'resources'         => 'wla_render_resources_explorer_section',
		'reviews'           => 'wla_render_reviews_section',
		'faq'               => 'wla_render_faq_section',
		'contact'           => 'wla_render_contact_section',
		'subpage_hero'      => 'wla_render_subpage_hero',
		'cta_band'          => 'wla_render_cta_band',
		'page_nav'          => 'wla_render_page_nav_strip',
		'rich_text'         => 'wla_render_rich_text_section',
		'treatment_grid'    => 'wla_render_treatment_grid_section',
		'treatment_detail'  => 'wla_render_treatment_detail_section',
	);
}

/**
 * Render a stored section.
 *
 * @param array $section Section array (type + fields).
 * @param array $context Optional context (post_id, current_slug).
 * @return string
 */
function wla_render_section( $section, $context = array() ) {
	if ( ! is_array( $section ) || empty( $section['type'] ) ) {
		return '';
	}
	$type = sanitize_key( $section['type'] );
	$map  = wla_section_render_map();
	if ( ! isset( $map[ $type ] ) || ! function_exists( $map[ $type ] ) ) {
		return '';
	}
	$fields = isset( $section['fields'] ) && is_array( $section['fields'] ) ? $section['fields'] : array();
	$fields = wp_parse_args( $fields, wla_section_defaults( $type ) );
	$args   = wla_prepare_section_args( $type, $fields, $context );

	return call_user_func( $map[ $type ], $args );
}

/**
 * Render every section stored on a page.
 *
 * @param int   $post_id Post ID.
 * @param array $context Context.
 * @return string
 */
function wla_render_page_sections( $post_id = 0, $context = array() ) {
	$post_id  = $post_id ? absint( $post_id ) : get_the_ID();
	$sections = wla_get_page_sections( $post_id );
	$context  = wp_parse_args(
		$context,
		array(
			'post_id'      => $post_id,
			'current_slug' => $post_id ? get_post_field( 'post_name', $post_id ) : '',
		)
	);

	$out = '';
	foreach ( $sections as $section ) {
		$out .= wla_render_section( $section, $context );
	}
	return $out;
}

/**
 * Convert stored fields to renderer arguments.
 *
 * @param string $type    Section type.
 * @param array  $f       Stored fields.
 * @param array  $context Render context.
 * @return array
 */
function wla_prepare_section_args( $type, $f, $context = array() ) {
	switch ( $type ) {

		case 'hero':
			$f['proof']   = wla_repeater_to_pairs( $f['proof'], array( 'value', 'label' ) );
			$f['marquee'] = wla_lines_to_array( $f['marquee'] );
			return $f;

		case 'product':
			$f['specs']   = wla_repeater_to_pairs( $f['specs'], array( 'label', 'value' ) );
			$f['metrics'] = wla_repeater_to_pairs( $f['metrics'], array( 'value', 'label' ) );
			$f['bullets'] = wla_lines_to_array( $f['bullets'] );
			return $f;

		case 'services':
			$cards = array();
			foreach ( (array) $f['cards'] as $card ) {
				if ( ! is_array( $card ) ) {
					continue;
				}
				$cards[] = array(
					'tag'   => $card['tag'] ?? '',
					'title' => $card['title'] ?? '',
					'text'  => $card['text'] ?? '',
					'body'  => $card['text'] ?? '',
					'cta'   => $card['cta'] ?? '',
					'href'  => $card['href'] ?? '',
					'icon'  => $card['icon'] ?? 'heart',
				);
			}
			$f['cards'] = $cards;
			return $f;

		case 'journey':
			$steps = array();
			foreach ( (array) $f['steps'] as $step ) {
				if ( ! is_array( $step ) ) {
					continue;
				}
				$steps[] = array(
					$step['number'] ?? '',
					$step['title'] ?? '',
					$step['description'] ?? '',
					$step['meta'] ?? '',
					$step['detail'] ?? '',
				);
			}
			$f['steps'] = $steps;
			$f['chips'] = wla_lines_to_array( $f['chips'] );
			return $f;

		case 'approach':
			$f['bullets'] = wla_lines_to_array( $f['bullets'] );
			return $f;

		case 'pricing':
			$f['rows'] = wla_repeater_to_pairs( $f['rows'], array( 'label', 'value' ) );
			return $f;

		case 'resources':
			$guides = array();
			foreach ( (array) $f['guides'] as $guide ) {
				if ( ! is_array( $guide ) ) {
					continue;
				}
				$guide['questions'] = wla_lines_to_array( $guide['questions'] ?? '' );
				$guides[]           = $guide;
			}
			$f['guides'] = $guides;
			$f['strip']  = wla_lines_to_array( $f['strip'] );
			return $f;

		case 'faq':
			$f['faqs']     = wla_repeater_to_pairs( $f['faqs'], array( 'question', 'answer' ) );
			$f['show_all'] = '1' === (string) $f['show_all'];
			return $f;

		case 'contact':
			$f['checks']        = wla_lines_to_array( $f['checks'] );
			$f['topic_options'] = wla_lines_to_pairs( $f['topics'] );
			return $f;

		case 'subpage_hero':
			$f['bullets'] = wla_lines_to_array( $f['bullets'] );
			return $f;

		case 'page_nav':
			$exclude                = array_filter( array_map( 'trim', explode( ',', (string) $f['exclude'] ) ) );
			$context_slug           = isset( $context['current_slug'] ) ? $context['current_slug'] : '';
			$f['exclude']           = $exclude;
			$f['current_slug']      = $context_slug;
			return $f;

		case 'treatment_grid':
		case 'treatment_detail':
		case 'rich_text':
		case 'intro':
		case 'reviews':
		case 'cta_band':
		default:
			return $f;
	}
}

/**
 * Render all sections for the current main query page.
 *
 * @return void
 */
function wla_the_sections() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderers escape their own output.
	echo wla_render_page_sections( get_the_ID() );
}
