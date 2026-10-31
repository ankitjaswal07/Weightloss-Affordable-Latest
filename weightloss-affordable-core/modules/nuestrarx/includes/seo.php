<?php
/**
 * SEO output, structured data, editable metadata and legacy redirects.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the SEO record for the current request.
 *
 * @return array
 */
function nrx_seo_context() {
	$site = untrailingslashit( home_url() );
	$context = array(
		'title'       => '',
		'description' => '',
		'canonical'   => '',
		'image'       => '',
		'noindex'     => false,
		'type'        => 'website',
		'page_id'     => 0,
	);

	if ( is_front_page() ) {
		$context['title']       = nrx_setting( 'seo_home_title', get_bloginfo( 'name' ) );
		$context['description'] = nrx_setting( 'seo_home_description', get_bloginfo( 'description' ) );
		$context['canonical']   = $site . '/';
		$context['page_id']     = (int) get_option( 'page_on_front' );
	} elseif ( is_singular() ) {
		$id                     = get_queried_object_id();
		$context['page_id']     = $id;
		$context['canonical']   = get_permalink( $id );
		$context['title']       = get_post_meta( $id, '_nrx_seo_title', true );
		$context['description'] = get_post_meta( $id, '_nrx_seo_description', true );
		if ( ! $context['title'] ) {
			$context['title'] = get_the_title( $id ) . ' · ' . get_bloginfo( 'name' );
		}
		if ( ! $context['description'] ) {
			$context['description'] = get_the_excerpt( $id );
		}
		$context['noindex'] = (bool) get_post_meta( $id, '_nrx_seo_noindex', true );
		$context['image']   = (string) get_post_meta( $id, '_nrx_seo_og_image', true );

		// /intake is kept as a working alias of the canonical /evaluacion funnel.
		$intake_slug = (string) nrx_setting( 'intake_slug', 'evaluacion' );
		if ( is_page_template( 'page-templates/intake.php' ) && 'intake' === get_post_field( 'post_name', $id ) && 'intake' !== $intake_slug ) {
			$canonical_page = get_page_by_path( $intake_slug );
			if ( $canonical_page ) {
				$context['canonical'] = get_permalink( $canonical_page );
				$context['title']     = get_the_title( $canonical_page ) . ' · ' . get_bloginfo( 'name' );
			}
		}
		$context['type']    = is_page() ? 'website' : 'article';

		if ( 'nrx_treatment' === get_post_type( $id ) ) {
			$data = nrx_treatment_data( $id );
			if ( ! $context['title'] ) {
				$context['title'] = sprintf( '%s compuesta desde %s/mes · %s', $data['name'], nrx_treatment_price_label( $data ) ? nrx_treatment_price_label( $data ) : '—', get_bloginfo( 'name' ) );
			}
			if ( ! $context['description'] ) {
				$context['description'] = wp_strip_all_tags( $data['tagline'] ?? '' );
			}
			if ( ! $context['image'] ) {
				$context['image'] = nrx_image_url( $data['image'] ?? '' );
			}
		}
	} elseif ( is_archive() ) {
		$context['title']       = get_the_archive_title() . ' · ' . get_bloginfo( 'name' );
		$context['description'] = get_the_archive_description();
		$context['canonical']   = get_pagenum_link( 1 );
		$context['type']        = 'website';
	} elseif ( is_search() ) {
		$context['title']   = sprintf( __( 'Resultados para %s', 'nuestrarx-core' ), get_search_query() );
		$context['noindex'] = true;
	} elseif ( is_404() ) {
		$context['title']   = __( 'Página no encontrada', 'nuestrarx-core' );
		$context['noindex'] = true;
	}

	if ( ! $context['description'] ) {
		$context['description'] = (string) nrx_setting( 'seo_home_description', get_bloginfo( 'description' ) );
	}
	$context['description'] = wp_trim_words( wp_strip_all_tags( $context['description'] ), 34, '…' );

	if ( ! $context['image'] ) {
		$context['image'] = (string) nrx_setting( 'default_og_image', '' );
	}

	return apply_filters( 'nrx_seo_context', $context );
}

/**
 * Override the document title on the front end.
 *
 * @param string $title Title.
 * @return string
 */
function nrx_document_title( $title ) {
	if ( is_admin() || is_feed() ) {
		return $title;
	}
	$context = nrx_seo_context();
	return $context['title'] ? $context['title'] : $title;
}
add_filter( 'pre_get_document_title', 'nrx_document_title', 20 );

/**
 * Print head metadata. Replaces the parent theme's helper (not called by the
 * child header) and WordPress's canonical tag.
 */
function nrx_output_seo() {
	$context = nrx_seo_context();
	$name    = get_bloginfo( 'name' );

	if ( $context['canonical'] ) {
		echo '<link rel="canonical" href="' . esc_url( $context['canonical'] ) . '">' . "\n";
	}
	if ( $context['description'] ) {
		echo '<meta name="description" content="' . esc_attr( $context['description'] ) . '">' . "\n";
	}
	if ( $context['noindex'] || is_search() || is_404() ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	} else {
		echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
	}

	echo '<meta property="og:site_name" content="' . esc_attr( $name ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $context['type'] ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $context['title'] ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $context['description'] ) . '">' . "\n";
	if ( $context['canonical'] ) {
		echo '<meta property="og:url" content="' . esc_url( $context['canonical'] ) . '">' . "\n";
	}
	if ( $context['image'] ) {
		echo '<meta property="og:image" content="' . esc_url( $context['image'] ) . '">' . "\n";
	}
	echo '<meta property="og:locale" content="es_US">' . "\n";
	echo '<meta property="og:locale:alternate" content="en_US">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $context['title'] ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $context['description'] ) . '">' . "\n";
	if ( $context['image'] ) {
		echo '<meta name="twitter:image" content="' . esc_url( $context['image'] ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'nrx_output_seo', 2 );

/**
 * Collect FAQ entries from the sections assigned to the current page.
 *
 * @return array
 */
function nrx_page_faqs() {
	$faqs = array();
	if ( is_singular( 'nrx_treatment' ) ) {
		$data = nrx_treatment_data( get_queried_object_id() );
		foreach ( (array) ( $data['faqs'] ?? array() ) as $faq ) {
			if ( ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
				$faqs[] = array(
					'question'  => wp_strip_all_tags( $faq['question'] ),
					'answer'    => wp_strip_all_tags( $faq['answer'] ),
				);
			}
		}
		return $faqs;
	}

	$context   = is_front_page() ? 'front' : 'page';
	$page_id   = is_front_page() ? 0 : get_queried_object_id();
	$sections  = nrx_get_sections_for( $context, $page_id );
	foreach ( $sections as $section ) {
		$data = nrx_get_section_data( $section );
		if ( empty( $data['type'] ) || 'faq' !== $data['type'] ) {
			continue;
		}
		foreach ( (array) ( $data['items'] ?? array() ) as $faq ) {
			if ( ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
				$faqs[] = array(
					'question' => wp_strip_all_tags( $faq['question'] ),
					'answer'   => wp_strip_all_tags( $faq['answer'] ),
				);
			}
		}
	}
	return $faqs;
}

/**
 * Structured data. Only verified, page-level facts.
 */
function nrx_output_structured_data() {
	$site     = untrailingslashit( home_url() );
	$context  = nrx_seo_context();
	$org_id   = $site . '/#organization';
	$website  = $site . '/#website';
	$graph    = array();

	$same_as = array_filter(
		array(
			(string) nrx_setting( 'instagram_url', '' ),
			(string) nrx_setting( 'facebook_url', '' ),
		)
	);

	$graph[] = array(
		'@type'             => 'Organization',
		'@id'               => $org_id,
		'name'              => nrx_setting( 'brand_name', 'NuestraRx' ),
		'legalName'         => nrx_setting( 'legal_name', 'NuestraRx LLC' ),
		'url'               => $site,
		'description'       => wp_strip_all_tags( (string) nrx_setting( 'seo_home_description', get_bloginfo( 'description' ) ) ),
		'email'             => sanitize_email( (string) nrx_setting( 'contact_email', '' ) ),
		'telephone'         => (string) nrx_setting( 'phone_e164', '' ),
		'availableLanguage' => array( 'Spanish', 'English' ),
		'address'           => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => '5935 NE 2nd Ave',
			'addressLocality' => 'Miami',
			'addressRegion'   => 'FL',
			'postalCode'      => '33137',
			'addressCountry'  => 'US',
		),
		'sameAs'            => array_values( $same_as ),
	);

	$graph[] = array(
		'@type'      => 'WebSite',
		'@id'        => $website,
		'url'        => $site,
		'name'       => nrx_setting( 'brand_name', 'NuestraRx' ),
		'publisher'  => array( '@id' => $org_id ),
		'inLanguage' => 'es-US',
	);

	if ( ! empty( $context['canonical'] ) ) {
		$webpage = array(
			'@type'      => is_singular( 'nrx_treatment' ) ? 'MedicalWebPage' : 'WebPage',
			'@id'        => $context['canonical'] . '#webpage',
			'url'        => $context['canonical'],
			'name'       => $context['title'],
			'description' => $context['description'],
			'inLanguage' => 'es-US',
			'isPartOf'   => array( '@id' => $website ),
			'about'      => array( '@id' => $org_id ),
		);
		if ( is_singular( 'nrx_treatment' ) ) {
			$data = nrx_treatment_data( get_queried_object_id() );
			$webpage['audience'] = array(
				'@type'       => 'Patient',
				'geographicArea' => array(
					'@type' => 'Country',
					'name'  => 'United States',
				),
			);
			$webpage['lastReviewed'] = get_the_modified_date( 'c' );
			$webpage['significantLink'] = $data['url'];
		}
		$graph[] = $webpage;

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $context['canonical'] . '#breadcrumb',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Inicio',
					'item'     => $site . '/',
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $context['title'],
					'item'     => $context['canonical'],
				),
			),
		);
	}

	$faqs = nrx_page_faqs();
	if ( $faqs ) {
		$graph[] = array(
			'@type'      => 'FAQPage',
			'@id'        => ( $context['canonical'] ? $context['canonical'] : $site ) . '#faq',
			'mainEntity' => array_map(
				static function ( $faq ) {
					return array(
						'@type'          => 'Question',
						'name'           => $faq['question'],
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => $faq['answer'],
						),
					);
				},
				array_slice( $faqs, 0, 12 )
			),
		);
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'nrx_output_structured_data', 3 );

/* -------------------------------------------------------------------------
 * Meta box
 * ---------------------------------------------------------------------- */

/**
 * Register the SEO meta box.
 */
function nrx_register_seo_meta_box() {
	add_meta_box( 'nrx_seo', __( 'SEO y redes sociales', 'nuestrarx-core' ), 'nrx_seo_meta_box', array( 'page', 'post', 'nrx_treatment' ), 'side', 'default' );
}
add_action( 'add_meta_boxes', 'nrx_register_seo_meta_box' );

/**
 * Render the SEO meta box.
 *
 * @param WP_Post $post Post.
 */
function nrx_seo_meta_box( $post ) {
	wp_nonce_field( 'nrx_save_seo', 'nrx_seo_nonce' );
	$title = get_post_meta( $post->ID, '_nrx_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_nrx_seo_description', true );
	$no    = get_post_meta( $post->ID, '_nrx_seo_noindex', true );
	$img   = get_post_meta( $post->ID, '_nrx_seo_og_image', true );
	?>
	<div class="nrx-admin">
		<p><label><strong><?php esc_html_e( 'Título SEO', 'nuestrarx-core' ); ?></strong><br>
			<input type="text" name="nrx_seo_title" value="<?php echo esc_attr( $title ); ?>" maxlength="120" style="width:100%"></label></p>
		<p><label><strong><?php esc_html_e( 'Meta descripción', 'nuestrarx-core' ); ?></strong><br>
			<textarea name="nrx_seo_description" rows="3" maxlength="320" style="width:100%"><?php echo esc_textarea( $desc ); ?></textarea></label></p>
		<p><label><strong><?php esc_html_e( 'Imagen OG (URL)', 'nuestrarx-core' ); ?></strong><br>
			<input type="url" name="nrx_seo_og_image" value="<?php echo esc_attr( $img ); ?>" style="width:100%"></label></p>
		<p><label><input type="checkbox" name="nrx_seo_noindex" value="1" <?php checked( $no, '1' ); ?>> <?php esc_html_e( 'No indexar esta página', 'nuestrarx-core' ); ?></label></p>
		<p class="nrx-field-help"><?php esc_html_e( 'Las páginas de evaluación y agradecimiento se marcan como noindex automáticamente.', 'nuestrarx-core' ); ?></p>
	</div>
	<?php
}

/**
 * Save SEO meta.
 *
 * @param int $post_id Post ID.
 */
function nrx_save_seo_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['nrx_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nrx_seo_nonce'] ) ), 'nrx_save_seo' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_nrx_seo_title', isset( $_POST['nrx_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['nrx_seo_title'] ) ) : '' );
	update_post_meta( $post_id, '_nrx_seo_description', isset( $_POST['nrx_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nrx_seo_description'] ) ) : '' );
	update_post_meta( $post_id, '_nrx_seo_og_image', isset( $_POST['nrx_seo_og_image'] ) ? esc_url_raw( wp_unslash( $_POST['nrx_seo_og_image'] ) ) : '' );
	update_post_meta( $post_id, '_nrx_seo_noindex', ! empty( $_POST['nrx_seo_noindex'] ) ? '1' : '' );
}
add_action( 'save_post', 'nrx_save_seo_meta' );

/* -------------------------------------------------------------------------
 * Legacy redirects
 * ---------------------------------------------------------------------- */

/**
 * Redirect legacy URLs from the reference site to their preserved routes.
 */
function nrx_legacy_redirects() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/', PHP_URL_PATH );
	$path = trim( (string) $path, '/' );

	$map = array(
		''                          => '',
		'inicio'                    => '',
		'evaluacion.html'           => 'evaluacion',
		'politica-de-privacidad.html' => 'politica-de-privacidad',
		'aviso-hipaa.html'          => 'aviso-hipaa',
		'terminos-de-servicio.html' => 'terminos-de-servicio',
		'politica-de-reembolso.html' => 'politica-de-reembolso',
		'informacion-de-seguridad.html' => 'informacion-de-seguridad',
		'cumplimiento.html'         => 'cumplimiento',
		'nosotros.html'             => 'nosotros',
		'pago-semanal.html'         => 'pago-semanal',
	);

	if ( ! isset( $map[ $path ] ) || '' === $map[ $path ] ) {
		return;
	}
	if ( is_front_page() ) {
		return;
	}
	$target = home_url( '/' . $map[ $path ] . '/' );
	if ( untrailingslashit( $target ) === untrailingslashit( home_url( '/' . $path ) ) ) {
		return;
	}
	wp_safe_redirect( $target, 301, 'NuestraRx' );
	exit;
}
add_action( 'template_redirect', 'nrx_legacy_redirects', 1 );
