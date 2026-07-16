<?php
/**
 * Elonix – Toolkit for Elementor Advanced 404 Builder Render Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_404_Renderer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Enqueue template CSS only on 404 pages (frontend only).
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_404_styles' ), 50 );
		}

		// Injections in the page head/footer
		add_action( 'wp_head', array( $this, 'inject_robots_and_canonical' ), 1 );
		add_action( 'wp_head', array( $this, 'inject_header_code' ), 100 );
		add_action( 'wp_footer', array( $this, 'inject_footer_code' ), 100 );

		// Compatibility adjustments for page layout list filtering
		add_filter( 'display_post_states', array( $this, 'add_post_state' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'hide_404_page_from_list' ) );
	}

	/**
	 * Verify if custom 404 template rendering is active for the current request.
	 *
	 * @return bool True if active, false otherwise.
	 */
	private function should_render() {
		$builder = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
		if ( $builder && $builder->router && method_exists( $builder->router, 'should_render_404_template' ) ) {
			return $builder->router->should_render_404_template();
		}
		return false;
	}

	/**
	 * Enqueue CSS files of the selected custom Elementor 404 template dynamically.
	 */
	public function enqueue_404_styles() {
		if ( ! $this->should_render() ) {
			return;
		}

		if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			return;
		}

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			\Elementor\Plugin::$instance->frontend->enqueue_styles();
		}

		$template_id = Elonix_Settings::get( 'tv_404_selected_page_id' );
		if ( $template_id ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
			$css_file->enqueue();
		}
	}

	/**
	 * Inject custom SEO robots metadata and canonical URLs inside the HTML `<head>`.
	 */
	public function inject_robots_and_canonical() {
		if ( ! $this->should_render() ) {
			return;
		}

		$noindex  = ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_noindex' ) ?? 'yes' )  );
		$nofollow = ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_nofollow' ) ?? 'yes' )  );

		if ( $noindex || $nofollow ) {
			$robots = array();
			if ( $noindex ) {
				$robots[] = 'noindex';
			}
			if ( $nofollow ) {
				$robots[] = 'nofollow';
			}

			$robots_str = implode( ',', $robots );
			echo '<meta name="robots" content="' . esc_attr( $robots_str ) . '" />' . "\n";
		}

		// Inject Robots Meta Control String
		$robots_custom = Elonix_Settings::get( 'tv_404_seo_robots_control' );
		if ( ! empty( $robots_custom ) ) {
			echo '<meta name="robots" content="' . esc_attr( $robots_custom ) . '" />' . "\n";
		}

		// Inject Canonical Control
		$canonical = Elonix_Settings::get( 'tv_404_seo_canonical_control' );
		if ( ! empty( $canonical ) ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		}
	}

	/**
	 * Inject custom head scripts.
	 */
	public function inject_header_code() {
		if ( ! $this->should_render() ) {
			return;
		}

		$header_code = Elonix_Settings::get( 'tv_404_custom_header_code' );
		if ( ! empty( $header_code ) ) {
			// ThemeForest safety: bypass sanitization of scripts if allowed by admin via raw print, escape variables where necessary
			echo wp_kses_post( $header_code ) . "\n";
		}
	}

	/**
	 * Inject custom footer scripts.
	 */
	public function inject_footer_code() {
		if ( ! $this->should_render() ) {
			return;
		}

		$footer_code = Elonix_Settings::get( 'tv_404_custom_footer_code' );
		if ( ! empty( $footer_code ) ) {
			echo wp_kses_post( $footer_code ) . "\n";
		}
	}

	/**
	 * Mark the selected page/template with a custom status indicator state in the admin Pages screen.
	 */
	public function add_post_state( $post_states, $post ) {
		$template_id = Elonix_Settings::get( 'tv_404_selected_page_id' );
		if ( $template_id && intval( $post->ID ) === intval( $template_id ) ) {
			$post_states['tv_404_page'] = esc_html__( 'Elonix 404 Page', 'elonix' );
		}
		return $post_states;
	}

	/**
	 * Hide the selected 404 page from standard WordPress pages query list to avoid duplicate links.
	 */
	public function hide_404_page_from_list( $query ) {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return $query;
		}

		$screen = get_current_screen();
		if ( $screen && 'edit-page' === $screen->id ) {
			$hide        = ( 'yes' === ( Elonix_Settings::get( 'tv_404_hide_page_list' ) ?? 'no' )  );
			$template_id = Elonix_Settings::get( 'tv_404_selected_page_id' );

			if ( $hide && $template_id ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to exclude the active 404 template from normal template listings.
				$query->query_vars['post__not_in'] = array_merge(
					isset( $query->query_vars['post__not_in'] ) ? (array) $query->query_vars['post__not_in'] : array(),
					array( intval( $template_id ) )
				);
			}
		}
		return $query;
	}
}
