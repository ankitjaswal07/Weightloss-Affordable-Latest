<?php
/**
 * Plugin settings.
 *
 * One option array (`wla_settings`) keeps business configuration out of code:
 * contact details, notification recipients, service area, retention window and
 * the compliance statements shown in the footer and on the treatment pages.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings.
 *
 * @return array
 */
function wla_default_settings() {
	return array(
		'business_name'     => get_bloginfo( 'name' ),
		'contact_email'     => get_option( 'admin_email' ),
		'contact_phone'     => '',
		'contact_address'   => '',
		'business_hours'    => __( 'Monday–Friday, 9am–5pm', 'weightloss-affordable-core' ),
		'states_served'     => __( 'Confirm with your clinical partner before publishing', 'weightloss-affordable-core' ),
		'pharmacy_statement' => __( 'Compounded medications are prepared by state-licensed pharmacies under a prescription from a licensed clinician.', 'weightloss-affordable-core' ),
		'notification_emails' => get_option( 'admin_email' ),
		'notify_enabled'    => '1',
		'retention_days'    => '365',
		'spam_min_seconds'  => '2',
		'form_disclaimer'   => __( 'Do not include medical details in a general enquiry form. The consultation questionnaire is the secure place for health information.', 'weightloss-affordable-core' ),
		'clinical_reviewer' => '',
		'last_reviewed'     => '',
		'currency_code'     => 'USD',
		'currency_symbol'   => '$',
	);
}

/**
 * Read settings with defaults.
 *
 * @param string $key Optional key.
 * @return mixed
 */
function wla_get_settings( $key = '' ) {
	$settings = wp_parse_args( (array) get_option( 'wla_settings', array() ), wla_default_settings() );
	if ( '' === $key ) {
		return $settings;
	}
	return isset( $settings[ $key ] ) ? $settings[ $key ] : '';
}

/**
 * Register the settings screen.
 */
function wla_register_settings_page() {
	add_submenu_page(
		'wla-dashboard',
		__( 'Settings', 'weightloss-affordable-core' ),
		__( 'Settings', 'weightloss-affordable-core' ),
		'manage_options',
		'wla-settings',
		'wla_render_settings_page'
	);
}
add_action( 'admin_menu', 'wla_register_settings_page', 20 );

/**
 * Settings field definitions.
 *
 * @return array
 */
function wla_settings_fields() {
	return array(
		'business'     => array(
			'label'  => __( 'Business details', 'weightloss-affordable-core' ),
			'fields' => array(
				'business_name'      => array( 'label' => __( 'Business name', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'contact_email'      => array( 'label' => __( 'Public contact email', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'contact_phone'      => array( 'label' => __( 'Public phone number', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'contact_address'    => array( 'label' => __( 'Business address', 'weightloss-affordable-core' ), 'type' => 'textarea' ),
				'business_hours'     => array( 'label' => __( 'Business hours', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'states_served'      => array( 'label' => __( 'Service area', 'weightloss-affordable-core' ), 'type' => 'text', 'help' => __( 'Only state a service area you can verify with your clinical partner.', 'weightloss-affordable-core' ) ),
				'pharmacy_statement' => array( 'label' => __( 'Pharmacy statement', 'weightloss-affordable-core' ), 'type' => 'textarea' ),
			),
		),
		'notifications' => array(
			'label'  => __( 'Form notifications', 'weightloss-affordable-core' ),
			'fields' => array(
				'notify_enabled'      => array( 'label' => __( 'Email new enquiries to the site admin', 'weightloss-affordable-core' ), 'type' => 'select', 'options' => array( '1' => __( 'Yes', 'weightloss-affordable-core' ), '0' => __( 'No', 'weightloss-affordable-core' ) ) ),
				'notification_emails' => array( 'label' => __( 'Notification recipients', 'weightloss-affordable-core' ), 'type' => 'text', 'help' => __( 'Comma-separated email addresses.', 'weightloss-affordable-core' ) ),
				'spam_min_seconds'    => array( 'label' => __( 'Minimum seconds before a form can be submitted', 'weightloss-affordable-core' ), 'type' => 'text', 'help' => __( 'Blocks fast automated submissions.', 'weightloss-affordable-core' ) ),
				'retention_days'      => array( 'label' => __( 'Enquiry retention (days)', 'weightloss-affordable-core' ), 'type' => 'text', 'help' => __( 'Older enquiries are deleted automatically. 0 disables automatic deletion.', 'weightloss-affordable-core' ) ),
				'form_disclaimer'     => array( 'label' => __( 'Form disclaimer', 'weightloss-affordable-core' ), 'type' => 'textarea' ),
			),
		),
		'compliance'   => array(
			'label'  => __( 'Clinical review & locale', 'weightloss-affordable-core' ),
			'fields' => array(
				'clinical_reviewer' => array( 'label' => __( 'Clinical reviewer (name & credentials)', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'last_reviewed'     => array( 'label' => __( 'Last clinical review date', 'weightloss-affordable-core' ), 'type' => 'text', 'help' => __( 'Shown on medical pages once entered.', 'weightloss-affordable-core' ) ),
				'currency_code'     => array( 'label' => __( 'Currency code', 'weightloss-affordable-core' ), 'type' => 'text' ),
				'currency_symbol'   => array( 'label' => __( 'Currency symbol', 'weightloss-affordable-core' ), 'type' => 'text' ),
			),
		),
	);
}

/**
 * Render the settings page.
 */
function wla_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = false;
	if ( isset( $_POST['wla_settings_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wla_settings_nonce'] ) ), 'wla_save_settings' ) ) {
		$raw  = isset( $_POST['wla_settings'] ) && is_array( $_POST['wla_settings'] ) ? wp_unslash( $_POST['wla_settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
		$data = array();
		foreach ( wla_settings_fields() as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				$value          = isset( $raw[ $key ] ) ? $raw[ $key ] : '';
				$data[ $key ]   = 'textarea' === $field['type'] ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
			}
		}
		update_option( 'wla_settings', $data );
		$saved = true;
	}

	$values = wla_get_settings();
	?>
	<div class="wrap wla-admin">
		<h1><?php esc_html_e( 'WeightLossAffordable settings', 'weightloss-affordable-core' ); ?></h1>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'weightloss-affordable-core' ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'wla_save_settings', 'wla_settings_nonce' ); ?>
			<?php foreach ( wla_settings_fields() as $group ) : ?>
				<h2><?php echo esc_html( $group['label'] ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $group['fields'] as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="wla-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( 'textarea' === $field['type'] ) : ?>
									<textarea id="wla-<?php echo esc_attr( $key ); ?>" name="wla_settings[<?php echo esc_attr( $key ); ?>]" rows="3" class="large-text"><?php echo esc_textarea( $values[ $key ] ?? '' ); ?></textarea>
								<?php elseif ( 'select' === $field['type'] ) : ?>
									<select id="wla-<?php echo esc_attr( $key ); ?>" name="wla_settings[<?php echo esc_attr( $key ); ?>]">
										<?php foreach ( $field['options'] as $opt_value => $opt_label ) : ?>
											<option value="<?php echo esc_attr( $opt_value ); ?>"<?php selected( (string) ( $values[ $key ] ?? '' ), (string) $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input id="wla-<?php echo esc_attr( $key ); ?>" type="text" name="wla_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $values[ $key ] ?? '' ); ?>" class="regular-text" />
								<?php endif; ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( __( 'Save settings', 'weightloss-affordable-core' ) ); ?>
		</form>
	</div>
	<?php
}
