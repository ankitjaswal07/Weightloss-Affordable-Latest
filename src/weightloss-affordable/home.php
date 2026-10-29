<?php
/**
 * Blog index (the page set as "Posts page").
 *
 * @package WeightLossAffordable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$wla_page_id = (int) get_option( 'page_for_posts' );
?>
<section class="subpage-hero">
	<div class="hero-backdrop" aria-hidden="true">
		<span class="hero-ring hero-ring-one"></span>
		<span class="hero-ring hero-ring-two"></span>
	</div>
	<div class="site-container">
		<div class="subpage-hero-copy">
			<nav class="subpage-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'weightloss-affordable' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'weightloss-affordable' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html( $wla_page_id ? get_the_title( $wla_page_id ) : __( 'Articles', 'weightloss-affordable' ) ); ?></span>
			</nav>
			<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php esc_html_e( 'LEARN', 'weightloss-affordable' ); ?></span></p>
			<h1><?php echo esc_html( $wla_page_id ? get_the_title( $wla_page_id ) : __( 'Articles', 'weightloss-affordable' ) ); ?></h1>
			<p class="hero-description"><?php esc_html_e( 'Plain-language guides about weight-management treatment, eligibility and safety. General information only — not medical advice.', 'weightloss-affordable' ); ?></p>
		</div>
	</div>
</section>

<div class="site-container section-pad">
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
		<p><?php esc_html_e( 'No articles published yet.', 'weightloss-affordable' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
