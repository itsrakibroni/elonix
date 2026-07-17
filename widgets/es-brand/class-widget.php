<?php
/**
 * Elonix – Toolkit for Elementor Brand Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Brand_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'es-brand';
	}

	public function get_title() {
		return esc_html__( 'Brand', 'elonix' );
	}

	public function get_es_widget_icon() {
		return 'eicon-slider-push';
	}

	public function get_keywords() {
		return array( 'brand', 'logo', 'carousel', 'grid', 'client', 'partner', 'eskit' );
	}

	public function get_style_depends() {
		return array( 'elonix-widget-es-brand', 'swiper' );
	}

	public function get_script_depends() {
		return array( 'elonix-widget-es-brand', 'swiper' );
	}

	protected function register_controls() {
		$this->register_content_general_controls();
		$this->register_content_layout_controls();
		$this->register_content_carousel_controls();
		$this->register_content_elements_controls();

		$this->register_style_card_controls();
		$this->register_style_logo_controls();
		$this->register_style_brand_name_controls();
		$this->register_style_navigation_controls();
		$this->register_style_pagination_controls();
	}

	protected function register_content_general_controls() {
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'General', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'skin',
			array(
				'label'   => esc_html__( 'Skin', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => array(
					'style-1' => esc_html__( 'Style One', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'layout_mode',
			array(
				'label'   => esc_html__( 'Layout Mode', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'carousel',
				'options' => array(
					'grid'     => esc_html__( 'Grid', 'elonix' ),
					'carousel' => esc_html__( 'Carousel', 'elonix' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'brand_logo',
			array(
				'label'   => esc_html__( 'Brand Logo', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'brand_name',
			array(
				'label'   => esc_html__( 'Brand Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Brand Name', 'elonix' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'brand_url',
			array(
				'label'   => esc_html__( 'Brand URL', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Brands', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'brand_name' => esc_html__( 'Brand 1', 'elonix' ) ),
					array( 'brand_name' => esc_html__( 'Brand 2', 'elonix' ) ),
					array( 'brand_name' => esc_html__( 'Brand 3', 'elonix' ) ),
					array( 'brand_name' => esc_html__( 'Brand 4', 'elonix' ) ),
					array( 'brand_name' => esc_html__( 'Brand 5', 'elonix' ) ),
				),
				'title_field' => '{{{ brand_name }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Settings', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Columns', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 8,
				'default'   => 5,
				'selectors' => array(
					'{{WRAPPER}} .es-brand__grid' => '--es-brand-columns: {{VALUE}};',
				),
				'condition' => array( 'layout_mode' => 'grid' ),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Column Gap', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-brand__grid' => '--es-brand-gap-x: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => esc_html__( 'Row Gap', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-brand__grid' => '--es-brand-gap-y: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'min_card_height',
			array(
				'label'      => esc_html__( 'Min Card Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-brand__item' => 'min-height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'equal_height',
			array(
				'label'     => esc_html__( 'Equal Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'selectors' => array(
					'{{WRAPPER}} .es-brand__item' => 'height: 100%;',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_carousel_controls() {
		$this->start_controls_section(
			'section_carousel',
			array(
				'label'     => esc_html__( 'Carousel Settings', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => array( 'layout_mode' => 'carousel' ),
			)
		);

		$this->add_responsive_control(
			'slides_per_view',
			array(
				'label'   => esc_html__( 'Slides Per View', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 10,
				'default' => 5,
			)
		);

		$this->add_responsive_control(
			'slides_per_group',
			array(
				'label'   => esc_html__( 'Slides To Scroll', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 10,
				'default' => 1,
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'   => esc_html__( 'Infinite Loop', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'   => esc_html__( 'Autoplay', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'autoplaySpeed',
			array(
				'label'     => esc_html__( 'Autoplay Speed (ms)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3000,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'     => esc_html__( 'Pause on Hover', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'   => esc_html__( 'Transition Speed (ms)', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
			)
		);

		$this->add_control(
			'continuous_marquee',
			array(
				'label'       => esc_html__( 'Continuous Marquee Mode', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'Creates an endless smooth scrolling effect. Disables pagination and navigation.', 'elonix' ),
			)
		);

		$this->add_control(
			'reverse_direction',
			array(
				'label'   => esc_html__( 'Reverse Direction', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'navigation',
			array(
				'label'     => esc_html__( 'Navigation', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => array( 'continuous_marquee!' => 'yes' ),
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'     => esc_html__( 'Pagination', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => array( 'continuous_marquee!' => 'yes' ),
			)
		);

		$this->add_control(
			'grab_cursor',
			array(
				'label'   => esc_html__( 'Grab Cursor', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'keyboard',
			array(
				'label'   => esc_html__( 'Keyboard Navigation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'mousewheel',
			array(
				'label'   => esc_html__( 'Mousewheel Navigation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_elements_controls() {
		$this->start_controls_section(
			'section_elements',
			array(
				'label' => esc_html__( 'Elements', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_brand_name',
			array(
				'label'   => esc_html__( 'Show Brand Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'show_tooltip',
			array(
				'label'   => esc_html__( 'Show Tooltip', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_card_controls() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Card', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_card_style' );

		$this->start_controls_tab( 'tab_card_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg',
				'selector' => '{{WRAPPER}} .es-brand__item',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .es-brand__item',
			)
		);
		$this->add_responsive_control(
			'card_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .es-brand__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .es-brand__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .es-brand__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .es-brand__item',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_card_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg_hover',
				'selector' => '{{WRAPPER}} .es-brand__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border_hover',
				'selector' => '{{WRAPPER}} .es-brand__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow_hover',
				'selector' => '{{WRAPPER}} .es-brand__item:hover',
			)
		);
		$this->add_control(
			'card_hover_translate',
			array(
				'label'     => esc_html__( 'Hover Translate Y', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -50,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__item:hover' => 'transform: translateY({{SIZE}}{{UNIT}});' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->add_control(
			'card_transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__item' => 'transition: all {{SIZE}}s ease;' ),
				'separator' => 'before',
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_logo_controls() {
		$this->start_controls_section(
			'section_style_logo',
			array(
				'label' => esc_html__( 'Logo', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'logo_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'max' => 500 ) ),
				'selectors'  => array( '{{WRAPPER}} .es-brand__logo img' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'logo_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'max' => 500 ) ),
				'selectors'  => array( '{{WRAPPER}} .es-brand__logo img' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'logo_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'max' => 500 ) ),
				'selectors'  => array( '{{WRAPPER}} .es-brand__logo img' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'logo_max_height',
			array(
				'label'      => esc_html__( 'Max Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'max' => 500 ) ),
				'selectors'  => array( '{{WRAPPER}} .es-brand__logo img' => 'max-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'logo_object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'contain' => 'Contain',
					'cover'   => 'Cover',
					'fill'    => 'Fill',
				),
				'default'   => 'contain',
				'selectors' => array( '{{WRAPPER}} .es-brand__logo img' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'logo_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => 'Left',
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => 'Center',
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => 'Right',
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__figure' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->start_controls_tabs( 'tabs_logo_style' );

		$this->start_controls_tab( 'tab_logo_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_control(
			'logo_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.05,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__logo img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'logo_css_filters',
				'selector' => '{{WRAPPER}} .es-brand__logo img',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_logo_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'logo_opacity_hover',
			array(
				'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.05,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__item:hover .es-brand__logo img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_control(
			'logo_scale_hover',
			array(
				'label'     => esc_html__( 'Hover Scale', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.5,
						'max'  => 2,
						'step' => 0.01,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__item:hover .es-brand__logo img' => 'transform: scale({{SIZE}});' ),
			)
		);
		$this->add_control(
			'logo_rotate_hover',
			array(
				'label'     => esc_html__( 'Hover Rotate', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -180,
						'max' => 180,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__item:hover .es-brand__logo img' => 'transform: rotate({{SIZE}}deg);' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'logo_css_filters_hover',
				'selector' => '{{WRAPPER}} .es-brand__item:hover .es-brand__logo img',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->add_control(
			'logo_transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__logo img' => 'transition: all {{SIZE}}s ease;' ),
				'separator' => 'before',
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_brand_name_controls() {
		$this->start_controls_section(
			'section_style_brand_name',
			array(
				'label'     => esc_html__( 'Brand Name', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_brand_name' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'brand_name_typography',
				'selector' => '{{WRAPPER}} .es-brand__title',
			)
		);

		$this->start_controls_tabs( 'tabs_brand_name_style' );
		$this->start_controls_tab( 'tab_brand_name_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_control(
			'brand_name_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-brand__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_brand_name_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'brand_name_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-brand__item:hover .es-brand__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'brand_name_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-brand__title' => 'margin-top: {{SIZE}}{{UNIT}};' ),
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'brand_name_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => 'Left',
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => 'Center',
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => 'Right',
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-brand__title' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_navigation_controls() {
		$this->start_controls_section(
			'section_style_navigation',
			array(
				'label'     => esc_html__( 'Navigation', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout_mode' => 'carousel',
					'navigation'  => 'yes',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_nav_style' );

		$this->start_controls_tab( 'tab_nav_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_responsive_control(
			'nav_size',
			array(
				'label'     => esc_html__( 'Arrow Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_width',
			array(
				'label'     => esc_html__( 'Arrow Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_height',
			array(
				'label'     => esc_html__( 'Arrow Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'nav_color',
			array(
				'label'     => esc_html__( 'Arrow Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'nav_bg',
				'selector' => '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'nav_border',
				'selector' => '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next',
			)
		);
		$this->add_responsive_control(
			'nav_radius',
			array(
				'label'     => esc_html__( 'Arrow Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_offset',
			array(
				'label'     => esc_html__( 'Position Offset', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_nav_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'nav_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev:hover, {{WRAPPER}} .es-swiper-button-next:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'nav_bg_hover',
				'selector' => '{{WRAPPER}} .es-swiper-button-prev:hover, {{WRAPPER}} .es-swiper-button-next:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'nav_border_hover',
				'selector' => '{{WRAPPER}} .es-swiper-button-prev:hover, {{WRAPPER}} .es-swiper-button-next:hover',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_pagination_controls() {
		$this->start_controls_section(
			'section_style_pagination',
			array(
				'label'     => esc_html__( 'Pagination', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout_mode' => 'carousel',
					'pagination'  => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'pagination_size',
			array(
				'label'     => esc_html__( 'Dot Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_gap',
			array(
				'label'     => esc_html__( 'Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_margin_top',
			array(
				'label'     => esc_html__( 'Margin Top', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'pagination_color',
			array(
				'label'     => esc_html__( 'Inactive Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pagination_color_active',
			array(
				'label'     => esc_html__( 'Active Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$wrapper_classes = array(
			'es-brand',
			'es-brand--' . esc_attr( $settings['layout_mode'] ),
		);

		$is_carousel = 'carousel' === $settings['layout_mode'];

		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<?php
			if ( $is_carousel ) {
				include __DIR__ . '/views/layout-carousel.php';
			} else {
				include __DIR__ . '/views/layout-grid.php';
			}
			?>
		</div>
		<?php
	}
}
