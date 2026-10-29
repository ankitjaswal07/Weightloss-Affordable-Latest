<?php
/**
 * Single post template.
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'site-container section-pad article-full' ); ?>>
		<header class="section-heading">
			<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( get_the_date() ); ?></span></p>
			<h1><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="article-full-media">
				<?php the_post_thumbnail( 'wla-hero', array( 'loading' => 'eager' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="article-full-content entry-content">
			<?php the_content(); ?>
		</div>

		<footer class="article-footer">
			<p class="editorial-note">
				<?php esc_html_e( 'General information only. This article is not medical advice and does not replace an assessment by a licensed clinician.', 'weightloss-affordable' ); ?>
			</p>
			<?php the_post_navigation(); ?>
		</footer>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="site-container section-pad">';
		comments_template();
		echo '</div>';
	}
endwhile;

get_footer();
