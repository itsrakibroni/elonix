<?php
/**
 * Elonix – Toolkit for Elementor Smart Contact Actions Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

class Elonix_Toolkit_Smart_Contact_Actions_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-smart-contact-actions';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Smart Contact Actions', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-comments';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'contact', 'actions', 'whatsapp', 'messenger', 'email', 'call', 'telegram', 'tvkit' );
	}

	/**
	 * Retrieve widget style dependency list.
	 *
	 * @return array Dependency handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-smart-contact-actions' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependency handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-tv-smart-contact-actions' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ================= CONTENT TAB =================

		// 1. Content Tab - Layout Section
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout & Design', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout_type',
			array(
				'label'   => esc_html__( 'Layout Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'speed_dial',
				'options' => array(
					'single'       => esc_html__( 'Floating Single Button', 'elonix' ),
					'speed_dial'   => esc_html__( 'Speed Dial menu', 'elonix' ),
					'radial'       => esc_html__( 'Radial Polar Menu', 'elonix' ),
					'vertical'     => esc_html__( 'Vertical Stack Panel', 'elonix' ),
					'horizontal'   => esc_html__( 'Horizontal Toolbar Stack', 'elonix' ),
					'contact_card' => esc_html__( 'Floating Contact Card', 'elonix' ),
					'mini_toolbar' => esc_html__( 'Floating Mini Toolbar', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'bar_position',
			array(
				'label'     => esc_html__( 'Dock Position Screen Edge', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bottom-right',
				'options'   => array(
					'top-left'     => esc_html__( 'Top Left Corner', 'elonix' ),
					'top-right'    => esc_html__( 'Top Right Corner', 'elonix' ),
					'bottom-left'  => esc_html__( 'Bottom Left Corner', 'elonix' ),
					'bottom-right' => esc_html__( 'Bottom Right Corner', 'elonix' ),
					'center-left'  => esc_html__( 'Center Left Edge', 'elonix' ),
					'center-right' => esc_html__( 'Center Right Edge', 'elonix' ),
				),
				'condition' => array(
					'layout_type!' => array( 'vertical', 'horizontal' ),
				),
			)
		);

		$this->end_controls_section();

		// 2. Content Tab - Actions & Agents Repeater
		$this->start_controls_section(
			'section_actions',
			array(
				'label' => esc_html__( 'Contact Actions & Agents', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'label',
			array(
				'label'   => esc_html__( 'Label / Display Name', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Support Desk', 'elonix' ),
			)
		);

		$repeater->add_control(
			'action_type',
			array(
				'label'   => esc_html__( 'Action Trigger Connection', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'whatsapp',
				'options' => array(
					'whatsapp'    => esc_html__( 'WhatsApp Chat Link', 'elonix' ),
					'call'        => esc_html__( 'Direct Phone Call (tel:)', 'elonix' ),
					'email'       => esc_html__( 'Direct Email Send (mailto:)', 'elonix' ),
					'telegram'    => esc_html__( 'Telegram Chat Link', 'elonix' ),
					'messenger'   => esc_html__( 'Facebook Messenger Chat', 'elonix' ),
					'skype'       => esc_html__( 'Skype Action Call/Chat', 'elonix' ),
					'facebook'    => esc_html__( 'Facebook profile', 'elonix' ),
					'instagram'   => esc_html__( 'Instagram Profile', 'elonix' ),
					'linkedin'    => esc_html__( 'LinkedIn Profile', 'elonix' ),
					'x_twitter'   => esc_html__( 'X (Twitter) Profile', 'elonix' ),
					'youtube'     => esc_html__( 'YouTube Channel Profile', 'elonix' ),
					'tiktok'      => esc_html__( 'TikTok Profile Link', 'elonix' ),
					'inline_form' => esc_html__( 'Render Shortcode Inline Form', 'elonix' ),
					'custom_url'  => esc_html__( 'Custom Action Redirect Link', 'elonix' ),
				),
			)
		);

		$repeater->add_control(
			'agent_photo',
			array(
				'label'     => esc_html__( 'Agent Display Photo (Avatar)', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array( 'url' => '' ),
				'condition' => array(
					'action_type' => 'whatsapp',
				),
			)
		);

		$repeater->add_control(
			'agent_dept',
			array(
				'label'       => esc_html__( 'Department / Role Designation', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. Sales, Billing, Tech Support', 'elonix' ),
				'condition'   => array(
					'action_type' => 'whatsapp',
				),
			)
		);

		$repeater->add_control(
			'action_value',
			array(
				'label'       => esc_html__( 'Target Connection Value', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. Phone, email, profile url, or shortcode', 'elonix' ),
				'description' => esc_html__( 'Enter the details associated with selected action (e.g. +1234567, agent@mail.com, [contact-form-7...])', 'elonix' ),
			)
		);

		$repeater->add_control(
			'chat_template',
			array(
				'label'     => esc_html__( 'Pre-filled Template Message', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'          => esc_html__( 'No Prefilled Message (Free Chat)', 'elonix' ),
					'hello'         => esc_html__( 'Template: Hello there!', 'elonix' ),
					'need_support'  => esc_html__( 'Template: Hi, I need support helper', 'elonix' ),
					'request_quote' => esc_html__( 'Template: I want a project quote', 'elonix' ),
					'contact_sales' => esc_html__( 'Template: Connect me to Sales team', 'elonix' ),
					'custom'        => esc_html__( 'Custom Template (Enter text below)', 'elonix' ),
				),
				'condition' => array(
					'action_type' => 'whatsapp',
				),
			)
		);

		$repeater->add_control(
			'custom_chat_message',
			array(
				'label'     => esc_html__( 'Custom Prefilled Message', 'elonix' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array(
					'action_type'   => 'whatsapp',
					'chat_template' => 'custom',
				),
			)
		);

		$repeater->add_control(
			'item_icon',
			array(
				'label'   => esc_html__( 'Display Graphic Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-phone-alt',
					'library' => 'solid',
				),
			)
		);

		$repeater->add_control(
			'tooltip_text',
			array(
				'label'       => esc_html__( 'Hover Tooltip text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Talk with us', 'elonix' ),
			)
		);

		$this->add_control(
			'action_items',
			array(
				'label'       => esc_html__( 'Configure Actions / Agents list', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'label'       => esc_html__( 'WhatsApp Tech Support', 'elonix' ),
						'action_type' => 'whatsapp',
						'agent_dept'  => esc_html__( 'Technical Support', 'elonix' ),
						'item_icon'   => array(
							'value'   => 'fab fa-whatsapp',
							'library' => 'brands',
						),
					),
				),
				'title_field' => '{{{ label }}} ({{{ action_type }}})',
			)
		);

		$this->end_controls_section();

		// 3. Content Tab - Business Hours schedule timezone
		$this->start_controls_section(
			'section_business_hours',
			array(
				'label' => esc_html__( 'Automated Business Hours', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_hours',
			array(
				'label'        => esc_html__( 'Enable Weekly Schedule Filter', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'business_timezone',
			array(
				'label'       => esc_html__( 'Corporate Office Timezone', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Asia/Dhaka',
				'placeholder' => esc_html__( 'e.g. UTC+6 or Asia/Dhaka or America/New_York', 'elonix' ),
				'description' => esc_html__( 'Enter the server timezone ID or absolute GMT string offset to evaluate online statuses.', 'elonix' ),
				'condition'   => array(
					'enable_hours' => 'yes',
				),
			)
		);

		$schedule_repeater = new Repeater();

		$schedule_repeater->add_control(
			'day_of_week',
			array(
				'label'   => esc_html__( 'Day of Week', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'monday',
				'options' => array(
					'monday'    => esc_html__( 'Monday', 'elonix' ),
					'tuesday'   => esc_html__( 'Tuesday', 'elonix' ),
					'wednesday' => esc_html__( 'Wednesday', 'elonix' ),
					'thursday'  => esc_html__( 'Thursday', 'elonix' ),
					'friday'    => esc_html__( 'Friday', 'elonix' ),
					'saturday'  => esc_html__( 'Saturday', 'elonix' ),
					'sunday'    => esc_html__( 'Sunday', 'elonix' ),
				),
			)
		);

		$schedule_repeater->add_control(
			'open_time',
			array(
				'label'       => esc_html__( 'Open Time (24h)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '09:00',
				'placeholder' => '09:00',
			)
		);

		$schedule_repeater->add_control(
			'close_time',
			array(
				'label'       => esc_html__( 'Close Time (24h)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '18:00',
				'placeholder' => '18:00',
			)
		);

		$schedule_repeater->add_control(
			'lunch_start',
			array(
				'label'       => esc_html__( 'Lunch Break Start (24h)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '13:00',
				'placeholder' => '13:00',
			)
		);

		$schedule_repeater->add_control(
			'lunch_end',
			array(
				'label'       => esc_html__( 'Lunch Break End (24h)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '14:00',
				'placeholder' => '14:00',
			)
		);

		$this->add_control(
			'hours_schedule',
			array(
				'label'       => esc_html__( 'Weekly Time schedule Slots', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $schedule_repeater->get_controls(),
				'default'     => array(
					array(
						'day_of_week' => 'monday',
						'open_time'   => '09:00',
						'close_time'  => '18:00',
						'lunch_start' => '13:00',
						'lunch_end'   => '14:00',
					),
				),
				'title_field' => '{{{ day_of_week }}} ({{{ open_time }}} - {{{ close_time }}})',
				'condition'   => array(
					'enable_hours' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 4. Content Tab - Custom QR Code Generation Options
		$this->start_controls_section(
			'section_qr_engine',
			array(
				'label' => esc_html__( 'QR Code Generator (Client)', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_qr',
			array(
				'label'        => esc_html__( 'Enable Quick scan QR Modal', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'qr_type',
			array(
				'label'     => esc_html__( 'Scan Target Output Code', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'whatsapp_qr',
				'options'   => array(
					'whatsapp_qr' => esc_html__( 'Direct WhatsApp Scan Code', 'elonix' ),
					'vcard_qr'    => esc_html__( 'Download vCard Contact Details', 'elonix' ),
					'custom_qr'   => esc_html__( 'Load Custom Redirect Link', 'elonix' ),
				),
				'condition' => array(
					'enable_qr' => 'yes',
				),
			)
		);

		$this->add_control(
			'qr_custom_value',
			array(
				'label'       => esc_html__( 'QR Redirect Target URL', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'https://elonix.net',
				'condition'   => array(
					'enable_qr' => 'yes',
					'qr_type'   => 'custom_qr',
				),
			)
		);

		$this->add_control(
			'qr_vcard_name',
			array(
				'label'       => esc_html__( 'vCard Display Full Name', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'John Doe',
				'condition'   => array(
					'enable_qr' => 'yes',
					'qr_type'   => 'vcard_qr',
				),
			)
		);

		$this->add_control(
			'qr_vcard_phone',
			array(
				'label'       => esc_html__( 'vCard Mobile Phone', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '+15550199',
				'condition'   => array(
					'enable_qr' => 'yes',
					'qr_type'   => 'vcard_qr',
				),
			)
		);

		$this->add_control(
			'qr_vcard_email',
			array(
				'label'       => esc_html__( 'vCard Professional Email', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'john@mail.com',
				'condition'   => array(
					'enable_qr' => 'yes',
					'qr_type'   => 'vcard_qr',
				),
			)
		);

		$this->end_controls_section();

		// 5. Content Tab - Custom Visibility, Triggers, Notifications
		$this->start_controls_section(
			'section_vis_actions',
			array(
				'label' => esc_html__( 'Visibility Rules & Badges', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_badge',
			array(
				'label'        => esc_html__( 'Notification Counter Badge', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'badge_val',
			array(
				'label'       => esc_html__( 'Counter / Label Text', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'New',
				'placeholder' => '1, 3, New, Hot',
				'condition'   => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'attention_anim',
			array(
				'label'   => esc_html__( 'Attention Grab Animation', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'pulse',
				'options' => array(
					'none'   => esc_html__( 'None (Static)', 'elonix' ),
					'pulse'  => esc_html__( 'Pulsing Indicator Loop', 'elonix' ),
					'wiggle' => esc_html__( 'Vibrating Wiggle Wave', 'elonix' ),
					'bounce' => esc_html__( 'Arrow Bouncing', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'smart_visibility',
			array(
				'label'        => esc_html__( 'Hide on Scroll Down / Show Up', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'enable_analytics_tracking',
			array(
				'label'        => esc_html__( 'GA4 / Google Tag Manager dataLayer Events', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_controls_section();

		// ================= STYLE TAB SECTIONS =================

		// 6. Style Tab - Container Styling
		$this->start_controls_section(
			'section_container_style',
			array(
				'label' => esc_html__( 'Container Dock & Positions', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'container_offset_bottom',
			array(
				'label'      => esc_html__( 'Bottom Screen offset (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 300,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-container' => 'bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_offset_side',
			array(
				'label'      => esc_html__( 'Horizontal Screen Offset (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 300,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-align-right' => 'right: {{SIZE}}{{UNIT}}; left: auto;',
					'{{WRAPPER}} .tv-sca-align-left'  => 'left: {{SIZE}}{{UNIT}}; right: auto;',
				),
			)
		);

		$this->add_control(
			'bg_preset',
			array(
				'label'   => esc_html__( 'Interactive Styling Preset', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'solid'       => esc_html__( 'Solid Background Preset', 'elonix' ),
					'gradient'    => esc_html__( 'Gradients Preset Fill', 'elonix' ),
					'glass'       => esc_html__( 'Glassmorphism Blur preset', 'elonix' ),
					'neumorphism' => esc_html__( 'Neumorphism preset', 'elonix' ),
					'custom'      => esc_html__( 'Custom (Style Tab Settings)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'glass_blur_intensity',
			array(
				'label'     => esc_html__( 'Backdrop Blur (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 10 ),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 30,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-bg-glass' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
					'{{WRAPPER}} .tv-sca-card-drawer.tv-sca-bg-glass' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				),
				'condition' => array(
					'bg_preset' => 'glass',
				),
			)
		);

		$this->add_control(
			'neumorphism_light_angle',
			array(
				'label'     => esc_html__( 'Neumorphism Angle (deg)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 135 ),
				'range'     => array(
					'deg' => array(
						'min' => 0,
						'max' => 360,
					),
				),
				'condition' => array(
					'bg_preset' => 'neumorphism',
				),
			)
		);

		$this->end_controls_section();

		// 7. Style Tab - Main FAB Trigger
		$this->start_controls_section(
			'section_trigger_style',
			array(
				'label' => esc_html__( 'Main Trigger Button', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'trigger_width',
			array(
				'label'      => esc_html__( 'Button Dimensions (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 30,
						'max' => 120,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-trigger-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'trigger_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-trigger-btn svg, {{WRAPPER}} .tv-sca-trigger-btn i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'trigger_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-trigger-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_trigger_color' );

		$this->start_controls_tab(
			'tab_trigger_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'trigger_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-trigger-btn' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'trigger_text_color',
			array(
				'label'     => esc_html__( 'Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-trigger-btn'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-trigger-btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'trigger_border',
				'selector' => '{{WRAPPER}} .tv-sca-trigger-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trigger_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-trigger-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_hover',
			array(
				'label' => esc_html__( 'Hover / Active', 'elonix' ),
			)
		);

		$this->add_control(
			'trigger_bg_color_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-trigger-btn:hover' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'trigger_text_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-trigger-btn:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-trigger-btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trigger_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-sca-trigger-btn:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 8. Style Tab - Dial Sub-menu Action Buttons
		$this->start_controls_section(
			'section_actions_style',
			array(
				'label'     => esc_html__( 'Sub-Action List Items', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout_type!' => array( 'single', 'contact_card' ),
				),
			)
		);

		$this->add_responsive_control(
			'action_item_size',
			array(
				'label'      => esc_html__( 'Item Dimensions (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 30,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-item-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'action_item_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-item-btn svg, {{WRAPPER}} .tv-sca-item-btn i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'action_item_gap',
			array(
				'label'      => esc_html__( 'Expansion Spacing (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 5,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-dial-open .tv-sca-item:nth-child(1)' => 'transform: translateY(-{{SIZE}}px);',
					'{{WRAPPER}} .tv-sca-dial-open .tv-sca-item:nth-child(2)' => 'transform: translateY(-calc({{SIZE}}px * 2));',
					'{{WRAPPER}} .tv-sca-dial-open .tv-sca-item:nth-child(3)' => 'transform: translateY(-calc({{SIZE}}px * 3));',
					'{{WRAPPER}} .tv-sca-dial-open .tv-sca-item:nth-child(4)' => 'transform: translateY(-calc({{SIZE}}px * 4));',
					'{{WRAPPER}} .tv-sca-dial-open .tv-sca-item:nth-child(5)' => 'transform: translateY(-calc({{SIZE}}px * 5));',
					'{{WRAPPER}} .tv-sca-item-list' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'action_item_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-item-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_items_colors' );

		$this->start_controls_tab(
			'tab_items_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'item_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-item-btn' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'item_text_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-item-btn'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-item-btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .tv-sca-item-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-item-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_items_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'item_bg_color_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-item-btn:hover' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'item_text_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-item-btn:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-item-btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-sca-item-btn:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 9. Style Tab - Card Layout Panels
		$this->start_controls_section(
			'section_card_style',
			array(
				'label'     => esc_html__( 'Floating Contact Card Layout', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout_type' => 'contact_card',
				),
			)
		);

		$this->add_responsive_control(
			'card_width',
			array(
				'label'      => esc_html__( 'Card Box Width (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-card-drawer' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Card Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-card-drawer' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .tv-sca-card-drawer',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-card-drawer',
			)
		);

		$this->add_control(
			'card_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-card-drawer' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		// Header Heading
		$this->add_control(
			'heading_header_style',
			array(
				'label'     => esc_html__( 'Card Header Area', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'card_header_bg',
			array(
				'label'     => esc_html__( 'Header Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-card-header' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_responsive_control(
			'card_header_padding',
			array(
				'label'      => esc_html__( 'Header Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-card-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_title_color',
			array(
				'label'     => esc_html__( 'Title Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-card-header h4' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_title_typo',
				'selector' => '{{WRAPPER}} .tv-sca-card-header h4',
			)
		);

		$this->add_control(
			'card_desc_color',
			array(
				'label'     => esc_html__( 'Description Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-card-header p' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_desc_typo',
				'selector' => '{{WRAPPER}} .tv-sca-card-header p',
			)
		);

		// Body Heading
		$this->add_control(
			'heading_body_style',
			array(
				'label'     => esc_html__( 'Card Body Area', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'card_body_padding',
			array(
				'label'      => esc_html__( 'Body Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Agent Row Heading
		$this->add_control(
			'heading_agent_row_style',
			array(
				'label'     => esc_html__( 'Agent Row Block', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'agent_row_bg',
			array(
				'label'     => esc_html__( 'Row Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-row' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_responsive_control(
			'agent_row_padding',
			array(
				'label'      => esc_html__( 'Row Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-agent-row' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'agent_row_radius',
			array(
				'label'      => esc_html__( 'Row Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-agent-row' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'agent_row_border',
				'selector' => '{{WRAPPER}} .tv-sca-agent-row',
			)
		);

		$this->add_control(
			'agent_name_color',
			array(
				'label'     => esc_html__( 'Agent Name Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-info h5' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'agent_name_typo',
				'selector' => '{{WRAPPER}} .tv-sca-agent-info h5',
			)
		);

		$this->add_control(
			'agent_dept_color',
			array(
				'label'     => esc_html__( 'Designation Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-info span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'agent_dept_typo',
				'selector' => '{{WRAPPER}} .tv-sca-agent-info span',
			)
		);

		// Agent Button Heading
		$this->add_control(
			'heading_agent_btn_style',
			array(
				'label'     => esc_html__( 'Agent Action Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'agent_btn_size',
			array(
				'label'      => esc_html__( 'Button Dimension (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'agent_btn_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn svg, {{WRAPPER}} .tv-sca-agent-link-btn i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_agent_btn_colors' );

		$this->start_controls_tab(
			'tab_agent_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'agent_btn_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'agent_btn_text_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-agent-link-btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'agent_btn_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_agent_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'agent_btn_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn:hover' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_control(
			'agent_btn_text_color_hover',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-agent-link-btn:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-agent-link-btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 10. Style Tab - Tooltips, Badges & Status markers
		$this->start_controls_section(
			'section_elements_style',
			array(
				'label' => esc_html__( 'Tooltips, Badges & Status markers', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Tooltip Styling
		$this->add_control(
			'heading_tooltip_style',
			array(
				'label' => esc_html__( 'Hover Tooltips', 'elonix' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'tooltip_bg',
			array(
				'label'     => esc_html__( 'Tooltip Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-tooltip'        => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .tv-sca-tooltip::after' => 'border-color: transparent transparent transparent {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tooltip_text_color',
			array(
				'label'     => esc_html__( 'Tooltip Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-tooltip' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tooltip_typo',
				'selector' => '{{WRAPPER}} .tv-sca-tooltip',
			)
		);

		$this->add_responsive_control(
			'tooltip_padding',
			array(
				'label'      => esc_html__( 'Tooltip Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-tooltip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'tooltip_radius',
			array(
				'label'      => esc_html__( 'Tooltip Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-tooltip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'tooltip_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-tooltip',
			)
		);

		// Badge Styling
		$this->add_control(
			'heading_badge_style',
			array(
				'label'     => esc_html__( 'Notification Badges', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_bg',
			array(
				'label'     => esc_html__( 'Notification Badge Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-badge' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Notification Badge Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-badge' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'badge_typo',
				'selector'  => '{{WRAPPER}} .tv-sca-badge',
				'condition' => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Badge Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'enable_badge' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'badge_radius',
			array(
				'label'      => esc_html__( 'Badge Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'enable_badge' => 'yes',
				),
			)
		);

		// Status Indicator Styling
		$this->add_control(
			'heading_status_style',
			array(
				'label'     => esc_html__( 'Business Hours Status Dots', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'status_dot_size',
			array(
				'label'      => esc_html__( 'Indicator Dimensions (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 6,
						'max' => 24,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-status-indicator' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'status_dot_border_width',
			array(
				'label'      => esc_html__( 'Dot Border Thickness (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 6,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-status-indicator' => 'border-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'status_dot_border_color',
			array(
				'label'     => esc_html__( 'Dot Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-status-indicator' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'status_online_color',
			array(
				'label'     => esc_html__( 'Online Color Status', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-status-online' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'status_lunch_color',
			array(
				'label'     => esc_html__( 'Lunch Break / Away Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-status-lunch' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'status_offline_color',
			array(
				'label'     => esc_html__( 'Offline Color Status', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-status-offline' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 11. Style Tab - QR Code Modal
		$this->start_controls_section(
			'section_qr_modal_style',
			array(
				'label'     => esc_html__( 'QR Code Scan Modal', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_qr' => 'yes',
				),
			)
		);

		$this->add_control(
			'qr_backdrop_bg',
			array(
				'label'     => esc_html__( 'Backdrop Overlay Bg', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.tv-sca-qr-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'qr_modal_bg',
			array(
				'label'     => esc_html__( 'Modal Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-qr-modal' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'qr_modal_width',
			array(
				'label'      => esc_html__( 'Modal Width (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-qr-modal' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'qr_modal_padding',
			array(
				'label'      => esc_html__( 'Modal Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-qr-modal' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'qr_modal_radius',
			array(
				'label'      => esc_html__( 'Modal Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-qr-modal' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'qr_modal_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-qr-modal',
			)
		);

		$this->add_control(
			'qr_header_title_color',
			array(
				'label'     => esc_html__( 'Header Title Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-qr-header span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qr_header_title_typo',
				'selector' => '{{WRAPPER}} .tv-sca-qr-header span',
			)
		);

		$this->start_controls_tabs( 'tabs_qr_close_btn' );

		$this->start_controls_tab(
			'tab_qr_close_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'qr_close_color',
			array(
				'label'     => esc_html__( 'Close Button Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-qr-close' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_qr_close_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'qr_close_color_hover',
			array(
				'label'     => esc_html__( 'Close Button Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-qr-close:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 12. Style Tab - Stacks & Toolbar Panels
		$this->start_controls_section(
			'section_stacks_toolbar_style',
			array(
				'label'     => esc_html__( 'Stacks & Toolbar Panels', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout_type' => array( 'vertical', 'horizontal', 'mini_toolbar' ),
				),
			)
		);

		$this->add_control(
			'stack_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-sca-stack-wrapper' => 'background-color: {{VALUE}}; background-image: none;',
					'{{WRAPPER}} .tv-sca-toolbar-panel' => 'background-color: {{VALUE}}; background-image: none;',
				),
			)
		);

		$this->add_responsive_control(
			'stack_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-stack-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .tv-sca-toolbar-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'stack_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-sca-stack-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .tv-sca-toolbar-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'stack_border',
				'selector' => '{{WRAPPER}} .tv-sca-stack-wrapper, {{WRAPPER}} .tv-sca-toolbar-panel',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'stack_shadow',
				'selector' => '{{WRAPPER}} .tv-sca-stack-wrapper, {{WRAPPER}} .tv-sca-toolbar-panel',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Prep business hour schedules for JSON encoding
		$schedules = array();
		if ( 'yes' === $settings['enable_hours'] && ! empty( $settings['hours_schedule'] ) ) {
			foreach ( $settings['hours_schedule'] as $slot ) {
				$schedules[] = array(
					'day'        => esc_attr( $slot['day_of_week'] ),
					'open'       => esc_attr( $slot['open_time'] ),
					'close'      => esc_attr( $slot['close_time'] ),
					'lunchStart' => esc_attr( $slot['lunch_start'] ),
					'lunchEnd'   => esc_attr( $slot['lunch_end'] ),
				);
			}
		}

		// Core JSON frontend JS configuration
		$js_config = array(
			'id'              => esc_attr( $this->get_id() ),
			'layout'          => esc_attr( $settings['layout_type'] ),
			'position'        => esc_attr( $settings['bar_position'] ),
			'enableHours'     => ( 'yes' === $settings['enable_hours'] ),
			'timezone'        => esc_attr( $settings['business_timezone'] ),
			'schedule'        => $schedules,
			'enableQr'        => ( 'yes' === $settings['enable_qr'] ),
			'qrType'          => esc_attr( $settings['qr_type'] ),
			'qrVal'           => esc_attr( $settings['qr_custom_value'] ),
			'vcardName'       => esc_attr( $settings['qr_vcard_name'] ),
			'vcardPhone'      => esc_attr( $settings['qr_vcard_phone'] ),
			'vcardEmail'      => esc_attr( $settings['qr_vcard_email'] ),
			'smartVisibility' => ( 'yes' === $settings['smart_visibility'] ),
			'tracking'        => ( 'yes' === $settings['enable_analytics_tracking'] ),
		);

		// Docking class positions
		$align_class = 'tv-sca-align-right';
		$dock_pos    = isset( $settings['bar_position'] ) ? $settings['bar_position'] : 'bottom-right';
		if ( strpos( $dock_pos, 'left' ) !== false ) {
			$align_class = 'tv-sca-align-left';
		}

		$pos_class    = 'tv-sca-pos-' . esc_attr( $dock_pos );
		$layout_class = 'tv-sca-layout-' . esc_attr( $settings['layout_type'] );
		$preset_class = 'tv-sca-bg-' . esc_attr( $settings['bg_preset'] );
		$anim_class   = 'tv-sca-anim-' . esc_attr( $settings['attention_anim'] );

		// Neumorphism dynamic angles
		$style_attrs = '';
		if ( 'neumorphism' === $settings['bg_preset'] && isset( $settings['neumorphism_light_angle']['size'] ) ) {
			$style_attrs = ' style="--tv-sca-neumorphic-angle: ' . (int) $settings['neumorphism_light_angle']['size'] . 'deg;"';
		}

		?>
		<div class="tv-sca-container <?php echo esc_attr( $align_class ); ?> <?php echo esc_attr( $pos_class ); ?> <?php echo esc_attr( $layout_class ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $js_config ) ); ?>">
			
			<?php
			// Render dynamic layouts
			if ( 'single' === $settings['layout_type'] ) :
				$first_item  = ! empty( $settings['action_items'][0] ) ? $settings['action_items'][0] : array();
				$action_link = $this->get_action_link( $first_item );
				?>
				<a href="<?php echo esc_url( $action_link ); ?>" class="tv-sca-trigger-btn <?php echo esc_attr( $preset_class ); ?> <?php echo esc_attr( $anim_class ); ?>" aria-label="<?php echo esc_attr( $first_item['label'] ); ?>" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( ! empty( $first_item['item_icon'] ) ) : ?>
						<?php Icons_Manager::render_icon( $first_item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['enable_badge'] ) : ?>
						<span class="tv-sca-badge"><?php echo esc_html( $settings['badge_val'] ); ?></span>
					<?php endif; ?>
				</a>

			<?php elseif ( 'speed_dial' === $settings['layout_type'] || 'radial' === $settings['layout_type'] ) : ?>
				<div class="tv-sca-item-list" style="display: none;">
					<?php
					foreach ( $settings['action_items'] as $index => $item ) :
						$item_link  = $this->get_action_link( $item );
						$tooltip_id = 'tv-sca-tooltip-' . $this->get_id() . '-' . $index;
						?>
						<div class="tv-sca-item" data-action="<?php echo esc_attr( $item['action_type'] ); ?>">
							<a href="<?php echo esc_url( $item_link ); ?>" class="tv-sca-item-btn" aria-describedby="<?php echo esc_attr( $tooltip_id ); ?>">
								<?php if ( ! empty( $item['item_icon'] ) ) : ?>
									<?php Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
								<?php endif; ?>
							</a>
							<?php if ( ! empty( $item['tooltip_text'] ) ) : ?>
								<span id="<?php echo esc_attr( $tooltip_id ); ?>" class="tv-sca-tooltip" role="tooltip"><?php echo esc_html( $item['tooltip_text'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<button class="tv-sca-trigger-btn <?php echo esc_attr( $preset_class ); ?> <?php echo esc_attr( $anim_class ); ?>" aria-expanded="false" aria-haspopup="true" aria-label="<?php echo esc_attr__( 'Toggle Contact Options', 'elonix' ); ?>" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="tv-sca-trigger-icon-open">
						<i class="fas fa-comment-alt" aria-hidden="true"></i>
					</span>
					<span class="tv-sca-trigger-icon-close" style="display: none;">
						<i class="fas fa-times" aria-hidden="true"></i>
					</span>
					<?php if ( 'yes' === $settings['enable_badge'] ) : ?>
						<span class="tv-sca-badge"><?php echo esc_html( $settings['badge_val'] ); ?></span>
					<?php endif; ?>
				</button>

			<?php elseif ( 'vertical' === $settings['layout_type'] || 'horizontal' === $settings['layout_type'] ) : ?>
				<div class="tv-sca-stack-wrapper <?php echo esc_attr( $preset_class ); ?>" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php
					foreach ( $settings['action_items'] as $index => $item ) :
						$item_link  = $this->get_action_link( $item );
						$tooltip_id = 'tv-sca-tooltip-' . $this->get_id() . '-' . $index;
						?>
						<div class="tv-sca-item" data-action="<?php echo esc_attr( $item['action_type'] ); ?>">
							<a href="<?php echo esc_url( $item_link ); ?>" class="tv-sca-item-btn" aria-describedby="<?php echo esc_attr( $tooltip_id ); ?>">
								<?php if ( ! empty( $item['item_icon'] ) ) : ?>
									<?php Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
								<?php endif; ?>
							</a>
							<?php if ( ! empty( $item['tooltip_text'] ) ) : ?>
								<span id="<?php echo esc_attr( $tooltip_id ); ?>" class="tv-sca-tooltip" role="tooltip"><?php echo esc_html( $item['tooltip_text'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

			<?php elseif ( 'contact_card' === $settings['layout_type'] ) : ?>
				<div class="tv-sca-card-drawer <?php echo esc_attr( $preset_class ); ?>" style="display: none;" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="tv-sca-card-header">
						<h4><?php echo esc_html__( 'Smart Support Hub', 'elonix' ); ?></h4>
						<p><?php echo esc_html__( 'Choose an agent below to begin talking.', 'elonix' ); ?></p>
					</div>

					<div class="tv-sca-card-body">
						<?php
						foreach ( $settings['action_items'] as $index => $item ) :
							$item_link = $this->get_action_link( $item );
							?>
							<div class="tv-sca-agent-row" data-action="<?php echo esc_attr( $item['action_type'] ); ?>">
								<?php if ( 'whatsapp' === $item['action_type'] && ! empty( $item['agent_photo']['url'] ) ) : ?>
									<div class="tv-sca-agent-avatar">
										<img src="<?php echo esc_url( $item['agent_photo']['url'] ); ?>" alt="<?php echo esc_attr( $item['label'] ); ?>" loading="lazy" />
										<span class="tv-sca-status-indicator tv-sca-status-offline" title="<?php echo esc_attr__( 'Offline', 'elonix' ); ?>"></span>
									</div>
								<?php endif; ?>
								
								<div class="tv-sca-agent-info">
									<h5><?php echo esc_html( $item['label'] ); ?></h5>
									<?php if ( ! empty( $item['agent_dept'] ) ) : ?>
										<span><?php echo esc_html( $item['agent_dept'] ); ?></span>
									<?php endif; ?>
								</div>

								<a href="<?php echo esc_url( $item_link ); ?>" class="tv-sca-agent-link-btn" aria-label="<?php echo esc_attr( $item['label'] ); ?>">
									<?php if ( ! empty( $item['item_icon'] ) ) : ?>
										<?php Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
									<?php endif; ?>
								</a>
							</div>
							
							<?php if ( 'inline_form' === $item['action_type'] && ! empty( $item['action_value'] ) ) : ?>
								<div class="tv-sca-inline-form-wrapper">
									<?php echo do_shortcode( $item['action_value'] ); ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

				<button class="tv-sca-trigger-btn <?php echo esc_attr( $preset_class ); ?> <?php echo esc_attr( $anim_class ); ?>" aria-expanded="false" aria-haspopup="true" aria-label="<?php echo esc_attr__( 'Open Contact Card', 'elonix' ); ?>" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="tv-sca-trigger-icon-open">
						<i class="fas fa-envelope-open-text" aria-hidden="true"></i>
					</span>
					<span class="tv-sca-trigger-icon-close" style="display: none;">
						<i class="fas fa-times" aria-hidden="true"></i>
					</span>
					<?php if ( 'yes' === $settings['enable_badge'] ) : ?>
						<span class="tv-sca-badge"><?php echo esc_html( $settings['badge_val'] ); ?></span>
					<?php endif; ?>
				</button>

			<?php elseif ( 'floating_mini_toolbar' === $settings['layout_type'] || 'mini_toolbar' === $settings['layout_type'] ) : ?>
				<div class="tv-sca-toolbar-panel <?php echo esc_attr( $preset_class ); ?>" <?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php
					foreach ( $settings['action_items'] as $index => $item ) :
						$item_link  = $this->get_action_link( $item );
						$tooltip_id = 'tv-sca-tooltip-' . $this->get_id() . '-' . $index;
						?>
						<div class="tv-sca-item" data-action="<?php echo esc_attr( $item['action_type'] ); ?>">
							<a href="<?php echo esc_url( $item_link ); ?>" class="tv-sca-item-btn" aria-describedby="<?php echo esc_attr( $tooltip_id ); ?>">
								<?php if ( ! empty( $item['item_icon'] ) ) : ?>
									<?php Icons_Manager::render_icon( $item['item_icon'], array( 'aria-hidden' => 'true' ) ); ?>
								<?php endif; ?>
							</a>
							<?php if ( ! empty( $item['tooltip_text'] ) ) : ?>
								<span id="<?php echo esc_attr( $tooltip_id ); ?>" class="tv-sca-tooltip" role="tooltip"><?php echo esc_html( $item['tooltip_text'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['enable_qr'] ) : ?>
				<div class="tv-sca-qr-modal" style="display: none;">
					<div class="tv-sca-qr-header">
						<span><?php echo esc_html__( 'Scan to Chat Mobile', 'elonix' ); ?></span>
						<button class="tv-sca-qr-close" aria-label="<?php echo esc_attr__( 'Close Modal', 'elonix' ); ?>">&times;</button>
					</div>
					<div class="tv-sca-qr-canvas-wrapper">
						<canvas class="tv-sca-qr-canvas"></canvas>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Build dynamic anchor connection protocol values.
	 *
	 * @param array $item The layout item configuration options.
	 * @return string Dynamic URI target.
	 */
	protected function get_action_link( $item ) {
		$type = isset( $item['action_type'] ) ? $item['action_type'] : 'custom_url';
		$val  = isset( $item['action_value'] ) ? $item['action_value'] : '';

		switch ( $type ) {
			case 'whatsapp':
				$message = '';
				if ( isset( $item['chat_template'] ) && 'none' !== $item['chat_template'] ) {
					if ( 'custom' === $item['chat_template'] && ! empty( $item['custom_chat_message'] ) ) {
						$message = '?text=' . rawurlencode( $item['custom_chat_message'] );
					} else {
						$templates = array(
							'hello'         => 'Hello there!',
							'need_support'  => 'Hi, I need support helper',
							'request_quote' => 'I want a project quote',
							'contact_sales' => 'Connect me to Sales team',
						);
						if ( isset( $templates[ $item['chat_template'] ] ) ) {
							$message = '?text=' . rawurlencode( $templates[ $item['chat_template'] ] );
						}
					}
				}
				return 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $val ) . $message;

			case 'call':
				return 'tel:' . preg_replace( '/[^0-9+]/', '', $val );

			case 'email':
				return 'mailto:' . sanitize_email( $val );

			case 'telegram':
				return 'https://t.me/' . esc_attr( $val );

			case 'messenger':
				return 'https://m.me/' . esc_attr( $val );

			case 'skype':
				return 'skype:' . esc_attr( $val ) . '?chat';

			case 'facebook':
			case 'instagram':
			case 'linkedin':
			case 'x_twitter':
			case 'youtube':
			case 'tiktok':
			case 'custom_url':
				return ! empty( $val ) ? esc_url( $val ) : '#';

			case 'inline_form':
			default:
				return '#';
		}
	}
}
