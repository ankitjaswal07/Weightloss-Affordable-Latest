<?php
/**
 * Single Post / CPT Template — matches exact Straw editorial layout.
 */
get_header();
?>
<main id="main-content">
<?php while ( have_posts() ) : the_post(); ?>
	<?php
	echo wla_render_subpage_hero(
		array(
			'eyebrow'   => strtoupper( get_post_type_object( get_post_type() )->labels->singular_name ?? 'ARTICLE' ),
			'title'     => get_the_title(),
			'lead'      => has_excerpt() ? get_the_excerpt() : '',
			'badge'     => 'Educational draft — not individualized medical advice',
			'image'     => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '',
			'image_alt' => get_the_title(),
		)
	);
	?>
	<section class="intro-section section-pad">
		<div class="site-container">
			<article <?php post_class( 'article-full-card' ); ?> data-reveal="up">
				<div class="article-full-content" style="grid-column:1 / -1">
					<?php the_content(); ?>
					<div class="editorial-note subpage-notice">
						<span><?php echo wla_svg_icon( 'shield' ); ?></span>
						<p>Educational information only — never a substitute for advice from an appropriately licensed healthcare professional.</p>
					</div>
				</div>
			</article>
		</div>
	</section>
	<?php echo wla_render_cta_band(); ?>
	<?php echo wla_render_page_nav_strip( '' ); ?>
<?php endwhile; ?>
</main>
<?php
get_footer();
