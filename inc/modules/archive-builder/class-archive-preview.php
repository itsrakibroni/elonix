<?php
/**
 * Elonix Archive Builder Custom Preview and Mocks Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Archive_Preview {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'mock_archive_query' ), 5 );
		add_filter( 'the_posts', array( $this, 'inject_dummy_posts_in_editor' ), 10, 2 );
	}

	/**
	 * Check if the current request is editing or previewing a elonix_archive template.
	 *
	 * @return bool True if editing/previewing elonix_archive template, false otherwise.
	 */
	public function is_editing_archive_template() {
		if ( is_admin() ) {
			return false;
		}

		// Check if Elementor is active
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$post_id = 0;
		// Read-only: identifies whether we're inside Elementor's own gated preview/editor
		// context (is_admin() already excluded above); value is intval-cast and only ever used
		// to look up a post type for a boolean return, never output or acted on.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		if ( isset( $_GET['elementor-preview'] ) ) {
			$post_id = intval( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( isset( $_GET['p'] ) ) {
			$post_id = intval( $_GET['p'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( is_singular( 'elonix_archive' ) ) {
			$post_id = get_the_ID();
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
			return ( $post && 'elonix_archive' === $post->post_type );
		}

		return false;
	}

	/**
	 * Mock standard main query parameters inside the editor screen to mimic chosen preview targets.
	 */
	public function mock_archive_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $this->is_editing_archive_template() ) {
			return;
		}

		// Prevent modifying main query type flags inside Elementor contexts to avoid "Content area not found" error
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview routing check.
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor AJAX routing check.
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		if ( isset( $_GET['elementor-preview'] ) ) {
			$post_id = intval( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( is_singular( 'elonix_archive' ) ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		// Load configured sample preview variables
		$preview_type = get_post_meta( $post_id, '_es_archive_preview_type', true );
		$preview_val  = get_post_meta( $post_id, '_es_archive_preview_id', true );

		// Enforce pagination matches
		$query->set( 'posts_per_page', 6 );
		$query->set( 'post_status', 'publish' );

		// Set target queries depending on selection types
		switch ( $preview_type ) {
			case 'category':
				if ( ! empty( $preview_val ) ) {
					if ( is_numeric( $preview_val ) ) {
						$query->set( 'cat', intval( $preview_val ) );
					} else {
						$query->set( 'category_name', sanitize_text_field( $preview_val ) );
					}
				}
				$query->is_archive  = true;
				$query->is_category = true;
				$query->is_home     = false;
				$query->is_singular = false;
				break;

			case 'tag':
				if ( ! empty( $preview_val ) ) {
					if ( is_numeric( $preview_val ) ) {
						$query->set( 'tag_id', intval( $preview_val ) );
					} else {
						$query->set( 'tag', sanitize_text_field( $preview_val ) );
					}
				}
				$query->is_archive  = true;
				$query->is_tag      = true;
				$query->is_home     = false;
				$query->is_singular = false;
				break;

			case 'author':
				if ( ! empty( $preview_val ) ) {
					$query->set( 'author', intval( $preview_val ) );
				}
				$query->is_archive  = true;
				$query->is_author   = true;
				$query->is_home     = false;
				$query->is_singular = false;
				break;

			case 'cpt':
				if ( ! empty( $preview_val ) ) {
					$query->set( 'post_type', sanitize_text_field( $preview_val ) );
				}
				$query->is_archive           = true;
				$query->is_post_type_archive = true;
				$query->is_home              = false;
				$query->is_singular          = false;
				break;

			default: // standard blog / general posts archive preview
				$query->set( 'post_type', 'post' );
				$query->is_home     = true;
				$query->is_archive  = false;
				$query->is_singular = false;
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

		if ( ! $this->is_editing_archive_template() ) {
			return $posts;
		}

		// Inject mock posts context only if queries output empty datasets
		if ( empty( $posts ) ) {
			$posts = array();
			for ( $i = 1; $i <= 4; $i++ ) {
				$posts[] = $this->create_dummy_post_object( $i );
			}
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
		$post->post_content          = __( 'This is a sample post content generated automatically by Elonix Archive Preview Engine to prevent empty layouts.', 'elonix' );
		$post->post_excerpt          = __( 'Sample placeholder text excerpt describing layout styles.', 'elonix' );
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
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
