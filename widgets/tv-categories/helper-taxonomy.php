<?php
/**
 * Elonix Categories Helper Taxonomy Engine
 *
 * Safe query parsing, dynamic whitelisting, image resolving, and dynamic transient
 * caching with automated cache invalidation hooks.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Taxonomy_Helper {

	/**
	 * Main query method with smart cache checking.
	 *
	 * @param array $settings Widget controls configuration.
	 * @return array Standardized terms array.
	 */
	public static function get_categories_data( $settings ) {
		// 1. Resolve taxonomy name
		$taxonomy = ! empty( $settings['taxonomy'] ) ? sanitize_text_field( $settings['taxonomy'] ) : 'category';

		// Verify taxonomy is registered on the system
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		// WooCommerce active guards
		if ( in_array( $taxonomy, array( 'product_cat', 'product_tag' ), true ) && ! class_exists( 'WooCommerce' ) ) {
			return array();
		}

		// 4. Build query parameters
		$query_args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => ( 'yes' === $settings['hide_empty'] ),
		);

		// Handle Order parameters
		$query_args['orderby'] = ! empty( $settings['orderby'] ) ? sanitize_text_field( $settings['orderby'] ) : 'name';
		$query_args['order']   = ! empty( $settings['order'] ) ? sanitize_text_field( $settings['order'] ) : 'ASC';

		// Limit parameter
		$limit                = ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : 8;
		$query_args['number'] = min( 100, max( 1, $limit ) );

		// Query Selection Mode / Featured options
		$mode = ! empty( $settings['selection_mode'] ) ? $settings['selection_mode'] : 'all';

		if ( 'manual' === $mode ) {
			$manual_ids = ! empty( $settings['manual_ids'] ) ? array_map( 'absint', (array) explode( ',', $settings['manual_ids'] ) ) : array();
			if ( ! empty( $manual_ids ) ) {
				$query_args['include'] = $manual_ids;
				unset( $query_args['number'] ); // Include overrides number restrictions
			}
		} elseif ( 'popular' === $mode ) {
			$query_args['orderby'] = 'count';
			$query_args['order']   = 'DESC';
		} elseif ( 'recent' === $mode ) {
			$query_args['orderby'] = 'term_id';
			$query_args['order']   = 'DESC';
		} elseif ( 'featured' === $mode ) {
			$query_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'tv_cat_featured',
					'value'   => 'yes',
					'compare' => '=',
				),
			);
		}

		// Include / Exclude filters
		if ( 'manual' !== $mode ) {
			if ( ! empty( $settings['include_ids'] ) ) {
				$query_args['include'] = array_map( 'absint', (array) explode( ',', $settings['include_ids'] ) );
			}
			if ( ! empty( $settings['exclude_ids'] ) ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- User-configurable taxonomy exclusion is an intentional widget feature.
				$query_args['exclude'] = array_map( 'absint', (array) explode( ',', $settings['exclude_ids'] ) );
			}
		}

		// Parent/Child only rules
		if ( ! empty( $settings['parent_only'] ) && 'yes' === $settings['parent_only'] ) {
			$query_args['parent'] = 0;
		}

		// If child_only is enabled, fetch more terms to filter in PHP
		if ( ! empty( $settings['child_only'] ) && 'yes' === $settings['child_only'] ) {
			$query_args['number'] = 100;
		}

		// Run terms query
		$terms      = get_terms( $query_args );
		$terms_data = array();

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$count        = 0;
			$target_limit = min( 100, max( 1, $limit ) );
			foreach ( $terms as $term ) {
				// Child only filter: must have a parent ID > 0
				if ( ! empty( $settings['child_only'] ) && 'yes' === $settings['child_only'] && 0 === $term->parent ) {
					continue;
				}
				$terms_data[] = self::format_term_data( $term, $settings );
				++$count;
				if ( 'manual' !== $mode && $count >= $target_limit ) {
					break;
				}
			}
		}

		return $terms_data;
	}

	/**
	 * Helper: Format taxonomy term data securely.
	 */
	private static function format_term_data( $term, $settings ) {
		$term_id = $term->term_id;

		$data = array(
			'id'          => $term_id,
			'name'        => esc_html( $term->name ),
			'slug'        => esc_attr( $term->slug ),
			'description' => esc_html( $term->description ),
			'count'       => intval( $term->count ),
			'url'         => esc_url( get_term_link( $term ) ),
			'image'       => '',
			'icon'        => '',
			'badge'       => '',
			'parent_name' => '',
			'parent_url'  => '',
			'children'    => array(),
		);

		// Get Category Image
		$data['image'] = self::resolve_category_image( $term_id, $term->taxonomy, $settings );

		// Resolve Category Icon Architecture based on approved priority order
		$icon_type  = 'font';
		$icon_value = '';

		// Search for override item for this term_id
		$override_item = null;
		if ( ! empty( $settings['overrides_list'] ) && is_array( $settings['overrides_list'] ) ) {
			foreach ( $settings['overrides_list'] as $override ) {
				if ( ! empty( $override['term_id'] ) && intval( $override['term_id'] ) === $term_id ) {
					$override_item = $override;
					break;
				}
			}
		}

		// 1. Custom Icon (term override picker)
		if ( ! empty( $override_item['override_icon']['value'] ) ) {
			$icon_type  = 'font';
			$icon_value = $override_item['override_icon'];
		}
		// 2. SVG Upload
		elseif ( ! empty( $override_item['override_svg']['url'] ) ) {
			$icon_type  = 'svg';
			$icon_value = esc_url( $override_item['override_svg']['url'] );
		} elseif ( ! empty( $settings['cat_icon_source'] ) && 'svg_upload' === $settings['cat_icon_source'] && ! empty( $settings['cat_icon_svg']['url'] ) ) {
			$icon_type  = 'svg';
			$icon_value = esc_url( $settings['cat_icon_svg']['url'] );
		} elseif ( ( $meta_svg = get_term_meta( $term_id, 'tv_cat_svg', true ) ) ) {
			$icon_type  = 'svg';
			$icon_value = esc_url( $meta_svg );
		}
		// 3. Image Upload
		elseif ( ! empty( $override_item['override_image_icon']['url'] ) ) {
			$icon_type  = 'image';
			$icon_value = esc_url( $override_item['override_image_icon']['url'] );
		} elseif ( ! empty( $settings['cat_icon_source'] ) && 'image_upload' === $settings['cat_icon_source'] && ! empty( $settings['cat_icon_image']['url'] ) ) {
			$icon_type  = 'image';
			$icon_value = esc_url( $settings['cat_icon_image']['url'] );
		} elseif ( ( $meta_image_icon = get_term_meta( $term_id, 'tv_cat_image_icon', true ) ) ) {
			$icon_type  = 'image';
			$icon_value = esc_url( $meta_image_icon );
		}
		// 4. Dynamic Term Icon
		elseif ( ( $meta_icon = get_term_meta( $term_id, 'tv_cat_icon', true ) ) ) {
			$icon_type  = 'font';
			$icon_value = array(
				'value'   => esc_attr( $meta_icon ),
				'library' => 'fa-solid',
			);
		}
		// 5. WooCommerce Category Icon
		elseif ( 'product_cat' === $term->taxonomy && class_exists( 'WooCommerce' ) && ( $woo_thumb_id = get_term_meta( $term_id, 'thumbnail_id', true ) ) ) {
			$woo_image = wp_get_attachment_image_url( $woo_thumb_id, 'thumbnail' );
			if ( $woo_image ) {
				$icon_type  = 'image';
				$icon_value = esc_url( $woo_image );
			}
		}

		// 6. Default Fallback Icon
		if ( empty( $icon_value ) ) {
			if ( ! empty( $settings['cat_icon_source'] ) && 'icon_library' === $settings['cat_icon_source'] && ! empty( $settings['cat_icon_library']['value'] ) ) {
				$icon_type  = 'font';
				$icon_value = $settings['cat_icon_library'];
			} else {
				$icon_type  = 'font';
				$icon_value = array(
					'value'   => 'fas fa-folder',
					'library' => 'solid',
				);
			}
		}

		$data['icon_type']  = $icon_type;
		$data['icon_value'] = $icon_value;

		// Set dynamic icon string for backward compatibility
		if ( 'font' === $icon_type ) {
			$data['icon'] = is_array( $icon_value ) ? esc_attr( $icon_value['value'] ) : esc_attr( $icon_value );
		} else {
			$data['icon'] = '';
		}

		// Parent metadata lookup if term has a parent
		if ( $term->parent > 0 ) {
			$parent_term = get_term( $term->parent, $term->taxonomy );
			if ( ! is_wp_error( $parent_term ) && ! empty( $parent_term ) ) {
				$data['parent_name'] = esc_html( $parent_term->name );
				$data['parent_url']  = esc_url( get_term_link( $parent_term ) );
			}
		}

		// Apply Custom Overrides for Name, Image, and Badge
		if ( $override_item ) {
			if ( ! empty( $override_item['override_title'] ) ) {
				$data['name'] = esc_html( $override_item['override_title'] );
			}
			if ( ! empty( $override_item['override_image']['url'] ) ) {
				$data['image'] = esc_url( $override_item['override_image']['url'] );
			}
			if ( ! empty( $override_item['override_badge'] ) ) {
				$data['badge'] = esc_html( $override_item['override_badge'] );
			}
		}

		// If children layout rendering is enabled, query nested children nodes
		if ( ! empty( $settings['nested_categories'] ) && 'yes' === $settings['nested_categories'] ) {
			$child_args     = array(
				'taxonomy'   => $term->taxonomy,
				'parent'     => $term_id,
				'hide_empty' => ( 'yes' === $settings['hide_empty'] ),
			);
			$children_terms = get_terms( $child_args );
			if ( ! is_wp_error( $children_terms ) && ! empty( $children_terms ) ) {
				foreach ( $children_terms as $child_term ) {
					$data['children'][] = self::format_term_data( $child_term, $settings );
				}
			}
		}

		return $data;
	}

	/**
	 * Helper: Resolve category images including WooCommerce dynamic image assets.
	 */
	private static function resolve_category_image( $term_id, $taxonomy, $settings ) {
		// WooCommerce category image check
		if ( 'product_cat' === $taxonomy && class_exists( 'WooCommerce' ) ) {
			$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
			if ( $thumbnail_id ) {
				$image_url = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
				if ( $image_url ) {
					return esc_url( $image_url );
				}
			}
		}

		// Generic Taxonomy featured image (using custom term metadata common in themes)
		$tax_image_id = get_term_meta( $term_id, 'tv_category_image', true );
		if ( ! empty( $tax_image_id ) ) {
			$image_url = wp_get_attachment_image_url( $tax_image_id, 'medium' );
			if ( $image_url ) {
				return esc_url( $image_url );
			}
		}

		// Fallback term image helper
		$image_meta_url = get_term_meta( $term_id, 'image_url', true );
		if ( ! empty( $image_meta_url ) ) {
			return esc_url( $image_meta_url );
		}

		// Fallback image option from settings
		if ( ! empty( $settings['fallback_image']['url'] ) ) {
			return esc_url( $settings['fallback_image']['url'] );
		}

		// Default placeholder fallback
		return ELONIX_ACC_URL . 'assets/images/placeholder.jpg';
	}
}
