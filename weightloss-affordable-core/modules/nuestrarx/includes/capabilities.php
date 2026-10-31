<?php
/**
 * Capabilities and the clinical reviewer role.
 *
 * Content editing uses standard WordPress post capabilities. Clinical and
 * privacy-sensitive surfaces use dedicated capabilities so access can be
 * granted separately from general content editing.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability map.
 *
 * @return array<string,string>
 */
function nrx_capabilities() {
	return array(
		'nrx_manage_settings'   => 'Administrar ajustes de NuestraRx',
		'nrx_manage_quiz'       => 'Editar la evaluación de elegibilidad',
		'nrx_publish_quiz'      => 'Publicar cambios de la evaluación',
		'nrx_manage_intakes'    => 'Ver evaluaciones recibidas',
		'nrx_export_intakes'    => 'Exportar evaluaciones',
		'nrx_manage_compliance' => 'Administrar cumplimiento y reclamaciones',
		'nrx_manage_content'    => 'Administrar tratamientos y secciones',
		'nrx_view_protocols'    => 'Leer los protocolos clínicos restringidos',
	);
}

/**
 * Grant capabilities to administrators and create the reviewer role.
 */
function nrx_grant_capabilities() {
	$caps = array_keys( nrx_capabilities() );
	$role = get_role( 'administrator' );
	if ( $role ) {
		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
	}

	// Keep an existing reviewer role in sync when capabilities are added.
	$existing = get_role( 'nrx_clinical_reviewer' );
	if ( $existing ) {
		$existing->add_cap( 'nrx_view_protocols' );
	}

	$reviewer_caps = array(
		'read'               => true,
		'nrx_manage_quiz'    => true,
		'nrx_publish_quiz'   => true,
		'nrx_manage_compliance' => true,
		'nrx_manage_intakes' => true,
		'nrx_view_protocols' => true,
	);
	add_role( 'nrx_clinical_reviewer', 'Revisor clínico NuestraRx', $reviewer_caps );

	update_option( 'nrx_caps_version', NRX_CORE_VERSION, false );
}

/**
 * Ensure new capabilities exist after plugin updates.
 */
function nrx_maybe_upgrade_capabilities() {
	if ( get_option( 'nrx_caps_version' ) !== NRX_CORE_VERSION ) {
		nrx_grant_capabilities();
	}
}
add_action( 'admin_init', 'nrx_maybe_upgrade_capabilities' );

/**
 * Restrict intake records to holders of the intake capability.
 *
 * @param array  $caps    Required capabilities.
 * @param string $cap     Capability being checked.
 * @param int    $user_id User ID.
 * @param array  $args    Arguments.
 * @return array
 */
function nrx_map_intake_caps( $caps, $cap, $user_id, $args ) {
	$managed = array( 'edit_nrx_intake', 'read_nrx_intake', 'delete_nrx_intake', 'edit_nrx_intakes', 'edit_others_nrx_intakes', 'publish_nrx_intakes', 'read_private_nrx_intakes', 'delete_nrx_intakes', 'delete_private_nrx_intakes', 'delete_published_nrx_intakes', 'delete_others_nrx_intakes', 'edit_private_nrx_intakes', 'edit_published_nrx_intakes' );
	if ( in_array( $cap, $managed, true ) ) {
		return user_can( $user_id, 'nrx_manage_intakes' ) || user_can( $user_id, 'manage_options' ) ? array( 'read' ) : array( 'do_not_allow' );
	}
	return $caps;
}
add_filter( 'map_meta_cap', 'nrx_map_intake_caps', 10, 4 );

/**
 * Hide intake records from users without permission in the admin list.
 *
 * @param WP_Query $query Query.
 */
function nrx_hide_intakes_from_unauthorized( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'nrx_intake' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( ! current_user_can( 'nrx_manage_intakes' ) && ! current_user_can( 'manage_options' ) ) {
		$query->set( 'post__in', array( 0 ) );
	}
}
add_action( 'pre_get_posts', 'nrx_hide_intakes_from_unauthorized' );

/**
 * Only allow setting the quiz publish option with the publish capability.
 *
 * @return bool
 */
function nrx_can_publish_quiz() {
	return current_user_can( 'nrx_publish_quiz' ) || current_user_can( 'manage_options' );
}

/**
 * Only allow changing clinical screening logic with the manage capability.
 *
 * @return bool
 */
function nrx_can_edit_quiz() {
	return current_user_can( 'nrx_manage_quiz' ) || current_user_can( 'manage_options' );
}
