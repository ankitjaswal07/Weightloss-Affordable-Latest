<?php
/**
 * Archive Template — matches exact Straw luxury design system.
 */
get_header();
?>
<main id="main-content">
	<?php
	echo wla_render_subpage_hero(
		array(
			'eyebrow' => 'EXPLORE RESOURCES',
			'title'   => wp_strip_all_tags( get_the_archive_title() ),
			'lead'    => wp_strip_all_tags( get_the_archive_description() ),
			'badge'   => 'Educational archive',
		)
	);
	?>
	<section class="intro-section section-pad">
		<div class="site-container">
			<?php if ( have_posts() ) : ?>
				<div class="articles-stack">
					<?php while ( have_posts() ) : the_post(); ?>
						<article <?php post_class( 'article-full-card' ); ?> data-reveal="up">
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="article-full-media" href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'wla-card', array( 'loading' => 'lazy' ) ); ?>
								</a>
							<?php endif; ?>
							<div class="article-full-content">
								<span class="card-tag"><?php wla_posted_on(); ?> · <?php wla_reading_time(); ?></span>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( get_the_excerpt() ?: wp_trim_words( wp_strip_all_tags( get_the_content() ), 36 ) ); ?></p>
								<a class="text-link" href="<?php the_permalink(); ?>">
									<span>Read full article</span>
									<span class="link-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span>
								</a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<p>No published entries found yet.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
