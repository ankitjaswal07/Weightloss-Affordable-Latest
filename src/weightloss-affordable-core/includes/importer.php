<?php
/**
 * One-click demo importer.
 *
 * Design rules:
 *  - never deletes existing content unless the administrator explicitly asks
 *    for a reset, and even then only records created by this importer,
 *  - safe to run twice: every imported record carries a `_wla_demo_key`,
 *  - writes both `_wla_sections` and Elementor data, so pages are editable in
 *    either editor straight away,
 *  - reports exactly what happened and what still needs configuring.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the companion theme active?
 *
 * @return bool
 */
function wla_is_theme_active() {
	return function_exists( 'wla_render_hero_section' ) || 'weightloss-affordable' === get_template();
}

/**
 * Look up an imported record by its demo key.
 *
 * @param string $key Demo key.
 * @return int Post ID or 0.
 */
function wla_find_demo_post( $key ) {
	$found = get_posts(
		array(
			'post_type'        => array( 'page', 'post', 'nrx_treatment' ),
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wla_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Import a bundled demo image into the media library (once).
 *
 * @param string $filename File name inside demo/images.
 * @return int Attachment ID or 0.
 */
function wla_import_demo_image( $filename ) {
	$filename = basename( $filename );
	$cache    = (array) get_option( 'wla_demo_media', array() );

	if ( ! empty( $cache[ $filename ] ) && get_post( (int) $cache[ $filename ] ) ) {
		return (int) $cache[ $filename ];
	}

	$source = WLA_CORE_DIR . 'demo/images/' . $filename;
	if ( ! file_exists( $source ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $filename );
	if ( ! $tmp ) {
		return 0;
	}
	if ( ! copy( $source, $tmp ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, null, array( 'post_excerpt' => __( 'Bundled demo image.', 'weightloss-affordable-core' ) ) );
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	update_post_meta( $attachment_id, '_wla_demo_asset', $filename );
	$cache[ $filename ] = (int) $attachment_id;
	update_option( 'wla_demo_media', $cache, false );

	return (int) $attachment_id;
}

/**
 * Convert a section field value that stores a bundled file name into an
 * attachment ID when the image has been imported.
 *
 * @param mixed $value Image field value.
 * @return mixed
 */
function wla_map_media_value( $value ) {
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	if ( is_string( $value ) && '' !== $value && ! preg_match( '#^https?://#i', $value ) ) {
		$id = wla_import_demo_image( $value );
		if ( $id ) {
			return $id;
		}
	}
	return $value;
}

/**
 * Is this an untouched WordPress-generated stub page?
 *
 * WordPress creates a "Privacy Policy" page with suggested-text placeholders
 * during install. Replacing that stub is safe; replacing a page a human wrote
 * is not.
 *
 * @param WP_Post $page Page object.
 * @return bool
 */
function wla_is_wordpress_stub_page( $page ) {
	if ( ! $page instanceof WP_Post ) {
		return false;
	}
	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy_id !== (int) $page->ID ) {
		return false;
	}
	$content = (string) $page->post_content;
	if ( '' === trim( $content ) ) {
		return true;
	}
	return false !== strpos( $content, 'Suggested text:' ) || false !== strpos( $content, '## Who we are' );
}

/**
 * Build the section list for a demo page.
 *
 * @param string $slug      Page slug.
 * @param array  $overrides Per-section field overrides.
 * @return array
 */
function wla_build_demo_sections( $slug, $overrides = array() ) {
	$blueprints = wla_default_page_blueprints();
	$types      = isset( $blueprints[ $slug ] ) ? $blueprints[ $slug ] : array( 'subpage_hero', 'cta_band', 'page_nav' );
	$sections   = array();
	$index      = 1;

	foreach ( $types as $type ) {
		$fields = wla_section_defaults( $type );
		$fields = wp_parse_args( isset( $overrides[ $type ] ) ? $overrides[ $type ] : array(), $fields );

		if ( isset( $fields['index'] ) ) {
			$fields['index'] = sprintf( '%02d', $index );
			++$index;
		}

		// Map bundled image names to media library IDs.
		foreach ( array( 'image' ) as $media_key ) {
			if ( isset( $fields[ $media_key ] ) ) {
				$fields[ $media_key ] = wla_map_media_value( $fields[ $media_key ] );
			}
		}
		if ( ! empty( $fields['guides'] ) && is_array( $fields['guides'] ) ) {
			foreach ( $fields['guides'] as $gi => $guide ) {
				if ( ! empty( $guide['image'] ) ) {
					$fields['guides'][ $gi ]['image'] = wla_map_media_value( $guide['image'] );
				}
			}
		}

		$sections[] = array(
			'type'   => $type,
			'fields' => $fields,
		);
	}

	return wla_normalize_sections( $sections );
}

/**
 * Convert section settings into the value shape Elementor controls expect.
 *
 * @param string $type     Section type.
 * @param array  $settings Settings.
 * @return array
 */
function wla_elementor_settings_for_section( $type, $settings ) {
	$schema = wla_section_schema();
	if ( ! isset( $schema[ $type ] ) ) {
		return $settings;
	}
	foreach ( $schema[ $type ]['fields'] as $field ) {
		$key = $field['key'];
		if ( 'media' === $field['type'] && ! empty( $settings[ $key ] ) ) {
			$settings[ $key ] = array(
				'id'  => is_numeric( $settings[ $key ] ) ? (int) $settings[ $key ] : '',
				'url' => is_numeric( $settings[ $key ] ) ? (string) wp_get_attachment_image_url( (int) $settings[ $key ], 'full' ) : $settings[ $key ],
			);
		}
		if ( 'repeater' === $field['type'] && ! empty( $settings[ $key ] ) && is_array( $settings[ $key ] ) ) {
			foreach ( $settings[ $key ] as $ri => $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$settings[ $key ][ $ri ]['_id'] = substr( md5( $type . $ri . wp_json_encode( $row ) ), 0, 7 );
			}
		}
	}
	return $settings;
}

/**
 * Build Elementor document data from sections.
 *
 * @param array $sections Sections.
 * @return array
 */
function wla_build_elementor_data( $sections ) {
	$data = array();
	foreach ( $sections as $section ) {
		$widget_id = substr( md5( uniqid( 'wla', true ) ), 0, 7 );
		$data[]    = array(
			'id'       => substr( md5( uniqid( 'sec', true ) ), 0, 7 ),
			'elType'   => 'section',
			'settings' => array(
				'layout'             => 'full_width',
				'gap'                => 'no',
				'structure'          => '10',
				'content_position'   => 'middle',
				'padding'            => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
			),
			'elements' => array(
				array(
					'id'       => substr( md5( uniqid( 'col', true ) ), 0, 7 ),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100, '_inline_size' => null ),
					'elements' => array(
						array(
							'id'         => $widget_id,
							'elType'     => 'widget',
							'widgetType' => 'wla_section_' . $section['type'],
							'settings'   => wla_elementor_settings_for_section( $section['type'], $section['fields'] ),
							'elements'   => array(),
						),
					),
					'isInner'  => false,
				),
			),
			'isInner'  => false,
		);
	}
	return $data;
}

/**
 * Write sections in both formats for a post.
 *
 * @param int   $post_id  Post ID.
 * @param array $sections Sections.
 * @return void
 */
function wla_store_sections( $post_id, $sections ) {
	wla_save_page_sections( $post_id, $sections );

	$elementor_data = wla_build_elementor_data( $sections );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elementor_data ) ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_wp_page_template', 'default' );
}

/**
 * Import one page.
 *
 * @param string $slug Page slug.
 * @param array  $def  Definition.
 * @param array  $report Report (by reference).
 * @return int Page ID.
 */
function wla_import_demo_page( $slug, $def, &$report ) {
	$key      = 'page:' . $slug;
	$existing = wla_find_demo_post( $key );

	// Never take over a page the administrator created by hand.
	if ( ! $existing ) {
		$clash = get_page_by_path( $slug );
		if ( $clash && ! get_post_meta( $clash->ID, '_wla_demo_key', true ) ) {
			// WordPress creates a stub "Privacy Policy" page on install. Adopt it
			// rather than leaving the site with an empty legal page.
			if ( wla_is_wordpress_stub_page( $clash ) ) {
				$existing = (int) $clash->ID;
				update_post_meta( $existing, '_wla_demo_key', $key );
				$report['warnings'][] = sprintf(
					/* translators: %s: page title */
					__( 'Adopted the default WordPress “%s” page and replaced its stub content with the demo content.', 'weightloss-affordable-core' ),
					$clash->post_title
				);
			} else {
				$report['warnings'][] = sprintf(
					/* translators: %s: page slug */
					__( 'A page with the slug “%s” already exists and was left untouched. Delete or rename it, then run the import again to create the demo page.', 'weightloss-affordable-core' ),
					$slug
				);
				return 0;
			}
		}
	}

	$postarr = array(
		'post_type'    => 'page',
		'post_title'   => $def['title'],
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_excerpt' => $def['excerpt'] ?? '',
		'post_content' => '',
		'menu_order'   => isset( $def['menu_order'] ) ? (int) $def['menu_order'] : 0,
	);
	if ( $existing ) {
		$postarr['ID'] = $existing;
	}

	$post_id = wp_insert_post( $postarr, true );
	if ( is_wp_error( $post_id ) ) {
		$report['warnings'][] = sprintf( /* translators: 1: page, 2: error */ __( 'Could not import “%1$s”: %2$s', 'weightloss-affordable-core' ), $def['title'], $post_id->get_error_message() );
		return 0;
	}

	update_post_meta( $post_id, '_wla_demo_key', $key );

	$sections = wla_build_demo_sections( $slug, isset( $def['overrides'] ) ? $def['overrides'] : array() );
	if ( ! empty( $def['is_posts_page'] ) ) {
		// The posts page uses the theme's index template, not sections.
		delete_post_meta( $post_id, '_elementor_edit_mode' );
		delete_post_meta( $post_id, '_elementor_data' );
		update_post_meta( $post_id, '_wla_sections', wp_slash( wp_json_encode( array() ) ) );
	} else {
		wla_store_sections( $post_id, $sections );
	}

	// Featured image for inner pages.
	if ( ! empty( $def['overrides']['subpage_hero']['image'] ) ) {
		$image_id = wla_map_media_value( $def['overrides']['subpage_hero']['image'] );
		if ( is_numeric( $image_id ) ) {
			set_post_thumbnail( $post_id, (int) $image_id );
		}
	}

	// SEO overrides are explicit only; otherwise WordPress builds the title from
	// the page title, which is what search results should show.
	if ( ! empty( $def['seo']['title'] ) ) {
		update_post_meta( $post_id, '_wla_seo_title', $def['seo']['title'] );
	} else {
		delete_post_meta( $post_id, '_wla_seo_title' );
	}
	if ( ! empty( $def['seo']['description'] ) ) {
		update_post_meta( $post_id, '_wla_seo_description', $def['seo']['description'] );
	} elseif ( ! empty( $def['excerpt'] ) ) {
		update_post_meta( $post_id, '_wla_seo_description', $def['excerpt'] );
	}

	$report['pages'][] = array(
		'id'     => $post_id,
		'title'  => $def['title'],
		'action' => $existing ? 'updated' : 'created',
	);

	return $post_id;
}

/**
 * Import treatments.
 *
 * @param array $report Report (by reference).
 * @return void
 */
function wla_import_demo_treatments( &$report ) {
	foreach ( wla_demo_treatments() as $slug => $def ) {
		$key      = 'treatment:' . $slug;
		$existing = wla_find_demo_post( $key );

		if ( ! $existing ) {
			$clash = get_page_by_path( $slug, OBJECT, 'nrx_treatment' );
			if ( $clash ) {
				$report['warnings'][] = sprintf(
					/* translators: %s: treatment slug */
					__( 'A treatment with the slug “%s” already exists and was left untouched.', 'weightloss-affordable-core' ),
					$slug
				);
				continue;
			}
		}

		$postarr = array(
			'post_type'    => 'nrx_treatment',
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_excerpt' => $def['excerpt'] ?? '',
			'post_content' => isset( $def['meta']['short_description'] ) ? $def['meta']['short_description'] : '',
			'menu_order'   => isset( $def['menu_order'] ) ? (int) $def['menu_order'] : 0,
		);
		if ( $existing ) {
			$postarr['ID'] = $existing;
		}

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			$report['warnings'][] = sprintf( /* translators: 1: treatment, 2: error */ __( 'Could not import “%1$s”: %2$s', 'weightloss-affordable-core' ), $def['title'], $post_id->get_error_message() );
			continue;
		}

		update_post_meta( $post_id, '_wla_demo_key', $key );

		foreach ( (array) $def['meta'] as $meta_key => $meta_value ) {
			update_post_meta( $post_id, '_wla_' . $meta_key, $meta_value );
		}
		update_post_meta( $post_id, '_wla_display_order', isset( $def['menu_order'] ) ? (int) $def['menu_order'] : 0 );
		update_post_meta( $post_id, '_wla_seo_description', $def['meta']['seo_description'] ?? $def['excerpt'] );

		foreach ( array( 'benefits', 'variations', 'faqs' ) as $group ) {
			if ( ! empty( $def[ $group ] ) ) {
				update_post_meta( $post_id, '_wla_' . $group, $def[ $group ] );
			}
		}

		$term = wp_set_object_terms( $post_id, $def['title'], 'nrx_treatment_category' );
		if ( is_wp_error( $term ) ) {
			$report['warnings'][] = __( 'A treatment category could not be assigned.', 'weightloss-affordable-core' );
		}

		$image_id = ! empty( $def['image'] ) ? wla_map_media_value( $def['image'] ) : 0;
		if ( is_numeric( $image_id ) && $image_id ) {
			set_post_thumbnail( $post_id, (int) $image_id );
			update_post_meta( (int) $image_id, '_wp_attachment_image_alt', $def['hero']['image_alt'] ?? '' );
		}

		// Sections: hero, dynamic detail, FAQs, CTA, page nav.
		$sections = array(
			array(
				'type'   => 'subpage_hero',
				'fields' => wp_parse_args(
					array_merge(
						isset( $def['hero'] ) ? $def['hero'] : array(),
						array( 'image' => $image_id ? (int) $image_id : '' )
					),
					wla_section_defaults( 'subpage_hero' )
				),
			),
			array(
				'type'   => 'treatment_detail',
				'fields' => wla_section_defaults( 'treatment_detail' ),
			),
			array(
				'type'   => 'faq',
				'fields' => wp_parse_args(
					array(
						'index'    => '02',
						'eyebrow'  => __( 'TREATMENT QUESTIONS', 'weightloss-affordable-core' ),
						'title'    => __( 'Questions about this treatment', 'weightloss-affordable-core' ),
						'intro'    => __( 'General information only — your clinician will answer questions about your own situation.', 'weightloss-affordable-core' ),
						'show_all' => '0',
						'faqs'     => $def['faqs'] ?? array(),
					),
					wla_section_defaults( 'faq' )
				),
			),
			array(
				'type'   => 'cta_band',
				'fields' => wp_parse_args(
					array(
						'title'       => __( 'Ready to find out whether treatment is appropriate for you?', 'weightloss-affordable-core' ),
						'body'        => __( 'Request a consultation and a licensed clinician will review your history. Not everyone is eligible, and no outcome is guaranteed.', 'weightloss-affordable-core' ),
						'button_text' => __( 'Request a consultation', 'weightloss-affordable-core' ),
						'button_url'  => '/consultation-eligibility',
					),
					wla_section_defaults( 'cta_band' )
				),
			),
			array(
				'type'   => 'page_nav',
				'fields' => wla_section_defaults( 'page_nav' ),
			),
		);

		wla_store_sections( $post_id, wla_normalize_sections( $sections ) );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );

		$report['treatments'][] = array(
			'id'     => $post_id,
			'title'  => $def['title'],
			'action' => $existing ? 'updated' : 'created',
		);
	}
}

/**
 * Import blog posts.
 *
 * @param array $report Report (by reference).
 * @return void
 */
function wla_import_demo_posts( &$report ) {
	foreach ( wla_demo_posts() as $def ) {
		$key      = 'post:' . $def['slug'];
		$existing = wla_find_demo_post( $key );

		$postarr = array(
			'post_type'    => 'post',
			'post_title'   => $def['title'],
			'post_name'    => $def['slug'],
			'post_status'  => 'publish',
			'post_excerpt' => $def['excerpt'],
			'post_content' => $def['content'],
		);
		if ( $existing ) {
			$postarr['ID'] = $existing;
		}

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			$report['warnings'][] = sprintf( /* translators: 1: post, 2: error */ __( 'Could not import “%1$s”: %2$s', 'weightloss-affordable-core' ), $def['title'], $post_id->get_error_message() );
			continue;
		}

		update_post_meta( $post_id, '_wla_demo_key', $key );
		wp_set_object_terms( $post_id, $def['category'], 'category' );

		$report['posts'][] = array(
			'id'    => $post_id,
			'title' => $def['title'],
		);
	}
}

/**
 * Import navigation menus.
 *
 * @param array $page_ids Page IDs keyed by slug.
 * @param array $report   Report.
 * @return void
 */
function wla_import_demo_menus( $page_ids, &$report ) {
	$menus = array(
		'primary'        => array(
			'name'  => __( 'Primary menu', 'weightloss-affordable-core' ),
			'items' => array( 'weight-loss-treatments', 'how-it-works', 'pricing-and-plans', 'resources', 'frequently-asked-questions', 'about' ),
		),
		'footer_explore' => array(
			'name'  => __( 'Footer explore', 'weightloss-affordable-core' ),
			'items' => array( 'weight-loss-treatments', 'compare-treatments', 'consultation-eligibility', 'articles', 'contact' ),
		),
		'footer_legal'   => array(
			'name'  => __( 'Footer legal', 'weightloss-affordable-core' ),
			'items' => array( 'privacy-policy', 'terms-and-conditions', 'medical-disclaimer', 'treatment-safety-information', 'refund-cancellation-policy', 'accessibility-statement' ),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $menus as $location => $menu_def ) {
		$menu = wp_get_nav_menu_object( $menu_def['name'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $menu_def['name'] );
			if ( is_wp_error( $menu_id ) ) {
				$report['warnings'][] = __( 'A navigation menu could not be created.', 'weightloss-affordable-core' );
				continue;
			}
		} else {
			$menu_id = (int) $menu->term_id;
		}

		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $existing_items ) ) {
			$position = 1;
			foreach ( $menu_def['items'] as $slug ) {
				if ( empty( $page_ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $page_ids[ $slug ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => (int) $page_ids[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $position,
					)
				);
				++$position;
			}
		}

		$locations[ $location ] = $menu_id;
		$report['menus'][]      = $menu_def['name'];
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Configure site options.
 *
 * @param array $page_ids Page IDs.
 * @param array $report   Report.
 * @return void
 */
function wla_import_demo_options( $page_ids, &$report ) {
	if ( ! empty( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $page_ids['home'] );
	}
	if ( ! empty( $page_ids['articles'] ) ) {
		update_option( 'page_for_posts', (int) $page_ids['articles'] );
	}
	if ( ! empty( $page_ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', (int) $page_ids['privacy-policy'] );
	}

	$sample = get_page_by_path( 'sample-page' );
	if ( $sample && 'publish' === $sample->post_status ) {
		$report['warnings'][] = __( 'The default WordPress “Sample Page” is still published. Delete it in Pages when you are ready.', 'weightloss-affordable-core' );
	}

	// The untouched "Hello world!" post would show up on the Articles page as
	// placeholder content, so it is moved to drafts (never deleted).
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status && ! get_post_meta( $hello->ID, '_wla_demo_key', true ) && false !== strpos( (string) $hello->post_content, 'Welcome to WordPress' ) ) {
		wp_update_post(
			array(
				'ID'          => $hello->ID,
				'post_status' => 'draft',
			)
		);
		$report['warnings'][] = __( 'The default WordPress “Hello world!” post was moved to drafts so it does not appear on the Articles page. It was not deleted.', 'weightloss-affordable-core' );
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$report['warnings'][] = __( 'Permalinks were switched to “Post name”. If you use a caching plugin, purge it.', 'weightloss-affordable-core' );
	}

	$settings = get_option( 'wla_settings' );
	if ( ! is_array( $settings ) || empty( $settings ) ) {
		$defaults = wla_default_settings();
		$defaults['business_name'] = get_bloginfo( 'name' );
		$defaults['contact_email'] = get_option( 'admin_email' );
		$defaults['notification_emails'] = get_option( 'admin_email' );
		update_option( 'wla_settings', $defaults );
	}

	update_option( 'blogdescription', __( 'Clinician-led weight care with clear pricing and honest information.', 'weightloss-affordable-core' ) );
	update_option( 'wla_demo_imported_at', current_time( 'mysql' ), false );

	flush_rewrite_rules( false );
}

/**
 * Run the full import.
 *
 * @param array $args Options: reset (bool), include_posts (bool).
 * @return array Report.
 */
function wla_run_demo_import( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'reset'         => false,
			'include_posts' => true,
		)
	);

	$report = array(
		'fatal'      => false,
		'summary'    => '',
		'warnings'   => array(),
		'pages'      => array(),
		'treatments' => array(),
		'posts'      => array(),
		'menus'      => array(),
	);

	if ( ! wla_is_theme_active() ) {
		$report['fatal']     = true;
		$report['warnings'][] = __( 'The WeightLossAffordable theme is not active. Activate it before importing so the section renderers exist.', 'weightloss-affordable-core' );
		return $report;
	}

	if ( $args['reset'] ) {
		wla_delete_demo_content( $report );
	}

	$page_ids = array();

	foreach ( wla_demo_pages() as $slug => $def ) {
		$page_id = wla_import_demo_page( $slug, $def, $report );
		if ( $page_id ) {
			$page_ids[ $slug ] = $page_id;
		}
	}

	wla_import_demo_treatments( $report );

	if ( $args['include_posts'] ) {
		wla_import_demo_posts( $report );
	}

	wla_import_demo_menus( $page_ids, $report );
	wla_import_demo_options( $page_ids, $report );

	if ( ! did_action( 'elementor/loaded' ) ) {
		$report['warnings'][] = __( 'Elementor is not active. Layouts were stored and will open in Elementor once the free plugin is installed and activated.', 'weightloss-affordable-core' );
	} else {
		delete_option( 'wla_elementor_kit_seeded' );
		if ( function_exists( 'wla_seed_elementor_kit' ) ) {
			wla_seed_elementor_kit();
		}
	}

	$report['summary'] = sprintf(
		/* translators: 1: pages, 2: treatments, 3: posts */
		__( '%1$d pages, %2$d treatments and %3$d articles are in place. Next: confirm your business details under WeightLossAffordable → Settings, then publish.', 'weightloss-affordable-core' ),
		count( $report['pages'] ),
		count( $report['treatments'] ),
		count( $report['posts'] )
	);

	return $report;
}

/**
 * Delete content created by this importer only.
 *
 * @param array $report Report (by reference).
 * @return void
 */
function wla_delete_demo_content( &$report ) {
	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'nrx_treatment' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_wla_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_compare'   => 'EXISTS',
		)
	);

	$count = 0;
	foreach ( $posts as $id ) {
		if ( wp_delete_post( $id, true ) ) {
			++$count;
		}
	}

	if ( $count ) {
		$report['warnings'][] = sprintf(
			/* translators: %d: number of records */
			__( 'Reset: removed %d previously imported records. Content created by you was not touched.', 'weightloss-affordable-core' ),
			$count
		);
	}
}

/**
 * Handle the import form submission.
 *
 * @return void
 */
function wla_handle_demo_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to run the importer.', 'weightloss-affordable-core' ), 403 );
	}
	check_admin_referer( 'wla_import_demo', 'wla_import_nonce' );

	$reset = isset( $_POST['wla_reset'] ) && '1' === $_POST['wla_reset'];

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	$report = wla_run_demo_import( array( 'reset' => $reset ) );

	set_transient( 'wla_demo_report_' . get_current_user_id(), $report, 5 * MINUTE_IN_SECONDS );

	wp_safe_redirect( admin_url( 'admin.php?page=wla-dashboard&imported=1' ) );
	exit;
}
add_action( 'admin_post_wla_import_demo', 'wla_handle_demo_import' );
