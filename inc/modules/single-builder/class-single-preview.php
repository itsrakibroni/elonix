<?php
/**
 * Elonix Single Builder Custom Preview and Mocks Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Single_Preview {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'mock_single_query' ), 5 );
		add_filter( 'the_posts', array( $this, 'inject_dummy_posts_in_editor' ), 10, 2 );
	}

	/**
	 * Check if the current request is editing or previewing a tv_single template.
	 *
	 * @return bool True if editing/previewing tv_single template, false otherwise.
	 */
	public function is_editing_single_template() {
		if ( is_admin() ) {
			return false;
		}

		// Check if Elementor is active
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$post_id = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = intval( $_GET['elementor-preview'] );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_GET['p'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = intval( $_GET['p'] );
		} elseif ( is_singular( 'tv_single' ) ) {
			$post_id = get_the_ID();
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
			return ( $post && 'tv_single' === $post->post_type );
		}

		return false;
	}

	/**
	 * Mock standard main query parameters inside the editor screen to mimic chosen preview targets.
	 */
	public function mock_single_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $this->is_editing_single_template() ) {
			return;
		}

		// Prevent modifying main query type flags inside Elementor contexts to avoid "Content area not found" error
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
			return;
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return;
			}
		}

		// Retrieve the target template ID being designed
		$post_id = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = intval( $_GET['elementor-preview'] );
		} elseif ( is_singular( 'tv_single' ) ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		// Load configured sample preview variables
		$preview_type = get_post_meta( $post_id, '_tv_single_preview_type', true );
		$preview_val  = get_post_meta( $post_id, '_tv_single_preview_id', true );

		$query->set( 'posts_per_page', 1 );
		$query->set( 'post_status', 'publish' );
		$query->is_singular = true;
		$query->is_archive  = false;
		$query->is_home     = false;

		// Set target queries depending on selection types
		switch ( $preview_type ) {
			case 'post':
				if ( ! empty( $preview_val ) ) {
					$query->set( 'p', intval( $preview_val ) );
				}
				$query->set( 'post_type', 'post' );
				$query->is_single = true;
				break;

			case 'page':
				if ( ! empty( $preview_val ) ) {
					$query->set( 'page_id', intval( $preview_val ) );
				}
				$query->set( 'post_type', 'page' );
				$query->is_page = true;
				break;

			case 'cpt':
				if ( ! empty( $preview_val ) ) {
					$query->set( 'post_type', sanitize_text_field( $preview_val ) );
				}
				$query->is_single = true;
				break;

			default: // standard post preview
				$query->set( 'post_type', 'post' );
				$query->is_single = true;
				break;
		}
	}

	/**
	 * Inject dummy template posts in editor preview loops if matching post queries returns empty.
	 */
	public function inject_dummy_posts_in_editor( $posts, $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return $posts;
		}

		if ( ! $this->is_editing_single_template() ) {
			return $posts;
		}

		// Inject mock posts context only if queries output empty datasets
		if ( empty( $posts ) ) {
			$posts   = array();
			$posts[] = $this->create_dummy_post_object( 1 );
		}

		return $posts;
	}

	/**
	 * Helper: Construct a dummy WP_Post object mapping.
	 */
	private function create_dummy_post_object( $index ) {
		$post                        = new stdClass();
		$post->ID                    = -9999 - $index;
		$post->post_author           = get_current_user_id() ? get_current_user_id() : 1;
		$post->post_date             = current_time( 'mysql' );
		$post->post_date_gmt         = current_time( 'mysql', 1 );
		/* translators: %d: string */
		$post->post_title            = sprintf( __( 'Sample Preview Post #%d', 'elonix' ), $index );
		$post->post_content          = __( 'This is a sample post content generated automatically by Elonix Single Preview Engine to prevent empty layouts. You can test your typography, spacing, and post widgets against this dummy content seamlessly.', 'elonix' );
		$post->post_excerpt          = __( 'Sample placeholder text excerpt describing layout styles.', 'elonix' );
		$post->post_status           = 'publish';
		$post->comment_status        = 'open';
		$post->ping_status           = 'open';
		$post->post_password         = '';
		$post->post_name             = 'sample-preview-post-' . $index;
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = current_time( 'mysql' );
		$post->post_modified_gmt     = current_time( 'mysql', 1 );
		$post->post_content_filtered = '';
		$post->post_parent           = 0;
		$post->guid                  = home_url( '/?p=' . $post->ID );
		$post->menu_order            = 0;
		$post->post_type             = 'post';
		$post->post_mime_type        = '';
		$post->comment_count         = 0;
		$post->filter                = 'raw';

		return new WP_Post( $post );
	}
}
