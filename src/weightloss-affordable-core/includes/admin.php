<?php
/**
 * Admin hub: dashboard, environment checks and the demo importer UI.
 *
 * @package WeightLossAffordable_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the admin menu.
 */
function wla_register_admin_menu() {
	add_menu_page(
		__( 'WeightLossAffordable', 'weightloss-affordable-core' ),
		__( 'WeightLossAffordable', 'weightloss-affordable-core' ),
		'manage_options',
		'wla-dashboard',
		'wla_render_dashboard',
		'dashicons-heart',
		3
	);

	add_submenu_page(
		'wla-dashboard',
		__( 'Dashboard & demo import', 'weightloss-affordable-core' ),
		__( 'Dashboard & import', 'weightloss-affordable-core' ),
		'manage_options',
		'wla-dashboard',
		'wla_render_dashboard'
	);

	add_submenu_page(
		'wla-dashboard',
		__( 'Treatments', 'weightloss-affordable-core' ),
		__( 'Treatments', 'weightloss-affordable-core' ),
		'edit_posts',
		'edit.php?post_type=nrx_treatment'
	);

	add_submenu_page(
		'wla-dashboard',
		__( 'Enquiries', 'weightloss-affordable-core' ),
		__( 'Enquiries', 'weightloss-affordable-core' ),
		'edit_posts',
		'edit.php?post_type=wla_submission'
	);
}
add_action( 'admin_menu', 'wla_register_admin_menu', 9 );

/**
 * Environment checks shown on the dashboard.
 *
 * @return array
 */
function wla_environment_checks() {
	$theme      = wp_get_theme();
	$checks     = array();

	$checks[] = array(
		'label'  => __( 'Theme', 'weightloss-affordable-core' ),
		'value'  => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
		'status' => wla_is_theme_active() ? 'ok' : 'fail',
		'help'   => __( 'The WeightLossAffordable theme provides the section renderers.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'Elementor', 'weightloss-affordable-core' ),
		'value'  => did_action( 'elementor/loaded' ) ? ( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'active', 'weightloss-affordable-core' ) ) : __( 'not active', 'weightloss-affordable-core' ),
		'status' => did_action( 'elementor/loaded' ) ? 'ok' : 'warn',
		'help'   => __( 'Free Elementor is enough for visual editing. Elementor Pro is only needed for Theme Builder headers and footers.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'WordPress version', 'weightloss-affordable-core' ),
		'value'  => get_bloginfo( 'version' ),
		'status' => version_compare( get_bloginfo( 'version' ), '6.4', '>=' ) ? 'ok' : 'warn',
		'help'   => __( 'Supported from WordPress 6.4; tested to 6.7.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'PHP version', 'weightloss-affordable-core' ),
		'value'  => PHP_VERSION,
		'status' => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'ok' : 'fail',
		'help'   => __( 'PHP 7.4 or newer is required.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'Site language', 'weightloss-affordable-core' ),
		'value'  => get_locale(),
		'status' => 0 === strpos( get_locale(), 'en' ) ? 'ok' : 'warn',
		'help'   => __( 'This build ships English content. Switch the site language in Settings → General → Site Language.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'Permalinks', 'weightloss-affordable-core' ),
		'value'  => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'plain', 'weightloss-affordable-core' ),
		'status' => get_option( 'permalink_structure' ) ? 'ok' : 'warn',
		'help'   => __( '“Post name” permalinks are recommended for treatments and pages.', 'weightloss-affordable-core' ),
	);

	$front = (int) get_option( 'page_on_front' );
	$checks[] = array(
		'label'  => __( 'Static homepage', 'weightloss-affordable-core' ),
		'value'  => $front ? get_the_title( $front ) : __( 'not set', 'weightloss-affordable-core' ),
		'status' => ( 'page' === get_option( 'show_on_front' ) && $front ) ? 'ok' : 'warn',
		'help'   => __( 'The importer sets a static front page automatically.', 'weightloss-affordable-core' ),
	);

	$checks[] = array(
		'label'  => __( 'Email delivery', 'weightloss-affordable-core' ),
		'value'  => wla_get_settings( 'notification_emails' ),
		'status' => 'ok',
		'help'   => __( 'Enquiries are stored in WordPress regardless. If wp_mail() is not configured, install an SMTP plugin.', 'weightloss-affordable-core' ),
	);

	return $checks;
}

/**
 * Render the dashboard.
 */
function wla_render_dashboard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = get_transient( 'wla_demo_report_' . get_current_user_id() );
	if ( $report ) {
		delete_transient( 'wla_demo_report_' . get_current_user_id() );
	}

	$theme = wp_get_theme();
	?>
	<div class="wrap wla-admin">
		<h1><?php esc_html_e( 'WeightLossAffordable', 'weightloss-affordable-core' ); ?></h1>
		<p class="wla-admin__lead">
			<?php esc_html_e( 'This plugin owns the treatments, enquiry forms, SEO output and demo content. The theme owns the design. Both are needed for the full site.', 'weightloss-affordable-core' ); ?>
		</p>

		<?php if ( is_array( $report ) ) : ?>
			<div class="notice <?php echo ! empty( $report['fatal'] ) ? 'notice-error' : 'notice-success'; ?>">
				<p>
					<strong>
						<?php echo ! empty( $report['fatal'] ) ? esc_html__( 'Import stopped', 'weightloss-affordable-core' ) : esc_html__( 'Demo import complete', 'weightloss-affordable-core' ); ?>
					</strong>
				</p>
				<?php if ( ! empty( $report['summary'] ) ) : ?>
					<p><?php echo esc_html( $report['summary'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $report['warnings'] ) ) : ?>
					<ul class="wla-admin__warnings">
						<?php foreach ( $report['warnings'] as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $report['pages'] ) ) : ?>
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: page count */
								__( '%d pages processed.', 'weightloss-affordable-core' ),
								count( $report['pages'] )
							)
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="wla-admin__grid">
			<div class="wla-admin__card">
				<h2><?php esc_html_e( 'One-click demo import', 'weightloss-affordable-core' ); ?></h2>
				<p><?php esc_html_e( 'Creates the full English demo site: 19 pages, 2 treatment records, 3 articles, menus, Elementor layouts and the settings needed to publish.', 'weightloss-affordable-core' ); ?></p>
				<p class="wla-admin__note">
					<?php esc_html_e( 'Running it again updates the imported records instead of duplicating them. Your own pages, posts and treatments are never modified or deleted.', 'weightloss-affordable-core' ); ?>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wla-admin__import">
					<input type="hidden" name="action" value="wla_import_demo" />
					<?php wp_nonce_field( 'wla_import_demo', 'wla_import_nonce' ); ?>
					<label class="wla-admin__reset">
						<input type="checkbox" name="wla_reset" value="1" data-wla-reset />
						<?php esc_html_e( 'Reset: first delete records created by a previous import (your own content is kept).', 'weightloss-affordable-core' ); ?>
					</label>
					<?php submit_button( __( 'Import demo content', 'weightloss-affordable-core' ), 'primary large', 'submit', false ); ?>
				</form>
			</div>

			<div class="wla-admin__card">
				<h2><?php esc_html_e( 'Environment', 'weightloss-affordable-core' ); ?></h2>
				<table class="wla-admin__table">
					<tbody>
						<?php foreach ( wla_environment_checks() as $check ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $check['label'] ); ?></th>
								<td>
									<span class="wla-status wla-status--<?php echo esc_attr( $check['status'] ); ?>"></span>
									<?php echo esc_html( $check['value'] ); ?>
									<?php if ( ! empty( $check['help'] ) ) : ?>
										<p class="description"><?php echo esc_html( $check['help'] ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="wla-admin__card">
				<h2><?php esc_html_e( 'Next steps', 'weightloss-affordable-core' ); ?></h2>
				<ol class="wla-admin__steps">
					<li><?php esc_html_e( 'Import the demo content.', 'weightloss-affordable-core' ); ?></li>
					<li>
						<?php
						printf(
							/* translators: %s: settings link */
							esc_html__( 'Confirm your business details, prices and notification recipients in %s.', 'weightloss-affordable-core' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=wla-settings' ) ) . '">' . esc_html__( 'Settings', 'weightloss-affordable-core' ) . '</a>'
						);
						?>
					</li>
					<li><?php esc_html_e( 'Review the clinical and legal content with your clinician and counsel.', 'weightloss-affordable-core' ); ?></li>
					<li><?php esc_html_e( 'Test the contact form and confirm the notification email arrives.', 'weightloss-affordable-core' ); ?></li>
					<li><?php esc_html_e( 'Set Settings → General → Site Language to English if it is not already.', 'weightloss-affordable-core' ); ?></li>
				</ol>
				<p>
					<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=nrx_treatment' ) ); ?>"><?php esc_html_e( 'Manage treatments', 'weightloss-affordable-core' ); ?></a>
					<a class="button button-secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View site', 'weightloss-affordable-core' ); ?></a>
				</p>
			</div>

			<div class="wla-admin__card">
				<h2><?php esc_html_e( 'Where things live', 'weightloss-affordable-core' ); ?></h2>
				<ul class="wla-admin__list">
					<li><strong><?php esc_html_e( 'Pages', 'weightloss-affordable-core' ); ?></strong> — <?php esc_html_e( 'edit page content in Elementor, or in the “Page sections” box below the editor.', 'weightloss-affordable-core' ); ?></li>
					<li><strong><?php esc_html_e( 'Treatments', 'weightloss-affordable-core' ); ?></strong> — <?php esc_html_e( 'prices, medication facts, eligibility, safety, FAQs and variations.', 'weightloss-affordable-core' ); ?></li>
					<li><strong><?php esc_html_e( 'Enquiries', 'weightloss-affordable-core' ); ?></strong> — <?php esc_html_e( 'private submissions from the contact and consultation forms.', 'weightloss-affordable-core' ); ?></li>
					<li><strong><?php esc_html_e( 'Reviews', 'weightloss-affordable-core' ); ?></strong> — <?php esc_html_e( 'under Treatments → Reviews. Only publish consented, verified feedback.', 'weightloss-affordable-core' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Admin assets.
 *
 * @param string $hook Current admin page.
 * @return void
 */
function wla_admin_assets( $hook ) {
	$screen     = get_current_screen();
	$is_editor  = $screen && in_array( $screen->base, array( 'post' ), true );
	$is_ours    = false !== strpos( (string) $hook, 'wla-' );

	if ( ! $is_ours && ! $is_editor ) {
		return;
	}

	wp_enqueue_style( 'wla-admin', WLA_CORE_URL . 'assets/css/admin.css', array(), WLA_CORE_VERSION );

	if ( $is_editor ) {
		wp_enqueue_media();
		wp_enqueue_script( 'wla-admin', WLA_CORE_URL . 'assets/js/admin.js', array( 'jquery' ), WLA_CORE_VERSION, true );
	}

	if ( $is_ours ) {
		wp_enqueue_script( 'wla-admin', WLA_CORE_URL . 'assets/js/admin.js', array(), WLA_CORE_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'wla_admin_assets' );

/**
 * Ask for a review of the imported content once, after import.
 */
function wla_admin_import_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_option( 'wla_demo_imported_at' ) || get_option( 'wla_demo_notice_dismissed' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->base ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'WeightLossAffordable demo content is installed.', 'weightloss-affordable-core' ),
		esc_html__( 'Before publishing, confirm prices, service area and clinical content.', 'weightloss-affordable-core' ),
		esc_url( admin_url( 'admin.php?page=wla-settings' ) ),
		esc_html__( 'Open settings', 'weightloss-affordable-core' )
	);
}
add_action( 'admin_notices', 'wla_admin_import_notice' );
