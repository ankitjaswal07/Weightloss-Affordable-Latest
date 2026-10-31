<?php
/**
 * 100% Preview-Identical Site Header + Live Single-Page EN/ES Translator Switcher.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wla_snap    = wla_get_demo_snapshot();
$wla_d       = $wla_snap['home'] ?? array();
$wla_is_home = is_front_page();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wla_output_seo_meta(); ?>
	<?php wla_output_structured_data(); ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="wla-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'weightloss-affordable' ); ?></a>

<div class="site-frame">
	<div class="topline">
		<div class="site-container topline-inner">
			<span><?php echo wla_svg_icon( 'spark' ); ?><span><?php echo esc_html( $wla_d['top'] ?? 'Clear information. A more confident next step.' ); ?></span></span>
			<span class="topline-language"><?php echo esc_html( $wla_d['topLang'] ?? 'LIVE TRANSLATOR · EN / ES' ); ?></span>
		</div>
	</div>

	<header class="site-header" data-wla-header>
		<div class="scroll-progress" aria-hidden="true"><span></span></div>
		<div class="site-container header-inner">
			<a class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php
				$wla_logo_id = get_theme_mod( 'custom_logo' );
				if ( $wla_logo_id ) {
					echo wp_get_attachment_image( $wla_logo_id, 'full', false, array( 'class' => 'custom-logo', 'alt' => get_bloginfo( 'name' ) ) );
				} else {
					echo wla_render_brand_mark();
				}
				?>
			</a>

			<button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" data-wla-menu-toggle>
				<span></span><span></span><span></span>
			</button>

			<nav class="main-nav" aria-label="Main navigation" data-wla-nav>
				<?php
				// True multi-page navigation: every entry is a real page route on
				// every page (no on-page anchors), so the site behaves as a set of
				// distinct pages rather than one long scrolling document.
				$wla_current_slug = $wla_is_home ? 'home' : (string) get_post_field( 'post_name', get_queried_object_id() );
				$wla_nav_items    = wla_nav_items();
				foreach ( $wla_nav_items as $wla_nav ) :
					$wla_is_current = ( $wla_nav['slug'] === $wla_current_slug );
					?>
					<a href="<?php echo wla_resolve_route_url( $wla_nav['path'] ); ?>"<?php echo $wla_is_current ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo esc_html( $wla_nav['label'] ); ?></a>
				<?php endforeach; ?>

				<div class="pages-menu-wrap" data-wla-dropdown>
					<button type="button" class="pages-menu-trigger" aria-expanded="false" data-wla-dropdown-btn>
						<?php echo wla_svg_icon( 'layers' ); ?>
						<span>Pages</span>
						<i aria-hidden="true">▾</i>
					</button>
					<div class="pages-dropdown" hidden data-wla-dropdown-panel>
						<?php if ( has_nav_menu( 'pages_dropdown' ) ) : ?>
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'pages_dropdown',
									'container'      => false,
									'menu_class'     => 'pages-dropdown-menu',
									'depth'          => 2,
									'fallback_cb'    => false,
								)
							);
							?>
						<?php else : ?>
							<?php foreach ( ( $wla_d['pagesMenuGroups'] ?? array() ) as $group ) : ?>
								<div class="pages-dropdown-col">
									<span class="pages-dropdown-title"><?php echo esc_html( $group['heading'] ?? '' ); ?></span>
									<?php foreach ( ( $group['links'] ?? array() ) as $lnk ) : ?>
										<a href="<?php echo wla_resolve_route_url( $lnk[0] ?? '/' ); ?>">
											<span><?php echo esc_html( $lnk[1] ?? '' ); ?></span>
											<span aria-hidden="true">→</span>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				<div class="nav-end">
					<div class="language-switcher" role="group" aria-label="Translate site" data-wla-lang-switcher>
						<span class="language-globe" aria-hidden="true"><?php echo wla_svg_icon( 'globe' ); ?></span>
						<button type="button" class="is-active" data-set-lang="en" aria-pressed="true">EN</button>
						<button type="button" data-set-lang="es" aria-pressed="false">ES</button>
					</div>

					<a class="button button-small button-dark" href="<?php echo esc_url( wla_resolve_route_url( '/consultation-eligibility' ) ); ?>">
						<span><?php echo esc_html( $wla_d['cta'] ?? 'Get a quote' ); ?></span>
						<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
					</a>
				</div>
			</nav>
		</div>
	</header>
