<?php
/**
 * Section rendering bridge.
 *
 * The plugin owns data and ordering; the active theme owns markup. Templates
 * live in the theme under template-parts/sections/{type}.php so the design
 * system stays with the theme.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build wrapper classes from layout meta.
 *
 * @param array $data Section data.
 * @return string
 */
function nrx_section_classes( $data ) {
	$classes = array( 'nrx-section', 'nrx-section-' . sanitize_html_class( $data['type'] ) );
	$classes[] = 'nrx-bg-' . ( $data['background'] ? sanitize_html_class( $data['background'] ) : 'default' );
	$classes[] = 'nrx-space-' . ( $data['spacing'] ? sanitize_html_class( $data['spacing'] ) : 'normal' );
	$devices   = is_array( $data['devices'] ) ? $data['devices'] : array( 'desktop', 'tablet', 'mobile' );
	foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
		if ( ! in_array( $device, $devices, true ) ) {
			$classes[] = 'nrx-hide-' . $device;
		}
	}
	if ( ! empty( $data['custom_class'] ) ) {
		$classes[] = $data['custom_class'];
	}
	if ( ! empty( $data['require_approval'] ) && ! nrx_compliance_is_approved() ) {
		$classes[] = 'nrx-gated-preview';
	}
	return implode( ' ', array_filter( $classes ) );
}

/**
 * Render a section by type.
 *
 * @param int|WP_Post $section Section post.
 * @return string
 */
function nrx_render_section( $section ) {
	$data = nrx_get_section_data( $section );
	if ( empty( $data ) ) {
		return '';
	}
	$template = locate_template( array( 'template-parts/sections/' . $data['type'] . '.php' ) );
	if ( ! $template ) {
		return nrx_render_section_fallback( $data );
	}
	ob_start();
	include $template;
	return (string) ob_get_clean();
}

/**
 * Render all sections for a context.
 *
 * @param string $context front|page.
 * @param int    $page_id Page ID.
 */
function nrx_render_sections( $context = 'front', $page_id = 0 ) {
	$sections = nrx_get_sections_for( $context, $page_id );
	foreach ( $sections as $section ) {
		echo nrx_render_section( $section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in templates.
	}
}

/**
 * Safe fallback if a theme template is missing.
 *
 * @param array $data Section data.
 * @return string
 */
function nrx_render_section_fallback( $data ) {
	$title = $data['title'] ?? '';
	$body  = '';
	if ( ! empty( $data['body'] ) && is_string( $data['body'] ) ) {
		$body = $data['body'];
	}
	ob_start();
	?>
	<section class="<?php echo esc_attr( nrx_section_classes( $data ) ); ?>">
		<div class="site-container">
			<p class="eyebrow"><span><?php echo esc_html( nrx_section_type_label( $data['type'] ) ); ?></span></p>
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php if ( $body ) : ?>
				<div class="nrx-prose"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Print an inline image or an intentional editorial placeholder.
 *
 * @param string $url    Image URL.
 * @param string $alt    Alt text.
 * @param string $class  Extra classes.
 * @param bool   $eager  Eager loading.
 * @return string
 */
function nrx_media( $url, $alt = '', $class = '', $eager = false ) {
	$class = trim( 'nrx-media ' . $class );
	if ( ! $url ) {
		return '<div class="' . esc_attr( $class . ' nrx-media-placeholder' ) . '" aria-hidden="true"><span class="nrx-media-mark">nuestra<em>Rx</em></span></div>';
	}
	return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="' . ( $eager ? 'eager' : 'lazy' ) . '" decoding="async"' . ( $eager ? ' fetchpriority="high"' : '' ) . '>';
}

/**
 * Render a button.
 *
 * @param string $label    Label (Spanish).
 * @param string $label_en English label.
 * @param string $url      URL.
 * @param string $style    primary|dark|quiet|outline.
 * @param string $icon     Icon key.
 * @return string
 */
function nrx_button( $label, $label_en, $url, $style = 'primary', $icon = 'arrow' ) {
	if ( ! $label ) {
		return '';
	}
	$classes = 'button nrx-button button-' . sanitize_html_class( $style );
	$html    = '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( nrx_url( $url ) ) . '"' . nrx_bi_attr( $label_en ) . '><span>' . esc_html( $label ) . '</span>';
	if ( $icon ) {
		$html .= '<span class="button-arrow">' . nrx_icon( $icon, 18 ) . '</span>';
	}
	return $html . '</a>';
}

/**
 * Render an eyebrow label with the theme's rule treatment.
 *
 * @param string $label    Spanish label.
 * @param string $label_en English label.
 * @param string $index    Optional index.
 * @return string
 */
function nrx_eyebrow( $label, $label_en = '', $index = '' ) {
	if ( ! $label && ! $index ) {
		return '';
	}
	$html = '<p class="eyebrow nrx-eyebrow">';
	if ( '' !== $index ) {
		$html .= '<span class="section-index">' . esc_html( $index ) . '</span><span class="eyebrow-line"></span>';
	}
	$html .= '<span' . nrx_bi_attr( $label_en ) . '>' . esc_html( $label ) . '</span></p>';
	return $html;
}

/**
 * Split a textarea into lines of bullet text.
 *
 * @param string $text Text.
 * @return array
 */
function nrx_lines( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	return array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
}
