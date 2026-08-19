<?php
/**
 * Elonix Archive Builder Custom Frontend Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Archive_Renderer {

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
		add_filter( 'template_include', array( $this, 'load_archive_template_canvas' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_archive_template_styles' ), 50 );
		add_filter( 'body_class', array( $this, 'add_archive_body_classes' ) );
	}

	/**
	 * Centralized Helper: Check if archive builder should render inside the current request.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function should_render_archive_template() {
		// 1. Elementor Editor protection
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return false;
			}
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		// 2. Elementor Query parameters check
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview routing check.
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
			return false;
		}

		// 3. Elementor AJAX requests check
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor AJAX routing check.
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
			return false;
		}

		// 4. Do not render in admin screen
		if ( is_admin() ) {
			return false;
		}

		// 5. Query context: Must be an archive page or the blog home page
		if ( ! is_archive() && ! is_home() ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve matched archive template based on displaying conditions.
	 *
	 * @return int Matching WP_Post ID, or 0 if none found.
	 */
	public function get_active_matched_archive() {
		if ( class_exists( 'Elonix_Assignment_Engine' ) ) {
			return \Elonix_Assignment_Engine::instance()->get_matching_template( 'elonix_archive' );
		}
		return 0;
	}

	/**
	 * Hijack theme layout template load and replace with custom template canvas.
	 */
	public function load_archive_template_canvas( $template ) {
		if ( $this->is_processing ) {
			return $template;
		}

		if ( ! $this->should_render_archive_template() ) {
			return $template;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_archive();
		$this->is_processing = false;

		if ( ! $matched_id ) {
			return $template;
		}

		$custom_canvas = ELONIX_ACC_PATH . 'inc/modules/archive-builder/templates/archive-canvas.php';
		if ( file_exists( $custom_canvas ) ) {
			// Save matched ID to query state for canvas fetch
			set_query_var( 'es_matched_archive_id', $matched_id );
			return $custom_canvas;
		}

		return $template;
	}

	/**
	 * Enqueue Elementor CSS styles for the matched archive template.
	 */
	public function enqueue_archive_template_styles() {
		if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			return;
		}

		if ( ! $this->should_render_archive_template() ) {
			return;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_archive();
		$this->is_processing = false;

		if ( $matched_id ) {
			// Trigger Elementor post render action to register the template ID in Atomic_Styles_Manager
			do_action( 'elementor/post/render', $matched_id );

			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				\Elementor\Plugin::$instance->frontend->enqueue_styles();
			}

			if ( class_exists( '\Elementor\Core\Files\CSS\Global_CSS' ) ) {
				$global_css = new \Elementor\Core\Files\CSS\Global_CSS();
				$global_css->enqueue();
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
	public function add_archive_body_classes( $classes ) {
		if ( ! $this->should_render_archive_template() ) {
			return $classes;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_archive();
		$this->is_processing = false;

		if ( $matched_id ) {
			$classes[] = 'elementor-page';
			$classes[] = 'elementor-page-' . $matched_id;
			$classes[] = 'elementor-template-full-width';
		}

		return $classes;
	}
}
