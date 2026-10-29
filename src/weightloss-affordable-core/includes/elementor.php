<?php
/**
 * Elementor integration.
 *
 * One widget is generated per section type from the shared schema, so:
 *  - every section is editable visually with clear, labelled controls,
 *  - adding a new field to the schema automatically adds a control,
 *  - Elementor saves are written back into `_wla_sections` (and vice versa),
 *    so the backend editor and Elementor never disagree.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the widget category.
 *
 * @param \Elementor\Elements_Manager $elements_manager Manager.
 * @return void
 */
function wla_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'wla-sections',
		array(
			'title' => __( 'WeightLossAffordable sections', 'weightloss-affordable-core' ),
			'icon'  => 'eicon-heart',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'wla_register_elementor_category' );

/**
 * Register widgets.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
 * @return void
 */
function wla_register_elementor_widgets( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}
	require_once WLA_CORE_DIR . 'includes/elementor-widget.php';

	foreach ( wla_section_schema() as $type => $definition ) {
		$class = wla_elementor_widget_class( $type );
		if ( class_exists( $class ) ) {
			$widgets_manager->register( new $class() );
		}
	}
}
add_action( 'elementor/widgets/register', 'wla_register_elementor_widgets' );

/**
 * Build the widget class name for a section type.
 *
 * @param string $type Section type.
 * @return string
 */
function wla_elementor_widget_class( $type ) {
	$parts = explode( '_', $type );
	$parts = array_map( 'ucfirst', $parts );
	return 'WLA_Section_' . implode( '_', $parts ) . '_Widget';
}

/**
 * Reverse of wla_elementor_widget_class().
 *
 * @param string $widget_name Elementor widget name (wla_section_hero).
 * @return string Section type or ''.
 */
function wla_section_type_from_widget( $widget_name ) {
	$widget_name = (string) $widget_name;
	if ( 0 !== strpos( $widget_name, 'wla_section_' ) ) {
		return '';
	}
	return substr( $widget_name, strlen( 'wla_section_' ) );
}

/**
 * Elementor control type for a schema field.
 *
 * @param array $field Schema field.
 * @return string
 */
function wla_elementor_control_type( $field ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return '\Elementor\Controls_Manager::TEXTAREA';
		case 'wysiwyg':
			return '\Elementor\Controls_Manager::WYSIWYG';
		case 'select':
			return '\Elementor\Controls_Manager::SELECT';
		case 'media':
			return '\Elementor\Controls_Manager::MEDIA';
		case 'number':
			return '\Elementor\Controls_Manager::NUMBER';
		case 'repeater':
			return '\Elementor\Controls_Manager::REPEATER';
		default:
			return '\Elementor\Controls_Manager::TEXT';
	}
}

/**
 * Elementor controls registered for one section type (shared logic).
 *
 * @param \Elementor\Widget_Base $widget Widget.
 * @param string                 $type   Section type.
 * @return void
 */
function wla_register_section_controls( $widget, $type ) {
	$schema = wla_section_schema();
	if ( ! isset( $schema[ $type ] ) ) {
		return;
	}

	$widget->start_controls_section(
		'wla_content',
		array(
			'label' => __( 'Content', 'weightloss-affordable-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		)
	);

	foreach ( $schema[ $type ]['fields'] as $field ) {
		$control = array(
			'label'   => $field['label'],
			'type'    => wla_elementor_control_type( $field ),
			'default' => $field['default'] ?? '',
		);
		if ( ! empty( $field['help'] ) ) {
			$control['description'] = $field['help'];
		}

		if ( 'repeater' === $field['type'] ) {
			$repeater = new \Elementor\Repeater();
			foreach ( $field['fields'] as $sub_key => $sub ) {
				$repeater->add_control(
					$sub_key,
					array(
						'label'   => $sub['label'],
						'type'    => wla_elementor_control_type( $sub ),
						'default' => '',
					)
				);
			}
			$control['fields']      = $repeater->get_controls();
			$control['title_field'] = '{{{ ' . array_key_first( $field['fields'] ) . ' }}}';
			$control['default']     = $field['default'] ?? array();
		}

		if ( 'select' === $field['type'] ) {
			$control['options'] = $field['options'] ?? array();
		}

		if ( 'media' === $field['type'] ) {
			$control['default'] = array( 'url' => '', 'id' => '' );
		}

		$widget->add_control( $field['key'], $control );
	}

	$widget->end_controls_section();

	$widget->start_controls_section(
		'wla_style',
		array(
			'label' => __( 'Spacing', 'weightloss-affordable-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		)
	);
	$widget->add_responsive_control(
		'wla_margin_top',
		array(
			'label'      => __( 'Top spacing', 'weightloss-affordable-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
			'selectors'  => array( '{{WRAPPER}} .wla-section-widget' => 'margin-top: {{SIZE}}{{UNIT}};' ),
		)
	);
	$widget->add_responsive_control(
		'wla_margin_bottom',
		array(
			'label'      => __( 'Bottom spacing', 'weightloss-affordable-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
			'selectors'  => array( '{{WRAPPER}} .wla-section-widget' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
		)
	);
	$widget->add_control(
		'wla_hide_mobile',
		array(
			'label'        => __( 'Hide on mobile', 'weightloss-affordable-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'selectors'    => array( '{{WRAPPER}} .wla-section-widget' => 'display:none;' ),
			'prefix_class' => '',
		)
	);
	$widget->end_controls_section();
}

/**
 * Read the settings of a widget interaction from Elementor data.
 *
 * @param int $post_id Post ID.
 * @return array List of [type, settings] in document order.
 */
function wla_extract_sections_from_elementor( $post_id ) {
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( is_string( $raw ) ) {
		$raw = json_decode( $raw, true );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$found = array();

	$walk = function ( $elements ) use ( &$walk, &$found ) {
		foreach ( (array) $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			$type = isset( $element['widgetType'] ) ? wla_section_type_from_widget( $element['widgetType'] ) : '';
			if ( $type && wla_section_exists( $type ) ) {
				$found[] = array(
					'type'     => $type,
					'element_id' => $element['id'] ?? '',
					'settings' => isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array(),
				);
			}
			if ( ! empty( $element['elements'] ) ) {
				$walk( $element['elements'] );
			}
		}
	};

	$walk( $raw );
	return $found;
}

/**
 * Copy Elementor widget settings into `_wla_sections`.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_sync_elementor_to_sections( $post_id ) {
	$extracted = wla_extract_sections_from_elementor( $post_id );
	if ( empty( $extracted ) ) {
		return;
	}
	$sections = array();
	foreach ( $extracted as $item ) {
		$sections[] = array(
			'type'   => $item['type'],
			'fields' => wla_normalize_section( $item['type'], $item['settings'] ),
		);
	}
	wla_save_page_sections( $post_id, $sections );
}

/**
 * Write `_wla_sections` back into the Elementor document.
 *
 * The section list is treated as the source of truth for the widgets this
 * plugin owns: matching widgets are updated, widgets whose section was removed
 * are deleted, and new sections are appended. Any other Elementor elements
 * (native widgets, containers) are preserved untouched.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_sync_sections_to_elementor( $post_id ) {
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( is_string( $raw ) ) {
		$raw = json_decode( $raw, true );
	}
	if ( ! is_array( $raw ) || empty( $raw ) ) {
		return;
	}

	$sections = wla_get_page_sections( $post_id, false );
	if ( empty( $sections ) ) {
		return;
	}

	// Index sections by type (first occurrence wins; types are unique per page).
	$by_type = array();
	foreach ( $sections as $section ) {
		if ( ! isset( $by_type[ $section['type'] ] ) ) {
			$by_type[ $section['type'] ] = $section['fields'];
		}
	}
	$used = array();

	/**
	 * Recurse: update matching widgets, drop stale ones, drop empty sections.
	 *
	 * @param array $elements Elements.
	 * @return array
	 */
	$walk = function ( $elements ) use ( &$walk, &$by_type, &$used ) {
		$kept = array();
		foreach ( (array) $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( ! empty( $element['widgetType'] ) ) {
				$type = wla_section_type_from_widget( $element['widgetType'] );
				if ( $type ) {
					if ( ! isset( $by_type[ $type ] ) || isset( $used[ $type ] ) ) {
						continue; // Section deleted or duplicated: drop the widget.
					}
					$element['settings'] = array_merge(
						isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array(),
						$by_type[ $type ]
					);
					$used[ $type ] = true;
				}
				$kept[] = $element;
				continue;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element['elements'] = $walk( $element['elements'] );
				// Drop a section/column that only existed to hold removed widgets.
				if ( empty( $element['elements'] ) && in_array( $element['elType'] ?? '', array( 'section', 'column' ), true ) ) {
					continue;
				}
			}
			$kept[] = $element;
		}
		return $kept;
	};

	$raw = $walk( $raw );

	// Append sections that were added in the meta box.
	foreach ( $sections as $section ) {
		if ( isset( $used[ $section['type'] ] ) ) {
			continue;
		}
		$used[ $section['type'] ] = true;
		$raw[] = array(
			'id'       => substr( md5( uniqid( 'sec', true ) ), 0, 7 ),
			'elType'   => 'section',
			'settings' => array( 'layout' => 'full_width', 'gap' => 'no' ),
			'elements' => array(
				array(
					'id'       => substr( md5( uniqid( 'col', true ) ), 0, 7 ),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100 ),
					'elements' => array(
						array(
							'id'         => substr( md5( uniqid( 'wid', true ) ), 0, 7 ),
							'elType'     => 'widget',
							'widgetType' => 'wla_section_' . $section['type'],
							'settings'   => wla_elementor_settings_for_section( $section['type'], $section['fields'] ),
							'elements'   => array(),
						),
					),
				),
			),
		);
	}

	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $raw ) ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/**
 * After an Elementor save, mirror widget settings into `_wla_sections`.
 *
 * @param \Elementor\Core\Base\Document $document Document.
 * @return void
 */
function wla_after_elementor_save( $document ) {
	if ( ! is_object( $document ) || ! method_exists( $document, 'get_main_id' ) ) {
		return;
	}
	$post_id = $document->get_main_id();
	if ( ! $post_id ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	wla_sync_elementor_to_sections( $post_id );
}
add_action( 'elementor/document/after_save', 'wla_after_elementor_save' );

/**
 * Register Elementor locations for the theme's header/footer, when Pro is active.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Manager.
 * @return void
 */
function wla_register_elementor_locations( $manager ) {
	$manager->register_core_location( 'header' );
	$manager->register_core_location( 'footer' );
}
add_action( 'elementor/theme/register_locations', 'wla_register_elementor_locations' );

/**
 * Seed Elementor global colours/typography from the theme palette once.
 *
 * @return void
 */
function wla_seed_elementor_kit() {
	if ( get_option( 'wla_elementor_kit_seeded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( ! $kit_id || 'elementor_library' !== get_post_type( $kit_id ) ) {
		return;
	}

	$colors = array(
		array( '_id' => 'wlaforest', 'title' => 'Forest 900', 'color' => '#102F2A' ),
		array( '_id' => 'wlasage', 'title' => 'Sage 600', 'color' => '#647F67' ),
		array( '_id' => 'wlasand', 'title' => 'Sand', 'color' => '#EFE7D8' ),
		array( '_id' => 'wlagold', 'title' => 'Gold', 'color' => '#D19A67' ),
		array( '_id' => 'wlaivory', 'title' => 'Ivory', 'color' => '#F8F5EE' ),
		array( '_id' => 'wlaink', 'title' => 'Ink', 'color' => '#1C2B27' ),
	);
	$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
	$settings = is_array( $settings ) ? $settings : array();

	if ( empty( $settings['system_colors'] ) ) {
		$settings['system_colors'] = array();
	}
	foreach ( $colors as $index => $color ) {
		$settings['system_colors'][ $index ] = array(
			'_id'   => 'wla_c' . $index,
			'title' => $color['title'],
			'color' => $color['color'],
		);
	}

	$settings['system_typography'] = array(
		array(
			'_id'         => 'wla_t1',
			'title'       => 'Headings',
			'typography_typography'     => 'custom',
			'typography_font_family'    => 'Fraunces',
			'typography_font_weight'    => '500',
		),
		array(
			'_id'         => 'wla_t2',
			'title'       => 'Body',
			'typography_typography'     => 'custom',
			'typography_font_family'    => 'Manrope',
			'typography_font_weight'    => '400',
		),
	);

	update_post_meta( $kit_id, '_elementor_page_settings', $settings );
	update_option( 'wla_elementor_kit_seeded', 1, false );
}
add_action( 'elementor/init', 'wla_seed_elementor_kit', 20 );
