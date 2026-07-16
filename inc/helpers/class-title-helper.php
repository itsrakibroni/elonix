<?php
/**
 * Elonix – Toolkit for Elementor Title Helper Class
 *
 * Provides reusable title generation logic.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Title_Helper {

	/**
	 * Get the title based on settings and context.
	 *
	 * @param array $settings Widget settings.
	 * @return string Title text.
	 */
	public static function get_title( $settings = array() ) {
		$source = isset( $settings['title_source'] ) ? $settings['title_source'] : 'current';
		$title  = '';

		if ( 'custom' === $source ) {
			$title = isset( $settings['custom_title'] ) ? $settings['custom_title'] : '';
		} else {
			// Current Post / Page / Archive Title
			$title = self::get_current_context_title();
		}

		// Fallback system
		if ( empty( $title ) ) {
			$title = isset( $settings['fallback_text'] ) ? $settings['fallback_text'] : '';
		}

		return $title;
	}

	/**
	 * Get the title based on current WordPress query context.
	 *
	 * @return string Current title.
	 */
	public static function get_current_context_title() {
		$title = '';

		// 1. WooCommerce check
		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_shop() ) {
				return woocommerce_page_title( false );
			} elseif ( is_product_category() || is_product_tag() ) {
				return single_term_title( '', false );
			}
		}

		// 2. Archive check
		if ( is_archive() ) {
			$title = get_the_archive_title();
		}
		// 3. Search check
		elseif ( is_search() ) {
			/* translators: %s: string */
			$title = sprintf( __( 'Search Results for: %s', 'elonix' ), get_search_query() );
		}
		// 4. 404 page check
		elseif ( is_404() ) {
			$title = __( 'Page Not Found (404)', 'elonix' );
		}
		// 5. Singular check
		elseif ( is_singular() ) {
			$title = get_the_title();
		}
		// 6. Home page check
		elseif ( is_home() ) {
			$post_for_posts = get_option( 'page_for_posts' );
			if ( $post_for_posts ) {
				$title = get_the_title( $post_for_posts );
			} else {
				$title = __( 'Blog', 'elonix' );
			}
		}

		return $title;
	}

	/**
	 * Suggest HTML tag dynamically based on query context.
	 *
	 * @return string Recommended tag (h1 or h2).
	 */
	public static function get_recommended_html_tag() {
		if ( ( is_singular() || is_archive() || is_search() || is_home() ) && is_main_query() ) {
			return 'h1';
		}
		return 'h2';
	}
}
