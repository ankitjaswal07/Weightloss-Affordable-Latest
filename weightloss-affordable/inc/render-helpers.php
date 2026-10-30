<?php
/**
 * Shared 100% Preview-Identical Render Helpers used by BOTH Theme Templates
 * AND Free Elementor Custom Widgets (zero visual drift, zero duplicate code).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_get_demo_snapshot() {
	static $snapshot = null;
	if ( null !== $snapshot ) {
		return $snapshot;
	}
	$path = WLA_THEME_DIR . '/inc/demo-snapshot.json';
	$raw  = is_readable( $path ) ? file_get_contents( $path ) : false;
	$data = $raw ? json_decode( $raw, true ) : array();
	$snapshot = is_array( $data ) ? $data : array();
	return $snapshot;
}

function wla_svg_icon( $name ) {
	$paths = array(
		'spark'  => 'M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3zm7 12l1 2.5 2.5 1-2.5 1L19 22l-1-2.5-2.5-1 2.5-1L19 15z',
		'heart'  => 'M20.8 8.7c0 5.1-8.8 10.2-8.8 10.2S3.2 13.8 3.2 8.7A4.2 4.2 0 0111 6.1l1 1 1-1a4.2 4.2 0 017.8 2.6z',
		'chat'   => 'M20 11.3a7.8 7.8 0 01-8 7.7 8.5 8.5 0 01-3.5-.7L4 20l1.5-3.6A7.5 7.5 0 014 11.3a8 8 0 0116 0z',
		'book'   => 'M4 5.5A2.5 2.5 0 016.5 3H20v16H6.5A2.5 2.5 0 014 21.5v-16zm0 13A2.5 2.5 0 016.5 16H20M8 7h8m-8 4h6',
		'shield' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11zm-3.5-10 2.3 2.3 4.8-5',
		'arrow'  => 'M5 12h14m-6-6 6 6-6 6',
		'leaf'   => 'M20 4c-8 0-14 3.8-14 10a6 6 0 006 6c6.2 0 8-8 8-16zM4 20c2.8-4.7 6-7.4 11-10',
		'check'  => 'M5 12l4 4L19 6',
		'clock'  => 'M12 22a10 10 0 110-20 10 10 0 010 20zm0-16v6l4 2',
		'globe'  => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z',
		'close'  => 'M18 6L6 18M6 6l12 12',
		'layers' => 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['spark'];
	return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="' . esc_attr( $path ) . '" /></svg>';
}

function wla_render_brand_mark() {
	return '<span class="brand-lockup">'
		. '<span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 44 44" fill="none"><rect x="1" y="1" width="42" height="42" rx="15" fill="#173D36" /><path d="M12 26.3c7.1 1 15-2.9 19.9-11.7 1.5 10.6-3.2 18.1-10.7 18.1-4.1 0-7.2-2.4-9.2-6.4Z" fill="#D7E7D2" /><path d="M12 31.7c4.5-6.7 9.4-10.3 16.2-13.8" stroke="#F7C89A" stroke-width="1.7" stroke-linecap="round" /></svg></span>'
		. '<span class="brand-type"><strong>weightloss</strong><span>affordable</span></span>'
		. '</span>';
}

function wla_resolve_asset_url( $image_or_file ) {
	if ( is_array( $image_or_file ) && ! empty( $image_or_file['url'] ) ) {
		return esc_url( $image_or_file['url'] );
	}
	if ( is_string( $image_or_file ) && 0 === strpos( $image_or_file, 'http' ) ) {
		return esc_url( $image_or_file );
	}
	$filename = basename( (string) $image_or_file );
	if ( '' === $filename ) {
		$filename = 'hero-care.jpg';
	}
	$found = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_wla_demo_asset_file',
			'meta_value'     => $filename,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $found ) ) {
		$url = wp_get_attachment_image_url( absint( $found[0] ), 'full' );
		if ( $url ) {
			return esc_url( $url );
		}
	}
	return esc_url( WLA_THEME_URI . '/assets/images/' . $filename );
}

function wla_resolve_route_url( $href ) {
	$href = trim( (string) $href );
	if ( '' === $href ) {
		return esc_url( home_url( '/' ) );
	}
	if ( 0 === strpos( $href, '#' ) || 0 === strpos( $href, 'http' ) ) {
		return esc_url( $href );
	}
	if ( '/' === $href ) {
		return esc_url( home_url( '/' ) );
	}
	if ( 0 === strpos( $href, '/#' ) ) {
		return esc_url( home_url( '/' ) . ltrim( $href, '/' ) );
	}
	return esc_url( home_url( '/' . trim( $href, '/' ) . '/' ) );
}

function wla_render_hero_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'eyebrow'        => $d['heroEyebrow'] ?? 'A thoughtful place to begin',
			'line1'          => $d['heroLines'][0] ?? 'A clearer,',
			'emphasis_word'  => $d['heroEmphasisWord'] ?? 'calmer',
			'line2_suffix'   => $d['heroAfterEmphasis'] ?? ' way',
			'line3'          => $d['heroLines'][2] ?? 'to begin.',
			'body'           => $d['heroBody'] ?? '',
			'primary_text'   => $d['heroPrimary'] ?? 'Explore our approach',
			'primary_url'    => '#about',
			'secondary_text' => $d['heroSecondary'] ?? 'See how it works',
			'secondary_url'  => '#process',
			'image'          => 'hero-care.jpg',
			'image_alt'      => 'A woman connecting with a healthcare professional in a calm video visit at home',
			'lang_mini'      => $d['heroLangMini'] ?? 'LIVE EN · ES',
			'lang_title'     => $d['heroLangTitle'] ?? 'Instant site translator',
			'float_badge'    => $d['imageBadge'] ?? 'Your goals. Your pace.',
			'float_sub'      => $d['imageSub'] ?? 'Information made easier to navigate',
			'orbit_text'     => $d['orbitText'] ?? 'CLEAR INFORMATION • YOUR PACE • ',
			'scroll_label'   => $d['scrollLabel'] ?? 'Scroll to explore',
			'proof'          => $d['heroProof'] ?? array(),
			'marquee'        => $d['marqueeItems'] ?? array(),
		)
	);
	$img_url = wla_resolve_asset_url( $a['image'] );
	ob_start();
	?>
	<section class="hero-section" id="home">
		<div class="hero-backdrop" aria-hidden="true">
			<span class="hero-word">begin</span>
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

				<div class="hero-proof">
					<?php foreach ( $a['proof'] as $item ) : ?>
						<div class="hero-proof-item">
							<strong><?php echo esc_html( $item[0] ?? '' ); ?></strong>
							<span><?php echo esc_html( $item[1] ?? '' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="hero-visual">
				<div class="hero-image-shell" data-parallax="0.018">
					<div class="hero-image-wrap">
						<img src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" class="hero-image" loading="eager" fetchpriority="high" />
						<span class="hero-image-shade" aria-hidden="true"></span>
					</div>
				</div>

				<div class="hero-language-card">
					<span class="mini-label"><?php echo esc_html( $a['lang_mini'] ); ?></span>
					<strong><?php echo esc_html( $a['lang_title'] ); ?></strong>
					<span class="language-dots"><i></i><i></i><i></i></span>
				</div>

				<div class="image-float-card">
					<span class="float-icon"><?php echo wla_svg_icon( 'heart' ); ?></span>
					<span>
						<strong><?php echo esc_html( $a['float_badge'] ); ?></strong>
						<small><?php echo esc_html( $a['float_sub'] ); ?></small>
					</span>
					<span class="float-spark">✳</span>
				</div>

				<div class="orbit-badge" aria-hidden="true">
					<svg viewBox="0 0 100 100">
						<defs><path id="wla-orbit-circle" d="M50,50 m-38,0 a38,38 0 1,1 76,0 a38,38 0 1,1 -76,0" /></defs>
						<text><textPath href="#wla-orbit-circle"><?php echo esc_html( $a['orbit_text'] ); ?></textPath></text>
					</svg>
					<span>✳</span>
				</div>
			</div>
		</div>

		<div class="site-container hero-scroll">
			<span><?php echo esc_html( $a['scroll_label'] ); ?></span>
			<i aria-hidden="true"></i>
		</div>

		<div class="values-marquee">
			<div class="marquee-track">
				<?php for ( $g = 0; $g < 2; $g++ ) : ?>
					<div class="marquee-group"<?php echo 1 === $g ? ' aria-hidden="true"' : ''; ?>>
						<?php foreach ( $a['marquee'] as $m_item ) : ?>
							<span><?php echo esc_html( $m_item ); ?><i>✳</i></span>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_intro_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'      => '01',
			'eyebrow'    => $d['aboutEyebrow'] ?? 'OUR POINT OF VIEW',
			'title'      => $d['aboutTitle'] ?? 'Your goals deserve clarity — not pressure.',
			'body'       => $d['aboutBody'] ?? '',
			'note'       => $d['aboutNote'] ?? '',
			'link_label' => $d['aboutLinkLabel'] ?? 'Read full About Us page',
			'link_url'   => '/about',
		)
	);
	ob_start();
	?>
	<section class="intro-section section-pad" id="about">
		<div class="site-container intro-grid">
			<div class="section-heading" data-reveal="left">
				<p class="eyebrow">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
			</div>
			<div class="intro-copy" data-reveal="right" style="--reveal-delay:0.12s">
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<div class="editorial-note">
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php echo esc_html( $a['note'] ); ?></p>
				</div>
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

function wla_render_services_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'   => '02',
			'eyebrow' => $d['servicesEyebrow'] ?? "WHAT YOU'LL FIND HERE",
			'title'   => $d['servicesTitle'] ?? 'Useful guidance, without assumptions.',
			'intro'   => $d['servicesIntro'] ?? '',
			'cards'   => $d['services'] ?? array(),
			'notice'  => '',
		)
	);
	ob_start();
	?>
	<section class="services-section section-pad" id="services">
		<div class="site-container">
			<div class="section-header-row" data-reveal>
				<div>
					<p class="eyebrow">
						<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
						<span class="eyebrow-line"></span>
						<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
					</p>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				</div>
				<?php if ( ! empty( $a['intro'] ) ) : ?>
					<p class="section-side-copy"><?php echo esc_html( $a['intro'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="service-grid">
				<?php foreach ( $a['cards'] as $idx => $card ) :
					$variant = ! empty( $card['featured'] ) ? 1 : ( ( 0 === $idx ) ? 1 : ( ( 2 === $idx % 3 ) ? 3 : 2 ) );
					if ( isset( $card['pricePlaceholder'] ) && empty( $card['featured'] ) && 0 === $idx ) {
						$variant = 2;
					}
					$icon = $card['icon'] ?? ( 0 === $idx ? 'book' : ( 1 === $idx ? 'heart' : 'chat' ) );
					$text = $card['text'] ?? ( $card['body'] ?? '' );
					?>
					<article class="service-card service-card-<?php echo esc_attr( $variant ); ?>" data-reveal="up" data-tilt style="--reveal-delay:<?php echo esc_attr( number_format( $idx * 0.1, 2 ) ); ?>s">
						<span class="service-number" aria-hidden="true">0<?php echo esc_html( $idx + 1 ); ?></span>
						<?php if ( empty( $card['pricePlaceholder'] ) ) : ?>
							<div class="service-icon"><?php echo wla_svg_icon( $icon ); ?></div>
						<?php endif; ?>
						<span class="card-tag"><?php echo esc_html( $card['tag'] ?? 'Information' ); ?></span>
						<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
						<?php if ( ! empty( $card['pricePlaceholder'] ) ) : ?>
							<div class="subpage-price-tag">
								<strong><?php echo esc_html( $card['pricePlaceholder'] ); ?></strong>
								<small><?php echo esc_html( $card['billingPlaceholder'] ?? '' ); ?></small>
							</div>
						<?php endif; ?>
						<p><?php echo esc_html( $text ); ?></p>
						<?php if ( ! empty( $card['bullets'] ) && is_array( $card['bullets'] ) ) : ?>
							<ul class="subpage-card-bullets">
								<?php foreach ( $card['bullets'] as $b ) : ?>
									<li><?php echo wla_svg_icon( 'check' ); ?><span><?php echo esc_html( $b ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php elseif ( ! empty( $card['cta'] ) ) : ?>
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

function wla_render_journey_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'          => '03',
			'window_left'    => $d['processWindowLeft'] ?? 'Your information journey',
			'window_right'   => $d['processWindowRight'] ?? '04 stages',
			'eyebrow'        => $d['processEyebrow'] ?? 'A SIMPLE STARTING POINT',
			'title'          => $d['processTitle'] ?? 'A clearer path, one step at a time.',
			'intro'          => $d['processIntro'] ?? '',
			'chips'          => $d['processChips'] ?? array( 'Four clear stages', 'Illustrative demo flow', 'No medical intake' ),
			'steps'          => $d['steps'] ?? array(),
			'progress_label' => $d['processProgressLabel'] ?? 'Journey stage',
			'footnote'       => $d['processFootnote'] ?? '',
			'cta_text'       => $d['processCta'] ?? 'Start with a question',
			'cta_url'        => '#contact',
		)
	);
	$step_icons = array( 'book', 'chat', 'shield', 'heart' );
	ob_start();
	?>
	<section class="process-section journey-section section-pad" id="process" style="--journey-progress:0.13" data-wla-journey>
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
					<p class="eyebrow">
						<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
						<span class="eyebrow-line"></span>
						<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
						<span class="eyebrow-line"></span>
					</p>
					<h2><?php echo esc_html( $a['title'] ); ?></h2>
					<p><?php echo esc_html( $a['intro'] ); ?></p>
					<div class="journey-summary-chips">
						<?php foreach ( $a['chips'] as $ci => $chip ) : ?>
							<span><?php echo wla_svg_icon( 0 === $ci ? 'spark' : ( 1 === $ci ? 'book' : 'shield' ) ); ?><p style="margin:0;"><?php echo esc_html( $chip ); ?></p></span>
						<?php endforeach; ?>
					</div>
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
						<?php foreach ( $a['steps'] as $idx => $s ) :
							$num    = $s[0] ?? sprintf( '%02d', $idx + 1 );
							$stitle = $s[1] ?? '';
							$sdesc  = $s[2] ?? '';
							$smeta  = $s[3] ?? '';
							$sdet   = $s[4] ?? '';
							?>
							<li class="journey-step<?php echo 0 === $idx ? ' is-active' : ''; ?>" data-step-index="<?php echo esc_attr( $idx ); ?>" data-step-num="<?php echo esc_attr( $num ); ?>" data-reveal="up" style="--reveal-delay:<?php echo esc_attr( number_format( $idx * 0.11, 2 ) ); ?>s">
								<article class="journey-card" data-tilt>
									<span class="journey-card-ghost" aria-hidden="true"><?php echo esc_html( $num ); ?></span>
									<div class="journey-card-top">
										<button class="journey-number-button" type="button" aria-pressed="<?php echo 0 === $idx ? 'true' : 'false'; ?>">
											<span class="step-num-text"><?php echo esc_html( $num ); ?></span>
										</button>
										<span class="journey-card-icon"><?php echo wla_svg_icon( $step_icons[ $idx % 4 ] ); ?></span>
									</div>
									<p class="journey-card-kicker"><?php echo esc_html( $sdet ); ?></p>
									<h3><?php echo esc_html( $stitle ); ?></h3>
									<p class="journey-card-description"><?php echo esc_html( $sdesc ); ?></p>
									<div class="journey-card-footer">
										<span class="journey-chip"><?php echo wla_svg_icon( 2 === $idx ? 'shield' : 'clock' ); ?><span><?php echo esc_html( $smeta ); ?></span></span>
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
							<strong data-journey-counter>01 / 0<?php echo esc_html( count( $a['steps'] ) ); ?></strong>
						</div>
						<div class="journey-progress"><span data-journey-bar style="transform:scaleX(0.25)"></span></div>
					</div>
					<p class="journey-footnote"><?php echo wla_svg_icon( 'shield' ); ?><span><?php echo esc_html( $a['footnote'] ); ?></span></p>
					<a class="button button-primary journey-cta" href="<?php echo wla_resolve_route_url( $a['cta_url'] ); ?>">
						<span><?php echo esc_html( $a['cta_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_approach_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'         => '04',
			'eyebrow'       => $d['approachEyebrow'] ?? 'A MORE HUMAN APPROACH',
			'title'         => $d['approachTitle'] ?? 'Careful information can help you feel more prepared.',
			'body'          => $d['approachBody'] ?? '',
			'bullets'       => $d['approachBullets'] ?? array(),
			'button_text'   => $d['approachButton'] ?? 'Read our FAQs',
			'button_url'    => '#faq',
			'image'         => 'lifestyle-walk.jpg',
			'image_alt'     => 'Two friends enjoying a relaxed walk together outdoors',
			'photo_caption' => $d['approachPhotoCaption'] ?? 'Wellbeing is personal.',
		)
	);
	$img_url = wla_resolve_asset_url( $a['image'] );
	ob_start();
	?>
	<section class="approach-section section-pad" id="approach">
		<div class="site-container approach-grid">
			<div class="approach-photo" data-parallax="0.022">
				<img src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" loading="lazy" />
				<span class="approach-photo-ring" aria-hidden="true"></span>
				<div class="photo-caption">
					<span class="caption-number">01</span>
					<span><?php echo esc_html( $a['photo_caption'] ); ?></span>
				</div>
			</div>
			<div class="approach-copy" data-reveal="right" style="--reveal-delay:0.12s">
				<p class="eyebrow">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p class="approach-description"><?php echo esc_html( $a['body'] ); ?></p>
				<ul class="check-list">
					<?php foreach ( $a['bullets'] as $item ) : ?>
						<li><span class="check-badge"><?php echo wla_svg_icon( 'check' ); ?></span><span><?php echo esc_html( $item ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<a class="button button-outline" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>">
					<span><?php echo esc_html( $a['button_text'] ); ?></span>
					<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</a>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_pricing_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'       => '05',
			'eyebrow'     => $d['pricingEyebrow'] ?? 'PRICING & AVAILABILITY',
			'title'       => $d['pricingTitle'] ?? 'The details should be clear before you decide.',
			'body'        => $d['pricingBody'] ?? '',
			'button_text' => $d['pricingButton'] ?? 'View package placeholders',
			'button_url'  => '/pricing-and-packages',
			'status'      => $d['pricingStatus'] ?? 'Business details pending verification',
			'sub'         => $d['pricingSub'] ?? 'Transparency before commitment',
			'rows'        => $d['pricingRows'] ?? array(),
			'note'        => $d['pricingNote'] ?? '',
		)
	);
	ob_start();
	?>
	<section class="pricing-section section-pad" id="pricing">
		<div class="site-container pricing-card" data-reveal="scale">
			<div class="pricing-emblem"><?php echo wla_svg_icon( 'spark' ); ?></div>
			<div class="pricing-copy">
				<p class="eyebrow">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<a href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>" class="text-link">
					<span><?php echo esc_html( $a['button_text'] ); ?></span>
					<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</a>
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
					<?php foreach ( $a['rows'] as $row ) : ?>
						<div class="pricing-row">
							<span><?php echo esc_html( $row[0] ?? '' ); ?></span>
							<strong><?php echo esc_html( $row[1] ?? '' ); ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
				<p><?php echo esc_html( $a['note'] ); ?></p>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}



function wla_render_resources_explorer_section( $args = array() ) {

	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();

	$a = wp_parse_args(
		$args,
		array(
			'index'           => '06',
			'eyebrow'         => $d['resourcesEyebrow'] ?? 'LEARN AT YOUR OWN PACE',
			'title'           => $d['resourcesTitle'] ?? 'Questions worth exploring.',
			'lead'            => $d['resourcesLead'] ?? '',
			'all_link'        => $d['resourcesAllLink'] ?? 'Open full resource library',
			'all_url'         => '/resources',
			'featured_label'  => $d['resourceFeaturedLabel'] ?? 'Featured educational guide',
			'read_button'     => $d['resourceReadButton'] ?? 'Read full guide',
			'ask_button'      => $d['resourceAskButton'] ?? 'Ask about this topic',
			'q_heading'       => $d['resourceQuestionsHeading'] ?? 'Questions to bring',
			'q_note'          => $d['resourceQuestionsNote'] ?? '',
			'editorial_title' => $d['resourceEditorialTitle'] ?? '',
			'editorial_body'  => $d['resourceEditorialBody'] ?? '',
			'strip'           => $d['resourceStrip'] ?? array(),
			'guides'          => $d['resources'] ?? array(),
		)
	);

	$guides = array_values(
		is_array( $a['guides'] ) ? $a['guides'] : array()
	);

	/*
	 * Fetch WordPress post Featured Images.
	 * Supported guide keys: post_id, postId, id, slug.
	 */
	foreach ( $guides as $gi => $g ) {

		$post_id = absint(
			$g['post_id'] ?? $g['postId'] ?? 0
		);

		// Use id only if it is a valid WordPress post ID.
		if ( ! $post_id && ! empty( $g['id'] ) ) {
			$candidate = absint( $g['id'] );

			if ( $candidate && get_post( $candidate ) ) {
				$post_id = $candidate;
			}
		}

		// If no post ID is supplied, try the guide slug.
		if ( ! $post_id && ! empty( $g['slug'] ) ) {
			$matched_post = get_page_by_path(
				sanitize_title( $g['slug'] ),
				OBJECT,
				array( 'post', 'page', 'resources', 'guides' )
			);

			if ( $matched_post ) {
				$post_id = absint( $matched_post->ID );
			}
		}

		// Resolve the Featured Image from the matched WordPress post.
		$image_url = '';

		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$image_url = get_the_post_thumbnail_url(
				$post_id,
				'large'
			);
		}

		// Preserve the configured image only when no Featured Image exists.
		if ( ! $image_url && ! empty( $g['image'] ) ) {
			$image_url = wla_resolve_asset_url( $g['image'] );
		}

		$guides[ $gi ]['post_id'] = $post_id;
		$guides[ $gi ]['resolvedImage'] = esc_url_raw(
			$image_url ?: ''
		);

		// Use the Media Library alt text when available.
		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$media_alt    = get_post_meta(
				$thumbnail_id,
				'_wp_attachment_image_alt',
				true
			);

			if ( $media_alt ) {
				$guides[ $gi ]['alt'] = $media_alt;
			}
		}

		if ( empty( $guides[ $gi ]['alt'] ) ) {
			$guides[ $gi ]['alt'] = $g['title'] ?? '';
		}
	}

	$first       = $guides[0] ?? array();
	$first_image = $first['resolvedImage'] ?? '';
	$count       = count( $guides );

	ob_start();
	?>

	<section
		class="resources-section section-pad"
		id="resources"
		data-wla-explorer
		data-guides="<?php echo esc_attr( wp_json_encode( $guides ) ); ?>"
	>
		<span class="resources-ghost" data-parallax="0.012" aria-hidden="true">learn</span>

		<div class="site-container">

			<div class="section-header-row resources-header" data-reveal>
				<div>
					<p class="eyebrow">
						<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
						<span class="eyebrow-line"></span>
						<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
					</p>

					<h2><?php echo esc_html( $a['title'] ); ?></h2>
				</div>

				<div class="resources-header-side">
					<p><?php echo esc_html( $a['lead'] ); ?></p>

					<a
						href="<?php echo esc_url( wla_resolve_route_url( $a['all_url'] ) ); ?>"
						class="text-link resource-all"
					>
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
								<img
									src="<?php echo esc_url( $first_image ); ?>"
									alt="<?php echo esc_attr( $first['alt'] ?? '' ); ?>"
									class="resource-stage-image"
									data-stage-img
									decoding="async"
								/>
							<?php else : ?>
								<img
									src=""
									alt=""
									class="resource-stage-image"
									data-stage-img
									hidden
								/>
							<?php endif; ?>

							<span class="resource-stage-shade"></span>
						</div>

						<div class="resource-stage-inner">
							<div class="resource-stage-toprow">
								<div class="resource-stage-rail">
									<span>
										<i></i>
										<span data-stage-featured-label><?php echo esc_html( $a['featured_label'] ); ?></span>
									</span>

									<span>
										<span data-stage-idx>01</span>
										<em>/</em>
										<?php echo esc_html( sprintf( '%02d', $count ) ); ?>
									</span>
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

										<button type="button" class="resource-stage-secondary" data-ask-guide-topic>
											<?php echo esc_html( $a['ask_button'] ); ?>
										</button>
									</div>
								</div>

								<aside class="resource-question-card">
									<div class="resource-question-head">
										<span><?php echo wla_svg_icon( 'chat' ); ?></span>
										<strong><?php echo esc_html( $a['q_heading'] ); ?></strong>
									</div>

									<ul data-stage-questions>
										<?php foreach ( ( $first['questions'] ?? array() ) as $qi => $q ) : ?>
											<li>
												<span><?php echo esc_html( sprintf( '%02d', $qi + 1 ) ); ?></span>
												<span><?php echo esc_html( $q ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>

									<p><?php echo esc_html( $a['q_note'] ); ?></p>
								</aside>
							</div>

							<div class="resource-stage-controls">
								<button type="button" aria-label="Previous guide" data-explorer-prev>
									<span class="resource-prev-icon"><?php echo wla_svg_icon( 'arrow' ); ?></span>
								</button>

								<button class="resource-autoplay-button" type="button" aria-pressed="false" data-explorer-toggle>
									<span class="pause-icon" aria-hidden="true"><i></i><i></i></span>
									<span data-explorer-toggle-label>Pause</span>
								</button>

								<button type="button" aria-label="Next guide" data-explorer-next>
									<?php echo wla_svg_icon( 'arrow' ); ?>
								</button>
							</div>
						</div>
					</article>
				</div>

				<div class="resource-playlist-wrap">
					<div class="resource-playlist">

						<?php foreach ( $guides as $idx => $g ) : ?>
							<button
								class="resource-playlist-item<?php echo 0 === $idx ? ' is-active' : ''; ?>"
								type="button"
								data-playlist-idx="<?php echo esc_attr( $idx ); ?>"
								aria-pressed="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
								aria-current="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
							>
								<span class="resource-thumb">
									<?php if ( ! empty( $g['resolvedImage'] ) ) : ?>
										<img
											src="<?php echo esc_url( $g['resolvedImage'] ); ?>"
											alt=""
											loading="lazy"
											decoding="async"
										/>
									<?php endif; ?>

									<span><?php echo esc_html( sprintf( '%02d', $idx + 1 ) ); ?></span>
								</span>

								<span class="resource-playlist-copy">
									<small>
										<?php
										echo esc_html(
											( $g['category'] ?? '' ) . ' · ' .
											( $g['readTime'] ?? '' )
										);
										?>
									</small>

									<strong><?php echo esc_html( $g['title'] ?? '' ); ?></strong>
									<span><?php echo esc_html( $g['prompt'] ?? '' ); ?></span>
								</span>

								<span class="resource-playlist-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>

								<span class="resource-playlist-progress" aria-hidden="true">
									<?php if ( 0 === $idx ) : ?>
										<i></i>
									<?php endif; ?>
								</span>
							</button>
						<?php endforeach; ?>

					</div>

					<div class="resource-editorial-note">
						<span class="resource-note-icon"><?php echo wla_svg_icon( 'book' ); ?></span>
						<div>
							<strong><?php echo esc_html( $a['editorial_title'] ); ?></strong>
							<p><?php echo esc_html( $a['editorial_body'] ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="resource-topic-strip" data-reveal="up" style="--reveal-delay:0.1s">
				<?php foreach ( (array) $a['strip'] as $s_item ) : ?>
					<span>
						<?php echo wla_svg_icon( 'check' ); ?>
						<p style="margin:0;"><?php echo esc_html( $s_item ); ?></p>
					</span>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php

	return ob_get_clean();
}



function wla_render_reviews_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'     => '07',
			'eyebrow'   => $d['reviewsEyebrow'] ?? 'REAL PEOPLE, REAL RESPECT',
			'title'     => $d['reviewsTitle'] ?? "A place for authentic voices — when they're approved.",
			'body'      => $d['reviewsBody'] ?? '',
			'badge'     => $d['reviewBadge'] ?? 'Verified review slot reserved',
			'caption'   => $d['reviewCaption'] ?? '',
			'link_text' => $d['reviewsLink'] ?? 'See sample placeholder layout',
			'link_url'  => '/patient-reviews',
		)
	);
	ob_start();
	?>
	<section class="reviews-section section-pad" id="reviews">
		<div class="site-container reviews-panel" data-reveal="scale">
			<div class="review-ornament" aria-hidden="true">“</div>
			<div class="reviews-copy">
				<p class="eyebrow">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<div class="section-inline-link">
					<a class="text-link" href="<?php echo wla_resolve_route_url( $a['link_url'] ); ?>">
						<span><?php echo esc_html( $a['link_text'] ); ?></span>
						<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				</div>
			</div>
			<div class="review-placeholder">
				<span class="review-seal"><?php echo wla_svg_icon( 'shield' ); ?></span>
				<span class="review-badge"><?php echo wla_svg_icon( 'chat' ); ?><span><?php echo esc_html( $a['badge'] ); ?></span></span>
				<p><?php echo esc_html( $a['caption'] ); ?></p>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_faq_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'    => '08',
			'eyebrow'  => $d['faqEyebrow'] ?? 'GOOD QUESTIONS',
			'title'    => $d['faqTitle'] ?? 'A few things you may be wondering.',
			'intro'    => $d['faqIntro'] ?? '',
			'cta_text' => $d['cta'] ?? 'Ask a question',
			'all_text' => $d['faqAllLink'] ?? 'Open complete FAQ page',
			'show_all' => true,
			'faqs'     => $d['faqs'] ?? array(),
		)
	);
	ob_start();
	?>
	<section class="faq-section section-pad" id="faq" data-wla-faq>
		<div class="site-container faq-grid">
			<div class="faq-heading" data-reveal="left">
				<p class="eyebrow">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
				<div class="faq-heading-links">
					<a class="text-link" href="#contact">
						<span><?php echo esc_html( $a['cta_text'] ); ?></span>
						<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
					<?php if ( ! empty( $a['show_all'] ) ) : ?>
						<a class="text-link" href="<?php echo wla_resolve_route_url( '/frequently-asked-questions' ); ?>">
							<span><?php echo esc_html( $a['all_text'] ); ?></span>
							<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="faq-list" data-reveal="right" style="--reveal-delay:0.12s">
				<?php foreach ( $a['faqs'] as $idx => $pair ) :
					$open = ( 0 === $idx );
					$num  = sprintf( '%02d', $idx + 1 );
					?>
					<article class="faq-item<?php echo $open ? ' is-open' : ''; ?>" data-faq-item>
						<h3>
							<button type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
								<span class="faq-number"><?php echo esc_html( $num ); ?></span>
								<span><?php echo esc_html( $pair[0] ?? '' ); ?></span>
								<span class="faq-toggle" aria-hidden="true"><?php echo $open ? '−' : '+'; ?></span>
							</button>
						</h3>
						<div class="faq-answer"<?php echo $open ? '' : ' hidden'; ?>>
							<p><?php echo wp_kses_post( $pair[1] ?? '' ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_contact_section( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'index'               => '09',
			'eyebrow'             => $d['contactEyebrow'] ?? "WE'RE HERE TO HELP YOU FIND INFORMATION",
			'title'               => $d['contactTitle'] ?? 'What would you like to know?',
			'body'                => $d['contactBody'] ?? '',
			'privacy'             => $d['formPrivacy'] ?? '',
			'checks'              => $d['contactChecks'] ?? array( 'General questions only', 'No medical intake', 'Instant confirmation' ),
			'form_head_title'     => $d['formHeadTitle'] ?? 'General inquiry',
			'form_head_sub'       => $d['formHeadSub'] ?? 'Non-clinical website questions',
			'form_name_label'     => $d['formName'] ?? 'Your name',
			'form_name_ph'        => $d['formNamePlaceholder'] ?? 'Elena Morales',
			'form_email_label'    => $d['formEmail'] ?? 'Email address',
			'form_email_ph'       => $d['formEmailPlaceholder'] ?? 'elena@example.com',
			'form_topic_label'    => $d['formTopic'] ?? 'What is your question about?',
			'topic_options'       => $d['topicOptions'] ?? array(
				array( 'general', 'General information' ),
				array( 'services', 'Services and availability' ),
				array( 'language', 'Language support' ),
				array( 'website', 'Website help' ),
			),
			'consent_text'        => $d['consent'] ?? '',
			'submit_text'         => $d['formButton'] ?? 'Send general inquiry',
			'success_message'     => $d['success'] ?? 'Thank you — your general inquiry has been saved.',
			'error_message'       => $d['error'] ?? 'Please check all required fields and try again.',
		)
	);
	$status = isset( $_GET['wla_form_status'] ) ? sanitize_key( wp_unslash( $_GET['wla_form_status'] ) ) : '';
	ob_start();
	?>
	<section class="contact-section section-pad" id="contact">
		<div class="site-container contact-grid">
			<div class="contact-copy" data-reveal="left">
				<p class="eyebrow eyebrow-light">
					<span class="section-index"><?php echo esc_html( $a['index'] ); ?></span>
					<span class="eyebrow-line"></span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</p>
				<h2><?php echo esc_html( $a['title'] ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<div class="contact-note">
					<span><?php echo wla_svg_icon( 'shield' ); ?></span>
					<p><?php echo esc_html( $a['privacy'] ); ?></p>
				</div>
				<div class="contact-mini-list">
					<?php foreach ( $a['checks'] as $c ) : ?>
						<span><?php echo wla_svg_icon( 'check' ); ?><span><?php echo esc_html( $c ); ?></span></span>
					<?php endforeach; ?>
				</div>
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

				<form data-wla-contact-form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wla_general_contact" />
					<?php wp_nonce_field( 'wla_general_contact', 'wla_contact_nonce' ); ?>
					<label class="form-label">
						<span><?php echo esc_html( $a['form_name_label'] ); ?></span>
						<input name="wla_name" type="text" placeholder="<?php echo esc_attr( $a['form_name_ph'] ); ?>" autocomplete="name" maxlength="120" required />
					</label>
					<label class="form-label">
						<span><?php echo esc_html( $a['form_email_label'] ); ?></span>
						<input name="wla_email" type="email" placeholder="<?php echo esc_attr( $a['form_email_ph'] ); ?>" autocomplete="email" maxlength="254" required />
					</label>
					<label class="form-label">
						<span><?php echo esc_html( $a['form_topic_label'] ); ?></span>
						<select name="wla_topic" data-wla-topic-select required>
							<?php foreach ( $a['topic_options'] as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt[0] ); ?>"><?php echo esc_html( $opt[1] ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="honeypot" aria-hidden="true">Company<input name="wla_company" type="text" tabindex="-1" autocomplete="off" /></label>
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

function wla_render_subpage_hero( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow'   => 'WEIGHTLOSSAFFORDABLE',
			'title'     => '',
			'lead'      => '',
			'badge'     => 'Verified information & transparent disclosures',
			'image'     => '',
			'image_alt' => '',
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
				<div class="subpage-breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $a['eyebrow'] ); ?></span>
				</div>
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h1><?php echo esc_html( $a['title'] ); ?></h1>
				<?php if ( ! empty( $a['lead'] ) ) : ?>
					<p class="hero-description"><?php echo esc_html( $a['lead'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $a['badge'] ) ) : ?>
					<div class="subpage-badge"><?php echo wla_svg_icon( 'shield' ); ?><span><?php echo esc_html( $a['badge'] ); ?></span></div>
				<?php endif; ?>
			</div>

			<?php if ( $has_image ) : ?>
				<div class="subpage-hero-media" data-parallax="0.015">
					<div class="hero-image-shell">
						<div class="hero-image-wrap subpage-image-wrap">
							<img src="<?php echo wla_resolve_asset_url( $a['image'] ); ?>" alt="<?php echo esc_attr( $a['image_alt'] ? $a['image_alt'] : $a['title'] ); ?>" class="hero-image" loading="eager" />
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_full_homepage() {
	return wla_render_hero_section()
		. wla_render_intro_section()
		. wla_render_services_section()
		. wla_render_journey_section()
		. wla_render_approach_section()
		. wla_render_pricing_section()
		. wla_render_resources_explorer_section()
		. wla_render_reviews_section()
		. wla_render_faq_section()
		. wla_render_cta_band()
		. wla_render_page_nav_strip( 'home' );
}

function wla_render_snapshot_subpage( $slug ) {
	$snap     = wla_get_demo_snapshot();
	$subpages = $snap['subpages'] ?? array();
	$found    = null;
	foreach ( $subpages as $sp ) {
		if ( ( $sp['slug'] ?? '' ) === $slug ) {
			$found = $sp;
			break;
		}
	}
	if ( ! $found ) {
		return '';
	}
	$out = wla_render_subpage_hero(
		array(
			'eyebrow'   => $found['eyebrow'] ?? '',
			'title'     => $found['title'] ?? '',
			'lead'      => $found['lead'] ?? '',
			'badge'     => $found['badge'] ?? '',
			'image'     => $found['image'] ?? '',
			'image_alt' => $found['imageAlt'] ?? '',
		)
	);
	foreach ( ( $found['sections'] ?? array() ) as $idx => $sec ) {
		if ( ! empty( $sec['steps'] ) ) {
			$out .= wla_render_journey_section(
				array(
					'index'   => sprintf( '%02d', $idx + 1 ),
					'eyebrow' => $sec['eyebrow'] ?? '',
					'title'   => $sec['heading'] ?? '',
					'intro'   => $sec['body'] ?? '',
					'steps'   => $sec['steps'],
				)
			);
		} elseif ( ! empty( $sec['faqs'] ) ) {
			$out .= wla_render_faq_section(
				array(
					'index'    => sprintf( '%02d', $idx + 1 ),
					'show_all' => false,
					'faqs'     => $sec['faqs'],
				)
			);
		} else {
			$out .= wla_render_services_section(
				array(
					'index'   => sprintf( '%02d', $idx + 1 ),
					'eyebrow' => $sec['eyebrow'] ?? '',
					'title'   => $sec['heading'] ?? '',
					'intro'   => $sec['body'] ?? '',
					'cards'   => $sec['cards'] ?? array(),
					'notice'  => $sec['notice'] ?? '',
				)
			);
		}
	}
	if ( 'resources' === $slug ) {
		$out .= wla_render_resources_explorer_section( array( 'index' => '02' ) );
	}

	// Multi-page behaviour: only the Contact page renders the inquiry form.
	// Every other page closes with a CTA band that routes to /contact, followed
	// by the "continue exploring" page grid.
	if ( 'contact' === $slug ) {
		$out .= wla_render_contact_section( array( 'index' => '02' ) );
	} else {
		$out .= wla_render_cta_band(
			array(
				'title'       => $found['ctaHeading'] ?? '',
				'body'        => $found['ctaBody'] ?? '',
				'button_text' => $found['ctaButton'] ?? '',
				'button_url'  => $found['ctaHref'] ?? '/contact',
			)
		);
	}

	$out .= wla_render_page_nav_strip( $slug );
	return $out;
}

/**
 * Closing call-to-action band. Links out to a real page (default /contact)
 * instead of embedding a duplicate form on every page.
 */
function wla_render_cta_band( $args = array() ) {
	$snap = wla_get_demo_snapshot();
	$d    = $snap['home'] ?? array();
	$a    = wp_parse_args(
		$args,
		array(
			'eyebrow'         => $d['ctaBandEyebrow'] ?? 'NEXT STEP',
			'title'           => $d['ctaBandTitle'] ?? "Have a question we haven't covered?",
			'body'            => $d['ctaBandBody'] ?? '',
			'button_text'     => $d['ctaBandButton'] ?? 'Open the Contact page',
			'button_url'      => '/contact',
			'secondary_text'  => $d['ctaBandSecondary'] ?? 'Read the FAQs',
			'secondary_url'   => '/frequently-asked-questions',
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
					<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>">
						<span><?php echo esc_html( $a['button_text'] ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
					<a class="button button-ghost" href="<?php echo wla_resolve_route_url( $a['secondary_url'] ); ?>">
						<span><?php echo esc_html( $a['secondary_text'] ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * "Continue exploring" grid linking to the other real pages, so each page
 * participates in a genuine multi-page site structure.
 */
function wla_render_page_nav_strip( $current_slug = '' ) {
	$snap  = wla_get_demo_snapshot();
	$d     = $snap['home'] ?? array();
	$spanish = function_exists( 'wla_is_spanish_language' ) && wla_is_spanish_language();
	$label   = $d['explorePagesLabel'] ?? ( $spanish ? 'Continúa explorando otras páginas' : 'Continue exploring other pages' );
	$back    = $spanish ? 'Volver al inicio' : 'Back to homepage';
	$pages   = $d['explorePages'] ?? array();

	if ( empty( $pages ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="page-nav-section">
		<div class="site-container">
			<div class="page-nav-head" data-reveal>
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $label ); ?></span></p>
				<a class="text-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span><?php echo esc_html( $back ); ?></span>
					<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</a>
			</div>
			<div class="page-nav-grid">
				<?php
				$shown = 0;
				foreach ( $pages as $p ) :
					$href  = $p[0] ?? '';
					$slug  = trim( (string) $href, '/' );
					if ( '' === $slug || $slug === $current_slug ) {
						continue;
					}
					if ( $shown >= 8 ) {
						break;
					}
					$shown++;
					?>
					<a class="page-nav-card" href="<?php echo wla_resolve_route_url( $href ); ?>" data-reveal="up" data-tilt style="--reveal-delay:<?php echo esc_attr( number_format( ( $shown - 1 ) * 0.05, 2 ) ); ?>s">
						<span class="page-nav-index" aria-hidden="true">0<?php echo esc_html( $shown ); ?></span>
						<strong><?php echo esc_html( $p[1] ?? '' ); ?></strong>
						<span class="page-nav-blurb"><?php echo esc_html( $p[2] ?? '' ); ?></span>
						<span class="page-nav-go" aria-hidden="true"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
