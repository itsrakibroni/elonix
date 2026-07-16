<?php
/**
 * Elonix Search Results AJAX Handler.
 *
 * Registers and handles AJAX hooks for Search Results pagination, load-more,
 * and infinite-scroll. Delegates rendering to the shared Post Block Renderer.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * ARCHITECTURE NOTE
 * ─────────────────────────────────────────────────────────────────────────────
 * This file is loaded at plugins_loaded (priority 25) — BEFORE Elementor
 * fires its own hooks. Loading any Elementor widget class here would cause:
 *
 *   Fatal Error: Class "Elementor\Widget_Base" not found
 *
 * This handler therefore NEVER loads:
 *   - Elonix_Widget_Base
 *   - Elonix_Toolkit_Search_Results_Widget
 *   - Elonix_Toolkit_Post_Block_Widget
 *
 * It uses ONLY:
 *   - Elonix_Toolkit_Search_Results_Query_Helper  (helper-query.php)
 *   - Elonix_Toolkit_Search_Results_Empty_State_Helper (helper-empty-state.php)
 *   - Elonix_Toolkit_Post_Block_Query_Helper      (tv-post-block/helper-query.php)
 *   - Elonix_Toolkit_Post_Block_Renderer          (tv-post-block/class-renderer.php)
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Search Results AJAX handler.
 *
 * Registers wp_ajax_* hooks and handles the AJAX request for the
 * Search Results widget's load-more / pagination / infinite-scroll.
 */
class Elonix_Toolkit_Search_Results_AJAX_Handler {

	/**
	 * Register AJAX hooks.
	 *
	 * Called from elonix.php at plugins_loaded priority 25.
	 */
	public static function register_hooks() {
		add_action( 'wp_ajax_tv_search_results_fetch', array( __CLASS__, 'ajax_fetch_results' ) );
		add_action( 'wp_ajax_nopriv_tv_search_results_fetch', array( __CLASS__, 'ajax_fetch_results' ) );
	}

	/**
	 * AJAX callback — fetch a page of search results and return rendered HTML.
	 *
	 * Uses Query Helpers and the shared Renderer. No Elementor widget class is loaded.
	 */
	public static function ajax_fetch_results() {
		check_ajax_referer( 'tv-search-results-nonce', 'security' );

		// ── Ensure helpers and renderer are available ──────────────────────────
		if ( ! class_exists( 'Elonix_Toolkit_Search_Results_Query_Helper' ) ) {
			require_once __DIR__ . '/helper-query.php';
		}
		if ( ! class_exists( 'Elonix_Toolkit_Search_Results_Empty_State_Helper' ) ) {
			require_once __DIR__ . '/helper-empty-state.php';
		}
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Query_Helper' ) ) {
			require_once dirname( __DIR__ ) . '/tv-post-block/helper-query.php';
		}
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Renderer' ) ) {
			require_once dirname( __DIR__ ) . '/tv-post-block/class-renderer.php';
		}

		// ── Read and sanitize parameters ───────────────────────────────────────
		$settings_raw = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$settings     = Elonix_Toolkit_Search_Results_Query_Helper::sanitize_settings( $settings_raw );

		$paged       = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
		$keyword_raw = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$post_type   = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		$date_filter = isset( $_POST['date_filter'] ) ? sanitize_key( wp_unslash( $_POST['date_filter'] ) ) : '';

		$settings['paged'] = $paged;
		if ( '' !== $keyword_raw ) {
			$settings['search_keyword'] = $keyword_raw;
		}
		if ( '' !== $post_type ) {
			$settings['post_types'] = array( $post_type );
		}
		if ( '' !== $date_filter ) {
			$settings['date_filter'] = $date_filter;
		}

		// ── Build query ────────────────────────────────────────────────────────
		$keyword = Elonix_Toolkit_Search_Results_Query_Helper::get_search_keyword( $settings );

		$post_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post', 'page' );
		$post_types = Elonix_Toolkit_Search_Results_Query_Helper::sanitize_post_types_public( $post_types );

		$limit   = ! empty( $settings['limit'] ) ? max( 1, min( 100, absint( $settings['limit'] ) ) ) : 9;
		$orderby = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'relevance';
		$order   = ! empty( $settings['order'] ) ? strtoupper( sanitize_key( $settings['order'] ) ) : 'DESC';

		$allowed_orderby = array( 'relevance', 'date', 'title', 'modified', 'comment_count', 'rand' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'relevance';
		}
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$query_args = array(
			's'                   => $keyword,
			'post_type'           => $post_types,
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'paged'               => $paged,
			'orderby'             => $orderby,
			'order'               => $order,
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $settings['date_filter'] ) && 'none' !== $settings['date_filter'] ) {
			$date_map = array(
				'today' => '1 day ago',
				'week'  => '1 week ago',
				'month' => '1 month ago',
			);
			if ( isset( $date_map[ $settings['date_filter'] ] ) ) {
				$query_args['date_query'] = array(
					array( 'after' => $date_map[ $settings['date_filter'] ] ),
				);
			}
		}

		if ( ! empty( $settings['exclude_ids'] ) ) {
			$exclude_ids = array_filter( array_map( 'absint', explode( ',', $settings['exclude_ids'] ) ) );
			if ( ! empty( $exclude_ids ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required for user-defined excluded posts.
				$query_args['post__not_in'] = $exclude_ids;
			}
		}

		$query      = new WP_Query( $query_args );
		$posts_data = array();
		$start_time = microtime( true );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;
				$posts_data[] = Elonix_Toolkit_Post_Block_Query_Helper::format_post_data( $post, $settings );
			}
			wp_reset_postdata();
		}

		// ── Render HTML via the shared renderer (no Elementor widget involved) ─
		$elapsed   = max( 0.001, microtime( true ) - $start_time );
		$max_pages = max( 1, (int) $query->max_num_pages );
		$layout    = ! empty( $settings['layout'] ) ? sanitize_key( $settings['layout'] ) : 'style_1';

		ob_start();
		if ( ! empty( $posts_data ) ) {
			$post_index  = 0;
			$has_wrapper = false;
			foreach ( $posts_data as $item ) {
				if ( 'style_3' === $layout && 1 === $post_index ) {
					echo '<div class="tv-grid-secondary-wrapper">';
					$has_wrapper = true;
				}
				Elonix_Toolkit_Post_Block_Renderer::render_single_post( $item, $settings, $layout, $post_index );
				++$post_index;
			}
			if ( $has_wrapper ) {
				echo '</div>';
			}
		} else {
			Elonix_Toolkit_Search_Results_Empty_State_Helper::render( $settings, $keyword );
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'          => $html,
				'max_num_pages' => $max_pages,
				'paged'         => $paged,
				'found_posts'   => (int) $query->found_posts,
				'keyword'       => esc_html( $keyword ),
				'elapsed'       => $elapsed,
			)
		);
	}
}
