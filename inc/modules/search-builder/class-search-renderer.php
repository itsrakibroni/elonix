<?php
/**
 * Elonix Search Builder Custom Frontend Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.Files.FileName -- Search Builder keeps Archive Builder file naming to preserve shared module architecture.
/**
 * Elonix Search Builder Custom Frontend Renderer.
 */
class Elonix_Search_Renderer {

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
		add_filter( 'template_include', array( $this, 'load_search_template_canvas' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_search_template_styles' ), 50 );
		add_filter( 'body_class', array( $this, 'add_search_body_classes' ) );
	}

	/**
	 * Check if Search Builder should render on the current request.
	 *
	 * @return bool
	 */
	public function should_render_search_template() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return false;
			}
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview routing check.
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor AJAX routing check.
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) ) {
			return false;
		}

		if ( is_admin() ) {
			return false;
		}

		return is_search();
	}

	/**
	 * Retrieve matched Search Builder template.
	 *
	 * @return int Matching template post ID, or 0.
	 */
	public function get_active_matched_search() {
		$templates = get_posts(
			array(
				'post_type'      => 'es_search_template',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $templates ) ) {
			return 0;
		}

		global $wp_query;
		$has_results = ( $wp_query instanceof WP_Query && ! empty( $wp_query->posts ) );

		foreach ( $templates as $tpl ) {
			$type = get_post_meta( $tpl->ID, '_es_search_type', true );

			if ( empty( $type ) ) {
				$type = 'search_results';
			}

			if ( 'all_search' === $type ) {
				return $tpl->ID;
			}

			if ( 'search_results' === $type && $has_results ) {
				return $tpl->ID;
			}

			if ( 'empty_search' === $type && ! $has_results ) {
				return $tpl->ID;
			}
		}

		return 0;
	}

	/**
	 * Replace the active theme template with the Search Builder canvas.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function load_search_template_canvas( $template ) {
		if ( $this->is_processing ) {
			return $template;
		}

		if ( ! $this->should_render_search_template() ) {
			return $template;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_search();
		$this->is_processing = false;

		if ( ! $matched_id ) {
			return $template;
		}

		$custom_canvas = ELONIX_ACC_PATH . 'inc/modules/search-builder/templates/search-canvas.php';
		if ( file_exists( $custom_canvas ) ) {
			set_query_var( 'es_matched_search_id', $matched_id );
			return $custom_canvas;
		}

		return $template;
	}

	/**
	 * Enqueue Elementor CSS for the matched Search Builder template.
	 */
	public function enqueue_search_template_styles() {
		if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			return;
		}

		if ( ! $this->should_render_search_template() ) {
			return;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_search();
		$this->is_processing = false;

		if ( $matched_id ) {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Elementor public hook name uses slashes.
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
	public function add_search_body_classes( $classes ) {
		if ( ! $this->should_render_search_template() ) {
			return $classes;
		}

		$this->is_processing = true;
		$matched_id          = $this->get_active_matched_search();
		$this->is_processing = false;

		if ( $matched_id ) {
			$classes[] = 'elementor-page';
			$classes[] = 'elementor-page-' . $matched_id;
			$classes[] = 'elementor-template-full-width';
		}

		return $classes;
	}
}
// phpcs:enable WordPress.Files.FileName
