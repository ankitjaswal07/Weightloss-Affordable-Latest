<?php
/**
 * Presentation helpers.
 *
 * Every section renderer accepts an argument array (see the plugin's section
 * schema) and returns escaped HTML. Both the theme templates and the Elementor
 * widgets call these functions, so the editor preview and the front end are
 * driven by exactly the same code.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema defaults for a section, or an empty array if the plugin is inactive.
 *
 * @param string $type Section type.
 * @return array
 */
function wla_defaults( $type ) {
	return function_exists( 'wla_section_defaults' ) ? wla_section_defaults( $type ) : array();
}

/**
 * Resolve an image value (attachment ID, URL or bundled file name) to a URL.
 *
 * @param mixed  $value   Image value.
 * @param string $fallback Fallback file name.
 * @return string
 */
function wla_resolve_asset_url( $value, $fallback = '' ) {
	$value = is_array( $value ) ? ( $value['url'] ?? '' ) : $value;

	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, 'large' );
		if ( $url ) {
			return esc_url( $url );
		}
	}

	if ( is_string( $value ) && preg_match( '#^https?://#i', $value ) ) {
		return esc_url( $value );
	}

	if ( is_string( $value ) && '' !== $value ) {
		$path = WLA_THEME_DIR . '/assets/images/' . basename( $value );
		if ( file_exists( $path ) ) {
			return esc_url( WLA_THEME_URI . '/assets/images/' . basename( $value ) );
		}
	}

	if ( $fallback ) {
		return esc_url( WLA_THEME_URI . '/assets/images/' . basename( $fallback ) );
	}
	return '';
}

/**
 * Resolve an internal route to a real permalink.
 *
 * @param string $href Route, anchor or absolute URL.
 * @return string
 */
function wla_resolve_route_url( $href ) {
	$href = (string) $href;
	if ( '' === $href ) {
		return esc_url( home_url( '/' ) );
	}
	if ( '#' === $href[0] ) {
		return esc_attr( $href );
	}
	if ( preg_match( '#^(https?:)?//#i', $href ) || 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
		return esc_url( $href );
	}
	$path = trim( wp_parse_url( $href, PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return esc_url( home_url( '/' ) );
	}
	$page = get_page_by_path( $path );
	if ( $page ) {
		return esc_url( get_permalink( $page ) );
	}
	return esc_url( home_url( '/' . $path . '/' ) );
}

/**
 * Inline SVG icon set.
 *
 * @param string $name Icon name.
 * @return string
 */
function wla_svg_icon( $name ) {
	$paths = array(
		'spark'  => 'M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3zm7 12l1 2.5 2.5 1-2.5 1L19 22l-1-2.5-2.5-1 2.5-1L19 15z',
		'heart'  => 'M20.8 8.7c0 5.1-8.8 10.2-8.8 10.2S3.2 13.8 3.2 8.7A4.2 4.2 0 0111 6.1l1 1 1-1a4.2 4.2 0 017.8 2.6z',
		'chat'   => 'M20 11.3a7.8 7.8 0 01-8 7.7 8.5 8.5 0 01-3.5-.7L4 20l1.5-3.6A7.5 7.5 0 014 11.3a8 8 0 0116 0z',
		'book'   => 'M4 5.5A2.5 2.5 0 016.5 3H20v16H6.5A2.5 2.5 0 004 21.5v-16zm0 13A2.5 2.5 0 016.5 16H20M8 7h8m-8 4h6',
		'shield' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11zm-3.5-10 2.3 2.3 4.8-5',
		'arrow'  => 'M5 12h14m-6-6 6 6-6 6',
		'leaf'   => 'M20 4c-8 0-14 3.8-14 10a6 6 0 006 6c6.2 0 8-8 8-16zM4 20c2.8-4.7 6-7.4 11-10',
		'check'  => 'M5 12l4 4L19 6',
		'clock'  => 'M12 22a10 10 0 110-20 10 10 0 010 20zm0-16v6l4 2',
		'globe'  => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z',
		'close'  => 'M18 6L6 18M6 6l12 12',
		'layers' => 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5',
		'pill'   => 'M10.5 20.5a5 5 0 01-7-7l6-6a5 5 0 017 7l-6 6zM8 8l8 8',
		'truck'  => 'M3 16V6h11v10M14 10h4l3 3v3h-7M7 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['spark'];
	return '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="' . esc_attr( $path ) . '" /></svg>';
}

/**
 * Brand mark (logo fallback).
 *
 * @return string
 */
function wla_render_brand_mark() {
	return '<span class="brand-lockup">'
		. '<span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 44 44" fill="none"><rect x="1" y="1" width="42" height="42" rx="15" fill="#173D36" /><path d="M12 26.3c7.1 1 15-2.9 19.9-11.7 1.5 10.6-3.2 18.1-10.7 18.1-4.1 0-7.2-2.4-9.2-6.4Z" fill="#D7E7D2" /><path d="M12 31.7c4.5-6.7 9.4-10.3 16.2-13.8" stroke="#F7C89A" stroke-width="1.7" stroke-linecap="round" /></svg></span>'
		. '<span class="brand-type"><strong>weightloss</strong><span>affordable</span></span>'
		. '</span>';
}

/**
 * Eyebrow markup.
 *
 * @param string $index  Section number.
 * @param string $eyebrow Text.
 * @param bool   $light  Light variant.
 * @return string
 */
function wla_eyebrow( $index, $eyebrow, $light = false ) {
	ob_start();
	?>
	<p class="eyebrow<?php echo $light ? ' eyebrow-light' : ''; ?>">
		<?php if ( '' !== (string) $index ) : ?>
			<span class="section-index"><?php echo esc_html( $index ); ?></span>
			<span class="eyebrow-line"></span>
		<?php else : ?>
			<span class="eyebrow-dot"></span>
		<?php endif; ?>
		<span><?php echo esc_html( $eyebrow ); ?></span>
	</p>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Hero
 * ====================================================================== */

/**
 * Hero section.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_hero_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow'       => 'A clear path to medically guided weight care',
			'line1'         => 'Modern weight care,',
			'emphasis_word' => 'made',
			'line2_suffix'  => 'understandable.',
			'line3'         => 'Start with a conversation.',
			'body'          => '',
			'primary_text'  => 'Request a consultation',
			'primary_url'   => '/consultation-eligibility',
			'secondary_text' => 'See how it works',
			'secondary_url' => '/how-it-works',
			'image'         => 'hero-care.jpg',
			'image_alt'     => '',
			'lang_mini'     => '',
			'lang_title'    => '',
			'float_badge'   => '',
			'float_sub'     => '',
			'orbit_text'    => '',
			'scroll_label'  => 'Scroll to explore',
			'proof'         => array(),
			'marquee'       => array(),
		)
	);

	$img_url = wla_resolve_asset_url( $a['image'], 'hero-care.jpg' );
	ob_start();
	?>
	<section class="hero-section" id="home">
		<div class="hero-backdrop" aria-hidden="true">
			<span class="hero-word">care</span>
			<span class="hero-ring hero-ring-one"></span>
			<span class="hero-ring hero-ring-two"></span>
		</div>

		<div class="site-container hero-grid">
			<div class="hero-copy">
				<p class="eyebrow hero-eyebrow">
					<span class="eyebrow-dot"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
					<span class="eyebrow-rule"></span>
				</p>

				<h1>
					<span class="hero-line"><span><?php echo esc_html( $a['line1'] ); ?></span></span>
					<span class="hero-line"><span><em><?php echo esc_html( $a['emphasis_word'] ); ?></em> <?php echo esc_html( trim( $a['line2_suffix'] ) ); ?></span></span>
					<span class="hero-line"><span><?php echo esc_html( $a['line3'] ); ?></span></span>
				</h1>

				<p class="hero-description"><?php echo esc_html( $a['body'] ); ?></p>

				<div class="hero-actions">
					<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['primary_url'] ); ?>">
						<span><?php echo esc_html( $a['primary_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
					<a class="button button-quiet" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
						<span class="play-mark">↗</span>
						<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
					</a>
				</div>

				<?php if ( ! empty( $a['proof'] ) ) : ?>
					<div class="hero-proof">
						<?php foreach ( (array) $a['proof'] as $item ) : ?>
							<div class="hero-proof-item">
								<strong><?php echo esc_html( is_array( $item ) ? ( $item[0] ?? '' ) : $item ); ?></strong>
								<span><?php echo esc_html( is_array( $item ) ? ( $item[1] ?? '' ) : '' ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="hero-visual">
				<div class="hero-image-shell" data-parallax="0.018">
					<div class="hero-image-wrap">
						<img src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" class="hero-image" loading="eager" fetchpriority="high" decoding="async" />
						<span class="hero-image-shade" aria-hidden="true"></span>
					</div>
				</div>

				<?php if ( ! empty( $a['lang_title'] ) ) : ?>
					<div class="hero-language-card">
						<span class="mini-label"><?php echo esc_html( $a['lang_mini'] ); ?></span>
						<strong><?php echo esc_html( $a['lang_title'] ); ?></strong>
						<span class="language-dots"><i></i><i></i><i></i></span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $a['float_badge'] ) ) : ?>
					<div class="image-float-card">
						<span class="float-icon"><?php echo wla_svg_icon( 'heart' ); ?></span>
						<span>
							<strong><?php echo esc_html( $a['float_badge'] ); ?></strong>
							<small><?php echo esc_html( $a['float_sub'] ); ?></small>
						</span>
						<span class="float-spark" aria-hidden="true">✳</span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $a['orbit_text'] ) ) : ?>
					<div class="orbit-badge" aria-hidden="true">
						<svg viewBox="0 0 100 100">
							<defs><path id="wla-orbit-circle" d="M50,50 m-38,0 a38,38 0 1,1 76,0 a38,38 0 1,1 -76,0" /></defs>
							<text><textPath href="#wla-orbit-circle"><?php echo esc_html( $a['orbit_text'] ); ?></textPath></text>
						</svg>
						<span>✳</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="site-container hero-scroll">
			<span><?php echo esc_html( $a['scroll_label'] ); ?></span>
			<i aria-hidden="true"></i>
		</div>

		<?php if ( ! empty( $a['marquee'] ) ) : ?>
			<div class="values-marquee">
				<div class="marquee-track">
					<?php for ( $g = 0; $g < 2; $g++ ) : ?>
						<div class="marquee-group"<?php echo 1 === $g ? ' aria-hidden="true"' : ''; ?>>
							<?php foreach ( (array) $a['marquee'] as $m_item ) : ?>
								<span><?php echo esc_html( $m_item ); ?><i aria-hidden="true">✳</i></span>
							<?php endforeach; ?>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Intro
 * ====================================================================== */

/**
 * Intro / point-of-view section.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_intro_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'      => '01',
			'eyebrow'    => 'OUR APPROACH',
			'title'      => '',
			'body'       => '',
			'note'       => '',
			'link_label' => '',
			'link_url'   => '/about',
		)
	);
	ob_start();
	?>
	<section class="intro-section section-pad" id="about">
		<div class="site-container intro-grid">
			<div class="section-heading" data-reveal="left">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
			</div>
			<div class="intro-copy" data-reveal="right" style="--reveal-delay:0.12s">
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<?php if ( ! empty( $a['note'] ) ) : ?>
					<div class="editorial-note">
						<span><?php echo wla_svg_icon( 'shield' ); ?></span>
						<p><?php echo esc_html( $a['note'] ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $a['link_label'] ) ) : ?>
					<div class="section-inline-link">
						<a class="text-link" href="<?php echo wla_resolve_route_url( $a['link_url'] ); ?>">
							<span><?php echo esc_html( $a['link_label'] ); ?></span>
							<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Product showcase (new section requested in the brief)
 * ====================================================================== */

/**
 * Product showcase: imagery, specification rows, bullets and metrics.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_product_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'          => '02',
			'eyebrow'        => 'WHAT YOU RECEIVE',
			'title'          => '',
			'body'           => '',
			'image'          => 'product-vials.jpg',
			'image_alt'      => '',
			'badge'          => '',
			'specs'          => array(),
			'bullets'        => array(),
			'cta_text'       => '',
			'cta_url'        => '/weight-loss-treatments',
			'secondary_text' => '',
			'secondary_url'  => '/compare-treatments',
			'note'           => '',
			'metrics'        => array(),
		)
	);

	$img_url = wla_resolve_asset_url( $a['image'], 'product-vials.jpg' );
	ob_start();
	?>
	<section class="product-section section-pad" id="product">
		<span class="product-ghost" data-parallax="0.012" aria-hidden="true">care</span>
		<div class="site-container">
			<div class="product-grid">
				<div class="product-media" data-reveal="clip">
					<div class="product-media-frame">
						<img src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" loading="lazy" decoding="async" />
						<span class="product-media-shade" aria-hidden="true"></span>
					</div>
					<?php if ( ! empty( $a['badge'] ) ) : ?>
						<div class="product-badge">
							<span class="product-badge-icon"><?php echo wla_svg_icon( 'shield' ); ?></span>
							<span><?php echo esc_html( $a['badge'] ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $a['metrics'] ) ) : ?>
						<div class="product-metrics">
							<?php foreach ( (array) $a['metrics'] as $metric ) : ?>
								<div class="product-metric">
									<strong><?php echo esc_html( is_array( $metric ) ? ( $metric[0] ?? '' ) : $metric ); ?></strong>
									<span><?php echo esc_html( is_array( $metric ) ? ( $metric[1] ?? '' ) : '' ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="product-copy" data-reveal="right" style="--reveal-delay:0.1s">
					<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
					<p class="product-description"><?php echo esc_html( $a['body'] ); ?></p>

					<?php if ( ! empty( $a['specs'] ) ) : ?>
						<dl class="product-specs">
							<?php foreach ( (array) $a['specs'] as $spec ) : ?>
								<div class="product-spec">
									<dt><?php echo esc_html( is_array( $spec ) ? ( $spec[0] ?? '' ) : $spec ); ?></dt>
									<dd><?php echo esc_html( is_array( $spec ) ? ( $spec[1] ?? '' ) : '' ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<?php if ( ! empty( $a['bullets'] ) ) : ?>
						<ul class="check-list product-list">
							<?php foreach ( (array) $a['bullets'] as $bullet ) : ?>
								<li><span class="check-badge"><?php echo wla_svg_icon( 'check' ); ?></span><span><?php echo esc_html( $bullet ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="product-actions">
						<?php if ( ! empty( $a['cta_text'] ) ) : ?>
							<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['cta_url'] ); ?>">
								<span><?php echo esc_html( $a['cta_text'] ); ?></span>
								<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $a['secondary_text'] ) ) : ?>
							<a class="button button-outline" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
								<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
								<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
							</a>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $a['note'] ) ) : ?>
						<p class="product-note"><?php echo esc_html( $a['note'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Card grid
 * ====================================================================== */

/**
 * Service / treatment card grid.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_services_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'   => '03',
			'eyebrow' => 'TREATMENT OPTIONS',
			'title'   => '',
			'intro'   => '',
			'cards'   => array(),
			'notice'  => '',
		)
	);
	ob_start();
	?>
	<section class="services-section section-pad" id="services">
		<div class="site-container">
			<div class="section-header-row" data-reveal>
				<div>
					<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				</div>
				<?php if ( ! empty( $a['intro'] ) ) : ?>
					<p class="section-side-copy"><?php echo esc_html( $a['intro'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="service-grid">
				<?php
				foreach ( (array) $a['cards'] as $idx => $card ) :
					$variant = ( 0 === $idx ) ? 1 : ( ( 2 === $idx % 3 ) ? 3 : 2 );
					$icon    = $card['icon'] ?? 'heart';
					$text    = $card['text'] ?? ( $card['body'] ?? '' );
					?>
					<article class="service-card service-card-<?php echo esc_attr( $variant ); ?>" data-reveal="up" data-tilt style="--reveal-delay:<?php echo esc_attr( number_format( $idx * 0.1, 2 ) ); ?>s">
						<span class="service-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $idx + 1 ) ); ?></span>
						<div class="service-icon"><?php echo wla_svg_icon( $icon ); ?></div>
						<?php if ( ! empty( $card['tag'] ) ) : ?>
							<span class="card-tag"><?php echo esc_html( $card['tag'] ); ?></span>
						<?php endif; ?>
						<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $text ); ?></p>
						<?php if ( ! empty( $card['cta'] ) ) : ?>
							<a href="<?php echo wla_resolve_route_url( $card['href'] ?? '#contact' ); ?>" class="text-link">
								<span><?php echo esc_html( $card['cta'] ); ?></span>
								<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
							</a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $a['notice'] ) ) : ?>
				<div class="editorial-note subpage-notice" data-reveal>
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php echo esc_html( $a['notice'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Journey board
 * ====================================================================== */

/**
 * Interactive four-stage journey board.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_journey_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'          => '04',
			'window_left'    => 'Your care journey',
			'window_right'   => '04 stages',
			'eyebrow'        => 'HOW IT WORKS',
			'title'          => '',
			'intro'          => '',
			'chips'          => array(),
			'steps'          => array(),
			'progress_label' => 'Journey stage',
			'footnote'       => '',
			'cta_text'       => '',
			'cta_url'        => '/contact',
		)
	);
	$step_icons = array( 'book', 'chat', 'shield', 'heart' );
	$steps      = array_values( (array) $a['steps'] );
	ob_start();
	?>
	<section class="process-section journey-section section-pad" id="process" data-wla-journey>
		<span class="process-ghost" data-parallax="0.014" aria-hidden="true">path</span>
		<div class="journey-aurora" aria-hidden="true"><span></span><span></span><span></span></div>

		<div class="site-container">
			<div class="journey-shell" data-reveal="scale">
				<div class="journey-shell-top" aria-hidden="true">
					<span><?php echo esc_html( $a['window_left'] ); ?></span>
					<span class="journey-shell-dots"><i></i><i></i><i></i></span>
					<span><?php echo esc_html( $a['window_right'] ); ?></span>
				</div>

				<div class="process-intro journey-intro">
					<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
					<p><?php echo esc_html( $a['intro'] ); ?></p>
					<?php if ( ! empty( $a['chips'] ) ) : ?>
						<div class="journey-summary-chips">
							<?php foreach ( (array) $a['chips'] as $ci => $chip ) : ?>
								<span><?php echo wla_svg_icon( 0 === $ci ? 'spark' : ( 1 === $ci ? 'book' : 'shield' ) ); ?><p style="margin:0;"><?php echo esc_html( $chip ); ?></p></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="journey-board">
					<div class="journey-track" aria-hidden="true">
						<svg viewBox="0 0 1200 128" preserveAspectRatio="none" focusable="false">
							<path class="journey-path journey-path-background" pathLength="1" d="M24 82 C 210 18, 350 112, 590 62 C 805 18, 970 104, 1176 38" />
							<path class="journey-path journey-path-progress" pathLength="1" d="M24 82 C 210 18, 350 112, 590 62 C 805 18, 970 104, 1176 38" />
						</svg>
						<span class="journey-traveler"><i></i></span>
					</div>

					<ol class="journey-steps">
						<?php foreach ( $steps as $idx => $s ) : ?>
							<li class="journey-step<?php echo 0 === $idx ? ' is-active' : ''; ?>" data-step-index="<?php echo esc_attr( $idx ); ?>" data-reveal="up" style="--reveal-delay:<?php echo esc_attr( number_format( $idx * 0.11, 2 ) ); ?>s">
								<article class="journey-card" data-tilt>
									<span class="journey-card-ghost" aria-hidden="true"><?php echo esc_html( $s[0] ?? '' ); ?></span>
									<div class="journey-card-top">
										<button class="journey-number-button" type="button" aria-pressed="<?php echo 0 === $idx ? 'true' : 'false'; ?>">
											<span class="step-num-text"><?php echo esc_html( $s[0] ?? '' ); ?></span>
										</button>
										<span class="journey-card-icon"><?php echo wla_svg_icon( $step_icons[ $idx % 4 ] ); ?></span>
									</div>
									<p class="journey-card-kicker"><?php echo esc_html( $s[4] ?? '' ); ?></p>
									<h3><?php echo esc_html( $s[1] ?? '' ); ?></h3>
									<p class="journey-card-description"><?php echo esc_html( $s[2] ?? '' ); ?></p>
									<div class="journey-card-footer">
										<span class="journey-chip"><?php echo wla_svg_icon( 2 === $idx ? 'shield' : 'clock' ); ?><span><?php echo esc_html( $s[3] ?? '' ); ?></span></span>
										<span class="journey-card-arrow" aria-hidden="true"><?php echo wla_svg_icon( 'arrow' ); ?></span>
									</div>
									<span class="journey-card-shine" aria-hidden="true"></span>
								</article>
								<span class="journey-node" aria-hidden="true"><i></i></span>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>

				<div class="journey-bottom">
					<div class="journey-progress-wrap" aria-hidden="true">
						<div class="journey-progress-meta">
							<span><?php echo esc_html( $a['progress_label'] ); ?></span>
							<strong data-journey-counter>01 / <?php echo esc_html( sprintf( '%02d', count( $steps ) ) ); ?></strong>
						</div>
						<div class="journey-progress"><span data-journey-bar style="transform:scaleX(0.25)"></span></div>
					</div>
					<?php if ( ! empty( $a['footnote'] ) ) : ?>
						<p class="journey-footnote"><?php echo wla_svg_icon( 'shield' ); ?><span><?php echo esc_html( $a['footnote'] ); ?></span></p>
					<?php endif; ?>
					<?php if ( ! empty( $a['cta_text'] ) ) : ?>
						<a class="button button-primary journey-cta" href="<?php echo wla_resolve_route_url( $a['cta_url'] ); ?>">
							<span><?php echo esc_html( $a['cta_text'] ); ?></span>
							<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Image + text split
 * ====================================================================== */

/**
 * Split section with photo, copy, bullets and button.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_approach_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'         => '05',
			'eyebrow'       => 'SAFETY FIRST',
			'title'         => '',
			'body'          => '',
			'bullets'       => array(),
			'button_text'   => '',
			'button_url'    => '/treatment-safety-information',
			'image'         => 'lifestyle-walk.jpg',
			'image_alt'     => '',
			'photo_caption' => '',
		)
	);
	$img_url = wla_resolve_asset_url( $a['image'], 'lifestyle-walk.jpg' );
	ob_start();
	?>
	<section class="approach-section section-pad" id="approach">
		<div class="site-container approach-grid">
			<div class="approach-photo" data-parallax="0.022">
				<img src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" loading="lazy" decoding="async" />
				<span class="approach-photo-ring" aria-hidden="true"></span>
				<?php if ( ! empty( $a['photo_caption'] ) ) : ?>
					<div class="photo-caption">
						<span class="caption-number">01</span>
						<span><?php echo esc_html( $a['photo_caption'] ); ?></span>
					</div>
				<?php endif; ?>
			</div>
			<div class="approach-copy" data-reveal="right" style="--reveal-delay:0.12s">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p class="approach-description"><?php echo esc_html( $a['body'] ); ?></p>
				<?php if ( ! empty( $a['bullets'] ) ) : ?>
					<ul class="check-list">
						<?php foreach ( (array) $a['bullets'] as $item ) : ?>
							<li><span class="check-badge"><?php echo wla_svg_icon( 'check' ); ?></span><span><?php echo esc_html( $item ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $a['button_text'] ) ) : ?>
					<a class="button button-outline" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>">
						<span><?php echo esc_html( $a['button_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Pricing panel
 * ====================================================================== */

/**
 * Pricing transparency panel.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_pricing_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'       => '06',
			'eyebrow'     => 'PRICING & AVAILABILITY',
			'title'       => '',
			'body'        => '',
			'button_text' => '',
			'button_url'  => '/pricing-and-plans',
			'status'      => '',
			'sub'         => '',
			'rows'        => array(),
			'note'        => '',
		)
	);
	ob_start();
	?>
	<section class="pricing-section section-pad" id="pricing">
		<div class="site-container pricing-card" data-reveal="scale">
			<div class="pricing-emblem"><?php echo wla_svg_icon( 'spark' ); ?></div>
			<div class="pricing-copy">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<?php if ( ! empty( $a['button_text'] ) ) : ?>
					<a href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>" class="text-link">
						<span><?php echo esc_html( $a['button_text'] ); ?></span>
						<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
			<div class="pricing-status">
				<div class="pricing-status-head">
					<span class="status-orbit"><span></span></span>
					<div>
						<span class="status-label"><?php echo esc_html( $a['status'] ); ?></span>
						<small><?php echo esc_html( $a['sub'] ); ?></small>
					</div>
				</div>
				<div class="pricing-rows">
					<?php foreach ( (array) $a['rows'] as $row ) : ?>
						<div class="pricing-row">
							<span><?php echo esc_html( is_array( $row ) ? ( $row[0] ?? '' ) : $row ); ?></span>
							<strong><?php echo esc_html( is_array( $row ) ? ( $row[1] ?? '' ) : '' ); ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( ! empty( $a['note'] ) ) : ?>
					<p><?php echo esc_html( $a['note'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Guide explorer
 * ====================================================================== */

/**
 * Interactive guide explorer.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_resources_explorer_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'           => '07',
			'eyebrow'         => 'LEARN AT YOUR OWN PACE',
			'title'           => '',
			'lead'            => '',
			'all_link'        => '',
			'all_url'         => '/resources',
			'featured_label'  => 'Featured guide',
			'read_button'     => 'Read the guide',
			'ask_button'      => 'Ask about this topic',
			'q_heading'       => 'Questions to bring',
			'q_note'          => '',
			'editorial_title' => '',
			'editorial_body'  => '',
			'strip'           => array(),
			'guides'          => array(),
		)
	);

	$guides = array();
	foreach ( (array) $a['guides'] as $guide ) {
		if ( ! is_array( $guide ) ) {
			continue;
		}
		$image_url = ! empty( $guide['image'] ) ? wla_resolve_asset_url( $guide['image'] ) : '';
		$guide['resolvedImage'] = $image_url;
		$guide['questions']     = is_array( $guide['questions'] ?? null ) ? $guide['questions'] : array();
		$guides[]               = $guide;
	}

	if ( empty( $guides ) ) {
		return '';
	}

	$first       = $guides[0];
	$first_image = $first['resolvedImage'];
	$count       = count( $guides );
	ob_start();
	?>
	<section class="resources-section section-pad" id="resources" data-wla-explorer data-guides="<?php echo esc_attr( wp_json_encode( $guides ) ); ?>">
		<span class="resources-ghost" data-parallax="0.012" aria-hidden="true">learn</span>
		<div class="site-container">

			<div class="section-header-row resources-header" data-reveal>
				<div>
					<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				</div>
				<div class="resources-header-side">
					<p><?php echo esc_html( $a['lead'] ); ?></p>
					<a href="<?php echo wla_resolve_route_url( $a['all_url'] ); ?>" class="text-link resource-all">
						<span><?php echo esc_html( $a['all_link'] ); ?></span>
						<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				</div>
			</div>

			<div class="resource-explorer" data-reveal="scale">
				<div class="resource-stage" data-parallax="0.008">
					<article class="resource-slide" data-explorer-slide>
						<div class="resource-stage-media" aria-hidden="true">
							<?php if ( $first_image ) : ?>
								<img src="<?php echo esc_url( $first_image ); ?>" alt="" class="resource-stage-image" data-stage-img decoding="async" />
							<?php else : ?>
								<img src="" alt="" class="resource-stage-image" data-stage-img hidden />
							<?php endif; ?>
							<span class="resource-stage-shade"></span>
						</div>

						<div class="resource-stage-inner">
							<div class="resource-stage-toprow">
								<div class="resource-stage-rail">
									<span><i></i><span data-stage-featured-label><?php echo esc_html( $a['featured_label'] ); ?></span></span>
									<span><span data-stage-idx>01</span><em>/</em><?php echo esc_html( sprintf( '%02d', $count ) ); ?></span>
								</div>
								<span class="resource-stage-number" aria-hidden="true" data-stage-watermark>01</span>
							</div>

							<div class="resource-stage-grid">
								<div class="resource-stage-copy">
									<div class="resource-stage-meta">
										<span data-stage-category><?php echo esc_html( $first['category'] ?? '' ); ?></span>
										<span data-stage-readtime><?php echo esc_html( $first['readTime'] ?? '' ); ?></span>
									</div>
									<p class="resource-stage-prompt" data-stage-prompt><?php echo esc_html( $first['prompt'] ?? '' ); ?></p>
									<h3 data-stage-title><?php echo esc_html( $first['title'] ?? '' ); ?></h3>
									<p class="resource-stage-text" data-stage-text><?php echo esc_html( $first['text'] ?? '' ); ?></p>
									<div class="resource-stage-actions">
										<button type="button" class="resource-stage-button" data-open-guide-modal>
											<p data-read-btn-text style="margin:0"><?php echo esc_html( $a['read_button'] ); ?></p>
											<span><?php echo wla_svg_icon( 'arrow' ); ?></span>
										</button>
										<button type="button" class="resource-stage-secondary" data-ask-guide-topic><?php echo esc_html( $a['ask_button'] ); ?></button>
									</div>
								</div>

								<aside class="resource-question-card">
									<div class="resource-question-head">
										<span><?php echo wla_svg_icon( 'chat' ); ?></span>
										<strong><?php echo esc_html( $a['q_heading'] ); ?></strong>
									</div>
									<ul data-stage-questions>
										<?php foreach ( (array) ( $first['questions'] ?? array() ) as $qi => $q ) : ?>
											<li><span><?php echo esc_html( sprintf( '%02d', $qi + 1 ) ); ?></span><span><?php echo esc_html( $q ); ?></span></li>
										<?php endforeach; ?>
									</ul>
									<p><?php echo esc_html( $a['q_note'] ); ?></p>
								</aside>
							</div>

							<div class="resource-stage-controls">
								<button type="button" aria-label="<?php esc_attr_e( 'Previous guide', 'weightloss-affordable' ); ?>" data-explorer-prev>
									<span class="resource-prev-icon"><?php echo wla_svg_icon( 'arrow' ); ?></span>
								</button>
								<button class="resource-autoplay-button" type="button" aria-pressed="false" data-explorer-toggle>
									<span class="pause-icon" aria-hidden="true"><i></i><i></i></span>
									<span data-explorer-toggle-label><?php esc_html_e( 'Pause', 'weightloss-affordable' ); ?></span>
								</button>
								<button type="button" aria-label="<?php esc_attr_e( 'Next guide', 'weightloss-affordable' ); ?>" data-explorer-next>
									<?php echo wla_svg_icon( 'arrow' ); ?>
								</button>
							</div>
						</div>
					</article>
				</div>

				<div class="resource-playlist-wrap">
					<div class="resource-playlist">
						<?php foreach ( $guides as $idx => $g ) : ?>
							<button class="resource-playlist-item<?php echo 0 === $idx ? ' is-active' : ''; ?>" type="button" data-playlist-idx="<?php echo esc_attr( $idx ); ?>" aria-pressed="<?php echo 0 === $idx ? 'true' : 'false'; ?>">
								<span class="resource-thumb">
									<?php if ( ! empty( $g['resolvedImage'] ) ) : ?>
										<img src="<?php echo esc_url( $g['resolvedImage'] ); ?>" alt="" loading="lazy" decoding="async" />
									<?php endif; ?>
									<span><?php echo esc_html( sprintf( '%02d', $idx + 1 ) ); ?></span>
								</span>
								<span class="resource-playlist-copy">
									<small><?php echo esc_html( trim( ( $g['category'] ?? '' ) . ' · ' . ( $g['readTime'] ?? '' ) ) ); ?></small>
									<strong><?php echo esc_html( $g['title'] ?? '' ); ?></strong>
									<span><?php echo esc_html( $g['prompt'] ?? '' ); ?></span>
								</span>
								<span class="resource-playlist-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
								<span class="resource-playlist-progress" aria-hidden="true"><?php echo 0 === $idx ? '<i></i>' : ''; ?></span>
							</button>
						<?php endforeach; ?>
					</div>

					<?php if ( ! empty( $a['editorial_title'] ) || ! empty( $a['editorial_body'] ) ) : ?>
						<div class="resource-editorial-note">
							<span class="resource-note-icon"><?php echo wla_svg_icon( 'book' ); ?></span>
							<div>
								<strong><?php echo esc_html( $a['editorial_title'] ); ?></strong>
								<p><?php echo esc_html( $a['editorial_body'] ); ?></p>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( ! empty( $a['strip'] ) ) : ?>
				<div class="resource-topic-strip" data-reveal="up" style="--reveal-delay:0.1s">
					<?php foreach ( (array) $a['strip'] as $s_item ) : ?>
						<span><?php echo wla_svg_icon( 'check' ); ?><p style="margin:0;"><?php echo esc_html( $s_item ); ?></p></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Reviews panel
 * ====================================================================== */

/**
 * Reviews / trust panel.
 *
 * Renders published, consented reviews when any exist; otherwise shows the
 * "no reviews yet" placeholder. It never invents testimonials.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_reviews_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'     => '08',
			'eyebrow'   => 'PATIENT EXPERIENCES',
			'title'     => '',
			'body'      => '',
			'badge'     => '',
			'caption'   => '',
			'link_text' => '',
			'link_url'  => '/patient-reviews',
		)
	);

	$reviews = new WP_Query(
		array(
			'post_type'           => 'wla_review',
			'posts_per_page'      => 3,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	ob_start();
	?>
	<section class="reviews-section section-pad" id="reviews">
		<div class="site-container reviews-panel" data-reveal="scale">
			<div class="review-ornament" aria-hidden="true">“</div>
			<div class="reviews-copy">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<?php if ( ! empty( $a['link_text'] ) ) : ?>
					<div class="section-inline-link">
						<a class="text-link" href="<?php echo wla_resolve_route_url( $a['link_url'] ); ?>">
							<span><?php echo esc_html( $a['link_text'] ); ?></span>
							<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $reviews->have_posts() ) : ?>
				<div class="review-grid">
					<?php
					while ( $reviews->have_posts() ) :
						$reviews->the_post();
						$rating = (int) ( get_post_meta( get_the_ID(), '_wla_rating', true ) ?: 5 );
						$author = (string) ( get_post_meta( get_the_ID(), '_wla_author', true ) ?: get_the_title() );
						$meta   = (string) get_post_meta( get_the_ID(), '_wla_author_meta', true );
						?>
						<blockquote class="review-card">
							<div class="review-stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating */ __( '%d out of 5 stars', 'weightloss-affordable' ), $rating ) ); ?>">
								<?php echo esc_html( str_repeat( '★', max( 0, min( 5, $rating ) ) ) ); ?>
							</div>
							<p><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
							<footer>
								<strong><?php echo esc_html( $author ); ?></strong>
								<span><?php echo esc_html( $meta ); ?></span>
							</footer>
						</blockquote>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<div class="review-placeholder">
					<span class="review-seal"><?php echo wla_svg_icon( 'shield' ); ?></span>
					<span class="review-badge"><?php echo wla_svg_icon( 'chat' ); ?><span><?php echo esc_html( $a['badge'] ); ?></span></span>
					<p><?php echo esc_html( $a['caption'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/* =========================================================================
 * FAQ
 * ====================================================================== */

/**
 * FAQ accordion.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_faq_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'    => '09',
			'eyebrow'  => 'GOOD QUESTIONS',
			'title'    => '',
			'intro'    => '',
			'cta_text' => '',
			'all_text' => '',
			'show_all' => true,
			'faqs'     => array(),
		)
	);
	$faqs = array_values( (array) $a['faqs'] );
	if ( empty( $faqs ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="faq-section section-pad" id="faq" data-wla-faq>
		<div class="site-container faq-grid">
			<div class="faq-heading" data-reveal="left">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
				<div class="faq-heading-links">
					<?php if ( ! empty( $a['cta_text'] ) ) : ?>
						<a class="text-link" href="<?php echo wla_resolve_route_url( '/contact' ); ?>">
							<span><?php echo esc_html( $a['cta_text'] ); ?></span>
							<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $a['show_all'] ) && ! empty( $a['all_text'] ) ) : ?>
						<a class="text-link" href="<?php echo wla_resolve_route_url( '/frequently-asked-questions' ); ?>">
							<span><?php echo esc_html( $a['all_text'] ); ?></span>
							<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="faq-list" data-reveal="right" style="--reveal-delay:0.12s">
				<?php foreach ( $faqs as $idx => $pair ) : ?>
					<?php
					$open = ( 0 === $idx );
					$q    = is_array( $pair ) ? ( $pair[0] ?? '' ) : $pair;
					$ans  = is_array( $pair ) ? ( $pair[1] ?? '' ) : '';
					?>
					<article class="faq-item<?php echo $open ? ' is-open' : ''; ?>" data-faq-item>
						<h3>
							<button type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
								<span class="faq-number"><?php echo esc_html( sprintf( '%02d', $idx + 1 ) ); ?></span>
								<span><?php echo esc_html( $q ); ?></span>
								<span class="faq-toggle" aria-hidden="true"><?php echo $open ? '−' : '+'; ?></span>
							</button>
						</h3>
						<div class="faq-answer"<?php echo $open ? '' : ' hidden'; ?>>
							<p><?php echo wp_kses_post( wpautop( $ans ) ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Contact / form
 * ====================================================================== */

/**
 * Contact or consultation form section.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_contact_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'             => '10',
			'eyebrow'           => 'WE ARE HERE TO HELP',
			'title'             => '',
			'body'              => '',
			'privacy'           => '',
			'checks'            => array(),
			'form_type'         => 'general',
			'form_head_title'   => '',
			'form_head_sub'     => '',
			'form_name_label'   => 'Your name',
			'form_name_ph'      => 'Alex Morgan',
			'form_email_label'  => 'Email address',
			'form_email_ph'     => 'you@example.com',
			'form_topic_label'  => 'What is your question about?',
			'consent_text'      => '',
			'submit_text'       => 'Send message',
			'success_message'   => '',
			'error_message'     => '',
			'topic_options'     => array(),
		)
	);

	$form_type = in_array( $a['form_type'], array( 'general', 'consultation' ), true ) ? $a['form_type'] : 'general';
	$status    = isset( $_GET['wla_form_status'] ) ? sanitize_key( wp_unslash( $_GET['wla_form_status'] ) ) : '';
	$ref       = isset( $_GET['wla_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['wla_ref'] ) ) : '';
	$topics    = (array) $a['topic_options'];
	if ( empty( $topics ) ) {
		$topics = array( array( 'general', 'General information' ) );
	}
	ob_start();
	?>
	<section class="contact-section section-pad" id="contact">
		<div class="site-container contact-grid">
			<div class="contact-copy" data-reveal="left">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<?php if ( ! empty( $a['privacy'] ) ) : ?>
					<div class="contact-note">
						<span><?php echo wla_svg_icon( 'shield' ); ?></span>
						<p><?php echo esc_html( $a['privacy'] ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $a['checks'] ) ) : ?>
					<div class="contact-mini-list">
						<?php foreach ( (array) $a['checks'] as $c ) : ?>
							<span><?php echo wla_svg_icon( 'check' ); ?><span><?php echo esc_html( $c ); ?></span></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="contact-form-card" data-reveal="right" style="--reveal-delay:0.12s">
				<div class="form-card-head">
					<span class="form-step">01</span>
					<div>
						<strong><?php echo esc_html( $a['form_head_title'] ); ?></strong>
						<small><?php echo esc_html( $a['form_head_sub'] ); ?></small>
					</div>
					<span class="form-head-icon"><?php echo wla_svg_icon( 'chat' ); ?></span>
				</div>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-wla-form>
					<input type="hidden" name="action" value="<?php echo esc_attr( 'wla_' . $form_type . '_form' ); ?>" />
					<input type="hidden" name="wla_form_type" value="<?php echo esc_attr( $form_type ); ?>" />
					<input type="hidden" name="wla_source_url" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>" />
					<?php wp_nonce_field( 'wla_form_submit', 'wla_form_nonce' ); ?>
					<input type="hidden" name="wla_started_at" value="<?php echo esc_attr( time() ); ?>" />

					<label class="form-label">
						<span><?php echo esc_html( $a['form_name_label'] ); ?></span>
						<input name="wla_name" type="text" placeholder="<?php echo esc_attr( $a['form_name_ph'] ); ?>" autocomplete="name" maxlength="120" required />
					</label>
					<label class="form-label">
						<span><?php echo esc_html( $a['form_email_label'] ); ?></span>
						<input name="wla_email" type="email" placeholder="<?php echo esc_attr( $a['form_email_ph'] ); ?>" autocomplete="email" maxlength="254" required />
					</label>

					<?php if ( 'consultation' === $form_type ) : ?>
						<label class="form-label">
							<span><?php esc_html_e( 'Phone number', 'weightloss-affordable' ); ?></span>
							<input name="wla_phone" type="tel" autocomplete="tel" maxlength="40" />
						</label>
						<label class="form-label">
							<span><?php esc_html_e( 'State of residence', 'weightloss-affordable' ); ?></span>
							<input name="wla_state" type="text" autocomplete="address-level1" maxlength="60" required />
						</label>
						<label class="form-label">
							<span><?php esc_html_e( 'Preferred contact method', 'weightloss-affordable' ); ?></span>
							<select name="wla_contact_method">
								<option value="email"><?php esc_html_e( 'Email', 'weightloss-affordable' ); ?></option>
								<option value="phone"><?php esc_html_e( 'Phone', 'weightloss-affordable' ); ?></option>
							</select>
						</label>
					<?php endif; ?>

					<label class="form-label">
						<span><?php echo esc_html( $a['form_topic_label'] ); ?></span>
						<select name="wla_topic" required>
							<?php foreach ( $topics as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt[0] ?? '' ); ?>"><?php echo esc_html( $opt[1] ?? '' ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="form-label">
						<span><?php esc_html_e( 'Your message', 'weightloss-affordable' ); ?></span>
						<textarea name="wla_message" rows="4" maxlength="2000" required></textarea>
					</label>

					<label class="honeypot" aria-hidden="true"><?php esc_html_e( 'Company', 'weightloss-affordable' ); ?><input name="wla_company" type="text" tabindex="-1" autocomplete="off" /></label>

					<label class="consent-label">
						<input name="wla_consent" type="checkbox" value="1" required />
						<span><?php echo esc_html( $a['consent_text'] ); ?></span>
					</label>

					<button class="button button-primary form-submit" type="submit">
						<span data-submit-label><?php echo esc_html( $a['submit_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</button>

					<p class="form-feedback success-feedback" role="status" data-form-success<?php echo 'sent' === $status ? '' : ' hidden'; ?>>
						<?php echo esc_html( $a['success_message'] ); ?>
						<?php if ( $ref ) : ?>
							<br /><small><?php echo esc_html( sprintf( /* translators: %s: reference code */ __( 'Your reference: %s', 'weightloss-affordable' ), $ref ) ); ?></small>
						<?php endif; ?>
					</p>
					<p class="form-feedback error-feedback" role="alert" data-form-error<?php echo ( 'error' === $status || 'invalid' === $status ) ? '' : ' hidden'; ?>>
						<?php echo esc_html( $a['error_message'] ); ?>
					</p>
				</form>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Inner page hero
 * ====================================================================== */

/**
 * Inner page hero.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_subpage_hero( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow'    => 'WEIGHTLOSSAFFORDABLE',
			'title'      => '',
			'lead'       => '',
			'badge'      => '',
			'image'      => '',
			'image_alt'  => '',
			'price'      => '',
			'price_note' => '',
			'bullets'    => array(),
		)
	);
	$has_image = ! empty( $a['image'] );
	ob_start();
	?>
	<section class="subpage-hero">
		<div class="hero-backdrop" aria-hidden="true">
			<span class="hero-ring hero-ring-one"></span>
			<span class="hero-ring hero-ring-two"></span>
		</div>
		<div class="site-container subpage-hero-grid<?php echo $has_image ? ' has-image' : ''; ?>">
			<div class="subpage-hero-copy">
				<nav class="subpage-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'weightloss-affordable' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'weightloss-affordable' ); ?></a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</nav>
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h1><?php echo esc_html( $a['title'] ); ?></h1>
				<?php if ( ! empty( $a['lead'] ) ) : ?>
					<p class="hero-description"><?php echo esc_html( $a['lead'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $a['bullets'] ) ) : ?>
					<ul class="check-list subpage-hero-list">
						<?php foreach ( (array) $a['bullets'] as $bullet ) : ?>
							<li><span class="check-badge"><?php echo wla_svg_icon( 'check' ); ?></span><span><?php echo esc_html( $bullet ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $a['price'] ) ) : ?>
					<div class="subpage-price-tag">
						<strong><?php echo esc_html( $a['price'] ); ?></strong>
						<small><?php echo esc_html( $a['price_note'] ); ?></small>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $a['badge'] ) ) : ?>
					<div class="subpage-badge"><?php echo wla_svg_icon( 'shield' ); ?><span><?php echo esc_html( $a['badge'] ); ?></span></div>
				<?php endif; ?>
			</div>

			<?php if ( $has_image ) : ?>
				<div class="subpage-hero-media" data-parallax="0.015">
					<div class="hero-image-shell">
						<div class="hero-image-wrap subpage-image-wrap">
							<img src="<?php echo wla_resolve_asset_url( $a['image'] ); ?>" alt="<?php echo esc_attr( $a['image_alt'] ? $a['image_alt'] : $a['title'] ); ?>" class="hero-image" loading="eager" decoding="async" />
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * CTA band
 * ====================================================================== */

/**
 * Closing call to action band.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_cta_band( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow'        => 'NEXT STEP',
			'title'          => '',
			'body'           => '',
			'button_text'    => '',
			'button_url'     => '/contact',
			'secondary_text' => '',
			'secondary_url'  => '/frequently-asked-questions',
		)
	);
	ob_start();
	?>
	<section class="cta-band-section">
		<div class="site-container">
			<div class="cta-band" data-reveal="scale">
				<div class="cta-band-copy">
					<p class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
					<p><?php echo esc_html( $a['body'] ); ?></p>
				</div>
				<div class="cta-band-actions">
					<?php if ( ! empty( $a['button_text'] ) ) : ?>
						<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>">
							<span><?php echo esc_html( $a['button_text'] ); ?></span>
							<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $a['secondary_text'] ) ) : ?>
						<a class="button button-ghost" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
							<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Explore other pages
 * ====================================================================== */

/**
 * "Continue exploring" grid built from the site's own pages.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_page_nav_strip( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'label'        => 'Continue exploring',
			'back_label'   => 'Back to homepage',
			'exclude'      => array(),
			'current_slug' => '',
		)
	);

	$exclude = array_filter(
		array_merge(
			(array) $a['exclude'],
			array(
				(string) $a['current_slug'],
				'privacy-policy',
				'terms-and-conditions',
				'cookie-policy',
				'accessibility-statement',
				'medical-disclaimer',
				'treatment-safety-information',
				'refund-cancellation-policy',
			)
		)
	);

	$pages = get_pages(
		array(
			'parent'      => 0,
			'sort_column' => 'menu_order,post_title',
			'post_status' => 'publish',
			'number'      => 12,
		)
	);

	if ( empty( $pages ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="page-nav-section">
		<div class="site-container">
			<div class="page-nav-head" data-reveal>
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['label'] ); ?></span></p>
				<a class="text-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span><?php echo esc_html( $a['back_label'] ); ?></span>
					<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</a>
			</div>
			<div class="page-nav-grid">
				<?php
				$shown = 0;
				foreach ( $pages as $page ) :
					if ( in_array( $page->post_name, $exclude, true ) ) {
						continue;
					}
					if ( $shown >= 6 ) {
						break;
					}
					++$shown;
					$blurb = $page->post_excerpt ? $page->post_excerpt : wp_trim_words( wp_strip_all_tags( $page->post_content ), 14 );
					?>
					<a class="page-nav-card" href="<?php echo esc_url( get_permalink( $page ) ); ?>" data-reveal="up" data-tilt style="--reveal-delay:<?php echo esc_attr( number_format( ( $shown - 1 ) * 0.05, 2 ) ); ?>s">
						<span class="page-nav-index" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $shown ) ); ?></span>
						<strong><?php echo esc_html( get_the_title( $page ) ); ?></strong>
						<span class="page-nav-blurb"><?php echo esc_html( $blurb ); ?></span>
						<span class="page-nav-go" aria-hidden="true"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Rich text (legal and information pages)
 * ====================================================================== */

/**
 * Rich text block, used for legal and information pages.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_rich_text_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'   => '01',
			'eyebrow' => 'INFORMATION',
			'title'   => '',
			'updated' => '',
			'body'    => '',
		)
	);
	ob_start();
	?>
	<section class="legal-section section-pad" id="legal">
		<div class="site-container legal-grid">
			<aside class="legal-aside" data-reveal="left">
				<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( ! empty( $a['title'] ) ) : ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $a['updated'] ) ) : ?>
					<p class="legal-updated"><?php echo esc_html( $a['updated'] ); ?></p>
				<?php endif; ?>
				<div class="editorial-note">
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php esc_html_e( 'This page is general information only. Have legal or clinical content reviewed before publication.', 'weightloss-affordable' ); ?></p>
				</div>
			</aside>
			<div class="legal-content" data-reveal="right">
				<?php echo wp_kses_post( $a['body'] ); ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/* =========================================================================
 * Dynamic: treatment grid
 * ====================================================================== */

/**
 * Query published treatments.
 *
 * @param array $args Query args.
 * @return WP_Query
 */
function wla_get_treatments( $args = array() ) {
	return new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'           => 'nrx_treatment',
				'post_status'         => 'publish',
				'posts_per_page'      => 6,
				'orderby'             => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		)
	);
}

/**
 * Render one treatment card.
 *
 * @param int   $post_id Treatment ID.
 * @param array $args    Card args (cta_text, show_price).
 * @return string
 */
function wla_render_treatment_card( $post_id, $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'cta_text'   => __( 'View treatment', 'weightloss-affordable' ),
			'show_price' => true,
		)
	);

	$is_dynamic = function_exists( 'wla_treatment_meta' );
	$summary    = $is_dynamic ? wla_treatment_meta( $post_id, 'short_description', '' ) : '';
	if ( ! $summary ) {
		$summary = get_the_excerpt( $post_id );
	}
	$price      = $is_dynamic ? wla_treatment_meta( $post_id, 'price', '' ) : '';
	$period     = $is_dynamic ? wla_treatment_meta( $post_id, 'billing_period', '' ) : '';
	$ingredient = $is_dynamic ? wla_treatment_meta( $post_id, 'active_ingredient', '' ) : '';
	$mechanism  = $is_dynamic ? wla_treatment_meta( $post_id, 'mechanism', '' ) : '';
	$labels     = array(
		'glp1'    => __( 'GLP-1 receptor agonist', 'weightloss-affordable' ),
		'glp1_gip' => __( 'GLP-1 + GIP dual agonist', 'weightloss-affordable' ),
	);
	$terms      = get_the_terms( $post_id, 'nrx_treatment_category' );
	$tag        = ( $mechanism && isset( $labels[ $mechanism ] ) ) ? $labels[ $mechanism ] : ( ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '' );

	ob_start();
	?>
	<article class="service-card service-card-2 treatment-card" data-reveal="up" data-tilt>
		<span class="service-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', (int) get_post_field( 'menu_order', $post_id ) + 1 ) ); ?></span>
		<div class="service-icon"><?php echo wla_svg_icon( 'pill' ); ?></div>
		<?php if ( $tag ) : ?>
			<span class="card-tag"><?php echo esc_html( $tag ); ?></span>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<?php if ( $summary ) : ?>
			<p><?php echo esc_html( wp_trim_words( $summary, 32 ) ); ?></p>
		<?php endif; ?>
		<?php if ( $a['show_price'] && ( $price || $period ) ) : ?>
			<div class="subpage-price-tag">
				<strong><?php echo esc_html( $price ); ?></strong>
				<small><?php echo esc_html( $period ); ?></small>
			</div>
		<?php endif; ?>
		<?php if ( $ingredient ) : ?>
			<p class="treatment-card__ingredient"><strong><?php esc_html_e( 'Active ingredient:', 'weightloss-affordable' ); ?></strong> <?php echo esc_html( $ingredient ); ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="text-link">
			<span><?php echo esc_html( $a['cta_text'] ); ?></span>
			<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
		</a>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * Dynamic treatment grid.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_treatment_grid_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'index'          => '01',
			'eyebrow'        => 'AVAILABLE TREATMENTS',
			'title'          => '',
			'intro'          => '',
			'limit'          => '6',
			'show_price'     => '1',
			'cta_text'       => __( 'View treatment', 'weightloss-affordable' ),
			'secondary_text' => '',
			'secondary_url'  => '/consultation-eligibility',
			'note'           => '',
		)
	);

	$query = wla_get_treatments( array( 'posts_per_page' => max( 1, (int) $a['limit'] ) ) );

	ob_start();
	?>
	<section class="services-section section-pad treatment-grid-section" id="treatments">
		<div class="site-container">
			<div class="section-header-row" data-reveal>
				<div>
					<?php echo wla_eyebrow( $a['index'], $a['eyebrow'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				</div>
				<?php if ( ! empty( $a['intro'] ) ) : ?>
					<p class="section-side-copy"><?php echo esc_html( $a['intro'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $query->have_posts() ) : ?>
				<div class="service-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						echo wla_render_treatment_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							get_the_ID(),
							array(
								'cta_text'   => $a['cta_text'],
								'show_price' => '1' === (string) $a['show_price'],
							)
						);
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="editorial-note" data-reveal>
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php esc_html_e( 'No treatments have been published yet. Add them under Treatments in the WordPress admin.', 'weightloss-affordable' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $a['note'] ) ) : ?>
				<div class="editorial-note subpage-notice" data-reveal>
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php echo esc_html( $a['note'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $a['secondary_text'] ) ) : ?>
				<div class="treatment-grid-actions">
					<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
						<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/* =========================================================================
 * Dynamic: treatment detail
 * ====================================================================== */

/**
 * Treatment detail block, driven by the treatment record's custom fields.
 *
 * @param array $args Arguments.
 * @return string
 */
function wla_render_treatment_detail_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'cta_text'       => __( 'Request a consultation', 'weightloss-affordable' ),
			'cta_url'        => '/consultation-eligibility',
			'secondary_text' => __( 'Compare treatments', 'weightloss-affordable' ),
			'secondary_url'  => '/compare-treatments',
			'show_pricing'   => '1',
			'show_faqs'      => '1',
			'show_safety'    => '1',
			'note'           => '',
		)
	);

	$post_id = get_the_ID();
	if ( ! $post_id || 'nrx_treatment' !== get_post_type( $post_id ) ) {
		return '';
	}

	$meta = function ( $key, $default = '' ) use ( $post_id ) {
		return function_exists( 'wla_treatment_meta' ) ? wla_treatment_meta( $post_id, $key, $default ) : $default;
	};
	$rows = function ( $key ) use ( $post_id ) {
		return function_exists( 'wla_treatment_rows' ) ? wla_treatment_rows( $post_id, $key ) : array();
	};

	$medication  = $meta( 'medication_name' );
	$ingredient  = $meta( 'active_ingredient' );
	$mechanism   = $meta( 'mechanism' );
	$form        = $meta( 'form' );
	$frequency   = $meta( 'frequency' );
	$supplier    = $meta( 'supplied_by' );
	$price       = $meta( 'price' );
	$compare     = $meta( 'compare_price' );
	$period      = $meta( 'billing_period' );
	$price_note  = $meta( 'price_note' );
	$eligibility = $meta( 'eligibility' );
	$safety      = $meta( 'safety' );
	$disclaimer  = $meta( 'disclaimer' );
	$benefits    = $rows( 'benefits' );
	$variations  = $rows( 'variations' );
	$faqs        = $rows( 'faqs' );
	$content     = get_post_field( 'post_content', $post_id );

	$labels = array(
		'glp1'     => __( 'GLP-1 receptor agonist', 'weightloss-affordable' ),
		'glp1_gip' => __( 'Dual GLP-1 / GIP receptor agonist', 'weightloss-affordable' ),
	);

	$facts = array_filter(
		array(
			__( 'Active ingredient', 'weightloss-affordable' )  => $ingredient,
			__( 'Receptor class', 'weightloss-affordable' )     => isset( $labels[ $mechanism ] ) ? $labels[ $mechanism ] : '',
			__( 'Dosage form', 'weightloss-affordable' )        => $form,
			__( 'Frequency', 'weightloss-affordable' )          => $frequency,
			__( 'Supplied by', 'weightloss-affordable' )        => $supplier,
		)
	);

	ob_start();
	?>
	<section class="treatment-detail section-pad" id="treatment-detail">
		<div class="site-container treatment-detail-grid">
			<div class="treatment-detail-main" data-reveal="left">
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $medication ? $medication : __( 'Treatment information', 'weightloss-affordable' ) ); ?></span></p>
				<?php if ( $content ) : ?>
					<div class="treatment-description"><?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $benefits ) ) : ?>
					<h2 class="treatment-subhead"><?php esc_html_e( 'How it is understood to work', 'weightloss-affordable' ); ?></h2>
					<div class="service-grid treatment-benefits">
						<?php foreach ( $benefits as $benefit ) : ?>
							<article class="service-card service-card-2">
								<div class="service-icon"><?php echo wla_svg_icon( $benefit['icon'] ?? 'spark' ); ?></div>
								<h3><?php echo esc_html( $benefit['title'] ?? '' ); ?></h3>
								<p><?php echo esc_html( $benefit['text'] ?? '' ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
					<p class="treatment-source-note">
						<?php esc_html_e( 'Mechanism of action is described from published literature about this medication class. It is not a claim of effectiveness for any individual, and individual results vary.', 'weightloss-affordable' ); ?>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $variations ) ) : ?>
					<h2 class="treatment-subhead"><?php esc_html_e( 'Available plans', 'weightloss-affordable' ); ?></h2>
					<div class="pricing-rows treatment-variations">
						<?php foreach ( $variations as $variation ) : ?>
							<div class="pricing-row">
								<span>
									<strong><?php echo esc_html( $variation['label'] ?? '' ); ?></strong>
									<?php if ( ! empty( $variation['note'] ) ) : ?>
										<small><?php echo esc_html( $variation['note'] ); ?></small>
									<?php endif; ?>
								</span>
								<strong><?php echo esc_html( trim( ( $variation['price'] ?? '' ) . ' ' . ( $variation['period'] ?? '' ) ) ); ?></strong>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $eligibility ) ) : ?>
					<h2 class="treatment-subhead"><?php esc_html_e( 'Who this may be suitable for', 'weightloss-affordable' ); ?></h2>
					<p><?php echo esc_html( $eligibility ); ?></p>
				<?php endif; ?>

				<?php if ( '1' === (string) $a['show_safety'] && ! empty( $safety ) ) : ?>
					<h2 class="treatment-subhead"><?php esc_html_e( 'Safety information & contraindications', 'weightloss-affordable' ); ?></h2>
					<div class="treatment-safety-block"><?php echo wp_kses_post( wpautop( $safety ) ); ?></div>
				<?php endif; ?>

				<?php if ( '1' === (string) $a['show_faqs'] && ! empty( $faqs ) ) : ?>
					<h2 class="treatment-subhead"><?php esc_html_e( 'Treatment questions', 'weightloss-affordable' ); ?></h2>
					<div class="faq-list treatment-faqs" data-wla-faq>
						<?php foreach ( $faqs as $idx => $faq ) : ?>
							<?php $open = ( 0 === $idx ); ?>
							<article class="faq-item<?php echo $open ? ' is-open' : ''; ?>" data-faq-item>
								<h3>
									<button type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
										<span class="faq-number"><?php echo esc_html( sprintf( '%02d', $idx + 1 ) ); ?></span>
										<span><?php echo esc_html( $faq['question'] ?? '' ); ?></span>
										<span class="faq-toggle" aria-hidden="true"><?php echo $open ? '−' : '+'; ?></span>
									</button>
								</h3>
								<div class="faq-answer"<?php echo $open ? '' : ' hidden'; ?>>
									<p><?php echo esc_html( $faq['answer'] ?? '' ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $disclaimer ) ) : ?>
					<div class="editorial-note treatment-disclaimer">
						<span><?php echo wla_svg_icon( 'shield' ); ?></span>
						<p><?php echo esc_html( $disclaimer ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<aside class="treatment-detail-aside" data-reveal="right">
				<div class="treatment-facts-card">
					<?php if ( $medication ) : ?>
						<h2><?php echo esc_html( $medication ); ?></h2>
					<?php endif; ?>
					<?php if ( '1' === (string) $a['show_pricing'] && ( $price || $period ) ) : ?>
						<div class="treatment-price">
							<?php if ( $compare ) : ?>
								<s><?php echo esc_html( $compare ); ?></s>
							<?php endif; ?>
							<strong><?php echo esc_html( $price ); ?></strong>
							<span><?php echo esc_html( $period ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $facts ) ) : ?>
						<dl class="product-specs treatment-facts">
							<?php foreach ( $facts as $label => $value ) : ?>
								<div class="product-spec">
									<dt><?php echo esc_html( $label ); ?></dt>
									<dd><?php echo esc_html( $value ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['cta_url'] ); ?>">
						<span><?php echo esc_html( $a['cta_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
					<?php if ( ! empty( $a['secondary_text'] ) ) : ?>
						<a class="button button-outline" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
							<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $price_note ) : ?>
						<p class="treatment-price-note"><?php echo esc_html( $price_note ); ?></p>
					<?php endif; ?>
					<p class="treatment-legal-note"><?php esc_html_e( 'A prescription is issued only if a licensed clinician decides treatment is appropriate. Nothing on this page is a guarantee of eligibility or outcome.', 'weightloss-affordable' ); ?></p>
				</div>
				<?php if ( ! empty( $a['note'] ) ) : ?>
					<p class="treatment-aside-note"><?php echo esc_html( $a['note'] ); ?></p>
				<?php endif; ?>
			</aside>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
