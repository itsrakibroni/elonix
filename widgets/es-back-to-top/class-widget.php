<?php
/**
 * Elonix – Toolkit for Elementor Back To Top Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

class Elonix_Toolkit_Back_To_Top_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-back-to-top';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Back To Top', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-arrow-up';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'back', 'top', 'scroll', 'progress', 'reading', 'time', 'eskit' );
	}

	/**
	 * Retrieve widget style dependency list.
	 *
	 * @return array Dependency handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-back-to-top' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependency handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-back-to-top' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

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
				'label'   => esc_html__( 'Layout Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => array(
					'circle'        => esc_html__( 'Floating Circle', 'elonix' ),
					'square'        => esc_html__( 'Floating Square', 'elonix' ),
					'rounded'       => esc_html__( 'Floating Rounded', 'elonix' ),
					'glass'         => esc_html__( 'Glassmorphism Button', 'elonix' ),
					'neumorphic'    => esc_html__( 'Neumorphism Button', 'elonix' ),
					'progress_ring' => esc_html__( 'Progress Ring Outline', 'elonix' ),
					'progress_bar'  => esc_html__( 'Horizontal Viewport Bar', 'elonix' ),
					'dot'           => esc_html__( 'Minimal Dot', 'elonix' ),
					'pill'          => esc_html__( 'Floating Pill', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'progress_mode',
			array(
				'label'   => esc_html__( 'Scroll Progress Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'circular',
				'options' => array(
					'none'     => esc_html__( 'None (Simple Button)', 'elonix' ),
					'circular' => esc_html__( 'Circular Progress Circle', 'elonix' ),
					'linear'   => esc_html__( 'Linear/Bar Progress', 'elonix' ),
					'percent'  => esc_html__( 'Numeric Percentage Counter', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'bar_position',
			array(
				'label'     => esc_html__( 'Progress Bar Position', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top',
				'options'   => array(
					'top'    => esc_html__( 'Top of Viewport', 'elonix' ),
					'bottom' => esc_html__( 'Bottom of Viewport', 'elonix' ),
				),
				'condition' => array(
					'layout_type' => 'progress_bar',
				),
			)
		);

		$this->add_control(
			'position_sticky',
			array(
				'label'     => esc_html__( 'Sticky Position Mode', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fixed',
				'options'   => array(
					'fixed'    => esc_html__( 'Fixed Viewport Floating', 'elonix' ),
					'absolute' => esc_html__( 'Absolute Page Boundary', 'elonix' ),
				),
				'condition' => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_responsive_control(
			'position_align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'right',
				'condition' => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->end_controls_section();

		// 2. Content Tab - Triggers & Actions
		$this->start_controls_section(
			'section_trigger_action',
			array(
				'label' => esc_html__( 'Triggers & Actions', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'trigger_type',
			array(
				'label'   => esc_html__( 'Show Button On', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'scroll_offset',
				'options' => array(
					'always'         => esc_html__( 'Always Visible', 'elonix' ),
					'scroll_offset'  => esc_html__( 'Scroll Vertical Offset (px)', 'elonix' ),
					'scroll_percent' => esc_html__( 'Scroll Percentage (%)', 'elonix' ),
					'after_selector' => esc_html__( 'After Custom Selector / Section', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'trigger_value_px',
			array(
				'label'     => esc_html__( 'Scroll Offset (px)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 300,
				'condition' => array(
					'trigger_type' => 'scroll_offset',
				),
			)
		);

		$this->add_control(
			'trigger_value_percent',
			array(
				'label'     => esc_html__( 'Scroll Percentage (%)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 25 ),
				'range'     => array(
					'%' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'condition' => array(
					'trigger_type' => 'scroll_percent',
				),
			)
		);

		$this->add_control(
			'trigger_selector',
			array(
				'label'       => esc_html__( 'Trigger CSS Selector', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. #hero-section, .entry-content', 'elonix' ),
				'condition'   => array(
					'trigger_type' => 'after_selector',
				),
			)
		);

		$this->add_control(
			'click_action',
			array(
				'label'   => esc_html__( 'Click/Tap Action', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'scroll_to_top',
				'options' => array(
					'scroll_to_top' => esc_html__( 'Scroll Back to Top', 'elonix' ),
					'scroll_target' => esc_html__( 'Scroll to Custom Target ID/Selector', 'elonix' ),
					'custom_link'   => esc_html__( 'Custom Action Redirect Link', 'elonix' ),
					'js_callback'   => esc_html__( 'Execute Javascript Callback', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'click_scroll_target',
			array(
				'label'       => esc_html__( 'Target CSS Selector', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. #primary, .content-area', 'elonix' ),
				'condition'   => array(
					'click_action' => 'scroll_target',
				),
			)
		);

		$this->add_control(
			'click_custom_url',
			array(
				'label'       => esc_html__( 'Action Redirect URL', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://elonix.net', 'elonix' ),
				'condition'   => array(
					'click_action' => 'custom_link',
				),
			)
		);

		$this->add_control(
			'click_js_callback',
			array(
				'label'       => esc_html__( 'JS Callback Function Name', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'elonixCustomAction', 'elonix' ),
				'description' => esc_html__( 'Ensure this global function is defined in script scope.', 'elonix' ),
				'condition'   => array(
					'click_action' => 'js_callback',
				),
			)
		);

		$this->end_controls_section();

		// 3. Content Tab - Smart Assistant & Reading Modes
		$this->start_controls_section(
			'section_reading_assistant',
			array(
				'label' => esc_html__( 'Smart Reading Assistant', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_reading_assistant',
			array(
				'label'        => esc_html__( 'Enable Reading Progress Engine', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'content_selector',
			array(
				'label'       => esc_html__( 'Article Content Selector', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '.entry-content, article, main',
				'placeholder' => esc_html__( 'e.g. .entry-content, article', 'elonix' ),
				'condition'   => array(
					'enable_reading_assistant' => 'yes',
				),
			)
		);

		$this->add_control(
			'reading_assistant_mode',
			array(
				'label'     => esc_html__( 'Text Display Mode', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'minutes_remaining',
				'options'   => array(
					'none'              => esc_html__( 'No Text Display', 'elonix' ),
					'minutes_total'     => esc_html__( 'Total Estimated Reading Time', 'elonix' ),
					'minutes_remaining' => esc_html__( 'Remaining Reading Time (Dynamic)', 'elonix' ),
				),
				'condition' => array(
					'enable_reading_assistant' => 'yes',
				),
			)
		);

		$this->add_control(
			'reading_speed_wpm',
			array(
				'label'     => esc_html__( 'WPM Reading Speed', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 200,
				'condition' => array(
					'enable_reading_assistant' => 'yes',
					'reading_assistant_mode!'  => 'none',
				),
			)
		);

		$this->add_control(
			'enable_icon_swap',
			array(
				'label'        => esc_html__( 'Swap Icon on Completion (100%)', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'enable_reading_assistant' => 'yes',
				),
			)
		);

		$this->add_control(
			'completion_icon',
			array(
				'label'     => esc_html__( 'Completion Swap Icon', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-check',
					'library' => 'solid',
				),
				'condition' => array(
					'enable_reading_assistant' => 'yes',
					'enable_icon_swap'         => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 4. Content Tab - Graphic Icon/Media Options
		$this->start_controls_section(
			'section_icon_media',
			array(
				'label' => esc_html__( 'Icon & Graphics Layout', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'icon_source',
			array(
				'label'   => esc_html__( 'Icon Graphic Source', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'library',
				'options' => array(
					'none'    => esc_html__( 'No Graphic (Text / Percent Only)', 'elonix' ),
					'library' => esc_html__( 'Elementor Icon Library', 'elonix' ),
					'svg'     => esc_html__( 'Upload Custom SVG', 'elonix' ),
					'image'   => esc_html__( 'Static Image Graphic', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'     => esc_html__( 'Select Font Icon', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-chevron-up',
					'library' => 'solid',
				),
				'condition' => array(
					'icon_source' => 'library',
				),
			)
		);

		$this->add_control(
			'selected_svg',
			array(
				'label'      => esc_html__( 'Upload SVG', 'elonix' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'image',
				'condition'  => array(
					'icon_source' => 'svg',
				),
			)
		);

		$this->add_control(
			'selected_image',
			array(
				'label'     => esc_html__( 'Upload Image', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'icon_source' => 'image',
				),
			)
		);

		$this->end_controls_section();

		// 5. Content Tab - Smart Visibility & Exclusions
		$this->start_controls_section(
			'section_visibility_rules',
			array(
				'label' => esc_html__( 'Smart Visibility Rules', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'inactivity_fade',
			array(
				'label'        => esc_html__( 'Inactivity Auto-Fade', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'inactivity_timeout',
			array(
				'label'     => esc_html__( 'Inactivity Timeout (Seconds)', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3,
				'condition' => array(
					'inactivity_fade' => 'yes',
				),
			)
		);

		$this->add_control(
			'footer_avoidance',
			array(
				'label'        => esc_html__( 'Footer Overlap Avoidance', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_control(
			'footer_selector',
			array(
				'label'       => esc_html__( 'Footer CSS Selector', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'footer, .site-footer',
				'placeholder' => esc_html__( 'e.g. footer, .site-footer', 'elonix' ),
				'condition'   => array(
					'footer_avoidance' => 'yes',
					'layout_type!'     => 'progress_bar',
				),
			)
		);

		$this->add_control(
			'hide_on_popups',
			array(
				'label'        => esc_html__( 'Hide on Active Popups / Modals', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'hide_on_selectors',
			array(
				'label'       => esc_html__( 'Hide on Presence of Selectors', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. .elementor-location-footer, #slider', 'elonix' ),
				'description' => esc_html__( 'Hides the widget entirely if any of these selectors are present on the current page.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// ================= STYLE TAB SECTIONS =================

		// 6. Style Tab - Button Layout Style Section
		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => esc_html__( 'Button Dimensions & Presets', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'label'      => esc_html__( 'Button Width (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 30,
						'max' => 120,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-back-to-top-button' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_responsive_control(
			'button_height',
			array(
				'label'      => esc_html__( 'Button Height (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 30,
						'max' => 120,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-back-to-top-button' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-back-to-top-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_responsive_control(
			'button_offset_bottom',
			array(
				'label'      => esc_html__( 'Bottom Viewport Offset (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 300,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-back-to-top-container' => 'bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_responsive_control(
			'button_offset_side',
			array(
				'label'      => esc_html__( 'Side Viewport Offset (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 300,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-align-right' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-align-left'  => 'left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout_type!' => 'progress_bar',
				),
			)
		);

		$this->add_control(
			'bg_preset',
			array(
				'label'   => esc_html__( 'Background Preset Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'solid'       => esc_html__( 'Solid Background', 'elonix' ),
					'gradient'    => esc_html__( 'Gradient Fill', 'elonix' ),
					'glass'       => esc_html__( 'Glassmorphism preset', 'elonix' ),
					'neumorphism' => esc_html__( 'Neumorphism preset', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'glass_blur_val',
			array(
				'label'     => esc_html__( 'Backdrop Blur Intensity (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 10 ),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 30,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-bg-glass' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				),
				'condition' => array(
					'bg_preset' => 'glass',
				),
			)
		);

		$this->add_control(
			'neumorphism_angle_val',
			array(
				'label'     => esc_html__( 'Neumorphism Dynamic Angle', 'elonix' ),
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

		// 7. Style Tab - Color Systems & Backgrounds
		$this->start_controls_section(
			'section_button_colors',
			array(
				'label' => esc_html__( 'Colors & Borders', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_button_colors' );

		$this->start_controls_tab(
			'tab_button_colors_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'button_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#016087',
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .es-progress-bar-fill'  => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .es-back-to-top-button',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_shadow',
				'selector' => '{{WRAPPER}} .es-back-to-top-button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_colors_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'button_bg_color_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#183ad6',
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => esc_html__( 'Hover Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_shadow_hover',
				'selector' => '{{WRAPPER}} .es-back-to-top-button:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 8. Style Tab - Progress Line/Ring Styling
		$this->start_controls_section(
			'section_progress_style',
			array(
				'label'     => esc_html__( 'Progress Indicator Style', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'progress_mode!' => 'none',
				),
			)
		);

		$this->add_control(
			'progress_track_color',
			array(
				'label'     => esc_html__( 'Track Color (Background)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.2)',
				'selectors' => array(
					'{{WRAPPER}} .es-progress-ring-track' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .es-progress-track'      => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'progress_fill_color',
			array(
				'label'     => esc_html__( 'Fill Indicator Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3858e9',
				'selectors' => array(
					'{{WRAPPER}} .es-progress-ring-fill' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .es-progress-fill'      => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'progress_stroke_width',
			array(
				'label'     => esc_html__( 'Indicator Stroke Width (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 4 ),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 12,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-progress-ring-fill'  => 'stroke-width: {{SIZE}};',
					'{{WRAPPER}} .es-progress-ring-track' => 'stroke-width: {{SIZE}};',
					'{{WRAPPER}} .es-progress-track'      => 'height: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'progress_gap_offset',
			array(
				'label'     => esc_html__( 'Progress Circle Ring Gap (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 0 ),
				'range'     => array(
					'px' => array(
						'min' => -10,
						'max' => 15,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-svg-progress-wrapper' => 'padding: {{SIZE}}px;',
				),
				'condition' => array(
					'progress_mode' => 'circular',
				),
			)
		);

		$this->end_controls_section();

		// 9. Style Tab - Graphic Icon / Text Style
		$this->start_controls_section(
			'section_icon_style',
			array(
				'label' => esc_html__( 'Icon & Typography', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-back-to-top-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .es-back-to-top-icon-complete svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon / Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-back-to-top-button:hover .es-back-to-top-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .es-back-to-top-button:hover .es-back-to-top-icon-complete svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => esc_html__( 'Icon Scale Size (px)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 16 ),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-back-to-top-button i' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .es-back-to-top-button svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				),
			)
		);

		$this->end_controls_section();

		// 10. Style Tab - Entrance & Micro Transitions
		$this->start_controls_section(
			'section_motion_style',
			array(
				'label' => esc_html__( 'Animations & Transitions', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'entrance_animation',
			array(
				'label'   => esc_html__( 'Button Entrance Transition', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => array(
					'fade'     => esc_html__( 'Simple Fade', 'elonix' ),
					'slide_up' => esc_html__( 'Slide Up & Fade', 'elonix' ),
					'scale'    => esc_html__( 'Scale Zoom & Fade', 'elonix' ),
					'rotate'   => esc_html__( 'Rotate Scale Bounce', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'hover_transition',
			array(
				'label'   => esc_html__( 'Button Hover Micro-Animation', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'scale',
				'options' => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'scale'  => esc_html__( 'Scale Zoom Up', 'elonix' ),
					'float'  => esc_html__( 'Vertical Float', 'elonix' ),
					'bounce' => esc_html__( 'Arrow Bouncing Loop', 'elonix' ),
					'rotate' => esc_html__( 'Rotation Shift', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Establish parameters to serialize for frontend JS config
		$js_config = array(
			'layout'            => esc_attr( $settings['layout_type'] ),
			'progressMode'      => esc_attr( $settings['progress_mode'] ),
			'triggerType'       => esc_attr( $settings['trigger_type'] ),
			'triggerValuePx'    => (int) $settings['trigger_value_px'],
			'triggerValuePct'   => isset( $settings['trigger_value_percent']['size'] ) ? (int) $settings['trigger_value_percent']['size'] : 25,
			'triggerSelector'   => esc_attr( $settings['trigger_selector'] ),
			'action'            => esc_attr( $settings['click_action'] ),
			'actionSelector'    => esc_attr( $settings['click_scroll_target'] ),
			'actionUrl'         => ! empty( $settings['click_custom_url'] ) ? esc_url( $settings['click_custom_url'] ) : '',
			'actionJs'          => esc_attr( $settings['click_js_callback'] ),
			'inactivityFade'    => ( 'yes' === $settings['inactivity_fade'] ),
			'inactivityTimeout' => (int) $settings['inactivity_timeout'] * 1000,
			'footerAvoidance'   => ( 'yes' === $settings['footer_avoidance'] && 'progress_bar' !== $settings['layout_type'] ),
			'footerSelector'    => esc_attr( $settings['footer_selector'] ),
			'hideOnPopup'       => ( 'yes' === $settings['hide_on_popups'] ),
			'hideOnSelectors'   => esc_attr( $settings['hide_on_selectors'] ),
			'readingAssistant'  => ( 'yes' === $settings['enable_reading_assistant'] ),
			'contentSelector'   => esc_attr( $settings['content_selector'] ),
			'readingTimeMode'   => esc_attr( $settings['reading_assistant_mode'] ),
			'readingSpeedWpm'   => (int) $settings['reading_speed_wpm'],
			'iconSwap'          => ( 'yes' === $settings['enable_icon_swap'] ),
		);

		// Determine alignments
		$align_class = 'es-align-right';
		if ( isset( $settings['position_align'] ) ) {
			$align_class = 'es-align-' . esc_attr( $settings['position_align'] );
		}

		// Sticky positioning class
		$sticky_class = '';
		if ( isset( $settings['position_sticky'] ) ) {
			$sticky_class = 'es-' . esc_attr( $settings['position_sticky'] );
		}

		// CSS Preset classes
		$preset_class = 'es-bg-' . esc_attr( $settings['bg_preset'] );
		$layout_class = 'es-layout-' . esc_attr( $settings['layout_type'] );
		$motion_class = 'es-motion-in-' . esc_attr( $settings['entrance_animation'] );
		$hover_class  = 'es-motion-hover-' . esc_attr( $settings['hover_transition'] );

		// Neumorphic inline style variables
		$style_attrs = '';
		if ( 'neumorphism' === $settings['bg_preset'] && isset( $settings['neumorphism_angle_val']['size'] ) ) {
			$style_attrs = ' style="--es-neumorphic-angle: ' . (int) $settings['neumorphism_angle_val']['size'] . 'deg;"';
		}

		// Horizontal Progress Bar render mode (handles separately)
		if ( 'progress_bar' === $settings['layout_type'] ) {
			$bar_pos   = isset( $settings['bar_position'] ) ? $settings['bar_position'] : 'top';
			$bar_class = 'es-progress-bar-' . esc_attr( $bar_pos );
			?>
			<div class="es-progress-track es-progress-bar-track <?php echo esc_attr( $bar_class ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $js_config ) ); ?>">
				<div class="es-progress-fill es-progress-bar-fill"></div>
			</div>
			<?php
			return;
		}

		// Floating Button Wrapper Render
		?>
		<div class="es-back-to-top-container <?php echo esc_attr( $align_class ); ?> <?php echo esc_attr( $motion_class ); ?> <?php echo esc_attr( $sticky_class ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $js_config ) ); ?>">
			
			<button class="es-back-to-top-button <?php echo esc_attr( $layout_class ); ?> <?php echo esc_attr( $preset_class ); ?> <?php echo esc_attr( $hover_class ); ?>" 
				aria-label="<?php echo esc_attr__( 'Back to Top', 'elonix' ); ?>" 
				aria-hidden="true" 
				tabindex="-1"
				<?php echo $style_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute string built safely. ?>>
				
				<?php if ( 'circular' === $settings['progress_mode'] || 'progress_ring' === $settings['layout_type'] ) : ?>
					<div class="es-svg-progress-wrapper">
						<svg class="es-progress-svg" viewBox="0 0 100 100" aria-hidden="true">
							<circle class="es-progress-ring-track" cx="50" cy="50" r="45"></circle>
							<circle class="es-progress-ring-fill" cx="50" cy="50" r="45"></circle>
						</svg>
					</div>
				<?php endif; ?>

				<?php if ( 'linear' === $settings['progress_mode'] ) : ?>
					<div class="es-progress-track es-linear-progress-bar-track">
						<div class="es-progress-fill es-linear-progress-bar-fill"></div>
					</div>
				<?php endif; ?>

				<div class="es-button-content-wrapper">
					<?php
					// Dynamic Icon/Graphics renderer
					$icon_swappable = ( 'yes' === $settings['enable_reading_assistant'] && 'yes' === $settings['enable_icon_swap'] );
					?>
					
					<span class="es-back-to-top-icon <?php echo $icon_swappable ? 'es-swappable' : ''; ?>">
						<?php
						// Default Icon / Source output
						$icon_source = ! empty( $settings['icon_source'] ) ? $settings['icon_source'] : 'library';
						if ( 'library' === $icon_source ) {
							Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) );
						} elseif ( 'svg' === $icon_source && ! empty( $settings['selected_svg']['url'] ) ) {
							echo '<img class="es-svg-icon" src="' . esc_url( $settings['selected_svg']['url'] ) . '" alt="" aria-hidden="true" />';
						} elseif ( 'image' === $icon_source && ! empty( $settings['selected_image']['url'] ) ) {
							echo '<img class="es-img-icon" src="' . esc_url( $settings['selected_image']['url'] ) . '" alt="" aria-hidden="true" />';
						}
						?>
					</span>

					<?php if ( $icon_swappable ) : ?>
						<span class="es-back-to-top-icon-complete" style="display: none;">
							<?php Icons_Manager::render_icon( $settings['completion_icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php endif; ?>

					<?php if ( 'percent' === $settings['progress_mode'] ) : ?>
						<span class="es-progress-percent-text">0%</span>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['enable_reading_assistant'] && 'none' !== $settings['reading_assistant_mode'] ) : ?>
						<span class="es-reading-time-text"></span>
					<?php endif; ?>
				</div>

			</button>
		</div>
		<?php
	}
}
