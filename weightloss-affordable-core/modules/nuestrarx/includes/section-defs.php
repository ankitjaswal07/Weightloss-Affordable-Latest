<?php
/**
 * Section type definitions.
 *
 * Each type declares the content fields an administrator can edit. The
 * theme renders the same types through template-parts/sections/{type}.php.
 *
 * @package NuestraRx_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small field factory.
 *
 * @param string $key   Field key.
 * @param string $label Label.
 * @param string $type  Field type.
 * @param array  $args  Extra field args.
 * @return array
 */
function nrx_f( $key, $label, $type = 'text', $args = array() ) {
	return array_merge(
		array(
			'key'   => $key,
			'label' => $label,
			'type'  => $type,
		),
		$args
	);
}

/**
 * Bilingual field factory.
 *
 * @param string $key   Field key.
 * @param string $label Label.
 * @param string $type  Field type.
 * @param array  $args  Extra args.
 * @return array
 */
function nrx_bi_f( $key, $label, $type = 'text', $args = array() ) {
	$args['bilingual'] = true;
	return nrx_f( $key, $label, $type, $args );
}

/**
 * Icon choices available to content editors.
 *
 * @return array
 */
function nrx_icon_choices() {
	return array(
		''            => __( 'Sin icono', 'nuestrarx-core' ),
		'spark'       => 'Spark',
		'heart'       => 'Corazón',
		'shield'      => 'Escudo',
		'chat'        => 'Chat',
		'book'        => 'Libro',
		'clock'       => 'Reloj',
		'map'         => 'Mapa',
		'lock'        => 'Candado',
		'phone'       => 'Teléfono',
		'mail'        => 'Correo',
		'whatsapp'    => 'WhatsApp',
		'star'        => 'Estrella',
		'user'        => 'Persona',
		'stethoscope' => 'Estetoscopio',
		'pill'        => 'Medicamento',
		'truck'       => 'Envío',
		'lab'         => 'Laboratorio',
		'document'    => 'Documento',
		'leaf'        => 'Hoja',
		'globe'       => 'Globo',
		'check'       => 'Check',
		'info'        => 'Información',
	);
}

/**
 * Reusable repeater sub-schemas.
 *
 * @return array<string,array>
 */
function nrx_section_subfields() {
	$icon = nrx_icon_choices();
	return array(
		'chip' => array(
			nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ),
			nrx_f( 'icon', __( 'Icono', 'nuestrarx-core' ), 'select', array( 'options' => $icon ) ),
		),
		'stat' => array(
			nrx_bi_f( 'value', __( 'Valor', 'nuestrarx-core' ) ),
			nrx_bi_f( 'label', __( 'Etiqueta', 'nuestrarx-core' ) ),
		),
		'card' => array(
			nrx_f( 'icon', __( 'Icono', 'nuestrarx-core' ), 'select', array( 'options' => $icon ) ),
			nrx_bi_f( 'tag', __( 'Etiqueta pequeña', 'nuestrarx-core' ) ),
			nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
			nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'textarea' ),
		),
		'step' => array(
			nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
			nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'textarea' ),
			nrx_bi_f( 'meta', __( 'Dato (tiempo, condición)', 'nuestrarx-core' ) ),
			nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ),
			nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
		),
		'bullet' => array(
			nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
			nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'textarea' ),
		),
		'faq' => array(
			nrx_bi_f( 'question', __( 'Pregunta', 'nuestrarx-core' ) ),
			nrx_bi_f( 'answer', __( 'Respuesta', 'nuestrarx-core' ), 'richtext' ),
		),
		'science' => array(
			nrx_bi_f( 'eyebrow', __( 'Clase / etiqueta', 'nuestrarx-core' ) ),
			nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
			nrx_bi_f( 'bullets', __( 'Puntos (uno por línea)', 'nuestrarx-core' ), 'textarea' ),
			nrx_bi_f( 'note', __( 'Nota al pie', 'nuestrarx-core' ) ),
		),
	);
}

/**
 * All section types.
 *
 * @return array
 */
function nrx_section_types() {
	$sub  = nrx_section_subfields();
	$icon = nrx_icon_choices();

	$types = array(
		'hero' => array(
			'label'       => __( 'Hero / Portada', 'nuestrarx-core' ),
			'description' => __( 'Primera impresión: propuesta de valor, CTA y prueba de confianza.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title_line1', __( 'Título — línea 1', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title_emphasis', __( 'Título — énfasis (cursiva)', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Descripción', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'primary_label', __( 'Botón principal', 'nuestrarx-core' ) ),
				nrx_f( 'primary_url', __( 'Enlace del botón principal', 'nuestrarx-core' ), 'text', array( 'default' => '/evaluacion/' ) ),
				nrx_bi_f( 'secondary_label', __( 'Botón secundario', 'nuestrarx-core' ) ),
				nrx_f( 'secondary_url', __( 'Enlace secundario', 'nuestrarx-core' ), 'text', array( 'default' => '#tratamientos' ) ),
				nrx_f( 'image', __( 'Imagen principal', 'nuestrarx-core' ), 'image' ),
				nrx_bi_f( 'image_alt', __( 'Texto alternativo de la imagen', 'nuestrarx-core' ) ),
				nrx_bi_f( 'note', __( 'Nota bajo los botones', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'card_label', __( 'Tarjeta flotante — etiqueta', 'nuestrarx-core' ) ),
				nrx_bi_f( 'card_title', __( 'Tarjeta flotante — título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'card_meta', __( 'Tarjeta flotante — detalle', 'nuestrarx-core' ) ),
				nrx_f( 'chips', __( 'Indicadores de confianza', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['chip'], 'row_label' => __( 'Indicador', 'nuestrarx-core' ) ) ),
				nrx_f( 'stats', __( 'Cifras destacadas', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['stat'], 'row_label' => __( 'Cifra', 'nuestrarx-core' ) ) ),
			),
		),

		'trust_marquee' => array(
			'label'       => __( 'Banda de confianza', 'nuestrarx-core' ),
			'description' => __( 'Tira continua con condiciones del servicio y sellos verificables.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_f( 'items', __( 'Elementos', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Elemento', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'note', __( 'Nota al pie de la banda', 'nuestrarx-core' ) ),
			),
		),

		'problem' => array(
			'label'       => __( 'Problema / contexto', 'nuestrarx-core' ),
			'description' => __( 'Explica por qué el enfoque médico tiene sentido, sin promesas.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Texto principal', 'nuestrarx-core' ), 'richtext' ),
				nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ),
				nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
				nrx_f( 'cards', __( 'Tarjetas', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['card'], 'row_label' => __( 'Tarjeta', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'note', __( 'Nota editorial', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'link_label', __( 'Enlace de texto', 'nuestrarx-core' ) ),
				nrx_f( 'link_url', __( 'URL del enlace', 'nuestrarx-core' ) ),
			),
		),

		'mechanism' => array(
			'label'       => __( 'Mecanismo / cómo funciona', 'nuestrarx-core' ),
			'description' => __( 'Explicación educativa del mecanismo, con aviso de compuestos.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'richtext' ),
				nrx_f( 'bullets', __( 'Puntos', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['bullet'], 'row_label' => __( 'Punto', 'nuestrarx-core' ) ) ),
				nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ),
				nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'aside_label', __( 'Etiqueta del panel', 'nuestrarx-core' ) ),
				nrx_bi_f( 'aside_body', __( 'Texto del panel', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'cta_label', __( 'Botón', 'nuestrarx-core' ) ),
				nrx_f( 'cta_url', __( 'Enlace del botón', 'nuestrarx-core' ) ),
				nrx_bi_f( 'disclaimer', __( 'Aviso legal', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'guarantee' => array(
			'label'       => __( 'Garantía / oferta', 'nuestrarx-core' ),
			'description' => __( 'Oferta de garantía. Requiere aprobación legal y términos vigentes.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'richtext' ),
				nrx_bi_f( 'badge_value', __( 'Valor del sello (ej. 12)', 'nuestrarx-core' ) ),
				nrx_bi_f( 'badge_label', __( 'Etiqueta del sello', 'nuestrarx-core' ) ),
				nrx_f( 'bullets', __( 'Puntos de la garantía', 'nuestrarx-core' ), 'repeater', array( 'fields' => array( nrx_bi_f( 'label', __( 'Texto', 'nuestrarx-core' ) ) ), 'row_label' => __( 'Punto', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'cta_label', __( 'Botón', 'nuestrarx-core' ) ),
				nrx_f( 'cta_url', __( 'Enlace', 'nuestrarx-core' ) ),
				nrx_bi_f( 'disclaimer', __( 'Términos y aviso', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'require_approval', __( 'Ocultar hasta aprobación de cumplimiento', 'nuestrarx-core' ), 'checkbox', array( 'default' => 1 ) ),
			),
		),

		'treatments' => array(
			'label'       => __( 'Tratamientos (dinámico)', 'nuestrarx-core' ),
			'description' => __( 'Carga automáticamente los tratamientos publicados. Sin datos duplicados.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'limit', __( 'Número máximo de tratamientos', 'nuestrarx-core' ), 'number', array( 'default' => 4, 'min' => 1, 'max' => 12 ) ),
				nrx_bi_f( 'cta_label', __( 'Botón de cada tarjeta', 'nuestrarx-core' ) ),
				nrx_bi_f( 'note', __( 'Nota bajo las tarjetas', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'steps' => array(
			'label'       => __( 'Pasos del proceso', 'nuestrarx-core' ),
			'description' => __( 'Cómo funciona el proceso, en pasos numerados.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'steps', __( 'Pasos', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['step'], 'row_label' => __( 'Paso', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'cta_label', __( 'Botón', 'nuestrarx-core' ) ),
				nrx_f( 'cta_url', __( 'Enlace del botón', 'nuestrarx-core' ) ),
				nrx_bi_f( 'note', __( 'Nota', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'journey' => array(
			'label'       => __( 'Jornada del paciente', 'nuestrarx-core' ),
			'description' => __( 'Tarjetas grandes con imagen para explicar el recorrido.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'steps', __( 'Etapas', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['step'], 'row_label' => __( 'Etapa', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'cta_label', __( 'Botón', 'nuestrarx-core' ) ),
				nrx_f( 'cta_url', __( 'Enlace del botón', 'nuestrarx-core' ) ),
			),
		),

		'personas' => array(
			'label'       => __( 'Personas / a quién ayudamos', 'nuestrarx-core' ),
			'description' => __( 'Tres o más perfiles con imagen.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f(
					'cards',
					__( 'Perfiles', 'nuestrarx-core' ),
					'repeater',
					array(
						'fields'    => array(
							nrx_bi_f( 'tag', __( 'Etiqueta', 'nuestrarx-core' ) ),
							nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
							nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'textarea' ),
							nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ),
							nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
						),
						'row_label' => __( 'Perfil', 'nuestrarx-core' ),
					)
				),
			),
		),

		'transformations' => array(
			'label'       => __( 'Antes y después', 'nuestrarx-core' ),
			'description' => __( 'Resultados de pacientes. Requiere consentimiento y verificación.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f(
					'items',
					__( 'Casos', 'nuestrarx-core' ),
					'repeater',
					array(
						'fields'    => array(
							nrx_bi_f( 'name', __( 'Nombre y edad', 'nuestrarx-core' ) ),
							nrx_bi_f( 'meta', __( 'Ciudad · tratamiento · semanas', 'nuestrarx-core' ) ),
							nrx_bi_f( 'loss', __( 'Pérdida (ej. −42 lb)', 'nuestrarx-core' ) ),
							nrx_bi_f( 'period', __( 'Periodo (ej. en 6 meses)', 'nuestrarx-core' ) ),
							nrx_bi_f( 'quote', __( 'Cita', 'nuestrarx-core' ), 'textarea' ),
							nrx_f( 'before_image', __( 'Imagen antes', 'nuestrarx-core' ), 'image' ),
							nrx_f( 'after_image', __( 'Imagen después', 'nuestrarx-core' ), 'image' ),
						),
						'row_label' => __( 'Caso', 'nuestrarx-core' ),
					)
				),
				nrx_bi_f( 'disclaimer', __( 'Aviso de resultados', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'require_approval', __( 'Ocultar hasta aprobación de cumplimiento', 'nuestrarx-core' ), 'checkbox', array( 'default' => 1 ) ),
			),
		),

		'testimonials' => array(
			'label'       => __( 'Testimonios (dinámico)', 'nuestrarx-core' ),
			'description' => __( 'Carga testimonios verificados del gestor de testimonios.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'limit', __( 'Número de testimonios', 'nuestrarx-core' ), 'number', array( 'default' => 3, 'min' => 1, 'max' => 12 ) ),
				nrx_bi_f( 'source_note', __( 'Nota sobre la fuente', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'disclaimer', __( 'Aviso obligatorio', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'require_approval', __( 'Ocultar hasta aprobación de cumplimiento', 'nuestrarx-core' ), 'checkbox', array( 'default' => 1 ) ),
			),
		),

		'science' => array(
			'label'       => __( 'Ciencia', 'nuestrarx-core' ),
			'description' => __( 'Mecanismo descrito en literatura revisada por pares.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'items', __( 'Fichas', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['science'], 'row_label' => __( 'Ficha', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'disclaimer', __( 'Aviso FDA', 'nuestrarx-core' ), 'richtext' ),
			),
		),

		'clinicians' => array(
			'label'       => __( 'Modelo clínico', 'nuestrarx-core' ),
			'description' => __( 'Supervisión médica, red de profesionales y estándares.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'richtext' ),
				nrx_f( 'cards', __( 'Garantías del modelo', 'nuestrarx-core' ), 'repeater', array( 'fields' => array_merge( array( nrx_f( 'icon', __( 'Icono', 'nuestrarx-core' ), 'select', array( 'options' => $icon ) ) ), $sub['card'] ), 'row_label' => __( 'Garantía', 'nuestrarx-core' ) ) ),
				nrx_f( 'image', __( 'Imagen', 'nuestrarx-core' ), 'image' ),
				nrx_bi_f( 'image_alt', __( 'Texto alternativo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'cta_label', __( 'Botón', 'nuestrarx-core' ) ),
				nrx_f( 'cta_url', __( 'Enlace', 'nuestrarx-core' ) ),
				nrx_bi_f( 'note', __( 'Nota de verificación', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'clinician_team' => array(
			'label'       => __( 'Equipo clínico (dinámico)', 'nuestrarx-core' ),
			'description' => __( 'Muestra los profesionales verificados. No se publica ningún perfil sin verificación.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'empty_text', __( 'Texto si aún no hay perfiles verificados', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'faq' => array(
			'label'       => __( 'Preguntas frecuentes', 'nuestrarx-core' ),
			'description' => __( 'Contenido con schema FAQPage generado automáticamente.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'items', __( 'Preguntas', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['faq'], 'row_label' => __( 'Pregunta', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'note', __( 'Nota con enlace de contacto', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'blog' => array(
			'label'       => __( 'Blog (dinámico)', 'nuestrarx-core' ),
			'description' => __( 'Últimos artículos publicados en WordPress.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f( 'count', __( 'Número de artículos', 'nuestrarx-core' ), 'number', array( 'default' => 3, 'min' => 1, 'max' => 6 ) ),
				nrx_bi_f( 'button_label', __( 'Botón "ver todos"', 'nuestrarx-core' ) ),
				nrx_f( 'fallback_url', __( 'URL del blog externo (opcional)', 'nuestrarx-core' ), 'url' ),
			),
		),

		'cta' => array(
			'label'       => __( 'Llamado a la acción', 'nuestrarx-core' ),
			'description' => __( 'Cierre de página con CTA y avisos.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Texto', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'primary_label', __( 'Botón principal', 'nuestrarx-core' ) ),
				nrx_f( 'primary_url', __( 'Enlace principal', 'nuestrarx-core' ), 'text', array( 'default' => '/evaluacion/' ) ),
				nrx_bi_f( 'secondary_label', __( 'Botón secundario', 'nuestrarx-core' ) ),
				nrx_f( 'secondary_url', __( 'Enlace secundario', 'nuestrarx-core' ) ),
				nrx_f( 'chips', __( 'Indicadores', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['chip'], 'row_label' => __( 'Indicador', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'disclaimer', __( 'Aviso', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'rich_text' => array(
			'label'       => __( 'Texto enriquecido', 'nuestrarx-core' ),
			'description' => __( 'Bloque editorial libre con HTML seguro.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'body', __( 'Contenido', 'nuestrarx-core' ), 'richtext', array( 'rows' => 12 ) ),
				nrx_f( 'width', __( 'Ancho', 'nuestrarx-core' ), 'select', array( 'options' => array( 'wide' => 'Ancho completo', 'narrow' => 'Lectura (760px)' ), 'default' => 'narrow' ) ),
			),
		),

		'pharmacy_partners' => array(
			'label'       => __( 'Transparencia de farmacias', 'nuestrarx-core' ),
			'description' => __( 'Farmacias verificadas, licencias y documentación. No inventa certificados.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'richtext' ),
				nrx_f( 'steps', __( 'Cómo verificar', 'nuestrarx-core' ), 'repeater', array( 'fields' => $sub['step'], 'row_label' => __( 'Paso', 'nuestrarx-core' ) ) ),
				nrx_bi_f( 'documents_note', __( 'Nota sobre certificados de análisis', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'disclaimer', __( 'Aviso legal', 'nuestrarx-core' ), 'textarea' ),
			),
		),

		'provider_resources' => array(
			'label'       => __( 'Recursos para profesionales', 'nuestrarx-core' ),
			'description' => __( 'Documentación clínica y contacto profesional.', 'nuestrarx-core' ),
			'fields'      => array(
				nrx_bi_f( 'eyebrow', __( 'Antetítulo', 'nuestrarx-core' ) ),
				nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
				nrx_bi_f( 'intro', __( 'Introducción', 'nuestrarx-core' ), 'textarea' ),
				nrx_f(
					'resources',
					__( 'Recursos', 'nuestrarx-core' ),
					'repeater',
					array(
						'fields'    => array(
							nrx_bi_f( 'title', __( 'Título', 'nuestrarx-core' ) ),
							nrx_bi_f( 'body', __( 'Descripción', 'nuestrarx-core' ), 'textarea' ),
							nrx_f( 'url', __( 'Enlace o PDF', 'nuestrarx-core' ), 'image' ),
							nrx_bi_f( 'meta', __( 'Detalle (formato, fecha)', 'nuestrarx-core' ) ),
						),
						'row_label' => __( 'Recurso', 'nuestrarx-core' ),
					)
				),
				nrx_bi_f( 'contact_title', __( 'Título de contacto', 'nuestrarx-core' ) ),
				nrx_bi_f( 'contact_body', __( 'Texto de contacto', 'nuestrarx-core' ), 'textarea' ),
				nrx_bi_f( 'disclaimer', __( 'Aviso', 'nuestrarx-core' ), 'textarea' ),
			),
		),
	);

	/**
	 * Filter the section registry so add-ons can extend it.
	 *
	 * @param array $types Section types.
	 */
	return apply_filters( 'nrx_section_types', $types );
}
