<?php
/** Content types for business information that should survive theme changes. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_register_content_types() {
	$types = array(
		'wla_service' => array( __( 'Services', 'weightloss-affordable-core' ), __( 'Service', 'weightloss-affordable-core' ), 'dashicons-heart', true ),
		'wla_treatment' => array( __( 'Treatment information', 'weightloss-affordable-core' ), __( 'Treatment information', 'weightloss-affordable-core' ), 'dashicons-info-outline', true ),
		'wla_package' => array( __( 'Packages', 'weightloss-affordable-core' ), __( 'Package', 'weightloss-affordable-core' ), 'dashicons-tag', true ),
		'wla_review' => array( __( 'Reviews', 'weightloss-affordable-core' ), __( 'Review', 'weightloss-affordable-core' ), 'dashicons-format-chat', false ),
		'wla_team' => array( __( 'Team profiles', 'weightloss-affordable-core' ), __( 'Team profile', 'weightloss-affordable-core' ), 'dashicons-groups', false ),
	);
	foreach ( $types as $type => $data ) {
		register_post_type( $type, array(
			'labels' => array(
				'name' => $data[0], 'singular_name' => $data[1],
				'add_new_item' => sprintf( __( 'Add %s', 'weightloss-affordable-core' ), $data[1] ),
				'edit_item' => sprintf( __( 'Edit %s', 'weightloss-affordable-core' ), $data[1] ),
				'view_item' => sprintf( __( 'View %s', 'weightloss-affordable-core' ), $data[1] ),
				'not_found' => __( 'No items found.', 'weightloss-affordable-core' ),
			),
			'public' => true,
			'show_in_rest' => true,
			'has_archive' => $data[3],
			'rewrite' => array( 'slug' => str_replace( '_', '-', $type ) ),
			'menu_icon' => $data[2],
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'capability_type' => 'post',
			'map_meta_cap' => true,
		) );
	}
	register_taxonomy( 'wla_resource_topic', array( 'post', 'wla_service', 'wla_treatment' ), array(
		'labels' => array( 'name' => __( 'Resource topics', 'weightloss-affordable-core' ), 'singular_name' => __( 'Resource topic', 'weightloss-affordable-core' ) ),
		'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
		'rewrite' => array( 'slug' => 'resource-topic' ),
	) );
}
add_action( 'init', 'wla_register_content_types' );

/** Keep custom content types editable with Elementor's supported CPT setting. */
function wla_elementor_content_type_support( $post_types ) {
	foreach ( array( 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team' ) as $type ) {
		if ( ! in_array( $type, $post_types, true ) ) { $post_types[] = $type; }
	}
	return $post_types;
}
add_filter( 'elementor_cpt_support', 'wla_elementor_content_type_support' );

/** Expose business records in Polylang's language settings and translation UI. */
function wla_polylang_post_types( $post_types, $is_settings ) {
	if ( ! $is_settings ) {
		foreach ( array( 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team' ) as $type ) { $post_types[ $type ] = $type; }
	}
	return $post_types;
}
add_filter( 'pll_get_post_types', 'wla_polylang_post_types', 10, 2 );
function wla_polylang_taxonomies( $taxonomies, $is_settings ) {
	if ( ! $is_settings ) { $taxonomies['wla_resource_topic'] = 'wla_resource_topic'; }
	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'wla_polylang_taxonomies', 10, 2 );
