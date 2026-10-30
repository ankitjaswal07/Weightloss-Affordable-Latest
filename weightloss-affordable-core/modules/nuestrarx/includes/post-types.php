<?php
/**
 * Custom post types and taxonomies.
 *
 * Treatments are the product source of truth when WooCommerce is not in
 * use. Sections are the page-builder records. Intakes are private clinical
 * records and are never publicly queryable.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register content types.
 */
function nrx_register_post_types() {
	register_post_type(
		'nrx_treatment',
		array(
			'labels'              => array(
				'name'          => __( 'Treatments', 'nuestrarx-core' ),
				'singular_name' => __( 'Treatment', 'nuestrarx-core' ),
				'add_new_item'  => __( 'Add treatment', 'nuestrarx-core' ),
				'edit_item'     => __( 'Edit treatment', 'nuestrarx-core' ),
				'view_item'     => __( 'View treatment', 'nuestrarx-core' ),
				'search_items'  => __( 'Search treatments', 'nuestrarx-core' ),
				'not_found'     => __( 'No treatments found.', 'nuestrarx-core' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => 'nrx_treatment',
			'menu_icon'           => 'dashicons-heart',
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
			'taxonomies'          => array( 'nrx_treatment_class' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'exclude_from_search' => false,
		)
	);

	register_post_type(
		'nrx_section',
		array(
			'labels'          => array(
				'name'          => __( 'Secciones de página', 'nuestrarx-core' ),
				'singular_name' => __( 'Sección', 'nuestrarx-core' ),
				'add_new_item'  => __( 'Añadir sección', 'nuestrarx-core' ),
				'edit_item'     => __( 'Editar sección', 'nuestrarx-core' ),
				'search_items'  => __( 'Buscar secciones', 'nuestrarx-core' ),
				'not_found'     => __( 'Aún no hay secciones.', 'nuestrarx-core' ),
			),
			'public'          => false,
			'publicly_queryable' => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-layout',
			'supports'        => array( 'title', 'revisions', 'page-attributes' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);

	register_post_type(
		'nrx_clinician',
		array(
			'labels'          => array(
				'name'          => __( 'Equipo clínico', 'nuestrarx-core' ),
				'singular_name' => __( 'Profesional clínico', 'nuestrarx-core' ),
				'add_new_item'  => __( 'Añadir profesional', 'nuestrarx-core' ),
				'edit_item'     => __( 'Editar profesional', 'nuestrarx-core' ),
			),
			'public'          => true,
			'publicly_queryable' => true,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=nrx_section',
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-groups',
			'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => 'equipo-clinico', 'with_front' => false ),
			'exclude_from_search' => true,
		)
	);

	register_post_type(
		'nrx_pharmacy',
		array(
			'labels'          => array(
				'name'          => __( 'Farmacias asociadas', 'nuestrarx-core' ),
				'singular_name' => __( 'Farmacia', 'nuestrarx-core' ),
				'add_new_item'  => __( 'Añadir farmacia', 'nuestrarx-core' ),
				'edit_item'     => __( 'Editar farmacia', 'nuestrarx-core' ),
			),
			'public'          => false,
			'publicly_queryable' => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=nrx_section',
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-store',
			'supports'        => array( 'title', 'excerpt', 'page-attributes' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);

	register_post_type(
		'nrx_testimonial',
		array(
			'labels'          => array(
				'name'          => __( 'Testimonios', 'nuestrarx-core' ),
				'singular_name' => __( 'Testimonio', 'nuestrarx-core' ),
				'add_new_item'  => __( 'Añadir testimonio', 'nuestrarx-core' ),
				'edit_item'     => __( 'Editar testimonio', 'nuestrarx-core' ),
			),
			'public'          => false,
			'publicly_queryable' => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=nrx_section',
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-format-quote',
			'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);

	register_post_type(
		'nrx_intake',
		array(
			'labels'          => array(
				'name'          => __( 'Evaluaciones', 'nuestrarx-core' ),
				'singular_name' => __( 'Evaluación', 'nuestrarx-core' ),
			),
			'public'          => false,
			'publicly_queryable' => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-clipboard',
			'supports'        => array( 'title' ),
			'capability_type' => array( 'nrx_intake', 'nrx_intakes' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
			'exclude_from_search' => true,
		)
	);

	register_taxonomy(
		'nrx_treatment_class',
		array( 'nrx_treatment' ),
		array(
			'labels'            => array(
				'name'          => __( 'Clases de tratamiento', 'nuestrarx-core' ),
				'singular_name' => __( 'Clase de tratamiento', 'nuestrarx-core' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'nrx_register_post_types' );

/**
 * Intake records must never appear in sitemaps or public queries.
 *
 * @param array $types Post types.
 * @return array
 */
function nrx_exclude_private_from_sitemaps( $types ) {
	unset( $types['nrx_intake'] );
	return $types;
}
add_filter( 'wp_sitemaps_post_types', 'nrx_exclude_private_from_sitemaps' );

/**
 * Ensure anonymous requests can never query intake records through WP_Query.
 *
 * @param array $public Post types that are publicly queryable.
 * @return array
 */
function nrx_public_post_types( $public ) {
	$public = is_array( $public ) ? $public : array();
	if ( ( $key = array_search( 'nrx_intake', $public, true ) ) !== false ) {
		unset( $public[ $key ] );
	}
	return $public;
}
add_filter( 'query_var_post_types', 'nrx_public_post_types' );

/**
 * Disable XML-RPC for the public site by default; intakes never need it and
 * it is a common credential-stuffing surface. A filter allows opting back in.
 *
 * @param bool $enabled Whether XML-RPC is enabled.
 * @return bool
 */
function nrx_maybe_disable_xmlrpc( $enabled ) {
	if ( apply_filters( 'nrx_enable_xmlrpc', false ) ) {
		return $enabled;
	}
	return false;
}
add_filter( 'xmlrpc_enabled', 'nrx_maybe_disable_xmlrpc' );

/**
 * Remove the WordPress version generator for a smaller fingerprint surface.
 */
remove_action( 'wp_head', 'wp_generator' );
