<?php
/**
 * Admin menus, dashboard, settings screen and asset loading.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menus.
 */
function nrx_admin_menu() {
	add_menu_page(
		__( 'NuestraRx', 'nuestrarx-core' ),
		__( 'NuestraRx', 'nuestrarx-core' ),
		'edit_posts',
		'nrx-dashboard',
		'nrx_dashboard_screen',
		'dashicons-heart',
		3
	);

	add_submenu_page( 'nrx-dashboard', __( 'Panel', 'nuestrarx-core' ), __( 'Panel', 'nuestrarx-core' ), 'edit_posts', 'nrx-dashboard', 'nrx_dashboard_screen' );
	add_submenu_page( 'nrx-dashboard', __( 'Secciones', 'nuestrarx-core' ), __( 'Secciones', 'nuestrarx-core' ), 'edit_posts', 'edit.php?post_type=nrx_section' );
	add_submenu_page( 'nrx-dashboard', __( 'Tratamientos', 'nuestrarx-core' ), __( 'Tratamientos', 'nuestrarx-core' ), 'edit_posts', 'edit.php?post_type=nrx_treatment' );
	add_submenu_page( 'nrx-dashboard', __( 'Testimonios', 'nuestrarx-core' ), __( 'Testimonios', 'nuestrarx-core' ), 'edit_posts', 'edit.php?post_type=nrx_testimonial' );
	add_submenu_page( 'nrx-dashboard', __( 'Equipo clínico', 'nuestrarx-core' ), __( 'Equipo clínico', 'nuestrarx-core' ), 'edit_posts', 'edit.php?post_type=nrx_clinician' );
	add_submenu_page( 'nrx-dashboard', __( 'Farmacias', 'nuestrarx-core' ), __( 'Farmacias', 'nuestrarx-core' ), 'edit_posts', 'edit.php?post_type=nrx_pharmacy' );
	add_submenu_page( 'nrx-dashboard', __( 'Evaluaciones', 'nuestrarx-core' ), __( 'Evaluaciones', 'nuestrarx-core' ), 'nrx_manage_intakes', 'edit.php?post_type=nrx_intake' );
	add_submenu_page( 'nrx-dashboard', __( 'Cumplimiento', 'nuestrarx-core' ), __( 'Cumplimiento', 'nuestrarx-core' ), 'nrx_manage_compliance', 'nrx-compliance', 'nrx_compliance_screen' );
	add_submenu_page( 'nrx-dashboard', __( 'Instalación', 'nuestrarx-core' ), __( 'Instalación', 'nuestrarx-core' ), 'manage_options', 'nrx-setup', 'nrx_setup_screen' );
	add_submenu_page( 'nrx-dashboard', __( 'Ajustes', 'nuestrarx-core' ), __( 'Ajustes', 'nuestrarx-core' ), 'nrx_manage_settings', 'nrx-settings', 'nrx_settings_screen' );
}
add_action( 'admin_menu', 'nrx_admin_menu', 9 );

/**
 * Register the settings option.
 */
function nrx_register_settings() {
	register_setting(
		'nrx_settings_group',
		'nrx_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'nrx_sanitize_settings',
			'default'           => nrx_default_settings(),
		)
	);
}
add_action( 'admin_init', 'nrx_register_settings' );

/**
 * Hide the unrelated WeightLossAffordable demo importer when configured.
 */
function nrx_hide_unrelated_admin() {
	if ( (int) nrx_setting( 'hide_wla_admin', 1 ) ) {
		remove_menu_page( 'wla-demo-import' );
	}
}
add_action( 'admin_menu', 'nrx_hide_unrelated_admin', 99 );

/**
 * Admin assets.
 *
 * @param string $hook Current screen hook.
 */
function nrx_admin_assets( $hook ) {
	$screen = get_current_screen();
	$is_nrx_screen = $screen && ( false !== strpos( (string) $screen->id, 'nrx' ) || false !== strpos( (string) ( $screen->post_type ?? '' ), 'nrx_' ) );
	$load          = (bool) $is_nrx_screen;
	if ( ! $load ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'nrx-admin', NRX_CORE_URL . 'assets/css/admin.css', array(), NRX_CORE_VERSION );
	wp_enqueue_script( 'nrx-admin', NRX_CORE_URL . 'assets/js/admin.js', array( 'jquery' ), NRX_CORE_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'nrx_admin_assets' );

/**
 * Dashboard screen.
 */
function nrx_dashboard_screen() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$counts = array(
		'sections'     => (int) wp_count_posts( 'nrx_section' )->publish,
		'treatments'   => (int) wp_count_posts( 'nrx_treatment' )->publish,
		'testimonials' => (int) wp_count_posts( 'nrx_testimonial' )->publish,
		'clinicians'   => (int) wp_count_posts( 'nrx_clinician' )->publish,
		'pharmacies'   => (int) wp_count_posts( 'nrx_pharmacy' )->publish,
	);
	$quiz       = nrx_quiz_get( 'published' );
	$intakes    = current_user_can( 'nrx_manage_intakes' ) ? get_posts( array( 'post_type' => 'nrx_intake', 'post_status' => 'private', 'posts_per_page' => 5, 'orderby' => 'date' ) ) : array();
	$integrations = nrx_integration_status();
	$theme      = wp_get_theme();
	$theme_ok   = 'nuestrarx' === get_stylesheet() || 'weightloss-affordable' === $theme->get_template();
	?>
	<div class="wrap nrx-admin-wrap">
		<h1><?php esc_html_e( 'NuestraRx · Panel de control', 'nuestrarx-core' ); ?></h1>
		<?php if ( ! nrx_compliance_is_approved() ) : ?>
			<div class="notice notice-warning"><p><strong><?php esc_html_e( 'Contenido pendiente de aprobación.', 'nuestrarx-core' ); ?></strong> <?php echo esc_html__( 'Garantía, testimonios y transformaciones no se muestran al público.', 'nuestrarx-core' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=nrx-compliance' ) ); ?>"><?php esc_html_e( 'Revisar cumplimiento', 'nuestrarx-core' ); ?></a></p></div>
		<?php endif; ?>

		<div class="nrx-dashboard-grid">
			<div class="nrx-card">
				<h2><?php esc_html_e( 'Estado del sitio', 'nuestrarx-core' ); ?></h2>
				<ul class="nrx-status-list">
					<li><span><?php esc_html_e( 'Tema activo', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( $theme_ok ? $theme->get( 'Name' ) : __( 'Revisar tema', 'nuestrarx-core' ) ); ?></strong></li>
					<li><span><?php esc_html_e( 'Secciones publicadas', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( (string) $counts['sections'] ); ?></strong></li>
					<li><span><?php esc_html_e( 'Tratamientos', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( (string) $counts['treatments'] ); ?></strong></li>
					<li><span><?php esc_html_e( 'Testimonios', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( (string) $counts['testimonials'] ); ?></strong></li>
					<li><span><?php esc_html_e( 'Versión de la evaluación', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( 'v' . ( $quiz['version'] ?? 1 ) ); ?><?php echo nrx_quiz_has_draft_changes() ? ' · ' . esc_html__( 'borrador pendiente', 'nuestrarx-core' ) : ''; ?></strong></li>
					<li><span><?php esc_html_e( 'Última publicación de la evaluación', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( $quiz['updated'] ?? '—' ); ?></strong></li>
				</ul>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nrx-quiz' ) ); ?>"><?php esc_html_e( 'Editar evaluación', 'nuestrarx-core' ); ?></a> <a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ver sitio', 'nuestrarx-core' ); ?></a></p>
			</div>

			<div class="nrx-card">
				<h2><?php esc_html_e( 'Integraciones', 'nuestrarx-core' ); ?></h2>
				<table class="widefat striped">
					<tbody>
					<?php foreach ( $integrations as $id => $item ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $item['label'] ); ?></strong><br><small><?php echo esc_html( $item['note'] ); ?></small></td>
							<td style="width:90px"><span class="nrx-pill nrx-pill-<?php echo esc_attr( $item['status'] ); ?>"><?php echo esc_html( 'ok' === $item['status'] ? __( 'Listo', 'nuestrarx-core' ) : ( 'warn' === $item['status'] ? __( 'Revisar', 'nuestrarx-core' ) : __( 'Falta', 'nuestrarx-core' ) ) ); ?></span></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nrx-settings&tab=integrations' ) ); ?>"><?php esc_html_e( 'Configurar integraciones', 'nuestrarx-core' ); ?></a></p>
			</div>

			<div class="nrx-card">
				<h2><?php esc_html_e( 'Contenido', 'nuestrarx-core' ); ?></h2>
				<p><?php esc_html_e( 'La portada, los tratamientos, la evaluación y las páginas legales se gestionan desde WordPress. No hay contenido duplicado en las plantillas.', 'nuestrarx-core' ); ?></p>
				<ul class="nrx-quick-links">
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_section' ) ); ?>"><?php esc_html_e( 'Secciones de página', 'nuestrarx-core' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_treatment' ) ); ?>"><?php esc_html_e( 'Tratamientos y precios', 'nuestrarx-core' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_testimonial' ) ); ?>"><?php esc_html_e( 'Testimonios', 'nuestrarx-core' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_clinician' ) ); ?>"><?php esc_html_e( 'Equipo clínico', 'nuestrarx-core' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_pharmacy' ) ); ?>"><?php esc_html_e( 'Farmacias', 'nuestrarx-core' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=nrx-setup' ) ); ?>"><?php esc_html_e( 'Instalación / contenido inicial', 'nuestrarx-core' ); ?></a></li>
				</ul>
			</div>

			<?php if ( current_user_can( 'nrx_manage_intakes' ) ) : ?>
			<div class="nrx-card">
				<h2><?php esc_html_e( 'Últimas evaluaciones', 'nuestrarx-core' ); ?></h2>
				<?php if ( $intakes ) : ?>
					<table class="widefat striped">
						<tbody>
						<?php foreach ( $intakes as $intake ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $intake->ID ) ); ?>"><?php echo esc_html( 'NRX-' . $intake->ID ); ?></a></td>
								<td><?php echo esc_html( get_the_date( 'Y-m-d H:i', $intake ) ); ?></td>
								<td><?php echo esc_html( nrx_quiz_outcome_labels()[ get_post_meta( $intake->ID, '_nrx_outcome', true ) ] ?? '' ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_intake' ) ); ?>"><?php esc_html_e( 'Ver todas', 'nuestrarx-core' ); ?></a></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Aún no hay evaluaciones.', 'nuestrarx-core' ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<?php $errors = get_option( 'nrx_recent_errors', array() ); ?>
		<?php if ( $errors && current_user_can( 'manage_options' ) ) : ?>
			<h2><?php esc_html_e( 'Registro reciente (sin datos de pacientes)', 'nuestrarx-core' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Fecha', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Evento', 'nuestrarx-core' ); ?></th><th><?php esc_html_e( 'Contexto', 'nuestrarx-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( array_slice( $errors, 0, 8 ) as $error ) : ?>
					<tr>
						<td><?php echo esc_html( $error['time'] ?? '' ); ?></td>
						<td><?php echo esc_html( $error['message'] ?? '' ); ?></td>
						<td><?php echo esc_html( wp_json_encode( $error['context'] ?? array() ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Settings screen with tab navigation.
 */
function nrx_settings_screen() {
	if ( ! current_user_can( 'nrx_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para ver esta página.', 'nuestrarx-core' ) );
	}
	$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'brand';
	$tabs     = array(
		'brand'        => __( 'Marca y contacto', 'nuestrarx-core' ),
		'clinical'     => __( 'Modelo clínico', 'nuestrarx-core' ),
		'links'        => __( 'Enlaces y rutas', 'nuestrarx-core' ),
		'disclosures'  => __( 'Avisos legales', 'nuestrarx-core' ),
		'integrations' => __( 'Evaluación e integraciones', 'nuestrarx-core' ),
		'privacy'      => __( 'Privacidad y analítica', 'nuestrarx-core' ),
		'availability' => __( 'Disponibilidad', 'nuestrarx-core' ),
		'seo'          => __( 'SEO', 'nuestrarx-core' ),
		'advanced'     => __( 'Avanzado', 'nuestrarx-core' ),
	);
	?>
	<div class="wrap nrx-admin-wrap">
		<h1><?php esc_html_e( 'Ajustes de NuestraRx', 'nuestrarx-core' ); ?></h1>
		<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Secciones de ajustes', 'nuestrarx-core' ); ?>">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'nrx-settings', 'tab' => $key ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php" class="nrx-settings-form">
			<?php settings_fields( 'nrx_settings_group' ); ?>
			<input type="hidden" name="nrx_settings[__tab]" value="<?php echo esc_attr( $tab ); ?>">
			<?php
			switch ( $tab ) {
				case 'clinical':
					nrx_settings_fields(
						array(
							nrx_f( 'legal_name', 'Razón social' ),
							nrx_f( 'clinical_partner', 'Socio clínico' ),
							nrx_f( 'clinical_partner_url', 'URL del socio clínico', 'url' ),
							nrx_f( 'clinical_partner_about', 'Página de credenciales del socio', 'url' ),
							nrx_f( 'processing_partner', 'Procesador de pagos' ),
							nrx_f( 'processing_partner_url', 'URL del procesador', 'url' ),
							nrx_f( 'pharmacy_model', 'Modelo de farmacias', 'textarea' ),
							nrx_f( 'service_states_note', 'Nota de cobertura', 'textarea' ),
						)
					);
					break;

				case 'links':
					nrx_settings_fields(
						array(
							nrx_f( 'intake_slug', 'Slug de la evaluación' ),
							nrx_f( 'checkout_url', 'URL de checkout externo (NMI / plataforma existente)', 'url', array( 'help' => 'Si se deja vacío, los CTA llevan a tratamientos o WhatsApp. No se procesan pagos en este sitio sin una integración autorizada.' ) ),
							nrx_f( 'payment_page_slug', 'Slug de pago semanal' ),
							nrx_f( 'patient_portal_url', 'URL del portal del paciente', 'url' ),
							nrx_f( 'blog_url', 'URL del blog', 'url' ),
							nrx_f( 'header_cta_label_es', 'CTA del header (ES)' ),
							nrx_f( 'header_cta_label_en', 'CTA del header (EN)' ),
							nrx_f( 'header_cta_url', 'URL del CTA del header' ),
						)
					);
					break;

				case 'disclosures':
					nrx_settings_fields(
						array(
							nrx_f( 'footer_disclaimer', 'Aviso médico del footer', 'textarea', array( 'rows' => 7 ) ),
							nrx_f( 'compounding_disclaimer', 'Aviso de medicamentos compuestos', 'textarea', array( 'rows' => 4 ) ),
							nrx_f( 'results_disclaimer', 'Aviso de resultados', 'textarea', array( 'rows' => 3 ) ),
							nrx_f( 'testimonials_disclaimer', 'Aviso de testimonios', 'textarea', array( 'rows' => 3 ) ),
						)
					);
					break;

				case 'integrations':
					nrx_settings_fields(
						array(
							nrx_f( 'quiz_submissions_enabled', 'Habilitar envíos públicos de evaluación', 'checkbox', array( 'help' => 'Desactivado por defecto. Actívalo solo después de verificar HTTPS, consentimiento, aviso de privacidad, seguridad del almacenamiento o webhook clínico y flujo de atención. El cuestionario puede previsualizarse mientras los envíos están desactivados.' ) ),
							nrx_f( 'quiz_notify_email', 'Email de notificación de evaluaciones', 'email' ),
							nrx_f( 'inquiry_notify_email', 'Email de notificación de consultas', 'email' ),
							nrx_f( 'quiz_webhook_url', 'Webhook clínico (HTTPS)', 'url', array( 'help' => 'Recibe las respuestas con firma HMAC. Obligatorio para el handoff clínico automatizado.' ) ),
							nrx_f( 'inquiry_webhook_url', 'Webhook de consultas (HTTPS)', 'url' ),
							nrx_f( 'store_clinical_answers', 'Almacenar respuestas clínicas en WordPress (cifradas)', 'checkbox', array( 'help' => 'Por defecto NO se almacenan. Si no hay webhook configurado, se almacenan cifradas para no perder el caso.' ) ),
							nrx_f( 'intake_retention_days', 'Retención de evaluaciones (días)', 'number', array( 'min' => 1, 'max' => 3650 ) ),
							nrx_f( 'quiz_analytics_events', 'Enviar eventos de progreso anónimos a analítica', 'checkbox', array( 'help' => 'Solo números de paso y resultado. Nunca respuestas ni datos de contacto.' ) ),
							nrx_f( 'product_source', 'Origen de precios', 'select', array( 'options' => array( 'internal' => 'Tratamientos NuestraRx (recomendado)', 'woocommerce' => 'WooCommerce (usar ID de producto)' ) ) ),
						)
					);
					?>
					<h3><?php esc_html_e( 'Secreto del webhook', 'nuestrarx-core' ); ?></h3>
					<p class="nrx-field-help"><?php esc_html_e( 'Se usa para firmar el payload (X-NuestraRx-Signature). No se muestra por seguridad.', 'nuestrarx-core' ); ?></p>
					<p><input type="password" name="nrx_settings[quiz_webhook_secret]" value="" autocomplete="new-password" class="regular-text" placeholder="<?php echo nrx_setting( 'quiz_webhook_secret' ) ? esc_attr__( '•••••••• (configurado)', 'nuestrarx-core' ) : esc_attr__( 'Sin configurar', 'nuestrarx-core' ); ?>"></p>
					<p><label><input type="checkbox" name="nrx_settings[clear_webhook_secret]" value="1"> <?php esc_html_e( 'Borrar el secreto guardado', 'nuestrarx-core' ); ?></label></p>
					<?php
					break;

				case 'privacy':
					nrx_settings_fields(
						array(
							nrx_f( 'analytics_enabled', 'Habilitar analítica (solo tras consentimiento)', 'checkbox' ),
							nrx_f( 'ga_measurement_id', 'GA4 Measurement ID' ),
							nrx_f( 'meta_pixel_id', 'Meta Pixel ID' ),
							nrx_f( 'tiktok_pixel_id', 'TikTok Pixel ID' ),
							nrx_f( 'privacy_page_url', 'URL de privacidad' ),
							nrx_f( 'hipaa_page_url', 'URL del aviso HIPAA' ),
							nrx_f( 'terms_page_url', 'URL de términos' ),
							nrx_f( 'refund_page_url', 'URL de reembolsos' ),
							nrx_f( 'safety_page_url', 'URL de información de seguridad' ),
							nrx_f( 'compliance_page_url', 'URL de cumplimiento' ),
							nrx_f( 'do_not_sell_url', 'URL "No vender / compartir"' ),
						)
					);
					break;

				case 'availability':
					?>
					<h3><?php esc_html_e( 'Estados sin disponibilidad', 'nuestrarx-core' ); ?></h3>
					<p class="nrx-field-help"><?php esc_html_e( 'Marca los estados donde el servicio no está disponible. La evaluación y la página de pago semanal los usan.', 'nuestrarx-core' ); ?></p>
					<div class="nrx-state-grid">
						<?php $unavailable = (array) nrx_setting( 'states_unavailable', array() ); ?>
						<?php foreach ( nrx_states() as $code => $names ) : ?>
							<label class="nrx-checkbox-row"><input type="checkbox" name="nrx_settings[states_unavailable][]" value="<?php echo esc_attr( $code ); ?>" <?php checked( in_array( $code, $unavailable, true ) ); ?>> <span><?php echo esc_html( $names[0] ); ?> <code><?php echo esc_html( $code ); ?></code></span></label>
						<?php endforeach; ?>
					</div>
					<?php
					break;

				case 'seo':
					nrx_settings_fields(
						array(
							nrx_f( 'seo_home_title', 'Título SEO de la portada' ),
							nrx_f( 'seo_home_description', 'Descripción SEO de la portada', 'textarea', array( 'rows' => 3 ) ),
							nrx_f( 'default_og_image', 'Imagen OG por defecto (URL)', 'url' ),
						)
					);
					break;

				case 'advanced':
					nrx_settings_fields(
						array(
							nrx_f( 'default_lang', 'Idioma por defecto', 'select', array( 'options' => array( 'es' => 'Español', 'en' => 'English' ) ) ),
							nrx_f( 'google_fonts', 'Cargar Google Fonts (Fraunces / Manrope)', 'checkbox', array( 'help' => 'Desactiva para self-hosting de fuentes o restricciones de privacidad.' ) ),
							nrx_f( 'load_parent_motion', 'Cargar el motor de animaciones del tema base', 'checkbox', array( 'help' => 'Recomendado desactivado: el tema hijo incluye su propio motor ligero.' ) ),
							nrx_f( 'hide_wla_admin', 'Ocultar el importador de WeightLossAffordable en el panel', 'checkbox', array( 'help' => 'Evita importar contenido de otra marca por error. El plugin sigue activo.' ) ),
						)
					);
					?>
					<h3><?php esc_html_e( 'Diagnóstico', 'nuestrarx-core' ); ?></h3>
					<ul class="nrx-status-list">
						<li><span>PHP</span><strong><?php echo esc_html( PHP_VERSION ); ?></strong></li>
						<li><span>WordPress</span><strong><?php echo esc_html( get_bloginfo( 'version' ) ); ?></strong></li>
						<li><span><?php esc_html_e( 'Tema padre', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( wp_get_theme( get_template() )->get( 'Name' ) ); ?></strong></li>
						<li><span><?php esc_html_e( 'Permalinks', 'nuestrarx-core' ); ?></span><strong><?php echo esc_html( get_option( 'permalink_structure' ) ? __( 'Bonitos', 'nuestrarx-core' ) : __( 'Simples (recomendado cambiar)', 'nuestrarx-core' ) ); ?></strong></li>
						<li><span><?php esc_html_e( 'Cifrado sodium', 'nuestrarx-core' ); ?></span><strong><?php echo function_exists( 'sodium_crypto_secretbox' ) ? esc_html__( 'Disponible', 'nuestrarx-core' ) : esc_html__( 'No disponible', 'nuestrarx-core' ); ?></strong></li>
					</ul>
					<?php
					break;

				default:
					nrx_settings_fields(
						array(
							nrx_f( 'brand_name', 'Nombre de marca' ),
							nrx_f( 'brand_tagline_es', 'Tagline (ES)' ),
							nrx_f( 'brand_tagline_en', 'Tagline (EN)' ),
							nrx_f( 'address', 'Dirección legal', 'textarea', array( 'rows' => 2 ) ),
							nrx_f( 'phone_display', 'Teléfono visible' ),
							nrx_f( 'phone_e164', 'Teléfono E.164' ),
							nrx_f( 'whatsapp', 'WhatsApp (solo dígitos)' ),
							nrx_f( 'contact_email', 'Email general', 'email' ),
							nrx_f( 'privacy_email', 'Email de privacidad', 'email' ),
							nrx_f( 'safety_email', 'Email de seguridad / eventos adversos', 'email' ),
							nrx_f( 'support_email', 'Email de soporte', 'email' ),
							nrx_f( 'instagram_url', 'Instagram', 'url' ),
							nrx_f( 'facebook_url', 'Facebook', 'url' ),
							nrx_f( 'legitscript_url', 'Verificación LegitScript', 'url' ),
						)
					);
					break;
			}
			submit_button( __( 'Guardar ajustes', 'nuestrarx-core' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Render settings fields bound to the nrx_settings option.
 *
 * @param array $fields Field schema.
 */
function nrx_settings_fields( $fields ) {
	$values = nrx_settings();
	$normalized = array();
	foreach ( $fields as $field ) {
		$key = $field['key'] ?? '';
		if ( ! $key ) {
			continue;
		}
		$value = $values[ $key ] ?? '';
		$name  = 'nrx_settings[' . $key . ']';
		echo '<div class="nrx-field">';
		echo '<label class="nrx-field-label" for="nrx_settings_' . esc_attr( $key ) . '">' . esc_html( $field['label'] ?? $key ) . '</label>';
		if ( 'checkbox' === ( $field['type'] ?? 'text' ) ) {
			echo '<label class="nrx-checkbox-row"><input type="checkbox" id="nrx_settings_' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( (int) $value, 1, false ) . '> <span>' . esc_html__( 'Activar', 'nuestrarx-core' ) . '</span></label>';
		} elseif ( 'textarea' === ( $field['type'] ?? 'text' ) ) {
			echo '<textarea id="nrx_settings_' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( $field['rows'] ?? 4 ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
		} elseif ( 'select' === ( $field['type'] ?? 'text' ) ) {
			echo '<select id="nrx_settings_' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( (array) ( $field['options'] ?? array() ) as $opt => $label ) {
				echo '<option value="' . esc_attr( $opt ) . '" ' . selected( (string) $value, (string) $opt, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
		} else {
			$type = in_array( $field['type'] ?? '', array( 'email', 'url', 'number' ), true ) ? $field['type'] : 'text';
			echo '<input type="' . esc_attr( $type ) . '" id="nrx_settings_' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"' . ( isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '' ) . '>';
		}
		if ( ! empty( $field['help'] ) ) {
			echo '<p class="nrx-field-help">' . wp_kses_post( $field['help'] ) . '</p>';
		}
		echo '</div>';
	}
}
