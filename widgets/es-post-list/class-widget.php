<?php
/**
 * Elonix Premium Post List Widget Class
 *
 * A highly customizable premium posts, custom post types, and WooCommerce products
 * listing widget for Elementor with support for multiple sources, layouts,
 * AJAX pagination, caching, and clean styling controls.
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

class Elonix_Toolkit_Post_List_Widget extends Elonix_Widget_Base {

	/**
	 * Iteration counter for layouts like featured+list.
	 *
	 * @var int
	 */
	public static $post_index = 0;

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-post-list';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post List', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'posts', 'list', 'grid', 'woo', 'cpt', 'blog', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-post-list' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-post-list' );
	}

	/**
	 * Helper: Fetch all public post types.
	 *
	 * @return array Post types dictionary.
	 */
	protected function get_post_types_options() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$options    = array();
		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type->name, array( 'attachment' ), true ) ) {
				continue;
			}
			$options[ $post_type->name ] = $post_type->label;
		}
		return $options;
	}

	/**
	 * Helper: Retrieve combined categories options for query filter.
	 *
	 * @return array Categories option dictionary.
	 */
	protected function get_all_categories_options() {
		$options    = array();
		$taxonomies = array();
		if ( taxonomy_exists( 'category' ) ) {
			$taxonomies[] = 'category';
		}
		if ( taxonomy_exists( 'product_cat' ) ) {
			$taxonomies[] = 'product_cat';
		}

		if ( empty( $taxonomies ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomies,
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = sprintf( '%s (%s)', $term->name, $term->taxonomy );
			}
		}
		return $options;
	}

	/**
	 * Helper: Retrieve combined tags options for query filter.
	 *
	 * @return array Tags option dictionary.
	 */
	protected function get_all_tags_options() {
		$options    = array();
		$taxonomies = array();
		if ( taxonomy_exists( 'post_tag' ) ) {
			$taxonomies[] = 'post_tag';
		}
		if ( taxonomy_exists( 'product_tag' ) ) {
			$taxonomies[] = 'product_tag';
		}

		if ( empty( $taxonomies ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomies,
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = sprintf( '%s (%s)', $term->name, $term->taxonomy );
			}
		}
		return $options;
	}

	/**
	 * Helper: Retrieve all registered users.
	 *
	 * @return array Authors option dictionary.
	 */
	protected function get_authors_options() {
		$users   = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
		$options = array();
		foreach ( $users as $user ) {
			$options[ $user->ID ] = $user->display_name;
		}
		return $options;
	}

	/**
	 * Register Post List widget controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		// 1. Query Options Section
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__( 'Query Options', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'query_mode',
			array(
				'label'   => esc_html__( 'Query Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => array(
					'custom'  => esc_html__( 'Custom Query', 'elonix' ),
					'current' => esc_html__( 'Current Query (Contextual)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'query_source',
			array(
				'label'     => esc_html__( 'Query Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'posts',
				'options'   => array(
					'posts'   => esc_html__( 'Posts', 'elonix' ),
					'pages'   => esc_html__( 'Pages', 'elonix' ),
					'cpt'     => esc_html__( 'Custom Post Type (CPT)', 'elonix' ),
					'woo'     => esc_html__( 'WooCommerce Products', 'elonix' ),
					'current' => esc_html__( 'Current Query', 'elonix' ),
					'related' => esc_html__( 'Related Posts', 'elonix' ),
					'manual'  => esc_html__( 'Manual Selection', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_post_type',
			array(
				'label'     => esc_html__( 'Select Post Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_post_types_options(),
				'condition' => array(
					'query_mode'   => 'custom',
					'query_source' => 'cpt',
				),
			)
		);

		$this->add_control(
			'manual_ids',
			array(
				'label'       => esc_html__( 'Manual IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '10, 14, 25',
				'description' => esc_html__( 'Comma-separated post ID list.', 'elonix' ),
				'condition'   => array(
					'query_mode'   => 'custom',
					'query_source' => 'manual',
				),
			)
		);

		$this->add_control(
			'categories_filter',
			array(
				'label'       => esc_html__( 'Categories Filter', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_all_categories_options(),
				'label_block' => true,
				'condition'   => array(
					'query_mode'   => 'custom',
					'query_source' => array( 'posts', 'cpt', 'woo' ),
				),
			)
		);

		$this->add_control(
			'tags_filter',
			array(
				'label'       => esc_html__( 'Tags Filter', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_all_tags_options(),
				'label_block' => true,
				'condition'   => array(
					'query_mode'   => 'custom',
					'query_source' => array( 'posts', 'cpt', 'woo' ),
				),
			)
		);

		$this->add_control(
			'author_ids',
			array(
				'label'       => esc_html__( 'Authors Filter', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_authors_options(),
				'label_block' => true,
				'condition'   => array(
					'query_mode'   => 'custom',
					'query_source' => array( 'posts', 'pages', 'cpt', 'woo' ),
				),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Post Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 1,
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'     => esc_html__( 'Offset Shift', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'ignore_sticky_posts',
			array(
				'label'     => esc_html__( 'Ignore Sticky Posts', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'yes',
				'options'   => array(
					'yes' => esc_html__( 'Yes', 'elonix' ),
					'no'  => esc_html__( 'No', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'     => esc_html__( 'Order By', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'     => esc_html__( 'Published Date', 'elonix' ),
					'title'    => esc_html__( 'Title Alphabetical', 'elonix' ),
					'comments' => esc_html__( 'Comment Count', 'elonix' ),
					'views'    => esc_html__( 'Views Count', 'elonix' ),
					'rand'     => esc_html__( 'Randomized Order', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Sorting Direction', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => esc_html__( 'Descending', 'elonix' ),
					'ASC'  => esc_html__( 'Ascending', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'date_filter',
			array(
				'label'     => esc_html__( 'Date Threshold', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => array(
					'all'    => esc_html__( 'All Time', 'elonix' ),
					'day'    => esc_html__( 'Past 24 Hours', 'elonix' ),
					'week'   => esc_html__( 'Past Week', 'elonix' ),
					'month'  => esc_html__( 'Past Month', 'elonix' ),
					'custom' => esc_html__( 'Custom Range', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_date_after',
			array(
				'label'       => esc_html__( 'Published After', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '2026-01-01',
				'description' => esc_html__( 'Format: YYYY-MM-DD', 'elonix' ),
				'condition'   => array(
					'query_mode'  => 'custom',
					'date_filter' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_date_before',
			array(
				'label'       => esc_html__( 'Published Before', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '2026-06-01',
				'description' => esc_html__( 'Format: YYYY-MM-DD', 'elonix' ),
				'condition'   => array(
					'query_mode'  => 'custom',
					'date_filter' => 'custom',
				),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => esc_html__( 'Exclude IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '3, 7, 21',
				'description' => esc_html__( 'Exclude specific post IDs.', 'elonix' ),
				'condition'   => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->end_controls_section();

		// 2. Layout Options Section
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
				'label'   => esc_html__( 'Layout Template', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic_list',
				'options' => array(
					'classic_list'  => esc_html__( 'Classic List', 'elonix' ),
					'news_list'     => esc_html__( 'News List', 'elonix' ),
					'featured_list' => esc_html__( 'Featured + List', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// 3. Content Blocks Toggles Section
		$this->start_controls_section(
			'section_content_blocks',
			array(
				'label' => esc_html__( 'Content Blocks', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_thumbnail',
			array(
				'label'        => esc_html__( 'Display Thumbnail', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'thumbnail_size',
			array(
				'label'     => esc_html__( 'Thumbnail Size', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'medium',
				'options'   => array(
					'thumbnail' => esc_html__( 'Thumbnail (150x150)', 'elonix' ),
					'medium'    => esc_html__( 'Medium (300x300)', 'elonix' ),
					'large'     => esc_html__( 'Large (1024x1024)', 'elonix' ),
					'full'      => esc_html__( 'Full Size', 'elonix' ),
				),
				'condition' => array(
					'show_thumbnail' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Display Title', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => esc_html__( 'Title HTML Tag', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
				),
				'condition' => array(
					'show_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_word_limit',
			array(
				'label'     => esc_html__( 'Title Word Limit', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'default'   => 0,
				'description' => esc_html__( 'Set 0 to show full title.', 'elonix' ),
				'condition' => array(
					'show_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_suffix',
			array(
				'label'     => esc_html__( 'Title Suffix', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '...',
				'condition' => array(
					'show_title' => 'yes',
					'title_word_limit!' => 0,
					'title_word_limit!' => '',
				),
			)
		);

		$this->add_control(
			'show_meta',
			array(
				'label'        => esc_html__( 'Display Meta Details', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'meta_elements',
			array(
				'label'       => esc_html__( 'Select Meta Components', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => array( 'author', 'date' ),
				'options'     => array(
					'author'       => esc_html__( 'Author', 'elonix' ),
					'date'         => esc_html__( 'Published Date', 'elonix' ),
					'comments'     => esc_html__( 'Comment Count', 'elonix' ),
					'categories'   => esc_html__( 'Categories List', 'elonix' ),
					'reading_time' => esc_html__( 'Reading Time Estimate', 'elonix' ),
				),
				'label_block' => true,
				'condition'   => array(
					'show_meta' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Display Excerpt', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => esc_html__( 'Excerpt Words Length', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 15,
				'min'       => 1,
				'condition' => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'excerpt_suffix',
			array(
				'label'     => esc_html__( 'Excerpt Suffix', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '...',
				'condition' => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => esc_html__( 'Display Badge overlay', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'badge_type',
			array(
				'label'     => esc_html__( 'Badge Content Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'category',
				'options'   => array(
					'category'  => esc_html__( 'First Category Name', 'elonix' ),
					'post_type' => esc_html__( 'Post Type Label', 'elonix' ),
					'custom'    => esc_html__( 'Custom Badge Label', 'elonix' ),
				),
				'condition' => array(
					'show_badge' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'     => esc_html__( 'Custom Badge Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'NEW',
				'condition' => array(
					'show_badge' => 'yes',
					'badge_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'show_read_more',
			array(
				'label'        => esc_html__( 'Display Read More Button', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'     => esc_html__( 'Read More Label', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Read More',
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 4. Pagination / AJAX Options Section
		$this->start_controls_section(
			'section_pagination',
			array(
				'label' => esc_html__( 'Pagination / AJAX', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'   => esc_html__( 'Pagination Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'            => esc_html__( 'None (No Pagination)', 'elonix' ),
					'load_more'       => esc_html__( 'AJAX Load More Button', 'elonix' ),
					'infinite_scroll' => esc_html__( 'AJAX Infinite Scroll', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'pagination_text',
			array(
				'label'     => esc_html__( 'Load More Button Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Load More',
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// 1. Container Style Section
		$this->start_controls_section(
			'section_style_container',
			array(
				'label' => esc_html__( 'Container Styling', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Container Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-list-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_row_gap',
			array(
				'label'      => esc_html__( 'Row Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => 20,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-list-container' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 2. Card Style Section
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Card Styling', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_card_style' );

		// Normal Card Tab
		$this->start_controls_tab(
			'tab_card_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background',
				'label'    => esc_html__( 'Card Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-post-card',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-post-card',
			)
		);

		$this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .es-post-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Card Tab
		$this->start_controls_tab(
			'tab_card_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background_hover',
				'label'    => esc_html__( 'Card Background Hover', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-post-card:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border_hover',
				'label'    => esc_html__( 'Border Hover', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-post-card:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow_hover',
				'selector' => '{{WRAPPER}} .es-post-card:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 3. Thumbnail Style Section
		$this->start_controls_section(
			'section_style_thumbnail',
			array(
				'label'     => esc_html__( 'Thumbnail Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_thumbnail' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_width',
			array(
				'label'      => esc_html__( 'Thumbnail Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 600,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-thumbnail' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_height',
			array(
				'label'      => esc_html__( 'Thumbnail Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 600,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}  .es-post-thumbnail' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-thumbnail, {{WRAPPER}} .es-post-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; object-fit: cover;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'thumbnail_border',
				'selector' => '{{WRAPPER}} .es-post-thumbnail',
			)
		);

		$this->add_responsive_control(
			'thumbnail_spacing',
			array(
				'label'      => esc_html__( 'Spacing gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-thumbnail' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-layout-news-list .es-post-thumbnail, {{WRAPPER}} .es-layout-featured_list .es-standard-card .es-post-thumbnail' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: 0;',
				),
			)
		);

		$this->end_controls_section();

		// 4. Title Style Section
		$this->start_controls_section(
			'section_style_title',
			array(
				'label'     => esc_html__( 'Title Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .es-post-title, {{WRAPPER}} .es-post-title a',
			)
		);

		$this->start_controls_tabs( 'tabs_title_colors' );

		$this->start_controls_tab(
			'tab_title_color_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-title, {{WRAPPER}} .es-post-title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_color_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-title a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => esc_html__( 'Spacing Bottom', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 5. Meta Details Style Section
		$this->start_controls_section(
			'section_style_meta',
			array(
				'label'     => esc_html__( 'Meta Details Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_meta' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .es-post-meta, {{WRAPPER}} .es-post-meta .es-meta-item, {{WRAPPER}} .es-post-meta a',
			)
		);

		$this->add_control(
			'meta_color',
			array(
				'label'     => esc_html__( 'Meta Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-meta, {{WRAPPER}} .es-post-meta .es-meta-item, {{WRAPPER}} .es-post-meta a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_hover_color',
			array(
				'label'     => esc_html__( 'Meta Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-meta a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'meta_gap',
			array(
				'label'      => esc_html__( 'Gap between details', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-meta' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'meta_spacing',
			array(
				'label'      => esc_html__( 'Spacing Bottom', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 6. Excerpt Style Section
		$this->start_controls_section(
			'section_style_excerpt',
			array(
				'label'     => esc_html__( 'Excerpt Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .es-post-excerpt',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'excerpt_spacing',
			array(
				'label'      => esc_html__( 'Spacing Bottom', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 7. Read More Button Style Section
		$this->start_controls_section(
			'section_style_read_more',
			array(
				'label'     => esc_html__( 'Read More Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'read_more_typography',
				'selector' => '{{WRAPPER}} .es-post-readmore .es-readmore-btn',
			)
		);

		$this->start_controls_tabs( 'tabs_read_more_colors' );

		$this->start_controls_tab(
			'tab_read_more_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'read_more_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'read_more_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'read_more_border',
				'selector' => '{{WRAPPER}} .es-post-readmore .es-readmore-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_read_more_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'read_more_color_hover',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'read_more_bg_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'read_more_border_hover',
				'selector' => '{{WRAPPER}} .es-post-readmore .es-readmore-btn:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'read_more_padding',
			array(
				'label'      => esc_html__( 'Button Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: inline-block;',
				),
			)
		);

		$this->add_responsive_control(
			'read_more_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-readmore .es-readmore-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 8. Badge Style Section
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label'     => esc_html__( 'Badge Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_badge' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .es-post-badge',
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; line-height: 1;',
				),
			)
		);

		$this->add_responsive_control(
			'badge_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 9. Pagination Style Section
		$this->start_controls_section(
			'section_style_pagination',
			array(
				'label'     => esc_html__( 'Pagination Styling', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'pagination_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_spacing_top',
			array(
				'label'      => esc_html__( 'Spacing Top', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-list-pagination' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'pagination_typography',
				'selector'  => '{{WRAPPER}} .es-load-more-btn',
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_pagination_colors' );

		$this->start_controls_tab(
			'tab_pagination_normal',
			array(
				'label'     => esc_html__( 'Normal', 'elonix' ),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-load-more-btn' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_btn_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-load-more-btn' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pagination_btn_border',
				'selector'  => '{{WRAPPER}} .es-load-more-btn',
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pagination_hover',
			array(
				'label'     => esc_html__( 'Hover', 'elonix' ),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_btn_color_hover',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-load-more-btn:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'pagination_btn_bg_hover',
			array(
				'label'     => esc_html__( 'Hover Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-load-more-btn:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pagination_btn_border_hover',
				'selector'  => '{{WRAPPER}} .es-load-more-btn:hover',
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pagination_btn_padding',
			array(
				'label'      => esc_html__( 'Button Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_btn_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-load-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Post List widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Filter widget settings for AJAX and query context
		$ajax_settings = array();
		$keys_to_pass  = array(
			'query_mode',
			'query_source',
			'custom_post_type',
			'manual_ids',
			'categories_filter',
			'tags_filter',
			'author_ids',
			'limit',
			'offset',
			'ignore_sticky_posts',
			'orderby',
			'order',
			'date_filter',
			'custom_date_after',
			'custom_date_before',
			'exclude_ids',
			'layout',
			'show_thumbnail',
			'thumbnail_size',
			'show_title',
			'title_tag',
			'title_word_limit',
			'title_suffix',
			'show_meta',
			'meta_elements',
			'show_excerpt',
			'excerpt_length',
			'excerpt_suffix',
			'show_badge',
			'badge_type',
			'badge_text',
			'show_read_more',
			'read_more_text',
			'pagination_type',
			'pagination_text',
		);
		foreach ( $keys_to_pass as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$ajax_settings[ $key ] = $settings[ $key ];
			}
		}

		// Set default layout
		$layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'classic_list';

		// Set initial page number
		$ajax_settings['paged'] = 1;

		if ( ! class_exists( 'Elonix_Toolkit_Post_List_Query_Helper' ) ) {
			require_once __DIR__ . '/helper-query.php';
		}

		$posts_data = Elonix_Toolkit_Post_List_Query_Helper::get_posts_data( $ajax_settings );

		$pagination = ! empty( $settings['pagination_type'] ) ? $settings['pagination_type'] : 'none';

		// Reset post iteration index
		self::$post_index = 0;

		$nonce = wp_create_nonce( 'es-post-list-nonce' );

		// Safe check for max page count
		global $wp_query;
		if ( ! empty( $settings['query_mode'] ) && 'current' === $settings['query_mode'] ) {
			$max_num_pages = $wp_query->max_num_pages;
		} else {
			// Query via helper for non-cached query vars to read totals
			$query_args    = \Elonix_Query_Context::build_query_args( $ajax_settings );
			$count_query   = new WP_Query( $query_args );
			$max_num_pages = $count_query->max_num_pages;
			wp_reset_postdata();
		}

		?>
		<div class="es-post-list-wrap"
			data-settings="<?php echo esc_attr( wp_json_encode( $ajax_settings ) ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-max-pages="<?php echo esc_attr( $max_num_pages ); ?>">

			<div class="es-post-list-container es-layout-<?php echo esc_attr( $layout ); ?>">
				<?php
				if ( ! empty( $posts_data ) ) {
					foreach ( $posts_data as $item ) {
						self::render_single_post( $item, $settings, $layout );
						++self::$post_index;
					}
				} else {
					?>
					<div class="es-no-posts"><?php esc_html_e( 'No posts found.', 'elonix' ); ?></div>
					<?php
				}
				?>
			</div>

			<?php if ( 'none' !== $pagination && ! empty( $posts_data ) && $max_num_pages > 1 ) : ?>
				<div class="es-post-list-pagination">
					<?php if ( 'load_more' === $pagination ) : ?>
						<button class="es-load-more-btn es-btn">
							<span class="es-btn-text"><?php echo esc_html( $settings['pagination_text'] ); ?></span>
							<span class="es-btn-loading-icon" style="display: none;">
								<svg class="es-spinner" viewBox="0 0 50 50">
									<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle>
								</svg>
							</span>
						</button>
					<?php elseif ( 'infinite_scroll' === $pagination ) : ?>
						<div class="es-infinite-scroll-trigger">
							<span class="es-scroll-loading-icon">
								<svg class="es-spinner" viewBox="0 0 50 50">
									<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle>
								</svg>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Method: Render single post container. Used globally by initial page render
	 * and by the AJAX fetch response to guarantee matching outputs.
	 *
	 * @param array  $item Formatted post structure from query helper.
	 * @param array  $settings Control settings.
	 * @param string $layout Active layout.
	 */
	public static function render_single_post( $item, $settings, $layout ) {
		$show_thumbnail = ! empty( $settings['show_thumbnail'] ) && 'yes' === $settings['show_thumbnail'];
		$show_title     = ! empty( $settings['show_title'] ) && 'yes' === $settings['show_title'];
		$show_meta      = ! empty( $settings['show_meta'] ) && 'yes' === $settings['show_meta'];
		$show_excerpt   = ! empty( $settings['show_excerpt'] ) && 'yes' === $settings['show_excerpt'];
		$show_read_more = ! empty( $settings['show_read_more'] ) && 'yes' === $settings['show_read_more'];
		$show_badge     = ! empty( $settings['show_badge'] ) && 'yes' === $settings['show_badge'];

		// Resolve badge text
		$badge_html = '';
		if ( $show_badge ) {
			$badge_text = '';
			$badge_type = ! empty( $settings['badge_type'] ) ? $settings['badge_type'] : 'category';

			if ( 'category' === $badge_type && ! empty( $item['categories'] ) ) {
				$badge_text = $item['categories'][0]['name'];
			} elseif ( 'post_type' === $badge_type ) {
				$post_obj   = get_post( $item['id'] );
				$badge_text = $post_obj ? get_post_type_object( $post_obj->post_type )->labels->singular_name : 'Post';
			} elseif ( 'custom' === $badge_type ) {
				$badge_text = ! empty( $settings['badge_text'] ) ? $settings['badge_text'] : '';
			}

			if ( ! empty( $badge_text ) ) {
				$badge_html = sprintf( '<span class="es-post-badge">%s</span>', esc_html( $badge_text ) );
			}
		}

		// Calculate class names
		$card_classes   = array( 'es-post-card' );
		$is_featured    = ( 'featured_list' === $layout && isset( $settings['paged'] ) && 1 === intval( $settings['paged'] ) && 0 === self::$post_index );
		$card_classes[] = $is_featured ? 'es-featured-card' : 'es-standard-card';

		// Render Card Markup
		?>
		<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
			<?php
			if ( class_exists( 'Elonix_Toolkit_Edit_Overlay' ) ) {
				Elonix_Toolkit_Edit_Overlay::render( $item['id'] );
			}
			?>

			<?php if ( $show_thumbnail && ! empty( $item['thumbnail'] ) ) : ?>
				<div class="es-post-thumbnail">
					<a href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo $item['thumbnail']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php if ( ! empty( $badge_html ) ) : ?>
						<?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</div>
			<?php elseif ( ! empty( $badge_html ) ) : ?>
				<div class="es-post-badge-wrapper">
					<?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<div class="es-post-content">

				<?php
				if ( $show_title ) {
					$title_tag = ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3';
					printf(
						'<%1$s class="es-post-title"><a href="%2$s">%3$s</a></%1$s>',
						esc_attr( $title_tag ),
						esc_url( $item['url'] ),
						esc_html( $item['title'] )
					);
				}
				?>

				<?php if ( $show_meta && ! empty( $settings['meta_elements'] ) ) : ?>
					<div class="es-post-meta">
						<?php if ( in_array( 'author', $settings['meta_elements'], true ) ) : ?>
							<span class="es-meta-item es-meta-author">
								<a href="<?php echo esc_url( $item['author_url'] ); ?>">
									<?php echo esc_html( $item['author_name'] ); ?>
								</a>
							</span>
						<?php endif; ?>

						<?php if ( in_array( 'date', $settings['meta_elements'], true ) ) : ?>
							<span class="es-meta-item es-meta-date">
								<?php echo esc_html( $item['date'] ); ?>
							</span>
						<?php endif; ?>

						<?php if ( in_array( 'comments', $settings['meta_elements'], true ) ) : ?>
							<span class="es-meta-item es-meta-comments">
								<?php
								/* translators: %d: Number of comments */
								printf( esc_html( _n( '%d Comment', '%d Comments', $item['comments'], 'elonix' ) ), intval( $item['comments'] ) );
								?>
							</span>
						<?php endif; ?>

						<?php if ( in_array( 'categories', $settings['meta_elements'], true ) && ! empty( $item['categories'] ) ) : ?>
							<span class="es-meta-item es-meta-categories">
								<?php
								$cat_links = array();
								foreach ( $item['categories'] as $cat ) {
									$cat_links[] = sprintf( '<a href="%s">%s</a>', esc_url( $cat['url'] ), esc_html( $cat['name'] ) );
								}
								echo implode( ', ', $cat_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						<?php endif; ?>

						<?php if ( in_array( 'reading_time', $settings['meta_elements'], true ) ) : ?>
							<span class="es-meta-item es-meta-reading-time">
								<?php
								/* translators: %d: Estimated reading time in minutes. */
								printf( esc_html__( '%d min read', 'elonix' ), intval( $item['reading_time'] ) );
								?>
							</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_excerpt && ! empty( $item['excerpt'] ) ) : ?>
					<div class="es-post-excerpt">
						<?php echo esc_html( $item['excerpt'] ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_read_more ) : ?>
					<div class="es-post-readmore">
						<a href="<?php echo esc_url( $item['url'] ); ?>" class="es-readmore-btn">
							<?php echo esc_html( $settings['read_more_text'] ); ?>
						</a>
					</div>
				<?php endif; ?>

			</div>

		</article>
		<?php
	}
}
