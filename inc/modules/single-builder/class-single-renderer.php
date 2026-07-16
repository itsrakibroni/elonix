<?php
/**
 * Elonix Single Builder Custom Frontend Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Single_Renderer {

	/**
	 * Prevention lock for recursive template loading.
	 *
	 * @var bool
	 */
	private $is_processing = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'load_single_template_canvas' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_single_template_styles' ), 999 );
		add_filter( 'body_class', array( $this, 'add_single_body_classes' ) );
	}

	/**
	 * Centralized Helper: Check if single builder should render inside the current request.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function should_render_single_template() {
		// 1. Elementor Editor protection
		if ( class_exists( '\Elementor\Plugin' ) ) {
			// Do not render Single Builder inside the Elementor Editor top-level frame.
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		// Prevent single template from hijacking itself (fallback to preview engine)
		if ( 'tv_single' === get_post_type() ) {
			return false;
		}

		// 2. Elementor Query parameters check (Keep edit mode protection)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
			return false;
		}

		// 3. Elementor AJAX requests check
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
			return false;
		}

		// 4. Do not render in admin screen
		if ( is_admin() ) {
			return false;
		}

		// 5. Query context: Must be a singular page (post, page, custom post type)
		// Exclude templates that are not meant for singular content.
		if ( ! is_singular() ) {
			return false;
		}

		// Prevent overriding Elonix Archive or Search Builder or Popups
		if ( is_singular( array( 'tv_archive', 'tv_search', 'tv_single', 'elementor_library' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve matched single template based on displaying conditions.
	 * Priority rules:
	 * 1. Specific Post/Page/CPT
	 * 2. Specific Category/Tag/Taxonomy
	 * 3. Specific Post Type
	 * 4. All Posts / All Pages
	 * 5. All Singular
	 *
	 * @return int Matching WP_Post ID, or 0 if none found.
	 */
	public function get_active_matched_single() {
		if ( class_exists( 'Elonix_Assignment_Engine' ) ) {
			return \Elonix_Assignment_Engine::instance()->get_matching_template( 'tv_single' );
		}
		return 0;
	}

	/**
	 * Hijack theme layout template load and replace with custom template canvas.
	 */
	public function load_single_template_canvas( $template ) {
		if ( $this->is_processing ) {
			return $template;
		}

		if ( ! $this->should_render_single_template() ) {
			return $template;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_single();
		$this->is_processing = false;

		if ( ! $matched_id ) {
			return $template;
		}

		$custom_canvas = ELONIX_ACC_PATH . 'inc/modules/single-builder/templates/single-canvas.php';
		if ( file_exists( $custom_canvas ) ) {
			// Save matched ID to query state for canvas fetch
			set_query_var( 'tv_matched_single_id', $matched_id );
			return $custom_canvas;
		}

		return $template;
	}

	/**
	 * Enqueue Elementor CSS styles for the matched single template.
	 */
	public function enqueue_single_template_styles() {
		if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			return;
		}

		if ( ! $this->should_render_single_template() ) {
			return;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_single();
		$this->is_processing = false;

		if ( $matched_id ) {
			// Trigger Elementor post render action to register the template ID in Atomic_Styles_Manager
			do_action( 'elementor/post/render', $matched_id );

			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				\Elementor\Plugin::$instance->frontend->enqueue_styles();
			}

			$css_file = new \Elementor\Core\Files\CSS\Post( $matched_id );
			$css_file->enqueue();

			// Trigger Elementor Atomic Styles Manager to enqueue global and local container styles
			do_action( 'elementor/frontend/after_enqueue_post_styles' );
		}
	}

	/**
	 * Inject Elementor body classes for consistent flexbox container rendering across all themes.
	 */
	public function add_single_body_classes( $classes ) {
		if ( ! $this->should_render_single_template() ) {
			return $classes;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_single();
		$this->is_processing = false;

		if ( $matched_id ) {
			$classes[] = 'elementor-page';
			$classes[] = 'elementor-page-' . $matched_id;
			$classes[] = 'elementor-template-full-width';
		}

		return $classes;
	}
}
