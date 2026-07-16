<?php
/**
 * Theme Search Component with Style Options
 *
 * Provides a reusable Elementor Search system with prefix-based controls and multiple styles.
 * This class handles registering Search controls, rendering search HTML with different styles.
 * Search Controls Component for Elementor Widgets
 *
 * @package ElonixToolkit
 * @subpackage Components
 * @since 1.0.0
 * @version 2.0.0
 * @author Creative RakibRoni
 * @link https://github.com/itsrakibroni
 *
 * Usage: tv_search_controls( $widget_instance, 'search_one', __( 'Search Settings', 'elonix' ) );
 * Render: tv_render_search( $settings, 'search_one' );
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register Search Controls
 *
 * @param object $widget Widget instance
 * @param string $prefix Control prefix for multiple search instances
 * @param string $section_label Section label
 */
function tv_search_controls( $widget, $prefix = 'search', $section_label = 'Search Settings' ) {

	// Search Section
	$widget->start_controls_section(
		$prefix . '_section',
		array(
			'label' => $section_label,
			'tab'   => Controls_Manager::TAB_CONTENT,
		)
	);

	// Enable/Disable Search
	$widget->add_control(
		$prefix . '_enable',
		array(
			'label'        => __( 'Enable Search', 'elonix' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'elonix' ),
			'label_off'    => __( 'No', 'elonix' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		)
	);

	// Search Style
	$widget->add_control(
		$prefix . '_style',
		array(
			'label'     => __( 'Search Style', 'elonix' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'style1',
			'options'   => array(
				'style1' => __( 'Style 1 (Popup Overlay)', 'elonix' ),
				'style2' => __( 'Style 2 (Inline Expand)', 'elonix' ),
			),
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Search Button Text
	$widget->add_control(
		$prefix . '_btn_text',
		array(
			'label'       => __( 'Button Text (Optional)', 'elonix' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Search', 'elonix' ),
			'condition'   => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Search Button Icon
	$widget->add_control(
		$prefix . '_btn_icon',
		array(
			'label'     => __( 'Button Icon', 'elonix' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => array(
				'value'   => 'fa-solid fa-search',
				'library' => 'fa-solid',
			),
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Search Placeholder
	$widget->add_control(
		$prefix . '_placeholder',
		array(
			'label'     => __( 'Search Placeholder', 'elonix' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'Search...', 'elonix' ),
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Close Icon (X)
	$widget->add_control(
		$prefix . '_close_icon',
		array(
			'label'     => __( 'Close Icon (X)', 'elonix' ),
			'type'      => Controls_Manager::ICONS,
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Submit Icon
	$widget->add_control(
		$prefix . '_submit_icon',
		array(
			'label'     => __( 'Submit Button Icon', 'elonix' ),
			'type'      => Controls_Manager::ICONS,
			'condition' => array(
				$prefix . '_enable' => 'yes',
				$prefix . '_style'  => 'style1',
			),
		)
	);

	// Search Action
	$widget->add_control(
		$prefix . '_action',
		array(
			'label'     => __( 'Search Action', 'elonix' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'get',
			'options'   => array(
				'get'  => __( 'WordPress Search (GET)', 'elonix' ),
				'post' => __( 'Custom Form (POST)', 'elonix' ),
			),
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Custom Action URL
	$widget->add_control(
		$prefix . '_action_url',
		array(
			'label'       => __( 'Custom Action URL', 'elonix' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => __( 'https://your-site.com/search', 'elonix' ),
			'condition'   => array(
				$prefix . '_enable' => 'yes',
				$prefix . '_action' => 'post',
			),
		)
	);

	$widget->end_controls_section();

	// ============================================
	// Search Button Style Section with Tabs
	// ============================================
	$widget->start_controls_section(
		$prefix . '_btn_style',
		array(
			'label'     => __( 'Search Button Style', 'elonix' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Button Size (Common for all tabs)
	$widget->add_responsive_control(
		$prefix . '_btn_size',
		array(
			'label'      => __( 'Button Size', 'elonix' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 20,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .search-btn'                  => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		)
	);

	// Start Tabs for Normal and Hover
	$widget->start_controls_tabs( $prefix . '_btn_style_tabs' );

	// Normal Tab
	$widget->start_controls_tab(
		$prefix . '_btn_normal_tab',
		array(
			'label' => __( 'Normal', 'elonix' ),
		)
	);

	// Button Background
	$widget->add_group_control(
		Group_Control_Background::get_type(),
		array(
			'name'     => $prefix . '_btn_bg',
			'label'    => __( 'Background', 'elonix' ),
			'types'    => array( 'classic', 'gradient' ),
			'selector' => '{{WRAPPER}} .search-btn, {{WRAPPER}} .search-wrapper .search-icon',
		)
	);

	// Button Icon Color
	$widget->add_control(
		$prefix . '_btn_icon_color',
		array(
			'label'     => __( 'Icon Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-btn .icon'            => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-btn .icon i'          => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-btn .icon svg'        => 'fill: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-icon' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-icon svg' => 'fill: {{VALUE}};',
			),
		)
	);

	// Button Text Color
	$widget->add_control(
		$prefix . '_btn_text_color',
		array(
			'label'     => __( 'Text Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-btn .text' => 'color: {{VALUE}};',
			),
		)
	);

	// Button Border
	$widget->add_group_control(
		Group_Control_Border::get_type(),
		array(
			'name'     => $prefix . '_btn_border',
			'selector' => '{{WRAPPER}} .search-btn, {{WRAPPER}} .search-wrapper .search-icon',
		)
	);

	// Button Box Shadow
	$widget->add_group_control(
		Group_Control_Box_Shadow::get_type(),
		array(
			'name'     => $prefix . '_btn_shadow',
			'selector' => '{{WRAPPER}} .search-btn, {{WRAPPER}} .search-wrapper .search-icon',
		)
	);

	$widget->end_controls_tab();

	// Hover Tab
	$widget->start_controls_tab(
		$prefix . '_btn_hover_tab',
		array(
			'label' => __( 'Hover', 'elonix' ),
		)
	);

	// Hover Background
	$widget->add_group_control(
		Group_Control_Background::get_type(),
		array(
			'name'     => $prefix . '_btn_hover_bg',
			'label'    => __( 'Background', 'elonix' ),
			'types'    => array( 'classic', 'gradient' ),
			'selector' => '{{WRAPPER}} .search-btn:hover, {{WRAPPER}} .search-wrapper .search-icon:hover',
		)
	);

	// Hover Icon Color
	$widget->add_control(
		$prefix . '_btn_hover_icon_color',
		array(
			'label'     => __( 'Icon Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-btn:hover .icon'     => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-btn:hover .icon i'   => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-btn:hover .icon svg' => 'fill: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-icon:hover' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-icon:hover svg' => 'fill: {{VALUE}};',
			),
		)
	);

	// Hover Text Color
	$widget->add_control(
		$prefix . '_btn_hover_text_color',
		array(
			'label'     => __( 'Text Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-btn:hover .text' => 'color: {{VALUE}};',
			),
		)
	);

	// Hover Border Color
	$widget->add_control(
		$prefix . '_btn_hover_border_color',
		array(
			'label'     => __( 'Border Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-btn:hover' => 'border-color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-icon:hover' => 'border-color: {{VALUE}};',
			),
		)
	);

	// Hover Box Shadow
	$widget->add_group_control(
		Group_Control_Box_Shadow::get_type(),
		array(
			'name'     => $prefix . '_btn_hover_shadow',
			'selector' => '{{WRAPPER}} .search-btn:hover, {{WRAPPER}} .search-wrapper .search-icon:hover',
		)
	);

	$widget->end_controls_tab();

	$widget->end_controls_tabs();

	// Button Icon Size (Common)
	$widget->add_responsive_control(
		$prefix . '_btn_icon_size',
		array(
			'label'      => __( 'Icon Size', 'elonix' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 50,
				),
			),
			'default'    => array(
				'size' => 18,
			),
			'selectors'  => array(
				'{{WRAPPER}} .search-btn .icon'            => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-btn .icon i'          => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-btn .icon svg'        => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
			'separator'  => 'before',
		)
	);

	// Button Border Radius (Common)
	$widget->add_responsive_control(
		$prefix . '_btn_radius',
		array(
			'label'      => __( 'Border Radius', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-btn'                  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		)
	);

	// Button Padding (Common)
	$widget->add_responsive_control(
		$prefix . '_btn_padding',
		array(
			'label'      => __( 'Padding', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-btn'                  => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		)
	);

	$widget->end_controls_section();

	// ============================================
	// Search Popup/Container Style Section
	// ============================================
	$widget->start_controls_section(
		$prefix . '_popup_style',
		array(
			'label'     => __( 'Search Container Style', 'elonix' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array(
				$prefix . '_enable' => 'yes',
			),
		)
	);

	// Popup Background
	$widget->add_group_control(
		Group_Control_Background::get_type(),
		array(
			'name'     => $prefix . '_popup_bg',
			'label'    => __( 'Background', 'elonix' ),
			'types'    => array( 'classic', 'gradient' ),
			'selector' => '{{WRAPPER}} .search-popup, {{WRAPPER}} .search-wrapper',
		)
	);

	// Popup Padding
	$widget->add_responsive_control(
		$prefix . '_popup_padding',
		array(
			'label'      => __( 'Padding', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup'   => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		)
	);

	// Input Style Heading
	$widget->add_control(
		$prefix . '_input_heading',
		array(
			'label'     => __( 'Input Field', 'elonix' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		)
	);

	// Start Tabs for Input Field
	$widget->start_controls_tabs( $prefix . '_input_style_tabs' );

	// Input Normal Tab
	$widget->start_controls_tab(
		$prefix . '_input_normal_tab',
		array(
			'label' => __( 'Normal', 'elonix' ),
		)
	);

	// Input Typography
	$widget->add_group_control(
		Group_Control_Typography::get_type(),
		array(
			'name'     => $prefix . '_input_typography',
			'selector' => '{{WRAPPER}} .search-popup input[type="search"], {{WRAPPER}} .search-wrapper .search-input',
		)
	);

	// Input Text Color
	$widget->add_control(
		$prefix . '_input_color',
		array(
			'label'     => __( 'Text Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input' => 'color: {{VALUE}};',
			),
		)
	);

	// Input Placeholder Color
	$widget->add_control(
		$prefix . '_input_placeholder_color',
		array(
			'label'     => __( 'Placeholder Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]::placeholder' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input::placeholder' => 'color: {{VALUE}};',
			),
		)
	);

	// Input Background
	$widget->add_control(
		$prefix . '_input_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]' => 'background-color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input' => 'background-color: {{VALUE}};',
			),
		)
	);

	// Input Border
	$widget->add_group_control(
		Group_Control_Border::get_type(),
		array(
			'name'     => $prefix . '_input_border',
			'selector' => '{{WRAPPER}} .search-popup input[type="search"], {{WRAPPER}} .search-wrapper .search-input',
		)
	);

	$widget->end_controls_tab();

	// Input Focus Tab
	$widget->start_controls_tab(
		$prefix . '_input_focus_tab',
		array(
			'label' => __( 'Focus', 'elonix' ),
		)
	);

	// Input Focus Text Color
	$widget->add_control(
		$prefix . '_input_focus_color',
		array(
			'label'     => __( 'Text Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]:focus' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input:focus' => 'color: {{VALUE}};',
			),
		)
	);

	// Input Focus Background
	$widget->add_control(
		$prefix . '_input_focus_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]:focus' => 'background-color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input:focus' => 'background-color: {{VALUE}};',
			),
		)
	);

	// Input Focus Border Color
	$widget->add_control(
		$prefix . '_input_focus_border_color',
		array(
			'label'     => __( 'Border Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup input[type="search"]:focus' => 'border-color: {{VALUE}};',
				'{{WRAPPER}} .search-wrapper .search-input:focus' => 'border-color: {{VALUE}};',
			),
		)
	);

	$widget->end_controls_tab();

	$widget->end_controls_tabs();

	// Input Border Radius (Common)
	$widget->add_responsive_control(
		$prefix . '_input_radius',
		array(
			'label'      => __( 'Border Radius', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup input[type="search"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .input-holder' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
			'separator'  => 'before',
		)
	);

	// Input Padding (Common)
	$widget->add_responsive_control(
		$prefix . '_input_padding',
		array(
			'label'      => __( 'Padding', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup input[type="search"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .search-wrapper .search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		)
	);

	// Submit Button Heading
	$widget->add_control(
		$prefix . '_submit_heading',
		array(
			'label'     => __( 'Submit Button', 'elonix' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		)
	);

	// Start Tabs for Submit Button
	$widget->start_controls_tabs( $prefix . '_submit_style_tabs' );

	// Submit Normal Tab
	$widget->start_controls_tab(
		$prefix . '_submit_normal_tab',
		array(
			'label' => __( 'Normal', 'elonix' ),
		)
	);

	// Submit Button Background
	$widget->add_control(
		$prefix . '_submit_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup button[type="submit"]' => 'background-color: {{VALUE}};',
			),
		)
	);

	// Submit Icon Color
	$widget->add_control(
		$prefix . '_submit_icon_color',
		array(
			'label'     => __( 'Icon Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup button[type="submit"]' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup button[type="submit"] i' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup button[type="submit"] svg' => 'fill: {{VALUE}};',
			),
		)
	);

	// Submit Border
	$widget->add_group_control(
		Group_Control_Border::get_type(),
		array(
			'name'     => $prefix . '_submit_border',
			'selector' => '{{WRAPPER}} .search-popup button[type="submit"]',
		)
	);

	$widget->end_controls_tab();

	// Submit Hover Tab
	$widget->start_controls_tab(
		$prefix . '_submit_hover_tab',
		array(
			'label' => __( 'Hover', 'elonix' ),
		)
	);

	// Submit Hover Background
	$widget->add_control(
		$prefix . '_submit_hover_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup button[type="submit"]:hover' => 'background-color: {{VALUE}};',
			),
		)
	);

	// Submit Hover Icon Color
	$widget->add_control(
		$prefix . '_submit_hover_icon_color',
		array(
			'label'     => __( 'Icon Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup button[type="submit"]:hover' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup button[type="submit"]:hover i' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup button[type="submit"]:hover svg' => 'fill: {{VALUE}};',
			),
		)
	);

	// Submit Hover Border Color
	$widget->add_control(
		$prefix . '_submit_hover_border_color',
		array(
			'label'     => __( 'Border Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup button[type="submit"]:hover' => 'border-color: {{VALUE}};',
			),
		)
	);

	$widget->end_controls_tab();

	$widget->end_controls_tabs();

	// Submit Icon Size (Common)
	$widget->add_responsive_control(
		$prefix . '_submit_icon_size',
		array(
			'label'      => __( 'Icon Size', 'elonix' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup button[type="submit"]' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-popup button[type="submit"] i' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-popup button[type="submit"] svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
			'separator'  => 'before',
		)
	);

	// Close Button Heading (Only for Style 1)
	$widget->add_control(
		$prefix . '_close_heading',
		array(
			'label'     => __( 'Close Button', 'elonix' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			// 'condition' => [
			// $prefix . '_style' => 'style1',
			// ],
		)
	);

	// Start Tabs for Close Button
	$widget->start_controls_tabs( $prefix . '_close_style_tabs' );

	// Close Normal Tab
	$widget->start_controls_tab(
		$prefix . '_close_normal_tab',
		array(
			'label' => __( 'Normal', 'elonix' ),
		)
	);

	// Close Button Color
	$widget->add_control(
		$prefix . '_close_color',
		array(
			'label'     => __( 'Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup .close-search' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup .close-search i' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup .close-search svg' => 'fill: {{VALUE}};',
			),
		)
	);

	// Close Button Background
	$widget->add_control(
		$prefix . '_close_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup .close-search' => 'background-color: {{VALUE}};',
			),
		)
	);

	// Close Border
	$widget->add_group_control(
		Group_Control_Border::get_type(),
		array(
			'name'     => $prefix . '_close_border',
			'selector' => '{{WRAPPER}} .search-popup .close-search',
		)
	);

	$widget->end_controls_tab();

	// Close Hover Tab
	$widget->start_controls_tab(
		$prefix . '_close_hover_tab',
		array(
			'label'     => __( 'Hover', 'elonix' ),
			'condition' => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	// Close Hover Color
	$widget->add_control(
		$prefix . '_close_hover_color',
		array(
			'label'     => __( 'Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup .close-search:hover' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup .close-search:hover i' => 'color: {{VALUE}};',
				'{{WRAPPER}} .search-popup .close-search:hover svg' => 'fill: {{VALUE}};',
			),
			'condition' => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	// Close Hover Background
	$widget->add_control(
		$prefix . '_close_hover_bg',
		array(
			'label'     => __( 'Background Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup .close-search:hover' => 'background-color: {{VALUE}};',
			),
			'condition' => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	// Close Hover Border Color
	$widget->add_control(
		$prefix . '_close_hover_border_color',
		array(
			'label'     => __( 'Border Color', 'elonix' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .search-popup .close-search:hover' => 'border-color: {{VALUE}};',
			),
			'condition' => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	$widget->end_controls_tab();

	$widget->end_controls_tabs();

	// Close Icon Size (Common)
	$widget->add_responsive_control(
		$prefix . '_close_icon_size',
		array(
			'label'      => __( 'Icon Size', 'elonix' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup .close-search' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-popup .close-search i' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .search-popup .close-search svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
			'separator'  => 'before',
			'condition'  => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	// Close Button Size (Common)
	$widget->add_responsive_control(
		$prefix . '_close_size',
		array(
			'label'      => __( 'Button Size', 'elonix' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 20,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup .close-search' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
			'condition'  => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	// Close Border Radius (Common)
	$widget->add_responsive_control(
		$prefix . '_close_radius',
		array(
			'label'      => __( 'Border Radius', 'elonix' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .search-popup .close-search' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
			'condition'  => array(
				$prefix . '_style' => 'style1',
			),
		)
	);

	$widget->end_controls_section();
}

/**
 * Render Search Component
 *
 * @param array  $settings Widget settings
 * @param string $prefix Control prefix
 */
function tv_render_search( $settings, $prefix = 'search' ) {

	// Check if search is enabled
	if ( empty( $settings[ $prefix . '_enable' ] ) || $settings[ $prefix . '_enable' ] !== 'yes' ) {
		return;
	}

	// Get style
	$search_style = ! empty( $settings[ $prefix . '_style' ] ) ? $settings[ $prefix . '_style' ] : 'style1';

	// Get settings
	$btn_text    = ! empty( $settings[ $prefix . '_btn_text' ] ) ? $settings[ $prefix . '_btn_text' ] : '';
	$btn_icon    = $settings[ $prefix . '_btn_icon' ];
	$placeholder = ! empty( $settings[ $prefix . '_placeholder' ] ) ? $settings[ $prefix . '_placeholder' ] : __( 'Search...', 'elonix' );
	$submit_icon = $settings[ $prefix . '_submit_icon' ];
	$action      = ! empty( $settings[ $prefix . '_action' ] ) ? $settings[ $prefix . '_action' ] : 'get';
	$action_url  = ! empty( $settings[ $prefix . '_action_url' ]['url'] ) ? $settings[ $prefix . '_action_url' ]['url'] : '#';

	// Generate unique ID
	$unique_id = 'search-' . uniqid();

	// Determine form method and action
	$form_method = ( $action === 'get' ) ? 'get' : 'post';
	$form_action = ( $action === 'get' ) ? esc_url( home_url( '/' ) ) : esc_url( $action_url );
	$input_name  = ( $action === 'get' ) ? 's' : 'search-field';

	// Render based on style
	if ( $search_style === 'style1' ) {
		// Style 1: Popup Overlay
		$close_icon = $settings[ $prefix . '_close_icon' ];

		?>
		<!-- Search Button -->
		<button class="search-btn" data-search-target="<?php echo esc_attr( $unique_id ); ?>" type="button">
			<?php if ( ! empty( $btn_icon['value'] ) ) : ?>
				<span class="icon">
					<?php Icons_Manager::render_icon( $btn_icon, array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $btn_text ) : ?>
				<span class="text"><?php echo esc_html( $btn_text ); ?></span>
			<?php endif; ?>
		</button>

		<!-- Search Popup -->
		<div class="search-popup" id="<?php echo esc_attr( $unique_id ); ?>">
			<!-- Close Buttons -->
			<button class="close-search style-1" type="button" aria-label="<?php esc_attr_e( 'Close Search', 'elonix' ); ?>">
				<?php Icons_Manager::render_icon( $close_icon, array( 'aria-hidden' => 'true' ) ); ?>
			</button>

			<!-- Search Form -->
			<form method="<?php echo esc_attr( $form_method ); ?>" action="<?php echo esc_attr( $form_action ); ?>" role="search">
				<div class="form-group">
					<input id="<?php echo esc_attr( $unique_id . '-input' ); ?>"
						type="search"
						name="<?php echo esc_attr( $input_name ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						required
						aria-label="<?php esc_attr_e( 'Search', 'elonix' ); ?>"
					>
					<button type="submit" aria-label="<?php esc_attr_e( 'Submit Search', 'elonix' ); ?>">
						<?php Icons_Manager::render_icon( $submit_icon, array( 'aria-hidden' => 'true' ) ); ?>
					</button>
				</div>
			</form>
		</div>

		<!-- Search JavaScript for Style 1 -->
		<script>
		(function($) {
			'use strict';
			$(document).ready(function() {
				if ($(".search-btn").length) {
					// Use .on() for click event on .search-btn
					$(".search-btn").on("click", function() {
						$("body").addClass("search-active");
					});

					// Use .on() for click event on .close-search
					$(".close-search").on("click", function() {
						$("body").removeClass("search-active");
					});

					// Close on ESC key
					$(document).on("keydown", function(e) {
						if (e.key === "Escape" && $("body").hasClass("search-active")) {
							$("body").removeClass("search-active");
						}
					});

					// Close on overlay click
					$(".search-popup").on("click", function(e) {
						if ($(e.target).hasClass("search-popup")) {
							$("body").removeClass("search-active");
						}
					});
				}
			});
		})(jQuery);
		</script>
		<?php

	} else {
		// Style 2: Inline Expand
		$close_icon = $settings[ $prefix . '_close_icon' ];

		?>
		<div class="search-wrapper" id="<?php echo esc_attr( $unique_id ); ?>">
			<form method="<?php echo esc_attr( $form_method ); ?>" action="<?php echo esc_attr( $form_action ); ?>" role="search">
				<div class="input-holder">
					<input id="<?php echo esc_attr( $unique_id . '-input' ); ?>"
						class="search-input"
						type="search"
						name="<?php echo esc_attr( $input_name ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						aria-label="<?php esc_attr_e( 'Search', 'elonix' ); ?>"
					>
					<button class="search-icon" type="button" aria-label="<?php esc_attr_e( 'Toggle Search', 'elonix' ); ?>">
						<span class="search-icon-open">
							<?php if ( ! empty( $btn_icon['value'] ) ) : ?>
								<?php Icons_Manager::render_icon( $btn_icon, array( 'aria-hidden' => 'true' ) ); ?>
							<?php endif; ?>
						</span>
						<?php if ( $btn_text ) : ?>
							<span class="text"><?php echo esc_html( $btn_text ); ?></span>
						<?php endif; ?>
					</button>
				</div>
				<div class="close">
					<?php if ( $close_icon ) : ?>
						<span class="search-icon-close">
							<?php Icons_Manager::render_icon( $close_icon, array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php else : ?>
						<span class="search-icon-close">
							<?php Icons_Manager::render_icon( $close_icon, array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php endif; ?>
				</div>

			</form>
		</div>

		<!-- Search JavaScript for Style 2 -->
		<script>
		(function($) {
			'use strict';
			$(document).ready(function() {
				var searchWrapper = $("#<?php echo esc_js( $unique_id ); ?>");

				if (searchWrapper.length) {
					var searchInput = searchWrapper.find('.search-input');
					var searchIcon = searchWrapper.find('.search-icon');
					var closeBtn = searchWrapper.find('.close');
					var searchForm = searchWrapper.find('form');

					// Toggle search on icon click
					searchIcon.on('click', function(e) {
						e.preventDefault();

						if (!searchWrapper.hasClass('active')) {
							// Open search
							searchWrapper.addClass('active');
							setTimeout(function() {
								searchInput.focus();
							}, 300);
						} else {
							// Submit form if search is active and has value
							var searchValue = searchInput.val().trim();
							if (searchValue !== '') {
								searchForm.submit();
							}
						}
					});

					// Close search
					closeBtn.on('click', function(e) {
						e.preventDefault();
						searchWrapper.removeClass('active');
						searchInput.val('');
					});

					// Submit on Enter key
					searchInput.on('keypress', function(e) {
						if (e.which === 13) {
							e.preventDefault();
							var searchValue = $(this).val().trim();
							if (searchValue !== '') {
								searchForm.submit();
							}
						}
					});

					// Close on ESC key
					$(document).on('keydown', function(e) {
						if (e.key === "Escape" && searchWrapper.hasClass("active")) {
							searchWrapper.removeClass("active");
							searchInput.val('');
						}
					});
				}
			});
		})(jQuery);
		</script>
		<?php
	}
}
