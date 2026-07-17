<?php
/**
 * Elonix – Toolkit for Elementor Advanced Breadcrumb Widget
 *
 * @package Elonix_Toolkit
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Breadcrumb_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-breadcrumb';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Breadcrumb', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-navigation-horizontal';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'breadcrumb', 'navigation', 'seo', 'yoast', 'rankmath', 'woocommerce' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-breadcrumb' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content - General settings
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'General Settings', 'elonix' ),
			)
		);

		$this->add_control(
			'breadcrumb_source',
			array(
				'label'   => esc_html__( 'Breadcrumb Source', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'        => esc_html__( 'Auto Detect (SEO Plugins First)', 'elonix' ),
					'wordpress'   => esc_html__( 'WordPress Core / Taxonomy fallback', 'elonix' ),
					'woocommerce' => esc_html__( 'WooCommerce Only', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'show_home',
			array(
				'label'        => esc_html__( 'Show Home', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'home_text',
			array(
				'label'       => esc_html__( 'Home Page Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Home', 'elonix' ),
				'placeholder' => esc_html__( 'Home', 'elonix' ),
				'condition'   => array(
					'show_home' => 'yes',
				),
			)
		);

		$this->add_control(
			'home_icon',
			array(
				'label'       => esc_html__( 'Home Icon', 'elonix' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
				'default'     => array(
					'value'   => 'fas fa-home',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'show_home' => 'yes',
				),
			)
		);

		$this->add_control(
			'home_icon_only',
			array(
				'label'        => esc_html__( 'Home Icon Only', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'condition'    => array(
					'show_home' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_current_page',
			array(
				'label'        => esc_html__( 'Show Current Page', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_parent_pages',
			array(
				'label'        => esc_html__( 'Show Parent Pages', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_category',
			array(
				'label'        => esc_html__( 'Show Categories', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_post_type',
			array(
				'label'        => esc_html__( 'Show Post Type Archive', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_archive',
			array(
				'label'        => esc_html__( 'Show Archives Name', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'truncate_title',
			array(
				'label'        => esc_html__( 'Truncate Long Title', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'truncate_limit',
			array(
				'label'     => esc_html__( 'Truncate Character Limit', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 30,
				),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'condition' => array(
					'truncate_title' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Content - Display text settings
		$this->start_controls_section(
			'section_display_text',
			array(
				'label' => esc_html__( 'Display Text Settings', 'elonix' ),
			)
		);

		$this->add_control(
			'search_text',
			array(
				'label'       => esc_html__( 'Search Page Label', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Search results for: ', 'elonix' ),
				'placeholder' => esc_html__( 'Search results for: ', 'elonix' ),
			)
		);

		$this->add_control(
			'error_text',
			array(
				'label'       => esc_html__( '404 Page Label', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Page Not Found (404)', 'elonix' ),
				'placeholder' => esc_html__( 'Page Not Found (404)', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// Content - Separator settings
		$this->start_controls_section(
			'section_separator',
			array(
				'label' => esc_html__( 'Separator Settings', 'elonix' ),
			)
		);

		$this->add_control(
			'separator_type',
			array(
				'label'   => esc_html__( 'Separator Type', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'raquo',
				'options' => array(
					'greater_than' => esc_html__( '>', 'elonix' ),
					'slash'        => esc_html__( '/', 'elonix' ),
					'double_slash' => esc_html__( '//', 'elonix' ),
					'arrow'        => esc_html__( '→', 'elonix' ),
					'raquo'        => esc_html__( '»', 'elonix' ),
					'pipe'         => esc_html__( '|', 'elonix' ),
					'custom_text'  => esc_html__( 'Custom Text', 'elonix' ),
					'custom_icon'  => esc_html__( 'Custom Icon', 'elonix' ),
					'none'         => esc_html__( 'None', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'separator_text',
			array(
				'label'       => esc_html__( 'Custom Separator Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '»',
				'placeholder' => '»',
				'condition'   => array(
					'separator_type' => 'custom_text',
				),
			)
		);

		$this->add_control(
			'separator_icon',
			array(
				'label'       => esc_html__( 'Custom Separator Icon', 'elonix' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
				'default'     => array(
					'value'   => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'separator_type' => 'custom_icon',
				),
			)
		);

		$this->add_control(
			'animated_separator',
			array(
				'label'        => esc_html__( 'Animated Separator (Hover)', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'condition'    => array(
					'separator_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();

		// Content - Schema options
		$this->start_controls_section(
			'section_advanced',
			array(
				'label' => esc_html__( 'SEO Schema Support', 'elonix' ),
			)
		);

		$this->add_control(
			'enable_schema',
			array(
				'label'        => esc_html__( 'Enable Breadcrumb Schema (JSON-LD)', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Injects structured JSON-LD BreadcrumbList schema into the page for search engine indexing.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// Content - Layout options
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Options', 'elonix' ),
			)
		);

		$this->add_control(
			'structure_type',
			array(
				'label'   => esc_html__( 'HTML Structure', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'list',
				'options' => array(
					'list'   => esc_html__( 'List (UL/LI)', 'elonix' ),
					'inline' => esc_html__( 'Inline (DIV/SPAN)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wrapped',
				'options' => array(
					'horizontal'  => esc_html__( 'Horizontal Scroll (No Wrap)', 'elonix' ),
					'wrapped'     => esc_html__( 'Wrapped (Multiline)', 'elonix' ),
					'inline_flex' => esc_html__( 'Inline Flex', 'elonix' ),
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumbs' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'mobile_optimized',
			array(
				'label'        => esc_html__( 'Mobile Optimization', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Enables horizontal scrollbars and optimized dimensions specifically for mobile devices.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// Styles - General Items
		$this->start_controls_section(
			'section_general_style',
			array(
				'label' => esc_html__( 'General Style', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'spacing_between_items',
			array(
				'label'     => esc_html__( 'Gap between Items', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 10,
				),
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-item:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-separator' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'breadcrumbs_padding',
			array(
				'label'      => esc_html__( 'Item Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumb-item, {{WRAPPER}} .elonix-breadcrumb-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'breadcrumbs_style_tabs' );

		// Normal link tab
		$this->start_controls_tab(
			'breadcrumbs_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'breadcrumbs_color',
			array(
				'label'     => esc_html__( 'Text / Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumb-item a, {{WRAPPER}} .elonix-breadcrumb-item' => 'color: {{VALUE}}',
					'{{WRAPPER}} .elonix-breadcrumb-item a svg path' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'breadcrumbs_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-breadcrumb-item, {{WRAPPER}} .elonix-breadcrumb-item a',
			)
		);

		$this->end_controls_tab();

		// Hover link tab
		$this->start_controls_tab(
			'breadcrumbs_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'breadcrumbs_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumb-item a:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .elonix-breadcrumb-item a:hover svg path' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		// Active/Current tab
		$this->start_controls_tab(
			'breadcrumbs_active_tab',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_control(
			'breadcrumbs_color_active',
			array(
				'label'     => esc_html__( 'Active Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumb-item.elonix-breadcrumb-last, {{WRAPPER}} .elonix-breadcrumb-item.elonix-breadcrumb-last span' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Styles - Separator
		$this->start_controls_section(
			'section_separator_style',
			array(
				'label'     => esc_html__( 'Separator Style', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'separator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumb-separator' => 'color: {{VALUE}}',
					'{{WRAPPER}} .elonix-breadcrumb-separator i, {{WRAPPER}} .elonix-breadcrumb-separator svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'separator_size',
			array(
				'label'     => esc_html__( 'Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 12,
				),
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumb-separator' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-breadcrumb-separator svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'separator_typography',
				'label'     => esc_html__( 'Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .elonix-breadcrumb-separator',
				'condition' => array(
					'separator_type!' => 'custom_icon',
				),
			)
		);

		$this->end_controls_section();

		// Styles - Current Item Styling
		$this->start_controls_section(
			'section_current_style',
			array(
				'label'     => esc_html__( 'Current Item Style', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_current_page' => 'yes',
				),
			)
		);

		$this->add_control(
			'current_item_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last, {{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last span' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'current_item_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'current_item_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last',
			)
		);

		$this->add_responsive_control(
			'current_item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'current_item_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumbs .elonix-breadcrumb-last' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Styles - Container
		$this->start_controls_section(
			'section_container_style',
			array(
				'label' => esc_html__( 'Container Style', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .elonix-breadcrumbs-container',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-breadcrumbs-container',
			)
		);

		$this->add_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumbs-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-breadcrumbs-container',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Container Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumbs-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_margin',
			array(
				'label'      => esc_html__( 'Container Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-breadcrumbs-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Separator Delimiter Markup.
	 *
	 * @param array $settings Widget settings.
	 * @return string Delimiter string (HTML or Text).
	 */
	private function get_separator_delimiter( $settings ) {
		$type = $settings['separator_type'];
		if ( 'none' === $type ) {
			return '';
		}

		if ( 'custom_icon' === $type ) {
			ob_start();
			Icons_Manager::render_icon( $settings['separator_icon'], array( 'aria-hidden' => 'true' ) );
			return '<span class="elonix-breadcrumb-separator-icon">' . ob_get_clean() . '</span>';
		}

		$text = '';
		switch ( $type ) {
			case 'greater_than':
				$text = '>';
				break;
			case 'slash':
				$text = '/';
				break;
			case 'double_slash':
				$text = '//';
				break;
			case 'arrow':
				$text = '→';
				break;
			case 'raquo':
				$text = '»';
				break;
			case 'pipe':
				$text = '|';
				break;
			case 'custom_text':
				$text = isset( $settings['separator_text'] ) ? $settings['separator_text'] : '»';
				break;
		}

		return '<span class="elonix-breadcrumb-separator-text">' . esc_html( $text ) . '</span>';
	}

	/**
	 * Render Breadcrumbs output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Fetch the calculated crumbs array from our reusable helper class
		$crumbs = Elonix_Breadcrumb_Helper::get_breadcrumbs( $settings );

		if ( empty( $crumbs ) ) {
			return;
		}

		$delimiter = $this->get_separator_delimiter( $settings );

		$container_tag = ( isset( $settings['structure_type'] ) && 'inline' === $settings['structure_type'] ) ? 'div' : 'ul';
		$item_tag      = ( isset( $settings['structure_type'] ) && 'inline' === $settings['structure_type'] ) ? 'span' : 'li';

		// Set layout classes
		$classes   = array( 'elonix-breadcrumbs' );
		$classes[] = 'structure-' . ( isset( $settings['structure_type'] ) ? $settings['structure_type'] : 'list' );
		$classes[] = 'layout-' . ( isset( $settings['layout'] ) ? $settings['layout'] : 'wrapped' );
		$classes[] = 'align-' . ( isset( $settings['align'] ) ? $settings['align'] : 'left' );
		if ( isset( $settings['mobile_optimized'] ) && 'yes' === $settings['mobile_optimized'] ) {
			$classes[] = 'mobile-optimized';
		}
		if ( isset( $settings['animated_separator'] ) && 'yes' === $settings['animated_separator'] ) {
			$classes[] = 'separator-animated';
		}

		// RENDER HTML COMPLIANT WITH WCAG ACCESSIBILITY
		echo '<nav class="elonix-breadcrumbs-container" aria-label="' . esc_attr__( 'Breadcrumb', 'elonix' ) . '">';
		echo '<' . esc_html( $container_tag ) . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		$total       = count( $crumbs );
		$position    = 1;
		$schema_list = array();

		foreach ( $crumbs as $index => $crumb ) {
			$is_last    = ( $index === $total - 1 );
			$item_class = isset( $crumb['class'] ) ? $crumb['class'] : '';

			if ( $is_last ) {
				$item_class .= ' elonix-breadcrumb-last';
			}

			echo '<' . esc_html( $item_tag ) . ' class="elonix-breadcrumb-item ' . esc_attr( trim( $item_class ) ) . '">';

			// Home icon/only logic
			if ( 0 === $index && 'yes' === $settings['show_home'] ) {
				echo '<span class="elonix-breadcrumb-home-icon">';
				Icons_Manager::render_icon( $settings['home_icon'], array( 'aria-hidden' => 'true' ) );
				echo '</span>';

				if ( 'yes' === $settings['home_icon_only'] ) {
					// Output title as hidden screen-reader text for accessibility
					echo '<span class="sr-only">' . wp_kses_post( $crumb['title'] ) . '</span>';
				}
			}

			// Render title (hide title text if home icon only is enabled on index 0)
			$show_title = true;
			if ( 0 === $index && 'yes' === $settings['show_home'] && 'yes' === $settings['home_icon_only'] ) {
				$show_title = false;
			}

			if ( ! empty( $crumb['url'] ) && ! $is_last ) {
				echo '<a href="' . esc_url( $crumb['url'] ) . '">';
				if ( $show_title ) {
					echo '<span>' . wp_kses_post( $crumb['title'] ) . '</span>';
				}
				echo '</a>';

				$schema_list[] = array(
					'position' => $position++,
					'name'     => $crumb['title'],
					'item'     => $crumb['url'],
				);
			} else {
				if ( $show_title ) {
					echo '<span aria-current="page">' . wp_kses_post( $crumb['title'] ) . '</span>';
				}

				$schema_list[] = array(
					'position' => $position++,
					'name'     => $crumb['title'],
					'item'     => ! empty( $crumb['url'] ) ? $crumb['url'] : home_url( add_query_arg( array(), $GLOBALS['wp']->request ) ),
				);
			}

			echo '</' . esc_html( $item_tag ) . '>';

			// Render separator delimiter
			if ( ! $is_last && 'none' !== $settings['separator_type'] ) {
				echo '<' . esc_html( $item_tag ) . ' class="elonix-breadcrumb-separator" aria-hidden="true">';
				echo $delimiter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Delimiter markup rendered safely.
				echo '</' . esc_html( $item_tag ) . '>';
			}
		}

		echo '</' . esc_html( $container_tag ) . '>';
		echo '</nav>';

		// INJECT JSON-LD SCHEMA IF ENABLED
		if ( isset( $settings['enable_schema'] ) && 'yes' === $settings['enable_schema'] && ! empty( $schema_list ) ) {
			$schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => array(),
			);

			foreach ( $schema_list as $schema_item ) {
				$schema['itemListElement'][] = array(
					'@type'    => 'ListItem',
					'position' => $schema_item['position'],
					'name'     => $schema_item['name'],
					'item'     => esc_url( $schema_item['item'] ),
				);
			}

			echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
		}
	}
}
