<?php
/**
 * 404 template.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="subpage-hero">
	<div class="site-container">
		<div class="subpage-hero-copy">
			<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php esc_html_e( 'PAGE NOT FOUND', 'weightloss-affordable' ); ?></span></p>
			<h1><?php esc_html_e( 'We could not find that page.', 'weightloss-affordable' ); ?></h1>
			<p class="hero-description"><?php esc_html_e( 'The page may have moved. Try one of the links below, or search the site.', 'weightloss-affordable' ); ?></p>
			<?php get_search_form(); ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span><?php esc_html_e( 'Back to homepage', 'weightloss-affordable' ); ?></span>
					<span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			</p>
		</div>
	</div>
</section>
<?php
get_footer();
