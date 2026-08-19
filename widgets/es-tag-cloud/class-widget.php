<?php
/**
 * Elonix Smart Taxonomy Filter Widget Class
 *
 * A premium highly customizable taxonomy and term filter widget for Elementor,
 * supporting 8 layouts, dynamic taxonomy selection, WooCommerce, CPTs, live AJAX
 * filtering, transients query caching, and full WCAG accessibility.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

// Reuse taxonomy engine from es-categories if not loaded
if ( ! class_exists( 'Elonix_Toolkit_Taxonomy_Helper' ) ) {
	$elonix_fallback_path = ELONIX_ACC_PATH . 'widgets/es-categories/helper-taxonomy.php';
	if ( file_exists( $elonix_fallback_path ) ) {
		require_once $elonix_fallback_path;
	}
}

class Elonix_Toolkit_Tag_Cloud_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-tag-cloud';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Smart Taxonomy Filter', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-tags';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'tag', 'cloud', 'category', 'taxonomy', 'filter', 'ajax', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-tag-cloud' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-tag-cloud' );
	}

	/**
	 * Helper: Fetch all public taxonomies dynamically.
	 *
	 * @return array Taxonomies dictionary.
	 */
	protected function get_taxonomies_options() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		$options    = array();
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, array( 'nav_menu', 'link_category', 'post_format' ), true ) ) {
				continue;
			}
			$options[ $taxonomy->name ] = sprintf( '%s (%s)', $taxonomy->label, $taxonomy->name );
		}
		return $options;
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		// 1. Taxonomy Source Options Section
		$this->start_controls_section(
			'section_taxonomy_source',
			array(
				'label' => esc_html__( 'Taxonomy & Source', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'taxonomy',
			array(
				'label'   => esc_html__( 'Select Taxonomy', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_tag',
				'options' => $this->get_taxonomies_options(),
			)
		);

		$this->add_control(
			'selection_mode',
			array(
				'label'   => esc_html__( 'Terms Selection', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'      => esc_html__( 'All Terms', 'elonix' ),
					'popular'  => esc_html__( 'Popular Terms (Post Count)', 'elonix' ),
					'recent'   => esc_html__( 'Recently Created', 'elonix' ),
					'featured' => esc_html__( 'Featured Terms (Meta Check)', 'elonix' ),
					'manual'   => esc_html__( 'Manual Term IDs', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'manual_ids',
			array(
				'label'       => esc_html__( 'Manual IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '4, 12, 18',
				'condition'   => array(
					'selection_mode' => 'manual',
				),
			)
		);

		$this->add_control(
			'include_ids',
			array(
				'label'       => esc_html__( 'Include IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '1, 5',
				'condition'   => array(
					'selection_mode!' => 'manual',
				),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => esc_html__( 'Exclude IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '2, 9',
				'condition'   => array(
					'selection_mode!' => 'manual',
				),
			)
		);

		$this->add_control(
			'parent_only',
			array(
				'label'        => esc_html__( 'Parent Terms Only', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'child_only',
			array(
				'label'        => esc_html__( 'Child Terms Only', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'parent_only!' => 'yes',
				),
			)
		);

		$this->add_control(
			'hide_empty',
			array(
				'label'        => esc_html__( 'Hide Empty Terms', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Terms Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 15,
				'min'     => 1,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'     => esc_html__( 'Order By', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'name',
				'options'   => array(
					'name'  => esc_html__( 'Name', 'elonix' ),
					'count' => esc_html__( 'Post Count', 'elonix' ),
					'id'    => esc_html__( 'Term ID', 'elonix' ),
					'slug'  => esc_html__( 'Slug', 'elonix' ),
				),
				'condition' => array(
					'selection_mode!' => array( 'popular', 'recent' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order Direction', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ASC',
				'options'   => array(
					'ASC'  => esc_html__( 'Ascending', 'elonix' ),
					'DESC' => esc_html__( 'Descending', 'elonix' ),
				),
				'condition' => array(
					'selection_mode!' => array( 'popular', 'recent' ),
				),
			)
		);

		$this->end_controls_section();

		// 2. Layout Selection Section
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Options', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Select Layout Template', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tag_cloud',
				'options' => array(
					'tag_cloud'        => esc_html__( 'Tag Cloud', 'elonix' ),
					'pills'            => esc_html__( 'Pills', 'elonix' ),
					'modern_pills'     => esc_html__( 'Modern Pills', 'elonix' ),
					'glass_pills'      => esc_html__( 'Glass Pills', 'elonix' ),
					'icon_tags'        => esc_html__( 'Icon Tags', 'elonix' ),
					'category_cards'   => esc_html__( 'Category Cards', 'elonix' ),
					'popular_topics'   => esc_html__( 'Popular Topics', 'elonix' ),
					'ajax_filter_tags' => esc_html__( 'AJAX Filter Tags', 'elonix' ),
				),
			)
		);

		$this->add_responsive_control(
			'grid_columns',
			array(
				'label'          => esc_html__( 'Grid Columns', 'elonix' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1 Column',
					'2' => '2 Columns',
					'3' => '3 Columns',
					'4' => '4 Columns',
					'5' => '5 Columns',
					'6' => '6 Columns',
				),
				'selectors'      => array(
					'{{WRAPPER}} .es-tag-cloud-container' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
				'condition'      => array(
					'layout' => array( 'category_cards', 'popular_topics' ),
				),
			)
		);

		$this->end_controls_section();

		// 3. Category Content Components switches
		$this->start_controls_section(
			'section_category_content',
			array(
				'label' => esc_html__( 'Category Content', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => esc_html__( 'Show Post Count', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'count_format',
			array(
				'label'     => esc_html__( 'Count Format', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => array(
					'normal'      => 'Normal (12)',
					'parenthesis' => 'Parenthesis (12)',
					'badge'       => 'Pill Badge',
				),
				'condition' => array(
					'show_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => esc_html__( 'Show Icon', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'cat_icon_source',
			array(
				'label'     => esc_html__( 'Category Icon Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'icon_library',
				'options'   => array(
					'icon_library' => esc_html__( 'Icon Library', 'elonix' ),
					'svg_upload'   => esc_html__( 'SVG Upload', 'elonix' ),
					'image_upload' => esc_html__( 'Image Upload', 'elonix' ),
					'dynamic'      => esc_html__( 'Dynamic Term Icon', 'elonix' ),
					'woo'          => esc_html__( 'Woo Category Icon', 'elonix' ),
				),
				'condition' => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_icon_library',
			array(
				'label'     => esc_html__( 'Icon Library', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-folder',
					'library' => 'solid',
				),
				'condition' => array(
					'show_icon'       => 'yes',
					'cat_icon_source' => 'icon_library',
				),
			)
		);

		$this->add_control(
			'cat_icon_svg',
			array(
				'label'     => esc_html__( 'Upload SVG', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'show_icon'       => 'yes',
					'cat_icon_source' => 'svg_upload',
				),
			)
		);

		$this->add_control(
			'cat_icon_image',
			array(
				'label'     => esc_html__( 'Upload Image', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'show_icon'       => 'yes',
					'cat_icon_source' => 'image_upload',
				),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => esc_html__( 'Icon Position', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Before Title', 'elonix' ),
					'after'  => esc_html__( 'After Title', 'elonix' ),
					'above'  => esc_html__( 'Above Title', 'elonix' ),
				),
				'condition' => array(
					'show_icon' => 'yes',
					'layout!'   => 'icon_tags',
				),
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => esc_html__( 'Show Badge Overlay', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'     => esc_html__( 'Badge Content Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'NEW',
				'condition' => array(
					'show_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_tooltip',
			array(
				'label'        => esc_html__( 'Show Tooltip Info', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'tooltip_type',
			array(
				'label'     => esc_html__( 'Tooltip Description Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'count',
				'options'   => array(
					'count'       => esc_html__( 'Post Counts Text', 'elonix' ),
					'description' => esc_html__( 'Term Description Meta', 'elonix' ),
				),
				'condition' => array(
					'show_tooltip' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 4. AJAX filter interface triggers
		$this->start_controls_section(
			'section_ajax_filter',
			array(
				'label' => esc_html__( 'AJAX Live Filtering', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'interaction_mode',
			array(
				'label'   => esc_html__( 'Tag Actions Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'link',
				'options' => array(
					'link'              => esc_html__( 'Static Archive Links', 'elonix' ),
					'ajax_filter'       => esc_html__( 'AJAX Live Filter (Single Select)', 'elonix' ),
					'ajax_multi_select' => esc_html__( 'AJAX Live Filter (Multi Select)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'target_widget_id',
			array(
				'label'       => esc_html__( 'Target Post List CSS ID', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter the CSS ID (under Advanced > CSS ID) of the target Elonix Post List widget to filter.', 'elonix' ),
				'condition'   => array(
					'interaction_mode' => array( 'ajax_filter', 'ajax_multi_select' ),
				),
			)
		);

		$this->add_control(
			'clear_filters_text',
			array(
				'label'     => esc_html__( 'Clear Filter Button Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Clear Active Filters',
				'condition' => array(
					'interaction_mode' => 'ajax_multi_select',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// 1. Card Container Style
		$this->start_controls_section(
			'section_style_container',
			array(
				'label' => esc_html__( 'Filter Container', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-cloud-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'tag_gap_row',
			array(
				'label'      => esc_html__( 'Row Spacing (Gap)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-cloud-container' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'tag_gap_col',
			array(
				'label'      => esc_html__( 'Column Spacing (Gap)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-cloud-container' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-tag-cloud-container' => 'justify-content: {{VALUE}};',
				),
				'condition' => array(
					'layout!' => array( 'category_cards', 'popular_topics' ),
				),
			)
		);

		$this->end_controls_section();

		// 2. Tag Item Styles
		$this->start_controls_section(
			'section_style_tag_item',
			array(
				'label' => esc_html__( 'Tag Item', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tag_typography',
				'selector' => '{{WRAPPER}} .es-tag-item',
			)
		);

		$this->start_controls_tabs( 'tabs_tag_item_colors' );

		// Normal State Tab
		$this->start_controls_tab(
			'tab_tag_item_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'tag_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item, {{WRAPPER}} .es-tag-item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tag_bg',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-tag-item',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'tag_border',
				'selector' => '{{WRAPPER}} .es-tag-item',
			)
		);

		$this->end_controls_tab();

		// Hover State Tab
		$this->start_controls_tab(
			'tab_tag_item_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'tag_color_hover',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item:hover, {{WRAPPER}} .es-tag-item:hover a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tag_bg_hover',
				'label'    => esc_html__( 'Hover Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-tag-item:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'tag_border_hover',
				'selector' => '{{WRAPPER}} .es-tag-item:hover',
			)
		);

		$this->end_controls_tab();

		// Active State Tab
		$this->start_controls_tab(
			'tab_tag_item_active',
			array(
				'label' => esc_html__( 'Active State', 'elonix' ),
			)
		);

		$this->add_control(
			'tag_color_active',
			array(
				'label'     => esc_html__( 'Active Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item.es-active, {{WRAPPER}} .es-tag-item.es-active a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tag_bg_active',
				'label'    => esc_html__( 'Active Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-tag-item.es-active',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'tag_border_active',
				'selector' => '{{WRAPPER}} .es-tag-item.es-active',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'tag_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'tag_item_padding',
			array(
				'label'      => esc_html__( 'Padding Inside', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 3. Category Icon styles
		$this->start_controls_section(
			'section_style_icon',
			array(
				'label'     => esc_html__( 'Category Icon', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Font Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_width',
			array(
				'label'      => esc_html__( 'Icon Box Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-icon' => 'width: {{SIZE}}{{UNIT}}; display: inline-flex; justify-content: center; align-items: center;',
				),
			)
		);

		$this->add_responsive_control(
			'icon_height',
			array(
				'label'      => esc_html__( 'Icon Box Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-icon' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_icon_colors' );

		// Normal Icon State
		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-icon, {{WRAPPER}} .es-tag-icon i' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Icon State
		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item:hover .es-tag-icon, {{WRAPPER}} .es-tag-item:hover .es-tag-icon i' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item:hover .es-tag-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border',
				'selector' => '{{WRAPPER}} .es-tag-icon',
			)
		);

		$this->add_responsive_control(
			'icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-icon, {{WRAPPER}} .es-tag-icon img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'      => esc_html__( 'Gap spacing from text', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-tag-item .es-tag-icon' => 'margin-bottom: 0;',
					'{{WRAPPER}} .es-tag-item' => 'align-items: center;',
				),
			)
		);

		$this->add_responsive_control(
			'icon_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-tag-icon' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_section();

		// 4. Count Badge styles
		$this->start_controls_section(
			'section_style_count',
			array(
				'label'     => esc_html__( 'Count Badge', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_count' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'count_typography',
				'selector' => '{{WRAPPER}} .es-tag-count',
			)
		);

		$this->start_controls_tabs( 'tabs_count_badge_colors' );

		// Normal state count colors
		$this->start_controls_tab(
			'tab_count_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'count_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-count' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'count_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-count' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'count_format' => 'badge',
				),
			)
		);

		$this->end_controls_tab();

		// Hover state count colors
		$this->start_controls_tab(
			'tab_count_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'count_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item:hover .es-tag-count' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'count_bg_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-item:hover .es-tag-count' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'count_format' => 'badge',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'count_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'count_format' => 'badge',
				),
			)
		);

		$this->add_responsive_control(
			'count_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'count_format' => 'badge',
				),
			)
		);

		$this->end_controls_section();

		// 5. Clear filter button styling
		$this->start_controls_section(
			'section_style_clear_btn',
			array(
				'label'     => esc_html__( 'Clear Filter Button', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'interaction_mode' => 'ajax_multi_select',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'clear_btn_typography',
				'selector' => '{{WRAPPER}} .es-tag-clear-filter-btn',
			)
		);

		$this->start_controls_tabs( 'tabs_clear_btn_colors' );

		// Normal state clear button colors
		$this->start_controls_tab(
			'tab_clear_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'clear_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'clear_btn_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'clear_btn_border',
				'selector' => '{{WRAPPER}} .es-tag-clear-filter-btn',
			)
		);

		$this->end_controls_tab();

		// Hover state clear button colors
		$this->start_controls_tab(
			'tab_clear_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'clear_btn_color_hover',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'clear_btn_bg_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'clear_btn_border_hover',
				'selector' => '{{WRAPPER}} .es-tag-clear-filter-btn:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'clear_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'clear_btn_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-tag-clear-filter-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! class_exists( 'Elonix_Toolkit_Taxonomy_Helper' ) ) {
			return;
		}

		// Map selection mode parameter to get_categories_data signature
		$query_settings                   = $settings;
		$query_settings['selection_mode'] = ! empty( $settings['selection_mode'] ) ? $settings['selection_mode'] : 'all';

		// Trigger categories query
		$terms = Elonix_Toolkit_Taxonomy_Helper::get_categories_data( $query_settings );

		$layout      = ! empty( $settings['layout'] ) ? $settings['layout'] : 'tag_cloud';
		$interaction = ! empty( $settings['interaction_mode'] ) ? $settings['interaction_mode'] : 'link';

		// Set default wrappers
		$wrap_classes = array( 'es-tag-cloud-wrap' );
		if ( 'link' !== $interaction ) {
			$wrap_classes[] = 'es-tag-ajax-filter-active';
		}

		$container_classes = array( 'es-tag-cloud-container', 'es-layout-' . $layout );

		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>" 
			data-interaction="<?php echo esc_attr( $interaction ); ?>" 
			data-target-widget="<?php echo esc_attr( $settings['target_widget_id'] ); ?>">
			
			<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>">
				<?php
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( 'category_cards' === $layout ) {
							self::render_category_card( $term, $settings );
						} elseif ( 'popular_topics' === $layout ) {
							self::render_popular_topic( $term, $settings );
						} else {
							self::render_single_tag( $term, $settings, $layout );
						}
					}
				} else {
					?>
					<span class="es-no-terms"><?php esc_html_e( 'No terms found.', 'elonix' ); ?></span>
					<?php
				}
				?>
			</div>

			<?php if ( 'ajax_multi_select' === $interaction && ! empty( $terms ) ) : ?>
				<div class="es-tag-clear-filter-wrap" style="display: none;">
					<button type="button" class="es-tag-clear-filter-btn">
						<?php echo esc_html( $settings['clear_filters_text'] ); ?>
					</button>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Method: Render single tag container.
	 *
	 * @param array  $term Standardized term details.
	 * @param array  $settings Widget settings.
	 * @param string $layout Active layout.
	 */
	public static function render_single_tag( $term, $settings, $layout ) {
		$show_count   = ! empty( $settings['show_count'] ) && 'yes' === $settings['show_count'];
		$show_icon    = ! empty( $settings['show_icon'] ) && 'yes' === $settings['show_icon'];
		$show_badge   = ! empty( $settings['show_badge'] ) && 'yes' === $settings['show_badge'];
		$show_tooltip = ! empty( $settings['show_tooltip'] ) && 'yes' === $settings['show_tooltip'];

		// Resolve icon
		$icon_html = '';
		if ( $show_icon && ! empty( $term['icon_type'] ) ) {
			if ( 'font' === $term['icon_type'] && ! empty( $term['icon_value']['value'] ) ) {
				$icon_html = sprintf( '<span class="es-tag-icon es-icon-font"><i class="%s"></i></span>', esc_attr( $term['icon_value']['value'] ) );
			} elseif ( 'svg' === $term['icon_type'] && ! empty( $term['icon_value'] ) ) {
				$icon_html = sprintf( '<span class="es-tag-icon es-icon-svg"><img src="%s" alt="" /></span>', esc_url( $term['icon_value'] ) );
			} elseif ( 'image' === $term['icon_type'] && ! empty( $term['icon_value'] ) ) {
				$icon_html = sprintf( '<span class="es-tag-icon es-icon-img"><img src="%s" alt="" /></span>', esc_url( $term['icon_value'] ) );
			}
		}

		// Resolve count
		$count_html = '';
		if ( $show_count ) {
			$count_format = ! empty( $settings['count_format'] ) ? $settings['count_format'] : 'normal';
			if ( 'parenthesis' === $count_format ) {
				$count_html = sprintf( '<span class="es-tag-count">(%d)</span>', intval( $term['count'] ) );
			} elseif ( 'badge' === $count_format ) {
				$count_html = sprintf( '<span class="es-tag-count es-count-badge">%d</span>', intval( $term['count'] ) );
			} else {
				$count_html = sprintf( '<span class="es-tag-count">%d</span>', intval( $term['count'] ) );
			}
		}

		// Resolve badge overlay
		$badge_html = '';
		if ( $show_badge && ! empty( $settings['badge_text'] ) ) {
			$badge_html = sprintf( '<span class="es-tag-badge">%s</span>', esc_html( $settings['badge_text'] ) );
		}

		// Tooltip attributes
		$tooltip_attrs = '';
		if ( $show_tooltip ) {
			$tooltip_type = ! empty( $settings['tooltip_type'] ) ? $settings['tooltip_type'] : 'count';
			/* translators: %d: Number of posts in tag. */
			$tooltip_text  = ( 'description' === $tooltip_type && ! empty( $term['description'] ) ) ? $term['description'] : sprintf( esc_html__( '%d posts', 'elonix' ), intval( $term['count'] ) );
			$tooltip_attrs = sprintf( ' data-es-tooltip="%s" title="%s"', esc_attr( $tooltip_text ), esc_attr( $tooltip_text ) );
		}

		// Render wrapper elements
		$tag_tag   = ( 'link' !== $settings['interaction_mode'] ) ? 'button' : 'a';
		$href_attr = ( 'a' === $tag_tag ) ? sprintf( ' href="%s"', esc_url( $term['url'] ) ) : ' type="button"';

		$classes = array( 'es-tag-item' );
		if ( $show_icon && ! empty( $icon_html ) ) {
			$classes[] = 'es-has-icon';
		}

		// Tag size calculation for classic Tag Cloud layout
		$tag_style = '';
		if ( 'tag_cloud' === $layout ) {
			$min_font  = 12;
			$max_font  = 24;
			$count_val = min( 100, max( 1, $term['count'] ) );
			$font_size = $min_font + ( $count_val * 0.15 );
			$font_size = min( $max_font, $font_size );
			$tag_style = sprintf( ' style="font-size: %dpx;"', $font_size );
		}

		?>
		<<?php echo esc_attr( $tag_tag ); ?> <?php echo $href_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_url() above. ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-term-id="<?php echo esc_attr( $term['id'] ); ?>"<?php echo $tooltip_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_attr() above. ?><?php echo $tag_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf( '%d' ) above, integer-cast. ?>>
			
			<?php if ( 'icon_tags' === $layout && ! empty( $icon_html ) ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_attr()/esc_url() above. ?>
			<?php endif; ?>

			<?php if ( 'icon_tags' !== $layout && ! empty( $icon_html ) && ! empty( $settings['icon_position'] ) && 'before' === $settings['icon_position'] ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_attr()/esc_url() above. ?>
			<?php endif; ?>

			<?php if ( 'icon_tags' !== $layout && ! empty( $icon_html ) && ! empty( $settings['icon_position'] ) && 'above' === $settings['icon_position'] ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_attr()/esc_url() above. ?>
			<?php endif; ?>

			<span class="es-tag-text"><?php echo esc_html( $term['name'] ); ?></span>

			<?php if ( ! empty( $count_html ) ) : ?>
				<?php echo $count_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf( '%d' ) above, integer-cast. ?>
			<?php endif; ?>

			<?php if ( ! empty( $badge_html ) ) : ?>
				<?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_html() above. ?>
			<?php endif; ?>

			<?php if ( 'icon_tags' !== $layout && ! empty( $icon_html ) && ! empty( $settings['icon_position'] ) && 'after' === $settings['icon_position'] ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_attr()/esc_url() above. ?>
			<?php endif; ?>

		</<?php echo esc_attr( $tag_tag ); ?>>
		<?php
	}

	/**
	 * Method: Render dynamic Category Card Layout.
	 */
	public static function render_category_card( $term, $settings ) {
		$show_count = ! empty( $settings['show_count'] ) && 'yes' === $settings['show_count'];
		$image_url  = ! empty( $term['image'] ) ? $term['image'] : '';
		$tag_tag    = ( 'link' !== $settings['interaction_mode'] ) ? 'button' : 'a';
		$href_attr  = ( 'a' === $tag_tag ) ? sprintf( ' href="%s"', esc_url( $term['url'] ) ) : ' type="button"';
		?>
		<<?php echo esc_attr( $tag_tag ); ?> <?php echo $href_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_url() above. ?> class="es-tag-card-item es-tag-item" data-term-id="<?php echo esc_attr( $term['id'] ); ?>">
			<?php if ( ! empty( $image_url ) ) : ?>
				<div class="es-tag-card-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
			<?php endif; ?>
			<div class="es-tag-card-content">
				<h4 class="es-tag-card-title"><?php echo esc_html( $term['name'] ); ?></h4>
				<?php if ( ! empty( $term['description'] ) ) : ?>
					<p class="es-tag-card-desc"><?php echo esc_html( wp_trim_words( $term['description'], 12, '...' ) ); ?></p>
				<?php endif; ?>
				<?php if ( $show_count ) : ?>
					<span class="es-tag-card-count">
						<?php
						/* translators: %d: Number of posts. */
						printf( esc_html( _n( '%d Post', '%d Posts', $term['count'], 'elonix' ) ), intval( $term['count'] ) );
						?>
					</span>
				<?php endif; ?>
			</div>
		</<?php echo esc_attr( $tag_tag ); ?>>
		<?php
	}

	/**
	 * Method: Render Popular Topics Category Tree structure.
	 */
	public static function render_popular_topic( $term, $settings ) {
		$tag_tag   = ( 'link' !== $settings['interaction_mode'] ) ? 'button' : 'a';
		$href_attr = ( 'a' === $tag_tag ) ? sprintf( ' href="%s"', esc_url( $term['url'] ) ) : ' type="button"';
		?>
		<div class="es-tag-topic-block">
			<<?php echo esc_attr( $tag_tag ); ?> <?php echo $href_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_url() above. ?> class="es-tag-topic-parent es-tag-item" data-term-id="<?php echo esc_attr( $term['id'] ); ?>">
				<span class="es-tag-text"><?php echo esc_html( $term['name'] ); ?></span>
				<?php if ( ! empty( $term['count'] ) ) : ?>
					<span class="es-tag-count"><?php echo intval( $term['count'] ); ?></span>
				<?php endif; ?>
			</<?php echo esc_attr( $tag_tag ); ?>>

			<?php if ( ! empty( $term['children'] ) ) : ?>
				<div class="es-tag-topic-children">
					<?php foreach ( $term['children'] as $child ) : ?>
						<?php
						$child_href = ( 'a' === $tag_tag ) ? sprintf( ' href="%s"', esc_url( $child['url'] ) ) : ' type="button"';
						?>
						<<?php echo esc_attr( $tag_tag ); ?> <?php echo $child_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built via sprintf() with esc_url() above. ?> class="es-tag-topic-child es-tag-item" data-term-id="<?php echo esc_attr( $child['id'] ); ?>">
							<span class="es-tag-text"><?php echo esc_html( $child['name'] ); ?></span>
							<span class="es-tag-count"><?php echo intval( $child['count'] ); ?></span>
						</<?php echo esc_attr( $tag_tag ); ?>>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
