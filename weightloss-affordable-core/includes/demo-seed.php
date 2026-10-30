<?php
/** Safe, idempotent seed of the original NRX quiz draft and held treatment records. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_core_seed_nuestra_demo_data( &$report = null ) {
	if ( function_exists( 'wla_import_clinical_site' ) ) {
		wla_import_clinical_site( $report, array() );
		return;
	}
	if ( function_exists( 'nrx_quiz_default_config' ) ) {
		$draft = nrx_quiz_default_config();
		$draft['version'] = 1;
		update_option( 'nrx_quiz_draft', nrx_quiz_normalize( $draft ), false );
		update_option( 'nrx_quiz_published', nrx_quiz_normalize( $draft ), false );
	}
	if ( function_exists( 'nrx_seed_treatment' ) && defined( 'NRX_CORE_DIR' ) ) {
		$records = require NRX_CORE_DIR . 'content/treatments.php';
		foreach ( $records as $slug => $record ) {
			$record['fields']['require_approval'] = 0;
			$id = nrx_seed_treatment( $slug, $record, true );
			if ( $id ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
				if ( is_array( $report ) ) {
					$report['treatments'] = ( $report['treatments'] ?? 0 ) + 1;
				}
			}
		}
	}
}
