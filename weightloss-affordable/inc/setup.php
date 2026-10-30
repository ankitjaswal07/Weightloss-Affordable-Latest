<?php
/** Theme setup and bilingual navigation helpers. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_theme_setup() {
	load_theme_textdomain( 'weightloss-affordable', WLA_THEME_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 88, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_image_size( 'wla-card', 760, 560, true );
	add_image_size( 'wla-hero', 1280, 980, true );
	register_nav_menus( array(
		'primary'         => esc_html__( 'Primary navigation', 'weightloss-affordable' ),
		'footer'          => esc_html__( 'Footer navigation', 'weightloss-affordable' ),
		'pages_dropdown'  => esc_html__( 'Header Pages dropdown', 'weightloss-affordable' ),
		'footer_explore'  => esc_html__( 'Footer Explore links', 'weightloss-affordable' ),
	) );
}
add_action( 'after_setup_theme', 'wla_theme_setup' );

function wla_content_width() { $GLOBALS['content_width'] = apply_filters( 'wla_content_width', 1340 ); }
add_action( 'after_setup_theme', 'wla_content_width', 0 );

function wla_current_language_slug() {
	if ( function_exists( 'pll_current_language' ) ) { return pll_current_language( 'slug' ); }
	$demo_key = get_post_meta( get_queried_object_id(), '_wla_demo_key', true );
	if ( is_string( $demo_key ) && '-es' === substr( $demo_key, -3 ) ) { return 'es'; }
	return ( is_page( 'inicio' ) || is_page( 'sobre-nosotros' ) || is_page( 'contacto' ) || is_page( 'recursos' ) ) ? 'es' : 'en';
}
function wla_is_spanish_language() { return 'es' === wla_current_language_slug(); }
function wla_copy( $english, $spanish ) { return wla_is_spanish_language() ? $spanish : __( $english, 'weightloss-affordable' ); }
function wla_home_url() {
	if ( function_exists( 'pll_home_url' ) ) { return pll_home_url( wla_current_language_slug() ); }
	return wla_is_spanish_language() ? home_url( '/inicio/' ) : home_url( '/' );
}
function wla_page_url( $english_slug, $spanish_slug = '' ) {
	$slug = wla_is_spanish_language() && $spanish_slug ? $spanish_slug : $english_slug;
	$page = get_page_by_path( $slug );
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translation = pll_get_post( $page->ID, wla_current_language_slug() );
		if ( $translation ) { return get_permalink( $translation ); }
	}
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}
function wla_language_switcher() {
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		if ( is_array( $languages ) ) {
			foreach ( $languages as $language ) {
				if ( empty( $language['current_lang'] ) && ! empty( $language['url'] ) ) {
					$current = wla_current_language_slug();
					$aria = 'en' === $current ? __( 'Read in Spanish', 'weightloss-affordable' ) : __( 'Leer en inglés', 'weightloss-affordable' );
					printf( '<a class="wla-language-switch" href="%1$s" lang="%2$s" hreflang="%2$s" aria-label="%3$s">%4$s</a>', esc_url( $language['url'] ), esc_attr( $language['locale'] ?? $language['slug'] ), esc_attr( $aria ), esc_html( strtoupper( $language['slug'] ) ) );
					return;
				}
			}
		}
	}
	$is_spanish = wla_is_spanish_language();
	$target = $is_spanish ? 'en' : 'es';
	$url = $is_spanish ? home_url( '/' ) : home_url( '/inicio/' );
	$aria = $is_spanish ? __( 'Leer en inglés', 'weightloss-affordable' ) : __( 'Read in Spanish', 'weightloss-affordable' );
	printf( '<a class="wla-language-switch" href="%1$s" lang="%2$s" aria-label="%3$s">%4$s</a>', esc_url( $url ), esc_attr( $target ), esc_attr( $aria ), esc_html( strtoupper( $target ) ) );
}
/**
 * Primary navigation as real page routes (shared by header + fallback menu).
 * Labels come from the demo snapshot so the live EN/ES translator can rewrite them.
 */
function wla_nav_items() {
	$snap = function_exists( 'wla_get_demo_snapshot' ) ? wla_get_demo_snapshot() : array();
	$d    = $snap['home'] ?? array();
	$nav  = $d['nav'] ?? array();

	return array(
		array( 'slug' => 'about', 'path' => '/about', 'label' => $nav[0] ?? 'About' ),
		array( 'slug' => 'weight-loss-services', 'path' => '/weight-loss-services', 'label' => $nav[1] ?? 'Treatments' ),
		array( 'slug' => 'how-it-works', 'path' => '/how-it-works', 'label' => $nav[2] ?? 'How it works' ),
		array( 'slug' => 'consultation-eligibility', 'path' => '/consultation-eligibility', 'label' => $nav[3] ?? 'Get Quote' ),
		array( 'slug' => 'frequently-asked-questions', 'path' => '/frequently-asked-questions', 'label' => $nav[4] ?? 'FAQs' ),
	);
}

function wla_menu_fallback() {
	$spanish = wla_is_spanish_language();
	echo '<ul class="wla-menu-fallback">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $spanish ? 'Inicio' : 'Home' ) . '</a></li>';
	foreach ( wla_nav_items() as $item ) {
		echo '<li><a href="' . wla_resolve_route_url( $item['path'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '<li><a href="' . wla_resolve_route_url( '/contact' ) . '">' . esc_html( $spanish ? 'Contacto' : 'Contact' ) . '</a></li>';
	echo '</ul>';
}

/**
 * Per-page SEO meta output: description, keywords, canonical, OpenGraph,
 * Twitter card, and robots directives. Reads from post meta when present
 * (editable via the "WLA SEO" meta box) and falls back to demo snapshot data.
 */
function wla_output_seo_meta() {
	$site_url = untrailingslashit( home_url() );
	$is_home  = is_front_page();
	$slug     = $is_home ? 'home' : get_post_field( 'post_name', get_queried_object_id() );

	// Pull demo snapshot defaults
	$snap = function_exists( 'wla_get_demo_snapshot' ) ? wla_get_demo_snapshot() : array();
	$defaults = array(
		'title'       => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'keywords'    => '',
		'noindex'     => false,
		'og_image'    => $site_url . '/wp-content/themes/weightloss-affordable/assets/images/hero-care.jpg',
	);

	// Per-post overrides via meta keys (editable in the WLA SEO meta box)
	$meta_title = get_post_meta( get_queried_object_id(), '_wla_seo_title', true );
	$meta_desc  = get_post_meta( get_queried_object_id(), '_wla_seo_description', true );
	$meta_kw    = get_post_meta( get_queried_object_id(), '_wla_seo_keywords', true );
	$meta_noidx = get_post_meta( get_queried_object_id(), '_wla_seo_noindex', true );
	$meta_ogimg = get_post_meta( get_queried_object_id(), '_wla_seo_og_image', true );

	$title = $meta_title ? $meta_title : $defaults['title'];
	$desc  = $meta_desc ? $meta_desc : $defaults['description'];
	$kw    = $meta_kw ? $meta_kw : $defaults['keywords'];
	$noidx = $meta_noidx ? true : $defaults['noindex'];
	$ogimg = $meta_ogimg ? $meta_ogimg : $defaults['og_image'];

	$canonical = $is_home ? $site_url . '/' : get_permalink();

	// Title is handled by add_theme_support('title-tag'); we only output the rest.
	echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	if ( $kw ) {
		echo '<meta name="keywords" content="' . esc_attr( $kw ) . '" />' . "\n";
	}
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";

	if ( $noidx ) {
		echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
	} else {
		echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1" />' . "\n";
	}

	// OpenGraph
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . ( is_single() ? 'article' : 'website' ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical ) . '" />' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $ogimg ) . '" />' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr( wla_is_spanish_language() ? 'es_ES' : 'en_US' ) . '" />' . "\n";

	// Twitter card
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $ogimg ) . '" />' . "\n";
}

/**
 * JSON-LD structured data: Organization, WebSite, WebPage, BreadcrumbList.
 * Only outputs data that is accurate — no fabricated credentials or claims.
 */
function wla_output_structured_data() {
	$site_url = untrailingslashit( home_url() );
	$is_home  = is_front_page();
	$slug     = $is_home ? 'home' : get_post_field( 'post_name', get_queried_object_id() );
	$title    = get_the_title();
	$desc     = get_post_meta( get_queried_object_id(), '_wla_seo_description', true ) ?: get_bloginfo( 'description' );
	$canonical = $is_home ? $site_url . '/' : get_permalink();
	$locale    = wla_is_spanish_language() ? 'es' : 'en';

	$org = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'@id'      => $site_url . '/#organization',
		'name'     => get_bloginfo( 'name' ),
		'url'      => $site_url,
		'description' => get_bloginfo( 'description' ),
		'availableLanguage' => array( 'English', 'Spanish' ),
	);
	$website = array(
		'@context' => 'https://schema.org',
		'@type'    => 'WebSite',
		'@id'      => $site_url . '/#website',
		'url'      => $site_url,
		'name'     => get_bloginfo( 'name' ),
		'inLanguage' => $locale,
		'publisher' => array( '@id' => $site_url . '/#organization' ),
	);
	$webpage = array(
		'@context' => 'https://schema.org',
		'@type'    => is_front_page() ? 'WebPage' : 'WebPage',
		'@id'      => $canonical . '#webpage',
		'url'      => $canonical,
		'name'     => $title,
		'description' => $desc,
		'inLanguage' => $locale,
		'isPartOf' => array( '@id' => $site_url . '/#website' ),
	);
	$breadcrumb = array(
		'@context' => 'https://schema.org',
		'@type'    => 'BreadcrumbList',
		'itemListElement' => array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => wla_is_spanish_language() ? 'Inicio' : 'Home', 'item' => $site_url ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $canonical ),
		),
	);

	foreach ( array( $org, $website, $webpage, $breadcrumb ) as $data ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}
}

/**
 * Register the WLA SEO meta box on pages so every page has editable
 * title, description, keywords, noindex, and OG image fields in wp-admin.
 */
function wla_register_seo_meta_box() {
	add_meta_box(
		'wla_seo',
		wla_is_spanish_language() ? 'WLA SEO (Metadatos)' : 'WLA SEO (Metadata)',
		'wla_render_seo_meta_box',
		array( 'page', 'post' ),
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'wla_register_seo_meta_box' );

function wla_render_seo_meta_box( $post ) {
	wp_nonce_field( 'wla_save_seo_meta', 'wla_seo_nonce' );
	$title = get_post_meta( $post->ID, '_wla_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_wla_seo_description', true );
	$kw    = get_post_meta( $post->ID, '_wla_seo_keywords', true );
	$noidx = get_post_meta( $post->ID, '_wla_seo_noindex', true );
	$ogimg = get_post_meta( $post->ID, '_wla_seo_og_image', true );
	$spanish = wla_is_spanish_language();
	?>
	<p><label><strong><?php echo esc_html( $spanish ? 'Título SEO' : 'SEO Title' ); ?></strong><br>
	<input type="text" name="wla_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%" maxlength="120"></label></p>
	<p><label><strong><?php echo esc_html( $spanish ? 'Meta descripción' : 'Meta Description' ); ?></strong><br>
	<textarea name="wla_seo_description" rows="3" style="width:100%" maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea></label></p>
	<p><label><strong><?php echo esc_html( $spanish ? 'Palabras clave (separadas por comas)' : 'Keywords (comma-separated)' ); ?></strong><br>
	<input type="text" name="wla_seo_keywords" value="<?php echo esc_attr( $kw ); ?>" style="width:100%"></label></p>
	<p><label><strong><?php echo esc_html( $spanish ? 'Imagen OG (URL)' : 'OG Image (URL)' ); ?></strong><br>
	<input type="url" name="wla_seo_og_image" value="<?php echo esc_attr( $ogimg ); ?>" style="width:100%"></label></p>
	<p><label><input type="checkbox" name="wla_seo_noindex" value="1" <?php checked( $noidx, '1' ); ?>> <strong><?php echo esc_html( $spanish ? 'No indexar esta página (borrador)' : 'Noindex this page (draft)' ); ?></strong></label></p>
	<?php
}

function wla_save_seo_meta( $post_id ) {
	if ( ! isset( $_POST['wla_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wla_seo_nonce'] ) ), 'wla_save_seo_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$fields = array(
		'_wla_seo_title'       => 'sanitize_text_field',
		'_wla_seo_description' => 'sanitize_textarea_field',
		'_wla_seo_keywords'    => 'sanitize_text_field',
		'_wla_seo_og_image'    => 'esc_url_raw',
	);
	foreach ( $fields as $key => $fn ) {
		if ( isset( $_POST[ substr( $key, 1 ) ] ) ) {
			update_post_meta( $post_id, $key, $fn( wp_unslash( $_POST[ substr( $key, 1 ) ] ) ) );
		}
	}
	$noidx = isset( $_POST['wla_seo_noindex'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['wla_seo_noindex'] ) ) ? '1' : '0';
	update_post_meta( $post_id, '_wla_seo_noindex', $noidx );
}
add_action( 'save_post', 'wla_save_seo_meta' );
