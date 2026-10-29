<?php
/**
 * Template tags.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Key pages used for navigation fallbacks.
 *
 * @return array slug => label
 */
function wla_primary_routes() {
	return array(
		'weight-loss-treatments' => __( 'Treatments', 'weightloss-affordable' ),
		'how-it-works'           => __( 'How it works', 'weightloss-affordable' ),
		'pricing-and-plans'      => __( 'Pricing', 'weightloss-affordable' ),
		'compare-treatments'     => __( 'Compare', 'weightloss-affordable' ),
		'resources'              => __( 'Resources', 'weightloss-affordable' ),
		'frequently-asked-questions' => __( 'FAQs', 'weightloss-affordable' ),
		'about'                  => __( 'About', 'weightloss-affordable' ),
	);
}

/**
 * Primary navigation fallback when no menu is assigned.
 *
 * @return void
 */
function wla_nav_fallback() {
	echo '<ul class="wla-menu-fallback">';
	foreach ( wla_primary_routes() as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			continue;
		}
		printf(
			'<li><a href="%1$s"%2$s>%3$s</a></li>',
			esc_url( get_permalink( $page ) ),
			( is_page( $page->ID ) ? ' aria-current="page"' : '' ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Footer explore links fallback.
 *
 * @return void
 */
function wla_footer_links_fallback( $slugs ) {
	foreach ( (array) $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			continue;
		}
		printf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
	}
}

/**
 * Site logo or the bundled brand mark.
 *
 * @return void
 */
function wla_site_brand() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'custom-logo', 'alt' => get_bloginfo( 'name' ) ) );
		return;
	}
	echo wla_render_brand_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Published page count helper used by the importer summary.
 *
 * @return int
 */
function wla_published_page_count() {
	$count = wp_count_posts( 'page' );
	return isset( $count->publish ) ? (int) $count->publish : 0;
}
