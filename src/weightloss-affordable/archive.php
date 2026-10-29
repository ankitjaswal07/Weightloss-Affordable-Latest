<?php
/**
 * Archive template.
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
		<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php esc_html_e( 'ARCHIVE', 'weightloss-affordable' ); ?></span></p>
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
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
		<?php
		the_posts_pagination(
			array(
				'prev_text' => esc_html__( 'Previous', 'weightloss-affordable' ),
				'next_text' => esc_html__( 'Next', 'weightloss-affordable' ),
			)
		);
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found here yet.', 'weightloss-affordable' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
