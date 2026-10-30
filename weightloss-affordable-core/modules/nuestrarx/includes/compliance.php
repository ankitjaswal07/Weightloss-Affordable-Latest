<?php
/**
 * Compliance register.
 *
 * Marketing, clinical and certification claims are tracked individually.
 * Sections that depend on a claim (guarantee, testimonials, before/after)
 * stay hidden from the public until the client explicitly approves them.
 * Nothing is verified automatically: approval records the WordPress user,
 * date and evidence URL.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Claim register used by the installer and the compliance screen.
 *
 * Status "factual" means the statement is a regulatory/factual disclosure
 * (e.g. compounded drugs are not FDA approved) and is required copy.
 *
 * @return array
 */
function nrx_default_claims() {
	return array(
		'guarantee_12m' => array(
			'label'    => 'Garantía de resultados de 12 meses o reembolso total',
			'type'     => 'marketing',
			'gates'    => 'guarantee',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — sección Garantía y Política de reembolso',
			'evidence' => '',
			'notes'    => 'Requiere términos publicados, aprobación legal y operativa de reembolsos. No activar antes de confirmar elegibilidad del programa y letra pequeña.',
		),
		'testimonials' => array(
			'label'    => 'Testimonios de pacientes reales',
			'type'     => 'patient_experience',
			'gates'    => 'testimonials',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Testimonios',
			'evidence' => '',
			'notes'    => 'Se publican con el aviso de compensación por tiempo de entrevista. Confirmar autorizaciones firmadas y vigencia.',
		),
		'transformations' => array(
			'label'    => 'Resultados antes y después',
			'type'     => 'patient_experience',
			'gates'    => 'transformations',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Transformaciones',
			'evidence' => '',
			'notes'    => 'Requiere consentimiento escrito para uso de imagen y datos de resultado. Verificar que las fotos sean de pacientes reales.',
		),
		'legitscript' => array(
			'label'    => 'Certificación LegitScript Healthcare Merchant',
			'type'     => 'certification',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Cumplimiento',
			'evidence' => 'https://www.legitscript.com/websites/?checker_keywords=nuestrarx.com',
			'notes'    => 'El sello solo debe mostrarse si la certificación está activa al momento de la publicación.',
		),
		'hipaa' => array(
			'label'    => 'Operación como entidad cubierta / asociado de negocios HIPAA',
			'type'     => 'privacy',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Cumplimiento y Aviso HIPAA',
			'evidence' => '',
			'notes'    => 'Confirmar con asesoría legal y los acuerdos de asociado de negocios con Beluga Health y farmacias.',
		),
		'beluga' => array(
			'label'    => 'Servicios clínicos provistos por Beluga Health',
			'type'     => 'clinical',
			'gates'    => '',
			'status'   => 'verified',
			'source'   => 'Sitio de referencia — Nosotros y Cumplimiento',
			'evidence' => 'https://www.belugahealth.com/',
			'notes'    => 'Relación publicada en el sitio de referencia. Confirmar contrato vigente antes del lanzamiento.',
		),
		'pharmacies_503a' => array(
			'label'    => 'Preparación por farmacias 503A/503B con licencia',
			'type'     => 'clinical',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Tratamientos y Cumplimiento',
			'evidence' => '',
			'notes'    => 'Publicar nombres, estados y números de licencia verificables. No publicar certificados de análisis sin documento del lote.',
		),
		'fda_compounded' => array(
			'label'    => 'Los compuestos GLP-1 no están aprobados por la FDA y no son bioequivalentes',
			'type'     => 'regulatory',
			'gates'    => '',
			'status'   => 'verified',
			'source'   => 'Sitio de referencia — Información de seguridad',
			'evidence' => 'https://www.fda.gov/drugs/human-drug-compounding',
			'notes'    => 'Divulgación obligatoria; debe mantenerse en todas las páginas de tratamiento y consentimiento.',
		),
		'pci_nmi' => array(
			'label'    => 'Pagos procesados por NMI con PCI DSS Nivel 1',
			'type'     => 'security',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Cumplimiento',
			'evidence' => '',
			'notes'    => 'Confirmar la relación vigente y el alcance PCI antes de mostrar el sello.',
		),
		'average_weight_loss' => array(
			'label'    => 'Pérdida promedio reportada de 15–20% en 12 meses',
			'type'     => 'clinical',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Evaluación y objetivos',
			'evidence' => '',
			'notes'    => 'Debe atribuirse a los medicamentos de marca estudiados, no a los compuestos. Añadir referencia bibliográfica o retirar la cifra.',
		),
		'review_24h' => array(
			'label'    => 'Revisión médica en menos de 24 horas',
			'type'     => 'service',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Hero y proceso',
			'evidence' => '',
			'notes'    => 'Solo publicar si Beluga Health garantiza operativamente ese tiempo de revisión. El tema no usa esta promesa por defecto.',
		),
		'states_50' => array(
			'label'    => 'Disponibilidad en los 50 estados',
			'type'     => 'service',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Hero y FAQ',
			'evidence' => '',
			'notes'    => 'La disponibilidad depende de licencias. Si se configura un estado como no disponible, actualizar también el copy.',
		),
		'first_latino_clinic' => array(
			'label'    => '"La primera clínica de telemedicina para latinos"',
			'type'     => 'superlative',
			'gates'    => '',
			'status'   => 'unverified',
			'source'   => 'Sitio de referencia — Hero',
			'evidence' => '',
			'notes'    => 'Superlativo difícil de sustentar. Se recomienda sustituirlo por una descripción factual ("plataforma de telemedicina en español").',
		),
	);
}

/**
 * Current claims merged with defaults.
 *
 * @return array
 */
function nrx_claims() {
	$saved = get_option( 'nrx_claims', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = array();
	foreach ( nrx_default_claims() as $id => $claim ) {
		$out[ $id ] = array_merge( $claim, isset( $saved[ $id ] ) && is_array( $saved[ $id ] ) ? $saved[ $id ] : array() );
	}
	return $out;
}

/**
 * Whether gated content may be shown publicly.
 *
 * Requires the explicit approval flag and that every gating claim is verified.
 *
 * @return bool
 */
function nrx_compliance_is_approved() {
	if ( ! (int) nrx_setting( 'compliance_approved', 0 ) ) {
		return false;
	}
	foreach ( nrx_claims() as $claim ) {
		if ( ! empty( $claim['gates'] ) && 'verified' !== $claim['status'] ) {
			return false;
		}
	}
	return true;
}

/**
 * Approve every claim that was imported from the reference site.
 *
 * This is a deliberate client action; it records the user and timestamp.
 *
 * @param string $note Reviewer note.
 * @return true|WP_Error
 */
function nrx_approve_imported_claims( $note = '' ) {
	if ( ! current_user_can( 'nrx_manage_compliance' ) && ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'nrx_forbidden', __( 'No tienes permiso para aprobar reclamaciones.', 'nuestrarx-core' ) );
	}
	$claims = nrx_claims();
	$user   = wp_get_current_user();
	foreach ( $claims as $id => &$claim ) {
		if ( 'unverified' === $claim['status'] ) {
			$claim['status']      = 'verified';
			$claim['reviewed_by'] = $user->user_login;
			$claim['reviewed_at'] = current_time( 'mysql' );
			$claim['review_note'] = sanitize_text_field( $note );
		}
	}
	unset( $claim );
	update_option( 'nrx_claims', $claims, false );
	nrx_update_settings(
		array(
			'compliance_approved' => 1,
			'claims_reviewed_at'  => current_time( 'mysql' ),
		)
	);
	nrx_log( 'Reclamaciones aprobadas', array( 'user' => $user->user_login ) );
	return true;
}

/**
 * Save the claims register.
 *
 * @param array $input Raw input.
 * @return array
 */
function nrx_save_claims( $input ) {
	$claims = nrx_claims();
	$user   = wp_get_current_user();
	foreach ( $claims as $id => &$claim ) {
		$row = isset( $input[ $id ] ) && is_array( $input[ $id ] ) ? $input[ $id ] : array();
		$status = isset( $row['status'] ) ? sanitize_key( $row['status'] ) : $claim['status'];
		if ( ! in_array( $status, array( 'unverified', 'verified', 'retired' ), true ) ) {
			$status = $claim['status'];
		}
		if ( $status !== $claim['status'] ) {
			$claim['reviewed_by'] = $user->user_login;
			$claim['reviewed_at'] = current_time( 'mysql' );
		}
		$claim['status']   = $status;
		$claim['evidence'] = isset( $row['evidence'] ) ? esc_url_raw( $row['evidence'] ) : $claim['evidence'];
		$claim['notes']    = isset( $row['notes'] ) ? sanitize_textarea_field( $row['notes'] ) : $claim['notes'];
	}
	unset( $claim );
	update_option( 'nrx_claims', $claims, false );
	return $claims;
}

/**
 * Render the compliance screen.
 */
function nrx_compliance_screen() {
	if ( ! current_user_can( 'nrx_manage_compliance' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para ver esta página.', 'nuestrarx-core' ) );
	}
	$approved = nrx_compliance_is_approved();
	$notice   = get_transient( 'nrx_compliance_notice_' . get_current_user_id() );
	if ( $notice ) {
		delete_transient( 'nrx_compliance_notice_' . get_current_user_id() );
	}
	$claims = nrx_claims();
	?>
	<div class="wrap nrx-admin-wrap">
		<h1><?php esc_html_e( 'Cumplimiento y reclamaciones', 'nuestrarx-core' ); ?></h1>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<div class="notice <?php echo $approved ? 'notice-success' : 'notice-warning'; ?> inline">
			<p>
				<strong><?php echo $approved ? esc_html__( 'Contenido aprobado.', 'nuestrarx-core' ) : esc_html__( 'Contenido pendiente de aprobación.', 'nuestrarx-core' ); ?></strong>
				<?php
				echo $approved
					? esc_html__( 'Las secciones de garantía, testimonios y transformaciones pueden mostrarse al público.', 'nuestrarx-core' )
					: esc_html__( 'Las secciones de garantía, testimonios y transformaciones están ocultas al público. Revisa cada reclamación y su evidencia antes de aprobar.', 'nuestrarx-core' );
				?>
			</p>
			<?php if ( nrx_setting( 'claims_reviewed_at' ) ) : ?>
				<p><?php echo esc_html( sprintf( __( 'Última revisión: %s', 'nuestrarx-core' ), nrx_setting( 'claims_reviewed_at' ) ) ); ?></p>
			<?php endif; ?>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nrx_save_claims">
			<?php wp_nonce_field( 'nrx_save_claims', '_nrx_nonce' ); ?>
			<table class="widefat striped nrx-claims-table">
				<thead><tr>
					<th><?php esc_html_e( 'Reclamación', 'nuestrarx-core' ); ?></th>
					<th><?php esc_html_e( 'Fuente', 'nuestrarx-core' ); ?></th>
					<th><?php esc_html_e( 'Estado', 'nuestrarx-core' ); ?></th>
					<th><?php esc_html_e( 'Evidencia (URL)', 'nuestrarx-core' ); ?></th>
					<th><?php esc_html_e( 'Notas', 'nuestrarx-core' ); ?></th>
					<th><?php esc_html_e( 'Revisión', 'nuestrarx-core' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $claims as $id => $claim ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $claim['label'] ); ?></strong><br><code><?php echo esc_html( $id ); ?></code>
							<?php if ( $claim['gates'] ) : ?><br><span class="nrx-pill"><?php echo esc_html( sprintf( __( 'Bloquea: %s', 'nuestrarx-core' ), $claim['gates'] ) ); ?></span><?php endif; ?>
						</td>
						<td><?php echo esc_html( $claim['source'] ); ?></td>
						<td>
							<select name="claims[<?php echo esc_attr( $id ); ?>][status]">
								<?php foreach ( array( 'unverified' => __( 'Sin verificar', 'nuestrarx-core' ), 'verified' => __( 'Verificada', 'nuestrarx-core' ), 'retired' => __( 'Retirada', 'nuestrarx-core' ) ) as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $claim['status'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="url" name="claims[<?php echo esc_attr( $id ); ?>][evidence]" value="<?php echo esc_attr( $claim['evidence'] ); ?>" placeholder="https://" style="width:100%"></td>
						<td><textarea name="claims[<?php echo esc_attr( $id ); ?>][notes]" rows="3" style="width:100%"><?php echo esc_textarea( $claim['notes'] ); ?></textarea></td>
						<td><?php echo esc_html( $claim['reviewed_by'] ?? '—' ); ?><br><small><?php echo esc_html( $claim['reviewed_at'] ?? '' ); ?></small></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Guardar reclamaciones', 'nuestrarx-core' ), 'primary', 'submit', false ); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Aprobar contenido importado', 'nuestrarx-core' ); ?></h2>
		<p><?php esc_html_e( 'Usa esta acción solo después de revisar cada reclamación con el cliente y contar con la evidencia correspondiente. Se registra tu usuario y la fecha.', 'nuestrarx-core' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('¿Confirmas que el cliente revisó y aprobó estas reclamaciones?');">
			<input type="hidden" name="action" value="nrx_approve_claims">
			<?php wp_nonce_field( 'nrx_approve_claims', '_nrx_nonce' ); ?>
			<p><label><input type="checkbox" required> <?php esc_html_e( 'Confirmo que revisé las reclamaciones y la evidencia con el cliente.', 'nuestrarx-core' ); ?></label></p>
			<p><input type="text" name="note" class="regular-text" placeholder="<?php esc_attr_e( 'Nota de aprobación (quién aprobó, cómo)', 'nuestrarx-core' ); ?>"></p>
			<?php submit_button( __( 'Aprobar todas las reclamaciones pendientes', 'nuestrarx-core' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle claim save.
 */
function nrx_handle_save_claims() {
	nrx_verify_admin_request( 'nrx_save_claims', 'nrx_manage_compliance' );
	$input = isset( $_POST['claims'] ) && is_array( $_POST['claims'] ) ? wp_unslash( $_POST['claims'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	nrx_save_claims( $input );
	set_transient( 'nrx_compliance_notice_' . get_current_user_id(), __( 'Reclamaciones guardadas.', 'nuestrarx-core' ), 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=nrx-compliance' ) );
	exit;
}
add_action( 'admin_post_nrx_save_claims', 'nrx_handle_save_claims' );

/**
 * Handle approval.
 */
function nrx_handle_approve_claims() {
	nrx_verify_admin_request( 'nrx_approve_claims', 'nrx_manage_compliance' );
	$note   = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';
	$result = nrx_approve_imported_claims( $note );
	set_transient(
		'nrx_compliance_notice_' . get_current_user_id(),
		is_wp_error( $result ) ? $result->get_error_message() : __( 'Reclamaciones aprobadas. Las secciones habilitadas ya son visibles.', 'nuestrarx-core' ),
		60
	);
	wp_safe_redirect( admin_url( 'admin.php?page=nrx-compliance' ) );
	exit;
}
add_action( 'admin_post_nrx_approve_claims', 'nrx_handle_approve_claims' );
