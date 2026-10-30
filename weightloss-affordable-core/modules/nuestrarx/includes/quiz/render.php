<?php
/**
 * Front-end eligibility quiz renderer.
 *
 * Steps are server-rendered from the published (or previewed draft)
 * configuration so the content is accessible and degrades gracefully.
 * assets/js/quiz.js drives branching, validation and submission.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is an authorized quiz preview.
 *
 * @return bool
 */
function nrx_quiz_is_preview() {
	if ( empty( $_GET['nrx_quiz_preview'] ) ) {
		return false;
	}
	if ( ! is_user_logged_in() || ! nrx_can_edit_quiz() ) {
		return false;
	}
	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	return (bool) wp_verify_nonce( $nonce, 'nrx_quiz_preview' );
}

/**
 * Get the configuration used for rendering.
 *
 * @return array
 */
function nrx_quiz_render_config() {
	$config = nrx_quiz_is_preview() ? nrx_quiz_get( 'draft' ) : nrx_quiz_get( 'published' );
	return function_exists( 'nrx_prefer_english' ) ? nrx_prefer_english( $config ) : $config;
}

/**
 * Enqueue quiz assets on quiz pages.
 */
function nrx_quiz_enqueue_assets() {
	$needs = is_page_template( 'page-templates/intake.php' ) || is_singular( 'nrx_treatment' );
	if ( ! $needs && is_singular() ) {
		$post = get_post();
		$needs = $post && ( has_shortcode( $post->post_content, 'nrx_quiz' ) || has_shortcode( $post->post_content, 'wla_treatment_detail' ) || has_shortcode( $post->post_content, 'wla_get_quote' ) );
	}
	if ( ! $needs ) {
		return;
	}

	if ( ! wp_style_is( 'nuestrarx-main', 'registered' ) ) {
		wp_register_style( 'nuestrarx-main', false, array(), NRX_CORE_VERSION );
	}
	wp_enqueue_style( 'nrx-quiz', NRX_CORE_URL . 'assets/css/quiz.css', array( 'nuestrarx-main' ), NRX_CORE_VERSION );
	wp_enqueue_script( 'nrx-quiz', NRX_CORE_URL . 'assets/js/quiz.js', array(), NRX_CORE_VERSION, true );

	$config   = nrx_quiz_render_config();
	$settings = $config['settings'];
	wp_localize_script(
		'nrx-quiz',
		'NRX_QUIZ',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'nrx_quiz_submit' ),
			'preview'     => nrx_quiz_is_preview(),
			'minBmi'      => (float) ( $settings['min_bmi'] ?? 25 ),
			'unavailable' => array_values( nrx_unavailable_states() ),
			'whatsapp'    => nrx_whatsapp_url( 'Hola, tengo una pregunta sobre mi evaluación' ),
			'analytics'   => (int) nrx_setting( 'quiz_analytics_events', 0 ),
			'i18n'        => array(
				'required'      => __( 'Complete this field to continue.', 'nuestrarx-core' ),
				'choose'        => __( 'Choose an option to continue.', 'nuestrarx-core' ),
				'chooseMany'    => __( 'Select at least one option to continue.', 'nuestrarx-core' ),
				'consent'       => __( 'Check this box to continue.', 'nuestrarx-core' ),
				'phone'         => __( 'Enter a valid 10-digit phone number.', 'nuestrarx-core' ),
				'zip'           => __( 'Enter a 5-digit ZIP code.', 'nuestrarx-core' ),
				'date'          => __( 'Enter a valid date.', 'nuestrarx-core' ),
				'underage'      => __( 'You must be at least 18 years old.', 'nuestrarx-core' ),
				'bmiLow'        => __( 'Your BMI is below the minimum for this program. A clinician can still review your case.', 'nuestrarx-core' ),
				'bmiValue'      => __( 'Your estimated BMI', 'nuestrarx-core' ),
				'stateUnavailable' => __( 'This state is not available yet.', 'nuestrarx-core' ),
				'stateAvailable'   => __( 'Available in your state. Medication ships to your door if prescribed.', 'nuestrarx-core' ),
				'sending'       => __( 'Sending your enquiry…', 'nuestrarx-core' ),
				'network'       => __( 'We could not send your enquiry. Check your connection and try again.', 'nuestrarx-core' ),
				'step'          => __( 'Step', 'nuestrarx-core' ),
				'of'            => __( 'of', 'nuestrarx-core' ),
				'question'      => __( 'Question', 'nuestrarx-core' ),
				'back'          => __( 'Previous', 'nuestrarx-core' ),
				'next'          => __( 'Next', 'nuestrarx-core' ),
				'submit'        => __( 'Submit my enquiry', 'nuestrarx-core' ),
				'previewLabel'  => __( 'Draft preview', 'nuestrarx-core' ),
				'review'        => __( 'Please review the highlighted answers.', 'nuestrarx-core' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nrx_quiz_enqueue_assets', 20 );

/**
 * Render the quiz.
 *
 * @param array $args Optional args: show_intro.
 * @return string
 */
function nrx_quiz_render( $args = array() ) {
	$args   = wp_parse_args( $args, array( 'show_intro' => true ) );
	$config = nrx_quiz_render_config();
	$meta   = $config['meta'];
	$steps  = $config['steps'];
	$settings = $config['settings'];
	$min_bmi  = (float) ( $settings['min_bmi'] ?? 25 );
	$preview  = nrx_quiz_is_preview();
	$total_q  = 0;
	foreach ( $steps as $step ) {
		if ( ! empty( $step['question_number'] ) ) {
			$total_q++;
		}
	}

	$public_config = array(
		'steps'  => array_map(
			static function ( $step ) {
				unset( $step['flag'] );
				return $step;
			},
			$steps
		),
		'minBmi' => $min_bmi,
	);

	ob_start();
	?>
	<div class="nrx-quiz" id="nrx-quiz" data-nrx-quiz data-preview="<?php echo $preview ? '1' : '0'; ?>">
		<?php if ( $preview ) : ?>
			<div class="nrx-quiz-preview-banner" role="status"><?php echo nrx_icon( 'alert', 18 ); ?><span><?php esc_html_e( 'Vista previa del borrador. Los cambios no se han publicado.', 'nuestrarx-core' ); ?></span><a href="<?php echo esc_url( admin_url( 'admin.php?page=nrx-quiz' ) ); ?>"><?php esc_html_e( 'Volver al editor', 'nuestrarx-core' ); ?></a></div>
		<?php endif; ?>

		<?php if ( $args['show_intro'] ) : ?>
			<div class="nrx-quiz-intro">
				<p class="nrx-quiz-eyebrow"><?php echo nrx_icon( 'shield', 16 ); ?><span<?php echo nrx_bi_attr( $meta['intro_eyebrow_en'] ?? '' ); ?>><?php echo esc_html( $meta['intro_eyebrow'] ?? '' ); ?></span></p>
				<h1 class="nrx-quiz-title"<?php echo nrx_bi_attr( $meta['intro_title_en'] ?? '' ); ?>><?php echo esc_html( $meta['intro_title'] ?? '' ); ?></h1>
				<p class="nrx-quiz-lead"<?php echo nrx_bi_attr( $meta['intro_body_en'] ?? '' ); ?>><?php echo esc_html( $meta['intro_body'] ?? '' ); ?></p>
				<ul class="nrx-quiz-trust">
					<?php
					$trust    = (array) ( $meta['trust'] ?? array() );
					$trust_en = (array) ( $meta['trust_en'] ?? array() );
					foreach ( $trust as $i => $item ) :
						?>
						<li><?php echo nrx_icon( 'check', 14 ); ?><span<?php echo isset( $trust_en[ $i ] ) ? nrx_bi_attr( $trust_en[ $i ] ) : ''; ?>><?php echo esc_html( $item ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="nrx-quiz-shell">
			<div class="nrx-quiz-progress">
				<div class="nrx-quiz-progress-meta">
					<span data-nrx-phase><?php echo esc_html( $steps[0]['phase'] ?? '' ); ?></span>
					<span data-nrx-progress-count aria-live="polite"></span>
				</div>
					<div class="nrx-quiz-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="<?php esc_attr_e( 'Quiz progress', 'nuestrarx-core' ); ?>" data-nrx-progressbar>
					<span class="nrx-quiz-progress-bar" data-nrx-progressbar-fill></span>
				</div>
			</div>

			<noscript>
				<div class="nrx-quiz-noscript">
						<p><?php esc_html_e( 'This quiz needs JavaScript. You can also contact the clinic to complete it with help.', 'nuestrarx-core' ); ?></p>
					<?php if ( nrx_whatsapp_url() ) : ?>
						<a class="button button-primary" href="<?php echo esc_url( nrx_whatsapp_url( 'Hola, quiero completar mi evaluación' ) ); ?>">WhatsApp</a>
					<?php endif; ?>
				</div>
			</noscript>

			<form class="nrx-quiz-form" novalidate data-nrx-quiz-form>
				<?php
				$selected_treatment = isset( $_GET['treatment'] ) ? sanitize_title( wp_unslash( $_GET['treatment'] ) ) : '';
				$selected_plan      = isset( $_GET['plan'] ) ? sanitize_title( wp_unslash( $_GET['plan'] ) ) : '';
				if ( $selected_treatment || $selected_plan ) :
					?>
					<p class="nrx-quiz-context">
						<?php
						echo esc_html(
							trim(
								sprintf(
									/* translators: 1: treatment slug, 2: plan slug */
									__( 'Continuing from %1$s%2$s. Complete your details to submit this quote request.', 'nuestrarx-core' ),
									$selected_treatment ? $selected_treatment : __( 'your selected treatment', 'nuestrarx-core' ),
									$selected_plan ? ' · ' . $selected_plan : ''
								)
							)
						);
						?>
					</p>
					<input type="hidden" name="selected_treatment" value="<?php echo esc_attr( $selected_treatment ); ?>" data-nrx-selected-treatment>
					<input type="hidden" name="selected_plan" value="<?php echo esc_attr( $selected_plan ); ?>" data-nrx-selected-plan>
				<?php endif; ?>
				<?php
				$step_index = 0;
				foreach ( $steps as $step ) :
					$is_start_visible = nrx_quiz_step_visible( $step, array() );
					nrx_quiz_render_step( $step, $step_index, $min_bmi, $is_start_visible );
					$step_index++;
				endforeach;
				?>
				<div class="nrx-quiz-step-error" role="alert" data-nrx-error hidden></div>
				<div class="nrx-quiz-actions" data-nrx-actions>
					<button type="button" class="nrx-quiz-back" data-nrx-back hidden>
						<?php echo nrx_icon( 'arrow', 16 ); ?><span><?php esc_html_e( 'Back', 'nuestrarx-core' ); ?></span>
					</button>
					<div class="nrx-quiz-actions-end">
						<button type="button" class="button button-primary nrx-quiz-next" data-nrx-next>
							<span data-nrx-next-label><?php echo esc_html( $steps[0]['type'] ?? '' ) === 'consent' ? esc_html__( 'Submit my enquiry', 'nuestrarx-core' ) : esc_html__( 'Continue', 'nuestrarx-core' ); ?></span>
							<span class="button-arrow"><?php echo nrx_icon( 'arrow', 18 ); ?></span>
						</button>
					</div>
				</div>
				<p class="nrx-quiz-footnote">
					<?php echo nrx_icon( 'lock', 14 ); ?>
					<span><?php esc_html_e( 'Your answers are sent securely and reviewed only by the clinical team. They are not used for advertising.', 'nuestrarx-core' ); ?></span>
				</p>
			</form>
		</div>

		<div class="nrx-quiz-result" data-nrx-result hidden aria-live="polite"></div>

		<script type="application/json" data-nrx-quiz-config><?php echo wp_json_encode( $public_config ); ?></script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render a single step.
 *
 * @param array $step      Step config.
 * @param int   $index     Index.
 * @param float $min_bmi   Minimum BMI.
 * @param bool  $visible   Visible on first render.
 */
function nrx_quiz_render_step( $step, $index, $min_bmi, $visible ) {
	$type = $step['type'] ?? 'radio';
	$id   = sanitize_key( $step['id'] ?? 'step-' . $index );
	$field = sanitize_key( $step['field'] ?? $id );
	$help = str_replace( '{min_bmi}', (string) $min_bmi, (string) ( $step['help'] ?? '' ) );
	$help_en = str_replace( '{min_bmi}', (string) $min_bmi, (string) ( $step['help_en'] ?? '' ) );
	$qnum = (int) ( $step['question_number'] ?? 0 );
	?>
	<fieldset class="nrx-step nrx-step-<?php echo esc_attr( $type ); ?>" data-nrx-step="<?php echo esc_attr( $id ); ?>" data-type="<?php echo esc_attr( $type ); ?>" data-index="<?php echo esc_attr( $index ); ?>" data-question="<?php echo esc_attr( $qnum ); ?>" data-field="<?php echo esc_attr( $field ); ?>" data-required="<?php echo ! empty( $step['required'] ) ? '1' : '0'; ?>" <?php echo $visible ? '' : 'hidden'; ?>>
		<legend class="nrx-step-legend">
			<span class="nrx-step-phase"<?php echo nrx_bi_attr( $step['phase_en'] ?? '' ); ?>><?php echo esc_html( $step['phase'] ?? '' ); ?></span>
			<?php if ( $qnum ) : ?>
				<span class="nrx-step-qnum" data-nrx-qnum-label><?php echo esc_html( sprintf( __( 'Pregunta %d', 'nuestrarx-core' ), $qnum ) ); ?></span>
			<?php endif; ?>
			<span class="nrx-step-title"<?php echo nrx_bi_attr( $step['title_en'] ?? '' ); ?>><?php echo esc_html( $step['title'] ?? '' ); ?></span>
			<?php if ( $help ) : ?>
				<span class="nrx-step-help"<?php echo nrx_bi_attr( $help_en ); ?>><?php echo esc_html( $help ); ?></span>
			<?php endif; ?>
		</legend>

		<div class="nrx-step-body">
			<?php
			switch ( $type ) {
				case 'radio':
				case 'checkboxes':
					nrx_quiz_render_choices( $step, $field, 'checkboxes' === $type );
					break;

				case 'select':
					nrx_quiz_render_select( $step, $field );
					break;

				case 'state':
					nrx_quiz_render_select( $step, 'state' );
					break;

				case 'text':
				case 'textarea':
				case 'number':
				case 'date':
					nrx_quiz_render_input( $step, $field );
					break;

				case 'measurements':
					nrx_quiz_render_measurements( $step, $min_bmi );
					break;

				case 'fields':
					nrx_quiz_render_composite( $step );
					break;

				case 'consent':
					nrx_quiz_render_consent( $step );
					break;

				case 'info':
					echo '<div class="nrx-step-info">' . wp_kses_post( $step['body'] ?? '' ) . '</div>';
					break;
			}
			?>
		</div>
	</fieldset>
	<?php
}

/**
 * Radio / checkbox options.
 *
 * @param array  $step       Step.
 * @param string $field      Field key.
 * @param bool   $multiple   Checkboxes.
 */
function nrx_quiz_render_choices( $step, $field, $multiple ) {
	$options = (array) ( $step['options'] ?? array() );
	$id      = sanitize_key( $step['id'] ?? $field );
	echo '<div class="' . ( $multiple ? 'nrx-choice-grid nrx-choice-grid-multi' : 'nrx-choice-grid' ) . '" role="group" aria-labelledby="nrx-label-' . esc_attr( $id ) . '">';
	$i = 0;
	foreach ( $options as $option ) {
		$i++;
		$value    = sanitize_text_field( $option['value'] ?? '' );
		$label    = $option['label'] ?? $value;
		$input_id = 'nrx_' . $id . '_' . $i;
		$exclusive = ! empty( $option['exclusive'] );
		?>
		<label class="nrx-choice<?php echo $exclusive ? ' nrx-choice-exclusive' : ''; ?>" for="<?php echo esc_attr( $input_id ); ?>">
			<input
				type="<?php echo $multiple ? 'checkbox' : 'radio'; ?>"
				id="<?php echo esc_attr( $input_id ); ?>"
				name="answers[<?php echo esc_attr( $field ); ?>]<?php echo $multiple ? '[]' : ''; ?>"
				value="<?php echo esc_attr( $value ); ?>"
				data-exclusive="<?php echo $exclusive ? '1' : '0'; ?>"
				<?php echo ! empty( $step['required'] ) && ! $multiple ? 'required' : ''; ?>
			>
			<span class="nrx-choice-inner">
				<?php if ( ! empty( $option['emoji'] ) ) : ?>
					<span class="nrx-choice-emoji" aria-hidden="true"><?php echo esc_html( $option['emoji'] ); ?></span>
				<?php endif; ?>
				<span class="nrx-choice-copy">
					<span class="nrx-choice-label"<?php echo nrx_bi_attr( $option['label_en'] ?? '' ); ?>><?php echo esc_html( $label ); ?></span>
					<?php if ( ! empty( $option['note'] ) ) : ?>
						<span class="nrx-choice-note"<?php echo nrx_bi_attr( $option['note_en'] ?? '' ); ?>><?php echo esc_html( $option['note'] ); ?></span>
					<?php endif; ?>
				</span>
				<span class="nrx-choice-mark" aria-hidden="true"></span>
			</span>
		</label>
		<?php
	}
	echo '</div>';
	if ( 'state' !== ( $step['type'] ?? '' ) ) {
		echo '<p class="nrx-field-error" data-error-for="' . esc_attr( $field ) . '" hidden></p>';
	}
}

/**
 * Select field.
 *
 * @param array  $step  Step.
 * @param string $field Field key.
 */
function nrx_quiz_render_select( $step, $field ) {
	$options = (array) ( $step['options'] ?? array() );
	$id      = 'nrx_' . sanitize_key( $step['id'] ?? $field );
	?>
	<div class="nrx-field">
		<label class="nrx-field-label" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Selecciona una opción', 'nuestrarx-core' ); ?></label>
		<select id="<?php echo esc_attr( $id ); ?>" name="answers[<?php echo esc_attr( $field ); ?>]" class="nrx-select" autocomplete="<?php echo 'state' === ( $step['type'] ?? '' ) ? 'address-level1' : 'off'; ?>" <?php echo ! empty( $step['required'] ) ? 'required' : ''; ?>>
			<option value=""><?php esc_html_e( 'Selecciona…', 'nuestrarx-core' ); ?></option>
			<?php foreach ( $options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ?? '' ); ?>"<?php echo nrx_bi_attr( $option['label_en'] ?? '' ); ?>><?php echo esc_html( $option['label'] ?? '' ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="nrx-state-note" data-nrx-state-note hidden></p>
		<p class="nrx-field-error" data-error-for="<?php echo esc_attr( $field ); ?>" hidden></p>
	</div>
	<?php
}

/**
 * Single input.
 *
 * @param array  $step  Step.
 * @param string $field Field key.
 */
function nrx_quiz_render_input( $step, $field ) {
	$type = $step['type'] ?? 'text';
	$id   = 'nrx_' . sanitize_key( $step['id'] ?? $field );
	$input_type = in_array( $type, array( 'date', 'number' ), true ) ? $type : 'text';
	if ( 'text' === $type && ! empty( $step['validation']['pattern'] ) ) {
		$input_type = 'text';
	}
	echo '<div class="nrx-field">';
	if ( 'textarea' === $type ) {
		echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $step['field_label'] ?? $step['title'] ) . '</label>';
		echo '<textarea id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $field ) . ']" rows="4" class="nrx-textarea" placeholder="' . esc_attr( $step['placeholder'] ?? '' ) . '"></textarea>';
	} else {
		echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $step['field_label'] ?? $step['title'] ) . '</label>';
		echo '<input type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $field ) . ']" class="nrx-input" value="" placeholder="' . esc_attr( $step['placeholder'] ?? '' ) . '" autocomplete="' . esc_attr( $step['autocomplete'] ?? 'off' ) . '"' . ( isset( $step['maxlength'] ) ? ' maxlength="' . esc_attr( $step['maxlength'] ) . '"' : '' ) . '>';
	}
	echo '<p class="nrx-field-error" data-error-for="' . esc_attr( $field ) . '" hidden></p></div>';
}

/**
 * Height / weight / BMI.
 *
 * @param array $step    Step.
 * @param float $min_bmi Minimum BMI.
 */
function nrx_quiz_render_measurements( $step, $min_bmi ) {
	$id = 'nrx_' . sanitize_key( $step['id'] ?? 'measurements' );
	?>
	<div class="nrx-measure-grid">
		<div class="nrx-field">
			<label class="nrx-field-label" for="<?php echo esc_attr( $id ); ?>_ft"><?php esc_html_e( 'Estatura', 'nuestrarx-core' ); ?></label>
			<div class="nrx-height-row">
				<span class="nrx-input-suffix">
					<input type="number" id="<?php echo esc_attr( $id ); ?>_ft" name="answers[height_ft]" min="3" max="8" step="1" inputmode="numeric" aria-label="<?php esc_attr_e( 'Pies', 'nuestrarx-core' ); ?>" placeholder="5">
					<em><?php esc_html_e( 'pies', 'nuestrarx-core' ); ?></em>
				</span>
				<span class="nrx-input-suffix">
					<input type="number" id="<?php echo esc_attr( $id ); ?>_in" name="answers[height_in]" min="0" max="11" step="1" inputmode="numeric" aria-label="<?php esc_attr_e( 'Pulgadas', 'nuestrarx-core' ); ?>" placeholder="6">
					<em><?php esc_html_e( 'pulg.', 'nuestrarx-core' ); ?></em>
				</span>
			</div>
		</div>
		<div class="nrx-field">
			<label class="nrx-field-label" for="<?php echo esc_attr( $id ); ?>_lb"><?php esc_html_e( 'Peso actual', 'nuestrarx-core' ); ?></label>
			<span class="nrx-input-suffix">
				<input type="number" id="<?php echo esc_attr( $id ); ?>_lb" name="answers[weight_lb]" min="70" max="700" step="0.1" inputmode="decimal" placeholder="180">
				<em><?php esc_html_e( 'lbs', 'nuestrarx-core' ); ?></em>
			</span>
		</div>
	</div>
	<div class="nrx-bmi-card" data-nrx-bmi-card>
		<span class="nrx-bmi-label"><?php esc_html_e( 'Tu IMC estimado', 'nuestrarx-core' ); ?></span>
		<strong class="nrx-bmi-value" data-nrx-bmi>—</strong>
		<span class="nrx-bmi-status" data-nrx-bmi-status></span>
		<input type="hidden" name="answers[bmi]" value="" data-nrx-bmi-input>
	</div>
	<p class="nrx-field-help"><?php echo esc_html( sprintf( __( 'El tratamiento se considera a partir de un IMC de %s. La decisión final es del médico.', 'nuestrarx-core' ), number_format_i18n( $min_bmi, 1 ) ) ); ?></p>
	<p class="nrx-field-error" data-error-for="height_ft" hidden></p>
	<?php
}

/**
 * Composite form step.
 *
 * @param array $step Step.
 */
function nrx_quiz_render_composite( $step ) {
	$fields = (array) ( $step['fields'] ?? array() );
	echo '<div class="nrx-composite">';
	foreach ( $fields as $sub ) {
		$key   = sanitize_key( $sub['key'] ?? '' );
		$type  = $sub['type'] ?? 'text';
		$id    = 'nrx_' . sanitize_key( $step['id'] ?? 'step' ) . '_' . $key;
		$label = $sub['label'] ?? $key;
		$req   = ! empty( $sub['required'] );
		echo '<div class="nrx-field">';
		if ( 'state' === $type ) {
			echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $req ? ' <span class="nrx-req">*</span>' : '' ) . '</label>';
			echo '<select id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $key ) . ']" class="nrx-select" autocomplete="address-level1" data-nrx-state ' . ( $req ? 'required' : '' ) . '>';
			echo '<option value="">' . esc_html__( 'Select your state…', 'nuestrarx-core' ) . '</option>';
			foreach ( nrx_states() as $code => $names ) {
				$state_label = nrx_is_spanish() ? $names[0] : $names[1];
				echo '<option value="' . esc_attr( $code ) . '"' . nrx_bi_attr( $names[1] ) . '>' . esc_html( $state_label ) . '</option>';
			}
			echo '</select>';
			echo '<p class="nrx-state-note" data-nrx-state-note hidden></p>';
		} elseif ( 'textarea' === $type ) {
			echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $req ? ' <span class="nrx-req">*</span>' : '' ) . '</label>';
			echo '<textarea id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $key ) . ']" rows="3" class="nrx-textarea" placeholder="' . esc_attr( $sub['placeholder'] ?? '' ) . '"' . nrx_bi_attr( $sub['placeholder_en'] ?? '', 'placeholder' ) . '></textarea>';
		} elseif ( 'select' === $type ) {
			echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			echo '<select id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $key ) . ']" class="nrx-select"><option value="">' . esc_html__( 'Select…', 'nuestrarx-core' ) . '</option>';
			foreach ( (array) ( $sub['options'] ?? array() ) as $opt ) {
				echo '<option value="' . esc_attr( $opt['value'] ?? '' ) . '">' . esc_html( $opt['label'] ?? '' ) . '</option>';
			}
			echo '</select>';
		} else {
			$input_type = in_array( $type, array( 'email', 'tel', 'number', 'date', 'url' ), true ) ? $type : 'text';
			echo '<label class="nrx-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $req ? ' <span class="nrx-req">*</span>' : '' ) . '</label>';
			echo '<input type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $id ) . '" name="answers[' . esc_attr( $key ) . ']" class="nrx-input" placeholder="' . esc_attr( $sub['placeholder'] ?? '' ) . '"' . nrx_bi_attr( $sub['placeholder_en'] ?? '', 'placeholder' ) . ' autocomplete="' . esc_attr( $sub['autocomplete'] ?? 'off' ) . '"' . ( ! empty( $sub['maxlength'] ) ? ' maxlength="' . esc_attr( $sub['maxlength'] ) . '"' : '' ) . ( ! empty( $sub['pattern'] ) ? ' data-pattern="' . esc_attr( $sub['pattern'] ) . '"' : '' ) . '>';
		}
		$help_text = nrx_is_spanish() ? ( $sub['help'] ?? '' ) : ( $sub['help_en'] ?? $sub['help'] ?? '' );
		if ( $help_text ) {
			echo '<p class="nrx-field-help">' . esc_html( $help_text ) . '</p>';
		}
		echo '<p class="nrx-field-error" data-error-for="' . esc_attr( $key ) . '" hidden></p>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Consent step.
 *
 * @param array $step Step.
 */
function nrx_quiz_render_consent( $step ) {
	$items      = (array) ( $step['items'] ?? array() );
	$checkboxes = (array) ( $step['checkboxes'] ?? array() );
	// WhatsApp fallback for "I need more information".
	$secondary = $step['secondary_url'] ?? '';
	if ( empty( $secondary ) ) {
		$secondary = nrx_whatsapp_url( 'Hola, necesito más información antes de decidir' );
	}
	?>
	<div class="nrx-consent">
		<ul class="nrx-consent-list">
			<?php foreach ( $items as $item ) : ?>
				<li><?php echo wp_kses_post( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="nrx-consent-checks">
			<?php foreach ( $checkboxes as $i => $box ) :
				$key = sanitize_key( $box['key'] ?? 'consent_' . $i );
				$id  = 'nrx_consent_' . $key;
				?>
				<label class="nrx-consent-check" for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="answers[<?php echo esc_attr( $key ); ?>]" value="1" data-consent <?php echo ! empty( $box['required'] ) ? 'data-required="1"' : ''; ?>>
					<span<?php echo nrx_bi_attr( $box['label_en'] ?? '' ); ?>><?php echo esc_html( $box['label'] ?? '' ); ?></span>
				</label>
				<p class="nrx-field-error" data-error-for="<?php echo esc_attr( $key ); ?>" hidden></p>
			<?php endforeach; ?>
		</div>
		<div class="nrx-consent-links">
			<?php if ( $secondary ) : ?>
				<a href="<?php echo esc_url( $secondary ); ?>" class="nrx-text-link"<?php echo nrx_bi_attr( $step['secondary_label_en'] ?? '' ); ?>><?php echo esc_html( $step['secondary_label'] ?? __( 'Necesito más información antes de decidir', 'nuestrarx-core' ) ); ?></a>
			<?php endif; ?>
			<button type="button" class="nrx-decline" data-nrx-decline<?php echo nrx_bi_attr( $step['decline_label_en'] ?? '' ); ?>><?php echo esc_html( $step['decline_label'] ?? __( 'No deseo continuar', 'nuestrarx-core' ) ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Shortcode for embedding the quiz.
 *
 * @param array $atts Attributes.
 * @return string
 */
function nrx_quiz_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'intro' => '1' ), $atts, 'nrx_quiz' );
	return nrx_quiz_render( array( 'show_intro' => '1' === (string) $atts['intro'] ) );
}
add_shortcode( 'nrx_quiz', 'nrx_quiz_shortcode' );

/**
 * Admin bar shortcut for editors.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function nrx_quiz_admin_bar( $bar ) {
	if ( is_admin() || ! nrx_can_edit_quiz() ) {
		return;
	}
	$bar->add_node(
		array(
			'id'    => 'nrx-quiz-edit',
			'title' => __( 'Editar evaluación', 'nuestrarx-core' ),
			'href'  => admin_url( 'admin.php?page=nrx-quiz' ),
		)
	);
}
add_action( 'admin_bar_menu', 'nrx_quiz_admin_bar', 80 );
