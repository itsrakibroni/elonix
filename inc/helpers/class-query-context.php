<?php
/**
 * Elonix – Toolkit for Elementor Shared Query Context
 *
 * Safe query parsing, custom post type resolving, date filtering, and
 * dynamic contextual query adaptions for Archive Builders.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Query_Context {

	/**
	 * Main entry point to build query arguments.
	 *
	 * @param array $settings
	 * @param array $archive_vars
	 * @return array
	 */
	public static function build_query_args( $settings, $archive_vars = array() ) {
		$query_mode = isset( $settings['query_mode'] ) ? $settings['query_mode'] : 'custom';

		// Auto-detect if we are inside a Elonix Archive Builder template
		$is_archive_template = false;
		if ( get_query_var( 'es_matched_archive_id' ) || defined( 'ELONIX_ARCHIVE_TEMPLATE_RENDERING' ) ) {
			$is_archive_template = true;
		} elseif ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			try {
				$location = \ElementorPro\Plugin::instance()->theme_builder->get_locations_manager()->get_current_location();
				if ( 'archive' === $location ) {
					$is_archive_template = true;
				}
			} catch ( \Exception $e ) {
				// Ignore
			}
		}

		// If we are in an Archive Template, and the widget is using default custom posts (no specific manual IDs/taxonomies)
		// Or if the user explicitly chose 'current' query mode.
		if ( 'current' === $query_mode || $is_archive_template ) {
			return self::resolve_context( $settings, $archive_vars );
		}

		return self::build_custom_query_args( $settings );
	}

	/**
	 * Execute the query
	 *
	 * @param array $settings Widget settings.
	 * @param array $archive_vars Contextual query variables for AJAX.
	 * @return WP_Query
	 */
	public static function execute_query( $settings, $archive_vars = array() ) {
		$query_args = self::build_query_args( $settings, $archive_vars );
		return new WP_Query( $query_args );
	}

	/**
	 * Resolves the query arguments based on the current WordPress archive context.
	 */
	public static function resolve_context( $settings, $archive_vars = array() ) {
		global $wp_query;

		// 1. Base inheritance: if AJAX and archive vars are passed, use them. Else, use current wp_query.
		if ( wp_doing_ajax() && ! empty( $archive_vars ) ) {
			$query_args = $archive_vars;
		} else {
			$query_args = $wp_query->query_vars;

			// If Elementor preview mode and previewing an archive, Elementor injects specific query_vars
			if ( class_exists( '\Elementor\Plugin' ) ) {
				$elementor = \Elementor\Plugin::$instance;
				if ( $elementor->editor->is_edit_mode() || $elementor->preview->is_preview_mode() ) {
					// Fallback manual context extraction if Elementor didn't fully override $wp_query
					if ( empty( $query_args ) || ( ! is_archive() && ! is_search() && ! is_home() && ! is_singular() ) ) {
						$queried_object = get_queried_object();
						if ( $queried_object instanceof \WP_Term ) {
							// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for querying terms in preview mode.
							$query_args['tax_query'] = array(
								array(
									'taxonomy' => $queried_object->taxonomy,
									'field'    => 'term_id',
									'terms'    => $queried_object->term_id,
								),
							);
							// Also set the correct archive post type
							$query_args['post_type'] = 'any';
						} elseif ( $queried_object instanceof \WP_User ) {
							$query_args['author'] = $queried_object->ID;
						} elseif ( $queried_object instanceof \WP_Post_Type ) {
							$query_args['post_type'] = $queried_object->name;
						}
					}
				}
			}
		}

		// Pagination limit overriding
		$limit                        = ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : get_option( 'posts_per_page' );
		$query_args['posts_per_page'] = $limit;

		$paged = ! empty( $settings['paged'] ) ? intval( $settings['paged'] ) : 1;
		if ( $paged > 1 ) {
			$query_args['paged'] = $paged;
		}

		// Security status
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			$query_args['post_status'] = 'publish';
		}

		// Exclude current singular ID
		if ( is_singular() ) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to prevent duplicate posts and honor Elementor widget exclusion settings.
			$query_args['post__not_in'] = array( get_the_ID() );
		}

		// AJAX Dynamic Overrides for Archive templates (Category filter tabs, search, etc.)
		if ( ! empty( $settings['s'] ) ) {
			$query_args['s'] = sanitize_text_field( $settings['s'] );
		}

		if ( ! empty( $settings['categories_filter'] ) ) {
			$conflicting_cat_keys = array( 'cat', 'category_name', 'category__in', 'category__and', 'category__not_in' );
			foreach ( $conflicting_cat_keys as $key ) {
				if ( isset( $query_args[ $key ] ) ) {
					unset( $query_args[ $key ] );
				}
			}
		}

		if ( ! empty( $settings['tags_filter'] ) ) {
			$conflicting_tag_keys = array( 'tag', 'tag_id', 'tag__in', 'tag__and', 'tag_slug__in' );
			foreach ( $conflicting_tag_keys as $key ) {
				if ( isset( $query_args[ $key ] ) ) {
					unset( $query_args[ $key ] );
				}
			}
		}

		if ( ! empty( $settings['author_ids'] ) ) {
			$conflicting_author_keys = array( 'author', 'author_name', 'author__in', 'author__not_in' );
			foreach ( $conflicting_author_keys as $key ) {
				if ( isset( $query_args[ $key ] ) ) {
					unset( $query_args[ $key ] );
				}
			}
			$author_ids               = is_array( $settings['author_ids'] ) ? $settings['author_ids'] : explode( ',', $settings['author_ids'] );
			$query_args['author__in'] = array_map( 'absint', array_filter( $author_ids ) );
		}

		if ( ! empty( $settings['categories_filter'] ) || ! empty( $settings['tags_filter'] ) ) {
			$tax_query = ! empty( $query_args['tax_query'] ) ? $query_args['tax_query'] : array();

			if ( ! empty( $settings['categories_filter'] ) ) {
				$cat_ids = is_array( $settings['categories_filter'] ) ? $settings['categories_filter'] : explode( ',', $settings['categories_filter'] );
				$cat_ids = array_map( 'absint', array_filter( $cat_ids ) );
				if ( ! empty( $cat_ids ) ) {
					$tax_groups = array();
					foreach ( $cat_ids as $cat_id ) {
						$term = get_term( $cat_id );
						if ( $term && ! is_wp_error( $term ) ) {
							$tax_groups[ $term->taxonomy ][] = $cat_id;
						} else {
							$tax_groups['category'][] = $cat_id;
						}
					}
					foreach ( $tax_groups as $taxonomy => $ids ) {
						if ( 'category' === $taxonomy ) {
							$query_args['category__in'] = $ids;
						} else {
							$tax_query[] = array(
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => $ids,
								'operator' => 'IN',
							);
						}
					}
				}
			}
			if ( ! empty( $settings['tags_filter'] ) ) {
				$tag_ids = is_array( $settings['tags_filter'] ) ? $settings['tags_filter'] : explode( ',', $settings['tags_filter'] );
				$tag_ids = array_map( 'absint', array_filter( $tag_ids ) );
				if ( ! empty( $tag_ids ) ) {
					$tax_groups = array();
					foreach ( $tag_ids as $tag_id ) {
						$term = get_term( $tag_id );
						if ( $term && ! is_wp_error( $term ) ) {
							$tax_groups[ $term->taxonomy ][] = $tag_id;
						} else {
							$tax_groups['post_tag'][] = $tag_id;
						}
					}
					foreach ( $tax_groups as $taxonomy => $ids ) {
						if ( 'post_tag' === $taxonomy ) {
							$query_args['tag__in'] = $ids;
						} else {
							$tax_query[] = array(
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => $ids,
								'operator' => 'IN',
							);
						}
					}
				}
			}

			if ( ! empty( $tax_query ) ) {
				if ( count( $tax_query ) > 1 && empty( $tax_query['relation'] ) ) {
					$tax_query['relation'] = 'AND';
				}
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for filtering content by terms.
				$query_args['tax_query'] = $tax_query;
			}
		}

		return $query_args;
	}

	/**
	 * Builds custom WP_Query args from settings
	 */
	public static function build_custom_query_args( $settings ) {
		$query_args = array(
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		);

		// Limit & Pagination
		$limit                        = ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : 5;
		$query_args['posts_per_page'] = $limit;

		$paged               = ! empty( $settings['paged'] ) ? intval( $settings['paged'] ) : 1;
		$query_args['paged'] = $paged;

		if ( ! empty( $settings['offset'] ) ) {
			$query_args['offset'] = intval( $settings['offset'] ) + ( ( $paged - 1 ) * $limit );
		}

		// Performance: Disable SQL_CALC_FOUND_ROWS if pagination is not used
		if ( empty( $settings['pagination_type'] ) || 'none' === $settings['pagination_type'] ) {
			$query_args['no_found_rows'] = true;
		}

		$source = ! empty( $settings['query_source'] ) ? $settings['query_source'] : 'posts';

		// Related Posts mode
		if ( 'related' === $source ) {
			$current_id                 = get_the_ID();
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to prevent duplicate posts and honor Elementor widget exclusion settings.
			$query_args['post__not_in'] = $current_id ? array( $current_id ) : array();

			$post_type               = get_post_type();
			$query_args['post_type'] = $post_type ? $post_type : 'post';

			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$cat_ids                    = wp_list_pluck( $categories, 'term_id' );
				$query_args['category__in'] = $cat_ids;
			}
		}
		// Manual Selection Mode
		elseif ( 'manual' === $source ) {
			$manual_ids = ! empty( $settings['manual_ids'] ) ? array_map( 'absint', (array) explode( ',', $settings['manual_ids'] ) ) : array();
			if ( ! empty( $manual_ids ) ) {
				$query_args['post__in'] = $manual_ids;
				$query_args['orderby']  = 'post__in';
			} else {
				$query_args['post__in'] = array( 0 );
			}
		}
		// Woo Products Mode
		elseif ( 'woo' === $source && class_exists( 'WooCommerce' ) ) {
			$query_args['post_type'] = 'product';
			self::apply_taxonomies_query( $query_args, $settings, 'product_cat', 'product_tag' );
		}
		// Custom Post Types / Pages / Posts Mode
		else {
			$query_args['post_type'] = ( 'pages' === $source ) ? 'page' : ( ( 'cpt' === $source && ! empty( $settings['custom_post_type'] ) ) ? sanitize_text_field( $settings['custom_post_type'] ) : 'post' );
			self::apply_taxonomies_query( $query_args, $settings, 'category', 'post_tag' );
		}

		// Sticky Posts Mode
		if ( ! empty( $settings['ignore_sticky_posts'] ) && 'yes' === $settings['ignore_sticky_posts'] ) {
			$query_args['ignore_sticky_posts'] = true;
		} elseif ( 'yes' === get_option( 'sticky_posts' ) ) {
			$query_args['ignore_sticky_posts'] = false;
		}

		// Authors filter
		if ( ! empty( $settings['author_ids'] ) ) {
			$author_ids               = is_array( $settings['author_ids'] ) ? $settings['author_ids'] : explode( ',', $settings['author_ids'] );
			$query_args['author__in'] = array_map( 'absint', array_filter( $author_ids ) );
		}

		// Include IDs / Exclude IDs
		if ( ! empty( $settings['exclude_ids'] ) ) {
			$exclude_ids = array_map( 'absint', explode( ',', $settings['exclude_ids'] ) );
			if ( ! empty( $query_args['post__not_in'] ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to prevent duplicate posts and honor Elementor widget exclusion settings.
				$query_args['post__not_in'] = array_merge( $query_args['post__not_in'], $exclude_ids );
			} else {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to prevent duplicate posts and honor Elementor widget exclusion settings.
				$query_args['post__not_in'] = $exclude_ids;
			}
		}

		// Order & Orderby
		if ( ! empty( $settings['orderby'] ) ) {
			$query_args['orderby'] = sanitize_text_field( $settings['orderby'] );
		}
		if ( ! empty( $settings['order'] ) ) {
			$query_args['order'] = sanitize_text_field( $settings['order'] );
		}

		// Date Range Filters
		if ( ! empty( $settings['date_filter'] ) && 'none' !== $settings['date_filter'] ) {
			$date_query = array();
			switch ( $settings['date_filter'] ) {
				case 'today':
					$date_query['after'] = '1 day ago';
					break;
				case 'week':
					$date_query['after'] = '1 week ago';
					break;
				case 'month':
					$date_query['after'] = '1 month ago';
					break;
				case 'custom':
					if ( ! empty( $settings['custom_date_after'] ) ) {
						$date_query['after'] = sanitize_text_field( $settings['custom_date_after'] );
					}
					if ( ! empty( $settings['custom_date_before'] ) ) {
						$date_query['before'] = sanitize_text_field( $settings['custom_date_before'] );
					}
					break;
			}
			if ( ! empty( $date_query ) ) {
				$query_args['date_query'] = array( $date_query );
			}
		}

		// Search phrase mapping for Custom Queries
		if ( ! empty( $settings['s'] ) ) {
			$query_args['s'] = sanitize_text_field( $settings['s'] );
		}

		return $query_args;
	}

	/**
	 * Taxonomy query helper mapping for categories & tags.
	 */
	protected static function apply_taxonomies_query( &$query_args, $settings, $cat_tax, $tag_tax ) {
		$tax_query = array();

		// Add Category filters
		if ( ! empty( $settings['categories_filter'] ) ) {
			$cat_ids = is_array( $settings['categories_filter'] ) ? $settings['categories_filter'] : explode( ',', $settings['categories_filter'] );
			$cat_ids = array_map( 'absint', array_filter( $cat_ids ) );
			if ( ! empty( $cat_ids ) ) {
				$tax_groups = array();
				foreach ( $cat_ids as $cat_id ) {
					$term = get_term( $cat_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$tax_groups[ $term->taxonomy ][] = $cat_id;
					} else {
						$tax_groups[ $cat_tax ][] = $cat_id;
					}
				}
				foreach ( $tax_groups as $taxonomy => $ids ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $ids,
						'operator' => 'IN',
					);
				}
			}
		}

		// Add Tag filters
		if ( ! empty( $settings['tags_filter'] ) ) {
			$tag_ids = is_array( $settings['tags_filter'] ) ? $settings['tags_filter'] : explode( ',', $settings['tags_filter'] );
			$tag_ids = array_map( 'absint', array_filter( $tag_ids ) );
			if ( ! empty( $tag_ids ) ) {
				$tax_groups = array();
				foreach ( $tag_ids as $tag_id ) {
					$term = get_term( $tag_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$tax_groups[ $term->taxonomy ][] = $tag_id;
					} else {
						$tax_groups[ $tag_tax ][] = $tag_id;
					}
				}
				foreach ( $tax_groups as $taxonomy => $ids ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $ids,
						'operator' => 'IN',
					);
				}
			}
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for filtering content by terms.
			$query_args['tax_query'] = $tax_query;
		}
	}
}
