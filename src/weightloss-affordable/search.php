<?php
/**
 * Search results.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="site-container section-pad">
	<header class="section-heading">
		<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php esc_html_e( 'SEARCH', 'weightloss-affordable' ); ?></span></p>
		<h1>
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Results for “%s”', 'weightloss-affordable' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="articles-stack">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'post' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No results found. Try a different term.', 'weightloss-affordable' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
