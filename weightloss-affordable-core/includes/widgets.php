<?php
/**
 * Complete Free Elementor Widgets matching the 100% Straw Luxury Preview.
 * Every control edits real Elementor document settings and renders through the shared
 * theme render engine so editor preview and public frontend are 100.0% pixel-identical.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WLA_Base_Widget extends \Elementor\Widget_Base {
	public function get_categories() {
		return array( 'wla-content' );
	}

	protected function ensure_render_helpers() {
		if ( ! function_exists( 'wla_render_hero_section' ) ) {
			$helper = get_template_directory() . '/inc/render-helpers.php';
			if ( is_readable( $helper ) ) {
				require_once $helper;
			}
		}
		return function_exists( 'wla_render_hero_section' );
	}
}

class WLA_Hero_Pro_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_hero_pro'; }
	public function get_title() { return __( 'WLA: Hero Section (Straw Pro)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-banner'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A thoughtful place to begin' ) );
		$this->add_control( 'line1', array( 'label' => __( 'Headline Line 1', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A clearer,' ) );
		$this->add_control( 'emphasis_word', array( 'label' => __( 'Italic Accent Word', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'calmer' ) );
		$this->add_control( 'line2_suffix', array( 'label' => __( 'Line 2 Suffix', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'way' ) );
		$this->add_control( 'line3', array( 'label' => __( 'Headline Line 3', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'to begin.' ) );
		$this->add_control( 'body', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Explore weight-management information, learn what questions to ask, and request details about services that may be available. No pressure. No unsupported promises.' ) );
		$this->add_control( 'primary_text', array( 'label' => __( 'Primary Button Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Explore our approach' ) );
		$this->add_control( 'primary_url', array( 'label' => __( 'Primary Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#about' ) );
		$this->add_control( 'secondary_text', array( 'label' => __( 'Secondary Button Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'See how it works' ) );
		$this->add_control( 'secondary_url', array( 'label' => __( 'Secondary Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#process' ) );
		$this->add_control( 'image', array( 'label' => __( 'Hero Photo', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) ) );
		$this->add_control( 'float_badge', array( 'label' => __( 'Floating Card Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Your goals. Your pace.' ) );
		$this->add_control( 'float_sub', array( 'label' => __( 'Floating Card Subtitle', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Information made easier to navigate' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'extras', array( 'label' => __( 'Badges, Stats & Marquee', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'lang_mini', array( 'label' => __( 'Language Card — Mini Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'LIVE EN · ES' ) );
		$this->add_control( 'lang_title', array( 'label' => __( 'Language Card — Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Instant site translator' ) );
		$this->add_control( 'orbit_text', array( 'label' => __( 'Rotating Orbit Badge Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'CLEAR INFORMATION • YOUR PACE • ' ) );
		$this->add_control( 'scroll_label', array( 'label' => __( 'Scroll Indicator Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Scroll to explore' ) );

		$proof_rep = new \Elementor\Repeater();
		$proof_rep->add_control( 'value', array( 'label' => __( 'Stat Value', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'EN · ES' ) );
		$proof_rep->add_control( 'label', array( 'label' => __( 'Stat Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Instant live translator' ) );
		$this->add_control( 'proof', array(
			'label'       => __( 'Proof Stat Cards (3 recommended)', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $proof_rep->get_controls(),
			'default'     => array(
				array( 'value' => 'EN · ES', 'label' => 'Instant live translator' ),
				array( 'value' => '04', 'label' => 'Clear first stages' ),
				array( 'value' => '0', 'label' => 'Medical details collected here' ),
			),
			'title_field' => '{{{ value }}} — {{{ label }}}',
		) );
		$this->add_control( 'marquee_raw', array( 'label' => __( 'Scrolling Marquee Items (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Clear information\nInstant EN / ES translator\nNo pressure\nQuestions welcome\nYour pace matters" ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s     = $this->get_settings_for_display();
		$proof = array();
		foreach ( ( $s['proof'] ?? array() ) as $p ) {
			$proof[] = array( $p['value'] ?? '', $p['label'] ?? '' );
		}
		$marquee = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $s['marquee_raw'] ?? '' ) ) ) ) );
		$args    = array(
			'eyebrow'        => $s['eyebrow'] ?? '',
			'line1'          => $s['line1'] ?? '',
			'emphasis_word'  => $s['emphasis_word'] ?? '',
			'line2_suffix'   => $s['line2_suffix'] ?? '',
			'line3'          => $s['line3'] ?? '',
			'body'           => $s['body'] ?? '',
			'primary_text'   => $s['primary_text'] ?? '',
			'primary_url'    => $s['primary_url'] ?? '#about',
			'secondary_text' => $s['secondary_text'] ?? '',
			'secondary_url'  => $s['secondary_url'] ?? '#process',
			'image'          => ! empty( $s['image']['url'] ) ? $s['image']['url'] : 'hero-care.jpg',
			'float_badge'    => $s['float_badge'] ?? '',
			'float_sub'      => $s['float_sub'] ?? '',
			'lang_mini'      => $s['lang_mini'] ?? '',
			'lang_title'     => $s['lang_title'] ?? '',
			'orbit_text'     => $s['orbit_text'] ?? '',
			'scroll_label'   => $s['scroll_label'] ?? '',
		);
		if ( ! empty( $proof ) ) {
			$args['proof'] = $proof;
		}
		if ( ! empty( $marquee ) ) {
			$args['marquee'] = $marquee;
		}
		echo wla_render_hero_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Philosophy_Intro_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_philosophy_intro'; }
	public function get_title() { return __( 'WLA: Philosophy Intro (01)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-text-area'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Intro Content', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'index', array( 'label' => __( 'Section Index', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '01' ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'OUR POINT OF VIEW' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Your goals deserve clarity — not pressure.' ) );
		$this->add_control( 'body', array( 'label' => __( 'Drop-Cap Paragraph', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Weight management can involve many different choices. Our aim is to make the information easier to understand, explain what has and has not been confirmed, and help you prepare thoughtful questions for an appropriately qualified healthcare professional.' ) );
		$this->add_control( 'note', array( 'label' => __( 'Verification Note', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Business, clinical partner, and service details are clearly marked placeholders until verified by WeightLossAffordable.' ) );
		$this->add_control( 'link_label', array( 'label' => __( 'Link Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Read full About Us page' ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Link URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/about' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		echo wla_render_intro_section( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Service_Cards_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_service_cards'; }
	public function get_title() { return __( 'WLA: Service / Package Cards (3D Tilt)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'header_sec', array( 'label' => __( 'Section Header & Cards', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'index', array( 'label' => __( 'Section Index', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '02' ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => "WHAT YOU'LL FIND HERE" ) );
		$this->add_control( 'title', array( 'label' => __( 'Section Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Useful guidance, without assumptions.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Side Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'The cards below describe possible areas of support. Any clinical service, treatment, or partner must be confirmed before it is presented as available.' ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'tag', array( 'label' => __( 'Tag', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Information' ) );
		$rep->add_control( 'title', array( 'label' => __( 'Card Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Weight-management education' ) );
		$rep->add_control( 'pricePlaceholder', array( 'label' => __( 'Optional Price Placeholder', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$rep->add_control( 'billingPlaceholder', array( 'label' => __( 'Optional Billing Note', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$rep->add_control( 'text', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Accessible, plain-language resources to help you prepare questions.' ) );
		$rep->add_control( 'bullets_raw', array( 'label' => __( 'Optional Bullets (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '' ) );
		$rep->add_control( 'cta', array( 'label' => __( 'Link Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Explore guides' ) );
		$rep->add_control( 'href', array( 'label' => __( 'Link URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/resources' ) );
		$rep->add_control( 'featured', array( 'label' => __( 'Dark Featured Style', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
		) );
		$this->add_control( 'notice', array( 'label' => __( 'Optional Bottom Notice', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s     = $this->get_settings_for_display();
		$cards = array();
		foreach ( ( $s['cards'] ?? array() ) as $c ) {
			if ( ! empty( $c['bullets_raw'] ) ) {
				$c['bullets'] = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $c['bullets_raw'] ) ) ) );
			}
			$cards[] = $c;
		}
		$args = array(
			'index'   => $s['index'] ?? '02',
			'eyebrow' => $s['eyebrow'] ?? '',
			'title'   => $s['title'] ?? '',
			'intro'   => $s['intro'] ?? '',
			'notice'  => $s['notice'] ?? '',
		);
		if ( ! empty( $cards ) ) {
			$args['cards'] = $cards;
		}
		echo wla_render_services_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Steps_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_steps'; }
	public function get_title() { return __( 'WLA: Journey Board (4 Stages)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-number-field'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Journey Board', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'index', array( 'label' => __( 'Section Index', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '03' ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A SIMPLE STARTING POINT' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A clearer path, one step at a time.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro Copy', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'This is an illustrative four-stage information journey — inspired by modern telehealth clarity without making clinical promises.' ) );
		$this->add_control( 'window_left', array( 'label' => __( 'Browser Window Chrome — Left Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Your information journey' ) );
		$this->add_control( 'window_right', array( 'label' => __( 'Browser Window Chrome — Right Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '04 stages' ) );
		$this->add_control( 'chips_raw', array( 'label' => __( 'Summary Chips (one per line, 3 recommended)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Four clear stages\nIllustrative demo flow\nNo medical intake" ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'number', array( 'label' => __( 'Number', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '01' ) );
		$rep->add_control( 'title', array( 'label' => __( 'Stage Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Explore' ) );
		$rep->add_control( 'description', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Read plain-language guides at your own pace.' ) );
		$rep->add_control( 'meta', array( 'label' => __( 'Status Pill', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Start here' ) );
		$rep->add_control( 'detail', array( 'label' => __( 'Kicker Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Step 01 · Browse' ) );

		$this->add_control( 'steps', array(
			'label'       => __( 'Stages', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ number }}} — {{{ title }}}',
		) );
		$this->add_control( 'progress_label', array( 'label' => __( 'Progress Bar Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Journey stage' ) );
		$this->add_control( 'footnote', array( 'label' => __( 'Compliance Footnote', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Clinical eligibility, diagnosis, and treatment decisions are always reserved for appropriately licensed healthcare professionals.' ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'CTA Button', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Start with a question' ) );
		$this->add_control( 'cta_url', array( 'label' => __( 'CTA URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#contact' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s     = $this->get_settings_for_display();
		$steps = array();
		foreach ( ( $s['steps'] ?? array() ) as $idx => $item ) {
			$steps[] = array(
				$item['number'] ?? sprintf( '%02d', $idx + 1 ),
				$item['title'] ?? '',
				$item['description'] ?? '',
				$item['meta'] ?? '',
				$item['detail'] ?? '',
			);
		}
		$chips = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $s['chips_raw'] ?? '' ) ) ) ) );
		$args  = array(
			'index'          => $s['index'] ?? '03',
			'eyebrow'        => $s['eyebrow'] ?? '',
			'title'          => $s['title'] ?? '',
			'intro'          => $s['intro'] ?? '',
			'window_left'    => $s['window_left'] ?? '',
			'window_right'   => $s['window_right'] ?? '',
			'progress_label' => $s['progress_label'] ?? 'Journey stage',
			'footnote'       => $s['footnote'] ?? '',
			'cta_text'       => $s['cta_text'] ?? 'Start with a question',
			'cta_url'        => $s['cta_url'] ?? '#contact',
		);
		if ( ! empty( $steps ) ) {
			$args['steps'] = $steps;
		}
		if ( ! empty( $chips ) ) {
			$args['chips'] = $chips;
		}
		echo wla_render_journey_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Approach_Split_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_approach_split'; }
	public function get_title() { return __( 'WLA: Human Approach Split (04)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-image-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Approach Split', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A MORE HUMAN APPROACH' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Careful information can help you feel more prepared.' ) );
		$this->add_control( 'body', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Weight management is personal. There is no single path that fits everyone. Useful health information should respect different lived experiences, avoid judgment, and stay honest about what requires clinical review.' ) );
		$this->add_control( 'bullets_raw', array( 'label' => __( 'Check Items (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Plain-language education — never a substitute for medical advice\nNo guaranteed outcomes, fabricated transformations, or pressure tactics\nClinical decisions always belong to licensed healthcare professionals" ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Read our FAQs' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#faq' ) );
		$this->add_control( 'image', array( 'label' => __( 'Photo', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) ) );
		$this->add_control( 'photo_caption', array( 'label' => __( 'Photo Caption Badge', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Wellbeing is personal.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s       = $this->get_settings_for_display();
		$bullets = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $s['bullets_raw'] ?? '' ) ) ) ) );
		echo wla_render_approach_section( array(
			'eyebrow'       => $s['eyebrow'] ?? '',
			'title'         => $s['title'] ?? '',
			'body'          => $s['body'] ?? '',
			'bullets'       => $bullets,
			'button_text'   => $s['button_text'] ?? '',
			'button_url'    => $s['button_url'] ?? '#faq',
			'image'         => ! empty( $s['image']['url'] ) ? $s['image']['url'] : 'lifestyle-walk.jpg',
			'photo_caption' => $s['photo_caption'] ?? '',
		) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Pricing_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_pricing_cards'; }
	public function get_title() { return __( 'WLA: Pricing Transparency Panel (05)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-price-table'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Pricing Transparency Panel', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'PRICING & AVAILABILITY' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'The details should be clear before you decide.' ) );
		$this->add_control( 'body', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Live prices, packages, inclusions, and service availability have not been supplied for this demo. We never invent live pricing — view our editable package placeholders or ask for written terms.' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Link Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View package placeholders' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Link URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/pricing-and-packages' ) );
		$this->add_control( 'status', array( 'label' => __( 'Status Card Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Business details pending verification' ) );
		$this->add_control( 'sub', array( 'label' => __( 'Status Card Subtitle', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Transparency before commitment' ) );

		$row_rep = new \Elementor\Repeater();
		$row_rep->add_control( 'label', array( 'label' => __( 'Row Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Live pricing' ) );
		$row_rep->add_control( 'value', array( 'label' => __( 'Row Value', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'To confirm' ) );
		$this->add_control( 'rows', array(
			'label'       => __( 'Transparency Rows', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $row_rep->get_controls(),
			'default'     => array(
				array( 'label' => 'Live pricing', 'value' => 'To confirm' ),
				array( 'label' => 'Included services', 'value' => 'To confirm' ),
				array( 'label' => 'Eligibility terms', 'value' => 'To confirm' ),
			),
			'title_field' => '{{{ label }}} — {{{ value }}}',
		) );
		$this->add_control( 'note', array( 'label' => __( 'Footnote', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'No prices or treatment inclusions are advertised in this demo.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s    = $this->get_settings_for_display();
		$rows = array();
		foreach ( ( $s['rows'] ?? array() ) as $r ) {
			$rows[] = array( $r['label'] ?? '', $r['value'] ?? '' );
		}
		$args = array(
			'eyebrow'     => $s['eyebrow'] ?? '',
			'title'       => $s['title'] ?? '',
			'body'        => $s['body'] ?? '',
			'button_text' => $s['button_text'] ?? '',
			'button_url'  => $s['button_url'] ?? '/pricing-and-packages',
			'status'      => $s['status'] ?? '',
			'sub'         => $s['sub'] ?? '',
			'note'        => $s['note'] ?? '',
		);
		if ( ! empty( $rows ) ) {
			$args['rows'] = $rows;
		}
		echo wla_render_pricing_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Resources_Explorer_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_resources_explorer'; }
	public function get_title() { return __( 'WLA: Questions Worth Exploring (Interactive)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-slider-album'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Interactive Explorer', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'LEARN AT YOUR OWN PACE' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Questions worth exploring.' ) );
		$this->add_control( 'lead', array( 'label' => __( 'Intro Copy', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Select any guide below to preview practical conversation prompts, read the full educational draft, or bring better questions to a licensed healthcare professional.' ) );
		$this->add_control( 'all_link', array( 'label' => __( '"View Library" Link Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Open full resource library' ) );
		$this->add_control( 'all_url', array( 'label' => __( '"View Library" Link URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/resources' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'guides_sec', array( 'label' => __( 'Guide Cards (Stage + Playlist + Modal)', 'weightloss-affordable-core' ) ) );
		$g_rep = new \Elementor\Repeater();
		$g_rep->add_control( 'image', array( 'label' => __( 'Photo', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) ) );
		$g_rep->add_control( 'alt', array( 'label' => __( 'Photo Alt Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$g_rep->add_control( 'category', array( 'label' => __( 'Category', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Getting started' ) );
		$g_rep->add_control( 'readTime', array( 'label' => __( 'Read Time', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '4 min read' ) );
		$g_rep->add_control( 'prompt', array( 'label' => __( 'Italic Prompt Line', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Prepare for a thoughtful first conversation' ) );
		$g_rep->add_control( 'title', array( 'label' => __( 'Guide Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'What does a weight-management conversation include?' ) );
		$g_rep->add_control( 'text', array( 'label' => __( 'Stage Summary', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'A practical framework for organizing your goals, expectations, and questions before speaking with a qualified healthcare professional.' ) );
		$g_rep->add_control( 'questions_raw', array( 'label' => __( 'Questions to Bring (one per line, 3 recommended)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Which evidence-based options fit my health priorities?\nWhat realistic benefits, risks, and limitations apply?\nHow does ongoing follow-up and clinical support work?" ) );
		$g_rep->add_control( 'paragraphs_raw', array( 'label' => __( 'Full Modal Article (one paragraph per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Preparing a short list of questions ahead of an appointment can make a healthcare conversation calmer, clearer, and far more productive.' ) );
		$g_rep->add_control( 'disclaimer', array( 'label' => __( 'Educational Disclaimer', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Educational draft only — not individualized medical advice. Review with a qualified healthcare professional before publication.' ) );
		$g_rep->add_control( 'topicValue', array(
			'label'   => __( '"Ask about this topic" → Contact Form Topic', 'weightloss-affordable-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'general',
			'options' => array(
				'general'  => __( 'General information', 'weightloss-affordable-core' ),
				'services' => __( 'Services and availability', 'weightloss-affordable-core' ),
				'language' => __( 'Language support', 'weightloss-affordable-core' ),
				'website'  => __( 'Website help', 'weightloss-affordable-core' ),
			),
		) );
		$this->add_control( 'guides', array(
			'label'       => __( 'Guides (shown in Stage + Playlist + Modal Reader)', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $g_rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array(
					'category'       => 'Getting started',
					'readTime'       => '4 min read',
					'prompt'         => 'Prepare for a thoughtful first conversation',
					'title'          => 'What does a weight-management conversation include?',
					'text'           => 'A practical framework for organizing your goals, expectations, and questions before speaking with a qualified healthcare professional.',
					'questions_raw'  => "Which evidence-based options fit my health priorities?\nWhat realistic benefits, risks, and limitations apply?\nHow does ongoing follow-up and clinical support work?",
					'paragraphs_raw' => "Preparing a short list of questions ahead of an appointment can make a healthcare conversation calmer, clearer, and far more productive.\nStart by asking who provides clinical care, how professional licensure is verified, what an evaluation involves, and whether follow-up support is included over time.",
					'disclaimer'     => 'Educational draft only — not individualized medical advice. Review with a qualified healthcare professional before publication.',
					'topicValue'     => 'services',
				),
				array(
					'category'       => 'Everyday wellbeing',
					'readTime'       => '3 min read',
					'prompt'         => 'Build sustainable routines without all-or-nothing rules',
					'title'          => 'Small, practical habits that support everyday wellbeing',
					'text'           => 'Ideas for discussing movement, balanced meals, rest, and daily routines in ways that respect real life and individual health needs.',
					'questions_raw'  => "Which one small habit feels realistic for my schedule this week?\nHow can balanced meals support steady daily energy?\nWhat kind of accountability feels supportive rather than stressful?",
					'paragraphs_raw' => "Sustainable wellbeing rarely comes from rigid, punitive routines. Work schedules, family caregiving, culture, sleep, stress, and medical history all shape what works in everyday life.\nMany clinicians encourage focusing on approachable, repeatable habits — such as regular meals with adequate protein and fiber, enjoyable walking or gentle movement, and consistent sleep routines.",
					'disclaimer'     => 'General wellness education draft only. Always consult a qualified healthcare professional before changing exercise, nutrition, or medical plans.',
					'topicValue'     => 'general',
				),
				array(
					'category'       => 'Health literacy',
					'readTime'       => '4 min read',
					'prompt'         => 'Spot trustworthy transparency vs. unsupported hype',
					'title'          => 'How to evaluate telehealth & weight-loss claims online',
					'text'           => 'Essential questions to ask about authorship, clinical evidence, medication disclosures, pricing clarity, and privacy safeguards.',
					'questions_raw'  => "Who wrote and clinically reviewed the information on this page?\nAre risks, contraindications, and limitations stated clearly?\nWhere is sensitive health data collected and how is it protected?",
					'paragraphs_raw' => "Online weight-management websites vary widely in transparency. Taking two minutes to check how information is presented can protect both your health and your wallet.\nLook for clear identification of independent licensed medical providers, honest distinction between FDA-approved medications and compounded preparations, and realistic explanations that not every visitor qualifies for prescription treatment.",
					'disclaimer'     => 'Educational health-literacy draft only. Does not endorse or verify any specific provider, pharmacy, or treatment.',
					'topicValue'     => 'website',
				),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'guides_ui_sec', array( 'label' => __( 'Explorer UI Labels', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'featured_label', array( 'label' => __( 'Stage Badge Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Featured educational guide' ) );
		$this->add_control( 'read_button', array( 'label' => __( '"Read Guide" Button Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Read full guide' ) );
		$this->add_control( 'ask_button', array( 'label' => __( '"Ask About This" Button Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Ask about this topic' ) );
		$this->add_control( 'q_heading', array( 'label' => __( '"Questions to Bring" Card Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Questions to bring' ) );
		$this->add_control( 'q_note', array( 'label' => __( '"Questions to Bring" Card Footnote', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Educational conversation starters — not medical advice.' ) );
		$this->add_control( 'editorial_title', array( 'label' => __( 'Side Note Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Better questions create calmer conversations.' ) );
		$this->add_control( 'editorial_body', array( 'label' => __( 'Side Note Body', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Use these non-prescriptive guides to organize what matters most before speaking with an appropriately qualified clinician.' ) );
		$this->add_control( 'strip_raw', array( 'label' => __( 'Bottom Checklist Strip (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Prepare clear questions\nCheck authoritative sources\nUnderstand benefits & limits\nInstant English & Spanish switch" ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s             = $this->get_settings_for_display();
		$guides        = array();
		$default_cycle = array( 'hero-care.jpg', 'lifestyle-walk.jpg', 'nutrition.jpg' );
		$snapshot      = function_exists( 'wla_get_demo_snapshot' ) ? wla_get_demo_snapshot() : array();
		$resource_defaults = $snapshot['home']['resources'] ?? array();
		foreach ( ( $s['guides'] ?? array() ) as $idx => $g ) {
			$questions  = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $g['questions_raw'] ?? '' ) ) ) ) );
			$paragraphs = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $g['paragraphs_raw'] ?? '' ) ) ) ) );
			$resource_default = array();
			foreach ( $resource_defaults as $resource ) {
				if ( ! empty( $g['title'] ) && ( $g['title'] ?? '' ) === ( $resource['title'] ?? '' ) ) {
					$resource_default = $resource;
					break;
				}
			}
			$guides[]   = array(
				'image'      => ! empty( $g['image']['url'] ) ? $g['image']['url'] : ( $resource_default['image'] ?? $default_cycle[ $idx % count( $default_cycle ) ] ),
				'alt'        => $g['alt'] ?? ( $g['title'] ?? '' ),
				'category'   => $g['category'] ?? '',
				'readTime'   => $g['readTime'] ?? '',
				'prompt'     => $g['prompt'] ?? '',
				'title'      => $g['title'] ?? '',
				'text'       => $g['text'] ?? '',
				'questions'  => ! empty( $questions ) ? $questions : array( $g['text'] ?? '' ),
				'paragraphs' => ! empty( $paragraphs ) ? $paragraphs : array( $g['text'] ?? '' ),
				'disclaimer' => $g['disclaimer'] ?? '',
				'topicValue' => $g['topicValue'] ?? 'general',
				'slug'       => $resource_default['slug'] ?? sanitize_title( $g['title'] ?? ( 'guide-' . $idx ) ),
			);
		}
		$strip = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $s['strip_raw'] ?? '' ) ) ) ) );
		$args  = array(
			'eyebrow'         => $s['eyebrow'] ?? '',
			'title'           => $s['title'] ?? '',
			'lead'            => $s['lead'] ?? '',
			'all_link'        => $s['all_link'] ?? '',
			'all_url'         => $s['all_url'] ?? '/resources',
			'featured_label'  => $s['featured_label'] ?? '',
			'read_button'     => $s['read_button'] ?? '',
			'ask_button'      => $s['ask_button'] ?? '',
			'q_heading'       => $s['q_heading'] ?? '',
			'q_note'          => $s['q_note'] ?? '',
			'editorial_title' => $s['editorial_title'] ?? '',
			'editorial_body'  => $s['editorial_body'] ?? '',
		);
		if ( ! empty( $guides ) ) {
			$args['guides'] = $guides;
		}
		if ( ! empty( $strip ) ) {
			$args['strip'] = $strip;
		}
		echo wla_render_resources_explorer_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Reviews_Panel_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_reviews_panel'; }
	public function get_title() { return __( 'WLA: Authentic Reviews Panel (07)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-testimonial'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Reviews Panel', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'REAL PEOPLE, REAL RESPECT' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => "A place for authentic voices — when they're approved." ) );
		$this->add_control( 'body', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Sample review slots on this demo are explicitly labeled as illustrative placeholders rather than real patient outcomes.' ) );
		$this->add_control( 'badge', array( 'label' => __( 'Right Card Badge', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Verified review slot reserved' ) );
		$this->add_control( 'caption', array( 'label' => __( 'Right Card Note', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Replace demo cards in Elementor only with authorized, documented testimonials and required disclosures.' ) );
		$this->add_control( 'link_text', array( 'label' => __( 'Inline Link Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'See sample placeholder layout' ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Inline Link URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/patient-reviews' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		echo wla_render_reviews_section( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_FAQ_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_faq'; }
	public function get_title() { return __( 'WLA: Sticky FAQ Accordion (08)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-accordion'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'FAQ Accordion', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'GOOD QUESTIONS' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'A few things you may be wondering.' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Still have a question? Send a general inquiry or browse all 9 FAQ categories.' ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'question', array( 'label' => __( 'Question', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Are services or treatments live right now?' ) );
		$rep->add_control( 'answer', array( 'label' => __( 'Answer', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Business details and clinical offerings must be verified prior to launch.' ) );

		$this->add_control( 'questions', array(
			'label'       => __( 'Questions', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ question }}}',
		) );
		$this->add_control( 'cta_text', array( 'label' => __( '"Ask a Question" Link Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Ask a question' ) );
		$this->add_control( 'show_all', array( 'label' => __( 'Show "View all FAQs" Link', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'all_text', array( 'label' => __( '"View all FAQs" Link Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Open complete FAQ page', 'condition' => array( 'show_all' => 'yes' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s     = $this->get_settings_for_display();
		$pairs = array();
		foreach ( ( $s['questions'] ?? array() ) as $q ) {
			$pairs[] = array( $q['question'] ?? '', $q['answer'] ?? '' );
		}
		$args = array(
			'eyebrow'  => $s['eyebrow'] ?? 'GOOD QUESTIONS',
			'title'    => $s['title'] ?? 'A few things you may be wondering.',
			'intro'    => $s['intro'] ?? '',
			'cta_text' => $s['cta_text'] ?? 'Ask a question',
			'show_all' => 'yes' === ( $s['show_all'] ?? 'yes' ),
			'all_text' => $s['all_text'] ?? 'Open complete FAQ page',
		);
		if ( ! empty( $pairs ) ) {
			$args['faqs'] = $pairs;
		}
		echo wla_render_faq_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Contact_Section_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_contact_section'; }
	public function get_title() { return __( 'WLA: General Inquiry Contact Section (09)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Contact Section', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'index', array( 'label' => __( 'Section Index', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '09' ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => "WE'RE HERE TO HELP YOU FIND INFORMATION" ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'What would you like to know?' ) );
		$this->add_control( 'body', array( 'label' => __( 'Description', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Send a general, non-clinical question. Configure verified business email routing before launch.' ) );
		$this->add_control( 'privacy', array( 'label' => __( 'Privacy Note (next to shield icon)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Please don't share medical information. This demo inbox is for general inquiries only." ) );
		$this->add_control( 'checks_raw', array( 'label' => __( 'Trust Checklist Pills (one per line)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "General questions only\nNo medical intake\nInstant confirmation" ) );
		$this->end_controls_section();

		$this->start_controls_section( 'form_fields_sec', array( 'label' => __( 'Form Fields & Messages', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'form_head_title', array( 'label' => __( 'Form Card Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'General inquiry' ) );
		$this->add_control( 'form_head_sub', array( 'label' => __( 'Form Card Subtitle', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Non-clinical website questions' ) );
		$this->add_control( 'form_name_label', array( 'label' => __( '"Name" Field Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Your name' ) );
		$this->add_control( 'form_name_ph', array( 'label' => __( '"Name" Field Placeholder', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Elena Morales' ) );
		$this->add_control( 'form_email_label', array( 'label' => __( '"Email" Field Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Email address' ) );
		$this->add_control( 'form_email_ph', array( 'label' => __( '"Email" Field Placeholder', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'elena@example.com' ) );
		$this->add_control( 'form_topic_label', array( 'label' => __( '"Topic" Field Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'What is your question about?' ) );

		$topic_rep = new \Elementor\Repeater();
		$topic_rep->add_control( 'value', array( 'label' => __( 'Option Value', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'general', 'description' => __( 'Must be one of: general, services, language, website (used by the inquiry form handler).', 'weightloss-affordable-core' ) ) );
		$topic_rep->add_control( 'label', array( 'label' => __( 'Option Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'General information' ) );
		$this->add_control( 'topic_options', array(
			'label'       => __( 'Topic Dropdown Options', 'weightloss-affordable-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $topic_rep->get_controls(),
			'default'     => array(
				array( 'value' => 'general', 'label' => 'General information' ),
				array( 'value' => 'services', 'label' => 'Services and availability' ),
				array( 'value' => 'language', 'label' => 'Language support' ),
				array( 'value' => 'website', 'label' => 'Website help' ),
			),
			'title_field' => '{{{ label }}}',
		) );
		$this->add_control( 'consent_text', array( 'label' => __( 'Consent Checkbox Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'I agree to be contacted about this general inquiry and confirm I am not submitting health or medical information.' ) );
		$this->add_control( 'submit_text', array( 'label' => __( 'Submit Button Text', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Send general inquiry' ) );
		$this->add_control( 'success_message', array( 'label' => __( 'Success Message', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Thank you — your general inquiry has been saved. Configure verified business email routing prior to public launch.' ) );
		$this->add_control( 'error_message', array( 'label' => __( 'Error Message', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "We couldn't submit that request right now. Please check all required fields and try again." ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s      = $this->get_settings_for_display();
		$checks = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $s['checks_raw'] ?? '' ) ) ) ) );
		$topics = array();
		foreach ( ( $s['topic_options'] ?? array() ) as $opt ) {
			if ( '' !== trim( (string) ( $opt['value'] ?? '' ) ) ) {
				$topics[] = array( sanitize_key( $opt['value'] ), $opt['label'] ?? $opt['value'] );
			}
		}
		$args = array(
			'index'            => $s['index'] ?? '09',
			'eyebrow'          => $s['eyebrow'] ?? '',
			'title'            => $s['title'] ?? '',
			'body'             => $s['body'] ?? '',
			'privacy'          => $s['privacy'] ?? '',
			'form_head_title'  => $s['form_head_title'] ?? '',
			'form_head_sub'    => $s['form_head_sub'] ?? '',
			'form_name_label'  => $s['form_name_label'] ?? '',
			'form_name_ph'     => $s['form_name_ph'] ?? '',
			'form_email_label' => $s['form_email_label'] ?? '',
			'form_email_ph'    => $s['form_email_ph'] ?? '',
			'form_topic_label' => $s['form_topic_label'] ?? '',
			'consent_text'     => $s['consent_text'] ?? '',
			'submit_text'      => $s['submit_text'] ?? '',
			'success_message'  => $s['success_message'] ?? '',
			'error_message'    => $s['error_message'] ?? '',
		);
		if ( ! empty( $checks ) ) {
			$args['checks'] = $checks;
		}
		if ( ! empty( $topics ) ) {
			$args['topic_options'] = $topics;
		}
		echo wla_render_contact_section( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Subpage_Hero_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_subpage_hero'; }
	public function get_title() { return __( 'WLA: Subpage Editorial Hero', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-header'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Subpage Hero', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'WEIGHTLOSSAFFORDABLE' ) );
		$this->add_control( 'title', array( 'label' => __( 'Page Title', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Clarity, respect, and honest health literacy.' ) );
		$this->add_control( 'lead', array( 'label' => __( 'Lead Copy', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'badge', array( 'label' => __( 'Compliance Badge', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Placeholder profile — verify before launch' ) );
		$this->add_control( 'image', array( 'label' => __( 'Optional Right Photo', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s = $this->get_settings_for_display();
		echo wla_render_subpage_hero( array(
			'eyebrow' => $s['eyebrow'] ?? '',
			'title'   => $s['title'] ?? '',
			'lead'    => $s['lead'] ?? '',
			'badge'   => $s['badge'] ?? '',
			'image'   => ! empty( $s['image']['url'] ) ? $s['image']['url'] : '',
		) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Cta_Band_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_cta_band'; }
	public function get_title() { return __( 'WLA: Closing CTA Band (links to a page)', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-call-to-action'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'CTA Band', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'NEXT STEP' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => "Have a question we haven't covered?" ) );
		$this->add_control( 'body', array( 'label' => __( 'Body Copy', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Send a general, non-clinical inquiry and we will point you to the right information.' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Primary Button Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Open the Contact page' ) );
		$this->add_control( 'button_url', array( 'label' => __( 'Primary Button URL (a real page)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/contact' ) );
		$this->add_control( 'secondary_text', array( 'label' => __( 'Secondary Button Label', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Read the FAQs' ) );
		$this->add_control( 'secondary_url', array( 'label' => __( 'Secondary Button URL', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/frequently-asked-questions' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		echo wla_render_cta_band( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

class WLA_Page_Nav_Widget extends WLA_Base_Widget {
	public function get_name() { return 'wla_page_nav'; }
	public function get_title() { return __( 'WLA: Explore Other Pages Grid', 'weightloss-affordable-core' ); }
	public function get_icon() { return 'eicon-nav-menu'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Page Navigation Grid', 'weightloss-affordable-core' ) ) );
		$this->add_control( 'heading', array( 'label' => __( 'Section Heading', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Continue exploring other pages' ) );
		$this->add_control( 'current_slug', array( 'label' => __( 'Current Page Slug (excluded from grid)', 'weightloss-affordable-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->ensure_render_helpers() ) { return; }
		$s = $this->get_settings_for_display();
		echo wla_render_page_nav_strip( $s['current_slug'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
