<?php
/**
 * 100% Preview-Identical Site Footer + Guide Reader Modal + Translator Toast.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wla_snap = wla_get_demo_snapshot();
$wla_d    = $wla_snap['home'] ?? array();
?>
	<footer class="site-footer">
		<div class="site-container footer-main">
			<div class="footer-brand">
				<a class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo wla_render_brand_mark(); ?></a>
				<p><?php echo esc_html( $wla_d['footerText'] ?? 'Clear information for a more confident next step.' ); ?></p>
				<div class="footer-language" role="group" aria-label="Translate site">
					<button type="button" class="is-current-lang" data-set-lang="en" aria-pressed="true">EN (English)</button>
					<span>/</span>
					<button type="button" data-set-lang="es" aria-pressed="false">ES (Español)</button>
				</div>
			</div>

			<div class="footer-links">
				<p class="footer-label"><?php echo esc_html( $wla_d['footerExploreHeading'] ?? 'EXPLORE' ); ?></p>
				<?php if ( has_nav_menu( 'footer_explore' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_explore',
							'container'      => false,
							'menu_class'     => 'footer-explore-menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				<?php else : ?>
					<?php foreach ( ( $wla_d['footerExploreLinks'] ?? array() ) as $item ) : ?>
						<a href="<?php echo wla_resolve_route_url( $item[0] ?? '/' ); ?>"><?php echo esc_html( $item[1] ?? '' ); ?></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<div class="footer-links">
				<p class="footer-label"><?php echo esc_html( $wla_d['footerInfoHeading'] ?? 'LEGAL & COMPLIANCE' ); ?></p>
				<?php foreach ( ( $wla_d['footerLegalLinks'] ?? array() ) as $item ) : ?>
					<a href="<?php echo wla_resolve_route_url( $item[0] ?? '/' ); ?>"><?php echo esc_html( $item[1] ?? '' ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="site-container footer-word" aria-hidden="true">weightloss<span>affordable</span></div>

		<div class="site-container footer-disclaimer">
			<!--p><?php echo esc_html( $wla_d['footerDisclaimer'] ?? '' ); ?></p-->
			<span><?php echo esc_html( $wla_d['rights'] ?? '© 2026 WeightLossAffordable.com. All rights reserved.' ); ?></span>
		</div>
	</footer>

	<div class="guide-modal-backdrop" hidden data-wla-guide-modal role="dialog" aria-modal="true" aria-labelledby="wla-modal-title">
		<article class="guide-modal-panel">
			<div class="guide-modal-hero">
				<img src="" alt="" data-modal-img />
				<span class="guide-modal-scrim" aria-hidden="true"></span>
				<button type="button" class="guide-modal-close" aria-label="Close guide" data-close-modal><?php echo wla_svg_icon( 'close' ); ?></button>
				<div class="guide-modal-hero-copy">
					<div class="guide-modal-badges">
						<span data-modal-cat></span>
						<span data-modal-read></span>
					</div>
					<h2 id="wla-modal-title" data-modal-title></h2>
					<p data-modal-prompt></p>
				</div>
			</div>
			<div class="guide-modal-body">
				<div class="guide-modal-article" data-modal-paragraphs></div>
				<aside class="guide-modal-questions">
					<strong>Questions to bring</strong>
					<ul data-modal-questions></ul>
					<p class="guide-modal-disclaimer" data-modal-disclaimer></p>
				</aside>
			</div>
			<div class="guide-modal-footer">
				<a class="text-link" href="<?php echo wla_resolve_route_url( '/resources' ); ?>">
					<span>Open full resource library</span>
					<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</a>
				<button type="button" class="button button-primary" data-modal-ask>
					<span>Ask about this topic</span>
					<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
				</button>
			</div>
		</article>
	</div>

	<div class="translator-toast" hidden data-wla-toast role="status" aria-live="polite">
		<span class="translator-toast-dot" aria-hidden="true"></span>
		<span data-wla-toast-text></span>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
