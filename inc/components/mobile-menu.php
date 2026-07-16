<?php
/**
 * Theme Mobile Menu Component (Updated)
 *
 * @package ElonixToolkit
 * @subpackage Components
 * @since 1.0.0
 * @version 2.0.0
 * @author Creative RakibRoni
 *
 * Usage: tv_mobileMenu_controls( $this, 'Mobile Menu' );
 * Render: tv_render_mobilemenu( $settings );
 */

namespace Elementor;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elonix_Bootstrap_Navwalker;

/**
 * Register Mobile Menu Controls
 */
if ( ! function_exists( 'tv_mobile_menu_controls' ) ) :
	function tv_mobile_menu_controls( $widget, $section_label = 'Mobile Menu' ) {

		// ==================== Content Section ====================
		$widget->start_controls_section(
			'mobile_menu_content_section',
			array(
				'label' => esc_htmlesc_html( $section_label ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$widget->add_control(
			'mobile_menu_enable',
			array(
				'label'        => esc_html__( 'Enable Mobile Menu', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// Logo Settings
		$widget->add_control(
			'logo_heading',
			array(
				'label'     => esc_html__( 'Logo Settings', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			'logo_type',
			array(
				'label'     => esc_html__( 'Logo Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'image',
				'options'   => array(
					'image' => esc_html__( 'Image', 'elonix' ),
					'text'  => esc_html__( 'Text', 'elonix' ),
				),
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			'logo_image',
			array(
				'label'     => esc_html__( 'Logo Image', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
					'logo_type'          => 'image',
				),
			)
		);

		$widget->add_control(
			'logo_text',
			array(
				'label'     => esc_html__( 'Logo Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Elonix', 'elonix' ),
				'condition' => array(
					'mobile_menu_enable' => 'yes',
					'logo_type'          => 'text',
				),
			)
		);

		$widget->add_control(
			'logo_url',
			array(
				'label'       => esc_html__( 'Logo URL', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array(
					'url' => home_url( '/' ),
				),
				'condition'   => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		// Menu Settings
		$widget->add_control(
			'menu_heading',
			array(
				'label'     => esc_html__( 'Menu Settings', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			'menu_source',
			array(
				'label'     => esc_html__( 'Menu Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'wp_menu',
				'options'   => array(
					'wp_menu' => esc_html__( 'WordPress Menu', 'elonix' ),
					'custom'  => esc_html__( 'Custom Menu', 'elonix' ),
				),
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$menus        = wp_get_nav_menus();
		$menu_options = array();
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->slug ] = $menu->name;
		}

		$widget->add_control(
			'wp_menu',
			array(
				'label'     => esc_html__( 'Select Menu', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $menu_options,
				'default'   => ! empty( $menu_options ) ? array_keys( $menu_options )[0] : '',
				'condition' => array(
					'mobile_menu_enable' => 'yes',
					'menu_source'        => 'wp_menu',
				),
			)
		);

		// Custom Menu Items
		$repeater = new Repeater();

		$repeater->add_control(
			'menu_title',
			array(
				'label'   => esc_html__( 'Menu Title', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Menu Item', 'elonix' ),
			)
		);

		$repeater->add_control(
			'menu_link',
			array(
				'label'       => esc_html__( 'Link', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
			)
		);

		$widget->add_control(
			'custom_menu_items',
			array(
				'label'       => esc_html__( 'Menu Items', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'menu_title' => esc_html__( 'Home', 'elonix' ),
						'menu_link'  => array( 'url' => '#' ),
					),
					array(
						'menu_title' => esc_html__( 'About', 'elonix' ),
						'menu_link'  => array( 'url' => '#' ),
					),
				),
				'title_field' => '{{{ menu_title }}}',
				'condition'   => array(
					'mobile_menu_enable' => 'yes',
					'menu_source'        => 'custom',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Contact Info Section with Repeater ====================
		$widget->start_controls_section(
			'contact_info_section',
			array(
				'label'     => esc_html__( 'Contact Information', 'elonix' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			'show_contact_info',
			array(
				'label'        => esc_html__( 'Show Contact Info', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// Contact Info Repeater
		$contact_repeater = new Repeater();

		$contact_repeater->add_control(
			'contact_type',
			array(
				'label'   => esc_html__( 'Contact Type', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'phone',
				'options' => array(
					'phone'   => esc_html__( 'Phone', 'elonix' ),
					'email'   => esc_html__( 'Email', 'elonix' ),
					'address' => esc_html__( 'Address', 'elonix' ),
					'hours'   => esc_html__( 'Opening Hours', 'elonix' ),
					'custom'  => esc_html__( 'Custom', 'elonix' ),
				),
			)
		);

		$contact_repeater->add_control(
			'contact_icon',
			array(
				'label'   => esc_html__( 'Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-phone',
					'library' => 'solid',
				),
			)
		);

		$contact_repeater->add_control(
			'contact_title',
			array(
				'label'       => esc_html__( 'Title', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Contact Title', 'elonix' ),
				'label_block' => true,
			)
		);

		$contact_repeater->add_control(
			'contact_value',
			array(
				'label'       => esc_html__( 'Contact Value', 'elonix' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter phone number, email address, or other contact information', 'elonix' ),
			)
		);

		$contact_repeater->add_control(
			'enable_link',
			array(
				'label'        => esc_html__( 'Enable Link', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'conditions'   => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'contact_type',
							'operator' => '==',
							'value'    => 'phone',
						),
						array(
							'name'     => 'contact_type',
							'operator' => '==',
							'value'    => 'email',
						),
					),
				),
			)
		);

		$contact_repeater->add_control(
			'custom_link',
			array(
				'label'       => esc_html__( 'Custom Link', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'condition'   => array(
					'contact_type' => 'custom',
					'enable_link'  => 'yes',
				),
			)
		);

		$widget->add_control(
			'contact_items',
			array(
				'label'       => esc_html__( 'Contact Items', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $contact_repeater->get_controls(),
				'default'     => array(
					array(
						'contact_type'  => 'phone',
						'contact_icon'  => array(
							'value'   => 'fas fa-phone',
							'library' => 'solid',
						),
						'contact_title' => esc_html__( 'Call Us Anytime', 'elonix' ),
						'contact_value' => '+123 (4567) 890',
						'enable_link'   => 'yes',
					),
					array(
						'contact_type'  => 'email',
						'contact_icon'  => array(
							'value'   => 'fal fa-envelope',
							'library' => 'regular',
						),
						'contact_title' => esc_html__( 'Email Us', 'elonix' ),
						'contact_value' => 'example@gmail.com',
						'enable_link'   => 'yes',
					),
					array(
						'contact_type'  => 'hours',
						'contact_icon'  => array(
							'value'   => 'fal fa-alarm-clock',
							'library' => 'regular',
						),
						'contact_title' => esc_html__( 'Opening Hour', 'elonix' ),
						'contact_value' => 'Mon - Sat 8:00 - 6:30, Sunday - CLOSED',
						'enable_link'   => '',
					),
				),
				'title_field' => '{{{ contact_title }}}',
				'condition'   => array(
					'show_contact_info' => 'yes',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Style Tab - Mobile Menu General ====================
		$widget->start_controls_section(
			'mobile_menu_style_section',
			array(
				'label'     => esc_html__( 'Mobile Menu General', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_responsive_control(
			'menu_width',
			array(
				'label'      => esc_html__( 'Menu Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 200,
						'max'  => 500,
						'step' => 1,
					),
					'%'  => array(
						'min' => 50,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mobile-menu-wrapper' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->add_control(
			'menu_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu-area' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'menu_overlay_color',
			array(
				'label'     => esc_html__( 'Background Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu-wrapper' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_responsive_control(
			'menu_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .mobile-menu-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Style Tab - Toggle Button ====================
		$widget->start_controls_section(
			'toggle_button_style_section',
			array(
				'label'     => esc_html__( 'Mobile Toggle Button', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->start_controls_tabs( 'toggle_button_tabs' );

		// Normal Tab
		$widget->start_controls_tab(
			'toggle_button_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$widget->add_control(
			'toggle_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .menu-toggle'       => 'color: {{VALUE}};',
					'{{WRAPPER}} .sidebar-btn .line' => 'background: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'toggle_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .menu-toggle' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'toggle_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .menu-toggle',
			)
		);

		$widget->end_controls_tab();

		// Hover Tab
		$widget->start_controls_tab(
			'toggle_button_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$widget->add_control(
			'toggle_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .menu-toggle:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'toggle_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .menu-toggle:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'toggle_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .menu-toggle:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_responsive_control(
			'toggle_size',
			array(
				'label'      => esc_html__( 'Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 20,
						'max'  => 60,
						'step' => 1,
					),
				),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .menu-toggle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->add_responsive_control(
			'toggle_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .menu-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Style Tab - Logo ====================
		$widget->start_controls_section(
			'logo_style_section',
			array(
				'label'     => esc_html__( 'Mobile Logo', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_responsive_control(
			'logo_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 50,
						'max'  => 300,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mobile-menu-wrapper .mobile-logo img' => 'max-width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'logo_type' => 'image',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'logo_typography',
				'label'     => esc_html__( 'Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .mobile-logo a',
				'condition' => array(
					'logo_type' => 'text',
				),
			)
		);

		$widget->add_control(
			'logo_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-logo a' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'logo_type' => 'text',
				),
			)
		);

		$widget->add_responsive_control(
			'logo_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .mobile-logo' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->add_responsive_control(
			'logo_alignment',
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
					'{{WRAPPER}} .mobile-logo' => 'text-align: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Style Tab - Menu Items ====================
		$widget->start_controls_section(
			'menu_items_style_section',
			array(
				'label'     => esc_html__( 'Mobile Menu Items', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'menu_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .mobile-menu ul li a',
			)
		);

		$widget->start_controls_tabs( 'menu_items_tabs' );

		// Normal Tab
		$widget->start_controls_tab(
			'menu_items_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$widget->add_control(
			'menu_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li a .mean-expand-class' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'menu_active_text_color',
			array(
				'label'     => esc_html__( 'Active Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li.active>a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active-class>a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active>a>.mean-expand-class' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active-class>a>.mean-expand-class' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'menu_bg_color_item',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'dropdown_menu_border_color',
			array(
				'label'     => esc_html__( 'Dropdown Menu Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li:first-child' => 'border-color: {{VALUE}} !important',
					'{{WRAPPER}} .mobile-menu ul li' => 'border-color: {{VALUE}} !important',
				),
			)
		);

		$widget->end_controls_tab();

		// Hover Tab
		$widget->start_controls_tab(
			'menu_items_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$widget->add_control(
			'menu_hover_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li a .mean-expand-class:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'active_menu_hover_text_color',
			array(
				'label'     => esc_html__( 'Active Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li.active:hover>a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active-class:hover>a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active:hover>a>.mean-expand-class' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mobile-menu ul li.active-class:hover>a>.mean-expand-class' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'menu_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mobile-menu ul li a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_responsive_control(
			'menu_item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .mobile-menu ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->add_responsive_control(
			'menu_item_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .mobile-menu ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'menu_item_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .mobile-menu ul li',
			)
		);

		$widget->end_controls_section();

		// ==================== Style Tab - Contact Info ====================
		$widget->start_controls_section(
			'contact_info_style_section',
			array(
				'label'     => esc_html__( 'Mobile Contact Info', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'mobile_menu_enable' => 'yes',
					'show_contact_info'  => 'yes',
				),
			)
		);

		$widget->add_control(
			'mob_contact_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .contact-info-box .icon' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_responsive_control(
			'mob_contact_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .contact-info-box .icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'contact_title_typography',
				'label'    => esc_html__( 'Title Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .contact-info-box .title',
			)
		);

		$widget->add_control(
			'contact_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .contact-info-box .title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'contact_text_typography',
				'label'    => esc_html__( 'Text Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .contact-info-box a, {{WRAPPER}} .contact-info-box',
			)
		);

		$widget->start_controls_tabs( 'contact_link_tabs' );

		// Normal Tab
		$widget->start_controls_tab(
			'contact_link_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$widget->add_control(
			'contact_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .contact-info-box a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .contact-info-box'   => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		// Hover Tab
		$widget->start_controls_tab(
			'contact_link_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$widget->add_control(
			'contact_hover_color',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .contact-info-box a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		$widget->add_responsive_control(
			'contact_item_spacing',
			array(
				'label'      => esc_html__( 'Item Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .contact-list-one li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Social Links Section ====================
		tv_socialLinks_controls( $widget, 'social_two', __( 'Mobile Menu Social', 'elonix' ) );
	}
endif;

/**
 * Render Mobile Menu HTML
 */
if ( ! function_exists( 'tv_render_mobilemenu' ) ) :
	function tv_render_mobilemenu( $settings ) {

		if ( $settings['mobile_menu_enable'] !== 'yes' ) {
			return;
		}
		?>
		<div class="mobile-menu-wrapper">
			<div class="mobile-menu-area">
				<button class="menu-toggle">
					<i class="fas fa-times"></i>
				</button>

<div class="mobile-logo">
					<?php
					$logo_url = ! empty( $settings['logo_url']['url'] ) ? $settings['logo_url']['url'] : home_url( '/' );
					$target   = $settings['logo_url']['is_external'] ? ' target="_blank"' : '';
					$nofollow = $settings['logo_url']['nofollow'] ? ' rel="nofollow"' : '';

					if ( $settings['logo_type'] === 'image' && ! empty( $settings['logo_image']['url'] ) ) :
						?>
						<a href="<?php echo esc_url( $logo_url ); ?>"<?php echo esc_attr( $target . $nofollow ); ?>>
							<img src="<?php echo esc_url( $settings['logo_image']['url'] ); ?>"
								alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $logo_url ); ?>"<?php echo esc_attr( $target . $nofollow ); ?>>
							<?php echo esc_html( $settings['logo_text'] ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="mobile-menu">
					<ul class="navigation clearfix">
						<?php
						$menu            = $settings['menu'];
						$primary_nav_arg = array(
							'menu'           => $menu,
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => '',
							'depth'          => 3,
							'walker'         => new Elonix_Bootstrap_Navwalker(),
							'fallback_cb'    => 'Elonix_Bootstrap_Navwalker::fallback',
							'items_wrap'     => '%3$s',
						);
						if ( has_nav_menu( 'primary' ) ) {
							wp_nav_menu( $primary_nav_arg );
						}
						?>
					</ul>
				</div>

				<?php if ( $settings['show_contact_info'] === 'yes' && ! empty( $settings['contact_items'] ) ) : ?>
				<ul class="contact-list-one">
					<?php
					foreach ( $settings['contact_items'] as $item ) :
						if ( empty( $item['contact_value'] ) ) {
							continue;
						}
						?>
						<li>
							<div class="contact-info-box">
								<?php if ( ! empty( $item['contact_icon']['value'] ) ) : ?>
								<span class="icon">
									<?php \Elementor\Icons_Manager::render_icon( $item['contact_icon'], array( 'aria-hidden' => 'true' ) ); ?>
								</span>
								<?php endif; ?>

								<?php if ( ! empty( $item['contact_title'] ) ) : ?>
								<span class="title"><?php echo esc_html( $item['contact_title'] ); ?></span>
								<?php endif; ?>

								<?php
								// Handle different contact types
								if ( $item['contact_type'] === 'phone' && $item['enable_link'] === 'yes' ) {
									$phone_clean = str_replace( array( ' ', '-', '(', ')' ), '', $item['contact_value'] );
									?>
									<a href="tel:<?php echo esc_attr( $phone_clean ); ?>">
										<?php echo esc_html( $item['contact_value'] ); ?>
									</a>
									<?php
								} elseif ( $item['contact_type'] === 'email' && $item['enable_link'] === 'yes' ) {
									?>
									<a href="mailto:<?php echo esc_attr( $item['contact_value'] ); ?>">
										<?php echo esc_html( $item['contact_value'] ); ?>
									</a>
									<?php
} elseif ( $item['contact_type'] === 'custom' && $item['enable_link'] === 'yes' && ! empty( $item['custom_link']['url'] ) ) {
									$target   = $item['custom_link']['is_external'] ? ' target="_blank"' : '';
									$nofollow = $item['custom_link']['nofollow'] ? ' rel="nofollow"' : '';
									?>
									<a href="<?php echo esc_url( $item['custom_link']['url'] ); ?>"<?php echo esc_attr( $target . $nofollow ); ?>>
										<?php echo wp_kses_post( $item['contact_value'] ); ?>
									</a>
									<?php
								} else {
									echo wp_kses_post( $item['contact_value'] );
								}
								?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<ul class="social-links social-icon-one">
					<?php tv_render_socialLinks( $settings, 'social_two' ); ?>
				</ul>

			</div>
		</div>
		<?php
	}
endif;
