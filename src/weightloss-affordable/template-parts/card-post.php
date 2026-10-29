<?php
/**
 * Post card.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'article-full-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="article-full-media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'wla-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="article-full-content">
		<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( get_the_date() ); ?></span></p>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
		<a class="text-link" href="<?php the_permalink(); ?>">
			<span><?php esc_html_e( 'Read the article', 'weightloss-affordable' ); ?></span>
			<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	</div>
</article>
