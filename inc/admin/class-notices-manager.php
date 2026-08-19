<?php
/**
 * Elonix – Toolkit for Elementor Admin Notices Manager Class
 *
 * Responsible for detecting Elonix admin pages and filtering
 * third-party plugin promotional/marketing notices while preserving
 * WordPress core notices, PHP errors, and security/update warnings.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Admin_Notices_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook early in the admin header output cycle to inspect and filter notices.
		add_action( 'admin_print_styles', array( $this, 'filter_admin_notices' ), 1 );
		add_action( 'admin_head', array( $this, 'filter_admin_notices' ), 1 );
		add_action( 'in_admin_header', array( $this, 'filter_admin_notices' ), 1 );

		// Hook into admin_enqueue_scripts to output fallback suppression CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_suppression_css' ), 99 );
	}

	/**
	 * Detect if the current screen is a Elonix – Toolkit for Elementor page.
	 *
	 * @return bool True if Elonix admin page, false otherwise.
	 */
	private function is_elonix_admin_page() {
		if ( ! is_admin() ) {
			return false;
		}

		// Check screen ID via get_current_screen()
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && isset( $screen->id ) ) {
				if ( strpos( $screen->id, 'elonix' ) !== false ) {
					return true;
				}
			}
		}

		// Fallback check using query parameter
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen-detection check (are we on our own admin page), sanitized, not output.
		if ( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'elonix' ) === 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter admin notices dynamically by unsetting third-party callbacks from WordPress filter queue.
	 */
	public function filter_admin_notices() {
		// Apply restriction ONLY on Elonix – Toolkit for Elementor pages
		if ( ! $this->is_elonix_admin_page() ) {
			return;
		}

		global $wp_filter;

		// Hooks where notice HTML is commonly outputted
		$hooks = array(
			'admin_notices',
			'all_admin_notices',
			'network_admin_notices',
			'user_admin_notices',
			'admin_footer',
		);

		foreach ( $hooks as $hook_name ) {
			if ( isset( $wp_filter[ $hook_name ] ) ) {
				$callbacks      = array();
				$is_hook_object = false;

				if ( class_exists( 'WP_Hook' ) && $wp_filter[ $hook_name ] instanceof WP_Hook ) {
					$callbacks      = $wp_filter[ $hook_name ]->callbacks;
					$is_hook_object = true;
				} elseif ( is_array( $wp_filter[ $hook_name ] ) ) {
					$callbacks = $wp_filter[ $hook_name ];
				}

				foreach ( $callbacks as $priority => $priority_callbacks ) {
					foreach ( $priority_callbacks as $idx => $callback_data ) {
						if ( ! isset( $callback_data['function'] ) ) {
							continue;
						}

						$callback = $callback_data['function'];

						// If it should be filtered, remove it from the callback queue
						if ( $this->should_filter_callback( $callback, $hook_name ) ) {
							if ( $is_hook_object ) {
								unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $idx ] );
							} else {
								unset( $wp_filter[ $hook_name ][ $priority ][ $idx ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Determine if a callback should be filtered (removed).
	 *
	 * @param mixed  $callback  The callback to check.
	 * @param string $hook_name The hook name we are filtering.
	 * @return bool True if the callback should be filtered, false otherwise.
	 */
	private function should_filter_callback( $callback, $hook_name ) {
		$file = $this->get_callback_file( $callback );

		// If we cannot determine where it was defined, keep it to prevent breaking anything critical
		if ( empty( $file ) ) {
			return false;
		}

		// Normalize paths for reliable matching
		$file = str_replace( '\\', '/', $file );

		// 1. DO NOT filter Elonix – Toolkit for Elementor callbacks
		if ( strpos( $file, 'elonix' ) !== false ) {
			return false;
		}

		// 2. DO NOT filter WordPress core callbacks
		if ( strpos( $file, '/wp-admin/' ) !== false || strpos( $file, '/wp-includes/' ) !== false ) {
			return false;
		}

		// Extract callback details for keyword analysis
		$callback_info = $this->get_callback_info_string( $callback );

		// 3. Define promotional/marketing keywords to filter
		$promo_keywords = array(
			'promo',
			'discount',
			'sale',
			'upsell',
			'coupon',
			'black-friday',
			'christmas',
			'deal',
			'offer',
			'review',
			'rating',
			'feedback',
			'recommend',
			'subscribe',
			'newsletter',
			'pro-version',
			'go-pro',
			'buy-now',
			'upgrade-to',
			'ad-',
			'ads',
			'banner',
			'marketing',
			'plugin-ad',
			'donation',
			'support-us',
			'jeg-kit',
			'elementskit',
			'essential-addons',
			'ultimate-addons',
		);

		foreach ( $promo_keywords as $keyword ) {
			if ( stripos( $file, $keyword ) !== false || stripos( $callback_info, $keyword ) !== false ) {
				return true;
			}
		}

		// 4. Define critical keywords to preserve (update warnings, security warnings, error/fatal warnings)
		$critical_keywords = array(
			'error',
			'warning',
			'fatal',
			'critical',
			'security',
			'vulnerab',
			'update_nag',
			'update_notice',
			'version_check',
			'php_check',
			'php_version',
			'requirement',
			'license',
			'ssl',
			'wordfence',
			'sucuri',
			'limit-login',
			'ithemes',
			'defender',
		);

		foreach ( $critical_keywords as $keyword ) {
			if ( stripos( $file, $keyword ) !== false || stripos( $callback_info, $keyword ) !== false ) {
				return false;
			}
		}

		// 5. For 'admin_footer', only filter if it matched a promo keyword above.
		// Otherwise, keep it to avoid breaking functional elements or templates.
		if ( 'admin_footer' === $hook_name ) {
			return false;
		}

		// 6. Filter any other third-party plugin or theme callback
		if ( strpos( $file, '/wp-content/plugins/' ) !== false || strpos( $file, '/wp-content/themes/' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the file path where a callback is defined.
	 *
	 * @param mixed $callback The callback callable.
	 * @return string The absolute file path, or empty string on failure.
	 */
	private function get_callback_file( $callback ) {
		try {
			if ( is_string( $callback ) ) {
				if ( strpos( $callback, '::' ) !== false ) {
					$parts = explode( '::', $callback );
					$ref   = new ReflectionMethod( $parts[0], $parts[1] );
					return $ref->getFileName();
				}
				if ( function_exists( $callback ) ) {
					$ref = new ReflectionFunction( $callback );
					return $ref->getFileName();
				}
			} elseif ( is_array( $callback ) ) {
				if ( isset( $callback[0] ) && isset( $callback[1] ) ) {
					$ref = new ReflectionMethod( $callback[0], $callback[1] );
					return $ref->getFileName();
				}
			} elseif ( $callback instanceof Closure ) {
				$ref = new ReflectionFunction( $callback );
				return $ref->getFileName();
			} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
				$ref = new ReflectionMethod( $callback, '__invoke' );
				return $ref->getFileName();
			}
		} catch ( Exception $e ) {
			return '';
		}
		return '';
	}

	/**
	 * Get a string representation of the callback details for keyword searching.
	 *
	 * @param mixed $callback The callback.
	 * @return string String containing class name, method name, or function name.
	 */
	private function get_callback_info_string( $callback ) {
		if ( is_string( $callback ) ) {
			return $callback;
		}
		if ( is_array( $callback ) ) {
			$class  = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			$method = isset( $callback[1] ) ? $callback[1] : '';
			return $class . '::' . $method;
		}
		if ( $callback instanceof Closure ) {
			return 'closure';
		}
		if ( is_object( $callback ) ) {
			return get_class( $callback );
		}
		return '';
	}

	/**
	 * Output inline CSS to hide known third-party plugin notices and ads as a fallback.
	 */
	public function enqueue_suppression_css() {
		if ( ! $this->is_elonix_admin_page() ) {
			return;
		}
		
		wp_enqueue_style(
			'elonix-notices-suppression',
			ELONIX_ACC_URL . 'assets/admin/css/notices-manager.css',
			array(),
			ELONIX_VERSION
		);
	}
}
