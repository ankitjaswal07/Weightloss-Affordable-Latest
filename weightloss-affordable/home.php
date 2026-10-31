<?php
/**
 * Blog & Educational Resources Hub (home.php):
 * Matches the exact Next.js /resources page 100% — Subpage Hero + Interactive Explorer + Full Articles + Contact!
 */
get_header();
?>
<main id="main-content" class="wla-elementor-canvas-reset">
<?php
echo wla_render_subpage_hero(
	array(
		'eyebrow' => 'BLOG & EDUCATIONAL RESOURCES',
		'title'   => 'Evidence-minded wellness & health literacy guides.',
		'lead'    => 'Explore our educational articles below — written with calm, medically responsible language and instant English / Spanish translation.',
		'badge'   => 'Educational guides · Instant EN / ES translation',
	)
);

echo wla_render_resources_explorer_section( array( 'index' => '01' ) );
?>
<section class="intro-section section-pad">
	<div class="site-container">
		<div class="section-header-row" data-reveal>
			<div>
				<p class="eyebrow">
					<span class="section-index">02</span>
					<span class="eyebrow-line"></span>
					<span>FULL EDUCATIONAL ARTICLES</span>
				</p>
				<h2>Read every published resource in full.</h2>
			</div>
		</div>

		<div class="articles-stack">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'article-full-card' ); ?> data-reveal="up">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="article-full-media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'wla-hero', array( 'loading' => 'lazy' ) ); ?>
							</a>
						<?php endif; ?>
						<div class="article-full-content">
							<span class="card-tag"><?php wla_posted_on(); ?> · <?php wla_reading_time(); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( get_the_excerpt() ?: wp_trim_words( wp_strip_all_tags( get_the_content() ), 42 ) ); ?></p>
							<a class="text-link" href="<?php the_permalink(); ?>">
								<span>Read full article</span>
								<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php echo wla_render_cta_band(); ?>
<?php echo wla_render_page_nav_strip( 'resources' ); ?>
</main>
<?php
get_footer();
