<?php
/**
 * Theme setup.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports, menus and image sizes.
 */
function wla_theme_setup() {
	load_theme_textdomain( 'weightloss-affordable', WLA_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 88,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'wla-card', 760, 560, true );
	add_image_size( 'wla-hero', 1280, 980, true );

	register_nav_menus(
		array(
			'primary'        => __( 'Primary navigation', 'weightloss-affordable' ),
			'footer_explore' => __( 'Footer — explore links', 'weightloss-affordable' ),
			'footer_legal'   => __( 'Footer — legal links', 'weightloss-affordable' ),
		)
	);
}
add_action( 'after_setup_theme', 'wla_theme_setup' );

/**
 * Content width.
 */
function wla_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wla_content_width', 1340 );
}
add_action( 'after_setup_theme', 'wla_content_width', 0 );

/**
 * Widget areas (footer + blog sidebar).
 */
function wla_register_sidebars() {
	register_sidebar(
		array(
			'name'          => __( 'Blog sidebar', 'weightloss-affordable' ),
			'id'            => 'blog-sidebar',
			'description'   => __( 'Shown beside blog posts and archives.', 'weightloss-affordable' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer extra', 'weightloss-affordable' ),
			'id'            => 'footer-extra',
			'description'   => __( 'Optional extra footer column.', 'weightloss-affordable' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'wla_register_sidebars' );

/**
 * Body classes used by the stylesheet.
 *
 * @param array $classes Body classes.
 * @return array
 */
function wla_body_classes( $classes ) {
	$classes[] = 'wla-theme';
	if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
		$classes[] = 'wla-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'wla_body_classes' );

/**
 * Excerpt length.
 *
 * @return int
 */
function wla_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'wla_excerpt_length' );

/**
 * Excerpt more string.
 *
 * @return string
 */
function wla_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'wla_excerpt_more' );
