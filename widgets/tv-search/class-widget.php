<?php
/**
 * Elonix Search Widget Class
 *
 * Upgraded premium version supporting backdrop blur controls, grid layouts,
 * shimmering skeleton loading lines, and comprehensive spacing / size overrides.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

class Elonix_Toolkit_Search_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-search';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Search', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-search';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'search', 'find', 'ajax', 'live', 'filter', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-search' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-tv-search' );
	}

	/**
	 * Register Search widget controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		// 1. Layout Options Section
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Options', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => array(
					'classic'    => esc_html__( 'Classic Search', 'elonix' ),
					'modern'     => esc_html__( 'Modern Search', 'elonix' ),
					'minimal'    => esc_html__( 'Minimal Search', 'elonix' ),
					'overlay'    => esc_html__( 'Overlay Search', 'elonix' ),
					'fullscreen' => esc_html__( 'Fullscreen Search', 'elonix' ),
					'offcanvas'  => esc_html__( 'Offcanvas Search', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'       => esc_html__( 'Input Placeholder', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Search here...', 'elonix' ),
				'placeholder' => esc_html__( 'Search here...', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 2. Search Sources Section
		$this->start_controls_section(
			'section_sources',
			array(
				'label' => esc_html__( 'Search Sources', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// Get all public post types
		$post_types        = get_post_types( array( 'public' => true ), 'objects' );
		$post_type_options = array();
		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type->name, array( 'attachment', 'elementor_library' ), true ) ) {
				continue;
			}
			$post_type_options[ $post_type->name ] = $post_type->label;
		}

		$this->add_control(
			'search_sources',
			array(
				'label'       => esc_html__( 'Select Sources', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => array( 'post', 'page' ),
				'options'     => $post_type_options,
				'description' => esc_html__( 'Choose which post types to include in search results.', 'elonix' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Result Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'step'    => 1,
				'default' => 5,
			)
		);

		$this->end_controls_section();

		// 3. Search Results Content Controls
		$this->start_controls_section(
			'section_results_content',
			array(
				'label' => esc_html__( 'Search Results Cards', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'results_layout',
			array(
				'label'       => esc_html__( 'Results Layout Format', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'list',
				'options'     => array(
					'list'   => esc_html__( 'List View', 'elonix' ),
					'grid-2' => esc_html__( 'Grid 2 Columns', 'elonix' ),
					'grid-3' => esc_html__( 'Grid 3 Columns', 'elonix' ),
					'grid-4' => esc_html__( 'Grid 4 Columns', 'elonix' ),
				),
				'description' => esc_html__( 'Supported in Fullscreen and Offcanvas drop panels.', 'elonix' ),
			)
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => esc_html__( 'Show Feature Image', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Show Post Excerpt', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_category',
			array(
				'label'        => esc_html__( 'Show Category', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_author',
			array(
				'label'        => esc_html__( 'Show Author', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'show_date',
			array(
				'label'        => esc_html__( 'Show Publish Date', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// 1. Search Field Styles Section
		$this->start_controls_section(
			'section_style_field',
			array(
				'label' => esc_html__( 'Search Field', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'field_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'vw' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1200,
						'step' => 1,
					),
					'%'  => array(
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-field-wrapper' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-form'          => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'field_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 30,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_field_style' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_field_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'field_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_placeholder_color',
			array(
				'label'     => esc_html__( 'Placeholder Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'field_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'field_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input',
			)
		);

		$this->add_responsive_control(
			'field_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'field_typography',
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'field_placeholder_typography',
				'label'    => esc_html__( 'Placeholder Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input::placeholder',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_field_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'field_text_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'field_background_hover',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'field_border_hover',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:hover',
			)
		);

		$this->end_controls_tab();

		// Focus Tab
		$this->start_controls_tab(
			'tab_field_focus',
			array(
				'label' => esc_html__( 'Focus', 'elonix' ),
			)
		);

		$this->add_control(
			'field_text_color_focus',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'field_background_focus',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:focus',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'field_border_focus',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:focus',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_box_shadow_focus',
				'selector' => '{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'field_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-field-wrapper input.tv-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		// 2. Search Button Styles Section
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Search Button / Icon', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'button_icon_color',
			array(
				'label'     => esc_html__( 'Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-btn'         => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-btn svg'     => 'fill: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-btn, {{WRAPPER}} .tv-search-trigger-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-btn, {{WRAPPER}} .tv-search-trigger-btn',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn'         => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .tv-search-btn, {{WRAPPER}} .tv-search-trigger-btn',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'button_icon_color_hover',
			array(
				'label'     => esc_html__( 'Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-btn:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-btn:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_background_hover',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-btn:hover, {{WRAPPER}} .tv-search-trigger-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border_hover',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-btn:hover, {{WRAPPER}} .tv-search-trigger-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-search-btn:hover, {{WRAPPER}} .tv-search-trigger-btn:hover',
			)
		);

		$this->end_controls_tab();

		// Active Tab
		$this->start_controls_tab(
			'tab_button_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_control(
			'button_icon_color_active',
			array(
				'label'     => esc_html__( 'Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-btn:active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn:active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-search-btn:active svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .tv-search-trigger-btn:active svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_background_active',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-btn:active, {{WRAPPER}} .tv-search-trigger-btn:active',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border_active',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-btn:active, {{WRAPPER}} .tv-search-trigger-btn:active',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_active',
				'selector' => '{{WRAPPER}} .tv-search-btn:active, {{WRAPPER}} .tv-search-trigger-btn:active',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Responsive Button Width/Height Size and Spacing controls
		$this->add_responsive_control(
			'button_width',
			array(
				'label'      => esc_html__( 'Button Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 30,
						'max'  => 200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn'         => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'width: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'button_height',
			array(
				'label'      => esc_html__( 'Button Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 30,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn'         => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn'         => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn i'         => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-btn svg'       => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-trigger-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			array(
				'label'      => esc_html__( 'Icon Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-btn i, {{WRAPPER}} .tv-search-btn svg' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 3. Results Dropdown Styles Section
		$this->start_controls_section(
			'section_style_dropdown',
			array(
				'label' => esc_html__( 'Results Dropdown / Panel', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Dropdown Width & Height selectors
		$this->add_responsive_control(
			'dropdown_width',
			array(
				'label'      => esc_html__( 'Dropdown Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 200,
						'max'  => 1200,
						'step' => 1,
					),
					'%'  => array(
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-results-container' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_max_height',
			array(
				'label'      => esc_html__( 'Dropdown Max Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-results-container' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dropdown_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tv-search-results-container, .tv-search-overlay-wrapper, .tv-search-offcanvas-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dropdown_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-results-container',
			)
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-results-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dropdown_box_shadow',
				'selector' => '{{WRAPPER}} .tv-search-results-container',
			)
		);

		// WebKit Scrollbar Styling options
		$this->add_control(
			'scrollbar_thumb_color',
			array(
				'label'     => esc_html__( 'Scrollbar Thumb Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container::-webkit-scrollbar-thumb' => 'background-color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-results-container::-webkit-scrollbar-thumb' => 'background-color: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'scrollbar_track_color',
			array(
				'label'     => esc_html__( 'Scrollbar Track Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container::-webkit-scrollbar-track' => 'background-color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-results-container::-webkit-scrollbar-track' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 4. Result Item Cards Section
		$this->start_controls_section(
			'section_style_result_item',
			array(
				'label' => esc_html__( 'Result Item Cards', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_thumb_size',
			array(
				'label'      => esc_html__( 'Thumbnail Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 20,
						'max'  => 150,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-result-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_thumb_radius',
			array(
				'label'      => esc_html__( 'Thumbnail Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-result-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Card Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-result-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'card_gap',
			array(
				'label'      => esc_html__( 'Gap Between Cards', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-result-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-results-list.tv-results-layout-grid-2, {{WRAPPER}} .tv-search-results-list.tv-results-layout-grid-3, {{WRAPPER}} .tv-search-results-list.tv-results-layout-grid-4' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_result_item_style' );

		// Normal State Tab
		$this->start_controls_tab(
			'tab_item_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'item_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_title_typography',
				'label'    => esc_html__( 'Title Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-results-container .tv-search-result-title, .tv-search-overlay-wrapper .tv-search-result-title, .tv-search-offcanvas-wrapper .tv-search-result-title',
			)
		);

		$this->add_control(
			'item_excerpt_color',
			array(
				'label'     => esc_html__( 'Excerpt Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-excerpt' => 'color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-excerpt' => 'color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-excerpt' => 'color: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_excerpt_typography',
				'label'    => esc_html__( 'Excerpt Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-results-container .tv-search-result-excerpt, .tv-search-overlay-wrapper .tv-search-result-excerpt, .tv-search-offcanvas-wrapper .tv-search-result-excerpt',
			)
		);

		$this->add_control(
			'item_meta_color',
			array(
				'label'     => esc_html__( 'Meta Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-meta' => 'color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-meta' => 'color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-meta' => 'color: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_meta_typography',
				'label'    => esc_html__( 'Meta Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-results-container .tv-search-result-meta, .tv-search-overlay-wrapper .tv-search-result-meta, .tv-search-offcanvas-wrapper .tv-search-result-meta',
			)
		);

		$this->end_controls_tab();

		// Hover State Tab
		$this->start_controls_tab(
			'tab_item_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'item_bg_hover',
			array(
				'label'     => esc_html__( 'Card Hover Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-item:hover' => 'background-color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-item:hover' => 'background-color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-item:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'item_title_hover_color',
			array(
				'label'     => esc_html__( 'Title Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-item:hover .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-item:hover .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-item:hover .tv-search-result-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_hover_shadow',
				'label'    => esc_html__( 'Card Hover Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-result-item:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_hover_border',
				'label'    => esc_html__( 'Card Hover Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-search-result-item:hover',
			)
		);

		$this->end_controls_tab();

		// Selected State Tab (focused index)
		$this->start_controls_tab(
			'tab_item_selected',
			array(
				'label' => esc_html__( 'Selected', 'elonix' ),
			)
		);

		$this->add_control(
			'item_bg_active',
			array(
				'label'     => esc_html__( 'Focused Card Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-item.tv-selected' => 'background-color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-item.tv-selected' => 'background-color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-item.tv-selected' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'item_title_active_color',
			array(
				'label'     => esc_html__( 'Focused Card Title Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-results-container .tv-search-result-item.tv-selected .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-overlay-wrapper .tv-search-result-item.tv-selected .tv-search-result-title' => 'color: {{VALUE}};',
					'.tv-search-offcanvas-wrapper .tv-search-result-item.tv-selected .tv-search-result-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 5. Layout & Overlays Styling Section
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => esc_html__( 'Layout & Overlays', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'layout_container_width',
			array(
				'label'      => esc_html__( 'Container Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 200,
						'max'  => 1200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-layout-fullscreen .tv-search-form-inner' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-layout-offcanvas .tv-search-form-wrapper' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-layout-overlay .tv-search-form-wrapper' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'layout_container_height',
			array(
				'label'      => esc_html__( 'Container Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vh', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-layout-fullscreen .tv-search-form-inner' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-search-layout-offcanvas .tv-search-form-wrapper' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'layout_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}}'                          => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .tv-search-container'     => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .tv-search-field-wrapper' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'layout_overlay_color',
			array(
				'label'     => esc_html__( 'Backdrop Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-backdrop' => 'background-color: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'layout_blur_amount',
			array(
				'label'      => esc_html__( 'Backdrop Blur Amount', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 30,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-backdrop' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				),
			)
		);

		$this->add_control(
			'layout_animation_duration',
			array(
				'label'      => esc_html__( 'Animation Duration (ms)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'ms' ),
				'range'      => array(
					'ms' => array(
						'min'  => 100,
						'max'  => 2000,
						'step' => 50,
					),
				),
				'default'    => array(
					'unit' => 'ms',
					'size' => 300,
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-search-form-wrapper' => 'animation-duration: {{SIZE}}ms; transition-duration: {{SIZE}}ms;',
					'{{WRAPPER}} .tv-search-backdrop'     => 'animation-duration: {{SIZE}}ms; transition-duration: {{SIZE}}ms;',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Search Widget HTML output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$layout   = ! empty( $settings['layout'] ) ? $settings['layout'] : 'classic';

		// Form Configurations to pass to JS
		$placeholder = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : esc_html__( 'Search here...', 'elonix' );
		$limit       = ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : 5;
		$sources     = ! empty( $settings['search_sources'] ) ? (array) $settings['search_sources'] : array( 'post', 'page' );
		$res_layout  = ! empty( $settings['results_layout'] ) ? $settings['results_layout'] : 'list';

		// Display card details options
		$show_fields = array(
			'image'    => ( 'yes' === $settings['show_image'] ),
			'title'    => ( 'yes' === $settings['show_title'] ),
			'excerpt'  => ( 'yes' === $settings['show_excerpt'] ),
			'category' => ( 'yes' === $settings['show_category'] ),
			'author'   => ( 'yes' === $settings['show_author'] ),
			'date'     => ( 'yes' === $settings['show_date'] ),
		);

		// Wrapper properties
		$widget_id       = $this->get_id();
		$wrapper_classes = array(
			'tv-search-container',
			'tv-search-layout-' . $layout,
		);

		$wrapper_attrs = array(
			'class'               => esc_attr( implode( ' ', $wrapper_classes ) ),
			'id'                  => 'tv-search-' . esc_attr( $widget_id ),
			'data-widget-id'      => esc_attr( $widget_id ),
			'data-nonce'          => esc_attr( wp_create_nonce( 'tv_search_nonce' ) ),
			'data-limit'          => esc_attr( $limit ),
			'data-post-types'     => esc_attr( wp_json_encode( $sources ) ),
			'data-layout'         => esc_attr( $layout ),
			'data-show-fields'    => esc_attr( wp_json_encode( $show_fields ) ),
			'data-ajax-url'       => esc_url( admin_url( 'admin-ajax.php' ) ),
			'data-results-layout' => esc_attr( $res_layout ),
		);

		$attrs_str = '';
		foreach ( $wrapper_attrs as $key => $val ) {
			$attrs_str .= ' ' . $key . '="' . $val . '"';
		}

		?>
		<div<?php echo $attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			
			<?php if ( in_array( $layout, array( 'overlay', 'fullscreen', 'offcanvas' ), true ) ) : ?>
				<!-- Trigger Button for overlays/fullscreen/offcanvas -->
				<button class="tv-search-trigger-btn" aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open Search', 'elonix' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
						<path d="M10 2a8 8 0 0 1 6.32 12.9l4.39 4.38a1 1 0 0 1-1.42 1.42l-4.38-4.39A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/>
					</svg>
				</button>
			<?php endif; ?>

			<?php
			// Render Search Form container
			$form_classes = array( 'tv-search-form-wrapper' );
			if ( in_array( $layout, array( 'overlay', 'fullscreen', 'offcanvas' ), true ) ) {
				$form_classes[] = 'tv-search-panel-hidden';
			}
			?>
			
			<div class="<?php echo esc_attr( implode( ' ', $form_classes ) ); ?>">
				<?php if ( in_array( $layout, array( 'overlay', 'fullscreen', 'offcanvas' ), true ) ) : ?>
					<!-- Backdrop for modal overlays -->
					<div class="tv-search-backdrop" aria-hidden="true"></div>
				<?php endif; ?>

				<div class="tv-search-form-inner">
					
					<?php if ( in_array( $layout, array( 'fullscreen', 'offcanvas' ), true ) ) : ?>
						<!-- Close Button for Fullscreen/Offcanvas -->
						<button class="tv-search-close-btn" aria-label="<?php esc_attr_e( 'Close Search', 'elonix' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
								<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
							</svg>
						</button>
					<?php endif; ?>

					<form role="search" method="get" class="tv-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="tv-search-field-wrapper">
							<input 
								type="search" 
								class="tv-search-input" 
								name="s" 
								value="<?php echo esc_attr( get_search_query() ); ?>" 
								placeholder="<?php echo esc_attr( $placeholder ); ?>" 
								autocomplete="off" 
								aria-autocomplete="list" 
								aria-controls="tv-search-results-<?php echo esc_attr( $widget_id ); ?>" 
								aria-expanded="false"
							/>
							
							<button type="submit" class="tv-search-btn" aria-label="<?php esc_attr_e( 'Search Button', 'elonix' ); ?>">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
									<path d="M10 2a8 8 0 0 1 6.32 12.9l4.39 4.38a1 1 0 0 1-1.42 1.42l-4.38-4.39A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/>
								</svg>
							</button>
						</div>
					</form>

					<!-- Live Results Dropdown Container -->
					<div class="tv-search-results-container" id="tv-search-results-<?php echo esc_attr( $widget_id ); ?>" role="listbox" aria-label="<?php esc_attr_e( 'Search Results', 'elonix' ); ?>">
						<!-- Appended programmatically via AJAX -->
					</div>

					<!-- Screen Reader status updates -->
					<span class="screen-reader-text tv-search-sr-status" aria-live="polite"></span>
				</div>
			</div>
		</div>
		<?php
	}
}

// AJAX handler is loaded globally via plugin bootstrap
