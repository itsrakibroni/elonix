<?php
/**
 * Elonix – Toolkit for Elementor Button Helper Class
 *
 * Provides reusable button registration and rendering logic.
 *
 * @package Elonix_Toolkit
 */

namespace {

	use Elementor\Controls_Manager;
	use Elementor\Group_Control_Typography;
	use Elementor\Group_Control_Border;
	use Elementor\Group_Control_Box_Shadow;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class Elonix_Button_Helper {

		/**
		 * Register button content controls.
		 *
		 * @param \Elementor\Widget_Base $widget The widget instance.
		 * @param string                 $prefix Prefix for control IDs.
		 * @param array                  $defaults Default values override.
		 */
		public static function register_button_content_controls( $widget, $prefix = '', $defaults = array() ) {
			// Preset System
			$widget->add_control(
				$prefix . 'button_preset',
				array(
					'label'   => esc_html__( 'Button Preset', 'elonix' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'solid',
					'options' => array(
						'solid'            => esc_html__( 'Solid Button', 'elonix' ),
						'outline'          => esc_html__( 'Outline Button', 'elonix' ),
						'modern-cta'       => esc_html__( 'Modern CTA Button', 'elonix' ),
						'book-appointment' => esc_html__( 'Book Appointment Style', 'elonix' ),
					),
				)
			);

			// Size System
			$widget->add_control(
				$prefix . 'button_size',
				array(
					'label'   => esc_html__( 'Button Size', 'elonix' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'medium',
					'options' => array(
						'small'  => esc_html__( 'Small', 'elonix' ),
						'medium' => esc_html__( 'Medium', 'elonix' ),
						'large'  => esc_html__( 'Large', 'elonix' ),
						'xl'     => esc_html__( 'Extra Large', 'elonix' ),
					),
				)
			);

			// Button Text
			$widget->add_control(
				$prefix . 'text',
				array(
					'label'       => esc_html__( 'Button Text', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => isset( $defaults['text'] ) ? $defaults['text'] : esc_html__( 'Click Here', 'elonix' ),
					'dynamic'     => array(
						'active' => true,
					),
					'placeholder' => esc_html__( 'Enter button label', 'elonix' ),
					'label_block' => true,
				)
			);

			// Link
			$widget->add_control(
				$prefix . 'link',
				array(
					'label'       => esc_html__( 'Link URL', 'elonix' ),
					'type'        => Controls_Manager::URL,
					'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
					'default'     => array(
						'url'         => isset( $defaults['url'] ) ? $defaults['url'] : '#',
						'is_external' => false,
						'nofollow'    => false,
					),
					'dynamic'     => array(
						'active' => true,
					),
					'label_block' => true,
				)
			);

			// Icon Settings Header
			$widget->add_control(
				$prefix . 'icon_heading',
				array(
					'label'     => esc_html__( 'Icon Settings', 'elonix' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			// Selected Icon
			$widget->add_control(
				$prefix . 'selected_icon',
				array(
					'label'            => esc_html__( 'Icon Selector', 'elonix' ),
					'type'             => Controls_Manager::ICONS,
					'fa4compatibility' => 'icon',
				)
			);

			// Icon Position
			$widget->add_control(
				$prefix . 'icon_position',
				array(
					'label'     => esc_html__( 'Icon Position', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'before',
					'options'   => array(
						'before' => esc_html__( 'Before Label', 'elonix' ),
						'after'  => esc_html__( 'After Label', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			// Arrow System Header
			$widget->add_control(
				$prefix . 'arrow_heading',
				array(
					'label'     => esc_html__( 'Arrow Settings', 'elonix' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			// Arrow Type
			$widget->add_control(
				$prefix . 'arrow_type',
				array(
					'label'     => esc_html__( 'Arrow Type', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'none',
					'options'   => array(
						'none'   => esc_html__( 'None', 'elonix' ),
						'line'   => esc_html__( 'Arrow Line', 'elonix' ),
						'circle' => esc_html__( 'Arrow Circle', 'elonix' ),
						'box'    => esc_html__( 'Arrow Box', 'elonix' ),
						'pill'   => esc_html__( 'Arrow Pill', 'elonix' ),
						'reveal' => esc_html__( 'Arrow Reveal (Hover)', 'elonix' ),
						'slide'  => esc_html__( 'Arrow Slide (Hover)', 'elonix' ),
						'expand' => esc_html__( 'Arrow Expand (Hover)', 'elonix' ),
						'offset' => esc_html__( 'Arrow Offset', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'button_preset!' => 'modern-cta',
					),
				)
			);

			// Arrow Icon
			$widget->add_control(
				$prefix . 'arrow_icon',
				array(
					'label'      => esc_html__( 'Arrow Icon', 'elonix' ),
					'type'       => Controls_Manager::ICONS,
					'default'    => array(
						'value'   => 'fas fa-angles-right',
						'library' => 'fa-solid',
					),
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'name'     => $prefix . 'arrow_type',
								'operator' => '!=',
								'value'    => 'none',
							),
							array(
								'name'     => $prefix . 'button_preset',
								'operator' => '==',
								'value'    => 'modern-cta',
							),
						),
					),
				)
			);

			// Accessibility & Attributes Section
			$widget->add_control(
				$prefix . 'accessibility_heading',
				array(
					'label'     => esc_html__( 'Accessibility & Attributes', 'elonix' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$widget->add_control(
				$prefix . 'download',
				array(
					'label'        => esc_html__( 'Download Attribute', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$widget->add_control(
				$prefix . 'custom_rel',
				array(
					'label'       => esc_html__( 'Custom Rel', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'e.g. sponsor, ugc', 'elonix' ),
					'dynamic'     => array(
						'active' => true,
					),
				)
			);

			$widget->add_control(
				$prefix . 'aria_label',
				array(
					'label'       => esc_html__( 'Aria Label', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'Accessibility label description', 'elonix' ),
					'dynamic'     => array(
						'active' => true,
					),
					'label_block' => true,
				)
			);

			// Elonix CTA - Text Position
			$widget->add_control(
				$prefix . 'text_position',
				array(
					'label'     => esc_html__( 'Text Position', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'left',
					'options'   => array(
						'left'  => esc_html__( 'Left', 'elonix' ),
						'right' => esc_html__( 'Right', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'button_preset' => 'modern-cta',
					),
				)
			);

			// Elonix CTA - Arrow Position
			$widget->add_control(
				$prefix . 'arrow_position',
				array(
					'label'     => esc_html__( 'Arrow Position', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'right',
					'options'   => array(
						'left'  => esc_html__( 'Left', 'elonix' ),
						'right' => esc_html__( 'Right', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'button_preset' => 'modern-cta',
					),
				)
			);

			// Book Appointment Content Settings
			$widget->add_control(
				$prefix . 'ba_icon',
				array(
					'label'     => esc_html__( 'Book Appointment Icon', 'elonix' ),
					'type'      => Controls_Manager::ICONS,
					'default'   => array(
						'value'   => 'fas fa-angles-right',
						'library' => 'fa-solid',
					),
					'condition' => array(
						$prefix . 'button_preset' => 'book-appointment',
					),
				)
			);

			$widget->add_control(
				$prefix . 'ba_icon_position',
				array(
					'label'     => esc_html__( 'Icon Position', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'right',
					'options'   => array(
						'left'  => esc_html__( 'Left', 'elonix' ),
						'right' => esc_html__( 'Right', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'button_preset' => 'book-appointment',
					),
				)
			);

			$widget->add_control(
				$prefix . 'ba_hover_style',
				array(
					'label'     => esc_html__( 'Smart Hover Effect', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'style-1',
					'options'   => array(
						'style-1' => esc_html__( 'Hover Style 1: Icon Slide Right', 'elonix' ),
						'style-2' => esc_html__( 'Hover Style 2: Icon Rotate 15deg', 'elonix' ),
						'style-3' => esc_html__( 'Hover Style 3: Arrow Move Continuous', 'elonix' ),
						'style-4' => esc_html__( 'Hover Style 4: Background Fill', 'elonix' ),
						'style-5' => esc_html__( 'Hover Style 5: Magnetic Shift', 'elonix' ),
						'style-6' => esc_html__( 'Hover Style 6: Icon Area Expand', 'elonix' ),
						'style-7' => esc_html__( 'Hover Style 7: Double Shift (Text & Icon)', 'elonix' ),
						'style-8' => esc_html__( 'Hover Style 8: Premium Micro-Interaction', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'button_preset' => 'book-appointment',
					),
				)
			);
		}

		/**
		 * Register button style controls.
		 *
		 * @param \Elementor\Widget_Base $widget The widget instance.
		 * @param string                 $prefix Prefix for control IDs.
		 * @param array                  $selectors Selectors mappings.
		 */
		public static function register_button_style_controls( $widget, $prefix = '', $selectors = array(), $condition = array() ) {
			$btn_selector   = isset( $selectors['button'] ) ? $selectors['button'] : '{{WRAPPER}} .elonix-advanced-button';
			$icon_selector  = isset( $selectors['icon'] ) ? $selectors['icon'] : '{{WRAPPER}} .elonix-button-icon';
			$arrow_selector = isset( $selectors['arrow'] ) ? $selectors['arrow'] : '{{WRAPPER}} .elonix-button-arrow';

			$widget->start_controls_section(
				$prefix . 'button_style_section',
				array(
					'label'     => esc_html__( 'Button Style', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			// Width, Height, Min Width, Min Height Controls (Responsive)
			$widget->add_responsive_control(
				$prefix . 'button_width',
				array(
					'label'      => esc_html__( 'Button Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw' ),
					'range'      => array(
						'px' => array(
							'min' => 50,
							'max' => 800,
						),
					),
					'selectors'  => array(
						$btn_selector => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'button_height',
				array(
					'label'      => esc_html__( 'Button Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'vh' ),
					'range'      => array(
						'px' => array(
							'min' => 20,
							'max' => 200,
						),
					),
					'selectors'  => array(
						$btn_selector => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'button_min_width',
				array(
					'label'      => esc_html__( 'Min Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 500,
						),
					),
					'selectors'  => array(
						$btn_selector => 'min-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'button_min_height',
				array(
					'label'      => esc_html__( 'Min Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'vh', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 200,
						),
					),
					'selectors'  => array(
						$btn_selector => 'min-height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'button_transition_duration',
				array(
					'label'      => esc_html__( 'Transition Duration (s)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 's' ),
					'range'      => array(
						's' => array(
							'min'  => 0,
							'max'  => 5,
							'step' => 0.1,
						),
					),
					'default'    => array(
						'size' => 0.4,
						'unit' => 's',
					),
					'selectors'  => array(
						$btn_selector => 'transition-duration: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Container Width (Responsive)
			$widget->add_responsive_control(
				$prefix . 'button_container_width',
				array(
					'label'      => esc_html__( 'Container Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw' ),
					'range'      => array(
						'px' => array(
							'min' => 50,
							'max' => 1200,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .elonix-advanced-button-wrapper' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%; display: inline-block;',
					),
				)
			);

			// Content Gap (Responsive)
			$widget->add_responsive_control(
				$prefix . 'button_gap',
				array(
					'label'      => esc_html__( 'Content Gap', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .elonix-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'button_preset!' => 'modern-cta',
					),
				)
			);

			// Typography Group Control (Responsive)
			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'typography',
					'selector' => $btn_selector,
				)
			);

			// Tab Controls for States: Normal, Hover, Active, Focus
			$widget->start_controls_tabs( $prefix . 'tabs_button_style' );

			// Normal State
			$widget->start_controls_tab(
				$prefix . 'tab_button_normal',
				array(
					'label' => esc_html__( 'Normal', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'border',
					'selector' => $btn_selector,
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'box_shadow',
					'selector' => $btn_selector,
				)
			);

			$widget->end_controls_tab();

			// Hover State
			$widget->start_controls_tab(
				$prefix . 'tab_button_hover',
				array(
					'label' => esc_html__( 'Hover', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'hover_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'hover_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();

			// Active State
			$widget->start_controls_tab(
				$prefix . 'tab_button_active',
				array(
					'label' => esc_html__( 'Active', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'active_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':active' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'active_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':active' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'active_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':active' => 'border-color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();

			// Focus State
			$widget->start_controls_tab(
				$prefix . 'tab_button_focus',
				array(
					'label' => esc_html__( 'Focus', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'focus_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':focus, ' . $btn_selector . ':focus-visible' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'focus_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':focus, ' . $btn_selector . ':focus-visible' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'focus_ring_color',
				array(
					'label'     => esc_html__( 'Accessibility Focus Ring Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#3b82f6',
					'selectors' => array(
						$btn_selector . ':focus, ' . $btn_selector . ':focus-visible' => 'outline: 3px solid {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			// Border Radius (Responsive)
			$widget->add_responsive_control(
				$prefix . 'border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$btn_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'separator'  => 'before',
				)
			);

			// Padding (Responsive)
			$widget->add_responsive_control(
				$prefix . 'padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$btn_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Margin (Responsive)
			$widget->add_responsive_control(
				$prefix . 'margin',
				array(
					'label'      => esc_html__( 'Margin', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$btn_selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_section();

			// Icon Sizing & Design System Section
			$widget->start_controls_section(
				$prefix . 'button_icon_style_section',
				array(
					'label'     => esc_html__( 'Button Icon Style', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'selected_icon[value]!' => '',
						)
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_style_size',
				array(
					'label'      => esc_html__( 'Icon Size', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'range'      => array(
						'px' => array(
							'min' => 6,
							'max' => 60,
						),
					),
					'selectors'  => array(
						$icon_selector . ' i'   => 'font-size: {{SIZE}}{{UNIT}};',
						$icon_selector . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			// Icon Spacing (Responsive)
			$widget->add_responsive_control(
				$prefix . 'button_icon_spacing',
				array(
					'label'      => esc_html__( 'Icon Spacing', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .elonix-align-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .elonix-align-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'tabs_icon_style' );

			$widget->start_controls_tab(
				$prefix . 'tab_icon_normal',
				array(
					'label'     => esc_html__( 'Normal', 'elonix' ),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$icon_selector . ' i'   => 'color: {{VALUE}};',
						$icon_selector . ' svg' => 'fill: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_bg_color',
				array(
					'label'     => esc_html__( 'Icon Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$icon_selector => 'background-color: {{VALUE}}; padding: 5px; display: inline-flex;',
					),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_border_radius',
				array(
					'label'      => esc_html__( 'Icon Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						$icon_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->start_controls_tab(
				$prefix . 'tab_icon_hover',
				array(
					'label'     => esc_html__( 'Hover', 'elonix' ),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_color_hover',
				array(
					'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover ' . $icon_selector . ' i' => 'color: {{VALUE}};',
						$btn_selector . ':hover ' . $icon_selector . ' svg' => 'fill: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_bg_color_hover',
				array(
					'label'     => esc_html__( 'Icon Hover Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover ' . $icon_selector => 'background-color: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'selected_icon[value]!' => '',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// Arrow System Styling Section
			$sec_conds = array(
				$prefix . 'arrow_type!'    => 'none',
				$prefix . 'button_preset!' => 'modern-cta',
			);
			if ( ! empty( $condition ) ) {
				$sec_conds = array_merge( $sec_conds, $condition );
			}

			$widget->start_controls_section(
				$prefix . 'button_arrow_style_section',
				array(
					'label'     => esc_html__( 'Button Arrow Style', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $sec_conds,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'arrow_style_size',
				array(
					'label'      => esc_html__( 'Arrow Size', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'range'      => array(
						'px' => array(
							'min' => 6,
							'max' => 60,
						),
					),
					'selectors'  => array(
						$arrow_selector . ' i'   => 'font-size: {{SIZE}}{{UNIT}};',
						$arrow_selector . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			// Arrow Spacing (Responsive)
			$widget->add_responsive_control(
				$prefix . 'button_arrow_spacing',
				array(
					'label'      => esc_html__( 'Arrow Spacing', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .elonix-button-arrow' => 'margin-left: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'tabs_arrow_style' );

			$widget->start_controls_tab(
				$prefix . 'tab_arrow_normal',
				array(
					'label'     => esc_html__( 'Normal', 'elonix' ),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->add_control(
				$prefix . 'arrow_color',
				array(
					'label'     => esc_html__( 'Arrow Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$arrow_selector . ' i'   => 'color: {{VALUE}};',
						$arrow_selector . ' svg' => 'fill: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->add_control(
				$prefix . 'arrow_bg_color',
				array(
					'label'     => esc_html__( 'Arrow Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$arrow_selector => 'background-color: {{VALUE}}; padding: 8px; display: inline-flex; align-items: center; justify-content: center;',
					),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->add_control(
				$prefix . 'arrow_border_radius',
				array(
					'label'      => esc_html__( 'Arrow Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						$arrow_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->start_controls_tab(
				$prefix . 'tab_arrow_hover',
				array(
					'label'     => esc_html__( 'Hover', 'elonix' ),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->add_control(
				$prefix . 'arrow_color_hover',
				array(
					'label'     => esc_html__( 'Arrow Hover Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover ' . $arrow_selector . ' i' => 'color: {{VALUE}};',
						$btn_selector . ':hover ' . $arrow_selector . ' svg' => 'fill: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->add_control(
				$prefix . 'arrow_bg_color_hover',
				array(
					'label'     => esc_html__( 'Arrow Hover Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$btn_selector . ':hover ' . $arrow_selector => 'background-color: {{VALUE}};',
					),
					'condition' => array(
						$prefix . 'arrow_type!' => 'none',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// ==========================================
			// MODERN CTA STYLE CONTROLS
			// ==========================================
			$cta_outer_selector      = '{{WRAPPER}} .elonix-advanced-button.es-btn-preset-modern-cta';
			$cta_text_selector       = '{{WRAPPER}} .elonix-advanced-button.es-btn-preset-modern-cta .elonix-cta-text-area';
			$cta_arrow_selector      = '{{WRAPPER}} .elonix-advanced-button.es-btn-preset-modern-cta .elonix-cta-arrow-area';
			$cta_arrow_icon_selector = '{{WRAPPER}} .elonix-advanced-button.es-btn-preset-modern-cta .elonix-cta-arrow-area i, {{WRAPPER}} .elonix-advanced-button.es-btn-preset-modern-cta .elonix-cta-arrow-area svg';

			// Section 4: Modern CTA - Outer Container
			$widget->start_controls_section(
				$prefix . 'cta_layout_section',
				array(
					'label'     => esc_html__( 'Modern CTA - Outer Container', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'button_preset' => 'modern-cta',
						)
					),
				)
			);

			// Text / Arrow Gap (Responsive)
			$widget->add_responsive_control(
				$prefix . 'cta_text_arrow_gap',
				array(
					'label'      => esc_html__( 'Text / Arrow Gap', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector => 'gap: {{SIZE}}{{UNIT}}; --es-cta-text-arrow-gap: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'cta_outer_tabs' );

			// Normal Tab
			$widget->start_controls_tab(
				$prefix . 'cta_outer_normal_tab',
				array(
					'label' => esc_html__( 'Normal', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'cta_outer_bg',
				array(
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'cta_outer_border',
					'selector' => $cta_outer_selector,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_outer_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_outer_box_shadow',
					'selector' => $cta_outer_selector,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_outer_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --es-cta-outer-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_margin',
				array(
					'label'      => esc_html__( 'Margin', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_outer_selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();

			// Hover Tab
			$widget->start_controls_tab(
				$prefix . 'cta_outer_hover_tab',
				array(
					'label' => esc_html__( 'Hover', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'cta_outer_bg_hover',
				array(
					'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'cta_outer_border_hover',
					'selector' => $cta_outer_selector . ':hover',
				)
			);

			$widget->add_control(
				$prefix . 'cta_outer_border_color_hover',
				array(
					'label'     => esc_html__( 'Hover Border Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_border_radius_hover',
				array(
					'label'      => esc_html__( 'Hover Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_outer_selector . ':hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_outer_box_shadow_hover',
					'selector' => $cta_outer_selector . ':hover',
				)
			);

			// Transform controls
			$widget->add_control(
				$prefix . 'cta_outer_scale_hover',
				array(
					'label'      => esc_html__( 'Hover Scale', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ratio' ),
					'range'      => array(
						'ratio' => array(
							'min'  => 0.5,
							'max'  => 2,
							'step' => 0.05,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover' => '--es-cta-outer-scale: {{SIZE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_outer_rotate_hover',
				array(
					'label'      => esc_html__( 'Hover Rotation (deg)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'deg' ),
					'range'      => array(
						'deg' => array(
							'min'  => -360,
							'max'  => 360,
							'step' => 5,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover' => '--es-cta-outer-rotate: {{SIZE}}deg;',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_translate_x_hover',
				array(
					'label'      => esc_html__( 'Hover Translate X', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover' => '--es-cta-outer-translate-x: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_outer_translate_y_hover',
				array(
					'label'      => esc_html__( 'Hover Translate Y', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover' => '--es-cta-outer-translate-y: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_outer_opacity_hover',
				array(
					'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1,
							'step' => 0.05,
						),
					),
					'selectors' => array(
						$cta_outer_selector . ':hover' => 'opacity: {{SIZE}};',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// Section 5: Modern CTA - Text Area
			$widget->start_controls_section(
				$prefix . 'cta_text_area_section',
				array(
					'label'     => esc_html__( 'Modern CTA - Text Area', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'button_preset' => 'modern-cta',
						)
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'cta_text_tabs' );

			// Normal Tab
			$widget->start_controls_tab(
				$prefix . 'cta_text_normal_tab',
				array(
					'label' => esc_html__( 'Normal', 'elonix' ),
				)
			);

			// Text Area Background
			$widget->add_control(
				$prefix . 'cta_text_bg',
				array(
					'label'     => esc_html__( 'Text Area Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_text_selector => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_text_selector => 'color: {{VALUE}};',
						$cta_text_selector . ' .elonix-button-text' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'cta_text_typography',
					'selector' => $cta_text_selector,
				)
			);

			// Text Stroke Width
			$widget->add_control(
				$prefix . 'cta_text_stroke_width',
				array(
					'label'      => esc_html__( 'Text Stroke Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 10,
							'step' => 0.5,
						),
					),
					'selectors'  => array(
						$cta_text_selector . ' .elonix-button-text' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; text-stroke-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Text Stroke Color
			$widget->add_control(
				$prefix . 'cta_text_stroke_color',
				array(
					'label'     => esc_html__( 'Text Stroke Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_text_selector . ' .elonix-button-text' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_text_shadow',
					'selector' => $cta_text_selector,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_text_padding',
				array(
					'label'      => esc_html__( 'Text Area Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_text_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --es-cta-text-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_text_border_radius',
				array(
					'label'      => esc_html__( 'Text Area Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_text_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();

			// Hover Tab
			$widget->start_controls_tab(
				$prefix . 'cta_text_hover_tab',
				array(
					'label' => esc_html__( 'Hover', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_color_hover',
				array(
					'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => 'color: {{VALUE}};',
						$cta_outer_selector . ':hover .elonix-cta-text-area .elonix-button-text' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_bg_hover',
				array(
					'label'     => esc_html__( 'Text Hover Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'cta_text_typography_hover',
					'selector' => $cta_outer_selector . ':hover .elonix-cta-text-area',
				)
			);

			$widget->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_text_shadow_hover',
					'selector' => $cta_outer_selector . ':hover .elonix-cta-text-area',
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_stroke_width_hover',
				array(
					'label'      => esc_html__( 'Hover Text Stroke Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 10,
							'step' => 0.5,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area .elonix-button-text' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; text-stroke-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_stroke_color_hover',
				array(
					'label'     => esc_html__( 'Hover Text Stroke Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area .elonix-button-text' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_opacity_hover',
				array(
					'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1,
							'step' => 0.05,
						),
					),
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => 'opacity: {{SIZE}};',
					),
				)
			);

			// Transform controls for Text Area
			$widget->add_control(
				$prefix . 'cta_text_scale_hover',
				array(
					'label'      => esc_html__( 'Hover Scale', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ratio' ),
					'range'      => array(
						'ratio' => array(
							'min'  => 0.5,
							'max'  => 2,
							'step' => 0.05,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => '--es-cta-text-scale: {{SIZE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_text_rotate_hover',
				array(
					'label'      => esc_html__( 'Hover Rotation (deg)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'deg' ),
					'range'      => array(
						'deg' => array(
							'min'  => -360,
							'max'  => 360,
							'step' => 5,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => '--es-cta-text-rotate: {{SIZE}}deg;',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_text_translate_x_hover',
				array(
					'label'      => esc_html__( 'Hover Translate X', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => '--es-cta-text-translate-x: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_text_translate_y_hover',
				array(
					'label'      => esc_html__( 'Hover Translate Y', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-text-area' => '--es-cta-text-translate-y: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// Section 6: Modern CTA - Arrow Area
			$widget->start_controls_section(
				$prefix . 'cta_arrow_area_section',
				array(
					'label'     => esc_html__( 'Modern CTA - Arrow Area', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'button_preset' => 'modern-cta',
						)
					),
				)
			);

			// Arrow Icon Size (Responsive)
			$widget->add_responsive_control(
				$prefix . 'cta_arrow_size',
				array(
					'label'      => esc_html__( 'Arrow Icon Size', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 6,
							'max' => 60,
						),
					),
					'selectors'  => array(
						$cta_arrow_icon_selector => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'cta_arrow_tabs' );

			// Normal Tab
			$widget->start_controls_tab(
				$prefix . 'cta_arrow_normal_tab',
				array(
					'label' => esc_html__( 'Normal', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_bg',
				array(
					'label'     => esc_html__( 'Arrow Area Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_arrow_selector => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_icon_color',
				array(
					'label'     => esc_html__( 'Arrow Icon Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_arrow_icon_selector => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_width',
				array(
					'label'      => esc_html__( 'Arrow Area Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 150,
						),
					),
					'selectors'  => array(
						$cta_arrow_selector => 'width: {{SIZE}}{{UNIT}}; --es-cta-arrow-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_height',
				array(
					'label'      => esc_html__( 'Arrow Area Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 150,
						),
					),
					'selectors'  => array(
						$cta_arrow_selector => 'height: {{SIZE}}{{UNIT}}; --es-cta-arrow-height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_padding',
				array(
					'label'      => esc_html__( 'Arrow Area Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_arrow_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --es-cta-arrow-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_margin',
				array(
					'label'      => esc_html__( 'Arrow Area Margin', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_arrow_selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --es-cta-arrow-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'cta_arrow_border',
					'selector' => $cta_arrow_selector,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_border_radius',
				array(
					'label'      => esc_html__( 'Arrow Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_arrow_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_arrow_box_shadow',
					'selector' => $cta_arrow_selector,
				)
			);

			$widget->end_controls_tab();

			// Hover Tab
			$widget->start_controls_tab(
				$prefix . 'cta_arrow_hover_tab',
				array(
					'label' => esc_html__( 'Hover', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_bg_hover',
				array(
					'label'     => esc_html__( 'Arrow Hover Background Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => 'background-color: {{VALUE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_icon_color_hover',
				array(
					'label'     => esc_html__( 'Arrow Hover Icon Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area i, ' . $cta_outer_selector . ':hover .elonix-cta-arrow-area svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'cta_arrow_border_hover',
					'selector' => $cta_outer_selector . ':hover .elonix-cta-arrow-area',
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_border_color_hover',
				array(
					'label'     => esc_html__( 'Hover Border Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => 'border-color: {{VALUE}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_border_radius_hover',
				array(
					'label'      => esc_html__( 'Hover Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'cta_arrow_box_shadow_hover',
					'selector' => $cta_outer_selector . ':hover .elonix-cta-arrow-area',
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_opacity_hover',
				array(
					'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1,
							'step' => 0.05,
						),
					),
					'selectors' => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => 'opacity: {{SIZE}};',
					),
				)
			);

			// Transform controls for Arrow Area
			$widget->add_control(
				$prefix . 'cta_arrow_scale_hover',
				array(
					'label'      => esc_html__( 'Hover Scale', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ratio' ),
					'range'      => array(
						'ratio' => array(
							'min'  => 0.5,
							'max'  => 2,
							'step' => 0.05,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => '--es-cta-arrow-scale: {{SIZE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_arrow_rotate_hover',
				array(
					'label'      => esc_html__( 'Hover Rotation (deg)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'deg' ),
					'range'      => array(
						'deg' => array(
							'min'  => -360,
							'max'  => 360,
							'step' => 5,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => '--es-cta-arrow-rotate: {{SIZE}}deg;',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_translate_x_hover',
				array(
					'label'      => esc_html__( 'Hover Translate X', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => '--es-cta-arrow-translate-x: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_arrow_translate_y_hover',
				array(
					'label'      => esc_html__( 'Hover Translate Y', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -100,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$cta_outer_selector . ':hover .elonix-cta-arrow-area' => '--es-cta-arrow-translate-y: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// Section 7: Modern CTA - Hover & Animations
			$widget->start_controls_section(
				$prefix . 'cta_hover_section',
				array(
					'label'     => esc_html__( 'Modern CTA - Hover & Animations', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'button_preset' => 'modern-cta',
						)
					),
				)
			);

			// Hover Animation Selector
			$widget->add_control(
				$prefix . 'cta_hover_animation',
				array(
					'label'          => esc_html__( 'Hover Animation', 'elonix' ),
					'type'           => Controls_Manager::SELECT2,
					'default'        => 'none',
					'label_block'    => true,
					'select2options' => array(
						'placeholder' => esc_html__( 'Select animation...', 'elonix' ),
						'allowClear'  => true,
					),
					'options'        => array(
						'none'              => esc_html__( 'None', 'elonix' ),
						// Basic Group
						'grow'              => esc_html__( 'Grow (Basic)', 'elonix' ),
						'shrink'            => esc_html__( 'Shrink (Basic)', 'elonix' ),
						'pulse'             => esc_html__( 'Pulse (Basic)', 'elonix' ),
						'pulse-grow'        => esc_html__( 'Pulse Grow (Basic)', 'elonix' ),
						'pulse-shrink'      => esc_html__( 'Pulse Shrink (Basic)', 'elonix' ),
						// Entrance Group
						'push'              => esc_html__( 'Push (Entrance)', 'elonix' ),
						'pop'               => esc_html__( 'Pop (Entrance)', 'elonix' ),
						'bounce-in'         => esc_html__( 'Bounce In (Entrance)', 'elonix' ),
						'bounce-out'        => esc_html__( 'Bounce Out (Entrance)', 'elonix' ),
						// Rotation Group
						'rotate'            => esc_html__( 'Rotate (Rotation)', 'elonix' ),
						'grow-rotate'       => esc_html__( 'Grow Rotate (Rotation)', 'elonix' ),
						// Movement Group
						'forward'           => esc_html__( 'Forward (Movement)', 'elonix' ),
						'backward'          => esc_html__( 'Backward (Movement)', 'elonix' ),
						'float'             => esc_html__( 'Float (Movement)', 'elonix' ),
						'sink'              => esc_html__( 'Sink (Movement)', 'elonix' ),
						'bob'               => esc_html__( 'Bob (Movement)', 'elonix' ),
						'hang'              => esc_html__( 'Hang (Movement)', 'elonix' ),
						// Wobble Group
						'wobble-horizontal' => esc_html__( 'Wobble Horizontal (Wobble)', 'elonix' ),
						'wobble-vertical'   => esc_html__( 'Wobble Vertical (Wobble)', 'elonix' ),
						// Attention Group
						'buzz'              => esc_html__( 'Buzz (Attention)', 'elonix' ),
						'buzz-out'          => esc_html__( 'Buzz Out (Attention)', 'elonix' ),
						'shake'             => esc_html__( 'Shake (Attention)', 'elonix' ),
						'swing'             => esc_html__( 'Swing (Attention)', 'elonix' ),
						'tada'              => esc_html__( 'Tada (Attention)', 'elonix' ),
						'rubber-band'       => esc_html__( 'Rubber Band (Attention)', 'elonix' ),
						'jello'             => esc_html__( 'Jello (Attention)', 'elonix' ),
						// Premium CTA Group
						'arrow-slide-right' => esc_html__( 'Arrow Slide Right (Premium CTA)', 'elonix' ),
						'arrow-slide-left'  => esc_html__( 'Arrow Slide Left (Premium CTA)', 'elonix' ),
						'arrow-bounce'      => esc_html__( 'Arrow Bounce (Premium CTA)', 'elonix' ),
						'arrow-expand'      => esc_html__( 'Arrow Expand (Premium CTA)', 'elonix' ),
						'arrow-rotate'      => esc_html__( 'Arrow Rotate (Premium CTA)', 'elonix' ),
						'arrow-reveal'      => esc_html__( 'Arrow Reveal (Premium CTA)', 'elonix' ),
						'background-sweep'  => esc_html__( 'Background Sweep (Premium CTA)', 'elonix' ),
						'background-fill'   => esc_html__( 'Background Fill (Premium CTA)', 'elonix' ),
						'split-cta'         => esc_html__( 'Split CTA (Premium CTA)', 'elonix' ),
						'premium-cta-hover' => esc_html__( 'Premium CTA Hover (Premium CTA)', 'elonix' ),
					),
				)
			);

			// Animation Target Selector
			$widget->add_control(
				$prefix . 'cta_anim_target',
				array(
					'label'     => esc_html__( 'Hover Animation Target', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'button',
					'options'   => array(
						'button' => esc_html__( 'Entire Button', 'elonix' ),
						'text'   => esc_html__( 'Text Area Only', 'elonix' ),
						'arrow'  => esc_html__( 'Arrow Area Only', 'elonix' ),
						'both'   => esc_html__( 'Text + Arrow', 'elonix' ),
					),
					'condition' => array(
						$prefix . 'cta_hover_animation!' => 'none',
					),
				)
			);

			// Animation settings selectors mapping base
			$widget->add_control(
				$prefix . 'cta_anim_duration',
				array(
					'label'      => esc_html__( 'Animation Duration (ms)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ms' ),
					'range'      => array(
						'ms' => array(
							'min'  => 100,
							'max'  => 3000,
							'step' => 50,
						),
					),
					'default'    => array(
						'size' => 300,
						'unit' => 'ms',
					),
					'selectors'  => array(
						$cta_outer_selector => '--es-cta-anim-duration: {{SIZE}}ms;',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_anim_delay',
				array(
					'label'      => esc_html__( 'Animation Delay (ms)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ms' ),
					'range'      => array(
						'ms' => array(
							'min'  => 0,
							'max'  => 2000,
							'step' => 50,
						),
					),
					'selectors'  => array(
						$cta_outer_selector => '--es-cta-anim-delay: {{SIZE}}ms;',
					),
				)
			);

			// Animation Timing Function
			$widget->add_control(
				$prefix . 'cta_anim_timing',
				array(
					'label'     => esc_html__( 'Animation Timing Function', 'elonix' ),
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
						$cta_outer_selector => '--es-cta-anim-timing: {{VALUE}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'cta_anim_distance',
				array(
					'label'      => esc_html__( 'Animation Distance', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => -50,
							'max' => 50,
						),
					),
					'default'    => array(
						'size' => 8,
						'unit' => 'px',
					),
					'selectors'  => array(
						$cta_outer_selector => '--es-cta-anim-distance: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_anim_scale',
				array(
					'label'      => esc_html__( 'Animation Scale', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ratio' ),
					'range'      => array(
						'ratio' => array(
							'min'  => 0.5,
							'max'  => 2,
							'step' => 0.05,
						),
					),
					'default'    => array(
						'size' => 1.1,
						'unit' => 'ratio',
					),
					'selectors'  => array(
						$cta_outer_selector => '--es-cta-anim-scale: {{SIZE}};',
					),
				)
			);

			$widget->add_control(
				$prefix . 'cta_anim_rotation',
				array(
					'label'      => esc_html__( 'Animation Rotation (deg)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'deg' ),
					'range'      => array(
						'deg' => array(
							'min'  => -360,
							'max'  => 360,
							'step' => 5,
						),
					),
					'default'    => array(
						'size' => 4,
						'unit' => 'deg',
					),
					'selectors'  => array(
						$cta_outer_selector => '--es-cta-anim-rotation: {{SIZE}}deg;',
					),
				)
			);

			$widget->end_controls_section();

			// Book Appointment Style Controls
			$widget->start_controls_section(
				$prefix . 'book_appointment_style_section',
				array(
					'label'     => esc_html__( 'Book Appointment Style Settings', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array_merge(
						$condition,
						array(
							$prefix . 'button_preset' => 'book-appointment',
						)
					),
				)
			);

			// Enable Preset Switcher
			$widget->add_control(
				$prefix . 'ba_enable_preset',
				array(
					'label'        => esc_html__( 'Enable Preset Layout', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			// Button Width
			$widget->add_responsive_control(
				$prefix . 'ba_button_width',
				array(
					'label'      => esc_html__( 'Button Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw' ),
					'range'      => array(
						'px' => array(
							'min' => 50,
							'max' => 800,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Button Height
			$widget->add_responsive_control(
				$prefix . 'ba_button_height',
				array(
					'label'      => esc_html__( 'Button Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'vh' ),
					'range'      => array(
						'px' => array(
							'min' => 20,
							'max' => 200,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Button Padding
			$widget->add_responsive_control(
				$prefix . 'ba_button_padding',
				array(
					'label'      => esc_html__( 'Button Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Border Radius
			$widget->add_responsive_control(
				$prefix . 'ba_button_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Text / Icon Gap
			$widget->add_responsive_control(
				$prefix . 'ba_button_gap',
				array(
					'label'      => esc_html__( 'Text/Icon Gap', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'gap: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Typography
			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'ba_typography',
					'selector' => '{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-text',
				)
			);

			// Divider: Icon Container
			$widget->add_control(
				$prefix . 'ba_icon_container_heading',
				array(
					'label'     => esc_html__( 'Icon Container Settings', 'elonix' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			// Icon Width
			$widget->add_responsive_control(
				$prefix . 'ba_icon_width',
				array(
					'label'      => esc_html__( 'Icon Container Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 150,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Icon Height
			$widget->add_responsive_control(
				$prefix . 'ba_icon_height',
				array(
					'label'      => esc_html__( 'Icon Container Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 150,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			// Icon Border Radius
			$widget->add_responsive_control(
				$prefix . 'ba_icon_border_radius',
				array(
					'label'      => esc_html__( 'Icon Container Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Icon Padding
			$widget->add_responsive_control(
				$prefix . 'ba_icon_padding',
				array(
					'label'      => esc_html__( 'Icon Container Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			// Offset Position
			$widget->add_responsive_control(
				$prefix . 'ba_icon_offset',
				array(
					'label'      => esc_html__( 'Icon Offset Position', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem', '%' ),
					'range'      => array(
						'px' => array(
							'min' => -50,
							'max' => 50,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'transform: translateX({{SIZE}}{{UNIT}});',
					),
				)
			);

			// Colors: Normal / Hover Tabs
			$widget->start_controls_tabs( $prefix . 'tabs_ba_colors' );

			// Normal Tab
			$widget->start_controls_tab(
				$prefix . 'tab_ba_normal',
				array(
					'label' => esc_html__( 'Normal', 'elonix' ),
				)
			);

			// Button Background
			$widget->add_control(
				$prefix . 'ba_bg_color',
				array(
					'label'     => esc_html__( 'Button Background', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'background-color: {{VALUE}};',
					),
				)
			);

			// Button Text Color
			$widget->add_control(
				$prefix . 'ba_text_color',
				array(
					'label'     => esc_html__( 'Button Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-text' => 'color: {{VALUE}};',
					),
				)
			);

			// Icon Area Background
			$widget->add_control(
				$prefix . 'ba_icon_bg_color',
				array(
					'label'     => esc_html__( 'Icon Area Background', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container' => 'background-color: {{VALUE}};',
					),
				)
			);

			// Icon Color
			$widget->add_control(
				$prefix . 'ba_icon_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container i' => 'color: {{VALUE}};',
						'{{WRAPPER}} .es-btn-preset-book-appointment .elonix-button-icon-container svg' => 'fill: {{VALUE}};',
					),
				)
			);

			// Border Color
			$widget->add_control(
				$prefix . 'ba_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'border-color: {{VALUE}}; border-style: solid; border-width: 1px;',
					),
				)
			);

			// Box Shadow
			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'ba_box_shadow',
					'selector' => '{{WRAPPER}} .es-btn-preset-book-appointment',
				)
			);

			// Backdrop Blur
			$widget->add_control(
				$prefix . 'ba_backdrop_blur',
				array(
					'label'      => esc_html__( 'Backdrop Blur', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}); -webkit-backdrop-filter: blur({{SIZE}}{{UNIT}});',
					),
				)
			);

			$widget->end_controls_tab();

			// Hover Tab
			$widget->start_controls_tab(
				$prefix . 'tab_ba_hover',
				array(
					'label' => esc_html__( 'Hover', 'elonix' ),
				)
			);

			// Button Hover Background
			$widget->add_control(
				$prefix . 'ba_bg_color_hover',
				array(
					'label'     => esc_html__( 'Button Hover Background', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			// Button Hover Text Color
			$widget->add_control(
				$prefix . 'ba_text_color_hover',
				array(
					'label'     => esc_html__( 'Button Hover Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover .elonix-button-text' => 'color: {{VALUE}};',
					),
				)
			);

			// Icon Area Hover Background
			$widget->add_control(
				$prefix . 'ba_icon_bg_color_hover',
				array(
					'label'     => esc_html__( 'Icon Area Hover Background', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover .elonix-button-icon-container' => 'background-color: {{VALUE}};',
					),
				)
			);

			// Icon Hover Color
			$widget->add_control(
				$prefix . 'ba_icon_color_hover',
				array(
					'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover .elonix-button-icon-container i' => 'color: {{VALUE}};',
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover .elonix-button-icon-container svg' => 'fill: {{VALUE}};',
					),
				)
			);

			// Border Hover Color
			$widget->add_control(
				$prefix . 'ba_border_color_hover',
				array(
					'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment:hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			// Divider: Animations
			$widget->add_control(
				$prefix . 'ba_animations_heading',
				array(
					'label'     => esc_html__( 'Animation Settings', 'elonix' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			// Animation Duration
			$widget->add_control(
				$prefix . 'ba_anim_duration',
				array(
					'label'      => esc_html__( 'Animation Duration (ms)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ms' ),
					'range'      => array(
						'ms' => array(
							'min'  => 100,
							'max'  => 3000,
							'step' => 50,
						),
					),
					'default'    => array(
						'size' => 300,
						'unit' => 'ms',
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment, {{WRAPPER}} .es-btn-preset-book-appointment *, {{WRAPPER}} .elonix-button-icon-container i, {{WRAPPER}} .elonix-button-icon-container svg' => 'transition-duration: {{SIZE}}ms !important;',
					),
				)
			);

			// Animation Delay
			$widget->add_control(
				$prefix . 'ba_anim_delay',
				array(
					'label'      => esc_html__( 'Animation Delay (ms)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ms' ),
					'range'      => array(
						'ms' => array(
							'min'  => 0,
							'max'  => 2000,
							'step' => 50,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment, {{WRAPPER}} .es-btn-preset-book-appointment *, {{WRAPPER}} .elonix-button-icon-container i, {{WRAPPER}} .elonix-button-icon-container svg' => 'transition-delay: {{SIZE}}ms;',
					),
				)
			);

			// Transition Timing
			$widget->add_control(
				$prefix . 'ba_anim_timing',
				array(
					'label'     => esc_html__( 'Transition Timing', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'ease-in-out',
					'options'   => array(
						'linear'      => esc_html__( 'Linear', 'elonix' ),
						'ease'        => esc_html__( 'Ease', 'elonix' ),
						'ease-in'     => esc_html__( 'Ease In', 'elonix' ),
						'ease-out'    => esc_html__( 'Ease Out', 'elonix' ),
						'ease-in-out' => esc_html__( 'Ease In Out', 'elonix' ),
						'cubic-bezier(0.68, -0.6, 0.32, 1.6)' => esc_html__( 'Smooth Bounce', 'elonix' ),
					),
					'selectors' => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment, {{WRAPPER}} .es-btn-preset-book-appointment *, {{WRAPPER}} .elonix-button-icon-container i, {{WRAPPER}} .elonix-button-icon-container svg' => 'transition-timing-function: {{VALUE}};',
					),
				)
			);

			// Scale Amount
			$widget->add_control(
				$prefix . 'ba_anim_scale',
				array(
					'label'      => esc_html__( 'Scale Amount', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'ratio' ),
					'range'      => array(
						'ratio' => array(
							'min'  => 0.5,
							'max'  => 2,
							'step' => 0.05,
						),
					),
					'default'    => array(
						'size' => 1.15,
						'unit' => 'ratio',
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => '--es-ba-anim-scale: {{SIZE}};',
					),
				)
			);

			// Rotation Amount
			$widget->add_control(
				$prefix . 'ba_anim_rotation',
				array(
					'label'      => esc_html__( 'Rotation Amount (deg)', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'deg' ),
					'range'      => array(
						'deg' => array(
							'min'  => -360,
							'max'  => 360,
							'step' => 5,
						),
					),
					'default'    => array(
						'size' => 15,
						'unit' => 'deg',
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => '--es-ba-anim-rotation: {{SIZE}}deg;',
					),
				)
			);

			// Hover Distance
			$widget->add_control(
				$prefix . 'ba_anim_distance',
				array(
					'label'      => esc_html__( 'Hover Distance', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => -50,
							'max' => 50,
						),
					),
					'default'    => array(
						'size' => 5,
						'unit' => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} .es-btn-preset-book-appointment' => '--es-ba-anim-distance: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_section();
		}

		/**
		 * Render HTML markup for a button.
		 *
		 * @param \Elementor\Widget_Base $widget The widget instance.
		 * @param array                  $settings Widget settings.
		 * @param string                 $prefix Prefix for control IDs.
		 * @param array                  $extra_classes Additional classes.
		 */
		public static function render_button_html( $widget, $settings, $prefix = '', $extra_classes = array() ) {
			$text   = isset( $settings[ $prefix . 'text' ] ) ? $settings[ $prefix . 'text' ] : '';
			$preset = isset( $settings[ $prefix . 'button_preset' ] ) ? $settings[ $prefix . 'button_preset' ] : 'solid';

			if ( empty( $text ) ) {
				return;
			}

			$link          = isset( $settings[ $prefix . 'link' ] ) ? $settings[ $prefix . 'link' ] : array();
			$download      = isset( $settings[ $prefix . 'download' ] ) ? $settings[ $prefix . 'download' ] : 'no';
			$custom_rel    = isset( $settings[ $prefix . 'custom_rel' ] ) ? $settings[ $prefix . 'custom_rel' ] : '';
			$aria_label    = isset( $settings[ $prefix . 'aria_label' ] ) ? $settings[ $prefix . 'aria_label' ] : '';
			$selected_icon = isset( $settings[ $prefix . 'selected_icon' ] ) ? $settings[ $prefix . 'selected_icon' ] : array();
			$icon_position = isset( $settings[ $prefix . 'icon_position' ] ) ? $settings[ $prefix . 'icon_position' ] : 'before';
			$arrow_type    = isset( $settings[ $prefix . 'arrow_type' ] ) ? $settings[ $prefix . 'arrow_type' ] : 'none';
			$arrow_icon    = isset( $settings[ $prefix . 'arrow_icon' ] ) ? $settings[ $prefix . 'arrow_icon' ] : array();

			// Base and Preset Classes
			$classes   = array( 'elonix-advanced-button' );
			$classes[] = 'es-btn-preset-' . $preset;

			$size      = isset( $settings[ $prefix . 'button_size' ] ) ? $settings[ $prefix . 'button_size' ] : 'medium';
			$classes[] = 'es-btn-size-' . $size;

			$cta_animation   = '';
			$cta_anim_target = 'button';
			if ( 'modern-cta' === $preset ) {
				$cta_animation   = isset( $settings[ $prefix . 'cta_hover_animation' ] ) ? $settings[ $prefix . 'cta_hover_animation' ] : 'none';
				$cta_anim_target = isset( $settings[ $prefix . 'cta_anim_target' ] ) ? $settings[ $prefix . 'cta_anim_target' ] : 'button';
				if ( 'none' !== $cta_animation && ! empty( $cta_animation ) ) {
					if ( 'button' === $cta_anim_target ) {
						$classes[] = 'elementor-animation-' . $cta_animation;
					}
				}
			} elseif ( 'book-appointment' === $preset ) {
				$hover_style = isset( $settings[ $prefix . 'ba_hover_style' ] ) ? $settings[ $prefix . 'ba_hover_style' ] : 'style-1';
				$classes[]   = 'es-ba-hover-' . $hover_style;
			}

			if ( ! empty( $extra_classes ) ) {
				$classes = array_merge( $classes, $extra_classes );
			}

			$widget->add_render_attribute( $prefix . 'button_attr', 'class', implode( ' ', $classes ) );

			// Rel and Download attributes
			if ( ! empty( $custom_rel ) ) {
				$widget->add_render_attribute( $prefix . 'button_attr', 'rel', esc_attr( $custom_rel ) );
			}
			if ( 'yes' === $download ) {
				$widget->add_render_attribute( $prefix . 'button_attr', 'download', '' );
			}

			// Accessibility Aria Label / Screen-Reader
			if ( ! empty( $aria_label ) ) {
				$widget->add_render_attribute( $prefix . 'button_attr', 'aria-label', esc_attr( $aria_label ) );
			} else {
				$widget->add_render_attribute( $prefix . 'button_attr', 'aria-label', esc_attr( $text ) );
			}

			// ARIA Role for non-empty link, otherwise standard button role
			if ( empty( $link['url'] ) || '#' === $link['url'] ) {
				$widget->add_render_attribute( $prefix . 'button_attr', 'role', 'button' );
			}

			// Link Attributes
			if ( ! empty( $link['url'] ) ) {
				$widget->add_link_attributes( $prefix . 'button_attr', $link );
			}

			if ( method_exists( $widget, 'add_inline_editing_attributes' ) ) {
				try {
					$reflection = new \ReflectionMethod( $widget, 'add_inline_editing_attributes' );
					$reflection->setAccessible( true );
					$reflection->invoke( $widget, $prefix . 'text', 'none' );
				} catch ( \Exception $e ) {
					// Fallback safely if invocation fails.
				}
			}

			if ( 'modern-cta' === $preset ) {
				$text_position  = isset( $settings[ $prefix . 'text_position' ] ) ? $settings[ $prefix . 'text_position' ] : 'left';
				$arrow_position = isset( $settings[ $prefix . 'arrow_position' ] ) ? $settings[ $prefix . 'arrow_position' ] : 'right';

				$first_el  = ( 'left' === $text_position ) ? 'text' : 'arrow';
				$second_el = ( 'right' === $arrow_position ) ? 'arrow' : 'text';
				if ( $text_position === $arrow_position ) {
					$first_el  = 'text';
					$second_el = 'arrow';
				}
				?>
				<a <?php $widget->print_render_attribute_string( $prefix . 'button_attr' ); ?>>
					<?php
					$render_el = function ( $type ) use ( $widget, $settings, $prefix, $selected_icon, $icon_position, $text, $arrow_icon, $cta_animation, $cta_anim_target ) {
						$anim_class = '';
						if ( 'none' !== $cta_animation && ! empty( $cta_animation ) ) {
							if ( ( 'text' === $type && ( 'text' === $cta_anim_target || 'both' === $cta_anim_target ) ) ||
								( 'arrow' === $type && ( 'arrow' === $cta_anim_target || 'both' === $cta_anim_target ) ) ) {
								$anim_class = ' elementor-animation-' . $cta_animation;
							}
						}

						if ( 'text' === $type ) {
							?>
							<span class="elonix-cta-text-area<?php echo esc_attr( $anim_class ); ?>">
								<?php if ( ! empty( $selected_icon['value'] ) && 'before' === $icon_position ) : ?>
									<span class="elonix-button-icon elonix-align-icon-before <?php echo esc_attr( $prefix ); ?>elonix-button-icon elonix-button-icon elonix-align-icon-before">
										<?php \Elementor\Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
									</span>
								<?php endif; ?>

								<span class="elonix-button-text">
									<?php echo esc_html( $text ); ?>
								</span>

								<?php if ( ! empty( $selected_icon['value'] ) && 'after' === $icon_position ) : ?>
									<span class="elonix-button-icon elonix-align-icon-after <?php echo esc_attr( $prefix ); ?>elonix-button-icon elonix-button-icon elonix-align-icon-after">
										<?php \Elementor\Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
									</span>
								<?php endif; ?>
							</span>
							<?php
						} else {
							?>
							<span class="elonix-cta-arrow-area<?php echo esc_attr( $anim_class ); ?>">
								<?php if ( ! empty( $arrow_icon['value'] ) ) : ?>
									<?php \Elementor\Icons_Manager::render_icon( $arrow_icon, array( 'aria-hidden' => 'true' ) ); ?>
								<?php else : ?>
									<i class="fas fa-angles-right" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
							<?php
						}
					};

					$render_el( $first_el );
					$render_el( $second_el );
				?>
				</a>
				<?php
			} elseif ( 'book-appointment' === $preset ) {
				$ba_icon     = isset( $settings[ $prefix . 'ba_icon' ] ) ? $settings[ $prefix . 'ba_icon' ] : array();
				$ba_icon_pos = isset( $settings[ $prefix . 'ba_icon_position' ] ) ? $settings[ $prefix . 'ba_icon_position' ] : 'right';
				?>
				<a <?php $widget->print_render_attribute_string( $prefix . 'button_attr' ); ?>>
					<span class="elonix-button-content-wrapper es-ba-pos-<?php echo esc_attr( $ba_icon_pos ); ?>">
						<?php if ( 'left' === $ba_icon_pos ) : ?>
							<span class="elonix-button-icon-container">
								<?php if ( ! empty( $ba_icon['value'] ) ) : ?>
									<?php \Elementor\Icons_Manager::render_icon( $ba_icon, array( 'aria-hidden' => 'true' ) ); ?>
								<?php else : ?>
									<i class="fas fa-angles-right" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
						<?php endif; ?>

						<span class="elonix-button-text">
							<?php echo esc_html( $text ); ?>
						</span>

						<?php if ( 'right' === $ba_icon_pos ) : ?>
							<span class="elonix-button-icon-container">
								<?php if ( ! empty( $ba_icon['value'] ) ) : ?>
									<?php \Elementor\Icons_Manager::render_icon( $ba_icon, array( 'aria-hidden' => 'true' ) ); ?>
								<?php else : ?>
									<i class="fas fa-angles-right" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</span>
				</a>
				<?php
			} else {
				?>
				<a <?php $widget->print_render_attribute_string( $prefix . 'button_attr' ); ?>>
					<span class="elonix-button-content-wrapper">
						
						<?php
						// Render Icon Before
						if ( ! empty( $selected_icon['value'] ) && 'before' === $icon_position ) :
							?>
							<span class="elonix-button-icon elonix-align-icon-before <?php echo esc_attr( $prefix ); ?>elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>align-icon-before">
								<?php \Elementor\Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
							</span>
						<?php endif; ?>

						<span class="elonix-button-text">
							<?php echo esc_html( $text ); ?>
						</span>

						<?php
						// Render Icon After
						if ( ! empty( $selected_icon['value'] ) && 'after' === $icon_position ) :
							?>
							<span class="elonix-button-icon elonix-align-icon-after <?php echo esc_attr( $prefix ); ?>elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>align-icon-after">
								<?php \Elementor\Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
							</span>
						<?php endif; ?>

						<?php
						// Render Arrow System Element
						if ( 'none' !== $arrow_type && ! empty( $arrow_icon['value'] ) ) :
							?>
							<span class="elonix-button-arrow es-arrow-type-<?php echo esc_attr( $arrow_type ); ?> <?php echo esc_attr( $prefix ); ?>elonix-button-arrow">
								<?php \Elementor\Icons_Manager::render_icon( $arrow_icon, array( 'aria-hidden' => 'true' ) ); ?>
							</span>
						<?php endif; ?>

					</span>
				</a>
				<?php
			}
		}
	}
}

// =========================================================================
// BACKWARD COMPATIBILITY COMPONENT WRAPPERS
// =========================================================================

namespace Elonix_Toolkit_Compat {

	use Elementor\Controls_Manager;
	use Elementor\Icons_Manager;
	use Elonix_Button_Helper;

	if ( ! function_exists( __NAMESPACE__ . '\es_button_controls' ) ) {
		/**
		 * Wrapper function to register controls dynamically in components.
		 */
		function es_button_controls( $widget, $prefix = 'button', $section_label = 'Button Settings', $condition = array() ) {
			$section_args = array(
				'label' => $section_label,
				'tab'   => Controls_Manager::TAB_CONTENT,
			);

			if ( ! empty( $condition ) ) {
				$section_args['condition'] = $condition;
			}

			$widget->start_controls_section(
				$prefix . '_section',
				$section_args
			);

			$widget->add_control(
				$prefix . '_enable',
				array(
					'label'        => esc_html__( 'Enable Button', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			Elonix_Button_Helper::register_button_content_controls( $widget, $prefix . '_', array() );

			$widget->add_responsive_control(
				$prefix . '_align',
				array(
					'label'     => esc_html__( 'Alignment', 'elonix' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'left'    => array(
							'title' => esc_html__( 'Left', 'elonix' ),
							'icon'  => 'eicon-text-align-left',
						),
						'center'  => array(
							'title' => esc_html__( 'Center', 'elonix' ),
							'icon'  => 'eicon-text-align-center',
						),
						'right'   => array(
							'title' => esc_html__( 'Right', 'elonix' ),
							'icon'  => 'eicon-text-align-right',
						),
						'justify' => array(
							'title' => esc_html__( 'Justify', 'elonix' ),
							'icon'  => 'eicon-text-align-justify',
						),
					),
					'default'   => 'left',
					'selectors' => array(
						'{{WRAPPER}} .theme-btn-wrapper' => 'text-align: {{VALUE}};',
					),
					'condition' => array(
						$prefix . '_enable' => 'yes',
					),
				)
			);

			$widget->end_controls_section();

			Elonix_Button_Helper::register_button_style_controls(
				$widget,
				$prefix . '_',
				array(
					'button' => '{{WRAPPER}} .' . $prefix . '_elonix-button',
					'icon'   => '{{WRAPPER}} .' . $prefix . '_elonix-button-icon',
					'arrow'  => '{{WRAPPER}} .' . $prefix . '_elonix-button-arrow',
				),
				array_merge( $condition, array( $prefix . '_enable' => 'yes' ) )
			);
		}
	}

	if ( ! function_exists( __NAMESPACE__ . '\es_render_button' ) ) {
		/**
		 * Wrapper function to render component buttons dynamically.
		 */
		function es_render_button( $settings, $prefix = 'button' ) {
			$button_enable = isset( $settings[ $prefix . '_enable' ] ) ? $settings[ $prefix . '_enable' ] : 'yes';

			if ( 'yes' !== $button_enable ) {
				return;
			}

			$text   = isset( $settings[ $prefix . '_text' ] ) ? $settings[ $prefix . '_text' ] : '';
			$preset = isset( $settings[ $prefix . '_button_preset' ] ) ? $settings[ $prefix . '_button_preset' ] : 'solid';

			if ( empty( $text ) ) {
				return;
			}

			$link          = isset( $settings[ $prefix . '_link' ] ) ? $settings[ $prefix . '_link' ] : array();
			$download      = isset( $settings[ $prefix . '_download' ] ) ? $settings[ $prefix . '_download' ] : 'no';
			$custom_rel    = isset( $settings[ $prefix . '_custom_rel' ] ) ? $settings[ $prefix . '_custom_rel' ] : '';
			$aria_label    = isset( $settings[ $prefix . '_aria_label' ] ) ? $settings[ $prefix . '_aria_label' ] : '';
			$selected_icon = isset( $settings[ $prefix . '_selected_icon' ] ) ? $settings[ $prefix . '_selected_icon' ] : array();
			$icon_position = isset( $settings[ $prefix . '_icon_position' ] ) ? $settings[ $prefix . '_icon_position' ] : 'before';
			$arrow_type    = isset( $settings[ $prefix . '_arrow_type' ] ) ? $settings[ $prefix . '_arrow_type' ] : 'none';
			$arrow_icon    = isset( $settings[ $prefix . '_arrow_icon' ] ) ? $settings[ $prefix . '_arrow_icon' ] : array();

			// Classes
			$classes   = array( 'elonix-advanced-button', $prefix . '_elonix-button' );
			$classes[] = 'es-btn-preset-' . $preset;

			$size      = isset( $settings[ $prefix . '_button_size' ] ) ? $settings[ $prefix . '_button_size' ] : 'medium';
			$classes[] = 'es-btn-size-' . $size;

			$cta_animation   = '';
			$cta_anim_target = 'button';
			if ( 'modern-cta' === $preset ) {
				$cta_animation   = isset( $settings[ $prefix . '_cta_hover_animation' ] ) ? $settings[ $prefix . '_cta_hover_animation' ] : 'none';
				$cta_anim_target = isset( $settings[ $prefix . '_cta_anim_target' ] ) ? $settings[ $prefix . '_cta_anim_target' ] : 'button';
				if ( 'none' !== $cta_animation && ! empty( $cta_animation ) ) {
					if ( 'button' === $cta_anim_target ) {
						$classes[] = 'elementor-animation-' . $cta_animation;
					}
				}
			} elseif ( 'book-appointment' === $preset ) {
				$hover_style = isset( $settings[ $prefix . '_ba_hover_style' ] ) ? $settings[ $prefix . '_ba_hover_style' ] : 'style-1';
				$classes[]   = 'es-ba-hover-' . $hover_style;
			}

			// Link Attributes
			$target = '';
			$rel    = '';
			if ( ! empty( $link['is_external'] ) ) {
				$target = ' target="_blank"';
			}
			if ( ! empty( $link['nofollow'] ) ) {
				$rel = 'nofollow';
			}
			if ( ! empty( $custom_rel ) ) {
				$rel = $rel ? $rel . ' ' . $custom_rel : $custom_rel;
			}

			$rel_attr      = $rel ? ' rel="' . esc_attr( $rel ) . '"' : '';
			$download_attr = ( 'yes' === $download ) ? ' download' : '';
			$aria_attr     = ! empty( $aria_label ) ? ' aria-label="' . esc_attr( $aria_label ) . '"' : ' aria-label="' . esc_attr( $text ) . '"';
			$url           = ! empty( $link['url'] ) ? esc_url( $link['url'] ) : '#';

			$role_attr = '';
			if ( empty( $link['url'] ) || '#' === $link['url'] ) {
				$role_attr = ' role="button"';
			}
			?>
			<div class="theme-btn-wrapper">
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $url is esc_url()'d above; $target/$rel_attr/$download_attr/$aria_attr/$role_attr are esc_attr()'d or fixed static strings, built above. ?>
				<a href="<?php echo $url; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $target . $rel_attr . $download_attr . $aria_attr . $role_attr; ?>>
					<?php
					if ( 'modern-cta' === $preset ) :
						$text_position  = isset( $settings[ $prefix . '_text_position' ] ) ? $settings[ $prefix . '_text_position' ] : 'left';
						$arrow_position = isset( $settings[ $prefix . '_arrow_position' ] ) ? $settings[ $prefix . '_arrow_position' ] : 'right';

						$first_el  = ( 'left' === $text_position ) ? 'text' : 'arrow';
						$second_el = ( 'right' === $arrow_position ) ? 'arrow' : 'text';
						if ( $text_position === $arrow_position ) {
							$first_el  = 'text';
							$second_el = 'arrow';
						}

						$render_el = function ( $type ) use ( $settings, $prefix, $selected_icon, $icon_position, $text, $arrow_icon, $cta_animation, $cta_anim_target ) {
							$anim_class = '';
							if ( 'none' !== $cta_animation && ! empty( $cta_animation ) ) {
								if ( ( 'text' === $type && ( 'text' === $cta_anim_target || 'both' === $cta_anim_target ) ) ||
									( 'arrow' === $type && ( 'arrow' === $cta_anim_target || 'both' === $cta_anim_target ) ) ) {
									$anim_class = ' elementor-animation-' . $cta_animation;
								}
							}

							if ( 'text' === $type ) {
								?>
								<span class="elonix-cta-text-area<?php echo esc_attr( $anim_class ); ?>">
									<?php if ( ! empty( $selected_icon['value'] ) && 'before' === $icon_position ) : ?>
										<span class="elonix-button-icon elonix-align-icon-before <?php echo esc_attr( $prefix ); ?>_elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>_align-icon-before">
											<?php Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
										</span>
									<?php endif; ?>

									<span class="elonix-button-text">
										<?php echo esc_html( $text ); ?>
									</span>

									<?php if ( ! empty( $selected_icon['value'] ) && 'after' === $icon_position ) : ?>
										<span class="elonix-button-icon elonix-align-icon-after <?php echo esc_attr( $prefix ); ?>_elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>_align-icon-after">
											<?php Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
										</span>
									<?php endif; ?>
								</span>
								<?php
							} else {
								?>
								<span class="elonix-cta-arrow-area<?php echo esc_attr( $anim_class ); ?>">
									<?php if ( ! empty( $arrow_icon['value'] ) ) : ?>
										<?php Icons_Manager::render_icon( $arrow_icon, array( 'aria-hidden' => 'true' ) ); ?>
									<?php else : ?>
										<i class="fas fa-angles-right" aria-hidden="true"></i>
									<?php endif; ?>
								</span>
								<?php
							}
						};

						$render_el( $first_el );
						$render_el( $second_el );
					elseif ( 'book-appointment' === $preset ) :
						$ba_icon     = isset( $settings[ $prefix . '_ba_icon' ] ) ? $settings[ $prefix . '_ba_icon' ] : array();
						$ba_icon_pos = isset( $settings[ $prefix . '_ba_icon_position' ] ) ? $settings[ $prefix . '_ba_icon_position' ] : 'right';
						?>
						<span class="elonix-button-content-wrapper es-ba-pos-<?php echo esc_attr( $ba_icon_pos ); ?>">
							<?php if ( 'left' === $ba_icon_pos ) : ?>
								<span class="elonix-button-icon-container">
									<?php if ( ! empty( $ba_icon['value'] ) ) : ?>
										<?php Icons_Manager::render_icon( $ba_icon, array( 'aria-hidden' => 'true' ) ); ?>
									<?php else : ?>
										<i class="fas fa-angles-right" aria-hidden="true"></i>
									<?php endif; ?>
								</span>
							<?php endif; ?>

							<span class="elonix-button-text">
								<?php echo esc_html( $text ); ?>
							</span>

							<?php if ( 'right' === $ba_icon_pos ) : ?>
								<span class="elonix-button-icon-container">
									<?php if ( ! empty( $ba_icon['value'] ) ) : ?>
										<?php Icons_Manager::render_icon( $ba_icon, array( 'aria-hidden' => 'true' ) ); ?>
									<?php else : ?>
										<i class="fas fa-angles-right" aria-hidden="true"></i>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<span class="elonix-button-content-wrapper">
							
							<?php
							// Render Icon Before
							if ( ! empty( $selected_icon['value'] ) && 'before' === $icon_position ) :
								?>
								<span class="elonix-button-icon elonix-align-icon-before <?php echo esc_attr( $prefix ); ?>_elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>_align-icon-before">
									<?php Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
								</span>
							<?php endif; ?>

							<span class="elonix-button-text">
								<?php echo esc_html( $text ); ?>
							</span>

							<?php
							// Render Icon After
							if ( ! empty( $selected_icon['value'] ) && 'after' === $icon_position ) :
								?>
								<span class="elonix-button-icon elonix-align-icon-after <?php echo esc_attr( $prefix ); ?>_elonix-button-icon elonix-button-icon <?php echo esc_attr( $prefix ); ?>_align-icon-after">
									<?php Icons_Manager::render_icon( $selected_icon, array( 'aria-hidden' => 'true' ) ); ?>
								</span>
							<?php endif; ?>

							<?php
							// Render Arrow
							if ( 'none' !== $arrow_type && ! empty( $arrow_icon['value'] ) ) :
								?>
								<span class="elonix-button-arrow es-arrow-type-<?php echo esc_attr( $arrow_type ); ?> <?php echo esc_attr( $prefix ); ?>_elonix-button-arrow elonix-button-arrow">
									<?php Icons_Manager::render_icon( $arrow_icon, array( 'aria-hidden' => 'true' ) ); ?>
								</span>
							<?php endif; ?>

						</span>
					<?php endif; ?>
				</a>
			</div>
			<?php
		}
	}
}
