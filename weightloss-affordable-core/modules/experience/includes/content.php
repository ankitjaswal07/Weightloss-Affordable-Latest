<?php
/** Custom media records and adapters for the existing product sources. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wla_experience_register_video_type() {
	if ( post_type_exists( 'wla_treatment' ) ) { add_post_type_support( 'wla_treatment', 'page-attributes' ); }
	register_post_type( 'wla_video_story', array(
		'labels' => array(
			'name' => __( 'Video Testimonials', 'wla-experience' ), 'singular_name' => __( 'Video Testimonial', 'wla-experience' ),
			'add_new_item' => __( 'Add approved video', 'wla-experience' ), 'edit_item' => __( 'Edit video', 'wla-experience' ),
		),
		'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'show_in_menu' => true,
		'menu_icon' => 'dashicons-video-alt3', 'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'capability_type' => 'post', 'map_meta_cap' => true,
	) );
}
add_action( 'init', 'wla_experience_register_video_type' );

function wla_experience_video_fields() {
	add_meta_box( 'wla-experience-video', __( 'Approved video details', 'wla-experience' ), 'wla_experience_video_box', 'wla_video_story', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wla_experience_video_fields' );
function wla_experience_video_box( $post ) {
	wp_nonce_field( 'wla_experience_video_save', 'wla_experience_video_nonce' );
	$url = get_post_meta( $post->ID, '_wla_video_url', true );
	$track = get_post_meta( $post->ID, '_wla_video_track', true );
	$lang = get_post_meta( $post->ID, '_wla_video_track_lang', true );
	?>
	<p><label for="wla-video-url"><strong><?php esc_html_e( 'Video URL (YouTube, Vimeo, or direct MP4/WebM)', 'wla-experience' ); ?></strong></label><br><input class="widefat" type="url" id="wla-video-url" name="wla_video_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://" /></p>
	<p><label for="wla-video-track"><strong><?php esc_html_e( 'Optional captions track URL for a direct file (.vtt)', 'wla-experience' ); ?></strong></label><br><input class="widefat" type="url" id="wla-video-track" name="wla_video_track" value="<?php echo esc_attr( $track ); ?>" placeholder="https://" /></p>
	<p><label for="wla-video-track-lang"><strong><?php esc_html_e( 'Captions language (BCP 47, e.g. es or en-US)', 'wla-experience' ); ?></strong></label><br><input class="regular-text" type="text" id="wla-video-track-lang" name="wla_video_track_lang" value="<?php echo esc_attr( $lang ?: 'es' ); ?>" /></p>
	<?php $approved = '1' === get_post_meta( $post->ID, '_wla_video_approved', true ); ?>
	<p><label><input type="checkbox" name="wla_video_approved" value="1" <?php checked( $approved ); ?> /> <?php esc_html_e( 'I confirm this genuine video has documented consent and required clinical/legal review.', 'wla-experience' ); ?></label></p>
	<p class="description"><?php esc_html_e( 'Use only genuine, authorized testimonial media. Set the featured image as the poster. For YouTube/Vimeo, enable available captions at the provider; the optional WebVTT track applies to direct video files. Only checked records display publicly.', 'wla-experience' ); ?></p>
	<?php
}
function wla_experience_save_video( $post_id ) {
	if ( ! isset( $_POST['wla_experience_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wla_experience_video_nonce'] ) ), 'wla_experience_video_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) || 'wla_video_story' !== get_post_type( $post_id ) ) { return; }
	foreach ( array( 'wla_video_url' => '_wla_video_url', 'wla_video_track' => '_wla_video_track' ) as $field => $meta ) {
		if ( isset( $_POST[ $field ] ) && is_scalar( $_POST[ $field ] ) ) {
			$url = esc_url_raw( wp_unslash( (string) $_POST[ $field ] ) );
			if ( $url ) { update_post_meta( $post_id, $meta, $url ); } else { delete_post_meta( $post_id, $meta ); }
		}
	}
	if ( isset( $_POST['wla_video_approved'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['wla_video_approved'] ) ) ) {
		update_post_meta( $post_id, '_wla_video_approved', '1' );
	} else {
		delete_post_meta( $post_id, '_wla_video_approved' );
	}
	if ( isset( $_POST['wla_video_track_lang'] ) && is_scalar( $_POST['wla_video_track_lang'] ) ) {
		$lang = sanitize_text_field( wp_unslash( (string) $_POST['wla_video_track_lang'] ) );
		$lang = preg_match( '/^[a-z]{2,3}(?:-[A-Z]{2})?$/', $lang ) ? $lang : 'es';
		update_post_meta( $post_id, '_wla_video_track_lang', $lang );
	}
}
add_action( 'save_post_wla_video_story', 'wla_experience_save_video' );

/** Return only published treatments from the authoritative available source. */
function wla_experience_treatment_posts( $limit = 12 ) {
	$type = post_type_exists( 'nrx_treatment' ) && function_exists( 'nrx_treatment_data' ) ? 'nrx_treatment' : ( post_type_exists( 'wla_treatment' ) ? 'wla_treatment' : '' );
	if ( ! $type ) { return array(); }
	$query = new WP_Query( array(
		'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => max( 1, min( 24, absint( $limit ) ) ),
		'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'no_found_rows' => true,
		'update_post_meta_cache' => true, 'update_post_term_cache' => false,
	) );
	return $query->posts;
}

function wla_experience_demo_treatments() {
	return array(
		array(
			'slug' => 'semaglutide', 'nrx_slug' => 'semaglutida', 'class' => 'GLP-1', 'title' => 'Semaglutide', 'type' => 'GLP-1 · Compounded',
			'description' => 'Compounded semaglutide prepared by licensed 503A pharmacies. Individual results vary.',
			'price' => 139, 'compare' => 199,
			'bullets' => array( 'GLP-1 receptor agonist: drug class extensively described in peer-reviewed scientific literature', 'Prepared by a licensed 503A pharmacy under medical supervision', 'Dose titration guided by your doctor', 'Refrigerated delivery to your door each month' ),
		),
		array(
			'slug' => 'tirzepatide', 'nrx_slug' => 'tirzepatida', 'class' => 'GLP-1 + GIP', 'title' => 'Tirzepatide', 'type' => 'GLP-1 + GIP · Compounded',
			'description' => 'Compounded tirzepatide prepared by licensed 503A pharmacies. Tirzepatide acts on GLP-1 and GIP receptors. Individual results vary.',
			'price' => 239, 'compare' => 299,
			'bullets' => array( 'Dual GLP-1 / GIP receptor agonist: drug class described in peer-reviewed scientific literature', 'Dual mechanism: acts on GLP-1 + GIP receptors', 'Prepared by a licensed 503A pharmacy under medical supervision', 'Clinical eligibility is determined by a licensed physician' ),
		),
	);
}

function wla_experience_video_embed( $url, $title ) {
	$url = esc_url( $url );
	if ( ! $url ) { return ''; }
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( $host && preg_match( '/(^|\.)youtube\.com$/i', $host ) ) {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$id = sanitize_text_field( $query['v'] ?? '' );
		if ( ! $id && preg_match( '#/(?:embed|shorts)/([A-Za-z0-9_-]+)#', (string) $path, $match ) ) { $id = $match[1]; }
		if ( $id ) { return 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $id ); }
	}
	if ( $host && preg_match( '/(^|\.)youtu\.be$/i', $host ) ) {
		$id = trim( (string) $path, '/' );
		if ( $id ) { return 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $id ); }
	}
	if ( $host && preg_match( '/(^|\.)vimeo\.com$/i', $host ) && preg_match( '#/(\d+)#', (string) $path, $match ) ) {
		return 'https://player.vimeo.com/video/' . rawurlencode( $match[1] );
	}
	return '';
}
