<?php
/**
 * Site header.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="wla-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'weightloss-affordable' ); ?></a>

<div class="site-frame">
	<?php if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) : ?>
		<?php // Elementor Pro header template rendered. ?>
	<?php else : ?>
		<div class="topline">
			<div class="site-container topline-inner">
				<span>
					<?php echo wla_svg_icon( 'spark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( get_theme_mod( 'wla_topline', __( 'Clinician-led weight care · English-language support', 'weightloss-affordable' ) ) ); ?></span>
				</span>
				<span class="topline-language">
					<?php
					$phone = get_theme_mod( 'wla_phone', '' );
					if ( $phone ) {
						printf( '<a href="tel:%s">%s</a>', esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ), esc_html( $phone ) );
					} else {
						esc_html_e( 'Questions welcome — no obligation', 'weightloss-affordable' );
					}
					?>
				</span>
			</div>
		</div>

		<header class="site-header" data-wla-header>
			<div class="scroll-progress" aria-hidden="true"><span></span></div>
			<div class="site-container header-inner">
				<a class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php wla_site_brand(); ?>
				</a>

				<button class="menu-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle navigation', 'weightloss-affordable' ); ?>" aria-expanded="false" aria-controls="primary-navigation" data-wla-menu-toggle>
					<span></span><span></span><span></span>
				</button>

				<nav class="main-nav" id="primary-navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'weightloss-affordable' ); ?>" data-wla-nav>
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'menu_class'     => 'primary-menu',
								'depth'          => 2,
								'fallback_cb'    => 'wla_nav_fallback',
							)
						);
					} else {
						wla_nav_fallback();
					}
					?>

					<div class="nav-end">
						<a class="button button-small button-dark" href="<?php echo esc_url( wla_resolve_route_url( '/contact' ) ); ?>">
							<span><?php esc_html_e( 'Ask a question', 'weightloss-affordable' ); ?></span>
							<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</a>
					</div>
				</nav>
			</div>
		</header>
	<?php endif; ?>

	<main id="main-content" class="site-main">
