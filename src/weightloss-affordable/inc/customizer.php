<?php
/**
 * Customizer options for the header and footer text.
 *
 * Everything visually significant is editable in Elementor or the Page
 * sections box; these settings cover the two global strips that sit outside a
 * page's content.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function wla_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'wla_header_footer',
		array(
			'title'       => __( 'Header & footer text', 'weightloss-affordable' ),
			'priority'    => 30,
			'description' => __( 'Use Appearance → Menus for navigation. These fields control the small text strips above the header and in the footer.', 'weightloss-affordable' ),
		)
	);

	$fields = array(
		'wla_topline'          => array( 'label' => __( 'Top strip text', 'weightloss-affordable' ), 'default' => __( 'Clinician-led weight care · English-language support', 'weightloss-affordable' ), 'type' => 'text' ),
		'wla_phone'            => array( 'label' => __( 'Phone number shown in the top strip', 'weightloss-affordable' ), 'default' => '', 'type' => 'text' ),
		'wla_footer_text'      => array( 'label' => __( 'Footer introduction', 'weightloss-affordable' ), 'default' => __( 'Clear information about clinician-led weight care, so you can decide your next step with confidence.', 'weightloss-affordable' ), 'type' => 'textarea' ),
		'wla_footer_disclaimer' => array( 'label' => __( 'Footer medical disclaimer', 'weightloss-affordable' ), 'default' => __( 'This website provides general information about weight-management services. It is not medical advice and does not create a clinician–patient relationship. Prescription treatment is available only after an assessment by a licensed clinician, and not everyone is eligible. Individual results vary and are never guaranteed.', 'weightloss-affordable' ), 'type' => 'textarea' ),
	);

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'section' => 'wla_header_footer',
				'type'    => 'textarea' === $field['type'] ? 'textarea' : 'text',
			)
		);
	}
}
add_action( 'customize_register', 'wla_customize_register' );
