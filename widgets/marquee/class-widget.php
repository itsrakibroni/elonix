<?php
/**
 * Elonix – Toolkit for Elementor Marquee Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Marquee_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'es-marquee';
	}

	public function get_title() {
		return esc_html__( 'Marquee', 'elonix' );
	}

	public function get_es_widget_icon() {
		return 'eicon-carousel';
	}

	public function get_keywords() {
		return [ 'marquee', 'ticker', 'scroller', 'carousel', 'text', 'eskit' ];
	}

	public function get_style_depends() {
		return [ 'elonix-widget-es-marquee' ];
	}

	public function get_script_depends() {
		return [ 'elonix-core-js', 'elonix-marquee-engine', 'elonix-widget-es-marquee' ];
	}

		protected function register_controls() {
		$this->register_content_general_controls();
		$this->register_content_items_controls();
		$this->register_content_layout_controls();
		$this->register_content_behavior_controls();
		$this->register_content_accessibility_controls();
		$this->register_content_icon_animation_controls();
		$this->register_style_container_controls();
		$this->register_style_track_controls();
		$this->register_style_item_controls();
		$this->register_style_text_controls();
		$this->register_style_icon_controls();
		$this->register_style_image_controls();
	}

	protected function register_content_general_controls() {
		$this->start_controls_section(
			'section_general',
			[
				'label' => esc_html__( 'General', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'skin',
			[
				'label'   => esc_html__( 'Skin', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => [
					'style-1' => esc_html__( 'Style One', 'elonix' ),
				],
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'   => esc_html__( 'Layout Mode', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'marquee',
				'options' => [
					'marquee' => esc_html__( 'Marquee', 'elonix' ),
					'ticker'  => esc_html__( 'Ticker', 'elonix' ),
				],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_items_controls() {
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Items', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'item_text',
			[
				'label'       => esc_html__( 'Title', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Announcement', 'elonix' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_description',
			[
				'label'       => esc_html__( 'Description', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_display_type',
			[
				'label'   => esc_html__( 'Display Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon'  => esc_html__( 'Icon', 'elonix' ),
					'image' => esc_html__( 'Image', 'elonix' ),
				],
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_icon',
			[
				'label' => esc_html__( 'Icon', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'item_display_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'item_image',
			[
				'label' => esc_html__( 'Image', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'item_display_type' => 'image',
				],
			]
		);

		$repeater->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name'      => 'item_image',
				'default'   => 'medium',
				'condition' => [
					'item_display_type' => 'image',
				],
			]
		);

		$repeater->add_control(
			'item_link',
			[
				'label' => esc_html__( 'Link', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::URL,
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'items',
			[
				'label'       => esc_html__( 'Items', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[ 'item_text' => esc_html__( 'Brand Development Strategy', 'elonix' ) ],
					[ 'item_text' => esc_html__( 'Leadership Training Program', 'elonix' ) ],
					[ 'item_text' => esc_html__( 'Digital Transformation', 'elonix' ) ],
				],
				'title_field' => '{{{ item_text }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'direction',
			[
				'label'   => esc_html__( 'Direction', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'   => esc_html__( 'Left', 'elonix' ),
					'right'  => esc_html__( 'Right', 'elonix' ),
					'top'    => esc_html__( 'Top', 'elonix' ),
					'bottom' => esc_html__( 'Bottom', 'elonix' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 100, 'max' => 1000, 'step' => 10 ],
				],
				'default' => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee--vertical .es-marquee__track' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'direction' => [ 'top', 'bottom' ],
				],
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'      => esc_html__( 'Gap', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 200, 'step' => 1 ],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--es-marquee-gap: {{SIZE}}{{UNIT}};',
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => esc_html__( 'Alignment', 'elonix' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'elonix' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Middle', 'elonix' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bottom', 'elonix' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .es-marquee__track .js-marquee' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'duplicated',
			[
				'label'        => esc_html__( 'Duplicated', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'duplicateCount',
			[
				'label'   => esc_html__( 'Duplicate Count', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min'     => 1,
				'max'     => 10,
				'condition' => [
					'duplicated' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_behavior_controls() {
		$this->start_controls_section(
			'section_behavior',
			[
				'label' => esc_html__( 'Behavior', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'animation_speed',
			[
				'label'      => esc_html__( 'Duration (s)', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [ 'min' => 1, 'max' => 100, 'step' => 1 ],
				],
				'default' => [
					'unit' => 's',
					'size' => 20,
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'        => esc_html__( 'Pause on Hover', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'es-marquee--pause-hover-',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'pause_on_cycle',
			[
				'label'        => esc_html__( 'Pause on Cycle', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'no',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'delay_before_start',
			[
				'label'   => esc_html__( 'Delay Before Start (ms)', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
				'step'    => 100,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'start_visible',
			[
				'label'        => esc_html__( 'Start Visible', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'allow_css3_support',
			[
				'label'        => esc_html__( 'Allow CSS3 Support', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_accessibility_controls() {
		$this->start_controls_section(
			'section_accessibility',
			[
				'label' => esc_html__( 'Accessibility', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'pause_button',
			[
				'label'        => esc_html__( 'Show Pause Button', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_icon_animation_controls() {
		$this->start_controls_section(
			'section_icon_animation',
			[
				'label' => esc_html__( 'Icon Animation', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_icon_animation',
			[
				'label'        => esc_html__( 'Enable Animation', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'icon_animation_type',
			[
				'label'   => esc_html__( 'Animation Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'esIconSpin',
				'options' => [
					'none'                => esc_html__( 'None', 'elonix' ),
					'esIconRotate'        => esc_html__( 'Rotate', 'elonix' ),
					'esIconRotateReverse' => esc_html__( 'Rotate Reverse', 'elonix' ),
					'esIconPulse'         => esc_html__( 'Pulse', 'elonix' ),
					'esIconBounce'        => esc_html__( 'Bounce', 'elonix' ),
					'esIconFloat'         => esc_html__( 'Float', 'elonix' ),
					'esIconWave'          => esc_html__( 'Wave', 'elonix' ),
					'esIconSwing'         => esc_html__( 'Swing', 'elonix' ),
					'esIconHeartbeat'     => esc_html__( 'Heartbeat', 'elonix' ),
					'esIconScale'         => esc_html__( 'Scale', 'elonix' ),
					'esIconSpin'          => esc_html__( 'Spin', 'elonix' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--es-icon-animation-name: {{VALUE}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_animation_speed',
			[
				'label'      => esc_html__( 'Animation Speed (s)', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [ 'min' => 0.2, 'max' => 20, 'step' => 0.1 ],
				],
				'default' => [
					'unit' => 's',
					'size' => 2,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--es-icon-animation-speed: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_animation_timing',
			[
				'label'   => esc_html__( 'Animation Timing', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'linear',
				'options' => [
					'linear' => 'Linear',
					'ease' => 'Ease',
					'ease-in' => 'Ease In',
					'ease-out' => 'Ease Out',
					'ease-in-out' => 'Ease In Out',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--es-icon-animation-timing: {{VALUE}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_animation_direction',
			[
				'label'   => esc_html__( 'Animation Direction', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'normal',
				'options' => [
					'normal' => 'Normal',
					'reverse' => 'Reverse',
					'alternate' => 'Alternate',
					'alternate-reverse' => 'Alternate Reverse',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--es-icon-animation-direction: {{VALUE}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_animation_delay',
			[
				'label'      => esc_html__( 'Animation Delay (s)', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [ 'min' => 0, 'max' => 20, 'step' => 0.1 ],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--es-icon-animation-delay: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_animation_iteration',
			[
				'label'   => esc_html__( 'Animation Iteration', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'infinite',
				'options' => [
					'infinite' => 'Infinite',
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--es-icon-animation-iteration: {{VALUE}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_transform_origin',
			[
				'label'   => esc_html__( 'Transform Origin', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'center' => 'Center',
					'top left' => 'Top Left',
					'top' => 'Top',
					'top right' => 'Top Right',
					'right' => 'Right',
					'bottom right' => 'Bottom Right',
					'bottom' => 'Bottom',
					'bottom left' => 'Bottom Left',
					'left' => 'Left',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--es-icon-transform-origin: {{VALUE}};',
				],
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_hover_animation',
			[
				'label'   => esc_html__( 'Hover Animation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'    => esc_html__( 'Normal', 'elonix' ),
					'play'    => esc_html__( 'Play Only On Hover', 'elonix' ),
					'pause'   => esc_html__( 'Pause On Hover', 'elonix' ),
					'reverse' => esc_html__( 'Reverse On Hover', 'elonix' ),
				],
				'prefix_class' => 'es-marquee--icon-hover-',
				'condition' => [
					'enable_icon_animation' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Style Tab
	}

	protected function register_style_container_controls() {
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'container_bg',
				'selector' => '{{WRAPPER}} .es-marquee__wrapper',
			]
		);

		$this->add_control(
			'container_overlay',
			[
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-marquee__wrapper::before' => 'background-color: {{VALUE}}; content: ""; position: absolute; inset: 0; z-index: 1;',
				],
			]
		);

		$this->add_control(
			'container_blend_mode',
			[
				'label'   => esc_html__( 'Blend Mode', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Normal', 'elonix' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn' => 'Color Burn',
					'hue' => 'Hue',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'exclusion' => 'Exclusion',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__wrapper' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .es-marquee__wrapper',
			]
		);

		$this->add_responsive_control(
			'container_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .es-marquee__wrapper',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name' => 'container_backdrop_filter',
				'selector' => '{{WRAPPER}} .es-marquee__wrapper',
				'fields_options' => [
					'blur' => [
						'selectors' => [
							'{{WRAPPER}} .es-marquee__wrapper' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
						],
					],
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_track_controls() {
		$this->start_controls_section(
			'section_style_track',
			[
				'label' => esc_html__( 'Track', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'track_bg',
				'selector' => '{{WRAPPER}} .es-marquee__track',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'track_border',
				'selector' => '{{WRAPPER}} .es-marquee__track',
			]
		);

		$this->add_responsive_control(
			'track_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__track' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'track_padding',
			[
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__track' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'track_height',
			[
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', '%' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 1000 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__track' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_item_controls() {
		$this->start_controls_section(
			'section_style_item',
			[
				'label' => esc_html__( 'Item', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_item_style' );

		$this->start_controls_tab(
			'tab_item_normal',
			[
				'label' => esc_html__( 'Normal', 'elonix' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'selector' => '{{WRAPPER}} .es-marquee__item',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .es-marquee__item',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_shadow',
				'selector' => '{{WRAPPER}} .es-marquee__item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_hover',
			[
				'label' => esc_html__( 'Hover', 'elonix' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'item_border_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_shadow_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover',
			]
		);

		$this->add_control(
			'item_hover_scale',
			[
				'label' => esc_html__( 'Hover Scale', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 2,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover' => 'transform: scale({{SIZE}});',
				],
			]
		);

		$this->add_control(
			'item_hover_translate',
			[
				'label' => esc_html__( 'Hover Translate Y', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => -50,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover' => 'transform: translateY({{SIZE}}px);', // Note: could combine scale and translate but simple for now
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'item_transition',
			[
				'label' => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item' => 'transition: all {{SIZE}}s ease;',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_margin',
			[
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_text_controls() {
		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Text & Typography', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_heading',
			[
				'label' => esc_html__( 'Title', 'elonix' ),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs( 'tabs_title_style' );

		$this->start_controls_tab(
			'tab_title_normal',
			[
				'label' => esc_html__( 'Normal', 'elonix' ),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--es-marquee-title-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .es-marquee__title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_hover',
			[
				'label' => esc_html__( 'Hover', 'elonix' ),
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover' => '--es-marquee-title-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover .es-marquee__title',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .es-marquee__title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .es-marquee__title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'desc_heading',
			[
				'label' => esc_html__( 'Description', 'elonix' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--es-marquee-desc-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .es-marquee__desc',
			]
		);

		$this->add_responsive_control(
			'desc_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__desc' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_icon_controls() {
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icon', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_icon_style' );

		$this->start_controls_tab(
			'tab_icon_normal',
			[
				'label' => esc_html__( 'Normal', 'elonix' ),
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--es-marquee-icon-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-marquee__icon' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Size', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}}' => '--es-marquee-icon-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'icon_border',
				'selector' => '{{WRAPPER}} .es-marquee__icon',
			]
		);

		$this->add_responsive_control(
			'icon_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_margin',
			[
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_rotation',
			[
				'label'      => esc_html__( 'Rotation', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [ 'min' => 0, 'max' => 360 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__icon' => 'transform: rotate({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_icon_hover',
			[
				'label' => esc_html__( 'Hover', 'elonix' ),
			]
		);

		$this->add_control(
			'icon_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover .es-marquee__icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_hover_bg_color',
			[
				'label'     => esc_html__( 'Hover Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover .es-marquee__icon' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'icon_border_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover .es-marquee__icon',
			]
		);

		$this->add_responsive_control(
			'icon_hover_rotation',
			[
				'label'      => esc_html__( 'Hover Rotation', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [ 'min' => 0, 'max' => 360 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__item:hover .es-marquee__icon' => 'transform: rotate({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'icon_transition',
			[
				'label' => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__icon' => 'transition: all {{SIZE}}s ease;',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_image_controls() {
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 1, 'max' => 1000 ],
					'%'  => [ 'min' => 1, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 1, 'max' => 1000 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 1, 'max' => 1000 ],
					'%'  => [ 'min' => 1, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_max_height',
			[
				'label'      => esc_html__( 'Max Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 1, 'max' => 1000 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image img' => 'max-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_object_fit',
			[
				'label'   => esc_html__( 'Object Fit', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'contain',
				'options' => [
					'fill'    => esc_html__( 'Fill', 'elonix' ),
					'cover'   => esc_html__( 'Cover', 'elonix' ),
					'contain' => esc_html__( 'Contain', 'elonix' ),
					'none'    => esc_html__( 'None', 'elonix' ),
					'scale-down' => esc_html__( 'Scale Down', 'elonix' ),
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__image img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'image_background',
				'selector' => '{{WRAPPER}} .es-marquee__image',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .es-marquee__image',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .es-marquee__image img' => 'border-radius: inherit;',
				],
			]
		);

		$this->add_responsive_control(
			'image_padding',
			[
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_margin',
			[
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .es-marquee__image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .es-marquee__image',
			]
		);

		$this->start_controls_tabs( 'tabs_image_style' );

		$this->start_controls_tab(
			'tab_image_normal',
			[
				'label' => esc_html__( 'Normal', 'elonix' ),
			]
		);

		$this->add_control(
			'image_opacity',
			[
				'label' => esc_html__( 'Opacity', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__image img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters',
				'selector' => '{{WRAPPER}} .es-marquee__image img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_image_hover',
			[
				'label' => esc_html__( 'Hover', 'elonix' ),
			]
		);

		$this->add_control(
			'image_hover_opacity',
			[
				'label' => esc_html__( 'Hover Opacity', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__item:hover .es-marquee__image img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters_hover',
				'selector' => '{{WRAPPER}} .es-marquee__item:hover .es-marquee__image img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'image_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .es-marquee__image, {{WRAPPER}} .es-marquee__image img' => 'transition: all {{SIZE}}s ease;',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$layout_mode = $settings['layout_mode'] ? $settings['layout_mode'] : 'marquee';
		$skin = $settings['skin'] ? $settings['skin'] : 'style-1';

		$this->add_render_attribute( 'wrapper', 'class', 'es-marquee' );
		$this->add_render_attribute( 'wrapper', 'class', 'es-marquee--' . $skin );
		$this->add_render_attribute( 'wrapper', 'class', 'es-marquee--' . $layout_mode );

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php
			$view_path = __DIR__ . '/views/' . $skin . '.php';
			if ( file_exists( $view_path ) ) {
				include $view_path;
			}
			?>
		</div>
		<?php
	}
}

