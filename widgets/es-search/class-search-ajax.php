<?php
/**
 * Elonix Search Widget AJAX Handler
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Search_AJAX_Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_elonix_live_search', array( $this, 'handle_live_search' ) );
		add_action( 'wp_ajax_nopriv_elonix_live_search', array( $this, 'handle_live_search' ) );
	}

	/**
	 * Main live search AJAX handler.
	 */
	public function handle_live_search() {
		// 1. Verify Nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'es_search_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'elonix' ) ), 403 );
		}

		// 2. Validate capability
		if ( ! current_user_can( 'read' ) && ! is_user_logged_in() && 'yes' !== get_option( 'elonix_search_allow_guests', 'yes' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access.', 'elonix' ) ), 401 );
		}

		// 3. Enforce Rate Limiting (Max 30 searches per minute per IP)
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';
		$rate_key = 'elonix_search_rate_' . md5( $ip );
		$requests = (int) get_transient( $rate_key );

		if ( $requests > 30 ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Too many requests. Please slow down.', 'elonix' ) ), 429 );
		}
		set_transient( $rate_key, $requests + 1, MINUTE_IN_SECONDS );

		// 4. Validate and Sanitize Search Term
		$search_term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		if ( strlen( $search_term ) < 3 ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Search term too short (minimum 3 characters).', 'elonix' ) ), 400 );
		}

		// 5. Sanitize and Whitelist Post Types
		$allowed_post_types = get_post_types( array( 'public' => true ), 'names' );
		if ( ! is_array( $allowed_post_types ) ) {
			$allowed_post_types = array( 'post', 'page' );
		}

		$post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['post_types'] ) ) : array();
		$post_types = array_intersect( $post_types, $allowed_post_types );

		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		$limit = isset( $_POST['limit'] ) ? min( 20, max( 1, intval( wp_unslash( $_POST['limit'] ) ) ) ) : 5;

		// 6. Build query parameters
		$query_args = array(
			's'                   => $search_term,
			'post_type'           => $post_types,
			'posts_per_page'      => $limit,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		);

		// Support category filtering if selected
		if ( isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ) {
			$category                = sanitize_text_field( wp_unslash( $_POST['category'] ) );
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
			if ( class_exists( 'WooCommerce' ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $category,
				);
			}
		}

		// Execute WordPress Safe Query
		$search_query = new WP_Query( $query_args );
		$results      = array();

		if ( $search_query->have_posts() ) {
			while ( $search_query->have_posts() ) {
				$search_query->the_post();
				global $post;

				$item = array(
					'id'       => get_the_ID(),
					'title'    => get_the_title(),
					'url'      => get_permalink(),
					'excerpt'  => wp_trim_words( get_the_excerpt(), 15, '...' ),
					'date'     => get_the_date(),
					'author'   => get_the_author(),
					'image'    => '',
					'category' => '',
				);

				// Get Thumbnail
				if ( has_post_thumbnail() ) {
					$image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
					if ( $image_url ) {
						$item['image'] = esc_url( $image_url );
					}
				}

				// Get Category Term
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					$item['category'] = $categories[0]->name;
				} elseif ( class_exists( 'WooCommerce' ) && 'product' === get_post_type() ) {
					$product_cats = get_the_terms( get_the_ID(), 'product_cat' );
					if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) {
						$item['category'] = $product_cats[0]->name;
					}
				}

				$results[] = $item;
			}
			wp_reset_postdata();
		}

		// Send success response
		wp_send_json_success(
			array(
				'results' => $results,
				'count'   => count( $results ),
			)
		);
	}
}
