<?php
/**
 * Elonix – Toolkit for Elementor Progress Bar Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Progress_Bar_Widget extends Elonix_Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
		wp_register_style( 'elonix-progress', ELONIX_ACC_URL . 'assets/css/widget-progress.css', [], ELONIX_VERSION );
	}

	public function get_name() {
		return 'es-progress';
	}

	public function get_title() {
		return esc_html__( 'Progress Bar', 'elonix' );
	}

	public function get_es_widget_icon() {
		return 'eicon-skill-bar';
	}

	public function get_keywords() {
		return [ 'progress', 'bar', 'skill', 'circle', 'step', 'eskit' ];
	}

	public function get_style_depends() {
		return [ 'elonix-widget-es-progress', 'elonix-progress' ];
	}

	public function get_script_depends() {
		return [ 'elonix-core-js', 'elonix-widget-es-progress' ];
	}

	protected function register_controls() {
		$this->register_content_general_controls();
		$this->register_content_data_controls();
		$this->register_content_repeater_controls();
		$this->register_content_media_controls();
		$this->register_content_marker_controls();
		$this->register_content_animation_controls();

		$this->register_style_container_controls();
		$this->register_style_header_controls();
		$this->register_style_title_controls();
		$this->register_style_subtitle_controls();
		$this->register_style_track_controls();
		$this->register_style_fill_controls();
		$this->register_style_marker_controls();
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
					'style-1' => esc_html__( 'Corporate Style', 'elonix' ),
				],
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'   => esc_html__( 'Layout', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'linear',
				'options' => [
					'linear'      => esc_html__( 'Linear', 'elonix' ),
					'circle'      => esc_html__( 'Circle', 'elonix' ),
					'semi-circle' => esc_html__( 'Semi Circle', 'elonix' ),
					'step'        => esc_html__( 'Step Progress', 'elonix' ),
					'multi'       => esc_html__( 'Multi Segment', 'elonix' ),
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => esc_html__( 'Header Alignment', 'elonix' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [ 'title' => 'Left', 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => 'Center', 'icon' => 'eicon-text-align-center' ],
					'flex-end' => [ 'title' => 'Right', 'icon' => 'eicon-text-align-right' ],
					'space-between' => [ 'title' => 'Space Between', 'icon' => 'eicon-justify-space-between' ]
				],
				'default' => 'space-between',
				'selectors' => [
					'{{WRAPPER}} .es-progress__header' => 'justify-content: {{VALUE}};'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_data_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'layout_mode!' => [ 'step', 'multi' ],
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'My Skill', 'elonix' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'current_value',
			[
				'label' => esc_html__( 'Current Value', 'elonix' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 80,
			]
		);

		$this->add_control(
			'max_value',
			[
				'label' => esc_html__( 'Maximum Value', 'elonix' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 100,
			]
		);

		$this->add_control(
			'display_format',
			[
				'label' => esc_html__( 'Value Text', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'percentage',
				'options' => [
					'percentage' => esc_html__( 'Percentage (%)', 'elonix' ),
					'actual'     => esc_html__( 'Actual Value', 'elonix' ),
				],
			]
		);

		$this->add_control(
			'prefix',
			[
				'label' => esc_html__( 'Prefix', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'suffix',
			[
				'label' => esc_html__( 'Suffix', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '%',
				'condition' => [ 'display_format' => 'actual' ]
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_repeater_controls() {
		$this->start_controls_section(
			'section_repeater',
			[
				'label' => esc_html__( 'Segments', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'layout_mode' => [ 'step', 'multi' ],
				],
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'title', [ 'label' => esc_html__( 'Title', 'elonix' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Item', 'dynamic' => [ 'active' => true ] ] );
		$repeater->add_control( 'current_value', [ 'label' => esc_html__( 'Value', 'elonix' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 25 ] );
		$repeater->add_control( 'item_color', [ 'label' => esc_html__( 'Custom Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR ] );
		$repeater->add_control( 'icon', [ 'label' => esc_html__( 'Icon', 'elonix' ), 'type' => \Elementor\Controls_Manager::ICONS ] );

		$this->add_control(
			'items',
			[
				'label' => esc_html__( 'Segments', 'elonix' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[ 'title' => esc_html__( 'Part 1', 'elonix' ), 'current_value' => 30 ],
					[ 'title' => esc_html__( 'Part 2', 'elonix' ), 'current_value' => 40 ],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_media_controls() {
		$this->start_controls_section(
			'section_media',
			[
				'label' => esc_html__( 'Media', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [ 'layout_mode!' => [ 'step', 'multi' ] ]
			]
		);
		$this->add_control( 'icon', [ 'label' => esc_html__( 'Title Icon', 'elonix' ), 'type' => \Elementor\Controls_Manager::ICONS ] );
		$this->end_controls_section();
	}

	protected function register_content_marker_controls() {
		$this->start_controls_section(
			'section_marker',
			[
				'label' => esc_html__( 'Marker & Targets', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control( 'show_marker', [ 'label' => esc_html__( 'Show Marker (Tooltip)', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_target', [ 'label' => esc_html__( 'Show Target Line', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no', 'condition' => [ 'layout_mode' => 'linear' ] ] );
		$this->add_control( 'target_value', [ 'label' => esc_html__( 'Target Percentage', 'elonix' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 80, 'condition' => [ 'show_target' => 'yes', 'layout_mode' => 'linear' ] ] );
		$this->end_controls_section();
	}

	protected function register_content_animation_controls() {
		$this->start_controls_section( 'section_animation', [ 'label' => esc_html__( 'Animation', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );
		$this->add_control( 'enable_animation', [ 'label' => esc_html__( 'Enable Fill Animation', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'enable_counter', [ 'label' => esc_html__( 'Enable Counter', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => [ 'enable_animation' => 'yes', 'layout_mode!' => ['multi', 'step'] ] ] );
		$this->add_control( 'animate_once', [ 'label' => esc_html__( 'Animate Once', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => [ 'enable_animation' => 'yes' ] ] );
		$this->add_control( 'restart_on_scroll', [ 'label' => esc_html__( 'Restart On Scroll', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no', 'condition' => [ 'animate_once!' => 'yes', 'enable_animation' => 'yes' ] ] );


		$this->add_responsive_control( 'animation_duration', [
			'label' => esc_html__( 'Animation Duration (ms)', 'elonix' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'ms' ],
			'range' => [ 'ms' => [ 'min' => 0, 'max' => 10000, 'step' => 100 ] ],
			'default' => [ 'unit' => 'ms', 'size' => 0 ],
			'frontend_available' => true,
			'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-duration: {{SIZE}}{{UNIT}};' ],
			'condition' => [ 'enable_animation' => 'yes' ]
		] );

		$this->add_responsive_control( 'animation_delay', [
			'label' => esc_html__( 'Animation Delay (ms)', 'elonix' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'ms' ],
			'range' => [ 'ms' => [ 'min' => 0, 'max' => 10000, 'step' => 100 ] ],
			'default' => [ 'unit' => 'ms', 'size' => 0 ],
			'frontend_available' => true,
			'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-delay: {{SIZE}}{{UNIT}};' ],
			'condition' => [ 'enable_animation' => 'yes' ]
		] );

		$this->add_control(
			'animation_curve',
			[
				'label' => esc_html__( 'Animation Curve', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'ease-in-out',
				'options' => [
					'ease' => 'Ease',
					'ease-in' => 'Ease In',
					'ease-out' => 'Ease Out',
					'ease-in-out' => 'Ease In Out',
					'linear' => 'Linear',
					'cubic-bezier(0.175, 0.885, 0.32, 1.275)' => 'Bounce',
					'cubic-bezier(0.68, -0.55, 0.265, 1.55)' => 'Elastic'
				],
				'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-easing: {{VALUE}};' ],
				'condition' => [ 'enable_animation' => 'yes' ]
			]
		);
		$this->end_controls_section();
	}

	protected function register_style_container_controls() {
		$this->start_controls_section( 'section_style_container', [ 'label' => esc_html__( 'Container', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_responsive_control( 'container_width', [ 'label' => esc_html__( 'Width', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px', '%', 'vw' ], 'selectors' => [ '{{WRAPPER}} .es-progress' => 'width: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'container_max_width', [ 'label' => esc_html__( 'Max Width', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px', '%', 'vw' ], 'selectors' => [ '{{WRAPPER}} .es-progress' => 'max-width: {{SIZE}}{{UNIT}};' ] ] );
		$this->start_controls_tabs( 'tabs_container_style' );
		$this->start_controls_tab( 'tab_container_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => 'container_bg', 'selector' => '{{WRAPPER}} .es-progress' ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'container_border', 'selector' => '{{WRAPPER}} .es-progress' ] );
		$this->add_responsive_control( 'container_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'container_shadow', 'selector' => '{{WRAPPER}} .es-progress' ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_container_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => 'container_bg_hover', 'selector' => '{{WRAPPER}} .es-progress:hover' ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'container_border_hover', 'selector' => '{{WRAPPER}} .es-progress:hover' ] );
		$this->add_responsive_control( 'container_radius_hover', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'container_shadow_hover', 'selector' => '{{WRAPPER}} .es-progress:hover' ] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control( 'container_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'container_margin', [ 'label' => esc_html__( 'Margin', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->end_controls_section();
	}

	protected function register_style_header_controls() {
		$this->start_controls_section( 'section_style_header', [ 'label' => esc_html__( 'Header', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_responsive_control( 'header_gap', [ 'label' => esc_html__( 'Gap', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .es-progress__header' => 'gap: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'header_margin_bottom', [ 'label' => esc_html__( 'Bottom Margin', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .es-progress__header' => 'margin-bottom: {{SIZE}}{{UNIT}};' ] ] );
		$this->end_controls_section();
	}

	protected function register_style_title_controls() {
		$this->start_controls_section( 'section_style_title', [ 'label' => esc_html__( 'Title', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'title_typography', 'selector' => '{{WRAPPER}} .es-progress__title' ] );
		$this->add_control( 'title_color', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress__title' => 'color: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), [ 'name' => 'title_shadow', 'selector' => '{{WRAPPER}} .es-progress__title' ] );
		$this->add_group_control( \Elementor\Group_Control_Text_Stroke::get_type(), [ 'name' => 'title_stroke', 'selector' => '{{WRAPPER}} .es-progress__title' ] );
		$this->add_responsive_control( 'title_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'title_margin', [ 'label' => esc_html__( 'Margin', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->end_controls_section();
	}

	protected function register_style_subtitle_controls() {
		$this->start_controls_section( 'section_style_subtitle', [ 'label' => esc_html__( 'Subtitle', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'subtitle_typography', 'selector' => '{{WRAPPER}} .es-progress__subtitle' ] );
		$this->add_control( 'subtitle_color', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress__subtitle' => 'color: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), [ 'name' => 'subtitle_shadow', 'selector' => '{{WRAPPER}} .es-progress__subtitle' ] );
		$this->add_responsive_control( 'subtitle_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__subtitle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'subtitle_margin', [ 'label' => esc_html__( 'Margin', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->end_controls_section();
	}

	protected function register_style_track_controls() {
		$this->start_controls_section( 'section_style_track', [ 'label' => esc_html__( 'Track', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_responsive_control( 'track_width', [ 'label' => esc_html__( 'Width', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px', '%' ], 'selectors' => [ '{{WRAPPER}} .es-progress__track' => 'width: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'track_height', [ 'label' => esc_html__( 'Height / Thickness', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-track-height: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'circle_size', [ 'label' => esc_html__( 'Circle Size', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 50, 'max' => 500 ] ], 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-circle-size: {{SIZE}}{{UNIT}};' ], 'condition' => [ 'layout_mode' => [ 'circle', 'semi-circle' ] ] ] );

		$this->start_controls_tabs( 'tabs_track_style' );
		$this->start_controls_tab( 'tab_track_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => 'track_bg', 'selector' => '{{WRAPPER}} .es-progress__track' ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'track_border', 'selector' => '{{WRAPPER}} .es-progress__track' ] );
		$this->add_responsive_control( 'track_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-track-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'track_shadow', 'selector' => '{{WRAPPER}} .es-progress__track' ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_track_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$this->add_control( 'track_hover_color', [ 'label' => esc_html__( 'Background Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-track-hover-color: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'track_border_hover', 'selector' => '{{WRAPPER}} .es-progress:hover .es-progress__track' ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'track_shadow_hover', 'selector' => '{{WRAPPER}} .es-progress:hover .es-progress__track' ] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_fill_controls() {
		$this->start_controls_section( 'section_style_fill', [ 'label' => esc_html__( 'Fill', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		$this->add_responsive_control( 'fill_height', [ 'label' => esc_html__( 'Height', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-fill-height: {{SIZE}}{{UNIT}};' ] ] );
		$this->start_controls_tabs( 'tabs_fill_style' );
		$this->start_controls_tab( 'tab_fill_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => 'fill_bg', 'selector' => '{{WRAPPER}} .es-progress__fill, {{WRAPPER}} .es-progress__circle-fill, {{WRAPPER}} .es-progress__segment' ] );
		$this->add_responsive_control( 'fill_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-fill-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'fill_shadow', 'selector' => '{{WRAPPER}} .es-progress__fill, {{WRAPPER}} .es-progress__segment' ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_fill_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$this->add_control( 'fill_hover_color', [ 'label' => esc_html__( 'Background Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-fill-hover-color: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'fill_shadow_hover', 'selector' => '{{WRAPPER}} .es-progress:hover .es-progress__fill, {{WRAPPER}} .es-progress:hover .es-progress__segment' ] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control( 'striped_fill', [ 'label' => esc_html__( 'Striped Effect', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'prefix_class' => 'es-progress--striped-', 'default' => 'no', 'separator' => 'before' ] );
		$this->add_control( 'animated_stripe', [ 'label' => esc_html__( 'Animated Stripes', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'prefix_class' => 'es-progress--animated-stripe-', 'condition' => [ 'striped_fill' => 'yes' ] ] );

		$this->add_control( 'glass_fill', [ 'label' => esc_html__( 'Glass Effect', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'selectors' => [ '{{WRAPPER}} .es-progress__fill' => 'backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);' ], 'separator' => 'before' ] );
		$this->end_controls_section();
	}

	protected function register_style_marker_controls() {
		$this->start_controls_section( 'section_style_marker', [ 'label' => esc_html__( 'Marker / Tooltip', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'marker_typography', 'selector' => '{{WRAPPER}} .es-progress__marker' ] );
		$this->start_controls_tabs( 'tabs_marker_style' );
		$this->start_controls_tab( 'tab_marker_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$this->add_control( 'marker_color', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-color: {{VALUE}};' ] ] );
		$this->add_control( 'marker_bg_color', [ 'label' => esc_html__( 'Background Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-bg: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'marker_border', 'selector' => '{{WRAPPER}} .es-progress__marker' ] );
		$this->add_responsive_control( 'marker_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__marker' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'marker_shadow', 'selector' => '{{WRAPPER}} .es-progress__marker' ] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_marker_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$this->add_control( 'marker_color_hover', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-hover-color: {{VALUE}};' ] ] );
		$this->add_control( 'marker_bg_color_hover', [ 'label' => esc_html__( 'Background Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-hover-bg: {{VALUE}};' ] ] );
		$this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'marker_border_hover', 'selector' => '{{WRAPPER}} .es-progress:hover .es-progress__marker' ] );
		$this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'marker_shadow_hover', 'selector' => '{{WRAPPER}} .es-progress:hover .es-progress__marker' ] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control( 'marker_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .es-progress__marker' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'marker_min_width', [ 'label' => esc_html__( 'Min Width', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px', '%' ], 'selectors' => [ '{{WRAPPER}} .es-progress__marker' => 'min-width: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'marker_offset_y', [ 'label' => esc_html__( 'Vertical Offset Y', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -100, 'max' => 100 ] ], 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-top: {{SIZE}}{{UNIT}};' ] ] );

		$this->add_control( 'marker_glass', [ 'label' => esc_html__( 'Backdrop Filter (Glass)', 'elonix' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'selectors' => [ '{{WRAPPER}} .es-progress__marker' => 'backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);' ], 'separator' => 'before' ] );

		$this->add_control( 'heading_marker_arrow', [ 'label' => esc_html__( 'Marker Arrow', 'elonix' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'layout_mode' => 'linear' ] ] );
		$this->add_responsive_control( 'marker_arrow_size', [ 'label' => esc_html__( 'Arrow Size', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 0, 'max' => 30 ] ], 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-arrow-size: {{SIZE}}{{UNIT}};' ], 'condition' => [ 'layout_mode' => 'linear' ] ] );
		$this->add_responsive_control( 'marker_arrow_x', [ 'label' => esc_html__( 'Arrow Offset X', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -50, 'max' => 50 ] ], 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-arrow-x: {{SIZE}}{{UNIT}};' ], 'condition' => [ 'layout_mode' => 'linear' ] ] );
		$this->add_responsive_control( 'marker_arrow_y', [ 'label' => esc_html__( 'Arrow Offset Y', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -50, 'max' => 50 ] ], 'selectors' => [ '{{WRAPPER}} .es-progress' => '--es-progress-marker-arrow-y: {{SIZE}}{{UNIT}};' ], 'condition' => [ 'layout_mode' => 'linear' ] ] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$layout = $settings['layout_mode'];
		$wrapper_classes = [
			'es-progress',
			'es-progress--' . esc_attr( $layout )
		];

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );
		$this->add_render_attribute( 'wrapper', 'role', 'progressbar' );

		$current = floatval( $settings['current_value'] );
		$max = floatval( $settings['max_value'] );

		// PRIORITY 1: Percentage must always be (Current Value / Maximum Value) * 100
		$percent = $max > 0 ? ( $current / $max ) * 100 : 0;
		if ( $percent > 100 ) $percent = 100;
		if ( $percent < 0 ) $percent = 0;

		if ( in_array( $layout, ['linear', 'circle', 'semi-circle'] ) ) {
			$this->add_render_attribute( 'wrapper', 'aria-valuemin', '0' );
			$this->add_render_attribute( 'wrapper', 'aria-valuemax', $max );
			$this->add_render_attribute( 'wrapper', 'aria-valuenow', $current );
			$this->add_render_attribute( 'wrapper', 'style', '--es-progress-value: ' . $percent . '%;' );

			$frontend_settings = [
				'enable_counter'     => 'yes' === $settings['enable_counter'],
				'enable_animation'   => 'yes' === $settings['enable_animation'],
				'animate_once'       => 'yes' === $settings['animate_once'],
				'restart_on_scroll'  => 'yes' === $settings['restart_on_scroll'],
				'animation_duration' => isset($settings['animation_duration']['size']) && '' !== $settings['animation_duration']['size'] ? (float) $settings['animation_duration']['size'] : 1500,
				'animation_delay'    => isset($settings['animation_delay']['size']) ? (float) $settings['animation_delay']['size'] : 0,
				'animation_curve'    => isset( $settings['animation_curve'] ) ? $settings['animation_curve'] : 'ease-in-out',
			];
			$this->add_render_attribute( 'wrapper', 'data-settings', wp_json_encode( $frontend_settings ) );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php
			if ( $layout === 'multi' ) {
				include __DIR__ . '/views/layout-multi.php';
			} elseif ( $layout === 'step' ) {
				include __DIR__ . '/views/layout-step.php';
			} elseif ( in_array( $layout, ['circle', 'semi-circle'] ) ) {
				include __DIR__ . '/views/layout-circle.php';
			} else {
				include __DIR__ . '/views/layout-linear.php';
			}
			?>
		</div>
		<?php
	}
}
