<?php
/**
 * Elonix – Toolkit for Elementor Dynamic Data Engine
 *
 * Centralized service for resolving context and retrieving data
 * across Dynamic Tags, Dynamic Widgets, and Builders.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Dynamic_Data {

	/**
	 * Cache instances.
	 */
	private $cache = array(
		'posts'    => array(),
		'authors'  => array(),
		'terms'    => array(),
		'users'    => array(),
		'products' => array(),
	);

	/**
	 * Context Stack.
	 */
	private $context_stack = array();

	/**
	 * Current context overrides.
	 */
	private $current_context = array(
		'post_id'   => null,
		'author_id' => null,
		'term_id'   => null,
		'user_id'   => null,
	);

	/**
	 * Instance.
	 */
	private static $_instance = null;

	/**
	 * Get instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Push a new context onto the stack.
	 *
	 * @param array $context Associative array of context overrides (post_id, author_id, etc.)
	 */
	public function push_context( $context ) {
		// Save current context to stack
		$this->context_stack[] = $this->current_context;

		// Merge new context
		$this->current_context = wp_parse_args( $context, $this->current_context );
	}

	/**
	 * Pop the last context from the stack.
	 */
	public function pop_context() {
		if ( ! empty( $this->context_stack ) ) {
			$this->current_context = array_pop( $this->context_stack );
		}
	}

	/**
	 * Clear local request cache.
	 */
	public function clear_cache() {
		$this->cache = array(
			'posts'    => array(),
			'authors'  => array(),
			'terms'    => array(),
			'users'    => array(),
			'products' => array(),
		);
	}

	/**
	 * Get Current Post Context.
	 *
	 * @return \WP_Post|null
	 */
	public function get_current_post() {
		$post_id = $this->current_context['post_id'] ? $this->current_context['post_id'] : get_the_ID();
		if ( ! $post_id ) {
			return apply_filters( 'elonix/dynamic_data/current_post', null, $this );
		}

		if ( isset( $this->cache['posts'][ $post_id ] ) ) {
			return apply_filters( 'elonix/dynamic_data/current_post', $this->cache['posts'][ $post_id ], $this );
		}

		$post = get_post( $post_id );
		if ( $post ) {
			$this->cache['posts'][ $post_id ] = $post;
		}

		return apply_filters( 'elonix/dynamic_data/current_post', $post, $this );
	}

	/**
	 * Get Current Author Context.
	 *
	 * @return \WP_User|null
	 */
	public function get_current_author() {
		$author_id = $this->current_context['author_id'];

		if ( ! $author_id ) {
			if ( is_author() ) {
				$author_id = get_queried_object_id();
			} else {
				$post = $this->get_current_post();
				if ( $post ) {
					$author_id = $post->post_author;
				}
			}
		}

		if ( ! $author_id ) {
			return apply_filters( 'elonix/dynamic_data/current_author', null, $this );
		}

		if ( isset( $this->cache['authors'][ $author_id ] ) ) {
			return apply_filters( 'elonix/dynamic_data/current_author', $this->cache['authors'][ $author_id ], $this );
		}

		$author = get_userdata( $author_id );
		if ( $author ) {
			$this->cache['authors'][ $author_id ] = $author;
		}

		return apply_filters( 'elonix/dynamic_data/current_author', $author, $this );
	}

	/**
	 * Get Current Term Context.
	 *
	 * @return \WP_Term|null
	 */
	public function get_current_term() {
		$term_id = $this->current_context['term_id'];

		if ( ! $term_id ) {
			if ( is_tax() || is_category() || is_tag() ) {
				$term = get_queried_object();
				if ( $term instanceof \WP_Term ) {
					$this->cache['terms'][ $term->term_id ] = $term;
					return apply_filters( 'elonix/dynamic_data/current_term', $term, $this );
				}
			}

			// Try to get terms for current post
			$post = $this->get_current_post();
			if ( $post ) {
				$taxonomies = get_object_taxonomies( $post->post_type );
				if ( ! empty( $taxonomies ) ) {
					$terms = get_the_terms( $post->ID, $taxonomies[0] );
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
						$this->cache['terms'][ $terms[0]->term_id ] = $terms[0];
						return apply_filters( 'elonix/dynamic_data/current_term', $terms[0], $this );
					}
				}
			}
			return apply_filters( 'elonix/dynamic_data/current_term', null, $this );
		}

		if ( isset( $this->cache['terms'][ $term_id ] ) ) {
			return apply_filters( 'elonix/dynamic_data/current_term', $this->cache['terms'][ $term_id ], $this );
		}

		$term = get_term( $term_id );
		if ( ! is_wp_error( $term ) && $term ) {
			$this->cache['terms'][ $term_id ] = $term;
			return apply_filters( 'elonix/dynamic_data/current_term', $term, $this );
		}

		return apply_filters( 'elonix/dynamic_data/current_term', null, $this );
	}

	/**
	 * Get Current User Context.
	 *
	 * @return \WP_User|null
	 */
	public function get_current_user() {
		$user_id = $this->current_context['user_id'] ? $this->current_context['user_id'] : get_current_user_id();

		if ( ! $user_id ) {
			return apply_filters( 'elonix/dynamic_data/current_user', null, $this );
		}

		if ( isset( $this->cache['users'][ $user_id ] ) ) {
			return apply_filters( 'elonix/dynamic_data/current_user', $this->cache['users'][ $user_id ], $this );
		}

		$user = get_userdata( $user_id );
		if ( $user ) {
			$this->cache['users'][ $user_id ] = $user;
		}

		return apply_filters( 'elonix/dynamic_data/current_user', $user, $this );
	}

	/**
	 * Get Current WooCommerce Product Context.
	 *
	 * @return \WC_Product|null
	 */
	public function get_current_product() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return apply_filters( 'elonix/dynamic_data/current_product', null, $this );
		}

		$post = $this->get_current_post();
		if ( ! $post || 'product' !== $post->post_type ) {
			return apply_filters( 'elonix/dynamic_data/current_product', null, $this );
		}

		$product_id = $post->ID;

		if ( isset( $this->cache['products'][ $product_id ] ) ) {
			return apply_filters( 'elonix/dynamic_data/current_product', $this->cache['products'][ $product_id ], $this );
		}

		$product = wc_get_product( $product_id );
		if ( $product ) {
			$this->cache['products'][ $product_id ] = $product;
		}

		return apply_filters( 'elonix/dynamic_data/current_product', $product, $this );
	}

	/**
	 * Get Current Archive Title.
	 *
	 * @return string
	 */
	public function get_current_archive_title() {
		return get_the_archive_title();
	}

	/**
	 * Get Current Archive Description.
	 *
	 * @return string
	 */
	public function get_current_archive_description() {
		return get_the_archive_description();
	}

	/**
	 * Get Current Archive URL.
	 *
	 * @return string
	 */
	public function get_current_archive_url() {
		if ( is_post_type_archive() ) {
			return get_post_type_archive_link( get_query_var( 'post_type' ) );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			return get_term_link( get_queried_object() );
		} elseif ( is_author() ) {
			return get_author_posts_url( get_query_var( 'author' ) );
		}
		return '';
	}

	/**
	 * Get Search Query String.
	 *
	 * @return string
	 */
	public function get_search_query() {
		if ( is_search() ) {
			return get_search_query();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_GET['s'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_text_field( wp_unslash( $_GET['s'] ) );
		}
		return '';
	}

	/**
	 * Get Search Result Count.
	 *
	 * @return int
	 */
	public function get_search_count() {
		if ( is_search() ) {
			global $wp_query;
			return $wp_query->found_posts;
		}
		return 0;
	}
}
