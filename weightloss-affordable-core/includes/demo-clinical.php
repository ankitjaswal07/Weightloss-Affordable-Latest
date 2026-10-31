<?php
/**
 * Publish the clinical site on demo import: quiz, treatments, Get Quote page,
 * English defaults, menus and Elementor placements.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_el_enrich_sections( $sections ) {
	$body = array();
	$tail = array();
	$have = array();
	foreach ( (array) $sections as $sec ) {
		$type = $sec['elements'][0]['widgetType'] ?? '';
		if ( in_array( $type, array( 'wla_cta_band', 'wla_page_nav', 'wla_contact_section' ), true ) ) {
			$tail[] = $sec;
		} else {
			$body[] = $sec;
			$have[] = $type;
		}
	}
	$fillers = array( 'wla_treatments_duo', 'wla_guarantee', 'wla_science', 'wla_glp1_signal', 'wla_transformations', 'wla_video_stories' );
	foreach ( $fillers as $widget ) {
		if ( count( $body ) >= 6 ) {
			break;
		}
		if ( ! in_array( $widget, $have, true ) ) {
			$body[] = wla_el_section_widget( $widget );
			$have[] = $widget;
		}
	}
	return array_merge( $body, $tail );
}

function wla_clinical_el_quiz_page( $title, $lead, $badge ) {
	return wla_el_enrich_sections(
		array(
			wla_el_section_widget(
				'wla_subpage_hero',
				array(
					'eyebrow' => 'GET A QUOTE',
					'title'   => $title,
					'lead'    => $lead,
					'badge'   => $badge,
				)
			),
			wla_el_section_widget(
				'wla-experience-component',
				array(
					'module'     => 'get_quote',
					'quote_mode' => 'quiz',
					'title'      => __( 'Step-by-step enquiry', 'weightloss-affordable-core' ),
					'intro'      => __( 'Answer each step. You can go back without losing your answers. Submitting is not a diagnosis or a guaranteed prescription.', 'weightloss-affordable-core' ),
				)
			),
			wla_el_section_widget(
				'wla_cta_band',
				array(
					'eyebrow'     => 'TREATMENTS',
					'title'       => 'Prefer to read first?',
					'body'        => 'Open a treatment page, choose a package, then return to Get Quote with that selection.',
					'button_text' => 'View treatments',
					'button_url'  => '/weight-loss-services/',
				)
			),
			wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => 'consultation-eligibility' ) ),
		)
	);
}

function wla_clinical_assign_elementor( $post_id, $el_data ) {
	if ( ! $post_id ) {
		return;
	}
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $el_data ) ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
	}
}

function wla_import_clinical_site( &$report, $page_ids = array() ) {
	$report['treatments'] = isset( $report['treatments'] ) ? $report['treatments'] : 0;
	$report['quiz']       = false;

	update_option( 'WPLANG', '' );
	update_option( 'blog_public', '1' );
	update_option( 'WPLANG', '' );

	if ( function_exists( 'nrx_update_settings' ) ) {
		nrx_update_settings(
			array(
				'default_lang'             => 'en',
				'quiz_submissions_enabled' => 1,
				'store_clinical_answers'   => 1,
				'brand_name'               => 'WeightLossAffordable',
				'intake_slug'              => 'consultation-eligibility',
				'header_cta_url'           => '/consultation-eligibility/',
				'privacy_page_url'         => '/privacy-policy/',
				'terms_page_url'           => '/terms-and-conditions/',
				'safety_page_url'          => '/treatment-safety-information/',
				'refund_page_url'          => '/refund-cancellation-policy/',
				'quiz_notify_email'        => get_option( 'admin_email' ),
			)
		);
	}

	if ( function_exists( 'nrx_register_post_types' ) ) {
		nrx_register_post_types();
	}

	if ( function_exists( 'nrx_quiz_default_config' ) && function_exists( 'nrx_quiz_publish' ) ) {
		$draft = nrx_quiz_default_config();
		$draft['version'] = 1;
		nrx_quiz_save_draft( $draft );
		$published = nrx_quiz_publish( $draft, 'Demo import published the Get Quote quiz.' );
		$report['quiz'] = ! is_wp_error( $published );
		if ( is_wp_error( $published ) ) {
			update_option( 'nrx_quiz_published', nrx_quiz_normalize( $draft ), false );
			update_option( 'nrx_quiz_draft', nrx_quiz_normalize( $draft ), false );
			$report['quiz']       = true;
			$report['warnings'][] = 'Quiz published from defaults after validation warning: ' . $published->get_error_message();
		}
	}

	if ( function_exists( 'nrx_seed_treatment' ) && defined( 'NRX_CORE_DIR' ) ) {
		$records = require NRX_CORE_DIR . 'content/treatments.php';
		foreach ( $records as $slug => $record ) {
			$record['fields']['require_approval'] = 0;
			$id = nrx_seed_treatment( $slug, $record, true );
			if ( $id ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
				wla_clinical_assign_elementor(
					$id,
					wla_el_enrich_sections(
						array(
							wla_el_section_widget(
								'wla_subpage_hero',
								array(
									'eyebrow' => 'TREATMENT',
									'title'   => $record['title'],
									'lead'    => wp_strip_all_tags( $record['fields']['tagline_en'] ?? $record['fields']['tagline'] ?? '' ),
									'badge'   => 'Choose a package, then Get Quote',
								)
							),
							wla_el_section_widget(
								'wla-experience-component',
								array(
									'module'         => 'treatment_detail',
									'treatment_slug' => $slug,
								)
							),
							wla_el_section_widget( 'wla_guarantee', array() ),
							wla_el_section_widget( 'wla_science', array() ),
							wla_el_section_widget(
								'wla_cta_band',
								array(
									'eyebrow'     => 'NEXT STEP',
									'title'       => 'Ready to request a quote?',
									'body'        => 'Choosing a package sends you to Get Quote with that treatment selected. Completing the quiz is not a diagnosis.',
									'button_text' => 'Get a quote',
									'button_url'  => '/consultation-eligibility/?treatment=' . rawurlencode( $slug ),
								)
							),
							wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => $slug ) ),
						)
					)
				);
				update_post_meta( $id, '_wla_seo_title', $record['title'] . ' | WeightLossAffordable' );
				update_post_meta( $id, '_wla_seo_description', wp_strip_all_tags( $record['fields']['tagline_en'] ?? $record['fields']['tagline'] ?? '' ) );
				update_post_meta( $id, '_wla_seo_noindex', '0' );
				++$report['treatments'];
			}
		}
		delete_transient( 'nrx_treatment_routes' );
	}

	$extra_pages = array(
		'get-quote'        => array(
			'title' => 'Get Quote',
			'lead'  => 'Complete the step-by-step enquiry. You can move backward and forward without losing answers.',
			'quiz'  => true,
		),
		'weekly-payment'   => array(
			'title' => 'Weekly payment',
			'lead'  => 'Some programs describe a weekly payment option in addition to monthly and multi-month plans. Confirm live billing terms before publication. This page does not create a checkout.',
		),
		'how-glp1-works'   => array(
			'title' => 'How GLP-1 medications work',
			'lead'  => 'GLP-1 receptor agonists mimic a gut hormone involved in appetite and digestion. They are prescription medications. Compounded versions are not FDA-approved and are not bioequivalent to brand products.',
		),
		'clinicians'       => array(
			'title' => 'Clinicians',
			'lead'  => 'Clinical care, if offered, must be provided by independently licensed professionals. Do not invent credentials. Replace this educational copy with verified practitioner information before launch.',
		),
		'pharmacies'       => array(
			'title' => 'Pharmacy transparency',
			'lead'  => 'If prescriptions are fulfilled, name only licensed pharmacies you actually work with. Compounded medications are prepared by 503A or 503B pharmacies under clinician direction.',
		),
	);

	foreach ( $extra_pages as $slug => $meta ) {
		$content = wpautop( $meta['lead'] );
		if ( empty( $meta['quiz'] ) ) {
			$content .= '<p>' . esc_html__( 'This information is educational and does not replace a conversation with a licensed clinician.', 'weightloss-affordable-core' ) . '</p>';
		}
		$pid = wla_upsert_single_post( 'page', $slug, $meta['title'], $content, 'publish', 'single-page-' . $slug, $meta['lead'] );
		if ( $pid ) {
			$page_ids[ $slug ] = $pid;
			++$report['pages'];
			if ( ! empty( $meta['quiz'] ) ) {
				update_post_meta( $pid, '_wp_page_template', 'page-templates/intake.php' );
				wla_clinical_assign_elementor( $pid, wla_clinical_el_quiz_page( $meta['title'], $meta['lead'], 'Published step-by-step quiz' ) );
			} else {
				$el = wla_el_enrich_sections(
					array(
						wla_el_section_widget(
							'wla_subpage_hero',
							array(
								'eyebrow' => strtoupper( $meta['title'] ),
								'title'   => $meta['title'],
								'lead'    => $meta['lead'],
								'badge'   => 'Educational page · English',
							)
						),
						wla_el_section_widget(
							'wla_cta_band',
							array(
								'eyebrow'     => 'NEXT STEP',
								'title'       => 'Ready to ask about eligibility?',
								'body'        => 'Open the Get Quote quiz. Completing it is not a diagnosis or a guaranteed prescription.',
								'button_text' => 'Get a quote',
								'button_url'  => '/consultation-eligibility/',
							)
						),
						wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => $slug ) ),
					)
				);
				wla_clinical_assign_elementor( $pid, $el );
			}
			update_post_meta( $pid, '_wla_seo_title', $meta['title'] . ' | WeightLossAffordable' );
			update_post_meta( $pid, '_wla_seo_description', $meta['lead'] );
			update_post_meta( $pid, '_wla_seo_noindex', '0' );
		}
	}

	if ( ! empty( $page_ids['consultation-eligibility'] ) ) {
		update_post_meta( $page_ids['consultation-eligibility'], '_wp_page_template', 'page-templates/intake.php' );
		wla_clinical_assign_elementor(
			$page_ids['consultation-eligibility'],
			wla_clinical_el_quiz_page(
				'Start with a clear, step-by-step enquiry.',
				'This quiz maps every question to a stored field. Required answers are validated before you continue. Submitting is not medical approval.',
				'Get Quote quiz · published'
			)
		);
		wp_update_post( array( 'ID' => $page_ids['consultation-eligibility'], 'post_status' => 'publish' ) );
	}

	if ( ! empty( $page_ids['weight-loss-services'] ) ) {
		$services_el = wla_el_enrich_sections(
			array(
				wla_el_section_widget(
					'wla_subpage_hero',
					array(
						'eyebrow' => 'TREATMENTS',
						'title'   => 'Treatment options you can review in detail.',
						'lead'    => 'Each published treatment has its own page, unique educational copy, and a Get Quote call to action.',
						'badge'   => 'Published treatment catalog',
					)
				),
				wla_el_section_widget(
					'wla-experience-component',
					array(
						'module' => 'treatment_plans',
						'title'  => 'Published treatments',
						'intro'  => 'Open a treatment to read the overview, choose a package, then submit Get Quote.',
						'limit'  => 8,
					)
				),
				wla_el_section_widget( 'wla_treatments_duo', array() ),
				wla_el_section_widget( 'wla_science', array() ),
				wla_el_section_widget(
					'wla_cta_band',
					array(
						'eyebrow'     => 'NEXT STEP',
						'title'       => 'Choose a treatment, then a package.',
						'body'        => 'Package buttons open Get Quote with that treatment selected.',
						'button_text' => 'Get a quote',
						'button_url'  => '/consultation-eligibility/',
					)
				),
				wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => 'weight-loss-services' ) ),
			)
		);
		wla_clinical_assign_elementor( $page_ids['weight-loss-services'], $services_el );
	}

	$drafts = get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team', 'nrx_treatment' ),
			'post_status'    => array( 'draft', 'pending', 'private' ),
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_wla_demo_key',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	foreach ( $drafts as $draft_id ) {
		$type = get_post_type( $draft_id );
		if ( 'nrx_intake' === $type ) {
			continue;
		}
		wp_update_post( array( 'ID' => absint( $draft_id ), 'post_status' => 'publish' ) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menus     = array(
		'primary' => array( 'home', 'about', 'weight-loss-services', 'how-it-works', 'consultation-eligibility', 'frequently-asked-questions', 'contact' ),
		'footer'  => array( 'about', 'weight-loss-services', 'get-quote', 'weekly-payment', 'how-glp1-works', 'privacy-policy', 'terms-and-conditions', 'medical-disclaimer', 'treatment-safety-information', 'refund-cancellation-policy' ),
	);
	foreach ( $menus as $loc => $slugs ) {
		$menu_name = 'WeightLossAffordable ' . ucfirst( $loc );
		$menu_obj  = wp_get_nav_menu_object( $menu_name );
		$menu_id   = $menu_obj ? absint( $menu_obj->term_id ) : wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) || ! $menu_id ) {
			continue;
		}
		foreach ( wp_get_nav_menu_items( $menu_id ) ?: array() as $old_item ) {
			wp_delete_post( $old_item->ID, true );
		}
		foreach ( $slugs as $s_key ) {
			if ( empty( $page_ids[ $s_key ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $page_ids[ $s_key ] ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => absint( $page_ids[ $s_key ] ),
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
		$sema = get_page_by_path( 'semaglutida', OBJECT, 'nrx_treatment' );
		$tirz = get_page_by_path( 'tirzepatida', OBJECT, 'nrx_treatment' );
		if ( 'primary' === $loc ) {
			foreach ( array( $sema, $tirz ) as $treatment ) {
				if ( $treatment ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'     => get_the_title( $treatment ),
							'menu-item-object'    => 'nrx_treatment',
							'menu-item-object-id' => (int) $treatment->ID,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
		$locations[ $loc ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'elementor_cpt_support', array( 'page', 'post', 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team', 'nrx_treatment' ) );

	if ( function_exists( 'flush_rewrite_rules' ) ) {
		flush_rewrite_rules( false );
	}

	return $page_ids;
}
