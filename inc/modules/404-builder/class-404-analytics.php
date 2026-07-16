<?php
/**
 * Elonix – Toolkit for Elementor Advanced 404 Builder Logger and Analytics Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_404_Analytics {

	/**
	 * Log a 404 request into the database table.
	 *
	 * @param string $url        Requested URL.
	 * @param string $referrer   Referrer URL.
	 * @param string $user_agent User Agent string.
	 */
	public function log_404_request( $url, $referrer, $user_agent ) {
		global $wpdb;

		// Sanitize inputs
		$url        = esc_url_raw( $url );
		$referrer   = esc_url_raw( $referrer );
		$user_agent = sanitize_text_field( $user_agent );
		$ip_hash    = md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tv_404_logs WHERE url = %s AND ip_hash = %s AND referrer = %s AND updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)", $url, $ip_hash, $referrer ) );

		if ( $duplicate ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}tv_404_logs SET hits = hits + 1, updated_at = CURRENT_TIMESTAMP WHERE id = %d", absint( $duplicate->id ) ) );
		} else {
			// Insert new transaction row
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$wpdb->prefix . 'tv_404_logs',
				array(
					'url'        => $url,
					'referrer'   => $referrer,
					'user_agent' => $user_agent,
					'ip_hash'    => $ip_hash,
					'hits'       => 1,
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Fetch Top 404 URL logs.
	 *
	 * @param int $limit Max rows.
	 * @return array Logs list.
	 */
	public function get_top_urls( $limit = 10 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare( "SELECT url, SUM(hits) as total_hits, MAX(updated_at) as last_seen FROM {$wpdb->prefix}tv_404_logs GROUP BY url ORDER BY total_hits DESC LIMIT %d", absint( $limit ) ) );
	}

	/**
	 * Fetch Recent 404 logs.
	 *
	 * @param int $limit Max rows.
	 * @return array Logs list.
	 */
	public function get_recent_logs( $limit = 10 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare( "SELECT url, referrer, user_agent, hits, updated_at FROM {$wpdb->prefix}tv_404_logs ORDER BY updated_at DESC LIMIT %d", absint( $limit ) ) );
	}

	/**
	 * Fetch Referrer Sources log data.
	 *
	 * @param int $limit Max rows.
	 * @return array Referrers list.
	 */
	public function get_referrer_sources( $limit = 10 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare( "SELECT referrer, SUM(hits) as total_hits FROM {$wpdb->prefix}tv_404_logs WHERE referrer != '' AND referrer IS NOT NULL GROUP BY referrer ORDER BY total_hits DESC LIMIT %d", absint( $limit ) ) );
	}

	/**
	 * Fetch deduced Search keywords that led to 404s.
	 * Analyzes query strings (e.g. s, q, search, query) from referrers or path parameters.
	 *
	 * @param int $limit Max rows.
	 * @return array Decoded keywords list.
	 */
	public function get_search_terms( $limit = 10 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$raw_logs = $wpdb->get_results( $wpdb->prepare( "SELECT url, referrer FROM {$wpdb->prefix}tv_404_logs WHERE url LIKE %s OR referrer LIKE %s OR url LIKE %s OR referrer LIKE %s", '%' . $wpdb->esc_like('s=') . '%', '%' . $wpdb->esc_like('s=') . '%', '%' . $wpdb->esc_like('q=') . '%', '%' . $wpdb->esc_like('q=') . '%' ) );

		$keywords = array();
		foreach ( $raw_logs as $log ) {
			$search_string = '';
			if ( strpos( $log->url, '?' ) !== false ) {
				$search_string = wp_parse_url( $log->url, PHP_URL_QUERY );
			} elseif ( ! empty( $log->referrer ) ) {
				$search_string = wp_parse_url( $log->referrer, PHP_URL_QUERY );
			}

			if ( ! empty( $search_string ) ) {
				parse_str( $search_string, $query_params );
				// Standard query terms checking
				foreach ( array( 's', 'q', 'search', 'query', 'keyword' ) as $key ) {
					if ( isset( $query_params[ $key ] ) && ! empty( $query_params[ $key ] ) ) {
						$word = sanitize_text_field( trim( $query_params[ $key ] ) );
						if ( ! empty( $word ) ) {
							$word_lower              = strtolower( $word );
							$keywords[ $word_lower ] = isset( $keywords[ $word_lower ] ) ? $keywords[ $word_lower ] + 1 : 1;
						}
					}
				}
			}
		}

		arsort( $keywords );
		$sliced = array_slice( $keywords, 0, $limit, true );

		$formatted = array();
		foreach ( $sliced as $term => $count ) {
			$formatted[] = (object) array(
				'keyword' => $term,
				'count'   => $count,
			);
		}
		return $formatted;
	}

	/**
	 * Fetch Broken Links logs (Internal 404 referrers).
	 * If the referrer is our own website URL, it indicates a broken link inside our own posts/pages.
	 *
	 * @param int $limit Max rows.
	 * @return array Broken links list.
	 */
	public function get_broken_links_report( $limit = 10 ) {
		global $wpdb;
		$site_url   = esc_url_raw( home_url( '/' ) );

		// Strip scheme (http/https) to support secure/unsecure variations matching
		$site_domain = preg_replace( '/^https?:\/\//i', '', $site_url );
		$site_domain = rtrim( $site_domain, '/' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $wpdb->prepare( "SELECT url, referrer, SUM(hits) as total_hits, MAX(updated_at) as last_seen FROM {$wpdb->prefix}tv_404_logs WHERE referrer LIKE %s GROUP BY url, referrer ORDER BY total_hits DESC LIMIT %d", '%' . $wpdb->esc_like( $site_domain ) . '%', absint( $limit ) ) );
	}

	/**
	 * Clear all 404 log transactions.
	 */
	public function clear_logs() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}tv_404_logs" );
	}
}
