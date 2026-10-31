<?php
/**
 * Dynamic page sections: admin UI, ordering, duplication, placement and
 * display conditions. Markup lives in the theme.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once NRX_CORE_DIR . 'includes/section-defs.php';

/**
 * Section type label helper.
 *
 * @param string $type Type key.
 * @return string
 */
function nrx_section_type_label( $type ) {
	$types = nrx_section_types();
	return isset( $types[ $type ] ) ? $types[ $type ]['label'] : ( $type ? ucfirst( $type ) : __( 'Sin tipo', 'nuestrarx-core' ) );
}

/**
 * Register meta boxes.
 */
function nrx_register_section_meta_boxes() {
	add_meta_box( 'nrx_section_content', __( 'Contenido de la sección', 'nuestrarx-core' ), 'nrx_section_content_box', 'nrx_section', 'normal', 'high' );
	add_meta_box( 'nrx_section_layout', __( 'Ubicación y presentación', 'nuestrarx-core' ), 'nrx_section_layout_box', 'nrx_section', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'nrx_register_section_meta_boxes' );

/**
 * Content meta box.
 *
 * @param WP_Post $post Section.
 */
function nrx_section_content_box( $post ) {
	wp_nonce_field( 'nrx_save_section', 'nrx_section_nonce' );
	$type      = get_post_meta( $post->ID, '_nrx_section_type', true );
	$types     = nrx_section_types();
	$values    = $type && isset( $types[ $type ] ) ? nrx_get_meta_values( $post->ID, $types[ $type ]['fields'] ) : array();
	$placement = get_post_meta( $post->ID, '_nrx_show_on', true );
	$placement = is_array( $placement ) ? $placement : array( 'front' );
	?>
	<div class="nrx-admin">
		<p class="nrx-section-type-row">
			<label for="nrx_section_type"><strong><?php esc_html_e( 'Tipo de sección', 'nuestrarx-core' ); ?></strong></label>
			<select id="nrx_section_type" name="nrx_section_type" data-nrx-section-type>
				<option value=""><?php esc_html_e( '— Elige un tipo —', 'nuestrarx-core' ); ?></option>
				<?php foreach ( $types as $key => $def ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $def['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="nrx-field-help"><?php esc_html_e( 'El tipo define los campos editables y la plantilla de diseño. Cambiar el tipo no borra contenido antiguo.', 'nuestrarx-core' ); ?></span>
		</p>

		<div class="nrx-section-placeholder" data-nrx-section-placeholder <?php echo $type ? 'hidden' : ''; ?>>
			<p><?php esc_html_e( 'Elige un tipo de sección y guarda para ver sus campos.', 'nuestrarx-core' ); ?></p>
		</div>

		<?php foreach ( $types as $key => $def ) : ?>
			<div class="nrx-section-fields" data-nrx-section-fields="<?php echo esc_attr( $key ); ?>" <?php echo ( $type === $key ) ? '' : 'hidden'; ?>>
				<p class="nrx-section-description"><?php echo esc_html( $def['description'] ?? '' ); ?></p>
				<?php nrx_render_fields( $def['fields'], $values, 'nrx_content' ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Layout / placement meta box.
 *
 * @param WP_Post $post Section.
 */
function nrx_section_layout_box( $post ) {
	$enabled  = get_post_meta( $post->ID, '_nrx_enabled', true );
	$enabled  = '' === $enabled ? '1' : $enabled;
	$devices  = get_post_meta( $post->ID, '_nrx_devices', true );
	$devices  = is_array( $devices ) && $devices ? $devices : array( 'desktop', 'tablet', 'mobile' );
	$show_on  = get_post_meta( $post->ID, '_nrx_show_on', true );
	$show_on  = is_array( $show_on ) && $show_on ? $show_on : array( 'front' );
	$anchor   = get_post_meta( $post->ID, '_nrx_anchor', true );
	$bg       = get_post_meta( $post->ID, '_nrx_background', true );
	$spacing  = get_post_meta( $post->ID, '_nrx_spacing', true );
	$custom   = get_post_meta( $post->ID, '_nrx_custom_class', true );
	$audience = get_post_meta( $post->ID, '_nrx_audience', true );
	$pages    = get_pages( array( 'number' => 200, 'sort_column' => 'post_title' ) );
	?>
	<div class="nrx-admin">
		<p><label class="nrx-checkbox-row"><input type="checkbox" name="nrx_layout[enabled]" value="1" <?php checked( $enabled, '1' ); ?>> <span><?php esc_html_e( 'Sección activa', 'nuestrarx-core' ); ?></span></label></p>

		<p><strong><?php esc_html_e( 'Dispositivos', 'nuestrarx-core' ); ?></strong><br>
			<?php foreach ( array( 'desktop' => __( 'Escritorio', 'nuestrarx-core' ), 'tablet' => 'Tablet', 'mobile' => __( 'Móvil', 'nuestrarx-core' ) ) as $key => $label ) : ?>
				<label class="nrx-checkbox-row"><input type="checkbox" name="nrx_layout[devices][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $devices, true ) ); ?>> <span><?php echo esc_html( $label ); ?></span></label>
			<?php endforeach; ?>
		</p>

		<p><strong><?php esc_html_e( 'Mostrar en', 'nuestrarx-core' ); ?></strong><br>
			<label class="nrx-checkbox-row"><input type="checkbox" name="nrx_layout[show_on][]" value="front" <?php checked( in_array( 'front', $show_on, true ) ); ?>> <span><?php esc_html_e( 'Página de inicio', 'nuestrarx-core' ); ?></span></label>
		</p>
		<div class="nrx-page-checklist">
			<?php foreach ( $pages as $page ) : ?>
				<label class="nrx-checkbox-row"><input type="checkbox" name="nrx_layout[show_on][]" value="<?php echo esc_attr( $page->ID ); ?>" <?php checked( in_array( (string) $page->ID, array_map( 'strval', $show_on ), true ) ); ?>> <span><?php echo esc_html( $page->post_title ); ?> <code>/<?php echo esc_html( $page->post_name ); ?></code></span></label>
			<?php endforeach; ?>
		</div>

		<p><strong><?php esc_html_e( 'Audiencia', 'nuestrarx-core' ); ?></strong><br>
			<select name="nrx_layout[audience]">
				<option value="all" <?php selected( $audience, 'all' ); ?>><?php esc_html_e( 'Todos', 'nuestrarx-core' ); ?></option>
				<option value="logged_out" <?php selected( $audience, 'logged_out' ); ?>><?php esc_html_e( 'Solo visitantes', 'nuestrarx-core' ); ?></option>
				<option value="logged_in" <?php selected( $audience, 'logged_in' ); ?>><?php esc_html_e( 'Solo usuarios con sesión', 'nuestrarx-core' ); ?></option>
			</select>
		</p>

		<p><label><strong><?php esc_html_e( 'Ancla (para menús)', 'nuestrarx-core' ); ?></strong><br>
			<input type="text" name="nrx_layout[anchor]" value="<?php echo esc_attr( $anchor ); ?>" placeholder="tratamientos" style="width:100%"></label></p>

		<p><label><strong><?php esc_html_e( 'Fondo', 'nuestrarx-core' ); ?></strong><br>
			<select name="nrx_layout[background]" style="width:100%">
				<option value="" <?php selected( $bg, '' ); ?>><?php esc_html_e( 'Por defecto', 'nuestrarx-core' ); ?></option>
				<option value="paper" <?php selected( $bg, 'paper' ); ?>><?php esc_html_e( 'Marfil claro', 'nuestrarx-core' ); ?></option>
				<option value="ivory" <?php selected( $bg, 'ivory' ); ?>><?php esc_html_e( 'Marfil', 'nuestrarx-core' ); ?></option>
				<option value="sage" <?php selected( $bg, 'sage' ); ?>><?php esc_html_e( 'Salvia', 'nuestrarx-core' ); ?></option>
				<option value="forest" <?php selected( $bg, 'forest' ); ?>><?php esc_html_e( 'Verde bosque', 'nuestrarx-core' ); ?></option>
			</select></label></p>

		<p><label><strong><?php esc_html_e( 'Espaciado', 'nuestrarx-core' ); ?></strong><br>
			<select name="nrx_layout[spacing]" style="width:100%">
				<option value="" <?php selected( $spacing, '' ); ?>><?php esc_html_e( 'Normal', 'nuestrarx-core' ); ?></option>
				<option value="compact" <?php selected( $spacing, 'compact' ); ?>><?php esc_html_e( 'Compacto', 'nuestrarx-core' ); ?></option>
				<option value="loose" <?php selected( $spacing, 'loose' ); ?>><?php esc_html_e( 'Amplio', 'nuestrarx-core' ); ?></option>
			</select></label></p>

		<p><label><strong><?php esc_html_e( 'Clase CSS adicional', 'nuestrarx-core' ); ?></strong><br>
			<input type="text" name="nrx_layout[custom_class]" value="<?php echo esc_attr( $custom ); ?>" style="width:100%"></label></p>

		<p class="nrx-field-help"><?php esc_html_e( 'El orden se controla desde la lista de secciones con las flechas ↑ ↓.', 'nuestrarx-core' ); ?></p>
	</div>
	<?php
}

/**
 * Save section content and layout.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function nrx_save_section( $post_id, $post ) {
	if ( ! nrx_can_save_meta( $post_id, 'nrx_save_section', 'nrx_section_nonce' ) ) {
		return;
	}
	if ( 'nrx_section' !== $post->post_type ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type  = isset( $_POST['nrx_section_type'] ) ? sanitize_key( wp_unslash( $_POST['nrx_section_type'] ) ) : '';
	$types = nrx_section_types();
	if ( ! isset( $types[ $type ] ) ) {
		$type = '';
	}
	update_post_meta( $post_id, '_nrx_section_type', $type );

	if ( $type && isset( $_POST['nrx_content'] ) && is_array( $_POST['nrx_content'] ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per schema below.
		$raw    = wp_unslash( $_POST['nrx_content'] );
		$values = nrx_sanitize_fields( $types[ $type ]['fields'], $raw );
		nrx_save_meta_values( $post_id, $types[ $type ]['fields'], $values );
	}

	$layout  = isset( $_POST['nrx_layout'] ) && is_array( $_POST['nrx_layout'] ) ? wp_unslash( $_POST['nrx_layout'] ) : array();
	$devices = isset( $layout['devices'] ) && is_array( $layout['devices'] ) ? array_intersect( array_map( 'sanitize_key', $layout['devices'] ), array( 'desktop', 'tablet', 'mobile' ) ) : array();
	$show_on = isset( $layout['show_on'] ) && is_array( $layout['show_on'] ) ? $layout['show_on'] : array();
	$show_on = array_values(
		array_filter(
			array_map(
				static function ( $value ) {
					return 'front' === $value ? 'front' : (string) absint( $value );
				},
				$show_on
			),
			static function ( $value ) {
				return '' !== $value && '0' !== $value;
			}
		)
	);

	update_post_meta( $post_id, '_nrx_enabled', ! empty( $layout['enabled'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_nrx_devices', $devices ? $devices : array( 'desktop' ) );
	update_post_meta( $post_id, '_nrx_show_on', $show_on ? $show_on : array( 'front' ) );
	update_post_meta( $post_id, '_nrx_anchor', sanitize_title( $layout['anchor'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_background', sanitize_key( $layout['background'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_spacing', sanitize_key( $layout['spacing'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_custom_class', sanitize_html_class( $layout['custom_class'] ?? '' ) );
	update_post_meta( $post_id, '_nrx_audience', in_array( $layout['audience'] ?? 'all', array( 'all', 'logged_in', 'logged_out' ), true ) ? $layout['audience'] : 'all' );

	// New sections go to the end of the order.
	if ( ! $post->menu_order ) {
		$max = (int) $GLOBALS['wpdb']->get_var( "SELECT MAX(menu_order) FROM {$GLOBALS['wpdb']->posts} WHERE post_type = 'nrx_section' AND post_status != 'trash'" );
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => $max + 10 ) );
	}
}
add_action( 'save_post', 'nrx_save_section', 10, 2 );

/**
 * Section data for the theme.
 *
 * @param int|WP_Post $section Section.
 * @return array
 */
function nrx_get_section_data( $section ) {
	$post = get_post( $section );
	if ( ! $post ) {
		return array();
	}
	$type  = get_post_meta( $post->ID, '_nrx_section_type', true );
	$types = nrx_section_types();
	if ( ! isset( $types[ $type ] ) ) {
		return array();
	}
	$values = nrx_get_meta_values( $post->ID, $types[ $type ]['fields'] );
	return array_merge(
		$values,
		array(
			'id'            => $post->ID,
			'type'          => $type,
			'title'         => get_the_title( $post ),
			'anchor'        => get_post_meta( $post->ID, '_nrx_anchor', true ),
			'background'    => get_post_meta( $post->ID, '_nrx_background', true ),
			'spacing'       => get_post_meta( $post->ID, '_nrx_spacing', true ),
			'custom_class'  => get_post_meta( $post->ID, '_nrx_custom_class', true ),
			'devices'       => (array) get_post_meta( $post->ID, '_nrx_devices', true ),
			'audience'      => get_post_meta( $post->ID, '_nrx_audience', true ),
			'require_approval' => ! empty( $values['require_approval'] ),
		)
	);
}

/**
 * Whether a section should render in the current context.
 *
 * @param WP_Post $post    Section post.
 * @param string  $context front|page.
 * @param int     $page_id Page ID for page context.
 * @return bool
 */
function nrx_section_visible( $post, $context = 'front', $page_id = 0 ) {
	if ( '1' !== get_post_meta( $post->ID, '_nrx_enabled', true ) ) {
		return false;
	}
	$show_on = get_post_meta( $post->ID, '_nrx_show_on', true );
	$show_on = is_array( $show_on ) ? array_map( 'strval', $show_on ) : array( 'front' );
	$match   = ( 'front' === $context && in_array( 'front', $show_on, true ) )
		|| ( $page_id && in_array( (string) $page_id, $show_on, true ) );
	if ( ! $match ) {
		return false;
	}

	$audience = get_post_meta( $post->ID, '_nrx_audience', true );
	if ( 'logged_in' === $audience && ! is_user_logged_in() ) {
		return false;
	}
	if ( 'logged_out' === $audience && is_user_logged_in() ) {
		return false;
	}

	$data = nrx_get_section_data( $post );
	if ( ! empty( $data['require_approval'] ) && ! nrx_compliance_is_approved() ) {
		// Administrators see gated sections with an internal notice; the public never does.
		return current_user_can( 'nrx_manage_compliance' );
	}

	return (bool) apply_filters( 'nrx_section_visible', true, $post, $context, $page_id );
}

/**
 * All sections for a context, ordered.
 *
 * @param string $context front|page.
 * @param int    $page_id Page ID.
 * @param bool   $include_hidden Include disabled sections (admin preview).
 * @return WP_Post[]
 */
function nrx_get_sections_for( $context = 'front', $page_id = 0, $include_hidden = false ) {
	$posts = get_posts(
		array(
			'post_type'              => 'nrx_section',
			'post_status'            => 'publish',
			'posts_per_page'         => 100,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
		)
	);
	if ( $include_hidden ) {
		return $posts;
	}
	return array_values( array_filter( $posts, static function ( $post ) use ( $context, $page_id ) {
		return nrx_section_visible( $post, $context, $page_id );
	} ) );
}

/* -------------------------------------------------------------------------
 * Admin list: columns, ordering and actions
 * ---------------------------------------------------------------------- */

/**
 * Admin columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function nrx_section_columns( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		if ( 'date' === $key ) {
			$out['nrx_type']     = __( 'Tipo', 'nuestrarx-core' );
			$out['nrx_placement'] = __( 'Ubicación', 'nuestrarx-core' );
			$out['nrx_status']   = __( 'Estado', 'nuestrarx-core' );
		}
		$out[ $key ] = $label;
	}
	return $out;
}
add_filter( 'manage_nrx_section_posts_columns', 'nrx_section_columns' );

/**
 * Render columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function nrx_section_column_content( $column, $post_id ) {
	if ( 'nrx_type' === $column ) {
		echo esc_html( nrx_section_type_label( get_post_meta( $post_id, '_nrx_section_type', true ) ) );
	}
	if ( 'nrx_placement' === $column ) {
		$show_on = (array) get_post_meta( $post_id, '_nrx_show_on', true );
		$labels  = array();
		foreach ( $show_on as $item ) {
			if ( 'front' === (string) $item ) {
				$labels[] = __( 'Inicio', 'nuestrarx-core' );
			} else {
				$page = get_post( (int) $item );
				if ( $page ) {
					$labels[] = $page->post_title;
				}
			}
		}
		echo esc_html( $labels ? implode( ', ', $labels ) : '—' );
	}
	if ( 'nrx_status' === $column ) {
		$enabled = get_post_meta( $post_id, '_nrx_enabled', true );
		echo '1' === $enabled ? '<span class="nrx-pill nrx-pill-on">' . esc_html__( 'Activa', 'nuestrarx-core' ) . '</span>' : '<span class="nrx-pill">' . esc_html__( 'Oculta', 'nuestrarx-core' ) . '</span>';
	}
}
add_action( 'manage_nrx_section_posts_custom_column', 'nrx_section_column_content', 10, 2 );

/**
 * Order the admin list by menu_order.
 *
 * @param WP_Query $query Query.
 */
function nrx_section_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'nrx_section' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'nrx_section_admin_order' );

/**
 * Add move/duplicate row actions.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function nrx_section_row_actions( $actions, $post ) {
	if ( 'nrx_section' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}
	$base = admin_url( 'admin-post.php' );
	$args = array(
		'action'  => 'nrx_section_move',
		'post_id' => $post->ID,
		'dir'     => 'up',
	);
	$actions['nrx_up'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( $args, $base ), 'nrx_section_move_' . $post->ID ) ) . '">↑ ' . esc_html__( 'Subir', 'nuestrarx-core' ) . '</a>';
	$args['dir']       = 'down';
	$actions['nrx_down'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( $args, $base ), 'nrx_section_move_' . $post->ID ) ) . '">↓ ' . esc_html__( 'Bajar', 'nuestrarx-core' ) . '</a>';
	$actions['nrx_duplicate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'nrx_section_duplicate', 'post_id' => $post->ID ), $base ), 'nrx_section_duplicate_' . $post->ID ) ) . '">' . esc_html__( 'Duplicar', 'nuestrarx-core' ) . '</a>';
	return $actions;
}
add_filter( 'post_row_actions', 'nrx_section_row_actions', 10, 2 );

/**
 * Move a section up or down and normalize order.
 */
function nrx_handle_section_move() {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id ) {
		wp_die( esc_html__( 'Sección no válida.', 'nuestrarx-core' ), 400 );
	}
	nrx_verify_admin_request( 'nrx_section_move_' . $post_id, 'edit_post' );
	$dir = isset( $_GET['dir'] ) && 'down' === $_GET['dir'] ? 'down' : 'up';

	$ids = get_posts(
		array(
			'post_type'      => 'nrx_section',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 200,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'fields'         => 'ids',
		)
	);
	$index = array_search( $post_id, $ids, true );
	if ( false !== $index ) {
		$swap = 'down' === $dir ? $index + 1 : $index - 1;
		if ( isset( $ids[ $swap ] ) ) {
			$tmp          = $ids[ $index ];
			$ids[ $index ] = $ids[ $swap ];
			$ids[ $swap ]  = $tmp;
		}
		foreach ( $ids as $position => $id ) {
			wp_update_post( array( 'ID' => $id, 'menu_order' => ( $position + 1 ) * 10 ) );
		}
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=nrx_section' ) );
	exit;
}
add_action( 'admin_post_nrx_section_move', 'nrx_handle_section_move' );

/**
 * Duplicate a section with all meta.
 */
function nrx_handle_section_duplicate() {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id ) {
		wp_die( esc_html__( 'Sección no válida.', 'nuestrarx-core' ), 400 );
	}
	nrx_verify_admin_request( 'nrx_section_duplicate_' . $post_id, 'edit_post' );
	$post = get_post( $post_id );
	if ( ! $post || 'nrx_section' !== $post->post_type ) {
		wp_die( esc_html__( 'Sección no encontrada.', 'nuestrarx-core' ), 404 );
	}
	$new_id = wp_insert_post(
		array(
			'post_type'   => 'nrx_section',
			'post_status' => 'draft',
			'post_title'  => $post->post_title . ' (copia)',
			'menu_order'  => $post->menu_order + 1,
			'post_author' => get_current_user_id(),
		),
		true
	);
	if ( ! is_wp_error( $new_id ) ) {
		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			if ( 0 === strpos( $key, '_nrx_' ) ) {
				foreach ( $values as $value ) {
					update_post_meta( $new_id, $key, maybe_unserialize( $value ) );
				}
			}
		}
		wp_safe_redirect( admin_url( 'post.php?post=' . $new_id . '&action=edit' ) );
		exit;
	}
	wp_die( esc_html__( 'No se pudo duplicar la sección.', 'nuestrarx-core' ) );
}
add_action( 'admin_post_nrx_section_duplicate', 'nrx_handle_section_duplicate' );

/**
 * Helpful empty-state notice on the section list.
 */
function nrx_section_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-nrx_section' !== $screen->id ) {
		return;
	}
	$count = wp_count_posts( 'nrx_section' );
	if ( ! empty( $count->publish ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p>' . esc_html__( 'Aún no hay secciones publicadas. Ejecuta la instalación de contenido en NuestraRx → Instalación para crear la portada, o crea secciones manualmente.', 'nuestrarx-core' ) . '</p></div>';
}
add_action( 'admin_notices', 'nrx_section_admin_notice' );

/**
 * Show an internal notice when a gated section is hidden from the public.
 *
 * @param WP_Post $post Section.
 */
function nrx_section_gate_notice( $post ) {
	if ( ! $post || 'nrx_section' !== $post->post_type ) {
		return;
	}
	$data = nrx_get_section_data( $post );
	if ( empty( $data['require_approval'] ) || nrx_compliance_is_approved() ) {
		return;
	}
	echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Sección bloqueada por cumplimiento.', 'nuestrarx-core' ) . '</strong> ' . esc_html__( 'Solo los administradores con permiso de cumplimiento pueden verla hasta que se aprueben las reclamaciones relacionadas (NuestraRx → Cumplimiento).', 'nuestrarx-core' ) . '</p></div>';
}
