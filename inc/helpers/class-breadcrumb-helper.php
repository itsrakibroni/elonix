<?php
/**
 * Elonix – Toolkit for Elementor Breadcrumb Helper Class
 *
 * Provides reusable breadcrumb generation logic.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Breadcrumb_Helper {

	/**
	 * Get breadcrumb items based on current page and settings.
	 *
	 * @param array $settings Widget settings.
	 * @return array Breadcrumb items array.
	 */
	public static function get_breadcrumbs( $settings = array() ) {
		$breadcrumbs = array();

		// 1. Check third-party SEO plugin integrations if selected or auto-detected
		$source = isset( $settings['breadcrumb_source'] ) ? $settings['breadcrumb_source'] : 'auto';

		if ( 'auto' === $source || 'rank_math' === $source || 'yoast' === $source || 'seopress' === $source ) {
			$seo_crumbs = self::get_seo_plugin_breadcrumbs( $source );
			if ( ! empty( $seo_crumbs ) ) {
				return $seo_crumbs;
			}
		}

		// 2. Handle WooCommerce pages specifically if active
		if ( class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
			$wc_crumbs = self::get_woocommerce_breadcrumbs();
			if ( ! empty( $wc_crumbs ) ) {
				return $wc_crumbs;
			}
		}

		// 3. Fallback to WordPress core breadcrumbs generator
		return self::get_wordpress_breadcrumbs( $settings );
	}

	/**
	 * Retrieve breadcrumbs from Yoast, Rank Math, or SEOPress if active and requested.
	 *
	 * @param string $source Selected source.
	 * @return array Breadcrumb items array, or empty if none found.
	 */
	private static function get_seo_plugin_breadcrumbs( $source ) {
		$items = array();

		// Rank Math Integration
		if ( ( 'auto' === $source || 'rank_math' === $source ) && function_exists( 'rank_math_get_breadcrumbs' ) ) {
			if ( class_exists( '\RankMath\Frontend\Breadcrumbs' ) ) {
				$rm_breadcrumbs = new \RankMath\Frontend\Breadcrumbs();
				$crumbs         = $rm_breadcrumbs->get_crumbs();
				if ( is_array( $crumbs ) ) {
					foreach ( $crumbs as $crumb ) {
						$items[] = array(
							'title' => isset( $crumb[0] ) ? $crumb[0] : '',
							'url'   => isset( $crumb[1] ) ? $crumb[1] : '',
							'class' => '',
						);
					}
					return $items;
				}
			}
		}

		// Yoast SEO Integration
		if ( ( 'auto' === $source || 'yoast' === $source ) && class_exists( '\WPSEO_Breadcrumbs' ) ) {
			$links = \WPSEO_Breadcrumbs::get_instance()->get_links();
			if ( is_array( $links ) ) {
				foreach ( $links as $link ) {
					$title = isset( $link['text'] ) ? $link['text'] : '';
					if ( isset( $link['id'] ) ) {
						$url = get_permalink( $link['id'] );
					} elseif ( isset( $link['url'] ) ) {
						$url = $link['url'];
					} else {
						$url = '';
					}
					$items[] = array(
						'title' => $title,
						'url'   => $url,
						'class' => '',
					);
				}
				return $items;
			}
		}

		return $items;
	}

	/**
	 * Retrieve WooCommerce breadcrumbs using WC_Breadcrumb class.
	 *
	 * @return array Breadcrumb items array.
	 */
	private static function get_woocommerce_breadcrumbs() {
		$items = array();
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $items;
		}

		$wc_breadcrumb = new \WC_Breadcrumb();
		$crumbs        = $wc_breadcrumb->generate();

		if ( is_array( $crumbs ) && ! empty( $crumbs ) ) {
			$count = count( $crumbs );
			foreach ( $crumbs as $index => $crumb ) {
				$is_last = ( $index === $count - 1 );
				$items[] = array(
					'title' => $crumb[0],
					'url'   => $is_last ? '' : $crumb[1],
					'class' => $is_last ? 'elonix-breadcrumb-last' : ( 0 === $index ? 'elonix-breadcrumb-first' : '' ),
				);
			}
		}

		return $items;
	}

	/**
	 * Generate standard WordPress breadcrumbs array.
	 *
	 * @param array $settings Widget settings.
	 * @return array Breadcrumb items array.
	 */
	private static function get_wordpress_breadcrumbs( $settings ) {
		$items = array();

		$home_text = isset( $settings['home_text'] ) && ! empty( $settings['home_text'] ) ? $settings['home_text'] : __( 'Home', 'elonix' );
		$show_home = isset( $settings['show_home'] ) ? $settings['show_home'] : 'yes';

		// 1. Home link
		if ( 'yes' === $show_home ) {
			$items[] = array(
				'title' => $home_text,
				'url'   => home_url( '/' ),
				'class' => 'elonix-breadcrumb-first',
			);
		}

		if ( is_front_page() || is_home() && 'yes' === $show_home && count( $items ) === 1 ) {
			if ( is_home() && get_option( 'page_for_posts' ) ) {
				$items[] = array(
					'title' => get_the_title( get_option( 'page_for_posts' ) ),
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
			return $items;
		}

		// 2. Singular posts, pages, CPTs
		if ( is_singular() ) {
			$post_type = get_post_type();

			if ( 'page' === $post_type ) {
				$parents = get_post_ancestors( get_the_ID() );
				if ( ! empty( $parents ) && 'yes' === ( isset( $settings['show_parent_pages'] ) ? $settings['show_parent_pages'] : 'yes' ) ) {
					foreach ( array_reverse( $parents ) as $parent ) {
						$items[] = array(
							'title' => get_the_title( $parent ),
							'url'   => get_permalink( $parent ),
							'class' => '',
						);
					}
				}
			} else {
				$post_type_obj = get_post_type_object( $post_type );
				if ( $post_type_obj && $post_type_obj->has_archive && 'yes' === ( isset( $settings['show_post_type'] ) ? $settings['show_post_type'] : 'yes' ) ) {
					$items[] = array(
						'title' => $post_type_obj->labels->name,
						'url'   => get_post_type_archive_link( $post_type ),
						'class' => '',
					);
				}

				if ( 'yes' === ( isset( $settings['show_category'] ) ? $settings['show_category'] : 'yes' ) ) {
					$taxonomies       = get_object_taxonomies( $post_type, 'objects' );
					$hierarchical_tax = '';
					foreach ( $taxonomies as $tax ) {
						if ( $tax->hierarchical ) {
							$hierarchical_tax = $tax->name;
							break;
						}
					}

					if ( empty( $hierarchical_tax ) && ! empty( $taxonomies ) ) {
						$first_tax        = reset( $taxonomies );
						$hierarchical_tax = $first_tax->name;
					}

					if ( ! empty( $hierarchical_tax ) ) {
						$terms = get_the_terms( get_the_ID(), $hierarchical_tax );
						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							$main_term = $terms[0];
							if ( class_exists( 'WPSEO_Primary_Term' ) ) {
								$wpseo_primary = new \WPSEO_Primary_Term( $hierarchical_tax, get_the_ID() );
								$primary_id    = $wpseo_primary->get_primary_term();
								if ( $primary_id ) {
									$primary_term = get_term( $primary_id );
									if ( $primary_term && ! is_wp_error( $primary_term ) ) {
										$main_term = $primary_term;
									}
								}
							}

							$ancestors = get_ancestors( $main_term->term_id, $hierarchical_tax );
							if ( ! empty( $ancestors ) ) {
								foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
									$ancestor_term = get_term( $ancestor_id, $hierarchical_tax );
									if ( $ancestor_term && ! is_wp_error( $ancestor_term ) ) {
										$items[] = array(
											'title' => $ancestor_term->name,
											'url'   => get_term_link( $ancestor_term ),
											'class' => '',
										);
									}
								}
							}

							$items[] = array(
								'title' => $main_term->name,
								'url'   => get_term_link( $main_term ),
								'class' => '',
							);
						}
					}
				}
			}

			if ( 'yes' === ( isset( $settings['show_current_page'] ) ? $settings['show_current_page'] : 'yes' ) ) {
				$title = get_the_title();
				if ( isset( $settings['truncate_title'] ) && 'yes' === $settings['truncate_title'] ) {
					$limit = isset( $settings['truncate_limit']['size'] ) ? intval( $settings['truncate_limit']['size'] ) : 30;
					if ( strlen( $title ) > $limit ) {
						$title = substr( $title, 0, $limit ) . '...';
					}
				}
				$items[] = array(
					'title' => $title,
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
		}

		// 3. Taxonomies Archives
		elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term ) {
				$taxonomy  = $term->taxonomy;
				$ancestors = get_ancestors( $term->term_id, $taxonomy );
				if ( ! empty( $ancestors ) ) {
					foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
						$ancestor_term = get_term( $ancestor_id, $taxonomy );
						if ( $ancestor_term && ! is_wp_error( $ancestor_term ) ) {
							$items[] = array(
								'title' => $ancestor_term->name,
								'url'   => get_term_link( $ancestor_term ),
								'class' => '',
							);
						}
					}
				}

				$items[] = array(
					'title' => $term->name,
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
		}

		// 4. Post Type Archives
		elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj && 'yes' === ( isset( $settings['show_archive'] ) ? $settings['show_archive'] : 'yes' ) ) {
				$items[] = array(
					'title' => $post_type_obj->labels->name,
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
		}

		// 5. Author Archives
		elseif ( is_author() ) {
			$author = get_queried_object();
			if ( $author ) {
				$items[] = array(
					/* translators: %s: string */
					'title' => sprintf( __( 'Author: %s', 'elonix' ), $author->display_name ),
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
		}

		// 6. Date Archives
		elseif ( is_date() ) {
			if ( is_year() ) {
				$items[] = array(
					'title' => get_the_date( 'Y' ),
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			} elseif ( is_month() ) {
				$items[] = array(
					'title' => get_the_date( 'F Y' ),
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			} elseif ( is_day() ) {
				$items[] = array(
					'title' => get_the_date( 'F j, Y' ),
					'url'   => '',
					'class' => 'elonix-breadcrumb-last',
				);
			}
		}

		// 7. Search Results
		elseif ( is_search() ) {
			$search_text = isset( $settings['search_text'] ) && ! empty( $settings['search_text'] ) ? $settings['search_text'] : __( 'Search results for: ', 'elonix' );
			$items[]     = array(
				'title' => $search_text . ' "' . get_search_query() . '"',
				'url'   => '',
				'class' => 'elonix-breadcrumb-last',
			);
		}

		// 8. 404 Pages
		elseif ( is_404() ) {
			$error_text = isset( $settings['error_text'] ) && ! empty( $settings['error_text'] ) ? $settings['error_text'] : __( 'Page Not Found (404)', 'elonix' );
			$items[]    = array(
				'title' => $error_text,
				'url'   => '',
				'class' => 'elonix-breadcrumb-last',
			);
		}

		return $items;
	}
}
