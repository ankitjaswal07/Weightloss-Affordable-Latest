<?php
/**
 * Shared section rendering wrapper for page templates.
 *
 * Pages built with Elementor render their Elementor document (each widget is
 * one section). Every other page renders the stored section list, so content
 * is never hard-coded in the template.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Should the current page render its Elementor document?
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function wla_page_uses_elementor( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return false;
	}
	return '' !== (string) get_post_meta( $post_id, '_elementor_data', true );
}

/**
 * Render a page's content body.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_render_page_body( $post_id ) {
	if ( wla_page_uses_elementor( $post_id ) ) {
		the_content();
		return;
	}

	if ( function_exists( 'wla_render_page_sections' ) ) {
		$sections = wla_get_page_sections( $post_id, false );
		if ( ! empty( $sections ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- section renderers escape their own output.
			echo wla_render_page_sections( $post_id );
			return;
		}
	}

	// Fallback: plain WordPress content.
	$content = get_post_field( 'post_content', $post_id );
	if ( '' !== trim( (string) $content ) ) {
		echo '<div class="site-container section-pad entry-content">';
		the_content();
		echo '</div>';
	}
}
