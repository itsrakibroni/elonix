<?php
namespace Elementor;

/**
 * Theme Sidebar Component (Updated)
 *
 * @package ElonixToolkit
 * @subpackage Components
 * @since 1.0.0
 * @version 2.0.0
 * @author Creative RakibRoni
 *
 * Usage: tv_sidebar_controls( $this, 'my_sidebar', __( 'Sidebar Settings', 'elonix' ));
 * Render: tv_render_sidebar( $settings, 'my_sidebar' );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register Sidebar Controls
 *
 * @param object $widget The widget instance
 * @param string $prefix Control prefix for unique naming
 * @param string $section_label Section label
 */
if ( ! function_exists( 'tv_sidebar_menu_controls' ) ) :
	function tv_sidebar_menu_controls( $widget, $prefix = 'sidebar', $section_label = 'Sidebar Settings' ) {

		// ==================== Sidebar Settings Section ====================
		$widget->start_controls_section(
			$prefix . '_section',
			array(
				'label' => esc_htmlesc_html( $section_label ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$widget->add_control(
			$prefix . '_enable',
			array(
				'label'        => esc_html__( 'Enable Sidebar', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// ==================== Trigger Button ====================
		$widget->add_control(
			$prefix . '_trigger_heading',
			array(
				'label'     => esc_html__( 'Trigger Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_trigger_type',
			array(
				'label'     => esc_html__( 'Trigger Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'image',
				'options'   => array(
					'image' => esc_html__( 'Image', 'elonix' ),
					'icon'  => esc_html__( 'Icon', 'elonix' ),
					'text'  => esc_html__( 'Text', 'elonix' ),
				),
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_trigger_image',
			array(
				'label'     => esc_html__( 'Trigger Image', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					$prefix . '_enable'       => 'yes',
					$prefix . '_trigger_type' => 'image',
				),
			)
		);

		$widget->add_control(
			$prefix . '_trigger_icon',
			array(
				'label'     => esc_html__( 'Trigger Icon', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-bars',
					'library' => 'solid',
				),
				'condition' => array(
					$prefix . '_enable'       => 'yes',
					$prefix . '_trigger_type' => 'icon',
				),
			)
		);

		$widget->add_control(
			$prefix . '_trigger_text',
			array(
				'label'     => esc_html__( 'Trigger Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Open Sidebar', 'elonix' ),
				'condition' => array(
					$prefix . '_enable'       => 'yes',
					$prefix . '_trigger_type' => 'text',
				),
			)
		);

		// ==================== Sidebar Logo ====================
		$widget->add_control(
			$prefix . '_logo_heading',
			array(
				'label'     => esc_html__( 'Sidebar Logo', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_logo',
			array(
				'label'        => esc_html__( 'Show Logo', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_logo',
			array(
				'label'     => esc_html__( 'Logo Image', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					$prefix . '_enable'    => 'yes',
					$prefix . '_show_logo' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_logo_url',
			array(
				'label'       => esc_html__( 'Logo URL', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array(
					'url' => home_url( '/' ),
				),
				'condition'   => array(
					$prefix . '_enable'    => 'yes',
					$prefix . '_show_logo' => 'yes',
				),
			)
		);

		// ==================== Sidebar Menu ====================
		$widget->add_control(
			$prefix . '_menu_heading',
			array(
				'label'     => esc_html__( 'Sidebar Menu', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_menu',
			array(
				'label'        => esc_html__( 'Show Menu', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// ==================== About Section ====================
		$widget->add_control(
			$prefix . '_about_heading',
			array(
				'label'     => esc_html__( 'About Section', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_about',
			array(
				'label'        => esc_html__( 'Show About Section', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_about_subtitle',
			array(
				'label'     => esc_html__( 'Subtitle', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Inovative IT Solutions', 'elonix' ),
				'condition' => array(
					$prefix . '_enable'     => 'yes',
					$prefix . '_show_about' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_about_title',
			array(
				'label'     => esc_html__( 'Title', 'elonix' ),
				'type'      => Controls_Manager::TEXTAREA,
				'default'   => esc_html__( 'World\'s leading Business agency', 'elonix' ),
				'condition' => array(
					$prefix . '_enable'     => 'yes',
					$prefix . '_show_about' => 'yes',
				),
			)
		);

		// ==================== Social Feed ====================
		$widget->add_control(
			$prefix . '_social_feed_heading',
			array(
				'label'     => esc_html__( 'Social Feed', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_social_feed',
			array(
				'label'        => esc_html__( 'Show Social Feed', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$repeater_social_feed = new Repeater();

		$repeater_social_feed->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Image', 'elonix' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$repeater_social_feed->add_control(
			'url',
			array(
				'label'       => esc_html__( 'URL', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array(
					'url'         => '#',
					'is_external' => true,
				),
			)
		);

		$repeater_social_feed->add_control(
			'icon',
			array(
				'label'   => esc_html__( 'Overlay Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fab fa-instagram',
					'library' => 'brands',
				),
			)
		);

		$repeater_social_feed->add_control(
			'platform',
			array(
				'label'       => esc_html__( 'Platform Name', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Instagram', 'elonix' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			$prefix . '_social_feed_items',
			array(
				'label'       => esc_html__( 'Social Feed Items', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater_social_feed->get_controls(),
				'default'     => array(
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.instagram.com' ),
						'icon'     => array(
							'value'   => 'fab fa-instagram',
							'library' => 'brands',
						),
						'platform' => 'Instagram',
					),
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.facebook.com' ),
						'icon'     => array(
							'value'   => 'fab fa-facebook-f',
							'library' => 'brands',
						),
						'platform' => 'Facebook',
					),
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.twitter.com' ),
						'icon'     => array(
							'value'   => 'fab fa-x-twitter',
							'library' => 'brands',
						),
						'platform' => 'Twitter',
					),
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.linkedin.com' ),
						'icon'     => array(
							'value'   => 'fab fa-linkedin-in',
							'library' => 'brands',
						),
						'platform' => 'LinkedIn',
					),
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.youtube.com' ),
						'icon'     => array(
							'value'   => 'fab fa-youtube',
							'library' => 'brands',
						),
						'platform' => 'YouTube',
					),
					array(
						'image'    => array( 'url' => '' ),
						'url'      => array( 'url' => 'https://www.pinterest.com' ),
						'icon'     => array(
							'value'   => 'fab fa-pinterest-p',
							'library' => 'brands',
						),
						'platform' => 'Pinterest',
					),
				),
				'title_field' => '{{{ platform }}}',
				'condition'   => array(
					$prefix . '_enable'           => 'yes',
					$prefix . '_show_social_feed' => 'yes',
				),
			)
		);

		// ==================== Newsletter ====================
		$widget->add_control(
			$prefix . '_newsletter_heading',
			array(
				'label'     => esc_html__( 'Newsletter', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_newsletter',
			array(
				'label'        => esc_html__( 'Show Newsletter', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_newsletter_text',
			array(
				'label'     => esc_html__( 'Newsletter Text', 'elonix' ),
				'type'      => Controls_Manager::TEXTAREA,
				'default'   => esc_html__( 'Get latest update for our trusted applications', 'elonix' ),
				'condition' => array(
					$prefix . '_enable'          => 'yes',
					$prefix . '_show_newsletter' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_newsletter_type',
			array(
				'label'     => esc_html__( 'Newsletter Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'mailchimp',
				'options'   => array(
					'mailchimp' => esc_html__( 'Mailchimp', 'elonix' ),
					'custom'    => esc_html__( 'Custom Form', 'elonix' ),
				),
				'condition' => array(
					$prefix . '_enable'          => 'yes',
					$prefix . '_show_newsletter' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_mailchimp_action',
			array(
				'label'       => esc_html__( 'Mailchimp Form Action URL', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://yoursite.us1.list-manage.com/subscribe/post?u=...', 'elonix' ),
				'description' => esc_html__( 'Get this from: Mailchimp → Audience → Signup forms → Embedded forms', 'elonix' ),
				'condition'   => array(
					$prefix . '_enable'          => 'yes',
					$prefix . '_show_newsletter' => 'yes',
					$prefix . '_newsletter_type' => 'mailchimp',
				),
			)
		);

		$widget->add_control(
			$prefix . '_newsletter_action',
			array(
				'label'       => esc_html__( 'Form Action URL', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://formspree.io/f/yourform', 'elonix' ),
				'default'     => '',
				'condition'   => array(
					$prefix . '_enable'          => 'yes',
					$prefix . '_show_newsletter' => 'yes',
					$prefix . '_newsletter_type' => 'custom',
				),
			)
		);

		$widget->add_control(
			$prefix . '_newsletter_placeholder',
			array(
				'label'     => esc_html__( 'Email Placeholder', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Enter Your Email', 'elonix' ),
				'condition' => array(
					$prefix . '_enable'          => 'yes',
					$prefix . '_show_newsletter' => 'yes',
				),
			)
		);

		// ==================== Social Links ====================
		$widget->add_control(
			$prefix . '_social_heading',
			array(
				'label'     => esc_html__( 'Social Links', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->add_control(
			$prefix . '_show_social',
			array(
				'label'        => esc_html__( 'Show Social Links', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->end_controls_section();

		// =================================================================
		// ==================== STYLE TABS START ===========================
		// =================================================================

		// ==================== Trigger Button Style ====================
		$widget->start_controls_section(
			$prefix . '_trigger_style_section',
			array(
				'label'     => esc_html__( 'Trigger Button', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( $prefix . '_enable' => 'yes' ),
			)
		);

		$widget->start_controls_tabs( $prefix . '_trigger_tabs' );

		// Normal State
		$widget->start_controls_tab( $prefix . '_trigger_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );

		$widget->add_control(
			$prefix . '_trigger_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->add_control(
			$prefix . '_trigger_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger' => 'background-color: {{VALUE}} !important;' ),
			)
		);
		$widget->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $prefix . '_trigger_border',
				'selector' => '{{WRAPPER}} .sidebar-trigger',
			)
		);

		$widget->end_controls_tab();

		// Hover State
		$widget->start_controls_tab( $prefix . '_trigger_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$widget->add_control(
			$prefix . '_trigger_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger:hover' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->add_control(
			$prefix . '_trigger_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger:hover' => 'background-color: {{VALUE}} !important;' ),
			)
		);
		$widget->add_control(
			$prefix . '_trigger_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger:hover' => 'border-color: {{VALUE}} !important;' ),
				'condition' => array( $prefix . '_trigger_border_border!' => '' ),
			)
		);
		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => $prefix . '_trigger_typography',
				'selector'  => '{{WRAPPER}} .sidebar-trigger',
				'condition' => array( $prefix . '_trigger_type' => 'text' ),
				'separator' => 'before',
			)
		);

		$widget->add_responsive_control(
			$prefix . '_trigger_icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger i' => 'font-size: {{SIZE}}{{UNIT}} !important;' ),
				'condition' => array( $prefix . '_trigger_type' => 'icon' ),
			)
		);

		$widget->add_responsive_control(
			$prefix . '_trigger_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .sidebar-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
				'separator'  => 'before',
			)
		);

		$widget->add_control(
			$prefix . '_trigger_border_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .sidebar-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);

		$widget->end_controls_section();

		// ==================== Sidebar Container Style ====================
		$widget->start_controls_section(
			$prefix . '_container_style_section',
			array(
				'label'     => esc_html__( 'Sidebar Container', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( $prefix . '_enable' => 'yes' ),
			)
		);

		$widget->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => $prefix . '_background',
				'label'    => __( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .sidebar-wrapper',
			)
		);

		$widget->add_responsive_control(
			$prefix . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .sidebar-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);

		$widget->add_control(
			$prefix . '_overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-overlay' => 'background-color: {{VALUE}} !important;' ),
			)
		);

		$widget->add_control(
			$prefix . '_close_button_heading',
			array(
				'label'     => esc_html__( 'Close Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			$prefix . '_close_button_color',
			array(
				'label'     => esc_html__( 'Close Button Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-close-btn' => 'color: {{VALUE}} !important;' ),
			)
		);

		$widget->add_control(
			$prefix . '_close_button_hover_color',
			array(
				'label'     => esc_html__( 'Close Button Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-close-btn:hover' => 'color: {{VALUE}} !important;' ),
			)
		);

		$widget->end_controls_section();

		// ==================== Content Style ====================
		$widget->start_controls_section(
			$prefix . '_content_style_section',
			array(
				'label'     => esc_html__( 'Sidebar Content', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( $prefix . '_enable' => 'yes' ),
			)
		);

		$widget->add_control(
			$prefix . '_text_color',
			array(
				'label'     => esc_html__( 'General Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sidebar-content',
					'{{WRAPPER}} .sidebar-content p',
				),
			)
		);

		$widget->add_control(
			$prefix . '_heading_color',
			array(
				'label'     => esc_html__( 'Headings Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sidebar-content h1',
					'{{WRAPPER}} .sidebar-content h2',
					'{{WRAPPER}} .sidebar-content h3',
					'{{WRAPPER}} .sidebar-content h4',
					'{{WRAPPER}} .sidebar-content h5',
					'{{WRAPPER}} .sidebar-content h6',
				),
			)
		);

		$widget->add_control(
			$prefix . '_link_color',
			array(
				'label'     => esc_html__( 'Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-content a' => 'color: {{VALUE}} !important;' ),
			)
		);

		$widget->add_control(
			$prefix . '_link_hover_color',
			array(
				'label'     => esc_html__( 'Link Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-content a:hover' => 'color: {{VALUE}} !important;' ),
			)
		);

		$widget->end_controls_section();

		// ==================== Menu Style ====================
		$widget->start_controls_section(
			$prefix . '_menu_style_section',
			array(
				'label'     => esc_html__( 'Menu', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( $prefix . '_show_menu' => 'yes' ),
			)
		);

		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $prefix . '_menu_typography',
				'selector' => '{{WRAPPER}} .sidebar-menu-wrap .sidebar-menu > li > a',
			)
		);

		$widget->start_controls_tabs( $prefix . '_menu_link_tabs' );
		$widget->start_controls_tab( $prefix . '_menu_link_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$widget->add_control(
			$prefix . '_menu_link_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-menu-wrap .sidebar-menu > li > a' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->end_controls_tab();

		$widget->start_controls_tab( $prefix . '_menu_link_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$widget->add_control(
			$prefix . '_menu_link_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-menu-wrap .sidebar-menu > li > a:hover' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		$widget->end_controls_section();

		// ==================== Social Icons Style ====================
		$widget->start_controls_section(
			$prefix . '_social_style_section',
			array(
				'label'     => esc_html__( 'Social Links', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( $prefix . '_show_social' => 'yes' ),
			)
		);

		$widget->add_responsive_control(
			$prefix . '_social_icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .sidebar-social a' => 'font-size: {{SIZE}}{{UNIT}} !important;' ),
			)
		);

		$widget->start_controls_tabs( $prefix . '_social_tabs' );
		$widget->start_controls_tab( $prefix . '_social_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$widget->add_control(
			$prefix . '_social_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-social a' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->end_controls_tab();
		$widget->start_controls_tab( $prefix . '_social_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$widget->add_control(
			$prefix . '_social_icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sidebar-social a:hover' => 'color: {{VALUE}} !important;' ),
			)
		);
		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		$widget->end_controls_section();
	}
endif;

/**
 * Render Sidebar HTML (Updated - Form Action Hidden from Frontend)
 *
 * @param array $settings Widget settings
 * @param string $prefix Control prefix
 */
if ( ! function_exists( 'tv_render_sidebar' ) ) :
	function tv_render_sidebar( $settings, $prefix = 'sidebar' ) {

		if ( $settings[ $prefix . '_enable' ] !== 'yes' ) {
			return;
		}
		?>

		<!-- Sidebar Trigger Button -->
		<div class="sidebar-icon">
			<button class="sidebar-trigger open">
				<?php if ( $settings[ $prefix . '_trigger_type' ] === 'image' && ! empty( $settings[ $prefix . '_trigger_image' ]['url'] ) ) : ?>
					<img src="<?php echo esc_url( $settings[ $prefix . '_trigger_image' ]['url'] ); ?>" alt="<?php echo esc_attr__( 'Sidebar Toggle', 'elonix' ); ?>">
				<?php elseif ( $settings[ $prefix . '_trigger_type' ] === 'icon' ) : ?>
					<?php \Elementor\Icons_Manager::render_icon( $settings[ $prefix . '_trigger_icon' ], array( 'aria-hidden' => 'true' ) ); ?>
				<?php elseif ( $settings[ $prefix . '_trigger_type' ] === 'text' ) : ?>
					<?php echo esc_html( $settings[ $prefix . '_trigger_text' ] ); ?>
				<?php endif; ?>
			</button>
		</div>

		<!-- Sidebar Area -->
		<div id="sidebar-area" class="elonix-sidebar">
			<div class="sidebar-overlay"></div>
			<div class="sidebar-wrapper">
				<!-- Close Button -->
				<button class="sidebar-close-btn">
					<svg class="icon-close" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="12.7px" viewBox="0 0 16 12.7" style="enable-background:new 0 0 16 12.7" xml:space="preserve">
						<g>
							<rect x="0" y="5.4" transform="matrix(0.7071 -0.7071 0.7071 0.7071 -2.1569 7.5208)" width="16" height="2"></rect>
							<rect x="0" y="5.4" transform="matrix(0.7071 0.7071 -0.7071 0.7071 6.8431 -3.7929)" width="16" height="2"></rect>
						</g>
					</svg>
				</button>

				<div class="sidebar-content">

					<!-- Logo -->
					<?php
					if ( $settings[ $prefix . '_show_logo' ] === 'yes' && ! empty( $settings[ $prefix . '_logo' ]['url'] ) ) :
						$logo_url = ! empty( $settings[ $prefix . '_logo_url' ]['url'] ) ? $settings[ $prefix . '_logo_url' ]['url'] : home_url( '/' );
						$target   = ! empty( $settings[ $prefix . '_logo_url' ]['is_external'] ) ? ' target="_blank"' : '';
						$nofollow = ! empty( $settings[ $prefix . '_logo_url' ]['nofollow'] ) ? ' rel="nofollow"' : '';
						?>
					<div class="sidebar-logo">
						<a class="dark-logo" href="<?php echo esc_url( $logo_url ); ?>"<?php echo $target . $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<img src="<?php echo esc_url( $settings[ $prefix . '_logo' ]['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						</a>
					</div>
					<?php endif; ?>

					<!-- Menu -->
					<?php if ( $settings[ $prefix . '_show_menu' ] === 'yes' ) : ?>
					<div class="sidebar-menu-wrap"></div>
					<?php endif; ?>

					<!-- About Section -->
					<?php if ( $settings[ $prefix . '_show_about' ] === 'yes' ) : ?>
					<div class="sidebar-about">
						<?php if ( ! empty( $settings[ $prefix . '_about_subtitle' ] ) ) : ?>
						<h6><?php echo esc_html( $settings[ $prefix . '_about_subtitle' ] ); ?></h6>
						<?php endif; ?>
						<?php if ( ! empty( $settings[ $prefix . '_about_title' ] ) ) : ?>
						<div class="sidebar-header">
							<h3><?php echo wp_kses_post( $settings[ $prefix . '_about_title' ] ); ?></h3>
						</div>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<!-- Social Feed -->
					<?php if ( $settings[ $prefix . '_show_social_feed' ] === 'yes' && ! empty( $settings[ $prefix . '_social_feed_items' ] ) ) : ?>
					<div class="social-feed-wrapper">
						<?php
						foreach ( $settings[ $prefix . '_social_feed_items' ] as $item ) :
							if ( empty( $item['image']['url'] ) ) {
								continue;
							}
							$target   = ! empty( $item['url']['is_external'] ) ? ' target="_blank"' : '';
							$nofollow = ! empty( $item['url']['nofollow'] ) ? ' rel="nofollow"' : '';
							$feed_url = ! empty( $item['url']['url'] ) ? $item['url']['url'] : '#';
							$platform = ! empty( $item['platform'] ) ? $item['platform'] : 'Social';
							?>
						<div class="social-feed-item">
							<a href="<?php echo esc_url( $feed_url ); ?>"<?php echo $target . $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> title="<?php echo esc_attr( $platform ); ?>">
								<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $platform ); ?>">
								<span class="overlay">
									<?php \Elementor\Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
								</span>
							</a>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<!-- Newsletter -->
					<?php
					if ( $settings[ $prefix . '_show_newsletter' ] === 'yes' ) :
						$newsletter_type = isset( $settings[ $prefix . '_newsletter_type' ] ) ? $settings[ $prefix . '_newsletter_type' ] : 'mailchimp';
						$placeholder     = isset( $settings[ $prefix . '_newsletter_placeholder' ] ) ? $settings[ $prefix . '_newsletter_placeholder' ] : __( 'Enter Your Email', 'elonix' );

						// Get form action
						if ( $newsletter_type === 'mailchimp' ) {
							$form_action = isset( $settings[ $prefix . '_mailchimp_action' ] ) ? trim( $settings[ $prefix . '_mailchimp_action' ] ) : '';
						} else {
							$form_action = isset( $settings[ $prefix . '_newsletter_action' ] ) ? trim( $settings[ $prefix . '_newsletter_action' ] ) : '';
						}

						$form_id          = 'sidebar-newsletter-' . uniqid();
						$has_action       = ! empty( $form_action );
						$form_action_attr = $has_action ? $form_action : '#';
						?>

						<?php if ( ! empty( $settings[ $prefix . '_newsletter_text' ] ) ) : ?>
					<p class="text-center mt-40"><?php echo esc_html( $settings[ $prefix . '_newsletter_text' ] ); ?></p>
					<?php endif; ?>

					<form id="<?php echo esc_attr( $form_id ); ?>" class="newsletter-form" method="post" data-newsletter-type="<?php echo esc_attr( $newsletter_type ); ?>" data-ajax-form="true"
										<?php
										if ( $has_action ) :
											?>
						data-form-action="<?php echo esc_attr( base64_encode( $form_action_attr ) ); ?>"
												<?php
else :
	?>
							action="#"<?php endif; ?>>

						<div class="form-group">
							<input type="email" name="<?php echo $newsletter_type === 'mailchimp' ? 'EMAIL' : 'email'; ?>" class="email" placeholder="<?php echo esc_attr( $placeholder ); ?>" required autocomplete="on">

							<?php if ( $newsletter_type === 'mailchimp' ) : ?>
							<div style="position: absolute; left: -5000px;" aria-hidden="true">
								<input type="text" name="b_<?php echo esc_attr( uniqid() ); ?>" tabindex="-1" value="">
							</div>
							<?php endif; ?>

							<button type="submit">
								<i class="far fa-paper-plane"></i>
							</button>
						</div>

						<?php if ( ! $has_action ) : ?>
						<small style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
							<?php echo esc_html__( '⚠️ Please configure form action URL in Elementor settings.', 'elonix' ); ?>
						</small>
						<?php endif; ?>
					</form>

						<?php if ( $has_action ) : ?>
					<script>
					(function() {
						const form = document.getElementById('<?php echo esc_js( $form_id ); ?>');
						if (form && form.dataset.formAction) {
							form.setAttribute('action', atob(form.dataset.formAction));
						}
					})();
					</script>
					<?php endif; ?>

					<?php endif; ?>

					<!-- Social Links -->
					<?php if ( $settings[ $prefix . '_show_social' ] === 'yes' ) : ?>
					<ul class="sidebar-social">
						<?php elonix_social_link(); ?>
					</ul>
					<?php endif; ?>

				</div>
			</div>
		</div>
		<?php
	}
endif;
