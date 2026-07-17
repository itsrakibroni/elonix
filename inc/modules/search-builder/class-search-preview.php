<?php
/**
 * Elonix Search Builder Custom Preview Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.Files.FileName -- Search Builder keeps Archive Builder file naming to preserve shared module architecture.
/**
 * Elonix Search Builder Custom Preview Engine.
 */
class Elonix_Search_Preview {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'mock_search_query' ), 5 );
		add_filter( 'the_posts', array( $this, 'inject_dummy_posts_in_editor' ), 10, 2 );
	}

	/**
	 * Check if the current request is editing or previewing a Search Builder template.
	 *
	 * @return bool
	 */
	public function is_editing_search_template() {
		if ( is_admin() ) {
			return false;
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$post_id = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		if ( isset( $_GET['elementor-preview'] ) ) {
			$post_id = intval( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( isset( $_GET['p'] ) ) {
			$post_id = intval( $_GET['p'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( is_singular( 'es_search_template' ) ) {
			$post_id = get_the_ID();
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
			return ( $post && 'es_search_template' === $post->post_type );
		}

		return false;
	}

	/**
	 * Mock search main query context for non-Elementor frontend preview requests.
	 *
	 * @param WP_Query $query Main query.
	 */
	public function mock_search_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $this->is_editing_search_template() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview routing check.
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor AJAX routing check.
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) ) {
			return;
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return;
			}
		}

		$post_id = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		if ( isset( $_GET['elementor-preview'] ) ) {
			$post_id = intval( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor preview context check.
		} elseif ( is_singular( 'es_search_template' ) ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		$preview_term = get_post_meta( $post_id, '_es_search_preview_term', true );
		if ( '' === $preview_term ) {
			$preview_term = 'sample';
		}

		$query->set( 's', sanitize_text_field( $preview_term ) );
		$query->set( 'post_type', 'post' );
		$query->set( 'posts_per_page', 6 );
		$query->set( 'post_status', 'publish' );

		$query->is_search   = true;
		$query->is_home     = false;
		$query->is_archive  = false;
		$query->is_singular = false;
	}

	/**
	 * Inject dummy posts when editor preview loops are empty.
	 *
	 * @param array    $posts Posts.
	 * @param WP_Query $query Query.
	 * @return array
	 */
	public function inject_dummy_posts_in_editor( $posts, $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return $posts;
		}

		if ( ! $this->is_editing_search_template() ) {
			return $posts;
		}

		if ( empty( $posts ) ) {
			$posts = array();
			for ( $i = 1; $i <= 4; $i++ ) {
				$posts[] = $this->create_dummy_post_object( $i );
			}
		}

		return $posts;
	}

	/**
	 * Create a dummy WP_Post object for Search Builder previews.
	 *
	 * @param int $index Dummy post index.
	 * @return WP_Post
	 */
	private function create_dummy_post_object( $index ) {
		$post                = new stdClass();
		$post->ID            = -8999 - $index;
		$post->post_author   = get_current_user_id() ? get_current_user_id() : 1;
		$post->post_date     = current_time( 'mysql' );
		$post->post_date_gmt = current_time( 'mysql', 1 );
		/* translators: %d: sample preview result number. */
		$post->post_title            = sprintf( __( 'Sample Search Result #%d', 'elonix' ), $index );
		$post->post_content          = __( 'This is sample search result content generated automatically by Elonix Search Preview Engine to prevent empty layouts.', 'elonix' );
		$post->post_excerpt          = __( 'Sample placeholder excerpt for a search result preview.', 'elonix' );
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
		$post->post_password         = '';
		$post->post_name             = 'sample-search-result-' . $index;
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
// phpcs:enable WordPress.Files.FileName
