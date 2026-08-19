<?php
/**
 * Elonix – Toolkit for Elementor Post Excerpt Widget
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

class Elonix_Toolkit_Post_Excerpt_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-post-excerpt';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Excerpt', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-post-excerpt';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'post', 'excerpt', 'summary', 'read more', 'dynamic', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array();
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Tab - Excerpt Settings
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Excerpt Content', 'elonix' ),
			)
		);

		$this->add_control(
			'excerpt_source',
			array(
				'label'   => esc_html__( 'Excerpt Source', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wp_excerpt',
				'options' => array(
					'wp_excerpt'   => esc_html__( 'WordPress Excerpt', 'elonix' ),
					'custom_field' => esc_html__( 'Custom Field', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'custom_field_key',
			array(
				'label'       => esc_html__( 'Custom Field Key', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. my_custom_excerpt', 'elonix' ),
				'condition'   => array(
					'excerpt_source' => 'custom_field',
				),
			)
		);

		$this->add_control(
			'auto_generate',
			array(
				'label'       => esc_html__( 'Auto Generate from Content', 'elonix' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'elonix' ),
				'label_off'   => esc_html__( 'No', 'elonix' ),
				'default'     => 'yes',
				'description' => esc_html__( 'If the excerpt is empty, auto generate it from post content.', 'elonix' ),
				'condition'   => array(
					'excerpt_source' => 'wp_excerpt',
				),
			)
		);

		$this->add_control(
			'trim_type',
			array(
				'label'   => esc_html__( 'Trim Type', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'words',
				'options' => array(
					'words' => esc_html__( 'Words', 'elonix' ),
					'chars' => esc_html__( 'Characters', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'   => esc_html__( 'Limit Length', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 20,
				'min'     => 0,
			)
		);

		$this->add_control(
			'excerpt_suffix',
			array(
				'label'   => esc_html__( 'Truncation Suffix', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '...',
			)
		);

		$this->add_control(
			'strip_shortcodes',
			array(
				'label'     => esc_html__( 'Strip Shortcodes', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'elonix' ),
				'label_off' => esc_html__( 'No', 'elonix' ),
				'default'   => 'yes',
			)
		);

		$this->add_control(
			'strip_html',
			array(
				'label'     => esc_html__( 'Strip HTML', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'elonix' ),
				'label_off' => esc_html__( 'No', 'elonix' ),
				'default'   => 'yes',
			)
		);

		$this->add_control(
			'allow_basic_html',
			array(
				'label'       => esc_html__( 'Allow Basic HTML', 'elonix' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'elonix' ),
				'label_off'   => esc_html__( 'No', 'elonix' ),
				'default'     => 'no',
				'description' => esc_html__( 'Allow basic tags like <b>, <i>, <a>.', 'elonix' ),
				'condition'   => array(
					'strip_html' => 'no',
				),
			)
		);

		$this->add_control(
			'html_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'p',
				'options' => array(
					'p'   => 'p',
					'div' => 'div',
				),
			)
		);

		$this->add_control(
			'empty_state',
			array(
				'label'     => esc_html__( 'Empty State', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'hide',
				'options'   => array(
					'hide'             => esc_html__( 'Hide Widget', 'elonix' ),
					'show_placeholder' => esc_html__( 'Show Placeholder', 'elonix' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'placeholder_text',
			array(
				'label'     => esc_html__( 'Placeholder Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'No excerpt available.', 'elonix' ),
				'condition' => array(
					'empty_state' => 'show_placeholder',
				),
			)
		);

		$this->end_controls_section();

		// Content Tab - Read More Settings
		$this->start_controls_section(
			'section_read_more',
			array(
				'label' => esc_html__( 'Read More', 'elonix' ),
			)
		);

		$this->add_control(
			'show_read_more',
			array(
				'label'     => esc_html__( 'Enable Read More', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'elonix' ),
				'label_off' => esc_html__( 'No', 'elonix' ),
				'default'   => 'yes',
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'     => esc_html__( 'Read More Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More', 'elonix' ),
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'read_more_link_type',
			array(
				'label'     => esc_html__( 'Link To', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post',
				'options'   => array(
					'post'   => esc_html__( 'Post URL', 'elonix' ),
					'custom' => esc_html__( 'Custom URL', 'elonix' ),
				),
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'read_more_link_custom',
			array(
				'label'       => esc_html__( 'Custom URL', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'condition'   => array(
					'show_read_more'      => 'yes',
					'read_more_link_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'read_more_display',
			array(
				'label'     => esc_html__( 'Display Style', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => array(
					'inline'    => esc_html__( 'Inline', 'elonix' ),
					'block'     => esc_html__( 'Block', 'elonix' ),
					'button'    => esc_html__( 'Button', 'elonix' ),
					'text_link' => esc_html__( 'Text Link', 'elonix' ),
					'icon_only' => esc_html__( 'Icon Only', 'elonix' ),
				),
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'read_more_icon',
			array(
				'label'            => esc_html__( 'Read More Icon', 'elonix' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'condition'        => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'read_more_icon_position',
			array(
				'label'     => esc_html__( 'Icon Position', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'before' => esc_html__( 'Before Text', 'elonix' ),
					'after'  => esc_html__( 'After Text', 'elonix' ),
				),
				'condition' => array(
					'show_read_more'         => 'yes',
					'read_more_icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'read_more_hover_anim',
			array(
				'label'     => esc_html__( 'Hover Animation', 'elonix' ),
				'type'      => Controls_Manager::HOVER_ANIMATION,
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Excerpt Typography
		$this->start_controls_section(
			'section_style_excerpt',
			array(
				'label' => esc_html__( 'Excerpt Style', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'excerpt_align',
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
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-excerpt-wrapper' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-excerpt-content' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-post-excerpt-content',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'excerpt_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .elonix-post-excerpt-content',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'excerpt_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-post-excerpt-content',
			)
		);

		$this->add_responsive_control(
			'excerpt_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-post-excerpt-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'excerpt_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-post-excerpt-content',
			)
		);

		$this->add_responsive_control(
			'excerpt_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-post-excerpt-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'excerpt_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-post-excerpt-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'excerpt_spacing',
			array(
				'label'     => esc_html__( 'Spacing After Excerpt', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-post-excerpt-content' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Read More
		$this->start_controls_section(
			'section_style_read_more',
			array(
				'label'     => esc_html__( 'Read More Style', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'read_more_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-read-more',
			)
		);

		$this->start_controls_tabs( 'tabs_read_more_colors' );

		$this->start_controls_tab(
			'tab_read_more_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'read_more_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-read-more' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-read-more svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'read_more_background',
				'label'     => esc_html__( 'Background', 'elonix' ),
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .elonix-read-more',
				'condition' => array(
					'read_more_display!' => array( 'inline', 'text_link' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'read_more_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-read-more',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_read_more_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'read_more_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-read-more:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-read-more:hover svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'read_more_background_hover',
				'label'     => esc_html__( 'Background', 'elonix' ),
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .elonix-read-more:hover',
				'condition' => array(
					'read_more_display!' => array( 'inline', 'text_link' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'read_more_border_hover',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .elonix-read-more:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'read_more_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'read_more_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'read_more_display!' => array( 'inline' ),
				),
			)
		);

		$this->add_responsive_control(
			'read_more_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-read-more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'read_more_display!' => array( 'inline' ),
				),
			)
		);

		$this->add_responsive_control(
			'read_more_icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-read-more i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-read-more svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'read_more_icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'read_more_icon_gap',
			array(
				'label'     => esc_html__( 'Icon Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-read-more-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-read-more-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'read_more_icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		global $post;

		// The widget must work with current queried object (Post, Page, CPT, Archive, etc.)
		$post_id = get_the_ID();
		if ( ! $post_id && isset( $post->ID ) ) {
			$post_id = $post->ID;
		}

		if ( ! $post_id ) {
			return; // No post found
		}

		$excerpt = '';

		// 1. Fetch the Excerpt Source
		if ( 'custom_field' === $settings['excerpt_source'] && ! empty( $settings['custom_field_key'] ) ) {
			$excerpt = get_post_meta( $post_id, $settings['custom_field_key'], true );
		} elseif ( has_excerpt( $post_id ) ) {
				// Use explicit WordPress Excerpt
				$excerpt = get_the_excerpt( $post_id );
		} else {
			// WordPress Excerpt is empty.
			if ( 'yes' === $settings['auto_generate'] ) {
				$post_data = get_post( $post_id );
				if ( $post_data && ! empty( $post_data->post_content ) ) {
					$excerpt = $post_data->post_content;
				}
			}
		}

		// 2. Handle Empty State
		if ( empty( trim( wp_strip_all_tags( $excerpt ) ) ) ) {
			if ( 'show_placeholder' === $settings['empty_state'] && ! empty( $settings['placeholder_text'] ) ) {
				$excerpt = $settings['placeholder_text'];
			} else {
				return; // Output nothing (Hide Widget)
			}
		}

		// 3. Strip Rules
		if ( 'yes' === $settings['strip_shortcodes'] ) {
			$excerpt = strip_shortcodes( $excerpt );
		}

		if ( 'yes' === $settings['strip_html'] ) {
			$excerpt = wp_strip_all_tags( $excerpt );
		} elseif ( 'yes' === $settings['allow_basic_html'] ) {
			$excerpt = wp_strip_all_tags( $excerpt, '<a><b><i><strong><em><br><span><ul><ol><li>' );
		}

		// 4. Trim Rules
		$length = isset( $settings['excerpt_length'] ) ? intval( $settings['excerpt_length'] ) : 20;
		$suffix = isset( $settings['excerpt_suffix'] ) ? $settings['excerpt_suffix'] : '...';

		if ( $length > 0 ) {
			if ( 'words' === $settings['trim_type'] ) {
				$excerpt = wp_trim_words( $excerpt, $length, $suffix );
			} else {
				// Characters trim
				// Using wp_html_excerpt to preserve basic tags if not stripped
				$excerpt = wp_html_excerpt( $excerpt, $length, $suffix );
			}
		}

		// Resolve wrapper tag
		$html_tag = isset( $settings['html_tag'] ) ? $settings['html_tag'] : 'p';
		$html_tag = \Elementor\Utils::validate_html_tag( $html_tag );

		echo '<div class="elonix-post-excerpt-wrapper">';

		// We do NOT escape output here because users may choose to allow HTML.
		// Elementor standards: user text content should be output securely. Since it's from the post content/excerpt,
		// we rely on WP sanitization from save, plus our strip_tags logic above.
		// For ThemeForest, we can use wp_kses_post if strip_html is false.
		if ( 'yes' === $settings['strip_html'] ) {
			$excerpt_output = esc_html( $excerpt );
		} else {
			$excerpt_output = wp_kses_post( $excerpt );
		}

		echo '<' . $html_tag . ' class="elonix-post-excerpt-content">' . $excerpt_output . '</' . $html_tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $html_tag is validated via Elementor\Utils::validate_html_tag(), $excerpt_output via wp_kses_post().

		// Read More Link
		if ( 'yes' === $settings['show_read_more'] ) {
			$rm_link = '';
			if ( 'post' === $settings['read_more_link_type'] ) {
				$rm_link = get_permalink( $post_id );
			} elseif ( 'custom' === $settings['read_more_link_type'] && ! empty( $settings['read_more_link_custom']['url'] ) ) {
				$rm_link = $settings['read_more_link_custom']['url'];
			}

			if ( ! empty( $rm_link ) ) {
				$target   = ( 'custom' === $settings['read_more_link_type'] && ! empty( $settings['read_more_link_custom']['is_external'] ) ) ? ' target="_blank"' : '';
				$nofollow = ( 'custom' === $settings['read_more_link_type'] && ! empty( $settings['read_more_link_custom']['nofollow'] ) ) ? ' rel="nofollow"' : '';

				$rm_text = ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : '';

				$icon_html = '';
				if ( ! empty( $settings['read_more_icon']['value'] ) ) {
					ob_start();
					Icons_Manager::render_icon( $settings['read_more_icon'], array( 'aria-hidden' => 'true' ) );
					$icon_html = ob_get_clean();

					// Add gap class
					$icon_gap_class = ( 'before' === $settings['read_more_icon_position'] ) ? 'elonix-read-more-icon-before' : 'elonix-read-more-icon-after';
					$icon_html      = '<span class="' . esc_attr( $icon_gap_class ) . '">' . $icon_html . '</span>';
				}

				// Hover animation class
				$hover_class = '';
				if ( ! empty( $settings['read_more_hover_anim'] ) ) {
					$hover_class = ' elementor-animation-' . esc_attr( $settings['read_more_hover_anim'] );
				}

				$display_class = 'elonix-rm-display-' . esc_attr( $settings['read_more_display'] );
				if ( 'button' === $settings['read_more_display'] ) {
					$display_class .= ' elonix-btn elementor-button'; // basic elementor button classes
				}

				// Inline Read More adjustment: we can just output a space before it
				if ( 'inline' === $settings['read_more_display'] ) {
					echo ' ';
				}

				echo '<a href="' . esc_url( $rm_link ) . '" class="elonix-read-more ' . esc_attr( $display_class . $hover_class ) . '"' . $target . $nofollow . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $target/$nofollow are fixed static strings from a boolean ternary, no dynamic content.

				if ( 'icon_only' === $settings['read_more_display'] ) {
					// Icon only ignores text
					echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via Elementor's trusted Icons_Manager::render_icon() plus esc_attr() wrapper.
				} elseif ( 'before' === $settings['read_more_icon_position'] ) {
						echo $icon_html . ' <span class="elonix-rm-text">' . esc_html( $rm_text ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $icon_html built via Elementor's trusted Icons_Manager::render_icon().
				} else {
					echo '<span class="elonix-rm-text">' . esc_html( $rm_text ) . '</span> ' . $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $icon_html built via Elementor's trusted Icons_Manager::render_icon().

				}

				echo '</a>';
			}
		}

		echo '</div>';
	}
}
