<?php
/**
 * Elonix Search Results Elementor Widget.
 *
 * Functionally identical to es-post-block-grid. The only difference is that
 * this widget sources its posts from the current WordPress search query
 * (get_search_query()) instead of a manually-configured query builder.
 *
 * Rendering is entirely delegated to Elonix_Toolkit_Post_Block_Renderer
 * and Elonix_Toolkit_Post_Block_Query_Helper so HTML, CSS classes, and
 * DOM structure are 1:1 with Post Block Grid.
 *
 * IMPORTANT: To ensure 100% style control integration and avoid duplicated controls,
 * this widget extends Elonix_Toolkit_Post_Block_Widget and reuses its style registration.
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
use Elementor\Group_Control_Text_Shadow;

// Load Search Results helpers.
require_once __DIR__ . '/helper-query.php';
require_once __DIR__ . '/helper-empty-state.php';

// Load the shared renderer (no Elementor inheritance — safe at any boot phase).
if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Renderer' ) ) {
	require_once dirname( __DIR__ ) . '/es-post-block/class-renderer.php';
}

// Load Post Block Query Helper so the renderer can call limit_text_content().
if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Query_Helper' ) ) {
	require_once dirname( __DIR__ ) . '/es-post-block/helper-query.php';
}

// Load Post Block Widget to extend and reuse its style controls.
if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Widget' ) ) {
	require_once dirname( __DIR__ ) . '/es-post-block/class-widget.php';
}

/**
 * Elementor widget that displays the current WordPress Search Results using the
 * exact same layout engine, controls, and rendering as es-post-block-grid.
 */
class Elonix_Toolkit_Search_Results_Widget extends Elonix_Toolkit_Post_Block_Widget {

	// ──────────────────────────────────────────────────────────────────────────
	// Identity
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Get widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'es-search-results';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Search Results', 'elonix' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_es_widget_icon() {
		return 'eicon-archive-posts';
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array
	 */
	public function get_es_widget_keywords() {
		return array( 'search', 'results', 'query', 'posts', 'archive', 'grid', 'eskit' );
	}

	/**
	 * Declare style dependencies. Reuses the Post Block stylesheet so CSS classes
	 * are identical. Also loads the Search Results stylesheet for search-specific
	 * chrome (stats bar, empty state, filter sidebar).
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array(
			'elonix-widget-es-post-block',
			'elonix-widget-es-search-results',
		);
	}

	/**
	 * Declare script dependencies. Reuses the Post Block JS for AJAX, Load More,
	 * Infinite Scroll, and pagination. Also loads Search Results JS for
	 * search-specific filter sidebar behaviour.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array(
			'elonix-widget-es-post-block',
			'elonix-widget-es-search-results',
		);
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Helper: post-type options (mirrors Post Block)
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Return public post type options (excludes attachments).
	 *
	 * @return array
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

	// ──────────────────────────────────────────────────────────────────────────
	// Controls registration
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Register Elementor controls.
	 */
	protected function register_controls() {

		// ====================================================================
		// CONTENT TAB
		// ====================================================================

		// 1. Layout Settings (mirrors Post Block)
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
				'description' => esc_html__( 'Select a layout style for search result rendering.', 'elonix' ),
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
					'{{WRAPPER}} .es-post-block-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
				'condition'      => array(
					'layout' => array( 'style_2', 'style_5' ),
				),
			)
		);

		$this->end_controls_section();

		// 2. Query Section (Search-specific: post types, order, date — NO manual post IDs, NO category/tag filter)
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__( 'Search Query', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'search_info_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<div style="background:#f0f4ff;padding:10px 12px;border-left:3px solid #4e87ef;font-size:12px;line-height:1.6">'
					. '<strong>' . esc_html__( 'Search Source', 'elonix' ) . '</strong><br>'
					. esc_html__( 'This widget automatically displays the current WordPress search query (get_search_query()). No manual query configuration is needed.', 'elonix' )
					. '</div>',
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'post_types',
			array(
				'label'       => esc_html__( 'Post Types to Search', 'elonix' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => array( 'post', 'page' ),
				'options'     => $this->get_post_types_options(),
				'label_block' => true,
				'description' => esc_html__( 'Limit search results to these post types.', 'elonix' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'       => esc_html__( 'Results Per Page', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 9,
				'min'         => 1,
				'max'         => 100,
				'description' => esc_html__( 'Set maximum search results per page.', 'elonix' ),
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

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order By', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'relevance',
				'options' => array(
					'relevance'     => esc_html__( 'Search Relevance', 'elonix' ),
					'date'          => esc_html__( 'Date', 'elonix' ),
					'title'         => esc_html__( 'Title', 'elonix' ),
					'modified'      => esc_html__( 'Last Modified', 'elonix' ),
					'comment_count' => esc_html__( 'Comment Count', 'elonix' ),
					'rand'          => esc_html__( 'Random', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Sort Order', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => esc_html__( 'Descending', 'elonix' ),
					'ASC'  => esc_html__( 'Ascending', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'date_filter',
			array(
				'label'   => esc_html__( 'Date Filter', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'  => esc_html__( 'All Time', 'elonix' ),
					'today' => esc_html__( 'Today', 'elonix' ),
					'week'  => esc_html__( 'This Week', 'elonix' ),
					'month' => esc_html__( 'This Month', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => esc_html__( 'Exclude Post IDs (CSV)', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '12, 17',
				'description' => esc_html__( 'Post IDs to exclude from search results.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 3. Element Order Builder (mirrors Post Block)
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

		// Per Item: Category (Badge) Settings.
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

		// Per Item: Title Settings.
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

		// Per Item: Excerpt Settings.
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

		// Per Item: Read More Settings.
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

		$element_repeater->add_control(
			'element_color',
			array(
				'label'     => esc_html__( 'Text/Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}}'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} a'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} i'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
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
				'title_field' => '<span class="es-builder-item es-item-{{{ element_type }}} es-status-{{{ show_element }}}"><span class="es-builder-dot"></span><span class="es-builder-label"><# '
					. 'var names = {'
					. '"badge": "' . esc_html__( 'Category', 'elonix' ) . '",'
					. '"title": "' . esc_html__( 'Title', 'elonix' ) . '",'
					. '"meta": "' . esc_html__( 'Meta', 'elonix' ) . '",'
					. '"excerpt": "' . esc_html__( 'Excerpt', 'elonix' ) . '",'
					. '"read_more": "' . esc_html__( 'Read More', 'elonix' ) . '",'
					. '"author": "' . esc_html__( 'Author', 'elonix' ) . '",'
					. '"date": "' . esc_html__( 'Date', 'elonix' ) . '",'
					. '"comments": "' . esc_html__( 'Comments', 'elonix' ) . '",'
					. '"reading_time": "' . esc_html__( 'Reading Time', 'elonix' ) . '",'
					. '"share": "' . esc_html__( 'Share', 'elonix' ) . '"'
					. '};'
					. '#>'
					. '{{{ names[element_type] || element_type }}}'
					. '</span></span>',
				'description' => esc_html__( 'Drag and drop rows to reorder layout blocks. Toggle show/hide to enable or disable elements.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 4. Content Section (mirrors Post Block)
		$this->start_controls_section(
			'section_content_toggles',
			array(
				'label' => esc_html__( 'Content', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// Title controls.
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

		// Excerpt controls.
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

		// Badge controls.
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

		// Meta controls.
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
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}}'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} a'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} i'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-post-block-card {{CURRENT_ITEM}} svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_elements_order',
			array(
				'label'       => esc_html__( 'Meta Blocks Order', 'elonix' ),
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

		// Smart Badges.
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

		// Read More.
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

		// 5. Icons Section (mirrors Post Block)
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

		// Featured Post settings (mirrors Post Block)
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
					'{{WRAPPER}} .es-featured-card .es-post-block-thumbnail img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				),
				'condition'  => array(
					'show_featured' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// 6. Pagination / AJAX (mirrors Post Block)
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

		// 7. Empty State (Search-Results specific)
		$this->start_controls_section(
			'section_empty_state',
			array(
				'label' => esc_html__( 'Empty State', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'empty_title',
			array(
				'label'   => esc_html__( 'Title', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Nothing Found', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_description',
			array(
				'label'   => esc_html__( 'Description', 'elonix' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( "Sorry, we couldn't find anything matching your search.", 'elonix' ),
			)
		);

		$this->add_control(
			'empty_search_button_text',
			array(
				'label'   => esc_html__( 'Search Button', 'elonix' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Search Again', 'elonix' ),
			)
		);

		$this->add_control(
			'show_home_button',
			array(
				'label'        => esc_html__( 'Return Home Button', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_support_button',
			array(
				'label'        => esc_html__( 'Support Button', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'support_url',
			array(
				'label'       => esc_html__( 'Support URL', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/support',
				'condition'   => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_suggestions',
			array(
				'label'        => esc_html__( 'Show Suggestions', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Display related categories, tags, and recent posts in the empty state.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 8. Search Statistics (Search-Results specific)
		$this->start_controls_section(
			'section_search_stats',
			array(
				'label' => esc_html__( 'Search Statistics', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_stats',
			array(
				'label'        => esc_html__( 'Show Search Statistics Bar', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$stat_toggles = array(
			'show_stat_total'       => esc_html__( 'Total Results', 'elonix' ),
			'show_stat_keyword'     => esc_html__( 'Search Keyword', 'elonix' ),
			'show_stat_time'        => esc_html__( 'Search Time', 'elonix' ),
			'show_stat_current'     => esc_html__( 'Current Page', 'elonix' ),
			'show_stat_total_pages' => esc_html__( 'Total Pages', 'elonix' ),
		);

		foreach ( $stat_toggles as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'show_stats' => 'yes',
					),
				)
			);
		}

		$this->end_controls_section();

		// 9. Editor Preview (Search-Results specific)
		$this->start_controls_section(
			'section_editor_preview',
			array(
				'label' => esc_html__( 'Editor Preview', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'preview_keyword',
			array(
				'label'       => esc_html__( 'Preview Keyword', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'e.g. WordPress', 'elonix' ),
				'description' => esc_html__( 'Use a keyword to preview search results in the Elementor editor. Leave empty to show all posts.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// ====================================================================
		// STYLE TAB — mirrors Post Block style controls 1:1
		// All selectors point at .es-post-block-* classes.
		// ====================================================================
		$this->register_style_controls();

		// Stats Bar Styles (Search-Results specific).
		$this->start_controls_section(
			'section_style_stats',
			array(
				'label'     => esc_html__( 'Statistics Bar', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_stats' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'stats_typography',
				'selector' => '{{WRAPPER}} .es-search-results-stats',
			)
		);

		$this->add_control(
			'stats_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-stats' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'stats_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-stats' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Empty State Styles (Search-Results specific).
		$this->start_controls_section(
			'section_style_empty_state',
			array(
				'label' => esc_html__( 'Empty State', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// --- Wrapper ---
		$this->add_control(
			'empty_wrapper_heading',
			array(
				'label' => esc_html__( 'Wrapper / Box', 'elonix' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'empty_align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
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
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'empty_wrapper_bg',
				'selector' => '{{WRAPPER}} .es-search-results-empty',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_wrapper_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty',
			)
		);

		$this->add_responsive_control(
			'empty_wrapper_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_wrapper_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty',
			)
		);

		$this->add_responsive_control(
			'empty_wrapper_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'empty_wrapper_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// --- Visual / Icon ---
		$this->add_control(
			'empty_visual_heading',
			array(
				'label'     => esc_html__( 'Icon / Visual', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'empty_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'empty_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'empty_visual_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-visual' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// --- Title ---
		$this->add_control(
			'empty_title_heading',
			array(
				'label'     => esc_html__( 'Title', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_title_typography',
				'selector' => '{{WRAPPER}} .es-search-results-empty-title',
			)
		);

		$this->add_control(
			'empty_title_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'empty_title_text_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-title',
			)
		);

		$this->add_responsive_control(
			'empty_title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// --- Description ---
		$this->add_control(
			'empty_desc_heading',
			array(
				'label'     => esc_html__( 'Description', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_desc_typography',
				'selector' => '{{WRAPPER}} .es-search-results-empty-desc',
			)
		);

		$this->add_control(
			'empty_desc_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'empty_desc_text_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-desc',
			)
		);

		$this->add_responsive_control(
			'empty_desc_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// --- Search Form ---
		$this->add_control(
			'empty_search_form_heading',
			array(
				'label'     => esc_html__( 'Search Form', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'empty_search_form_margin',
			array(
				'label'      => esc_html__( 'Form Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Search Input Subheading
		$this->add_control(
			'empty_search_input_subheading',
			array(
				'label' => esc_html__( 'Input Field', 'elonix' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_search_input_typography',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form input',
			)
		);

		$this->add_control(
			'empty_search_input_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form input' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_search_input_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form input' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_search_input_placeholder_color',
			array(
				'label'     => esc_html__( 'Placeholder Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_search_input_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form input',
			)
		);

		$this->add_responsive_control(
			'empty_search_input_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-form input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_search_input_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form input',
			)
		);

		$this->add_responsive_control(
			'empty_search_input_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-form input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Search Button Subheading
		$this->add_control(
			'empty_search_btn_subheading',
			array(
				'label'     => esc_html__( 'Search Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_search_btn_typography',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form button',
			)
		);

		$this->start_controls_tabs( 'tabs_empty_search_btn' );

		$this->start_controls_tab(
			'tab_empty_search_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_search_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_search_btn_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_search_btn_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form button',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_search_btn_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_empty_search_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_search_btn_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_search_btn_hover_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-form button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_search_btn_hover_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form button:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_search_btn_hover_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-form button:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'empty_search_btn_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-form button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'empty_search_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-form button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// --- Return Home Button ---
		$this->add_control(
			'empty_home_btn_heading',
			array(
				'label'     => esc_html__( 'Return Home Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_home_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'empty_home_btn_typography',
				'selector'  => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home',
				'condition' => array(
					'show_home_button' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_empty_home_btn',
			array(
				'condition' => array(
					'show_home_button' => 'yes',
				),
			)
		);

		$this->start_controls_tab(
			'tab_empty_home_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_home_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_home_btn_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_home_btn_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_home_btn_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_empty_home_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_home_btn_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_home_btn_hover_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_home_btn_hover_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_home_btn_hover_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'empty_home_btn_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_home_button' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_home_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_home_button' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_home_btn_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-home' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_home_button' => 'yes',
				),
			)
		);

		// --- Support Button ---
		$this->add_control(
			'empty_support_btn_heading',
			array(
				'label'     => esc_html__( 'Support Button', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'empty_support_btn_typography',
				'selector'  => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support',
				'condition' => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_empty_support_btn',
			array(
				'condition' => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->start_controls_tab(
			'tab_empty_support_btn_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_support_btn_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_support_btn_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_support_btn_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_support_btn_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_empty_support_btn_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_support_btn_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_support_btn_hover_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_support_btn_hover_border',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_support_btn_hover_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'empty_support_btn_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_support_btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_support_button' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_support_btn_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-empty-actions .es-search-results-support' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_support_button' => 'yes',
				),
			)
		);

		// --- Suggestions ---
		$this->add_control(
			'empty_suggestions_heading',
			array(
				'label'     => esc_html__( 'Suggestions', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_sugg_margin',
			array(
				'label'      => esc_html__( 'Wrapper Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-suggestions' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_sugg_padding',
			array(
				'label'      => esc_html__( 'Wrapper Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-suggestions' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'empty_sugg_title_typography',
				'label'     => esc_html__( 'Group Title Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .es-search-results-suggestions h3',
				'condition' => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_control(
			'empty_sugg_title_color',
			array(
				'label'     => esc_html__( 'Group Title Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-suggestions h3' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_sugg_title_margin',
			array(
				'label'      => esc_html__( 'Group Title Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-suggestions h3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'empty_sugg_link_typography',
				'label'     => esc_html__( 'Link Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .es-search-results-suggestions li a',
				'condition' => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_empty_sugg_links',
			array(
				'condition' => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->start_controls_tab(
			'tab_empty_sugg_link_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_sugg_link_color',
			array(
				'label'     => esc_html__( 'Link Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-suggestions li a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_sugg_link_bg',
			array(
				'label'     => esc_html__( 'Link Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-suggestions li a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_sugg_link_border',
				'selector' => '{{WRAPPER}} .es-search-results-suggestions li a',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_sugg_link_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-suggestions li a',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_empty_sugg_link_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'empty_sugg_link_hover_color',
			array(
				'label'     => esc_html__( 'Link Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-suggestions li a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'empty_sugg_link_hover_bg',
			array(
				'label'     => esc_html__( 'Link Hover Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-search-results-suggestions li a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'empty_sugg_link_hover_border',
				'selector' => '{{WRAPPER}} .es-search-results-suggestions li a:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'empty_sugg_link_hover_box_shadow',
				'selector' => '{{WRAPPER}} .es-search-results-suggestions li a:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'empty_sugg_link_border_radius',
			array(
				'label'      => esc_html__( 'Link Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-suggestions li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'empty_sugg_link_padding',
			array(
				'label'      => esc_html__( 'Link Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-search-results-suggestions li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_suggestions' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Render
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Render the widget.
	 *
	 * Builds a search-sourced query using Elonix_Toolkit_Search_Results_Query_Helper,
	 * then delegates card rendering to Elonix_Toolkit_Post_Block_Renderer::render_single_post()
	 * so the HTML output is 1:1 with the Post Block Grid widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// ── Build AJAX settings payload (mirrors Post Block render()).
		$ajax_settings = array();
		$keys_to_pass  = array(
			'post_types',
			'limit',
			'thumbnail_size',
			'orderby',
			'order',
			'date_filter',
			'exclude_ids',
			'layout',
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
			'badge_icon',
			'read_more_icon',
			'meta_author_icon',
			'meta_date_icon',
			'meta_comments_icon',
			'meta_reading_time_icon',
			'meta_views_icon',
			'share_icon',
			'show_featured',
			'featured_mode',
			'featured_manual_id',
			'ajax_tabs',
			'ajax_search',
			// Search-Results specific:
			'show_stats',
			'show_stat_total',
			'show_stat_keyword',
			'show_stat_time',
			'show_stat_current',
			'show_stat_total_pages',
			'empty_title',
			'empty_description',
			'empty_search_button_text',
			'show_home_button',
		);

		foreach ( $keys_to_pass as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$ajax_settings[ $key ] = $settings[ $key ];
			}
		}

		// ── Build the search query.
		$search_keyword = Elonix_Toolkit_Search_Results_Query_Helper::get_search_keyword( $settings );

		// In the Elementor editor, fall back to the preview keyword if no live search is active.
		if ( empty( $search_keyword ) && ! empty( $settings['preview_keyword'] ) ) {
			$search_keyword = sanitize_text_field( $settings['preview_keyword'] );
		}

		// Build query args.
		$post_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post', 'page' );
		$post_types = Elonix_Toolkit_Search_Results_Query_Helper::sanitize_post_types_public( $post_types );

		$limit   = ! empty( $settings['limit'] ) ? max( 1, min( 100, absint( $settings['limit'] ) ) ) : 9;
		$orderby = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'relevance';
		$order   = ! empty( $settings['order'] ) ? strtoupper( sanitize_key( $settings['order'] ) ) : 'DESC';

		// Allowed orderby values.
		$allowed_orderby = array( 'relevance', 'date', 'title', 'modified', 'comment_count', 'rand' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'relevance';
		}
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$query_args = array(
			's'                   => $search_keyword,
			'post_type'           => $post_types,
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'paged'               => max( 1, get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : ( get_query_var( 'page' ) ? absint( get_query_var( 'page' ) ) : 1 ) ),
			'orderby'             => $orderby,
			'order'               => $order,
			'ignore_sticky_posts' => true,
		);

		// Date filter.
		if ( ! empty( $settings['date_filter'] ) && 'none' !== $settings['date_filter'] ) {
			$date_map = array(
				'today' => '1 day ago',
				'week'  => '1 week ago',
				'month' => '1 month ago',
			);
			if ( isset( $date_map[ $settings['date_filter'] ] ) ) {
				$query_args['date_query'] = array(
					array( 'after' => $date_map[ $settings['date_filter'] ] ),
				);
			}
		}

		// Exclude IDs.
		if ( ! empty( $settings['exclude_ids'] ) ) {
			$exclude_ids = array_filter( array_map( 'absint', explode( ',', $settings['exclude_ids'] ) ) );
			if ( ! empty( $exclude_ids ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required for Elementor Search Results duplicate prevention and exclusion controls.
				$query_args['post__not_in'] = $exclude_ids;
			}
		}

		$query_args = apply_filters( 'elonix_es_search_results_query_args', $query_args, $settings );

		// ── Run Query.
		$query      = new WP_Query( $query_args );
		$posts_data = array();
		$start_time = microtime( true );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;
				$posts_data[] = Elonix_Toolkit_Post_Block_Query_Helper::format_post_data( $post, $settings );
			}
			wp_reset_postdata();
		}

		$elapsed   = max( 0.001, microtime( true ) - $start_time );
		$max_pages = max( 1, (int) $query->max_num_pages );
		$paged     = max( 1, (int) $query_args['paged'] );

		// Add search-specific fields to AJAX settings payload.
		$ajax_settings['s']     = $search_keyword;
		$ajax_settings['paged'] = $paged;

		// ── Layout & pagination.
		$layout     = ! empty( $settings['layout'] ) ? sanitize_key( $settings['layout'] ) : 'style_1';
		$pagination = ! empty( $settings['pagination_type'] ) ? sanitize_key( $settings['pagination_type'] ) : 'none';
		$nonce      = wp_create_nonce( 'es-search-results-nonce' );
		?>
		<?php
		// Render search stats bar (Search-Results specific chrome).
		$this->render_stats_bar(
			array(
				'found_posts' => (int) $query->found_posts,
				'keyword'     => $search_keyword,
				'paged'       => $paged,
				'max_pages'   => $max_pages,
				'elapsed'     => $elapsed,
			),
			$settings
		);
		?>

		<div id="es-post-block-<?php echo esc_attr( $this->get_id() ); ?>"
			class="es-post-block-wrap es-posts-pagination-<?php echo esc_attr( $pagination ); ?> es-search-results-wrap"
			data-settings="<?php echo esc_attr( wp_json_encode( $ajax_settings ) ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-max-pages="<?php echo esc_attr( $max_pages ); ?>"
			data-action="elonix_search_results_fetch"
			data-i18n="
			<?php
				echo esc_attr(
					wp_json_encode(
						array(
							'prev'     => __( 'Prev', 'elonix' ),
							'next'     => __( 'Next', 'elonix' ),
							/* translators: %d: string */
							'page'     => __( 'Go to page %d', 'elonix' ),
							'loading'  => __( 'Loading results...', 'elonix' ),
							'loaded'   => __( 'Results loaded.', 'elonix' ),
							'no_posts' => __( 'No results found.', 'elonix' ),
						)
					)
				);
			?>
						"
		>
			<!-- WCAG aria-live announcer -->
			<div class="es-sr-announcer screen-reader-text sr-only" aria-live="polite" aria-atomic="true" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;"></div>

			<?php
			// Render Filter Tabs / Search bar (mirrors Post Block)
			if ( 'none' !== $settings['ajax_tabs'] || 'yes' === $settings['ajax_search'] ) :
				?>
				<div class="es-post-block-filters-bar">
					<?php if ( 'none' !== $settings['ajax_tabs'] ) : ?>
						<div class="es-post-block-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Archive filters', 'elonix' ); ?>">
							<button class="es-filter-tab active" role="tab" aria-selected="true" aria-controls="es-post-panel-<?php echo esc_attr( $this->get_id() ); ?>" id="es-filter-tab-0-<?php echo esc_attr( $this->get_id() ); ?>" data-filter-id="0">
								<?php esc_html_e( 'All', 'elonix' ); ?>
							</button>
							<?php
							if ( 'author' === $settings['ajax_tabs'] ) {
								$post_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post', 'page' );
								$authors    = get_users(
									array(
										'orderby' => 'post_count',
										'order'   => 'DESC',
										'number'  => 8,
										'has_published_posts' => $post_types,
									)
								);
								if ( ! empty( $authors ) && ! is_wp_error( $authors ) ) {
									foreach ( $authors as $author ) {
										printf(
											'<button class="es-filter-tab" role="tab" aria-selected="false" aria-controls="es-post-panel-%s" id="es-filter-tab-%d-%s" data-filter-id="%d">%s</button>',
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
											'<button class="es-filter-tab" role="tab" aria-selected="false" aria-controls="es-post-panel-%s" id="es-filter-tab-%d-%s" data-filter-id="%d">%s</button>',
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
						<div class="es-post-block-search-wrap" role="search">
							<input type="search" class="es-post-block-search-input" placeholder="<?php esc_attr_e( 'Search posts...', 'elonix' ); ?>" aria-label="<?php esc_attr_e( 'Search posts list', 'elonix' ); ?>">
							<span class="es-search-icon">
								<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php
			$grid_classes = array( 'es-post-block-container', 'es-layout-' . $layout );
			if ( in_array( $layout, array( 'style_2', 'style_5' ), true ) ) {
				$grid_classes[] = 'es-post-block-grid';
			}
			?>
			<div id="es-post-panel-<?php echo esc_attr( $this->get_id() ); ?>"
				class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>"
				aria-live="polite" aria-busy="false"
			>
				<?php
				if ( ! empty( $posts_data ) ) {
					$post_index  = 0;
					$has_wrapper = false;
					foreach ( $posts_data as $item ) {
						if ( 'style_3' === $layout && 1 === $post_index ) {
							echo '<div class="es-grid-secondary-wrapper">';
							$has_wrapper = true;
						}
						Elonix_Toolkit_Post_Block_Renderer::render_single_post( $item, $settings, $layout, $post_index );
						++$post_index;
					}
					if ( $has_wrapper ) {
						echo '</div>';
					}
				} else {
					// Empty State (Search-Results specific).
					Elonix_Toolkit_Search_Results_Empty_State_Helper::render( $settings, $search_keyword );
				}
				?>
			</div>

			<?php
			// Pagination — identical to Post Block.
			if ( 'none' !== $pagination && ! empty( $posts_data ) ) :
				$pag_display_style = ( $max_pages > 1 ) ? '' : 'display: none;';
				?>
				<div class="es-post-block-pagination" style="<?php echo esc_attr( $pag_display_style ); ?>">
					<?php if ( 'load_more' === $pagination ) : ?>
						<button class="es-post-block-load-more es-btn" <?php echo ( $max_pages <= 1 ) ? 'disabled aria-disabled="true"' : ''; ?>>
							<span class="es-btn-text"><?php echo esc_html( ! empty( $settings['pagination_text'] ) ? $settings['pagination_text'] : __( 'Load More', 'elonix' ) ); ?></span>
							<span class="es-btn-spinner" style="display:none;">
								<svg class="es-spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle></svg>
							</span>
						</button>
					<?php elseif ( 'infinite_scroll' === $pagination ) : ?>
						<div class="es-post-block-scroll-trigger">
							<span class="es-scroll-spinner">
								<svg class="es-spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke="currentColor"></circle></svg>
							</span>
						</div>
					<?php elseif ( 'ajax_numeric' === $pagination ) : ?>
						<nav class="es-post-block-pagination-nav" aria-label="<?php esc_attr_e( 'Pagination navigation', 'elonix' ); ?>">
							<button class="es-page-navprev disabled" disabled aria-disabled="true" data-paged="0" aria-label="<?php esc_attr_e( 'Previous page', 'elonix' ); ?>">
								<?php esc_html_e( 'Prev', 'elonix' ); ?>
							</button>
							<?php
							for ( $i = 1; $i <= $max_pages; $i++ ) {
								$active_class = ( 1 === $i ) ? 'active' : '';
								$aria_current = ( 1 === $i ) ? 'aria-current="page"' : '';
								$btn_disabled = ( 1 === $i ) ? 'disabled' : '';
								printf(
									'<button class="es-page-num %s" %s %s data-paged="%d" aria-label="%s">%d</button>',
									esc_attr( $active_class ),
									$aria_current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed static string literal, no dynamic content.
									esc_attr( $btn_disabled ),
									intval( $i ),
									/* translators: %d: string */
									esc_attr( sprintf( __( 'Go to page %d', 'elonix' ), $i ) ),
									intval( $i )
								);
							}
							?>
							<button class="es-page-navnext" <?php echo ( $max_pages <= 1 ) ? 'disabled aria-disabled="true"' : ''; ?> data-paged="2" aria-label="<?php esc_attr_e( 'Next page', 'elonix' ); ?>">
								<?php esc_html_e( 'Next', 'elonix' ); ?>
							</button>
						</nav>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Search-specific chrome: Stats Bar
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Render the search statistics bar.
	 *
	 * @param array $data     Query result meta (found_posts, keyword, paged, max_pages, elapsed).
	 * @param array $settings Widget settings.
	 */
	private function render_stats_bar( $data, $settings ) {
		if ( empty( $settings['show_stats'] ) || 'yes' !== $settings['show_stats'] ) {
			return;
		}

		$keyword          = ! empty( $data['keyword'] ) ? $data['keyword'] : '';
		$show_total       = ! isset( $settings['show_stat_total'] ) || 'yes' === $settings['show_stat_total'];
		$show_keyword     = ! isset( $settings['show_stat_keyword'] ) || 'yes' === $settings['show_stat_keyword'];
		$show_time        = ! isset( $settings['show_stat_time'] ) || 'yes' === $settings['show_stat_time'];
		$show_current     = ! isset( $settings['show_stat_current'] ) || 'yes' === $settings['show_stat_current'];
		$show_total_pages = ! isset( $settings['show_stat_total_pages'] ) || 'yes' === $settings['show_stat_total_pages'];
		?>
		<header class="es-search-results-stats">
			<div>
				<?php if ( $show_total ) : ?>
					<strong>
						<?php
						printf(
							/* translators: %d: Number of found search results. */
							esc_html( _n( '%d Result Found', '%d Results Found', intval( $data['found_posts'] ), 'elonix' ) ),
							intval( $data['found_posts'] )
						);
						?>
					</strong>
				<?php endif; ?>
				<?php if ( $show_keyword && '' !== $keyword ) : ?>
					<span><?php esc_html_e( 'Showing results for', 'elonix' ); ?> <em>"<?php echo esc_html( $keyword ); ?>"</em></span>
				<?php endif; ?>
			</div>
			<ul aria-label="<?php esc_attr_e( 'Search statistics', 'elonix' ); ?>">
				<?php if ( $show_current || $show_total_pages ) : ?>
					<li>
						<?php
						if ( $show_current && $show_total_pages ) {
							/* translators: 1: Current page number, 2: Total number of pages. */
							printf( esc_html__( 'Page %1$d of %2$d', 'elonix' ), intval( $data['paged'] ), (int) max( 1, intval( $data['max_pages'] ) ) );
						} elseif ( $show_current ) {
							/* translators: %d: Current page number. */
							printf( esc_html__( 'Page %d', 'elonix' ), intval( $data['paged'] ) );
						} else {
							/* translators: %d: Total number of pages. */
							printf( esc_html__( '%d Pages', 'elonix' ), (int) max( 1, intval( $data['max_pages'] ) ) );
						}
						?>
					</li>
				<?php endif; ?>
				<?php if ( $show_time ) : ?>
					<li>
						<?php
						/* translators: %s: Query elapsed time in seconds. */
						printf( esc_html__( '%s sec', 'elonix' ), esc_html( number_format_i18n( $data['elapsed'], 3 ) ) );
						?>
					</li>
				<?php endif; ?>
			</ul>
		</header>
		<?php
	}

	// ──────────────────────────────────────────────────────────────────────────
	// AJAX endpoint
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Register AJAX hooks.
	 */
	public static function register_ajax_hooks() {
		add_action( 'wp_ajax_elonix_search_results_fetch', array( __CLASS__, 'ajax_fetch_results' ) );
		add_action( 'wp_ajax_nopriv_elonix_search_results_fetch', array( __CLASS__, 'ajax_fetch_results' ) );
	}

	/**
	 * AJAX handler for Load More / Infinite Scroll / Numeric Pagination.
	 *
	 * Mirrors handler-ajax.php of Post Block but sources posts from a search query.
	 */
	public static function ajax_fetch_results() {
		check_ajax_referer( 'es-search-results-nonce', 'security' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passed through sanitize_settings() on the next line, which recursively sanitizes every key/value; post_status is separately whitelisted via sanitize_post_status().
		$settings_raw = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		$settings     = Elonix_Toolkit_Search_Results_Query_Helper::sanitize_settings( $settings_raw );

		$paged       = isset( $_POST['paged'] ) ? max( 1, absint( wp_unslash( $_POST['paged'] ) ) ) : 1;
		$keyword_raw = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$search_raw  = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$post_type   = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		$date_filter = isset( $_POST['date_filter'] ) ? sanitize_key( wp_unslash( $_POST['date_filter'] ) ) : '';

		$settings['paged'] = $paged;
		if ( '' !== $search_raw ) {
			$settings['search_keyword'] = $search_raw;
		} elseif ( '' !== $keyword_raw ) {
			$settings['search_keyword'] = $keyword_raw;
		}
		if ( '' !== $post_type ) {
			$settings['post_types'] = array( $post_type );
		}
		if ( '' !== $date_filter ) {
			$settings['date_filter'] = $date_filter;
		}

		// Ensure helpers and renderer are loaded.
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Query_Helper' ) ) {
			require_once dirname( __DIR__ ) . '/es-post-block/helper-query.php';
		}
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Renderer' ) ) {
			require_once dirname( __DIR__ ) . '/es-post-block/class-renderer.php';
		}
		if ( ! class_exists( 'Elonix_Toolkit_Search_Results_Empty_State_Helper' ) ) {
			require_once __DIR__ . '/helper-empty-state.php';
		}

		$keyword = Elonix_Toolkit_Search_Results_Query_Helper::get_search_keyword( $settings );

		// Build post_types.
		$post_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post', 'page' );
		$post_types = Elonix_Toolkit_Search_Results_Query_Helper::sanitize_post_types_public( $post_types );

		$limit   = ! empty( $settings['limit'] ) ? max( 1, min( 100, absint( $settings['limit'] ) ) ) : 9;
		$orderby = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'relevance';
		$order   = ! empty( $settings['order'] ) ? strtoupper( sanitize_key( $settings['order'] ) ) : 'DESC';

		$allowed_orderby = array( 'relevance', 'date', 'title', 'modified', 'comment_count', 'rand' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'relevance';
		}
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$query_args = array(
			's'                   => $keyword,
			'post_type'           => $post_types,
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'paged'               => $paged,
			'orderby'             => $orderby,
			'order'               => $order,
			'ignore_sticky_posts' => true,
		);

		// Apply AJAX Filter Tabs overrides (category, tag, author).
		if ( isset( $_POST['category'] ) && intval( wp_unslash( $_POST['category'] ) ) > 0 ) {
			$query_args['cat'] = intval( wp_unslash( $_POST['category'] ) );
		}
		if ( isset( $_POST['tag'] ) && intval( wp_unslash( $_POST['tag'] ) ) > 0 ) {
			$query_args['tag_id'] = intval( wp_unslash( $_POST['tag'] ) );
		}
		if ( isset( $_POST['author'] ) && intval( wp_unslash( $_POST['author'] ) ) > 0 ) {
			$query_args['author'] = intval( wp_unslash( $_POST['author'] ) );
		}

		if ( ! empty( $settings['date_filter'] ) && 'none' !== $settings['date_filter'] ) {
			$date_map = array(
				'today' => '1 day ago',
				'week'  => '1 week ago',
				'month' => '1 month ago',
			);
			if ( isset( $date_map[ $settings['date_filter'] ] ) ) {
				$query_args['date_query'] = array(
					array( 'after' => $date_map[ $settings['date_filter'] ] ),
				);
			}
		}

		if ( ! empty( $settings['exclude_ids'] ) ) {
			$exclude_ids = array_filter( array_map( 'absint', explode( ',', $settings['exclude_ids'] ) ) );
			if ( ! empty( $exclude_ids ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required for Elementor Search Results duplicate prevention and exclusion controls.
				$query_args['post__not_in'] = $exclude_ids;
			}
		}

		$query      = new WP_Query( $query_args );
		$posts_data = array();
		$start_time = microtime( true );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;
				$posts_data[] = Elonix_Toolkit_Post_Block_Query_Helper::format_post_data( $post, $settings );
			}
			wp_reset_postdata();
		}

		$elapsed   = max( 0.001, microtime( true ) - $start_time );
		$max_pages = max( 1, (int) $query->max_num_pages );
		$layout    = ! empty( $settings['layout'] ) ? sanitize_key( $settings['layout'] ) : 'style_1';

		ob_start();
		if ( ! empty( $posts_data ) ) {
			$post_index  = 0;
			$has_wrapper = false;
			foreach ( $posts_data as $item ) {
				if ( 'style_3' === $layout && 1 === $post_index ) {
					echo '<div class="es-grid-secondary-wrapper">';
					$has_wrapper = true;
				}
				Elonix_Toolkit_Post_Block_Renderer::render_single_post( $item, $settings, $layout, $post_index );
				++$post_index;
			}
			if ( $has_wrapper ) {
				echo '</div>';
			}
		} else {
			Elonix_Toolkit_Search_Results_Empty_State_Helper::render( $settings, $keyword );
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'          => $html,
				'max_num_pages' => $max_pages,
				'paged'         => $paged,
				'found_posts'   => (int) $query->found_posts,
				'keyword'       => esc_html( $keyword ),
				'elapsed'       => $elapsed,
			)
		);
	}
}
