<?php
/**
 * Elonix Sticky Element Extension
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Sticky_Extension extends Elonix_Base_Extension {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'sticky';
	}

	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'elonix_sticky_section',
			[
				'label' => esc_html__( 'Elonix Sticky', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'tv_sticky_enable',
			[
				'label'        => esc_html__( 'Enable Sticky', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'tv-sticky-element tv-sticky-',
				'frontend_available' => true,
				'render_type'  => 'none',
			]
		);

		$element->add_control(
			'tv_sticky_devices',
			[
				'label'       => esc_html__( 'Device', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => [
					'desktop'      => esc_html__( 'Desktop', 'elonix' ),
					'laptop'       => esc_html__( 'Laptop', 'elonix' ),
					'tablet_extra' => esc_html__( 'Tablet Extra', 'elonix' ),
					'tablet'       => esc_html__( 'Tablet', 'elonix' ),
					'mobile'       => esc_html__( 'Mobile', 'elonix' ),
				],
				'default'     => [ 'desktop', 'laptop', 'tablet_extra', 'tablet', 'mobile' ],
				'condition'   => [
					'tv_sticky_enable' => 'yes',
				],
				'frontend_available' => true,
				'render_type'  => 'none',
			]
		);

		$element->add_control(
			'tv_sticky_trigger',
			[
				'label'     => esc_html__( 'Sticky Trigger', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'scroll_down' => esc_html__( 'On Scroll Down', 'elonix' ),
					'scroll_up'   => esc_html__( 'On Scroll Up', 'elonix' ),
				],
				'default'   => 'scroll_down',
				'condition' => [
					'tv_sticky_enable' => 'yes',
				],
				'frontend_available' => true,
				'render_type'  => 'none',
			]
		);

		$element->add_control(
			'tv_sticky_position',
			[
				'label'     => esc_html__( 'Sticky Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'sticky' => esc_html__( 'Sticky', 'elonix' ),
					'fixed'  => esc_html__( 'Fixed', 'elonix' ),
				],
				'default'   => 'sticky',
				'condition' => [
					'tv_sticky_enable' => 'yes',
				],
				'frontend_available' => true,
				'render_type'  => 'none',
			]
		);

		$element->add_control(
			'tv_sticky_top_offset',
			[
				'label'      => esc_html__( 'Top Offset', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range'      => [
					'px' => [
						'min' => -500,
						'max' => 500,
					],
				],
				'default'    => [
					'size' => 0,
					'unit' => 'px',
				],
				'condition'  => [
					'tv_sticky_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}}.tv-sticky-element' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$element->add_control(
			'tv_sticky_hide_on_scroll',
			[
				'label'        => esc_html__( 'Hide On Scroll', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition'    => [
					'tv_sticky_enable' => 'yes',
				],
				'frontend_available' => true,
				'render_type'  => 'none',
			]
		);

		$element->add_control(
			'tv_sticky_zindex',
			[
				'label'     => esc_html__( 'Z Index', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 9999,
				'default'   => 99,
				'condition' => [
					'tv_sticky_enable' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}.tv-sticky-element' => 'z-index: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'tv_sticky_bg_color',
			[
				'label'     => esc_html__( 'Background Color (Sticky State)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					'tv_sticky_enable' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}.tv-sticky-element.is-sticking' => 'background-color: {{VALUE}};',
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'tv_sticky_box_shadow',
				'label'     => esc_html__( 'Box Shadow (Sticky State)', 'elonix' ),
				'selector'  => '{{WRAPPER}}.tv-sticky-element.is-sticking',
				'condition' => [
					'tv_sticky_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'tv_sticky_transition_duration',
			[
				'label'      => esc_html__( 'Transition Duration', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'ms', 's' ],
				'range'      => [
					'ms' => [
						'min' => 0,
						'max' => 3000,
						'step' => 100,
					],
					's' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default'    => [
					'size' => 300,
					'unit' => 'ms',
				],
				'condition'  => [
					'tv_sticky_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}}.tv-sticky-element' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$element->add_responsive_control(
			'tv_sticky_border_radius',
			[
				'label'      => esc_html__( 'Border Radius (Sticky State)', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}}.tv-sticky-element.is-sticking' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'tv_sticky_enable' => 'yes',
				],
			]
		);

		$element->add_responsive_control(
			'tv_sticky_padding',
			[
				'label'      => esc_html__( 'Padding (Sticky State)', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}}.tv-sticky-element.is-sticking' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'tv_sticky_enable' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	public function before_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( empty( $settings['tv_sticky_enable'] ) || 'yes' !== $settings['tv_sticky_enable'] ) {
			return;
		}

		wp_enqueue_style( 'elonix-sticky-element' );
		wp_enqueue_script( 'elonix-sticky-element' );

		if ( $this->is_edit_mode() ) {
			return;
		}

		$config = [
			'devices'       => ! empty( $settings['tv_sticky_devices'] ) ? $settings['tv_sticky_devices'] : [ 'desktop', 'tablet', 'mobile' ],
			'trigger'       => ! empty( $settings['tv_sticky_trigger'] ) ? $settings['tv_sticky_trigger'] : 'scroll_down',
			'position'      => ! empty( $settings['tv_sticky_position'] ) ? $settings['tv_sticky_position'] : 'sticky',
			'hideOnScroll'  => ( ! empty( $settings['tv_sticky_hide_on_scroll'] ) && 'yes' === $settings['tv_sticky_hide_on_scroll'] ),
		];

		// Inject configuration
		$element->add_render_attribute(
			'_wrapper',
			'data-tv-sticky-config',
			wp_json_encode( $config )
		);
	}

	public function enqueue_assets() {
		// Conditionally load JS and CSS if needed? 
		// Elementor renders dynamically, we register here and JS will naturally do nothing if elements don't exist.
		// However, to strictly follow "Load CSS and JS ONLY when the page contains at least one Sticky element":
		// We'd have to use a flag. Wait, Elementor's wp_enqueue_scripts happens BEFORE rendering. 
		// We can enqueue during elementor/frontend/after_enqueue_scripts or simply register here and enqueue inside before_render.
		// Let's register here and enqueue in before_render.
		
		wp_register_style(
			'elonix-sticky-element',
			ELONIX_ACC_URL . 'assets/css/tv-sticky-element.css',
			[],
			ELONIX_VERSION
		);

		wp_register_script(
			'elonix-sticky-element',
			ELONIX_ACC_URL . 'assets/js/tv-sticky-element.js',
			[],
			ELONIX_VERSION,
			true
		);
		
		// Wait, Elementor frontend renders elements during wp_head/wp_footer. It's safe to enqueue registered assets inside before_render.
		// Let's modify before_render to enqueue these.
	}
}
