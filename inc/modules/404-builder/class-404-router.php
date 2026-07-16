<?php
/**
 * Elonix – Toolkit for Elementor Advanced 404 Builder Routing and Verification Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_404_Router {

	/**
	 * Analytics logger instance.
	 *
	 * @var Elonix_Toolkit_404_Analytics
	 */
	private $analytics;

	/**
	 * Constructor.
	 *
	 * @param Elonix_Toolkit_404_Analytics $analytics Analytics instance.
	 */
	public function __construct( $analytics ) {
		$this->analytics = $analytics;

		// Intercept the query at template redirect loop
		add_action( 'template_redirect', array( $this, 'intercept_request' ), 5 );

		// Disable URL Guessing conditionally
		add_filter( 'redirect_canonical', array( $this, 'handle_canonical_guessing' ), 10, 2 );
	}

	/**
	 * Disable default WordPress redirect guessing when a 404 occurs.
	 *
	 * @param string $redirect_url  Guess redirect URL.
	 * @param string $requested_url Requested URL.
	 * @return string|false False to abort redirection, or url.
	 */
	public function handle_canonical_guessing( $redirect_url, $requested_url ) {
		$disable_guessing = ( 'yes' === ( Elonix_Settings::get( 'tv_404_disable_url_guessing' ) ?? 'yes' ) ) || ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_disable_redirect_guessing' ) ?? 'yes' ) );
		if ( $disable_guessing && is_404() ) {
			return false; // Prevent guessing
		}
		return $redirect_url;
	}

	/**
	 * Run inspections on the request URL and verify if a 404 should be intercepted.
	 */
	public function intercept_request() {
		// Verify if we should bypass or render the custom 404 template canvas
		if ( ! $this->should_render_404_template() ) {
			if ( ! is_404() ) {
				$template_id = intval( Elonix_Settings::get( 'tv_404_selected_page_id' ) ?? 0 );
				if ( $template_id && ( is_page( $template_id ) || get_queried_object_id() === $template_id ) ) {
					// Ensure we do not redirect during Elementor editor, preview, or AJAX sessions
					if ( class_exists( '\Elementor\Plugin' ) ) {
						if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
							return;
						}
					}
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
						return;
					}
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
						return;
					}

					// Non-administrators are redirected to the homepage
					$allow_admin = ( 'yes' === ( Elonix_Settings::get( 'tv_404_allow_admin_direct_access' ) ?? 'yes' ) );
					if ( ! ( $allow_admin && current_user_can( 'manage_options' ) ) ) {
						wp_safe_redirect( home_url( '/' ) );
						exit;
					}
				}
			}
			return;
		}

		// Check if we need to force 404 after page load based on current conditions
		$this->maybe_force_404_after_load();

		$template_id = intval( Elonix_Settings::get( 'tv_404_selected_page_id' ) ?? 0 );

		// If this is a real 404 request, proceed with headers, redirects, and logging
		if ( is_404() ) {
			// Evaluate Custom Redirect Rules
			$this->evaluate_redirect_rules();

			// Record the 404 error log dynamically
			if ( 'yes' === ( Elonix_Settings::get( 'tv_404_enable_logging' ) ?? 'yes' ) ) {
				$url        = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				$referrer   = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
				$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

				$this->analytics->log_404_request( $url, $referrer, $user_agent );
			}

			// Configure HTTP headers and redirects
			$this->send_http_status_header();
			$this->send_seo_robots_header();
		}

		// Hijack template loader to render our custom Elementor Canvas template
		add_filter( 'template_include', array( $this, 'load_404_template_canvas' ), 999 );
	}

	/**
	 * Override the rendering template file with our clean Elementor page canvas.
	 */
	public function load_404_template_canvas( $template ) {
		if ( ! $this->should_render_404_template() ) {
			return $template;
		}
		$custom_canvas = ELONIX_ACC_PATH . 'inc/modules/404-builder/templates/404-canvas.php';
		if ( file_exists( $custom_canvas ) ) {
			return $custom_canvas;
		}
		return $template;
	}

	/**
	 * Determine if the custom 404 template canvas should be rendered for the current request.
	 *
	 * @return bool True if custom template canvas should render, false otherwise.
	 */
	public function should_render_404_template() {
		// Rule 1: Never execute 404 template hijacks inside Elementor Editor
		// Rule 2: Never execute 404 template hijacks inside Elementor Preview
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return false;
			}
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		// Also check query parameters representing editor or preview session
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
			return false;
		}

		// Rule 3: Never execute 404 template hijacks during Elementor AJAX
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
			return false;
		}

		if ( 'yes' !== ( Elonix_Settings::get( 'tv_404_enable_custom_page' ) ?? 'no' ) ) {
			return false;
		}

		$template_id = intval( Elonix_Settings::get( 'tv_404_selected_page_id' ) ?? 0 );
		if ( ! $template_id ) {
			return false;
		}

		// Case 1: Real 404 request that is not excluded
		if ( is_404() ) {
			return ! $this->is_request_excluded();
		}

		// Case 2: Direct access to selected template page by administrator
		$allow_admin = ( 'yes' === ( Elonix_Settings::get( 'tv_404_allow_admin_direct_access' ) ?? 'yes' ) );
		if ( $allow_admin && current_user_can( 'manage_options' ) ) {
			if ( is_page( $template_id ) || get_queried_object_id() === $template_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Verify if current user role, URL path, post-type, or query keys are excluded.
	 *
	 * @return bool True if excluded, false otherwise.
	 */
	public function is_request_excluded() {
		// 2. User Roles Exclusions
		$excluded_roles = Elonix_Settings::get( 'tv_404_excluded_user_roles' ) ?? array();
		if ( ! empty( $excluded_roles ) && is_user_logged_in() ) {
			$user = wp_get_current_user();
			foreach ( (array) $excluded_roles as $role ) {
				if ( in_array( $role, $user->roles, true ) ) {
					return true;
				}
			}
		}

		$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// 3. Excluded URLs Check (sub-string / wildcard matches)
		$excluded_urls = Elonix_Settings::get( 'tv_404_excluded_urls' ) ?? '';
		if ( ! empty( $excluded_urls ) ) {
			$urls_list = array_map( 'trim', explode( "\n", $excluded_urls ) );
			foreach ( $urls_list as $exclude_url ) {
				if ( ! empty( $exclude_url ) && strpos( $current_uri, $exclude_url ) !== false ) {
					return true;
				}
			}
		}

		// 4. Excluded Post Types Check
		$excluded_post_types = Elonix_Settings::get( 'tv_404_excluded_post_types' ) ?? array();
		if ( ! empty( $excluded_post_types ) ) {
			$queried_post_type = get_query_var( 'post_type' );
			if ( ! empty( $queried_post_type ) && in_array( $queried_post_type, (array) $excluded_post_types, true ) ) {
				return true;
			}
		}

		// 5. Excluded Query Parameters Check
		$excluded_queries = Elonix_Settings::get( 'tv_404_excluded_query_parameters' ) ?? '';
		if ( ! empty( $excluded_queries ) ) {
			$params_list = array_map( 'trim', explode( ',', $excluded_queries ) );
			foreach ( $params_list as $param ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( ! empty( $param ) && isset( $_GET[ $param ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Evaluate wildcard and regex custom redirect rules.
	 */
	private function evaluate_redirect_rules() {
		$redirect_rules = Elonix_Settings::get( 'tv_404_custom_redirect_rules' ) ?? '';
		if ( empty( $redirect_rules ) ) {
			return;
		}

		$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$rules_list  = array_map( 'trim', explode( "\n", $redirect_rules ) );

		foreach ( $rules_list as $rule ) {
			if ( empty( $rule ) || strpos( $rule, '=>' ) === false ) {
				continue;
			}

			list( $pattern, $target ) = array_map( 'trim', explode( '=>', $rule, 2 ) );
			if ( empty( $pattern ) || empty( $target ) ) {
				continue;
			}

			// Support regular expression matches
			$is_regex = ( strpos( $pattern, '^' ) === 0 || strpos( $pattern, '/' ) === 0 );
			$matched  = false;

			if ( $is_regex ) {
				$delimited_pattern = '#' . str_replace( '#', '\#', $pattern ) . '#i';
				if ( preg_match( $delimited_pattern, $current_uri ) ) {
					$matched = true;
					// Replace backreferences if needed
					$target = preg_replace( $delimited_pattern, $target, $current_uri );
				}
			} else {
				// Simple wildcard match
				$wildcard_pattern = str_replace( '*', '.*', preg_quote( $pattern, '#' ) );
				if ( preg_match( '#^' . $wildcard_pattern . '$#i', $current_uri ) ) {
					$matched = true;
				}
			}

			if ( $matched ) {
				$status = intval( Elonix_Settings::get( 'tv_404_custom_status_code' ) ?? 301 );
				if ( ! in_array( $status, array( 301, 302, 307, 308 ), true ) ) {
					$status = 301;
				}
				wp_safe_redirect( esc_url_raw( $target ), $status );
				exit;
			}
		}
	}

	/**
	 * Send 404 / 410 Gone / Custom HTTP status header codes.
	 */
	private function send_http_status_header() {
		$send_410      = ( 'yes' === ( Elonix_Settings::get( 'tv_404_send_410_header' ) ?? 'no' ) ) || ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_410_header' ) ?? 'no' ) );
		$custom_status = Elonix_Settings::get( 'tv_404_custom_status_code' );

		if ( $send_410 ) {
			status_header( 410, 'Gone' );
		} elseif ( ! empty( $custom_status ) && in_array( intval( $custom_status ), array( 404, 410, 403, 401 ), true ) ) {
			status_header( intval( $custom_status ) );
		} else {
			status_header( 404 );
		}
	}

	/**
	 * Hook to inject SEO meta instructions (noindex, nofollow, canonical) inside response headers.
	 */
	private function send_seo_robots_header() {
		$noindex  = ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_noindex' ) ?? 'yes' ) );
		$nofollow = ( 'yes' === ( Elonix_Settings::get( 'tv_404_seo_nofollow' ) ?? 'yes' ) );

		if ( $noindex || $nofollow ) {
			$parts = array();
			if ( $noindex ) {
				$parts[] = 'noindex';
			}
			if ( $nofollow ) {
				$parts[] = 'nofollow';
			}

			header( 'X-Robots-Tag: ' . implode( ', ', $parts ) );
		}
	}

	/**
	 * Check if force 404 after load options are set to override templates dynamically.
	 */
	private function maybe_force_404_after_load() {
		if ( 'yes' === ( Elonix_Settings::get( 'tv_404_force_after_load' ) ?? 'no' ) ) {
			// Evaluates conditions to mark current request as 404 dynamically
			$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			if ( strpos( $current_uri, '/force-404' ) !== false ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
			}
		}
	}
}
