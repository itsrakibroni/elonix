<?php
/**
 * Elonix – Toolkit for Elementor Gallery Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Gallery_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'tv-gallery';
	}

	public function get_title() {
		return esc_html__( 'Gallery', 'elonix' );
	}

	public function get_tv_widget_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return array( 'gallery', 'image', 'photo', 'portfolio', 'grid', 'masonry', 'tvkit' );
	}

	public function get_style_depends() {
		return array( 'elonix-widget-tv-gallery' );
	}

	public function get_script_depends() {
		return array( 'elonix-widget-tv-gallery', 'isotope', 'imagesloaded' );
	}

	protected function register_controls() {
		$this->register_content_general_controls();
		$this->register_content_layout_controls();

		$this->register_content_filter_controls();
		$this->register_content_lightbox_controls();
		$this->register_content_elements_controls();
		$this->register_content_load_more_controls();

		$this->register_style_card_controls();
		$this->register_style_image_controls();
		$this->register_style_overlay_controls();
		$this->register_style_content_controls();
		$this->register_style_badge_controls();
		$this->register_style_filter_controls();
		$this->register_style_loader_controls();
		$this->register_style_empty_state_controls();
		$this->register_style_load_more_controls();
	}

	protected function register_content_general_controls() {
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'Query', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'data_source',
			array(
				'label'   => esc_html__( 'Data Source', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => array(
					'manual' => esc_html__( 'Manual Gallery', 'elonix' ),
					'posts'  => esc_html__( 'Posts', 'elonix' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Image', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'   => esc_html__( 'Title', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Gallery Item', 'elonix' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'subtitle',
			array(
				'label'   => esc_html__( 'Subtitle', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'description',
			array(
				'label'   => esc_html__( 'Description', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Category (For Filter)', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter a category name. Items with the same category will be grouped in filters.', 'elonix' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'url',
			array(
				'label'   => esc_html__( 'Custom Link', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'badge_text',
			array(
				'label'   => esc_html__( 'Badge Text', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Gallery Items', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'title' => esc_html__( 'Item 1', 'elonix' ) ),
					array( 'title' => esc_html__( 'Item 2', 'elonix' ) ),
					array( 'title' => esc_html__( 'Item 3', 'elonix' ) ),
				),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'data_source' => 'manual' ),
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

		$this->add_control(
			'layout_mode',
			array(
				'label'              => esc_html__( 'Layout Mode', 'elonix' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'default'            => 'grid',
				'frontend_available' => true,
				'options'            => array(
					'grid'    => esc_html__( 'Grid', 'elonix' ),
					'masonry' => esc_html__( 'Masonry', 'elonix' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Columns', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 8,
				'default'   => 3,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__grid' => '--tv-gallery-columns: {{VALUE}};',
				),
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
					'{{WRAPPER}} .tv-gallery__grid' => '--tv-gallery-gap-x: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .tv-gallery__grid' => '--tv-gallery-gap-y: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_ratio',
			array(
				'label'     => esc_html__( 'Image Ratio', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 3,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__image' => 'aspect-ratio: {{SIZE}};',
				),
				'condition' => array(
					'layout_mode' => 'masonry',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_filter_controls() {
		$this->start_controls_section(
			'section_filter',
			array(
				'label' => esc_html__( 'Filter', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_filter',
			array(
				'label'   => esc_html__( 'Show Filter Bar', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'filter_all_label',
			array(
				'label'     => esc_html__( '"All" Label', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'All', 'elonix' ),
				'condition' => array( 'show_filter' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_load_more_controls() {
		$this->start_controls_section(
			'section_load_more',
			array(
				'label' => esc_html__( 'Load More', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_load_more',
			array(
				'label'   => esc_html__( 'Enable Load More', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'initial_item_count',
			array(
				'label'     => esc_html__( 'Initial Item Count', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 6,
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->add_control(
			'items_per_click',
			array(
				'label'     => esc_html__( 'Items Per Click', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3,
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'     => esc_html__( 'Button Text', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Load More', 'elonix' ),
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->add_control(
			'loading_text',
			array(
				'label'     => esc_html__( 'Loading Text', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Loading...', 'elonix' ),
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'button_alignment',
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
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-wrapper' => 'justify-content: {{VALUE}};',
				),
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_lightbox_controls() {
		$this->start_controls_section(
			'section_lightbox',
			array(
				'label' => esc_html__( 'Lightbox', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_lightbox',
			array(
				'label'   => esc_html__( 'Enable Lightbox', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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
			'show_title',
			array(
				'label'   => esc_html__( 'Show Title', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_subtitle',
			array(
				'label'   => esc_html__( 'Show Subtitle', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_description',
			array(
				'label'   => esc_html__( 'Show Description', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_category',
			array(
				'label'   => esc_html__( 'Show Category', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'   => esc_html__( 'Show Badge', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_card_controls() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Card / Item', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_card_style' );

		$this->start_controls_tab( 'tab_card_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__item',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .tv-gallery__item',
			)
		);
		$this->add_responsive_control(
			'card_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'card_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .tv-gallery__item',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_card_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item:hover' => 'transform: translateY({{SIZE}}{{UNIT}});' ),
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item' => 'transition: all {{SIZE}}s ease;' ),
				'separator' => 'before',
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
		$this->add_control(
			'image_opacity',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__image img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters',
				'selector' => '{{WRAPPER}} .tv-gallery__image img',
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_image_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'image_opacity_hover',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__image img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_control(
			'image_scale_hover',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__image img' => 'transform: scale({{SIZE}});' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__image img',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->add_control(
			'image_transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration (s)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-gallery__image img' => 'transition: all {{SIZE}}s ease;' ),
				'separator' => 'before',
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_overlay_controls() {
		$this->start_controls_section(
			'section_style_overlay',
			array(
				'label' => esc_html__( 'Overlay', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_overlay_style' );

		$this->start_controls_tab( 'tab_overlay_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__overlay',
			)
		);
		$this->add_control(
			'overlay_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.05,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-gallery__overlay' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_overlay_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_bg_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__overlay',
			)
		);
		$this->add_control(
			'overlay_opacity_hover',
			array(
				'label'     => esc_html__( 'Hover Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0,
						'step' => 0.05,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__overlay' => 'opacity: {{SIZE}};' ),
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
				'label' => esc_html__( 'Content', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'content_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'content_alignment',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__content' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'heading_title_style',
			array(
				'label'     => esc_html__( 'Title', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_title' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_typography',
				'selector'  => '{{WRAPPER}} .tv-gallery__title',
				'condition' => array( 'show_title' => 'yes' ),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__title' => 'color: {{VALUE}};' ),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);
		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_badge_controls() {
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label'     => esc_html__( 'Badge', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .tv-gallery__badge',
			)
		);
		$this->start_controls_tabs( 'tabs_badge_style' );
		$this->start_controls_tab( 'tab_badge_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_control(
			'badge_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__badge' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'badge_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__badge',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'badge_border',
				'selector' => '{{WRAPPER}} .tv-gallery__badge',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'badge_box_shadow',
				'selector' => '{{WRAPPER}} .tv-gallery__badge',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_badge_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'badge_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__badge' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'badge_bg_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__badge',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'badge_border_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__badge',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'badge_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__item:hover .tv-gallery__badge',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'badge_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'badge_margin',
			array(
				'label'     => esc_html__( 'Position Offset', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__badge' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_filter_controls() {
		$this->start_controls_section(
			'section_style_filter',
			array(
				'label'     => esc_html__( 'Filter', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_filter' => 'yes' ),
			)
		);
		$this->add_responsive_control(
			'filter_alignment',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-bar' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'filter_gap',
			array(
				'label'     => esc_html__( 'Gap', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-bar' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'filter_margin',
			array(
				'label'     => esc_html__( 'Margin Bottom', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-bar' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn',
			)
		);
		$this->add_responsive_control(
			'filter_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'filter_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'tabs_filter_style' );
		$this->start_controls_tab( 'tab_filter_normal', array( 'label' => esc_html__( 'Normal', 'elonix' ) ) );
		$this->add_control(
			'filter_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'filter_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_border',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_filter_hover', array( 'label' => esc_html__( 'Hover', 'elonix' ) ) );
		$this->add_control(
			'filter_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'filter_bg_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn:hover',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_border_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn:hover',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_filter_active', array( 'label' => esc_html__( 'Active', 'elonix' ) ) );
		$this->add_control(
			'filter_color_active',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__filter-btn.tv-active' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'filter_bg_active',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn.tv-active',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_border_active',
				'selector' => '{{WRAPPER}} .tv-gallery__filter-btn.tv-active',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function register_style_loader_controls() {
		$this->start_controls_section(
			'section_style_loader',
			array(
				'label' => esc_html__( 'Loader', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'loader_color',
			array(
				'label'     => esc_html__( 'Spinner Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__loader-spinner' => 'border-top-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'loader_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__loader' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'loader_size',
			array(
				'label'     => esc_html__( 'Spinner Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__loader-spinner' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'loader_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__loader' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_empty_state_controls() {
		$this->start_controls_section(
			'section_style_empty',
			array(
				'label' => esc_html__( 'Empty State', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_typography',
				'selector' => '{{WRAPPER}} .tv-gallery__empty',
			)
		);
		$this->add_control(
			'empty_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__empty' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'empty_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__empty',
			)
		);
		$this->add_responsive_control(
			'empty_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .tv-gallery__empty' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'empty_alignment',
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
				'selectors' => array( '{{WRAPPER}} .tv-gallery__empty' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function register_style_load_more_controls() {
		$this->start_controls_section(
			'section_style_load_more',
			array(
				'label'     => esc_html__( 'Load More Button', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'enable_load_more' => 'yes' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'load_more_typography',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn',
			)
		);

		$this->add_responsive_control(
			'load_more_padding',
			array(
				'label'     => esc_html__( 'Padding', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'load_more_margin',
			array(
				'label'     => esc_html__( 'Margin', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'load_more_radius',
			array(
				'label'     => esc_html__( 'Border Radius', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::DIMENSIONS,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_load_more_style' );

		$this->start_controls_tab(
			'tab_load_more_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'load_more_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'load_more_bg',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'load_more_border',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'load_more_box_shadow',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_load_more_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'load_more_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-gallery__load-more-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'load_more_bg_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn:hover',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'load_more_border_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn:hover',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'load_more_box_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-gallery__load-more-btn:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Normalize data from multiple sources (Repeater, Posts, WooCommerce, etc)
	 * into one unified standard array for layout agnostic rendering.
	 */
	public function normalize_data( $settings, $offset = 0, $limit = null, $active_filter = '*' ) {
		$normalized = array();

		if ( 'manual' === $settings['data_source'] && ! empty( $settings['items'] ) ) {

			// 1. Filter first
			$filtered_items = array();
			foreach ( $settings['items'] as $item ) {
				if ( '*' !== $active_filter ) {
					$item_category = strtolower( sanitize_title( $item['category'] ) );
					$filter_slug   = strtolower( sanitize_title( $active_filter ) );
					if ( $item_category !== $filter_slug ) {
						continue; // Skip items that don't match active filter
					}
				}
				$filtered_items[] = $item;
			}

			// 2. Slice second
			if ( null !== $limit ) {
				$filtered_items = array_slice( $filtered_items, $offset, $limit );
			} else {
				$filtered_items = array_slice( $filtered_items, $offset );
			}

			// 3. Normalize
			foreach ( $filtered_items as $index => $item ) {
				$image_url = '';
				$alt       = '';
				if ( ! empty( $item['image']['url'] ) ) {
					$image_url = $item['image']['url'];
					$alt       = \Elementor\Control_Media::get_image_alt( $item['image'] );
				}

				$normalized[] = array(
					'id'          => 'item-' . $item['_id'],
					'image'       => $item['image'],
					'thumbnail'   => $image_url,
					'full_image'  => $image_url,
					'title'       => $item['title'],
					'subtitle'    => $item['subtitle'],
					'description' => $item['description'],
					'category'    => $item['category'],
					'badge'       => $item['badge_text'],
					'link'        => $item['url'],
					'lightbox'    => $image_url,
					'alt'         => $alt,
					'caption'     => '',
					'custom'      => array(),
				);
			}
		}

		// Future sources (Posts, WooCommerce) will be appended here mapping to the exact same array structure.

		return $normalized;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$wrapper_classes = array(
			'tv-gallery',
			'tv-gallery--' . esc_attr( $settings['layout_mode'] ),
		);

		// 1. Normalize Data (Universal Rendering Pipeline)
		$all_items = $this->normalize_data( $settings, 0, null, '*' );

		$is_load_more_enabled = 'yes' === $settings['enable_load_more'];
		$initial_limit        = $is_load_more_enabled ? absint( $settings['initial_item_count'] ) : null;

		$gallery_items = $this->normalize_data( $settings, 0, $initial_limit, '*' );

		// 2. Empty State Check
		if ( empty( $gallery_items ) ) {
			include __DIR__ . '/views/parts/empty-state.php';
			return;
		}

		$wrapper_attrs = array();
		if ( $is_load_more_enabled ) {
			$ajax_settings   = array(
				'post_id'   => get_the_ID(),
				'widget_id' => $this->get_id(),
				'nonce'     => wp_create_nonce( 'tv_gallery_ajax_nonce' ),
				'limit'     => absint( $settings['items_per_click'] ),
			);
			$wrapper_attrs[] = 'data-ajax-settings=\'' . wp_json_encode( $ajax_settings ) . '\'';
		}

		// 3. Layout Router
		?>
		<div class="<?php echo implode( ' ', array_map( 'esc_attr', $wrapper_classes ) ); ?>" <?php echo implode( ' ', $wrapper_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			if ( 'yes' === $settings['show_filter'] ) {
				include __DIR__ . '/views/parts/filter-bar.php';
			}

			$layout_file = __DIR__ . '/views/layout-' . $settings['layout_mode'] . '.php';
			if ( file_exists( $layout_file ) ) {
				include $layout_file;
			}

			if ( $is_load_more_enabled ) {
				include __DIR__ . '/views/parts/load-more.php';
			}
			?>
		</div>
		<?php
	}
}
