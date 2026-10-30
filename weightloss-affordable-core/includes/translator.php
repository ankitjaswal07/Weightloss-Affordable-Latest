<?php
/**
 * Built-in Single-Site EN <-> ES Live Translator Backend & Admin Settings.
 * Zero duplicate pages: translates custom Elementor / WordPress edits dynamically
 * and caches them permanently in WordPress options.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wla_translate_batch_ajax() {
	check_ajax_referer( 'wla_site_nonce', 'nonce' );

	$target = isset( $_POST['target'] ) && 'en' === sanitize_key( wp_unslash( $_POST['target'] ) ) ? 'en' : 'es';
	$source = 'es' === $target ? 'en' : 'es';
	$raw    = isset( $_POST['texts'] ) ? wp_unslash( $_POST['texts'] ) : '[]'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$texts  = json_decode( (string) $raw, true );

	if ( ! is_array( $texts ) ) {
		wp_send_json_error( array( 'translations' => array() ) );
	}

	$cache   = get_option( 'wla_translation_cache', array() );
	$cache   = is_array( $cache ) ? $cache : array();
	$results = array();
	$updated = false;

	foreach ( array_slice( $texts, 0, 35 ) as $item ) {
		$clean = trim( sanitize_textarea_field( (string) $item ) );
		if ( '' === $clean || strlen( $clean ) > 1000 ) {
			continue;
		}
		if ( 'es' === $target && isset( $cache[ $clean ] ) ) {
			$results[ $clean ] = $cache[ $clean ];
			continue;
		}
		$url      = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . rawurlencode( $source ) . '&tl=' . rawurlencode( $target ) . '&dt=t&q=' . rawurlencode( $clean );
		$response = wp_remote_get( $url, array( 'timeout' => 4 ) );
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $decoded ) && isset( $decoded[0] ) && is_array( $decoded[0] ) ) {
				$joined = '';
				foreach ( $decoded[0] as $segment ) {
					if ( is_array( $segment ) && isset( $segment[0] ) ) {
						$joined .= (string) $segment[0];
					}
				}
				$joined = trim( $joined );
				if ( '' !== $joined ) {
					$results[ $clean ] = $joined;
					if ( 'es' === $target ) {
						$cache[ $clean ] = $joined;
						$updated         = true;
					}
				}
			}
		}
	}

	if ( $updated ) {
		update_option( 'wla_translation_cache', $cache, false );
	}

	wp_send_json_success( array( 'translations' => $results ) );
}
add_action( 'wp_ajax_nopriv_wla_translate_batch', 'wla_translate_batch_ajax' );
add_action( 'wp_ajax_wla_translate_batch', 'wla_translate_batch_ajax' );

function wla_translator_admin_submenu() {
	add_submenu_page(
		'wla-demo-import',
		__( 'Live EN/ES Translator', 'weightloss-affordable-core' ),
		__( 'Live Translator', 'weightloss-affordable-core' ),
		'manage_options',
		'wla-translator',
		'wla_translator_admin_screen'
	);
}
add_action( 'admin_menu', 'wla_translator_admin_submenu', 20 );

function wla_save_translator_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'weightloss-affordable-core' ) );
	}
	check_admin_referer( 'wla_save_translator', 'wla_translator_nonce' );

	$default_lang = isset( $_POST['wla_default_lang'] ) && 'es' === sanitize_key( wp_unslash( $_POST['wla_default_lang'] ) ) ? 'es' : 'en';
	update_option( 'wla_default_lang', $default_lang );

	$lines     = isset( $_POST['wla_overrides_raw'] ) ? explode( "\n", sanitize_textarea_field( wp_unslash( $_POST['wla_overrides_raw'] ) ) ) : array();
	$overrides = array();
	foreach ( $lines as $line ) {
		$parts = explode( '=', $line, 2 );
		if ( 2 === count( $parts ) && '' !== trim( $parts[0] ) && '' !== trim( $parts[1] ) ) {
			$overrides[ trim( $parts[0] ) ] = trim( $parts[1] );
		}
	}
	update_option( 'wla_translator_overrides', $overrides );

	if ( ! empty( $_POST['wla_clear_cache'] ) ) {
		delete_option( 'wla_translation_cache' );
	}

	wp_safe_redirect( add_query_arg( 'saved', '1', admin_url( 'admin.php?page=wla-translator' ) ) );
	exit;
}
add_action( 'admin_post_wla_save_translator', 'wla_save_translator_settings' );

function wla_translator_admin_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$default_lang = get_option( 'wla_default_lang', 'en' );
	$overrides    = get_option( 'wla_translator_overrides', array() );
	$cache        = get_option( 'wla_translation_cache', array() );
	$raw_text     = '';
	if ( is_array( $overrides ) ) {
		foreach ( $overrides as $en => $es ) {
			$raw_text .= $en . ' = ' . $es . "\n";
		}
	}
	?>
	<div class="wrap" style="max-width:860px">
		<h1><?php esc_html_e( 'WeightLossAffordable — Single-Page Live Translator (EN / ES)', 'weightloss-affordable-core' ); ?></h1>
		<p><?php esc_html_e( 'Translates the entire website in-place without maintaining duplicate copy pages. All 554 built-in theme and demo phrases switch in 0ms, and any custom text you add in Elementor or WordPress is translated and cached automatically.', 'weightloss-affordable-core' ); ?></p>
		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success inline"><p><?php esc_html_e( 'Translator settings saved.', 'weightloss-affordable-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wla_save_translator" />
			<?php wp_nonce_field( 'wla_save_translator', 'wla_translator_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="wla_default_lang"><?php esc_html_e( 'Default Visitor Language', 'weightloss-affordable-core' ); ?></label></th>
					<td>
						<select id="wla_default_lang" name="wla_default_lang">
							<option value="en" <?php selected( $default_lang, 'en' ); ?>>English (EN)</option>
							<option value="es" <?php selected( $default_lang, 'es' ); ?>>Español (ES)</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wla_overrides_raw"><?php esc_html_e( 'Custom Phrase Overrides', 'weightloss-affordable-core' ); ?></label></th>
					<td>
						<textarea id="wla_overrides_raw" name="wla_overrides_raw" rows="8" class="large-text code" placeholder="Book a consultation = Reservar una consulta"><?php echo esc_textarea( trim( $raw_text ) ); ?></textarea>
						<p class="description"><?php printf( esc_html__( 'Optional custom EN = ES overrides (one per line). Auto-cached dynamic phrases stored: %d.', 'weightloss-affordable-core' ), is_array( $cache ) ? count( $cache ) : 0 ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Reset Dynamic Cache', 'weightloss-affordable-core' ); ?></th>
					<td><label><input type="checkbox" name="wla_clear_cache" value="1" /> <?php esc_html_e( 'Clear cached dynamic translations on save', 'weightloss-affordable-core' ); ?></label></td>
				</tr>
			</table>
			<?php submit_button( __( 'Save Translator Settings', 'weightloss-affordable-core' ) ); ?>
		</form>
	</div>
	<?php
}
