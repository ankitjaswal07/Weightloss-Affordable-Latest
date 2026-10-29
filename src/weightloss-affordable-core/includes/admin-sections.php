<?php
/**
 * "Page sections" meta box.
 *
 * Gives administrators a structured, drag-free editor for every page section
 * without opening Elementor. Data is stored in `_wla_sections` and mirrored
 * into the Elementor document when one exists, so both editors agree.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the meta box on pages, treatments and posts.
 */
function wla_add_sections_meta_box() {
	foreach ( array( 'page', 'nrx_treatment', 'post' ) as $type ) {
		add_meta_box(
			'wla_page_sections',
			__( 'Page sections', 'weightloss-affordable-core' ),
			'wla_render_sections_meta_box',
			$type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'wla_add_sections_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post object.
 */
function wla_render_sections_meta_box( $post ) {
	wp_nonce_field( 'wla_save_sections', 'wla_sections_nonce' );

	$sections = wla_get_page_sections( $post->ID, false );
	$schema   = wla_section_schema();
	$elementor = ( 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true ) );
	?>
	<div class="wla-sections" data-wla-sections>
		<p class="wla-sections__intro">
			<?php esc_html_e( 'These are the sections rendered on this page, in order. Edit the text here, or open Elementor to design the layout visually — both editors edit the same sections.', 'weightloss-affordable-core' ); ?>
		</p>
		<?php if ( $elementor ) : ?>
			<p class="notice notice-info inline wla-sections__notice">
				<?php esc_html_e( 'This page is built with Elementor. Changes saved here are written into the Elementor document as well.', 'weightloss-affordable-core' ); ?>
			</p>
		<?php endif; ?>

		<div class="wla-section-list" data-wla-section-list>
			<?php
			if ( empty( $sections ) ) {
				echo '<p class="wla-sections__empty">' . esc_html__( 'No sections yet. Add one below — or use Elementor to build the page.', 'weightloss-affordable-core' ) . '</p>';
			}
			foreach ( $sections as $i => $section ) {
				wla_render_section_card( $section['type'], $section['fields'], $i );
			}
			?>
		</div>

		<p class="wla-section-add">
			<select data-wla-add-type>
				<?php foreach ( $schema as $type => $def ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $def['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button button-secondary" data-wla-add-section>
				<?php esc_html_e( '+ Add section', 'weightloss-affordable-core' ); ?>
			</button>
		</p>

		<div data-wla-section-templates hidden>
			<?php
			foreach ( $schema as $type => $def ) {
				echo '<template data-wla-template="' . esc_attr( $type ) . '">';
				wla_render_section_card( $type, wla_section_defaults( $type ), '__i__' );
				echo '</template>';
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render one editable section card.
 *
 * @param string     $type   Section type.
 * @param array      $fields Field values.
 * @param int|string $index  Section index (or placeholder).
 * @return void
 */
function wla_render_section_card( $type, $fields, $index ) {
	$schema = wla_section_schema();
	if ( ! isset( $schema[ $type ] ) ) {
		return;
	}
	$def = $schema[ $type ];
	?>
	<div class="wla-section" data-wla-section data-section-type="<?php echo esc_attr( $type ); ?>">
		<div class="wla-section__head">
			<span class="wla-section__grip" aria-hidden="true">☰</span>
			<strong class="wla-section__title"><?php echo esc_html( $def['label'] ); ?></strong>
			<input type="hidden" name="wla_sections[<?php echo esc_attr( $index ); ?>][type]" value="<?php echo esc_attr( $type ); ?>" />
			<span class="wla-section__tools">
				<button type="button" class="button-link wla-move-up" aria-label="<?php esc_attr_e( 'Move section up', 'weightloss-affordable-core' ); ?>">↑</button>
				<button type="button" class="button-link wla-move-down" aria-label="<?php esc_attr_e( 'Move section down', 'weightloss-affordable-core' ); ?>">↓</button>
				<button type="button" class="button-link wla-toggle" aria-expanded="false"><?php esc_html_e( 'Edit', 'weightloss-affordable-core' ); ?></button>
				<button type="button" class="button-link-delete wla-remove"><?php esc_html_e( 'Delete', 'weightloss-affordable-core' ); ?></button>
			</span>
		</div>
		<div class="wla-section__body" hidden>
			<?php
			foreach ( $def['fields'] as $field ) {
				$name  = 'wla_sections[' . $index . '][fields][' . $field['key'] . ']';
				$value = isset( $fields[ $field['key'] ] ) ? $fields[ $field['key'] ] : ( $field['default'] ?? '' );
				if ( 'repeater' === $field['type'] ) {
					$name = 'wla_sections[' . $index . '][fields][' . $field['key'] . ']';
					wla_admin_repeater_placeholder( $name, $field, $value, '__r__' );
				} else {
					wla_admin_field( $name, $field, $value );
				}
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Repeater renderer that supports a row-index placeholder for templates.
 *
 * @param string $name        Base field name.
 * @param array  $field       Field definition.
 * @param mixed  $rows        Rows.
 * @param string $placeholder Placeholder token for the row index.
 * @return void
 */
function wla_admin_repeater_placeholder( $name, $field, $rows, $placeholder ) {
	$rows  = is_array( $rows ) ? array_values( $rows ) : array();
	$subs  = (array) ( $field['fields'] ?? array() );
	$index = 0;

	echo '<div class="wla-repeater" data-wla-repeater>';
	echo '<p class="wla-repeater__label"><strong>' . esc_html( $field['label'] ) . '</strong></p>';
	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
	}
	echo '<div class="wla-repeater__rows" data-wla-repeater-rows>';
	foreach ( $rows as $row ) {
		wla_admin_repeater_row( $name, $subs, is_array( $row ) ? $row : array(), $index );
		++$index;
	}
	wla_admin_repeater_row( $name, $subs, array(), $placeholder, true );
	echo '</div>';
	echo '<button type="button" class="button wla-repeater__add">' . esc_html__( '+ Add row', 'weightloss-affordable-core' ) . '</button>';
	echo '</div>';
}

/**
 * Save the meta box.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_save_sections_meta_box( $post_id ) {
	if ( ! isset( $_POST['wla_sections_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wla_sections_nonce'] ) ), 'wla_save_sections' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['wla_sections'] ) ? wp_unslash( $_POST['wla_sections'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field in wla_normalize_section().
	if ( ! is_array( $raw ) ) {
		return;
	}

	$sections = array();
	foreach ( $raw as $section ) {
		if ( ! is_array( $section ) || empty( $section['type'] ) ) {
			continue;
		}
		$type = sanitize_key( $section['type'] );
		if ( ! wla_section_exists( $type ) ) {
			continue;
		}
		$fields       = isset( $section['fields'] ) && is_array( $section['fields'] ) ? $section['fields'] : array();
		$sections[]   = array(
			'type'   => $type,
			'fields' => wla_normalize_section( $type, $fields ),
		);
	}

	wla_save_page_sections( $post_id, $sections );

	if ( function_exists( 'wla_sync_sections_to_elementor' ) ) {
		wla_sync_sections_to_elementor( $post_id );
	}
}
add_action( 'save_post', 'wla_save_sections_meta_box' );
