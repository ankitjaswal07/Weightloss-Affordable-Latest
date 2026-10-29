<?php
/**
 * Site footer.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</main>

	<?php if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) : ?>
		<?php // Elementor Pro footer template rendered. ?>
	<?php else : ?>
		<footer class="site-footer">
			<div class="site-container footer-main">
				<div class="footer-brand">
					<a class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo wla_render_brand_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<p><?php echo esc_html( get_theme_mod( 'wla_footer_text', __( 'Clear information about clinician-led weight care, so you can decide your next step with confidence.', 'weightloss-affordable' ) ) ); ?></p>
				</div>

				<div class="footer-links">
					<p class="footer-label"><?php esc_html_e( 'EXPLORE', 'weightloss-affordable' ); ?></p>
					<?php
					if ( has_nav_menu( 'footer_explore' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'footer_explore',
								'container'      => false,
								'menu_class'     => 'footer-explore-menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
					} else {
						wla_footer_links_fallback( array_keys( wla_primary_routes() ) );
					}
					?>
				</div>

				<div class="footer-links">
					<p class="footer-label"><?php esc_html_e( 'LEGAL & SAFETY', 'weightloss-affordable' ); ?></p>
					<?php
					if ( has_nav_menu( 'footer_legal' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'footer_legal',
								'container'      => false,
								'menu_class'     => 'footer-explore-menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
					} else {
						wla_footer_links_fallback(
							array(
								'privacy-policy',
								'terms-and-conditions',
								'medical-disclaimer',
								'treatment-safety-information',
								'refund-cancellation-policy',
								'accessibility-statement',
							)
						);
					}
					?>
				</div>

				<?php if ( is_active_sidebar( 'footer-extra' ) ) : ?>
					<div class="footer-links footer-extra"><?php dynamic_sidebar( 'footer-extra' ); ?></div>
				<?php endif; ?>
			</div>

			<div class="site-container footer-word" aria-hidden="true">weightloss<span>affordable</span></div>

			<div class="site-container footer-disclaimer">
				<p class="footer-medical-note">
					<?php echo esc_html( get_theme_mod( 'wla_footer_disclaimer', __( 'This website provides general information about weight-management services. It is not medical advice and does not create a clinician–patient relationship. Prescription treatment is available only after an assessment by a licensed clinician, and not everyone is eligible. Individual results vary and are never guaranteed.', 'weightloss-affordable' ) ) ); ?>
				</p>
				<span>
					<?php
					printf(
						/* translators: %1$s: year, %2$s: site name */
						esc_html__( '© %1$s %2$s. All rights reserved.', 'weightloss-affordable' ),
						esc_html( gmdate( 'Y' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
					?>
				</span>
			</div>
		</footer>
	<?php endif; ?>

	<div class="guide-modal-backdrop" hidden data-wla-guide-modal role="dialog" aria-modal="true" aria-labelledby="wla-modal-title">
		<article class="guide-modal-panel">
			<div class="guide-modal-hero">
				<img src="" alt="" data-modal-img />
				<span class="guide-modal-scrim" aria-hidden="true"></span>
				<button type="button" class="guide-modal-close" aria-label="<?php esc_attr_e( 'Close guide', 'weightloss-affordable' ); ?>" data-close-modal><?php echo wla_svg_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
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
					<strong><?php esc_html_e( 'Questions to bring', 'weightloss-affordable' ); ?></strong>
					<ul data-modal-questions></ul>
					<p class="guide-modal-disclaimer" data-modal-disclaimer></p>
				</aside>
			</div>
			<div class="guide-modal-footer">
				<a class="text-link" href="<?php echo esc_url( wla_resolve_route_url( '/resources' ) ); ?>">
					<span><?php esc_html_e( 'Open the resource library', 'weightloss-affordable' ); ?></span>
					<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
				<button type="button" class="button button-primary" data-modal-ask>
					<span><?php esc_html_e( 'Ask about this topic', 'weightloss-affordable' ); ?></span>
					<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</button>
			</div>
		</article>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
