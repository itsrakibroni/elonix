<?php
/**
 * Elonix – Toolkit for Elementor Nav Menu Widget Class
 *
 * Status: PRODUCTION READY | FROZEN | THEMEFOREST READY
 * Last Updated: June 23, 2026
 *
 * Freeze Policy:
 * - NO New Features, Controls, Layouts, Effects, or Responsive Logic.
 * - ALLOWED: Bug Fixes, Security Fixes, WordPress Compatibility Updates, Elementor Compatibility Updates, and Performance Optimizations.
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
use Elementor\Icons_Manager;

class Elonix_Toolkit_Nav_Menu_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-nav-menu';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Nav Menu', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'navigation', 'menu', 'navbar', 'header', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-nav-menu' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-nav-menu' );
	}

	/**
	 * Fetch WordPress navigation menus as selectable options.
	 *
	 * @return array List of menus.
	 */
	public function get_menus() {
		$menus   = wp_get_nav_menus();
		$options = array();
		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				$options[ $menu->slug ] = $menu->name;
			}
		}
		return $options;
	}

	/**
	 * Register Nav Menu widget controls.
	 */
	protected function register_controls() {

		// CONTENT TAB — Menu Settings Section
		$this->start_controls_section(
			'section_menu',
			array(
				'label' => esc_html__( 'Menu', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'menu_preset',
			array(
				'label'        => esc_html__( 'Menu Layout Preset', 'elonix' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'classic',
				'options'      => array(
					'classic' => esc_html__( 'Classic Header Style', 'elonix' ),
					'modern'  => esc_html__( 'Modern Centered Style', 'elonix' ),
					'saas'    => esc_html__( 'Premium SaaS Style', 'elonix' ),
				),
				'prefix_class' => 'es-nav-menu-preset-',
			)
		);

		$this->add_control(
			'menu',
			array(
				'label'   => esc_html__( 'Select WordPress Menu', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_menus(),
			)
		);

		$this->add_control(
			'dropdown_trigger',
			array(
				'label'   => esc_html__( 'Dropdown Trigger', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hover',
				'options' => array(
					'hover' => esc_html__( 'Hover', 'elonix' ),
					'click' => esc_html__( 'Click', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'one_page_scroll',
			array(
				'label'        => esc_html__( 'Enable One-Page Scroll', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_controls_section();

		// CONTENT TAB — Layout Settings Section
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout_mode',
			array(
				'label'        => esc_html__( 'Layout Mode', 'elonix' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'horizontal',
				'options'      => array(
					'horizontal' => esc_html__( 'Horizontal', 'elonix' ),
					'vertical'   => esc_html__( 'Vertical', 'elonix' ),
					'centered'   => esc_html__( 'Centered', 'elonix' ),
					'split'      => esc_html__( 'Split Menu', 'elonix' ),
					'offcanvas'  => esc_html__( 'Offcanvas Desktop', 'elonix' ),
				),
				'prefix_class' => 'es-nav-menu-layout-',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => esc_html__( 'Horizontal Alignment', 'elonix' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-h-align-right',
					),
					'justify' => array(
						'title' => esc_html__( 'Justify', 'elonix' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'default'      => 'left',
				'prefix_class' => 'es-nav-menu-align-',
				'condition'    => array(
					'layout_mode' => array( 'horizontal', 'centered' ),
				),
			)
		);

		$this->add_control(
			'submenu_indicator',
			array(
				'label'   => esc_html__( 'Dropdown Indicator Icon', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'chevron',
				'options' => array(
					'chevron' => esc_html__( 'Chevron Down', 'elonix' ),
					'plus'    => esc_html__( 'Plus Icon', 'elonix' ),
					'caret'   => esc_html__( 'Caret Icon', 'elonix' ),
					'none'    => esc_html__( 'None', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// CONTENT TAB — Mobile Settings Section
		$this->start_controls_section(
			'section_mobile',
			array(
				'label' => esc_html__( 'Mobile', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'responsive_breakpoint',
			array(
				'label'   => esc_html__( 'Responsive Breakpoint', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'tablet' => esc_html__( 'Tablet & Mobile (1024px)', 'elonix' ),
					'mobile' => esc_html__( 'Mobile Only (767px)', 'elonix' ),
					'none'   => esc_html__( 'None (Always Desktop)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'mobile_layout',
			array(
				'label'   => esc_html__( 'Mobile Menu Layout', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'offcanvas_right',
				'options' => array(
					'offcanvas_left'  => esc_html__( 'Slide Offcanvas Left', 'elonix' ),
					'offcanvas_right' => esc_html__( 'Slide Offcanvas Right', 'elonix' ),
					'fullscreen'      => esc_html__( 'Full Screen Takeover', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'mobile_logo',
			array(
				'label' => esc_html__( 'Mobile Menu Header Logo', 'elonix' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'mobile_logo_link',
			array(
				'label'   => esc_html__( 'Logo Redirect URL', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'home',
				'options' => array(
					'home'   => esc_html__( 'Home Page Default', 'elonix' ),
					'custom' => esc_html__( 'Custom Link URL', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'mobile_logo_custom_url',
			array(
				'label'     => esc_html__( 'Custom Logo Redirect Link', 'elonix' ),
				'type'      => Controls_Manager::URL,
				'condition' => array(
					'mobile_logo_link' => 'custom',
				),
			)
		);

		$this->add_control(
			'mobile_submenu_click',
			array(
				'label'   => esc_html__( 'Mobile Submenu Expand Trigger', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => array(
					'icon' => esc_html__( 'Indicator Caret Icon Only', 'elonix' ),
					'item' => esc_html__( 'Entire Link Text Item', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'mobile_accordion_duration',
			array(
				'label'   => esc_html__( 'Accordion Slide Duration (ms)', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 300,
				'min'     => 100,
				'max'     => 1000,
				'step'    => 50,
			)
		);

		$this->add_control(
			'mobile_accordion_easing',
			array(
				'label'   => esc_html__( 'Accordion Easing', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ease-in-out',
				'options' => array(
					'linear'      => esc_html__( 'Linear', 'elonix' ),
					'ease'        => esc_html__( 'Ease', 'elonix' ),
					'ease-in'     => esc_html__( 'Ease In', 'elonix' ),
					'ease-out'    => esc_html__( 'Ease Out', 'elonix' ),
					'ease-in-out' => esc_html__( 'Ease In Out', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'toggler_open_icon',
			array(
				'label'   => esc_html__( 'Hamburger Menu Open Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-bars',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'toggler_close_icon',
			array(
				'label'   => esc_html__( 'Hamburger Menu Close Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-times',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'toggler_label',
			array(
				'label'       => esc_html__( 'Toggler Extra Text Label', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. MENU', 'elonix' ),
			)
		);

		$this->add_control(
			'mobile_close_icon',
			array(
				'label'   => esc_html__( 'Mobile Panel Close Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-times',
					'library' => 'fa-solid',
				),
			)
		);

		$this->end_controls_section();

		// CONTENT TAB — Accessibility Settings Section
		$this->start_controls_section(
			'section_accessibility',
			array(
				'label' => esc_html__( 'Accessibility', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_keyboard_nav',
			array(
				'label'        => esc_html__( 'Enable Keyboard Navigation', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'enable_focus_ring',
			array(
				'label'        => esc_html__( 'Show Focus Ring Outline', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'focus_ring_color',
			array(
				'label'     => esc_html__( 'Focus Ring Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => array(
					'{{WRAPPER}} .es-navbar-nav a:focus, {{WRAPPER}} button:focus' => 'outline-color: {{VALUE}} !important;',
				),
				'condition' => array(
					'enable_focus_ring' => 'yes',
				),
			)
		);

		$this->add_control(
			'focus_ring_width',
			array(
				'label'      => esc_html__( 'Outline Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 5,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-navbar-nav a:focus, {{WRAPPER}} button:focus' => 'outline-width: {{SIZE}}{{UNIT}} !important; outline-style: solid !important;',
				),
				'condition'  => array(
					'enable_focus_ring' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		/*
		 * =========================================================================
		 * STYLE TAB OPTIONS
		 * =========================================================================
		 */

		// STYLE TAB — Desktop Menu Styling
		$this->start_controls_section(
			'section_style_items',
			array(
				'label' => esc_html__( 'Desktop Menu', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// States Tabs
		$this->start_controls_tabs( 'desktop_menu_tabs' );

		// Normal Tab
		$this->start_controls_tab(
			'desktop_menu_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'heading_desktop_menu_container_style',
			array(
				'label' => esc_html__( 'Menu Container', 'elonix' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'container_width',
			array(
				'label'      => esc_html__( 'Container Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_max_width',
			array(
				'label'      => esc_html__( 'Container Max Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container',
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Container Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Container Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_margin',
			array(
				'label'      => esc_html__( 'Container Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_height',
			array(
				'label'      => esc_html__( 'Container Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_min_height',
			array(
				'label'      => esc_html__( 'Container Min Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_max_height',
			array(
				'label'      => esc_html__( 'Container Max Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_desktop_menu_items_style',
			array(
				'label'     => esc_html__( 'Menu Items', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'items_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a',
			)
		);

		$this->add_responsive_control(
			'items_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a .es-submenu-indicator' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'items_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'items_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a',
			)
		);

		$this->add_responsive_control(
			'items_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'items_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'items_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'items_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'desktop_menu_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'items_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:hover > a .es-submenu-indicator' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'items_background_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a:hover',
			)
		);

		$this->add_control(
			'items_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'items_shadow_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a:hover',
			)
		);

		$this->end_controls_tab();

		// Active Tab
		$this->start_controls_tab(
			'desktop_menu_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'items_color_active',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-ancestor > a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-item > a .es-submenu-indicator, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-ancestor > a .es-submenu-indicator' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'items_background_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-ancestor > a',
			)
		);

		$this->add_control(
			'items_border_color_active',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-ancestor > a' => 'border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'items_shadow_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li.current-menu-ancestor > a',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Extra caret size and heights
		$this->add_control(
			'desktop_menu_extra_divider_label',
			array(
				'label'     => esc_html__( 'Extra Caret & Height Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'indicator_size',
			array(
				'label'      => esc_html__( 'Indicator Caret Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-submenu-indicator' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'indicator_color',
			array(
				'label'     => esc_html__( 'Indicator Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-submenu-indicator' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'indicator_spacing',
			array(
				'label'      => esc_html__( 'Indicator Left Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-submenu-indicator' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'items_height',
			array(
				'label'      => esc_html__( 'Item Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'height: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center;',
				),
			)
		);

		$this->add_responsive_control(
			'items_min_height',
			array(
				'label'      => esc_html__( 'Item Min Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a' => 'min-height: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center;',
				),
			)
		);

		// Spacing Section (Gap Between Items, Horizontal Gap, Vertical Gap)
		$this->add_control(
			'heading_desktop_menu_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'items_gap',
			array(
				'label'      => esc_html__( 'Gap Between Items', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: 0;',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-nav-menu-layout-vertical .es-navbar-nav > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right: 0;',
				),
			)
		);

		$this->add_responsive_control(
			'items_horizontal_gap',
			array(
				'label'      => esc_html__( 'Horizontal Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-nav-menu-layout-vertical .es-navbar-nav > li' => 'margin-right: 0;',
				),
			)
		);

		$this->add_responsive_control(
			'items_vertical_gap',
			array(
				'label'      => esc_html__( 'Vertical Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-nav-menu-layout-vertical .es-navbar-nav > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Dropdown Style Section
		$this->start_controls_section(
			'section_style_dropdown',
			array(
				'label' => esc_html__( 'Desktop Dropdown', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Container Styling
		$this->add_control(
			'heading_dropdown_container',
			array(
				'label' => esc_html__( 'Dropdown Container', 'elonix' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		// Container Tabs
		$this->start_controls_tabs( 'dropdown_container_tabs' );

		// Normal Container Tab
		$this->start_controls_tab(
			'dropdown_container_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'dropdown_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 150,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_min_width',
			array(
				'label'      => esc_html__( 'Min Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 600,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'min-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dropdown_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dropdown_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown',
			)
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dropdown_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown',
			)
		);

		$this->add_control(
			'dropdown_zindex',
			array(
				'label'     => esc_html__( 'Z-Index Override', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 999,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'z-index: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Container Tab
		$this->start_controls_tab(
			'dropdown_container_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dropdown_background_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dropdown_border_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown:hover',
			)
		);

		$this->end_controls_tab();

		// Active Container Tab
		$this->start_controls_tab(
			'dropdown_container_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dropdown_background_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container li.current-menu-item > .es-dropdown, {{WRAPPER}} .es-nav-menu-desktop-container li.current-menu-ancestor > .es-dropdown',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dropdown_border_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container li.current-menu-item > .es-dropdown, {{WRAPPER}} .es-nav-menu-desktop-container li.current-menu-ancestor > .es-dropdown',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Dropdown Items Styling
		$this->add_control(
			'heading_dropdown_items',
			array(
				'label'     => esc_html__( 'Dropdown Items', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Dropdown Items Tabs
		$this->start_controls_tabs( 'dropdown_items_tabs' );

		// Normal Items Tab
		$this->start_controls_tab(
			'dropdown_items_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dropdown_item_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a',
			)
		);

		$this->add_responsive_control(
			'dropdown_item_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a .es-submenu-indicator' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dropdown_item_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a',
			)
		);

		$this->end_controls_tab();

		// Hover Items Tab
		$this->start_controls_tab(
			'dropdown_items_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_color_hover',
			array(
				'label'     => esc_html__( 'Text Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li:hover > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a:hover .es-submenu-indicator, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li:hover > a .es-submenu-indicator' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_bg_hover',
			array(
				'label'     => esc_html__( 'Background Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li:hover > a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active Items Tab
		$this->start_controls_tab(
			'dropdown_items_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_color_active',
			array(
				'label'     => esc_html__( 'Text Color (Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-ancestor > a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-item > a .es-submenu-indicator, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-ancestor > a .es-submenu-indicator' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_bg_active',
			array(
				'label'     => esc_html__( 'Background Color (Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li.current-menu-ancestor > a' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Dropdown Spacing
		$this->add_control(
			'heading_dropdown_spacing',
			array(
				'label'     => esc_html__( 'Dropdown Items Spacing & Heights', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'dropdown_item_padding',
			array(
				'label'      => esc_html__( 'Item Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_margin',
			array(
				'label'      => esc_html__( 'Item Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_gap',
			array(
				'label'      => esc_html__( 'Dropdown Item Vertical Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_height',
			array(
				'label'      => esc_html__( 'Item Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a' => 'height: {{SIZE}}{{UNIT}}; display: flex; align-items: center;',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_item_min_height',
			array(
				'label'      => esc_html__( 'Item Min Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li a' => 'min-height: {{SIZE}}{{UNIT}}; display: flex; align-items: center;',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_submenu_indentation',
			array(
				'label'      => esc_html__( 'Nested Submenu Indentation', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown li .es-dropdown' => 'padding-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Dropdown Position
		$this->add_control(
			'heading_dropdown_position',
			array(
				'label'     => esc_html__( 'Dropdown Position', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'dropdown_horizontal_align',
			array(
				'label'        => esc_html__( 'Horizontal Alignment', 'elonix' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'left',
				'options'      => array(
					'left'   => esc_html__( 'Left', 'elonix' ),
					'center' => esc_html__( 'Center', 'elonix' ),
					'right'  => esc_html__( 'Right', 'elonix' ),
				),
				'prefix_class' => 'es-dropdown-align%s-',
			)
		);

		$this->add_responsive_control(
			'dropdown_offset_x',
			array(
				'label'      => esc_html__( 'Custom Offset X', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => '--es-dropdown-offset-x: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_offset_y',
			array(
				'label'      => esc_html__( 'Custom Offset Y', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => '--es-dropdown-offset-y: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Dropdown Animations
		$this->add_control(
			'heading_dropdown_animation',
			array(
				'label'     => esc_html__( 'Dropdown Animation', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'dropdown_transition',
			array(
				'label'   => esc_html__( 'Entrance Animation', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => array(
					'fade'       => esc_html__( 'Fade', 'elonix' ),
					'slide-down' => esc_html__( 'Slide Down', 'elonix' ),
					'slide-up'   => esc_html__( 'Slide Up', 'elonix' ),
					'zoom'       => esc_html__( 'Zoom In', 'elonix' ),
					'scale'      => esc_html__( 'Scale', 'elonix' ),
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_anim_duration',
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
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'transition-duration: {{SIZE}}ms !important; --es-dropdown-anim-duration: {{SIZE}}ms;',
				),
			)
		);

		$this->add_responsive_control(
			'dropdown_anim_delay',
			array(
				'label'      => esc_html__( 'Animation Delay (ms)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'ms' ),
				'range'      => array(
					'ms' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'transition-delay: {{SIZE}}ms !important; --es-dropdown-anim-delay: {{SIZE}}ms;',
				),
			)
		);

		$this->add_control(
			'dropdown_anim_easing',
			array(
				'label'     => esc_html__( 'Animation Easing', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ease',
				'options'   => array(
					'linear'      => esc_html__( 'Linear', 'elonix' ),
					'ease'        => esc_html__( 'Ease', 'elonix' ),
					'ease-in'     => esc_html__( 'Ease In', 'elonix' ),
					'ease-out'    => esc_html__( 'Ease Out', 'elonix' ),
					'ease-in-out' => esc_html__( 'Ease In Out', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-desktop-container .es-dropdown' => 'transition-timing-function: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Mobile Drawer Panel Style Section
		$this->start_controls_section(
			'section_style_mobile_panel',
			array(
				'label' => esc_html__( 'Mobile Panel', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Mobile Panel Tabs
		$this->start_controls_tabs( 'mobile_panel_tabs' );

		// Normal Mobile Panel Tab
		$this->start_controls_tab(
			'mobile_panel_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_panel_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_panel_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_panel_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_panel_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown',
			)
		);

		$this->add_responsive_control(
			'mobile_panel_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'mobile_overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_overlay_opacity',
			array(
				'label'      => esc_html__( 'Overlay Opacity', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'alpha' ),
				'range'      => array(
					'alpha' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-overlay, {{WRAPPER}} .es-mobile-menu-active .es-nav-menu-mobile-overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_overlay_blur',
			array(
				'label'      => esc_html__( 'Overlay Backdrop Blur', 'elonix' ),
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
					'size' => 0,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-overlay, {{WRAPPER}} .es-mobile-menu-active .es-nav-menu-mobile-overlay' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}); -webkit-backdrop-filter: blur({{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_panel_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'mobile_panel_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown',
			)
		);

		$this->end_controls_tab();

		// Hover Mobile Panel Tab
		$this->start_controls_tab(
			'mobile_panel_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_panel_background_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_panel_border_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown:hover',
			)
		);

		$this->end_controls_tab();

		// Active Mobile Panel Tab
		$this->start_controls_tab(
			'mobile_panel_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_panel_background_active',
				'selector' => '{{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-drawer, {{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-dropdown',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_panel_border_active',
				'selector' => '{{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-drawer, {{WRAPPER}}.es-mobile-menu-active .es-nav-menu-mobile-dropdown',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Mobile Panel Animations & Heights
		$this->add_control(
			'heading_mobile_panel_animation',
			array(
				'label'     => esc_html__( 'Entrance Animations', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'mobile_menu_entrance_effect',
			array(
				'label'        => esc_html__( 'Panel Entrance Effect', 'elonix' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'default',
				'options'      => array(
					'default'     => esc_html__( 'Default Layout Style', 'elonix' ),
					'slide-left'  => esc_html__( 'Slide Left (From Left)', 'elonix' ),
					'slide-right' => esc_html__( 'Slide Right (From Right)', 'elonix' ),
					'fade'        => esc_html__( 'Fade In', 'elonix' ),
					'zoom'        => esc_html__( 'Zoom In', 'elonix' ),
					'slide-up'    => esc_html__( 'Slide Up', 'elonix' ),
					'slide-down'  => esc_html__( 'Slide Down', 'elonix' ),
					'fade-slide'  => esc_html__( 'Fade + Slide', 'elonix' ),
					'fade-scale'  => esc_html__( 'Fade + Scale', 'elonix' ),
				),
				'prefix_class' => 'es-mobile-entrance-',
			)
		);

		$this->add_control(
			'mobile_menu_anim_duration',
			array(
				'label'      => esc_html__( 'Animation Duration', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's', 'ms' ),
				'range'      => array(
					's' => array(
						'min'  => 0.1,
						'max'  => 2.0,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 0.3,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown, {{WRAPPER}} .es-nav-menu-mobile-overlay' => 'transition-duration: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'mobile_menu_anim_delay',
			array(
				'label'      => esc_html__( 'Animation Delay', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's', 'ms' ),
				'range'      => array(
					's' => array(
						'min'  => 0,
						'max'  => 2.0,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown, {{WRAPPER}} .es-nav-menu-mobile-overlay' => 'transition-delay: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'mobile_menu_anim_easing',
			array(
				'label'     => esc_html__( 'Animation Easing', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cubic-bezier(0.77, 0.2, 0.05, 1)',
				'options'   => array(
					'linear'                               => esc_html__( 'Linear', 'elonix' ),
					'ease'                                 => esc_html__( 'Ease', 'elonix' ),
					'ease-in'                              => esc_html__( 'Ease In', 'elonix' ),
					'ease-out'                             => esc_html__( 'Ease Out', 'elonix' ),
					'ease-in-out'                          => esc_html__( 'Ease In Out', 'elonix' ),
					'cubic-bezier(0.77, 0.2, 0.05, 1)'     => esc_html__( 'Premium Smooth', 'elonix' ),
					'cubic-bezier(0.25, 0.46, 0.45, 0.94)' => esc_html__( 'Decelerate', 'elonix' ),
					'cubic-bezier(0.55, 0.085, 0.68, 0.53)' => esc_html__( 'Accelerate', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer, {{WRAPPER}} .es-nav-menu-mobile-dropdown, {{WRAPPER}} .es-nav-menu-mobile-overlay' => 'transition-timing-function: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_drawer_height',
			array(
				'label'      => esc_html__( 'Drawer/Panel Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer' => 'height: {{SIZE}}{{UNIT}}; --es-mobile-drawer-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_drawer_min_height',
			array(
				'label'      => esc_html__( 'Drawer Min Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer' => 'min-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_drawer_max_height',
			array(
				'label'      => esc_html__( 'Drawer Max Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer' => 'max-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_drawer_close_margin',
			array(
				'label'      => esc_html__( 'Close Button Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Mobile Panel Header Style Section
		$this->start_controls_section(
			'section_style_mobile_header',
			array(
				'label' => esc_html__( 'Mobile Panel Header', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'mobile_header_height',
			array(
				'label'      => esc_html__( 'Header Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-header' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}}; display: flex; align-items: center; justify-content: space-between;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_header_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-header',
			)
		);

		$this->add_responsive_control(
			'mobile_header_logo_width',
			array(
				'label'      => esc_html__( 'Logo Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-logo img' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_header_logo_height',
			array(
				'label'      => esc_html__( 'Logo Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-logo img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_header_border_bottom',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-header',
			)
		);

		$this->add_responsive_control(
			'mobile_header_padding',
			array(
				'label'      => esc_html__( 'Header Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Close Button Style Section
		$this->start_controls_section(
			'section_style_close_button',
			array(
				'label' => esc_html__( 'Close Button', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'close_btn_position_type',
			array(
				'label'     => esc_html__( 'Positioning Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'static',
				'options'   => array(
					'static'   => esc_html__( 'Flex Header Default', 'elonix' ),
					'absolute' => esc_html__( 'Absolute Position', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'position: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_pos_top',
			array(
				'label'      => esc_html__( 'Position Top', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'top: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'close_btn_position_type' => 'absolute',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_pos_right',
			array(
				'label'      => esc_html__( 'Position Right', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'vw' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'close_btn_position_type' => 'absolute',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_pos_left',
			array(
				'label'      => esc_html__( 'Position Left', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'vw' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'close_btn_position_type' => 'absolute',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_pos_bottom',
			array(
				'label'      => esc_html__( 'Position Bottom', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'close_btn_position_type' => 'absolute',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_align_self',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Flex Start', 'elonix' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Flex End', 'elonix' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'align-self: {{VALUE}};',
				),
				'condition' => array(
					'close_btn_position_type' => 'static',
				),
			)
		);

		// Tabs
		$this->start_controls_tabs( 'close_btn_style_tabs' );

		// Normal
		$this->start_controls_tab(
			'close_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'close_btn_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-close i' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-close svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'close_btn_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-close',
			)
		);

		$this->add_responsive_control(
			'close_btn_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'width: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center; justify-content: center;',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center; justify-content: center;',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'close_btn_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-close',
			)
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'close_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'close_btn_color_hover',
			array(
				'label'     => esc_html__( 'Icon Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover i' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'close_btn_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_scale_hover',
			array(
				'label'      => esc_html__( 'Hover Scale Effect', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'scale' ),
				'range'      => array(
					'scale' => array(
						'min'  => 0.8,
						'max'  => 1.5,
						'step' => 0.05,
					),
				),
				'default'    => array(
					'size' => 1.0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:hover' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'close_btn_shadow_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-close:hover',
			)
		);

		$this->end_controls_tab();

		// Active
		$this->start_controls_tab(
			'close_btn_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'close_btn_color_active',
			array(
				'label'     => esc_html__( 'Active Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:active' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
					'{{WRAPPER}} .es-nav-menu-mobile-close:active i' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
					'{{WRAPPER}} .es-nav-menu-mobile-close:active svg' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'close_btn_bg_color_active',
			array(
				'label'     => esc_html__( 'Active Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:active' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'close_btn_border_color_active',
			array(
				'label'     => esc_html__( 'Active Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-close:active' => 'border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// STYLE TAB — Mobile Menu Items
		$this->start_controls_section(
			'section_style_mobile_menu',
			array(
				'label' => esc_html__( 'Mobile Menu', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Tabs for Mobile States
		$this->start_controls_tabs( 'mobile_menu_tabs' );

		// Normal State Tab
		$this->start_controls_tab(
			'mobile_menu_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'mobile_item_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a',
			)
		);

		$this->add_responsive_control(
			'mobile_item_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a .es-submenu-indicator-mobile-toggle' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_item_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a',
			)
		);

		// Fallback Legacy BG color
		$this->add_responsive_control(
			'mobile_item_bg',
			array(
				'label'     => esc_html__( 'Background Color (Legacy)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_item_padding',
			array(
				'label'      => esc_html__( 'Item Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_item_margin',
			array(
				'label'      => esc_html__( 'Item Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_item_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_item_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a',
			)
		);

		$this->end_controls_tab();

		// Hover State Tab
		$this->start_controls_tab(
			'mobile_menu_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_item_color_hover',
			array(
				'label'     => esc_html__( 'Text Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li:hover > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a:hover .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li:hover > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a:hover .es-submenu-indicator-mobile-toggle' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_item_background_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a:hover',
			)
		);

		$this->add_responsive_control(
			'mobile_item_bg_hover',
			array(
				'label'     => esc_html__( 'Background Color (Legacy Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active State Tab
		$this->start_controls_tab(
			'mobile_menu_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_item_color_active',
			array(
				'label'     => esc_html__( 'Text Color (Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-ancestor > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-ancestor > a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-item > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-ancestor > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-item > a .es-submenu-indicator-mobile-toggle, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-ancestor > a .es-submenu-indicator-mobile-toggle' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_item_background_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-ancestor > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-ancestor > a',
			)
		);

		$this->add_responsive_control(
			'mobile_item_bg_active',
			array(
				'label'     => esc_html__( 'Background Color (Legacy Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li.current-menu-ancestor > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li.current-menu-ancestor > a' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Additional Heading (Borders and Spacing)
		$this->add_control(
			'heading_mobile_item_borders_spacing',
			array(
				'label'     => esc_html__( 'Borders & Divider', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'mobile_item_border_bottom_style',
			array(
				'label'     => esc_html__( 'Border Bottom Style', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'solid'  => esc_html__( 'Solid', 'elonix' ),
					'double' => esc_html__( 'Double', 'elonix' ),
					'dotted' => esc_html__( 'Dotted', 'elonix' ),
					'dashed' => esc_html__( 'Dashed', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'border-bottom-style: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_item_border_bottom_width',
			array(
				'label'      => esc_html__( 'Border Bottom Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'mobile_item_border_bottom_color',
			array(
				'label'     => esc_html__( 'Border Bottom Color (Divider Color)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li > a' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'heading_mobile_item_gap',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'mobile_item_gap',
			array(
				'label'      => esc_html__( 'Gap Between Items', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-navbar-nav > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown .es-navbar-nav > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Mobile Submenu Style Section
		$this->start_controls_section(
			'section_style_mobile_submenu',
			array(
				'label' => esc_html__( 'Mobile Submenu', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Submenu Tabs
		$this->start_controls_tabs( 'mobile_submenu_tabs' );

		// Normal Submenu Tab
		$this->start_controls_tab(
			'mobile_submenu_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_indent',
			array(
				'label'      => esc_html__( 'Submenu Indentation Padding', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer .es-dropdown' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_gap',
			array(
				'label'      => esc_html__( 'Submenu Item Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer .es-dropdown li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'mobile_submenu_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a',
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_submenu_background',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'mobile_submenu_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown',
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_item_padding',
			array(
				'label'      => esc_html__( 'Item Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_item_margin',
			array(
				'label'      => esc_html__( 'Item Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'mobile_submenu_divider_color',
			array(
				'label'     => esc_html__( 'Divider Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li:not(:last-child) a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li:not(:last-child) a' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_divider_thickness',
			array(
				'label'      => esc_html__( 'Divider Thickness', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li:not(:last-child) a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li:not(:last-child) a' => 'border-bottom-width: {{SIZE}}{{UNIT}}; border-bottom-style: solid;',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Submenu Tab
		$this->start_controls_tab(
			'mobile_submenu_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_color_hover',
			array(
				'label'     => esc_html__( 'Text Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li:hover > a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_submenu_background_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li:hover > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li a:hover, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li:hover > a',
			)
		);

		$this->add_control(
			'mobile_submenu_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active Submenu Tab
		$this->start_controls_tab(
			'mobile_submenu_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'mobile_submenu_color_active',
			array(
				'label'     => esc_html__( 'Text Color (Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li.current-menu-ancestor > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li.current-menu-item > a, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li.current-menu-ancestor > a' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'mobile_submenu_background_active',
				'selector' => '{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li.current-menu-item, {{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown li.current-menu-ancestor, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li.current-menu-item, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown li.current-menu-ancestor',
			)
		);

		$this->add_control(
			'mobile_submenu_border_color_active',
			array(
				'label'     => esc_html__( 'Border Color (Active)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-mobile-drawer-content .es-dropdown, {{WRAPPER}} .es-nav-menu-mobile-dropdown .es-dropdown' => 'border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// STYLE TAB — Mobile Submenu Toggle Icon Builder
		$this->start_controls_section(
			'section_style_submenu_toggle',
			array(
				'label' => esc_html__( 'Mobile Submenu Toggle Icon', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle' => 'width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center; justify-content: center;',
				),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center; justify-content: center;',
				),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_size',
			array(
				'label'      => esc_html__( 'Caret Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Tabs
		$this->start_controls_tabs( 'submenu_toggle_icon_style_tabs' );

		// Normal
		$this->start_controls_tab(
			'submenu_toggle_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle i' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'submenu_toggle_icon_bg',
				'selector' => '{{WRAPPER}} .es-submenu-indicator-mobile-toggle',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'submenu_toggle_icon_border',
				'selector' => '{{WRAPPER}} .es-submenu-indicator-mobile-toggle',
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_rotation',
			array(
				'label'      => esc_html__( 'Icon Rotation Angle', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array(
					'deg' => array(
						'min' => -360,
						'max' => 360,
					),
				),
				'default'    => array(
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle i'   => 'transform: rotate({{SIZE}}deg); transition: transform 0.2s ease;',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle svg' => 'transform: rotate({{SIZE}}deg); transition: transform 0.2s ease;',
				),
			)
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'submenu_toggle_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_color_hover',
			array(
				'label'     => esc_html__( 'Icon Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle:hover i' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle:hover svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'submenu_toggle_icon_bg_hover',
				'selector' => '{{WRAPPER}} .es-submenu-indicator-mobile-toggle:hover',
			)
		);

		$this->add_control(
			'submenu_toggle_icon_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-submenu-indicator-mobile-toggle:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active / Expanded
		$this->start_controls_tab(
			'submenu_toggle_icon_expanded',
			array(
				'label' => esc_html__( 'Expanded / Open', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_color_expanded',
			array(
				'label'     => esc_html__( 'Icon Color (Expanded)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
					'{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle i' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
					'{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle svg' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'submenu_toggle_icon_bg_expanded',
				'selector' => '{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle',
			)
		);

		$this->add_responsive_control(
			'submenu_toggle_icon_rotation_expanded',
			array(
				'label'      => esc_html__( 'Icon Expanded Rotation', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array(
					'deg' => array(
						'min' => -360,
						'max' => 360,
					),
				),
				'default'    => array(
					'size' => 180,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle i'   => 'transform: rotate({{SIZE}}deg) !important;',
					'{{WRAPPER}} .es-dropdown-open > a > .es-submenu-indicator-mobile-toggle svg' => 'transform: rotate({{SIZE}}deg) !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// STYLE TAB — Mobile Toggler Style Section
		$this->start_controls_section(
			'section_style_toggler',
			array(
				'label' => esc_html__( 'Toggler', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'toggler_width',
			array(
				'label'      => esc_html__( 'Toggler Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_height',
			array(
				'label'      => esc_html__( 'Toggler Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_size',
			array(
				'label'      => esc_html__( 'Toggler Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-hamburger svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-nav-menu-hamburger .es-hamburger-icon-default' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Toggler Tabs (Normal, Hover, Open State)
		$this->start_controls_tabs( 'tabs_toggler_style' );

		// Normal Tab
		$this->start_controls_tab(
			'toggler_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'toggler_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-hamburger .es-hamburger-icon-default' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'toggler_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-hamburger',
			)
		);

		$this->add_responsive_control(
			'toggler_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-nav-menu-hamburger' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'toggler_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-hamburger',
			)
		);

		$this->add_responsive_control(
			'toggler_label_color',
			array(
				'label'     => esc_html__( 'Toggler Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger .es-toggler-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'toggler_label_typography',
				'label'    => esc_html__( 'Text Label Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-hamburger .es-toggler-label',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'toggler_tab_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'toggler_color_hover',
			array(
				'label'     => esc_html__( 'Icon Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-hamburger:hover .es-hamburger-icon-default' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'toggler_shadow_hover',
				'selector' => '{{WRAPPER}} .es-nav-menu-hamburger:hover',
			)
		);

		$this->add_responsive_control(
			'toggler_label_color_hover',
			array(
				'label'     => esc_html__( 'Toggler Text Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger:hover .es-toggler-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Open State Tab
		$this->start_controls_tab(
			'toggler_tab_open',
			array(
				'label' => esc_html__( 'Open State', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'toggler_color_open',
			array(
				'label'     => esc_html__( 'Open Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger[aria-expanded="true"]' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .es-nav-menu-hamburger[aria-expanded="true"] .es-hamburger-icon-default' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_bg_color_open',
			array(
				'label'     => esc_html__( 'Open Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger[aria-expanded="true"]' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'toggler_border_color_open',
			array(
				'label'     => esc_html__( 'Open Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-nav-menu-hamburger[aria-expanded="true"]' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// STYLE TAB — Sticky Mode Styling (Theme Builder Prepared)
		$this->start_controls_section(
			'section_style_sticky',
			array(
				'label' => esc_html__( 'Sticky Navigation', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Sticky Tabs
		$this->start_controls_tabs( 'sticky_navigation_tabs' );

		// Normal State Tab
		$this->start_controls_tab(
			'sticky_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sticky_normal_bg',
				'selector' => '{{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sticky_normal_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-nav-menu-desktop-container .es-navbar-nav > li > a',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'sticky_normal_border',
				'selector' => '{{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sticky_normal_shadow',
				'selector' => '{{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->end_controls_tab();

		// Sticky State Tab
		$this->start_controls_tab(
			'sticky_tab_sticky',
			array(
				'label' => esc_html__( 'Sticky', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sticky_container_bg',
				'selector' => '.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->add_responsive_control(
			'sticky_menu_color',
			array(
				'label'     => esc_html__( 'Menu Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li > a' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'sticky_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li:hover > a, .elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li > a:hover' => 'color: {{VALUE}} !important;',
					'.elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li:hover > a .es-submenu-indicator, .elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li > a:hover .es-submenu-indicator' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'sticky_active_color',
			array(
				'label'     => esc_html__( 'Active Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li.current-menu-item > a, .elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li.current-menu-ancestor > a' => 'color: {{VALUE}} !important;',
					'.elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li.current-menu-item > a::before, .elementor-sticky--active {{WRAPPER}} .es-navbar-nav > li.current-menu-ancestor > a::before' => 'background-color: {{VALUE}} !important; border-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'sticky_logo_width',
			array(
				'label'      => esc_html__( 'Logo Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.elementor-sticky--active {{WRAPPER}} .es-nav-menu-mobile-logo img' => 'width: {{SIZE}}{{UNIT}} !important; max-width: 100%;',
				),
			)
		);

		$this->add_responsive_control(
			'sticky_header_height',
			array(
				'label'      => esc_html__( 'Header Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper' => 'min-height: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sticky_container_shadow',
				'selector' => '.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'sticky_container_border',
				'selector' => '.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Effects Heading
		$this->add_control(
			'heading_sticky_effects',
			array(
				'label'     => esc_html__( 'Sticky Animation Effects', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'sticky_animation',
			array(
				'label'   => esc_html__( 'Sticky Animation', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'       => esc_html__( 'None', 'elonix' ),
					'fade'       => esc_html__( 'Fade', 'elonix' ),
					'slide-down' => esc_html__( 'Slide Down', 'elonix' ),
					'reveal'     => esc_html__( 'Reveal', 'elonix' ),
					'shrink'     => esc_html__( 'Shrink', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'sticky_duration',
			array(
				'label'     => esc_html__( 'Animation Duration (ms)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 400,
				'min'       => 100,
				'max'       => 3000,
				'step'      => 50,
				'condition' => array(
					'sticky_animation!' => 'none',
				),
			)
		);

		$this->add_control(
			'sticky_delay',
			array(
				'label'     => esc_html__( 'Animation Delay (ms)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'max'       => 3000,
				'step'      => 50,
				'condition' => array(
					'sticky_animation!' => 'none',
				),
			)
		);

		$this->add_control(
			'sticky_easing',
			array(
				'label'     => esc_html__( 'Animation Easing', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
				'options'   => array(
					'linear'                               => esc_html__( 'Linear', 'elonix' ),
					'ease'                                 => esc_html__( 'Ease', 'elonix' ),
					'ease-in'                              => esc_html__( 'Ease In', 'elonix' ),
					'ease-out'                             => esc_html__( 'Ease Out', 'elonix' ),
					'ease-in-out'                          => esc_html__( 'Ease In Out', 'elonix' ),
					'cubic-bezier(0.25, 0.46, 0.45, 0.94)' => esc_html__( 'Elonix Default', 'elonix' ),
					'cubic-bezier(0.16, 1, 0.3, 1)'        => esc_html__( 'Out Quint', 'elonix' ),
					'cubic-bezier(0.87, 0, 0.13, 1)'       => esc_html__( 'InOut Expo', 'elonix' ),
				),
				'condition' => array(
					'sticky_animation!' => 'none',
				),
			)
		);

		// Shrink Specific Padding Top
		$this->add_responsive_control(
			'sticky_shrink_padding_top',
			array(
				'label'      => esc_html__( 'Shrink Padding Top', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 8,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper' => '--es-sticky-padding-top: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'sticky_animation' => 'shrink',
				),
			)
		);

		// Shrink Specific Padding Bottom
		$this->add_responsive_control(
			'sticky_shrink_padding_bottom',
			array(
				'label'      => esc_html__( 'Shrink Padding Bottom', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 8,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper' => '--es-sticky-padding-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'sticky_animation' => 'shrink',
				),
			)
		);

		// Shrink Specific Min Height
		$this->add_responsive_control(
			'sticky_shrink_min_height',
			array(
				'label'      => esc_html__( 'Shrink Min Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 60,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'.elementor-sticky--active {{WRAPPER}} .es-nav-menu-wrapper' => '--es-sticky-min-height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'sticky_animation' => 'shrink',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Menu Item Badge Styling (es-badge-*)
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => esc_html__( 'Badge', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Configure styling for global badges and specific types (New, Hot, Sale, Custom).', 'elonix' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		// Tabs for Badge
		$this->start_controls_tabs( 'badge_style_tabs' );

		// Normal Tab
		$this->start_controls_tab(
			'badge_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-menu-badge',
			)
		);

		$this->add_responsive_control(
			'badge_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-menu-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-menu-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Global Color & Background
		$this->add_control(
			'heading_badge_global_colors',
			array(
				'label'     => esc_html__( 'Global Colors', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'badge_color_global',
			array(
				'label'     => esc_html__( 'Global Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_global',
			array(
				'label'     => esc_html__( 'Global Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		// New Badge Colors
		$this->add_control(
			'heading_badge_new_colors',
			array(
				'label'     => esc_html__( 'New Badge Colors', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'badge_color_new',
			array(
				'label'     => esc_html__( 'New Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-new' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_new',
			array(
				'label'     => esc_html__( 'New Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-new' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Hot Badge Colors
		$this->add_control(
			'heading_badge_hot_colors',
			array(
				'label'     => esc_html__( 'Hot Badge Colors', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'badge_color_hot',
			array(
				'label'     => esc_html__( 'Hot Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-hot' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_hot',
			array(
				'label'     => esc_html__( 'Hot Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-hot' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Sale Badge Colors
		$this->add_responsive_control(
			'badge_color_sale',
			array(
				'label'     => esc_html__( 'Sale Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-sale' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_sale',
			array(
				'label'     => esc_html__( 'Sale Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-sale' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Custom Badge Colors
		$this->add_responsive_control(
			'badge_color_custom',
			array(
				'label'     => esc_html__( 'Custom Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-custom' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_custom',
			array(
				'label'     => esc_html__( 'Custom Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-menu-badge.es-badge-custom' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'badge_tab_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'badge_color_global_hover',
			array(
				'label'     => esc_html__( 'Global Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge, {{WRAPPER}} a:hover .es-menu-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_global_hover',
			array(
				'label'     => esc_html__( 'Global Background Hover', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge, {{WRAPPER}} a:hover .es-menu-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_color_new_hover',
			array(
				'label'     => esc_html__( 'New Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-new, {{WRAPPER}} a:hover .es-menu-badge.es-badge-new' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_new_hover',
			array(
				'label'     => esc_html__( 'New Background Hover', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-new, {{WRAPPER}} a:hover .es-menu-badge.es-badge-new' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_color_hot_hover',
			array(
				'label'     => esc_html__( 'Hot Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-hot, {{WRAPPER}} a:hover .es-menu-badge.es-badge-hot' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_hot_hover',
			array(
				'label'     => esc_html__( 'Hot Background Hover', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-hot, {{WRAPPER}} a:hover .es-menu-badge.es-badge-hot' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_color_sale_hover',
			array(
				'label'     => esc_html__( 'Sale Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-sale, {{WRAPPER}} a:hover .es-menu-badge.es-badge-sale' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_sale_hover',
			array(
				'label'     => esc_html__( 'Sale Background Hover', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-sale, {{WRAPPER}} a:hover .es-menu-badge.es-badge-sale' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_color_custom_hover',
			array(
				'label'     => esc_html__( 'Custom Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-custom, {{WRAPPER}} a:hover .es-menu-badge.es-badge-custom' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_bg_custom_hover',
			array(
				'label'     => esc_html__( 'Custom Background Hover', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} li:hover > a .es-menu-badge.es-badge-custom, {{WRAPPER}} a:hover .es-menu-badge.es-badge-custom' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Badge position
		$this->add_control(
			'heading_badge_position',
			array(
				'label'     => esc_html__( 'Position Offset (X & Y)', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'badge_position_x',
			array(
				'label'      => esc_html__( 'Position X Offset', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => -50,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-menu-badge' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_position_y',
			array(
				'label'      => esc_html__( 'Position Y Offset', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => -50,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-menu-badge' => 'transform: translateY({{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->end_controls_section();

		// STYLE TAB — Hover & Active Effects
		$this->start_controls_section(
			'section_style_effects',
			array(
				'label' => esc_html__( 'Effects', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Effects Tabs
		$this->start_controls_tabs( 'effects_tabs' );

		// Hover Effects Tab
		$this->start_controls_tab(
			'effects_tab_hover',
			array(
				'label' => esc_html__( 'Hover Effects', 'elonix' ),
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'   => esc_html__( 'Hover Effect Select', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'        => esc_html__( 'None', 'elonix' ),
					'underline'   => esc_html__( 'Underline', 'elonix' ),
					'overline'    => esc_html__( 'Overline', 'elonix' ),
					'bg_fill'     => esc_html__( 'Background Fill', 'elonix' ),
					'border_grow' => esc_html__( 'Border Grow', 'elonix' ),
					'text_slide'  => esc_html__( 'Text Slide', 'elonix' ),
					'text_reveal' => esc_html__( 'Text Reveal', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'hover_effect_color',
			array(
				'label'     => esc_html__( 'Hover Effect Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-hover-effect-color: {{VALUE}};',
				),
				'condition' => array(
					'hover_effect!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'hover_effect_height',
			array(
				'label'      => esc_html__( 'Line/Border Thickness', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-hover-effect-height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'hover_effect' => array( 'underline', 'overline', 'border_grow' ),
				),
			)
		);

		$this->add_responsive_control(
			'hover_effect_radius',
			array(
				'label'      => esc_html__( 'Effect Border Radius', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-hover-effect-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'hover_effect' => array( 'bg_fill', 'border_grow' ),
				),
			)
		);

		$this->end_controls_tab();

		// Active Effects Tab
		$this->start_controls_tab(
			'effects_tab_active',
			array(
				'label' => esc_html__( 'Active Effects', 'elonix' ),
			)
		);

		$this->add_control(
			'active_indicator_type',
			array(
				'label'   => esc_html__( 'Active Effect Select', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'       => esc_html__( 'None', 'elonix' ),
					'border'     => esc_html__( 'Active Border', 'elonix' ),
					'background' => esc_html__( 'Active Background', 'elonix' ),
					'underline'  => esc_html__( 'Active Underline', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'active_effect_color',
			array(
				'label'     => esc_html__( 'Active Effect Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-active-effect-color: {{VALUE}};',
				),
				'condition' => array(
					'active_indicator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'active_effect_bg',
			array(
				'label'     => esc_html__( 'Active Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(79, 70, 229, 0.1)',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-active-effect-bg: {{VALUE}};',
				),
				'condition' => array(
					'active_indicator_type' => 'background',
				),
			)
		);

		$this->add_responsive_control(
			'active_effect_height',
			array(
				'label'      => esc_html__( 'Line/Border Thickness', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-active-effect-height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'active_indicator_type' => array( 'underline', 'border' ),
				),
			)
		);

		$this->add_responsive_control(
			'active_effect_radius',
			array(
				'label'      => esc_html__( 'Active Border Radius', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-active-effect-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'active_indicator_type' => array( 'background', 'border' ),
				),
			)
		);

		$this->end_controls_tab();

		// Transition Controls Tab
		$this->start_controls_tab(
			'effects_tab_transition',
			array(
				'label' => esc_html__( 'Transition Controls', 'elonix' ),
			)
		);

		$this->add_control(
			'transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (ms)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 300,
				'min'       => 50,
				'max'       => 3000,
				'step'      => 50,
				'selectors' => array(
					'{{WRAPPER}}' => '--es-menu-transition-duration: {{VALUE}}ms;',
				),
			)
		);

		$this->add_control(
			'transition_delay',
			array(
				'label'     => esc_html__( 'Transition Delay (ms)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'max'       => 3000,
				'step'      => 50,
				'selectors' => array(
					'{{WRAPPER}}' => '--es-menu-transition-delay: {{VALUE}}ms;',
				),
			)
		);

		$this->add_control(
			'transition_easing',
			array(
				'label'     => esc_html__( 'Transition Easing', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
				'options'   => array(
					'linear'                               => esc_html__( 'Linear', 'elonix' ),
					'ease'                                 => esc_html__( 'Ease', 'elonix' ),
					'ease-in'                              => esc_html__( 'Ease In', 'elonix' ),
					'ease-out'                             => esc_html__( 'Ease Out', 'elonix' ),
					'ease-in-out'                          => esc_html__( 'Ease In Out', 'elonix' ),
					'cubic-bezier(0.25, 0.46, 0.45, 0.94)' => esc_html__( 'Elonix Default', 'elonix' ),
					'cubic-bezier(0.16, 1, 0.3, 1)'        => esc_html__( 'Out Quint', 'elonix' ),
					'cubic-bezier(0.87, 0, 0.13, 1)'       => esc_html__( 'InOut Expo', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-menu-transition-easing: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// STYLE TAB — Advanced Design Options
		$this->start_controls_section(
			'section_style_advanced_design',
			array(
				'label' => esc_html__( 'Advanced', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'enable_glassmorphism',
			array(
				'label'        => esc_html__( 'Enable Glassmorphism Dropdowns', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_responsive_control(
			'glass_blur',
			array(
				'label'      => esc_html__( 'Glass Backdrop Blur Radius', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'default'    => array(
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-dropdown-glassmorphism .es-dropdown' => 'backdrop-filter: blur({{SIZE}}px) !important; -webkit-backdrop-filter: blur({{SIZE}}px) !important;',
				),
				'condition'  => array(
					'enable_glassmorphism' => 'yes',
				),
			)
		);

		$this->add_control(
			'glass_bg_color',
			array(
				'label'     => esc_html__( 'Glass Background Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.7)',
				'selectors' => array(
					'{{WRAPPER}} .es-dropdown-glassmorphism .es-dropdown' => 'background-color: {{VALUE}} !important;',
				),
				'condition' => array(
					'enable_glassmorphism' => 'yes',
				),
			)
		);

		$this->add_control(
			'enable_soft_shadows',
			array(
				'label'        => esc_html__( 'Enable Soft Submenu Shadows', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'selectors'    => array(
					'{{WRAPPER}} .es-dropdown-soft-shadows .es-dropdown' => 'box-shadow: 0 15px 40px rgba(0, 0, 0, 0.04), 0 5px 15px rgba(0, 0, 0, 0.03) !important;',
				),
			)
		);

		$this->add_control(
			'mega_menu_placeholder',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Elonix Mega Menu Hook Status: Enabled (Nav Walker supports custom theme hooks for rendering template shortcodes).', 'elonix' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();}

	/**
	 * Render Nav Menu output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu'] ) ) {
			return;
		}

		$responsive_breakpoint = $settings['responsive_breakpoint'];
		$breakpoint_val        = 'none';
		if ( 'tablet' === $responsive_breakpoint ) {
			$breakpoint_val = '1024';
		} elseif ( 'mobile' === $responsive_breakpoint ) {
			$breakpoint_val = '767';
		}

		$indicator_icon_html = '';
		if ( 'chevron' === $settings['submenu_indicator'] ) {
			$indicator_icon_html = '<i class="fas fa-chevron-down es-submenu-indicator" aria-hidden="true"></i>';
		} elseif ( 'plus' === $settings['submenu_indicator'] ) {
			$indicator_icon_html = '<i class="fas fa-plus es-submenu-indicator" aria-hidden="true"></i>';
		} elseif ( 'caret' === $settings['submenu_indicator'] ) {
			$indicator_icon_html = '<i class="fas fa-caret-down es-submenu-indicator" aria-hidden="true"></i>';
		}

		$badge_config = apply_filters(
			'elonix_nav_menu_badge_config',
			array(
				'new'    => 'New',
				'hot'    => 'Hot',
				'sale'   => 'Sale',
				'custom' => 'Custom',
			),
			$settings,
			$this
		);

		// Configure primary nav walker args for Desktop
		$desktop_args = array(
			'menu'            => $settings['menu'],
			'container'       => 'div',
			'container_class' => 'es-nav-menu-container es-nav-menu-dropdown-anim-' . $settings['dropdown_transition'] . ( 'yes' === $settings['enable_glassmorphism'] ? ' es-dropdown-glassmorphism' : '' ) . ( 'yes' === $settings['enable_soft_shadows'] ? ' es-dropdown-soft-shadows' : '' ),
			'menu_class'      => 'es-navbar-nav es-nav-menu-hover-' . $settings['hover_effect'] . ' es-nav-menu-active-ind-' . $settings['active_indicator_type'] . ' es-nav-menu-trigger-' . $settings['dropdown_trigger'],
			'depth'           => 0,
			'fallback_cb'     => '__return_empty_string',
			'walker'          => new \Elonix_Nav_Menu_Walker( $indicator_icon_html, $badge_config ),
			'menu_id'         => 'es-desktop-menu-' . $this->get_id(),
		);

		// Configure primary nav walker args for Mobile (no hover or active indicators on mobile ul)
		$mobile_args = array(
			'menu'            => $settings['menu'],
			'container'       => 'div',
			'container_class' => 'es-nav-menu-container',
			'menu_class'      => 'es-navbar-nav es-navbar-nav-mobile',
			'depth'           => 0,
			'fallback_cb'     => '__return_empty_string',
			'walker'          => new \Elonix_Nav_Menu_Walker( $indicator_icon_html, $badge_config ),
			'menu_id'         => 'es-mobile-menu-' . $this->get_id(),
		);

		// Wrapper wrapper classnames
		$wrapper_classes = array(
			'es-nav-menu-wrapper',
			'es-breakpoint-' . $responsive_breakpoint,
		);

		$wrapper_classes[] = 'es-layout-' . $settings['layout_mode'];

		$hamburger_label    = ! empty( $settings['toggler_label'] ) ? '<span class="es-toggler-label">' . esc_html( $settings['toggler_label'] ) . '</span>' : '';
		$accordion_duration = isset( $settings['mobile_accordion_duration'] ) ? $settings['mobile_accordion_duration'] : 300;
		$accordion_easing   = ! empty( $settings['mobile_accordion_easing'] ) ? $settings['mobile_accordion_easing'] : 'ease-in-out';
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
			data-breakpoint="<?php echo esc_attr( $breakpoint_val ); ?>" 
			data-trigger="<?php echo esc_attr( $settings['dropdown_trigger'] ); ?>"
			data-accordion-duration="<?php echo esc_attr( $accordion_duration ); ?>"
			data-accordion-easing="<?php echo esc_attr( $accordion_easing ); ?>">
			
			<!-- Mobile Hamburger Toggle Toggler -->
			<?php if ( 'none' !== $responsive_breakpoint ) : ?>
				<button class="es-nav-menu-hamburger" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'elonix' ); ?>">
					<?php if ( ! empty( $settings['toggler_open_icon']['value'] ) ) : ?>
						<span class="es-hamburger-icon-open">
							<?php Icons_Manager::render_icon( $settings['toggler_open_icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php else : ?>
						<span class="es-hamburger-icon-default"></span>
						<span class="es-hamburger-icon-default"></span>
						<span class="es-hamburger-icon-default"></span>
					<?php endif; ?>
					<?php if ( ! empty( $settings['toggler_close_icon']['value'] ) ) : ?>
						<span class="es-hamburger-icon-close" style="display: none;">
							<?php Icons_Manager::render_icon( $settings['toggler_close_icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php endif; ?>
					<?php echo wp_kses_post( $hamburger_label ); ?>
				</button>
			<?php endif; ?>

			<!-- Desktop Traditional Navigation Container -->
			<div class="es-nav-menu-desktop-container">
				<?php wp_nav_menu( $desktop_args ); ?>
			</div>

			<!-- Mobile Navigation Drawer Overlay Drawer System -->
			<?php if ( 'none' !== $responsive_breakpoint ) : ?>
				<div class="es-nav-menu-mobile-drawer es-nav-menu-mobile-<?php echo esc_attr( $settings['mobile_layout'] ); ?> es-submenu-click-on-<?php echo esc_attr( $settings['mobile_submenu_click'] ); ?>">
					<div class="es-nav-menu-mobile-drawer-header">
						<?php if ( ! empty( $settings['mobile_logo']['url'] ) ) : ?>
							<?php
							$logo_url    = ( 'custom' === $settings['mobile_logo_link'] && ! empty( $settings['mobile_logo_custom_url']['url'] ) ) ? $settings['mobile_logo_custom_url']['url'] : home_url( '/' );
							$logo_target = ( 'custom' === $settings['mobile_logo_link'] && ! empty( $settings['mobile_logo_custom_url']['is_external'] ) ) ? ' target="_blank"' : '';
							?>
							<a href="<?php echo esc_url( $logo_url ); ?>"<?php echo $logo_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="es-nav-menu-mobile-logo">
								<?php echo wp_get_attachment_image( $settings['mobile_logo']['id'], 'medium' ); ?>
							</a>
						<?php endif; ?>
						<button class="es-nav-menu-mobile-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'elonix' ); ?>">
							<?php
							if ( ! empty( $settings['mobile_close_icon']['value'] ) ) {
								Icons_Manager::render_icon( $settings['mobile_close_icon'], array( 'aria-hidden' => 'true' ) );
							} else {
								echo 'X';
							}
							?>
						</button>
					</div>
					<div class="es-nav-menu-mobile-drawer-content">
						<?php wp_nav_menu( $mobile_args ); ?>
					</div>
				</div>
				<div class="es-nav-menu-mobile-overlay"></div>
			<?php endif; ?>
			
		</div>
		<?php
	}
}
