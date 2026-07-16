<?php
/**
 * Elonix – Toolkit for Elementor Post Comments Widget
 *
 * @package Elonix_Toolkit
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/class-renderer.php';

class Elonix_Toolkit_Post_Comments_Widget extends Elonix_Widget_Base {

	public static $has_rendered = false;

	public function get_name() {
		return 'tv-post-comments';
	}

	public function get_title() {
		return esc_html__( 'Post Comments', 'elonix' );
	}

	public function get_tv_widget_icon() {
		return 'eicon-comments';
	}

	public function get_tv_widget_keywords() {
		return array( 'post', 'comments', 'form', 'reply', 'review', 'tvkit' );
	}

	public function get_script_depends() {
		return [ 'elonix-widget-tv-post-comments', 'comment-reply' ];
	}

	protected function register_controls() {

		// ----------------------------------------------------------------------
		// CONTENT TAB
		// ----------------------------------------------------------------------

		$this->start_controls_section('section_comments_content', ['label' => esc_html__('Comments List', 'elonix')]);
		
		$this->add_control('show_comments', ['label' => esc_html__('Show Comments', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
		$this->add_control('show_comment_count', ['label' => esc_html__('Show Comment Count', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['show_comments' => 'yes']]);
		$this->add_control('show_avatar', ['label' => esc_html__('Show Avatar', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['show_comments' => 'yes'], 'selectors_dictionary' => ['' => 'display: none;'], 'selectors' => ['{{WRAPPER}} .tv-comment-avatar' => '{{VALUE}}']]);
		$this->add_control('show_date', ['label' => esc_html__('Show Date', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['show_comments' => 'yes'], 'selectors_dictionary' => ['' => 'display: none;'], 'selectors' => ['{{WRAPPER}} .tv-comment-meta' => '{{VALUE}}']]);
		$this->add_control('show_reply_button', ['label' => esc_html__('Show Reply Button', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['show_comments' => 'yes'], 'selectors_dictionary' => ['' => 'display: none;'], 'selectors' => ['{{WRAPPER}} .tv-comment-reply' => '{{VALUE}}']]);
		$this->add_control('show_pagination', ['label' => esc_html__('Show Pagination', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['show_comments' => 'yes']]);
		$this->add_control('comments_per_page', ['label' => esc_html__('Comments Per Page', 'elonix'), 'type' => Controls_Manager::NUMBER, 'default' => '', 'description' => esc_html__('Leave empty to use WordPress default settings.', 'elonix'), 'condition' => ['show_pagination' => 'yes', 'show_comments' => 'yes']]);
		$this->add_control('empty_comments_message', ['label' => esc_html__('Empty Message', 'elonix'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('No comments yet.', 'elonix'), 'condition' => ['show_comments' => 'yes']]);
		
		$this->end_controls_section();

		$this->start_controls_section('section_form_content', ['label' => esc_html__('Comment Form', 'elonix')]);
		
		$this->add_control('show_comment_form', ['label' => esc_html__('Show Form', 'elonix'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
		$this->add_control('reply_button_text', ['label' => esc_html__('Form Title', 'elonix'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Leave a Reply', 'elonix'), 'condition' => ['show_comment_form' => 'yes']]);
		$this->add_control('submit_button_text', ['label' => esc_html__('Submit Button', 'elonix'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Post Comment', 'elonix'), 'condition' => ['show_comment_form' => 'yes']]);
		$this->add_control('cancel_reply_text', ['label' => esc_html__('Cancel Reply Text', 'elonix'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Cancel Reply', 'elonix'), 'condition' => ['show_comment_form' => 'yes']]);
		
		$this->end_controls_section();

		// ----------------------------------------------------------------------
		// STYLE TAB
		// ----------------------------------------------------------------------

		// 1. Comment Card
		$this->start_controls_section('section_style_card', ['label' => esc_html__('Comment Card', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->start_controls_tabs('tabs_card_style');
		$this->start_controls_tab('tab_card_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_control('card_bg_color', ['label' => esc_html__('Background Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'background-color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'card_border', 'selector' => '{{WRAPPER}} .tv-comment-card', 'fields_options' => ['border' => ['default' => 'solid'], 'width' => ['default' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1]], 'color' => ['default' => '#f2f2f2']]]);
		$this->add_responsive_control('card_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'card_shadow', 'selector' => '{{WRAPPER}} .tv-comment-card', 'fields_options' => ['box_shadow_type' => ['default' => 'yes'], 'box_shadow' => ['default' => ['horizontal' => 0, 'vertical' => 10, 'blur' => 25, 'spread' => 0, 'color' => 'rgba(0,0,0,0.06)']] ]]);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_card_hover', ['label' => esc_html__('Hover', 'elonix')]);
		$this->add_control('card_bg_color_hover', ['label' => esc_html__('Background Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .tv-comment-card:hover' => 'background-color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'card_border_hover', 'selector' => '{{WRAPPER}} .tv-comment-card:hover']);
		$this->add_responsive_control('card_radius_hover', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .tv-comment-card:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'card_shadow_hover', 'selector' => '{{WRAPPER}} .tv-comment-card:hover']);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		
		$this->add_responsive_control('card_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'separator' => 'before', 'default' => ['top' => 30, 'right' => 30, 'bottom' => 30, 'left' => 30, 'unit' => 'px'], 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('card_margin', ['label' => esc_html__('Comment Gap', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 25], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
		$this->add_control('card_transition', ['label' => esc_html__('Transition Duration', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.3], 'range' => ['px' => ['min' => 0, 'max' => 3, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'transition: all {{SIZE}}s ease;']]);
		
		$this->end_controls_section();

		// 2. Avatar
		$this->start_controls_section('section_style_avatar', ['label' => esc_html__('Avatar', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['show_avatar' => 'yes']]);
		
		$this->add_responsive_control('avatar_size', ['label' => esc_html__('Size', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 50], 'range' => ['px' => ['min' => 20, 'max' => 120]], 'selectors' => ['{{WRAPPER}} .tv-comment-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .tv-comment-avatar' => 'flex-basis: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('avatar_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%'], 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('avatar_margin', ['label' => esc_html__('Margin', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-avatar' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('avatar_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-avatar img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'avatar_border', 'selector' => '{{WRAPPER}} .tv-comment-avatar img']);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'avatar_shadow', 'selector' => '{{WRAPPER}} .tv-comment-avatar img']);
		$this->add_responsive_control('avatar_gap', ['label' => esc_html__('Spacing', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 20], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'gap: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('avatar_alignment', ['label' => esc_html__('Vertical Alignment', 'elonix'), 'type' => Controls_Manager::CHOOSE, 'default' => 'flex-start', 'options' => ['flex-start' => ['title' => 'Top', 'icon' => 'eicon-v-align-top'], 'center' => ['title' => 'Center', 'icon' => 'eicon-v-align-middle']], 'selectors' => ['{{WRAPPER}} .tv-comment-card' => 'align-items: {{VALUE}};']]);
		
		$this->end_controls_section();

		// Comment Header
		$this->start_controls_section('section_style_comment_header', ['label' => esc_html__('Comment Header', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_responsive_control('header_alignment', ['label' => esc_html__('Vertical Alignment', 'elonix'), 'type' => Controls_Manager::CHOOSE, 'default' => 'flex-start', 'options' => ['flex-start' => ['title' => esc_html__('Top', 'elonix'), 'icon' => 'eicon-v-align-top'], 'center' => ['title' => esc_html__('Center', 'elonix'), 'icon' => 'eicon-v-align-middle'], 'flex-end' => ['title' => esc_html__('Bottom', 'elonix'), 'icon' => 'eicon-v-align-bottom']], 'selectors' => ['{{WRAPPER}} .tv-comment-header' => 'align-items: {{VALUE}};']]);
		
		$this->add_responsive_control('header_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		
		$this->add_responsive_control('header_margin', ['label' => esc_html__('Margin Bottom', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'selectors' => ['{{WRAPPER}} .tv-comment-header' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
		
		$this->add_control('header_bg_color', ['label' => esc_html__('Background Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .tv-comment-header' => 'background-color: {{VALUE}};']]);
		
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'header_border', 'selector' => '{{WRAPPER}} .tv-comment-header']);
		
		$this->end_controls_section();

		// 3. Author
		$this->start_controls_section('section_style_author', ['label' => esc_html__('Author', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'author_typography', 'selector' => '{{WRAPPER}} .tv-comment-author', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_weight' => ['default' => '700'], 'font_size' => ['default' => ['size' => 16, 'unit' => 'px']]]]);
		$this->add_control('author_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => ['{{WRAPPER}} .tv-comment-author .fn, {{WRAPPER}} .tv-comment-author .fn a' => 'color: {{VALUE}};']]);
		$this->add_control('author_color_hover', ['label' => esc_html__('Hover Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .tv-comment-author .fn a:hover' => 'color: {{VALUE}};']]);
		$this->add_responsive_control('author_spacing', ['label' => esc_html__('Spacing', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 4], 'selectors' => ['{{WRAPPER}} .tv-comment-author' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        
		$this->end_controls_section();

		// 4. Meta
		$this->start_controls_section('section_style_meta', ['label' => esc_html__('Meta', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->start_controls_tabs('tabs_meta_style');
		$this->start_controls_tab('tab_meta_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'meta_typography', 'selector' => '{{WRAPPER}} .tv-comment-meta, {{WRAPPER}} .tv-comment-meta a', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_size' => ['default' => ['size' => 13, 'unit' => 'px']]]]);
		$this->add_control('meta_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#777777', 'selectors' => ['{{WRAPPER}} .tv-comment-meta, {{WRAPPER}} .tv-comment-meta time' => 'color: {{VALUE}};']]);
		$this->add_control('meta_link_color', ['label' => esc_html__('Link Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#777777', 'selectors' => ['{{WRAPPER}} .tv-comment-meta a' => 'color: {{VALUE}};']]);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_meta_hover', ['label' => esc_html__('Hover', 'elonix')]);
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'meta_typography_hover', 'selector' => '{{WRAPPER}} .tv-comment-meta a:hover']);
		$this->add_control('meta_color_hover', ['label' => esc_html__('Hover Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => ['{{WRAPPER}} .tv-comment-meta a:hover' => 'color: {{VALUE}};']]);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control('meta_gap', ['label' => esc_html__('Gap', 'elonix'), 'type' => Controls_Manager::SLIDER, 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .tv-comment-meta' => 'gap: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('meta_alignment', ['label' => esc_html__('Alignment', 'elonix'), 'type' => Controls_Manager::CHOOSE, 'options' => ['flex-start' => ['title' => 'Left', 'icon' => 'eicon-text-align-left'], 'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'], 'flex-end' => ['title' => 'Right', 'icon' => 'eicon-text-align-right']], 'selectors' => ['{{WRAPPER}} .tv-comment-meta' => 'justify-content: {{VALUE}}; display: flex;']]);
        
		$this->end_controls_section();

		// 5. Comment Content
		$this->start_controls_section('section_style_content', ['label' => esc_html__('Comment Content', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .tv-comment-content', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_size' => ['default' => ['size' => 15, 'unit' => 'px']], 'line_height' => ['default' => ['size' => 1.6, 'unit' => 'em']]]]);
		$this->add_control('text_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#444444', 'selectors' => ['{{WRAPPER}} .tv-comment-content' => 'color: {{VALUE}};']]);
		$this->add_responsive_control('text_p_spacing', ['label' => esc_html__('Paragraph Spacing', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 15], 'selectors' => ['{{WRAPPER}} .tv-comment-content p' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('text_margin', ['label' => esc_html__('Margin Top', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 15], 'selectors' => ['{{WRAPPER}} .tv-comment-content' => 'margin-top: {{SIZE}}{{UNIT}};']]);
        
		$this->end_controls_section();

		// 6. Reply Button
		$this->start_controls_section('section_style_reply', ['label' => esc_html__('Reply Button', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'reply_typography', 'selector' => '{{WRAPPER}} .tv-comment-reply a', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_weight' => ['default' => '600'], 'font_size' => ['default' => ['size' => 13, 'unit' => 'px']]]]);
		$this->add_responsive_control('reply_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 8, 'right' => 20, 'bottom' => 8, 'left' => 20, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-reply a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('reply_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 30, 'right' => 30, 'bottom' => 30, 'left' => 30, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-reply a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

		$this->start_controls_tabs('tabs_reply_style');
		$this->start_controls_tab('tab_reply_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_control('reply_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#333333', 'selectors' => ['{{WRAPPER}} .tv-comment-reply a' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'reply_bg', 'selector' => '{{WRAPPER}} .tv-comment-reply a', 'fields_options' => ['background' => ['default' => 'classic'], 'color' => ['default' => '#f2f2f2']]]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'reply_border', 'selector' => '{{WRAPPER}} .tv-comment-reply a']);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_reply_hover', ['label' => esc_html__('Hover', 'elonix')]);
		$this->add_control('reply_color_hover', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .tv-comment-reply a:hover' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'reply_bg_hover', 'selector' => '{{WRAPPER}} .tv-comment-reply a:hover', 'fields_options' => ['background' => ['default' => 'classic'], 'color' => ['default' => '#111111']]]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'reply_border_hover', 'selector' => '{{WRAPPER}} .tv-comment-reply a:hover']);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control('reply_transition', ['label' => esc_html__('Transition Duration', 'elonix'), 'separator' => 'before', 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.3], 'range' => ['px' => ['min' => 0, 'max' => 3, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .tv-comment-reply a' => 'transition: all {{SIZE}}s ease;']]);
        
		$this->end_controls_section();

		// 7. Comment Form
		$this->start_controls_section('section_style_form_card', ['label' => esc_html__('Comment Form', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'form_card_bg', 'selector' => '{{WRAPPER}} .tv-comment-respond', 'fields_options' => ['background' => ['default' => 'classic'], 'color' => ['default' => '#ffffff']]]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'form_card_border', 'selector' => '{{WRAPPER}} .tv-comment-respond', 'fields_options' => ['border' => ['default' => 'solid'], 'width' => ['default' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1]], 'color' => ['default' => '#f2f2f2']]]);
		$this->add_responsive_control('form_card_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-respond' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('form_card_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-respond' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('form_card_margin', ['label' => esc_html__('Margin Top', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 40], 'selectors' => ['{{WRAPPER}} .tv-comment-respond' => 'margin-top: {{SIZE}}{{UNIT}};']]);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'form_card_shadow', 'selector' => '{{WRAPPER}} .tv-comment-respond', 'fields_options' => ['box_shadow_type' => ['default' => 'yes'], 'box_shadow' => ['default' => ['horizontal' => 0, 'vertical' => 10, 'blur' => 25, 'spread' => 0, 'color' => 'rgba(0,0,0,0.06)']] ]]);
		
		$this->end_controls_section();

		// 8. Input Fields
		$this->start_controls_section('section_style_form_inputs', ['label' => esc_html__('Input Fields', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'input_typography', 'selector' => '{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]']);
		$this->add_control('input_color', ['label' => esc_html__('Text Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#333333', 'selectors' => ['{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]' => 'color: {{VALUE}};']]);
		$this->add_control('input_placeholder_color', ['label' => esc_html__('Placeholder Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#999999', 'selectors' => ['{{WRAPPER}} .tv-comment-form input::placeholder' => 'color: {{VALUE}};']]);
		$this->add_control('input_bg_color', ['label' => esc_html__('Background', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#f9f9f9', 'selectors' => ['{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]' => 'background-color: {{VALUE}};']]);
		
		$this->start_controls_tabs('tabs_input_states');
		$this->start_controls_tab('tab_input_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'input_border', 'selector' => '{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]', 'fields_options' => ['border' => ['default' => 'solid'], 'width' => ['default' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1]], 'color' => ['default' => '#eaeaea']]]);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_input_focus', ['label' => esc_html__('Focus', 'elonix')]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'input_border_focus', 'selector' => '{{WRAPPER}} .tv-comment-form input[type="text"]:focus, {{WRAPPER}} .tv-comment-form input[type="email"]:focus, {{WRAPPER}} .tv-comment-form input[type="url"]:focus']);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'input_shadow_focus', 'selector' => '{{WRAPPER}} .tv-comment-form input[type="text"]:focus, {{WRAPPER}} .tv-comment-form input[type="email"]:focus, {{WRAPPER}} .tv-comment-form input[type="url"]:focus']);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control('input_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'separator' => 'before', 'default' => ['top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('input_height', ['label' => esc_html__('Height', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 50], 'selectors' => ['{{WRAPPER}} .tv-comment-form input[type="text"], {{WRAPPER}} .tv-comment-form input[type="email"], {{WRAPPER}} .tv-comment-form input[type="url"]' => 'height: {{SIZE}}{{UNIT}}; padding: 0 20px;']]);
		$this->add_responsive_control('input_spacing', ['label' => esc_html__('Spacing (Gap)', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 15], 'selectors' => ['{{WRAPPER}} .tv-comment-form' => 'gap: {{SIZE}}{{UNIT}};']]);
        
		$this->end_controls_section();

		// 9. Textarea
		$this->start_controls_section('section_style_textarea', ['label' => esc_html__('Textarea', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'textarea_typography', 'selector' => '{{WRAPPER}} .tv-comment-form textarea']);
		$this->add_responsive_control('textarea_height', ['label' => esc_html__('Height', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 140], 'range' => ['px' => ['min' => 50, 'max' => 500]], 'selectors' => ['{{WRAPPER}} .tv-comment-form textarea' => 'height: {{SIZE}}{{UNIT}}; padding: 20px;']]);
		$this->add_control('textarea_bg_color', ['label' => esc_html__('Background', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#f9f9f9', 'selectors' => ['{{WRAPPER}} .tv-comment-form textarea' => 'background-color: {{VALUE}};']]);
		
        $this->start_controls_tabs('tabs_textarea_states');
		$this->start_controls_tab('tab_textarea_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'textarea_border', 'selector' => '{{WRAPPER}} .tv-comment-form textarea', 'fields_options' => ['border' => ['default' => 'solid'], 'width' => ['default' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1]], 'color' => ['default' => '#eaeaea']]]);
		$this->end_controls_tab();
		
		$this->start_controls_tab('tab_textarea_focus', ['label' => esc_html__('Focus', 'elonix')]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'textarea_border_focus', 'selector' => '{{WRAPPER}} .tv-comment-form textarea:focus']);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control('textarea_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'separator' => 'before', 'default' => ['top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-comment-form textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        
		$this->end_controls_section();

		// 10. Submit Button
		$this->start_controls_section('section_style_submit', ['label' => esc_html__('Submit Button', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'submit_typography', 'selector' => '{{WRAPPER}} .tv-submit-btn', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_weight' => ['default' => '600']]]);
		$this->add_responsive_control('submit_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 16, 'right' => 35, 'bottom' => 16, 'left' => 35, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('submit_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px'], 'selectors' => ['{{WRAPPER}} .tv-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		
		$this->start_controls_tabs('tabs_submit_style');
		$this->start_controls_tab('tab_submit_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_control('submit_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .tv-submit-btn' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'submit_bg', 'selector' => '{{WRAPPER}} .tv-submit-btn', 'fields_options' => ['background' => ['default' => 'classic'], 'color' => ['default' => '#111111']]]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'submit_border', 'selector' => '{{WRAPPER}} .tv-submit-btn']);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_submit_hover', ['label' => esc_html__('Hover', 'elonix')]);
		$this->add_control('submit_color_hover', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .tv-submit-btn:hover' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'submit_bg_hover', 'selector' => '{{WRAPPER}} .tv-submit-btn:hover', 'fields_options' => ['background' => ['default' => 'classic'], 'color' => ['default' => '#333333']]]);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'submit_border_hover', 'selector' => '{{WRAPPER}} .tv-submit-btn:hover']);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control('submit_transition', ['label' => esc_html__('Transition Duration', 'elonix'), 'separator' => 'before', 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.3], 'range' => ['px' => ['min' => 0, 'max' => 3, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .tv-submit-btn' => 'transition: all {{SIZE}}s ease;']]);
		$this->add_responsive_control('submit_width', ['label' => esc_html__('Width', 'elonix'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .tv-submit-btn' => 'width: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('submit_alignment', ['label' => esc_html__('Alignment', 'elonix'), 'type' => Controls_Manager::CHOOSE, 'default' => 'flex-start', 'options' => ['flex-start' => ['title' => 'Left', 'icon' => 'eicon-text-align-left'], 'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'], 'flex-end' => ['title' => 'Right', 'icon' => 'eicon-text-align-right'], 'stretch' => ['title' => 'Justified', 'icon' => 'eicon-text-align-justify']], 'selectors' => ['{{WRAPPER}} .form-submit' => 'text-align: {{VALUE}};']]);
        
		$this->end_controls_section();

		// 11. Comments Title
		$this->start_controls_section('section_style_title', ['label' => esc_html__('Comments Title', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'form_title_typography', 'selector' => '{{WRAPPER}} .comment-reply-title, {{WRAPPER}} .comments-title', 'fields_options' => ['typography' => ['default' => 'custom'], 'font_size' => ['default' => ['size' => 24, 'unit' => 'px']], 'font_weight' => ['default' => '700']]]);
		$this->add_control('form_title_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => ['{{WRAPPER}} .comment-reply-title, {{WRAPPER}} .comments-title' => 'color: {{VALUE}};']]);
		$this->add_responsive_control('form_title_margin', ['label' => esc_html__('Margin Bottom', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 25], 'selectors' => ['{{WRAPPER}} .comment-reply-title, {{WRAPPER}} .comments-title' => 'margin-bottom: {{SIZE}}{{UNIT}}; display: block;']]);
		
		$this->end_controls_section();
		
		// 12. Pagination
		$this->start_controls_section('section_style_pagination', ['label' => esc_html__('Pagination', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'pagination_typography', 'selector' => '{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span']);
		$this->add_responsive_control('pagination_spacing', ['label' => esc_html__('Margin Top', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 30], 'selectors' => ['{{WRAPPER}} .tv-comment-navigation' => 'margin-top: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('pagination_gap', ['label' => esc_html__('Gap', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'selectors' => ['{{WRAPPER}} .tv-comment-navigation' => 'gap: {{SIZE}}{{UNIT}}; display: flex; flex-wrap: wrap;']]);
		$this->add_responsive_control('pagination_alignment', ['label' => esc_html__('Alignment', 'elonix'), 'type' => Controls_Manager::CHOOSE, 'options' => ['flex-start' => ['title' => 'Left', 'icon' => 'eicon-text-align-left'], 'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'], 'flex-end' => ['title' => 'Right', 'icon' => 'eicon-text-align-right']], 'selectors' => ['{{WRAPPER}} .tv-comment-navigation' => 'justify-content: {{VALUE}};']]);
		$this->add_responsive_control('pagination_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('pagination_radius', ['label' => esc_html__('Border Radius', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		
		$this->start_controls_tabs('tabs_pagination_style');
		$this->start_controls_tab('tab_pagination_normal', ['label' => esc_html__('Normal', 'elonix')]);
		$this->add_control('pagination_color', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'pagination_bg', 'selector' => '{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span']);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'pagination_border', 'selector' => '{{WRAPPER}} .tv-comment-navigation a, {{WRAPPER}} .tv-comment-navigation span']);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_pagination_hover', ['label' => esc_html__('Hover / Active', 'elonix')]);
		$this->add_control('pagination_color_hover', ['label' => esc_html__('Color', 'elonix'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .tv-comment-navigation a:hover, {{WRAPPER}} .tv-comment-navigation span.current' => 'color: {{VALUE}};']]);
		$this->add_group_control(Group_Control_Background::get_type(), ['name' => 'pagination_bg_hover', 'selector' => '{{WRAPPER}} .tv-comment-navigation a:hover, {{WRAPPER}} .tv-comment-navigation span.current']);
		$this->add_group_control(Group_Control_Border::get_type(), ['name' => 'pagination_border_hover', 'selector' => '{{WRAPPER}} .tv-comment-navigation a:hover, {{WRAPPER}} .tv-comment-navigation span.current']);
		$this->end_controls_tab();
		$this->end_controls_tabs();
        
		$this->end_controls_section();

		// 13. Nested Comments
		$this->start_controls_section('section_style_nested', ['label' => esc_html__('Nested Comments', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_responsive_control('nested_indent', ['label' => esc_html__('Nested Indent', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 50], 'selectors' => ['{{WRAPPER}} .tv-comment-list .children' => 'padding-left: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('nested_margin', ['label' => esc_html__('Nested Gap', 'elonix'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 25], 'selectors' => ['{{WRAPPER}} .tv-comment-list .children' => 'margin-top: {{SIZE}}{{UNIT}};']]);
		
		$this->end_controls_section();

		// 14. Animations
		$this->start_controls_section('section_style_animations', ['label' => esc_html__('Animations', 'elonix'), 'tab' => Controls_Manager::TAB_STYLE]);
		
		$this->add_control('anim_type', ['label' => esc_html__('New Comment Animation', 'elonix'), 'type' => Controls_Manager::SELECT, 'default' => 'fade', 'options' => ['none' => esc_html__('None', 'elonix'), 'fade' => esc_html__('Fade In', 'elonix'), 'slide_up' => esc_html__('Slide Up', 'elonix'), 'scale' => esc_html__('Scale In', 'elonix')]]);
		$this->add_control('anim_duration', ['label' => esc_html__('Transition Duration', 'elonix'), 'type' => Controls_Manager::SELECT, 'default' => '0.4s', 'options' => ['0.2s' => 'Fast (0.2s)', '0.4s' => 'Normal (0.4s)', '0.8s' => 'Slow (0.8s)'], 'condition' => ['anim_type!' => 'none']]);
		
		$this->add_control('scroll_after_ajax', ['label' => esc_html__('After AJAX Update', 'elonix'), 'type' => Controls_Manager::SELECT, 'default' => 'smooth', 'separator' => 'before', 'options' => ['none' => esc_html__('No Scroll', 'elonix'), 'top' => esc_html__('Scroll to Top', 'elonix'), 'list' => esc_html__('Scroll to Comment List', 'elonix'), 'smooth' => esc_html__('Smooth Scroll', 'elonix')]]);
		
		$this->end_controls_section();

		// 15. CSS Box (Advanced)
		$this->start_controls_section('section_css_box', ['label' => esc_html__('CSS Box', 'elonix'), 'tab' => Controls_Manager::TAB_ADVANCED]);
		
		$this->add_responsive_control('css_display', ['label' => esc_html__('Display', 'elonix'), 'type' => Controls_Manager::SELECT, 'options' => ['' => 'Default', 'block' => 'Block', 'inline-block' => 'Inline Block', 'flex' => 'Flex', 'grid' => 'Grid', 'none' => 'None'], 'selectors' => ['{{WRAPPER}} .tv-comments-area' => 'display: {{VALUE}};']]);
		$this->add_responsive_control('css_position', ['label' => esc_html__('Position', 'elonix'), 'type' => Controls_Manager::SELECT, 'options' => ['' => 'Default', 'static' => 'Static', 'relative' => 'Relative', 'absolute' => 'Absolute', 'fixed' => 'Fixed', 'sticky' => 'Sticky'], 'selectors' => ['{{WRAPPER}} .tv-comments-area' => 'position: {{VALUE}};']]);
		$this->add_responsive_control('css_width', ['label' => esc_html__('Width', 'elonix'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px', '%', 'vw'], 'selectors' => ['{{WRAPPER}} .tv-comments-area' => 'width: {{SIZE}}{{UNIT}};']]);
		$this->add_responsive_control('css_padding', ['label' => esc_html__('Padding', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comments-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_responsive_control('css_margin', ['label' => esc_html__('Margin', 'elonix'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .tv-comments-area' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		
		$this->end_controls_section();

		// 16. Custom CSS (Advanced)
		$this->start_controls_section('section_custom_css', ['label' => esc_html__('Custom CSS', 'elonix'), 'tab' => Controls_Manager::TAB_ADVANCED]);
		
		$this->add_control('custom_css', ['type' => Controls_Manager::CODE, 'language' => 'css', 'render_type' => 'ui', 'description' => esc_html__('Use "selector" to target the wrapper element. Example: selector { color: red; }', 'elonix')]);
		
		$this->end_controls_section();
	}

	protected function render() {
		if ( self::$has_rendered ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Multiple Post Comments widgets are not supported on the same page.', 'elonix' ) . '</div>';
			}
			return;
		}
		self::$has_rendered = true;

		$settings = $this->get_settings_for_display();
		if ( ! get_the_ID() ) return;
		
		$is_editor = Plugin::$instance->editor->is_edit_mode();
		
		// Render using the decoupled service layer
		echo Elonix_Toolkit_Post_Comments_Renderer::render_comments_area( $settings, $is_editor ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
