<?php
/**
 * Elonix Premium Post Block Query Engine Helper
 *
 * Safe query parsing, custom post type resolving, date filtering, caching
 * transients, and dynamic contextual query adaptions for Archive Builders.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Post_Block_Query_Helper {

	/**
	 * Main query method with transient caching.
	 *
	 * @param array $settings Widget controls configuration.
	 * @return array Array containing matching post structures.
	 */
	public static function get_posts_data( $settings, $archive_vars = array() ) {
		// 1. Build and run WP_Query via Shared Service
		$query = \Elonix_Query_Context::execute_query( $settings, $archive_vars );

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
	 * Formats dynamic post output properties safely.
	 */
	public static function format_post_data( $post, $settings ) {
		$title_suffix = isset( $settings['title_suffix'] ) ? sanitize_text_field( $settings['title_suffix'] ) : '...';
		$title_words  = isset( $settings['title_word_limit'] ) ? intval( $settings['title_word_limit'] ) : 0;
		$title_chars  = isset( $settings['title_char_limit'] ) ? intval( $settings['title_char_limit'] ) : 0;

		$raw_title = get_the_title( $post->ID );
		$title     = self::limit_text_content( $raw_title, $title_words, $title_chars, $title_suffix, false, false );

		$excerpt_suffix     = isset( $settings['excerpt_suffix'] ) ? sanitize_text_field( $settings['excerpt_suffix'] ) : '...';
		$excerpt_words      = isset( $settings['excerpt_word_limit'] ) ? intval( $settings['excerpt_word_limit'] ) : 15;
		$excerpt_chars      = isset( $settings['excerpt_char_limit'] ) ? intval( $settings['excerpt_char_limit'] ) : 0;
		$excerpt_strip_html = ! isset( $settings['excerpt_strip_html'] ) || 'yes' === $settings['excerpt_strip_html'];
		$excerpt_strip_sc   = ! isset( $settings['excerpt_strip_shortcodes'] ) || 'yes' === $settings['excerpt_strip_shortcodes'];

		$raw_excerpt = get_the_excerpt( $post->ID );
		if ( empty( $raw_excerpt ) ) {
			$raw_excerpt = $post->post_content;
		}
		$excerpt = self::limit_text_content( $raw_excerpt, $excerpt_words, $excerpt_chars, $excerpt_suffix, $excerpt_strip_html, $excerpt_strip_sc );

		// Image sizes
		$thumb_size = ! empty( $settings['thumbnail_size'] ) ? sanitize_text_field( $settings['thumbnail_size'] ) : 'medium';
		$thumbnail  = get_the_post_thumbnail(
			$post->ID,
			$thumb_size,
			array(
				'class'   => 'es-post-block-img',
				'loading' => 'lazy',
			)
		);

		// Category lists resolution
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

		// Fallback primary category resolution (RankMath/Yoast options checks)
		$primary_category = '';
		if ( ! empty( $categories_data ) ) {
			$primary_category = $categories_data[0]['name'];
			// Rank Math primary category check
			$rm_primary = get_post_meta( $post->ID, 'rank_math_primary_category', true );
			if ( $rm_primary ) {
				$term = get_term( $rm_primary );
				if ( $term && ! is_wp_error( $term ) ) {
					$primary_category = $term->name;
				}
			} else {
				// Yoast primary category check
				$yoast_primary = get_post_meta( $post->ID, '_yoast_wpseo_primary_category', true );
				if ( $yoast_primary ) {
					$term = get_term( $yoast_primary );
					if ( $term && ! is_wp_error( $term ) ) {
						$primary_category = $term->name;
					}
				}
			}
		}

		// Metadata calculations
		$word_count   = str_word_count( wp_strip_all_tags( get_the_content( null, false, $post->ID ) ) );
		$reading_time = max( 1, ceil( $word_count / 200 ) );
		$views        = get_post_meta( $post->ID, 'es_post_views_count', true );
		$views        = ( '' !== $views ) ? intval( $views ) : 0;

		// Smart Badges calculations
		$post_age_seconds = current_time( 'timestamp' ) - get_the_time( 'U', $post->ID );
		$is_new           = ( $post_age_seconds < 3 * DAY_IN_SECONDS );
		$is_popular       = ( intval( get_comments_number( $post->ID ) ) > 10 );
		$is_trending      = ( $views > 100 );

		return array(
			'id'                => $post->ID,
			'title'             => $title,
			'raw_title'         => get_the_title( $post->ID ),
			'url'               => esc_url( get_permalink( $post->ID ) ),
			'excerpt'           => $excerpt,
			'raw_excerpt'       => ! empty( $raw_excerpt ) ? $raw_excerpt : $post->post_content,
			'thumbnail'         => $thumbnail,
			'date'              => esc_html( get_the_date( '', $post->ID ) ),
			'updated_date'      => esc_html( get_the_modified_date( '', $post->ID ) ),
			'author_name'       => esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
			'author_url'        => esc_url( get_author_posts_url( $post->post_author ) ),
			'comments'          => intval( get_comments_number( $post->ID ) ),
			'views'             => $views,
			'reading_time'      => $reading_time,
			'categories'        => $categories_data,
			'primary_category'  => $primary_category,
			'is_new'            => $is_new,
			'is_popular'        => $is_popular,
			'is_trending'       => $is_trending,
			'is_sponsored'      => 'yes' === get_post_meta( $post->ID, 'es_is_sponsored', true ),
			'is_verified'       => 'yes' === get_post_meta( $post->ID, 'es_is_verified', true ) || 'yes' === get_user_meta( $post->post_author, 'es_author_verified', true ),
			'is_editors_choice' => 'yes' === get_post_meta( $post->ID, 'es_editors_choice', true ),
		);
	}

	/**
	 * Text trims, HTML tags removal, shortcodes strip utility.
	 */
	public static function limit_text_content( $text, $word_limit = 0, $char_limit = 0, $suffix = '...', $strip_html = true, $strip_shortcodes = true ) {
		if ( $strip_shortcodes ) {
			$text = strip_shortcodes( $text );
		}
		if ( $strip_html ) {
			$text = wp_strip_all_tags( $text );
		}

		if ( $word_limit > 0 ) {
			$text = wp_trim_words( $text, $word_limit, $suffix );
		} elseif ( $char_limit > 0 ) {
			if ( mb_strlen( $text ) > $char_limit ) {
				$text = mb_substr( $text, 0, $char_limit ) . $suffix;
			}
		}

		return trim( $text );
	}
}
