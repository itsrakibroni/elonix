<?php
/**
 * Elonix Categories Widget Class
 *
 * A premium categories and taxonomy grid list widget supporting lists,
 * grids, carousels, masonry grids, glassmorphism cards, and accordion category trees.
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

// Include the helper taxonomy engine
if ( ! class_exists( 'Elonix_Toolkit_Taxonomy_Helper' ) ) {
	require_once __DIR__ . '/helper-taxonomy.php';
}

class Elonix_Toolkit_Categories_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-categories';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Categories', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-folder-o';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'categories', 'terms', 'taxonomies', 'grid', 'carousel', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-categories', 'swiper' );
	}

	/**
	 * Retrieve widget script dependency list (conditionally enqueues Elementor Swiper).
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-categories', 'swiper' );
	}

	/**
	 * Helper: Retrieve all public taxonomies dynamically.
	 *
	 * @return array Taxonomies option dictionary.
	 */
	protected function get_taxonomies_options() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		$options    = array();
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, array( 'nav_menu', 'link_category' ), true ) ) {
				continue;
			}
			$options[ $taxonomy->name ] = sprintf( '%s (%s)', $taxonomy->label, $taxonomy->name );
		}
		return $options;
	}

	/**
	 * Register Categories widget controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		// 1. Layout Options Section
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
				'label'   => esc_html__( 'Layout Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'classic'         => esc_html__( 'Simple List', 'elonix' ),
					'icon_list'       => esc_html__( 'Icon List', 'elonix' ),
					'grid'            => esc_html__( 'Grid', 'elonix' ),
					'card_grid'       => esc_html__( 'Card Grid', 'elonix' ),
					'masonry'         => esc_html__( 'Masonry Grid', 'elonix' ),
					'overlay'         => esc_html__( 'Overlay Card', 'elonix' ),
					'background_card' => esc_html__( 'Background Image Card', 'elonix' ),
					'carousel'        => esc_html__( 'Horizontal Carousel', 'elonix' ),
					'glass_card'      => esc_html__( 'Modern Glass Card', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'taxonomy',
			array(
				'label'   => esc_html__( 'Select Taxonomy', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'category',
				'options' => $this->get_taxonomies_options(),
			)
		);

		$this->end_controls_section();

		// 2. Query Controls Section
		$this->start_controls_section(
			'section_query',
			array(
				'label' => esc_html__( 'Query Controls', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'selection_mode',
			array(
				'label'   => esc_html__( 'Selection Mode', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'      => esc_html__( 'All Terms', 'elonix' ),
					'featured' => esc_html__( 'Featured Categories (Meta Flag)', 'elonix' ),
					'popular'  => esc_html__( 'Popular Categories (Post Count)', 'elonix' ),
					'recent'   => esc_html__( 'Recent Categories (By ID)', 'elonix' ),
					'manual'   => esc_html__( 'Manual Selection', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'manual_ids',
			array(
				'label'       => esc_html__( 'Manual Term IDs', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter comma-separated Term IDs (e.g. 12, 15, 23).', 'elonix' ),
				'condition'   => array(
					'selection_mode' => 'manual',
				),
			)
		);

		$this->add_control(
			'include_ids',
			array(
				'label'       => esc_html__( 'Include IDs', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Only display these specific Term IDs (comma-separated).', 'elonix' ),
				'condition'   => array(
					'selection_mode!' => 'manual',
				),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => esc_html__( 'Exclude IDs', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Hide these Term IDs (comma-separated).', 'elonix' ),
				'condition'   => array(
					'selection_mode!' => 'manual',
				),
			)
		);

		$this->add_control(
			'parent_only',
			array(
				'label'        => esc_html__( 'Parent Only', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'child_only!' => 'yes',
				),
			)
		);

		$this->add_control(
			'child_only',
			array(
				'label'        => esc_html__( 'Child Only', 'elonix' ),
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
				'label'        => esc_html__( 'Hide Empty Categories', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
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
					'slug'  => esc_html__( 'Slug', 'elonix' ),
					'count' => esc_html__( 'Count', 'elonix' ),
					'id'    => esc_html__( 'ID', 'elonix' ),
				),
				'condition' => array(
					'selection_mode' => array( 'all', 'manual' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ASC',
				'options'   => array(
					'ASC'  => esc_html__( 'Ascending', 'elonix' ),
					'DESC' => esc_html__( 'Descending', 'elonix' ),
				),
				'condition' => array(
					'selection_mode' => array( 'all', 'manual' ),
				),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Limit', 'elonix' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 8,
			)
		);

		$this->end_controls_section();

		// 3. Category Content Option Section
		$this->start_controls_section(
			'section_content_toggles',
			array(
				'label' => esc_html__( 'Category Content', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title / Name', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => esc_html__( 'Show Image', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'layout!' => array( 'classic', 'icon_list' ),
				),
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => esc_html__( 'Show Category Icon', 'elonix' ),
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
				'default'   => 'dynamic_term',
				'options'   => array(
					'icon_library' => esc_html__( 'Icon Library', 'elonix' ),
					'svg_upload'   => esc_html__( 'SVG Upload', 'elonix' ),
					'image_upload' => esc_html__( 'Image Upload', 'elonix' ),
					'dynamic_term' => esc_html__( 'Dynamic Term Icon', 'elonix' ),
					'woo_icon'     => esc_html__( 'Woo Category Icon', 'elonix' ),
				),
				'condition' => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_icon_library',
			array(
				'label'     => esc_html__( 'Icon Library Picker', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => array(
					'show_icon'       => 'yes',
					'cat_icon_source' => 'icon_library',
				),
			)
		);

		$this->add_control(
			'cat_icon_svg',
			array(
				'label'     => esc_html__( 'Upload SVG Icon', 'elonix' ),
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
				'label'     => esc_html__( 'Upload Image Icon', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'show_icon'       => 'yes',
					'cat_icon_source' => 'image_upload',
				),
			)
		);

		$this->add_control(
			'category_icon_position',
			array(
				'label'     => esc_html__( 'Category Icon Position', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Show Before Title', 'elonix' ),
					'after'  => esc_html__( 'Show After Title', 'elonix' ),
					'above'  => esc_html__( 'Show Above Title', 'elonix' ),
				),
				'condition' => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_description',
			array(
				'label'        => esc_html__( 'Show Description', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_parent',
			array(
				'label'        => esc_html__( 'Show Parent Category', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
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
			'nested_categories',
			array(
				'label'        => esc_html__( 'Show Nested Children', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'nested_style',
			array(
				'label'     => esc_html__( 'Hierarchy Pattern', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'tree',
				'options'   => array(
					'tree'      => esc_html__( 'Category Tree (Static List)', 'elonix' ),
					'accordion' => esc_html__( 'Accordion (Expand/Collapse)', 'elonix' ),
				),
				'condition' => array(
					'nested_categories' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_toggle_icon',
			array(
				'label'        => esc_html__( 'Show Toggle Icon', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
				),
			)
		);

		$this->add_control(
			'icon_source',
			array(
				'label'     => esc_html__( 'Toggle Icon Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'chevron',
				'options'   => array(
					'chevron'    => esc_html__( 'Chevron', 'elonix' ),
					'caret'      => esc_html__( 'Caret', 'elonix' ),
					'plus_minus' => esc_html__( 'Plus / Minus', 'elonix' ),
					'arrow'      => esc_html__( 'Arrow', 'elonix' ),
					'custom'     => esc_html__( 'Elementor Icons Control', 'elonix' ),
					'svg'        => esc_html__( 'SVG Upload', 'elonix' ),
				),
				'condition' => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
					'show_toggle_icon'  => 'yes',
				),
			)
		);

		$this->add_control(
			'collapsed_icon',
			array(
				'label'     => esc_html__( 'Collapsed Icon', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-chevron-right',
					'library' => 'solid',
				),
				'condition' => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
					'show_toggle_icon'  => 'yes',
					'icon_source'       => 'custom',
				),
			)
		);

		$this->add_control(
			'expanded_icon',
			array(
				'label'     => esc_html__( 'Expanded Icon', 'elonix' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-chevron-down',
					'library' => 'solid',
				),
				'condition' => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
					'show_toggle_icon'  => 'yes',
					'icon_source'       => 'custom',
				),
			)
		);

		$this->add_control(
			'collapsed_svg',
			array(
				'label'     => esc_html__( 'Collapsed SVG', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
					'show_toggle_icon'  => 'yes',
					'icon_source'       => 'svg',
				),
			)
		);

		$this->add_control(
			'expanded_svg',
			array(
				'label'     => esc_html__( 'Expanded SVG', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'nested_categories' => 'yes',
					'nested_style'      => 'accordion',
					'show_toggle_icon'  => 'yes',
					'icon_source'       => 'svg',
				),
			)
		);

		$this->add_control(
			'show_read_more',
			array(
				'label'        => esc_html__( 'Show Read More', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'     => esc_html__( 'Read More Label', 'elonix' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Category', 'elonix' ),
				'condition' => array(
					'show_read_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label' => esc_html__( 'Fallback Image', 'elonix' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'custom_badge',
			array(
				'label'       => esc_html__( 'Badge Text Highlight', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. New, Hot, Sale', 'elonix' ),
				'description' => esc_html__( 'Leave empty to disable the badge.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// 4. Overrides Settings Section
		$this->start_controls_section(
			'section_overrides',
			array(
				'label' => esc_html__( 'Custom Overrides', 'elonix' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'term_id',
			array(
				'label'       => esc_html__( 'Term ID to Override', 'elonix' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => esc_html__( 'Specify the term ID you want to override settings for.', 'elonix' ),
			)
		);

		$repeater->add_control(
			'override_title',
			array(
				'label'       => esc_html__( 'Custom Name', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Override category name', 'elonix' ),
			)
		);

		$repeater->add_control(
			'override_image',
			array(
				'label' => esc_html__( 'Custom Image', 'elonix' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'override_icon',
			array(
				'label' => esc_html__( 'Custom Icon', 'elonix' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$repeater->add_control(
			'override_svg',
			array(
				'label' => esc_html__( 'Custom SVG Icon', 'elonix' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'override_image_icon',
			array(
				'label' => esc_html__( 'Custom Image Icon', 'elonix' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'override_badge',
			array(
				'label'       => esc_html__( 'Custom Badge', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. Popular, 10% Off', 'elonix' ),
			)
		);

		$this->add_control(
			'overrides_list',
			array(
				'label'       => esc_html__( 'Custom Term Overrides', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ term_id }}} - {{{ override_title }}}',
			)
		);

		$this->end_controls_section();

		// 5. Carousel Settings Section
		$this->start_controls_section(
			'section_carousel_settings',
			array(
				'label'     => esc_html__( 'Carousel Settings', 'elonix' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_responsive_control(
			'slides_per_view',
			array(
				'label'   => esc_html__( 'Slides Per View', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
			)
		);

		$this->add_control(
			'carousel_autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'carousel_loop',
			array(
				'label'        => esc_html__( 'Infinite Loop', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'carousel_center',
			array(
				'label'        => esc_html__( 'Center Slide Mode', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'carousel_nav',
			array(
				'label'   => esc_html__( 'Navigation Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => array(
					'standard' => esc_html__( 'Standard Arrows', 'elonix' ),
					'circle'   => esc_html__( 'Circle Arrows', 'elonix' ),
					'square'   => esc_html__( 'Square Arrows', 'elonix' ),
					'minimal'  => esc_html__( 'Minimal Arrows', 'elonix' ),
					'none'     => esc_html__( 'None', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'carousel_pag',
			array(
				'label'   => esc_html__( 'Pagination Style', 'elonix' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bullets',
				'options' => array(
					'bullets'  => esc_html__( 'Bullets', 'elonix' ),
					'fraction' => esc_html__( 'Fraction (1/3)', 'elonix' ),
					'progress' => esc_html__( 'Progress Bar', 'elonix' ),
					'numbers'  => esc_html__( 'Numbers', 'elonix' ),
					'none'     => esc_html__( 'None', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// 1. Card Container Style Section
		$this->start_controls_section(
			'section_style_container',
			array(
				'label' => esc_html__( 'Card Container', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Grid Columns', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => array(
					'1' => '1 Column',
					'2' => '2 Columns',
					'3' => '3 Columns',
					'4' => '4 Columns',
					'5' => '5 Columns',
				),
				'selectors' => array(
					'{{WRAPPER}} .es-categories-grid-wrapper' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
				'condition' => array(
					'layout' => array( 'grid', 'card_grid', 'overlay', 'background_card', 'glass_card' ),
				),
			)
		);

		$this->add_responsive_control(
			'card_height',
			array(
				'label'      => esc_html__( 'Card Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'vh' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 600,
						'step' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-card' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => array( 'classic', 'icon_list' ),
				),
			)
		);

		$this->add_responsive_control(
			'card_gap',
			array(
				'label'      => esc_html__( 'Gaps / Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-categories-grid-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-cat-card-classic' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'     => esc_html__( 'Card Hover Effect', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'lift',
				'options'   => array(
					'none'             => esc_html__( 'None', 'elonix' ),
					'lift'             => esc_html__( 'Lift Card', 'elonix' ),
					'zoom'             => esc_html__( 'Zoom Image', 'elonix' ),
					'scale'            => esc_html__( 'Scale Card', 'elonix' ),
					'blur'             => esc_html__( 'Blur Content', 'elonix' ),
					'overlay_fade'     => esc_html__( 'Overlay Fade', 'elonix' ),
					'gradient_overlay' => esc_html__( 'Gradient Overlay Shift', 'elonix' ),
				),
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'tabs_container_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_container_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-cat-card, {{WRAPPER}} .es-cat-card-classic',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-card, {{WRAPPER}} .es-cat-card-classic',
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-card'         => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .es-cat-card-classic' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .es-cat-card, {{WRAPPER}} .es-cat-card-classic',
			)
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_container_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background_hover',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-cat-card:hover, {{WRAPPER}} .es-cat-card-classic:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border_hover',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-card:hover, {{WRAPPER}} .es-cat-card-classic:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow_hover',
				'selector' => '{{WRAPPER}} .es-cat-card:hover, {{WRAPPER}} .es-cat-card-classic:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-card'         => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .es-cat-card-classic' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		// 2. Image Style Section
		$this->start_controls_section(
			'section_style_media',
			array(
				'label' => esc_html__( 'Thumbnail Image', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_size',
			array(
				'label'      => esc_html__( 'Image Size (Width)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 20,
						'max'  => 400,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-image-wrapper' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-image-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'media_overlay_color',
			array(
				'label'     => esc_html__( 'Hover Overlay Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-card-overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_hover_zoom',
			array(
				'label'      => esc_html__( 'Hover Zoom Scale', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 2,
						'step' => 0.05,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-image-wrapper img' => '--es-image-hover-zoom: {{SIZE}};',
				),
			)
		);

		$this->add_control(
			'image_hover_brightness',
			array(
				'label'      => esc_html__( 'Hover Brightness (%)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-image-wrapper img' => '--es-image-hover-brightness: {{SIZE}}%;',
				),
			)
		);

		$this->add_control(
			'image_hover_contrast',
			array(
				'label'      => esc_html__( 'Hover Contrast (%)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-image-wrapper img' => '--es-image-hover-contrast: {{SIZE}}%;',
				),
			)
		);

		$this->end_controls_section();

		// 2b. Premium Icon Style Section
		$this->start_controls_section(
			'section_style_icons',
			array(
				'label' => esc_html__( 'Category Icon', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min'  => 8,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon i' => 'font-size: {{SIZE}}{{UNIT}}; line-height: 1;',
					'{{WRAPPER}} .es-cat-icon svg, {{WRAPPER}} .es-cat-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: 1;',
				),
			)
		);

		$this->add_responsive_control(
			'icon_width',
			array(
				'label'      => esc_html__( 'Icon Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_height',
			array(
				'label'      => esc_html__( 'Icon Height', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 200,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'      => esc_html__( 'Icon Spacing / Gap', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-cat-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .es-cat-icon-above'  => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_alignment',
			array(
				'label'     => esc_html__( 'Icon Vert. Alignment', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center',
				'options'   => array(
					'center'     => esc_html__( 'Middle / Center', 'elonix' ),
					'flex-start' => esc_html__( 'Top', 'elonix' ),
					'flex-end'   => esc_html__( 'Bottom', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-cat-icon' => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_icon_style' );

		// Normal State Tab
		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-icon i'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-cat-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-icon',
			)
		);

		$this->add_control(
			'icon_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-cat-icon' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover State Tab
		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-item:hover .es-cat-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-cat-item:hover .es-cat-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color (Hover)', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-item:hover .es-cat-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'icon_border_hover',
				'label'    => esc_html__( 'Border (Hover)', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-item:hover .es-cat-icon',
			)
		);

		$this->add_control(
			'icon_opacity_hover',
			array(
				'label'     => esc_html__( 'Opacity (Hover)', 'elonix' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .es-cat-item:hover .es-cat-icon' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 3. Category Title Styling
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Category Title', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_title_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_title_normal',
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
					'{{WRAPPER}} .es-cat-title'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-cat-title-link' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .es-cat-title, {{WRAPPER}} .es-cat-title-link',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .es-cat-title, {{WRAPPER}} .es-cat-title-link',
			)
		);

		$this->add_control(
			'title_stroke_width',
			array(
				'label'      => esc_html__( 'Text Stroke Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-title, {{WRAPPER}} .es-cat-title-link' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; text-stroke-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'title_stroke_color',
			array(
				'label'     => esc_html__( 'Text Stroke Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-title, {{WRAPPER}} .es-cat-title-link' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_gradient_enable',
			array(
				'label'        => esc_html__( 'Gradient Text', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'title_gradient',
				'label'     => esc_html__( 'Text Gradient Background', 'elonix' ),
				'types'     => array( 'gradient' ),
				'selector'  => '{{WRAPPER}} .es-cat-title, {{WRAPPER}} .es-cat-title-link',
				'condition' => array(
					'title_gradient_enable' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-item:hover .es-cat-title'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-cat-item:hover .es-cat-title-link' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// 4. Description Style Section
		$this->start_controls_section(
			'section_style_description',
			array(
				'label' => esc_html__( 'Category Description', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-description' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .es-cat-description',
			)
		);

		$this->end_controls_section();

		// 5. Parent Category Style Section
		$this->start_controls_section(
			'section_style_parent',
			array(
				'label' => esc_html__( 'Parent Category Title', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'parent_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-parent-label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .es-cat-parent-link'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'parent_typography',
				'selector' => '{{WRAPPER}} .es-cat-parent-label, {{WRAPPER}} .es-cat-parent-link',
			)
		);

		$this->end_controls_section();

		// 6. Post Count Style Section
		$this->start_controls_section(
			'section_style_count',
			array(
				'label' => esc_html__( 'Category Post Count', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_count_style' );

		// Normal State Tab
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
					'{{WRAPPER}} .es-cat-count' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'count_background',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-cat-count',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'count_border',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-count',
			)
		);

		$this->add_responsive_control(
			'count_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'count_box_shadow',
				'selector' => '{{WRAPPER}} .es-cat-count',
			)
		);

		$this->add_responsive_control(
			'count_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover State Tab
		$this->start_controls_tab(
			'tab_count_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'count_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-item:hover .es-cat-count' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'count_background_hover',
				'label'    => esc_html__( 'Background', 'elonix' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .es-cat-item:hover .es-cat-count',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'count_border_hover',
				'label'    => esc_html__( 'Border', 'elonix' ),
				'selector' => '{{WRAPPER}} .es-cat-item:hover .es-cat-count',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'count_box_shadow_hover',
				'selector' => '{{WRAPPER}} .es-cat-item:hover .es-cat-count',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Typo (outside tabs, maps to both)
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'count_typography',
				'selector'  => '{{WRAPPER}} .es-cat-count',
				'separator' => 'before',
			)
		);

		// Layout parameters for Badge / Pill Mode
		$this->add_control(
			'count_badge_mode',
			array(
				'label'        => esc_html__( 'Enable Badge Mode', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
				'selectors'    => array(
					'{{WRAPPER}} .es-cat-count' => 'position: absolute; top: 15px; right: 15px; margin-left: 0; z-index: 3;',
					'{{WRAPPER}} .es-cat-card'  => 'position: relative;',
				),
			)
		);

		$this->add_control(
			'count_pill_mode',
			array(
				'label'        => esc_html__( 'Force Circle Pill', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'selectors'    => array(
					'{{WRAPPER}} .es-cat-count' => 'border-radius: 50%; min-width: 24px; height: 24px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center;',
				),
			)
		);

		$this->end_controls_section();

		// 7. Badges Styles Section
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => esc_html__( 'Badges', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .es-cat-badge',
			)
		);

		$this->end_controls_section();

		// 8. Layout & Overlays Styling Section
		$this->start_controls_section(
			'section_style_layout_overlay',
			array(
				'label' => esc_html__( 'Layout & Glass Overlays', 'elonix' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'layout_blur_amount',
			array(
				'label'      => esc_html__( 'Frosted Blur Amount (Glass Card)', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 30,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-cat-card-glass' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				),
			)
		);

		$this->add_control(
			'glass_border_color',
			array(
				'label'     => esc_html__( 'Frosted Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .es-cat-card-glass' => 'border: 1px solid {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Helper: Render dynamic accordion toggle indicator.
	 */
	protected function render_toggle_icon( $settings, $state = 'collapsed' ) {
		$source = ! empty( $settings['icon_source'] ) ? $settings['icon_source'] : 'chevron';

		// Decorative ARIA bindings: icons inside buttons are decorative since the button itself has aria-label
		$icon_attrs = array( 'aria-hidden' => 'true' );

		if ( 'chevron' === $source ) {
			$icon_class = 'collapsed' === $state ? 'eicon-chevron-right' : 'eicon-chevron-down';
			echo '<i class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
		} elseif ( 'caret' === $source ) {
			$icon_class = 'collapsed' === $state ? 'eicon-caret-right' : 'eicon-caret-down';
			echo '<i class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
		} elseif ( 'plus_minus' === $source ) {
			$icon_class = 'collapsed' === $state ? 'eicon-plus' : 'eicon-minus';
			echo '<i class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
		} elseif ( 'arrow' === $source ) {
			$icon_class = 'collapsed' === $state ? 'eicon-arrow-right' : 'eicon-arrow-down';
			echo '<i class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
		} elseif ( 'custom' === $source ) {
			$icon = 'collapsed' === $state ? $settings['collapsed_icon'] : $settings['expanded_icon'];
			if ( ! empty( $icon ) ) {
				\Elementor\Icons_Manager::render_icon( $icon, $icon_attrs );
			}
		} elseif ( 'svg' === $source ) {
			$svg = 'collapsed' === $state ? $settings['collapsed_svg'] : $settings['expanded_svg'];
			if ( ! empty( $svg['url'] ) ) {
				echo '<img src="' . esc_url( $svg['url'] ) . '" alt="" aria-hidden="true" style="display: inline-block; vertical-align: middle;" />';
			} else {
				echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>';
			}
		}
	}

	/**
	 * Helper: Render category icon based on resolved type (font, svg, image).
	 */
	protected function render_category_icon( $term, $settings, $position = '' ) {
		if ( empty( $term['icon_value'] ) ) {
			return;
		}

		$type = ! empty( $term['icon_type'] ) ? $term['icon_type'] : 'font';
		$val  = $term['icon_value'];

		// Decorative ARIA bindings
		$icon_attrs = array( 'aria-hidden' => 'true' );

		$classes = array( 'es-cat-icon' );
		if ( $position ) {
			$classes[] = 'es-cat-icon-' . $position;
		}

		echo '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		if ( 'font' === $type ) {
			if ( is_array( $val ) ) {
				\Elementor\Icons_Manager::render_icon( $val, $icon_attrs );
			} else {
				echo '<i class="' . esc_attr( $val ) . '" aria-hidden="true"></i>';
			}
		} elseif ( 'svg' === $type ) {
			echo '<img src="' . esc_url( $val ) . '" alt="" aria-hidden="true" class="es-cat-svg-icon" style="display: inline-block; vertical-align: middle; width: 1em; height: 1em;" />';
		} elseif ( 'image' === $type ) {
			echo '<img src="' . esc_url( $val ) . '" alt="" aria-hidden="true" class="es-cat-image-icon" style="display: inline-block; vertical-align: middle; width: 1em; height: 1em; object-fit: contain;" />';
		}
		echo '</span>';
	}

	/**
	 * Render Categories HTML layouts.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$layout   = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';

		// Get terms data from helper engine (caching, query resolution)
		$terms = Elonix_Toolkit_Taxonomy_Helper::get_categories_data( $settings );

		if ( empty( $terms ) ) {
			echo '<div class="elementor-no-results es-categories-empty">' . esc_html__( 'No categories found matching your query.', 'elonix' ) . '</div>';
			return;
		}

		// CSS Wrapper classes
		$wrapper_classes = array(
			'es-categories',
			'es-categories-wrapper',
			'es-categories-style-' . $layout,
		);

		// Container properties and data config
		$container_attrs = array(
			'class' => esc_attr( implode( ' ', $wrapper_classes ) ),
		);

		$is_list       = in_array( $layout, array( 'classic', 'icon_list' ), true );
		$container_tag = $is_list ? 'ul' : 'div';

		if ( $is_list ) {
			$container_attrs['role'] = 'tree';
		}

		// Carousel Swiper Configurations mapping
		if ( 'carousel' === $layout ) {
			$swiper_opts                            = array(
				'autoplay'      => ( 'yes' === $settings['carousel_autoplay'] ),
				'loop'          => ( 'yes' === $settings['carousel_loop'] ),
				'centered'      => ( 'yes' === $settings['carousel_center'] ),
				'slidesPerView' => array(
					'desktop' => intval( $settings['slides_per_view'] ),
					'tablet'  => intval( ! empty( $settings['slides_per_view_tablet'] ) ? $settings['slides_per_view_tablet'] : 2 ),
					'mobile'  => intval( ! empty( $settings['slides_per_view_mobile'] ) ? $settings['slides_per_view_mobile'] : 1 ),
				),
			);
			$container_attrs['data-swiper-options'] = esc_attr( wp_json_encode( $swiper_opts ) );
		}

		$attrs_str = '';
		foreach ( $container_attrs as $key => $val ) {
			$attrs_str .= ' ' . $key . '="' . $val . '"';
		}

		?>
		<<?php echo esc_attr( $container_tag ); ?><?php echo $attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			
			<?php if ( 'carousel' === $layout ) : ?>
				<!-- Swiper Carousel Container -->
				<div class="swiper-container es-categories-swiper">
					<div class="swiper-wrapper">
						<?php foreach ( $terms as $term ) : ?>
							<div class="swiper-slide">
								<?php $this->render_card( $term, $settings, $layout ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					
					<?php if ( 'none' !== $settings['carousel_nav'] ) : ?>
						<!-- Navigation Arrows -->
						<div class="swiper-button-prev es-carousel-arrow es-arrow-<?php echo esc_attr( $settings['carousel_nav'] ); ?>"></div>
						<div class="swiper-button-next es-carousel-arrow es-arrow-<?php echo esc_attr( $settings['carousel_nav'] ); ?>"></div>
					<?php endif; ?>

					<?php if ( 'none' !== $settings['carousel_pag'] ) : ?>
						<!-- Pagination -->
						<div class="swiper-pagination es-carousel-pagination es-pag-<?php echo esc_attr( $settings['carousel_pag'] ); ?>"></div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<!-- Standard Grids / Lists container -->
				<div class="<?php echo esc_attr( $is_list ? 'es-categories-list es-categories-list-wrapper' : 'es-categories-grid-wrapper' ); ?>" <?php echo $is_list ? 'role="none"' : ''; ?>>
					<?php foreach ( $terms as $term ) : ?>
						<?php $this->render_card( $term, $settings, $layout ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</<?php echo esc_attr( $container_tag ); ?>>
		<?php
	}

	/**
	 * Render Individual Term Cards.
	 */
	protected function render_card( $term, $settings, $layout ) {
		$show_title   = ( 'yes' === $settings['show_title'] );
		$show_parent  = ( 'yes' === $settings['show_parent'] );
		$show_image   = ( 'yes' === $settings['show_image'] );
		$show_icon    = ( 'yes' === $settings['show_icon'] );
		$show_desc    = ( 'yes' === $settings['show_description'] );
		$show_count   = ( 'yes' === $settings['show_count'] );
		$show_nested  = ( 'yes' === $settings['nested_categories'] );
		$nested_style = ! empty( $settings['nested_style'] ) ? $settings['nested_style'] : 'tree';
		$show_read    = ( 'yes' === $settings['show_read_more'] );
		$is_list      = in_array( $layout, array( 'classic', 'icon_list' ), true );
		$card_tag     = $is_list ? 'li' : 'div';

		// Image styles for background layouts
		$card_style_attr = '';
		if ( 'background_card' === $layout && ! empty( $term['image'] ) ) {
			$card_style_attr = ' style="background-image: url(\'' . esc_url( $term['image'] ) . '\');"';
		}

		// Cards CSS and hover effects
		$card_classes = array( 'es-cat-item' );
		if ( $is_list ) {
			$card_classes[] = 'es-cat-card-classic';
		} else {
			$card_classes[] = 'es-cat-card';
			if ( 'glass_card' === $layout ) {
				$card_classes[] = 'es-cat-card-glass';
			}
		}
		if ( ! empty( $settings['hover_effect'] ) && 'none' !== $settings['hover_effect'] ) {
			$card_classes[] = 'es-hover-effect-' . esc_attr( $settings['hover_effect'] );
		}

		$card_attrs = array(
			'class' => esc_attr( implode( ' ', $card_classes ) ),
		);

		if ( $is_list ) {
			$card_attrs['role'] = 'treeitem';
			if ( $show_nested && ! empty( $term['children'] ) ) {
				$card_attrs['aria-expanded'] = 'false';
			}
		}

		$attrs_str = '';
		foreach ( $card_attrs as $key => $val ) {
			$attrs_str .= ' ' . $key . '="' . $val . '"';
		}

		?>
		<<?php echo esc_attr( $card_tag ); ?><?php echo $attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $card_style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			
			<?php
			// Show Custom Overrides specific badge, otherwise fall back to global badge setting
			$badge_text = ! empty( $term['badge'] ) ? $term['badge'] : ( ! empty( $settings['custom_badge'] ) ? $settings['custom_badge'] : '' );
			if ( ! empty( $badge_text ) ) :
				?>
				<!-- Highlight Badge -->
				<span class="es-cat-badge"><?php echo esc_html( $badge_text ); ?></span>
			<?php endif; ?>

			<?php if ( $is_list ) : ?>
				
				<?php
				$icon_pos = ! empty( $settings['category_icon_position'] ) ? $settings['category_icon_position'] : 'before';

				if ( $show_icon && ! empty( $term['icon_value'] ) && 'before' === $icon_pos ) :
					?>
					<?php $this->render_category_icon( $term, $settings, 'before' ); ?>
				<?php endif; ?>

				<?php
				if ( $show_title ) :
					$title_classes      = array( 'es-cat-title' );
					$title_link_classes = array( 'es-cat-title-link' );
					if ( 'yes' === $settings['title_gradient_enable'] ) {
						$has_gradient = false;
						if ( ! empty( $settings['title_gradient_color'] ) || ! empty( $settings['title_gradient_color_b'] ) ) {
							$has_gradient = true;
						}
						if ( $has_gradient ) {
							$title_classes[]      = 'es-title-gradient-active';
							$title_link_classes[] = 'es-title-gradient-active';
						}
					}
					?>
					<span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>">
						<a href="<?php echo esc_url( $term['url'] ); ?>" class="<?php echo esc_attr( implode( ' ', $title_link_classes ) ); ?>">
							<?php echo esc_html( $term['name'] ); ?>
						</a>
					</span>
				<?php endif; ?>

				<?php if ( $show_icon && ! empty( $term['icon_value'] ) && 'after' === $icon_pos ) : ?>
					<?php $this->render_category_icon( $term, $settings, 'after' ); ?>
				<?php endif; ?>

				<?php if ( $show_count ) : ?>
					<span class="es-cat-count"><?php echo intval( $term['count'] ); ?> <span class="screen-reader-text"><?php esc_html_e( 'items in this category', 'elonix' ); ?></span></span>
				<?php endif; ?>

				<?php if ( $show_nested && ! empty( $term['children'] ) ) : ?>
					<!-- Nested categories tree -->
					<?php if ( 'accordion' === $nested_style ) : ?>
						<!-- Accordion Disclosure Pattern -->
						<button class="es-cat-toggle-btn" aria-expanded="false" aria-controls="es-children-list-<?php echo esc_attr( $term['id'] ); ?>" aria-label="<?php esc_attr_e( 'Toggle child categories', 'elonix' ); ?>">
							<?php if ( 'yes' === $settings['show_toggle_icon'] ) : ?>
								<span class="es-cat-icon-collapsed">
									<?php $this->render_toggle_icon( $settings, 'collapsed' ); ?>
								</span>
								<span class="es-cat-icon-expanded" style="display: none;">
									<?php $this->render_toggle_icon( $settings, 'expanded' ); ?>
								</span>
							<?php else : ?>
								<span class="screen-reader-text"><?php esc_html_e( 'Toggle', 'elonix' ); ?></span>
							<?php endif; ?>
						</button>
						<ul class="es-categories-tree es-cat-children-list es-cat-children-hidden" id="es-children-list-<?php echo esc_attr( $term['id'] ); ?>" role="group" aria-hidden="true">
							<?php foreach ( $term['children'] as $child ) : ?>
								<li class="es-cat-child-item" role="treeitem">
									<a href="<?php echo esc_url( $child['url'] ); ?>" class="es-cat-child-link"><?php echo esc_html( $child['name'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<!-- Standard Navigation tree pattern -->
						<ul class="es-categories-tree es-cat-children-list" role="group">
							<?php foreach ( $term['children'] as $child ) : ?>
								<li class="es-cat-child-item" role="treeitem">
									<a href="<?php echo esc_url( $child['url'] ); ?>" class="es-cat-child-link"><?php echo esc_html( $child['name'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>

			<?php else : ?>
				
				<?php if ( $show_image && ! empty( $term['image'] ) && 'background_card' !== $layout ) : ?>
					<!-- Card Image Wrapper -->
					<div class="es-cat-image-wrapper">
						<img src="<?php echo esc_url( $term['image'] ); ?>" alt="<?php echo esc_attr( $term['name'] ); ?>" loading="lazy" />
						<div class="es-cat-card-overlay"></div>
					</div>
				<?php endif; ?>

				<!-- Card Body -->
				<div class="es-cat-card-body">
					
					<?php
					$icon_pos = ! empty( $settings['category_icon_position'] ) ? $settings['category_icon_position'] : 'before';

					if ( $show_icon && ! empty( $term['icon_value'] ) && 'above' === $icon_pos ) :
						?>
						<!-- Icon element (Above Title) -->
						<?php $this->render_category_icon( $term, $settings, 'above' ); ?>
					<?php endif; ?>

					<div class="es-cat-card-header-row">
						<?php
						if ( $show_icon && ! empty( $term['icon_value'] ) && 'before' === $icon_pos ) :
							?>
							<!-- Icon element (Before Title) -->
							<?php $this->render_category_icon( $term, $settings, 'before' ); ?>
						<?php endif; ?>

						<?php
						if ( $show_title ) :
							$title_classes      = array( 'es-cat-title' );
							$title_link_classes = array( 'es-cat-title-link' );
							if ( 'yes' === $settings['title_gradient_enable'] ) {
								$has_gradient = false;
								if ( ! empty( $settings['title_gradient_color'] ) || ! empty( $settings['title_gradient_color_b'] ) ) {
									$has_gradient = true;
								}
								if ( $has_gradient ) {
									$title_classes[]      = 'es-title-gradient-active';
									$title_link_classes[] = 'es-title-gradient-active';
								}
							}
							?>
							<!-- Name & Title -->
							<span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>" style="display: inline-flex; align-items: center; gap: 5px;">
								<a href="<?php echo esc_url( $term['url'] ); ?>" class="<?php echo esc_attr( implode( ' ', $title_link_classes ) ); ?>">
									<?php echo esc_html( $term['name'] ); ?>
								</a>
							</span>
						<?php endif; ?>

						<?php if ( $show_icon && ! empty( $term['icon_value'] ) && 'after' === $icon_pos ) : ?>
							<!-- Icon element (After Title) -->
							<?php $this->render_category_icon( $term, $settings, 'after' ); ?>
						<?php endif; ?>

						<?php if ( $show_parent && ! empty( $term['parent_name'] ) ) : ?>
							<!-- Parent category name -->
							<span class="es-cat-parent-label">
								<?php esc_html_e( 'in', 'elonix' ); ?>
								<a href="<?php echo esc_url( $term['parent_url'] ); ?>" class="es-cat-parent-link">
									<?php echo esc_html( $term['parent_name'] ); ?>
								</a>
							</span>
						<?php endif; ?>

						<?php if ( $show_count ) : ?>
							<!-- Count Badge -->
							<span class="es-cat-count"><?php echo intval( $term['count'] ); ?> <span class="screen-reader-text"><?php esc_html_e( 'items in this category', 'elonix' ); ?></span></span>
						<?php endif; ?>
					</div>

					<?php if ( $show_desc && ! empty( $term['description'] ) ) : ?>
						<!-- Description -->
						<p class="es-cat-description"><?php echo esc_html( wp_trim_words( $term['description'], 12 ) ); ?></p>
					<?php endif; ?>

					<?php if ( $show_read ) : ?>
						<!-- Read More Link -->
						<a href="<?php echo esc_url( $term['url'] ); ?>" class="es-cat-read-more">
							<?php echo esc_html( $settings['read_more_text'] ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $show_nested && ! empty( $term['children'] ) ) : ?>
						<!-- Nested categories tree -->
						<?php if ( 'accordion' === $nested_style ) : ?>
							<!-- Accordion Disclosure Pattern -->
							<button class="es-cat-toggle-btn" aria-expanded="false" aria-controls="es-children-list-<?php echo esc_attr( $term['id'] ); ?>" aria-label="<?php esc_attr_e( 'Toggle child categories', 'elonix' ); ?>">
								<?php if ( 'yes' === $settings['show_toggle_icon'] ) : ?>
									<span class="es-cat-icon-collapsed">
										<?php $this->render_toggle_icon( $settings, 'collapsed' ); ?>
									</span>
									<span class="es-cat-icon-expanded" style="display: none;">
										<?php $this->render_toggle_icon( $settings, 'expanded' ); ?>
									</span>
								<?php else : ?>
									<span class="screen-reader-text"><?php esc_html_e( 'Toggle', 'elonix' ); ?></span>
								<?php endif; ?>
							</button>
							<ul class="es-categories-tree es-cat-children-list es-cat-children-hidden" id="es-children-list-<?php echo esc_attr( $term['id'] ); ?>" role="group" aria-hidden="true">
								<?php foreach ( $term['children'] as $child ) : ?>
									<li class="es-cat-child-item" role="treeitem">
										<a href="<?php echo esc_url( $child['url'] ); ?>" class="es-cat-child-link"><?php echo esc_html( $child['name'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<!-- Standard Navigation tree pattern -->
							<ul class="es-categories-tree es-cat-children-list" role="group">
								<?php foreach ( $term['children'] as $child ) : ?>
									<li class="es-cat-child-item" role="treeitem">
										<a href="<?php echo esc_url( $child['url'] ); ?>" class="es-cat-child-link"><?php echo esc_html( $child['name'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endif; ?>

				</div>
			<?php endif; ?>
		</<?php echo esc_attr( $card_tag ); ?>>
		<?php
	}
}
