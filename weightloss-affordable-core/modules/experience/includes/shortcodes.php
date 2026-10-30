<?php
/** Front-end components; all editorial records remain admin-managed. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_experience_section_start( $module, $title, $intro ) {
	ob_start(); ?>
	<section id="<?php echo esc_attr( 'treatment' === $module ? 'treatments' : 'wla-exp-' . sanitize_html_class( $module ) ); ?>" class="wla-exp-section wla-exp-<?php echo esc_attr( $module ); ?>" aria-labelledby="wla-exp-<?php echo esc_attr( $module ); ?>-title">
		<div class="wla-exp-heading">
			<p class="wla-exp-eyebrow"><?php echo esc_html( 'quote' === $module ? __( 'A simple starting point', 'wla-experience' ) : __( 'WeightLossAffordable', 'wla-experience' ) ); ?></p>
			<h2 id="wla-exp-<?php echo esc_attr( $module ); ?>-title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $intro ) : ?><p class="wla-exp-intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		</div>
	<?php return ob_get_clean();
}
function wla_experience_section_end() { return '</section>'; }
function wla_experience_header_atts( $atts, $module, $title, $intro ) {
	$atts = shortcode_atts( array( 'title' => $title, 'intro' => $intro ), $atts, 'wla_' . $module );
	return array( sanitize_text_field( $atts['title'] ), sanitize_text_field( $atts['intro'] ) );
}

function wla_experience_treatments_shortcode( $atts ) {
	list( $title, $intro ) = wla_experience_header_atts( $atts, 'treatment_plans', __( 'Treatment plans', 'wla-experience' ), __( 'Explore the treatment options currently published in the site’s product system.', 'wla-experience' ) );
	$source_posts = wla_experience_treatment_posts( $atts['limit'] ?? 8 );
	$products = array();
	foreach ( $source_posts as $source_post ) {
		$data = function_exists( 'nrx_treatment_data' ) && 'nrx_treatment' === $source_post->post_type ? nrx_treatment_data( $source_post ) : array();
		if ( ! empty( $data['require_approval'] ) ) { continue; }
		$products[] = array( 'post' => $source_post, 'data' => $data );
	}
	$show_demo = 'yes' === sanitize_key( $atts['demo'] ?? 'no' );
	if ( ! $products && ! $show_demo && ! current_user_can( 'edit_posts' ) ) { return ''; }
	$out = wla_experience_section_start( 'treatment', $title, $intro ) . '<div class="wla-exp-grid">';
	if ( ! $products && $show_demo ) {
		foreach ( wla_experience_demo_treatments() as $demo ) {
			$out .= '<article id="treatment-' . esc_attr( $demo['slug'] ) . '" class="wla-exp-card wla-exp-treatment-card wla-exp-demo-card"><div class="wla-exp-card-body"><p class="wla-exp-demo-label">' . esc_html__( 'DEMO CONTENT · verify before publication', 'wla-experience' ) . '</p><p class="wla-exp-eyebrow">' . esc_html( $demo['class'] ) . '</p><h3>' . esc_html( $demo['title'] ) . '</h3><p class="wla-exp-treatment-type">' . esc_html( $demo['type'] ) . '</p><p>' . esc_html( $demo['description'] ) . '</p><p class="wla-exp-demo-price"><span>' . esc_html__( 'from', 'wla-experience' ) . '</span> <s>$' . esc_html( number_format_i18n( $demo['compare'], 0 ) ) . '</s> <strong>$' . esc_html( number_format_i18n( $demo['price'], 0 ) ) . '</strong><small>/' . esc_html__( 'mo', 'wla-experience' ) . '</small></p><ul class="wla-exp-product-points">';
			foreach ( $demo['bullets'] as $bullet ) { $out .= '<li>' . esc_html( $bullet ) . '</li>'; }
			$out .= '</ul><div class="wla-exp-product-actions"><a class="wla-exp-button" href="' . esc_url( home_url( '/' . $demo['slug'] . '/' ) ) . '">' . esc_html__( 'View details', 'wla-experience' ) . '<span aria-hidden="true"> →</span></a><a class="wla-exp-text-link" href="' . esc_url( home_url( '/consultation-eligibility/' ) ) . '">' . esc_html__( 'Check if you qualify', 'wla-experience' ) . '</a></div></div></article>';
		}
	}
	foreach ( $products as $product ) {
		$post = $product['post'];
		$data = $product['data'];
		$image_url = '';
		$image_alt = get_the_title( $post );
		if ( ! empty( $data['image'] ) && is_string( $data['image'] ) ) { $image_url = $data['image']; }
		if ( ! $image_url && has_post_thumbnail( $post ) ) { $image_url = get_the_post_thumbnail_url( $post, 'large' ); }
		$description = get_the_excerpt( $post );
		if ( ! $description && ! empty( $data['tagline'] ) ) { $description = $data['tagline']; }
		if ( ! $description ) { $description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 26 ); }
		$url = ! empty( $data['url'] ) ? $data['url'] : get_permalink( $post );
		$price = ! empty( $data['from_price'] ) && function_exists( 'nrx_treatment_price_label' ) ? nrx_treatment_price_label( $data ) : '';
		$out .= '<article class="wla-exp-card wla-exp-treatment-card">';
		if ( $image_url ) { $out .= '<img class="wla-exp-card-image" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( ! empty( $data['image_alt'] ) ? $data['image_alt'] : $image_alt ) . '" loading="lazy" decoding="async">'; }
				$out .= '<div class="wla-exp-card-body"><h3>' . esc_html( get_the_title( $post ) ) . '</h3>';
		if ( $price ) {
			$compare = (float) ( $data['compare_price'] ?? 0 );
			$out .= '<p class="wla-exp-demo-price"><span>' . esc_html__( 'from', 'wla-experience' ) . '</span> ' . ( $compare > (float) ( $data['from_price'] ?? 0 ) ? '<s>$' . esc_html( number_format_i18n( $compare, 0 ) ) . '</s> ' : '' ) . '<strong>' . esc_html( $price ) . '</strong><small>/' . esc_html__( 'mo', 'wla-experience' ) . '</small></p>';
		}
		if ( $description ) { $out .= '<p>' . esc_html( $description ) . '</p>'; }
		if ( ! empty( $data['bullets'] ) && is_array( $data['bullets'] ) ) { $out .= '<ul class="wla-exp-product-points">'; foreach ( $data['bullets'] as $bullet ) { if ( ! empty( $bullet['label'] ) ) { $out .= '<li>' . esc_html( $bullet['label'] ) . '</li>'; } } $out .= '</ul>'; }
		$out .= '<div class="wla-exp-product-actions"><a class="wla-exp-button" href="' . esc_url( $url ) . '">' . esc_html__( 'View treatment details', 'wla-experience' ) . '<span aria-hidden="true"> →</span></a><a class="wla-exp-text-link" href="' . esc_url( home_url( '/consultation-eligibility/' ) ) . '">' . esc_html__( 'Check if you qualify', 'wla-experience' ) . '</a></div></div></article>';
	}
	if ( ! $products && ! $show_demo && current_user_can( 'edit_posts' ) ) { $out .= '<p class="wla-exp-empty">' . esc_html__( 'No publishable treatment records are available to display. Check the existing product source and any pending approval settings.', 'wla-experience' ) . '</p>'; }
	$out .= '</div>' . wla_experience_section_end();
	return $out;
}
add_shortcode( 'wla_treatment_plans', 'wla_experience_treatments_shortcode' );

function wla_experience_quote_url( $url = '' ) {
	if ( $url ) { return esc_url( $url ); }
	if ( ! post_type_exists( 'page' ) ) { return ''; }
	$pages = get_pages( array( 'post_status' => 'publish', 'number' => 100, 'sort_column' => 'menu_order,post_title' ) );
	foreach ( $pages as $page ) {
		$template = get_page_template_slug( $page->ID );
		$data = get_post_meta( $page->ID, '_elementor_data', true );
		if ( has_shortcode( $page->post_content, 'nrx_quiz' ) || 'page-templates/intake.php' === $template || ( is_string( $data ) && false !== strpos( $data, 'nrx_quiz' ) ) ) { return get_permalink( $page ); }
	}
	return '';
}
function wla_experience_quote_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'title' => __( 'A clearer path, one step at a time.', 'wla-experience' ), 'intro' => __( 'Continue to the approved secure intake to learn what information is needed and what to expect next.', 'wla-experience' ), 'button' => __( 'Get a quote', 'wla-experience' ), 'url' => '', 'mode' => 'cta' ), $atts, 'wla_get_quote' );
	$title = sanitize_text_field( $atts['title'] ); $intro = sanitize_text_field( $atts['intro'] );
	if ( 'quiz' === sanitize_key( $atts['mode'] ) ) {
		if ( shortcode_exists( 'nrx_quiz' ) && function_exists( 'wla_experience_nrx_quiz_is_configured' ) && wla_experience_nrx_quiz_is_configured() ) {
			$out = wla_experience_section_start( 'quote', $title, $intro );
			$out .= '<div class="wla-exp-nrx-quiz">' . do_shortcode( '[nrx_quiz intro="0"]' ) . '</div>';
			$out .= '<p class="wla-exp-disclaimer">' . esc_html__( 'Completing the intake is not a diagnosis, a guaranteed eligibility decision, or prescription approval.', 'wla-experience' ) . '</p>';
			return $out . wla_experience_section_end();
		}
		if ( current_user_can( 'edit_pages' ) ) {
			return wla_experience_section_start( 'quote', $title, $intro ) . '<p class="wla-exp-empty">' . esc_html__( 'The embedded assessment requires NuestraRx Core and a reviewed, published quiz configuration. No demo quiz is substituted.', 'wla-experience' ) . '</p>' . wla_experience_section_end();
		}
		return '';
	}
	$url = wla_experience_quote_url( $atts['url'] );
	$out = wla_experience_section_start( 'quote', $title, $intro ) . '<div class="wla-exp-quote-box">';
	if ( $url ) { $out .= '<a class="wla-exp-button" href="' . esc_url( $url ) . '">' . esc_html( $atts['button'] ) . '<span aria-hidden="true"> →</span></a>'; }
	elseif ( current_user_can( 'edit_pages' ) ) { $out .= '<p class="wla-exp-empty">' . esc_html__( 'Configure the verified intake URL in this Elementor widget before publication.', 'wla-experience' ) . '</p>'; }
	$out .= '<p class="wla-exp-disclaimer">' . esc_html__( 'An intake is not a diagnosis, a guarantee of eligibility, or prescription approval. Clinical decisions are made by an authorized clinician.', 'wla-experience' ) . '</p></div>' . wla_experience_section_end();
	return $out;
}
add_shortcode( 'wla_get_quote', 'wla_experience_quote_shortcode' );

function wla_experience_results_shortcode( $atts ) {
	list( $title, $intro ) = wla_experience_header_atts( $atts, 'results', __( 'Patient experiences', 'wla-experience' ), __( 'Publish only verified stories with documented authorization. Individual experiences vary.', 'wla-experience' ) );
	$type = post_type_exists( 'wla_review' ) ? 'wla_review' : '';
	$posts = $type ? get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => min( 12, max( 1, absint( $atts['limit'] ?? 6 ) ) ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'no_found_rows' => true ) ) : array();
	$posts = array_values( array_filter( $posts, function( $post ) { return '1' === get_post_meta( $post->ID, '_wla_review_approved', true ); } ) );
	$show_demo = 'yes' === sanitize_key( $atts['demo'] ?? 'no' );
	if ( ! $posts && ! $show_demo && ! current_user_can( 'edit_posts' ) ) { return ''; }
	$out = wla_experience_section_start( 'results', $title, $intro ) . '<div class="wla-exp-grid">';
	if ( ! $posts && $show_demo ) {
		foreach ( array( '01', '02', '03' ) as $slot ) {
			$out .= '<article class="wla-exp-card wla-exp-story-card wla-exp-demo-card"><div class="wla-exp-before-after" aria-label="' . esc_attr__( 'Before and after media slot; no patient images included', 'wla-experience' ) . '"><span>' . esc_html__( 'BEFORE', 'wla-experience' ) . '</span><i aria-hidden="true"></i><span>' . esc_html__( 'AFTER', 'wla-experience' ) . '</span></div><div class="wla-exp-card-body"><p class="wla-exp-demo-label">' . esc_html__( 'DEMO SLOT · add consented, approved story', 'wla-experience' ) . '</p><h3>' . esc_html( sprintf( __( 'Transformation story %s', 'wla-experience' ), $slot ) ) . '</h3><p>' . esc_html__( 'This space is reserved for a real person’s story, with documented permission and verified context. No outcome or image is fabricated in this demo.', 'wla-experience' ) . '</p></div></article>';
		}
	}
	foreach ( $posts as $post ) {
		$copy = get_the_excerpt( $post );
		if ( ! $copy ) { $copy = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ); }
		$out .= '<article class="wla-exp-card wla-exp-story-card">';
		if ( has_post_thumbnail( $post ) ) { $out .= get_the_post_thumbnail( $post, 'medium_large', array( 'class' => 'wla-exp-card-image', 'loading' => 'lazy', 'decoding' => 'async' ) ); }
		$out .= '<div class="wla-exp-card-body"><h3>' . esc_html( get_the_title( $post ) ) . '</h3>';
		if ( $copy ) { $out .= '<p>' . esc_html( $copy ) . '</p>'; }
		$out .= '<p class="wla-exp-disclaimer">' . esc_html__( 'Individual experiences are not a prediction or guarantee of another person’s outcome.', 'wla-experience' ) . '</p></div></article>';
	}
	if ( ! $posts && ! $show_demo && current_user_can( 'edit_posts' ) ) { $out .= '<p class="wla-exp-empty">' . esc_html__( 'No result stories are published. Add only verified, authorized content; otherwise this section stays hidden from visitors.', 'wla-experience' ) . '</p>'; }
	$out .= '</div>' . wla_experience_section_end();
	return $out;
}
add_shortcode( 'wla_results', 'wla_experience_results_shortcode' );

function wla_experience_videos_shortcode( $atts ) {
	list( $title, $intro ) = wla_experience_header_atts( $atts, 'videos', __( 'Video testimonials', 'wla-experience' ), __( 'Use genuine video stories only after documented authorization and required review.', 'wla-experience' ) );
	$show_demo = 'yes' === sanitize_key( $atts['demo'] ?? 'no' );
	$posts = get_posts( array( 'post_type' => 'wla_video_story', 'post_status' => 'publish', 'posts_per_page' => min( 12, max( 1, absint( $atts['limit'] ?? 6 ) ) ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'no_found_rows' => true ) );
	$posts = array_values( array_filter( $posts, function( $post ) use ( $show_demo ) {
		$url = (string) get_post_meta( $post->ID, '_wla_video_url', true );
		$is_demo = '1' === get_post_meta( $post->ID, '_wla_video_demo', true );
		$allowed = $is_demo ? $show_demo : ( '1' === get_post_meta( $post->ID, '_wla_video_approved', true ) );
		return $allowed && (bool) ( preg_match( '/\\.(mp4|webm)(?:[?#].*)?$/i', $url ) || wla_experience_video_embed( $url, get_the_title( $post ) ) );
	} ) );
	if ( ! $posts && ! $show_demo && ! current_user_can( 'edit_posts' ) ) { return ''; }
	$out = wla_experience_section_start( 'videos', $title, $intro ) . '<div class="wla-exp-grid">';
	if ( ! $posts && $show_demo ) {
		$out .= '<article class="wla-exp-card wla-exp-video-card wla-exp-demo-card"><div class="wla-exp-media"><video controls preload="none" playsinline aria-label="' . esc_attr__( 'Temporary sample video, not a patient testimonial', 'wla-experience' ) . '"><source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4">' . esc_html__( 'Your browser does not support this demo video.', 'wla-experience' ) . '</video></div><div class="wla-exp-card-body"><p class="wla-exp-demo-label">' . esc_html__( 'DEMO VIDEO · stock footage, not a patient story', 'wla-experience' ) . '</p><h3>' . esc_html__( 'A video-story layout preview', 'wla-experience' ) . '</h3><p>' . esc_html__( 'This temporary sample demonstrates the player only. Replace it with a genuine, consented and reviewed video before publication.', 'wla-experience' ) . '</p></div></article>';
	}
	foreach ( $posts as $post ) {
		$url = get_post_meta( $post->ID, '_wla_video_url', true ); $track = get_post_meta( $post->ID, '_wla_video_track', true ); $track_lang = get_post_meta( $post->ID, '_wla_video_track_lang', true ) ?: 'es';
		$embed = wla_experience_video_embed( $url, get_the_title( $post ) );
		$is_direct = preg_match( '/\.(mp4|webm)(?:[?#].*)?$/i', $url );
		$poster = get_the_post_thumbnail_url( $post, 'large' );
		$copy = get_the_excerpt( $post );
		$out .= '<article class="wla-exp-card wla-exp-video-card"><div class="wla-exp-media">';
		if ( $is_direct ) {
			$out .= '<video controls preload="none" playsinline' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . ' aria-label="' . esc_attr( get_the_title( $post ) ) . '"><source src="' . esc_url( $url ) . '" type="' . ( preg_match( '/\.webm(?:[?#].*)?$/i', $url ) ? 'video/webm' : 'video/mp4' ) . '">';
			if ( $track ) { $out .= '<track kind="captions" src="' . esc_url( $track ) . '" srclang="' . esc_attr( $track_lang ) . '" label="' . esc_attr( strtoupper( $track_lang ) ) . '" default>'; }
			$out .= '</video>';
		} elseif ( $embed ) {
			$out .= '<button type="button" class="wla-exp-play" data-embed="' . esc_attr( $embed ) . '" aria-label="' . esc_attr( sprintf( __( 'Play video: %s', 'wla-experience' ), get_the_title( $post ) ) ) . '">';
			if ( $poster ) { $out .= '<img src="' . esc_url( $poster ) . '" alt="" loading="lazy" decoding="async">'; }
			$out .= '<span aria-hidden="true">▶</span></button>';
		}
		$out .= '</div><div class="wla-exp-card-body"><h3>' . esc_html( get_the_title( $post ) ) . '</h3>';
		if ( $copy ) { $out .= '<p>' . esc_html( $copy ) . '</p>'; }
		$out .= '<p class="wla-exp-disclaimer">' . esc_html__( 'Personal experiences are individual and not a guarantee of results.', 'wla-experience' ) . '</p></div></article>';
	}
	if ( ! $posts && ! $show_demo && current_user_can( 'edit_posts' ) ) { $out .= '<p class="wla-exp-empty">' . esc_html__( 'No approved videos are published. Add only authorized video records in the Video Testimonials menu.', 'wla-experience' ) . '</p>'; }
	$out .= '</div>' . wla_experience_section_end();
	return $out;
}
add_shortcode( 'wla_video_testimonials', 'wla_experience_videos_shortcode' );

function wla_experience_calculators_shortcode( $atts ) {
	list( $title, $intro ) = wla_experience_header_atts( $atts, 'calculators', __( 'A few useful numbers', 'wla-experience' ), __( 'Simple math to help you understand measurements. These tools do not predict treatment outcomes or replace clinical advice.', 'wla-experience' ) );
	$bmi_title = sanitize_text_field( $atts['bmi_title'] ?? __( 'BMI calculator', 'wla-experience' ) );
	$bmi_intro = sanitize_text_field( $atts['bmi_intro'] ?? '' );
	$change_title = sanitize_text_field( $atts['change_title'] ?? __( 'Weight-change calculator', 'wla-experience' ) );
	$change_intro = sanitize_text_field( $atts['change_intro'] ?? '' );
	$out = wla_experience_section_start( 'calculators', $title, $intro ) . '<div class="wla-exp-calculator-grid">';
	$out .= '<form class="wla-exp-calculator" data-wla-calculator="bmi" novalidate><h3>' . esc_html( $bmi_title ) . '</h3>' . ( $bmi_intro ? '<p class="wla-exp-calculator-intro">' . esc_html( $bmi_intro ) . '</p>' : '' ) . '<label>' . esc_html__( 'Units', 'wla-experience' ) . '<select name="unit"><option value="us">' . esc_html__( 'US customary', 'wla-experience' ) . '</option><option value="metric">' . esc_html__( 'Metric', 'wla-experience' ) . '</option></select></label><div class="wla-exp-us-fields"><label>' . esc_html__( 'Height (feet)', 'wla-experience' ) . '<input name="feet" type="number" min="0" step="1" inputmode="numeric" required></label><label>' . esc_html__( 'Height (inches)', 'wla-experience' ) . '<input name="inches" type="number" min="0" max="11.9" step="0.1" inputmode="decimal" required></label><label>' . esc_html__( 'Weight (lb)', 'wla-experience' ) . '<input name="weight_lb" type="number" min="0.1" step="0.1" inputmode="decimal" required></label></div><div class="wla-exp-metric-fields" hidden><label>' . esc_html__( 'Height (cm)', 'wla-experience' ) . '<input name="height_cm" type="number" min="1" step="0.1" inputmode="decimal"></label><label>' . esc_html__( 'Weight (kg)', 'wla-experience' ) . '<input name="weight_kg" type="number" min="0.1" step="0.1" inputmode="decimal"></label></div><button class="wla-exp-button" type="submit">' . esc_html__( 'Calculate BMI', 'wla-experience' ) . '</button><p class="wla-exp-error" aria-live="polite"></p><output class="wla-exp-output" aria-live="polite"></output><p class="wla-exp-disclaimer">' . esc_html__( 'BMI is a screening calculation based on height and weight; it is not a diagnosis or a measure of individual health.', 'wla-experience' ) . '</p></form>';
	$out .= '<form class="wla-exp-calculator" data-wla-calculator="weight-change" novalidate><h3>' . esc_html( $change_title ) . '</h3>' . ( $change_intro ? '<p class="wla-exp-calculator-intro">' . esc_html( $change_intro ) . '</p>' : '' ) . '<label>' . esc_html__( 'Units', 'wla-experience' ) . '<select name="change_unit"><option value="lb">' . esc_html__( 'Pounds (lb)', 'wla-experience' ) . '</option><option value="kg">' . esc_html__( 'Kilograms (kg)', 'wla-experience' ) . '</option></select></label><label><span data-weight-label="starting">' . esc_html__( 'Starting weight (lb)', 'wla-experience' ) . '</span><input name="starting" type="number" min="0.1" step="0.1" inputmode="decimal" required></label><label><span data-weight-label="current">' . esc_html__( 'Current or comparison weight (lb)', 'wla-experience' ) . '</span><input name="current" type="number" min="0.1" step="0.1" inputmode="decimal" required></label><button class="wla-exp-button" type="submit">' . esc_html__( 'Calculate change', 'wla-experience' ) . '</button><p class="wla-exp-error" aria-live="polite"></p><output class="wla-exp-output" aria-live="polite"></output><p class="wla-exp-disclaimer">' . esc_html__( 'This is an arithmetic comparison only, not a predicted treatment outcome.', 'wla-experience' ) . '</p></form>';
	$out .= '</div>' . wla_experience_section_end();
	return $out;
}
add_shortcode( 'wla_calculators', 'wla_experience_calculators_shortcode' );
