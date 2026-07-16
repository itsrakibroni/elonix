<?php
/**
 * Elonix – Toolkit for Elementor Post Info Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_Info_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-post-info';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Info', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-post-info';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'post', 'info', 'meta', 'author', 'date', 'category', 'tag', 'comments', 'reading', 'views', 'custom', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-post-info' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - Meta Items
		$this->start_controls_section(
			'section_meta_items',
			array(
				'label' => esc_html__( 'Meta Items', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'meta_type',
			array(
				'label'   => esc_html__( 'Meta Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'author',
				'options' => array(
					'author'          => esc_html__( 'Author', 'elonix' ),
					'publish_date'    => esc_html__( 'Publish Date', 'elonix' ),
					'modified_date'   => esc_html__( 'Modified Date', 'elonix' ),
					'categories'      => esc_html__( 'Categories', 'elonix' ),
					'tags'            => esc_html__( 'Tags', 'elonix' ),
					'comments'        => esc_html__( 'Comments Count', 'elonix' ),
					'reading_time'    => esc_html__( 'Reading Time', 'elonix' ),
					'post_views'      => esc_html__( 'Post Views', 'elonix' ),
					'custom_meta'     => esc_html__( 'Custom Meta Field', 'elonix' ),
					'custom_taxonomy' => esc_html__( 'Custom Taxonomy', 'elonix' ),
					'post_id'         => esc_html__( 'Post ID', 'elonix' ),
					'post_type'       => esc_html__( 'Post Type', 'elonix' ),
				),
			)
		);

		$repeater->add_control(
			'text_prefix',
			array(
				'label'       => esc_html__( 'Before Text (Prefix)', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. By', 'elonix' ),
			)
		);

		$repeater->add_control(
			'text_suffix',
			array(
				'label'       => esc_html__( 'After Text (Suffix)', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. ,', 'elonix' ),
			)
		);

		$repeater->add_control(
			'link_enabled',
			array(
				'label'        => esc_html__( 'Enable Link', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'meta_type' => array( 'author', 'categories', 'tags', 'comments', 'custom_taxonomy' ),
				),
			)
		);

		$repeater->add_control(
			'show_avatar',
			array(
				'label'        => esc_html__( 'Show Author Avatar', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'meta_type' => 'author',
				),
			)
		);

		$repeater->add_control(
			'avatar_size',
			array(
				'label'     => esc_html__( 'Avatar Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 16,
						'max' => 128,
					),
				),
				'default'   => array(
					'size' => 24,
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .tv-post-info-avatar' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				),
				'condition' => array(
					'meta_type'   => 'author',
					'show_avatar' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'date_format',
			array(
				'label'     => esc_html__( 'Date Format', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default'  => esc_html__( 'WordPress Default', 'elonix' ),
					'relative' => esc_html__( 'Relative Time (e.g. 2 days ago)', 'elonix' ),
					'human'    => esc_html__( 'Human Readable (F j, Y)', 'elonix' ),
					'custom'   => esc_html__( 'Custom PHP Format', 'elonix' ),
				),
				'condition' => array(
					'meta_type' => array( 'publish_date', 'modified_date' ),
				),
			)
		);

		$repeater->add_control(
			'custom_date_format',
			array(
				'label'       => esc_html__( 'Custom PHP Date Format', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'F j, Y',
				'description' => esc_html__( 'Use PHP date formatting string (e.g. Y-m-d).', 'elonix' ),
				'condition'   => array(
					'meta_type'   => array( 'publish_date', 'modified_date' ),
					'date_format' => 'custom',
				),
			)
		);

		$repeater->add_control(
			'wpm',
			array(
				'label'       => esc_html__( 'Words Per Minute (WPM)', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 200,
				'condition'   => array(
					'meta_type' => 'reading_time',
				),
			)
		);

		$repeater->add_control(
			'meta_key',
			array(
				'label'       => esc_html__( 'Custom Meta Key', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'my_custom_meta_field',
				'condition'   => array(
					'meta_type' => 'custom_meta',
				),
			)
		);

		$repeater->add_control(
			'taxonomy',
			array(
				'label'     => esc_html__( 'Custom Taxonomy Slug', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => $this->get_available_taxonomies(),
				'condition' => array(
					'meta_type' => 'custom_taxonomy',
				),
			)
		);

		$repeater->add_control(
			'show_icon',
			array(
				'label'     => esc_html__( 'Icon', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => esc_html__( 'Default Icon', 'elonix' ),
					'custom'  => esc_html__( 'Custom Icon / SVG', 'elonix' ),
					'none'    => esc_html__( 'None', 'elonix' ),
				),
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'custom_icon',
			array(
				'label'     => esc_html__( 'Choose Icon', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'condition' => array(
					'show_icon' => 'custom',
				),
			)
		);

		$repeater->add_control(
			'icon_position',
			array(
				'label'     => esc_html__( 'Icon Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Before Text', 'elonix' ),
					'after'  => esc_html__( 'After Text', 'elonix' ),
				),
				'condition' => array(
					'show_icon!' => 'none',
				),
			)
		);

		$repeater->add_responsive_control(
			'icon_spacing',
			array(
				'label'     => esc_html__( 'Icon Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .tv-post-info-icon.tv-icon-before' => 'margin-right: {{SIZE}}px;',
					'{{WRAPPER}} {{CURRENT_ITEM}} .tv-post-info-icon.tv-icon-after'  => 'margin-left: {{SIZE}}px;',
				),
				'condition' => array(
					'show_icon!' => 'none',
				),
			)
		);

		$this->add_control(
			'meta_items',
			array(
				'label'       => esc_html__( 'Element Order Builder', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'meta_type'    => 'author',
						'text_prefix'  => esc_html__( 'By', 'elonix' ),
						'show_icon'    => 'default',
						'link_enabled' => 'yes',
					),
					array(
						'meta_type'   => 'publish_date',
						'date_format' => 'human',
						'show_icon'   => 'default',
					),
					array(
						'meta_type'    => 'categories',
						'show_icon'    => 'default',
						'link_enabled' => 'yes',
					),
					array(
						'meta_type'    => 'comments',
						'show_icon'    => 'default',
						'link_enabled' => 'yes',
					),
				),
				'title_field' => '{{{ meta_type.replace("_", " ").toUpperCase() }}}',
			)
		);

		$this->end_controls_section();

		// Style Section - Layout
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => esc_html__( 'Layout & Separator', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'inline',
				'options' => array(
					'inline' => array(
						'title' => esc_html__( 'Inline', 'elonix' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'list'   => array(
						'title' => esc_html__( 'List (Vertical)', 'elonix' ),
						'icon'  => 'eicon-editor-list-ul',
					),
				),
			)
		);

		$this->add_responsive_control(
			'alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
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
					'{{WRAPPER}} .tv-post-info-container' => 'justify-content: {{VALUE}}; text-align: {{VALUE}};',
					'{{WRAPPER}} .tv-post-info-container.tv-layout-list' => 'align-items: {{VALUE}} === "center" ? "center" : ({{VALUE}} === "right" ? "flex-end" : "flex-start");',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Spacing / Gap', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-info-container' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'separator_type',
			array(
				'label'     => esc_html__( 'Separator', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'slash',
				'options'   => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'slash'  => esc_html__( 'Slash (/)', 'elonix' ),
					'bullet' => esc_html__( 'Bullet (•)', 'elonix' ),
					'pipe'   => esc_html__( 'Pipe (|)', 'elonix' ),
					'dash'   => esc_html__( 'Dash (-)', 'elonix' ),
					'custom' => esc_html__( 'Custom', 'elonix' ),
				),
				'separator' => 'before',
				'condition' => array(
					'layout' => 'inline',
				),
			)
		);

		$this->add_control(
			'custom_separator',
			array(
				'label'     => esc_html__( 'Custom Separator', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '//',
				'condition' => array(
					'layout'         => 'inline',
					'separator_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Separator Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-separator' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'inline',
					'separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'separator_size',
			array(
				'label'     => esc_html__( 'Separator Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-separator' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout'          => 'inline',
					'separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'separator_padding',
			array(
				'label'     => esc_html__( 'Separator Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-separator' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout'          => 'inline',
					'separator_type!' => 'none',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'container_border',
				'selector'  => '{{WRAPPER}} .tv-post-info-container',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-info-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'selector' => '{{WRAPPER}} .tv-post-info-container',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-info-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'container_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-info-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Text
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Text & Typography', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-item' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_hover_color',
			array(
				'label'     => esc_html__( 'Text Hover Color (Links)', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-item' => '--tv-post-info-hover-color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-info-item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .tv-post-info-item',
			)
		);

		$this->end_controls_section();

		// Style Section - Icons
		$this->start_controls_section(
			'section_style_icon',
			array(
				'label' => esc_html__( 'Icon Styles', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-item:hover .tv-post-info-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-info-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-post-info-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get available taxonomies for select dropdown.
	 *
	 * @return array Taxonomies list.
	 */
	protected function get_available_taxonomies() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		$options    = array( '' => esc_html__( 'Select Taxonomy', 'elonix' ) );

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	/**
	 * Retrieve the active post object dynamically across all templates and contexts.
	 *
	 * @return WP_Post|null Current post object or null.
	 */
	protected function get_current_post_object() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				$preview_id = \Elementor\Plugin::$instance->editor->get_post_id();
				if ( ! $preview_id ) {
					$preview_id = \Elementor\Plugin::$instance->preview->get_post_id();
				}
				if ( $preview_id ) {
					$post = get_post( $preview_id );
					if ( $post ) {
						return $post;
					}
				}
			}
		}

		$post_id = get_the_ID();
		if ( $post_id ) {
			return get_post( $post_id );
		}

		$queried_object = get_queried_object();
		if ( $queried_object && isset( $queried_object->post_type ) ) {
			return $queried_object;
		}

		if ( ( is_search() || is_archive() ) && have_posts() ) {
			global $wp_query;
			if ( ! empty( $wp_query->posts[0] ) ) {
				return $wp_query->posts[0];
			}
		}

		return null;
	}

	/**
	 * Extract data for a specific meta item.
	 *
	 * @param array   $item Item settings.
	 * @param WP_Post $post Current post object.
	 * @return array|null Item data or null if empty state.
	 */
	protected function get_meta_item_data( $item, $post ) {
		$data = array(
			'text'         => '',
			'url'          => '',
			'default_icon' => '',
			'avatar'       => '',
			'terms_list'   => array(),
		);

		switch ( $item['meta_type'] ) {
			case 'author':
				$author_id = $post->post_author;
				$data['text'] = get_the_author_meta( 'display_name', $author_id );
				if ( 'yes' === $item['link_enabled'] ) {
					$data['url'] = get_author_posts_url( $author_id );
				}
				if ( 'yes' === $item['show_avatar'] ) {
					$size = ! empty( $item['avatar_size']['size'] ) ? (int) $item['avatar_size']['size'] : 24;
					$data['avatar'] = get_avatar_url( $author_id, array( 'size' => $size ) );
				}
				$data['default_icon'] = 'fas fa-user';
				break;

			case 'publish_date':
				if ( 'relative' === $item['date_format'] ) {
					$data['text'] = human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'elonix' );
				} elseif ( 'human' === $item['date_format'] ) {
					$data['text'] = get_post_time( 'F j, Y', false, $post, true );
				} elseif ( 'custom' === $item['date_format'] && ! empty( $item['custom_date_format'] ) ) {
					$data['text'] = get_post_time( $item['custom_date_format'], false, $post, true );
				} else {
					$data['text'] = get_post_time( get_option( 'date_format' ), false, $post, true );
				}
				$data['default_icon'] = 'fas fa-calendar-alt';
				break;

			case 'modified_date':
				if ( 'relative' === $item['date_format'] ) {
					$data['text'] = human_time_diff( get_post_modified_time( 'U', false, $post ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'elonix' );
				} elseif ( 'human' === $item['date_format'] ) {
					$data['text'] = get_post_modified_time( 'F j, Y', false, $post, true );
				} elseif ( 'custom' === $item['date_format'] && ! empty( $item['custom_date_format'] ) ) {
					$data['text'] = get_post_modified_time( $item['custom_date_format'], false, $post, true );
				} else {
					$data['text'] = get_post_modified_time( get_option( 'date_format' ), false, $post, true );
				}
				$data['default_icon'] = 'fas fa-calendar-check';
				break;

			case 'categories':
			case 'tags':
			case 'custom_taxonomy':
				$tax = 'category';
				if ( 'tags' === $item['meta_type'] ) {
					$tax = 'post_tag';
					$data['default_icon'] = 'fas fa-tags';
				} elseif ( 'custom_taxonomy' === $item['meta_type'] ) {
					$tax = ! empty( $item['taxonomy'] ) ? $item['taxonomy'] : '';
					$data['default_icon'] = 'fas fa-bookmark';
				} else {
					$data['default_icon'] = 'fas fa-folder';
				}

				if ( empty( $tax ) ) {
					return null;
				}

				$terms = get_the_terms( $post->ID, $tax );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					return null; // Empty state: hide item automatically
				}

				foreach ( $terms as $term ) {
					$term_data = array(
						'text' => $term->name,
						'url'  => '',
					);
					if ( 'yes' === $item['link_enabled'] ) {
						$link = get_term_link( $term );
						if ( ! is_wp_error( $link ) ) {
							$term_data['url'] = $link;
						}
					}
					$data['terms_list'][] = $term_data;
				}
				break;

			case 'comments':
				if ( ! comments_open( $post->ID ) && get_comments_number( $post->ID ) == 0 ) {
					return null; // Empty state
				}
				$count = (int) get_comments_number( $post->ID );
				if ( 0 === $count ) {
					$data['text'] = esc_html__( '0 Comments', 'elonix' );
				} elseif ( 1 === $count ) {
					$data['text'] = esc_html__( '1 Comment', 'elonix' );
				} else {
					/* translators: %d: Number of comments. */
					$data['text'] = sprintf( esc_html__( '%d Comments', 'elonix' ), $count );
				}

				if ( 'yes' === $item['link_enabled'] ) {
					$data['url'] = get_comments_link( $post->ID );
				}
				$data['default_icon'] = 'fas fa-comments';
				break;

			case 'reading_time':
				$clean_content = wp_strip_all_tags( $post->post_content );
				$word_count    = str_word_count( $clean_content );
				$wpm           = ! empty( $item['wpm'] ) ? (int) $item['wpm'] : 200;
				$minutes       = (int) ceil( $word_count / $wpm );
				$data['text']  = $minutes . ' ' . esc_html__( 'min read', 'elonix' );
				$data['default_icon'] = 'fas fa-clock';
				break;

			case 'post_views':
				$views = get_post_meta( $post->ID, 'tv_post_views_count', true );
				if ( empty( $views ) ) {
					return null; // Empty state
				}
				$data['text'] = number_format_i18n( (int) $views ) . ' ' . esc_html__( 'views', 'elonix' );
				$data['default_icon'] = 'fas fa-eye';
				break;

			case 'custom_meta':
				$key = ! empty( $item['meta_key'] ) ? $item['meta_key'] : '';
				if ( empty( $key ) ) {
					return null;
				}
				$val = get_post_meta( $post->ID, $key, true );
				if ( empty( $val ) ) {
					return null; // Empty state
				}
				$data['text'] = esc_html( $val );
				$data['default_icon'] = 'fas fa-info-circle';
				break;

			case 'post_id':
				$data['text'] = $post->ID;
				$data['default_icon'] = 'fas fa-hashtag';
				break;

			case 'post_type':
				$pt_obj = get_post_type_object( $post->post_type );
				$data['text'] = $pt_obj ? $pt_obj->labels->singular_name : $post->post_type;
				$data['default_icon'] = 'fas fa-file-alt';
				break;
		}

		if ( empty( $data['text'] ) && empty( $data['terms_list'] ) ) {
			return null;
		}

		return $data;
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$post     = $this->get_current_post_object();

		if ( ! $post || empty( $settings['meta_items'] ) ) {
			return;
		}

		$layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'inline';
		$this->add_render_attribute( 'container', 'class', 'tv-post-info-container tv-layout-' . esc_attr( $layout ) );

		$separator_html = '';
		if ( 'inline' === $layout && ! empty( $settings['separator_type'] ) && 'none' !== $settings['separator_type'] ) {
			$sep_char = '';
			switch ( $settings['separator_type'] ) {
				case 'slash':
					$sep_char = '/';
					break;
				case 'bullet':
					$sep_char = '•';
					break;
				case 'pipe':
					$sep_char = '|';
					break;
				case 'dash':
					$sep_char = '-';
					break;
				case 'custom':
					$sep_char = ! empty( $settings['custom_separator'] ) ? $settings['custom_separator'] : '';
					break;
			}
			if ( ! empty( $sep_char ) ) {
				$separator_html = '<span class="tv-post-info-separator">' . esc_html( $sep_char ) . '</span>';
			}
		}

		$valid_items = array();
		foreach ( $settings['meta_items'] as $item ) {
			$item_data = $this->get_meta_item_data( $item, $post );
			if ( ! empty( $item_data ) ) {
				$valid_items[] = array(
					'settings' => $item,
					'data'     => $item_data,
				);
			}
		}

		if ( empty( $valid_items ) ) {
			return; // Empty state: render nothing
		}

		$total_items = count( $valid_items );
		?>
		<ul <?php $this->print_render_attribute_string( 'container' ); ?>>
			<?php
			foreach ( $valid_items as $index => $item_bundle ) {
				$item      = $item_bundle['settings'];
				$data      = $item_bundle['data'];
				$item_id   = $item['_id'];
				$item_key  = 'item_' . $item_id;

				$this->add_render_attribute( $item_key, 'class', 'tv-post-info-item elementor-repeater-item-' . esc_attr( $item_id ) );

				$has_link = ! empty( $data['url'] ) && 'yes' === $item['link_enabled'];

				// Determine Icon HTML
				$icon_html     = '';
				$icon_position = ! empty( $item['icon_position'] ) ? $item['icon_position'] : 'before';

				if ( ! empty( $data['avatar'] ) && 'author' === $item['meta_type'] && 'yes' === $item['show_avatar'] ) {
					$icon_html = '<span class="tv-post-info-icon tv-icon-' . esc_attr( $icon_position ) . '"><img class="tv-post-info-avatar" src="' . esc_url( $data['avatar'] ) . '" alt="' . esc_attr( $data['text'] ) . '" /></span>';
				} elseif ( 'custom' === $item['show_icon'] && ! empty( $item['custom_icon']['value'] ) ) {
					ob_start();
					\Elementor\Icons_Manager::render_icon( $item['custom_icon'], array( 'aria-hidden' => 'true' ) );
					$rendered_icon = ob_get_clean();
					$icon_html = '<span class="tv-post-info-icon tv-icon-' . esc_attr( $icon_position ) . '">' . $rendered_icon . '</span>';
				} elseif ( 'default' === $item['show_icon'] && ! empty( $data['default_icon'] ) ) {
					$icon_html = '<span class="tv-post-info-icon tv-icon-' . esc_attr( $icon_position ) . '"><i class="' . esc_attr( $data['default_icon'] ) . '" aria-hidden="true"></i></span>';
				}

				// Determine Text HTML
				$text_html = '<span class="tv-post-info-text">';
				if ( ! empty( $item['text_prefix'] ) ) {
					$text_html .= '<span class="tv-post-info-prefix">' . esc_html( $item['text_prefix'] ) . '</span> ';
				}

				if ( ! empty( $data['terms_list'] ) ) {
					$terms_links = array();
					foreach ( $data['terms_list'] as $term ) {
						$term_text = esc_html( $term['text'] );
						if ( ! empty( $term['url'] ) && 'yes' === $item['link_enabled'] ) {
							$terms_links[] = '<a href="' . esc_url( $term['url'] ) . '" class="tv-post-info-term-link">' . $term_text . '</a>';
						} else {
							$terms_links[] = '<span class="tv-post-info-term-link">' . $term_text . '</span>';
						}
					}
					$text_html .= '<span class="tv-post-info-terms">' . implode( ', ', $terms_links ) . '</span>';
				} else {
					$text_html .= wp_kses_post( $data['text'] );
				}

				if ( ! empty( $item['text_suffix'] ) ) {
					$text_html .= ' <span class="tv-post-info-suffix">' . esc_html( $item['text_suffix'] ) . '</span>';
				}
				$text_html .= '</span>';
				?>
				<li <?php $this->print_render_attribute_string( $item_key ); ?>>
					<?php if ( $has_link ) : ?>
						<a href="<?php echo esc_url( $data['url'] ); ?>">
					<?php endif; ?>

					<?php
					if ( 'before' === $icon_position ) {
						echo wp_kses_post( $icon_html );
						echo wp_kses_post( $text_html );
					} else {
						echo wp_kses_post( $text_html );
						echo wp_kses_post( $icon_html );
					}
					?>

					<?php if ( $has_link ) : ?>
						</a>
					<?php endif; ?>
				</li>
				<?php
				if ( $index < ( $total_items - 1 ) && ! empty( $separator_html ) ) {
					echo wp_kses_post( $separator_html );
				}
			}
			?>
		</ul>
		<?php
	}
}
