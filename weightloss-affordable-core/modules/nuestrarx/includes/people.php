<?php
/**
 * Clinicians, pharmacies and testimonials: editorial fields and verification
 * gates.
 *
 * These three content types publish nothing until a human with the compliance
 * capability confirms that the credentials, licences or written consent exist.
 * The panels are read-only for editors without that capability.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current user can verify records.
 *
 * @return bool
 */
function nrx_can_verify() {
	return current_user_can( 'nrx_manage_compliance' ) || current_user_can( 'manage_options' );
}

/* -------------------------------------------------------------------------
 * Clinicians
 * ---------------------------------------------------------------------- */

/**
 * Meta box: clinician credentials.
 *
 * @param WP_Post $post Post.
 */
function nrx_clinician_box( $post ) {
	wp_nonce_field( 'nrx_save_clinician', 'nrx_clinician_nonce' );
	$role     = get_post_meta( $post->ID, '_nrx_role', true );
	$state    = get_post_meta( $post->ID, '_nrx_license_state', true );
	$number   = get_post_meta( $post->ID, '_nrx_license_number', true );
	$verified = get_post_meta( $post->ID, '_nrx_verified', true );
	$checked  = get_post_meta( $post->ID, '_nrx_verified_at', true );
	$photo    = get_post_meta( $post->ID, '_nrx_photo', true );
	$langs    = get_post_meta( $post->ID, '_nrx_languages', true );
	$can      = nrx_can_verify();
	?>
	<div class="nrx-admin">
		<?php if ( ! $can ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Solo el equipo de cumplimiento puede verificar credenciales. Puedes editar la biografía, pero los datos de licencia son de solo lectura.', 'nuestrarx-core' ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th style="width:190px"><label for="nrx_role"><?php esc_html_e( 'Rol visible (ES)', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_role" name="nrx_people[role]" value="<?php echo esc_attr( $role ); ?>" placeholder="<?php esc_attr_e( 'Médica internista', 'nuestrarx-core' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="nrx_license_state"><?php esc_html_e( 'Estado de la licencia', 'nuestrarx-core' ); ?></label></th>
					<td>
						<select id="nrx_license_state" name="nrx_people[license_state]" <?php disabled( ! $can ); ?>>
							<option value=""><?php esc_html_e( 'Sin indicar', 'nuestrarx-core' ); ?></option>
							<?php foreach ( nrx_states() as $code => $names ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( strtoupper( (string) $state ), $code ); ?>><?php echo esc_html( $names[0] . ' (' . $code . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="nrx_license_number"><?php esc_html_e( 'Número de licencia', 'nuestrarx-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="nrx_license_number" name="nrx_people[license_number]" value="<?php echo esc_attr( $number ); ?>" <?php disabled( ! $can ); ?>>
						<p class="description"><?php esc_html_e( 'Se publica junto al nombre. No inventes ni completes números: debe coincidir con el registro estatal.', 'nuestrarx-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="nrx_languages"><?php esc_html_e( 'Idiomas de consulta', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_languages" name="nrx_people[languages]" value="<?php echo esc_attr( $langs ); ?>" placeholder="<?php esc_attr_e( 'Español, inglés', 'nuestrarx-core' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="nrx_photo"><?php esc_html_e( 'Foto', 'nuestrarx-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="nrx_photo" name="nrx_people[photo]" value="<?php echo esc_attr( $photo ); ?>" placeholder="<?php esc_attr_e( 'URL o ID de la biblioteca de medios', 'nuestrarx-core' ); ?>">
						<p class="description"><?php esc_html_e( 'Si se deja vacío se usa la imagen destacada; si tampoco existe, se muestra la inicial del nombre.', 'nuestrarx-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Verificación', 'nuestrarx-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="nrx_people[verified]" value="1" <?php checked( $verified, '1' ); ?> <?php disabled( ! $can ); ?>> <?php esc_html_e( 'Credenciales verificadas (publicar en el sitio)', 'nuestrarx-core' ); ?></label>
						<?php if ( $checked ) : ?>
							<p class="description"><?php echo esc_html( sprintf( __( 'Última verificación: %s', 'nuestrarx-core' ), $checked ) ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Save clinician fields.
 *
 * @param int $post_id Post ID.
 */
function nrx_save_clinician( $post_id ) {
	if ( ! isset( $_POST['nrx_clinician_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nrx_clinician_nonce'] ) ), 'nrx_save_clinician' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$data = isset( $_POST['nrx_people'] ) && is_array( $_POST['nrx_people'] ) ? wp_unslash( $_POST['nrx_people'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.

	update_post_meta( $post_id, '_nrx_role', sanitize_text_field( $data['role'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_languages', sanitize_text_field( $data['languages'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_photo', sanitize_text_field( $data['photo'] ?? '' ) );

	if ( nrx_can_verify() ) {
		$state = strtoupper( sanitize_text_field( $data['license_state'] ?? '' ) );
		update_post_meta( $post_id, '_nrx_license_state', array_key_exists( $state, nrx_states() ) ? $state : '' );
		update_post_meta( $post_id, '_nrx_license_number', sanitize_text_field( $data['license_number'] ?? '' ) );
		$verified = ! empty( $data['verified'] ) ? '1' : '';
		update_post_meta( $post_id, '_nrx_verified', $verified );
		update_post_meta( $post_id, '_nrx_verified_at', $verified ? current_time( 'Y-m-d' ) : '' );
	}
}
add_action( 'save_post_nrx_clinician', 'nrx_save_clinician' );

/* -------------------------------------------------------------------------
 * Pharmacies
 * ---------------------------------------------------------------------- */

/**
 * Meta box: pharmacy licensing.
 *
 * @param WP_Post $post Post.
 */
function nrx_pharmacy_box( $post ) {
	wp_nonce_field( 'nrx_save_pharmacy', 'nrx_pharmacy_nonce' );
	$type     = get_post_meta( $post->ID, '_nrx_type', true );
	$state    = get_post_meta( $post->ID, '_nrx_license_state', true );
	$number   = get_post_meta( $post->ID, '_nrx_license_number', true );
	$verified = get_post_meta( $post->ID, '_nrx_verified', true );
	$at       = get_post_meta( $post->ID, '_nrx_verified_at', true );
	$docs     = get_post_meta( $post->ID, '_nrx_documents', true );
	$can      = nrx_can_verify();
	$types    = array(
		'503A'      => __( '503A · farmacia de compounding estatal', 'nuestrarx-core' ),
		'503B'      => __( '503B · outsourcing facility', 'nuestrarx-core' ),
		'minor'     => __( 'Farmacia de compounding no clasificada', 'nuestrarx-core' ),
		'other'     => __( 'Otro', 'nuestrarx-core' ),
	);
	?>
	<div class="nrx-admin">
		<?php if ( ! $can ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'La verificación de farmacias está reservada al equipo de cumplimiento.', 'nuestrarx-core' ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th style="width:190px"><label for="nrx_ph_type"><?php esc_html_e( 'Tipo de licencia', 'nuestrarx-core' ); ?></label></th>
					<td>
						<select id="nrx_ph_type" name="nrx_pharmacy[type]" <?php disabled( ! $can ); ?>>
							<option value=""><?php esc_html_e( 'Sin indicar', 'nuestrarx-core' ); ?></option>
							<?php foreach ( $types as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="nrx_ph_state"><?php esc_html_e( 'Estado de licencia', 'nuestrarx-core' ); ?></label></th>
					<td>
						<select id="nrx_ph_state" name="nrx_pharmacy[license_state]" <?php disabled( ! $can ); ?>>
							<option value=""><?php esc_html_e( 'Sin indicar', 'nuestrarx-core' ); ?></option>
							<?php foreach ( nrx_states() as $code => $names ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( strtoupper( (string) $state ), $code ); ?>><?php echo esc_html( $names[0] . ' (' . $code . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="nrx_ph_number"><?php esc_html_e( 'Número de licencia', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_ph_number" name="nrx_pharmacy[license_number]" value="<?php echo esc_attr( $number ); ?>" <?php disabled( ! $can ); ?>></td>
				</tr>
				<tr>
					<th><label for="nrx_ph_docs"><?php esc_html_e( 'Documentos revisados', 'nuestrarx-core' ); ?></label></th>
					<td>
						<textarea id="nrx_ph_docs" name="nrx_pharmacy[documents]" rows="3" class="large-text" <?php disabled( ! $can ); ?>><?php echo esc_textarea( $docs ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Describe qué documentos se recibieron: licencia, certificados, análisis por lote. No publiques números de lote ni resultados que no tengas en físico.', 'nuestrarx-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Verificación', 'nuestrarx-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="nrx_pharmacy[verified]" value="1" <?php checked( $verified, '1' ); ?> <?php disabled( ! $can ); ?>> <?php esc_html_e( 'Farmacia verificada (publicar en el sitio)', 'nuestrarx-core' ); ?></label>
						<?php if ( $at ) : ?><p class="description"><?php echo esc_html( sprintf( __( 'Última verificación: %s', 'nuestrarx-core' ), $at ) ); ?></p><?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Save pharmacy fields.
 *
 * @param int $post_id Post ID.
 */
function nrx_save_pharmacy( $post_id ) {
	if ( ! isset( $_POST['nrx_pharmacy_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nrx_pharmacy_nonce'] ) ), 'nrx_save_pharmacy' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || ! nrx_can_verify() ) {
		return;
	}
	$data = isset( $_POST['nrx_pharmacy'] ) && is_array( $_POST['nrx_pharmacy'] ) ? wp_unslash( $_POST['nrx_pharmacy'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.

	$type  = sanitize_key( $data['type'] ?? '' );
	$state = strtoupper( sanitize_text_field( $data['license_state'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_type', in_array( $type, array( '503A', '503B', 'minor', 'other' ), true ) ? $type : '' );
	update_post_meta( $post_id, '_nrx_license_state', array_key_exists( $state, nrx_states() ) ? $state : '' );
	update_post_meta( $post_id, '_nrx_license_number', sanitize_text_field( $data['license_number'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_documents', sanitize_textarea_field( $data['documents'] ?? '' ) );
	$verified = ! empty( $data['verified'] ) ? '1' : '';
	update_post_meta( $post_id, '_nrx_verified', $verified );
	update_post_meta( $post_id, '_nrx_verified_at', $verified ? current_time( 'Y-m-d' ) : '' );
}
add_action( 'save_post_nrx_pharmacy', 'nrx_save_pharmacy' );

/* -------------------------------------------------------------------------
 * Testimonials
 * ---------------------------------------------------------------------- */

/**
 * Meta box: testimonial source and consent.
 *
 * @param WP_Post $post Post.
 */
function nrx_testimonial_box( $post ) {
	wp_nonce_field( 'nrx_save_testimonial', 'nrx_testimonial_nonce' );
	$name      = get_post_meta( $post->ID, '_nrx_name', true );
	$location  = get_post_meta( $post->ID, '_nrx_location', true );
	$quote     = get_post_meta( $post->ID, '_nrx_quote', true );
	$result    = get_post_meta( $post->ID, '_nrx_result', true );
	$consent   = get_post_meta( $post->ID, '_nrx_consent_verified', true );
	$comp      = get_post_meta( $post->ID, '_nrx_compensated', true );
	$source    = get_post_meta( $post->ID, '_nrx_source', true );
	$treatment = get_post_meta( $post->ID, '_nrx_treatment', true );
	$can       = nrx_can_verify();
	?>
	<div class="nrx-admin">
		<?php if ( ! $can ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Solo el equipo de cumplimiento puede marcar un testimonio como autorizado.', 'nuestrarx-core' ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th style="width:190px"><label for="nrx_te_name"><?php esc_html_e( 'Nombre publicado', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_te_name" name="nrx_testimonial[name]" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Nombre o iniciales', 'nuestrarx-core' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="nrx_te_location"><?php esc_html_e( 'Ciudad / estado', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_te_location" name="nrx_testimonial[location]" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'Miami, FL', 'nuestrarx-core' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="nrx_te_quote"><?php esc_html_e( 'Testimonio', 'nuestrarx-core' ); ?></label></th>
					<td><textarea id="nrx_te_quote" name="nrx_testimonial[quote]" rows="4" class="large-text"><?php echo esc_textarea( $quote ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="nrx_te_result"><?php esc_html_e( 'Resultado declarado (opcional)', 'nuestrarx-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="nrx_te_result" name="nrx_testimonial[result]" value="<?php echo esc_attr( $result ); ?>">
						<p class="description"><?php esc_html_e( 'Solo si el paciente lo declaró y lo autorizó. Nunca lo completes con un promedio.', 'nuestrarx-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="nrx_te_treatment"><?php esc_html_e( 'Tratamiento', 'nuestrarx-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nrx_te_treatment" name="nrx_testimonial[treatment]" value="<?php echo esc_attr( $treatment ); ?>" placeholder="<?php esc_attr_e( 'Semaglutida', 'nuestrarx-core' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="nrx_te_source"><?php esc_html_e( 'Fuente de la autorización', 'nuestrarx-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="nrx_te_source" name="nrx_testimonial[source]" value="<?php echo esc_attr( $source ); ?>" placeholder="<?php esc_attr_e( 'Ej. correo del 2026-05-02, ID de caso', 'nuestrarx-core' ); ?>">
						<p class="description"><?php esc_html_e( 'Registra dónde está guardada la autorización escrita. El sitio no la muestra.', 'nuestrarx-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Divulgación', 'nuestrarx-core' ); ?></th>
					<td><label><input type="checkbox" name="nrx_testimonial[compensated]" value="1" <?php checked( $comp, '1' ); ?>> <?php esc_html_e( 'El paciente recibió una compensación (se mostrará la nota)', 'nuestrarx-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Autorización', 'nuestrarx-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="nrx_testimonial[consent_verified]" value="1" <?php checked( $consent, '1' ); ?> <?php disabled( ! $can ); ?>> <?php esc_html_e( 'Autorización escrita verificada (requisito para publicar)', 'nuestrarx-core' ); ?></label>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Save testimonial fields.
 *
 * @param int $post_id Post ID.
 */
function nrx_save_testimonial( $post_id ) {
	if ( ! isset( $_POST['nrx_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nrx_testimonial_nonce'] ) ), 'nrx_save_testimonial' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$data = isset( $_POST['nrx_testimonial'] ) && is_array( $_POST['nrx_testimonial'] ) ? wp_unslash( $_POST['nrx_testimonial'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.

	update_post_meta( $post_id, '_nrx_name', sanitize_text_field( $data['name'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_location', sanitize_text_field( $data['location'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_quote', sanitize_textarea_field( $data['quote'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_result', sanitize_text_field( $data['result'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_treatment', sanitize_text_field( $data['treatment'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_compensated', ! empty( $data['compensated'] ) ? '1' : '' );

	if ( nrx_can_verify() ) {
		update_post_meta( $post_id, '_nrx_source', sanitize_text_field( $data['source'] ?? '' ) );
		update_post_meta( $post_id, '_nrx_consent_verified', ! empty( $data['consent_verified'] ) ? '1' : '' );
	}
}
add_action( 'save_post_nrx_testimonial', 'nrx_save_testimonial' );

/* -------------------------------------------------------------------------
 * Registration + list columns
 * ---------------------------------------------------------------------- */

/**
 * Register the boxes.
 */
function nrx_people_meta_boxes() {
	add_meta_box( 'nrx_clinician_credentials', __( 'Credenciales y verificación', 'nuestrarx-core' ), 'nrx_clinician_box', 'nrx_clinician', 'normal', 'high' );
	add_meta_box( 'nrx_pharmacy_license', __( 'Licencia y verificación', 'nuestrarx-core' ), 'nrx_pharmacy_box', 'nrx_pharmacy', 'normal', 'high' );
	add_meta_box( 'nrx_testimonial_source', __( 'Testimonio y autorización', 'nuestrarx-core' ), 'nrx_testimonial_box', 'nrx_testimonial', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nrx_people_meta_boxes' );

/**
 * Add a verification column to the three lists.
 *
 * @param array $columns Columns.
 * @return array
 */
function nrx_people_columns( $columns ) {
	$columns['nrx_verified_col'] = __( 'Verificado', 'nuestrarx-core' );
	return $columns;
}
add_filter( 'manage_nrx_clinician_posts_columns', 'nrx_people_columns' );
add_filter( 'manage_nrx_pharmacy_posts_columns', 'nrx_people_columns' );
add_filter( 'manage_nrx_testimonial_posts_columns', 'nrx_people_columns' );

/**
 * Render the verification column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function nrx_people_column_content( $column, $post_id ) {
	if ( 'nrx_verified_col' !== $column ) {
		return;
	}
	$type = get_post_type( $post_id );
	$key  = 'nrx_testimonial' === $type ? '_nrx_consent_verified' : '_nrx_verified';
	$flag = get_post_meta( $post_id, $key, true );
	echo $flag
		? '<span class="nrx-pill" style="background:#edfaef;color:#1a7f37">' . esc_html__( 'Sí', 'nuestrarx-core' ) . '</span>'
		: '<span class="nrx-pill nrx-pill-warn">' . esc_html__( 'Pendiente', 'nuestrarx-core' ) . '</span>';
}
add_action( 'manage_nrx_clinician_posts_custom_column', 'nrx_people_column_content', 10, 2 );
add_action( 'manage_nrx_pharmacy_posts_custom_column', 'nrx_people_column_content', 10, 2 );
add_action( 'manage_nrx_testimonial_posts_custom_column', 'nrx_people_column_content', 10, 2 );

/**
 * Admin notice explaining why an item is not on the site yet.
 */
function nrx_people_gate_notice() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'nrx_clinician', 'nrx_pharmacy', 'nrx_testimonial' ), true ) ) {
		return;
	}
	$type = $screen->post_type;
	$text = 'nrx_testimonial' === $type
		? __( 'Los testimonios solo aparecen en el sitio cuando la autorización escrita está verificada.', 'nuestrarx-core' )
		: __( 'Estos perfiles solo aparecen en el sitio cuando la verificación está marcada.', 'nuestrarx-core' );
	echo '<div class="notice notice-info"><p>' . esc_html( $text ) . '</p></div>';
}
add_action( 'admin_notices', 'nrx_people_gate_notice' );
