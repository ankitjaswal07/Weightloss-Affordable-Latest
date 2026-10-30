<?php
/** Elementor widget loader; safe when Elementor is not installed. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function wla_experience_elementor_register() {
	add_action( 'elementor/elements/categories_registered', function( $manager ) {
		$manager->add_category( 'wla-experience', array( 'title' => __( 'WeightLossAffordable Experience', 'wla-experience' ), 'icon' => 'eicon-heart' ) );
	} );
	add_action( 'elementor/widgets/register', function( $manager ) {
		if ( class_exists( '\Elementor\Widget_Base' ) ) {
			require_once WLA_EXPERIENCE_DIR . 'includes/elementor-widget.php';
			$manager->register( new WLA_Experience_Widget() );
		}
	} );
}
add_action( 'plugins_loaded', 'wla_experience_elementor_register', 20 );
