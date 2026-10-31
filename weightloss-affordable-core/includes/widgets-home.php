<?php
/**
 * Elementor widgets for homepage clinical sections.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLA_Base_Widget' ) ) {
	return;
}

trait WLA_Home_Clinical_Helpers {
	protected function ensure_home_clinical() {
		if ( ! function_exists( 'wla_render_guarantee_section' ) ) {
			$file = get_template_directory() . '/inc/home-clinical.php';
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
		return function_exists( 'wla_render_guarantee_section' );
	}

	protected function lines_to_points( $raw ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $raw ) ) ) );
		$out   = array();
		foreach ( $lines as $line ) {
			$out[] = array( 'text' => $line );
		}
		return $out;
	}
}

class WLA_Guarantee_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_guarantee'; }
	public function get_title() { return __( 'WLA: Protected Start', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-check-circle'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'YOUR PROTECTION' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Start with protection, not pressure.' ) );
		$this->add_control( 'emphasis', array( 'label' => __( 'Italic word', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'protection' ) );
		$this->add_control( 'body', array( 'label' => __( 'Body', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'You are not charged for an enquiry. A licensed clinician reviews your information and decides whether any treatment is appropriate. Individual results vary and are never guaranteed.' ) );
		$this->add_control( 'points_raw', array( 'label' => __( 'Points (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "No charge for completing the Get Quote quiz\nA licensed clinician makes the prescribing decision\nCancel before a future billing cycle if you enroll\nCompounded medications are not FDA-approved" ) );
		$this->add_control( 'stat', array( 'label' => __( 'Stat', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '0' ) );
		$this->add_control( 'stat_label', array( 'label' => __( 'Stat label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Charge until a clinician reviews your case' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'See if you qualify' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/consultation-eligibility/' ) );
		$this->add_control( 'note', array( 'label' => __( 'Note', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'This is not a promise of weight loss, approval, or a specific outcome. Any refund or billing rule must match the published policy pages.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		$s = $this->get_settings_for_display();
		echo wla_render_guarantee_section( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'eyebrow'     => $s['eyebrow'] ?? '',
				'title'       => $s['title'] ?? '',
				'emphasis'    => $s['emphasis'] ?? 'protection',
				'body'        => $s['body'] ?? '',
				'points'      => $this->lines_to_points( $s['points_raw'] ?? '' ),
				'stat'        => $s['stat'] ?? '0',
				'stat_label'  => $s['stat_label'] ?? '',
				'button_text' => $s['button_text'] ?? '',
				'button_url'  => $s['button_url'] ?? '/consultation-eligibility/',
				'note'        => $s['note'] ?? '',
			)
		);
	}
}

class WLA_Glp1_Signal_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_glp1_signal'; }
	public function get_title() { return __( 'WLA: GLP-1 Signal', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-nerd'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'THE MECHANISM' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'How GLP-1 changes the signal.' ) );
		$this->add_control( 'body', array( 'label' => __( 'Body', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'GLP-1 is a hormone your gut releases after eating. Prescription GLP-1 medicines amplify that pathway. They are not a substitute for nutrition, movement, sleep, or clinical care.' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View treatments' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/weight-loss-services/' ) );
		$this->add_control( 'image', array( 'label' => __( 'Image', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		$s = $this->get_settings_for_display();
		echo wla_render_signal_section( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'eyebrow'     => $s['eyebrow'] ?? '',
				'title'       => $s['title'] ?? '',
				'body'        => $s['body'] ?? '',
				'button_text' => $s['button_text'] ?? '',
				'button_url'  => $s['button_url'] ?? '/weight-loss-services/',
				'image'       => ! empty( $s['image']['url'] ) ? $s['image']['url'] : 'nutrition.jpg',
			)
		);
	}
}

class WLA_Treatments_Duo_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_treatments_duo'; }
	public function get_title() { return __( 'WLA: Two Treatments', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-posts-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'OUR TREATMENTS' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Two medications. One careful path.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'A licensed clinician recommends an option based on your history. Compounded preparations are not FDA-approved and are not bioequivalent to brand-name medicines.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		$s = $this->get_settings_for_display();
		echo wla_render_treatments_duo_section( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Transformations_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_transformations'; }
	public function get_title() { return __( 'WLA: Transformations', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-image-before-after'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'REAL TRANSFORMATIONS' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Progress is personal — and never guaranteed.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Authorized before-and-after stories belong here only after documented consent. These cards are layout slots, not live patient results.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		echo wla_render_transformations_section( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Video_Stories_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_video_stories'; }
	public function get_title() { return __( 'WLA: Video Stories', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-video-playlist'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'VIDEO STORIES' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Hear the process in plain language.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Use only genuine, consented video. Until real files are approved, these panels show the layout only.' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Get a quote' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/consultation-eligibility/' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		echo wla_render_video_stories_section( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Science_Widget extends WLA_Base_Widget {
	use WLA_Home_Clinical_Helpers;
	public function get_name() { return 'wla_science'; }
	public function get_title() { return __( 'WLA: GLP-1 Science', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-sitemap'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'THE SCIENCE' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'How GLP-1 agonists work.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'The descriptions below explain drug classes found in peer-reviewed literature. They do not claim that a compounded preparation matches a brand-name trial result.' ) );
		$this->add_control( 'note', array( 'label' => __( 'Note', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Compounded semaglutide and tirzepatide are not FDA-approved and are not bioequivalent to brand-name products. Individual results vary.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_home_clinical() ) {
			return;
		}
		echo wla_render_science_section( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
