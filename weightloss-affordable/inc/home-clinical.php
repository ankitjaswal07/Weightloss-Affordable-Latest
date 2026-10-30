<?php
/**
 * Homepage clinical sections: guarantee, GLP-1 signal, treatments,
 * transformations, video stories, and science. Shared by theme + Elementor.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_home_quote_url() {
	return function_exists( 'wla_resolve_route_url' ) ? wla_resolve_route_url( '/consultation-eligibility/' ) : home_url( '/consultation-eligibility/' );
}

function wla_render_guarantee_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow'     => 'YOUR PROTECTION',
			'title'       => 'Start with protection, not pressure.',
			'emphasis'    => 'protection',
			'body'        => 'You are not charged for an enquiry. A licensed clinician reviews your information and decides whether any treatment is appropriate. Individual results vary and are never guaranteed.',
			'stat'        => '0',
			'stat_label'  => 'Charge until a clinician reviews your case',
			'button_text' => 'See if you qualify',
			'button_url'  => '/consultation-eligibility/',
			'points'      => array(
				array( 'No charge for completing the Get Quote quiz' ),
				array( 'A licensed clinician makes the prescribing decision' ),
				array( 'Cancel before a future billing cycle if you enroll' ),
				array( 'Compounded medications are not FDA-approved' ),
			),
			'note'        => 'This is not a promise of weight loss, approval, or a specific outcome. Any refund or billing rule must match the published policy pages.',
		)
	);
	$points = $a['points'];
	ob_start();
	?>
	<section class="wla-clinical-section wla-guarantee" id="guarantee">
		<div class="site-container">
			<div class="wla-guarantee-grid">
				<div class="wla-guarantee-copy">
					<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
					<h2><?php echo wp_kses_post( str_replace( $a['emphasis'], '<em>' . esc_html( $a['emphasis'] ) . '</em>', esc_html( $a['title'] ) ) ); ?></h2>
					<p><?php echo esc_html( $a['body'] ); ?></p>
					<ul class="wla-guarantee-points">
						<?php foreach ( $points as $point ) : ?>
							<li><?php echo wla_svg_icon( 'check' ); ?><span><?php echo esc_html( is_array( $point ) ? ( $point['text'] ?? $point[0] ?? '' ) : $point ); ?></span></li>
						<?php endforeach; ?>
					</ul>
					<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>"><span><?php echo esc_html( $a['button_text'] ); ?></span><span class="button-arrow"><?php echo wla_svg_icon( 'arrow' ); ?></span></a>
					<p class="wla-clinical-note"><?php echo esc_html( $a['note'] ); ?></p>
				</div>
				<aside class="wla-guarantee-card" aria-label="<?php esc_attr_e( 'Program protection', 'weightloss-affordable' ); ?>">
					<span class="wla-guarantee-kicker"><?php esc_html_e( 'CLEAR TERMS', 'weightloss-affordable' ); ?></span>
					<strong><?php echo esc_html( $a['stat'] ); ?></strong>
					<span><?php echo esc_html( $a['stat_label'] ); ?></span>
				</aside>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_signal_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => 'THE MECHANISM',
			'title'   => 'How GLP-1 changes the signal.',
			'body'    => 'GLP-1 (glucagon-like peptide-1) is a hormone your gut releases after eating. It helps the brain register fullness, slows how quickly the stomach empties, and supports glucose-dependent insulin signaling. Prescription GLP-1 medicines amplify that natural pathway. They are not a substitute for nutrition, movement, sleep, or clinical care.',
			'points'  => array(
				array( 'title' => 'Satiety', 'body' => 'Appetite signals can feel quieter, so meals may end with less effort.' ),
				array( 'title' => 'Slower digestion', 'body' => 'Food can stay in the stomach longer, which may help you feel full for more hours.' ),
				array( 'title' => 'Steadier glucose', 'body' => 'Fewer sharp swings can mean fewer urgent cravings for some people.' ),
			),
			'button_text' => 'View treatments',
			'button_url'  => '/weight-loss-services/',
			'image'       => 'nutrition.jpg',
			'image_alt'   => 'A balanced meal prepared at home',
		)
	);
	ob_start();
	?>
	<section class="wla-clinical-section wla-signal" id="ciencia">
		<div class="site-container wla-signal-grid">
			<div>
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h2><?php echo wp_kses_post( preg_replace( '/GLP-1/', '<em>GLP-1</em>', esc_html( $a['title'] ), 1 ) ); ?></h2>
				<p><?php echo esc_html( $a['body'] ); ?></p>
				<ol class="wla-signal-points">
					<?php foreach ( $a['points'] as $i => $point ) : ?>
						<li>
							<span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<div>
								<strong><?php echo esc_html( $point['title'] ?? '' ); ?></strong>
								<p><?php echo esc_html( $point['body'] ?? '' ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
				<a class="button button-quiet" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>"><span><?php echo esc_html( $a['button_text'] ); ?></span></a>
			</div>
			<figure class="wla-signal-media">
				<img src="<?php echo wla_resolve_asset_url( $a['image'] ); ?>" alt="<?php echo esc_attr( $a['image_alt'] ); ?>" loading="lazy" />
			</figure>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_home_treatment_cards() {
	$cards = array();
	if ( function_exists( 'nrx_treatment_data' ) && post_type_exists( 'nrx_treatment' ) ) {
		foreach ( array( 'semaglutida', 'tirzepatida' ) as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'nrx_treatment' );
			if ( ! $post ) {
				continue;
			}
			$data   = nrx_treatment_data( $post );
			$cards[] = array(
				'slug'        => $slug,
				'eyebrow'     => $data['eyebrow'] ?? '',
				'title'       => get_the_title( $post ),
				'body'        => $data['tagline'] ?? '',
				'price'       => ! empty( $data['from_price'] ) ? ( function_exists( 'nrx_treatment_price_label' ) ? nrx_treatment_price_label( $data ) : '' ) : '',
				'url'         => get_permalink( $post ),
				'quote'       => add_query_arg( 'treatment', $slug, wla_home_quote_url() ),
				'points'      => wp_list_pluck( array_slice( (array) ( $data['bullets'] ?? array() ), 0, 4 ), 'label' ),
			);
		}
	}
	if ( $cards ) {
		return $cards;
	}
	return array(
		array(
			'slug'    => 'semaglutida',
			'eyebrow' => 'GLP-1 · Receptor agonist',
			'title'   => 'Semaglutide',
			'body'    => 'A weekly GLP-1 option discussed only when a licensed clinician decides it is appropriate.',
			'price'   => 'from $139',
			'url'     => home_url( '/semaglutida/' ),
			'quote'   => add_query_arg( 'treatment', 'semaglutida', wla_home_quote_url() ),
			'points'  => array( 'GLP-1 receptor agonist class', 'Gradual dose titration under clinical review', 'Not FDA-approved when compounded', 'Individual results vary' ),
		),
		array(
			'slug'    => 'tirzepatida',
			'eyebrow' => 'GIP / GLP-1 · Dual agonist',
			'title'   => 'Tirzepatide',
			'body'    => 'A dual GIP and GLP-1 option. Dual action does not mean it is right for every person.',
			'price'   => 'from $239',
			'url'     => home_url( '/tirzepatida/' ),
			'quote'   => add_query_arg( 'treatment', 'tirzepatida', wla_home_quote_url() ),
			'points'  => array( 'Acts on GIP and GLP-1 receptors', 'Clinician chooses if a dual agonist is reasonable', 'Not bioequivalent to brand products', 'Prescription only if appropriate' ),
		),
	);
}

function wla_render_treatments_duo_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => 'OUR TREATMENTS',
			'title'   => 'Two medications. One careful path.',
			'intro'   => 'A licensed clinician recommends an option based on your history, measurements, and goals. Compounded preparations are not FDA-approved and are not bioequivalent to brand-name medicines.',
		)
	);
	$cards = wla_home_treatment_cards();
	ob_start();
	?>
	<section class="wla-clinical-section wla-treatments-duo" id="treatments">
		<div class="site-container">
			<div class="wla-clinical-heading">
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h2><?php echo wp_kses_post( preg_replace( '/One careful path/', '<em>One careful path</em>', esc_html( $a['title'] ), 1 ) ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
			</div>
			<div class="wla-duo-grid">
				<?php foreach ( $cards as $card ) : ?>
					<article class="wla-duo-card">
						<p class="wla-exp-eyebrow"><?php echo esc_html( $card['eyebrow'] ); ?></p>
						<h3><?php echo esc_html( $card['title'] ); ?></h3>
						<p><?php echo esc_html( $card['body'] ); ?></p>
						<?php if ( ! empty( $card['price'] ) ) : ?>
							<p class="wla-duo-price"><?php echo esc_html( $card['price'] ); ?><small><?php esc_html_e( '/mo from the configured plan · confirm before checkout', 'weightloss-affordable' ); ?></small></p>
						<?php endif; ?>
						<ul>
							<?php foreach ( (array) $card['points'] as $point ) : ?>
								<?php if ( $point ) : ?><li><?php echo esc_html( $point ); ?></li><?php endif; ?>
							<?php endforeach; ?>
						</ul>
						<div class="wla-duo-actions">
							<a class="button button-primary" href="<?php echo esc_url( $card['url'] ); ?>"><span><?php esc_html_e( 'View treatment', 'weightloss-affordable' ); ?></span></a>
							<a class="wla-text-link" href="<?php echo esc_url( $card['quote'] ); ?>"><?php esc_html_e( 'Get a quote', 'weightloss-affordable' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_transformations_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => 'REAL TRANSFORMATIONS',
			'title'   => 'Progress is personal — and never guaranteed.',
			'intro'   => 'Authorized before-and-after stories belong here only after documented consent. These cards are layout slots, not live patient results.',
			'cards'   => array(
				array( 'label' => 'Story slot 01', 'meta' => 'Replace with an authorized account', 'body' => 'No weight-loss number is invented on this page.' ),
				array( 'label' => 'Story slot 02', 'meta' => 'Photo permission required', 'body' => 'Individual experiences vary and are not a prediction.' ),
				array( 'label' => 'Story slot 03', 'meta' => 'Clinical review before publishing', 'body' => 'Do not present stock photography as a patient.' ),
			),
		)
	);
	ob_start();
	?>
	<section class="wla-clinical-section wla-transformations" id="transformations">
		<div class="site-container">
			<div class="wla-clinical-heading">
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h2><?php echo wp_kses_post( preg_replace( '/never guaranteed/', '<em>never guaranteed</em>', esc_html( $a['title'] ), 1 ) ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
			</div>
			<div class="wla-transform-grid">
				<?php foreach ( $a['cards'] as $card ) : ?>
					<article class="wla-transform-card">
						<div class="wla-ba" aria-hidden="true"><span><?php esc_html_e( 'BEFORE', 'weightloss-affordable' ); ?></span><span><?php esc_html_e( 'AFTER', 'weightloss-affordable' ); ?></span></div>
						<div class="wla-transform-copy">
							<p class="wla-exp-demo-label"><?php echo esc_html( $card['label'] ?? '' ); ?></p>
							<p class="wla-transform-meta"><?php echo esc_html( $card['meta'] ?? '' ); ?></p>
							<p><?php echo esc_html( $card['body'] ?? '' ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_video_stories_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => 'VIDEO STORIES',
			'title'   => 'Hear the process in plain language.',
			'intro'   => 'Use only genuine, consented video. Until real files are approved, these panels show the layout only.',
			'button_text' => 'Get a quote',
			'button_url'  => '/consultation-eligibility/',
		)
	);
	$videos = array();
	if ( post_type_exists( 'wla_video_story' ) ) {
		$posts = get_posts( array( 'post_type' => 'wla_video_story', 'post_status' => 'publish', 'posts_per_page' => 4 ) );
		foreach ( $posts as $post ) {
			if ( '1' !== get_post_meta( $post->ID, '_wla_video_approved', true ) ) {
				continue;
			}
			$videos[] = array(
				'title' => get_the_title( $post ),
				'url'   => get_post_meta( $post->ID, '_wla_video_url', true ),
				'copy'  => get_the_excerpt( $post ),
			);
		}
	}
	ob_start();
	?>
	<section class="wla-clinical-section wla-videos" id="video-stories">
		<div class="site-container">
			<div class="wla-clinical-heading">
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h2><?php echo wp_kses_post( preg_replace( '/plain language/', '<em>plain language</em>', esc_html( $a['title'] ), 1 ) ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
			</div>
			<div class="wla-video-grid">
				<?php if ( $videos ) : ?>
					<?php foreach ( $videos as $video ) : ?>
						<article class="wla-video-card">
							<div class="wla-video-frame">
								<?php if ( $video['url'] ) : ?>
									<video controls preload="none" playsinline src="<?php echo esc_url( $video['url'] ); ?>"></video>
								<?php endif; ?>
							</div>
							<h3><?php echo esc_html( $video['title'] ); ?></h3>
							<p><?php echo esc_html( $video['copy'] ); ?></p>
						</article>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ( array( '01', '02', '03' ) as $slot ) : ?>
						<article class="wla-video-card is-placeholder">
							<div class="wla-video-frame"><span><?php echo esc_html( sprintf( __( 'Video slot %s', 'weightloss-affordable' ), $slot ) ); ?></span></div>
							<h3><?php echo esc_html( sprintf( __( 'Approved story %s', 'weightloss-affordable' ), $slot ) ); ?></h3>
							<p><?php esc_html_e( 'Add a consented, reviewed video here. No patient footage is fabricated.', 'weightloss-affordable' ); ?></p>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="wla-clinical-cta">
				<a class="button button-primary" href="<?php echo wla_resolve_route_url( $a['button_url'] ); ?>"><span><?php echo esc_html( $a['button_text'] ); ?></span></a>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function wla_render_science_section( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => 'THE SCIENCE',
			'title'   => 'How GLP-1 agonists work.',
			'intro'   => 'The descriptions below explain drug classes found in peer-reviewed literature. They do not claim that a compounded preparation matches a brand-name trial result.',
			'columns' => array(
				array(
					'eyebrow' => 'Class: GLP-1 receptor agonist',
					'title'   => 'Semaglutide',
					'points'  => array(
						'Acts on GLP-1 receptors involved in appetite and fullness.',
						'Supports glucose-dependent insulin signaling.',
						'Slows gastric emptying so meals may last longer.',
					),
				),
				array(
					'eyebrow' => 'Class: dual GIP / GLP-1 agonist',
					'title'   => 'Tirzepatide',
					'points'  => array(
						'Acts on both GIP and GLP-1 receptors.',
						'The dual pathway is described in published literature for the brand product.',
						'A clinician decides whether that class is appropriate for you.',
					),
				),
			),
			'note' => 'Compounded semaglutide and tirzepatide are not FDA-approved and are not bioequivalent to brand-name products. Individual results vary. This is educational information, not personalized medical advice.',
		)
	);
	ob_start();
	?>
	<section class="wla-clinical-section wla-science" id="science">
		<div class="site-container">
			<div class="wla-clinical-heading">
				<p class="eyebrow"><span class="eyebrow-dot"></span><span><?php echo esc_html( $a['eyebrow'] ); ?></span></p>
				<h2><?php echo wp_kses_post( preg_replace( '/GLP-1 agonists/', '<em>GLP-1 agonists</em>', esc_html( $a['title'] ), 1 ) ); ?></h2>
				<p><?php echo esc_html( $a['intro'] ); ?></p>
			</div>
			<div class="wla-science-grid">
				<?php foreach ( $a['columns'] as $col ) : ?>
					<article>
						<p class="wla-exp-eyebrow"><?php echo esc_html( $col['eyebrow'] ?? '' ); ?></p>
						<h3><?php echo esc_html( $col['title'] ?? '' ); ?></h3>
						<ul>
							<?php foreach ( (array) ( $col['points'] ?? array() ) as $point ) : ?>
								<li><?php echo esc_html( $point ); ?></li>
							<?php endforeach; ?>
						</ul>
					</article>
				<?php endforeach; ?>
			</div>
			<p class="wla-clinical-note"><?php echo esc_html( $a['note'] ); ?></p>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
