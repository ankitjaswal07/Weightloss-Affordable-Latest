<?php
/**
 * Single Page Template: Supports full Elementor visual editing AND seamless fallback
 * to our Straw luxury editorial subpage design!
 */
get_header();
?>
<main id="main-content" class="wla-elementor-canvas-reset">
<?php
while ( have_posts() ) :
	the_post();
	$slug         = get_post_field( 'post_name', get_the_ID() );
	$is_elementor = ( 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) && did_action( 'elementor/loaded' ) );
	if ( $is_elementor ) {
		the_content();
	} else {
		$snapshot_markup = wla_render_snapshot_subpage( $slug );
		if ( $snapshot_markup ) {
			echo $snapshot_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo wla_render_subpage_hero(
				array(
					'eyebrow' => 'WEIGHTLOSSAFFORDABLE',
					'title'   => get_the_title(),
					'lead'    => has_excerpt() ? get_the_excerpt() : '',
					'badge'   => 'Editable WordPress page',
				)
			);
			?>
			<section class="intro-section section-pad">
				<div class="site-container">
					<div class="article-full-card">
						<div class="article-full-content" style="grid-column:1 / -1">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			echo wla_render_cta_band();
			echo wla_render_page_nav_strip( $slug );
		}
	}
endwhile;
?>
</main>
<?php
get_footer();
