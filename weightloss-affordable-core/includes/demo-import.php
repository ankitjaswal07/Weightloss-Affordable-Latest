<?php
/**
 * One-Click Demo Importer — Creates ONE Clean Canonical Set of 18 Pages (ZERO Copy Pages!),
 * 100% Preview-Identical Free Elementor Page Layouts, Local Media Library Assets,
 * Educational Articles, Draft CPT Placeholders, Menus, and Reusable Elementor Templates.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_demo_import_menu() {
	add_menu_page(
		__( 'WeightLoss Setup', 'weightloss-affordable-core' ),
		__( 'WeightLoss Setup', 'weightloss-affordable-core' ),
		'manage_options',
		'wla-demo-import',
		'wla_demo_import_screen',
		'dashicons-heart',
		58
	);
}
add_action( 'admin_menu', 'wla_demo_import_menu' );

function wla_demo_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_transient( 'wla_demo_import_report_' . get_current_user_id() );
	if ( $report ) {
		delete_transient( 'wla_demo_import_report_' . get_current_user_id() );
	}
	$theme           = wp_get_theme();
	$theme_ready     = 'weightloss-affordable' === $theme->get_template();
	$elementor_ready = did_action( 'elementor/loaded' ) > 0;
	?>
	<div class="wrap wla-admin-wrap" style="max-width:900px">
		<h1><?php esc_html_e( 'WeightLossAffordable — 100% Preview One-Click Importer', 'weightloss-affordable-core' ); ?></h1>
		<p><?php esc_html_e( 'Imports the exact Straw Luxury Preview website with 18 single-copy canonical pages (zero duplicate Spanish copy pages!), full Free Elementor widgets, interactive 4-Stage Journey Board, interactive Guide Explorer + Modal Reader, local media assets, sample educational articles, and built-in instant EN/ES full-site translation.', 'weightloss-affordable-core' ); ?></p>

		<ul style="margin:18px 0;padding:14px 18px;background:#fff;border:1px solid #dcdcde;line-height:2">
			<li><?php echo $theme_ready ? '✓' : '⚠'; ?> <?php echo esc_html( sprintf( __( 'Theme: %s', 'weightloss-affordable-core' ), $theme->get( 'Name' ) ) ); ?></li>
			<li><?php echo $elementor_ready ? '✓' : '⚠'; ?> <?php esc_html_e( 'Free Elementor: Install & activate Free Elementor to visually edit all page sections (Elementor Pro NOT required).', 'weightloss-affordable-core' ); ?></li>
			<li>✓ <?php esc_html_e( 'Built-in Live EN/ES Translator: Active out of the box (no duplicate copy pages needed!).', 'weightloss-affordable-core' ); ?></li>
		</ul>

		<?php if ( is_array( $report ) ) : ?>
			<div class="notice <?php echo ! empty( $report['fatal'] ) ? 'notice-error' : 'notice-success'; ?> inline">
				<p><strong><?php echo ! empty( $report['fatal'] ) ? esc_html__( 'Import stopped', 'weightloss-affordable-core' ) : esc_html__( 'Import complete!', 'weightloss-affordable-core' ); ?></strong></p>
				<?php if ( ! empty( $report['summary'] ) ) : ?><p><?php echo esc_html( $report['summary'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $report['warnings'] ) ) : ?>
					<ul><?php foreach ( $report['warnings'] as $w ) : ?><li><?php echo esc_html( $w ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form id="wla-demo-import-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wla_import_demo" />
			<?php wp_nonce_field( 'wla_import_demo', 'wla_import_nonce' ); ?>
			<?php submit_button( __( 'Import Complete 100% Preview Website', 'weightloss-affordable-core' ), 'primary large', 'submit', false ); ?>
		</form>
		<p id="wla-import-progress" class="notice notice-info inline" role="status" hidden><?php esc_html_e( 'Importing media, Elementor pages, menus, and templates…', 'weightloss-affordable-core' ); ?></p>
		<script>
		(function(){var f=document.getElementById('wla-demo-import-form');if(!f)return;f.addEventListener('submit',function(){var s=document.getElementById('wla-import-progress');if(s)s.hidden=false;var b=f.querySelector('input[type="submit"]');if(b){b.disabled=true;b.value='Importing…';}});}());
		</script>
	</div>
	<?php
}

function wla_demo_import_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'weightloss-affordable-core' ) );
	}
	check_admin_referer( 'wla_import_demo', 'wla_import_nonce' );
	$report = wla_run_demo_import();
	set_transient( 'wla_demo_import_report_' . get_current_user_id(), $report, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=wla-demo-import' ) );
	exit;
}
add_action( 'admin_post_wla_import_demo', 'wla_demo_import_handler' );

function wla_el_id() {
	return substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 8 );
}

function wla_el_section_widget( $widget_type, $settings = array() ) {
	return array(
		'id'       => wla_el_id(),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array(
			'content_width' => 'full',
			'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
		),
		'elements' => array(
			array(
				'id'         => wla_el_id(),
				'elType'     => 'widget',
				'widgetType' => $widget_type,
				'settings'   => $settings,
				'elements'   => array(),
			),
		),
	);
}

function wla_import_local_media_single( $file_name, $alt, &$report ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_wla_demo_asset_file',
			'meta_value'     => $file_name,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		return absint( $existing[0] );
	}
	$source = WLA_CORE_DIR . 'demo/images/' . $file_name;
	if ( ! is_readable( $source ) ) {
		$report['warnings'][] = sprintf( 'Missing local image: %s', $file_name );
		return 0;
	}
	$bytes = file_get_contents( $source );
	if ( false === $bytes ) {
		return 0;
	}
	$upload = wp_upload_bits( $file_name, null, $bytes );
	if ( ! empty( $upload['error'] ) ) {
		$report['warnings'][] = $upload['error'];
		return 0;
	}
	$filetype      = wp_check_filetype( $upload['file'], null );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_text_field( pathinfo( $file_name, PATHINFO_FILENAME ) ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}
	$meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	if ( is_array( $meta ) ) {
		wp_update_attachment_metadata( $attachment_id, $meta );
	}
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	update_post_meta( $attachment_id, '_wla_demo_asset_file', $file_name );
	$report['assets']++;
	return absint( $attachment_id );
}

function wla_upsert_single_post( $post_type, $slug, $title, $content, $status, $record_key, $excerpt = '' ) {
	$found = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'meta_key'       => '_wla_demo_key',
			'meta_value'     => $record_key,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	$existing_id = ! empty( $found ) ? absint( $found[0] ) : 0;
	if ( ! $existing_id ) {
		$by_path     = get_page_by_path( $slug, OBJECT, $post_type );
		$existing_id = $by_path instanceof WP_Post ? absint( $by_path->ID ) : 0;
	}
	$post_id = wp_insert_post(
		array(
			'ID'             => $existing_id,
			'post_type'      => $post_type,
			'post_status'    => $status,
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => wp_kses_post( $content ),
			'post_excerpt'   => sanitize_textarea_field( $excerpt ),
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}
	update_post_meta( $post_id, '_wla_demo_key', $record_key );
	return absint( $post_id );
}

function wla_cleanup_legacy_duplicate_language_posts() {
	$legacy = get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team' ),
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'meta_key'       => '_wla_demo_key',
			'posts_per_page' => 200,
			'fields'         => 'ids',
		)
	);
	foreach ( $legacy as $pid ) {
		$key = (string) get_post_meta( $pid, '_wla_demo_key', true );
		if ( '-es' === substr( $key, -3 ) ) {
			wp_delete_post( absint( $pid ), true );
		}
	}
}

function wla_run_demo_import() {
	$report = array(
		'fatal'     => false,
		'summary'   => '',
		'warnings'  => array(),
		'pages'     => 0,
		'posts'     => 0,
		'assets'    => 0,
		'drafts'    => 0,
		'templates' => 0,
	);

	$file = WLA_CORE_DIR . 'demo/content.json';
	$raw  = is_readable( $file ) ? file_get_contents( $file ) : false;
	$data = $raw ? json_decode( $raw, true ) : null;
	if ( ! is_array( $data ) || empty( $data['home'] ) ) {
		$report['fatal']      = true;
		$report['warnings'][] = 'Bundled demo/content.json is missing or invalid.';
		return $report;
	}

	// Remove any older duplicate Spanish pages if re-importing on top of v1
	wla_cleanup_legacy_duplicate_language_posts();

	$asset_map = array();
	foreach ( ( $data['assets'] ?? array() ) as $asset ) {
		$fname = sanitize_file_name( $asset['file'] ?? '' );
		if ( '' !== $fname ) {
			$aid = wla_import_local_media_single( $fname, $asset['alt'] ?? '', $report );
			if ( $aid ) {
				$asset_map[ $fname ] = array(
					'id'  => $aid,
					'url' => wp_get_attachment_url( $aid ),
				);
			}
		}
	}

	$hero_media = $asset_map['hero-care.jpg'] ?? array( 'id' => 0, 'url' => '' );
	$walk_media = $asset_map['lifestyle-walk.jpg'] ?? array( 'id' => 0, 'url' => '' );
	$h          = $data['home'];

	// 1. Create single canonical Home page with all 10 exact preview Elementor widgets
	$home_steps = array();
	foreach ( ( $h['steps'] ?? array() ) as $st ) {
		$home_steps[] = array(
			'_id'         => wla_el_id(),
			'number'      => $st[0] ?? '01',
			'title'       => $st[1] ?? '',
			'description' => $st[2] ?? '',
			'meta'        => $st[3] ?? '',
			'detail'      => $st[4] ?? '',
		);
	}
	$home_faqs = array();
	foreach ( ( $h['faqs'] ?? array() ) as $fq ) {
		$home_faqs[] = array(
			'_id'      => wla_el_id(),
			'question' => $fq[0] ?? '',
			'answer'   => $fq[1] ?? '',
		);
	}
	$home_cards = array();
	foreach ( ( $h['services'] ?? array() ) as $idx => $sv ) {
		$home_cards[] = array(
			'_id'      => wla_el_id(),
			'tag'      => $sv['tag'] ?? '',
			'title'    => $sv['title'] ?? '',
			'text'     => $sv['text'] ?? '',
			'cta'      => $sv['cta'] ?? '',
			'href'     => $sv['href'] ?? '/resources',
			'featured' => 0 === $idx ? 'yes' : '',
		);
	}

	$home_el_data = array(
		wla_el_section_widget( 'wla_hero_pro', array(
			'eyebrow'        => $h['heroEyebrow'] ?? '',
			'line1'          => $h['heroLines'][0] ?? 'A clearer,',
			'emphasis_word'  => $h['heroEmphasisWord'] ?? 'calmer',
			'line2_suffix'   => trim( $h['heroAfterEmphasis'] ?? 'way' ),
			'line3'          => $h['heroLines'][2] ?? 'to begin.',
			'body'           => $h['heroBody'] ?? '',
			'primary_text'   => $h['heroPrimary'] ?? 'Get a quote',
			'primary_url'    => '/consultation-eligibility/',
			'secondary_text' => $h['heroSecondary'] ?? 'How it works',
			'secondary_url'  => '#process',
			'image'          => $hero_media,
			'float_badge'    => $h['imageBadge'] ?? '',
			'float_sub'      => $h['imageSub'] ?? '',
		) ),
		wla_el_section_widget( 'wla_philosophy_intro', array(
			'index'      => '01',
			'eyebrow'    => $h['aboutEyebrow'] ?? '',
			'title'      => $h['aboutTitle'] ?? '',
			'body'       => $h['aboutBody'] ?? '',
			'note'       => $h['aboutNote'] ?? '',
			'link_label' => $h['aboutLinkLabel'] ?? '',
			'link_url'   => '/about',
		) ),
		wla_el_section_widget( 'wla_service_cards', array(
			'index'   => '02',
			'eyebrow' => $h['servicesEyebrow'] ?? '',
			'title'   => $h['servicesTitle'] ?? '',
			'intro'   => $h['servicesIntro'] ?? '',
			'cards'   => $home_cards,
		) ),
		wla_el_section_widget( 'wla_treatments_duo', array() ),
		wla_el_section_widget( 'wla_guarantee', array() ),
		wla_el_section_widget( 'wla_glp1_signal', array(
			'image' => $asset_map['nutrition.jpg'] ?? array( 'id' => 0, 'url' => '' ),
		) ),
		wla_el_section_widget( 'wla_steps', array(
			'index'    => '03',
			'eyebrow'  => $h['processEyebrow'] ?? '',
			'title'    => $h['processTitle'] ?? '',
			'intro'    => $h['processIntro'] ?? '',
			'steps'    => $home_steps,
			'cta_text' => $h['processCta'] ?? 'Get a quote',
			'cta_url'  => '/consultation-eligibility/',
		) ),
		wla_el_section_widget( 'wla_science', array() ),
		wla_el_section_widget( 'wla_transformations', array() ),
		wla_el_section_widget( 'wla_video_stories', array() ),
		wla_el_section_widget( 'wla_approach_split', array(
			'eyebrow'     => $h['approachEyebrow'] ?? '',
			'title'       => $h['approachTitle'] ?? '',
			'body'        => $h['approachBody'] ?? '',
			'bullets_raw' => implode( "\n", $h['approachBullets'] ?? array() ),
			'button_text' => $h['approachButton'] ?? '',
			'button_url'  => '#faq',
			'image'       => $walk_media,
		) ),
		wla_el_section_widget( 'wla_pricing_cards', array(
			'eyebrow'     => $h['pricingEyebrow'] ?? '',
			'title'       => $h['pricingTitle'] ?? '',
			'body'        => $h['pricingBody'] ?? '',
			'button_text' => $h['pricingButton'] ?? '',
			'button_url'  => '/pricing-and-packages',
			'status'      => $h['pricingStatus'] ?? '',
		) ),
		wla_el_section_widget( 'wla_resources_explorer', array(
			'eyebrow' => $h['resourcesEyebrow'] ?? '',
			'title'   => $h['resourcesTitle'] ?? '',
			'lead'    => $h['resourcesLead'] ?? '',
		) ),
		wla_el_section_widget( 'wla_reviews_panel', array(
			'eyebrow' => $h['reviewsEyebrow'] ?? '',
			'title'   => $h['reviewsTitle'] ?? '',
			'body'    => $h['reviewsBody'] ?? '',
			'badge'   => $h['reviewBadge'] ?? '',
			'caption' => $h['reviewCaption'] ?? '',
		) ),
		wla_el_section_widget( 'wla_faq', array(
			'eyebrow'   => $h['faqEyebrow'] ?? '',
			'title'     => $h['faqTitle'] ?? '',
			'intro'     => $h['faqIntro'] ?? '',
			'questions' => $home_faqs,
		) ),
		// Multi-page structure: the homepage closes with a CTA band that routes to
		// the dedicated Contact page plus an "explore other pages" grid, instead of
		// embedding a duplicate inquiry form.
		wla_el_section_widget( 'wla_cta_band', array(
			'eyebrow'     => $h['ctaBandEyebrow'] ?? 'NEXT STEP',
			'title'       => $h['ctaBandTitle'] ?? '',
			'body'        => $h['ctaBandBody'] ?? '',
			'button_text' => $h['ctaBandButton'] ?? 'Get a quote',
			'button_url'  => '/consultation-eligibility/',
		) ),
		wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => 'home' ) ),
	);

	$page_ids = array();
	$home_id  = wla_upsert_single_post( 'page', 'home', 'Home', '<!-- WeightLossAffordable Home -->', 'publish', 'single-page-home' );
	if ( $home_id ) {
		update_post_meta( $home_id, '_elementor_data', wp_slash( wp_json_encode( $home_el_data ) ) );
		update_post_meta( $home_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $home_id, '_elementor_template_type', 'wp-page' );
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $home_id, '_elementor_version', ELEMENTOR_VERSION );
		}
		$page_ids['home'] = $home_id;
		$report['pages']++;
	}

	// 2. Create all 17 single canonical subpages & legal drafts (NO duplicate copy pages!)
	foreach ( ( $data['subpages'] ?? array() ) as $sp ) {
		$slug = sanitize_title( $sp['slug'] ?? '' );
		if ( '' === $slug ) {
			continue;
		}
		$sp_img_file = ! empty( $sp['image'] ) ? basename( $sp['image'] ) : '';
		$sp_media    = ( $sp_img_file && isset( $asset_map[ $sp_img_file ] ) ) ? $asset_map[ $sp_img_file ] : array( 'id' => 0, 'url' => '' );

		$sp_el = array(
			wla_el_section_widget( 'wla_subpage_hero', array(
				'eyebrow' => $sp['eyebrow'] ?? '',
				'title'   => $sp['title'] ?? '',
				'lead'    => $sp['lead'] ?? '',
				'badge'   => $sp['badge'] ?? '',
				'image'   => $sp_media,
			) ),
		);

		foreach ( ( $sp['sections'] ?? array() ) as $sidx => $sec ) {
			if ( ! empty( $sec['steps'] ) ) {
				$s_items = array();
				foreach ( $sec['steps'] as $st ) {
					$s_items[] = array(
						'_id'         => wla_el_id(),
						'number'      => $st[0] ?? '01',
						'title'       => $st[1] ?? '',
						'description' => $st[2] ?? '',
						'meta'        => $st[3] ?? '',
						'detail'      => $st[4] ?? '',
					);
				}
				$sp_el[] = wla_el_section_widget( 'wla_steps', array(
					'index'   => sprintf( '%02d', $sidx + 1 ),
					'eyebrow' => $sec['eyebrow'] ?? '',
					'title'   => $sec['heading'] ?? '',
					'intro'   => $sec['body'] ?? '',
					'steps'   => $s_items,
				) );
			} elseif ( ! empty( $sec['faqs'] ) ) {
				$f_items = array();
				foreach ( $sec['faqs'] as $fq ) {
					$f_items[] = array( '_id' => wla_el_id(), 'question' => $fq[0] ?? '', 'answer' => $fq[1] ?? '' );
				}
				$sp_el[] = wla_el_section_widget( 'wla_faq', array(
					'eyebrow'   => $sec['eyebrow'] ?? '',
					'title'     => $sec['heading'] ?? '',
					'intro'     => $sec['body'] ?? '',
					'questions' => $f_items,
				) );
			} else {
				$c_items = array();
				foreach ( ( $sec['cards'] ?? array() ) as $cd ) {
					$c_items[] = array(
						'_id'                => wla_el_id(),
						'tag'                => $cd['tag'] ?? '',
						'title'              => $cd['title'] ?? '',
						'pricePlaceholder'   => $cd['pricePlaceholder'] ?? '',
						'billingPlaceholder' => $cd['billingPlaceholder'] ?? '',
						'text'               => $cd['body'] ?? '',
						'bullets_raw'        => ! empty( $cd['bullets'] ) ? implode( "\n", $cd['bullets'] ) : '',
						'featured'           => ! empty( $cd['featured'] ) ? 'yes' : '',
					);
				}
				$sp_el[] = wla_el_section_widget( 'wla_service_cards', array(
					'index'   => sprintf( '%02d', $sidx + 1 ),
					'eyebrow' => $sec['eyebrow'] ?? '',
					'title'   => $sec['heading'] ?? '',
					'intro'   => $sec['body'] ?? '',
					'cards'   => $c_items,
					'notice'  => $sec['notice'] ?? '',
				) );
			}
		}

		// Only the Contact page receives the inquiry form. Every other page closes
		// with a CTA band routing onward, plus the explore-pages grid.
		if ( 'contact' === $slug ) {
			$sp_el[] = wla_el_section_widget( 'wla_contact_section', array( 'index' => '02' ) );
		} else {
			$sp_el[] = wla_el_section_widget( 'wla_cta_band', array(
				'eyebrow'     => $h['ctaBandEyebrow'] ?? 'NEXT STEP',
				'title'       => $sp['ctaHeading'] ?? '',
				'body'        => $sp['ctaBody'] ?? '',
				'button_text' => $sp['ctaButton'] ?? '',
				'button_url'  => $sp['ctaHref'] ?? '/contact',
			) );
		}
		$sp_el[] = wla_el_section_widget( 'wla_page_nav', array( 'current_slug' => $slug ) );
		if ( function_exists( 'wla_el_enrich_sections' ) ) {
			$sp_el = wla_el_enrich_sections( $sp_el );
		}

		$pid = wla_upsert_single_post( 'page', $slug, sanitize_text_field( $sp['title'] ?? $slug ), wp_kses_post( wpautop( $sp['lead'] ?? '' ) ), 'publish', 'single-page-' . $slug );
		if ( $pid ) {
			if ( 'resources' !== $slug ) {
				update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $sp_el ) ) );
				update_post_meta( $pid, '_elementor_edit_mode', 'builder' );
				update_post_meta( $pid, '_elementor_template_type', 'wp-page' );
				if ( defined( 'ELEMENTOR_VERSION' ) ) {
					update_post_meta( $pid, '_elementor_version', ELEMENTOR_VERSION );
				}
			}
			$page_ids[ $slug ] = $pid;
			$report['pages']++;
		}
	}

	// 3. Import 3 educational articles (single set — live translated in-place!)
	foreach ( ( $data['articles'] ?? array() ) as $art ) {
		$aslug = sanitize_title( $art['slug'] ?? '' );
		$body  = '';
		foreach ( ( $art['paragraphs'] ?? array() ) as $para ) {
			$body .= '<p>' . esc_html( $para ) . '</p>';
		}
		if ( ! empty( $art['questions'] ) ) {
			$body .= '<h3>Questions to bring to a qualified healthcare professional</h3><ul>';
			foreach ( $art['questions'] as $q ) {
				$body .= '<li>' . esc_html( $q ) . '</li>';
			}
			$body .= '</ul>';
		}
		$post_id = wla_upsert_single_post( 'post', $aslug, sanitize_text_field( $art['title'] ?? '' ), $body, 'publish', 'single-article-' . $aslug, $art['excerpt'] ?? '' );
		if ( $post_id ) {
			$cat_name = sanitize_text_field( $art['category'] ?? 'Educational Guides' );
			$term     = term_exists( $cat_name, 'category' );
			if ( ! $term ) {
				$term = wp_insert_term( $cat_name, 'category' );
			}
			if ( ! is_wp_error( $term ) ) {
				$tid = is_array( $term ) ? absint( $term['term_id'] ) : absint( $term );
				wp_set_post_categories( $post_id, array( $tid ), false );
			}
			$img_file = sanitize_file_name( $art['imageFile'] ?? '' );
			if ( isset( $asset_map[ $img_file ]['id'] ) ) {
				set_post_thumbnail( $post_id, absint( $asset_map[ $img_file ]['id'] ) );
			}
			$report['posts']++;
		}
	}

	// 4. Import 5 clearly labeled draft-only CPT placeholders
	foreach ( ( $data['draft_placeholders'] ?? array() ) as $dp ) {
		$ptype = sanitize_key( $dp['post_type'] ?? '' );
		if ( ! post_type_exists( $ptype ) ) {
			continue;
		}
		$dp_id = wla_upsert_single_post( $ptype, sanitize_title( $dp['slug'] ?? $ptype ), sanitize_text_field( $dp['title'] ?? '' ), wp_kses_post( wpautop( $dp['content'] ?? '' ) ), 'publish', 'single-draft-' . ( $dp['slug'] ?? $ptype ) );
		if ( $dp_id ) {
			$report['drafts']++;
		}
	}

	// 5. Configure front page, posts page & clean single-site navigation menus
	if ( ! empty( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', absint( $page_ids['home'] ) );
	}
	if ( ! empty( $page_ids['resources'] ) ) {
		update_option( 'page_for_posts', absint( $page_ids['resources'] ) );
	}
	update_option( 'elementor_cpt_support', array( 'page', 'post', 'wla_service', 'wla_treatment', 'wla_package', 'wla_review', 'wla_team' ) );

	$menus_to_build = array(
		'primary' => array( 'home', 'about', 'weight-loss-services', 'how-it-works', 'consultation-eligibility', 'frequently-asked-questions', 'contact' ),
		'footer'  => array( 'about', 'weight-loss-services', 'how-it-works', 'consultation-eligibility', 'weekly-payment', 'how-glp1-works', 'privacy-policy', 'terms-and-conditions', 'medical-disclaimer' ),
	);
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus_to_build as $loc => $slugs ) {
		$menu_name = 'WeightLossAffordable ' . ucfirst( $loc );
		$menu_obj  = wp_get_nav_menu_object( $menu_name );
		$menu_id   = $menu_obj ? absint( $menu_obj->term_id ) : wp_create_nav_menu( $menu_name );
		if ( ! is_wp_error( $menu_id ) && $menu_id ) {
			foreach ( wp_get_nav_menu_items( $menu_id ) ?: array() as $old_item ) {
				wp_delete_post( $old_item->ID, true );
			}
			foreach ( $slugs as $s_key ) {
				if ( ! empty( $page_ids[ $s_key ] ) ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => get_the_title( $page_ids[ $s_key ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => absint( $page_ids[ $s_key ] ),
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					) );
				}
			}
			$locations[ $loc ] = $menu_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	// 6. Save reusable Elementor section templates when Elementor is active
	if ( post_type_exists( 'elementor_library' ) ) {
		$reusable = array(
			'hero-pro'           => array( 'WLA — Straw Hero Section', 'wla_hero_pro' ),
			'journey-board'      => array( 'WLA — 4-Stage Journey Board', 'wla_steps' ),
			'resources-explorer' => array( 'WLA — Questions Worth Exploring Explorer', 'wla_resources_explorer' ),
			'faq-accordion'      => array( 'WLA — Sticky FAQ Accordion', 'wla_faq' ),
			'contact-section'    => array( 'WLA — General Inquiry Contact Section', 'wla_contact_section' ),
			'cta-band'           => array( 'WLA — Closing CTA Band', 'wla_cta_band' ),
			'page-nav-grid'      => array( 'WLA — Explore Other Pages Grid', 'wla_page_nav' ),
		);
		foreach ( $reusable as $r_key => $meta_pair ) {
			$tid = wla_upsert_single_post( 'elementor_library', 'wla-tpl-' . $r_key, $meta_pair[0], '', 'publish', 'wla-tpl-' . $r_key );
			if ( $tid ) {
				update_post_meta( $tid, '_elementor_data', wp_slash( wp_json_encode( array( wla_el_section_widget( $meta_pair[1] ) ) ) ) );
				update_post_meta( $tid, '_elementor_edit_mode', 'builder' );
				update_post_meta( $tid, '_elementor_template_type', 'section' );
				$report['templates']++;
			}
		}
	}

	if ( function_exists( 'wla_import_clinical_site' ) ) {
		$page_ids = wla_import_clinical_site( $report, $page_ids );
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	$report['summary'] = sprintf(
		'Imported %1$d published pages, %2$d articles, %3$d media items, %4$d catalog records, %5$d Elementor templates, %6$d treatments, and %7$s Get Quote quiz.',
		$report['pages'],
		$report['posts'],
		$report['assets'],
		$report['drafts'],
		$report['templates'],
		isset( $report['treatments'] ) ? (int) $report['treatments'] : 0,
		! empty( $report['quiz'] ) ? 'a published' : 'no'
	);

	return $report;
}
