<?php
/**
 * WP-CLI commands for repeatable installs.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import the demo site from the command line.
 */
function wla_cli_import_demo() {
	WP_CLI::log( 'Starting WeightLossAffordable demo import…' );
	$report = wla_run_demo_import();
	foreach ( $report['warnings'] as $warning ) {
		WP_CLI::warning( $warning );
	}
	if ( ! empty( $report['fatal'] ) ) {
		WP_CLI::error( 'Import stopped. See the warnings above.' );
	}
	WP_CLI::success( $report['summary'] );
}

WP_CLI::add_command( 'wla import-demo', 'wla_cli_import_demo' );

/**
 * Report the state of the installation.
 */
function wla_cli_status() {
	foreach ( wla_environment_checks() as $check ) {
		WP_CLI::log( sprintf( '[%s] %s: %s', strtoupper( $check['status'] ), $check['label'], $check['value'] ) );
	}
}

WP_CLI::add_command( 'wla status', 'wla_cli_status' );
