<?php
/**
 * Elonix Premium Post Block Widget Class
 *
 * A ThemeForest-grade magazine, news, and blog grid block widget for Elementor
 * with support for Drag & Drop Element Ordering, AJAX category/tag filters,
 * skeleton loader, and native Archive Builder compatibility.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Load the standalone renderer — NO Elementor inheritance, safe at any boot phase.
require_once __DIR__ . '/class-renderer.php';

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;

class Elonix_Toolkit_Post_Block_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 */
	public function get_name() {
		return 'tv-post-block';
	}

	/**
	 * Retrieve widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Block Grid', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Retrieve widget keywords.
	 */
	public function get_tv_widget_keywords() {
		return array( 'posts', 'block', 'magazine', 'news', 'grid', 'ajax', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-post-block' );
	}

	/**
	 * Retrieve widget script dependency list.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-tv-post-block' );
	}

	/**
	 * Helper: Fetch all public post types.
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
	 */
	protected function get_all_categories_options() {
		$options    = array();
		$taxonomies = get_taxonomies(
			array(
				'public'       => true,
				'hierarchical' => true,
			),
			'names'
		);
		if ( empty( $taxonomies ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array_values( $taxonomies ),
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
	 */
	protected function get_all_tags_options() {
		$options    = array();
		$taxonomies = get_taxonomies(
			array(
				'public'       => true,
				'hierarchical' => false,
			),
			'names'
		);
		if ( empty( $taxonomies ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array_values( $taxonomies ),
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
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		// 1. Premium Visual Layout Selector
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Settings', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'       => esc_html__( 'Select Layout Style', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'default'     => 'style_1',
				'options'     => array(
					'style_1' => esc_html__( 'Style 1 — Featured Left, Content Right', 'elonix' ),
					'style_2' => esc_html__( 'Style 2 — Featured Top, Small Below', 'elonix' ),
					'style_3' => esc_html__( 'Style 3 — Magazine Grid', 'elonix' ),
					'style_4' => esc_html__( 'Style 4 — News Block', 'elonix' ),
					'style_5' => esc_html__( 'Style 5 — Compact Editorial Grid', 'elonix' ),
				),
				'label_block' => true,
				'description' => esc_html__( 'Select a layout style for post block rendering.', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Grid Columns', 'elonix' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors'      => array(
					'{{WRAPPER}} .tv-post-block-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
				'condition'      => array(
					'layout' => array( 'style_2', 'style_5' ),
				),
				'description'    => esc_html__( 'Set columns distribution across breakpoints.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 2. Query Source Section
		$this->start_controls_section(
			'section_query_source',
			array(
				'label' => esc_html__( 'Query Source', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'query_mode',
			array(
				'label'       => esc_html__( 'Query Mode', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'custom',
				'options'     => array(
					'custom'  => esc_html__( 'Custom Query', 'elonix' ),
					'current' => esc_html__( 'Current Query (Archive Mode)', 'elonix' ),
				),
				'description' => esc_html__( 'Choose "Current Query" when placing this block on Category/Tag templates for Archive Builder compatibility.', 'elonix' ),
			)
		);

		$this->add_control(
			'query_source',
			array(
				'label'     => esc_html__( 'Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'posts',
				'options'   => array(
					'posts'   => esc_html__( 'Posts', 'elonix' ),
					'pages'   => esc_html__( 'Pages', 'elonix' ),
					'cpt'     => esc_html__( 'Custom Post Type', 'elonix' ),
					'woo'     => esc_html__( 'WooCommerce Products', 'elonix' ),
					'manual'  => esc_html__( 'Manual Selection', 'elonix' ),
					'related' => esc_html__( 'Related Posts', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_post_type',
			array(
				'label'       => esc_html__( 'Select Post Type', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_post_types_options(),
				'label_block' => true,
				'condition'   => array(
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
				'description' => esc_html__( 'Specify explicit comma-separated post IDs.', 'elonix' ),
				'condition'   => array(
					'query_mode'   => 'custom',
					'query_source' => 'manual',
				),
			)
		);

		$this->add_control(
			'thumbnail_size',
			array(
				'label'   => esc_html__( 'Thumbnail Image Size', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => array(
					'thumbnail'    => esc_html__( 'Thumbnail', 'elonix' ),
					'medium'       => esc_html__( 'Medium', 'elonix' ),
					'medium_large' => esc_html__( 'Medium Large', 'elonix' ),
					'large'        => esc_html__( 'Large', 'elonix' ),
					'full'         => esc_html__( 'Full', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// 3. Query Filters Section
		$this->start_controls_section(
			'section_query_filters',
			array(
				'label'     => esc_html__( 'Filters', 'elonix' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'categories_filter',
			array(
				'label'       => esc_html__( 'Categories', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_all_categories_options(),
				'label_block' => true,
				'description' => esc_html__( 'Filter query results by selecting one or more categories.', 'elonix' ),
				'condition'   => array(
					'query_source' => array( 'posts', 'cpt', 'woo' ),
				),
			)
		);

		$this->add_control(
			'tags_filter',
			array(
				'label'       => esc_html__( 'Tags', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_all_tags_options(),
				'label_block' => true,
				'description' => esc_html__( 'Filter query results by selecting one or more tags.', 'elonix' ),
				'condition'   => array(
					'query_source' => array( 'posts', 'cpt', 'woo' ),
				),
			)
		);

		$this->add_control(
			'author_ids',
			array(
				'label'       => esc_html__( 'Authors', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_authors_options(),
				'label_block' => true,
				'description' => esc_html__( 'Filter query results by selecting one or more authors.', 'elonix' ),
				'condition'   => array(
					'query_source' => array( 'posts', 'pages', 'cpt', 'woo' ),
				),
			)
		);

		$this->end_controls_section();

		// 4. Query Advanced Settings Section
		$this->start_controls_section(
			'section_query_advanced',
			array(
				'label' => esc_html__( 'Advanced', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'       => esc_html__( 'Post Limit', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 6,
				'min'         => 1,
				'description' => esc_html__( 'Set maximum posts count to query and render.', 'elonix' ),
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'       => esc_html__( 'Offset', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Skip first N items of queries outputs.', 'elonix' ),
				'condition'   => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'ignore_sticky_posts',
			array(
				'label'       => esc_html__( 'Sticky Posts', 'elonix' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Ignore', 'elonix' ),
				'label_off'   => esc_html__( 'Show', 'elonix' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Choose whether to ignore sticky posts or prioritize them at the top.', 'elonix' ),
				'condition'   => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => esc_html__( 'Exclude Post IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '12, 17',
				'description' => esc_html__( 'IDs to exclude from results queries.', 'elonix' ),
				'condition'   => array(
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
					'date'          => esc_html__( 'Date', 'elonix' ),
					'ID'            => esc_html__( 'Post ID', 'elonix' ),
					'title'         => esc_html__( 'Title', 'elonix' ),
					'modified'      => esc_html__( 'Last Modified', 'elonix' ),
					'comment_count' => esc_html__( 'Popularity (Comments)', 'elonix' ),
					'rand'          => esc_html__( 'Random', 'elonix' ),
				),
				'condition' => array(
					'query_mode' => 'custom',
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Sort Order', 'elonix' ),
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
				'label'     => esc_html__( 'Date Filter', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'   => esc_html__( 'All Time', 'elonix' ),
					'today'  => esc_html__( 'Today', 'elonix' ),
					'week'   => esc_html__( 'This Week', 'elonix' ),
					'month'  => esc_html__( 'This Month', 'elonix' ),
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
				'label'          => esc_html__( 'After Date', 'elonix' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => array( 'enableTime' => false ),
				'condition'      => array(
					'query_mode'  => 'custom',
					'date_filter' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_date_before',
			array(
				'label'          => esc_html__( 'Before Date', 'elonix' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => array( 'enableTime' => false ),
				'condition'      => array(
					'query_mode'  => 'custom',
					'date_filter' => 'custom',
				),
			)
		);

		$this->end_controls_section();

		// 5. Visual Sortable Element Order Builder
		$this->start_controls_section(
			'section_elements_builder',
			array(
				'label' => esc_html__( 'Element Order Builder', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$element_repeater = new \Elementor\Repeater();
		$element_repeater->add_control(
			'element_type',
			array(
				'label'   => esc_html__( 'Element Type', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'title',
				'options' => array(
					'badge'        => esc_html__( 'Category', 'elonix' ),
					'title'        => esc_html__( 'Title', 'elonix' ),
					'meta'         => esc_html__( 'Meta', 'elonix' ),
					'excerpt'      => esc_html__( 'Excerpt', 'elonix' ),
					'read_more'    => esc_html__( 'Read More', 'elonix' ),
					'author'       => esc_html__( 'Author', 'elonix' ),
					'date'         => esc_html__( 'Date', 'elonix' ),
					'comments'     => esc_html__( 'Comments', 'elonix' ),
					'reading_time' => esc_html__( 'Reading Time', 'elonix' ),
					'share'        => esc_html__( 'Share', 'elonix' ),
				),
			)
		);

		$element_repeater->add_control(
			'show_element',
			array(
				'label'        => esc_html__( 'Enable Block Output', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'elonix' ),
				'label_off'    => esc_html__( 'Disable', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// Per Item: Category (Badge) Settings
		$element_repeater->add_control(
			'badge_type',
			array(
				'label'     => esc_html__( 'Badge Content Type', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'category',
				'options'   => array(
					'category'  => esc_html__( 'Primary Category', 'elonix' ),
					'post_type' => esc_html__( 'Post Type Name', 'elonix' ),
					'custom'    => esc_html__( 'Custom Static Text', 'elonix' ),
				),
				'condition' => array(
					'element_type' => 'badge',
				),
			)
		);

		$element_repeater->add_control(
			'badge_text',
			array(
				'label'     => esc_html__( 'Custom Static Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Featured',
				'condition' => array(
					'element_type' => 'badge',
					'badge_type'   => 'custom',
				),
			)
		);

		// Per Item: Title Settings
		$element_repeater->add_control(
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
					'element_type' => 'title',
				),
			)
		);

		$element_repeater->add_control(
			'title_word_limit',
			array(
				'label'       => esc_html__( 'Word Limit', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Trim titles by word count. Use 0 to disable.', 'elonix' ),
				'condition'   => array(
					'element_type' => 'title',
				),
			)
		);

		$element_repeater->add_control(
			'title_char_limit',
			array(
				'label'       => esc_html__( 'Character Limit', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Trim titles by character count. Use 0 to disable.', 'elonix' ),
				'condition'   => array(
					'element_type' => 'title',
				),
			)
		);

		$element_repeater->add_control(
			'title_suffix',
			array(
				'label'     => esc_html__( 'Custom Trim Suffix', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '...',
				'condition' => array(
					'element_type' => 'title',
				),
			)
		);

		// Per Item: Excerpt Settings
		$element_repeater->add_control(
			'excerpt_word_limit',
			array(
				'label'     => esc_html__( 'Excerpt Word Limit', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 15,
				'min'       => 0,
				'condition' => array(
					'element_type' => 'excerpt',
				),
			)
		);

		$element_repeater->add_control(
			'excerpt_char_limit',
			array(
				'label'     => esc_html__( 'Excerpt Character Limit', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'condition' => array(
					'element_type' => 'excerpt',
				),
			)
		);

		$element_repeater->add_control(
			'excerpt_strip_html',
			array(
				'label'     => esc_html__( 'Strip HTML Tags', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'element_type' => 'excerpt',
				),
			)
		);

		$element_repeater->add_control(
			'excerpt_strip_shortcodes',
			array(
				'label'     => esc_html__( 'Strip Shortcodes', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'element_type' => 'excerpt',
				),
			)
		);

		$element_repeater->add_control(
			'excerpt_suffix',
			array(
				'label'     => esc_html__( 'Excerpt Trim Suffix', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '...',
				'condition' => array(
					'element_type' => 'excerpt',
				),
			)
		);

		// Per Item: Read More Settings
		$element_repeater->add_control(
			'read_more_text',
			array(
				'label'     => esc_html__( 'Button Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More', 'elonix' ),
				'condition' => array(
					'element_type' => 'read_more',
				),
			)
		);

		// Icon Position for individual meta elements
		$element_repeater->add_control(
			'icon_position',
			array(
				'label'     => esc_html__( 'Icon Position', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Before Text', 'elonix' ),
					'after'  => esc_html__( 'After Text', 'elonix' ),
				),
				'condition' => array(
					'element_type' => array( 'author', 'date', 'comments', 'reading_time', 'share' ),
				),
			)
		);

		// Individual Color for individual elements
		$element_repeater->add_control(
			'element_color',
			array(
				'label'     => esc_html__( 'Text/Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}}' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
				'condition' => array(
					'element_type' => array( 'author', 'date', 'comments', 'reading_time', 'share' ),
				),
			)
		);

		$this->add_control(
			'post_element_order',
			array(
				'label'       => esc_html__( 'Sortable Layout Fields', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $element_repeater->get_controls(),
				'default'     => array(
					array(
						'element_type' => 'badge',
						'show_element' => 'yes',
					),
					array(
						'element_type' => 'title',
						'show_element' => 'yes',
					),
					array(
						'element_type' => 'meta',
						'show_element' => 'yes',
					),
					array(
						'element_type' => 'excerpt',
						'show_element' => 'yes',
					),
					array(
						'element_type' => 'read_more',
						'show_element' => 'yes',
					),
					array(
						'element_type' => 'author',
						'show_element' => 'no',
					),
					array(
						'element_type' => 'date',
						'show_element' => 'no',
					),
					array(
						'element_type' => 'comments',
						'show_element' => 'no',
					),
					array(
						'element_type' => 'reading_time',
						'show_element' => 'no',
					),
					array(
						'element_type' => 'share',
						'show_element' => 'no',
					),
				),
				'title_field' => '<span class="tv-builder-item tv-item-{{{ element_type }}} tv-status-{{{ show_element }}}"><span class="tv-builder-dot"></span><span class="tv-builder-label"><# ' .
					'var names = {' .
					'"badge": "' . esc_html__( 'Category', 'elonix' ) . '",' .
					'"title": "' . esc_html__( 'Title', 'elonix' ) . '",' .
					'"meta": "' . esc_html__( 'Meta', 'elonix' ) . '",' .
					'"excerpt": "' . esc_html__( 'Excerpt', 'elonix' ) . '",' .
					'"read_more": "' . esc_html__( 'Read More', 'elonix' ) . '",' .
					'"author": "' . esc_html__( 'Author', 'elonix' ) . '",' .
					'"date": "' . esc_html__( 'Date', 'elonix' ) . '",' .
					'"comments": "' . esc_html__( 'Comments', 'elonix' ) . '",' .
					'"reading_time": "' . esc_html__( 'Reading Time', 'elonix' ) . '",' .
					'"share": "' . esc_html__( 'Share', 'elonix' ) . '"' .
					'};' .
					'#>' .
					'{{{ names[element_type] || element_type }}}' .
					'</span></span>',
				'description' => esc_html__( 'Drag and drop rows to reorder layout blocks. Toggle show/hide to enable or disable elements.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 6. Content Section (Title, Excerpt, Badges, Meta parameters)
		$this->start_controls_section(
			'section_content_toggles',
			array(
				'label' => esc_html__( 'Content', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// Title customizer
		$this->add_control(
			'title_heading',
			array(
				'label'     => esc_html__( 'Title Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'Title HTML Tag', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
				),
			)
		);

		$this->add_control(
			'title_word_limit',
			array(
				'label'       => esc_html__( 'Word Limit', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Trim titles by word count. Use 0 to disable.', 'elonix' ),
			)
		);

		$this->add_control(
			'title_char_limit',
			array(
				'label'       => esc_html__( 'Character Limit', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( 'Trim titles by character count. Use 0 to disable.', 'elonix' ),
			)
		);

		$this->add_control(
			'title_suffix',
			array(
				'label'   => esc_html__( 'Custom Trim Suffix', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '...',
			)
		);

		// Excerpt customizer
		$this->add_control(
			'excerpt_heading',
			array(
				'label'     => esc_html__( 'Excerpt Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'excerpt_word_limit',
			array(
				'label'   => esc_html__( 'Excerpt Word Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 15,
				'min'     => 0,
			)
		);

		$this->add_control(
			'excerpt_char_limit',
			array(
				'label'   => esc_html__( 'Excerpt Character Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
			)
		);

		$this->add_control(
			'excerpt_strip_html',
			array(
				'label'   => esc_html__( 'Strip HTML Tags', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'excerpt_strip_shortcodes',
			array(
				'label'   => esc_html__( 'Strip Shortcodes', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'excerpt_suffix',
			array(
				'label'   => esc_html__( 'Excerpt Trim Suffix', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '...',
			)
		);

		// Badges customizer
		$this->add_control(
			'badge_heading',
			array(
				'label'     => esc_html__( 'Category Badge Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'badge_type',
			array(
				'label'   => esc_html__( 'Badge Content Type', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'category',
				'options' => array(
					'category'  => esc_html__( 'Primary Category', 'elonix' ),
					'post_type' => esc_html__( 'Post Type Name', 'elonix' ),
					'custom'    => esc_html__( 'Custom Static Text', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'badge_text',
			array(
				'label'     => esc_html__( 'Custom Static Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Featured',
				'condition' => array(
					'badge_type' => 'custom',
				),
			)
		);

		// Combined Meta settings
		$this->add_control(
			'meta_heading',
			array(
				'label'     => esc_html__( 'Meta Block Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$meta_repeater = new \Elementor\Repeater();
		$meta_repeater->add_control(
			'meta_type',
			array(
				'label'   => esc_html__( 'Meta Element', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'author'        => esc_html__( 'Author Name', 'elonix' ),
					'date'          => esc_html__( 'Date Created', 'elonix' ),
					'modified_date' => esc_html__( 'Date Modified', 'elonix' ),
					'comments'      => esc_html__( 'Comments Count', 'elonix' ),
					'reading_time'  => esc_html__( 'Reading Time (Auto)', 'elonix' ),
					'views'         => esc_html__( 'Views Count (Auto)', 'elonix' ),
				),
			)
		);

		$meta_repeater->add_control(
			'show_meta_item',
			array(
				'label'        => esc_html__( 'Enable Block Output', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'elonix' ),
				'label_off'    => esc_html__( 'Disable', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$meta_repeater->add_control(
			'icon_position',
			array(
				'label'   => esc_html__( 'Icon Position', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'before',
				'options' => array(
					'before' => esc_html__( 'Before Text', 'elonix' ),
					'after'  => esc_html__( 'After Text', 'elonix' ),
				),
			)
		);

		$meta_repeater->add_control(
			'element_color',
			array(
				'label'     => esc_html__( 'Text/Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}}' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-card {{CURRENT_ITEM}} svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_elements_order',
			array(
				'label'       => esc_html__( 'Meta Blocks Order (Combined Mode)', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $meta_repeater->get_controls(),
				'default'     => array(
					array(
						'meta_type'      => 'author',
						'show_meta_item' => 'yes',
						'icon_position'  => 'before',
					),
					array(
						'meta_type'      => 'date',
						'show_meta_item' => 'yes',
						'icon_position'  => 'before',
					),
					array(
						'meta_type'      => 'comments',
						'show_meta_item' => 'yes',
						'icon_position'  => 'before',
					),
				),
				'title_field' => '{{{ meta_type.charAt(0).toUpperCase() + meta_type.slice(1).replace("_", " ") }}}',
				'description' => esc_html__( 'Orders items displayed within the Combined Meta element block.', 'elonix' ),
			)
		);

		// Smart Badges options
		$this->add_control(
			'smart_badge_heading',
			array(
				'label'     => esc_html__( 'Smart Badge Toggles', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'show_new_badge',
			array(
				'label'   => esc_html__( 'Auto "New" Badge', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'show_popular_badge',
			array(
				'label'   => esc_html__( 'Auto "Popular" Badge', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'show_trending_badge',
			array(
				'label'   => esc_html__( 'Auto "Trending" Badge', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'show_sponsored_label',
			array(
				'label'   => esc_html__( 'Auto "Sponsored" Badge', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		// Read More Button
		$this->add_control(
			'readmore_heading',
			array(
				'label'     => esc_html__( 'Read More Controls', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'   => esc_html__( 'Button Text', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Read More', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 6. Icons Content Section
		$this->start_controls_section(
			'section_icons_content',
			array(
				'label' => esc_html__( 'Icons', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'badge_icon',
			array(
				'label'   => esc_html__( 'Category Badge Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-folder',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'read_more_icon',
			array(
				'label'   => esc_html__( 'Read More Button Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'meta_author_icon',
			array(
				'label'   => esc_html__( 'Author Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-user',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'meta_date_icon',
			array(
				'label'   => esc_html__( 'Date Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-calendar-alt',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'meta_comments_icon',
			array(
				'label'   => esc_html__( 'Comments Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-comments',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'meta_reading_time_icon',
			array(
				'label'   => esc_html__( 'Reading Time Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-clock',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'meta_views_icon',
			array(
				'label'   => esc_html__( 'Views Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-eye',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'share_icon',
			array(
				'label'   => esc_html__( 'Share Label Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-share-alt',
					'library' => 'fa-solid',
				),
			)
		);

		$this->end_controls_section();

		// 7. Featured Post settings
		$this->start_controls_section(
			'section_featured_system',
			array(
				'label' => esc_html__( 'Featured Post System', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_featured',
			array(
				'label'     => esc_html__( 'Enable Featured Post', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'elonix' ),
				'label_off' => esc_html__( 'No', 'elonix' ),
				'default'   => 'no',
			)
		);

		$this->add_control(
			'featured_mode',
			array(
				'label'     => esc_html__( 'Featured Selection Mode', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'first',
				'options'   => array(
					'first'  => esc_html__( 'First Post of Query', 'elonix' ),
					'manual' => esc_html__( 'Manual Selection', 'elonix' ),
					'sticky' => esc_html__( 'Sticky Post Priority', 'elonix' ),
				),
				'condition' => array(
					'show_featured' => 'yes',
				),
			)
		);

		$this->add_control(
			'featured_manual_id',
			array(
				'label'     => esc_html__( 'Manual Featured Post ID', 'elonix' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => array(
					'show_featured' => 'yes',
					'featured_mode' => 'manual',
				),
			)
		);

		$this->add_responsive_control(
			'featured_card_height',
			array(
				'label'      => esc_html__( 'Featured Card Height (px)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 150,
						'max' => 800,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-thumbnail img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				),
				'condition'  => array(
					'show_featured' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 8. Advanced AJAX Section
		$this->start_controls_section(
			'section_advanced_ajax_filters',
			array(
				'label' => esc_html__( 'Advanced AJAX Filters', 'elonix' ),
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
					'none'            => esc_html__( 'None', 'elonix' ),
					'ajax_numeric'    => esc_html__( 'AJAX Pagination (Numbers)', 'elonix' ),
					'load_more'       => esc_html__( 'AJAX Load More Button', 'elonix' ),
					'infinite_scroll' => esc_html__( 'Infinite Scroll (Auto)', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'pagination_text',
			array(
				'label'     => esc_html__( 'Load More Button Text', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Load More', 'elonix' ),
				'condition' => array(
					'pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'ajax_tabs',
			array(
				'label'   => esc_html__( 'Show AJAX Filter Tabs', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'     => esc_html__( 'No Tabs', 'elonix' ),
					'category' => esc_html__( 'Category Filter Tabs', 'elonix' ),
					'tag'      => esc_html__( 'Tag Filter Tabs', 'elonix' ),
					'author'   => esc_html__( 'Author Filter Tabs', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'ajax_search',
			array(
				'label'   => esc_html__( 'Enable Live Search Filter', 'elonix' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================
		$this->register_style_controls();
	}

	/**
	 * Register style controls (reusable for Search Results widget).
	 */
	public function register_style_controls() {

		// 1. Container Styles
		$this->start_controls_section(
			'section_style_container',
			array(
				'label' => esc_html__( 'Container', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'container_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 300,
						'max' => 1600,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-wrap' => 'max-width: {{SIZE}}{{UNIT}}; margin: 0 auto;',
				),
			)
		);

		$this->add_responsive_control(
			'container_width_val',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 300,
						'max' => 1600,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-wrap' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'     => esc_html__( 'Column Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-grid' => 'column-gap: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'     => esc_html__( 'Row / Card Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-grid' => 'row-gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-post-block-container.tv-layout-style_1' => 'row-gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-post-block-container.tv-layout-style_3' => 'row-gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-post-block-container.tv-layout-style_4' => 'row-gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-post-block-container.tv-layout-style_3 .tv-grid-secondary-wrapper' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Container Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'selector' => '{{WRAPPER}} .tv-post-block-wrap',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .tv-post-block-wrap',
			)
		);

		$this->add_control(
			'container_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .tv-post-block-wrap',
			)
		);

		$this->end_controls_section();

		// 2. AJAX Filter Tabs Styles
		$this->start_controls_section(
			'section_style_filter_tabs',
			array(
				'label' => esc_html__( 'AJAX Filter Tabs', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'filter_tab_typography',
				'selector' => '{{WRAPPER}} .tv-filter-tab',
			)
		);

		$this->add_responsive_control(
			'filter_tab_container_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-tabs' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_filter_tab_states' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_filter_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'filter_tab_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_tab_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_tab_border',
				'selector' => '{{WRAPPER}} .tv-filter-tab',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_filter_tab_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'filter_tab_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_tab_bg_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_tab_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active Tab
		$this->start_controls_tab(
			'tab_filter_tab_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_control(
			'filter_tab_color_active',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab.active' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_tab_bg_active',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab.active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'filter_tab_border_color_active',
			array(
				'label'     => esc_html__( 'Border Active Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-filter-tab.active' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'filter_tab_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-filter-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'filter_tab_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-filter-tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 3. Live Search Filter Styles
		$this->start_controls_section(
			'section_style_search_filter',
			array(
				'label' => esc_html__( 'Live Search Filter', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'search_input_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-search-input',
			)
		);

		$this->add_responsive_control(
			'search_input_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_search_input_states' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_search_input_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'search_input_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-search-input' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'search_input_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-search-input' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'search_input_border',
				'selector' => '{{WRAPPER}} .tv-post-block-search-input',
			)
		);

		$this->add_control(
			'search_placeholder_color',
			array(
				'label'     => esc_html__( 'Placeholder Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-search-input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Focus Tab
		$this->start_controls_tab(
			'tab_search_input_focus',
			array(
				'label' => esc_html__( 'Focus', 'elonix' ),
			)
		);

		$this->add_control(
			'search_input_border_color_focus',
			array(
				'label'     => esc_html__( 'Border Focus Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-search-input:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'search_input_shadow_focus',
				'selector' => '{{WRAPPER}} .tv-post-block-search-input:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'search_input_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_control(
			'search_icon_styling_heading',
			array(
				'label'     => esc_html__( 'Search Icon Styling', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'search_icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 40,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-search-icon svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'search_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-search-icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 4. Post Item/Card Styles
		$this->start_controls_section(
			'section_style_post_card',
			array(
				'label' => esc_html__( 'Post Item / Card', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_post_card_style' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_post_card_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background',
				'selector' => '{{WRAPPER}} .tv-post-block-card',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .tv-post-block-card',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .tv-post-block-card',
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_post_card_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background_hover',
				'selector' => '{{WRAPPER}} .tv-post-block-card:hover',
			)
		);

		$this->add_control(
			'card_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-card:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-post-block-card:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 5. Content Area Styles
		$this->start_controls_section(
			'section_style_content_area',
			array(
				'label' => esc_html__( 'Content Area', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'content_area_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 800,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-content' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_area_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_area_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'content_area_background',
				'selector' => '{{WRAPPER}} .tv-post-block-content',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'content_area_border',
				'selector' => '{{WRAPPER}} .tv-post-block-content',
			)
		);

		$this->add_responsive_control(
			'content_area_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 6. Thumbnail Styles
		$this->start_controls_section(
			'section_style_thumbnail',
			array(
				'label' => esc_html__( 'Thumbnail', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
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
					'{{WRAPPER}} .tv-post-block-thumbnail' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 600,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-thumbnail img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				),
			)
		);

		$this->add_control(
			'image_ratio',
			array(
				'label'     => esc_html__( 'Aspect Ratio', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none' => esc_html__( 'Default', 'elonix' ),
					'1-1'  => '1:1 (Square)',
					'4-3'  => '4:3 (Landscape)',
					'16-9' => '16:9 (Widescreen)',
					'3-4'  => '3:4 (Portrait)',
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-thumbnail img' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-thumbnail, {{WRAPPER}} .tv-post-block-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'cover'   => 'Cover',
					'contain' => 'Contain',
					'fill'    => 'Fill',
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-thumbnail img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_overlay',
			array(
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-thumbnail::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_overlay_hover',
			array(
				'label'     => esc_html__( 'Hover Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-thumbnail:hover::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .tv-post-block-thumbnail img',
			)
		);

		$this->start_controls_tabs( 'tabs_thumbnail_style_shadow' );

		$this->start_controls_tab(
			'tab_thumbnail_shadow_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_shadow',
				'selector' => '{{WRAPPER}} .tv-post-block-thumbnail img',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_thumbnail_shadow_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_shadow_hover',
				'selector' => '{{WRAPPER}} .tv-post-block-thumbnail:hover img, {{WRAPPER}} .tv-post-block-thumbnail img:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 7. Badge Styles
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => esc_html__( 'Badge', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-badge',
			)
		);

		$this->start_controls_tabs( 'tabs_badge_style' );

		$this->start_controls_tab(
			'tab_badge_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'badge_border',
				'selector' => '{{WRAPPER}} .tv-post-block-badge',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_badge_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'badge_text_color_hover',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-badge:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-badge:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-badge:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Badge Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'badge_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_wrapper_margin',
			array(
				'label'      => esc_html__( 'Badge Margin/Gap', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-badge-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 8. Title Styles
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Title', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-title, {{WRAPPER}} .tv-post-block-title a',
			)
		);

		$this->start_controls_tabs( 'tabs_title_style' );

		$this->start_controls_tab(
			'tab_title_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-title, {{WRAPPER}} .tv-post-block-title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-title a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_shadow',
				'selector' => '{{WRAPPER}} .tv-post-block-title, {{WRAPPER}} .tv-post-block-title a',
			)
		);

		$this->end_controls_section();

		// 9. Meta Styles
		$this->start_controls_section(
			'section_style_meta',
			array(
				'label' => esc_html__( 'Meta', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-meta, {{WRAPPER}} .tv-post-block-meta *, {{WRAPPER}} .tv-meta-item, {{WRAPPER}} .tv-meta-item *',
			)
		);

		$this->add_control(
			'meta_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta, {{WRAPPER}} .tv-post-block-meta *, {{WRAPPER}} .tv-meta-item, {{WRAPPER}} .tv-meta-item *' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta a:hover, {{WRAPPER}} .tv-post-block-meta-single a:hover, {{WRAPPER}} .tv-meta-item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'meta_gap',
			array(
				'label'     => esc_html__( 'Items Spacing Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta'    => 'gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-post-block-content' => 'row-gap: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'meta_divider_show',
			array(
				'label'     => esc_html__( 'Show Divider', 'elonix' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'meta_divider_style',
			array(
				'label'     => esc_html__( 'Divider Style', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'solid'  => esc_html__( 'Solid', 'elonix' ),
					'double' => esc_html__( 'Double', 'elonix' ),
					'dotted' => esc_html__( 'Dotted', 'elonix' ),
					'dashed' => esc_html__( 'Dashed', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta .tv-meta-item:not(:last-child)::after' => 'border-left-style: {{VALUE}};',
				),
				'condition' => array(
					'meta_divider_show' => 'yes',
				),
			)
		);

		$this->add_control(
			'meta_divider_color',
			array(
				'label'     => esc_html__( 'Divider Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ccc',
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta .tv-meta-item:not(:last-child)::after' => 'border-left-color: {{VALUE}};',
				),
				'condition' => array(
					'meta_divider_show' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_divider_width',
			array(
				'label'     => esc_html__( 'Divider Width (Thickness)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'default'   => array(
					'size' => 1,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta .tv-meta-item:not(:last-child)::after' => 'border-left-width: {{SIZE}}px;',
				),
				'condition' => array(
					'meta_divider_show' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_divider_height',
			array(
				'label'     => esc_html__( 'Divider Height', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 5,
						'max' => 40,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'default'   => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta .tv-meta-item:not(:last-child)::after' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'meta_divider_show' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_divider_gap',
			array(
				'label'     => esc_html__( 'Divider Spacing / Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array(
					'size' => 10,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-meta .tv-meta-item:not(:last-child)::after' => 'margin-left: {{SIZE}}px; margin-right: {{SIZE}}px;',
				),
				'condition' => array(
					'meta_divider_show' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_icon_size',
			array(
				'label'      => esc_html__( 'Individual Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 6,
						'max' => 36,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-meta-item .tv-post-block-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-meta-item .tv-post-block-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'meta_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-item .tv-post-block-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-meta-item .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 10. Excerpt Styles
		$this->start_controls_section(
			'section_style_excerpt',
			array(
				'label' => esc_html__( 'Excerpt', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-excerpt',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'excerpt_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 11. Read More Styles
		$this->start_controls_section(
			'section_style_readmore',
			array(
				'label' => esc_html__( 'Read More', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'readmore_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn',
			)
		);

		$this->start_controls_tabs( 'tabs_readmore_style' );

		$this->start_controls_tab(
			'tab_readmore_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'readmore_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'readmore_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'readmore_border',
				'selector' => '{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_readmore_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'readmore_color_hover',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a:hover, {{WRAPPER}} .tv-readmore-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'readmore_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a:hover, {{WRAPPER}} .tv-readmore-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'readmore_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a:hover, {{WRAPPER}} .tv-readmore-btn:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'readmore_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: inline-block;',
				),
			)
		);

		$this->add_responsive_control(
			'readmore_icon_gap',
			array(
				'label'     => esc_html__( 'Icon Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a .tv-post-block-icon' => 'margin-right: {{SIZE}}px; margin-left: 0;',
					'{{WRAPPER}} .tv-post-block-readmore a.tv-icon-pos-after .tv-post-block-icon' => 'margin-left: {{SIZE}}px; margin-right: 0;',
				),
			)
		);

		$this->add_control(
			'readmore_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-readmore a, {{WRAPPER}} .tv-readmore-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'read_more_icon_position',
			array(
				'label'   => esc_html__( 'Icon Position', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'before',
				'options' => array(
					'before' => esc_html__( 'Before Text', 'elonix' ),
					'after'  => esc_html__( 'After Text', 'elonix' ),
				),
			)
		);

		$this->add_responsive_control(
			'readmore_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 6,
						'max' => 36,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-readmore a .tv-post-block-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-post-block-readmore a .tv-post-block-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'readmore_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-readmore a .tv-post-block-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-readmore a .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 12. Icons Style Section
		$this->start_controls_section(
			'section_style_icon',
			array(
				'label' => esc_html__( 'Icons', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-icon i, {{WRAPPER}} .tv-post-block-icon svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-icon i, {{WRAPPER}} .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-card:hover .tv-post-block-icon i, {{WRAPPER}} .tv-post-block-card:hover .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'     => esc_html__( 'Icon Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-icon' => 'margin-right: {{SIZE}}px; margin-left: 0;',
					'{{WRAPPER}} .tv-meta-item.tv-icon-pos-after .tv-post-block-icon' => 'margin-left: {{SIZE}}px; margin-right: 0;',
					'{{WRAPPER}} .tv-post-block-meta-single.tv-icon-pos-after .tv-post-block-icon' => 'margin-left: {{SIZE}}px; margin-right: 0;',
					'{{WRAPPER}} .tv-post-block-share.tv-icon-pos-after .tv-post-block-icon' => 'margin-left: {{SIZE}}px; margin-right: 0;',
				),
			)
		);

		$this->add_control(
			'icon_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'text-top'    => array(
						'title' => esc_html__( 'Top', 'elonix' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle'      => array(
						'title' => esc_html__( 'Middle', 'elonix' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'text-bottom' => array(
						'title' => esc_html__( 'Bottom', 'elonix' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'middle',
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-icon' => 'vertical-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border',
				'selector' => '{{WRAPPER}} .tv-post-block-icon',
			)
		);

		$this->add_responsive_control(
			'icon_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: inline-block;',
				),
			)
		);

		$this->end_controls_section();

		// 13. Comments Style Section
		$this->start_controls_section(
			'section_style_comments',
			array(
				'label' => esc_html__( 'Comments', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'comments_typography',
				'selector' => '{{WRAPPER}} .tv-meta-item-comments, {{WRAPPER}} .tv-meta-item-comments a, {{WRAPPER}} .tv-meta-comments, {{WRAPPER}} .tv-meta-comments a',
			)
		);

		$this->add_control(
			'comments_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-item-comments, {{WRAPPER}} .tv-meta-item-comments a, {{WRAPPER}} .tv-meta-comments, {{WRAPPER}} .tv-meta-comments a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'comments_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-item-comments:hover, {{WRAPPER}} .tv-meta-item-comments a:hover, {{WRAPPER}} .tv-meta-comments:hover, {{WRAPPER}} .tv-meta-comments a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'comments_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-comments .tv-post-block-icon i, {{WRAPPER}} .tv-meta-comments .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'comments_icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-comments:hover .tv-post-block-icon i, {{WRAPPER}} .tv-meta-comments:hover .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 14. Reading Time Style Section
		$this->start_controls_section(
			'section_style_reading_time',
			array(
				'label' => esc_html__( 'Reading Time', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'reading_time_typography',
				'selector' => '{{WRAPPER}} .tv-meta-item-reading, {{WRAPPER}} .tv-meta-reading',
			)
		);

		$this->add_control(
			'reading_time_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-item-reading, {{WRAPPER}} .tv-meta-reading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'reading_time_color_hover',
			array(
				'label'     => esc_html__( 'Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-item-reading:hover, {{WRAPPER}} .tv-meta-reading:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'reading_time_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-reading .tv-post-block-icon i, {{WRAPPER}} .tv-meta-reading .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'reading_time_icon_color_hover',
			array(
				'label'     => esc_html__( 'Hover Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-meta-reading:hover .tv-post-block-icon i, {{WRAPPER}} .tv-meta-reading:hover .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// 15. Share Style Section
		$this->start_controls_section(
			'section_style_share',
			array(
				'label' => esc_html__( 'Share', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'share_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-share, {{WRAPPER}} .tv-share-label',
			)
		);

		$this->add_control(
			'share_color',
			array(
				'label'     => esc_html__( 'Label Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_color_hover',
			array(
				'label'     => esc_html__( 'Label Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-label:hover, {{WRAPPER}} .tv-post-block-share:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-share .tv-post-block-icon i, {{WRAPPER}} .tv-post-block-share .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_icon_color_hover',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-share:hover .tv-post-block-icon i, {{WRAPPER}} .tv-post-block-share:hover .tv-post-block-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_buttons_heading',
			array(
				'label'     => esc_html__( 'Share Buttons Styling', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'share_btn_size',
			array(
				'label'     => esc_html__( 'Button Size', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 15,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px * 0.45); line-height: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'share_btn_gap',
			array(
				'label'     => esc_html__( 'Button Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-share' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_share_btn_style' );

		$this->start_controls_tab(
			'tab_share_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'share_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_btn_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'share_btn_border',
				'selector' => '{{WRAPPER}} .tv-share-btn',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_share_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'share_btn_color_hover',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_btn_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'share_btn_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-share-btn:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'share_btn_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'share_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-share-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 16. Featured Post Style Section
		$this->start_controls_section(
			'section_style_featured',
			array(
				'label' => esc_html__( 'Featured Post', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'featured_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'featured_height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-thumbnail img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'featured_position',
			array(
				'label'     => esc_html__( 'Position', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'featured_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-card' => 'margin-bottom: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'featured_overlay',
			array(
				'label'     => esc_html__( 'Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-thumbnail::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'featured_gap',
			array(
				'label'     => esc_html__( 'Featured Card Gap', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-layout-style_1' => 'gap: {{SIZE}}px;',
					'{{WRAPPER}} .tv-layout-style_3' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'featured_content_padding',
			array(
				'label'      => esc_html__( 'Content Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'featured_content_margin',
			array(
				'label'      => esc_html__( 'Content Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 17. Featured Post Content Area Styles
		$this->start_controls_section(
			'section_style_feat_content',
			array(
				'label' => esc_html__( 'Featured Post Content Area', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'feat_content_area_width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'feat_content_area_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'feat_content_area_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'feat_content_area_background',
				'selector' => '{{WRAPPER}} .tv-featured-card .tv-post-block-content',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'feat_content_area_border',
				'selector' => '{{WRAPPER}} .tv-featured-card .tv-post-block-content',
			)
		);

		$this->add_responsive_control(
			'feat_content_area_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-featured-card .tv-post-block-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// 18. Pagination Styles
		$this->start_controls_section(
			'section_style_pagination',
			array(
				'label' => esc_html__( 'Pagination', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .tv-post-block-pagination, {{WRAPPER}} .tv-post-block-pagination-nav button, {{WRAPPER}} .tv-post-block-load-more',
			)
		);

		$this->start_controls_tabs( 'tabs_pagination_style' );

		// Normal Tab
		$this->start_controls_tab(
			'tab_pagination_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'pagination_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pagination_border',
				'selector' => '{{WRAPPER}} .tv-post-block-pagination-nav button, {{WRAPPER}} .tv-post-block-load-more',
			)
		);

		$this->add_responsive_control(
			'pagination_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button, {{WRAPPER}} .tv-post-block-load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover Tab
		$this->start_controls_tab(
			'tab_pagination_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'pagination_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:hover' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_radius_hover',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:hover, {{WRAPPER}} .tv-post-block-load-more:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		// Active Tab
		$this->start_controls_tab(
			'tab_pagination_active',
			array(
				'label' => esc_html__( 'Active', 'elonix' ),
			)
		);

		$this->add_control(
			'pagination_color_active',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button.active' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_bg_color_active',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button.active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_border_color_active',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button.active' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_radius_active',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		// Disabled Tab
		$this->start_controls_tab(
			'tab_pagination_disabled',
			array(
				'label' => esc_html__( 'Disabled', 'elonix' ),
			)
		);

		$this->add_control(
			'pagination_color_disabled',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:disabled' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:disabled' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_bg_color_disabled',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:disabled' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:disabled' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_border_color_disabled',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button:disabled' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-block-load-more:disabled' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Button Spacing / Margin / Padding
		$this->add_responsive_control(
			'pagination_button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav button, {{WRAPPER}} .tv-post-block-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_button_gap',
			array(
				'label'     => esc_html__( 'Button Gap Spacing', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-block-pagination-nav' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Post Block widget output on the frontend.
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
			'thumbnail_size',
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
			'show_featured',
			'featured_mode',
			'featured_manual_id',
			'title_tag',
			'title_word_limit',
			'title_char_limit',
			'title_suffix',
			'excerpt_word_limit',
			'excerpt_char_limit',
			'excerpt_strip_html',
			'excerpt_strip_shortcodes',
			'excerpt_suffix',
			'badge_type',
			'badge_text',
			'meta_elements_order',
			'post_element_order',
			'show_new_badge',
			'show_popular_badge',
			'show_trending_badge',
			'show_sponsored_label',
			'read_more_text',
			'pagination_type',
			'pagination_text',
			'ajax_tabs',
			'ajax_search',
			'badge_icon',
			'read_more_icon',
			'meta_author_icon',
			'meta_date_icon',
			'meta_comments_icon',
			'meta_reading_time_icon',
			'meta_views_icon',
			'share_icon',
		);
		foreach ( $keys_to_pass as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$ajax_settings[ $key ] = $settings[ $key ];
			}
		}

		$layout                 = ! empty( $settings['layout'] ) ? $settings['layout'] : 'style_1';
		$ajax_settings['paged'] = 1;

		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Query_Helper' ) ) {
			require_once __DIR__ . '/helper-query.php';
		}

		$archive_vars = array();
		if ( ! empty( $settings['query_mode'] ) && 'current' === $settings['query_mode'] ) {
			global $wp_query;
			$archive_vars = $wp_query->query_vars;
		}

		$posts_data = Elonix_Toolkit_Post_Block_Query_Helper::get_posts_data( $ajax_settings, $archive_vars );

		// Prepend manual featured post if configured
		if ( 'yes' === $settings['show_featured'] && 'manual' === $settings['featured_mode'] && ! empty( $settings['featured_manual_id'] ) ) {
			$manual_featured_id = intval( $settings['featured_manual_id'] );
			$featured_post_obj  = get_post( $manual_featured_id );
			if ( $featured_post_obj && ! is_wp_error( $featured_post_obj ) ) {
				$featured_item = Elonix_Toolkit_Post_Block_Query_Helper::format_post_data( $featured_post_obj, $ajax_settings );
				// Avoid duplicate rendering
				foreach ( $posts_data as $k => $item ) {
					if ( intval( $item['id'] ) === $manual_featured_id ) {
						unset( $posts_data[ $k ] );
					}
				}
				array_unshift( $posts_data, $featured_item );
				$posts_data = array_slice( $posts_data, 0, ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : 6 );
			}
		}

		$pagination = ! empty( $settings['pagination_type'] ) ? $settings['pagination_type'] : 'none';

		// Resolve total pages count
		global $wp_query;
		if ( ! empty( $settings['query_mode'] ) && 'current' === $settings['query_mode'] ) {
			$max_num_pages = $wp_query->max_num_pages;
		} else {
			$query_args    = \Elonix_Query_Context::build_query_args( $ajax_settings, $archive_vars );
			$count_query   = new WP_Query( $query_args );
			$max_num_pages = $count_query->max_num_pages;
			wp_reset_postdata();
		}

		$nonce = wp_create_nonce( 'tv-post-block-nonce' );

		?>
		<div id="tv-post-block-<?php echo esc_attr( $this->get_id() ); ?>" class="tv-post-block-wrap tv-posts-pagination-<?php echo esc_attr( $pagination ); ?>"
			data-settings="<?php echo esc_attr( wp_json_encode( $ajax_settings ) ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-max-pages="<?php echo esc_attr( $max_num_pages ); ?>"
			data-archive-vars="<?php echo esc_attr( wp_json_encode( $archive_vars ) ); ?>"
			data-i18n="
			<?php
			echo esc_attr(
				wp_json_encode(
					array(
						'prev'     => __( 'Prev', 'elonix' ),
						'next'     => __( 'Next', 'elonix' ),
						/* translators: %d: string */
						'page'     => __( 'Go to page %d', 'elonix' ),
						'loading'  => __( 'Loading posts...', 'elonix' ),
						'loaded'   => __( 'Posts loaded.', 'elonix' ),
						'no_posts' => __( 'No posts found.', 'elonix' ),
					)
				)
			);
			?>
			">

			<!-- WCAG aria-live dynamic contents announcer -->
			<div class="tv-sr-announcer screen-reader-text sr-only" aria-live="polite" aria-atomic="true" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;"></div>

			<?php
			// Render Filter Tabs / Search bar
			if ( 'none' !== $settings['ajax_tabs'] || 'yes' === $settings['ajax_search'] ) :
				?>
				<div class="tv-post-block-filters-bar">
					<?php if ( 'none' !== $settings['ajax_tabs'] ) : ?>
						<div class="tv-post-block-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Archive filters', 'elonix' ); ?>">
							<button class="tv-filter-tab active" role="tab" aria-selected="true" aria-controls="tv-post-panel-<?php echo esc_attr( $this->get_id() ); ?>" id="tv-filter-tab-0-<?php echo esc_attr( $this->get_id() ); ?>" data-filter-id="0">
								<?php esc_html_e( 'All', 'elonix' ); ?>
							</button>
							<?php
							if ( 'author' === $settings['ajax_tabs'] ) {
								$source    = ! empty( $settings['query_source'] ) ? $settings['query_source'] : 'posts';
								$post_type = 'post';
								if ( 'cpt' === $source && ! empty( $settings['custom_post_type'] ) ) {
									$post_type = $settings['custom_post_type'];
								} elseif ( 'woo' === $source ) {
									$post_type = 'product';
								} elseif ( 'pages' === $source ) {
									$post_type = 'page';
								}
								$authors = get_users(
									array(
										'orderby' => 'post_count',
										'order'   => 'DESC',
										'number'  => 8,
										'has_published_posts' => array( $post_type ),
									)
								);
								if ( ! empty( $authors ) && ! is_wp_error( $authors ) ) {
									foreach ( $authors as $author ) {
										printf(
											'<button class="tv-filter-tab" role="tab" aria-selected="false" aria-controls="tv-post-panel-%s" id="tv-filter-tab-%d-%s" data-filter-id="%d">%s</button>',
											esc_attr( $this->get_id() ),
											intval( $author->ID ),
											esc_attr( $this->get_id() ),
											intval( $author->ID ),
											esc_html( $author->display_name )
										);
									}
								}
							} else {
								$taxonomy   = $this->get_tabs_taxonomy( $settings );
								$terms_list = array();
								if ( ! empty( $taxonomy ) ) {
									$terms_list = get_terms(
										array(
											'taxonomy'   => $taxonomy,
											'number'     => 8,
											'hide_empty' => true,
										)
									);
								}

								if ( ! is_wp_error( $terms_list ) && ! empty( $terms_list ) ) {
									foreach ( $terms_list as $term_obj ) {
										printf(
											'<button class="tv-filter-tab" role="tab" aria-selected="false" aria-controls="tv-post-panel-%s" id="tv-filter-tab-%d-%s" data-filter-id="%d">%s</button>',
											esc_attr( $this->get_id() ),
											intval( $term_obj->term_id ),
											esc_attr( $this->get_id() ),
											intval( $term_obj->term_id ),
											esc_html( $term_obj->name )
										);
									}
								}
							}
							?>
						</div>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['ajax_search'] ) : ?>
						<div class="tv-post-block-search-wrap" role="search">
							<input type="search" class="tv-post-block-search-input" placeholder="<?php esc_attr_e( 'Search posts...', 'elonix' ); ?>" aria-label="<?php esc_attr_e( 'Search posts list', 'elonix' ); ?>">
							<span class="tv-search-icon">
								<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php
			$grid_classes = array( 'tv-post-block-container', 'tv-layout-' . $layout );
			if ( in_array( $layout, array( 'style_2', 'style_5' ), true ) ) {
				$grid_classes[] = 'tv-post-block-grid';
			}
			?>

			<div id="tv-post-panel-<?php echo esc_attr( $this->get_id() ); ?>" class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>" role="tabpanel" aria-labelledby="tv-filter-tab-0-<?php echo esc_attr( $this->get_id() ); ?>" aria-live="polite" aria-busy="false">
				<?php
				if ( ! empty( $posts_data ) ) {
					$post_index  = 0;
					$has_wrapper = false;
					foreach ( $posts_data as $item ) {
						if ( 'style_3' === $layout && 1 === $post_index ) {
							echo '<div class="tv-grid-secondary-wrapper">';
							$has_wrapper = true;
						}
						self::render_single_post( $item, $settings, $layout, $post_index );
						++$post_index;
					}
					if ( $has_wrapper ) {
						echo '</div>';
					}
				} else {
					?>
					<div class="tv-no-posts"><?php esc_html_e( 'No posts found.', 'elonix' ); ?></div>
					<?php
				}
				?>
			</div>

			<?php
			// Render dynamic pagination buttons
			if ( 'none' !== $pagination && ! empty( $posts_data ) ) :
				$pag_display_style = ( $max_num_pages > 1 ) ? '' : 'display: none;';
				?>
				<div class="tv-post-block-pagination" style="<?php echo esc_attr( $pag_display_style ); ?>">
					<?php if ( 'load_more' === $pagination ) : ?>
						<button class="tv-post-block-load-more tv-btn" <?php echo ( $max_num_pages <= 1 ) ? 'disabled aria-disabled="true"' : ''; ?>>
							<span class="tv-btn-text"><?php echo esc_html( $settings['pagination_text'] ); ?></span>
							<span class="tv-btn-spinner" style="display: none;">
								<svg class="tv-spinner" viewBox="0 0 50 50">
									<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle>
								</svg>
							</span>
						</button>
					<?php elseif ( 'infinite_scroll' === $pagination ) : ?>
						<div class="tv-post-block-scroll-trigger">
							<span class="tv-scroll-spinner">
								<svg class="tv-spinner" viewBox="0 0 50 50">
									<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle>
								</svg>
							</span>
						</div>
					<?php elseif ( 'ajax_numeric' === $pagination ) : ?>
						<nav class="tv-post-block-pagination-nav" aria-label="<?php esc_attr_e( 'Pagination navigation', 'elonix' ); ?>">
							<button class="tv-page-navprev disabled" disabled aria-disabled="true" data-paged="0" aria-label="<?php esc_attr_e( 'Previous page', 'elonix' ); ?>">
								<?php esc_html_e( 'Prev', 'elonix' ); ?>
							</button>
							<?php
							for ( $i = 1; $i <= $max_num_pages; $i++ ) {
								$active_class = ( 1 === $i ) ? 'active' : '';
								$aria_current = ( 1 === $i ) ? 'aria-current="page"' : '';
								$btn_disabled = ( 1 === $i ) ? 'disabled' : '';
								printf(
									'<button class="tv-page-num %s" %s %s data-paged="%d" aria-label="%s">%d</button>',
									esc_attr( $active_class ),
									$aria_current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									esc_attr( $btn_disabled ),
									intval( $i ),
									/* translators: %d: string */
									esc_attr( sprintf( __( 'Go to page %d', 'elonix' ), $i ) ),
									intval( $i )
								);
							}
							?>
							<button class="tv-page-navnext" <?php echo ( $max_num_pages <= 1 ) ? 'disabled aria-disabled="true"' : ''; ?> data-paged="2" aria-label="<?php esc_attr_e( 'Next page', 'elonix' ); ?>">
								<?php esc_html_e( 'Next', 'elonix' ); ?>
							</button>
						</nav>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Render a standard Elementor icon.
	 *
	 * Delegates to the shared renderer.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $icon_key Setting key that holds the icon array.
	 * @return string Icon HTML markup or empty string.
	 */
	protected static function get_icon_markup( $settings, $icon_key ) {
		return Elonix_Toolkit_Post_Block_Renderer::render_icon_markup( $settings, $icon_key );
	}

	/**
	 * Render single post card — delegates to the shared renderer.
	 *
	 * Kept as a public static method so that any existing call sites
	 * (including cached AJAX requests) continue to work.
	 *
	 * @param array  $item       Formatted post data.
	 * @param array  $settings   Widget settings.
	 * @param string $layout     Layout key.
	 * @param int    $post_index Zero-based post index.
	 */
	public static function render_single_post( $item, $settings, $layout, $post_index = 0 ) {
		Elonix_Toolkit_Post_Block_Renderer::render_single_post( $item, $settings, $layout, $post_index );
	}

	/**
	 * Resolves taxonomy name dynamically for AJAX filter tabs based on query source.
	 *
	 * @param array $settings Widget settings.
	 * @return string Taxonomy name.
	 */
	protected function get_tabs_taxonomy( $settings ) {
		$source   = ! empty( $settings['query_source'] ) ? $settings['query_source'] : 'posts';
		$tab_type = ! empty( $settings['ajax_tabs'] ) ? $settings['ajax_tabs'] : 'none';

		if ( 'category' === $tab_type ) {
			if ( 'woo' === $source ) {
				return 'product_cat';
			} elseif ( 'cpt' === $source && ! empty( $settings['custom_post_type'] ) ) {
				$taxonomies = get_object_taxonomies( $settings['custom_post_type'], 'objects' );
				foreach ( $taxonomies as $tax ) {
					if ( $tax->hierarchical && $tax->public ) {
						return $tax->name;
					}
				}
			}
			return 'category';
		} elseif ( 'tag' === $tab_type ) {
			if ( 'woo' === $source ) {
				return 'product_tag';
			} elseif ( 'cpt' === $source && ! empty( $settings['custom_post_type'] ) ) {
				$taxonomies = get_object_taxonomies( $settings['custom_post_type'], 'objects' );
				foreach ( $taxonomies as $tax ) {
					if ( ! $tax->hierarchical && $tax->public ) {
						return $tax->name;
					}
				}
			}
			return 'post_tag';
		}
		return '';
	}
}
