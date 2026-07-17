<?php
/**
 * Elonix – Toolkit for Elementor - Post Content Widget
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
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

class Elonix_Toolkit_Post_Content_Widget extends Elonix_Widget_Base {

	public function get_name() {
		return 'es-post-content';
	}

	public function get_title() {
		return esc_html__( 'Post Content', 'elonix' );
	}

	public function get_icon() {
		return 'eicon-post-content elonix-widget-icon';
	}

	public function get_keywords() {
		$keywords = parent::get_keywords();
		return array_merge( $keywords, array( 'post', 'content', 'the_content', 'dynamic', 'eskit', 'single' ) );
	}

	private function get_elementor_templates() {
		$templates = get_posts(
			array(
				'post_type'      => 'elementor_library',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);
		$options   = array( '' => esc_html__( 'None', 'elonix' ) );
		if ( ! empty( $templates ) ) {
			foreach ( $templates as $template ) {
				$options[ $template->ID ] = $template->post_title;
			}
		}
		return $options;
	}

	protected function register_controls() {

		// ==========================================
		// 1. Content Tab: Layout Configuration
		// ==========================================
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Layout', 'elonix' ),
			)
		);

		$this->add_control(
			'content_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'This widget acts as a dynamic bridge to the current post\'s native content. It does not accept manual input.', 'elonix' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$this->add_control(
			'enable_wrapper',
			array(
				'label'        => esc_html__( 'Enable Wrapper', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Required for alignment, animations, and custom typography scoping.', 'elonix' ),
			)
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'     => esc_html__( 'Content Width', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''       => esc_html__( 'Default', 'elonix' ),
					'full'   => esc_html__( 'Full Width', 'elonix' ),
					'boxed'  => esc_html__( 'Boxed', 'elonix' ),
					'custom' => esc_html__( 'Custom', 'elonix' ),
				),
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} .es-post-content' => 'width: 100%;',
					'{{WRAPPER}} .es-post-content.width-full' => 'max-width: 100%;',
					'{{WRAPPER}} .es-post-content.width-boxed' => 'max-width: 1140px; margin-left: auto; margin-right: auto;',
				),
			)
		);

		$this->add_responsive_control(
			'custom_width',
			array(
				'label'      => esc_html__( 'Custom Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1600,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'condition'  => array(
					'enable_wrapper' => 'yes',
					'content_width'  => 'custom',
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-content.width-custom' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => esc_html__( 'Justify', 'elonix' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} .es-post-content' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Content Tab: Slots
		// ==========================================
		$this->start_controls_section(
			'section_content_slots',
			array(
				'label' => esc_html__( 'Content Slots', 'elonix' ),
			)
		);

		$this->add_control(
			'before_content_template',
			array(
				'label'       => esc_html__( 'Before Content', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_elementor_templates(),
				'description' => esc_html__( 'Select a saved Elementor Template to inject BEFORE the post content.', 'elonix' ),
			)
		);

		$this->add_control(
			'after_content_template',
			array(
				'label'       => esc_html__( 'After Content', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_elementor_templates(),
				'description' => esc_html__( 'Select a saved Elementor Template to inject AFTER the post content.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Content Tab: Advanced Settings
		// ==========================================
		$this->start_controls_section(
			'section_advanced_settings',
			array(
				'label' => esc_html__( 'Advanced Settings', 'elonix' ),
			)
		);

		$this->add_control(
			'wrapper_id',
			array(
				'label'       => esc_html__( 'Reading Progress Anchor ID', 'elonix' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'es-reading-content',
				'description' => esc_html__( 'Add a unique ID to the wrapper for Table of Contents or Progress Bar targeting.', 'elonix' ),
				'condition'   => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'entrance_animation',
			array(
				'label'     => esc_html__( 'Entrance Animation', 'elonix' ),
				'type'      => Controls_Manager::ANIMATION,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// 2. Style Tab: Typography Scope
		// ==========================================
		$this->start_controls_section(
			'section_style_scope',
			array(
				'label'     => esc_html__( 'Styling Scope', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'typography_scope',
			array(
				'label'       => esc_html__( 'Apply Styles To', 'elonix' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'elonix',
				'options'     => array(
					'elonix' => esc_html__( 'Apply Elonix Styling', 'elonix' ),
					'native'     => esc_html__( 'Keep Native Editor Styling', 'elonix' ),
				),
				'description' => esc_html__( 'If set to Native, custom typography styles below will not forcefully override block editor styles.', 'elonix' ),
			)
		);

		$this->end_controls_section();

		// Helper var for CSS selectors targeting the scoped wrapper
		$scope = '{{WRAPPER}} .es-post-content.es-typography-elonix';

		// ==========================================
		// Style Tab: Paragraph
		// ==========================================
		$this->start_controls_section(
			'section_style_typography',
			array(
				'label'     => esc_html__( 'Paragraph & Text', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					$scope        => 'color: {{VALUE}};',
					$scope . ' p' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => $scope . ', ' . $scope . ' p',
			)
		);

		$this->add_responsive_control(
			'paragraph_margin',
			array(
				'label'      => esc_html__( 'Paragraph Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$scope . ' p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Headings
		// ==========================================
		$this->start_controls_section(
			'section_style_headings',
			array(
				'label'     => esc_html__( 'Headings (H1 - H6)', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					$scope . ' h1, ' . $scope . ' h2, ' . $scope . ' h3, ' . $scope . ' h4, ' . $scope . ' h5, ' . $scope . ' h6' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => $scope . ' h1, ' . $scope . ' h2, ' . $scope . ' h3, ' . $scope . ' h4, ' . $scope . ' h5, ' . $scope . ' h6',
			)
		);

		$this->add_responsive_control(
			'heading_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$scope . ' h1, ' . $scope . ' h2, ' . $scope . ' h3, ' . $scope . ' h4, ' . $scope . ' h5, ' . $scope . ' h6' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Links
		// ==========================================
		$this->start_controls_section(
			'section_style_links',
			array(
				'label'     => esc_html__( 'Links', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_links_style' );

		$this->start_controls_tab(
			'tab_links_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_ACCENT,
				),
				'selectors' => array(
					$scope . ' a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typography',
				'selector' => $scope . ' a',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_links_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'link_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Lists
		// ==========================================
		$this->start_controls_section(
			'section_style_lists',
			array(
				'label'     => esc_html__( 'Lists (UL / OL)', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'list_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' ul, ' . $scope . ' ol' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'list_spacing',
			array(
				'label'      => esc_html__( 'Item Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					$scope . ' li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'list_indent',
			array(
				'label'      => esc_html__( 'Indentation', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					$scope . ' ul, ' . $scope . ' ol' => 'padding-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Images & Media
		// ==========================================
		$this->start_controls_section(
			'section_style_images',
			array(
				'label'     => esc_html__( 'Images & Media', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$scope . ' img, ' . $scope . ' iframe' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_shadow',
				'selector' => $scope . ' img, ' . $scope . ' iframe',
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Quotes & Blockquote
		// ==========================================
		$this->start_controls_section(
			'section_style_quotes',
			array(
				'label'     => esc_html__( 'Blockquote', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'quote_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' blockquote'   => 'color: {{VALUE}};',
					$scope . ' blockquote p' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quote_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' blockquote' => 'border-left-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'quote_typography',
				'selector' => $scope . ' blockquote, ' . $scope . ' blockquote p',
			)
		);

		$this->add_responsive_control(
			'quote_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					$scope . ' blockquote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Selection & Highlight
		// ==========================================
		$this->start_controls_section(
			'section_style_selection',
			array(
				'label'     => esc_html__( 'Text Selection', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
				),
			)
		);

		$this->add_control(
			'selection_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' *::selection'      => 'background-color: {{VALUE}};',
					$scope . ' *::-moz-selection' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'selection_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$scope . ' *::selection'      => 'color: {{VALUE}};',
					$scope . ' *::-moz-selection' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// ==========================================
		// Style Tab: Responsive Container Spacing
		// ==========================================
		$this->start_controls_section(
			'section_style_container',
			array(
				'label'     => esc_html__( 'Responsive Container', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_wrapper' => 'yes',
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
					'{{WRAPPER}} .es-post-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .es-post-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		static $render_depth = 0;
		++$render_depth;

		try {
			if ( $render_depth > 1 ) {
				return;
			}

			$settings = $this->get_settings_for_display();

			// Ensure we're pulling the exact queried object context when inside Theme Builders.
			$post_id = get_queried_object_id();

			// Configuration flags
			$enable_wrapper   = isset( $settings['enable_wrapper'] ) ? ( $settings['enable_wrapper'] === 'yes' ) : true;
			$typography_scope = ! empty( $settings['typography_scope'] ) ? $settings['typography_scope'] : 'elonix';
			$content_width    = ! empty( $settings['content_width'] ) ? $settings['content_width'] : '';

			// Prevent infinite recursion: If the current post is a Elonix Single Template,
			// we must NOT render its own content. Instead, we should render the preview target.
			if ( 'es_single' === get_post_type( $post_id ) ) {
				$preview_id = get_post_meta( $post_id, '_es_single_preview_id', true );
				if ( ! empty( $preview_id ) && get_post( $preview_id ) ) {
					$post_id = intval( $preview_id );
				} else {
					// Fallback to the latest standard post
					$latest = get_posts(
						array(
							'numberposts' => 1,
							'post_type'   => 'post',
							'post_status' => 'publish',
						)
					);
					if ( ! empty( $latest ) ) {
						$post_id = $latest[0]->ID;
					} else {
						$post_id = 0; // Trigger dummy notice later
					}
				}
			}

			// Wrapper Classes
			$wrapper_classes   = array( 'es-post-content', 'es-clearfix' );
			$wrapper_classes[] = 'es-typography-' . $typography_scope;

			if ( ! empty( $content_width ) ) {
				$wrapper_classes[] = 'width-' . $content_width;
			}

			if ( ! empty( $settings['entrance_animation'] ) && 'none' !== $settings['entrance_animation'] ) {
				$wrapper_classes[] = 'animated ' . $settings['entrance_animation'];
			}

			$wrapper_id_attr = ! empty( $settings['wrapper_id'] ) ? ' id="' . esc_attr( $settings['wrapper_id'] ) . '"' : '';

			// 1. Render Before Content Slot
			if ( ! empty( $settings['before_content_template'] ) ) {
				if ( class_exists( '\Elementor\Plugin' ) ) {
					echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $settings['before_content_template'] ); // phpcs:ignore
				}
			}

			// 2. Open Wrapper
			if ( $enable_wrapper ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"' . $wrapper_id_attr . '>';
			}

			if ( 0 === $post_id ) {
				echo '<div class="es-dummy-content"><p style="padding: 30px; text-align: center; border: 2px dashed #ccc; color: #777;">' . esc_html__( 'Post Content will appear here. Please publish a post to see the preview.', 'elonix' ) . '</p></div>';
			} elseif ( post_password_required( $post_id ) ) {
				echo get_the_password_form( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				$queried_post = get_post( $post_id );
				if ( $queried_post instanceof \WP_Post ) {
					// We temporarily switch global post context perfectly for the_content filter
					// without permanent mutations.
					global $post;
					$original_post = $post;

					// Setup post data temporarily
					$post = $queried_post;
					setup_postdata( $post );

					// Determine if we are previewing a completely different post inside the editor
					$is_previewing_different_post = false;
					if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
						$elementor_preview_id = 0;
						if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$elementor_preview_id = intval( wp_unslash( $_GET['elementor-preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						} elseif ( isset( $_POST['editor_post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
							$elementor_preview_id = intval( wp_unslash( $_POST['editor_post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
						} else {
							$elementor_preview_id = \Elementor\Plugin::$instance->editor->get_post_id();
						}

						if ( $elementor_preview_id && (int) $elementor_preview_id !== (int) $post_id ) {
							$is_previewing_different_post = true;
						}
					}

					if ( $is_previewing_different_post ) {
						$document = \Elementor\Plugin::$instance->documents->get( $post_id );
						if ( $document && $document->is_built_with_elementor() ) {
							// Render visual elements manually since the_content ignores it in preview mode.
							echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post_id, true ); // phpcs:ignore
						} else {
							$content = get_the_content();
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
							$content = apply_filters( 'the_content', $content );
							$content = str_replace( ']]>', ']]&gt;', $content );
							echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					} else {
						$content = get_the_content();
						// Apply filters. Elementor natively hooks into 'the_content' to inject both
						// frontend builder content and preview builder wrapper containers.
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
						$content = apply_filters( 'the_content', $content );
						$content = str_replace( ']]>', ']]&gt;', $content );
						echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}

					// Restore immediately
					$post = $original_post;
					if ( $post ) {
						setup_postdata( $post );
					} else {
						wp_reset_postdata();
					}
				}
			}

			// 3. Close Wrapper
			if ( $enable_wrapper ) {
				echo '</div>';
			}

			// 4. Render After Content Slot
			if ( ! empty( $settings['after_content_template'] ) ) {
				if ( class_exists( '\Elementor\Plugin' ) ) {
					echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $settings['after_content_template'] ); // phpcs:ignore
				}
			}
		} finally {
			// Release Recursion Lock safely
			--$render_depth;
		}
	}
}
