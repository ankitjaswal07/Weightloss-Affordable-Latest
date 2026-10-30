<?php
/**
 * WLA-styled treatment details backed by NRX records or labeled client-demo copy.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_experience_render_demo_treatment_detail( $slug ) {
	foreach ( wla_experience_demo_treatments() as $demo ) {
		if ( ! in_array( $slug, array( $demo['slug'], $demo['nrx_slug'] ), true ) ) {
			continue;
		}
		$out  = '<section class="wla-exp-section wla-exp-treatment-detail wla-exp-demo-card"><div class="wla-exp-detail-hero"><div class="wla-exp-detail-copy">';
		$out .= '<p class="wla-exp-demo-label">' . esc_html__( 'DEMO CONTENT · verify before publication', 'wla-experience' ) . '</p>';
		$out .= '<p class="wla-exp-eyebrow">' . esc_html( $demo['class'] ) . '</p>';
		$out .= '<h1 id="wla-treatment-detail-title">' . esc_html( $demo['title'] ) . '</h1>';
		$out .= '<p class="wla-exp-treatment-type">' . esc_html( $demo['type'] ) . '</p>';
		$out .= '<p class="wla-exp-intro">' . esc_html( $demo['description'] ) . '</p>';
		$out .= '<p class="wla-exp-demo-price"><span>' . esc_html__( 'from', 'wla-experience' ) . '</span> <s>$' . esc_html( number_format_i18n( $demo['compare'], 0 ) ) . '</s> <strong>$' . esc_html( number_format_i18n( $demo['price'], 0 ) ) . '</strong><small>/' . esc_html__( 'mo', 'wla-experience' ) . '</small></p>';
		$out .= '<a class="wla-exp-button" href="' . esc_url( home_url( '/consultation-eligibility/' ) ) . '">' . esc_html__( 'Get a quote', 'wla-experience' ) . '<span aria-hidden="true"> →</span></a></div></div>';
		$out .= '<div class="wla-exp-detail-section"><ul class="wla-exp-detail-list">';
		foreach ( $demo['bullets'] as $bullet ) {
			$out .= '<li>' . esc_html( $bullet ) . '</li>';
		}
		$out .= '</ul></div><p class="wla-exp-detail-disclaimer">' . esc_html__( 'Demo copy and example prices are not live clinical or billing terms. Confirm product, pharmacy, clinical, pricing and legal details before publication. Individual results vary.', 'wla-experience' ) . '</p></section>';
		return $out;
	}
	return '';
}

function wla_experience_items( $items ) {
	$out = array();
	foreach ( (array) $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = sanitize_text_field( $item['title'] ?? $item['label'] ?? $item['question'] ?? '' );
		$body  = isset( $item['body'] ) ? wp_kses_post( $item['body'] ) : ( isset( $item['answer'] ) ? wp_kses_post( $item['answer'] ) : '' );
		$label = sanitize_text_field( $item['label'] ?? '' );
		if ( $title || $body || $label ) {
			$out[] = array(
				'title' => $title,
				'body'  => $body,
				'label' => $label,
			);
		}
	}
	return $out;
}

function wla_experience_quote_href() {
	if ( function_exists( 'wla_experience_quote_url' ) ) {
		$url = wla_experience_quote_url();
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/consultation-eligibility/' );
}

function wla_experience_render_treatment_detail( $atts ) {
	$atts      = shortcode_atts( array( 'slug' => '', 'title' => '', 'demo' => 'no' ), $atts, 'wla_treatment_detail' );
	$slug      = sanitize_title( $atts['slug'] );
	$show_demo = 'yes' === sanitize_key( $atts['demo'] );
	if ( ! function_exists( 'nrx_treatment_data' ) || ! post_type_exists( 'nrx_treatment' ) ) {
		return $show_demo ? wla_experience_render_demo_treatment_detail( $slug ) : ( current_user_can( 'edit_posts' ) ? '<p class="wla-exp-empty">' . esc_html__( 'A published treatment record is required.', 'wla-experience' ) . '</p>' : '' );
	}
	$post = $slug ? get_page_by_path( $slug, OBJECT, 'nrx_treatment' ) : get_post();
	if ( ! $post || 'nrx_treatment' !== $post->post_type ) {
		return $show_demo ? wla_experience_render_demo_treatment_detail( $slug ) : '';
	}
	$data = nrx_treatment_data( $post );
	if ( empty( $data ) || ! empty( $data['require_approval'] ) ) {
		return $show_demo ? wla_experience_render_demo_treatment_detail( $slug ) : '';
	}

	$name        = sanitize_text_field( $atts['title'] ?: ( $data['name'] ?? get_the_title( $post ) ) );
	$tagline     = sanitize_text_field( $data['tagline'] ?? '' );
	$description = isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '';
	$image       = ! empty( $data['image'] ) && is_string( $data['image'] ) ? $data['image'] : '';
	if ( ! $image && has_post_thumbnail( $post ) ) {
		$image = get_the_post_thumbnail_url( $post, 'large' );
	}
	$plans   = is_array( $data['plans'] ?? null ) ? $data['plans'] : array();
	$quote   = wla_experience_quote_href();
	$price   = ! empty( $data['from_price'] ) && function_exists( 'nrx_treatment_price_label' ) ? nrx_treatment_price_label( $data ) : '';

	$out  = '<article class="wla-exp-section wla-exp-treatment-detail" aria-labelledby="wla-treatment-detail-title">';
	$out .= '<div class="wla-exp-detail-hero">';
	if ( $image ) {
		$out .= '<img class="wla-exp-detail-image" src="' . esc_url( $image ) . '" alt="' . esc_attr( $data['image_alt'] ?? $name ) . '" loading="eager" decoding="async">';
	}
	$out .= '<div class="wla-exp-detail-copy">';
	$out .= '<p class="wla-exp-eyebrow">' . esc_html( $data['eyebrow'] ?? __( 'Treatment information', 'wla-experience' ) ) . '</p>';
	$out .= '<h1 id="wla-treatment-detail-title">' . esc_html( $name ) . '</h1>';
	if ( ! empty( $data['badge'] ) ) {
		$out .= '<p class="wla-exp-treatment-type">' . esc_html( $data['badge'] ) . '</p>';
	}
	if ( $tagline ) {
		$out .= '<p class="wla-exp-intro">' . esc_html( $tagline ) . '</p>';
	}
	if ( $price ) {
		$out .= '<p class="wla-exp-detail-price">' . esc_html( $price ) . '<small>' . esc_html__( 'per month from the configured plan record. Confirm live pricing before checkout.', 'wla-experience' ) . '</small></p>';
	}
	$out .= '<a class="wla-exp-button" href="' . esc_url( $quote ) . '">' . esc_html__( 'Get a quote', 'wla-experience' ) . '<span aria-hidden="true"> →</span></a>';
	$out .= '</div></div>';

	if ( $description ) {
		$out .= '<section class="wla-exp-detail-section" id="overview"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Overview', 'wla-experience' ) . '</p><h2>' . esc_html__( 'What this treatment is', 'wla-experience' ) . '</h2></div><div class="wla-exp-detail-description">' . $description . '</div></section>';
	}

	$ideal = wla_experience_items( $data['ideal_items'] ?? array() );
	$avoid = wla_experience_items( $data['avoid_items'] ?? array() );
	if ( $ideal || $avoid ) {
		$out .= '<section class="wla-exp-detail-section" id="suitability"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Benefits and suitability', 'wla-experience' ) . '</p><h2>' . esc_html( $data['ideal_title'] ?: __( 'Who it may be for', 'wla-experience' ) ) . '</h2></div><div class="wla-exp-split-lists">';
		if ( $ideal ) {
			$out .= '<div><h3>' . esc_html__( 'May be considered if', 'wla-experience' ) . '</h3><ul class="wla-exp-detail-list">';
			foreach ( $ideal as $item ) {
				$out .= '<li>' . esc_html( $item['label'] ?: $item['title'] ) . '</li>';
			}
			$out .= '</ul></div>';
		}
		if ( $avoid ) {
			$out .= '<div><h3>' . esc_html( $data['avoid_title'] ?: __( 'Not recommended if', 'wla-experience' ) ) . '</h3><ul class="wla-exp-detail-list">';
			foreach ( $avoid as $item ) {
				$out .= '<li>' . esc_html( $item['label'] ?: $item['title'] ) . '</li>';
			}
			$out .= '</ul></div>';
		}
		$out .= '</div></section>';
	}

	$mechanisms = wla_experience_items( $data['mechanisms'] ?? array() );
	if ( $mechanisms || ! empty( $data['mechanism_body'] ) ) {
		$out .= '<section class="wla-exp-detail-section" id="how-it-works"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'How it works', 'wla-experience' ) . '</p><h2>' . esc_html( $data['mechanism_title'] ?: __( 'Treatment overview', 'wla-experience' ) ) . '</h2>';
		if ( ! empty( $data['mechanism_body'] ) ) {
			$out .= '<p class="wla-exp-intro">' . esc_html( $data['mechanism_body'] ) . '</p>';
		}
		$out .= '</div>';
		if ( $mechanisms ) {
			$out .= '<div class="wla-exp-grid">';
			foreach ( $mechanisms as $item ) {
				$out .= '<article class="wla-exp-card"><div class="wla-exp-card-body"><h3>' . esc_html( $item['title'] ) . '</h3>';
				if ( $item['body'] ) {
					$out .= '<p>' . wp_kses_post( $item['body'] ) . '</p>';
				}
				$out .= '</div></article>';
			}
			$out .= '</div>';
		}
		$out .= '</section>';
	}

	$process = wla_experience_items( $data['process_items'] ?? array() );
	if ( $process || ! empty( $data['process_body'] ) ) {
		$out .= '<section class="wla-exp-detail-section" id="process"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Treatment process', 'wla-experience' ) . '</p><h2>' . esc_html( $data['process_title'] ?: __( 'What to expect', 'wla-experience' ) ) . '</h2>';
		if ( ! empty( $data['process_body'] ) ) {
			$out .= '<p class="wla-exp-intro">' . esc_html( $data['process_body'] ) . '</p>';
		}
		$out .= '</div>';
		if ( $process ) {
			$out .= '<ol class="wla-exp-steps">';
			$i = 1;
			foreach ( $process as $item ) {
				$out .= '<li><span>' . esc_html( sprintf( '%02d', $i ) ) . '</span><strong>' . esc_html( $item['title'] ) . '</strong>';
				if ( $item['body'] ) {
					$out .= '<p>' . wp_kses_post( $item['body'] ) . '</p>';
				}
				$out .= '</li>';
				++$i;
			}
			$out .= '</ol>';
		}
		$out .= '</section>';
	}

	if ( ! empty( $data['recovery_body'] ) ) {
		$out .= '<section class="wla-exp-detail-section" id="safety"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Safety and aftercare', 'wla-experience' ) . '</p><h2>' . esc_html( $data['recovery_title'] ?: __( 'Safety, recovery and aftercare', 'wla-experience' ) ) . '</h2><p class="wla-exp-intro">' . esc_html( $data['recovery_body'] ) . '</p></div></section>';
	}

	if ( $plans ) {
		$out .= '<section class="wla-exp-detail-section" id="plans"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Treatment options', 'wla-experience' ) . '</p><h2>' . esc_html__( 'Plans and variations', 'wla-experience' ) . '</h2><p class="wla-exp-intro">' . esc_html__( 'Figures below come from the editable treatment record. Confirm them with your clinical and billing teams before publication.', 'wla-experience' ) . '</p></div><div class="wla-exp-grid wla-exp-plan-grid">';
		foreach ( $plans as $plan ) {
			$monthly = isset( $plan['monthly'] ) ? (float) $plan['monthly'] : 0;
			$total   = isset( $plan['total'] ) ? (float) $plan['total'] : 0;
			$months  = isset( $plan['months'] ) ? absint( $plan['months'] ) : 0;
			$out    .= '<article class="wla-exp-card wla-exp-plan-card">';
			if ( ! empty( $plan['badge'] ) ) {
				$out .= '<p class="wla-exp-eyebrow">' . esc_html( $plan['badge'] ) . '</p>';
			}
			$out .= '<h3>' . esc_html( $plan['name'] ?? '' ) . '</h3>';
			if ( $monthly > 0 ) {
				$out .= '<p class="wla-exp-price">$' . esc_html( number_format_i18n( $monthly, 0 ) ) . '<small>' . esc_html__( 'per month', 'wla-experience' ) . '</small></p>';
			}
			if ( $total > 0 && $months > 1 ) {
				$out .= '<p>' . esc_html( sprintf( __( '$%1$s total for %2$d months', 'wla-experience' ), number_format_i18n( $total, 0 ), $months ) ) . '</p>';
			}
			if ( ! empty( $plan['doses'] ) ) {
				$out .= '<p>' . esc_html( $plan['doses'] ) . '</p>';
			}
			if ( ! empty( $plan['note'] ) ) {
				$out .= '<p class="wla-exp-disclaimer">' . esc_html( $plan['note'] ) . '</p>';
			}
			$plan_url = add_query_arg(
				array(
					'treatment' => $post->post_name,
					'plan'      => sanitize_title( $plan['name'] ?? 'plan' ),
				),
				$quote
			);
			$out .= '<a class="button button-primary" href="' . esc_url( $plan_url ) . '">' . esc_html__( 'Choose this package', 'wla-experience' ) . '</a>';
			$out .= '</article>';
		}
		$out .= '</div></section>';
	}

	$bullets = is_array( $data['bullets'] ?? null ) ? $data['bullets'] : array();
	if ( $bullets ) {
		$out .= '<section class="wla-exp-detail-section"><div class="wla-exp-heading"><h2>' . esc_html__( 'What is typically included', 'wla-experience' ) . '</h2></div><ul class="wla-exp-detail-list">';
		foreach ( $bullets as $bullet ) {
			if ( ! empty( $bullet['label'] ) ) {
				$out .= '<li>' . esc_html( $bullet['label'] ) . '</li>';
			}
		}
		$out .= '</ul></section>';
	}

	$faqs = wla_experience_items( $data['faqs'] ?? array() );
	if ( $faqs ) {
		$out .= '<section class="wla-exp-detail-section" id="faqs"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'FAQs', 'wla-experience' ) . '</p><h2>' . esc_html__( 'Frequently asked questions', 'wla-experience' ) . '</h2></div><div class="wla-exp-faq-list">';
		foreach ( $faqs as $faq ) {
			$out .= '<details class="wla-exp-faq"><summary>' . esc_html( $faq['title'] ) . '</summary><div>' . wp_kses_post( $faq['body'] ) . '</div></details>';
		}
		$out .= '</div></section>';
	}

	$out .= '<section class="wla-exp-detail-section wla-exp-detail-cta-block" id="get-quote"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Get a quote', 'wla-experience' ) . '</p><h2>' . esc_html__( 'Continue with the step-by-step enquiry', 'wla-experience' ) . '</h2><p class="wla-exp-intro">' . esc_html__( 'The quiz collects contact and eligibility information. Completing it is not a diagnosis, a guaranteed approval, or a prescription.', 'wla-experience' ) . '</p></div>';
	if ( shortcode_exists( 'nrx_quiz' ) && function_exists( 'wla_experience_nrx_quiz_is_configured' ) && wla_experience_nrx_quiz_is_configured() ) {
		$out .= '<div class="wla-exp-nrx-quiz">' . do_shortcode( '[nrx_quiz intro="0"]' ) . '</div>';
	} else {
		$out .= '<div class="wla-exp-detail-cta"><a class="wla-exp-button" href="' . esc_url( $quote ) . '">' . esc_html__( 'Open the Get Quote quiz', 'wla-experience' ) . '<span aria-hidden="true"> →</span></a></div>';
	}
	$out .= '</section>';

	$related_slugs = array_filter( array_map( 'sanitize_title', explode( ',', (string) ( $data['related_slugs'] ?? '' ) ) ) );
	if ( $related_slugs ) {
		$out .= '<section class="wla-exp-detail-section" id="related"><div class="wla-exp-heading"><p class="wla-exp-eyebrow">' . esc_html__( 'Related treatments', 'wla-experience' ) . '</p><h2>' . esc_html__( 'Other options to review', 'wla-experience' ) . '</h2></div><div class="wla-exp-grid">';
		foreach ( $related_slugs as $related_slug ) {
			$related = get_page_by_path( $related_slug, OBJECT, 'nrx_treatment' );
			if ( ! $related ) {
				continue;
			}
			$rel = nrx_treatment_data( $related );
			$out .= '<article class="wla-exp-card"><div class="wla-exp-card-body"><p class="wla-exp-eyebrow">' . esc_html( $rel['eyebrow'] ?? '' ) . '</p><h3>' . esc_html( get_the_title( $related ) ) . '</h3><p>' . esc_html( $rel['tagline'] ?? '' ) . '</p><a class="wla-exp-text-link" href="' . esc_url( get_permalink( $related ) ) . '">' . esc_html__( 'Read this treatment', 'wla-experience' ) . '</a></div></article>';
		}
		$out .= '</div></section>';
	}

	if ( ! empty( $data['disclaimer'] ) ) {
		$out .= '<p class="wla-exp-detail-disclaimer">' . esc_html( $data['disclaimer'] ) . '</p>';
	}
	if ( $faqs ) {
		$faq_schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);
		foreach ( $faqs as $faq ) {
			$faq_schema['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => $faq['title'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $faq['body'] ),
				),
			);
		}
		$out .= '<script type="application/ld+json">' . wp_json_encode( $faq_schema ) . '</script>';
	}
	$out .= '</article>';
	return $out;
}
add_shortcode( 'wla_treatment_detail', 'wla_experience_render_treatment_detail' );
