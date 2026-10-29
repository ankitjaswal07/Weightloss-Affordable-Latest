<?php
/**
 * Elementor section widgets.
 *
 * A single base class renders every section through the theme helper, and a
 * thin subclass exists per section type so Elementor can address each one
 * (`wla_section_hero`, `wla_section_product`, …). Controls are generated from
 * includes/schema.php, so the editor and the backend meta box always match.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base widget.
 */
abstract class WLA_Section_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Section type derived from the class name.
	 *
	 * @return string
	 */
	public function get_section_type() {
		$class = static::class;
		$class = str_replace( array( 'WLA_Section_', '_Widget' ), '', $class );
		return strtolower( $class );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wla_section_' . $this->get_section_type();
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		$schema = wla_section_schema();
		$type   = $this->get_section_type();
		return isset( $schema[ $type ] ) ? $schema[ $type ]['label'] : $type;
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		$schema = wla_section_schema();
		$type   = $this->get_section_type();
		return isset( $schema[ $type ]['icon'] ) ? $schema[ $type ]['icon'] : 'eicon-block';
	}

	/**
	 * Categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'wla-sections' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'weightloss', 'weight', 'section', $this->get_section_type(), 'wla' );
	}

	/**
	 * Styles the widget needs.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'wla-theme' );
	}

	/**
	 * Scripts the widget needs.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'wla-theme' );
	}

	/**
	 * Controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		wla_register_section_controls( $this, $this->get_section_type() );
	}

	/**
	 * Convert Elementor media values to the ID/string shape used by renderers.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function normalize_settings( $settings ) {
		$schema = wla_section_schema();
		$type   = $this->get_section_type();
		if ( isset( $schema[ $type ] ) ) {
			foreach ( $schema[ $type ]['fields'] as $field ) {
				$key = $field['key'];
				if ( 'media' === $field['type'] && ! empty( $settings[ $key ] ) && is_array( $settings[ $key ] ) ) {
					$settings[ $key ] = ! empty( $settings[ $key ]['id'] ) ? (int) $settings[ $key ]['id'] : ( $settings[ $key ]['url'] ?? '' );
				}
				if ( 'repeater' === $field['type'] && ! empty( $settings[ $key ] ) && is_array( $settings[ $key ] ) ) {
					$rows = array();
					foreach ( $settings[ $key ] as $row ) {
						if ( ! is_array( $row ) ) {
							continue;
						}
						unset( $row['_id'] );
						foreach ( $field['fields'] as $sub_key => $sub ) {
							if ( 'media' === $sub['type'] && ! empty( $row[ $sub_key ] ) && is_array( $row[ $sub_key ] ) ) {
								$row[ $sub_key ] = ! empty( $row[ $sub_key ]['id'] ) ? (int) $row[ $sub_key ]['id'] : ( $row[ $sub_key ]['url'] ?? '' );
							}
						}
						$rows[] = $row;
					}
					$settings[ $key ] = $rows;
				}
			}
		}
		return $settings;
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$type = $this->get_section_type();
		$map  = wla_section_render_map();
		if ( ! isset( $map[ $type ] ) || ! function_exists( $map[ $type ] ) ) {
			echo '<p class="wla-widget-error">' . esc_html__( 'The active theme does not provide this section. Activate the WeightLossAffordable theme.', 'weightloss-affordable-core' ) . '</p>';
			return;
		}

		$settings = $this->normalize_settings( $this->get_settings_for_display() );
		$settings = wp_parse_args( $settings, wla_section_defaults( $type ) );
		$args     = wla_prepare_section_args(
			$type,
			$settings,
			array(
				'post_id'      => get_the_ID(),
				'current_slug' => get_post_field( 'post_name', get_the_ID() ),
			)
		);

		// Widget-level display options.
		$wrapper_classes = 'wla-section-widget';
		if ( ! empty( $settings['wla_hide_mobile'] ) && 'yes' === $settings['wla_hide_mobile'] ) {
			$wrapper_classes .= ' wla-hide-mobile';
		}

		echo '<div class="' . esc_attr( $wrapper_classes ) . '">';
		echo call_user_func( $map[ $type ], $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme renderers escape output.
		echo '</div>';
	}
}

/* -------------------------------------------------------------------------
 * One subclass per section type.
 * ---------------------------------------------------------------------- */

/** Hero section widget. */
class WLA_Section_Hero_Widget extends WLA_Section_Widget_Base {}
/** Intro section widget. */
class WLA_Section_Intro_Widget extends WLA_Section_Widget_Base {}
/** Product showcase widget. */
class WLA_Section_Product_Widget extends WLA_Section_Widget_Base {}
/** Card grid widget. */
class WLA_Section_Services_Widget extends WLA_Section_Widget_Base {}
/** Journey board widget. */
class WLA_Section_Journey_Widget extends WLA_Section_Widget_Base {}
/** Image + text split widget. */
class WLA_Section_Approach_Widget extends WLA_Section_Widget_Base {}
/** Pricing panel widget. */
class WLA_Section_Pricing_Widget extends WLA_Section_Widget_Base {}
/** Guide explorer widget. */
class WLA_Section_Resources_Widget extends WLA_Section_Widget_Base {}
/** Reviews panel widget. */
class WLA_Section_Reviews_Widget extends WLA_Section_Widget_Base {}
/** FAQ widget. */
class WLA_Section_Faq_Widget extends WLA_Section_Widget_Base {}
/** Contact form widget. */
class WLA_Section_Contact_Widget extends WLA_Section_Widget_Base {}
/** Inner page hero widget. */
class WLA_Section_Subpage_Hero_Widget extends WLA_Section_Widget_Base {}
/** Closing CTA band widget. */
class WLA_Section_Cta_Band_Widget extends WLA_Section_Widget_Base {}
/** Explore other pages grid widget. */
class WLA_Section_Page_Nav_Widget extends WLA_Section_Widget_Base {}
/** Rich text block widget. */
class WLA_Section_Rich_Text_Widget extends WLA_Section_Widget_Base {}
/** Dynamic treatment grid widget. */
class WLA_Section_Treatment_Grid_Widget extends WLA_Section_Widget_Base {}
/** Dynamic treatment detail widget. */
class WLA_Section_Treatment_Detail_Widget extends WLA_Section_Widget_Base {}
