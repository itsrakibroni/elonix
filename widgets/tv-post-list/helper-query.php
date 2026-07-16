<?php
/**
 * Elonix Premium Post List Query Engine Helper
 *
 * Safe query parsing, custom post type resolving, date filtering, caching
 * transients, and dynamic contextual query adaptions.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_List_Query_Helper {

	/**
	 * Main query method with transient caching.
	 *
	 * @param array $settings Widget controls configuration.
	 * @return array Array containing matching post structures.
	 */
	public static function get_posts_data( $settings ) {
		// 1. Build WP_Query args
		$query_args = \Elonix_Query_Context::build_query_args( $settings );

		// Extensible Dynamic Taxonomy Filter Hook
		if ( ! empty( $settings['selected_terms'] ) ) {
			$query_args = apply_filters( 'elonix_tag_cloud_apply_filter', $query_args, $settings, $settings['selected_terms'] );
		}

		// 4. Run query
		$query      = new WP_Query( $query_args );
		$posts_data = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;
				$posts_data[] = self::format_post_data( $post, $settings );
			}
			wp_reset_postdata();
		}

		return $posts_data;
	}



	/**
	 * Helper: Format individual post values securely.
	 */
	private static function format_post_data( $post, $settings ) {
		$post_id = $post->ID;

		$title_word_limit = ! empty( $settings['title_word_limit'] ) ? intval( $settings['title_word_limit'] ) : 0;
		$title_suffix     = isset( $settings['title_suffix'] ) ? $settings['title_suffix'] : '...';
		$raw_title        = get_the_title( $post_id );
		$title            = $title_word_limit > 0 ? wp_trim_words( $raw_title, $title_word_limit, $title_suffix ) : $raw_title;

		$excerpt_length = ! empty( $settings['excerpt_length'] ) ? intval( $settings['excerpt_length'] ) : 15;
		$excerpt_suffix = isset( $settings['excerpt_suffix'] ) ? $settings['excerpt_suffix'] : '...';
		$raw_excerpt    = get_the_excerpt( $post_id );
		if ( empty( $raw_excerpt ) ) {
			$raw_excerpt = $post->post_content;
		}
		$excerpt = wp_trim_words( $raw_excerpt, $excerpt_length, $excerpt_suffix );

		$reading_time = self::calculate_reading_time( get_the_content( null, false, $post_id ) );

		$categories_data = array();
		$categories      = get_the_category( $post_id );
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$categories_data[] = array(
					'id'   => $cat->term_id,
					'name' => esc_html( $cat->name ),
					'url'  => esc_url( get_category_link( $cat->term_id ) ),
				);
			}
		}

		return array(
			'id'           => $post_id,
			'title'        => esc_html( $title ),
			'url'          => esc_url( get_permalink( $post_id ) ),
			'excerpt'      => esc_html( $excerpt ),
			'thumbnail'    => get_the_post_thumbnail(
				$post_id,
				'medium',
				array(
					'class'   => 'tv-post-img',
					'loading' => 'lazy',
				)
			),
			'date'         => esc_html( get_the_date( '', $post_id ) ),
			'updated_date' => esc_html( get_the_modified_date( '', $post_id ) ),
			'author_name'  => esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
			'author_url'   => esc_url( get_author_posts_url( $post->post_author ) ),
			'comments'     => intval( get_comments_number( $post_id ) ),
			'views'        => intval( get_post_meta( $post_id, 'tv_post_views_count', true ) ),
			'reading_time' => $reading_time,
			'categories'   => $categories_data,
		);
	}

	/**
	 * Estimate reading time based on standard 200 WPM speed.
	 */
	private static function calculate_reading_time( $content ) {
		$word_count = str_word_count( wp_strip_all_tags( $content ) );
		$minutes    = ceil( $word_count / 200 );
		return max( 1, $minutes );
	}
}
// Register the default dynamic filter hook for TV Tag Cloud widget target queries
add_filter( 'elonix_tag_cloud_apply_filter', 'elonix_tag_cloud_apply_default_filter', 10, 3 );
if ( ! function_exists( 'elonix_tag_cloud_apply_default_filter' ) ) {
	function elonix_tag_cloud_apply_default_filter( $query_args, $settings, $selected_term_ids ) {
		if ( empty( $selected_term_ids ) ) {
			return $query_args;
		}

		$selected_term_ids = array_map( 'absint', (array) $selected_term_ids );

		// Resolve the taxonomy of the first term dynamically
		$first_term = get_term( $selected_term_ids[0] );
		if ( is_wp_error( $first_term ) || empty( $first_term ) ) {
			return $query_args;
		}

		$taxonomy = $first_term->taxonomy;

		// Build a tax query overriding/appending to the query args
		$tax_query = isset( $query_args['tax_query'] ) ? $query_args['tax_query'] : array();

		// Clean out existing tax query clauses for this specific taxonomy if we want to override it
		foreach ( $tax_query as $key => $clause ) {
			if ( is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] === $taxonomy ) {
				unset( $tax_query[ $key ] );
			}
		}

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $selected_term_ids,
			'operator' => 'IN',
		);

		if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$query_args['tax_query'] = $tax_query;

		return $query_args;
	}
}
