<?php
/**
 * Content-compliance helpers.
 *
 * Enforces a few editorial rules in the admin:
 *  - every treatment record should declare a price note and disclaimer,
 *  - pages that make clinical claims are flagged for review,
 *  - the site shows a "last reviewed" line when a reviewer is configured.
 *
 * None of this replaces clinical, legal or regulatory review; it exists so a
 * non-technical editor can see what still needs sign-off.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show a checklist on treatment edit screens.
 */
function wla_treatment_compliance_box() {
	add_meta_box(
		'wla_treatment_compliance',
		__( 'Publishing checklist', 'weightloss-affordable-core' ),
		'wla_render_treatment_compliance_box',
		'nrx_treatment',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'wla_treatment_compliance_box' );

/**
 * Render the checklist.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function wla_render_treatment_compliance_box( $post ) {
	$checks = array(
		__( 'Price and billing period match what the pharmacy will charge', 'weightloss-affordable-core' ) => 'price',
		__( 'Safety text has been reviewed by a licensed clinician', 'weightloss-affordable-core' )        => 'safety',
		__( 'Eligibility wording does not promise treatment', 'weightloss-affordable-core' )               => 'eligibility',
		__( 'Disclaimer is present', 'weightloss-affordable-core' )                                        => 'disclaimer',
		__( 'No testimonial, photo or before/after image is used without consent', 'weightloss-affordable-core' ) => null,
		__( 'Medication name and active ingredient verified with the pharmacy', 'weightloss-affordable-core' ) => 'medication_name',
	);

	echo '<ul class="wla-checklist">';
	foreach ( $checks as $label => $meta_key ) {
		$done = $meta_key ? '' !== (string) get_post_meta( $post->ID, '_wla_' . $meta_key, true ) : false;
		printf(
			'<li><span class="wla-checklist__mark %1$s" aria-hidden="true">%2$s</span> %3$s</li>',
			$done ? 'is-done' : 'is-todo',
			$done ? '✓' : '○',
			esc_html( $label )
		);
	}
	echo '</ul>';
	echo '<p class="description">' . esc_html__( 'This checklist is a reminder, not an approval. Clinical, legal and regulatory sign-off is still required before launch.', 'weightloss-affordable-core' ) . '</p>';
}

/**
 * Print the clinical review line for pages that need it.
 *
 * @return string
 */
function wla_clinical_review_line() {
	$reviewer = wla_get_settings( 'clinical_reviewer' );
	$date     = wla_get_settings( 'last_reviewed' );
	if ( ! $reviewer && ! $date ) {
		return '';
	}
	$parts = array();
	if ( $reviewer ) {
		/* translators: %s: reviewer name */
		$parts[] = sprintf( __( 'Clinically reviewed by %s', 'weightloss-affordable-core' ), $reviewer );
	}
	if ( $date ) {
		/* translators: %s: date */
		$parts[] = sprintf( __( 'last reviewed %s', 'weightloss-affordable-core' ), $date );
	}
	return implode( ', ', $parts ) . '.';
}

/**
 * Append the review line to medical pages in the footer area.
 */
function wla_output_review_line() {
	if ( ! is_singular() ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( ! in_array( get_post_type( $post_id ), array( 'nrx_treatment', 'page' ), true ) ) {
		return;
	}
	$line = wla_clinical_review_line();
	if ( ! $line ) {
		return;
	}
	echo '<div class="site-container wla-review-line"><p>' . esc_html( $line ) . '</p></div>';
}
add_action( 'wp_footer', 'wla_output_review_line', 5 );
