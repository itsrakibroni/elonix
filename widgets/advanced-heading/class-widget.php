<?php
/**
 * Elonix – Toolkit for Elementor Advanced Heading Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Utils;

class Elonix_Toolkit_Heading_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'tv-heading';
	}

	public function get_title() {
		return esc_html__( 'Heading', 'elonix' );
	}

	public function get_tv_widget_icon() {
		return 'eicon-heading';
	}

	public function get_tv_widget_keywords() {
		return array( 'heading', 'title', 'dual', 'gradient', 'watermark', 'highlight', 'typography' );
	}

	public function get_style_depends() {
		return array( 'elonix-widget-tv-heading' );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_link_controls();
		$this->register_decoration_controls();
		$this->register_accessibility_controls();

		$this->register_text_part_style_controls( 'prefix', esc_html__( 'Prefix', 'elonix' ), '{{WRAPPER}} .elonix-heading-title-prefix' );
		$this->register_text_part_style_controls( 'title', esc_html__( 'Main Heading', 'elonix' ), '{{WRAPPER}} .elonix-heading-title-main' );
		$this->register_text_part_style_controls( 'highlight', esc_html__( 'Highlight', 'elonix' ), '{{WRAPPER}} .elonix-heading-title-highlight' );
		$this->register_text_part_style_controls( 'suffix', esc_html__( 'Suffix', 'elonix' ), '{{WRAPPER}} .elonix-heading-title-suffix' );

		$this->register_decoration_style_controls();
		$this->register_watermark_style_controls();
	}

	protected function register_content_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Heading Content', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'heading_source',
			array(
				'label'   => esc_html__( 'Dynamic Source', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'custom'         => esc_html__( 'Custom Text', 'elonix' ),
					'page_title'     => esc_html__( 'Current Page Title', 'elonix' ),
					'archive_title'  => esc_html__( 'Archive Title', 'elonix' ),
					'site_title'     => esc_html__( 'Site Title', 'elonix' ),
					'site_tagline'   => esc_html__( 'Site Tagline', 'elonix' ),
					'current_year'   => esc_html__( 'Current Year', 'elonix' ),
					'current_date'   => esc_html__( 'Current Date', 'elonix' ),
					'author'         => esc_html__( 'Author Name', 'elonix' ),
					'woo_product'    => esc_html__( 'WooCommerce Product Title', 'elonix' ),
				),
				'default' => 'custom',
			)
		);

		$this->add_control(
			'title_prefix',
			array(
				'label'       => esc_html__( 'Prefix Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Main Heading', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Advanced Heading Title', 'elonix' ),
				'dynamic'     => array( 'active' => true ),
				'rows'        => 3,
				'condition'   => array( 'heading_source' => 'custom' ),
			)
		);

		$this->add_control(
			'fallback_text',
			array(
				'label'       => esc_html__( 'Fallback Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Text to display if the dynamic source is empty.', 'elonix' ),
				'condition'   => array( 'heading_source!' => 'custom' ),
			)
		);

		$this->add_control(
			'title_highlight',
			array(
				'label'       => esc_html__( 'Highlight Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title_suffix',
			array(
				'label'       => esc_html__( 'Suffix Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => esc_html__( 'Left', 'elonix' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => esc_html__( 'Center', 'elonix' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => esc_html__( 'Right', 'elonix' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => esc_html__( 'Justified', 'elonix' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-heading-wrapper' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-heading-wrapper' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
				),
			)
		);
		
		$this->add_control(
			'white_space',
			array(
				'label'   => esc_html__( 'Text Wrap', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					''        => esc_html__( 'Default', 'elonix' ),
					'nowrap'  => esc_html__( 'No Wrap', 'elonix' ),
					'pre'     => esc_html__( 'Pre', 'elonix' ),
					'pre-wrap'=> esc_html__( 'Pre Wrap', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-title' => 'white-space: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'word_break',
			array(
				'label'   => esc_html__( 'Word Break', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					''             => esc_html__( 'Default', 'elonix' ),
					'break-all'    => esc_html__( 'Break All', 'elonix' ),
					'keep-all'     => esc_html__( 'Keep All', 'elonix' ),
					'break-word'   => esc_html__( 'Break Word', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-title' => 'word-break: {{VALUE}}; overflow-wrap: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_link_controls() {
		$this->start_controls_section(
			'section_link',
			array(
				'label' => esc_html__( 'Link', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'heading_link',
			array(
				'label'       => esc_html__( 'URL', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array( 'url' => '' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_decoration_controls() {
		$this->start_controls_section(
			'section_decoration',
			array(
				'label' => esc_html__( 'Decoration', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'decoration_type',
			array(
				'label'   => esc_html__( 'Decoration Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'none'         => esc_html__( 'None', 'elonix' ),
					'underline'    => esc_html__( 'Underline', 'elonix' ),
					'overline'     => esc_html__( 'Overline', 'elonix' ),
					'line_through' => esc_html__( 'Line Through', 'elonix' ),
					'svg_brush'    => esc_html__( 'SVG Brush', 'elonix' ),
					'mask_image'   => esc_html__( 'Image Mask', 'elonix' ),
				),
				'default' => 'none',
			)
		);

		$this->add_control(
			'decoration_image',
			array(
				'label'     => esc_html__( 'Mask Image', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'decoration_type' => 'mask_image' ),
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-decoration' => '-webkit-mask-image: url("{{URL}}"); mask-image: url("{{URL}}");',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_accessibility_controls() {
		$this->start_controls_section(
			'section_accessibility',
			array(
				'label' => esc_html__( 'Accessibility & Advanced', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'aria_label',
			array(
				'label'       => esc_html__( 'ARIA Label', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => esc_html__( 'Screen reader text replacing the visible heading.', 'elonix' ),
			)
		);

		$this->add_control(
			'heading_role',
			array(
				'label'   => esc_html__( 'Role', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					''        => esc_html__( 'Default', 'elonix' ),
					'heading' => esc_html__( 'Heading', 'elonix' ),
					'banner'  => esc_html__( 'Banner', 'elonix' ),
				),
				'default' => '',
			)
		);
		
		$this->add_control(
			'watermark_text',
			array(
				'label'       => esc_html__( 'Watermark Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => esc_html__( 'Adds a large background text behind the heading.', 'elonix' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_text_part_style_controls( $id, $label, $selector ) {
		$this->start_controls_section(
			"section_{$id}_style",
			array(
				'label' => $label,
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( "tabs_{$id}_style" );

		// Normal Tab
		$this->start_controls_tab( "tab_{$id}_normal", array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

		$this->add_control(
			"{$id}_color_type",
			array(
				'label'   => esc_html__( 'Color Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'classic'  => array( 'title' => esc_html__( 'Classic', 'elonix' ), 'icon' => 'eicon-paint-brush' ),
					'gradient' => array( 'title' => esc_html__( 'Gradient', 'elonix' ), 'icon' => 'eicon-barcode' ),
				),
				'default' => 'classic',
			)
		);

		$this->add_control(
			"{$id}_color",
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array( "{$id}_color_type" => 'classic' ),
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
					( $id === 'title' ? '{{WRAPPER}} .elonix-heading-title { color: {{VALUE}}; }' : '' ) // legacy support
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'      => "{$id}_gradient",
				'types'     => array( 'gradient' ),
				'selector'  => $selector,
				'condition' => array( "{$id}_color_type" => 'gradient' ),
			)
		);

		$this->add_control(
			"{$id}_gradient_clip",
			array(
				'type'      => \Elementor\Controls_Manager::HIDDEN,
				'default'   => 'gradient',
				'condition' => array( "{$id}_color_type" => 'gradient' ),
				'selectors' => array(
					$selector => 'color: transparent; -webkit-background-clip: text; background-clip: text;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => "{$id}_typography",
				'selector' => $selector . ( $id === 'title' ? ', {{WRAPPER}} .elonix-heading-title' : '' ),
			)
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => "{$id}_stroke",
				'selector' => $selector,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => "{$id}_shadow",
				'selector' => $selector . ( $id === 'title' ? ', {{WRAPPER}} .elonix-heading-title' : '' ),
			)
		);

		$this->add_responsive_control(
			"{$id}_margin",
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					$selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: inline-block;',
					( $id === 'title' ? '{{WRAPPER}} .elonix-heading-title { margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; }' : '' )
				),
			)
		);

		$this->add_responsive_control(
			"{$id}_padding",
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: inline-block;',
					( $id === 'title' ? '{{WRAPPER}} .elonix-heading-title { padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; }' : '' )
				),
			)
		);
		
		$this->add_control(
			"{$id}_blend_mode",
			array(
				'label'   => esc_html__( 'Blend Mode', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					''            => esc_html__( 'Normal', 'elonix' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn'  => 'Color Burn',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'luminosity'  => 'Luminosity',
				),
				'selectors' => array(
					$selector => 'mix-blend-mode: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab( "tab_{$id}_hover", array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );

		$this->add_control(
			"{$id}_hover_color_type",
			array(
				'label'   => esc_html__( 'Hover Color Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'classic'  => array( 'title' => esc_html__( 'Classic', 'elonix' ), 'icon' => 'eicon-paint-brush' ),
					'gradient' => array( 'title' => esc_html__( 'Gradient', 'elonix' ), 'icon' => 'eicon-barcode' ),
				),
				'default' => 'classic',
			)
		);

		$hover_selector = '{{WRAPPER}} .elonix-advanced-heading-wrapper:hover ' . $selector;

		$this->add_control(
			"{$id}_hover_color",
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array( "{$id}_hover_color_type" => 'classic' ),
				'selectors' => array(
					$hover_selector => 'color: {{VALUE}}; transition: all 0.3s ease;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'      => "{$id}_hover_gradient",
				'types'     => array( 'gradient' ),
				'selector'  => $hover_selector,
				'condition' => array( "{$id}_hover_color_type" => 'gradient' ),
			)
		);

		$this->add_control(
			"{$id}_hover_gradient_clip",
			array(
				'type'      => \Elementor\Controls_Manager::HIDDEN,
				'default'   => 'gradient',
				'condition' => array( "{$id}_hover_color_type" => 'gradient' ),
				'selectors' => array(
					$hover_selector => 'color: transparent; -webkit-background-clip: text; background-clip: text; transition: all 0.3s ease;',
				),
			)
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => "{$id}_hover_stroke",
				'selector' => $hover_selector,
			)
		);
		
		$this->add_control(
			"{$id}_hover_transition",
			array(
				'label'      => esc_html__( 'Transition Duration', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => array( 's' => array( 'max' => 3, 'step' => 0.1 ) ),
				'selectors'  => array(
					$selector => 'transition: all {{SIZE}}s ease;',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_decoration_style_controls() {
		$this->start_controls_section(
			'section_decoration_style',
			array(
				'label'     => esc_html__( 'Decoration', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'decoration_type!' => 'none' ),
			)
		);

		$this->add_control(
			'decoration_color',
			array(
				'label'     => esc_html__( 'Decoration Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-decoration' => 'background-color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'decoration_height',
			array(
				'label'      => esc_html__( 'Height / Thickness', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-heading-decoration' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'decoration_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-heading-decoration' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		
		$this->add_responsive_control(
			'decoration_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-heading-decoration' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'decoration_mask_size',
			array(
				'label'     => esc_html__( 'Mask Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'contain' => esc_html__( 'Contain', 'elonix' ),
					'cover'   => esc_html__( 'Cover', 'elonix' ),
					'auto'    => esc_html__( 'Auto', 'elonix' ),
				),
				'default'   => 'contain',
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-decoration' => '-webkit-mask-size: {{VALUE}}; mask-size: {{VALUE}};',
				),
				'condition' => array( 'decoration_type' => 'mask_image' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_watermark_style_controls() {
		$this->start_controls_section(
			'section_watermark_style',
			array(
				'label'     => esc_html__( 'Watermark', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'watermark_text!' => '' ),
			)
		);

		$this->add_control(
			'watermark_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-watermark' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'watermark_typography',
				'selector' => '{{WRAPPER}} .elonix-heading-watermark',
			)
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'watermark_stroke',
				'selector' => '{{WRAPPER}} .elonix-heading-watermark',
			)
		);

		$this->add_control(
			'watermark_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-heading-watermark' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function get_dynamic_title() {
		$settings = $this->get_settings_for_display();
		$source   = ! empty( $settings['heading_source'] ) ? $settings['heading_source'] : 'custom';
		
		$title = '';
		switch ( $source ) {
			case 'page_title':
				$title = get_the_title();
				break;
			case 'archive_title':
				$title = get_the_archive_title();
				break;
			case 'site_title':
				$title = get_bloginfo( 'name' );
				break;
			case 'site_tagline':
				$title = get_bloginfo( 'description' );
				break;
			case 'current_year':
				$title = gmdate( 'Y' );
				break;
			case 'current_date':
				$title = gmdate( get_option( 'date_format' ) );
				break;
			case 'author':
				$title = get_the_author();
				break;
			case 'woo_product':
				if ( class_exists( 'WooCommerce' ) ) {
					global $product;
					if ( $product ) {
						$title = $product->get_name();
					}
				}
				break;
			default:
				$title = ! empty( $settings['title'] ) ? $settings['title'] : '';
				break;
		}

		if ( empty( $title ) && 'custom' !== $source && ! empty( $settings['fallback_text'] ) ) {
			$title = $settings['fallback_text'];
		}
		
		return $title;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'elonix-advanced-heading-wrapper' );
		
		// Accessibility
		if ( ! empty( $settings['aria_label'] ) ) {
			$this->add_render_attribute( 'wrapper', 'aria-label', esc_attr( $settings['aria_label'] ) );
		}
		if ( ! empty( $settings['heading_role'] ) ) {
			$this->add_render_attribute( 'wrapper', 'role', esc_attr( $settings['heading_role'] ) );
		}

		// Watermark
		if ( ! empty( $settings['watermark_text'] ) ) {
			$this->add_render_attribute( 'watermark', 'class', 'elonix-heading-watermark' );
			echo '<div class="elonix-heading-watermark" aria-hidden="true" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:-1; pointer-events:none;">' . esc_html( $settings['watermark_text'] ) . '</div>';
		}

		// Main Title Parts
		$main_title = $this->get_dynamic_title();
		$prefix     = ! empty( $settings['title_prefix'] ) ? $settings['title_prefix'] : '';
		$highlight  = ! empty( $settings['title_highlight'] ) ? $settings['title_highlight'] : '';
		$suffix     = ! empty( $settings['title_suffix'] ) ? $settings['title_suffix'] : '';
		
		$this->add_render_attribute( 'title_tag', 'class', 'elonix-heading-title' );
		
		if ( ! empty( $settings['decoration_type'] ) && 'none' !== $settings['decoration_type'] ) {
			$this->add_render_attribute( 'title_tag', 'class', 'has-decoration' );
			$this->add_render_attribute( 'title_tag', 'class', 'decoration-' . esc_attr( $settings['decoration_type'] ) );
			$this->add_render_attribute( 'title_main_span', 'style', 'position:relative; display:inline-block;' );
		}

		// Title tag validation
		$title_tag    = ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h2';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
			$title_tag = 'h2';
		}

		// Link
		$is_link = false;
		if ( ! empty( $settings['heading_link']['url'] ) ) {
			$is_link = true;
			$this->add_link_attributes( 'heading_link', $settings['heading_link'] );
			$this->add_render_attribute( 'heading_link', 'class', 'elonix-heading-link' );
			$this->add_render_attribute( 'heading_link', 'style', 'text-decoration:none; color:inherit; display:block;' );
		}
		
		do_action( 'elonix/heading/before_render', $this, $settings );

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?> style="position:relative; z-index:1;">
			
			<?php 
			// Legacy Backward Compatibility: Subtitle
			if ( ! empty( $settings['subtitle'] ) ) : 
				$this->add_render_attribute( 'subtitle', 'class', 'elonix-heading-subtitle' );
			?>
				<div <?php $this->print_render_attribute_string( 'subtitle' ); ?>>
					<?php echo wp_kses_post( $settings['subtitle'] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $main_title ) || ! empty( $prefix ) || ! empty( $highlight ) || ! empty( $suffix ) ) : ?>
				
				<?php do_action( 'elonix/heading/before_title', $this, $settings ); ?>
				
				<<?php echo esc_attr( $title_tag ); ?> <?php $this->print_render_attribute_string( 'title_tag' ); ?>>
					
					<?php if ( $is_link ) : ?>
						<a <?php $this->print_render_attribute_string( 'heading_link' ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $prefix ) ) : ?>
						<span class="elonix-heading-title-prefix"><?php echo wp_kses_post( $prefix ); ?></span>
					<?php endif; ?>

					<?php if ( ! empty( $main_title ) ) : ?>
						<span class="elonix-heading-title-main" <?php $this->print_render_attribute_string( 'title_main_span' ); ?>>
							<?php echo wp_kses_post( $main_title ); ?>
							
							<?php if ( ! empty( $settings['decoration_type'] ) && 'none' !== $settings['decoration_type'] ) : ?>
								<span class="elonix-heading-decoration" style="position:absolute; content:''; display:block; bottom:0; left:0; z-index:-1;"></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>
					
					<?php if ( ! empty( $highlight ) ) : ?>
						<span class="elonix-heading-title-highlight"><?php echo wp_kses_post( $highlight ); ?></span>
					<?php endif; ?>

					<?php if ( ! empty( $suffix ) ) : ?>
						<span class="elonix-heading-title-suffix"><?php echo wp_kses_post( $suffix ); ?></span>
					<?php endif; ?>

					<?php if ( $is_link ) : ?>
						</a>
					<?php endif; ?>
					
				</<?php echo esc_attr( $title_tag ); ?>>
				
				<?php do_action( 'elonix/heading/after_title', $this, $settings ); ?>
				
			<?php endif; ?>

			<?php 
			// Legacy Backward Compatibility: Description
			if ( ! empty( $settings['description'] ) ) : 
				$this->add_render_attribute( 'description', 'class', 'elonix-heading-description' );
			?>
				<div <?php $this->print_render_attribute_string( 'description' ); ?>>
					<?php echo wp_kses_post( $settings['description'] ); ?>
				</div>
			<?php endif; ?>
			
		</div>
		<?php
		
		do_action( 'elonix/heading/after_render', $this, $settings );
	}
}
