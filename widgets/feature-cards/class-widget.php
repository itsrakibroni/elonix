<?php
/**
 * Elonix – Toolkit for Elementor Feature Cards Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Feature_Cards_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'es-feature-cards';
	}

	public function get_title() {
		return esc_html__( 'Feature Cards', 'elonix' );
	}

	public function get_es_widget_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return array( 'card', 'feature', 'grid', 'carousel' );
	}

	public function get_style_depends() {
		return array( 'elonix-widget-es-feature-cards', 'swiper' );
	}

	public function get_script_depends() {
		return array( 'elonix-widget-es-feature-cards', 'swiper' );
	}

	protected function register_controls() {

		$this->register_content_general_controls();
		$this->register_content_layout_controls();
		$this->register_content_carousel_controls();

		\Elonix_Style_Manager::register_card_style( $this, 'section_style_card', 'card', '{{WRAPPER}} .es-fc-card', '{{WRAPPER}} .elonix-feature-cards' );
		$this->register_style_card_inner_controls();

		\Elonix_Style_Manager::register_icon_style( $this, 'section_style_icon', 'icon', '{{WRAPPER}} .es-fc-icon', '{{WRAPPER}} .es-fc-card:hover .es-fc-icon' );
		$this->register_style_image_controls();

		\Elonix_Style_Manager::register_title_style( $this, 'section_style_title', 'title', '{{WRAPPER}} .es-fc-title', '{{WRAPPER}} .es-fc-card:hover .es-fc-title' );
		$this->register_style_subtitle_controls();
		$this->register_style_description_controls();

		\Elonix_Style_Manager::register_badge_style( $this, 'section_style_badge', 'badge', '{{WRAPPER}} .es-fc-badge', '{{WRAPPER}} .es-fc-card:hover .es-fc-badge' );
		$this->register_style_number_controls();

		\Elonix_Style_Manager::register_button_style( $this, 'section_style_button', 'button', '{{WRAPPER}} .es-fc-button' );

		$this->register_style_footer_controls();
		$this->register_style_navigation_controls();
		$this->register_style_pagination_controls();
		$this->register_style_scrollbar_controls();
		$this->register_style_spacing_controls();
		$this->register_style_effects_controls();
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
			'style',
			array(
				'label'   => esc_html__( 'Choose Style', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'style-01',
				'options' => array(
					'style-01' => esc_html__( 'Style 01', 'elonix' ),
					'style-02' => esc_html__( 'Style 02', 'elonix' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'media_type',
			array(
				'label'   => esc_html__( 'Media Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'none'  => array(
						'title' => esc_html__( 'None', 'elonix' ),
						'icon'  => 'eicon-ban',
					),
					'icon'  => array(
						'title' => esc_html__( 'Icon', 'elonix' ),
						'icon'  => 'eicon-star',
					),
					'image' => array(
						'title' => esc_html__( 'Image', 'elonix' ),
						'icon'  => 'eicon-image',
					),
				),
				'default' => 'icon',
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'     => esc_html__( 'Icon', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'condition' => array( 'media_type' => 'icon' ),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'     => esc_html__( 'Image', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => array( 'media_type' => 'image' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Feature Title', 'elonix' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'subtitle',
			array(
				'label'       => esc_html__( 'Subtitle', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Subtitle here', 'elonix' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'description',
			array(
				'label'   => esc_html__( 'Description', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'This is the description of the feature card. You can add more text here.', 'elonix' ),
			)
		);

		$repeater->add_control(
			'badge_text',
			array(
				'label' => esc_html__( 'Badge Text', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'number_text',
			array(
				'label' => esc_html__( 'Number (e.g. 01)', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'rating_value',
			array(
				'label' => esc_html__( 'Rating (0-5)', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::NUMBER,
				'min'   => 0,
				'max'   => 5,
				'step'  => 0.5,
			)
		);

		$repeater->add_control(
			'button_text',
			array(
				'label' => esc_html__( 'Button Text', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'button_link',
			array(
				'label' => esc_html__( 'Link', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::URL,
			)
		);

		$repeater->add_control(
			'link_whole_card',
			array(
				'label'        => esc_html__( 'Link Whole Card?', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$repeater->add_control(
			'button_icon',
			array(
				'label' => esc_html__( 'Button Icon', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::ICONS,
			)
		);

		$repeater->add_control(
			'button_icon_position',
			array(
				'label'   => esc_html__( 'Icon Position', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'after',
				'options' => array(
					'before' => esc_html__( 'Before', 'elonix' ),
					'after'  => esc_html__( 'After', 'elonix' ),
				),
			)
		);

		$repeater->add_control(
			'footer_label',
			array(
				'label' => esc_html__( 'Footer Label', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'custom_class',
			array(
				'label' => esc_html__( 'Custom CSS Class', 'elonix' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);

		$this->add_control(
			'cards',
			array(
				'label'       => esc_html__( 'Feature Cards', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'title'      => esc_html__( 'Card 1', 'elonix' ),
						'media_type' => 'icon',
					),
					array(
						'title'      => esc_html__( 'Card 2', 'elonix' ),
						'media_type' => 'icon',
					),
					array(
						'title'      => esc_html__( 'Card 3', 'elonix' ),
						'media_type' => 'icon',
					),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'Title HTML Tag', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
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
				'default' => 'h3',
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
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

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Columns', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 6,
				'default'   => 3,
				'selectors' => array(
					'{{WRAPPER}} .elonix-feature-cards' => '--es-fc-columns: {{VALUE}};',
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
					'{{WRAPPER}} .elonix-feature-cards' => '--es-fc-gap-column: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .elonix-feature-cards' => '--es-fc-gap-row: {{SIZE}}{{UNIT}};',
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

		$this->end_controls_section();
	}




	protected function register_style_card_inner_controls() {
		$this->start_controls_section(
			'section_style_card_inner',
			array(
				'label' => esc_html__( 'Card Inner', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'card_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .elonix-feature-cards' => '--es-fc-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_inner_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-card-inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_inner_min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .es-fc-card-inner' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_inner_align_x',
			array(
				'label'     => esc_html__( 'Horizontal Alignment', 'elonix' ),
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
				'selectors' => array( '{{WRAPPER}} .es-fc-card-inner, {{WRAPPER}} .es-fc-card-body, {{WRAPPER}} .es-fc-meta' => 'align-items: {{VALUE}}; text-align: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'card_inner_align_y',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start'    => array(
						'title' => 'Top',
						'icon'  => 'eicon-v-align-top',
					),
					'center'        => array(
						'title' => 'Middle',
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'      => array(
						'title' => 'Bottom',
						'icon'  => 'eicon-v-align-bottom',
					),
					'space-between' => array(
						'title' => 'Space Between',
						'icon'  => 'eicon-v-align-stretch',
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-card-inner' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}



	protected function register_style_image_controls() {
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_image_style' );

		$this->start_controls_tab( 'tab_image_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .es-fc-image img' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-image img' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'image_object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					''        => 'Default',
					'cover'   => 'Cover',
					'contain' => 'Contain',
					'fill'    => 'Fill',
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-image img' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .es-fc-image img',
			)
		);
		$this->add_responsive_control(
			'image_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .es-fc-image img',
			)
		);
		$this->add_control(
			'image_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-image img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters',
				'selector' => '{{WRAPPER}} .es-fc-image img',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_image_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border_hover',
				'selector' => '{{WRAPPER}} .es-fc-card:hover .es-fc-image img',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow_hover',
				'selector' => '{{WRAPPER}} .es-fc-card:hover .es-fc-image img',
			)
		);
		$this->add_control(
			'image_opacity_hover',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-image img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_responsive_control(
			'image_scale_hover',
			array(
				'label'     => esc_html__( 'Scale', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.5,
						'max'  => 2,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-image img' => 'transform: scale({{SIZE}});' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters_hover',
				'selector' => '{{WRAPPER}} .es-fc-card:hover .es-fc-image img',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}



	protected function register_style_subtitle_controls() {
		$this->start_controls_section(
			'section_style_subtitle',
			array(
				'label' => esc_html__( 'Subtitle', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_subtitle_style' );

		$this->start_controls_tab( 'tab_subtitle_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'subtitle_typography',
				'selector' => '{{WRAPPER}} .es-fc-subtitle',
			)
		);
		$this->add_control(
			'subtitle_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-subtitle' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'subtitle_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_subtitle_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'subtitle_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-subtitle' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_description_controls() {
		$this->start_controls_section(
			'section_style_description',
			array(
				'label' => esc_html__( 'Description', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_description_style' );

		$this->start_controls_tab( 'tab_description_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .es-fc-description',
			)
		);
		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-description' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'description_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_description_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'description_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-description' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}



	protected function register_style_number_controls() {
		$this->start_controls_section(
			'section_style_number',
			array(
				'label' => esc_html__( 'Number', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_number_style' );

		$this->start_controls_tab( 'tab_number_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'number_typography',
				'selector' => '{{WRAPPER}} .es-fc-number',
			)
		);
		$this->add_control(
			'number_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-number' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'number_bg',
				'selector' => '{{WRAPPER}} .es-fc-number',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'number_border',
				'selector' => '{{WRAPPER}} .es-fc-number',
			)
		);
		$this->add_responsive_control(
			'number_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'number_width',
			array(
				'label'     => esc_html__( 'Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-number' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'number_height',
			array(
				'label'     => esc_html__( 'Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-number' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'number_alignment',
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
				'selectors' => array( '{{WRAPPER}} .es-fc-number' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_number_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'number_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-number' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'number_bg_hover',
				'selector' => '{{WRAPPER}} .es-fc-card:hover .es-fc-number',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}



	protected function register_style_footer_controls() {
		$this->start_controls_section(
			'section_style_footer',
			array(
				'label' => esc_html__( 'Footer', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_footer_style' );

		$this->start_controls_tab( 'tab_footer_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'footer_typography',
				'selector' => '{{WRAPPER}} .es-fc-footer',
			)
		);
		$this->add_control(
			'footer_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-footer' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'footer_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-fc-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'footer_border_top_width',
			array(
				'label'     => esc_html__( 'Border Top Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-fc-footer' => 'border-top-width: {{SIZE}}{{UNIT}}; border-top-style: solid;' ),
			)
		);
		$this->add_control(
			'footer_border_top_color',
			array(
				'label'     => esc_html__( 'Border Top Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-footer' => 'border-top-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_footer_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'footer_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .es-fc-card:hover .es-fc-footer' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_navigation_controls() {
		$this->start_controls_section(
			'section_style_navigation',
			array(
				'label' => esc_html__( 'Navigation', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_nav_style' );

		$this->start_controls_tab( 'tab_nav_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_responsive_control(
			'nav_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'nav_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
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
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_width',
			array(
				'label'     => esc_html__( 'Width', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 20,
						'max' => 150,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'nav_height',
			array(
				'label'     => esc_html__( 'Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 20,
						'max' => 150,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'nav_shadow',
				'selector' => '{{WRAPPER}} .es-swiper-button-prev, {{WRAPPER}} .es-swiper-button-next',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_nav_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'nav_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
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
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_nav_disabled', array( 'label' => esc_html__( 'Disabled', 'elonix' ) ) );
		$this->add_control(
			'nav_opacity_disabled',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .es-swiper-button-prev.swiper-button-disabled, {{WRAPPER}} .es-swiper-button-next.swiper-button-disabled' => 'opacity: {{SIZE}};' ),
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
				'label' => esc_html__( 'Pagination', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_pagination_style' );

		$this->start_controls_tab( 'tab_pagination_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_responsive_control(
			'pagination_size',
			array(
				'label'     => esc_html__( 'Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 4,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_gap',
			array(
				'label'     => esc_html__( 'Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'pagination_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_pagination_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );
		$this->add_control(
			'pagination_color_active',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_scale_active',
			array(
				'label'     => esc_html__( 'Scale', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'transform: scale({{SIZE}});' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_scrollbar_controls() {
		// Implemented as part of pagination if needed, or skipped if not critical.
		// For compliance with "14. Scrollbar" I will add it briefly.
		$this->start_controls_section(
			'section_style_scrollbar',
			array(
				'label' => esc_html__( 'Scrollbar', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'scrollbar_height',
			array(
				'label'     => esc_html__( 'Height', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .swiper-scrollbar' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'scrollbar_bg',
			array(
				'label'     => esc_html__( 'Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-scrollbar' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'scrollbar_handle',
			array(
				'label'     => esc_html__( 'Handle Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .swiper-scrollbar-drag' => 'background: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_spacing_controls() {
		$this->start_controls_section(
			'section_style_spacing',
			array(
				'label' => esc_html__( 'Spacing', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'spacing_icon',
			array(
				'label'     => esc_html__( 'Icon / Image Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-fc-icon, {{WRAPPER}} .es-fc-image' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'spacing_title',
			array(
				'label'     => esc_html__( 'Title Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-fc-title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'spacing_description',
			array(
				'label'     => esc_html__( 'Description Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-fc-description' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'spacing_button',
			array(
				'label'     => esc_html__( 'Button Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-fc-button' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'spacing_footer',
			array(
				'label'     => esc_html__( 'Footer Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .es-fc-footer' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_effects_controls() {
		$this->start_controls_section(
			'section_style_effects',
			array(
				'label' => esc_html__( 'Hover Engine', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'hover_effect',
			array(
				'label'   => esc_html__( 'Hover Effect', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'es-hover-lift',
				'options' => array(
					'none'                 => 'None',
					'es-hover-lift'        => 'Lift',
					'es-hover-scale'       => 'Scale',
					'es-hover-glow'        => 'Glow',
					'es-hover-border-glow' => 'Border Glow',
					'es-hover-shadow-grow' => 'Shadow Grow',
				),
			)
		);
		$this->add_control(
			'hover_duration',
			array(
				'label'     => esc_html__( 'Hover Duration (s)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .elonix-feature-cards' => '--es-fc-transition: all {{SIZE}}s ease-in-out;' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$wrapper_classes = array(
			'elonix-feature-cards',
			esc_attr( $settings['hover_effect'] ),
		);

		$is_carousel = 'carousel' === $settings['layout_mode'];
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<?php if ( $is_carousel ) : ?>
				<?php
					$carousel_settings = array(
						'loop'          => $settings['loop'] ?? 'no',
						'autoplay'      => $settings['autoplay'] ?? 'no',
						'autoplaySpeed' => $settings['autoplaySpeed'] ?? 5000,
						'speed'         => $settings['speed'] ?? 500,
						'pauseOnHover'  => $settings['pause_on_hover'] ?? 'yes',
						'navigation'    => $settings['navigation'] ?? 'yes',
						'pagination'    => $settings['pagination'] ?? 'yes',
						'grabCursor'    => $settings['grab_cursor'] ?? 'no',
						'mousewheel'    => $settings['mousewheel'] ?? 'no',
						'keyboard'      => $settings['keyboard'] ?? 'no',
					);

					// Forward raw responsive settings directly to the JS Core Framework
					// This automatically supports any custom breakpoint (laptop, mobile_extra, etc.)
					foreach ( $settings as $key => $value ) {
						if ( strpos( $key, 'slides_per_view' ) === 0 ||
							strpos( $key, 'slides_per_group' ) === 0 ||
							strpos( $key, 'column_gap' ) === 0 ) {
							$carousel_settings[ $key ] = $value;
						}
					}
					?>
				<div class="es-fc-carousel" data-settings="<?php echo esc_attr( wp_json_encode( $carousel_settings ) ); ?>">
					<div class="swiper-container swiper">
						<div class="swiper-wrapper">
			<?php else : ?>
				<div class="es-fc-grid">
			<?php endif; ?>

			<?php
			foreach ( $settings['cards'] as $index => $item ) :
				$is_link_wrapper   = ! empty( $item['link_whole_card'] ) && 'yes' === $item['link_whole_card'] && ! empty( $item['button_link']['url'] );
				$wrapper_link_attr = '';
				if ( $is_link_wrapper ) {
					$link_key = 'card_link_' . $index;
					$this->add_link_attributes( $link_key, $item['button_link'] );
					$wrapper_link_attr = $this->get_render_attribute_string( $link_key );
				}

				if ( $is_carousel ) {
					echo '<div class="swiper-slide">';
				}

				$style         = $settings['style'];
				$template_path = __DIR__ . '/styles/' . $style . '.php';
				if ( file_exists( $template_path ) ) {
					include $template_path;
				}

				if ( $is_carousel ) {
					echo '</div>';
				}
			endforeach;
			?>
			
			<?php if ( $is_carousel ) : ?>
						</div>
						<?php if ( 'yes' === $settings['pagination'] ) : ?>
							<div class="swiper-pagination"></div>
						<?php endif; ?>
					</div>
					<?php if ( 'yes' === $settings['navigation'] ) : ?>
						<div class="es-swiper-button-prev"><i class="eicon-chevron-left" aria-hidden="true"></i></div>
						<div class="es-swiper-button-next"><i class="eicon-chevron-right" aria-hidden="true"></i></div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
