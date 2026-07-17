<?php
/**
 * Elonix – Toolkit for Elementor Toggle Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Toggle_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'es-toggle';
	}

	public function get_title() {
		return esc_html__( 'Toggle', 'elonix' );
	}

	public function get_es_widget_icon() {
		return 'eicon-exchange';
	}

	public function get_keywords() {
		return [ 'toggle', 'switcher', 'visibility', 'tabs', 'eskit' ];
	}

	public function get_style_depends() {
		return [ 'elonix-widget-es-toggle' ];
	}

	public function get_script_depends() {
		return [ 'elonix-widget-es-toggle' ];
	}

	protected function register_controls() {
		$this->register_content_toggle_items_controls();
		$this->register_content_general_controls();
		$this->register_content_layout_controls();
		$this->register_content_behaviour_controls();
		$this->register_content_accessibility_controls();
		$this->register_content_developer_controls();

		$this->register_style_toggle_controls();
		$this->register_style_active_toggle_controls();
		$this->register_style_inactive_toggle_controls();
		$this->register_style_typography_controls();
		$this->register_style_icon_controls();
		$this->register_style_indicator_controls();
		$this->register_style_spacing_controls();
		$this->register_style_border_controls();
		$this->register_style_animation_controls();
	}

	protected function register_content_toggle_items_controls() {
		$this->start_controls_section(
			'section_toggle_items',
			[
				'label' => esc_html__( 'Toggle Items', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'label',
			[
				'label'   => esc_html__( 'Label', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Toggle Item', 'elonix' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'target_selector',
			[
				'label'              => esc_html__( 'Target Selector', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::TEXT,
				'placeholder'        => '#monthly, .monthly, [data-plan="monthly"]',
				'description'        => esc_html__( 'Supports any valid CSS selector.', 'elonix' ),
				'default'            => '',
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::ICONS,
			]
		);

		$this->add_control(
			'toggle_items',
			[
				'label'       => esc_html__( 'Toggle Items', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'label'           => esc_html__( 'Monthly', 'elonix' ),
						'target_selector' => '.monthly',
					],
					[
						'label'           => esc_html__( 'Yearly', 'elonix' ),
						'target_selector' => '.yearly',
					],
				],
				'title_field' => '{{{ label }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_general_controls() {
		$this->start_controls_section(
			'section_general',
			[
				'label' => esc_html__( 'General', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'toggle_type',
			[
				'label'              => esc_html__( 'Toggle Type', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'segmented' => esc_html__( 'Segmented', 'elonix' ),
					'buttons'   => esc_html__( 'Buttons', 'elonix' ),
					'tabs'      => esc_html__( 'Tabs', 'elonix' ),
					'pills'     => esc_html__( 'Pills', 'elonix' ),
					'underline' => esc_html__( 'Underline', 'elonix' ),
					'switch'    => esc_html__( 'Switch', 'elonix' ),
				],
				'default'            => 'pills',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'toggle_style',
			[
				'label'              => esc_html__( 'Toggle Style', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'tabs'      => esc_html__( 'Tabs', 'elonix' ),
					'pills'     => esc_html__( 'Pills', 'elonix' ),
					'underline' => esc_html__( 'Underline', 'elonix' ),
				],
				'default'            => 'pills',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'show_icons',
			[
				'label'              => esc_html__( 'Show Icons', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'default_active_mode',
			[
				'label'              => esc_html__( 'Default Active Mode', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'index'             => esc_html__( 'Index', 'elonix' ),
					'css_selector'      => esc_html__( 'CSS Selector', 'elonix' ),
					'url_hash'          => esc_html__( 'URL Hash', 'elonix' ),
					'query_parameter'   => esc_html__( 'Query Parameter', 'elonix' ),
					'remember_previous' => esc_html__( 'Remember Previous', 'elonix' ),
				],
				'default'            => 'index',
				'frontend_available' => true,
				'separator'          => 'before',
			]
		);

		$this->add_control(
			'default_active_index',
			[
				'label'              => esc_html__( 'Default Active Index', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::NUMBER,
				'min'                => 0,
				'default'            => 0,
				'frontend_available' => true,
				'condition'          => [
					'default_active_mode' => 'index',
				],
			]
		);

		$this->add_control(
			'editor_helper',
			[
				'label'              => esc_html__( 'Editor Helper', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => esc_html__( 'On', 'elonix' ),
				'label_off'          => esc_html__( 'Off', 'elonix' ),
				'description'        => esc_html__( 'When enabled, inactive targets remain editable inside Elementor Editor.', 'elonix' ),
				'frontend_available' => true,
				'separator'          => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'orientation',
			[
				'label'              => esc_html__( 'Orientation', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'horizontal' => esc_html__( 'Horizontal', 'elonix' ),
					'vertical'   => esc_html__( 'Vertical', 'elonix' ),
				],
				'default'            => 'horizontal',
				'frontend_available' => true,
				'selectors'          => [
					'{{WRAPPER}} .es-toggle__list' => 'flex-direction: {{VALUE}} === "vertical" ? column : row;',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'              => esc_html__( 'List Alignment', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::CHOOSE,
				'options'            => [
					'left'    => [
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					],
					'stretch' => [
						'title' => esc_html__( 'Stretch', 'elonix' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'            => 'center',
				'frontend_available' => true,
				'selectors'          => [
					'{{WRAPPER}} .es-toggle__wrapper' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .es-toggle__list' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_alignment',
			[
				'label'              => esc_html__( 'Button Alignment', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::CHOOSE,
				'options'            => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'elonix' ),
						'icon'  => 'eicon-align-start-h',
					],
					'center'     => [
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-align-center-h',
					],
					'flex-end'   => [
						'title' => esc_html__( 'End', 'elonix' ),
						'icon'  => 'eicon-align-end-h',
					],
				],
				'default'            => 'center',
				'selectors'          => [
					'{{WRAPPER}} .es-toggle__button' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_valign',
			[
				'label'              => esc_html__( 'Button Vertical Align', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::CHOOSE,
				'options'            => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'elonix' ),
						'icon'  => 'eicon-align-start-v',
					],
					'center'     => [
						'title' => esc_html__( 'Middle', 'elonix' ),
						'icon'  => 'eicon-align-center-v',
					],
					'flex-end'   => [
						'title' => esc_html__( 'Bottom', 'elonix' ),
						'icon'  => 'eicon-align-end-v',
					],
				],
				'default'            => 'center',
				'selectors'          => [
					'{{WRAPPER}} .es-toggle__button' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'equal_width',
			[
				'label'              => esc_html__( 'Equal Width', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'no',
				'description'        => esc_html__( 'Make every toggle item equal width.', 'elonix' ),
				'frontend_available' => true,
				'selectors'          => [
					'{{WRAPPER}} .es-toggle__item' => 'flex: 1;',
					'{{WRAPPER}} .es-toggle__button' => 'width: 100%;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_behaviour_controls() {
		$this->start_controls_section(
			'section_behaviour',
			[
				'label' => esc_html__( 'Behaviour', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


		$this->add_control(
			'initial_render_mode',
			[
				'label'              => esc_html__( 'Initial Render Mode', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'hide_immediately' => esc_html__( 'Hide Immediately', 'elonix' ),
					'hide_after_js'    => esc_html__( 'Hide After JS', 'elonix' ),
					'no_hide'          => esc_html__( 'No Hide', 'elonix' ),
				],
				'default'            => 'hide_immediately',
				'frontend_available' => true,
				'separator'          => 'before',
			]
		);

		$this->add_control(
			'missing_target_action',
			[
				'label'              => esc_html__( 'Missing Target Action', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'ignore'          => esc_html__( 'Ignore', 'elonix' ),
					'editor_warning'  => esc_html__( 'Editor Warning', 'elonix' ),
					'hide_toggle'     => esc_html__( 'Hide Toggle', 'elonix' ),
					'disable_toggle'  => esc_html__( 'Disable Toggle', 'elonix' ),
				],
				'default'            => 'ignore',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'sync_group',
			[
				'label'              => esc_html__( 'Sync Group', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::TEXT,
				'placeholder'        => 'pricing-group',
				'frontend_available' => true,
				'separator'          => 'before',
			]
		);

		$this->add_control(
			'enable_url_hash',
			[
				'label'              => esc_html__( 'Enable URL Hash', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'no',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'remember_state',
			[
				'label'              => esc_html__( 'Remember State', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => [
					'none'            => esc_html__( 'None', 'elonix' ),
					'session_storage' => esc_html__( 'Session Storage', 'elonix' ),
					'local_storage'   => esc_html__( 'Local Storage', 'elonix' ),
				],
				'default'            => 'none',
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_accessibility_controls() {
		$this->start_controls_section(
			'section_accessibility',
			[
				'label' => esc_html__( 'Accessibility', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'aria_label',
			[
				'label'              => esc_html__( 'ARIA Label', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::TEXT,
				'default'            => esc_html__( 'Toggle Navigation', 'elonix' ),
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_content_developer_controls() {
		$this->start_controls_section(
			'section_developer',
			[
				'label' => esc_html__( 'Developer', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'developer_mode',
			[
				'label'              => esc_html__( 'Developer Mode', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'no',
				'frontend_available' => false,
			]
		);

		$this->add_control(
			'debug_mode',
			[
				'label'              => esc_html__( 'Debug Mode', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SWITCHER,
				'default'            => 'no',
				'frontend_available' => true,
				'condition'          => [
					'developer_mode' => 'yes',
				],
			]
		);


		$this->add_control(
			'css_prefix',
			[
				'label'       => esc_html__( 'CSS Prefix', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::HIDDEN,
				'default'     => 'es-toggle',
				'description' => esc_html__( 'Reserved for future Elonix namespace customization.', 'elonix' ),
				'condition'          => [
					'developer_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'before_toggle_event',
			[
				'label'              => esc_html__( 'Before Toggle Event', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::TEXT,
				'default'            => '',
				'description'        => esc_html__( 'Future JS hook.', 'elonix' ),
				'frontend_available' => true,
				'condition'          => [
					'developer_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'after_toggle_event',
			[
				'label'              => esc_html__( 'After Toggle Event', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::TEXT,
				'default'            => '',
				'description'        => esc_html__( 'Future JS hook.', 'elonix' ),
				'frontend_available' => true,
				'condition'          => [
					'developer_mode' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_toggle_controls() {
		$this->start_controls_section( 'section_style_toggle', [ 'label' => esc_html__( 'Toggle Colors', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->start_controls_tabs( 'tabs_toggle_style' );
		
		$states = [
			'normal'   => [ 'label' => esc_html__( 'Normal', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button' ],
			'hover'    => [ 'label' => esc_html__( 'Hover', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:hover' ],
			'active'   => [ 'label' => esc_html__( 'Active', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button.es-toggle__button--active' ],
			'focus'    => [ 'label' => esc_html__( 'Focus', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:focus-visible' ],
			'disabled' => [ 'label' => esc_html__( 'Disabled', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:disabled, {{WRAPPER}} .es-toggle__button[disabled]' ],
		];

		foreach ( $states as $state => $state_data ) {
			$this->start_controls_tab( 'tab_tabs_color_' . $state, [ 'label' => $state_data['label'] ] );

			$this->add_control(
				'tabs_color_' . $state,
				[
					'label'     => esc_html__( 'Text Color', 'elonix' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$state_data['selector'] => 'color: {{VALUE}};',
					],
				]
			);

			$bg_selectors = [
				$state_data['selector'] => 'background-color: {{VALUE}};',
			];

			$this->add_control(
				'tabs_bg_color_' . $state,
				[
					'label'     => esc_html__( 'Background Color', 'elonix' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => $bg_selectors,
				]
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_active_toggle_controls() {
		// Preserved as empty for Phase compatibility requirements.
	}

	protected function register_style_inactive_toggle_controls() {
		// Preserved as empty for Phase compatibility requirements.
	}

	protected function register_style_typography_controls() {
		$this->start_controls_section( 'section_style_typography', [ 'label' => esc_html__( 'Typography', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'toggle_typography',
				'selector' => '{{WRAPPER}} .es-toggle__button',
			]
		);
		
		$this->end_controls_section();
	}

	protected function register_style_icon_controls() {
		$this->start_controls_section( 'section_style_icon', [ 'label' => esc_html__( 'Icon', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'icon_gap',
			[
				'label' => esc_html__( 'Icon Gap', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__button' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->start_controls_tabs( 'tabs_icon_style' );
		
		$states = [
			'normal'   => [ 'label' => esc_html__( 'Normal', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button' ],
			'hover'    => [ 'label' => esc_html__( 'Hover', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:hover' ],
			'active'   => [ 'label' => esc_html__( 'Active', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button.es-toggle__button--active' ],
		];

		foreach ( $states as $state => $state_data ) {
			$this->start_controls_tab( 'tab_icon_color_' . $state, [ 'label' => $state_data['label'] ] );
			$this->add_control(
				'icon_color_' . $state,
				[
					'label'     => esc_html__( 'Icon Color', 'elonix' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$state_data['selector'] . ' .es-toggle__icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
					],
				]
			);
			$this->end_controls_tab();
		}
		
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_indicator_controls() {
		$this->start_controls_section( 'section_style_indicator', [ 'label' => esc_html__( 'Indicator', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->add_control(
			'indicator_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .es-toggle__indicator' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'indicator_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'elonix' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__indicator' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'indicator_box_shadow',
				'selector' => '{{WRAPPER}} .es-toggle__indicator',
			]
		);
		
		$this->end_controls_section();
	}

	protected function register_style_spacing_controls() {
		$this->start_controls_section( 'section_style_spacing', [ 'label' => esc_html__( 'Spacing', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->add_responsive_control(
			'toggle_padding',
			[
				'label' => esc_html__( 'Padding', 'elonix' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'toggle_margin',
			[
				'label' => esc_html__( 'Margin', 'elonix' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'toggle_gap',
			[
				'label' => esc_html__( 'Gap', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();
	}

	protected function register_style_border_controls() {
		$this->start_controls_section( 'section_style_border', [ 'label' => esc_html__( 'Border', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->start_controls_tabs( 'tabs_border_style' );
		
		$states = [
			'normal'   => [ 'label' => esc_html__( 'Normal', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button' ],
			'hover'    => [ 'label' => esc_html__( 'Hover', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:hover' ],
			'active'   => [ 'label' => esc_html__( 'Active', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button.es-toggle__button--active' ],
			'focus'    => [ 'label' => esc_html__( 'Focus', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:focus-visible' ],
			'disabled' => [ 'label' => esc_html__( 'Disabled', 'elonix' ), 'selector' => '{{WRAPPER}} .es-toggle__button:disabled, {{WRAPPER}} .es-toggle__button[disabled]' ],
		];

		foreach ( $states as $state_key => $state_data ) {
			$this->start_controls_tab( "tab_border_{$state_key}", [ 'label' => $state_data['label'] ] );
			
			$this->add_control(
				"border_style_{$state_key}",
				[
					'label' => esc_html__( 'Border Style', 'elonix' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'none'   => esc_html__( 'None', 'elonix' ),
						'solid'  => esc_html__( 'Solid', 'elonix' ),
						'double' => esc_html__( 'Double', 'elonix' ),
						'dotted' => esc_html__( 'Dotted', 'elonix' ),
						'dashed' => esc_html__( 'Dashed', 'elonix' ),
					],
					'selectors' => [
						$state_data['selector'] => 'border-style: {{VALUE}};',
					],
				]
			);
			
			$this->add_responsive_control(
				"border_width_{$state_key}",
				[
					'label' => esc_html__( 'Border Width', 'elonix' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'rem' ],
					'selectors' => [
						$state_data['selector'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						"border_style_{$state_key}!" => 'none',
					],
				]
			);
			
			$this->add_control(
				"border_color_{$state_key}",
				[
					'label' => esc_html__( 'Border Color', 'elonix' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$state_data['selector'] => 'border-color: {{VALUE}};',
					],
					'condition' => [
						"border_style_{$state_key}!" => 'none',
					],
				]
			);
			
			$radius_selectors = [
				$state_data['selector'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			];

			$this->add_responsive_control(
				"border_radius_{$state_key}",
				[
					'label' => esc_html__( 'Border Radius', 'elonix' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'rem', '%' ],
					'selectors' => $radius_selectors,
				]
			);
			
			$shadow_selectors = [
				$state_data['selector'] => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} {{box_shadow_position.VALUE}};',
			];

			$this->add_control(
				"box_shadow_{$state_key}",
				[
					'label' => esc_html__( 'Box Shadow', 'elonix' ),
					'type' => \Elementor\Controls_Manager::BOX_SHADOW,
					'selectors' => $shadow_selectors,
				]
			);
			
			$this->end_controls_tab();
		}
		
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_animation_controls() {
		$this->start_controls_section( 'section_style_animation', [ 'label' => esc_html__( 'Animation', 'elonix' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
		
		$this->add_control(
			'animation_type',
			[
				'label' => esc_html__( 'Animation Type', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'none'      => esc_html__( 'None', 'elonix' ),
					'fade'      => esc_html__( 'Fade', 'elonix' ),
					'fade_up'   => esc_html__( 'Fade Up', 'elonix' ),
					'fade_down' => esc_html__( 'Fade Down', 'elonix' ),
					'scale'     => esc_html__( 'Scale', 'elonix' ),
				],
				'default' => 'fade',
				'frontend_available' => true,
			]
		);
		
		$this->add_responsive_control(
			'animation_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'ms', 's' ],
				'default' => [
					'unit' => 'ms',
					'size' => 300,
				],
				'range' => [
					'ms' => [ 'min' => 0, 'max' => 2000, 'step' => 10 ],
					's' => [ 'min' => 0, 'max' => 2, 'step' => 0.1 ],
				],
				'selectors' => [
					'{{WRAPPER}} .es-toggle__button' => 'transition-duration: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-toggle__indicator' => 'transition-duration: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-toggle__icon' => 'transition-duration: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-transition-enabled' => 'transition-duration: {{SIZE}}{{UNIT}} !important;',
				],
				'frontend_available' => true,
			]
		);
		
		$this->add_control(
			'animation_easing',
			[
				'label' => esc_html__( 'Transition Easing', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'linear'      => 'linear',
					'ease'        => 'ease',
					'ease-in'     => 'ease-in',
					'ease-out'    => 'ease-out',
					'ease-in-out' => 'ease-in-out',
				],
				'default' => 'ease',
				'selectors' => [
					'{{WRAPPER}} .es-toggle__button' => 'transition-timing-function: {{VALUE}};',
					'{{WRAPPER}} .es-toggle__indicator' => 'transition-timing-function: {{VALUE}};',
					'{{WRAPPER}} .es-toggle__icon' => 'transition-timing-function: {{VALUE}};',
					'{{WRAPPER}} .es-transition-enabled' => 'transition-timing-function: {{VALUE}} !important;',
				],
			]
		);
		
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		if ( empty( $settings['toggle_items'] ) ) {
			return;
		}

		include __DIR__ . '/views/template.php';
	}
}
