<?php
/**
 * Elonix – Toolkit for Elementor Accordion Helper Class
 *
 * Provides reusable accordion registration, rendering, and schema logic.
 *
 * @package Elonix_Toolkit
 */

namespace {

	use Elementor\Controls_Manager;
	use Elementor\Group_Control_Typography;
	use Elementor\Group_Control_Border;
	use Elementor\Group_Control_Box_Shadow;
	use Elementor\Group_Control_Background;
	use Elementor\Repeater;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class Elonix_Accordion_Helper {

		/**
		 * Register content controls.
		 */
		public static function register_accordion_content_controls( $widget, $prefix = '', $defaults = array() ) {

			// SECTION 1: Accordion Items (Repeater First)
			$widget->start_controls_section(
				$prefix . 'section_accordion_items',
				array(
					'label' => esc_html__( 'Accordion Items', 'elonix' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$widget->add_control(
				$prefix . 'accordion_preset',
				array(
					'label'   => esc_html__( 'Accordion Style', 'elonix' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'classic',
					'options' => array(
						'classic'   => esc_html__( 'Classic Accordion', 'elonix' ),
						'bordered'  => esc_html__( 'Bordered Accordion', 'elonix' ),
						'card'      => esc_html__( 'Modern Card Accordion', 'elonix' ),
						'icon-left' => esc_html__( 'Icon Left Accordion', 'elonix' ),
						'faq'       => esc_html__( 'FAQ Accordion', 'elonix' ),
					),
				)
			);

			$repeater = new Repeater();

			$repeater->add_control(
				'item_active',
				array(
					'label'        => esc_html__( 'Active By Default', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);
			$repeater->add_control(
				'item_title',
				array(
					'label'       => esc_html__( 'Item Title', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => esc_html__( 'Accordion Item Title', 'elonix' ),
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
				)
			);

			$repeater->add_control(
				'item_content',
				array(
					'label'       => esc_html__( 'Item Content', 'elonix' ),
					'type'        => Controls_Manager::WYSIWYG,
					'default'     => esc_html__( 'Add your content here. This content is fully customizable and supports rich text formatting.', 'elonix' ),
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
				)
			);

			$repeater->add_control(
				'item_icon',
				array(
					'label'   => esc_html__( 'Normal Icon', 'elonix' ),
					'type'    => Controls_Manager::ICONS,
					'default' => array(
						'value'   => 'fas fa-plus',
						'library' => 'fa-solid',
					),
				)
			);

			$repeater->add_control(
				'item_active_icon',
				array(
					'label'   => esc_html__( 'Active Icon', 'elonix' ),
					'type'    => Controls_Manager::ICONS,
					'default' => array(
						'value'   => 'fas fa-minus',
						'library' => 'fa-solid',
					),
				)
			);

			$repeater->add_control(
				'item_image',
				array(
					'label' => esc_html__( 'Item Image (Optional)', 'elonix' ),
					'type'  => Controls_Manager::MEDIA,
				)
			);

			$repeater->add_control(
				'item_badge',
				array(
					'label'       => esc_html__( 'Item Badge (Optional)', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'e.g. NEW, HOT', 'elonix' ),
				)
			);

			$widget->add_control(
				$prefix . 'accordion_items',
				array(
					'label'       => esc_html__( 'Accordion Items', 'elonix' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'default'     => array(
						array(
							'item_title'   => esc_html__( 'What is Elonix – Toolkit for Elementor?', 'elonix' ),
							'item_content' => esc_html__( 'Elonix – Toolkit for Elementor is a premium, lightweight Elementor extension designed to provide highly optimized, accessible, and fast loading widgets for WordPress sites.', 'elonix' ),
						),
						array(
							'item_title'   => esc_html__( 'Is this widget accessible?', 'elonix' ),
							'item_content' => esc_html__( 'Yes, our Accordion Widget is built strictly following WAI-ARIA guidelines, supporting screen readers with dynamic attributes and full keyboard navigation (arrows, home, end, enter/space).', 'elonix' ),
						),
						array(
							'item_title'   => esc_html__( 'Can I add images to accordion items?', 'elonix' ),
							'item_content' => esc_html__( 'Yes! You can optionally select an image for each item which renders cleanly inside the body content area alongside your formatted description.', 'elonix' ),
						),
					),
					'title_field' => '{{{ item_title }}} {{{ (item_active === "yes") ? "<span style=\"color: #10b981; font-weight: bold; margin-left: 5px;\">[Active]</span>" : "" }}}',
				)
			);

			$widget->end_controls_section();

			// SECTION 2: General / Layout Settings
			$widget->start_controls_section(
				$prefix . 'section_accordion_general',
				array(
					'label' => esc_html__( 'General Settings', 'elonix' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$widget->add_control(
				$prefix . 'allow_multiple',
				array(
					'label'        => esc_html__( 'Allow Multiple Open', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$widget->add_control(
				$prefix . 'open_first',
				array(
					'label'        => esc_html__( 'Open First Item', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$widget->add_control(
				$prefix . 'collapse_others',
				array(
					'label'        => esc_html__( 'Collapse Others on Open', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						$prefix . 'allow_multiple!' => 'yes',
					),
				)
			);

			$widget->add_control(
				$prefix . 'toggle_animation',
				array(
					'label'        => esc_html__( 'Toggle Slide Animation', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$widget->add_control(
				$prefix . 'animation_duration',
				array(
					'label'     => esc_html__( 'Animation Duration (ms)', 'elonix' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 100,
							'max'  => 1000,
							'step' => 50,
						),
					),
					'default'   => array(
						'size' => 300,
					),
					'condition' => array(
						$prefix . 'toggle_animation' => 'yes',
					),
				)
			);

			$widget->end_controls_section();

			// SECTION 3: Numbering
			$widget->start_controls_section(
				$prefix . 'section_accordion_numbering',
				array(
					'label' => esc_html__( 'Numbering', 'elonix' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$widget->add_control(
				$prefix . 'enable_numbering',
				array(
					'label'        => esc_html__( 'Enable Item Numbering', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$widget->add_control(
				$prefix . 'number_format',
				array(
					'label'     => esc_html__( 'Number Format', 'elonix' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => '01',
					'options'   => array(
						'1'   => '1, 2, 3',
						'01'  => '01, 02, 03',
						'001' => '001, 002, 003',
						'I'   => 'I, II, III',
						'i'   => 'i, ii, iii',
						'A'   => 'A, B, C',
						'a'   => 'a, b, c',
					),
					'condition' => array(
						$prefix . 'enable_numbering' => 'yes',
					),
				)
			);

			$widget->add_control(
				$prefix . 'number_prefix',
				array(
					'label'       => esc_html__( 'Number Prefix', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'e.g. Step ', 'elonix' ),
					'condition'   => array(
						$prefix . 'enable_numbering' => 'yes',
					),
				)
			);

			$widget->add_control(
				$prefix . 'number_suffix',
				array(
					'label'       => esc_html__( 'Number Suffix', 'elonix' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'e.g. .', 'elonix' ),
					'condition'   => array(
						$prefix . 'enable_numbering' => 'yes',
					),
				)
			);

			$widget->end_controls_section();

			// SECTION 4: Icons
			$widget->start_controls_section(
				$prefix . 'section_accordion_icons',
				array(
					'label' => esc_html__( 'Icons', 'elonix' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$widget->add_control(
				$prefix . 'icon_position',
				array(
					'label'   => esc_html__( 'Icon Position', 'elonix' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'right',
					'options' => array(
						'left'  => esc_html__( 'Left', 'elonix' ),
						'right' => esc_html__( 'Right', 'elonix' ),
						'both'  => esc_html__( 'Both Sides', 'elonix' ),
					),
				)
			);

			$widget->add_control(
				$prefix . 'icon_rotation',
				array(
					'label'        => esc_html__( 'Rotate Icon on Expand', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$widget->end_controls_section();

			// SECTION 5: Advanced
			$widget->start_controls_section(
				$prefix . 'section_accordion_advanced',
				array(
					'label' => esc_html__( 'Advanced', 'elonix' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$widget->add_control(
				$prefix . 'enable_faq_schema',
				array(
					'label'        => esc_html__( 'Enable FAQ Schema (JSON-LD)', 'elonix' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elonix' ),
					'label_off'    => esc_html__( 'No', 'elonix' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$widget->end_controls_section();

		}

		/**
		 * Register styling controls.
		 */
		public static function register_accordion_style_controls( $widget, $prefix = '', $selectors = array(), $condition = array() ) {

			$container_sel    = isset( $selectors['container'] ) ? $selectors['container'] : '{{WRAPPER}} .es-accordion-container';
			$item_sel         = isset( $selectors['item'] ) ? $selectors['item'] : '{{WRAPPER}} .es-accordion-item';
			$header_sel       = isset( $selectors['header'] ) ? $selectors['header'] : '{{WRAPPER}} .es-accordion-header';
			$trigger_sel      = isset( $selectors['trigger'] ) ? $selectors['trigger'] : '{{WRAPPER}} .es-accordion-header-trigger';
			$title_sel        = isset( $selectors['title'] ) ? $selectors['title'] : '{{WRAPPER}} .es-accordion-title';
			$badge_sel        = isset( $selectors['badge'] ) ? $selectors['badge'] : '{{WRAPPER}} .es-accordion-badge';
			$icon_wrapper_sel = isset( $selectors['icon_wrapper'] ) ? $selectors['icon_wrapper'] : '{{WRAPPER}} .es-accordion-icon-wrapper';
			$content_sel      = isset( $selectors['content'] ) ? $selectors['content'] : '{{WRAPPER}} .es-accordion-content';
			$number_sel       = isset( $selectors['number'] ) ? $selectors['number'] : '{{WRAPPER}} .es-accordion-number';

			// ==========================================
			// 0. ACCORDION (Container)
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_container_style',
				array(
					'label'     => esc_html__( 'Accordion', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_width',
				array(
					'label'      => esc_html__( 'Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw', 'rem' ),
					'range'      => array(
						'px' => array( 'min' => 100, 'max' => 1200 ),
						'%'  => array( 'min' => 10, 'max' => 100 ),
					),
					'selectors'  => array(
						$container_sel => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_max_width',
				array(
					'label'      => esc_html__( 'Max Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw', 'rem' ),
					'selectors'  => array(
						$container_sel => 'max-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_content_width',
				array(
					'label'      => esc_html__( 'Content Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'vw', 'rem' ),
					'selectors'  => array(
						$content_sel => 'max-width: {{SIZE}}{{UNIT}}; width: 100%;',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_align',
				array(
					'label'     => esc_html__( 'Alignment', 'elonix' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'left'   => array( 'title' => esc_html__( 'Left', 'elonix' ), 'icon' => 'eicon-text-align-left' ),
						'center' => array( 'title' => esc_html__( 'Center', 'elonix' ), 'icon' => 'eicon-text-align-center' ),
						'right'  => array( 'title' => esc_html__( 'Right', 'elonix' ), 'icon' => 'eicon-text-align-right' ),
					),
					'prefix_class' => 'es-accordion-align%s-',
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'container_bg_group',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $container_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'container_border',
					'selector' => $container_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$container_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'container_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$container_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'container_shadow',
					'selector' => $container_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'item_gap',
				array(
					'label'      => esc_html__( 'Item Gap', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'selectors'  => array(
						$item_sel . ':not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_section();

			// ==========================================
			// 0.5. ACCORDION ITEM
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_item_style',
				array(
					'label'     => esc_html__( 'Accordion Item', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->start_controls_tabs( $prefix . 'item_tabs' );

			// Normal Tab
			$widget->start_controls_tab( $prefix . 'item_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'item_bg_group',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $item_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'item_border',
					'selector' => $item_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'item_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$item_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'item_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$item_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . 'item_shadow',
					'selector' => $item_sel,
				)
			);

			$widget->end_controls_tab();

			// Hover Tab
			$widget->start_controls_tab( $prefix . 'item_tab_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'item_bg_hover',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $item_sel . ':hover',
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'item_border_hover',
					'selector' => $item_sel . ':hover',
				)
			);

			$widget->end_controls_tab();

			// Active Tab
			$widget->start_controls_tab( $prefix . 'item_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'item_border_active_main',
					'selector' => $item_sel . '.es-active',
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->end_controls_section();

			// ==========================================
			// 1. HEADER
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_header_style',
				array(
					'label'     => esc_html__( 'Header', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->start_controls_tabs( $prefix . 'header_tabs' );
			$widget->start_controls_tab( $prefix . 'header_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'header_bg_group',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $trigger_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'header_border',
					'selector' => $trigger_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$trigger_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$trigger_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'header_tab_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'header_bg_hover',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $trigger_sel . ':hover',
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'header_border_hover',
					'selector' => $trigger_sel . ':hover',
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_padding_hover',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$trigger_sel . ':hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_border_radius_hover',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$trigger_sel . ':hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'header_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'item_background_active', // Backward compatibility with previous item background active
					'types'    => array( 'classic', 'gradient' ),
					'selector' => '{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger',
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'item_border_active',
					'selector' => '{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger',
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_padding_active',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'header_border_radius_active',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->end_controls_tabs();
			$widget->end_controls_section();

			// ==========================================
			// 2. CONTENT
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_content_style',
				array(
					'label'     => esc_html__( 'Content', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'content_typography',
					'selector' => $content_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'content_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$content_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'content_margin',
				array(
					'label'      => esc_html__( 'Margin', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$content_sel => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'content_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$content_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'content_tabs' );
			$widget->start_controls_tab( $prefix . 'content_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'content_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$content_sel => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'content_bg_group',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $content_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'content_border',
					'selector' => $content_sel,
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'content_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'content_color_active',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$item_sel . '.es-active ' . $content_sel => 'color: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'content_bg_active',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $item_sel . '.es-active ' . $content_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'content_border_active',
					'selector' => $item_sel . '.es-active ' . $content_sel,
				)
			);

			$widget->end_controls_tab();
			$widget->end_controls_tabs();
			$widget->end_controls_section();


			// ==========================================
			// 3. TITLE
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_title_style',
				array(
					'label'     => esc_html__( 'Title', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'title_typography',
					'selector' => $title_sel,
				)
			);

			$widget->start_controls_tabs( $prefix . 'title_tabs' );
			$widget->start_controls_tab( $prefix . 'title_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'title_color',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$title_sel => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'title_tab_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'title_color_hover',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-item:hover .es-accordion-title' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'title_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'title_color_active',
				array(
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-title' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->end_controls_tabs();
			$widget->end_controls_section();


			// ==========================================
			// 4. ICON
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_icons_style',
				array(
					'label'     => esc_html__( 'Icon', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => $condition,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_size',
				array(
					'label'      => esc_html__( 'Size', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem' ),
					'selectors'  => array(
						$icon_wrapper_sel . ' i'   => 'font-size: {{SIZE}}{{UNIT}};',
						$icon_wrapper_sel . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_width',
				array(
					'label'      => esc_html__( 'Width', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem', '%' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 200,
						),
					),
					'selectors'  => array(
						$icon_wrapper_sel => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_height',
				array(
					'label'      => esc_html__( 'Height', 'elonix' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em', 'rem', '%' ),
					'range'      => array(
						'px' => array(
							'min' => 10,
							'max' => 200,
						),
					),
					'selectors'  => array(
						$icon_wrapper_sel => 'height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'icon_tabs' );
			$widget->start_controls_tab( $prefix . 'icon_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'icon_color',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$icon_wrapper_sel . ' i'   => 'color: {{VALUE}};',
						$icon_wrapper_sel . ' svg' => 'fill: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'icon_bg_group',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $icon_wrapper_sel,
				)
			);

			$widget->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $prefix . 'icon_border',
					'selector' => $icon_wrapper_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$icon_wrapper_sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->add_responsive_control(
				$prefix . 'icon_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'selectors'  => array(
						$icon_wrapper_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'icon_tab_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'icon_color_hover',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-header-trigger:hover .es-accordion-icon-wrapper i'   => 'color: {{VALUE}};',
						'{{WRAPPER}} .es-accordion-header-trigger:hover .es-accordion-icon-wrapper svg' => 'fill: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'icon_bg_hover',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => '{{WRAPPER}} .es-accordion-header-trigger:hover .es-accordion-icon-wrapper',
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'icon_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'icon_color_active',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger .es-accordion-icon-wrapper i'   => 'color: {{VALUE}};',
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger .es-accordion-icon-wrapper svg' => 'fill: {{VALUE}};',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => $prefix . 'icon_bg_active',
					'types'    => array( 'classic', 'gradient' ),
					'selector' => '{{WRAPPER}} .es-accordion-item.es-active .es-accordion-header-trigger .es-accordion-icon-wrapper',
				)
			);

			$widget->end_controls_tab();
			$widget->end_controls_tabs();
			$widget->end_controls_section();


			// ==========================================
			// 5. NUMBER
			// ==========================================
			$widget->start_controls_section(
				$prefix . 'accordion_number_style_section',
				array(
					'label'     => esc_html__( 'Number', 'elonix' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array(
						$prefix . 'enable_numbering' => 'yes',
					),
				)
			);

			$widget->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $prefix . 'number_typography',
					'selector' => $number_sel,
				)
			);

			$widget->add_responsive_control(
				$prefix . 'number_padding',
				array(
					'label'      => esc_html__( 'Padding', 'elonix' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						$number_sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$widget->start_controls_tabs( $prefix . 'number_tabs' );
			$widget->start_controls_tab( $prefix . 'number_tab_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'number_color',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$number_sel => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'number_tab_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'number_color_hover',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-item:hover .es-accordion-number' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->start_controls_tab( $prefix . 'number_tab_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );

			$widget->add_control(
				$prefix . 'number_color_active',
				array(
					'label'     => esc_html__( 'Color', 'elonix' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .es-accordion-item.es-active .es-accordion-number' => 'color: {{VALUE}};',
					),
				)
			);

			$widget->end_controls_tab();
			$widget->end_controls_tabs();
			$widget->end_controls_section();

		}

		/**
		 * Render FAQ Schema page metadata.
		 */
		public static function render_faq_schema( $items ) {
			if ( empty( $items ) || ! is_array( $items ) ) {
				return;
			}

			$json = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => array(),
			);

			foreach ( $items as $item ) {
				$title   = isset( $item['item_title'] ) ? $item['item_title'] : '';
				$content = isset( $item['item_content'] ) ? $item['item_content'] : '';

				if ( empty( $title ) || empty( $content ) ) {
					continue;
				}

				$json['mainEntity'][] = array(
					'@type'          => 'Question',
					'name'           => esc_html( $title ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_kses_post( $content ),
					),
				);
			}

			if ( ! empty( $json['mainEntity'] ) ) {
				?>
				<script type="application/ld+json"><?php echo wp_json_encode( $json ); ?></script>
				<?php
			}
		}

		public static function format_number( $index, $format ) {
			$num = (int) $index + 1;
			if ( $format === '01' ) {
				return sprintf( '%02d', $num );
			} elseif ( $format === '001' ) {
				return sprintf( '%03d', $num );
			} elseif ( $format === 'A' ) {
				return chr( 64 + $num );
			} elseif ( $format === 'a' ) {
				return chr( 96 + $num );
			} elseif ( $format === 'I' ) {
				return self::to_roman( $num );
			} elseif ( $format === 'i' ) {
				return strtolower( self::to_roman( $num ) );
			}
			return (string) $num;
		}

		private static function to_roman( $number ) {
			$map = array( 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 );
			$returnValue = '';
			while ( $number > 0 ) {
				foreach ( $map as $roman => $int ) {
					if ( $number >= $int ) {
						$number -= $int;
						$returnValue .= $roman;
						break;
					}
				}
			}
			return $returnValue;
		}

		/**
		 * Render HTML markup for the Accordion.
		 */
		public static function render_accordion_html( $widget, $settings, $prefix = '' ) {
			$preset               = isset( $settings[ $prefix . 'accordion_preset' ] ) ? $settings[ $prefix . 'accordion_preset' ] : 'classic';
			$items                = isset( $settings[ $prefix . 'accordion_items' ] ) ? $settings[ $prefix . 'accordion_items' ] : array();
			$allow_multiple       = isset( $settings[ $prefix . 'allow_multiple' ] ) ? $settings[ $prefix . 'allow_multiple' ] : 'no';
			$open_first           = isset( $settings[ $prefix . 'open_first' ] ) ? $settings[ $prefix . 'open_first' ] : 'yes';
			$collapse_others      = isset( $settings[ $prefix . 'collapse_others' ] ) ? $settings[ $prefix . 'collapse_others' ] : 'yes';
			$enable_numbering     = isset( $settings[ $prefix . 'enable_numbering' ] ) ? $settings[ $prefix . 'enable_numbering' ] : 'no';
			$number_format        = isset( $settings[ $prefix . 'number_format' ] ) ? $settings[ $prefix . 'number_format' ] : '01';
			$number_prefix        = isset( $settings[ $prefix . 'number_prefix' ] ) ? $settings[ $prefix . 'number_prefix' ] : '';
			$number_suffix        = isset( $settings[ $prefix . 'number_suffix' ] ) ? $settings[ $prefix . 'number_suffix' ] : '';
			$toggle_animation     = isset( $settings[ $prefix . 'toggle_animation' ] ) ? $settings[ $prefix . 'toggle_animation' ] : 'yes';
			$anim_duration        = isset( $settings[ $prefix . 'animation_duration' ]['size'] ) ? $settings[ $prefix . 'animation_duration' ]['size'] : 300;
			$icon_rotation        = isset( $settings[ $prefix . 'icon_rotation' ] ) ? $settings[ $prefix . 'icon_rotation' ] : 'yes';
			$enable_schema        = isset( $settings[ $prefix . 'enable_faq_schema' ] ) ? $settings[ $prefix . 'enable_faq_schema' ] : 'no';
			$icon_pos             = isset( $settings[ $prefix . 'icon_position' ] ) ? $settings[ $prefix . 'icon_position' ] : ( 'icon-left' === $preset ? 'left' : 'right' );
			$enable_glassmorphism = isset( $settings[ $prefix . 'enable_glassmorphism' ] ) ? $settings[ $prefix . 'enable_glassmorphism' ] : 'no';
			$enable_border_glow   = isset( $settings[ $prefix . 'enable_border_glow' ] ) ? $settings[ $prefix . 'enable_border_glow' ] : 'no';
			$enable_modern_card   = isset( $settings[ $prefix . 'enable_modern_card' ] ) ? $settings[ $prefix . 'enable_modern_card' ] : 'no';
			$enable_soft_shadow   = isset( $settings[ $prefix . 'enable_soft_shadow' ] ) ? $settings[ $prefix . 'enable_soft_shadow' ] : 'no';

			if ( empty( $items ) ) {
				return;
			}

			// Render FAQ Schema if active
			if ( 'yes' === $enable_schema ) {
				self::render_faq_schema( $items );
			}

			$accordion_id = 'es-accordion-' . esc_attr( $widget->get_id() );

			// Accordion Container classes
			$container_classes = array(
				'es-accordion-container',
				'es-accordion-preset-' . $preset,
			);
			if ( 'yes' === $toggle_animation ) {
				$container_classes[] = 'es-accordion-animated';
			}
			if ( 'yes' === $enable_glassmorphism ) {
				$container_classes[] = 'es-accordion-glassmorphism';
			}
			if ( 'yes' === $enable_border_glow ) {
				$container_classes[] = 'es-accordion-border-glow';
			}
			if ( 'yes' === $enable_modern_card ) {
				$container_classes[] = 'es-accordion-modern-card';
			}
			if ( 'yes' === $enable_soft_shadow ) {
				$container_classes[] = 'es-accordion-soft-shadow';
			}

			$container_attrs = array(
				'class'                   => implode( ' ', $container_classes ),
				'id'                      => $accordion_id,
				'data-allow-multiple'     => esc_attr( $allow_multiple ),
				'data-collapse-others'    => esc_attr( $collapse_others ),
				'data-animation-duration' => esc_attr( $anim_duration ),
				'data-icon-rotation'      => esc_attr( $icon_rotation ),
			);

			$widget->add_render_attribute( 'accordion_container_attr', $container_attrs );
			?>
			<div <?php $widget->print_render_attribute_string( 'accordion_container_attr' ); ?>>
				<?php
				$active_items = array();
				$has_active_item = false;
				foreach ( $items as $index => $item ) {
					$is_default_active = isset( $item['item_active'] ) && 'yes' === $item['item_active'];
					if ( $is_default_active ) {
						if ( 'yes' !== $allow_multiple && $has_active_item ) {
							$active_items[$index] = false;
						} else {
							$active_items[$index] = true;
							$has_active_item = true;
						}
					} else {
						$active_items[$index] = false;
					}
				}
				if ( ! $has_active_item && 'yes' === $open_first && count( $items ) > 0 ) {
					$active_items[0] = true;
				}

				foreach ( $items as $index => $item ) :
					$item_id   = $accordion_id . '-item-' . $index;
					$header_id = $accordion_id . '-header-' . $index;
					$panel_id  = $accordion_id . '-panel-' . $index;

					$is_item_active = ! empty( $active_items[$index] );
					$item_classes   = array( 'es-accordion-item' );
					if ( $is_item_active ) {
						$item_classes[] = 'es-active';
					}

					$item_attrs = array(
						'class' => implode( ' ', $item_classes ),
					);
					$widget->add_render_attribute( 'item_attr_' . $index, $item_attrs );

					// Header / Trigger Attributes
					$trigger_attrs = array(
						'class'         => 'es-accordion-header-trigger',
						'role'          => 'button',
						'id'            => $header_id,
						'aria-expanded' => $is_item_active ? 'true' : 'false',
						'aria-controls' => $panel_id,
						'tabindex'      => '0',
					);
					$widget->add_render_attribute( 'trigger_attr_' . $index, $trigger_attrs );

					// Panel / Content Attributes
					$panel_attrs = array(
						'class'           => 'es-accordion-panel',
						'id'              => $panel_id,
						'role'            => 'region',
						'aria-labelledby' => $header_id,
						'aria-hidden'     => $is_item_active ? 'false' : 'true',
					);
					if ( ! $is_item_active ) {
						$panel_attrs['style'] = 'display: none;';
					}
					$widget->add_render_attribute( 'panel_attr_' . $index, $panel_attrs );
					?>
					<div <?php $widget->print_render_attribute_string( 'item_attr_' . $index ); ?>>

						<!-- Accordion Header -->
						<div class="es-accordion-header" id="<?php echo esc_attr( $header_id ); ?>">
							<div <?php $widget->print_render_attribute_string( 'trigger_attr_' . $index ); ?>>

								<!-- Left Icon -->
								<?php if ( ( 'left' === $icon_pos || 'both' === $icon_pos ) && ! empty( $item['item_icon']['value'] ) ) : ?>
									<span class="es-accordion-icon-wrapper es-icon-left">
										<span class="es-accordion-icon-normal">
											<?php \Elementor\Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
										</span>
										<?php if ( ! empty( $item['item_active_icon']['value'] ) ) : ?>
											<span class="es-accordion-icon-active">
												<?php \Elementor\Icons_Manager::render_icon( $item['item_active_icon'], array( 'aria-hidden' => 'true' ) ); ?>
											</span>
										<?php endif; ?>
									</span>
								<?php endif; ?>

								<!-- Item Badge -->
								<?php if ( ! empty( $item['item_badge'] ) ) : ?>
									<span class="es-accordion-badge"><?php echo esc_html( $item['item_badge'] ); ?></span>
								<?php endif; ?>

								<!-- Numbering -->
								<?php
								if ( 'yes' === $enable_numbering ) :
									$formatted_num = self::format_number( $index, $number_format );
								?>
									<span class="es-accordion-number" aria-hidden="true"><?php echo esc_html( $number_prefix . $formatted_num . $number_suffix ); ?></span>
								<?php endif; ?>

								<!-- Title -->
								<span class="es-accordion-title"><?php echo esc_html( $item['item_title'] ); ?></span>

								<!-- Right Icon -->
								<?php if ( ( 'right' === $icon_pos || 'both' === $icon_pos ) && ! empty( $item['item_icon']['value'] ) ) : ?>
									<span class="es-accordion-icon-wrapper es-icon-right">
										<span class="es-accordion-icon-normal">
											<?php \Elementor\Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
										</span>
										<?php if ( ! empty( $item['item_active_icon']['value'] ) ) : ?>
											<span class="es-accordion-icon-active">
												<?php \Elementor\Icons_Manager::render_icon( $item['item_active_icon'], array( 'aria-hidden' => 'true' ) ); ?>
											</span>
										<?php endif; ?>
									</span>
								<?php endif; ?>

							</div>
						</div>

						<!-- Accordion Panel Body -->
						<div <?php $widget->print_render_attribute_string( 'panel_attr_' . $index ); ?>>
							<div class="es-accordion-content">
								<?php if ( ! empty( $item['item_image']['url'] ) ) : ?>
									<div class="es-accordion-item-image">
										<?php echo wp_get_attachment_image( $item['item_image']['id'], 'medium' ); ?>
									</div>
								<?php endif; ?>
								<div class="es-accordion-item-text">
									<?php echo do_shortcode( wp_kses_post( $item['item_content'] ) ); ?>
								</div>
							</div>
						</div>

					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}
