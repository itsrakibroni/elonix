<?php
/**
 * Elonix Premium Post List AJAX Handler
 *
 * Handles AJAX requests for pagination, load-more operations, category swaps,
 * search parameters, and order sorting with nonce validation.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_List_AJAX {

	/**
	 * Constructor: Register actions.
	 */
	public function __construct() {
		add_action( 'wp_ajax_elonix_post_list_fetch_posts', array( $this, 'ajax_fetch_posts' ) );
		add_action( 'wp_ajax_nopriv_elonix_post_list_fetch_posts', array( $this, 'ajax_fetch_posts' ) );
	}

	/**
	 * Callback handler to retrieve HTML nodes.
	 */
	public function ajax_fetch_posts() {
		// 1. Security Check: Validate AJAX request nonce
		check_ajax_referer( 'es-post-list-nonce', 'security' );

		// 2. Read parameters
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every key/value is sanitize_key()/sanitize_text_field()'d two lines below before use; post_status can never come from user input (Elonix_Query_Context hardcodes it), so this can't be used to leak unpublished content.
		$settings_raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		$paged        = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
		$category     = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
		$search       = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		if ( empty( $settings_raw ) || ! is_array( $settings_raw ) ) {
			wp_send_json_error( 'Invalid settings parameter.', 400 );
		}

		// Standardize and sanitize settings mapping
		$settings = array();
		foreach ( $settings_raw as $key => $val ) {
			$settings[ sanitize_key( $key ) ] = is_array( $val ) ? array_map( 'sanitize_text_field', $val ) : sanitize_text_field( $val );
		}

		// Inject dynamic overrides
		$settings['paged'] = $paged;
		if ( $category > 0 ) {
			$settings['categories_filter'] = array( $category );
			$settings['query_source']      = 'posts'; // Force categories query
		}
		if ( ! empty( $search ) ) {
			$settings['s'] = $search;
		}

		// Load Query Helper if not loaded
		if ( ! class_exists( 'Elonix_Toolkit_Post_List_Query_Helper' ) ) {
			require_once __DIR__ . '/helper-query.php';
		}

		// 3. Build WP_Query arguments
		$query_args = \Elonix_Query_Context::build_query_args( $settings );
		if ( ! empty( $search ) ) {
			$query_args['s'] = $search;
		}

		// Extensible Dynamic Taxonomy Filter Hook
		if ( ! empty( $settings['selected_terms'] ) ) {
			$query_args = apply_filters( 'elonix_tag_cloud_apply_filter', $query_args, $settings, $settings['selected_terms'] );
		}

		// Run query
		$query      = new WP_Query( $query_args );
		$posts_html = '';

		$layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'classic_list';

		if ( $query->have_posts() ) {
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;

				// Format raw data matching Query Helper structure
				$excerpt_length = ! empty( $settings['excerpt_length'] ) ? intval( $settings['excerpt_length'] ) : 15;
				$excerpt        = wp_trim_words( get_the_excerpt( $post->ID ), $excerpt_length, '...' );
				$word_count     = str_word_count( wp_strip_all_tags( get_the_content( null, false, $post->ID ) ) );
				$reading_time   = max( 1, ceil( $word_count / 200 ) );

				$categories_data = array();
				$categories      = get_the_category( $post->ID );
				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
					foreach ( $categories as $cat ) {
						$categories_data[] = array(
							'id'   => $cat->term_id,
							'name' => esc_html( $cat->name ),
							'url'  => esc_url( get_category_link( $cat->term_id ) ),
						);
					}
				}

				$item = array(
					'id'           => $post->ID,
					'title'        => esc_html( get_the_title( $post->ID ) ),
					'url'          => esc_url( get_permalink( $post->ID ) ),
					'excerpt'      => esc_html( $excerpt ),
					'thumbnail'    => get_the_post_thumbnail(
						$post->ID,
						'medium',
						array(
							'class'   => 'es-post-img',
							'loading' => 'lazy',
						)
					),
					'date'         => esc_html( get_the_date( '', $post->ID ) ),
					'updated_date' => esc_html( get_the_modified_date( '', $post->ID ) ),
					'author_name'  => esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
					'author_url'   => esc_url( get_author_posts_url( $post->post_author ) ),
					'comments'     => intval( get_comments_number( $post->ID ) ),
					'views'        => intval( get_post_meta( $post->ID, 'es_post_views_count', true ) ),
					'reading_time' => $reading_time,
					'categories'   => $categories_data,
				);

				// Load Widget Class for render helper if not loaded
				if ( ! class_exists( 'Elonix_Toolkit_Post_List_Widget' ) ) {
					require_once __DIR__ . '/class-widget.php';
				}

				Elonix_Toolkit_Post_List_Widget::render_single_post( $item, $settings, $layout );
			}
			$posts_html = ob_get_clean();
			wp_reset_postdata();
		}

		$response = array(
			'html'          => $posts_html,
			'max_num_pages' => intval( $query->max_num_pages ),
			'paged'         => $paged,
		);

		wp_send_json_success( $response );
	}
}

// Instantiate handler on load
new Elonix_Toolkit_Post_List_AJAX();
