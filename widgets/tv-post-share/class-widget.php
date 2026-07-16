<?php
/**
 * Elonix – Toolkit for Elementor Post Share Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_Share_Widget extends Elonix_Social_Base_Widget {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-post-share';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Share', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-share';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'post', 'share', 'social', 'facebook', 'twitter', 'linkedin', 'whatsapp', 'pinterest', 'reddit', 'telegram', 'email', 'copy', 'popup', 'modal', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-social-icons', 'elonix-widget-tv-post-share' );
	}

	/**
	 * Retrieve widget scripts handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-tv-social-icons', 'elonix-widget-tv-post-share' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - SHARE NETWORKS SECTION
		// ==========================================
		$this->start_controls_section(
			'section_share_networks',
			array(
				'label' => esc_html__( 'Share Networks', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'share_network',
			array(
				'label'   => esc_html__( 'Network', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'facebook',
				'options' => array(
					'facebook'  => esc_html__( 'Facebook', 'elonix' ),
					'twitter'   => esc_html__( 'X (Twitter)', 'elonix' ),
					'linkedin'  => esc_html__( 'LinkedIn', 'elonix' ),
					'pinterest' => esc_html__( 'Pinterest', 'elonix' ),
					'whatsapp'  => esc_html__( 'WhatsApp', 'elonix' ),
					'telegram'  => esc_html__( 'Telegram', 'elonix' ),
					'reddit'    => esc_html__( 'Reddit', 'elonix' ),
					'tumblr'    => esc_html__( 'Tumblr', 'elonix' ),
					'email'     => esc_html__( 'Email', 'elonix' ),
					'copy_link' => esc_html__( 'Copy Link', 'elonix' ),
					'threads'   => esc_html__( 'Threads', 'elonix' ),
					'bluesky'   => esc_html__( 'Bluesky', 'elonix' ),
				),
			)
		);

		$repeater->add_control(
			'custom_icon',
			array(
				'label'            => esc_html__( 'Custom Icon', 'elonix' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => '',
					'library' => '',
				),
			)
		);

		$repeater->add_control(
			'custom_label',
			array(
				'label'       => esc_html__( 'Custom Label', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Share', 'elonix' ),
				'label_block' => true,
			)
		);

		// Per-Item Style Controls
		$this->register_item_style_controls( $repeater );

		$this->add_control(
			'share_networks_list',
			array(
				'label'       => esc_html__( 'Share Networks Repeater', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'share_network' => 'facebook',
						'custom_label'  => 'Facebook',
					),
					array(
						'share_network' => 'twitter',
						'custom_label'  => 'Twitter',
					),
					array(
						'share_network' => 'linkedin',
						'custom_label'  => 'LinkedIn',
					),
					array(
						'share_network' => 'whatsapp',
						'custom_label'  => 'WhatsApp',
					),
					array(
						'share_network' => 'copy_link',
						'custom_label'  => 'Copy Link',
					),
				),
				'title_field' => '{{{ custom_label || share_network }}}',
			)
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - SHARE OPTIONS SECTION
		// ==========================================
		$this->start_controls_section(
			'section_share_options',
			array(
				'label' => esc_html__( 'Share Options', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'share_text',
			array(
				'label'       => esc_html__( 'Share Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Share this article:', 'elonix' ),
				'placeholder' => esc_html__( 'Share:', 'elonix' ),
				'description' => esc_html__( 'Leave empty to hide.', 'elonix' ),
			)
		);

		$this->add_control(
			'share_text_tag',
			array(
				'label'     => esc_html__( 'Share Text HTML Tag', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'h5',
				'options'   => array(
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'condition' => array(
					'share_text!' => '',
				),
			)
		);

		$this->add_control(
			'copy_success_message',
			array(
				'label'   => esc_html__( 'Copy Success Message', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Copied!', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - POPUP OPTIONS SECTION
		// ==========================================
		$this->start_controls_section(
			'section_popup_options',
			array(
				'label' => esc_html__( 'Popup Options', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'open_share_popup',
			array(
				'label'        => esc_html__( 'Open Share Window', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Enable popup window sharing functionality.', 'elonix' ),
			)
		);

		$this->add_control(
			'popup_type',
			array(
				'label'     => esc_html__( 'Popup Window Type', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'modal',
				'options'   => array(
					'modal'   => esc_html__( 'On-Screen Custom Modal (Premium)', 'elonix' ),
					'browser' => esc_html__( 'Browser Native Popup Window', 'elonix' ),
				),
				'condition' => array(
					'open_share_popup' => 'yes',
				),
			)
		);

		$this->add_control(
			'modal_trigger_text',
			array(
				'label'     => esc_html__( 'Modal Trigger Button Text', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Share This Post', 'elonix' ),
				'condition' => array(
					'open_share_popup' => 'yes',
					'popup_type'       => 'modal',
				),
			)
		);

		$this->add_control(
			'modal_trigger_icon',
			array(
				'label'            => esc_html__( 'Modal Trigger Icon', 'elonix' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-share-alt',
					'library' => 'fa-solid',
				),
				'condition'        => array(
					'open_share_popup' => 'yes',
					'popup_type'       => 'modal',
				),
			)
		);

		$this->add_control(
			'modal_popup_title',
			array(
				'label'     => esc_html__( 'Modal Popup Title', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Share with your network', 'elonix' ),
				'condition' => array(
					'open_share_popup' => 'yes',
					'popup_type'       => 'modal',
				),
			)
		);

		$this->add_control(
			'popup_width',
			array(
				'label'     => esc_html__( 'Browser Popup Width (px)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 600,
				'condition' => array(
					'open_share_popup' => 'yes',
					'popup_type'       => 'browser',
				),
			)
		);

		$this->add_control(
			'popup_height',
			array(
				'label'     => esc_html__( 'Browser Popup Height (px)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 500,
				'condition' => array(
					'open_share_popup' => 'yes',
					'popup_type'       => 'browser',
				),
			)
		);

		$this->end_controls_section();

		// Register inherited base sections
		$this->register_layout_controls();
		$this->register_icon_settings_controls();
		$this->register_style_presets_controls();
		$this->register_tooltip_controls();
		$this->register_advanced_brand_controls();

		// ==========================================
		// STYLE TAB - SHARE TEXT STYLE
		// ==========================================
		$this->start_controls_section(
			'section_style_share_text',
			array(
				'label'     => esc_html__( 'Share Text Style', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'share_text!' => '',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'share_text_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-title',
			)
		);

		$this->add_control(
			'share_text_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'share_text_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - COPY SUCCESS MESSAGE STYLE
		// ==========================================
		$this->start_controls_section(
			'section_style_copy_success',
			array(
				'label'     => esc_html__( 'Copy Success Message Style', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'copy_success_message!' => '',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'copy_toast_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-copy-success-toast',
			)
		);

		$this->add_control(
			'copy_toast_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .tv-copy-success-toast' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'copy_toast_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#10b981',
				'selectors' => array(
					'{{WRAPPER}} .tv-copy-success-toast' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'copy_toast_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .tv-copy-success-toast svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'copy_toast_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-copy-success-toast',
			)
		);

		$this->add_responsive_control(
			'copy_toast_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-copy-success-toast' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'copy_toast_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-copy-success-toast',
			)
		);

		$this->add_responsive_control(
			'copy_toast_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-copy-success-toast' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - POPUP WINDOW STYLES
		// ==========================================
		$this->start_controls_section(
			'section_style_popup_window',
			array(
				'label'     => esc_html__( 'Popup Window Styles', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'open_share_popup' => 'yes',
				),
			)
		);

		$this->add_control(
			'heading_trigger_style',
			array(
				'label' => esc_html__( 'Modal Trigger Button', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'trigger_typography',
				'label'    => esc_html__( 'Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-trigger',
			)
		);

		$this->start_controls_tabs( 'tabs_trigger_style' );

		$this->start_controls_tab(
			'tab_trigger_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'trigger_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-trigger' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trigger_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-trigger' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'trigger_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-trigger',
			)
		);

		$this->add_responsive_control(
			'trigger_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'    => '8',
					'right'  => '8',
					'bottom' => '8',
					'left'   => '8',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-modal-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trigger_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-trigger',
			)
		);

		$this->add_responsive_control(
			'trigger_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => '12',
					'right'  => '24',
					'bottom' => '12',
					'left'   => '24',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-modal-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'trigger_color_hover',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-trigger:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trigger_bg_color_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-trigger:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'trigger_border_hover',
				'label'    => esc_html__( 'Border Hover', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-trigger:hover',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trigger_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow Hover', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-trigger:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_modal_overlay',
			array(
				'label'     => esc_html__( 'Modal Overlay & Box', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'modal_overlay_bg',
			array(
				'label'     => esc_html__( 'Overlay Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(15, 23, 42, 0.75)',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'modal_box_bg',
			array(
				'label'     => esc_html__( 'Modal Box Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-box' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'modal_box_width',
			array(
				'label'      => esc_html__( 'Modal Box Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 280,
						'max' => 1000,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 450,
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-modal-box' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'modal_box_padding',
			array(
				'label'      => esc_html__( 'Modal Box Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => '30',
					'right'  => '30',
					'bottom' => '30',
					'left'   => '30',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-modal-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'modal_box_border',
				'label'    => esc_html__( 'Modal Box Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-box',
			)
		);

		$this->add_responsive_control(
			'modal_box_border_radius',
			array(
				'label'      => esc_html__( 'Modal Box Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'    => '16',
					'right'  => '16',
					'bottom' => '16',
					'left'   => '16',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-modal-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'modal_box_shadow',
				'label'    => esc_html__( 'Modal Box Shadow', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-box',
			)
		);

		$this->add_control(
			'heading_modal_title_style',
			array(
				'label'     => esc_html__( 'Modal Title & Close', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'modal_title_typography',
				'label'    => esc_html__( 'Title Typography', 'elonix' ),
				'selector' => '{{WRAPPER}} .tv-share-modal-title',
			)
		);

		$this->add_control(
			'modal_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'modal_close_color',
			array(
				'label'     => esc_html__( 'Close Button Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-close' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'modal_close_color_hover',
			array(
				'label'     => esc_html__( 'Close Button Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-close:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'modal_close_bg_hover',
			array(
				'label'     => esc_html__( 'Close Button Hover Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#f1f5f9',
				'selectors' => array(
					'{{WRAPPER}} .tv-share-modal-close:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// Register inherited style tabs (Social Icon Styles & Tooltip Styles)
		$this->register_style_tabs_controls();
	}

	/**
	 * Retrieve current post object dynamically across all WordPress & Elementor contexts.
	 *
	 * @return WP_Post|null Current post object.
	 */
	private function get_current_post() {
		if ( method_exists( $this, 'get_current_post_object' ) ) {
			$post = $this->get_current_post_object();
			if ( ! empty( $post ) ) {
				return $post;
			}
		}

		global $post;
		$queried_object = get_queried_object();

		if ( $queried_object instanceof WP_Post ) {
			return $queried_object;
		}

		if ( ! empty( $post ) && $post instanceof WP_Post ) {
			return $post;
		}

		return null;
	}

	/**
	 * Render post share widget HTML output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['share_networks_list'] ) ) {
			return;
		}

		// Fetch dynamic post data
		$current_post = $this->get_current_post();
		if ( ! empty( $current_post ) ) {
			$post_id    = $current_post->ID;
			$post_url   = get_permalink( $post_id );
			$post_title = get_the_title( $post_id );
			$post_image = get_the_post_thumbnail_url( $post_id, 'full' );
			$post_desc  = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( $current_post->post_content, 20, '...' );
		} else {
			$post_url   = home_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
			$post_title = get_the_archive_title();
			$post_image = '';
			$post_desc  = get_the_archive_description();
		}

		if ( empty( $post_url ) ) {
			$post_url = home_url();
		}
		if ( empty( $post_title ) ) {
			$post_title = get_bloginfo( 'name' );
		}
		if ( empty( $post_desc ) ) {
			$post_desc = get_bloginfo( 'description' );
		}

		$is_modal = ( 'yes' === $settings['open_share_popup'] && 'modal' === $settings['popup_type'] );
		?>
		<div class="tv-post-share-container <?php echo esc_attr( $is_modal ? 'tv-share-mode-modal' : 'tv-share-mode-direct' ); ?>">
			
			<?php if ( $is_modal ) : ?>
				<button class="tv-share-modal-trigger" aria-haspopup="dialog" aria-expanded="false">
					<?php
					if ( ! empty( $settings['modal_trigger_icon']['value'] ) ) {
						\Elementor\Icons_Manager::render_icon( $settings['modal_trigger_icon'], array( 'aria-hidden' => 'true' ) );
					}
					if ( ! empty( $settings['modal_trigger_text'] ) ) {
						echo '<span>' . esc_html( $settings['modal_trigger_text'] ) . '</span>';
					}
					?>
				</button>

				<div class="tv-share-modal-overlay" aria-hidden="true">
					<div class="tv-share-modal-box" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $settings['modal_popup_title'] ); ?>">
						<button class="tv-share-modal-close" aria-label="<?php esc_attr_e( 'Close Share Window', 'elonix' ); ?>">
							<i class="fas fa-times" aria-hidden="true"></i>
						</button>
						
						<?php if ( ! empty( $settings['modal_popup_title'] ) ) : ?>
							<div class="tv-share-modal-header">
								<h4 class="tv-share-modal-title"><?php echo esc_html( $settings['modal_popup_title'] ); ?></h4>
							</div>
						<?php endif; ?>

						<div class="tv-share-modal-body">
			<?php endif; ?>

			<?php if ( ! $is_modal && ! empty( $settings['share_text'] ) ) : ?>
				<div class="tv-post-share-header">
					<?php
					$tag = ! empty( $settings['share_text_tag'] ) ? $settings['share_text_tag'] : 'h5';
					echo '<' . esc_attr( $tag ) . ' class="tv-share-title">' . esc_html( $settings['share_text'] ) . '</' . esc_attr( $tag ) . '>';
					?>
				</div>
			<?php endif; ?>

			<div class="elonix-social-icons-wrapper">
				<?php
				foreach ( $settings['share_networks_list'] as $index => $item ) :
					$network    = $item['share_network'];
					$item_key   = 'share_network_' . $index;
					$label_text = ! empty( $item['custom_label'] ) ? $item['custom_label'] : ucwords( str_replace( '_', ' ', $network ) );

					// Generate official share URLs
					$share_url = '#';
					switch ( $network ) {
						case 'facebook':
							$share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $post_url );
							break;
						case 'twitter':
							$share_url = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $post_url ) . '&text=' . rawurlencode( $post_title );
							break;
						case 'linkedin':
							$share_url = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $post_url );
							break;
						case 'pinterest':
							$share_url = 'https://www.pinterest.com/pin/create/button/?url=' . rawurlencode( $post_url ) . '&description=' . rawurlencode( $post_title ) . '&media=' . rawurlencode( $post_image );
							break;
						case 'whatsapp':
							$share_url = 'https://api.whatsapp.com/send?text=' . rawurlencode( $post_title . ' ' . $post_url );
							break;
						case 'telegram':
							$share_url = 'https://t.me/share/url?url=' . rawurlencode( $post_url ) . '&text=' . rawurlencode( $post_title );
							break;
						case 'reddit':
							$share_url = 'https://reddit.com/submit?url=' . rawurlencode( $post_url ) . '&title=' . rawurlencode( $post_title );
							break;
						case 'tumblr':
							$share_url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . rawurlencode( $post_url ) . '&title=' . rawurlencode( $post_title ) . '&caption=' . rawurlencode( $post_desc );
							break;
						case 'email':
							$share_url = 'mailto:?subject=' . rawurlencode( $post_title ) . '&body=' . rawurlencode( $post_title . "\n\n" . $post_url . "\n\n" . $post_desc );
							break;
						case 'threads':
							$share_url = 'https://www.threads.net/intent/post?text=' . rawurlencode( $post_title . ' ' . $post_url );
							break;
						case 'bluesky':
							$share_url = 'https://bsky.app/intent/compose?text=' . rawurlencode( $post_title . ' ' . $post_url );
							break;
						case 'copy_link':
							$share_url = '#';
							$this->add_render_attribute( $item_key, 'data-tv-copy-url', $post_url );
							$this->add_render_attribute( $item_key, 'data-tv-copy-success', ! empty( $settings['copy_success_message'] ) ? $settings['copy_success_message'] : esc_html__( 'Copied!', 'elonix' ) );
							break;
					}

					$this->add_render_attribute( $item_key, 'href', $share_url );

					// Target / Popup attributes
					if ( 'email' !== $network && 'copy_link' !== $network ) {
						if ( 'yes' === $settings['open_share_popup'] && 'browser' === $settings['popup_type'] ) {
							$this->add_render_attribute( $item_key, 'data-tv-share-popup', 'true' );
							$this->add_render_attribute( $item_key, 'data-tv-popup-width', ! empty( $settings['popup_width'] ) ? $settings['popup_width'] : 600 );
							$this->add_render_attribute( $item_key, 'data-tv-popup-height', ! empty( $settings['popup_height'] ) ? $settings['popup_height'] : 500 );
						} else {
							$this->add_render_attribute( $item_key, 'target', '_blank' );
						}
						$this->add_render_attribute( $item_key, 'rel', 'noopener noreferrer' );
					}

					$item_classes = 'tv-social-item tv-social-platform-' . esc_attr( $network );
					if ( ! empty( $item['_id'] ) ) {
						$item_classes .= ' elementor-repeater-item-' . esc_attr( $item['_id'] );
					}
					$this->add_render_attribute( $item_key, 'class', $item_classes );
					$this->add_render_attribute( $item_key, 'aria-label', esc_attr( $label_text ) );
					$this->add_render_attribute( $item_key, 'tabindex', '0' );
					?>
					<a <?php $this->print_render_attribute_string( $item_key ); ?>>
						<div class="tv-social-item-inner">
							
							<?php if ( 'label_only' !== $settings['icon_position'] ) : ?>
								<span class="tv-social-icon-box">
									<?php
									if ( ! empty( $item['custom_icon']['value'] ) ) {
										\Elementor\Icons_Manager::render_icon( $item['custom_icon'], array( 'aria-hidden' => 'true' ) );
									} else {
										$default_class = $this->get_default_platform_icon( $network );
										echo '<i class="' . esc_attr( $default_class ) . '" aria-hidden="true"></i>';
									}
									?>
								</span>
							<?php endif; ?>

							<?php if ( 'icon_only' !== $settings['icon_position'] && ! empty( $label_text ) ) : ?>
								<span class="tv-social-label">
									<?php echo esc_html( $label_text ); ?>
								</span>
							<?php endif; ?>

						</div>
						
						<?php if ( 'yes' === $settings['enable_tooltip'] && ! empty( $label_text ) ) : ?>
							<span class="tv-social-tooltip" role="tooltip">
								<?php echo esc_html( $label_text ); ?>
							</span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>

			<?php if ( $is_modal ) : ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}
}
