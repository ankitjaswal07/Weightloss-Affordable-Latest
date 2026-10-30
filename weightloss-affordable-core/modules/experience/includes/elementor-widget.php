<?php
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) { return; }

class WLA_Experience_Widget extends \Elementor\Widget_Base {
	public function get_name() { return 'wla-experience-component'; }
	public function get_title() { return __( 'Experience Section', 'wla-experience' ); }
	public function get_icon() { return 'eicon-heart'; }
	public function get_categories() { return array( 'wla-experience' ); }
	public function get_keywords() { return array( 'treatment', 'quote', 'results', 'video', 'calculator', 'weightlossaffordable' ); }
	protected function register_controls() {
		$this->start_controls_section( 'wla_content', array( 'label' => __( 'Section content', 'wla-experience' ) ) );
		$this->add_control( 'module', array(
			'label' => __( 'Component', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'treatment_plans',
			'options' => array(
				'treatment_plans' => __( 'Treatment Plans', 'wla-experience' ),
				'treatment_detail' => __( 'Treatment Detail (dynamic NuestraRx record)', 'wla-experience' ),
				'get_quote' => __( 'Get a Quote (secure intake link)', 'wla-experience' ),
				'results' => __( 'Results (approved stories only)', 'wla-experience' ),
				'video_testimonials' => __( 'Video Testimonials', 'wla-experience' ),
				'calculators' => __( 'Calculators', 'wla-experience' ),
			),
		) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$this->add_control( 'intro', array( 'label' => __( 'Supporting text', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '', 'rows' => 3 ) );
		$this->add_control( 'quote_mode', array( 'label' => __( 'Quote experience', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'cta', 'options' => array( 'cta' => __( 'Link to existing secure intake page', 'wla-experience' ), 'quiz' => __( 'Embed the existing NuestraRx quiz', 'wla-experience' ) ), 'condition' => array( 'module' => 'get_quote' ) ) );
		$this->add_control( 'button', array( 'label' => __( 'Quote button label', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Get a quote', 'wla-experience' ), 'condition' => array( 'module' => 'get_quote', 'quote_mode' => 'cta' ) ) );
		$this->add_control( 'url', array( 'label' => __( 'Verified secure intake URL', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::URL, 'placeholder' => __( 'Select the approved intake page', 'wla-experience' ), 'show_external' => false, 'condition' => array( 'module' => 'get_quote', 'quote_mode' => 'cta' ) ) );
		$this->add_control( 'treatment_slug', array( 'label' => __( 'Treatment record slug (blank = current record)', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '', 'placeholder' => 'semaglutida', 'condition' => array( 'module' => 'treatment_detail' ) ) );
		$this->add_control( 'demo', array( 'label' => __( 'Show clearly labeled demo placeholders (not publication-ready)', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'condition' => array( 'module' => array( 'treatment_plans', 'treatment_detail', 'results', 'video_testimonials' ) ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'Maximum cards', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6, 'min' => 1, 'max' => 12, 'condition' => array( 'module' => array( 'treatment_plans', 'treatment_detail', 'results', 'video_testimonials' ) ) ) );
		$this->add_control( 'bmi_title', array( 'label' => __( 'BMI calculator heading', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' , 'condition' => array( 'module' => 'calculators' ) ) );
		$this->add_control( 'bmi_intro', array( 'label' => __( 'BMI calculator description', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '', 'condition' => array( 'module' => 'calculators' ) ) );
		$this->add_control( 'change_title', array( 'label' => __( 'Weight-change calculator heading', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '', 'condition' => array( 'module' => 'calculators' ) ) );
		$this->add_control( 'change_intro', array( 'label' => __( 'Weight-change calculator description', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '', 'condition' => array( 'module' => 'calculators' ) ) );
		$this->add_control( 'cta_label', array( 'label' => __( 'Optional section CTA label', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'cta_url', array( 'label' => __( 'Optional section CTA URL', 'wla-experience' ), 'type' => \Elementor\Controls_Manager::URL, 'show_external' => false ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$module = sanitize_key( $s['module'] ?? 'treatment_plans' );
		$map = array( 'treatment_plans' => 'wla_treatment_plans', 'treatment_detail' => 'wla_treatment_detail', 'get_quote' => 'wla_get_quote', 'results' => 'wla_results', 'video_testimonials' => 'wla_video_testimonials', 'calculators' => 'wla_calculators' );
		if ( ! isset( $map[ $module ] ) ) { return; }
		$attrs = array();
		if ( ! empty( $s['title'] ) ) { $attrs['title'] = $s['title']; }
		if ( ! empty( $s['intro'] ) ) { $attrs['intro'] = $s['intro']; }
		if ( ! empty( $s['limit'] ) ) { $attrs['limit'] = absint( $s['limit'] ); }
		if ( 'treatment_detail' === $module && ! empty( $s['treatment_slug'] ) ) { $attrs['slug'] = sanitize_title( $s['treatment_slug'] ); }
		if ( ! empty( $s['demo'] ) ) { $attrs['demo'] = 'yes'; }
		if ( 'calculators' === $module ) {
			foreach ( array( 'bmi_title', 'bmi_intro', 'change_title', 'change_intro' ) as $key ) { if ( ! empty( $s[ $key ] ) ) { $attrs[ $key ] = $s[ $key ]; } }
		}
		if ( 'get_quote' === $module ) {
			$attrs['mode'] = 'quiz' === ( $s['quote_mode'] ?? 'cta' ) ? 'quiz' : 'cta';
			if ( ! empty( $s['button'] ) ) { $attrs['button'] = $s['button']; }
			if ( ! empty( $s['url']['url'] ) ) { $attrs['url'] = $s['url']['url']; }
		}
		$shortcode = '[' . $map[ $module ];
		foreach ( $attrs as $key => $value ) { $shortcode .= ' ' . sanitize_key( $key ) . '="' . esc_attr( (string) $value ) . '"'; }
		$shortcode .= ']';
		echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode renderers escape stored/dynamic content.
		if ( ! empty( $s['cta_label'] ) && ! empty( $s['cta_url']['url'] ) && 'get_quote' !== $module ) {
			$url = esc_url( $s['cta_url']['url'] );
			if ( $url ) { echo '<div class="wla-exp-bottom-cta"><a class="wla-exp-button" href="' . $url . '">' . esc_html( $s['cta_label'] ) . ' <span aria-hidden="true">→</span></a></div>'; }
		}
	}
}
