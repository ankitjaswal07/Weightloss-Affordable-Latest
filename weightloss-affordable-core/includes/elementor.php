<?php
/**
 * Register WeightLossAffordable Free Elementor Widget Category & All 11 Preview Widgets.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_elementor_register_category( $elements_manager ) {
	$elements_manager->add_category(
		'wla-content',
		array(
			'title' => __( 'WeightLossAffordable (Preview Pro)', 'weightloss-affordable-core' ),
			'icon'  => 'eicon-star',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'wla_elementor_register_category' );

function wla_elementor_register_widgets( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}
	require_once WLA_CORE_DIR . 'includes/widgets.php';
	require_once WLA_CORE_DIR . 'includes/widgets-home.php';
	$widgets_manager->register( new WLA_Hero_Pro_Widget() );
	$widgets_manager->register( new WLA_Philosophy_Intro_Widget() );
	$widgets_manager->register( new WLA_Service_Cards_Widget() );
	$widgets_manager->register( new WLA_Steps_Widget() );
	$widgets_manager->register( new WLA_Approach_Split_Widget() );
	$widgets_manager->register( new WLA_Pricing_Widget() );
	$widgets_manager->register( new WLA_Resources_Explorer_Widget() );
	$widgets_manager->register( new WLA_Reviews_Panel_Widget() );
	$widgets_manager->register( new WLA_FAQ_Widget() );
	$widgets_manager->register( new WLA_Contact_Section_Widget() );
	$widgets_manager->register( new WLA_Subpage_Hero_Widget() );
	$widgets_manager->register( new WLA_Cta_Band_Widget() );
	$widgets_manager->register( new WLA_Page_Nav_Widget() );
	if ( class_exists( 'WLA_Guarantee_Widget' ) ) {
		$widgets_manager->register( new WLA_Guarantee_Widget() );
		$widgets_manager->register( new WLA_Glp1_Signal_Widget() );
		$widgets_manager->register( new WLA_Treatments_Duo_Widget() );
		$widgets_manager->register( new WLA_Transformations_Widget() );
		$widgets_manager->register( new WLA_Video_Stories_Widget() );
		$widgets_manager->register( new WLA_Science_Widget() );
	}
}
add_action( 'elementor/widgets/register', 'wla_elementor_register_widgets' );
