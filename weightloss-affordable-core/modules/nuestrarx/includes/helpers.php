<?php
/**
 * Shared helpers: settings access, bilingual output, price formatting,
 * states, icons, request validation, rate limiting and privacy utilities.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return all settings merged over defaults.
 *
 * @return array
 */
function nrx_settings() {
	static $cache = null;
	if ( is_array( $cache ) ) {
		return $cache;
	}
	$saved = get_option( 'nrx_settings', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$cache = array_merge( nrx_default_settings(), $saved );
	return $cache;
}

/**
 * Single setting accessor.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback when the key is empty.
 * @return mixed
 */
function nrx_setting( $key, $default = null ) {
	$settings = nrx_settings();
	if ( ! array_key_exists( $key, $settings ) ) {
		return $default;
	}
	$value = $settings[ $key ];
	if ( '' === $value || null === $value ) {
		return null === $default ? $value : $default;
	}
	return $value;
}

/**
 * Merge a patch into stored settings.
 *
 * @param array $patch Keys to update.
 * @return array
 */
function nrx_update_settings( $patch ) {
	$current = nrx_settings();
	$merged  = array_merge( $current, (array) $patch );
	update_option( 'nrx_settings', $merged, false );
	wp_cache_delete( 'nrx_settings', 'options' );
	return $merged;
}

/**
 * Build a bilingual attribute for an element. Spanish content is the
 * rendered default; English lives in data attributes and is applied by
 * assets/js/nrx.js. This mirrors the reference site's markup contract.
 *
 * @param string $en   English value.
 * @param string $type text|html|placeholder|aria|title.
 * @return string Attribute string, already escaped.
 */
function nrx_bi_attr( $en, $type = 'text' ) {
	$en = (string) $en;
	if ( '' === $en ) {
		return '';
	}
	switch ( $type ) {
		case 'html':
			$attr = 'data-en-html';
			break;
		case 'placeholder':
			$attr = 'data-en-placeholder';
			break;
		case 'aria':
			$attr = 'data-en-aria';
			break;
		case 'title':
			$attr = 'data-en-title';
			break;
		default:
			$attr = 'data-en';
	}
	return ' ' . $attr . '="' . esc_attr( $en ) . '"';
}

/**
 * Output bilingual text inside a wrapper element.
 *
 * @param string $es   Spanish.
 * @param string $en   English.
 * @param string $html Use data-en-html.
 * @return string
 */
function nrx_bi( $es, $en = '', $html = false ) {
	$es = (string) $es;
	$en = (string) $en;
	if ( ! nrx_is_spanish() ) {
		$primary = '' !== $en ? $en : $es;
		return $html ? wp_kses_post( $primary ) : esc_html( $primary );
	}
	$out = $html ? wp_kses_post( $es ) : esc_html( $es );
	if ( '' === $en ) {
		return $out;
	}
	return '<span class="nrx-bi"' . nrx_bi_attr( $en, $html ? 'html' : 'text' ) . '>' . $out . '</span>';
}

/**
 * Whether the site is configured Spanish-first.
 *
 * @return bool
 */
function nrx_is_spanish() {
	return 'es' === nrx_setting( 'default_lang', 'en' );
}

/**
 * Prefer English copies of bilingual arrays when English is the default.
 *
 * Keys ending in `_en` replace their base key. Nested repeaters are walked.
 *
 * @param mixed $data Config or field map.
 * @return mixed
 */
function nrx_prefer_english( $data ) {
	if ( ! is_array( $data ) || nrx_is_spanish() ) {
		return $data;
	}
	foreach ( $data as $key => $value ) {
		if ( is_array( $value ) ) {
			$data[ $key ] = nrx_prefer_english( $value );
		}
	}
	foreach ( $data as $key => $value ) {
		if ( ! is_string( $key ) || '_en' !== substr( $key, -3 ) ) {
			continue;
		}
		$base = substr( $key, 0, -3 );
		if ( ! array_key_exists( $base, $data ) ) {
			continue;
		}
		if ( is_array( $value ) ) {
			if ( $value ) {
				$data[ $base ] = $value;
			}
		} elseif ( '' !== (string) $value && null !== $value ) {
			$data[ $base ] = $value;
		}
	}
	return $data;
}

/**
 * Get the site states with Spanish and English labels.
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function nrx_states() {
	return array(
		'AL' => array( 'Alabama', 'Alabama', 'Alabama' ),
		'AK' => array( 'Alaska', 'Alaska', 'Alaska' ),
		'AZ' => array( 'Arizona', 'Arizona', 'Arizona' ),
		'AR' => array( 'Arkansas', 'Arkansas', 'Arkansas' ),
		'CA' => array( 'California', 'California', 'California' ),
		'NC' => array( 'Carolina del Norte', 'North Carolina', 'Carolina del Norte' ),
		'SC' => array( 'Carolina del Sur', 'South Carolina', 'Carolina del Sur' ),
		'CO' => array( 'Colorado', 'Colorado', 'Colorado' ),
		'CT' => array( 'Connecticut', 'Connecticut', 'Connecticut' ),
		'ND' => array( 'Dakota del Norte', 'North Dakota', 'Dakota del Norte' ),
		'SD' => array( 'Dakota del Sur', 'South Dakota', 'Dakota del Sur' ),
		'DE' => array( 'Delaware', 'Delaware', 'Delaware' ),
		'DC' => array( 'Washington D.C.', 'Washington D.C.', 'Distrito de Columbia' ),
		'FL' => array( 'Florida', 'Florida', 'Florida' ),
		'GA' => array( 'Georgia', 'Georgia', 'Georgia' ),
		'HI' => array( 'Hawái', 'Hawaii', 'Hawái' ),
		'ID' => array( 'Idaho', 'Idaho', 'Idaho' ),
		'IL' => array( 'Illinois', 'Illinois', 'Illinois' ),
		'IN' => array( 'Indiana', 'Indiana', 'Indiana' ),
		'IA' => array( 'Iowa', 'Iowa', 'Iowa' ),
		'KS' => array( 'Kansas', 'Kansas', 'Kansas' ),
		'KY' => array( 'Kentucky', 'Kentucky', 'Kentucky' ),
		'LA' => array( 'Luisiana', 'Louisiana', 'Luisiana' ),
		'ME' => array( 'Maine', 'Maine', 'Maine' ),
		'MD' => array( 'Maryland', 'Maryland', 'Maryland' ),
		'MA' => array( 'Massachusetts', 'Massachusetts', 'Massachusetts' ),
		'MI' => array( 'Míchigan', 'Michigan', 'Míchigan' ),
		'MN' => array( 'Minnesota', 'Minnesota', 'Minnesota' ),
		'MS' => array( 'Misisipi', 'Mississippi', 'Misisipi' ),
		'MO' => array( 'Misuri', 'Missouri', 'Misuri' ),
		'MT' => array( 'Montana', 'Montana', 'Montana' ),
		'NE' => array( 'Nebraska', 'Nebraska', 'Nebraska' ),
		'NV' => array( 'Nevada', 'Nevada', 'Nevada' ),
		'NH' => array( 'Nueva Hampshire', 'New Hampshire', 'Nueva Hampshire' ),
		'NJ' => array( 'Nueva Jersey', 'New Jersey', 'Nueva Jersey' ),
		'NY' => array( 'Nueva York', 'New York', 'Nueva York' ),
		'NM' => array( 'Nuevo México', 'New Mexico', 'Nuevo México' ),
		'OH' => array( 'Ohio', 'Ohio', 'Ohio' ),
		'OK' => array( 'Oklahoma', 'Oklahoma', 'Oklahoma' ),
		'OR' => array( 'Oregón', 'Oregon', 'Oregón' ),
		'PA' => array( 'Pensilvania', 'Pennsylvania', 'Pensilvania' ),
		'PR' => array( 'Puerto Rico', 'Puerto Rico', 'Puerto Rico' ),
		'RI' => array( 'Rhode Island', 'Rhode Island', 'Rhode Island' ),
		'TN' => array( 'Tennessee', 'Tennessee', 'Tennessee' ),
		'TX' => array( 'Texas', 'Texas', 'Texas' ),
		'UT' => array( 'Utah', 'Utah', 'Utah' ),
		'VT' => array( 'Vermont', 'Vermont', 'Vermont' ),
		'VA' => array( 'Virginia', 'Virginia', 'Virginia' ),
		'WV' => array( 'Virginia Occidental', 'West Virginia', 'Virginia Occidental' ),
		'WA' => array( 'Washington', 'Washington', 'Washington' ),
		'WI' => array( 'Wisconsin', 'Wisconsin', 'Wisconsin' ),
		'WY' => array( 'Wyoming', 'Wyoming', 'Wyoming' ),
	);
}

/**
 * States where the service is currently unavailable, as configured.
 *
 * @return array<int,string> State codes.
 */
function nrx_unavailable_states() {
	$states = nrx_setting( 'states_unavailable', array() );
	return is_array( $states ) ? array_map( 'strtoupper', $states ) : array();
}

/**
 * Check state availability.
 *
 * @param string $code Two letter code.
 * @return bool
 */
function nrx_state_is_available( $code ) {
	$code = strtoupper( (string) $code );
	if ( ! array_key_exists( $code, nrx_states() ) ) {
		return false;
	}
	return ! in_array( $code, nrx_unavailable_states(), true );
}

/**
 * Format a whole-dollar amount for display.
 *
 * @param float|int $amount Amount.
 * @param bool      $cents  Include cents.
 * @return string
 */
function nrx_price( $amount, $cents = false ) {
	$amount  = (float) $amount;
	$decimal = $cents ? 2 : 0;
	return '$' . number_format_i18n( $amount, $decimal );
}

/**
 * Resolve an image field (attachment ID, URL or empty) to a URL.
 *
 * @param mixed  $value Field value.
 * @param string $size  Image size for attachment IDs.
 * @return string
 */
function nrx_image_url( $value, $size = 'large' ) {
	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, $size );
		return $url ? $url : '';
	}
	if ( is_string( $value ) && '' !== $value ) {
		return esc_url_raw( $value );
	}
	return '';
}

/**
 * Resolve a site route from a stored value.
 *
 * Accepts absolute URLs, site-relative paths and #anchors. Keeps the
 * configured home URL so links survive staging/production moves.
 *
 * @param string $value Stored link.
 * @param string $fallback Fallback path.
 * @return string
 */
function nrx_url( $value, $fallback = '/' ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		$value = $fallback;
	}
	if ( 0 === strpos( $value, '#' ) ) {
		return esc_url( home_url( '/' ) ) . ltrim( $value, '/' );
	}
	if ( 0 === strpos( $value, 'mailto:' ) || 0 === strpos( $value, 'tel:' ) ) {
		return esc_url( $value );
	}
	if ( 0 === strpos( $value, 'http://' ) || 0 === strpos( $value, 'https://' ) ) {
		return esc_url( $value );
	}
	return esc_url( home_url( '/' . ltrim( $value, '/' ) ) );
}

/**
 * Build a URL to a known page by slug, with a safe fallback.
 *
 * @param string $slug Page slug.
 * @return string
 */
function nrx_page_url( $slug ) {
	$slug = trim( (string) $slug, '/' );
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return home_url( '/' . $slug . '/' );
}

/**
 * Eligibility quiz URL.
 *
 * @param array $args Query arguments.
 * @return string
 */
function nrx_intake_url( $args = array() ) {
	$slug = nrx_setting( 'intake_slug', 'evaluacion' );
	$url  = nrx_page_url( $slug );
	if ( ! empty( $args ) ) {
		$url = add_query_arg( array_map( 'sanitize_text_field', $args ), $url );
	}
	return $url;
}

/**
 * External/hosted checkout URL (payment integration is configuration-only).
 *
 * @param array $args Query arguments.
 * @return string
 */
function nrx_checkout_url( $args = array() ) {
	$base = trim( (string) nrx_setting( 'checkout_url', '' ) );
	if ( '' === $base ) {
		return '';
	}
	return add_query_arg( array_map( 'sanitize_text_field', $args ), $base );
}

/**
 * WhatsApp deep link.
 *
 * @param string $message Prefilled message.
 * @return string
 */
function nrx_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/[^0-9]/', '', (string) nrx_setting( 'whatsapp', '' ) );
	if ( '' === $number ) {
		return '';
	}
	$url = 'https://wa.me/' . $number;
	if ( '' !== $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}
	return $url;
}

/**
 * Patient portal URL.
 *
 * @return string
 */
function nrx_portal_url() {
	$url = (string) nrx_setting( 'patient_portal_url', '' );
	return $url ? esc_url( $url ) : '';
}

/**
 * Current request URL without credentials.
 *
 * @return string
 */
function nrx_current_url() {
	$scheme = is_ssl() ? 'https' : 'http';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : wp_parse_url( home_url(), PHP_URL_HOST );
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return $scheme . '://' . $host . $uri;
}

/**
 * Capability check shortcut.
 *
 * @param string $cap Capability.
 * @return bool
 */
function nrx_user_can( $cap ) {
	return current_user_can( $cap );
}

/**
 * Verify an admin request nonce and capability. Dies on failure.
 *
 * @param string $action Nonce action.
 * @param string $cap    Capability.
 */
function nrx_verify_admin_request( $action, $cap = 'manage_options' ) {
	if ( ! is_user_logged_in() || ! current_user_can( $cap ) ) {
		wp_die( esc_html__( 'No tienes permiso para realizar esta acción.', 'nuestrarx-core' ), 403 );
	}
	$nonce = isset( $_REQUEST['_nrx_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_nrx_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		wp_die( esc_html__( 'La verificación de seguridad falló. Recarga la página e inténtalo de nuevo.', 'nuestrarx-core' ), 403 );
	}
}

/**
 * Rate limit an action for the current visitor.
 *
 * Uses REMOTE_ADDR only. If the site runs behind a proxy/CDN the proxy
 * must normalize the client address.
 *
 * @param string $action Action key.
 * @param int    $limit  Allowed attempts.
 * @param int    $window Window in seconds.
 * @return bool True when the request is allowed.
 */
function nrx_rate_limit( $action, $limit = 6, $window = 3600 ) {
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key  = 'nrx_rl_' . md5( $action . '|' . $ip . '|' . wp_salt( 'nonce' ) );
	$hits = (int) get_transient( $key );
	if ( $hits >= $limit ) {
		return false;
	}
	set_transient( $key, $hits + 1, $window );
	return true;
}

/**
 * Clear a rate limit bucket (used after a successful legitimate action).
 *
 * @param string $action Action key.
 */
function nrx_rate_limit_clear( $action ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'nrx_rl_' . md5( $action . '|' . $ip . '|' . wp_salt( 'nonce' ) );
	delete_transient( $key );
}

/**
 * Sanitize a hex color.
 *
 * @param string $color Raw color.
 * @param string $fallback Fallback.
 * @return string
 */
function nrx_hex( $color, $fallback = '#173d36' ) {
	$color = sanitize_hex_color( (string) $color );
	return $color ? $color : $fallback;
}

/**
 * Write a non-PHI diagnostic entry.
 *
 * @param string $message Message.
 * @param array  $context Non-sensitive context.
 */
function nrx_log( $message, $context = array() ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( '[NuestraRx] ' . $message . ' ' . wp_json_encode( $context ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
	$log = get_option( 'nrx_recent_errors', array() );
	$log = is_array( $log ) ? $log : array();
	array_unshift(
		$log,
		array(
			'time'    => current_time( 'mysql' ),
			'message' => sanitize_text_field( $message ),
			'context' => array_map( 'sanitize_text_field', array_filter( (array) $context, 'is_scalar' ) ),
		)
	);
	update_option( 'nrx_recent_errors', array_slice( $log, 0, 20 ), false );
}

/**
 * Encrypt a free-text clinical answer when sodium is available.
 *
 * Only used when the administrator explicitly enables clinical answer
 * storage. Returns a prefixed payload so decryption is unambiguous.
 *
 * @param string $plain Plain text.
 * @return string
 */
function nrx_encrypt( $plain ) {
	$plain = (string) $plain;
	if ( '' === $plain ) {
		return '';
	}
	if ( function_exists( 'sodium_crypto_secretbox' ) ) {
		try {
			$key    = hash( 'sha256', wp_salt( 'auth' ), true );
			$nonce  = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$cipher = sodium_crypto_secretbox( $plain, $nonce, $key );
			return 'nrxenc:' . base64_encode( $nonce . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} catch ( Exception $e ) {
			nrx_log( 'Encryption unavailable', array( 'error' => $e->getMessage() ) );
		}
	}
	$mac = hash_hmac( 'sha256', $plain, wp_salt( 'auth' ), true );
	return 'nrxb64:' . base64_encode( $mac . $plain ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

/**
 * Decrypt a stored clinical answer.
 *
 * @param string $value Stored value.
 * @return string
 */
function nrx_decrypt( $value ) {
	$value = (string) $value;
	if ( 0 === strpos( $value, 'nrxb64:' ) ) {
		$raw = base64_decode( substr( $value, 7 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $raw || strlen( $raw ) <= 32 ) {
			return '';
		}
		$mac  = substr( $raw, 0, 32 );
		$plain = substr( $raw, 32 );
		$check = hash_hmac( 'sha256', $plain, wp_salt( 'auth' ), true );
		return hash_equals( $mac, $check ) ? $plain : '';
	}
	if ( 0 !== strpos( $value, 'nrxenc:' ) || ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
		return $value;
	}
	$raw = base64_decode( substr( $value, 7 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( false === $raw || strlen( $raw ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}
	$key    = hash( 'sha256', wp_salt( 'auth' ), true );
	$nonce  = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	$cipher = substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	$plain  = sodium_crypto_secretbox_open( $cipher, $nonce, $key );
	return false === $plain ? '' : $plain;
}

/**
 * Privacy-safe hash of an IP for audit purposes.
 *
 * @return string
 */
function nrx_hashed_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ? substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 24 ) : '';
}

/**
 * SVG icon set used by templates. Stroke icons inherit currentColor.
 *
 * @param string $name Icon key.
 * @param int    $size Pixel size.
 * @return string
 */
function nrx_icon( $name, $size = 24 ) {
	$paths = array(
		'check'       => '<path d="M5 12.5l4.2 4.2L19 6.8"/>',
		'arrow'       => '<path d="M4 12h15M13 6l6 6-6 6"/>',
		'arrow-up'    => '<path d="M12 19V5M6 11l6-6 6 6"/>',
		'shield'      => '<path d="M12 21s7-3.5 7-9.5V5.5L12 3 5 5.5v6C5 17.5 12 21 12 21z"/><path d="M9 12l2 2 4-4"/>',
		'heart'       => '<path d="M20.8 8.7c0 5.1-8.8 10.2-8.8 10.2S3.2 13.8 3.2 8.7A4.2 4.2 0 0 1 11 6.1l1 1 1-1a4.2 4.2 0 0 1 7.8 2.6z"/>',
		'chat'        => '<path d="M20 11.3a7.8 7.8 0 0 1-8 7.7 8.5 8.5 0 0 1-3.5-.7L4 20l1.5-3.6A7.5 7.5 0 0 1 4 11.3a8 8 0 0 1 16 0z"/>',
		'book'        => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16z"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20"/>',
		'spark'       => '<path d="M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3z"/>',
		'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'map'         => '<path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
		'lock'        => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
		'phone'       => '<path d="M6.5 3h3l1.5 4-2 1.5a12 12 0 0 0 6.5 6.5L17 13l4 1.5v3A2.5 2.5 0 0 1 18.5 20C10.5 19.6 4.4 13.5 4 5.5A2.5 2.5 0 0 1 6.5 3z"/>',
		'mail'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5 12 13l8.5-6.5"/>',
		'whatsapp'    => '<path d="M20 11.5A8 8 0 0 1 8.4 18.7L4 20l1.4-4.3A8 8 0 1 1 20 11.5z"/><path d="M8.7 8.3c.2-.5.4-.5.7-.5h.5c.2 0 .4 0 .6.5l.7 1.6c.1.3 0 .5-.1.7l-.5.6c-.1.2-.2.4 0 .7.3.5.8 1.1 1.4 1.5.6.4 1 .5 1.3.5.2 0 .4-.1.5-.3l.5-.6c.2-.2.4-.2.6-.1l1.6.7c.3.1.4.3.5.5 0 .2 0 1-.3 1.4-.3.5-1.2 1-1.8 1-.6 0-1.4 0-2.9-.7a10 10 0 0 1-4.1-4c-.5-.9-.7-1.7-.7-2.4 0-.7.4-1.2.6-1.5z"/>',
		'star'        => '<path d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3z"/>',
		'chevron'     => '<path d="M6 9l6 6 6-6"/>',
		'plus'        => '<path d="M12 5v14M5 12h14"/>',
		'minus'       => '<path d="M5 12h14"/>',
		'close'       => '<path d="M6 6l12 12M18 6 6 18"/>',
		'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'globe'       => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.7 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.7-3.8-9S9.5 5.6 12 3z"/>',
		'user'        => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
		'stethoscope' => '<path d="M6 3v6a4 4 0 0 0 8 0V3"/><path d="M10 13v3a4 4 0 0 0 8 0v-2"/><circle cx="18" cy="10" r="2"/>',
		'pill'        => '<rect x="3.5" y="9" width="17" height="6" rx="3" transform="rotate(-45 12 12)"/><path d="M8.5 8.5l7 7"/>',
		'truck'       => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7z"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
		'lab'         => '<path d="M9 3h6M10 3v6l-5 8.5A2.5 2.5 0 0 0 7.2 21h9.6a2.5 2.5 0 0 0 2.2-3.5L14 9V3"/><path d="M8 15h8"/>',
		'document'    => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
		'alert'       => '<path d="M12 3 2.8 20h18.4L12 3z"/><path d="M12 9v5M12 17.2v.1"/>',
		'info'        => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.1"/>',
		'leaf'        => '<path d="M20 4C10 4 4 8 4 15c0 3 2 5 5 5 7 0 11-6 11-16z"/><path d="M4 21c3-6 7-9 12-11"/>',
		'flask'       => '<path d="M9 3h6M10 3v5l-4 9a2 2 0 0 0 1.8 3h8.4A2 2 0 0 0 18 17l-4-9V3"/>',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['spark'];
	return '<svg class="nrx-icon nrx-icon-' . esc_attr( $name ) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

/**
 * Whether the current request is an intake/quiz page.
 *
 * @return bool
 */
function nrx_is_intake_page() {
	if ( ! is_page() ) {
		return false;
	}
	return (bool) get_page_template_slug( get_queried_object_id() ) === 'page-templates/intake.php'
		|| is_page( nrx_setting( 'intake_slug', 'evaluacion' ) );
}

/**
 * Get a treatment post by slug.
 *
 * @param string $slug Treatment slug.
 * @return WP_Post|null
 */
function nrx_get_treatment_by_slug( $slug ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'nrx_treatment',
			'name'                   => sanitize_title( $slug ),
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);
	return $query->have_posts() ? $query->posts[0] : null;
}

/**
 * Published treatments ordered for display.
 *
 * Returns the prepared data arrays produced by nrx_treatment_data() so
 * templates never read raw meta or duplicate pricing logic.
 *
 * @param int $limit Max items, 0 for all.
 * @return array[]
 */
function nrx_get_treatments( $limit = 0 ) {
	$args = array(
		'post_type'      => 'nrx_treatment',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : 10,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'no_found_rows'  => true,
	);
	$query = new WP_Query( $args );
	$out   = array();
	foreach ( $query->posts as $post ) {
		$data = nrx_treatment_data( $post );
		if ( $data ) {
			$out[] = $data;
		}
	}
	return $out;
}

/**
 * Featured image URL with a graceful empty string.
 *
 * @param int    $post_id Post ID.
 * @param string $size Size.
 * @return string
 */
function nrx_thumbnail_url( $post_id, $size = 'large' ) {
	$url = get_the_post_thumbnail_url( $post_id, $size );
	return $url ? $url : '';
}
