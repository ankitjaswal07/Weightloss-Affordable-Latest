<?php
/**
 * Technical SEO output.
 *
 * Provides editable meta titles/descriptions, canonicals, Open Graph data and
 * conservative structured data. It steps aside when a dedicated SEO plugin is
 * active, so nothing is duplicated.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is another SEO plugin handling meta output?
 *
 * @return bool
 */
function wla_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Meta description for the current request.
 *
 * @return string
 */
function wla_current_meta_description() {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$custom  = get_post_meta( $post_id, '_wla_seo_description', true );
		if ( ! $custom && 'nrx_treatment' === get_post_type( $post_id ) ) {
			$custom = get_post_meta( $post_id, '_wla_seo_description', true );
		}
		if ( $custom ) {
			return wp_strip_all_tags( $custom );
		}
		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 30 );
		}
		return wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 30 );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_trim_words( wp_strip_all_tags( $desc ), 30 );
		}
	}
	return get_bloginfo( 'description' );
}

/**
 * SEO title for the current request.
 *
 * @return string
 */
function wla_current_seo_title() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_wla_seo_title', true );
		if ( $custom ) {
			return $custom;
		}
	}
	return '';
}

/**
 * Print head meta tags.
 */
function wla_output_seo_meta() {
	if ( wla_seo_plugin_active() ) {
		return;
	}

	$description = wla_current_meta_description();
	$canonical   = wla_current_canonical();
	$og_image    = '';

	if ( is_singular() ) {
		$custom_image = (int) get_post_meta( get_queried_object_id(), '_wla_og_image', true );
		if ( $custom_image ) {
			$og_image = wp_get_attachment_image_url( $custom_image, 'wla-hero' );
		} elseif ( has_post_thumbnail() ) {
			$og_image = get_the_post_thumbnail_url( get_queried_object_id(), 'wla-hero' );
		}
	}

	echo "\n<!-- WeightLossAffordable SEO -->\n";
	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	}
	if ( $og_image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $og_image ) );
		printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
	}

	if ( ! get_option( 'blog_public' ) || is_search() || is_404() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}

/**
 * Canonical URL for the current request.
 *
 * @return string
 */
function wla_current_canonical() {
	if ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
		if ( $url ) {
			return $url;
		}
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}
	if ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( get_post_type() );
		if ( $link ) {
			return $link;
		}
	}
	return '';
}

/**
 * Override the document title with an editable SEO title.
 *
 * @param array $parts Title parts.
 * @return array
 */
function wla_filter_document_title( $parts ) {
	$custom = wla_current_seo_title();
	if ( $custom ) {
		$parts['title'] = $custom;
		unset( $parts['tagline'] );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'wla_filter_document_title' );

/**
 * Structured data: organisation + breadcrumbs only.
 *
 * We deliberately avoid medical/clinical schema (MedicalWebPage, Review,
 * AggregateRating) unless the business can substantiate it.
 */
function wla_output_structured_data() {
	if ( wla_seo_plugin_active() ) {
		return;
	}

	$org = array(
		'@context' => 'https://schema.org',
		'@type'    => 'MedicalBusiness',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);
	$email = wla_get_settings( 'contact_email' );
	if ( $email ) {
		$org['email'] = $email;
	}
	$phone = wla_get_settings( 'contact_phone' );
	if ( $phone ) {
		$org['telephone'] = $phone;
	}

	$graph = array( $org );

	if ( is_front_page() ) {
		$graph[] = array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		);
	}

	if ( is_singular() ) {
		$crumbs = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Home', 'weightloss-affordable-core' ),
				'item'     => home_url( '/' ),
			),
			array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			),
		);
		$graph[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumbs,
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'wla_output_seo_meta', 2 );
add_action( 'wp_head', 'wla_output_structured_data', 3 );

/**
 * Remove the core canonical redirect conflict for paginated archives.
 *
 * @param string $redirect Redirect URL.
 * @return string
 */
function wla_disable_canonical_redirect( $redirect ) {
	return wla_seo_plugin_active() ? $redirect : $redirect;
}
add_filter( 'redirect_canonical', 'wla_disable_canonical_redirect' );
