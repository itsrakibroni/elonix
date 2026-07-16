<?php
/**
 * Elonix – Toolkit for Elementor Post Terms Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_Terms_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-post-terms';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Post Terms', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-tags';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'post', 'terms', 'taxonomy', 'category', 'tag', 'custom', 'badge', 'pill', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-post-terms' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - Taxonomy & Source
		$this->start_controls_section(
			'section_taxonomy_source',
			array(
				'label' => esc_html__( 'Taxonomy Source', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'taxonomy_source',
			array(
				'label'   => esc_html__( 'Taxonomy Source', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'            => esc_html__( 'Auto Detect (Current Post Primary Taxonomy)', 'elonix' ),
					'category'        => esc_html__( 'Categories', 'elonix' ),
					'post_tag'        => esc_html__( 'Tags', 'elonix' ),
					'custom_taxonomy' => esc_html__( 'Custom Taxonomies', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'custom_taxonomy',
			array(
				'label'     => esc_html__( 'Select Custom Taxonomy', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => $this->get_available_taxonomies(),
				'condition' => array(
					'taxonomy_source' => 'custom_taxonomy',
				),
			)
		);

		$this->add_control(
			'display_style',
			array(
				'label'   => esc_html__( 'Display Style', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => array(
					'inline' => esc_html__( 'Inline', 'elonix' ),
					'block'  => esc_html__( 'Block (Vertical)', 'elonix' ),
					'badge'  => esc_html__( 'Badge', 'elonix' ),
					'pill'   => esc_html__( 'Pill', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'separator_type',
			array(
				'label'     => esc_html__( 'Separator', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'comma',
				'options'   => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'comma'  => esc_html__( 'Comma (,)', 'elonix' ),
					'dot'    => esc_html__( 'Dot (•)', 'elonix' ),
					'slash'  => esc_html__( 'Slash (/)', 'elonix' ),
					'pipe'   => esc_html__( 'Pipe (|)', 'elonix' ),
					'custom' => esc_html__( 'Custom Text', 'elonix' ),
				),
				'condition' => array(
					'display_style' => array( 'inline' ),
				),
			)
		);

		$this->add_control(
			'custom_separator',
			array(
				'label'     => esc_html__( 'Custom Separator', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => ' - ',
				'condition' => array(
					'display_style'  => array( 'inline' ),
					'separator_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'max_terms',
			array(
				'label'       => esc_html__( 'Maximum Terms', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'description' => esc_html__( 'Set to 0 to show all terms.', 'elonix' ),
			)
		);

		$this->add_control(
			'show_remaining',
			array(
				'label'        => esc_html__( 'Show Remaining Count', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Example: +3', 'elonix' ),
				'condition'    => array(
					'max_terms!' => 0,
				),
			)
		);

		$this->add_control(
			'link_terms',
			array(
				'label'        => esc_html__( 'Link Terms', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'elonix' ),
				'label_off'    => esc_html__( 'Disable', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'open_new_tab',
			array(
				'label'        => esc_html__( 'Open in New Tab', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'link_terms' => 'yes',
				),
			)
		);

		$this->add_control(
			'nofollow',
			array(
				'label'        => esc_html__( 'Add NoFollow', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'link_terms' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Content Section - Label
		$this->start_controls_section(
			'section_label',
			array(
				'label' => esc_html__( 'Label', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_label',
			array(
				'label'        => esc_html__( 'Show Label', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'elonix' ),
				'label_off'    => esc_html__( 'Hide', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'custom_label',
			array(
				'label'       => esc_html__( 'Custom Label', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Categories:', 'elonix' ),
				'placeholder' => esc_html__( 'e.g. Category, Categories, Tag, Topics', 'elonix' ),
				'condition'   => array(
					'show_label' => 'yes',
				),
			)
		);

		$this->add_control(
			'label_position',
			array(
				'label'     => esc_html__( 'Label Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Before Terms', 'elonix' ),
					'after'  => esc_html__( 'After Terms', 'elonix' ),
				),
				'condition' => array(
					'show_label' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Content Section - Icons
		$this->start_controls_section(
			'section_icons',
			array(
				'label' => esc_html__( 'Icons', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_icon',
			array(
				'label'        => esc_html__( 'Enable Icon', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'custom_icon',
			array(
				'label'     => esc_html__( 'Choose Icon', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-tag',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => esc_html__( 'Icon Position', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => array(
					'before' => esc_html__( 'Before Term Name', 'elonix' ),
					'after'  => esc_html__( 'After Term Name', 'elonix' ),
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 8,
						'max' => 64,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .tv-post-terms-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-post-terms-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
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
					'{{WRAPPER}} .tv-post-terms-icon.tv-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .tv-post-terms-icon.tv-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Terms
		$this->start_controls_section(
			'section_style_terms',
			array(
				'label' => esc_html__( 'Terms Styling', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'terms_typography',
				'selector' => '{{WRAPPER}} .tv-post-term-item',
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
					'{{WRAPPER}} .tv-post-terms-container' => 'justify-content: {{VALUE}}; text-align: {{VALUE}};',
					'{{WRAPPER}} .tv-post-terms-container.tv-style-block' => 'align-items: {{VALUE}} === "center" ? "center" : ({{VALUE}} === "right" ? "flex-end" : "flex-start");',
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
					'{{WRAPPER}} .tv-post-terms-container' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_terms_style' );

		$this->start_controls_tab(
			'tab_terms_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-term-item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item .tv-post-terms-icon' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_terms_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'hover_color',
			array(
				'label'     => esc_html__( 'Text Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tv-post-term-item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'hover_background',
			array(
				'label'     => esc_html__( 'Hover Background', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-term-item:hover .tv-post-terms-icon' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'enable_icon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .tv-post-term-item',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-term-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-term-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-term-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Label Styling
		$this->start_controls_section(
			'section_style_label',
			array(
				'label'     => esc_html__( 'Label Styling', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_label' => 'yes',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .tv-post-terms-label',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-terms-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'label_padding',
			array(
				'label'      => esc_html__( 'Label Spacing', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .tv-post-terms-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Separator Styling
		$this->start_controls_section(
			'section_style_separator',
			array(
				'label'     => esc_html__( 'Separator Styling', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'display_style'   => array( 'inline' ),
					'separator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Separator Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-terms-separator' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'separator_size',
			array(
				'label'     => esc_html__( 'Separator Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-terms-separator' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'separator_padding',
			array(
				'label'     => esc_html__( 'Separator Spacing', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .tv-post-terms-separator' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
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
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$post     = $this->get_current_post_object();

		if ( ! $post ) {
			return; // Empty state: render nothing
		}

		$taxonomy = '';
		if ( 'auto' === $settings['taxonomy_source'] ) {
			if ( 'post' === $post->post_type ) {
				$taxonomy = 'category';
			} elseif ( 'product' === $post->post_type ) {
				$taxonomy = 'product_cat';
			} else {
				$taxonomies = get_object_taxonomies( $post->post_type, 'names' );
				if ( ! empty( $taxonomies ) ) {
					foreach ( $taxonomies as $tax ) {
						if ( 'post_tag' !== $tax && is_taxonomy_hierarchical( $tax ) ) {
							$taxonomy = $tax;
							break;
						}
					}
					if ( empty( $taxonomy ) ) {
						$taxonomy = reset( $taxonomies );
					}
				}
			}
		} elseif ( 'custom_taxonomy' === $settings['taxonomy_source'] ) {
			$taxonomy = ! empty( $settings['custom_taxonomy'] ) ? $settings['custom_taxonomy'] : '';
		} else {
			$taxonomy = ! empty( $settings['taxonomy_source'] ) ? $settings['taxonomy_source'] : 'category';
		}

		if ( empty( $taxonomy ) ) {
			return; // Empty state: render nothing
		}

		$terms = get_the_terms( $post->ID, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return; // Empty state: render nothing
		}

		$display_style = ! empty( $settings['display_style'] ) ? $settings['display_style'] : 'inline';
		$this->add_render_attribute( 'container', 'class', 'tv-post-terms-container tv-style-' . esc_attr( $display_style ) );

		$separator_html = '';
		if ( 'inline' === $display_style && ! empty( $settings['separator_type'] ) && 'none' !== $settings['separator_type'] ) {
			$sep_char = '';
			switch ( $settings['separator_type'] ) {
				case 'comma':
					$sep_char = ',';
					break;
				case 'dot':
					$sep_char = '•';
					break;
				case 'slash':
					$sep_char = '/';
					break;
				case 'pipe':
					$sep_char = '|';
					break;
				case 'custom':
					$sep_char = ! empty( $settings['custom_separator'] ) ? $settings['custom_separator'] : '';
					break;
			}
			if ( ! empty( $sep_char ) ) {
				$separator_html = '<span class="tv-post-terms-separator">' . esc_html( $sep_char ) . '</span>';
			}
		}

		$max_terms = ! empty( $settings['max_terms'] ) ? (int) $settings['max_terms'] : 0;
		$remaining = 0;
		if ( $max_terms > 0 && count( $terms ) > $max_terms ) {
			$remaining = count( $terms ) - $max_terms;
			$terms     = array_slice( $terms, 0, $max_terms );
		}

		$label_html     = '';
		$label_position = ! empty( $settings['label_position'] ) ? $settings['label_position'] : 'before';
		if ( 'yes' === $settings['show_label'] && ! empty( $settings['custom_label'] ) ) {
			$label_html = '<span class="tv-post-terms-label">' . esc_html( $settings['custom_label'] ) . '</span>';
		}

		$icon_html     = '';
		$icon_position = ! empty( $settings['icon_position'] ) ? $settings['icon_position'] : 'before';
		if ( 'yes' === $settings['enable_icon'] && ! empty( $settings['custom_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['custom_icon'], array( 'aria-hidden' => 'true' ) );
			$rendered_icon = ob_get_clean();
			$icon_html     = '<span class="tv-post-terms-icon tv-icon-' . esc_attr( $icon_position ) . '">' . $rendered_icon . '</span>';
		}

		$link_terms   = 'yes' === $settings['link_terms'];
		$open_new_tab = 'yes' === $settings['open_new_tab'];
		$nofollow     = 'yes' === $settings['nofollow'];

		$rel = '';
		if ( $nofollow ) {
			$rel .= 'nofollow ';
		}
		if ( $open_new_tab ) {
			$rel .= 'noopener noreferrer';
		}
		$rel = trim( $rel );

		$total_terms = count( $terms );
		?>
		<div <?php $this->print_render_attribute_string( 'container' ); ?>>
			<?php
			if ( 'before' === $label_position && ! empty( $label_html ) ) {
				echo wp_kses_post( $label_html );
			}

			foreach ( $terms as $index => $term ) {
				$term_link = get_term_link( $term );
				$term_name = esc_html( $term->name );

				echo '<span class="tv-post-term-item">';

				if ( $link_terms && ! is_wp_error( $term_link ) ) {
					echo '<a href="' . esc_url( $term_link ) . '"';
					if ( $open_new_tab ) {
						echo ' target="_blank"';
					}
					if ( ! empty( $rel ) ) {
						echo ' rel="' . esc_attr( $rel ) . '"';
					}
					echo '>';
				}

				if ( 'before' === $icon_position && ! empty( $icon_html ) ) {
					echo wp_kses_post( $icon_html );
				}

				echo '<span class="tv-post-term-name">' . esc_html( $term_name ) . '</span>';

				if ( 'after' === $icon_position && ! empty( $icon_html ) ) {
					echo wp_kses_post( $icon_html );
				}

				if ( $link_terms && ! is_wp_error( $term_link ) ) {
					echo '</a>';
				}

				echo '</span>';

				if ( $index < ( $total_terms - 1 ) && ! empty( $separator_html ) ) {
					echo wp_kses_post( $separator_html );
				}
			}

			if ( 'yes' === $settings['show_remaining'] && $remaining > 0 ) {
				echo '<span class="tv-post-terms-remaining">+' . esc_html( $remaining ) . '</span>';
			}

			if ( 'after' === $label_position && ! empty( $label_html ) ) {
				echo wp_kses_post( $label_html );
			}
			?>
		</div>
		<?php
	}
}
