<?php
/**
 * Elonix – Toolkit for Elementor Advanced Post Title Widget
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

class Elonix_Toolkit_Post_Title_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-post-title';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Title', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-post-title';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'post', 'title', 'heading', 'seo', 'dynamic' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-post-title' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Tab - General Settings
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'General Settings', 'elonix' ),
			)
		);

		$this->add_control(
			'title_source',
			array(
				'label'   => esc_html__( 'Title Source', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current' => esc_html__( 'Current Post / Page Title', 'elonix' ),
					'custom'  => esc_html__( 'Custom Title (Dynamic tag supported)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'custom_title',
			array(
				'label'       => esc_html__( 'Title Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => esc_html__( 'Your Post Title', 'elonix' ),
				'placeholder' => esc_html__( 'Enter custom title text', 'elonix' ),
				'condition'   => array(
					'title_source' => 'custom',
				),
			)
		);

		$this->add_control(
			'fallback_text',
			array(
				'label'       => esc_html__( 'Fallback Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Untitled Post', 'elonix' ),
				'placeholder' => esc_html__( 'Fallback if title empty', 'elonix' ),
			)
		);

		$this->add_control(
			'word_count',
			array(
				'label'       => esc_html__( 'Word Count Limit', 'elonix' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 0, // 0 means no limit
				),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'description' => esc_html__( 'Set to 0 to show the full title.', 'elonix' ),
			)
		);

		$this->add_control(
			'word_count_suffix',
			array(
				'label'       => esc_html__( 'Truncation Suffix', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '...',
				'placeholder' => esc_html__( 'e.g. ...', 'elonix' ),
				'condition'   => array(
					'word_count[size]!' => 0,
				),
			)
		);

		$this->add_control(
			'html_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto' => esc_html__( 'Auto Detect (Recommended)', 'elonix' ),
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
			)
		);

		$this->add_control(
			'link_to',
			array(
				'label'   => esc_html__( 'Link To', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'post'   => esc_html__( 'Post Permalink', 'elonix' ),
					'home'   => esc_html__( 'Home URL', 'elonix' ),
					'custom' => esc_html__( 'Custom URL', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'custom_link',
			array(
				'label'       => esc_html__( 'URL Link', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'condition'   => array(
					'link_to' => 'custom',
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
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-container' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// Content Tab - Title Decoration
		$this->start_controls_section(
			'section_decoration',
			array(
				'label' => esc_html__( 'Title Decoration', 'elonix' ),
			)
		);

		$this->add_control(
			'before_text',
			array(
				'label'       => esc_html__( 'Before Title Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. Hot:', 'elonix' ),
			)
		);

		$this->add_control(
			'before_icon',
			array(
				'label'       => esc_html__( 'Before Title Icon', 'elonix' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
			)
		);

		$this->add_control(
			'after_text',
			array(
				'label'       => esc_html__( 'After Title Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. [New]', 'elonix' ),
			)
		);

		$this->add_control(
			'after_icon',
			array(
				'label'       => esc_html__( 'After Title Icon', 'elonix' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
			)
		);

		$this->add_control(
			'enable_separator',
			array(
				'label'        => esc_html__( 'Enable Under-title Separator', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'enable_badge',
			array(
				'label'        => esc_html__( 'Enable Title Badge Label', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'       => esc_html__( 'Badge Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Popular', 'elonix' ),
				'placeholder' => esc_html__( 'Popular', 'elonix' ),
				'condition'   => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Content Tab - Scroll/Reveal Animation Settings
		$this->start_controls_section(
			'section_scroll_effects',
			array(
				'label' => esc_html__( 'Reveal Scroll Effects', 'elonix' ),
			)
		);

		$this->add_control(
			'reveal_animation',
			array(
				'label'   => esc_html__( 'Reveal Animation', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'       => esc_html__( 'None', 'elonix' ),
					'fade'       => esc_html__( 'Fade In', 'elonix' ),
					'slide'      => esc_html__( 'Slide In Up', 'elonix' ),
					'split-char' => esc_html__( 'Split Character Reveal (Staggered)', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Heading Text Options
		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => esc_html__( 'Title Typography', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-post-title-heading',
			)
		);

		$this->start_controls_tabs( 'title_color_tabs' );

		$this->start_controls_tab(
			'title_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-post-title-heading a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'title_background',
				'label'    => esc_html__( 'Container Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .elonix-post-title-container',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-container:hover .elonix-post-title-heading' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-post-title-container:hover .elonix-post-title-heading a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'text_stroke_width',
			array(
				'label'     => esc_html__( 'Text Stroke Width (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 0,
				),
				'range'     => array(
					'px' => array(
						'max' => 5,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'text_stroke_color',
			array(
				'label'     => esc_html__( 'Text Stroke Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading' => '-webkit-text-stroke-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => array(
					'normal'      => esc_html__( 'Normal', 'elonix' ),
					'multiply'    => esc_html__( 'Multiply', 'elonix' ),
					'screen'      => esc_html__( 'Screen', 'elonix' ),
					'overlay'     => esc_html__( 'Overlay', 'elonix' ),
					'darken'      => esc_html__( 'Darken', 'elonix' ),
					'lighten'     => esc_html__( 'Lighten', 'elonix' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'elonix' ),
					'saturation'  => esc_html__( 'Saturation', 'elonix' ),
					'color'       => esc_html__( 'Color', 'elonix' ),
					'difference'  => esc_html__( 'Difference', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading' => 'mix-blend-mode: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-post-title-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-post-title-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Advanced Effects Styling
		$this->start_controls_section(
			'section_advanced_effects',
			array(
				'label' => esc_html__( 'Advanced Title Effects', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Gradient Text Effect
		$this->add_control(
			'enable_gradient_text',
			array(
				'label'        => esc_html__( 'Enable Gradient Text', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'text_gradient_color_first',
			array(
				'label'     => esc_html__( 'First Gradient Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading.gradient-effect' => 'background: linear-gradient(to right, {{VALUE}} 0%, {{text_gradient_color_second.VALUE}} 100%); background-clip: text; -webkit-background-clip: text;',
				),
				'condition' => array(
					'enable_gradient_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'text_gradient_color_second',
			array(
				'label'     => esc_html__( 'Second Gradient Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9c27b0',
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-heading.gradient-effect' => 'background: linear-gradient(to right, {{text_gradient_color_first.VALUE}} 0%, {{VALUE}} 100%); background-clip: text; -webkit-background-clip: text;',
				),
				'condition' => array(
					'enable_gradient_text' => 'yes',
				),
			)
		);

		// Highlighted Text Marker
		$this->add_control(
			'enable_highlighted_text',
			array(
				'label'        => esc_html__( 'Enable Highlighter Marker', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'highlight_text_match',
			array(
				'label'       => esc_html__( 'Text snippet to Highlight', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Type exact word/phrase', 'elonix' ),
				'condition'   => array(
					'enable_highlighted_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'highlight_style',
			array(
				'label'     => esc_html__( 'Highlighter Style', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'marker',
				'options'   => array(
					'marker'           => esc_html__( 'Felt Marker Overlay', 'elonix' ),
					'sketch-underline' => esc_html__( 'Double Sketch Underline', 'elonix' ),
					'full-bg'          => esc_html__( 'Full Highlight Box', 'elonix' ),
				),
				'condition' => array(
					'enable_highlighted_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'highlight_color',
			array(
				'label'     => esc_html__( 'Marker Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff59d',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-highlight-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_highlighted_text' => 'yes',
				),
			)
		);

		// Split Text Options
		$this->add_control(
			'enable_split_text',
			array(
				'label'        => esc_html__( 'Enable Split Text Styles', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'split_type',
			array(
				'label'     => esc_html__( 'Split By', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'first_word',
				'options'   => array(
					'first_word' => esc_html__( 'First Word', 'elonix' ),
					'last_word'  => esc_html__( 'Last Word', 'elonix' ),
					'char'       => esc_html__( 'Custom Separator Character', 'elonix' ),
				),
				'condition' => array(
					'enable_split_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'split_char',
			array(
				'label'       => esc_html__( 'Separator Character', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => ' ',
				'placeholder' => 'e.g. | or space',
				'condition'   => array(
					'enable_split_text' => 'yes',
					'split_type'        => 'char',
				),
			)
		);

		$this->add_control(
			'split_first_color',
			array(
				'label'     => esc_html__( 'First Half Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-split-first-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_split_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'split_first_weight',
			array(
				'label'     => esc_html__( 'First Half Weight', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inherit',
				'options'   => array(
					'inherit' => 'Default',
					'normal'  => 'Normal (400)',
					'600'     => 'Semi-Bold (600)',
					'bold'    => 'Bold (700)',
					'900'     => 'Black (900)',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-split-first-weight: {{VALUE}};',
				),
				'condition' => array(
					'enable_split_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'split_second_color',
			array(
				'label'     => esc_html__( 'Second Half Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-split-second-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_split_text' => 'yes',
				),
			)
		);

		$this->add_control(
			'split_second_weight',
			array(
				'label'     => esc_html__( 'Second Half Weight', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inherit',
				'options'   => array(
					'inherit' => 'Default',
					'normal'  => 'Normal (400)',
					'600'     => 'Semi-Bold (600)',
					'bold'    => 'Bold (700)',
					'900'     => 'Black (900)',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-split-second-weight: {{VALUE}};',
				),
				'condition' => array(
					'enable_split_text' => 'yes',
				),
			)
		);

		// Animated Underline
		$this->add_control(
			'enable_animated_underline',
			array(
				'label'        => esc_html__( 'Enable Animated Underline', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'underline_direction',
			array(
				'label'     => esc_html__( 'Expand Direction', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center',
				'options'   => array(
					'center' => esc_html__( 'Expand from Center', 'elonix' ),
					'left'   => esc_html__( 'Expand from Left', 'elonix' ),
					'right'  => esc_html__( 'Expand from Right', 'elonix' ),
				),
				'condition' => array(
					'enable_animated_underline' => 'yes',
				),
			)
		);

		$this->add_control(
			'underline_color',
			array(
				'label'     => esc_html__( 'Underline Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-underline-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_animated_underline' => 'yes',
				),
			)
		);

		$this->add_control(
			'underline_height',
			array(
				'label'     => esc_html__( 'Underline Height (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 2,
				),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-underline-height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'enable_animated_underline' => 'yes',
				),
			)
		);

		// Background Highlight box
		$this->add_control(
			'enable_bg_highlight',
			array(
				'label'        => esc_html__( 'Enable Title Container Highlight Box', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'default'      => 'no',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'bg_box_color',
			array(
				'label'     => esc_html__( 'Highlight Box Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.03)',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-box-bg-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_bg_highlight' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'bg_box_padding',
			array(
				'label'      => esc_html__( 'Box Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-title-box-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'enable_bg_highlight' => 'yes',
				),
			)
		);

		$this->add_control(
			'bg_box_border_radius',
			array(
				'label'      => esc_html__( 'Box Border Radius (px)', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--es-title-box-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'enable_bg_highlight' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Decorators Style
		$this->start_controls_section(
			'section_decorators_style',
			array(
				'label' => esc_html__( 'Decorators & Separator Style', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Icon / Meta text styling
		$this->add_control(
			'meta_decorator_color',
			array(
				'label'     => esc_html__( 'Before/After Meta Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-meta-before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-post-title-meta-before svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .elonix-post-title-meta-after' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-post-title-meta-after svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'meta_decorator_size',
			array(
				'label'     => esc_html__( 'Meta Size (em)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 0.85,
				),
				'range'     => array(
					'em' => array(
						'min'  => 0.5,
						'max'  => 2,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-title-meta-before' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-post-title-meta-after' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Badge styles
		$this->add_control(
			'badge_style_heading',
			array(
				'label'     => esc_html__( 'Badge styles', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Badge Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-badge-bg-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Badge Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-badge-text-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'badge_typography',
				'label'     => esc_html__( 'Badge Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .elonix-post-title-badge',
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		// Under-title Separator styles
		$this->add_control(
			'separator_style_heading',
			array(
				'label'     => esc_html__( 'Separator line styles', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'enable_separator' => 'yes',
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Separator Line Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e0e0e0',
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-sep-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_separator' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'separator_width',
			array(
				'label'     => esc_html__( 'Separator Width (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 50,
				),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 300,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-sep-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'enable_separator' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'separator_height',
			array(
				'label'     => esc_html__( 'Separator Thickness (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 3,
				),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 15,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--es-title-sep-height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'enable_separator' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Advanced Post Title frontend markup.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Fetch calculated title text from helper class
		$title = Elonix_Title_Helper::get_title( $settings );

		if ( empty( $title ) ) {
			return;
		}

		// Handle Word Count Limit
		if ( ! empty( $settings['word_count']['size'] ) && $settings['word_count']['size'] > 0 ) {
			$suffix = isset( $settings['word_count_suffix'] ) ? $settings['word_count_suffix'] : '...';
			$title  = wp_trim_words( $title, $settings['word_count']['size'], $suffix );
		}

		$title_html = esc_html( $title );

		// 1. Handle Split Text styling
		if ( 'yes' === $settings['enable_split_text'] ) {
			$split_type = $settings['split_type'];
			$split_char = ! empty( $settings['split_char'] ) ? $settings['split_char'] : ' ';

			if ( 'char' === $split_type ) {
				$parts = explode( $split_char, $title, 2 );
				if ( count( $parts ) === 2 ) {
					$title_html = '<span class="title-split-first">' . esc_html( $parts[0] ) . '</span>' . esc_html( $split_char ) . '<span class="title-split-second">' . esc_html( $parts[1] ) . '</span>';
				}
			} elseif ( 'first_word' === $split_type ) {
				$words = explode( ' ', $title );
				if ( count( $words ) > 1 ) {
					$first      = array_shift( $words );
					$rest       = implode( ' ', $words );
					$title_html = '<span class="title-split-first">' . esc_html( $first ) . '</span> <span class="title-split-second">' . esc_html( $rest ) . '</span>';
				}
			} elseif ( 'last_word' === $split_type ) {
				$words = explode( ' ', $title );
				if ( count( $words ) > 1 ) {
					$last       = array_pop( $words );
					$rest       = implode( ' ', $words );
					$title_html = '<span class="title-split-second">' . esc_html( $rest ) . '</span> <span class="title-split-first">' . esc_html( $last ) . '</span>';
				}
			}
		}
		// 2. Handle Highlighted Text marker
		elseif ( 'yes' === $settings['enable_highlighted_text'] ) {
			$highlight_style = $settings['highlight_style'];
			$highlight_text  = $settings['highlight_text_match'];

			if ( ! empty( $highlight_text ) && strpos( $title, $highlight_text ) !== false ) {
				$replace    = '<span class="elonix-title-highlight ' . esc_attr( $highlight_style ) . '">' . esc_html( $highlight_text ) . '</span>';
				$title_html = str_replace( $highlight_text, $replace, esc_html( $title ) );
			} else {
				// Highlight entire string if snippet matches nothing
				$title_html = '<span class="elonix-title-highlight ' . esc_attr( $highlight_style ) . '">' . esc_html( $title ) . '</span>';
			}
		}

		// 3. Handle Split Character Stagger Reveal animation
		if ( 'split-char' === $settings['reveal_animation'] ) {
			$plain_title = wp_strip_all_tags( $title );
			$words       = explode( ' ', $plain_title );
			$title_html  = '';
			$delay       = 0;

			foreach ( $words as $word ) {
				$title_html .= '<span class="reveal-word">';
				$chars       = preg_split( '//u', $word, -1, PREG_SPLIT_NO_EMPTY );
				foreach ( $chars as $char ) {
					$title_html .= '<span class="reveal-char" style="animation-delay: ' . $delay . 's;">' . esc_html( $char ) . '</span>';
					$delay      += 0.03;
				}
				$title_html .= '</span> ';
			}
		}

		// Resolve recommended heading HTML tags dynamically if set to 'auto'
		$tag = $settings['html_tag'];
		if ( 'auto' === $tag ) {
			$tag = Elonix_Title_Helper::get_recommended_html_tag();
		}
		$tag = \Elementor\Utils::validate_html_tag( $tag );

		// Set CSS classes for title effects
		$title_classes = array( 'elonix-post-title-heading' );
		if ( 'yes' === $settings['enable_gradient_text'] ) {
			$title_classes[] = 'gradient-effect';
		}
		if ( isset( $settings['text_stroke_width']['size'] ) && $settings['text_stroke_width']['size'] > 0 ) {
			$title_classes[] = 'stroke-effect';
		}
		if ( 'yes' === $settings['enable_animated_underline'] ) {
			$title_classes[] = 'underline-effect';
			$title_classes[] = 'underline-' . $settings['underline_direction'];
		}
		if ( 'yes' === $settings['enable_bg_highlight'] ) {
			$title_classes[] = 'bg-highlight-effect';
		}

		// Set container wrapper reveal animation class
		$container_classes = array( 'elonix-post-title-container' );
		if ( 'fade' === $settings['reveal_animation'] ) {
			$container_classes[] = 'elonix-reveal-fade';
		} elseif ( 'slide' === $settings['reveal_animation'] ) {
			$container_classes[] = 'elonix-reveal-slide';
		}

		// Resolve Link Wrapper
		$has_link = ( 'none' !== $settings['link_to'] );
		$link_url = '';
		if ( 'post' === $settings['link_to'] ) {
			$link_url = get_permalink();
		} elseif ( 'home' === $settings['link_to'] ) {
			$link_url = home_url( '/' );
		} elseif ( 'custom' === $settings['link_to'] && ! empty( $settings['custom_link']['url'] ) ) {
			$link_url = $settings['custom_link']['url'];
		}

		// RENDER HTML OUTPUT
		echo '<div class="' . esc_attr( implode( ' ', $container_classes ) ) . '">';

		// H1 duplicate checking banner in editor
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() && 'h1' === $tag ) {
			?>
			<div class="elonix-seo-warning" style="display:none; color:#c7254e; background:#f9f2f4; border:1px solid #d9534f; padding:10px; margin:10px 0; border-radius:4px; font-size:13px; font-family:sans-serif;">
				<strong>SEO WARNING:</strong> Multiple &lt;h1&gt; tags detected on this page. For best SEO practices, only one H1 tag should exist per page. Consider changing this widget's HTML Tag to H2, H3 or "Auto Detect".
			</div>
			<?php
		}

		// Render Before Text and Icons
		if ( ! empty( $settings['before_text'] ) || ! empty( $settings['before_icon']['value'] ) ) {
			echo '<span class="elonix-post-title-meta-before">';
			if ( ! empty( $settings['before_icon']['value'] ) ) {
				Icons_Manager::render_icon( $settings['before_icon'], array( 'aria-hidden' => 'true' ) );
			}
			if ( ! empty( $settings['before_text'] ) ) {
				echo esc_html( $settings['before_text'] );
			}
			echo '</span>';
		}

		// Open Heading Tag
		echo '<' . esc_html( $tag ) . ' class="' . esc_attr( implode( ' ', $title_classes ) ) . '">';

		if ( $has_link && ! empty( $link_url ) ) {
			$target   = ( 'custom' === $settings['link_to'] && ! empty( $settings['custom_link']['is_external'] ) ) ? ' target="_blank"' : '';
			$nofollow = ( 'custom' === $settings['link_to'] && ! empty( $settings['custom_link']['nofollow'] ) ) ? ' rel="nofollow"' : '';
			echo '<a href="' . esc_url( $link_url ) . '"' . $target . $nofollow . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes sanitized.
		}

		echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-formatted markup.

		if ( $has_link && ! empty( $link_url ) ) {
			echo '</a>';
		}

		// Render Badge Label inside header tags if enabled
		if ( 'yes' === $settings['enable_badge'] && ! empty( $settings['badge_text'] ) ) {
			echo '<span class="elonix-post-title-badge">' . esc_html( $settings['badge_text'] ) . '</span>';
		}

		// Close Heading Tag
		echo '</' . esc_html( $tag ) . '>';

		// Render After Text and Icons
		if ( ! empty( $settings['after_text'] ) || ! empty( $settings['after_icon']['value'] ) ) {
			echo '<span class="elonix-post-title-meta-after">';
			if ( ! empty( $settings['after_text'] ) ) {
				echo esc_html( $settings['after_text'] );
			}
			if ( ! empty( $settings['after_icon']['value'] ) ) {
				Icons_Manager::render_icon( $settings['after_icon'], array( 'aria-hidden' => 'true' ) );
			}
			echo '</span>';
		}

		// Render Under-title Separator line
		if ( 'yes' === $settings['enable_separator'] ) {
			echo '<div class="elonix-post-title-separator" aria-hidden="true"></div>';
		}

		echo '</div>';
	}
}
