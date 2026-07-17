<?php
/**
 * Elonix – Toolkit for Elementor Elementor Social Base Widget Class
 *
 * Shared architectural foundation for Social Icons and Post Share widgets.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Elonix_Social_Base_Widget extends Elonix_Widget_Base {

	/**
	 * Helper function to retrieve default platforms icons class strings.
	 * Supports both static profile networks and dynamic sharing platforms.
	 *
	 * @param string $platform Platform slug.
	 * @return string FontAwesome icon class.
	 */
	protected function get_default_platform_icon( $platform ) {
		$icons = array(
			'facebook'  => 'fab fa-facebook-f',
			'twitter'   => 'fab fa-x-twitter',
			'instagram' => 'fab fa-instagram',
			'linkedin'  => 'fab fa-linkedin-in',
			'youtube'   => 'fab fa-youtube',
			'tiktok'    => 'fab fa-tiktok',
			'telegram'  => 'fab fa-telegram-plane',
			'whatsapp'  => 'fab fa-whatsapp',
			'pinterest' => 'fab fa-pinterest-p',
			'reddit'    => 'fab fa-reddit-alien',
			'discord'   => 'fab fa-discord',
			'github'    => 'fab fa-github',
			'behance'   => 'fab fa-behance',
			'dribbble'  => 'fab fa-dribbble',
			'medium'    => 'fab fa-medium-m',
			'skype'     => 'fab fa-skype',
			'vimeo'     => 'fab fa-vimeo-v',
			'snapchat'  => 'fab fa-snapchat-ghost',
			'threads'   => 'fab fa-threads',
			'bluesky'   => 'fas fa-cloud',
			'tumblr'    => 'fab fa-tumblr',
			'email'     => 'fas fa-envelope',
			'copy_link' => 'fas fa-link',
			'custom'    => 'fas fa-share-alt',
		);

		return isset( $icons[ $platform ] ) ? $icons[ $platform ] : 'fas fa-share-alt';
	}

	/**
	 * Register per-item style controls for repeater.
	 *
	 * @param \Elementor\Repeater $repeater Repeater instance.
	 */
	protected function register_item_style_controls( $repeater ) {
		$repeater->start_controls_tabs( 'tabs_item_style' );

		// Normal State
		$repeater->start_controls_tab(
			'tab_item_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$repeater->add_control(
			'item_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} {{CURRENT_ITEM}} .es-social-icon-box' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$repeater->add_control(
			'item_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .es-social-icon-box' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$repeater->end_controls_tab();

		// Hover State
		$repeater->start_controls_tab(
			'tab_item_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$repeater->add_control(
			'item_icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}:hover' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} {{CURRENT_ITEM}}:hover .es-social-icon-box' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
				),
			)
		);

		$repeater->add_control(
			'item_bg_color_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}:hover .es-social-icon-box' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();
	}

	/**
	 * Register Layout Options Section.
	 */
	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout_options',
			array(
				'label' => esc_html__( 'Layout Options', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout_type',
			array(
				'label'        => esc_html__( 'Layout Type', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'horizontal',
				'options'      => array(
					'horizontal' => esc_html__( 'Horizontal Flow', 'elonix' ),
					'vertical'   => esc_html__( 'Vertical List', 'elonix' ),
					'inline'     => esc_html__( 'Inline Flex', 'elonix' ),
					'grid'       => esc_html__( 'Grid Layout', 'elonix' ),
				),
				'prefix_class' => 'es-social-layout-',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => esc_html__( 'Alignment', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::CHOOSE,
				'options'      => array(
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
				'default'      => 'center',
				'condition'    => array(
					'layout_type!' => 'grid',
				),
				'prefix_class' => 'es-social-align%s-',
			)
		);

		$this->add_responsive_control(
			'grid_columns',
			array(
				'label'          => esc_html__( 'Grid Columns', 'elonix' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '3',
				'mobile_default' => '2',
				'options'        => array(
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'8'  => '8',
					'10' => '10',
					'12' => '12',
				),
				'condition'      => array(
					'layout_type' => 'grid',
				),
				'selectors'      => array(
					'{{WRAPPER}} .elonix-social-icons-wrapper' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Icon Settings Section.
	 */
	protected function register_icon_settings_controls() {
		$this->start_controls_section(
			'section_icon_settings',
			array(
				'label' => esc_html__( 'Icon Settings', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 18,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-icon-box'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-social-icon-box svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'use_height_equals_width',
			array(
				'label'        => esc_html__( 'Use Height = Width', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'es-use-height-width-',
			)
		);

		$this->add_responsive_control(
			'icon_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}'                     => '--es-icon-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-social-icon-box' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'condition'  => array(
					'use_height_equals_width!' => 'yes',
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-icon-box' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_line_height',
			array(
				'label'      => esc_html__( 'Line Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-icon-box' => 'line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_padding',
			array(
				'label'      => esc_html__( 'Icon Inner Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-icon-box' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'      => esc_html__( 'Gap Between Icons', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-social-icons-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_rotation',
			array(
				'label'     => esc_html__( 'Icon Rotation', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -360,
						'max' => 360,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors' => array(
					'{{WRAPPER}} .es-social-icon-box' => 'transform: rotate({{SIZE}}deg);',
				),
			)
		);

		$this->add_control(
			'icon_shape',
			array(
				'label'        => esc_html__( 'Icon Shape', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'circle',
				'options'      => array(
					'none'    => esc_html__( 'None', 'elonix' ),
					'circle'  => esc_html__( 'Circle', 'elonix' ),
					'square'  => esc_html__( 'Square', 'elonix' ),
					'rounded' => esc_html__( 'Rounded Square', 'elonix' ),
					'hexagon' => esc_html__( 'Hexagon', 'elonix' ),
				),
				'prefix_class' => 'es-social-shape-',
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'        => esc_html__( 'Icon Position', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'icon_only',
				'options'      => array(
					'left'       => esc_html__( 'Icon Before Label', 'elonix' ),
					'right'      => esc_html__( 'Icon After Label', 'elonix' ),
					'top'        => esc_html__( 'Icon Above Label', 'elonix' ),
					'icon_only'  => esc_html__( 'Icon Only', 'elonix' ),
					'label_only' => esc_html__( 'Label Only', 'elonix' ),
				),
				'prefix_class' => 'es-social-pos-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Presets Section.
	 */
	protected function register_style_presets_controls() {
		$this->start_controls_section(
			'section_style_presets',
			array(
				'label' => esc_html__( 'Style Presets', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'preset_style',
			array(
				'label'        => esc_html__( 'Preset Style', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'modern',
				'options'      => array(
					'minimal'       => esc_html__( 'Style 1: Minimalist', 'elonix' ),
					'modern'        => esc_html__( 'Style 2: Modern Flat', 'elonix' ),
					'outline'       => esc_html__( 'Style 3: Outline Edge', 'elonix' ),
					'glassmorphism' => esc_html__( 'Style 4: Glassmorphism', 'elonix' ),
					'gradient'      => esc_html__( 'Style 5: Gradient Spark', 'elonix' ),
					'floating'      => esc_html__( 'Style 6: Floating Accent', 'elonix' ),
					'border_glow'   => esc_html__( 'Style 7: Border Glow Effect', 'elonix' ),
					'neumorphism'   => esc_html__( 'Style 8: Neumorphic Depth', 'elonix' ),
				),
				'prefix_class' => 'es-social-preset-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Tooltips Settings Section.
	 */
	protected function register_tooltip_controls() {
		$this->start_controls_section(
			'section_tooltips',
			array(
				'label' => esc_html__( 'Tooltips Settings', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_tooltip',
			array(
				'label'        => esc_html__( 'Enable Tooltip', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'es-social-tooltip-',
			)
		);

		$this->add_control(
			'tooltip_position',
			array(
				'label'        => esc_html__( 'Tooltip Position', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'top',
				'options'      => array(
					'top'    => esc_html__( 'Top', 'elonix' ),
					'bottom' => esc_html__( 'Bottom', 'elonix' ),
					'left'   => esc_html__( 'Left', 'elonix' ),
					'right'  => esc_html__( 'Right', 'elonix' ),
				),
				'condition'    => array(
					'enable_tooltip' => 'yes',
				),
				'prefix_class' => 'es-tooltip-dir-',
			)
		);

		$this->add_control(
			'tooltip_animation',
			array(
				'label'        => esc_html__( 'Tooltip Animation', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'fade',
				'options'      => array(
					'fade'  => esc_html__( 'Fade In', 'elonix' ),
					'slide' => esc_html__( 'Slide Slide', 'elonix' ),
					'zoom'  => esc_html__( 'Zoom Scale', 'elonix' ),
				),
				'condition'    => array(
					'enable_tooltip' => 'yes',
				),
				'prefix_class' => 'es-tooltip-anim-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Advanced Brand Config Section.
	 */
	protected function register_advanced_brand_controls() {
		$this->start_controls_section(
			'section_advanced_features',
			array(
				'label' => esc_html__( 'Advanced Brand Config', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'brand_colors',
			array(
				'label'        => esc_html__( 'Official Brand Colors', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Apply official colors to custom brand items dynamically.', 'elonix' ),
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'es-social-brand-colors-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register entire Style Tab Controls (Social Icon Styles & Tooltip Styles).
	 */
	protected function register_style_tabs_controls() {
		// ==========================================
		// STYLE TAB - SOCIAL ICONS DESIGN SECTION
		// ==========================================
		$this->start_controls_section(
			'section_style_icons',
			array(
				'label' => esc_html__( 'Social Icon Styles', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_icon_style' );

		// NORMAL STATE TAB
		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Label Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-label',
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon & Label Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .es-social-item'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-social-icon-box' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
				'condition' => array(
					'brand_colors!' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .es-social-icon-box' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'brand_colors!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-icon-box',
			)
		);

		$this->add_responsive_control(
			'icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'icon_shape!' => 'hexagon',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'icon_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-icon-box',
			)
		);

		$this->add_responsive_control(
			'icon_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1.0,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 1.0,
				),
				'selectors' => array(
					'{{WRAPPER}} .es-social-item' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		// HOVER STATE TAB
		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color_hover',
			array(
				'label'     => esc_html__( 'Icon & Label Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-social-item:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-social-item:hover .es-social-icon-box' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
				'condition' => array(
					'brand_colors!' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-social-item:hover .es-social-icon-box' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'brand_colors!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border_hover',
				'label'    => esc_html__( 'Border Hover', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-item:hover .es-social-icon-box',
			)
		);

		$this->add_responsive_control(
			'icon_border_radius_hover',
			array(
				'label'      => esc_html__( 'Border Radius Hover', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-item:hover .es-social-icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'icon_shape!' => 'hexagon',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'icon_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow Hover', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-item:hover .es-social-icon-box',
			)
		);

		$this->add_control(
			'icon_opacity_hover',
			array(
				'label'     => esc_html__( 'Opacity Hover', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1.0,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-social-item:hover' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'        => esc_html__( 'Hover Micro Effect', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => 'grow',
				'options'      => array(
					'none'       => esc_html__( 'None', 'elonix' ),
					'grow'       => esc_html__( 'Grow Larger', 'elonix' ),
					'shrink'     => esc_html__( 'Shrink Smaller', 'elonix' ),
					'rotate'     => esc_html__( 'Rotate Clockwise', 'elonix' ),
					'bounce'     => esc_html__( 'Bounce Up & Down', 'elonix' ),
					'float'      => esc_html__( 'Float Hovering', 'elonix' ),
					'pulse'      => esc_html__( 'Pulse Glow', 'elonix' ),
					'slide-up'   => esc_html__( 'Slide Upward', 'elonix' ),
					'slide-down' => esc_html__( 'Slide Downward', 'elonix' ),
					'glow'       => esc_html__( 'Shadow Glow', 'elonix' ),
					'flip'       => esc_html__( '3D Flip Over', 'elonix' ),
				),
				'prefix_class' => 'es-social-hover-',
			)
		);

		$this->add_control(
			'hover_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (ms)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 2000,
						'step' => 50,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 300,
				),
				'selectors' => array(
					'{{WRAPPER}} .es-social-item, {{WRAPPER}} .es-social-icon-box' => 'transition-duration: {{SIZE}}ms;',
				),
			)
		);

		$this->end_controls_tab();

		// ACTIVE STATE TAB
		$this->start_controls_tab(
			'tab_icon_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color_active',
			array(
				'label'     => esc_html__( 'Icon & Label Active Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-social-item:active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-social-item:active .es-social-icon-box' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color_active',
			array(
				'label'     => esc_html__( 'Background Active Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-social-item:active .es-social-icon-box' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'icon_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow Active', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-item:active .es-social-icon-box',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - TOOLTIPS DESIGN SECTION
		// ==========================================
		$this->start_controls_section(
			'section_style_tooltips',
			array(
				'label'     => esc_html__( 'Tooltip Styles', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_tooltip' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'tooltip_typography',
				'label'    => esc_html__( 'Tooltip Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-tooltip',
			)
		);

		$this->add_control(
			'tooltip_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .es-social-tooltip' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tooltip_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array(
					'{{WRAPPER}} .es-social-tooltip' => 'background-color: {{VALUE}};',
					'{{WRAPPER}}.es-tooltip-dir-top .es-social-tooltip::after' => 'border-top-color: {{VALUE}};',
					'{{WRAPPER}}.es-tooltip-dir-bottom .es-social-tooltip::after' => 'border-bottom-color: {{VALUE}};',
					'{{WRAPPER}}.es-tooltip-dir-left .es-social-tooltip::after' => 'border-left-color: {{VALUE}};',
					'{{WRAPPER}}.es-tooltip-dir-right .es-social-tooltip::after' => 'border-right-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'tooltip_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-tooltip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'tooltip_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-social-tooltip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'tooltip_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-social-tooltip',
			)
		);

		$this->end_controls_section();
	}
}
