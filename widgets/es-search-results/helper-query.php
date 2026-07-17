<?php
/**
 * Elonix Search Results query helper.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Builds and formats Search Results queries.
 */
class Elonix_Toolkit_Search_Results_Query_Helper {

	/**
	 * Get query data for the widget.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	public static function get_results_data( $settings ) {
		$query_args = self::build_query_args( $settings );
		$query      = new WP_Query( $query_args );
		$items      = array();

		$start = microtime( true );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$items[] = self::format_post_data( get_post(), $settings );
			}
			wp_reset_postdata();
		}

		return array(
			'items'       => $items,
			'query'       => $query,
			'keyword'     => self::get_search_keyword( $settings ),
			'found_posts' => intval( $query->found_posts ),
			'max_pages'   => intval( $query->max_num_pages ),
			'paged'       => max( 1, intval( $query_args['paged'] ) ),
			'elapsed'     => max( 0.001, microtime( true ) - $start ),
		);
	}

	/**
	 * Build WP_Query arguments.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	public static function build_query_args( $settings ) {
		$paged          = ! empty( $settings['paged'] ) ? absint( $settings['paged'] ) : self::get_current_page();
		$posts_per_page = ! empty( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 9;
		$post_types     = self::sanitize_post_types( isset( $settings['post_types'] ) ? $settings['post_types'] : array( 'post', 'page' ) );
		$post_status    = self::sanitize_post_status( isset( $settings['post_status'] ) ? $settings['post_status'] : 'publish' );
		$keyword        = self::get_search_keyword( $settings );

		$args = array(
			's'                   => $keyword,
			'post_type'           => $post_types,
			'post_status'         => $post_status,
			'posts_per_page'      => max( 1, min( 100, $posts_per_page ) ),
			'paged'               => max( 1, $paged ),
			'ignore_sticky_posts' => ! empty( $settings['ignore_sticky'] ) && 'yes' === $settings['ignore_sticky'],
			'no_found_rows'       => empty( $settings['pagination_type'] ) || 'none' === $settings['pagination_type'],
		);

		if ( ! empty( $settings['offset'] ) ) {
			$args['offset'] = absint( $settings['offset'] ) + ( ( max( 1, $paged ) - 1 ) * $args['posts_per_page'] );
		}

		$exclude_ids = self::csv_to_absint_array( isset( $settings['exclude_ids'] ) ? $settings['exclude_ids'] : '' );
		if ( ! empty( $exclude_ids ) ) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Centralized exclusion parser for widget queries.
			$args['post__not_in'] = $exclude_ids;
		}

		self::apply_order( $args, $settings );
		self::apply_author_filter( $args, $settings );
		self::apply_date_filter( $args, $settings );
		self::apply_taxonomy_filters( $args, $settings );

		return apply_filters( 'elonix_es_search_results_query_args', $args, $settings );
	}

	/**
	 * Resolve current search keyword.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	public static function get_search_keyword( $settings = array() ) {
		if ( ! empty( $settings['search_keyword'] ) ) {
			return sanitize_text_field( $settings['search_keyword'] );
		}

		$keyword = get_search_query( false );
		if ( empty( $keyword ) && isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search query fallback.
			$keyword = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search query fallback.
		}

		return sanitize_text_field( $keyword );
	}

	/**
	 * Format a post into render-ready data.
	 *
	 * @param WP_Post $post     Post object.
	 * @param array   $settings Widget settings.
	 * @return array
	 */
	public static function format_post_data( $post, $settings ) {
		$post_id        = $post->ID;
		$excerpt_length = ! empty( $settings['excerpt_length'] ) ? absint( $settings['excerpt_length'] ) : 22;
		$image_size     = ! empty( $settings['thumbnail_size'] ) ? sanitize_key( $settings['thumbnail_size'] ) : 'medium_large';
		$keyword        = self::get_search_keyword( $settings );
		$excerpt_source = get_the_excerpt( $post_id );

		if ( empty( $excerpt_source ) ) {
			$excerpt_source = $post->post_content;
		}

		$taxonomy_terms = self::get_primary_terms( $post_id );
		$tag_terms      = self::get_post_tags( $post_id );
		$word_count     = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );

		return array(
			'id'             => $post_id,
			'title'          => get_the_title( $post_id ),
			'highlighted'    => self::highlight_keyword( get_the_title( $post_id ), $keyword ),
			'url'            => get_permalink( $post_id ),
			'excerpt'        => wp_trim_words( wp_strip_all_tags( strip_shortcodes( $excerpt_source ) ), $excerpt_length, '...' ),
			'post_type'      => get_post_type( $post_id ),
			'post_type_name' => self::get_post_type_label( get_post_type( $post_id ) ),
			'thumbnail'      => get_the_post_thumbnail(
				$post_id,
				$image_size,
				array(
					'class'   => 'es-search-results-image',
					'loading' => 'lazy',
				)
			),
			'date'           => get_the_date( '', $post_id ),
			'author_name'    => get_the_author_meta( 'display_name', $post->post_author ),
			'author_url'     => get_author_posts_url( $post->post_author ),
			'comments'       => intval( get_comments_number( $post_id ) ),
			'reading_time'   => max( 1, (int) ceil( $word_count / 200 ) ),
			'rating'         => self::get_post_rating( $post_id ),
			'categories'     => $taxonomy_terms,
			'tags'           => $tag_terms,
		);
	}

	/**
	 * Resolve a display-safe post rating from common metadata keys.
	 *
	 * @param int $post_id Post ID.
	 * @return float
	 */
	private static function get_post_rating( $post_id ) {
		$keys = array( 'rating', '_rating', 'average_rating', '_average_rating', 'es_rating' );
		foreach ( $keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( is_numeric( $value ) ) {
				return min( 5, max( 0, (float) $value ) );
			}
		}

		return 0.0;
	}

	/**
	 * Highlight keyword in a safe text string.
	 *
	 * @param string $text    Text.
	 * @param string $keyword Search keyword.
	 * @return string
	 */
	public static function highlight_keyword( $text, $keyword ) {
		$text = wp_strip_all_tags( $text );
		if ( '' === $keyword ) {
			return esc_html( $text );
		}

		$pattern = '/' . preg_quote( $keyword, '/' ) . '/iu';
		$parts   = preg_split( $pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( empty( $parts ) ) {
			return esc_html( $text );
		}

		return preg_replace_callback(
			$pattern,
			static function ( $matches ) {
				return '<mark class="es-search-results-highlight">' . esc_html( $matches[0] ) . '</mark>';
			},
			esc_html( $text )
		);
	}

	/**
	 * Get suggested keywords from local content.
	 *
	 * @param string $keyword Current keyword.
	 * @return array
	 */
	public static function get_keyword_suggestions( $keyword ) {
		$suggestions = array();
		$terms       = get_terms(
			array(
				'taxonomy'   => array( 'category', 'post_tag' ),
				'hide_empty' => true,
				'number'     => 8,
				'search'     => $keyword,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$suggestions[] = $term->name;
			}
		}

		if ( empty( $suggestions ) ) {
			$recent_posts = get_posts(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 5,
					'ignore_sticky_posts' => true,
				)
			);
			foreach ( $recent_posts as $post ) {
				$words = preg_split( '/\s+/', wp_strip_all_tags( $post->post_title ) );
				foreach ( $words as $word ) {
					$word = trim( $word );
					if ( strlen( $word ) > 3 ) {
						$suggestions[] = $word;
						break;
					}
				}
			}
		}

		return array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $suggestions ) ) ) );
	}

	/**
	 * Convert CSV to integer IDs.
	 *
	 * @param string|array $value Value.
	 * @return array
	 */
	public static function csv_to_absint_array( $value ) {
		$items = is_array( $value ) ? $value : explode( ',', (string) $value );
		return array_values( array_filter( array_map( 'absint', $items ) ) );
	}

	/**
	 * Sanitize posted settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public static function sanitize_settings( $settings ) {
		$clean = array();
		foreach ( (array) $settings as $key => $value ) {
			$key           = sanitize_key( $key );
			$clean[ $key ] = self::sanitize_setting_value( $value );
		}
		return $clean;
	}

	/**
	 * Sanitize scalar or nested setting values.
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	private static function sanitize_setting_value( $value ) {
		if ( is_array( $value ) ) {
			$clean = array();
			foreach ( wp_unslash( $value ) as $child_key => $child_value ) {
				$clean[ sanitize_key( $child_key ) ] = self::sanitize_setting_value( $child_value );
			}
			return $clean;
		}

		return sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	 * Get public post type options.
	 *
	 * @return array
	 */
	public static function get_post_type_options() {
		$options    = array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			if ( 'attachment' === $post_type->name ) {
				continue;
			}
			$options[ $post_type->name ] = $post_type->label;
		}

		return $options;
	}

	/**
	 * Get taxonomy options.
	 *
	 * @param bool $hierarchical Whether taxonomy is hierarchical.
	 * @return array
	 */
	public static function get_taxonomy_term_options( $hierarchical = true ) {
		$options    = array();
		$taxonomies = get_taxonomies(
			array(
				'public'       => true,
				'hierarchical' => $hierarchical,
			),
			'names'
		);

		if ( empty( $taxonomies ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => array_values( $taxonomies ),
				'hide_empty' => false,
				'number'     => 200,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = sprintf( '%1$s (%2$s)', $term->name, $term->taxonomy );
			}
		}

		return $options;
	}

	/**
	 * Get author options.
	 *
	 * @return array
	 */
	public static function get_author_options() {
		$options = array();
		$users   = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
		foreach ( $users as $user ) {
			$options[ $user->ID ] = $user->display_name;
		}
		return $options;
	}


	/**
	 * Get current page number.
	 *
	 * @return int
	 */
	private static function get_current_page() {
		$paged = get_query_var( 'paged' );
		if ( ! $paged ) {
			$paged = get_query_var( 'page' );
		}
		return max( 1, absint( $paged ) );
	}

	/**
	 * Public wrapper for sanitizing post types against registered public types.
	 * Used by class-widget.php render() and ajax_fetch_results().
	 *
	 * @param array|string $post_types Post types.
	 * @return array
	 */
	public static function sanitize_post_types_public( $post_types ) {
		return self::sanitize_post_types( $post_types );
	}

	/**
	 * Sanitize post types.
	 *
	 * @param array|string $post_types Post types.
	 * @return array
	 */
	private static function sanitize_post_types( $post_types ) {
		$post_types = is_array( $post_types ) ? $post_types : explode( ',', (string) $post_types );
		$public     = self::get_post_type_options();
		$clean      = array();

		foreach ( $post_types as $post_type ) {
			$post_type = sanitize_key( $post_type );
			if ( isset( $public[ $post_type ] ) ) {
				$clean[] = $post_type;
			}
		}

		return ! empty( $clean ) ? array_values( array_unique( $clean ) ) : array( 'post' );
	}

	/**
	 * Sanitize post status.
	 *
	 * @param string $status Post status.
	 * @return string
	 */
	private static function sanitize_post_status( $status ) {
		$allowed = array( 'publish' );
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			$allowed[] = 'private';
		}

		return in_array( $status, $allowed, true ) ? $status : 'publish';
	}

	/**
	 * Apply ordering settings.
	 *
	 * @param array $args     Query args.
	 * @param array $settings Widget settings.
	 */
	private static function apply_order( &$args, $settings ) {
		$allowed_orderby = array( 'date', 'title', 'modified', 'comment_count', 'rand', 'relevance' );
		$orderby         = ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'relevance';
		$order           = ! empty( $settings['order'] ) ? strtoupper( sanitize_key( $settings['order'] ) ) : 'DESC';

		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'relevance';
		}

		$args['orderby'] = $orderby;
		$args['order']   = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';
	}

	/**
	 * Apply author filters.
	 *
	 * @param array $args     Query args.
	 * @param array $settings Widget settings.
	 */
	private static function apply_author_filter( &$args, $settings ) {
		if ( empty( $settings['author_ids'] ) ) {
			return;
		}

		$authors = self::csv_to_absint_array( $settings['author_ids'] );
		if ( ! empty( $authors ) ) {
			$args['author__in'] = $authors;
		}
	}

	/**
	 * Apply date filters.
	 *
	 * @param array $args     Query args.
	 * @param array $settings Widget settings.
	 */
	private static function apply_date_filter( &$args, $settings ) {
		if ( empty( $settings['date_filter'] ) || 'all' === $settings['date_filter'] ) {
			return;
		}

		$date_query = array();
		switch ( $settings['date_filter'] ) {
			case 'day':
				$date_query['after'] = '1 day ago';
				break;
			case 'week':
				$date_query['after'] = '1 week ago';
				break;
			case 'month':
				$date_query['after'] = '1 month ago';
				break;
			case 'year':
				$date_query['after'] = '1 year ago';
				break;
		}

		if ( ! empty( $date_query ) ) {
			$args['date_query'] = array( $date_query );
		}
	}

	/**
	 * Apply taxonomy filters.
	 *
	 * @param array $args     Query args.
	 * @param array $settings Widget settings.
	 */
	private static function apply_taxonomy_filters( &$args, $settings ) {
		$tax_query  = array();
		$categories = ! empty( $settings['categories'] ) ? self::csv_to_absint_array( $settings['categories'] ) : array();
		$tags       = ! empty( $settings['tags'] ) ? self::csv_to_absint_array( $settings['tags'] ) : array();

		if ( ! empty( $categories ) ) {
			self::append_terms_query( $tax_query, $categories );
		}

		if ( ! empty( $tags ) ) {
			self::append_terms_query( $tax_query, $tags );
		}

		if ( ! empty( $settings['custom_taxonomy'] ) && ! empty( $settings['custom_terms'] ) ) {
			$taxonomy = sanitize_key( $settings['custom_taxonomy'] );
			$terms    = self::csv_to_absint_array( $settings['custom_terms'] );
			if ( taxonomy_exists( $taxonomy ) && ! empty( $terms ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
					'operator' => 'IN',
				);
			}
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for user-selected taxonomy filters with sanitized term IDs.
			$args['tax_query'] = $tax_query;
		}
	}

	/**
	 * Append term queries grouped by their taxonomy.
	 *
	 * @param array $tax_query Tax query.
	 * @param array $term_ids  Term IDs.
	 */
	private static function append_terms_query( &$tax_query, $term_ids ) {
		$groups = array();

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$groups[ $term->taxonomy ][] = $term_id;
			}
		}

		foreach ( $groups as $taxonomy => $ids ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', $ids ),
				'operator' => 'IN',
			);
		}
	}

	/**
	 * Get primary taxonomy terms.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private static function get_primary_terms( $post_id ) {
		$data       = array();
		$taxonomies = get_object_taxonomies( get_post_type( $post_id ), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy->hierarchical || ! $taxonomy->public ) {
				continue;
			}

			$terms = get_the_terms( $post_id, $taxonomy->name );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$data[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
					'url'  => get_term_link( $term ),
				);
			}
			break;
		}

		return $data;
	}

	/**
	 * Get public non-hierarchical terms.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private static function get_post_tags( $post_id ) {
		$data       = array();
		$taxonomies = get_object_taxonomies( get_post_type( $post_id ), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( $taxonomy->hierarchical || ! $taxonomy->public ) {
				continue;
			}

			$terms = get_the_terms( $post_id, $taxonomy->name );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$data[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
					'url'  => get_term_link( $term ),
				);
			}
		}

		return $data;
	}

	/**
	 * Get post type label.
	 *
	 * @param string $post_type Post type.
	 * @return string
	 */
	private static function get_post_type_label( $post_type ) {
		$object = get_post_type_object( $post_type );
		return $object ? $object->labels->singular_name : $post_type;
	}
}

