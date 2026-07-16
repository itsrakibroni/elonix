<?php
/**
 * Elonix – Toolkit for Elementor Featured Image Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Featured_Image_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-featured-image';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Featured Image', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-featured-image';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'featured', 'image', 'thumbnail', 'post', 'page', 'archive', 'dynamic', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-featured-image' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - Image
		$this->start_controls_section(
			'section_featured_image',
			array(
				'label' => esc_html__( 'Featured Image', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image_source',
			array(
				'label'   => esc_html__( 'Image Source', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => array(
					'featured' => esc_html__( 'Featured Image', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'fallback_type',
			array(
				'label'   => esc_html__( 'Fallback Image', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'placeholder',
				'options' => array(
					'none'        => esc_html__( 'None', 'elonix' ),
					'placeholder' => esc_html__( 'Placeholder', 'elonix' ),
					'custom'      => esc_html__( 'Custom Image', 'elonix' ),
				),
				'description' => esc_html__( 'Displayed if the current post or queried object does not have a featured image.', 'elonix' ),
			)
		);

		$this->add_control(
			'custom_fallback',
			array(
				'label'     => esc_html__( 'Choose Fallback Image', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'default'   => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'fallback_type' => 'custom',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'image',
				'default' => 'large',
			)
		);

		$this->add_control(
			'object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'default' => esc_html__( 'Default', 'elonix' ),
					'cover'   => esc_html__( 'Cover', 'elonix' ),
					'contain' => esc_html__( 'Contain', 'elonix' ),
					'fill'    => esc_html__( 'Fill', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'object_position',
			array(
				'label'     => esc_html__( 'Object Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => array(
					'center center' => esc_html__( 'Center Center', 'elonix' ),
					'center top'    => esc_html__( 'Center Top', 'elonix' ),
					'center bottom' => esc_html__( 'Center Bottom', 'elonix' ),
					'left center'   => esc_html__( 'Left Center', 'elonix' ),
					'left top'      => esc_html__( 'Left Top', 'elonix' ),
					'left bottom'   => esc_html__( 'Left Bottom', 'elonix' ),
					'right center'  => esc_html__( 'Right Center', 'elonix' ),
					'right top'     => esc_html__( 'Right Top', 'elonix' ),
					'right bottom'  => esc_html__( 'Right Bottom', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'object-position: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lazy_load',
			array(
				'label'        => esc_html__( 'Lazy Loading', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'elonix' ),
				'label_off'    => esc_html__( 'Disable', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'link_type',
			array(
				'label'   => esc_html__( 'Link', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'            => esc_html__( 'None', 'elonix' ),
					'media'           => esc_html__( 'Media File', 'elonix' ),
					'attachment_page' => esc_html__( 'Attachment Page', 'elonix' ),
					'post'            => esc_html__( 'Post URL', 'elonix' ),
					'custom'          => esc_html__( 'Custom URL', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Custom Link URL', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
				'condition'   => array(
					'link_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'open_new_tab',
			array(
				'label'        => esc_html__( 'Open in New Tab', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'link_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'nofollow',
			array(
				'label'        => esc_html__( 'NoFollow', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'link_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'custom_rel',
			array(
				'label'       => esc_html__( 'Custom Attributes / Rel', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. sponsored, external', 'elonix' ),
				'condition'   => array(
					'link_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();

		// Content Section - Image Ratio
		$this->start_controls_section(
			'section_image_ratio',
			array(
				'label' => esc_html__( 'Image Ratio', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'aspect_ratio',
			array(
				'label'   => esc_html__( 'Aspect Ratio', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'original',
				'options' => array(
					'original' => esc_html__( 'Original', 'elonix' ),
					'1 / 1'    => esc_html__( '1:1 (Square)', 'elonix' ),
					'4 / 3'    => esc_html__( '4:3 (Standard)', 'elonix' ),
					'3 / 2'    => esc_html__( '3:2 (Classic)', 'elonix' ),
					'16 / 9'   => esc_html__( '16:9 (Widescreen)', 'elonix' ),
					'21 / 9'   => esc_html__( '21:9 (Cinematic)', 'elonix' ),
					'9 / 16'   => esc_html__( '9:16 (Vertical)', 'elonix' ),
					'custom'   => esc_html__( 'Custom Ratio', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'custom_aspect_ratio',
			array(
				'label'       => esc_html__( 'Custom Ratio', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. 16 / 10', 'elonix' ),
				'default'     => '16 / 10',
				'selectors'   => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'aspect-ratio: {{VALUE}};',
				),
				'condition'   => array(
					'aspect_ratio' => 'custom',
				),
			)
		);

		$this->end_controls_section();

		// Content Section - Advanced / Accessibility
		$this->start_controls_section(
			'section_advanced_content',
			array(
				'label' => esc_html__( 'Caption & Accessibility', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_caption',
			array(
				'label'        => esc_html__( 'Image Caption', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'elonix' ),
				'label_off'    => esc_html__( 'Disable', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'caption_position',
			array(
				'label'     => esc_html__( 'Caption Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'below',
				'options'   => array(
					'below'   => esc_html__( 'Below Image', 'elonix' ),
					'overlay' => esc_html__( 'Overlay on Image', 'elonix' ),
				),
				'condition' => array(
					'show_caption' => 'yes',
				),
			)
		);

		$this->add_control(
			'auto_alt_info',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: Media Library Alt text and ARIA standard attributes are automatically respected and implemented.', 'elonix' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();

		// Style Section - Image
		$this->start_controls_section(
			'section_image_style',
			array(
				'label' => esc_html__( 'Image Styles', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'max_height',
			array(
				'label'      => esc_html__( 'Max Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
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
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .tv-featured-image-container img' => 'margin-left: {{VALUE}} === "left" ? "0" : ({{VALUE}} === "right" ? "auto" : "auto"); margin-right: {{VALUE}} === "right" ? "0" : ({{VALUE}} === "left" ? "auto" : "auto");',
				),
			)
		);

		// Style Tabs for Normal & Hover States
		$this->start_controls_tabs( 'tabs_featured_image_states' );

		// Normal State
		$this->start_controls_tab(
			'tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .tv-featured-image-container img',
			)
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'hover_opacity',
			array(
				'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container:hover img' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_control(
			'overlay_hover_color',
			array(
				'label'     => esc_html__( 'Overlay Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container:hover .tv-featured-image-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'hover_css_filters',
				'selector' => '{{WRAPPER}} .tv-featured-image-container:hover img',
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'   => esc_html__( 'Hover Animation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'      => esc_html__( 'None', 'elonix' ),
					'zoom-in'   => esc_html__( 'Zoom In', 'elonix' ),
					'zoom-out'  => esc_html__( 'Zoom Out', 'elonix' ),
					'scale'     => esc_html__( 'Scale Up', 'elonix' ),
					'rotate'    => esc_html__( 'Rotate', 'elonix' ),
					'grayscale' => esc_html__( 'Grayscale', 'elonix' ),
					'blur'      => esc_html__( 'Blur', 'elonix' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (ms)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 300,
				),
				'range'     => array(
					'px' => array(
						'min' => 100,
						'max' => 3000,
						'step' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'transition: all {{SIZE}}ms ease;',
					'{{WRAPPER}} .tv-featured-image-overlay'       => 'transition: all {{SIZE}}ms ease;',
				),
				'separator' => 'before',
			)
		);

		// Borders, Radii & Shadow Controls
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .tv-featured-image-container img, {{WRAPPER}} .tv-featured-image-container .tv-featured-image-overlay',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .tv-featured-image-overlay'       => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .tv-featured-image-container img',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'background',
				'selector' => '{{WRAPPER}} .tv-featured-image-container',
			)
		);

		$this->add_responsive_control(
			'spacing_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'spacing_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Caption
		$this->start_controls_section(
			'section_caption_style',
			array(
				'label'     => esc_html__( 'Caption Styles', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_caption' => 'yes',
				),
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-image-caption' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'caption_typography',
				'selector' => '{{WRAPPER}} .tv-featured-image-caption',
			)
		);

		$this->add_responsive_control(
			'caption_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'caption_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-image-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Retrieve the featured image ID dynamically based on the current queried object / post context.
	 *
	 * @return int Attachment ID if found, 0 otherwise.
	 */
	protected function get_current_featured_image_id() {
		// 1. Check Elementor Theme Builder / Editor Preview Context
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				$preview_id = \Elementor\Plugin::$instance->editor->get_post_id();
				if ( ! $preview_id ) {
					$preview_id = \Elementor\Plugin::$instance->preview->get_post_id();
				}
				if ( $preview_id && has_post_thumbnail( $preview_id ) ) {
					return (int) get_post_thumbnail_id( $preview_id );
				}
			}
		}

		// 2. Global post object (Single Post, Page, CPT, WooCommerce Product)
		$post_id = get_the_ID();
		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			return (int) get_post_thumbnail_id( $post_id );
		}

		// 3. Queried Object Fallbacks (Archives, Terms, Custom Objects)
		$queried_object = get_queried_object();
		if ( $queried_object ) {
			if ( isset( $queried_object->ID ) && has_post_thumbnail( $queried_object->ID ) ) {
				return (int) get_post_thumbnail_id( $queried_object->ID );
			}
			// Taxonomy Term Archive thumbnail (WooCommerce product_cat, custom term metas)
			if ( isset( $queried_object->term_id ) ) {
				$term_id      = $queried_object->term_id;
				$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
				if ( ! $thumbnail_id ) {
					$thumbnail_id = get_term_meta( $term_id, '_thumbnail_id', true );
				}
				if ( ! $thumbnail_id && function_exists( 'get_woocommerce_term_meta' ) ) {
					$thumbnail_id = get_woocommerce_term_meta( $term_id, 'thumbnail_id', true );
				}
				if ( $thumbnail_id ) {
					return (int) $thumbnail_id;
				}
			}
		}

		// 4. Search Templates / General Archives fallback to first post in loop if in main query
		if ( ( is_search() || is_archive() ) && have_posts() ) {
			global $wp_query;
			if ( ! empty( $wp_query->posts[0]->ID ) && has_post_thumbnail( $wp_query->posts[0]->ID ) ) {
				return (int) get_post_thumbnail_id( $wp_query->posts[0]->ID );
			}
		}

		return 0;
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$image_id = $this->get_current_featured_image_id();
		$fallback_url = '';

		if ( ! $image_id ) {
			if ( 'none' === $settings['fallback_type'] ) {
				return; // Empty state: render nothing without notices
			} elseif ( 'placeholder' === $settings['fallback_type'] ) {
				$fallback_url = \Elementor\Utils::get_placeholder_image_src();
			} elseif ( 'custom' === $settings['fallback_type'] && ! empty( $settings['custom_fallback']['id'] ) ) {
				$image_id = $settings['custom_fallback']['id'];
			} elseif ( 'custom' === $settings['fallback_type'] && ! empty( $settings['custom_fallback']['url'] ) ) {
				$fallback_url = $settings['custom_fallback']['url'];
			} else {
				return; // No valid fallback available
			}
		}

		// Add Wrapper Classes
		$this->add_render_attribute( 'wrapper', 'class', 'tv-featured-image-container' );

		if ( ! empty( $settings['hover_effect'] ) && 'none' !== $settings['hover_effect'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'tv-effect-' . $settings['hover_effect'] );
		}

		if ( 'yes' === $settings['show_caption'] && 'overlay' === $settings['caption_position'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'tv-caption-overlay' );
		}

		// Configure Image Tag Alt, Title and loading Attributes
		$alt = '';
		if ( $image_id ) {
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		}

		$title = $image_id ? get_the_title( $image_id ) : '';

		$attr = array(
			'alt'   => esc_attr( $alt ),
			'title' => esc_attr( $title ),
			'role'  => 'img',
		);

		if ( ! empty( $settings['lazy_load'] ) && 'yes' === $settings['lazy_load'] ) {
			$attr['loading']  = 'lazy';
			$attr['decoding'] = 'async';
		} else {
			$attr['loading'] = 'eager';
		}

		// Resolve Link Options
		$link_url = '';
		if ( 'media' === $settings['link_type'] ) {
			$link_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : $fallback_url;
		} elseif ( 'attachment_page' === $settings['link_type'] && $image_id ) {
			$link_url = get_attachment_link( $image_id );
		} elseif ( 'post' === $settings['link_type'] ) {
			$post_id = get_the_ID();
			if ( $post_id ) {
				$link_url = get_permalink( $post_id );
			}
		} elseif ( 'custom' === $settings['link_type'] && ! empty( $settings['link']['url'] ) ) {
			$link_url = $settings['link']['url'];
		}

		if ( ! empty( $link_url ) ) {
			$this->add_render_attribute( 'link', 'href', esc_url( $link_url ) );
			$this->add_render_attribute( 'link', 'class', 'tv-featured-image-link' );

			if ( 'yes' === $settings['open_new_tab'] || ( 'custom' === $settings['link_type'] && ! empty( $settings['link']['is_external'] ) ) ) {
				$this->add_render_attribute( 'link', 'target', '_blank' );
			}
			if ( 'yes' === $settings['nofollow'] || ( 'custom' === $settings['link_type'] && ! empty( $settings['link']['nofollow'] ) ) ) {
				$this->add_render_attribute( 'link', 'rel', 'nofollow' );
			}
			if ( ! empty( $settings['custom_rel'] ) ) {
				$this->add_render_attribute( 'link', 'rel', esc_attr( $settings['custom_rel'] ) );
			}
		}

		// Resolve Caption Options
		$caption = '';
		if ( 'yes' === $settings['show_caption'] && $image_id ) {
			$attachment = get_post( $image_id );
			if ( $attachment && ! empty( $attachment->post_excerpt ) ) {
				$caption = $attachment->post_excerpt;
			}
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( ! empty( $link_url ) ) : ?>
				<a <?php $this->print_render_attribute_string( 'link' ); ?>>
			<?php endif; ?>

			<div class="tv-featured-image-overlay"></div>

			<?php
			if ( $image_id ) {
				$settings['image'] = array(
					'id'  => $image_id,
					'url' => wp_get_attachment_image_url( $image_id, 'full' ),
				);
				$image_html = \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'image', 'image' );
				if ( ! empty( $image_html ) ) {
					echo wp_kses_post( $image_html );
				} else {
					echo wp_get_attachment_image( $image_id, 'large', false, $attr );
				}
			} else {
				echo '<img src="' . esc_url( $fallback_url ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" loading="' . esc_attr( $attr['loading'] ) . '" role="img" />';
			}
			?>

			<?php if ( ! empty( $link_url ) ) : ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $caption ) ) : ?>
				<div class="tv-featured-image-caption">
					<?php echo esc_html( $caption ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
