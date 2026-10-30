<?php
/**
 * Eligibility quiz configuration, validation and evaluation.
 *
 * The quiz is a draft/published document. Administrators edit a draft,
 * validate it, test it, and only then publish. The seeded configuration
 * reproduces the live NuestraRx evaluation
 * (nuestrarx.com/evaluacion): 9 numbered questions plus the clinical
 * safety screens, consent and outcomes.
 *
 * Clinical copy is deliberately conservative: the public result states that
 * initial criteria were met and that a licensed clinician reviews the case.
 * It never claims that a patient has been approved.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supported step types.
 *
 * @return array
 */
function nrx_quiz_step_types() {
	return array(
		'radio'        => __( 'Single choice', 'nuestrarx-core' ),
		'checkboxes'   => __( 'Multiple choice', 'nuestrarx-core' ),
		'select'       => __( 'Dropdown', 'nuestrarx-core' ),
		'state'        => __( 'U.S. state', 'nuestrarx-core' ),
		'text'         => __( 'Short text', 'nuestrarx-core' ),
		'textarea'     => __( 'Long text', 'nuestrarx-core' ),
		'number'       => __( 'Number', 'nuestrarx-core' ),
		'date'         => __( 'Date', 'nuestrarx-core' ),
		'measurements' => __( 'Height and weight (BMI)', 'nuestrarx-core' ),
		'fields'       => __( 'Multiple fields (form)', 'nuestrarx-core' ),
		'consent'      => __( 'Consent', 'nuestrarx-core' ),
		'info'         => __( 'Information screen', 'nuestrarx-core' ),
	);
}

/**
 * Condition operators.
 *
 * @return array
 */
function nrx_quiz_operators() {
	return array(
		'equals'       => __( 'es igual a', 'nuestrarx-core' ),
		'not_equals'   => __( 'no es igual a', 'nuestrarx-core' ),
		'in'           => __( 'está entre', 'nuestrarx-core' ),
		'not_in'       => __( 'no está entre', 'nuestrarx-core' ),
		'includes'     => __( 'incluye', 'nuestrarx-core' ),
		'not_includes' => __( 'no incluye', 'nuestrarx-core' ),
		'gt'           => __( 'es mayor que', 'nuestrarx-core' ),
		'gte'          => __( 'es mayor o igual que', 'nuestrarx-core' ),
		'lt'           => __( 'es menor que', 'nuestrarx-core' ),
		'lte'          => __( 'es menor o igual que', 'nuestrarx-core' ),
		'is_set'       => __( 'tiene respuesta', 'nuestrarx-core' ),
		'is_empty'     => __( 'está vacío', 'nuestrarx-core' ),
	);
}

/**
 * Outcome keys and labels.
 *
 * @return array
 */
function nrx_quiz_outcome_labels() {
	return array(
		'qualified'           => __( 'Initial criteria met', 'nuestrarx-core' ),
		'review'              => __( 'Needs a direct medical consultation', 'nuestrarx-core' ),
		'ineligible_bmi'      => __( 'BMI below the minimum', 'nuestrarx-core' ),
		'ineligible_pregnancy' => __( 'Pregnancy or breastfeeding', 'nuestrarx-core' ),
		'ineligible_age'      => __( 'Under 18', 'nuestrarx-core' ),
		'unavailable_state'   => __( 'Not available in this state', 'nuestrarx-core' ),
		'declined'            => __( 'The visitor chose not to continue', 'nuestrarx-core' ),
	);
}

/**
 * Fresh configuration. Used by the installer and by "restore defaults".
 *
 * @return array
 */
function nrx_quiz_default_config() {
	$states = array();
	foreach ( nrx_states() as $code => $names ) {
		$states[] = array(
			'value'    => $code,
			'label'    => $names[0],
			'label_en' => $names[1],
		);
	}

	$common_conditions = array(
		array( 'value' => 't2d', 'label' => 'Diabetes tipo 2', 'label_en' => 'Type 2 diabetes', 'emoji' => '🩸', 'note' => 'GLP-1 es especialmente efectivo', 'note_en' => 'GLP-1 is especially effective', 'flag' => 'type_2_diabetes' ),
		array( 'value' => 'prediabetes', 'label' => 'Prediabetes', 'label_en' => 'Prediabetes', 'emoji' => '⚠️' ),
		array( 'value' => 'hypertension', 'label' => 'Presión arterial alta', 'label_en' => 'High blood pressure', 'emoji' => '💓' ),
		array( 'value' => 'cholesterol', 'label' => 'Colesterol alto', 'label_en' => 'High cholesterol', 'emoji' => '🔬' ),
		array( 'value' => 'none', 'label' => 'Ninguna de las anteriores', 'label_en' => 'None of the above', 'emoji' => '✅', 'exclusive' => true ),
	);

	$medical_history = array(
		array( 'value' => 'gastroparesis', 'label' => 'Gastroparesia (vaciamiento gástrico lento)', 'label_en' => 'Gastroparesis (slow gastric emptying)', 'flag' => 'gastroparesis' ),
		array( 'value' => 'pancreatitis', 'label' => 'Pancreatitis', 'label_en' => 'Pancreatitis', 'flag' => 'pancreatitis' ),
		array( 'value' => 'ibd', 'label' => 'Enfermedad inflamatoria intestinal (colitis ulcerosa o Crohn)', 'label_en' => 'Inflammatory bowel disease (ulcerative colitis or Crohn\'s)', 'flag' => 'ibd' ),
		array( 'value' => 'endocrine_cancer', 'label' => 'Cáncer endocrino', 'label_en' => 'Endocrine cancer', 'flag' => 'endocrine_cancer' ),
		array( 'value' => 't1d', 'label' => 'Diabetes tipo 1', 'label_en' => 'Type 1 diabetes', 'flag' => 'type_1_diabetes' ),
		array( 'value' => 't2d', 'label' => 'Diabetes tipo 2', 'label_en' => 'Type 2 diabetes', 'flag' => 'type_2_diabetes' ),
		array( 'value' => 'gerd', 'label' => 'Reflujo gastroesofágico severo', 'label_en' => 'Severe GERD', 'flag' => 'severe_gerd' ),
		array( 'value' => 'men2', 'label' => 'Neoplasia endocrina múltiple tipo 2 (MEN 2)', 'label_en' => 'Multiple endocrine neoplasia type 2 (MEN 2)', 'flag' => 'men2' ),
		array( 'value' => 'mtc_history', 'label' => 'Historial personal o familiar de cáncer medular de tiroides', 'label_en' => 'Personal or family history of medullary thyroid cancer', 'flag' => 'mtc_history' ),
		array( 'value' => 'retinopathy', 'label' => 'Retinopatía diabética', 'label_en' => 'Diabetic retinopathy', 'flag' => 'retinopathy' ),
		array( 'value' => 'pregnancy', 'label' => 'Embarazo o lactancia', 'label_en' => 'Pregnancy or breastfeeding', 'flag' => 'pregnancy_lactation' ),
		array( 'value' => 'bariatric_recent', 'label' => 'Cirugía bariátrica en los últimos 12 meses', 'label_en' => 'Bariatric surgery in the last 12 months', 'flag' => 'bariatric_recent' ),
		array( 'value' => 'hypoglycemia', 'label' => 'Hipoglucemia (azúcar baja en sangre)', 'label_en' => 'Hypoglycemia (low blood sugar)', 'flag' => 'hypoglycemia' ),
		array( 'value' => 'kidney', 'label' => 'Enfermedad renal severa', 'label_en' => 'Severe kidney disease', 'flag' => 'severe_kidney' ),
		array( 'value' => 'liver', 'label' => 'Enfermedad hepática severa', 'label_en' => 'Severe liver disease', 'flag' => 'severe_liver' ),
		array( 'value' => 'none', 'label' => 'Ninguna de las anteriores', 'label_en' => 'None of the above', 'exclusive' => true ),
	);

	$glp1_allergies = array(
		array( 'value' => 'semaglutide', 'label' => 'Semaglutida (Ozempic, Wegovy, Rybelsus)', 'label_en' => 'Semaglutide (Ozempic, Wegovy, Rybelsus)', 'flag' => 'allergy_semaglutide' ),
		array( 'value' => 'tirzepatide', 'label' => 'Tirzepatida (Mounjaro, Zepbound)', 'label_en' => 'Tirzepatide (Mounjaro, Zepbound)', 'flag' => 'allergy_tirzepatide' ),
		array( 'value' => 'liraglutide', 'label' => 'Liraglutida (Saxenda, Victoza)', 'label_en' => 'Liraglutide (Saxenda, Victoza)' ),
		array( 'value' => 'dulaglutide', 'label' => 'Dulaglutida (Trulicity)', 'label_en' => 'Dulaglutide (Trulicity)' ),
		array( 'value' => 'exenatide', 'label' => 'Exenatida (Byetta, Bydureon)', 'label_en' => 'Exenatide (Byetta, Bydureon)' ),
		array( 'value' => 'other', 'label' => 'Otro medicamento GLP-1', 'label_en' => 'Another GLP-1 medication' ),
		array( 'value' => 'none', 'label' => 'Ninguno de los anteriores', 'label_en' => 'None of the above', 'exclusive' => true ),
	);

	$steps = array(

		array(
			'id'              => 'sex',
			'type'            => 'radio',
			'phase'           => 'Perfil',
			'phase_en'        => 'Profile',
			'question_number' => 1,
			'title'           => '¿Cuál es tu sexo biológico?',
			'title_en'        => 'What is your biological sex?',
			'help'            => 'Tu médico usa esta información para personalizar tu plan de tratamiento.',
			'help_en'         => 'Your clinician uses this information to personalize your treatment plan.',
			'field'           => 'sex',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'female', 'label' => 'Mujer', 'label_en' => 'Female', 'emoji' => '♀' ),
				array( 'value' => 'male', 'label' => 'Hombre', 'label_en' => 'Male', 'emoji' => '♂' ),
			),
		),

		array(
			'id'              => 'age',
			'type'            => 'radio',
			'phase'           => 'Perfil',
			'phase_en'        => 'Profile',
			'question_number' => 2,
			'title'           => '¿Cuántos años tienes?',
			'title_en'        => 'How old are you?',
			'help'            => 'El tratamiento GLP-1 está disponible para adultos de 18 años en adelante.',
			'help_en'         => 'GLP-1 treatment is available for adults 18 and older.',
			'field'           => 'age',
			'required'        => 1,
			'options'         => array(
				array( 'value' => '18-29', 'label' => '18 – 29 años', 'label_en' => '18 – 29 years', 'emoji' => '🌱' ),
				array( 'value' => '30-44', 'label' => '30 – 44 años', 'label_en' => '30 – 44 years', 'emoji' => '⚡', 'note' => 'El grupo más frecuente en nuestro programa', 'note_en' => 'The most common group in our program' ),
				array( 'value' => '45-59', 'label' => '45 – 59 años', 'label_en' => '45 – 59 years', 'emoji' => '🌟', 'note' => 'El segundo grupo más frecuente', 'note_en' => 'The second most common group' ),
				array( 'value' => '60+', 'label' => '60 años o más', 'label_en' => '60 years or older', 'emoji' => '🏅' ),
			),
		),

		array(
			'id'              => 'measurements',
			'type'            => 'measurements',
			'phase'           => 'Elegibilidad médica',
			'phase_en'        => 'Medical eligibility',
			'question_number' => 3,
			'title'           => '¿Cuánto mides y cuánto pesas?',
			'title_en'        => 'How tall are you and how much do you weigh?',
			'help'            => 'Calculamos tu IMC para verificar elegibilidad. Ofrecemos tratamiento a partir de un IMC de {min_bmi}. La decisión final es del médico.',
			'help_en'         => 'We calculate your BMI to check eligibility. Treatment is considered from a BMI of {min_bmi}. The final decision is your clinician\'s.',
			'field'           => 'measurements',
			'required'        => 1,
			'validation'      => array(
				'min_bmi' => 25,
				'max_weight' => 700,
				'min_weight' => 70,
			),
		),

		array(
			'id'              => 'contact',
			'type'            => 'fields',
			'phase'           => 'Elegibilidad médica',
			'phase_en'        => 'Medical eligibility',
			'question_number' => 4,
			'title'           => '¿A dónde te enviamos tu plan?',
			'title_en'        => 'Where should we send your plan?',
			'help'            => 'Usamos estos datos para contactarte sobre tu evaluación. Protegido bajo HIPAA.',
			'help_en'         => 'We use this information to contact you about your evaluation. Protected under HIPAA.',
			'field'           => 'contact',
			'required'        => 1,
			'fields'          => array(
				array( 'key' => 'first_name', 'type' => 'text', 'label' => 'Nombre', 'label_en' => 'First name', 'placeholder' => '', 'placeholder_en' => 'First name', 'required' => 1, 'autocomplete' => 'given-name' ),
				array( 'key' => 'email', 'type' => 'email', 'label' => 'Correo electrónico', 'label_en' => 'Email', 'placeholder' => 'name@email.com', 'placeholder_en' => 'name@email.com', 'required' => 1, 'autocomplete' => 'email' ),
				array( 'key' => 'phone', 'type' => 'tel', 'label' => 'Teléfono celular', 'label_en' => 'Mobile phone', 'placeholder' => '(555) 555-0123', 'required' => 1, 'autocomplete' => 'tel-national' ),
				array( 'key' => 'state_contact', 'type' => 'state', 'label' => 'Estado de residencia', 'label_en' => 'State of residence', 'required' => 1, 'autocomplete' => 'address-level1' ),
			),
		),

		array(
			'id'              => 'goal',
			'type'            => 'radio',
			'phase'           => 'Tu objetivo',
			'phase_en'        => 'Your goal',
			'question_number' => 5,
			'title'           => '¿Cuántas libras quieres perder?',
			'title_en'        => 'How many pounds do you want to lose?',
			'help'            => 'En promedio, los pacientes con GLP-1 pierden 15–20% de su peso corporal inicial. Los resultados individuales varían.',
			'help_en'         => 'On average, GLP-1 patients lose 15–20% of their starting body weight. Individual results vary.',
			'field'           => 'goal',
			'required'        => 1,
			'options'         => array(
				array( 'value' => '10-20', 'label' => '10 – 20 lbs', 'label_en' => '10 – 20 lbs', 'emoji' => '🎯' ),
				array( 'value' => '20-40', 'label' => '20 – 40 lbs', 'label_en' => '20 – 40 lbs', 'emoji' => '💪' ),
				array( 'value' => '40-60', 'label' => '40 – 60 lbs', 'label_en' => '40 – 60 lbs', 'emoji' => '🌟', 'note' => 'El objetivo más común de nuestros pacientes', 'note_en' => 'The most common goal among our patients' ),
				array( 'value' => '60+', 'label' => 'Más de 60 lbs', 'label_en' => 'More than 60 lbs', 'emoji' => '🏆' ),
			),
		),

		array(
			'id'              => 'conditions_common',
			'type'            => 'checkboxes',
			'phase'           => 'Historial médico',
			'phase_en'        => 'Medical history',
			'question_number' => 6,
			'title'           => '¿Tienes alguna de estas condiciones?',
			'title_en'        => 'Do you have any of these conditions?',
			'help'            => 'Información confidencial protegida por HIPAA. Ayuda al médico a encontrar el tratamiento más seguro.',
			'help_en'         => 'Confidential information protected under HIPAA. It helps the clinician find the safest treatment.',
			'field'           => 'conditions_common',
			'required'        => 1,
			'options'         => $common_conditions,
		),

		array(
			'id'              => 'pregnancy',
			'type'            => 'radio',
			'phase'           => 'Seguridad clínica',
			'phase_en'        => 'Clinical safety',
			'question_number' => 0,
			'title'           => '¿Estás embarazada, amamantando o planeas embarazarte?',
			'title_en'        => 'Are you pregnant, breastfeeding, or planning to become pregnant?',
			'help'            => 'Los GLP-1 no son seguros durante el embarazo o la lactancia. Esta pregunta es obligatoria por seguridad.',
			'help_en'         => 'GLP-1 medications are not safe during pregnancy or breastfeeding. This question is required for safety.',
			'field'           => 'pregnant',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'no', 'label' => 'No', 'label_en' => 'No', 'emoji' => '✅' ),
				array( 'value' => 'yes', 'label' => 'Sí, estoy embarazada / amamantando / planeando embarazarme', 'label_en' => 'Yes, I am pregnant / breastfeeding / planning to become pregnant', 'emoji' => '⛔', 'note' => 'No es seguro continuar el tratamiento en este momento.', 'note_en' => 'It is not safe to continue treatment at this time.', 'flag' => 'pregnancy_lactation' ),
			),
		),

		array(
			'id'              => 'medications',
			'type'            => 'fields',
			'phase'           => 'Tu salud actual',
			'phase_en'        => 'Your current health',
			'question_number' => 0,
			'title'           => 'Cuéntale a tu médico sobre ti',
			'title_en'        => 'Tell your clinician about you',
			'help'            => 'Esta información va directo al médico licenciado de Beluga Health. Está protegida bajo HIPAA y solo se usa para personalizar tu tratamiento.',
			'help_en'         => 'This information goes directly to a licensed Beluga Health clinician. It is protected under HIPAA and used only to personalize your treatment.',
			'field'           => 'medications',
			'required'        => 0,
			'fields'          => array(
				array( 'key' => 'current_medications', 'type' => 'textarea', 'label' => 'Medicamentos actuales (incluyendo dosis)', 'label_en' => 'Current medications (including doses)', 'placeholder' => 'No tomo medicamentos actualmente', 'placeholder_en' => 'I do not take any medications', 'required' => 0, 'sensitive' => 1 ),
				array( 'key' => 'allergies', 'type' => 'textarea', 'label' => 'Alergias conocidas', 'label_en' => 'Known allergies', 'placeholder' => 'No tengo alergias conocidas', 'placeholder_en' => 'I have no known allergies', 'required' => 0, 'sensitive' => 1 ),
			),
		),

		array(
			'id'              => 'medical_history',
			'type'            => 'checkboxes',
			'phase'           => 'Condiciones médicas',
			'phase_en'        => 'Medical conditions',
			'question_number' => 0,
			'title'           => '¿Tienes o has tenido alguna de estas condiciones?',
			'title_en'        => 'Do you have or have you had any of these conditions?',
			'help'            => 'Si ninguna aplica, selecciona “Ninguna de las anteriores”.',
			'help_en'         => 'If none apply, select “None of the above”.',
			'field'           => 'medical_history',
			'required'        => 1,
			'options'         => $medical_history,
		),

		array(
			'id'              => 'glp1_allergies',
			'type'            => 'checkboxes',
			'phase'           => 'Alergias a GLP-1',
			'phase_en'        => 'GLP-1 allergies',
			'question_number' => 0,
			'title'           => '¿Eres alérgico/a a alguno de estos medicamentos?',
			'title_en'        => 'Are you allergic to any of these medications?',
			'help'            => 'Si nunca los tomaste o no tienes alergia, selecciona “Ninguno de los anteriores”.',
			'help_en'         => 'If you never took them or have no allergy, select “None of the above”.',
			'field'           => 'glp1_allergies',
			'required'        => 1,
			'options'         => $glp1_allergies,
		),

		array(
			'id'              => 'prior_glp1',
			'type'            => 'checkboxes',
			'phase'           => 'Tratamientos previos',
			'phase_en'        => 'Previous treatments',
			'question_number' => 0,
			'title'           => '¿Tomaste algún GLP-1 en los últimos 2 meses?',
			'title_en'        => 'Did you take any GLP-1 in the last 2 months?',
			'help'            => 'Si nunca tomaste GLP-1, selecciona “Ninguno de estos”. Es lo más común.',
			'help_en'         => 'If you never took GLP-1, select “None of these”. That is the most common answer.',
			'field'           => 'prior_glp1',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'semaglutide', 'label' => 'Semaglutida (Ozempic, Wegovy, Rybelsus)', 'label_en' => 'Semaglutide (Ozempic, Wegovy, Rybelsus)' ),
				array( 'value' => 'tirzepatide', 'label' => 'Tirzepatida (Mounjaro, Zepbound)', 'label_en' => 'Tirzepatide (Mounjaro, Zepbound)' ),
				array( 'value' => 'none', 'label' => 'Ninguno de estos', 'label_en' => 'None of these', 'exclusive' => true ),
			),
		),

		array(
			'id'              => 'bariatric',
			'type'            => 'radio',
			'phase'           => 'Cirugías recientes',
			'phase_en'        => 'Recent surgery',
			'question_number' => 0,
			'title'           => '¿Te hicieron una cirugía de bypass gástrico en los últimos 6 meses?',
			'title_en'        => 'Did you have gastric bypass surgery in the last 6 months?',
			'help'            => 'Última verificación clínica antes de continuar.',
			'help_en'         => 'Final clinical check before continuing.',
			'field'           => 'bariatric_surgery',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'no', 'label' => 'No', 'label_en' => 'No', 'emoji' => '✅' ),
				array( 'value' => 'yes_recent', 'label' => 'Sí, en los últimos 6 meses', 'label_en' => 'Yes, in the last 6 months', 'emoji' => '⚠️', 'note' => 'Esto puede requerir consulta médica directa antes del tratamiento.', 'note_en' => 'This may require a direct medical consultation before treatment.', 'flag' => 'bariatric_surgery_recent' ),
			),
		),

		array(
			'id'              => 'thyroid',
			'type'            => 'radio',
			'phase'           => 'Seguridad',
			'phase_en'        => 'Safety',
			'question_number' => 7,
			'title'           => 'Pregunta de seguridad importante',
			'title_en'        => 'Important safety question',
			'help'            => 'Esta información ayuda al médico a confirmar que el GLP-1 sea seguro para ti.',
			'help_en'         => 'This information helps the clinician confirm that GLP-1 is safe for you.',
			'field'           => 'thyroid_history',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'no', 'label' => 'No tengo historial de cáncer de tiroides medular ni síndrome MEN2', 'label_en' => 'I have no history of medullary thyroid cancer or MEN2 syndrome', 'emoji' => '✅', 'note' => 'La mayoría de los pacientes no tienen estas condiciones', 'note_en' => 'Most patients do not have these conditions' ),
				array( 'value' => 'yes_unsure', 'label' => 'Sí tengo, o no estoy seguro/a', 'label_en' => 'Yes, I do, or I am not sure', 'emoji' => '⛔', 'note' => 'Vas a necesitar primero una consulta médica directa', 'note_en' => 'You will need a direct medical consultation first', 'flag' => 'mtc_risk' ),
			),
		),

		array(
			'id'              => 'history',
			'type'            => 'checkboxes',
			'phase'           => 'Tu historia',
			'phase_en'        => 'Your story',
			'question_number' => 8,
			'title'           => '¿Qué has intentado antes para bajar de peso?',
			'title_en'        => 'What have you tried before to lose weight?',
			'help'            => 'Si has intentado todo sin resultados, no es tu culpa. GLP-1 actúa directamente sobre las hormonas del hambre.',
			'help_en'         => 'If you have tried everything without results, it is not your fault. GLP-1 acts directly on hunger hormones.',
			'field'           => 'history',
			'required'        => 1,
			'options'         => array(
				array( 'value' => 'diets', 'label' => 'Dietas (keto, ayuno, etc.)', 'label_en' => 'Diets (keto, fasting, etc.)', 'emoji' => '🥗' ),
				array( 'value' => 'exercise', 'label' => 'Ejercicio intenso', 'label_en' => 'Intense exercise', 'emoji' => '🏃' ),
				array( 'value' => 'pills', 'label' => 'Pastillas o suplementos', 'label_en' => 'Pills or supplements', 'emoji' => '💊' ),
				array( 'value' => 'all_failed', 'label' => 'He probado todo sin éxito', 'label_en' => 'I have tried everything without success', 'emoji' => '😔', 'note' => 'El problema no eres tú, es la bioquímica', 'note_en' => 'The problem is not you, it is biochemistry' ),
				array( 'value' => 'first_time', 'label' => 'Es mi primera vez buscando ayuda', 'label_en' => 'This is my first time seeking help', 'emoji' => '🆕' ),
			),
		),

		array(
			'id'              => 'state',
			'type'            => 'state',
			'phase'           => 'Disponibilidad',
			'phase_en'        => 'Availability',
			'question_number' => 9,
			'title'           => '¿En qué estado de EE. UU. vives?',
			'title_en'        => 'Which U.S. state do you live in?',
			'help'            => 'Verificamos cobertura médica y disponibilidad de envío en tu estado.',
			'help_en'         => 'We verify clinical coverage and shipping availability in your state.',
			'field'           => 'state',
			'required'        => 1,
			'options'         => $states,
		),

		array(
			'id'              => 'identity',
			'type'            => 'fields',
			'phase'           => 'Datos de envío',
			'phase_en'        => 'Shipping details',
			'question_number' => 0,
			'title'           => '¿Tu fecha de nacimiento y código postal de envío?',
			'title_en'        => 'Your date of birth and shipping ZIP code?',
			'help'            => 'Información requerida por tu médico para verificar identidad y enviar tu tratamiento. Debes tener al menos 18 años. Información protegida bajo HIPAA.',
			'help_en'         => 'Information required by your clinician to verify identity and ship your treatment. You must be at least 18. Information protected under HIPAA.',
			'field'           => 'identity',
			'required'        => 1,
			'fields'          => array(
				array( 'key' => 'dob', 'type' => 'date', 'label' => 'Fecha de nacimiento', 'label_en' => 'Date of birth', 'required' => 1, 'autocomplete' => 'bday', 'sensitive' => 1 ),
				array( 'key' => 'zip', 'type' => 'text', 'label' => 'Código postal (5 dígitos)', 'label_en' => 'ZIP code (5 digits)', 'required' => 1, 'autocomplete' => 'postal-code', 'pattern' => '^[0-9]{5}$', 'maxlength' => 5, 'sensitive' => 1 ),
			),
		),

		array(
			'id'              => 'consent',
			'type'            => 'consent',
			'phase'           => 'Consentimiento informado',
			'phase_en'        => 'Informed consent',
			'question_number' => 0,
			'title'           => 'Consentimiento de tratamiento GLP-1',
			'title_en'        => 'GLP-1 treatment consent',
			'help'            => 'Por favor lee y reconoce lo siguiente antes de continuar:',
			'help_en'         => 'Please read and acknowledge the following before continuing:',
			// No container field: the answers come from the checkboxes below,
			// and reusing the `consent` key here collided with the checkbox.
			'field'           => '',
			'required'        => 1,
			'items'           => array(
				'Los medicamentos compuestos GLP-1 (semaglutida, tirzepatida) no están aprobados por la FDA y se preparan en farmacias 503A reguladas por estado.',
				'Los efectos secundarios pueden incluir náusea, vómito, diarrea, estreñimiento, dolor abdominal, fatiga, dolor de cabeza y en casos raros pancreatitis, problemas de vesícula o cambios en la visión.',
				'Estos medicamentos están contraindicados si tienes historial personal o familiar de cáncer medular de tiroides, síndrome MEN2, embarazo o lactancia.',
				'La pérdida de peso varía. Resultados promedio reportados: 15-20% del peso corporal en 12 meses combinado con cambios de estilo de vida.',
				'La receta es decisión única del médico con licencia de Beluga Health, con base en tu evaluación. La aprobación no está garantizada.',
				'Autorizas a NuestraRx y Beluga Health a procesar tu información médica bajo HIPAA y a contactarte por teléfono, SMS, email o WhatsApp sobre tu tratamiento.',
				'Puedes cancelar tu suscripción cuando quieras desde tu portal de paciente.',
			),
			'checkboxes'      => array(
				array( 'key' => 'injection_ok', 'label' => 'Confirmo que puedo aplicarme la inyección semanal en casa o que cuento con la ayuda de un adulto de confianza.', 'label_en' => 'I confirm that I can give myself the weekly injection at home or that I have a trusted adult who can help.', 'required' => 1 ),
				array( 'key' => 'consent', 'label' => 'He leído y entiendo. Deseo continuar y acepto recibir el tratamiento si soy aprobado/a por el médico.', 'label_en' => 'I have read and understand. I want to continue and accept treatment if I am approved by the clinician.', 'required' => 1 ),
			),
			'decline_label'   => 'No deseo continuar',
			'decline_label_en' => 'I do not want to continue',
			'secondary_label' => 'Necesito más información antes de decidir',
			'secondary_label_en' => 'I need more information before deciding',
			'secondary_url'   => '', // WhatsApp is filled at render time when empty.
		),
	);

	return array(
		'version'   => 1,
		'updated'   => current_time( 'mysql' ),
		'meta'      => array(
			'title'              => 'Evaluación médica',
			'title_en'           => 'Medical evaluation',
			'intro_eyebrow'      => 'Evaluación en línea · 5 minutos',
			'intro_eyebrow_en'   => 'Online evaluation · 5 minutes',
			'intro_title'        => 'Tu punto de partida',
			'intro_title_en'     => 'Your starting point',
			'intro_body'         => 'Responde unas preguntas para que un médico con licencia en tu estado sepa si el tratamiento GLP-1 puede ser apropiado para ti. No es una aprobación médica y no reemplaza una consulta.',
			'intro_body_en'      => 'Answer a few questions so a clinician licensed in your state can see whether GLP-1 treatment may be appropriate for you. This is not a medical approval and does not replace a consultation.',
			'estimated_minutes'  => 5,
			'trust'              => array(
				'Médicos con licencia en los 50 estados',
				'HIPAA',
				'WhatsApp en español',
				'Sin seguro requerido',
			),
			'trust_en'           => array(
				'Licensed clinicians in all 50 states',
				'HIPAA',
				'Spanish WhatsApp support',
				'No insurance required',
			),
		),
		'settings'  => array(
			'min_bmi'             => 25,
			'require_state'       => 1,
			'collect_phone'       => 1,
			'collect_email'       => 0,
			'store_clinical_answers' => 0,
			'notify_email'        => '',
			'webhook_url'         => '',
			'consent_version'     => '2026-10-26',
			'privacy_url'         => '/privacy-policy/',
			'hipaa_url'           => '/privacy-policy/',
			'safety_url'          => '/treatment-safety-information/',
			'outcomes'            => array(
				'qualified' => array(
					'eyebrow'    => 'Evaluación completa',
					'eyebrow_en' => 'Evaluation complete',
					'title'      => 'Cumples los criterios iniciales',
					'title_en'   => 'You meet the initial criteria',
					'body'       => 'Un médico con licencia revisará tu caso. Si el tratamiento es apropiado, recibirás tu receta. La aprobación no está garantizada y los resultados individuales varían.',
					'body_en'    => 'A licensed clinician will review your case. If treatment is appropriate, you will receive a prescription. Approval is not guaranteed and individual results vary.',
					'cta_label'  => 'Ver mi plan y precio',
					'cta_label_en' => 'See my plan and price',
					'cta_url'    => '',
					'tone'       => 'success',
				),
				'review' => array(
					'eyebrow'    => 'Revisión clínica',
					'eyebrow_en' => 'Clinical review',
					'title'      => 'Necesitas una consulta médica directa',
					'title_en'   => 'You need a direct medical consultation',
					'body'       => 'Según tu historial, te recomendamos hablar con tu médico antes de iniciar un tratamiento GLP-1. Esto es por tu seguridad. Si tienes dudas sobre tu historial, consulta a tu médico y vuelve a completar la evaluación.',
					'body_en'    => 'Based on your history, we recommend speaking with your clinician before starting GLP-1 treatment. This is for your safety. If you are unsure about your history, consult your clinician and complete the evaluation again.',
					'cta_label'  => 'Hablar con el equipo',
					'cta_label_en' => 'Talk to the team',
					'cta_url'    => '',
					'tone'       => 'review',
				),
				'ineligible_bmi' => array(
					'eyebrow'    => 'Elegibilidad',
					'eyebrow_en' => 'Eligibility',
					'title'      => 'Tu IMC está por debajo del mínimo',
					'title_en'   => 'Your BMI is below the minimum',
					'body'       => 'Para considerar el tratamiento GLP-1 se requiere un IMC de al menos {min_bmi}. Tu médico puede revisar tu caso de forma individual. Si crees que hay un error en tus medidas, puedes intentarlo de nuevo.',
					'body_en'    => 'Considering GLP-1 treatment requires a BMI of at least {min_bmi}. Your clinician can review your case individually. If you think your measurements are wrong, you can try again.',
					'cta_label'  => 'Ingresar mis medidas de nuevo',
					'cta_label_en' => 'Enter my measurements again',
					'cta_url'    => '',
					'tone'       => 'neutral',
				),
				'ineligible_pregnancy' => array(
					'eyebrow'    => 'Seguridad',
					'eyebrow_en' => 'Safety',
					'title'      => 'No es seguro continuar ahora',
					'title_en'   => 'It is not safe to continue right now',
					'body'       => 'Los medicamentos GLP-1 no son seguros durante el embarazo o la lactancia. Consulta a tu médico cuando esta situación cambie; puedes volver a completar la evaluación.',
					'body_en'    => 'GLP-1 medications are not safe during pregnancy or breastfeeding. Consult your clinician when this changes; you can complete the evaluation again.',
					'cta_label'  => 'Hablar con el equipo',
					'cta_label_en' => 'Talk to the team',
					'cta_url'    => '',
					'tone'       => 'neutral',
				),
				'ineligible_age' => array(
					'eyebrow'    => 'Elegibilidad',
					'eyebrow_en' => 'Eligibility',
					'title'      => 'El servicio es para mayores de 18 años',
					'title_en'   => 'The service is for adults 18 and older',
					'body'       => 'NuestraRx solo puede atender a personas mayores de 18 años.',
					'body_en'    => 'NuestraRx can only care for people 18 and older.',
					'cta_label'  => 'Volver al inicio',
					'cta_label_en' => 'Back to home',
					'cta_url'    => '/',
					'tone'       => 'neutral',
				),
				'unavailable_state' => array(
					'eyebrow'    => 'Disponibilidad',
					'eyebrow_en' => 'Availability',
					'title'      => 'Aún no estamos disponibles en tu estado',
					'title_en'   => 'We are not available in your state yet',
					'body'       => 'La disponibilidad depende de las licencias médicas y farmacéuticas en cada estado. Escríbenos y te avisamos cuando esté disponible.',
					'body_en'    => 'Availability depends on medical and pharmacy licenses in each state. Message us and we will let you know when it is available.',
					'cta_label'  => 'Hablar con el equipo',
					'cta_label_en' => 'Talk to the team',
					'cta_url'    => '',
					'tone'       => 'neutral',
				),
				'declined' => array(
					'eyebrow'    => 'Evaluación',
					'eyebrow_en' => 'Evaluation',
					'title'      => 'Gracias por tu tiempo',
					'title_en'   => 'Thank you for your time',
					'body'       => 'No enviamos tu evaluación. Si quieres más información antes de decidir, escríbenos.',
					'body_en'    => 'We did not submit your evaluation. If you want more information before deciding, message us.',
					'cta_label'  => 'Hablar con el equipo',
					'cta_label_en' => 'Talk to the team',
					'cta_url'    => '',
					'tone'       => 'neutral',
				),
			),
		),
		'evaluation' => array(
			'default_outcome' => 'qualified',
			'checks'          => array(
				array( 'id' => 'bmi_min', 'enabled' => 1, 'field' => 'bmi', 'operator' => 'lt', 'value' => '25', 'outcome' => 'ineligible_bmi', 'flag' => '', 'label' => 'IMC mínimo' ),
				array( 'id' => 'pregnancy', 'enabled' => 1, 'field' => 'pregnant', 'operator' => 'equals', 'value' => 'yes', 'outcome' => 'ineligible_pregnancy', 'flag' => 'pregnancy_lactation', 'label' => 'Embarazo o lactancia' ),
				array( 'id' => 'age_min', 'enabled' => 1, 'field' => 'dob', 'operator' => 'age_under', 'value' => '18', 'outcome' => 'ineligible_age', 'flag' => 'under_18', 'label' => 'Edad mínima 18' ),
				array( 'id' => 'state', 'enabled' => 1, 'field' => 'state', 'operator' => 'state_unavailable', 'value' => '', 'outcome' => 'unavailable_state', 'flag' => 'state_unavailable', 'label' => 'Estado disponible' ),
				array( 'id' => 'thyroid', 'enabled' => 1, 'field' => 'thyroid_history', 'operator' => 'not_equals', 'value' => 'no', 'outcome' => 'review', 'flag' => 'mtc_risk', 'label' => 'Historial MTC / MEN2' ),
				array( 'id' => 'bariatric', 'enabled' => 1, 'field' => 'bariatric_surgery', 'operator' => 'equals', 'value' => 'yes_recent', 'outcome' => 'review', 'flag' => 'bariatric_surgery_recent', 'label' => 'Bypass en 6 meses' ),
				array( 'id' => 'testosterone_none', 'enabled' => 0, 'field' => 'medical_history', 'operator' => 'includes', 'value' => 'pancreatitis', 'outcome' => '', 'flag' => 'pancreatitis', 'label' => 'Bandera: pancreatitis (solo para el médico)' ),
			),
		),
		'steps'     => $steps,
	);
}

/**
 * Normalize a stored configuration (defensive, keeps drafts valid across updates).
 *
 * @param mixed $config Stored config.
 * @return array
 */
function nrx_quiz_normalize( $config ) {
	$defaults = nrx_quiz_default_config();
	if ( ! is_array( $config ) ) {
		return $defaults;
	}
	$config = array_merge( $defaults, $config );
	$config['meta']     = array_merge( $defaults['meta'], is_array( $config['meta'] ?? null ) ? $config['meta'] : array() );
	$config['settings'] = array_merge( $defaults['settings'], is_array( $config['settings'] ?? null ) ? $config['settings'] : array() );
	$config['settings']['outcomes'] = array_merge(
		$defaults['settings']['outcomes'],
		is_array( $config['settings']['outcomes'] ?? null ) ? $config['settings']['outcomes'] : array()
	);
	$config['evaluation'] = array_merge( $defaults['evaluation'], is_array( $config['evaluation'] ?? null ) ? $config['evaluation'] : array() );
	$config['evaluation']['checks'] = is_array( $config['evaluation']['checks'] ?? null ) ? $config['evaluation']['checks'] : $defaults['evaluation']['checks'];
	$config['steps'] = is_array( $config['steps'] ?? null ) ? array_values( $config['steps'] ) : $defaults['steps'];
	if ( empty( $config['version'] ) ) {
		$config['version'] = 1;
	}
	return $config;
}

/**
 * Get a configuration version.
 *
 * @param string $which published|draft.
 * @return array
 */
function nrx_quiz_get( $which = 'published' ) {
	$option = 'draft' === $which ? 'nrx_quiz_draft' : 'nrx_quiz_published';
	$config = get_option( $option );
	if ( false === $config || null === $config ) {
		if ( 'published' === $which ) {
			$config = get_option( 'nrx_quiz_published' );
			if ( false === $config || null === $config ) {
				// Until the installer publishes a copy, serve the seed as the published config.
				$config = nrx_quiz_default_config();
			}
		} else {
			$config = nrx_quiz_get( 'published' );
		}
	}
	return nrx_quiz_normalize( $config );
}

/**
 * Whether a draft differs from the published configuration.
 *
 * @return bool
 */
function nrx_quiz_has_draft_changes() {
	$published = get_option( 'nrx_quiz_published' );
	$draft     = get_option( 'nrx_quiz_draft' );
	if ( false === $draft ) {
		return false;
	}
	return wp_json_encode( $draft ) !== wp_json_encode( $published );
}

/**
 * Save the draft.
 *
 * @param array $config Configuration.
 * @return array Saved config.
 */
function nrx_quiz_save_draft( $config ) {
	$config = nrx_quiz_normalize( $config );
	$config['updated'] = current_time( 'mysql' );
	update_option( 'nrx_quiz_draft', $config, false );
	return $config;
}

/**
 * Publish a configuration and keep a revision snapshot.
 *
 * @param array  $config Configuration (draft).
 * @param string $note   Optional change note.
 * @return true|WP_Error
 */
function nrx_quiz_publish( $config, $note = '' ) {
	$config   = nrx_quiz_normalize( $config );
	$validate = nrx_quiz_validate( $config );
	if ( is_wp_error( $validate ) ) {
		return $validate;
	}
	$config['version'] = max( 1, (int) ( $config['version'] ?? 1 ) ) + 1;
	$config['updated'] = current_time( 'mysql' );
	$config['published_by'] = get_current_user_id();
	update_option( 'nrx_quiz_published', $config, false );
	update_option( 'nrx_quiz_draft', $config, false );

	$revisions = get_option( 'nrx_quiz_revisions', array() );
	$revisions = is_array( $revisions ) ? $revisions : array();
	array_unshift(
		$revisions,
		array(
			'time'    => current_time( 'mysql' ),
			'user'    => get_current_user_id(),
			'note'    => sanitize_text_field( $note ),
			'version' => $config['version'],
			'config'  => $config,
		)
	);
	update_option( 'nrx_quiz_revisions', array_slice( $revisions, 0, 10 ), false );

	/**
	 * Fires after a quiz configuration is published.
	 *
	 * @param array $config Published config.
	 */
	do_action( 'nrx_quiz_published', $config );

	return true;
}

/**
 * Validate a configuration before publishing.
 *
 * @param array $config Configuration.
 * @return true|WP_Error
 */
function nrx_quiz_validate( $config ) {
	$errors = array();
	$steps  = $config['steps'] ?? array();
	if ( empty( $steps ) ) {
		return new WP_Error( 'nrx_quiz_no_steps', __( 'La evaluación debe tener al menos un paso.', 'nuestrarx-core' ) );
	}

	$ids    = array();
	$fields = array();
	foreach ( $steps as $index => $step ) {
		$label = sprintf( __( 'Paso %d', 'nuestrarx-core' ), $index + 1 );
		$id    = sanitize_key( $step['id'] ?? '' );
		if ( ! $id ) {
			$errors[] = sprintf( __( '%s: falta el identificador.', 'nuestrarx-core' ), $label );
		} elseif ( isset( $ids[ $id ] ) ) {
			$errors[] = sprintf( __( '%s: identificador duplicado "%s".', 'nuestrarx-core' ), $label, $id );
		}
		$ids[ $id ] = true;
		if ( empty( $step['title'] ) ) {
			$errors[] = sprintf( __( '%s: el título es obligatorio.', 'nuestrarx-core' ), $label );
		}
		$type = $step['type'] ?? 'radio';
		if ( ! array_key_exists( $type, nrx_quiz_step_types() ) ) {
			$errors[] = sprintf( __( '%s: tipo desconocido "%s".', 'nuestrarx-core' ), $label, $type );
		}
		if ( in_array( $type, array( 'radio', 'checkboxes', 'select', 'state' ), true ) && empty( $step['options'] ) ) {
			$errors[] = sprintf( __( '%s: faltan opciones de respuesta.', 'nuestrarx-core' ), $label );
		}
		if ( 'consent' === $type && empty( $step['checkboxes'] ) ) {
			$errors[] = sprintf( __( '%s: el consentimiento necesita al menos una casilla.', 'nuestrarx-core' ), $label );
		}
		$field_names = array();
		if ( 'measurements' === $type ) {
			$field_names = array( 'height_ft', 'height_in', 'weight_lb', 'bmi' );
		} elseif ( 'consent' === $type ) {
			foreach ( (array) ( $step['checkboxes'] ?? array() ) as $box ) {
				if ( ! empty( $box['key'] ) ) {
					$field_names[] = sanitize_key( $box['key'] );
				}
			}
		} elseif ( 'fields' === $type ) {
			foreach ( (array) ( $step['fields'] ?? array() ) as $sub ) {
				if ( ! empty( $sub['key'] ) ) {
					$field_names[] = sanitize_key( $sub['key'] );
				}
			}
		} elseif ( ! empty( $step['field'] ) ) {
			$field_names[] = sanitize_key( $step['field'] );
		}
		foreach ( $field_names as $field ) {
			if ( isset( $fields[ $field ] ) ) {
				$errors[] = sprintf( __( '%s: el campo "%s" ya existe en otro paso.', 'nuestrarx-core' ), $label, $field );
			}
			$fields[ $field ] = true;
		}
	}

	// Condition references.
	foreach ( $steps as $index => $step ) {
		foreach ( (array) ( $step['conditions'] ?? array() ) as $condition ) {
			if ( ! empty( $condition['field'] ) && ! isset( $fields[ $condition['field'] ] ) ) {
				$errors[] = sprintf(
					/* translators: 1: step label, 2: field key */
					__( 'Paso %1$d: la condición apunta al campo inexistente "%2$s".', 'nuestrarx-core' ),
					$index + 1,
					$condition['field']
				);
			}
		}
	}

	// Evaluation check references and outcomes.
	$outcomes = array_keys( nrx_quiz_outcome_labels() );
	foreach ( (array) ( $config['evaluation']['checks'] ?? array() ) as $check ) {
		if ( empty( $check['field'] ) ) {
			continue;
		}
		if ( ! isset( $fields[ $check['field'] ] ) ) {
			$errors[] = sprintf(
				/* translators: %s: field key */
				__( 'Regla clínica "%s": campo inexistente.', 'nuestrarx-core' ),
				$check['field']
			);
		}
		if ( ! empty( $check['outcome'] ) && ! in_array( $check['outcome'], $outcomes, true ) ) {
			$errors[] = sprintf(
				/* translators: %s: outcome key */
				__( 'Regla clínica con resultado desconocido "%s".', 'nuestrarx-core' ),
				$check['outcome']
			);
		}
		if ( empty( $check['outcome'] ) && empty( $check['flag'] ) ) {
			$errors[] = __( 'Cada regla clínica debe producir un resultado o una bandera clínica.', 'nuestrarx-core' );
		}
	}

	if ( ! in_array( $config['evaluation']['default_outcome'] ?? 'qualified', $outcomes, true ) ) {
		$errors[] = __( 'El resultado por defecto no es válido.', 'nuestrarx-core' );
	}

	if ( $errors ) {
		return new WP_Error( 'nrx_quiz_invalid', implode( '<br>', array_map( 'esc_html', $errors ) ) );
	}
	return true;
}

/**
 * Compute BMI (US formula).
 *
 * @param float $feet   Feet.
 * @param float $inches Inches.
 * @param float $pounds Pounds.
 * @return float
 */
function nrx_bmi( $feet, $inches, $pounds ) {
	$total_inches = ( (float) $feet * 12 ) + (float) $inches;
	$pounds       = (float) $pounds;
	if ( $total_inches <= 0 || $pounds <= 0 ) {
		return 0;
	}
	return round( 703 * $pounds / ( $total_inches * $total_inches ), 1 );
}

/**
 * Age in years from a Y-m-d date.
 *
 * @param string $date Date.
 * @return int|null
 */
function nrx_age_from_date( $date ) {
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $date ) ) {
		return null;
	}
	try {
		$birth = new DateTime( $date, wp_timezone() );
	} catch ( Exception $e ) {
		return null;
	}
	$now = new DateTime( 'now', wp_timezone() );
	if ( $birth > $now ) {
		return null;
	}
	return (int) $birth->diff( $now )->y;
}

/**
 * Evaluate a condition against answers.
 *
 * @param array $condition Condition definition.
 * @param array $answers   Sanitized answers.
 * @return bool
 */
function nrx_quiz_condition_met( $condition, $answers ) {
	$field    = sanitize_key( $condition['field'] ?? '' );
	$operator = $condition['operator'] ?? 'equals';
	$expected = $condition['value'] ?? '';
	$actual   = $answers[ $field ] ?? null;

	switch ( $operator ) {
		case 'is_set':
			return ! ( null === $actual || '' === $actual || array() === $actual );
		case 'is_empty':
			return null === $actual || '' === $actual || array() === $actual;
		case 'equals':
			return is_array( $actual ) ? in_array( (string) $expected, $actual, true ) : (string) $actual === (string) $expected;
		case 'not_equals':
			return is_array( $actual ) ? ! in_array( (string) $expected, $actual, true ) : (string) $actual !== (string) $expected;
		case 'in':
			$list = array_map( 'trim', explode( ',', (string) $expected ) );
			return is_array( $actual ) ? (bool) array_intersect( $actual, $list ) : in_array( (string) $actual, $list, true );
		case 'not_in':
			$list = array_map( 'trim', explode( ',', (string) $expected ) );
			return is_array( $actual ) ? ! array_intersect( $actual, $list ) : ! in_array( (string) $actual, $list, true );
		case 'includes':
			return is_array( $actual ) ? in_array( (string) $expected, $actual, true ) : false;
		case 'not_includes':
			return is_array( $actual ) ? ! in_array( (string) $expected, $actual, true ) : true;
		case 'gt':
			return is_numeric( $actual ) && (float) $actual > (float) $expected;
		case 'gte':
			return is_numeric( $actual ) && (float) $actual >= (float) $expected;
		case 'lt':
			return is_numeric( $actual ) && (float) $actual < (float) $expected;
		case 'lte':
			return is_numeric( $actual ) && (float) $actual <= (float) $expected;
		case 'age_under':
			$age = nrx_age_from_date( (string) $actual );
			return null !== $age && $age < (int) $expected;
		case 'age_over':
			$age = nrx_age_from_date( (string) $actual );
			return null !== $age && $age > (int) $expected;
		case 'state_unavailable':
			return '' !== (string) $actual && ! nrx_state_is_available( (string) $actual );
	}
	return false;
}

/**
 * Whether a step is visible given current answers.
 *
 * @param array $step    Step.
 * @param array $answers Answers.
 * @return bool
 */
function nrx_quiz_step_visible( $step, $answers ) {
	$conditions = $step['conditions'] ?? array();
	if ( empty( $conditions ) ) {
		return true;
	}
	$match = ( $step['condition_match'] ?? 'all' ) === 'any' ? 'any' : 'all';
	$met   = array();
	foreach ( $conditions as $condition ) {
		$met[] = nrx_quiz_condition_met( $condition, $answers );
	}
	return 'any' === $match ? in_array( true, $met, true ) : ! in_array( false, $met, true );
}

/**
 * Visible steps for a set of answers.
 *
 * @param array $config  Config.
 * @param array $answers Answers.
 * @return array
 */
function nrx_quiz_visible_steps( $config, $answers ) {
	return array_values(
		array_filter(
			$config['steps'],
			static function ( $step ) use ( $answers ) {
				return nrx_quiz_step_visible( $step, $answers );
			}
		)
	);
}

/**
 * Evaluate answers and return outcome plus clinical flags.
 *
 * @param array $config  Config.
 * @param array $answers Sanitized answers.
 * @return array{outcome:string,flags:array,matched:array}
 */
function nrx_quiz_evaluate( $config, $answers ) {
	$outcome = 'qualified';
	$flags   = array();
	$matched = array();

	foreach ( (array) ( $config['evaluation']['checks'] ?? array() ) as $check ) {
		if ( empty( $check['enabled'] ) ) {
			continue;
		}
		$condition = array(
			'field'    => $check['field'] ?? '',
			'operator' => $check['operator'] ?? 'equals',
			'value'    => $check['value'] ?? '',
		);
		if ( ! nrx_quiz_condition_met( $condition, $answers ) ) {
			continue;
		}
		$matched[] = $check['id'] ?? '';
		if ( ! empty( $check['flag'] ) ) {
			$flags[] = sanitize_key( $check['flag'] );
		}
		if ( ! empty( $check['outcome'] ) ) {
			$outcome = $check['outcome'];
			break;
		}
	}

	// Option-level flags (e.g. pancreatitis, MEN2) for clinician review.
	$flag_map = array();
	foreach ( $config['steps'] as $step ) {
		foreach ( (array) ( $step['options'] ?? array() ) as $option ) {
			if ( ! empty( $option['flag'] ) && ! empty( $option['value'] ) ) {
				$flag_map[ sanitize_key( $option['flag'] ) ] = array( $step['field'] ?? '', $option['value'] );
			}
		}
	}
	foreach ( $flag_map as $flag => $pair ) {
		$field = $pair[0];
		$value = $pair[1];
		if ( ! $field ) {
			continue;
		}
		$actual = $answers[ $field ] ?? null;
		if ( is_array( $actual ) ? in_array( $value, $actual, true ) : (string) $actual === (string) $value ) {
			$flags[] = $flag;
		}
	}

	$outcome = sanitize_key( $outcome );
	if ( ! array_key_exists( $outcome, nrx_quiz_outcome_labels() ) ) {
		$outcome = 'review';
	}

	return array(
		'outcome' => $outcome,
		'flags'   => array_values( array_unique( array_filter( $flags ) ) ),
		'matched' => array_values( array_filter( $matched ) ),
	);
}
