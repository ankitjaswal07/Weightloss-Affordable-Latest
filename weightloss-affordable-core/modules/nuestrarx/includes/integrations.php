<?php
/**
 * Integration status, consent-gated analytics and privacy helpers.
 *
 * No analytics tag is loaded before the visitor grants consent. Quiz events
 * never include answers or contact data.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration readiness for the admin dashboard.
 *
 * @return array<string,array{label:string,status:string,note:string}>
 */
function nrx_integration_status() {
	$quiz      = nrx_quiz_get( 'published' );
	$webhook   = trim( (string) ( $quiz['settings']['webhook_url'] ?? '' ) );
	$webhook   = $webhook ? $webhook : (string) nrx_setting( 'quiz_webhook_url', '' );
	$checkout  = (string) nrx_setting( 'checkout_url', '' );
	$secret    = (string) nrx_setting( 'quiz_webhook_secret', '' );
	$portal    = (string) nrx_setting( 'patient_portal_url', '' );

	$status = array(
		'clinical_webhook' => array(
			'label'  => __( 'Handoff clínico (webhook)', 'nuestrarx-core' ),
			'status' => $webhook && $secret ? 'ok' : ( $webhook ? 'warn' : 'missing' ),
			'note'   => $webhook
				? ( $secret ? __( 'Configurado con firma HMAC.', 'nuestrarx-core' ) : __( 'Falta la clave secreta; los envíos fallarán.', 'nuestrarx-core' ) )
				: __( 'Sin webhook. Las respuestas clínicas se almacenan cifradas y requieren proceso manual.', 'nuestrarx-core' ),
		),
		'checkout'         => array(
			'label'  => __( 'Checkout / pago', 'nuestrarx-core' ),
			'status' => $checkout ? 'ok' : 'warn',
			'note'   => $checkout ? __( 'URL de checkout configurada.', 'nuestrarx-core' ) : __( 'No hay URL de checkout. El CTA lleva a la sección de tratamientos o a WhatsApp. El cobro con NMI debe integrarse en el entorno autorizado.', 'nuestrarx-core' ),
		),
		'patient_portal'   => array(
			'label'  => __( 'Portal del paciente', 'nuestrarx-core' ),
			'status' => $portal ? 'ok' : 'missing',
			'note'   => $portal ? esc_url( $portal ) : __( 'Falta la URL del portal.', 'nuestrarx-core' ),
		),
		'analytics'        => array(
			'label'  => __( 'Analítica con consentimiento', 'nuestrarx-core' ),
			'status' => nrx_setting( 'analytics_enabled' ) ? 'ok' : 'warn',
			'note'   => nrx_setting( 'analytics_enabled' ) ? __( 'Activa; las etiquetas solo se cargan tras el consentimiento.', 'nuestrarx-core' ) : __( 'Desactivada.', 'nuestrarx-core' ),
		),
		'compliance'       => array(
			'label'  => __( 'Reclamaciones aprobadas', 'nuestrarx-core' ),
			'status' => nrx_compliance_is_approved() ? 'ok' : 'warn',
			'note'   => nrx_compliance_is_approved() ? __( 'Aprobadas por el responsable de cumplimiento.', 'nuestrarx-core' ) : __( 'Las secciones de garantía, testimonios y transformaciones están ocultas hasta aprobarlas.', 'nuestrarx-core' ),
		),
	);

	return $status;
}

/**
 * Print the analytics configuration only. Tags are injected by consent.js
 * after the visitor opts in.
 */
function nrx_print_analytics_config() {
	if ( ! nrx_setting( 'analytics_enabled' ) ) {
		return;
	}
	$config = array(
		'ga'     => sanitize_text_field( (string) nrx_setting( 'ga_measurement_id', '' ) ),
		'meta'   => sanitize_text_field( (string) nrx_setting( 'meta_pixel_id', '' ) ),
		'tiktok' => sanitize_text_field( (string) nrx_setting( 'tiktok_pixel_id', '' ) ),
	);
	if ( ! array_filter( $config ) ) {
		return;
	}
	echo '<script type="application/json" id="nrx-analytics-config">' . wp_json_encode( $config ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'nrx_print_analytics_config', 5 );

/**
 * Add privacy links to the consent banner through a filter-friendly array.
 *
 * @return array
 */
function nrx_privacy_links() {
	return array(
		'privacy'   => nrx_url( nrx_setting( 'privacy_page_url', '/politica-de-privacidad/' ) ),
		'hipaa'     => nrx_url( nrx_setting( 'hipaa_page_url', '/aviso-hipaa/' ) ),
		'do_not_sell' => nrx_url( nrx_setting( 'do_not_sell_url', '/politica-de-privacidad/#no-vender' ) ),
	);
}

/**
 * Never index private or transient pages.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function nrx_private_robots( $robots ) {
	if ( is_page_template( 'page-templates/intake.php' ) || is_page( nrx_setting( 'intake_slug', 'evaluacion' ) ) || is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['nofollow'] = true;
	}
	$page_id = get_queried_object_id();
	if ( $page_id && get_post_meta( $page_id, '_nrx_seo_noindex', true ) ) {
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'nrx_private_robots' );

/**
 * Disable WordPress's default canonical tag; the plugin prints a complete set.
 */
remove_action( 'wp_head', 'rel_canonical' );

/**
 * Add the sitemap to robots.txt and disallow private endpoints.
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function nrx_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}
	$output .= "\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
	$output .= 'Sitemap: ' . esc_url_raw( home_url( '/wp-sitemap.xml' ) ) . "\n";
	return $output;
}
add_filter( 'robots_txt', 'nrx_robots_txt', 10, 2 );

/**
 * Exclude noindex pages from the core sitemap.
 *
 * @param array  $args  Query args.
 * @param string $type  Post type.
 * @return array
 */
function nrx_sitemap_exclude_noindex( $args, $type ) {
	if ( 'page' !== $type ) {
		return $args;
	}
	$exclude = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_nrx_seo_noindex',
					'value' => '1',
				),
			),
		)
	);
	$intake = get_page_by_path( nrx_setting( 'intake_slug', 'evaluacion' ) );
	if ( $intake ) {
		$exclude[] = $intake->ID;
	}
	if ( $exclude ) {
		$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), $exclude );
	}
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'nrx_sitemap_exclude_noindex', 10, 2 );
