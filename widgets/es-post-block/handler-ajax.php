<?php
/**
 * Elonix Premium Post Block AJAX Handler
 *
 * Handles AJAX requests for pagination, load-more operations, category swaps,
 * tag filter swaps, search parameters, and order sorting with nonce validation.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_Block_AJAX {

	/**
	 * Constructor: Register actions.
	 */
	public function __construct() {
		add_action( 'wp_ajax_es_post_block_fetch_posts', array( $this, 'ajax_fetch_posts' ) );
		add_action( 'wp_ajax_nopriv_es_post_block_fetch_posts', array( $this, 'ajax_fetch_posts' ) );
	}

	/**
	 * Callback handler to retrieve HTML nodes.
	 */
	public function ajax_fetch_posts() {
		// 1. Security Check: Validate AJAX request nonce and check basic permissions
		check_ajax_referer( 'es-post-block-nonce', 'security' );

		if ( is_user_logged_in() && ! current_user_can( 'read' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'elonix' ), 403 );
		}

		// 2. Read parameters
		$settings_raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$paged        = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
		$category     = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
		$tag          = isset( $_POST['tag'] ) ? intval( $_POST['tag'] ) : 0;
		$author       = isset( $_POST['author'] ) ? intval( $_POST['author'] ) : 0;
		$search       = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		if ( empty( $settings_raw ) || ! is_array( $settings_raw ) ) {
			wp_send_json_error( esc_html__( 'Invalid settings parameter.', 'elonix' ), 400 );
		}

		// Standardize and strictly validate settings using a whitelist
		$settings = self::validate_ajax_settings( $settings_raw );

		// Decode original archive variables to preserve archive template context in AJAX
		$archive_vars_raw = isset( $_POST['archive_vars'] ) ? wp_unslash( $_POST['archive_vars'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$archive_vars     = array();
		if ( ! empty( $archive_vars_raw ) ) {
			$archive_vars = json_decode( $archive_vars_raw, true );
			if ( ! is_array( $archive_vars ) ) {
				$archive_vars = array();
			}
		}

		// Inject dynamic overrides
		$settings['paged'] = $paged;
		if ( isset( $_POST['category'] ) ) {
			$category = intval( $_POST['category'] );
			if ( $category > 0 ) {
				$settings['categories_filter'] = array( $category );
				if ( empty( $settings['query_source'] ) ) {
					$settings['query_source'] = 'posts';
				}
			}
		}
		if ( isset( $_POST['tag'] ) ) {
			$tag = intval( $_POST['tag'] );
			if ( $tag > 0 ) {
				$settings['tags_filter'] = array( $tag );
				if ( empty( $settings['query_source'] ) ) {
					$settings['query_source'] = 'posts';
				}
			}
		}
		if ( isset( $_POST['author'] ) ) {
			$author = intval( $_POST['author'] );
			if ( $author > 0 ) {
				$settings['author_ids'] = array( $author );
				if ( empty( $settings['query_source'] ) ) {
					$settings['query_source'] = 'posts';
				}
			}
		}
		if ( isset( $_POST['search'] ) ) {
			$settings['s'] = sanitize_text_field( wp_unslash( $_POST['search'] ) );
		}

		// Load Query Helper if not loaded
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Query_Helper' ) ) {
			require_once __DIR__ . '/helper-query.php';
		}

		// Load standalone renderer — safe at any boot phase (no Elementor inheritance).
		if ( ! class_exists( 'Elonix_Toolkit_Post_Block_Renderer' ) ) {
			require_once __DIR__ . '/class-renderer.php';
		}

		// 3. Build WP_Query arguments
		$query_args = \Elonix_Query_Context::build_query_args( $settings, $archive_vars );
		if ( isset( $_POST['search'] ) ) {
			$search = sanitize_text_field( wp_unslash( $_POST['search'] ) );
			if ( '' === $search ) {
				unset( $query_args['s'] );
			} else {
				$query_args['s'] = $search;
			}
		}

		// Run query
		$query      = new WP_Query( $query_args );
		$posts_html = '';

		$layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'style_1';

		if ( $query->have_posts() ) {
			ob_start();
			$post_index  = 0;
			$has_wrapper = false;
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;

				$item = Elonix_Toolkit_Post_Block_Query_Helper::format_post_data( $post, $settings );

				if ( 'style_3' === $layout && 1 === $post_index ) {
					echo '<div class="es-grid-secondary-wrapper">';
					$has_wrapper = true;
				}

				Elonix_Toolkit_Post_Block_Renderer::render_single_post( $item, $settings, $layout, $post_index );
				++$post_index;
			}
			if ( $has_wrapper ) {
				echo '</div>';
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

	/**
	 * Whitelist and strictly validate incoming AJAX settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array Validated settings.
	 */
	protected static function validate_ajax_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$validated = array();

		// Whitelist of keys we allow to be processed
		$allowed_keys = array(
			'query_mode'               => 'string',
			'query_source'             => 'string',
			'custom_post_type'         => 'post_type',
			'manual_ids'               => 'id_list',
			'thumbnail_size'           => 'string',
			'categories_filter'        => 'id_array',
			'tags_filter'              => 'id_array',
			'author_ids'               => 'id_array',
			'limit'                    => 'integer',
			'offset'                   => 'integer',
			'ignore_sticky_posts'      => 'string',
			'orderby'                  => 'orderby',
			'order'                    => 'order',
			'date_filter'              => 'string',
			'custom_date_after'        => 'string',
			'custom_date_before'       => 'string',
			'exclude_ids'              => 'id_list',
			'layout'                   => 'string',
			'show_featured'            => 'string',
			'featured_mode'            => 'string',
			'featured_manual_id'       => 'integer',
			'title_tag'                => 'string',
			'title_word_limit'         => 'integer',
			'title_char_limit'         => 'integer',
			'title_suffix'             => 'string',
			'excerpt_word_limit'       => 'integer',
			'excerpt_char_limit'       => 'integer',
			'excerpt_strip_html'       => 'string',
			'excerpt_strip_shortcodes' => 'string',
			'excerpt_suffix'           => 'string',
			'badge_type'               => 'string',
			'badge_text'               => 'string',
			'meta_elements_order'      => 'array_custom',
			'post_element_order'       => 'array_custom',
			'show_new_badge'           => 'string',
			'show_popular_badge'       => 'string',
			'show_trending_badge'      => 'string',
			'show_sponsored_label'     => 'string',
			'read_more_text'           => 'string',
			'pagination_type'          => 'string',
			'pagination_text'          => 'string',
			'ajax_tabs'                => 'string',
			'ajax_search'              => 'string',
			'badge_icon'               => 'array_icon',
			'read_more_icon'           => 'array_icon',
			'meta_author_icon'         => 'array_icon',
			'meta_date_icon'           => 'array_icon',
			'meta_comments_icon'       => 'array_icon',
			'meta_reading_time_icon'   => 'array_icon',
			'meta_views_icon'          => 'array_icon',
			'share_icon'               => 'array_icon',
		);

		foreach ( $allowed_keys as $key => $type ) {
			if ( ! isset( $settings[ $key ] ) ) {
				continue;
			}

			$val = $settings[ $key ];

			switch ( $type ) {
				case 'string':
					$validated[ $key ] = sanitize_text_field( $val );
					break;

				case 'integer':
					$validated[ $key ] = intval( $val );
					// Cap limits for safety
					if ( 'limit' === $key ) {
						$validated[ $key ] = min( max( 1, $validated[ $key ] ), 100 );
					}
					break;

				case 'post_type':
					$post_type         = sanitize_key( $val );
					$public_post_types = get_post_types( array( 'public' => true ) );
					if ( in_array( $post_type, $public_post_types, true ) && 'attachment' !== $post_type ) {
						$validated[ $key ] = $post_type;
					} else {
						$validated[ $key ] = 'post';
					}
					break;

				case 'id_list':
					if ( is_string( $val ) ) {
						$ids               = explode( ',', $val );
						$ids               = array_map( 'absint', array_filter( $ids ) );
						$validated[ $key ] = implode( ',', $ids );
					} elseif ( is_array( $val ) ) {
						$ids               = array_map( 'absint', array_filter( $val ) );
						$validated[ $key ] = implode( ',', $ids );
					} else {
						$validated[ $key ] = '';
					}
					break;

				case 'id_array':
					if ( is_array( $val ) ) {
						$validated[ $key ] = array_map( 'absint', array_filter( $val ) );
					} elseif ( is_string( $val ) ) {
						$ids               = explode( ',', $val );
						$validated[ $key ] = array_map( 'absint', array_filter( $ids ) );
					} else {
						$validated[ $key ] = array();
					}
					break;

				case 'orderby':
					$allowed_orderby = array( 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num', 'post__in' );
					$clean_orderby   = sanitize_text_field( $val );
					if ( in_array( $clean_orderby, $allowed_orderby, true ) ) {
						$validated[ $key ] = $clean_orderby;
					} else {
						$validated[ $key ] = 'date';
					}
					break;

				case 'order':
					$clean_order = strtoupper( sanitize_key( $val ) );
					if ( in_array( $clean_order, array( 'ASC', 'DESC' ), true ) ) {
						$validated[ $key ] = $clean_order;
					} else {
						$validated[ $key ] = 'DESC';
					}
					break;

				case 'array_icon':
					if ( is_array( $val ) ) {
						$validated[ $key ] = array(
							'value'   => isset( $val['value'] ) ? sanitize_text_field( $val['value'] ) : '',
							'library' => isset( $val['library'] ) ? sanitize_key( $val['library'] ) : '',
						);
					}
					break;

				case 'array_custom':
					if ( is_array( $val ) ) {
						$validated[ $key ] = self::sanitize_custom_repeater( $val );
					}
					break;
			}
		}

		return $validated;
	}

	/**
	 * Helper to sanitize visual elements repeater items.
	 *
	 * @param array $array Raw repeater array.
	 * @return array Sanitized repeater.
	 */
	protected static function sanitize_custom_repeater( $array ) {
		$sanitized = array();
		foreach ( $array as $k => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$clean_item = array();
			foreach ( $item as $sub_key => $sub_val ) {
				$clean_sub_key = sanitize_key( $sub_key );
				if ( is_array( $sub_val ) ) {
					$clean_item[ $clean_sub_key ] = self::sanitize_custom_repeater( $sub_val );
				} else {
					$clean_item[ $clean_sub_key ] = sanitize_text_field( $sub_val );
				}
			}
			$sanitized[ intval( $k ) ] = $clean_item;
		}
		return $sanitized;
	}
}

// Instantiate handler on load
new Elonix_Toolkit_Post_Block_AJAX();
