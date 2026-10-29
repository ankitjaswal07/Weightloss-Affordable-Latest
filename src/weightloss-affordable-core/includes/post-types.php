<?php
/**
 * Custom post types and taxonomies.
 *
 * - nrx_treatment  : treatment & medication records (public, Elementor-enabled)
 * - wla_review     : consented patient reviews (not public by default)
 * - wla_submission : enquiry / consultation submissions (private, admin only)
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register content types.
 */
function wla_register_content_types() {

	register_post_type(
		'nrx_treatment',
		array(
			'labels'              => array(
				'name'               => __( 'Treatments', 'weightloss-affordable-core' ),
				'singular_name'      => __( 'Treatment', 'weightloss-affordable-core' ),
				'add_new'            => __( 'Add treatment', 'weightloss-affordable-core' ),
				'add_new_item'       => __( 'Add new treatment', 'weightloss-affordable-core' ),
				'edit_item'          => __( 'Edit treatment', 'weightloss-affordable-core' ),
				'new_item'           => __( 'New treatment', 'weightloss-affordable-core' ),
				'view_item'          => __( 'View treatment', 'weightloss-affordable-core' ),
				'view_items'         => __( 'View treatments', 'weightloss-affordable-core' ),
				'search_items'       => __( 'Search treatments', 'weightloss-affordable-core' ),
				'not_found'          => __( 'No treatments found.', 'weightloss-affordable-core' ),
				'not_found_in_trash' => __( 'No treatments found in the bin.', 'weightloss-affordable-core' ),
				'all_items'          => __( 'All treatments', 'weightloss-affordable-core' ),
				'menu_name'          => __( 'Treatments', 'weightloss-affordable-core' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-heart',
			'menu_position'       => 21,
			'has_archive'         => 'treatments',
			'rewrite'             => array(
				'slug'       => 'treatments',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'taxonomies'          => array( 'nrx_treatment_category' ),
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'nrx_treatment_category',
		array( 'nrx_treatment' ),
		array(
			'labels'            => array(
				'name'          => __( 'Treatment categories', 'weightloss-affordable-core' ),
				'singular_name' => __( 'Treatment category', 'weightloss-affordable-core' ),
				'add_new_item'  => __( 'Add treatment category', 'weightloss-affordable-core' ),
				'edit_item'     => __( 'Edit treatment category', 'weightloss-affordable-core' ),
				'search_items'  => __( 'Search treatment categories', 'weightloss-affordable-core' ),
				'menu_name'     => __( 'Categories', 'weightloss-affordable-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'treatment-category',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'wla_review',
		array(
			'labels'          => array(
				'name'          => __( 'Patient reviews', 'weightloss-affordable-core' ),
				'singular_name' => __( 'Patient review', 'weightloss-affordable-core' ),
				'add_new_item'  => __( 'Add patient review', 'weightloss-affordable-core' ),
				'edit_item'     => __( 'Edit patient review', 'weightloss-affordable-core' ),
				'all_items'     => __( 'All patient reviews', 'weightloss-affordable-core' ),
				'menu_name'     => __( 'Reviews', 'weightloss-affordable-core' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=nrx_treatment',
			'show_in_rest'    => true,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'menu_icon'       => 'dashicons-format-quote',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);

	register_post_type(
		'wla_submission',
		array(
			'labels'              => array(
				'name'          => __( 'Enquiries', 'weightloss-affordable-core' ),
				'singular_name' => __( 'Enquiry', 'weightloss-affordable-core' ),
				'all_items'     => __( 'All enquiries', 'weightloss-affordable-core' ),
				'edit_item'     => __( 'View enquiry', 'weightloss-affordable-core' ),
				'menu_name'     => __( 'Enquiries', 'weightloss-affordable-core' ),
				'not_found'     => __( 'No enquiries yet.', 'weightloss-affordable-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'supports'            => array( 'title' ),
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'wla_register_content_types' );

/**
 * Let Elementor edit treatment records.
 *
 * @param array $post_types Supported post types.
 * @return array
 */
function wla_elementor_cpt_support( $post_types ) {
	foreach ( array( 'nrx_treatment', 'page' ) as $type ) {
		if ( ! in_array( $type, (array) $post_types, true ) ) {
			$post_types[] = $type;
		}
	}
	return $post_types;
}
add_filter( 'elementor_cpt_support', 'wla_elementor_cpt_support' );

/**
 * Flush rewrite rules once after activation.
 */
function wla_maybe_flush_rewrites() {
	if ( get_option( 'wla_core_rewrite_version' ) !== WLA_CORE_VERSION ) {
		wla_register_content_types();
		flush_rewrite_rules( false );
		update_option( 'wla_core_rewrite_version', WLA_CORE_VERSION, false );
	}
}
add_action( 'admin_init', 'wla_maybe_flush_rewrites' );
