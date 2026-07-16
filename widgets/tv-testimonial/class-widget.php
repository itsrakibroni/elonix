<?php
/**
 * Elonix – Toolkit for Elementor Testimonial Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Testimonial_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'tv-testimonial';
	}

	public function get_title() {
		return esc_html__( 'Testimonial', 'elonix' );
	}

	public function get_tv_widget_icon() {
		return 'eicon-testimonial';
	}

	public function get_keywords() {
		return array( 'testimonial', 'review', 'quote', 'carousel', 'grid', 'tvkit' );
	}

	public function get_style_depends() {
		return array( 'elonix-widget-tv-testimonial', 'swiper' );
	}

	public function get_script_depends() {
		return array( 'elonix-widget-tv-testimonial', 'swiper' );
	}

	protected function register_controls() {
		$this->register_content_general_controls();
		$this->register_content_layout_controls();
		$this->register_content_carousel_controls();

		$this->register_style_card_controls();
		$this->register_style_content_controls();
		$this->register_style_quote_icon_controls();
		$this->register_style_avatar_controls();
		$this->register_style_company_logo_controls();
		$this->register_style_client_name_controls();
		$this->register_style_designation_controls();
		$this->register_style_company_controls();
		$this->register_style_rating_controls();
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
				'default' => 'grid',
				'options' => array(
					'grid'     => esc_html__( 'Grid', 'elonix' ),
					'carousel' => esc_html__( 'Carousel', 'elonix' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'client_name',
			array(
				'label'   => esc_html__( 'Client Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'John Doe', 'elonix' ),
			)
		);

		$repeater->add_control(
			'designation',
			array(
				'label'   => esc_html__( 'Designation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'CEO', 'elonix' ),
			)
		);

		$repeater->add_control(
			'company_name',
			array(
				'label'   => esc_html__( 'Company Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Acme Corp', 'elonix' ),
			)
		);

		$repeater->add_control(
			'avatar',
			array(
				'label' => esc_html__( 'Avatar', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'company_logo',
			array(
				'label' => esc_html__( 'Company Logo', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'testimonial',
			array(
				'label'   => esc_html__( 'Testimonial', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'This is a fantastic product! Highly recommended to everyone.', 'elonix' ),
			)
		);

		$repeater->add_control(
			'rating',
			array(
				'label'   => esc_html__( 'Rating', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 5,
				'step'    => 0.1,
				'default' => 5,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'quote_icon_override',
			array(
				'label' => esc_html__( 'Quote Icon Override', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Testimonials', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'client_name' => esc_html__( 'John Doe', 'elonix' ),
						'testimonial' => esc_html__( 'This is an amazing service. It exceeded all my expectations.', 'elonix' ),
					),
					array(
						'client_name' => esc_html__( 'Jane Smith', 'elonix' ),
						'testimonial' => esc_html__( 'Very professional and responsive. Will definitely use again.', 'elonix' ),
					),
					array(
						'client_name' => esc_html__( 'Michael Johnson', 'elonix' ),
						'testimonial' => esc_html__( 'The quality of work is outstanding. A reliable partner for our business.', 'elonix' ),
					),
				),
				'title_field' => '{{{ client_name }}}',
			)
		);

		$this->add_control(
			'show_avatar',
			array(
				'label'     => esc_html__( 'Show Avatar', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'show_company_logo',
			array(
				'label'   => esc_html__( 'Show Company Logo', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_client_name',
			array(
				'label'   => esc_html__( 'Show Client Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_designation',
			array(
				'label'   => esc_html__( 'Show Designation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_company_name',
			array(
				'label'   => esc_html__( 'Show Company Name', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_rating',
			array(
				'label'   => esc_html__( 'Show Rating', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_quote_icon',
			array(
				'label'   => esc_html__( 'Show Quote Icon', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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
				'max'       => 6,
				'default'   => 3,
				'selectors' => array(
					'{{WRAPPER}} .tv-testimonial__grid' => '--tv-testimonial-columns: {{VALUE}};',
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
					'{{WRAPPER}} .tv-testimonial__grid' => '--tv-testimonial-gap-x: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .tv-testimonial__grid' => '--tv-testimonial-gap-y: {{SIZE}}{{UNIT}};',
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
				'default' => 3,
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
			'speed',
			array(
				'label'   => esc_html__( 'Transition Speed (ms)', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
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
			'navigation',
			array(
				'label'   => esc_html__( 'Navigation', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'   => esc_html__( 'Pagination', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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
				'selector' => '{{WRAPPER}} .tv-testimonial__item',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .tv-testimonial__item',
			)
		);
		$this->add_responsive_control(
			'card_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .tv-testimonial__item',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_card_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg_hover',
				'selector' => '{{WRAPPER}} .tv-testimonial__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border_hover',
				'selector' => '{{WRAPPER}} .tv-testimonial__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-testimonial__item:hover',
			)
		);
		$this->add_control(
			'card_hover_distance',
			array(
				'label'     => esc_html__( 'Hover Animation Distance', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -50,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__item:hover' => 'transform: translateY({{SIZE}}{{UNIT}});' ),
			)
		);
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
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__item' => 'transition: all {{SIZE}}s ease;' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_content_controls() {
		$this->start_controls_section(
			'section_style_content',
			array(
				'label' => esc_html__( 'Quote Content', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'selector' => '{{WRAPPER}} .tv-testimonial__content',
			)
		);
		$this->add_control(
			'content_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__content' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'content_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => 'Left',
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => 'Center',
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => 'Right',
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => 'Justified',
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__content' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'content_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .tv-testimonial__content' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'content_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_quote_icon_controls() {
		$this->start_controls_section(
			'section_style_quote_icon',
			array(
				'label'     => esc_html__( 'Quote Icon', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_quote_icon' => 'yes' ),
			)
		);
		$this->add_control(
			'global_quote_icon',
			array(
				'label'   => esc_html__( 'Icon', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-quote-left',
					'library' => 'fa-solid',
				),
			)
		);
		$this->add_responsive_control(
			'quote_icon_size',
			array(
				'label'     => esc_html__( 'Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'quote_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'quote_icon_bg',
				'selector' => '{{WRAPPER}} .tv-testimonial__quote-icon',
			)
		);
		$this->add_responsive_control(
			'quote_icon_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'quote_icon_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'quote_icon_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'quote_icon_opacity',
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
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__quote-icon' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_avatar_controls() {
		$this->start_controls_section(
			'section_style_avatar',
			array(
				'label'     => esc_html__( 'Avatar', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_avatar' => 'yes' ),
			)
		);
		$this->add_responsive_control(
			'avatar_size',
			array(
				'label'     => esc_html__( 'Size (Width & Height)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 20,
						'max' => 150,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'avatar_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'avatar_border',
				'selector' => '{{WRAPPER}} .tv-testimonial__avatar img',
			)
		);
		$this->add_responsive_control(
			'avatar_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__avatar' => 'margin-right: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'avatar_object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'cover'   => 'Cover',
					'contain' => 'Contain',
					'fill'    => 'Fill',
				),
				'default'   => 'cover',
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__avatar img' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'avatar_bg',
				'selector' => '{{WRAPPER}} .tv-testimonial__avatar img',
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_company_logo_controls() {
		$this->start_controls_section(
			'section_style_company_logo',
			array(
				'label'     => esc_html__( 'Company Logo', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_company_logo' => 'yes' ),
			)
		);
		$this->add_responsive_control(
			'company_logo_width',
			array(
				'label'     => esc_html__( 'Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'max' => 250 ) ),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo img' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'company_logo_height',
			array(
				'label'     => esc_html__( 'Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'max' => 150 ) ),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo img' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'company_logo_max_width',
			array(
				'label'     => esc_html__( 'Max Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'max' => 300 ) ),
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo img' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'company_logo_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'company_logo_opacity',
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
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_control(
			'company_logo_object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'contain' => 'Contain',
					'cover'   => 'Cover',
				),
				'default'   => 'contain',
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company-logo img' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_client_name_controls() {
		$this->start_controls_section(
			'section_style_client_name',
			array(
				'label'     => esc_html__( 'Client Name', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_client_name' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'client_name_typography',
				'selector' => '{{WRAPPER}} .tv-testimonial__client-name',
			)
		);
		$this->add_control(
			'client_name_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__client-name' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'client_name_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__client-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_designation_controls() {
		$this->start_controls_section(
			'section_style_designation',
			array(
				'label'     => esc_html__( 'Designation', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_designation' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'designation_typography',
				'selector' => '{{WRAPPER}} .tv-testimonial__designation',
			)
		);
		$this->add_control(
			'designation_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__designation' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'designation_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__designation' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_company_controls() {
		$this->start_controls_section(
			'section_style_company',
			array(
				'label'     => esc_html__( 'Company Name', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_company_name' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'company_typography',
				'selector' => '{{WRAPPER}} .tv-testimonial__company',
			)
		);
		$this->add_control(
			'company_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'company_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__company' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_rating_controls() {
		$this->start_controls_section(
			'section_style_rating',
			array(
				'label'     => esc_html__( 'Rating Badge', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_rating' => 'yes' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'rating_bg',
				'selector' => '{{WRAPPER}} .tv-testimonial__rating',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'rating_border',
				'selector' => '{{WRAPPER}} .tv-testimonial__rating',
			)
		);
		$this->add_responsive_control(
			'rating_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'rating_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'rating_gap',
			array(
				'label'     => esc_html__( 'Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'rating_icon',
			array(
				'label'     => esc_html__( 'Icon', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'rating_icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating-icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'rating_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating-icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'      => 'rating_typography',
				'selector'  => '{{WRAPPER}} .tv-testimonial__rating-text',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'rating_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-testimonial__rating-text' => 'color: {{VALUE}};' ),
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
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_width',
			array(
				'label'     => esc_html__( 'Arrow Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_height',
			array(
				'label'     => esc_html__( 'Arrow Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'nav_color',
			array(
				'label'     => esc_html__( 'Arrow Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'nav_bg',
				'selector' => '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'nav_border',
				'selector' => '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next',
			)
		);
		$this->add_responsive_control(
			'nav_radius',
			array(
				'label'     => esc_html__( 'Arrow Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev, {{WRAPPER}} .tv-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_offset',
			array(
				'label'     => esc_html__( 'Offset', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
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
				'selectors' => array( '{{WRAPPER}} .tv-swiper-button-prev:hover, {{WRAPPER}} .tv-swiper-button-next:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'nav_bg_hover',
				'selector' => '{{WRAPPER}} .tv-swiper-button-prev:hover, {{WRAPPER}} .tv-swiper-button-next:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'nav_border_hover',
				'selector' => '{{WRAPPER}} .tv-swiper-button-prev:hover, {{WRAPPER}} .tv-swiper-button-next:hover',
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
			'tv-testimonial',
			'tv-testimonial--' . esc_attr( $settings['layout_mode'] ),
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
