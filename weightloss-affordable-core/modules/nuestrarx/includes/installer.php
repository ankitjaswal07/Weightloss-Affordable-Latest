<?php
/**
 * Idempotent content installer and setup screen.
 *
 * Nothing is created on activation. An administrator reviews the checklist
 * and runs the installer explicitly. Running it twice never duplicates a page,
 * section, treatment or menu item: every seeded object carries a `_nrx_seed`
 * key and is matched before insertion.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NRX_CONTENT_VERSION = '1.2.4';

/**
 * Full page manifest.
 *
 * @return array<string,array>
 */
function nrx_page_manifest() {
	return array(
		'inicio' => array(
			'title'    => 'Inicio',
			'template' => '',
			'html'     => '',
			'front'    => true,
		),
		'evaluacion' => array(
			'title'    => 'Evaluación médica',
			'template' => 'page-templates/intake.php',
			'html'     => '',
			'noindex'  => true,
		),
		// Live site also serves a second funnel at /intake. The URL is kept so
		// existing links keep working; it renders the same quiz and points its
		// canonical at /evaluacion/.
		'intake' => array(
			'title'    => 'Evaluación médica (enlace anterior)',
			'template' => 'page-templates/intake.php',
			'html'     => '',
			'noindex'  => true,
		),
		'nosotros' => array(
			'title'    => 'Nosotros',
			'template' => '',
			'html'     => 'nosotros.html',
		),
		'clinicos' => array(
			'title'    => 'Profesionales de la salud',
			'template' => '',
			'html'     => 'clinicos.html',
			'sections' => array( 'clinicians', 'clinician_team', 'provider_resources', 'cta' ),
		),
		'farmacias' => array(
			'title'    => 'Transparencia de farmacias',
			'template' => '',
			'html'     => 'farmacias.html',
			'sections' => array( 'pharmacy_partners', 'cta' ),
		),
		'cumplimiento' => array(
			'title'    => 'Cumplimiento',
			'template' => '',
			'html'     => 'cumplimiento.html',
			'sections' => array( 'cta' ),
		),
		'informacion-de-seguridad' => array(
			'title'    => 'Información de seguridad',
			'template' => '',
			'html'     => 'informacion-de-seguridad.html',
		),
		'aviso-hipaa' => array(
			'title'    => 'Aviso HIPAA',
			'template' => '',
			'html'     => 'aviso-hipaa.html',
		),
		'politica-de-privacidad' => array(
			'title'    => 'Política de privacidad',
			'template' => '',
			'html'     => 'politica-de-privacidad.html',
		),
		'politica-de-reembolso' => array(
			'title'    => 'Política de reembolso',
			'template' => '',
			'html'     => 'politica-de-reembolso.html',
		),
		'terminos-de-servicio' => array(
			'title'    => 'Términos de servicio',
			'template' => '',
			'html'     => 'terminos-de-servicio.html',
		),
		'pago-semanal' => array(
			'title'    => 'Pago semanal',
			'template' => '',
			'html'     => 'pago-semanal.html',
		),
		'blog' => array(
			'title'    => 'Blog',
			'template' => '',
			'html'     => '',
			'posts'    => true,
		),
	);
}

/**
 * Read a content file.
 *
 * @param string $relative Path inside content/.
 * @return string
 */
function nrx_content_file( $relative ) {
	$path = NRX_CORE_DIR . 'content/' . ltrim( $relative, '/' );
	if ( ! file_exists( $path ) ) {
		return '';
	}
	return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local read-only seed file.
}

/**
 * Create or update a seeded post.
 *
 * @param array $args Post args + seed key.
 * @return int Post ID (0 on failure).
 */
function nrx_seed_post( $args ) {
	$defaults = array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => '',
		'post_name'   => '',
		'post_content' => '',
		'meta'        => array(),
	);
	$args = wp_parse_args( $args, $defaults );
	$seed = $args['seed'];
	$existing = get_posts(
		array(
			'post_type'      => $args['post_type'],
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_nrx_seed',
			'meta_value'     => $seed,
			'no_found_rows'  => true,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => $args['post_type'],
			'post_status'  => $args['post_status'],
			'post_title'   => $args['post_title'],
			'post_name'    => $args['post_name'],
			'post_content' => $args['post_content'],
			'menu_order'   => (int) ( $args['menu_order'] ?? 0 ),
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		nrx_log( 'No se pudo crear el contenido inicial', array( 'seed' => $seed, 'error' => $post_id->get_error_message() ) );
		return 0;
	}
	update_post_meta( $post_id, '_nrx_seed', $seed );
	foreach ( (array) $args['meta'] as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
	return (int) $post_id;
}

/**
 * Create one seeded section.
 *
 * @param string $key    Unique seed key.
 * @param string $type   Section type.
 * @param string $title  Admin title.
 * @param array  $fields Field values (flat, with *_en twins).
 * @param array  $args   Layout args.
 * @return int
 */
function nrx_seed_section( $key, $type, $title, $fields, $args = array() ) {
	$types = nrx_section_types();
	if ( ! isset( $types[ $type ] ) ) {
		return 0;
	}
	$args = wp_parse_args(
		$args,
		array(
			'order'        => 0,
			'show_on'      => array( 'front' ),
			'enabled'      => 1,
			'anchor'       => '',
			'background'   => '',
			'spacing'      => '',
			'devices'      => array( 'desktop', 'tablet', 'mobile' ),
			'audience'     => 'all',
		)
	);
	$post_id = nrx_seed_post(
		array(
			'post_type'    => 'nrx_section',
			'post_title'   => $title,
			'post_name'    => sanitize_title( $key ),
			'post_status'  => 'publish',
			'menu_order'   => (int) $args['order'],
			'seed'         => $key,
			'meta'         => array(
				'_nrx_section_type' => $type,
				'_nrx_enabled'      => $args['enabled'] ? '1' : '0',
				'_nrx_show_on'      => (array) $args['show_on'],
				'_nrx_devices'      => (array) $args['devices'],
				'_nrx_anchor'       => sanitize_title( $args['anchor'] ),
				'_nrx_background'   => sanitize_key( $args['background'] ),
				'_nrx_spacing'      => sanitize_key( $args['spacing'] ),
				'_nrx_audience'     => sanitize_key( $args['audience'] ),
			),
		)
	);
	if ( ! $post_id ) {
		return 0;
	}

	// Only fill fields when they are still empty, so re-running never
	// overwrites edits made in WordPress.
	$submitted = $fields;
	$values    = nrx_sanitize_fields( $types[ $type ]['fields'], $submitted );
	foreach ( $types[ $type ]['fields'] as $field ) {
		$key_name = $field['key'] ?? '';
		if ( ! $key_name ) {
			continue;
		}
		$existing = get_post_meta( $post_id, '_nrx_' . $key_name, true );
		$value    = $values[ $key_name ] ?? '';
		$is_empty = '' === $existing || array() === $existing || null === $existing || false === $existing;
		if ( $is_empty && '' !== $value && array() !== $value ) {
			update_post_meta( $post_id, '_nrx_' . $key_name, $value );
		}
		if ( ! empty( $field['bilingual'] ) ) {
			$en_key  = $key_name . '_en';
			$current = get_post_meta( $post_id, '_nrx_' . $en_key, true );
			$en_val  = $values[ $en_key ] ?? '';
			if ( ( '' === $current || null === $current ) && '' !== $en_val ) {
				update_post_meta( $post_id, '_nrx_' . $en_key, $en_val );
			}
		}
	}
	return $post_id;
}

/**
 * Seed one treatment.
 *
 * @param string $slug   Slug.
 * @param array  $record Title + fields.
 * @return int
 */
function nrx_seed_treatment( $slug, $record, $force = false ) {
	$post_id = nrx_seed_post(
		array(
			'post_type'   => 'nrx_treatment',
			'post_title'  => $record['title'],
			'post_name'   => $slug,
			'post_status' => 'publish',
			'seed'        => 'treatment-' . $slug,
			'meta'        => array(
				'_nrx_seed_slug' => $slug,
			),
		)
	);
	if ( ! $post_id ) {
		return 0;
	}
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => 'publish',
			'post_title'  => $record['title'],
			'post_name'   => $slug,
		)
	);
	$fields = nrx_treatment_fields();
	$values = nrx_sanitize_fields( $fields, $record['fields'] );
	foreach ( $fields as $field ) {
		$key = $field['key'] ?? '';
		if ( ! $key ) {
			continue;
		}
		$existing = get_post_meta( $post_id, '_nrx_' . $key, true );
		$empty    = ( '' === $existing || array() === $existing || null === $existing || false === $existing );
		if ( ( $force || $empty ) && isset( $values[ $key ] ) && '' !== $values[ $key ] && array() !== $values[ $key ] ) {
			update_post_meta( $post_id, '_nrx_' . $key, $values[ $key ] );
		}
		if ( ! empty( $field['bilingual'] ) ) {
			$en_key  = $key . '_en';
			$current = get_post_meta( $post_id, '_nrx_' . $en_key, true );
			if ( ( $force || '' === $current || null === $current ) && ! empty( $values[ $en_key ] ) ) {
				update_post_meta( $post_id, '_nrx_' . $en_key, $values[ $en_key ] );
			}
		}
	}
	if ( ! empty( $record['fields']['related_slugs'] ) ) {
		update_post_meta( $post_id, '_nrx_related_slugs', sanitize_text_field( $record['fields']['related_slugs'] ) );
	}
	return $post_id;
}

/**
 * Create the two navigation menus once.
 *
 * @param array $pages Map slug => page ID.
 * @return array
 */
function nrx_seed_menus( $pages ) {
	$report = array();

	$primary = wp_get_nav_menu_object( 'NuestraRx principal' );
	if ( ! $primary ) {
		$primary_id = wp_create_nav_menu( 'NuestraRx principal' );
		if ( is_wp_error( $primary_id ) ) {
			return $report;
		}
		$primary = wp_get_nav_menu_object( $primary_id );

		$items = array(
			array( 'title' => 'Inicio', 'page' => $pages['inicio'] ?? 0 ),
			array( 'title' => 'Tratamientos', 'url' => home_url( '/#tratamientos' ) ),
			array( 'title' => 'Semaglutida', 'page' => 0, 'url' => home_url( '/semaglutida/' ), 'parent' => 'Tratamientos' ),
			array( 'title' => 'Tirzepatida', 'page' => 0, 'url' => home_url( '/tirzepatida/' ), 'parent' => 'Tratamientos' ),
			array( 'title' => 'Cómo funciona', 'url' => home_url( '/#como-funciona' ) ),
			array( 'title' => 'Nosotros', 'page' => $pages['nosotros'] ?? 0 ),
			array( 'title' => 'Profesionales', 'page' => $pages['clinicos'] ?? 0 ),
			array( 'title' => 'Farmacias', 'page' => $pages['farmacias'] ?? 0 ),
		);
		$parent_ids = array();
		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'     => $item['title'],
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => isset( $item['parent'], $parent_ids[ $item['parent'] ] ) ? $parent_ids[ $item['parent'] ] : 0,
			);
			if ( ! empty( $item['page'] ) ) {
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = (int) $item['page'];
				$args['menu-item-type']      = 'post_type';
			} else {
				$args['menu-item-url']  = $item['url'];
				$args['menu-item-type'] = 'custom';
			}
			$item_id = wp_update_nav_menu_item( $primary->term_id, 0, $args );
			if ( 'Tratamientos' === $item['title'] && ! is_wp_error( $item_id ) ) {
				$parent_ids['Tratamientos'] = $item_id;
			}
		}
		$report['primary'] = 'created';
	}

	$footer = wp_get_nav_menu_object( 'NuestraRx legal' );
	if ( ! $footer ) {
		$footer_id = wp_create_nav_menu( 'NuestraRx legal' );
		if ( ! is_wp_error( $footer_id ) ) {
			$legal = array(
				'nosotros'                 => 'Nosotros',
				'cumplimiento'             => 'Cumplimiento',
				'informacion-de-seguridad' => 'Seguridad',
				'politica-de-privacidad'   => 'Privacidad',
				'aviso-hipaa'              => 'Aviso HIPAA',
				'politica-de-reembolso'    => 'Reembolsos',
				'terminos-de-servicio'     => 'Términos',
			);
			foreach ( $legal as $slug => $title ) {
				if ( empty( $pages[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item(
					$footer_id,
					0,
					array(
						'menu-item-title'     => $title,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => (int) $pages[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
			$report['footer'] = 'created';
		}
	}

	$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $primary->term_id;
	if ( $footer ) {
		$locations['footer'] = $footer->term_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	return $report;
}

/**
 * Seed the page-level sections for the clinician hub and pharmacy page.
 *
 * @param array $pages Page IDs.
 */
function nrx_seed_page_sections( $pages ) {
	$common_cta = array(
		'eyebrow'        => 'Siguiente paso',
		'eyebrow_en'     => 'Next step',
		'title'          => '¿Tienes preguntas antes de empezar?',
		'title_en'       => 'Do you have questions before starting?',
		'body'           => 'Escríbenos en español o completa la evaluación médica gratuita.',
		'body_en'        => 'Message us in Spanish or complete the free medical evaluation.',
		'primary_label'  => 'Haz tu evaluación gratis',
		'primary_label_en' => 'Take the free evaluation',
		'primary_url'    => '/evaluacion/',
		'secondary_label' => 'Hablar por WhatsApp',
		'secondary_label_en' => 'Talk on WhatsApp',
		'secondary_url'  => '',
		'chips'          => array(),
		'disclaimer'     => 'La evaluación no garantiza una receta. La decisión es del médico.',
		'disclaimer_en'  => 'The evaluation does not guarantee a prescription. The decision belongs to the clinician.',
	);

	if ( ! empty( $pages['clinicos'] ) ) {
		$id = $pages['clinicos'];
		nrx_seed_section(
			'page-clinicos-clinicians',
			'clinicians',
			'Clínicos · Modelo de supervisión',
			array(
				'eyebrow'      => 'Supervisión médica',
				'eyebrow_en'   => 'Medical oversight',
				'title'        => 'Un médico licenciado revisa cada caso',
				'title_en'     => 'A licensed clinician reviews every case',
				'body'         => 'La evaluación estandarizada llega al equipo clínico con las banderas de seguridad relevantes. El médico decide de forma independiente y puede solicitar información adicional o rechazar el tratamiento.',
				'body_en'      => 'The standardized evaluation reaches the clinical team with the relevant safety flags. The clinician decides independently and may request more information or decline treatment.',
				'cards'        => array(
					array( 'icon' => 'shield', 'tag' => 'Criterios', 'tag_en' => 'Criteria', 'title' => 'Contraindicaciones', 'title_en' => 'Contraindications', 'body' => 'MTC, MEN2, embarazo, lactancia y pancreatitis activa bloquean la prescripción.', 'body_en' => 'MTC, MEN2, pregnancy, breastfeeding and active pancreatitis block prescribing.' ),
					array( 'icon' => 'document', 'tag' => 'Registro', 'tag_en' => 'Record', 'title' => 'Trazabilidad', 'title_en' => 'Traceability', 'body' => 'Cada evaluación conserva la versión del cuestionario y el consentimiento aceptado.', 'body_en' => 'Every evaluation keeps the questionnaire version and the accepted consent.' ),
					array( 'icon' => 'lab', 'tag' => 'Farmacia', 'tag_en' => 'Pharmacy', 'title' => 'Verificación', 'title_en' => 'Verification', 'body' => 'Solo se envían recetas a farmacias con licencia verificada.', 'body_en' => 'Prescriptions are sent only to pharmacies with verified licenses.' ),
				),
				'image'        => '',
				'image_alt'    => '',
				'cta_label'    => 'Ver recursos clínicos',
				'cta_label_en' => 'See clinical resources',
				'cta_url'      => '#recursos-clinicos',
				'note'         => 'No publicamos protocolos internos en abierto.',
				'note_en'      => 'We do not publish internal protocols publicly.',
			),
			array( 'order' => 10, 'show_on' => array( $id ), 'anchor' => 'supervision', 'background' => 'paper' )
		);
		nrx_seed_section(
			'page-clinicos-team',
			'clinician_team',
			'Clínicos · Equipo',
			array(
				'eyebrow'    => 'Equipo',
				'eyebrow_en' => 'Team',
				'title'      => 'Perfiles del equipo clínico',
				'title_en'   => 'Clinical team profiles',
				'intro'      => 'Publicamos el nombre, la especialidad y el estado de licencia de cada profesional solo después de verificar sus credenciales.',
				'intro_en'   => 'We publish each professional\'s name, specialty and license state only after verifying credentials.',
				'empty_text' => 'Los perfiles se están verificando. Mientras tanto, puedes confirmar la licencia de tu proveedor a través del portal o escribiendo a hola@nuestrarx.com.',
				'empty_text_en' => 'Profiles are being verified. In the meantime, you can confirm your provider\'s license through the portal or by emailing hola@nuestrarx.com.',
			),
			array( 'order' => 20, 'show_on' => array( $id ), 'anchor' => 'equipo', 'background' => 'ivory' )
		);
		nrx_seed_section(
			'page-clinicos-resources',
			'provider_resources',
			'Clínicos · Recursos',
			array(
				'eyebrow'       => 'Recursos',
				'eyebrow_en'    => 'Resources',
				'title'         => 'Material para profesionales',
				'title_en'      => 'Material for professionals',
				'intro'         => 'Enlaces oficiales y documentos de referencia. Los protocolos internos se publican en el área restringida.',
				'intro_en'      => 'Official links and reference documents. Internal protocols are published in the restricted area.',
				'resources'     => array(
					array( 'title' => 'Información de seguridad para pacientes', 'title_en' => 'Patient safety information', 'body' => 'Documento público que puedes compartir con tus pacientes.', 'body_en' => 'A public document you can share with patients.', 'url' => '/informacion-de-seguridad/', 'meta' => 'Público', 'meta_en' => 'Public' ),
					array( 'title' => 'MedWatch (FDA)', 'title_en' => 'MedWatch (FDA)', 'body' => 'Reporte de eventos adversos y problemas de calidad.', 'body_en' => 'Adverse event and product quality reporting.', 'url' => 'https://www.fda.gov/medwatch', 'meta' => 'fda.gov', 'meta_en' => 'fda.gov' ),
					array( 'title' => 'Marco de compuestos (FDA)', 'title_en' => 'Compounding framework (FDA)', 'body' => 'Requisitos para instalaciones 503A y 503B.', 'body_en' => 'Requirements for 503A and 503B facilities.', 'url' => 'https://www.fda.gov/drugs/human-drug-compounding', 'meta' => 'fda.gov', 'meta_en' => 'fda.gov' ),
					array( 'title' => 'Verificación de Beluga Health', 'title_en' => 'Beluga Health verification', 'body' => 'Socio clínico y verificación de licencias.', 'body_en' => 'Clinical partner and license verification.', 'url' => 'https://www.belugahealth.com/about', 'meta' => 'belugahealth.com', 'meta_en' => 'belugahealth.com' ),
				),
				'contact_title' => 'Acceso al área clínica',
				'contact_title_en' => 'Clinical area access',
				'contact_body'  => 'Solicita acceso con tu nombre, especialidad, estado de licencia y número de licencia. Verificamos cada solicitud antes de habilitar los protocolos internos.',
				'contact_body_en' => 'Request access with your name, specialty, license state and license number. We verify every request before enabling internal protocols.',
				'disclaimer'    => 'Los recursos externos pertenecen a sus autores. NuestraRx no modifica ni avala su contenido.',
				'disclaimer_en' => 'External resources belong to their authors. NuestraRx does not modify or endorse their content.',
			),
			array( 'order' => 30, 'show_on' => array( $id ), 'anchor' => 'recursos-clinicos', 'background' => 'paper' )
		);
		nrx_seed_section( 'page-clinicos-cta', 'cta', 'Clínicos · CTA', $common_cta, array( 'order' => 40, 'show_on' => array( $id ), 'background' => 'forest' ) );
	}

	if ( ! empty( $pages['farmacias'] ) ) {
		$id = $pages['farmacias'];
		nrx_seed_section(
			'page-farmacias-partners',
			'pharmacy_partners',
			'Farmacias · Verificación',
			array(
				'eyebrow'       => 'Farmacias asociadas',
				'eyebrow_en'    => 'Partner pharmacies',
				'title'         => 'Quién prepara tu medicamento',
				'title_en'      => 'Who prepares your medication',
				'intro'         => 'Cada farmacia debe demostrar licencia vigente, buenas prácticas y capacidad de emitir certificados de análisis por lote. Publicamos sus datos solo después de verificarlos.',
				'intro_en'      => 'Every pharmacy must demonstrate a current license, good practices and the ability to issue batch certificates of analysis. We publish their details only after verification.',
				'steps'         => array(
					array( 'title' => 'Licencia estatal', 'title_en' => 'State license', 'body' => 'Verificamos la licencia en la junta de farmacia del estado de envío.', 'body_en' => 'We verify the license with the pharmacy board of the shipping state.', 'meta' => 'Requisito', 'meta_en' => 'Requirement', 'image' => '', 'image_alt' => '' ),
					array( 'title' => 'Registro 503B', 'title_en' => '503B registration', 'body' => 'Si aplica, confirmamos el registro de la instalación ante la FDA.', 'body_en' => 'If applicable, we confirm the facility registration with the FDA.', 'meta' => 'Requisito', 'meta_en' => 'Requirement', 'image' => '', 'image_alt' => '' ),
					array( 'title' => 'Certificado por lote', 'title_en' => 'Batch certificate', 'body' => 'El COA debe corresponder al lote y estar disponible para el paciente.', 'body_en' => 'The COA must match the batch and be available to the patient.', 'meta' => 'Documento', 'meta_en' => 'Document', 'image' => '', 'image_alt' => '' ),
					array( 'title' => 'Cadena de frío', 'title_en' => 'Cold chain', 'body' => 'El envío debe mantener el rango de 2–8 °C y documentarlo.', 'body_en' => 'Shipping must maintain the 2–8 °C range and document it.', 'meta' => 'Calidad', 'meta_en' => 'Quality', 'image' => '', 'image_alt' => '' ),
				),
				'documents_note' => 'Los listados y certificados se publican cuando la documentación está vigente. Si un documento no puede verificarse, no se muestra.',
				'documents_note_en' => 'Listings and certificates are published while documentation is current. If a document cannot be verified, it is not shown.',
				'disclaimer'    => 'Los medicamentos compuestos no están aprobados por la FDA ni son bioequivalentes a los productos de marca. La preparación se realiza bajo prescripción individual.',
				'disclaimer_en' => 'Compounded medications are not FDA-approved and are not bioequivalent to brand products. Preparation occurs under an individual prescription.',
			),
			array( 'order' => 10, 'show_on' => array( $id ), 'background' => 'paper' )
		);
		nrx_seed_section( 'page-farmacias-cta', 'cta', 'Farmacias · CTA', $common_cta, array( 'order' => 20, 'show_on' => array( $id ), 'background' => 'forest' ) );
	}

	if ( ! empty( $pages['cumplimiento'] ) ) {
		nrx_seed_section( 'page-cumplimiento-cta', 'cta', 'Cumplimiento · CTA', $common_cta, array( 'order' => 10, 'show_on' => array( $pages['cumplimiento'] ), 'background' => 'forest' ) );
	}
}

/**
 * Run the full installation.
 *
 * @return array Report for the setup screen.
 */
function nrx_install_content() {
	$report = array(
		'pages'     => array(),
		'sections'  => 0,
		'treatments' => array(),
		'menus'     => array(),
		'warnings'  => array(),
		'errors'    => array(),
	);

	$wla_combined = defined( 'WLA_CORE_FILE' );

	// 1. Site basics. Keep the active WLA identity and locale in the combined package.
	if ( ! $wla_combined && 'NuestraRx' !== get_option( 'blogname' ) && '' !== (string) nrx_setting( 'brand_name', '' ) ) {
		update_option( 'blogname', nrx_setting( 'brand_name', 'NuestraRx' ) );
	}
	if ( ! $wla_combined && '' === (string) get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'Salud en tu idioma' );
	}
	if ( ! $wla_combined ) {
		update_option( 'timezone_string', 'America/New_York' );
		update_option( 'start_of_week', 0 );
	}

	// 2. Settings defaults. Keep the existing WLA site's identity settings intact.
	if ( ! $wla_combined ) { nrx_update_settings( array() ); }

	// 3. Pages.
	$pages = array();
	foreach ( nrx_page_manifest() as $slug => $page ) {
		$content = $page['html'] ? nrx_content_file( 'pages/' . $page['html'] ) : '';
		$post_id = nrx_seed_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $content,
				'seed'         => 'page-' . $slug,
				'meta'         => array(
					'_wp_page_template' => $page['template'] ?: 'default',
					'_nrx_seo_noindex'  => ! empty( $page['noindex'] ) ? '1' : '',
					'_nrx_legal_draft'  => in_array( $slug, array( 'aviso-hipaa', 'politica-de-privacidad', 'politica-de-reembolso', 'terminos-de-servicio' ), true ) ? '1' : '',
				),
			)
		);
		if ( $post_id ) {
			$pages[ $slug ] = $post_id;
			$report['pages'][ $slug ] = $post_id;
		}
	}

	// 4. Front page and posts page.
	if ( ! $wla_combined && ! empty( $pages['inicio'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['inicio'] );
	}
	if ( ! $wla_combined && ! empty( $pages['blog'] ) ) {
		update_option( 'page_for_posts', $pages['blog'] );
	}

	// 5. Homepage sections.
	$home = require NRX_CORE_DIR . 'content/home-sections.php';
	$order = 0;
	foreach ( (array) $home as $section ) {
		$order += 10;
		$id = nrx_seed_section(
			'home-' . sanitize_title( $section['anchor'] ?? $section['type'] ) . '-' . $section['type'],
			$section['type'],
			$section['title'] ?? $section['type'],
			(array) $section['fields'],
			array(
				'order'      => $order,
				'show_on'    => array( 'front' ),
				'anchor'     => $section['anchor'] ?? '',
				'background' => $section['background'] ?? '',
			)
		);
		if ( $id ) {
			$report['sections']++;
		}
	}

	// 6. Page sections.
	nrx_seed_page_sections( $pages );

	// 7. Treatments.
	$treatments = require NRX_CORE_DIR . 'content/treatments.php';
	foreach ( $treatments as $slug => $record ) {
		if ( $wla_combined && get_page_by_path( $slug, OBJECT, 'nrx_treatment' ) ) { continue; }
		if ( $wla_combined ) { $record['fields']['require_approval'] = 1; }
		$id = nrx_seed_treatment( $slug, $record );
		if ( $wla_combined && $id ) { wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) ); }
		if ( $id ) {
			$report['treatments'][ $slug ] = $id;
		}
	}

	// 8. Quiz (published snapshot only if absent).
	if ( false === get_option( 'nrx_quiz_published' ) ) {
		$config = nrx_quiz_default_config();
		$config['version'] = 1;
		if ( ! $wla_combined ) { update_option( 'nrx_quiz_published', nrx_quiz_normalize( $config ), false ); }
		if ( false === get_option( 'nrx_quiz_draft', false ) ) { update_option( 'nrx_quiz_draft', nrx_quiz_normalize( $config ), false ); }
	}

	// 9. Menus.
	$report['menus'] = $wla_combined ? array( 'note' => __( 'WLA theme navigation was preserved.', 'nuestrarx-core' ) ) : nrx_seed_menus( $pages );

	// 10. Housekeeping.
	nrx_register_post_types();
	delete_transient( 'nrx_treatment_routes' );
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$report['warnings'][] = __( 'Se activaron enlaces permanentes con el nombre de la página. Verifica que el servidor tenga mod_rewrite o reglas equivalentes.', 'nuestrarx-core' );
	}
	if ( ! $wla_combined && '' === (string) get_option( 'WPLANG' ) ) {
		update_option( 'WPLANG', 'es_ES' );
	}
	nrx_register_treatment_rewrites();
	flush_rewrite_rules();
	foreach ( array( 'sample-page' => 'page', 'hello-world' => 'post' ) as $demo_slug => $demo_type ) {
		$demo = get_page_by_path( $demo_slug, OBJECT, $demo_type );
		if ( $demo && ! get_post_meta( $demo->ID, '_nrx_seed', true ) ) {
			wp_trash_post( $demo->ID );
		}
	}
	update_option( 'nrx_content_version', NRX_CONTENT_VERSION, false );
	update_option( 'nrx_installed_at', current_time( 'mysql' ), false );

	$missing_templates = array();
	$theme_dir         = get_stylesheet_directory();
	$required_templates = $wla_combined ? array( 'front-page.php', 'header.php', 'footer.php' ) : array( 'front-page.php', 'header.php', 'footer.php', 'page-templates/intake.php' );
	foreach ( $required_templates as $file ) {
		if ( ! file_exists( $theme_dir . '/' . $file ) ) {
			$missing_templates[] = $file;
		}
	}
	if ( $missing_templates ) {
		$report['warnings'][] = sprintf(
			'El tema activo no contiene: %s. Activa el tema NuestraRx para ver el diseño completo.',
			implode( ', ', $missing_templates )
		);
	}

	if ( $wla_combined ) {
		$report['warnings'][] = __( 'The WLA homepage, brand, timezone and navigation are preserved. Seeded treatments remain drafts, and quiz submissions stay disabled until approved and configured.', 'nuestrarx-core' );
	}
	if ( ! is_ssl() && 'local' !== wp_parse_url( home_url(), PHP_URL_HOST ) && false === strpos( (string) home_url(), 'localhost' ) ) {
		$report['warnings'][] = 'El sitio no está sirviendo por HTTPS. Configura SSL antes de recolectar datos clínicos.';
	}

	nrx_log( 'Contenido inicial instalado', array( 'pages' => count( $report['pages'] ), 'sections' => $report['sections'] ) );
	return $report;
}

/**
 * Handle the installer form.
 */
function nrx_handle_install_content() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para instalar contenido.', 'nuestrarx-core' ) );
	}
	check_admin_referer( 'nrx_install_content', '_nrx_nonce' );
	$report = nrx_install_content();
	set_transient( 'nrx_install_report_' . get_current_user_id(), $report, 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=nrx-setup&installed=1' ) );
	exit;
}
add_action( 'admin_post_nrx_install_content', 'nrx_handle_install_content' );

/**
 * Health checks shown on the setup screen.
 *
 * @return array<string,array{label:string,ok:bool,note:string}>
 */
function nrx_setup_checks() {
	$theme      = wp_get_theme();
	$stylesheet = get_stylesheet();
	$checks     = array();

	$combined = defined( 'WLA_CORE_FILE' );
	$checks['theme'] = array(
		'label' => $combined ? __( 'WeightLossAffordable theme active', 'nuestrarx-core' ) : __( 'NuestraRx theme active', 'nuestrarx-core' ),
		'ok'    => $combined ? 'weightloss-affordable' === $stylesheet || 'weightloss-affordable' === $theme->get( 'Template' ) : ( 'nuestrarx' === $stylesheet || 'nuestrarx' === $theme->get( 'Template' ) ),
		'note'  => sprintf( __( 'Current theme: %s', 'nuestrarx-core' ), $theme->get( 'Name' ) ),
	);
	$checks['https'] = array(
		'label' => __( 'HTTPS', 'nuestrarx-core' ),
		'ok'    => is_ssl(),
		'note'  => is_ssl() ? __( 'Conexión cifrada.', 'nuestrarx-core' ) : __( 'Obligatorio antes de recolectar datos clínicos.', 'nuestrarx-core' ),
	);
	$checks['permalinks'] = array(
		'label' => __( 'Enlaces permanentes', 'nuestrarx-core' ),
		'ok'    => (bool) get_option( 'permalink_structure' ),
		'note'  => get_option( 'permalink_structure' ) ?: __( 'Configura "Nombre de la entrada".', 'nuestrarx-core' ),
	);
	$checks['encryption'] = array(
		'label' => __( 'Cifrado disponible', 'nuestrarx-core' ),
		'ok'    => function_exists( 'sodium_crypto_secretbox' ) || function_exists( 'openssl_encrypt' ),
		'note'  => function_exists( 'sodium_crypto_secretbox' ) ? 'sodium' : ( function_exists( 'openssl_encrypt' ) ? 'openssl' : __( 'Sin extensión de cifrado.', 'nuestrarx-core' ) ),
	);
	$checks['webhook'] = array(
		'label' => __( 'Handoff clínico (webhook)', 'nuestrarx-core' ),
		'ok'    => (bool) nrx_setting( 'quiz_webhook_url' ),
		'note'  => nrx_setting( 'quiz_webhook_url' ) ? __( 'Configurado; verify routing before enabling intake.', 'nuestrarx-core' ) : ( $combined ? __( 'Optional while demo submissions are disabled. Configure and verify an approved clinical handoff before enabling intake.', 'nuestrarx-core' ) : __( 'Pendiente: sin webhook, las evaluaciones se guardan cifradas y requieren gestión manual.', 'nuestrarx-core' ) ),
	);
	$checks['checkout'] = array(
		'label' => __( 'Checkout existente', 'nuestrarx-core' ),
		'ok'    => (bool) nrx_setting( 'checkout_url' ),
		'note'  => nrx_setting( 'checkout_url' ) ? __( 'Configurado.', 'nuestrarx-core' ) : __( 'Pendiente: no se procesan pagos en este sitio sin la integración autorizada.', 'nuestrarx-core' ),
	);
	$checks['compliance'] = array(
		'label' => __( 'Reclamaciones aprobadas', 'nuestrarx-core' ),
		'ok'    => nrx_compliance_is_approved(),
		'note'  => nrx_compliance_is_approved() ? __( 'Aprobadas.', 'nuestrarx-core' ) : __( 'Garantía, testimonios y transformaciones permanecen ocultos.', 'nuestrarx-core' ),
	);
	$quiz_published = false !== get_option( 'nrx_quiz_published' );
	$checks['quiz'] = array(
		'label' => $combined ? __( 'Cuestionario original incluido', 'nuestrarx-core' ) : __( 'Evaluación publicada', 'nuestrarx-core' ),
		'ok'    => $combined ? function_exists( 'nrx_quiz_default_config' ) : $quiz_published,
		'note'  => $combined ? __( 'El cuestionario se muestra como demo; los envíos públicos están desactivados.', 'nuestrarx-core' ) : ( $quiz_published ? sprintf( __( 'Versión %d.', 'nuestrarx-core' ), (int) ( nrx_quiz_get( 'published' )['version'] ?? 1 ) ) : __( 'Se publica al instalar el contenido.', 'nuestrarx-core' ) ),
	);

	return $checks;
}

/**
 * Setup screen.
 */
function nrx_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para ver esta página.', 'nuestrarx-core' ) );
	}
	$report = get_transient( 'nrx_install_report_' . get_current_user_id() );
	if ( $report ) {
		delete_transient( 'nrx_install_report_' . get_current_user_id() );
	}
	$checks = nrx_setup_checks();
	$missing = array_filter(
		$checks,
		static function ( $check ) {
			return ! $check['ok'];
		}
	);
	?>
	<div class="wrap nrx-admin-wrap">
		<h1><?php esc_html_e( 'Instalación de NuestraRx', 'nuestrarx-core' ); ?></h1>

		<?php if ( is_array( $report ) ) : ?>
			<div class="notice notice-success inline">
				<p><strong><?php esc_html_e( 'Contenido instalado.', 'nuestrarx-core' ); ?></strong></p>
				<p>
					<?php
					printf(
						esc_html__( '%1$d páginas, %2$d secciones y %3$d registros de tratamiento añadidos.', 'nuestrarx-core' ),
						count( $report['pages'] ?? array() ),
						(int) ( $report['sections'] ?? 0 ),
						count( $report['treatments'] ?? array() )
					);
					?>
				</p>
				<?php if ( ! empty( $report['warnings'] ) ) : ?>
					<ul><?php foreach ( $report['warnings'] as $warning ) : ?><li><?php echo esc_html( $warning ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nrx-card">
			<h2><?php esc_html_e( 'Antes de empezar', 'nuestrarx-core' ); ?></h2>
			<p><?php esc_html_e( 'Usa este entorno como staging. Haz una copia de seguridad y no ejecutes la instalación sobre el sitio en producción sin autorización.', 'nuestrarx-core' ); ?></p>
			<table class="widefat striped">
				<tbody>
				<?php foreach ( $checks as $id => $check ) : ?>
					<tr>
						<td style="width:32px"><?php echo $check['ok'] ? '<span class="nrx-dot nrx-dot-ok" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Correcto', 'nuestrarx-core' ) . '</span>' : '<span class="nrx-dot nrx-dot-warn" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Pendiente', 'nuestrarx-core' ) . '</span>'; ?></td>
						<td><strong><?php echo esc_html( $check['label'] ); ?></strong><br><small><?php echo esc_html( $check['note'] ); ?></small></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="nrx-card">
			<h2><?php esc_html_e( 'Añadir páginas y datos de NuestraRx', 'nuestrarx-core' ); ?></h2>
			<p><?php esc_html_e( 'Añade páginas de información, secciones y tratamientos de NuestraRx. En el paquete combinado, conserva la portada, navegación y marca de WeightLossAffordable; los tratamientos se dejan como borradores y el cuestionario no acepta envíos públicos hasta que completes su revisión.', 'nuestrarx-core' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('¿Instalar/actualizar el contenido inicial en este entorno?');">
				<input type="hidden" name="action" value="nrx_install_content">
				<?php wp_nonce_field( 'nrx_install_content', '_nrx_nonce' ); ?>
				<?php submit_button( __( 'Instalar contenido', 'nuestrarx-core' ), 'primary', 'submit', false ); ?>
			</form>
			<?php if ( $missing ) : ?>
				<p class="nrx-field-help"><?php esc_html_e( 'Puedes instalar ahora y completar las integraciones pendientes después. Las secciones que dependen de datos no verificados permanecen ocultas.', 'nuestrarx-core' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="nrx-card">
			<h2><?php esc_html_e( 'Después de instalar', 'nuestrarx-core' ); ?></h2>
			<ol class="nrx-numbered">
				<li><?php esc_html_e( 'Revisa y aprueba las reclamaciones en Cumplimiento antes de publicar testimonios o la garantía.', 'nuestrarx-core' ); ?></li>
				<li><?php esc_html_e( 'Configura el webhook clínico y el correo de notificaciones en Ajustes → Evaluación e integraciones.', 'nuestrarx-core' ); ?></li>
				<li><?php esc_html_e( 'Sustituye las imágenes de marcador por fotografías aprobadas.', 'nuestrarx-core' ); ?></li>
				<li><?php esc_html_e( 'Verifica la información legal con asesoría antes de publicar.', 'nuestrarx-core' ); ?></li>
				<li><?php esc_html_e( 'Ejecuta la lista de control de QA incluida en docs/QA-CHECKLIST.md.', 'nuestrarx-core' ); ?></li>
			</ol>
		</div>
	</div>
	<?php
}

/**
 * Run content migrations after a plugin update.
 *
 * Idempotent: re-running the installer only creates what is missing, so it is
 * safe to call from admin_init.
 */
function nrx_maybe_upgrade_content() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$installed = (string) get_option( 'nrx_content_version', '' );
	if ( NRX_CONTENT_VERSION === $installed ) {
		return;
	}
	if ( get_transient( 'nrx_content_upgrade_lock' ) ) {
		return;
	}
	set_transient( 'nrx_content_upgrade_lock', 1, 5 * MINUTE_IN_SECONDS );

	if ( '' === $installed ) {
		// Fresh installs run through activation, not here.
		update_option( 'nrx_content_version', NRX_CONTENT_VERSION, false );
		delete_transient( 'nrx_content_upgrade_lock' );
		return;
	}

	$needs_seeding = ! get_page_by_path( 'evaluacion' );
	if ( $needs_seeding ) {
		nrx_install_content();
	}

	// Point every stored CTA that still uses the old funnel slug at the
	// canonical /evaluacion page. Only exact matches are touched.
	$stale = get_posts(
		array(
			'post_type'      => array( 'nrx_section', 'nrx_treatment' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $stale as $stale_id ) {
		foreach ( array_keys( (array) get_post_meta( $stale_id ) ) as $meta_key ) {
			if ( ! is_string( $meta_key ) || '_nrx_seed' === $meta_key ) {
				continue;
			}
			if ( '/intake/' === get_post_meta( $stale_id, $meta_key, true ) ) {
				update_post_meta( $stale_id, $meta_key, '/evaluacion/' );
			}
		}
	}

	// 1.2.0: the contact step reused the `state` field key, which collided with
	// the dedicated state step and with the state-availability clinical rule.
	foreach ( array( 'nrx_quiz_draft', 'nrx_quiz_published' ) as $quiz_option ) {
		$quiz = get_option( $quiz_option );
		if ( ! is_array( $quiz ) || empty( $quiz['steps'] ) ) {
			continue;
		}
		$quiz_changed = false;
		foreach ( $quiz['steps'] as $step_index => $step ) {
			if ( 'contact' === ( $step['id'] ?? '' ) && ! empty( $step['fields'] ) ) {
				foreach ( $step['fields'] as $field_index => $field ) {
					if ( 'state' === ( $field['key'] ?? '' ) ) {
						$quiz['steps'][ $step_index ]['fields'][ $field_index ]['key'] = 'state_contact';
						$quiz_changed = true;
					}
				}
			}
			if ( 'state' === ( $step['type'] ?? '' ) ) {
				// State option values must be the uppercase keys used by
				// nrx_states(); earlier saves lowercased them through sanitize_title().
				foreach ( (array) ( $step['options'] ?? array() ) as $option_index => $option ) {
					$code = strtoupper( (string) ( $option['value'] ?? '' ) );
					if ( '' !== $code && $code !== ( $option['value'] ?? '' ) && array_key_exists( $code, nrx_states() ) ) {
						$quiz['steps'][ $step_index ]['options'][ $option_index ]['value'] = $code;
						$quiz_changed = true;
					}
				}
			}
			if ( 'goal' === ( $step['id'] ?? '' ) ) {
				// sanitize_title() also stripped the "+" from the top goal tier
				// ("60+" became "60"). Restore it when the label still says 60+.
				foreach ( (array) ( $step['options'] ?? array() ) as $option_index => $option ) {
					if ( '60' === ( $option['value'] ?? '' ) && false !== strpos( (string) ( $option['label'] ?? '' ), '60' ) ) {
						$quiz['steps'][ $step_index ]['options'][ $option_index ]['value'] = '60+';
						$quiz_changed = true;
					}
				}
			}
			if ( 'consent' === ( $step['type'] ?? '' ) ) {
				$keys = array();
				foreach ( (array) ( $step['checkboxes'] ?? array() ) as $box ) {
					$keys[] = $box['key'] ?? '';
				}
				if ( ! empty( $step['field'] ) && in_array( $step['field'], $keys, true ) ) {
					$quiz['steps'][ $step_index ]['field'] = '';
					$quiz_changed = true;
				}
			}
		}
		if ( ! $quiz_changed ) {
			continue;
		}
		$quiz['version'] = (int) ( $quiz['version'] ?? 1 ) + 1;
		$quiz['updated'] = current_time( 'mysql' );
		if ( ! update_option( $quiz_option, $quiz, false ) ) {
			global $wpdb;
			$wpdb->update( $wpdb->options, array( 'option_value' => maybe_serialize( $quiz ) ), array( 'option_name' => $quiz_option ) );
			wp_cache_delete( $quiz_option, 'options' );
		}
	}

	$settings = get_option( 'nrx_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	$settings_changed = false;
	if ( ( $settings['intake_slug'] ?? 'intake' ) === 'intake' && get_page_by_path( 'evaluacion' ) ) {
		$settings['intake_slug'] = 'evaluacion';
		$settings_changed        = true;
	}
	if ( empty( $settings['header_cta_url'] ) || '/intake/' === $settings['header_cta_url'] ) {
		$settings['header_cta_url'] = '/evaluacion/';
		$settings_changed           = true;
	}
	if ( $settings_changed ) {
		// Only advance the content version when every write stuck, otherwise the
		// migration retries on the next admin request (a stale object cache can
		// make update_option() a no-op in some hosts).
		$migration_ok = (bool) update_option( 'nrx_settings', $settings );
		if ( ! $migration_ok && get_option( 'nrx_settings' ) !== $settings ) {
			delete_transient( 'nrx_content_upgrade_lock' );
			return;
		}
	}

	// Post types and redirects changed in this release; refresh the stored rules
	// once so new public routes resolve without a manual permalink save.
	nrx_register_post_types();
	nrx_register_treatment_rewrites();
	flush_rewrite_rules();

	update_option( 'nrx_content_version', NRX_CONTENT_VERSION, false );
	delete_transient( 'nrx_content_upgrade_lock' );
}
add_action( 'admin_init', 'nrx_maybe_upgrade_content', 20 );
