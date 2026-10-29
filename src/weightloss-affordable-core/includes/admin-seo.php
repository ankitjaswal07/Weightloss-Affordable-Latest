<?php
/**
 * SEO fields meta box for pages and posts.
 *
 * Titles and descriptions are editable per page without an SEO plugin. When a
 * dedicated SEO plugin is active this box hides itself, so there is only ever
 * one source of meta output.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the meta box.
 */
function wla_add_seo_meta_box() {
	if ( wla_seo_plugin_active() ) {
		return;
	}
	foreach ( array( 'page', 'post' ) as $type ) {
		add_meta_box(
			'wla_seo',
			__( 'SEO and social preview', 'weightloss-affordable-core' ),
			'wla_render_seo_meta_box',
			$type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'wla_add_seo_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function wla_render_seo_meta_box( $post ) {
	wp_nonce_field( 'wla_save_seo', 'wla_seo_nonce' );

	$title       = get_post_meta( $post->ID, '_wla_seo_title', true );
	$description = get_post_meta( $post->ID, '_wla_seo_description', true );
	$noindex     = get_post_meta( $post->ID, '_wla_noindex', true );
	$image_id    = (int) get_post_meta( $post->ID, '_wla_og_image', true );
	$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	?>
	<p class="description">
		<?php esc_html_e( 'Leave the title blank to use the page title and site name. Aim for a description of roughly 150–160 characters.', 'weightloss-affordable-core' ); ?>
	</p>
	<p class="wla-field">
		<label for="wla-seo-title"><strong><?php esc_html_e( 'SEO title', 'weightloss-affordable-core' ); ?></strong></label>
		<input type="text" id="wla-seo-title" name="wla_seo_title" class="large-text" maxlength="120" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<p class="wla-field">
		<label for="wla-seo-description"><strong><?php esc_html_e( 'Meta description', 'weightloss-affordable-core' ); ?></strong></label>
		<textarea id="wla-seo-description" name="wla_seo_description" class="large-text" rows="3" maxlength="320"><?php echo esc_textarea( $description ); ?></textarea>
	</p>
	<p class="wla-field">
		<label for="wla-seo-image"><strong><?php esc_html_e( 'Social sharing image (optional)', 'weightloss-affordable-core' ); ?></strong></label>
		<span class="wla-media-field">
			<input type="text" id="wla-seo-image" name="wla_og_image" value="<?php echo esc_attr( $image_id ? (string) $image_id : '' ); ?>" class="regular-text" />
			<button type="button" class="button wla-media-pick"><?php esc_html_e( 'Choose image', 'weightloss-affordable-core' ); ?></button>
			<button type="button" class="button-link wla-media-clear"><?php esc_html_e( 'Clear', 'weightloss-affordable-core' ); ?></button>
		</span>
		<?php if ( $image_url ) : ?>
			<br /><img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:180px;height:auto;margin-top:8px" />
		<?php endif; ?>
	</p>
	<p class="wla-field">
		<label>
			<input type="checkbox" name="wla_noindex" value="1" <?php checked( $noindex, '1' ); ?> />
			<?php esc_html_e( 'Hide this page from search engines (noindex)', 'weightloss-affordable-core' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Save SEO fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function wla_save_seo_meta( $post_id ) {
	if ( ! isset( $_POST['wla_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wla_seo_nonce'] ) ), 'wla_save_seo' ) ) {
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

	$title       = isset( $_POST['wla_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['wla_seo_title'] ) ) : '';
	$description = isset( $_POST['wla_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wla_seo_description'] ) ) : '';
	$noindex     = isset( $_POST['wla_noindex'] ) ? '1' : '';
	$image       = isset( $_POST['wla_og_image'] ) ? absint( $_POST['wla_og_image'] ) : 0;

	if ( '' === $title ) {
		delete_post_meta( $post_id, '_wla_seo_title' );
	} else {
		update_post_meta( $post_id, '_wla_seo_title', $title );
	}
	if ( '' === $description ) {
		delete_post_meta( $post_id, '_wla_seo_description' );
	} else {
		update_post_meta( $post_id, '_wla_seo_description', $description );
	}
	if ( '1' === $noindex ) {
		update_post_meta( $post_id, '_wla_noindex', '1' );
	} else {
		delete_post_meta( $post_id, '_wla_noindex' );
	}
	if ( $image ) {
		update_post_meta( $post_id, '_wla_og_image', $image );
	} else {
		delete_post_meta( $post_id, '_wla_og_image' );
	}
}
add_action( 'save_post', 'wla_save_seo_meta' );

/**
 * Respect the per-page noindex flag and the sharing image.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function wla_filter_robots( $robots ) {
	if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_wla_noindex', true ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'wla_filter_robots' );

/**
 * Include the per-post noindex in our own robots output.
 */
function wla_echo_noindex_meta() {
	if ( wla_seo_plugin_active() ) {
		return;
	}
	if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_wla_noindex', true ) ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'wla_echo_noindex_meta', 4 );
