<?php
/**
 * Treatments: dynamic product records, pricing plans, preserved URLs and an
 * optional WooCommerce price adapter.
 *
 * The reference site sells two compounded treatments (semaglutida and
 * tirzepatida) with monthly, quarterly and six-month plans, plus a separate
 * weekly payment page. Those records are seeded by the installer and remain
 * fully editable; nothing is hardcoded in templates.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment field schema.
 *
 * @return array
 */
function nrx_treatment_fields() {
	$sub = nrx_section_subfields();
	return array(
		nrx_bi_f( 'eyebrow', __( 'Antetítulo (ej. GLP-1 · Agonista del Receptor)', 'nuestrarx-core' ) ),
		nrx_bi_f( 'badge', __( 'Etiqueta corta (ej. GLP-1)', 'nuestrarx-core' ) ),
		nrx_bi_f( 'title_accent', __( 'Palabra destacada del título', 'nuestrarx-core' ) ),
		nrx_bi_f( 'tagline', __( 'Descripción corta', 'nuestrarx-core' ), 'textarea' ),
		nrx_bi_f( 'description', __( 'Descripción completa', 'nuestrarx-core' ), 'richtext' ),
		nrx_f( 'image', __( 'Imagen principal', 'nuestrarx-core' ), 'image' ),
		nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
		nrx_f( 'gallery', __( 'Galería adicional', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ), nrx_bi_f( 'alt', __( 'Texto alternativo', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Imagen', 'nuestrarx-core' ) ) ),
		nrx_f(
			'plans',
			__( 'Planes y precios', 'nuestrarx-core' ),
			'repeater',
			array(
				'fields'    => array(
					nrx_bi_f( 'name', __( 'Nombre del plan (ej. Plan Mensual)', 'nuestrarx-core' ) ),
					nrx_bi_f( 'doses', __( 'Dosis incluidas', 'nuestrarx-core' ) ),
					nrx_f( 'months', __( 'Meses', 'nuestrarx-core' ), 'number', array( 'default' => 1, 'min' => 1 ) ),
					nrx_f( 'total', __( 'Total del plan ($)', 'nuestrarx-core' ), 'number', array( 'min' => 0, 'step' => '0.01' ) ),
					nrx_f( 'monthly', __( 'Precio por mes ($)', 'nuestrarx-core' ), 'number', array( 'min' => 0, 'step' => '0.01' ) ),
					nrx_f( 'compare_monthly', __( 'Precio anterior por mes ($)', 'nuestrarx-core' ), 'number', array( 'min' => 0, 'step' => '0.01' ) ),
					nrx_bi_f( 'badge', __( 'Insignia (ej. Más popular)', 'nuestrarx-core' ) ),
					nrx_f( 'popular', __( 'Marcar como más popular', 'nuestrarx-core' ), 'checkbox' ),
					nrx_bi_f( 'note', __( 'Nota del plan', 'nuestrarx-core' ) ),
				),
				'row_label' => __( 'Plan', 'nuestrarx-core' ),
			)
		),
		nrx_f( 'weekly_price', __( 'Precio semanal ($)', 'nuestrarx-core' ), 'number', array( 'min' => 0, 'step' => '0.01' ) ),
		nrx_f( 'weekly_compare', __( 'Precio semanal anterior ($)', 'nuestrarx-core' ), 'number', array( 'min' => 0, 'step' => '0.01' ) ),
		nrx_f( 'bullets', __( 'Características incluidas', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Característica', 'nuestrarx-core' ) ) ),
		nrx_bi_f( 'mechanism_title', __( 'Título del mecanismo', 'nuestrarx-core' ) ),
		nrx_bi_f( 'mechanism_body', __( 'Texto del mecanismo', 'nuestrarx-core' ), 'textarea' ),
		nrx_f( 'mechanisms', __( 'Puntos del mecanismo', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['bullet'], 'row_label' => __( 'Punto', 'nuestrarx-core' ) ) ),
		nrx_bi_f( 'ideal_title', __( 'Título "Ideal si…"', 'nuestrarx-core' ) ),
		nrx_f( 'ideal_items', __( 'Ideal si…', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Criterio', 'nuestrarx-core' ) ) ),
		nrx_bi_f( 'avoid_title', __( 'Título "No recomendado si…"', 'nuestrarx-core' ) ),
		nrx_f( 'avoid_items', __( 'No recomendado si…', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Criterio', 'nuestrarx-core' ) ) ),
		nrx_f( 'faqs', __( 'Preguntas frecuentes', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['faq'], 'row_label' => __( 'Pregunta', 'nuestrarx-core' ) ) ),
		nrx_bi_f( 'process_title', __( 'Treatment process heading', 'nuestrarx-core' ) ),
		nrx_bi_f( 'process_body', __( 'Treatment process introduction', 'nuestrarx-core' ), 'textarea' ),
		nrx_f( 'process_items', __( 'Treatment process steps', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['bullet'], 'row_label' => __( 'Step', 'nuestrarx-core' ) ) ),
		nrx_bi_f( 'recovery_title', __( 'Safety and aftercare heading', 'nuestrarx-core' ) ),
		nrx_bi_f( 'recovery_body', __( 'Safety and aftercare copy', 'nuestrarx-core' ), 'textarea' ),
		nrx_f( 'related_slugs', __( 'Related treatment slugs (comma-separated)', 'nuestrarx-core' ), 'text' ),
		nrx_bi_f( 'disclaimer', __( 'Aviso legal del tratamiento', 'nuestrarx-core' ), 'textarea' ),
		nrx_f( 'quiz_key', __( 'Clave en la evaluación (semaglutida / tirzepatida)', 'nuestrarx-core' ), 'text' ),
		nrx_f( 'woo_product_id', __( 'ID de producto WooCommerce (opcional)', 'nuestrarx-core' ), 'number', array( 'min' => 0 ) ),
		nrx_f( 'require_approval', __( 'Ocultar hasta aprobación de cumplimiento', 'nuestrarx-core' ), 'checkbox' ),
	);
}

/**
 * Register treatment meta box.
 */
function nrx_register_treatment_meta_boxes() {
	add_meta_box( 'nrx_treatment_fields', __( 'Ficha del tratamiento', 'nuestrarx-core' ), 'nrx_treatment_meta_box', 'nrx_treatment', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nrx_register_treatment_meta_boxes' );

/**
 * Render the treatment fields.
 *
 * @param WP_Post $post Treatment.
 */
function nrx_treatment_meta_box( $post ) {
	wp_nonce_field( 'nrx_save_treatment', 'nrx_treatment_nonce' );
	$fields = nrx_treatment_fields();
	$values = nrx_get_meta_values( $post->ID, $fields );
	echo '<div class="nrx-admin nrx-treatment-admin">';
	echo '<p class="nrx-field-help">' . esc_html__( 'Los precios se muestran automáticamente en la portada, las tarjetas y el comparador. Los planes con precio mensual más bajo definen el "desde".', 'nuestrarx-core' ) . '</p>';
	nrx_render_fields( $fields, $values, 'nrx_treatment' );
	echo '</div>';
}

/**
 * Save treatment fields.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function nrx_save_treatment( $post_id, $post ) {
	if ( ! nrx_can_save_meta( $post_id, 'nrx_save_treatment', 'nrx_treatment_nonce' ) ) {
		return;
	}
	if ( 'nrx_treatment' !== $post->post_type ) {
		return;
	}
	$fields = nrx_treatment_fields();
	$raw    = isset( $_POST['nrx_treatment'] ) && is_array( $_POST['nrx_treatment'] ) ? wp_unslash( $_POST['nrx_treatment'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$values = nrx_sanitize_fields( $fields, $raw );
	nrx_save_meta_values( $post_id, $fields, $values );
	delete_transient( 'nrx_treatment_routes' );
	if ( 'publish' === $post->post_status ) {
		set_transient( 'nrx_flush_routes', 1, 60 );
	}
}
add_action( 'save_post', 'nrx_save_treatment', 10, 2 );

/**
 * Rebuild rewrite rules once when a treatment URL changed.
 */
function nrx_maybe_flush_routes() {
	if ( get_transient( 'nrx_flush_routes' ) ) {
		delete_transient( 'nrx_flush_routes' );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'nrx_maybe_flush_routes', 99 );

/**
 * Published treatment routes, cached.
 *
 * @return array<string,int>
 */
function nrx_treatment_routes() {
	$cached = get_transient( 'nrx_treatment_routes' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$routes = array();
	$posts  = get_posts(
		array(
			'post_type'      => 'nrx_treatment',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $id ) {
		$slug = get_post_field( 'post_name', $id );
		if ( $slug ) {
			$routes[ $slug ] = (int) $id;
		}
	}
	set_transient( 'nrx_treatment_routes', $routes, HOUR_IN_SECONDS );
	return $routes;
}

/**
 * Preserve the live site URLs: /semaglutida and /tirzepatida.
 */
function nrx_register_treatment_rewrites() {
	foreach ( nrx_treatment_routes() as $slug => $id ) {
		add_rewrite_rule( '^' . preg_quote( $slug, '/' ) . '/?$', 'index.php?nrx_treatment_id=' . (int) $id, 'top' );
	}
}
add_action( 'init', 'nrx_register_treatment_rewrites', 21 );

/**
 * Register the custom query var used by the treatment routes.
 *
 * @param array $vars Public query vars.
 * @return array
 */
function nrx_treatment_query_vars( $vars ) {
	$vars[] = 'nrx_treatment_id';
	return $vars;
}
add_filter( 'query_vars', 'nrx_treatment_query_vars' );

/**
 * Resolve the custom rule into a normal single-post query.
 *
 * @param array $vars Query vars.
 * @return array
 */
function nrx_treatment_request( $vars ) {
	if ( ! empty( $vars['nrx_treatment_id'] ) ) {
		$id = absint( $vars['nrx_treatment_id'] );
		unset( $vars['nrx_treatment_id'], $vars['error'], $vars['attachment'], $vars['name'], $vars['pagename'] );
		$vars['p']              = $id;
		$vars['post_type']      = 'nrx_treatment';
		$vars['post_status']    = 'publish';
		$vars['posts_per_page'] = 1;
	}
	return $vars;
}
add_filter( 'request', 'nrx_treatment_request' );

/**
 * Build the canonical treatment permalink.
 *
 * @param string  $permalink Permalink.
 * @param WP_Post $post      Post.
 * @return string
 */
function nrx_treatment_permalink( $permalink, $post ) {
	if ( ! $post instanceof WP_Post || 'nrx_treatment' !== $post->post_type ) {
		return $permalink;
	}
	if ( 'publish' !== $post->post_status ) {
		return $permalink;
	}
	$slug = $post->post_name;
	return $slug ? home_url( '/' . $slug . '/' ) : $permalink;
}
add_filter( 'post_type_link', 'nrx_treatment_permalink', 10, 2 );

/**
 * Prevent WordPress from redirecting a pretty treatment URL to ?p=.
 *
 * @param string $redirect Redirect URL.
 * @return string|false
 */
function nrx_treatment_disable_canonical_redirect( $redirect ) {
	if ( is_singular( 'nrx_treatment' ) ) {
		return false;
	}
	return $redirect;
}
add_filter( 'redirect_canonical', 'nrx_treatment_disable_canonical_redirect' );

/**
 * Flush routes on activation / theme switch.
 */
function nrx_treatment_activation_routes() {
	delete_transient( 'nrx_treatment_routes' );
	flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'nrx_treatment_activation_routes' );

/**
 * Normalize plan values from stored meta.
 *
 * @param array $plans Stored plans.
 * @return array
 */
function nrx_normalize_plans( $plans ) {
	$out = array();
	foreach ( (array) $plans as $plan ) {
		if ( ! is_array( $plan ) ) {
			continue;
		}
		$monthly = isset( $plan['monthly'] ) && '' !== $plan['monthly'] ? (float) $plan['monthly'] : 0;
		$months  = isset( $plan['months'] ) && '' !== $plan['months'] ? max( 1, (int) $plan['months'] ) : 1;
		$total   = isset( $plan['total'] ) && '' !== $plan['total'] ? (float) $plan['total'] : $monthly * $months;
		$compare = isset( $plan['compare_monthly'] ) && '' !== $plan['compare_monthly'] ? (float) $plan['compare_monthly'] : 0;
		$out[]   = array_merge(
			$plan,
			array(
				'months'          => $months,
				'monthly'         => $monthly,
				'total'           => $total,
				'compare_monthly' => $compare,
				'popular'         => ! empty( $plan['popular'] ),
				'savings_monthly' => $compare > $monthly ? $compare - $monthly : 0,
				'is_subscription' => 1 === $months,
			)
		);
	}
	return $out;
}

/**
 * Lowest monthly price and its comparator.
 *
 * @param array $plans Normalized plans.
 * @return array{from:float,compare:float,plan:array}
 */
function nrx_lowest_plan( $plans ) {
	$lowest = null;
	foreach ( (array) $plans as $plan ) {
		if ( $plan['monthly'] <= 0 ) {
			continue;
		}
		if ( null === $lowest || $plan['monthly'] < $lowest['monthly'] ) {
			$lowest = $plan;
		}
	}
	if ( null === $lowest ) {
		return array( 'from' => 0, 'compare' => 0, 'plan' => array() );
	}
	return array( 'from' => $lowest['monthly'], 'compare' => $lowest['compare_monthly'], 'plan' => $lowest );
}

/**
 * Assemble everything a template needs for one treatment.
 *
 * @param int|WP_Post $treatment Treatment.
 * @return array
 */
function nrx_treatment_data( $treatment ) {
	$post = get_post( $treatment );
	if ( ! $post || 'nrx_treatment' !== $post->post_type ) {
		return array();
	}
	$fields = nrx_treatment_fields();
	$values = nrx_get_meta_values( $post->ID, $fields );
	$plans  = nrx_normalize_plans( $values['plans'] ?? array() );
	$lowest = nrx_lowest_plan( $plans );
	$data   = array_merge(
		$values,
		array(
			'id'          => $post->ID,
			'name'        => get_the_title( $post ),
			'slug'        => $post->post_name,
			'url'         => get_permalink( $post ),
			'plans'       => $plans,
			'from_price'  => $lowest['from'],
			'compare_price' => $lowest['compare'],
			'lowest_plan' => $lowest['plan'],
			'weekly_price' => isset( $values['weekly_price'] ) ? (float) $values['weekly_price'] : 0,
			'weekly_compare' => isset( $values['weekly_compare'] ) ? (float) $values['weekly_compare'] : 0,
			'image'       => $values['image'] ?? '',
			'image_alt'   => $values['image_alt'] ?? '',
			'faqs'        => is_array( $values['faqs'] ?? null ) ? $values['faqs'] : array(),
			'quiz_key'    => $values['quiz_key'] ?? '',
			'woo_product_id' => (int) ( $values['woo_product_id'] ?? 0 ),
			'require_approval' => ! empty( $values['require_approval'] ),
			'source'      => 'internal',
		)
	);

	// Optional WooCommerce adapter: keep WooCommerce as the price source when configured.
	if ( 'woocommerce' === nrx_setting( 'product_source', 'internal' ) && $data['woo_product_id'] && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $data['woo_product_id'] );
		if ( $product && $product->is_purchasable() ) {
			$price   = (float) $product->get_price();
			$regular = (float) $product->get_regular_price();
			$data['from_price']    = $price;
			$data['compare_price'] = $regular > $price ? $regular : 0;
			$data['url']           = get_permalink( $product->get_id() );
			$data['source']        = 'woocommerce';
			$data['add_to_cart_url'] = $product->add_to_cart_url();
		}
	}

	if ( function_exists( 'nrx_prefer_english' ) ) {
		$data = nrx_prefer_english( $data );
	}
	return $data;
}

/**
 * Format a price display for a treatment ("$139").
 *
 * @param array $data Treatment data.
 * @return string
 */
function nrx_treatment_price_label( $data ) {
	if ( empty( $data['from_price'] ) ) {
		return '';
	}
	return nrx_price( $data['from_price'] );
}

/**
 * Plans used in the treatment page, keyed by months.
 *
 * @param array $data Treatment data.
 * @return array
 */
function nrx_treatment_plans( $data ) {
	return is_array( $data['plans'] ?? null ) ? $data['plans'] : array();
}

/* -------------------------------------------------------------------------
 * Admin list
 * ---------------------------------------------------------------------- */

/**
 * Treatment admin columns.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function nrx_treatment_columns( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['nrx_from']   = __( 'Desde / mes', 'nuestrarx-core' );
			$out['nrx_plans']  = __( 'Planes', 'nuestrarx-core' );
			$out['nrx_weekly'] = __( 'Semanal', 'nuestrarx-core' );
			$out['nrx_url']    = __( 'URL pública', 'nuestrarx-core' );
		}
	}
	return $out;
}
add_filter( 'manage_nrx_treatment_posts_columns', 'nrx_treatment_columns' );

/**
 * Render treatment columns.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function nrx_treatment_column_content( $column, $post_id ) {
	$data = nrx_treatment_data( $post_id );
	if ( 'nrx_from' === $column ) {
		echo $data['from_price'] ? esc_html( nrx_price( $data['from_price'] ) ) : '—';
	}
	if ( 'nrx_plans' === $column ) {
		echo esc_html( (string) count( $data['plans'] ) );
	}
	if ( 'nrx_weekly' === $column ) {
		echo $data['weekly_price'] ? esc_html( nrx_price( $data['weekly_price'], true ) ) : '—';
	}
	if ( 'nrx_url' === $column ) {
		$url = get_permalink( $post_id );
		echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $url, PHP_URL_PATH ) ) . '</a>';
	}
}
add_action( 'manage_nrx_treatment_posts_custom_column', 'nrx_treatment_column_content', 10, 2 );

/**
 * Warn when a page shadows a treatment URL.
 */
function nrx_treatment_slug_conflict_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'nrx_treatment' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	$post = get_post();
	if ( ! $post || 'publish' !== $post->post_status ) {
		return;
	}
	$conflict = get_page_by_path( $post->post_name );
	if ( $conflict ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Ya existe una página con este slug. La página tiene prioridad sobre el tratamiento en la misma URL. Renombra uno de los dos.', 'nuestrarx-core' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'nrx_treatment_slug_conflict_notice' );
